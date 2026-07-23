<?php

namespace BackOffice\Models\RelationOfficer;

use Illuminate\Database\Eloquent\Model;

class BusinessAssociate extends Model
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
    protected $table = 'business_associate';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['baCreatedOn', 'baUpdatedOn'];
    const CREATED_AT = 'baCreatedOn';
    const UPDATED_AT = 'baUpdatedOn';
}
