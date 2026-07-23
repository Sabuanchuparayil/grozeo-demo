<?php
namespace App\CourierPartners\Shiprocket;

use App\Helpers\HttpCurlCalls;

class ShiprocketTracking
{
    protected $authId;
    protected $authPass;
    protected $channelId;
    protected $apiUrl;
    function __construct()
    {
        $this->authId = config('courierpartners.shiprocket.auths.id');
        $this->authPass = config('courierpartners.shiprocket.auths.password');
        $this->channelId = config('courierpartners.shiprocket.channel_id');
        $this->apiUrl = config('courierpartners.shiprocket.authentication');
    }
       
    public function authentication()
    {
        return $this->createAuthToken();
    }
    private function createAuthToken()
    {
        $body = [
            'email'     => $this->authId,
            'password'  => $this->authPass
        ];
        $response = (new HttpCurlCalls)->curlCall($this->apiUrl, json_encode($body), 'POST', ['Content-Type: application/json']);
        return $response;
    }
}