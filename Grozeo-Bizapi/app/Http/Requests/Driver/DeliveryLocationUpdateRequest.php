<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Driver\DeliveryStartRequest;

class DeliveryLocationUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return (new DeliveryStartRequest)->rules();
    }

}
