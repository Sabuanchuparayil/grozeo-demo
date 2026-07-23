<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class HealthConcern extends Model
{
   //
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mypha_disease';
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'disease_id';
}
