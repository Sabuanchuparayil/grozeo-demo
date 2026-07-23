<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StockItemMaster;

class StockUniqueItem extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_stock_uniqueitem';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'fsi_uid';

     /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function itemMaster()
    {
        return $this->hasMany(StockItemMaster::class, 'stit_fsiuid', 'fsi_uid');
    }

}
