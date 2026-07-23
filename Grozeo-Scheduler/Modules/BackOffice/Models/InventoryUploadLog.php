<?php

namespace BackOffice\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryUploadLog extends Model
{
    /**
     * New connection froma adifferent database.
     *
     * @var string
     */
    protected $connection = 'support_db';
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
    protected $table = 'branch_inventory_upload_log';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    

}
