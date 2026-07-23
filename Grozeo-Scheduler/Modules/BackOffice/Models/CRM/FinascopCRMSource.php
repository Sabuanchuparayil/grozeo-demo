<?php

namespace BackOffice\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class FinascopCRMSource extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['crms_id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_crm_source';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['crms_CreatedOn', 'crms_UpdatedOn'];
    const CREATED_AT = 'crms_CreatedOn';
    const UPDATED_AT = 'crms_UpdatedOn';
}
