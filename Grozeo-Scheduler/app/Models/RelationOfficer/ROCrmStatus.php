<?php

namespace App\Models\RelationOfficer;

use Illuminate\Database\Eloquent\Model;

class ROCrmStatus extends Model
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
    protected $table = 'finascop_crm_status';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['crmu_CreatedOn', 'crmu_UpdatedOn'];
    const CREATED_AT = 'crmu_CreatedOn';
    const UPDATED_AT = 'crmu_UpdatedOn';
}
