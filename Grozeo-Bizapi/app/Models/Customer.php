<?php

namespace App\Models;

use App\Models\DeliveryInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
class Customer extends Authenticatable  implements JWTSubject
{
    use Notifiable;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_customer';

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
    protected $primaryKey = 'cust_id';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['cust_created_at', 'cust_updated_at'];

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'cust_created_at';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'cust_updated_at';



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

    public function cartEntries()
    {
        return $this->hasMany('App\Models\Cart', 'cart_customer_id');
    }

    public function orders()
    {
        return $this->hasMany('App\Models\Order', 'order_customer_id', 'cust_id');
    }

    public function deliveryInfo()
    {
        return $this->hasOne('App\Models\DeliveryInfo', 'deli_customer_id');
    }

    public function address()
    {
        return $this->hasMany('App\Models\DeliveryInfo', 'deli_customer_id');
    }

    public function primaryAddress()
    {
        return $this->hasOne(DeliveryInfo::class, 'deli_customer_id')
                    ->where('deli_is_primary', 1);
    }

    public function savedItems()
    {
        return $this->hasMany('App\Models\SavedItem', 'customer_id');
    }

    public function getCustAvatarAttribute($value)
    {
        return $value ? asset(Storage::url($value)) : null;
    }
}
