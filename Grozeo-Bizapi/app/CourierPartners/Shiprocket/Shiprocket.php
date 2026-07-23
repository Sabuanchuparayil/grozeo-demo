<?php
namespace App\CourierPartners\Shiprocket;

use DateTime;
use Illuminate\Support\Facades\DB;
use BackOffice\Models\TransferOrder;
use App\Http\Responses\SuccessWithData;

use App\CourierPartners\Shiprocket\{
	ShiprocketAuthentication,
	ShiprocketOrders,
	ShiprocketTracking,
	ShiprocketPickup
};

class Shiprocket
{
	protected $auth;
	protected $ordering;
	protected $tracking;
	protected $functions;
	function __construct()
	{
		$this->auth = new ShiprocketAuthentication;
		$this->ordering = new ShiprocketOrders;
		$this->tracking = new ShiprocketTracking;
		$this->pickup = new ShiprocketPickup;
	}
	public function srAuth()
	{
		try
		{
			$outs = [
				'status'	=> false,
				'token'		=> ''
			];
			$authResponse = $this->auth->authentication();
			if($authResponse)
			{
				$outs['status'] = true;
				$outs['token'] = $authResponse;
			}
            return $outs; 
		}
		catch (\Exception $e)
        {
            return [
            	'status'	=> 'error',
            	'msg'		=> $e->getMessage()
            ]; 
        }
	}

	public function createPickupAddress($branch_id)
	{
		try
		{
			$outs = [
				'status'	=> false,
				'details'		=> ''
			];
			$authResponse = $this->pickup->createPickupAddress($branch_id);
			if($authResponse)
			{
				$outs['status'] = true;
				$outs['details'] = $authResponse;
			}
            return $outs; 
		}
		catch (\Exception $e)
        {
            return [
            	'status'	=> 'error',
            	'msg'		=> $e->getMessage()
            ]; 
        }
	}

	public function generateShipment($fsto_id)
	{
		try
		{
			$outs = [
				'status'	=> false,
				'details'		=> ''
			];
			$authResponse = $this->ordering->createNewShipment($fsto_id);
			if($authResponse)
			{
				$outs['status'] = true;
				$outs['details'] = $authResponse;
			}
            return $outs; 
		}
		catch (\Exception $e)
        {
            return [
            	'status'	=> 'error',
            	'msg'		=> $e->getMessage()
            ]; 
        }
	}
}