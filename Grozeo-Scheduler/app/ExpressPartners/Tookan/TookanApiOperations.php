<?php

namespace App\ExpressPartners\Tookan;

use App\Models\{
    Order,
    QugeoOrder,
    TransferOrder
};
use App\Events\OrderHistory;
use App\Helpers\HttpCurlCalls;
use App\Http\Repositories\ShippingLogRepository;

class TookanApiOperations
{
    protected $apiKey, $nearbyAgentsURL, $fareEstimateURL, $regionFromPointsURL, $createTaskURL;
    function __construct()
    {
        $this->apiKey = config("expresspartners.tookan.apiKey");
        $this->nearbyAgentsURL = config("expresspartners.tookan.nearbyAgents");
        $this->fareEstimateURL = config("expresspartners.tookan.fareEstimate");
        $this->regionFromPointsURL = config("expresspartners.tookan.regionFromPoints");
        $this->createTaskURL = config("expresspartners.tookan.createTask");
    }

    public function checkifAgentAvailable($data)
    {
        $data["radius_in_metres"] = config("expresspartners.tookan.checkRadius");
        $order_id = $data['order_id'];
        unset($data['order_id']);

        $headers = ["Content-Type: application/json"];

        $response = (new HttpCurlCalls)->curlCall($this->nearbyAgentsURL, json_encode($data), 'POST', $headers);
        (new ShippingLogRepository)->createLog([
            'order_id'      => $order_id,
            'order_method'  => 1,
            'type'          => config('expresspartners.tookan.local_id'),
            'request'       => json_encode($data),
            'response'      => json_encode($response)
        ]);
        if(@$response->status == 200)
        {
            return true;
        }
        return false;
    }
    public function getRegionFromPoints($data)
    {
        $order_id = $data['order_id'];
        unset($data['order_id']);
        $headers = ["Content-Type: application/json"];

        $response = (new HttpCurlCalls)->curlCall($this->regionFromPointsURL, json_encode($data), 'POST', $headers);
        (new ShippingLogRepository)->createLog([
            'order_id'      => $order_id,
            'order_method'  => 1,
            'type'          => config('expresspartners.tookan.local_id'),
            'request'       => json_encode($data),
            'response'      => json_encode($response)
        ]);
        if(@$response->status == 200)
        {
            $teamID = @reset(reset($response->data)->team_data)->team_id ?? config("expresspartners.tookan.defTeamID");
            return @$teamID ?? false;
        }
        return false;
    }
    public function getFareEstimate($data)
    {
        $data["template_name"] = config("expresspartners.tookan.templateName");
        $data["formula_type"] = config("expresspartners.tookan.formulaType");
        $order_id = $data['order_id'];
        unset($data['order_id']);

        $headers = ["Content-Type: application/json"];

        $response = (new HttpCurlCalls)->curlCall($this->fareEstimateURL, json_encode($data), 'POST', $headers);
        (new ShippingLogRepository)->createLog([
            'order_id'      => $order_id,
            'order_method'  => 1,
            'type'          => config('expresspartners.tookan.local_id'),
            'request'       => json_encode($data),
            'response'      => json_encode($response)
        ]);
        if(@$response->status == 200)
        {
            $fare = @$response->data->estimated_fare ?? 0;
            return $fare;
        }
        return 0;
    }

    public function createNewConsignment($data)
    {
        $outs = [
            'status'    => 'error',
            'data'      => [],
            'request'   => [],
            'message'   => ""
        ];
        $checkFareReq = [
            'api_key'               => $data['api_key'],
            'order_id'              => $data['order_id'],
            'pickup_latitude'       => $data['from_details']['latitude'],
            'pickup_longitude'      => $data['from_details']['longitude'],
            'delivery_latitude'     => $data['to_details']['latitude'],
            'delivery_longitude'    => $data['to_details']['longitude']
        ];
        $checkFare = $this->getFareEstimate($checkFareReq);
        if($checkFare > $data['delivery_charge'])
        {
            (new ShippingLogRepository)->createLog([
                'order_id'      => $data['order_id'],
                'order_method'  => 1,
                'type'          => config('expresspartners.tookan.local_id'),
                'request'       => '',
                'response'      => "Unable to create task because order delivery charge is {$data['delivery_charge']} and tookan delivery charge is {$checkFare}."
            ]);
            Order::where('order_order_id', $data['order_id'])->update([
                'status_id' => 55
            ]);
            event(new OrderHistory($data['orderID'], 55));
            TransferOrder::where('fsto_id', $data['fsto_id'])->update([
                'fsto_status'   => 21
            ]);
            QugeoOrder::where('quor_TransferOrder_id', $data['fsto_id'])->update([
                'quor_Status'   => 41
            ]);
            $outs['message'] = "Unable to create task because order delivery charge is {$data['delivery_charge']} and tookan delivery charge is {$checkFare}.";
        }
        else
        {
            $description = ($data['hasRestaurant'] == 1) ? "Restaurant Order Delivery" : "Grocery Delivery";
            $fromPhone = $this->checkCountryCode(@$data['from_details']['phone']);
            $toPhone = $this->checkCountryCode(@$data['to_details']['phone']);
            $metaData = [
                [
                    "label" => "Payment_Mode",
                    "data"  => $data['payment_mode']
                ]
            ];
            if($data['payment_mode'] == "CASH ON DELIVERY")
            {
                $metaData[] = [
                    "label" => "Payable Amount",
                    "data"  => $data['pending_amount']
                ];
            }
            $fields = [
                "api_key"                       => $data['api_key'],
                "order_id"                      => $data['order_id'],
                "team_id"                       => "",
                "auto_assignment"               => 1,
                "job_description"               => $description,
                "job_pickup_phone"              => $fromPhone,
                "job_pickup_name"               => $data['from_details']['name'],
                "job_pickup_email"              => $data['from_details']['email'],
                "job_pickup_address"            => $data['from_details']['address'],
                "job_pickup_latitude"           => $data['from_details']['latitude'],
                "job_pickup_longitude"          => $data['from_details']['longitude'],
                "job_pickup_datetime"           => $data['pickup_date'],
                "customer_username"             => $data['to_details']['name'],
                "customer_email"                => $data['to_details']['email'],
                "customer_phone"                => $toPhone,
                "customer_address"              => $data['to_details']['address'],
                "latitude"                      => $data['to_details']['latitude'],
                "longitude"                     => $data['to_details']['longitude'],
                "job_delivery_datetime"         => $data['delivery_date'],
                "has_pickup"                    => 1,
                "has_delivery"                  => 1,
                "layout_type"                   => 0,
                "tracking_link"                 => 1,
                "timezone"                      => config("expresspartners.tookan.timezone"),
                "custom_field_template"         => config("expresspartners.tookan.templateName"),
                "meta_data"                     => $metaData,
                "pickup_custom_field_template"  => config("expresspartners.tookan.templateName"),
                "pickup_meta_data"              => $metaData,
                "fleet_id"                      => "",
                "notify"                        => 1,
                "tags"                          => "",
                "geofence"                      => 0,
                "ride_type"                     => 0
            ];
            $headers = ["Content-Type: application/json"];

            $response = (new HttpCurlCalls)->curlCall($this->createTaskURL, json_encode($fields), 'POST', $headers);
            $outs['data'] = $response;
            $outs['request'] = $fields;
            $outs['message'] = @$response->message;
            (new ShippingLogRepository)->createLog([
                'order_id'      => $data['order_id'],
                'order_method'  => 1,
                'type'          => config('expresspartners.tookan.local_id'),
                'request'       => json_encode($fields),
                'response'      => json_encode($response)
            ]);
            if(@$response->status == 200)
            {
                $outs['data']->tookanFare = $checkFare;
                $outs['status'] = 'success';
                $outs['message'] = 'success';
            }
        }
        return $outs;
    }

    public function getAllConsignments($token)
    {
    }

    public function getDeliveryDetails($token, $shipment)
    {
    }
    public function cancelConsignment($token, $shipment)
    {
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
}