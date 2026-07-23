<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");

function insertInvoiceNo($invoice_id) {
    global $db;
    $br_Id = $_SESSION['admin']->finascop_current_branch_id;

    $lastInvoiceNo = generateNextDocNo('16', $br_Id);
  
    $data = array(
        "puen_InvoiceNo" => $lastInvoiceNo
    );
    $con = "puen_Id = '{$invoice_id}' ";
    $status = $db->perform(FINASCOP_DB . "finascop_purchase_invoice", $data, 'update', $con);

    return $lastInvoiceNo;
}

//function insertStockRegNo() {
//    global $db;
//    $br_Id = $_SESSION['admin']->finascop_current_branch_id;
//    
//    $stockReg_Number = generateNextDocNo('9', $br_Id);
//    
//    return $stockReg_Number;
//}

function insertPurchaseReturnInvoiceNo() {
    global $db;
    $br_Id = $_SESSION['admin']->finascop_current_branch_id;
 
    $invoice_Number = generateNextDocNo('17', $br_Id);
    return $invoice_Number;
}

function prabDataIntegrityIsOK($prev_key, $prab_RefId, $br_id) {
    global $db;
    $entry = "SELECT COUNT(*) FROM finascop_purchase_returnable WHERE prab_RefId = '{$prab_RefId}'";
    $count = $db->getItemFromDB($entry);
    if ($count > 0) {
        $qry = "SELECT fpr.prab_updated_on FROM " . FINASCOP_DB . "finascop_purchase_returnable fpr "
                . "WHERE fpr.prab_RefId = '{$prab_RefId}' and fpr.br_id = '{$br_id}'";
        $curr_key = $db->getItemFromDB($qry);

        if ($curr_key == $prev_key) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

function pureDataIntegrityIsOK($prev_key, $invID, $br_id) {
    global $db;
    $entry = "SELECT COUNT(*) FROM finascop_purchase_return WHERE puen_Id = '{$invID}'";
    $count = $db->getItemFromDB($entry);
    if ($count > 0) {
        $qry = "SELECT fpi.puen_updated_on FROM " . FINASCOP_DB . "finascop_purchase_invoice fpi "
                . "WHERE fpi.puen_Id = '{$invID}' and fpi.br_id = '{$br_id}'";
        $curr_key = $db->getItemFromDB($qry);

        if ($curr_key == $prev_key) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}
