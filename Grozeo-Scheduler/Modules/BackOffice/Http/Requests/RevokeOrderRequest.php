<?php

namespace BackOffice\Http\Requests;

use BackOffice\Rules\ValidOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class RevokeOrderRequest extends FormRequest
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
            'order_pk_id' => [
                'required',
                'integer',                
                new ValidOrder
            ],
            'boy_id' => 'nullable|integer',
        ];
    }
}
