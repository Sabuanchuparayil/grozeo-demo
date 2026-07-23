<?php
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	define('SITE_PATH', dirname(dirname(__FILE__)));
	define('CLASS_PATH', SITE_PATH."/classes");	
	define('SCHEDULER_PATH', dirname(__FILE__));
	define('INCLUDE_PATH', SITE_PATH . "/includes");
	define('ADMIN_PATH', SITE_PATH);
	include(INCLUDE_PATH . "/config.php");
	include(INCLUDE_PATH . "/lib.php");
	//include(CLASS_PATH . "/sqlDb.php");
	include(SITE_PATH . '/finascop_config/config.php');

	class OnlinePaymentValidator{
		
		public function convertToTimeoutBooking($db){
			//We did NOT receive funds for {#var#} order you tried. If amount deducted from bank, please call at {#var#} and refer No.{#var#} - velosit
			$qry = "SELECT order_id,order_payment_initiate_time,order_order_id,order_customer_id FROM retaline_customer_order WHERE status_id  = '1' and payment_mode in (2,5) and  TIME_TO_SEC(TIMEDIFF(NOW(), order_payment_initiate_time)) > 600 order by order_id";
			//$online_booking = $db->getMultipleData($qry, true);
			$online_booking = $db->getMultipleData($qry, true);
			if (empty($online_booking)) {
				$online_booking = [];
			}
			foreach($online_booking as $item) {
				//$db->query("update retaline_customer_order set order_payment_response_received=1, order_payment_failed_scheduler_time =now(), status_id = 21, updated_at=now() WHERE order_id = {$item['order_id']}");	
				//$db->query("insert into retaline_customer_order_history(order_id, order_action, order_status, created_at, updated_at) values({$item['order_id']},'Payment timed out',21,now(),now())");
				$projname = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='PROJECT_NAME'");
				$projsupport = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='PROJECT_SUPPORT_NUMBER'");
				$customerno = $db->getItemFromDB("SELECT cust_mobile FROM retaline_customer WHERE cust_id={$item['order_customer_id']}");
				$test['receiver_id'] = $customerno;
				$test['is_sms'] = 1;
                                //1607100000000130950
				$test['text_message'] = "We did NOT receive funds for {$projname} order you tried. If amount deducted from bank, please call at {$projsupport} and refer No.{$item['order_order_id']} - velosit";
				$test['created_on'] = date("Y-m-d H:i:s");
        		//$status = $db->perform('retaline_emailsms_queue', $test);				
			}		
			
		}
		public function convertToFailedBooking($db){
			
			$qry = "SELECT order_id,order_order_id,order_payment_initiate_time,order_order_id,order_customer_id,order_wallet_amount FROM retaline_customer_order WHERE status_id  = '21' and payment_mode in (2,5) and  TIME_TO_SEC(TIMEDIFF(NOW(), order_payment_initiate_time)) > 21600 order by order_id";
			$online_booking = $db->getMultipleData($qry, true);
			foreach($online_booking as $item) {
				if($item['order_wallet_amount']>0){
					
					$db->query("update retaline_customer set cust_walletbalance = cust_walletbalance + " . $item['order_wallet_amount'] . " where cust_id = " . $item['order_customer_id']);

					$db->query("insert into retaline_customer_wallet_transaction(cust_id, refentry_id, brcw_SourceType, brcw_Amount, brcw_AddInfo,stiid_barcode,brcw_CreatedOn,brcw_Updateon) values(" . $item['order_customer_id'] . "," . $item['order_id'] . ",1," .  $item['order_wallet_amount'] . ", 'Payment Timed out and customer didn\'t follow up - " . $item['order_order_id'] ."' ,0,now(),now())");
				}
				$db->query("update retaline_customer_order set  status_id = 2, updated_at=now() WHERE order_id = {$item['order_id']}");	
				$db->query("insert into retaline_customer_order_history(order_id, order_action, order_status, created_at, updated_at) values({$item['order_id']},'Payment marked as failed',2,now(),now())");
			
			}		
			
		}
	}
	
	$db = new sqlDb(DSN);
	$db->quiet =false;
	$beginstatus = $db->query('begin');
	if ($beginstatus == false){
		echo "Begin Transaction for process_lock - OnlinePaymentValidatorLock failed";
		exit;
	}
	$proclockrow = $db->query("select prlk_status from process_lock where prlk_name = 'OnlinePaymentValidatorLock' FOR UPDATE;");
	if ($proclockrow == false){
		echo "Error retriving process_lock - OnlinePaymentValidatorLock";
		exit;
	}
	else{
		$procstatus = $db->fetch_row($proclockrow);
		if ((is_array($procstatus)) or ($procstatus instanceof Traversable)){
			if ($procstatus[0] == 1){
				echo "LOCKED OnlinePaymentValidatorLock. Bye bye \n";
				$db->query('rollback');	
				exit;
			}
			else if($procstatus[0] == 0){
				$db->query("update process_lock set prlk_status = 1 where prlk_name = 'OnlinePaymentValidatorLock';");
			}
			else{
				echo "Error retriving prlk_status from process_lock - OnlinePaymentValidatorLock";
				exit;
			}
		}
		else{
			echo "Error retriving prlk_status from process_lock - OnlinePaymentValidatorLock, Not an array";
			print_r($procstatus);
			exit;		
		}
	}
	$commitstatus = $db->query('commit');
	if ($commitstatus == false){
		echo "Commit Transaction for process_lock - OnlinePaymentValidatorLock failed";
		exit;
	}
	
	$pid = getmypid();
	
	$onlpay = new OnlinePaymentValidator();
	$db->query('begin');
	$onlpay->convertToFailedBooking($db);
	$onlpay->convertToTimeoutBooking($db);
	$db->query('commit');
	
	$UnlockProc = $db->query("update process_lock set prlk_status = 0 where prlk_name = 'OnlinePaymentValidatorLock';");
	if ($UnlockProc == false){
		echo "Unlock for process_lock - OnlinePaymentValidatorLock failed";
		exit;
	}
echo "---\n";