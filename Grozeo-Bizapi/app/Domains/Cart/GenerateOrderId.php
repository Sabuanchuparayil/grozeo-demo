<?php

namespace App\Domains\Cart;

use App\Models\Order;
use App\Models\Customer;

class GenerateOrderId
{
    /**
     * Static function to generate a unique order Id.
     */
    public static function generate()
    {
        return (new static)->generateId();

    }

    /**
     * Generate a unique refferal code.
     *
     * @return string
     */
    public function generateId()
    {
        return 'PKT' .
                $this->getDate() .
                $this->addPaddingZeros(
                    $this->getLastNumber()
                );
    }

    /**
     * Get the last inserted number from db.
     *
     * @return int
     */
    public function getLastNumber()
    {
        $latest = Order::latest('order_id')->whereDate('created_at', today())->first();
        return $latest ?
                ((int) substr($latest->order_order_id, 9)) + 1 :
                1;
    }

    /**
     * Add padding zeros.
     *
     * @param string $refCode
     * @return string
     */
    public function addPaddingZeros($refCode)
    {
        return str_pad($refCode, 4, '0', STR_PAD_LEFT);
    }

    public function getDate()
    {
        return now()->format('dmy');
    }

    public function getMonthCode()
    {
        $monthCodes = [
            'A','B','C','D','E','F','G','H','I','J',
            'K','L','M','N','O','P','Q','R','S','T',
            'U','V','W','X','Y','Z'
        ];
        $month = now()->month;
        return $monthCodes[$month - 1];
    }

}
