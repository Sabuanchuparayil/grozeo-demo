<?php

namespace BackOffice\Models\CostDistribution;

use Illuminate\Database\Eloquent\Model;
use BackOffice\Models\CostDistribution\CostDistributionRule;

class CostDistribution extends Model
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
    protected $table = 'cost_distribution';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    
    public function costDistributionRule()
    {
        return $this->belongsTo(CostDistributionRule::class, 'rule_id', 'id');
    }
}
