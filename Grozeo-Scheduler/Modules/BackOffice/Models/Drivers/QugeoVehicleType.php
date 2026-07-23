<?php

namespace BackOffice\Models\Drivers;

use Illuminate\Database\Eloquent\Model;

class QugeoVehicleType extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['vhty_id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'qugeo_vehicletype';
    
    /**
    * The primary key associated with the table.
    *
    * @var string
    */
    protected $primaryKey = 'vhty_id';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    /* protected $dates = ['created_at', 'updated_at'];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at'; */
}
