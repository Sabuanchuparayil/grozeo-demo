<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class PaytmModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_paytm';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    
}
