<?php

namespace BackOffice\Http\Requests;

use BackOffice\Rules\ValidBoyOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
class OrderProceedRequest extends FormRequest
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

            $input =  $this->all();
            
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
            'type' => 'required|in:0,1,2',
            'items' => 'required|array',
            'boy_order_id' => [
                'required',
                'string',
                new ValidBoyOrder
            ],
            'items.*.item_id' => 'required|integer',
            'items.*.barcodes' => 'required|array',
            'items.*.barcodes.*' => 'required|integer',
            'number_bags' => 'required|integer',
        ];
    }
}
