<?php

namespace BackOffice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferOrderAcceptRequest extends FormRequest
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

            $input = array_map('trim', $this->all());
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
            'action' => 'required|integer',
            'order_request_id' => 'required|integer',
        ];
    }
}
