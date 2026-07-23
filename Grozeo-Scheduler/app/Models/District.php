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
}
