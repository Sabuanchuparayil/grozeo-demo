<?php
namespace App\Domains\EasyPay;
/**
 * while integration please use sendEasyPayRequest function along with parameters
 */
 
use Exception;
use App\Domains\Easypay\AesForJava;

class EasyPay extends AesForJava {

    function __construct($POST_URL = '', $CHECKSUM_KEY = '', $ENCRYPTION_KEY = '') {
        if (empty($POST_URL) || empty($CHECKSUM_KEY) || empty($ENCRYPTION_KEY)){
            throw new Exception('POST_URL, CHECKSUM_KEY and ENCRYPTION_KEY params are required', '001');
        }
        defined('POST_URL') || define('POST_URL', $POST_URL);
        defined('CHECKSUM_KEY') || define('CHECKSUM_KEY', $CHECKSUM_KEY);
        defined('ENCRYPTION_KEY') || define('ENCRYPTION_KEY', $ENCRYPTION_KEY);
    }

    function sendEasyPayRequest($cid = '', $rid = '', $crn = '', $amt = '', $ver = '', $typ = '', $cny = '', $rtu = '', $ppi = '', $re1 = 'MN', $re2 = '', $re3 = '', $re4 = '', $re5 = '') {
        $i = $this->createEasypayRequest($cid, $rid, $crn, $amt, $ver, $typ, $cny, $rtu, $ppi, $re1 = 'MN', $re2, $re3, $re4, $re5);
        header('Location:' . POST_URL . "?i=" . $i);
    }

    function calcCheckSum($cid, $rid, $crn, $amt, $key) {
        $str = $cid . $rid . $crn . $amt . $key;
        return hash("sha256", $str);
    }

    function createEasypayRequest($cid = '', $rid = '', $crn = '', $amt = '', $ver = '', $typ = '', $cny = '', $rtu = '', $ppi = '', $re1 = 'MN', $re2 = '', $re3 = '', $re4 = '', $re5 = '') {
        $req_params = array('CID', 'RID', 'CRN', 'AMT', 'VER', 'TYP', 'CNY', 'RTU', 'PPI');
        $checksumkey = CHECKSUM_KEY; /* ask easypay team for check-sum key */
        $encryption_key = ENCRYPTION_KEY; /* ask easypay team for encryption key */

        $arr = array(
            "CID" => $cid,
            "RID" => $rid,
            "CRN" => $crn,
            "AMT" => $amt,
            "VER" => $ver,
            "TYP" => $typ,
            "CNY" => $cny,
            "RTU" => $rtu,
            "PPI" => $ppi,
            "RE1" => $re1,
            "RE2" => $re2,
            "RE3" => $re3,
            "RE4" => $re4,
            "RE5" => $re5,
        );

        foreach ($arr as $key => $value) {
            if (in_array($key, $req_params) && empty($value)) {
                $missing_params[] = $key;
            }
        }

        if (!empty($missing_params)) {
            echo "MISSING PARAMETERS ARE : " . implode(' , ', $missing_params);
            exit;
        }

        $arr['CKS'] = $this->calcCheckSum($cid, $rid, $crn, $amt, $checksumkey);

        $aesJava = new AesForJava();
        $str = urldecode(http_build_query($arr));
        $value_i = $aesJava->encrypt($str, $encryption_key, 128);
        return $value_i;
    }

    public function callEasyPayEnquiry($cid = '', $rid = '', $crn = '', $ver = '', $typ = '') {
        $req_params = array('CID', 'RID', 'CRN', 'VER', 'TYP');

        $arr = array(
            "CID" => $cid,
            "RID" => $rid,
            "CRN" => $crn,
            "VER" => $ver,
            "TYP" => $typ,
        );

        foreach ($arr as $key => $value) {
            if (in_array($key, $req_params) && empty($value)) {
                $missing_params[] = $key;
            }
        }

        if (!empty($missing_params)) {
            echo "MISSING PARAMETERS ARE : " . implode(' , ', $missing_params);
            exit;
        }

        $arr['CKS'] = hash("sha256", $cid . $rid . $crn . CHECKSUM_KEY);

        if (!extension_loaded('CURL')) {
            echo 'Please enable CURL extension in php.ini';
            exit;
        }

        $aesJava = new AesForJava();
        $str = urldecode(http_build_query($arr));
        $value_i = $aesJava->encrypt($str, ENCRYPTION_KEY, 128);
        $param = array('i' => $value_i);
        $ch = curl_init(POST_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($param));
      //  curl_setopt($ch, CURLOPT_PROXY, 'http://idccfm.axisb.com:1050'); //Use Proxy wherever needed and change IP accordingly
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $returnData = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
            exit;
        }
        curl_close($ch);

        if (strpos($returnData, 'rror') && strpos($returnData, 'message')) {
            return $returnData;
        }

        return $dec_str = $aesJava->decrypt($returnData, ENCRYPTION_KEY, 128);
    }

        function callEasyPayPostTxnUpdate($cid = '', $rid = '', $crn = '',$tid = '') {
        $req_params = array('CID', 'RID', 'CRN', 'TID' );

        $arr = array(
            "CID" => $cid,
            "RID" => $rid,
            "CRN" => $crn,
            "TID" => $tid,
        );

        foreach ($arr as $key => $value) {
            if (in_array($key, $req_params) && empty($value)) {
                $missing_params[] = $key;
            }
        }

        if (!empty($missing_params)) {
            echo "MISSING PARAMETERS ARE : " . implode(' , ', $missing_params);
            exit;
        }

        $arr['CKS'] = hash("sha256", $cid . $rid . $crn. $tid . CHECKSUM_KEY);

        if (!extension_loaded('CURL')) {
            echo 'Please enable CURL extension in php.ini';
            exit;
        }

        $aesJava = new AesForJava();
        $str = urldecode(http_build_query($arr));
        $value_i = $aesJava->encrypt($str, ENCRYPTION_KEY, 128);

        $param = array('i' => $value_i);
        $ch = curl_init(POST_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($param));
        //curl_setopt($ch, CURLOPT_PROXY, '10.0.0.0:8080'); Use Proxy wherever needed and change IP accordingly
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
       echo $returnData = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
            exit;
        }
        curl_close($ch);

        if (strpos($returnData, 'rror') && strpos($returnData, 'message')) {
            return $returnData;
        }

        return $dec_str = $aesJava->decrypt($returnData, ENCRYPTION_KEY, 128);
    }
}
