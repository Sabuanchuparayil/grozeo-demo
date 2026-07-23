<?php

require_once(EXTERNAL_LIBRARY_PATH);
require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/finascop_config/config.php');
require_once(INCLUDE_PATH . "/finascop_accounts_Transactions.php");
require_once(INCLUDE_PATH . "/finascop_common_functions.php");
include_once(INCLUDE_PATH ."/dompdf/autoload.inc.php");


switch ($op) {
    case 'getAccounts':
        if ($_POST['type'] == '') {
            echo "{success : false, error : 'Missing entry type!'}";
            exit;
        }
        if ($_POST['query'] != "")
            $filter_query = "And accled_LedgerName like '" . $_POST['query'] . "%'";

        if ($_SESSION['admin']->finascop_typId != 4 || $_POST['referedfrom'] != 'Audit') {
            $add_con = " AND accled_BranchId = {$_SESSION['admin']->finascop_current_branch_id} 
			And accled_IsEnabled = 1 AND accled_CompId = {$_SESSION['admin']->finascop_current_company_id} ";
        } else {
            $add_con = " AND accled_BranchId = " . intval($_POST['branch_id']) . " 
			And accled_IsEnabled = 1 AND accled_CompId = {$_POST['company_id']} ";
        }

        switch ($_POST['type']) {
            case 'Receipt':
                $restrict = ' and alt.Group_ID IN (1,2) ';
                break;
            case 'Payment':
                $restrict = ' and alt.Group_ID IN (1,2) ';
                break;
            case 'Journal Voucher':
                $restrict = ' and alt.Group_ID NOT IN (1,2) ';
                break;
            case 'Contra Entry':
                $restrict = ' and alt.Group_ID IN (1,2) ';
                break;
        }


        $qry = "SELECT alt.ledgertypeid,accled_LedgerName,accled_Ledger_Id,alt.GroupName,alt.ledgertypename,alt.Group_ID 
		from " . FINASCOP_DB . "`finascop_accounts_ledgertype` alt 
		INNER join " . FINASCOP_DB . "`finascop_accounts_ledger` al
		ON alt.ledgertypeid = al.ledgertypeid
		WHERE 1 =1 $restrict  $add_con $filter_query ORDER BY accled_LedgerName";

        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;



    case 'getParticulars':
        if ($_POST['type'] == '') {
            echo "{success : false, error : 'Missing entry type!'}";
            exit;
        }
        if ($_POST['type'] == 'Contra Entry' && empty($_POST['Group_ID'])) {
            echo '{"totalCount":"0","data":[]}';
            exit;
        }
        if ($_POST['query'] != "")
            $filter_query = "And accled_LedgerName like '" . $_POST['query'] . "%'";

        switch ($_POST['type']) {
            case 'Receipt':
                $con = " AND alt.Group_ID NOT IN (1,2) ";
                break;
            case 'Payment':
                $con = " AND alt.Group_ID NOT IN (1,2) ";
                break;
            case 'Journal Voucher':
                $con = " AND alt.Group_ID NOT IN (1,2) ";
                if (!empty($_POST['selectedAccounts']))
                    $con .= " AND accled_Ledger_Id <> {$_POST['selectedAccounts']} ";
                break;
            case 'Contra Entry':
                $con = " AND alt.Group_ID  IN (1,2) AND accled_Ledger_Id <> " . $_POST['selectedAccounts'];
                break;
        }


        /* if ($_POST['ledgertypeid'] > 0) {
          $con = " AND alt.ledgertypeid NOT IN ({$_POST['ledgertypeid']}) AND alt.Group_ID IN (1,2)";
          } */

        if ($_SESSION['admin']->finascop_typId != 4 || $_POST['referedfrom'] != 'Audit') {
            $add_con = " AND accled_BranchId = {$_SESSION['admin']->finascop_current_branch_id} 
			And accled_IsEnabled = 1 AND accled_CompId = {$_SESSION['admin']->finascop_current_company_id}  ";
        } else {
            $add_con = " AND accled_BranchId = " . intval($_POST['branch_id']) . " 
			And accled_IsEnabled = 1 AND accled_CompId = {$_POST['company_id']} ";
        }


        $qry = "SELECT alt.ledgertypeid,CONCAT(al.accled_LedgerName,' (',al.GroupName,')') AS accled_LedgerName,accled_Ledger_Id,alt.GroupName,alt.ledgertypename,alt.Group_ID 
		from " . FINASCOP_DB . "`finascop_accounts_ledgertype` alt 
		INNER join " . FINASCOP_DB . "`finascop_accounts_ledger` al
		ON alt.ledgertypeid = al.ledgertypeid
		WHERE 1=1  $add_con $con $filter_query ORDER BY accled_LedgerName";
        //echo $qry;
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;


    case 'saveParticularData':
        $qry = "SELECT IsAutoApprovalEnabled FROM  " . FINASCOP_DB . "finascop_user_details WHERE UserId = " . $_SESSION['admin']->Finascop_UserId;
        $IsAutoApprovalEnabled = $db->getItemFromDB($qry, true);
        if ($IsAutoApprovalEnabled == 1 && intval($_POST['total_amount']) <= 0) {
            $msg = "Total Amount <= 0,Cannot Approve.";
            echo '{"success" : false,"msg":"' . $msg . '}';
            exit();
        }
        $data = $_POST;
        $sessiondets = new \stdClass;
        $sessiondets->company_id = $_SESSION['admin']->finascop_current_company_id;
        $sessiondets->company = $_SESSION['admin']->finascop_current_company;
        $sessiondets->Finascop_UserId = $_SESSION['admin']->Finascop_UserId;
        $sessiondets->branch_id = $_SESSION['admin']->finascop_current_branch_id;
        define('NOT_EDITABLE', 0);
        $db->query("begin");
        $accVouchers = new \finascop\accounts\Transactions\AccountingVouchers();
        $returned = $accVouchers->saveParticularData($data, $sessiondets, NOT_EDITABLE, $IsAutoApprovalEnabled);
        $dataentry = json_decode($returned);
        if ($dataentry->success == true) {
            $db->query("commit");
            echo $returned;
        } else {
            echo "error " . $returned; 
        }
        break;


    case 'listParticulars':

        $acet_NO = $_POST['acet_NO'];
        /*       switch ($type) {
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
          $cond_str = " AND actr_IsDebtor = '1'";
          }
         */
        $cond_str = "AND a.ledg_Id <> {$_POST['acc_ledger_id']}";
        $countQuery = "SELECT count(*)
		from " . FINASCOP_DB . "finascop_accounts_transaction
		WHERE acet_NO = '{$acet_NO}'";

        $listQuery = "SELECT ledg_Id AS particular_id,IF(actr_IsNegative = 1,-actr_amount,actr_amount) AS amount,
		IF(b.GroupName<>'',CONCAT(ledgertypename,' (',GroupName,')'),ledgertypename) AS particular_name
		from " . FINASCOP_DB . "finascop_accounts_transaction a
		INNER join " . FINASCOP_DB . "finascop_accounts_ledger b
		ON a.ledg_Id = b.accled_Ledger_Id 
		WHERE acet_NO = '{$acet_NO}'  {$cond_str} ";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'generatepdf':   

        $acet_NO = $_GET['acet_NO'];
        $acet_type = $_GET['type'];
        $qry = "SELECT (SELECT comp_name FROM " . FINASCOP_DB . "finascop_company fc WHERE fc.comp_id=fae.comp_id) AS company_name,
(SELECT br_Name FROM " . FINASCOP_DB . "finascop_branch fb WHERE fb.br_ID=fae.branch_id) AS branch_Name,
(SELECT br_Address FROM " . FINASCOP_DB . "finascop_branch fb WHERE fb.br_ID=fae.branch_id) AS branch_address,
acet_DocNO AS receipt_no, acet_Date AS receipt_date,acet_Amount, acet_InWords,acet_Narration, acet_ledg_Id  FROM " . FINASCOP_DB . "finascop_accounts_entry fae WHERE acet_NO= '$acet_NO' ";
        $data = $db->getFromDB($qry, true);
        $query = "SELECT fat.ledg_Id, fat.actr_amount AS amount, fat.actr_IsDebtor, fat.actr_IsNegative,
       (SELECT fal.ledgertypename
        FROM " . FINASCOP_DB . "finascop_accounts_ledger fal
        WHERE fal.accled_Ledger_Id = fat.ledg_Id
       ) AS NAME FROM " . FINASCOP_DB . "finascop_accounts_transaction fat WHERE acet_NO = '$acet_NO'";
        $particular = $db->getMultipleData($query, true);
        if($acet_type == 'Payment' || $acet_type=='Receipt')
        {
        CreatePDF($data['company_name'], $data['branch_Name'], $data['branch_address'], $data['receipt_no'], $data['receipt_date'], $acet_type, $data['acet_Amount'], $data['acet_InWords'], $data['acet_Narration'], $data['acet_ledg_Id'], $particular);
        }
         elseif ($acet_type=='Journal Voucher' || $acet_type=='Contra Entry' ) {
        CreateJournalVoucherPDF($data['company_name'], $data['branch_Name'], $data['branch_address'], $data['receipt_no'], $data['receipt_date'], $acet_type, $data['acet_Amount'], $data['acet_InWords'], $data['acet_Narration'], $data['acet_ledg_Id'], $particular);    
         }
        break;  
  
    case 'get_s3_details':

        $data ['file_name'] = sha1(microtime(true) . mt_rand(10000, 90000)); /* add extension in js */
        $data['albumBucketName'] = AWS_FINASCOP_ASSET_BUCKET;
        $data['accessKey'] = AWS_FINASCOP_ASSET_ACCESS_KEY;
        $data['secretKey'] = AWS_FINASCOP_ASSET_PASSWORD_KEY;
        $data['bucketRegion'] = AWS_FINASCOP_ASSET_REGION;
        $data['oncompleteurl'] = AWS_FINASCOP_ASSET_ON_COMPLETE_URL;
        echo "{success : true,'data':" . json_encode($data) . "}";
        break;



    case 'saveFileDetails':

        $data = $_POST;
        $sessiondets = new \stdClass;
        $sessiondets->company_id = $_SESSION['admin']->finascop_current_company_id;
        $sessiondets->company = $_SESSION['admin']->finascop_current_company;
        $sessiondets->Finascop_UserId = $_SESSION['admin']->Finascop_UserId;
        $sessiondets->branch_id = $_SESSION['admin']->finascop_current_branch_id;
        $db->query("begin");
        $accVouchers = new \finascop\accounts\Transactions\AccountingVouchers();
        $returned = $accVouchers->saveFileDetails($data, $sessiondets);
        $dataentry = json_decode($returned);
        if ($dataentry->success == true) {
            $db->query("commit");
            echo $returned;
        }
        break;

    case 'listDataEntries':

        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $rec_sort = empty($_POST['sort']) ? 'acet_Date' : ($_POST['sort'] == 'acetDate' ? 'acet_Date' : $_POST['sort']);
        $rec_sort_dir = empty($_POST['dir']) ? 'DESC' : $_POST['dir'];


        $filter_query = ' 1=1';

        /* if (isset($_POST['filter'])) {
          foreach ($_POST['filter'] as $key => $val) {
          $filter_part .= " and " . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
          }
          }
         */



        if (isset($_POST['filter'])) {
        $allowedFields = ['de_id', 'de_voucher_no', 'de_date', 'de_amount', 'de_narration'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }



        $countQuery = "SELECT count(*)
		from " . FINASCOP_DB . "`finascop_accounts_entry` a
		WHERE (acet_Status <> 4 AND acet_Status <> 3) and comp_id = {$_SESSION['admin']->finascop_current_company_id} 
		And branch_id = {$_SESSION['admin']->finascop_current_branch_id}  and $filter_query";

        $listQuery = "SELECT acet_NO,acet_DocNO,DATE_FORMAT(acet_Date, '%d-%m-%Y') AS acetDate,
		acet_Amount,acet_InWords,a.comp_id,acet_TypeId,updated_on,acet_Narration as narration,
		(SELECT comp_name from " . FINASCOP_DB . "finascop_company WHERE comp_id=a.comp_id) AS company,
		(SELECT CONCAT(FirstName,' ',LastName) FROM  " . FINASCOP_DB . "finascop_usr_profile WHERE UserId = a.acet_EntryBy) AS acet_EntryBy,
		acet_AssignedTo,acet_ImageURL,
		if(acet_ImageURL !='','Img','') as input_type,
		(SELECT GROUP_CONCAT(ledgertypename) from " . FINASCOP_DB . "finascop_accounts_ledger  WHERE accled_Ledger_Id IN 
		(SELECT ledg_Id from " . FINASCOP_DB . "`finascop_accounts_transaction`  WHERE finascop_accounts_transaction.acet_NO = a.acet_NO 
		AND entry_type = 'Account')) AS accounts_ip, 
		(SELECT GROUP_CONCAT(ledgertypename) from " . FINASCOP_DB . "finascop_accounts_ledger  WHERE accled_Ledger_Id IN 
		(SELECT ledg_Id from " . FINASCOP_DB . "`finascop_accounts_transaction`  WHERE finascop_accounts_transaction.acet_NO = a.acet_NO 
		AND entry_type = 'Particular')) AS particular_ip,
		CASE WHEN acet_TypeId = 1 OR acet_TypeId = 3   THEN 'Receipt'
		WHEN acet_TypeId = 2 OR acet_TypeId = 4 THEN 'Payment'
		WHEN acet_TypeId = 5 THEN 'Journal Voucher'
		WHEN acet_TypeId = 6 THEN 'Contra Entry'
		ELSE '-' END AS  type_name,acet_Status,if(acet_ImageURL='',0,1) as HasImage from " . FINASCOP_DB . "`finascop_accounts_entry` a
		WHERE (acet_Status <> 4 AND acet_Status <> 3) and acet_QueueForRecon = 0 and comp_id = {$_SESSION['admin']->finascop_current_company_id} 
		And branch_id = {$_SESSION['admin']->finascop_current_branch_id} and $filter_query
		ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);


        break;

    case 'getDetails':
        $acc_ref = $_POST['acet_NO'];
        $updated_on = $_POST['updated_on'];
        $currentUpdatedOnDate = $db->getItemFromDB("SELECT updated_on from " . FINASCOP_DB . "finascop_accounts_entry WHERE acet_NO = '{$acc_ref}'");
        if ($updated_on != $currentUpdatedOnDate) {
            echo '{"success":false,"msg":"Please refresh the entries. This entry has been updated."}';
            exit();
        }
        $data = $_POST;

        switch ($data['type']) {
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
                $cond_str = " AND actr_IsDebtor = '1'";
                break;
            default:
                $cond_str = " AND actr_IsDebtor = '1'";
        }
        /* $acc_dbtr = ($data['type'] == 'Receipt' && $data['type'] != 'Journal Voucher') ? 0 : 1;
          $acc_tr_dbtr = ($data['type'] == 'Receipt' && $data['type'] != 'Journal Voucher') ? 1 : 0;

          if ($data['type'] == 'Journal Voucher') {
          if ($data['ctr_dtr_type'] == 'Debtor') {
          $acc_dbtr = 1;
          $acc_tr_dbtr = 0;
          } else {
          $acc_dbtr = 0;
          $acc_tr_dbtr = 1;
          }
          }

          if ($data['type'] == 'Contra Entry') {
          $acc_dbtr = 0;
          $acc_tr_dbtr = 1;
          } */

        $rt = $db->getFromDB("SELECT acet_NO,acet_DocNO,DATE_FORMAT(acet_Date, '%d-%m-%Y') AS acet_Date,"
                . "acet_Narration,acet_Amount,acet_TypeId,updated_on,(SELECT ledg_Id from " . FINASCOP_DB . "`finascop_accounts_transaction` 
		WHERE acet_NO = '{$data['acet_NO']}' AND entry_type = 'Account') AS account, (SELECT actr_IsDebtor from " . FINASCOP_DB . "`finascop_accounts_transaction` 
		WHERE acet_NO = '{$data['acet_NO']}' AND entry_type = 'Account') as IsDebtor, acet_ImageURL as imgsrc,acet_AWSBucket as AWSBucket "
                . " from " . FINASCOP_DB . "finascop_accounts_entry WHERE acet_NO = '{$data['acet_NO']}'", true);

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
}

