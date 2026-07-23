<?php

namespace App\CourierPartners\Clickpost;

use App\Helpers\HttpCurlCalls;
use App\Models\CourierDelivery\{
    ShippingConsignmentLog,
    OrderCourierPartnerSelections
};
use App\Http\Repositories\ShippingLogRepository;

class ClickpostApiOperations
{
    protected $reqStructure, $recommendation, $createOrder, $orderDetails, $orderCancel, $orderTracking;
    function __construct()
    {
        $reqStr = config("courierpartners.clickpost.requestClass");
        $this->reqStructure = new $reqStr();

        $key = config("courierpartners.clickpost.key");
        $username = config("courierpartners.clickpost.username");
        $partnerID = config("courierpartners.clickpost.partnerID");
        $type = config('courierpartners.clickpost.local_id');

        $this->recommendation = strtr(config("courierpartners.clickpost.recommendation"), [
            "{#key}" => $key
        ]);
        $this->createOrder = strtr(config("courierpartners.clickpost.createOrder"), [
            "{#username}"   => $username,
            "{#key}"        => $key
        ]);
        $this->orderDetails = strtr(config("courierpartners.clickpost.orderDetails"), [
            "{#username}"   => $username,
            "{#partnerID}"  => $partnerID,
            "{#key}"        => $key
        ]);
        $this->orderCancel = strtr(config("courierpartners.clickpost.orderCancel"), [
            "{#username}"       => $username,
            "{#partnerID}"      => $partnerID,
            "{#key}"            => $key,
            "{#courierName}"    => $username
        ]);
        $this->orderTracking = strtr(config("courierpartners.clickpost.orderTracking"), [
            "{#username}"   => $username,
            "{#partnerID}"  => $partnerID,
            "{#key}"        => $key
        ]);
    }

    public function checkPartnerAvailability($data)
    {
        $outs = [
            "status"    => "failed",
            "data"      => []
        ];
        $requestData = $this->reqStructure->recommendationRequest($data);
        $requestData = [$requestData];
        $partnerDetails = (new HttpCurlCalls)->curlCall($this->recommendation, json_encode($requestData), 'POST', ['Content-Type: application/json']);
        (new ShippingLogRepository)->createLog([
            'order_id'      => @$data['order_id'],
            'order_method'  => 3,
            'type'          => config('courierpartners.clickpost.local_id'),
            'APIName'       => "Recommendation",
            "APIURL"        => $this->recommendation,
            "APIHeaders"    => "",
            'request'       => json_encode($requestData),
            'response'      => @$partnerDetails ? json_encode($partnerDetails) : ""
        ]);
        $result = (@$partnerDetails->result) ? reset($partnerDetails->result) : NULL;
        if(@$result->pincode_serviceable == true)
        {
            $outs["status"] = "success";
            $outs["data"] = reset($result->preference_array);
        }
        return $outs;
    }

    public function createConsignment($data, $partner)
    {
        $outs = [
            "status"    => "failed",
            "request"   => [],
            "data"      => []
        ];
        $requestData = $this->reqStructure->consignmentRequest($data, $partner);
        $createConsignment = (new HttpCurlCalls)->curlCall($this->createOrder, json_encode($requestData), 'POST', ['Content-Type: application/json']);
        (new ShippingLogRepository)->createLog([
            'order_id'      => @$data['order_id'],
            'order_method'  => 3,
            'type'          => config('courierpartners.clickpost.local_id'),
            'APIName'       => "Create Order",
            "APIURL"        => $this->createOrder,
            "APIHeaders"    => "",
            'request'       => json_encode($requestData),
            'response'      => @$createConsignment ? json_encode($createConsignment) : ""
        ]);
        $outs['request'] = $requestData;
        $outs['data'] = $createConsignment;
        $outs['message'] = @$createConsignment->meta->message;
        if(@$createConsignment->meta->status == 200)
        {
            $outs['status'] = 'success';
            $outs['message'] = '';
        }
        return $outs;
    }

    public function cancelConsignment($order_id, $shipmentID)
    {
        $outs = [
            "status"    => "failed",
            "data"      => [],
            "message"   => ""
        ];
        $url = strstr($this->orderCancel, ["{#wayBillID}" => $shipmentID]);
        $cancellation = (new HttpCurlCalls)->curlCall($url, [], 'GET', ['Content-Type: application/json']);
        (new ShippingLogRepository)->createLog([
            'order_id'      => $order_id,
            'order_method'  => 3,
            'type'          => config('courierpartners.clickpost.local_id'),
            'APIName'       => "Order Cancellation",
            "APIURL"        => $url,
            "APIHeaders"    => "",
            'request'       => "",
            'response'      => @$cancellation ? json_encode($cancellation) : ""
        ]);
        $outs["message"] = @$cancellation->meta->message;
        $outs['data'] = $cancellation;
        if(@$cancellation->meta->success == true)
        {
            $outs["status"] = "success";
        }
        return $outs;
    }

    public function consignmentTracking($order_id, $shipmentID)
    {
        $outs = [
            "status"    => "failed",
            "data"      => [],
            "message"   => ""
        ];
        $url = strstr($this->orderTracking, ["{#wayBillID}" => $shipmentID]);
        $tracking = (new HttpCurlCalls)->curlCall($url, [], 'GET', ['Content-Type: application/json']);
        (new ShippingLogRepository)->createLog([
            'order_id'      => $order_id,
            'order_method'  => 3,
            'type'          => config('courierpartners.clickpost.local_id'),
            'APIName'       => "Order Tracking",
            "APIURL"        => $url,
            "APIHeaders"    => "",
            'request'       => "",
            'response'      => @$tracking ? json_encode($tracking) : ""
        ]);
        $outs["message"] = @$tracking->meta->message;
        $outs['data'] = $tracking;
        if(@$tracking->meta->status == 200)
        {
            $outs["status"] = "success";
        }
        return $outs;
    }
}