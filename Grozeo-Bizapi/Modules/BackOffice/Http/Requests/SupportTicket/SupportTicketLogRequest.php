<?php
namespace BackOffice\Http\Requests\SupportTicket;

use Illuminate\Foundation\Http\FormRequest;

class SupportTicketLogRequest extends FormRequest
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
            'ticket_id'     => 'required|integer|exists:support_ticket,ticketId',
            'remarks'       => 'nullable|string',
            'file_name'     => 'required|string',
            'file_url'      => 'required|url',
            'created_by'    => 'nullable|integer'
        ];
    }
}
