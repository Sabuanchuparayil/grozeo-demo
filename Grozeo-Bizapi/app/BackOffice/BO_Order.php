<?php

namespace App\BackOffice;

use App\BackOffice\BO_OrderItem;
use Illuminate\Database\Eloquent\Model;

class BO_Order extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_table';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'order_auto_id';

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

    public function orderItems()
    {
        return $this->hasMany(BO_OrderItem::class, 'order_details_order_id');
    }
}
