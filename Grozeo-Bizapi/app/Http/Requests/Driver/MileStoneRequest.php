<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class MileStoneRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'orderid' => 'required',
            'milestone'=>'required',
            'geocoords'=>'required'
        ];
    }
}
