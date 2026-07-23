<?php
	
	require_once(EXTERNAL_LIBRARY_PATH);	
	require_once(ROOT . '/finascop_config/lib.php');
	require_once(ROOT . '/finascop_config/config.php');

	require_once(INCLUDE_PATH . "/finascop_accounts_Transactions.php");
        require_once(INCLUDE_PATH . "/finascop_common_functions.php");
	
	switch ($op) {
		
		case 'getComboStore':
        $ind = $_GET['ind'];
        switch ($ind) {
            case 1:
			if ($_POST['query'] != "")
			$filter_query = "AND comp_name like '" . $_POST['query'] . "%'";
			if ($_SESSION['admin']->Finascop_UserId > 1) {
				$con = " WHERE UserId = {$_SESSION['admin']->Finascop_UserId} ";
			}
			$qry = "SELECT comp_id AS id, comp_name AS `name` from " . FINASCOP_DB . "finascop_company
			WHERE comp_id IN (SELECT comp_id from " . FINASCOP_DB . "finascop_branch_company 
			WHERE br_Id IN (SELECT br_id from " . FINASCOP_DB . "finascop_user_auditingbranches {$con})) {$filter_query}";
			break;
            case 2:
			
			if ($_POST['company'] > 0) {
				if ($_SESSION['admin']->Finascop_UserId > 1) {
					$con = " WHERE UserId = {$_SESSION['admin']->Finascop_UserId} ";
				}
				$qry = "SELECT br_ID AS id, br_Name AS `name` from " . FINASCOP_DB . "finascop_branch
				WHERE br_ID IN (SELECT br_Id from " . FINASCOP_DB . "finascop_branch_company 
				WHERE comp_id = {$_POST['company']} AND br_Id IN (SELECT br_id from " . FINASCOP_DB . "finascop_user_auditingbranches {$con}))";
			}
			
			break;
            case 3:
			$qry = "SELECT id, `name` FROM accounts_type";
			break;
		}
        if (!empty($qry)) {
            $qry .= " ORDER BY `name` ASC";
			
            finascop_getjsonkeyarray($qry);
		}
        break;
		
		case 'load_s3_details':
		
        break;
		
		case 'listAudit':
        //$rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        //$rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $rec_sort = empty($_POST['sort']) ? 'acet_Date' : $_POST['sort'];
        $rec_sort_dir = empty($_POST['dir']) ? 'DESC' : $_POST['dir'];
		
		
        $filter_part = ' 1=1';
        $finalisedcond = "";
        if ($_POST['audit_finalized'] == 'true') {
            $status_con = " (acet_Status = 3 OR  acet_Status = 4) ";
            $date_cond = " AND acet_Date BETWEEN '{$_POST['audit_record_from_date']}' AND  '{$_POST['audit_record_to_date']}' ";
            $finalisedcond = " OR (" . $status_con . $date_cond . ") ";
		}
		
		
        if ($_POST['audit_company'] > 0 || $_POST['audit_branch'] > 0 || $_POST['audit_type'] > 0) {
			
            if ($_POST['audit_company'] > 0) {
                $comp_con = " AND comp_id = {$_POST['audit_company']} ";
			}
			
            if ($_POST['audit_branch'] > 0) {
                $br_con = " AND branch_id = {$_POST['audit_branch']} ";
			}
            if ($_POST['audit_type'] > 0) {
                $type_con = " AND acet_TypeId = {$_POST['audit_type']} ";
			}
		}
		
        // SECURITY: use buildSafeFilterQuery
        $allowedFields = ['audit_date', 'audit_action', 'audit_user'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        /* RAW (disabled): if (isset($_POST['filter'])) { */
			
            foreach ($_POST['filter'] as $key => $val) {
                $filter_part .= " and " . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
			}
		}
		
		
		
        /* $countQuery = "SELECT count(*)
			from " . FINASCOP_DB . "`finascop_accounts_entry` a
			WHERE (acet_Status = 0 OR ((acet_Status = 1 or acet_Status = 2) AND acet_AssignedTo = {$_SESSION['admin']->Finascop_UserId}))
			AND comp_id IN (SELECT comp_id from " . FINASCOP_DB . "`finascop_branch_company`
			WHERE branch_id IN (SELECT br_id from " . FINASCOP_DB . "`finascop_user_auditingbranches`
		WHERE UserId = {$_SESSION['admin']->Finascop_UserId}) ) {$comp_con} {$br_con} {$type_con}"; */
		
        $countQuery = "Select 0";
		
        $listQuery = "SELECT acet_NO,DATE_FORMAT(acet_Date, '%d-%m-%y') AS acet_Date,IF(acet_Status = 3,1,0) AS Approved,IF(acet_Status = 3,1,IF(acet_Status = 4,1,0)) AS RollBack,
		acet_Amount,acet_InWords,a.comp_id,acet_TypeId,updated_on,acet_Narration as narration,
		(SELECT comp_name from " . FINASCOP_DB . "finascop_company WHERE comp_id=a.comp_id) AS company,
		(SELECT CONCAT(FirstName,' ',LastName) FROM " . FINASCOP_DB . "`finascop_usr_profile` WHERE UserId = a.acet_EntryBy) AS acet_EntryBy,
		acet_AssignedTo,acet_ImageURL,
		if(acet_ImageURL !='','Img','') as entry_type,
		(SELECT GROUP_CONCAT(accled_LedgerName) from " . FINASCOP_DB . "finascop_accounts_ledger  inner join  " . FINASCOP_DB . "`finascop_accounts_transaction` fat on  accled_Ledger_Id = ledg_Id      WHERE fat.acet_NO = a.acet_NO AND entry_type = 'Account') AS accounts_ip, 
		(SELECT GROUP_CONCAT(accled_LedgerName) from " . FINASCOP_DB . "finascop_accounts_ledger  inner join  " . FINASCOP_DB . "`finascop_accounts_transaction` fat on  accled_Ledger_Id = ledg_Id      WHERE fat.acet_NO = a.acet_NO	AND entry_type = 'Particular') AS particular_ip,
		CASE WHEN acet_TypeId = 1 OR acet_TypeId = 3 THEN 'Receipt'
		WHEN acet_TypeId = 2 OR acet_TypeId = 4 THEN 'Payment'
		WHEN acet_TypeId = 5 THEN 'Journal Voucher'
		WHEN acet_TypeId = 6 THEN 'Contra Entry' 
		ELSE '-' END AS  type_name,acet_Status,
		(SELECT br_Name from " . FINASCOP_DB . "finascop_branch WHERE br_id=a.branch_id) AS branch,if(acet_ImageURL='',0,1) as HasImage, acet_SourceOfEntry 
		from " . FINASCOP_DB . "`finascop_accounts_entry` a
		WHERE (acet_Status = 0 {$finalisedcond} OR ((acet_Status = 1 or acet_Status = 2) AND acet_AssignedTo = {$_SESSION['admin']->Finascop_UserId}))		
		AND comp_id IN (SELECT comp_id from " . FINASCOP_DB . "`finascop_branch_company` 
		WHERE branch_id IN (SELECT br_id from " . FINASCOP_DB . "`finascop_user_auditingbranches` 
		WHERE UserId = {$_SESSION['admin']->Finascop_UserId}) )
		{$comp_con} {$br_con} {$type_con}
		ORDER BY $rec_sort $rec_sort_dir ";
        //ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        $db->printGridJson($countQuery, $listQuery);
		
        break;
		
		case 'updateStatus':
        $post_data = $_POST;
		$sessiondets = new \stdClass;		
		$sessiondets->Finascop_UserId = $_SESSION['admin']->Finascop_UserId;		
		$db->query("begin");
		$accVoucher = new \finascop\accounts\Transactions\AccountingVouchers();
        $returned =  $accVoucher->updateStatus($post_data,$sessiondets);
		$dataentry = json_decode($returned);

		if($dataentry->success == true){
			$db->query("commit");
			echo	$returned;						
		}
        break;
		
		case 'listParticulars':
		
        $acet_NO = $_POST['acet_NO'];
        $type = $_POST['type'];
/*        
        switch ($type) {
            case "Receipt":
			$cond_str = " AND actr_IsDebtor = '0'";
			break;
            case 'Payment':
			$cond_str = " AND actr_IsDebtor = '1' ";
			break;
            case "Journal Voucher":
			$cond_str = " AND entry_type = 'Particular'";
			break;
            case "Contra Entry":
			$cond_str = " AND actr_IsDebtor = '1'";
			break;
            default:
			$cond_str = " AND actr_IsDebtor = '0'";
		}
*/
        
        $cond_str = "AND a.ledg_Id <> {$_POST['acc_ledger_id']}" ;           
		
        $countQuery = "SELECT count(*)
		from " . FINASCOP_DB . "finascop_accounts_transaction
		WHERE acet_NO = '{$acet_NO}'";
		
        $listQuery = "SELECT ledg_Id AS particular_id,IF(actr_IsNegative = 1,-actr_amount,actr_amount) AS amount,
		IF(b.GroupName<>'',CONCAT(accled_LedgerName,' (',GroupName,')'),accled_LedgerName) AS particular_name, actr_IsDebtor as IsDebtor
		from " . FINASCOP_DB . "finascop_accounts_transaction a
		INNER join " . FINASCOP_DB . "finascop_accounts_ledger b
		ON a.ledg_Id = b.accled_Ledger_Id 
		WHERE acet_NO = '{$acet_NO}' {$cond_str}";
		
        $db->printGridJson($countQuery, $listQuery);
        break;
		
		case 'getDetails':
		
        //$data = $_POST;
        $acet_NO = $_POST['acet_NO'];
        $type = $_POST['type'];
        /* $acc_dbtr = ($data['type'] == 'Receipt' && $data['type'] != 'Journal Voucher') ? 0 : 1;
			$acc_tr_dbtr = ($data['type'] == 'Receipt' && $data['type'] != 'Journal Voucher') ? 1 : 0;
			
			/* if ($data['type'] == 'Journal Voucher') {
			if ($data['ctr_dtr_type'] == 'Debtor') {
			$acc_dbtr = 1;
			$acc_tr_dbtr = 0;
			} else {
			$acc_dbtr = 0;
			$acc_tr_dbtr = 1;
			}
			} *//*
			
			if ($data['type'] == 'Contra Entry') {
			$acc_dbtr = 0;
			$acc_tr_dbtr = 1;
		} */
        switch ($type) {
            case "Receipt":
			$cond_str = " AND actr_IsDebtor = '1'";
			break;
            case 'Payment':
			$cond_str = " AND actr_IsDebtor = '0' ";
			break;
            case "Journal Voucher":
			$cond_str = " AND entry_type = 'Account'";
			break;
            case "Contra Entry":
			$cond_str = " AND actr_IsDebtor = '0'";
			break;
            default:
			$cond_str = " AND actr_IsDebtor = '1'";
		}
        $rt = $db->getFromDB("SELECT acet_NO,DATE_FORMAT(acet_Date, '%d-%m-%Y') AS acet_Date,"
		. "acet_Narration,acet_Amount,acet_TypeId,(SELECT ledg_Id from " . FINASCOP_DB . "`finascop_accounts_transaction` 
		WHERE acet_NO = '{$acet_NO}' AND entry_type = 'Account') AS account, (SELECT actr_IsDebtor from " . FINASCOP_DB . "`finascop_accounts_transaction` 
		WHERE acet_NO = '{$acet_NO}' AND entry_type = 'Account') as IsDebtor,branch_id,comp_id "
		. "from " . FINASCOP_DB . "finascop_accounts_entry WHERE acet_NO = '{$acet_NO}'", true);
		
        if (!empty($rt)) {
            echo "{success : true, data:" . json_encode($rt) . "}";
			} else {
            echo "{success : true}";
		}
        /* $account_entry = array('acet_NO' => $acc_ref,
			'acet_Date' => date('Y-m-d', strtotime($data['receipt_date'])),
			'acet_Narration' => $data['narration'],
			'acet_Amount' => $data['total_amount'],
			'acet_InWords' => finascop_numberToWords($data['total_amount']),
			'comp_id' => $_SESSION['admin']->finascop_current_company_id,
			'acet_TypeId' => $data['ledger_type'],
			'acet_EntryBy' => $_SESSION['admin']->Finascop_UserId,
			'acet_ImageURL' => $data['location'],
			'acet_AWSBucket' => $data['bucket']);
		$db->perform(FINASCOP_DB . "finascop_accounts_entry", $account_entry); */
		
        break;
		
		case 'getAccounts':
        if ($_POST['query'] != "")
		$filter_query = "And accled_LedgerName like '" . $_POST['query'] . "%'";
		
        if ($_SESSION['admin']->Finascop_UserId > 1) {
            $add_con = " AND accled_BranchId = {$_SESSION['admin']->finascop_current_branch_id} 
			And accled_IsEnabled = 1 AND accled_CompId = {$_SESSION['admin']->finascop_current_company_id} ";
		}
		
        $qry = "SELECT alt.ledgertypeid,accled_LedgerName,accled_Ledger_Id,alt.GroupName,alt.ledgertypename 
		from " . FINASCOP_DB . "`finascop_accounts_ledgertype` alt 
		INNER join " . FINASCOP_DB . "`finascop_accounts_ledger` al
		ON alt.ledgertypeid = al.ledgertypeid
		WHERE alt.Group_ID IN (1,2) $add_con $filter_query ORDER BY accled_LedgerName";
		
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
		} else
		echo '{"totalCount":"0","data":[]}';
		
        break;
		
		case 'saveParticularData':
        $data = $_POST;
		$sessiondets = new \stdClass;
		$sessiondets->Finascop_UserId = $_SESSION['admin']->Finascop_UserId;
		$db->query("begin");
		$accVouchers = new \finascop\accounts\Transactions\AccountingVouchers();
        $returned =  $accVouchers->saveParticularData($data, $sessiondets );
		$dataentry = json_decode($returned);
		if($dataentry->success == true){
			$db->query("commit");
			echo	$returned;						
		}
        break;
	}														