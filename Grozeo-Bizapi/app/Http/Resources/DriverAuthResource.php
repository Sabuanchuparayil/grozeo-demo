<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DriverAuthResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'status' => 'ok',
            'msg' => 'Success',
            'data' => [
                'name' => $this->d_Name,
                'address1' => $this->d_Add1,
                'address2' => $this->d_Add2,
                'address3' => $this->d_Add3,
                'licno' => $this->d_licence,
                'licexpon' => $this->d_licenceexpairy,
                'auth_token'=>$this->token
            ],
        ];
    }
}
