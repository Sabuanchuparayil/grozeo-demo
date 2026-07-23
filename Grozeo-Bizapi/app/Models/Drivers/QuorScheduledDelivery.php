<?php

namespace App\Models\Drivers;

use Illuminate\Database\Eloquent\Model;

class QuorScheduledDelivery extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quor_scheduled_deliveries';
    protected $fillable=['quor_id','quorddb_id','sch_uuid'];
    public $timestamps = false;
}

