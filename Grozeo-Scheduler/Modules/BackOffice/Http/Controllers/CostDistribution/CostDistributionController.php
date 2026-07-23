<?php

namespace BackOffice\Http\Controllers\CostDistribution;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use BackOffice\Models\CostDistribution\{
    CostDistribution,
    CostDistributionRule,
    OrderCostDistributionAllocations
};
use App\Http\Responses\{
	SuccessWithData,
	SuccessResponse,
	ErrorResponse
};

class CostDistributionController
{
    public function __construct() {}

    public function addCostDistribution($order_id)
    {	
    	try
    	{
    		$ruleID = config('costdistribution.rule');
    		$ruleData = CostDistributionRule::find($ruleID);
    		if($ruleData)
    		{

	    		$checkData = OrderCostDistributionAllocations::where([
	    			['rule_id', $ruleID],
	    			['order_id', $order_id]
	    		])->count();
	    		if($checkData == 0)
	    		{
	    			$distributions = $ruleData->costDistribution;
	    			if($distributions)
	    			{
			    		$order = Order::find($order_id);
			    		if($order)
			    		{
			    			$insertions = [];
	    					if($order->order_sales_margin > 0)
	    					{
	    						$totAllocatn = 0;
	    						foreach ($distributions as $dist)
	    						{
	    							if($dist->mode == 1)
	    							{
	    								$allocatedAmount = ($dist->allocation/100)*$order->order_sales_margin;
	    								$allocatedAmount = number_format(floor($allocatedAmount * 100) / 100, 2);
	    								$insertions[] = OrderCostDistributionAllocations::create([
	    									'rule_id'				=> $ruleID,
											'distribution_id'		=> $dist->id,
											'order_id'				=> $order->order_id,
											'allocation_amount'		=> $allocatedAmount
	    								]);
	    								$totAllocatn += $allocatedAmount;
	    							}
	    						}
	    						$allocatedAmount = $order->order_sales_margin - $totAllocatn;
	    						$insertions[] = OrderCostDistributionAllocations::create([
									'rule_id'				=> $ruleID,
									'distribution_id'		=> -1,
									'order_id'				=> $order->order_id,
									'allocation_amount'		=> $allocatedAmount
								]);
	    					}
							return new SuccessWithData($insertions);
			    		}
			    		else
			    		{
			    			return new ErrorResponse('Order not found'); 
			    		}
					}
		    		else
		    		{
		    			return new ErrorResponse('Rule Allocations is not defined'); 
		    		}
		    	}
		    	else
	    		{
	    			return new ErrorResponse('Already added allocation data'); 
	    		}
    		}
    		else
    		{
    			return new ErrorResponse('Rule is not configured'); 
    		}
    	}
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
}