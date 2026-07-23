<?php

namespace BackOffice\Models;

use App\Models\StockItemImage;
use Illuminate\Database\Eloquent\Model;

class TransferOrderDetailsBarcodes extends Model
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
    protected $table = 'finascop_stock_transfer_order_details_barcodes';

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
    protected $primaryKey = 'fstob_id';

}
