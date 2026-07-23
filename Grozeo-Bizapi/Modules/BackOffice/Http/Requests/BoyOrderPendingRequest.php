<?php

namespace BackOffice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class BoyOrderPendingRequest extends FormRequest
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
          'sort'    => 'nullable|in:asc,desc',
          'filter'  => 'nullable|integer|exists:finascop_stock_transfer_order_status,fstos_id',
          'type'    => 'nullable|integer|in:0,1,2,3,4,5,6,7,8,9'
        ];
    }
}
