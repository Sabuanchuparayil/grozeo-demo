<?php

namespace BackOffice\Models\RelationOfficer;

use Illuminate\Database\Eloquent\Model;
use BackOffice\Models\RelationOfficer\BusinessCategory;
use BackOffice\Models\RelationOfficer\ROContactType;
use BackOffice\Models\RelationOfficer\ROSurveyResponses;

class ROFinascopLeads extends Model
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
    protected $table = 'finascop_crm_lead';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['crle_CreatedOn', 'crle_UpdatedOn'];
    const CREATED_AT = 'crle_CreatedOn';
    const UPDATED_AT = 'crle_UpdatedOn';

    public function retailCategory()
    {
        return $this->belongsTo(BusinessCategory::class, 'retailCategory', 'business_category_id');
    }
    public function contactType()
    {
        return $this->belongsTo(ROContactType::class, 'crle_type', 'id');
    }
    public function survey()
    {
        return $this->hasMany(ROSurveyResponses::class, 'crm_user_id', 'id')->where('crm_user_type', 'lead');
    }
}
