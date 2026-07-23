<?php
require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/includes/config.php');
require_once(INCLUDE_PATH . '/config.php');
require_once(EXTERNAL_LIBRARY_PATH);
require_once(INCLUDE_PATH . '/CloudFcmNotification.php');
require_once(QUGEO_API_ROOT . '/Models/QugeoScheduler.php');
require_once(QUGEO_API_ROOT . '/Models/QugeoOrderHandler.php');
require_once(QUGEO_API_ROOT . '/Models/QugeoOrderPoller.php');
require_once(QUGEO_API_ROOT . '/Models/Utils.php');

require_once(INCLUDE_PATH . "/finascop_common_functions.php");


global $db;

$userid = $_SESSION['admin']->Finascop_UserId;
switch ($op) {

    case 'listtolerance':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'rtm_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 AND type = 1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM retaline_tolerance_master  {$search}";
        $listQuery = "SELECT * FROM retaline_tolerance_master {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'saveTolerance':
        $rpgtr['type'] = 1;
        $rpgtr['rtm_value'] = $_POST['rtm_value'];
        $rpgtr['rtm_percentage'] = $_POST['rtm_percentage'];
        $count = $db->getItemSafe("SELECT COUNT(*) FROM retaline_tolerance_master WHERE rtm_value = ? and  rtm_percentage = {$rpgtr['rtm_percentage']}", "s", [$_POST['rtm_value']]);
        $db->query('begin');
        if ($count == 0) {
            $rpgtr['rtm_createdOn'] = date('Y-m-d H:i:s');
            $rpgtr['rtm_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('retaline_tolerance_master', $rpgtr);
        } else {
            echo "{success: false,msg:'Tolerance Value already added'}";
            exit();
        }
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Saved Successfully.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'setDefault':
        $rtm_id = $_POST['rtm_id'];
        $db->query('begin');
        $db->query("UPDATE retaline_tolerance_master SET rtm_default = 0 WHERE type = 1");
        $data['rtm_default'] = 1;
        $data['rtm_updatedOn'] = date("Y-m-d H:i:s");
        $data['rtm_updatedBy'] = $userid;
        $status = $db->perform('retaline_tolerance_master', $data, 'update', " rtm_id = {$rtm_id}");
        $status = $db->query('commit');
        if ($status) {
            $msg = "Saved as Default";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'getArea':

        //****** Previous Code ******//

        $query = $_POST['query'];
        if ($query != '')
            $con = " and areaName like '" . $query . "%'";
        else
            $con = '';


        $qry = "select id,areaName from area_entries " . $con . " order by areaName ";
        $branch = $db->getMulipleData($qry, true);
        if (!empty($branch)) {
            $branch = json_encode($branch);
            echo '{"data":' . $branch . '}';
        } else {
            echo '{"data":[]}';
        }


        break;
    case 'listDeficientDelivery':
        loadDeficientDeliverJobDetails();
        break;
    case 'order_details_viewdd':
        require(THIS_MODULE_PATH . "/detailView.php");
        break;
    case 'actionOnDeliveryDeficientOrder':
        $quorId = $_POST['quor_id'];
        $action = $_POST['action'];
        $newCharge = $_POST['newCharge'];
        $deficiencyValue = $_POST['deficiencyValue'];
        $partner_id = $_POST['partner_id'];
        $orderDetails = $db->getFromDB("SELECT order_id,quor_id,quor_DeliveryMethodsAllowed,order_customer_id,order_branch_id,order_order_id,br_Name,payment_mode,fsto_id,
                total,order_total_gst,order_kfc_amount,order_total_cgst,order_total_sgst,status_id
                FROM qugeo_order 
        INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status 
        INNER JOIN finascop_stock_transfer_order ON fsto_id = quor_TransferOrder_id 
        INNER JOIN retaline_customer_order ON order_id = fstr_id  
        INNER JOIN finascop_branch ON br_ID = order_branch_id 
        INNER JOIN finascop_branch_group ON store_group_id = br_storeGroup WHERE quor_id = {$quorId}", true);

        $status_id = $orderDetails['status_id'];
        $order_no = $orderDetails['order_id'];
        $date = date('Y-m-d H:i:s');
        $userId = $_SESSION['admin']->UserId;
        $newCharge = '';
        $order_customer_id = $orderDetails['order_customer_id'];
        $order_branch_id = $orderDetails['order_branch_id'];
        $order_order_id = $orderDetails['order_order_id'];
        $branch_name = $orderDetails['br_Name'];
        $item_product_ids = $db->getMultipleData("SELECT item_product_id,item_id,item_retail_price,item_sales_price,item_order_qty FROM retaline_customer_order_items WHERE customer_order_id = {$orderDetails['order_id']}", true);
        $payment_mode = $orderDetails['payment_mode'];
        $fstoId = $orderDetails['fsto_id'];
        $db->query('begin');
        if ($orderDetails['order_order_id'] > 0) {
            switch ($action) {
                case '1':

                    $nodb = new \cgoDynamiteDB();
                    $responsedata = "Merchant agreed to bear the deficiency cost of {$newCharge})";
                    $shipLog = array();
                    $shipLog['Data'] = array();
                    $uniqueId = getNewFinascopApiKey();

                    /*array_push($shipLog['Data'], array('col' => 'uuid', 'val' => $uniqueId));
                    array_push($shipLog['Data'], array('col' => 'tstamp', 'val' => $date));
                    array_push($shipLog['Data'], array('col' => 'orderID', 'val' => $order_order_id));
                    array_push($shipLog['Data'], array('col' => 'orderMethod', 'val' => 3));
                    array_push($shipLog['Data'], array('col' => 'response', 'val' => $responsedata));
                    $NewOrder = $nodb->perform('shipping_consignment_log', 'insert', $shipLog, $response);
*/

                    $order_his['order_id'] = $order_no;
                    $order_his['order_status'] = 9;
                    $order_his['created_at'] = $date;
                    $order_his['order_action'] = "Action by Admin user {$_SESSION['admin']->UserName}, {$responsedata}";
                    $qstatus = $db->perform('retaline_customer_order_history', $order_his);
                    $datafsto['fsto_status'] = 22;
                    if ($orderDetails['quor_DeliveryMethodsAllowed'] == 8)
                        $datafsto['fsto_hasShipmentCreated'] = 3;
                    $datafsto['fsto_updateon'] = $date;
                    $datafsto['fsto_updateby'] = $userId;
                    $qstatus = $db->perform('finascop_stock_transfer_order', $datafsto, 'update', 'fsto_ordertype = 1 AND fstr_id=' . $order_no);

                    $dataqgo['quor_Status'] = 22;
                    $dataqgo['quor_UpdateOn'] = $date;
                    $qstatus = $db->perform('qugeo_order', $dataqgo, 'update', 'quor_id=' . $quorId);

                    $datasr['status_id'] = 9;
                    $datasr['updated_at'] = $date;
                    $qstatus = $db->perform('retaline_customer_order', $datasr, 'update', 'order_id=' . $order_no);

                    $msg = "Merchant bear Deficiency - Shippment is on processing.";
                    break;
                case '2':
                    $qstatus = $db->query("UPDATE qugeo_order SET quor_DeliveryMethodsAllowed = 32,quor_UpdateOn = '{$date}' WHERE quor_TransferOrder_id = {$fstoId}");

                    $order_his['order_id'] = $order_no;
                    $order_his['order_status'] = 9;
                    $order_his['created_at'] = $date;
                    $order_his['order_action'] = "Moved by Admin user {$_SESSION['admin']->UserName}, Merchant manage Delivery";
                    $qstatus = $db->perform('retaline_customer_order_history', $order_his);

                    $dataqgo['quor_Status'] = 22;
                    $dataqgo['quor_UpdateOn'] = $date;
                    $qstatus = $db->perform('qugeo_order', $dataqgo, 'update', 'quor_id=' . $quorId);

                    $datasr['status_id'] = 9;
                    $datasr['updated_at'] = $date;
                    $qstatus = $db->perform('retaline_customer_order', $datasr, 'update', 'order_id=' . $order_no);
                    $msg = "Order - Merchant manage Delivery.";
                    break;
                case '3':


                    if ($fstoId > 0) {
                        $qstatus = $db->query("UPDATE qugeo_order SET quor_Status = 40,quor_UpdateOn = '{$date}' WHERE quor_TransferOrder_id = {$fstoId}");
                        $barcodes = $db->getItemFromDB("SELECT GROUP_CONCAT(stiid_barcode) FROM finascop_stock_transfer_order_details_barcodes WHERE fsto_id = {$fstoId}");
                    }

                    if (!empty($barcodes)) {
                        $amount = $db->getItemFromDB("SELECT SUM(stii_epraft) FROM finascop_stock_item_inventorydetails WHERE stiid_barcode IN ({$barcodes})");
                    }

                    if ($status_id == 7) {
                        $qstatus = $db->query("DELETE FROM finascop_stock_blocked WHERE order_id = {$order_no}");
                    } else {
                        //if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                        foreach ($item_product_ids as $item_product_id) {
                            $itemSKU = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$item_product_id['item_product_id']}");
                            $itemCountEntry = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory WHERE branch_id = {$order_branch_id} AND stit_id =  {$item_product_id['item_product_id']}");
                            $orderInvetories = $db->getFromDB("SELECT stiid_mrp,stiid_selpri,stiid_itemmasterid,fsbg_id,stiid_itemmastername,stiid_barcode FROM finascop_stock_item_inventorydetails WHERE stiid_itemmasterid = {$item_product_id['item_product_id']} AND cust_order_id = '{$order_no}'", true);
                            if ($status_id == 9 || $status_id == 55) {
                                if ($itemCountEntry > 0) {
                                    //fsbg_id
                                    $qstatus = $db->query("UPDATE finascop_stock_branch_inventory SET item_count = item_count + {$item_product_id['item_order_qty']} WHERE stit_id = {$item_product_id['item_product_id']} AND branch_id = {$order_branch_id}");
                                } else {
                                    $fsbi['stit_id'] = $item_product_id['item_product_id'];
                                    $fsbi['branch_id'] = $order_branch_id;
                                    $fsbi['item_count'] = $item_product_id['item_order_qty'];
                                    $fsbi['mrp'] = $item_product_id['item_retail_price'];
                                    $fsbi['selling_price'] = $item_product_id['item_sales_price'];
                                    $fsbi['updated_on'] = date('Y-m-d H:i:s');
                                    $status = $db->perform('finascop_stock_branch_inventory', $fsbi);

                                    $type = 'Order Cancellation - ⁠Merchant demands Cancel';
                                    $updatatLog['old_selling_price'] = NULL;
                                    $updatatLog['old_item_count'] = NULL;
                                    $updatatLog['selling_price'] = $item_product_id['item_sales_price'];
                                    $updatatLog['branch_id'] = $order_branch_id;
                                    $updatatLog['stit_id'] = $item_product_id['item_product_id'];
                                    $updatatLog['item_count'] = $item_product_id['item_order_qty'];
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
                    }


                    $order_his['order_id'] = $order_no;
                    $order_his['order_status'] = 19;
                    $order_his['created_at'] = $date;
                    $order_his['order_action'] = "Cancelled by Admin user {$_SESSION['admin']->UserName}, Merchant demands Cancel";
                    $qstatus = $db->perform('retaline_customer_order_history', $order_his);
                    $datafsto['fsto_status'] = 15;
                    $datafsto['fsto_updateon'] = $date;
                    $datafsto['fsto_updateby'] = $userId;
                    $qstatus = $db->perform('finascop_stock_transfer_order', $datafsto, 'update', 'fsto_ordertype = 1 AND fstr_id=' . $order_no);

                    $dataqgo['quor_Status'] = 40;
                    $dataqgo['quor_UpdateOn'] = $date;
                    $qstatus = $db->perform('qugeo_order', $dataqgo, 'update', 'quor_id=' . $quorId);

                    $datasr['status_id'] = 19;
                    $datasr['updated_at'] = $date;
                    $qstatus = $db->perform('retaline_customer_order', $datasr, 'update', 'order_id=' . $order_no);

                    $order_canc['customer_id'] = $order_customer_id;
                    $order_canc['order_id'] = $order_no;
                    $order_canc['reason'] = $order_his['order_action'];
                    $order_canc['cancelled_by_type'] = 2;
                    $order_canc['cancelled_by_id'] = $userId;
                    $order_canc['created_at'] = $date;
                    $qstatus = $db->perform('retaline_customer_order_cancellationdets', $order_canc);

                    $order_roundoff = $db->getItemFromDB("SELECT order_roundoff FROM retaline_customer_order WHERE order_id = {$order_no}");
                    $order_delivery_charge = $db->getItemFromDB("SELECT order_delivery_charge FROM retaline_customer_order WHERE order_id = {$order_no}");


                    switch ($payment_mode) {
                        case 2: //onlinepayment
                        case 3: //Wallet
                        case 5: //online with Wallet
                            //4. THe customer wallet should be updated.
                            $item_sales_price = $db->getItemFromDB("SELECT total FROM retaline_customer_order WHERE order_id = {$order_no}");
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
                                "information" => "Cancelled the Order {$order_order_id} from {$branch_name}.⁠Merchant demands Cancel",
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
                                "information" => "Cancelled the Order {$order_order_id} from {$branch_name}.⁠Merchant demands Cancel",
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
                    $msg = "Order cancelled succesfully";
                    break;
            }
            $qstatus = $db->query('commit');

            if ($qstatus == 1) {
                echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
            } else {
                $msg = "Error Occured";
                echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
            }
        }
        break;
    case 'updateAreaTolerance':
        $data = $_POST;
        $griddata = json_decode(stripslashes($data['data']));
        $griddata = (array) $griddata;

        $db->query('begin');
        $lpupDatat['tolerance'] = $griddata['tolerance'];
        $lpupDatat['areaUpdatedOn'] = date("Y-m-d H:i:s");
        $lpupDatat['areaUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('area_entries', $lpupDatat, 'update', " id = {$griddata['id']}");
        $status = $db->query('commit');
        if ($status) {
            $msg = "Tolerance Updated.";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'listareatolerance':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM area_entries  {$search}";
        $listQuery = "SELECT id,areaName,tolerance FROM area_entries {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
}
