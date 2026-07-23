<?php

namespace App\Http\Controllers;
use App\Http\Repositories\Finascop\OrderFinascop;

Use Redirect;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Branch;
use App\Models\Customer;
use App\Events\{
    OrderHistory,
    DelayedOrderActions
};
use App\Models\FinanceAutopostingValues;
use App\Http\Repositories\PostingRepository;

use App\Models\BlockedItems;
use Illuminate\Http\Request;
use App\Modules\BlockedProducts;
use App\Models\UploadPrescription;
use App\Modules\CustomerPickupOtp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Events\BookingPaymentSuccess;
use App\Models\MarginDistributionb2c;
use App\Models\Payment\InstamojoModel;
use BackOffice\Models\BranchInventory;
use App\Http\Repositories\Sms\StoreSms;
use App\Http\Repositories\Coupon\Coupon;
use App\PaymentGateways\InstamojoVerify;
use App\Http\Services\B2CToTransferOrder;
use App\Models\PaymentTransactionDetails;
use BackOffice\Status\CustomerOrderStatus;
use App\Http\Repositories\Payment\AfterPayment;

use App\PaymentGateways\InterfacePaymentGateway;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Repositories\Finascop\StoreFinascop;
use App\Http\Repositories\Payment\PaymentRepository;
use App\Models\OrderItem;
use App\Models\ContractPOProducts;
use App\Models\Vendor;
use App\Http\Services\B2CtoSalesOrder;


use App\Models\CompanyRazorpay;
use Razorpay\Api\Api;
use App\Models\Payment\RazorpayModel;



class PaymentResultController extends Controller
{
    public function webhook($paymentgatewayparam){

        $request = $_SERVER;
        $compid = 1;
        try{
            $paymentgatewayarray = explode('-', $paymentgatewayparam);
            $paymentgateway = $paymentgatewayarray[0];
            $compid = $paymentgatewayarray[1];
        } catch (\Exception $e) {
            //throw new ErrorException($e->getMessage());
        }

        $paymentgatewayclass = config("paymentgateway.". $paymentgateway . ".class");
        $paymentGatewayobj = new $paymentgatewayclass();
        $response = $paymentGatewayobj->paymentComplete($request,$compid);

        $status = @$response["status"];
        $statusCheck = ["declined", "updated"];
        $failCondition = (in_array($status, $statusCheck) || in_array(@$response['order'], [NULL, "", []]));
        if($failCondition)
        {
            if(@$response["type"] == "webhook")
                return response()->json(["status" => "success"], 200);
            
            return view('payments.result', compact('status'));
        }
        $user = Customer::find($response['order']->order_customer_id);
        //$request->attributes->add(['authUser' => $user]);
        $paidOrders =  Order::where('order_group_id', $response['order']->order_group_id)
        ->selectraw( 'order_id, order_customer_id,order_order_id,order_group_id,order_branch_type_id, order_branch_id, total' )
        ->get();
		//$orders = $paidOrders->toArray();
		foreach($paidOrders as $order){

			$this->paymentProcess($response, $order, $order['order_customer_id']);
		}

        $redirecturl = $response['order']->order_portal_afterpayment_redirecturl;
        $ordernum = $response['order']->order_order_id;
        if($paymentgatewayparam == "razorpay")
        {
            return response()->json(["status" => "success"], 200);
        }
        if($redirecturl != "")
        {
            $redirecturl = $redirecturl . $ordernum;
            return Redirect::away($redirecturl);
        }
        else{
            return view('payments.result', compact('status'));
        }
    }

    public function store(Request $request,$paymentgatewayparam)
    {
        $compid = -1;
        try{
        $paymentgatewayarray = explode('-', $paymentgatewayparam);
            $paymentgateway = $paymentgatewayarray[0];
            $compid = $paymentgatewayarray[1];
        } catch (\Exception $e) {
            //throw new ErrorException($e->getMessage());
        }
        $podToOnline = 0;
        try{
            if(isset($request->podToOnline))
                $podToOnline = $request->podToOnline;
        }
        catch (\Exception $e){
            $podToOnline = 0;
        }

        $paymentgatewayclass = config("paymentgateway.". $paymentgateway . ".class");
        $paymentGatewayobj = new $paymentgatewayclass();
        $response = $paymentGatewayobj->paymentComplete($request,$compid);

        $user = Customer::find($response['order']->order_customer_id);
        $request->attributes->add(['authUser' => $user]);
        //$paidOrders =  Order::where('order_group_id', $response['order']->order_group_id)
        $paidOrders =  Order::where(function($query) use ($podToOnline, $response){
                    $query->where([
                        [DB::raw($podToOnline), '=', DB::raw(1)],
                        ['order_id', '=', $response['order']->order_id]
                    ])->orWhere([
                        [DB::raw($podToOnline), '=', DB::raw(0)],
                        ['order_group_id', $response['order']->order_group_id]
                    ]);
                })
        ->selectraw( 'order_id, order_customer_id,order_order_id,order_group_id,order_branch_type_id, order_branch_id, total' )
        ->get();

        $this->checkPaymentStatus($paymentgateway, $response);
		//$orders = $paidOrders->toArray();
        if($podToOnline == 0  || $response["status"] == "success"){
		    foreach($paidOrders as $order){
			    $this->paymentProcess($response, $order, 0, $podToOnline);
		    }
        }

        $status = $response["status"];
        $redirecturl = $response['order']->order_portal_afterpayment_redirecturl;
        $ordernum = $response['order']->order_order_id;

        if($redirecturl != "")
        {
            $redirecturl = $redirecturl . $ordernum;
            return Redirect::away($redirecturl);
        }
        else{
            return view('payments.result', compact('status'));
        }
    }


    public function redirectToPayment($order_group_id, $podToOnline = 0)
    {
        try
        {
            $order = Order::where('order_group_id', $order_group_id)->first();
            if($podToOnline == 1)
            {
                $order = Order::where('order_order_id', $order_group_id)->first();
            }
            if($order)
            {
                if($order->order_payment_gateway)
                {
                    $paymentgatewayclass = config("paymentgateway.". $order->order_payment_gateway . ".class");
                    $paymentGatewayobj = new $paymentgatewayclass();
                    $path = $paymentGatewayobj->sendPaymentUrl($order_group_id);
                    if($path)
                    {
                        // return Redirect::away($path->iframe_path);
                        return view('payments.payment-gateway-success', compact('path'));
                    }
                }
            }
            return view('payments.payment-gateway-error');
        }
        catch(\Exception $e)
        {
            return view('payments.payment-gateway-error');
        }
    }

    public function paymentProcessingRedirect()
    {
        return view('payments.payment-gateway-processing');
    }


    public function webstore(Request $request)
    {
        $paymentId = $request->input('payment_request_id');

        $order = $this->findOrder($paymentId);

        $user = Customer::find($order->customer_id);
        $request->attributes->add(['authUser' => $user]);

        $response = $this->instamojoVerifier->verify([
            'id' => $paymentId,
            'order_id' => $order->order_id,
        ]);

        $status = $response['payments'][0]['status'] ?? '';
        $status = ($response['payments'][0]['status']=="Credit") ? 'success':'failed';
        $type="web";
       return redirect(route('payment.redirect', ['status' => $status,"id"=>$paymentId]));
       // return view('payments.result',compact('status','paymentId','type') );


    }
    public function payment_redirect($status,$id){
        return view('payments.order_status', compact('status'));
    }
    private function updatestockcount($customer_id){

        $cartitems =  Cart::where('cart_customer_id', $customer_id)
        ->selectraw( 'cart_product_id as item_id, cart_branch_id as branch_id, cart_order_qty as count' )
        ->get();

        $blockedItem = $cartitems->toArray();

        DB::transaction(function () use ($blockedItem) {
            foreach ($blockedItem as $item) {
                $branchInventoy = BranchInventory::where('stit_id', $item['item_id'])
                    ->where('branch_id', $item['branch_id'])
                    ->select('item_count')
                    ->first();
                if ($branchInventoy) {
                    $reduceCount = $branchInventoy->item_count - $item['count'];
                    BranchInventory::where('stit_id', $item['item_id'])
                        ->where('branch_id', $item['branch_id'])
                        ->update(['item_count' => $reduceCount]);
                }
            }

        });

   }
    private function paymentProcess($response, $order, $custId = 0, $podToOnline=0)
    {
        //["status"=>$status,"amount"=> $amount, 'reponseid' => $ref_id, 'responsestring' => $responsestring  ]
        $customer_id = auth_user()->cust_id ?? $custId;

        $order_branch_type_id = $order->order_branch_type_id;
        PaymentTransactionDetails::where("order_id",$order->order_id)->update(
              array("roop_responsestring"=>json_encode($response['responsestring']))
            );
        $status =$response['status'] ?? '';
        $ordData = Order::where('order_id', $order->order_id)->first();
        $storegroup_id = (@$ordData->storegroup_id) ? $ordData->storegroup_id : 0;
        $podToOnline = (($ordData->status_id >= 4) && ($ordData->payment_mode == 1)) ? 1 : 0;
        if($status == 'success')
        {
            DB::transaction(function () use($customer_id, $response, $status, $order, $storegroup_id, $podToOnline) {

                $ref_id = $response['reponseid']  ?? '';
               // $this->updatestockcount($customer_id);
                BlockedProducts::markedForDelivery($order->order_id,$customer_id);

                $order_status=CustomerOrderStatus::SUCCESS;

                $payment_mode = $order->payment_mode;
                if($podToOnline == 1){
                    if($payment_mode == 4)
                        $payment_mode = 5;
                    else
                        $payment_mode = 2;
                }

                $this->updateOrderStatus($order_status, $order->order_id, "Success", $ref_id, $order->order_group_id, $customer_id, $podToOnline, $payment_mode);

                $ordercontact = '';
                $user = auth_user(); //Customer::find($order->customer_id);
                if(isset($user))
                    $ordercontact = $user->cust_mobile;

                    if($ordercontact == '')
                    {
                        // $ordercontact = $order->order_contact_no;
                        $orderCustomer =  Customer::where('cust_id',$order->order_customer_id)->select('cust_mobile')->first();
                        $ordercontact = @$orderCustomer->cust_mobile;
                    }

                    $data = [
                        "mobile" => $ordercontact,//auth_user()->cust_mobile,
                        "order_id" => $order->order_order_id ?? "",
                        "ref_no" => $ref_id,
                        "amount" => $order->total ?? "",
						"storegroup_id" => $storegroup_id,
                    ];
                    StoreSms::successOnline($data);
                 //   $emailTemplate = new EmailTemplateController();
                  //  $emailTemplate($customer_id,$order->order_id);

//                }
                try{ B2CtoSalesOrder::salesOrders($order->order_id); } catch (\Exception $e){
                    Log::error('B2CtoSalesOrder failed after payment success', [
                        'order_id' => $order->order_id,
                        'error' => $e->getMessage(),
                    ]);
                }

                try {
                    $postReq = new Request();
                    $postReq->setMethod('POST');
                    $postReq->request->add([
                        'order_id'              => $order->order_id,
                        'finascopEventRefId'    => config("event_master.orderPlacing"),
                        'storegroup_id'         => (@$order->storegroup_id ? $order->storegroup_id : 0)
                    ]);
                    (new PostingRepository)->finascopPosting($postReq);
                } catch (\Exception $e) {
                    Log::error('finascopPosting failed after payment success', [
                        'order_id' => $order->order_id,
                        'error' => $e->getMessage(),
                    ]);
                }


                CustomerPickupOtp::sendOtp($order->order_id);
                if($podToOnline != 1)
                {
                    event(new OrderHistory($order->order_id,$order_status));
                    event(new DelayedOrderActions($order->order_id, 2));
                }
                //$order = $this->getOrder($order->order_id);

            });
        }
        else {
            DB::transaction(function () use( $response, $status, $order, $customer_id, $podToOnline) {
                $ref_id = $response['reponseid'] ?? '';
                if($podToOnline != 1)
                {
                    $this->updateOrderStatus(CustomerOrderStatus::PAYMENT_FAILED,$order->order_id, 'Failed',$ref_id,$order->order_group_id, $customer_id, $podToOnline);
                    BlockedProducts::unsetMarkedItems($order->order_id);
                }
                event(new OrderHistory($order->order_id, CustomerOrderStatus::PAYMENT_FAILED));
                StoreSms::failureOnline(auth_user()->cust_mobile, $order->order_order_id);
            });
        }
    }

    private function updateOrderStatus($status = 0, $order_id, $msg, $ref_id = 0,$order_group_id, $custId = 0, $podToOnline=0, $payment_mode=0)
    {
        $data = [
            'status_id' => $status,
            'order_payment_response_received' => 1,
            'order_payment_status' => $msg,
        ];

            $data['order_payment_gateway_refid'] = $ref_id;
            $data['order_payment_gateway_refid_crc32'] = crc32($ref_id);

        if($podToOnline == 0){
            if($status == CustomerOrderStatus::SUCCESS){
                $data['order_customer_cancel_till'] = PaymentRepository::getAfterBookingDelayTime(time(),1);
                $data['order_delivery_start_at'] = PaymentRepository::getAfterBookingDelayTime(time(),0);
            }elseif($status == CustomerOrderStatus::PAYMENT_INITIATED){
                $data['order_payment_initiate_time'] = date('Y-m-d H:i:s');
            }
        }
        else
        {
            unset($data['status_id']);
            $data['order_payment_initiate_time'] = date('Y-m-d H:i:s');
        }
//        if($status == CustomerOrderStatus::CPR_ON_HOLD){
//            $data['order_cutoff_time'] = PaymentRepository::getAfterBookingDelayTime(time(),2);
//        }

        $ordr = Order::where('order_group_id', $order_group_id)
                    //->where('order_customer_id', auth_user()->cust_id)
                    ->where('status_id', CustomerOrderStatus::PAYMENT_INITIATED);
                    //->update($data);
        if($podToOnline == 1)
            $ordr =  Order::where('order_id', $order_id);
        else if($custId == 0)
            $ordr = $ordr->where('order_customer_id', auth_user()->cust_id);

        if($podToOnline == 1 && $payment_mode > 0){
            $data['payment_mode'] = $payment_mode;
        }

        return $ordr->update($data);

       //return Order::where('order_group_id', $order_group_id)
       //             ->where('order_customer_id', auth_user()->cust_id)
       //             ->where('status_id', CustomerOrderStatus::PAYMENT_INITIATED)
       //             ->update($data);
    }

    public function getpaymentstatus($request){
        $order = $this->getOrder($request['order_id']);
        if (!$order) {
            return ["status" => 'Failed', "payments" => [["status" => 'Failed']]];
        }
        if($order->order_payment_response_received !=0)
        {
            return ["status"=>($order['status_id']>= 4 && $order['status_id'] != 21 ?'Completed':'Failed'),"payments"=>[["status"=>($order['status_id']>= 4 && $order['status_id'] != 21 ?'Completed':'Failed')]]];
        }
        else if($order->payment_mode == 3){
            return ["status"=>'Completed',"payments"=>[["status"=>'Completed']]];
        }
        else{
            return ["status"=>'Pending',"payments"=>[["status"=>'Pending']]];
        }
    }
    private function getOrder($orderid)
    {
        return Order::where('order_id', $orderid)
            ->where('order_customer_id', auth_user()->cust_id)
            ->latest()->first();
    }
    private function getmargindistrinution($order_id,$customer_id) {
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

        $data['ho'] = new \stdClass();
        $data['company'] = new \stdClass();
        $data['order'] = new \stdClass();
        $data['cs'] = new \stdClass();
        $data['distributor'] = new \stdClass();
        $data['retailor'] = new \stdClass();
        $data['incentive'] = new \stdClass();
        $data['deliverycharge'] = new \stdClass();

        //$data['ho']->amt = $amount->total;
        $amount = DB::table('retaline_customer_order_items')
        ->selectraw(' SUM((item_retail_price-item_sales_price)*item_order_qty) as total')
        ->where('customer_order_id', $order->order_id)
        ->first() ;
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
        // return array('order' => array('order_id' => $order->order_id, 'order_order_id' => $order->order_id, 'total' => $amount->total), 'company' => array('amt' => 0, 'br_ReferenceID' => 'sdfsd'));
    }
    private function updatePlacedOrderStatus($order_group_id, $order_id) {
        $orderDet = Order::where('order_group_id', $order_group_id)->where('order_id', $order_id)
            ->select('order_id', 'order_branch_id', 'order_branch_type_id', 'status_id', 'order_cutoff_time', 'order_slot_id', 'order_slot_date')
            ->first();

    $br_schedulePackiing = Branch::where('br_ID', $orderDet->order_branch_id)
            ->first();
    if($br_schedulePackiing->br_type == 1){
        if ($orderDet->order_slot_id > 0) {
            // TODO: Replace string-concatenated SQL with parameterized bindings.
            $slot = DB::select('SELECT rbds_time_from, rbds_time_to FROM retaline_branch_delivery_slot WHERE rbds_id= ' . $orderDet->order_slot_id);
            $default_br_schedulePackiing = DB::select("SELECT cfg_Value, cfg_Type FROM sys_configuration WHERE cfg_Name= 'DEFAULT_SCHEDULE_PACKING' ");
            $date = date('Y-m-d', strtotime($orderDet->order_slot_date));
            if($br_schedulePackiing->br_schedulePackiing > 0){
                $time = date('H:i:s', strtotime('-' . $br_schedulePackiing->br_schedulePackiing . ' hours', strtotime($slot[0]->rbds_time_from)));
            }else{
                $time = date('H:i:s', strtotime('-' . $default_br_schedulePackiing[0]->cfg_Value . ' hours', strtotime($slot[0]->rbds_time_from)));
            }

            $order_delivered_date = $date . ' ' . $time;
            $data['order_delivery_start_at'] = $order_delivered_date;
        }else{
            $data['order_delivery_start_at'] = PaymentRepository::getAfterBookingDelayTime(time(),0);
        }
            $data = [
                'status_id' => CustomerOrderStatus::DARK_ON_HOLD
            ];
            $ordr = Order::where('order_id', $orderDet->order_id);
            $ordr->update($data);
            event(new OrderHistory($orderDet->order_id, CustomerOrderStatus::DARK_ON_HOLD));

    }else if ($orderDet->order_branch_type_id == 2) {
        $orderItems = OrderItem::where('customer_order_id', $orderDet->order_id)
                ->select('item_product_id', 'item_order_qty', 'item_retail_price', 'item_sales_price', 'item_order_qty', 'item_cgst', 'item_sgst', 'item_amount', 'item_price', 'item_kfc')
                ->get();

        foreach ($orderItems as $orderIte) {
            $itemcprdets = ContractPOProducts::where('fcpod_itemid', $orderIte->item_product_id)
                    ->select('fcpod_vendorid', 'fcpo_validDate')
                    ->first();
            $count = Vendor::where('stpa_id', $itemcprdets->fcpod_vendorid)
                    ->where('deliverMode_cpr', 2)
                    ->count();
            if ($count > 0) {
                $asctedbrach_cpr++;
            }
        }
        if ($asctedbrach_cpr > 0) {
            $data = [
                'status_id' => CustomerOrderStatus::CPR_ON_HOLD,
                'order_cutoff_time' => PaymentRepository::getAfterBookingDelayTime(time(), 2)
            ];
            $ordr = Order::where('order_id', $orderDet->order_id);
            $ordr->update($data);
            event(new OrderHistory($orderDet->order_id, CustomerOrderStatus::CPR_ON_HOLD));
        }
    } else {
        if ($orderDet->order_slot_id > 0) {
            // TODO: Replace string-concatenated SQL with parameterized bindings.
            $slot = DB::select('SELECT rbds_time_from, rbds_time_to FROM retaline_branch_delivery_slot WHERE rbds_id= ' . $orderDet->order_slot_id);
            $default_br_schedulePackiing = DB::select("SELECT cfg_Value, cfg_Type FROM sys_configuration WHERE cfg_Name= 'DEFAULT_SCHEDULE_PACKING' ");
            $date = date('Y-m-d', strtotime($orderDet->order_slot_date));
            if($br_schedulePackiing->br_schedulePackiing > 0){
                $time = date('H:i:s', strtotime('-' . $br_schedulePackiing->br_schedulePackiing . ' hours', strtotime($slot[0]->rbds_time_from)));
            }else{
                $time = date('H:i:s', strtotime('-' . $default_br_schedulePackiing[0]->cfg_Value . ' hours', strtotime($slot[0]->rbds_time_from)));
            }

            $order_delivered_date = $date . ' ' . $time;
            $data = [
                'status_id' => CustomerOrderStatus::PACK_ON_HOLD,
                'order_delivery_start_at' => $order_delivered_date
            ];
            //order_delivered_date
            $ordr = Order::where('order_id', $orderDet->order_id);
            $ordr->update($data);
            event(new OrderHistory($orderDet->order_id, CustomerOrderStatus::PACK_ON_HOLD));
        }
    }

    return true;
}

    private function checkPaymentStatus($paymentgateway, $response)
    {
        $paymentgatewayclass = config("paymentgateway.". $paymentgateway . ".class");
        $paymentGatewayobj = new $paymentgatewayclass();
        $response = $paymentGatewayobj->checkPaymentStatus($paymentgateway, $response);
    }
    private function updateCustomerOrder($order_id, $data)
    {
        $order = Order::where('order_id', $order_id)->first();
        $order->update($data);
        /* $autoPosting = FinanceAutopostingValues::where('order_id', $order_id)->update([
            'GSTInputonMDRTotal'    => $data['order_payment_gateway_fees']
        ]); */
    }
}
