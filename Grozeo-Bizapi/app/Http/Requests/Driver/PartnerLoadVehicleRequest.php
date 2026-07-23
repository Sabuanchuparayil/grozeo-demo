<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class PartnerLoadVehicleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'longitude' => 'required',
            'latitude' => 'required',
            'br_id' => 'required',
        ];
    }
}

