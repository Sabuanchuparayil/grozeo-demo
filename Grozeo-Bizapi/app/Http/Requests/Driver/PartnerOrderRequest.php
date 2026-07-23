<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class PartnerOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'quorIds'=>'required',
            'qugeobk_NO'=>'required',
            'br_id'=>'required',
            'handling_br_id'=>'required',
            'hdnVehicleId'=>'required',
            'type'=>'required',
        ];
    }
}

