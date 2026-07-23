<?php
namespace App\PaymentGateways;

use App\Models\Order;
use App\Models\Payment\EasebuzzModel;
use App\Models\CompanyEasebuzz;
use App\Models\CompanyBranch;
use App\Models\UploadPrescription;
use App\Modules\CustomerPickupOtp;
use Illuminate\Support\Facades\Log;
use App\Http\Services\B2CToTransferOrder;
use BackOffice\Status\CustomerOrderStatus;
use App\Http\Repositories\Payment\AfterPayment;
use App\Models\FinanceAutopostingValues;
use App\Http\Repositories\PostingRepository;
use App\Http\Repositories\Finascop\StoreFinascop;
use App\PaymentGateways\InterfacePaymentGateway;
use Illuminate\Http\Request;
use App\Helpers\HttpCurlCalls;

class EasebuzzPayment implements InterfacePaymentGateway
{
    protected $ebCreds;

    protected const ONLINE_PAYMENT = 2;
    const PAYMENT_SUCCESS = 1;
    const PAYMENT_FAILED = 2;
    const ONLINE = 2;

    public function __construct()
    {
        $this->ebCreds = $this->init();
    }

    public function processPayment($request, $podToOnline = 0)
    {
        $storegroupid = getHeaderStoreGroup();
        $params = [
            'txnid'         => $request->order_group_id,
            'amount'        => (double)$request->total,
            'firstname'     => auth_user()->cust_customer_name,
            'email'         => auth_user()->cust_email,
            'phone'         => auth_user()->primaryAddress->deli_contact_no,
            'productinfo'   => $request->order_group_id,
            'surl'          => $this->ebCreds['successUrl'],
            'furl'          => $this->ebCreds['failureUrl'],
            'key'           => $this->ebCreds['merchantID']
        ];

        $hashKey = $this->getHashKey($params, $this->ebCreds['saltKey']);
        $params['hash'] = $hashKey;

        $apiUrl = $this->ebCreds['paymentURL']."payment/initiateLink";
        $generateAccessKey = (new HttpCurlCalls)->curlCall($apiUrl, http_build_query($params), 'POST', []);

        $returns = [
            "key"           => $this->ebCreds['merchantID'],
            "theme"         => [
                "color"     => "#99cc33"
            ],
            "amount"        => $request->total,
            "order_id"      => (string)$request->order_id,
            "mode"          => (@config("easebuzz.type") == 'live') ? "prod" : "test",
            "id"            => '',
            "name"          => $this->ebCreds['company_name'],
            "description"   => "Hypershop",
            "image"         => "",
            "prefill"       => [
                "name"      => auth_user()->cust_customer_name,
                "email"     => auth_user()->cust_email,
                "contact"   => auth_user()->cust_mobile,
            ]
        ];
        if($generateAccessKey->status == 1)
        {
            $data = [
                'customer_id'       => auth_user()->cust_id,
                'order_id'          => $request->order_id,
                'order_group_id'    => $params['txnid'],
                'transaction_id'    => $params['txnid'],
                'amount'            => $params['amount'],
                'iframe_path'       => $generateAccessKey->data
            ];
            $insData = $this->addEasebuzz($data);
            $this->updateOrderDetails($request->order_group_id, $podToOnline);
            $returns['id'] = $generateAccessKey->data;
        }
        return $returns;
    }

    public function paymentComplete($request, $compid)
    {
        $response = $request;
        $status = 'failed';

        $order = $this->updateEasebuzz($request);
        $ccData = EasebuzzModel::where('order_group_id', $response['order_id'])->first();
        if ($order && $ccData && (@$request['status'] === 'success' || @$ccData->status === 'success')) {
            $status = 'success';
        }
        return [
            "status"            => $status,
            "amount"            => $order->total,
            'reponseid'         => $ccData->transaction_id,
            'responsestring'    => '',
            'paymentid'         => $ccData->transaction_id,
            'order'             => $order
        ];
    }
    
    public function sendPaymentUrl($order_group_id)
    {
        return EasebuzzModel::select('iframe_path')->where('order_group_id', $order_group_id)->first();
    }

    public function checkPaymentStatus($paymentgateway, $response)
    {
        $ebData = EasebuzzModel::where('transaction_id', $response['paymentid'])->first();
        $ebFees = @$ebData->total_fees ? $ebData->total_fees : 0;
        $ebTax = @$ebData->tax_amount ? $ebData->tax_amount : 0;
        Order::where('order_id', $ebData->order_id)->update([
            'order_payment_gateway'         => $paymentgateway,
            'order_payment_gateway_fees'    => $ebFees,
            'order_payment_gateway_tax'     => $ebTax
        ]);
        if($ebData->status == "success")
        {
            /* $defaultFinance = config('finance.default');
            $financeClass = config("finance.{$defaultFinance}");
            $financeObj = new $financeClass();
            $fData = [
                'fees'              => $ebFees,
                'tax'               => $ebTax,
                'order_id'          => $ebData->order_id,
                'paymentgateway'    => $paymentgateway
            ];
            $financeObj->financeAutopostings($fData, 'paymentgateway'); */
            /* if(config("paymentgateway.{$paymentgateway}.tax") == 'inclusive')
            {
                $mdrTax = $ebFees - $ebTax;
                $apUpdate['MerchantDiscountRate_MDR'] = $mdrTax;
            }
            else
            {
                $apUpdate['MerchantDiscountRate_MDR'] = $ebFees;
            }
            if(@config("paymentgateway.{$paymentgateway}.b_type") == 'intra')
            {
                $apUpdate['CGSTInputonMDR'] = $ebTax/2;
                $apUpdate['SGSTInputonMDR'] = $ebTax/2;
            }
            else
            {
                $apUpdate['IGSTInputonMDR'] = $ebTax;
            }
            $autoPosting = FinanceAutopostingValues::where('order_id', $ebData->order_id)->update($apUpdate); */

            $order = Order::find($ebData->order_id);
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

    private function updateOrderDetails($order_group_id, $podToOnline)
    {
        if($podToOnline != 1)
        {
            return Order::where('order_group_id', $order_group_id)
            ->update([
                'payment_mode' => static::ONLINE,
                'status_id' => CustomerOrderStatus::PAYMENT_INITIATED,
                'order_confirm_date' => now()->format('Y-m-d'),
                'order_confirmed_on' =>  now()->format('Y-m-d H:i:s'),
            ]);
        }
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
                    'mode'  => $response->card_type,
                    'type'  => $response->bank_name
                ];
                $outs['reference'] = $response->bank_ref_num;
            }
        }
        return $outs;
    }
    private function addEasebuzz($data)
    {
        return EasebuzzModel::create($data);
    }
    private function updateEasebuzz($request)
    {
        $fees = $request['amount'] * ($request['deduction_percentage'] / 100);
        $tax = $fees * 0.18;
        EasebuzzModel::where('order_group_id', $request['txnid'])->update([
            'bank_ref_no'               => $request['bank_ref_num'],
            'transaction_id'            => $request['easepayid'],
            'amount_due'                => $request['net_amount_debit'],
            'amount_paid'               => $request['amount'],
            'deduction_percentage'      => $request['deduction_percentage'],
            'total_fees'                => $fees,
            'tax_amount'                => $tax,
            'payment_mode'              => $request['mode'],
            'balance_payment_response'  => json_encode($request),
            'status'                    => $request['status']
        ]);
        Order::where('order_group_id', $request['txnid'])->update([
            'payment_mode'          => static::ONLINE,
            'status_id'             => CustomerOrderStatus::PAYMENT_INITIATED,
            'order_confirm_date'    => now()->format('Y-m-d'),
            'order_confirmed_on'    =>  now()->format('Y-m-d H:i:s'),
        ]);

        return Order::where('order_group_id', $request['order_id'])->first();
    }
    private function init()
    {
        $compid = 1;
        try
        {
            $company_id = CompanyBranch::where('br_Id', auth_user()->deli_branch_id)->first();
            $compid =  $company_id->comp_id ?? 1;
        }
        catch(\Exception $e)
        {
            $compid = 1;
        }
        $getEBData = CompanyEasebuzz::getCompanyPaydetails($compid, getHeaderStoreGroup());
        return [
            "merchantID"    => $getEBData['merchantID'],
            "successUrl"    => $getEBData['successUrl'],
            "failureUrl"    => $getEBData['failureUrl'],
            "paymentURL"    => $getEBData['paymentURL'],
            "saltKey"       => $getEBData['saltKey'],
            "company_name"  => $getEBData['company_name']
        ];
    }
    private function getHashKey($posted, $salt_key)
    {
        $hash_sequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
        $hash_sequence_array = explode('|', $hash_sequence);
        $hash = null;

        foreach ($hash_sequence_array as $value)
        {
            $hash .= isset($posted[$value]) ? $posted[$value] : '';
            $hash .= '|';
        }
        $hash .= $salt_key;
        return strtolower(hash('sha512', $hash));
    }
}