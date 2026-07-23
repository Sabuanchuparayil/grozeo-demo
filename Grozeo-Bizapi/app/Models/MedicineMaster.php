<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineMaster extends Model
{
    //
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mypha_medicineMaster';
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'medicineMaster_id';


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['medicine_createdBy','medicine_createdOn','medicine_updatedBy','medicine_updatedOn'];


    public function medicinecontent()
    {
        return $this->hasMany("App\Models\MedicineContent", "medicineContent_id", "medicineContent_id");
    }

    public function medicinetype()
    {
        return $this->hasMany("App\Models\MedicineType", "medicine_type_id", "medicine_type");
    }


    public function manufacture()
    {
        return $this->hasOne("App\Models\ManuFacture", "manufacture_id", "medicine_manufacture");

    }

    public function composition()
    {
        return $this->hasOne("App\Models\MedicineComposition", "composition_id", "medicine_composition");

    }
    public function alternateBrand()
    {
        return $this->hasMany("App\Models\MedicineMaster", "medicine_composition", "medicine_composition");
    }
}
