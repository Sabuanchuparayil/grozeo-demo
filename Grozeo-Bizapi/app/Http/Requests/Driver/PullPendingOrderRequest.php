<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class PullPendingOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|numeric',
            'key' => 'required',
            'drivetype'=>'required|in:PICKUP,DELIVERY',
        ];
    }
}
