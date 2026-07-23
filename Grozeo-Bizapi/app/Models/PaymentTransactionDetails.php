<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransactionDetails extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    const CREATED_AT = 'roop_requestdatetime';
    const UPDATED_AT = 'roop_responsedatetime';

    protected $table = 'retaline_customer_onlinepayment_details';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    
}
