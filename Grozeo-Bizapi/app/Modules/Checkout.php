<?php

namespace App\Modules;
use App\Http\Repositories\Finascop\OrderFinascop;
use App\Http\Repositories\Cart\CartRepository;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\CompanyStripe;   

use App\Models\Cart;
use App\Models\Order;
use App\Models\FinanceAutopostingValues;
use App\Http\Repositories\PostingRepository;
use App\Models\Branch;
use App\Modules\AllStock;
use App\Modules\BothStock;
use App\Modules\EmptyStock;
use App\Events\{
    OrderHistory,
    DelayedOrderActions
};
use App\Models\BlockedItems;
use App\Modules\OrderCollect;
//use App\Modules\Payment\InterfacePayment;
use App\Models\StockItemMaster;
use App\Models\StockUniqueItem;
use App\Exceptions\ErrException;
use App\Exceptions\MsgException;
use App\Modules\BlockedProducts;
use App\Models\UploadPrescription;
use App\Modules\CustomerPickupOtp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Http\Repositories\Item\Stock;
use App\Models\MarginDistributionb2c;
use BackOffice\Models\BranchInventory;
use App\Http\Services\B2CToTransferOrder;
use App\Modules\Payment\InterfacePayment;
use BackOffice\Status\CustomerOrderStatus;
use App\Http\Repositories\Payment\AfterPayment;
use App\PaymentGateways\InterfacePaymentGateway;
use App\Http\Repositories\Finascop\StoreFinascop;
use App\Modules\CreateOrderId;
use App\Models\OrderItem;
use App\Models\ContractPOProducts;
use App\Models\Vendor;
use App\Http\Repositories\Payment\PaymentRepository;
use App\Http\Repositories\OrderHistoryRepository;
use App\Models\WalletTransaction;
use App\Http\Services\B2CtoSalesOrder;
use App\Sms\SmsSender;
use App\Http\Repositories\PaymentGatewayCredentials;

use DateTime;
use DatePeriod;
use DateInterval;

class Checkout 
{

    const I_CAN_DELIVERY = 1;

    const I_CAN_COLLECT = 2;

    protected const CASH_ON_DELIVERY = 1;

    protected const ONLINE_PAYMENT = 2;

    protected const SALES = 2;

    const COD_WITH_WALLET = 4;

    const ONLINE_WITH_WALLET = 5;

    private $payment;

    private $emptyStock;

    private $stockItem;

    private $uniqueItem;

    private $allStock;

    private $bothStock;

    private $stock;

    private $paymentGateway;
	
	private $smssender;


   // public function __construct(InterfacePayment $paymenty)
    public function __construct(InterfacePaymentGateway $paymentGateway,SmsSender $smssender)
    {
       // $this->payment = $payment;
        $this->emptyStock = new EmptyStock;
        $this->stockItem = new StockItemMaster;
        $this->uniqueItem = new StockUniqueItem;
        $this->allStock = new AllStock;
        $this->bothStock = new BothStock;
        $this->stock = new Stock;
        $this->paymentGateway = $paymentGateway;
	$this->smssender = $smssender;
		
        
    }

    public function create(array $request)
    {

        return $this->checkout($request);
    }

    private function checkout($request)
    {
		
        $payment = new \stdClass;
        $orders = $this->createOrder($request);   

        $totalOrders = count($orders);
        $orderIdCount = count(array_column($orders, 'order_id'));
        //if (array_search(0, array_column($orders, 'order_id')) == FALSE)
        if($totalOrders != $orderIdCount)
        {
            $hassOrder =  'false';
        }else{
            $hassOrder =  'true';
        }
	
        if (array_key_exists('splitorder', $request)) {
            if($request['splitorder'] == 1){
				if($hassOrder == 'true'){					
                $orderDetails = $this->getCurrntOrderList($orders);  
				}
                //$orders['orderDetails'] = json_encode($orderDetails);
                if(array_key_exists('getwalletbalance', $request) && $request['getwalletbalance'] == 1){
                    	$cust_wallet =  Customer::where('cust_id',auth_user()->cust_id)
                            ->select('cust_walletbalance')
                            ->first();    

	                    return [
                                "stock_available" => True,
                                "sufficient_available" => True,
                                "message" => "Stock is Available",
                                "orders" => $orders,
                                "style" => [],//$style,
                                "item" => [],
                                "wallet_balance" => $cust_wallet->cust_walletbalance,
                            ];
                }
                return $orders;
            }
        }
        


        //$order_id = $order->order_id ?? 0;
        //$order_order_id = $order->order_order_id ?? 0;
        //if(is_array($order) && $order['order'] == 0)
        //{
        //    return $order;
        //}
        
        if ($request['payment_mode'] === 1) {
            $codorders = array();
            foreach ($orders as $order) {
                    array_push($codorders, $this->cashOnDelivery($order));
            }
            return $codorders[0];
        } elseif ($request['payment_mode'] === 2) {
                    return $this->onlinePayment($orders);
        }
       return $orders[0];
    }

    // The default checkout function was updated to skip the confirm order part on payment (COD or online).
    // This function will becalled after split order and confirm by customer with selection on COD / Pay now.
    // All orders associated to the order group id will be selected from DB and will be processed for payment status.
    public function confirmorder($request)
    {
        $walletAmount = 0;
        $flag = 0; $order_total= 0; $onlineAdditionalPayment = 0;
        // Get all orders associated to the order group id.
        $orderGroupId = $request['order_group_id'] ?? 0;
        $orderId = $request['order_id'] ?? 0;
        $podToOnline = 0;//($orderGroupId <= 0 && $orderId > 0 ? 1 : 0);

        try{
            if(isset($request->podToOnline))
                $podToOnline = $request->podToOnline;
            else
                $podToOnline = (( $orderGroupId == '0' || $orderGroupId <= 0) && $orderId > 0 ? 1 : 0);

        }
        catch (\Exception $e){
        

            $podToOnline = 0;
        }

        $orders = Order::where('order_customer_id', auth_user()->cust_id)
                ->where(function($query) use ($podToOnline, $orderGroupId, $orderId){
                    $query->where([
                        [DB::raw($podToOnline), '=', DB::raw(0)],
                        ['order_group_id', $orderGroupId]
                    ])->orWhere([
                        [DB::raw($podToOnline), '=', DB::raw(1)],
                        ['order_id', '=', DB::raw($orderId)]
                    ]);
                })->get(); //where('order_group_id', $request['order_group_id'])->get();
        $statusIDs = array_column($orders->toArray(), "status_id");
        $ordStat = array_filter($statusIDs, function($st){
            if($st >= 4)
            {
                return 1;
            }
        });
        if(!empty($ordStat))
        {
            return null;
        }
        if($podToOnline == 1 && ($orders[0]->status_id > 7 || ($orders[0]->payment_mode == 2 && $orders[0]->order_payment_status == 'Success')))
            return null;

        $ordersToProcess = array(); $singleorder = null;
        if (isset($request['use_wallet']) && $request['use_wallet'] == 1) {
            $codorders = array();
            foreach ($orders as $order) {
                if($order->status_id < 5)
                {
                    $cust_wallet =  Customer::where('cust_id',auth_user()->cust_id)->select('cust_walletbalance')->first();     
                    $walletAmount = $cust_wallet->cust_walletbalance;
                    if($walletAmount <= 0)
                    {
                        array_push($ordersToProcess, $order);
                        continue;
                    }

                    $amt = $order->total ?? 0;

                    if ($amt <= $walletAmount) {
                        $oldWallet = $walletAmount;
                        $walletAmount =$amt;
                        $this->storeWalletTransaction($order->order_id, $order->order_order_id, $order->order_branch_id,  $amt);
                    // return $this->walletPayment($request, $order, $walletAmount);
                        array_push($codorders, $this->cashOnDelivery($order, $request, $walletAmount, $oldWallet, true));
                    }
                    else{
                        $this->storeWalletTransaction($order->order_id, $order->order_order_id, $order->order_branch_id,  $walletAmount);
                        if ($request['payment_mode'] === 1){
                            $request['cod_with_wallet'] = static::COD_WITH_WALLET;
                            array_push($codorders, $this->cashOnDelivery($order, $request, $walletAmount, 0, false));
                        }
                        else{
                            $request['online_with_wallet'] = static::ONLINE_WITH_WALLET;
                            $onlineorders = array();
                            array_push($onlineorders, $order);
                            $onlineAdditionalPayment = $onlineAdditionalPayment + ($amt - $walletAmount);
                            $paidorder = $this->onlinePayment($onlineorders, $request, $walletAmount);
                            array_push($codorders, $paidorder);
                        }
                    }
                    if(!empty($walletAmount) && $amt > $walletAmount)
                    {
                        $flag = 1;
                    }
                }

            }
            if (is_array($codorders) && count($codorders) > 0)
                $singleorder = $codorders[count($codorders)-1];
        }
        else
        {
            $ordersToProcess = $orders;
        }

        if(count($ordersToProcess) > 0){
            if ($request['payment_mode'] === 1) {
                // COD
                $codorders = array();
                foreach ($ordersToProcess as $order) {
                        array_push($codorders, $this->cashOnDelivery($order, $request));
                }
                //return $codorders[0];
                if (is_array($codorders) && count($codorders) > 0)
                    $singleorder = $codorders[0];
            } else{
                // Pay now. Generate payment page for the sum order value of all orders.
                $singleorder =  $this->onlinePayment($ordersToProcess, $request, 0, $onlineAdditionalPayment, $podToOnline);
            }
            $autoPosting = FinanceAutopostingValues::where('order_id', $orders[0]->order_id)->update([
                'order_payment_mode'    => $request['payment_mode']
            ]);
        }
        return $singleorder;
    }

    public function storeWalletTransaction($order_id, $order_order_id, $branch_id, $usedWalletAmount)
    {
        return DB::transaction(function () use ($order_id, $order_order_id, $branch_id, $usedWalletAmount) {
            $cust_wallet = Customer::where('cust_id', auth_user()->cust_id)
                ->lockForUpdate()
                ->select('cust_walletbalance')
                ->first();

            $currentWalletBalance = $cust_wallet->cust_walletbalance;

            if ($currentWalletBalance < $usedWalletAmount) {
                throw new MsgException('Insufficient wallet balance.');
            }

            $updated = Customer::where('cust_id', auth_user()->cust_id)
                ->where('cust_walletbalance', '>=', $usedWalletAmount)
                ->decrement('cust_walletbalance', $usedWalletAmount);

            if (!$updated) {
                throw new MsgException('Insufficient wallet balance.');
            }

            $newBalance = $currentWalletBalance - $usedWalletAmount;
            auth_user()->cust_walletbalance = ($newBalance >= 0) ? $newBalance : 0;

            $openBalQuery = '(SELECT brcw_closingBalance FROM retaline_customer_wallet_transaction tx1 WHERE tx1.cust_id = '.auth_user()->cust_id.' AND tx1.brcw_id = (SELECT MAX(tx2.brcw_id) FROM retaline_customer_wallet_transaction tx2 WHERE tx2.cust_id = '.auth_user()->cust_id.'))';

            $openBalQueryData = DB::select($openBalQuery);

            $checkWalletTransaction = WalletTransaction::where(
            [
                'cust_id' => auth_user()->cust_id,
                'refentry_id' => $order_id,
                'brcw_SourceType' => static::SALES
            ])->first();
            if($checkWalletTransaction == null)
            {
                $branch = DB::table('finascop_branch')->select('br_Name', 'br_storeGroup')->where('br_ID', $branch_id)->first();
                $branch_name = @$branch->br_Name;
                return WalletTransaction::create([
                    'cust_id' => auth_user()->cust_id,
                    'refentry_id' => $order_id,
                    'brcw_SourceType' => static::SALES,
                    'brcw_Amount' => -$usedWalletAmount,
                    'brcw_AddInfo' => "You Purchased the Order {$order_order_id} from {$branch_name}",
                    'stiid_barcode' => 0,
                    'brcw_OpeningBalance' => (@$openBalQueryData[0]->brcw_closingBalance) ? $openBalQueryData[0]->brcw_closingBalance : 0
                ]);
            }
            return true;
        });
    }
    
    public function podToOnlineGenerateLink($orderId)
    {
        $order = Order::where('order_id', $orderId)
            ->where('order_customer_id', auth_user()->cust_id)
            ->first();
        if($order && $order->payment_mode == 1)
        {
            return $this->onlinePayment([$order], [], @$order->order_wallet_amount, 0, 1);
        }
        return null;
    }

    private function updatestockcount($customer_id){

        $storegroupid = getHeaderStoreGroup();
           
         $cartitems =  Cart::where('cart_customer_id', $customer_id)->where('storegroup_id', $storegroupid)
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
    private function cashOnDelivery($order, $request, $walletAmount = 0, $oldWallet = 0, $isFullyWallet = false){
        $order_id = $order->order_id ?? 0;
        $order_order_id = $order->order_order_id ?? 0;
        $order_group_id = $order->order_group_id ?? 0;
        $order_branch_type_id = $order->order_branch_type_id;

        $wallet_cod = $request['cod_with_wallet'] ?? 0;
        if($wallet_cod > 0)
        {
            $request['payment_mode'] = $request['cod_with_wallet'];
        }

        if($order_id){

             DB::transaction(function () use($order_id,$order, $request, $walletAmount, $oldWallet, $isFullyWallet) {
                $order_status=CustomerOrderStatus::SUCCESS;
                    $br_details = Branch::where('br_ID', $order->order_branch_id)->first();
                //$this->updatestockcount(auth_user()->cust_id);
                BlockedProducts::markedForDelivery($order_id, auth_user()->cust_id );
                $this->updateOrderDetails($order,$order_status, $request['payment_mode'], $walletAmount, $isFullyWallet);
                
               // $this->updatePlacedOrderStatus($order->order_group_id,$order->order_id);
                
                //if(($UploadPrescription==0) && ($asctedbrach_cpr == 0) && ($br_details->br_type == 0)){                     
                    // B2CToTransferOrder::transferOrders($order_id);
                     
                     //12-SalesB2COnlineHmeDelPayOnDeli
                     //StoreFinascop::topayondeliverybooking('12-SalesB2COnlineHmeDelPayOnDeli', auth_user()->cust_id, $order_id);
                     //$datas = $this->getmargindistrinution($order_id, auth_user()->cust_id);
                     //StoreFinascop::margindistribution('12a-MarginDistribuProcessQueue', $datas['order'], $datas['ho'], $datas['company'], $datas['cs'], $datas['distributor'], $datas['retailor'], $datas['incentive'], $datas['deliverycharge']);
                //}
                try{ B2CtoSalesOrder::salesOrders($order_id); } catch (\Exception $e){}
                // try {
                  // OrderFinascop::OrderVoucher($order_id);
                // } catch (\Exception $e) {
                  
                // }
				
				  $storegroupid = getHeaderStoreGroup();
                  $postReq = new Request();
                  $postReq->setMethod('POST');
                  $postReq->request->add([
                      'order_id'            => $order_id,
                      'finascopEventRefId'  => config("event_master.orderPlacing"),
                      'storegroup_id'       => (@$storegroupid ? $storegroupid : 0)
                  ]);

                  (new PostingRepository)->finascopPosting($postReq);
                $customer =  Customer::where('cust_id',auth_user()->cust_id)
        ->select('cust_mobile')
        ->first();
                // $templateData['order_id'] = $order_id;
                // $templateData['order_order_id'] = $order->order_order_id;
                $templateData['orderAmt'] = $order->total;
                if(!$isFullyWallet) // BLOCK SMS IF WALLET TRANSACTION
                {
                    $this->smssender->fetchContentSendSms($templateData, $customer->cust_mobile, 9);
                }
                else
                {
                    $templateData = [
                        'order_id'  => $order->order_order_id,
                        'amount'    => $walletAmount,
                        // 'balance'   => ($oldWallet - $walletAmount)
                    ];
                    $this->smssender->fetchContentSendSms($templateData, $customer->cust_mobile, 22);
                }
                CustomerPickupOtp::sendOtp($order_id);
                event(new OrderHistory($order_id, $order_status));
                event(new DelayedOrderActions($order_id, 2));

            });
        }
        return $order_id ? [
            "order_status" => true,
            "payment_mode" =>  "Cash on Delivery.",
            "message" => "Order placed Successfully..!",
            "payment_details" => new \stdClass,
            "order_order_id"=> $order_order_id
        ] : [
            "order_status" => false,
            "payment_mode" =>  "Cash on Delivery.",
            "message" => "Oops Something went Wrong..!",
            "payment_details" => new \stdClass,
        ];
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
    private function updateOrderPaymentGateway($orderid,$paymengatewayused,$refid,$order_group_id)
    {
        $savedets['order_payment_gateway'] = $paymengatewayused;
        $savedets['order_payment_gateway_req_refid'] = $refid;
        $savedets['order_payment_gateway_req_refid_crc32'] = crc32($refid);
        $savedets['order_payment_initiate_time'] = date('Y-m-d H:i:s');
        $savedets['order_customer_cancel_till'] = $this->getAfterBookingDelayTime(time(),1);
        //Order::where('order_group_id', $order_group_id)->update($savedets);
        Order::where('order_id', $orderid)->update($savedets);
    }

    private function onlinePayment($orders, $request, $walletAmount = 0, $additionalamount = 0, $podToOnline=0){
        $order = $orders[0];
        $order_id = $order->order_id ?? 0;
        $order_order_id = $order->order_order_id ?? 0;
        $order_group_id = $order->order_group_id ?? 0;
        $order_total= ($additionalamount ?? 0);

        foreach ($orders as $myorder) {
            $order_total= $order_total + $myorder->total;
        }

        if($order_total <= 0)
            return null;

        $order_total = $order_total - $walletAmount;

        if($order_total < 0)
            $order_total = 0;

		$order->total=$order_total;

        if($order_id)
        {
            $payCredentials = app(PaymentGatewayCredentials::class)->getCredentials();



            $defPayment = (@$payCredentials['provider'] != "") ? $payCredentials['provider'] : config('paymentgateway.default');
            $keyId = @$payCredentials['credentials']['key_id'];

            $paymentgatewayclass = config("paymentgateway.{$defPayment}.class");
            $this->paymentGateway = new $paymentgatewayclass();
            if($podToOnline == 1)
            {
                $order['order_group_id'] = $order_order_id;
            }
            $payment_details = $order ? $this->paymentGateway->processPayment($order, $podToOnline) : new \stdClass;

            if($podToOnline != 1)
                foreach ($orders as $myorder) {
	                if($myorder)
	                {
                        $this->updateOrderPaymentGateway($myorder->order_id,$defPayment,$payment_details['id'],$myorder->order_group_id);
                        if(count($orders) == 1 && $walletAmount > 0){
                            $orderpaymentmode = static::ONLINE_WITH_WALLET;
                            $this->updateOrderDetails($myorder, CustomerOrderStatus::PAYMENT_INITIATED, $orderpaymentmode, $walletAmount);
                        }
                        /* else
                        {
                            $postReq = new Request();
                            $postReq->setMethod('POST');
                            $postReq->request->add([
                                'order_id' => $order_id,
                                'finascopEventRefId'     => config("event_master.orderPlacing"),
                                'storegroup_id' => (@$storegroupid ? $storegroupid : 0)
                            ]);
                            (new PostingRepository)->finascopPosting($postReq);
                        } */
	                    event(new OrderHistory($myorder->order_id, CustomerOrderStatus::PAYMENT_INITIATED));
	                }
		        }
                
          /*  $payment = $this->payment->createPayment([
                "total" => $order->total ?? 0,
                "order_id" => $order_id,
                "order_order_id" => $order_order_id,
            ]);*/
        }

        return [
            "payment_gateway"   => @$defPayment,
            "order_id"          => @$order_id,
            "order_order_id"    => @$order_order_id,
            "order_group_id"    => @$order_group_id,
           "details"            => @$order ? @$payment_details : new \stdClass,
           "key_id"             => @$keyId,
           "pg_mode"            => @$pgMode
        ];

    }

/*
    private function createOrder($request)
    {

        $cart = $this->getCart($request);
        count($cart) == 0 ? $this->msg("Empty cart createOrder") : true;
        if($request['order_method'] === static::I_CAN_DELIVERY)
        {
            return $this->deliveryOrder($request, $cart);
        }
        else if($request['order_method'] === static::I_CAN_COLLECT) {
            return $this->collectOrder($request, $cart->toArray());
        }
        else {
            throw new ErrException("Invalid order method. !");
        }
    }

*/
    private function createOrder($request)
    {
        $storegroupid = getHeaderStoreGroup();

	    $cstid=auth()->user()->cust_id;
        // TODO: Replace string-concatenated SQL with parameterized bindings.
        $cart_branches = DB::select('SELECT cart_branch_id, branch_type_id FROM retaline_cart WHERE cart_customer_id= ' . auth()->user()->cust_id . ' and storegroup_id = '. $storegroupid .' GROUP BY cart_branch_id, branch_type_id');
        //$cart = $this->getCart($request);
        $orders = array();

        // move cpo cart to the collecting branch.
		$branch_cart = [];
        foreach ($cart_branches as $br){
            if($br->branch_type_id != 2){
                $bcart = $this->getBranchCart($request, $br->cart_branch_id, $br->branch_type_id);
                
                // Single pack - each item will be separate order
                $bcartItemGroup = $bcart->where('packingMode', 1)->groupBy('cart_product_id');

                // Group pack - items in the particular sub category will be separate order
                $bcartCatGroup = $bcart->where('packingMode', 2)->groupBy('product_category');

                // Other items into single order
                $bcartDefGroup = $bcart->where('packingMode', '<>', 2)->where('packingMode', '<>', 1)->groupBy('cart_branch_id');

                foreach ($bcartCatGroup as $productId => $prodGroup)
                {
                    array_push($branch_cart, [
                        "cart_branch_id"    => $br->cart_branch_id,
                        "branch_type_id"    => $br->branch_type_id,
                        "cart"              => $prodGroup
                    ]);
                }

                foreach ($bcartItemGroup as $productId => $prodGroup)
                {
                    array_push($branch_cart, [
                        "cart_branch_id"    => $br->cart_branch_id,
                        "branch_type_id"    => $br->branch_type_id,
                        "cart"              => $prodGroup
                    ]);
                }

                foreach ($bcartDefGroup as $productId => $prodGroup)
                {
                    array_push($branch_cart, [
                        "cart_branch_id"    => $br->cart_branch_id,
                        "branch_type_id"    => $br->branch_type_id,
                        "cart"              => $prodGroup
                    ]);
                }

                // array_push($branch_cart,array("cart_branch_id" => $br->cart_branch_id,"branch_type_id" => $br->branch_type_id, "cart" => $bcart->toArray()));
            }
        }
        foreach ($cart_branches as $br){
            if($br->branch_type_id == 2){
                $bcart = $this->getBranchCart($request, $br->cart_branch_id, $br->branch_type_id);
                $cprVendor = $br->cart_branch_id;
                $cprVendorDetails = Vendor::where('stpa_id',$cprVendor)
                            ->where('asctedbrach_cpr','>',0)
                            ->where('deliverMode_cpr',2)     
                            ->first();
                $asctedbrach_cpr = $cprVendorDetails->asctedbrach_cpr ?? 0;
                if($asctedbrach_cpr > 0)
                {
                    $isNew=1;
                    foreach($branch_cart as $bc => $field){
                        if($field["cart_branch_id"] == $cprVendorDetails->asctedbrach_cpr)
                        {
			                foreach($bcart->toArray() as $item)
				                $branch_cart[$bc]['cart'][]= $item;
                                            $isNew=0;
                        }
                    }
                    if($isNew==1)
                        array_push($branch_cart,array("cart_branch_id" => $cprVendorDetails->asctedbrach_cpr,"branch_type_id" => 3, "cart" => $bcart));    
                }else{
                    array_push($branch_cart,array("cart_branch_id" => $br->cart_branch_id,"branch_type_id" => $br->branch_type_id, "cart" => $bcart));
                }
            }
        }

        // end of move cpo cart
        $request['order_group_id']=CreateOrderId::generateGrId();
        foreach($branch_cart as $br){
            $brid=$br["cart_branch_id"];
            $brtypeid=$br["branch_type_id"];

            $request['cart_branch_id'] = $brid; //$br['cart_branch_id'];
            $request['branch_type_id'] = $brtypeid;
            $request['nearest_retailer_branch']=$brid;

            $cart = $br["cart"]; //$this->getBranchCart($request, $brid, $brtypeid);
            
            count($cart) == 0 ? $this->msg("Empty cart createOrder") : true;


            if($request['order_method'] === static::I_CAN_DELIVERY)
            {
                //return $this->deliveryOrder($request, $cart);
		        $ordr = $this->deliveryOrder($request, $cart);
                if($brtypeid != 2){
                    $slotQuery = "CALL getAvailableSlots(".$brid.")";
                    $slotsDetails = DB::select($slotQuery);

                    $slots = $this->getFilteredSlots($slotsDetails);
		            $ordr["availableslots"] = $slots;

                    if(isset($slots) && count($slots) > 0){
                        $isScheduledDelivery = DB::select("select br_scheduledDelivery from finascop_branch where br_ID = " . $brid);
                        if(isset($isScheduledDelivery) && count($isScheduledDelivery) > 0 && $isScheduledDelivery[0]->br_scheduledDelivery == 1)
                            $ordr["isScheduledDelivery"] = 1;
                    }
                }
                array_push($orders, $ordr);
            }
            else if($request['order_method'] === static::I_CAN_COLLECT) {
                //return $this->collectOrder($request, $cart->toArray());
                array_push($orders, $this->collectOrder($request, $cart->toArray()));
            }
            else {
                throw new ErrException("Invalid order method. !");
            }

        }

        return $orders;
    }

    public function deliveryOrder($request, $cart)
    {
        $data['branch_id'] = $request['nearest_retailer_branch'];
        $data['cart_branch_id'] = $request['cart_branch_id'];
        $data['branch_type_id'] = $request['branch_type_id'];

        $request['selection']=1;
//        if($request['selection'] != 3)
//        {
//            $cart_items = $this->splitCart($cart->toArray(), $data);
//        }

        $hasRestService = $cart->where('hasRestaurantService', 1)->count();
        $cart_ids = array_column($cart->toArray(), 'id');

        $cart = app(CartRepository::class)->getcartobj(1, $cart_ids, 1);
        $cart_items['available_stock'] = $cart->toArray();
        
        switch ($request['selection'])
        {
            case 1 : return $this->availableStock($request, $cart_items['available_stock'], $hasRestService);
            break;
            case 2 : return $this->emptyStock->emptyStockOrder($request, $cart_items['empty_stock']);
            break;
            case 3 : return $this->allStock->allStockOrder($request, $cart->toArray());
            break;
            case 4 : return $this->bothStock->bothStockOrder($request, $cart_items);
            break;
            default : throw new ErrException("Invalid Selection.");
        }
    }

    public function collectOrder($request, array $cart)
    {
        $stock = $this->checkStock($cart, $request);
        if(count($stock['empty_stock']) > 0)
        {
           return [ "type" => 1, 
                    "data" => $stock['empty_stock'], 
                    "order" => 0, 
                    "msg" => "Empty Stock"];
        } 
        if(count($stock['over_stock']) > 0)
        {
            return [ "type" => 2, 
                    "data" => $stock['over_stock'], 
                    "order" => 0, 
                    "msg" => "Insufficient stock based on your Order"];
        }
        return OrderCollect::createOrder($cart, $request); 
    }

    private function getCart(array $request)
    {
        $storegroupid = getHeaderStoreGroup();
        return Cart::where('cart_customer_id', auth()->user()->cust_id)->where('storegroup_id', $storegroupid)
                     ->where('order_method', $request['order_method'])
                     ->select(
                         'cart_customer_id', 
                         'cart_group_id', 
                         'cart_product_id', 
                         'cart_branch_id', 
                         'cart_order_qty', 
                         'order_method'
                         )
                     ->get();
    }

    private function getBranchCart(array $request, $brid, $brtypeid)
    {
        $storegroupid = getHeaderStoreGroup();
        $subquery = '(SELECT i.stit_id, i.product_category, c.packingMode, c.hasRestaurantService, isPerishable FROM finascop_stock_itemmaster i INNER JOIN mypha_productsubcategory c ON i.product_category = c.sub_category_id) i';
        return Cart::join(DB::raw($subquery), function($join)
                    {
                        $join->on('retaline_cart.cart_product_id', '=', 'i.stit_id');
                    })->where('cart_customer_id', auth()->user()->cust_id)->where('storegroup_id', $storegroupid)
                     ->where('order_method', $request['order_method'])
                     ->where('cart_branch_id', $brid)
                     ->where('branch_type_id', $brtypeid)
                     ->select(
                         'id',
                         'cart_customer_id', 
                         'cart_group_id', 
                         'cart_product_id', 
                         'cart_branch_id', 
                         'cart_order_qty', 
                         'order_method',
			             'branch_type_id', 'packingMode', 'hasRestaurantService', 'isPerishable', 'product_category' 
                         )
                     ->get();
    }

    public function msg($msg)
    {
        throw new MsgException($msg);
    }

    private function checkStock(array $cart, $request)
    {
        $empty_stock = [];
        $over_stock = [];
        $products = array_column($cart, "cart_product_id");
        $groups = array_column($cart, "cart_group_id");
        $stock = $this->stock->getStock($products, $request['branch_id']);
        $qty = $this->getQty($products);
        $item_name = $this->getItemName($groups);
        foreach($cart as $key => $value)
        {
            $stock_val = array_key_exists($value['cart_product_id'], $stock) ? 
                                    $stock[$value['cart_product_id']] : 0;
            $data = [
                    "item_id" => $value['cart_product_id'],
                    "name" => array_key_exists($value['cart_group_id'], $item_name) ? $item_name[$value['cart_group_id']] : "",
                    "quantity" => array_key_exists($value['cart_product_id'], $qty) ? $qty[$value['cart_product_id']] : "",
                    "stock_available" => $stock_val,
                    ];
            if($stock_val == 0)
            {
                $empty_stock[] = $data;
            }
            else if($value['cart_order_qty'] > $stock_val) {
                $over_stock[] = $data;
            }
        }
      return [
            "empty_stock" => $empty_stock,
            "over_stock" => $over_stock,
        ];
    }

    private function getQty(array $products)
    {
        $items = $this->stockItem->whereIn('stit_ID', $products)
                                ->select('stit_ID', 'stit_quantity')
                                ->get()
                                ->toArray();
        return array_column($items, 'stit_quantity', 'stit_ID');
    }

    private function getItemName(array $groups)
    {
        $groups = $this->uniqueItem->whereIn('fsi_uid', $groups)
                                ->select('fsi_uid', 'fsi_item_name')
                                ->get()
                                ->toArray();
        return array_column($groups, 'fsi_item_name', 'fsi_uid');
    }
    
    private function splitCart(array $cart, $request)
    {
       $available_stock = [];
       $empty_stock = [];
       $products = array_column($cart, "cart_product_id");

       $cartbranchid = $request['cart_branch_id'];
       $cartbranchtypeid=$request['branch_type_id'];
        /*
        if($cartbranchtypeid ==2){
            return [
                "available_stock" => $cart,
                "empty_stock" => [],
            ];                
        }
        */

       $stock = $this->stock->getStock($products, $cartbranchid, $cartbranchtypeid);//$request['branch_id']);

       foreach($cart as $key => $value)
       {
            if($cartbranchtypeid ==2){
                $available_stock[] = $value;
                continue;
            }

           $stock_val = array_key_exists($value['cart_product_id'], $stock) ? 
                                   $stock[$value['cart_product_id']] : 0;

          if ($stock_val >= $value['cart_order_qty']) {
         // if($value['cart_order_qty'] < $stock_val) {
               $available_stock[] = $value;
           }
           else{
                $empty_stock[] = $value;
           }
       }
     return [
           "available_stock" => $available_stock,
           "empty_stock" => $empty_stock,
       ];
    }

    private function availableStock($request, $available_stock, $hasRestService = 0)
    {
        //count($available_stock) == 0 ? $this->msg("Empty cart availableStock") : true;
        //$request['branch_id'] = $request['nearest_retailer_branch'];
        if(count($available_stock) == 0){
            return ['status' => 'failed', 'msg' => "Empty cart availableStock"];
        }else{
            return OrderCollect::createOrder($available_stock, $request, $hasRestService); 
        }
       
        //$request['branch_id'] = $request['nearest_retailer_branch'];
        //return $this->collectOrder($request, $available_stock);
    }
    private function updateOrderDetails($order,$status, $paymentMode, $walletAmount = 0, $isFullyWallet = false){
        if($status == CustomerOrderStatus::CPR_ON_HOLD){
            $cutofftime = PaymentRepository::getAfterBookingDelayTime(time(),2);
        }else{
            $cutofftime = '0000-00-00 00:00:00';
        }
        $payment_mode = $isFullyWallet ? 3 : $paymentMode;
        $total = ($order->total) ? $order->total : 0;
        $amountPayable = $total - $walletAmount;
        $updatedOrder = Order::where('order_id', $order->order_id)->where('status_id', '<', CustomerOrderStatus::SUCCESS)->update([
            'status_id'                     => $status,
            "payment_mode"                  => $payment_mode,
            "order_amount_payable"          => $amountPayable,
            'order_wallet_amount'           => $walletAmount,
            'order_confirm_date'            => now()->format('Y-m-d'),
            'order_confirmed_on'            =>  now()->format('Y-m-d H:i:s'),
            'order_cutoff_time'             => $cutofftime,
            'order_customer_cancel_till'    => $this->getAfterBookingDelayTime(time(),1),
        ]);
        /* $updateAutoPostingValues = [];
        $OrderGrandTotal = $order->total;
        if(($payment_mode == 1) || ($payment_mode == 4))
        {
            $updateAutoPostingValues['OrderGrandTotal'] = NULL;
            $updateAutoPostingValues['OrderGrandTotal_POD'] = $OrderGrandTotal;
            $updateAutoPostingValues['TSOPOD_PendingCollection'] = $OrderGrandTotal - $walletAmount;
            $updateAutoPostingValues['CustomerWallet_Withdrawal_POD'] = $walletAmount;
        }
        else
        {
            $updateAutoPostingValues['CustomerWallet_Withdrawal'] = $walletAmount;
        }
    
        $autoPosting = FinanceAutopostingValues::where('order_id', $order->order_id)->update($updateAutoPostingValues); */

        /* $postReq = new Request();
        $postReq->setMethod('POST');
        $postReq->request->add([
            'order_id' => $order->order_id,
            'finascopEventRefId'     => config("event_master.orderPlacing"),
            'storegroup_id' => (@$order->storegroup_id ? $order->storegroup_id : 0)
        ]);
        
        (new PostingRepository)->finascopPosting($postReq);  */
		
		//(new PostingRepository)->finascopPosting($order->order_id, '0780263b-38d7-11ee-9967-065723bafb24', (@$order->storegroup_id ? $order->storegroup_id : 0));

        return $updatedOrder;
    }

    private function getAfterBookingDelayTime($date,$type)
    {
		if($type==1){
			$addseconds  =  config('b2cbooking.customer_cancel_till_seconds') ?? 120;
		}else{
			$addseconds  =  config('b2cbooking.delivery_process_start_at_seconds') ?? 240;
        }
        return date('Y-m-d H:i:s', $date + $addseconds);
    }
    
     public function getCurrntOrderList($list)
    {
        //Can we provide Order ID, Order Status, Branch Name, Branch Location, Branch Phone, Order Date & Time, Order Amount,  Payment Mode, Bank Ref in the order details
        foreach($list as $key => $value)
        {
            /*$order_history = $value['order_history'];
            foreach($order_history as $val => $history)
            {
                $list[$key]['order_history'][$val]['status'] = $history['get_order_status']['status'];
        }*/
		//if(!(isset($value) && array_key_exists('order_id', $value)))
			//continue;
		
            $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
            $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;
			
            $itemdets = DB::table('retaline_customer_order_items')
            ->select('item_product_id')                       
            ->where('customer_order_id', $value['order_id'])            
            ->orderBy('item_sales_price', 'desc')
            ->first() ; 
            
            
            $itemcount = DB::table('retaline_customer_order_items')            
            ->where('customer_order_id', $value['order_id'])           
            ->count() ; 

            $images = DB::table('finascop_stock_item_images')
            ->select('image_url')                       
            ->where('product_id', $itemdets->item_product_id)
            ->where('image_type',1)          
            ->first() ;
            
            if($value['order_branch_type_id'] == 2){
                $branchDeta = DB::table('finascop_stock_party')
            ->select('stpa_id as br_id','stpa_Fname as br_name','stpa_Address as br_address','stpa_MobileNo as br_phone')                       
            ->where('stpa_id', $value['order_branch_id'])
            ->first() ;
            }else{
                $branchDeta = DB::table('finascop_branch')
            ->select('br_id','br_name','br_address','br_phone')                       
            ->where('br_ID', $value['order_branch_id'])
            ->first() ;
            }

            $itemname = DB::table('finascop_stock_itemmaster')
            ->select('stit_itemName')                       
            ->where('stit_ID', $itemdets->item_product_id)    
            ->first() ;  
            
            $itemdetails = DB::table('retaline_customer_order_items')
            ->select('item_product_id','item_sales_price', 'item_order_qty')                       
            ->where('customer_order_id', $value['order_id'])            
            ->orderBy('item_sales_price', 'desc')
            ->get() ; 
			
            //$list[$key]['highest_priced_image'] = $images->image_url ?$domain.'thumbnail-'.$images->image_url: '';
            $image_url = $images->image_url ?? '';  
             $list[$key]['highest_priced_image'] = $image_url?$domain.'thumbnail-'.$image_url:'';  
            $list[$key]['highest_priced_itemname'] = $itemname->stit_itemName;  
            $list[$key]['total_item_count'] = $itemcount;
            $list[$key]['itemDetails'] = $itemdetails; //json_encode($itemdetails);
            $list[$key]['branchDetails'] = $branchDeta; //json_encode($branchDeta);
		
        }
        
       return $list;
    }
    private function updatePlacedOrderStatus($order_group_id, $order_id) {

    $orderDet = Order::where('order_group_id', $order_group_id)->where('order_id', $order_id)
            ->select('order_id', 'order_branch_id', 'order_branch_type_id', 'status_id', 'order_cutoff_time', 'order_slot_id', 'order_slot_date')
            ->first();

    $br_schedulePackiing = Branch::where('br_ID', $orderDet->order_branch_id)
            ->first();

    if($br_schedulePackiing->br_type == 1){
        if ($orderDet->order_slot_id > 0) {
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
    
private function getFilteredSlots($slotsDetails)
{
    $outs = [];
    foreach($slotsDetails as $slot)
    {
        $outs[] = [
            'id'        => $slot->rbds_id,
            'branch_id' => $slot->br,
            'slot'      => date('g A', strtotime($slot->rbds_time_from)).' - '.date('g A', strtotime($slot->rbds_time_to)),
            'max'       => $slot->rbds_time_maxslot,
            'date'      => $slot->dt
        ];
    }
    if(count($outs) > 0)
    {
        $outs = array_slice($outs, 0, 6);
    }
    return $outs;
}
   
}
