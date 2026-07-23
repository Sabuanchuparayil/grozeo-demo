<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CpdOrder extends Model
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
    protected $table = 'retaline_branch_outward_order';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'order_id';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'bcor_createdon';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'bcor_updatedon';

    public function orderItems()
    {
        return $this->hasMany(CpdOrderItems::class, 'bcor_id');
    }

}
