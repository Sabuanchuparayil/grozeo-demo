<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineComposition extends Model
{
    //
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mypha_composition';
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
    protected $primaryKey = 'composition_id';


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['created_on','created_by','updated_on','updated_by'];



    public function subcategory()
    {
        return $this->hasMany("App\Models\MedicineSubCategory", "subCategory_id", "composition_id");

    }
   
}
