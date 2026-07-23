<?php

namespace BackOffice\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class TransferOrderResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'item_id'           => $this->fsto_ItemId ?? 0,
            'order_count'       => $this->checkDecimals($this->fsto_ItemQty),
            'packed_count'      => $this->checkDecimals($this->fsto_ItemQty),
            'name'              => $this->item->stit_SKU ?? "",
            'mrp'               => $this->item->mrp ?? "",
            'price'             => $this->price->selling_price ?? 0,
            'image'             => $this->image->image_url ?? "",
            'mode'              => $this->item->stit_ConvertCalcMode ?? "",
            'stockValue'        => ($this->fsto_ItemQty*$this->item->stit_ConvertCalcRate) ?? "",
            'packed_stockValue' => $this->fsto_stockValue ?? "",
            'packePrice'        => $this->fstro_ItemSPincTax ?? 0,
            'fsto_pkdQty'       => $this->fsto_pkdQty ?? 0,
            'barcode_ERP'       => $this->erpID ?? "",
        ];
    }

    private function checkDecimals($value = "")
    {
        if($value)
        {
            $intVal = floor($value);
            return (($value - $intVal) == 0.0) ? (string)(int)$value : (string)$value;
        }
        return "0";
    }
}