<?php

namespace App\Calls;

use App\Helpers\HttpCurlCalls;

class OzontelCalls
{
    public function triggerIvrCall($mobile, $name)
    {
        $url = config('ivrcalls.ozonetel.url');
        $fields = [
            "api_key" => config("ivrcalls.ozonetel.key"),
            "campaign_name" => config("ivrcalls.ozonetel.campaign"),
            "format" => 'json',
            "PhoneNumber" => $mobile . 'Name=' . $name,
            "action" => 'start'
        ];
        $fields_string = http_build_query($fields, '', '&');
        $apiURL = $url . $fields_string;
        $message = (new HttpCurlCalls)->curlCall($apiURL, [], 'GET', []);
        $response = (is_array($message) || is_object($message)) ? json_encode($message) : $message;

        return (@$response->status == "SUCCESS") ? true : false;
    }
}
