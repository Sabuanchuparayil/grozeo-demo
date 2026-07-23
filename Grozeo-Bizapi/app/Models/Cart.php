<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_cart';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at',
    ];

    public function product()
    {

        return $this->belongsTo('App\Product', 'cart_product_id', 'product_id');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\StockUniqueItem', 'cart_group_id', 'fsi_uid');
    }
}
