<?php

require_once(INCLUDE_PATH . '/lib.php');

require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/finascop_config/config.php');
require_once(INCLUDE_PATH . '/config.php');
require_once(EXTERNAL_LIBRARY_PATH);
require_once(INCLUDE_PATH . '/CloudFcmNotification.php');
require_once(QUGEO_API_ROOT . '/Models/QugeoScheduler.php');
require_once(QUGEO_API_ROOT . '/Models/QugeoOrderHandler.php');
require_once(QUGEO_API_ROOT . '/Models/QugeoOrderPoller.php');
require_once(QUGEO_API_ROOT . '/Models/Utils.php');

require_once(INCLUDE_PATH . "/finascop_common_functions.php");
switch ($op) {

    case 'listReceiveDispatchDetails':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'dis.bcd_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['rd_id', 'order_id', 'rd_date', 'rd_status', 'rd_type'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
        $brId = $_SESSION['admin']->finascop_current_branch_id;
        if ($_SESSION['admin']->br_PyramidLevel == 1) {
            $searchitem .= " ";
           /* if ($_SESSION['admin']->IsSuperUser == 'Yes') {
                $searchitem .= " ";
            } else {
                $searchitem .= " and quor_Deliverybr_id = {$brId} ";
            }*/
        } else {
            $searchitem .= " and quor_Deliverybr_id = {$brId} ";
        }



        //$query = "SELECT quor_id,quor_RefNo,quor_Refno_Source,quor_Type,quor_TransferOrder_id,quor_Deliverybr_id from qugeo_order where quor_TransferOrder_Type = 0 and quor_Status IN (15,38) and quor_Deliverybr_id = {$brId}";
        $countQuery = "SELECT COUNT(*) FROM qugeo_order where quor_TransferOrder_Type IN(0,3) and quor_Status IN (15,38) ";
        $listQuery = "SELECT quor_id,quor_RefNo,quor_Pickupbr_id,quor_Type,quor_TransferOrder_id,quor_Deliverybr_id,quor_TransferOrder_Type from qugeo_order where quor_TransferOrder_Type IN(0,3) and quor_Status IN (15,38)    ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                if ($datas[$i]['quor_TransferOrder_Type'] == 0) {
                    //                    $fstr_requestreceived = $db->getItemFromDB("select fstr_requestreceived from finascop_stock_transfer_request inner join finascop_stock_transfer_order on
                    //        finascop_stock_transfer_request.fstr_id = finascop_stock_transfer_order.fstr_id and fsto_ordertype=0
                    //                where finascop_stock_transfer_order.fsto_id = {$datas[$i]['quor_TransferOrder_id']}");
                    $fstr_requestreceived = $db->getItemFromDB("SELECT fsto_requestreceived FROM finascop_stock_transfer_order WHERE fsto_ordertype=0 AND fsto_id = {$datas[$i]['quor_TransferOrder_id']}");
                } else {
                    $fstr_requestreceived = $db->getItemFromDB("select frrrp_requestreceived from finascop_stock_return_request_packing inner join finascop_stock_transfer_order on
        finascop_stock_return_request_packing.frrp_id = finascop_stock_transfer_order.fstr_id and fsto_ordertype=3
                where finascop_stock_transfer_order.fsto_id = {$datas[$i]['quor_TransferOrder_id']}");
                }
                $receiveDetails = $db->getFromDB("SELECT fstro_receivedOn,fstro_receivedTime FROM finascop_stock_transfer_order_details WHERE fsto_id = {$datas[$i]['quor_TransferOrder_id']} LIMIT 1", true);
                $datas[$i]['fstro_receivedOn'] = $receiveDetails['fstro_receivedOn'];
                $datas[$i]['fstro_receivedTime'] = $receiveDetails['fstro_receivedTime'];

                $datas[$i]['fstr_requestreceived'] = $fstr_requestreceived;

                if ($fstr_requestreceived == 0) {
                    $datas[$i]['fstr_requestreceivedStatus'] = 'Not Received';
                } else if ($fstr_requestreceived == 1) {
                    $datas[$i]['fstr_requestreceivedStatus'] = 'Patially Received';
                } else if ($fstr_requestreceived == 2) {
                    $datas[$i]['fstr_requestreceivedStatus'] = 'Received';
                }
                $datas[$i]['quor_Pickupbr_name'] = $db->getItemFromDB("SELECT CONCAT(br_Name ,'-',branch_shortname) FROM finascop_branch WHERE br_ID = {$datas[$i]['quor_Pickupbr_id']}");
                $datas[$i]['quor_Deliverybr_name'] = $db->getItemFromDB("SELECT CONCAT(br_Name ,'-',branch_shortname)  FROM finascop_branch WHERE br_ID = {$datas[$i]['quor_Deliverybr_id']}");
                switch ($datas[$i]['quor_Type']) {
                    case '1':
                        $datas[$i]['quor_TypeName'] = 'Drive';
                        break;
                    case '2':
                        $datas[$i]['quor_TypeName'] = 'Hired';
                        break;
                    case '3':
                        $datas[$i]['quor_TypeName'] = 'Customer Pickup';
                        break;
                    case '4':
                        $datas[$i]['quor_TypeName'] = 'Courier';
                        break;
                    case '5':
                        $datas[$i]['quor_TypeName'] = 'Driver Pickup';
                        break;
                    case '6':
                        $datas[$i]['quor_TypeName'] = 'Manually Delivered';
                        break;
                }
            }
            echo '{"totalCount":"', $resCount, '","data":' . json_encode($datas) . '}';
            //echo json_encode($qry);
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        break;

    case 'dispatchdetailsView':
        $quor_id = $_GET['quor_id'];
        if ($quor_id) {
            $quor_TransferOrder_id = $db->getItemFromDB("SELECT quor_TransferOrder_id FROM qugeo_order WHERE quor_id = {$quor_id}");
            $quor_TransferReqId = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
            //$trdetails = $db->getFromDB("");
            require(THIS_MODULE_PATH . "/receive_dispatch_iframe.php");
        }
        break;

    case 'listOrderData':
        $bcd_id = $_POST['bcd_id'];
        $cpd_id = $_SESSION['admin']->current_branch_cpdId;
        if ($cpd_id != 0) {
            $cpId = "AND cpo.cpd_id = {$cpd_id}";
        }
        //finascop_current_branch_id
        $countQuery = "SELECT 0";
        $listQuery = "SELECT order_id,order_no,order_no_last_id,cpo.cpd_id,DATE_FORMAT(bcor_createdon, '%d-%m-%Y') AS bcor_createdon,branch_id,
        CASE WHEN order_status = 0 THEN 'Created'
            WHEN order_status = 1 THEN 'Manual Queued'
            WHEN order_status = 2 THEN 'Polled'
            WHEN order_status = 3 THEN 'Assigned'
            WHEN order_status = 4 THEN 'Scanning Started'
            WHEN order_status = 5 THEN 'Incomplete Order'
            WHEN order_status = 6 THEN 'Order Completed'
            WHEN order_status = 7 THEN 'Cancelled'
            WHEN order_status = 8 THEN 'Expired'
            WHEN order_status = 9 THEN 'Dispatched'
            WHEN order_status = 10 THEN 'Partly Received'
            ELSE 'Received'
        END AS order_status,CONCAT(fcp.br_Name ,'-',fcp.branch_shortname) AS cpd_Name ,
        CONCAT(fb.br_Name ,'-',fb.branch_shortname) AS branch_name FROM retaline_branch_outward_order cpo
                    INNER JOIN finascop_branch fcp ON fcp.br_ID = cpo.cpd_id
                    INNER JOIN finascop_branch fb ON fb.br_ID = cpo.branch_id WHERE order_status IN (9,10) AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}";
        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'ReceiveDispatchDetails':
        //print_r($_POST);
        //exit();
        $db->query('begin');
        $griddata = json_decode(stripslashes($_POST['receiveItems']));
        $griddata = (array) $griddata;
        $quor_id = $_POST['quor_id'];
        $quor_TransferOrder_id = $_POST['quor_TransferOrder_id'];
        $date = date('Y-m-d', strtotime($_POST['receive_date']));
        $time = date("H:i:s", strtotime($_POST['receive_time']));
        $fsto_isPurchaseReturn = $db->getItemFromDB("SELECT fsto_isPurchaseReturn FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        $quor_TransferReqId = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        $fsto_source = $db->getItemFromDB("SELECT fsto_source FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        $fstoItemDetails = $db->getMultipleData("SELECT * FROM finascop_stock_transfer_order_details WHERE fsto_id = {$quor_TransferOrder_id}", true);
        if ($quor_id > 0) {
            //foreach ($fstoItemDetails as $fstoItemDetail) {
            for ($i = 0; $i < count($griddata); $i++) {
                if ($griddata[$i]->stii_toReceiveqty > 0) {
                    $fsto_ItemQty = $griddata[$i]->fsto_ItemQty;
                    $receiveStockItemQty = $griddata[$i]->stii_toReceiveqty;
                    $fsto_ItemId = $griddata[$i]->fsto_ItemId;
                    $fsto_id = $quor_TransferOrder_id;
                    $fsto_ItemQtyL3Received = $db->getItemFromDB("select fsto_ItemQtyL3Received from finascop_stock_transfer_order_details where fstod_id = {$griddata[$i]->fstod_id}");

                    $packageDetails = $db->getFromDB("SELECT csb_package_type_name,csb_package_type_id,cs_nos,cs_package_type_name,cs_package_type_id,ds_package_type_id,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,cos_package_type_id "
                        . "FROM finascop_stock_itemmaster WHERE stit_ID = {$fsto_ItemId}", true);
                    $itemMRP = $db->getItemFromDB("SELECT fstro_ItemMRP FROM finascop_stock_transfer_order_details WHERE fsto_id = {$quor_TransferOrder_id} AND fsto_ItemId = {$fsto_ItemId}");

                    $totalReceivedTO = intval($fsto_ItemQtyL3Received) + intval($receiveStockItemQty);
                    $balanceItem = $fsto_ItemQty - $totalReceivedTO;

                    if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                        $stii_id = $db->getItemFromDB("SELECT stii_id FROM finascop_stock_item_inventorydetails WHERE cpd_order_id = {$quor_TransferReqId} AND stiid_itemmasterid = {$fsto_ItemId} LIMIT 1");
                        $fsiinventoryData = $db->getFromDB("SELECT * FROM finascop_stock_item_inventory WHERE stii_id = {$stii_id}", TRUE);
                        $fsbg_id = $db->getItemFromDB("SELECT fsbg_id FROM finascop_stock_item_batch_group WHERE stit_ID = {$fsto_ItemId} AND fsbg_mrp = {$fsiinventoryData['stii_mrp']} AND fsbg_expirydate = '{$fsiinventoryData['stii_expirydate']}' AND fsbg_batch = '{$fsiinventoryData['stii_batch']}'");
                    }
                    $fsbgDetails = $db->getFromDB("SELECT * FROM finascop_stock_item_batch_group WHERE stit_ID = {$fsto_ItemId} AND fsbg_leastSKUmrp = {$itemMRP}", true);
                    if ($fsto_isPurchaseReturn == 0) {

                        $fstrdDetails = $db->getFromDB("SELECT fstr_ApprovedItemQty,fstr_TransferedItemQty,fstr_ReceivedItemQty,fstr_leastSKUCount FROM finascop_stock_transfer_request_details WHERE fstr_id = {$quor_TransferReqId} AND fstr_ItemId = {$fsto_ItemId}", true);
                        $totalReceivedTR = intval($fstrdDetails['fstr_ReceivedItemQty']) + intval($receiveStockItemQty);
                        if ($totalReceivedTR > $fstrdDetails['fstr_leastSKUCount']) {
                            // echo '{"success":false,"message":"Order already receieved."}';
                            //exit();
                        } else {
                            $fstrd['fstr_ReceivedItemQty'] = $totalReceivedTR;
                            $fstrd['fstrd_updatedOn'] = date('Y-m-d H:i:s');
                            $fstrd['fstrd_updatedBy'] = $_SESSION['admin']->UserId;
                            if ($totalReceivedTR == $fstrdDetails['fstr_leastSKUCount']) {
                                $fstrd['fstrd_status'] = 6;
                            } else {
                                $fstrd['fstrd_status'] = 5;
                            }
                            $status = $db->perform('finascop_stock_transfer_request_details', $fstrd, 'update', " fstr_id = {$quor_TransferReqId} AND fstr_ItemId = {$fsto_ItemId}");

                            $fstod['fsto_ItemQtyL3Received'] = $totalReceivedTO;
                            $fstod['fsto_immovableItem'] = $balanceItem;
                            $fstod['fstro_updatedOn'] = date('Y-m-d H:i:s');
                            $fstod['fstro_updatedBy'] = $_SESSION['admin']->UserId;
                            $fstod['fstro_receivedOn'] = $date;
                            $fstod['fstro_receivedTime'] = $time;
                            $fstod['fstro_receivedStatus'] = 1;
                            $status = $db->perform('finascop_stock_transfer_order_details', $fstod, 'update', " fsto_id = {$fsto_id} AND fsto_ItemId = {$fsto_ItemId}");


                            $fstrCountDetails = $db->getFromDB("SELECT SUM(fstr_leastSKUCount) as totfstr_leastSKUCount,SUM(fstr_ApprovedItemQty) as totApprovedItemQty,SUM(fstr_TransferedItemQty) as totTransferedItemQty,SUM(fstr_ReceivedItemQty) as totReceivedItemQty "
                                . "FROM finascop_stock_transfer_request_details WHERE fstrd_status <> 3 AND fstr_id = {$quor_TransferReqId}", true);
                            $toIds = $db->getItemFromDB("SELECT GROUP_CONCAT(fsto_id) FROM finascop_stock_transfer_order WHERE  fstr_id = {$quor_TransferReqId}");
                            $pkdQty = $db->getItemFromDB("SELECT SUM(fsto_pkdQty) FROM finascop_stock_transfer_order_details WHERE fsto_id IN({$toIds})");



                            $totalTods = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id}");
                            $ReceivedTods = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id} and fstro_receivedStatus = 1");
                            if ($ReceivedTods == $totalTods) {
                                $fstorder['fsto_requestreceived'] = 2;
                            } else {
                                $fstorder['fsto_requestreceived'] = 1;
                            }
                            $fstorder['fsto_updateby'] = $_SESSION['admin']->UserId;
                            $fstorder['fsto_updateon'] = date('Y-m-d H:i:s');
                            $status = $db->perform('finascop_stock_transfer_order', $fstorder, 'update', " fsto_id = {$fsto_id}");

                            $totalTos = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order WHERE  fstr_id = {$quor_TransferReqId}");
                            $ReceivedTos = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order WHERE  fstr_id = {$quor_TransferReqId} and fsto_requestreceived = 2");
                            if ($ReceivedTos == $totalTos) {
                                $fstr['fstr_requestreceived'] = 2;
                            } else {
                                $fstr['fstr_requestreceived'] = 1;
                            }
                            $fstr['fstr_updatedBy'] = $_SESSION['admin']->UserId;
                            $fstr['fstr_updatedOn'] = date('Y-m-d H:i:s');
                            $status = $db->perform('finascop_stock_transfer_request', $fstr, 'update', " fstr_id = {$quor_TransferReqId}");

                            if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {

                                $inventoryItemDetails = $db->getMultipleData("SELECT * FROM finascop_stock_item_inventorydetails WHERE cpd_order_id = {$quor_TransferReqId} AND stiid_itemmasterid = {$fsto_ItemId}", true);
                                foreach ($inventoryItemDetails as $inventoryDetail) {
                                    if ($_SESSION['admin']->br_PyramidLevel == 3) {
                                        $fsiidpts['stiid_status'] = 1;
                                    } else {
                                        $fsiidpts['stiid_status'] = 4;
                                    }
                                    $fsiidpts['cpd_branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
                                    $fsiidpts['is_branch'] = 1;
                                    $fsiidpts['stiid_updatedon'] = date('Y-m-d H:i:s');
                                    $fsiidpts['stiid_updatedby'] = $_SESSION['admin']->UserId;
                                    $fsiidstatus = $db->perform("finascop_stock_item_inventorydetails", $fsiidpts, 'update', "cpd_order_id = {$quor_TransferReqId} AND stiid_barcode = {$inventoryDetail['stiid_barcode']}");
                                    $fsiidmDatapts['stiid_id'] = $inventoryDetail['stiid_id'];
                                    $fsiidmDatapts['stiidm_itemmasterid'] = $inventoryDetail['stiid_itemmasterid'];
                                    $fsiidmDatapts['stiidm_barcode'] = $inventoryDetail['stiid_barcode'];
                                    $fsiidmDatapts['created_at'] = date('Y-m-d H:i:s');
                                    $fsiidmDatapts['stiidm_details'] = 'Received the dispatch item in the delivery order ' . $quor_TransferReqId;
                                    $fsiidmstatus = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiidmDatapts);
                                }
                            }


                            $itemCount = $db->getItemFromDB("SELECT count(*) FROM finascop_stock_branch_inventory WHERE stit_id = {$fsto_ItemId} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} AND fsbg_id = {$fsbgDetails['fsbg_id']}"); // AND fsbg_id = {$fsbg_id} 
                            if ($itemCount > 0) {
                                $fbisupd['fpod_leastSKUepr'] = $fsbgDetails['fsbg_leastSKUepr'];
                                $fbisupd['fpod_customerRateHmDel'] = $fsbgDetails['fsbg_customerRateHmDel'];
                                $fbisupd['fpod_customerRateCouDel'] = $fsbgDetails['fsbg_customerRateCouDel'];
                                $fbisupd['fpod_customerRatePikup'] = $fsbgDetails['fsbg_customerRatePikup'];
                                $fbisupd['fpod_poLandingCostleastSKU'] = $fsbgDetails['fpod_poLandingCostleastSKU'];
                                $fbisupd['fpod_poMMGleastSKU'] = $fsbgDetails['fpod_poMMGleastSKU'];
                                $fbisupd['fpod_itemleastSKUptr'] = $fsbgDetails['fsbg_itemleastSKUptr'];
                                $fbisupd['fpod_itemleastSKUpts'] = $fsbgDetails['fsbg_itemleastSKUpts'];
                                $fbisupd['fpod_leastSKUb2bCSsp'] = $fsbgDetails['fsbg_leastSKUb2bCSsp'];
                                $fbisupd['fpod_leastSKUb2bRetailsp'] = $fsbgDetails['fsbg_leastSKUb2bRetailsp'];

                                $db->query("UPDATE finascop_stock_branch_inventory SET item_count = item_count + {$receiveStockItemQty} WHERE stit_id = {$fsto_ItemId} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}  AND fsbg_id = {$fsbgDetails['fsbg_id']}");
                                $status = $db->perform('finascop_stock_branch_inventory', $fbisupd, 'update', " stit_id = {$fsto_ItemId} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}  AND fsbg_id = {$fsbgDetails['fsbg_id']}");
                            } else {
                                $fsbipts['stit_id'] = $fsto_ItemId;
                                $fsbipts['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
                                $fsbipts['item_count'] = $receiveStockItemQty;
                                $fsbipts['mrp'] = $fsbgDetails['fsbg_mrp'];
                                $fsbipts['selling_price'] = $fsbgDetails['fsbg_sellinprice'];
                                $fsbipts['updated_on'] = date('Y-m-d H:i:s');
                                $fsbipts['fsbg_id'] = $fsbgDetails['fsbg_id'];

                                $fsbipts['fpod_leastSKUmrp'] = $fsbgDetails['fsbg_leastSKUmrp'];
                                $fsbipts['fpod_leastSKUepr'] = $fsbgDetails['fsbg_leastSKUepr'];
                                $fsbipts['fpod_customerRateHmDel'] = $fsbgDetails['fsbg_customerRateHmDel'];
                                $fsbipts['fpod_customerRateCouDel'] = $fsbgDetails['fsbg_customerRateCouDel'];
                                $fsbipts['fpod_customerRatePikup'] = $fsbgDetails['fsbg_customerRatePikup'];

                                $fsbipts['fpod_itemleastSKUptr'] = $fsbgDetails['fsbg_itemleastSKUptr'];
                                $fsbipts['fpod_itemleastSKUpts'] = $fsbgDetails['fsbg_itemleastSKUpts'];

                                $fsbipts['fpod_leastSKUb2bCSsp'] = $fsbgDetails['fsbg_leastSKUb2bCSsp'];
                                $fsbipts['fpod_leastSKUb2bRetailsp'] = $fsbgDetails['fsbg_leastSKUb2bRetailsp'];

                                $fsbipts['fpod_poLandingCostleastSKU'] = $fsbgDetails['fpod_poLandingCostleastSKU'];
                                $fsbipts['fpod_poMMGleastSKU'] = $fsbgDetails['fpod_poMMGleastSKU'];

                                $db->perform('finascop_stock_branch_inventory', $fsbipts);
                            }

                            $updatatLog['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
                            $updatatLog['stit_id'] = $fsto_ItemId;
                            $updatatLog['item_count'] = $receiveStockItemQty;
                            $updatatLog['fpod_skuPurchaseQty'] = $receiveStockItemQty;
                            $updatatLog['updated_on'] = date("Y-m-d H:i:s");
                            $updatatLog['updated_by'] = $_SESSION['admin']->Finascop_UserId;
                            $updatatLog['type'] = 'On Receive';
                            $updatatLog['action'] = 'Receive Dispatch on branch - ' . $_SESSION['admin']->finascop_current_branch_id;
                            //$status = $db->perform('finascop_stock_branch_inventory_log', $updatatLog);
                            $updatatLog['selling_price'] = NULL;
                            $updatatLog['old_selling_price'] = NULL;
                            $updatatLog['fpod_skuPurchaseRange'] = NULL;
                            $updatatLog['fpod_skuAvgPurchaseRate'] = NULL;
                            $updatatLog['fpod_skuLastPurchaseRate'] = NULL;
                            $updatatLog['fpod_effectivemargin'] = NULL;

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
                    } else {

                        $fsrrpd_isReceived = $db->getItemFromDB("SELECT fsrrpd_isReceived  FROM finascop_stock_return_request_packing_details WHERE frrp_id = {$quor_TransferReqId} AND fsrrpd_ItemId = {$fsto_ItemId}");
                        if ($fsrrpd_isReceived == 1) {
                            echo '{"success":false,"message":"Item already receieved."}';
                            exit();
                        } else {
                            $datetime = date('Y-m-d H:i:s');
                            $fsrrpd['fsrrpd_isReceived'] = 1;
                            $fsrrpd['fsrrpd_updatedOn'] = date('Y-m-d H:i:s');
                            $fsrrpd['fsrrpd_updatedBy'] = $_SESSION['admin']->UserId;
                            $status = $db->perform('finascop_stock_return_request_packing_details', $fsrrpd, 'update', " frrp_id = {$quor_TransferReqId} AND fsrrpd_ItemId = {$fsto_ItemId}");

                            $fstod['fsto_ItemQtyL3Received'] = $totalReceivedTO;
                            $fstod['fstro_updatedOn'] = date('Y-m-d H:i:s');
                            $fstod['fstro_updatedBy'] = $_SESSION['admin']->UserId;
                            $fstod['fstro_receivedOn'] = $date;
                            $fstod['fstro_receivedTime'] = $time;
                            $fstod['fstro_receivedStatus'] = 1;
                            $status = $db->perform('finascop_stock_transfer_order_details', $fstod, 'update', " fsto_id = {$fsto_id} AND fsto_ItemId = {$fsto_ItemId}");
                            if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                                if ($_SESSION['admin']->br_PyramidLevel == 3) {
                                    //echo 'br_PyramidLevel3'.$_SESSION['admin']->br_PyramidLevel;
                                    $fsiidstatus = $db->query("update finascop_stock_item_inventorydetails set cpd_branch_id = {$_SESSION['admin']->finascop_current_branch_id},is_branch = 1,stiid_updatedon = '{$datetime}',"
                                        . "stiid_updatedby = {$_SESSION['admin']->UserId},stiid_status = if(stiid_status=9,8,13) where ret_packing_id = {$quor_TransferReqId} ");
                                } else {
                                    //echo 'br_PyramidLevel2'.$_SESSION['admin']->br_PyramidLevel;
                                    $fsiidstatus = $db->query("update finascop_stock_item_inventorydetails set cpd_branch_id = {$_SESSION['admin']->finascop_current_branch_id},is_branch = 1,stiid_updatedon = '{$datetime}',"
                                        . "stiid_updatedby = {$_SESSION['admin']->UserId},stiid_status = if(stiid_status=9,10,15) where ret_packing_id = {$quor_TransferReqId} ");
                                }
                                $inventoryDetails = $db->getMultipleData("SELECT * FROM finascop_stock_item_inventorydetails WHERE ret_packing_id =" . (int) $quor_TransferReqId . " AND stiid_itemmasterid = {$fsto_ItemId}", true);
                                foreach ($inventoryDetails as $inventoryDetail) {
                                    $fsiidmData['stiid_id'] = $inventoryDetail['stiid_id'];
                                    $fsiidmData['stiidm_itemmasterid'] = $inventoryDetail['stiid_itemmasterid'];
                                    $fsiidmData['stiidm_barcode'] = $inventoryDetail['stiid_barcode'];
                                    $fsiidmData['created_at'] = date('Y-m-d H:i:s');
                                    $fsiidmData['stiidm_details'] = 'Received the dispatch item in the return order ' . $quor_TransferReqId;
                                    $fsiidmstatus = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiidmData);
                                }
                            }






                            $itemsCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_return_request_packing_details WHERE frrp_id = {$quor_TransferReqId}");
                            $receivedItemCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_return_request_packing_details WHERE frrp_id = {$quor_TransferReqId} AND fsrrpd_isReceived = 1");
                            if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                                $stitIds = $db->getMultipleData("SELECT stiid_mrp as minmrp,stiid_selpri as minsp,stiid_itemmasterid,count(*) AS cnt,fsbg_id,stii_epraft,stiid_leastSKUmrp,stiid_leastSKUepr "
                                    . "FROM finascop_stock_item_inventorydetails WHERE ret_packing_id = {$quor_TransferReqId} AND stiid_itemmasterid = {$fsto_ItemId} GROUP BY stiid_itemmasterid,fsbg_id", true);
                            }


                            if ($_SESSION['admin']->br_PyramidLevel == 2) {
                                $itemCount = $db->getItemFromDB("SELECT count(*) FROM finascop_return_branch_inventory WHERE stit_id = {$fsto_ItemId} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}  AND fsbg_id = {$fsbg_id}");
                                if ($itemCount > 0) {
                                    $status = $db->query("UPDATE finascop_return_branch_inventory SET updated_on = '{$datetime}',item_count = item_count + {$receiveStockItemQty} WHERE stit_id = {$fsto_ItemId} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}  AND fsbg_id = {$fsbg_id}");
                                } else {
                                    $fsbi['stit_id'] = $fsto_ItemId;
                                    $fsbi['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
                                    $fsbi['item_count'] = $receiveStockItemQty;
                                    $fsbi['mrp'] = $fsbgDetails['fsbg_mrp'];
                                    $fsbi['selling_price'] = $fsbgDetails['fsbg_sellinprice'];
                                    $fsbi['frbi_epr'] = $fsbgDetails['fsbg_epr'];
                                    $fsbi['updated_on'] = date('Y-m-d H:i:s');
                                    $fsbi['created_at'] = date('Y-m-d H:i:s');
                                    $fsbi['fsbg_id'] = $fsbgDetails['fsbg_id'];
                                    $fsbi['frbi_leastSKUmrp'] = $fsbgDetails['fsbg_leastSKUmrp'];
                                    $fsbi['frbi_leastSKUepr'] = $fsbgDetails['fsbg_leastSKUepr'];
                                    $status = $db->perform('finascop_return_branch_inventory', $fsbi);
                                }
                            } else {
                                if ($itemsCount == $receivedItemCount) {
                                    $fsrrpData = $db->getFromDb("SELECT * FROM finascop_stock_return_request_packing where frrp_id = {$quor_TransferReqId}", true);
                                    $newfsrrqo['rtrqo_type'] = 0;
                                    $newfsrrqo['rtrqo_sourceType'] = 1;
                                    $newfsrrqo['rtrqo_isDirect'] = 2;
                                    $newfsrrqo['rtrqo_sourceBranch'] = $fsrrpData['frrp_destination'];
                                    $newfsrrqo['rtrqo_dstinationBranch'] = $db->getItemFromDB("SELECT br_cpd FROM finascop_branch WHERE br_ID = {$fsrrpData['frrp_destination']}");
                                    $newfsrrqo['rtrqo_createdOn'] = date('Y-m-d H:i:s');
                                    $newfsrrqo['rtrqo_createdBy'] = $_SESSION['admin']->UserId;
                                    $status = $db->perform("finascop_stock_return_request_order", $newfsrrqo);
                                    $newrtrqo_id = $db->insert_id();
                                    $fsrrpDataItems = $db->getMultipleData("SELECT * FROM finascop_stock_return_request_packing_details WHERE frrp_id = {$quor_TransferReqId}", true);
                                    foreach ($fsrrpDataItems as $fsrrpDataItem) {
                                        $newfsrrqodet['rtrqod_item_id'] = $fsrrpDataItem['fsrrpd_ItemId'];
                                        $newfsrrqodet['rtrqod_return_count'] = $fsrrpDataItem['fsrrpd_ItemQty'];
                                        $newfsrrqodet['rtrqod_return_damaged'] = $fsrrpDataItem['fsrrpd_ItemQty'];
                                        $newfsrrqodet['rtrqo_id'] = $newrtrqo_id;
                                        $status = $db->perform("finascop_stock_return_request_order_details", $newfsrrqodet);
                                    }
                                }
                            }


                            if ($itemsCount == $receivedItemCount) {
                                $frrp['frrrp_requestreceived'] = 2;
                            } else {
                                $frrp['frrrp_requestreceived'] = 1;
                            }

                            $frrp['frrp_updatedBy'] = $_SESSION['admin']->UserId;
                            $frrp['frrp_updatedOn'] = date('Y-m-d H:i:s');
                            $status = $db->perform('finascop_stock_return_request_packing', $frrp, 'update', " frrp_id = {$quor_TransferReqId}");

                            $totalTods = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id}");
                            $ReceivedTods = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id} and fstro_receivedStatus = 1");
                            if ($ReceivedTods == $totalTods) {
                                $fstorder['fsto_requestreceived'] = 2;
                            } else {
                                $fstorder['fsto_requestreceived'] = 1;
                            }
                            $fstorder['fsto_updateby'] = $_SESSION['admin']->UserId;
                            $fstorder['fsto_updateon'] = date('Y-m-d H:i:s');
                            $status = $db->perform('finascop_stock_transfer_order', $fstorder, 'update', " fsto_id = {$fsto_id}");
                        }
                    }
                }
            }
        }

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ' }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;
    case 'listOrderItemData':
        $quor_TransferOrder_id = $db->getItemSafe("SELECT quor_TransferOrder_id FROM qugeo_order WHERE quor_id = ?", "i", [$_POST['quor_id']]);
        $quor_TransferReqId = $db->getItemSafe("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = ?", "i", [$_POST['quor_TransferOrder_id']]);
        $fsto_id = $quor_TransferOrder_id;
        $quorder = $db->getFromDB("SELECT quor_Type,quor_QugeoDeliveryDDBOrderId,quor_QugeoDeliveryDDBDriverId FROM `qugeo_order` WHERE quor_id = {$quor_id} ", true);
        $orderDetails = $db->getFromDb("SELECT fsto_uid,fsto_createdOn,fsto_destination,(SELECT CONCAT(br_Name ,'-',branch_shortname) FROM finascop_branch where br_ID = fsto_source) as sourcename,fsto_ordertype,fstr_id,fsto_source,"
            . "CASE WHEN fsto_ordertype=0 THEN 'CPD TO BR' WHEN fsto_ordertype=1 THEN 'B2C' WHEN fsto_ordertype=2 THEN 'B2B' WHEN fsto_ordertype=3 THEN 'Return' WHEN fsto_ordertype=4 THEN 'Distribution' END AS fsto_ordertypeName,"
            . "(SELECT CONCAT(br_Name ,'-',branch_shortname) FROM finascop_branch where br_ID = fsto_destination) as branch,fsto_id"
            . " FROM finascop_stock_transfer_order WHERE fsto_id={$fsto_id}", true);
        $sourcePyramid = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch where br_ID = {$orderDetails['fsto_source']}");
        $countDetails = $db->getMultipleData("SELECT fsto_id,fsto_ItemId,fsto_ItemQtyL3Received FROM finascop_stock_transfer_order_details WHERE fsto_id={$orderDetails['fsto_id']}", true);
        $count = count($countDetails);
        if (!empty($countDetails)) {
            for ($i = 0; $i < $count; $i++) {
                //                $packageType = $db->getFromDB("SELECT cs_package_type_name,ds_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$countDetails[$i]['fsto_ItemId']}", true);
                //                if ($sourcePyramid == 2) {
                //                    $packTyp = $packageType['cs_package_type_name'];
                //                } else if ($sourcePyramid == 3) {
                //                    $packTyp = $packageType['ds_package_type_name'];
                //                } else if ($sourcePyramid == 4) {
                //                    $packTyp = $packageType['ds_package_type_name'];
                //                }
                $itemDetails = $db->getFromDb("SELECT fsto_ItemId,(SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = fsto_ItemId) as item_name,fsto_ItemQty,fsto_ItemQtyL3Received,fstro_receivedOn,fstro_receivedTime  "
                    . "FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id} AND fsto_ItemId = {$countDetails[$i]['fsto_ItemId']}", true);
                $datas[$i]['fsto_ItemId'] = $itemDetails['fsto_ItemId'];
                $datas[$i]['item_name'] = $itemDetails['item_name'];
                $datas[$i]['fsto_ItemQty'] = $itemDetails['fsto_ItemQty'];
                $datas[$i]['package_type'] = $db->getItemFromDB("SELECT least_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$countDetails[$i]['fsto_ItemId']}");
                $datas[$i]['fsto_id'] = $countDetails[$i]['fsto_id'];
                $datas[$i]['fsto_ItemQtyL3Received'] = $itemDetails['fsto_ItemQtyL3Received'];
                $datas[$i]['fstro_receivedOn'] = $itemDetails['fstro_receivedOn'];
                $datas[$i]['fstro_receivedTime'] = $itemDetails['fstro_receivedTime'];
            }

            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
            //echo json_encode($qry);
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        break;
    case 'itemStockReceiveDetailsView':
        $fsto_id = isset($_POST['fsto_id']) ? intval($_POST['fsto_id']) : 0;
        $fsto_ItemId = $_POST['fsto_ItemId'];
        $item_name = $_POST['item_name'];
        $fsto_ItemQty = $_POST['fsto_ItemQty'];

        if ($fsto_ItemId > 0) {
            $orderDetails = $db->getFromDb("SELECT fsto_uid,fsto_createdOn,fsto_destination,(SELECT CONCAT(br_Name ,'-',branch_shortname) FROM finascop_branch where br_ID = fsto_source) as sourcename,fsto_ordertype,fstr_id,fsto_source,"
                . "CASE WHEN fsto_ordertype=0 THEN 'CPD TO BR' WHEN fsto_ordertype=1 THEN 'B2C' WHEN fsto_ordertype=2 THEN 'B2B' WHEN fsto_ordertype=3 THEN 'Return' WHEN fsto_ordertype=4 THEN 'Distribution' END AS fsto_ordertypeName,"
                . "(SELECT CONCAT(br_Name ,'-',branch_shortname) FROM finascop_branch where br_ID = fsto_destination) as branch,fsto_id"
                . " FROM finascop_stock_transfer_order WHERE fsto_id={$fsto_id}", true);
            $sourcePyramid = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch where br_ID = {$orderDetails['fsto_source']}");
            $itemMRP = $db->getItemFromDb("SELECT fpod_leastSKUmrp FROM finascop_stock_branch_inventory WHERE stit_id={$fsto_ItemId} AND branch_id={$orderDetails['fsto_source']}");
            //            $packageType = $db->getFromDB("SELECT cs_package_type_name,ds_package_type_name,ccs_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$fsto_ItemId}", true);
            //            if ($sourcePyramid == 2) {
            //                $packTyp = $packageType['cs_package_type_name'];
            //            } else if ($sourcePyramid == 3) {
            //                $packTyp = $packageType['ds_package_type_name'];
            //            } else if ($sourcePyramid == 4) {
            //                $packTyp = $packageType['ds_package_type_name'];
            //            }
            $packTyp = $db->getItemFromDB("SELECT least_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$fsto_ItemId}");
            $result['item_name'] = $item_name;
            $result['fsto_ItemQty'] = $fsto_ItemQty . ' ' . $packTyp;
            $result['itemMRP'] = $itemMRP;
            $result['ccs_package_type_name'] = $packTyp;
            $result['success'] = true;
            echo json_encode($result);
        }
        break;
    case 'listReceStockEntryItemStore':
        $fsto_id - $_POST['fsto_id'];
        $fsto_ItemId = $_POST['fsto_ItemId'];
        $quor_TransferReqId = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$fsto_id}");
        $fstrdDetails = $db->getMultipleData("SELECT fstod_id,fsto_ItemId,fstro_receivedOn,fstro_receivedTime,fsto_ItemQtyL3Received FROM finascop_stock_transfer_order_details "
            . "WHERE fsto_id = {$fsto_id} AND fsto_ItemId = {$fsto_ItemId}", true);
        $count = count($fstrdDetails);
        if (!empty($fstrdDetails)) {
            for ($i = 0; $i < $count; $i++) {
                $datas[$i]['stii_id'] = $fstrdDetails[$i]['fstod_id'];
                $datas[$i]['stii_itemmastername'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$fsto_ItemId}");
                $datas[$i]['stii_qty'] = $fstrdDetails[$i]['fsto_ItemQtyL3Received'];
                $datas[$i]['fstro_receivedOn'] = $fstrdDetails[$i]['fstro_receivedOn'];
                $datas[$i]['fstro_receivedTime'] = $fstrdDetails[$i]['fstro_receivedTime'];
            }


            //echo json_encode($qry);
            //        $parentBarcodes = $db->getMultipleData("SELECT stiid_id,stiid_barcode,fstod_id from finascop_stock_transfer_order_details_barcodes where fsto_id = {$fsto_id}", true);
            //        $stiids = [];
            //        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
            //        $start = isset($_POST['start']) ? $_POST['start'] : 0;
            //        $sort = empty($sort) ? 'stii_id' : $sort;
            //        $dir = empty($dir) ? 'ASC' : $dir;
            //        $resCount = count($parentBarcodes);
            //        if (!empty($parentBarcodes)) {
            //            for ($i = 0; $i < $resCount; $i++) {
            //                $stii_idChild = $db->getItemFromDB("SELECT stii_id FROM finascop_stock_item_inventorydetails WHERE stiid_parent_barcode = {$parentBarcodes[$i]['stiid_barcode']} LIMIT 1");
            //                $inventoryData = $db->getFromDB("SELECT stii_id,
            //        CASE WHEN (stii_itemmastername = '') THEN 'Gift'
            //        ELSE stii_itemmastername
            //        END AS stii_itemmastername,stii_itemmasterid,stii_batch,stii_qty4 as retailqty,stii_isgift,DATE_FORMAT(stii_expirydate,'%d-%m-%Y') as stii_expirydate,DATE_FORMAT(stii_createdon,'%d-%m-%Y') as stii_createdon 
            //        FROM finascop_stock_item_inventory 
            //        WHERE stii_id = {$stii_idChild} ", true);
            //                $datas[$i]['stii_id'] = $inventoryData['stii_id'];
            //                $datas[$i]['stii_itemmastername'] = $inventoryData['stii_itemmastername'];
            //                $datas[$i]['stii_itemmasterid'] = $inventoryData['stii_itemmasterid'];
            //                $datas[$i]['stii_batch'] = $inventoryData['stii_batch'];
            //                $datas[$i]['stii_qty'] = $inventoryData['retailqty'];
            //                $datas[$i]['stii_isgift'] = $inventoryData['stii_isgift'];
            //                $datas[$i]['stii_expirydate'] = $inventoryData['stii_expirydate'];
            //                $datas[$i]['stii_createdon'] = $inventoryData['stii_createdon'];
            //            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else {
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        }

        break;
    case 'barcodeItemStore':
        $stiid = $_REQUEST['stii_id'];
        $fsto_id = $_REQUEST['fsto_id'];
        $data = array();
        $parentBarcodes = $db->getMultipleData("SELECT stiid_id,stiid_barcode,fstod_id from finascop_stock_transfer_order_details_barcodes where fsto_id = {$fsto_id}", true);
        $resCount = count($parentBarcodes);
        if (!empty($parentBarcodes)) {
            for ($i = 0; $i < $resCount; $i++) {
                $BCQuery = "SELECT stii_id,stiid_barcode FROM finascop_stock_item_inventorydetails WHERE stiid_parent_barcode = {$parentBarcodes[$i]['stiid_barcode']}";
                $bcDatats = $db->getMultipleData($BCQuery, true);
                foreach ($bcDatats as $bcData) {
                    array_push($data, $bcData);
                }
            }
        }
        //$countQuery = "SELECT COUNT(*) FROM finascop_stock_item_inventorydetails WHERE stii_id={$stiid}";
        //$listQuery = "SELECT stii_id,stiid_barcode FROM finascop_stock_item_inventorydetails WHERE stii_id={$stiid}";
        //$data = $db->getMultipleData($listQuery, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        // echo '{"success":true,"data":' . json_encode($data) . '}';$db->printGridJson($countQuery, $listQuery);
        break;
    case 'rePrintBarcodeOp':
        $stiidD = $_POST['stii_id'];
        $barcode = $_POST['stiid_barcode'];
        $barcodeTo = $_POST['stiid_Tobarcode'];
        ob_start();
        // $startCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_item_inventorydetails WHERE stii_id=".$_REQUEST['stii_id']." AND stiid_barcode =".$barcode);
        // $endCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_item_inventorydetails WHERE stii_id=".$_REQUEST['stii_id']." AND stiid_barcode =".$barcodeTo);
        // if($startCount==$endCount){
        include('zebra.php');
        //include('barcodeview.php');
        $resHtml = ob_get_contents();
        ob_end_clean();

        header("Content-Disposition: attachment; filename=\"" . basename("barcode.prn") . "\"");
        header("Content-Type: application/octet-stream");
        header("Connection: close");
        echo $resHtml;
        exit();
    case 'checkBarcode':
        $stiidD = $_POST['stii_id'];
        $barcode = $_POST['stiid_barcode'];
        $barcodeTo = $_POST['stiid_Tobarcode'];
        ob_start();
        $startCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_item_inventorydetails WHERE stii_id=" . $_REQUEST['stii_id'] . " AND stiid_barcode =" . $barcode);
        $endCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_item_inventorydetails WHERE stii_id=" . $_REQUEST['stii_id'] . " AND stiid_barcode =" . $barcodeTo);

        if ($startCount == $endCount) {
            echo '{"success":true}';
        } else {
            $msg = "Invalid Barcode Entered!";
            echo '{"success":false}';
            exit();
        }
        break;
    case 'listReceiveItemsStore':
        //print_r($_POST);
        $fsto_id = $_POST['quor_TransferOrder_id'];
        $quor_TransferReqId = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$fsto_id}");
        $fstrdDetails = $db->getMultipleData("SELECT fstod_id,fsto_ItemId,fstro_receivedOn,fstro_receivedTime,fsto_ItemQtyL3Received,fsto_pkdQty,fsto_ItemQty FROM finascop_stock_transfer_order_details "
            . "WHERE fsto_id = {$fsto_id} ", true);
        $count = count($fstrdDetails);
        if (!empty($fstrdDetails)) {
            for ($i = 0; $i < $count; $i++) {
                $datas[$i]['stii_id'] = $fstrdDetails[$i]['fstod_id'];
                $datas[$i]['stii_itemmastername'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$fstrdDetails[$i]['fsto_ItemId']}");
                $datas[$i]['stii_qty'] = $fstrdDetails[$i]['fsto_ItemQtyL3Received'];
                $datas[$i]['fstro_receivedOn'] = $fstrdDetails[$i]['fstro_receivedOn'];
                $datas[$i]['fstro_receivedTime'] = $fstrdDetails[$i]['fstro_receivedTime'];

                $datas[$i]['stii_packedQty'] = $fstrdDetails[$i]['fsto_pkdQty'];
                $toReceive = $fstrdDetails[$i]['fsto_pkdQty'] - $fstrdDetails[$i]['fsto_ItemQtyL3Received'];
                if ($toReceive > 0) {
                    $stii_toReceiveqty = $toReceive;
                } else {
                    $stii_toReceiveqty = 0;
                }
                $datas[$i]['stii_toReceiveqty'] = $stii_toReceiveqty;
                $datas[$i]['fstro_receivedOn'] = $fstrdDetails[$i]['fstro_receivedOn'];
                $datas[$i]['fstro_receivedTime'] = $fstrdDetails[$i]['fstro_receivedTime'];

                $datas[$i]['fsto_ItemId'] = $fstrdDetails[$i]['fsto_ItemId'];
                $datas[$i]['fstod_id'] = $fstrdDetails[$i]['fstod_id'];
                $datas[$i]['fsto_ItemQty'] = $fstrdDetails[$i]['fsto_ItemQty'];
                $datas[$i]['fsto_pkdQty'] = $fstrdDetails[$i]['fsto_pkdQty'];
                $datas[$i]['fsto_ItemQtyL3Received'] = $fstrdDetails[$i]['fsto_ItemQtyL3Received'];
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else {
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        }

        break;
    case 'ReceiveDispatchDetailsold':
        $db->query('begin');
        $quor_id = $_POST['quor_id'];
        $quor_TransferOrder_id = $_POST['quor_TransferOrder_id'];
        $date = date('Y-m-d', strtotime($_POST['receive_date']));
        $time = date("H:i:s", strtotime($_POST['receive_time']));
        $fsto_ItemQty = $_POST['fsto_ItemQty'];
        $receiveStockItemQty = $_POST['receiveStockItemQty'];
        $fsto_ItemId = $_POST['fsto_ItemId'];
        $fsto_id = $_POST['fsto_id'];
        $fsto_ItemQtyL3Received = $_POST['fsto_ItemQtyL3Received'];

        $totalReceivedTO = intval($fsto_ItemQtyL3Received) + intval($receiveStockItemQty);

        $packageDetails = $db->getFromDB("SELECT csb_package_type_name,csb_package_type_id,cs_nos,cs_package_type_name,cs_package_type_id,ds_package_type_id,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,cos_package_type_id "
            . "FROM finascop_stock_itemmaster WHERE stit_ID = {$fsto_ItemId}", true);
        $itemMRP = $db->getItemFromDB("SELECT fstro_ItemMRP FROM finascop_stock_transfer_order_details WHERE fsto_id = {$quor_TransferOrder_id} AND fsto_ItemId = {$fsto_ItemId}");

        //        print_r($_POST);
        //        exit();

        $fsto_isPurchaseReturn = $db->getItemFromDB("SELECT fsto_isPurchaseReturn FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        $quor_TransferReqId = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        $fsto_source = $db->getItemFromDB("SELECT fsto_source FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
            $stii_id = $db->getItemFromDB("SELECT stii_id FROM finascop_stock_item_inventorydetails WHERE cpd_order_id = {$quor_TransferReqId} AND stiid_itemmasterid = {$fsto_ItemId} LIMIT 1");
            $fsiinventoryData = $db->getFromDB("SELECT * FROM finascop_stock_item_inventory WHERE stii_id = {$stii_id}", TRUE);
            $fsbg_id = $db->getItemFromDB("SELECT fsbg_id FROM finascop_stock_item_batch_group WHERE stit_ID = {$fsto_ItemId} AND fsbg_mrp = {$fsiinventoryData['stii_mrp']} AND fsbg_expirydate = '{$fsiinventoryData['stii_expirydate']}' AND fsbg_batch = '{$fsiinventoryData['stii_batch']}'");
        }
        $fsbgDetails = $db->getFromDB("SELECT * FROM finascop_stock_item_batch_group WHERE stit_ID = {$fsto_ItemId} AND fsbg_leastSKUmrp = {$itemMRP}", true);
        if ($quor_id > 0) {
            if ($fsto_isPurchaseReturn == 0) {
                $fstrdDetails = $db->getFromDB("SELECT fstr_ApprovedItemQty,fstr_TransferedItemQty,fstr_ReceivedItemQty,fstr_leastSKUCount FROM finascop_stock_transfer_request_details WHERE fstr_id = {$quor_TransferReqId} AND fstr_ItemId = {$fsto_ItemId}", true);
                $totalReceivedTR = intval($fstrdDetails['fstr_ReceivedItemQty']) + intval($receiveStockItemQty);
                if ($totalReceivedTR > $fstrdDetails['fstr_leastSKUCount']) {
                    echo '{"success":false,"message":"Order already receieved."}';
                    exit();
                } else {
                    $fstrd['fstr_ReceivedItemQty'] = $totalReceivedTR;
                    $fstrd['fstrd_updatedOn'] = date('Y-m-d H:i:s');
                    $fstrd['fstrd_updatedBy'] = $_SESSION['admin']->UserId;
                    if ($totalReceivedTR == $fstrdDetails['fstr_leastSKUCount']) {
                        $fstrd['fstrd_status'] = 6;
                    } else {
                        $fstrd['fstrd_status'] = 5;
                    }
                    $status = $db->perform('finascop_stock_transfer_request_details', $fstrd, 'update', " fstr_id = {$quor_TransferReqId} AND fstr_ItemId = {$fsto_ItemId}");

                    $fstod['fsto_ItemQtyL3Received'] = $totalReceivedTO;
                    $fstod['fstro_updatedOn'] = date('Y-m-d H:i:s');
                    $fstod['fstro_updatedBy'] = $_SESSION['admin']->UserId;
                    $fstod['fstro_receivedOn'] = $date;
                    $fstod['fstro_receivedTime'] = $time;
                    $fstod['fstro_receivedStatus'] = 1;
                    $status = $db->perform('finascop_stock_transfer_order_details', $fstod, 'update', " fsto_id = {$fsto_id} AND fsto_ItemId = {$fsto_ItemId}");

                    $fstrCountDetails = $db->getFromDB("SELECT SUM(fstr_leastSKUCount) as totfstr_leastSKUCount,SUM(fstr_ApprovedItemQty) as totApprovedItemQty,SUM(fstr_TransferedItemQty) as totTransferedItemQty,SUM(fstr_ReceivedItemQty) as totReceivedItemQty "
                        . "FROM finascop_stock_transfer_request_details WHERE fstrd_status <> 3 AND fstr_id = {$quor_TransferReqId}", true);
                    $toIds = $db->getItemFromDB("SELECT GROUP_CONCAT(fsto_id) FROM finascop_stock_transfer_order WHERE  fstr_id = {$quor_TransferReqId}");
                    $pkdQty = $db->getItemFromDB("SELECT SUM(fsto_pkdQty) FROM finascop_stock_transfer_order_details WHERE fsto_id IN({$toIds})");



                    $totalTods = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id}");
                    $ReceivedTods = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id} and fstro_receivedStatus = 1");
                    if ($ReceivedTods == $totalTods) {
                        $fstorder['fsto_requestreceived'] = 2;
                    } else {
                        $fstorder['fsto_requestreceived'] = 1;
                    }
                    $fstorder['fsto_updateby'] = $_SESSION['admin']->UserId;
                    $fstorder['fsto_updateon'] = date('Y-m-d H:i:s');
                    $status = $db->perform('finascop_stock_transfer_order', $fstorder, 'update', " fsto_id = {$fsto_id}");

                    $totalTos = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order WHERE  fstr_id = {$quor_TransferReqId}");
                    $ReceivedTos = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order WHERE  fstr_id = {$quor_TransferReqId} and fsto_requestreceived = 2");
                    if ($ReceivedTos == $totalTos) {
                        $fstr['fstr_requestreceived'] = 2;
                    } else {
                        $fstr['fstr_requestreceived'] = 1;
                    }
                    $fstr['fstr_updatedBy'] = $_SESSION['admin']->UserId;
                    $fstr['fstr_updatedOn'] = date('Y-m-d H:i:s');
                    $status = $db->perform('finascop_stock_transfer_request', $fstr, 'update', " fstr_id = {$quor_TransferReqId}");

                    if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {

                        $inventoryItemDetails = $db->getMultipleData("SELECT * FROM finascop_stock_item_inventorydetails WHERE cpd_order_id = {$quor_TransferReqId} AND stiid_itemmasterid = {$fsto_ItemId}", true);
                        foreach ($inventoryItemDetails as $inventoryDetail) {
                            if ($_SESSION['admin']->br_PyramidLevel == 3) {
                                $fsiidpts['stiid_status'] = 1;
                                //$fsiidpts['purchasing_unit'] = '';
                            } else {
                                $fsiidpts['stiid_status'] = 4;
                                //$fsiidpts['purchasing_unit'] = '';
                            }
                            $fsiidpts['cpd_branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
                            $fsiidpts['is_branch'] = 1;
                            $fsiidpts['stiid_updatedon'] = date('Y-m-d H:i:s');
                            $fsiidpts['stiid_updatedby'] = $_SESSION['admin']->UserId;
                            $fsiidstatus = $db->perform("finascop_stock_item_inventorydetails", $fsiidpts, 'update', "cpd_order_id = {$quor_TransferReqId} AND stiid_barcode = {$inventoryDetail['stiid_barcode']}");
                            $fsiidmDatapts['stiid_id'] = $inventoryDetail['stiid_id'];
                            $fsiidmDatapts['stiidm_itemmasterid'] = $inventoryDetail['stiid_itemmasterid'];
                            $fsiidmDatapts['stiidm_barcode'] = $inventoryDetail['stiid_barcode'];
                            $fsiidmDatapts['created_at'] = date('Y-m-d H:i:s');
                            $fsiidmDatapts['stiidm_details'] = 'Received the dispatch item in the delivery order ' . $quor_TransferReqId;
                            $fsiidmstatus = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiidmDatapts);

                            //}
                        }
                    }


                    $itemCount = $db->getItemFromDB("SELECT count(*) FROM finascop_stock_branch_inventory WHERE stit_id = {$fsto_ItemId} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} "); // AND fsbg_id = {$fsbg_id} AND fsbg_id = {$fsbgDetails['fsbg_id']}
                    if ($itemCount > 0) {
                        $db->query("UPDATE finascop_stock_branch_inventory SET item_count = item_count + {$receiveStockItemQty} WHERE stit_id = {$fsto_ItemId} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}  AND fsbg_id = {$fsbgDetails['fsbg_id']}");
                        //$status = $db->perform('finascop_stock_branch_inventory', $fbisupd, 'update', " stit_id = {$fsto_ItemId} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}  AND fsbg_id = {$fsbgDetails['fsbg_id']}");
                    } else {
                        $fsbipts['stit_id'] = $fsto_ItemId;
                        $fsbipts['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
                        $fsbipts['item_count'] = $receiveStockItemQty;
                        $fsbipts['mrp'] = $fsbgDetails['fsbg_mrp'];
                        $fsbipts['selling_price'] = $fsbgDetails['fsbg_sellinprice'];
                        $fsbipts['updated_on'] = date('Y-m-d H:i:s');
                        $fsbipts['fsbg_id'] = $fsbgDetails['fsbg_id'];

                        $fsbipts['fpod_leastSKUmrp'] = $fsbgDetails['fsbg_leastSKUmrp'];
                        $fsbipts['fpod_leastSKUepr'] = $fsbgDetails['fsbg_leastSKUepr'];
                        $fsbipts['fpod_customerRateHmDel'] = $fsbgDetails['fsbg_customerRateHmDel'];
                        $fsbipts['fpod_customerRateCouDel'] = $fsbgDetails['fsbg_customerRateCouDel'];
                        $fsbipts['fpod_customerRatePikup'] = $fsbgDetails['fsbg_customerRatePikup'];

                        $fsbipts['fpod_itemleastSKUptr'] = $fsbgDetails['fsbg_itemleastSKUptr'];
                        $fsbipts['fpod_itemleastSKUpts'] = $fsbgDetails['fsbg_itemleastSKUpts'];

                        $fsbipts['fpod_leastSKUb2bCSsp'] = $fsbgDetails['fsbg_leastSKUb2bCSsp'];
                        $fsbipts['fpod_leastSKUb2bRetailsp'] = $fsbgDetails['fsbg_leastSKUb2bRetailsp'];

                        $fsbipts['fpod_poLandingCostleastSKU'] = $fsbgDetails['fpod_poLandingCostleastSKU'];
                        $fsbipts['fpod_poMMGleastSKU'] = $fsbgDetails['fpod_poMMGleastSKU'];

                        $db->perform('finascop_stock_branch_inventory', $fsbipts);
                    }
                }
            } else {
                $fsrrpd_isReceived = $db->getItemFromDB("SELECT fsrrpd_isReceived  FROM finascop_stock_return_request_packing_details WHERE frrp_id = {$quor_TransferReqId} AND fsrrpd_ItemId = {$fsto_ItemId}");
                if ($fsrrpd_isReceived == 1) {
                    echo '{"success":false,"message":"Item already receieved."}';
                    exit();
                } else {
                    $datetime = date('Y-m-d H:i:s');
                    $fsrrpd['fsrrpd_isReceived'] = 1;
                    $fsrrpd['fsrrpd_updatedOn'] = date('Y-m-d H:i:s');
                    $fsrrpd['fsrrpd_updatedBy'] = $_SESSION['admin']->UserId;
                    $status = $db->perform('finascop_stock_return_request_packing_details', $fsrrpd, 'update', " frrp_id = {$quor_TransferReqId} AND fsrrpd_ItemId = {$fsto_ItemId}");

                    $fstod['fsto_ItemQtyL3Received'] = $totalReceivedTO;
                    $fstod['fstro_updatedOn'] = date('Y-m-d H:i:s');
                    $fstod['fstro_updatedBy'] = $_SESSION['admin']->UserId;
                    $fstod['fstro_receivedOn'] = $date;
                    $fstod['fstro_receivedTime'] = $time;
                    $fstod['fstro_receivedStatus'] = 1;
                    $status = $db->perform('finascop_stock_transfer_order_details', $fstod, 'update', " fsto_id = {$fsto_id} AND fsto_ItemId = {$fsto_ItemId}");
                    if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                        if ($_SESSION['admin']->br_PyramidLevel == 3) {
                            //echo 'br_PyramidLevel3'.$_SESSION['admin']->br_PyramidLevel;
                            $fsiidstatus = $db->query("update finascop_stock_item_inventorydetails set cpd_branch_id = {$_SESSION['admin']->finascop_current_branch_id},is_branch = 1,stiid_updatedon = '{$datetime}',"
                                . "stiid_updatedby = {$_SESSION['admin']->UserId},stiid_status = if(stiid_status=9,8,13) where ret_packing_id = {$quor_TransferReqId} ");
                        } else {
                            //echo 'br_PyramidLevel2'.$_SESSION['admin']->br_PyramidLevel;
                            $fsiidstatus = $db->query("update finascop_stock_item_inventorydetails set cpd_branch_id = {$_SESSION['admin']->finascop_current_branch_id},is_branch = 1,stiid_updatedon = '{$datetime}',"
                                . "stiid_updatedby = {$_SESSION['admin']->UserId},stiid_status = if(stiid_status=9,10,15) where ret_packing_id = {$quor_TransferReqId} ");
                        }
                        $inventoryDetails = $db->getMultipleData("SELECT * FROM finascop_stock_item_inventorydetails WHERE ret_packing_id =" . (int) $quor_TransferReqId . " AND stiid_itemmasterid = {$fsto_ItemId}", true);
                        foreach ($inventoryDetails as $inventoryDetail) {
                            $fsiidmData['stiid_id'] = $inventoryDetail['stiid_id'];
                            $fsiidmData['stiidm_itemmasterid'] = $inventoryDetail['stiid_itemmasterid'];
                            $fsiidmData['stiidm_barcode'] = $inventoryDetail['stiid_barcode'];
                            $fsiidmData['created_at'] = date('Y-m-d H:i:s');
                            $fsiidmData['stiidm_details'] = 'Received the dispatch item in the return order ' . $quor_TransferReqId;
                            $fsiidmstatus = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiidmData);
                        }
                    }






                    $itemsCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_return_request_packing_details WHERE frrp_id = {$quor_TransferReqId}");
                    $receivedItemCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_return_request_packing_details WHERE frrp_id = {$quor_TransferReqId} AND fsrrpd_isReceived = 1");
                    if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                        $stitIds = $db->getMultipleData("SELECT stiid_mrp as minmrp,stiid_selpri as minsp,stiid_itemmasterid,count(*) AS cnt,fsbg_id,stii_epraft,stiid_leastSKUmrp,stiid_leastSKUepr "
                            . "FROM finascop_stock_item_inventorydetails WHERE ret_packing_id = {$quor_TransferReqId} AND stiid_itemmasterid = {$fsto_ItemId} GROUP BY stiid_itemmasterid,fsbg_id", true);
                    }


                    if ($_SESSION['admin']->br_PyramidLevel == 2) {
                        $itemCount = $db->getItemFromDB("SELECT count(*) FROM finascop_return_branch_inventory WHERE stit_id = {$fsto_ItemId} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}  AND fsbg_id = {$fsbg_id}");
                        if ($itemCount > 0) {
                            $status = $db->query("UPDATE finascop_return_branch_inventory SET updated_on = '{$datetime}',item_count = item_count + {$receiveStockItemQty} WHERE stit_id = {$fsto_ItemId} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}  AND fsbg_id = {$fsbg_id}");
                        } else {
                            $fsbi['stit_id'] = $fsto_ItemId;
                            $fsbi['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
                            $fsbi['item_count'] = $receiveStockItemQty;
                            $fsbi['mrp'] = $fsbgDetails['fsbg_mrp'];
                            $fsbi['selling_price'] = $fsbgDetails['fsbg_sellinprice'];
                            $fsbi['frbi_epr'] = $fsbgDetails['fsbg_epr'];
                            $fsbi['updated_on'] = date('Y-m-d H:i:s');
                            $fsbi['created_at'] = date('Y-m-d H:i:s');
                            $fsbi['fsbg_id'] = $fsbgDetails['fsbg_id'];
                            $fsbi['frbi_leastSKUmrp'] = $fsbgDetails['fsbg_leastSKUmrp'];
                            $fsbi['frbi_leastSKUepr'] = $fsbgDetails['fsbg_leastSKUepr'];
                            $status = $db->perform('finascop_return_branch_inventory', $fsbi);
                        }
                    } else {
                        if ($itemsCount == $receivedItemCount) {
                            $fsrrpData = $db->getFromDb("SELECT * FROM finascop_stock_return_request_packing where frrp_id = {$quor_TransferReqId}", true);
                            $newfsrrqo['rtrqo_type'] = 0;
                            $newfsrrqo['rtrqo_sourceType'] = 1;
                            $newfsrrqo['rtrqo_isDirect'] = 2;
                            $newfsrrqo['rtrqo_sourceBranch'] = $fsrrpData['frrp_destination'];
                            $newfsrrqo['rtrqo_dstinationBranch'] = $db->getItemFromDB("SELECT br_cpd FROM finascop_branch WHERE br_ID = {$fsrrpData['frrp_destination']}");
                            $newfsrrqo['rtrqo_createdOn'] = date('Y-m-d H:i:s');
                            $newfsrrqo['rtrqo_createdBy'] = $_SESSION['admin']->UserId;
                            $status = $db->perform("finascop_stock_return_request_order", $newfsrrqo);
                            $newrtrqo_id = $db->insert_id();
                            $fsrrpDataItems = $db->getMultipleData("SELECT * FROM finascop_stock_return_request_packing_details WHERE frrp_id = {$quor_TransferReqId}", true);
                            foreach ($fsrrpDataItems as $fsrrpDataItem) {
                                $newfsrrqodet['rtrqod_item_id'] = $fsrrpDataItem['fsrrpd_ItemId'];
                                $newfsrrqodet['rtrqod_return_count'] = $fsrrpDataItem['fsrrpd_ItemQty'];
                                $newfsrrqodet['rtrqod_return_damaged'] = $fsrrpDataItem['fsrrpd_ItemQty'];
                                $newfsrrqodet['rtrqo_id'] = $newrtrqo_id;
                                $status = $db->perform("finascop_stock_return_request_order_details", $newfsrrqodet);
                            }
                        }
                    }


                    if ($itemsCount == $receivedItemCount) {
                        $frrp['frrrp_requestreceived'] = 2;
                    } else {
                        $frrp['frrrp_requestreceived'] = 1;
                    }

                    $frrp['frrp_updatedBy'] = $_SESSION['admin']->UserId;
                    $frrp['frrp_updatedOn'] = date('Y-m-d H:i:s');
                    $status = $db->perform('finascop_stock_return_request_packing', $frrp, 'update', " frrp_id = {$quor_TransferReqId}");

                    $totalTods = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id}");
                    $ReceivedTods = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id} and fstro_receivedStatus = 1");
                    if ($ReceivedTods == $totalTods) {
                        $fstorder['fsto_requestreceived'] = 2;
                    } else {
                        $fstorder['fsto_requestreceived'] = 1;
                    }
                    $fstorder['fsto_updateby'] = $_SESSION['admin']->UserId;
                    $fstorder['fsto_updateon'] = date('Y-m-d H:i:s');
                    $status = $db->perform('finascop_stock_transfer_order', $fstorder, 'update', " fsto_id = {$fsto_id}");
                }
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ' }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;
    case 'updateItemStock':
        $quor_id = $_POST['quor_id'];
        $quor_TransferOrder_id = $_POST['quor_TransferOrder_id'];
        $fsto_isPurchaseReturn = $db->getItemFromDB("SELECT fsto_isPurchaseReturn FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        $quor_TransferReqId = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        $fsto_source = $db->getItemFromDB("SELECT fsto_source FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        $isDarkStore = $db->getItemFromDB("SELECT br_type FROM finascop_branch WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");
        if ($isDarkStore == 0) {
            if ($quor_id > 0) {
                $fstoItemDetails = $db->getMultipleData("SELECT * FROM finascop_stock_transfer_order_details WHERE fsto_id = {$quor_TransferOrder_id}", true);
                if ($fsto_isPurchaseReturn == 0) {
                    foreach ($fstoItemDetails as $fstoItemDetail) {
                        $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'UPDATECHILDSTOCK'");
                        if (!empty($url)) {
                            $fields = array(
                                "parentItem" => $fstoItemDetail['fsto_ItemId'],
                                "branch" => $_SESSION['admin']->finascop_current_branch_id
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
                        }
                    }
                }
            }
        }


        $msg = "'Stock updation processing.'";
        echo '{"success":true,"valid":true,"message":' . $msg . '}';

        break;
}
