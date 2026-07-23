<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class WalletCouponRequest extends FormRequest
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
           "order_id" => "required|integer|exists:retaline_customer_order,order_id",
           "coupon_code" => 'nullable|string',
           "use_wallet" => 'required|boolean',
        ];
    }
}
