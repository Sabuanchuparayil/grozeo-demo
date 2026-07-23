<?php

namespace BackOffice\Rules;

use App\Models\Order;
use BackOffice\Models\BoyOrder;
use BackOffice\Models\CpdOrder;
use BackOffice\Status\BoyOrderStatus;
use BackOffice\Status\CpdOrderStatus;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;
class ValidBoyOrder implements Rule
{
    protected $message;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if($value == "-10") 
            return true;
        $order = BoyOrder::find($value);

        if (is_null($order)) {
            $this->message = 'Invalid boy order id';
            return false;
        }

        if ($order->is_cpd == 1) {
            $cpdOrder = CpdOrder::where('order_no', $order->order_id)->first();

            if ($cpdOrder && $cpdOrder->order_status == CpdOrderStatus::EXPIRED) {
                $this->message = 'Cpd order expired';
                return false;
            }
        }

        // if ($order->status == BoyOrderStatus::REVOKED) {
        //     $this->message = 'The order was revoked';
        //     return false;
        // }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
