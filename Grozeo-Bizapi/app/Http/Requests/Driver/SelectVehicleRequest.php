<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class SelectVehicleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ishired' => 'required',
            'vehicleid' => 'required|integer',
            'vehicleregno' => 'required|string',
            'vehicletype' => 'required|integer',
            'geocoords' => 'required',
        ];
    }
}
