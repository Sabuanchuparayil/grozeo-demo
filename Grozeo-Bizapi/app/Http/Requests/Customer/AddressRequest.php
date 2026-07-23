<?php

namespace App\Http\Requests\Customer;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
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
            'deli_delivery_pin' => 'nullable',
            'deli_branch_id'    => 'nullable',
            'deli_house_no'     => 'nullable',
            'deli_house_name'   => 'nullable',
            'deli_land_mark'    => 'nullable',
            'deli_house_name'   => 'nullable',
            'deli_post'         => 'nullable',
            'deli_city'         => 'nullable',
            'deli_state'        => 'nullable',
            'deli_address'      => 'nullable',
            'deli_address2'     => 'nullable',
            'deli_email'        => 'nullable',
            'deli_name'         => 'required|max:255',
            'deli_district'     => 'required|max:255',
            'deli_contact_no'   => 'required|max:16',
            'deli_latitude'     => 'required|numeric',
            'deli_longitude'    => 'required|numeric',
            'deli_type'         => 'required|max:100',
        ];
    }
}
