<?php

require('TextLocalClass.php');

class TextLocalClass {

    public $TextLocalAuthCode;
    private $senderName = 'TXTLCL';
    private $Textlocal;
    private $SmsDB;

    /**
     * Constructor to initialize common settings
     *
     * @param string $authCode
     * The TextLocal User field(default:false -> will take saturn's account)
     */
    public function __construct($db) {
        if (defined('TextLocalSMS_CREDENTIALS'))
            $this->TextLocalAuthCode = TextLocalSMS_CREDENTIALS;    //api key
        if (defined('TextLocalSMS_SENDER'))
            $this->senderName = TextLocalSMS_SENDER;
        //($username, $hash, $apiKey = false)
        $this->Textlocal = new TextLocal(false, false, TextLocalSMS_CREDENTIALS);
        $this->SmsDB = $db;
    }

    /**
     * Function to send the SMS through TextLocal API
     *
     * @param string $receipientno
     * 		The mobile no of the receipient
     * @param string $message
     * 		The message to send
     * @return mixed Transaction no on success else false
     */
    public function sendSMS($receipientno, $message, $refno) {
        try {
            $responseObj = $this->Textlocal->sendSms(array($receipientno), $message, $this->senderName);
            $response = json_encode($responseObj);

            if ($responseObj->status == "success") {
                $this->writeToLog(true, $refno, $receipientno, $message, $response);
                $transID = ($responseObj->status == "success") ? time() : false;
                return $transID;
            } else {
                $this->writeToLog(false, $refno, $receipientno, $message, $response);
            }
            return false;
        }

        //catch exception
        catch (Exception $e) {
            $this->writeToLog(false, $refno, $receipientno, $message, $e->getMessage());
            return false;
        }
    }

    /**
     * Function used to write the log entry
     *
     * @param string $transID The transaction id
     * @param string $url The url used for request
     */
    private function writeToLog($isSucess, $refno, $mobno, $message, $smsresponse) {
        if ($isSucess) {
            $smsdata = array('sms_responseid' => $smsresponse, 'smsemail_idno' => $bookno, 'smsemail_id' => $mobno, 'sms_isaccepted' => 1, 'smsemail_datetime' => date("Y-m-d H:i:s"), 'smsemail_text' => $message, 'cust_id' => $refno);
        } else {
            $smsdata = array('sms_responseid' => $smsresponse, 'smsemail_idno' => $bookno, 'smsemail_id' => $mobno, 'sms_isaccepted' => 0, 'smsemail_datetime' => date("Y-m-d H:i:s"), 'smsemail_text' => $message, 'cust_id' => $refno);
        }
        $this->SmsDB->perform('sms_email_logs', $smsdata);
        return $ret;
    }

}

class sms {

    public static function send($number, $message, $db, $refno = "") {
        //$obj  = new TextLocalClass($db);
        //$resp = $obj->sendSMS($number,$message,$refno);		
        //return (defined('DEBUG_SMS')) ? $resp : ($resp !== false);
        if (strpos($message, 'Thank you for using PackSure') !== false) {
            $tempid = "1607100000000130950";
        } elseif (strpos($message, 'to the delivery boy on completion of delivery') !== false) {
            $tempid = "1607100000000130928";
        } elseif (strpos($message, 'your fastest delivering super market with hyper local delivery') !== false) {
            $tempid = "1607100000000130942";
        } elseif (strpos($message, 'We have received Rs.') !== false) {
            $tempid = "1607100000000130940";
        } elseif (strpos($message, 'Since you opt Cash on Delivery payment, please keep ready Rs') !== false) {
            $tempid = "1607100000000130939";
        } elseif (strpos($message, 'is failed. We have saved your order so that you may complete the same') !== false) {
            $tempid = "1607100000000130937";
        } elseif (strpos($message, 'You have selected online payment and we will update you once the payment complete') !== false) {
            $tempid = "1607100000000130934";
        } elseif (strpos($message, 'has delivered your Order No.') !== false) {//Delivered
            $tempid = "1607100000000004819";
        } elseif (strpos($message, 'is arriving to you soon. Please provide the OTP') !== false) {//Delivery Reaching
            $tempid = "1607100000000004818";
        } elseif (strpos($message, 'Please check for items carefully while accepting the order.') !== false) {//Delivery Pickup
            $tempid = "1607100000000004817";
        } elseif (strpos($message, 'Thank you for using Drive') !== false) {//Drive Registration
            $tempid = "1607100000000004851";
        } elseif (strpos($message, 'We did NOT receive funds for') !== false) {//Payment Time Out
            $tempid = "1607100000000117420";
        } else {
            $tempid = "1607100000000130950";
        }
        file_put_contents('php://stderr', print_r("POCKETKART_SMS_PROVIDER -- " . POCKETKART_SMS_PROVIDER . "\n", TRUE));
        if (defined('POCKETKART_SMS_PROVIDER') && POCKETKART_SMS_PROVIDER == true) {
            $abasoft = self::requestCURL("http://sms.bulksmsind.in/sendSMS?username=codefacetech&message=" . urlencode($message) . "&sendername=PTKART&smstype=TRANS&numbers=" . trim($number) . "&apikey=9b794001-fc6a-47e9-8163-7d0144d85573");
        } elseif (defined('AIRTEL_SMS_PROVIDER') && AIRTEL_SMS_PROVIDER == true) {
            $abasoft = self::requestAirtelCURL("https://openapi.airtel.in/gateway/airtel-iq-sms-utility/sendSms", $number, $message, $tempid);
        } else {
            $abasoft = self::requestCURL("http://sms.aabasoft.mobi/bulksms/bulksms?username=Aa10-velosit&password=velosit1&type=0&dlr=1&destination=91" . trim($number) . "&tempid=" . $tempid . "&entityid=1601100000000010565&source=VELOSI&message=" . urlencode($message));
        }
        file_put_contents('php://stderr', print_r("aabasoft -- " . $abasoft . "\n", TRUE));
        sms::sendbackupmailforSMS(
                array("email" => "velosit_sms@yopmail.com", "subject" => $number . "-" . $message, "body" => $message));
        return true;
    }

    public static function sendbackupmailforSMS($data) {
        $db = new sqlDb(DSN);
        $test['receiver_id'] = $data['email'];
        $test['is_sms'] = 0;
        $test['extra_info'] = $data['subject'];
        $test['text_message'] = $data['body'];
        $test['created_on'] = date("Y-m-d H:i:s");
        $status = $db->perform('retaline_emailsms_queue', $test);
    }

    public static function requestCURL($url) {
        $response = false;
        $cURLConnection = curl_init();

        curl_setopt($cURLConnection, CURLOPT_URL, $url);
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
        file_put_contents('php://stderr', print_r("aabasoft -- entering . " . $url . " \n", TRUE));
        $response = curl_exec($cURLConnection);
        file_put_contents('php://stderr', print_r("aabasoft -- exiting \n", TRUE));
        curl_close($cURLConnection);
        return (defined('DEBUG_SMS')) ? $response : $response;
    }

    public static function requestAirtelCURL($url, $phone, $msg, $tempid, $entityid = "1601100000000010565") {
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
        file_put_contents('php://stderr', print_r("aabasoft -- entering . " . $url . " \n", TRUE));
        $response = curl_exec($cURLConnection);
        file_put_contents('php://stderr', print_r("aabasoft -- exiting \n", TRUE));
        curl_close($cURLConnection);
        return (defined('DEBUG_SMS')) ? $response : $response;
    }

}
