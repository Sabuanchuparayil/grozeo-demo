<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\MerchantSettlementNumbering;

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
        $date = date('dmy');
        $query = "CALL SettlementNumbering('{$prefix}', '{$date}')";
        $data = DB::select($query);
        return @$data[0]->settlement_id;
    }
}
