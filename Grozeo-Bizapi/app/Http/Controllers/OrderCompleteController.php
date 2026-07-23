<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Exception;
use App\Models\OrderHistory;
use Illuminate\Support\Arr;
use App\Models\CustomerOrderStatus;
use App\Modules\DetermineStates;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\SuccessWithData;
use App\Modules\CustomerPickupOtp;
use Illuminate\Http\Request;
use App\Modules\PriceCalculation;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;
use App\Models\Customer;
use App\Modules\Checkout;
use BackOffice\Models\QugeoOrder;
use App\Models\DeliveryInfo;
use App\Http\Requests\Order\OrderNotesRequest;

use App\Http\Repositories\Coupon\Coupon;

class OrderCompleteController extends Controller
{
    protected $order;

    public function __construct(Order $order)
    {

        $this->order = $order;
    }
    public function setslot(Request $request)
    {
        $storegroupid = getHeaderStoreGroup();
        $validatedData = $request->validate([
            'order_id' => 'required|integer',
            'slot_id' => 'required|integer',
            'slot_date' => 'nullable',
            'use_wallet' => 'nullable',

        ]);

        $customer_id = auth()->user()->cust_id ?? 0;
        throw_if($customer_id==0, new Exception('Invalid customer'));

        $latlang = DB::select('SELECT deli_latitude, deli_longitude FROM retaline_customer_delivery_info WHERE deli_is_primary=1 AND deli_customer_id='. $customer_id. ' limit 1');
        
        $orderData = DB::select('SELECT order_group_id FROM retaline_customer_order WHERE order_id ='. $request->get('order_id'). ' limit 1');
        
        $deliveryCharg = new PriceCalculation();

        $allOrders = DB::select('SELECT * FROM retaline_customer_order WHERE order_group_id ='.$orderData[0]->order_group_id);
        $outs = [];
        if(count($allOrders) > 0)
        {
            foreach ($allOrders as $ord)
            {
                if($ord->order_id == $request->get('order_id'))
                {
                    $deliData = DeliveryInfo::where([
                        ['deli_is_primary', 1],
                        ['deli_customer_id', @$customer_id]
                    ])->first();
                    if($request->get('slot_id') > 0){

                        $slots = DB::select("SELECT rbds_id AS id, branch_id, rbds_time_maxslot, (SELECT COUNT(order_id) FROM retaline_customer_order WHERE order_slot_id = retaline_branch_delivery_slot.rbds_id AND status_id != 19 AND order_slot_date = ".$request->get('slot_date').") as booked FROM retaline_branch_delivery_slot WHERE rbds_id= ".$request->get('slot_id'));

                        if($slots)
                        {
                            if($slots[0]->rbds_time_maxslot > $slots[0]->booked)
                            {
                                // $delcharge = $deliveryCharg->calculateDeliveryCharges($ord->order_branch_id,$latlang[0]->deli_latitude, $latlang[0]->deli_longitude,3,$ord->order_branch_type_id,$ord->subtotal);

                                // delivery charge calculation based on new delivery rule 07/08/2024
                                
                                $weightSum = 0;
                                $hasRestService = 0;
                                $isScheduled = 1;
                                $delcharge = $deliveryCharg->calculateDeliveryChargeNew($ord->order_branch_id, $ord->order_branch_type_id, $weightSum, $hasRestService, @$deliData->deli_latitude, @$deliData->deli_longitude, $ord->subtotal, $isScheduled, @$deliData->state->cnt_ID, @$deliData->state->st_ID, @$deliData->deli_district);

                                //on 14march23
                                $requestArr = json_decode(json_encode($request), true);
                                $delchargeRelatedFields = $deliveryCharg->getDeliveryChargesFields($customer_id,$request->get('order_id'),round($delcharge['ratforDistance']),$requestArr);
                                
                                $total = $ord->subtotal + round($delcharge['ratforDistance']);
                                if(empty($request->get('slot_date'))){
                                $slotDate = date('Y-m-d');
                                }else{
                                $slotDate = date('Y-m-d', strtotime($request->get('slot_date')));
                                }
                                //on 14march23
                                $delchargeRelatedFields['order_slot_id'] = $request->get('slot_id');
                                $delchargeRelatedFields['order_slot_date'] = $slotDate;
                                
                                Order::where('order_id', $request->get('order_id'))
                                ->where('order_customer_id', $customer_id)
                                ->where('status_id', 0)
                                ->update($delchargeRelatedFields);
                               // ->update(['order_slot_id' => $request->get('slot_id'), 'order_slot_date' => $slotDate,'order_delivery_charge' => $delcharge['ratforDistance'],'total' => $total ]);
                            }
                            else
                            {
                                return new ErrorResponse('This slot is already full');
                            }
                        }
                    }else{
                        
                        $weightSum = 0;
                        $hasRestService = 0;
                        $isScheduled = 0;
                        $delcharge = $deliveryCharg->calculateDeliveryChargeNew($ord->order_branch_id, $ord->order_branch_type_id, $weightSum, $hasRestService, @$deliData->deli_latitude, @$deliData->deli_longitude, $ord->subtotal, $isScheduled, @$deliData->state->cnt_ID, @$deliData->state->st_ID, @$deliData->deli_district);
                        $requestArr = json_decode(json_encode($request), true);
                        $delchargeRelatedFields = $deliveryCharg->getDeliveryChargesFields($customer_id,$request->get('order_id'),round($delcharge['ratforDistance']),$requestArr);
                        
                        $total = $ord->subtotal + round($delcharge['ratforDistance']);
                        $delchargeRelatedFields['order_slot_id'] = NULL;
                        $delchargeRelatedFields['order_slot_date'] = NULL;
                        
                        Order::where('order_id', $request->get('order_id'))
                        ->where('order_customer_id', $customer_id)
                        ->where('storegroup_id', $storegroupid)
                        ->where('status_id', 0)
                        ->update($delchargeRelatedFields);
                    }                    
                }
                $order = Order::where('order_id', $request->get('order_id'))->first();
                $outs['orders'][] = [
                    'order_id'          => $ord->order_id,
                    'order_order_id'    => $ord->order_order_id,
                    'order_group_id'    => $ord->order_group_id,
                    'style'             => $this->getStyle($order)//, 'style.coupon')
                ];
            }
        }
        $useWallet = @$request->get('use_wallet') ? $request->get('use_wallet') : 0;
        $outs['summary'] = (new Coupon)->getWalletOnlyDetails([
            'order_id'      => $request->get('order_id'),
            'use_wallet'    => $useWallet
        ]);
        return new SuccessWithData($outs);
    }

    public function setslot_09_03_2023_v(Request $request)
    {
        $storegroupid = getHeaderStoreGroup();
        $validatedData = $request->validate([
            'order_id' => 'required|integer',
            'slot_id' => 'required|integer',
            'slot_date' => 'nullable',

        ]);

        $customer_id = auth()->user()->cust_id ?? 0;
        throw_if($customer_id==0, new Exception('Invalid customer'));

        $latlang = DB::select('SELECT deli_latitude, deli_longitude FROM retaline_customer_delivery_info WHERE deli_is_primary=1 AND deli_customer_id='. $customer_id. ' limit 1');
        
        $orderData = DB::select('SELECT order_id,order_branch_type_id,order_branch_id,subtotal FROM retaline_customer_order WHERE order_id ='. $request->get('order_id'). ' limit 1');
        
        $deliveryCharg = new PriceCalculation();
       

        if($request->get('slot_id') == -1){
                //homedelivery
                $delcharge = $deliveryCharg->calculateDeliveryCharges($orderData[0]->order_branch_id,$latlang[0]->deli_latitude, $latlang[0]->deli_longitude,2,$orderData[0]->order_branch_type_id,$orderData[0]->subtotal);
                $total = $orderData[0]->subtotal + $delcharge['ratforDistance'];
            Order::where('order_id', $request->get('order_id'))
                             ->where('order_customer_id', $customer_id)
                             ->where('storegroup_id', $storegroupid)
                             ->where('status_id', 0)
                             ->update(['order_slot_id' => DB::raw('null'), 'order_slot_date' => DB::raw('null') ]);

        }
        else
        {
                //slotdelivery
                $delcharge = $deliveryCharg->calculateDeliveryCharges($orderData[0]->order_branch_id,$latlang[0]->deli_latitude, $latlang[0]->deli_longitude,3,$orderData[0]->order_branch_type_id,$orderData[0]->subtotal);
                 $total = $orderData[0]->subtotal + $delcharge['ratforDistance'];
            if(empty($request->get('slot_date'))){
                $slotDate = date('Y-m-d');
            }else{
                $slotDate = date('Y-m-d', strtotime($request->get('slot_date')));
            }
            Order::where('order_id', $request->get('order_id'))
                             ->where('order_customer_id', $customer_id)
                             ->where('status_id', 0)
                             ->update(['order_slot_id' => $request->get('slot_id'), 'order_slot_date' => $slotDate,'order_delivery_charge' => $delcharge['ratforDistance'],'total' => $total ]);
        }
        $order = Order::where('order_id', $request->get('order_id'))->first();
        return new SuccessResponse([
            'style' => $this->getStyle($order),
            'status' => 'success'
        ]);
    }

    public function getdata($orderId)
    {
        $storegroupid = getHeaderStoreGroup();

        $order = $this->order->where('storegroup_id', $storegroupid)
            ->where('order_order_id', $orderId)
            ->where('order_customer_id', auth_user()->cust_id)
            ->first();

        throw_if(empty($order), new Exception('Invalid order'));
        
        $payment = $this->getPaymentMode($order->payment_mode);

        $orderMethod = $order->order_method;
        $deliveryType = '';
        if($orderMethod == 1)
        {
            $deliveryType = 'Express Delivery';
        }
        if($orderMethod == 2)
        {
            $deliveryType = "Customer Collect";
        }
        if($orderMethod == 3)
        {
            $deliveryType = "Courier Delivery";
        }

        $order->with(['orderHistory' => function ($q) {
                $q->with(['getOrderStatus' => function ($status) {
                    $status->select('status_id', 'customer_description as status')->where('status_id','!=',0);
                }])
                ->select('order_id', 'order_status', 'created_at')->where('order_status','!=',0);
            }]);
 
        $images=$order->orderItems()->with('image','item:stit_ID,stit_sku,stit_brand_name')->get();
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $branch = DB::table('finascop_branch')
        ->select('br_Name','br_City', 'br_Address','br_Phone')                       
        ->where('br_ID', $order->order_branch_id)
        ->first() ;

        $slotdate = ''; $slottime='';
        if($order->order_slot_id != null){
            $slot = DB::select('SELECT rbds_time_from, rbds_time_to, CONCAT_WS(" - ", CONCAT_WS(" ", CONCAT_WS(".", TRIM(LEADING "0" FROM NULLIF(TIME_FORMAT(rbds_time_from, "%h"), "00")), NULLIF(TIME_FORMAT(rbds_time_from, "%i"), "00")), TIME_FORMAT(rbds_time_from, "%p")), CONCAT_WS(" ", CONCAT_WS(".", TRIM(LEADING "0" FROM NULLIF(TIME_FORMAT(rbds_time_to, "%h"), "00")), NULLIF(TIME_FORMAT(rbds_time_to, "%i"), "00")), TIME_FORMAT(rbds_time_to, "%p"))) AS slot FROM retaline_branch_delivery_slot WHERE rbds_id= ' . $order->order_slot_id);
            if(isset($slot) && count($slot) > 0){
                $slottime = $slot[0]->slot; // . ' - ' .  $slot[0]->rbds_time_to;
            }
        }
        $tracking = [];
        /* $shippingData = DB::table('shipping_consignment')->where('order_id', $order->order_order_id)->first();
        if($shippingData)
        {
            $tracking = DB::table('consignment_tracking')->select('tracking_id', 'status_id', 'status_value', 'location', 'status_date')->where('tracking_id', $shippingData->tracking_id)->get();
        } */

        $orderCurrentStatus = $this->getOrderStatus($order->order_id);
        $branchID = $order->order_branch_id;
        $orderItemDetails = $order->orderItems()->with(['image'=>function($q)use($domain){
                $q->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));
            }])
            ->with(['item'=>function($q) use($branchID)
            {
                $q->select('finascop_stock_itemmaster.stit_ID', 'finascop_stock_itemmaster.stit_sku', 'finascop_stock_itemmaster.stit_brand_name', 'fsbi.hasSpotReturn as stit_custInitiate', 'fsbi.returnTime as stit_itemReturnTime')
                    ->leftJoin('finascop_stock_branch_inventory as fsbi', 'finascop_stock_itemmaster.stit_ID', '=', 'fsbi.stit_id')
                    ->where('fsbi.branch_id', $branchID);
            }])->get();
        $x = 0;
        $returnStart = 0;
        $returnAvailable = 0;
        $deliveryMessage = '';
        if($order->status_id == 16)
        {
            $qugeoData = QugeoOrder::select('quor_id', 'quor_RefNo', 'quor_Status')->where([
                ['quor_RefNo', $order->order_order_id],
                ['quor_TransferOrder_Type', 1]
            ])->first();
            if($qugeoData)
            {
                $qugeo_deliverystatus = DB::table('qugeo_deliverystatus')->select('dls_ID', 'dls_DelStatus')->where('dls_ID', $qugeoData->quor_Status)->first();
                $deliveryMessage = ($qugeo_deliverystatus) ? $qugeo_deliverystatus->dls_DelStatus : '';
            }
        }
        $totalReturnCount = 0;
        foreach($orderItemDetails as $oid)
        {
            $orderItemDetails[$x]['returnCanStart'] = 0;
            $orderItemDetails[$x]['isReturnAvailable'] = 0;
            $orderItemDetails[$x]['item']['return_details'] = '';
            if($oid['item']['stit_custInitiate'] == 1)
            {
                $orderItemDetails[$x]['item']['return_details'] = 'Return possible only while accepting delivery.';
                $returnAvailable++;
                $orderItemDetails[$x]['isReturnAvailable'] = 1;
            }
            if($oid['item']['stit_itemReturnTime'] > 0)
            {
                $totalReturnCount++;
                $returnAvailable++;
                $orderItemDetails[$x]['isReturnAvailable'] = 1;
                $orderItemDetails[$x]['item']['return_details'] = 'Return possible within '.$oid['item']['stit_itemReturnTime'].' Days.';
                if($orderCurrentStatus['status_id'] == 18)
                {
                    $now = time();
                    $your_date = strtotime($orderCurrentStatus['status_date']);
                    $datediff = $now - $your_date;

                    $dateDiff = round($datediff / (60 * 60 * 24));
                    if($dateDiff > $oid['item']['stit_itemReturnTime'])
                    {
                        $orderItemDetails[$x]['item']['return_details'] = 'Return Period is Over';
                    }
                    else
                    {
                        $returnStart++;
                        $orderItemDetails[$x]['returnCanStart'] = 1;
                    }
                }
            }
            if(($oid['item']['stit_itemReturnTime'] == 0) && ($oid['item']['stit_custInitiate'] != 1))
            {
                $orderItemDetails[$x]['item']['return_details'] = 'Not Returnable Product';
            }
            $x++;
        }
        $pendingReturns = $totalReturnCount - $order->order_itemReturnRequestCount;
        $response = [
            'order_id' => $orderId,
            'order_order_id' => $order->order_order_id,
            'order_group_id' => $order->order_group_id,
            'orderMethod'  => $orderMethod,
            'delivery_type' => $deliveryType,
            'order_date' => $order->created_at,
            'branch_name' => $branch->br_Name,
            'branch_location' => ($branch->br_Name == $branch->br_City) ? $branch->br_Address : $branch->br_City,
            'branch_phone' => $branch->br_Phone,
            'order_datetime' => $order->created_at,
            'order_delivered_date' => $order->order_delivered_date,
            'order_shipping_address' => $order->deliveryAddress()->get(),
            'order_items' => $orderItemDetails,
            'order_total' => $order->total,
            'order_subtotal' => $order->subtotal,
            'order_trackID'    => $order->order_trackID,
            'order_trackURL'    => $order->order_trackURL,
            'order_kfc' => $order->order_kfc_amount,
            'order_shipping_charge' => ($order->order_delivery_charge == 0 && $order->order_courier_charge > 0 ? $order->order_courier_charge : $order->order_delivery_charge),
            'order_total_gst' => $order->order_total_gst,
            'order_total_cgst' => round($order->order_total_cgst, 2),
            'order_total_sgst' => round($order->order_total_sgst, 2),
            'order_amount' => $order->order_total_amount,
            'order_discount' => ($order->order_discount_amount > 0) ? $order->order_discount_amount : 0,
            'payment_mode_val' => $order->payment_mode,
            'payment_mode' => $payment ?? '',
            'order_trackURL' => $order->order_trackURL ?? '',
            'order_status' => $orderCurrentStatus,
            'delivery_failed_message' => $deliveryMessage,
            'order_primary_key' => $order->order_id,
            'order_DeliveryDriver' => $order->order_DeliveryDriver ?? '',
            'order_DeliveryDriverNumber' => $order->order_DeliveryDriverNumber ?? '',
            'order_customerpickup_otp'=>$order->order_customerpickup_otp,
            'style' => $this->getStyle($order),
            'otp_message'=>app(CustomerPickupOtp::class)->getMessage($order,$order->order_customerpickup_otp),
            'order_deliveryslot_time' => $slottime,
            'order_deliveryslot_date' => $order->order_slot_date,
            'order_customer_cancel_till' => $order->order_customer_cancel_till,
            'order_method'  => '',
            'order_time_now' => now()->format('Y-m-d  H:i:s'),
            'order_can_cancel' =>  (now()->format('Y-m-d')<$order->order_customer_cancel_till) && ($order->status_id != 19) ?? false,
            'order_HasReturnRequest'    => $order->order_HasReturnRequest,
            'order_ReturnVerified'      => $order->order_ReturnVerified,
            'order_itemReturnRequestCount'      => $order->order_itemReturnRequestCount,
            'order_totalReturnCount'  => $totalReturnCount,
            'order_pendingReturns'  => ($pendingReturns < 0) ? 0 : $pendingReturns,
            'isReturnAvailable' => ($returnAvailable > 0) ? 1 : 0,
            'returnCanStart' => ($returnStart > 0) ? 1: 0,
            'order_notes'   => $order->order_notes,
            'order_history' => $order->orderHistory,
            // 'tracking_details'  => $tracking,
        ];
        if($order->order_roundoff != 0)
        {
            $response['order_roundoff'] = round($order->order_roundoff, 2);
        }
        return new SuccessWithData($response);

    }

    private function getPaymentMode($mode)
    {
        $paymentMode = "";
        switch ($mode)
        {
            case 1:
                $paymentMode = "Pay on Delivery (POD)";
                break;
            case 2:
                $paymentMode = "Online";
                break;
            case 3:
                $paymentMode = "Wallet Payment";
                break;
            case 4:
                $paymentMode = "Pay on Delivery with Wallet";
                break;
            case 5:
                $paymentMode = "Online with Wallet";
                break;
            case 6:
                $paymentMode = "Online on Delivery";
                break;
            case 7:
                $paymentMode = "Cash on delivery";
                break;
            
            default:
                $paymentMode = "";
                break;
        }
        return $paymentMode;
    }

    private function getOrderStatus($order_id)
    {
        $status = OrderHistory::where('order_id', $order_id)
                        ->orderBy('id', 'desc')
                        ->first(['order_status', 'created_at']);
        $status_id = $status->order_status ?? '';
        $returns = CustomerOrderStatus::where('status_id', $status_id)
                                    ->first(['status_id', 'customer_description as status']);
        $returns->status_date = (@$status->created_at) ? date('Y-m-d H:i:s', strtotime($status->created_at)) : '';
        return $returns;
    }

    private function getStyle($order)
    {
        $i = 1;
        $styles = config('style.order_complete');
        $address = $order->deliveryAddress()->first();
        $state = $address->order_state ?? "";
        $order = $order->toArray();
        $state = DetermineStates::find($order['order_branch_id'], $state);
        /*
        if($state)
        {
            $styles = Arr::except($styles, ["order_total_gst"]);
            $gst = $order['order_total_gst'] / 2;
            $order['order_total_sgst'] = $gst_value = round($gst, 2); 
            $order['order_total_cgst'] = $gst_value;
        }
        else {
        */
            $styles = Arr::except($styles, ["order_total_sgst", "order_total_cgst", "order_kfc_amount"]);
        // }
        foreach($styles as $key => $style)
        {
            if(array_key_exists($key, $order))
            {
                $styles[$key]['order'] = $i;
                $styles[$key]['value'] = (string) $order[$key];
                ($key === "total") ? $styles[$key]['value'] = config('app.def_currency_symbol')." ".(string) $order[$key] : "";
                $i++;
            }
        }
       return array_values($styles);
    }


    public function reloadOrder(Request $request){

        $validatedData = $request->validate([
            'order_group_id' => 'required|string',
        ]);


        $order_group_id = $request->order_group_id;

        $orders = $this->order->where('order_group_id', $order_group_id)->where('status_id', 0)->select([
            'order_id', 'order_group_id', 'order_order_id', 'order_branch_type_id', 'order_isB2b', 'order_customer_id', DB::raw('order_total_amount  + 0E0 as order_total_amount'),
             DB::raw('order_delivery_charge + 0E0 as order_delivery_charge'), DB::raw('order_total_gst + 0E0 as order_total_gst'), DB::raw('order_kfc_amount + 0E0 as order_kfc_amount'),
             DB::raw('order_discount_amount + 0E0 as order_discount_amount'), DB::raw('order_discount_add_total + 0E0 as order_discount_add_total'), DB::raw('order_mrp + 0E0 as order_mrp'), 'order_assigned_boy', 'order_polled_boy',
             DB::raw('subtotal + 0E0 as subtotal'), DB::raw('order_roundoff + 0E0 as order_roundoff'), DB::raw('total + 0E0 as total'), DB::raw('order_saved_amount + 0E0 as order_saved_amount'), 'payment_mode', 'order_payment_initiate_time', 'order_payment_gateway', 'order_parent',
             'order_payment_gateway_req_refid', 'order_payment_gateway_req_refid_crc32', 'order_payment_gateway_refid', 'order_payment_gateway_refid_crc32', 'order_payment_status', 'order_payment_response_received', 'order_payment_failed_scheduler_time', 'order_branch_id',
             'order_company_id', 'status_id', 'order_status_addinfo', 'order_confirm_date', 'order_confirmed_on', 'order_cancel_date', 'order_delivered_date', 'order_packedbags_count', 'order_trackURL', 'order_DeliveryRatingStar',
             'order_DeliveryRatingComment', 'order_DeliveryDriver', 'order_DeliveryDriverNumber', 'order_HasReturnRequest', 'order_HasReturn', 'order_ItemsReturned',
             'order_itemReturnRequestCount', 'order_ReturnVerified', 'order_amounts', 'order_amount_payable', 'order_wallet_amount', DB::raw('order_amount_returnon_cash + 0E0 as order_amount_returnon_cash'), DB::raw('order_amount_addedon_wallet + 0E0 as order_amount_addedon_wallet'), 'order_customer_cancel_till', 'order_delivery_start_at', 'order_ondel_bankref_id', DB::raw('order_ondel_entry_amount + 0E0 as order_ondel_entry_amount'), 'order_app_version', 'order_app_os',
             DB::raw('order_courier_charge + 0E0 as order_courier_charge'), 'order_method', 'order_type', 'order_approvedOn', 'order_approvalStatus', 'order_approvedBy', 'order_prescription_validated',
             DB::raw('order_total_cgst + 0E0 as order_total_cgst'), DB::raw('order_total_sgst + 0E0 as order_total_sgst'), 'order_invoiceno', 'order_invoicedate', DB::raw('order_invoiceamt + 0E0 as order_invoiceamt'),
             'order_customerpickup_otp', 'order_portal_afterpayment_redirecturl', 'order_cutoff_time', 'order_slot_id', 'order_slot_date', 'storegroup_id'])->get();

        if(!isset($orders) || count($orders) < 1)
            return "Invalid order id";

        $orders = app(Checkout::class)->getCurrntOrderList($orders);
        foreach($orders as $key => $value)
        {
            if($value['order_branch_type_id'] != 2){
                /* $slots = DB::select("SELECT rbds_id AS id, branch_id
                    , CONCAT_WS(' - ', CONCAT_WS(' ', CONCAT_WS('.', TRIM(LEADING '0' FROM NULLIF(TIME_FORMAT(rbds_time_from, '%h'), '00')), NULLIF(TIME_FORMAT(rbds_time_from, '%i'), '00')), TIME_FORMAT(rbds_time_from, '%p')) 
                    , CONCAT_WS(' ', CONCAT_WS('.', TRIM(LEADING '0' FROM NULLIF(TIME_FORMAT(rbds_time_to, '%h'), '00')), NULLIF(TIME_FORMAT(rbds_time_to, '%i'), '00')), TIME_FORMAT(rbds_time_to, '%p'))) AS slot
                    , rbds_time_maxslot AS `max`, CASE WHEN DATE_ADD(NOW(), INTERVAL 1 HOUR) < TIMESTAMP(rbds_time_from) THEN '' ELSE DATE_FORMAT(DATE_ADD(TIMESTAMP(rbds_time_from),INTERVAL 1 DAY), '%d-%m-%Y') END AS `date`
                        FROM retaline_branch_delivery_slot WHERE branch_id= " . $value['order_branch_id'] . " ORDER BY `date`, rbds_time_from"); */

                $slotQuery = "CALL getAvailableSlots(".$value['order_branch_id'].")";
                $slotsDetails = DB::select($slotQuery);

                $slots = $this->getFilteredSlots($slotsDetails);

                $value["availableslots"] = $slots;//array(array("slot"=>"12 PM - 2 PM", "id" => "1"),array("slot"=>"2 PM - 4 PM", "id" => "2"),array("slot"=>"4 PM - 6 PM", "id" => "3"),array("slot"=>"12 PM - 2 PM", "id" => "1", "day" =>"Next Day", "date" => "03-Dec-20"));
                if(isset($slots) && count($slots) > 0){
                    $isScheduledDelivery = DB::select("select br_scheduledDelivery from finascop_branch where br_ID = " . $value['order_branch_id']);
                    if(isset($isScheduledDelivery) && count($isScheduledDelivery) > 0 && $isScheduledDelivery[0]->br_scheduledDelivery == 1)
                        $value["isScheduledDelivery"] = 1;
                }
            }
        }

        $cust_wallet =  Customer::where('cust_id',auth_user()->cust_id)
            ->select('cust_walletbalance')
            ->first();    

        return  new SuccessWithData([
                "stock_available" => True,
                "sufficient_available" => True,
                "message" => "Stock is Available",
                "orders" => $orders,
                "style" => [],//$style,
                "item" => [],
                "wallet_balance" => $cust_wallet->cust_walletbalance,
            ]);

    }

    public function notes(OrderNotesRequest $request)
    {
        try
        {
            $updateOrder = Order::where([
                ['order_id', $request->order_id],
                ['order_customer_id', @auth_user()->cust_id],
                ['status_id', 0]
            ])->update([
                'order_notes'   => $request->note
            ]);
            if($updateOrder)
            {
                $order = Order::select('order_id', 'order_order_id', 'order_notes')->where('order_id', $request->order_id)->first();
                return new SuccessWithData($order);
            }
            return new ErrorResponse("Invalid operation"); 
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
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
