<?php

namespace BackOffice\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class B2bOrderResource extends Resource
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
			'item_id' => $this->b2bso_itemid ?? 0,
			'count' => $this->b2bso_itemqty ?? 0,
			'name' => $this->b2bso_itemname ?? "",
			'price' => $this->b2bso_netamount ?? 0,
			'image' => $this->image_thumb_url ?? "",
        ];
    }
}
