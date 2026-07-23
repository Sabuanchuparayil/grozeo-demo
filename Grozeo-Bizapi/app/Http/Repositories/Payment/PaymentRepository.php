<?php

namespace App\Http\Repositories\Payment;

use App\Models\Cart;
use App\Models\Order;
use GuzzleHttp\Client;
use App\Events\OrderHistory;
use App\PaymentGateways\Paytm;
use App\Domains\Paytm\PaytmPayment;
use App\Http\Services\B2CToTransferOrder;
use App\PaymentGateways\InstamojoPayment;
use BackOffice\Status\CustomerOrderStatus;
use App\Http\Repositories\Payment\AfterPayment;
use App\Models\UploadPrescription;
use App\Modules\CustomerPickupOtp;


class PaymentRepository
{
    private $payment;

    protected const ONLINE_PAYMENT = 2;
    
    public function __construct(PaytmPayment $payment)
    {
        $this->payment = $payment;
    }

    public function checkoutProceed(array $request)
    {
        if($request['payment_mode'] == 1)
        {
            return $this->cashOnDelivery($request);
        }
        elseif($request['payment_mode'] == 2)
        {
            return $this->onlinePayment($request);
        }
    }

    private function cashOnDelivery(array $request)
    {   
      
        $cart = $this->checkCart(auth_user()->cust_id);
        $order = $cart ? $this->updateOrderDetails($request, 7) : false;
        $customer_id = auth_user()->cust_id ?? 0;
        isset($order) ? AfterPayment::minusStock($customer_id, $request['order_id']) : false;
        B2CToTransferOrder::transferOrders($request['order_id']);
        isset($order) ? event(new OrderHistory($request['order_id'], CustomerOrderStatus::MANUAL_ASSIGNMENT)) : false;
        return isset($order) ? [
            "order_status" => true,
            "payment_mode" => "Cash on Delivery.",
            "message" => "Order placed Successfully..!",
            "payment_details" => new \stdClass,
       ] : [
            "order_status" => false,
            "payment_mode" => "Cash on Delivery.",
            "message" => "Oops Something went Wrong..!",
            "payment_details" => new \stdClass,
            ];
    }

    private function onlinePayment(array $request)
    {   
        if(in_array($request['payment_gateway'], ['paytm', 'instamojo'], true))
        {
             $flag = true;
        }
        else {
            $flag = false;
        }
        $cart = $flag ? $this->checkCart(auth_user()->cust_id) : false;
        $order = $cart ? $this->updateOrderDetails($request, 1) : false;
        $payment_details = $order ? $this->processPayment($request) : new \stdClass;
        isset($order) ? event(new OrderHistory($request['order_id'], CustomerOrderStatus::PAYMENT_INITIATED)) : false;
        return isset($order) ? [
            "order_status" => true,
            "payment_mode" => "Online",
            "message" => "Order placed Successfully..!",
            "payment_details" => $payment_details,
       ] : [
            "order_status" => false,
            "payment_mode" => "Online",
            "message" => "Oops Something went Wrong..!",
            "payment_details" => new \stdClass,
            ];
    }

    private function updateOrderDetails(array $request, $orderStatus)
    {
        return Order::where('order_id', $request['order_id'])
                ->where('order_customer_id', auth_user()->cust_id)
                ->update([
                    'payment_mode' => $request['payment_mode'],
                    'status_id' => $orderStatus,
                    'order_confirm_date' => now()->format('Y-m-d'),
                    'order_confirmed_on' =>  now()->format('Y-m-d H:i:s'),
                    'order_customer_cancel_till' => PaymentRepository::getAfterBookingDelayTime(time(),1),
                    'order_delivery_start_at' => PaymentRepository::getAfterBookingDelayTime(time(),2)
                    ]);
    }
    public static function getAfterBookingDelayTime($date,$type)
    {
		if($type==1){
			$addseconds  =  config('b2cbooking.customer_cancel_till_seconds') ?? 120;
		}else{
			$addseconds  =  config('b2cbooking.delivery_process_start_at_seconds') ?? 240;
        }
        return date('Y-m-d H:i:s', $date + $addseconds);
    }
    private function checkCart($customer = '')
    {
        return Cart::where('cart_customer_id', $customer)
                          ->exists();
    }


    public function verifyChecksum(array $request)
    {
        $paytmParams = $request;
        $merchantKey = config('paytm.merchant_key');
        $paytmChecksum = $request['CHECKSUMHASH'];
        $isValidChecksum = $this->payment->verifychecksum_e($paytmParams, $merchantKey, $paytmChecksum);
        if ($isValidChecksum) {
            $client = new Client();
            $response = $client->post('https://securegw-stage.paytm.in/order/status', [
                'json' => [
                    'MID' => $request['MID'],
                    'ORDERID' => $request['ORDERID'],
                    'CHECKSUMHASH' => $request['CHECKSUMHASH'],
                    //'TXN_TYPE' => $request['TXN_TYPE'],
                ]
            ]);
            //$this->reduceStockNo(json_decode($response->getBody()->getContents()));
            $txn = json_decode($response->getBody()->getContents());
            $customer_id = auth_user()->cust_id ?? 0;
            if($txn->STATUS == 'TXN_SUCCESS')
            {
                $order_id=$this->getOrderId($request['ORDERID']);
                $order_status=CustomerOrderStatus::SUCCESS;
                $UploadPrescription=UploadPrescription::where('order_id', $order_id)->count();
                if($UploadPrescription>0){
                    $order_status=CustomerOrderStatus::ON_HOLD;
                }
                $this->updateOrderStatus($order_status, $order_id);
                AfterPayment::minusStock($customer_id, $order_id);
                B2CToTransferOrder::transferOrders($request['order_id']);
                CustomerPickupOtp::sendOtp($order_id);
                event(new OrderHistory($order_id, $order_status));
           
            }
            return $txn;
        } else {
            $this->updateOrderStatus(CustomerOrderStatus::PAYMENT_FAILED, $this->getOrderId($request['ORDERID']));
            event(new OrderHistory($this->getOrderId($request['ORDERID']), CustomerOrderStatus::PAYMENT_FAILED));
            return new \stdClass;
        }
    }

    private function updateOrderStatus($status = 0, $order_id = null)
    {
        $query = Order::where('order_customer_id', auth_user()->cust_id)
                    ->where('payment_mode', static::ONLINE_PAYMENT)
                    ->where('status_id', CustomerOrderStatus::PAYMENT_INITIATED);
        if ($order_id) {
            $query->where('order_id', $order_id);
        }
        return $query->update(['status_id' => $status]);
                  
    }

    private function getOrderId($order_id)
    {
        $order = Order::where('order_order_id', $order_id)
                        ->select('order_id')
                        ->first();
        return $order['order_id'] ?? '';
    }

    private function processPayment(array $request)
    {
        if($request['payment_gateway'] === 'paytm')
        {
            return Paytm::paymentPaytm($request);
        }
        else if($request['payment_gateway'] === 'instamojo')
        {
            return InstamojoPayment::payment($request);
        }
    }

}