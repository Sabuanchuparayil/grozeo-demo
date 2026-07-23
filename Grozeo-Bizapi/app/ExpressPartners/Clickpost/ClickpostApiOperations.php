<?php

namespace App\ExpressPartners\Clickpost;

use App\Helpers\HttpCurlCalls;
use App\Models\CourierDelivery\{
    ShippingConsignmentLog,
    OrderCourierPartnerSelections
};
use App\Http\Repositories\ShippingLogRepository;

class ClickpostApiOperations
{
    protected $reqStructure, $recommendation, $createOrder, $orderDetails, $orderCancel, $orderTracking, $partnerID;
    function __construct()
    {
        $reqStr = config("expresspartners.clickpost.requestClass");
        $this->reqStructure = new $reqStr();

        $key = config("expresspartners.clickpost.key");
        $username = config("expresspartners.clickpost.username");
        $this->partnerID = config("expresspartners.clickpost.partnerID");
        $type = config('expresspartners.clickpost.local_id');

        $this->recommendation = strtr(config("expresspartners.clickpost.recommendation"), [
            "{#key}" => $key
        ]);
        $this->createOrder = strtr(config("expresspartners.clickpost.createOrder"), [
            "{#username}"   => $username,
            "{#key}"        => $key
        ]);
        $this->orderDetails = strtr(config("expresspartners.clickpost.orderDetails"), [
            "{#username}"   => $username,
            "{#partnerID}"  => $this->partnerID,
            "{#key}"        => $key
        ]);
        $this->orderCancel = strtr(config("expresspartners.clickpost.orderCancel"), [
            "{#username}"       => $username,
            "{#partnerID}"      => $this->partnerID,
            "{#key}"            => $key,
            "{#courierName}"    => $username
        ]);
        $this->orderTracking = strtr(config("expresspartners.clickpost.orderTracking"), [
            "{#username}"   => $username,
            "{#partnerID}"  => $this->partnerID,
            "{#key}"        => $key
        ]);
    }

    public function checkPartnerAvailability($data)
    {
        info("checkPartnerAvailability");
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
            'type'          => config('expresspartners.clickpost.local_id'),
            'APIName'       => "Recommendation",
            "APIURL"        => $this->recommendation,
            "APIHeaders"    => "",
            'request'       => json_encode($requestData),
            'response'      => @$partnerDetails ? json_encode($partnerDetails) : ""
        ]);
        $result = (@$partnerDetails->result) ?? NULL;
        $result = $result ? reset($result) : NULL;
        if(@$result->pincode_serviceable == true)
        {
            $partner = array_filter($result->preference_array, function($x){
                if($x->cp_id == $this->partnerID)
                {
                    return $x;
                }
            });
            $outs["status"] = "success";
            $outs["data"] = reset($partner);
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
            'type'          => config('expresspartners.clickpost.local_id'),
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
            'type'          => config('expresspartners.clickpost.local_id'),
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
            'type'          => config('expresspartners.clickpost.local_id'),
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