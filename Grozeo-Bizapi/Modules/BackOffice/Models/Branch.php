<?php

namespace BackOffice\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_branch';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'br_ID';

    protected $hidden = ['location']; // Exclude the 'location' field

    public function cpd()
    {
        return $this->belongsTo(Branch::class, 'br_cpd');
    }
    public function on_off_time()
    {
        return $this->hasMany('BackOffice\Models\BranchTime','branch_id','br_ID');
    }

}
