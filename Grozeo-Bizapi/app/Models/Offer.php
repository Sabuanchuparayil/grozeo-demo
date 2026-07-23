<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'bom_id';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_offer_management';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    /**
     * created at field
     */
    const CREATED_AT = 'bom_createdOn';
    /**
     * updated at field
     */
    const UPDATED_AT = 'bom_updatedOn';
}