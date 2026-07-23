<?php

namespace App\Models;

use App\Models\StockItemImage;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class TransferOrderDetails extends Model
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
    protected $table = 'finascop_stock_transfer_order_details';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'fstod_id';

    public function item()
    {
        return $this->belongsTo(Item::class, 'fsto_ItemId', 'stit_ID');
    }

    public function image()
    {
        return $this->belongsTo(StockItemImage::class, 'fsto_ItemId', 'product_id');
    }

    public function price()
    {
        return $this->belongsTo(BranchInventory::class, 'fsto_ItemId', 'stit_ID');
    }

    public function barcodes()
    {
        return $this->hasMany(TransferOrderDetailsBarcodes::class, 'fstod_id');
    }
    public static function nextTransferOrderNo($brId)
    {
        $branches = Branch::select('branch_shortname')
        ->where('br_id', $brid)
        ->first();
       // //DB::enableQueryLog();
    $lastOrderNo = TransferOrderDetails::selectraw('right(fstr_uid,3)*1 as fstr_uid ')
        ->where('fstr_source',$brid)
        ->orderBy('fstr_id', 'desc')
        ->first();
    $lastOrderNo =  $lastOrderNo->fstr_uid??0;
    return $branches->branch_shortname . '-TOR-' . now()->format('ym') . '-' .
                str_pad(($lastOrderNo + 1), 3, '0', STR_PAD_LEFT);
//    return 'TOR' . now()->format('ymd') . $branches->br_key .
//        str_pad(($lastOrderNo+1), 3, '0', STR_PAD_LEFT) ;
    }
}
