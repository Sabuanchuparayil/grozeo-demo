<?php

define('SITE_PATH', dirname(dirname(__FILE__)));
define('CLASS_PATH', SITE_PATH . "/classes");
define('SCHEDULER_PATH', dirname(__FILE__));
define('INCLUDE_PATH', SITE_PATH . "/includes");
define('ADMIN_PATH', SITE_PATH);
include(INCLUDE_PATH . "/config.php");
//include(CLASS_PATH . "/sqlDb.php");
include(INCLUDE_PATH . "/lib.php");
//include(SITE_PATH . '/includes/config.php');
include(SITE_PATH . '/finascop_config/lib.php');
include(SITE_PATH . '/finascop_config/config.php');
include(EXTERNAL_LIBRARY_PATH);
require('orderProcess.php');

$db = new sqlDb(DSN);
$db->quiet = false;
$beginstatus = $db->query('begin');
if ($beginstatus == false) {
    echo "Begin Transaction for process_lock - OrderProcessorSch failed";
    exit;
}
$proclockrow = $db->query("select prlk_status from process_lock where prlk_name = 'OrderProcessorSch' FOR UPDATE;");
if ($proclockrow == false) {
    echo "Error retriving process_lock - OrderProcessorSch";
    exit;
} else {
    $procstatus = $db->fetch_row($proclockrow);
    if ((is_array($procstatus)) or ( $procstatus instanceof Traversable)) {
        if ($procstatus[0] == 1) {
            echo "OrderProcessorSch is locked. Bye \n";
            $db->query('rollback');
            exit;
        } else if ($procstatus[0] == 0) {
            $db->query("update process_lock set prlk_status = 1 where prlk_name = 'OrderProcessorSch';");
        } else {
            echo "Error retriving prlk_status from process_lock - OrderProcessorSch";
            exit;
        }
    } else {
        echo "Error retriving prlk_status from process_lock - OrderProcessorSch, Not an array";
        exit;
    }
}
$commitstatus = $db->query('commit');
if ($commitstatus == false) {
    echo "Commit Transaction for process_lock - OrderProcessorSch failed";
    exit;
}

$obj = new OrderProcessorSch();
//$obj->prescriptionAvaialbale();
$obj->getPromotedProducts();



$UnlockProc = $db->query("update process_lock set prlk_status = 0 where prlk_name = 'OrderProcessorSch';");
if ($UnlockProc == false) {
    echo "Unlock for process_lock - OrderProcessorSch failed";
    exit;
}
echo "---\n";
