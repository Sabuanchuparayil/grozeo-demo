<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnPacking extends Model
{
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
    protected $table = 'finascop_stock_return_request_packing';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'frrp_id';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'frrp_createdOn';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'frrp_updatedOn';

   

}
