<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class LiveOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'msgid' => 'required',
            'hasaccepted'=>'required|boolean',
            'fcm_token'=>'required'
        ];
    }
}
