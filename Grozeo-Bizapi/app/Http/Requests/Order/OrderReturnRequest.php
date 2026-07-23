<?php

namespace App\Http\Requests\Order;

use App\Rules\OrderBelongsToCustomer;
use Illuminate\Foundation\Http\FormRequest;

class OrderReturnRequest extends FormRequest
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
            'order_id' => [
                'required',
                new OrderBelongsToCustomer,
            ],
            'reason' => 'required|string|max:500',
        ];
    }
}
