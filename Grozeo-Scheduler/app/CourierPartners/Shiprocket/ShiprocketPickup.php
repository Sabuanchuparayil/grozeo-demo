<?php
namespace App\CourierPartners\Shiprocket;

use App\Helpers\HttpCurlCalls;
use App\CourierPartners\Shiprocket\ShiprocketApiFunctions;

class ShiprocketPickup
{
    protected $functions;
    function __construct()
    {
        $this->functions = new ShiprocketApiFunctions;
        $this->apiUrl = config('courierpartners.shiprocket.createPickup');
    }
       
    public function createPickupAddress($branch_id)
    {
        $headers = $this->functions->getHeader(1);
        $pickupBody = $this->functions->createPickupAddress($branch_id);

        $response = (new HttpCurlCalls)->curlCall($this->apiUrl, json_encode($pickupBody), 'POST', $headers);
        if(@$response->success == true)
        {
        }
        return $response;
    }
}