<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
define('SITE_PATH', dirname(dirname(__FILE__)));
define('CLASS_PATH', SITE_PATH . "/classes");
define('SCHEDULER_PATH', dirname(__FILE__));
define('INCLUDE_PATH', SITE_PATH . "/includes");
define('ADMIN_PATH', SITE_PATH);
include(INCLUDE_PATH . "/config.php");
include(INCLUDE_PATH . "/lib.php");
//include(CLASS_PATH . "/sqlDb.php");
include(SITE_PATH . '/finascop_config/config.php');

class frequentOutboundJobs
{


    public function paymentFailedOrders($db, $supportdb)
    {
    }

    
}



$db = new sqlDb(DSN);
$supportdb = new sqlDb(SUPPORTDSN);

$db->quiet = false;
$beginstatus = $db->query('begin');
if ($beginstatus == false) {
    echo "Begin Transaction for process_lock - OutboundPendingJobs-Frequent failed";
    exit;
}
$proclockrow = $db->query("select prlk_status from process_lock where prlk_name = 'OutboundPendingJobs-Frequent' FOR UPDATE;");
if ($proclockrow == false) {
    echo "Error retriving process_lock - OutboundPendingJobs-Frequent";
    exit;
} else {
    $procstatus = $db->fetch_row($proclockrow);
    if ((is_array($procstatus)) or ($procstatus instanceof Traversable)) {
        if ($procstatus[0] == 1) {
            echo "LOCKED OutboundPendingJobs-Frequent. Bye bye \n";
            $db->query('rollback');
            exit;
        } else if ($procstatus[0] == 0) {
            $db->query("update process_lock set prlk_status = 1 where prlk_name = 'OutboundPendingJobs-Frequent';");
        } else {
            echo "Error retriving prlk_status from process_lock - OutboundPendingJobs-Frequent";
            exit;
        }
    } else {
        echo "Error retriving prlk_status from process_lock - OutboundPendingJobs-Frequent, Not an array";
        print_r($procstatus);
        exit;
    }
}
$commitstatus = $db->query('commit');
if ($commitstatus == false) {
    echo "Commit Transaction for process_lock - OutboundPendingJobs-Frequent failed";
    exit;
}

$pid = getmypid();

$outjobs = new frequentOutboundJobs();
$db->query('begin');
$outjobs->paymentFailedOrders($db, $supportdb);

$db->query('commit');

$UnlockProc = $db->query("update process_lock set prlk_status = 0 where prlk_name = 'OutboundPendingJobs-Frequent';");
if ($UnlockProc == false) {
    echo "Unlock for process_lock - OutboundPendingJobs-Frequent failed";
    exit;
}
echo "---\n";
