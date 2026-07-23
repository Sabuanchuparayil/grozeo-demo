<?php
namespace App\ExpressPartners\Uber;

use App\Helpers\HttpCurlCalls;

class UberAuthorization
{
    protected $clientID, $clientSecret, $tokenApi;
    function __construct()
    {
        $this->clientID = config("expresspartners.uber.clientID");
        $this->clientSecret = config("expresspartners.uber.clientSecret");
        $this->tokenApi = config("expresspartners.uber.generateToken");
    }

    public function generateUberToken()
    {
        $body = [
            "client_id"     => $this->clientID,
            "client_secret" => $this->clientSecret,
            "grant_type"    => "client_credentials",
            "scope"         => "eats.deliveries"
        ];
        $data = (new HttpCurlCalls)->curlCall($this->tokenApi, $body, 'POST', []);
        return $data;
    }
}