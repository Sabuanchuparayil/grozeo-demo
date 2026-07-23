<?php
namespace App\Sms\SMSProviders;

use Illuminate\Support\Facades\DB;

class KalyeraSMS
{
    public function sendSMS($mobile, $type, $msg, $tempid)
    {
        $response = $this->curlurl->requestKalyeraSmsCURL($mobile, $type, $msg, $tempid);

        $url = config('sms.kalyera.api_url');
        $kalayeraSid = config('sms.kalyera.sender_id');
        $kalayeraApi = config('sms.kalyera.api_key');
        $kalayeraNum = config('sms.kalyera.sender_num');
        
        $callurl = $url . 'v1/' . $kalayeraSid . '/messages';
        
        $fields = array(
            "to" => $phone,
            "sender" => $kalayeraNum,
            "type" => $type,
            "body" => $content,
            "template_id" => $tempid
        );
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'api-key:' . $kalayeraApi
        );
		
        $fields_string = http_build_query($fields, '', '&');
        $opts = array(
            CURLOPT_URL => $callurl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => $headers
        );
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        curl_close($ch);
        $response = (defined('DEBUG_SMS')) ? $data : $data;


        DB::table('retaline_emailsms_queue')->insert([
            'sender_id'     => config('sms.kalyera.email_sender_id'),
            'sender_name'   => config('sms.kalyera.sender_num'),
            'receiver_id'   => $mobile,
            'type'          => 2,
            'extra_info'    => json_encode($response),
            'text_message'  => $msg,
            'created_on'    => date('Y-m-d H:i:s'),
            'updated_on'    => date('Y-m-d H:i:s')
        ]);
        return $response;
    }
}