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


    case 'listbankRecmaingrid' :
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $curr_branch = $_SESSION['admin']->finascop_current_branch_id;
        $currentCompanyID = $_SESSION['admin']->finascop_current_company_id;
        $bankName = $_POST['bankname'];
        $ledgerId = $_POST['ledgerId'];
        $search = " AND 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
//        $date = explode('/', $_POST['date']);
//        $acet_Date = date("Y-m-d", mktime(0, 0, 0, $date[1], $date[0], $date[2]));
        $acet_Date = $_POST['date'];
        $acet_DateTo = $_POST['dateto'];
        $qry = "SELECT a.acet_DocNO as bankRec_No,a.acet_NO as bankRec_refno,a.acet_Date as bankRec_date,a.acet_Narration as bankRec_narration,acet_BankDate,a.acet_Date as bankInstrument_date,acet_ledg_Id,"
                . "a.acet_Amount as bankRec_amount,a.acet_UTRRefno as bankRec_utrRefno,a.updated_on as updated_on,CASE WHEN a.acet_TypeId = 1 OR a.acet_TypeId = 3   THEN 'Receipt'
		WHEN a.acet_TypeId = 2 OR a.acet_TypeId = 4 THEN 'Payment'
		WHEN a.acet_TypeId = 5 THEN 'Journal Voucher'
		WHEN a.acet_TypeId = 6 THEN 'Contra Entry'
		ELSE '-' END AS  type_name,a.acet_Amount as bankCreditRec_amount,"
                . "(SELECT GROUP_CONCAT(accled_LedgerName) from " . FINASCOP_DB . "finascop_accounts_ledger  WHERE accled_Ledger_Id IN "
                . "(SELECT fat.ledg_Id from " . FINASCOP_DB . "finascop_accounts_transaction fat WHERE fat.acet_NO = a.acet_NO "
                . "AND fat.entry_type = 'Particular' AND fat.br_ID = a.branch_id GROUP BY fat.acet_NO)) AS bankRec_particulars"
                . " FROM " . FINASCOP_DB . "finascop_accounts_entry a  WHERE   "
                . "  a.branch_id = '{$curr_branch}' AND a.acet_ledg_Id = '{$ledgerId}' "
                . "AND a.comp_id = '{$currentCompanyID}' AND a.acet_Date BETWEEN '{$acet_Date}' AND  '{$acet_DateTo}' {$search} ";
        /* (IF (actr_IsDebtor = 1,actr_amount,'')) AS dr,(IF (actr_IsDebtor = 0,actr_amount,'')) AS cr,
          (SELECT `name` FROM " . FINASCOP_DB . "`finascop_accounts_type` WHERE id = fae.acet_TypeId) AS `type`, */
        $listQry = "{$qry} LIMIT {$rec_start}, {$rec_limit} ";
        $result = $db->getMultipleData($listQry, true);
        $_SESSION['bankreconcileexport'] = $qry;
        $qry = "SELECT COUNT(*) FROM " . FINASCOP_DB . "finascop_accounts_entry a WHERE  "
                . "  a.branch_id = '{$curr_branch}' AND a.acet_ledg_Id = '{$ledgerId}' "
                . "AND a.comp_id = '{$currentCompanyID}' AND a.acet_Date BETWEEN '{$acet_Date}' AND  '{$acet_DateTo}'";
        $count = $db->getItemFromDB($qry);
        foreach ($result as $trialkey => $trialvalue) {
            $isDebtor = $db->getFromDB("select actr_amount,actr_IsDebtor from finascop_accounts_transaction where acet_NO = '{$result[$trialkey]['bankRec_refno']}' and ledg_Id = {$result[$trialkey]['acet_ledg_Id']}",true);
            if($isDebtor['actr_IsDebtor'] == 1){
                $result[$trialkey]['dr'] = $isDebtor['actr_amount'];
                $result[$trialkey]['cr'] = '';
            }else{
                $result[$trialkey]['dr'] = '';
                $result[$trialkey]['cr'] = $isDebtor['actr_amount'];
            }
           
           
        }
        if (!empty($result)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($result) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    

    case 'getbankDetails':
        $curr_branch = $_SESSION['admin']->finascop_current_branch_id;
        $qry = "SELECT fal.ledgertypename AS bankName, fal.accled_Ledger_Id AS bankId FROM " . FINASCOP_DB .
                "finascop_accounts_ledger fal inner join finascop_accounts_ledgertype on finascop_accounts_ledgertype.ledgertypeid = fal.ledgertypeid  " .
                " LEFT join finascop_accounts_ledgertype_default on finascop_accounts_ledgertype_default.ledgertypedefaultid = finascop_accounts_ledgertype.ledgertypedefaultid "
                . "WHERE coalesce(isPaymentGateway,0) =0 and fal.Group_ID = 2 AND accled_BranchId = '{$curr_branch}'";
        $result = $db->getMultipleData($qry, true);
        if (!empty($result)) {
            echo '{"totalCount":' . count($result) . ',"data":' . json_encode($result) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
 
    case 'updateBankDate':
        $bankRec_refno = $_POST['bankRec_refno'];
        $bankDate = $_POST['bankDate'];
        $db->query("begin");
        $status = $db->query("UPDATE finascop_accounts_entry SET acet_BankDate = '$bankDate' WHERE acet_NO = '{$bankRec_refno}'");
        $status = $db->query("commit");
        if ($status == 1) {
            echo '{"success":true,"valid":true}';
            exit();
        } else {
            echo '{"success":false,"valid":false}';
            exit();
        }
        break;
        
    case 'getLedgerBalaces':
//date_from: 2021-06-27
//date_to: 2021-07-27
        $ledger = $_POST['ledger'];
        $date_from = $_POST['date_from'];
        $date_to = $_POST['date_to'];
        //$approvedstatus = " actr_IsApproved " . (($_POST['audit_status'] == 1) ? ' >=0' : ($_POST['audit_status'] == 2 ? ' = 1 ' : ' = 0  '));
        //echo $approvedstatus;
        if ($ledger > 0 && !empty($date_from) && !empty($date_to)) {
            $data = $db->getFromDB("SELECT SUM(debt) as debt,sum(crdt) as crdt from( SELECT SUM(IF(actr_IsDebtor=1,actr_amount,0)) AS debt,
			SUM(IF(actr_IsDebtor=0,actr_amount,0)) AS crdt
			from " . FINASCOP_DB . "finascop_accounts_transaction WHERE ledg_Id = {$ledger} AND 
			actr_Date < STR_TO_DATE('{$date_from}','%Y-%m-%d') 
			UNION ALL
			SELECT 
			SUM(IF(OpenBal_IsDebtor=1,OpenBal_Amt,0))  AS debt ,
			SUM(IF(OpenBal_IsDebtor=0,OpenBal_Amt,0))  AS crdt
			from " . FINASCOP_DB . "finascop_accounts_openingbalance							
			where finascop_accounts_openingbalance.openBal_Led_ID = {$ledger}) as openbal	", true);
                        
             $close = $db->getFromDB("SELECT SUM(debt) as debt,sum(crdt) as crdt from( SELECT SUM(IF(actr_IsDebtor=1,actr_amount,0)) AS debt,
			SUM(IF(actr_IsDebtor=0,actr_amount,0)) AS crdt
			from " . FINASCOP_DB . "finascop_accounts_transaction WHERE ledg_Id = {$ledger} AND 
			actr_Date <= STR_TO_DATE('{$date_to}','%Y-%m-%d') 
			UNION ALL
			SELECT 
			SUM(IF(OpenBal_IsDebtor=1,OpenBal_Amt,0))  AS debt ,
			SUM(IF(OpenBal_IsDebtor=0,OpenBal_Amt,0))  AS crdt
			from " . FINASCOP_DB . "finascop_accounts_openingbalance							
			where finascop_accounts_openingbalance.openBal_Led_ID = {$ledger}) as openbal	", true);
            if (!empty($data)) {
               $openingbalance = $data['debt'] - $data['crdt'];       
               
            }else{
                
                $openingbalance =0;
            }
            
             if (!empty($close)) {
                 $closingbalance = $close['debt'] - $close['crdt'];    
            }else{
                
                 $closingbalance =0;
            }
            
             echo "{success:true, 'openingBal':'" . $openingbalance . "', 'closingBal':'" . $closingbalance . "'}";
        }
        break;
}