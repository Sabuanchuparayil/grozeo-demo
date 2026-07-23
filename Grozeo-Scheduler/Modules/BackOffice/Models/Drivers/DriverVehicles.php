<?php

namespace BackOffice\Models\Drivers;

use Illuminate\Database\Eloquent\Model;
use BackOffice\Models\Drivers\{
    QugeoDriver,
    QugeoVehicleType
};

class DriverVehicles extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'driver_vehicles';
    
    /**
    * The primary key associated with the table.
    *
    * @var string
    */
    protected $primaryKey = 'id';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Relationship to describe the vehicle type for each vehicle.
     *
     * @var array
     */
    public function vehicleType()
    {
        return $this->belongsTo(QugeoVehicleType::class, 'vehicle_type', 'vhty_id');
    }
    /**
     * Relationship to describe the assigned driver for each vehicle.
     *
     * @var array
     */
    public function driver()
    {
        return $this->belongsTo(QugeoDriver::class, 'driver_id', 'd_ID');
    }
}
