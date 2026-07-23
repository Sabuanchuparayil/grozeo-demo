<?php

namespace App\Domains\Paytm;

use GuzzleHttp\Client;
use App\Domains\Paytm\PaytmPayment;
use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\ChecksumMismatchException;

class PaytmGateway implements PaymentGatewayInterface
{
    protected $payment;

    public function __construct()
    {
        $this->payment = new PaytmPayment;
    }

    public function getPaymentDetails($orderId, $amount)
    {
        $paytmParams = [];
        $paytmParams["MID"] = config('paytm.merchant_mid');
        $paytmParams["ORDER_ID"] = $orderId;
        $paytmParams["CUST_ID"] = auth_user()->cust_customer_id;
        $paytmParams["MOBILE_NO"] = auth_user()->cust_mobile;
        $paytmParams["EMAIL"] = auth_user()->cust_email;
        $paytmParams["CHANNEL_ID"] = "WAP";
        $paytmParams["TXN_AMOUNT"] = $amount;
        $paytmParams["WEBSITE"] = "WEBSTAGING";
        $paytmParams["INDUSTRY_TYPE_ID"] = "Retail";
        $paytmParams["CALLBACK_URL"] = config('paytm.callback_url') . "?ORDER_ID={$orderId}";
        $paytmChecksum = $this->payment->getChecksumFromArray($paytmParams, config('paytm.merchant_key'));

        return [
            'checksum' => $paytmChecksum,
            'order_id' => $orderId,
            'customer_id' => $paytmParams["CUST_ID"],
            'mobile_no' => $paytmParams["MOBILE_NO"],
            'email' => $paytmParams["EMAIL"],
        ];
    }

    public function hasValidChecksum($paymentDetails)
    {
        $merchantKey = config('paytm.merchant_key');
        $paytmChecksum = $paymentDetails['CHECKSUMHASH'];
        return $this->payment->verifychecksum_e($paymentDetails, $merchantKey, $paytmChecksum);
    }

    public function getPaymentStatus($paymentDetails)
    {
        if ($this->hasValidChecksum($paymentDetails)) {
            $client = new Client();
            $response = $client->post('https://securegw-stage.paytm.in/order/status', [
                'json' => [
                    'MID' => $paymentDetails['MID'],
                    'ORDERID' => $paymentDetails['ORDERID'],
                    'CHECKSUMHASH' => $paymentDetails['CHECKSUMHASH'],
                ]
            ]);
            return $response;            
        } else {
            throw new ChecksumMismatchException("Checksum mismatch", 400);
        }
    }

}
