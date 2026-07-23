<?php
namespace BackOffice\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use BackOffice\Models\RelationOfficer\BusinessCategory;

class CRMEnquiryRequest extends FormRequest
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
        if($validator->fails())
        {
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
            'business_name'     => 'nullable|string',
            'name'              => 'required|string',
            'phone'             => 'required|string',
            'email'             => 'required|email',
            'location'          => 'nullable|string',
            'address'           => 'nullable|string',
            'business_category' => 'nullable|numeric',
            'message'           => 'nullable|string',
            'source'            => 'required|exists:finascop_crm_source,crms_id'
        ];
    }
}
