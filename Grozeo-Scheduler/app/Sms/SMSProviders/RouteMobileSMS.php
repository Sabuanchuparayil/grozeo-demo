<?php
namespace App\Sms\SMSProviders;

use Illuminate\Support\Facades\DB;

class RouteMobileSMS
{
    public function sendSMS($mobile, $type, $msg, $tempid)
    {
        $url = config('sms.routemobile.url');
        $fields = [
            'username'      => config('sms.routemobile.username'),
            'password'      => config('sms.routemobile.password'),
            'type'          => config('sms.routemobile.type'),
            'dlr'           => config('sms.routemobile.dlr'),
            'source'        => config('sms.routemobile.source'),
            'destination'   => $mobile,
            'message'       => $msg
        ];
        $url = "{$url}?".http_build_query($fields, '', '&', PHP_QUERY_RFC3986);

        $message = file_get_contents($url);
        $response = (is_array($message) || is_object($message)) ? json_encode($message) : $message;
        DB::table('retaline_emailsms_queue')->insert([
            'sender_id'     => config('sms.routemobile.username'),
            'sender_name'   => config('sms.routemobile.username'),
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