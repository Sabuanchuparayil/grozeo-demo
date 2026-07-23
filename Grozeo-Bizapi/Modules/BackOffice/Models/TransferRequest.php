<?php

namespace BackOffice\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Branch;

class TransferRequest extends Model
{
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
    protected $table = 'finascop_stock_transfer_request';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'fstr_id';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'fstr_createdOn';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'fstr_updatedOn';

    public function requestItems()
    {
        return $this->hasMany(TransferRequestItems::class, 'fstr_id');
    }

    public static function  nextTransferRequestNo($brid)
    {
        $branches = Branch::select('branch_shortname')
        ->where('br_id', $brid)
        ->first();
       // //DB::enableQueryLog();
        
        $lastOrderNo = TransferRequest::selectraw('right(fstr_uid,3)*1 as fstr_uid ')
                ->where('fstr_source',$brid)
                ->orderBy('fstr_id', 'desc')
                ->first();
//    $lastOrderNo = TransferRequest::selectraw('right(fstr_uid,3)*1 as fstr_uid ')
//        ->where('fstr_source',$brid)
//        ->WhereBetween('fstr_createdOn', [now()->format('y-m-d') . ' 00:00:00', now()->format('y-m-d') . ' 23:59:59'])
//        ->orderBy('fstr_id', 'desc')
//        ->first();
    $lastOrderNo =  $lastOrderNo->fstr_uid??0;
    return $branches->branch_shortname . '/TRQ/' . now()->format('ym') . '/' .
                str_pad(($lastOrderNo + 1), 3, '0', STR_PAD_LEFT);
//    return 'TRQ' . now()->format('ymd') . $branches->branch_shortname .
//        str_pad(($lastOrderNo+1), 3, '0', STR_PAD_LEFT) ;
    }

}
