<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class InstamojoModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_paymentgateway_instamojo';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}