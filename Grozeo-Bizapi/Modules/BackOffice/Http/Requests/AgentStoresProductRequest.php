<?php

namespace BackOffice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;


class AgentStoresProductRequest extends FormRequest
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
            'store' => 'required',
            'count' => 'required|numeric|min:1|max:1000',
            'catlevel' => 'required|numeric|min:0|max:3',
            'brand' => 'required|numeric',
            'category' => 'required|numeric'
        ];
    }
}
