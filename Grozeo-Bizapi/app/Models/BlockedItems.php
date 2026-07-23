<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedItems extends Model
{
     /**
     * Table name of the Model
     *
     * @var string
     */
    protected $table = 'finascop_stock_blocked';
    /**
     * the Attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
