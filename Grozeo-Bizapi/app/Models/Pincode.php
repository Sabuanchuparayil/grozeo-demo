<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pincode extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'postoffice';

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
    protected $primaryKey = 'psof_id';

    public function district()
    {
        return $this->belongsTo('App\Models\District','dst_id');
    }

    public function districtAndState()
    {
        return $this->district()
                    ->join('finascop_state', 'finascop_state.st_ID', '=', 'finascop_district.st_Id')
                    ->select('finascop_district.dst_Id', 'finascop_district.dst_Name', 'finascop_state.st_name');
    }

    public function state()
    {
        return $this->belongsTo('App\Models\State','pincode_state_id','state_id');
    }
}
