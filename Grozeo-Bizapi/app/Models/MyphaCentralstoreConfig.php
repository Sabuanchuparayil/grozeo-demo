<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyphaCentralstoreConfig extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mypha_centralstore_config';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

}
