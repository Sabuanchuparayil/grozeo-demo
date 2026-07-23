<?php
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	define('SITE_PATH', dirname(dirname(__FILE__)));
	define('CLASS_PATH', SITE_PATH."/classes");	
	define('SCHEDULER_PATH', dirname(__FILE__));
	define('INCLUDE_PATH', SITE_PATH . "/includes");
	define('ADMIN_PATH', SITE_PATH);
	//include(INCLUDE_PATH . "/config.php");
	//include(CLASS_PATH . "/sqlDb.php");
	include(INCLUDE_PATH . "/lib.php");
	include(INCLUDE_PATH . "/finascop_wallet_client.php");
	include(SITE_PATH . '/includes/config.php');
	include(SITE_PATH . '/finascop_config/lib.php');
	include(SITE_PATH . '/finascop_config/config.php');
	include(EXTERNAL_LIBRARY_PATH);
	require(QUGEO_API_ROOT . '/qugeoapi-autoloader.php');
	
	/*
require('../html/includes/config.php');
require('../html/includes/lib.php');
require('../html/rpc/config.php');
require('../html/rpc/lib.php');
require('../html/admin/includes/functions.php');
require(EXTERNAL_LIBRARY_PATH);
require(API_ROOT . '/caregoapi-autoloader.php');*/
echo "-START-" . date("Y-m-d H:i:s") ."\n"     ;

$db = new sqlDb(DSN);
$db->quiet =false;
 $beginstatus = $db->query('begin');
 if ($beginstatus == false){
	echo "Begin Transaction for process_lock - FinascopAutoSch failed";
	exit;
 }
 $proclockrow = $db->query("select prlk_status from process_lock where prlk_name = 'FinascopAutoSch' FOR UPDATE;");
 if ($proclockrow == false){
	echo "Error retriving process_lock - FinascopAutoSch";
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
		   $db->query("update process_lock set prlk_status = 1 where prlk_name = 'FinascopAutoSch';");
		}
		else{
		   echo "Error retriving prlk_status from process_lock - FinascopAutoSch";
		   exit;
		}
	}
	else{
		echo "Error retriving prlk_status from process_lock - FinascopAutoSch, Not an array";
		exit;		
	}
 }
 $commitstatus = $db->query('commit');
if ($commitstatus == false){
	echo "Commit Transaction for process_lock - FinascopAutoSch failed";
	exit;
 }
 
$obj=new FinascopWalletClient(FINASCOPAPIDOMAIN);
$obj->processTransactionQ();



$UnlockProc = $db->query("update process_lock set prlk_status = 0 where prlk_name = 'FinascopAutoSch';");
if ($UnlockProc == false){
	echo "Unlock for process_lock - FinascopAutoSch failed";
	exit;
 }
echo "-END-" . date("Y-m-d H:i:s") ."\n"     ;