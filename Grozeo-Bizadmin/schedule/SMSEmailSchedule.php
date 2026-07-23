<?php
	define('SITE_PATH', dirname(dirname(__FILE__)));
	define('CLASS_PATH', SITE_PATH."/classes");	
	define('SCHEDULER_PATH', dirname(__FILE__));
	define('INCLUDE_PATH', SITE_PATH . "/includes");
	define('ADMIN_PATH', SITE_PATH);
	include(INCLUDE_PATH . "/config.php");
	//include(CLASS_PATH . "/sqlDb.php");
	include(INCLUDE_PATH . "/lib.php");
	include(SITE_PATH . '/includes/config.php');
	include(SITE_PATH . '/finascop_config/lib.php');
	include(SITE_PATH . '/finascop_config/config.php');
	include(EXTERNAL_LIBRARY_PATH);
	require('smsemail.php');
	require_once(INCLUDE_PATH . '/class.phpmailer.php');
	require(SITE_PATH.'/classes/TextLocal.php');
	
$db = new sqlDb(DSN);
$db->quiet =false;
 $beginstatus = $db->query('begin');
 if ($beginstatus == false){
	echo "Begin Transaction for process_lock - SMSEmailAutoSch failed";
	exit;
 }
 $proclockrow = $db->query("select prlk_status from process_lock where prlk_name = 'SMSEmailAutoSch' FOR UPDATE;");
 if ($proclockrow == false){
	echo "Error retriving process_lock - SMSEmailAutoSch";
	exit;
 }
 else{
	$procstatus = $db->fetch_row($proclockrow);
	if ((is_array($procstatus)) or ($procstatus instanceof Traversable)){
		if ($procstatus[0] == 1){
			echo "SMSEmailAutoSch is locked. Bye \n";
			$db->query('rollback');	
			exit;
		}
		else if($procstatus[0] == 0){
		   $db->query("update process_lock set prlk_status = 1 where prlk_name = 'SMSEmailAutoSch';");
		}
		else{
		   echo "Error retriving prlk_status from process_lock - SMSEmailAutoSch";
		   exit;
		}
	}
	else{
		echo "Error retriving prlk_status from process_lock - SMSEmailAutoSch, Not an array";
		exit;		
	}
 }
 $commitstatus = $db->query('commit');
if ($commitstatus == false){
	echo "Commit Transaction for process_lock - SMSEmailAutoSch failed";
	exit;
 }
 
$pid = getmypid();

$obj=new SMSEmailAutoSch();
$obj->mailsend();
$obj->mailsms();



$UnlockProc = $db->query("update process_lock set prlk_status = 0 where prlk_name = 'SMSEmailAutoSch';");
if ($UnlockProc == false){
	echo "Unlock for process_lock - SMSEmailAutoSch failed";
	exit;
 }
echo "---\n";