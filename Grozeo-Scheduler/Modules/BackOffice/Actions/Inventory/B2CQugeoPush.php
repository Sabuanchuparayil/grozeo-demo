<?php

namespace BackOffice\Actions\Inventory;
use App\Models\OrderItem;
use App\Models\StockItemMaster;
use BackOffice\Models\TransferOrderDetails;
use BackOffice\Models\TransferOrderDetailsBarcodes;
use Illuminate\Support\Facades\Log;

class B2CQugeoPush
{

    //'1 - for pay on delivery, 2 - for Online Payment, 3 - Wallet, 4 - COD with Wallet,  5 - online with Wallet, 6 - Online on Delivery, 7 - Cash on delivery',

    const PAY_ON_DELIVERY = 1;

    const ONLINE = 2;

    const WALLET = 3;

    const COD_WITH_WALLET = 4;

    const ONLINE_WITH_WALLET = 5;

    const ONLINE_ON_DELIVERY =6;

    const CASH_ON_DELIVERY =7;
    
    public function getB2CDeliveryAddress($delivery)
    {
        return $delivery->order_house_no . ' ' . $delivery->order_house_name . ' ' . @$delivery->order_land_mark . ' ' . $delivery->order_address . ' ' . $delivery->order_address2;
    }

    public function getB2CStatusUpdateSQL($order_id)
    {
        return "update retaline_customer_order set status_id = '###1',order_status_addinfo = '###2',payment_mode = if(payment_mode=1,'###6',payment_mode),order_ondel_bankref_id='###7',updated_at = now() where order_id = $order_id; \n insert into retaline_customer_order_history (order_id,order_status,created_at,updated_at) values ($order_id,'###1',now(),now());";
    }

    public function getB2CTrackUrlSQL($order_id)
    {
        return "update retaline_customer_order set order_trackURL = '###1', order_DeliveryDriver ='##10', order_DeliveryDriverNumber ='##11' where order_id = $order_id;";
    }

    public function getB2CTrackHistorySQL($order_id)
    {
        return "insert into retaline_customer_order_history(order_id, order_status, created_at, updated_at) values($order_id, '##12', now(), now());";
    }

    public function getB2CItemDetails($order_id,$transferorderid)
    {        
         $orders = OrderItem::where('customer_order_id', $order_id)
                                ->select('item_sales_price as rate', 'item_order_qty as count', 'item_product_id', 'item_id')
                                ->get()
                                ->toArray();
        $product_id = array_column($orders, 'item_product_id');
        
        $item = StockItemMaster::whereIn('stit_ID', $product_id)
                                ->select('stit_ID' ,'stit_SKU')
                                ->get()
                                ->toArray();

        $item_names = array_column($item, 'stit_SKU', 'stit_ID');

        $transferorderdetails = TransferOrderDetails::where('fsto_id',$transferorderid)
                                                        ->select('fsto_ItemId','fstod_id')
                                                        ->get();
                                                  
        foreach($orders as $key => $order)
        {
            $orders[$key]['itemname'] = $item_names[$order['item_product_id']];
            $transferorderdetailsid = $this->getTransferOrderDetailsID($transferorderdetails,$order['item_product_id']);
            $orders[$key]['barcodes'] = $this->getB2CBarCode($transferorderdetailsid);
            unset($orders[$key]['item_id']);
            unset($orders[$key]['item_product_id']);
        }

        return json_encode($orders);

    }

    private function getB2CBarCode($transferorderdetailsid)
    {
        $barcode = TransferOrderDetailsBarcodes::where('fstod_id', $transferorderdetailsid)                                                       
                                ->get()
                                ->toArray();
        return array_column($barcode, 'stiid_barcode');
    }

    private function getTransferOrderDetailsID($transferorderdets,$ItemId){
        foreach ($transferorderdets as $value) {            
            if($value['fsto_ItemId']==$ItemId){
              return  $value['fstod_id'];
            }
           
          }
    }
    public function getB2CItemReturnUpdateSQL($order_id)
    {
        return "UPDATE retaline_customer_order SET order_ItemsReturned = '##13', order_HasReturn = IF(LENGTH(order_ItemsReturned)>0,1,0) WHERE order_id = $order_id;"; 
    }

    public function getB2CAmount($order,$invoiceamt)
    {
        $payment_mode = $order->payment_mode;
        //if($payment_mode === static::CASH_ON_DELIVERY || $payment_mode === static::PAY_ON_DELIVERY)
        if(!empty($order->order_amount_payable))
        {
           return $order->order_amount_payable;
        }else{
        return 0;
    }

    }

    public function getB2CPaymentMode($order)
    {
        $payment_mode = $order->payment_mode;
        if($payment_mode === static::PAY_ON_DELIVERY)
        {
            return "Pay On Delivery";
        }
        elseif($payment_mode === static::ONLINE) {
            return "Paid Online";
        }
        elseif($payment_mode === static::WALLET) {
            return "Paid with Wallet";
        }
        elseif($payment_mode === static::COD_WITH_WALLET) {
            return "Pay on Delivery";
        }
        elseif($payment_mode === static::ONLINE_WITH_WALLET) {
            return "Paid Online";
        }
        elseif($payment_mode === static::ONLINE_ON_DELIVERY) {
            return "Online ON Delivery";
        }
        elseif($payment_mode === static::CASH_ON_DELIVERY) {
            return "Cash On Delivery";
        }

    }

}
