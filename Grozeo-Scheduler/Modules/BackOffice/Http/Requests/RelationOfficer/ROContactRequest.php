<?php

namespace BackOffice\Http\Requests\RelationOfficer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

use Illuminate\Validation\Rule;
use BackOffice\Models\RelationOfficer\BusinessCategory;


class ROContactRequest extends FormRequest
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
        $ids = BusinessCategory::where([['status', 1], ['store_group_id', 0]])->pluck('business_category_id');
        $ids = $ids->toArray();
        return [
            'store_name'        => 'required|string',
            'address_status'    => 'required|integer|in:1,2',   // 1:manual, 2:google
            'google_address'    => 'nullable|string',
            'latitude'          => 'nullable|string',
            'longitude'         => 'nullable|string',
            'address_1'         => 'required|string',
            'address_2'         => 'nullable|string',
            'country'           => 'required|string',
            'route'             => 'nullable|string',
            'locality'          => 'nullable|string',
            'place'             => 'nullable|string',
            'post_code'         => 'nullable|string',
            'phone'             => 'nullable|string',
            'email'             => 'nullable|email',
            'contact_person'    => 'required|string',
            'contact_number'    => 'nullable|string',
            'retailer_category' => [
                'required',
                Rule::in($ids)
            ],
            'contact_type'      => 'required|array',
            'is_others'         => 'required||integer|in:0,1',
            'remarks'           => 'nullable|string',
        ];
    }
}
