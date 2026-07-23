<?php

namespace App\Http\Requests\Driver;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AddVehicleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'registration'          => 'required|string',
            'type'                  => [
                'required',
                Rule::exists('qugeo_vehicletype', 'vhty_id')->where(function ($query) {
                    return $query->where('vhty_Active', 1);
                }),
            ],
            'location'              => 'required|array',
            'location.latitude'     => 'required|numeric|between:-90,90',
            'location.longitude'    => 'required|numeric|between:-180,180'
        ];
        
    }

}