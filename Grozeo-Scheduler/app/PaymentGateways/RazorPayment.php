<?php

namespace App\PaymentGateways;

use App\Models\Order;
use App\Models\CompanyRazorpay;
use App\Models\CompanyBranch;
use App\Exceptions\MsgException;
use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use App\Domains\EasyPay\AesForJava;
use App\Models\Payment\RazorpayModel;
use App\Domains\Atom\AtomTransactionRequest;
use App\Domains\Atom\AtomTransactionResponse;
use App\PaymentGateways\InterfacePaymentGateway;
use App\Models\FinanceAutopostingValues;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use App\Status\CustomerOrderStatus;
use Illuminate\Http\Request;
use App\Http\Repositories\PostingRepository;
use Aws\DynamoDb\DynamoDbClient;

class RazorPayment implements InterfacePaymentGateway
{
    protected const ONLINE_PAYMENT = 2;

    const PAYMENT_SUCCESS = 1;

    const PAYMENT_FAILED = 2;
    const ONLINE = 2;

    public function paymentComplete($request,$compid)
    {
        $success = true;
        $error = "Payment Failed";
        $rpoid = $request->rpoid;
        $storegroupid = getHeaderStoreGroup();

        if (empty($request->razorpay_payment_id) === false)
        {
            $compid = 1;
            try{
                $company_id = CompanyBranch::where('br_Id', auth_user()->deli_branch_id)->first();
                $compid =  $company_id->comp_id ?? 1;
            }
            catch(\Exception $e)
            {
                $compid = 1;
            }
            $companyrazorpay = CompanyRazorpay::getCompanyPaydetails($compid, $storegroupid);
            // Razorpay API
            $api = new Api($companyrazorpay['key_id'], $companyrazorpay['key_secret']);

            try
            {
                $attributes = array(
                    'razorpay_order_id' => $rpoid, //$_SESSION['razorpay_order_id'],
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_signature' => $request->razorpay_signature
                );
                $api->utility->verifyPaymentSignature($attributes);
            }
            catch(SignatureVerificationError $e)
            {
                $success = false;
            }
        }

        $status = "";
        if ($success === true)
        {
            $status = "success";
            $order = $this->updateRazorpay($status, static::PAYMENT_SUCCESS, $rpoid);
        }
        else
        {
            $status = "failed";
            $order = $this->updateRazorpay('Failed', static::PAYMENT_FAILED, $rpoid);
        }
        //$order = Order::where('order_group_id', $razorpay_ordergroupid)->first();

        //return ["status"=>$status, 'paymentid' => $request->razorpay_payment_id   ];
        return ["status"=>$status,"amount"=> $order->total, 'reponseid' => $request->razorpay_payment_id, 'responsestring' => '', 'paymentid' => $request->razorpay_payment_id, 'order' => @$order   ];

    }
    
    public function checkPaymentStatus($paymentgateway, $response)
    {
        $payment_id = $response['paymentid'];
        $compRazorpay = CompanyRazorpay::getCompanyPaydetails(1, getHeaderStoreGroup());
        $razApi = new Api($compRazorpay['key_id'], $compRazorpay['key_secret']);

        $paymentData = $razApi->payment->fetch($payment_id);
        if($paymentData)
        {
            $razor = RazorpayModel::where('razorpay_id', $paymentData->order_id)->first();
            $razor->update([
                'total_fees'                => ($paymentData->fee)/100,
                'tax_amount'                => ($paymentData->tax)/100,
                'amount_paid'               => ($paymentData->amount)/100,
                'balance_payment_response'  => json_encode($paymentData->toArray())
            ]);
            Order::where('order_id', $razor->order_id)->update([
                'order_payment_gateway'         => $paymentgateway,
                'order_payment_gateway_fees'    => ($paymentData->fee)/100,
                'order_payment_gateway_tax'     => ($paymentData->tax)/100
            ]);
            if($razor->status == "success")
            {
                $defaultFinance = config('finance.default');
                $financeClass = config("finance.{$defaultFinance}");
                $financeObj = new $financeClass();
                $fData = [
                    'fees'              => ($paymentData->fee)/100,
                    'tax'               => ($paymentData->tax)/100,
                    'order_id'          => $razor->order_id,
                    'paymentgateway'    => $paymentgateway
                ];
                $financeObj->financeAutopostings($fData, 'paymentgateway');

                $order = Order::find($razor->order_id);
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
    }

    // Create new order in Razorpay and get the order id and ref id.
    public function processPayment($request, $podToOnline = 0)
    {
        $storegroupid = getHeaderStoreGroup();
        //try {
            $company_id = CompanyBranch::where('br_Id', auth_user()->deli_branch_id)->first();
            $compid =  $company_id->comp_id ?? 1;
            $companyrazorpay = CompanyRazorpay::getCompanyPaydetails($compid, $storegroupid);
            // Razorpay API
            $api = new Api($companyrazorpay['key_id'], $companyrazorpay['key_secret']);
            $company_name = '';
            try{
                $company_name = $companyrazorpay['company_name'];
            }
            catch (\Exception $e){
                $company_name = '';
            }
            // Create new order
            $orderData = [
                'receipt'         => $request["order_id"],
                'amount'          => $request->total  * 100, // rupees in paise
                'currency'        => $companyrazorpay['cny'] //'INR'
            ];
            $response = $api->order->create($orderData);

            // Log response.
            $this->addRazorpay($response, $request);
            session(['order_group_id' => $request["order_group_id"]]);
            // Result.
            $data = [
                "key"               => $companyrazorpay['key_id'],
                "amount"            => $response->amount,
                "name"              => $company_name, //"Retaline",
                "description"       => "Hypershop",
                "image"             => "https://cdn.razorpay.com/logos/FFATTsJeURNMxx_medium.png",
                "prefill"           => [
                "name"              => auth_user()->cust_customer_name,
                "email"             => auth_user()->cust_email,
                "contact"           => auth_user()->cust_mobile,
                ],
                "notes"             => [
                "address"           => "",
                "merchant_order_id" => $request["order_group_id"],
                ],
                "theme"             => [
                "color"             => "#99cc33"
                ],
                "order_id"          => $response->id,
                "id" => $response->id
            ];
            $this->updateOrderStatus($request["order_group_id"], $podToOnline);

            return $data;
    }

    public function getNarrationDetails($order_id)
    {
        $order = Order::where('order_id', $order_id)->first();
        $details = RazorpayModel::where('order_id', $order_id)->first();
        if(!$details)
        {
            $details = RazorpayModel::where('order_group_id', $order->order_group_id)->first();
        }

        return $this->getNarrationData($details);
    }

    private function getNarrationData($details)
    {
        $outs = [];
        if($details)
        {
            $response = json_decode($details->balance_payment_response);
            if($response)
            {
                switch ($response->method)
                {
                    case 'card':
                        $type = $response->card->network;
                        break;
                    case 'netbanking':
                        $type = $response->bank;
                        break;
                    case 'wallet':
                        $type = $response->wallet;
                        break;
                    default:
                        $type = '';
                        break;
                }
                $mode = ($response->method == 'card') ? "{$response->card->type} Card" : $response->method;
                $outs['payment_type'] = [
                    'mode'  => ucwords($mode),
                    'type'  => ucwords($type)
                ];
                $outs['reference'] = $response->order_id;
            }
        }
        return $outs;
    }
    private function findOrder($paymentId)
    {
        $crcId = crc32($paymentId);

        return Order::where('order_payment_gateway_req_refid_crc32', $crcId)
            ->where('order_payment_gateway_req_refid', $paymentId)
            ->latest()->firstOrFail();
    }
    
    private function addRazorpay($response, $request)
    {
        $razor = RazorpayModel::where('order_id', $request['order_id'])->first();
                //where('order_group_id', $request['order_order_id'])->first();

        if(isset($razor)){

            try{

                $dateTimeObject1 = date_create($razor->created_at); 
                $difference = date_diff($dateTimeObject1, NOW()); 
                $minutes = $difference->days * 24 * 60;
                $minutes += $difference->h * 60;
                $minutes += $difference->i;

                if($minutes < 60)
                    return null;
            }
            catch (\Exception $e){}

            $calls = $razor->calls;
            $calls = $calls+1;
            return $razor->update(['razorpay_id' =>  $response->id,'calls' => $calls, 'updated_at' => NOW()]);
        }

       return RazorpayModel::create([
            "customer_id" => auth_user()->cust_id,
            "order_id" => $request['order_id'],
            "order_group_id" => $request['order_group_id'],
            "razorpay_id" => $response->id,
            //"razorpay_id_crc32" => crc32($response['id']),
            "currency" => $response->currency,
            "amount_due" => ($response->amount_due)/100,
            "amount_paid" => $response->amount_paid,
            "amount" => ($response['amount'])/100,
            "status" => $response->status,
            "receipt" => $response->receipt,
            "created_at" => $response->created_at,
            "notes" => json_encode($response->notes->toArray()),
            "attempts" => $response->attempts,
            'response' => json_encode($response->toArray())
        ]);
    }

    private function updateRazorpay($status, $payment_status, $razorpay_order_id)
    {
        $razorpay = RazorpayModel::where('razorpay_id', $razorpay_order_id)->first();
        if($razorpay)
        {
            $order_group_id = $razorpay->order_group_id;
            $orderId = $razorpay->order_id;

            $data = [
                'status' => $status,
            ];
            $razorpay->update($data);

            if($order_group_id == '0' && $orderId > 0)
                return Order::where('order_id', $orderId)->first();

            return Order::where('order_group_id', $order_group_id)->first();
        }
    }

    public function verify($request,$isredirect=true)
    {
    }

    private function getOrder($order_group_id)
    {
        return Order::where('order_group_id', $order_group_id)    
                    ->select('order_order_id','order_id','order_payment_response_received','status_id','order_branch_id', 'total')
                    ->first();
    }

    private function updateOrderStatus($order_group_id, $podToOnline)
    {
        if($podToOnline != 1)
        {
            return Order::where('order_group_id', $order_group_id)
            ->update([
                'payment_mode' => static::ONLINE,
                'status_id' => CustomerOrderStatus::PAYMENT_INITIATED,
                'order_confirm_date' => now()->format('Y-m-d'),
                'order_confirmed_on' =>  now()->format('Y-m-d H:i:s'),
                //'order_customer_cancel_till' => $this->getAfterBookingDelayTime(time(),1),
                //'order_delivery_start_at' => $this->getAfterBookingDelayTime(time(),2)
            ]);
        }
    }

    public function checkScheduledPaymentStatus($order_id, $type = "payment")
    {
        $razorData = RazorpayModel::select('customer_id', 'order_id', 'order_group_id', 'razorpay_id', 'balance_payment_response')->where('order_group_id', $order_id)->first();
        if($razorData)
        {
            $getOrder = Order::select('storegroup_id')->where('order_group_id', $order_id)->first();
            $storeGroup = @$getOrder->storegroup_id ?? 0;
            $payResponse = json_decode($razorData->balance_payment_response);
            $razorKeys = CompanyRazorpay::getCompanyPaydetails(1, $storeGroup);
            if($razorKeys)
            {
                $razorAPI = new Api($razorKeys['key_id'], $razorKeys['key_secret']);
                $paymentDetails = $razorAPI->order->fetch($razorData->razorpay_id)->payments();

                $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
                $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
                $dynamoClient->putItem([
                'TableName' => config('aws.prefix').'timedout_order_payments',
                    'Item'      => [
                        'uuid'          => ['S' => (string)$uuid],
                        'tstamp'        => ['S' => (string)date('Y-m-d H:i:s')],
                        'order_id'      => ['S' => (string)$order_id],
                        'gateway'       => ['S' => "razorpay"],
                        "type"          => ['S' => "payment"],
                        'apiURL'        => ['S' => ""],
                        'apiResponse'   => ['S' => json_encode($paymentDetails->toArray())]
                    ]
                ]);
                if(@$paymentDetails->count > 0)
                {
                    $items = @$paymentDetails->items;
                    $details = array_column($items, 'status');
                    if(in_array('captured', $details))
                    {
                        if($type == 'refund')
                        {
                            $items = @$paymentDetails->toArray()['items'];
                            if($items)
                            {
                                $refundTransactn = $this->orderRefunds($items, $razorAPI, $razorData->order_id);
                                $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
                                $dynamoClient->putItem([
                                'TableName' => config('aws.prefix').'timedout_order_payments',
                                    'Item'      => [
                                        'uuid'          => ['S' => (string)$uuid],
                                        'tstamp'        => ['S' => (string)date('Y-m-d H:i:s')],
                                        'order_id'      => ['S' => (string)$order_id],
                                        "type"          => ['S' => "refund"],
                                        'gateway'       => ['S' => "razorpay"],
                                        'apiURL'        => ['S' => ""],
                                        'apiResponse'   => ['S' => json_encode($refundTransactn->toArray())]
                                    ]
                                ]);
                                if(@$refundTransactn->status == "processed")
                                {
                                    return $razorData->order_id;
                                }
                            }
                            
                        }
                        else
                        {
                            return $this->updateScheduledDetails($paymentDetails, $razorAPI, $razorData->order_id);
                        }
                    }
                }
            }
        }
        return false;
    }
    
    public function cancellationRefunds($order_id, $amount)
    {
        $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
        $razorData = RazorpayModel::where('order_group_id', $order_id)->first();
        if($razorData)
        {
            $payResponse = json_decode($razorData->balance_payment_response);
            $razorKeys = CompanyRazorpay::getCompanyPaydetails(1, getHeaderStoreGroup());
            if($razorKeys)
            {
                $razorAPI = new Api($razorKeys['key_id'], $razorKeys['key_secret']);
                $paymentDetails = $razorAPI->order->fetch($razorData->razorpay_id)->payments();

                $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
                $dynamoClient->putItem([
                    'TableName' => config('aws.prefix').' order_refunds_log',
                    'Item'      => [
                        'uuid'          => ['S' => (string)$uuid],
                        'tstamp'        => ['S' => (string)date('Y-m-d H:i:s')],
                        'order_id'      => ['S' => (string)$order_id],
                        'gateway'       => ['S' => "razorpay"],
                        "type"          => ['S' => "payment"],
                        'reqType'       => ['S' => "Check Payment"],
                        'apiURL'        => ['S' => ""],
                        'apiResponse'   => ['S' => json_encode($paymentDetails->toArray())]
                    ]
                ]);
                if(@$paymentDetails->count > 0)
                {
                    $items = @$paymentDetails->items;
                    $details = array_column($items, 'status');
                    if(in_array('captured', $details))
                    {
                        $items = @$paymentDetails->toArray()['items'];
                        if($items)
                        {
                            $refundTransactn = $this->orderRefunds($items, $razorAPI, $razorData->order_id, $amount);
                            $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
                            $dynamoClient->putItem([
                                'TableName' => config('aws.prefix').' order_refunds_log',
                                'Item'      => [
                                    'uuid'          => ['S' => (string)$uuid],
                                    'tstamp'        => ['S' => (string)date('Y-m-d H:i:s')],
                                    'order_id'      => ['S' => (string)$order_id],
                                    'gateway'       => ['S' => "razorpay"],
                                    "type"          => ['S' => "refund"],
                                    'reqType'       => ['S' => "Refund"],
                                    'apiURL'        => ['S' => ""],
                                    'apiResponse'   => ['S' => json_encode($refundTransactn->toArray())]
                                ]
                            ]);
                            if(@$refundTransactn->status == "processed")
                            {
                                return [ 
                                    "id"        => $razorData->order_id,
                                    "request"   => json_encode([
                                        "amount"    => $refundTransactn->amount,
                                        "speed"     => "normal",
                                        "receipt"   => "Refund for {$order_id}"
                                    ]),
                                    "response"  => json_encode($refundTransactn->toArray())
                                ];
                            }
                        }
                    }
                }
            }
        }
        return false;
    }


    private function updateScheduledDetails($request, $razorAPI, $order_id)
    {
        $key = array_keys(
            array_filter($request->items, function($item){
                return ($item->status == "captured");
            })
        );
        $request = $request->toArray();
        $reqItem = @$request['items'][@$key[0]];
        if($reqItem)
        {
            $updateRazor = RazorpayModel::where('razorpay_id', $reqItem['order_id'])->update([
                'status'                    => 'success',
                'total_fees'                => $reqItem['fee'] / 100,
                'tax_amount'                => $reqItem['tax'] / 100,
                'amount_paid'               => $reqItem['amount'] / 100,
                'balance_payment_response'  => json_encode($request)
            ]);
            $updateOrder = Order::where('order_id', $order_id)->update([
                'order_payment_gateway'         => 'razorpay',
                'order_payment_gateway_fees'    => $reqItem['fee'] / 100,
                'order_payment_gateway_tax'     => $reqItem['tax'] / 100
            ]);

            $order = Order::find($order_id);
            $postReq = new Request();
            $postReq->setMethod('POST');
            $postReq->request->add([
                'order_id'              => $order->order_id,
                'finascopEventRefId'    => '07801eb4-38d7-11ee-9967-065723bafb24',
                'storegroup_id'         => ($order->storegroup_id ? $order->storegroup_id : 0)
            ]);
            (new PostingRepository)->finascopPosting($postReq);

            return $reqItem['order_id'];
        }
        return false;
    }
    private function orderRefunds($items, $razorAPI, $order_id, $amount = 0)
    {
        $paymentData = array_values(array_filter(array_map(function ($item) {
            if($item['status'] == 'captured')
            return $item;
        }, $items)));
        if(@$paymentData[0])
        {
            $paymentData = $paymentData[0];
            $refAmount = ($amount > 0) ? ($amount * 100) : $paymentData['amount'];
            $refundPayment = $razorAPI->payment->fetch($paymentData['id'])->refund([
                "amount"    => $refAmount,
                "speed"     => "normal",
                "receipt"   => "Refund for {$order_id}"
            ]);
            return $refundPayment;
        }
        return false;
    }
}
