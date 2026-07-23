<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductUnits;

class StockItemMaster extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_stock_itemmaster';

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
    protected $primaryKey = 'stit_ID';

     /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function mainImage()
    {
        return $this->hasMany('App\Models\StockItemImage','product_id','stit_ID');
    }

    public function additionalImage()
    {
        return $this->hasMany('App\Models\StockItemImage','product_id','stit_ID');
    }

    public function medicine()
    {
        return $this->belongsTo('App\Models\MedicineMaster','stit_ID','medicineMaster_id');
    }
    public function uniqueitem()
    {
        return $this->hasMany('App\Models\StockUniqueItem','fsi_item_id','stit_ID');
    }
    public function quantityUnit()
    {
        return $this->belongsTo(ProductUnits::class, "stit_unit", "unit_id");
    }

}
