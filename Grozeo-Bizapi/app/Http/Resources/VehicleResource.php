<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'status' => 'ok',
            'msg' => 'Success',
            'data' => [
                'id' => $this->v_ID,
                'regno' => $this->v_no,
                'v_type' => $this->vhty_id,
                'vhty_name' => $this->vhty_name,
            ],
            'cleanlogout'=>true
        ];
    }
}
