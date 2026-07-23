<?php

namespace BackOffice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use BackOffice\Rules\ValidSingleBoyOrder;

class ItemProceedRequest extends FormRequest
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
            'item_id' => 'required|integer',
            'barcode' => 'required|integer',           
            'boy_order_id' => [
                'required',
                'string',
                new ValidSingleBoyOrder
            ],
          //  'boy_order_id' => 'required|exists:retaline_godown_boy_orders,id',
            'scanned_count' => 'required|integer'
        ];
    }
}
