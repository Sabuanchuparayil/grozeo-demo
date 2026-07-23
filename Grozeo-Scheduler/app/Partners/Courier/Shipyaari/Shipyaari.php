<?php

namespace App\Partners\Courier\Shipyaari;

use Carbon\Carbon;
use App\Partners\{
	BaseDeliveryPartner,
	BaseDeliveryRequest,
	DeliveryPartnerInterface
};
use App\Models\CourierDelivery\{
    ShippingConsignment,
    ConsignmentTracking,
    CancelConsignment
};

class Shipyaari extends BaseDeliveryPartner implements DeliveryPartnerInterface
{
	protected $operations, $auth, $requests, $storage;

	function __construct()
	{
		$this->auth = new ShipyaariAuthorization;
		$this->requests = new BaseDeliveryRequest;
		$this->operations = new ShipyaariApiOperations;
	}

	public function checkDeliveryPartner($params)
	{
    	try
		{
			$getToken = $this->auth->generateAuthToken();
			if(@$getToken['status'] != 'success') { return $this->errorResponse($getToken); }

			$token = $getToken["message"];
			$getPartner = $this->operations->checkAvailablePartners($params, $token);
			if(@$getPartner['status'] != 'success') { return $this->errorResponse($getPartner); }

			return $this->successResponse($getPartner, 1);
		}
		catch (\Exception $e)
        {
            info("Shipyaari bookShipment() Error");info($e);
            return $this->errorResponse(["status" => "failed", "message" => $e->getMessage()]);
        }
	}
    public function bookShipment($fsto_id)
    {
    	try
		{
			$getToken = $this->auth->generateAuthToken();
			if(@$getToken['status'] != 'success') { return $this->errorResponse($getToken); }
			
			$token = $getToken["message"];

			$requestData = $this->requests->createConsignmentRequest($fsto_id);
			if(@$requestData['status'] != 'success') { return $this->errorResponse($requestData); }

			$getPartner = $this->operations->checkAvailablePartners($requestData['data'], $token);
			if(@$getPartner['status'] != 'success') { return $this->errorResponse($getPartner); }

			$consignment = $this->operations->createConsignment(@$requestData['data'], @$getPartner['data'], $token);
			if(@$consignment['status'] != 'success') { return $this->errorResponse($consignment); }

			$response = [];
			$consignmentData = reset($consignment['data']['response']->data);
			$orderId = $consignmentData->orderId;
			$response['status'] = 'success';
			$response['message'] = @$consignment['data']['response']->message ?? "Operation failed";
			foreach ($consignmentData->awbs as $awb)
			{
				$shippingLabel = $this->operations->generateShippingLabel($awb->tracking->awb, $orderId, $token);
				$shippingLabel = (@$shippingLabel['status'] == 'success') ? @$shippingLabel['data'] : "";
				$pickupDate = Carbon::createFromTimestamp($awb->pickupDate);
				$response['data'][] = [
					"order_id"			=> $orderId,
					"order_method"		=> "3",
					"shipping_type"		=> "shipyaari",
					"shipping_partner"	=> json_encode([
						"partner_id"		=> $awb->charges->partnerServiceId,
						"partner_name"		=> $awb->charges->partnerServiceName,
					]),
					"status"			=> 1,
					"shipping_charge"	=> $awb->charges->total,
					"request"			=> $consignment["data"]["request"],
					"response"			=> $consignment["data"]["response"],
					"shipping_id"		=> $awb->tracking->awb,
					"tracking_id"		=> $awb->tracking->awb,
					"shipping_label"	=> $shippingLabel,
					"tracking_link"		=> strtr(config("shipping.creds.shipyaari.tracking"), ["{#trackID}" => $awb->tracking->awb]),
					"pickup_time"		=> $pickupDate->toDateTimeString(),
					"customerName"		=> @$requestData['data']['to_details']['name'],
					"customerEmail"		=> @$requestData['data']['to_details']['email']
				];
			}
			return $this->successResponse($response, 2);
		}
		catch (\Exception $e)
        {
            info("Shipyaari bookShipment() Error");info($e);
            return $this->errorResponse(["status" => "failed", "message" => $e->getMessage()]);
        }
    }
    public function cancelShipment($orderID)
    {
    	try
		{
			$shipments = $this->getShipmentDetails($orderID, 'shipyaari');
			if(!$shipments) { return $this->errorResponse(["status" => "failed", "message" => "Shipment not available"]); }
			$getToken = $this->auth->generateAuthToken();
			if(@$getToken['status'] != 'success') { return $this->errorResponse($getToken); }
			
			$token = $getToken["message"];
			$awbs = $shipments->pluck('tracking_id')->toArray();
			$cancellation = $this->operations->CancelConsignment($awbs, $orderID, $token);
			if(@$cancellation['status'] != 'success') { return $this->errorResponse($cancellation); }

			return $this->successResponse($cancellation, 3);
		}
		catch (\Exception $e)
        {
            info("Shipyaari cancelShipment() Error");info($e);
            return $this->errorResponse(["status" => "failed", "message" => $e->getMessage()]);
        }
    }
    public function trackShipment($orderID)
    {
    	try
		{
			$shipments = $this->getShipmentDetails($orderID, 'shipyaari');
			if(!$shipments) { return $this->errorResponse(["status" => "failed", "message" => "Shipment not available"]); }
			$getToken = $this->auth->generateAuthToken();
			if(@$getToken['status'] != 'success') { return $this->errorResponse($getToken); }
			
			$token = $getToken["message"];
			$outs = [];
			foreach ($shipments as $ship)
			{
				$tracking = $this->operations->getCompleteTrackingData($ship->tracking_id, $orderID, $token);
				if(@$tracking['status'] == 'success')
				{
					$outs[] = $this->formatTrackingDetails($tracking['data']);
				}
			}
			if(count($outs) == 0) { return $this->errorResponse(["status" => "failed", "message" => "Tracking not available"]); }
			return $this->successResponse(["status" => "success", "data" => $outs, "message" => "success"], 4);
		}
		catch (\Exception $e)
        {
            info("Shipyaari trackShipment() Error");info($e);
            return $this->errorResponse(["status" => "failed", "message" => $e->getMessage()]);
        }
    }
    public function webhook($request)
    {
    	try
		{
		}
		catch (\Exception $e)
        {
            info("Shipyaari webhook() Error");info($e);
            return $this->errorResponse(["status" => "failed", "message" => $e->getMessage()]);
        }
    }

    private function formatTrackingDetails($details)
    {
    	// return $details;
    	$trackingData = @reset($details->data);
    	$trackingData = @reset($trackingData->trackingInfo);
    	// return $trackingData;
    	$currentStatus = @$trackingData->currentStatus;
    	$statusTime = @$trackingData->lastScanDatePartner ?? time();
    	$statusTime = Carbon::createFromTimestamp($statusTime);
    	$outs = [
    		"orderID"				=> $trackingData->orderId,
    		"trackingID"			=> $trackingData->awb,
    		"consignment_status"	=> 0,
    		"status_value"			=> $currentStatus,
    		"location"				=> "",
    		"status_time"			=> $statusTime->toDateTimeString(),
    		"response"				=> json_encode($details)
    	];

    	switch ($currentStatus)
    	{
    		case 'BOOKED':
    			$outs['consignment_status'] = 1;
    			break;

    		case 'PICKED UP':
    			$outs['consignment_status'] = 2;
    			break;

    		case 'RTO DELIVERED':
    		case 'DELIVERED':
    			$outs['consignment_status'] = 3;
    			break;

    		case 'CANCELLED':
    			$outs['consignment_status'] = 4;
    			break;
    		
    		default:
    			break;
    	}
    	return $outs;
    }
}