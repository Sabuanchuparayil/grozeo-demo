<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adzone extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'app_adzones';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'adzone_id';
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;


    public function adzone_details()
    {
        return $this->hasMany('App\Models\Advertisement','adzone_id','adzone_id');
    }

}
