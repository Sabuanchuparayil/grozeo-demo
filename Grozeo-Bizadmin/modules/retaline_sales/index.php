<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");
require_once(INCLUDE_PATH . "/finascop_wallet_client.php");

switch ($op) {

    case 'createTransferOrder':

        $db->query('begin');
        $date = date('Y-m-d H:i:s');

        $query = "SELECT b2b_Customer_ID,br_ID,bbso_HandlingCharges,bbso_CGSTVal,bbso_SGSTVal,bbso_InvValBtax,bbso_InvValBtax,bbso_SOValue
        FROM retaline_B2B_SalesOrder WHERE bbso_id = " . intval($_POST['bbso_id']) . "";
        $B2BSOData = $db->getFromDB($query, true);
        $custGST = $db->getItemFromDB("SELECT b2b_Customer_gst FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = {$B2BSOData['b2b_Customer_ID']}");
        $listQuery = "SELECT b2bso_itemid,b2bso_itemqty,b2bso_itemmrp,b2bso_gst,b2bso_cgst_percent,b2bso_sgst_percent,
        b2bso_cgst_value,b2bso_sgst_value,b2bso_amount_btax,b2bso_amount,b2bso_netamount
         FROM retaline_B2B_SalesOrderDetails WHERE bbso_id = " . intval($_POST['bbso_id']) . "";
        $itemDetails = $db->getMultipleData($listQuery, true);


        $data['fsto_source'] = $B2BSOData['br_ID'];
        $data['fsto_destination'] = $B2BSOData['b2b_Customer_ID'];
        $data['fsto_handlingcharge'] = $B2BSOData['bbso_HandlingCharges'];
        $data['fsto_cgstval'] = round($B2BSOData['bbso_CGSTVal'], 2);
        $data['fsto_sgstval'] = round($B2BSOData['bbso_SGSTVal'], 2);
        $data['fsto_amtbeforetax'] = round($B2BSOData['bbso_InvValBtax'], 2);
        $data['fsto_amtaftertax'] = round($B2BSOData['bbso_InvValBtax'], 2);
        $data['fsto_netamount'] = round($B2BSOData['bbso_SOValue'], 2);

        $data['fsto_sourcetype'] = 1;
        $data['fsto_destinationtype'] = 2;
        $tdy = date("Y-m-d") . " 00:00:00";
        $maxId = $db->getItemFromDB("select right(fsto_uid,3)*1 as fsto_uid  from `finascop_stock_transfer_order` where `fsto_source` = {$data['fsto_source']} and `fsto_createdOn` between '{$tdy}' and '{$date}' order by `fsto_id` desc limit 0,1");
        $br_key = $db->getItemFromDB("SELECT br_key FROM finascop_branch WHERE br_ID={$data['fsto_source']}");
        $uid_max = getNewTONumber($data['fsto_source']);
        $data['fsto_uid'] = $uid_max;
        $data['fsto_ordertype'] = 2;
        $data['fsto_type'] = 0;
        $data['fstr_id'] = $_POST['bbso_id'];
        $data['fsto_createdOn'] = $date;
        $data['fsto_initiatedBy'] = $_SESSION['admin']->finascop_current_branch_id;
        $data['fsto_createdBy'] = $_SESSION['admin']->UserId;
        $data['fsto_status'] = 6;

        $status = $db->perform('finascop_stock_transfer_order', $data);
        $lastId = $db->insert_id();

        if ($lastId) {

            foreach ($itemDetails as $key => $item) {
                $fstro_gst_value = $item['b2bso_cgst_value'] + $item['b2bso_sgst_value'];
                $trODetailss['fsto_ItemId'] = $item['b2bso_itemid'];
                $trODetailss['fsto_ItemQty'] = $item['b2bso_itemqty'];
                $trODetailss['fstro_ItemMRP'] = $item['b2bso_itemmrp'];
                $trODetailss['fstro_gst_percent'] = round($item['b2bso_gst'], 2);
                $trODetailss['fstro_gst_value'] = round($fstro_gst_value, 2);
                $trODetailss['fstro_cgst_percent'] = round($item['b2bso_cgst_percent'], 2);
                $trODetailss['fstro_sgst_percent'] = round($item['b2bso_sgst_percent'], 2);
                $trODetailss['fstro_cgst_value'] = round($item['b2bso_cgst_value'], 2);
                $trODetailss['fstro_sgst_value'] = round($item['b2bso_sgst_value'], 2);
                $trODetailss['fstro_totamtbeforetax'] = round($item['b2bso_amount_btax'], 2);
                $trODetailss['fstro_totamtaftertax'] = round($item['b2bso_amount'], 2);
                $trODetailss['fstro_kfc_percent'] = 0;
                $trODetailss['fstro_kfc_value'] = 0;

                $items = $db->getFromDb("SELECT item_weight,stit_item_volume 
                FROM finascop_stock_itemmaster where stit_itemId = {$trODetailss['fsto_ItemId']}", true);
                $trODetailss['fsto_ItemWeight'] = $items['item_weight'] * $trODetailss['fsto_ItemQty'];
                $trODetailss['fsto_ItemVolume'] = $items['stit_item_volume'] * $trODetailss['fsto_ItemQty'];
                $trODetailss['fsto_uid'] = $data['fsto_uid'];
                $trODetailss['fsto_id'] = $lastId;

                $trODetailss['fstro_createdBy'] = $_SESSION['admin']->UserId;
                $trODetailss['fstro_createdOn'] = $date;
                $status = $db->perform('finascop_stock_transfer_order_details', $trODetailss);
            }
            $status = $db->executeSafe("UPDATE retaline_B2B_SalesOrder SET status_id = 2 WHERE bbso_id = ?", "i", [$_POST['bbso_id']]);
        }


        $status = $db->query('commit');
        if ($status) {
            $msg = "Order transfered succesfully";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
        }
        break;

    case 'setB2BSchemeToCustomer':
        $data['rbsch_name'] = $_POST['rbsch_name'];
        $status = $db->perform('retaline_B2Bcustomer', $data, 'update', "b2b_Customer_ID = " . intval($_POST['b2b_Customer_ID']));
        if ($status == 1) {
            $msg = "'B2B Sales Scheme applied successfully.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error while Sales Scheme to customer.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'getSalesSchemes':
        $data = $_POST;
        $countDataQuery = "SELECT COUNT(DISTINCT(rbsch_name)) FROM retaline_B2BScheme WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}";
        $listQuery = "SELECT DISTINCT(rbsch_name) as rbsch_name FROM retaline_B2BScheme WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}";
        $db->printGridJson($countDataQuery, $listQuery);
        break;
    case 'pushB2BPOtoPackSure':
        $bbso_id = $_POST['bbso_id'];

        $db->query("UPDATE retaline_B2B_SalesOrder SET status_id = 7 WHERE bbso_id = {$bbso_id}");

        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'B2B Sales Order Items pushed successfully to Assign Carting.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error while pusing B2B Sales Order Items to Assign Carting.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;

    case 'cancelTempB2BSR':
        $data = $_POST;
        $delquery = "DELETE FROM retaline_B2B_SalesRequest_temp WHERE b2bsr_UniqueID = '{$data['b2bsr_UniqueID']}'";
        $status = $db->query($delquery);

        if ($status == 1) {
            echo "{success: true,msg:'Deleted Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while deleting data' }}";
        }
        break;
    case 'getItemBatchDetails':
        $data = $_POST;

        $isStockEnough = $db->getItemFromDB("SELECT COALESCE((IF( (item_count - (SELECT COALESCE((SELECT SUM(fsb.count) AS items_blocked 
        FROM finascop_stock_blocked fsb 
        WHERE fsb.item_id = fbi.stit_id AND  fsb.branch_id = fbi.branch_id),0) AS items_blocked) >= {$data['itemQty']}) ,1, 0)),0) AS isStockEnough
        FROM finascop_stock_branch_inventory fbi WHERE fbi.stit_id = {$data['itemID']} AND fbi.branch_id = {$_SESSION['admin']->finascop_current_branch_id}");
        if ($isStockEnough <> 1) {
            $result['msg'] = "Item stock not enough.";
            echo "{success: false,record:" . json_encode($result) . "}";
            exit(1);
        }

        $query = "SELECT fsib.fsbg_id, fsbg_mrp,fsbg_epr FROM finascop_stock_branch_inventory fsbi "
                . " INNER JOIN finascop_stock_item_batch_group fsib "
                . " ON fsbi.fsbg_id = fsib.fsbg_id WHERE item_count >0 and fsbi.stit_id = '{$data['itemID']}' "
                . " AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} ORDER BY fsbg_epr ASC, fsbg_expirydate ASC LIMIT 0,1";
        $fsbg_data = $db->getFromDB($query, true);
        $rbsch_name = $data['rbsch_name'];
        if (!empty($fsbg_data)) {

            $rbsch_data = null;
            if (!empty($rbsch_name)) {
                $query = "SELECT rbsch_id,"
                        . "CASE "
                        . "WHEN (rbsch_FromQtyL1 <= {$data['itemQty']} AND rbsch_ToQtyL1 > {$data['itemQty']}) THEN rbsch_CompL1 "
                        . "WHEN (rbsch_FromQtyL2 <= {$data['itemQty']} AND rbsch_ToQtyL2 > {$data['itemQty']}) THEN rbsch_CompL2 "
                        . "WHEN (rbsch_FromQtyL3 <= {$data['itemQty']} AND rbsch_ToQtyL3 > {$data['itemQty']}) THEN rbsch_CompL3 "
                        . "WHEN (rbsch_FromQtyL4 <= {$data['itemQty']} AND rbsch_ToQtyL4 > {$data['itemQty']}) THEN rbsch_CompL4 "
                        . "WHEN (rbsch_FromQtyL5 <= {$data['itemQty']} AND rbsch_ToQtyL5 > {$data['itemQty']}) THEN rbsch_CompL5 "
                        . "END AS company,"
                        . "CASE "
                        . "WHEN (rbsch_FromQtyL1 <= {$data['itemQty']} AND rbsch_ToQtyL1 > {$data['itemQty']}) THEN rbsch_TechL1 "
                        . "WHEN (rbsch_FromQtyL2 <= {$data['itemQty']} AND rbsch_ToQtyL2 > {$data['itemQty']}) THEN rbsch_TechL2 "
                        . "WHEN (rbsch_FromQtyL3 <= {$data['itemQty']} AND rbsch_ToQtyL3 > {$data['itemQty']}) THEN rbsch_TechL3 "
                        . "WHEN (rbsch_FromQtyL4 <= {$data['itemQty']} AND rbsch_ToQtyL4 > {$data['itemQty']}) THEN rbsch_TechL4 "
                        . "WHEN (rbsch_FromQtyL5 <= {$data['itemQty']} AND rbsch_ToQtyL5 > {$data['itemQty']}) THEN rbsch_TechL5 "
                        . "END AS technology,"
                        . "CASE "
                        . "WHEN (rbsch_FromQtyL1 <= {$data['itemQty']} AND rbsch_ToQtyL1 > {$data['itemQty']}) THEN rbsch_CustL1 "
                        . "WHEN (rbsch_FromQtyL2 <= {$data['itemQty']} AND rbsch_ToQtyL2 > {$data['itemQty']}) THEN rbsch_CustL2 "
                        . "WHEN (rbsch_FromQtyL3 <= {$data['itemQty']} AND rbsch_ToQtyL3 > {$data['itemQty']}) THEN rbsch_CustL3 "
                        . "WHEN (rbsch_FromQtyL4 <= {$data['itemQty']} AND rbsch_ToQtyL4 > {$data['itemQty']}) THEN rbsch_CustL4 "
                        . "WHEN (rbsch_FromQtyL5 <= {$data['itemQty']} AND rbsch_ToQtyL5 > {$data['itemQty']}) THEN rbsch_CustL5 "
                        . "END AS b2bCustomer FROM  retaline_B2BScheme WHERE rbsch_name = '{$rbsch_name}' AND br_ID = {$_SESSION['admin']->finascop_current_branch_id} "
                        . "AND rbsch_FromMrp <=  {$fsbg_data['fsbg_mrp']} AND br_ID = {$_SESSION['admin']->finascop_current_branch_id} "
                        . "AND rbsch_ToMrp >  {$fsbg_data['fsbg_mrp']}";
                $rbsch_data = $db->getFromDB($query, true);
            }
            if (empty($rbsch_data)) {
                $query = "SELECT rbsch_id,"
                        . "CASE "
                        . "WHEN (rbsch_FromQtyL1 <= {$data['itemQty']} AND rbsch_ToQtyL1 > {$data['itemQty']}) THEN rbsch_CompL1 "
                        . "WHEN (rbsch_FromQtyL2 <= {$data['itemQty']} AND rbsch_ToQtyL2 > {$data['itemQty']}) THEN rbsch_CompL2 "
                        . "WHEN (rbsch_FromQtyL3 <= {$data['itemQty']} AND rbsch_ToQtyL3 > {$data['itemQty']}) THEN rbsch_CompL3 "
                        . "WHEN (rbsch_FromQtyL4 <= {$data['itemQty']} AND rbsch_ToQtyL4 > {$data['itemQty']}) THEN rbsch_CompL4 "
                        . "WHEN (rbsch_FromQtyL5 <= {$data['itemQty']} AND rbsch_ToQtyL5 > {$data['itemQty']}) THEN rbsch_CompL5 "
                        . "END AS company,"
                        . "CASE "
                        . "WHEN (rbsch_FromQtyL1 <= {$data['itemQty']} AND rbsch_ToQtyL1 > {$data['itemQty']}) THEN rbsch_TechL1 "
                        . "WHEN (rbsch_FromQtyL2 <= {$data['itemQty']} AND rbsch_ToQtyL2 > {$data['itemQty']}) THEN rbsch_TechL2 "
                        . "WHEN (rbsch_FromQtyL3 <= {$data['itemQty']} AND rbsch_ToQtyL3 > {$data['itemQty']}) THEN rbsch_TechL3 "
                        . "WHEN (rbsch_FromQtyL4 <= {$data['itemQty']} AND rbsch_ToQtyL4 > {$data['itemQty']}) THEN rbsch_TechL4 "
                        . "WHEN (rbsch_FromQtyL5 <= {$data['itemQty']} AND rbsch_ToQtyL5 > {$data['itemQty']}) THEN rbsch_TechL5 "
                        . "END AS technology,"
                        . "CASE "
                        . "WHEN (rbsch_FromQtyL1 <= {$data['itemQty']} AND rbsch_ToQtyL1 > {$data['itemQty']}) THEN rbsch_CustL1 "
                        . "WHEN (rbsch_FromQtyL2 <= {$data['itemQty']} AND rbsch_ToQtyL2 > {$data['itemQty']}) THEN rbsch_CustL2 "
                        . "WHEN (rbsch_FromQtyL3 <= {$data['itemQty']} AND rbsch_ToQtyL3 > {$data['itemQty']}) THEN rbsch_CustL3 "
                        . "WHEN (rbsch_FromQtyL4 <= {$data['itemQty']} AND rbsch_ToQtyL4 > {$data['itemQty']}) THEN rbsch_CustL4 "
                        . "WHEN (rbsch_FromQtyL5 <= {$data['itemQty']} AND rbsch_ToQtyL5 > {$data['itemQty']}) THEN rbsch_CustL5 "
                        . "END AS b2bCustomer FROM  retaline_B2BScheme WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id} "
                        . "AND rbsch_IsGeneral = 1 "
                        . "AND rbsch_FromMrp <=  {$fsbg_data['fsbg_mrp']} "
                        . "AND rbsch_ToMrp >  {$fsbg_data['fsbg_mrp']}";
                $rbsch_data = $db->getFromDB($query, true);

                if (empty($rbsch_data)) {
                    $msg = "'No valid B2B Scheme found! Please check your B2B Sales Schemes.'";
                    echo '{"success":false,"msg":' . $msg . '}';
                    exit(1);
                }
            }

            $result['rbsch_id'] = $rbsch_data['rbsch_id'];

            $result['fsbg_id'] = $fsbg_data['fsbg_id'];
            $result['fsbg_mrp'] = $fsbg_data['fsbg_mrp'];
            $result['fsbg_sellinprice'] = $fsbg_data['fsbg_epr'] + ($fsbg_data['fsbg_mrp'] - $fsbg_data['fsbg_epr'] ) * (($rbsch_data['company'] + $rbsch_data['technology']) / 100);
            echo "{success: true,record:" . json_encode($result) . "}";
        } else {
            $result['msg'] = "Price scheme not found";
            echo "{success: false,record:" . json_encode($result) . "}";
        }
        break;
    case 'checkDuplicateItems':
        $data = $_POST;
        if (!empty($data['salesRequestIDs'])) {
            $salesRequestIDs = json_decode(stripslashes($data['salesRequestIDs']), true);
            if (!empty($salesRequestIDs)) {
                $salesRequestIDs = implode(',', $salesRequestIDs);
            } else {
                $salesRequestIDs = "''";
            }
        } else {
            $salesRequestIDs = "''";
        }
        //print_r($salesRequestIDs);
        $listQuery = "SELECT inn.b2bsr_itemid AS b2bso_itemid, inn.b2bsr_itemname AS b2bso_itemname, inn.b2bsr_itemqty AS b2bso_itemqty FROM retaline_B2B_SalesRequestDetails inn
        WHERE b2bsr_itemid IN (SELECT b2bsr_itemid FROM  retaline_B2B_SalesRequestDetails  WHERE bbsr_id IN (" . $salesRequestIDs . ") )";
        $Duplicates = $db->getMultipleData($listQuery, true);
        if (!empty($Duplicates)) {
            echo '{"count":' . count($Duplicates) . ',"data":' . json_encode($Duplicates) . '}';
        } else {
            echo '{"count":"0","data":[]}';
        }
        break;
    case 'populateB2BSRDetailsInSO':
        $data = $_POST;
        if (!empty($data['salesRequestIDs'])) {
            $salesRequestIDs = json_decode(stripslashes($data['salesRequestIDs']), true);
            if (!empty($salesRequestIDs)) {
                $salesRequestIDs = implode(',', $salesRequestIDs);
            } else {
                $salesRequestIDs = "''";
            }
        } else {
            $salesRequestIDs = "''";
        }
        $query = "SELECT '0' AS bbso_id, '' AS bbso_SONumber,NOW() AS bbso_SODate,b2b_Customer_ID AS b2b_Customer_ID,"
                . "b2b_Customer_Name AS b2b_Customer_Name,0.00 AS bbso_SOValue,"
                . "0.00 AS bbso_HandlingCharges ,'Active' AS bbso_Active "
                . "FROM retaline_B2B_SalesRequest WHERE b2b_Customer_ID = {$data['customerID']}";
        $B2BSalesOrderData = $db->getFromDB($query, true);
        $listQuery = "SELECT '{$data['b2bso_UniqueID']}' AS b2bso_UniqueID,'{$data['customerID']}' AS b2bso_Customerid ,'0' AS bbso_id, "
                . " b2bsr_itemid AS b2bso_itemid, b2bsr_itemname AS b2bso_itemname, "
                . " b2bsr_itemqty AS b2bso_itemqty,b2bsr_itemPkg AS b2bso_itemPkg, "
                . " 0.00 AS b2bso_discountpercent,0.00 AS b2bso_discountamt,"
                . " COALESCE((SELECT IF( (item_count - (SELECT COALESCE((SELECT SUM(fsb.count) AS items_blocked FROM finascop_stock_blocked fsb"
                . " WHERE fsb.item_id = fbi.stit_id AND  fsb.branch_id = fbi.branch_id),0) AS items_blocked) >= b2bsr_itemqty) ,1, 0)"
                . " AS ss FROM finascop_stock_branch_inventory fbi"
                . " WHERE fbi.stit_id = b2bsr_itemid AND fbi.branch_id = {$_SESSION['admin']->finascop_current_branch_id}),0) AS isStockEnough,"
                . " COALESCE((SELECT fsib.fsbg_id FROM finascop_stock_branch_inventory fsbi "
                . " INNER JOIN finascop_stock_item_batch_group fsib "
                . " ON fsbi.fsbg_id = fsib.fsbg_id WHERE fsbi.stit_id = b2bso_itemid "
                . " AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} ORDER BY fsbg_mrp ASC LIMIT 0,1),0) AS fsbg_id"
                . " FROM retaline_B2B_SalesRequestDetails a WHERE bbsr_id IN (" . $salesRequestIDs . ") "
                . " AND b2bsr_createdon = (SELECT MAX(b2bsr_createdon) FROM retaline_B2B_SalesRequestDetails b"
                . " WHERE b.b2bsr_itemid = a.b2bsr_itemid AND bbsr_id IN (" . $salesRequestIDs . "))";


        $B2BSOItemDetails = $db->getMultipleData($listQuery, true);

        $rbsch_name = $db->getItemFromDB("SELECT rbsch_name FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = {$data['customerID']}");
        $NewB2BSOItemDetails = array();
        if (count($B2BSOItemDetails) > 0 && !empty($B2BSOItemDetails)) {
            foreach ($B2BSOItemDetails as $itemDetails) {
                $itemDetails['b2bso_createdon'] = date("Y-m-d H:i:s");
                $itemDetails['b2bso_createdby'] = $_SESSION['admin']->Finascop_UserId;

                $fsbgquery = "SELECT fsib.fsbg_id, fsbg_mrp,fsbg_epr FROM finascop_stock_branch_inventory fsbi "
                        . " INNER JOIN finascop_stock_item_batch_group fsib "
                        . " ON fsbi.fsbg_id = fsib.fsbg_id WHERE item_count >0 and fsbi.stit_id = {$itemDetails['b2bso_itemid']} "
                        . " AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} ORDER BY fsbg_epr ASC, fsbg_expirydate ASC LIMIT 0,1";
                $fsbg_data = $db->getFromDB($fsbgquery, true);

                //begin //get scheme and batch data inserted
                if (!empty($fsbg_data)) {
                    $rbsch_data = null;
                    if (!empty($rbsch_name)) {
                        $query = "SELECT rbsch_id,"
                                . " CASE "
                                . "WHEN (rbsch_FromQtyL1 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL1 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CompL1 "
                                . "WHEN (rbsch_FromQtyL2 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL2 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CompL2 "
                                . "WHEN (rbsch_FromQtyL3 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL3 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CompL3 "
                                . "WHEN (rbsch_FromQtyL4 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL4 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CompL4 "
                                . "WHEN (rbsch_FromQtyL5 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL5 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CompL5 "
                                . "END AS company,"
                                . "CASE "
                                . "WHEN (rbsch_FromQtyL1 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL1 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_TechL1 "
                                . "WHEN (rbsch_FromQtyL2 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL2 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_TechL2 "
                                . "WHEN (rbsch_FromQtyL3 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL3 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_TechL3 "
                                . "WHEN (rbsch_FromQtyL4 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL4 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_TechL4 "
                                . "WHEN (rbsch_FromQtyL5 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL5 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_TechL5 "
                                . "END AS technology,"
                                . "CASE "
                                . "WHEN (rbsch_FromQtyL1 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL1 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CustL1 "
                                . "WHEN (rbsch_FromQtyL2 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL2 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CustL2 "
                                . "WHEN (rbsch_FromQtyL3 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL3 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CustL3 "
                                . "WHEN (rbsch_FromQtyL4 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL4 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CustL4 "
                                . "WHEN (rbsch_FromQtyL5 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL5 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CustL5 "
                                . "END AS b2bCustomer FROM  retaline_B2BScheme WHERE rbsch_name = '{$rbsch_name}' AND br_ID = {$_SESSION['admin']->finascop_current_branch_id} "
                                . "AND rbsch_FromMrp <=  {$fsbg_data['fsbg_mrp']} "
                                . "AND rbsch_ToMrp >  {$fsbg_data['fsbg_mrp']}";
                        $rbsch_data = $db->getFromDB($query, true);
                    }

                    if (empty($rbsch_data)) {
                        $query = "SELECT rbsch_id,"
                                . " CASE "
                                . "WHEN (rbsch_FromQtyL1 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL1 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CompL1 "
                                . "WHEN (rbsch_FromQtyL2 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL2 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CompL2 "
                                . "WHEN (rbsch_FromQtyL3 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL3 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CompL3 "
                                . "WHEN (rbsch_FromQtyL4 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL4 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CompL4 "
                                . "WHEN (rbsch_FromQtyL5 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL5 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CompL5 "
                                . "END AS company,"
                                . "CASE "
                                . "WHEN (rbsch_FromQtyL1 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL1 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_TechL1 "
                                . "WHEN (rbsch_FromQtyL2 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL2 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_TechL2 "
                                . "WHEN (rbsch_FromQtyL3 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL3 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_TechL3 "
                                . "WHEN (rbsch_FromQtyL4 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL4 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_TechL4 "
                                . "WHEN (rbsch_FromQtyL5 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL5 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_TechL5 "
                                . "END AS technology,"
                                . "CASE "
                                . "WHEN (rbsch_FromQtyL1 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL1 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CustL1 "
                                . "WHEN (rbsch_FromQtyL2 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL2 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CustL2 "
                                . "WHEN (rbsch_FromQtyL3 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL3 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CustL3 "
                                . "WHEN (rbsch_FromQtyL4 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL4 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CustL4 "
                                . "WHEN (rbsch_FromQtyL5 <= {$itemDetails['b2bso_itemqty']} AND rbsch_ToQtyL5 > {$itemDetails['b2bso_itemqty']}) THEN rbsch_CustL5 "
                                . "END AS b2bCustomer FROM  retaline_B2BScheme WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id} "
                                . "AND rbsch_IsGeneral = 1 "
                                . "AND rbsch_FromMrp <=  {$fsbg_data['fsbg_mrp']} "
                                . "AND rbsch_ToMrp >  {$fsbg_data['fsbg_mrp']}";
                        $rbsch_data = $db->getFromDB($query, true);

                        if (empty($rbsch_data)) {
                            $msg = "'No valid B2B Scheme found! Please check your B2B Sales Schemes.'";
                            echo '{"success":false,"msg":' . $msg . '}';
                            exit(1);
                        }
                    }

                    $itemDetails['rbsch_id'] = $rbsch_data['rbsch_id'];

                    //end //get scheme and batch data inserted
                    //begin //itemrate, gst, netamount calculation


                    $itemDetails['b2bso_itemmrp'] = $fsbg_data['fsbg_mrp'];
                    $itemRate = $fsbg_data['fsbg_epr'] + ($fsbg_data['fsbg_mrp'] - $fsbg_data['fsbg_epr'] ) * (($rbsch_data['company'] + $rbsch_data['technology']) / 100);

                    $itemDetails['b2bso_itemrate'] = $itemRate;
                    $gstItemRate = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID ={$itemDetails['b2bso_itemid']}");
                    $itemDetails['b2bso_amount'] = $itemDetails['b2bso_itemqty'] * $itemRate;
                    $itemDetails['b2bso_gst'] = $gstItemRate;
                    $itemDetails['b2bso_netamount'] = $itemDetails['b2bso_amount'];

                    $gst_details = $db->getFromDB("SELECT fbi.stit_HSNCode AS b2bso_HSN,
                 (({$itemDetails['b2bso_amount']} * 100) / (100 + fbi.stit_GST)) AS b2bso_amount_btax,
                  (fbi.stit_GST / 2) AS b2bso_cgst_percent,(fbi.stit_GST / 2) AS b2bso_sgst_percent,
                  (fbi.stit_GST / 200 * (({$itemDetails['b2bso_amount']} * 100) / (100 + (fbi.stit_GST))) ) AS b2bso_cgst_value,
                  (fbi.stit_GST / 200 * (({$itemDetails['b2bso_amount']} * 100) / (100 + (fbi.stit_GST))) ) AS b2bso_sgst_value
                  FROM finascop_stock_itemmaster fbi WHERE fbi.stit_id = {$itemDetails['b2bso_itemid']} ", true);

                    $itemDetails = array_merge($itemDetails, $gst_details);
                } else {
                    $itemDetails['b2bso_itemmrp'] = 0.00;
                    $itemDetails['b2bso_itemrate'] = 0.00;
                    $itemDetails['b2bso_amount'] = 0.00;
                    $gstItemRate = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID ={$itemDetails['b2bso_itemid']}");
                    $itemDetails['b2bso_gst'] = $gstItemRate;
                    $itemDetails['b2bso_netamount'] = 0.00;
                }
                //end // itemrate, gst, netamount calculation
                unset($itemDetails['bbso_id']);
                $isOK = $db->perform('retaline_B2B_SalesOrder_temp', $itemDetails);
                if ($isOK <> 1) {
                    $msg = "'Error while saving B2B Sales Order Details.'";
                    echo '{"success":false,"msg":' . $msg . '}';
                    exit(1);
                }
                array_push($NewB2BSOItemDetails, $itemDetails);
            }
        }

        if (!empty($NewB2BSOItemDetails)) {
            echo '{"totalCount":' . count($NewB2BSOItemDetails) . ',"data":' . json_encode($NewB2BSOItemDetails) . ',"SOdata":' . json_encode($B2BSalesOrderData) . '}';
        } else {
            echo '{"totalCount":"0","data":[],"SOdata":[],"":[]}';
        }

        break;
    case 'viewB2BSRDetails':
        $data = $_POST;
        $query = "SELECT bbsr_id, bbsr_SRNumber, bbsr_SRUpdatedOn,b2b_Customer_ID,b2b_Customer_Name,"
                . "b2bsr_requesterName,b2bsr_emailAndContactNo,b2bsr_requestedThrough,b2bsr_requestDate, TIME_FORMAT(b2bsr_requestTime, '%h:%i %p') as b2bsr_requestTime,"
                . "IF((status_id=1),'Active','Inactive')AS bbsr_Active "
                . "FROM retaline_B2B_SalesRequest WHERE bbsr_id = {$data['bbsr_id']}";
        $B2BSalesRequestData = $db->getFromDB($query, true);
        $listQuery = "SELECT bbsr_id,b2bsr_itemid, b2bsr_itemname, b2bsr_itemqty, "
                . "b2bsr_itemPkg FROM retaline_B2B_SalesRequestDetails WHERE bbsr_id = {$data['bbsr_id']}";
        $B2BSRItemDetails = $db->getMultipleData($listQuery, true);
        if (!empty($B2BSRItemDetails)) {
            echo '{"totalCount":' . count($B2BSRItemDetails) . ',"data":' . json_encode($B2BSRItemDetails) . ',"SRdata":' . json_encode($B2BSalesRequestData) . '}';
        } else {
            echo '{"totalCount":"0","data":[],"SRdata":[]}';
        }

        break;
    case 'listB2BSalesRequests':
        $data = $_POST;

        $limit = is_numeric($data['limit']) ? $data['limit'] : 23;
        $start = is_numeric($data['start']) ? $data['start'] : 0;
        $_allowed_sort = ['bbso_id', 'bbso_OrderDate', 'bbso_TotalAmount', 'bbso_InvoiceNumber'];
        $sort = in_array(trim($data['sort'] ?? ''), $_allowed_sort) ? trim($data['sort']) : 'bbso_id';
        $dir = (strtoupper(trim($data['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';

        $filter_qry = " WHERE status_id = 1 AND br_ID = {$_SESSION['admin']->finascop_current_branch_id}";
        if (!empty($data['CustomerID'])) {
            $filter_qry .= " AND b2b_Customer_ID = {$data['CustomerID']}";
        }
        if (isset($data['filter'])) {
            $filter = $data['filter'];
            foreach ($filter as $key => $val) {
                switch ($val['data']['type']) {
                    case 'string':
                        $filter_qry .= " AND " . $val['field'] . "  LIKE  '" . $val['data']['value'] . "%'";
                        break;
                }
            }
        }

        $countDataQuery = "SELECT count(1) from retaline_B2B_SalesRequest {$filter_qry} ";
        $listQuery = "SELECT bbsr_id, bbsr_SRNumber, bbsr_SRUpdatedOn,b2b_Customer_ID,b2b_Customer_Name,"
                . "IF((status_id = 1),'Active','Inactive')AS bbsr_Active "
                . "FROM retaline_B2B_SalesRequest {$filter_qry} GROUP BY bbsr_id ORDER BY {$sort} {$dir} LIMIT {$start},{$limit}";
        $db->printGridJson($countDataQuery, $listQuery);
        break;
    case 'saveB2BSalesRequest':
        $data = $_POST;

        if (!array_key_exists('bbsr_id', $data) || !empty($data['bbsr_id'])) {
            $data['bbsr_SRNumber'] = $db->getItemFromDB("SELECT CONCAT('SO',DATE_FORMAT(CURDATE(),'%Y'), LPAD((COUNT(1)+1), 5, '0')) AS soNo FROM retaline_B2B_SalesRequest WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");
        }
        $data['status_id'] = 1;
        $data['br_ID'] = $_SESSION['admin']->finascop_current_branch_id;
        $db->query('begin');
        $bbso_id = intval($data['bbsr_id']);
        $con = "bbsr_id = {$bbso_id}";
        $dup = $db->getItemFromDB("SELECT COUNT(1) FROM retaline_B2B_SalesRequest WHERE {$con}");
        $b2bsr_UniqueID = $data['b2bsr_UniqueID'];
        unset($data['apikey']);
        unset($data['tstamp']);
        unset($data['b2bsr_UniqueID']);
        $data['b2bsr_requestTime'] = DATE("H:i", STRTOTIME($data['b2bsr_requestTime']));
        if ($dup > 0) {
            $db->perform('retaline_B2B_SalesRequest', $data, 'update', $con);
        } else {
            $data['bbsr_SRUpdatedOn'] = date("Y-m-d H:i:s");
            $data['bbsr_SREnteredBy'] = $_SESSION['admin']->Finascop_UserId;
            $data['bbsr_createdon'] = date("Y-m-d H:i:s");
            $db->perform('retaline_B2B_SalesRequest', $data);
            $data['bbsr_id'] = $db->insert_id();
        }


        $listQuery = "SELECT * FROM retaline_B2B_SalesRequest_temp WHERE b2bsr_UniqueID = '{$b2bsr_UniqueID}' ORDER BY b2bsr_createdon ASC";
        $itemDetailsArray = $db->getMultipleData($listQuery, true);
        if (count($itemDetailsArray) > 0 && !empty($itemDetailsArray)) {
            foreach ($itemDetailsArray as $itemDetails) {
                unset($itemDetails['b2bsr_UniqueID']);
                $itemDetails['bbsr_id'] = $data['bbsr_id'];
                $isOK = $db->perform('retaline_B2B_SalesRequestDetails', $itemDetails);
                if ($isOK <> 1) {
                    $msg = "'Error while saving B2B Sales Order Details.'";
                    echo '{"success":false,"msg":' . $msg . '}';
                    exit(1);
                }
            }
        } else {
            $msg = "'Error while saving B2B Sales Order Details. Items not available'";
            echo '{"success":false,"msg":' . $msg . '}';
            exit(1);
        }
        $db->query("DELETE FROM retaline_B2B_SalesRequest_temp WHERE b2bsr_UniqueID = '{$b2bsr_UniqueID}'");

        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'B2B Sales Order details saved successfully.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error while saving B2B Sales Order.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }

        break;
    case 'listB2BSRItems':
        $b2bsr_UniqueID = $_POST['b2bsr_UniqueID'];
        $countDataQuery = "SELECT count(*) from retaline_B2B_SalesRequest_temp where b2bsr_UniqueID = '{$b2bsr_UniqueID}'";
        $listQuery = "SELECT * FROM retaline_B2B_SalesRequest_temp WHERE b2bsr_UniqueID = '{$b2bsr_UniqueID}' ORDER BY b2bsr_createdon ASC";
        $db->printGridJson($countDataQuery, $listQuery);
        break;
    case 'saveB2BSRItemDetails':

        $data = $_POST;

        $data['b2bsr_createdon'] = date("Y-m-d H:i:s");
        $data['b2bsr_createdby'] = $_SESSION['admin']->Finascop_UserId;


        unset($data['apikey']);
        unset($data['tstamp']);


        $db->query('begin');
        $con = "b2bsr_UniqueID = '{$data['b2bsr_UniqueID']}' and b2bsr_itemid = {$data['b2bsr_itemid']}";
        $dup = $db->getItemFromDB("SELECT COUNT(1) FROM retaline_B2B_SalesRequest_temp WHERE {$con}");

        if ($dup > 0) {
            $status = $db->perform('retaline_B2B_SalesRequest_temp', $data, 'update', $con);
        } else {
            $status = $db->perform('retaline_B2B_SalesRequest_temp', $data);
        }

        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'B2B Sales Request Item saved successfully.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error while saving SR Details.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'viewB2BSODetails':
        $data = $_POST;
        $query = "SELECT bbso_id, bbso_SONumber, bbso_SODate,b2b_Customer_ID,b2b_Customer_Name,bbso_SOValue,"
                . "bbso_HandlingCharges ,IF((status_id = 1),'Active','Inactive')AS bbso_Active "
                . "FROM retaline_B2B_SalesOrder WHERE bbso_id = {$data['bbso_id']}";
        $B2BSalesOrderData = $db->getFromDB($query, true);
        $listQuery = "SELECT bbso_id,b2bso_itemid, b2bso_itemname, b2bso_itemmrp, b2bso_itemqty, b2bso_itemrate,b2bso_gst,"
                . " b2bso_itemPkg,b2bso_amount,b2bso_discountpercent,b2bso_discountamt, b2bso_netamount,"
                . " COALESCE((SELECT IF( (SUM(item_count) - (SELECT COALESCE((SELECT SUM(fsb.count) AS items_blocked FROM finascop_stock_blocked fsb"
                . " WHERE fsb.item_id = fbi.stit_id AND  fsb.branch_id = fbi.branch_id AND fbi.fsbg_id = fsbg_id),0) AS items_blocked) >= b2bso_itemqty) ,1, 0)"
                . " AS ss FROM finascop_stock_branch_inventory fbi"
                . " WHERE fbi.stit_id = b2bso_itemid AND fbi.fsbg_id = fsbg_id AND fbi.branch_id = {$_SESSION['admin']->finascop_current_branch_id}),0) AS isStockEnough "
                . " FROM retaline_B2B_SalesOrderDetails WHERE bbso_id = {$data['bbso_id']}";

        $B2BSOItemDetails = $db->getMultipleData($listQuery, true);
        if (!empty($B2BSOItemDetails)) {
            echo '{"totalCount":' . count($B2BSOItemDetails) . ',"data":' . json_encode($B2BSOItemDetails) . ',"SOdata":' . json_encode($B2BSalesOrderData) . '}';
        } else {
            echo '{"totalCount":"0","data":[],"SOdata":[]}';
        }

        break;
    case 'listB2BSalesOrders':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 23;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['bbso_id', 'bbso_OrderDate', 'bbso_TotalAmount', 'bbso_InvoiceNumber'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'bbso_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'bbso_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;

        $filter_qry = " WHERE  1=1 ";
        if (isset($_POST['filter'])) {
        $allowedFields = ['bbso_id', 'bbsr_id', 'bbsr_SRNumber', 'b2b_Customer_Name', 'b2b_Customer_ID', 'bbso_InvoiceNumber', 'bbsr_SRUpdatedOn', 'bbso_OrderDate', 'status_id', 'order_status', 'bbso_TotalAmount'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }
        if ($_SESSION['admin']->br_PyramidLevel == 1) {
            $filter_qry .= " ";
            /*if ($_SESSION['admin']->IsSuperUser == 'Yes') {
                $filter_qry .= " ";
            } else {
                $filter_qry .= " AND br_ID = {$_SESSION['admin']->finascop_current_branch_id} ";
            }*/
        } else {
            $filter_qry .= " AND br_ID = {$_SESSION['admin']->finascop_current_branch_id} ";
        }
        /* status_id >= 1 AND status_id <= 7 AND */
        $countDataQuery = "SELECT count(1) from retaline_B2B_SalesOrder {$filter_qry} ";
        $listQuery = "SELECT bbso_id, bbso_SONumber, bbso_SODate,b2b_Customer_ID,b2b_Customer_Name,bbso_SOValue,bbso_InvoiceStatus,"
                . "CASE WHEN bbso_InvoiceStatus = 0 THEN 'Not Ready for Invoice' WHEN bbso_InvoiceStatus = 1 THEN 'Ready for Invoice' WHEN bbso_InvoiceStatus = 2 THEN 'Invoiced' END AS bbso_InvoiceStatusName,"
                . "bbso_HandlingCharges ,(SELECT status from retaline_B2B_Status rbbs WHERE rbbs.status_id = rbs.status_id) AS bbso_Active,status_id "
                . "FROM retaline_B2B_SalesOrder rbs {$filter_qry} ORDER BY {$sort} {$dir} LIMIT {$start},{$limit}";
        //echo $listQuery;
        //exit(1);
        $db->printGridJson($countDataQuery, $listQuery);
        break;
    case 'saveB2BSalesOrder':
        $data = $_POST;
        if (!empty($data['salesRequestIDs'])) {
            $salesRequestIDs = json_decode(stripslashes($data['salesRequestIDs']), true);
            unset($data['salesRequestIDs']);
            if (!empty($salesRequestIDs)) {
                $salesRequestIDs = implode(',', $salesRequestIDs);
            } else {
                $salesRequestIDs = "''";
            }
        } else {
            $salesRequestIDs = "''";
        }

        if (!array_key_exists('bbso_id', $data) || !empty($data['bbso_id'])) {
            $data['bbso_SONumber'] = $db->getItemFromDB("SELECT CONCAT('SO',DATE_FORMAT(CURDATE(),'%Y'),LPAD((COUNT(1)+1), 6, '0')) AS soNo FROM retaline_B2B_SalesOrder");
        }
        $data['status_id'] = 1;
        $data['br_ID'] = $_SESSION['admin']->finascop_current_branch_id;
        $db->query('begin');
        $bbso_id = intval($data['bbso_id']);
        $con = "bbso_id = {$bbso_id}";
        $dup = $db->getItemFromDB("SELECT COUNT(1) FROM retaline_B2B_SalesOrder WHERE {$con}");
        $b2bso_UniqueID = $data['b2bso_UniqueID'];
        $rbsch_name = $data['rbsch_name'];
        unset($data['apikey']);
        unset($data['tstamp']);
        unset($data['b2bso_UniqueID']);
        unset($data['rbsch_name']);

        $query = "SELECT SUM(b2bso_amount) AS bbso_InvValAtax,  SUM(b2bso_amount_btax) AS bbso_InvValBtax,
        SUM(b2bso_cgst_value) AS bbso_CGSTVal, SUM(b2bso_sgst_value) AS bbso_SGSTVal"
                . " FROM retaline_B2B_SalesOrder_temp WHERE b2bso_UniqueID = '{$b2bso_UniqueID}' AND b2bso_itemrate > 0";
        $B2BSalesInvData = $db->getFromDB($query, true);
        $TotAmount = $B2BSalesInvData['bbso_InvValAtax'];
        $paise = round($TotAmount - ($Ruppes = floor($TotAmount)), 2) * 100;
        $B2BSalesInvData['bbso_totInFig'] = "Rupees " . $Ruppes . " and " . $paise . " Paise";
        $B2BSalesInvData['bbso_totInWords'] = getIndianCurrencyToWords(number_format((float) $TotAmount, 2, '.', ''));

        $data = array_merge($data, $B2BSalesInvData);


        if ($dup > 0) {
            $data['bbso_updatedon'] = date("Y-m-d H:i:s");
            $data['bbso_updatedby'] = $_SESSION['admin']->Finascop_UserId;
            $db->perform('retaline_B2B_SalesOrder', $data, 'update', $con);
        } else {

            $data['bbso_SODate'] = date("Y-m-d H:i:s");
            $data['bbso_SOOrderedby'] = $_SESSION['admin']->Finascop_UserId;
            $data['bbso_createdon'] = date("Y-m-d H:i:s");
            $db->perform('retaline_B2B_SalesOrder', $data);
            $data['bbso_id'] = intval($db->insert_id());
        }

        $stockEnoughQuery = "SELECT "
                . " COUNT(COALESCE((SELECT IF( (item_count - (SELECT COALESCE((SELECT SUM(fsb.count) AS items_blocked FROM finascop_stock_blocked fsb"
                . " WHERE fsb.item_id = fbi.stit_id AND  fsb.branch_id = fbi.branch_id),0) AS items_blocked) >= b2bso_itemqty) ,1, 0)"
                . " AS ss FROM finascop_stock_branch_inventory fbi"
                . " WHERE fbi.stit_id = b2bso_itemid AND fbi.branch_id = {$_SESSION['admin']->finascop_current_branch_id}),0)) AS isStockEnough "
                . " FROM retaline_B2B_SalesOrder_temp  tmp "
                . " WHERE isStockEnough = 0 AND tmp.b2bso_UniqueID = '{$b2bso_UniqueID}'";

        $stockNotEnoughItemCount = $db->getItemFromDB($stockEnoughQuery);
        if ($stockNotEnoughItemCount > 0) {
            $msg = "'{$stockNotEnoughItemCount} item(s) have not enough stock for saving B2B Sales Order.'";
            echo '{"success":false,"msg":' . $msg . '}';
            exit(1);
        }


        $isOK = $db->query("INSERT INTO retaline_B2B_SalesOrderDetails (bbso_id,b2bso_itemid,b2bso_itemname,b2bso_HSN,b2bso_itemmrp,b2bso_itemrate,"
                . "b2bso_itemPkg,b2bso_itemqty,b2bso_gst,b2bso_cgst_percent, b2bso_cgst_value, b2bso_sgst_percent, b2bso_sgst_value, "
                . " b2bso_amount_btax, b2bso_amount,b2bso_discountpercent,b2bso_discountamt,b2bso_netamount,"
                . "b2bso_createdon,b2bso_createdby,rbsch_id,fsbg_id) "
                . "SELECT {$data['bbso_id']},b2bso_itemid,b2bso_itemname,b2bso_HSN,b2bso_itemmrp,b2bso_itemrate,"
                . "b2bso_itemPkg, b2bso_itemqty,b2bso_gst,b2bso_cgst_percent, b2bso_cgst_value, b2bso_sgst_percent, b2bso_sgst_value, "
                . " b2bso_amount_btax,b2bso_amount,b2bso_discountpercent,b2bso_discountamt,b2bso_netamount,"
                . "b2bso_createdon,b2bso_createdby,rbsch_id,fsbg_id FROM retaline_B2B_SalesOrder_temp tmp "
                . "WHERE tmp.b2bso_UniqueID = '{$b2bso_UniqueID}' AND tmp.b2bso_itemrate >0  ORDER BY b2bso_createdon ASC");

        if ($isOK <> 1) {
            $msg = "'Error while saving B2B Sales Order Details.'";
            echo '{"success":false,"msg":' . $msg . '}';
            exit(1);
        }
        $db->query("DELETE FROM retaline_B2B_SalesOrder_temp WHERE b2bso_UniqueID = '{$b2bso_UniqueID}'");
        $db->query("UPDATE retaline_B2B_SalesRequest SET bbso_id = {$data['bbso_id']},status_id = 2 WHERE bbsr_id IN ({$salesRequestIDs})");

        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'B2B Sales Order details saved successfully.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error while saving B2B Sales Order.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }

        break;
    case 'cancelTempB2BSO':
        $data = $_POST;
        $delquery = "DELETE FROM retaline_B2B_SalesOrder_temp WHERE b2bso_UniqueID = '{$data['b2bso_UniqueID']}'";
        $status = $db->query($delquery);

        if ($status == 1) {
            echo "{success: true,msg:'Deleted Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while deleting data' }}";
        }
        break;
    case 'deleteItemFromB2BSO' :
        $data = $_POST;
        $db->query('begin');
        $delquery = "DELETE FROM retaline_B2B_SalesOrder_temp "
                . "WHERE b2bso_UniqueID = '{$data['uid']}' and b2bso_itemid = {$data['itemid']}";
        $db->query($delquery);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Deleted Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while deleting data' }}";
        }
        break;
    case 'b2bSOCustomerDetails':
        $b2bSOCustomer = $_POST['b2bSOCustomer'];
        $result = $db->getFromDB("SELECT b2b_Customer_Incharge,b2b_Customer_pincode,b2b_Customer_Mobile,b2b_Customer_gst,rbsch_name"
                . "  FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = {$b2bSOCustomer}", true);
        if (!empty($result)) {
            echo json_encode($result);
        } else {
            echo '{"data":[]}';
        }
        break;

    case 'listB2BSOItems':
        $b2bso_UniqueID = $_POST['b2bso_UniqueID'];
        $countDataQuery = "SELECT count(1) from retaline_B2B_SalesOrder_temp where b2bso_UniqueID = '{$b2bso_UniqueID}'";
        $listQuery = "SELECT * FROM retaline_B2B_SalesOrder_temp WHERE b2bso_UniqueID = '{$b2bso_UniqueID}' ORDER BY b2bso_createdon ASC";
        $db->printGridJson($countDataQuery, $listQuery);
        break;

    case 'saveB2BSOItemDetails':

        $data = $_POST;
        $item_gst_percentage = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$data['b2bso_itemid']}");
        $data['b2bso_gst'] = $item_gst_percentage;
        if ($data['b2bso_discountin'] == 'Percentage') {

            $data['b2bso_discountpercent'] = floatval($data['b2bso_discount']);
            $data['b2bso_discountamt'] = (floatval($data['b2bso_amount']) * floatval($data['b2bso_discountpercent']) / 100);
        } else {
            $data['b2bso_discountpercent'] = '0.00';
            $data['b2bso_discountamt'] = floatval($data['b2bso_discount']);
        }
        $data['b2bso_createdon'] = date("Y-m-d H:i:s");
        $data['b2bso_createdby'] = $_SESSION['admin']->Finascop_UserId;

        //begin //get scheme and batch data inserted
        $result = null;
        if (!empty($data['rbsch_name'])) {
            $query = "SELECT rbsch_id FROM  retaline_B2BScheme WHERE rbsch_name = '{$data['rbsch_name']}' "
                    . "AND rbsch_FromMrp <=  {$data['b2bso_itemmrp']} "
                    . "AND rbsch_ToMrp >  {$data['b2bso_itemmrp']}";
            $result = $db->getFromDB($query, true);
        }
        if (empty($result)) {
            $query = "SELECT rbsch_id FROM  retaline_B2BScheme
               WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id} "
                    . "AND rbsch_IsGeneral = 1 "
                    . "AND rbsch_FromMrp <=  {$data['b2bso_itemmrp']} "
                    . "AND rbsch_ToMrp >  {$data['b2bso_itemmrp']}";
            $result = $db->getFromDB($query, true);

            if (empty($result)) {
                $msg = "'No valid B2B Scheme found! Please check your B2B Sales Schemes.'";
                echo '{"success":false,"msg":' . $msg . '}';
                exit(1);
            }
        }

        $data['rbsch_id'] = $result['rbsch_id'];

        //end //get scheme and batch data inserted

        $data['isStockEnough'] = $db->getItemFromDB("SELECT COALESCE((IF( (item_count - (SELECT COALESCE((SELECT SUM(fsb.count) AS items_blocked 
        FROM finascop_stock_blocked fsb 
        WHERE fsb.item_id = fbi.stit_id AND  fsb.branch_id = fbi.branch_id),0) AS items_blocked) >= {$data['b2bso_itemqty']}) ,1, 0)),0) AS isStockEnough
        FROM finascop_stock_branch_inventory fbi WHERE fbi.stit_id = {$data['b2bso_itemid']} AND fbi.branch_id = {$_SESSION['admin']->finascop_current_branch_id}");

        $gst_details = $db->getFromDB("SELECT fbi.stit_HSNCode AS b2bso_HSN,
         (({$data['b2bso_amount']} * 100) / (100 + fbi.stit_GST)) AS b2bso_amount_btax,
          (fbi.stit_GST / 2) AS b2bso_cgst_percent,(fbi.stit_GST / 2) AS b2bso_sgst_percent,
           (fbi.stit_GST / 200 * (({$data['b2bso_amount']} * 100) / (100 + (fbi.stit_GST))) ) AS b2bso_cgst_value,
            (fbi.stit_GST / 200 * (({$data['b2bso_amount']} * 100) / (100 + (fbi.stit_GST))) ) AS b2bso_sgst_value
             FROM finascop_stock_itemmaster fbi WHERE fbi.stit_id = {$data['b2bso_itemid']} ", true);

        $data = array_merge($data, $gst_details);

        unset($data['apikey']);
        unset($data['tstamp']);
        unset($data['b2bso_discount']);
        unset($data['b2bso_discountin']);
        unset($data['rbsch_name']);

        $db->query('begin');
        $con = "b2bso_UniqueID = '{$data['b2bso_UniqueID']}' and b2bso_itemid = {$data['b2bso_itemid']}";
        $dup = $db->getItemFromDB("SELECT COUNT(1) FROM retaline_B2B_SalesOrder_temp WHERE {$con}");

        if ($dup > 0) {
            $status = $db->perform('retaline_B2B_SalesOrder_temp', $data, 'update', $con);
        } else {
            $status = $db->perform('retaline_B2B_SalesOrder_temp', $data);
        }

        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'B2B Sales Order Item saved successfully.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error while saving SO Details.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;

    case 'generateUniqueID':
        $uniqueId = '';
        while ($uniqueId == '') {
            $uniqueId = getNewFinascopApiKey();
        }
        echo '{"uid":"' . $uniqueId . '"}';
        break;
    case 'getB2BBrCustomers':
        $result = $db->getMulipleData("SELECT b2b_Customer_ID,b2b_Customer_Name FROM retaline_B2Bcustomer WHERE b2b_Customer_status='Active' AND br_ID = {$_SESSION['admin']->finascop_current_branch_id}", true);
        if (!empty($result)) {
            echo '{"totalCount":' . count($result) . ',"data":' . json_encode($result) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'getB2BCustomerItems' :
        $con = ($_POST['query'] != 'WHERE 1 = 1') ? "WHERE stit_SKU LIKE '%" . $query . "%'" : '';
        $qry = "select stit_ID,stit_SKU from " . FINASCOP_DB . "finascop_stock_itemmaster {$con} AND stit_SalesEnabled = 1 ORDER BY stit_SKU";
        $result = $db->getMultipleData($qry, true);
        if (!empty($result)) {
            echo '{"totalCount":' . count($result) . ',"data":' . json_encode($result) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'getB2BCustomerSOItems':
        $con = ($_POST['query'] != '') ? " stit_SKU LIKE '%" . $query . "%'" : '1=1';

        $qry = "SELECT fsi.stit_ID,fsi.stit_SKU FROM " . FINASCOP_DB . "finascop_stock_itemmaster fsi INNER JOIN finascop_stock_branch_inventory fsbi"
                . " ON fsi.stit_ID = fsbi.stit_id "
                . " WHERE {$con} AND fsbi.branch_id = {$_SESSION['admin']->finascop_current_branch_id} AND stit_SalesEnabled = 1  ORDER BY stit_SKU";
        $result = $db->getMultipleData($qry, true);
        if (!empty($result)) {
            echo '{"totalCount":' . count($result) . ',"data":' . json_encode($result) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'listB2BCustomers':

        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'b2b_Customer_Name' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['bbso_id', 'bbsr_id', 'bbsr_SRNumber', 'b2b_Customer_Name', 'b2b_Customer_ID', 'bbso_InvoiceNumber', 'bbsr_SRUpdatedOn', 'bbso_OrderDate', 'status_id', 'order_status', 'bbso_TotalAmount'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . "retaline_B2Bcustomer a WHERE {$filter_part} AND a.br_ID = {$_SESSION['admin']->finascop_current_branch_id}";
        $listQuery = "SELECT b2b_Customer_ID,b2b_Customer_Name,"
                . "b2b_Customer_Address,b2b_Customer_Email,b2b_Customer_Phone,"
                . "b2b_Customer_status,b2b_Customer_pincode,b2b_Customer_Mobile,b2b_Customer_Incharge,"
                . "b2b_Customer_Lat,b2b_Customer_Lng,rbsch_name "
                . "FROM " . FINASCOP_DB . "retaline_B2Bcustomer a WHERE {$filter_part} AND a.br_ID = {$_SESSION['admin']->finascop_current_branch_id} "
                . "ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        $db->printGridJson($countQuery, $listQuery);

        break;

    case 'saveB2BCustomer':
        global $db;

        $db->query('begin');
        $data = $_POST;
        unset($data['apikey']);
        unset($data['tstamp']);

        if (!array_key_exists("b2b_Customer_Name", $data)) {
            echo "{success:false,errors: { msg: 'B2B Customer's name param missing. ' }}";
            exit();
        }

        $b2b_Customer_in_db_qry = "SELECT COUNT(*) from " . FINASCOP_DB . "retaline_B2Bcustomer "
                . "WHERE b2b_Customer_Name = '{$data['b2b_Customer_Name']}' AND br_ID = {$_SESSION['admin']->finascop_current_branch_id}";

        if ($data['b2b_Customer_ID'] > 0 && $IsAddNew == false) {
            $b2b_Customer_in_db_qry .= " AND b2b_Customer_ID <> {$data['b2b_Customer_ID']}";
        }

        $b2b_Customer_in_db = $db->getItemFromDB($b2b_Customer_in_db_qry);
        if ($b2b_Customer_in_db > 0) {
            echo "{errors: { reason: 'B2B Client with name {$data['b2b_Customer_Name']} already exists.' }}";
            exit;
        }

        if ($data['b2b_Customer_ID'] > 0) {
            $data['b2b_updatedon'] = date("Y-m-d H:i:s");
            $data['b2b_updatedby'] = $_SESSION['admin']->Finascop_UserId;

            //FINASCOP API CALLS BEGIN
            $FinascopWC = new FinascopWalletClient(FINASCOPAPIDOMAIN);
            $RefIDs['companyApiKey'] = $db->getItemFromDB("SELECT comp_ReferenceId FROM finascop_company WHERE comp_id ={$_SESSION['admin']->finascop_current_company_id}");
            $RefIDs['groupReferenceId'] = SUNDRYDEBTORGRP;
            $RefIDs['branchApiKey'] = $db->getItemFromDB("SELECT br_ReferenceId FROM finascop_branch WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");

            $ReferenceId = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = {$data['b2b_Customer_ID']}");
            if (empty($ReferenceId)) {
                $returned = $FinascopWC->createLedger(time(), $data['b2b_Customer_Name'], $data['b2b_Customer_Mobile'], $RefIDs, $credit_limit = 0);
                $result = json_decode($returned, true);
                $data['accled_ReferenceId'] = $result['ledgerID'];
                $status = $db->perform(FINASCOP_DB . "retaline_B2Bcustomer", $data, "update", "b2b_Customer_ID={$data['b2b_Customer_ID'] }");
                // $status = $db->perform(FINASCOP_DB . 'finascop_stock_party', $ledger, 'update', 'stpa_id=' . $cust_id);
            } else {
                $returned = $FinascopWC->editLedger(time(), $ReferenceId, $data['b2b_Customer_Name'], $data['b2b_Customer_Mobile'], $RefIDs, $credit_limit = 0);
                $status = $db->perform(FINASCOP_DB . "retaline_B2Bcustomer", $data, "update", "b2b_Customer_ID={$data['b2b_Customer_ID'] }");
            }
           // print_r($returned);
            $result = json_decode($returned, true);
            //print_r($result);
            if (array_key_exists('success', $result) && $result['success'] == true) {
                $data['accled_ReferenceId'] = $result['ledgerID'];
            } else {
                echo "{'success':'false','msg':'Failed to create ledger.{$result['error']}'}";
                exit(1);
            }
            //FINASCOP API CALLS END 
        } else {
            unset($data['b2b_Customer_ID']);
            $data['b2b_createdon'] = date("Y-m-d H:i:s");
            $data['b2b_createdby'] = $_SESSION['admin']->Finascop_UserId;
            $data['b2b_updatedon'] = date("Y-m-d H:i:s");
            $data['b2b_updatedby'] = $_SESSION['admin']->Finascop_UserId;
            $data['br_ID'] = $_SESSION['admin']->finascop_current_branch_id;
            $data['b2b_Customer_ReferenceID'] = getNewFinascopApiKey();

            //FINASCOP API CALLS BEGIN

            $FinascopWC = new FinascopWalletClient(FINASCOPAPIDOMAIN);
            $RefIDs['companyApiKey'] = $db->getItemFromDB("SELECT comp_ReferenceId FROM finascop_company WHERE comp_id ={$_SESSION['admin']->finascop_current_company_id}");
            $RefIDs['groupReferenceId'] = SUNDRYDEBTORGRP;
            $RefIDs['branchApiKey'] = $db->getItemFromDB("SELECT br_ReferenceId FROM finascop_branch WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");


            $returned = $FinascopWC->createLedger(time(), $data['b2b_Customer_Name'], $data['b2b_Customer_Mobile'], $RefIDs, $credit_limit = 0);

            $result = json_decode($returned, true);
            if (array_key_exists('success', $result) && $result['success'] == true) {
                $data['accled_ReferenceId'] = $result['ledgerID'];
            } else {
                echo "{'success':'false','msg':'Failed to create ledger.{$result['error']}'}";
                exit(1);
            }
            //FINASCOP API CALLS END   
            $status = $db->perform(FINASCOP_DB . "retaline_B2Bcustomer", $data);
        }


        if ($status) {
            echo "{success: true}";
            $db->query('commit');
        } else {
            echo "{success: false, errors: 'Error occured while saving B2B Customer data.' }";
        }

        break;

    case 'getB2BClientDetails':
        if (!empty($_POST['id'])) {
            $detailsQuery = "SELECT b2b_Customer_ID,b2b_Customer_Name,"
                    . "b2b_Customer_Address,b2b_Customer_Email,b2b_Customer_Phone,b2b_Customer_gst,"
                    . "b2b_Customer_status,b2b_Customer_pincode,b2b_Customer_Mobile,b2b_Customer_Incharge,"
                    . "b2b_Customer_Lat,b2b_Customer_Lng,b2b_Customer_dlno1,b2b_Customer_dlno2,b2b_Customer_fssaino "
                    . "FROM " . FINASCOP_DB . "retaline_B2Bcustomer a WHERE b2b_Customer_ID = {$_POST['id']} ";
            $db->_loadRecordJson($detailsQuery, true);
        }
        break;
    case 'getSODetails':
        $poId = $_POST['bbso_id'];
        $podata = $db->getFromDB("SELECT  bbso_id,bbso_SONumber,b2b_Customer_Name,b2b_Customer_Name,DATE_FORMAT(bbso_SODate,'%d-%m-%Y') as bbso_SODate,bbso_paymentTerms,bbso_SOOrderedby,bbso_SOValue,bbso_paymentValue,"
                . "CONCAT(bbso_gdiscpercent,'','%') as bbso_gdiscpercent,CONCAT(bbso_validityType,'',' days') as bbso_validityType,bbso_HandlingCharges  FROM retaline_B2B_SalesOrder where bbso_id = {$poId}", true);
        if (!empty($podata)) {
            echo json_encode($podata);
        }
        break;
    case 'listB2BSOItemsView':
        $bbso_id = $_POST['bbso_id'];
        $countDataQuery = "SELECT count(1) from retaline_B2B_SalesOrderDetails where bbso_id = '{$bbso_id}'";
        $listQuery = "SELECT bbso_id,b2bso_itemid,b2bso_itemname,b2bso_itemmrp,b2bso_itemqty,b2bso_itemrate,b2bso_itemoffrqty,b2bso_itemaddidisc,b2bso_shippingcharge,b2bso_gst,b2bso_itemPkg,b2bso_gendiscount,"
                . "b2bso_effectiverate,b2bso_amount,b2bso_discountpercent,b2bso_discountamt,b2bso_netamount,b2bso_idiscountcalculs, "
                . "IF(b2bso_itemaddidisc > 0,(CONCAT(b2bso_itemaddidisc,'',IF(b2bso_idiscountcalculs = 'Amount',' Rs',' %'))),'') AS itemDisc FROM retaline_B2B_SalesOrderDetails WHERE bbso_id = '{$bbso_id}' ORDER BY bbsd_id ASC";
        $db->printGridJson($countDataQuery, $listQuery);
        break;
}
