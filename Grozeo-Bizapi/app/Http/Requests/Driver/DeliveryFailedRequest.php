<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Driver\DeliveryStartRequest;

class DeliveryFailedRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'failure_id'    => 'required|exists:qugeo_deliverystatus,dls_ID'
        ];
        return array_merge(
            (new DeliveryStartRequest)->rules(),
            $rules
        );
    }

}
