<?php

namespace BackOffice\Http\Requests\Drivers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

use Illuminate\Validation\Rule;


class DriveUpdateStatusRequest extends FormRequest
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
            "status"    => 'required|in:0,1',
            "latitude"  => 'required_if:status,1',
            "longitude" => 'required_if:status,1',
            "gcm_id"    => 'required_if:status,1'
        ];
    }
}
