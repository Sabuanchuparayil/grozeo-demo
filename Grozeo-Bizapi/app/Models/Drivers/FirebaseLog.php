<?php

namespace App\Models\Drivers;

use Illuminate\Database\Eloquent\Model;

class FirebaseLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'qugeo_firebase_log';
    const CREATED_AT = 'rfir_date';
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $guarded = [];
}