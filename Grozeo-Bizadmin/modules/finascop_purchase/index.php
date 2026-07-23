<?php

require_once(EXTERNAL_LIBRARY_PATH);
require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/finascop_config/config.php');

require_once(INCLUDE_PATH . "/finascop_accounts_Transactions.php");
require_once(INCLUDE_PATH . "/finascop_common_functions.php");

switch ($op) {


    case 'listPurchaseInvoices':

        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $rec_sort = empty($_POST['sort']) ? 'a.puen_InvoiceNo' : $_POST['sort'];
        $rec_sort_dir = empty($_POST['dir']) ? 'DESC' : $_POST['dir'];

        $curr_branch = $_SESSION['admin']->finascop_current_branch_id;

        $total = "select SUM(puen_NetAmt) AS total_amount,SUM(puen_Tax) as tax_total, SUM(puen_TotalItems) AS total_items, SUM(puen_TotalItemQty) AS total_item_qty "
                . " from " . FINASCOP_DB . "finascop_purchase_invoice WHERE br_id= '$curr_branch'";
        $coltotal = $db->getFromDB($total, true);

        $qry = "SELECT a.puen_Id as puen_Id,a.puen_InvoiceDate as puen_InvoiceDate,a.puen_InvoiceNo as puen_InvoiceNo,"
                . "a.puen_Vendorname as puen_Vendorname,"
                . "a.puen_NetAmt as puen_NetAmt,a.puen_Tax as puen_Tax,a.puen_TotalItems as puen_TotalItems,"
                . "a.puen_TotalItemQty as puen_TotalItemQty,a.puen_VendorPurchaseOrder as puen_VendorPurchaseOrder,"
                . "a.puen_VendorPurchaseOrderDate as puen_VendorPurchaseOrderDate,a.puen_RefNo as puen_RefNo"
                . " FROM " . FINASCOP_DB . "finascop_purchase_invoice a WHERE a.br_id= '$curr_branch' "
                . "ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";

        $data = $db->getMultipleData($qry, true);

        $countQuery = "SELECT COUNT(*) FROM " . FINASCOP_DB . "finascop_purchase_invoice a WHERE a.br_id= '{$curr_branch}'";
        $count = $db->getItemFromDB($countQuery);

        $result = [];
        foreach ($data as $key => $value) {

            foreach ($coltotal as $k => $v) {

                $value[$k] = $v;
                $result[$key] = $value;
            }
        }

        if (!empty($result)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($result) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;

    case 'getParty':

        $filter_query = "CONCAT(stpa_Fname,' ',stpa_Lname) LIKE '" . $_POST['query'] . "%'";

        if ($_POST['isParty'] == 'false') {

            echo '{"success":true,"data":[]}';
            exit();
        } else {
            $br_id = $_SESSION['admin']->finascop_current_branch_id;
            $qry = "SELECT stpa_id AS id,CONCAT(stpa_Fname,' ',stpa_Lname) AS party,"
                    . "(SELECT st_Id from " . FINASCOP_DB . "finascop_district fd WHERE fd.dst_Id=fsp.dst_Id) AS party_state_id, br_id "
                    . "from " . FINASCOP_DB . "finascop_stock_party fsp WHERE br_id = {$br_id} AND  $filter_query "
                    . "ORDER BY CONCAT(stpa_Fname,' ',stpa_Lname) ASC ";
            $party = $db->getMultipleData($qry, true);

            echo '{"success":true,"data":' . json_encode($party) . '}';
        }
        break;
    case 'getInvoiceItemdetails':
        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;
        $branch_qry = "select br_State from " . FINASCOP_DB . "finascop_branch WHERE br_ID = '{$currentBranch}' ";
        $branch_state_id = $db->getFromDB($branch_qry, true);

        $item_id = $_POST['item_id'];
        $party_state_id = $_POST['party_state_id'];
        if ($branch_state_id == $party_state_id) {
            $qry = "select stit_MRP,stit_HSNCode,stit_GST,0 AS IGST, (stit_GST*0.5) AS CGST, (stit_GST*0.5) AS SGST,"
                    . "stit_StockEnabled as stit_StockEnabled from " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID= '{$item_id}' ";
        } else {
            $qry = "select stit_MRP,stit_HSNCode,stit_GST,  (stit_GST) AS IGST, 0 AS CGST, 0 AS SGST,stit_StockEnabled as stit_StockEnabled "
                    . "from " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID= '{$item_id}' ";
        }


        $data = $db->getFromDB($qry, true);
        if ($data) {
            echo "{success:true,data:" . json_encode($data) . "}";
        } else {
            echo '{"success":false,"msg":"Data not inserted"}';
        }

        break;


    case 'getItems':

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

        $filter_query = "stit_itemName LIKE '" . $_POST['query'] . "%'";

        $qry = "SELECT stit_ID AS item_id, stit_itemName AS item_name "
                . "FROM " . FINASCOP_DB . "finascop_stock_itemmaster "
                . "WHERE stit_PurchaseEnabled=1 AND stit_ID NOT IN (" . $excludeIds . ") AND  $filter_query"
                . " UNION "
                . "SELECT stit_ID AS item_id, stit_itemName AS item_name "
                . "FROM " . FINASCOP_DB . "finascop_stock_itemmaster "
                . "WHERE stit_PurchaseEnabled=1 AND stit_ID NOT IN (" . $excludeIds . ") "
                . "AND  stit_itemName LIKE '%" . $_POST['query'] . "%' "
                . "AND stit_itemName NOT LIKE '" . $_POST['query'] . "%'";
        $items = $db->getMultipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . count($items) . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'savePurchaseInvoices':

        $db->query('begin');
        $invDate = explode('/', $_POST['invoiceDate']);
        date_default_timezone_set('Asia/Kolkata');
        $invDate = date("Y-m-d", mktime(0, 0, 0, $invDate[1], $invDate[0], $invDate[2]));
        $poDate = explode('/', $_POST['purchaseOrderDate']);
        $poDate = date("Y-m-d", mktime(0, 0, 0, $poDate[1], $poDate[0], $poDate[2]));
        $br_Id = $_SESSION['admin']->finascop_current_branch_id;
        $isParty = $_POST['cb1'];

        if (empty($data['puen_Id'])) {
            $puen_Id = getRandomRef();
        }

//form data

        $purchaseInvoiceFormdata = array(
            "puen_Id" => $puen_Id,
            'puen_IsParty' => ($_POST['cb1'] == "on" ? 1 : 0),
            "puen_VendorPurchaseOrder" => $_POST['purchcase_order_no'],
            "puen_InvoiceDate" => $invDate,
            "puen_VendorPurchaseOrderDate" => $poDate,
            "stpa_id" => ($isParty == 1 ? $_POST['party_id'] : 0),
            "br_id" => $_SESSION['admin']->finascop_current_branch_id,
            "puen_Vendorname" => $_POST['party'],
            "puen_NetAmt" => $_POST['netAmount'],
            "puen_signature" => $_POST['signature'],
            "puen_RefNo" => $_POST['ref_no'],
            "puen_GrossAmt" => $_POST['grossAmount'],
            "puen_Discount" => $_POST['discount'],
            "puen_TotalItems" => $_POST['totalItems'],
            "puen_TotalItemQty" => $_POST['totalItemQty'],
            "puen_Tax" => $_POST['tax'],
            "puen_updated_on" => sha1(microtime(true) . mt_rand(10000, 90000))
        );

        $status = $db->perform(FINASCOP_DB . 'finascop_purchase_invoice', $purchaseInvoiceFormdata);

//grid data
        $invoice_id = $puen_Id;

// $status = insertInvoiceNo($invoice_id);
        $lastInvoiceNo = insertInvoiceNo($invoice_id);



        $gridData = json_decode($_POST['invoiceItem_data'], true);

        //$stockReg_Number = insertStockRegNo();
        $stockReg_Number = generateNextDocNo('18', $br_Id);

        foreach ($gridData as $key => $val) {


            $purchaseInvoiceGriddata = array(
                "puen_Id" => $puen_Id,
                "paii_itemID" => $val['item_id'],
                "paii_itemQty" => $val['quantity'],
                "paii_Rate" => $val['rate'],
                "paii_IGST" => $val['igst'],
                "paii_CGST" => $val['cgst'],
                "paii_SGST" => $val['sgst']
            );
            $status = $db->perform(FINASCOP_DB . 'finascop_purchase_invoice_items', $purchaseInvoiceGriddata);

// $stock_enabled_checkQuery = "SELECT stit_StockEnabled FROM ".FINASCOP_DB ."finascop_stock_itemmaster WHERE stit_StockEnabled = '1' AND stit_ID = '$itemId'";
//$result_stockEnabled = $db->query($stock_enabled_checkQuery);

            $result_stockEnabled = intval($val['stockEnabled']);

            if ($result_stockEnabled == 1) {
                $stock_register_data = array(
                    "stit_ID" => $val['item_id'],
                    "stre_Qty" => $val['quantity'],
                    "stre_isPurchase" => 1,
                    "br_id" => $_SESSION['admin']->finascop_current_branch_id,
                    "stre_invNo" => $stockReg_Number,
                    "stre_RefInvId" => $puen_Id,
                    "stre_Date" => $invDate
                );

                $status = $db->perform(FINASCOP_DB . "finascop_stock_register", $stock_register_data);
            } else {
                $item_id = $val['item_id'];
                $qty = intval($val['quantity']);
                $integrity_key = sha1(microtime(true) . mt_rand(10000, 90000));
                $br_id = $_SESSION['admin']->finascop_current_branch_id;
                $qry = "UPDATE  " . FINASCOP_DB . "finascop_stock_branch SET stbr_CurrentStock = stbr_CurrentStock + {$qty}, "
                        . "stbr_updated_on = '{$integrity_key}'"
                        . "WHERE stit_ID = {$item_id} AND br_Id = {$br_id} ";
                $status = $db->query($qry);
            }
        }



        $status = $db->query('commit');
        if ($status == 1) {
            echo '{"success":true,"msg":"Data saved successfully."}';
            exit;
        } else {
            echo '{"success":false,"error":"Error while saving data."}';
            exit;
        }
        break;

    case 'listPurchaseReturnable':
        $curr_branch = $_SESSION['admin']->finascop_current_branch_id;
        $filter_itemName = $_POST['filter_item'];
        $filter_itemId = $_POST['filter_itemId'];
        $filter_InvNumber = $_POST['filter_InvNumber'];
        $showReturned = $_POST['filter_ShowReturned'];
        $filter_ShowReturned = ($showReturned == 'true' ? 1 : 0);

        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];


        $item_con = "";
        $invNo_con = "";
        $showReturned_con = "";
        $filtercond = "";

        if ($filter_itemId > 0) {
            $item_con = " AND stit_ID = " . intval($_POST['filter_itemId']) . " ";
        }

        if ($filter_InvNumber != "") {
            $invNo_con = " AND a.prab_RefId IN (SELECT fpi.puen_Id AS ID FROM " . FINASCOP_DB . "finascop_purchase_invoice fpi "
                    . "WHERE fpi.puen_InvoiceNo LIKE '%$filter_InvNumber%' AND fpi.br_id = '$curr_branch'"
                    . " UNION SELECT fsi.saen_Id AS ID FROM " . FINASCOP_DB . "finascop_sales_invoice fsi "
                    . "WHERE fsi.saen_InvoiceNo LIKE '%$filter_InvNumber%' AND fsi.br_id = '$curr_branch')";
        }

        if ($filter_ShowReturned == 1) {

            $showReturned_con = " AND a.prab_IsActive = 0";
        }
        if ($filter_ShowReturned == 0) {
            $showReturned_con = " AND a.prab_IsActive = 1 ";
        }

        if ($_POST['filter_FromDate'] != "" && $_POST['filter_ToDate'] != "") {

            $fromdate = explode('/', $_POST['filter_FromDate']);

            $todate = explode('/', $_POST['filter_ToDate']);

            $filter_FromDate = date("Y-m-d", mktime(0, 0, 0, $fromdate[1], $fromdate[0], $fromdate[2]));

            $filter_ToDate = date("Y-m-d", mktime(0, 0, 0, $todate[1], $todate[0], $todate[2]));

            $filtercond = " AND a.prab_RecordDate BETWEEN '$filter_FromDate' AND  '$filter_ToDate'";
        }

        $qry = "select a.prab_Id as prab_Id,a.prab_RecordDate as prab_RecordDate,a.prab_updated_on as prab_updated_on,"
                . "(SELECT c.stit_itemName FROM " . FINASCOP_DB . "finascop_stock_itemmaster c WHERE c.stit_ID = a.stit_ID) as stit_ID,"
                . "a.prab_Qty as prab_Qty,a.prab_IsActive as prab_IsActive,"
                . "(SELECT fpi.puen_InvoiceNo FROM " . FINASCOP_DB . "finascop_purchase_invoice fpi "
                . "WHERE fpi.puen_Id = a.prab_RefId UNION SELECT fsi.saen_InvoiceNo FROM " . FINASCOP_DB . "finascop_sales_invoice fsi "
                . "WHERE fsi.saen_Id = a.prab_RefId) as RefInvNo,"
                . "(IF(a.prab_RefIsPurchaseReturn = 1,'Purchase return','Sales Return')) as prab_RefIsPurchaseReturn,"
                . "(SELECT concat(b.FirstName,' ',b.LastName) from " . FINASCOP_DB . "finascop_usr_profile b WHERE b.UserId = a.prab_EntryBy) as prab_EntryBy"
                . " from " . FINASCOP_DB . "finascop_purchase_returnable a WHERE a.br_id = '$curr_branch'"
                . "{$item_con}{$invNo_con}{$filtercond}{$showReturned_con}"
                . " ORDER BY a.prab_RecordDate DESC LIMIT $rec_start,$rec_limit ";

        $countQuery = "SELECT COUNT(*) FROM " . FINASCOP_DB . "finascop_purchase_returnable a WHERE a.br_id= '{$curr_branch}'";
        $count = $db->getItemFromDB($countQuery);

        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        break;

    case 'getInvoiceSliderItems':
        $qry = "SELECT (SELECT stit_itemName FROM " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID = paii_itemID) AS item, paii_itemID AS item_id, "
                . "(SELECT stit_MRP FROM " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID = paii_itemID) AS mrp, "
                . "paii_Rate AS rate, paii_itemQty AS qty, paii_IGST AS igst, paii_CGST AS cgst, paii_SGST AS sgst, "
                . "(SELECT stit_HSNCode FROM " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID = paii_itemID) AS hsncode,"
                . "paii_Rate * paii_itemQty as amt_bf_tax,paii_Rate * paii_itemQty + paii_IGST + paii_CGST + paii_SGST as amt_af_tax"
                . " FROM " . FINASCOP_DB . "finascop_purchase_invoice_items fpii INNER JOIN " . FINASCOP_DB . "finascop_purchase_invoice fpi "
                . "ON fpii.puen_Id = fpi.puen_Id WHERE fpi.puen_Id = '{$_POST['puen_Id']}'";

        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . count($items) . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'saveEditedData':
        $invoice_date = explode('/', $_POST['invoice_date']);
        date_default_timezone_set('Asia/Kolkata');
        $invoice_date = date("Y-m-d", mktime(0, 0, 0, $invoice_date[1], $invoice_date[0], $invoice_date[2]));
        $integrity_key = sha1(microtime(true) . mt_rand(10000, 90000));

        $purchase_orderdate = explode('/', $_POST['purchase_orderdate']);
        date_default_timezone_set('Asia/Kolkata');
        $purchase_orderdate = date("Y-m-d", mktime(0, 0, 0, $purchase_orderdate[1], $purchase_orderdate[0], $purchase_orderdate[2]));
        $data = array(
            "puen_InvoiceDate" => $invoice_date,
            "puen_VendorPurchaseOrder" => $_POST['purchase_orderno'],
            "puen_VendorPurchaseOrderDate" => $purchase_orderdate,
            "puen_RefNo" => $_POST['referenceno'],
            "puen_updated_on" => $integrity_key
        );
        $items = array(
            "puen_InvoiceNo" => $_POST['invoiceno'],
            "puen_InvoiceDate" => $invoice_date,
            "puen_Vendorname" => $_POST['party'],
            "puen_VendorPurchaseOrder" => $_POST['purchase_orderno'],
            "puen_VendorPurchaseOrderDate" => $purchase_orderdate,
            "puen_RefNo" => $_POST['referenceno']
        );
        $con = "puen_Id='" . $_POST['puen_Id'] . "'";
        $status = $db->perform(FINASCOP_DB . "finascop_purchase_invoice", $data, 'update', $con);
        if ($status) {
            echo "{success: true,data:" . json_encode($items) . ",msg:'Updated Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }

        break;

    case 'getPurchaseReturnableGridData':

        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;

        $fromdate = explode('/', $_POST['item_search_from']);
        $fromDate = date("Y-m-d", mktime(0, 0, 0, $fromdate[1], $fromdate[0], $fromdate[2]));

        $todate = explode('/', $_POST['item_search_to']);
        $toDate = date("Y-m-d", mktime(0, 0, 0, $todate[1], $todate[0], $todate[2]));


        $itemName = $_POST['itemName'];
        $itemId = $_POST['itemId'];


        $qry = "SELECT(SELECT fsi.stit_itemName FROM finascop_stock_itemmaster fsi WHERE fsi.stit_ID = fpii.paii_itemID) AS itemname,"
                . "(SELECT stit_ID FROM finascop_stock_itemmaster fsi WHERE stit_ID = fpii.paii_itemID) AS stit_ID,"
                . "fpi.puen_InvoiceNo AS puen_InvNo, fpi.puen_InvoiceDate AS puen_InvDate,fpii.paii_itemQty AS paii_itemQty,fpi.puen_Id as refId,"
                . "(SELECT COALESCE(SUM(fpr.prab_Qty),0) FROM finascop_purchase_returnable fpr "
                . "WHERE fpr.stit_ID = fpii.paii_itemID AND fpr.prab_RefId = fpi.puen_Id) AS prab_Qty,"
                . "(IF((SELECT COUNT(stit_ID) FROM finascop_purchase_returnable fpr "
                . "WHERE fpr.stit_ID = '$itemId' AND fpr.prab_RefId = fpi.puen_Id)> 0,'Yes','No')) AS item_avialability,"
                . "fpi.puen_updated_on as puen_updated_on"
                . " FROM finascop_purchase_invoice fpi INNER JOIN finascop_purchase_invoice_items fpii ON fpi.puen_Id = fpii.puen_Id"
                . " WHERE fpii.paii_itemID = '$itemId' AND fpi.br_id = '$currentBranch'"
                . " AND fpi.puen_InvoiceDate BETWEEN '$fromDate' AND '$toDate' ORDER BY puen_InvoiceDate DESC";

        $items = $db->getMulipleData($qry, true);

        if (!empty($items)) {
            echo '{"totalCount":' . count($items) . ',"data":' . json_encode($items) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;

    case 'savePurchaseReturnable_ModifyData':
        $ref_id = $_POST['refId'];
        $toReturn = $_POST['mod_ToReturn'];
        $mod_Date = $_POST['mod_Date'];
        $mod_ItemName = $_POST['mod_ItemName'];
        $mod_ReturnableQty = $_POST['mod_ReturnableQty'];
        $itemId = $_POST['itemId'];
        $br_id = $_SESSION['admin']->finascop_current_branch_id;

        $prev_key = $_POST['previous_key'];
        $integrity_key = sha1(microtime(true) . mt_rand(10000, 90000));
        $prab_RefId = $_POST['refId'];
        $invno = $_POST['invoice_no'];
        if (!prabDataIntegrityIsOK($prev_key, $prab_RefId, $br_id)) {
            echo '{"success":false,"errors":"Current Invoice has been updated by another user, since you access the data."}';
            exit;
        }

        if (empty($data['prab_Id'])) {
            $prab_Id = getRandomRef();
        }

        $query = "UPDATE  " . FINASCOP_DB . "finascop_purchase_invoice SET puen_HasPurchaseReturns = 1,"
                . "puen_updated_on = '{$integrity_key}' WHERE puen_InvoiceNo = '{$_POST['invoice_no']}'";

        $status = $db->query($query);

        $mod_data = array(
            "prab_Id" => $prab_Id,
            "prab_RefId" => $ref_id,
            "prab_RecordDate" => $mod_Date,
            "stit_ID" => $itemId,
            "prab_Qty" => $toReturn,
            "prab_ReturnedQty" => $toReturn,
            "br_id" => $_SESSION['admin']->finascop_current_branch_id,
            "prab_EntryBy" => $_SESSION['admin']->UserId,
            "prab_IsActive" => 1,
            "prab_updated_on" => sha1(microtime(true) . mt_rand(10000, 90000))
        );
        $status = $db->perform(FINASCOP_DB . 'finascop_purchase_returnable', $mod_data);
        if ($status == true) {
            echo '{"success":true,"msg":"data modified successfully"}';
        } else {
            echo '{"success":false,"errors":"error in modified data"}';
        }
        break;

    case 'listPurchaseReturn':

        $curr_branch = $_SESSION['admin']->finascop_current_branch_id;
        $qry = "select a.puen_Id,a.pure_InvoiceNo as pure_InvoiceNo,a.pure_InvoiceDate as pure_InvoiceDate,a.pure_id as pure_id,"
                . "a.pure_TotalItems as pure_TotalItems,a.pure_TotalItemsQty as pure_TotalItemsQty,a.pure_NetAmt as pure_NetAmt,"
                . "(SELECT concat(FirstName,' ',LastName) from " . FINASCOP_DB . "finascop_usr_profile WHERE pure_EntryBy = UserId) as pure_EntryBy,"
                . "a.pure_EntryOn as pure_EntryOn,'YES' AS have_returned, puen_Id AS invID,pure_updated_on as pure_updated_on "
                . " from " . FINASCOP_DB . "finascop_purchase_return a WHERE a.br_id = '$curr_branch' AND a.pure_IsCancelled = 0 ";

        $data = $db->getMultipleData($qry, true);

        $total = "select SUM(pure_TotalItems) AS returned_item_total,SUM(pure_TotalItemsQty) as total_items_qty, SUM(pure_NetAmt) as totalamount "
                . " from " . FINASCOP_DB . "finascop_purchase_return WHERE br_id = '$curr_branch' AND pure_IsCancelled = 0";
        $coltotal = $db->getFromDB($total, true);

        $result = [];
        foreach ($data as $key => $value) {

            foreach ($coltotal as $k => $v) {

                $value[$k] = $v;
                $result[$key] = $value;
            }
        }

        echo '{"success":true,"data":' . json_encode($result) . '}';
        break;

    case 'removePurchaseReturnable':
        $del_id = $_POST['remove_PRid'];
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        $prev_key = $_POST['prev_key'];
        //$integrity_key = sha1(microtime(true) . mt_rand(10000, 90000));
        if (!prabDataIntegrityIsOK($prev_key, $del_id, $branch_id)) {
            echo '{"success":false,"msg":"Current sales return invoice has been updated by another user, since you access the data."}';
            exit();
        }
        $qry = "DELETE FROM  " . FINASCOP_DB . "finascop_purchase_returnable WHERE prab_Id = '$del_id' AND br_id = '$branch_id'";

// $qry = "UPDATE  " . FINASCOP_DB . "finascop_purchase_returnable SET prab_IsActive = 1 WHERE prab_Id = '$del_id' AND br_id = '$branch_id' ";

        $status = $db->query($qry);
        if ($status == true) {
            echo '{"success":true,"msg":"Data Removed Successfully"}';
        } else {
            echo '{"success":fasle,"msg":"Error occured while removing this data"}';
        }
        break;

    case 'getPurchaseReturnAddData':

        $filter_invNo = $_POST['filter_invNo'];

        $rec_limit = empty($_POST['limit']) ? 14 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

        $from_date = $_POST['invNo_search_from'];

        $to_date = $_POST['invNo_search_to'];

        $curr_branch = $_SESSION['admin']->finascop_current_branch_id;

        if ($filter_invNo != "") {
            $inv_con = " AND puen_InvoiceNo LIKE '%$filter_invNo%' ";
        }
        if ($from_date != "") {

            $fromDate = explode('/', $_POST['invNo_search_from']);
            $invNo_search_from = date("Y-m-d", mktime(0, 0, 0, $fromDate[1], $fromDate[0], $fromDate[2]));

            $toDate = explode('/', $_POST['invNo_search_to']);
            $invNo_search_to = date("Y-m-d", mktime(0, 0, 0, $toDate[1], $toDate[0], $toDate[2]));

            $date_cond = " AND puen_InvoiceDate BETWEEN '$invNo_search_from' AND  '$invNo_search_to'";
        }
        if ($from_date == "") {
            $toDate = explode('/', $_POST['invNo_search_to']);
            $invNo_search_to = date("Y-m-d", mktime(0, 0, 0, $toDate[1], $toDate[0], $toDate[2]));
            $date_cond = " AND puen_InvoiceDate BETWEEN '$from_date' AND  '$invNo_search_to' ";
        }


        $qry = "SELECT puen_Id AS invID,puen_InvoiceNo AS puRe_InvNo,puen_InvoiceDate AS puRe_InvDate,"
                . "puen_TotalItems AS puRe_totalitems,"
                . "br_id AS br_id,(IF(puen_HasPurchaseReturns = 0,'NO','YES')) AS have_returned,puen_updated_on AS puen_updated_on "
                . "FROM " . FINASCOP_DB . "finascop_purchase_invoice WHERE br_id = '$curr_branch' {$inv_con}{$date_cond}"
                . "ORDER BY puen_InvoiceDate DESC LIMIT $rec_start,$rec_limit";

        $data = $db->getMultipleData($qry, true);

        $count_qry = "SELECT COUNT(*) FROM " . FINASCOP_DB . "finascop_purchase_invoice WHERE br_id = '$curr_branch' {$inv_con}{$date_cond} ";
        $count = $db->getItemFromDB($count_qry);

        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;

    case 'listpurchasegridstore':
        $invoice_no = $_POST['invoice_no'];
        $invID = $_POST['invID'];

        $qry = "SELECT (SELECT stit_itemName FROM " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID = fpii.paii_itemID) AS addPR_Item,"
                . "fpii.paii_itemID AS itemId,fpii.paii_itemQty AS addPR_ItemQty,fpii.paii_Rate AS addPR_Rate,"
                . "fpii.puen_Id AS purchaseId,(SELECT COUNT(fpr.prab_IsActive) FROM " . FINASCOP_DB . "finascop_purchase_returnable fpr WHERE "
                . "fpr.stit_ID = fpii.paii_itemID AND fpr.prab_RefId = '{$invID}') AS prab_IsActive,(SELECT fpi.stpa_id FROM " . FINASCOP_DB . "finascop_purchase_invoice fpi "
                . "WHERE fpi.puen_Id = fpii.puen_Id) AS partyId,"
                . "(SELECT fprli.purd_itemReturnedQty FROM " . FINASCOP_DB . "finascop_purchase_return_items fprli "
                . "INNER JOIN " . FINASCOP_DB . "finascop_purchase_return fprn ON fprli.pure_id = fprn.pure_id  WHERE fprn.puen_Id = fpii.puen_Id "
                . "AND fprn.pure_IsCancelled = 0 AND fprli.purd_itemID = fpii.paii_itemID ) AS addPR_ReturnQty,"
                . "(SELECT fprli.purd_IGST " . FINASCOP_DB . "FROM finascop_purchase_return_items fprli INNER JOIN " . FINASCOP_DB . "finascop_purchase_return fprn "
                . "ON fprli.pure_id = fprn.pure_id WHERE fprn.puen_Id = fpii.puen_Id AND fprn.pure_IsCancelled = 0 "
                . "AND fprli.purd_itemID = fpii.paii_itemID ) AS addPR_IGST,"
                . "(SELECT fprli.purd_CGST FROM " . FINASCOP_DB . "finascop_purchase_return_items fprli INNER JOIN " . FINASCOP_DB . "finascop_purchase_return fprn "
                . "ON fprli.pure_id = fprn.pure_id WHERE fprn.puen_Id = fpii.puen_Id AND fprn.pure_IsCancelled = 0 "
                . "AND fprli.purd_itemID = fpii.paii_itemID ) AS addPR_CGST,"
                . "(SELECT fprli.purd_SGST FROM " . FINASCOP_DB . "finascop_purchase_return_items fprli INNER JOIN " . FINASCOP_DB . "finascop_purchase_return fprn "
                . "ON fprli.pure_id = fprn.pure_id WHERE fprn.puen_Id = fpii.puen_Id AND fprn.pure_IsCancelled = 0 "
                . "AND fprli.purd_itemID = fpii.paii_itemID ) AS addPR_SGST,"
                . "(SELECT fprn.pure_NetAmt FROM " . FINASCOP_DB . "finascop_purchase_return fprn INNER JOIN " . FINASCOP_DB . "finascop_purchase_return_items fprli "
                . "ON fprli.pure_id = fprn.pure_id WHERE fprn.puen_Id = fpii.puen_Id AND fprn.pure_IsCancelled = 0 "
                . "AND fprli.purd_itemID = fpii.paii_itemID ) AS addPR_Amount "
                . "FROM " . FINASCOP_DB . "finascop_purchase_invoice_items fpii INNER JOIN " . FINASCOP_DB . "finascop_purchase_invoice fpi ON fpii.puen_Id = fpi.puen_Id "
                . "WHERE  fpii.puen_Id = '{$invID}'";

        $data = $db->getMulipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }

        break;
//
    case 'listpurchasereturnviewstore':
        $invoice_no = $_POST['invoice_no'];
        $invID = $_POST['invID'];
        $view = $_POST['view'];
        $checkedIds = "";

        if ($view == 1) {

            $checkedIds = "(SELECT GROUP_CONCAT(sel.prab_Id) FROM (SELECT GROUP_CONCAT(fpr.prab_Id) AS prab_Id, 1 AS gr FROM " . FINASCOP_DB . "finascop_purchase_invoice_items fpis "
                    . "INNER JOIN " . FINASCOP_DB . "finascop_purchase_returnable fpr ON fpis.puen_Id = fpr.prab_RefId AND fpr.stit_ID = fpis.paii_itemID "
                    . "WHERE fpr.prab_RefId = '{$invID}' AND fpr.stit_ID = fpis.paii_itemID AND fpr.pure_id != '' GROUP BY fpis.puen_Id "
                    . "UNION SELECT GROUP_CONCAT(fpr.prab_Id) AS prab_Id,"
                    . " 1 AS gr FROM " . FINASCOP_DB . "finascop_sales_invoice_items fsii INNER JOIN " . FINASCOP_DB . "finascop_purchase_returnable fpr ON fsii.saen_Id = fpr.prab_RefId "
                    . "WHERE fpr.stit_ID = fsii.saii_itemID AND fpr.pure_id != '' GROUP BY fsii.saen_Id ) AS sel "
                    . "GROUP BY gr) AS checkedIds,";
        }

        $qry = "SELECT (SELECT stit_itemName FROM " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID = fsii.paii_itemID) AS addPR_Item,"
                . "(SELECT stit_StockEnabled from " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID = fsii.paii_itemID) AS stock_enabled,"
                . "fsii.paii_itemID AS itemId,fsii.paii_itemQty AS addPR_ItemQty,fsii.paii_Rate AS addPR_Rate,"
                . "fsii.puen_Id AS purchaseId,(SELECT COUNT(fpr.prab_IsActive) FROM " . FINASCOP_DB . "finascop_purchase_returnable fpr "
                . "WHERE fpr.stit_ID = fsii.paii_itemID AND fpr.prab_RefId = '{$invID}') AS prab_IsActive,"
                . "(SELECT fpi.stpa_id FROM " . FINASCOP_DB . "finascop_purchase_invoice fpi WHERE fpi.puen_Id = fsii.puen_Id) AS partyId,{$checkedIds}"
                . "(SELECT fd.st_Id FROM " . FINASCOP_DB . "finascop_district fd "
                . "WHERE fd.dst_Id = (SELECT dst_Id FROM " . FINASCOP_DB . "finascop_stock_party fsp WHERE fsp.stpa_id=fsi.stpa_id)) AS state_id,"
                . "purd_itemReturnedQty AS addPR_ReturnQty,purd_IGST AS addPR_IGST, purd_CGST AS addPR_CGST,"
                . "purd_SGST AS addPR_SGST, "
                . "fsii.paii_Rate*purd_itemReturnedQty+purd_IGST+purd_CGST+purd_SGST+purd_CGST AS addPR_Amount "
                . "FROM " . FINASCOP_DB . "finascop_purchase_invoice_items fsii INNER JOIN " . FINASCOP_DB . "finascop_purchase_invoice fsi "
                . "ON fsii.puen_Id = fsi.puen_Id INNER JOIN " . FINASCOP_DB . "finascop_purchase_return fsr ON fsr.puen_Id = fsi.puen_Id "
                . "INNER JOIN " . FINASCOP_DB . "finascop_purchase_return_items fsri ON fsr.pure_id = fsri.pure_Id "
                . "AND fsri.purd_ItemID = fsii.paii_itemID AND pure_IsCancelled=0 WHERE '{$invID}' = fsii.puen_Id";

        $data = $db->getMulipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;

    case 'getBranchStateId':
        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;
        $qry = "select br_State from " . FINASCOP_DB . "finascop_branch WHERE br_ID = '{$currentBranch}' ";
        $data = $db->getFromDB($qry, true);
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            echo '{"success":false,"msg":"No data found"}';
        }
        break;

    case 'getPurchaseReturnItemdetails':

        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;
        $branch_qry = "select br_State from " . FINASCOP_DB . "finascop_branch WHERE br_ID = '{$currentBranch}' ";
        $branch_state_id = $db->getFromDB($branch_qry, true);
        $item_id = $_POST['item_id'];
        $party_stateid = $_POST['party_state_id'];
        $party_state_id = ($party_stateid == "" ? $branch_state_id : $_POST['party_state_id']);

        if ($branch_state_id == $party_state_id) {
            $qry = "select stit_GST, 0 AS IGST, ((stit_GST/100)*0.5) AS CGST,((stit_GST/100)*0.5) AS SGST "
                    . "from " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID= '{$item_id}' ";
        } else {
            $qry = "select stit_MRP,stit_HSNCode,stit_GST, (stit_GST/100) AS IGST, 0 AS CGST, 0 AS SGST  "
                    . "from " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID= '{$item_id}' ";
        }
        $data = $db->getFromDB($qry, true);
        if ($data) {
            echo "{success:true,totalCount:" . count($data) . ",data:" . json_encode($data) . "}";
        } else {
            echo '{"success":false,"msg":"Data not inserted"}';
        }

        break;


    case 'listpurchaseReturnablegrid':
        $itemId = $_POST['itemID'];
        $purchaseId = $_POST['purchaseId'];
        $view = $_POST['view'];
        $show_con = "";
        if ($view == 1) {
            $show_con = " AND fpr.prab_IsActive = '0' AND fpr.pure_id != ''";
        }

        $qry = "SELECT (SELECT fsi.stit_itemName FROM " . FINASCOP_DB . "finascop_stock_itemmaster fsi WHERE fsi.stit_ID = fpr.stit_ID) "
                . "AS purchaseReturnableItem,"
                . "fpr.prab_Id AS purchaseReturnableNo,fpr.prab_Qty AS quantityReturned,"
                . "fpr.stit_ID AS itemId,fpr.prab_RefId AS refid,fpr.prab_Id AS purchaseReturnableId,"
                . "(IF(fpr.prab_RefIsPurchaseReturn = 1,'Purchase Returnable','Sales Return')) AS purchaseType "
                . "FROM " . FINASCOP_DB . "finascop_purchase_returnable fpr "
                . "INNER JOIN " . FINASCOP_DB . "finascop_purchase_invoice fpi ON fpi.puen_Id = fpr.prab_RefId "
                . "WHERE fpr.stit_ID = '{$itemId}' AND fpi.puen_Id  = '{$purchaseId}'"
                . "UNION "
                . "SELECT (SELECT fsit.stit_itemName FROM " . FINASCOP_DB . "finascop_stock_itemmaster fsit WHERE fsit.stit_ID = fpr.stit_ID) AS purchaseReturnableItem,"
                . "fpr.prab_Id AS purchaseReturnableNo,fpr.prab_Qty AS quantityReturned,fpr.stit_ID AS itemId,fpr.prab_RefId AS refid,"
                . "fpr.prab_Id AS purchaseReturnableId,(IF(fpr.prab_RefIsPurchaseReturn = 1,'Purchase Returnable','Sales Return')) AS purchaseType "
                . "FROM " . FINASCOP_DB . "finascop_purchase_returnable fpr INNER JOIN " . FINASCOP_DB . "finascop_sales_invoice fsi "
                . "ON fsi.saen_Id = fpr.prab_RefId WHERE fpr.stit_ID = '{$itemId}' {$show_con} ";




        $data = $db->getMultipleData($qry, true);
        if ($data) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"success":false,"msg":"Data fetching is failed"}';
        }

        break;

    case 'savePurchaseReturned':
        $db->query('begin');
        date_default_timezone_set('Asia/Kolkata');
        $br_id = intval($_SESSION['admin']->finascop_current_branch_id);
        $UserId = $_SESSION['admin']->Finascop_UserId;
        $curDate = date("Y-m-d");

        $prev_key = $_POST['puen_updated_on'];
        $integrity_key = sha1(microtime(true) . mt_rand(10000, 90000));
        $invID = $_POST['puen_Id'];
        if (!pureDataIntegrityIsOK($prev_key, $invID, $br_id)) {
            echo '{"success":false,"msg":"Current Invoice has been updated by another user, since you access the data."}';
            exit;
        }

        if (empty($data['pure_id'])) {
            $pure_id = getRandomRef();
        }


        $purchase_return_gridData = json_decode(stripslashes($_POST['purchase_return_gridData']), true);
        $purchase_return_total_gridData = json_decode(stripslashes($_POST['purchase_return_total_gridData']), true);
        $itemcount = 0;
        $tax = $sales_return_total_gridData[0]['addPR_IGST'] + $sales_return_total_gridData[0]['addPR_CGST'] + $sales_return_total_gridData[0]['addPR_SGST'];
        $invID = $_POST['puen_Id'];
        $purchaseReturnInvoice_Number = insertPurchaseReturnInvoiceNo();

        foreach ($purchase_return_gridData as $key => $val) {


            if ($val['addPR_ReturnQty'] != 0) {
                $purchasereturnGriddata = array(
                    "pure_Id" => $pure_id,
                    "purd_itemID" => $val['itemId'],
                    "purd_itemReturnedQty" => $val['addPR_ReturnQty'],
                    "purd_Rate" => $val['addPR_Rate'],
                    "purd_IGST" => $val['addPR_IGST'],
                    "purd_CGST" => $val['addPR_CGST'],
                    "purd_SGST" => $val['addPR_SGST'],
                );

                $itemId = $val['itemId'];
                $selectedId = explode(',', $val['checkedIds']);

                foreach ($selectedId as $key1) {

                    $invId_qry = "SELECT prab_RefId FROM  " . FINASCOP_DB . "finascop_purchase_returnable WHERE prab_Id = '$key1'";
                    $invId = $db->getItemFromDB($invId_qry, true);
                    $query = "UPDATE  " . FINASCOP_DB . "finascop_purchase_returnable SET pure_id = '$pure_id',prab_IsActive = '0' "
                            . "WHERE prab_Id = '{$key1}'";
                    $status = $db->query($query);

                    $allRefId_qry = "SELECT b.prab_Id,b.prab_RefId FROM  " . FINASCOP_DB . "finascop_purchase_returnable b WHERE prab_RefId = '$invId' "
                            . "AND stit_ID = '$itemId' UNION SELECT b.prab_Id,b.prab_RefId FROM  " . FINASCOP_DB . "finascop_purchase_returnable b "
                            . "INNER JOIN finascop_sales_return a ON b.prab_RefId = a.saen_Id WHERE  stit_ID = '$itemId' ";
                    $allRefIds = $db->getMultipleData($allRefId_qry, true);
                    foreach ($allRefIds as $key2 => $value) {

                        $active_change_query = "UPDATE  " . FINASCOP_DB . "finascop_purchase_returnable SET prab_IsActive = '0' "
                                . "WHERE prab_Id = '{$value['prab_Id']}'";
                        $status = $db->query($active_change_query);
                    }
                }


                $status = $db->perform(FINASCOP_DB . 'finascop_purchase_return_items', $purchasereturnGriddata);
                $itemcount = $itemcount + 1;

                $query = "UPDATE  " . FINASCOP_DB . "finascop_purchase_invoice SET puen_HasPurchaseReturns = 1,puen_updated_on = '{$integrity_key}' "
                        . "WHERE puen_InvoiceNo = '{$_POST['invoice_no']}'";
                $status = $db->query($query);
            }
        }

        $purchaseReturndata = array(
            "pure_id" => $pure_id,
            "pure_InvoiceNo" => $purchaseReturnInvoice_Number,
            "pure_InvoiceDate" => $curDate,
            "pure_TotalItems" => $itemcount,
            "pure_TotalItemsQty" => $purchase_return_total_gridData[0]['addPR_ReturnQty'],
            "puen_Id" => $_POST['puen_Id'],
            "br_id" => $_POST['br_id'],
            "pure_EntryBy" => $UserId,
            "pure_Tax" => $tax,
            "pure_NetAmt" => $_POST['net_amount'],
            "pure_EntryOn" => $curDate,
            "pure_updated_on" => sha1(microtime(true) . mt_rand(10000, 90000))
        );
        $status = $db->perform(FINASCOP_DB . 'finascop_purchase_return', $purchaseReturndata);

        $stockRegInvNo = generateNextDocNo('18', $br_id);

        foreach ($purchase_return_gridData as $key => $value) {
            if ($value['addPR_ReturnQty'] != 0 &&
                    $value['stock_enabled'] == 1) {
                $stockRegisterReturndata = array(
                    "stit_ID" => intval($value['itemId']),
                    "stre_Qty" => $value['addPR_ReturnQty'],
                    "stre_isPurchase" => 1,
                    "stre_InvNo" => $stockRegInvNo,
                    "br_id" => $br_id,
                    "stre_RefInvId" => $pure_id,
                    "stre_Date" => $curDate
                );
                $status = $db->perform(FINASCOP_DB . 'finascop_stock_register', $stockRegisterReturndata);
            } else {

                $itemid = intval($value['itemId']);
                $return_qty = intval($value['addPR_ReturnQty']);
                $integrity_key = sha1(microtime(true) . mt_rand(10000, 90000));
                $br_id = $_SESSION['admin']->finascop_current_branch_id;

                $qry = "UPDATE  " . FINASCOP_DB . "finascop_stock_branch SET stbr_CurrentStock = stbr_CurrentStock + {$return_qty}, "
                        . "stbr_updated_on = '{$integrity_key}'"
                        . "WHERE stit_ID = '{$itemid}' AND br_Id = '{$br_id}'";
                $status = $db->query($qry);
            }
        }
        $status = $db->query('commit');

        if ($status) {
            echo "{success: true, msg:'Data Saved'}";
        } else {
            echo "{success: false, errors: { msg: 'Error occured while saving data' }}";
        }

        break;

    case 'removePurchaseReturn':
        $db->query('begin');
        $can_id = $_POST['remove_PRid'];
        $puen_Id = $_POST['puen_Id'];
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        $prev_key = $_POST['prev_key'];
        $integrity_key = sha1(microtime(true) . mt_rand(10000, 90000));
        if (!pureDataIntegrityIsOK($prev_key, $can_id, $branch_id)) {
            echo '{"success":false,"msg":"Current purchase return invoice has been updated by another user, since you access the data."}';
            exit();
        }

        $purchaseReturnableIds_qry = "SELECT b.prab_Id, b.prab_RefId FROM " . FINASCOP_DB . "finascop_purchase_returnable b "
                . "INNER JOIN " . FINASCOP_DB . "finascop_purchase_return a ON b.prab_RefId = a.puen_Id "
                . "WHERE a.pure_IsCancelled = 0 AND a.pure_Id = '{$can_id}' AND b.pure_id = '{$can_id}' "
                . "UNION "
                . "SELECT b.prab_Id, b.prab_RefId FROM finascop_purchase_returnable b INNER JOIN finascop_sales_return a "
                . "ON b.prab_RefId = a.saen_Id WHERE b.pure_id = '{$can_id}'";
        $result = $db->getMultipleData($purchaseReturnableIds_qry, true);
        if ($result == true) {

            foreach ($result as $key => $val) {


                $query = "UPDATE " . FINASCOP_DB . "finascop_purchase_returnable SET pure_id = '', prab_IsActive = 1, prab_ReturnedQty = 0"
                        . " WHERE prab_Id = '{$val['prab_Id']}' AND pure_id != '' ";

                $status = $db->query($query);
            }
        }

        $stockregId = "SELECT stre_id, stre_ApprovedBy FROM " . FINASCOP_DB . "finascop_stock_register WHERE stre_RefInvId = '{$can_id}' AND stre_ApprovedBy > 0";
        $result = $db->getMultipleData($stockregId, true);

        if ($result == true) {
            $val_StockUpdation = "SELECT purd_itemID, purd_itemReturnedQty FROM " . FINASCOP_DB . "finascop_purchase_return_items "
                    . "WHERE pure_Id = '{$can_id}'";
            $resultForStockUpdation = $db->getMultipleData($val_StockUpdation, true);
            foreach ($result as $key => $value) {
                if ($value['stre_ApprovedBy'] > 0) {

                    if ($resultForStockUpdation == true) {
                        foreach ($resultForStockUpdation as $key => $val) {
                            $itemid = intval($val['purd_itemID']);
                            $return_qty = intval($val['purd_itemReturnedQty']);


                            $qry = "UPDATE " . FINASCOP_DB . "finascop_stock_branch SET stbr_CurrentStock = stbr_CurrentStock - {$return_qty}, "
                                    . "stbr_updated_on = '{$integrity_key}'"
                                    . "WHERE stit_ID = '{$itemid}' AND br_Id = '{$branch_id}'";
                            $status = $db->query($qry);
                        }
                    }
                }
            }
        } else {
            $query = "DELETE FROM " . FINASCOP_DB . "finascop_stock_register WHERE stre_RefInvId = '$can_id' AND br_id = '$branch_id'";
            $status = $db->query($query);
        }



        $qry = "UPDATE " . FINASCOP_DB . "finascop_purchase_return SET pure_IsCancelled = 1 "
                . "WHERE pure_id = '$can_id' AND br_id = '$branch_id' ";
        $status = $db->query($qry);


        $query = "UPDATE " . FINASCOP_DB . "finascop_purchase_invoice SET puen_HasPurchaseReturns = 0,puen_updated_on = '{$integrity_key}' "
                . "WHERE puen_Id = '$puen_Id'";
        $status = $db->query($query);

        $status = $db->query('commit');

        if ($status == 1) {
            echo '{"success":true,"msg":"Data Removed Successfully"}';
        } else {
            echo '{"success":fasle,"msg":"Error occured while removing this data"}';
        }
        break;

    case 'getPurchaseReturn_details':
        $invID = $_POST['invID'];
        $qry = "SELECT pure_InvoiceNo as pure_invNo FROM " . FINASCOP_DB . "finascop_purchase_return "
                . "WHERE pure_IsCancelled = 0 AND puen_Id = '{$invID}'";

        $pure_InvoiceNo = $db->getFromDB($qry, true);

        if ($pure_InvoiceNo) {
            echo '{"success":true,"data":' . json_encode($pure_InvoiceNo) . ',"msg":"error"}';
        } else {
            echo '{"success":false,"msg":"Data fetching is failed"}';
        }

        break;
    case 'vendorPOSearchGridStore':
        $search_field = $_POST['search_field'];
        $type = $_POST['type'];
        switch ($type) {
            case 'Vendor PO':
                $countQuery = "SELECT COUNT(*) FROM finascop_purchase_order WHERE fpo_Active = 1 AND fpo_vendorName LIKE '{$search_field}%'";
                $listQuery = "SELECT fpo_id as search_id,fpo_vendorName as search_name,fpo_poNumber,DATE_FORMAT(fpo_poDate,'%d-%m-%Y') as fpo_poDate FROM finascop_purchase_order WHERE fpo_Active = 1 AND fpo_vendorName LIKE '{$search_field}%'";
                break;
        }
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'listPodetailsStore':
        $poId = $_POST['poId'];
        if ($poId > 0) {
            $countQuery = "SELECT COUNT(*) FROM finascop_purchase_order_details fpod WHERE fpod_fpoId = {$poId} ";
            $listQuery = "SELECT fpod_itemname,fpod_itemqty,fpod_itemoffrrate,fpod_itemmrp,fpod_itemoffrqty,fpod_effectiverate,fpod_totalqty,fpod_receivedqty,"
                    . "(SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = fpod_itemid) as tax_percentage,(SELECT stit_HSN_code FROM finascop_stock_itemmaster WHERE stit_ID = fpod_itemid) as hsn FROM finascop_purchase_order_details fpod WHERE fpod_fpoId = {$poId}";
            $db->printGridJson($countQuery, $listQuery);
        }

        break;
}


