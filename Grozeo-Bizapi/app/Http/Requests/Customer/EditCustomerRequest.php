<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class EditCustomerRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            
            'mobile' => 'required|max:15',
            'email' => 'required|email|max:50',
            'name' => 'required|max:50',
            'pincode' => 'nullable',
            'house_name' => 'required|max:100',
            // 'land_mark' => 'required|max:100',
            'land_mark' => 'nullable',
            'latitude'=>'nullable',
            'longitude'=>'nullable',
            'house_no' => 'nullable',
            'post' => 'nullable',
            'city' => 'nullable',
            'state' => 'nullable',
        ];
    }
}
