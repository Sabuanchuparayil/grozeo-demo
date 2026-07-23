<?php

namespace App\PaymentGateways;

use App\Domains\Paytm\PaytmPayment;

class Paytm
{
   
    public static function paymentPaytm(array $request)
    {
        return (new static)->processPayment($request);
    }

    public function processPayment($request)
    {
        try
        {
            $paytmParams = [];
            $paytmParams["MID"] = config('paymentgateway.paytm.merchant_mid');
            $paytmParams["ORDER_ID"] = $orderId = $request['order_order_id'];
            $paytmParams["CUST_ID"] = auth_user()->cust_customer_id;
            $paytmParams["MOBILE_NO"] = auth_user()->cust_mobile;
            $paytmParams["EMAIL"] = auth_user()->cust_email;
            $paytmParams["CHANNEL_ID"] = "WAP";
            $paytmParams["TXN_AMOUNT"] = $request['total_amount'];
            $paytmParams["WEBSITE"] = "WEBSTAGING";
            $paytmParams["INDUSTRY_TYPE_ID"] = "Retail";
            $paytmParams["CALLBACK_URL"] = config('paymentgateway.paytm.callback_url') . "?ORDER_ID={$orderId}";
            $paytmChecksum = (new PaytmPayment)->getChecksumFromArray($paytmParams, config('paymentgateway.paytm.merchant_key'));

            return [
                'checksum' => $paytmChecksum,
                'order_id' => $orderId,
                'cust_id'  => $paytmParams["CUST_ID"],
                'mobile_no' => $paytmParams["MOBILE_NO"],
                'email' => $paytmParams["EMAIL"],
                'txn_amount' => $paytmParams["TXN_AMOUNT"],
                'callback_url' => $paytmParams["CALLBACK_URL"],
            ];
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }

}