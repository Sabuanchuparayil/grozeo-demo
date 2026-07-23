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

        if ($_SESSION['admin']->br_PyramidLevel == 1) {
            $where = " ";
            /*if ($_SESSION['admin']->IsSuperUser == 'Yes') {
                $where = " ";
            } else {
                $where = " AND fsto_source =" . $br_ID;
            }*/
        } else {
            $where = " AND fsto_source =" . $_SESSION['admin']->finascop_current_branch_id;
        }


        $query = "SELECT fo.fsto_id as fsto_id,fo.fsto_uid as fsto_uid,fstr_id,(SELECT SUM(fsto_ItemWeight) FROM finascop_stock_transfer_order_details fd WHERE fo.fsto_id= fd.fsto_id) as fsto_ItemWeight,"
            . "(SELECT SUM(fsto_ItemVolume) FROM finascop_stock_transfer_order_details fd WHERE fo.fsto_id= fd.fsto_id) as fsto_ItemVolume,fsto_sourcetype,fsto_destinationtype,"
            . "CASE WHEN fsto_ordertype=0 THEN '' WHEN fsto_ordertype=1 THEN (SELECT order_order_id FROM retaline_customer_order WHERE order_id = fstr_id) WHEN fsto_ordertype=2 THEN '' END as parentOrderMain,"
            . "CASE WHEN fsto_ordertype=0 THEN '' WHEN fsto_ordertype=1 THEN (SELECT SONumber FROM B2CSalesOrder WHERE customer_order_id = fstr_id LIMIT 1) WHEN fsto_ordertype=2 THEN '' END as parentOrder,"
            . "DATE_FORMAT(fsto_createdOn,'%d-%m-%y %H:%i:%s') as fsto_createdOn,(SELECT COUNT(*) from finascop_stock_transfer_order_callskip_log fcd WHERE fcd.fsto_id = fo.fsto_id) as isskipped,"
            . "(SELECT br_Name FROM finascop_branch where br_ID = fo.fsto_source) as fsto_sourceName,fsto_source,"
            . "CASE WHEN fsto_ordertype=0 THEN 'Branch Transfer' WHEN fsto_ordertype=1 THEN 'B2C' WHEN fsto_ordertype=2 THEN 'B2B' WHEN fsto_ordertype=3 THEN 'Return' WHEN fsto_ordertype=4 THEN 'Distribution' END AS fsto_ordertypeName,fsto_ordertype,"
            . "CASE WHEN fsto_type=0 THEN 'User Created' WHEN fsto_type=1 THEN 'System Created' END AS fsto_type,"
            . "(SELECT fstos_status FROM finascop_stock_transfer_order_status WHERE fstos_id = fsto_status) as fsto_statusName,fsto_status,"
            . "CASE WHEN fsto_ordertype=0 THEN (SELECT br_Name FROM finascop_branch where br_ID = fsto_destination) WHEN fsto_ordertype=1 THEN (SELECT cust_customer_name FROM retaline_customer WHERE cust_id = fsto_destination) WHEN fsto_ordertype=2 THEN '' END AS fsto_destinationName,fsto_destination,fsto_pickingNumber  FROM finascop_stock_transfer_order  fo WHERE fsto_status IN (9,20) ";
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
            . "fo.fsto_id as fsto_id,fstro_ItemMRP as mrp,fstro_ItemSPincTax as selPrce,fsto_stockValue,(SELECT stit_ConvertCalcRate FROM finascop_stock_itemmaster where stit_ID = fsto_ItemId)  as stit_ConvertCalcRate,fstro_ItemPackedSPincTax "
            . " FROM finascop_stock_transfer_order fo INNER JOIN finascop_stock_transfer_order_details fd ON fo.fsto_id = fd.fsto_id  {$search} AND fo.fsto_id={$fsto_id} ORDER BY {$sort} {$dir} ";
        $datas = $db->getMulipleData($listQry, true);
        $resCount = count($datas);
        $count = $db->getItemFromDB($countQry);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $branch = $db->getItemFromDB("SELECT mrp FROM finascop_stock_branch_inventory WHERE stit_id={$datas[$i]['fsto_ItemId']} AND branch_id={$datas[$i]['fsto_destination']}");
                $packageType = $db->getItemFromDB("SELECT least_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['fsto_ItemId']}");
                $stit_ParentItemId = $db->getItemFromDB("SELECT stit_ParentItemId FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['fsto_ItemId']}");
                //$datas[$i]['mrp'] = $branch;
                $datas[$i]['slNo'] = $i + 1;
                $datas[$i]['fsto_stockValue'] = $datas[$i]['fsto_stockValue'] . ' ' . $packageType;
                $datas[$i]['stit_ConvertCalcRate'] = ($datas[$i]['fsto_ItemQty'] * $datas[$i]['stit_ConvertCalcRate']) . ' ' . $packageType;
                if ($stit_ParentItemId > 0) {
                    $datas[$i]['diff_conversion'] = floatval($datas[$i]['fsto_stockValue']) - floatval($datas[$i]['stit_ConvertCalcRate']);
                } else {
                    $datas[$i]['diff_conversion'] = floatval($datas[$i]['fsto_ItemQty']) - floatval($datas[$i]['fsto_pkdQty']);
                }

                $datas[$i]['selPrce'] = $datas[$i]['fsto_ItemQty'] * $datas[$i]['selPrce'];
                if (floatval($datas[$i]['fstro_ItemPackedSPincTax']) != 0) {
                    $spValue_diff = floatval($datas[$i]['fstro_ItemPackedSPincTax']) - floatval($datas[$i]['selPrce']);
                } else {
                    $spValue_diff = 0;
                }

                $datas[$i]['spValue_diff'] = round($spValue_diff, 2);
            }
        }
        echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        break;
    case 'cancelInComplOrder':
        //$del_query = "DELETE FROM finascop_stock_transfer_order_details_barcodes_temp WHERE tmp_barcode_fstoId={$fsto_id} AND rpb_status=1";
        $fsto_id = $_POST['fsto_id'];
        $fsto_ordertype = $_POST['fsto_ordertypeName'];
        $fstr_id = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$fsto_id}");

        $db->query('begin');
        $date = date('Y-m-d H:i:s');
        $userId = $_SESSION['admin']->UserId;
        $statusFinascop = true;
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
                $branchName  = $db->getItemFromDB("SELECT br_Name FROM finascop_branch WHERE br_ID = {$order_branch_id} ");
                $item_product_ids = $db->getMultipleData("SELECT item_product_id,item_id,item_retail_price,item_sales_price,item_order_qty FROM retaline_customer_order_items WHERE customer_order_id = {$toParentDetails['order_id']}", true);
                $payment_mode = $db->getItemFromDB("SELECT payment_mode FROM retaline_customer_order WHERE order_id = {$toParentDetails['order_id']}");

                $status = $db->query("DELETE FROM finascop_stock_blocked WHERE order_id = {$toParentDetails['order_id']}");

                $refunds = $db->getFromDB("SELECT order_wallet_amount,total FROM retaline_customer_order WHERE order_id = {$toParentDetails['order_id']}", true);

                $refundamt = ($payment_mode == 2 || $payment_mode == 5 ? $refunds['total'] : $refunds['order_wallet_amount']);

                //$status = $db->query("UPDATE retaline_customer SET cust_walletbalance = cust_walletbalance + {$refundamt}  WHERE cust_id = {$order_customer_id}");
                //4. THe customer wallet should be updated.
                //5. An entry should be added in customer wallet transaction
                $bcwt['cust_id'] = $order_customer_id;
                $bcwt['refentry_id'] = $order_no;
                $bcwt['brcw_SourceType'] = 1;
                $bcwt['brcw_Amount'] = $refundamt;
                $bcwt['brcw_AddInfo'] = "Order " . $toParentDetails['order_order_id'] . " from " . $branchName . " Cancelled by Grozeo after clarification with customer  due to item(s) unavailability.";
                $bcwt['stiid_barcode'] = 0;
                //$bcwt['brcw_CreatedOn'] = date("Y-m-d H:i:s");
                //$bcwt['brcw_Updateon'] = date("Y-m-d H:i:s");


                $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'WALLET_API'");
                $fields = array(
                    "customer_id" => $order_customer_id,
                    "order_id" => $order_no,
                    "source_type" => 1,
                    "amount" => $refundamt,
                    "information" => $bcwt['brcw_AddInfo'],
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
                    
                    $statusFinascop = OrderCancelFinascop::OrderCancellationVoucher($toParentDetails['order_id']);
                } else {
                    echo '{"success":true,"valid":false,"msg":"Wallet Transaction Failed ."}';
                }
                //$status = $db->perform('retaline_customer_wallet_transaction', $bcwt);
                //5. An entry should be added in customer wallet transaction


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

        if ($statusFinascop) {
            $status = $db->query('commit');
        }

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
            case 'Branch Transfer':
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
            $result = $db->getFromDB("SELECT fsto_uid,fsto_createdOn,fsto_destination,(SELECT br_Name FROM finascop_branch where br_ID = fsto_source) as sourcename,fsto_ordertype,fstr_id,fsto_status,"
                . "CASE WHEN fsto_ordertype=0 THEN 'Branch Transfer' WHEN fsto_ordertype=1 THEN 'B2C' WHEN fsto_ordertype=2 THEN 'B2B' WHEN fsto_ordertype=3 THEN 'Return' WHEN fsto_ordertype=4 THEN 'Distribution' END AS fsto_ordertypeName,"
                . "(SELECT br_Name FROM finascop_branch where br_ID = fsto_destination) as branch,fsto_id"
                . " FROM finascop_stock_transfer_order WHERE fsto_id={$fsto_id}", true);
            $totalItems = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id}");
            $fsto_ItemQty = $db->getItemFromDB("SELECT SUM(fsto_ItemQty) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id}");
            $fsto_ItemQty = number_format($fsto_ItemQty, 3);
            $fsto_pkdQty = $db->getItemFromDB("SELECT SUM(fsto_ItemQty*(SELECT stit_ConvertCalcRate FROM finascop_stock_itemmaster WHERE stit_ID = fsto_ItemId)) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id}");
            $fsto_conversionpkdQty = $db->getItemFromDB("SELECT SUM(fsto_stockValue) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id}");
            switch ($result['fsto_ordertype']) {
                case '0': //cpd to branch
                    $parentOrder = $db->getFromDB("SELECT fstr_uid AS paOrderNumber,fstr_createdOn AS paOrderDate FROM finascop_stock_transfer_request WHERE fstr_id = {$result['fstr_id']}", true);
                    $customerName = $result['branch'];
                    break;
                case '1': //b2c
                    $parentOrder = $db->getFromDB("SELECT order_order_id AS paOrderNumber,order_confirm_date AS paOrderDate,order_customer_id as custId,total as totalAmt,payment_mode,order_delivery_charge,order_courier_charge FROM retaline_customer_order WHERE order_id = {$result['fstr_id']}", true);
                    $parentOrder['paOrderNumber'] = $db->getItemFromDB("SELECT SONumber FROM B2CSalesOrder WHERE customer_order_id = {$result['fstr_id']}");
                    $customerName = $db->getItemFromDB("SELECT cust_customer_name FROM retaline_customer WHERE cust_id = {$parentOrder['custId']}");
                    $cust_mobile = $db->getItemFromDB("SELECT cust_mobile FROM retaline_customer WHERE cust_id = {$parentOrder['custId']}");
                    $transferOrderData = $db->getFromDB("SELECT fstr_id,fsto_ordertype FROM finascop_stock_transfer_order WHERE fsto_id = {$fsto_id}", true);

                    $walletAmount = $db->getItemFromDB("SELECT cust_walletbalance FROM retaline_customer WHERE cust_id = {$parentOrder['custId']}");
                    $parentOrderedItems = $db->getMultipleData("SELECT * FROM retaline_customer_order_items WHERE customer_order_id = {$transferOrderData['fstr_id']}", true);
                    $currentOrderTotal = $parentOrder['totalAmt'];
                    $deliveryCharge = $parentOrder['order_delivery_charge'] + $parentOrder['order_courier_charge'];
                    $newSutotal = 0;
                    foreach ($parentOrderedItems as $parentOrderedItem) {
                        if ($result['fsto_status'] != 9) {
                            $fstro_ItemPackedSPincTax = $db->getItemFromDB("SELECT fstro_ItemPackedSPincTax FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id} AND fsto_ItemId = {$parentOrderedItem['item_product_id']}");
                        } else {
                            $fstro_ItemPackedSPincTax = $db->getItemFromDB("SELECT (fsto_pkdQty*fstro_ItemSPincTax) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id} AND fsto_ItemId = {$parentOrderedItem['item_product_id']}");
                        }

                        $newSutotal += $fstro_ItemPackedSPincTax;
                    }
                    if ($newSutotal > 0) {
                        $newOrderTotal = $newSutotal + $deliveryCharge;
                    } else {
                        $newOrderTotal = $newSutotal;
                    }

                    $newOrderTotal = round($newOrderTotal, 2);

                    $result['order_delivery_charge'] = $deliveryCharge;
                    $result['newTotal'] = $newOrderTotal;
                    $result['newSubTotal'] = $newSutotal;
                    $result['walletAmount'] = $walletAmount;
                    if (floatval($newOrderTotal) > floatval($currentOrderTotal)) {
                        $balanceToPay = floatval($newOrderTotal) - floatval($currentOrderTotal);
                    } else {
                        $balanceToPay = 0;
                    }

                    $result['balanceToPay'] = round($balanceToPay, 2);

                    switch ($parentOrder['payment_mode']) {
                        case 1:
                            $payMod = 'Pay On Delivery';
                            break;
                        case 2:
                            $payMod = 'Online Payment';
                            break;
                        case 3:
                            $payMod = 'Wallet';
                            break;
                        case 4:
                            $payMod = 'COD with Wallet';
                            break;
                        case 5:
                            $payMod = 'Online with Wallet';
                            break;
                        case 6:
                            $payMod = 'Online on Delivery';
                            break;
                        case 7:
                            $payMod = 'Cash on Delivery';
                            break;
                    }
                    $result['branch'] = $customerName;
                    break;
                case '2': //b2b
                    $parentOrder = $db->getFromDB("SELECT bbso_SONumber AS paOrderNumber,bbso_SODate AS paOrderDate,b2b_Customer_ID as custId,b2b_Customer_Name,bbso_totInFig as totalAmt FROM retaline_B2B_SalesOrder WHERE bbso_id = {$result['fstr_id']}", true);
                    $customerName = $parentOrder['b2b_Customer_Name'];
                    $cust_mobile = '';
                    break;
            }
            $skipDetail = $db->getFromDB("SELECT ftocl_createdBy,ftocl_createdOn,ftocl_remarks FROM finascop_stock_transfer_order_callskip_log WHERE fsto_id = {$fsto_id} ORDER BY ftocl_id DESC LIMIT 1", true);
            $result['ftocl_createdBy'] = $skipDetail['ftocl_createdBy'];
            if ($skipDetail['ftocl_createdBy'] > 0) {
                $result['skipDetails'] = $skipDetail['ftocl_remarks'];
                $result['skipedBy'] = $db->getItemFromDB("SELECT CONCAT(FirstName,' ',LastName) FROM finascop_usr_profile WHERE UserId = {$skipDetail['ftocl_createdBy']}");
                $result['skipedOn'] = $skipDetail['ftocl_createdOn'];
            }
            if ($parentOrder['payment_mode'] > 0) {
                $result['payment_mode'] = $parentOrder['payment_mode'];
                $result['payment_modename'] = $payMod;
            } else {
                $result['payment_mode'] = 0;
            }

            $result['totalAmt'] = $parentOrder['totalAmt'];
            $result['paOrderNumber'] = $parentOrder['paOrderNumber'];
            $result['paOrderDate'] = date('d-m-Y', strtotime($parentOrder['paOrderDate']));
            $result['customerName'] = $customerName;
            $result['customerNumber'] = $cust_mobile;
            $result['totalItems'] = $totalItems;
            $result['fsto_ItemQty'] = $fsto_ItemQty;
            $result['fsto_pkdQty'] = number_format($fsto_pkdQty, 3);
            $result['fsto_conversionpkdQty'] = $fsto_conversionpkdQty;
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


        $type = $db->getItemFromDB("SELECT fsto_ordertype FROM finascop_stock_transfer_order WHERE fsto_id = {$fsto_id}");
        $fstr_id = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$fsto_id}");

        $order_total = $db->getItemFromDB("SELECT total FROM retaline_customer_order WHERE order_id={$fstr_id}"); //16sept2022
        if ($type == 1) {
            $diffamt = $db->getItemFromDB("SELECT sum(fstro_ItemSPincTax*fsto_pkdQty) as amt FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id} ");
            $deliveryCharge = $db->getItemFromDB("SELECT order_delivery_charge FROM retaline_customer_order WHERE order_id={$fstr_id}");
            $diffamt = $diffamt + $deliveryCharge;

            if (floatval($diffamt) <= 0) {
                echo '{"success":false,"msg":"Unable to calculate new Order amount."}';
                exit;
            }

            $status = $db->query("update retaline_customer_order set status_id=7,total_afterpacking={$diffamt} WHERE order_id={$fstr_id}");
            $order_his['order_id'] = $fstr_id;
            $order_his['order_status'] = 7;
            $order_his['created_at'] = 'now()';
            $order_his['order_action'] = "Proceed with aval Qty by Admin user {$_SESSION['admin']->UserName}";
            $status = $db->perform('retaline_customer_order_history', $order_his);
            $payment_mode = $db->getItemFromDB("SELECT payment_mode FROM retaline_customer_order WHERE order_id={$fstr_id}");

            $refundamount = 0;
            switch ($payment_mode) { //13jan2023
                case 2:
                case 3:
                case 5:
                    $refundamount = $order_total - $diffamt;
                    break;
                case 1:
                case 4:
                case 6:
                case 7:
                    $order_amount_payable = $db->getItemFromDB("SELECT order_amount_payable FROM retaline_customer_order WHERE order_id={$fstr_id}");
                    if (($order_total - $diffamt) > $order_amount_payable) { //16sept2022                        
                        $refundamount = ($order_total - $diffamt) - $order_amount_payable; //16sept2022
                    }
                    if (($order_total - $diffamt) < $order_amount_payable) {
                        $order_amount_payable = ($order_total - $diffamt);
                        $status = $db->query("UPDATE retaline_customer_order SET order_amount_payable = {$order_amount_payable}  WHERE  order_id={$fstr_id}");
                    }
                    break;
            }
            /* if ($payment_mode == 2) {
                $refundamount = $order_total - $diffamt; //16sept2022
            } elseif ($payment_mode == 3) {
                $refundamount = $order_total - $diffamt; //16sept2022
            } elseif ($payment_mode == 4) {
                if (($order_total - $diffamt) > $order->order_amount_payable) {//16sept2022
                    $order_amount_payable = $db->getItemFromDB("SELECT order_amount_payable FROM retaline_customer_order WHERE order_id={$fstr_id}");
                    $refundamount = ($order_total - $diffamt) - $order_amount_payable; //16sept2022
                }
            } elseif ($payment_mode == 5) {
                $refundamount = $order_total - $diffamt; //16sept2022
            }*/
            //Wallet refund
            if ($refundamount > 0) {
                $custid = $db->getItemFromDB("SELECT order_customer_id FROM retaline_customer_order WHERE order_id={$fstr_id}");
                //$status = $db->query("UPDATE retaline_customer SET cust_walletbalance = cust_walletbalance + {$refundamount}  WHERE cust_id = {$custid}");
                $orderno = $db->getItemFromDB("SELECT order_order_id FROM retaline_customer_order WHERE order_id={$fstr_id}");
                $bcwt['cust_id'] = $custid;
                $bcwt['refentry_id'] = $fstr_id;
                $bcwt['brcw_SourceType'] = 1;
                $bcwt['brcw_Amount'] = $refundamount;
                $bcwt['stiid_barcode'] = 0;
                $bcwt['brcw_AddInfo'] = "Refund due insufficient items to deliver of order " . $orderno;
                $bcwt['brcw_CreatedOn'] = date("Y-m-d H:i:s");
                $bcwt['brcw_Updateon'] = date("Y-m-d H:i:s");
                //$status = $db->perform('retaline_customer_wallet_transaction', $bcwt);
                $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'WALLET_API'");
                $fields = array(
                    "customer_id" => $custid,
                    "order_id" => $fstr_id,
                    "source_type" => 1,
                    "amount" => $refundamount,
                    "information" => $bcwt['brcw_AddInfo'],
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
                    $status == 1;
                } else {
                    $status == 0;
                }
            }
        } elseif ($type == 2) {
            $status = $db->query("update retaline_B2B_SalesOrder set status_id=7 WHERE bbso_id={$fstr_id}");
        } elseif ($type == 3) {
            $status = $db->query("update finascop_stock_return_request_packing set frrp_status=1 WHERE frrp_id={$fstr_id}");
        }

        $status = $db->query('commit');
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
        $incompleteStatus = array('9', '20');
        //if ($status != '9' && $status != '20') {
        if (!in_array($status, $incompleteStatus)) {
            echo '{"success":false,"msg":"You cant revert this order, this is not an Incomplete order."}';
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
    case 'calculatePickedAmount':
        $fstoId = $_POST['fsto_id'];
        $transferOrderData = $db->getFromDB("SELECT fstr_id,fsto_ordertype FROM finascop_stock_transfer_order WHERE fsto_id = {$fstoId}", true);
        if ($transferOrderData['fsto_ordertype'] == 1) {
            $parentOrder = $db->getFromDB("SELECT * FROM retaline_customer_order WHERE order_id = {$transferOrderData['fstr_id']}", true);
            $walletAmount = $db->getItemFromDB("SELECT cust_walletbalance FROM retaline_customer WHERE cust_id = {$parentOrder['order_customer_id']}");
            $parentOrderedItems = $db->getMultipleData("SELECT * FROM retaline_customer_order_items WHERE customer_order_id = {$transferOrderData['fstr_id']}", true);
            $currentOrderTotal = $parentOrder['total'];
            $deliveryCharge = $parentOrder['order_delivery_charge'] + $parentOrder['order_courier_charge'];
            $newSutotal = 0;
            foreach ($parentOrderedItems as $parentOrderedItem) {
                $fstro_ItemPackedSPincTax = $db->getItemFromDB("SELECT fstro_ItemPackedSPincTax FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fstoId} AND fsto_ItemId = {$parentOrderedItem['item_product_id']}");
                $newSutotal += $fstro_ItemPackedSPincTax;
            }
            $newOrderTotal = $newSutotal + $deliveryCharge;
            $newOrderTotal = round($newOrderTotal, 2);

            $result['total'] = $currentOrderTotal;
            $result['order_delivery_charge'] = $deliveryCharge;
            $result['newTotal'] = $newOrderTotal;
            $result['newSubTotal'] = $newSutotal;
            $result['walletAmount'] = $walletAmount;
            $result['fsto_id'] = $fstoId;
            $result['orderUid'] = $parentOrder['order_order_id'];
            $result['fstr_id'] = $transferOrderData['fstr_id'];
            $result['success'] = true;
        } else {
            $result['success'] = true;
        }
        echo json_encode($result);
        break;
    case 'updatePackedAmt':
        $fsto_id = $_POST['fstoId'];
        $fstr_id = $_POST['OrderId'];
        $total = $_POST['total'];
        $newTotal = $_POST['newTotal'];
        $orderAmount = $_POST['orderAmount'];
        $orderAmountToPay = $_POST['orderAmountToPay'];
        $roundof = $newTotal - $orderAmount;
        $balancePaymentMode1 = $_POST['balancePaymentMode1'];

        $data['fsto_updateon'] = date('Y-m-d H:i:s');
        $data['fsto_updateby'] = $_SESSION['admin']->UserId;
        $data['fsto_status'] = 6;
        if ($fsto_id > 0) {
            $db->query('begin');
            $status = $db->perform('finascop_stock_transfer_order', $data, 'update', " fsto_id = {$fsto_id}");

            $type = $db->getItemFromDB("SELECT fsto_ordertype FROM finascop_stock_transfer_order WHERE fsto_id = {$fsto_id}");
            if ($type == 1) {
                if ($orderAmountToPay <= 0) {
                    $orderdetails = $db->getFromDB("SELECT order_id,order_customer_id,payment_mode,total,order_wallet_amount,payment_mode,order_order_id FROM retaline_customer_order WHERE order_id={$fstr_id}", true);
                    $newwalletEntry = abs($total - $newTotal);
                    //$status = $db->query("UPDATE retaline_customer SET cust_walletbalance = cust_walletbalance + {$newwalletEntry}  WHERE cust_id = {$orderdetails['order_customer_id']}");
                    //4. THe customer wallet should be updated.
                    //5. An entry should be added in customer wallet transaction
                    $bcwt['cust_id'] = $orderdetails['order_customer_id'];
                    $bcwt['refentry_id'] = $orderdetails['order_id'];
                    $bcwt['brcw_SourceType'] = 1;
                    $bcwt['brcw_Amount'] = $newwalletEntry;
                    $bcwt['stiid_barcode'] = 0;
                    $bcwt['brcw_CreatedOn'] = date("Y-m-d H:i:s");
                    $bcwt['brcw_Updateon'] = date("Y-m-d H:i:s");
                    // $status = $db->perform('retaline_customer_wallet_transaction', $bcwt);


                    $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'WALLET_API'");
                    $fields = array(
                        "customer_id" => $orderdetails['order_customer_id'],
                        "order_id" => $orderdetails['order_id'],
                        "source_type" => 1,
                        "amount" => $newwalletEntry,
                        "information" => "On item quantity change of order - ".$orderdetails['order_order_id'],
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
                        $status = 1;
                    } else {
                        $status = 0;
                    }
                } else {
                    $order_amount_payable = $db->getItemFromDB("SELECT order_amount_payable FROM retaline_customer_order WHERE order_id={$fstr_id}");
                    $order_roundoff = $db->getItemFromDB("SELECT order_roundoff FROM retaline_customer_order WHERE order_id={$fstr_id}");
                    if (empty($order_roundoff)) {
                        $totalround = $roundof;
                    } else {
                        $totalround = $order_roundoff + $roundof;
                    }
                    if ($order_amount_payable > 0) {
                        $totalorder_amount_payable = floatval($order_amount_payable) + floatval($orderAmountToPay);
                    } else {
                        $totalorder_amount_payable = floatval($orderAmountToPay);
                    }
                    $status = $db->query("update retaline_customer_order set status_id=7,total_afterpacking={$orderAmount},order_amount_payable = {$totalorder_amount_payable},order_roundoff = {$totalround} WHERE order_id = {$fstr_id}");
                    $order_his['order_id'] = $fstr_id;
                    $order_his['order_status'] = 7;
                    $order_his['created_at'] = 'now()';
                    $order_his['order_action'] = "Proceed with aval Qty by Admin user {$_SESSION['admin']->UserName}";
                    $status = $db->perform('retaline_customer_order_history', $order_his);
                    $payment_mode = $db->getItemFromDB("SELECT payment_mode FROM retaline_customer_order WHERE order_id={$fstr_id}");
                    $rcoep_his['rcep_order_id'] = $fstr_id;
                    $rcoep_his['rcep_total_bfrpack'] = $total;
                    $rcoep_his['rcep_total_aftrpack'] = $newTotal;
                    $rcoep_his['rcep_entered_amt'] = $orderAmount;
                    $rcoep_his['rcep_amt_roundoffbfr'] = $order_roundoff;
                    $rcoep_his['rcep_amt_roundoff'] = $newTotal - $orderAmount;
                    $rcoep_his['rcep_amt_payablebfr'] = $order_amount_payable;
                    $rcoep_his['rcep_amt_payable'] = $orderAmountToPay;
                    $rcoep_his['rcep_payment_mode'] = $balancePaymentMode1;
                    $rcoep_his['rcep_createdOn'] = date('Y-m-d H:i:s');
                    $rcoep_his['rcep_createdBy'] = $_SESSION['admin']->UserId;
                    $rcoep_his = array_filter($rcoep_his);
                    $status = $db->perform('customer_order_extra_payment_log', $rcoep_his);
                }
            } elseif ($type == 2) {
                $status = $db->query("update retaline_B2B_SalesOrder set status_id=7 WHERE bbso_id={$fstr_id}");
            } elseif ($type == 3) {
                $status = $db->query("update finascop_stock_return_request_packing set frrp_status=1 WHERE frrp_id={$fstr_id}");
            }

            $status = $db->query('commit');
        }

        if ($status == 1) {
            echo '{"success":true,"valid":true,"msg":"Data updated ."}';
            exit;
        } else {
            echo '{"success":false,"msg":"Error while saving data."}';
            exit;
        }
        break;
    case 'getOrderPicker':
        $order_branch_id = $_POST['fsto_source'];
        // $qry = $db->getMulipleData("SELECT id,name,has_open_orders,phone,is_offline from retaline_godown_boy WHERE  branch_id={$order_branch_id} ", true);
        //has_open_orders = 0 AND COALESCE(fcm_id,'') <> '' AND AND is_offline = 0 
        if ($order_branch_id > 0) {
            $countQuery = "SELECT COUNT(*) FROM retaline_godown_boy  WHERE  status = 1 and branch_id={$order_branch_id}";

            $listQuery = "SELECT id,name,has_open_orders,phone,is_offline,if(is_offline = 1,'Offline','Online') as liveStatus from retaline_godown_boy WHERE  status = 1 and branch_id={$order_branch_id} ORDER BY name ASC";

            $db->printGridJson($countQuery, $listQuery);
        }

        break;
    case 'getBranches':
        $fsto_source = $_POST['fsto_source'];
        if ($fsto_source > 0) {
            $cond = " AND br_ID = {$fsto_source}";
        } else {
            $cond = " ";
        }
        $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch where br_status = 'Active' {$cond}", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'assignOrderPicker':
        //$br_IsCPD = $db->getItemSafe("SELECT br_IsCPD FROM finascop_branch WHERE br_ID = ?", "i", [$_POST['br_ID']]);
        $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'ASSIGNEXEC'");
        $fields = array(
            "is_cpd" => 0,
            "order_id" => $_POST['order_NO'],
            "boy_id" => $_POST['id'],
            "branch_id" => $_POST['br_ID'],
            "order_pk_id" => $_POST['order_ID'],
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
        echo $data;
        break;
    case 'submitManualReplenish':
        $peItemSGriddata = json_decode(stripslashes($_POST['itemGriddata']));

        $fsto_uid = $_POST['fsto_uid'];
        $order_id = $_POST['order_id'];

        $tmp_order['fsto_manualreplenuser'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform("finascop_stock_transfer_order", $tmp_order, 'update', 'fsto_id =' . $order_id);

        $cfg_Value = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'MANUAL_REPLENISH'");
        $url = str_replace('{orderId}', $fsto_uid, $cfg_Value);


        $result['action'] = 0;
        $result['order_pk_id'] = $order_id;
        $result['order_request_id'] = '-10';

        $fields_string = json_encode($result);
        // print_r($fields_string);
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
}
