<?php
require_once(INCLUDE_PATH . "/finascop_common_functions.php");

function insertInvoiceNo($invoice_id) {
    global $db;
    $br_Id = $_SESSION['admin']->finascop_current_branch_id;
   

    $lastInvoiceNo = generateNextDocNo('19', $br_Id);

    $data = array(
        "saen_InvoiceNo" => $lastInvoiceNo
    );
    $con = "saen_Id = '{$invoice_id}' ";
    $status = $db->perform(FINASCOP_DB . "finascop_sales_invoice", $data, 'update', $con);

    return $lastInvoiceNo;
}

function insertSalesReturnInvoiceNo($invoice_id) {
    global $db;
    $br_Id = $_SESSION['admin']->finascop_current_branch_id;

    $lastInvoiceNo = generateNextDocNo('20', $br_Id);
    $data = array(
        "sare_InvoiceNo" => $lastInvoiceNo
    );
    $con = "sare_id = '{$invoice_id}' ";
    $status = $db->perform(FINASCOP_DB . "finascop_sales_return", $data, 'update', $con);

    return $lastInvoiceNo;
}

function saveItems($invoice_id, $itemData, $br_id) {
    global $db;
    $branch_id = $br_id;
    $itemData = json_decode(stripslashes($itemData), true);
    foreach ($itemData as $k => $v) {
        $data = array(
            'saen_Id' => $invoice_id,
            'saii_itemID' => $v['item_id'],
            'saii_itemQty' => $v['quantity'],
            'saii_Rate' => $v['rate'],
            'saii_IGST' => $v['igst'],
            'saii_CGST' => $v['cgst'],
            'saii_SGST' => $v['sgst']
        );

        $db->perform(FINASCOP_DB . 'finascop_sales_invoice_items', $data);

        $item_id = $v['item_id'];
        $br_id = $_POST['br_id'];
        $qty = $v['quantity'];
        $integrity_key = sha1(microtime(true) . mt_rand(10000, 90000));

        $qry = "UPDATE  " . FINASCOP_DB . "finascop_stock_branch SET stbr_CurrentStock = stbr_CurrentStock - {$qty}, "
                . "stbr_updated_on = '{$integrity_key}'"
                . "WHERE stit_ID = {$item_id} AND br_Id = {$branch_id} ";
        $status = $db->query($qry);
    }
}
//
//function insertStockRegNo() {
//    global $db;
//    $br_Id = $_SESSION['admin']->finascop_current_branch_id;
//    $brId_with_threeDigit = str_pad($br_Id, 3, 0, STR_PAD_LEFT);
//    $stockReg_Number = $db->getItemFromDB("SELECT MAX(CAST(SUBSTRING(stre_InvNo,5,6) AS UNSIGNED)) from " . FINASCOP_DB . "finascop_stock_register");
//    if ($stockReg_Number > 0) {
//        $stockReg_Number += 1;
//        $six_number = str_pad($stockReg_Number, 6, 0, STR_PAD_LEFT);
//        $stockReg_Number = "R" . $brId_with_threeDigit . $six_number;
//    } else {
//        $stockReg_Number = "R" . $brId_with_threeDigit . "000001";
//    }
//    return $stockReg_Number;
//}

function salesTableDataIntegrityIsOK($prev_key, $saen_Id, $br_id) {
    global $db;
    $qry = "SELECT saen_updated_on FROM " . FINASCOP_DB . "finascop_sales_invoice fsi WHERE fsi.saen_Id = '{$saen_Id}' and fsi.br_id = '{$br_id}'";
    $curr_key = $db->getItemFromDB($qry);
    if ($curr_key == $prev_key) {
        return true;
    } else {
        return false;
    }
}

function salesReturnTableDataIntegrityIsOK($prev_key, $sare_id, $br_id) {
    global $db;
    $qry = "SELECT sare_updated_on FROM " . FINASCOP_DB . "finascop_sales_return fsr WHERE fsr.sare_id = '{$sare_id}' and fsr.br_id = '{$br_id}'";
    $curr_key = $db->getItemFromDB($qry);

    if ($curr_key == $prev_key) {
        return true;
    } else {
        return false;
    }
}
