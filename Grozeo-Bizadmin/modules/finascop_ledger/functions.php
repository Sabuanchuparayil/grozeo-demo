<?php

function getIncANDExpTotal($accled_Branch_con, $OpenBal_Br_con, $company) {
    global $db;
    $qry = "SELECT 
				 SUM(UnionReturn.DrAmts)  AS DrAmts
				, SUM(UnionReturn.CrAmts) AS CrAmts						
				FROM (SELECT SUM(IF(actr_IsDebtor=1,finascop_accounts_transaction.actr_amount,0))  AS DrAmts ,
				SUM(IF(actr_IsDebtor=0,finascop_accounts_transaction.actr_amount,0))  AS CrAmts,
				finascop_accounts_groups.Group_ID AS LEDID 
				FROM (" . FINASCOP_DB . "finascop_accounts_ledger INNER join " . FINASCOP_DB . "finascop_accounts_transaction 
				ON finascop_accounts_ledger.accled_Ledger_Id = finascop_accounts_transaction.ledg_Id			 
				INNER join " . FINASCOP_DB . "finascop_accounts_groups ON finascop_accounts_groups.Group_ID = finascop_accounts_ledger.Group_ID)  
				WHERE finascop_accounts_transaction.actr_IsApproved = 1 
				AND NatGroupID in (1,2)
				AND comp_Id={$company} {$accled_Branch_con}
				GROUP BY finascop_accounts_ledger.accled_Ledger_Id 
				UNION ALL
				SELECT 
				SUM(IF(OpenBal_IsDebtor=1,OpenBal_Amt,0))  AS DrAmts ,
				SUM(IF(OpenBal_IsDebtor=0,OpenBal_Amt,0))  AS CrAmts
				,finascop_accounts_ledger.Group_ID  AS LEDID
				from " . FINASCOP_DB . "finascop_accounts_openingbalance
				INNER join " . FINASCOP_DB . "finascop_accounts_ledger 
				ON finascop_accounts_openingbalance.openBal_Led_ID = finascop_accounts_ledger.accled_Ledger_Id	
				INNER join " . FINASCOP_DB . "finascop_accounts_groups ON finascop_accounts_groups.Group_ID = finascop_accounts_ledger.Group_ID
				WHERE NatGroupID in (1,2) AND OpenBal_CompID = {$company} {$OpenBal_Br_con}
				GROUP BY openBal_Led_ID	) AS UnionReturn 
				INNER join " . FINASCOP_DB . "finascop_accounts_groups ON finascop_accounts_groups.Group_ID = UnionReturn.LEDID		 
				";
    $NatGroupName = $db->getFromDB($qry, true);
    return array('CrAmts' => $NatGroupName['CrAmts'], 'DrAmts' => $NatGroupName['DrAmts']);
}

function _exportExcelReport($data) {

    /*
     * Created on 25-Nov-10
     * @author : Azad K G <azad@saturn.in>
     *
     * To create excel of report
     */
    global $db;
    /* Title Settings */
    require_once INCLUDE_PATH . '/simpleExcelWriter.php';

    $query = $_SESSION['Export']['Query'];



    $heads = json_decode(stripslashes($data['headers']), true);
    $fields = json_decode(stripslashes($data['dataindexes']), true);
    $excel = new simpleExcelWriter($db);
    $time = date('YmdHis');
    if (!empty($data['name'])) {
        if ($data['name'] == 'partner_daily_') {
            $lastParameters = json_decode(stripslashes(($_POST["filterData"])), true);
            $excel->exportFile = $data['name'] . $lastParameters['daily_date'] . '.xls';
        } else {
            $excel->exportFile = $data['name'] . $time . '.xls';
        }
    } else if (!empty($data['park_from_Date'])) {
        $excel->exportFile = $data['username'] . '_' . $data['park_from_Date'] . 'to' . $data['park_to_Date'] . '.xls';
    } else {
        $excel->exportFile = $_SESSION['Export']['Settings']['title'] . $time . '.xls';
    }

    $excel->totalFields = (isset($_SESSION['Export']['Settings']['totalFields'])) ? $_SESSION['Export']['Settings']['totalFields'] : false;
    $excel->export($query, $heads, $fields);
    exit();
}

function retalineExportToExcel($data, $file = false, $win = false, $title) {
    global $db;
    /* Title Settings */
    require_once INCLUDE_PATH . '/simpleExcelWriter.php';
    if ($win == 'ledgerbalancereport') {
        $query = $_SESSION['ledgerBalreportqry'];
    }else if ($win == 'trialbalancereport') {
        $query = $_SESSION['trialBalreportqry'];
    }
    try {
        //echo $query;exit();
        $heads = json_decode(stripslashes($data['headers']), true);
        $fields = json_decode(stripslashes($data['dataindexes']), true);
        $excel = new simpleExcelWriter($db);
        if ($file === false) {
            $excel->exportFile = $title . '.xls';
            $excel->export($query, $heads, $fields);
            exit();
        } else {
            echo $query;
            $excel->output($query, $heads, $fields, $file);
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function retalineExportToExcelFromArr($data, $file = false, $win = false, $title) {
    global $db;
    /* Title Settings */
    require_once INCLUDE_PATH . '/simpleExcelWriter.php';
    if ($win == 'trialbalancereport') {
        $query = $_SESSION['trialBalreportqry'];
    }
    try {
        //print_r($query) ;exit();
        $heads = json_decode(stripslashes($data['headers']), true);
        $fields = json_decode(stripslashes($data['dataindexes']), true);
        $excel = new simpleExcelWriter($db);
        if ($file === false) {
            $excel->exportFile = $title . '.xls';
            $excel->exportFromArray($query, $heads, $fields);
            exit();
        } else {
            echo $query;
            $excel->output($query, $heads, $fields, $file);
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}