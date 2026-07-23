<?php

namespace BackOffice\Models;

use Illuminate\Database\Eloquent\Model;

class LaravelJobs extends Model
{
    public $timestamps = false;


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
    protected $table = 'laravel_jobs';


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

   

}
