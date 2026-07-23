<?php

namespace App\Sms;

use App\Sms\TextLocal;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Sms\SmsProviderInterFace;
use Illuminate\Support\Facades\Log;
use App\Http\Repositories\SmsEmailRepository;

if (file_exists(dirname(__FILE__, 3)."/vendor/twilio/sdk/src/Twilio/autoload.php")) {
    include dirname(__FILE__, 3)."/vendor/twilio/sdk/src/Twilio/autoload.php";
}
use Twilio\Rest\Client;


class TextLocalSms implements SmsProviderInterFace {

    protected $smsEmailLogs;

    public function __construct(SmsEmailRepository $smsEmailLogs) {
        $this->smsEmailLogs = $smsEmailLogs;
    }

    private function getTemplate() {
        
    }

    public function requestAirtelCURL($url, $phone, $msg, $tempid, $entityid = "1601100000000010565") {
        $response = false;
        $cURLConnection = curl_init();
        $headers = [
            'Authorization: Basic VmVsb3NpdF9TTVM6Nl9zcSdxK0puOX5EW1o1JQ==',
            'Content-Type: application/json'
        ];
        $postData = [
            "customerId" => "Velosit",
            "destinationAddress" => [
                $phone
            ],
            "message" => $msg,
            "sourceAddress" => "VELOSI",
            "messageType" => "SERVICE_IMPLICIT",
            "dltTemplateId" => $tempid,
            "entityId" => $entityid
        ];
        curl_setopt($cURLConnection, CURLOPT_URL, $url);
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cURLConnection, CURLOPT_POST, 1);
        curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, json_encode($postData));
        // file_put_contents('php://stderr', print_r("aabasoft -- entering . " . $url . " \n", TRUE));
        $response = curl_exec($cURLConnection);
        //file_put_contents('php://stderr', print_r("aabasoft -- exiting \n", TRUE));
        curl_close($cURLConnection);
        return (defined('DEBUG_SMS')) ? $response : $response;
    }
    
    public function requestKalyeraSmsCURL($phone, $type,$content,$tempid) {
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
        return (defined('DEBUG_SMS')) ? $data : $data;
    }

    public function requestcurl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); //$url,
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $ch = curl_init();
        $err = curl_error($ch);  //if you need
        curl_close($ch);


        return $response;
    }

    public function sendSms($mobile, $msg, $templid = 0) {

        if (strpos($msg, 'Thank you for using PackSure') !== false) {//Packsure OTP
            //$tempid = "1607100000000130950";
            $tempid = "1607100000000004826";
        } elseif (strpos($msg, 'to the delivery boy on completion of delivery') !== false) {//on qugeo
            $tempid = "1607100000000130928";
        } elseif (strpos($msg, 'is your fastest delivering super market with hyper local delivery.') !== false) {//customer otp
            $tempid = "1607100000000004824";
        } elseif (strpos($msg, 'We have received ') !== false) {//payment received
            $tempid = "1607100000000004823";
        } elseif (strpos($msg, 'Since you opt Cash on Delivery payment, please keep ready ') !== false) {//Order Created CoD Selected
            $tempid = "1607100000000004822";
        } elseif (strpos($msg, 'is failed. We have saved your order so that you may complete the same') !== false) {//Payment Failed
            $tempid = "1607100000000004821";
        } elseif (strpos($msg, 'You have selected online payment and we will update you once the payment complete') !== false) {//Order Created PG Selected
            $tempid = "1607100000000004820";
        } elseif (strpos($msg, 'has delivered your Order No.') !== false) {//on qugeo
            $tempid = "1607100000000004819";
        } elseif (strpos($msg, 'is arriving to you soon. Please provide the OTP') !== false) {//on qugeo
            //$tempid = "1607100000000130944";
            $tempid = "1607100000000004818";
        } elseif (strpos($msg, 'Please check for items carefully while accepting the order.') !== false) {
            $tempid = "1607100000000130925";
        } elseif (strpos($msg, 'Thank you for using Drive') !== false) {
            //$tempid = "1607100000000004851";
            $tempid = "1607100000000130948";
        } elseif (strpos($msg, 'We did NOT receive funds for') !== false) {
            $tempid = "1607100000000130950";
        } else {
            $tempid = "1607100000000130950";
        }
        if (config('sms.pocketkart_sms_provider') == 'true') {
            $url = "http://sms.bulksmsind.in/sendSMS?username=" . env('POCKETKART_SMS_USERNAME', '') . "&message=" . urlencode($msg) . "&sendername=PTKART&smstype=TRANS&numbers=" . $mobile . "&apikey=" . env('POCKETKART_SMS_API_KEY', '');

            $response = $this->requestcurl($url);
        } elseif (config('sms.airtel_sms_provider') == 'true') {
            $url = "https://openapi.airtel.in/gateway/airtel-iq-sms-utility/sendSms";
            $response = $this->requestAirtelCURL($url, $mobile, $msg, $tempid);
        } else {
            $url = "http://sms.aabasoft.mobi/bulksms/bulksms?username=" . config('sms.text_local_sms_username') . "&entityid=1601100000000010565&password=" . config('sms.text_local_sms_password') . "&tempid=" . $tempid . "&type=0&dlr=1&destination=91" . $mobile . "&source=" . config('sms.text_local_sms_source') . "&message=" . urlencode($msg);
            $response = $this->requestcurl($url);
        }
        DB::table('retaline_emailsms_queue')->insert(
                [
                    'sender_id' => config('emailschedule.email_sender_id'),
                    'sender_name' => config('emailschedule.welcome_sender'),
                    'receiver_id' => "velosit_sms@yopmail.com",
                    'type' => 2,
                    'extra_info' => $mobile . "-" . $url,
                    'text_message' => $msg,
                    'created_on' => date('Y-m-d H:i:s'),
                    'updated_on' => date('Y-m-d H:i:s')
                ]
        );
        $data = compact("mobile", "msg", "response");
        $msg = $this->smsEmailLogs->store($data);
        return $response;
    }

    public function requestTwilioSms($phone, $content)
    {
        $sid = config('sms.twilio.sender_id'); 
        $token = config('sms.twilio.api_key');
        $service = config('sms.twilio.service_id');
        $twilio = new Client($sid, $token);

        $message = $twilio->messages->create(
            $phone,
            array(
                "messagingServiceSid"   => $service,
                "body"                  => $content
        )); 
        return $message;
    }
    public function requestEquenceSms($phone, $templateID, $content)
    {
        $url = config('sms.equence.url');
        $fields = [
            'username'  => config('sms.equence.username'),
            'peId'      => config('sms.equence.peId'),
            'password'  => config('sms.equence.password'),
            'from'      => config('sms.equence.from'),
            'to'        => $phone,
            'tmplId'    => $templateID,
            'text'      => $content
        ];
        $url = "{$url}?".http_build_query($fields, '', '&', PHP_QUERY_RFC3986);
        $message = (new HttpCurlCalls)->curlCall($url, [], 'GET', []);
        return (is_array($message) || is_object($message)) ? json_encode($message) : $message;
    }

}
