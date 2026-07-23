<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineAdvice extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mypha_medicine_advice';
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'medadv_createdOn', 'medadv_createdBy', 'medadv_updatedOn', 'medadv_updatedBy'
    ];


    public function safety_advice()
    {
        return $this->hasOne("App\Models\SafetyAdvice", "advice_id", "advice_id");
    }


    public function safety_precaution()
    {
        return $this->hasOne("App\Models\SafetyPrecaution", "precaution_id", "precaution_id");
    }




}
