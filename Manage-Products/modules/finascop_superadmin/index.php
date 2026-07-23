<?php

require_once(INCLUDE_PATH . "/finascop_accounts_Master.php");
require_once(INCLUDE_PATH . "/finascop_common_functions.php");
switch ($op) {
    case 'populateFromJson':
        $waqs_id = $_POST['waqs_id'];

        $data = $db->getFromDB("SELECT * FROM finascop_wallet_queue_settings WHERE waqs_id = '{$waqs_id}'", true);
        $waqs_Configuration = json_decode($data['waqs_Configuration'], true);

        $dr = $waqs_Configuration ['dr'];
        $cr = $waqs_Configuration ['cr'];

        $db->query('begin');
        $db->query("DELETE FROM finascop_wallet_queue_setter WHERE waqs_id = {$data['waqs_id']}");
        unset($data['waqs_Configuration']);

        foreach ($cr as $account => $val) {
            $data['waqt_drcr'] = 'cr';
            $data['waqt_account_name'] = $account;
            $data['waqt_key'] = $val['key'];
            $data['waqt_type'] = $val['type'];
            $status = $db->perform(FINASCOP_DB . "finascop_wallet_queue_setter", $data);
        }

        foreach ($dr as $account => $val) {
            $data['waqt_drcr'] = 'dr';
            $data['waqt_account_name'] = $account;
            $data['waqt_key'] = $val['key'];
            $data['waqt_type'] = $val['type'];
            $status = $db->perform(FINASCOP_DB . "finascop_wallet_queue_setter", $data);
        }

        $status = $db->query('commit');

        if ($status == 1) {
            echo '{"success":true ,"comments":' . $waqs_Configuration ['comments'] . '}';
        } else {
            echo '{"success":false}';
        }
        break;
    case 'saveNewWalletConfig':
        $data = $_POST;
        unset($data['apikey']);
        unset($data['tstamp']);
        if ($data['wst_multiBranch'] == 'true') {
            $data['waqs_IsCrossBranch'] = 1;
        } else {
            $data['waqs_IsCrossBranch'] = 0;
        }
        unset($data['wst_multiBranch']);
        $data['waqs_Configuration'] = json_encode(json_decode($data['waqs_Configuration']));
        $con = "waqs_id = {$data['waqs_id']}";
        $status = $db->perform(FINASCOP_DB . "finascop_wallet_queue_settings", $data, 'update', $con);

        if ($status == 1) {
            echo '{"success":true, "msg":"Wallet Queue Settings saved." }';
        } else {
            echo '{"success":false,"msg":"Wallet Queue Settings saving failed."}';
        }

        break;
    case 'removeWalletSettingsItem':
        $data = $_POST;

        $status = $db->query("DELETE FROM finascop_wallet_queue_setter WHERE waqt_id = {$data['waqt_id']}");

        if ($status == 1) {
            echo '{"success":true, "msg":"Wallet Settings deleted." }';
        } else {
            echo '{"success":false,"msg":"Wallet Settings deletion failed."}';
        }
        break;
    case 'createNewWalletQSettings':
        $data = $_POST;

        $con = "waqs_Name = '{$data['wtq_settingName']}'";
        $count = $db->getItemFromDB("SELECT COUNT(1) FROM finascop_wallet_queue_settings WHERE {$con}");
        if ($count > 0) {
            echo '{"success":false,"msg":"Wallet Settings Name already exists."}';
            exit(0);
        }

        $status = $db->query("INSERT INTO finascop_wallet_queue_settings (waqs_id,waqs_Name) VALUES ((SELECT MAX(B.waqs_id) + 1 FROM finascop_wallet_queue_settings B) , '{$data['wtq_settingName']}')");
        $waqs_id = $db->getLastInsertId();
        if ($status == 1) {
            echo '{"success":true, "waqs_id":' . $waqs_id . ',"msg":"Wallet Settings initiated." }';
        } else {
            echo '{"success":false,"msg":"Wallet Settings initiation failed."}';
        }
        break;
    case 'getLedgerDetails':
        $data = $_POST;
        if ($data['led_type'] == 1) {
            $qry = "SELECT ledgertypedefaultname as ledger_name, ledt_referenceID as waqt_key FROM finascop_accounts_ledgertype_default";
        }
        if ($data['led_type'] == 2) {
            $qry = "SELECT ledgertypename as ledger_name,falt_ReferenceID  as waqt_key FROM finascop_accounts_ledgertype WHERE isSystem = 1 AND ledgercompid = {$_SESSION['admin']->finascop_current_company_id}";
        }
        if ($data['led_type'] == 3) {
            echo '{"totalCount":"0","data":[]}';
            exit(0);
        }

        $rd = $db->getMultipleData($qry, true);

        if (!empty($rd)) {
            echo '{"totalCount":' . count($rd) . ',"data":' . json_encode($rd) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'addWalletSettingsItem':
        $data = $_POST;
        unset($data['apikey']);
        unset($data['tstamp']);
        if ($data['waqt_type'] != 3) {
            $con = "waqs_id = {$data['waqs_id']} AND waqt_key = '{$data['waqt_key']}' AND waqt_type = {$data['waqt_type']} AND waqt_account_name =  '{$data['waqt_account_name']}'";
        } else {

            $con = "waqs_id = {$data['waqs_id']} AND waqt_key = '{$data['waqt_key']}' AND waqt_type = {$data['waqt_type']} AND waqt_account_name =  '{$data['waqt_account_name']}'";
        }

        $count = $db->getItemFromDB("SELECT COUNT(1) FROM finascop_wallet_queue_setter WHERE {$con}");
        if ($count > 0) {
            $status = $db->perform(FINASCOP_DB . "finascop_wallet_queue_setter", $data, 'update', $con);
        } else {
            $status = $db->perform(FINASCOP_DB . "finascop_wallet_queue_setter", $data);
        }

        if ($status == 1) {
            echo '{"success":true}';
        } else {
            echo '{"success":false}';
        }
        break;
    case 'getWalletQSettings':

        $waqs_id = $_POST['waqs_id'];

        if (!empty($waqs_id)) {
            $rd = $db->getMultipleData("SELECT waqt_id, waqs_id,waqs_Name,if(waqt_drcr ='dr', 'Debit', 'Credit') as waqt_drcr,waqt_account_name,waqt_type,waqt_key,"
                    . " CASE "
                    . " WHEN waqt_type = 'ledgerdefaulttype' THEN (SELECT ledgertypedefaultname FROM finascop_accounts_ledgertype_default WHERE ledt_referenceID = waqt_key)"
                    . " WHEN waqt_type = 'ledgertype' THEN (SELECT ledgertypename FROM finascop_accounts_ledgertype WHERE falt_ReferenceID = waqt_key)"
                    . " END AS ledger_name "
                    . " FROM finascop_wallet_queue_setter WHERE waqs_id = {$waqs_id} ", true);
        }

        if (!empty($rd)) {
            echo '{"totalCount":' . count($rd) . ',"data":' . json_encode($rd) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'getWalletQSettingsJson':
        $waqs_id = $_POST['waqs_id'];
        $waqs_Configuration = $db->getItemFromDB("SELECT waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = '{$waqs_id}'");
        $waqs_Configuration = json_decode($waqs_Configuration, true);

        if (!empty($waqs_Configuration)) {
            echo '{"success":true,"data":' . json_encode($waqs_Configuration, JSON_PRETTY_PRINT) . '}';
        } else {
            echo '{"success":false,"data":{}}';
        }
        break;
    case 'getWalletQueueSettingsList':

        $rd = $db->getMultipleData("SELECT waqs_id,waqs_Name FROM finascop_wallet_queue_settings", true);

        if (!empty($rd)) {
            echo '{"totalCount":' . count($rd) . ',"data":' . json_encode($rd) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;
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
    case 'updateLedgerList':
        $ledger = $_POST;
        $ledgerCount = $db->getItemFromDB("SELECT COUNT(1) FROM " . FINASCOP_DB . "finascop_accounts_ledgertype_default WHERE ledgertypedefaultname = '{$ledger['ledgertypedefaultname']}'");
        if ($ledgerCount > 0) {
            echo '{"success":false}';
            exit(1);
        }

        unset($ledger['apikey']);
        unset($ledger['tstamp']);
        $ledgerId = $db->getItemFromDB("SELECT IF(MAX(ledgertypedefaultid) IS NULL,1,MAX(ledgertypedefaultid) + 1) FROM "
                . FINASCOP_DB . "finascop_accounts_ledgertype_default");
        $ledger['ledgertypedefaultid'] = $ledgerId;
        $ledger['isSystem'] = 1;
        $ledger['ledt_referenceID'] = getNewFinascopApiKey();
        ;
        $ledger['ledt_referenceIDCRC32'] = crc32($ledger['ledt_referenceID']);

        $status = $db->perform(FINASCOP_DB . "finascop_accounts_ledgertype_default", $ledger);
        if ($status == 1) {
            echo '{"success":true}';
        } else {
            echo '{"success":false}';
        }
        break;
    case 'getLedgerStatus':

        /* $rd = $db->getMultipleData("SELECT ledgertypedefaultname as ledgerName,
          IF((SELECT COUNT(1) FROM finascop_company) > 1,IF(COUNT(ledgertypedefaultname) = (SELECT COUNT(1) FROM finascop_company),0,1),IF(COUNT(ledgertypedefaultname) = (SELECT COUNT(1) FROM finascop_company),1,0)) AS status,GroupName,ledt_referenceID FROM
          finascop_accounts_ledgertype_default altd CROSS JOIN
          (SELECT alt.ledgertypename,alt.ledgertypedefaultid FROM finascop_accounts_ledgertype alt LEFT JOIN finascop_company fc
          ON alt.ledgercompid = fc.comp_id) jn
          ON altd.ledgertypedefaultid = jn.ledgertypedefaultid
          GROUP BY ledgertypedefaultname
          UNION
          SELECT altd.ledgertypedefaultname as ledgerName,0 AS status,GroupName,ledt_referenceID FROM
          finascop_accounts_ledgertype_default altd
          WHERE altd.ledgertypedefaultid NOT IN (SELECT ledgertypedefaultid FROM finascop_accounts_ledgertype)", true); */

        $response = array();
        $rd = $db->getMultipleData("SELECT * from finascop_accounts_ledgertype_default order by ledgertypedefaultname", true);
        if ($rd) {
            foreach ($rd as $rdvalues) {
                $ismissing = false;
                $companys = $db->getMultipleData("SELECT * FROM finascop_company", true);
                foreach ($companys as $company) {
                    $ledgertypecount = $db->getItemFromDB("SELECT count(*) FROM finascop_accounts_ledgertype where ledgercompid = {$company['comp_id']} and ledgertypedefaultid = {$rdvalues['ledgertypedefaultid']}  ", true);
                    if (intval($ledgertypecount) == 0)
                        $ismissing = true;
                    $ledgercount = $db->getItemFromDB("SELECT count(*) FROM finascop_branch where br_ID not in (select accled_BranchId from finascop_accounts_ledger join finascop_accounts_ledgertype using (ledgertypeid) where accled_CompId = {$company['comp_id']} and ledgertypedefaultid ={$rdvalues['ledgertypedefaultid']} )  ", true);
                    if (intval($ledgercount) != 0)
                        $ismissing = true;
                }
                switch ($rdvalues['isPaymentGateway']) {
                    case 1:
                        if ($rdvalues['GroupName'] == 'Bank')
                            $isPaymentGateway = 'Yes';
                        else
                            $isPaymentGateway = '-';
                        break;
                    case 0:
                        if ($rdvalues['GroupName'] == 'Bank')
                            $isPaymentGateway = 'No';
                        else
                            $isPaymentGateway = '-';
                        break;
                    default:
                        $isPaymentGateway = '-';
                        break;
                }
                $response[] = array("isPaymentGateway" => $isPaymentGateway, "ledgertypedefaultid" => $rdvalues['ledgertypedefaultid'], "ledgerName" => $rdvalues['ledgertypedefaultname'], "status" => ($ismissing == true ? '0' : '1'), "GroupName" => $rdvalues['GroupName'], 'ledt_referenceID' => $rdvalues['ledt_referenceID']);
            }
        }

        if (!empty($rd)) {
            echo '{"totalCount":' . count($response) . ',"data":' . json_encode($response) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;
    case 'createNewAutoLedgers':
        $db->query('begin');
        $companies = $db->getMultipleData("SELECT comp_id,comp_name FROM finascop_company", true);
        foreach ($companies as $key => $company) {
            $companyID = $company['comp_id'];

            $leddef = $db->getMultipleData("SELECT altd.ledgertypedefaultid,altd.ledgertypedefaultname,altd.Group_ID,altd.GroupName "
                    . " FROM finascop_accounts_ledgertype_default altd LEFT JOIN (SELECT * FROM finascop_accounts_ledgertype WHERE ledgercompid = {$companyID}) AS alt "
                    . " ON altd.ledgertypedefaultid = alt.ledgertypedefaultid "
                    . " WHERE alt.ledgertypedefaultid IS NULL", true);
            if (count($leddef) <= 0) {
                continue;
            }
            foreach ($leddef as $k => $value) {
                $ledExists = $db->getItemFromDB("SELECT count(1)  from " . FINASCOP_DB . "finascop_accounts_ledgertype WHERE ledgertypename = '{$value['ledgertypedefaultname']}'");
                if ($ledExists > 0) {
                    continue;
                }
                $maxledgerid = $db->getItemFromDB("SELECT coalesce(max(ledgertypeid),0)+1 as id  from " . FINASCOP_DB . "finascop_accounts_ledgertype; ", true);
                $WalleRefId = getNewFinascopApiKey();
                $leddata = array('ledgertypeid' => $maxledgerid, 'ledgertypename' => $value['ledgertypedefaultname'],
                    'Group_ID' => $value['Group_ID'], 'GroupName' => $value['GroupName'], 'isCommon' => 0, 'isSystem' => 1,
                    'ledgercompid' => $companyID, 'ledgertypedefaultid' => $value['ledgertypedefaultid'],
                    'falt_ReferenceID' => $WalleRefId,
                    'falt_ReferenceIDCRC32' => crc32($WalleRefId),
                );


                $status = $db->perform(FINASCOP_DB . "finascop_accounts_ledgertype", $leddata);
            }


            $ldg_company = $db->getItemFromDB("SELECT comp_shortname from " . FINASCOP_DB . "finascop_company WHERE comp_id = {$companyID}");

            $rd = $db->getMultipleData("SELECT fb.br_ID FROM finascop_branch fb INNER JOIN finascop_branch_company fbc ON fb.br_ID = fbc.br_Id WHERE fbc.comp_id = {$companyID}");
            $status = 1;

            $leddef = $db->getMultipleData("SELECT alt.ledgertypedefaultid,alt.ledgertypename AS ledgertypedefaultname,alt.Group_ID,alt.GroupName"
                    . " FROM finascop_accounts_ledgertype alt WHERE ledgertypedefaultid <> 0 AND ledgercompid = {$companyID}", true);

            foreach ($rd as $brID) {
                $ldg_branch = $db->getItemFromDB("select branch_shortname from " . FINASCOP_DB . "finascop_branch where br_ID = {$brID}");


                foreach ($leddef as $k => $value) {
                    $ledgName = $value['ledgertypedefaultname'] . '_' . $ldg_company . '_' . $ldg_branch;
                    $ledExists = $db->getItemFromDB("SELECT count(1)  from " . FINASCOP_DB . "finascop_accounts_ledger WHERE accled_LedgerName = '{$ledgName}'");
                    if ($ledExists > 0) {
                        continue;
                    }
                    $ledgertypedefaultid = $value['ledgertypedefaultid'];
                    $accLedgerTypeId = $db->getItemFromDB("SELECT ledgertypeid from " . FINASCOP_DB . "finascop_accounts_ledgertype where ledgertypedefaultid = {$ledgertypedefaultid} and ledgercompid = {$companyID}");
                    if (intval($accLedgerTypeId) == 0) {
                        echo "{success:false,errors: 'FINASCOP: Missing Ledger type details for the company.' }";
                        exit();
                    }
                    $ledgertypename = $value['ledgertypedefaultname'];
                    $accled_ReferenceId = $db->getItemFromDB("SELECT accled_ReferenceId from " . FINASCOP_DB . "finascop_accounts_ledger WHERE ledgertypename = '{$ledgertypename}' AND accled_BranchId = {$brID}");
                    if (!empty($accled_ReferenceId)) {
                        continue;
                    }

                    $accLedgerId = $db->getItemFromDB("SELECT IF(MAX(accled_Ledger_Id) IS NULL,1,MAX(accled_Ledger_Id)+1) from " . FINASCOP_DB . "finascop_accounts_ledger");
                    $WalleRefId = getNewFinascopApiKey();

                    $ledger = array(
                        'accled_Ledger_Id' => $accLedgerId,
                        'accled_LedgerName' => $value['ledgertypedefaultname'] . '_' . $ldg_company . '_' . $ldg_branch,
                        'ledgertypeid' => $accLedgerTypeId,
                        'ledgertypename' => $value['ledgertypedefaultname'],
                        'Group_ID' => $value['Group_ID'],
                        'GroupName' => $value['GroupName'],
                        'accled_system' => 1,
                        'accled_IsEnabled' => 1,
                        'accled_IsLocal' => 0,
                        'accled_ReferenceId' => $WalleRefId,
                        'accled_RefIdCRC32' => crc32($WalleRefId),
                        'accled_BranchId' => $brID,
                        'accled_CompId' => $companyID,
                        'accled_IsVendor' => 0
                    );
                    $status = $db->perform(FINASCOP_DB . "finascop_accounts_ledger", $ledger);
                }
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success : true}";
        } else {
            echo "{success : false}";
        }

        break;
    case 'updatePaymentGw':
        $GroupName = $_POST['GroupName'];
        $isPaymentGateway = $_POST['isPaymentGateway'];
        $ledgertypedefaultid = $_POST['ledgertypedefaultid'];
        if ($isPaymentGateway == 'Yes') {
            $data['isPaymentGateway'] = 0;
        } else {
            $data['isPaymentGateway'] = 1;
        }
        $db->query('begin');
        if ($GroupName == 'Bank') {
            $status = $db->perform(FINASCOP_DB . "finascop_accounts_ledgertype_default", $data, 'update', " ledgertypedefaultid = {$ledgertypedefaultid} ");
        }

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success : true}";
        } else {
            echo "{success : false}";
        }
        break;
}