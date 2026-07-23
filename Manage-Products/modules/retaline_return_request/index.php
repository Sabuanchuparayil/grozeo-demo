<?php
global $db;
require_once (INCLUDE_PATH . "/finascop_wallet_client.php");
require_once (ROOT . '/finascop_config/lib.php');

switch ($op)
{
    case 'getReturnedOrders':

        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;

        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'rtrqo_id' : $sort;
        //        $sort = ($sort=='order_created_on') ? 'DATE(order_created_on)' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array(
            'gt' => '>',
            'lt' => '<',
            'eq' => '='
        );
        $userID = $_SESSION['admin']->UserId;
        $filter_qry = " ";

        $order = '';
        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
                }
            }

            if ($_POST['current_branch_id'] > 0)
            {
                $current_branch_id = $_POST['current_branch_id'];
            }
            else
            {
                $current_branch_id = $db->getItemSafe("SELECT br_ID FROM finascop_branch WHERE br_Name = ?", "s", [$_POST['br_Name']]);
            }

            switch ($sort)
            {

                default:
                    $order .= "ORDER BY CAST({$sort} as char) {$dir},binary {$sort} {$dir}";
                break;
            }
            if ($_POST['br_Name'] != '')
            {
                $filter_qry .= "AND br_Name  LIKE  '" . $_POST['br_Name'] . "%'";
            }
            if ($current_branch_id > 0)
            {
                $pyramidLevel = $db->getItemFromDB("SELECT br_PyramidLevel  FROM finascop_branch WHERE br_ID = $current_branch_id");
                if ($pyramidLevel == 4)
                {
                    $countQuery = "SELECT COUNT(*) " . " FROM finascop_stock_return_request_order INNER JOIN retaline_customer_order bco ON bco.order_id = finascop_stock_return_request_order.order_id 
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id " . " WHERE 1=1 and bco.status_id > 0 AND rtrqo_type = 0 AND order_ItemsReturned > 0 AND order_branch_id = {$current_branch_id}   {$filter_qry}  ORDER BY bco.order_id desc";

                    $listQuery = " SELECT DISTINCT(finascop_stock_return_request_order.order_id),rtrqo_id,bco.order_id,order_order_id,order_packedbags_count,order_customer_id,order_branch_id,br_Name,bco.status_id as status,
                DATE_FORMAT(created_at,'%d-%m-%Y') AS order_created_on,TIME_FORMAT(cast(created_at as time),'%r') as ordertime,admin_description AS order_status,
            (SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = order_customer_id) AS delivery_to,rtrqo_sourceType,
            (SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = order_customer_id) AS cust_mobile,order_itemReturnRequestCount,
            order_HasReturn,order_ItemsReturned,order_ReturnVerified
            FROM finascop_stock_return_request_order INNER JOIN retaline_customer_order bco ON bco.order_id = finascop_stock_return_request_order.order_id
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id  " . " WHERE 1 = 1 and bco.status_id > 0 AND rtrqo_type = 0  AND order_branch_id = {$current_branch_id}  {$filter_qry}  GROUP BY bco.order_id ORDER BY bco.order_id desc limit " . $start . "," . $limit;

                    //        $db->printGridJson($countQuery, $listQuery);
                    $datas = $db->getMulipleData($listQuery, true);
                    $resCount = count($datas);
                    $count = $db->getItemFromDB($countQuery);
                    if (!empty($datas))
                    {
                        for ($i = 0;$i < $resCount;$i++)
                        {
                            $order_cancellationdets = $db->getFromDB("SELECT reason,if(cancelled_by_type = 1,'Customer','Back Office') as cancelled_by_type_name,cancelled_by_type," . "(SELECT CONCAT(FirstName,' ',LastName) FROM finascop_usr_profile WHERE UserId = cancelled_by_id) as cancelled_by_name,cancelled_by_id FROM retaline_customer_order_cancellationdets " . "where order_id = {$datas[$i]['order_id']}", true);
                            $datas[$i]['order_qty'] = $db->getItemFromDB("SELECT SUM(item_order_qty) FROM retaline_customer_order_items WHERE customer_order_id = {$datas[$i]['order_id']}");
                        }
                    }
                }
                else
                {
                    $countQuery = "SELECT COUNT(*) " . " FROM finascop_stock_return_request_order rsc INNER JOIN retaline_B2B_SalesOrder bco ON bco.bbso_id = rsc.order_id 
                        INNER JOIN retaline_B2B_Status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch fbrn ON fbrn.br_ID = bco.br_ID " . " WHERE 1=1 and bco.status_id > 0 AND rtrqo_type = 0 AND order_itemReturnRequestCount > 0 AND bco.br_ID = {$current_branch_id}   {$filter_qry}  ORDER BY bco.bbso_id desc";

                    $listQuery = " SELECT DISTINCT(rsc.order_id),rtrqo_id,bco.bbso_id as order_id,bbso_SONumber as order_order_id,bbso_packedbags_count as order_packedbags_count,
                    b2b_Customer_ID as order_customer_id,bco.br_ID as order_branch_id,br_Name,bco.status_id as status,
                DATE_FORMAT(bbso_createdon,'%d-%m-%Y') AS order_created_on,TIME_FORMAT(cast(bbso_createdon as time),'%r') as ordertime,status AS order_status,
            b2b_Customer_Name AS delivery_to,rtrqo_sourceType,
            (SELECT b2b_Customer_Mobile FROM `retaline_B2Bcustomer` WHERE retaline_B2Bcustomer.b2b_Customer_ID = bco.b2b_Customer_ID) AS cust_mobile,order_itemReturnRequestCount,
            bbso_HasReturn as order_HasReturn,bbso_ItemsReturned as order_ItemsReturned,bbso_ReturnVerified as order_ReturnVerified
            FROM finascop_stock_return_request_order rsc INNER JOIN retaline_B2B_SalesOrder bco ON bco.bbso_id = rsc.order_id 
                        INNER JOIN retaline_B2B_Status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch fbrn ON fbrn.br_ID = bco.br_ID   " . " WHERE 1 = 1 and bco.status_id > 0 AND rtrqo_type = 0 AND order_itemReturnRequestCount > 0 AND bco.br_ID = {$current_branch_id}  {$filter_qry}  GROUP BY bco.bbso_id ORDER BY bco.bbso_id desc limit " . $start . "," . $limit;

                    //        $db->printGridJson($countQuery, $listQuery);
                    $datas = $db->getMulipleData($listQuery, true);
                    $resCount = count($datas);
                    $count = $db->getItemFromDB($countQuery);
                    if (!empty($datas))
                    {
                        for ($i = 0;$i < $resCount;$i++)
                        {

                            $datas[$i]['order_qty'] = $db->getItemFromDB("SELECT SUM(b2bso_itemqty) FROM retaline_B2B_SalesOrderDetails WHERE bbso_id = {$datas[$i]['order_id']}");
                        }
                    }
                }
            }

            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        break;
        case 'getOrdersDetails':
            $ordertype = $_POST['ordertype'];
            $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
            $limit = is_numeric($limit) ? $limit : 20;
            $start = is_numeric($start) ? $start : 0;
            $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';

            //        $sort = ($sort=='order_created_on') ? 'DATE(order_created_on)' : $sort;
            $dir = empty($dir) ? 'DESC' : $dir;
            $comparisons = array(
                'gt' => '>',
                'lt' => '<',
                'eq' => '='
            );
            $userID = $_SESSION['admin']->UserId;
            $filter_qry = " ";

            $order = '';

            if ($ordertype == 'B2B')
            {
                $sort = empty($sort) ? 'bbso_id' : $sort;

                if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
                        }
                    }

                    if ($_POST['current_branch_id'] > 0)
                    {
                        $current_branch_id = $_POST['current_branch_id'];
                    }
                    else
                    {
                        $current_branch_id = $db->getItemSafe("SELECT br_ID FROM finascop_branch WHERE br_Name = ?", "s", [$_POST['br_Name']]);
                    }
                    switch ($sort)
                    {

                        default:
                            $order .= "ORDER BY {$sort} {$dir}";
                        break;
                    }
                    if ($_POST['customer_mobileretreq'] != "")
                    {
                        if ($_POST['type'] == "mobile")
                        {
                            $mob_order = "AND rc.b2b_Customer_Mobile  ={$_POST['customer_mobileretreq']}";
                        }
                        else if ($_POST['type'] == "order")
                        {
                            $mob_order = "AND bbso_SONumber  = '{$_POST['customer_mobileretreq']}' ";
                        }

                        $countQuery = "SELECT COUNT(*) " . " FROM retaline_B2B_SalesOrder bco
                            INNER JOIN finascop_stock_transfer_order  ON fstr_id = bbso_id AND fsto_ordertype = 2
                            LEFT JOIN qugeo_order  ON quor_id = fsto_id AND quor_TransferOrder_Type = 2  AND quor_Status IN (15)
                        INNER JOIN retaline_B2B_Status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON finascop_branch.br_ID = bco.br_ID INNER JOIN retaline_B2Bcustomer rc ON rc.b2b_Customer_ID = bco.b2b_Customer_ID" . " WHERE 1=1  AND bco.status_id IN(9,29) AND bbso_HasReturnRequest = 0 {$mob_order} {$filter_qry}  {$order}";

                        $listQuery = " SELECT bbso_id as order_id,bbso_SONumber as order_order_id,bco.b2b_Customer_ID as order_customer_id,bco.br_ID as order_branch_id,br_Name,bco.status_id as status,DATE_FORMAT(bbso_createdon,'%d-%m-%Y') AS order_created_on,
                DATE_FORMAT(bbso_updatedon,'%Y-%m-%d %H:%i:%s') as ordertime,status AS order_status,DATE_FORMAT(bbso_updatedon,'%Y-%m-%d %H:%i:%s') as updated_at,
            bco.b2b_Customer_Name AS delivery_to,b2b_Customer_Mobile AS cust_mobile,
            bbso_HasReturn,bbso_ItemsReturned,bbso_ReturnVerified
            FROM retaline_B2B_SalesOrder bco 
            INNER JOIN finascop_stock_transfer_order  ON fstr_id = bbso_id AND fsto_ordertype = 2
            LEFT JOIN qugeo_order  ON quor_id = fsto_id AND quor_TransferOrder_Type = 2  AND quor_Status IN (15) 
                        INNER JOIN retaline_B2B_Status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON finascop_branch.br_ID = bco.br_ID INNER JOIN retaline_B2Bcustomer rc ON rc.b2b_Customer_ID = bco.b2b_Customer_ID" . " WHERE 1=1  AND bco.status_id IN(9,29) AND bbso_HasReturnRequest = 0  {$mob_order} {$filter_qry}  {$order} ";
                    }
                }
                else
                {
                    $sort = empty($sort) ? 'order_id' : $sort;
                    if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
                            }
                        }

                        if ($_POST['current_branch_id'] > 0)
                        {
                            $current_branch_id = $_POST['current_branch_id'];
                        }
                        else
                        {
                            $current_branch_id = $db->getItemSafe("SELECT br_ID FROM finascop_branch WHERE br_Name = ?", "s", [$_POST['br_Name']]);
                        }
                        switch ($sort)
                        {

                            default:
                                $order .= "ORDER BY {$sort} {$dir}";
                            break;
                        }
                        if ($_POST['customer_mobileretreq'] != "")
                        {
                            if ($_POST['type'] == "mobile")
                            {
                                $mob_order = "AND rc.cust_mobile  ={$_POST['customer_mobileretreq']}";
                            }
                            else if ($_POST['type'] == "order")
                            {
                                $mob_order = "AND order_order_id  ={$_POST['customer_mobileretreq']}";
                            }

                            $countQuery = "SELECT COUNT(*) " . " FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id INNER JOIN retaline_customer rc ON cust_id = order_customer_id" . " WHERE 1=1  AND bco.status_id IN(17,18) AND order_HasReturnRequest = 0 {$mob_order} {$filter_qry}  {$order}";

                            $listQuery = " SELECT order_id,order_order_id,order_mrp,order_packedbags_count,order_customer_id,order_branch_id,br_Name,bco.status_id as status,DATE_FORMAT(created_at,'%d-%m-%Y') AS order_created_on,
                DATE_FORMAT(updated_at,'%Y-%m-%d %H:%i:%s') as ordertime,admin_description AS order_status,DATE_FORMAT(updated_at,'%Y-%m-%d %H:%i:%s') as updated_at,
            (SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = order_customer_id) AS delivery_to,(SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = order_customer_id) AS cust_mobile,
            order_HasReturn,order_ItemsReturned,order_ReturnVerified
            FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id INNER JOIN retaline_customer rc ON cust_id = order_customer_id" . " WHERE 1 = 1  AND bco.status_id IN(17,18) AND order_HasReturnRequest = 0 {$mob_order} {$filter_qry}  {$order} ";
                        }
                    }

                    //TIME_FORMAT(cast(created_at as time),'%r') as ordertime
                    $db->printGridJson($countQuery, $listQuery);

                break;

                case "order_details":
                    require (THIS_MODULE_PATH . "/order_details.php");
                break;

                case "saveRequestinOrder":
                    $status = $_POST['status_id'];
                    $updated_at = $_POST['updated_at'];
                    $order_no = $_POST['order_no'];
                    $cust_id = $_POST['cust_id'];
                    $item_return_qty = $_POST['item_return_qty'];
                    $order_customer_id = $db->getItemFromDB("SELECT order_customer_id FROM retaline_customer_order WHERE order_id = {$order_no}");
                    $order_branch_id = $db->getItemFromDB("SELECT order_branch_id FROM retaline_customer_order WHERE order_id = {$order_no}");
                    $payment_mode = $db->getItemFromDB("SELECT payment_mode FROM retaline_customer_order WHERE order_id = {$order_no}");
                    //PRINT_R($_POST);exit();
                    $db->query('begin');

                    $itemReturnRequested = $db->getItemFromDb("SELECT order_itemReturnRequestCount FROM retaline_customer_order WHERE order_id = {$order_no}");
                    //$rco['order_HasReturnRequest'] = 1;
                    $rco['updated_at'] = date("Y-m-d H:i:s");
                    $peItemSGriddata = json_decode(stripslashes($_POST['itemGriddata']));
                    $orderItemCount = $db->getItemFromDB("SELECT SUM(item_order_qty) FROM retaline_customer_order_items WHERE customer_order_id = {$order_no}");
                    $order_roundoff = $db->getItemFromDB("SELECT order_roundoff FROM retaline_customer_order WHERE order_id = {$order_no}");
                    $order_delivery_charge = $db->getItemFromDB("SELECT order_delivery_charge FROM retaline_customer_order WHERE order_id = {$order_no}");
                    //        print_r($peItemSGriddata);
                    //        exit();
                    if ($item_return_qty > 0)
                    {
                        $rtrqo['order_id'] = $order_no;
                        $rtrqo['rtrqo_sourceType'] = 1;
                        $rtrqo['rtrqo_type'] = 0;
                        $rtrqo['rtrqo_createdOn'] = date('Y-m-d H:i:s');
                        $rtrqo['rtrqo_createdBy'] = $_SESSION['admin']->UserId;
                        $status = $db->perform("finascop_stock_return_request_order", $rtrqo);
                        $rtrqo_id = $db->insert_id();

                        if (!empty($peItemSGriddata))
                        {
                            foreach ($peItemSGriddata as $peItemS)
                            {
                                $item_product_id = $peItemS->itemId;
                                $itemName = $db->getItemFromDB("SELECT stit_SKU FROM  finascop_stock_itemmaster WHERE stit_ID = {$item_product_id}");
                                $itemCountEntry = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory WHERE branch_id = {$order_branch_id} AND stit_id =  {$item_product_id}");
                                //print_r($peItemS);
                                if ($peItemS->item_return_qty > 0)
                                {
                                    $rtrqod['rtrqo_id'] = $rtrqo_id;
                                    $rtrqod['rtrqo_type'] = 0;
                                    $rtrqod['rtrqod_item_id'] = $peItemS->itemId;
                                    $rtrqod['rtrqod_return_count'] = $peItemS->item_return_qty;
                                    $rtrqod['rtrqod_return_damaged'] = (!empty($peItemS->item_return_damagedqty)) ? $peItemS->item_return_damagedqty : 0;
                                    $rtrqod['rtrqod_return_sellable'] = (!empty($peItemS->item_return_sellableqty)) ? $peItemS->item_return_sellableqty : 0;
                                    $status = $db->perform("finascop_stock_return_request_order_details", $rtrqod);
                                    $rtrqod_id = $db->insert_id();
                                    if ($_SESSION['admin']->IS_RETALINE_LITE != 1)
                                    {
                                        $rtrqb['rtrqo_id'] = $rtrqo_id;
                                        $rtrqb['rtrqod_id'] = $rtrqod_id;
                                        $rtrqb['rtrqb_status'] = 1;
                                        $rtrqo['rtrqb_updatedOn'] = date('Y-m-d H:i:s');
                                        $rtrqo['rtrqb_updatedBy'] = $_SESSION['admin']->UserId;
                                        $status = $db->perform("finascop_stock_return_request_order_barcodes", $rtrqb, 'update', " order_id = {$order_no} AND rtrqb_status = 0 AND rtrqb_itemId = {$peItemS->itemId}");
                                    }

                                    $itemReturnRequested = $itemReturnRequested + $peItemS->item_return_qty;
                                    $status = $db->query("UPDATE retaline_customer_order_items SET item_return_qty_requested = item_return_qty_requested + {$peItemS->item_return_qty},item_return_qty_damaged = item_return_qty_damaged + {$rtrqod['rtrqod_return_damaged']},item_return_qty_sellable = item_return_qty_sellable + {$rtrqod['rtrqod_return_sellable']} WHERE item_product_id = {$peItemS->itemId} AND customer_order_id = {$order_no} ");
                                }

                                /////
                                $stit_id = $db->getFromDB("SELECT mrp,selling_price,fsbg_id FROM finascop_stock_branch_inventory WHERE stit_id = {$item_product_id} AND branch_id = {$order_branch_id}", true);
                                if ($peItemS->item_return_sellableqty > 0)
                                {
                                    if ($itemCountEntry > 0)
                                    {
                                        //fsbg_id
                                        $status = $db->query("UPDATE finascop_stock_branch_inventory SET item_count = item_count + {$peItemS->item_return_sellableqty} WHERE fsbg_id = {$stit_id['fsbg_id']} AND stit_id = {$item_product_id} AND branch_id = {$order_branch_id}");
                                    }
                                    else
                                    {

                                        $fsbi['stit_id'] = $item_product_id;
                                        $fsbi['branch_id'] = $order_branch_id;
                                        $fsbi['item_count'] = $peItemS->item_return_sellableqty;
                                        $fsbi['mrp'] = $stit_id['mrp'];
                                        $fsbi['selling_price'] = $stit_id['selling_price'];
                                        $fsbi['updated_on'] = date('Y-m-d H:i:s');
                                        $fsbi['fsbg_id'] = $stit_id['fsbg_id'];
                                        $status = $db->perform('finascop_stock_branch_inventory', $fsbi);
                                    }
                                }
                                $itemPrices = $db->getFromDB("SELECT item_price,item_discount,item_order_qty,item_cgst,item_sgst,item_kfc FROM retaline_customer_order_items WHERE item_product_id = {$peItemS->itemId} AND customer_order_id = {$order_no}", true);
                                $taxes = floatval($itemPrices['item_cgst']) + floatval($itemPrices['item_sgst']) + floatval($itemPrices['item_kfc']);
                                if ($_SESSION['admin']->IS_RETALINE_LITE != 1)
                                {
                                    if (($item_return_qty == $orderItemCount) && ($peItemS->item_return_qty == 1))
                                    {
                                        $item_sales_price = floatval($itemPrices['item_price']) - floatval($itemPrices['item_discount']) + floatval($order_delivery_charge) + floatval($order_roundoff);
                                        $bcwt['brcw_AddInfo'] = "On sales return of {$itemName} with delivery charge on order {$order_no}";
                                    }
                                    else
                                    {
                                        $bcwt['brcw_AddInfo'] = "On sales return of {$itemName} on order {$order_no}";
                                        $item_sales_price = (floatval($itemPrices['item_price']) - floatval($itemPrices['item_discount'])) / $itemPrices['item_order_qty'];
                                    }

                                    $returnBarcodes = $db->getMultipleData("SELECT * FROM finascop_stock_return_request_order_barcodes WHERE order_id = {$order_no} AND rtrqb_status = 1 AND rtrqb_itemId = {$peItemS->itemId}", true);
                                    foreach ($returnBarcodes as $returnBarcod)
                                    {
                                        $fsiim['stiid_id'] = $db->getItemFromDB("SELECT stiid_id FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = {$returnBarcod['rtrqb_barcode']}");
                                        $fsiim['stiidm_itemmasterid'] = $returnBarcod['rtrqb_itemId'];
                                        $fsiim['stiidm_barcode'] = $returnBarcod['rtrqb_barcode'];
                                        $fsiim['stiidm_details'] = "Item returned from customer in order - {$order_no} ";
                                        $fsiim['created_at'] = date("Y-m-d H:i:s");
                                        $fsiim['updated_at'] = date("Y-m-d H:i:s");
                                        $status = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiim);

                                        $status = $db->query("UPDATE finascop_stock_item_inventorydetails SET stiid_status = 4 WHERE stiid_barcode = '{$returnBarcod['rtrqb_barcode']}'");

                                        $bcwt['stiid_barcode'] = $returnBarcod['rtrqb_barcode'];

                                        $stit_id = $db->getFromDB("SELECT stiid_mrp,stiid_selpri,stiid_itemmasterid,fsbg_id,stiid_itemmastername FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = '{$returnBarcod['rtrqb_barcode']}'", true);

                                        switch ($payment_mode)
                                        {
                                            case 2: //onlinepayment
                                                
                                            case 3: //Wallet
                                                
                                            case 5: //online with Wallet
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
                                            case 4: //COD with Wallet
                                                $wcodAmounts = $db->getFromDB("SELECT order_amount_payable,order_wallet_amount,order_amount_returnon_cash,order_amount_addedon_wallet FROM retaline_customer_order WHERE order_id = {$order_no}", true);
                                                $codAmt = $wcodAmounts['order_amount_payable'] - $wcodAmounts['order_amount_returnon_cash'];
                                                $walletAmt = $wcodAmounts['order_wallet_amount'] - $wcodAmounts['order_amount_addedon_wallet'];
                                                if ($codAmt >= $item_sales_price)
                                                {
                                                    $status = $db->query("UPDATE retaline_customer_order SET order_amount_returnon_cash = order_amount_returnon_cash + {$item_sales_price}  WHERE order_id = {$order_no}");
                                                }
                                                if ($codAmt < $item_sales_price)
                                                {

                                                    if ($codAmt == 0)
                                                    {
                                                        $status = $db->query("UPDATE retaline_customer_order SET order_amount_addedon_wallet = order_amount_addedon_wallet + {$item_sales_price}  WHERE order_id = {$order_no}");
                                                    }
                                                    else
                                                    {
                                                        $item_sales_price = $item_sales_price - $codAmt;
                                                        $status = $db->query("UPDATE retaline_customer_order SET order_amount_addedon_wallet = order_amount_addedon_wallet + {$item_sales_price}  WHERE order_id = {$order_no}");
                                                    }
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
                                                    
                                                }
                                            break;
                                        }
                                    }
                                }
                                else
                                {
                                    if (($item_return_qty == $orderItemCount) && ($peItemS->item_return_qty == 1))
                                    {
                                        $item_sales_price = floatval($itemPrices['item_price']) - floatval($itemPrices['item_discount']) + floatval($order_delivery_charge) + floatval($order_roundoff);
                                        $bcwt['brcw_AddInfo'] = "On sales return of {$itemName} with delivery charge on order {$order_no}";
                                    }
                                    else
                                    {
                                        $bcwt['brcw_AddInfo'] = "On sales return of {$itemName} on order {$order_no}";
                                        $item_sales_price = ((floatval($itemPrices['item_price']) - floatval($itemPrices['item_discount'])) / $itemPrices['item_order_qty']) * $peItemS->item_return_qty;
                                    }
                                    //echo '$item_sales_price' . $item_sales_price;
                                    switch ($payment_mode)
                                    {
                                        case 2: //onlinepayment
                                            
                                        case 3: //Wallet
                                            
                                        case 5: //online with Wallet
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
                                        case 4: //COD with Wallet
                                            $wcodAmounts = $db->getFromDB("SELECT order_amount_payable,order_wallet_amount,order_amount_returnon_cash,order_amount_addedon_wallet FROM retaline_customer_order WHERE order_id = {$order_no}", true);
                                            $codAmt = $wcodAmounts['order_amount_payable'] - $wcodAmounts['order_amount_returnon_cash'];
                                            $walletAmt = $wcodAmounts['order_wallet_amount'] - $wcodAmounts['order_amount_addedon_wallet'];
                                            if ($codAmt >= $item_sales_price)
                                            {
                                                $status = $db->query("UPDATE retaline_customer_order SET order_amount_returnon_cash = order_amount_returnon_cash + {$item_sales_price}  WHERE order_id = {$order_no}");
                                            }
                                            if ($codAmt < $item_sales_price)
                                            {

                                                if ($codAmt == 0)
                                                {
                                                    $status = $db->query("UPDATE retaline_customer_order SET order_amount_addedon_wallet = order_amount_addedon_wallet + {$item_sales_price}  WHERE order_id = {$order_no}");
                                                }
                                                else
                                                {
                                                    $item_sales_price = $item_sales_price - $codAmt;
                                                    $status = $db->query("UPDATE retaline_customer_order SET order_amount_addedon_wallet = order_amount_addedon_wallet + {$item_sales_price}  WHERE order_id = {$order_no}");
                                                }
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
                                                
                                            }
                                        break;
                                    }
                                }
                                //}
                                
                            }
                        }
                    }

                    if ($itemReturnRequested > 0)
                    {
                        $rco['order_itemReturnRequestCount'] = $itemReturnRequested;
                        $status = $db->perform("retaline_customer_order", $rco, 'update', " order_id = {$order_no}");
                        $status = $db->query('commit');
                        if ($status == 1)
                        {
                            echo '{"success":true,"valid":true}';
                        }
                    }
                    else
                    {
                        // var_dup($error);
                        echo '{"success":false,"valid":false}';
                    }

                break;
                case 'listItemsinCustomerOrder':
                    $order_id = $_POST['order_id'];
                    $orderType = $_POST['order_order_id'];
                    if ($orderType == 'B2B')
                    {
                        $fsto_id = $db->getItemFromDB("SELECT fsto_id FROM finascop_stock_transfer_order WHERE fsto_ordertype = 2 AND fstr_id = {$order_id}");
                        $Deltime = $db->getFromDB("SELECT quor_DeliveredTime,quor_DeliveryConfTime FROM qugeo_order WHERE quor_TransferOrder_id = {$fsto_id}", true);
                        $customerOrderItems = $db->getMultipleData("SELECT b2bso_itemid as item_product_id,b2bso_itemqty as item_order_qty,b2bso_netamount as item_sales_price,b2bso_amount_btax as item_retail_price," . "b2bso_netamount as item_amount,item_return_qty_requested,item_return_qty_damaged,item_return_qty_sellable," . "item_return_qty_damagedinTransit FROM retaline_B2B_SalesOrderDetails WHERE bbso_id = {$order_id}", true);
                        foreach ($customerOrderItems as $customerOrderItem)
                        {
                            for ($j = 0;$j < count($customerOrderItems);$j++)
                            {
                                if ($fsto_id > 0)
                                {
                                    $fstod_id = $db->getItemFromDB("SELECT fstod_id FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id} AND fsto_ItemId = {$customerOrderItems[$j]['item_product_id']}", true);
                                    $stiid_barcodes = $db->getMultipleData("SELECT stiid_barcode,fstod_id,stiid_id FROM finascop_stock_transfer_order_details_barcodes WHERE fstod_id = {$fstod_id}", true);
                                }
                                $stit_itemReturnTime = $db->getItemFromDB("SELECT stit_itemReturnTime FROM finascop_stock_itemmaster WHERE stit_ID = {$customerOrderItems[$j]['item_product_id']}");
                                if ($Deltime['quor_DeliveredTime'] != '0000-00-00 00:00:00')
                                {
                                    $DeliveredTime = $Deltime['quor_DeliveredTime'];
                                }
                                else
                                {
                                    $DeliveredTime = $Deltime['quor_DeliveryConfTime'];
                                }
                                $returnDate = date('Y-m-d', strtotime($DeliveredTime . ' + ' . $stit_itemReturnTime . ' days'));
                                $date_now = date("Y-m-d");
                                $datas[$j]['isReturn'] = 1; //Yes
                                $datas[$j]['itemId'] = $customerOrderItems[$j]['item_product_id'];

                                $datas[$j]['item_return_sellableqty'] = $customerOrderItems[$j]['item_return_qty_sellable'];
                                $datas[$j]['item_return_damagedqty'] = $customerOrderItems[$j]['item_return_qty_damaged'];

                                $datas[$j]['itemName'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$customerOrderItems[$j]['item_product_id']}");
                                $datas[$j]['item_returnDate'] = $returnDate;
                                $datas[$j]['item_deliveredDate'] = $DeliveredTime;
                                $datas[$j]['item_return'] = $db->getItemFromDB("SELECT stit_itemReturnTime FROM finascop_stock_itemmaster WHERE stit_ID = {$customerOrderItems[$j]['item_product_id']}");
                                $datas[$j]['item_sales_price'] = $customerOrderItems[$j]['item_sales_price'];
                                $datas[$j]['item_retail_price'] = $customerOrderItems[$j]['item_retail_price'];
                                $datas[$j]['item_order_qty'] = $customerOrderItems[$j]['item_order_qty'];
                                $datas[$j]['item_amount'] = $customerOrderItems[$j]['item_amount'];
                                $datas[$j]['item_return_qty_requested'] = $customerOrderItems[$j]['item_return_qty_requested'];
                                $datas[$j]['stiid_barcode'] = $stiid_barcodes[$j]['stiid_barcode'];
                                $datas[$j]['stiid_id'] = $stiid_barcodes[$j]['stiid_id'];

                                $datas[$j]['order_id'] = $order_id;
                                $datas[$j]['orderType'] = "B2B";
                            }
                        }
                    }
                    elseif ($orderType == 'B2C')
                    {
                        $fsto_id = $db->getItemFromDB("SELECT fsto_id FROM finascop_stock_transfer_order WHERE fsto_ordertype = 1 AND fstr_id = {$order_id}");
                        $Deltime = $db->getFromDB("SELECT quor_DeliveredTime,quor_DeliveryConfTime FROM qugeo_order WHERE quor_TransferOrder_id = {$fsto_id}", true);
                        $customerOrderItems = $db->getMultipleData("SELECT item_product_id,item_order_qty,item_sales_price,item_retail_price,item_amount,item_return_qty_requested,item_return_qty_damaged,item_return_qty_sellable," . "item_return_qty_damagedinTransit FROM retaline_customer_order_items WHERE customer_order_id = {$order_id}", true);
                        foreach ($customerOrderItems as $customerOrderItem)
                        {
                            for ($j = 0;$j < count($customerOrderItems);$j++)
                            {
                                if ($fsto_id > 0)
                                {
                                    $fstod_id = $db->getItemFromDB("SELECT fstod_id FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id} AND fsto_ItemId = {$customerOrderItems[$j]['item_product_id']}", true);
                                    $stiid_barcodes = $db->getMultipleData("SELECT stiid_barcode,fstod_id,stiid_id FROM finascop_stock_transfer_order_details_barcodes WHERE fstod_id = {$fstod_id}", true);
                                }
                                $stit_itemReturnTime = $db->getItemFromDB("SELECT stit_itemReturnTime FROM finascop_stock_itemmaster WHERE stit_ID = {$customerOrderItems[$j]['item_product_id']}");
                                if ($Deltime['quor_DeliveredTime'] != '0000-00-00 00:00:00')
                                {
                                    $DeliveredTime = $Deltime['quor_DeliveredTime'];
                                }
                                else
                                {
                                    $DeliveredTime = $Deltime['quor_DeliveryConfTime'];
                                }
                                $returnDate = date('Y-m-d', strtotime($DeliveredTime . ' + ' . $stit_itemReturnTime . ' days'));
                                $date_now = date("Y-m-d");
                                if ($returnDate >= $date_now)
                                {
                                    $datas[$j]['isReturn'] = 1; //Yes
                                    
                                }
                                else
                                {
                                    $datas[$j]['isReturn'] = 0; //no
                                    
                                }
                                $datas[$j]['itemId'] = $customerOrderItems[$j]['item_product_id'];

                                $datas[$j]['item_return_sellableqty'] = $customerOrderItems[$j]['item_return_qty_sellable'];
                                $datas[$j]['item_return_damagedqty'] = $customerOrderItems[$j]['item_return_qty_damaged'];

                                $datas[$j]['itemName'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$customerOrderItems[$j]['item_product_id']}");
                                $datas[$j]['item_returnDate'] = $returnDate;
                                $datas[$j]['item_deliveredDate'] = $DeliveredTime;
                                $datas[$j]['item_return'] = $db->getItemFromDB("SELECT stit_itemReturnTime FROM finascop_stock_itemmaster WHERE stit_ID = {$customerOrderItems[$j]['item_product_id']}");
                                $datas[$j]['item_sales_price'] = $customerOrderItems[$j]['item_sales_price'];
                                $datas[$j]['item_retail_price'] = $customerOrderItems[$j]['item_retail_price'];
                                $datas[$j]['item_order_qty'] = $customerOrderItems[$j]['item_order_qty'];
                                $datas[$j]['item_amount'] = $customerOrderItems[$j]['item_amount'];
                                $datas[$j]['item_return_qty_requested'] = $customerOrderItems[$j]['item_return_qty_requested'];
                                $datas[$j]['stiid_barcode'] = $stiid_barcodes[$j]['stiid_barcode'];
                                $datas[$j]['stiid_id'] = $stiid_barcodes[$j]['stiid_id'];

                                $datas[$j]['order_id'] = $order_id;
                                $datas[$j]['orderType'] = "B2C";
                            }
                        }
                    }

                    $count = count($datas);
                    if (!empty($datas))
                    {
                        echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
                        //echo json_encode($qry);
                        
                    }
                    else echo '{"totalCount":"0","data":' . json_encode($datas) . '}';

                    break;
                case 'returnOrderDetailsView':
                    $ordertype = $_POST['ordertype'];
                    $cust_id = isset($_POST['cust_id']) ? intval($_POST['cust_id']) : 0;
                    $order_id = $_POST['order_id'];
                    if ($ordertype == 'B2B')
                    {
                        $fsto_id = $db->getItemFromDB("SELECT fsto_id FROM finascop_stock_transfer_order WHERE fsto_ordertype = 2 AND fstr_id = {$order_id}");
                        $Deltime = $db->getFromDB("SELECT quor_DeliveredTime,quor_DeliveryConfTime FROM qugeo_order WHERE quor_TransferOrder_id = {$fsto_id}", true);
                        if ($Deltime['quor_DeliveredTime'] != '0000-00-00 00:00:00')
                        {
                            $DeliveredTime = $Deltime['quor_DeliveredTime'];
                        }
                        else
                        {
                            $DeliveredTime = $Deltime['quor_DeliveryConfTime'];
                        }
                        if ($order_id > 0)
                        {
                            //                "SELECT bbso_id as order_id,bbso_SONumber as order_order_id,bco.b2b_Customer_ID as order_customer_id,bco.br_ID as order_branch_id,br_Name,bco.status_id as status,DATE_FORMAT(bbso_createdon,'%d-%m-%Y') AS order_created_on,
                            //            DATE_FORMAT(bbso_updatedon,'%Y-%m-%d %H:%i:%s') as ordertime,status AS order_status,DATE_FORMAT(bbso_updatedon,'%Y-%m-%d %H:%i:%s') as updated_at,
                            //            bco.b2b_Customer_Name AS delivery_to,b2b_Customer_Mobile AS cust_mobile,
                            //            bbso_HasReturn,bbso_ItemsReturned,bbso_ReturnVerified
                            //            FROM retaline_B2B_SalesOrder bco
                            //            INNER JOIN retaline_B2B_Status bcos ON bcos.status_id = bco.status_id ";
                            $result = $db->getFromDB("SELECT bbso_id as order_id ,bbso_SONumber as order_order_id, bbso_SOValue as order_total_amount,b2b_Customer_ID as order_customer_id,TIME_FORMAT(cast(bbso_createdon as time),'%r') as ordertime,
            status as order_status,DATE_FORMAT(bbso_createdon,'%d-%m-%Y') as orderdate,bbso_SOValue as total,bbso_InvDate,DATE_FORMAT(bbso_InvDate,'%d-%m-%Y') as deliveryDate,TIME_FORMAT(cast(bbso_InvDate as time),'%r') as deliverytime " . " FROM retaline_B2B_SalesOrder bco " . " inner join retaline_B2B_Status bcos ON bcos.status_id = bco.status_id" . " WHERE bbso_id =' " . $order_id . "'", true);
                            $customerDetails = $db->getFromDB("SELECT b2b_Customer_Name as cust_customer_name,b2b_Customer_Email as cust_email,b2b_Customer_Mobile as cust_mobile,0 as cust_walletbalance FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = {$cust_id}", true);
                            $result['cust_customer_name'] = $customerDetails['cust_customer_name'];
                            $result['cust_mobile'] = $customerDetails['cust_mobile'];
                            $result['deliveryDate'] = $DeliveredTime;
                            $result['success'] = true;
                            echo json_encode($result);
                        }
                    }
                    else
                    {
                        $fsto_id = $db->getItemFromDB("SELECT fsto_id FROM finascop_stock_transfer_order WHERE fsto_ordertype = 1 AND fstr_id = {$order_id}");
                        $Deltime = $db->getFromDB("SELECT quor_DeliveredTime,quor_DeliveryConfTime FROM qugeo_order WHERE quor_TransferOrder_id = {$fsto_id}", true);
                        if ($Deltime['quor_DeliveredTime'] != '0000-00-00 00:00:00')
                        {
                            $DeliveredTime = $Deltime['quor_DeliveredTime'];
                        }
                        else
                        {
                            $DeliveredTime = $Deltime['quor_DeliveryConfTime'];
                        }
                        if ($order_id > 0)
                        {
                            $result = $db->getFromDB(" SELECT order_id ,order_order_id, order_total_amount,order_delivery_charge,order_customer_id, payment_mode,TIME_FORMAT(cast(created_at as time),'%r') as ordertime,
            admin_description as order_status,order_total_gst,DATE_FORMAT(created_at,'%d-%m-%Y') as orderdate,total,order_delivered_date,DATE_FORMAT(order_delivered_date,'%d-%m-%Y') as deliveryDate,TIME_FORMAT(cast(order_delivered_date as time),'%r') as deliverytime " . " FROM retaline_customer_order bco " . " inner join retaline_customer_order_status bcos ON bcos.status_id = bco.status_id" . " WHERE order_id =' " . $order_id . "'", true);
                            $customerDetails = $db->getFromDB("SELECT cust_customer_name,cust_email,cust_mobile,cust_walletbalance FROM retaline_customer WHERE cust_id = {$cust_id}", true);
                            $result['cust_customer_name'] = $customerDetails['cust_customer_name'];
                            $result['cust_mobile'] = $customerDetails['cust_mobile'];
                            $result['deliveryDate'] = $DeliveredTime;
                            $result['success'] = true;
                            echo json_encode($result);
                        }
                    }

                    break;
                case 'retbarcodedelete':
                    $order_id = $_POST['order_id'];
                    $barcode = $_POST['barcodesearch_fieldrt'];
                    $db->query('begin');
                    $del_query = "DELETE FROM finascop_stock_return_request_order_barcodes WHERE rtrqb_barcode={$barcode} AND order_id = {$order_id} AND rtrqb_status = 0";
                    $db->query($del_query);

                    $status = $db->query('commit');
                    if ($status == 1)
                    {
                        $msg = "'tmp deleted'";
                        echo '{"success":true,"valid":true,"msg":' . $msg . '}';
                    }
                    else
                    {
                        $msg = "'Error occured while saving.'";
                        echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
                    }
                    break;
                case 'returnbarcodeCheck':
                    $order_id = $_POST['order_id'];
                    $barcode = $_POST['barcodesearch_fieldrt'];
                    $ordertype = $_POST['ordertype'];

                    if ($ordertype == 'B2B')
                    {
                        $fsto_id = $db->getItemFromDB("SELECT fsto_id FROM finascop_stock_transfer_order WHERE fsto_ordertype = 2 AND fstr_id = {$order_id}");
                    }
                    else
                    {
                        $fsto_id = $db->getItemFromDB("SELECT fsto_id FROM finascop_stock_transfer_order WHERE fsto_ordertype = 1 AND fstr_id = {$order_id}");
                    }

                    $Deltime = $db->getFromDB("SELECT quor_DeliveredTime,quor_DeliveryConfTime FROM qugeo_order WHERE quor_TransferOrder_id = {$fsto_id}", true);

                    if ($fsto_id > 0)
                    {
                        $stiid_barcodes = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order_details_barcodes WHERE fsto_id = {$fsto_id} AND stiid_barcode = {$barcode}");
                    }
                    $stiid_itemmasterid = $db->getItemFromDB("SELECT stiid_itemmasterid FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = {$barcode}");

                    $stiid_batchno = $db->getItemFromDB("SELECT stiid_batchno FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = {$barcode}");

                    $stit_itemReturnTime = $db->getItemFromDB("SELECT stit_itemReturnTime FROM finascop_stock_itemmaster WHERE stit_ID = {$stiid_itemmasterid}");
                    if ($Deltime['quor_DeliveredTime'] != '0000-00-00 00:00:00')
                    {
                        $DeliveredTime = $Deltime['quor_DeliveredTime'];
                    }
                    else
                    {
                        $DeliveredTime = $Deltime['quor_DeliveryConfTime'];
                    }
                    $returnDate = date('Y-m-d', strtotime($DeliveredTime . ' + ' . $stit_itemReturnTime . ' days'));
                    $date_now = date("Y-m-d");
                    if ($returnDate >= $date_now)
                    {
                        $db->query('begin');
                        if ($barcode != '')
                        {
                            $unique_brcode = $db->getItemFromDB("SELECT count(rtrqb_barcode) FROM finascop_stock_return_request_order_barcodes WHERE rtrqb_barcode={$barcode}");
                            if ($stiid_barcodes == 1)
                            {
                                $rtrqb['order_id'] = $order_id;
                                $rtrqb['rtrqb_barcode'] = $barcode;
                                $rtrqb['rtrqb_status'] = 0;
                                $rtrqb['rtrqb_itemId'] = $stiid_itemmasterid;
                                $rtrqb['rtrqb_createdOn'] = date('Y-m-d H:i:s');
                                $rtrqb['rtrqb_createdBy'] = $_SESSION['admin']->UserId;
                                if ($unique_brcode == 0)
                                {
                                    $status = $db->perform('finascop_stock_return_request_order_barcodes', $rtrqb);
                                    $lastId = $db->insert_id();
                                }
                                else
                                {
                                    echo '{"success":false,"valid":false,"msg":"Barcode already entered"}';
                                    exit();
                                }
                                $status = $db->query('commit');
                                if ($status == 1)
                                {
                                    $isBanned = $db->getItemFromDB("SELECT fsibb_id FROM finascop_stock_item_banned_batch WHERE stit_id = {$stiid_itemmasterid} AND fsibb_batch = '{$stiid_batchno}'");
                                    $msg = "'Barcode added'";
                                    if ($isBanned > 0)
                                    {
                                        echo '{"success":true,"valid":true,"banned":true,"msg":' . $msg . ',item_id :' . $stiid_itemmasterid . '}';
                                    }
                                    else
                                    {
                                        echo '{"success":true,"valid":true,"banned":false,"msg":' . $msg . ',item_id :' . $stiid_itemmasterid . '}';
                                    }
                                }
                                else
                                {
                                    $msg = "'Error occured while saving.'";
                                    echo '{"success":false,"valid":false,"msg":' . $msg . '}';
                                }
                            }
                            else
                            {
                                $msg = "'Barcode Mismatch.'";
                                echo '{"success":false,"valid":false,"msg":' . $msg . '}';
                                exit();
                            }
                        }
                    }
                    else
                    {
                        $msg = "'Item cant be returned.'";
                        echo '{"success":false,"valid":false,"msg":' . $msg . '}';
                        exit();
                    }

                    break;
                case 'listReturnDamage':
                    $data = $_POST;
                    $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
                    $rec_start = empty($data['start']) ? 0 : $data['start'];
                    $rec_sort = empty($data['sort']) ? 'stit_id' : $data['sort'];
                    $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
                    $search = ' WHERE 1=1 AND item_count > 0 ';
                    $filter = $_POST['filter'];
                    if (isset($filter))
                    {

                        foreach ($filter as $key => $field)
                        {
                            $checkComa = strstr($field['data']['value'], ',');

                            if ($checkComa != '')
                            {
                                $fiterItem = $field['data']['value'];
                                $fiterItem = str_replace(',', "','", $fiterItem);
                                $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                            }
                            else
                            {
                                $searchitem .= " and ({$field[field]} LIKE '%{$field['data']['value']}%') ";
                            }
                        }
                    }
                    $current_branch_id = $_SESSION['admin']->finascop_current_branch_id;
                    $returnOrders = [];
                    //$returnOrders = $db->getItemFromDB(" SELECT GROUP_CONCAT(rtrqo_id) FROM finascop_stock_return_request_order   WHERE 1 = 1 AND rtrqo_sourceBranch = {$current_branch_id} ");
                    //if (!empty($returnOrders)) {
                    $damagedItems = "SELECT GROUP_CONCAT(finascop_stock_return_request_order_details.rtrqo_id) AS rtrqoId,rtrqod_item_id,SUM(rtrqod_return_damaged) AS item_count,finascop_stock_return_request_order_details.rtrqo_type from finascop_stock_return_request_order_details " . "INNER JOIN finascop_stock_return_request_order ON finascop_stock_return_request_order.rtrqo_id = finascop_stock_return_request_order_details.rtrqo_id  " . "WHERE rtrqo_sourceBranch = {$current_branch_id} AND rtrqod_isPackOrderCreated = 0 AND rtrqod_return_damaged > 0 " . "GROUP BY rtrqod_item_id,finascop_stock_return_request_order_details.rtrqo_type ORDER BY rtrqod_item_id DESC ";
                    $totalDatas = $db->getMulipleData($damagedItems, true);
                    $datas = $db->getMulipleData("{$damagedItems} LIMIT $rec_start,$rec_limit", true);
                    //$itemCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_return_request_order_details WHERE rtrqod_return_damaged > 0 AND rtrqo_id IN ($returnOrders) GROUP BY rtrqod_item_id,rtrqo_type ");
                    $resCount = count($totalDatas);
                    // }
                    //print_r($datas);
                    if (!empty($datas))
                    {
                        for ($i = 0;$i < $resCount;$i++)
                        {
                            $datas[$i]['stit_SKU'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['rtrqod_item_id']}");
                            $datas[$i]['returnOrders'] = array_push($returnOrders, $datas[$i]['rtrqo_id']);
                            if ($datas[$i]['rtrqo_type'] == 2)
                            {
                                $datas[$i]['rtrqo_typeName'] = 'From Stock Return';
                            }
                            else
                            {
                                $datas[$i]['rtrqo_typeName'] = 'From Sales Return / Banned';
                            }
                        }
                        echo '{"totalCount":"', $itemCount, '","data":' . json_encode($datas) . '}';
                    }
                    else
                    {
                        echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
                    }

                    break;
                case 'return_order_view':
                    ob_start();
                    include ('retOrderDetail.php');
                    $resHtml = ob_get_clean();
                    echo $resHtml;
                    break;
                case 'createPackingOrderfromReturns':
                    $returnOrders = $_POST['returnOrders'];
                    $itemIds = json_decode($_POST['itemIds']);
                    $itemRecords = json_decode(stripslashes($_POST['itemRecords']));
                    $date = date('Y-m-d H:i:s');
                    $returnTypeName = json_decode($_POST['rqotypeNAme']);
                    $returnTypeName = array_shift($returnTypeName);

                    //print_r($_POST);
                    //exit();
                    $db->query('begin');
                    $tdy = date("Y-m-d") . " 00:00:00";
                    $br_key = $db->getItemFromDB("SELECT br_key FROM finascop_branch WHERE br_ID={$_SESSION['admin']->finascop_current_branch_id}");
                    $maxIdPack = $db->getItemFromDB("select right(frrp_uid,3)*1 as frrp_uid  from `finascop_stock_return_request_packing` where `frrp_source` = {$_SESSION['admin']->finascop_current_branch_id} and `frrp_createdOn` between '{$tdy}' and '{$date}' order by `frrp_id` desc limit 1");
                    $uid_maxpack = 'RET' . date('ymd') . $br_key . str_pad(($maxIdPack + 1) , 3, '0', STR_PAD_LEFT);
                    $frrpdata['frrp_source'] = $_SESSION['admin']->finascop_current_branch_id;
                    $frrpdata['frrp_destination'] = $db->getItemFromDB("SELECT br_cpd FROM finascop_branch WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");
                    $frrpdata['frrp_uid'] = $uid_maxpack;
                    $frrpdata['frrp_createdOn'] = $date;
                    $frrpdata['frrp_createdBy'] = $_SESSION['admin']->UserId;
                    if ($returnTypeName == 'From Sales Return / Banned')
                    {
                        $frrpdata['frrp_isNotSellable'] = 1;
                    }
                    else
                    {
                        $frrpdata['frrp_isNotSellable'] = 0;
                    }
                    $status = $db->perform('finascop_stock_return_request_packing', $frrpdata);
                    $fsrrpId = $db->insert_id();

                    $data['fsto_source'] = $_SESSION['admin']->finascop_current_branch_id;
                    $data['fsto_destination'] = $db->getItemFromDB("SELECT br_cpd FROM finascop_branch WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");
                    $data['fsto_sourcetype'] = 1;
                    $data['fsto_status'] = 6;
                    $data['fsto_destinationtype'] = 1;

                    $maxId = $db->getItemFromDB("select right(fsto_uid,3)*1 as fsto_uid  from `finascop_stock_transfer_order` where `fsto_source` = {$_SESSION['admin']->finascop_current_branch_id} and `fsto_createdOn` between '{$tdy}' and '{$date}' order by `fsto_id` desc limit 1");

                    $uid_max = 'TOR' . date('ymd') . $br_key . str_pad(($maxId + 1) , 3, '0', STR_PAD_LEFT);
                    $data['fsto_uid'] = $uid_max;
                    $data['fsto_type'] = 1;
                    $data['fstr_id'] = $fsrrpId;
                    $data['fsto_ordertype'] = 3;
                    $data['fsto_isPurchaseReturn'] = 1;
                    $data['fsto_createdOn'] = $date;
                    $data['fsto_updateon'] = $date;
                    $data['fsto_initiatedBy'] = $_SESSION['admin']->finascop_current_branch_id;
                    $data['fsto_createdBy'] = $_SESSION['admin']->UserId;
                    $data['fsto_updateby'] = $_SESSION['admin']->UserId;
                    if ($returnTypeName == 'From Sales Return / Banned')
                    {
                        $data['fsto_isNotSellable'] = 1;
                    }
                    else
                    {
                        $data['fsto_isNotSellable'] = 0;
                    }

                    $status = $db->perform('finascop_stock_transfer_order', $data);
                    $lastId = $db->insert_id();
                    if (!empty($itemRecords))
                    {
                        foreach ($itemRecords as $itemRecord)
                        {
                            $data_details['fsto_ItemId'] = $itemRecord->rtrqod_item_id;
                            $data_details['fsto_ItemQty'] = $itemRecord->item_count;

                            $fsrrpd['fsrrpd_ItemId'] = $itemRecord->rtrqod_item_id;
                            $fsrrpd['fsrrpd_ItemQty'] = $itemRecord->item_count;
                            $fsrrpd['frrp_id'] = $fsrrpId;
                            $status = $db->perform('finascop_stock_return_request_packing_details', $fsrrpd);

                            $items = $db->getFromDb("SELECT item_weight,stit_item_volume,stit_GST FROM finascop_stock_itemmaster where stit_itemId = {$itemRecord->rtrqod_item_id}", true);
                            $data_details['fsto_ItemWeight'] = round(($items['item_weight'] * $itemRecord->item_count) , 3); //r 3
                            $data_details['fsto_ItemVolume'] = round(($items['stit_item_volume'] * $itemRecord->item_count) , 3);
                            $data_details['fsto_uid'] = $data['fsto_uid'];
                            $data_details['fsto_id'] = $lastId;
                            $data_details['fstro_createdBy'] = $_SESSION['admin']->UserId;
                            $data_details['fstro_createdOn'] = $date;

                            $itemPriceDetails = $db->getFromDB("SELECT mrp,selling_price FROM finascop_stock_branch_inventory WHERE stit_id = {$itemRecord->rtrqod_item_id} AND branch_id = {$data['fsto_source']}", true);
                            if (count($itemPriceDetails) > 0)
                            {
                                $data_details['fstro_ItemMRP'] = $itemPriceDetails['mrp'];
                                $data_details['fstro_ItemSPincTax'] = $itemPriceDetails['selling_price'];
                                $data_details['fstro_gst_value'] = $items['stit_GST'];
                                $amtbtax = round(($data_details['fstro_ItemSPincTax'] / (100 + $items['stit_GST'])) * 100, 2);
                                $data_details['fstro_cgst_percent'] = round(($items['stit_GST'] / 2) , 2);
                                $data_details['fstro_sgst_percent'] = round(($items['stit_GST'] / 2) , 2);
                                $data_details['fstro_cgst_value'] = $amtbtax * $itemRecord->item_count * ($data_details['fstro_cgst_percent'] / 100); //amtbtax * qty * (cgst/100)
                                $data_details['fstro_sgst_value'] = $amtbtax * $itemRecord->item_count * ($data_details['fstro_sgst_percent'] / 100); //amtbtax * qty * sgst
                                $data_details['fstro_totamtbeforetax'] = $amtbtax * $itemRecord->item_count;
                                $data_details['fstro_totamtaftertax'] = $data_details['fstro_ItemSPincTax'] * $itemRecord->item_count;
                                $data_details['fstro_kfc_percent'] = 0;
                                $data_details['fstro_kfc_value'] = 1;
                            }
                            $data_details = array_filter($data_details, 'strlen');
                            $status = $db->perform('finascop_stock_transfer_order_details', $data_details);
                            //print_r($itemRecord->rtrqo_id);
                            if ($returnTypeName == 'From Sales Return / Banned')
                            {
                                $estatus = $db->query("UPDATE finascop_stock_return_request_order_details SET rtrqod_isPackOrderCreated = 1,frrp_id = {$fsrrpId} WHERE rtrqo_type <> 2  AND rtrqod_item_id = {$itemRecord->rtrqod_item_id} " . "AND rtrqod_return_damaged > 0 AND rtrqod_isPackOrderCreated = 0 AND  rtrqo_id IN ({$itemRecord->rtrqoId}) ");
                            }
                            else
                            {
                                $estatus = $db->query("UPDATE finascop_stock_return_request_order_details SET rtrqod_isPackOrderCreated = 1,frrp_id = {$fsrrpId} WHERE rtrqo_type = 2 AND rtrqod_item_id = {$itemRecord->rtrqod_item_id} " . "AND rtrqod_return_damaged > 0 AND rtrqod_isPackOrderCreated = 0 AND rtrqo_id IN ({$itemRecord->rtrqoId})");
                            }
                        }
                        $estatus = $db->query('commit');
                    }
                    if ($estatus == 1)
                    {

                        echo '{"success":true,"valid":true,"msg":"Packing Order Created ."}';
                        exit;
                    }
                    else
                    {
                        echo '{"success":false,"error":"Error while saving data."}';
                        exit;
                    }

                    break;
                case 'generateUniqueId':
                    $uniqueId = '';
                    while ($uniqueId == '')
                    {
                        $uniqueId = getNewFinascopApiKey();
                    }
                    echo '{"uid":"' . $uniqueId . '"}';
                    break;
                case "saveItemReturninOrder":
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
                    $ordertype = $_POST['ordertype'];
                    $msg = "";
                    //print_r($_POST);exit();
                    if ($ordertype == 'B2B')
                    {
                        $order_customer_id = $db->getItemFromDB("SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_id = {$order_no}");

                        $fstoId = $db->getItemFromDB("SELECT fsto_id FROM finascop_stock_transfer_order WHERE fsto_ordertype = 2 AND fstr_id = {$order_no}");
                        $quor_id = $db->getItemFromDB("SELECT quor_id FROM qugeo_order WHERE quor_TransferOrder_id = {$fstoId}");
                        $quor_Type = $db->getItemFromDB("SELECT quor_Type FROM qugeo_order WHERE quor_id = {$quor_id}");
                        //quor_Type 1- Drive, 2-Hired, 3-CustomerPickup ,4-Courier, 5-DriverPickup, 6-ManualDelivery
                        $order_branch_id = $db->getItemFromDB("SELECT br_ID FROM retaline_B2B_SalesOrder WHERE bbso_id = {$order_no}");
                        //$payment_mode = $db->getItemFromDB("SELECT payment_mode FROM retaline_customer_order WHERE order_id = {$order_no}");
                        $db->query('begin');

                        $itemReturnRequested = $db->getItemFromDb("SELECT order_itemReturnRequestCount FROM retaline_B2B_SalesOrder WHERE bbso_id = {$order_no}");

                        $orderItemCount = $db->getItemFromDB("SELECT SUM(b2bso_itemqty) FROM retaline_B2B_SalesOrderDetails WHERE bbso_id = {$order_no}");
                        $order_roundoff = $db->getItemFromDB("SELECT bbso_roundoff FROM retaline_B2B_SalesOrder WHERE bbso_id = {$order_no}");
                        //$order_delivery_charge = $db->getItemFromDB("SELECT order_delivery_charge FROM retaline_customer_order WHERE order_id = {$order_no}");
                        $itemName = $db->getItemFromDB("SELECT stit_SKU FROM  finascop_stock_itemmaster WHERE stit_ID = {$item_product_id}");
                        $itemCountEntry = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory WHERE branch_id = {$order_branch_id} AND stit_id =  {$item_product_id}");
                        if ($returned_qty > 0)
                        {
                            $count = $db->getItemFromDB("SELECT rtrqo_id FROM finascop_stock_return_request_order  WHERE rtrqo_uuid = '{$uuid}'");
                            if ($count == 0)
                            {
                                $rtrqo['rtrqo_uuid'] = $uuid;
                                $rtrqo['order_id'] = $order_no;
                                $rtrqo['rtrqo_sourceBranch'] = $order_branch_id;
                                $rtrqo['rtrqo_dstinationBranch'] = $db->getItemFromDB("SELECT br_cpd FROM finascop_branch WHERE br_ID = {$order_branch_id}");
                                $rtrqo['rtrqo_sourceType'] = 3;
                                $rtrqo['rtrqo_type'] = 0;
                                $rtrqo['rtrqo_createdOn'] = date('Y-m-d H:i:s');
                                $rtrqo['rtrqo_createdBy'] = $_SESSION['admin']->UserId;
                                $status = $db->perform("finascop_stock_return_request_order", $rtrqo);
                                $rtrqo_id = $db->insert_id();
                            }
                            else
                            {
                                $rtrqo_id = $count;
                                $rtrqo['rtrqo_updatedOn'] = date('Y-m-d H:i:s');
                                $rtrqo['rtrqo_updatedBy'] = $_SESSION['admin']->UserId;
                                $status = $db->perform("finascop_stock_return_request_order", $rtrqo, 'update', " rtrqo_id = {$rtrqo_id}");
                            }
                            $rtrqod['rtrqo_id'] = $rtrqo_id;
                            $rtrqod['rtrqo_type'] = 0;
                            $rtrqod['rtrqod_item_id'] = $item_product_id;
                            $rtrqod['rtrqod_return_count'] = $returned_qty;
                            $rtrqod['rtrqod_return_damaged'] = $itemReturn_type2qty;
                            $rtrqod['rtrqod_return_sellable'] = $itemReturn_type1qty;
                            $rtrqod['rtrqod_return_damagedinTransit'] = $itemReturn_type3qty;
                            $status = $db->perform("finascop_stock_return_request_order_details", $rtrqod);
                            $rtrqod_id = $db->insert_id();

                            if ($_SESSION['admin']->IS_RETALINE_LITE != 1)
                            {
                                if ($itemReturn_type1qty > 0)
                                {
                                    $rtrqb['rtrqb_isSellable'] = 1;
                                }
                                else if ($itemReturn_type2qty > 0)
                                {
                                    $rtrqb['rtrqb_isSellable'] = 0;
                                }
                                else if ($itemReturn_type3qty > 0)
                                {
                                    $rtrqb['rtrqb_isSellable'] = 2;
                                }
                                $rtrqb['rtrqo_id'] = $rtrqo_id;
                                $rtrqb['rtrqod_id'] = $rtrqod_id;
                                $rtrqb['rtrqb_status'] = 1;
                                $rtrqb['rtrqod_type'] = 2;
                                $rtrqb['rtrqb_updatedOn'] = date('Y-m-d H:i:s');
                                $rtrqb['rtrqb_updatedBy'] = $_SESSION['admin']->UserId;
                                $status = $db->perform("finascop_stock_return_request_order_barcodes", $rtrqb, 'update', " order_id = {$order_no} AND rtrqb_status = 0 AND rtrqb_barcode = {$barcodesearch_fieldrt} AND rtrqb_itemId = {$item_product_id}");
                            }
                            $itemReturnRequested = $itemReturnRequested + $returned_qty;
                            $status = $db->query("UPDATE retaline_B2B_SalesOrderDetails SET item_return_qty_requested = item_return_qty_requested + {$returned_qty}," . "item_return_qty_damaged = item_return_qty_damaged + {$itemReturn_type2qty},item_return_qty_sellable = item_return_qty_sellable + {$itemReturn_type1qty}," . "item_return_qty_damagedinTransit = item_return_qty_damagedinTransit + {$itemReturn_type3qty} WHERE b2bso_itemid = {$item_product_id} AND bbso_id = {$order_no} ");

                            $stit_id = $db->getFromDB("SELECT * FROM finascop_stock_branch_inventory WHERE stit_id = {$item_product_id} AND branch_id = {$order_branch_id}", true);
                            $fsbg_id = $db->getItemFromDB("SELECT fsbg_id FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = {$barcodesearch_fieldrt}");

                            if ($itemReturn_type1qty > 0)
                            {
                                if ($itemCountEntry > 0)
                                {
                                    //fsbg_id
                                    $status = $db->query("UPDATE finascop_stock_branch_inventory SET item_count = item_count + {$itemReturn_type1qty} WHERE fsbg_id = {$fsbg_id} AND stit_id = {$item_product_id} AND branch_id = {$order_branch_id}");
                                }
                                else
                                {

                                    $fsbi['stit_id'] = $item_product_id;
                                    $fsbi['branch_id'] = $order_branch_id;
                                    $fsbi['item_count'] = $itemReturn_type1qty;
                                    $fsbi['mrp'] = $stit_id['mrp'];
                                    $fsbi['selling_price'] = $stit_id['selling_price'];
                                    $fsbi['updated_on'] = date('Y-m-d H:i:s');
                                    $fsbi['fsbg_id'] = $fsbg_id;
                                    $fsbi['mrp'] = $stit_id['mrp'];
                                    $fsbi['fpod_itemleastSKUptr'] = $stit_id['fpod_itemleastSKUptr'];
                                    $fsbi['fpod_itemleastSKUpts'] = $stit_id['fpod_itemleastSKUpts'];
                                    $fsbi['fpod_leastSKUb2bCSsp'] = $stit_id['fpod_leastSKUb2bCSsp'];
                                    $fsbi['fpod_leastSKUb2bRetailsp'] = $stit_id['fpod_leastSKUb2bRetailsp'];
                                    $fsbi['fpod_poLandingCostleastSKU'] = $stit_id['fpod_poLandingCostleastSKU'];
                                    $fsbi['fpod_poMMGleastSKU'] = $stit_id['fpod_poMMGleastSKU'];
                                    $status = $db->perform('finascop_stock_branch_inventory', $fsbi);
                                }

                            }
                            if ($itemReturn_type2qty > 0)
                            {

                            }

                            if ($itemReturn_type3qty > 0)
                            {

                            }
                            $rco['order_itemReturnRequestCount'] = $itemReturnRequested;
                            $status = $db->perform("retaline_B2B_SalesOrder", $rco, 'update', " bbso_id = {$order_no}");

                            $itemPrices = $db->getFromDB("SELECT b2bso_itemrate,b2bso_itemqty,b2bso_cgst_value,b2bso_sgst_value,b2bso_discountamt FROM retaline_B2B_SalesOrderDetails WHERE b2bso_itemid = {$item_product_id} AND bbso_id = {$order_no}", true);

                            $returnBarcod = $db->getFromDB("SELECT * FROM finascop_stock_return_request_order_barcodes WHERE rtrqod_type = 2 AND order_id = {$order_no} AND rtrqb_status = 1 AND rtrqb_barcode = {$barcodesearch_fieldrt} AND rtrqb_itemId = {$item_product_id}", true);

                            if (($itemReturnRequested == $orderItemCount))
                            {
                                $item_sales_price = floatval($itemPrices['b2bso_itemrate']) - floatval($itemPrices['b2bso_discountamt']);
                                $bcwt['brcw_AddInfo'] = "On sales return of {$itemName}  on order {$order_no}";
                            }
                            else
                            {
                                $bcwt['brcw_AddInfo'] = "On sales return of {$itemName} on order {$order_no}";
                                $item_sales_price = (floatval($itemPrices['item_price']) - floatval($itemPrices['item_discount'])) / $itemPrices['item_order_qty'];
                            }
                            $item_cgst = $item_sales_price + $itemPrices['b2bso_cgst_value'];
                            $item_cgst = round($item_cgst, 2);
                            $fsiim['stiid_id'] = $db->getItemFromDB("SELECT stiid_id FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = {$returnBarcod['rtrqb_barcode']}");
                            $fsiim['stiidm_itemmasterid'] = $returnBarcod['rtrqb_itemId'];
                            $fsiim['stiidm_barcode'] = $returnBarcod['rtrqb_barcode'];
                            $fsiim['stiidm_details'] = "Item returned from b2b customer in order - {$order_no} ";
                            $fsiim['created_at'] = date("Y-m-d H:i:s");
                            $fsiim['updated_at'] = date("Y-m-d H:i:s");
                            $status = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiim);
                            if ($itemReturn_type1qty > 0)
                            {
                                $status = $db->query("UPDATE finascop_stock_item_inventorydetails SET stiid_status = 4 WHERE stiid_barcode = '{$returnBarcod['rtrqb_barcode']}'");
                            }
                            if (($itemReturn_type2qty > 0) || ($itemReturn_type3qty > 0))
                            {
                                $status = $db->query("UPDATE finascop_stock_item_inventorydetails SET stiid_status = 8 WHERE stiid_barcode = '{$returnBarcod['rtrqb_barcode']}'");
                            }

                            $bcwt['stiid_barcode'] = $returnBarcod['rtrqb_barcode'];

                            $stit_id = $db->getFromDB("SELECT stiid_mrp,stiid_selpri,stiid_itemmasterid,fsbg_id,stiid_itemmastername FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = '{$returnBarcod['rtrqb_barcode']}'", true);

                            if ($itemReturn_type1qty > 0)
                            {

                                $msg .= " {$itemReturn_type1qty} item(s) added to current stock.";
                            }
                            if ($itemReturn_type3qty > 0)
                            {

                                $msg .= " {$itemReturn_type3qty} item(s) added for  Issuing a Debit Note of Rs.{$item_sales_price} to Delivery Boy Driver_Name.";
                            }
                            if ($itemReturn_type2qty > 0)
                            {

                                $msg .= " {$itemReturn_type2qty} item(s) added to Damaged to initiate Purchase Return.";
                            }
                        }
                    }
                    elseif ($ordertype == 'B2C')
                    {
                        $order_customer_id = $db->getItemFromDB("SELECT order_customer_id FROM retaline_customer_order WHERE order_id = {$order_no}");

                        $fstoId = $db->getItemFromDB("SELECT fsto_id FROM finascop_stock_transfer_order WHERE fsto_ordertype = 1 AND fstr_id = {$order_no}");
                        $quor_id = $db->getItemFromDB("SELECT quor_id FROM qugeo_order WHERE quor_TransferOrder_id = {$fstoId}");
                        $quor_Type = $db->getItemFromDB("SELECT quor_Type FROM qugeo_order WHERE quor_id = {$quor_id}");
                        $order_branch_id = $db->getItemFromDB("SELECT order_branch_id FROM retaline_customer_order WHERE order_id = {$order_no}");
                        $payment_mode = $db->getItemFromDB("SELECT payment_mode FROM retaline_customer_order WHERE order_id = {$order_no}");
                        $db->query('begin');

                        $itemReturnRequested = $db->getItemFromDb("SELECT order_itemReturnRequestCount FROM retaline_customer_order WHERE order_id = {$order_no}");

                        $orderItemCount = $db->getItemFromDB("SELECT SUM(item_order_qty) FROM retaline_customer_order_items WHERE customer_order_id = {$order_no}");
                        $order_roundoff = $db->getItemFromDB("SELECT order_roundoff FROM retaline_customer_order WHERE order_id = {$order_no}");
                        $order_delivery_charge = $db->getItemFromDB("SELECT order_delivery_charge FROM retaline_customer_order WHERE order_id = {$order_no}");

                        $itemName = $db->getItemFromDB("SELECT stit_SKU FROM  finascop_stock_itemmaster WHERE stit_ID = {$item_product_id}");
                        $itemCountEntry = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory WHERE branch_id = {$order_branch_id} AND stit_id =  {$item_product_id}");
                        if ($returned_qty > 0)
                        {

                            $count = $db->getItemFromDB("SELECT rtrqo_id FROM finascop_stock_return_request_order  WHERE rtrqo_uuid = '{$uuid}'");
                            if ($count == 0)
                            {
                                $rtrqo['rtrqo_uuid'] = $uuid;
                                $rtrqo['order_id'] = $order_no;
                                $rtrqo['rtrqo_sourceBranch'] = $order_branch_id;
                                $rtrqo['rtrqo_dstinationBranch'] = $db->getItemFromDB("SELECT br_cpd FROM finascop_branch WHERE br_ID = {$order_branch_id}");
                                $rtrqo['rtrqo_sourceType'] = 1;
                                $rtrqo['rtrqo_type'] = 0;
                                $rtrqo['rtrqo_createdOn'] = date('Y-m-d H:i:s');
                                $rtrqo['rtrqo_createdBy'] = $_SESSION['admin']->UserId;
                                $status = $db->perform("finascop_stock_return_request_order", $rtrqo);
                                $rtrqo_id = $db->insert_id();
                            }
                            else
                            {
                                $rtrqo_id = $count;
                                $rtrqo['rtrqo_updatedOn'] = date('Y-m-d H:i:s');
                                $rtrqo['rtrqo_updatedBy'] = $_SESSION['admin']->UserId;
                                $status = $db->perform("finascop_stock_return_request_order", $rtrqo, 'update', " rtrqo_id = {$rtrqo_id}");
                            }

                            $rtrqod['rtrqo_id'] = $rtrqo_id;
                            $rtrqod['rtrqo_type'] = 0;
                            $rtrqod['rtrqod_item_id'] = $item_product_id;
                            $rtrqod['rtrqod_return_count'] = $returned_qty;
                            $rtrqod['rtrqod_return_damaged'] = $itemReturn_type2qty;
                            $rtrqod['rtrqod_return_sellable'] = $itemReturn_type1qty;
                            $rtrqod['rtrqod_return_damagedinTransit'] = $itemReturn_type3qty;
                            $status = $db->perform("finascop_stock_return_request_order_details", $rtrqod);
                            $rtrqod_id = $db->insert_id();
                            if ($_SESSION['admin']->IS_RETALINE_LITE != 1)
                            {
                                if ($itemReturn_type1qty > 0)
                                {
                                    $rtrqb['rtrqb_isSellable'] = 1;
                                }
                                else if ($itemReturn_type2qty > 0)
                                {
                                    $rtrqb['rtrqb_isSellable'] = 0;
                                }
                                else if ($itemReturn_type3qty > 0)
                                {
                                    $rtrqb['rtrqb_isSellable'] = 2;
                                }
                                $rtrqb['rtrqo_id'] = $rtrqo_id;
                                $rtrqb['rtrqod_id'] = $rtrqod_id;
                                $rtrqb['rtrqb_status'] = 1;
                                $rtrqb['rtrqod_type'] = 1;
                                $rtrqb['rtrqb_updatedOn'] = date('Y-m-d H:i:s');
                                $rtrqb['rtrqb_updatedBy'] = $_SESSION['admin']->UserId;
                                $status = $db->perform("finascop_stock_return_request_order_barcodes", $rtrqb, 'update', " order_id = {$order_no} AND rtrqb_status = 0 AND rtrqb_barcode = {$barcodesearch_fieldrt} AND rtrqb_itemId = {$item_product_id}");
                            }

                            $itemReturnRequested = $itemReturnRequested + $returned_qty;
                            $status = $db->query("UPDATE retaline_customer_order_items SET item_return_qty_requested = item_return_qty_requested + {$returned_qty}," . "item_return_qty_damaged = item_return_qty_damaged + {$itemReturn_type2qty},item_return_qty_sellable = item_return_qty_sellable + {$itemReturn_type1qty}," . "item_return_qty_damagedinTransit = item_return_qty_damagedinTransit + {$itemReturn_type3qty} WHERE item_product_id = {$item_product_id} AND customer_order_id = {$order_no} ");

                            $stit_id = $db->getFromDB("SELECT mrp,selling_price,fsbg_id FROM finascop_stock_branch_inventory WHERE stit_id = {$item_product_id} AND branch_id = {$order_branch_id}", true);
                            $fsbg_id = $db->getItemFromDB("SELECT fsbg_id FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = {$barcodesearch_fieldrt}");

                            if ($itemReturn_type1qty > 0)
                            {
                                if ($itemCountEntry > 0)
                                {
                                    //fsbg_id
                                    $status = $db->query("UPDATE finascop_stock_branch_inventory SET item_count = item_count + {$itemReturn_type1qty} WHERE fsbg_id = {$fsbg_id} AND stit_id = {$item_product_id} AND branch_id = {$order_branch_id}");
                                }
                                else
                                {

                                    $fsbi['stit_id'] = $item_product_id;
                                    $fsbi['branch_id'] = $order_branch_id;
                                    $fsbi['item_count'] = $itemReturn_type1qty;
                                    $fsbi['mrp'] = $stit_id['mrp'];
                                    $fsbi['selling_price'] = $stit_id['selling_price'];
                                    $fsbi['updated_on'] = date('Y-m-d H:i:s');
                                    $fsbi['fsbg_id'] = $fsbg_id;
                                    $status = $db->perform('finascop_stock_branch_inventory', $fsbi);
                                }

                            }
                            if ($itemReturn_type2qty > 0)
                            {

                            }

                            if ($itemReturn_type3qty > 0)
                            {

                            }
                            $rco['order_itemReturnRequestCount'] = $itemReturnRequested;
                            $status = $db->perform("retaline_customer_order", $rco, 'update', " order_id = {$order_no}");

                            $itemPrices = $db->getFromDB("SELECT item_price,item_discount,item_order_qty,item_cgst,item_sgst,item_kfc FROM retaline_customer_order_items WHERE item_product_id = {$item_product_id} AND customer_order_id = {$order_no}", true);

                            if ($_SESSION['admin']->IS_RETALINE_LITE != 1)
                            {

                                $returnBarcod = $db->getFromDB("SELECT * FROM finascop_stock_return_request_order_barcodes WHERE order_id = {$order_no} AND rtrqb_status = 1 AND rtrqb_barcode = {$barcodesearch_fieldrt} AND rtrqb_itemId = {$item_product_id}", true);

                                if (($itemReturnRequested == $orderItemCount))
                                {
                                    $item_sales_price = floatval($itemPrices['item_price']) - floatval($itemPrices['item_discount']) + floatval($order_delivery_charge) + floatval($order_roundoff);
                                    $bcwt['brcw_AddInfo'] = "On sales return of {$itemName} with delivery charge on order {$order_no}";
                                }
                                else
                                {
                                    $bcwt['brcw_AddInfo'] = "On sales return of {$itemName} on order {$order_no}";
                                    $item_sales_price = (floatval($itemPrices['item_price']) - floatval($itemPrices['item_discount'])) / $itemPrices['item_order_qty'];
                                }
                                $item_cgst = $item_sales_price * $itemPrices['item_igst'] / (100 + $itemPrices['item_igst']);
                                $item_cgst = round($item_cgst, 2);
                                $fsiim['stiid_id'] = $db->getItemFromDB("SELECT stiid_id FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = {$returnBarcod['rtrqb_barcode']}");
                                $fsiim['stiidm_itemmasterid'] = $returnBarcod['rtrqb_itemId'];
                                $fsiim['stiidm_barcode'] = $returnBarcod['rtrqb_barcode'];
                                $fsiim['stiidm_details'] = "Item returned from customer in order - {$order_no} ";
                                $fsiim['created_at'] = date("Y-m-d H:i:s");
                                $fsiim['updated_at'] = date("Y-m-d H:i:s");
                                $status = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiim);
                                if ($itemReturn_type1qty > 0)
                                {
                                    $status = $db->query("UPDATE finascop_stock_item_inventorydetails SET stiid_status = 4 WHERE stiid_barcode = '{$returnBarcod['rtrqb_barcode']}'");
                                    //BEGIN FINANCIAL TRANSACION
                                    $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 45";
                                    $wqSettings = $db->getFromDB($query, true);
                                    $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);

                                    $transctionTemplate['cr']['retailerSales']['amt'] = round($item_sales_price, 2);
                                    $transctionTemplate['dr']['customerWallet']['amt'] = round($item_sales_price, 2);

                                    $search = array(
                                        "#AMT#",
                                        "#NO#",
                                        "#ID#"
                                    );
                                    $replace = array(
                                        $item_sales_price,
                                        $rtrqo_id,
                                        $uuid
                                    );
                                    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                                    if (strcmp($transctionTemplate1['comments'], '') != 0) {
                                        $fields = array(
                                            "waqu_TransDate" => date('Y-m-d') ,
                                            "waqu_comment" => $transctionTemplate['comments'],
                                            "waqu_SourceID" => intval($rtrqo_id) ,
                                            "waqs_id" => intval($wqSettings['waqs_id']) ,
                                            "waqu_Amount" => doubleval($item_sales_price) ,
                                            "br_id" => intval($_SESSION['admin']->finascop_current_branch_id) ,
                                            "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                                        );
                                        $status = $db->perform('finascop_wallet_queue', $fields);
                                    }

                                    //        print_r($wqSettings);
                                    //        print_r($transctionTemplate);
                                    //        exit();
                                    //END FINANCIAL TRANSACION
                                    
                                }
                                if (($itemReturn_type2qty > 0) || ($itemReturn_type3qty > 0))
                                {
                                    $status = $db->query("UPDATE finascop_stock_item_inventorydetails SET stiid_status = 8 WHERE stiid_barcode = '{$returnBarcod['rtrqb_barcode']}'");
                                }

                                $bcwt['stiid_barcode'] = $returnBarcod['rtrqb_barcode'];

                                $stit_id = $db->getFromDB("SELECT stiid_mrp,stiid_selpri,stiid_itemmasterid,fsbg_id,stiid_itemmastername FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = '{$returnBarcod['rtrqb_barcode']}'", true);

                                switch ($payment_mode)
                                {
                                    case 1: //payondelivery
                                        
                                    case 2: //onlinepayment
                                        
                                    case 3: //Wallet
                                        
                                    case 4: //COD with Wallet
                                        
                                    case 5: //online with Wallet
                                        
                                    case 6: //online on delivery
                                        
                                    case 7: //cash with delivery
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
                            }
                            else
                            {

                                if (($item_return_qty == $orderItemCount))
                                {
                                    $item_sales_price = floatval($itemPrices['item_price']) - floatval($itemPrices['item_discount']) + floatval($order_delivery_charge) + floatval($order_roundoff);
                                    $bcwt['brcw_AddInfo'] = "On sales return of {$itemName} with delivery charge on order {$order_no}";
                                }
                                else
                                {
                                    $bcwt['brcw_AddInfo'] = "On sales return of {$itemName} on order {$order_no}";
                                    $item_sales_price = ((floatval($itemPrices['item_price']) - floatval($itemPrices['item_discount'])) / $itemPrices['item_order_qty']) * $returned_qty;
                                }
                                $item_cgst = $item_sales_price * $itemPrices['item_igst'] / (100 + $itemPrices['item_igst']);
                                $item_cgst = round($item_cgst, 2);
                                //echo '$item_sales_price' . $item_sales_price;
                                switch ($payment_mode)
                                {
                                    case 1: //payondelivery
                                        
                                    case 2: //onlinepayment
                                        
                                    case 3: //Wallet
                                        
                                    case 4: //COD with Wallet
                                        
                                    case 5: //online with Wallet
                                        
                                    case 6: //online on delivery
                                        
                                    case 7: //cash with delivery
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

                            if ($itemReturn_type1qty > 0)
                            {

                                $msg .= " {$itemReturn_type1qty} item(s) added to current stock.";
                            }
                            if ($itemReturn_type3qty > 0)
                            {
                                //BEGIN FINANCIAL TRANSACION
                                $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 47";
                                $wqSettings = $db->getFromDB($query, true);
                                $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                                if ($quor_Type == 1)
                                {
                                    $transctionTemplate['dr']['Driver']['amt'] = $item_sales_price;
                                    //$transctionTemplate['dr']['Driver']['key'] = '';
                                    
                                }
                                if ($quor_Type == 4)
                                {
                                    $qoc_courier = $db->getItemFromDB("SELECT qoc_courier FROM qugeo_order_courier WHERE quor_id = {$quor_id}");
                                    $accled_ReferenceId = $db->getItemFromDB("SELECT accled_ReferenceId FROM mst_courier WHERE mst_courier_id = {$qoc_courier}");
                                    $transctionTemplate['dr']['Courier']['amt'] = $item_sales_price;
                                    $transctionTemplate['dr']['Courier']['key'] = $accled_ReferenceId;
                                }

                                $transctionTemplate['cr']['customerWallet']['amt'] = $item_sales_price;
                                //$comments = "Sellable return stock of Rs. .{$item_sales_price}. on dated: " . date('Y-m-d') . " of branch {$_SESSION['admin']->finascop_current_branch_id}.";
                                //$transctionTemplate['comments'] = $comments;
                                $search = array(
                                    "#AMT#",
                                    "#NO#",
                                    "#ID#"
                                );
                                $replace = array(
                                    $item_sales_price,
                                    $rtrqo_id,
                                    $uuid
                                );
                                $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                                if (strcmp($transctionTemplate1['comments'], '') != 0) {
                                    $fields = array(
                                        "waqu_TransDate" => date('Y-m-d') ,
                                        "waqu_comment" => $transctionTemplate['comments'],
                                        "waqu_SourceID" => intval($rtrqo_id) ,
                                        "waqs_id" => intval($wqSettings['waqs_id']) ,
                                        "waqu_Amount" => doubleval($item_sales_price) ,
                                        "br_id" => intval($_SESSION['admin']->finascop_current_branch_id) ,
                                        "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                                    );
                                    $status = $db->perform('finascop_wallet_queue', $fields);
                                }
                                //        print_r($wqSettings);
                                //        print_r($transctionTemplate);
                                //        exit();
                                //END FINANCIAL TRANSACION
                                $msg .= " {$itemReturn_type3qty} item(s) added for  Issuing a Debit Note of Rs.{$item_sales_price} to Delivery Boy Driver_Name.";
                            }
                            if ($itemReturn_type2qty > 0)
                            {
                                //BEGIN FINANCIAL TRANSACION
                                $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 48";
                                $wqSettings = $db->getFromDB($query, true);
                                $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);

                                //$transctionTemplate['dr']['retailerSales']['amt'] = $item_sales_price;
                                $transctionTemplate['cr']['cgst']['amt'] = $item_cgst;
                                $transctionTemplate['cr']['sgst']['amt'] = $item_cgst;
                                $transctionTemplate['cr']['kfc']['amt'] = $itemPrices['item_kfc'];
                                $transctionTemplate['cr']['rounOff']['amt'] = $itemPrices['item_discount'];
                                $retailerStockDamaged = floatval($item_sales_price) - (floatval($item_cgst) + floatval($item_cgst) + floatval($itemPrices['item_kfc']) + floatval($itemPrices['item_discount']));
                                $transctionTemplate['cr']['retailerStockDamaged']['amt'] = $retailerStockDamaged;
                                $transctionTemplate['dr']['customerWallet']['amt'] = $item_sales_price;
                                // $transctionTemplate['cr']['customerWallet']['key'] = '';
                                //                $comments = "Sellable return stock of Rs. .{$item_sales_price}. on dated: " . date('Y-m-d') . " of branch {$_SESSION['admin']->finascop_current_branch_id}.";
                                //                $transctionTemplate['comments'] = $comments;
                                $search = array(
                                    "#AMT#",
                                    "#NO#",
                                    "#ID#"
                                );
                                $replace = array(
                                    $item_sales_price,
                                    $rtrqo_id,
                                    $uuid
                                );
                                $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                                if (strcmp($transctionTemplate1['comments'], '') != 0) {
                                    $fields = array(
                                        "waqu_TransDate" => date('Y-m-d') ,
                                        "waqu_comment" => $transctionTemplate['comments'],
                                        "waqu_SourceID" => intval($rtrqo_id) ,
                                        "waqs_id" => intval($wqSettings['waqs_id']) ,
                                        "waqu_Amount" => doubleval($item_sales_price) ,
                                        "br_id" => intval($_SESSION['admin']->finascop_current_branch_id) ,
                                        "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                                    );
                                    $status = $db->perform('finascop_wallet_queue', $fields);
                                }
                                //        print_r($wqSettings);
                                //        print_r($transctionTemplate);
                                //        exit();
                                //END FINANCIAL TRANSACION
                                $msg .= " {$itemReturn_type2qty} item(s) added to Damaged to initiate Purchase Return.";
                            }
                            $bmdd_id = $db->getItemFromDB("SELECT bmdd_id FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = {$returnBarcod['rtrqb_barcode']}");
                            $marginDetails = $db->getFromDB("SELECT * FROM retaline_margindistributions WHERE bmd_id = {$bmdd_id}", true);
                            $companyamt = $item_sales_price * $marginDetails['bmd_company'] / 100;
                            $bmdCSAmt = $item_sales_price * $marginDetails['bmd_hub'] / 100;
                            $bmdDistributorAmt = $item_sales_price * $marginDetails['bmd_distributor'] / 100;
                            $bmdRetailerAmt = $item_sales_price * $marginDetails['bmd_retailor'] / 100;
                            $bmdIncentiveAmt = $item_sales_price * $marginDetails['bmd_incentive'] / 100;
                            $bmdCelChgAmt = $item_sales_price * $marginDetails['bmd_driver'] / 100;
                            $retailerBranch = $db->getFromDB("SELECT * FROM finascop_branch WHERE br_ID = {$order_branch_id}", true);
                            $distributorBranch = $db->getFromDB("SELECT * FROM finascop_branch WHERE br_ID = {$retailerBranch['br_cpd']}", true);
                            $centralStoreBranch = $db->getFromDB("SELECT * FROM finascop_branch WHERE br_ID = {$distributorBranch['br_cpd']}", true);
                            $cpdBranch = $db->getFromDB("SELECT * FROM finascop_branch WHERE br_ID = {$centralStoreBranch['br_cpd']}", true);

                            //BEGIN FINANCIAL TRANSACION
                            if ($itemReturn_type1qty > 0)
                            {
                                $proqquery = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 46";
                                $proqSettings = $db->getFromDB($proqquery, true);
                            }
                            if ($itemReturn_type2qty > 0)
                            {
                                $proqquery = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 49";
                                $proqSettings = $db->getFromDB($proqquery, true);
                            }
                            if ($proqSettings['waqs_id'] > 0)
                            {
                                $protansac = json_decode($proqSettings['waqs_Configuration'], true);

                                $HOmarginPayable = round($bmdCelChgAmt, 2) + round($bmdRetailerAmt, 2) + round($bmdIncentiveAmt, 2) + round($bmdDistributorAmt, 2) + round($bmdCSAmt, 2) + round($companyamt, 2);
                                $protansac['dr']['HOmarginPayable']['amt'] = round($HOmarginPayable, 2);
                                $protansac['dr']['HOmarginPayable']['br_ReferenceID'] = $cpdBranch['br_ReferenceID'];
                                $protansac['cr']['retrailerDeliveryCharge']['amt'] = round($bmdCelChgAmt, 2);
                                $protansac['cr']['retrailerDeliveryCharge']['br_ReferenceID'] = $retailerBranch['br_ReferenceID'];
                                $protansac['cr']['retailerMargin']['amt'] = round($bmdRetailerAmt, 2);
                                $protansac['cr']['retailerMargin']['br_ReferenceID'] = $retailerBranch['br_ReferenceID'];
                                $protansac['cr']['revenueIncentive']['amt'] = round($bmdIncentiveAmt, 2);
                                $protansac['cr']['revenueIncentive']['br_ReferenceID'] = $retailerBranch['br_ReferenceID'];
                                $protansac['cr']['distributorMargin']['amt'] = round($bmdDistributorAmt, 2);
                                $protansac['cr']['distributorMargin']['br_ReferenceID'] = $distributorBranch['br_ReferenceID'];
                                $protansac['cr']['csMargin']['amt'] = round($bmdCSAmt, 2);
                                $protansac['cr']['csMargin']['br_ReferenceID'] = $centralStoreBranch['br_ReferenceID'];
                                $protansac['cr']['companyMargin']['amt'] = round($companyamt, 2);
                                $protansac['cr']['companyMargin']['br_ReferenceID'] = $cpdBranch['br_ReferenceID'];

                                $search = array(
                                    "#AMT#",
                                    "#NO#",
                                    "#ID#"
                                );
                                $replace = array(
                                    $HOmarginPayable,
                                    $rtrqo_id,
                                    $uuid
                                );
                                $protansac['comments'] = str_replace($search, $replace, $protansac['comments']);
                                if (strcmp($protansac['comments'], '') != 0) {
                                    $proqfields = array(
                                        "waqu_TransDate" => date('Y-m-d') ,
                                        "waqu_comment" => $protansac['comments'],
                                        "waqu_SourceID" => intval($rtrqo_id) ,
                                        "waqs_id" => intval($proqSettings['waqs_id']) ,
                                        "waqu_Amount" => doubleval($HOmarginPayable) ,
                                        "br_id" => intval($_SESSION['admin']->finascop_current_branch_id) ,
                                        "waqu_Data" => stripslashes(json_encode($protansac))
                                    );
                                    $status = $db->perform('finascop_wallet_queue', $proqfields);
                                }
                            }
                            
                            //END FINANCIAL TRANSACION
                        }
                    }

                    $status = $db->query('commit');
                    if ($status == 1)
                    {
                        echo '{"success":true,"valid":true,"msg": "' . $msg . '"}';
                    }
                    else
                    {
                        // var_dup($error);
                        echo '{"success":false,"valid":false,"msg": "Error Occured."}';
                    }

                    break;
                case 'getBranchName':
                    $branch_id = $_SESSION['admin']->finascop_current_branch_id;
                    $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch WHERE br_status = 'Active' AND (br_cpd={$branch_id} OR br_ID={$branch_id})", true);
                    if (!empty($qry))
                    {
                        echo json_encode($qry);
                    }
                    else echo [];
                    break;
                case 'listReturnedToDamage':
                    $data = $_POST;
                    $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
                    $rec_start = empty($data['start']) ? 0 : $data['start'];
                    $rec_sort = empty($data['sort']) ? 'stit_id' : $data['sort'];
                    $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
                    $search = ' WHERE 1=1';
                    $filter = $_POST['filter'];
                    if (isset($filter))
                    {

                        foreach ($filter as $key => $field)
                        {
                            $checkComa = strstr($field['data']['value'], ',');

                            if ($checkComa != '')
                            {
                                $fiterItem = $field['data']['value'];
                                $fiterItem = str_replace(',', "','", $fiterItem);
                                $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                            }
                            else
                            {
                                $searchitem .= " and ({$field[field]} LIKE '%{$field['data']['value']}%') ";
                            }
                        }
                    }

                    if ($_SESSION['admin']->br_PyramidLevel == 1)
                    {
                        $centStores = $db->getItemFromDB("SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_cpd = {$_SESSION['admin']->finascop_current_branch_id}");
                        $where = " AND branch_id IN ({$centStores})";
                    }
                    else
                    {
                        $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;
                        $branchName = $_POST['branchName'];
                        $where = " AND branch_id =" . $br_ID;
                    }

                    $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . " finascop_return_branch_inventory fsb " . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID " . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$where}{$searchitem}";
                    $count = $db->getItemFromDB($countQuery);
                    $listQuery = "SELECT fsb.stit_id as stit_id,br.br_Name as br_Name,fsi.stit_SKU as stit_SKU,fsbg_id,frbi_epr,frbi_leastSKUmrp,frbi_leastSKUepr," . "fsb.item_count as item_count,fsb.mrp as mrp,fsb.selling_price as selling_price,fsb.branch_id as branchId,stit_category_name,stit_quantity,stit_brand_name,stit_product_variant,frbi_status,frbi_id," . "CASE WHEN frbi_status= 1 THEN 'Damaged' WHEN frbi_status= 2 THEN 'Expirable' END AS frbi_statusName," . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stitl1_optimumqty " . "WHEN br.br_stockLevel= 2 THEN fsi.stitl2_optimumqty " . "ELSE stitl3_optimumqty END AS optimumqty," . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stit11_minimumqty " . "WHEN br.br_stockLevel= 2 THEN fsi.stit12_minimumqty " . "ELSE stit13_minimumqty END AS minimumqty," . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stit11_maximumqty " . "WHEN br.br_stockLevel= 2 THEN fsi.stit13_maximumqty " . "ELSE stit13_maximumqty END AS maximumqty " . "FROM finascop_return_branch_inventory fsb " . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID " . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$where}{$searchitem} AND fsb.item_count > 0 ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
                    $datas = $db->getMulipleData($listQuery, true);
                    $resCount = count($datas);
                    for ($i = 0;$i < $resCount;$i++)
                    {
                        //echo '$i'.$stii_epraft;
                        $datas[$i]['actualValue'] = 0;
                        $availableBarcodes = $db->getMulipleData("SELECT stiid_barcode,stiid_fpoid,stiid_itemmasterid,stiid_barcode_created_level,stiid_leastSKUepr,stii_epraft FROM finascop_stock_item_inventorydetails " . "WHERE stiid_itemmasterid = {$datas[$i]['stit_id']} AND stiid_status IN( 15,10)", true);
                        $packageDetails = $db->getFromDB("SELECT csb_package_type_name,csb_package_type_id,cs_nos,cs_package_type_name,cs_package_type_id,ds_package_type_id,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,cos_package_type_id " . "FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['stit_id']}", true);
                        $countDiff = $datas[$i]['item_count'] - count($availableBarcodes);
                        $totalItem = 0;
                        $csCount = 0;
                        $dsCount = 0;
                        if (count($availableBarcodes) > 0)
                        {
                            foreach ($availableBarcodes as $availableBarcod)
                            {
                                if ($availableBarcod['stiid_barcode_created_level'] == 2)
                                {
                                    $totalItem = $totalItem + $packageDetails['cs_nos'];
                                    $stii_epraft = $availableBarcod['stiid_leastSKUepr'] * $packageDetails['cs_nos'];
                                    $csCount = $csCount + 1;
                                }
                                else if ($availableBarcod['stiid_barcode_created_level'] == 3)
                                {
                                    $totalItem = $totalItem + 1;
                                    $stii_epraft = $availableBarcod['stiid_leastSKUepr'];
                                    $dsCount = $dsCount + 1;
                                }
                                //echo '$i'.$datas[$i]['actualValue'];
                                $datas[$i]['actualValue'] = $datas[$i]['actualValue'] + $stii_epraft;
                            }
                        }

                        //echo $countDiff;
                        $datas[$i]['totalSKUs'] = $totalItem;
                        if ($countDiff > 0)
                        {
                            $stii_epraft = $datas[$i]['frbi_leastSKUepr'] * $packageDetails['cs_nos'] * $countDiff;
                            $datas[$i]['actualValue'] = $datas[$i]['actualValue'] + $stii_epraft;
                            //$csCount = $csCount + ($packageDetails['cs_nos'] * $countDiff);
                            $totalItem = $totalItem + ($packageDetails['cs_nos'] * $countDiff);
                        }
                        $datas[$i]['qtyDistribution'] = "{$totalItem} {$packageDetails['ds_package_type_name']}";
                    }
                    if (!empty($datas))
                    {
                        echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
                    }
                    else echo '{"totalCount":"0","data":' . json_encode($datas) . '}';

                    break;
                case 'getItemName':
                    if ($_POST['query'] != '')
                    {
                        $que = addslashes($_POST['query']);
                        $searchQuery = " AND stit_SKU LIKE '%{$que}%'";
                    }
                    else
                    {
                        $searchQuery = '';
                    }
                    $searchQuery .= " AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}";

                    //finascop_getjsonkeyarray("SELECT stit_ID,stit_itemName,stit_SKU FROM finascop_stock_itemmaster where 1=1 {$searchQuery} ");
                    $qry = $db->getMulipleData("SELECT fsi.stit_ID as stit_ID,stit_itemName,stit_SKU,item_count FROM finascop_stock_itemmaster fsi INNER JOIN finascop_stock_branch_inventory fsb ON fsb.stit_id=fsi.stit_ID  where 1=1 and item_count > 0 {$searchQuery}", true);
                    if (!empty($qry))
                    {
                        echo json_encode($qry);
                    }
                    else echo [];
                    break;
                case 'listStockReturnItems':
                    $uuid = $_POST['uuid'];
                    $rec_sort = empty($data['sort']) ? 'rtrqod_id' : $data['sort'];
                    $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
                    $filter_part = ' 1=1';

                    if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                    if (!empty($uuid)) $cond = " AND rtrqo_uid = '{$uuid}'";

                    $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . "finascop_stock_return_request_order_details where {$filter_part} {$cond}";
                    $listQuery = "SELECT rtrqod_id,rtrqod_return_damaged,(SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = rtrqod_item_id) as rtrqod_ItemName " . "from " . FINASCOP_DB . "finascop_stock_return_request_order_details WHERE {$filter_part} {$cond} ORDER BY $rec_sort $rec_sort_dir ";
                    $db->printGridJson($countQuery, $listQuery);
                    break;
                case 'addItemstoStockReturn':
                    $uuid = $_POST['uuid'];
                    $rtrqod['rtrqod_item_id'] = $_POST['rtrqod_itemId'];
                    $rtrqod['rtrqod_return_damaged'] = $_POST['rtrqod_return_damaged'];
                    $rtrqod['rtrqod_return_count'] = $_POST['rtrqod_return_damaged'];

                    $itemQty = $db->getItemSafe("SELECT item_count FROM finascop_stock_branch_inventory WHERE stit_id = ? AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}", "i", [$_POST['rtrqod_itemId']]);
                    if ($_POST['rtrqod_return_damaged'] > $itemQty)
                    {
                        echo '{"success":false,"valid":false,"msg":" ' . $itemQty . ' items only available in the stock, please check and proceed"}';
                        exit();
                    }
                    $count = $db->getItemSafe("SELECT COUNT(*) FROM finascop_stock_return_request_order_details WHERE rtrqo_uid = '{$uuid}' AND rtrqod_item_id = ?", "i", [$_POST['rtrqod_itemId']]);
                    $db->query('begin');
                    if ($count > 0)
                    {
                        $status = $db->perform('finascop_stock_return_request_order_details', $rtrqod, 'update', " rtrqo_uid = '{$uuid}' AND rtrqod_item_id = {$_POST['rtrqod_itemId']}");
                    }
                    else
                    {
                        $rtrqod['rtrqo_uid'] = $uuid;
                        $status = $db->perform('finascop_stock_return_request_order_details', $rtrqod);
                    }
                    $status = $db->query('commit');
                    if ($status == 1)
                    {
                        $msg = "'Items added to stock return.'";
                        echo '{"success":true,"valid":true,"msg":' . $msg . '}';
                    }
                    else
                    {
                        $msg = "'Error occured while saving.'";
                        echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
                    }
                    break;
                case 'saveStockReturn':
                    $uuid = $_POST['uuid'];
                    $rtrqo['rtrqo_sourceBranch'] = $_SESSION['admin']->finascop_current_branch_id;
                    $rtrqo['rtrqo_dstinationBranch'] = $db->getItemFromDB("SELECT br_cpd FROM  finascop_branch WHERE br_Id = {$_SESSION['admin']->finascop_current_branch_id}");
                    $rtrqo['rtrqo_createdOn'] = date("Y-m-d H:i");
                    $rtrqo['rtrqo_createdBy'] = $_SESSION['admin']->UserId;
                    $rtrqo['rtrqo_isDirect'] = 1;
                    $rtrqo['rtrqo_uuid'] = $uuid;
                    $rtrqo['rtrqo_sourceType'] = 1;
                    $rtrqo['rtrqo_type'] = 2;
                    $db->query('begin');
                    $status = $db->perform("finascop_stock_return_request_order", $rtrqo);
                    $rtrqo_id = $db->insert_id();
                    $rtrqodet['rtrqo_id'] = $rtrqo_id;
                    $rtrqodet['rtrqo_type'] = 2;
                    $status = $db->perform('finascop_stock_return_request_order_details', $rtrqodet, 'update', " rtrqo_uid = '{$uuid}'");
                    $fsrroDetails = $db->getMultipleData("SELECT rtrqod_id,rtrqo_id,rtrqod_item_id,rtrqod_return_damaged FROM finascop_stock_return_request_order_details WHERE rtrqo_id = {$rtrqo_id}", true);
                    $amount = 0;
                    $nofqty = 0;
                    foreach ($fsrroDetails as $fsrroDet)
                    {
                        //$status = $db->query("UPDATE finascop_stock_branch_inventory SET item_count = item_count - {$fsrroDet['rtrqod_return_damaged']} WHERE stit_id = {$fsrroDet['rtrqod_item_id']} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}");
                        $sellingPrice = $db->getItemFromDB("SELECT selling_price FROM finascop_stock_branch_inventory WHERE stit_id = {$fsrroDet['rtrqod_item_id']} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}");
                        $nofqty = $nofqty + $fsrroDet['rtrqod_return_damaged'];
                        $netSp = $fsrroDet['rtrqod_return_damaged'] * $sellingPrice;
                        $amount = $amount + $netSp;
                    }

                    //BEGIN FINANCIAL TRANSACION
                    if ($_SESSION['admin']->br_PyramidLevel == 4)
                    {
                        $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 50";
                        $wqSettings = $db->getFromDB($query, true);
                        $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);

                        $transctionTemplate['cr']['retailerStock']['amt'] = round($amount, 2);

                        $transctionTemplate['dr']['retailerStockDamaged']['amt'] = round($amount, 2);
                        // $comments = "Stock returns {$nofqty} quantity of items on dated: " . date('Y-m-d') . " to branch {$_SESSION['admin']->finascop_current_branch_id}.";
                        //$transctionTemplate['comments'] = $comments;
                        
                    }
                    else if ($_SESSION['admin']->br_PyramidLevel == 3)
                    {
                        $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 53";
                        $wqSettings = $db->getFromDB($query, true);
                        $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);

                        $transctionTemplate['dr']['ditributStockDamaged']['amt'] = round($amount, 2);
                        $transctionTemplate['cr']['distributorStock']['amt'] = round($amount, 2);
                        // $comments = "Stock returns {$nofqty} quantity of items on dated: " . date('Y-m-d') . " to branch {$_SESSION['admin']->finascop_current_branch_id}.";
                        //$transctionTemplate['comments'] = $comments;
                        
                    }
                    $transactionID = '';
                    $search = array(
                        "#AMT#",
                        "#NO#",
                        "#ID#"
                    );
                    //$replace = array($amount, $rtrqo_id, $uuid);
                    $replace = array(
                        $amount,
                        $rtrqo_id,
                        $rtrqo_id
                    );
                    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                    if (strcmp($transctionTemplate['comments'], '') != 0) {
                        $fields = array(
                            "waqu_TransDate" => date('Y-m-d') ,
                            "waqu_comment" => $transctionTemplate['comments'],
                            "waqu_SourceID" => intval($rtrqo_id) ,
                            "waqs_id" => intval($wqSettings['waqs_id']) ,
                            "waqu_Amount" => doubleval($amount) ,
                            "br_id" => intval($_SESSION['admin']->finascop_current_branch_id) ,
                            "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                        );
                        $status = $db->perform('finascop_wallet_queue', $fields);
                    }
                    //END FINANCIAL TRANSACION
                    $status = $db->query('commit');
                    if ($status == 1)
                    {
                        $msg = "'Items added to stock return.'";
                        echo '{"success":true,"valid":true,"msg":' . $msg . '}';
                    }
                    else
                    {
                        $msg = "'Error occured while saving.'";
                        echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
                    }
                    break;
                case 'addItemstoPurchaseReturn':

                    $rtrqod['fpr_itemId'] = $_POST['rtrqod_itemId'];
                    $rtrqod['fpr_itemQty'] = $_POST['rtrqod_return_damaged'];
                    $rtrqod['fpr_branchId'] = $_SESSION['admin']->finascop_current_branch_id;
                    $stiid_ids = json_decode(stripslashes($_POST['stiid_id']) , true);
                    $datetime = date('Y-m-d H:i:s');

                    $itemQty = $db->getItemSafe("SELECT item_count FROM finascop_stock_branch_inventory WHERE stit_id = ? AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}", "i", [$_POST['rtrqod_itemId']]);
                    if ($_POST['rtrqod_return_damaged'] > $itemQty)
                    {
                        echo '{"success":false,"valid":false,"msg":"Item available is only ' . $itemQty . '"}';
                        exit();
                    }
                    $itemDetails = $db->getFromSafe("select * from finascop_stock_branch_inventory WHERE stit_id = ? AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}", "i", [$_POST['rtrqod_itemId']], true);
                    $fsbg_leastSKUepr = $db->getFromDB("SELECT fsbg_leastSKUepr FROM finascop_stock_item_batch_group WHERE stit_ID = {$_POST['rtrqod_itemId']} AND fsbg_id = {$itemDetails['fsbg_id']}");
                    $count = $db->getItemSafe("SELECT COUNT(*) FROM finascop_purchase_returnable_direct WHERE fpr_itemId = ?", "i", [$_POST['rtrqod_itemId']]);
                    $db->query('begin');
                    for ($i = 0;$i < count($stiid_ids);$i++)
                    {
                        $tlstiiId['stiid_status'] = 10;
                        $tlstiiId['stiid_updatedon'] = $datetime;
                        $tlstiiId['stiid_updatedby'] = $_SESSION['admin']->Finascop_UserId;
                        $fsiidupstatus = $db->perform('finascop_stock_item_inventorydetails', $tlstiiId, 'update', " stiid_id = {$stiid_ids[$i]}");

                        $inventoryDetail = $db->getFromDB("SELECT * FROM finascop_stock_item_inventorydetails WHERE stiid_id = {$stiid_ids[$i]}", true);
                        $fsiidmData['stiid_id'] = $stiid_ids[$i];
                        $fsiidmData['stiidm_itemmasterid'] = $inventoryDetail['stiid_itemmasterid'];
                        $fsiidmData['stiidm_barcode'] = $inventoryDetail['stiid_barcode'];
                        $fsiidmData['created_at'] = date('Y-m-d H:i:s');
                        $fsiidmData['stiidm_details'] = 'Barcode status changed to 15 ';
                        $status = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiidmData);
                    }
                    if ($count > 0)
                    {
                        $status = $db->executeSafe("UPDATE finascop_purchase_returnable_direct SET fpr_itemQty = fpr_itemQty + {$rtrqod['fpr_itemQty']} WHERE fpr_itemId = ? AND fpr_branchId = {$_SESSION['admin']->finascop_current_branch_id}", "i", [$_POST['rtrqod_itemId']]);
                        $fprd['fpr_updatedOn'] = date('Y-m-d H:i:s');
                        $fprd['fpr_updatedBy'] = $_SESSION['admin']->UserId;
                        $status = $db->perform('finascop_purchase_returnable_direct', $fprd, 'update', " fpr_itemId = {$_POST['rtrqod_itemId']} AND fpr_branchId = {$_SESSION['admin']->finascop_current_branch_id}");
                    }
                    else
                    {
                        $rtrqod['fpr_createdOn'] = date('Y-m-d H:i:s');
                        $rtrqod['fpr_createdBy'] = $_SESSION['admin']->UserId;
                        $status = $db->perform('finascop_purchase_returnable_direct', $rtrqod);
                    }
                    $itemCount = $db->getItemSafe("SELECT count(*) FROM finascop_return_branch_inventory WHERE stit_id = ? AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}", "i", [$_POST['rtrqod_itemId']]);
                    if ($itemCount > 0)
                    {
                        $status = $db->executeSafe("UPDATE finascop_return_branch_inventory SET updated_on = '{$datetime}',item_count = item_count + {$rtrqod['fpr_itemQty']} WHERE stit_id = ? AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}", "i", [$_POST['rtrqod_itemId']]);
                    }
                    else
                    {
                        $fsbi['stit_id'] = $_POST['rtrqod_itemId'];
                        $fsbi['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
                        $fsbi['item_count'] = $rtrqod['fpr_itemQty'];
                        $fsbi['mrp'] = $itemDetails['mrp'];
                        $fsbi['frbi_leastSKUmrp'] = $itemDetails['fpod_leastSKUmrp'];
                        $fsbi['frbi_leastSKUepr'] = $fsbg_leastSKUepr;
                        $fsbi['selling_price'] = $itemDetails['selling_price'];
                        $fsbi['updated_on'] = date('Y-m-d H:i:s');
                        $fsbi['created_at'] = date('Y-m-d H:i:s');
                        $status = $db->perform('finascop_return_branch_inventory', $fsbi);
                    }
                    $status = $db->executeSafe("UPDATE finascop_stock_branch_inventory SET updated_on = '{$datetime}',item_count = item_count - {$rtrqod['fpr_itemQty']} WHERE stit_id = ? AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}", "i", [$_POST['rtrqod_itemId']]);

                    //BEGIN FINANCIAL TRANSACION
                    $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 56";
                    $transctionTemplate['dr']['csStockDamaged']['amt'] = $itemDetails['selling_price'];
                    $transctionTemplate['cr']['csStock']['amt'] = $itemDetails['selling_price'];
                    $wqSettings = $db->getFromDB($query, true);
                    $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);

                    $search = array(
                        "#AMT#",
                        "#NO#",
                        "#ID#"
                    );
                    $replace = array(
                        $itemDetails['selling_price'],
                        $_POST['rtrqod_itemId'],
                        $uuid
                    );
                    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                    if (strcmp($transctionTemplate['comments'], '') != 0){
                        $fields = array(
                            "waqu_TransDate" => date('Y-m-d') ,
                            "waqu_comment" => $transctionTemplate['comments'],
                            "waqu_SourceID" => intval($_POST['rtrqod_itemId']) ,
                            "waqs_id" => intval($wqSettings['waqs_id']) ,
                            "waqu_Amount" => doubleval($itemDetails['selling_price']) ,
                            "br_id" => intval($_SESSION['admin']->finascop_current_branch_id) ,
                            "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                        );
                        $status = $db->perform('finascop_wallet_queue', $fields);
                    }
                    //END FINANCIAL TRANSACION
                    $status = $db->query('commit');
                    if ($status == 1)
                    {
                        $msg = "'Items added to stock return.'";
                        echo '{"success":true,"valid":true,"msg":' . $msg . '}';
                    }
                    else
                    {
                        $msg = "'Error occured while saving.'";
                        echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
                    }
                    break;
                case 'getVendorName':
                    $itemarr = json_decode(stripslashes($_POST['itemarr']) , true);
                    $itemIds = implode(',', $itemarr);
                    $PoIds = $db->getItemFromDB("SELECT GROUP_CONCAT(DISTINCT(stiid_fpoid)) FROM finascop_stock_item_inventorydetails WHERE stiid_status = 10 AND stiid_itemmasterid IN ({$itemIds})");
                    if ($_POST['query'] != '')
                    {
                        $que = addslashes($_POST['query']);
                        $searchQuery = " AND stpa_Fname LIKE '%{$que}%'";
                    }
                    else
                    {
                        $searchQuery = '';
                    }
                    if (!empty($PoIds))
                    {
                        $vendorIds = $db->getItemFromDB("SELECT GROUP_CONCAT(fpo_vendorId) FROM finascop_purchase_order WHERE fpo_id IN ({$PoIds})");
                        $searchQuery = " AND stpa_id IN ({$vendorIds})";
                    }

                    $qry = $db->getMulipleData("SELECT stpa_id,stpa_Fname FROM finascop_stock_party  where 1=1  {$searchQuery}", true);
                    $even[0]['stpa_id'] = 0;
                    $even[0]['stpa_Fname'] = "NA";
                    if ($_SESSION['admin']->IS_RETALINE_LITE != 1)
                    {
                        if (!empty($qry))
                        {
                            echo json_encode($qry);
                        }
                        else
                        {
                            echo json_encode($even);
                        }
                    }
                    else
                    {
                        echo json_encode($even);
                    }

                    break;
                case 'saveStockLoss':
                    $prtoLossValue = $_POST['prtoLossValue'];
                    $iteminLossStocks = json_decode(stripslashes($_POST['stockLossItems']));
                    $fsl['fsl_netAmount'] = $prtoLossValue;
                    $fsl['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
                    $fsl['fsl_createdOn'] = date("Y-m-d H:i:s");
                    $fsl['fsl_createdBy'] = $_SESSION['admin']->UserId;
                    $db->query('begin');
                    $status = $db->perform('finascop_stock_loss', $fsl);
                    $fsl_id = $db->insert_id();
                    $totalItems = 0;
                    $itemCount = 0;
                    $totalEPR = 0;
                    $datTime = date('Y-m-d H:i:s');
                    foreach ($iteminLossStocks as $iteminReturnedStock)
                    {
                        $itemCount++;
                        $fslitems['fsli_itemID'] = $iteminReturnedStock->stit_id;
                        $fslitems['fsli_itemQty'] = $iteminReturnedStock->item_count;
                        $fslitems['fsl_id'] = $fsl_id;
                        $fslitems['frbi_epr'] = $iteminReturnedStock->frbi_epr;
                        $status = $db->perform('finascop_stock_loss_items', $fslitems);
                        $totalItemsQty += $iteminReturnedStock->item_count;
                        $totalEPR = $totalEPR + $iteminReturnedStock->frbi_epr;
                        $status = $db->query("UPDATE finascop_return_branch_inventory SET item_lossCount = item_lossCount + {$iteminReturnedStock->item_count},item_count = item_count - {$iteminReturnedStock->item_count},updated_on = '{$datTime}' WHERE frbi_id = {$iteminReturnedStock->frbi_id}");

                        $status = $db->query("UPDATE finascop_stock_item_inventorydetails SET stiid_status = if(stiid_status=10,20,21) WHERE stiid_itemmasterid = {$iteminReturnedStock->stit_id} AND stiid_status IN (15,10)");
                    }
                    $fslUpdate['fsl_totalItems'] = $itemCount;
                    $fslUpdate['fsl_totalItemsQty'] = $totalItemsQty;
                    $status = $db->perform('finascop_stock_loss', $fslUpdate, 'update', " fsl_id = {$fsl_id}");

                    //BEGIN FINANCIAL TRANSACION
                    $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 58";
                    $wqSettings = $db->getFromDB($query, true);
                    $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                    $transctionTemplate['cr']['csStockDamaged']['amt'] = $totalEPR;
                    $transctionTemplate['dr']['lostStock']['amt'] = $totalEPR;

                    //        $comments = "Stock Loss on dated: " . date('Y-m-d') . " to branch {$_SESSION['admin']->finascop_current_branch_id}.";
                    //        $transctionTemplate['comments'] = $comments;
                    $search = array(
                        "#AMT#",
                        "#NO#",
                        "#ID#"
                    );
                    $replace = array(
                        $totalEPR,
                        $fsl_id,
                        $quor_TransferReqId
                    );
                    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                    if (strcmp($transctionTemplate['comments'], '') != 0){
                        $fields = array(
                            "waqu_TransDate" => date('Y-m-d') ,
                            "waqu_comment" => $transctionTemplate['comments'],
                            "waqu_SourceID" => intval($fsl_id) ,
                            "waqs_id" => intval($wqSettings['waqs_id']) ,
                            "waqu_Amount" => doubleval($totalEPR) ,
                            "br_id" => intval($_SESSION['admin']->finascop_current_branch_id) ,
                            "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                        );
                        //print_r($transctionTemplate);
                        $status = $db->perform('finascop_wallet_queue', $fields);
                    }
                    //END FINANCIAL TRANSACION
                    $status = $db->query('commit');
                    if ($status == 1)
                    {
                        $msg = "'Return Order Created'";
                        echo '{"success":true,"valid":true,"msg":' . $msg . '}';
                    }
                    else
                    {
                        $msg = "'Error occured while saving.'";
                        echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
                    }
                    break;
                case 'saveReturnOrder':
                    $iteminReturnedStocks = json_decode(stripslashes($_POST['iteminReturnedStock']));
                    $vendor = $_POST['rtvendorId'];
                    $prtoValue = $_POST['prtoValue'];
                    $prtoAmount = $_POST['prtoAmount'];
                    $fpr['pure_vendorId'] = $vendor;
                    $fpr['br_id'] = $_SESSION['admin']->finascop_current_branch_id;
                    $fpr['pure_EntryBy'] = $_SESSION['admin']->UserId;
                    $fpr['pure_EntryOn'] = date('Y-m-d H:i:s');
                    $fpr['pure_GrossActualAmt'] = $prtoValue;
                    $fpr['pure_GrossAmt'] = $prtoAmount;
                    $db->query('begin');
                    $status = $db->perform('finascop_purchase_return', $fpr);
                    $pure_id = $db->insert_id();
                    $totalItems = 0;
                    $itemCount = 0;
                    $totalEPR = 0;
                    $datTime = date('Y-m-d H:i:s');
                    foreach ($iteminReturnedStocks as $iteminReturnedStock)
                    {
                        //print_r($iteminReturnedStock);//gridForReturnOrder
                        $itemCount++;
                        $fpri['purd_itemID'] = $iteminReturnedStock->stit_id;
                        $fpri['purd_itemReturnedQty'] = $iteminReturnedStock->itemReturnable;
                        $fpri['pure_Id'] = $pure_id;
                        $fpri['purd_Rate'] = $iteminReturnedStock->frbi_epr;
                        $status = $db->perform('finascop_purchase_return_items', $fpri);
                        $totalItemsQty += $iteminReturnedStock->itemReturnable;
                        $totalEPR = $totalEPR + $iteminReturnedStock->frbi_epr;
                        $status = $db->query("UPDATE finascop_return_branch_inventory SET item_returned = item_returned + {$iteminReturnedStock->itemReturnable},item_count = item_count - {$iteminReturnedStock->itemReturnable},updated_on = '{$datTime}' WHERE frbi_id = {$iteminReturnedStock->frbi_id}");

                        $status = $db->query("UPDATE finascop_stock_item_inventorydetails SET stiid_status = if(stiid_status=10,11,16) WHERE stiid_itemmasterid = {$iteminReturnedStock->stit_id} AND stiid_status IN (15 ,10)");
                    }
                    $fprUpdate['pure_TotalItems'] = $itemCount;
                    $fprUpdate['pure_TotalItemsQty'] = $totalItemsQty;
                    $status = $db->perform('finascop_purchase_return', $fprUpdate, 'update', " pure_id = {$pure_id}");
                    $vendoDetails = $db->getFromDB("SELECT stpa_Fname,stpa_MobileNo FROM finascop_stock_party WHERE stpa_id = {$vendor}", true);
                    //BEGIN FINANCIAL TRANSACION
                    $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 57";
                    $wqSettings = $db->getFromDB($query, true);
                    $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);

                    $transctionTemplate['cr']['csStockDamaged']['amt'] = doubleval($totalEPR);
                    $transctionTemplate['dr']['lostStock']['amt'] = doubleval($totalEPR - $prtoAmount);
                    $transctionTemplate['dr']['vendor']['amt'] = doubleval($prtoAmount);
                    $transctionTemplate['dr']['vendor']['key'] = $db->getItemFromDB("SELECT accled_ReferenceId FROM finascop_stock_party WHERE stpa_id = {$vendor}");

                    $search = array(
                        "#AMT#",
                        "#NO#",
                        "#ID#",
                        "#NAME#",
                        "#PHONE#"
                    );
                    $replace = array(
                        $totalEPR,
                        $pure_id,
                        $transctionTemplate['dr']['vendor']['key'],
                        $vendoDetails['stpa_Fname'],
                        $vendoDetails['stpa_MobileNo']
                    );
                    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                    if (strcmp($transctionTemplate['comments'], '') != 0){
                        
                        $fields = array(
                            "waqu_TransDate" => date('Y-m-d') ,
                            "waqu_comment" => $transctionTemplate['comments'],
                            "waqu_SourceID" => intval($pure_id) ,
                            "waqs_id" => intval($wqSettings['waqs_id']) ,
                            "waqu_Amount" => doubleval($totalEPR) ,
                            "br_id" => intval($_SESSION['admin']->finascop_current_branch_id) ,
                            "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                        );
                        //print_r($transctionTemplate);
                        $status = $db->perform('finascop_wallet_queue', $fields);
                    }
                    //END FINANCIAL TRANSACION
                    $status = $db->query('commit');
                    if ($status == 1)
                    {
                        $msg = "'Return Order Created'";
                        echo '{"success":true,"valid":true,"msg":' . $msg . '}';
                    }
                    else
                    {
                        $msg = "'Error occured while saving.'";
                        echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
                    }

                    break;
                case 'getReturnActualValue':
                    print_r($_POST);
                    break;

                case 'confirmedReturnedToDamage':
                    $data = $_POST;
                    $itemarr = json_decode(stripslashes($_POST['itemarr']) , true);
                    $itemIds = implode(',', $itemarr);
                    $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
                    $rec_start = empty($data['start']) ? 0 : $data['start'];
                    $rec_sort = empty($data['sort']) ? 'stit_id' : $data['sort'];
                    $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
                    $search = " WHERE 1=1 and fsb.stit_id in ({$itemIds}) ";
                    $filter = $_POST['filter'];
                    if (isset($filter))
                    {

                        foreach ($filter as $key => $field)
                        {
                            $checkComa = strstr($field['data']['value'], ',');

                            if ($checkComa != '')
                            {
                                $fiterItem = $field['data']['value'];
                                $fiterItem = str_replace(',', "','", $fiterItem);
                                $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                            }
                            else
                            {
                                $searchitem .= " and ({$field[field]} LIKE '%{$field['data']['value']}%') ";
                            }
                        }
                    }
                    if ($_SESSION['admin']->br_PyramidLevel == 1)
                    {
                        $centStores = $db->getItemFromDB("SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_cpd = {$_SESSION['admin']->finascop_current_branch_id}");
                        $where = " AND branch_id IN ({$centStores})";
                    }
                    else
                    {
                        $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;
                        $branchName = $_POST['branchName'];
                        $where = " AND branch_id =" . $br_ID;
                    }

                    // $selBranchCpd = $db->getItemFromDB("SELECT br_IsCPD FROM finascop_branch WHERE br_ID = {$br_ID}");
                    if ($selBranchCpd == 1)
                    {
                        $rackStatus = " AND stiid_status = 1";
                        $dispatchStatus = " AND stiid_status = 2";
                        $receiveStatus = " ";
                        $deliverStatus = " ";
                    }
                    else
                    {
                        $rackStatus = " AND stiid_status = 4";
                        $dispatchStatus = " ";
                        $receiveStatus = " AND stiid_status = 3";
                        $deliverStatus = " AND stiid_status = 5";
                    }

                    $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . " finascop_return_branch_inventory fsb " . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID " . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$where}{$searchitem}";
                    $count = $db->getItemFromDB($countQuery);
                    $listQuery = "SELECT fsb.stit_id as stit_id,br.br_Name as br_Name,fsi.stit_SKU as stit_SKU,fsbg_id,frbi_leastSKUmrp,frbi_leastSKUepr," . "fsb.item_count as item_count,fsb.item_count as itemReturnable,fsb.mrp as mrp,fsb.selling_price as selling_price,fsb.branch_id as branchId,stit_category_name,stit_quantity,stit_brand_name," . "stit_product_variant,frbi_epr,frbi_id," . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stitl1_optimumqty " . "WHEN br.br_stockLevel= 2 THEN fsi.stitl2_optimumqty " . "ELSE stitl3_optimumqty END AS optimumqty," . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stit11_minimumqty " . "WHEN br.br_stockLevel= 2 THEN fsi.stit12_minimumqty " . "ELSE stit13_minimumqty END AS minimumqty," . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stit11_maximumqty " . "WHEN br.br_stockLevel= 2 THEN fsi.stit13_maximumqty " . "ELSE stit13_maximumqty END AS maximumqty " . "FROM finascop_return_branch_inventory fsb " . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID " . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$where}{$searchitem} ORDER BY $rec_sort $rec_sort_dir ";
                    $datas = $db->getMulipleData($listQuery, true);
                    $resCount = count($datas);
                    for ($i = 0;$i < $resCount;$i++)
                    {
                        //echo '$i'.$stii_epraft;
                        $datas[$i]['actualValue'] = 0;
                        $availableBarcodes = $db->getMulipleData("SELECT stiid_barcode,stiid_fpoid,stiid_itemmasterid,stiid_barcode_created_level,stiid_leastSKUepr,stii_epraft FROM finascop_stock_item_inventorydetails " . "WHERE stiid_itemmasterid = {$datas[$i]['stit_id']} AND stiid_status IN( 15,10)", true);
                        $packageDetails = $db->getFromDB("SELECT csb_package_type_name,csb_package_type_id,cs_nos,cs_package_type_name,cs_package_type_id,ds_package_type_id,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,cos_package_type_id " . "FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['stit_id']}", true);
                        $countDiff = $datas[$i]['item_count'] - count($availableBarcodes);
                        $totalItem = 0;
                        if (count($availableBarcodes) > 0)
                        {
                            foreach ($availableBarcodes as $availableBarcod)
                            {
                                if ($availableBarcod['stiid_barcode_created_level'] == 2)
                                {
                                    $totalItem = $totalItem + $packageDetails['cs_nos'];
                                    $stii_epraft = $availableBarcod['stiid_leastSKUepr'] * $packageDetails['cs_nos'];
                                }
                                else if ($availableBarcod['stiid_barcode_created_level'] == 3)
                                {
                                    $totalItem = $totalItem + 1;
                                    $stii_epraft = $availableBarcod['stiid_leastSKUepr'];
                                }
                                //echo '$i'.$datas[$i]['actualValue'];
                                $datas[$i]['actualValue'] = $datas[$i]['actualValue'] + $stii_epraft;
                            }
                        }
                        //echo $countDiff;
                        $datas[$i]['totalSKUs'] = $totalItem;
                        if ($countDiff > 0)
                        {
                            $stii_epraft = $datas[$i]['frbi_leastSKUepr'] * $packageDetails['cs_nos'] * $countDiff;
                            $datas[$i]['actualValue'] = $datas[$i]['actualValue'] + $stii_epraft;
                        }
                    }

                    if (!empty($datas))
                    {
                        echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
                    }
                    else echo '{"totalCount":"0","data":' . json_encode($datas) . '}';

                    break;
                case 'getItemBarcodes':
                    $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
                    $start = isset($_POST['start']) ? $_POST['start'] : 0;
                    $sort = empty($sort) ? 'rtrqb_barcode' : $sort;
                    $dir = empty($dir) ? 'ASC' : $dir;
                    $search = " WHERE 1=1 ";
                    $filter = $_POST['filter'];
                    if (isset($filter))
                    {
                        foreach ($filter as $key => $field)
                        {
                            if ($field['data']['value'] != "")
                            {
                                $checkComa = strstr($field['data']['value'], ',');
                                if ($checkComa != '')
                                {
                                    $fiterItem = $field['data']['value'];
                                    $fiterItem = str_replace(',', "','", $fiterItem);
                                    $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                                }
                                else
                                {
                                    $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                                }
                            }
                        }
                    }
                    $fsto_source = $_POST['fsto_source'];
                    $countQuery = "SELECT COUNT(*) FROM finascop_stock_return_request_order_barcodes {$search}  AND rtrqb_itemId=" . intval($_POST['fsto_itemId']) . " AND order_id={$_POST['fsto_id']} ORDER BY {$sort} {$dir}";

                    $listQuery = "SELECT rtrqb_barcode,rtrqb_itemId,rtrqb_id FROM finascop_stock_return_request_order_barcodes {$search} AND rtrqb_itemId=" . intval($_POST['fsto_itemId']) . " AND order_id={$_POST['fsto_id']} ORDER BY {$sort} {$dir} ";

                    $db->printGridJson($countQuery, $listQuery);
                    break;
                case 'getRetalineBannedStock':
                    $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
                    $start = isset($_POST['start']) ? $_POST['start'] : 0;
                    $sort = empty($sort) ? 'fsibb_id' : $sort;
                    $dir = empty($dir) ? 'DESC' : $dir;
                    $search = " WHERE 1=1 ";
                    $filter = $_POST['filter'];
                    $subCategory_name = '';
                    if (isset($filter))
                    {
                        foreach ($filter as $key => $field)
                        {
                            if ($field['data']['value'] != "")
                            {
                                $checkComa = strstr($field['data']['value'], ',');
                                if ($checkComa != '')
                                {
                                    $fiterItem = $field['data']['value'];
                                    $fiterItem = str_replace(',', "','", $fiterItem);
                                    $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                                }
                                else
                                {

                                    $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                                }
                            }
                        }
                    }
                    $branchName = $_POST['branchName'];
                    $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;
                    $brcond = "  cpd_branch_id =" . $br_ID;

                    $countQuery = "SELECT COUNT(*) FROM finascop_stock_item_banned_batch " . "INNER JOIN  finascop_stock_item_inventorydetails ON  stiid_itemmasterid = stit_id AND stiid_batchno = fsibb_batch AND {$brcond} AND stiid_status IN (17) {$search} GROUP BY fsibb_batch  ";
                    $count = $db->getItemFromDB($countQuery);

                    $listQuery = "SELECT COUNT(stiid_id) AS fsibb_quantity,stiid_itemmasterid,stiid_itemmastername,fsibb_batch,fsibb_id FROM finascop_stock_item_banned_batch " . "INNER JOIN  finascop_stock_item_inventorydetails ON  stiid_itemmasterid = stit_id AND stiid_batchno = fsibb_batch AND {$brcond} AND stiid_status IN (17) {$search} GROUP BY fsibb_batch ORDER BY {$sort} {$dir} limit $start,$limit";
                    $datas = $db->getMulipleData($listQuery, true);
                    $resCount = count($datas);
                    if (!empty($datas))
                    {
                        echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
                    }
                    else
                    {
                        echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
                    }
                    break;
                case 'addBannedItemtoStockReturn':
                    // print_r($_POST);exit();
                    $returnOrders = $_POST['returnOrders'];
                    $itemIds = json_decode($_POST['bannedItemIds']);
                    $itemRecords = json_decode(stripslashes($_POST['bannedItemRecords']));
                    $date = date('Y-m-d H:i:s');

                    //print_r($_POST);
                    //  exit();
                    $db->query('begin');
                    $uuid = $_POST['uuid'];
                    $rtrqo['rtrqo_sourceBranch'] = $_SESSION['admin']->finascop_current_branch_id;
                    $rtrqo['rtrqo_dstinationBranch'] = $db->getItemFromDB("SELECT br_cpd FROM  finascop_branch WHERE br_Id = {$_SESSION['admin']->finascop_current_branch_id}");
                    $rtrqo['rtrqo_createdOn'] = date("Y-m-d H:i:s");
                    $rtrqo['rtrqo_createdBy'] = $_SESSION['admin']->UserId;
                    $rtrqo['rtrqo_isDirect'] = 1;
                    $rtrqo['rtrqo_isBanned'] = 1;
                    $rtrqo['rtrqo_uuid'] = $uuid;
                    $rtrqo['rtrqo_sourceType'] = 1;
                    $rtrqo['rtrqo_type'] = 3;
                    $db->query('begin');
                    $status = $db->perform("finascop_stock_return_request_order", $rtrqo);
                    $rtrqo_id = $db->insert_id();
                    if (!empty($itemRecords))
                    {
                        foreach ($itemRecords as $itemRecord)
                        {

                            $rtrqodet['rtrqo_id'] = $rtrqo_id;
                            $rtrqodet['rtrqod_item_id'] = $itemRecord->stiid_itemmasterid;
                            $rtrqodet['rtrqod_return_damaged'] = $itemRecord->fsibb_quantity;
                            $rtrqodet['rtrqod_return_count'] = $itemRecord->fsibb_quantity;
                            $rtrqodet['rtrqo_uid'] = $uuid;
                            $rtrqodet['rtrqo_type'] = 3;

                            $status = $db->perform('finascop_stock_return_request_order_details', $rtrqodet);
                        }
                    }

                    $inventoryDetails = $db->getMultipleData("SELECT stiid_id,stiid_itemmasterid,stiid_itemmastername,stiid_batchno,cpd_branch_id,fsbg_id,stiid_status,stiid_barcode  FROM finascop_stock_item_inventorydetails " . "WHERE stiid_itemmasterid = {$itemRecord->stiid_itemmasterid} AND stiid_batchno = '{$itemRecord->fsibb_batch}' AND cpd_branch_id = {$_SESSION['admin']->finascop_current_branch_id} AND stiid_status IN (17)", true);
                    foreach ($inventoryDetails as $inventoryDetai)
                    {
                        $pyramidLevel = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch WHERE br_ID = {$inventoryDetai['cpd_branch_id']}");

                        $invent['stiid_status'] = 13;
                        $invent['stiid_updatedon'] = date("Y-m-d H:i:s");
                        $invent['stiid_updatedby'] = $_SESSION['admin']->Finascop_UserId;
                        $status = $db->perform("finascop_stock_item_inventorydetails", $invent, 'update', " stiid_id = {$inventoryDetai['stiid_id']}");
                        $fsiidmDatatl['stiidm_barcode'] = $inventoryDetai['stiid_barcode'];
                        $fsiidmDatatl['stiidm_itemmasterid'] = $inventoryDetai['stiid_itemmasterid'];
                        $fsiidmDatatl['created_at'] = date('Y-m-d H:i:s');
                        $fsiidmDatatl['stiid_id'] = $inventoryDetai['stiid_id'];
                        $fsiidmDatatl['stiidm_details'] = 'Banned Barcode is moved for stock return - ' . $inventoryDetai['stiid_barcode'];
                        $status = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiidmDatatl);
                    }
                    //print_r($inventoryDetails);
                    //AND cpd_branch_id = {$_SESSION['admin']->finascop_current_branch_id}
                    

                    $status = $db->query('commit');
                    if ($status == 1)
                    {
                        $msg = "'Items added to stock return.'";
                        echo '{"success":true,"valid":true,"msg":' . $msg . '}';
                    }
                    else
                    {
                        $msg = "'Error occured while saving.'";
                        echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
                    }
                    break;
                case 'addBannedItemtoStockReturninCS':
                    $returnOrders = $_POST['returnOrders'];
                    $itemIds = json_decode($_POST['bannedItemIds']);
                    $itemRecords = json_decode(stripslashes($_POST['bannedItemRecords']));
                    $datetime = date('Y-m-d H:i:s');

                    if (!empty($itemRecords))
                    {
                        $db->query('begin');
                        foreach ($itemRecords as $itemRecord)
                        {
                            $inventoryDetails = $db->getMultipleData("SELECT * FROM finascop_stock_item_inventorydetails WHERE cpd_branch_id = {$_SESSION['admin']->finascop_current_branch_id} AND stiid_batchno = '{$itemRecord->fsibb_batch}' AND stiid_itemmasterid = {$itemRecord->stiid_itemmasterid} AND  stiid_status = 17", true);

                            // print_r($inventoryDetails);
                            foreach ($inventoryDetails as $inventoryDetail)
                            {

                                $itemCount = $db->getItemFromDB("SELECT count(*) FROM finascop_return_branch_inventory WHERE stit_id = {$inventoryDetail['stiid_itemmasterid']} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}  AND fsbg_id = {$inventoryDetail['fsbg_id']}");

                                if ($itemCount > 0)
                                {

                                    $fsiidmstatus = $db->query("UPDATE finascop_return_branch_inventory SET updated_on = '{$datetime}',item_count = item_count + 1 WHERE stit_id = {$inventoryDetail['stiid_itemmasterid']} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}  AND fsbg_id = {$inventoryDetail['fsbg_id']}");
                                }
                                else
                                {
                                    $fsbi['stit_id'] = $inventoryDetail['stiid_itemmasterid'];
                                    $fsbi['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
                                    $fsbi['item_count'] = 1;
                                    $fsbi['mrp'] = $inventoryDetail['stiid_mrp'];
                                    $fsbi['selling_price'] = $inventoryDetail['stiid_selpri'];
                                    $fsbi['frbi_epr'] = $inventoryDetail['stii_epraft'];
                                    $fsbi['updated_on'] = date('Y-m-d H:i:s');
                                    $fsbi['created_at'] = date('Y-m-d H:i:s');
                                    $fsbi['fsbg_id'] = $inventoryDetail['fsbg_id'];
                                    $fsbi['frbi_leastSKUmrp'] = $inventoryDetail['stiid_leastSKUmrp'];
                                    $fsbi['frbi_leastSKUepr'] = $inventoryDetail['stiid_leastSKUepr'];
                                    $fsiidmstatus = $db->perform('finascop_return_branch_inventory', $fsbi);
                                }

                                $inventoryDetailDatat['stiid_status'] = 15;
                                $inventoryDetailDatat['stiid_updatedby'] = $_SESSION['admin']->UserId;
                                $inventoryDetailDatat['stiid_updatedon'] = $datetime;
                                $fsiidmstatus = $db->perform('finascop_stock_item_inventorydetails', $inventoryDetailDatat, 'update', " stiid_barcode = {$inventoryDetail['stiid_barcode']}");

                                $fsiidmData['stiid_id'] = $inventoryDetail['stiid_id'];
                                $fsiidmData['stiidm_itemmasterid'] = $inventoryDetail['stiid_itemmasterid'];
                                $fsiidmData['stiidm_barcode'] = $inventoryDetail['stiid_barcode'];
                                $fsiidmData['created_at'] = date('Y-m-d H:i:s');
                                $fsiidmData['stiidm_details'] = 'Banned stock is added to purchase return ' . $quor_TransferReqId;
                                $fsiidmstatus = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiidmData);
                            }
                        }

                        $fsiidmstatus = $db->query('commit');
                        if ($fsiidmstatus == 1)
                        {
                            $msg = "'Items added to purchase return.'";
                            echo '{"success":true,"valid":true,"msg":' . $msg . '}';
                        }
                        else
                        {
                            $msg = "'Error occured while saving.'";
                            echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
                        }
                    }

                    break;
                case 'listbarcodesinCurrentStock':
                    //pass branchid and item list details from stock inventory details where sttus id in 1,4
                    $rec_limit = empty($data['limit']) ? 16 : $data['limit'];
                    $rec_start = empty($data['start']) ? 0 : $data['start'];
                    $rec_sort = empty($data['sort']) ? 'stiid_id' : $data['sort'];
                    $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
                    $filter_part = ' AND 1=1 and stiid_status = 1 ';

                    if (isset($_POST['filter']))
                    {

                        foreach ($_POST['filter'] as $key => $val)
                        {
                            if ($val['field'] == 'stiid_barcode')
                            {
                                $filter_part .= " and " . $val['field'] . " = " . $val['data']['value'] . " ";
                            }
                            else
                            {
                                $filter_part .= " and " . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
                            }
                        }
                    }

                    $countQuery = "SELECT COUNT(*) from finascop_stock_item_inventorydetails WHERE cpd_branch_id = {$_SESSION['admin']->finascop_current_branch_id} AND stiid_itemmasterid = " . intval($_POST['stit_id']) . "   {$filter_part}";
                    $listQuery = "SELECT stiid_id,stiid_barcode,DATE_FORMAT(stiid_createdon, '%d-%m-%Y %H:%i:%s') AS stiid_createdon,DATE_FORMAT(stiid_updatedon, '%d-%m-%Y %H:%i:%s') AS stiid_updatedon,stiid_leastSKUmrp," . "(SELECT stiid_description from finascop_stock_item_inventorydetails_status where finascop_stock_item_inventorydetails_status.stiid_status = finascop_stock_item_inventorydetails.stiid_status) as stiid_statusStat," . "ROUND(stiid_customerRateHmDel, 2) as stiid_customerRateHmDel,ROUND(stiid_customerRateCouDel, 2) as stiid_customerRateCouDel,ROUND(stiid_customerRatePikup, 2) as stiid_customerRatePikup,DATE_FORMAT(stiid_expirydate, '%d-%m-%Y') AS stiid_expirydate," . "stiid_itemleastSKUptr,stiid_itemleastSKUpts,stiid_batchno " . "from finascop_stock_item_inventorydetails WHERE cpd_branch_id = {$_SESSION['admin']->finascop_current_branch_id} AND stiid_itemmasterid = {$_POST['stit_id']}  {$filter_part}  ORDER BY $rec_sort $rec_sort_dir ";
                    $db->printGridJson($countQuery, $listQuery);
                    break;
                }
?>