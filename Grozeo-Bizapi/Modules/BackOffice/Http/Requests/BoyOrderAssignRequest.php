<?php

namespace BackOffice\Http\Requests;

use BackOffice\Rules\ValidOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class BoyOrderAssignRequest extends FormRequest
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

    

    /*protected function prepareForValidation() {

        $input = array_map('trim', $this->all());

    }*/

    public function withValidator($validator)
    {
        if($validator->fails()){

            $input =$this->all();
        }
    }




/*    public function all()
    {
        $input = parent::all();
        return parent::all();
    }*/

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
            'branch_id' => 'required|integer',
            'type'=>'nullable|string'
        ];
    }
}
