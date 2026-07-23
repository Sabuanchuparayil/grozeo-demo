<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of service
 *
 * @author jijutm
 */
class service {

    //put your code here

    public static function response($valid, $message, $data, $extra = false) {
        $out = array(
            "success" => true,
            "valid" => $valid,
            "message" => $message
        );
        if ($data !== false) {
            $out['data'] = $data;
        }
        if ($extra !== false) {
            $out['xtradata'] = $extra;
        }
        echo json_encode($out);
        exit();
    }

    public static function fail($message) {
        echo '{"success": false, "reason": "' . $message . '"}';
        exit();
    }

    public static function kill($message) {
        echo $message;
        exit();
    }

    public static function error($message, $data = false) {
        self::response(false, $message, $data);
    }

    public static function success($message, $data = false) {
        self::response(true, $message, $data);
    }

    public static function debug($var, $exit = true) {
        print_r($var);
        $exit && exit();
    }

    public static function saveUserLog($logDetails) {
        global $db;
        if(!is_array($logDetails)){
            $logDetails = array('comments' => $logDetails);
        }
        $logDetails['module'] = $_REQUEST['module'];
        $logDetails['operation'] = $_REQUEST['op'];
        $logDetails['uidnr_admin'] = $_SESSION['admin']->uidnr_admin;
        $logDetails['log_activity_on'] = date("Y-m-d H:i:s");
        $db->perform("iv_user_activity_log", $logDetails);
        return true;
    }

    public static function refreshiframe($seconds){
        echo '<html>'
        . '<head>'
                . '<meta http-equiv="refresh" content="'.$seconds.'">'
                . '</head>'
                . '<body></body>'
                . '</html>';
    }
}
