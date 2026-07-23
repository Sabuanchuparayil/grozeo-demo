<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class PolledOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'order_id'              => 'required|exists:retaline_customer_order,order_order_id',
            'location'              => 'required|array',
            'location.latitude'     => 'required|numeric|between:-90,90',
            'location.longitude'    => 'required|numeric|between:-180,180'
        ];
    }
}
