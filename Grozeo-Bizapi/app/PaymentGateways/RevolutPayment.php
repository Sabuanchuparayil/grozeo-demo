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
use App\Http\Repositories\PaymentStatusCheckRepository;
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
            'token'     => @$response->token,
            'longurl'   => @$response->checkout_url
        ];
    }

    public function paymentComplete($request, $compid)
    {
        // info("REVOLUT WEBHOOK REQUEST");info($request->all());
        try
        {
            $revolutID = @$request->order_id;
            $event = @$request->event;
            $eventType = $this->getEventType($event);
            $revoData = RevolutModel::where('transaction_id', $revolutID)->first();

            $paymentStat = (new PaymentStatusCheckRepository)->paymentStatusCheck(@$revoData->order_id, $eventType);
            
            if(!$paymentStat)
            {
                $outs['status'] = 'declined';
                return $outs;
            }
            $headers = [
                "Authorization: Bearer {$this->creds['secretKey']}",
                "Revolut-Api-Version: {$this->creds['apiVersion']}",
                "Content-Type: application/json"
            ];
            $url = strtr($this->creds['getOrder'], ["{#orderID}" => $revolutID]);
            $response = (new HttpCurlCalls)->curlCall($url, "", 'GET', $headers);
            // info("REVOLUT GET ORDER RESPONSE");info(json_encode($response));
            if($response->id == $revolutID)
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
            // info("RevolutPayment paymentComplete ERROR");info($e);
            return [
                "status"    => "failed"
            ];
        }
    }

    public function sendPaymentUrl($order_group_id)
    {
        return RevolutModel::select('iframe_path')->where('order_group_id', $order_group_id)->first();
    }

    public function checkPaymentStatus($paymentgateway, $response)
    {
        $revData = RevolutModel::where('transaction_id', $response['paymentid'])->first();

        if($revData->status == "Success")
        {
            $order = Order::find($revData->order_id);
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
                $fees = array_sum(array_column($feesList[0], 'amount'));
                $fees = $fees / 100;
            }
            $tax = $fees * $this->creds['taxCalc'];
            $paymentMethod = array_values(array_column($response->payments, "payment_method"));
            $paymentMethod = @$paymentMethod[0] ?? [];
            RevolutModel::where('transaction_id', $response->id)->update([
                'bank_ref_no'               => $response->id,
                'total_fees'                => $fees,
                'tax_amount'                => $tax,
                'payment_mode'              => @$paymentMethod->type,
                'balance_payment_response'  => json_encode($response),
                'status'                    => $response->state
            ]);
            $order = Order::find($revoData->order_id);
            if(@$order->status_id >= 4)
            {
                Order::where('order_id', $revoData->order_id)->update([
                    'payment_mode'                  => ($order->payment_mode > 0) ? $order->payment_mode : static::ONLINE,
                    'status_id'                     => CustomerOrderStatus::PAYMENT_INITIATED,
                    'order_confirm_date'            => now()->format('Y-m-d'),
                    'order_confirmed_on'            =>  now()->format('Y-m-d H:i:s'),
                    'order_payment_gateway'         => 'revolut',
                    'order_payment_gateway_fees'    => $fees,
                    'order_payment_gateway_tax'     => $tax
                ]);
                return $order;
            }
            else
            {
                Order::where('order_group_id', $revoData->order_group_id)->update([
                    'payment_mode'                  => ($order->payment_mode > 0) ? $order->payment_mode : static::ONLINE,
                    'status_id'                     => CustomerOrderStatus::PAYMENT_INITIATED,
                    'order_confirm_date'            => now()->format('Y-m-d'),
                    'order_confirmed_on'            =>  now()->format('Y-m-d H:i:s'),
                    'order_payment_gateway'         => 'revolut',
                    'order_payment_gateway_fees'    => $fees,
                    'order_payment_gateway_tax'     => $tax
                ]);
                return Order::where('order_group_id', $revoData->order_group_id)->first();
            }
        }
    }
    private function getEventType($event)
    {
        switch ($event)
        {
            case 'ORDER_PAYMENT_FAILED':
                return "failed";
                break;
            case 'ORDER_CANCELLED':
                return "failed";
                break;
            case 'ORDER_PAYMENT_DECLINED':
                return "failed";
                break;
            case 'ORDER_COMPLETED':
                return "success";
                break;
        }
        return "";
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