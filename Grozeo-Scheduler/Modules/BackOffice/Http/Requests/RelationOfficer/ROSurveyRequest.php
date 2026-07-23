<?php

namespace BackOffice\Http\Requests\RelationOfficer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class ROSurveyRequest extends FormRequest
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
            'crm_user_id'           => $this->crmUserID_Validation($this->type),
            'type'                  => 'required|in:contact,lead,prospect',
            'responses'             => 'required|filled',
            'responses.*.question'  => [
                'required',
                Rule::exists('crm_survey_questions', 'id')
                ->where('crm_type', $this->type)
            ],
            'responses.*.answer'    => 'required|exists:crm_survey_options,id'
        ];
    }
    private function crmUserID_Validation($type)
    {
        $roUser = auth_user();
        $outs[] = 'required';
        switch ($type) {
            case 'contact':
                $outs[] = Rule::exists('finascop_crm_contact', 'id')->where('crco_isActive', 1)->where('crco_CreatedBy', $roUser->id);
                break;
            case 'lead':
                $outs[] = Rule::exists('finascop_crm_lead', 'id')->where('crle_isActive', 1)->where('assignedRO', $roUser->id);
                break;
            case 'prospect':
                $outs[] = Rule::exists('finascop_crm_prospect', 'id')->where('crpr_isActive', 1)->where('assignedRO', $roUser->id);
                break;
            
            default:
                $outs[] = 'integer';
                $outs[] = 'gt:0';
                break;
        }
        return $outs;
    }
}
