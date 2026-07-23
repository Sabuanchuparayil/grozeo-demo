<?php

namespace BackOffice\Models;

use Illuminate\Database\Eloquent\Model;

class TransferOrderStatus extends Model
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
    protected $table = 'finascop_stock_transfer_order_status';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'fstos_id';
}
