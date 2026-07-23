<?php

namespace App\Http\Requests\CategoryScreen;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CategoryScreenRequest extends FormRequest
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

            'requested_id' => 'nullable|max:100',
            'branch_id'=>'required',
            'order_method'=>'required',
            'sort'=>'nullable|array',
            'sort.*.price' => 'nullable|string',
            'filter'=>'nullable|array',
            'filter.*.category'=>'nullable|array',
            'filter.*.brands' => 'nullable|array',
            'filter.*.price_range' => 'nullable|array',	
            'virtualcategoryid' => 'nullable|integer',
            'category_level' => 'nullable|integer'

        ];
    }
}
