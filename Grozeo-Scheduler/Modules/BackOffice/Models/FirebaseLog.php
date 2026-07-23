<?php

namespace BackOffice\Models;

use Illuminate\Database\Eloquent\Model;

class FirebaseLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_firebase_log';
    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'rfir_date';


    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'rfir_id';

}