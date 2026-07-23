<?php

namespace BackOffice\Models;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\SalesOrderNumbering;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model {

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'B2CSalesOrder';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'createdon';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'updatedon';

    public function salesorderDetails() {
        return $this->hasMany(SalesOrderDetails::class, 'bcso_id');
    }

    public static function nextSalesOrderNo($brid) {
        $branches = Branch::select('branch_shortname')
                ->where('br_id', $brid)
                ->first();
        // //DB::enableQueryLog();
        /* $lastOrderNo = SalesOrder::selectraw('right(SONumber,3)*1 as SONumber ')
                ->where('bcso_br_ID', $brid)
                ->orderBy('id', 'desc')
                ->first();
        $lastOrderNo = $lastOrderNo->SONumber??0; */
        $numbering = SalesOrderNumbering::create();
        $numbering = $numbering ? $numbering->id : 1;
        return $branches->branch_shortname .'/SOR/' . now()->format('ym') . '/'.
                str_pad($numbering, 3, '0', STR_PAD_LEFT);
    }

}
