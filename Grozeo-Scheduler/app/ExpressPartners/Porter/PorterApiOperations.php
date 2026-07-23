<?php
namespace App\ExpressPartners\Porter;

use App\Helpers\HttpCurlCalls;
use App\Models\CourierDelivery\{
    ShippingConsignmentLog,
    OrderCourierPartnerSelections
};
use App\Http\Repositories\ShippingLogRepository;

class PorterApiOperations
{
    protected $apiHeader, $getQuote, $createOrder, $trackOrder, $cancelOrder;
    function __construct()
    {
        $apiKey = config("expresspartners.porter.apiKey");
        $this->apiHeader = ["Content-Type: application/json", "x-api-key: {$apiKey}"];

        $this->getQuote = config("expresspartners.porter.host").config("expresspartners.porter.getQuote");
        $this->createOrder = config("expresspartners.porter.host").config("expresspartners.porter.createOrder");
        $this->trackOrder = config("expresspartners.porter.host").config("expresspartners.porter.trackOrder");
        $this->cancelOrder = config("expresspartners.porter.host").config("expresspartners.porter.cancelOrder");
    }

    public function checkDeliveryAvailable($request)
    {
        $outs = [
            'status'    => 'error',
            'data'      => [],
            'message'   => ""
        ];

        $phoneCode = config('app.phonecode') ?? "+91";
        $number = preg_replace('/[\s\-()]/', '', $request["to_details"]["phone"]);
        $phone = str_replace($phoneCode, '', $number);
        $body = [
            "pickup_details"    => [
                "lat"       => $request["from_details"]['latitude'],
                "lng"       => $request["from_details"]['longitude'],
            ],
            "drop_details"      => [
                "lat"       => $request["to_details"]['latitude'],
                "lng"       => $request["to_details"]['longitude'],
            ],
            "customer"          => [
                "name"      => $request["to_details"]["name"],
                "mobile"    => [
                    "country_code"  => $phoneCode,
                    "number"        => $phone
                ]
            ]
        ];
        $response = (new HttpCurlCalls)->curlCall($this->getQuote, json_encode($body), 'POST', $this->apiHeader);
        (new ShippingLogRepository)->createLog([
            'order_id'      => @$request['order_id'],
            'order_method'  => 1,
            'type'          => config('expresspartners.porter.local_id'),
            'APIName'       => "checkDeliveryAvailable",
            "APIURL"        => $this->getQuote,
            "APIHeaders"    => json_encode($this->apiHeader),
            'request'       => json_encode($body),
            'response'      => @$response ? json_encode($response) : ""
        ]);
        $outs['message'] = @$response->message;
        if(isset($response->vehicles) && count($response->vehicles) > 0)
        {
            $outs['status'] = 'success';
            $outs['data'] = $response;
        }
        return $outs;
    }
    public function createNewConsignment($data)
    {
        $outs = [
            'status'    => 'error',
            'data'      => [],
            'request'   => [],
            'message'   => ""
        ];
        $request = $this->generateRequest($data);
        $response = (new HttpCurlCalls)->curlCall($this->createOrder, json_encode($request), 'POST', $this->apiHeader);

        (new ShippingLogRepository)->createLog([
            'order_id'      => @$data['order_id'],
            'order_method'  => 1,
            'type'          => config('expresspartners.porter.local_id'),
            'APIName'       => "createNewConsignment",
            "APIURL"        => $this->createOrder,
            "APIHeaders"    => json_encode($this->apiHeader),
            'request'       => json_encode($request),
            'response'      => @$response ? json_encode($response) : ""
        ]);
        $outs['request'] = $request;
        $outs['data'] = $response;
        $outs['message'] = @$response->message;
        if(@$response->order_id != "")
        {
            $outs['status'] = 'success';
            $outs['message'] = 'success';
        }
        return $outs;
    }
    public function cancelConsignment($order_id, $shipID)
    {
        $outs = ['status'    => 'error'];
        $cancelAPI = strtr($this->cancelOrder, ["{#orderID}" => $shipID]);
        $response = (new HttpCurlCalls)->curlCall($cancelAPI, [], 'GET', $this->apiHeader);
        
        (new ShippingLogRepository)->createLog([
            'order_id'      => $order_id,
            'order_method'  => 3,
            'type'          => config('expresspartners.porter.local_id'),
            'APIName'       => "Order Cancellation",
            "APIURL"        => $cancelAPI,
            "APIHeaders"    => "",
            'request'       => "",
            'response'      => @$response ? json_encode($response) : ""
        ]);
        if(@$response->code == 200)
        {
            $outs['status'] = 'success';
        }
        return $outs;
    }
    public function getDeliveryDetails($shipment)
    {
        $outs = [
            'status'    => 'error',
            'data'      => [],
            'message'   => ""
        ];
        $trackOrder = strtr($this->trackOrder, ["{#orderID}" => $shipment->tracking_id]);
        $response = (new HttpCurlCalls)->curlCall($trackOrder, [], 'GET', $this->apiHeader);
        (new ShippingLogRepository)->createLog([
            'order_id'      => $shipment->order_id,
            'order_method'  => 1,
            'type'          => config('expresspartners.porter.local_id'),
            'APIName'       => "getDeliveryDetails",
            "APIURL"        => $trackOrder,
            "APIHeaders"    => json_encode($this->apiHeader),
            'request'       => " - ",
            'response'      => json_encode($response)
        ]);
        $outs['message'] = "Unable to get details";
        $outs['data'] = $response;
        if(@$response->status != "")
        {
            $outs['status'] = 'success';
            $outs['message'] = 'success';
        }
        return $outs;
    }

    private function generateRequest($data)
    {
        return [
            "request_id"            => $data['order_id'],
            "delivery_instructions" => [
                "instructions_list"     => []
            ],
            "pickup_details"        => [
                "address"   => [
                    "apartment_address" => "",
                    "street_address1"   => $data['from_details']['address'],
                    "street_address2"   => @$data['from_details']['address2'],
                    "landmark"          => "",
                    "city"              => $data['from_details']['city'],
                    "state"             => $data['from_details']['state'],
                    "country"           => $data['from_details']['country'],
                    "pincode"           => $data['from_details']['pincode'],
                    "lat"               => (double)$data['from_details']['latitude'],
                    "lng"               => (double)$data['from_details']['longitude'],
                    "contact_details"   => [
                        "name"              => $data['from_details']['name'],
                        "phone_number"      => $data['from_details']['phone'],
                    ]
                ],
            ],
            "drop_details"          => [
                "address"   => [
                    "apartment_address" => "",
                    "street_address1"   => $data['to_details']['address1'],
                    "street_address2"   => $data['to_details']['address2'],
                    "landmark"          => $data['to_details']['landmark'],
                    "city"              => $data['to_details']['city'],
                    "state"             => $data['to_details']['state'],
                    "country"           => $data['to_details']['country'],
                    "pincode"           => $data['to_details']['pincode'],
                    "lat"               => $data['to_details']['latitude'],
                    "lng"               => $data['to_details']['longitude'],
                    "contact_details"   => [
                        "name"              => $data['to_details']['name'],
                        "phone_number"      => $data['to_details']['phone'],
                    ]
                ],
            ]
        ];
    }
}