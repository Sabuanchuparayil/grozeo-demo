<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MinimumMargin extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'minimum_margin_range';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'mm_id';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    //public $incrementing = false;

}
