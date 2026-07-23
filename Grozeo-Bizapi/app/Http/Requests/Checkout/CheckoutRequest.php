<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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
            "order_method" => "required|integer|between:1,2",
            "selection" => "nullable|integer",
            "shipping_method" => "nullable|integer",
            //"branch_id" => "required|integer|exists:finascop_branch,br_ID",
            //"nearest_retailer_branch" => "required_if:order_method,1||integer|exists:finascop_branch,br_ID",
            "branch_id" => "nullable|integer",
            "nearest_retailer_branch" => "nullable|integer",
            "payment_mode"=> "required|integer",
            "splitorder"=> "nullable|integer",
            "prescription_id"=>'nullable|array ',
	        "portal_redirecturl" => 'nullable|string',
		"getwalletbalance"=> "nullable|integer",
        "order_group_id" => "nullable|integer",
        "order_id" => "nullable|integer"
        ];
    }
}
