<?php

namespace App\CourierPartners\Clickpost;

use DateTime;
use App\Helpers\EmailHelper;
use Illuminate\Support\Facades\DB;
use BackOffice\Models\TransferOrder;
use App\CourierPartners\Clickpost\{
	ClickpostApiOperations,
	ClickpostRequests
};
use App\Http\Responses\SuccessWithData;

use App\Models\{
	Order,
	QugeoOrderCourier
};
use BackOffice\Models\QugeoOrder;
use App\Models\CourierDelivery\{
    ShippingConsignment,
    ConsignmentTracking,
    CancelConsignment
};

class Clickpost
{
	function __construct()
	{
		$this->operations = new ClickpostApiOperations;
		$this->requests = new ClickpostRequests;
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

	public function generateShipment($fsto_id)
	{
		$outs = [];
		$requestData = $this->requests->createConsignmentRequest($fsto_id);
		// return $requestData;
		if($requestData['status'] == "success")
		{
			$checkPartners = $this->operations->checkPartnerAvailability($requestData['data']);
			// return $checkPartners;
			if(@$checkPartners['status'] == 'success')
			{
				$consignment = $this->operations->createConsignment($requestData['data'], $checkPartners['data']);
				info($consignment);info($consignment['status']);
				if(@$consignment['status'] == 'success')
				{
					$consignmentData = (array)@$consignment['data'];
                    $consignmentReq = @$consignment['request'];
                    $trackingLink = strtr(config("courierpartners.clickpost.trackingURL"), [
			            "{#wayBillID}"	=> @$consignmentData['result']->waybill,
			            "{#partnerID}"	=> @$consignmentData['result']->courier_partner_id
			        ]);
			        info("consignmentData");info(json_encode($consignmentData));
			        info("consignmentData Result");info(json_encode(@$consignmentData['result']));
			        info("trackingLink");info($trackingLink);
			        $create = [
						'order_id'              => @$consignmentData['result']->reference_number,
                        'order_method'          => 3,
                        'shipping_type'         => 'clickpost',
                        'shipping_id'           => @$consignmentData['result']->waybill,
                        'tracking_id'           => @$consignmentData['tracking_id'],
                        'shipment_label'        => @$consignmentData['result']->label,
                        'tracking_link'         => $trackingLink,
                        'shipping_partner'      => @$consignmentData['result']->courier_name,
                        'shipping_charge'       => 0,
                        'pickupdate'            => "",
                        'consignment_status'    => 1,
                        'consignment_request'   => json_encode($consignmentReq),
                        'consignment_response'  => json_encode($consignmentData)
					];
					info($create);
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
                    return [$createCon, $updateOrder, $transferOrderUpdate, $sendEmail];
				}
				return $consignment;
			}
		}
		return $outs;
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
}