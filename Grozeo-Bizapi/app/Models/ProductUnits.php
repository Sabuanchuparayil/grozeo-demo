<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnits extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mypha_unit';
    protected $primaryKey = 'unit_id';
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'created_on', 'created_by', 'updated_by', 'updated_on'
    ];
}
