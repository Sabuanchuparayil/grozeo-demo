<?php

namespace BackOffice\Models\RelationOfficer;

use Illuminate\Database\Eloquent\Model;
use BackOffice\Models\{
    BranchGroup,
    RelationOfficer\BusinessCategory,
    RelationOfficer\ROContactType
};

class ROFinascopProspects extends Model
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
    protected $table = 'finascop_crm_prospect';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['crpr_CreatedOn', 'crpr_UpdatedOn'];
    const CREATED_AT = 'crpr_CreatedOn';
    const UPDATED_AT = 'crpr_UpdatedOn';

    public function retailCategory()
    {
        return $this->belongsTo(BusinessCategory::class, 'retailCategory', 'business_category_id');
    }
    public function contactType()
    {
        return $this->belongsTo(ROContactType::class, 'crpr_type', 'id');
    }
    public function storegroup()
    {
        return $this->belongsTo(BranchGroup::class, 'storeGroupId', 'store_group_id');
    }
}
