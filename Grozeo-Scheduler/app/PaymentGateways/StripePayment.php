<?php

namespace App\PaymentGateways;

use App\Models\Order;
use App\Models\CompanyStripe;
use App\Models\CompanyBranch;
use App\Models\FinanceAutopostingValues;
use App\Http\Repositories\PostingRepository;
use App\Exceptions\MsgException;
use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use App\Domains\EasyPay\AesForJava;
use App\Models\Payment\StripeModel;
use App\Domains\Atom\AtomTransactionRequest;
use App\Domains\Atom\AtomTransactionResponse;
use App\PaymentGateways\InterfacePaymentGateway;
use App\Status\CustomerOrderStatus;
use Illuminate\Http\Request;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\Log;


require __DIR__.'/../../vendor/stripe/init.php';

use Stripe\Stripe;
use Stripe\WebhookEndpoint;

class StripePayment implements InterfacePaymentGateway
{
    protected const ONLINE_PAYMENT = 2;

    const PAYMENT_SUCCESS = 1;

    const PAYMENT_FAILED = 2;
    const ONLINE = 2;

    private $apiKey;

    private $stripeService;

    public function __construct()
    {
        $compid =  1;
        $companystripe = CompanyStripe::getCompanyPaydetails($compid);
        $this->apiKey = $companystripe['secret_key'];
        $this->stripeService = new Stripe();
        $this->stripeService->setApiKey($this->apiKey);
    }


    public function paymentComplete($request,$compid)
    {
        //$company_id = CompanyBranch::where('br_Id', auth_user()->deli_branch_id)->first();
        $compid =  1; //$company_id->comp_id ?? 1;
        $companystripe = CompanyStripe::getCompanyPaydetails($compid);

        \Stripe\Stripe::setApiKey($companystripe['secret_key']);

        $json = file_get_contents("php://input");
        //$file = fopen("app.log", "a");
        $webhookSecret = $companystripe['webhook_key'];
        if(!isset($webhookSecret) || $webhookSecret == "")
            $webhookSecret = config('paymentgateway.stripe.webhook_key');

        //fwrite($file, $json);
        $sig_header = $request['HTTP_STRIPE_SIGNATURE']; //$_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;
        try {
            $event = \Stripe\Webhook::constructEvent($json, $sig_header, $webhookSecret); // "WEBHOOK_SECRET_HERE"
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }

        if (! empty($event)) {
            $orderId = $event->data->object->metadata->order_id;
            $eventType = $event->type;
            //fwrite($file, $event);

            $orderId = $event->data->object->metadata->order_id;
            $email = $event->data->object->metadata->email;
            $paymentIntentId = $event->data->object->id;
            $amount = $event->data->object->amount;
            $stripePaymentStatus = $event->data->object->status;
            $status = $paymentStatus = "failed";
            if ($eventType == "payment_intent.payment_failed") {
                $orderStatus = 'Payement Failure';
                $paymentStatus = 'Unpaid';
                $amount = $amount / 100;
                $ordergroupid = $this->updateOrder($paymentIntentId, $orderId, $orderStatus, $paymentStatus, $stripePaymentStatus, $event);
            }
            if ($eventType == "payment_intent.succeeded") {
                $orderStatus = 'Completed';
                $paymentStatus = 'Paid';
                $amount = $amount / 100;
                $status = "success";
                $ordergroupid = $this->updateOrder($paymentIntentId, $orderId, $orderStatus, $paymentStatus, $stripePaymentStatus, $event);
                //http_response_code(200);
            }


            $balStripe = new \Stripe\StripeClient($companystripe['secret_key']);
            $balTrans = '';
            if(isset($event->data->object->charges))
            {
                $balTrans = $event->data->object->charges->data[0]->balance_transaction;
            }
            else
            {
                $stripeChargeData = $balStripe->charges->retrieve($event->data->object->latest_charge, []);
                if($stripeChargeData)
                {
                    $balTrans = $stripeChargeData->balance_transaction;
                }
            }
            if($balTrans != '')
            {
                // CHECK PAYMENT FEE AND TAX
                $balanceTrans = $balStripe->balanceTransactions->retrieve($balTrans, []);
                $balanceTrans = $balanceTrans->toArray();
                if(!empty($balanceTrans))
                {
                    $taxamount = 0;
                    $feedetails = $balanceTrans['fee_details'];
                    $feeDetKey = array_search('tax', 
                        array_map(function($feedetail)
                        {
                            return $feedetail['type'];
                    }, $feedetails));
                    if($feeDetKey !== false)
                    {
                        $taxamount = $feedetails[$feeDetKey]['amount'];
                    }
                    if (StripeModel::where('order_hash', '=', $orderId)->exists())
                    {
                        $spaydata = StripeModel::where('order_hash', $orderId)->first();
                        $spaydata->update([
                            'total_fees'                => ($balanceTrans['fee'])/100,
                            'tax_amount'                => $taxamount,
                            'balance_payment_response'  => json_encode($balanceTrans)
                        ]);
                    }
                    $order = Order::where('order_group_id', $orderId)->first();
                    $order->update([
                        'order_payment_gateway'         => 'stripe',
                        'order_payment_gateway_fees'    => ($balanceTrans['fee'])/100,
                        'order_payment_gateway_tax'     => $taxamount
                    ]);
                    if($paymentStatus == "Paid")
                    {
                        $postReq = new Request();
                        $postReq->setMethod('POST');
                        $postReq->request->add([
                            'order_id' => $orderId,
                            'finascopEventRefId'     => '07801eb4-38d7-11ee-9967-065723bafb24',
                            'storegroup_id' => ($order->storegroup_id ? $order->storegroup_id : 0)
                        ]);

                        (new PostingRepository)->finascopPosting($postReq); 
                    }
                }
            }
        }

        //$uniqid = $request['UNIQUE_ID'];
	    $uniqid = '';

        $order = Order::where('order_group_id', $orderId)->first();
        return ["status"=>$status,"amount"=> $order->total, 'reponseid' => $uniqid, 'responsestring' => '', 'paymentid' => $uniqid, 'order' => $order ];

    }

    // Create new order in Stripe and get the order id and ref id.
    public function processPayment($request, $podToOnline = 0)
    {
            $company_id = CompanyBranch::where('br_Id', auth_user()->deli_branch_id)->first();
            $compid =  $company_id->comp_id ?? 1;
            $companystripe = CompanyStripe::getCompanyPaydetails($compid);
            $primaryAddress = auth_user()->primaryAddress;
            $addr = $primaryAddress->deli_house_no . ' ' . $primaryAddress->deli_house_name . ' ' . $primaryAddress->deli_city . ' ' . $primaryAddress->deli_state;
            try {
                $this->stripeService->setApiKey($this->apiKey);
                $metaData = array(
                    "email" => auth_user()->cust_email,
                    "order_id" => $request["order_group_id"]
                );

                $paymentIntent = \Stripe\PaymentIntent::create([
                    'description' => '',//$notes,
                    'shipping' => [
                        'name' => auth_user()->cust_customer_name,
                        'address' => [
                            'line1' => $addr,//$customerDetailsArray["address"],
                            'postal_code' => $primaryAddress->deli_delivery_pin,//$customerDetailsArray["postalCode"],
                            'country' => config('app.operating_country') //$customerDetailsArray["country"]
                        ]
                    ],
                    'amount' => $request->total * 100,
                    'currency' => $companystripe['currency'],
                    'payment_method_types' => [
                        'card'
                    ],
                    'metadata' => $metaData
                ]);
                $this->addStripe($request["order_group_id"], auth_user()->cust_email, $request->total, $companystripe['currency'], 'Pending', 'stripe', '', auth_user()->cust_customer_name, $addr, config('app.operating_country'), $primaryAddress->deli_delivery_pin, @$paymentIntent->id);
                $this->updateOrderStatus($request["order_group_id"], $podToOnline);

                //$output = array(
                //    "status" => "success",
                //    "response" => array('orderHash' => $orderReferenceId, 'clientSecret'=>$paymentIntent->client_secret)
                //);
                $output = $paymentIntent;

            } catch (\Error $e) {
                $output = array(
                    "status" => "error",
                    "response" => $e->getMessage()
                );
            }
            return $output;

    }

    public function getNarrationDetails($order_id)
    {
        $order = Order::where('order_id', $order_id)->first();
        $details = StripeModel::where('order_id', $order_id)->first();
        if(!$details)
        {
            $details = StripeModel::where('order_hash', $order->order_group_id)->first();
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
                $outs['payment_type'] = [
                    'mode'  => 'Card',
                    'type'  => ""
                ];
                $outs['reference'] = $response->id;
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
    
    private function addStripe($orderReferenceId, $email, $unitAmount, $currency, $orderStatus, $paymentType, $notes, $name, $address, $country, $postalCode, $paymentIntentId)
    {
        $order_date = date("Y-m-d H:i:s");
        if (StripeModel::where('order_hash', '=', $orderReferenceId)->exists()) {
            $stripe = StripeModel::where('order_hash', $orderReferenceId)->first();
            //$calls = $stripe->calls;
            //$calls = $calls+1;
            return $stripe;//->update(['calls' => $calls, 'updated_at' => NOW()]);
        }

       return StripeModel::create([
            'order_hash'                => $orderReferenceId,
            'payer_email'               => $email,
            'amount'                    => $unitAmount,
            'currency'                  => $currency,
            'payment_type'              => $paymentType,
            'order_date'                => $order_date,
            'order_status'              => $orderStatus,
            'notes'                     => $notes,
            'name'                      => $name,
            'address'                   => $address,
            'country'                   => $country,
            'postal_code'               => $postalCode,
            'stripe_payment_intent_id'  => $paymentIntentId
        ]);
    }

    private function updateOrder($paymentIntentId, $orderId, $orderStatus, $paymentStatus, $stripePaymentStatus, $stripeResponse)
    {
        $stripe = StripeModel::where('order_hash', $orderId)
                        ->first();
        $order_group_id = $stripe->order_hash;
        $data = [
            'stripe_payment_intent_id' => $paymentIntentId,
            'payment_status' => $paymentStatus,
            'order_status' => $orderStatus,
            'stripe_payment_status' => $stripePaymentStatus,
            'stripe_payment_response' => $stripeResponse
        ];

        $stripe->update($data);

        return $order_group_id;
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
        $stripeData = StripeModel::where('order_hash', $order_id)->first();
        if($stripeData)
        {
            $intent = $stripeData->stripe_payment_intent_id;
            if($intent)
            {
                $companystripe = CompanyStripe::getCompanyPaydetails(1);
                $stripe = new \Stripe\StripeClient($companystripe['secret_key']);
                $intentData = $stripe->paymentIntents->retrieve($intent, []);

                $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
                $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
                $dynamoClient->putItem([
                'TableName' => config('aws.prefix').'timedout_order_payments',
                    'Item'      => [
                        'uuid'          => ['S' => (string)$uuid],
                        'tstamp'        => ['S' => (string)date('Y-m-d H:i:s')],
                        'order_id'      => ['S' => (string)$order_id],
                        'gateway'       => ['S' => "stripe"],
                        "type"          => ['S' => "payment"],
                        'apiURL'        => ['S' => ""],
                        'apiResponse'   => ['S' => json_encode($intentData)]
                    ]
                ]);
                if((@$intentData->amount_received > 0) && (@$intentData->status == "succeeded"))
                {
                    if($type == 'refund')
                    {
                        $refundTransactn = $this->orderRefunds(@$intentData->amount_received, $stripe, $intentData->latest_charge);
                        $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
                        $dynamoClient->putItem([
                        'TableName' => config('aws.prefix').'timedout_order_payments',
                            'Item'      => [
                                'uuid'          => ['S' => (string)$uuid],
                                'tstamp'        => ['S' => (string)date('Y-m-d H:i:s')],
                                'order_id'      => ['S' => (string)$order_id],
                                "type"          => ['S' => "refund"],
                                'gateway'       => ['S' => "stripe"],
                                'apiURL'        => ['S' => ""],
                                'apiResponse'   => ['S' => json_encode($refundTransactn)]
                            ]
                        ]);
                        if(@$refundTransactn->status == "succeeded")
                        {
                            return $intentData->id;
                        }
                        return false;
                    }
                    else
                    {
                        $this->updateScheduledDetails($intentData, $stripe, $order_id);
                    }
                    return $intentData->id;
                }
            }
        }
        return false;
    }

    public function cancellationRefunds($order_id, $amount)
    {
        $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
        $stripeData = StripeModel::where('order_hash', $order_id)->first();
        if($stripeData)
        {
            $intent = $stripeData->stripe_payment_intent_id;
            if($intent)
            {
                $companystripe = CompanyStripe::getCompanyPaydetails(1);
                $stripe = new \Stripe\StripeClient($companystripe['secret_key']);
                $intentData = $stripe->paymentIntents->retrieve($intent, []);

                $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
                $dynamoClient->putItem([
                    'TableName' => config('aws.prefix').'order_refunds_log',
                    'Item'      => [
                        'uuid'          => ['S' => (string)$uuid],
                        'tstamp'        => ['S' => (string)date('Y-m-d H:i:s')],
                        'order_id'      => ['S' => (string)$order_id],
                        'gateway'       => ['S' => "stripe"],
                        "type"          => ['S' => "payment"],
                        'reqType'       => ['S' => "Check Payment"],
                        'apiURL'        => ['S' => ""],
                        'apiResponse'   => ['S' => json_encode($intentData)]
                    ]
                ]);
                if((@$intentData->amount_received > 0) && (@$intentData->status == "succeeded"))
                {
                    $amount *= 100;
                    $refundAmount = ($amount <= $intentData->amount_received) ? $amount : $intentData->amount_received;
                    try
                    {
                        $refundTransactn = $this->orderRefunds($refundAmount, $stripe, $intentData->latest_charge);
                    }
                    catch(\Exception $e)
                    {
                        Log::error("Stripe cancellationRefunds failed for order {$order_id}: " . $e->getMessage());
                        $refundTransactn = null;
                    }
                    $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
                    $dynamoClient->putItem([
                        'TableName' => config('aws.prefix').'order_refunds_log',
                        'Item'      => [
                            'uuid'          => ['S' => (string)$uuid],
                            'tstamp'        => ['S' => (string)date('Y-m-d H:i:s')],
                            'order_id'      => ['S' => (string)$order_id],
                            'gateway'       => ['S' => "stripe"],
                            "type"          => ['S' => "refund"],
                            'reqType'       => ['S' => "Refund"],
                            'apiURL'        => ['S' => ""],
                            'apiResponse'   => ['S' => json_encode($refundTransactn)]
                        ]
                    ]);
                    if ($refundTransactn && @$refundTransactn->status == "succeeded") {
                        return [
                            "id"        => $intentData->id,
                            "request"   => json_encode([
                                'charge'    => $intentData->latest_charge,
                                'amount'    => $refundAmount
                            ]),
                            "response"  => json_encode($refundTransactn)
                        ];
                    }
                    return false;
                }
            }
        }
        return false;
    }

    private function updateScheduledDetails($request, $stripe, $order_id)
    {
        $updateStripeData = [
            "payment_status"            => 'Completed',
            "order_status"              => "success",
            "stripe_payment_status"     => 'Paid',
            "stripe_payment_response"   => json_encode($request),
        ];

        $chargeData = $stripe->charges->retrieve($request->latest_charge, []);
        if($chargeData)
        {
            $balanceTrans = $stripe->balanceTransactions->retrieve($chargeData->balance_transaction, []);
            if($balanceTrans)
            {
                $feedetails = $balanceTrans->fee_details;
                $feeDetKey = array_search('tax', 
                    array_map(function($feedetail)
                    {
                        return $feedetail['type'];
                }, (array)$feedetails));
                $taxamount = 0;
                if($feeDetKey)
                {
                    $taxamount = @$feedetails[$feeDetKey]['amount'];
                }
                $updateStripeData["total_fees"] = ($balanceTrans['fee'])/100;
                $updateStripeData["tax_amount"] = $taxamount;
                $updateStripeData["balance_payment_response"] = json_encode($balanceTrans);

                $updateOrder = Order::where('order_group_id', $order_id)->update([
                    'order_payment_gateway'         => 'stripe',
                    'order_payment_gateway_fees'    => ($balanceTrans['fee'])/100,
                    'order_payment_gateway_tax'     => $taxamount
                ]);
                $updateStripe = StripeModel::where('stripe_payment_intent_id', $request->id)->update($updateStripeData);
                $order = Order::where('order_group_id', $order_id)->first();
                $postReq = new Request();
                $postReq->setMethod('POST');
                $postReq->request->add([
                    'order_id'              => $order->order_id,
                    'finascopEventRefId'    => '07801eb4-38d7-11ee-9967-065723bafb24',
                    'storegroup_id'         => ($order->storegroup_id ? $order->storegroup_id : 0)
                ]);
            }
        }

    }
    private function orderRefunds($latCharge, $stripe, $chargeID)
    {

        if(@$latCharge > 0)
        {
            $refundTransactn = $stripe->refunds->create([
                'charge'    => $chargeID,
                'amount'    => $latCharge
            ]);
            return $refundTransactn;
        }
        return false;
    }
}
