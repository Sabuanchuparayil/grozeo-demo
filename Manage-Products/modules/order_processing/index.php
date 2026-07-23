<?php

global $db;

function updateOrderLog($log_entry) {
    global $db;
    $log_entry['action_by'] = $_SESSION['admin']->UserId;
    $log_entry['action_at'] = 'now()';
    $db->perform('order_log', $log_entry);
}

switch ($op) {

    case 'listorders':

        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;

        $_allowed_sort = ['online_booking_id', 'order_created_on', 'order_total_amount', 'order_status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'online_booking_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'online_booking_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');

        $filter_qry = " WHERE 1 = 1 ";
        if (isset($_POST['filter'])) {
            $allowedFields = ['order_generated_id', 'member_phone', 'mst_plan_name', 'online_payment_amount', 'member_nominee', 'order_created_on', 'online_booking_date', 'order_user_type', 'order_status', 'payment_response_status', 'order_id', 'order_auto_id', 'order_note'];
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        $userID = $_SESSION['admin']->UserId;

        $countQuery = "SELECT COUNT(*) "
                . " FROM order_table  ";




        $listQuery = "SELECT order_auto_id ,order_generated_id, order_user_type,order_status_sort_order, order_user_mobile,order_total_amount, TIME_FORMAT(cast(order_created_on as time),'%r') as ordertime,
                        order_status_config_name AS order_status,order_tax,DATE_FORMAT(order_created_on,'%d-%m-%Y') AS order_created_on,
                        request_generated_id,
                        (SELECT customer_name FROM `customers` WHERE customer_id=order_payment_by) as payment_by
                        FROM order_table
                        INNER JOIN order_status_config ON order_status_config_id = order_status"
                . " {$filter_qry}   ORDER BY {$sort} {$dir} limit " . $start . "," . $limit;

        $db->printGridJson($countQuery, $listQuery);

        break;

    case 'getOrders':

        $isb2cso = $_POST['isb2cso'];
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;

        $_allowed_sort = ['online_booking_id', 'order_created_on', 'order_total_amount', 'order_status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'online_booking_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'order_id' : $sort;
//        $sort = ($sort=='order_created_on') ? 'DATE(order_created_on)' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $userID = $_SESSION['admin']->UserId;
        $filter_qry = "WHERE 1=1 ";

        $order = '';
        if (isset($_POST['filter'])) {
            $allowedFields = ['order_generated_id', 'member_phone', 'mst_plan_name', 'online_payment_amount', 'member_nominee', 'order_created_on', 'online_booking_date', 'order_user_type', 'order_status', 'payment_response_status', 'order_id', 'order_auto_id', 'order_note'];
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }

        if ($_POST['current_branch_id'] > 0) {
            $current_branch_id = $_POST['current_branch_id'];
        } else {
            $current_branch_id = $db->getItemSafe("SELECT br_ID FROM finascop_branch WHERE br_Name = ?", "s", [$_POST['br_Name']]);
        }
        if ($sort == 'order_created_on') {
            $sort = 'order_id';
        }
        if ($_POST['br_Name'] != '') {
            $filter_qry .= " AND br_Name  LIKE  '" . $_POST['br_Name'] . "%'";
        }
        if ($isb2cso == 1) {
            $filter_qry .= " AND status IN (4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,20,23) ";
        }


        $query = "SELECT bco.order_id,bco.order_order_id,order_packedbags_count,
bco.order_customer_id,order_branch_id,br_Name,
 bco.status_id AS STATUS,DATE_FORMAT(bco.created_at,'%d-%m-%Y') AS order_created_on,
 TIME_FORMAT(CAST(bco.created_at AS TIME),'%r') AS ordertime,admin_description AS order_status,
 admin_description,order_payment_gateway_refid,order_payment_gateway_refid_crc32,
CASE
    WHEN order_method = 1 THEN 'Drive Delivery'
    WHEN order_method = 2 THEN 'Customer Collect'
    WHEN order_method = 3 THEN 'Courier Delivery'
END AS order_method,
(SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS delivery_to,
(SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS cust_mobile,
            order_HasReturn,order_ItemsReturned,order_ReturnVerified,bco.created_at,
            order_latitude,order_longitude
            FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN retaline_customer_order_delivery_address bcoda ON bcoda.customer_order_id = bco.order_id
                        INNER JOIN finascop_branch ON br_ID = order_branch_id 
                        WHERE 1 = 1 AND bco.status_id > 0 AND order_branch_id = {$current_branch_id}";
        $countQuery = " SELECT COUNT(*) FROM ({$query}) AS orderCount {$filter_qry} ORDER BY  {$sort} {$dir} ";
        $listQuery = "SELECT * FROM({$query}) as orderList  {$filter_qry}  ORDER BY  {$sort} {$dir} limit " . $start . "," . $limit;
//CAST({$sort} as char) {$dir},binary {$sort} {$dir}
        $db->printGridJson($countQuery, $listQuery);

        break;


    case 'detailsView':
        $order_auto_id = isset($_POST['order_auto_id']) ? intval($_POST['order_auto_id']) : 0;

        if ($order_auto_id > 0) {
            $data = $db->getFromDB(" SELECT order_auto_id ,order_generated_id, order_user_type, order_user_mobile,order_total_amount, 
            order_status_config_name as order_status,order_tax,DATE_FORMAT(order_created_on,'%d-%m-%Y') as order_created_on, request_generated_id"
                    . " FROM order_table "
                    . " inner join order_status_config on order_status_config_id = order_status"
                    . " WHERE order_auto_id =' " . $order_auto_id . "'", true);

            $data['success'] = true;
            echo json_encode($data);
        }

        break;
    case "order_details":
        require(THIS_MODULE_PATH . "/order_details.php");
        break;
    case 'oreder_userlog_dtlsview':
        ob_start();
        include('order_log.php');
        $resHtml = ob_get_clean();
        echo $resHtml;
        break;

    case 'downloadInvoice':
        ob_start();
        include('Invoice.php');
        $rehtml = ob_get_clean();
        echo $rehtml;
        exit;
//        $attach = $order_generated_id . '.pdf';
//        HtmlContentOperations :: outWithPdfFromHtml($rehtml, $attach);
        break;

    case 'updateOrderStatus':
        $data = $_POST;
        if ($data['order_id'] > 0) {
            switch ($data['action']) {
                case 1:
                    $up = array('order_status' => 4);
                    $message = 'Order Printed';
                    break;
                case 2:
                    $up = array('order_status' => 5);
                    $message = 'Printed the pay slip';
                    break;
                case 3:
                    $up = array('order_status' => 6);
                    $message = 'Dispatched';

                    $disp = array('dispatch_courier' => $data['order_dispatch_courier'],
                        'dispatch_consignment' => $data['order_dispatch_consignment']);
                    // var_dump($disp);exit;
                    $db->perform('order_table', $disp, 'update', 'order_auto_id=' . $data['order_id']);

                    break;
                case 4:
                    $up = array('order_status' => 7);
                    $message = 'Delivered';

                    $del = array('delivery_at_date' => date('Y-m-d', strtotime($data['order_delivery_at_date'])),
                        'delivery_at_time' => $data['order_delivery_at_time'],
                        'delivery_notes' => $data['order_delivery_notes']);

                    $db->perform('order_table', $del, 'update', 'order_auto_id=' . $data['order_id']);

                    break;
            }
            if (!empty($up)) {

                $old = $db->getItemFromDB("SELECT order_status FROM `order_table` WHERE order_auto_id = {$data['order_id']}");

                /*  if ($old >= 4 && ($up['order_status'] == 4 || $up['order_status'] == 5 || $up['order_status'] == 6 || $up['order_status'] == 7)) {

                  } else {
                  $db->perform('order_table', $up, 'update', 'order_auto_id=' . $data['order_id']);
                  } */

                //if ($old < $up['order_status'])
                $db->perform('order_table', $up, 'update', 'order_auto_id=' . $data['order_id']);

                $log_entry = array('order_id' => $data['order_id'],
                    'old_status' => $old,
                    'current_status' => $up['order_status'],
                    'message' => $message);

                updateOrderLog($log_entry);
                echo '{"success":true,"valid":true}';
                exit;
            }
            echo '{"success":true,"valid":false}';
        }
        break;

    case 'orderPrint':
        ob_start();
        include('printView.php');
        $resHtml = ob_get_clean();
        echo $resHtml;
        break;

    case 'thermalsticker':

        $order_auto_id = $_REQUEST['order_id'];
        $order_generated_id = $_REQUEST['order_generated_id'];
        ob_start();
        include('thermalsticker.php');
        $resHtml = ob_get_clean();
        echo $resHtml;
        break;

    case 'orderPrintSlip':
        ob_start();
        include('printSlipView.php');
        $resHtml = ob_get_clean();
        echo $resHtml;
        break;

    case 'sendSMS':
        $data = $_POST;
        $order_dispatch_courier_id = $data['order_dispatch_courier'];
        $order_dispatch_courier_det = $db->getFromDB("SELECT mst_courier_name,mst_courier_url FROM `mst_courier` WHERE mst_courier_id = {$order_dispatch_courier_id}", TRUE);
        $order_dispatch_courier_name = $order_dispatch_courier_det['mst_courier_name'];
        $mst_courier_url = $order_dispatch_courier_det['mst_courier_url'];
        $number = $data['order_user_mobile'];
        $delivery_to = $data['delivery_to'];

        //$numbers = explode(',', $data['contact_id']);
//        foreach ($numbers as $number) {
//            $contacts = $data['contact_id'];
//            $message = $data['sms_content'];

        $message = 'Dear ' . $delivery_to . ',' . PHP_EOL .
                'Your Order with Order ID ' . $data['order_generated_id'] . ' has been dispatched through Courier '
                . $order_dispatch_courier_name . '.' . PHP_EOL . 'Please use Consignment Number ' . $data['order_dispatch_consignment']
                . ' to track your order from ' . $mst_courier_url;
        $starttime = microtime();

        $smsresponse = sms::send($number, $message);
        if ($smsresponse == 1) {
            $endtime = microtime();
            $latency = $endtime - $starttime;
            $smsdata = array(
                'sms_to' => $number,
                'sms_content' => $message,
                'sms_latency' => $latency
            );
            $status = $db->perform("sys_sms_send_entry", $smsdata);
        }
//        }

        if ($status) {
            echo "{success:true,valid:true,message:'SMS Sent'}";
        } else {
            echo "{success:false,valid:false,message:'SMS Sending Failed'}";
        }
        break;
    case 'deliverManually':
        $bcodaat['status_id'] = 16;
        $db->query('begin');
        $status = $db->perform('retaline_customer_order', $bcodaat, 'update', "order_id = " . intval($_POST['order_id']));
        $status = $db->query('commit');
        if ($status > 0) {
            echo "{success: true, msg:  'Order Deispatched' }";
        } else {
            echo "{success: false, errors:  'FINASCOP: Error occured ' }";
        }
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
        for ($i = 0; $i < $count; $i++) {
            $bcDetails[$i] = $db->getFromDB("SELECT customer_order_id,item_id,stiid_id,stiid_barcode FROM retaline_customer_order_items_barcodes WHERE stiid_barcode = '{$arrRetItems[$i]}' AND customer_order_id = {$order_id}", true);
        }
        //print_r($bcDetails);
        $resCount = count($bcDetails);
        if (!empty($bcDetails)) {
            for ($j = 0; $j < $resCount; $j++) {
                $datas[$j]['order_id'] = $bcDetails[$j]['customer_order_id'];
                $datas[$j]['stiid_barcode'] = $bcDetails[$j]['stiid_barcode'];
                $datas[$j]['itemId'] = $bcDetails[$j]['item_id'];
                $datas[$j]['stiid_id'] = $bcDetails[$j]['stiid_id'];
                $datas[$j]['itemName'] = $db->getItemFromDB("SELECT stiid_itemmastername FROM finascop_stock_item_inventorydetails WHERE stiid_id = {$bcDetails[$j]['stiid_id']}");
                $datas[$j]['item_sales_price'] = $db->getItemFromDB("SELECT item_sales_price FROM retaline_customer_order_items WHERE item_id = {$bcDetails[$j]['item_id']} AND customer_order_id = {$bcDetails[$j]['customer_order_id']}");
                $datas[$j]['item_product_id'] = $db->getItemFromDB("SELECT item_product_id FROM retaline_customer_order_items WHERE item_id = {$bcDetails[$j]['item_id']} AND customer_order_id = {$bcDetails[$j]['customer_order_id']}");
                $datas[$j]['returnCount'] = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer_order_return_barcode WHERE stiid_barcode = '{$bcDetails[$j]['stiid_barcode']}' AND customer_order_id = {$bcDetails[$j]['customer_order_id']}");
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
            //echo json_encode($qry);
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';

        break;
    case 'moveRetunItemstoStock':
        /*
         * [order_id] => 8
          [stiid_barcode] => 224455778904
          [order_order_id] => PKT2303200008
          [itemId] => 10
          [stiid_id] => 104
         */
        $order_customer_id = $db->getItemSafe("SELECT order_customer_id FROM retaline_customer_order WHERE order_id = ?", "i", [$_POST['order_id']]);
        $order_branch_id = $db->getItemSafe("SELECT order_branch_id FROM retaline_customer_order WHERE order_id = ?", "i", [$_POST['order_id']]);
        $item_product_id = $db->getItemSafe("SELECT item_product_id FROM retaline_customer_order_items WHERE item_id = ? AND customer_order_id = {$_POST['order_id']}", "i", [$_POST['itemId']]);
        $itemCountEntry = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory WHERE branch_id = {$order_branch_id} AND stit_id =  {$item_product_id}");
        $stit_id = $db->getFromSafe("SELECT stiid_mrp,stiid_selpri,stiid_itemmasterid,fsbg_id,stiid_itemmastername FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = ?", "s", [$_POST['stiid_barcode']], true);
        $payment_mode = $db->getItemSafe("SELECT payment_mode FROM retaline_customer_order WHERE order_id = ?", "i", [$_POST['order_id']]);
        $db->query('begin');
        //1. The branch's stock item count should increase.

        if ($itemCountEntry > 0) {
            //fsbg_id
            $status = $db->query("UPDATE finascop_stock_branch_inventory SET item_count = item_count + 1 WHERE stit_id = {$item_product_id} AND branch_id = {$order_branch_id}");
        } else {

            $fsbi['stit_id'] = $stit_id['stiid_itemmasterid'];
            $fsbi['branch_id'] = $order_branch_id;
            $fsbi['item_count'] = 1;
            $fsbi['mrp'] = $stit_id['stiid_mrp'];
            $fsbi['selling_price'] = $stit_id['stiid_selpri'];
            $fsbi['updated_on'] = date('Y-m-d H:i:s');
            $fsbi['fsbg_id'] = $stit_id['fsbg_id'];
            $status = $db->perform('finascop_stock_branch_inventory', $fsbi);
        }
        //1. The branch's stock item count should increase.
        //2. THe Barcode history should have a new entry -
        $fsiim['stiid_id'] = $_POST['stiid_id'];
        $fsiim['stiidm_itemmasterid'] = $stit_id['stiid_itemmasterid'];
        $fsiim['stiidm_barcode'] = $_POST['stiid_barcode'];
        $fsiim['stiidm_details'] = "Item returned from customer in order - {$_POST['order_id']} ";
        $fsiim['created_at'] = date("Y-m-d H:i:s");
        $fsiim['updated_at'] = date("Y-m-d H:i:s");
        $status = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiim);
        //2. THe Barcode history should have a new entry -
        //3. Barcode table status should change so that the item is ready for sale
        $status = $db->executeSafe("UPDATE finascop_stock_item_inventorydetails SET stiid_status = 4 WHERE stiid_barcode = ?", "s", [$_POST['stiid_barcode']]);
        //3. Barcode table status should change so that the item is ready for sale
        //Capture the basrcode returned  and order items count 

        $order_ItemsReturned = $db->getItemSafe("SELECT order_ItemsReturned FROM retaline_customer_order WHERE order_id = ?", "i", [$_POST['order_id']]);
        $order_ItemsReturned = str_replace('[', '', $order_ItemsReturned);
        $order_ItemsReturned = str_replace(']', '', $order_ItemsReturned);
        $arrRetItems = explode(',', $order_ItemsReturned);

        $countorder_ItemsReturned = count($arrRetItems);
        $orderItemCount = $db->getItemSafe("SELECT SUM(item_order_qty) FROM retaline_customer_order_items WHERE customer_order_id = ?", "i", [$_POST['order_id']]);
        $confirmedReturn = $db->getMultipleSafe("SELECT stiid_barcode FROM retaline_customer_order_return_barcode WHERE customer_order_id = ?", "i", [$_POST['order_id']]);
        if (empty($confirmedReturn)) {
            $confirmedReturn = [];
        }
        $returnDiff = array_diff($arrRetItems, $confirmedReturn);
        $count = count($returnDiff);

        $order_roundoff = $db->getItemSafe("SELECT order_roundoff FROM retaline_customer_order WHERE order_id = ?", "i", [$_POST['order_id']]);
        $order_delivery_charge = $db->getItemSafe("SELECT order_delivery_charge FROM retaline_customer_order WHERE order_id = ?", "i", [$_POST['order_id']]);
        $itemPrices = $db->getFromSafe("SELECT item_price,item_discount,item_order_qty FROM retaline_customer_order_items WHERE item_id = ? AND customer_order_id = {$_POST['order_id']}", "i", [$_POST['itemId']], true);
        if (($countorder_ItemsReturned == $orderItemCount) && ($count == 1)) {
            $item_sales_price = floatval($itemPrices['item_price']) - floatval($itemPrices['item_discount']) + floatval($order_delivery_charge) + floatval($order_roundoff);
            $bcwt['brcw_AddInfo'] = "On sales return of {$stit_id['stiid_itemmastername']} with delivery charge on order {$_POST['order_id']}";
        } else {
            $bcwt['brcw_AddInfo'] = "On sales return of {$stit_id['stiid_itemmastername']} on order {$_POST['order_id']}";
            if (floatval($itemPrices['item_order_qty']) > 0) {
                $item_sales_price = (floatval($itemPrices['item_price']) - floatval($itemPrices['item_discount'])) / $itemPrices['item_order_qty'];
            } else {
                $item_sales_price = 0;
            }
        }
        //echo '$item_sales_price'.$item_sales_price;exit();
        switch ($payment_mode) {
            case 2://onlinepayment
            case 3://Wallet
            case 5://online with Wallet
                //4. THe customer wallet should be updated.

                $status = $db->query("UPDATE retaline_customer SET cust_walletbalance = cust_walletbalance + {$item_sales_price}  WHERE cust_id = {$order_customer_id}");
                //4. THe customer wallet should be updated.
                //5. An entry should be added in customer wallet transaction
                $bcwt['cust_id'] = $order_customer_id;
                $bcwt['refentry_id'] = $_POST['order_id'];
                $bcwt['brcw_SourceType'] = 1;
                $bcwt['brcw_Amount'] = $item_sales_price;
                $bcwt['stiid_barcode'] = $_POST['stiid_barcode'];
                $bcwt['brcw_CreatedOn'] = date("Y-m-d H:i:s");
                $bcwt['brcw_Updateon'] = date("Y-m-d H:i:s");
                $status = $db->perform('retaline_customer_wallet_transaction', $bcwt);
                //5. An entry should be added in customer wallet transaction

                break;
            case 4://COD with Wallet
                $wcodAmounts = $db->getFromSafe("SELECT order_amount_payable,order_wallet_amount,order_amount_returnon_cash,order_amount_addedon_wallet FROM retaline_customer_order WHERE order_id = ?", "i", [$_POST['order_id']], true);
                $codAmt = $wcodAmounts['order_amount_payable'] - $wcodAmounts['order_amount_returnon_cash'];
                $walletAmt = $wcodAmounts['order_wallet_amount'] - $wcodAmounts['order_amount_addedon_wallet'];
                if ($codAmt >= $item_sales_price) {
                    $status = $db->executeSafe("UPDATE retaline_customer_order SET order_amount_returnon_cash = order_amount_returnon_cash + {$item_sales_price}  WHERE order_id = ?", "i", [$_POST['order_id']]);
                }
                if ($codAmt < $item_sales_price) {

                    if ($codAmt == 0) {
                        $status = $db->executeSafe("UPDATE retaline_customer_order SET order_amount_addedon_wallet = order_amount_addedon_wallet + {$item_sales_price}  WHERE order_id = ?", "i", [$_POST['order_id']]);
                    } else {
                        $status = $db->executeSafe("UPDATE retaline_customer_order SET order_amount_returnon_cash = order_amount_returnon_cash + {$codAmt} WHERE order_id = ?", "i", [$_POST['order_id']]);
                        $item_sales_price = $item_sales_price - $codAmt;
                        $status = $db->executeSafe("UPDATE retaline_customer_order SET order_amount_addedon_wallet = order_amount_addedon_wallet + {$item_sales_price}  WHERE order_id = ?", "i", [$_POST['order_id']]);
                    }
                    //4. THe customer wallet should be updated.

                    $status = $db->query("UPDATE retaline_customer SET cust_walletbalance = cust_walletbalance + {$item_sales_price}  WHERE cust_id = {$order_customer_id}");
                    //4. THe customer wallet should be updated.
                    //5. An entry should be added in customer wallet transaction
                    $bcwt['cust_id'] = $order_customer_id;
                    $bcwt['refentry_id'] = $_POST['order_id'];
                    $bcwt['brcw_SourceType'] = 1;
                    $bcwt['brcw_Amount'] = $item_sales_price;
                    $bcwt['stiid_barcode'] = $_POST['stiid_barcode'];
                    $bcwt['brcw_CreatedOn'] = date("Y-m-d H:i:s");
                    $bcwt['brcw_Updateon'] = date("Y-m-d H:i:s");
                    $status = $db->perform('retaline_customer_wallet_transaction', $bcwt);
                    //5. An entry should be added in customer wallet transaction
                }
                break;
        }

        //6. Capture the basrcode returned in the customer_order_return_barcode
        $bcorb['customer_order_id'] = $_POST['order_id'];
        $bcorb['stiid_barcode'] = $_POST['stiid_barcode'];
        $bcorb['created_on'] = date("Y-m-d H:i:s");
        $bcorb['updated_on'] = date("Y-m-d H:i:s");
        $status = $db->perform('retaline_customer_order_return_barcode', $bcorb);
        //6. Capture the basrcode returned in the customer_order_return_barcode
        $confirmedReturned = $db->getMultipleSafe("SELECT stiid_barcode FROM retaline_customer_order_return_barcode WHERE customer_order_id = ?", "i", [$_POST['order_id']]);
        $returnDiff = array_diff($arrRetItems, $confirmedReturned);
        $fincount = count($returnDiff);
        if ($fincount == 0) {
            $status = $db->executeSafe("UPDATE retaline_customer_order SET order_ReturnVerified = 1  WHERE order_id = ?", "i", [$_POST['order_id']]);
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true, msg:  'Item back to stock' }";
        } else {
            echo "{success: false, errors:  'FINASCOP: Error occured ' }";
        }
        break;
    case 'getCustomerDetails':
        $order_customer_mobile = $_POST['order_customer_mobile'];
        $customerDetails = $db->getFromDB("SELECT cust_id,cust_customer_id,cust_mobile,cust_email,cust_customer_name FROM retaline_customer WHERE cust_mobile = {$order_customer_mobile}", true);
        if (!empty($customerDetails)) {
            echo json_encode($customerDetails);
        }
        break;
    case 'generateUniqueId':
        $uniqueId = '';
        while ($uniqueId == '') {
            $uniqueId = getNewFinascopApiKey();
        }
        echo '{"uid":"' . $uniqueId . '"}';
        break;
    case 'listItemsForSO':
        $uuid = $_POST['uuid'];
        $fstr_id = $_POST['fstr_id'];
        $rec_sort = empty($data['sort']) ? 'fstr_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['order_generated_id', 'member_phone', 'mst_plan_name', 'online_payment_amount', 'member_nominee', 'order_created_on', 'online_booking_date', 'order_user_type', 'order_status', 'payment_response_status', 'order_id', 'order_auto_id', 'order_note'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        if ($fstr_id > 0) {
            $cond = " AND fstr_id = {$fstr_id}";
        } else {
            if (!empty($uuid))
                $cond = " AND uuid = '{$uuid}'";
            else
                $cond = "AND uuid = 0";
        }
        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . "finascop_stock_transfer_request_details where {$filter_part} {$cond}";
        $listQuery = "SELECT uuid,fstr_id,fstrd_id,fstr_ItemId,fstr_RequiredItemQty,fstrd_status,(SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = fstr_ItemId) as fstr_ItemName "
                . "from " . FINASCOP_DB . "finascop_stock_transfer_request_details WHERE {$filter_part} {$cond} ORDER BY $rec_sort $rec_sort_dir ";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'barcodeCheckForso':
        $barcode = $_POST['barcodesearch_field'];
        $branch = $_SESSION['admin']->finascop_current_branch_id;
        $isReturn = 0;
        $db->query('begin');
        if ($barcode != '') {

            $unique_brcode = $db->getItemFromDB("SELECT count(tmp_barcode_code) FROM finascop_stock_transfer_order_details_barcodes_temp WHERE tmp_barcode_code={$barcode} and branch_id = {$branch} AND "
                    . "rpb_status = 1 and tmp_barcode_isReturn = {$isReturn}");

            $brcodeInCustOrderTmp = $db->getItemFromDB("SELECT count(coib_id) FROM retaline_customer_order_items_barcodes WHERE stiid_barcode={$barcode} and branch_id = {$branch} ");
            $cpd = $db->getItemFromDB("SELECT is_branch FROM finascop_stock_item_inventorydetails WHERE stiid_barcode={$barcode} AND cpd_branch_id={$branch}");
            if ($cpd == 0) {
                $brCdItemId = $db->getItemFromDB("SELECT stiid_itemmasterid FROM finascop_stock_item_inventorydetails WHERE stiid_barcode={$barcode} AND stiid_status=1 AND cpd_branch_id={$branch}");
            } else {
                $brCdItemId = $db->getItemFromDB("SELECT stiid_itemmasterid FROM finascop_stock_item_inventorydetails WHERE stiid_barcode={$barcode} AND (stiid_status=1 OR stiid_status=4) AND cpd_branch_id={$branch}");
            }
            if ($brCdItemId) {
                $inventoryDetails = $db->getFromDB("SELECT * FROM finascop_stock_item_inventorydetails WHERE stiid_barcode={$barcode}", true);
                $ItemDetails = $db->getFromDB("SELECT stit_GST,cos_nos FROM finascop_stock_itemmaster WHERE stit_ID={$brCdItemId}", true);
                $icrPkdQty['stiid_barcode'] = $barcode;
                $icrPkdQty['item_id'] = $brCdItemId;
                $icrPkdQty['stiid_id'] = $inventoryDetails['stiid_id'];
                $icrPkdQty['branch_id'] = date('Y-m-d H:i:s');
                $icrPkdQty['unique_id'] = $_POST['unique_id'];
                $icrPkdQty['branch_id'] = $branch;
                $icrPkdQty['stiid_itemmastername'] = $inventoryDetails['stiid_itemmastername'];
                $icrPkdQty['stiid_expirydate'] = $inventoryDetails['stiid_expirydate'];
                $icrPkdQty['itemmrp'] = $inventoryDetails['stiid_mrp'];
                $icrPkdQty['itemselling_price'] = $inventoryDetails['stiid_selpri'];
                $icrPkdQty['fpod_leastSKUmrp'] = $inventoryDetails['stiid_leastSKUmrp'];
                $icrPkdQty['fpod_customerRatePikup'] = $inventoryDetails['fpod_customerRatePikup'];
                $selling_price = $inventoryDetails['fpod_customerRatePikup'] * $ItemDetails['cos_nos'];
                $price_tax = ($ItemDetails['stit_GST'] * $selling_price ) / 100;
                $basket_price = $selling_price - $price_tax;
                $mrp = $inventoryDetails['stiid_leastSKUmrp'] * $ItemDetails['cos_nos'];
                $percentage = ($mrp - $selling_price) * 100 / $mrp;
                $icrPkdQty['sales_price'] = $selling_price;
                $icrPkdQty['retail_price'] = $mrp;
                $icrPkdQty['item_gst'] = $ItemDetails['stit_GST'];
                $icrPkdQty['item_cgst'] = round($ItemDetails['stit_GST']/2,2);
                $icrPkdQty['item_sgst'] = round($ItemDetails['stit_GST']/2,2);
                $icrPkdQty['basket_price'] = $basket_price;
                $icrPkdQty['price_tax'] = $price_tax;


                if ($unique_brcode == 0 && $brcodeInCustOrderTmp == 0) {
                    $status = $db->perform('retaline_customer_order_items_barcodes', $icrPkdQty);
                    $lastId = $db->insert_id();
                } else {
                    echo '{"success":false,"valid":false,"msg":"Barcode already entered"}';
                    exit();
                }

                $status = $db->query('commit');
                if ($status == 1) {
                    $msg = "'tmp data added'";
                    echo '{"success":true,"valid":true,"msg":' . $msg . ',item_id :' . $item_id . '}';
                } else {
                    $msg = "'Error occured while saving.'";
                    echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
                }
            } else {
                echo '{"success":false,"valid":false,"msg":"Already packed"}';
                exit();
            }
        }
        break;
}
?>


