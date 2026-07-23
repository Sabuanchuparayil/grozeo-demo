<?php

namespace App\PaymentGateways;

use Instamojo;
use App\Models\Order;
use App\Models\UploadPrescription;
use App\Modules\CustomerPickupOtp;
use Illuminate\Support\Facades\Log;
use App\Http\Services\B2CToTransferOrder;
use BackOffice\Status\CustomerOrderStatus;
use App\Http\Repositories\Payment\AfterPayment;
use App\Http\Repositories\Finascop\StoreFinascop;

class InstamojoVerify {
    protected

    const ONLINE_PAYMENT = 2;

    public static function instamojoVerify($request) {
        return (new static)->verify($request);
    }

    public function verify($request) {
        try {
            $api = new Instamojo\Instamojo(
                    config('paymentgateway.instamojo.api_key'), config('paymentgateway.instamojo.auth_token'), config('paymentgateway.instamojo.url')
            );
            $response = $api->paymentRequestStatus($request['id']);
            $this->paymentProcess($response);
            return $response;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function paymentProcess($response) {
        $status = $response['payments'][0]['status'] ?? '';
        if ($status === 'Credit') {
            $customer_id = auth_user()->cust_id ?? 0;
            AfterPayment::minusStock($customer_id);
            $order_status = CustomerOrderStatus::SUCCESS;
            $UploadPrescription = UploadPrescription::where('order_id', $order_id)->count();
            if ($UploadPrescription > 0) {
                $this->updateOrderStatus(CustomerOrderStatus::ON_HOLD);
            } else {
                B2CToTransferOrder::transferOrders($request['order_id']);
                $this->updateOrderStatus(CustomerOrderStatus::SUCCESS);
                StoreFinascop::store(config('paymentgateway.default'), $customer_id, $order_id);
                $datas = $this->getmargindistrinution($order_id);
                StoreFinascop::margindistribution($configid, $datas['order'], $datas['ho'], $datas['company'], $datas['cs'], $datas['distributor'], $datas['retailor'], $datas['incentive'], $datas['deliverycharge']);
            }
            CustomerPickupOtp::sendOtp($order_id);
        } else {
            $this->updateOrderStatus(CustomerOrderStatus::PAYMENT_FAILED);
        }
    }

    private function getmargindistrinution($order_id) {
        //order - order_id, order_order_id, total
        //HOpayable - amt, referenceid 10
        //Company - amt, referenceid (total *10/100)
        //$cs  - amt, referenceid
        //$distri - amt, referenceid
        //$retail - amt, referenceid
        //$incen  - amt, referenceid
        //$delchrg - amt, referenceid

        $order = Order::where('order_id', $order_id)
                ->where('order_customer_id', $customer_id)
                ->first();
        $amount = DB::table('retaline_customer_order_items')
                ->selectraw(' SUM((item_retail_price-item_sales_price)*item_order_qty) as total')                       
                ->where('customer_order_id', $order->order_id)
                ->first() ;                
        $marginDistributions = MarginDistributionb2c::where('is_default', 1)
                ->first();
        $retailer = Branch::where('br_ID', $order->order_branch_id)
                ->first();
        $distributor = Branch::where('br_ID', $retailer->br_cpd)
                ->first();
        $centralStore = Branch::where('br_ID', $distributor->br_cpd)
                ->first();
        $cpd = Branch::where('br_ID', $centralStore->br_cpd)
                ->first();

        $data['ho']->amt = $amount->total;
        $data['ho']->br_referenceID = $cpd->br_ReferenceID;
        $data['company']->amt = ($amount->total * $marginDistributions->bmd_company) / 100;
        $data['company']->br_referenceID = $cpd->br_ReferenceID;
        $data['order']->order_id = $order->order_id;
        $data['order']->order_order_id = $order->order_order_id;
        $data['order']->total = $amount->total;
        $data['order']->order_confirm_date = $order->order_confirm_date;
        $data['order']->order_branch_id = $order->order_branch_id;
        $data['cs']->amt = ($amount->total * $marginDistributions->bmd_hub) / 100;
        $data['cs']->br_referenceID = $centralStore->br_ReferenceID;
        $data['distributor']->amt = ($amount->total * $marginDistributions->bmd_distributor) / 100;
        $data['distributor']->br_referenceID = $distributor->br_ReferenceID;
        $data['retailor']->amt = ($amount->total * $marginDistributions->bmd_retailor) / 100;
        $data['retailor']->br_referenceID = $retailer->br_ReferenceID;
        $data['incentive']->amt = ($amount->total * $marginDistributions->bmd_incentive) / 100;
        $data['incentive']->br_referenceID = $retailer->br_ReferenceID;
        $data['deliverycharge']->amt = ($amount->total * $marginDistributions->bmd_driver) / 100;
        $data['deliverycharge']->br_referenceID = $retailer->br_ReferenceID;

        return $data;
        // return array('order' => array('order_id' => $order->order_id, 'order_order_id' => $order->order_id, 'total' => $order->total), 'company' => array('amt' => 0, 'br_ReferenceID' => 'sdfsd'));
    }

    private function updateOrderStatus($status = 0) {
        return Order::where('order_customer_id', auth_user()->cust_id)
                        ->where('payment_mode', static::ONLINE_PAYMENT)
                        ->where('status_id', CustomerOrderStatus::PAYMENT_INITIATED)
                        ->update(['status_id' => $status]);
    }

}
