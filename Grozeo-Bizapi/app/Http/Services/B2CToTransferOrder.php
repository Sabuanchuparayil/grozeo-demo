<?php

namespace App\Http\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockItemMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use BackOffice\Models\TransferOrder;
use BackOffice\Status\DeliveryMethods;
use BackOffice\Models\TransferOrderDetails;

use BackOffice\Http\Controllers\CostDistribution\CostDistributionController;

use App\Helpers\EmailHelper;
use App\Models\Customer;
use App\Models\Branch;

class B2CToTransferOrder
{
    public static function transferOrders($order_id)
    {
        return (new static)->insert($order_id);
    }

    private function insert($order_id)
    {
        $order = Order::where('order_id', $order_id)
                    ->select('order_branch_id','order_customer_id','order_delivery_start_at','order_delivery_charge','order_total_gst','order_total_cgst','order_total_sgst','order_kfc_amount','order_discount_amount',
                            'order_total_amount','subtotal','total','order_method','order_slot_id','status_id','order_delivery_charge_gst','order_roundoff','total_afterpacking', 'storegroup_id', 'order_order_id', 'order_confirm_date')
                    ->get();
        $orderdets = OrderItem::where('customer_order_id', $order_id)
                    ->select('item_product_id','item_order_qty','item_retail_price','item_sales_price','item_order_qty','item_cgst','item_sgst','item_amount','item_price','item_kfc')
                    ->get();     
        $tcsValue = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TCS' limit 1");
        // config('app.possible_delivery_methods') ;
        //Create Transfer Order
        $transferOrder = new TransferOrder;    
        $transferOrder_no = TransferOrder::nextTransferOrderNo( $order[0]->order_branch_id);   
        $transferOrder->fsto_uid = $transferOrder_no;
        $transferOrder->fsto_openingtime =  $order[0]->order_delivery_start_at;
        $transferOrder->fstr_id = $order_id;
        if($order[0]->order_method ==3){
            $transferOrder->fsto_DeliveryMethodsAllowedPossible = DeliveryMethods::COURIER ;
        }elseif($order[0]->order_method ==2){
            $transferOrder->fsto_DeliveryMethodsAllowedPossible = DeliveryMethods::CUSTOMER_PICKUP ;
        }else{
            $transferOrder->fsto_DeliveryMethodsAllowedPossible = config('app.possible_delivery_methods') ;
        }
        $transferOrder->fsto_source =  $order[0]->order_branch_id;
        $transferOrder->fsto_ordertype = 1;
        $transferOrder->fsto_type = 1;
        if($order[0]->order_slot_id > 0){
            $transferOrder->fsto_status = 11;
        }else{
            $transferOrder->fsto_status = 1;
        }
        
        $transferOrder->fsto_sourcetype = 1;
        $transferOrder->fsto_destination = $order[0]->order_customer_id;
        $transferOrder->fsto_destinationtype = 2;
        $transferOrder->fsto_closingQuery = "";
        $transferOrder->fsto_closingaction = 1;
        $transferOrder->fsto_closingapi = "";
        $transferOrder->fsto_closingapiconfigjson = "";
        $transferOrder->fsto_assigned_boy = 0;
        $transferOrder->fsto_polled_boy = 0;
        $transferOrder->fsto_initiatedBy = 0;
        $transferOrder->fsto_handlingcharge = $order[0]->order_delivery_charge;
        $transferOrder->fsto_handlingchargeEt = ($order[0]->order_delivery_charge - $order[0]->order_delivery_charge_gst);
        $transferOrder->fsto_tcsbaseamount = $order[0]->order_total_amount + $transferOrder->fsto_handlingchargeEt;
        $transferOrder->fsto_tcs  = $transferOrder->fsto_tcsbaseamount*$tcsValue[0]->cfg_Value / 100;
        $transferOrder->fsto_cgstval = round($order[0]->order_total_cgst,2); //
        $transferOrder->fsto_sgstval = round($order[0]->order_total_sgst,2);
        $transferOrder->fsto_kfcval = $order[0]->order_kfc_amount;
        $transferOrder->fsto_discount = $order[0]->order_discount_amount;
        $transferOrder->fsto_amtbeforetax = $order[0]->order_total_amount;
        $transferOrder->fsto_amtaftertax = $order[0]->subtotal;
        $transferOrder->fsto_netamount =  $order[0]->total;
        $transferOrder->save();  
        $transferOrder_id =  $transferOrder->fsto_id;
        //Create Transfer Order Details
        foreach($orderdets as $orderdet){
            $transferOrderDets = new TransferOrderDetails;
            $transferOrderDets->fsto_id = $transferOrder_id;
            $transferOrderDets->fsto_uid = $transferOrder_no ;
            $transferOrderDets->fsto_ItemId =  $orderdet->item_product_id;
            $transferOrderDets->fsto_ItemQty =  $orderdet->item_order_qty;
            $transferOrderDets->fsto_pkdQty =0;
            $transferOrderDets->fstro_ItemMRP = $orderdet->item_retail_price;
            $transferOrderDets->fstro_ItemSPincTax = $orderdet->item_sales_price;
            $itemdets = StockItemMaster::where('stit_ID', $orderdet->item_product_id)
                            ->select( 'item_weight','stit_item_volume')
                            ->first();
            //$gstpercent = $itemdets->stit_GST;
            $transferOrderDets->fsto_ItemWeight = round($itemdets->item_weight*$orderdet->item_order_qty,3);
            $transferOrderDets->fsto_ItemVolume = round($itemdets->stit_item_volume*$orderdet->item_order_qty,3);;
            $transferOrderDets->fstro_gst_percent = $orderdet->item_cgst+$orderdet->item_sgst;
            $transferOrderDets->fstro_cgst_percent = $orderdet->item_cgst;
            $transferOrderDets->fstro_sgst_percent = $orderdet->item_sgst;
            $transferOrderDets->fstro_cgst_value = round($orderdet->item_cgst*$orderdet->item_amount/100,2);
            $transferOrderDets->fstro_sgst_value = round($orderdet->item_sgst*$orderdet->item_amount/100,2);
            $transferOrderDets->fstro_gst_value =    round($transferOrderDets->fstro_sgst_value + $transferOrderDets->fstro_cgst_value,2) ;
            $transferOrderDets->fstro_totamtbeforetax = $orderdet->item_amount;
            $transferOrderDets->fstro_totamtaftertax = $orderdet->item_price;
            $transferOrderDets->fstro_kfc_percent = $orderdet->item_kfc;
            $transferOrderDets->fstro_kfc_value = round($orderdet->item_kfc * $orderdet->item_amount,2) ;
            $transferOrderDets->save();  
        }
        // $distAllocation = $this->costDistributionAllocations($order_id);

        try
        {
            $branchData = Branch::where('br_ID', $order[0]->order_branch_id)->first();
            $storename = @$branchData->storegroup->store_group_name ? $branchData->storegroup->store_group_name : 'Grozeo';
            $customer = Customer::find($order[0]->order_customer_id);
            $sendEmail = (new EmailHelper)->sendEmail('orderComplete', [
                'fullname'      => $customer->cust_customer_name,
                'email'         => $customer->cust_email,
                'storename'     => $storename,
                'sku'           => '',
                'ordernum'      => $order[0]->order_order_id,
                'orderdate'     => $order[0]->order_confirm_date,
                'orderquantity' => count($orderdets),
                'deliverydate'  => 'Yet to be delivered',
                'total'         => $order[0]->total
            ]);
        }
        catch (\Exception $e)
        {
            // info($e->getMessage()); 
        }
        return true;
    }
    private function costDistributionAllocations($order_id)
    {
        return (new CostDistributionController)->addCostDistribution($order_id);
    }
}
