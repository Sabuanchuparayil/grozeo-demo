<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifyLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_customer_signup_verifiLog';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

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
    protected $primaryKey = 'veri_id';

}
