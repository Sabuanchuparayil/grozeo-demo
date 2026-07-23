<?php

namespace BackOffice\Actions\Inventory;
use App\Models\StockItemMaster;
use BackOffice\Models\TransferOrderDetails;
use BackOffice\Models\TransferOrderDetailsBarcodes;


class RETPACKQugeoPush
{

    public function getRETPACKStatusUpdateSQL($transferrequest_id)
    {
        return "update finascop_stock_return_request_packing set frrp_status = '##61',frrp_updatedOn = now() where frrp_id = $transferrequest_id; \n select 1 as ss;";
    }

    public function getRETPACKTrackUrlSQL($transferrequest_id)
    {
        return "update finascop_stock_return_request_packing set frrp_updatedOn = now() where frrp_id = $transferrequest_id;";
    }

    public function getRETPACKItemDetails($transferorder_id)
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

    public function getRETPACKItemReturnUpdateSQL($order_id)
    {
        return "UPDATE finascop_stock_return_request_packing SET fstr_itemsReturned = '##13', fstr_HasReturn = IF(LENGTH(fstr_itemsReturned)>0,1,0) WHERE fstr_id = $order_id;"; 
    }

    public function getRETPACKAmount($order)
    {
        return 0;
    }

    public function getRETPACKPaymentMode($order)
    {
        return "Internal";
    }

}
