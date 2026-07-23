<?php

namespace App\ExpressPartners\Uber;

use App\Helpers\HttpCurlCalls;
use App\Models\CourierDelivery\{
    OrderCourierPartnerSelections
};
use App\Models\TransferOrder;
use App\Http\Repositories\ShippingLogRepository;

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
        $fromPhone = $this->checkCountryCode(@$data['from_details']['phone']);
        $toPhone = $this->checkCountryCode(@$data['to_details']['phone']);
        $checkingData = [
            "pickup_address"        => @$data['from_details']['address'],
            "dropoff_address"       => @$data['to_details']['address'],
            "external_store_id"     => @$data['from_details']['extStoreID'],
            "pickup_latitude"       => (double)@$data['from_details']['latitude'],
            "pickup_longitude"      => (double)@$data['from_details']['longitude'],
            "dropoff_latitude"      => (double)@$data['to_details']['latitude'],
            "dropoff_longitude"     => (double)@$data['to_details']['longitude'],
            "pickup_phone_number"   => @$fromPhone,
            "dropoff_phone_number"  => @$toPhone,
            "manifest_total_value"  => (array_sum(array_column($data['package_details']['products'], 'price'))),
            // "pickup_ready_dt"       => now()->addMinutes(10)->format('Y-m-d\TH:i:s.000\Z')
        ];
        $error = "";
        $fromLatCheck = $this->checklatLongLength(@$data['from_details']['latitude']);
        $fromLngCheck = $this->checklatLongLength(@$data['from_details']['longitude']);
        $toLatCheck = $this->checklatLongLength(@$data['to_details']['latitude']);
        $toLngCheck = $this->checklatLongLength(@$data['to_details']['longitude']);

        $coordCheck = $fromLatCheck && $fromLngCheck && $toLatCheck && $toLngCheck;
        if($coordCheck)
        {
            $response = (new HttpCurlCalls)->curlCall($this->availabilityURL, json_encode($checkingData), 'POST', $apiHeaders);
        }
        else
        {
            $error = "All latitudes and longitudes must contain atleast three decimal places";
        }
        (new ShippingLogRepository)->createLog([
            'order_id'      => @$data['order_id'],
            'order_method'  => 1,
            'type'          => config('expresspartners.uber.local_id'),
            'APIName'       => "checkDeliveryAvailable",
            "APIURL"        => $this->availabilityURL,
            "APIHeaders"    => json_encode($apiHeaders),
            'request'       => json_encode($checkingData),
            'response'      => @$response ? json_encode($response) : $error
        ]);
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
        
        $fromPhone = $this->checkCountryCode(@$data['from_details']['phone']);
        $toPhone = $this->checkCountryCode(@$data['to_details']['phone']);
        $fields = [
            "quote_id"              => $deliveryID,
            "pickup_name"           => $data['from_details']['name'],
            "pickup_address"        => $data['from_details']['address'],
            "pickup_phone_number"   => @$fromPhone,
            "dropoff_name"          => $data['to_details']['name'],
            "dropoff_address"       => $data['to_details']['address'],
            "dropoff_phone_number"  => @$toPhone,
            "pickup_business_name"  => $data['from_details']['name'],
            "pickup_latitude"       => (double)$data['from_details']['latitude'],
            "pickup_longitude"      => (double)$data['from_details']['longitude'],
            "manifest_items"        => $data['package_details']['products'],
            "pickup_notes"          => "",
            "pickup_verification"   => [
                "signature_requirement" => [
                    "enabled"                       => false,
                    "collect_signer_name"           => false,
                    "collect_signer_relationship"   => false
                ],
                "picture"               => false
            ],
            "dropoff_business_name" => $data['to_details']['name'],
            "dropoff_latitude"      => (double)$data['to_details']['latitude'],
            "dropoff_longitude"     => (double)$data['to_details']['longitude'],
            "dropoff_notes"         => "",
            "dropoff_seller_notes"  => $data['to_details']['notes'],
            "dropoff_verification"  => [
                "signature_requirement" => [
                    "enabled"                       => false,
                    "collect_signer_name"           => false,
                    "collect_signer_relationship"   => false
                ],
                "picture"               => false
            ],
            "manifest_reference"    => $data['order_id'],
            "manifest_total_value"  => (array_sum(array_column($data['package_details']['products'], 'price'))),
            "idempotency_key"       => $data['order_id'],
            "external_store_id"     => $data['from_details']['extStoreID'],
            "return_verification"   => [
                "picture"               => false
            ],
            "external_id"           => $data['order_id'],
            "undeliverable_action"  => (($data['isRestaurant'] > 0) ? "leave_at_door" : "return")
        ];
        $error = "";
        $fromLatCheck = $this->checklatLongLength(@$data['from_details']['latitude']);
        $fromLngCheck = $this->checklatLongLength(@$data['from_details']['longitude']);
        $toLatCheck = $this->checklatLongLength(@$data['to_details']['latitude']);
        $toLngCheck = $this->checklatLongLength(@$data['to_details']['longitude']);

        $coordCheck = $fromLatCheck && $fromLngCheck && $toLatCheck && $toLngCheck;
        if($coordCheck)
        {
            $response = (new HttpCurlCalls)->curlCall($this->createConsignment, json_encode($fields), 'POST', $apiHeaders);
        }
        else
        {
            $error = "All latitudes and longitudes must contain atleast three decimal places";
        }
        (new ShippingLogRepository)->createLog([
            'order_id'      => @$data['order_id'],
            'order_method'  => 1,
            'type'          => config('expresspartners.uber.local_id'),
            'APIName'       => "createConsignment",
            "APIURL"        => $this->createConsignment,
            "APIHeaders"    => json_encode($apiHeaders),
            'request'       => json_encode($fields),
            'response'      => @$response ? json_encode($response) : $error
        ]);
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
        (new ShippingLogRepository)->createLog([
            'order_id'      => @$data['order_id'],
            'order_method'  => 1,
            'type'          => config('expresspartners.uber.local_id'),
            'APIName'       => "getAllConsignments",
            "APIURL"        => $this->listConsignment,
            "APIHeaders"    => json_encode($apiHeaders),
            'request'       => "",
            'response'      => json_encode($response)
        ]);
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
        (new ShippingLogRepository)->createLog([
            'order_id'      => @$data['order_id'],
            'order_method'  => 1,
            'type'          => config('expresspartners.uber.local_id'),
            'APIName'       => "getDeliveryDetails",
            "APIURL"        => $getConsignment,
            "APIHeaders"    => json_encode($apiHeaders),
            'request'       => "",
            'response'      => json_encode($response)
        ]);
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
        (new ShippingLogRepository)->createLog([
            'order_id'      => @$data['order_id'],
            'order_method'  => 1,
            'type'          => config('expresspartners.uber.local_id'),
            'APIName'       => "cancelConsignment",
            "APIURL"        => $cancelConsignment,
            "APIHeaders"    => json_encode($apiHeaders),
            'request'       => "",
            'response'      => json_encode($response)
        ]);
        return $response;
    }

    private function checkCountryCode($mobile = "")
    {
        $mobile = ltrim($mobile, '0');
        if(substr($mobile, 0, 1) != "+")
        {
            $phonecode = config('app.phonecode');
            $pos = strpos($mobile, $phonecode);
            if ($pos == false)
            {
                $mobile = $phonecode.$mobile;
            }
        }
        return $mobile;
    }
    private function checklatLongLength($number = 0)
    {
        $latarr = explode(".", $number);
        $decimalPart = @$latarr[1] ?? "";
        $digitCount = strlen($decimalPart);

        return ($digitCount >= 3) ? true : false;
    }
}