<?php

namespace App\Models\Drivers;

use Illuminate\Database\Eloquent\Model;

class QugeoOrderDetails extends Model
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
    protected $table = 'qugeo_orderdetails';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'quod_id';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];
    

}
