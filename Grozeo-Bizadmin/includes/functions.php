<?php

function getClasses($class_name) {
    if (file_exists(ROOT . '/classes/' . $class_name . '.php'))
        include ROOT . '/classes/' . $class_name . '.php';
    
}

spl_autoload_register('getClasses');

function _loadJsonStore($sql, $jsonArray = true, $append = array(), $countQuery = '') {
    global $db;
    $results = $db->getMultipleData($sql, true);
    $n = count($results);
    if (count($append) > 0 && $results) {
        $results = array_merge($results, array($n => $append));
    }
    /* Edited by arun
     * Added new paramenter $countQuery
     */
    $jsonTotalCount = '';
    if ($countQuery != '') {
        $count = $db->getItemFromDB($countQuery);
        $totalCount = $count + count($append);
        $jsonTotalCount = '"totalCount":' . $count . ',';
    }

    if (!$results) {
        echo '{"success":true,' . $jsonTotalCount . '"data":';
        if (count($append) > 0) {
            echo '[' . json_encode($append) . ']}';
        } else {
            echo '[]}';
        }
    } else {
        if ($jsonArray) {
            echo '{"success":true,' . $jsonTotalCount . '"data":',
            json_encode($results),
            '}';
        } else {
            echo '{"data":',
            json_encode($results),
            '}';
        }
    }
}

function _loadRecordJson($sql) {
    global $db;
    $results = $db->getFromDB($sql, true);
    if (!$results) {
        echo '{"success":true,"data":[]}';
    } else {
        echo '{"success":true, "data":',
        json_encode($results),
        '}';
    }
}

// Legacy autoload hook removed for PHP 8+ compatibility.
// Class loading is handled by spl_autoload_register('getClasses') above.

/**
 * Purpose : Error log of modules
 */
function writeLog($file) {
    
}

/**
 * smtp mail function
 */
function sendMail($to, $subject, $content, $isHTML = true) {
    $meObj = new mimeEmail();


    $meObj->setBody($content, $isHTML);
    $mailMIME = $meObj->getMailMime();
    $from = '"' . DEFAULT_FROM_NAME . '" <' . DEFAULT_FROM_ADDRESS . '>';
    $headers = array("From : $from", "Reply-To: $from");
    $headers[] = $mailMIME['headers'];

    $header = join("\r\n", $headers);
    $smtp = new smtpSend(DEFAULT_FROM_ADDRESS);
    $send = $smtp->send($to, $subject, $mailMIME['mail'], $header);
}

/*
 *
 * * To Send Mail
 * @param $string
 *   To eMail Address
 *
 * @param $string
 *   Mail Subject to be used.
 *
 * @param $string
 *   Mail content to be send.
 *
 * @param $boolean
 *   Specify whether it is a html email or not.
 *
 * @param $boolean
 *   Specify whether we need to save to email_log table. Default: false
 *
 * @param $string
 *   Specify on what event (like user_register, forgot_password, etc.) this send mail request executed
 *
 * @return	boolean
 * 	 Return TRUE on success
 * 	 Return FALSE on failure
 *
 */

function old_send_mail($to, $subject, $content, $html_mail = true, $save_log = false, $mail_event = '') {
    $content = stripslashes($content);
    /* to send mail */
    include("./includes/saturn/phpmailer/saturnmailer.php");
    $mail = new SaturnMailer();
    $mail->From = DEFAULT_FROM_ADDRESS;
    $mail->FromName = DEFAULT_FROM_NAME;
    $mail->AddAddress($to);
    $mail->Body = $content;
    $mail->IsHTML($html_mail);
    $mail->Subject = $subject;
    $status = $mail->Send();
    unset($mail);

    if ($save_log) {
        save_email_log($to, $mail_event, $content, $subject, $status);
    }

    return $status;
}

/**
 * Prepare for sending email
 *
 * @author : Ratheesh Kumar CK <ratheesh@saturn.in>
 * @Created On: November 04, 2009 02:50PM
 *
 * @param   $string   Recipient's email address
 * @param   $string   Subject of the email
 * @param   $string   Email Content
 * @param   $boolean  Indicates whether it is an html email of plain text email
 * @param   $string   The system event, when the email request is fired.
 *
 * @return  void
 *
 * Based on the inputs,
 * - All the details will be saved to email log table
 * - Then the function will initiate a Background process to which
 *   calls an OP in UI module
 *
 */
function send2email_queue($to, $subject, $content, $html_mail = true, $mail_event = '', $attachments = '') {
    $logID = save_email_log($to, $mail_event, $content, $subject, $status, $attachments);
    $cmd = executePHPCLICMD(array('module' => 'ui', 'op' => 'sendMailFromQueue', 'logId' => $logID));
    return true;
}

function send_mail($to, $subject, $content, $html_mail = true, $save_log = false, $mail_event = '', $attachments = '') {
    $content = stripslashes($content);
    /* to send mail */
    require_once(INCLUDE_PATH . "/phpMailer_v2.3/class.phpmailer.php");
    $mail = new PHPMailer();
    $subparts = explode('proof', strtolower($subject));
    if (sizeof($subparts) > 1)
        $mail->From = 'mail@saturn.in';
    else
        $mail->From = DEFAULT_FROM_ADDRESS;
    $mail->FromName = DEFAULT_FROM_NAME;
    $mail->AddAddress($to);
    $mail->Body = $content;
    $mail->IsHTML($html_mail);
    $mail->Subject = $subject;
    if ($attachments != '') {

        $attachments = unserialize($attachments);
        for ($i = 0; $i < $size; $i++) {

            $mail->AddAttachment($attachments[$i]);
        }
    }

    $status = $mail->Send();
    unset($mail);

    if ($save_log) {

        save_email_log($to, $mail_event, $content, $subject, $status, $attachments);
    }

    return $status;
}

/*  Use to save email log- */

function save_email_log($recipient, $event, $content, $subject, $status = '', $attachments = '') {
    global $db;

    $email_status = 'Waiting';
    if ($status === true)
        $email_status = 'Sent';
    if ($status === false)
        $email_status = 'Failed';

    //1. Insert log details into 'email_log' table
    $data = array(
        'Recipient' => $recipient,
        'Subject' => $subject,
        'MailEventId' => $event,
        'ContentAvailable' => (trim($content) != "") ? "yes" : "no",
        'SentOn' => "now()",
        'Status' => $email_status);
    $status = $db->perform('sys_email_log', $data);

    //2. Get the last inserted id.
    $last_id = $db->insert_id();

    //3. Based on the last inserted id, insert data into 'email_log_content' table
    $data = array(
        'EmailLogId' => $last_id,
        'Recipient' => $recipient,
        'Content' => $content//,
            //'Attachments' => $attachments
    );
    $status = $db->perform('sys_email_log_content', $data);

    return $last_id;
}

/**
 * Prepare Command Line statement for executing PHP *
 *
 * @author : Ratheesh Kumar CK <ratheesh@saturn.in>
 *
 * @param $array
 *     An associate array, contains the parameters needs to be send to php through shell command
 * @param [$string]
 *     File needs to be executed. Default to index.php
 * @param [$mixed]
 *     The lengthy data needs to be passed to the command through db (otherwise it is not possible to pass lengthy data through shell command). If it is array, it needs to serialized first
 * @param [$boolean]
 *     Indicates whether the system needs to save the command along with processID, etc details in `tmp_shell_process` table
 *
 * @return $string
 *     Prepared Command Line statement.
 *
 * Based on the inputs, this function returns the php command in the following format:
 * php <filename>.php module=workorder op=sendModifiedNotification <arg3_key>=<arg3_value>
 *
 */
function executePHPCLICMD($args, $file = 'index.php', $extraData = FALSE, $keepLog = FALSE) {
    global $db;
    $CMD = 'php ' . $file;

    foreach ($args as $key => $val)
        $CMD .= ' ' . $key . '=' . $val;

    if ($extraData || $keepLog) {
        $processQueueID = intval($db->getItemFromDB('SELECT COALESCE(MAX(TmpId),0) + 1 FROM tmp_shell_process;'));

        //Save the Details into Process Table.
        $db->perform('tmp_shell_process', array(
            'TmpId' => $processQueueID,
            'Command' => $CMD,
            'InputData' => is_array($extraData) ? serialize($extraData) : $extraData,
            'KeepLogAfterProcess' => $keepLog ? 'Yes' : 'No'
        ));
        $CMD .= ' processQueueID=' . $processQueueID;
    }

    if (!defined(PROCESS_CLASS))
        if (defined(INCLUDE_PATH))
            require_once (INCLUDE_PATH . '/process.php');
        else
            require_once ('./includes/process.php');

    file_put_contents('/tmp/so_bgproc_cmds.log', $CMD . "\n", FILE_APPEND);

    // echo $CMD."\n";  // This will print preflight command you need to use in Terminal
    $process = new Process($CMD);
    $sysProcessID = $process->getPid();

    if ($extraData || $keepLog) {
        //Update Process Log record with Process Id
        $db->perform('tmp_shell_process', array('SysProcessId' => $sysProcessID), 'update', 'TmpId= ' . $processQueueID);
    }

    //Return Process ID
    return $sysProcessID;
}

/*
 * Name       : encrypt_decrypt
 * Description: Function will perform xor operation to convert  a string. Which
 *              can be used as encryption or decryption.
 * Author     : Sreeram P
 * Source     : http://www.weberdev.com/get_example-4118.html
 * Parameters : String, need to be 'xor' ed
 * Return     : Cipher text
 */

function encrypt_decrypt($Str_Message) {

    // Finds length of the string
    $Len_Str_Message = STRLEN($Str_Message);
    // Following will store cipher text
    $Str_Encrypted_Message = "";

    //Iterates through a string. Start from position 0
    for ($Position = 0; $Position < $Len_Str_Message; $Position++) {

        // Generting the key.
        //Length of string + Position + 1
        $Key_To_Use = (($Len_Str_Message + $Position) + 1); // (+5 or *3 or ^2)
        //after that we need a module division because can´t be greater than 255
        $Key_To_Use = (255 + $Key_To_Use) % 255;
        // Take this particular character
        $Byte_To_Be_Encrypted = SUBSTR($Str_Message, $Position, 1);
        // Find ascii code
        $Ascii_Num_Byte_To_Encrypt = ORD($Byte_To_Be_Encrypted);
        //xor operation
        $Xored_Byte = $Ascii_Num_Byte_To_Encrypt ^ $Key_To_Use;
        // ascii to character once again
        $Encrypted_Byte = CHR($Xored_Byte);
        $Str_Encrypted_Message .= $Encrypted_Byte;
    }
    return $Str_Encrypted_Message;
}

/*
 * Name       : sortArrayByArray
 * Description: Sorts an associative array based on another array oy keys
 * Author     : Sreeram P
 * Parameters : 1. Array to be sorted
 *              2. Array of keys in ordered form
 * Return     : Sorted array
 */

function sortArrayByArray($array, $orderArray) {
    $ordered = array();
    foreach ($orderArray as $key) {
        if (array_key_exists($key, $array)) {
            $ordered[$key] = $array[$key];
            unset($array[$key]);
        }
    }
    return $ordered + $array;
}

/*
 * Name       : generateTemplateVariables
 * Description: generate an array of template variables from mail_template and corresponding tables
 * Author     : Lakshmi J
 * Return     : Array
 */

function generateMail($voucherId, $customerId) {
    global $db;
    include_once(ROOT . "/includes/SysRequestTokenHandler.php");
    $sysEmailToken = new SysRequestTokenHandler($db);

    $tpl = $db->getFromDB('select * from sys_email_template WHERE MailTemplateId=1', true);
    $voucherData = $db->getFromDB("SELECT PurposeLabel,TotalAmount,ExpiryDate,MinAmount FROM admin_voucher a,admin_purpose b WHERE a.PurposeId=b.PurposeId AND VoucherId=$voucherId AND CustomerId = $customerId");
    $customerData = $db->getFromDB("SELECT CONCAT(FirstName,' ',LastName) as CustomerName,Email FROM admin_customer WHERE CustomerId=$customerId");
    $templateVariables = array("[CustomerName]", "[Purpose]", "[TotalAmount]", "[ValidTill]", "[From]", "[Payment Link To Customer]");
    $templateReplaceVariables = array($customerData[0], $voucherData[0], $voucherData[1], $voucherData[2], "Support @ Saturn");

    $argumentArray = array(
        "email_action" => array(
            "TODO" => "REDIRECT",
            "data" => array(
                "VoucherId" => $voucherId,
                "TotalAmount" => $voucherData[2],
                "MinAmount" => $voucherData[3],
                "CustomerId" => $customerId
            )
        )
    );
    $templateReplaceVariables[] = $argumentArray;

    foreach ($templateVariables as $tKey => $tVal) {
        if (is_array($templateReplaceVariables[$tKey])) {
            $templateReplaceVariables[$tKey] = $sysEmailToken->setToken($templateReplaceVariables[$tKey]);
        }
    }
    $CustomerEmailId = $customerData[1];
    $status = send2email_queue($CustomerEmailId, str_ireplace($templateVariables, $templateReplaceVariables, $tpl['MailSubject']), str_ireplace($templateVariables, $templateReplaceVariables, $tpl['MailTemplateContents']), true, 1);
    if ($status) {
        $db->perform('admin_voucher', array('StatusId' => 2), 'update', 'VoucherId=' . $voucherId);
    }
}

function deleteAll($directory, $empty = false) {
    if (substr($directory, -1) == "/") {
        $directory = substr($directory, 0, -1);
    }

    if (!file_exists($directory) || !is_dir($directory)) {
        return false;
    } elseif (!is_readable($directory)) {
        return false;
    } else {
        $directoryHandle = opendir($directory);

        while ($contents = readdir($directoryHandle)) {
            if ($contents != '.' && $contents != '..') {
                $path = $directory . "/" . $contents;

                if (is_dir($path)) {
                    deleteAll($path);
                } else {
                    unlink($path);
                }
            }
        }

        closedir($directoryHandle);

        if ($empty == false) {
            if (!rmdir($directory)) {
                return false;
            }
        }

        return true;
    }
}

/**
 * @author Kavitha<kavitha@saturn.in>
 * @17th Nov 2011
 * Function to compress file as gzip
 * @param type $srcFileName
 * @param type $dstFileName 
 */
function compress($srcFileName, $dstFileName) {
    // getting file content
    $fp = fopen($srcFileName, "r");
    $data = fread($fp, filesize($srcFileName));
    fclose($fp);


    // writing compressed file
    $zp = gzopen64($dstFileName, "w9");
    gzwrite($zp, $data);
    gzclose($zp);
}

function actionDW($table, $data, $dw, $action, $condition = '') {

    // for multipe entries from the same web / ajax event  loop
    // to add the database action insert|update|delete
    $dw->appendData($action);

    // to add the table name
    $dw->appendData($table);

    // to add the fields names ( delete event will not need this )
    ($action !== 'delete') && $dw->appendData(join(',', array_keys($data)));

    // to make sure strings with ',' is correctly encoded
    ($action !== 'delete') && $dw->appendData($dw->getAsCsv(array_values($data), "\t"));

    // if needed a condition to be added (insert event will not need this )
    (!empty($condition)) && $dw->appendData($condition);

    // to close one database action
    $dw->appendData('^');

    // for multipe entries from the same web / ajax event loop till here
}

function reqFieldValidation($fields) {
    foreach ($fields as $k => $val) {
        if ($val == '')
            return 'Please enter ' . $k;
    }
}

/**
 * @author Kavitha <kavitha@saturn.in>
 * @29th FEB 2012
 * @global <obj> $db 
 * @param <array> $combo 
 * @return <string> 
 * 
 * use
 * $comboFields = array("State" => array("cond"=> "st_ID=".intval($_POST['c_State']),"table"=>"state",$_POST['c_State']),
  "District" => array("cond"=> "dst_Id=".intval($_POST['c_District'])." and st_Id=".intval($_POST['c_State']),"table"=>"district",$_POST['c_District']),
  "Branch" => array("cond"=> "br_ID=".intval($_POST['br_Id']),"table"=>"branch",$_POST['br_Id']),
  "Content Type"=> array("cond"=> "ct_ID=".intval($_POST['ct_ID']),"table"=>"contenttype",$_POST['ct_ID']),
  "Place"=> array("cond"=> "p_ID=".intval($_POST['p_ID']),"table"=>"place",$_POST['p_ID']));
  $cMsg = checkValidationCombo($comboFields);
 */
function checkValidationCombo($combo) {
    global $db;
    foreach ($combo as $k => $v) {
        foreach ($v as $key => $val) {
            if ($key == 'table')
                continue;
            if (!empty($v[0]) && $v[0] != '') {
                $qry = "select count(*) from " . $v['table'] . " where " . $val;
                if ($db->getItemFromDB($qry) == 0)
                    return 'Please select valid data in ' . $k;
            }
        }
    }
}

function delRateTmpData() {

    global $db;

    $db->query("DELETE FROM tmprateheadsdetails WHERE TIMEDIFF(NOW(),tmrrt_date) > TIME('24:00:00');");
}

function delQutonTmpData() {

    global $db;

    $db->query("DELETE FROM tmpquoteheadsdetails WHERE TIMEDIFF(NOW(),tmrrt_date) > TIME('24:00:00');");
}

//for add new Rate settings,get a unique id
function getUniqId() {

    $UniqId = uniqid();
    $_SESSION['UniqId'] = $UniqId;
    echo "{success: true, UniqId: '" . $UniqId . "'}";
}

function AssociateNearestLocations($brid, $lat, $long) {
    global $db;
    $QC_BRANCH_CIRCLE_DIST = $db->getItemFromDB("select keyvalue  from global_config where  keyname ='QC_BRANCH_CIRCLE_DIST'");
    $QC_BRANCH_CIRCLE_DIST_RANGE = $db->getItemFromDB("select keyvalue  from global_config where  keyname ='QC_BRANCH_CIRCLE_DIST_RANGE'");
    $locs = new cgoGeoUtilities();
    $locdata = $locs->getNearestAerialLocations($long, $lat, $QC_BRANCH_CIRCLE_DIST);
    $assoc_brloid = array();
    foreach ($locdata as $key => $data) {
        if ($data['distance'] <= $QC_BRANCH_CIRCLE_DIST) {
            $dist = $locs->GetDrivingDistance($lat, $data['brlo_Lati'], $long, $data['brlo_Long']);
            if ((int) $data['br_id'] <= 1) {
                $upd = array('brlo_Distance' => $dist, 'br_id' => $brid);
                $cond = 'brlo_id=' . $data['brlo_id'];
                array_push($assoc_brloid, $data['brlo_id']);
                $db->perform('branch_location', $upd, 'update', $cond);
            } else {
                if (((($data['brlo_Distance'] - $dist) >= $QC_BRANCH_CIRCLE_DIST_RANGE) && ($data['brlo_Distance'] - $dist) >= 0) || $data['brlo_Distance'] == 0) {
                    $upd = array('brlo_Distance' => $dist, 'br_id' => $brid);
                    $cond = 'brlo_id=' . $data['brlo_id'];
                    array_push($assoc_brloid, $data['brlo_id']);
                    $db->perform("branch_location", $upd, 'update', $cond);
                }
            }
        }
    }
    return $assoc_brloid;
}

function AssociateNearestBranch($brloid, $brlodistance, $brid, $lat, $long) {
    global $db;
    $QC_BRANCH_CIRCLE_DIST = $db->getItemFromDB("select keyvalue  from global_config where  keyname ='QC_BRANCH_CIRCLE_DIST'");
    $QC_BRANCH_CIRCLE_DIST_RANGE = $db->getItemFromDB("select keyvalue  from global_config where  keyname ='QC_BRANCH_CIRCLE_DIST_RANGE'");
    $locs = new cgoGeoUtilities();
    $locdata = $locs->getNearestAerialBranches($long, $lat, $QC_BRANCH_CIRCLE_DIST, false);

    foreach ($locdata as $key => $data) {
        if ($data['distance'] <= $QC_BRANCH_CIRCLE_DIST) {
            $dist = $locs->GetDrivingDistance($lat, $data['br_Lati'], $long, $data['br_Lng']);
            if (((($brlodistance - $dist) >= $QC_BRANCH_CIRCLE_DIST_RANGE) && ($brlodistance - $dist) >= 0 && $brid != $data['br_ID']) || $brlodistance == 0) {
                $brlodistance = $dist;
                $brid = $data['br_ID'];
                $upd = array('brlo_Distance' => $dist, 'br_id' => $data['br_ID']);
                $cond = 'brlo_id=' . $brloid;
                $db->perform("branch_location", $upd, 'update', $cond);
            }
            return true;
        }
    }
    return false;
}

function ReCalcLocationDistance($brloids, $brid = 0, $brlat = 0, $brlong = 0) {
    global $db;
    $brloid_str = implode(",", $brloids);
    if ($brloid_str !== '') {
        $qry = "SELECT brlo_id,br_id,brlo_Lati,brlo_Long FROM branch_location WHERE " . ($brid > 0 ?
                        " br_id = " . $brid . " and " : "") . "  brlo_id NOT IN(" . $brloid_str . ")";
    } else {
        $qry = "SELECT brlo_id,br_id,brlo_Lati,brlo_Long FROM branch_location  " . ($brid > 0 ?
                        " WHERE br_id = " . $brid : "");
    }
    $rd = $db->getMultipleData($qry, true);
    foreach ($rd as $k => $value) {
        if (!AssociateNearestBranch($value['brlo_id'], 0, $value['br_id'], $value['brlo_Lati'], $value['brlo_Long'])) {
            $locs = new cgoGeoUtilities();
            $dist = $locs->GetDrivingDistance($brlat, $value['brlo_Lati'], $brlong, $value['brlo_Long']);
            $upd = array('brlo_Distance' => $dist, 'br_id' => $value['br_id']);
            $cond = 'brlo_id=' . $value['brlo_id'];
            $db->perform("branch_location", $upd, 'update', $cond);
        }
    }
}

function checkTypeAhead($field) {
    global $db;
    $typeAhead = '';
    if (!empty($_POST['query'])) {
        $typeAhead = sprintf(" AND %s LIKE '%s%%'", $field, $db->sanitizeString($_POST['query']));
    }
    return $typeAhead;
}
function getJsonKeyArray($sql) {
    global $db;
    $items = $db->getMultipleData($sql);
    if ($items) {
        echo "[";
        $n = count($items);
        $i = 1;
        foreach ($items as $item) {
            echo "[\"{$item[0]}\", \"{$item[1]}\"]";
            if ($n > $i) {
                echo ",";
            }
            $i++;
        }
        echo "]";
    }
}
function numberTowords($num) {
    $ones = array(
        1 => "one",
        2 => "two",
        3 => "three",
        4 => "four",
        5 => "five",
        6 => "six",
        7 => "seven",
        8 => "eight",
        9 => "nine",
        10 => "ten",
        11 => "eleven",
        12 => "twelve",
        13 => "thirteen",
        14 => "fourteen",
        15 => "fifteen",
        16 => "sixteen",
        17 => "seventeen",
        18 => "eighteen",
        19 => "nineteen"
    );
    $tens = array(
        1 => "ten",
        2 => "twenty",
        3 => "thirty",
        4 => "forty",
        5 => "fifty",
        6 => "sixty",
        7 => "seventy",
        8 => "eighty",
        9 => "ninety"
    );
    $hundreds = array(
        "hundred",
        "thousand",
        "million",
        "billion",
        "trillion",
        "quadrillion"
    ); //limit t quadrillion 
    $num = number_format($num, 2, ".", ",");
    $num_arr = explode(".", $num);
    $wholenum = $num_arr[0];
    $decnum = $num_arr[1];
    $whole_arr = array_reverse(explode(",", $wholenum));
    krsort($whole_arr);
    $rettxt = "";
    foreach ($whole_arr as $key => $i) {
        if ($i < 20) {
            $rettxt .= $ones[$i];
        } elseif ($i < 100) {
            $rettxt .= $tens[substr($i, 0, 1)];
            $rettxt .= " " . $ones[substr($i, 1, 1)];
        } else {
            $rettxt .= $ones[substr($i, 0, 1)] . " " . $hundreds[0];
            $rettxt .= " " . $tens[substr($i, 1, 1)];
            $rettxt .= " " . $ones[substr($i, 2, 1)];
        }
        if ($key > 0) {
            $rettxt .= " " . $hundreds[$key] . " ";
        }
    }
    if ($decnum > 0) {
        $rettxt .= " and ";
        if ($decnum < 20) {
            $rettxt .= $ones[$decnum];
        } elseif ($decnum < 100) {
            $rettxt .= $tens[substr($decnum, 0, 1)];
            $rettxt .= " " . $ones[substr($decnum, 1, 1)];
        }
    }
    return $rettxt;
}

function orderBy($data, $field) {
    $code = "return strnatcmp(\$a['$field'], \$b['$field']);";
    usort($data, create_function('$a,$b', $code));
    return $data;
}
