<?php

namespace App\ExpressPartners\Uber;

use App\Helpers\HttpCurlCalls;
use App\Models\CourierDelivery\{
    ShippingConsignmentLog,
    OrderCourierPartnerSelections
};
use App\Models\TransferOrder;

class UberApiOperations
{
    protected $uberCustID, $availabilityURL;
    function __construct()
    {
        $this->uberCustID = config("expresspartners.uber.customerID");
        $this->availabilityURL = strtr(config("expresspartners.uber.checkAvailability"), ["{#customerID}" => $this->uberCustID]);
        $this->createConsignment = strtr(config("expresspartners.uber.createConsignment"), ["{#customerID}" => $this->uberCustID]);
        $this->listConsignment = strtr(config("expresspartners.uber.listConsignment"), ["{#customerID}" => $this->uberCustID]);
        $this->getConsignment = strtr(config("expresspartners.uber.getConsignment"), ["{#customerID}" => $this->uberCustID]);
        $this->updateConsignment = strtr(config("expresspartners.uber.updateConsignment"), ["{#customerID}" => $this->uberCustID]);
        $this->cancelConsignment = strtr(config("expresspartners.uber.cancelConsignment"), ["{#customerID}" => $this->uberCustID]);
        $this->deliveryDetails = strtr(config("expresspartners.uber.deliveryDetails"), ["{#customerID}" => $this->uberCustID]);
    }

    

    public function checkDeliveryAvailable($token, $data)
    {
        $outs = [
            'status'    => 'error',
            'data'      => [],
            'message'   => ""
        ];
        $apiHeaders = [
            "Content-Type: application/json",
            "Authorization: Bearer {$token}"
        ];
        $storeName = preg_replace('/[^A-Za-z0-9\s]/', '', @$data['from_details']['company_name']);
        $manifestTotal = (@$data['total']) ?? array_sum(array_column($data['package_details']['products'], 'price'));
        $checkingData = [
            "pickup_address"        => @$data['from_details']['address'],
            "dropoff_address"       => @$data['to_details']['address'],
            "external_store_id"     => @$data['from_details']['extStoreID'],
            "pickup_latitude"       => (double)@$data['from_details']['latitude'],
            "pickup_longitude"      => (double)@$data['from_details']['longitude'],
            "dropoff_latitude"      => (double)@$data['to_details']['latitude'],
            "dropoff_longitude"     => (double)@$data['to_details']['longitude'],
            "pickup_phone_number"   => @$data['from_details']['phone'],
            "dropoff_phone_number"  => @$data['to_details']['phone'],
            "manifest_total_value"  => ($manifestTotal * 100)
        ];
        $response = (new HttpCurlCalls)->curlCall($this->availabilityURL, json_encode($checkingData), 'POST', $apiHeaders);
        $outs['data'] = $response;
        $outs['message'] = @$response->message;
        if(@$response->id)
        {
            $outs['status'] = 'success';
            $outs['message'] = 'success';
        }
        return $outs;
    }

    public function createNewConsignment($token, $data, $deliveryID)
    {
        $outs = [
            'status'    => 'error',
            'data'      => [],
            'request'   => [],
            'message'   => ""
        ];
        $apiHeaders = [
            "Content-Type: application/json",
            "Authorization: Bearer {$token}"
        ];
        $symbol = config('app.def_currency_symbol');
        $note = ($data['payment_mode'] == "cod") ? "Please collect {$symbol} {$data['pending_amount']} from the customer" : "";
        $fields = [
            "quote_id"              => $deliveryID,
            "pickup_name"           => $data['from_details']['name'],
            "pickup_address"        => $data['from_details']['address'],
            "pickup_phone_number"   => $data['from_details']['phone'],
            "dropoff_name"          => $data['to_details']['name'],
            "dropoff_address"       => $data['to_details']['address'],
            "dropoff_phone_number"  => $data['to_details']['phone'],
            "pickup_business_name"  => $data['from_details']['name'],
            "pickup_latitude"       => (double)$data['from_details']['latitude'],
            "pickup_longitude"      => (double)$data['from_details']['longitude'],
            "manifest_items"        => $data['package_details']['products'],
            "pickup_notes"          => "",
            "pickup_verification"   => [
                "signature_requirement" => [
                    "enabled"                       => true,
                    "collect_signer_name"           => true,
                    "collect_signer_relationship"   => true
                ],
                "picture"               => true
            ],
            "dropoff_business_name" => $data['to_details']['name'],
            "dropoff_latitude"      => $data['to_details']['latitude'],
            "dropoff_longitude"     => (double)$data['to_details']['longitude'],
            "dropoff_notes"         => "",
            "dropoff_seller_notes"  => $note,
            "dropoff_verification"  => [
                "signature_requirement" => [
                    "enabled"                       => true,
                    "collect_signer_name"           => true,
                    "collect_signer_relationship"   => true
                ],
                "picture"               => true
            ],
            "manifest_reference"    => $data['order_id'],
            "manifest_total_value"  => (array_sum(array_column($data['package_details']['products'], 'price')) * 100),
            "idempotency_key"       => $data['order_id'],
            "external_store_id"     => $data['from_details']['extStoreID'],
            "return_verification"   => [
                "picture"               => true
            ],
            "external_id"           => $data['order_id']
        ];
        $response = (new HttpCurlCalls)->curlCall($this->createConsignment, json_encode($fields), 'POST', $apiHeaders);
        $outs['data'] = $response;
        $outs['request'] = $fields;
        $outs['message'] = @$response->message;
        if(@$response->id)
        {
            $outs['status'] = 'success';
            $outs['message'] = 'success';
        }

        return $outs;
    }

    public function getAllConsignments($token)
    {
        $outs = [
            'status'    => 'error',
            'data'      => [],
            'message'   => ""
        ];
        $apiHeaders = [
            "Content-Type: application/json",
            "Authorization: Bearer {$token}"
        ];
        $response = (new HttpCurlCalls)->curlCall($this->listConsignment, [], 'GET', $apiHeaders);
        $outs['data'] = $response;
        $outs['message'] = @$response->message;
        if(@$response->id)
        {
            $outs['status'] = 'success';
            $outs['message'] = 'success';
        }
        return $outs;
    }

    public function getDeliveryDetails($token, $shipment)
    {
        $outs = [
            'status'    => 'error',
            'data'      => [],
            'message'   => ""
        ];
        $apiHeaders = [
            "Content-Type: application/json",
            "Authorization: Bearer {$token}"
        ];
        $getConsignment = strtr($this->getConsignment, ["{#deliveryID}" => $shipment->tracking_id]);
        $response = (new HttpCurlCalls)->curlCall($getConsignment, [], 'GET', $apiHeaders);
        $outs['message'] = "Unable to get details";
        $outs['data'] = $response;
        if(@$response->id)
        {
            $outs['status'] = 'success';
            $outs['message'] = 'success';
        }
        return $outs;
    }
    public function cancelConsignment($token, $shipment)
    {

        $outs = [
            'status'    => 'error',
            'data'      => [],
            'message'   => ""
        ];
        $apiHeaders = [
            "Content-Type: application/json",
            "Authorization: Bearer {$token}"
        ];
        $cancelConsignment = strtr($this->cancelConsignment, ["{#deliveryID}" => $shipment->tracking_id]);
        $response = (new HttpCurlCalls)->curlCall($cancelConsignment, [], 'POST', $apiHeaders);
        return $response;
    }
}