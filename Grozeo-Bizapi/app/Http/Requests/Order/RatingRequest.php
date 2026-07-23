<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class RatingRequest extends FormRequest
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
            'order_DeliveryRatingStar' => 'required|numeric|between:0,5',
            'order_DeliveryRatingComment' => 'nullable|string'
        ];
    }
}
