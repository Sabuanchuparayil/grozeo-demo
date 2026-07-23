<?php

switch ($op) {
    case 'listIncompleteOrderData':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'fsto_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['order_id', 'order_generated_id', 'member_phone', 'order_created_on', 'order_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }
        $branchName = $_POST['branchName'];
        $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;

        if ($_SESSION['admin']->br_PyramidLevel == 1)
            $where = " AND fsto_source =" . $br_ID;
        else
            $where = " AND fsto_source =" . $_SESSION['admin']->finascop_current_branch_id;

        $query = "SELECT fo.fsto_id as fsto_id,fo.fsto_uid as fsto_uid,fstr_id,(SELECT SUM(fsto_ItemWeight) FROM finascop_stock_transfer_order_details fd WHERE fo.fsto_id= fd.fsto_id) as fsto_ItemWeight,"
                . "(SELECT SUM(fsto_ItemVolume) FROM finascop_stock_transfer_order_details fd WHERE fo.fsto_id= fd.fsto_id) as fsto_ItemVolume,fsto_sourcetype,fsto_destinationtype,"
                . "DATE_FORMAT(fsto_createdOn,'%d-%m-%y') as fsto_createdOn,"
                . "(SELECT br_Name FROM finascop_branch where br_ID = fo.fsto_source) as fsto_sourceName,fsto_source,"
                . "CASE WHEN fsto_ordertype=0 THEN 'CPD TO BR' WHEN fsto_ordertype=1 THEN 'B2C' WHEN fsto_ordertype=2 THEN 'B2B' END AS fsto_ordertype,"
                . "CASE WHEN fsto_type=0 THEN 'User Created' WHEN fsto_type=1 THEN 'System Created' END AS fsto_type,"
                . "(SELECT fstos_status FROM finascop_stock_transfer_order_status WHERE fstos_id = fsto_status) as fsto_statusName,fsto_status,"
                . "(SELECT br_Name FROM finascop_branch where br_ID = fsto_destination) as fsto_destinationName,fsto_destination  FROM finascop_stock_transfer_order  fo WHERE fsto_status = 9 ";
        $countQuery = "SELECT COUNT(*) FROM ({$query}) as countICO  {$search} {$where} ";

        $listQuery = "SELECT * FROM ({$query}) AS listICO {$search} {$where} ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'getBranchName':
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch WHERE br_status = 'Active' AND (br_cpd={$branch_id} OR br_ID={$branch_id})", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'getOrderItemList':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'fo.fsto_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['order_id', 'order_generated_id', 'member_phone', 'order_created_on', 'order_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    case 'string':
                        foreach ($filter as $key => $field) {
                            if ($field['data']['value'] != "") {
                                $checkComa = strstr($field['data']['value'], ',');
                                if ($checkComa != '') {
                                    $fiterItem = $field['data']['value'];
                                    $fiterItem = str_replace(',', "','", $fiterItem);
                                    $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                                } else {
                                    $search .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                                }
                            }
                        }
                }
            }
        }
        $fsto_id = $_POST['fsto_id'];
        $barcode = $_POST['barcodesearch_field'];
        $branch = $_POST['fsto_destination'];

        $countQry = "SELECT COUNT(*) FROM finascop_stock_transfer_order fo INNER JOIN finascop_stock_transfer_order_details fd ON fo.fsto_id = fd.fsto_id {$search} AND fo.fsto_id = {$fsto_id}  ORDER BY {$sort} {$dir}";

        $listQry = "SELECT fo.fsto_uid as fsto_uid,fo.fsto_id as fsto_id,fsto_ItemId,fsto_createdOn,fsto_destination,fsto_destination,fsto_source,(SELECT br_Name FROM finascop_branch where br_ID = fsto_source) as fsto_source,"
                . "(SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = fsto_ItemId) as item_name,fsto_ItemQty,fsto_pkdQty,(SELECT br_Name FROM finascop_branch where br_ID = fsto_destination) as branch,"
                . "fo.fsto_id as fsto_id,fstro_ItemMRP as mrp,fstro_ItemSPincTax as selPrce"
                . " FROM finascop_stock_transfer_order fo INNER JOIN finascop_stock_transfer_order_details fd ON fo.fsto_id = fd.fsto_id  {$search} AND fo.fsto_id={$fsto_id} ORDER BY {$sort} {$dir} ";
        $datas = $db->getMulipleData($listQry, true);
        $resCount = count($datas);
        $count = $db->getItemFromDB($countQry);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $branch = $db->getItemFromDB("SELECT mrp FROM finascop_stock_branch_inventory WHERE stit_id={$datas[$i]['fsto_ItemId']} AND branch_id={$datas[$i]['fsto_destination']}");
                //$datas[$i]['mrp'] = $branch;
                $datas[$i]['slNo'] = $i + 1;
            }
        }
        echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        break;
    case 'cancelInComplOrder':
        //$del_query = "DELETE FROM finascop_stock_transfer_order_details_barcodes_temp WHERE tmp_barcode_fstoId={$fsto_id} AND rpb_status=1";
        $fsto_id = $_POST['fsto_id'];
        $fsto_ordertype = $_POST['fsto_ordertype'];
        $fstr_id = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$fsto_id}");

        $db->query('begin');
        $date = date('Y-m-d H:i:s');
        $userId = $_SESSION['admin']->UserId;
        switch ($fsto_ordertype) {
            case 'CPD TO BR':
                $orderType = 0;
                $toParentDetails = $db->getFromDB("SELECT fstr_id,fstr_uid,fstr_type,fstr_status from finascop_stock_transfer_request where fstr_id = {$fstr_id}", true);
                $fstr['fstr_updatedOn'] = $date;
                $bbso['fstr_updatedBy'] = $userId;
                $bbso['fstr_status'] = 16;
                $status = $db->perform("finascop_stock_transfer_request", $bbso, 'update', 'fstr_id =' . $toParentDetails['fstr_id']);
                break;
            case 'B2C':
                $orderType = 1;
                $toParentDetails = $db->getFromDB("SELECT order_id,order_order_id,order_isB2b,order_customer_id,status_id from retaline_customer_order where order_id = {$fstr_id}", true);
                $order_no = $toParentDetails['order_id'];

                $order_customer_id = $db->getItemFromDB("SELECT order_customer_id FROM retaline_customer_order WHERE order_id = {$toParentDetails['order_id']}");
                $order_branch_id = $db->getItemFromDB("SELECT order_branch_id FROM retaline_customer_order WHERE order_id = {$toParentDetails['order_id']}");
                $item_product_ids = $db->getMultipleData("SELECT item_product_id,item_id,item_retail_price,item_sales_price,item_order_qty FROM retaline_customer_order_items WHERE customer_order_id = {$toParentDetails['order_id']}", true);
                $payment_mode = $db->getItemFromDB("SELECT payment_mode FROM retaline_customer_order WHERE order_id = {$toParentDetails['order_id']}");

                $status = $db->query("DELETE FROM finascop_stock_blocked WHERE order_id = {$toParentDetails['order_id']}");

                foreach ($item_product_ids as $item_product_id) {
                    $itemSKU = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$item_product_id['item_product_id']}");
                    $itemCountEntry = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory WHERE branch_id = {$order_branch_id} AND stit_id =  {$item_product_id['item_product_id']}");
                    $orderInvetories = $db->getFromDB("SELECT stiid_mrp,stiid_selpri,stiid_itemmasterid,fsbg_id,stiid_itemmastername,stiid_barcode FROM finascop_stock_item_inventorydetails WHERE stiid_itemmasterid = {$item_product_id['item_product_id']} AND cust_order_id = '{$toParentDetails['order_id']}'", true);

                    $order_roundoff = $db->getItemFromDB("SELECT order_roundoff FROM retaline_customer_order WHERE order_id = {$toParentDetails['order_id']}");
                    $order_delivery_charge = $db->getItemFromDB("SELECT order_delivery_charge FROM retaline_customer_order WHERE order_id = {$toParentDetails['order_id']}");
                    $itemPrices = $db->getFromDB("SELECT item_price,item_discount,item_order_qty FROM retaline_customer_order_items WHERE item_id = {$item_product_id['item_id']} AND customer_order_id = {$toParentDetails['order_id']}", true);
                    if (($countorder_ItemsReturned == $orderItemCount) && ($count == 1)) {
                        $item_sales_price = floatval($itemPrices['item_price']) - floatval($itemPrices['item_discount']) + floatval($order_delivery_charge) + floatval($order_roundoff);
                        $bcwt['brcw_AddInfo'] = "On cancel of {$itemSKU} from incomplete order with delivery charge on order {$toParentDetails['order_id']}";
                    } else {
                        $bcwt['brcw_AddInfo'] = "On cancel of {$itemSKU} from incomplete order on order {$toParentDetails['order_id']}";
                        $item_sales_price = (floatval($itemPrices['item_price']) - floatval($itemPrices['item_discount'])) / $itemPrices['item_order_qty'];
                    }

                    switch ($payment_mode) {
                        case 2://onlinepayment
                        case 3://Wallet
                        case 5://online with Wallet
                            //4. THe customer wallet should be updated.

                            $status = $db->query("UPDATE retaline_customer SET cust_walletbalance = cust_walletbalance + {$item_sales_price}  WHERE cust_id = {$order_customer_id}");
                            //4. THe customer wallet should be updated.
                            //5. An entry should be added in customer wallet transaction
                            $orderInvetorBarcodes = $db->getItemFromDB("SELECT GROUP_CONCAT(stiid_barcode) FROM finascop_stock_item_inventorydetails "
                                    . "WHERE stiid_itemmasterid = {$item_product_id['item_product_id']} AND cust_order_id = '{$toParentDetails['order_id']}'", true);
                            $bcwt['cust_id'] = $order_customer_id;
                            $bcwt['refentry_id'] = $order_no;
                            $bcwt['brcw_SourceType'] = 1;
                            $bcwt['brcw_Amount'] = $item_sales_price;
                            if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                                $bcwt['stiid_barcode'] = $orderInvetorBarcodes;
                            } else {
                                $bcwt['stiid_barcode'] = 0;
                            }
                            $bcwt['brcw_CreatedOn'] = date("Y-m-d H:i:s");
                            $bcwt['brcw_Updateon'] = date("Y-m-d H:i:s");
                            $status = $db->perform('retaline_customer_wallet_transaction', $bcwt);
                            //5. An entry should be added in customer wallet transaction

                            break;
                        case 4://COD with Wallet
                            $wcodAmounts = $db->getFromDB("SELECT order_amount_payable,order_wallet_amount,order_amount_returnon_cash,order_amount_addedon_wallet FROM retaline_customer_order WHERE order_id = {$order_no}", true);
                            $codAmt = $wcodAmounts['order_amount_payable'] - $wcodAmounts['order_amount_returnon_cash'];
                            $walletAmt = $wcodAmounts['order_wallet_amount'] - $wcodAmounts['order_amount_addedon_wallet'];
                            if ($codAmt >= $item_sales_price) {
                                $status = $db->query("UPDATE retaline_customer_order SET order_amount_returnon_cash = order_amount_returnon_cash + {$item_sales_price}  WHERE order_id = {$order_no}");
                            }
                            if ($codAmt < $item_sales_price) {

                                if ($codAmt == 0) {
                                    $status = $db->query("UPDATE retaline_customer_order SET order_amount_addedon_wallet = order_amount_addedon_wallet + {$item_sales_price}  WHERE order_id = {$order_no}");
                                } else {
                                    $item_sales_price = $item_sales_price - $codAmt;
                                    $status = $db->query("UPDATE retaline_customer_order SET order_amount_addedon_wallet = order_amount_addedon_wallet + {$item_sales_price}  WHERE order_id = {$order_no}");
                                }
                                //4. THe customer wallet should be updated.

                                $status = $db->query("UPDATE retaline_customer SET cust_walletbalance = cust_walletbalance + {$item_sales_price}  WHERE cust_id = {$order_customer_id}");
                                //4. THe customer wallet should be updated.
                                //5. An entry should be added in customer wallet transaction

                                $orderInvetorBarcodes = $db->getItemFromDB("SELECT GROUP_CONCAT(stiid_barcode) FROM finascop_stock_item_inventorydetails "
                                        . "WHERE stiid_itemmasterid = {$item_product_id['item_product_id']} AND cust_order_id = '{$toParentDetails['order_id']}'", true);
                                $bcwt['cust_id'] = $order_customer_id;
                                $bcwt['refentry_id'] = $order_no;
                                $bcwt['brcw_SourceType'] = 1;
                                $bcwt['brcw_Amount'] = $item_sales_price;
                                if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                                    $bcwt['stiid_barcode'] = $orderInvetorBarcodes;
                                } else {
                                    $bcwt['stiid_barcode'] = 0;
                                }

                                $bcwt['brcw_CreatedOn'] = date("Y-m-d H:i:s");
                                $bcwt['brcw_Updateon'] = date("Y-m-d H:i:s");
                                $status = $db->perform('retaline_customer_wallet_transaction', $bcwt);
                                //5. An entry should be added in customer wallet transaction
                            }
                            break;
                    }
                }
                $order_his['order_id'] = $toParentDetails['order_id'];
                $order_his['order_status'] = 19;
                $order_his['created_at'] = $date;
                $order_his['order_action'] = "Cancelled by Admin user {$_SESSION['admin']->UserName}";
                $status = $db->perform('retaline_customer_order_history', $order_his);

                $datasr['status_id'] = 19;
                $datasr['updated_at'] = $date;
                $status = $db->perform('retaline_customer_order', $datasr, 'update', 'order_id=' . $toParentDetails['order_id']);
                $order_canc['customer_id'] = $toParentDetails['order_customer_id'];
                $order_canc['order_id'] = $toParentDetails['order_id'];
                $order_canc['reason'] = 'From Incomplete Orders';
                $order_canc['cancelled_by_type'] = 2;
                $order_canc['cancelled_by_id'] = $userId;
                $order_canc['created_at'] = $date;
                $status = $db->perform('retaline_customer_order_cancellationdets', $order_canc);
                break;
            case 'B2B':
                $orderType = 2;
                $toParentDetails = $db->getFromDB("SELECT bbso_id,bbso_SONumber,b2b_Customer_ID,b2b_Customer_Name,status_id from retaline_B2B_SalesOrder where bbso_id = {$fstr_id}", true);
                $bbso['bbso_updatedon'] = $date;
                $bbso['bbso_updatedby'] = $userId;
                $bbso['status_id'] = 10;
                $status = $db->perform("retaline_B2B_SalesOrder", $bbso, 'update', 'bbso_id =' . $toParentDetails['bbso_id']);
                break;
        }

        $tmp_order = array(
            "fsto_status" => 15,
            "fsto_updateon" => $date,
            "fsto_updateby" => $userId
        );
        $status = $db->perform("finascop_stock_transfer_order", $tmp_order, 'update', 'fsto_id =' . $fsto_id);
        $status = $db->query("update finascop_stock_transfer_order_details_barcodes_temp set rpb_status=3 WHERE tmp_barcode_fstoId={$fsto_id}");
        $status = $db->query('commit');
        if ($status == 1) {
            echo '{"success":true,"valid":true,"msg":"Order Cancelled ."}';
            exit;
        } else {
            echo '{"success":false,"msg":"Error while saving data."}';
            exit;
        }

        break;
    case 'cancelInComplOrderItem':
        break;
    case 'submitManualPackingInCompleteOrder':
        $peItemSGriddata = json_decode(stripslashes($_POST['itemGriddata']));
        // print_r($peItemSGriddata);
        $db->query('begin');
        $order_id = $_POST['order_id'];
        if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
            $cfg_Value = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'MANUAL_PACKING_URL'");
            $url = str_replace('{orderId}', $order_id, $cfg_Value);
        } else {
            $cfg_Value = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'MANUAL_PACKING_NOBARCODE_URL'");
            $url = str_replace('{orderId}', $order_id, $cfg_Value);
        }

        $noofbags = 1;
        $fsto_ordertype = $_POST['fsto_ordertype'];
        switch ($fsto_ordertype) {
            case 'CPD TO BR':
                $orderType = 0;
                break;
            case 'B2B':
                $orderType = 1;
                break;
            case 'B2C':
                $orderType = 2;
                break;
        }
        $result['type'] = $orderType;
        $result['ismanual'] = 1;
        $result['isIncompleteOrder'] = true;
        if (!empty($peItemSGriddata)) {
            foreach ($peItemSGriddata as $peItemS) {
                $fsto_ItemId = $peItemS->fsto_ItemId;
                $fsto_pkdQty = $peItemS->fsto_pkdQty;
                $tmpitems = [];
                $tmpitems['item_id'] = $fsto_ItemId;
                if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                    $barcode = $db->getItemFromDB("SELECT fsto_pkdBarcode FROM finascop_stock_transfer_order_details WHERE fsto_ItemId={$fsto_ItemId} AND fsto_id={$order_id} ", true);
                    $barcodes = explode(',', $barcode);
                    $tmpitems['barcodes'] = $barcodes;
                } else {
                    $tmpitems['count'] = $fsto_pkdQty;
                }
                $result['items'][] = $tmpitems;
            }
        }
        $result['boy_order_id'] = '-10';
        $result['number_bags'] = $noofbags;

        //$fields_string = json_encode($result);
        $opts = array(
            CURLOPT_URL => $url,
            CURLINFO_CONTENT_TYPE => "application/json",
            CURLOPT_BINARYTRANSFER => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST => count($result),
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        header("Content-Type: application/json");
        echo $data;
        break;
    case 'incompOrderDetailsView':
        $fsto_id = isset($_POST['fsto_id']) ? intval($_POST['fsto_id']) : 0;
        if ($fsto_id > 0) {
            $result = $db->getFromDB("SELECT fsto_uid,fsto_createdOn,fsto_destination,(SELECT br_Name FROM finascop_branch where br_ID = fsto_source) as sourcename,fsto_ordertype,fstr_id,"
                    . "CASE WHEN fsto_ordertype=0 THEN 'CPD TO BR' WHEN fsto_ordertype=1 THEN 'B2C' WHEN fsto_ordertype=2 THEN 'B2B' END AS fsto_ordertypeName,"
                    . "(SELECT br_Name FROM finascop_branch where br_ID = fsto_destination) as branch,fsto_id"
                    . " FROM finascop_stock_transfer_order WHERE fsto_id={$fsto_id}", true);
            $totalItems = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id}");
            $fsto_ItemQty = $db->getItemFromDB("SELECT SUM(fsto_ItemQty) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id}");
            $fsto_pkdQty = $db->getItemFromDB("SELECT SUM(fsto_pkdQty) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id}");
            switch ($result['fsto_ordertype']) {
                case '0'://cpd to branch
                    $parentOrder = $db->getFromDB("SELECT fstr_uid AS paOrderNumber,fstr_createdOn AS paOrderDate FROM finascop_stock_transfer_request WHERE fstr_id = {$result['fstr_id']}", true);
                    break;
                case '1'://b2c
                    $parentOrder = $db->getFromDB("SELECT order_order_id AS paOrderNumber,order_confirm_date AS paOrderDate,order_customer_id as custId,total as totalAmt FROM retaline_customer_order WHERE order_id = {$result['fstr_id']}", true);
                    $customerName = $db->getItemFromDB("SELECT cust_customer_name FROM retaline_customer WHERE cust_id = {$parentOrder['custId']}");
                    $cust_mobile = $db->getItemFromDB("SELECT cust_mobile FROM retaline_customer WHERE cust_id = {$parentOrder['custId']}");
                    break;
                case '2'://b2b
                    $parentOrder = $db->getFromDB("SELECT bbso_SONumber AS paOrderNumber,bbso_SODate AS paOrderDate,b2b_Customer_ID as custId,b2b_Customer_Name,bbso_totInFig as totalAmt FROM retaline_B2B_SalesOrder WHERE bbso_id = {$result['fstr_id']}", true);
                    $customerName = $parentOrder['b2b_Customer_Name'];
                    $cust_mobile = '';
                    break;
            }
            $result['totalAmt'] = $parentOrder['totalAmt'];
            $result['paOrderNumber'] = $parentOrder['paOrderNumber'];
            $result['paOrderDate'] = $parentOrder['paOrderDate'];
            $result['customerName'] = $customerName;
            $result['customerNumber'] = $cust_mobile;
            $result['totalItems'] = $totalItems;
            $result['fsto_ItemQty'] = $fsto_ItemQty;
            $result['fsto_pkdQty'] = $fsto_pkdQty;
            $result['success'] = true;
            echo json_encode($result);
        }
        break;
    case 'skipincompleteOrder':
        $fsto_ordertype = $_POST['fsto_ordertype'];
        $fstoId = $_POST['fstoId'];
        $reason = $_POST['reason'];
        $db->query('begin');
        $date = date('Y-m-d H:i:s');
        $userId = $_SESSION['admin']->UserId;
        $order_skip['fsto_id'] = $fstoId;
        $order_skip['ftocl_remarks'] = $reason;
        $order_skip['ftocl_createdOn'] = $date;
        $order_skip['ftocl_createdBy'] = $userId;
        $status = $db->perform('finascop_stock_transfer_order_callskip_log', $order_skip);
        $status = $db->query("update finascop_stock_transfer_order_details_barcodes_temp set rpb_status=3 WHERE tmp_barcode_fstoId={$fstoId}");
        $status = $db->query('commit');
        if ($status == 1) {
            echo '{"success":true,"valid":true,"msg":"Data updated ."}';
            exit;
        } else {
            echo '{"success":false,"msg":"Error while saving data."}';
            exit;
        }
        break;
    case 'proceedWithAvail':
        $fsto_id = $_POST['fsto_id'];
        $data['fsto_updateon'] = date('Y-m-d H:i:s');
        $data['fsto_updateby'] = $_SESSION['admin']->UserId;
        $data['fsto_status'] = 6;
        $db->query('begin');
        $status = $db->perform('finascop_stock_transfer_order', $data, 'update', " fsto_id = {$fsto_id}");
        if ($status == 1) {
            echo '{"success":true,"valid":true,"msg":"Data updated ."}';
            exit;
        } else {
            echo '{"success":false,"msg":"Error while saving data."}';
            exit;
        }
        break;
    case 'revertIncompleteOrder':
        $fsto_id = $_POST['fsto_id'];
        $data['fsto_updateon'] = date('Y-m-d H:i:s');
        $data['fsto_updateby'] = $_SESSION['admin']->UserId;
        $data['fsto_status'] = 6;
        $data['fsto_isalreadypacked'] = 0;

        $status = $db->getItemFromDB("SELECT fsto_status FROM finascop_stock_transfer_order WHERE fsto_id = {$fsto_id}");
        if ($status != '9') {
            echo '{"success":false,"msg":"The order is not in Incomplete status."}';
            exit;
        }

        $db->query('begin');
        $status = $db->perform('finascop_stock_transfer_order', $data, 'update', " fsto_id = {$fsto_id}");
        $type = $db->getItemFromDB("SELECT fsto_ordertype FROM finascop_stock_transfer_order WHERE fsto_id = {$fsto_id}");
        $fstr_id = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$fsto_id}");
        if ($type == 1) {
            $status = $db->query("update retaline_customer_order set status_id=7 WHERE order_id={$fstr_id}");
            $order_his['order_id'] = $fstr_id;
            $order_his['order_status'] = 7;
            $order_his['created_at'] = 'now()';
            $order_his['order_action'] = "Reverted by Admin user {$_SESSION['admin']->UserName}";
            $status = $db->perform('retaline_customer_order_history', $order_his);
        } elseif ($type == 2) {
            $status = $db->query("update retaline_B2B_SalesOrder set status_id=7 WHERE bbso_id={$fstr_id}");
        } elseif ($type == 3) {
            $status = $db->query("update finascop_stock_return_request_packing set frrp_status=1 WHERE frrp_id={$fstr_id}");
        }


        $status = $db->query('commit');
        if ($status == 1) {
            echo '{"success":true,"valid":true,"msg":"Order reverted ."}';
            exit;
        } else {
            echo '{"success":false,"msg":"Error while saving data."}';
            exit;
        }
        break;
}
