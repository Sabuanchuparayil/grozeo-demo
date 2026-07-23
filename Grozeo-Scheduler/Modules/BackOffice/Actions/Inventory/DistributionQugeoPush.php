<?php

namespace BackOffice\Actions\Inventory;
use App\Models\StockItemMaster;
use BackOffice\Models\TransferOrderDetails;
use BackOffice\Models\TransferOrderDetailsBarcodes;


class DistributionQugeoPush
{

    public function getDistributionStatusUpdateSQL($transferrequest_id)
    {
        return "update retaline_distribution_chart set rdc_status = '##31',rdc_status_addinfo = '###2',rdc_updatedOn = now() where rdc_id = $transferrequest_id; \n select 1 as ss;";
    }

    public function getDistributionTrackUrlSQL($transferrequest_id)
    {
        return "update retaline_distribution_chart set rdc_trackURL = '###1', rdc_DeliveryDriver ='##10', rdc_DeliveryDriverNumber ='##11' where rdc_id = $transferrequest_id;";
    }

    public function getDistributionItemDetails($transferorder_id)
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

    public function getDistributionItemReturnUpdateSQL($order_id)
    {
        return "UPDATE retaline_distribution_chart SET rdc_itemsReturned = '##13', rdc_HasReturn = IF(LENGTH(rdc_itemsReturned)>0,1,0) WHERE rdc_id = $order_id;"; 
    }

    public function getDistributionAmount($order)
    {
        return 0;
    }

    public function getDistributionPaymentMode($order)
    {
        return "Internal";
    }

}
