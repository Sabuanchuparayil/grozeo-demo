<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StockItemImage;
use App\Models\StockUniqueItem;

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
       return $this->hasMany(StockItemImage::class, 'product_id', 'stit_ID');
    }
    public function additionalImage()
    {
      return $this->hasMany(StockItemImage::class, 'product_id', 'stit_ID');
    }
    public function uniqueitem()
    {
        return $this->hasMany(StockUniqueItem::class, 'fsi_item_id', 'stit_ID');
    }
}
