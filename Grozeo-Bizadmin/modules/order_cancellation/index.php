<?php

global $db;

switch ($op) {
    case 'getOrders':

        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;

        $_allowed_sort = ['order_id', 'order_created_on'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'order_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'order_id' : $sort;
        //        $sort = ($sort=='order_created_on') ? 'DATE(order_created_on)' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $userID = $_SESSION['admin']->UserId;
        $filter_qry = " ";

        $order = '';
        if (isset($_POST['filter'])) {
            $allowedFields = ['order_generated_id', 'member_phone', 'order_created_on', 'cancel_reason', 'order_status'];
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }

        if ($_POST['current_branch_id'] > 0) {
            $current_branch_id = $_POST['current_branch_id'];
        } else {
            $current_branch_id = $db->getItemSafe("SELECT br_ID FROM finascop_branch WHERE br_Name = ?", "s", [$_POST['br_Name']]);
        }
        switch ($sort) {

            default:
                $order .= "ORDER BY CAST({$sort} as char) {$dir},binary {$sort} {$dir}";
                break;
        }
        if ($_POST['br_Name'] != '') {
            $filter_qry .= "AND br_Name  LIKE  '" . $_POST['br_Name'] . "%'";
        }

        if ($_SESSION['admin']->br_PyramidLevel == 1) {
            $brQuery = " ";
            /*if ($_SESSION['admin']->IsSuperUser == 'Yes') {
                $brQuery = " ";
            } else {
                $brQuery = " AND order_branch_id = {$current_branch_id} ";
            }*/
        } else {
            $brQuery = " AND order_branch_id = {$current_branch_id} ";
        }
        $countQuery = "SELECT COUNT(*) "
            . " FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id "
            . " WHERE 1=1 and bco.status_id > 0 {$brQuery} AND bco.status_id =19 {$filter_qry}  ORDER BY order_id desc";

        $listQuery = " SELECT order_id,order_order_id,order_packedbags_count,order_customer_id,order_branch_id,CONCAT(br_Name ,'-',branch_shortname) as br_Name,bco.status_id as status,DATE_FORMAT(created_at,'%d-%m-%Y') AS order_created_on,TIME_FORMAT(cast(created_at as time),'%r') as ordertime,admin_description AS order_status,
            (SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = order_customer_id) AS delivery_to,(SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = order_customer_id) AS cust_mobile,
            order_HasReturn,order_ItemsReturned,order_ReturnVerified
            FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id "
            . " WHERE 1 = 1 and bco.status_id > 0 {$brQuery} AND bco.status_id =19 {$filter_qry}  ORDER BY order_id desc limit " . $start . "," . $limit;

        //        $db->printGridJson($countQuery, $listQuery);
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        $count = $db->getItemFromDB($countQuery);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $order_cancellationdets = $db->getFromDB("SELECT reason,cancelled_by_type,cancelled_by_id FROM retaline_customer_order_cancellationdets where order_id = {$datas[$i]['order_id']}", true);
                switch ($order_cancellationdets['cancelled_by_type']) {
                    case '1':
                        $cancelled_by_type_name = 'Customer';
                        $cancelled_by_name = $db->getItemFromDB("SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = {$order_cancellationdets['cancelled_by_id']}");
                        break;
                    case '2':
                        $cancelled_by_type_name = 'Back Office';
                        $cancelled_by_name = $db->getItemFromDB("SELECT CONCAT(FirstName,' ',LastName) FROM finascop_usr_profile WHERE UserId = {$order_cancellationdets['cancelled_by_id']}");
                        break;
                    case '3':
                        $cancelled_by_type_name = 'Merchant';
                        $cancelled_by_name = 'Merchant user';
                        break;
                    case '4':
                        $cancelled_by_type_name = 'Packsure';
                        $cancelled_by_name = $db->getItemFromDB("SELECT name FROM retaline_godown_boy WHERE id = {$order_cancellationdets['cancelled_by_id']}");
                        break;
                }
                $datas[$i]['reason'] = $order_cancellationdets['reason'];
                $datas[$i]['cancelled_by_type_name'] = $cancelled_by_type_name;
                $datas[$i]['cancelled_by_type'] = $order_cancellationdets['cancelled_by_type'];
                $datas[$i]['cancelled_by_name'] = $cancelled_by_name;
                $datas[$i]['cancelled_by_id'] = $order_cancellationdets['cancelled_by_id'];
            }
        }
        echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        break;

    case 'getOrdersDetails':

        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;

        $_allowed_sort = ['order_id', 'order_created_on'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'order_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'order_id' : $sort;
        //        $sort = ($sort=='order_created_on') ? 'DATE(order_created_on)' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $userID = $_SESSION['admin']->UserId;
        $filter_qry = " ";

        $order = '';
        if (isset($_POST['filter'])) {
            $allowedFields = ['order_generated_id', 'member_phone', 'order_created_on', 'cancel_reason', 'order_status'];
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }

        if ($_POST['current_branch_id'] > 0) {
            $current_branch_id = $_POST['current_branch_id'];
        } else {
            $current_branch_id = $db->getItemSafe("SELECT br_ID FROM finascop_branch WHERE br_Name = ?", "s", [$_POST['br_Name']]);
        }
        switch ($sort) {

            default:
                $order .= "ORDER BY CAST({$sort} as char) {$dir},binary {$sort} {$dir}";
                break;
        }
        if ($_POST['customer_mobile'] != "") {
            if ($_POST['type'] == "mobile") {
                $customerMobile = $db->sanitizeInt($_POST['customer_mobile']);
                $mob_order = "AND rc.cust_mobile  = {$customerMobile}";
            } else if ($_POST['type'] == "order") {
                $orderFilter = $db->sanitizeInt($_POST['customer_mobile']);
                $mob_order = "AND order_order_id  = {$orderFilter}";
            }

            $countQuery = "SELECT COUNT(*) "
                . " FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id INNER JOIN retaline_customer rc ON cust_id = order_customer_id"
                . " WHERE 1=1  AND bco.status_id IN(9,7,22) {$mob_order} {$filter_qry}  ORDER BY order_id desc";

            $listQuery = " SELECT order_id,order_order_id,order_mrp,order_packedbags_count,order_customer_id,order_branch_id,CONCAT(br_Name ,'-',branch_shortname) as br_Name,bco.status_id as status,DATE_FORMAT(created_at,'%d-%m-%Y') AS order_created_on,
                TIME_FORMAT(cast(created_at as time),'%r') as ordertime,admin_description AS order_status,DATE_FORMAT(updated_at,'%Y-%m-%d %H:%i:%s') as updated_at,
            (SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = order_customer_id) AS delivery_to,(SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = order_customer_id) AS cust_mobile,
            order_HasReturn,order_ItemsReturned,order_ReturnVerified
            FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id INNER JOIN retaline_customer rc ON cust_id = order_customer_id"
                . " WHERE 1 = 1  AND bco.status_id IN(9,7,22) {$mob_order} {$filter_qry}  ORDER BY order_id desc ";
        }
        $db->printGridJson($countQuery, $listQuery);

        break;

    case "order_details":
        require(THIS_MODULE_PATH . "/order_details.php");
        break;

    case "cancelNewOrder":
        //Packed AND Ready FOR delivery,Queued FOR manual assignment,Approval pending AFTER Order
        $status_id = $_POST['status_id'];
        $updated_at = $_POST['updated_at'];
        $order_no = $_POST['order_no'];
        $cust_id = $_POST['cust_id'];
        $reason = $_POST['reason'];
        //print_r($_POST);
        $count = $db->getItemFromDB("SELECT count(*) FROM retaline_customer_order WHERE order_id = {$order_no} and updated_at = '{$updated_at}'");

        //exit;
        if ($count == 0) {
            $msg = "You are not allowed to Cancel this Order.";
            echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
            exit();
        } else {
            $db->query('begin');

            $date = date('Y-m-d H:i:s');
            $userId = $_SESSION['admin']->UserId;
            if ($_POST['order_no'] > 0) {
                $order_customer_id = $db->getItemSafe("SELECT order_customer_id FROM retaline_customer_order WHERE order_id = ?", "i", [$_POST['order_no']]);
                $order_branch_id = $db->getItemSafe("SELECT order_branch_id FROM retaline_customer_order WHERE order_id = ?", "i", [$_POST['order_no']]);
                $order_order_id = $db->getItemSafe("SELECT order_order_id FROM retaline_customer_order WHERE order_id = ?", "i", [$order_no]);
                $branch_name = $db->getItemFromDB("SELECT br_Name FROM finascop_branch WHERE br_ID = {$order_branch_id}");
                $item_product_ids = $db->getMultipleData("SELECT item_product_id,item_id,item_retail_price,item_sales_price,item_order_qty FROM retaline_customer_order_items WHERE customer_order_id = {$_POST['order_no']}", true);
                $payment_mode = $db->getItemSafe("SELECT payment_mode FROM retaline_customer_order WHERE order_id = ?", "i", [$_POST['order_no']]);
                $fstoId = $db->getItemSafe("SELECT fsto_id FROM finascop_stock_transfer_order WHERE fstr_id  = ? and fsto_ordertype = 1", "i", [$_POST['order_no']]);
                if ($fstoId > 0) {

                    $qstatus = $db->query("UPDATE qugeo_order SET quor_Status = 40,quor_UpdateOn = '{$date}' WHERE quor_TransferOrder_id = {$fstoId}");
                    $qugeoDetails = $db->getFromDB("SELECT quor_id,quor_DeliveryMethodsAllowed FROM qugeo_order WHERE quor_TransferOrder_id = {$fstoId}", true);
                    $barcodes = $db->getItemFromDB("SELECT GROUP_CONCAT(stiid_barcode) FROM finascop_stock_transfer_order_details_barcodes WHERE fsto_id = {$fstoId}");
                }

                if (!empty($barcodes)) {
                    $amount = $db->getItemFromDB("SELECT SUM(stii_epraft) FROM finascop_stock_item_inventorydetails WHERE stiid_barcode IN ({$barcodes})");
                }

                if ($payment_mode == 1 || $payment_mode == 4 || $payment_mode == 7) {
                    if ($qugeoDetails['quor_DeliveryMethodsAllowed'] == 8) {
                        $B2COrderDetails = $db->getFromSafe("SELECT payment_mode,total,order_total_gst,order_kfc_amount,order_total_cgst,order_total_sgst,order_branch_id FROM retaline_customer_order WHERE order_id = ?", "i", [$_POST['order_no']], true);
                        $barcodeDetails = $db->getMultipleData("SELECT fsto_id,stiid_barcode,stiid_id FROM finascop_stock_transfer_order_details_barcodes WHERE fsto_id = {$fstoId}", true);
                        $retailerDetail = $db->getFromDB("SELECT br_ReferenceID,br_cpd FROM finascop_branch WHERE br_ID = {$B2COrderDetails['order_branch_id']}", true);
                        //$distributorDetail = $db->getFromDB("SELECT br_ReferenceID,br_cpd FROM finascop_branch WHERE br_ID = {$retailerDetail['br_cpd']}", true);
                        //$centralStoreDetail = $db->getFromDB("SELECT br_ReferenceID,br_cpd FROM finascop_branch WHERE br_ID = {$distributorDetail['br_cpd']}", true);
                        //$cpdDetail = $db->getFromDB("SELECT br_ReferenceID,br_cpd FROM finascop_branch WHERE br_ID = {$centralStoreDetail['br_cpd']}", true);
                        $companyMarginAmt = 0;
                        $cshubMarginAmt = 0;
                        $incentiveMarginAmt = 0;
                        $distributMarginAmt = 0;
                        $retailMarginAmt = 0;
                        $driveMarginAmt = 0;
                        $courierMarginAmt = 0;
                        if (!empty($barcodeDetails)) {
                        foreach ($barcodeDetails as $barcodeDetail) {
                            $itemAmount = $db->getItemSafe("SELECT stiid_selpri FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = ?", "s", [$barcodeDetail['stiid_barcode']]);
                            if (empty($itemAmount)) {
                                continue;
                            }
                            $bmdd_id = $db->getItemSafe("SELECT bmdd_id FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = ?", "s", [$barcodeDetail['stiid_barcode']]);
                            $marginDetails = $db->getFromDB("SELECT bmd_company,bmd_hub,bmd_incentive,bmd_customer,bmd_distributor,bmd_retailor,bmd_driver,bmd_courier FROM retaline_margindistributions WHERE bmd_id = {$bmdd_id}", true);
                            $companyMargin = round($marginDetails['bmd_company'] * $itemAmount / 100, 2);
                            $companyMarginAmt = $companyMarginAmt + $companyMargin;
                            $cshubMargin = round($marginDetails['bmd_hub'] * $itemAmount / 100, 2);
                            $cshubMarginAmt = $cshubMarginAmt + $cshubMargin;
                            $incentiveMargin = round($marginDetails['bmd_incentive'] * $itemAmount / 100, 2);
                            $incentiveMarginAmt = $incentiveMarginAmt + $incentiveMargin;
                            $distributMargin = round($marginDetails['bmd_distributor'] * $itemAmount / 100, 2);
                            $distributMarginAmt = $distributMarginAmt + $distributMargin;
                            $retailMargin = round($marginDetails['bmd_retailor'] * $itemAmount / 100, 2);
                            $retailMarginAmt = $retailMarginAmt + $retailMargin;
                            $driveMargin = round($marginDetails['bmd_driver'] * $itemAmount / 100, 2);
                            $driveMarginAmt = $driveMarginAmt + $driveMargin;
                            $courierMargin = round($marginDetails['bmd_courier'] * $itemAmount / 100, 2);
                            $courierMarginAmt = $courierMarginAmt + $courierMargin;
                        }
                        }
                    }
                }
                //            for ($i = 0; $i < count($order_no); $i++) {
                if ($status_id == 7) {
                    $qstatus = $db->query("DELETE FROM finascop_stock_blocked WHERE order_id = {$order_no}");
                } else {
                    //if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                    foreach ($item_product_ids as $item_product_id) {
                        $itemSKU = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$item_product_id['item_product_id']}");
                        $orderInvetories = $db->getMultipleSafe("SELECT stiid_mrp,stiid_selpri,stiid_itemmasterid,fsbg_id,stiid_itemmastername,stiid_barcode FROM finascop_stock_item_inventorydetails WHERE stiid_itemmasterid = {$item_product_id['item_product_id']} AND cust_order_id = ?", "i", [$_POST['order_no']], true);
                        if ($status_id == 9 && !empty($orderInvetories)) {
                            foreach ($orderInvetories as $orderInventory) {
                                $itemCountEntry = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory WHERE branch_id = {$order_branch_id} AND stit_id = {$item_product_id['item_product_id']} AND fsbg_id = {$orderInventory['fsbg_id']}");
                            if ($itemCountEntry > 0) {
                                $qstatus = $db->query("UPDATE finascop_stock_branch_inventory SET item_count = item_count + 1 WHERE stit_id = {$item_product_id['item_product_id']} AND branch_id = {$order_branch_id} AND fsbg_id = {$orderInventory['fsbg_id']}");
                            } else {
                                $fsbi['stit_id'] = $item_product_id['item_product_id'];
                                $fsbi['branch_id'] = $order_branch_id;
                                $fsbi['fsbg_id'] = $orderInventory['fsbg_id'];
                                $fsbi['item_count'] = 1;
                                $fsbi['mrp'] = $item_product_id['item_retail_price'];
                                $fsbi['selling_price'] = $item_product_id['item_sales_price'];
                                $fsbi['updated_on'] = date('Y-m-d H:i:s');
                                $status = $db->perform('finascop_stock_branch_inventory', $fsbi);

                                $type = 'Order Cancellation';
                                $updatatLog['old_selling_price'] = NULL;
                                $updatatLog['old_item_count'] = NULL;
                                $updatatLog['selling_price'] = $item_product_id['item_sales_price'];
                                $updatatLog['branch_id'] = $order_branch_id;
                                $updatatLog['stit_id'] = $item_product_id['item_product_id'];
                                $updatatLog['item_count'] = 1;
                                $updatatLog['fpod_skuPurchaseRange'] = NULL;
                                $updatatLog['fpod_skuPurchaseQty'] = NULL;
                                $updatatLog['fpod_skuAvgPurchaseRate'] = NULL;
                                $updatatLog['fpod_skuLastPurchaseRate'] = NULL;
                                $updatatLog['fpod_leastSKUepr'] = NULL;
                                $updatatLog['fpod_effectivemargin'] = NULL;
                                $updatatLog['updated_on'] = date("Y-m-d H:i:s");
                                $updatatLog['updated_by'] = $_SESSION['admin']->Finascop_UserId;
                                $updatatLog['type'] = $type;
                                $updatatLog['action'] = 'Inventory update - ' . $type;
                                $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'INVENTORYLOG'");
                                $fields_string = json_encode($updatatLog);
                                $opts = array(
                                    CURLOPT_URL => $url,
                                    CURLINFO_CONTENT_TYPE => "application/json",
                                    CURLOPT_TIMEOUT => 0,
                                    CURLOPT_FOLLOWLOCATION => true,
                                    CURLOPT_BINARYTRANSFER => TRUE,
                                    CURLOPT_RETURNTRANSFER => TRUE,
                                    CURLOPT_POST => count($fields),
                                    CURLOPT_POSTFIELDS => $fields_string,
                                    CURLOPT_HTTPHEADER => array('Content-Type: application/json')
                                );

                                $ch = curl_init();
                                curl_setopt_array($ch, $opts);
                                $logrresult = curl_exec($ch);
                                $info = curl_getinfo($ch);
                                curl_close($ch);
                                header("Content-Type: application/json");
                                //$result = json_decode($datacl, true);
                                if ($logrresult != true) {
                                    echo '{"success":false, "msg":"Some problem in log insertion."}';
                                    exit();
                                }
                            }
                            }
                        }
                        if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                            $orderInvetoriesDet = $db->getMultipleData("SELECT stiid_id,stiid_mrp,stiid_selpri,stiid_itemmasterid,fsbg_id,stiid_itemmastername,stiid_barcode FROM finascop_stock_item_inventorydetails "
                                . "WHERE stiid_itemmasterid = {$item_product_id['item_product_id']} AND cust_order_id = '{$_POST['order_no']}'", true);

                            if ($status_id == 9) {
                                foreach ($orderInvetoriesDet as $orderInvetory) {
                                    $fsiim['stiid_id'] = $orderInvetory['stiid_id'];
                                    $fsiim['stiidm_itemmasterid'] = $orderInvetory['stiid_itemmasterid'];
                                    $fsiim['stiidm_barcode'] = $orderInvetory['stiid_barcode'];
                                    $fsiim['stiidm_details'] = "Item cancelled by admin for customer in order - {$_POST['order_no']} ";
                                    $fsiim['created_at'] = date("Y-m-d H:i:s");
                                    $fsiim['updated_at'] = date("Y-m-d H:i:s");
                                    $status = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiim);
                                    //2. THe Barcode history should have a new entry -
                                    //3. Barcode table status should change so that the item is ready for sale
                                    $status = $db->query("UPDATE finascop_stock_item_inventorydetails SET stiid_status = 4 WHERE stiid_barcode = {$orderInvetory['stiid_barcode']} ");
                                }
                            } else {
                                $status = $db->executeSafe("DELETE FROM finascop_stock_blocked WHERE order_id = ?", "i", [$_POST['order_no']]);
                            }
                        }
                    }
                }


                $order_his['order_id'] = $order_no;
                $order_his['order_status'] = 19;
                $order_his['created_at'] = $date;
                $order_his['order_action'] = "Cancelled by Admin user {$_SESSION['admin']->UserName}";
                $qstatus = $db->perform('retaline_customer_order_history', $order_his);
                $datafsto['fsto_status'] = 15;
                $datafsto['fsto_updateon'] = $date;
                $datafsto['fsto_updateby'] = $userId;
                $qstatus = $db->perform('finascop_stock_transfer_order', $datafsto, 'update', 'fsto_ordertype = 1 AND fstr_id=' . $order_no);



                $datasr['status_id'] = 19;
                $datasr['updated_at'] = $date;
                $qstatus = $db->perform('retaline_customer_order', $datasr, 'update', 'order_id=' . $order_no);
                $order_canc['customer_id'] = $cust_id;
                $order_canc['order_id'] = $order_no;
                $order_canc['reason'] = $reason;
                $order_canc['cancelled_by_type'] = 2;
                $order_canc['cancelled_by_id'] = $userId;
                $order_canc['created_at'] = $date;
                $qstatus = $db->perform('retaline_customer_order_cancellationdets', $order_canc);

                //finascop_stock_blocked
                //7 befor TO - scanning nadannittilla
                //9 - inventory details, stock count increase
                //to details barcode
                //            }

                $order_roundoff = $db->getItemSafe("SELECT order_roundoff FROM retaline_customer_order WHERE order_id = ?", "i", [$_POST['order_no']]);
                $order_delivery_charge = $db->getItemSafe("SELECT order_delivery_charge FROM retaline_customer_order WHERE order_id = ?", "i", [$_POST['order_no']]);


                switch ($payment_mode) {
                    case 2: //onlinepayment
                    case 3: //Wallet
                    case 5: //online with Wallet
                        //4. THe customer wallet should be updated.
                        $item_sales_price = $db->getItemSafe("SELECT total FROM retaline_customer_order WHERE order_id = ?", "i", [$_POST['order_no']]);
                        // $status = $db->query("UPDATE retaline_customer SET cust_walletbalance = cust_walletbalance + {$item_sales_price}  WHERE cust_id = {$order_customer_id}");
                        //4. THe customer wallet should be updated.
                        //5. An entry should be added in customer wallet transaction

                        $bcwt['stiid_barcode'] = '';

                        $bcwt['cust_id'] = $order_customer_id;
                        $bcwt['refentry_id'] = $order_no;
                        $bcwt['brcw_SourceType'] = 1;
                        $bcwt['brcw_Amount'] = $item_sales_price;
                        $bcwt['brcw_CreatedOn'] = date("Y-m-d H:i:s");
                        $bcwt['brcw_Updateon'] = date("Y-m-d H:i:s");

                        $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'WALLET_API'");
                        $fields = array(
                            "customer_id" => $order_customer_id,
                            "order_id" => $order_no,
                            "source_type" => 1,
                            "amount" => $item_sales_price,
                            "information" => "You Cancelled the Order {$order_order_id} from {$branch_name}",
                            "barcode" => 0
                        );
                        $fields_string = json_encode($fields);
                        //print_r($fields_string);
                        $opts = array(
                            CURLOPT_URL => $url,
                            CURLINFO_CONTENT_TYPE => "application/json",
                            CURLOPT_BINARYTRANSFER => TRUE,
                            CURLOPT_RETURNTRANSFER => TRUE,
                            CURLOPT_POST => count($fields),
                            CURLOPT_POSTFIELDS => $fields_string,
                            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
                        );

                        $ch = curl_init();
                        curl_setopt_array($ch, $opts);
                        $data = curl_exec($ch);
                        $info = curl_getinfo($ch);
                        curl_close($ch);
                        header("Content-Type: application/json");
                        $result = json_decode($data, true);
                        if ($result['status'] == 'ok') {
                            $qstatus = 1;
                        } else {
                            $qstatus = 0;
                        }
                        //$status = $db->perform('retaline_customer_wallet_transaction', $bcwt);
                        //5. An entry should be added in customer wallet transaction

                        break;
                    case 4: //COD with Wallet
                        $wcodAmounts = $db->getFromDB("SELECT order_amount_payable,order_wallet_amount,order_amount_returnon_cash,order_amount_addedon_wallet FROM retaline_customer_order WHERE order_id = {$order_no}", true);
                        $codAmt = $wcodAmounts['order_amount_payable'] - $wcodAmounts['order_amount_returnon_cash'];
                        $walletAmt = $wcodAmounts['order_wallet_amount'] - $wcodAmounts['order_amount_addedon_wallet'];
                        $item_sales_price = $wcodAmounts['order_wallet_amount'];


                        //4. THe customer wallet should be updated.

                        //$status = $db->query("UPDATE retaline_customer SET cust_walletbalance = cust_walletbalance + {$item_sales_price}  WHERE cust_id = {$order_customer_id}");
                        //4. THe customer wallet should be updated.
                        //5. An entry should be added in customer wallet transaction

                        $bcwt['stiid_barcode'] = '';
                        $bcwt['cust_id'] = $order_customer_id;
                        $bcwt['refentry_id'] = $order_no;
                        $bcwt['brcw_SourceType'] = 1;
                        $bcwt['brcw_Amount'] = $item_sales_price;

                        $bcwt['brcw_CreatedOn'] = date("Y-m-d H:i:s");
                        $bcwt['brcw_Updateon'] = date("Y-m-d H:i:s");
                        $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'WALLET_API'");
                        $fields = array(
                            "customer_id" => $order_customer_id,
                            "order_id" => $order_no,
                            "source_type" => 1,
                            "amount" => $item_sales_price,
                            "information" => "You Cancelled the Order {$order_order_id} from {$branch_name}",
                            "barcode" => 0
                        );
                        $fields_string = json_encode($fields);
                        //print_r($fields_string);
                        $opts = array(
                            CURLOPT_URL => $url,
                            CURLINFO_CONTENT_TYPE => "application/json",
                            CURLOPT_BINARYTRANSFER => TRUE,
                            CURLOPT_RETURNTRANSFER => TRUE,
                            CURLOPT_POST => count($fields),
                            CURLOPT_POSTFIELDS => $fields_string,
                            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
                        );

                        $ch = curl_init();
                        curl_setopt_array($ch, $opts);
                        $data = curl_exec($ch);
                        $info = curl_getinfo($ch);
                        curl_close($ch);
                        header("Content-Type: application/json");
                        $result = json_decode($data, true);
                        if ($result['status'] == 'ok') {
                            $qstatus = 1;
                        } else {
                            $qstatus = 0;
                        }
                        //5. An entry should be added in customer wallet transaction

                        break;
                }
                $statusFinascop = OrderCancelFinascop::OrderCancellationVoucher($order_no);
            }


            $qstatus = $db->query('commit');

            if ($qstatus == 1) {
                $msg = "Order cancelled succesfully";
                echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
            } else {
                $msg = "Error Occured";
                echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
            }
        }

        break;
}
