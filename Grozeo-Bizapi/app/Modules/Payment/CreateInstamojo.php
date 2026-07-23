<?php

namespace App\Modules\Payment;

use Instamojo;
use App\Models\Order;
use App\Models\Branch;
use App\Events\OrderHistory;
use App\Payment\InstamojoModel;
use App\Exceptions\ErrException;
use App\Exceptions\MsgException;
use App\Modules\BlockedProducts;
use App\Models\UploadPrescription;
use App\Modules\CustomerPickupOtp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\MarginDistributionb2c;
use App\Http\Services\B2CToTransferOrder;
use App\Modules\Payment\InterfacePayment;
use BackOffice\Status\CustomerOrderStatus;
use App\Http\Repositories\Finascop\StoreFinascop;


//use BackOffice\Models\TransferOrder;
//use BackOffice\Models\InventoryDetails;
//use BackOffice\Models\PurchaseOrderDetails;

class CreateInstamojo implements InterfacePayment {

    private $order;

    const PAYMENT_SUCCESS = 1;
    const PAYMENT_FAILED = 2;

    private $mojo;

    public function __construct(Order $order, InstamojoModel $mojo) {
        $this->order = $order;
        $this->mojo = $mojo;
    }

    public function createPayment(array $data) {
        try {
            $api = new Instamojo\Instamojo(
                    config('payment.instamojo.api_key'), config('payment.instamojo.auth_token'), config('payment.instamojo.url')
            );
            $date = \gmdate("Y-m-d H:i:s", strtotime('+595 seconds'));
            $response = $api->paymentRequestCreate(array(
                "purpose" => "Pharmacy",
                "amount" => $data['total'],
                "send_email" => false,
                "email" => auth()->user()->cust_email,
                "redirect_url" => route('payment.result'),
                "phone" => auth()->user()->cust_mobile,
                "send_sms" => false,
                "buyer_name" => auth()->user()->cust_customer_name,
                "expires_at" => $date,
                "webhook" => route('payment.webhook'),
            ));
            $this->addInstamojo($response, $data);
            event(new OrderHistory($data['order_id'], CustomerOrderStatus::PAYMENT_INITIATED));
            return $response;
        } catch (\Exception $e) {
            throw new ErrException($e->getMessage());
        }
    }

    private function addInstamojo($response, $data) {
        return $this->mojo->create([
                    "customer_id" => auth()->user()->cust_id,
                    "order_id" => $data['order_id'],
                    "order_order_id" => $data['order_order_id'],
                    "instamojo_id" => $response['id'],
                    "instamojo_id_crc32" => crc32($response['id']),
                    "phone" => $response['phone'],
                    "email" => $response['email'],
                    "name" => $response['buyer_name'],
                    "amount" => $response['amount'],
                    "purpose" => $response['purpose'],
                    "payment_status" => 0,
                    "long_url" => $response['longurl'],
                    "inst_created_at" => $response['created_at'],
                    "inst_modified_at" => $response['modified_at'],
                    "redirect_url" => $response['redirect_url'],
                    "status" => "Pending"
        ]);
    }

    public function verifyPayment(array $data) {
        $orders = $this->findPaymentOrder($data['id']);
        $order_recieve = $this->getOrder($orders->order_id);
        // try {
        $api = new Instamojo\Instamojo(
                config('payment.instamojo.api_key'), config('payment.instamojo.auth_token'), config('payment.instamojo.url')
        );
        $response = $api->paymentRequestStatus($data['id']);
        if (empty($order_recieve)) {
            return $response;
        }
        $this->paymentProcess($response, $orders->order_id, $order_recieve, $orders->customer_id);
        return $response;
        // }
        // catch (\Exception $e) {
        //     throw new ErrException($e->getMessage());
        // }
    }

    private function paymentProcess($response, $order_id, $order, $customer_id) {
        $status = $response['payments'][0]['status'] ?? '';
        if ($status === 'Credit') {
            DB::transaction(function () use($customer_id, $order_id, $response, $status, $order) {
                $ref_id = $response['payments'][0]['payment_id'] ?? '';
                BlockedProducts::markedForDelivery($order_id, $customer_id);
                $order_status = CustomerOrderStatus::SUCCESS;
                $UploadPrescription = UploadPrescription::where('order_id', $order_id)->count();
                if ($UploadPrescription > 0) {
                    $order_status = CustomerOrderStatus::ON_HOLD;
                }
                $this->updateOrderStatus($order_status, $order_id, "Success", $ref_id);
                if ($UploadPrescription == 0) {
                    B2CToTransferOrder::transferOrders($order_id);
                    StoreFinascop::store(config('paymentgateway.default'), $customer_id, $order_id);
                    $datas = $this->getmargindistrinution($order_id, $customer_id);
                    //StoreFinascop::margindistribution('10a-MarginDistribuProcesQue', $datas['order'], $datas['ho'], $datas['company'], $datas['cs'], $datas['distributor'], $datas['retailor'], $datas['incentive'], $datas['deliverycharge']);
                }

                $this->updateInstamojo($response, $status, static::PAYMENT_SUCCESS, $order_id);
                CustomerPickupOtp::sendOtp($order_id);
                event(new OrderHistory($order_id, $order_status));
            });
        } else {
            DB::transaction(function () use($order_id, $response, $status, $order) {
                $this->updateOrderStatus(CustomerOrderStatus::PAYMENT_FAILED, $order_id, 'Failed');
                $this->updateInstamojo($response, 'Failed', static::PAYMENT_FAILED, $order_id);
                BlockedProducts::unsetMarkedItems($order_id);
                event(new OrderHistory($order_id, CustomerOrderStatus::PAYMENT_FAILED));
            });
        }
    }

    private function getmargindistrinution($order_id, $customer_id) {
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
//        $transorder = TransferOrder::where('fsto_id', $order_id)
//                ->where('fsto_ordertype', 1)
//            ->first(); 
//        $fstobarcodes = TransferOrderDetailsBarcodes::where('fsto_id', $transorder->fsto_id)                                
//                                ->get()
//                                ->toArray();
//        $companyMargin = 0; $operationMargin = 0; $csMargin = 0; $distributorMargin = 0;  $stiid_poLandingCostleastSKU = 0;  $totGST = 0;
//        $retailMargin = 0; $courierMargin = 0; $driverMargin = 0; 
//foreach($fstobarcodes as $key => $fstobarcod)
//        {
//    $poDetail = InventoryDetails::where('stiid_id', $fstobarcod[$key]['stiid_id'])
//                          ->select('stiid_fpoid','stiid_fpodid','stiid_itemmasterid','stiid_poLandingCostleastSKU')
//                          ->first()  ;
//    $margins = PurchaseOrderDetails::where('fpod_id', $poDetail->stiid_fpodid)
//                          ->select('fpod_companyMarginCD','fpod_incentiveMarginCD','fpod_csMarginCD','fpod_distributorMarginCD','fpod_retailorMarginCD','fpod_courierMarginCD','fpod_companyMarginHD',
//'fpod_incentiveMarginHD','fpod_csMarginHD','fpod_distributorMarginHD','fpod_retailorMarginHD','fpod_driverMarginHD','fpod_companyMargin','fpod_incentiveMargin','fpod_csMargin','fpod_distributorMargin',
//'fpod_retailorMargin','fpod_gstHmDel','fpod_gstCouDel','fpod_gstPikup')
//                          ->first()  ;
//    $stiid_poLandingCostleastSKU = $stiid_poLandingCostleastSKU + $poDetail['stiid_poLandingCostleastSKU'];
//    switch($order->order_method){
//            case 1://delivery
//                $companyMargin = $companyMargin + $margins->fpod_companyMarginHD;
//                $operationMargin = $operationMargin + $margins->fpod_incentiveMarginHD;
//                $csMargin = $csMargin + $margins->fpod_csMarginHD;
//                $distributorMargin = $distributorMargin + $margins->fpod_distributorMarginHD;
//                $retailMargin = $retailMargin + $margins->fpod_retailorMarginHD;
//                $driverMargin = $driverMargin + $margins->fpod_driverMarginHD;
//                $totGST = $totGST + $margins->fpod_gstHmDel;
//                break;
//            case 2://collect
//                $companyMargin = $companyMargin + $margins->fpod_companyMargin;
//                $operationMargin = $operationMargin + $margins->fpod_incentiveMargin;
//                $csMargin = $csMargin + $margins->fpod_csMargin;
//                $distributorMargin = $distributorMargin + $margins->fpod_distributorMargin;
//                $retailMargin = $retailMargin + $margins->fpod_retailorMargin;
//                $totGST = $totGST + $margins->fpod_gstPikup;
//                break;
//            case 3://courier
//                $companyMargin = $companyMargin + $margins->fpod_companyMarginCD;
//                $operationMargin = $operationMargin + $margins->fpod_incentiveMarginCD;
//                $csMargin = $csMargin + $margins->fpod_csMarginCD;
//                $distributorMargin = $distributorMargin + $margins->fpod_distributorMarginCD;
//                $retailMargin = $retailMargin + $margins->fpod_retailorMarginCD;
//                $courierMargin = $courierMargin + $margins->fpod_courierMarginCD;
//                $totGST = $totGST + $margins->fpod_gstCouDel;
//                break;
//        }
//}
        
        $data['ho'] = new \stdClass();
        $data['company'] = new \stdClass();
        $data['order'] = new \stdClass();
        $data['cs'] = new \stdClass();
        $data['distributor'] = new \stdClass();
        $data['retailor'] = new \stdClass();
        $data['incentive'] = new \stdClass();
        $data['deliverycharge'] = new \stdClass();

        //$data['ho']->amt = $amount->total;
        $data['ho']->br_ReferenceID = $cpd->br_ReferenceID;
        $data['company']->amt = round(($amount->total * $marginDistributions->bmd_company) / 100,2);
        $data['company']->br_ReferenceID = $cpd->br_ReferenceID;
        $data['order']->order_id = $order->order_id;
        $data['order']->order_order_id = $order->order_order_id;
        $data['order']->total = $amount->total;
        $data['order']->order_confirm_date = $order->order_confirm_date;
        $data['order']->order_branch_id = $order->order_branch_id;
        $data['cs']->amt = round(($amount->total * $marginDistributions->bmd_hub) / 100,2);
        $data['cs']->br_ReferenceID = $centralStore->br_ReferenceID;
        $data['distributor']->amt = round(($amount->total * $marginDistributions->bmd_distributor) / 100,2);
        $data['distributor']->br_ReferenceID = $distributor->br_ReferenceID;
        $data['retailor']->amt = round(($amount->total * $marginDistributions->bmd_retailor) / 100,2);
        $data['retailor']->br_ReferenceID = $retailer->br_ReferenceID;
        $data['incentive']->amt = round(($amount->total * $marginDistributions->bmd_incentive) / 100,2);
        $data['incentive']->br_ReferenceID = $retailer->br_ReferenceID;
        $data['deliverycharge']->amt = round(($amount->total * $marginDistributions->bmd_driver) / 100,2);
        $data['deliverycharge']->br_ReferenceID = $retailer->br_ReferenceID;

        $data['ho']->amt = $data['deliverycharge']->amt + $data['incentive']->amt + $data['retailor']->amt + $data['distributor']->amt + $data['cs']->amt + $data['company']->amt;
        $data['ho']->amt = round($data['ho']->amt,2);

        return $data;
        // return array('order' => array('order_id' => $order->order_id, 'order_order_id' => $order->order_id, 'total' => $order->total), 'company' => array('amt' => 0, 'br_ReferenceID' => 'sdfsd'));
    }

    private function updateOrderStatus($status = 0, $order_id, $msg, $ref_id = 0) {
        $data = [
            'status_id' => $status,
            'order_payment_response_received' => 1,
            'order_payment_status' => $msg,
            'order_payment_gateway' => 'Instamojo'
        ];
        if ($status === CustomerOrderStatus::SUCCESS) {
            $data['order_payment_gateway_refid'] = $ref_id;
        }
        return $this->order->where('order_id', $order_id)
                        ->update($data);
    }

    private function updateInstamojo($response, $status, $payment_status, $order_id) {
        $instamojo = $this->mojo->where('order_id', $order_id)
                ->firstOrFail();
        $data = [
            'payment_status' => $payment_status,
            'status' => $status,
            'response' => json_encode($response),
        ];
        if ($payment_status === static::PAYMENT_SUCCESS) {
            $data['mojo_id'] = $response['payments'][0]['payment_id'] ?? '';
            $data['currency'] = $response['payments'][0]['currency'] ?? '';
            $data['fees'] = $response['payments'][0]['fees'] ?? '';
        }
        return $instamojo->update($data);
    }

    protected function findPaymentOrder($paymentId) {
        $crcId = crc32($paymentId);

        return $this->mojo->where('instamojo_id_crc32', $crcId)
                        ->where('instamojo_id', $paymentId)
                        ->firstOrFail();
    }

    private function getOrder($order_id) {
        return $this->order->where('order_id', $order_id)
                        ->where('order_payment_response_received', 0)
                        ->select('order_order_id')
                        ->first();
    }

    public function instamojoStatus(array $paymentId) {
        $data = $this->findPaymentOrder($paymentId['payment_request_id']);
        return [
        "payment_status" => $data->payment_status ?? 0,
        "msg" => $data->status ?? ''
        ];
    }

}
