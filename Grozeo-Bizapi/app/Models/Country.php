<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_country';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_on', 'created_on'];
    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'created_on';

}
