<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QugeoOrderCourier extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'qugeo_order_courier';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'qoc_id';

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
    protected $dates = ['qoc_createdOn', 'qoc_updatedOn'];
    const CREATED_AT = 'qoc_createdOn';
    const UPDATED_AT = 'qoc_updatedOn';
}
