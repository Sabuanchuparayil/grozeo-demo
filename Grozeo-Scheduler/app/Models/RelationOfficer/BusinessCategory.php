<?php

namespace App\Models\RelationOfficer;

use Illuminate\Database\Eloquent\Model;
use App\Models\RelationOfficer\BusinessAssociate;

class BusinessCategory extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['business_category_id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_business_category';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_on', 'updated_on'];
    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'updated_on';
}
