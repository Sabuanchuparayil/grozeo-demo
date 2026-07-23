<?php

namespace BackOffice\Models\CostDistribution;

use Illuminate\Database\Eloquent\Model;
/* use App\Models\Order;
use BackOffice\Models\CostDistribution\{
    CostDistribution,
    CostDistributionRule
}; */

class OrderCostDistributionAllocations extends Model
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
    protected $table = 'order_cost_distribution_allocations';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    
    /* public function costDistributionRule()
    {
        return $this->belongsTo(CostDistributionRule::class, 'rule_id', 'id');
    }
    public function costDistribution()
    {
        return $this->belongsTo(CostDistribution::class, 'distribution_id', 'id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    } */
}
