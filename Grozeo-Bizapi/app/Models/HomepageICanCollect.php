<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageICanCollect extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_homepage_collect';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
