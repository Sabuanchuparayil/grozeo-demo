<?php

namespace BackOffice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;


class AgentStoresGroupUpdateRequest extends FormRequest
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
//         id: 
// name: ABC
// store_group_primary_businessType: 1
// store_group_additional_businessType: 2,4,3
// status: 1
// tstamp: 20210717181216
        return [ 
            'id' => 'required|numeric|min:1',          
            'name' => 'required|string',			
			'primarybusinesstype' => 'required|numeric',
			'additionalbusinessType' => 'nullable|string',
        ];
    }
}
