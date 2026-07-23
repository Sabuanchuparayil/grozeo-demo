<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VehiclesResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            "vehicle_id"    => $this->dv_id,
            "driver_id"     => $this->d_ID,
            "registration"  => $this->v_No,
            "is_primary"    => $this->is_primary,
            "vehicleType"   => [
                "type_id"           => $this->vehicleType->vhty_id,
                "type_name"         => $this->vehicleType->vhty_name,
                "type_icon"         => $this->vehicleType->vhty_Icon,
                "type_maxcapacity" => $this->vehicleType->vhty_MaxCapacity
            ],
        ];
    }
}
