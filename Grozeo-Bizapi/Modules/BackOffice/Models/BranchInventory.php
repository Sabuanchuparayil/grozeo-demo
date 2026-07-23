<?php

namespace BackOffice\Models;

use BackOffice\Models\Item;
use Illuminate\Database\Eloquent\Model;

class BranchInventory extends Model
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
    protected $table = 'finascop_stock_branch_inventory';
    /**
     * Updated at field.
     * @var string
     */
    const UPDATED_AT = "updated_on";

    public function item()
    {
        return $this->belongsTo(Item::class, 'stit_id', 'stit_ID');
    }
}
