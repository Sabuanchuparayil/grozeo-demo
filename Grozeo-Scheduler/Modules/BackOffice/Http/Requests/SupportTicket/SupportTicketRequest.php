<?php
namespace BackOffice\Http\Requests\SupportTicket;

use Illuminate\Foundation\Http\FormRequest;

class SupportTicketRequest extends FormRequest
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
            'support_type'  => 'required|integer|exists:support_type,typeId',
            'phone'         => 'required|string',
            'email'         => 'required|email',
            'name'          => 'required|string',
            'title'         => 'required|string',
            'description'   => 'required|string',
            'support_unit'  => 'nullable|integer',
            'file_name'     => 'nullable|string',
            'file_url'      => 'nullable|url',
            'created_from'  => 'nullable|integer',
            'created_by'    => 'nullable|integer'
        ];
    }
}
