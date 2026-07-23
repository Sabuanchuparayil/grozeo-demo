<?php
namespace App\PackingPartners\Petpooja;

use App\Helpers\HttpCurlCalls;
use App\Http\Repositories\PackingLogRepository;

class PetpoojaApiOperations
{
    protected $defAppKey, $defSecretKey, $defAccessToken, $defOrderType, $defRestPerpTime, $defGroPrepTime, $defStoreID, $callback, $saveOrder;
    
    public function __construct()
    {
        $partner = config('packingpartners.default');

        $this->defAppKey = config("packingpartners.{$partner}.appKey");
        $this->defSecretKey = config("packingpartners.{$partner}.appSecret");
        $this->defAccessToken = config("packingpartners.{$partner}.accessToken");
        $this->defOrderType = config("packingpartners.{$partner}.orderType") ?? "H";
        $this->defRestPerpTime = config("packingpartners.{$partner}.restPerpTime");
        $this->defGroPrepTime = config("packingpartners.{$partner}.groPrepTime");
        $this->defStoreID = config("packingpartners.{$partner}.defStoreID");
        $this->callback = config("packingpartners.{$partner}.callback");

        $this->saveOrder = config("packingpartners.{$partner}.saveOrderAPI");
        $this->cancelOrder = config("packingpartners.{$partner}.cancelOrderAPI");
    }
    public function createPacking($reqData)
    {
        $outs = [
            'status'    => 'error',
            'data'      => [],
            'request'   => [],
            'message'   => ''
        ];
        $appKey = @$reqData['appKey'] != "" ? $reqData['appKey'] : $this->defAppKey;
        $secretKey = @$reqData['secretKey'] != "" ? $reqData['secretKey'] : $this->defSecretKey;
        $accessToken = @$reqData['accessToken'] != "" ? $reqData['accessToken'] : $this->defAccessToken;
        $orderType = @$reqData['orderType'] != "" ? $reqData['orderType'] : $this->defOrderType;
        $restPerpTime = @$reqData['restPerpTime'] != "" ? $reqData['restPerpTime'] : $this->defRestPerpTime;
        $groPrepTime = @$reqData['groPrepTime'] != "" ? $reqData['groPrepTime'] : $this->defGroPrepTime;
        $restID = @$reqData['restID'] != "" ? $reqData['restID'] : $this->defStoreID;
        $prepTime = ($reqData['hasRestaurant'] == 1) ? $restPerpTime : $groPrepTime;

        $fromPhone = $this->checkCountryCode(@$reqData['from_details']['phone']);
        $toPhone = $this->checkCountryCode(@$reqData['to_details']['phone']);

        $petPooja = [
            "app_key"       => $appKey,
            "app_secret"    => $secretKey,
            "access_token"  => $accessToken,
            "orderinfo"     => [
                "OrderInfo"     => [
                    "Restaurant"    => [
                        "details"   => [
                            "res_name"              =>  $reqData['from_details']['name'],
                            "address"               =>  $reqData['from_details']['address'],
                            "contact_information"   =>  $fromPhone,
                            "restID"                =>  $restID
                        ],
                    ],
                    "Customer"      => [
                        "details"   => [
                            "name"      =>  $reqData['to_details']['name'],
                            "email"     =>  $reqData['to_details']['email'],
                            "address"   =>  $reqData['to_details']['address'],
                            "phone"     =>  $toPhone,
                            "latitude"  =>  $reqData['to_details']['latitude'],
                            "longitude" =>  $reqData['to_details']['longitude'],
                        ],
                    ],
                    "Order"         => [
                        "details"   => [
                            "orderID"           => $reqData['order_id'],
                            "preorder_date"     => date("Y-m-d", strtotime($reqData['package_details']['pickup'])),
                            "preorder_time"     => date("H:i:s", strtotime($reqData['package_details']['pickup'])),
                            "service_charge"    => 0,
                            "sc_tax_amount"     => 0,
                            "delivery_charges"  => $reqData['delivery_charge'],
                            "dc_tax_amount"     => $reqData['delivery_charge_tax'],
                            "dc_gst_details"    => [
                                [
                                    "gst_liable"    => "vendor",
                                    "amount"        => $reqData['delivery_charge_tax']
                                ],
                                [
                                    "gst_liable"    => "restaurant",
                                    "amount"        => "0"
                                ]
                            ],
                            "packing_charges"   => 0,
                            "pc_tax_amount"     => 0,
                            "pc_gst_details"    => [
                                [
                                    "gst_liable"    => "vendor",
                                    "amount"        => 0
                                ],
                                [
                                    "gst_liable"    => "restaurant",
                                    "amount"        => "0"
                                ]
                            ],
                            "order_type"        => $orderType,
                            "ondc_bap"          => "",
                            "advanced_order"    => "N",
                            "payment_type"      => $reqData['payment_mode'],
                            "table_no"          => "",
                            "no_of_persons"     => "0",
                            "discount_total"    => $reqData['discount_amount'],
                            "tax_total"         => $reqData['tax'],
                            "discount_type"     => "F",
                            "total"             => $reqData['total'],
                            "description"       => $reqData['notes'],
                            "created_on"        => date('Y-m-d H:i:s'),
                            "enable_delivery"   => 1,
                            "min_prep_time"     => $prepTime ?? 60,
                            "callback_url"      => $this->callback,
                            "collect_cash"      => $reqData['pending_amount'],
                            "otp"               => ""
                        ],
                    ],
                    "OrderItem"     => [
                        "details"   => $reqData['package_details']['products'],
                    ],
                    "Tax"           => [
                        "details"   => $reqData['Tax'],
                    ],
                    "Discount"      => [
                        "details"   => $reqData['Discount'],
                    ],
                ],
                "udid"          => "",
                "device_type"   => "Web"
            ],
        ];
        $response = (new HttpCurlCalls)->curlCall($this->saveOrder, json_encode($petPooja), 'POST', ['Content-Type: application/json']);
        (new PackingLogRepository)->createLog([
            'order_id'      => @$reqData['order_id'],
            'type'          => 'petpooja',
            'APIName'       => "SaveOrder",
            "APIURL"        => $this->saveOrder,
            "APIHeaders"    => "",
            'request'       => json_encode($petPooja),
            'response'      => json_encode($response)
        ]);
        $outs['data'] = $response;
        $outs['message'] = @$response->message;
        $outs['request'] = $petPooja;
        if(@$response->success == 1)
        {
            $outs['status'] = 'success';
            $outs['message'] = 'success';
        }
        return $outs;
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

    public function orderCancellation($orderID, $reason = "")
    {
        $outs = [
            'status'    => 'error',
            'message'   => ''
        ];
        $appKey = @$reqData['appKey'] != "" ? $reqData['appKey'] : $this->defAppKey;
        $secretKey = @$reqData['secretKey'] != "" ? $reqData['secretKey'] : $this->defSecretKey;
        $accessToken = @$reqData['accessToken'] != "" ? $reqData['accessToken'] : $this->defAccessToken;
        $restID = @$reqData['restID'] != "" ? $reqData['restID'] : $this->defStoreID;
        $cancelRequest = [
            "app_key"       => $appKey,
            "app_secret"    => $secretKey,
            "access_token"  => $accessToken,
            "restID"        => $restID,
            "orderID"       => "",
            "clientorderID" => $orderID,
            "cancelReason"  => ($reason =! "") ? $reason : "Please cancel my order.",
            "status"        => "-1"
        ];
        $response = (new HttpCurlCalls)->curlCall($this->cancelOrder, json_encode($cancelRequest), 'POST', ['Content-Type: application/json']);
        (new PackingLogRepository)->createLog([
            'order_id'      => $orderID,
            'type'          => 'petpooja',
            'APIName'       => "cancelOrder",
            "APIURL"        => $this->cancelOrder,
            "APIHeaders"    => "",
            'request'       => json_encode($cancelRequest),
            'response'      => json_encode($response)
        ]);
        if(@$response->success == 1)
        {
            $outs['status'] = 'success';
            $outs['message'] = 'success';
        }
        return $outs;
    }
}