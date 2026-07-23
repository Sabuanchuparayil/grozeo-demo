<?php

namespace App\Domains\Customer;

use App\Models\Customer;

class GenerateReferralCode
{
    /**
     * Static function to generate a unique refferal code
     */
    public static function generate()
    {
        return (new static)->generateCode();
    }

    /**
     * Generate a unique refferal code.
     *
     * @return string
     */
    public function generateCode()
    {
        return $this->getMonthCode() .
                $this->getDate() .
                'RF' . $this->addPaddingZeros(
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
        $latest = Customer::latest('cust_id')->first();
        return $latest ?
                ((int) substr($latest->cust_ref_code, 5)) + 1 :
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
        return str_pad($refCode, 5, '0', STR_PAD_LEFT);
    }

    public function getDate()
    {
        return now()->format('d');
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
