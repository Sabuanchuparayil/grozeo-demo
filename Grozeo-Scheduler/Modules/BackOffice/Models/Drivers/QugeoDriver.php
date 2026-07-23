<?php

namespace BackOffice\Models\Drivers;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class QugeoDriver extends Authenticatable implements JWTSubject
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['d_ID'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'qugeo_driver';
    
    /**
    * The primary key associated with the table.
    *
    * @var string
    */
    protected $primaryKey = 'd_ID';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    /**
     * relationship to list all vehicles for each driver.
     *
     * @return array
     */
    public function vehicles()
    {
        return $this->hasMany(DriverVehicles::class, 'driver_id')->orderBy('status', 'DESC');
    }
}
