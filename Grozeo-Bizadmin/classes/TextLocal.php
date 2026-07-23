<?php

require('TextLocalClass.php');
require(EXTERNAL_LIBRARY_PATH);
include dirname(__FILE__, 3) . "/externallibs/vendor/twilio/sdk/src/Twilio/autoload.php";

class TextLocalClass
{

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
    public function __construct($db)
    {
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
    public function sendSMS($receipientno, $message, $refno)
    {
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
    private function writeToLog($isSucess, $refno, $mobno, $message, $smsresponse)
    {
        if ($isSucess) {
            $smsdata = array('sms_responseid' => $smsresponse, 'smsemail_idno' => $bookno, 'smsemail_id' => $mobno, 'sms_isaccepted' => 1, 'smsemail_datetime' => date("Y-m-d H:i:s"), 'smsemail_text' => $message, 'cust_id' => $refno);
        } else {
            $smsdata = array('sms_responseid' => $smsresponse, 'smsemail_idno' => $bookno, 'smsemail_id' => $mobno, 'sms_isaccepted' => 0, 'smsemail_datetime' => date("Y-m-d H:i:s"), 'smsemail_text' => $message, 'cust_id' => $refno);
        }
        $this->SmsDB->perform('sms_email_logs', $smsdata);
        return $ret;
    }
}

class sms
{

    public static function send($number, $message, $db, $refno = "")
    {
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
        } elseif (strpos($message, 'has delivered your Order No.') !== false) { //Delivered
            $tempid = "1607100000000004819";
        } elseif (strpos($message, 'is arriving to you soon. Please provide the OTP') !== false) { //Delivery Reaching
            $tempid = "1607100000000004818";
        } elseif (strpos($message, 'Please check for items carefully while accepting the order.') !== false) { //Delivery Pickup
            $tempid = "1607100000000004817";
        } elseif (strpos($message, 'Thank you for using Drive') !== false) { //Drive Registration
            $tempid = "1607100000000004851";
        } elseif (strpos($message, 'We did NOT receive funds for') !== false) { //Payment Time Out
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
            array("email" => "velosit_sms@yopmail.com", "subject" => $number . "-" . $message, "body" => $message)
        );
        return true;
    }

    public static function sendbackupmailforSMS($data)
    {
        $db = new sqlDb(DSN);
        $test['receiver_id'] = $data['email'];
        $test['is_sms'] = 0;
        $test['extra_info'] = $data['subject'];
        $test['text_message'] = $data['body'];
        $test['created_on'] = date("Y-m-d H:i:s");
        $status = $db->perform('retaline_emailsms_queue', $test);
    }

    public static function requestCURL($url)
    {
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

    public static function requestAirtelCURL($url, $phone, $msg, $tempid, $entityid = "1601100000000010565")
    {
        $db = new sqlDb(DSN);
        $response = false;
        $isMobileConfigured = $db->getItemFromDB("SELECT COUNT(*) FROM test_mobile WHERE mobile = '{$phone}'");
        if ($isMobileConfigured == 0) {
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
        }

        return (defined('DEBUG_SMS')) ? $response : $response;
    }

    public static function requestKalyeraSmsCURL($phone, $type, $content, $tempid)
    {
        $db = new sqlDb(DSN);
        $url = KSMS_API_URL;
        $kalayeraSid = KSMS_SENDER_ID;
        $kalayeraApi = KSMS_API_KEY;
        $kalayeraNum = KSMS_SENDER_NUM;


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

        $response = '';
        $isMobileConfigured = $db->getItemFromDB("SELECT COUNT(*) FROM test_mobile WHERE mobile = '{$phone}'");
        if ($isMobileConfigured == 0) {
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
            $response = curl_exec($ch);
            curl_close($ch);
        }


        $test = array(
            'sender_id' => $kalayeraSid,
            'sender_name' => $kalayeraNum,
            'receiver_id' => "velosit_sms@yopmail.com",
            'type' => 2,
            'extra_info' => $phone . "-" . $url,
            'text_message' => $content,
            'created_on' => date('Y-m-d H:i:s'),
            'updated_on' => date('Y-m-d H:i:s')
        );


        $status = $db->perform('retaline_emailsms_queue', $test);
        $data = compact("phone", "content", "response");
        $seldata = array(
            'smsemail_id' => $data['phone'],
            'smsemail_text' => $data['content'],
            'smsemail_datetime' => date('Y-m-d H:i:s'),
            'sms_responseid' => $data['response']
        );
        $status = $db->perform('sms_email_logs', $seldata);



        file_put_contents('php://stderr', print_r("kaleraaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa --" . $data . " \n.", TRUE));
        return (defined('DEBUG_SMS')) ? $data : $data;
    }

    public static function fetchContentSendSms($templatedata, $mobile, $templateType, $strGrpId = 0, $strGrpType = 1)
    {

        $db = new sqlDb(DSN);
        $url = KSMS_API_URL;
        $kalayeraSid = KSMS_SENDER_ID;
        $kalayeraApi = KSMS_API_KEY;
        $kalayeraNum = KSMS_SENDER_NUM;
        $getTemplateContent = $db->getFromDB('SELECT templateContent,templateId,templateHeader FROM sms_templates WHERE stn_templateNameId= ' . $templateType . ' AND (store_group_id = ' . $strGrpId . ' OR store_group_id = 0) AND is_used = ' . $strGrpType . ' ORDER BY store_group_id DESC LIMIT 1', true);
        //        $storegroupid = getHeaderStoreGroup();
        //        if ($storegroupid > 0) {
        //            $data = BranchGroup::where('store_group_id', $storegroupid)
        //                    ->select('store_group_name')
        //                    ->first();
        //            $siteName = $data['store_group_name'];
        //        } else {
        $siteName = PROJECT_NAME;
        //        }

        switch ($templateType) { // from table sms_template_name
            case 3:
                $type = 'MKT';
                //$otp = $this->genrtOtp->generateOtp($mobile);

                $data_base[0]['msg'] = $getTemplateContent['templateContent'];

                $vars = array(
                    '{#var1}' => $templatedata['total'],
                    '{#var2}' => $templatedata['sitename'],
                    '{#var3}' => self::getrandomstr($mobile)
                );

                $msg = strtr($data_base[0]['msg'], $vars);
                $tempid = $getTemplateContent['templateId'];
                break;
            case 4:
                $type = 'MKT';
                //$otp = $this->genrtOtp->generateOtp($mobile);

                $data_base[0]['msg'] = $getTemplateContent['templateContent'];

                $vars = array(
                    '{#var1}' => $templatedata['amount'],
                    '{#var2}' => $templatedata['order_id'],
                    '{#var3}' => $siteName,
                    '{#var4}' => $templatedata['ref_no']
                );

                $msg = strtr($data_base[0]['msg'], $vars);
                $tempid = $getTemplateContent['templateId'];
                break;
            case 5:
                $type = 'MKT';
                //$otp = $this->genrtOtp->generateOtp($mobile);

                $data_base[0]['msg'] = $getTemplateContent['templateContent'];

                $vars = array(
                    ' {#var1}' => $templatedata['sitename'],
                    '{#var2}' => self::getrandomstr($mobile)
                );

                $msg = strtr($data_base[0]['msg'], $vars);
                $tempid = $getTemplateContent['templateId'];
                break;
            case 6:
                $type = 'OTP';
                //$otp = $this->genrtOtp->generateOtp($mobile);

                $data_base[0]['msg'] = $getTemplateContent['templateContent'];

                $vars = array(
                    '{#var1}' => $siteName,
                    '{#var2}' => $templatedata['otp']
                );

                $msg = strtr($data_base[0]['msg'], $vars);
                $tempid = $getTemplateContent['templateId'];
                break;
            case 7:
                $type = 'MKT';
                //$otp = $this->genrtOtp->generateOtp($mobile);
                $orderBranch  = $db->getItemFromDB("SELECT order_branch_id FROM retaline_customer_order WHERE order_order_id ='{$templatedata['order_order_id']}'");
                $sgId = $db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$orderBranch}");
                $sgName = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = {$sgId}");
                if (!empty($sgName)) {
                    $siteData = $sgName;
                } else {
                    $siteData = $siteName;
                }
                $data_base[0]['msg'] = $getTemplateContent['templateContent'];

                $vars = array(
                    '{#var1}' => $templatedata['order_order_id'],
                    '{#var2}' => $siteData,
                    '{#var3}' => $templatedata['otp'],
                    '{#var4}' => self::getrandomstr($mobile)
                );

                $msg = strtr($data_base[0]['msg'], $vars);
                $tempid = $getTemplateContent['templateId'];
                break;
            case 9:
                $type = 'MKT';
                //$otp = $this->genrtOtp->generateOtp($mobile);

                $data_base[0]['msg'] = $getTemplateContent['templateContent'];

                $vars = array(
                    '{#var1}' => $siteName,
                    '{#var2}' => $templatedata['order_order_id'],
                    '{#var3}' => $templatedata['orderAmt']
                );

                $msg = strtr($data_base[0]['msg'], $vars);
                $tempid = $getTemplateContent['templateId'];
                break;
            case 10:
                $type = 'OTP';

                $data_base[0]['msg'] = $getTemplateContent['templateContent'];

                $vars = array(
                    '{#var1}' => $templatedata['otp'],
                    '{#var2}' => self::getrandomstr($mobile)
                );

                $msg = strtr($data_base[0]['msg'], $vars);
                $tempid = $getTemplateContent['templateId'];
                break;
            case 11:
                $type = 'MKT';
                $orderBranch  = $db->getItemFromDB("SELECT order_branch_id FROM retaline_customer_order WHERE order_order_id ='{$templatedata['order_order_id']}'");
                $sgId = $db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$orderBranch}");
                $sgName = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = {$sgId}");
                if (!empty($sgName)) {
                    $siteData = $sgName;
                } else {
                    $siteData = $siteName;
                }
                $data_base[0]['msg'] = $getTemplateContent['templateContent'];

                $vars = array(
                    '{#var1}' => $templatedata['order_order_id'],
                    '{#var2}' => $siteData,
                    '{#var3}' => self::getrandomstr($mobile)
                );

                $msg = strtr($data_base[0]['msg'], $vars);
                $tempid = $getTemplateContent['templateId'];
                break;
            case 12:
                $type = 'MKT';
                $orderBranch  = $db->getItemFromDB("SELECT order_branch_id FROM retaline_customer_order WHERE order_order_id ='{$templatedata['order_order_id']}'");
                $sgId = $db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$orderBranch}");
                $sgName = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = {$sgId}");
                if (!empty($sgName)) {
                    $siteData = $sgName;
                } else {
                    $siteData = $siteName;
                }
                $data_base[0]['msg'] = $getTemplateContent['templateContent'];

                $vars = array(
                    '{#var1}' => $templatedata['order_order_id'],
                    '{#var2}' => $siteData,
                    '{#var3}' => self::getrandomstr($mobile)
                );

                $msg = strtr($data_base[0]['msg'], $vars);
                $tempid = $getTemplateContent['templateId'];
                break;
            case 15:
                $type = 'OTP';

                $data_base[0]['msg'] = $getTemplateContent['templateContent'];

                $vars = array(
                    '{#var1}' => 'Grozeo',
                    '{#var2}' => $templatedata['otp'],
                    '{#var3}' => self::getrandomstr($mobile)
                );

                $msg = strtr($data_base[0]['msg'], $vars);
                $tempid = $getTemplateContent['templateId'];
                break;
            case 20:
                $type = 'OTP';

                $data_base[0]['msg'] = $getTemplateContent['templateContent'];

                $vars = array(
                    '{#var1}' => $templatedata['gst'],
                    '{#var2}' => $templatedata['otp']
                );

                $msg = strtr($data_base[0]['msg'], $vars);
                $tempid = $getTemplateContent['templateId'];
                break;
            case 21:
                $type = 'OTP';

                $data_base[0]['msg'] = $getTemplateContent['templateContent'];

                $vars = array(
                    '{#var1}' => $templatedata['otp']
                );

                $msg = strtr($data_base[0]['msg'], $vars);
                $tempid = $getTemplateContent['templateId'];
                break;
            case 24:
                $type = 'MKT';
                $data_base[0]['msg'] = $getTemplateContent['templateContent'];

                $vars = array(
                    '{#var1}' => $templatedata['invitationLink'],
                    '{#var2}' => self::getrandomstr($mobile)
                );

                $msg = strtr($data_base[0]['msg'], $vars);
                $tempid = $getTemplateContent['templateId'];
                break;
        }
        $operatingcountry = OPERATING_COUNTRY;
        $phonecode = PHONECODE;
        $url = KSMS_API_URL;
        $pos = strpos($mobile, $phonecode);
        file_put_contents('php://stderr', "-----------------mobile-------------------------------");
        file_put_contents('php://stderr', print_r($mobile, TRUE));
        file_put_contents('php://stderr', "-----------------pos-------------------------------");
        file_put_contents('php://stderr', print_r($pos, TRUE));
        if (strpos($mobile, $phonecode) !== false) {
        } else {
            $mobile = $phonecode . $mobile;
        }
        file_put_contents('php://stderr', "-----------------mobile-------------------------------");
        file_put_contents('php://stderr', print_r($mobile, TRUE));
        switch (SMSPROVIDER) {
            case 'kalyera':
                $response = self::requestKalyeraSmsCURL($mobile, $type, $msg, $tempid);
                break;
            case 'equence':
                $response = self::requestEquenceSmsCURL($mobile, $type, $msg, $tempid);
                break;
            case 'twilio':
                $response = self::requestTwilioSms($mobile, $msg);
                break;
            case 'airtel':
                $response = self::requestAirtelCURL($url, $mobile, $msg, $tempid);
                break;
            case 'route':
                $response = self::requestRouteSms($mobile, $type, $msg, $tempid);
                break;
        }


        return $response;
    }

    public function requestTwilioSms($phone, $content)
    {
        $db = new sqlDb(DSN);
        $sid = TWILIO_SENDER_ID;
        $token = TWILIO_API_KEY;
        $service = TWILIO_SERVICE_ID;
        $sendrNum = TWILIO_SENDER_NUM;
        $twilio = new \Twilio\Rest\Client($sid, $token);

        $response = '';
        $phonecode = PHONECODE;
        /*$pos = strpos($phone, $phonecode);
        if ($pos == false) {
            $mobile = str_replace($phonecode, '', $phone);
        }*/
        $isMobileConfigured = $db->getItemFromDB("SELECT COUNT(*) FROM test_mobile WHERE mobile = '{$phone}'");
        if ($isMobileConfigured == 0) {
            $response = $twilio->messages->create(
                $phone,
                array(
                    "messagingServiceSid"   => $service,
                    "body"                  => $content
                )
            );
        }


        $test = array(
            'sender_id' => $sid,
            'sender_name' => $sendrNum,
            'receiver_id' => "velosit_sms@yopmail.com",
            'type' => 2,
            'extra_info' => $phone,
            'text_message' => $content,
            'created_on' => date('Y-m-d H:i:s'),
            'updated_on' => date('Y-m-d H:i:s')
        );


        $status = $db->perform('retaline_emailsms_queue', $test);
        $data = compact("phone", "content", "response");
        $seldata = array(
            'smsemail_id' => $data['phone'],
            'smsemail_text' => $data['content'],
            'smsemail_datetime' => date('Y-m-d H:i:s'),
            'sms_responseid' => $data['response']
        );
        $status = $db->perform('sms_email_logs', $seldata);

        file_put_contents('php://stderr', print_r("Twiliooooo --" . $data . " \n.", TRUE));

        return $response;
    }

    public static function requestEquenceSmsCURL($phone, $type, $content, $tempid)
    {
        $db = new sqlDb(DSN);
        $url = EQC_API_URL;

        $fields = array(
            'username'  => EQC_USER,
            'peId'      => EQC_PEID,
            'password'  => EQC_PASSWORD,
            'from'      => EQC_SENDER_NUM,
            'to'        => $phone,
            'tmplId'    => $tempid,
            'text'      => $content
        );

        $callurl = $url . '?' . http_build_query($fields, '', '&', PHP_QUERY_RFC3986);

        $phonecode = PHONECODE;
        /*$pos = strpos($phone, $phonecode);
        if ($pos == false) {
            $mobile = str_replace($phonecode, '', $phone);
        }
        $response = '';*/
        $isMobileConfigured = $db->getItemFromDB("SELECT COUNT(*) FROM test_mobile WHERE mobile = '{$phone}'");
        if ($isMobileConfigured == 0) {
            $opts = array(
                CURLOPT_URL => $callurl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            );
            $ch = curl_init();
            curl_setopt_array($ch, $opts);
            $response = curl_exec($ch);
            curl_close($ch);
        }


        $test = array(
            'sender_id' => EQC_USER,
            'sender_name' => EQC_SENDER_NUM,
            'receiver_id' => "velosit_sms@yopmail.com",
            'type' => 2,
            'extra_info' => $phone . "-" . $callurl,
            'text_message' => $content,
            'created_on' => date('Y-m-d H:i:s'),
            'updated_on' => date('Y-m-d H:i:s')
        );


        $status = $db->perform('retaline_emailsms_queue', $test);
        $data = compact("phone", "content", "response");
        $seldata = array(
            'smsemail_id' => $data['phone'],
            'smsemail_text' => $data['content'],
            'smsemail_datetime' => date('Y-m-d H:i:s'),
            'sms_responseid' => $data['response']
        );
        $status = $db->perform('sms_email_logs', $seldata);

        file_put_contents('php://stderr', print_r("equence --" . $data . " \n.", TRUE));
        return (defined('DEBUG_SMS')) ? $data : $data;
    }
    public function getrandomstr($mobile)
    {
        //public function getrandomstr($length, $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789') {
        /*$result = '';
        $count = strlen($charset);
        for ($i = 0; $i < $length; $i++) {
            $result .= $charset[mt_rand(0, $count - 1)];
        }*/
        $result = SMSKEYWORD . strtoupper(substr(md5('now()' . $mobile), 0, 5));
        return $result;
    }
    public static function requestRouteSms($phone, $type, $content, $tempid)
    {
        $db = new sqlDb(DSN);
        $url = ROUTE_API_URL;

        $fields = array(
            'username'      => ROUTE_USER,
            'password'      => ROUTE_PASSWORD,
            'type'          => ROUTE_TYPE,
            'dlr'           => ROUTE_DLR,
            'source'        => ROUTE_SOURCE,
            'destination'   => $phone,
            'message'       => $content
        );

        $callurl = $url . '?' . http_build_query($fields, '', '&', PHP_QUERY_RFC3986);        
       
        $isMobileConfigured = $db->getItemFromDB("SELECT COUNT(*) FROM test_mobile WHERE mobile = '{$phone}'");
        if ($isMobileConfigured == 0) {
            $message = file_get_contents($callurl);
            $response = (is_array($message) || is_object($message)) ? json_encode($message) : $message;
        }


        $test = array(
            'sender_id' => ROUTE_USER,
            'sender_name' => ROUTE_SOURCE,
            'receiver_id' => "sms@yopmail.com",
            'type' => 2,
            'extra_info' => $phone . "-" . $callurl,
            'text_message' => $content,
            'extra_info' => $response,
            'created_on' => date('Y-m-d H:i:s'),
            'updated_on' => date('Y-m-d H:i:s')
        );


        $status = $db->perform('retaline_emailsms_queue', $test);
        $data = compact("phone", "content", "response");
        $seldata = array(
            'smsemail_id' => $data['phone'],
            'smsemail_text' => $data['content'],
            'smsemail_datetime' => date('Y-m-d H:i:s'),
            'sms_responseid' => $data['response']
        );
        $status = $db->perform('sms_email_logs', $seldata);

        file_put_contents('php://stderr', print_r("roeute --" . $data . " \n.", TRUE));
        return (defined('DEBUG_SMS')) ? $data : $data;
    }
}
