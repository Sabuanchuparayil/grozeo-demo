<?php

namespace App\Http\Repositories\Payment;

class PaymentRepository
{
    public function __construct() {}

    public static function getAfterBookingDelayTime($date, $type)
    {
		if($type == 1)
        {
			$addseconds = config('b2cbooking.customer_cancel_till_seconds') ?? 120;
		}
        else
        {
			$addseconds = config('b2cbooking.delivery_process_start_at_seconds') ?? 240;
        }
        return date('Y-m-d H:i:s', $date + $addseconds);
    }

}