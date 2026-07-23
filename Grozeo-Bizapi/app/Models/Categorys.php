<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorys extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mypha_productcategory';

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
    protected $primaryKey = 'category_id';

/**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'created_on', 'created_by', 'updated_by', 'updated_on'
    ];


    public function subcategory()
    {
        return $this->hasMany("App\Models\Category", "main_category", "category_id");
    }
}
