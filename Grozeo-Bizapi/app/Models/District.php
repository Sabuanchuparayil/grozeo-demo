<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'dst_Id';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_district';

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