<?php

namespace BackOffice\Models\RelationOfficer;

use Illuminate\Database\Eloquent\Model;
use BackOffice\Models\RelationOfficer\BusinessAssociate;

class AreaEntries extends Model
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
    protected $table = 'area_entries';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['areaCreatedOn', 'areaUpdatedOn'];
    const CREATED_AT = 'areaCreatedOn';
    const UPDATED_AT = 'areaUpdatedOn';

    
    public function businessAssociate()
    {
        return $this->belongsTo(BusinessAssociate::class, 'baId', 'id');
    }
}
