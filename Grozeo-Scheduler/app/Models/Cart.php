<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\StockUniqueItem;

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

    public function item()
    {
        return $this->belongsTo(StockUniqueItem::class, 'cart_group_id', 'fsi_uid');
    }
}
