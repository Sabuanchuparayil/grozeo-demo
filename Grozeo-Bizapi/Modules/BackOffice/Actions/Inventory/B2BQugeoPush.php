<?php

namespace BackOffice\Actions\Inventory;
use App\Models\StockItemMaster;
use BackOffice\Models\B2bOrderItems;
use BackOffice\Models\TransferOrderDetails;
use BackOffice\Models\TransferOrderDetailsBarcodes;

class B2BQugeoPush
{

    public function getB2BStatusUpdateSQL($order_id)
    {
        return "update retaline_B2B_SalesOrder set status_id = '##21',b2b_status_addinfo = '###2',bbso_updatedon = now() where bbso_id = $order_id;  \n select 1 as ss;";
    }

    public function getB2BTrackUrlSQL($order_id)
    {
        return "update retaline_B2B_SalesOrder set bbso_trackURL = '###1', bbso_DeliveryDriver ='##10', bbso_DeliveryDriverNumber ='##11' where bbso_id = $order_id;";
    }

    public function getB2BItemDetails($order_id,$transferorderdetailsid)
    {        
         $orders = B2bOrderItems::where('bbso_id', $order_id)
                                ->select('b2bso_netamount as rate', 'b2bso_itemqty as count', 'b2bso_itemid', 'bbsd_id')
                                ->get()
                                ->toArray();
        $product_id = array_column($orders, 'b2bso_itemid');
        
        $item = StockItemMaster::whereIn('stit_ID', $product_id)
                                ->select('stit_ID' ,'stit_SKU')
                                ->get()
                                ->toArray();

        $item_names = array_column($item, 'stit_SKU', 'stit_ID');

        $transferorderdetails = TransferOrderDetails::where('fsto_id',$order_id)
        ->select('fsto_ItemId','fstod_id')
        ->get();

        foreach($orders as $key => $order)
        {
            $orders[$key]['itemname'] = $item_names[$order['b2bso_itemid']];
            $transferorderdetailsid = $this->getTransferOrderDetailsID($transferorderdetails,$order['b2bso_itemid']);
            $orders[$key]['barcodes'] = $this->getB2BBarCode($transferorderdetailsid);
            unset($orders[$key]['bbsd_id']);
            unset($orders[$key]['b2bso_itemid']);
        }

        return json_encode($orders);

    }

    private function getB2BBarCode($transferorderdetailsid)
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

    public function getB2BItemReturnUpdateSQL($order_id)
    {
        return "UPDATE retaline_customer_order SET order_ItemsReturned = '##13', order_HasReturn = IF(LENGTH(order_ItemsReturned)>0,1,0) WHERE order_id = $order_id;"; 
    }

    public function getB2BAmount($order)
    {

        return 0;
    }

    public function getB2BPaymentMode($order)
    {

            return "Credit";

    }

}
