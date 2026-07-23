<?php

namespace BackOffice\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessLock extends Model
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
    protected $table = 'process_lock';
   

}
