<?php

global $db;

switch ($op) {
    case 'getOrdersinSprtReturn':

        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;

        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'order_id' : $sort;
//        $sort = ($sort=='order_created_on') ? 'DATE(order_created_on)' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $userID = $_SESSION['admin']->UserId;
        $filter_qry = " ";

        $order = '';
        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
            }
        }

        if ($_POST['current_branch_id'] > 0) {
            $current_branch_id = $_POST['current_branch_id'];
        } else {
            $current_branch_id = $db->getItemSafe("SELECT br_ID FROM finascop_branch WHERE br_Name = ?", "s", [$_POST['br_Name']]);
        }
        switch ($sort) {

            default :
                $order .= "ORDER BY CAST({$sort} as char) {$dir},binary {$sort} {$dir}";
                break;
        }
        if ($_POST['br_Name'] != '') {
            $filter_qry .= "AND br_Name  LIKE  '" . $_POST['br_Name'] . "%'";
        }
        $countQuery = "SELECT COUNT(*) "
                . " FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id "
                . " WHERE 1=1 AND order_HasReturn = 1  and bco.status_id > 0 AND order_branch_id = {$current_branch_id}  {$filter_qry}  ORDER BY order_id desc";

        $listQuery = " SELECT order_id,order_order_id,order_packedbags_count,order_customer_id,order_branch_id,br_Name,bco.status_id as status,DATE_FORMAT(created_at,'%d-%m-%Y') AS order_created_on,TIME_FORMAT(cast(created_at as time),'%r') as ordertime,admin_description AS order_status,
            (SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = order_customer_id) AS delivery_to,(SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = order_customer_id) AS cust_mobile,
            order_HasReturn,order_ItemsReturned,order_ReturnVerified
            FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id "
                . " WHERE 1 = 1 AND order_HasReturn = 1 and bco.status_id > 0 AND order_branch_id = {$current_branch_id}  {$filter_qry}  ORDER BY order_id desc limit " . $start . "," . $limit;

//        $db->printGridJson($countQuery, $listQuery);
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        $count = $db->getItemFromDB($countQuery);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $order_cancellationdets = $db->getFromDB("SELECT reason,if(cancelled_by_type = 1,'Customer','Back Office') as cancelled_by_type_name,cancelled_by_type,"
                        . "(SELECT CONCAT(FirstName,' ',LastName) FROM finascop_usr_profile WHERE UserId = cancelled_by_id) as cancelled_by_name,cancelled_by_id FROM retaline_customer_order_cancellationdets "
                        . "where order_id = {$datas[$i]['order_id']}", true);
                $datas[$i]['reason'] = $order_cancellationdets['reason'];
                $datas[$i]['cancelled_by_type_name'] = $order_cancellationdets['cancelled_by_type_name'];
                $datas[$i]['cancelled_by_type'] = $order_cancellationdets['cancelled_by_type'];
                $datas[$i]['cancelled_by_name'] = $order_cancellationdets['cancelled_by_name'];
                $datas[$i]['cancelled_by_id'] = $order_cancellationdets['cancelled_by_id'];
                $datas[$i]['order_qty'] = $db->getItemFromDB("SELECT SUM(item_order_qty) FROM retaline_customer_order_items WHERE customer_order_id = {$datas[$i]['order_id']}");
            }
        }
        echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        break;

    case "order_detailsspret":
        require(THIS_MODULE_PATH . "/order_details.php");
        break;
    case 'listItemsinReturnedOrder':
        $order_id = $_POST['order_id'];
        $order_order_id = $_POST['order_order_id'];
        $order_ItemsReturned = $db->getItemFromDB("SELECT order_ItemsReturned FROM retaline_customer_order WHERE order_id = {$order_id}");
        $order_ItemsReturned = str_replace('[', '', $order_ItemsReturned);
        $order_ItemsReturned = str_replace(']', '', $order_ItemsReturned);
        //print_r($order_ItemsReturned);
        $arrRetItems = explode(',', $order_ItemsReturned);
        $count = count($arrRetItems);
        $fsto_id = $db->getItemFromDB("SELECT fsto_id FROM finascop_stock_transfer_order WHERE fsto_ordertype = 1 AND fstr_id = {$order_id}");
        for ($i = 0; $i < $count; $i++) {
            $bcDetails[$i] = $db->getFromDB("SELECT stiid_id,stiid_barcode FROM finascop_stock_transfer_order_details_barcodes WHERE fsto_id = {$fsto_id} AND stiid_barcode = '{$arrRetItems[$i]}' ", true);
        }
        //print_r($bcDetails);
        $resCount = count($bcDetails);
        if (!empty($bcDetails)) {
            for ($j = 0; $j < $resCount; $j++) {
                $stiid_itemmasterid = $db->getItemFromDB("SELECT stiid_itemmasterid FROM finascop_stock_item_inventorydetails WHERE stiid_id = {$bcDetails[$j]['stiid_id']}");
                $itemDetails = $db->getFromDB("SELECT item_id,item_sales_price,item_return_qty_requested,item_return_qty_damaged,item_return_qty_sellable,item_return_qty_damagedinTransit,item_order_qty FROM retaline_customer_order_items WHERE item_product_id = {$stiid_itemmasterid} AND customer_order_id = {$order_id}", true);
                $datas[$j]['order_id'] = $order_id;
                $datas[$j]['stiid_barcode'] = $bcDetails[$j]['stiid_barcode'];
                $datas[$j]['itemId'] = $itemDetails['item_id'];
                $datas[$j]['stiid_id'] = $bcDetails[$j]['stiid_id'];
                $datas[$j]['itemName'] = $db->getItemFromDB("SELECT stiid_itemmastername FROM finascop_stock_item_inventorydetails WHERE stiid_id = {$bcDetails[$j]['stiid_id']}");
                $datas[$j]['item_sales_price'] = $itemDetails['item_sales_price'];
                $datas[$j]['item_product_id'] = $stiid_itemmasterid;
                $datas[$j]['returnCount'] = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_return_request_order_barcodes WHERE rtrqb_barcode = '{$bcDetails[$j]['stiid_barcode']}' AND order_id = {$order_id} AND rtrqb_status = 1");
                $datas[$j]['item_return_sellableqty'] = $itemDetails['item_return_qty_sellable'];
                $datas[$j]['item_return_damagedqty'] = $itemDetails['item_return_qty_damaged'];
                $datas[$j]['item_return_qty_requested'] = $itemDetails['item_return_qty_requested'];
                $datas[$j]['item_order_qty'] = $itemDetails['item_order_qty'];
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
            //echo json_encode($qry);
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';

        break;
    case 'generateUniqueId':
        $uniqueId = '';
        while ($uniqueId == '') {
            $uniqueId = getNewFinascopApiKey();
        }
        echo '{"uid":"' . $uniqueId . '"}';
        break;
    case 'saveItemtoSpotReturnOrder':

        $uuid = $_POST['uuid'];
        $itemReturn_type1qty = $_POST['itemReturn_type1qty'];
        $itemReturn_type2qty = $_POST['itemReturn_type2qty'];
        $itemReturn_type3qty = $_POST['itemReturn_type3qty'];
        $returnableQty = $_POST['returnableQty'];
        $totalReturn = $_POST['totalReturn'];
        $returned_qty = $_POST['returned_qty'];
        $order_no = $_POST['order_id'];
        $item_product_id = $_POST['itemId'];
        $item_order_qty = $_POST['item_order_qty'];
        $barcodesearch_fieldrt = $_POST['barcodesearch_fieldrt'];
        $msg = "";

        $order_customer_id = $db->getItemFromDB("SELECT order_customer_id FROM retaline_customer_order WHERE order_id = {$order_no}");
        $order_branch_id = $db->getItemFromDB("SELECT order_branch_id FROM retaline_customer_order WHERE order_id = {$order_no}");
        $payment_mode = $db->getItemFromDB("SELECT payment_mode FROM retaline_customer_order WHERE order_id = {$order_no}");
        $db->query('begin');

        $itemReturnRequested = $db->getItemFromDb("SELECT order_itemReturnRequestCount FROM retaline_customer_order WHERE order_id = {$order_no}");

        $orderItemCount = $db->getItemFromDB("SELECT SUM(item_order_qty) FROM retaline_customer_order_items WHERE customer_order_id = {$order_no}");
        $order_roundoff = $db->getItemFromDB("SELECT order_roundoff FROM retaline_customer_order WHERE order_id = {$order_no}");
        $order_delivery_charge = $db->getItemFromDB("SELECT order_delivery_charge FROM retaline_customer_order WHERE order_id = {$order_no}");

        $itemName = $db->getItemFromDB("SELECT stit_SKU FROM  finascop_stock_itemmaster WHERE stit_ID = {$item_product_id}");
        $itemCountEntry = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory WHERE branch_id = {$order_branch_id} AND stit_id =  {$item_product_id}");
        if ($returned_qty > 0) {


            $count = $db->getItemFromDB("SELECT rtrqo_id FROM finascop_stock_return_request_order  WHERE rtrqo_uuid = '{$uuid}'");
            if ($count == 0) {
                $rtrqo['rtrqo_uuid'] = $uuid;
                $rtrqo['order_id'] = $order_no;
                $rtrqo['rtrqo_sourceBranch'] = $order_branch_id;
                $rtrqo['rtrqo_dstinationBranch'] = $db->getItemFromDB("SELECT br_cpd FROM finascop_branch WHERE br_ID = {$order_branch_id}");
                $rtrqo['rtrqo_sourceType'] = 1;
                $rtrqo['rtrqo_type'] = 1;
                $rtrqo['rtrqo_createdOn'] = date('Y-m-d H:i:s');
                $rtrqo['rtrqo_createdBy'] = $_SESSION['admin']->UserId;
                $status = $db->perform("finascop_stock_return_request_order", $rtrqo);
                $rtrqo_id = $db->insert_id();
            } else {
                $rtrqo_id = $count;
                $rtrqo['rtrqo_updatedOn'] = date('Y-m-d H:i:s');
                $rtrqo['rtrqo_updatedBy'] = $_SESSION['admin']->UserId;
                $status = $db->perform("finascop_stock_return_request_order", $rtrqo, 'update', " rtrqo_id = {$rtrqo_id}");
            }

            $rtrqod['rtrqo_id'] = $rtrqo_id;
            $rtrqod['rtrqod_item_id'] = $item_product_id;
            $rtrqod['rtrqod_return_count'] = $returned_qty;
            $rtrqod['rtrqod_return_damaged'] = $itemReturn_type2qty;
            $rtrqod['rtrqod_return_sellable'] = $itemReturn_type1qty;
            $rtrqod['rtrqod_return_damagedinTransit'] = $itemReturn_type3qty;
            $status = $db->perform("finascop_stock_return_request_order_details", $rtrqod);
            $rtrqod_id = $db->insert_id();
            if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                if ($itemReturn_type1qty > 0) {
                    $rtrqb['rtrqb_isSellable'] = 1;
                } else if ($itemReturn_type2qty > 0) {
                    $rtrqb['rtrqb_isSellable'] = 0;
                } else if ($itemReturn_type3qty > 0) {
                    $rtrqb['rtrqb_isSellable'] = 2;
                }
                $rtrqb['rtrqb_barcode'] = $barcodesearch_fieldrt;
                $rtrqb['order_id'] = $order_no;
                $rtrqb['rtrqb_itemId'] = $item_product_id;
                $rtrqb['rtrqo_id'] = $rtrqo_id;
                $rtrqb['rtrqod_id'] = $rtrqod_id;
                $rtrqb['rtrqb_status'] = 1;
                $rtrqb['rtrqb_updatedOn'] = date('Y-m-d H:i:s');
                $rtrqb['rtrqb_updatedBy'] = $_SESSION['admin']->UserId;
                $rtrqb['rtrqb_createdOn'] = date('Y-m-d H:i:s');
                $rtrqb['rtrqb_createdBy'] = $_SESSION['admin']->UserId;
                $status = $db->perform("finascop_stock_return_request_order_barcodes", $rtrqb);
            }

            $itemReturnRequested = $itemReturnRequested + $returned_qty;
            $status = $db->query("UPDATE retaline_customer_order_items SET item_return_qty_requested = item_return_qty_requested + {$returned_qty},"
                    . "item_return_qty_damaged = item_return_qty_damaged + {$itemReturn_type2qty},item_return_qty_sellable = item_return_qty_sellable + {$itemReturn_type1qty},"
                    . "item_return_qty_damagedinTransit = item_return_qty_damagedinTransit + {$itemReturn_type3qty} WHERE item_product_id = {$item_product_id} AND customer_order_id = {$order_no} ");

            $stit_id = $db->getFromDB("SELECT mrp,selling_price,fsbg_id FROM finascop_stock_branch_inventory WHERE stit_id = {$item_product_id} AND branch_id = {$order_branch_id}", true);

            if ($itemReturn_type1qty > 0) {
                if ($itemCountEntry > 0) {
                    //fsbg_id
                    $status = $db->query("UPDATE finascop_stock_branch_inventory SET item_count = item_count + {$itemReturn_type1qty} WHERE stit_id = {$item_product_id} AND branch_id = {$order_branch_id}");
                } else {

                    $fsbi['stit_id'] = $item_product_id;
                    $fsbi['branch_id'] = $order_branch_id;
                    $fsbi['item_count'] = $itemReturn_type1qty;
                    $fsbi['mrp'] = $stit_id['mrp'];
                    $fsbi['selling_price'] = $stit_id['selling_price'];
                    $fsbi['updated_on'] = date('Y-m-d H:i:s');
                    $fsbi['fsbg_id'] = $stit_id['fsbg_id'];
                    $status = $db->perform('finascop_stock_branch_inventory', $fsbi);
                }
            }
            if ($itemReturn_type2qty > 0) {
                
            }

            if ($itemReturn_type3qty > 0) {
                
            }
            $rco['order_itemReturnRequestCount'] = $itemReturnRequested;
            $status = $db->perform("retaline_customer_order", $rco, 'update', " order_id = {$order_no}");

            $itemPrices = $db->getFromDB("SELECT item_price,item_discount,item_order_qty FROM retaline_customer_order_items WHERE item_product_id = {$item_product_id} AND customer_order_id = {$order_no}", true);
            //print_r($itemPrices);

            if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {

                $returnBarcod = $db->getFromDB("SELECT * FROM finascop_stock_return_request_order_barcodes WHERE order_id = {$order_no} AND rtrqb_status = 1 AND rtrqb_barcode = {$barcodesearch_fieldrt} AND rtrqb_itemId = {$item_product_id}", true);

                if (($itemReturnRequested == $orderItemCount)) {
                    $item_sales_price = floatval($itemPrices['item_price']) - floatval($itemPrices['item_discount']) + floatval($order_delivery_charge) + floatval($order_roundoff);
                    $bcwt['brcw_AddInfo'] = "On sales return of {$itemName} with delivery charge on order {$order_no}";
                } else {
                    $bcwt['brcw_AddInfo'] = "On sales return of {$itemName} on order {$order_no}";
                    $item_sales_price = (floatval($itemPrices['item_price']) - floatval($itemPrices['item_discount'])) / $itemPrices['item_order_qty'];
                }
                $fsiim['stiid_id'] = $db->getItemFromDB("SELECT stiid_id FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = {$returnBarcod['rtrqb_barcode']}");
                $fsiim['stiidm_itemmasterid'] = $returnBarcod['rtrqb_itemId'];
                $fsiim['stiidm_barcode'] = $returnBarcod['rtrqb_barcode'];
                $fsiim['stiidm_details'] = "Item returned from customer in order - {$order_no} ";
                $fsiim['created_at'] = date("Y-m-d H:i:s");
                $fsiim['updated_at'] = date("Y-m-d H:i:s");
                $status = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiim);
                if ($itemReturn_type1qty > 0) {
                    $status = $db->query("UPDATE finascop_stock_item_inventorydetails SET stiid_status = 4 WHERE stiid_barcode = '{$returnBarcod['rtrqb_barcode']}'");
                }
                if (($itemReturn_type2qty > 0) || ($itemReturn_type2qty > 0)) {
                    $status = $db->query("UPDATE finascop_stock_item_inventorydetails SET stiid_status = 8 WHERE stiid_barcode = '{$returnBarcod['rtrqb_barcode']}'");
                }

                $bcwt['stiid_barcode'] = $returnBarcod['rtrqb_barcode'];

                $stit_id = $db->getFromDB("SELECT stiid_mrp,stiid_selpri,stiid_itemmasterid,fsbg_id,stiid_itemmastername FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = '{$returnBarcod['rtrqb_barcode']}'", true);

                switch ($payment_mode) {
                    case 1://payondelivery
                    case 2://onlinepayment
                    case 3://Wallet
                    case 4://COD with Wallet
                    case 5://online with Wallet
                    case 6://online on delivery
                    case 7://cash with delivery
                        //4. THe customer wallet should be updated.

                        $status = $db->query("UPDATE retaline_customer SET cust_walletbalance = cust_walletbalance + {$item_sales_price}  WHERE cust_id = {$order_customer_id}");
                        //4. THe customer wallet should be updated.
                        //5. An entry should be added in customer wallet transaction
                        $bcwt['cust_id'] = $order_customer_id;
                        $bcwt['refentry_id'] = $order_no;
                        $bcwt['brcw_SourceType'] = 1;
                        $bcwt['brcw_Amount'] = $item_sales_price;
                        $bcwt['stit_id'] = $item_product_id;
                        $bcwt['brcw_CreatedOn'] = date("Y-m-d H:i:s");
                        $bcwt['brcw_Updateon'] = date("Y-m-d H:i:s");
                        $status = $db->perform('retaline_customer_wallet_transaction', $bcwt);
                        //5. An entry should be added in customer wallet transaction

                        break;
                }
            } else {



                if (($item_return_qty == $orderItemCount)) {
                    $item_sales_price = floatval($itemPrices['item_price']) - floatval($itemPrices['item_discount']) + floatval($order_delivery_charge) + floatval($order_roundoff);
                    $bcwt['brcw_AddInfo'] = "On sales return of {$itemName} with delivery charge on order {$order_no}";
                } else {
                    $bcwt['brcw_AddInfo'] = "On sales return of {$itemName} on order {$order_no}";
                    $item_sales_price = ((floatval($itemPrices['item_price']) - floatval($itemPrices['item_discount'])) / $itemPrices['item_order_qty']) * $returned_qty;
                }
                //echo '$item_sales_price' . $item_sales_price;
                switch ($payment_mode) {
                    case 1://payondelivery
                    case 2://onlinepayment
                    case 3://Wallet
                    case 4://COD with Wallet
                    case 5://online with Wallet
                    case 6://online on delivery
                    case 7://cash with delivery
                        //4. THe customer wallet should be updated.

                        $status = $db->query("UPDATE retaline_customer SET cust_walletbalance = cust_walletbalance + {$item_sales_price}  WHERE cust_id = {$order_customer_id}");
                        //4. THe customer wallet should be updated.
                        //5. An entry should be added in customer wallet transaction
                        $bcwt['cust_id'] = $order_customer_id;
                        $bcwt['refentry_id'] = $order_no;
                        $bcwt['brcw_SourceType'] = 1;
                        $bcwt['brcw_Amount'] = $item_sales_price;
                        $bcwt['stit_id'] = $item_product_id;
                        $bcwt['brcw_CreatedOn'] = date("Y-m-d H:i:s");
                        $bcwt['brcw_Updateon'] = date("Y-m-d H:i:s");
                        $status = $db->perform('retaline_customer_wallet_transaction', $bcwt);
                        //5. An entry should be added in customer wallet transaction
                        $msg .= " Added Rs.{$item_sales_price} to Customer Wallet for {$returned_qty} item(s).";
                        break;
                }
            }


            if ($itemReturn_type1qty > 0) {
                $msg .= " {$itemReturn_type1qty} item(s) added to current stock.";
            }
            if ($itemReturn_type2qty > 0) {
                $msg .= " {$itemReturn_type2qty} item(s) added to Damaged to initiate Purchase Return.";
            }
            if ($itemReturn_type3qty > 0) {
                $msg .= " {$itemReturn_type3qty} item(s) added for  Issuing a Debit Note of Rs.XXX to Delivery Boy Driver_Name.";
            }
        }

        $status = $db->query('commit');
        if ($status == 1) {
            echo '{"success":true,"valid":true,"msg": "' . $msg . '"}';
        } else {
            // var_dup($error);
            echo '{"success":false,"valid":false,"msg": "Error Occured."}';
        }
        break;
}
?>

