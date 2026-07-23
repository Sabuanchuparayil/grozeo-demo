<?php
namespace App\PaymentGateways;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\CompanyRevolut;
use App\Helpers\HttpCurlCalls;
use App\Models\UploadPrescription;
use App\Modules\CustomerPickupOtp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Payment\RevolutModel;
use App\Http\Services\B2CToTransferOrder;
use BackOffice\Status\CustomerOrderStatus;
use App\Models\FinanceAutopostingValues;
use App\Http\Repositories\PostingRepository;
use App\Http\Repositories\Payment\AfterPayment;
use App\PaymentGateways\InterfacePaymentGateway;

class RevolutPayment implements InterfacePaymentGateway
{
    protected $creds;

    protected const ONLINE_PAYMENT = 2;
    const PAYMENT_SUCCESS = 1;
    const PAYMENT_FAILED = 2;
    const ONLINE = 2;

    public function __construct()
    {
        $this->creds = $this->init();
    }


    public function processPayment($request, $podToOnline = 0)
    {
        $params = [
            "amount"                    => $request->total * 100,
            "currency"                  => $this->creds['currency'],
            "customer"                  => [
                "full_name" => auth_user()->cust_customer_name,
                "phone"     => auth_user()->primaryAddress->deli_contact_no,
                "email"     => auth_user()->cust_email,
            ],
            "shipping"                  => [
                "address"   => [
                    "street_line_1"     => auth_user()->primaryAddress->order_address,
                    "street_line_2"     => auth_user()->primaryAddress->order_address2,
                    "city"              => auth_user()->primaryAddress->deli_city,
                    "country_code"      => $this->creds['countryCode'],
                    "postcode"          => auth_user()->primaryAddress->deli_post,
                ]
            ],
            'metadata'                  => [
                "customer_order_id"     => $request->order_group_id
            ],
            "redirect_url"              => $this->creds['redirectUrl']
        ];
        $headers = [
            "Authorization: Bearer {$this->creds['secretKey']}",
            "Revolut-Api-Version: {$this->creds['apiVersion']}",
            "Content-Type: application/json"
        ];
        $response = (new HttpCurlCalls)->curlCall($this->creds['orderURL'], json_encode($params), 'POST', $headers);

        if(@$response->id != "")
        {
            $data = [
                'customer_id'       => auth_user()->cust_id,
                'order_id'          => $request->order_id,
                'receipt'           => $request->order_id,
                'order_group_id'    => $request->order_group_id,
                'transaction_id'    => $response->id,
                'amount'            => $response->amount/100,
                'iframe_path'       => $response->checkout_url,
                'currency'          => $response->currency,
                'request'           => json_encode($params),
                'response'          => json_encode($response)
            ];
            $insData = $this->addRevolut($data);
        }
        return [
            'id'        => @$response->id,
            'longurl'   => @$response->checkout_url
        ];
    }
    public function paymentComplete($request, $compid)
    {
        try
        {
            $revolutID = @$request->order_id;
            $event = @$request->event;
            $headers = [
                "Authorization: Bearer {$this->creds['secretKey']}",
                "Content-Type: application/json"
            ];
            $url = strtr($this->creds['getOrder'], ["{#orderID}" => $revolutID]);
            $response = new HttpCurlCalls($url, "", 'GET', $headers);
            if(@$response->id == $revolutID)
            {
                $status = ($response->state == 'completed') ? "success" : "failed";
                $order = $this->updateRevolut($response);

                $revoData = RevolutModel::where('transaction_id', $response->id)->first();
                return [
                    "status"            => $status,
                    "amount"            => $order->total,
                    'reponseid'         => $revoData->transaction_id,
                    'responsestring'    => '',
                    'paymentid'         => $revoData->transaction_id,
                    'order'             => $order
                ];
            }
        }
        catch (\Exception $e)
        {
            info("RevolutPayment paymentComplete ERROR");info($e);
            return [
                "status"    => "failed"
            ];
        }
    }
    public function sendPaymentUrl($order_group_id)
    {
        return RevolutModel::select('iframe_path')->where('order_group_id', $order_group_id)->first();
    }
    public function checkScheduledPaymentStatus($order_id, $type = "payment")
    {
        $revoData = RevolutModel::where('order_group_id', $order_id)->latest()->first();
        if($revoData)
        {
            $headers = [
                "Authorization: Bearer {$this->creds['secretKey']}",
                "Content-Type: application/json"
            ];
            $url = strtr($this->creds['getOrder'], ["{#orderID}" => $revoData->transaction_id]);
            $orderDetails = new HttpCurlCalls($url, "", 'GET', $headers);
            if(@$orderDetails->id == $revoData->transaction_id)
            {
                $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
                $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
                $dynamoClient->putItem([
                'TableName' => config('aws.prefix').'timedout_order_payments',
                    'Item'      => [
                        'uuid'          => ['S' => (string)$uuid],
                        'tstamp'        => ['S' => (string)date('Y-m-d H:i:s')],
                        'order_id'      => ['S' => (string)$order_id],
                        "type"          => ['S' => "payment"],
                        'gateway'       => ['S' => "revolut"],
                        'apiURL'        => ['S' => $url],
                        'apiResponse'   => ['S' => json_encode($orderDetails)]
                    ]
                ]);
                if($orderDetails->state == 'completed')
                {
                    if($type == "refund")
                    {
                        return $this->orderRefunds($revoData, $orderDetails, $order_id);
                    }
                    else
                    {
                        $this->updateRevolut($respData);
                        $order = Order::find($ccData->order_id);
                        $postReq = new Request();
                        $postReq->setMethod('POST');
                        $postReq->request->add([
                            'order_id'              => $order->order_id,
                            'finascopEventRefId'    => '07801eb4-38d7-11ee-9967-065723bafb24',
                            'storegroup_id'         => ($order->storegroup_id ? $order->storegroup_id : 0)
                        ]);
                        (new PostingRepository)->finascopPosting($postReq);
                    }
                }
            }
        }
        return false;
    }

    public function getNarrationDetails($order_id)
    {
        $order = Order::where('order_id', $order_id)->first();
        $details = RevolutModel::where('order_id', $order_id)->first();
        if(!$details)
        {
            $details = RevolutModel::where('order_group_id', $order->order_group_id)->first();
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
                $paymentMethod = array_values(array_column($response->payments, "payment_method"));
                $paymentMethod = @$paymentMethod[0] ?? [];
                $outs['payment_type'] = [
                    'mode'  => @$paymentMethod->type,
                    'type'  => @$paymentMethod->card_brand." ".@$paymentMethod->funding
                ];
                $outs['reference'] = $response->id;
            }
        }
        return $outs;
    }
    private function orderRefunds($revoData, $orderDetails, $order_id)
    {
        $headers = [
            "Authorization: Bearer {$this->creds['secretKey']}",
            "Content-Type: application/json"
        ];
        $url = strtr($this->creds['refundURL'], ["{#orderID}" => $revoData->transaction_id]);
        $params = [
            "amount"                    => $revoData->amount * 100,
            "description"               => "Refund for cancelled order",
            "merchant_order_ext_ref"    => $revoData->order_group_id,
            "currency"                  => $revoData->currency
        ];
        $refundDetails = new HttpCurlCalls($url, json_encode($params), 'POST', $headers);
        $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
        $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
        $dynamoClient->putItem([
        'TableName' => config('aws.prefix').'timedout_order_payments',
            'Item'      => [
                'uuid'          => ['S' => (string)$uuid],
                'tstamp'        => ['S' => (string)date('Y-m-d H:i:s')],
                'order_id'      => ['S' => (string)$order_id],
                "type"          => ['S' => "refund"],
                'gateway'       => ['S' => "revolut"],
                'apiURL'        => ['S' => $url],
                'apiResponse'   => ['S' => json_encode($refundDetails)]
            ]
        ]);
        if(@$refundDetails->id == $revoData->transaction_id)
        {
            return $refundDetails->id;
        }
    }
    private function addRevolut($data)
    {
        $check = RevolutModel::where([
            ['customer_id', $data['customer_id']],
            ['order_id', $data['order_id']]
        ])->first();
        if($check)
        {
            $revolut = RevolutModel::where('id', $check->id)->update([
                'order_group_id'    => $data['order_group_id'],
                'transaction_id'    => $data['transaction_id'],
                'iframe_path'       => $data['iframe_path']
            ]);
        }
        else
        {
            $revolut = RevolutModel::create($data);
        }
        if($revolut)
        {
            $this->updateOrderStatus($data['order_group_id'], CustomerOrderStatus::CHECKEDOUT, CustomerOrderStatus::PAYMENT_INITIATED);
        }
        return $revolut;
    }
    private function updateRevolut($response)
    {
        $revoData = RevolutModel::where('transaction_id', $response->id)->first();
        if($revoData)
        {
            $fees = 0;
            $feesList = array_column($response->payments, "fees");
            if(@$feesList[0])
            {
                $payments = (array)$payments;
                $fee = array_sum(array_column($feesList[0], 'amount'));
            }
            $tax = $fees * $this->creds['taxCalc'];
            RevolutModel::where('transaction_id', $response->id)->update([
                'bank_ref_no'               => $response->id,
                'total_fees'                => $fees,
                'tax_amount'                => $tax,
                'payment_mode'              => $response->payment_method->type,
                'balance_payment_response'  => json_encode($response),
                'status'                    => $response->state
            ]);

            $order = Order::where('order_id', $revoData->order_id)->first();
            if(@$order->status_id >= 4)
            {
                Order::where('order_id', $revoData->order_id)->update([
                    'payment_mode'          => ($order->payment_mode > 0) ? $order->payment_mode : static::ONLINE,
                    'status_id'                     => CustomerOrderStatus::PAYMENT_INITIATED,
                    'order_confirm_date'            => now()->format('Y-m-d'),
                    'order_confirmed_on'            => now()->format('Y-m-d H:i:s'),
                    'order_payment_gateway_fees'    => $fees,
                    'order_payment_gateway_tax'     => $tax
                ]);
                return $order;
            }
            else
            {
                Order::where('order_group_id', $revoData->order_group_id)->update([
                    'payment_mode'          => ($order->payment_mode > 0) ? $order->payment_mode : static::ONLINE,
                    'status_id'                     => CustomerOrderStatus::PAYMENT_INITIATED,
                    'order_confirm_date'            => now()->format('Y-m-d'),
                    'order_confirmed_on'            => now()->format('Y-m-d H:i:s'),
                    'order_payment_gateway_fees'    => $fees,
                    'order_payment_gateway_tax'     => $tax
                ]);
                return Order::where('order_group_id', $revoData->order_group_id)->first();
            }
        }
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
    private function init()
    {
        $revolutCreds = CompanyRevolut::getCompanyPaydetails(1, getHeaderStoreGroup());
        return [
            "secretKey"     => $revolutCreds['secretKey'],
            "publicKey"     => $revolutCreds['publicKey'],
            "orderURL"      => $revolutCreds['orderURL'],
            "refundURL"     => $revolutCreds['refundURL'],
            "getOrder"      => $revolutCreds['getOrder'],
            "apiVersion"    => $revolutCreds['apiVersion'],
            "redirectUrl"   => $revolutCreds['redirectUrl'],
            "cancelUrl"     => $revolutCreds['cancelUrl'],
            "currency"      => $revolutCreds['currency'],
            "countryCode"   => $revolutCreds['countryCode'],
            "taxCalc"       => $revolutCreds['taxCalc']
        ];
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