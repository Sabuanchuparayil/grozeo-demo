<?php

namespace BackOffice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;


class AgentStoresUpdateRequest extends FormRequest
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
            'brid' => 'required|numeric|min:1',
            'brname' => 'required|string',			
			'braddress' => 'required|string',
			'brdistrict' => 'required|numeric',
			'brstate' => 'required|numeric',
			'brcity' => 'required|string',
			'brpincode' => 'required|numeric',
			'brincharge' => 'required|string',
			'brphone' => 'required|string',
			'bremail' => 'required|string',
			'brfax' => 'required|string',
            'brstocklevel' => 'required|numeric',
			'brdefaultapibranch' => 'required|numeric|min:0|max:1',
			'brstoregroup' => 'required|numeric',
			'brlat' => 'required|numeric',
			'brlng' => 'required|numeric',			
        ];
    }
}
