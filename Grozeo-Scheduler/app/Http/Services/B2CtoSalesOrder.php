<?php

namespace App\Http\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockItemMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SalesOrder;
use App\Status\DeliveryMethods;
use App\Models\SalesOrderDetails;


class B2CtoSalesOrder
{
    public static function salesOrders($order_id)
    {
        return (new static)->insert($order_id);
    }

    private function insert($order_id)
    {
        if(SalesOrder::where('customer_order_id', $order_id)->doesntExist())
        {
            $order = Order::where('order_id', $order_id)
                        ->select('order_branch_id','order_customer_id','order_delivery_start_at','order_delivery_charge','order_total_gst','order_total_cgst','order_total_sgst','order_kfc_amount','order_discount_amount',
                                'order_total_amount','subtotal','total','order_method','order_slot_id','status_id', 'order_order_id', 'order_confirm_date', 'storegroup_id')
                        ->get();
            $orderdets = OrderItem::where('customer_order_id', $order_id)
                        ->select('item_product_id','item_order_qty','item_retail_price','item_sales_price','item_order_qty','item_cgst','item_sgst','item_amount','item_price','item_kfc')
                        ->get();      
            // config('app.possible_delivery_methods') ;
            //Create Transfer Order
            $salesOrder = new SalesOrder;    
            $salesOrder_no = SalesOrder::nextSalesOrderNo( $order[0]->order_branch_id);   
            $salesOrder->SONumber = $salesOrder_no;
            $salesOrder->customer_order_id = $order_id;
            
            $salesOrder->bcso_br_ID =  $order[0]->order_branch_id;
            
            $salesOrder->bcso_Customer_ID = $order[0]->order_customer_id;
            $salesOrder->HandlingCharges = $order[0]->order_delivery_charge;
            $salesOrder->CGSTVal = round($order[0]->order_total_cgst,2); //
            $salesOrder->SGSTVal = round($order[0]->order_total_sgst,2);
            $salesOrder->SOkfcval = $order[0]->order_kfc_amount;
            $salesOrder->SOdiscount = $order[0]->order_discount_amount;
            $salesOrder->SOValBtax = $order[0]->order_total_amount;
            $salesOrder->SOValAtax = $order[0]->subtotal;
            $salesOrder->SOValue =  $order[0]->total;
            $salesOrder->save();  
            $salesOrder_id =  $salesOrder->id;
            //Create Transfer Order Details
            foreach($orderdets as $orderdet){
                $salesOrderDets = new SalesOrderDetails;
                $salesOrderDets->bcso_id = $salesOrder_id;
                $salesOrderDets->itemid =  $orderdet->item_product_id;
                $salesOrderDets->itemqty =  $orderdet->item_order_qty;
                $salesOrderDets->itemscannedqty =0;
                $salesOrderDets->itemmrp = $orderdet->item_retail_price;
                $salesOrderDets->itemrate = $orderdet->item_sales_price;
                
                $salesOrderDets->gst_percent = $orderdet->item_cgst+$orderdet->item_sgst;
                $salesOrderDets->cgst_percent = $orderdet->item_cgst;
                $salesOrderDets->sgst_percent = $orderdet->item_sgst;
                $salesOrderDets->cgst_value = round($orderdet->item_cgst*$orderdet->item_amount/100,2);
                $salesOrderDets->sgst_value = round($orderdet->item_sgst*$orderdet->item_amount/100,2);
                $salesOrderDets->gst_value =    round($salesOrderDets->fstro_sgst_value + $salesOrderDets->fstro_cgst_value,2) ;
                $salesOrderDets->amount_btax = $orderdet->item_amount;
                $salesOrderDets->amount = $orderdet->item_price;
                $salesOrderDets->save();  
            }
        }
        return true;
    }
}
