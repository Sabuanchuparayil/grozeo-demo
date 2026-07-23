<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafetyPrecaution extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mypha_safety_precaution';
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
protected $primaryKey = 'precaution_id';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'precautionCreatedOn', 'precautionCreatedBy', 'precautionUpdatedOn', 'precautionUpdatedBy'
    ];




}
