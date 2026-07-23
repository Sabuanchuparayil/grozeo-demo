<?php
	define('SITE_PATH', dirname(dirname(__FILE__)));
	define('CLASS_PATH', SITE_PATH."/classes");	
	define('SCHEDULER_PATH', dirname(__FILE__));
	define('INCLUDE_PATH', SITE_PATH . "/includes");
	define('ADMIN_PATH', SITE_PATH);
	include(INCLUDE_PATH . "/config.php");
	//include(CLASS_PATH . "/sqlDb.php");
	include(INCLUDE_PATH . "/lib.php");
	include(SITE_PATH . '/finascop_config/config.php');
	include(SITE_PATH . '/finascop_config/lib.php');
	include(EXTERNAL_LIBRARY_PATH);
	include(INCLUDE_PATH . '/CloudFcmNotification.php');
	require(QUGEO_API_ROOT . '/qugeoapi-autoloader.php');
	
	/*
require('../html/includes/config.php');
require('../html/includes/lib.php');
require('../html/rpc/config.php');
require('../html/rpc/lib.php');
require('../html/admin/includes/functions.php');
require(EXTERNAL_LIBRARY_PATH);
require(API_ROOT . '/caregoapi-autoloader.php');*/

$db = new sqlDb(DSN);
$db->quiet =false;
 $beginstatus = $db->query('begin');
 if ($beginstatus == false){
	echo "Begin Transaction for process_lock - QugeoAutoSch failed";
	exit;
 }
 $proclockrow = $db->query("select prlk_status from process_lock where prlk_name = 'QugeoAutoSch' FOR UPDATE;");
 if ($proclockrow == false){
	echo "Error retriving process_lock - QugeoAutoSch";
	exit;
 }
 else{
	$procstatus = $db->fetch_row($proclockrow);
	if ((is_array($procstatus)) or ($procstatus instanceof Traversable)){
		if ($procstatus[0] == 1){
			$db->query('rollback');	
			exit;
		}
		else if($procstatus[0] == 0){
		   $db->query("update process_lock set prlk_status = 1 where prlk_name = 'QugeoAutoSch';");
		}
		else{
		   echo "Error retriving prlk_status from process_lock - QugeoAutoSch";
		   exit;
		}
	}
	else{
		echo "Error retriving prlk_status from process_lock - QugeoAutoSch, Not an array";
		exit;		
	}
 }
 $commitstatus = $db->query('commit');
if ($commitstatus == false){
	echo "Commit Transaction for process_lock - QugeoAutoSch failed";
	exit;
 }
 
$pid = getmypid();

$API = new  Controllers\RequestHandler('', '');
echo "Start Validating curent logins " . date("Y-m-d H:i:s") . " \n";
$API->qugeoscheduler('validateLiveQugeos');
echo "Start Auto Scheduling " . date("Y-m-d H:i:s") . " \n";
$API->qugeoscheduler('scheduleNewBookings');
echo "Start Update of no response polls " . date("Y-m-d H:i:s") . " \n";
$API->qugeoscheduler('updateNoResponsePolls');
echo "Start of rescheduling bookings " . date("Y-m-d H:i:s") . " \n";
$API->qugeoscheduler('rescheduleBookings');
echo "Start of rescheduling Delivery " . date("Y-m-d H:i:s") . " \n";
$API->qugeoscheduler('rescheduleDelivery'); 




$UnlockProc = $db->query("update process_lock set prlk_status = 0 where prlk_name = 'QugeoAutoSch';");
if ($UnlockProc == false){
	echo "Unlock for process_lock - QugeoAutoSch failed";
	exit;
 }
echo "---\n";