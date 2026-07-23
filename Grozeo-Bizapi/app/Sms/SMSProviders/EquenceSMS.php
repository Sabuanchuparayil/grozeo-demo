<?php
namespace App\Sms\SMSProviders;

use App\Helpers\HttpCurlCalls;
use Illuminate\Support\Facades\DB;

class EquenceSMS
{
    public function sendSMS($mobile, $type, $msg, $tempid)
    {
        $url = config('sms.equence.url');
        $fields = [
            'username'  => config('sms.equence.username'),
            'peId'      => config('sms.equence.peId'),
            'password'  => config('sms.equence.password'),
            'from'      => config('sms.equence.from'),
            'to'        => $mobile,
            'tmplId'    => $tempid,
            'text'      => $msg
        ];
        $url = "{$url}?".http_build_query($fields, '', '&', PHP_QUERY_RFC3986);
        $message = (new HttpCurlCalls)->curlCall($url, [], 'GET', []);
        $response = (is_array($message) || is_object($message)) ? json_encode($message) : $message;
        
        DB::table('retaline_emailsms_queue')->insert([
            'sender_id'     => config('sms.equence.peId'),
            'sender_name'   => config('sms.equence.username'),
            'receiver_id'   => $mobile,
            'type'          => 2,
            'extra_info'    => $response,
            'text_message'  => $msg,
            'created_on'    => date('Y-m-d H:i:s'),
            'updated_on'    => date('Y-m-d H:i:s')
        ]);

        return $response;
    }
}