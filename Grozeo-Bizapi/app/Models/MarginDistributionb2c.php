<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MarginDistributionb2c extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_margindistributions';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'bmd_id';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    //public $incrementing = false;

}
