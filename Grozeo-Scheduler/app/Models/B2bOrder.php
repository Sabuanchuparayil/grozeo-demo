<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\B2bOrderItems;

class B2bOrder extends Model
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
    protected $table = 'retaline_B2B_SalesOrder';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'bbso_id';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'bbso_createdon';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'bbso_updatedon';

    public function orderItems()
    {
        return $this->hasMany(B2bOrderItems::class, 'bbso_id');
    }
    

}
