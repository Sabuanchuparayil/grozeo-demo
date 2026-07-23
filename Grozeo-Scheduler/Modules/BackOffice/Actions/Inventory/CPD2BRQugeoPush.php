<?php

namespace BackOffice\Actions\Inventory;
use App\Models\StockItemMaster;
use BackOffice\Models\TransferOrderDetails;
use BackOffice\Models\TransferOrderDetailsBarcodes;


class CPD2BRQugeoPush
{

    public function getCPD2BRStatusUpdateSQL($transferrequest_id)
    {
        return "update finascop_stock_transfer_request set fstr_status = '##31',fstr_status_addinfo = '###2',fstr_updatedOn = now() where fstr_id = $transferrequest_id; \n select 1 as ss;";
    }

    public function getCPD2BRTrackUrlSQL($transferrequest_id)
    {
        return "update finascop_stock_transfer_request set fstr_trackURL = '###1', fstr_DeliveryDriver ='##10', fstr_DeliveryDriverNumber ='##11' where fstr_id = $transferrequest_id;";
    }

    public function getCPD2BRItemDetails($transferorder_id)
    {
         $orders = TransferOrderDetails::where('fsto_id', $transferorder_id)
                                ->selectraw("0 as rate, fsto_pkdQty as count, fsto_ItemId, fstod_id")
                                ->get()
                                ->toArray();
        $product_id = array_column($orders, 'fsto_ItemId');
        
        $item = StockItemMaster::whereIn('stit_ID', $product_id)
                                ->select('stit_ID' ,'stit_SKU')
                                ->get()
                                ->toArray();

        $item_names = array_column($item, 'stit_SKU', 'stit_ID');

        foreach($orders as $key => $order)
        {
            $orders[$key]['itemname'] = $item_names[$order['fsto_ItemId']];
            $orders[$key]['barcodes'] = $this->getTransferOrderBarCode($order['fstod_id']);
            unset($orders[$key]['fstod_id']);
            unset($orders[$key]['fsto_ItemId']);
        }

        return json_encode($orders);

    }
    
    private function getTransferOrderBarCode($transferorderdetailsid)
    {
        $barcode = TransferOrderDetailsBarcodes::where('fstod_id', $transferorderdetailsid)
                               
                                ->get()
                                ->toArray();
        return array_column($barcode, 'stiid_barcode');
    }

    public function getCPD2BRItemReturnUpdateSQL($order_id)
    {
        return "UPDATE finascop_stock_transfer_request SET fstr_itemsReturned = '##13', fstr_HasReturn = IF(LENGTH(fstr_itemsReturned)>0,1,0) WHERE fstr_id = $order_id;"; 
    }

    public function getCPD2BRAmount($order)
    {
        return 0;
    }

    public function getCPD2BRPaymentMode($order)
    {
        return "Internal";
    }

}
