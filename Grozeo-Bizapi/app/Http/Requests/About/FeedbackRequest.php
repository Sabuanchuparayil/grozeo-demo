<?php

namespace App\Http\Requests\About;

use Illuminate\Foundation\Http\FormRequest;

class FeedbackRequest extends FormRequest
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
            'fb_mobile' => 'required',
            'fb_email' => 'required|email',
            'fb_comments' => 'required|string',
        ];
    }
}
