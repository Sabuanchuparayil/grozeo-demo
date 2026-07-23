<?php

namespace App\Http\Controllers;

use App\Http\Responses\SuccessWithData;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\OrderHistoryRepository;

class BlockedIpController extends Controller
{
    public function __construct()
    {
    }
    
    public function getOrdersByPhone($phone)
    {
        $data = [];
        $customerDetails = Customer::where('cust_mobile', $phone)->first();
        if($customerDetails)
        {
            $customer_id = $customerDetails->cust_id;
            $orderFields = DB::raw('order_id, order_order_id,status_id,(SELECT customer_description FROM retaline_customer_order_status where  retaline_customer_order_status.status_id = retaline_customer_order.status_id) as status,created_at,order_status_addinfo,order_trackURL,order_delivered_date,total + 0E0 as order_total, subtotal + 0E0 as order_subtotal, order_delivery_charge + 0E0 as order_delivery_charge, order_courier_charge + 0E0 as order_courier_charge, order_payment_gateway_refid as bank_reference_id,payment_mode,order_branch_id,order_customer_cancel_till,now() as order_time_now,if(order_customer_cancel_till>now() and status_id<>19,\'true\',\'false\') as order_can_cancel');
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
            ->select($orderFields)
            ->where('order_customer_id', "$customer_id")
            ->where('status_id', '>', "0")
            ->orderBy('created_at', 'desc');
            $list = $query->get()->makeVisible('created_at');
            $data = (count($list) > 0) ? $this->getOrderList($list->toArray()) : $list;
        }
            /*$branchID = $branchDetails->br_ID;
            $customer = 'SELECT CONCAT(cust_customer_name,", ", cust_email, ", ", cust_mobile) FROM retaline_customer WHERE cust_id = retaline_customer_order.order_customer_id';
            $status = 'SELECT customer_description FROM retaline_customer_order_status WHERE status_id = retaline_customer_order.status_id';
            $data = Order::select([
                '*',
                DB::raw("({$customer}) as customer_details"),
                DB::raw("({$status}) as status")
            ])->where('order_branch_id', $branchID)->get();*/
        return new SuccessWithData($data);
    }

    private function getOrderList(array $list)
    {
        //Can we provide Order ID, Order Status, Branch Name, Branch Location, Branch Phone, Order Date & Time, Order Amount,  Payment Mode, Bank Ref in the order details
        foreach($list as $key => $value)
        {
            $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
            $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

            $branch = DB::table('finascop_branch')
            ->select('br_Name','br_City','br_Address','br_Phone')                       
            ->where('br_ID', $value['order_branch_id'])
            ->first() ;     
            
            $list[$key]['branch_name'] = (isset($branch)?$branch->br_Name : '');
            $list[$key]['branch_location'] = (isset($branch)? (($branch->br_Name == $branch->br_City) ? $branch->br_Address : $branch->br_City)  : '');
            $list[$key]['branch_phone'] = (isset($branch)?$branch->br_Phone : '');  
            
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
}