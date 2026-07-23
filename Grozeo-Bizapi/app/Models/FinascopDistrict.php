<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinascopDistrict extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_district';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'dst_Id';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
