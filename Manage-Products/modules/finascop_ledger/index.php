<?php

require_once(EXTERNAL_LIBRARY_PATH);
require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/finascop_config/config.php');
require_once(INCLUDE_PATH . "/finascop_accounts_Transactions.php");
require_once(INCLUDE_PATH . "/finascop_common_functions.php");


global $db;
switch ($op) {
    case 'getGroupDetails':
        $qry = "SELECT GroupName AS group_name, NatGroupID AS id, "
                . "(SELECT NatGroupName from " . FINASCOP_DB . "finascop_accounts_natureofgroups ang WHERE ang.NatGroupId = ag.NatGroupID) AS natureOfGroup,"
                . "Group_ShortCode AS  group_shortname "
                . "from " . FINASCOP_DB . "finascop_accounts_groups ag WHERE Group_ID = '{$_POST['id']}' ";
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'saveGroup':

        $group = $_POST['group_name'];
        $nogID = $_POST['natureOfGroup'];
        $shortCode = $_POST['group_shortname'];

        if (intval($_POST['id']) == 0) {
            $qry = "SELECT COUNT(1) from " . FINASCOP_DB . "finascop_accounts_groups WHERE UPPER(GroupName) = UPPER('{$group}')";
        } else {
            $qry = "SELECT COUNT(1) from " . FINASCOP_DB . "finascop_accounts_groups WHERE UPPER(GroupName) = UPPER('{$group}') AND Group_ID <> {$_POST['id']}";
        }

        $count = $db->getItemFromDB($qry, true);
        if ($count > 0) {
            echo "{success: false, msg: 'Group Name already exist.' }";
            exit;
        }

        if (intval($_POST['id']) == 0) {
            $qry = "SELECT COUNT(1) from " . FINASCOP_DB . "finascop_accounts_groups WHERE UPPER(Group_ShortCode) = UPPER('{$shortCode}')";
        } else {
            $qry = "SELECT COUNT(1) from " . FINASCOP_DB . "finascop_accounts_groups WHERE UPPER(Group_ShortCode) = UPPER('{$shortCode}') AND Group_ID <> {$_POST['id']}";
        }

        $countCode = $db->getItemFromDB($qry, true);
        if ($countCode > 0) {
            echo "{success: false, msg: 'Group Short Name already exist.' }";
            exit;
        }

        if (intval($_POST['id']) == 0) {
            $groupAPIkey = '';
            while ($groupAPIkey == '') {
                $groupAPIkey = getNewFinascopApiKey();
            }
            $qry = "SELECT MAX(Group_ID) from " . FINASCOP_DB . "finascop_accounts_groups";
            $max = $db->getItemFromDB($qry, true);

            $groupData = array(
                'Group_ID' => $max + 1,
                'GroupName' => $group,
                'NatGroupID' => $nogID,
                'System' => 0,
                'Group_ReferenceId' => $groupAPIkey,
                'Group_RefIdCRC32' => crc32($groupAPIkey),
                'Group_ShortCode' => $shortCode
            );
            $status = $db->perform(FINASCOP_DB . "finascop_accounts_groups", $groupData);
            if ($status == 1) {
                echo "{success: true,msg:'Saved Successfully'}";
            } else {
                echo "{success: false, msg: 'Error occured while saving data'}";
            }
        } else {
            $groupData = array(
                'GroupName' => $group,
                'NatGroupID' => $nogID,
                'System' => 0,
                'Group_ShortCode' => $shortCode
            );
            $con = 'Group_ID = ' . intval($_POST['id']);
            $status = $db->perform(FINASCOP_DB . "finascop_accounts_groups", $groupData, 'update', $con);
            if ($status == 1) {
                echo "{success: true,msg:'Updated Successfully'}";
            } else {
                echo "{success: false, msg: 'Error occured while saving data'}";
            }
        }

        break;
    case 'listNatureOfGroups':
        $qry = "SELECT NatGroupId AS id, NatGroupName AS nog from " . FINASCOP_DB . "finascop_accounts_natureofgroups";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listGroups':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $rec_sort = empty($data['sort']) ? 'id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filterCon = "";

        // SECURITY: use buildSafeFilterQuery
        $allowedFields = ['led_name', 'led_code', 'led_group'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        /* RAW (disabled): if (isset($_POST['filter']) && $_POST['filter'] != '') { */
            $precondition = " where ";
            foreach ($_POST['filter'] as $key => $v) {
                switch ($v['field']) {

                    case 'group_name':
                        $filterCon .= $precondition . "GroupName like '" . $v['data']['value'] . "%'";
                        break;
                    case 'nature_of_group':
                        $filterCon .= $precondition . "NatGroupName like '" . $v['data']['value'] . "%'";
                        break;
                    case 'group_ReferenceId':
                        $filterCon .= $precondition . "Group_ReferenceId like '" . $v['data']['value'] . "%'";
                        break;
                }

                $precondition = " and ";
            }
        }


        $qry = "SELECT Group_ID as SlNo,Group_ID AS id, GroupName AS group_name, "
                . "NatGroupName AS nature_of_group ,System,Group_ReferenceId AS group_ReferenceId FROM "
                . FINASCOP_DB . "finascop_accounts_groups ag INNER join " . FINASCOP_DB . "finascop_accounts_natureofgroups anog ON anog.NatGroupId = ag.NatGroupID {$filterCon}"
                . " ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        //echo $qry; exit;
        $count = $db->getItemFromDB("SELECT count(*) FROM "
                . FINASCOP_DB . "finascop_accounts_groups ag INNER join " . FINASCOP_DB . "finascop_accounts_natureofgroups anog ON anog.NatGroupId = ag.NatGroupID {$filterCon}");
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    //for loading  ledger grid details 
    case 'SaveOpeningBalance':

        $qry = "DELETE from " . FINASCOP_DB . "finascop_accounts_openingbalance WHERE OpenBal_CompID = {$_SESSION['admin']->finascop_current_company_id} AND OpenBal_BrID = {$_SESSION['admin']->finascop_current_branch_id}";
        //print_r($_POST);

        $db->query($qry);
        $griddata = json_decode($_POST['jsonData'], true);


        if (isset($griddata)) {
            $amt = 0;
            $isDebtor = 2;
            foreach ($griddata as $rowkey => $rowvalue) {

                if ($rowvalue['DrAmts'] > 0) {
                    $amt = $rowvalue['DrAmts'];
                    $isDebtor = 1;
                }
                if ($rowvalue['CrAmts'] > 0) {
                    $amt = $rowvalue['CrAmts'];
                    $isDebtor = 0;
                }

                if ($amt > 0) {
                    $openingBalanceEntry = array(
                        'openBal_Led_ID' => $rowvalue['accled_Ledger_Id'],
                        'OpenBal_Amt' => $amt,
                        'OpenBal_CompID' => $_SESSION['admin']->finascop_current_company_id,
                        'OpenBal_BrID' => $_SESSION['admin']->finascop_current_branch_id,
                        'OpenBal_IsDebtor' => $isDebtor
                    );
                    $amt = 0;

                    $db->perform(FINASCOP_DB . "finascop_accounts_openingbalance", $openingBalanceEntry);
                }
            }
        }

        break;
    case 'getOpeningBalance':
        $search = " WHERE 1=1 ";
        //$filter = $_POST['filter'];
        /*if (isset($filter)) {

            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');
                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                    }
                    //}
                }
            }
        }*/
        //    $filter = $_POST['ledger_filter'];
        $listQuery = "SELECT al.accled_Ledger_Id,CONCAT(al.ledgertypename,' (',al.GroupName,')') AS LedgerName, 
		IF(COALESCE(aob.OpenBal_IsDebtor,0)=1, COALESCE(aob.OpenBal_Amt,''),'') AS DrAmts,
		IF(COALESCE(aob.OpenBal_IsDebtor,0)=1,'',COALESCE(aob.OpenBal_Amt,'')) AS CrAmts
		from " . FINASCOP_DB . "finascop_accounts_ledger al
		LEFT join " . FINASCOP_DB . "finascop_accounts_openingbalance aob ON al.accled_Ledger_Id = aob.openBal_Led_ID AND accled_BranchId = openbal_brid 
		WHERE  al.accled_CompId = {$_SESSION['admin']->finascop_current_company_id} AND al.accled_BranchId = {$_SESSION['admin']->finascop_current_branch_id}";
//CONCAT(al.ledgertypename,' (',al.GroupName,')') LIKE '" . $filter . "%' AND
        $data = $db->getMultipleData($listQuery, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'getLedgers' :



        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $rec_sort = empty($_POST['sort']) ? 'al.ledgertypeid' : $_POST['sort'];
        $rec_sort_dir = empty($_POST['dir']) ? 'DESC' : $_POST['dir'];


        $filter = $_POST['filter'];
        $filter_query = "";
        if (isset($filter)) {
            
           foreach ($_POST['filter'] as $key => $val) {
                switch ($val['field']) {

                    case 'ledger_ReferenceId':
                        $filter_query  .= " AND al.accled_ReferenceId LIKE '%" . $val['data']['value'] . "%' ";
                        break;
                    default:
                        $filter_query .= " AND al." . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
                        break;
                }

                $precondition = " and ";
            }

        }

        $listQuery = "SELECT DISTINCT al.ledgertypename, alt.GroupName,al.ledgertypeid,isSystem,
		(SELECT comp_name from " . FINASCOP_DB . "finascop_company WHERE comp_id = (SELECT DISTINCT accled_CompId 
		from " . FINASCOP_DB . "`finascop_accounts_ledger` WHERE accled_IsEnabled = 1 AND ledgertypeid = al.ledgertypeid 
                    AND al.accled_CompId = finascop_accounts_ledger.accled_CompId )) AS accled_Comp,
		(SELECT DISTINCT accled_CompId 
		from " . FINASCOP_DB . "`finascop_accounts_ledger` WHERE accled_IsEnabled = 1 AND ledgertypeid = al.ledgertypeid 
                    AND al.accled_CompId = finascop_accounts_ledger.accled_CompId ) AS accled_Comp_id,
		al.ledgertypeid ,al.Group_ID,(select accled_ReferenceId from " . FINASCOP_DB . "finascop_accounts_ledger where accled_IsEnabled = 1 
                and ledgertypeid = alt.ledgertypeid and accled_BranchId= {$_SESSION['admin']->finascop_current_branch_id} ) as ledger_ReferenceId
		from " . FINASCOP_DB . "finascop_accounts_ledgertype alt
		INNER join " . FINASCOP_DB . "finascop_accounts_ledger al ON alt.ledgertypeid = al.ledgertypeid
		WHERE al.ledgertypename != ''  AND al.accled_CompId = {$_SESSION['admin']->finascop_current_company_id} and accled_BranchId= {$_SESSION['admin']->finascop_current_branch_id} $filter_query
		ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit ";
        //echo $listQuery; exit;
        $countQuery = "SELECT count(DISTINCT al.ledgertypename)
		from " . FINASCOP_DB . "finascop_accounts_ledgertype alt
		INNER join " . FINASCOP_DB . "finascop_accounts_ledger al ON alt.ledgertypeid = al.ledgertypeid
		WHERE al.ledgertypename != '' AND al.accled_CompId = {$_SESSION['admin']->finascop_current_company_id} and accled_BranchId= {$_SESSION['admin']->finascop_current_branch_id} $filter_query";

        //$db->printGridJson($countQuery, $listQuery);
        $count = $db->getItemFromDB($countQuery);
        $data = $db->getMultipleData($listQuery, true);
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    //for loading  branches grid details 
    case 'getLedgerBranches':
        $qry = "SELECT group_concat(br_ID) from " . FINASCOP_DB . "finascop_branch"
                . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$_POST['company']}) ";
        $active_branch = " AND br.br_ID IN (" . $db->getItemFromDB($qry) . ")";
        //echo $qry;
        //if ($_SESSION['admin']->Finascop_UserId > 1 /* && empty($_POST['lb']) */)
        //    $active_branch = " AND br.br_ID IN (SELECT br_Id from " . FINASCOP_DB . "`finascop_user_activebranches` WHERE UserId = {$_SESSION['admin']->Finascop_UserId})";

        if ($_POST['company'] > 0) {
            $query = "SELECT COALESCE(if(accled_Ledger_Id>0,IF(COALESCE(accled_IsEnabled,0)=1, 1, 0),0),0)
			AS isChecked, br_Name, br_ID, 
			COALESCE(if(accled_Ledger_Id>0,1,0),0) AS accled_Ledger_Id_true, branch_shortname, 
			COALESCE(accled_IsEnabled,0) AS accled_IsEnabled, 
			COALESCE(accled_Ledger_Id,0) AS  accled_Ledger_Id
			from " . FINASCOP_DB . "finascop_branch br LEFT join " . FINASCOP_DB . "finascop_accounts_ledger leb 
			ON br.br_ID = leb.accled_BranchId   AND ledgertypeid = '" . $_POST['ledger_id'] . "'
			WHERE br.br_ID IN (SELECT br_Id from " . FINASCOP_DB . "`finascop_branch_company` 
			WHERE comp_id = {$_POST['company']}) $active_branch  order by br.br_Name";
            //echo $query;
            $branches = $db->getMultipleData($query, true);
            $totalCount = sizeof($branches);
            $data = array("totalCount" => $totalCount, "branches" => $branches);
            echo json_encode($data);
        }
        break;
    case 'getBranches':
        //$and_query = "";
        // if ($_POST['ledger_id'] > 0)
        //    $and_query = " AND ledgertypeid = '" . $_POST['ledger_id'] . "' ";
        switch ($_SESSION['admin']->finascop_typId) {
            case 1:
                $qry = "SELECT group_concat(br_ID)  from " . FINASCOP_DB . "finascop_branch"
                        . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$_POST['company']}) ";
                break;
            case 2:
                $qry = "SELECT group_concat(br_ID) from " . FINASCOP_DB . "finascop_branch"
                        . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$_POST['company']}) ";
                break;
            case 3:
                $qry = "SELECT group_concat(br_ID) from " . FINASCOP_DB . "finascop_branch"
                        . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$_POST['company']}) AND "
                        . " br_ID IN (SELECT br_Id from " . FINASCOP_DB . "finascop_user_activebranches WHERE UserId = {$_SESSION['admin']->Finascop_UserId})";
                break;
            case 4:
                $qry = "SELECT group_concat(br_ID) from " . FINASCOP_DB . "finascop_branch_company  "
                        . " WHERE  comp_id= {$_POST['company']} and finascop_branch_company.br_Id IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_user_auditingbranches` WHERE   UserId = {$_SESSION['admin']->Finascop_UserId} ) ";
                break;
            default:
                $qry = "SELECT group_concat(br_ID) from " . FINASCOP_DB . "finascop_branch"
                        . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$_POST['company']}) ";
        }

        $active_branch = " AND br.br_ID IN (" . $db->getItemFromDB($qry) . ")";
        //if ($_SESSION['admin']->Finascop_UserId > 1 /* && empty($_POST['lb']) */)
        //    $active_branch = " AND br.br_ID IN (SELECT br_Id from " . FINASCOP_DB . "`finascop_user_activebranches` WHERE UserId = {$_SESSION['admin']->Finascop_UserId})";

        if ($_POST['company'] > 0) {
            $query = "SELECT COALESCE(if(accled_Ledger_Id>0,IF(COALESCE(accled_IsEnabled,0)=1, 1, 0),0),0)
			AS isChecked, br_Name, br_ID, 
			COALESCE(if(accled_Ledger_Id>0,1,0),0) AS accled_Ledger_Id_true, branch_shortname,CONCAT(branch_shortname,'-',br_Name) AS brsnnam,
			COALESCE(accled_IsEnabled,0) AS accled_IsEnabled, 
			COALESCE(accled_Ledger_Id,0) AS  accled_Ledger_Id
			from " . FINASCOP_DB . "finascop_branch br LEFT join " . FINASCOP_DB . "finascop_accounts_ledger leb 
			ON br.br_ID = leb.accled_BranchId   AND ledgertypeid = '" . $_POST['ledger_id'] . "'
			WHERE br.br_ID IN (SELECT br_Id from " . FINASCOP_DB . "`finascop_branch_company` 
			WHERE comp_id = {$_POST['company']}) $active_branch  order by br.br_Name";
            //echo $query;
            $branches = $db->getMultipleData($query, true);
            $totalCount = sizeof($branches);
            $data = array("totalCount" => $totalCount, "branches" => $branches);
            echo json_encode($data);
        }
        break;

    //for loading group combo
    case 'getGroups':

        if ($_POST['query'] != "")
            $filter_query = "And GroupName like '" . $_POST['query'] . "%'";
        $query = "Select Group_ID, GroupName from " . FINASCOP_DB . "finascop_accounts_groups 
		where 1=1 $filter_query Order By GroupName Asc;";
        $rs = $db->query($query);
        $i = 0;
        $num_rows = $db->num_rows($rs);
        echo "[";
        while ($row = $db->fetch_array($rs)) {

            echo "[";
            echo '"' . $row['Group_ID'] . '","' . addslashes($row['GroupName']) . '"';
            echo "]";
            $i++;
            if ($i < $num_rows)
                echo ",";
            flush();
        }
        echo "]";
        break;
    //save ledger

    case 'saveLedger':

        $ledger = $_POST['ledger'];
        $branches = json_decode($_POST['branches'], true);
        $db->query("begin");
        $accVoucher = new \finascop\accounts\Transactions\AccountingVouchers();
        $returned = $accVoucher->saveLedgers($ledger, $_POST['ldg_company'], $branches);
        $ledgerentry = json_decode($returned);
        if ($ledgerentry->success == true) {
            $db->query("commit");
        }
        echo $returned;
        break;

    case 'getCompany':
        echo "[";
        echo "[";
        echo '"' . $_SESSION['admin']->finascop_current_company_id . '","' . addslashes($_SESSION['admin']->finascop_current_company) . '"';
        echo "]";
        flush();
        echo "]";
        return;

        //        if ($_POST['query'] != "")
        //            $filter_query = "And comp_name like '" . $_POST['query'] . "%'";
        //
        //        if ($_SESSION['admin']->Finascop_UserId > 1)
        //            $query = "SELECT DISTINCT c.comp_id as comp_id,comp_name from " . FINASCOP_DB . "finascop_company c
        //		INNER join " . FINASCOP_DB . "`finascop_branch_company` bc
        //		ON bc.comp_id = c.comp_id
        //		WHERE bc.br_Id IN (SELECT br_Id from " . FINASCOP_DB . "`finascop_user_activebranches` WHERE UserId = {$_SESSION['admin']->Finascop_UserId})  
        //		$filter_query Order By comp_name Asc";
        //        else {
        //            $query = "SELECT DISTINCT comp_id as comp_id,comp_name from " . FINASCOP_DB . "finascop_company $filter_query Order By comp_name Asc";
        //        }
        //
        //        $rs = $db->query($query);
        //        $i = 0;
        //        $num_rows = $db->num_rows($rs);
        //        echo "[";
        //        while ($row = $db->fetch_array($rs)) {
        //
        //            echo "[";
        //            echo '"' . $row['comp_id'] . '","' . addslashes($row['comp_name']) . '"';
        //            echo "]";
        //            $i++;
        //            if ($i < $num_rows)
        //                echo ",";
        //            flush();
        //        }
        //        echo "]";
        break;


    case 'getLedgerList':

        $company = $_POST['company'];
        $branch = $_POST['branch'];
        if ($_POST['query'] != "")
            $filter_query = " And CONCAT(al.ledgertypename,' (',al.GroupName,')') like '" . $_POST['query'] . "%'";
        $query = "SELECT al.accled_Ledger_Id,CONCAT(al.ledgertypename,' (',al.GroupName,')') AS accled_LedgerName
		from " . FINASCOP_DB . "finascop_accounts_ledgertype alt
		INNER join " . FINASCOP_DB . "finascop_accounts_ledger al ON alt.ledgertypeid = al.ledgertypeid
		WHERE al.ledgertypename != ''  AND accled_BranchId = {$branch}
		AND accled_CompId = {$company}{$filter_query}
		ORDER BY al.ledgertypename";

        $ledgers = $db->getMultipleData($query, true);
        $totalCount = sizeof($ledgers);
        $data = array("totalCount" => $totalCount, "data" => $ledgers);
        echo json_encode($data);

        break;

    case 'listLedgerDetails':

        $ledger = $_POST['ledger'];
        $date_from = $_POST['date_from'];
        $date_to = $_POST['date_to'];
        $approvedstatus = " actr_IsApproved " . (($_POST['audit_status'] == 1) ? ' >=0' : ($_POST['audit_status'] == 2 ? ' = 1 ' : ' = 0  '));
        $db->query("set @tot = 0;");

        if ($ledger > 0 && !empty($date_from) && !empty($date_to)) {
            $qry = "SELECT @tot:=@tot+1 AS sl_no,ledg_Id,a.acet_NO,acet_DocNO,(SELECT `name` FROM " . FINASCOP_DB . "`finascop_accounts_type` WHERE id = a.acet_TypeId) AS `type`,
            DATE_FORMAT(actr_Date, '%d-%m-%Y') AS date,
            (IF (actr_IsDebtor = 1,actr_amount,'')) AS dr,(IF (actr_IsDebtor = 0,actr_amount,'')) AS cr,
            (SELECT acet_Narration from " . FINASCOP_DB . "finascop_accounts_entry
            WHERE acet_NO = a.acet_NO) as narration,actr_IsApproved
            from " . FINASCOP_DB . "`finascop_accounts_transaction` a 
            INNER JOIN finascop_accounts_entry fae ON a.acet_NO = fae.acet_NO
            WHERE ledg_Id = {$ledger} AND 
            actr_Date  between STR_TO_DATE('{$date_from}','%d-%m-%Y') AND STR_TO_DATE('{$date_to}','%d-%m-%Y') AND {$approvedstatus} "
                    . "ORDER BY actr_Date";
            
            $_SESSION['ledgerBalreportqry'] = $qry;

            $ledgers = $db->getMultipleData($qry, true);
            if (!empty($ledgers)) {
                $totalCount = sizeof($ledgers);
                $data = array("totalCount" => $totalCount, "data" => $ledgers);
                echo json_encode($data);
            } else {
                echo '{"totalCount":0, "data":[]}';
            }
        } else {
            echo '{"totalCount":0, "data":[]}';
        }
        break;

    case 'updateAmount':

        $ledger = $_POST['ledger'];
        $date_from = $_POST['date_from'];
        $date_to = $_POST['date_to'];
        $approvedstatus = " actr_IsApproved " . (($_POST['audit_status'] == 1) ? ' >=0' : ($_POST['audit_status'] == 2 ? ' = 1 ' : ' = 0  '));
        //echo $approvedstatus;
        if ($ledger > 0 && !empty($date_from) && !empty($date_to)) {
            $data = $db->getFromDB("SELECT SUM(debt) as debt,sum(crdt) as crdt from( SELECT SUM(IF(actr_IsDebtor=1,actr_amount,0)) AS debt,
			SUM(IF(actr_IsDebtor=0,actr_amount,0)) AS crdt
			from " . FINASCOP_DB . "finascop_accounts_transaction WHERE ledg_Id = {$ledger} AND 
			actr_Date < STR_TO_DATE('{$date_from}','%d-%m-%Y') AND {$approvedstatus} 
			UNION ALL
			SELECT 
			SUM(IF(OpenBal_IsDebtor=1,OpenBal_Amt,0))  AS debt ,
			SUM(IF(OpenBal_IsDebtor=0,OpenBal_Amt,0))  AS crdt
			from " . FINASCOP_DB . "finascop_accounts_openingbalance							
			where finascop_accounts_openingbalance.openBal_Led_ID = {$ledger}) as openbal	", true);

            if (!empty($data)) {
                $diff = $data['debt'] - $data['crdt'];
                if ($diff > 0) {
                    echo "{success:true, 'ldg_dr':'" . abs($diff) . "', 'ldg_cr':''}";
                } else if ($diff < 0) {
                    echo "{success:true, 'ldg_dr':'', 'ldg_cr':'" . abs($diff) . "'}";
                } else {
                    echo "{success:true, 'ldg_dr':'', 'ldg_cr':''}";
                }
            }
        }
        break;

    case 'listLedgers':
        $acet_NO = $_POST['acet_NO'];

        $Basic_Nature_nodentQuery = "SELECT 0";

        $listQuery = "SELECT (SELECT accled_LedgerName from " . FINASCOP_DB . "`finascop_accounts_ledger` WHERE accled_Ledger_Id = a.ledg_Id) AS ledger_name,
		if(actr_IsDebtor=1,actr_amount,'') AS dr,
		if(actr_IsDebtor=0,actr_amount,'') AS cr		
		from " . FINASCOP_DB . "`finascop_accounts_transaction` a WHERE acet_NO = '{$acet_NO}' order by entry_type";

        $db->printGridJson($Basic_Nature_nodentQuery, $listQuery);

        break;

    /*  case 'listLedgers':
      $acet_NO = $_POST['acet_NO'];

      $Basic_Nature_nodentQuery = "SELECT count(*) from " . FINASCOP_DB . "`finascop_accounts_transaction` a WHERE acet_NO = '{$acet_NO}'";

      echo $listQuery = "SELECT (SELECT accled_LedgerName from " . FINASCOP_DB . "`finascop_accounts_ledger` WHERE accled_Ledger_Id = a.ledg_Id) AS ledger_name,
      (SELECT a.actr_amount from " . FINASCOP_DB . "finascop_accounts_transaction a INNER join " . FINASCOP_DB . "finascop_accounts_ledger b ON a.ledg_Id = b.accled_Ledger_Id  WHERE a.actr_IsDebtor = 1) AS dr,

      from " . FINASCOP_DB . "`finascop_accounts_transaction` a WHERE a.acet_NO = '{$acet_NO}'";
      exit;

      $db->printGridJson($Basic_Nature_nodentQuery, $listQuery); */







    case 'getAccountStructure':

        $Basic_Nature = $db->getMultipleData("SELECT basic_Id,COALESCE(Basic_Nature,' ') AS Basic_Nature  
		from " . FINASCOP_DB . "finascop_accounts_basicnature                                             
		ORDER BY Basic_Nature", true);

        if (!empty($Basic_Nature)) {
            $Basic_Nature_node = array();
            foreach ($Basic_Nature as $idx => $val) {
                $Basic_Nature_node[$idx] = array();
                $Basic_Nature_node[$idx]['id'] = 'BA_' . $val['basic_Id'];
                $Basic_Nature_node[$idx]['text'] = $val['Basic_Nature'];
                $Basic_Nature_node[$idx]['draggable'] = false;
                $Basic_Nature_node[$idx]['children'] = '';
                $Basic_Nature_node[$idx]['cls'] = 'finascop_basic_nature';


                $NatGroupName = $db->getMultipleData("SELECT NatGroupId, COALESCE(NatGroupName,' ') AS NatGroupName 
				from " . FINASCOP_DB . "finascop_accounts_natureofgroups                                     
				WHERE BasicID = '{$val['basic_Id']}'
				ORDER BY NatGroupName", true);


                if (!empty($NatGroupName)) {
                    $Basic_Nature_node[$idx]['leaf'] = false;
                    $NatGroupName_node = array();
                    foreach ($NatGroupName as $idp => $value) {
                        $NatGroupName_node[$idp] = array();
                        $NatGroupName_node[$idp]['id'] = 'NA_' . $value['NatGroupId'];
                        $NatGroupName_node[$idp]['text'] = $value['NatGroupName'];
                        $NatGroupName_node[$idp]['draggable'] = false;
                        $NatGroupName_node[$idp]['children'] = '';
                        $NatGroupName_node[$idp]['cls'] = 'finascop_nature_group';

                        $GroupName = $db->getMultipleData("SELECT Group_ID,COALESCE(finascop_accounts_groups.GroupName, ' ') AS GroupName 
						FROM 
						" . FINASCOP_DB . "finascop_accounts_groups 
						WHERE NatGroupID = '{$value['NatGroupId']}'
						ORDER BY GroupName", true);

                        if (!empty($GroupName)) {
                            $NatGroupName_node[$idp]['leaf'] = false;
                            $BranchLedgerName_node = array();
                            foreach ($GroupName as $idl => $values) {
                                $BranchLedgerName_node[$idl] = array();
                                $BranchLedgerName_node[$idl]['id'] = 'GR_' . $values['Group_ID'];
                                $BranchLedgerName_node[$idl]['text'] = $values['GroupName'];
                                //  $BranchLedgerName_node[$idl]['leaf'] = false;
                                $BranchLedgerName_node[$idl]['draggable'] = true;
                                $BranchLedgerName_node[$idl]['children'] = '';
                                $BranchLedgerName_node[$idl]['cls'] = 'finascop_group';

                                $Ledgertypename = $db->getMultipleData("SELECT finascop_accounts_ledgertype.ledgertypeid as ledgertypeid,
								COALESCE(finascop_accounts_ledgertype.ledgertypename, ' ') AS Ledgertypename
								FROM 
								" . FINASCOP_DB . "finascop_accounts_ledgertype                                                   
								WHERE finascop_accounts_ledgertype.Group_ID = {$values['Group_ID']}  AND ledgercompid = {$_SESSION['admin']->finascop_current_company_id}
								ORDER BY Ledgertypename", true);


                                if (!empty($Ledgertypename)) {
                                    $BranchLedgerName_node[$idl]['leaf'] = false;
                                    $Ledgertypename_node = array();
                                    foreach ($Ledgertypename as $ld => $Ledgertypename_values) {
                                        $Ledgertypename_node[$ld] = array();
                                        $Ledgertypename_node[$ld]['id'] = 'LT_' . $Ledgertypename_values['ledgertypeid'];
                                        $Ledgertypename_node[$ld]['text'] = $Ledgertypename_values['Ledgertypename'];
                                        //  $Ledgertypename_node[$ld]['leaf'] = false;
                                        $Ledgertypename_node[$ld]['draggable'] = true;
                                        $Ledgertypename_node[$ld]['children'] = '';
                                        $Ledgertypename_node[$ld]['cls'] = 'finascop_ledger_type';

                                        if ($_POST['company'] > 0) {
                                            $con = " AND accled_CompId = {$_POST['company']} ";
                                        }


                                        $ledger_name = $db->getMultipleData("SELECT accled_Ledger_Id, 
										COALESCE(accled_LedgerName, ' ') AS LedgerName
										FROM 
										" . FINASCOP_DB . "finascop_accounts_ledger WHERE ledgertypeid = {$Ledgertypename_values['ledgertypeid']} {$con}
										ORDER BY LedgerName", true);


                                        if (!empty($ledger_name)) {
                                            $Ledgertypename_node[$ld]['leaf'] = false;
                                            $ledger_name_node = array();
                                            foreach ($ledger_name as $ldg => $ldg_values) {
                                                $ledger_name_node[$ldg] = array();
                                                $ledger_name_node[$ldg]['id'] = 'LN_' . $ldg_values['accled_Ledger_Id'];
                                                $ledger_name_node[$ldg]['text'] = $ldg_values['LedgerName'];
                                                $ledger_name_node[$ldg]['leaf'] = true;
                                                $ledger_name_node[$ldg]['draggable'] = true;
                                                $ledger_name_node[$ldg]['cls'] = 'finascop_ledger_name';
                                            }

                                            $Ledgertypename_node[$ld]['children'] = $ledger_name_node;
                                            $ledger_name_node = "";
                                        } else {
                                            $Ledgertypename_node[$ld]['leaf'] = true;
                                            $Ledgertypename_node[$ld]['childeren'] = array();
                                        }
                                    }

                                    /*   if ($values['Group_ID'] == 23) {
                                      print_r($Ledgertypename_node);
                                      exit;
                                      } */

                                    $BranchLedgerName_node[$idl]['children'] = $Ledgertypename_node;
                                    $Ledgertypename_node = array();
                                    $ledger_name_node = array();
                                } else {
                                    $BranchLedgerName_node[$idl]['leaf'] = true;
                                    $BranchLedgerName_node[$idl]['children'] = array();
                                }
                            }
                            $NatGroupName_node[$idp]['children'] = $BranchLedgerName_node;
                            $BranchLedgerName_node = array();
                        } else {
                            $NatGroupName_node[$idp]['leaf'] = true;
                            $NatGroupName_node[$idp]['children'] = array();
                        }
                    }
                    $Basic_Nature_node[$idx]['children'] = $NatGroupName_node;
                    $NatGroupName_node = array();
                } else {
                    $Basic_Nature_node[$idx]['leaf'] = true;
                    $Basic_Nature_node[$idx]['children'] = array();
                }
            }
        }
        //print_r($Basic_Nature_node);
        echo json_encode($Basic_Nature_node);
        break;

    case 'trialBalanceStore':
        $data = $_POST;


        if (!empty($data['branch'])) {
            $br_ID_con = " AND br_ID ={$data['branch']} ";
            $OpenBal_BrID = " AND OpenBal_BrID = {$data['branch']} ";
            $accled_BranchId_con = " AND finascop_accounts_ledger.accled_BranchId = {$data['branch']} ";
        } else {
            switch ($_SESSION['admin']->finascop_typId) {
                case 1:
                    $qry = "SELECT group_concat(br_ID)  from " . FINASCOP_DB . "finascop_branch"
                            . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$data['company']}) ";
                    break;
                case 2:
                    $qry = "SELECT group_concat(br_ID) from " . FINASCOP_DB . "finascop_branch"
                            . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$data['company']}) ";
                    break;
                case 3:
                    $qry = "SELECT group_concat(br_ID) from " . FINASCOP_DB . "finascop_branch"
                            . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$data['company']}) AND "
                            . " br_ID IN (SELECT br_Id from " . FINASCOP_DB . "finascop_user_activebranches WHERE UserId = {$_SESSION['admin']->Finascop_UserId})";
                    break;
                case 4:
                    $qry = "SELECT group_concat(br_ID) from " . FINASCOP_DB . "finascop_branch_company  "
                            . " WHERE  comp_id= {$data['company']} and finascop_branch_company.br_Id IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_user_auditingbranches` WHERE   UserId = {$_SESSION['admin']->Finascop_UserId} ) ";
                    break;
                default:
                    $qry = "SELECT group_concat(br_ID) from " . FINASCOP_DB . "finascop_branch"
                            . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$data['company']}) ";
            }
            $active_branch = $db->getItemFromDB($qry);
            $br_ID_con = " AND br_ID in ({$active_branch})";
            $OpenBal_BrID = " AND OpenBal_BrID in ({$active_branch}) ";
            $accled_BranchId_con = " AND finascop_accounts_ledger.accled_BranchId in ({$active_branch}) ";
        }

        if ($data['ind'] == 1) {
            $query = "SELECT 	
			(SELECT GroupName from " . FINASCOP_DB . "`finascop_accounts_groups` WHERE Group_ID = GrpID) AS GroupName
			, IF (SUM(UnionReturn.DrAmts)-SUM(UnionReturn.CrAmts) > 0,SUM(UnionReturn.DrAmts)-SUM(UnionReturn.CrAmts),0) AS DrAmts
			, IF (SUM(UnionReturn.DrAmts)-SUM(UnionReturn.CrAmts) < 0,SUM(UnionReturn.CrAmts)-SUM(UnionReturn.DrAmts),0) AS CrAmts
			, COALESCE(UnionReturn.GrpID,-1) AS GrpID 
			, IF (SUM(UnionReturn.DrAmts)-SUM(UnionReturn.CrAmts) < 0,1,0) AS IsCreditor
			FROM 
			(SELECT 
			SUM(IF(actr_IsDebtor=1,finascop_accounts_transaction.actr_amount,0))  AS DrAmts ,
			SUM(IF(actr_IsDebtor=0,finascop_accounts_transaction.actr_amount,0))  AS CrAmts 
			, finascop_accounts_ledger.accled_Ledger_Id AS LEDID
			, Group_ID  AS GrpID
			FROM (" . FINASCOP_DB . "finascop_accounts_ledger 
			INNER join " . FINASCOP_DB . "finascop_accounts_transaction ON finascop_accounts_ledger.accled_Ledger_Id = finascop_accounts_transaction.ledg_Id)		 
			WHERE finascop_accounts_transaction.actr_IsApproved = 1 			
			AND comp_Id={$data['company']} {$br_ID_con}
			GROUP BY finascop_accounts_ledger.accled_Ledger_Id 
			UNION ALL				
			SELECT 
			
			SUM(IF(OpenBal_IsDebtor=1,OpenBal_Amt,0))  AS DrAmts ,
			SUM(IF(OpenBal_IsDebtor=0,OpenBal_Amt,0))  AS CrAmts 			
			,openBal_Led_ID AS LEDID
			,Group_ID  AS GrpID
			from " . FINASCOP_DB . "finascop_accounts_openingbalance OP
			INNER join " . FINASCOP_DB . "finascop_accounts_ledger LED ON LED.accled_Ledger_Id=OP.openBal_Led_ID			
			WHERE  OpenBal_CompID = {$data['company']} {$OpenBal_BrID}
			GROUP BY openBal_Led_ID
			) AS UnionReturn
			GROUP BY UnionReturn.GrpID
			ORDER BY UnionReturn.GrpID;";
        } else if ($data['ind'] == 2) {
            $query = "SELECT 
			BR.br_Name
			, SUM(UnionReturn.DrAmts)  AS DrAmts
			, SUM(UnionReturn.CrAmts) AS CrAmts
			, {$data['group']} AS grpid
			, IF (DrAmts-CrAmts < 0,1,0) AS 'IsCreditor'
			, BR.br_ID AS BRID
			FROM 
			(SELECT 
			SUM(IF(actr_IsDebtor=1,finascop_accounts_transaction.actr_amount,0))  AS DrAmts ,
			SUM(IF(actr_IsDebtor=0,finascop_accounts_transaction.actr_amount,0))  AS CrAmts ,
			finascop_accounts_ledger.accled_BranchId  AS BRID
			FROM (finascop_accounts_ledger INNER join " . FINASCOP_DB . "finascop_accounts_transaction ON 
			finascop_accounts_ledger.accled_Ledger_Id = finascop_accounts_transaction.ledg_Id)
			WHERE 
			finascop_accounts_transaction.actr_IsApproved = 1 
			AND Group_ID = {$data['group']}
			AND comp_Id= {$data['company']}
			{$br_ID_con}
			GROUP BY  finascop_accounts_transaction.ledg_Id,  finascop_accounts_transaction.br_ID
			UNION ALL
			SELECT
			SUM(IF(OpenBal_IsDebtor=1,OpenBal_Amt,0))  AS DrAmts ,
			SUM(IF(OpenBal_IsDebtor=0,OpenBal_Amt,0))  AS CrAmts , OpenBal_BrID AS BRID
			from " . FINASCOP_DB . "finascop_accounts_openingbalance
			INNER join " . FINASCOP_DB . "finascop_accounts_ledger ON finascop_accounts_openingbalance.openBal_Led_ID = finascop_accounts_ledger.accled_Ledger_Id    
			WHERE Group_ID = {$data['group']} AND OpenBal_CompID = {$data['company']} {$OpenBal_BrID}
			GROUP BY accled_Ledger_Id,accled_BranchId) 
			AS UnionReturn 
			INNER join " . FINASCOP_DB . "finascop_branch BR ON UnionReturn.BRID = BR.br_ID
			GROUP BY BR.br_ID";
        } else if ($data['ind'] == 3) {
            $query = "SELECT 
			finascop_accounts_ledger.accled_LedgerName
			, SUM(UnionReturn.DrAmts)  AS DrAmts
			, SUM(UnionReturn.CrAmts) AS CrAmts
			, finascop_accounts_ledger.accled_Ledger_Id
			, IF (DrAmts-CrAmts < 0,1,0) AS 'IsCreditor'
			FROM 
			(SELECT 
			SUM(IF(actr_IsDebtor=1,finascop_accounts_transaction.actr_amount,0))  AS DrAmts ,
			SUM(IF(actr_IsDebtor=0,finascop_accounts_transaction.actr_amount,0))  AS CrAmts,
			finascop_accounts_ledger.accled_Ledger_Id 
			FROM (finascop_accounts_ledger INNER join " . FINASCOP_DB . "finascop_accounts_transaction 
			ON finascop_accounts_ledger.accled_Ledger_Id = finascop_accounts_transaction.ledg_Id) 
			WHERE 
			finascop_accounts_transaction.actr_IsApproved = 1 
			AND  Group_ID = {$data['group']}
			AND comp_Id= {$data['company']} {$accled_BranchId_con}
			GROUP BY finascop_accounts_ledger.accled_Ledger_Id
			UNION ALL 
			SELECT SUM(IF(OpenBal_IsDebtor=1,OpenBal_Amt,0))  AS DrAmts ,
			SUM(IF(OpenBal_IsDebtor=0,OpenBal_Amt,0))  AS CrAmts,  finascop_accounts_ledger.accled_Ledger_Id 
			from " . FINASCOP_DB . "finascop_accounts_openingbalance
			INNER join " . FINASCOP_DB . "finascop_accounts_ledger ON finascop_accounts_openingbalance.openBal_Led_ID = finascop_accounts_ledger.accled_Ledger_Id			
			WHERE  Group_ID = {$data['group']} AND OpenBal_CompID = {$data['company']} {$OpenBal_BrID}
			GROUP BY  finascop_accounts_ledger.accled_Ledger_Id) 
			AS UnionReturn 
			INNER join " . FINASCOP_DB . "finascop_accounts_ledger ON UnionReturn.accled_Ledger_Id = finascop_accounts_ledger.accled_Ledger_Id 
			GROUP BY finascop_accounts_ledger.accled_LedgerName, finascop_accounts_ledger.accled_Ledger_Id HAVING 
			(SUM(UnionReturn.DrAmts) <> 0 OR  SUM(UnionReturn.CrAmts) <> 0 ) 
			ORDER BY finascop_accounts_ledger.accled_LedgerName";
        }
        //echo $query;
        $_SESSION['trialBalreportqry'] = $query;
        $trial = $db->getMultipleData($query, true);
        $rettrial = array();
        $DrAmtTot = 0;
        $CrAmtTot = 0;
        foreach ($trial as $trialkey => $trialvalue) {
            if ($trialvalue['DrAmts'] - $trialvalue['CrAmts'] > 0) {
                $trial[$trialkey]['DrAmts'] = $trialvalue['DrAmts'] - $trialvalue['CrAmts'];
                $trial[$trialkey]['CrAmts'] = '';
                $DrAmtTot += $trial[$trialkey]['DrAmts'];
                array_push($rettrial, $trial[$trialkey]);
            } elseif ($trialvalue['DrAmts'] - $trialvalue['CrAmts'] < 0) {
                $trial[$trialkey]['CrAmts'] = $trialvalue['CrAmts'] - $trialvalue['DrAmts'];
                $trial[$trialkey]['DrAmts'] = '';
                $CrAmtTot += $trial[$trialkey]['CrAmts'];
                array_push($rettrial, $trial[$trialkey]);
            }
        }
        if ($data['ind'] == 1) {
            if (round($DrAmtTot - $CrAmtTot, 2) > 0) {
                $trial["Opening Balance Difference"]['CrAmts'] = round($DrAmtTot - $CrAmtTot, 2);
                $trial["Opening Balance Difference"]['DrAmts'] = '';
                $trial["Opening Balance Difference"]['GroupName'] = 'Difference In Opening Balance';
                array_push($rettrial, $trial["Opening Balance Difference"]);
            }
            if (round($CrAmtTot - $DrAmtTot, 2) > 0) {
                $trial["Opening Balance Difference"]['DrAmts'] = round($CrAmtTot - $DrAmtTot, 2);
                $trial["Opening Balance Difference"]['CrAmts'] = '';
                $trial["Opening Balance Difference"]['GroupName'] = 'Difference In Opening Balance';
                array_push($rettrial, $trial["Opening Balance Difference"]);
            }
        }
        //print_r($rettrial);
        
        $totalCount = sizeof($rettrial);
        $data = array("totalCount" => $totalCount, "data" => $rettrial);
        echo json_encode($data);


        break;

    case 'getProfitLoss':

        $data = $_POST;
        setlocale(LC_MONETARY, 'en_IN');
        //$amount = money_format('%!i', $amount);
        //setlocale(LC_ALL, ''); // Locale will be different on each system.

        $Basic_Nature = $db->getMultipleData("SELECT * from " . FINASCOP_DB . "finascop_accounts_natureofgroups where BasicID in (3,4) order BY NatGroupId Asc", true);

        if (!empty($data['branch'])) {
            $accled_Branch_con = " AND finascop_accounts_ledger.accled_BranchId = {$data['branch']} ";
            $OpenBal_Br_con = " AND OpenBal_BrID = {$data['branch']}  ";
        } else {
            switch ($_SESSION['admin']->finascop_typId) {
                case 1:
                    $qry = "SELECT group_concat(br_ID)  from " . FINASCOP_DB . "finascop_branch"
                            . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$data['company']}) ";
                    break;
                case 2:
                    $qry = "SELECT group_concat(br_ID) FROM " . FINASCOP_DB . "finascop_branch"
                            . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$data['company']}) ";
                    break;
                case 3:
                    $qry = "SELECT group_concat(br_ID) from " . FINASCOP_DB . "finascop_branch"
                            . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$data['company']}) AND "
                            . " br_ID IN (SELECT br_Id from " . FINASCOP_DB . "finascop_user_activebranches WHERE UserId = {$_SESSION['admin']->Finascop_UserId})";
                    break;
                case 4:
                    $qry = "SELECT group_concat(br_ID) from " . FINASCOP_DB . "finascop_branch_company  "
                            . " WHERE  comp_id= {$data['company']} and finascop_branch_company.br_Id IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_user_auditingbranches` WHERE   UserId = {$_SESSION['admin']->Finascop_UserId} ) ";
                    break;
                default:
                    $qry = "SELECT group_concat(br_ID) from " . FINASCOP_DB . "finascop_branch"
                            . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$data['company']}) ";
            }
            $active_branch = $db->getItemFromDB($qry);
            $accled_Branch_con = " AND finascop_accounts_ledger.accled_BranchId  in ({$active_branch})";
            $OpenBal_Br_con = " AND OpenBal_BrID in ({$active_branch}) ";
        }


        if (!empty($Basic_Nature)) {

            $bn_cr_total = 0;
            $bn_dr_total = 0;

            foreach ($Basic_Nature as $idx => $val) {

                $Basic_Nature_node[$idx]['id'] = 'BA_' . $val['NatGroupId'];
                $Basic_Nature_node[$idx]['NatGroupName'] = $val['NatGroupName'];
                $Basic_Nature_node[$idx]['draggable'] = false;
                $Basic_Nature_node[$idx]['children'] = '';
                $Basic_Nature_node[$idx]['cls'] = 'finascop_nature_group';

                $NatureOfGrpQry = "SELECT finascop_accounts_groups.GroupName AS GrpName
					, SUM(UnionReturn.DrAmts)  AS DrAmts
					, SUM(UnionReturn.CrAmts) AS CrAmts
					, IF (SUM(UnionReturn.DrAmts)-SUM(UnionReturn.CrAmts) < 0,1,0) AS 'IsCreditor'
					, finascop_accounts_groups.Group_ID AS GrpID 				
					FROM (SELECT SUM(IF(actr_IsDebtor=1,finascop_accounts_transaction.actr_amount,0))  AS DrAmts ,
					SUM(IF(actr_IsDebtor=0,finascop_accounts_transaction.actr_amount,0))  AS CrAmts,
					finascop_accounts_groups.Group_ID AS LEDID 
					FROM (" . FINASCOP_DB . "finascop_accounts_ledger INNER join " . FINASCOP_DB . "finascop_accounts_transaction 
					ON finascop_accounts_ledger.accled_Ledger_Id = finascop_accounts_transaction.ledg_Id			 
					INNER join " . FINASCOP_DB . "finascop_accounts_groups ON finascop_accounts_groups.Group_ID = finascop_accounts_ledger.Group_ID)  
					WHERE finascop_accounts_transaction.actr_IsApproved = 1 
					AND NatGroupID = {$val['NatGroupId']}
					AND comp_Id={$data['company']} {$accled_Branch_con}
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
					WHERE NatGroupID = {$val['NatGroupId']} AND OpenBal_CompID = {$data['company']} {$OpenBal_Br_con}
					GROUP BY openBal_Led_ID	) AS UnionReturn 
					INNER join " . FINASCOP_DB . "finascop_accounts_groups ON finascop_accounts_groups.Group_ID = UnionReturn.LEDID		 
					GROUP BY finascop_accounts_groups.Group_ID, finascop_accounts_groups.GroupName";

                $NatureOfGroupNames = $db->getMultipleData($NatureOfGrpQry, true);

                if (!empty($NatureOfGroupNames)) {
                    $Basic_Nature_node[$idx]['leaf'] = false;
                    foreach ($NatureOfGroupNames as $idp => $NatureOfGroupName) {

                        $nog_cr_total = 0;
                        $nog_dr_total = 0;

                        $NatGroupName_node[$idp]['id'] = 'NA_' . $NatureOfGroupName['GrpID'];
                        $NatGroupName_node[$idp]['NatGroupName'] = $NatureOfGroupName['GrpName'];
                        $NatGroupName_node[$idp]['draggable'] = false;
                        $NatGroupName_node[$idp]['children'] = '';
                        $NatGroupName_node[$idp]['cls'] = 'finascop_group';
                        //$NatGroupName_node[$idp]['nt_amt'] = $nt_amt;
                        // $NatGroupName_node[$idp]['DrAmts'] = $value['DrAmts'];
                        // $NatGroupName_node[$idp]['CrAmts'] = $value['CrAmts'];
//first split 			



                        $GrpNameqry = "SELECT finascop_accounts_ledger.ledgertypename AS 'Ledger'
					    , SUM(UnionReturn.DrAmts)  AS DrAmts
					    , SUM(UnionReturn.CrAmts) AS CrAmts                                  
					    ,  IF (SUM(UnionReturn.DrAmts)-SUM(UnionReturn.CrAmts) < 0,1,0) AS 'IsCreditor'
					    , finascop_accounts_ledger.ledgertypeid AS 'LID' 
					    FROM (
					    SELECT 
					    IF(actr_IsDebtor=1,finascop_accounts_ledger.accled_Debits,0)  AS DrAmts ,
					    IF(actr_IsDebtor=0,finascop_accounts_ledger.accled_Credits,0)  AS CrAmts ,
					    finascop_accounts_ledger.accled_Ledger_Id 
					    FROM (" . FINASCOP_DB . "finascop_accounts_ledger INNER join " . FINASCOP_DB . "finascop_accounts_transaction 
					    ON finascop_accounts_ledger.accled_Ledger_Id = finascop_accounts_transaction.ledg_Id) 
					    WHERE 
					    finascop_accounts_transaction.actr_IsApproved = 1 
					    AND  Group_ID = {$NatureOfGroupName['GrpID']}
					    AND comp_Id = {$data['company']}
					    GROUP BY finascop_accounts_ledger.ledgertypeid 
					    UNION ALL 
					    SELECT 
					    IF(OpenBal_IsDebtor=1,LED.accled_Debits,0)  AS DrAmts ,
					    IF(OpenBal_IsDebtor=0,LED.accled_Credits,0)  AS CrAmts
					    ,LED.accled_Ledger_Id 
					    from " . FINASCOP_DB . "finascop_accounts_openingbalance OP
					    INNER join " . FINASCOP_DB . "finascop_accounts_ledger LED ON LED.accled_Ledger_Id=OP.openBal_Led_ID			
					    WHERE Group_ID = {$NatureOfGroupName['GrpID']}  AND  OpenBal_CompID = {$data['company']}
					    GROUP BY accled_Ledger_Id) AS UnionReturn 
					    INNER join " . FINASCOP_DB . "finascop_accounts_ledger ON UnionReturn.accled_Ledger_Id = finascop_accounts_ledger.accled_Ledger_Id 
					    GROUP BY finascop_accounts_ledger.ledgertypeid
					    ORDER BY finascop_accounts_ledger.ledgertypeid";
                        //print_r($GrpNameqry);


                        $GroupNames = $db->getMultipleData($GrpNameqry, true);


                        if (!empty($GroupNames)) {
                            $NatGroupName_node[$idp]['leaf'] = false;

                            foreach ($GroupNames as $idl => $GroupName) {

                                $gr_cr_total = $gr_cr_total + $GroupName['CrAmts'];
                                $gr_dr_total = $gr_dr_total + $GroupName['DrAmts'];

                                $LedgerName_node[$idl]['id'] = 'GR_' . $GroupName['LID'];
                                $LedgerName_node[$idl]['NatGroupName'] = $GroupName['Ledger'];
                                $LedgerName_node[$idl]['draggable'] = true;
                                $LedgerName_node[$idl]['children'] = '';
                                $LedgerName_node[$idl]['cls'] = 'finascop_ledger_name';


                                //last node
                                $BranchLegerQry = "SELECT finascop_accounts_ledger.accled_LedgerName AS 'BrLedger'
							, SUM(UnionReturn.DrAmts)  AS DrAmts
							, SUM(UnionReturn.CrAmts) AS CrAmts                                  
							,  IF (SUM(UnionReturn.DrAmts)-SUM(UnionReturn.CrAmts) < 0,1,0) AS 'IsCreditor'
							, finascop_accounts_ledger.accled_Ledger_Id AS 'BrLID' 
							FROM (SELECT SUM(IF(actr_IsDebtor=1,finascop_accounts_transaction.actr_amount,0))  AS DrAmts ,
							SUM(IF(actr_IsDebtor=0,finascop_accounts_transaction.actr_amount,0))  AS CrAmts ,
							finascop_accounts_ledger.accled_Ledger_Id 
							FROM (" . FINASCOP_DB . "finascop_accounts_ledger INNER join " . FINASCOP_DB . "finascop_accounts_transaction 
							ON finascop_accounts_ledger.accled_Ledger_Id = finascop_accounts_transaction.ledg_Id) 
							WHERE 
							finascop_accounts_transaction.actr_IsApproved = 1 
							AND  ledgertypeid = {$GroupName['LID']}
							{$accled_Branch_con}
							GROUP BY finascop_accounts_ledger.accled_Ledger_Id 
							UNION ALL 
							SELECT 
							SUM(IF(OpenBal_IsDebtor=1,OpenBal_Amt,0))  AS DrAmts ,
							SUM(IF(OpenBal_IsDebtor=0,OpenBal_Amt,0))  AS CrAmts
							,LED.accled_Ledger_Id 
							from " . FINASCOP_DB . "finascop_accounts_openingbalance OP
							INNER join " . FINASCOP_DB . "finascop_accounts_ledger LED ON LED.accled_Ledger_Id=OP.openBal_Led_ID			
							WHERE ledgertypeid = {$GroupName['LID']}  AND  OpenBal_CompID = {$data['company']} {$OpenBal_Br_con} 
							GROUP BY accled_Ledger_Id) AS UnionReturn 
							INNER join " . FINASCOP_DB . "finascop_accounts_ledger ON UnionReturn.accled_Ledger_Id = finascop_accounts_ledger.accled_Ledger_Id 
							GROUP BY finascop_accounts_ledger.accled_LedgerName, finascop_accounts_ledger.accled_Ledger_Id  
							ORDER BY finascop_accounts_ledger.accled_LedgerName";
                                //print_r($BranchLegerQry);


                                $LedgerNames = $db->getMultipleData($BranchLegerQry, true);


                                if (!empty($LedgerNames)) {
                                    //print_r($LedgerNames);
                                    $LedgerName_node[$idl]['leaf'] = false;
                                    $BranchLedgerName_node = array();
                                    foreach ($LedgerNames as $idbrl => $LedgerName) {
                                        //print_r($LedgerName);

                                        $brl_cr_total = $brl_cr_total + $LedgerName['CrAmts'];
                                        $brl_dr_total = $brl_dr_total + $LedgerName['DrAmts'];

                                        if ($val['BasicID'] == 3) {
                                            $diff = $LedgerName['CrAmts'] - $LedgerName['DrAmts'];
                                        } else {
                                            $diff = $LedgerName['DrAmts'] - $LedgerName['CrAmts'];
                                        }
                                        $is_contrary = ($diff < 0) ? 1 : 0;
                                        $brgmt = ($is_contrary == 0 || $diff == 0) ? ' ' . money_format('%!i', abs($diff)) . ' ' : '(' . money_format('%!i', abs($diff)) . ')';

                                        $BranchLedgerName_node[$idbrl]['id'] = 'GR_' . $LedgerName['BrLID'];
                                        $BranchLedgerName_node[$idbrl]['NatGroupName'] = $LedgerName['BrLedger'];
                                        $BranchLedgerName_node[$idbrl]['draggable'] = true;
                                        $BranchLedgerName_node[$idbrl]['children'] = '';
                                        $BranchLedgerName_node[$idbrl]['cls'] = 'finascop_ledger_name';
                                        $BranchLedgerName_node[$idbrl]['grp_amt'] = $brgmt;
                                        $BranchLedgerName_node[$idbrl]['leaf'] = true;
                                        $BranchLedgerName_node[$idbrl]['dr'] = money_format('%!i', $LedgerName['DrAmts']);
                                        $BranchLedgerName_node[$idbrl]['cr'] = money_format('%!i', $LedgerName['CrAmts']);
                                    }

                                    //last node ends here				
                                    $nog_cr_total = $nog_cr_total + $brl_cr_total;
                                    $nog_dr_total = $nog_dr_total + $brl_dr_total;

                                    if ($val['BasicID'] == 3) {
                                        $dif = $brl_cr_total - $brl_dr_total;
                                    } else {
                                        $dif = $brl_dr_total - $brl_cr_total;
                                    }
                                    $is_contrary = ($dif < 0) ? 1 : 0;
                                    $nt_amt = ($is_contrary == 0 || $dif == 0) ? ' ' . money_format('%!i', abs($dif)) . ' ' : '(' . money_format('%!i', abs($dif)) . ')';


                                    //$LedgerName_node[$idl]['grp_amt'] = $gmt;
                                    $LedgerName_node[$idl]['leaf'] = false;
                                    $LedgerName_node[$idl]['dr'] = money_format('%!i', $GroupName['DrAmts']);
                                    $LedgerName_node[$idl]['cr'] = money_format('%!i', $GroupName['CrAmts']);
                                    $LedgerName_node[$idl]['nt_amt'] = $nt_amt;
                                    $LedgerName_node[$idl]['children'] = $BranchLedgerName_node;
                                    $BranchLedgerName_node = array();
                                    $brl_cr_total = 0;
                                    $brl_dr_total = 0;
                                } else {
                                    $LedgerName_node[$idl]['leaf'] = true;
                                    $LedgerName_node[$idl]['children'] = array();
                                }
                            }

                            //first split ends here	
                            $bn_cr_total = $bn_cr_total + $nog_cr_total;
                            $bn_dr_total = $bn_dr_total + $nog_dr_total;

                            if ($val['BasicID'] == 3) {
                                $dif = $nog_cr_total - $nog_dr_total;
                            } else {
                                $dif = $nog_dr_total - $nog_cr_total;
                            }
                            $is_contrary = ($dif < 0) ? 1 : 0;
                            $nt_amt = ($is_contrary == 0 || $dif == 0) ? ' ' . money_format('%!i', abs($dif)) . ' ' : '(' . money_format('%!i', abs($dif)) . ')';


                            $NatGroupName_node[$idp]['cr_total'] = money_format('%!i', $nog_cr_total);
                            $NatGroupName_node[$idp]['dr_total'] = money_format('%!i', $nog_dr_total);
                            $NatGroupName_node[$idp]['nt_amt'] = $nt_amt;
                            $NatGroupName_node[$idp]['children'] = $LedgerName_node;
                            $LedgerName_node = array();
                            $nog_cr_total = 0;
                            $nog_dr_total = 0;
                        } else {
                            $NatGroupName_node[$idp]['leaf'] = true;
                            $NatGroupName_node[$idp]['children'] = array();
                        }
                    }

                    if ($val['BasicID'] == 3) {
                        $d = $bn_cr_total - $bn_dr_total;
                    } else {
                        $d = $bn_dr_total - $bn_cr_total;
                    }
                    $is_contrary = ($d < 0) ? 1 : 0;
                    $final_amt = ($is_contrary == 0 || $d == 0) ? ' ' . money_format('%!i', abs($d)) . ' ' : '(' . money_format('%!i', abs($d)) . ')';
                    $Basic_Nature_node[$idx]['final_amt'] = $final_amt;
                    $Basic_Nature_node[$idx]['org'] = abs($d);
                    $Basic_Nature_node[$idx]['children'] = $NatGroupName_node;
                    $NatGroupName_node = array();
                    $bn_cr_total = 0;
                    $bn_dr_total = 0;
                } else {
                    $Basic_Nature_node[$idx]['leaf'] = true;
                    $Basic_Nature_node[$idx]['children'] = array();
                }
            }



            /* additional node */

            $profit = $Basic_Nature_node[0]['org'];
            $exp = $Basic_Nature_node[1]['org'];
            if ($profit != $exp && ($profit != 0 || $exp != 0)) {
                if ($profit > $exp) {
                    $name = "Profit Over Loss";
                    $ie = $profit - $exp;
                } else {
                    $name = "Loss over Profit";
                    $ie = $exp - $profit;
                }

                $Basic_Nature_node[2]['id'] = '';
                $Basic_Nature_node[2]['NatGroupName'] = strtoupper($name);
                $Basic_Nature_node[2]['draggable'] = false;
                $Basic_Nature_node[2]['cls'] = 'finascop_nature_group';
                $Basic_Nature_node[2]['leaf'] = true;
                $Basic_Nature_node[2]['children'] = array();
                $Basic_Nature_node[2]['final_amt'] = money_format('%!i', $ie);
            }
        }

        echo json_encode($Basic_Nature_node);

        break;

    case 'getBalancesheet':

        $data = $_POST;
        $b_grps = array();

        $b_grps[0]['BasicID'] = 2;
        $b_grps[0]['BasicGroupName'] = 'Liabilities';

        $b_grps[1]['BasicID'] = 1;
        $b_grps[1]['BasicGroupName'] = 'Assets';


        $r_cr_total = 0;
        $r_dr_total = 0;

        $addedPNL = false;

        if (!empty($data['branch'])) {
            $ledger_Branch_con = " AND finascop_accounts_ledger.accled_BranchId = {$data['branch']} ";
            $OpenBal_Br_con = " AND OpenBal_BrID = {$data['branch']}  ";
        } else {
            switch ($_SESSION['admin']->finascop_typId) {
                case 1:
                    $qry = "SELECT group_concat(br_ID)  from " . FINASCOP_DB . "finascop_branch"
                            . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$data['company']}) ";
                    break;
                case 2:
                    $qry = "SELECT group_concat(br_ID) from " . FINASCOP_DB . "finascop_branch"
                            . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$data['company']}) ";
                    break;
                case 3:
                    $qry = "SELECT group_concat(br_ID) from " . FINASCOP_DB . "finascop_branch"
                            . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$data['company']}) AND "
                            . " br_ID IN (SELECT br_Id from " . FINASCOP_DB . "finascop_user_activebranches WHERE UserId = {$_SESSION['admin']->Finascop_UserId})";
                    break;
                case 4:
                    $qry = "SELECT group_concat(br_ID) from " . FINASCOP_DB . "finascop_branch_company  "
                            . " WHERE  comp_id= {$data['company']} and finascop_branch_company.br_Id IN (SELECT DSTINCT br_Id from " . FINASCOP_DB . "`finascop_user_auditingbranches` WHERE   UserId = {$_SESSION['admin']->Finascop_UserId} ) ";
                    break;
                default:
                    $qry = "SELECT group_concat(br_ID) from " . FINASCOP_DB . "finascop_branch"
                            . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$data['company']}) ";
            }
            $active_branch = $db->getItemFromDB($qry);
            $ledger_Branch_con = " AND finascop_accounts_ledger.accled_BranchId  in ({$active_branch})";
            $OpenBal_Br_con = " AND OpenBal_BrID in ({$active_branch}) ";
        }



        foreach ($b_grps as $index => $rec) {/* light green */

            $root_node[$index]['id'] = 'BA_' . $rec['BasicID'];
            $root_node[$index]['NatGroupName'] = $rec['BasicGroupName'];
            $root_node[$index]['draggable'] = false;
            $root_node[$index]['children'] = array();
            $root_node[$index]['cls'] = 'finascop_basic_nature';
            $rootamount = 0;
            $Basic_Nature = $db->getMultipleData("SELECT * from " . FINASCOP_DB . "finascop_accounts_natureofgroups "
                    . "where BasicID = {$rec['BasicID']} order BY NatGroupId Asc", true);

            if (!empty($Basic_Nature)) {



                foreach ($Basic_Nature as $idx => $val) { /* blue */
                    $b_cr_total = 0;
                    $b_dr_total = 0;
                    $Basic_Nature_node[$idx]['id'] = 'BN_' . $val['NatGroupId'];
                    $Basic_Nature_node[$idx]['NatGroupName'] = $val['NatGroupName'];
                    $Basic_Nature_node[$idx]['draggable'] = false;
                    $Basic_Nature_node[$idx]['children'] = array();
                    $Basic_Nature_node[$idx]['cls'] = 'finascop_nature_group';

                    $qry = "SELECT finascop_accounts_groups.GroupName AS GrpName
					, SUM(UnionReturn.DrAmts)  AS DrAmts
					, SUM(UnionReturn.CrAmts) AS CrAmts
					, IF (SUM(UnionReturn.DrAmts) - SUM(UnionReturn.CrAmts) < 0,1,0) AS 'IsCreditor'	
					, finascop_accounts_groups.Group_ID AS GrpId
					FROM (SELECT 
					SUM(finascop_accounts_ledger.accled_Debits)  AS DrAmts ,
					SUM(finascop_accounts_ledger.accled_Credits)  AS CrAmts ,  
					finascop_accounts_groups.Group_ID AS GrpID
					FROM (finascop_accounts_ledger 
					INNER JOIN finascop_accounts_groups ON finascop_accounts_groups.Group_ID =finascop_accounts_ledger.Group_ID) 
					WHERE finascop_accounts_ledger.accled_CompId = {$data['company']} {$ledger_Branch_con}
					AND finascop_accounts_groups.NatGroupID = {$val['NatGroupId']}
					GROUP BY finascop_accounts_groups.Group_ID
					UNION ALL
					SELECT 
					SUM(IF(OpenBal_IsDebtor=1,OpenBal_Amt,0))  AS DrAmts ,
					SUM(IF(OpenBal_IsDebtor=0,OpenBal_Amt,0))  AS CrAmts
					,  finascop_accounts_groups.Group_ID AS GrpID
					from " . FINASCOP_DB . "finascop_accounts_openingbalance
					INNER join " . FINASCOP_DB . "finascop_accounts_ledger ON finascop_accounts_openingbalance.openBal_Led_ID = finascop_accounts_ledger.accled_Ledger_Id	
					INNER join " . FINASCOP_DB . "finascop_accounts_groups ON finascop_accounts_groups.Group_ID =finascop_accounts_ledger.Group_ID
					WHERE 
					OpenBal_CompID = {$data['company']} {$OpenBal_Br_con}					
					AND finascop_accounts_groups.NatGroupID = {$val['NatGroupId']}
					GROUP BY finascop_accounts_groups.Group_ID)
					AS UnionReturn INNER join " . FINASCOP_DB . "finascop_accounts_groups 
					ON finascop_accounts_groups.Group_ID= UnionReturn.GrpID
					GROUP BY finascop_accounts_groups.Group_ID
					HAVING (SUM(UnionReturn.DrAmts) - SUM(UnionReturn.CrAmts) <> 0) ";

                    //echo $qry. '\n';


                    $NatGroupName = $db->getMultipleData($qry, true);
                    $PNLval = getIncANDExpTotal($ledger_Branch_con, $OpenBal_Br_con, $data['company']);

                    if ($val['NatGroupId'] == '8') {
                        if (empty($NatGroupName)) {
                            if ($PNLval['DrAmts'] - $PNLval['CrAmts'] != 0) {
                                $grpname = $db->getItemFromDB("Select GroupName from " . FINASCOP_DB . "finascop_accounts_groups where Group_ID =9", true);
                                $NatGroupName = array();
                                array_push($NatGroupName, array('GrpId' => '9', 'GrpName' => $grpname, 'IsManual' => true, 'CrAmts' => 0, 'DrAmts' => 0));
                            }
                        }
                    }

                    if (!empty($NatGroupName)) {
                        $Basic_Nature_node[$idx]['leaf'] = false;

                        foreach ($NatGroupName as $idp => $value) { /* red */

                            $NatGroupName_node[$idp]['id'] = 'NA_' . $value['GrpId'];
                            $NatGroupName_node[$idp]['NatGroupName'] = $value['GrpName'];
                            $NatGroupName_node[$idp]['draggable'] = false;
                            $NatGroupName_node[$idp]['children'] = array();
                            $NatGroupName_node[$idp]['cls'] = 'finascop_group';

                            $b_cr_total = $b_cr_total + $value['CrAmts'];
                            $b_dr_total = $b_dr_total + $value['DrAmts'];

                            if ($rec['BasicID'] == 1) {
                                $nt_diff = $value['DrAmts'] - $value['CrAmts'];
                            } else {
                                $nt_diff = $value['CrAmts'] - $value['DrAmts'];
                            }

                            $is_contrary = ($nt_diff < 0) ? 1 : 0;
                            $nt_gmt = ($is_contrary == 0 || $nt_diff == 0) ? abs($nt_diff) : '(' . abs($nt_diff) . ')';

                            $NatGroupName_node[$idp]['nt_amt'] = $nt_gmt;

                            $ledgerQuery = "SELECT falt.ledgertypeid AS LedgerTypeID,falt.ledgertypename AS LedgerTypeName
							, SUM(UnionReturn.DrAmts)  AS DrAmts
							, SUM(UnionReturn.CrAmts) AS CrAmts
							, IF (UnionReturn.DrAmts -UnionReturn.CrAmts < 0,1,0) AS 'IsCreditor'	
							FROM 
							(
								SELECT 
									SUM(finascop_accounts_ledger.accled_Debits)  AS DrAmts ,
									SUM(finascop_accounts_ledger.accled_Credits)  AS CrAmts , 
									finascop_accounts_ledger.ledgertypeid AS TypeId
									FROM (finascop_accounts_ledger) 
									WHERE finascop_accounts_ledger.accled_CompId = {$data['company']} $ledger_Branch_con
									AND finascop_accounts_ledger.Group_ID = {$value['GrpId']} 
									GROUP BY finascop_accounts_ledger.ledgertypeid
								UNION ALL
								SELECT 
									SUM(IF(OpenBal_IsDebtor=1,OpenBal_Amt,0)) AS DrAmts , 
									SUM(IF(OpenBal_IsDebtor=0,OpenBal_Amt,0)) AS CrAmts ,
									finascop_accounts_ledgertype.ledgertypeid AS TypeId 
									FROM " . FINASCOP_DB . "finascop_accounts_openingbalance faob
									INNER JOIN " . FINASCOP_DB . "finascop_accounts_ledger
									ON faob.openBal_Led_ID = finascop_accounts_ledger.accled_Ledger_Id 
									INNER JOIN " . FINASCOP_DB . "finascop_accounts_ledgertype
									ON finascop_accounts_ledger.ledgertypeid = finascop_accounts_ledgertype.ledgertypeid
									WHERE OpenBal_CompID = {$data['company']}
									AND finascop_accounts_ledgertype.Group_ID = {$value['GrpId']} $OpenBal_Br_con
									GROUP BY finascop_accounts_ledger.ledgertypeid)
							AS UnionReturn INNER JOIN " . FINASCOP_DB . "finascop_accounts_ledgertype falt
							ON falt.ledgertypeid= UnionReturn.TypeId 
							GROUP BY falt.ledgertypeid
							HAVING (SUM(UnionReturn.DrAmts) - SUM(UnionReturn.CrAmts) <> 0) ";

                            //echo $ledgerQuery ."\n";

                            $LedgerType = $db->getMultipleData($ledgerQuery, true);


                            if (!empty($LedgerType)) {
                                $NatGroupName_node[$idp]['leaf'] = false;
                                $ledcntrid = 0;
                                $prevLedgerName = '';
                                foreach ($LedgerType as $ledcntrid => $values) /* green */ {
                                    $currentLedgerName = $values['LedgerTypeName'];


                                    if ($values['IsManual'] != true) {

                                        $LedgerName_node[$ledcntrid]['id'] = 'LD_' . $values['LedgerTypeID'];
                                        $LedgerName_node[$ledcntrid]['NatGroupName'] = $values['LedgerTypeName'];
                                        $LedgerName_node[$ledcntrid]['draggable'] = true;
                                        $LedgerName_node[$ledcntrid]['cls'] = 'finascop_ledger_name';

                                        $LedgerName_node[$ledcntrid]['dr'] = $values['DrAmts'];
                                        $LedgerName_node[$ledcntrid]['cr'] = $values['CrAmts'];

                                        if ($LedgerName_node['IsCreditor'] == 0) {
                                            $ldg_diff = $values['DrAmts'] - $values['CrAmts'];
                                        } else {
                                            $ldg_diff = $values['CrAmts'] - $values['DrAmts'];
                                        }
                                        $is_contrary = ($ldg_diff < 0) ? 1 : 0;
                                        $ldg_gmt = ($is_contrary == 0 || $ldg_diff == 0) ? abs($ldg_diff) : '(' . abs($ldg_diff) . ')';

                                        $LedgerName_node[$ledcntrid]['grp_amt'] = $ldg_gmt;

                                        $brLedgerQry = "SELECT finascop_accounts_ledger.accled_Ledger_Id as accled_Ledger_Id,
							finascop_accounts_ledger.accled_LedgerName AS LedgerName
							, SUM(UnionReturn.DrAmts)  AS DrAmts
							, SUM(UnionReturn.CrAmts) AS CrAmts
							, IF ((SUM(UnionReturn.DrAmts) - SUM(UnionReturn.CrAmts)) < 0,1,0) AS 'IsCreditor'
							FROM (SELECT 
							finascop_accounts_ledger.accled_Debits  AS DrAmts ,
							finascop_accounts_ledger.accled_Credits  AS CrAmts , 
							finascop_accounts_ledger.accled_Ledger_Id AS LedId
							FROM finascop_accounts_ledger
							WHERE finascop_accounts_ledger.accled_CompId = {$data['company']} {$ledger_Branch_con}
							AND finascop_accounts_ledger.ledgertypeid = {$values['LedgerTypeID']}
							GROUP BY finascop_accounts_ledger.accled_Ledger_Id
							UNION ALL
							SELECT 
							SUM(IF(OpenBal_IsDebtor=1,OpenBal_Amt,0))  AS DrAmts ,
							SUM(IF(OpenBal_IsDebtor=0,OpenBal_Amt,0))  AS CrAmts
							,  finascop_accounts_ledger.accled_Ledger_Id AS LedId
							from " . FINASCOP_DB . "finascop_accounts_openingbalance
							INNER join " . FINASCOP_DB . "finascop_accounts_ledger 
							ON finascop_accounts_openingbalance.openBal_Led_ID = finascop_accounts_ledger.accled_Ledger_Id		
							WHERE 
							OpenBal_CompID = {$data['company']} {$OpenBal_Br_con}
							AND finascop_accounts_ledger.ledgertypeid = {$values['LedgerTypeID']}
							GROUP BY finascop_accounts_ledger.accled_Ledger_Id)
							AS UnionReturn INNER join " . FINASCOP_DB . "finascop_accounts_ledger
							ON finascop_accounts_ledger.accled_Ledger_Id= UnionReturn.LedId 
							GROUP BY finascop_accounts_ledger.accled_Ledger_Id
							HAVING (SUM(UnionReturn.DrAmts) - SUM(UnionReturn.CrAmts) <> 0) ";

                                        //print_r($brLedgerQry . '\n\n');
//					exit;

                                        $BranchLedgerNames = $db->getMultipleData($brLedgerQry, true);

                                        if (!empty($BranchLedgerNames)) {
                                            $LedgerName_node[$ledcntrid]['leaf'] = false;
                                            $brledcntrid = 0;
                                            $prevBrLedgerName = '';
                                            foreach ($BranchLedgerNames as $brledcntrid => $brvalues) /* green */ {
                                                $currentBrLedgerName = $brvalues['LedgerName'];


                                                if ($brvalues['IsManual'] != true) {

                                                    $BranchLedgerName_node[$brledcntrid]['id'] = 'GR_' . $brvalues['accled_Ledger_Id'];
                                                    $BranchLedgerName_node[$brledcntrid]['NatGroupName'] = $brvalues['LedgerName'];
                                                    $BranchLedgerName_node[$brledcntrid]['draggable'] = true;
                                                    $BranchLedgerName_node[$brledcntrid]['cls'] = 'finascop_ledger_name';

                                                    $BranchLedgerName_node[$brledcntrid]['dr'] = $brvalues['DrAmts'];
                                                    $BranchLedgerName_node[$brledcntrid]['cr'] = $brvalues['CrAmts'];

                                                    if ($BranchLedgerName_node['IsCreditor'] == 0) {
                                                        $brldg_diff = $brvalues['DrAmts'] - $brvalues['CrAmts'];
                                                    } else {
                                                        $brldg_diff = $brvalues['CrAmts'] - $brvalues['DrAmts'];
                                                    }
                                                    $is_contrary = ($brldg_diff < 0) ? 1 : 0;
                                                    $brldg_gmt = ($is_contrary == 0 || $brldg_diff == 0) ? abs($brldg_diff) : '(' . abs($brldg_diff) . ')';

                                                    $BranchLedgerName_node[$brledcntrid]['grp_amt'] = $brldg_gmt;
                                                    $BranchLedgerName_node[$brledcntrid]['leaf'] = true;
                                                    $BranchLedgerName_node[$brledcntrid]['children'] = array();
                                                    $brledcntrid++;
                                                }
                                            }

//					    if ($value['GrpId'] == 9 && $addedPNL == false) {
//						$addedPNL = true;
//
//						if (($PNLval['DrAmts'] - $PNLval['CrAmts']) != 0) {
//
//						    $BranchLedgerName_node[$brledcntrid]['id'] = 'GR_PNL';
//						    $BranchLedgerName_node[$brledcntrid]['NatGroupName'] = 'Transfer from Profit & Loss A/C';
//						    $BranchLedgerName_node[$brledcntrid]['draggable'] = true;
//						    $BranchLedgerName_node[$brledcntrid]['children'] = array();
//						    $BranchLedgerName_node[$brledcntrid]['cls'] = 'finascop_rose';
//
//						    $BranchLedgerName_node[$brledcntrid]['leaf'] = true;
//						    $BranchLedgerName_node[$brledcntrid]['dr'] = $PNLval['DrAmts'];
//						    $BranchLedgerName_node[$brledcntrid]['cr'] = $PNLval['CrAmts'];
//
//						    $cur_diff = $PNLval['CrAmts'] - $PNLval['DrAmts'];
//
//						    $b_cr_total = $b_cr_total + $PNLval['CrAmts'];
//						    $b_dr_total = $b_dr_total + $PNLval['DrAmts'];
//
//
//						    $nt_diff = $nt_diff + $cur_diff;
//
//
//						    $nt_is_credit = ($nt_diff < 0) ? 1 : 0;
//						    $nt_gmt = ($nt_is_credit == 0 || $nt_diff == 0) ? abs($nt_diff) : '(' . abs($nt_diff) . ')';
//
//						    $LedgerName_node[$ledcntrid]['nt_amt'] = $nt_gmt;
//
//						    $cur_is_credit = ($cur_diff < 0) ? 1 : 0;
//						    $cur_gmt = ($cur_is_credit == 0 || $cur_diff == 0) ? abs($cur_diff) : '(' . abs($cur_diff) . ')';
//
//						    $BranchLedgerName_node[$brledcntrid]['grp_amt'] = $cur_gmt;
//						}
//					    }

                                            $LedgerName_node[$ledcntrid]['children'] = $BranchLedgerName_node;
                                            $BranchLedgerName_node = array();
                                        } else {
                                            $LedgerName_node[$ledcntrid]['leaf'] = true;
                                            $LedgerName_node[$ledcntrid]['children'] = array();
                                        }
                                        $ledcntrid++;
                                    }
                                }

                                $NatGroupName_node[$idp]['children'] = $LedgerName_node;
                                $LedgerName_node = array();
                            } else {
                                $NatGroupName_node[$idp]['leaf'] = true;
                                $NatGroupName_node[$idp]['children'] = array();
                            }
                        }



                        if ($rec['BasicID'] == 1) {
                            $b_diff = $b_dr_total - $b_cr_total;
                        } else {
                            $b_diff = $b_cr_total - $b_dr_total;
                        }
                        $is_contrary = ($b_diff < 0) ? 1 : 0;
                        $b_amt = ($is_contrary == 0 || $b_diff == 0) ? abs($b_diff) : '(' . abs($b_diff) . ')';

                        $Basic_Nature_node[$idx]['final_amt'] = $b_amt;
                        $rootamount = $rootamount + $b_diff;
                        $Basic_Nature_node[$idx]['children'] = $NatGroupName_node;
                        $NatGroupName_node = array();
                    } else {
                        $Basic_Nature_node[$idx]['leaf'] = true;
                        $Basic_Nature_node[$idx]['children'] = array();
                    }
                }
                $root_node[$index]['children'] = $Basic_Nature_node;
                $Basic_Nature_node = array();
            } else {
                $root_node[$index]['leaf'] = true;
                $root_node[$index]['children'] = array();
            }
            $root_node[$index]['final_amt'] = ( $rootamount > 0 || $rootamount == 0) ? abs($rootamount) : '(' . abs($rootamount) . ')';
        }



        /* additional node for showing opening balance */

        $OpeningBalLiabilility = $root_node[0]['final_amt'];
        $OpeningBalAsset = $root_node[1]['final_amt'];
        if ($OpeningBalLiabilility != $OpeningBalAsset && ($OpeningBalLiabilility != 0 || $OpeningBalAsset != 0)) {
            $name = "Difference In Opening Balance";
            if (round($OpeningBalLiabilility - $OpeningBalAsset, 2) > 0) {
                $openingBalance = $OpeningBalLiabilility - $OpeningBalAsset;
                $nextIndex = count($root_node[1]['children']);
                $Opening_Balance_node['id'] = 'BA_OP';
                $Opening_Balance_node['NatGroupName'] = strtoupper($name);
                $Opening_Balance_node['draggable'] = false;
                $Opening_Balance_node['cls'] = 'finascop_opening_balance_group';
                $Opening_Balance_node['leaf'] = true;
                $Opening_Balance_node['children'] = array();
                $Opening_Balance_node['final_amt'] = round($openingBalance, 2);
                $root_node[1]['children'][$nextIndex] = $Opening_Balance_node;
                $root_node[1]['final_amt'] = round($root_node[1]['final_amt'] + $openingBalance, 2);
            }
            if (round($OpeningBalAsset - $OpeningBalLiabilility, 2) > 0) {
                $openingBalance = $OpeningBalAsset - $OpeningBalLiabilility;
                $nextIndex = count($root_node[0]['children']);
                $Opening_Balance_node['id'] = 'BA_OP';
                $Opening_Balance_node['NatGroupName'] = strtoupper($name);
                $Opening_Balance_node['draggable'] = false;
                $Opening_Balance_node['cls'] = 'finascop_opening_balance_group';
                $Opening_Balance_node['leaf'] = true;
                $Opening_Balance_node['children'] = array();
                $Opening_Balance_node['final_amt'] = round($openingBalance, 2);
                $root_node[0]['children'][$nextIndex] = $Opening_Balance_node;
                $root_node[0]['final_amt'] = round($root_node[0]['final_amt'] + $openingBalance, 2);
            }
        }

        echo json_encode($root_node);


        break;
    case 'getledgerBalancerepexport':
       // require(THIS_MODULE_PATH . "/functions.php");
        $data = $_POST; //json_decode(stripslashes($_POST['e']), true);
        $win = $data['wisize'];
        $title = $data['activeTabtitle'];
        retalineExportToExcel($data, false, $win, $title);
        break;
    case 'gettrialBalancerepexport':
       // require(THIS_MODULE_PATH . "/functions.php");
        $data = $_POST; //json_decode(stripslashes($_POST['e']), true);
        $win = $data['wisize'];
        $title = $data['activeTabtitle'];
        retalineExportToExcel($data, false, $win, $title);//$_SESSION['trialBalreportqry'] = $qry;
        break;
}   																														


