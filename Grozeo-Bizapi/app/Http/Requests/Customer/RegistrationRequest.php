<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationRequest extends FormRequest
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
            'mobile'    => 'required|max:16|unique:retaline_customer,cust_mobile',
            'email'     => 'required|email|max:50',
            'name'      => 'required|max:50',
            'password'  => 'nullable',
            'refCode'   => 'required'
        ];

    }
    public function messages()
    {
        return [
           'mobile.unique'  => 'Already Registered'
        ];
    }
}




