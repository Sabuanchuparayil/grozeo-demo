<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mypha_productsubcategory';

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
    protected $primaryKey = 'sub_category_id';

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
        return $this->belongsTo("App\Models\Categorys", "sub_category_id", "category_id");
    }

    public function product()
    {

        return $this->hasMany("App\Models\StockItemMaster","product_category", "sub_category_id");
    }


}
