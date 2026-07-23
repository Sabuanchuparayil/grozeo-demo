<?php

namespace BackOffice\Models;

use Illuminate\Database\Eloquent\Model;
use BackOffice\Models\MerchantSettlementNumbering;

class MerchantSettlements extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'merchant_settlements';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public static function merchantSettlementNumbering()
    {
        $prefix = "GMS";
        $date = date('ymd');
        $numbering = MerchantSettlementNumbering::create();
        $number = $numbering ? $numbering->id : 1;
        $lastNumber = (string)str_pad($number, 8, '0', STR_PAD_LEFT);

        $numbPrefix = $lastNumber[0].$lastNumber[1];
        $lastNumberOut = substr($lastNumber, 2);

        $prefixStr = static::getLetterSequence($numbPrefix);
        return $prefix.$date.$prefixStr.$lastNumberOut;
    }
    private static function getLetterSequence($number)
    {
        $letters = "";
        while ($number >= 0)
        {
            $letters = chr($number % 26 + ord('A')).$letters;
            $number = intval($number / 26, 10) - 1;
        }
        return $letters;
    }
}
