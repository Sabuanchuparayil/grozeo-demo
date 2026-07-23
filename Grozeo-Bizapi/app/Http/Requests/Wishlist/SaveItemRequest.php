<?php

namespace App\Http\Requests\Wishlist;

use Illuminate\Foundation\Http\FormRequest;

class SaveItemRequest extends FormRequest
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
            'product_id'        => 'required|exists:finascop_stock_itemmaster,stit_ID',
            'group_id'          => 'required|exists:finascop_stock_uniqueitem,fsi_uid',
            'branch_id'         => 'required|integer',
            'order_method'      => 'required',
            'branch_type_id'    => 'integer',
            'source'            => 'nullable|integer|in:1,2,3',
            'type'              => 'nullable|integer|in:0,1,2,3',
        ];
    }
}
