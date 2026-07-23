<?php

namespace App\Models\RelationOfficer;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\RelationOfficer\AreaEntries;
use App\Models\RelationOfficer\ROFinascopContacts;
use App\Models\RelationOfficer\BusinessAssociate;

class ROUser extends Authenticatable implements JWTSubject
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
    protected $table = 'relationship_officer';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['otp_generated_at', 'roCreatedOn', 'roUpdatedOn'];
    const CREATED_AT = 'roCreatedOn';
    const UPDATED_AT = 'roUpdatedOn';

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

    public function contacts()
    {
        return $this->hasMany(ROFinascopContacts::class, 'crco_CreatedBy');
    }
    public function areaEntries()
    {
        return $this->belongsTo(AreaEntries::class, 'roArea', 'id');
    }
    public function ba()
    {
        return $this->belongsTo(BusinessAssociate::class, 'roBusAssociate', 'id');
    }
}
