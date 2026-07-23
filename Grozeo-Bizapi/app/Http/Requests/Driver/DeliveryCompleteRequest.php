<?php
namespace App\Http\Requests\Driver;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Driver\DeliveryStartRequest;

class DeliveryCompleteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            // delivery verification
            'verification'              => 'required|array',
            'verification.signature'    => 'required_without_all:verification.photo,verification.otp|url|nullable',
            'verification.photo'        => 'required_without_all:verification.signature,verification.otp|url|nullable',
            'verification.otp'          => [
                'required_without_all:verification.signature,verification.photo',
                'nullable',
                Rule::exists('qugeo_order', 'quor_DeliverySMS')->where('quor_RefNo', $this->input('order_id'))
            ],
            // payment mode
            'payments'                  => 'required|array',
            'payments.mode'             => 'required|in:-1,0,1',
            'payments.amount'           => [
                'required_if:payments.mode,0',
                'numeric',
                'nullable',
                function ($attribute, $value, $fail)
                {
                    $orderAmount = DB::table('retaline_customer_order')
                        ->where('order_order_id', $this->input('order_id'))
                        ->whereIn('payment_mode', [1, 4])
                        ->value('order_amount_payable');
                    if ($orderAmount === NULL)
                    {
                        $fail('Order not found.');
                    }
                    elseif((float)$value !== (float)$orderAmount)
                    {
                        $fail("The {$attribute} is invalid.");
                    }
                }
            ],
            'payments.reference_id'     => 'required_if:payments.mode,1|string|nullable'
        ];

        return array_merge(
            (new DeliveryStartRequest)->rules(),
            $rules
        );
    }
    public function messages()
    {
        return [
            'verification.otp.exists'       => 'Invalid Otp.',
            'verification.photo.url'        => 'Invalid photo.',
            'verification.signature.url'    => 'Invalid signature.',
            'payments.amount.numeric'       => 'Invalid amount.',
            'payments.mode.in'              => 'Invalid payment mode.'
        ];
    }

}
