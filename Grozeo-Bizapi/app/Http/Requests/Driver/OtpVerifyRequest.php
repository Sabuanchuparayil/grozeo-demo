<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class OtpVerifyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'mobile_number' => 'required|numeric',
            'otp' => 'required|numeric',
            'fcm_token'=>'required',
            'geocoords'=>'required'
        ];
    }
}
