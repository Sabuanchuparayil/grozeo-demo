<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class ConcludeOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'orderid' => 'required',
            'failed'=>'required',
            'failedreasonid'=>'nullable',
            'confirmationdetails'=>'nullable',
            'isVerifiedByOtp'=>'nullable',
            'return_items'=>'nullable',
            'ondel_payment_mode'=>'nullable',
            'ondel_payment_amount'=>'nullable',
            'ondel_refer_id'=>'nullable',
            'geocoords'=>'required',
        ];
    }

}
