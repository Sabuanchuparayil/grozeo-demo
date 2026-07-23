<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
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
            'cart_group_id' => 'required|exists:finascop_stock_uniqueitem,fsi_uid',
            'cart_product_id' => 'required|exists:finascop_stock_itemmaster,stit_ID',
            'cart_order_qty' => 'required|integer',
            'cart_branch_id' => 'required|integer',
            'type'=>'required',
            'order_method'=>'required',
            'branch_type_id' => 'nullable|integer',
        ];
    }

}
