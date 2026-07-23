<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MstParentCategory extends Model
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
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'parent_category_id';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'created_by', 'created_on', 'updated_on', 'updated_by'
    ];


     /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function subcategories()
    {
        return $this->hasMany("App\Models\Categorys", "parent_category", "parent_category_id");
    }


}
