<?php

namespace BackOffice\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionChart extends Model
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
    protected $table = 'retaline_distribution_chart';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'rdc_id';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'rdc_createdOn';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'rdc_updatedOn';

   

}
