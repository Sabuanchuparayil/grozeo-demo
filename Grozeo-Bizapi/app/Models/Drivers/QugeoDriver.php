<?php
 
namespace App\Models\Drivers;
 
use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
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

    protected  $fillable=['last_access_at', 'd_otp', 'd_otpvalidtill', 'gcmregstid', 'imeinumber', 'd_apikey'];

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
    public function vehicles()
    {
        return $this->hasMany(DriverVehicles::class, 'd_ID')->where('v_active', 1);
    }
    public function primaryVehicle()
    {
        return $this->hasOne(DriverVehicles::class, 'd_ID')->select('dv_id', 'd_ID', 'v_ID', 'v_No', 'lastused', 'vhty_id', 'is_primary')->where('v_active', 1)->where('is_primary', 1);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'br_id', 'br_ID');
    }
}