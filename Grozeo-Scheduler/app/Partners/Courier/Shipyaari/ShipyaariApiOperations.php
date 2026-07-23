<?php

namespace App\Partners\Courier\Shipyaari;

use App\Helpers\HttpCurlCalls;
use App\Http\Repositories\{
	StorageRepository,
	ShippingLogRepository
};

class ShipyaariApiOperations
{
	function __construct()
	{
		$baseURL = config('shipping.creds.shipyaari.baseURL');
		$this->searchAPI = $baseURL.config('shipping.creds.shipyaari.searchAPI');
		$this->createOrderAPI = $baseURL.config('shipping.creds.shipyaari.createOrderAPI');
		$this->trackAPI = $baseURL.config('shipping.creds.shipyaari.trackAPI');
		$this->cancelAPI = $baseURL.config('shipping.creds.shipyaari.cancelAPI');
		$this->labelAPI = $baseURL.config('shipping.creds.shipyaari.labelAPI');

		$this->curlCall = new HttpCurlCalls();
		$this->storage = new StorageRepository;
		$this->shipLog = new ShippingLogRepository;
		$this->request = new ShipyaariRequestFormat;
	}
	
	public function checkAvailablePartners($data, $token)
	{
		$outs = ["status" => "failed", 'data' => [], "message" => "Operation failed"];
		$apiHeader = $this->apiHeader($token);
		$request = $this->request->searchServiceRequest($data);

		$response = $this->curlCall->curlCall($this->searchAPI, json_encode($request), "POST", $apiHeader);
		$this->shipLog->createLog([
			'order_id'      => @$data['order_id'] ?? "-",
			'type'          => 1,
			'orderMethod'	=> 3,
			'APIHeaders'	=> @$apiHeader ? json_encode($apiHeader) : "",
			'APIName'		=> 'Check Available Partners',
			'APIURL'		=> $this->searchAPI,
			'request'      	=> @$request ? json_encode($request) : "",
			'response'      => @$response ? json_encode($response) : ""
		]);
		$outs["message"] = @$response->message ?? "Data not available";

		if(@$response->success == true)
        {
        	$outs['status'] = 'success';
            $outs['data'] = reset($response->data);
        }
		return $outs;
	}
	public function createConsignment($data, $partner, $token)
	{
		$outs = ["status" => "failed", 'data' => [], "message" => "Operation failed"];
		$apiHeader = $this->apiHeader($token);
		$request = $this->request->bookOrderRequest($data, $partner);
		$response = $this->curlCall->curlCall($this->createOrderAPI, json_encode($request), "POST", $apiHeader);
		$this->shipLog->createLog([
			'order_id'      => @$data['order_id'] ?? "-",
			'type'          => 1,
			'orderMethod'	=> 3,
			'APIHeaders'	=> @$apiHeader ? json_encode($apiHeader) : "",
			'APIName'		=> 'Create Order',
			'APIURL'		=> $this->createOrderAPI,
			'request'      	=> @$request ? json_encode($request) : "",
			'response'      => @$response ? json_encode($response) : ""
		]);
		$outs["message"] = @$response->message ?? "Data not available";

		if(@$response->success == true)
        {
        	$outs['status'] = 'success';
            $outs['data'] = [
            	"request"	=> $request,
            	"response"	=> $response
            ];
        }
		return $outs;
	}
	public function generateShippingLabel($awbID, $orderID = "", $token)
	{
		try
		{
			$outs = ["status" => "failed", "data" => "", "message" => "Operation failed"];
			$apiHeader = $this->apiHeader($token);
			$request = [
				"awbs"		=> [$awbID],
				"source"	=> "API"
			];
			$response = $this->curlCall->curlCall($this->labelAPI, json_encode($request), "POST", $apiHeader, 'all');
			$checkExtension = $this->storage->getFileExtension($response);
			
			$outs["message"] = "Unavailable";
			if($checkExtension)
			{
				$filePath = "partner/courier/label/{$orderID}-{$awbID}";
				$outs['status'] = 'success';
				$outs["data"] = $this->storage->s3PutItem($response, $filePath, $checkExtension);
			}
			$this->shipLog->createLog([
				'order_id'      => ($orderID != "") ? $orderID : "-",
				'type'          => 1,
				'orderMethod'	=> 3,
				'APIHeaders'	=> @$apiHeader ? json_encode($apiHeader) : "",
				'APIName'		=> 'Generate Shipping Label',
				'APIURL'		=> $this->labelAPI,
				'request'      	=> @$request ? json_encode($request) : "",
				'response'      => (@$outs["data"] ==  false) ? "" : $outs["data"]
			]);
			return $outs;
		}
		catch (\Exception $e)
        {
            info("ShipyaariApiOperations generateShippingLabel() Error");info($e);
			return ["status" => "failed", "data" => "", "message" => "Operation failed"];
		}
	}
	public function cancelConsignment($awbs, $orderID, $token)
	{
		$outs = ["status" => "failed", 'data' => [], "message" => "Operation failed"];
		$apiHeader = $this->apiHeader($token);
		$request = [
			"awbs"		=> $awbs,
		];
		$response = $this->curlCall->curlCall($this->cancelAPI, json_encode($request), "POST", $apiHeader);
		$this->shipLog->createLog([
			'order_id'      => ($orderID != "") ? $orderID : "-",
			'type'          => 1,
			'orderMethod'	=> 3,
			'APIHeaders'	=> @$apiHeader ? json_encode($apiHeader) : "",
			'APIName'		=> 'Cancel Shipment',
			'APIURL'		=> $this->cancelAPI,
			'request'      	=> @$request ? json_encode($request) : "",
			'response'      => @$response ? json_encode($response) : ""
		]);
		$outs["message"] = @$response->message ?? "Data not available";

		if(@$response->success == true)
        {
        	$outs['status'] = 'success';
            $outs['data'] = $response;
            $outs['orderID'] = $orderID;
        }
		return $outs;
	}
	public function getCompleteTrackingData($awb, $orderID, $token)
	{
		$outs = ["status" => "failed", 'data' => [], "message" => "Operation failed"];
		$apiHeader = $this->apiHeader($token);
		$url = strtr($this->trackAPI, ["{#trackID}" => $awb]);
		$response = $this->curlCall->curlCall($url, [], "GET", $apiHeader);

		$this->shipLog->createLog([
			'order_id'      => ($orderID != "") ? $orderID : "-",
			'type'          => 1,
			'orderMethod'	=> 3,
			'APIHeaders'	=> @$apiHeader ? json_encode($apiHeader) : "",
			'APIName'		=> 'Track Shipment',
			'APIURL'		=> $url,
			'request'      	=> "",
			'response'      => @$response ? json_encode($response) : ""
		]);
		$outs["message"] = @$response->message ?? "Data not available";

		if(@$response->success == true)
        {
        	$outs['status'] = 'success';
            $outs['data'] = $response;
        }
		return $outs;
	}

	private function apiHeader($token = "")
	{
		$header = ["Content-Type: application/json"];
		if($token)
		{
			array_push($header, "Authorization: Bearer {$token}");
		}
		return $header;
	}
}