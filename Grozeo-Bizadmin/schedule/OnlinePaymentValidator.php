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
include(CLASS_PATH . "/TextLocal.php");

class OnlinePaymentValidator
{

	public function convertToTimeoutBooking($db)
	{
		//We did NOT receive funds for {#var#} order you tried. If amount deducted from bank, please call at {#var#} and refer No.{#var#} - velosit
		$qry = "SELECT order_id,order_payment_initiate_time,order_order_id,order_customer_id,storegroup_id,order_branch_id,total FROM retaline_customer_order WHERE status_id  = '1' and payment_mode in (2,5) and  TIME_TO_SEC(TIMEDIFF(NOW(), order_payment_initiate_time)) > 600 order by order_id";
		$online_booking = $db->getMultipleData($qry, true);
		foreach ($online_booking as $item) {
			if ($item['order_payment_initiate_time'] != '0000-00-00 00:00:00') {
				$db->query("update retaline_customer_order set order_payment_response_received=1, order_payment_failed_scheduler_time =now(), status_id = 21, updated_at=now() WHERE order_id = {$item['order_id']}");
				$db->query("insert into retaline_customer_order_history(order_id, order_action, order_status, created_at, updated_at) values({$item['order_id']},'Payment timed out',21,now(),now())");
				$projname = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='PROJECT_NAME'");
				$projsupport = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='PROJECT_SUPPORT_NUMBER'");
				$customerno = $db->getItemFromDB("SELECT cust_mobile FROM retaline_customer WHERE cust_id={$item['order_customer_id']}");
				$test['receiver_id'] = $customerno;
				$test['is_sms'] = 1;
				if ($item['storegroup_id'] > 0) {
					$sitename = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = {$item['storegroup_id']}");
				} else {
					$sgId = $db->getItemFromDB("SELECT br_cpd FROM finascop_branch WHERE br_ID = {$item['order_branch_id']}");
					$sitename = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = {$sgId}");
				}
				//1207166746065122397
				$templatedata['total'] = $item['total'];
				$templatedata['sitename'] = $sitename;

				sms::fetchContentSendSms($templatedata, $customerno, 3);
				$test['text_message'] = "We did NOT receive funds for {$projname} order you tried. If amount deducted from bank, please call at {$projsupport} and refer No.{$item['order_order_id']} - velosit";
				$test['created_on'] = date("Y-m-d H:i:s");
				//$status = $db->perform('retaline_emailsms_queue', $test);
			}
		}
	}
	public function convertToFailedBooking($db, $supportdb)
	{

		$qry = "SELECT order_id,order_order_id,order_payment_initiate_time,order_order_id,order_customer_id,order_wallet_amount,storegroup_id,order_branch_id FROM retaline_customer_order WHERE status_id  = '21' and payment_mode in (2,5) and  TIME_TO_SEC(TIMEDIFF(NOW(), order_payment_initiate_time)) > 900 order by order_id";
		$online_booking = $db->getMultipleData($qry, true);
		foreach ($online_booking as $item) {
			if ($item['order_wallet_amount'] > 0) {

				//$db->query("update retaline_customer set cust_walletbalance = cust_walletbalance + " . $item['order_wallet_amount'] . " where cust_id = " . $item['order_customer_id']);

				//$db->query("insert into retaline_customer_wallet_transaction(cust_id, refentry_id, brcw_SourceType, brcw_Amount, brcw_AddInfo,stiid_barcode,brcw_CreatedOn,brcw_Updateon) values(" . $item['order_customer_id'] . "," . $item['order_id'] . ",1," .  $item['order_wallet_amount'] . ", 'Payment Timed out and customer didn\'t follow up - " . $item['order_order_id'] ."' ,0,now(),now())");

				$url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'WALLET_API'");
				$fields = array(
					"customer_id" => $item['order_customer_id'],
					"order_id" => $item['order_id'],
					"source_type" => 1,
					"amount" => $item['order_wallet_amount'],
					"information" => "Payment Timed out and customer didn\'t follow up - " . $item['order_order_id'],
					"barcode" => 0
				);
				$fields_string = json_encode($fields);
				//print_r($fields_string);
				$opts = array(
					CURLOPT_URL => $url,
					CURLINFO_CONTENT_TYPE => "application/json",
					CURLOPT_BINARYTRANSFER => TRUE,
					CURLOPT_RETURNTRANSFER => TRUE,
					CURLOPT_POST => count($fields),
					CURLOPT_POSTFIELDS => $fields_string,
					CURLOPT_HTTPHEADER => array('Content-Type: application/json')
				);

				$ch = curl_init();
				curl_setopt_array($ch, $opts);
				$data = curl_exec($ch);
				$info = curl_getinfo($ch);
				curl_close($ch);
				header("Content-Type: application/json");
				$result = json_decode($data, true);
				if ($result['status'] == 'ok') {
					$status = 1;
				} else {
					$status = 0;
				}
			}
			$customerno = $db->getItemFromDB("SELECT cust_mobile FROM retaline_customer WHERE cust_id={$item['order_customer_id']}");
			$db->query("update retaline_customer_order set  status_id = 2, updated_at=now() WHERE order_id = {$item['order_id']}");
			$db->query("insert into retaline_customer_order_history(order_id, order_action, order_status, created_at, updated_at) values({$item['order_id']},'Payment marked as failed',2,now(),now())");

			//Payment Failed Jobs
			$outboundEvent =  $supportdb->getFromDB("SELECT * FROM support_user_events WHERE id = 13", true);
			$isJob = $supportdb->getItemFromDB("SELECT COUNT(*) FROM outbound_jobs WHERE calleeId = {$item['order_customer_id']} AND calleeType = 2 AND eventId = 13 AND orderRefrenceId = '{$item['order_order_id']}'");
			if ($isJob == 0) {
				$getCaleeDetails = $db->getFromDB("SELECT cust_mobile,cust_customer_name FROM retaline_customer WHERE cust_id = {$item['order_customer_id']}", true);
				$secEveData['eventId'] = $outboundEvent['id'];
				$secEveData['jobTitle'] = $outboundEvent['eventName'] . '-' . $getCaleeDetails['cust_customer_name'];
				$secEveData['calleeId'] = $item['order_customer_id'];
				$secEveData['calleeName'] = $getCaleeDetails['cust_customer_name'];
				$secEveData['calleeMobile'] = $getCaleeDetails['cust_mobile'];
				$secEveData['calleeType'] = 1;
				$secEveData['orderRefrenceId'] = $item['order_order_id'];
				$secEveData['eventRank'] = $outboundEvent['rank'];
				$secEveData['status'] = 1;
				$supportdb->perform('outbound_jobs', $secEveData);
			}

			if ($item['storegroup_id'] > 0) {
				$sitename = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = {$item['storegroup_id']}");
			} else {
				$sgId = $db->getItemFromDB("SELECT br_cpd FROM finascop_branch WHERE br_ID = {$item['order_branch_id']}");
				$sitename = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = {$sgId}");
			}
			$templatedata['sitename'] = $sitename;
			sms::fetchContentSendSms($templatedata, $customerno, 5);
		}
	}
}

$db = new sqlDb(DSN);
$supportdb = new sqlDb(SUPPORTDSN);

$db->quiet = false;
$beginstatus = $db->query('begin');
if ($beginstatus == false) {
	echo "Begin Transaction for process_lock - OnlinePaymentValidatorLock failed";
	exit;
}
$proclockrow = $db->query("select prlk_status from process_lock where prlk_name = 'OnlinePaymentValidatorLock' FOR UPDATE;");
if ($proclockrow == false) {
	echo "Error retriving process_lock - OnlinePaymentValidatorLock";
	exit;
} else {
	$procstatus = $db->fetch_row($proclockrow);
	if ((is_array($procstatus)) or ($procstatus instanceof Traversable)) {
		if ($procstatus[0] == 1) {
			echo "LOCKED OnlinePaymentValidatorLock. Bye bye \n";
			$db->query('rollback');
			exit;
		} else if ($procstatus[0] == 0) {
			$db->query("update process_lock set prlk_status = 1 where prlk_name = 'OnlinePaymentValidatorLock';");
		} else {
			echo "Error retriving prlk_status from process_lock - OnlinePaymentValidatorLock";
			exit;
		}
	} else {
		echo "Error retriving prlk_status from process_lock - OnlinePaymentValidatorLock, Not an array";
		print_r($procstatus);
		exit;
	}
}
$commitstatus = $db->query('commit');
if ($commitstatus == false) {
	echo "Commit Transaction for process_lock - OnlinePaymentValidatorLock failed";
	exit;
}

$pid = getmypid();

$onlpay = new OnlinePaymentValidator();
$db->query('begin');
$onlpay->convertToFailedBooking($db, $supportdb);
$onlpay->convertToTimeoutBooking($db);
$db->query('commit');

$UnlockProc = $db->query("update process_lock set prlk_status = 0 where prlk_name = 'OnlinePaymentValidatorLock';");
if ($UnlockProc == false) {
	echo "Unlock for process_lock - OnlinePaymentValidatorLock failed";
	exit;
}
echo "---\n";
