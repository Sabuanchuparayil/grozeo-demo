<?php

namespace App\Models;

use BackOffice\Models\Item;
use App\Models\BlockedItems;
use BackOffice\Models\BranchInventory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItemBarcodes extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_customer_order_items_barcodes';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'coib_id';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

}
