<?php

namespace BackOffice\Models\RelationOfficer;

use Illuminate\Database\Eloquent\Model;
use BackOffice\Models\RelationOfficer\BusinessCategory;
use BackOffice\Models\RelationOfficer\ROContactType;

class ROFinascopContacts extends Model
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
    protected $table = 'finascop_crm_contact';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['crco_CreatedOn', 'crco_UpdatedOn'];
    const CREATED_AT = 'crco_CreatedOn';
    const UPDATED_AT = 'crco_UpdatedOn';

    public function retailCategory()
    {
        return $this->belongsTo(BusinessCategory::class, 'retailCategory', 'business_category_id');
    }
    public function contactType()
    {
        return $this->belongsTo(ROContactType::class, 'crco_type', 'id');
    }
}
