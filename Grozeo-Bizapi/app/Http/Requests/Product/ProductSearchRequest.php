<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductSearchRequest extends FormRequest
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
            'product_name' => ['nullable', 'string', 'max:255']
        ];
    }

    /**
     * Sanitize search input before validation
     */
    protected function prepareForValidation()
    {
        if ($this->has('product_name'))
        {
            $this->merge([
                'product_name' => $this->sanitizeSearch($this->input('product_name')),
            ]);
        }
    }

    /**
     * Custom sanitizer for search inputs
     */
    private function sanitizeSearch($input): string
    {
        if(is_null($input)) { return ""; }
        
        // Remove SQL keywords
        $input = preg_replace('/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|ALTER|CREATE|TRUNCATE|REPLACE|EXEC)\b/i', '', $input);

        // Remove special characters (quotes, symbols, emoji, etc.)
        $input = preg_replace("/[^a-zA-Z0-9\s]/u", '', $input);

        // Normalize spaces
        return trim(preg_replace('/\s+/', ' ', $input));
    }
}
