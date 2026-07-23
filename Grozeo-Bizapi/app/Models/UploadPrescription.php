<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadPrescription extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'upload_prescription';
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * The attributes that aren't mass assignable.
     *
     *
     *
     * @var array
     */
    protected $guarded = [];



    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];
}
