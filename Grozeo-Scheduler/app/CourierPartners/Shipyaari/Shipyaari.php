<?php

namespace App\CourierPartners\Shipyaari;

use DateTime;
use App\Helpers\EmailHelper;
use Illuminate\Support\Facades\DB;
use App\CourierPartners\Shipyaari\{
	ShipyaariDefaults,
	ShipyaariApiOperations,
	ShipyaariRequests
};
use App\Http\Responses\SuccessWithData;

use App\Models\{
	Order,
	QugeoOrder,
	TransferOrder,
	QugeoOrderCourier
};
use App\Models\CourierDelivery\{
    ShippingConsignment,
    ConsignmentTracking,
    CancelConsignment
};
use App\Http\Repositories\PartnerOrderUpdateRepository;

class Shipyaari
{
	protected $defaults;
	protected $order;
	protected $operations;
	protected $requests;
    protected $shippingConsignment;
    protected $cancelConsignment;
    protected $consignmentTracking;

	function __construct()
	{
		$this->defaults = new ShipyaariDefaults;
		$this->operations = new ShipyaariApiOperations;
		$this->requests = new ShipyaariRequests;

        $this->shippingConsignment = DB::table('shipping_consignment');
        $this->cancelConsignment = DB::table('cancel_consignment');
        $this->consignmentTracking = DB::table('consignment_tracking');
	}

	public function getPartnersList($data)
	{
		$details = [
			'pickup_pincode'    => $data['from_details']['pincode'],
            'delivery_pincode'  => $data['to_details']['pincode'],
            'invoicevalue'      => $data['total'],
            'length'            => $data['length'],
            'width'             => $data['width'],
            'height'            => $data['height'],
            'weight'            => $data['weight']
		];
		$partnerList = $this->checkForPartners($details);
		if(@$partnerList->status)
		{
			return [
				"status"	=> @$partnerList->status,
				"amount"	=> 0
			];
		}
		else
		{
			$partner = reset($partnerList);
			return [
				"status"	=> "success",
				"amount"	=> @$partner->delivery_charge,
				"partner"	=> [
					'partner_id'		=> @$partner->partner_id,
					'courier_name'		=> @$partner->courier_name,
					'service_name'		=> @$partner->service_name,
					'delivery_charge'	=> @$partner->delivery_charge
				]
			];
		}
	}
	private function checkForPartners($data)
	{
			$data['paymentmode'] = 'online';
			$partnerList = $this->operations->checkPartnerAvailability($data);
			return $partnerList;
	}
	public function checkPartnerAvailability($data)
	{
		try
		{
			$partnerList = $this->checkForPartners($data);
			if(@$partnerList->status == 'error')
			{
				return [
					"status"	=> @$partnerList->status,
					"messsage"	=> @$partnerList->message
				];
			}
			else
			{
				return [
					"status"		=> @$partnerList->status,
					"partner_id"	=> @$partnerList->partner_id,
					"partner_name"	=> @$partnerList->courier_name,
					"subtotal"		=> @$partnerList->subtotal,
					"tax"			=> @$partnerList->tax,
					"total"			=> @$partnerList->total,
					"estimate_date"	=> @$partnerList->estimate_date
				];
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
	public function generateShipment($fsto_id)
	{
		try
		{
			$outs = false;
			$consignmentData = $this->requests->createConsignmentRequest($fsto_id);
			if($consignmentData['status'] == 'success')
			{
				$consignment = $this->operations->createConsignment($consignmentData['data']);
				if($consignment['status'] == 'success')
				{
					$response = ['status'	=> 'success', 'details' => []];
					for($i = 0; $i < count($consignment['shipping_id']); $i++)
					{
						$response['details'][] = $details = [
							'order_id'				=> $consignment['order_id'],
							'shipping_type'			=> 'shipyaari',
							'shipping_id'			=> $consignment['shipping_id'][$i],
							'tracking_id'			=> $consignment['tracking_id'][$i],
							'shipment_label'		=> $consignment['shipment_label'][$i],
							'tracking_link'			=> config('courierpartners.shipyaari.track_prefix').$consignment['tracking_id'][$i],
							'shipping_partner'		=> json_encode([
								"partner_id"	=> $consignment['partner_id'],
								"partner_name"	=> $consignment['partner_name']
							]),
							'shipping_charge'		=> $consignment['shipping_charge'],
							'pickupdate'			=> $consignment['pickupdate'],
							'consignment_status'	=> 1,
							'consignment_request'	=> json_encode($consignment['request']),
							'consignment_response'	=> json_encode($consignment['response'])
						];
						$this->shippingConsignment->insert($details);
						$updateOrder = Order::where('order_order_id', $consignment['order_id'])->update([
							'order_trackID'		=> $consignment['tracking_id'][$i],
							'order_trackURL'	=> config('courierpartners.shipyaari.track_prefix').$consignment['tracking_id'][$i]
						]);
						$qugeoOrder = QugeoOrder::where([
							['quor_RefNo', $consignment['order_id']],
							['quor_TransferOrder_Type', 1]
						])->first();
						$trackUpdate = QugeoOrderCourier::create([
							'qoc_courier'		=> 1,
							'qoc_qcn'			=> $consignment['tracking_id'][$i],
							'qoc_date'			=> $consignment['pickupdate'],
							'quor_id'			=> @$qugeoOrder->quor_id,
							'qoc_trackingUrl'	=> config('courierpartners.shipyaari.track_prefix').$consignment['tracking_id'][$i]
						]);
						

						$sendEmail = (new EmailHelper)->sendEmail('ShipmentConfirmation', [
							'Customersname'		=> @$consignmentData['data']['to_details']['customer_name'],
							'email'     		=> @$consignmentData['data']['to_details']['customer_email'],
							'order_order_id'	=> @$consignment['order_id']
						]);
					}
					$transferOrderUpdate = TransferOrder::where('fsto_id', $fsto_id)->update([
						'fsto_hasShipmentCreated'	=> 1
					]);
					$outs = true;
				}
			}
			return $outs;
		}
		catch (\Exception $e)
        {
            info("Shipyaari createShipment() Error");info($e);
			return false;
        }
	}
	public function cancelConsignment($order_id, $reason = '')
	{
		try
		{
			$shippingData = $this->shippingConsignment->select('id', 'order_id', 'shipping_id', 'tracking_id')->where('order_id', $order_id)->get();
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
	        		$cancellation = $this->operations->cancelConsignment([$sdata->tracking_id]);
					if($cancellation['status'])
			        {
			        	$cancelled++;
			            $this->shippingConsignment->where([
			                'shipping_type' => 'shipyaari',
			                'id'            => $sdata->id
			            ])->update([
			                'consignment_status'    => 4
			            ]);

			            $this->cancelConsignment->insert([
			                'order_id'      => $sdata->order_id,
			                'shipping_id'   => $sdata->shipping_id,
			                'cancel_reason' => $reason
			            ]);
			        }
	        	}
	            if($cancelled > 0)
	            {
	            	(new PartnerOrderUpdateRepository)->orderCancel(['orderID' => $order_id]);
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
	public function currentTrackingStatus($order_id)
	{
		try
		{
			$shippingData = $this->shippingConsignment->select('id', 'order_id', 'shipping_id', 'tracking_id')->where('order_id', $order_id)->get();
			$outs = [
				'status'    => 'error',
				'data'      => [],
				'message'   => 'Shipping Data not found'
			];
			if($shippingData)
			{
				$trackings = [];
				$x = 0;
				foreach ($shippingData as $sdata)
				{
					$trackingData = $this->operations->getCurrentTrackingData([$sdata->tracking_id]);
					if($trackingData['status'])
					{
						$x++;
						$outs['data'][] = $trackingData['details'];
					}
				}
				if($x > 0)
				{
					$outs['status'] = 'success';
					$outs['message'] = '';
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
	public function completeTrackingStatus($sdata)
	{
		try
		{
			$trackingData = $this->operations->getCompleteTrackingData([$sdata->tracking_id]);
			if($trackingData['status'])
			{
				$x++;
				$outs['data'][] = $trackingData;
				if(count($trackingData['details']['routemap']) > 0)
				{
					$this->consignmentTracking->where([
						['shipping_type', 'shipyaari'],
						['tracking_id', $sdata->tracking_id]
					])->delete();
					foreach ($trackingData['details']['routemap'] as $rp)
					{
						$this->consignmentTracking->insert([
							'shipping_type' => 'shipyaari',
							'tracking_id'   => $trackingData['details']['tracking_id'],
							'status_id'  	=> $rp['status_id'],
							'status_value'  => $rp['status_value'],
							'location'      => $rp['location'],
							'status_date'   => $rp['status_date']
						]);
						if($rp['status_value'] == 'Delivered')
						{
							$this->shippingConsignment->where([
								'shipping_type' => 'shipyaari',
								'id'            => $sdata->id
							])->update([
								'consignment_status'    => 3
							]);
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
	public function completeTrackingStatus_OLD($order_id)
	{
		try
		{
			$shippingData = $this->shippingConsignment->select('id', 'order_id', 'shipping_id', 'tracking_id')->where('order_id', $order_id)->get();
			$outs = [
				'status'    => 'error',
				'data'      => [],
				'message'   => 'Shipping Data not found'
			];
			if($shippingData)
			{
				$x = 0;
				foreach ($shippingData as $sdata)
				{
					$trackingData = $this->operations->getCompleteTrackingData([$sdata->tracking_id]);
					if($trackingData['status'])
					{
						$x++;
						$outs['data'][] = $trackingData;
						if(count($trackingData['details']['routemap']) > 0)
						{
							$this->consignmentTracking->where([
								['shipping_type', 'shipyaari'],
								['tracking_id', $sdata->tracking_id]
							])->delete();
							foreach ($trackingData['details']['routemap'] as $rp)
							{
								$this->consignmentTracking->insert([
									'shipping_type' => 'shipyaari',
									'tracking_id'   => $trackingData['details']['tracking_id'],
									'status_id'  	=> $rp['status_id'],
									'status_value'  => $rp['status_value'],
									'location'      => $rp['location'],
									'status_date'   => $rp['status_date']
								]);
								if($rp['status_value'] == 'Delivered')
								{
									$this->shippingConsignment->where([
										'shipping_type' => 'shipyaari',
										'id'            => $sdata->id
									])->update([
										'consignment_status'    => 3
									]);
								}
							}
						}
					}
				}
				if($x > 0)
				{
					$outs['status'] = 'success';
					$outs['message'] = '';
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
}