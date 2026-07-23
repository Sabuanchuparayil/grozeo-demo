<?php

namespace BackOffice\Models;

use App\Models\StockItemImage;
use Illuminate\Database\Eloquent\Model;

class TransferRequestItems extends Model
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
    protected $table = 'finascop_stock_transfer_request_details';

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
    protected $primaryKey = 'fstrd_id';

    public function item()
    {
        return $this->belongsTo(Item::class, 'fstr_ItemId', 'stit_ID');
    }

    public function image()
    {
        return $this->belongsTo(StockItemImage::class, 'fstr_ItemId', 'product_id');
    }

    public function price()
    {
        return $this->belongsTo(BranchInventory::class, 'fstr_ItemId', 'stit_ID');
    }

}
