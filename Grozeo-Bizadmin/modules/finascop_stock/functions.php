<?php

function getChildrenIds($parentId) {
    global $db;
    $childrenIds = [];
    $query = "SELECT stgp_groupID FROM " . FINASCOP_DB . "finascop_stock_group WHERE stgp_parentGpID = {$parentId}";
    $result = $db->getMultipleData($query);
    //echo $query;
    //print_r($result);
    if (!empty($result)) {
        foreach ($result as $val) {
            $childrenIds = array_merge($childrenIds, getChildrenIds($val));
        }
        return array_merge($result, $childrenIds);
    } else {
        return [];
    }
}

//$group_names = $db->getMultipleData("SELECT stgp_groupID,COALESCE(stgp_groupName,' ') AS stgp_groupName  
//		from " . FINASCOP_DB . "finascop_stock_group                                             
//		WHERE stgp_parentGpID=0");

function buildtree(array $groups, $parentId) {
    //global $db;
    $branch = array();
    foreach ($groups as $element) {
        if ($element['stgp_parentGpID'] == $parentId) {
            $element['id'] = $element['stgp_groupID'];
            $element['text'] = $element['stgp_groupName'];
            $element['children'] = '';
            $element['leaf'] = true;
            $element['draggable'] = false;
            //$element['cls'] = 'finascop_child-group';
            $children = buildTree($groups, $element['stgp_groupID']);
            if ($children) {
                $element['draggable'] = false;
                $element['children'] = $children;
                $element['leaf'] = false;
                //$element['cls'] = 'finascop_parent-group';
            }

            $branch[] = $element;
        }
    }

    return $branch;
}

function getNextConversionNo() {
    global $db;
    $lastInvoiceNo = $db->getItemFromDB("SELECT MAX(CAST(stco_No AS UNSIGNED)) from " . FINASCOP_DB . "finascop_stock_conversion");
    if ($lastInvoiceNo > 0) {
        $lastInvoiceNo += 1;
    } else {
        $lastInvoiceNo = 1;
    }
    return $lastInvoiceNo;
}

function insertInvoiceNo($invoice_id) {
    global $db;
    $lastInvoiceNo = $db->getItemFromDB("SELECT MAX(saen_InvoiceNo) from " . FINASCOP_DB . "finascop_sales_invoice");
    if ($lastInvoiceNo > 0) {
        $lastInvoiceNo += 1;
    } else {
        $lastInvoiceNo = 1;
    }
    $data = array(
        "saen_InvoiceNo" => $lastInvoiceNo
    );
    $con = 'saen_Id = ' . intval($invoice_id);
    $status = $db->perform(FINASCOP_DB . "finascop_sales_invoice", $data, 'update', $con);
    return $status;
}

function saveItems($invoice_id, $itemData) {
    global $db;
    $itemData = json_decode(stripslashes($itemData), true);
    foreach ($itemData as $k => $v) {
        $data = array(
            'init_invoiceID' => $invoice_id,
            'init_itemID' => $v['item_id'],
            'init_itemQty' => $v['quantity'],
            'init_Rate' => $v['rate']
        );
        $db->perform(FINASCOP_DB . 'finascop_sales_invoice_items', $data);
    }
}

function stockTableDataIntegrityIsOK($prev_key, $item_id, $br_id) {
    global $db;
    $qry = "SELECT stbr_updated_on FROM " . FINASCOP_DB . "finascop_stock_branch fsb WHERE fsb.stit_ID = {$item_id} and fsb.br_Id = {$br_id}";
    $curr_key = $db->getItemFromDB($qry);

    if ($curr_key == $prev_key) {
        return true;
    } else {
        return false;
    }
}

//function updateUniqueItemTable($uid, $uniData) {
//    global $db;
//    $uniData['fsi_category_id'] = addslashes($uniData['fsi_category_id']);
//    $chkUnqExiste = $db->getFromDB("SELECT fsi_uid,fsi_count FROM " . FINASCOP_DB . "finascop_stock_uniqueitem WHERE fsi_item_id = {$uniData['fsi_item_id']} "
//            . "  AND fsi_brand_id = {$uniData['fsi_brand_id']} AND fsi_category_id = {$uniData['fsi_category_id']} AND fsi_variant = '{$uniData['fsi_variant']}'", true);
//    $uidCount = $db->getItemFromDB("SELECT fsi_count FROM " . FINASCOP_DB . "finascop_stock_uniqueitem WHERE fsi_uid = {$uid}");
//
//    if ($uid == 0) {
//
//        if (intval($chkUnqExiste['fsi_uid']) > 0) {
//            $fsuidata['fsi_count'] = intval($chkUnqExiste['fsi_count']) + 1;
//            $status = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $fsuidata, 'update', " fsi_uid = {$chkUnqExiste['fsi_uid']}");
//            $stit_fsiuid['fsi_uid'] = $chkUnqExiste['fsi_uid'];
//        } else {
//
//            $fsuidata = $uniData;
//            $fsuidata['fsi_count'] = 1;
//            $ustatus = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $fsuidata);
//            $stit_fsiuid['fsi_uid'] = $db->insert_id();
//            $stit_fsiuid['status'] = 'NEW';
//        }
//    } else {
//        if (intval($chkUnqExiste['fsi_uid']) > 0) {
//            if (intval($chkUnqExiste['fsi_uid']) != $uid) {
//
//                $fsuidata['fsi_count'] = intval($uidCount) - 1;
//                $status = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $fsuidata, 'update', " fsi_uid = {$uid}");
//                $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM finascop_stock_itemmaster WHERE stit_fsiuid = {$uid}");
//                $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $uuit, 'update', " fsi_uid = {$uid}");
//
//
//                $updatCou['fsi_count'] = intval($chkUnqExiste['fsi_count']) + 1;
//                $status = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $updatCou, 'update', " fsi_uid = {$chkUnqExiste['fsi_uid']}");
//                $stit_fsiuid['fsi_uid'] = $chkUnqExiste['fsi_uid'];
//            } else {
//                $stit_fsiuid['fsi_uid'] = $uid;
//            }
//        } else {
//
//            $fsdata['fsi_count'] = intval($uidCount) - 1;
//            $status = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $fsdata, 'update', " fsi_uid = {$uid}");
//            $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM finascop_stock_itemmaster WHERE stit_fsiuid = {$uid}");
//            $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $uuit, 'update', " fsi_uid = {$uid}");
//
//            $fsuidata = $uniData;
//            $fsuidata['fsi_count'] = 1;
//            $ustatus = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $fsuidata);
//            $stit_fsiuid['fsi_uid'] = $db->insert_id();
//            $stit_fsiuid['status'] = 'NEW';
//        }
//    }
//    return $stit_fsiuid;
//}
