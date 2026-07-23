<?php
namespace App\PaymentGateways;

use App\Models\Order;
use App\Models\Payment\{
    CCAvenueModel,
    CCAvenueResponseLog
};
use App\Models\CompanyCCAvenue;
use App\Models\UploadPrescription;
use Illuminate\Support\Facades\Log;
use App\Http\Services\B2CToTransferOrder;
use App\Status\CustomerOrderStatus;
use App\Http\Repositories\Payment\AfterPayment;
use App\Models\FinanceAutopostingValues;
use App\Http\Repositories\PostingRepository;
use App\PaymentGateways\InterfacePaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\HttpCurlCalls;
use Aws\DynamoDb\DynamoDbClient;

class CCAvenuePayment implements InterfacePaymentGateway
{
    protected $ccCreds;

    protected const ONLINE_PAYMENT = 2;
    const PAYMENT_SUCCESS = 1;
    const PAYMENT_FAILED = 2;
    const ONLINE = 2;

    public function __construct()
    {
        $this->ccCreds = $this->init();
    }

    public function processPayment($request, $podToOnline = 0)
    {
        $params = [
            'merchant_id'       => $this->ccCreds['merchantID'],
            'currency'          => $this->ccCreds['currency'],
            'language'          => $this->ccCreds['language'],
            'redirect_url'      => $this->ccCreds['redirectUrl'],
            'cancel_url'        => $this->ccCreds['cancelUrl'],
            'integration_type'  => $this->ccCreds['integration'],
            'tid'               => round(microtime(1)*1000),
            'order_id'          => $request->order_group_id,
            'amount'            => $request->total,
            'billing_name'      => auth_user()->cust_customer_name,
            'billing_address'   => auth_user()->primaryAddress->deli_house_name,
            'billing_city'      => auth_user()->primaryAddress->deli_city,
            'billing_state'     => auth_user()->primaryAddress->deli_state,
            'billing_zip'       => auth_user()->primaryAddress->deli_post,
            'billing_country'   => (config('ccavenue.country') ? config('ccavenue.country') : 'India'),
            'billing_tel'       => auth_user()->primaryAddress->deli_contact_no,
            'billing_email'     => auth_user()->cust_email,
            'delivery_name'     => auth_user()->cust_customer_name,
            'delivery_address'  => auth_user()->primaryAddress->deli_house_name,
            'delivery_city'     => auth_user()->primaryAddress->deli_city,
            'delivery_state'    => auth_user()->primaryAddress->deli_state,
            'delivery_zip'      => auth_user()->primaryAddress->deli_post,
            'delivery_country'  => (config('ccavenue.country') ? config('ccavenue.country') : 'India'),
            'delivery_tel'      => auth_user()->primaryAddress->deli_contact_no
        ];
        $queryParam = http_build_query($params);
        $encRequest = $this->encryptRequest($queryParam, $this->ccCreds['workingKey']);
        $iframePath = $this->ccCreds['paymentURL']."&encRequest={$encRequest}&access_code={$this->ccCreds['accessCode']}&scrollbar=yes";
        $data = [
            'customer_id'       => auth_user()->cust_id,
            'order_id'          => $request->order_id,
            'receipt'           => $request->order_id,
            'order_group_id'    => $params['order_id'],
            'transaction_id'    => $params['tid'],
            'amount'            => $params['amount'],
            'iframe_path'       => $iframePath,
            'currency'          => $params['currency']
        ];
        $insData = $this->addCCAvenue($data);
        return [
            'id'        => (string)$params['tid'],
            'longurl'   => $iframePath
        ];
    }

    public function paymentComplete($request, $compid)
    {
        $decryptedData = $this->decryptResponse($request["encResp"], $this->ccCreds['workingKey']);
        parse_str($decryptedData, $response);
        try
        {
            $status = ($response['order_status'] == 'Success') ? "success" : "failed";
            $order = $this->updateCCAvenue($response);
            $ccData = CCAvenueModel::where('order_group_id', $response['order_id'])->first();
            return [
                "status"            => $status,
                "amount"            => $order->total,
                'reponseid'         => $ccData->transaction_id,
                'responsestring'    => '',
                'paymentid'         => $ccData->transaction_id,
                'order'             => $order
            ];
        }
        catch (\Exception $e)
        {
            info("CCAvenuePayment paymentComplete ERROR => ".$e->getMessage());
            info($response);
            return [
                "status"    => "failed"
            ];
        }
    }
    
    public function sendPaymentUrl($order_group_id)
    {
        return CCAvenueModel::select('iframe_path')->where('order_group_id', $order_group_id)->first();
    }

    public function checkPaymentStatus($paymentgateway, $response)
    {
        $ccData = CCAvenueModel::where('transaction_id', $response['paymentid'])->first();
        $ccFeeTax = $this->getCCAvenueFeeTax($ccData);
        $ccFees = @$ccFeeTax['ccFees'] ? $ccFeeTax['ccFees'] : 0;
        $ccTax = @$ccFeeTax['ccTax'] ? $ccFeeTax['ccTax'] : 0;
        if($ccData->status == "Success")
        {
            if(config("paymentgateway.{$paymentgateway}.tax") == 'inclusive')
            {
                $mdrTax = $ccFees - $ccTax;
                $apUpdate['MerchantDiscountRate_MDR'] = $mdrTax;
            }
            else
            {
                $apUpdate['MerchantDiscountRate_MDR'] = $ccFees;
            }
            if(@config("paymentgateway.{$paymentgateway}.b_type") == 'intra')
            {
                $apUpdate['CGSTInputonMDR'] = $ccTax/2;
                $apUpdate['SGSTInputonMDR'] = $ccTax/2;
            }
            else
            {
                $apUpdate['IGSTInputonMDR'] = $ccTax;
            }
            $autoPosting = FinanceAutopostingValues::where('order_id', $ccData->order_id)->update($apUpdate);
            Order::where('order_id', $ccData->order_id)->update([
                'order_payment_gateway'         => $paymentgateway,
                'order_payment_gateway_fees'    => $ccFees,
                'order_payment_gateway_tax'     => $ccTax
            ]);
            $order = Order::find($ccData->order_id);
            $postReq = new Request();
            $postReq->setMethod('POST');
            $postReq->request->add([
                'order_id' => $order->order_id,
                'finascopEventRefId'     => '07801eb4-38d7-11ee-9967-065723bafb24',
                'storegroup_id' => ($order->storegroup_id ? $order->storegroup_id : 0)
            ]);
            (new PostingRepository)->finascopPosting($postReq);
        }
    }
    public function getNarrationDetails($order_id)
    {
        $order = Order::where('order_id', $order_id)->first();
        $details = CCAvenueModel::where('order_id', $order_id)->first();
        if(!$details)
        {
            $details = CCAvenueModel::where('order_group_id', $order->order_group_id)->first();
        }

        return $this->getNarrationData($details);
    }

    public function checkScheduledPaymentStatus($order_id, $type = "payment")
    {
        $ccData = CCAvenueModel::where('order_group_id', $order_id)->latest()->first();
        if($ccData)
        {
            $credType = config("ccavenue.type");
            $accessCode = config("ccavenue.api.{$credType}.accessCode");
            $encKey = config("ccavenue.api.{$credType}.workingKey");
            $apiURL = config("ccavenue.api.{$credType}.url");
            $params = [
                "order_no"      => $ccData->order_group_id
            ];
            $encRequest = $this->encryptRequest(json_encode($params), $encKey);
            $apiParams = [
                'enc_request'   => $encRequest,
                'access_code'   => $accessCode,
                'command'       => 'orderStatusTracker',
                'request_type'  => 'JSON',
                'response_type' => 'JSON',
                'version'       => '1.2'
            ];
            $apiResponse = (new HttpCurlCalls)->curlCall($apiURL.http_build_query($apiParams), [], 'POST', ['Content-Type: application/json'], "all");
            parse_str($apiResponse, $response);
            if($response)
            {
                $encResponse = @$response['enc_response'];
                if($encResponse)
                {
                    $encResponse = str_replace("\r\n", '', $encResponse);
                    $respData = $this->decryptResponse($encResponse, $encKey);

                    $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
                    $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
                    $dynamoClient->putItem([
                    'TableName' => config('aws.prefix').'timedout_order_payments',
                        'Item'      => [
                            'uuid'          => ['S' => (string)$uuid],
                            'tstamp'        => ['S' => (string)date('Y-m-d H:i:s')],
                            'order_id'      => ['S' => (string)$order_id],
                            "type"          => ['S' => "payment"],
                            'gateway'       => ['S' => "ccavenue"],
                            'apiURL'        => ['S' => ($apiURL.http_build_query($apiParams))],
                            'apiResponse'   => ['S' => $respData]
                        ]
                    ]);
                    $respData = json_decode($respData);
                    if((@$respData->order_status == "Shipped") || (@$respData->order_status == "Successful"))
                    {
                        if($type == "refund")
                        {
                            $refundTransactn = $this->orderRefunds($respData->reference_no, $respData->order_amt, $order_id);
                            
                            if(@$refundTransactn['request'] && @$refundTransactn['response'])
                            {
                                $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
                                $dynamoClient->putItem([
                                'TableName' => config('aws.prefix').'timedout_order_payments',
                                    'Item'      => [
                                        'uuid'          => ['S' => (string)$uuid],
                                        'tstamp'        => ['S' => (string)date('Y-m-d H:i:s')],
                                        'order_id'      => ['S' => (string)$order_id],
                                        "type"          => ['S' => "refund"],
                                        'gateway'       => ['S' => "ccavenue"],
                                        'apiURL'        => ['S' => @$refundTransactn['request']],
                                        'apiResponse'   => ['S' => @$refundTransactn['response']]
                                    ]
                                ]);
                                $refundTransactnResp = json_decode(@$refundTransactn['response']);
                                if(@$refundTransactnResp->refund_status == 0)
                                {
                                    return $respData->reference_no;
                                }
                            }
                        }
                        else
                        {
                            $this->updateScheduledDetails($respData);
                        }
                        return $respData->reference_no;
                    }
                }
            }
        }
        return false;
    }
    public function cancellationRefunds($order_id, $amount)
    {
        try
        {
            $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
            $ccData = CCAvenueModel::where('order_group_id', $order_id)->latest()->first();
            if($ccData)
            {
                $credType = config("ccavenue.type");
                $accessCode = config("ccavenue.api.{$credType}.accessCode");
                $encKey = config("ccavenue.api.{$credType}.workingKey");
                $apiURL = config("ccavenue.api.{$credType}.url");
                $params = [
                    "order_no"      => $ccData->order_group_id
                ];
                $encRequest = $this->encryptRequest(json_encode($params), $encKey);
                $apiParams = [
                    'enc_request'   => $encRequest,
                    'access_code'   => $accessCode,
                    'command'       => 'orderStatusTracker',
                    'request_type'  => 'JSON',
                    'response_type' => 'JSON',
                    'version'       => '1.2'
                ];
                $apiResponse = (new HttpCurlCalls)->curlCall($apiURL.http_build_query($apiParams), [], 'POST', ['Content-Type: application/json'], "all");
                parse_str($apiResponse, $response);
                if($response)
                {
                    $encResponse = @$response['enc_response'];
                    if($encResponse)
                    {
                        $encResponse = str_replace("\r\n", '', $encResponse);
                        $respData = $this->decryptResponse($encResponse, $encKey);

                        $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
                        $dynamoClient->putItem([
                            'TableName' => config('aws.prefix').' order_refunds_log',
                            'Item'      => [
                                'uuid'          => ['S' => (string)$uuid],
                                'tstamp'        => ['S' => (string)date('Y-m-d H:i:s')],
                                'order_id'      => ['S' => (string)$order_id],
                                'gateway'       => ['S' => "ccavenue"],
                                "type"          => ['S' => "payment"],
                                'reqType'       => ['S' => "Check Payment"],
                                'apiURL'        => ['S' => ($apiURL.http_build_query($apiParams))],
                                'apiResponse'   => ['S' => $respData]
                            ]
                        ]);
                        $respData = json_decode($respData);
                        if((@$respData->order_status == "Shipped") || (@$respData->order_status == "Successful"))
                        {
                            $refundTransactn = $this->orderRefunds($respData->reference_no, $amount, $order_id);
                            if(@$refundTransactn['request'] && @$refundTransactn['response'])
                            {
                                $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
                                $dynamoClient->putItem([
                                'TableName' => config('aws.prefix').'order_refunds_log',
                                    'Item'      => [
                                        'uuid'          => ['S' => (string)$uuid],
                                        'tstamp'        => ['S' => (string)date('Y-m-d H:i:s')],
                                        'order_id'      => ['S' => (string)$order_id],
                                        "type"          => ['S' => "refund"],
                                        'gateway'       => ['S' => "ccavenue"],
                                        'reqType'       => ['S' => "Refund"],
                                        'apiURL'        => ['S' => @$refundTransactn['request']],
                                        'apiResponse'   => ['S' => @$refundTransactn['response']]
                                    ]
                                ]);
                                $refundTransactnResp = json_decode(@$refundTransactn['response']);
                                if(@$refundTransactnResp->refund_status == 0)
                                {
                                    return [ 
                                        "id"        => $respData->reference_no,
                                        "request"   => @$refundTransactn['request'],
                                        "response"  => @$refundTransactn['response']
                                    ];
                                }
                            }
                        }
                    }
                }
            }
            return false;
        }
        catch (\Exception $e)
        {
            info("CCAvenue cancellationRefunds ERROR");info($e);
            return false;
        }
    }


    
    private function updateScheduledDetails($request)
    {
        $ccData = CCAvenueModel::where('order_group_id', $request->order_no)->first();
        if($ccData)
        {
            $ccFees = @$request->order_fee_perc_value ? $request->order_fee_perc_value : 0;
            $ccTax = @$request->order_tax ? $this->nearestEvenDecimal(round($request->order_tax, 2)) : 0;
            $paymentMode = $this->selectPaymentMode($request->order_option_type);

            CCAvenueModel::where('order_group_id', $request->order_no)->update([
                'bank_ref_no'               => $request->order_bank_ref_no,
                'amount_due'                => $request->order_gross_amt,
                'amount_paid'               => $request->order_amt,
                'total_fees'                => $ccFees,
                'tax_amount'                => $ccTax,
                'payment_mode'              => $paymentMode,
                'balance_payment_response'  => json_encode($request),
                'status'                    => $request->order_status
            ]);

            //UPDATE CCAVENUE LOG
            $logData = [
                "pg_ccavenue_id"    => $ccData->id,
                "order_group_id"    => $request->order_no,
                "response"          => json_encode($request)
            ];
            $this->saveCCAvenueLog($logData, "update");

            $order = Order::where('order_id', $ccData->order_id)->first();
            if(@$order->status_id >= 4)
            {
                Order::where('order_id', $ccData->order_id)->update([
                    'payment_mode'                  => (@$order->payment_mode > 0) ? @$order->payment_mode : static::ONLINE,
                    'status_id'                     => CustomerOrderStatus::PAYMENT_INITIATED,
                    'order_confirm_date'            => now()->format('Y-m-d'),
                    'order_confirmed_on'            =>  now()->format('Y-m-d H:i:s'),
                    'order_payment_gateway'         => 'ccavenue',
                    'order_payment_gateway_fees'    => $ccFees,
                    'order_payment_gateway_tax'     => $ccTax
                ]);
                return $order;
            }
            else
            {
                Order::where('order_group_id', $request->order_id)->update([
                    'payment_mode'                  => (@$order->payment_mode > 0) ? @$order->payment_mode : static::ONLINE,
                    'status_id'                     => CustomerOrderStatus::PAYMENT_INITIATED,
                    'order_confirm_date'            => now()->format('Y-m-d'),
                    'order_confirmed_on'            =>  now()->format('Y-m-d H:i:s'),
                    'order_payment_gateway'         => 'ccavenue',
                    'order_payment_gateway_fees'    => $ccFees,
                    'order_payment_gateway_tax'     => $ccTax
                ]);
            }

            $order = Order::find($ccData->order_id);
            $postReq = new Request();
            $postReq->setMethod('POST');
            $postReq->request->add([
                'order_id' => $order->order_id,
                'finascopEventRefId'     => '07801eb4-38d7-11ee-9967-065723bafb24',
                'storegroup_id' => ($order->storegroup_id ? $order->storegroup_id : 0)
            ]);
            (new PostingRepository)->finascopPosting($postReq);
        }
    }
    private function orderRefunds($reference_no, $amount, $order_id)
    {
        $credType = config("ccavenue.type");
        $accessCode = config("ccavenue.api.{$credType}.accessCode");
        $encKey = config("ccavenue.api.{$credType}.workingKey");
        $apiURL = config("ccavenue.api.{$credType}.url");
        $params = [
            "reference_no"  => $reference_no,
            "refund_amount" => $amount,
            "refund_ref_no" => $order_id
        ];
        $encRequest = $this->encryptRequest(json_encode($params), $encKey);
        $apiParams = [
            'enc_request'   => $encRequest,
            'access_code'   => $accessCode,
            'command'       => 'refundOrder',
            'request_type'  => 'JSON',
            'response_type' => 'JSON'
        ];
        $apiResponse = (new HttpCurlCalls)->curlCall($apiURL.http_build_query($apiParams), [], 'POST', ['Content-Type: application/json'], "all");
        parse_str($apiResponse, $response);
        if($response)
        {
            $encResponse = @$response['enc_response'];
            if($encResponse)
            {
                $encResponse = str_replace("\r\n", '', $encResponse);
                $respData = $this->decryptResponse($encResponse, $encKey);

                return [
                    "request"   => $apiURL.http_build_query($apiParams),
                    "response"  => $respData
                ];
            }
        }
        return false;
    }
    private function selectPaymentMode($mode)
    {
        $mode = "";
        switch ($mode)
        {
            case 'OPTCASHC':
                $mode = "Cash card";
                break;
            case 'OPTCRDC':
                $mode = "Credit Card";
                break;
            case 'OPTDBCRD':
                $mode = "Debit Card";
                break;
            case 'OPTEMI':
                $mode = "EMI";
                break;
            case 'OPTIVRS':
                $mode = "IVRS Payment";
                break;
            case 'OPTMOBP':
                $mode = "Mobile Payments";
                break;
            case 'OPTNBK':
                $mode = "Net Banking";
                break;
            case 'OPTUPI':
                $mode = "UPI Payments";
                break;
            
            default:
                $mode = "";
                break;
        }
        return $mode;
    }
    private function getNarrationData($details)
    {
        $outs = [];
        if($details)
        {
            $response = json_decode($details->balance_payment_response);
            if($response)
            {
                $outs['payment_type'] = [
                    'mode'  => $response->payment_mode,
                    'type'  => $response->card_name
                ];
                $outs['reference'] = $response->bank_ref_no;
            }
        }
        return $outs;
    }
    private function getCCAvenueFeeTax($ccData)
    {
        $ccFees = 0;
        $ccTax = 0;
        $responseData = json_decode($ccData->balance_payment_response);
        if(is_object($responseData))
        {
            $paymentMode = $responseData->payment_mode;
            $cardName = $responseData->card_name;
            $total = $responseData->amount;

            $query = "SELECT * FROM `paymentgateway_rates_ccavenue` WHERE `mode`='{$paymentMode}' AND `type`=COALESCE((SELECT `type` FROM `paymentgateway_rates_ccavenue` WHERE `type`='{$cardName}' LIMIT 1), '') AND CASE WHEN (`range_from` > 0 && {$total} > `range_from`) THEN 1 WHEN (`range_to` > 0 && {$total} <= `range_to`) THEN 1 WHEN (`range_to` = 0 && `range_from` = 0) THEN 1 ELSE 0 END";
            $getRates = DB::select($query);

            if(!empty($getRates))
            {
                $ccFees = ($total * $getRates[0]->percentage) / 100;
                if(($total >= 2000) || (!in_array($paymentMode, ["Debit Card", "Credit Card"])))
                {
                    $ccTax = round(($ccFees * 0.18), 2);
                    $ccTax = $this->nearestEvenDecimal($ccTax);
                }
            }
        }
        return [
            'ccFees'    => $ccFees,
            'ccTax'     => $ccTax
        ];
    }
    private function addCCAvenue($data)
    {
        $check = CCAvenueModel::where([
            ['customer_id', $data['customer_id']],
            ['order_id', $data['order_id']]
        ])->first();
        if($check)
        {
            $ccavenue = CCAvenueModel::where('id', $check->id)->update([
                'order_group_id'    => $data['order_group_id'],
                'transaction_id'    => $data['transaction_id'],
                'iframe_path'       => $data['iframe_path']
            ]);
            if($ccavenue)
            {
                $data["pg_ccavenue_id"] = $check->id;
                $this->saveCCAvenueLog($data, "update");
                $this->updateOrderStatus($data['order_group_id'], CustomerOrderStatus::CHECKEDOUT, CustomerOrderStatus::PAYMENT_INITIATED);
            }
            return $ccavenue;
        }
        $ccavenue = CCAvenueModel::create($data);
        if($ccavenue)
        {
            $data["pg_ccavenue_id"] = $ccavenue->id;
            $this->saveCCAvenueLog($data, "insert");
            $this->updateOrderStatus($data['order_group_id'], CustomerOrderStatus::CHECKEDOUT, CustomerOrderStatus::PAYMENT_INITIATED);
        }
        return $ccavenue;
    }
    private function updateOrderStatus($order_group_id, $old_status, $new_status)
    {
        $order = Order::where('order_group_id', $order_group_id)->first();
        Order::where([
            ['order_group_id', $order_group_id],
            ['status_id', $old_status]
        ])->update([
            'payment_mode'          => (@$order->payment_mode > 0) ? @$order->payment_mode : static::ONLINE,
            'status_id'             => $new_status,
            'order_confirm_date'    => now()->format('Y-m-d'),
            'order_confirmed_on'    =>  now()->format('Y-m-d H:i:s'),
        ]);
    }
    private function saveCCAvenueLog($data, $type)
    {
        if($type =="insert")
        {
            CCAvenueResponseLog::create([
                "pg_ccavenue_id"    => $data["pg_ccavenue_id"],
                "order_id"          => $data["order_id"],
                "order_group_id"    => $data["order_group_id"],
                "transaction_id"    => $data["transaction_id"],
                "iframe_path"       => $data["iframe_path"]
            ]);
        }
        if($type =="update")
        {
            CCAvenueResponseLog::where([
                ["pg_ccavenue_id", $data["pg_ccavenue_id"]],
                ["order_group_id", $data["order_group_id"]],
            ])->orderBy('id', 'desc')->first()->update([
                "response"  => @$data['response']
            ]);
        }
    }
    private function updateCCAvenue($request)
    {
        $ccData = CCAvenueModel::where('order_group_id', $request['order_id'])->first();
        if($ccData)
        {
            $ccFeeTax = $this->getCCAvenueFeeTax($ccData);
            $ccFees = @$ccFeeTax['ccFees'] ? $ccFeeTax['ccFees'] : 0;
            $ccTax = @$ccFeeTax['ccTax'] ? $ccFeeTax['ccTax'] : 0;
            CCAvenueModel::where('order_group_id', $request['order_id'])->update([
                'bank_ref_no'               => $request['bank_ref_no'],
                'amount_due'                => $request['mer_amount'],
                'amount_paid'               => $request['amount'],
                'total_fees'                => $ccFees,
                'tax_amount'                => $ccTax,
                'payment_mode'              => $request['payment_mode'],
                'balance_payment_response'  => json_encode($request),
                'status'                    => $request['order_status']
            ]);

            //UPDATE CCAVENUE LOG
            $logData = [
                "pg_ccavenue_id"    => $ccData->id,
                "order_group_id"    => $request['order_id'],
                "response"          => json_encode($request)
            ];
            $this->saveCCAvenueLog($logData, "update");

            $order = Order::where('order_id', $ccData->order_id)->first();
            if(@$order->status_id >= 4)
            {
                Order::where('order_id', $ccData->order_id)->update([
                    'payment_mode'          => (@$order->payment_mode > 0) ? @$order->payment_mode : static::ONLINE,
                    'status_id'             => CustomerOrderStatus::PAYMENT_INITIATED,
                    'order_confirm_date'    => now()->format('Y-m-d'),
                    'order_confirmed_on'    =>  now()->format('Y-m-d H:i:s'),
                ]);
                return $order;
            }
            else
            {
                Order::where('order_group_id', $request['order_id'])->update([
                    'payment_mode'          => (@$order->payment_mode > 0) ? @$order->payment_mode : static::ONLINE,
                    'status_id'             => CustomerOrderStatus::PAYMENT_INITIATED,
                    'order_confirm_date'    => now()->format('Y-m-d'),
                    'order_confirmed_on'    =>  now()->format('Y-m-d H:i:s'),
                ]);
                return Order::where('order_group_id', $request['order_id'])->first();
            }
        }
    }
    private function init()
    {
        $ccAvenueCreds = CompanyCCAvenue::getCompanyPaydetails(1, getHeaderStoreGroup());
        return [
            "merchantID"    => $ccAvenueCreds['merchantID'],
            "redirectUrl"   => $ccAvenueCreds['redirectUrl'],
            "cancelUrl"     => $ccAvenueCreds['cancelUrl'],
            "currency"      => $ccAvenueCreds['currency'],
            "language"      => $ccAvenueCreds['language'],
            "integration"   => $ccAvenueCreds['integration'],
            "paymentURL"    => $ccAvenueCreds['paymentURL'],
            "accessCode"    => $ccAvenueCreds['accessCode'],
            "workingKey"    => $ccAvenueCreds['workingKey']
        ];
    }
    private function encryptRequest($plainText, $key)
    {
        $key = $this->hexToBin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        $encryptedText = bin2hex($openMode);
        return $encryptedText;
    }
    private function decryptResponse($encryptedText, $key)
    {
        $key = $this->hexToBin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText = $this->hexToBin($encryptedText);
        $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        return $decryptedText;
    }
    private function hexToBin($hexString) 
    { 
        $length = strlen($hexString); 
        $binString = "";   
        $count = 0;
        $x=3;
        while($count < $length) 
        {
            $subString = substr($hexString, $count, 2);
            $packedString = pack("H*", $subString); 
            if ($count == 0)
            {
                $binString = $packedString;
            } 

            else 
            {
                $binString .= $packedString;
            } 

            $count+=2; 
        }
        return $binString; 
    }
    private function nearestEvenDecimal($decimalValue)
    {
        $numArr = explode('.',$decimalValue);
        if(@$numArr[1])
        {
            $num_length = strlen((string)$numArr[1]); //to cover .01, .001
            if($num_length == 1)
            {
                $numArr[1] = $numArr[1]*10;
            }
            $remainder = $numArr[1]%2;
            if ($remainder == 0)
            {
              return $decimalValue;
            }
            else
            {
              return $decimalValue + 0.01;
            }
        }
    }
}