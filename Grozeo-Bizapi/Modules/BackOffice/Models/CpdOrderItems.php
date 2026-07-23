<?php

namespace BackOffice\Models;

use App\Models\StockItemImage;
use Illuminate\Database\Eloquent\Model;

class CpdOrderItems extends Model
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
    protected $table = 'retaline_branch_outward_order_items';

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
    protected $primaryKey = 'bcod_id';

    public function item()
    {
        return $this->belongsTo(Item::class, 'stit_ID', 'stit_ID');
    }

    public function image()
    {
        return $this->belongsTo(StockItemImage::class, 'stit_ID', 'product_id');
    }

    public function price()
    {
        return $this->belongsTo(BranchInventory::class, 'stit_ID', 'stit_ID');
    }

    public function barcodes()
    {
        return $this->hasMany(CpdOrderItemBarcodes::class, 'bcod_id');
    }
}
