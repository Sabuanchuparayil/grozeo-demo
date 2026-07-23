<?php
namespace BackOffice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BoyBranchOrderRequest extends FormRequest
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

            $input =$this->all();
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
            'date'      => 'required|array',
            'date.from' => 'nullable|date|date_format:Y-m-d',
            'date.to'   => 'nullable|date|date_format:Y-m-d'
        ];
    }
}
