<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\B2bOrderItemBarcodes;

class B2bOrderItems extends Model
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
    protected $table = 'retaline_B2B_SalesOrderDetails';

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
    protected $primaryKey = 'b2bso_itemid';

    public function barcodes()
    {
        return $this->hasMany(B2bOrderItemBarcodes::class, 'bbsd_id');
    }

    
}
