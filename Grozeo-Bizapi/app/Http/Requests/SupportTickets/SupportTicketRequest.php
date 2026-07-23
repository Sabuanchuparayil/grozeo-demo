<?php

namespace App\Http\Requests\SupportTickets;

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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'unit'          => 'required|integer|exists:support_unit,id',
            'title'         => 'required|string',
            'description'   => 'required|string',
            'retailer_id'   => 'nullable|integer',
            'file'          => 'nullable|string'
        ];
    }
}
