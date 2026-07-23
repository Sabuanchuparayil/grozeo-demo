<?php

namespace App\Models\Drivers;
use Illuminate\Database\Eloquent\Model;
use App\Models\Drivers\{
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
    protected $guarded = ['dv_id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'qugeo_drivervehicle';
    
    /**
    * The primary key associated with the table.
    *
    * @var string
    */
    protected $primaryKey = 'dv_id';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    public $timestamps = false;
    protected $dates = ['created_at', 'updated_at'];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable=['d_ID','v_ID','v_No','lastused','vhty_id','is_primary'];

    /**
     * Relationship to describe the vehicle type for each vehicle.
     *
     * @var array
     */
    public function vehicleType()
    {
        return $this->belongsTo(QugeoVehicleType::class, 'vhty_id', 'vhty_id');
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
