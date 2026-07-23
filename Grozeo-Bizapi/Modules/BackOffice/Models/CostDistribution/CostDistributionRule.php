<?php

namespace BackOffice\Models\CostDistribution;

use Illuminate\Database\Eloquent\Model;
use BackOffice\Models\CostDistribution\CostDistribution;

class CostDistributionRule extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cost_distribution_rule';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */

    
    public function costDistribution()
    {
        return $this->hasMany(CostDistribution::class, 'rule_id');
    }
}
