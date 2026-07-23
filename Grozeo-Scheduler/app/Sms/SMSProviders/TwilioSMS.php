<?php
namespace App\Sms\SMSProviders;

use Illuminate\Support\Facades\DB;
// include dirname(__FILE__, 4)."/vendor/twilio/sdk/src/Twilio/autoload.php";
use Twilio\Rest\Client;

class TwilioSMS
{
    public function sendSMS($mobile, $type, $msg, $tempid)
    {
        $sid = config('sms.twilio.sender_id'); 
        $token = config('sms.twilio.api_key');
        $service = config('sms.twilio.service_id');
        $twilio = new Client($sid, $token);
        $message = $twilio->messages->create(
            $mobile,
            array(
                "messagingServiceSid"   => $service,
                "body"                  => $msg
        ));

        DB::table('retaline_emailsms_queue')->insert([
            'sender_id'     => config('sms.twilio.sender_id'),
            'sender_name'   => config('sms.twilio.sender_num'),
            'receiver_id'   => $mobile,
            'type'          => 2,
            'extra_info'    => json_encode($message),
            'text_message'  => $msg,
            'created_on'    => date('Y-m-d H:i:s'),
            'updated_on'    => date('Y-m-d H:i:s')
        ]);

        return $message;
    }
}