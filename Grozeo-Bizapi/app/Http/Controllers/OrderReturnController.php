<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Order;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\OrderHistory;
use App\Models\CustomerOrderStatus;
use App\Modules\Checkout;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\ErrorResponse;

class OrderReturnController extends Controller
{
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getAllReturnableProducts($orderId)
    {
        $storegroupid = getHeaderStoreGroup();
        try
        {
            $order = $this->order->where('storegroup_id', $storegroupid)
                ->where('order_order_id', $orderId)
                ->where('order_customer_id', auth_user()->cust_id)
                ->first();
            $domain = "https://".config('filesystems.disks.s3.bucket').".".config('filesystems.disks.s3.driver').".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
            $orderCurrentStatus = $this->getOrderStatus($order->order_id);
            $branchID = $order->order_branch_id;
            $orderItemDetails = $order
                ->orderItems()
                ->with(['image' => function($q)use($domain){
                    $q->select(
                        'id',
                        'product_id',
                        DB::raw('CONCAT("'.$domain.'preview-", image_url) as image_url'),
                        DB::raw('CONCAT("'.$domain.'thumbnail-", image_url) as image_thumb_url')
                    );
                }])
                ->with(['item'=>function($q) use($branchID)
                {
                    $q->select('finascop_stock_itemmaster.stit_ID', 'finascop_stock_itemmaster.stit_sku', 'finascop_stock_itemmaster.stit_brand_name', 'fsbi.hasSpotReturn as stit_custInitiate', 'fsbi.returnTime as stit_itemReturnTime')
                        ->leftJoin('finascop_stock_branch_inventory as fsbi', 'finascop_stock_itemmaster.stit_ID', '=', 'fsbi.stit_id')
                        ->where('fsbi.branch_id', $branchID);
                    ;
                }])
                ->where('item_order_qty_scanned', '>', 0)
                ->get()->toArray();
            $x = 0;
            foreach($orderItemDetails as $oid)
            {
                $orderItemDetails[$x]['returnCanStart'] = 0;
                $orderItemDetails[$x]['isReturnAvailable'] = 0;
                $orderItemDetails[$x]['item']['return_details'] = '';
                if($oid['item']['stit_custInitiate'] == 1)
                {
                    $orderItemDetails[$x]['item']['return_details'] = 'Product has spot return.';
                    $orderItemDetails[$x]['isReturnAvailable'] = 1;
                }
                if($oid['item']['stit_itemReturnTime'] > 0)
                {
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
                            unset($orderItemDetails[$x]);
                        }
                    }
                    else
                    {
                        $orderItemDetails[$x]['returnCanStart'] = 1;
                    }
                }
                if(($oid['item']['stit_itemReturnTime'] == 0) && ($oid['item']['stit_custInitiate'] <= 0))
                {
                    unset($orderItemDetails[$x]);
                }
                $x++;
            }
            return new SuccessWithData(array_values($orderItemDetails));
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
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

    public function returnSelectedProducts(Request $request)
    {
        $validateData = $request->validate([
            'order_id'      => 'required',
            'items'       => 'required|array'
        ]);
        $orderID = $request->order_id;
        $itemList = $request->items;
        try
        {
            $storegroupid = getHeaderStoreGroup();
            $order = $this->order
                ->where('storegroup_id', $storegroupid)
                ->where('order_order_id', $orderID)
                ->where('order_customer_id', auth_user()->cust_id)
                ->first();
            if($order)
            {
                if($order->status_id == 18)
                {
                    $x = 0;
                    $requestCount = $order->order_itemReturnRequestCount;
                    $errors = [];
                    foreach($itemList as $item)   
                    {
                        $checkReturnable = $this->isProductReturnable($item['item_id'], $order->order_id, $order->order_branch_id);
                        if(is_numeric($checkReturnable))
                        {
                            $checkQuantity = OrderItem::where('item_id', $item['item_id'])
                                ->where('customer_order_id', $order->order_id)
                                ->first();
                            $diffQty = @$checkQuantity->item_order_qty_scanned - @$checkQuantity->item_return_qty_requested;
                            if($item['qty'] <= $diffQty)
                            {
                                $updateOrderItem = OrderItem::where('item_id', $item['item_id'])
                                    ->where('customer_order_id', $order->order_id)
                                    ->update([
                                        'item_return_qty_requested'  => $item['qty'] + $checkQuantity->item_return_qty_requested
                                    ]);
                                $x++;
                                $requestCount++;
                            }
                        }
                        else
                        {
                            $errors[] = $checkReturnable;
                        }
                    }
                    if($x > 0)
                    {
                        $updateOrder = $this->order
                            ->where('order_id', $order->order_id)
                            ->update([
                                'order_HasReturnRequest'        => 1,
                                'order_itemReturnRequestCount'  => $requestCount
                        ]);
                        return new SuccessResponse('Return request created for order '.$orderID);
                    }
                    else
                    {
                        return new ErrorResponse('Unable to create a return request for order '.$orderID);
                    }
                }
                else
                {
                    return new ErrorResponse('Order not delivered yet.'); 
                }
            }
            else
            {
                return new ErrorResponse('Order not found.'); 
            }
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    private function isProductReturnable($itemID, $order_id, $branchID)
    {
        $orderItem = OrderItem::with(
            ['item'=>function($q) use($branchID)
            {
                $q->select('finascop_stock_itemmaster.stit_ID', 'finascop_stock_itemmaster.stit_sku', 'finascop_stock_itemmaster.stit_brand_name', 'fsbi.hasSpotReturn as stit_custInitiate', 'fsbi.returnTime as stit_itemReturnTime')
                    ->leftJoin('finascop_stock_branch_inventory as fsbi', 'finascop_stock_itemmaster.stit_ID', '=', 'fsbi.stit_id')
                    ->where('fsbi.branch_id', $branchID);
            }])->where('item_order_qty_scanned', '>', 0)->find($itemID);
        if($orderItem)
        {
            $returns = '';
            $orderCurrentStatus = $this->getOrderStatus($order_id);
            if($orderItem->item->stit_custInitiate == 1)
            {
                $returns = 'Return possible only while accepting delivery for '.$itemID;
            }
            if($orderItem->item->stit_itemReturnTime > 0)
            {
                $now = time();
                $your_date = strtotime($orderCurrentStatus['status_date']);
                $datediff = $now - $your_date;

                $dateDiff = round($datediff / (60 * 60 * 24));
                if($dateDiff <= $orderItem->item->stit_itemReturnTime)
                {
                    $returns = $orderItem->item->stit_itemReturnTime;
                }
                else
                {
                    $returns = 'Return not possible for '.$itemID;
                }
            }
            if(($orderItem->item->stit_itemReturnTime == 0) && ($orderItem->item->stit_custInitiate <= 0))
            {
                $returns = 'Return not possible for '.$itemID;
            }
            return $returns;
        }
        return 'No products found on this order';
    }
}