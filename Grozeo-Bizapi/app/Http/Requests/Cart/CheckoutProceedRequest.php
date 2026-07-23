<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutProceedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_id' => 'required|integer|exists:retaline_customer_order,order_id',
            'order_order_id' => 'required|string|exists:retaline_customer_order,order_order_id',
            'order_customer_id' => 'required|integer|exists:retaline_customer_order,order_customer_id',
            'total_amount' => 'required',
            'payment_mode' => 'required',
            'payment_gateway' => 'required_if:payment_mode,2',
        ];
    }
}
