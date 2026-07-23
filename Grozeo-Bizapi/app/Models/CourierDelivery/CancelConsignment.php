<?php

namespace App\Models\CourierDelivery;

use Illuminate\Database\Eloquent\Model;

class CancelConsignment extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cancel_consignment';
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
/**
 * The primary key associated with the table.
 *
 * @var string
 */
protected $primaryKey = 'id';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_date', 'updated_date'];
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
}
