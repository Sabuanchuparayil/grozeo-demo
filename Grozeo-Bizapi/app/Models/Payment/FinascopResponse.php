<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class FinascopResponse extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'onlinebooking_finascop_response';
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
   
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'onfin_id';
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}