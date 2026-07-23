<?php

namespace App\Http\Requests\Verify;

use Illuminate\Foundation\Http\FormRequest;

class VerificationRequest extends FormRequest
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
            'mobile'    => 'nullable|required_without:email|max:15',
            'email'     => 'nullable|required_without:mobile|email|max:50',
            'userID'    => 'nullable|integer',
            'otp'       => 'required|max:6',
        ];
    }
}
