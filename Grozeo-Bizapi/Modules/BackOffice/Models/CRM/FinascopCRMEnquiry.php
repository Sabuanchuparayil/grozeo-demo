<?php

namespace BackOffice\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use BackOffice\Models\CRM\FinascopCRMSource;
use BackOffice\Models\RelationOfficer\ROContactType;
use BackOffice\Models\RelationOfficer\BusinessCategory;

class FinascopCRMEnquiry extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['crme_id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_crm_enquiry';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['crmm_CreatedOn', 'crmm_UpdatedOn'];
    const CREATED_AT = 'crmm_CreatedOn';
    const UPDATED_AT = 'crmm_UpdatedOn';

    public function source()
    {
        return $this->belongsTo(FinascopCRMSource::class, 'crms_id', 'crms_id');
    }
    public function contact_type()
    {
        return $this->belongsTo(FinascopCRMSource::class, 'crme_type', 'id');
    }
    public function businessCategory()
    {
        return $this->belongsTo(BusinessCategory::class, 'crmm_business_category', 'business_category_id');
    }
}
