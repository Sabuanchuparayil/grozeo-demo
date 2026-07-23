<?php

require_once(EXTERNAL_LIBRARY_PATH);
require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/finascop_config/config.php');
require_once(INCLUDE_PATH . "/finascop_accounts_Transactions.php");
require_once(INCLUDE_PATH . "/finascop_common_functions.php");

switch ($op) {
    case 'getbranch':
        $query = $_POST['query'];
        if ($query != '')
            $con = " and br_Name like '" . $query . "%'";
        else
            $con = '';


        $qry = "select br_ID,br_Name from " . DB_PREFIX . "1.finascop_branch where br_id>1  
                " . $con . " order by br_Name ";
        $branch = $db->getMulipleData($qry, true);
        if (!empty($branch)) {
            echo '{"totalCount":' . count($branch) . ',"data":' . json_encode($branch) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;


    case 'ledger_store':
        $query = $_POST['query'];

        $con = '';

        if ($_POST['chk_customer'] == true) {
            if ($query != '')
                $con = " and c_Name like '" . $query . "%'";
            $qry = "Select c_Name as Customer,CONCAT_WS(',', c_Add1, c_Add2,c_Add3) as Address,"
                    . " c_ID from  " . DB_PREFIX . "1.customer where c_Active=1 {$con} ";
        } else {

            if ($query != '')
                $con = " and accled_LedgerName like '" . $query . "%'";

            $qry = "select accled_LedgerName as Ledger, accled_Ledger_Id "
                    . " from " . DB_PREFIX . "1.ledger ld "
                    . " inner join " . DB_PREFIX . "1.ledgertypename ltn on ld.ledgertypeid = ltn.ledgertypeid  "
                    . " Where ( accled_BranchId = {$branch_id} or accled_BranchId = 0) "
                    . " And coalesce(accled_IsEnabled, 0) = 1 {$con} ";
        }
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;


    case 'getOpeningBalance':

        $data = $_POST;
        /*    $company_id = 
          $ledger_id
          $br_id
          $from_date
          $to_date */

        if ($data['$br_id'] == 1) {
            if ($data['checked'] == false) {
                $qry = "call " . DB_PREFIX . "1.populate_ledgerbalanceho(IF({$data['br_id']} <> '', 1, 0),IF({$data['br_id']} <> '', {$data['br_id']}, 0),{$data['ledger_id']},'{$data['from_date']}', '{$data['to_date']}', 0, 0,{$data['company_id']})";
            } else {
                $qry = "call " . DB_PREFIX . "1.populate_ledgerbalanceho(IF({$data['br_id']} <> '', 1, 0),IF({$data['br_id']} <> '', {$data['br_id']}, 0),{$data['ledger_id']},'{$data['from_date']}', '{$data['to_date']}', 1, 0,{$data['company_id']})";
            }
        } else {
            if ($data['checked'] == false) {
                $qry = "call " . DB_PREFIX . "1.populate_LedgerBalance(IF({$data['br_id']} <> '', 1, 0),IF({$data['br_id']} <> '', {$data['br_id']}, 0),{$data['ledger_id']},'{$data['from_date']}',' {$data['to_date']}', 0, 0,{$data['company_id']})";
            } else {
                $qry = "call " . DB_PREFIX . "1.populate_LedgerBalance(IF({$data['br_id']} <> '', 1, 0),IF({$data['br_id']} <> '', {$data['br_id']}, 0),{$data['ledger_id']},'{$data['from_date']}','{$data['to_date']}', 1, 0,{$data['company_id']})";
            }
        }

        $result = $db->getFromDB($qry, true);
        /*  [FinalDrAmts] => 0
          [FinalCrAmts] => 0
          [IsCreditor] => 0 */
        if (!empty($result)) {
            echo '{"success":true,"data":' . json_encode($result) . '}';
        } else
            echo '{"success":true,"data":[]}';
        break;


    case 'ledgerBalanceGrid':
        $data = $_POST;

        if ($data['$br_id'] == 1) {
            if ($data['checked'] == false) {
                $qry = "call " . DB_PREFIX . "1.populate_ledgerbalanceho(IF({$data['br_id']} <> '', 1, 0),IF({$data['br_id']} <> '', {$data['br_id']}, 0),{$data['ledger_id']},'{$data['from_date']}', '{$data['to_date']}', 0, 1,{$data['company_id']})";
            } else {
                $qry = "call " . DB_PREFIX . "1.populate_ledgerbalanceho(IF({$data['br_id']} <> '', 1, 0),IF({$data['br_id']} <> '', {$data['br_id']}, 0),{$data['ledger_id']},'{$data['from_date']}', '{$data['to_date']}', 1, 1,{$data['company_id']})";
            }
        } else {
            if ($data['checked'] == false) {
                $qry = "call " . DB_PREFIX . "1.populate_LedgerBalance(IF({$data['br_id']} <> '', 1, 0),IF({$data['br_id']} <> '', {$data['br_id']}, 0),{$data['ledger_id']},'{$data['from_date']}',' {$data['to_date']}', 0, 1,{$data['company_id']})";
            } else {
                $qry = "call " . DB_PREFIX . "1.populate_LedgerBalance(IF({$data['br_id']} <> '', 1, 0),IF({$data['br_id']} <> '', {$data['br_id']}, 0),{$data['ledger_id']},'{$data['from_date']}','{$data['to_date']}', 1, 1,{$data['company_id']})";
            }
        }

        $result = $db->getMultipleData($qry, true);
        if (!empty($result)) {
            echo '{"totalCount":' . count($result) . ',"data":' . json_encode($result) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;


    case 'resultGridlist':
        $data = $_POST;

        $fromDate = new DateTime($_POST['from_date']);
        $data['from_date'] = $fromDate->format('Y-m-d');

        $toDate = new DateTime($_POST['to_date']);
        $data['to_date'] = $toDate->format('Y-m-d');


        if ($data['option_0'] == true) {

            $qry = "call " . DB_PREFIX . "1.search_Accounts('" . trim($data['text']) . "',1,{$_POST['type']},0,'" . formatCapabilitie($data['from_date'], "yyyy-mm-dd") . "','" . formatCapabilitie($data['to_date'], "yyyy-mm-dd") . "')";
        } else if ($data['option_1'] == true) {
            $qry = "call " . DB_PREFIX . "1.search_Accounts('" . trim($data['text']) . "',2,{$_POST['type']},0,'" . formatCapabilitie($data['from_date'], "yyyy-mm-dd") . "','" . formatCapabilitie($data['to_date'], "yyyy-mm-dd") . "')";
        } else if ($data['option_2'] == true) {

            $qry = "call " . DB_PREFIX . "1.search_Accounts('" . trim($data['text']) . "',3,{$_POST['type']},0,'" . formatCapabilitie($data['from_date'], "yyyy-mm-dd") . "','" . formatCapabilitie($data['to_date'], "yyyy-mm-dd") . "')";
        } else if ($data['option_3'] == true) {


            $qry = "call " . DB_PREFIX . "1. search_Accounts('" . trim($data['text']) . "',4,{$_POST['type']}," . $bridlbl . ",'" . formatCapabilitie($data['from_date'], "yyyy-mm-dd") . "','" . formatCapabilitie($data['to_date'], "yyyy-mm-dd") . "')";
        }
        $result = $db->getMultipleData($qry, true);

        if (!empty($result)) {
            echo '{"totalCount":' . count($result) . ',"data":' . json_encode($result) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;

    case 'listbankRecmaingrid' :
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $curr_branch = $_SESSION['admin']->finascop_current_branch_id;
        $currentCompanyID = $_SESSION['admin']->finascop_current_company_id;
        $bankName = $_POST['bankname'];
        $ledgerId = $_POST['ledgerId'];
        $search = " AND 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['acc_id', 'acc_name', 'acc_code', 'acc_type', 'acc_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
//        $date = explode('/', $_POST['date']);
//        $acet_Date = date("Y-m-d", mktime(0, 0, 0, $date[1], $date[0], $date[2]));
        $acet_Date = $_POST['date'];
        $qry = "SELECT a.acet_DocNO as bankRec_No,a.acet_NO as bankRec_refno,a.acet_Date as bankRec_date,a.acet_Narration as bankRec_narration,"
                . "a.acet_Amount as bankRec_amount,a.acet_UTRRefno as bankRec_utrRefno,a.updated_on as updated_on,"
                . "(SELECT GROUP_CONCAT(accled_LedgerName) from " . FINASCOP_DB . "finascop_accounts_ledger  WHERE accled_Ledger_Id IN "
                . "(SELECT fat.ledg_Id from " . FINASCOP_DB . "finascop_accounts_transaction fat WHERE fat.acet_NO = a.acet_NO "
                . "AND fat.entry_type = 'Particular' AND fat.br_ID = a.branch_id GROUP BY fat.acet_NO)) AS bankRec_particulars"
                . " FROM " . FINASCOP_DB . "finascop_accounts_entry a WHERE a.acet_QueueForRecon = 1 "
                . "AND a.branch_id = '{$curr_branch}' AND a.acet_ledg_Id = '{$ledgerId}' "
                . "AND a.comp_id = '{$currentCompanyID}' AND a.acet_Date = '{$acet_Date}' {$search} ";
        $listQry = "{$qry} LIMIT {$rec_start}, {$rec_limit} ";
        $result = $db->getMultipleData($listQry, true);
        $_SESSION['bankreconcileexport'] = $qry;
        $qry = "SELECT COUNT(*) FROM " . FINASCOP_DB . "finascop_accounts_entry WHERE acet_QueueForRecon = 1 {$search}";
        $count = $db->getFromDB($qry, true);
        if (!empty($result)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($result) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;


    case 'listreconciliation_ledger' :

        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;
        $currentCompanyID = $_SESSION['admin']->finascop_current_company_id;
        $qry = "SELECT accled_Ledger_Id,ledgertypename "
                . "FROM " . FINASCOP_DB . "finascop_accounts_ledger WHERE accled_BranchId='{$currentBranch}' AND accled_CompId = '{$currentCompanyID}' ";
        $result = $db->getMultipleData($qry, true);
        if (!empty($result)) {
            echo '{"totalCount":' . count($result) . ',"data":' . json_encode($result) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'getbankDetails':
        $curr_branch = $_SESSION['admin']->finascop_current_branch_id;
        $qry = "SELECT fal.ledgertypename AS bankName, fal.accled_Ledger_Id AS bankId FROM " . FINASCOP_DB . "finascop_accounts_ledger fal " .
                " inner join finascop_accounts_ledgertype on finascop_accounts_ledgertype.ledgertypeid = fal.ledgertypeid  " .
                " inner join finascop_accounts_ledgertype_default on finascop_accounts_ledgertype_default.ledgertypedefaultid = finascop_accounts_ledgertype.ledgertypedefaultid "
                . "WHERE isPaymentGateway =1 AND fal.Group_ID = 2 AND accled_BranchId = '{$curr_branch}'";
        $result = $db->getMultipleData($qry, true);
        if (!empty($result)) {
            echo '{"totalCount":' . count($result) . ',"data":' . json_encode($result) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'reconciliation_upload':

        $fileName = $_FILES['template_file']['name'];
        $tmpFileName = $_FILES['template_file']['tmp_name'];
        $fileSize = $_FILES['template_file']['size'];
        $fileType = $_FILES['template_file']['type'];
        $data = array();
        $bank_id = $_POST['bank'];
        $utrColumn = $_POST['utrNoColumn'];

        $fh = fopen($_FILES['template_file']['tmp_name'], 'r+');
        $lines = array();
        while (($row = fgetcsv($fh)) !== FALSE) {
            $lines[] = $row[$utrColumn];
        }
        // print_r($lines);
        foreach ($lines as $key => $val) {
            //echo $val;
            $qry = "SELECT acet_NO FROM " . FINASCOP_DB . "finascop_accounts_entry "
                    . "WHERE acet_UTRRefno = '{$val}' AND acet_QueueForRecon = 1 AND acet_ledg_Id = '{$bank_id}'";
            $acet_NO = $db->getItemFromDB($qry);
            $data[$key] = $acet_NO;
        }

        if (!empty($data)) {
            echo '{"success":true,"totalCount":' . count($data) . ',"data":' . json_encode($data) . ',"msg":"Uploaded Successfully"}';
        } else {
            echo '{"success":false,"totalCount":"0","data":[],"msg":"Error"}';
        }
        break;

    case 'approveSelected':
        $db->query("begin");
        $post_data = $_POST;


        $post_data['reason'] = "approved selected bank reconciliation";
        $post_data['type'] = 3;
        $bankrecgridData = json_decode(stripslashes($_POST['bankrecgridData']), true);

        //$selectedIds = explode(",", $_POST['selectedIds']);

        $selectedRecords = json_decode(stripslashes($_POST['selectedRecords']), true);

        $sessiondets = new \stdClass;
        $sessiondets->Finascop_UserId = $_SESSION['admin']->Finascop_UserId;
        foreach ($selectedRecords as $key => $val) {

            $query = "UPDATE  " . FINASCOP_DB . "finascop_accounts_entry SET acet_QueueForRecon = '2' "
                    . "WHERE acet_NO = '{$val['bankRec_refno']}'";
            $post_data['acet_NO'] = $val['bankRec_refno'];
            $post_data['updated_on'] = $val['updated_on'];
            $status = $db->query($query);

            $accVoucher = new \finascop\accounts\Transactions\AccountingVouchers();
            $returned = $accVoucher->updateStatus($post_data, $sessiondets);
            $dataentry = json_decode($returned);
            // print_r($dataentry);

            if ($dataentry->success != true) {

                echo "{success: false, 'msg':'Error occured while updating this data'}";
            }
        }


        //$db->query("begin");


        $status = $db->query("commit");

        // echo "{success: true, msg:'Data Saved'}";
        if ($status) {
            echo "{success: true, msg:'Data Saved'}";
        }
//        else {
//            echo '{"success":fasle,"msg":"Error occured while removing this data"}';
//        }

        break;
    case 'bankreceiptexportexcel':
        require(THIS_MODULE_PATH . "/function.php");

        $_SESSION['Export']['Query'] = $_SESSION['bankreconcileexport'];
        $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
        _exportExcelReport($_POST);
        break;
}