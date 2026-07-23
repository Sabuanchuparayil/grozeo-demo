<?php

namespace BackOffice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferOrderAcceptedRequest extends FormRequest
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
            'order_pk_id' => 'required|exists:finascop_stock_transfer_order,fsto_id',
        ];
    }
}
