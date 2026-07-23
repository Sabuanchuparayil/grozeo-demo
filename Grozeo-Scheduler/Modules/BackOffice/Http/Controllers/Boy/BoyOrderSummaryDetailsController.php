<?php

namespace BackOffice\Http\Controllers\Boy;


use App\Http\Responses\SuccessWithData;
use BackOffice\Models\TransferOrderDetails;
use BackOffice\Http\Requests\BoyOrderSummaryDetailsRequest;

class BoyOrderSummaryDetailsController
{
    public function __invoke(BoyOrderSummaryDetailsRequest $request)
    {
        $summary = $this->getOrder($request->order_pk_id);
        return new SuccessWithData($summary);
    }

    private function getOrder($order_pk_id){
        $orders = TransferOrderDetails::where('fsto_id', $order_pk_id)
        ->selectraw('fsto_ItemQty as count, (select stit_SKU from finascop_stock_itemmaster where stit_ID = fsto_ItemId limit 1) as item')
        ->orderBy('item', 'asc')
        ->get();
        $i =1;
        foreach($orders as &$order){
            $order['sl'] = $i;
            $i++;
        }
        return $orders;
    }

}
