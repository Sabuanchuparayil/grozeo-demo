<?php

namespace BackOffice\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class Item extends Model
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
    protected $table = 'finascop_stock_itemmaster';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'stit_ID';

    public function productCategory()
    {
        return $this->belongsTo(Category::class, 'product_category', 'sub_category_id');
    }

}
