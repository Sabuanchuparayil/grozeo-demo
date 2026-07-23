<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
define('SITE_PATH', dirname(dirname(__FILE__)));
define('CLASS_PATH', SITE_PATH . "/classes");
define('SCHEDULER_PATH', dirname(__FILE__));
define('INCLUDE_PATH', SITE_PATH . "/includes");
define('ADMIN_PATH', SITE_PATH);
define('AWS_ROOT', '/home/system/awsapi');
include(INCLUDE_PATH . "/config.php");
include(INCLUDE_PATH . "/lib.php");
//include(CLASS_PATH . "/sqlDb.php");
include(SITE_PATH . '/finascop_config/config.php');
include(SITE_PATH . '/finascop_config/lib.php');
require(AWS_ROOT . '/aws-autoloader.php');

class schoutboundJobs
{


    public function createOnboardingJobs($db, $supportdb)
    {
        $fields['pendingOnly'] = 0;
        $fields['streogroupId'] = 0;
        $url =  $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'ONBOARDING_JOBS'");
        $url = $url . "?pendingOnly=1&streogroupId=0";
        $fields_string = json_encode($fields);
        $opts = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $datacl = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        header("Content-Type: application/json");
        $result = json_decode($datacl, true);

        if ($result['result'] == 1 && $result['status'] == 'Success') {
            $outboundEvents = $result['data'];
            foreach ($outboundEvents as $outboundEvent) {
                $isJob = $supportdb->getItemFromDB("SELECT COUNT(*) FROM outbound_jobs WHERE calleeId = {$outboundEvent['APIStoregroupId']} AND eventId = 1");
                if ($isJob == 0) {
                    $expectedEntry = $db->getFromDB("SELECT store_group_id,store_group_name,br_Email,br_Phone,IF(contactNo IS NULL,contactNumber,contactNo) AS contactNumber FROM finascop_branch_group 
                         INNER JOIN finascop_branch ON br_storeGroup = store_group_id 
                         LEFT JOIN contact_preference ON userId = store_group_id AND userType = 2
                         WHERE store_group_id = {$outboundEvent['APIStoregroupId']} GROUP BY br_storeGroup", true);
                    $eventRank =  $supportdb->getItemFromDB("SELECT `rank` FROM support_user_events WHERE id = 1");
                    $jobData['eventId'] = 1;
                    $jobData['jobTitle'] = 'Onboarding Call for -' . $expectedEntry['store_group_name'];
                    $jobData['calleeId'] = $expectedEntry['store_group_id'];
                    $jobData['calleeMobile'] = $expectedEntry['contactNumber'];
                    $jobData['calleeName'] = $expectedEntry['store_group_name'];
                    $jobData['calleeType'] = 2;
                    $jobData['eventRank'] = $eventRank;
                    $jobData['status'] = 1;
                    //$jobData['createdBy'] = $_SESSION['admin']->UserId;
                    if ($expectedEntry['store_group_id'] > 0 && !empty($expectedEntry['contactNumber'])) {
                        $supportdb->perform('outbound_jobs', $jobData);
                    }
                }
            }
        }
    }

    public function createWelcomeJobs($db, $supportdb)
    {
        $availableMerchants = $db->getMultipleData("SELECT store_group_id,store_group_name,br_Email,br_Phone,IF(contactNo IS NULL,contactNumber,contactNo) AS contactNumber FROM finascop_branch_group fbg 
        INNER JOIN finascop_branch fb ON br_storeGroup = store_group_id 
        LEFT JOIN contact_preference ON userId = store_group_id AND userType = 2 
        WHERE fbg.created_on < CURDATE() GROUP BY fbg.store_group_id", true);
        $outboundEvent =  $supportdb->getFromDB("SELECT * FROM support_user_events WHERE id = 2", true);
        foreach ($availableMerchants as $availableMerchant) {
            $isJob = $supportdb->getItemFromDB("SELECT COUNT(*) FROM outbound_jobs WHERE calleeId = {$availableMerchant['store_group_id']} AND eventId = 2");
            if ($isJob == 0) {
                $incompleteOnboardingJobs = $supportdb->getFromDB("SELECT id,calleeId,calleeMobile,status FROM outbound_jobs WHERE eventId = 1 AND calleeId = {$availableMerchant['store_group_id']}", true);

                if ($incompleteOnboardingJobs['id'] > 0) {
                    if ($incompleteOnboardingJobs['status'] == 3) {
                        $jobData['eventId'] = $outboundEvent['id'];
                        $jobData['jobTitle'] = $outboundEvent['eventName'] . '-' . $availableMerchant['store_group_name'];
                        $jobData['calleeId'] = $availableMerchant['store_group_id'];
                        $jobData['calleeName'] = $availableMerchant['store_group_name'];
                        $jobData['calleeMobile'] = $availableMerchant['contactNumber'];
                        $jobData['calleeType'] = 2;
                        $jobData['eventRank'] = $outboundEvent['rank'];
                        $jobData['status'] = 1;
                        if ($availableMerchant['store_group_id'] > 0 && !empty($availableMerchant['contactNumber'])) {
                            $supportdb->perform('outbound_jobs', $jobData);
                        }
                    }
                } else {
                    $jobData['eventId'] = $outboundEvent['id'];
                    $jobData['jobTitle'] = $outboundEvent['eventName'] . '-' . $availableMerchant['store_group_name'];
                    $jobData['calleeId'] = $availableMerchant['store_group_id'];
                    $jobData['calleeName'] = $availableMerchant['store_group_name'];
                    $jobData['calleeMobile'] = $availableMerchant['contactNumber'];
                    $jobData['calleeType'] = 2;
                    $jobData['eventRank'] = $outboundEvent['rank'];
                    $jobData['status'] = 1;
                    if ($availableMerchant['store_group_id'] > 0 && !empty($availableMerchant['contactNumber'])) {
                        $supportdb->perform('outbound_jobs', $jobData);
                    }
                }
            }
        }
    }

    public function socialMarketingJobs($db, $supportdb)
    {
        $finishedFirstEvents = $supportdb->getMultipleData("SELECT * FROM outbound_jobs WHERE eventId = 2 AND status = 3", true);
        $outboundEvent =  $supportdb->getFromDB("SELECT * FROM support_user_events WHERE id = 3", true);
        foreach ($finishedFirstEvents as $finishedFirstEvent) {
            $isAvailable = $supportdb->getItemFromDB("SELECT COUNT(*) FROM outbound_jobs WHERE calleeId = {$finishedFirstEvent['calleeId']} AND eventId = 3");
            if ($isAvailable == 0) {
                $secEveData['eventId'] = $outboundEvent['id'];
                $secEveData['jobTitle'] = $outboundEvent['eventName'] . '-' . $finishedFirstEvent['calleeName'];
                $secEveData['calleeId'] = $finishedFirstEvent['calleeId'];
                $secEveData['calleeName'] = $finishedFirstEvent['calleeName'];
                $secEveData['calleeMobile'] = $finishedFirstEvent['calleeMobile'];
                $secEveData['calleeType'] = 2;
                $secEveData['eventRank'] = $outboundEvent['rank'];
                $secEveData['status'] = 1;
                $supportdb->perform('outbound_jobs', $secEveData);
            }
        }
    }

    public function incompleteOrderJobs($db, $supportdb)
    {
        $incompleteOrders = $db->getMultipleData("SELECT order_customer_id,cust_customer_name,cust_mobile,order_order_id FROM finascop_stock_transfer_order 
        INNER JOIN retaline_customer_order ON order_id = fstr_id 
        INNER JOIN retaline_customer ON cust_id = order_customer_id WHERE fsto_ordertype = 1 AND fsto_status = 9", true);
        $outboundEvent =  $supportdb->getFromDB("SELECT * FROM support_user_events WHERE id = 14", true);
        foreach ($incompleteOrders as $finishedFirstEvent) {
            $isAvailable = $supportdb->getItemFromDB("SELECT COUNT(*) FROM outbound_jobs WHERE orderRefrenceId = '{$finishedFirstEvent['order_order_id']}' AND eventId = 14");
            if ($isAvailable == 0) {
                $incEveData['eventId'] = $outboundEvent['id'];
                $incEveData['jobTitle'] = $outboundEvent['eventName'] . '-' . $finishedFirstEvent['cust_customer_name'];
                $incEveData['calleeId'] = $finishedFirstEvent['order_customer_id'];
                $incEveData['calleeName'] = $finishedFirstEvent['cust_customer_name'];
                $incEveData['calleeMobile'] = $finishedFirstEvent['cust_mobile'];
                $incEveData['orderRefrenceId'] = $finishedFirstEvent['order_order_id'];
                $incEveData['calleeType'] = 1;
                $incEveData['eventRank'] = $outboundEvent['rank'];
                $incEveData['status'] = 1;
                $supportdb->perform('outbound_jobs', $incEveData);
            }
        }
    }

    public function deliveryDeficientOrderJobs($db, $supportdb)
    {
        $deliveryDeficientOrders = $db->getMultipleData("SELECT order_customer_id,store_group_name,CONCAT(store_group_name,'-',br_Name) as calleeName,store_group_id,br_Email,br_Phone,IF(contactNo IS NULL,contactNumber,contactNo) AS contactNumber,order_order_id FROM finascop_stock_transfer_order 
        INNER JOIN retaline_customer_order ON order_id = fstr_id 
        INNER JOIN finascop_branch ON br_ID = order_branch_id 
        INNER JOIN finascop_branch_group ON store_group_id = br_storeGroup 
        LEFT JOIN contact_preference ON userId = store_group_id AND userType = 2 WHERE fsto_ordertype = 1 AND fsto_status = 21", true);
        $outboundEvent =  $supportdb->getFromDB("SELECT * FROM support_user_events WHERE id = 15", true);
        foreach ($deliveryDeficientOrders as $finishedFirstEvent) {
            $isAvailable = $supportdb->getItemFromDB("SELECT COUNT(*) FROM outbound_jobs WHERE orderRefrenceId = '{$finishedFirstEvent['order_order_id']}' AND eventId = 15");
            if ($isAvailable == 0) {
                $ddlEveData['eventId'] = $outboundEvent['id'];
                $ddlEveData['jobTitle'] = $outboundEvent['eventName'] . '-' . $finishedFirstEvent['calleeName'];
                $ddlEveData['calleeId'] = $finishedFirstEvent['store_group_id'];
                $ddlEveData['calleeName'] = $finishedFirstEvent['store_group_name'];
                $ddlEveData['calleeMobile'] = $finishedFirstEvent['contactNumber'];
                $ddlEveData['orderRefrenceId'] = $finishedFirstEvent['order_order_id'];
                $ddlEveData['calleeType'] = 2;
                $ddlEveData['eventRank'] = $outboundEvent['rank'];
                $ddlEveData['status'] = 1;
                $supportdb->perform('outbound_jobs', $ddlEveData);
            }
        }
    }

    public function moveCallRecording($db, $supportdb)
    {
        $callRecords = $supportdb->getMultipleData("SELECT * FROM call_communications WHERE AudioFile <> '' AND AudioPath IS NULL", true);
        foreach ($callRecords as $callRecord) {

            //copy audio file to our s3
            $audioFilePath = $callRecord['AudioFile'];
            // Extract the actual filename with any extension (e.g., .wav, .pdf, .zip, etc.)
            preg_match('/([^\/]+\.[a-zA-Z0-9]+)(?=\/|$)/', $audioFilePath, $matches);
            $originalFileName = $matches[1] ?? 'unknown.tmp';

            // Extract extension dynamically
            $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);

            // Generate a unique file name with the same extension
            $fileuploadname = trim(str_replace('.', '', uniqid("", true))) . '.' . $extension;
            $destinationPath = ADMIN_PATH . '/tmp/audiofile/' . $fileuploadname;

            // --- Check remote file existence using cURL ---
            $ch = curl_init($audioFilePath);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // skip SSL verify (port 81 / internal certs)
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // --- Proceed if file exists remotely ---
            if ($httpCode == 200) {
                $fileData = @file_get_contents($audioFilePath);
                if ($fileData !== false) {
                    file_put_contents($destinationPath, $fileData);

                    $s3upload = new \cgoS3FileHandler();
                    $cloudFrontPath = 'calllog/';
                    $isFileUploaded = $s3upload->putFileToS3($cloudFrontPath, AWSBUCKETNAME, $destinationPath, $fileuploadname, 'mp3', 'audio');

                    if ($isFileUploaded == 1) {
                        $newAudioPath = AWSBUCKETPATH . '/' . $cloudFrontPath . $fileuploadname;
                        $ccData = [
                            'AudioPath' => $newAudioPath,
                            'updatedOn' => date('Y-m-d H:i:s')
                        ];
                        $supportdb->perform('call_communications', $ccData, 'update', "id = {$callRecord['id']}");

                        $clData = [
                            'callRecords' => $newAudioPath
                        ];
                        $supportdb->perform('call_logs', $clData, 'update', "referenceId = {$callRecord['id']}");
                    }
                    // Remove temporary file after processing
                    if (file_exists($destinationPath)) {
                        @unlink($destinationPath);
                    }
                } else {
                    // Failed to download file
                    $ccData['AudioPath'] = "DOWNLOAD_FAILED";
                    $ccData['updatedOn'] = date('Y-m-d H:i:s');
                    $supportdb->perform('call_communications', $ccData, 'update', "id = {$callRecord['id']}");

                    $clData['callRecords'] = "DOWNLOAD_FAILED";
                    $supportdb->perform('call_logs', $clData, 'update', "referenceId = {$callRecord['id']}");
                }
            } else {
                // File not found remotely
                $ccData['AudioPath'] = "NO_FILE_EXISTS";
                $ccData['updatedOn'] = date('Y-m-d H:i:s');
                $supportdb->perform('call_communications', $ccData, 'update', "id = {$callRecord['id']}");

                $clData['callRecords'] = "NO_FILE_EXISTS";
                $supportdb->perform('call_logs', $clData, 'update', "referenceId = {$callRecord['id']}");
            }
        }
    }
    public function moveCallRecordingOld($db, $supportdb)
    {
        $callRecords = $supportdb->getMultipleData("SELECT * FROM call_communications WHERE AudioFile <> '' AND AudioPath IS NULL", true);
        foreach ($callRecords as $callRecord) {

            //copy audio file to our s3
            $audioFilePath = $callRecord['AudioFile'];
            $fileuploadname = trim(str_replace('.', '', uniqid("", true))) . "." . pathinfo(basename($audioFilePath), PATHINFO_EXTENSION);
            $destinationPath = ADMIN_PATH . '/tmp/audiofile/' . $fileuploadname;

            if (file_exists($audioFilePath)) {
                if (copy($audioFilePath, $destinationPath)) {

                    $s3upload = new \cgoS3FileHandler();

                    $cloudFrontPath = 'calllog/';

                    $isFileUploaded = $s3upload->putFileToS3($cloudFrontPath, AWSBUCKETNAME, $destinationPath, $fileuploadname, 'mp3', 'audio');
                    //echo 'isFileUploaded'.$isFileUploaded;
                    if ($isFileUploaded == 1) {
                        $newAudioPath = AWSBUCKETPATH . '/' . $cloudFrontPath . $fileuploadname;
                        $ccData['AudioPath'] = $newAudioPath;
                        $ccData['updatedOn'] = date('Y-m-d H:i:s');
                        $supportdb->perform('call_communications', $ccData, 'update', " id = {$callRecord['id']}");

                        $clData['callRecords'] = $newAudioPath;
                        //$clData['updatedOn'] = date('Y-m-d H:i:s');
                        $supportdb->perform('call_logs', $clData, 'update', " referenceId = {$callRecord['id']}");
                    }
                }
            } else {
                $ccData['AudioPath'] = "NO_FILE_EXISTS";
                $ccData['updatedOn'] = date('Y-m-d H:i:s');
                $supportdb->perform('call_communications', $ccData, 'update', " id = {$callRecord['id']}");
                $clData['callRecords'] = "NO_FILE_EXISTS";
                $supportdb->perform('call_logs', $clData, 'update', " referenceId = {$callRecord['id']}");
            }
        }
    }

    public function generateMissedCalls($db, $supportdb)
    {

        $missedCalls = $supportdb->getMultipleData("SELECT * FROM call_communications WHERE Status = 'Missed Call' AND isCallGenerated = 0", true);
        foreach ($missedCalls as $missedCall) {
            switch ($missedCall['AgentID']) {
                case '918069256050':
                    $calleeType = 1;
                    $outboundEvent =  $supportdb->getFromDB("SELECT * FROM support_user_events WHERE id = 17", true);
                    $isAvailable = $db->getFromDB("SELECT cust_id AS id ,cust_customer_name AS name FROM retaline_customer WHERE cust_mobile LIKE '%{$missedCall['CallerID']}%'", true);
                    break;
                default:
                    $calleeType = 2;
                    $outboundEvent =  $supportdb->getFromDB("SELECT * FROM support_user_events WHERE id = 16", true);
                    $isAvailable = $db->getFromDB("SELECT store_group_id AS id ,store_group_name AS name FROM finascop_branch_group WHERE contactNumber LIKE '%{$missedCall['CallerID']}%'", true);
                    break;
            }
            if ($isAvailable['id'] > 0) {
                $priority = 1;
                $calleeId = $isAvailable['id'];
                $calleeName = $isAvailable['name'];
            } else {
                $priority = 3;
                $calleeId = 0;
                $calleeName = 'Guest';
            }
            $missEveData['eventId'] = $outboundEvent['id'];
            $missEveData['jobTitle'] = $outboundEvent['eventName'] . '-' . $calleeName;
            $missEveData['calleeId'] = $calleeId;
            $missEveData['calleeName'] = $calleeName;
            $missEveData['calleeMobile'] = $missedCall['CallerID'];
            $missEveData['orderRefrenceId'] = '';
            $missEveData['calleeType'] = $calleeType;
            $missEveData['eventRank'] = $priority;
            $missEveData['status'] = 1;
            $supportdb->perform('outbound_jobs', $missEveData);

            $crData['isCallGenerated'] = 1;
            $crData['updatedOn'] = date('Y-m-d H:i:s');
            $supportdb->perform('call_communications', $crData, 'update', "id = {$missedCall['id']}");
        }
    }
}



$db = new sqlDb(DSN);
$supportdb = new sqlDb(SUPPORTDSN);

$db->quiet = false;
$beginstatus = $db->query('begin');
if ($beginstatus == false) {
    echo "Begin Transaction for process_lock - OutboundPendingJobs failed";
    exit;
}
$proclockrow = $db->query("select prlk_status from process_lock where prlk_name = 'OutboundPendingJobs' FOR UPDATE;");
if ($proclockrow == false) {
    echo "Error retriving process_lock - OutboundPendingJobs";
    exit;
} else {
    $procstatus = $db->fetch_row($proclockrow);
    if ((is_array($procstatus)) or ($procstatus instanceof Traversable)) {
        if ($procstatus[0] == 1) {
            echo "LOCKED OutboundPendingJobs. Bye bye \n";
            $db->query('rollback');
            exit;
        } else if ($procstatus[0] == 0) {
            $db->query("update process_lock set prlk_status = 1 where prlk_name = 'OutboundPendingJobs';");
        } else {
            echo "Error retriving prlk_status from process_lock - OutboundPendingJobs";
            exit;
        }
    } else {
        echo "Error retriving prlk_status from process_lock - OutboundPendingJobs, Not an array";
        print_r($procstatus);
        exit;
    }
}
$commitstatus = $db->query('commit');
if ($commitstatus == false) {
    echo "Commit Transaction for process_lock - OutboundPendingJobs failed";
    exit;
}

$pid = getmypid();

$outjobs = new schoutboundJobs();
$db->query('begin');
$outjobs->createOnboardingJobs($db, $supportdb);
$outjobs->createWelcomeJobs($db, $supportdb);
$outjobs->socialMarketingJobs($db, $supportdb);

$outjobs->incompleteOrderJobs($db, $supportdb);
$outjobs->deliveryDeficientOrderJobs($db, $supportdb);

$outjobs->generateMissedCalls($db, $supportdb);

$outjobs->moveCallRecording($db, $supportdb);
$db->query('commit');

$UnlockProc = $db->query("update process_lock set prlk_status = 0 where prlk_name = 'OutboundPendingJobs';");
if ($UnlockProc == false) {
    echo "Unlock for process_lock - OutboundPendingJobs failed";
    exit;
}
echo "---\n";
