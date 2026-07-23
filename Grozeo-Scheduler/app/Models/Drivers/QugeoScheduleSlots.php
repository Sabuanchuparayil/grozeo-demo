<?php

namespace App\Models\Drivers;

use Illuminate\Database\Eloquent\Model;

class QugeoScheduleSlots extends Model
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
    protected $table = 'qugeo_scheduleslots';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
}
