<?php

require_once(ROOT . '/finascop_config/lib.php');
require_once(INCLUDE_PATH . "/finascop_common_functions.php");
require_once(INCLUDE_PATH . "/finascop_wallet_client.php");


switch ($op) {

    case 'getInvoiceItems':
        $qry = "SELECT (SELECT sait_Name from " . FINASCOP_DB . "finascop_sales_itemmaster WHERE sait_ID = init_itemID) AS item,"
                . " init_itemID AS item_id ,init_Rate AS rate, init_itemQty AS quantity,"
                . "(SELECT sait_DefaultUnit from " . FINASCOP_DB . "finascop_sales_itemmaster WHERE sait_ID = init_itemID) AS unit "
                . "from " . FINASCOP_DB . "finascop_sales_invoice_items WHERE init_invoiceID = {$_POST['invID']}";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . count($items) . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;


    case 'getgroupitems':

        $group_names = $db->getMultipleData("SELECT stgp_groupID,COALESCE(stgp_groupName,' ') AS stgp_groupName, stgp_parentGpID  
		from " . FINASCOP_DB . "finascop_stock_group ", true);

        $group_tree = buildtree($group_names, $parentId = 0);
        //print_r($group_tree);
        echo json_encode($group_tree);
        break;


    case 'getConversionList':


        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;

        if (isset($_POST['from_date']) && isset($_POST['to_date'])) {

            $qry = "SELECT stco_id AS conversion_id, stco_No AS conversion_no, stco_Date AS converted_on,
        stco_ItemsConsumed AS consumed_items_count,stco_ItemsProduced AS produced_items_count, 
        (SELECT concat(FirstName,' ' ,LastName) FROM " . FINASCOP_DB . "finascop_usr_profile WHERE stco_AddedBy = UserId) AS added_by from " . FINASCOP_DB . "finascop_stock_conversion WHERE br_id = {$currentBranch} AND stco_Date BETWEEN '{$_POST['from_date']}' AND '{$_POST['to_date']}' LIMIT $rec_start,$rec_limit";

            $items = $db->getMulipleData($qry, true);
            $countQuery = "SELECT COUNT(*) FROM " . FINASCOP_DB . "finascop_stock_conversion  WHERE br_id = {$currentBranch} AND stco_Date BETWEEN '{$_POST['from_date']}' AND '{$_POST['to_date']}'";
            $count = $db->getItemFromDB($countQuery);
        } else {
            $qry = "SELECT stco_id AS conversion_id, stco_No AS conversion_no, stco_Date AS converted_on,
        stco_ItemsConsumed AS consumed_items_count,stco_ItemsProduced AS produced_items_count, 
        (SELECT concat(FirstName,' ' ,LastName) FROM " . FINASCOP_DB . "finascop_usr_profile WHERE stco_AddedBy = UserId) AS added_by from " . FINASCOP_DB . "finascop_stock_conversion WHERE br_id = {$currentBranch} LIMIT $rec_start,$rec_limit";

            $items = $db->getMulipleData($qry, true);
            $countQuery = "SELECT COUNT(*) FROM " . FINASCOP_DB . "finascop_stock_conversion  WHERE br_id = {$currentBranch} ";
            $count = $db->getItemFromDB($countQuery);
        }

        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'getStockConversionDetails':

        $conversion_id = $_POST['conversion_id'];


        $qry = "SELECT (SELECT stit_itemName FROM " . FINASCOP_DB . "finascop_stock_itemmaster fsi "
                . "WHERE fsi.stit_ID = fsb.stit_ID) AS itemname, stcd_Qty AS qty,"
                . " IF(stcd_IsConsumption = 1,'consumption','production') AS consORpro "
                . "FROM " . FINASCOP_DB . "finascop_stock_branch fsb "
                . "INNER JOIN " . FINASCOP_DB . "finascop_stock_conversion_details fscd ON fsb.stbr_id = fscd.stbr_id "
                . "WHERE stco_id = '{$conversion_id}'";

        $items = $db->getMultipleData($qry, true);

        if ($items) {
            echo '{"success":true,"totalCount":' . count($items) . ',"data":' . json_encode($items) . ',"msg":"success"}';
        } else {
            $msg = "'Error no data.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;

    case 'saveInvoice':
        $db->query('begin');
        $cDate = explode('/', $_POST['inv_date']);
        date_default_timezone_set('Asia/Kolkata');
        $cDate = date("Y-m-d", mktime(0, 0, 0, $cDate[1], $cDate[0], $cDate[2]));
        $poDate = explode('/', $_POST['purchase_order_date']);
        $poDate = date("Y-m-d", mktime(0, 0, 0, $poDate[1], $poDate[0], $poDate[2]));
        $data = array(
            'saen_InvoiceDate' => $cDate,
            'saen_ClientID' => $_POST['client'],
            'saen_ClinetsPurchaseOrder' => $_POST['purchcase_order_no'],
            'saen_ClinetsPurchaseOrderDate' => $poDate,
            'saen_RefNo' => $_POST['ref_no'],
            'saen_Bank' => $_POST['bank'],
            'saen_Discount' => $_POST['discount'],
            'saen_Tax' => $_POST['tax'],
            'saen_terms_id' => $_POST['terms'],
            'saen_signature' => $_POST['signature'],
        );
        $status = $db->perform(FINASCOP_DB . 'finascop_sales_invoice', $data);
        $invoice_id = $db->getLastInsertId();

        $status = insertInvoiceNo($invoice_id);

        $status = saveItems($invoice_id, $_POST['formData']);


        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Invoice saved successfully.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error while saving invoice.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'getTermsData':
        $qry = "SELECT inte_terms AS terms, inte_termsDetails AS term_details from " . FINASCOP_DB . "finascop_inventory_terms WHERE inte_id = '{$_POST['id']}' ";
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';

        break;
    case 'listTermsdata':
        $qry = "SELECT inte_id AS id,inte_terms as terms,inte_termsDetails AS details from " . FINASCOP_DB . "finascop_inventory_terms";
        $terms = $db->getMulipleData($qry, true);
        if (!empty($terms)) {
            echo '{"totalCount":' . count($terms) . ',"data":' . json_encode($terms) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'saveTerms':
        $termsName = $_POST['terms'];
        $IsUnique = $db->getItemFromDB("SELECT COUNT(inte_terms) from " . FINASCOP_DB . "finascop_inventory_terms WHERE inte_terms = '{$termsName}'");
        if ($IsUnique > 0) {
            echo "{success: false,msg:'Terms name already existing.'}";
            exit;
        }
        $data = array(
            "inte_terms" => $_POST['terms'],
            "inte_termsDetails" => $_POST['term_details']
        );
        $status = $db->perform(FINASCOP_DB . "finascop_inventory_terms", $data);
        if ($status) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'getDefaultUnit':
        $item_id = $_POST['item_id'];
        $qry = "SELECT sait_DefaultUnit AS item_unit from " . FINASCOP_DB . "finascop_sales_itemmaster WHERE sait_ID = {$item_id}";
        //$items = $db->getFromDB($qry,true);
        $items = $db->getItemFromDB($qry);
        if (!empty($items)) {
            echo '{"totalCount":' . count($items) . ',"data":[{"item_unit": ' . json_encode($items) . '}]}';
            //echo '{"totalCount":' . count($items) . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'getItems':
        $client_id = $_POST['client_id'];
        $qry = "SELECT sait_ID AS item_id, sait_Name AS item_name,sait_DefaultUnit AS default_unit from " . FINASCOP_DB . "finascop_sales_itemmaster WHERE sait_Ledger_Id = {$client_id} UNION "
                . "SELECT sait_ID AS item_id, sait_Name AS item,sait_DefaultUnit AS default_unit from " . FINASCOP_DB . "finascop_sales_itemmaster WHERE sait_Ledger_Id IS NULL";
        $items = $db->getMultipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . count($items) . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'getItemsData':
        $qry = "SELECT sait_ID AS item_id, sait_Name AS item, IF(sait_IsClientSpecific = 1,'on','') AS clinetspecific,"
                . "(SELECT ledgertypename from " . FINASCOP_DB . "finascop_accounts_ledger WHERE accled_Ledger_Id = sait_Ledger_Id) AS 'client', "
                . " sait_Ledger_Id as id,"
                . "sait_DefaultUnit AS default_unit from " . FINASCOP_DB . "finascop_sales_itemmaster WHERE sait_ID = '{$_POST['id']}' ";
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';

        break;
    case 'listItems':
        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;
        $currentCompanyID = $_SESSION['admin']->finascop_current_company_id;
        $qry = "SELECT sait_ID AS ItemId,sait_Name AS ItemName,ledgertypename  AS ClientName "
                . "from " . FINASCOP_DB . "finascop_sales_itemmaster LEFT join " . FINASCOP_DB . "finascop_accounts_ledger ON sait_Ledger_Id = accled_Ledger_Id "
                . " WHERE (accled_BranchId = {$currentBranch} OR accled_BranchId IS NULL) AND (accled_CompId = {$currentCompanyID} OR accled_CompId IS NULL)";
        $items = $db->getMultipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . count($items) . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;
    case 'saveItem':
        if (intval($_POST['id']) == 0) {
            $itemName = $_POST['item'];
            $IsUnique = $db->getItemFromDB("SELECT COUNT(sait_Name) from " . FINASCOP_DB . "finascop_sales_itemmaster WHERE sait_Name = '{$itemName}'");
            if ($IsUnique > 0) {
                echo "{success: false,msg:'Item already existing.'}";
                exit;
            }
        }

        if (!empty($_POST['clinetspecific'])) {
            $data = array(
                "sait_Name" => $_POST['item'],
                "sait_IsClientSpecific" => 1,
                "sait_Ledger_Id" => $_POST['client'],
                "sait_DefaultUnit" => $_POST['default_unit']
            );
        } else {
            $data = array(
                "sait_Name" => $_POST['item'],
                "sait_IsClientSpecific" => 0,
                //"sait_Ledger_Id" => '',
                "sait_DefaultUnit" => $_POST['default_unit']
            );
        }
        if (intval($_POST['id']) == 0) {
            $status = $db->perform(FINASCOP_DB . "finascop_sales_itemmaster", $data);
            if ($status) {
                echo "{success: true,msg:'Saved Successfully'}";
            } else {
                echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
            }
        } else {
            $con = 'sait_ID = ' . intval($_POST['id']);
            $status = $db->perform(FINASCOP_DB . "finascop_sales_itemmaster", $data, 'update', $con);
            if ($status) {
                echo "{success: true,msg:'Updated Successfully'}";
            } else {
                echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
            }
        }


        break;
    case 'listClients':
        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;
        $currentCompanyID = $_SESSION['admin']->finascop_current_company_id;
        $qry = "SELECT accled_Ledger_Id AS id,ledgertypename AS client from " . FINASCOP_DB . "finascop_accounts_ledger WHERE Group_ID = 7 AND "
                . "accled_BranchId = {$currentBranch} AND accled_CompId = {$currentCompanyID}";
        $client = $db->getMultipleData($qry, true);
        if (!empty($client)) {
            echo '{"totalCount":' . count($client) . ',"data":' . json_encode($client) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listInvoices':
        $qry = "SELECT saen_Id as InvID,saen_InvoiceNo AS InvoiceNo,saen_InvoiceDate as InvoiceDate,"
                . "(SELECT ledgertypename AS client from " . FINASCOP_DB . "finascop_accounts_ledger WHERE accled_Ledger_Id = saen_ClientID) as Client,"
                . "saen_ClinetsPurchaseOrder as ClientPO,saen_ClinetsPurchaseOrderDate as ClientPODate,"
                . "saen_RefNo as RefNo, saen_Bank as Bank, saen_Discount as Discount, saen_Tax as Tax,"
                . "(SELECT inte_termsDetails from " . FINASCOP_DB . "finascop_inventory_terms WHERE inte_id = saen_terms_id) as Terms,"
                . "saen_signature as Signature from " . FINASCOP_DB . "finascop_sales_invoice";
        $invoices = $db->getMulipleData($qry, true);
        if (!empty($invoices)) {
            echo '{"totalCount":' . count($invoices) . ',"data":' . json_encode($invoices) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'saveBankMasterData':
        if (!empty($_POST['id']))
        // $cond = " and inba_Id <> " . intval($_POST['id']);
            $IsUnique = $db->getItemFromDB("SELECT COUNT(inba_AccountNo) from " . FINASCOP_DB . "finascop_inventory_bankdetails WHERE inba_AccountNo = '{$_POST['id_accno']}' AND inba_BankName = '{$_POST['id_bankname']}'AND inba_Id <> '{$_POST['id']}' ");
        if ($IsUnique > 0) {
            echo "{success: false,msg:'Acc.No already existing.'}";
            exit;
        }
        $IsUnique = $db->getItemFromDB("SELECT COUNT(inba_BankName) from " . FINASCOP_DB . "finascop_inventory_bankdetails WHERE inba_BankName = '{$_POST['id_bankname']}' AND inba_Branch = '{$_POST['id_branch']}'AND inba_Id <> '{$_POST['id']}'");
        if ($IsUnique > 0) {
            echo "{success: false,msg:'Bank Name already existing.'}";
            exit;
        }

        $data = array(
            "inba_AccountNo" => $_POST['id_accno'],
            "inba_BankName" => $_POST['id_bankname'],
            "inba_Branch" => $_POST['id_branch'],
            "inba_IFSC" => $_POST['id_ifc']
        );
        if (empty($_POST['id'])) {
            $status = $db->perform(FINASCOP_DB . "finascop_inventory_bankdetails", $data);
            if ($status) {
                echo "{success: true,msg:'Saved Successfully'}";
            } else {
                echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
            }
        } else {
            $con = 'inba_Id=' . intval($_POST['id']);
            $status = $db->perform(FINASCOP_DB . "finascop_inventory_bankdetails", $data, 'update', $con);
            if ($status) {
                echo "{success: true,msg:'Updated Successfully'}";
            } else {
                echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
            }
        }




        break;


    case 'listBankdata':
        global $db;
        $qry = "SELECT inba_Id AS id,inba_AccountNo AS accountno,inba_BankName AS  bank,inba_Branch AS branch,inba_IFSC As ifc  from " . FINASCOP_DB . "finascop_inventory_bankdetails";

        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        //echo' {"totalCount":"2","data":[{"customer":"Hari","client":"Alleppey Parcel Service","bank":"SBI","amount":"1000","utr":"123"},
        //{"customer":"Manu","client":"ABC","bank":"HDFC","amount":"5000","utr":"1234"}]}';

        break;

    case 'getBankMasterData':

        $qry = "select inba_Id AS id, inba_AccountNo AS id_accno,inba_BankName As id_bankname,inba_Branch As id_branch,inba_IFSC AS id_ifc
        from " . FINASCOP_DB . "finascop_inventory_bankdetails WHERE inba_Id = '{$_POST['id']}' ";
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;

    case 'getConsumptionItems':
        $qry = "SELECT stit_ID AS item_id, stit_itemName AS item_name from " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_Convertible=1";
        $items = $db->getMultipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . count($items) . ',"data":' . json_encode($items) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;


    case 'getProductionItems':
        $qry = "SELECT stit_ID AS item_id, stit_itemName AS item_name from " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_Convertible=1 OR stit_Convertible=0";
        $items = $db->getMultipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . count($items) . ',"data":' . json_encode($items) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;

    case 'saveConversion':
        $db->query('begin');
        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;
        if (empty($currentBranch)) {
            echo '{"success":false,"msg":"Invalid branch"}';
            exit;
        }
        $UserId = $_SESSION['admin']->Finascop_UserId;
        $i = 'abc';
        $data = array(
            "stco_No" => getNextConversionNo(),
            "stco_Date" => date("Y-m-d"),
            "stco_AddedBy" => $UserId,
            "stco_ItemsConsumed" => $_POST['items_consumed'],
            "stco_ItemsProduced" => $_POST['items_produced'],
            "br_id" => $currentBranch
        );


        $status = $db->perform(FINASCOP_DB . "finascop_stock_conversion", $data);

        $stco_id = $db->getLastInsertId();

        $ConsumptionData = json_decode(stripslashes($_POST['consumptiongridData']), true);

        foreach ($ConsumptionData as $k => $v) {

            $item_id = intval($v['item_id']);
            $qty = floatval($v['qty']);
            if ($qty <= 0) {
                $db->query('rollback');
                echo '{"success":false,"msg":"Invalid consumption quantity"}';
                exit;
            }
            $qry = "SELECT stbr_id, stbr_CurrentStock from " . FINASCOP_DB . "finascop_stock_branch WHERE stit_ID = {$item_id} AND br_Id = $currentBranch ";

            $branchRow = $db->getFromDB($qry, true);
            $stbr_id = $branchRow['stbr_id'] ?? 0;
            if (empty($stbr_id)) {
                $db->query('rollback');
                echo '{"success":false,"msg":"Stock branch record not found for consumption item"}';
                exit;
            }
            if ($qty > floatval($branchRow['stbr_CurrentStock'])) {
                $db->query('rollback');
                echo '{"success":false,"msg":"Insufficient stock for consumption item"}';
                exit;
            }
            $data = array(
                "stco_id" => $stco_id,
                "stbr_id" => intval($stbr_id),
                "stcd_Qty" => $qty,
                "stcd_IsConsumption" => 1
            );
            $status = $db->perform(FINASCOP_DB . "finascop_stock_conversion_details", $data);
            $integrity_key = sha1(microtime(true) . mt_rand(10000, 90000));
            $qry = "UPDATE  " . FINASCOP_DB . "finascop_stock_branch SET stbr_CurrentStock = stbr_CurrentStock - {$qty},"
                    . "stbr_updated_on = '{$integrity_key}'"
                    . " WHERE stbr_id = {$stbr_id} ";
            $status = $db->query($qry);
        }
        $producedgridData = json_decode(stripslashes($_POST['producedgridData']), true);
        foreach ($producedgridData as $l => $m) {
            $item_id = intval($m['item_id']);
            $qty = floatval($m['qty']);
            if ($qty <= 0) {
                $db->query('rollback');
                echo '{"success":false,"msg":"Invalid production quantity"}';
                exit;
            }

            $qry = "SELECT stbr_id from " . FINASCOP_DB . "finascop_stock_branch WHERE stit_ID = {$item_id} AND br_Id = $currentBranch ";
            $stbr_id = $db->getItemFromDB($qry);
            if (empty($stbr_id)) {
                $db->query('rollback');
                echo '{"success":false,"msg":"Stock branch record not found for production item"}';
                exit;
            }
            $data = array(
                "stco_id" => $stco_id,
                "stbr_id" => intval($stbr_id),
                "stcd_Qty" => $qty,
                "stcd_IsConsumption" => 0
            );
            $status = $db->perform(FINASCOP_DB . "finascop_stock_conversion_details", $data);
            $integrity_key = sha1(microtime(true) . mt_rand(10000, 90000));

            $qry = "UPDATE  " . FINASCOP_DB . "finascop_stock_branch SET stbr_CurrentStock = stbr_CurrentStock + {$qty},"
                    . "stbr_updated_on = '{$integrity_key}'"
                    . " WHERE stbr_id = {$stbr_id} ";
            $status = $db->query($qry);
        }

        $status = $db->query('commit');

        if ($status) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false, msg: 'Error occured while saving data'}";
        }

        break;



    case 'saveStockGroups' :
        $curr_groupId = $_POST['id'];

        $curr_group_name = $_POST['hide_group_name'];

        $edit_group_name = $_POST['group_name'];

        $curr_parent_id = $_POST['hide_ParentGroup_name'];

        $edit_parent_id = ($_POST['parent_group_name'] == '' ? '0' : $_POST['parent_group_name']);

        $parent = ($_POST['parent_group_name'] == '' ? '0' : $_POST['parent_group_name']);
        $group = $_POST['group_name'];

        if ($parent != 0) {
            $fq_query = " SELECT stgp_groupName FROM " . FINASCOP_DB . "finascop_stock_group WHERE stgp_groupID = '$parent' ";
            $parent_name = $db->getItemFromDB($fq_query);
            $fullyQualifiedGroup = $parent_name . "\\" . $_POST['group_name'];
        } else {

            $fullyQualifiedGroup = $_POST['group_name'];
        }


        if (empty($_POST['id'])) {

            $groupName_check_qry = " SELECT COUNT(*) FROM " . FINASCOP_DB . "finascop_stock_group WHERE stgp_groupName = '{$edit_group_name}' "
                    . "AND stgp_parentGpID = '$parent' ";
            $groupNameExist = $db->getItemFromDB($groupName_check_qry);

            if ($groupNameExist > 0) {
                echo "{success: false,msg:'Group Name already exist.'}";
                exit;
            }

            $db->query('begin');
            $data1 = array(
                "stgp_groupName" => $group,
                "stgp_parentGpID" => $parent,
                "stgp_isLeaf" => 1,
                "stgp_fqGroupName" => $fullyQualifiedGroup
            );
            $status = $db->perform(FINASCOP_DB . "finascop_stock_group", $data1);
            $data2 = array(
                "stgp_isLeaf" => 0
            );
            $con = 'stgp_groupID = ' . $parent;
            $status = $db->perform(FINASCOP_DB . "finascop_stock_group", $data2, 'update', $con);
            $status = $db->query('commit');
            if ($status) {
                echo "{success: true,msg:'Saved Successfully'}";
            } else {
                echo "{success: false, msg: 'Error occured while saving data'}";
            }
        } else {
            $db->query('begin');

            $parentId = $edit_parent_id;

            $child_check_query = "SELECT COUNT(stgp_parentGpID) FROM " . FINASCOP_DB . "finascop_stock_group WHERE stgp_parentGpID = {$curr_parent_id} ";
            $child_check_query_result = $db->query($child_check_query);
            if ($child_check_query_result == 1) {
                $data = array(
                    "stgp_isLeaf" => 1
                );
                $con = 'stgp_groupID = ' . $curr_parent_id;
                $status = $db->perform(FINASCOP_DB . "finascop_stock_group", $data, 'update', $con);
            }

            if ($curr_group_name != $edit_group_name) {
                $groupName_check_qry = " SELECT COUNT(*) FROM " . FINASCOP_DB . "finascop_stock_group WHERE stgp_groupName = '{$edit_group_name}'"
                        . " AND stgp_parentGpID = '$parent' ";
                $groupNameExist = $db->getItemFromDB($groupName_check_qry);

                if ($groupNameExist > 0) {
                    echo "{success: false,msg:'Group Name already exist.'}";
                    exit;
                }
            }

            do {
                if ($curr_groupId == $edit_parent_id) {

                    echo "{success: false,errors:'Can not choose this parent group.This is a child of $curr_group_name'}";
                    exit;
                }
                $parentGroupName_check_qry = "SELECT stgp_parentGpID FROM " . FINASCOP_DB . "finascop_stock_group WHERE stgp_groupID = '$edit_parent_id'";
                $edit_parent_id = $db->getItemFromDB($parentGroupName_check_qry);
            } while (intval($edit_parent_id) != 0);


            $qry = " SELECT COUNT(*) FROM " . FINASCOP_DB . "finascop_stock_group WHERE stgp_parentGpID = '$curr_groupId' ";
            $result = $db->getItemFromDB($qry);

            if ($result < 1) {
                $data = array(
                    "stgp_groupName" => $_POST['group_name'],
                    "stgp_parentGpID" => $parentId,
                    "stgp_isLeaf" => 1,
                    "stgp_fqGroupName" => $fullyQualifiedGroup
                );
            } else {

                $data = array(
                    "stgp_groupName" => $_POST['group_name'],
                    "stgp_parentGpID" => $parentId,
                    "stgp_isLeaf" => 0,
                    "stgp_fqGroupName" => $fullyQualifiedGroup
                );
            }

            $con = 'stgp_groupID=' . intval($_POST['id']);
            $status = $db->perform(FINASCOP_DB . "finascop_stock_group", $data, 'update', $con);

            $data2 = array(
                "stgp_isLeaf" => 0
            );
            $con = 'stgp_groupID = ' . $parentId;
            $status = $db->perform(FINASCOP_DB . "finascop_stock_group", $data2, 'update', $con);
            $status = $db->query('commit');

            if ($status) {
                echo "{success: true,msg:'Updated Successfully'}";
            } else {
                echo "{success: false,errors:'Error occured while saving data'}";
            }
        }
        break;
    case 'getStockGroups':

        $exclude_id = $_POST['excludeId'];

        $rec_limit = empty($_POST['limit']) ? 19 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $rec_sort = $_POST['sort'];
        $condition = "";
        if (!empty($_POST['excludeId'])) {

            $condition = "where stgp_groupID NOT IN ( $exclude_id )";
            //$excludeIds = getExcludedIds($exclude_id);
        }
        $qry = "select a.stgp_groupID as group_id ,a.stgp_groupName as group_name,"
                . "COALESCE((select b.stgp_groupName from " . FINASCOP_DB . "finascop_stock_group b "
                . "where b.stgp_groupID = a.stgp_parentGpID),'') as parent_group,"
                . "a.stgp_fqGroupName as fqGroupName"
                . " from " . FINASCOP_DB . "finascop_stock_group a $condition LIMIT $rec_start,$rec_limit";


        $data = $db->getMultipleData($qry, true);
        $qry = "select count(*) from " . FINASCOP_DB . "finascop_stock_group";
        $count = $db->getItemFromDB($qry, true);
        echo "{'success':true,'totalCount':" . $count . ",'data':" . json_encode($data) . "}";
        break;
    case 'getItemMasterStockGroups':


        $qry = "select stgp_groupID AS group_id ,stgp_groupName AS group_name,stgp_fqGroupName AS parent_group"
                . " from " . FINASCOP_DB . "finascop_stock_group where stgp_isLeaf = 1 ";


        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'gethsnStore':
        $search_hint = $_POST['query'];
        $qry = "select hsn_id,hsn_code,gst_percent from " . FINASCOP_DB . "finascop_hsn WHERE hsn_code LIKE '{$search_hint}%' order by hsn_code";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'saveItemMaster':

        $SKU = $_POST['stit_category_name'] . " " . $_POST['stit_brand_name'] . " " . $_POST['item_name'] . " " . $_POST['stit_product_variant'] . " " . $_POST['stit_quantity'];
        $dSKU = addslashes($SKU);

        $data = array(
            "stit_itemId" => $_POST['item'],
            "stit_SKU" => $SKU,
            "stit_HSNCode" => $_POST['HSN'],
            "stit_GST" => $_POST['GST'],
            "stit_Description" => $_POST['description'],
            "stit_product_variant" => $_POST['stit_product_variant'],
            "pdt_package_type_id" => $_POST['pdt_package_type_id'],
            "product_category" => $_POST['product_category'],
            "pdt_brand" => $_POST['pdt_brand'],
            "featured" => $_POST['featured'],
            "popular" => $_POST['popular'],
            "item_length" => $_POST['item_length'],
            "item_breadth" => $_POST['item_breadth'],
            "item_height" => $_POST['item_height'],
            "item_weight" => $_POST['item_weight'],
            "stit_item_volume" => $_POST['stit_item_volume'],
            "stit_long_description" => $_POST['stit_long_description'],
            "stit_quantity" => $_POST['stit_quantity'],
            "stit_itemName" => $_POST['item_name'],
            "stit_HSN_code" => $_POST['HSN_code'],
            "stit_package_type_namme" => $_POST['stit_package_type_namme'],
            "stit_category_name" => $_POST['stit_category_name'],
            "stit_brand_name" => $_POST['stit_brand_name'],
            "stitl1_optimumqty" => $_POST['stitl1_optimumqty'],
            "stitl2_optimumqty" => $_POST['stitl2_optimumqty'],
            "stitl3_optimumqty" => $_POST['stitl3_optimumqty'],
            "stit11_minimumqty" => $_POST['stit11_minimumqty'],
            "stit12_minimumqty" => $_POST['stit12_minimumqty'],
            "stit13_minimumqty" => $_POST['stit13_minimumqty'],
            "stit11_maximumqty" => $_POST['stit11_maximumqty'],
            "stit12_maximumqty" => $_POST['stit12_maximumqty'],
            "stit13_maximumqty" => $_POST['stit13_maximumqty'],
            "stii_csb" => $_POST['stii_csb']
        );



        $fsuidata['fsi_item_id'] = $data['stit_itemId'];
        $fsuidata['fsi_item_name'] = $data['stit_itemName'];
        $fsuidata['fsi_brand_id'] = $data['pdt_brand'];
        $fsuidata['fsi_brand_name'] = $data['stit_brand_name'];
        $fsuidata['fsi_category_id'] = $data['product_category'];
        $fsuidata['fsi_categry_name'] = $data['stit_category_name'];
        $fsuidata['fsi_variant'] = $data['stit_product_variant'];


        $itemName = $_POST['item'];

        if ($_POST['dupitem'] == 'D') {
            unset($_POST['id']);
        }
        $db->query('begin');
        if (empty($_POST['id'])) {

            $stit_fsiuid = updateUniqueItemTable(0, $fsuidata);
            $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];

            $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  WHERE stit_itemId = '{$itemName}' AND  stit_product_variant = '{$_POST['stit_product_variant']}' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']}");
            if ($IsItemNameUnique > 0) {
                echo "{success: false, msg:'This Item already existing.'}";
                exit;
            }
            $SKUUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  WHERE stit_SKU = '{$dSKU}'  ");
            if ($SKUUnique > 0) {
                echo "{success: false, msg:'This SKU already existing.'}";
                exit;
            }


            $status = $db->perform(FINASCOP_DB . "finascop_stock_itemmaster", $data);
            $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM finascop_stock_itemmaster WHERE stit_fsiuid = {$stit_fsiuid['fsi_uid']}");
            if ($stit_fsiuid['status'] == 'NEW') {

                $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $uuit, 'update', " fsi_uid = {$stit_fsiuid['fsi_uid']}");
            }
            $message = 'Saved Successfully';
        } else {
            $fsiUid = $db->getItemFromDB("SELECT stit_fsiuid FROM " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID = {$_POST['id']}");
            $stit_fsiuid = updateUniqueItemTable($fsiUid, $fsuidata);
            $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];
            $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  WHERE stit_itemId = '{$itemName}' AND stit_ID <> {$_POST['id']} "
                    . "AND  stit_product_variant = '{$_POST['stit_product_variant']}' AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']}");
            if ($IsItemNameUnique > 0) {
                echo "{success: false, msg:'This Item already existing.'}";
                exit;
            }
            $SKUUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  WHERE stit_SKU = '{$dSKU}'  AND stit_ID <> {$_POST['id']}");
            if ($SKUUnique > 0) {
                echo "{success: false, msg:'This SKU already existing.'}";
                exit;
            }
            $con = 'stit_ID=' . intval($_POST['id']);
            $data['stit_updatedOn '] = date("Y-m-d H:i:s");
            $status = $db->perform(FINASCOP_DB . "finascop_stock_itemmaster", $data, 'update', $con);
            $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM finascop_stock_itemmaster WHERE stit_fsiuid = {$stit_fsiuid['fsi_uid']}");
            if ($stit_fsiuid['status'] == 'NEW') {
                $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $uuit, 'update', " fsi_uid = {$stit_fsiuid['fsi_uid']}");
            }

            $fpodData['fpod_itemname'] = $_POST['item_name'];
            $status = $db->perform(FINASCOP_DB . "finascop_purchase_order_details", $fpodData, 'update', "fpod_itemid = " . intval($_POST['id']));

            $fsiiData['stii_itemmastername'] = $_POST['item_name'];
            $status = $db->perform(FINASCOP_DB . "finascop_stock_item_inventory", $fsiiData, 'update', "stii_itemmasterid = " . intval($_POST['id']));

            $fsiidData['stiid_itemmastername'] = $_POST['item_name'];
            $status = $db->perform(FINASCOP_DB . "finascop_stock_item_inventorydetails", $fsiidData, 'update', "stiid_itemmasterid = " . intval($_POST['id']));
            $message = "Updated Successfully";
        }
        $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'{$message}'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data' }";
        }

        break;

    case 'saveParty':
        $data = array(
            "stpa_Fname" => $_POST['firstname'],
            "stpa_Lname" => $_POST['lastname'],
            "stpa_Address" => $_POST['address'],
            "stpa_City" => $_POST['city'],
            "stpa_PINCODE" => $_POST['pincode'],
            "stpa_GSTIN" => $_POST['gstno'],
            "stpa_ContactPerson" => $_POST['contactperson'],
            "stpa_MobileNo" => $_POST['mobile'],
            "stpa_Email" => $_POST['email'],
            "stpa_PanNo" => $_POST['pan'],
            "dst_Id" => $_POST['dst_Id'],
            "br_id" => $_SESSION['admin']->finascop_current_branch_id,
            "stpa_IsVendor" => ( $_POST['partytype'] == 1 ? 1 : 0)
        );
        $fname = $_POST['firstname'];
        $lname = $_POST['lastname'];
        $db->query('begin');
        if (empty($_POST['id'])) {
            $IsNameUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_party  WHERE concat(stpa_Fname,' ' ,stpa_Lname) = '{$fname} {$lname}'");
            if ($IsNameUnique > 0) {
                echo "{success: false, msg:'Customer already existing.'}";
                exit;
            }
            $status = $db->perform(FINASCOP_DB . "finascop_stock_party", $data);

            if ($status) {
                echo "{success: true,msg:'Saved Successfully'}";
            } else {
                echo "{success: false, msg: 'Error occured while saving data'}";
            }
        }
//updating customer//
        else {
            $cur_fname = $_POST['fname'];
            $cur_lname = $_POST['lname'];

            if ($cur_fname == $fname && $cur_lname == $lname) {
                $IsNameUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_party  WHERE concat(stpa_Fname,' ' ,stpa_Lname) = '{$fname} {$lname}'");
                if ($IsNameUnique == 1) {
                    $con = 'stpa_id=' . intval($_POST['id']);
                    $status = $db->perform(FINASCOP_DB . "finascop_stock_party", $data, 'update', $con);

                    if ($status) {
                        echo "{success: true,msg:'Updated Successfully'}";
                    } else {
                        echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
                    }
                }
            } else {

                if ($cur_fname != $fname || $cur_lname != $lname) {
                    $IsNameUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_party  WHERE concat(stpa_Fname,' ' ,stpa_Lname) = '{$fname} {$lname}'");
                    if ($IsNameUnique > 0) {

                        echo "{success: false, msg:'Customer already existing.'}";
                        exit;
                    }
                }
                $con = 'stpa_id=' . intval($_POST['id']);
                $status = $db->perform(FINASCOP_DB . "finascop_stock_party", $data, 'update', $con);
                if ($status) {
                    echo "{success: true,msg:'Updated Successfully'}";
                } else {
                    echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
                }
            }
        }

        $db->query('commit');

        break;
    case 'getpartyData':
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $filter_query = ' 1=1';
        $br_id = $_SESSION['admin']->finascop_current_branch_id;

        if (isset($_POST['filter'])) {
            $allowedFields = ['stpa_Fname', 'stpa_Lname', 'stpa_GSTIN', 'stpa_City', 'stpa_PINCODE', 'dst_Id', 'item_id', 'item_name', 'item_code', 'item_barcode', 'batch_no', 'expiry_date', 'current_stock'];
            $filter_query .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }


        $qry = "select stpa_id as customerId, CONCAT(stpa_Fname,'-',stpa_id) as stpa_Fname,stpa_Lname as stpa_Lname,stpa_Address as stpa_Address,stpa_ContactPerson as stpa_ContactPerson,stpa_MobileNo as stpa_MobileNo,stpa_Email as stpa_Email,stpa_PanNo as stpa_PanNo ,stpa_City as stpa_City,stpa_PINCODE as stpa_PINCODE,stpa_GSTIN as stpa_GSTIN,"
                . "(select st_name from " . FINASCOP_DB . "finascop_state b inner join " . FINASCOP_DB . " finascop_district d on b.st_ID = d.st_Id where d.dst_Id = a.dst_Id)as st_name,"
                . "(select b.st_ID from " . FINASCOP_DB . "finascop_state b inner join " . FINASCOP_DB . " finascop_district d on b.st_ID = d.st_Id where d.dst_Id = a.dst_Id)as st_id,"
                . "(select c.dst_Id from " . FINASCOP_DB . " finascop_district c where c.dst_Id = a.dst_Id )as dst_Id,stpa_latitude,stpa_longitude,accled_ReferenceId,stpa_isLiveStock,"
                . "(select dst_Name from " . FINASCOP_DB . " finascop_district c where c.dst_Id = a.dst_Id )as dst_Name,stpa_dlno1,stpa_dlno2,stpa_fssaino,deliverMode_stdDelivery,asctedbrach_stdDelivery,deliverMode_cpr,deliveryRule_courier,deliveryRule_express,deliveryRule_slotted,stpa_latitude,stpa_longitude,asctedbrach_cpr "
                . "  from " . FINASCOP_DB . " finascop_stock_party a where br_id = {$br_id} and a.stpa_IsVendor=" . ( $_POST['partytype'] == 1 ? 1 : 0) . " and $filter_query order by stpa_id asc limit {$rec_start},{$rec_limit} ";

        $data = $db->getMultipleData($qry, true);

        $countQuery = "SELECT COUNT(*) FROM " . FINASCOP_DB . " finascop_stock_party a where br_id = {$br_id} and a.stpa_IsVendor=" . ( $_POST['partytype'] == 1 ? 1 : 0) . " and $filter_query ";
        $count = $db->getItemFromDB($countQuery);


        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }

        break;
    case 'listItemMasterData':
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
            $allowedFields = ['stpa_Fname', 'stpa_Lname', 'stpa_GSTIN', 'stpa_City', 'stpa_PINCODE', 'dst_Id', 'item_id', 'item_name', 'item_code', 'item_barcode', 'batch_no', 'expiry_date', 'current_stock'];
            $search .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
        }
        $countQuery = "SELECT count(*)
		from " . FINASCOP_DB . "finascop_stock_itemmaster order by stit_itemId";
        $count = $db->getItemFromDB($countQuery);
        $total = "select SUM(stit_MRP) AS total_mrp,SUM(stit_GST) as tax_total "
                . " from " . FINASCOP_DB . "finascop_stock_itemmaster {$search} order by stit_ID desc";
        $coltotal = $db->getFromDB($total, true);
        $qry = "select  stit_ID as ItemId ,product_is_home, (SELECT item_name FROM finascop_stock_itemmastername where itemname_id = stit_itemId) as ItemName ,"
                . "stit_itemName,stit_package_type_namme,stit_category_name,stit_brand_name,stit_product_variant,stit_quantity,"
                . "(SELECT hsn_code FROM finascop_hsn where hsn_id = stit_HSNCode) as hsn_code,stit_GST as tax,stit_MRP as mrp,(IF(stit_Convertible = 1,1,0) )AS convertable_off ,(IF(stit_Convertible = 0,1,0))AS convertable_on,"
                . "(IF(stit_SalesEnabled = 0,0,1) )AS list_in_sales_off ,(IF(stit_SalesEnabled = 1,0,1))AS list_in_sales_on,"
                . "(IF(stit_StockEnabled = 0,0,1) )AS stock_disabled ,(IF(stit_StockEnabled = 1,0,1))AS stock_enabled,"
                . "(IF(stit_PurchaseEnabled = 0,0,1) )AS list_in_purchase_off ,(IF(stit_PurchaseEnabled = 1,0,1))AS list_in_purchase_on,"
                . "(IF(stit_Tangible = 1,0,1) )AS tangible_off ,(IF(stit_Tangible = 0,0,1))AS tangible_on, @miCount:=(SELECT COUNT(1) FROM finascop_stock_item_images WHERE `product_id`= stit_ID AND `image_type` = 1 ), 
@aiCount:=(SELECT COUNT(1) FROM finascop_stock_item_images WHERE `product_id`= stit_ID AND `image_type` = 0), 
CONCAT(@miCount, '/',@aiCount) AS imgCount "
                . " from " . FINASCOP_DB . "finascop_stock_itemmaster {$search} order by stit_ID desc limit {$rec_start},{$rec_limit} ";
        $data = $db->getMultipleData($qry, true);
        $result = [];
        foreach ($data as $key => $value) {

            foreach ($coltotal as $k => $v) {

                $value[$k] = $v;
                $result[$key] = $value;
            }
        }

        echo '{"totalCount":' . $count . ',"data":' . json_encode($result) . '}';
        break;

    case 'getItemMaster_EditData':
        $id = $_POST['id'];
        $qry = "select stit_ID as itemId,stit_itemId as item ,stit_HSNCode as HSN,stit_GST as GST,stit_MRP as MRP, stgp_groupID as itemgroup,stit_product_variant,"
                . "stit_package_type_namme ,pdt_package_type_id,product_category,pdt_brand,featured,pdt_sale_rate,popular,item_length,item_breadth,item_height,item_weight,"
                //."(select stgp_groupName from ".FINASCOP_DB."finascop_stock_group b where b.stgp_groupID = a.stgp_groupID)as groups,"
                . " stit_Description as description,stit_long_description,stit_quantity,stit_HSN_code,stit_brand_name,stit_category_name,stit_itemName,stitl1_optimumqty,stitl2_optimumqty,stitl3_optimumqty,"
                . "stit11_minimumqty,stit12_minimumqty,stit13_minimumqty,stit11_maximumqty,stit12_maximumqty,stit13_maximumqty,stii_csb from " . FINASCOP_DB . "finascop_stock_itemmaster  where stit_ID = '$id' ";
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;

    case 'getStates':
        $defaultCountry = $db->getItemFromDB("SELECT country_id FROM retaline_country WHERE is_default = 1");

        $qry = "select st_ID,st_name from " . FINASCOP_DB . "finascop_state WHERE cnt_ID = {$defaultCountry} order by st_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        // echo '{success:true, data:'.json_encode($data).'}';
        break;
    case 'getDistrict':
        $state = $_POST['st_Id'];
        $qry = "select dst_ID,dst_Name from " . FINASCOP_DB . "finascop_district where st_Id = '$state'  order by dst_Name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        // echo '{success:true, data:'.json_encode($data).'}';
        break;

    case 'saveConvertableStatus':
        $data = array(
            "stit_Convertible" => $_POST['stit_Convertible']
        );
        $cond = " stit_ID = " . $_POST['ItemId'];
        //$convertible = $_POST['stit_Convertible'];
        $status = $db->perform(FINASCOP_DB . 'finascop_stock_itemmaster', $data, 'update', $cond);
        if ($status == true) {
            echo '{"success":true,"msg":"Convertable Status Updated."}';
        } else {
            echo '{"success":false,"msg":"Convertable Status Updation failed,"}';
        }
        break;
//
    case 'saveSalesEnabledStatus':
        $data = array(
            "stit_SalesEnabled" => $_POST['stit_SalesEnabled']
        );
        $cond = " stit_ID = " . $_POST['ItemId'];
        $status = $db->perform(FINASCOP_DB . 'finascop_stock_itemmaster', $data, 'update', $cond);
        if ($status == true) {
            echo '{"success":true,"msg":"Sales Status Updated."}';
        } else {
            echo '{"success":false,"msg":"Sales Status Updation failed,"}';
        }
        break;

    case 'saveStockEnabledStatus':
        $data = array(
            "stit_StockEnabled" => $_POST['stit_StockEnabled']
        );
        $cond = " stit_ID = " . $_POST['ItemId'];
        $status = $db->perform(FINASCOP_DB . 'finascop_stock_itemmaster', $data, 'update', $cond);
        if ($status == true) {
            echo '{"success":true,"msg":"Stock  Status Updated."}';
        } else {
            echo '{"success":false,"msg":"Stock  Status Updation failed,"}';
        }
        break;

    case 'getParty':
        $search_hint = $_POST['query'];
        if (!empty($_REQUEST['excludeIds'])) {
            $excludeIds = json_decode(stripslashes($_REQUEST['excludeIds']), true);
            if (!empty($excludeIds)) {
                $excludeIds = implode(',', $excludeIds);
            } else {
                $excludeIds = "''";
            }
        } else {
            $excludeIds = "''";
        }

        $br_id = $_SESSION['admin']->finascop_current_branch_id;
        $qry = "select stpa_id as party_id, concat(stpa_Fname,' ' ,stpa_Lname) as party "
                . "from " . FINASCOP_DB . "finascop_stock_party where stpa_Fname LIKE '{$search_hint}%' AND br_id = {$br_id} AND stpa_id NOT IN (" . $excludeIds . ") ";
        $data = $db->getMultipleData($qry, true);
        //echo '{"success":true,"data":' . json_encode($data) . '}';
        $o = new stdClass();
        $o->success = true;
        $o->data = $data;
        echo json_encode($o);
        break;

    case 'getGroup_EditData':

        $id = $_POST['id'];
        // $editGroup_name = $_POST['group_name'];
        //$editGroup_parent = $_POST['parent_group_name'];
        $qry = "select  stgp_groupID as groupId,stgp_groupName as group_name,stgp_parentGpID as parent_group_name,stgp_groupName as hide_group_name,stgp_parentGpID as hide_ParentGroup_name from " . FINASCOP_DB . "finascop_stock_group  where stgp_groupID = '$id'  ";
        $data = $db->getFromDB($qry, true);
        //echo '{"success":true,"data":' . json_encode($data) . '}';
        $o = new stdClass();
        $o->success = true;
        $o->data = $data;
        echo json_encode($o);
        break;
    case 'getCustomer_EditData':
        $id = $_POST['id'];
        $qry = "select  stpa_id as partyId,stpa_Fname as firstname,stpa_Fname as curr_firstname,stpa_Lname as lastname,stpa_Lname as curr_lastname,stpa_GSTIN as gstno,stpa_Address as address,stpa_City as city,stpa_PINCODE as pincode,stpa_ContactPerson,stpa_MobileNo,stpa_Email,stpa_PanNo,"
                . "(select st_name from " . FINASCOP_DB . " finascop_state b inner join " . FINASCOP_DB . " finascop_district d on b.st_ID = d.st_Id where d.dst_Id = a.dst_Id)as state,"
                . "(select dst_Name from " . FINASCOP_DB . "finascop_district  where dst_Id = a.dst_Id) as c_district,"
                . "(select e.st_ID from " . FINASCOP_DB . " finascop_state e inner join " . FINASCOP_DB . " finascop_district f on e.st_ID = f.st_Id where f.dst_Id = a.dst_Id ) as state_ID,"
                . "(select d.dst_ID from " . FINASCOP_DB . " finascop_district d   where d.dst_Id = a.dst_Id ) as dst_ID"
                . " from " . FINASCOP_DB . "finascop_stock_party a  where stpa_id = '$id' ";
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;

    case 'setBranchStockRateData':

        $db->query('begin');
        $item_id = $_POST['ItemId'];
        $br_id = $_SESSION['admin']->finascop_current_branch_id;


        $edit_curr_stock = $_POST['curr_stock'];
        $check_curr_stock = $_POST['hide_curr_stock'];
        $prev_key = $_POST['integrity_key'];

        if ($edit_curr_stock != $check_curr_stock) {
            $prev_key = $_POST['integrity_key'];
            if (!stockTableDataIntegrityIsOK($prev_key, $item_id, $br_id)) {
                echo "{'success':false,'msg':'Current Stock data has been updated by another user, since you access the data.<br>"
                . "Please reload the data. OR Please re-enter current stock as {$check_curr_stock}'}";
                exit;
            }
            $branchStockRateData1 = array(
                "stit_ID" => $_POST['ItemId'],
                "br_Id" => $_SESSION['admin']->finascop_current_branch_id,
                "stbr_CommonRate" => $_POST['commonRate'],
                "stbr_MinStock" => $_POST['min_stock'],
                "stbr_CurrentStock" => $_POST['curr_stock'],
                "stbr_updated_on" => sha1(microtime(true) . mt_rand(10000, 90000))
            );
        } else {
            $branchStockRateData1 = array(
                "stit_ID" => $_POST['ItemId'],
                "br_Id" => $_SESSION['admin']->finascop_current_branch_id,
                "stbr_CommonRate" => $_POST['commonRate'],
                "stbr_MinStock" => $_POST['min_stock'],
                "stbr_updated_on" => sha1(microtime(true) . mt_rand(10000, 90000))
            );
        }
        $isUpdate = $db->getItemFromDB("SELECT COUNT(*) FROM " . FINASCOP_DB . "finascop_stock_branch fsb WHERE fsb.stit_ID = '{$item_id}' and fsb.br_Id = '{$br_id}'");
        if ($isUpdate == 0) {
            $status = $db->perform(FINASCOP_DB . 'finascop_stock_branch', $branchStockRateData1);
        } else {
            $status = $db->perform(FINASCOP_DB . 'finascop_stock_branch', $branchStockRateData1, 'update', "stit_ID = {$item_id} and br_Id = {$br_id}");
        }


        $gridData = json_decode($_POST['party_data'], true);
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        $itemID = $_POST['ItemId'];
        $del_query = "delete from " . FINASCOP_DB . " finascop_stock_branch_rate where stbr_id = '$branch_id' and stit_id = '$itemID' ";
        $db->query($del_query);
        foreach ($gridData as $key => $val) {
            $branchStockRateData2 = array(
                "stit_id" => $_POST['ItemId'],
                "stbr_id" => $_SESSION['admin']->finascop_current_branch_id,
                "stpa_id" => $val['partyId'],
                "stbp_Rate" => $val['rate']
            );
            $status = $db->perform(FINASCOP_DB . 'finascop_stock_branch_rate', $branchStockRateData2);
        }

        $status = $db->query('commit');
        if ($status == 1) {
            echo '{"success":true,"msg":"Data saved successfully."}';
            exit;
        } else {
            echo '{"success":false,"msg":"Error while saving data."}';
            exit;
        }

        break;

    case 'getBranchStockRate_EditData':
        $id = $_POST['id'];
        $br_id = $_SESSION['admin']->finascop_current_branch_id;
        $qry = "SELECT  stbr_CommonRate AS commonRate ,stbr_MinStock AS min_stock,stbr_CurrentStock AS curr_stock,"
                . "stbr_CurrentStock AS hide_curr_stock, stbr_updated_on as integrity_key"
                . " FROM " . FINASCOP_DB . " finascop_stock_branch  WHERE stit_ID = '$id' AND br_Id = '$br_id'";
        $form_data = $db->getFromDB($qry, true);

        $qry = "SELECT stpa_id as partyId, (SELECT CONCAT(b.stpa_Fname,' ' ,b.stpa_Lname) FROM " . FINASCOP_DB . " finascop_stock_party b WHERE b.stpa_id = a.stpa_id) as party,a.stbp_Rate as rate"
                . " FROM " . FINASCOP_DB . " finascop_stock_branch_rate a WHERE a.stit_id = '$id' AND a.stbr_id = '$br_id' ";
        $grid_data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($form_data) . ',"gridData":' . json_encode($grid_data) . '}';
        break;

    case 'savePurchaseEnabledStatus':
        $data = array(
            "stit_PurchaseEnabled" => $_POST['stit_PurchaseEnabled']
        );
        $cond = " stit_ID = " . $_POST['ItemId'];
        $status = $db->perform(FINASCOP_DB . 'finascop_stock_itemmaster', $data, 'update', $cond);
        if ($status == true) {
            echo '{"success":true,"msg":"Purchase Status Updated."}';
        } else {
            echo '{"success":false,"msg":"Purchase Status Updation failed,"}';
        }
        break;

    case 'saveTangibleEnabledStatus':
        $data = array(
            "stit_Tangible" => $_POST['stit_Tangible']
        );
        $cond = " stit_ID = " . $_POST['ItemId'];
        $status = $db->perform(FINASCOP_DB . 'finascop_stock_itemmaster', $data, 'update', $cond);
        if ($status == true) {
            echo '{"success":true,"msg":"Tangible Status Updated."}';
        } else {
            echo '{"success":false,"msg":"Tangible Status Updation failed,"}';
        }
        break;

    case 'listStockRegisterData':
        $sourceValue = $_POST['sourceValue'];
        $viewClosed = ($_POST['viewClosed'] == 'true' ? 1 : 0);
        $fromDate = $_POST['fromDate'];
        $toDate = $_POST['toDate'];

        $con_source = "";
        $con_viewClosed = "";
        $con_date = "";

        if ($sourceValue != '') {
            $con_source = " AND fsr.stre_isPurchase = '{$sourceValue}'";
        }
        if ($viewClosed == 1) {
            $con_viewClosed = " AND stre_ApprovedBy != 0";
        }
//        if ($viewClosed == 0) {
//            $con_viewClosed = " AND stre_ApprovedBy = 0";
//        }
        if ($fromDate != '' && $toDate != '') {

            $fromdate = explode('/', $_POST['fromDate']);

            $todate = explode('/', $_POST['toDate']);

            $filter_FromDate = date("Y-m-d", mktime(0, 0, 0, $fromdate[1], $fromdate[0], $fromdate[2]));

            $filter_ToDate = date("Y-m-d", mktime(0, 0, 0, $todate[1], $todate[0], $todate[2]));

            $con_date = " AND fsr.stre_Date BETWEEN '$filter_FromDate' AND  '$filter_ToDate'";
        }
        $curr_branch = $_SESSION['admin']->finascop_current_branch_id;
        $qry = "SELECT  fsr.stre_InvNo AS stregInvNo,fsr.stre_Date AS stregInvDate,GROUP_CONCAT(stit_ID) AS stregItems,"
                . "(SELECT fpii.paii_itemQty FROM " . FINASCOP_DB . "finascop_purchase_invoice_items fpii WHERE fpii.puen_Id = fsr.stre_RefInvId "
                . "AND fpii.paii_itemID = fsr.stit_ID UNION SELECT fpri.purd_itemReturnedQty "
                . "FROM " . FINASCOP_DB . "finascop_purchase_return_items fpri WHERE fpri.pure_Id = fsr.stre_RefInvId AND fpri.purd_itemID = fsr.stit_ID "
                . "UNION SELECT fsri.sard_itemReturnedQty FROM " . FINASCOP_DB . "finascop_sales_return_items fsri "
                . "WHERE fsri.sare_Id = fsr.stre_RefInvId AND fsri.sard_itemID = fsr.stit_ID) AS stregCount,"
                . "(IF (fsr.stre_isPurchase = 1 ,'Purchase','Sales Return')) AS stregSource,"
                . "fsr.stre_ApprovedBy AS approved"
                . " FROM " . FINASCOP_DB . " finascop_stock_register fsr  WHERE fsr.br_id = '{$curr_branch}'"
                . "{$con_source}{$con_viewClosed}{$con_date} GROUP BY fsr.stre_InvNo";

        $data = $db->getMultipleData($qry, true);

        $countQuery = "SELECT COUNT(*) FROM " . FINASCOP_DB . "finascop_stock_register a WHERE a.br_id= '{$curr_branch}'";
        $count = $db->getItemFromDB($countQuery);
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'listViewApproveItemDetail':
        $stregInvNo = $_POST['stregInvNo'];
        $itemIds = explode(',', $_POST['stregItems']);
        $curr_branch = $_SESSION['admin']->finascop_current_branch_id;
        // print_r($itemIds);
        $data = array();
        foreach ($itemIds as $key) {

            $qry = "SELECT fsr.stre_InvNo  AS stregApproveNo,"
                    . "(SELECT fsi.stit_itemId FROM " . FINASCOP_DB . "finascop_stock_itemmaster fsi WHERE fsi.stit_ID = '{$key}') AS stregItem,"
                    . "(IF (fsr.stre_isPurchase = 1 ,'Purchase','Sales Return')) AS stregSource,"
                    . "(SELECT fsrn.sare_InvoiceNo FROM " . FINASCOP_DB . "finascop_sales_return fsrn WHERE fsrn.sare_id = fsr.stre_RefInvId "
                    . "UNION SELECT fprn.pure_InvoiceNo FROM " . FINASCOP_DB . "finascop_purchase_return fprn WHERE fprn.pure_id = fsr.stre_RefInvId "
                    . "UNION SELECT fpi.puen_InvoiceNo FROM " . FINASCOP_DB . "finascop_purchase_invoice fpi "
                    . "WHERE fpi.puen_Id = fsr.stre_RefInvId) AS stregSourceRefNo,"
                    . "(SELECT CONCAT (fup.FirstName,' ',fup.LastName) FROM " . FINASCOP_DB . "finascop_usr_profile fup "
                    . "WHERE fup.UserId = fsr.stre_ApprovedBy )AS stregApprovedBy,"
                    . "fsr.stre_ApprovedOn AS stregApprovalDate,"
                    . "(SELECT fpii.paii_itemQty FROM " . FINASCOP_DB . "finascop_purchase_invoice_items fpii WHERE fpii.puen_Id = fsr.stre_RefInvId "
                    . "AND fpii.paii_itemID = fsr.stit_ID UNION SELECT fpri.purd_itemReturnedQty "
                    . "FROM " . FINASCOP_DB . "finascop_purchase_return_items fpri "
                    . "WHERE fpri.pure_Id = fsr.stre_RefInvId AND fpri.purd_itemID = fsr.stit_ID UNION "
                    . "SELECT fsri.sard_itemReturnedQty FROM " . FINASCOP_DB . "finascop_sales_return_items fsri "
                    . "WHERE fsri.sare_Id = fsr.stre_RefInvId AND fsri.sard_itemID = fsr.stit_ID) AS stregQty "
                    . "FROM " . FINASCOP_DB . "finascop_stock_register fsr  "
                    . "WHERE fsr.br_id = '{$curr_branch}' AND fsr.stit_ID = '{$key}' AND fsr.stre_InvNo = '{$stregInvNo}'";
            $tmp_data = $db->getMultipleData($qry, true);
            $data = array_merge($data, $tmp_data);
        }

        if ($data) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }

        break;

    case 'saveApprovedItems':
        $view_approve_gridData = json_decode(stripslashes($_POST['gridData']), true);
        $UserId = $_SESSION['admin']->Finascop_UserId;
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        date_default_timezone_set('Asia/Kolkata');
        $date = date("Y-m-d");
        foreach ($view_approve_gridData as $key => $val) {

            $invNo = $val['stregApproveNo'];
            $integrity_key = sha1(microtime(true) . mt_rand(10000, 90000));

            $query = "UPDATE " . FINASCOP_DB . "finascop_stock_register SET stre_ApprovedBy = '{$UserId}',stre_ApprovedOn = '{$date}',"
                    . "stbr_updated_on = '{$integrity_key} "
                    . "WHERE stre_InvNo = '{$invNo}' AND br_id = '$branch_id'";
            $status = $db->query($query);
        }
        if ($status == true) {
            echo '{"success":true,"msg":"Approved Successfully"}';
        } else {
            echo '{"success":false,"msg":"Approval failed,"}';
        }
        break;

    case 'vendoritemlisting':

        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $item_name = $_POST['currentItem'];
        $item_id = $_POST['current_type'];
        //1:brand,2:item,3:Make
        switch ($item_id) {
            case 1:
                $cond = " WHERE 1=1 ";
                if ($item_name != '') {
                    $cond .= " AND stit_SKU LIKE '%{$item_name}%'";
                }
                $countQuery = "SELECT COUNT(stit_itemId) FROM finascop_stock_itemmaster  {$cond} AND isMedicine=1";
                $count = $db->getItemFromDB($countQuery);

                $qry = "SELECT stit_itemName,stit_ID,stit_brand_name as brand,stit_SKU,stit_quantity,least_package_type_name  FROM finascop_stock_itemmaster {$cond} AND isMedicine=1 AND stit_status = 1 ORDER BY stit_SKU ASC";
                $data = $db->getMultipleData($qry, true);
                break;
            case 2:
                $cond = " WHERE 1=1 ";
                if ($item_name != '') {
                    $cond .= " AND stit_SKU  LIKE '%{$item_name}%'";
                }
                $countQuery = "SELECT COUNT(stit_itemId) FROM finascop_stock_itemmaster  {$cond} AND isMedicine=0";
                $count = $db->getItemFromDB($countQuery);

                $qry = "SELECT stit_itemName,stit_ID,stit_brand_name as brand,stit_SKU,stit_quantity,least_package_type_name  FROM finascop_stock_itemmaster {$cond} AND isMedicine=0 AND stit_status = 1  ORDER BY stit_SKU ASC";
                $data = $db->getMultipleData($qry, true);
                break;
            case 3:
                $cond = " WHERE 1=1 ";
                if ($item_name != '') {
                    $cond .= " AND stit_SKU LIKE '%{$item_name}%'";
                }
                $countQuery = "SELECT COUNT(stit_itemId) FROM finascop_stock_itemmaster  {$cond} ";
                $count = $db->getItemFromDB($countQuery);

                $qry = "SELECT stit_itemName,stit_ID,stit_brand_name as brand,stit_SKU,stit_quantity,least_package_type_name  FROM finascop_stock_itemmaster {$cond} ";
                $data = $db->getMultipleData($qry, true);
                break;
        }
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'saveitemvendor':
        $itemar = $_POST['itemarr'];
        $stpa_id = $_POST['cid'];
        $itemType = $_POST['itemtype'];

        $itemdecode = json_decode($itemar);
        // print_r($itemdecode);
        $itemcount = count($itemdecode);
        //exit;
        for ($i = 0; $i < $itemcount; $i++) {


            $data = array(
                "stpa_id" => $stpa_id,
                "stit_id" => $itemdecode[$i],
                "stit_type" => $itemType
            );
            $itemdup = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_party_items WHERE stpa_id = {$stpa_id} AND stit_id = {$itemdecode[$i]} AND stit_type = {$itemType}");
            if ($itemdup == 0) {
                $status = $db->perform(FINASCOP_DB . 'finascop_stock_party_items', $data);
            }
        }

        if ($status) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;

    case 'EditVendorDetails':
        //print_r($_POST);
        $fname = addslashes($_POST['stpa_Fname']);
        $lname = addslashes($_POST['stpa_Lname']);
        $data = array(
            "stpa_Fname" => !empty($_POST['stpa_Fname']) ? $_POST['stpa_Fname'] : '',
            "stpa_Lname" => !empty($_POST['stpa_Lname']) ? $_POST['stpa_Lname'] : '',
            "stpa_Address" => !empty($_POST['stpa_Address']) ? $_POST['stpa_Address'] : '',
            "stpa_City" => !empty($_POST['stpa_City']) ? $_POST['stpa_City'] : '',
            "stpa_PINCODE" => !empty($_POST['stpa_PINCODE']) ? $_POST['stpa_PINCODE'] : '',
            "stpa_GSTIN" => !empty($_POST['stpa_GSTIN']) ? $_POST['stpa_GSTIN'] : '',
            "stpa_ContactPerson" => !empty($_POST['stpa_ContactPerson']) ? $_POST['stpa_ContactPerson'] : '',
            "stpa_MobileNo" => !empty($_POST['stpa_MobileNo']) ? $_POST['stpa_MobileNo'] : '',
            "stpa_Email" => !empty($_POST['stpa_Email']) ? $_POST['stpa_Email'] : '',
            "stpa_PanNo" => !empty($_POST['stpa_PanNo']) ? $_POST['stpa_PanNo'] : '',
            "dst_Id" => !empty($_POST['c_district']) ? $_POST['c_district'] : 0,
            //"br_Id" => $_POST['state'],
            "stpa_visitFrequency" => $_POST['visit_frequency'],
            "stpa_oncallDelivery" => $_POST['oncall_delivery'],
            "br_id" => $_SESSION['admin']->finascop_current_branch_id,
            "stpa_IsVendor" => ( $_POST['partytype'] == 1 ? 1 : 0),
            "stpa_paymentTerms" => $_POST['paymentTerms'],
            "stpa_dlno1" => !empty($_POST['stpa_dlno1']) ? $_POST['stpa_dlno1'] : '',
            "stpa_dlno2" => !empty($_POST['stpa_dlno2']) ? $_POST['stpa_dlno2'] : '',
            "stpa_fssaino" => !empty($_POST['stpa_fssaino']) ? $_POST['stpa_fssaino'] : '',
                /* "deliverMode_stdDelivery" => !empty($_POST['deliverMode_stdDelivery']) ? $_POST['deliverMode_stdDelivery'] : 0,
                  "asctedbrach_stdDelivery" => !empty($_POST['asctedbrach_stdDelivery']) ? $_POST['asctedbrach_stdDelivery'] : 0,
                  "asctedbrach_cpr" => !empty($_POST['asctedbrach_cpr']) ? $_POST['asctedbrach_cpr'] : 0,
                  "deliverMode_cpr" => !empty($_POST['deliverMode_cpr']) ? $_POST['deliverMode_cpr'] : 0,
                  "deliveryRule_courier" => !empty($_POST['deliveryRule_courier']) ? $_POST['deliveryRule_courier'] : 0,
                  "deliveryRule_express" => !empty($_POST['deliveryRule_express']) ? $_POST['deliveryRule_express'] : 0,
                  "deliveryRule_slotted" => !empty($_POST['deliveryRule_slotted']) ? $_POST['deliveryRule_slotted'] : 0,
                  "stpa_longitude" => $_POST['stpa_longitude'],
                  "stpa_latitude" => $_POST['stpa_latitude'],
                  "packMode_std" => !empty($_POST['packMode_std']) ? $_POST['packMode_std'] : 0,
                  "asctedbrach_stdPack" => !empty($_POST['asctedbrach_stdPack']) ? $_POST['asctedbrach_stdPack'] : 0, */
                //deliverMode_stdDelivery,asctedbrach_stdDelivery,deliverMode_cpr,deliveryRule_courier,deliveryRule_express,deliveryRule_slotted
        );
        // $cur_fname = $_POST['fname'];
        // $cur_lname = $_POST['lname'];
        //$data = array_filter($data);

        $state = $db->getItemfromDB("SELECT st_name FROM finascop_state WHERE st_ID = {$_POST['state']}");
        $state = strtolower($state);
        if ($state == 'kerala') {
            $data['stpa_invendor'] = 1;
        } else {
            $data['stpa_invendor'] = 0;
        }
        $db->query('begin');
        $cust_id = $_POST['customer_id'];
        if ($cust_id != 0) {
            $IsNameUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_party  WHERE concat(stpa_Fname,' ' ,stpa_Lname) = '{$fname} {$lname}' AND br_ID = {$_SESSION['admin']->finascop_current_branch_id} AND stpa_id <> {$cust_id}");
            if ($IsNameUnique > 0) {
                echo "{success: false, msg:'Already existing.'}";
                exit;
            }
            if (!empty($_POST['stpa_GSTIN'])) {
                $IsgstUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_party  WHERE stpa_GSTIN = '{$_POST['stpa_GSTIN']}' AND br_ID = {$_SESSION['admin']->finascop_current_branch_id} AND stpa_id <> {$cust_id}");
                if ($IsgstUnique > 0) {
                    echo "{success: false, msg:'GST already existing.'}";
                    exit;
                }
            }


            $IsMobileUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_party  WHERE stpa_MobileNo = {$_POST['stpa_MobileNo']} AND br_ID = {$_SESSION['admin']->finascop_current_branch_id} AND stpa_id <> {$cust_id}");
            if ($IsMobileUnique > 0) {
                echo "{success: false, msg:'Mobile number exists, please provide another mobile number to proceed.'}";
                exit;
            }
            $status = $db->perform(FINASCOP_DB . 'finascop_stock_party', $data, 'update', 'stpa_id=' . $cust_id);
            $fpoo['fpo_vendorName'] = $_POST['stpa_Fname'];
            $status = $db->perform(FINASCOP_DB . "finascop_purchase_order", $fpoo, 'update', " fpo_vendorId = {$cust_id} ");

            //FINASCOP API CALLS BEGIN
            if ($data['stpa_IsVendor'] === 1) {

                $FinascopWC = new FinascopWalletClient(FINASCOPAPIDOMAIN);
                $RefIDs['companyApiKey'] = $db->getItemFromDB("SELECT comp_ReferenceId FROM finascop_company WHERE comp_id ={$_SESSION['admin']->finascop_current_company_id}");
                $RefIDs['groupReferenceId'] = SUNDRYCREDITORGRP;
                $RefIDs['branchApiKey'] = $db->getItemFromDB("SELECT br_ReferenceId FROM finascop_branch WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");
                //print_r($RefIDs);

                $ReferenceId = $db->getItemFromDB("SELECT accled_ReferenceId FROM finascop_stock_party WHERE stpa_id = {$cust_id}");
                if (empty($ReferenceId)) {
                    $returned = $FinascopWC->createLedger(time(), $data['stpa_Fname'], $data['stpa_MobileNo'], $RefIDs, $credit_limit = 0);
                    $result = json_decode($returned, true);
                    $ledger['accled_ReferenceId'] = $result['ledgerID'];
                    $status = $db->perform(FINASCOP_DB . 'finascop_stock_party', $ledger, 'update', 'stpa_id=' . $cust_id);
                } else {
                    $returned = $FinascopWC->editLedger(time(), $ReferenceId, $data['stpa_Fname'], $data['stpa_MobileNo'], $RefIDs, $credit_limit = 0);
                }

                $result = json_decode($returned, true);
                if (array_key_exists('success', $result) && $result['success'] == true) {
                    $data['accled_ReferenceId'] = $result['ledgerID'];
                } else {
                    echo "{'success':'false','msg':'Failed to create ledger.{$result['error']}'}";
                    exit(1);
                }
            }
            //FINASCOP API CALLS END 
        } else {
            $IsNameUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_party  WHERE concat(stpa_Fname,' ' ,stpa_Lname) = '{$fname} {$lname}'");
            if ($IsNameUnique > 0) {
                echo "{success: false, msg:'Customer already existing.'}";
                exit;
            }
            if (!empty($_POST['stpa_GSTIN'])) {
                $IsgstUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_party  WHERE stpa_GSTIN = '{$_POST['stpa_GSTIN']}'");
                if ($IsgstUnique > 0) {
                    echo "{success: false, msg:'GST already existing.'}";
                    exit;
                }
            }


            $IsMobileUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_party  WHERE stpa_MobileNo = {$_POST['stpa_MobileNo']} ");
            if ($IsMobileUnique > 0) {
                echo "{success: false, msg:'Mobile number exists, please provide another mobile number to proceed.'}";
                exit;
            }
            //FINASCOP API CALLS BEGIN
            if ($data['stpa_IsVendor'] === 1) {

                $FinascopWC = new FinascopWalletClient(FINASCOPAPIDOMAIN);
                $RefIDs['companyApiKey'] = $db->getItemFromDB("SELECT comp_ReferenceId FROM finascop_company WHERE comp_id ={$_SESSION['admin']->finascop_current_company_id}");
                $RefIDs['groupReferenceId'] = SUNDRYCREDITORGRP;
                $RefIDs['branchApiKey'] = $db->getItemFromDB("SELECT br_ReferenceId FROM finascop_branch WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");


                $returned = $FinascopWC->createLedger(time(), $data['stpa_Fname'], $data['stpa_MobileNo'], $RefIDs, $credit_limit = 0);

                $result = json_decode($returned, true);
                if (array_key_exists('success', $result) && $result['success'] == true) {
                    $data['accled_ReferenceId'] = $result['ledgerID'];
                } else {
                    echo "{'success':'false','msg':'Failed to create ledger.{$result['error']}'}";
                    exit(1);
                }
            }
            //FINASCOP API CALLS END            
            $status = $db->perform(FINASCOP_DB . "finascop_stock_party", $data);
        }
        $status = $db->query('commit');

        if ($status == 1) {
            echo "{success: true,msg:'Vendor details has been saved successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }


        break;
    case 'editFormDataLoad' :
        $cust_id = $_POST['customerId'];
        if ($cust_id != 0) {

            $qry = "SELECT stpa_id AS customerId, stpa_Fname AS stpa_Fname,stpa_Lname AS stpa_Lname,stpa_Address AS stpa_Address,stpa_ContactPerson AS stpa_ContactPerson,stpa_MobileNo AS stpa_MobileNo,stpa_Email AS stpa_Email,stpa_PanNo AS stpa_PanNo ,stpa_City AS stpa_City,stpa_PINCODE AS stpa_PINCODE,stpa_GSTIN AS stpa_GSTIN,
        (SELECT st_name FROM finascop_state b INNER JOIN  finascop_district d ON b.st_ID = d.st_Id WHERE d.dst_Id = a.dst_Id)AS st_name,stpa_paymentTerms,
        (SELECT b.st_ID FROM finascop_state b INNER JOIN finascop_district d ON b.st_ID = d.st_Id WHERE d.dst_Id = a.dst_Id)AS st_id,
        (SELECT c.dst_Id FROM  finascop_district c WHERE c.dst_Id = a.dst_Id )AS dst_Id,
        (SELECT br_Name FROM  finascop_branch WHERE br_ID = a.asctedbrach_stdDelivery )AS asctedbrach_stdName,
        (SELECT br_Name FROM  finascop_branch WHERE br_ID = a.asctedbrach_cpr )AS asctedbrach_cprName,
        (SELECT dst_Name FROM  finascop_district c WHERE c.dst_Id = a.dst_Id )AS dst_Name,stpa_visitFrequency as visit_frequency,stpa_oncallDelivery as oncall_delivery,stpa_dlno1,stpa_dlno2,asctedbrach_cpr,
        stpa_fssaino,deliverMode_stdDelivery,asctedbrach_stdDelivery,deliverMode_cpr,deliveryRule_courier,deliveryRule_express,deliveryRule_slotted,stpa_latitude,stpa_longitude,packMode_std,asctedbrach_stdPack,
        (SELECT br_Name FROM  finascop_branch WHERE br_ID = a.asctedbrach_stdPack )AS asctedbrach_stdPackName 
        FROM  finascop_stock_party a WHERE stpa_id = {$cust_id}";
            $results = $db->getFromDB($qry, true);
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;

    case 'listitemvendor':
        $data = $_POST;
        $rec_sort = empty($data['sort']) ? 'stpi_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
//        $filter_part = ' 1=1';
//
//        if (isset($data['filter'])) {
//
//            foreach ($data['filter'] as $key => $val) 
//            {
//                if ($val['field'] == 'itemName') {
//                    $itemName = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(stit_id),0) FROM finascop_stock_party_items WHERE stit_id IN (SELECT GROUP_CONCAT(stit_ID) FROM finascop_stock_itemmaster WHERE stit_SKU LIKE '{$val['data']['value']}%')");
//                    $filter_part .= " AND stit_id IN({$itemName}) ";
//                } else if ($val['field'] == 'itemType') {
//                    $itemType = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(stpi_id),0) FROM finascop_stock_party_items WHERE stit_type LIKE '{$val['data']['value']}%' ");
//                    $filter_part .= " AND stit_type IN({$itemType}) ";
//                }  else {
//                    $filter_part .= " and " . $val['field'] . " LIKE ' " . $val['data']['value'] . "%' ";
//                }
//            }
//        }


        $customerId = $_POST['cust_id'];
        if (customerId != '') {

            $qry = "SELECT stpi_id,stit_type,IF(stit_type = 1,'Medicine','Product') AS itemType,finascop_stock_itemmaster.stit_id AS itemId,stit_SKU AS itemName,stit_brand_name,stit_quantity,least_package_type_name 
                    FROM finascop_stock_party_items INNER JOIN finascop_stock_itemmaster ON finascop_stock_itemmaster .stit_id= finascop_stock_party_items.stit_id WHERE stpa_id='{$customerId}' ORDER BY $rec_sort $rec_sort_dir";
            //. "AND  {$filter_part} ORDER BY $rec_sort $rec_sort_dir";
//                  echo $qry;
//                   exit;

            $data = $db->getMultipleData($qry, true);

            $countQuery = "SELECT COUNT(*) FROM finascop_stock_party_items s  WHERE s.stpa_id='{$customerId}'";
            $count = $db->getItemFromDB($countQuery);


            if (!empty($data)) {
                echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
            } else {
                echo '{"totalCount":"0","data":[]}';
            }
        }
        break;

    case 'deleteVendorItemFromgrid':
        $currentItem = $_POST['current_id'];
        $current_customer_id = $_POST['current_cust'];
        //echo("currentItem".$currentItem);
        $delquery = "DELETE FROM finascop_stock_party_items  WHERE stpi_id = {$currentItem}";
        $status = $db->query($delquery);

        if ($status) {
            echo "{success: true,msg:'Deleted Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while deleting data' }}";
        }


        break;
    case 'filterItem':
        $item_name = $_POST['currentItem'];
        $item_id = $_POST['current_type'];

        $countQuery = "SELECT COUNT(stit_itemId) FROM finascop_stock_itemmaster  WHERE stit_itemId LIKE '%{$item_name}%'";
        $count = $db->getItemFromDB($countQuery);

        $qry = $qry = "SELECT stit_itemId,stit_ID  FROM finascop_stock_itemmaster WHERE stit_itemId LIKE '%{$item_name}%'";
        $data = $db->getMultipleData($qry, true);

        if (!empty($data)) {
            echo '{Success:true,"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;
    case 'checkisHomeItemCount':
        $scount = $db->getItemFromDB("SELECT COUNT(*) FROM " . FINASCOP_DB . " finascop_stock_itemmaster WHERE product_is_home = 'Yes'");
        echo "{success:true,valid:true,count:'" . $scount . "'}";
        break;
    case 'ishomeactiveitem':

        $ID = intval($_POST['product_id']);
        $db->query("begin");
        $db->query(
                "update `" . FINASCOP_DB . "finascop_stock_itemmaster` set `product_is_home`=if(`product_is_home`='Yes', 'No', 'Yes')"
                . " where stit_ID = {$ID}"
        );

        $db->query("commit;");
        $scount = $db->getItemFromDB("SELECT product_is_home FROM " . FINASCOP_DB . " finascop_stock_itemmaster WHERE stit_ID = {$ID}");
        echo "{success:true,valid:true,status:'" . $scount . "'}";
        break;
    case 'getPaymentTerms':
        $qry = "select ptc_id,ptc_name from " . FINASCOP_DB . "retaline_paymtTermscfg order by ptc_id";
        $data = $db->getMultipleData($qry, true);
        //echo json_encode($data);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getassociatedBranch':
        $long = $_POST['stpa_longitude'];
        $lat = $_POST['stpa_latitude'];
        $MAX_VENDOR_BRANCH_DISTANCE = $db->getItemFromDB("select cfg_Value  from sys_configuration where  cfg_Name ='MAX_VENDOR_BRANCH_DISTANCE'");
        $locs = new cgoGeoUtilities();
        $locdata = $locs->getNearestAerialBranches($long, $lat, $MAX_VENDOR_BRANCH_DISTANCE, false);
        $brIDs = implode(',', array_column($locdata, 'br_ID'));

        $qry = "select br_ID,br_Name from " . FINASCOP_DB . "finascop_branch WHERE br_status = 'Active' AND br_StoreType <> 'Dealer' and br_ID IN ({$brIDs}) order by br_Name";
        $data = $db->getMultipleData($qry, true);
        //echo json_encode($data);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getDeliveryRules':
        $type = $_POST['deliveryMode'];
        $qry = "select rdr_id ,rdr_ruleName from retaline_delivery_rules where rdr_ruleFor = 1 AND rdr_deliveryMode = {$type} order by rdr_ruleName";
        $data = $db->getMultipleData($qry, true);
        //echo json_encode($data);
        echo '{"success":true,"data":' . json_encode($data) . '}';

        break;
    case 'updateDeliveryModes':
        $data = array(
            "deliverMode_stdDelivery" => !empty($_POST['deliverMode_stdDelivery']) ? $_POST['deliverMode_stdDelivery'] : 0,
            "asctedbrach_stdDelivery" => !empty($_POST['asctedbrach_stdDelivery']) ? $_POST['asctedbrach_stdDelivery'] : 0,
            "asctedbrach_cpr" => !empty($_POST['asctedbrach_cpr']) ? $_POST['asctedbrach_cpr'] : 0,
            "deliverMode_cpr" => !empty($_POST['deliverMode_cpr']) ? $_POST['deliverMode_cpr'] : 0,
            "deliveryRule_courier" => !empty($_POST['deliveryRule_courier']) ? $_POST['deliveryRule_courier'] : 0,
            "deliveryRule_express" => !empty($_POST['deliveryRule_express']) ? $_POST['deliveryRule_express'] : 0,
            "deliveryRule_slotted" => !empty($_POST['deliveryRule_slotted']) ? $_POST['deliveryRule_slotted'] : 0,
            "packMode_std" => !empty($_POST['packMode_std']) ? $_POST['packMode_std'] : 0,
            "stpa_isLiveStock" => $_POST['stpa_isLiveStock']
        );
        $db->query('begin');
        $stpa_id = $_POST['stpa_id'];
        if ($stpa_id > 0) {
            $status = $db->perform(FINASCOP_DB . 'finascop_stock_party', $data, 'update', 'stpa_id=' . $stpa_id);
        }
        $status = $db->query('commit');

        if ($status == 1) {
            echo "{success: true,msg:'Data updated'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'loadVendorDeliveryRules':
        $stpa_id = isset($_POST['stpa_id']) ? intval($_POST['stpa_id']) : 0;
        if ($stpa_id) {
            _loadRecordJson("SELECT  deliverMode_stdDelivery,asctedbrach_stdDelivery,asctedbrach_cpr,deliverMode_cpr,deliveryRule_courier,deliveryRule_express,deliveryRule_slotted,packMode_std,"
                    . "(SELECT br_Name FROM  finascop_branch WHERE br_ID = asctedbrach_stdDelivery )AS asctedbrach_stdName,stpa_isLiveStock,
        (SELECT br_Name FROM  finascop_branch WHERE br_ID = asctedbrach_cpr )AS asctedbrach_cprName FROM finascop_stock_party WHERE stpa_id = {$stpa_id}");
        }
        break;
    case 'renewBranchAPIKey':
        $data = $_POST;
        $update['accled_ReferenceId'] = getNewFinascopApiKey();
        $status = $db->perform("finascop_stock_party", $update, "update", "stpa_id={$data['br_ID']}");
        if ($status) {
            echo "{success: true, newBrAPIKey :'" . $update['br_ReferenceID'] . "'}";
        } else {
            echo "{success: false, errors:  'FINASCOP: Error occured while renewing API Key' }";
        }
        break;
}
