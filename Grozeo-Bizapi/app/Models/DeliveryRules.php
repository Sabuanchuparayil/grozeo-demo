<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryRules extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_delivery_rules';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function deliveryType()
    {
        return $this->belongsTo(FinanceDeliveryType::class, 'rdr_deliveryMode', 'id');
    }
}
