<?php

namespace BackOffice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

use Illuminate\Validation\Rule;
use BackOffice\Models\RelationOfficer\BusinessCategory;


class WalletRequest extends FormRequest
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

    public function withValidator($validator)
    {
        if($validator->fails()){

            $input = $this->all();
        }
    }
    

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer_id'   => 'required|integer|exists:retaline_customer,cust_id',
            'order_id'      => 'required|integer|exists:retaline_customer_order,order_id',
            'source_type'   => 'required|integer|in:1,2,3', // 1 - Sales return, 2- Sales, 3 - Promotion Credit
            'amount'        => 'required|numeric',
            'information'   => 'required|string',
            'barcode'       => 'required|integer'
        ];
    }
}
