<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_stock_party';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'stpa_id';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    //public $incrementing = false;

}
