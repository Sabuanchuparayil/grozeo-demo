<?php

namespace BackOffice\Http\Requests\Drivers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

use Illuminate\Validation\Rule;


class CreateVehicleRequest extends FormRequest
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
            "vehicle_number"    => 'required|string',
            "vehicle_type"      => [
                'required',
                Rule::exists('qugeo_vehicletype', 'vhty_id')
                ->where('vhty_Active', 1)
            ],
        ];
    }
}
