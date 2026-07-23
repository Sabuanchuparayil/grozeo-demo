<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UploadPrescription;

class prescriptiomMedicineMap extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mypha_prescriptiom_medicine_map';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'pmm_id';

    public function prescription()
    {
        return $this->belongsTo(UploadPrescription::class, 'prescription_id', 'id');
    }
}
