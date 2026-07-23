<?php
namespace App\CourierPartners\Shiprocket;

use App\Helpers\HttpCurlCalls;
use App\CourierPartners\Shiprocket\ShiprocketApiFunctions;

class ShiprocketOrders
{
    protected $authId;
    protected $authPass;
    protected $channelId;
    protected $apiUrl;
    protected $functions;
    function __construct()
    {
        $this->functions = new ShiprocketApiFunctions;

        $this->authId = config('courierpartners.shiprocket.auths.id');
        $this->authPass = config('courierpartners.shiprocket.auths.password');
        $this->channelId = config('courierpartners.shiprocket.channel_id');
        $this->apiUrl = config('courierpartners.shiprocket.authentication');
    }

    public function createNewShipment($fsto_id)
    {
        $headers = $this->functions->getHeader(1);
        $orderData = $this->functions->getOrderBody($fsto_id);
        return [
            'headers'   => $headers,
            'orderData' => $orderData
        ];
    }
}