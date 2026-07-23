<?php

require_once(EXTERNAL_LIBRARY_PATH);
require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/finascop_config/config.php');
require_once(INCLUDE_PATH . "/finascop_accounts_Transactions.php");
require_once(INCLUDE_PATH . "/finascop_common_functions.php");

switch ($op) {


    case 'listGSTReportsGrid' :
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $curr_branch = $_SESSION['admin']->finascop_current_branch_id;
        $currentCompanyID = $_SESSION['admin']->finascop_current_company_id;
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
        $gstReportType = $_POST['gstReportType'];
        switch ($gstReportType) {
            case 1:
                $ledgerId = $db->getItemFromDB("SELECT finascop_accounts_ledger.accled_Ledger_Id FROM finascop_accounts_ledgertype_default 
INNER JOIN finascop_accounts_ledgertype ON finascop_accounts_ledgertype.ledgertypedefaultid = finascop_accounts_ledgertype_default.ledgertypedefaultid
INNER JOIN finascop_accounts_ledger ON finascop_accounts_ledger.ledgertypeid = finascop_accounts_ledgertype.ledgertypeid
WHERE ledgertypedefaultname = 'IGST' AND accled_BranchId ={$_SESSION['admin']->finascop_current_branch_id}");
                break;
            case 2:
                $ledgerId = $db->getItemFromDB("SELECT finascop_accounts_ledger.accled_Ledger_Id FROM finascop_accounts_ledgertype_default 
INNER JOIN finascop_accounts_ledgertype ON finascop_accounts_ledgertype.ledgertypedefaultid = finascop_accounts_ledgertype_default.ledgertypedefaultid
INNER JOIN finascop_accounts_ledger ON finascop_accounts_ledger.ledgertypeid = finascop_accounts_ledgertype.ledgertypeid
WHERE ledgertypedefaultname = 'CGST' AND accled_BranchId ={$_SESSION['admin']->finascop_current_branch_id}");
                break;
            case 3:
                $ledgerId = $db->getItemFromDB("SELECT finascop_accounts_ledger.accled_Ledger_Id FROM finascop_accounts_ledgertype_default 
INNER JOIN finascop_accounts_ledgertype ON finascop_accounts_ledgertype.ledgertypedefaultid = finascop_accounts_ledgertype_default.ledgertypedefaultid
INNER JOIN finascop_accounts_ledger ON finascop_accounts_ledger.ledgertypeid = finascop_accounts_ledgertype.ledgertypeid
WHERE ledgertypedefaultname = 'SGST' AND accled_BranchId ={$_SESSION['admin']->finascop_current_branch_id}");
                break;
            case 4:
                $ledgerId = $db->getItemFromDB("SELECT finascop_accounts_ledger.accled_Ledger_Id FROM finascop_accounts_ledgertype_default 
INNER JOIN finascop_accounts_ledgertype ON finascop_accounts_ledgertype.ledgertypedefaultid = finascop_accounts_ledgertype_default.ledgertypedefaultid
INNER JOIN finascop_accounts_ledger ON finascop_accounts_ledger.ledgertypeid = finascop_accounts_ledgertype.ledgertypeid
WHERE ledgertypedefaultname = 'KFC' AND accled_BranchId ={$_SESSION['admin']->finascop_current_branch_id}");
                break;
            case 5:
                $ledgerId = 0;
                break;
        }

        $qry = "select a.acet_NO, CASE WHEN a.acet_TypeId = 1 OR a.acet_TypeId = 3   THEN 'Receipt'
		WHEN a.acet_TypeId = 2 OR a.acet_TypeId = 4 THEN 'Payment'
		WHEN a.acet_TypeId = 5 THEN 'Journal Voucher'
		WHEN a.acet_TypeId = 6 THEN 'Contra Entry'
		ELSE '-' END AS  type_name,  a.actr_Date,
		(SELECT GROUP_CONCAT(accled_LedgerName) FROM finascop_accounts_ledger  WHERE accled_Ledger_Id IN (SELECT fat.ledg_Id FROM finascop_accounts_transaction fat WHERE fat.acet_NO = a.acet_NO  and fat.ledg_Id <> a.ledg_Id  ) ) AS bankRec_particulars,
                if(actr_IsDebtor=1,actr_amount,-actr_amount) as drcr_amount
		FROM finascop_accounts_transaction a  WHERE     a.ledg_Id = '{$ledgerId}'  AND a.actr_Date BETWEEN '{$acet_Date}' AND  '{$acet_DateTo}' {$search} ";
        /* (IF (actr_IsDebtor = 1,actr_amount,'')) AS dr,(IF (actr_IsDebtor = 0,actr_amount,'')) AS cr,
          (SELECT `name` FROM " . FINASCOP_DB . "`finascop_accounts_type` WHERE id = fae.acet_TypeId) AS `type`, */
        $listQry = "{$qry} LIMIT {$rec_start}, {$rec_limit} ";
        $result = $db->getMultipleData($listQry, true);
        $_SESSION['gstreportexport'] = $qry;
        $qry = "SELECT COUNT(*) FROM finascop_accounts_transaction a  WHERE     a.ledg_Id = '{$ledgerId}'  AND a.actr_Date BETWEEN '{$acet_Date}' AND  '{$acet_DateTo}'";
        $count = $db->getItemFromDB($qry);
        foreach ($result as $trialkey => $trialvalue) {
            if ($result[$trialkey]['drcr_amount'] > 0) {
                $result[$trialkey]['dr'] = 0;
                $result[$trialkey]['cr'] = abs($result[$trialkey]['drcr_amount']);
            } else {
                $result[$trialkey]['dr'] = abs($result[$trialkey]['drcr_amount']);
                $result[$trialkey]['cr'] = 0;
            }
        }
        if (!empty($result)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($result) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'getreportrexport':
        $datas = $db->getMulipleData($_SESSION['gstreportexport'], true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                if ($datas[$i]['drcr_amount'] > 0) {
                    $datas[$i]['dr'] = 0;
                    $datas[$i]['cr'] = $datas[$i]['drcr_amount'];
                } else {
                    $datas[$i]['dr'] = $datas[$i]['drcr_amount'];
                    $datas[$i]['cr'] = 0;
                }


                $exportDatas[$i]['Date'] = $datas[$i]['actr_Date'];
                $exportDatas[$i]['Particulars'] = $datas[$i]['bankRec_particulars'];
                $exportDatas[$i]['Voucher Type'] = $datas[$i]['type_name'];
                $exportDatas[$i]['Voucher No'] = $datas[$i]['acet_NO'];
                $exportDatas[$i]['Debit'] = $datas[$i]['dr'];
                $exportDatas[$i]['Credit'] = $datas[$i]['cr'];
            }
            $filename = time() . ".xls";
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");

            ExportFile($exportDatas);
        }
        break;
    case 'listLedgerReportsGrid':
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $curr_branch = $_SESSION['admin']->finascop_current_branch_id;
        $currentCompanyID = $_SESSION['admin']->finascop_current_company_id;
        $ledgerDeditCombo = $_POST['ledgerDeditCombo'];
        $search = " AND 1=1 and ledg_Id = {$ledgerDeditCombo} ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
        $acet_Date = $_POST['date'];
        $acet_DateTo = $_POST['dateto'];
        //acet_NO,account,ledg_Id,actr_amount,narration,particular
        if ($ledgerDeditCombo > 0) {
            $qry = "SELECT a.acet_NO,(SELECT accled_LedgerName FROM finascop_accounts_ledger WHERE finascop_accounts_ledger.accled_Ledger_Id = ledg_Id ) AS account,ledg_Id,actr_amount,actr_Date,
            (SELECT acet_Narration FROM finascop_accounts_entry ae WHERE ae.acet_NO = a.acet_NO) AS narration,CASE WHEN a.acet_TypeId = 1 OR a.acet_TypeId = 3   THEN 'Receipt'
		WHEN a.acet_TypeId = 2 OR a.acet_TypeId = 4 THEN 'Payment'
		WHEN a.acet_TypeId = 5 THEN 'Journal Voucher'
		WHEN a.acet_TypeId = 6 THEN 'Contra Entry'
		ELSE '-' END AS  type_name,if(actr_IsDebtor=1,actr_amount,-actr_amount) as drcr_amount,
            (SELECT GROUP_CONCAT(accled_LedgerName) FROM finascop_accounts_ledger  WHERE accled_Ledger_Id IN (SELECT fat.ledg_Id FROM finascop_accounts_transaction fat WHERE fat.acet_NO = a.acet_NO  AND fat.ledg_Id <> a.ledg_Id  ) ) AS particular 
            FROM finascop_accounts_transaction a
INNER JOIN finascop_accounts_ledger ON finascop_accounts_ledger.accled_Ledger_Id 
WHERE finascop_accounts_ledger.Group_ID = 8 AND a.actr_IsDebtor = 0 AND actr_Date BETWEEN '{$acet_Date}' AND  '{$acet_DateTo}' {$search} ";
            /* (IF (actr_IsDebtor = 1,actr_amount,'')) AS dr,(IF (actr_IsDebtor = 0,actr_amount,'')) AS cr,
              (SELECT `name` FROM " . FINASCOP_DB . "`finascop_accounts_type` WHERE id = fae.acet_TypeId) AS `type`, */
            $listQry = "{$qry} LIMIT {$rec_start}, {$rec_limit} ";
            $result = $db->getMultipleData($listQry, true);
            $_SESSION['debitnotreportexport'] = $qry;
            $cqry = "SELECT COUNT(*) FROM finascop_accounts_transaction 
INNER JOIN finascop_accounts_ledger ON finascop_accounts_ledger.accled_Ledger_Id
WHERE finascop_accounts_ledger.Group_ID = 8 AND finascop_accounts_transaction.actr_IsDebtor = 0 AND actr_Date BETWEEN '{$acet_Date}' AND  '{$acet_DateTo}' {$search}";
            $count = $db->getItemFromDB($cqry);
            foreach ($result as $trialkey => $trialvalue) {
                if ($result[$trialkey]['drcr_amount'] > 0) {
                    $result[$trialkey]['debit'] = 0;
                    $result[$trialkey]['credit'] = abs($result[$trialkey]['drcr_amount']);
                } else {
                    $result[$trialkey]['debit'] = abs($result[$trialkey]['drcr_amount']);
                    $result[$trialkey]['credit'] = 0;
                }
            }
        }

        if (!empty($result)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($result) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'debitNotereportsrexport':
        $datas = $db->getMulipleData($_SESSION['debitnotreportexport'], true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $exportDatas[$i]['Date'] = $datas[$i]['actr_Date'];
                $exportDatas[$i]['Particulars'] = $datas[$i]['particular'];
                $exportDatas[$i]['Voucher Type'] = $datas[$i]['type_name'];
                $exportDatas[$i]['Voucher No'] = $datas[$i]['acet_NO'];
                $exportDatas[$i]['Narration'] = $datas[$i]['narration'];
                $exportDatas[$i]['Debit'] = $datas[$i]['debit'];
                $exportDatas[$i]['Credit'] = $datas[$i]['credit'];
            }
            $filename = time() . ".xls";
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");

            ExportFile($exportDatas);
        }
        break;
    case 'getledgerDeditCombo':
        $qry = $db->getMulipleData("SELECT accled_Ledger_Id as id,accled_LedgerName as name FROM finascop_accounts_ledger WHERE Group_ID = 8 ", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'getledgerCreditCombo':
        $qry = $db->getMulipleData("SELECT accled_Ledger_Id as id,accled_LedgerName as name FROM finascop_accounts_ledger WHERE Group_ID = 7 ", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'listCreditReportsGrid':
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $curr_branch = $_SESSION['admin']->finascop_current_branch_id;
        $currentCompanyID = $_SESSION['admin']->finascop_current_company_id;
        $ledgerDeditCombo = $_POST['ledgerDeditCombo'];
        $search = " AND 1=1 and ledg_Id = {$ledgerDeditCombo} ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
        $acet_Date = $_POST['date'];
        $acet_DateTo = $_POST['dateto'];
        //acet_NO,account,ledg_Id,actr_amount,narration,particular
        if ($ledgerDeditCombo > 0) {
            $qry = "SELECT a.acet_NO,(SELECT accled_LedgerName FROM finascop_accounts_ledger WHERE finascop_accounts_ledger.accled_Ledger_Id = ledg_Id ) AS account,ledg_Id,actr_amount,actr_Date,
            (SELECT acet_Narration FROM finascop_accounts_entry ae WHERE ae.acet_NO = a.acet_NO) AS narration,CASE WHEN a.acet_TypeId = 1 OR a.acet_TypeId = 3   THEN 'Receipt'
		WHEN a.acet_TypeId = 2 OR a.acet_TypeId = 4 THEN 'Payment'
		WHEN a.acet_TypeId = 5 THEN 'Journal Voucher'
		WHEN a.acet_TypeId = 6 THEN 'Contra Entry'
		ELSE '-' END AS  type_name,if(actr_IsDebtor=1,actr_amount,-actr_amount) as drcr_amount,
            (SELECT GROUP_CONCAT(accled_LedgerName) FROM finascop_accounts_ledger  WHERE accled_Ledger_Id IN (SELECT fat.ledg_Id FROM finascop_accounts_transaction fat WHERE fat.acet_NO = a.acet_NO  AND fat.ledg_Id <> a.ledg_Id  ) ) AS particular 
            FROM finascop_accounts_transaction a
INNER JOIN finascop_accounts_ledger ON finascop_accounts_ledger.accled_Ledger_Id 
WHERE finascop_accounts_ledger.Group_ID = 7 AND a.actr_IsDebtor = 0 AND actr_Date BETWEEN '{$acet_Date}' AND  '{$acet_DateTo}' {$search} ";
            /* (IF (actr_IsDebtor = 1,actr_amount,'')) AS dr,(IF (actr_IsDebtor = 0,actr_amount,'')) AS cr,
              (SELECT `name` FROM " . FINASCOP_DB . "`finascop_accounts_type` WHERE id = fae.acet_TypeId) AS `type`, */
            $listQry = "{$qry} LIMIT {$rec_start}, {$rec_limit} ";
            $result = $db->getMultipleData($listQry, true);
            $_SESSION['creditnotereportexport'] = $qry;
            $cqry = "SELECT COUNT(*) FROM finascop_accounts_transaction 
INNER JOIN finascop_accounts_ledger ON finascop_accounts_ledger.accled_Ledger_Id
WHERE finascop_accounts_ledger.Group_ID = 7 AND finascop_accounts_transaction.actr_IsDebtor = 0 AND actr_Date BETWEEN '{$acet_Date}' AND  '{$acet_DateTo}' {$search}";
            $count = $db->getItemFromDB($cqry);
            foreach ($result as $trialkey => $trialvalue) {
                if ($result[$trialkey]['drcr_amount'] > 0) {
                    $result[$trialkey]['debit'] = 0;
                    $result[$trialkey]['credit'] = abs($result[$trialkey]['drcr_amount']);
                } else {
                    $result[$trialkey]['debit'] = abs($result[$trialkey]['drcr_amount']);
                    $result[$trialkey]['credit'] = 0;
                }
            }
        }

        if (!empty($result)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($result) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'creditNotereportsrexport':
        $datas = $db->getMulipleData($_SESSION['creditnotereportexport'], true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $exportDatas[$i]['Date'] = $datas[$i]['actr_Date'];
                $exportDatas[$i]['Particulars'] = $datas[$i]['particular'];
                $exportDatas[$i]['Voucher Type'] = $datas[$i]['type_name'];
                $exportDatas[$i]['Voucher No'] = $datas[$i]['acet_NO'];
                $exportDatas[$i]['Narration'] = $datas[$i]['narration'];
                $exportDatas[$i]['Debit'] = $datas[$i]['debit'];
                $exportDatas[$i]['Credit'] = $datas[$i]['credit'];
            }
            $filename = time() . ".xls";
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");

            ExportFile($exportDatas);
        }
        break;
    case 'listHSNReportsGrid':
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $curr_branch = $_SESSION['admin']->finascop_current_branch_id;
        $currentCompanyID = $_SESSION['admin']->finascop_current_company_id;
        $ledgerDeditCombo = $_POST['search_hsnCode'];
        $search = " AND 1=1 and ledg_Id = {$ledgerDeditCombo} ";
        $search_hsnCodeVal = $_POST['search_hsnCodeVal'];
        $search_hsngstCode = $_POST['search_hsngstCode'];
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
        $acet_Date = $_POST['date'];
        $acet_DateTo = $_POST['dateto'];

        if ($ledgerDeditCombo > 0 || $search_hsngstCode > 0) {
            if($ledgerDeditCombo > 0){
                $itemiDS = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_ID) FROM  finascop_stock_itemmaster WHERE stit_HSNCode = '{$ledgerDeditCombo}' ");
                $hsn_gst = $db->getItemFromDB("SELECT gst_percent FROM finascop_hsn WHERE hsn_id = '{$ledgerDeditCombo}'");
                $hsnorgst = $search_hsnCodeVal;
            }else{
                $itemiDS = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_ID) FROM  finascop_stock_itemmaster WHERE stit_GST = '{$search_hsngstCode}' ");
                $hsnorgst = $search_hsngstCode;
            }
            
            
            if (!empty($itemiDS)) {
                $qry = "SELECT '{$hsnorgst}' AS hsn_code, itemid, SUM(cnt) AS invoice_count, SUM(dd) AS total_cgst,SUM(pp) AS total_sgst,SUM(kk) AS total_gst,SUM(SS) AS total_sales  
                FROM ((SELECT b2bso_itemid AS itemid, COUNT(DISTINCT (bbso_id)) AS cnt,SUM(b2bso_netamount) AS ss,
                SUM(b2bso_cgst_value) AS dd,SUM(b2bso_sgst_value) AS pp,SUM(b2bso_cgst_value
+b2bso_sgst_value) AS kk FROM retaline_B2B_SalesOrderDetails WHERE b2bso_itemid IN ({$itemiDS}) AND  b2bso_createdon BETWEEN '{$acet_Date}' AND  '{$acet_DateTo}' GROUP BY b2bso_itemid
)UNION (
SELECT item_product_id AS itemid, COUNT(DISTINCT(customer_order_id)) AS cnt,SUM(item_price) AS ss,SUM(item_price*item_cgst/100) AS dd,SUM(item_price*item_sgst/100) AS pp,SUM(item_price*item_igst/100) AS kk 
FROM retaline_customer_order_items WHERE item_product_id IN ({$itemiDS}) AND created_at BETWEEN '{$acet_Date}' AND  '{$acet_DateTo}' GROUP BY item_product_id)) AS totaltable GROUP BY itemid";
                $listQry = "{$qry} LIMIT {$rec_start}, {$rec_limit} ";
                $result = $db->getMultipleData($listQry, true);
                $_SESSION['hsnreportexport'] = $qry;
                $count = $db->getItemFromDB("SELECT COUNT(*) FROM ({$qry}) AS hsnCount");
            }
        }

        if (!empty($result)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($result) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'HSNreportsrexport':
        $datas = $db->getMulipleData($_SESSION['hsnreportexport'], true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $exportDatas[$i]['HSN'] = $datas[$i]['hsn_code'];
                $exportDatas[$i]['TAX'] = $datas[$i]['hsn_gst'];
                $exportDatas[$i]['Invoice Count'] = $datas[$i]['invoice_count'];
                $exportDatas[$i]['Total Sales'] = $datas[$i]['total_sales'];
                $exportDatas[$i]['Total GST'] = $datas[$i]['total_gst'];
                $exportDatas[$i]['CGST'] = $datas[$i]['total_cgst'];
                $exportDatas[$i]['SGST'] = $datas[$i]['total_sgst'];
            }
            $filename = time() . ".xls";
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");

            ExportFile($exportDatas);
        }
        break;
    case 'getHSNs':
        $qry = $db->getMulipleData("SELECT hsn_id,hsn_code FROM finascop_hsn WHERE status = 1 ", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'getGSTs':
        $qry = $db->getMulipleData("SELECT hsn_id,gst_percent FROM finascop_hsn  ", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
} 