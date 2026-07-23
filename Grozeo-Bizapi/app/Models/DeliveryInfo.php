<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryInfo extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_customer_delivery_info';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'deli_id';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'deli_created_at';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'deli_updated_at';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['deli_created_at', 'deli_updated_at'];

    public function delivery()
    {
        return $this->belongsTo(BrmPincode::class, 'deli_delivery_pin', 'pincode');
    }
    public function state()
    {
        return $this->belongsTo(State::class, 'deli_state', 'st_name');
    }

}
