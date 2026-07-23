<?php

namespace BackOffice\Http\Services;

use stdClass;
use App\Models\Order;
use App\Models\Client;
use App\Models\Customer;
use App\Models\OrderItem;
use BackOffice\Models\Branch;
use BackOffice\Models\B2bOrder;
use BackOffice\Status\QugeoType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use BackOffice\Models\ReturnPacking;
use BackOffice\Models\TransferOrder;
use BackOffice\Status\B2bOrderStatus;
use BackOffice\Models\TransferRequest;
use BackOffice\Status\DeliveryMethods;
use BackOffice\Status\CustomerOrderStatus;
use BackOffice\Models\Invoice;
use BackOffice\Models\InvoiceDetails;
use BackOffice\Actions\Inventory\B2BQugeoPush;
use BackOffice\Actions\Inventory\B2CQugeoPush;
use BackOffice\Actions\Inventory\InvoiceProcessor;
use BackOffice\Actions\Inventory\CPD2BRQugeoPush;
use BackOffice\Actions\Inventory\RETPACKQugeoPush;
use BackOffice\Actions\Inventory\DistributionQugeoPush;
use BackOffice\Models\DistributionChart;
use BackOffice\Models\TransferOrderDetails;

class TransferOrderToInvoice
{
    protected const CPD_ORDER = 0;

    protected const CUSTOMER_ORDER = 1;

    protected const B2B_ORDER = 2;

    protected const STOCK_RETURN = 3;
    
    protected const BRANCH_DISTRIBUTION = 4;

    public static function createInvoice($transferOrder,$type,$number_bags,$invoiceamt)
    {
        return (new static)->insert($transferOrder,$type,$number_bags,$invoiceamt);
    }
    
     private function insert($transferOrder,$type,$number_bags,$invoiceamt)
    {
         $orderdets = TransferOrderDetails::where('fsto_id', $transferOrder->fsto_id)
                    ->select('fsto_ItemId','fsto_ItemQty','fsto_pkdQty','fstro_ItemMRP','fstro_ItemSPincTax','fstro_totamtaftertax','fsto_stockValue','fstro_ItemPackedSPincTax')
                    ->get();
         if($type === static::CUSTOMER_ORDER){          
            $orders = new Order;
            $order = $orders->find($transferOrder->fstr_id,['order_order_id','order_packedbags_count','order_branch_id','order_id','payment_mode','order_amount_payable','order_customer_id','total_afterpacking','total','order_delivery_charge','order_total_amount','subtotal','order_roundoff']); 
            
            $invoice = new Invoice;    
            $invoice_no = Invoice::nextInvoiceNo($order->order_branch_id);
            $invoice->invoiceNumber = $invoice_no; 
            $invoice->orderType = static::CUSTOMER_ORDER;
            $salesOrder = DB::table('B2CSalesOrder')->select('id', 'SONumber')->where('customer_order_id', $order->order_id)->first();
            $invoice->bci_bcso_id = $salesOrder->id;
            $invoice->bci_fsto_id = $transferOrder->fsto_id;
            $invoice->bci_fstr_id = $order->order_id;
            $invoice->bci_br_ID = $order->order_branch_id;
            $invoice->bci_Customer_ID = $order->order_customer_id;
            $invoice->createdon = now()->format('Y-m-d H:i:s');
            $invoice->updatedon = now()->format('Y-m-d H:i:s');
            $invoice->invoiceDate = now()->format('Y-m-d');
            if($order->total_afterpacking > 0){
                $invoiceValue = $order->total_afterpacking;
            }else{
                $invoiceValue = $order->total;
            }
            $invoice->invoiceValue = $invoiceValue;
            $invoice->HandlingCharges = $order->order_delivery_charge;
            $invoice->InvValBtax = $order->order_total_amount;
            $invoice->InvValAtax = $order->subtotal;
            $invoice->roundoff = $order->order_roundoff;
            if(!empty($order->order_amount_payable)){
                $order_amount_payable =  $order->order_amount_payable;
            }else{
                $order_amount_payable =  0;
                
            }
            $invoice->AmountCollectible = $order_amount_payable;
            $invoice->InitialPaymode = $order->payment_mode;
            $invoice->save();  
            
            $invoiceId =  $invoice->id;
            foreach($orderdets as $orderdet){
                $invDets = new InvoiceDetails;
                $invDets->bci_id = $invoiceId;
                $invDets->itemid =  $orderdet->fsto_ItemId;
                $invDets->itemqty =  $orderdet->fsto_ItemQty;
                $invDets->itemPackedqty = $orderdet->fsto_pkdQty;
                $invDets->itemmrp = $orderdet->fstro_ItemMRP;
                $invDets->itemrate = $orderdet->fstro_ItemSPincTax;
                $invDets->itemConversionValue = $orderdet->fsto_stockValue;
                $invDets->itemrateAfterPack = $orderdet->fstro_ItemPackedSPincTax;
            
                $invDets->save();  
            }
        }    
        return true;
    }
    

}
