<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapDiseaseMedicine extends Model
{
   //
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mypha_mapDiseaseMedicine';
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
    protected $primaryKey = 'medDise_id';
}
