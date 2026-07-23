<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinascopState extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_state';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'st_ID';

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
