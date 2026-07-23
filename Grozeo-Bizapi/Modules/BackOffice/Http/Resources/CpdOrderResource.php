<?php

namespace BackOffice\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class CpdOrderResource extends Resource
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
          'item_id' => $this->stit_ID ?? 0,
          'count' => $this->bcod_Count ?? 0,
          'name' => $this->item->stit_SKU ?? "",
          'price' => $this->price->selling_price ?? 0,
          'image' => $this->image->image_thumb_url ?? "",
        ];
    }
}
