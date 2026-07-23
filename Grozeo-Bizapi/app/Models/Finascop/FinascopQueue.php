<?php

namespace App\Models\Finascop;

use Illuminate\Database\Eloquent\Model;

class FinascopQueue extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_wallet_queue';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'waqu_id';

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