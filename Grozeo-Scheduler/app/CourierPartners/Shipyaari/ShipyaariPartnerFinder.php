<?php

namespace App\CourierPartners\Shipyaari;

class ShipyaariPartnerFinder
{
	// public function findBestPartner($deliveryPartnerList, $packageWeight = 1, $charge = 0)
	public function findBestPartner($deliveryPartnerList, $charge = 0)
	{
		if($charge > 0)
		{
			$deliveryPartners = array_filter($deliveryPartnerList, function($dp) use($charge){
				if($dp->delivery_charge <= $charge)
				{
					return 1;
				}
			});
			if(count($deliveryPartners) > 0)
			{
				if(count($deliveryPartners) > 1)
				{
					usort($deliveryPartners, function($item1, $item2){
	    				return ($item1->delivery_charge >= $item2->delivery_charge) ? 1 : -1;
					});
				}
				return reset($deliveryPartners);
			}
		}
		return [];
	}
	private function bestPartnerCalculator($dp, $packageWeight)
	{
		$maxDeliveryCharge = 100;
    	$maxDeliveryDays = 14;

    	$deliveryChargeScore = ($maxDeliveryCharge - $rate['cost']) / $maxDeliveryCharge;
	    $deliveryDaysScore = ($maxDeliveryTime - $rate['deliveryTime']) / $maxDeliveryTime;

	    $totalScore = ($deliveryChargeScore * $packageWeight) + ($deliveryDaysScore * $packageWeight);
	    return $totalScore;
	}
}