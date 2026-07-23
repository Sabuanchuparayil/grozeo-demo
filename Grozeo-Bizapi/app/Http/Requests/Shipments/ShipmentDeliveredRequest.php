<?php

namespace App\Http\Requests\Shipments;

use Illuminate\Foundation\Http\FormRequest;

class ShipmentDeliveredRequest extends FormRequest
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
            "tracking_no"   => 'required',
            "status"        => 'required',
            "location"      => 'required',
            "pickup_date"   => 'required|date_format:Y-m-d H:i:s',
            "estimate_date" => 'required|date_format:Y-m-d H:i:s',
            "delivery_date" => 'required|date_format:Y-m-d H:i:s',
            "user_id"       => 'required'
        ];
    }
}
