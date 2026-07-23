<?php
namespace App\Partners\Courier\Shipyaari;

use App\Helpers\HttpCurlCalls;

class ShipyaariAuthorization
{
    function __construct()
    {
        $baseURL = config('shipping.creds.shipyaari.baseURL');
        $this->email = config('shipping.creds.shipyaari.email');
        $this->password = config('shipping.creds.shipyaari.password');
        $this->tokenAPI = $baseURL.config('shipping.creds.shipyaari.tokenAPI');

        $this->curlCall = new HttpCurlCalls();
    }

    public function generateAuthToken()
    {
        $outs = ["status" => "failed", "message" => "Operation failed"];
        $request = [
            "email"     => $this->email,
            "password"  => $this->password
        ];
        $response = $this->curlCall->curlCall($this->tokenAPI, json_encode($request), "POST", ['Content-Type: application/json']);
        $outs["message"] = @$response->message ?? "Token not available";
        if(@$response->success == true)
        {
            $data = reset($response->data);
            $token = @$data->token;
            if($token)
            {
                $outs['status'] = "success";
                $outs['message'] = $token;
            }
        }
        return $outs;
    }
}