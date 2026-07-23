<?php

namespace App\PaymentGateways;

use App\Models\Order;
use App\Models\CompanyRazorpay;
use App\Models\CompanyBranch;
use App\Exceptions\MsgException;
use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Domains\EasyPay\AesForJava;
use App\Models\Payment\RazorpayModel;
use App\Domains\Atom\AtomTransactionRequest;
use App\Domains\Atom\AtomTransactionResponse;
use App\PaymentGateways\InterfacePaymentGateway;
use App\Models\FinanceAutopostingValues;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use BackOffice\Status\CustomerOrderStatus;
use Illuminate\Http\Request;
use App\Http\Repositories\PostingRepository;

class RazorPayment implements InterfacePaymentGateway
{
    protected const ONLINE_PAYMENT = 2;

    const PAYMENT_SUCCESS = 1;

    const PAYMENT_FAILED = 2;
    const ONLINE = 2;

    public function paymentComplete($request,$compid)
    {
        $respType = "api";
        $userAgent = @$request["HTTP_USER_AGENT"] ?? "";
        $status = "";
        $success = false;
        $error = "Payment Failed";
        $storegroupid = getHeaderStoreGroup();

        if(str_contains($userAgent, "Razorpay-Webhook"))
        {
            $respType = "webhook";
            $payload = file_get_contents('php://input');
            $webhookSignature = is_object($request) && method_exists($request, 'header')
                ? ($request->header('X-Razorpay-Signature') ?? '')
                : (@$request['HTTP_X_RAZORPAY_SIGNATURE'] ?? '');
            $webhookPayload = json_decode($payload);
            $webhookUpdate = $this->webhookUpdates($webhookPayload, $payload, $webhookSignature);
            if($webhookUpdate['status'] == 'updated')
            {
                return $webhookUpdate;
            }
            if($webhookUpdate['status'] == 'declined')
            {
                return ["status" => 'declined', "amount" => 0, 'reponseid' => '', 'responsestring' => 'Invalid webhook signature', 'paymentid' => '', 'order' => null, "type" => @$respType];
            }
            $paymentID = @$webhookUpdate["paymentID"];
            $rpoid = @$webhookUpdate["rpoid"];
        }
        else
        {
            $paymentID = @$request->razorpay_payment_id;
            $rpoid = @$request->rpoid;
        }

        if ($paymentID && $rpoid)
        {
            $compid = 1;
            $customer = auth_user();
            $orderData = null;
            if(!$customer)
            {
                $razorData = RazorpayModel::where('razorpay_id', $rpoid)->first();
                if(!$razorData)
                {
                    return ["status" => 'declined', "amount" => @$order->total, 'reponseid' => @$paymentID, 'responsestring' => '', 'paymentid' => $paymentID, 'order' => @$order, "type" => @$respType];
                }
                $orderData = Order::where('order_group_id', $razorData->order_group_id)->first();
                $customer = @$orderData->customer;
                $branch = @$orderData->branchDetails;
                $storegroupid = $branch->br_storeGroup;
            }

            try{
                $company_id = CompanyBranch::where('br_Id', $customer->deli_branch_id)->first();
                $compid =  $company_id->comp_id ?? 1;
            }
            catch(\Exception $e)
            {
                $compid = 1;
            }
            $companyrazorpay = CompanyRazorpay::getCompanyPaydetails($compid, $storegroupid);
            $razorData = RazorpayModel::where('razorpay_id', $rpoid)->first();
            try
            {
                // Razorpay API
                $api = new Api($companyrazorpay['key_id'], $companyrazorpay['key_secret']);
                // Fetch payment
                $payment = $api->payment->fetch($paymentID);
                if ($payment['status'] === 'captured')
                {
                    $expectedAmountPaise = null;
                    if ($razorData) {
                        $expectedAmountPaise = (int) round($razorData->amount * 100);
                    } elseif (@$orderData) {
                        $expectedAmountPaise = (int) round($orderData->total * 100);
                    }
                    if ($expectedAmountPaise !== null && (int) $payment['amount'] !== $expectedAmountPaise) {
                        Log::warning('Razorpay payment amount mismatch', [
                            'payment_id' => $paymentID,
                            'expected_paise' => $expectedAmountPaise,
                            'paid_paise' => (int) $payment['amount'],
                        ]);
                        $success = false;
                    } else {
                        $success = true;
                    }
                }
            }
            catch(\Exception $e)
            {
                info("razorpay exception error");info($e);
                $success = false;
            }
        }

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
        return ["status" => $status, "amount" => @$order->total, 'reponseid' => @$paymentID, 'responsestring' => '', 'paymentid' => $paymentID, 'order' => @$order, "type" => @$respType];
    }


    private function webhookUpdates($webhookPayload, $rawPayload = null, $webhookSignature = null)
    {
        $eventType = $webhookPayload->event;
        switch($eventType)
        {
            case "payment.captured":
            case "order.paid":
                $paymentID = $webhookPayload->payload->payment->entity->id;
                $rpoid = $webhookPayload->payload->payment->entity->order_id;
                break;

            default:
                $paymentID = NULL;
                $rpoid = NULL;              
                break;
        }
        $razorData = RazorpayModel::where('razorpay_id', $rpoid)->first();
        if ($rawPayload && $webhookSignature && $razorData) {
            $orderData = Order::where('order_group_id', $razorData->order_group_id)->first();
            $storegroupid = @$orderData->storegroup_id ?: getHeaderStoreGroup();
            try {
                $company_id = CompanyBranch::where('br_Id', @$orderData->order_branch_id)->first();
                $compid = $company_id->comp_id ?? 1;
            } catch (\Exception $e) {
                $compid = 1;
            }
            $companyrazorpay = CompanyRazorpay::getCompanyPaydetails($compid, $storegroupid);
            $webhookSecret = config('paymentgateway.razorpay.webhook_secret', env('RP_WEBHOOK_SECRET', ''));
            if (!$webhookSecret) {
                Log::warning('Razorpay webhook secret not configured');
                return ["status" => "declined"];
            }
            try {
                $api = new Api($companyrazorpay['key_id'], $companyrazorpay['key_secret']);
                $api->utility->verifyWebhookSignature($rawPayload, $webhookSignature, $webhookSecret);
            } catch (SignatureVerificationError $e) {
                Log::warning('Razorpay webhook signature verification failed', ['error' => $e->getMessage()]);
                return ["status" => "declined"];
            }
        }
        if($razorData)
        {
            $orderData = Order::where('order_group_id', $razorData->order_group_id)->first();
            if(@$orderData->status_id >= CustomerOrderStatus::SUCCESS)
            {
                return [
                    "status"            => "updated",
                    "amount"            => $orderData->total,
                    'reponseid'         => $paymentID,
                    'responsestring'    => "",
                    'paymentid'         => $paymentID,
                    'order'             => $orderData,
                    'type'              => "webhook"
                ];
            }
        }
        if($paymentID == NULL)
        {
            return ["status"    => "declined"];
        }
        return [
            "status"    => "success",
            "paymentID" => $paymentID,
            "rpoid"     => $rpoid,
        ];
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
                $order = Order::find($razor->order_id);
                $postReq = new Request();
                $postReq->setMethod('POST');
                $postReq->request->add([
                    'order_id'              => $order->order_id,
                    'finascopEventRefId'    => config("event_master.paymentSuccess"),
                    'storegroup_id'         => ($order->storegroup_id ? $order->storegroup_id : 0)
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
            $company_name = '';
            try{
                $company_name = $companyrazorpay['company_name'];
            }
            catch (\Exception $e){
                $company_name = '';
            }
            try
            {
                // Razorpay API
                $api = new Api($companyrazorpay['key_id'], $companyrazorpay['key_secret']);
                // Create new order
                $orderData = [
                    'receipt'         => $request["order_id"],
                    'amount'          => $request->total  * 100, // rupees in paise
                    'currency'        => $companyrazorpay['cny'] //'INR'
                ];
                $response = $api->order->create($orderData);
            }
            catch (\Exception $e){info("Razorpay Create Order Error => {$e}");}

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
}
