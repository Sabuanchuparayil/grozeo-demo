<?php

namespace App\Calls;

use App\Helpers\HttpCurlCalls;

class VoxbayCalls
{
    public function triggerIvrCall($mobile, $name)
    {
        $url = config('ivrcalls.voxbay.url');
        if(substr($mobile, 0, 1) != "+")
            {
                $phonecode = config('app.phonecode');
                $pos = strpos($mobile, $phonecode);
                if ($pos == false)
                {
                    $mobile = $phonecode.$mobile;
                }
            }
        $mobile = ltrim($mobile, '+');
        $fields = [            
            "uid" => config("ivrcalls.voxbay.uid"),
            "upin" => config("ivrcalls.voxbay.upin"),
            "phone_no" => $mobile,
            "call_flow_name" => config("ivrcalls.voxbay.call_flow_name"),
            "did" => config("ivrcalls.voxbay.did"),
        ];
        $fields_string = http_build_query($fields, '', '&');
        $apiURL = $url . $fields_string;
        $response = (new HttpCurlCalls)->curlCall($apiURL, [], 'GET', []);
        //$response = (is_array($message) || is_object($message)) ? json_encode($message) : $message;

        return (@$response->status == true) ? true : false;
    }
}
