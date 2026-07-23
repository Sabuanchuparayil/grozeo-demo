<?php

namespace App\About;

use Illuminate\Database\Eloquent\Model;

class CallCenter extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'app_callcenter';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'tid';

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
}
