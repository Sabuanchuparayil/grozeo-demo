<?php

namespace App\ExpressPartners\Clickpost;

use DateTime;
use App\Models\{
	Order,
	QugeoOrder,
	TransferOrder,
	QugeoOrderCourier
};
use App\Helpers\EmailHelper;
use App\Models\CourierDelivery\{
    ShippingConsignment,
    ConsignmentTracking,
    CancelConsignment
};
use Illuminate\Support\Facades\DB;
use App\ExpressPartners\Clickpost\{
	ClickpostApiOperations,
	ClickpostRequests
};
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\PartnerOrderUpdateRepository;

class Clickpost
{
	function __construct()
	{
		$this->operations = new ClickpostApiOperations;
		$this->requests = new ClickpostRequests;
		$this->orderMethod = config("expresspartners.clickpost.orderMethod") ?? 1;
	}
	public function checkIfDeliveryAgentAvailable($request)
	{
		return true;
	}
	public function checkPartnerAvailability($fsto_id)
	{
		$outs = [];
		$requestData = $this->requests->createConsignmentRequest($fsto_id);
		if($requestData['status'] == "success")
		{
			$checkPartners = $this->operations->checkPartnerAvailability($requestData['data']);
			return $checkPartners;
		}
		return [];
	}

	public function createNewConsignment($package)
	{
		try
		{
			$outs = false;
			$requestData = $this->requests->createConsignmentRequest($package->fsto_id);
			if($requestData['status'] == "success")
			{
				$checkPartners = $this->operations->checkPartnerAvailability($requestData['data']);
				if(@$checkPartners['status'] == 'success')
				{
					$consignment = $this->operations->createConsignment($requestData['data'], $checkPartners['data']);
					if(@$consignment['status'] == 'success')
					{
						$consignmentData = (array)@$consignment['data'];
						$consignmentReq = @$consignment['request'];
						$trackingLink = strtr(config("expresspartners.clickpost.trackingURL"), [
							"{#wayBillID}"	=> @$consignmentData['result']->waybill,
							"{#partnerID}"	=> @$consignmentData['result']->courier_partner_id
						]);
						$create = [
							'order_id'              => @$consignmentData['result']->reference_number,
							'order_method'          => $this->orderMethod,
							'shipping_type'         => 'clickpost',
							'shipping_id'           => @$consignmentData['result']->waybill,
							'tracking_id'           => @$consignmentData['tracking_id'],
							'shipment_label'        => @$consignmentData['result']->label,
							'tracking_link'         => $trackingLink,
							'shipping_partner'      => @$consignmentData['result']->courier_name,
							'shipping_charge'       => 0,
							'consignment_status'    => 1,
							'consignment_request'   => json_encode($consignmentReq),
							'consignment_response'  => json_encode($consignmentData)
						];
						$createCon = ShippingConsignment::create($create);
						$updateOrder = Order::where('order_order_id', @$consignmentData['result']->reference_number)->update([
							'order_trackID'     => $consignmentData['tracking_id'],
							'order_trackURL'	=> $trackingLink
						]);
						$transferOrderUpdate = TransferOrder::where('fsto_id', $package->fsto_id)->update([
							'fsto_hasShipmentCreated'	=> 1
						]);
						$sendEmail = (new EmailHelper)->sendEmail('ShipmentConfirmation', [
							'Customersname'		=> @$consignmentReq['drop_info']['drop_name'],
							'email'     		=> @$consignmentReq['drop_info']['drop_email'],
							'order_order_id'	=> @$consignmentData['result']->reference_number
						]);
						$outs = true;
					}
				}
			}
			return $outs;
        }
        catch (\Exception $e)
        {
            info("CLICKPOST ERROR");info($e);
            return false; 
        }
	}

	public function cancelConsignment($order_id, $reason = '')
	{
		try
		{
			$shippingData = ShippingConsignment::select('id', 'order_id', 'shipping_id', 'tracking_id')->where([
				['order_id', $order_id],
				['consignment_status', '<', 3]
			])->get();
	        $outs = [
	            'status'    => 'error',
	            'data'      => [],
	            'message'   => 'Shipping Data not found'
	        ];
	        if($shippingData)
	        {
	        	$cancelled = 0;
	        	foreach ($shippingData as $sdata)
	        	{
	        		$cancellation = $this->operations->cancelConsignment($order_id, $sdata->shipping_id);
					if($cancellation['status'] == "success")
			        {
			        	$cancelled++;
			        	ShippingConsignment::where([
			                'shipping_type' => 'clickpost',
			                'id'            => $sdata->id
			            ])->update([
			                'consignment_status'    => 4
			            ]);

			            CancelConsignment::create([
			                'order_id'      => $sdata->order_id,
			                'shipping_id'   => $sdata->shipping_id,
			                'cancel_reason' => $reason
			            ]);
			        }
	        	}
	            if($cancelled > 0)
	            {
		            $outs['status'] = 'success';
		            $outs['message'] = $cancelled." shipment(s) cancelled.";
	            }
			}
	        return $outs;
		}
		catch (\Exception $e)
        {
            return [
            	'status'	=> 'error',
            	'message'	=> $e->getMessage()
            ]; 
        }
	}

	public function completeTrackingStatus($order_id)
	{
		try
		{
			$shippingData = ShippingConsignment::select('id', 'order_id', 'shipping_id', 'tracking_id')->where([
				['order_id', $order_id],
				['consignment_status', '<', 3]
			])->get();
	        $outs = [
	            'status'    => 'error',
	            'data'      => [],
	            'message'   => 'Shipping Data not found'
	        ];
	        if($shippingData)
	        {
	        	$cancelled = 0;
	        	$consignmentTracking = [];
	        	foreach ($shippingData as $sdata)
	        	{
	        		$shippingID = $sdata->shipping_id;
	        		$consignmentTracking = $this->operations->consignmentTracking($order_id, $shippingID);
	        		return $consignmentTracking;
					if($consignmentTracking['status'] == "success")
			        {
			        	$tracking = (array)$consignmentTracking['data'];
			        	$trackingDetails = @$tracking['result']->$shippingID;
			        	if($trackingDetails)
			        	{
			        		$currentTracking = $trackingDetails->latest_status;
			        		$latestTracking = ConsignmentTracking::where('tracking_id', $sdata->tracking_id)->latest()->first();
			        		if(@$currentTracking->status != @$latestTracking->status_value)
			        		{
								$this->updateTrackingStatus($currentTracking, $shippingData, $sdata->tracking_id);
			        			ConsignmentTracking::where('tracking_id', $sdata->tracking_id)->delete();
			        			$create = [];
				        		foreach ($trackingDetails->scans as $td)
				        		{
				        			$create[] = [
					                    'shipping_type' => 'clickpost',
					                    'tracking_id'   => $td->id,
					                    'status_id'     => $td->clickpost_status_code,
					                    'status_value'  => $td->status,
					                    'location'      => $td->location,
					                    'status_date'   => $td->created_at
					                ];
				        		}
				        		ConsignmentTracking::insert($create);
				        	}
			        	}
			        }
	        	}
	        }
	    }
	    catch (\Exception $e)
        {
            return [
            	'status'	=> 'error',
            	'message'	=> $e->getMessage()
            ]; 
        }
	}

	public function webhook($data) {}


	private function updateTrackingStatus($currentTracking, $shipping, $trackingID)
	{
		switch($currentTracking->clickpost_status_code)
		{
			case 1:
				$consignmentStatus = 1;
				break;
			case 2:
				$consignmentStatus = 1;
				$this->orderPickup($ship, $currentTracking, $trackingID);
				break;
			case 4:
				$consignmentStatus = 2;
				$this->orderPickupComplete($ship, $currentTracking, $trackingID);
				break;
			case 6:
				$consignmentStatus = 2;
				break;
			case 8:
				$consignmentStatus = 3;
				$this->updateDeliveryStatus($ship, $currentTracking, $trackingID);
				break;
			case 10:
				$consignmentStatus = 4;
				$this->cancelDelivery($ship, $currentTracking, $trackingID);
				break;
		}
		ShippingConsignment::where([
			['shipping_type', 'uber'],
			['shipping_id', $ship->shipping_id]
		])->update([
			'consignment_status' => $consignmentStatus
		]);
	}
	
    private function orderPickup($ship, $currentTracking, $trackingID)
    {
        $request = [
            "orderID"       => $ship->order_id,
            "deliveryID"    => $trackingID,
            "partner"       => "clickpost"
        ];
        (new PartnerOrderUpdateRepository)->orderPickup($request);
    }
    private function orderPickupComplete($ship, $currentTracking, $trackingID)
    {
        $request = [
            "orderID"       => $ship->order_id,
            "deliveryID"    => $trackingID,
            "partner"       => "clickpost"
        ];
        (new PartnerOrderUpdateRepository)->orderPickupComplete($request);
    }
    private function updateDeliveryStatus($ship, $currentTracking, $trackingID)
    {

        $request = [
            "orderDate"     => $currentTracking->timestamp,
            "orderID"       => $ship->order_id,
            "deliveryID"    => $trackingID,
            "partner"       => "clickpost"
        ];
        (new PartnerOrderUpdateRepository)->orderDelivered($request);
    }
	private function cancelDelivery($ship, $currentTracking, $trackingID) {}
}