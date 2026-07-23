<?php

namespace App\Models\Drivers;

use Illuminate\Database\Eloquent\Model;

class QugeoDriverLog extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'qugeo_driver_log';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    const CREATED_AT = 'createdOn';
    const UPDATED_AT = null;

}
