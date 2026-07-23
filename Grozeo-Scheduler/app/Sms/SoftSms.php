<?php

namespace App\Sms;

use App\Sms\SmsProviderInterface;
use GuzzleHttp\Client;

class SoftSms implements SmsProviderInterface
{
 
    protected $client;

    public function sendSms($mobile, $msg)
    {
        $this->client = new Client(['base_uri' => config('sms.soft_sms.api_url')]);
        
        return $this->client->request('GET', '', [
            'query' => [
                'key' => config('sms.soft_sms.api_key'),
                'type' => 'text',
                'contacts' => $mobile,
                'senderid' => config('sms.soft_sms.sender_id'),
                'msg' => $msg,
            ]
        ]);
    }
}