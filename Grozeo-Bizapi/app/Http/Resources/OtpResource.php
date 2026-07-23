<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OtpResource extends JsonResource
{
    public function toArray($request)
    {
    
        return [
            'status' => 'ok',
            'msg' => 'Please use the OTP just send, to complete the registration',
            'default_currency' => '₹',
            'data' =>$this->resource
        ];
    }
}

