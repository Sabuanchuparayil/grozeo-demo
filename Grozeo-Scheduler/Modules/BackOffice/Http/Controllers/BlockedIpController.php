<?php

namespace BackOffice\Http\Controllers;

use Response;
use Illuminate\Http\Request;
use App\Modules\CustomerPickupOtp;
use App\Models\{
    Branch,
    Order,
    Customer,
    OrderHistory,
    CustomerOrderStatus
};

use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\OrderHistoryRepository;
use App\Http\Controllers\OrderCompleteController;

class BlockedIpController
{
    public function __construct()
    {
    }
    
    public function getOrdersByPhone(Request $request)
    {
        $data = [
            'status'    => 'error',
            'message'   => 'Customer not found',
            'data'      => []
        ];
        if(@$request->phone != '')
        {
            $customerDetails = Customer::where('cust_mobile', (string)$request->phone)->with("primaryAddress:deli_house_name,deli_land_mark,deli_post,deli_city,deli_state,deli_district,deli_customer_id")->first();
            if($customerDetails)
            {
                $data['status'] = 'ok';
                $data['message'] = '';
                $customer_id = $customerDetails->cust_id;
                unset($customerDetails->primaryAddress->deli_customer_id);
                $data['data']['customer'] = [
                    'name'          => $customerDetails->cust_customer_name,
                    'email'         => $customerDetails->cust_email,
                    'phone'         => $customerDetails->cust_mobile,
                    // 'address'       => $customerDetails->primaryAddress,
                    'activeOrders'  => Order::where([
                        ['order_customer_id', $customer_id],
                        ['status_id', '>=', '4'],
                        ['status_id', '<', '19']
                    ])->count()
                ];

                $orderFields = DB::raw('order_id, order_order_id,status_id,(SELECT customer_description FROM retaline_customer_order_status where  retaline_customer_order_status.status_id = retaline_customer_order.status_id) as status,created_at,order_status_addinfo,order_trackURL,order_delivered_date,total + 0E0 as order_total, subtotal + 0E0 as order_subtotal, order_delivery_charge + 0E0 as order_delivery_charge, order_courier_charge + 0E0 as order_courier_charge, order_payment_gateway_refid as bank_reference_id,(
                    CASE
                        WHEN payment_mode=1 THEN "Cash on Delivery(COD)"
                        WHEN payment_mode=2 THEN "Online"
                        WHEN payment_mode=3 THEN "Wallet"
                        WHEN payment_mode=4 THEN "Cash on Delivery(COD) with Wallet"
                        WHEN payment_mode=5 THEN "Online with Wallet"
                        WHEN payment_mode=6 THEN "Online on Delivery"
                        WHEN payment_mode=7 THEN "Cash on Delivery(COD)"
                        ELSE "Unavailable"
                    END ) as payment_mode,
                    order_branch_id,order_customer_cancel_till,now() as order_time_now,if(order_customer_cancel_till>now() and status_id<>19,\'true\',\'false\') as order_can_cancel,storegroup_id');
                $query = Order::query();

                $query->whereHas('orderHistory', function ($q1)
                {
                    $q1->where('order_status','!=',0);
                })
                ->with(['orderHistory' => function ($q)
                {
                    $q->with(['getOrderStatus' => function ($status)
                    {
                        $status->select('status_id', 'customer_description as status')->where('status_id','!=',0);
                    }])
                    ->select('order_id', 'order_status', 'created_at')->where('order_status','!=',0);
                }])
                ->with('deliveryAddress:customer_order_id,order_customer_name,order_customer_email,order_contact_no,order_house_no,order_house_name,order_address,order_address2,order_land_mark,order_city,order_post,order_state')
                ->select($orderFields)
                ->where('order_customer_id', $customer_id)
                ->where('status_id', '>', "0")
                ->orderBy('created_at', 'desc');
                $list = $query->paginate(10);
                $mdata = $list->getCollection();
                if(count($mdata) > 0)
                {
                    $mdata = $this->getOrderList($mdata->toArray());
                    $list->setCollection(collect($mdata));
                }
                $data['data']['orders'] = $list;
            }
        }
        return response()->json($data);
    }

    public function getSingleOrder(Request $request)
    {
        $data = [
            'status'    => 'error',
            'message'   => 'Order not found',
            'data'      => []
        ];
        if(@$request->order != '')
        {
            $order = Order::where('order_order_id', $request->order)->first();
            if($order)
            {
                $data['status'] = 'ok';
                $data['message'] = '';
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

                $storegroup = DB::table('finascop_branch_group')
                ->select('store_group_name')                       
                ->where('store_group_id', $order->storegroup_id)
                ->first();

                $slotdate = ''; $slottime='';
                if($order->order_slot_id != null){
                    $slot = DB::select('SELECT rbds_time_from, rbds_time_to, CONCAT_WS(" - ", CONCAT_WS(" ", CONCAT_WS(".", TRIM(LEADING "0" FROM NULLIF(TIME_FORMAT(rbds_time_from, "%h"), "00")), NULLIF(TIME_FORMAT(rbds_time_from, "%i"), "00")), TIME_FORMAT(rbds_time_from, "%p")), CONCAT_WS(" ", CONCAT_WS(".", TRIM(LEADING "0" FROM NULLIF(TIME_FORMAT(rbds_time_to, "%h"), "00")), NULLIF(TIME_FORMAT(rbds_time_to, "%i"), "00")), TIME_FORMAT(rbds_time_to, "%p"))) AS slot FROM retaline_branch_delivery_slot WHERE rbds_id= ' . $order->order_slot_id);
                    if(isset($slot) && count($slot) > 0){
                        $slottime = $slot[0]->slot; // . ' - ' .  $slot[0]->rbds_time_to;
                    }
                }
                $tracking = [];
                $shippingData = DB::table('shipping_consignment')->where('order_id', $order->order_order_id)->first();
                if($shippingData)
                {
                    $tracking = DB::table('consignment_tracking')->select('tracking_id', 'status_id', 'status_value', 'location', 'status_date')->where('tracking_id', $shippingData->tracking_id)->get();

                }
                $branchID = $order->order_branch_id;
                $orderItems = $order->orderItems()->select('item_product_id')
                /* ->with(
                    ['image'=>function($q) use ($domain)
                    {
                        $q->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));
                    }]) */
                    ->with(['item'=>function($q) use($branchID)
                    {
                        $q->select('finascop_stock_itemmaster.stit_ID', 'finascop_stock_itemmaster.stit_sku', 'finascop_stock_itemmaster.stit_brand_name')
                            ->leftJoin('finascop_stock_branch_inventory as fsbi', 'finascop_stock_itemmaster.stit_ID', '=', 'fsbi.stit_id')
                            ->where('fsbi.branch_id', $branchID);
                        ;
                    }])
                ->get();
                $order_shipping_address = $order->deliveryAddress()->select('order_city', 'order_contact_no', 'order_customer_email', 'order_customer_name', 'order_state')->get();
                $payment = '';
                switch ($order->payment_mode)
                {
                    case 1:
                        $payment = "Cash on Delivery(COD)";
                        break;
                    case 2:
                        $payment = "Online";
                        break;
                    case 3:
                        $payment = "Wallet";
                        break;
                    case 4:
                        $payment = "Cash on Delivery(COD) with Wallet";
                        break;
                    case 5:
                        $payment = "Online with Wallet";
                        break;
                    case 6:
                        $payment = "Online on Delivery";
                        break;
                    case 7:
                        $payment = "Cash on Delivery(COD)";
                        break;
                    
                    default:
                        $payment = '';
                        break;
                }
                $data['data'] = [
                    'order_id' => $request->order,
                    // 'order_group_id' => $order->order_group_id,
                    // 'order_date' => $order->created_at,
                    'storegroup_id' => $order->storegroup_id,
                    'storegroup_name'   => ($storegroup ? $storegroup->store_group_name : 'Grozeo'),
                    'store_name' => $branch->br_Name,
                    'store_location' => ($branch->br_Name == $branch->br_City) ? $branch->br_Address : $branch->br_City,
                    'store_phone' => $branch->br_Phone,
                    'order_branch_id'   => $order->order_branch_id,
                    // 'order_datetime' => $order->created_at,
                    // 'order_delivered_date' => $order->order_delivered_date,
                    'order_shipping_address' => $order_shipping_address,
                    'order_items' => $orderItems,
                    'order_total' => $order->total,
                    'order_subtotal' => $order->subtotal,
                    // 'order_kfc' => $order->order_kfc_amount,
                    'order_shipping_charge' => ($order->order_delivery_charge == 0 && $order->order_courier_charge > 0 ? $order->order_courier_charge : $order->order_delivery_charge),
                    // 'order_total_gst' => $order->order_total_gst,
                    // 'order_total_cgst' => round($order->order_total_cgst, 2),
                    // 'order_total_sgst' => round($order->order_total_sgst, 2),
                    // 'order_amount' => $order->order_total_amount,
                    // 'order_discount' => ($order->order_discount_amount > 0) ? $order->order_discount_amount : 0,
                    // 'payment_mode_val' => $order->payment_mode,
                    'payment_mode' => $payment ?? '',
                    // 'order_trackURL' => $order->order_trackURL ?? '',
                    'order_status' => $this->getOrderStatus($order->order_id),
                    // 'order_primary_key' => $order->order_id,
                    // 'order_DeliveryDriver' => $order->order_DeliveryDriver ?? '',
                    // 'order_DeliveryDriverNumber' => $order->order_DeliveryDriverNumber ?? '',
                    // 'order_customerpickup_otp'=>$order->order_customerpickup_otp,
                    // 'otp_message'=>app(CustomerPickupOtp::class)->getMessage($order,$order->order_customerpickup_otp),
                    // 'order_deliveryslot_time' => $slottime,
                    // 'order_deliveryslot_date' => $order->order_slot_date,
                    // 'order_customer_cancel_till' => $order->order_customer_cancel_till,
                    'order_time_now' => now()->format('Y-m-d  H:i:s'),
                    // 'order_can_cancel' =>  (now()->format('Y-m-d')<$order->order_customer_cancel_till) && ($order->status_id != 19) ?? false,
                    // 'order_history' => $order->orderHistory,
                    'tracking_details'  => $tracking
                ];
                /*if($order->order_roundoff != 0)
                {
                    $data['data']['order_roundoff'] = round($order->order_roundoff, 2);
                }*/
            }
        }
        return response()->json($data);
    }

    public function saveCRMHistory(Request $request)
    {
        try
        {
            $validated = $request->validate([
                'phone'         => 'required',
                'CustomerType'  => 'required',
            ]);
            $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));

            $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
            $dynamoClient->putItem([
            'TableName' => config('aws.prefix').'support_crm',
                'Item'      => [
                    'uuid'          => ['S' => (string)$uuid],
                    'tstamp'        => ['S' => (string)date('Y-m-d H:i:s')],
                    'title'         => ['S' => (string)$request->title],
                    'phone'         => ['S' => (string)$request->phone],
                    'type'          => ['S' => (string)$request->type],
                    'source'        => ['S' => (string)$request->source],
                    'data'          => ['S' => (string)$request->data],
                    'storegroupid'  => ['S' => (string)$request->Storegroupid],
                    'customer_type' => ['S' => (string)$request->CustomerType],
                    'createdOn'     => ['S' => (string)$request->createdOn]
                ]
            ]);
            return response()->json([
                'status'    => 'success'
            ]);
        }
        catch (\Exception $e)
        {
            return response()->json([
                'status'    => 'failure',
                'msg'       => $e->getMessage()
            ]);
        }
    }

    private function getOrderList(array $list)
    {
        foreach($list as $key => $value)
        {
            $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
            $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

            $storegroup = DB::table('finascop_branch_group')
            ->select('store_group_name')                       
            ->where('store_group_id', $value['storegroup_id'])
            ->first();
            $list[$key]['storegroup_id'] = $value['storegroup_id'];
            $list[$key]['storegroup_name'] = ($storegroup ? $storegroup->store_group_name : 'Grozeo');

            $branch = DB::table('finascop_branch')
            ->select('br_Name','br_City','br_Address','br_Phone')                       
            ->where('br_ID', $value['order_branch_id'])
            ->first() ;     
            
            $list[$key]['store_name'] = (isset($branch)?$branch->br_Name : '');
            $list[$key]['store_location'] = (isset($branch)? (($branch->br_Name == $branch->br_City) ? $branch->br_Address : $branch->br_City)  : '');
            $list[$key]['store_phone'] = (isset($branch)?$branch->br_Phone : '');  
            
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

            $itemname = DB::table('finascop_stock_itemmaster')
            ->select('stit_itemName')                       
            ->where('stit_ID', $itemdets->item_product_id)    
            ->first() ;  
            
            $itemdetails = DB::table('retaline_customer_order_items')
            ->select('item_product_id')                       
            ->where('customer_order_id', $value['order_id'])            
            ->orderBy('item_sales_price', 'desc')
            ->get() ; 
            $image_url = $images->image_url ?? '';  
             $list[$key]['highest_priced_image'] = $image_url?$domain.'thumbnail-'.$image_url:'';  
            $list[$key]['highest_priced_itemname'] = $itemname->stit_itemName;  
            $list[$key]['total_item_count'] = $itemcount; 
        }
        
       return $list;
    }
    private function getOrderStatus($order_id)
    {
        $status = OrderHistory::where('order_id', $order_id)
                        ->orderBy('id', 'desc')
                        ->first(['order_status']);
        $status_id = $status->order_status ?? '';
        return CustomerOrderStatus::where('status_id', $status_id)
                                    ->first(['status_id', 'customer_description as status']);
    }
}