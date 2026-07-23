<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class EasebuzzModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_paymentgateway_easebuzz';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
}