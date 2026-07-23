<?php

namespace App\CourierPartners\Shipyaari;

use Carbon\Carbon;

class ShipyaariPartnerFinder
{
	public function findBestPartner($deliveryPartnerList, $packageWeight = 1)
	{
		return $this->partnerChecker($deliveryPartnerList, $packageWeight);
	}

	private function partnerChecker($deliveryPartnerList, $packageWeight)
	{
		$partner = [];
		$maxScore = -1;
		$charge = array_column($deliveryPartnerList, 'total');
		$maxDeliveryCharge = max($charge);
		foreach ($deliveryPartnerList as $dp)
		{
			$score = $this->bestPartnerCalculator($dp, $packageWeight, $maxDeliveryCharge);

			if($score > $maxScore)
			{
				$partner = $dp;
				$maxScore = $score;
			}
		}
		return [
			'partner'	=> $partner,
			'score'		=> $maxScore
		];
	}
	private function bestPartnerCalculator($dp, $packageWeight, $maxDeliveryCharge = 100)
	{
    	$maxDeliveryDays = 14;
    	$estimatedDate = Carbon::parse($dp->estimate_date);
    	$now = Carbon::now();
    	$deliveryTime = $estimatedDate->diffInDays($now);

    	$deliveryChargeScore = ($maxDeliveryCharge - $dp->total) / $maxDeliveryCharge;
	    $deliveryDaysScore = ($maxDeliveryDays - $deliveryTime) / $maxDeliveryDays;

	    $totalScore = ($deliveryChargeScore * $packageWeight) + ($deliveryDaysScore * $packageWeight);
	    return $totalScore;
	}
}