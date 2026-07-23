<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use BackOffice\Models\BranchGroup;

class Branch extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_branch';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['location'];

    public $timestamps = false;

    public function state()
    {
        return $this->belongsTo(State::class, 'br_State', 'st_ID');
    }
    public function district()
    {
        return $this->belongsTo(District::class, 'br_District', 'dst_Id');
    }
    public function storegroup()
    {
        return $this->belongsTo(BranchGroup::class, 'br_storeGroup', 'store_group_id');
    }
    public function settings()
    {
        return $this->hasMany(BranchSettings::class, 'branch_id', 'br_ID');
    }
    public function deliveryRule()
    {
        return $this->hasOne(DeliveryRules::class, 'rdr_id', 'br_rdrIdExpress')->where('rdr_ruleFor', 1);
    }
}
