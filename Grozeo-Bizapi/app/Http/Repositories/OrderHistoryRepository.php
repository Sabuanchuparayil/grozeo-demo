<?php

namespace App\Http\Repositories;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use BackOffice\Status\CustomerOrderStatus;

class OrderHistoryRepository
{
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function orderList($condition)
    {
        $storegroupid = getHeaderStoreGroup();
        $customer_id = auth_user()->cust_id ?? 0;
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $orders = Order::where([
            ['storegroup_id', $storegroupid],
            ['order_customer_id', $customer_id]
        ]);
        if($condition == 'active')
        {
            $orders->whereNotIn('status_id', $this->activeStatus());
        }
        if($condition == 'failed')
        {
            $orders->whereIn('status_id', $this->failedStatus());
        }
        $orders = $orders->orderBy('created_at', 'desc')->paginate(10);
        $list = $orders->getCollection()->transform(function ($order) use ($domain)
        {
            $item = $order->orderItems->first();
            $image_url = @$item->image->image_url;
            return [
                "order_id"                      => $order->order_id,
                "order_order_id"                => $order->order_order_id,
                "status_id"                     => $order->status_id,
                "status"                        => $order->orderStatus->customer_description,
                "created_at"                    => $order->created_at,
                "order_status_addinfo"          => $order->order_status_addinfo,
                "order_trackID"                 => $order->order_trackID,
                "order_trackURL"                => $order->order_trackURL,
                "order_delivered_date"          => $order->order_delivered_date,
                "order_total"                   => (double)$order->total,
                "order_subtotal"                => (double)$order->subtotal,
                "order_delivery_charge"         => (double)$order->order_delivery_charge,
                "order_courier_charge"          => (double)$order->order_courier_charge,
                "order_method"                  => $order->order_method,
                "bank_reference_id"             => $order->order_payment_gateway_refid,
                "payment_mode"                  => $order->payment_mode,
                "order_branch_id"               => $order->order_branch_id,
                "order_customer_cancel_till"    => $order->order_customer_cancel_till,
                "order_can_cancel"              => (($order->order_customer_cancel_till > now()) && ($order->status_id != 19)) ? "true" : "false",
                "branch_name"                   => @$order->branchDetails->br_Name,
                "branch_location"               => @$order->branchDetails->br_Address ?? @$order->branchDetails->br_City,
                "branch_phone"                  => @$order->branchDetails->br_Phone,
                "highest_priced_image"          => (@$image_url) ? "{$domain}thumbnail-{$image_url}" : "",
                "highest_priced_itemname"       => @$item->item->stit_itemName,
                "total_item_count"              => $order->orderItems->count(),
                "delivery_type"                 => $this->deliveryType($order->order_method)
            ];
        });
        $orders->setCollection($list);
        return $orders;
    }
    private function deliveryType($method)
    {
        switch ($method)
        {
            case 1:
                return "Express Delivery";
                break;
            case 2:
                return "Customer Collect";
                break;
            case 3:
                return "Courier Delivery";
                break;
            
            default:
                return "";
                break;
        }
    }
    private function failedStatus()
    {
        return [
            CustomerOrderStatus::PAYMENT_FAILED,
            CustomerOrderStatus::PAYMENT_TIMEDOUT,
            CustomerOrderStatus::CANCELLED_AFTER_PACKING
        ];
    }
    private function activeStatus()
    {
        return [
            CustomerOrderStatus::CHECKEDOUT,
            CustomerOrderStatus::PAYMENT_FAILED,
            CustomerOrderStatus::PAYMENT_TIMEDOUT,
            CustomerOrderStatus::CANCELLED_AFTER_PACKING,
            CustomerOrderStatus::NOT_DELIVERABLE
        ];
    }

    public function orderList1($condition)
    {
        $storegroupid = getHeaderStoreGroup();

        $customer_id = auth_user()->cust_id ?? 0;
        if($condition == 'active'){
            $statuses  = DB::table('retaline_customer_order_status')
            ->select('status_id')            
            ->wherenotin('status_id',[0,2,21,24])
            ->get() 
            ->pluck('status_id')
            ->toArray(); 
        }elseif($condition == 'failed'){
            $statuses  = DB::table('retaline_customer_order_status')
            ->select('status_id')            
            ->wherein('status_id',[2,21,24])
            ->get()
            ->pluck('status_id')
            ->toArray() ; 
        }else{
            throw new ErrorException("Invalid order's condition requested");            
        }
        $query = $this->order->query();
        /*
                $query->has('orderHistory')
                        ->with(['orderHistory' => function ($q) {
                            $q->with(['getOrderStatus' => function ($status) {
                                $status->select('status_id', 'customer_description as status');
                            }])
                            ->select('order_id', 'order_status', 'created_at');
                        }])*/

                 $query->where('storegroup_id', $storegroupid)->whereHas('orderHistory', function ($q1) {
                    $q1->where('order_status','!=',0);
                    })
                        ->with(['orderHistory' => function ($q) {
                            $q->with(['getOrderStatus' => function ($status) {
                                $status->select('status_id', 'customer_description as status')->where('status_id','!=',0);
                            }])
                            ->select('order_id', 'order_status', 'created_at')->where('order_status','!=',0);
                        }])
                  ->select($this->orderFields())
                ->where('order_customer_id', "$customer_id")
                ->where('status_id', '>', "0")
                ->wherein('status_id',$statuses)
               ->orderBy('created_at', 'desc');
            $list = $query->get()->makeVisible('created_at');
        return (count($list) > 0) ? $this->getOrderList($list->toArray()) : $list;
    }

    public function orderSummary($order_id)
    {
        $customer_id = auth()->user()->cust_id ?? 0;
        $storegroupid = getHeaderStoreGroup();

        $query = $this->order->query();
        $query->where('storegroup_id', $storegroupid)->has('orderStatus')->with(['orderStatus' => function ($query) {
            $query->select('status_id', 'customer_description as status');
        }])->has('deliveryAddress')->with(['deliveryAddress' => function ($query) {
        }]);
        $query->select('order_id', 'order_order_id', 'status_id', 'created_at')
            ->where('order_customer_id', "$customer_id")
            ->where('order_id', $order_id);
        $list = $query->first()->makeVisible('created_at');
        //$list = $query->get();
        return $list;
    }

    public function orderDetails($order_id)
    {

        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;
        $storegroupid = getHeaderStoreGroup();

        return $this->order->where('storegroup_id', $storegroupid)
            ->where('order_customer_id', auth_user()->cust_id)
            ->with(['orderStatus' => function ($query)use($domain) {
            $query->select('status_id', 'customer_description as status');
        }])
            ->with('deliveryAddress')
            ->with(['orderItems' => function ($orderItems)use($domain) {
                $orderItems->with(['orderUniqueItem' => function ($unique)use($domain) {
                    $unique->select($this->getItemFields())
                        ->with(['itemMaster' => function ($itemmaster)use($domain) {
                            $itemmaster->select($this->itemMasterFields())
                                ->with(['mainImage' => function ($mainImage)use($domain){
                                    $mainImage->where('image_type', 1)
                                ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));

                            }]);
                        }]);
                }]);
            }])
            ->where('order_id', $order_id)
            ->get();
    }

    /**
     * Return stock unique items fields
     *
     * @return Array
     */
    private function getItemFields()
    {
        return [
            "fsi_uid",
            "fsi_uid as item_group_id",
            "fsi_item_name as item_name",
            "fsi_brand_name as brand_name",
            "fsi_category_id as category_id",
            "fsi_categry_name as category_name",
            "fsi_variant as variant"
        ];
    }

    /**
     *Item master table fields.
     *
     * @return Array
     */
    private function itemMasterFields()
    {
        return [
            'stit_ID',
            'stit_fsiuid',
            'stit_quantity as quantity',
            'stit_ID as itemId',
            'stit_Description as short_description',
            'stit_long_description as long_description',
        ];
    }

    public function getOrderList(array $list)
    {
        //Can we provide Order ID, Order Status, Branch Name, Branch Location, Branch Phone, Order Date & Time, Order Amount,  Payment Mode, Bank Ref in the order details
        foreach($list as $key => $value)
        {
            /*$order_history = $value['order_history'];
            foreach($order_history as $val => $history)
            {
                $list[$key]['order_history'][$val]['status'] = $history['get_order_status']['status'];
        }*/
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
            //$list[$key]['highest_priced_image'] = $images->image_url ?$domain.'thumbnail-'.$images->image_url: '';
            $deliveryType = '';
            if($value['order_method'] == 1)
            {
                $deliveryType = 'Express Delivery';
            }
            if($value['order_method'] == 2)
            {
                $deliveryType = "Customer Collect";
            }
            if($value['order_method'] == 3)
            {
                $deliveryType = "Courier Delivery";
            }
            $image_url = $images->image_url ?? '';  
             $list[$key]['highest_priced_image'] = $image_url?$domain.'thumbnail-'.$image_url:'';  
            $list[$key]['highest_priced_itemname'] = $itemname->stit_itemName;  
            $list[$key]['total_item_count'] = $itemcount;
            $list[$key]['delivery_type'] = $deliveryType;
        }
        
       return $list;
    }

    private function orderFields()
    {
        //return DB::raw('order_id, order_order_id,status_id,created_at,order_status_addinfo,order_trackURL,if(order_customer_cancel_till>now() and status_id<>19,\'true\',\'false\') as order_can_cancel, total + 0E0 as order_total');        
        return DB::raw('order_id, order_order_id,status_id,(SELECT customer_description FROM retaline_customer_order_status where  retaline_customer_order_status.status_id = retaline_customer_order.status_id) as status,created_at,order_status_addinfo,order_trackID,order_trackURL,order_delivered_date,total + 0E0 as order_total, subtotal + 0E0 as order_subtotal, order_delivery_charge + 0E0 as order_delivery_charge, order_courier_charge + 0E0 as order_courier_charge, order_method, order_payment_gateway_refid as bank_reference_id,payment_mode,order_branch_id,order_customer_cancel_till,now() as order_time_now,if(order_customer_cancel_till>now() and status_id<>19,\'true\',\'false\') as order_can_cancel');
    }

    public function trackUrl($order_id)
    {
        return Order::where('order_id', $order_id)
                        ->where('order_customer_id', auth_user()->cust_id)
                        ->first(['order_trackURL']);
    }

    public function addRating($request)
    {
        $storegroupid = getHeaderStoreGroup();

        return $this->order->where('order_id', $request['order_id'])
                    ->where('storegroup_id', $storegroupid)
                    ->where('order_customer_id', auth_user()->cust_id)
                    ->update([
                        'order_DeliveryRatingStar' => $request['order_DeliveryRatingStar'],
                        'order_DeliveryRatingComment' => $request['order_DeliveryRatingComment']
                    ]);
    }

    public function groupOrders($order_group_id)
    {
        $storegroupid = getHeaderStoreGroup();

        $customer_id = auth_user()->cust_id ?? 0;

        $query = $this->order->query();

                 $query->where('storegroup_id', $storegroupid)
                  ->select($this->orderFields())
                ->where('order_customer_id', "$customer_id")
                ->where('order_group_id', $order_group_id)
               ->orderBy('created_at', 'desc');
            $list = $query->get();
        return (count($list) > 0) ? $this->getOrderList($list->toArray()) : $list;
    }


}
