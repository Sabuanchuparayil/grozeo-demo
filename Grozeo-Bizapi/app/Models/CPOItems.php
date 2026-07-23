<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CPOItems extends Model
{
     /**
     * Table name of the Model
     *
     * @var string
     */
    protected $table = 'finascop_contractpo_products';
    /**
     * the Attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
