<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafetyAdvice extends Model
{
  /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mypha_safety_advice';
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
    protected $primaryKey = 'advice_id';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'advice_createdOn', 'advice_createdBy', 'advice_updatedOn', 'advice_updatedBy'
    ];



}
