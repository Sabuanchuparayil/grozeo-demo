<?php

namespace App\CourierPartners\Shipyaari;

use App\Models\{
	Order,
	QugeoOrder,
	TransferOrder,
	CourierDelivery\OrderCourierPartnerSelections
};
use App\Events\OrderHistory;
use App\Http\Repositories\ShippingLogRepository;
use App\CourierPartners\Shipyaari\ShipyaariPartnerFinder;

class ShipyaariApiOperations
{
	protected $avnKey;
	protected $userName;
	protected $creator;

	function __construct()
	{
		$this->avnKey = config('courierpartners.shipyaari.avnkey');
		$this->userName = config('courierpartners.shipyaari.username');
		$this->creator = config('courierpartners.shipyaari.creator');
	}
	public function checkPartnerAvailability($data)
	{
		$url = config('courierpartners.shipyaari.search_partners');
		$data['avnkey'] = $this->avnKey;
		$data['service'] = 'Standard';
		$order_id = @$data['order_id'];
		unset($data['order_id']);
		$response = $this->curlCall($url, http_build_query($data), 'POST',['Content-Type: application/x-www-form-urlencoded']);
		(new ShippingLogRepository)->createLog([
			'order_id'      => $order_id ?? "-",
			'type'          => config('courierpartners.shipyaari.local_id'),
			'APIName'		=> 'Partner List',
			'APIURL'		=> $url,
			'request'      	=> @$data ? json_encode($data): "",
			'response'      => @$response ? json_encode($response): ""
		]);
		if($response)
		{
			if(@$response->status)
			{
				$resp = new \stdClass();
				$resp->status = 'error';
				$resp->message = $response->status;
				return $resp;
			}
			else
			{
				return $response;
			}
		}
		$resp = new \stdClass();
		$resp->status = 'error';
		$resp->message = 'Some error occured';
		return $resp;
	}
	public function createConsignment($data)
	{
		try
		{
			$url = config('courierpartners.shipyaari.create_consignment');
			
			//default values
			$fields['username'] = base64_encode($this->userName);
			$fields['avnkey'] = base64_encode($this->avnKey);
			$fields['created_by'] = base64_encode($this->creator);
			$fields['package_type'] = base64_encode('Identical');
			$fields['package_content'] = base64_encode('product');
			$fields['package_content_desc'] = "";
			$fields['channel'] = "API";
			$fields['no_of_packages'] = base64_encode('1');
			$fields['ship_date'] = base64_encode(date('Y-m-d'));
			$fields['customer_alternative_contact_no'] = "";
			$fields['package_name'] = "";
			$fields['partner_id'] = "";

			// order id
			$fields['order_id'] =  @base64_encode($data['order_id']);

			// store details
			$fields['from_company_name'] =  @base64_encode($data['from_details']['company_name']);
			$fields['from_contact_number'] =  @base64_encode($data['from_details']['phone']);
			$fields['from_address'] =  @base64_encode($data['from_details']['address1']);
			$fields['from_address2'] =  @base64_encode($data['from_details']['address2']);
			$fields['from_landmark'] =  @base64_encode($data['from_details']['landmark']);
			$fields['from_pincode'] =  @base64_encode($data['from_details']['pincode']);

			// customer details
			$fields['customer_name'] =  @base64_encode($data['to_details']['customer_name']);
			$fields['customer_email'] =  @base64_encode($data['to_details']['customer_email']);
			$fields['customer_contact_no'] =  @base64_encode($data['to_details']['customer_phone']);
			$fields['to_address'] =  @base64_encode($data['to_details']['address1']);
			$fields['to_address2'] =  @base64_encode($data['to_details']['address2']);
			$fields['to_landmark'] =  @base64_encode($data['to_details']['landmark']);
			$fields['to_pincode'] =  @base64_encode($data['to_details']['pincode']);

			// product details
			$splitter = ceil(count($data['package_details']['products']) / count($data['package_details']['package']));
			$productList = (count($data['package_details']['package']) > 1) ? array_chunk($data['package_details']['products'], $splitter) : [$data['package_details']['products']];
			$p = 0;
			$partnerDetails = [
				'paymentmode'		=> $data['payment_mode'],
				'pickup_pincode'	=> (int)$data['from_details']['pincode'],
				'delivery_pincode'	=> (int)$data['to_details']['pincode'],
				"invoicevalue"		=> 0,
				"length"			=> 0,
				"width"				=> 0,
				"height"			=> 0,
				"weight"			=> 0,
				'order_id'			=> $data['order_id']
			];
			foreach ($data['package_details']['package'] as $pdetails)
			{
				$fields['product_data'][] = [
					'package_weight'	=> $pdetails['weight'],
		            'package_length'	=> $pdetails['length'],
		            'package_width'		=> $pdetails['width'],
		            'package_height'	=> $pdetails['height'],
		            'total'				=> array_sum(array_column($productList[$p], 'total')),
		            'total_qty'			=> count($productList[$p]),
		            'cod_amt'			=> $data['pending_amount'],
		            'package_details'	=> $productList[$p]
				];
				/* $partnerDetails['length'] = $pdetails['length'];
				$partnerDetails['width'] = $pdetails['width'];
				$partnerDetails['height'] = $pdetails['height']; */
				$partnerDetails['weight'] += $pdetails['weight'];
				$partnerDetails['invoicevalue'] += array_sum(array_column($productList[$p], 'total'));
				$p++;
			}
			$total_amount = array_sum(array_column($data['package_details']['products'], 'total'));

			$fields['total_invoice_value'] =  @base64_encode($total_amount);
			$fields['total_price_set'] =  @$total_amount;
			$fields['collect_amt'] =  @base64_encode($data['pending_amount']);
			$fields['payment_mode'] = base64_encode($data['payment_mode']);
			
			$getPartnerID = $this->checkExistingDeliveryPartner($partnerDetails, $data['delivery_charge_et']);
			$fields['partner_id'] = "";
			if(@$getPartnerID['status'] == 2)
			{
				(new ShippingLogRepository)->createLog([
					'order_id'      => $data['order_id'],
					'type'          => config('courierpartners.shipyaari.local_id'),
					'request'		=> '',
					'response'      => "Unable to create shipment due to delivery charge less than or equal to ".$data['delivery_charge_et']." not available."
				]);
				(new ShippingLogRepository)->createLog([
					'order_id'      => $data['order_id'],
					'type'          => config('courierpartners.shipyaari.local_id'),
					'request'		=> 'Consignment Request',
					'response'      => json_encode($fields)
				]);
				Order::where('order_order_id', $data['order_id'])->update([
					'status_id'	=> 55
				]);
				event(new OrderHistory($data['orderID'], 55));
				TransferOrder::where('fsto_id', $data['fsto_id'])->update([
					'fsto_status'	=> 21
				]);
				QugeoOrder::where('quor_TransferOrder_id', $data['fsto_id'])->update([
					'quor_Status'	=> 41
				]);
				$response = ['status' => false];
			}
			if(@$getPartnerID['status'] == 0)
			{
				(new ShippingLogRepository)->createLog([
					'order_id'      => $data['order_id'],
					'type'          => config('courierpartners.shipyaari.local_id'),
					'request'		=> '',
					'response'      => @$getPartnerID['data']
				]);
				(new ShippingLogRepository)->createLog([
					'order_id'      => $data['order_id'],
					'type'          => config('courierpartners.shipyaari.local_id'),
					'request'		=> 'Consignment Request',
					'response'      => json_encode($fields)
				]);
				$response = ['status' => false];
			}
			else
			{
				$fields['partner_id'] = base64_encode($getPartnerID['data']);
				$details = $this->curlCall($url, json_encode($fields), 'POST', ['Content-Type: application/json']);
				$response = ['status' => false];
				(new ShippingLogRepository)->createLog([
					'order_id'      => $data['order_id'],
					'type'          => config('courierpartners.shipyaari.local_id'),
					'request'       => json_encode($fields),
					'response'      => json_encode($details)
				]);
				if($details)
				{
					if($details->status == 'success')
					{
						$response = [
							'status'			=> $details->status,
							'order_id'			=> $details->client_order_id,
							'shipment_label'	=> explode(',', $details->shipment_label),
							'tracking_id'		=> explode(',', $details->tracking_number),
							'partner_id'		=> $details->partner_id,
							'partner_name'		=> $details->partner_name,
							'shipping_charge'	=> $details->total_applicable_charge,
							'shipping_id'		=> explode(',', $details->avn_shipping_id),
							'pickupdate'		=> $details->pickupdate,
							'request'			=> $fields,
							'response'			=> $details
						];
					}
					else
					{
						$response['message'] = ((@$details->status_code) ? ($details->status_code.". ") : "").$details->status;
					}
				}
			}
			return $response;
		}
		catch (\Exception $e)
        {
        	info("ShipyaariApiOperations");info($e);
            return [
            	'status'	=> 'error',
            	'message'	=> $e->getMessage()
            ]; 
        }
	}
	public function cancelConsignment($tracking_id)
	{
		$url = config('courierpartners.shipyaari.cancel_consignment');
		$fields['avn_key'] = $this->avnKey;
		$fields['ids'] = $tracking_id;

		$details = $this->curlCall($url, json_encode($fields), 'POST', ['Content-Type: application/json']);
		$response = ['status' => false];
		if($details)
		{
			$response['status'] = $details->status;
			if(@$details->data)
			{
				$response['details'] = array_map(
					function ($data)
					{
						return [
							'id'		=> $data->shipyaari_id,
							'status'	=> $data->status
						];
					}, $details->data);
			}
			else
			{
				$response['details'] = $details->message;
			}
		}
		return $response;
	}
	public function getCurrentTrackingData($tracking_id)
	{
		$url = config('courierpartners.shipyaari.track_current_status');
		$fields['avn_key'] = $this->avnKey;
		$fields['tracking_number'] = $tracking_id;
		$details = $this->curlCall($url, json_encode($fields), 'POST', ['Content-Type: application/json']);
		$response = ['status' => false];
		if($details)
		{
			$response['status'] = $details->status;
			if(@$details->data)
			{
				$response['details'] = array_map(
					function ($data)
					{
						return [
							'tracking_id'		=> $data->tracking_number,
							'status_code'		=> $data->status_code,
							'current_status'	=> $data->current_status,
							'status_date'		=> date('d-m-Y', strtotime($data->status_date))
						];
					}, $details->data);
			}
			else
			{
				$response['details'] = $details->msg;
			}
		}
		return $response;
	}
	public function getCompleteTrackingData($tracking_id)
	{
		$url = config('courierpartners.shipyaari.track_complete_status');
		$fields['avn_key'] = $this->avnKey;
		$fields['tracking_number'] = $tracking_id;

		$details = $this->curlCall($url, json_encode($fields), 'POST', ['Content-Type: application/json']);
		$response = ['status' => false];
		if($details)
		{
			$response['status'] = $details->status;
			if($details->status)
			{
				if(@$details->result)
				{
					$result = reset($details->result)->msg;
					// return $result->msg;
					$response['details'] = [
						'shipping_id'		=> $result->id,
						'tracking_id'		=> $result->tracking_number,
						'routemap'			=> []
					];
					if(!empty($result->checkpoints))
					{
						$response['details']['routemap'] = array_map(function ($checkpoint)
						{
							return [
								'location'		=> $checkpoint->location,
								'status_id'		=> @$checkpoint->status_code,
								'status_value'	=> $checkpoint->tag,
								'status_date'	=> date('Y-m-d H:i:s', strtotime($checkpoint->checkpoint_time))
							];
						}, (array)$result->checkpoints);
					}
				}
				else
				{
					$response['status'] = false;
					$response['details'] = $details->msg;
				}
			}
			else
			{
				$response['details'] = $details->msg;
			}
		}
		return $response;
	}

	private function checkExistingDeliveryPartner($partnerDetails, $charge)
	{
		$partnerList = $this->checkPartnerAvailability($partnerDetails);
		$outs = [
			'status'	=> 0,
			'data'		=> @$partnerList->message ?? 'Operation failed'
		];
		if(!isset($partnerList->status))
		{
			$partner = reset($partnerList);
			if(@$partner->partner_id)
			{
				switch(config('app.deficient_delivery_enabled'))
				{
					case 0:
						$outs = ['status' => 1, 'data' => $partner->partner_id];
						break;
					case 1:
						if($partner->delivery_charge <= $charge)
						{
							$outs = ['status' => 1, 'data' => $partner->partner_id];
						}
						else
						{
							$outs = ['status' => 2, 'data' => 0];
						}
						break;
					
					default:
						$outs = [
							'status'	=> 0,
							'data'		=> @$partnerList->message ?? 'Operation failed'
						];
						break;
				}
			}
		}
		return $outs;
		/* if(@$partnerList->status != 'error')
		{
			$findBestPartner = (new ShipyaariPartnerFinder)->findBestPartner($partnerList, $partnerDetails['weight'], $charge);
			if(@$findBestPartner->partner_id)
			{
				OrderCourierPartnerSelections::create([
					'order_id'			=> $order_id,
					'courier_partner'   => config("courierpartners.shipyaari.local_id"),
                    'partner_id'        => @$findBestPartner->partner_id,
                    'courier_name'      => @$findBestPartner->courier_name,
                    'service_name'      => @$findBestPartner->service_name,
                    'delivery_charge'   => @$findBestPartner->delivery_charge,
                    'selected_data'     => json_encode(@$findBestPartner),
                    'whole_response'    => json_encode(@$partnerDetails)
				]);
				return [
					'status'	=> 1,
					'data'		=> $findBestPartner->partner_id
				];
			}
			else
			{
				return [
					'status'	=> 0,
					'data'		=> 0
				];
			}
		}
		else
		{
			return [
				'status'	=> -1,
				'data'		=> @$partnerList->message
			];
		}
		return [
			'status'	=> -1,
			'data'		=> 'Operation failed'
		]; */
	}


	

	private function curlCall($url, $data, $method, $header)
	{
		$curl = curl_init();
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL				=> $url,
				CURLOPT_RETURNTRANSFER	=> true,
				CURLOPT_ENCODING		=> '',
				CURLOPT_MAXREDIRS		=> 10,
				CURLOPT_TIMEOUT			=> 0,
				CURLOPT_FOLLOWLOCATION	=> true,
				CURLOPT_HTTP_VERSION	=> CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST	=> $method,
				CURLOPT_POSTFIELDS		=> $data,
				CURLOPT_HTTPHEADER		=> $header
			)
		);
		$response = curl_exec($curl);
		if (curl_errno($curl))
		{
			return json_decode(curl_error($curl));
		}
		curl_close($curl);
		return json_decode($response);
	}
}