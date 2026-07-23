<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class RazorpayModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_paymentgateway_razorpay';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
