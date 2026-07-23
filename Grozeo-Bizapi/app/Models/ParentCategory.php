<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentCategory extends Model
{


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mypha_productparent_category';
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
    protected $primaryKey = 'parent_category_id';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_by', 'created_on', 'updated_on', 'updated_by'
    ];


    /**
     * Undocumented variable
     *
     * @var boolean
     */
    public $timestamps = false;

    public function subcategories()
    {
        return $this->hasMany("App\Models\SubCategory", "parent_category", "parent_category_id");
    }
}
