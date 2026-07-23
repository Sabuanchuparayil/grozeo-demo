<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DriveBranchResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id"            => $this->br_ID,
            "name"          => $this->br_Name,
            "address1"      => $this->br_Address,
            "address2"      => $this->br_Address2,
            "address3"      => $this->br_Address3,
            "city"          => $this->br_City,
            "district"      => [
                "id"        => $this->district->dst_Id,
                "name"      => $this->district->dst_Name
            ],
            "state"         => [
                "id"        => $this->state->st_ID,
                "name"      => $this->state->st_name
            ],
            "storegroup"    => [
                "id"        => $this->storegroup->store_group_id,
                "name"      => $this->storegroup->store_group_name
            ]
        ];
    }
}
