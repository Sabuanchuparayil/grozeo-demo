<?php

namespace BackOffice\Http\Requests\Drivers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

use Illuminate\Validation\Rule;


class DriverAuthRequest extends FormRequest
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
            "userid"                => [
                'required',
                Rule::exists('qugeo_driver', 'd_Ph1')
                ->where('d_Active', 1)
            ],
            "usertype"              => 'required|integer|in:2',
            "password"              => [
                'required',
                Rule::exists('qugeo_driver', 'imeinumber')
                ->where('d_Active', 1)
            ],
        ];
    }
}
