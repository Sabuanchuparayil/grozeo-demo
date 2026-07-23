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
switch ($op) {
    case 'save':
        global $db;
        $quor_id = intval($_POST['quor_id']);
        $action = $_POST['action'];
        $failed_id = $_POST['reason_id'];
        $ispickup = $_POST['IsPickup'];
        $lastreaddate = $_POST['BkLastEditTime'];
        $current_dlsid = $_POST['dls_id'];
        $orderconcludetime = $_POST['dls_id'];
        
//        print_r($_POST);
//        exit(1);
        if ($action == 'Failed') {
            $data["quor_Status"] = $_POST['reason_id']; 
            $con = ' quor_id=' . $quor_id;
            $db->perform('qugeo_order', $data, 'update', $con);
        }
        
        $qry = "select quor_UpdateOn,quor_id,quor_RefNo from qugeo_order where quor_id =  " . $quor_id;
        $bookingtimedetails = $db->getFromDB($qry, true);
        if ($bookingtimedetails["quor_UpdateOn"] != $lastreaddate) {
            echo '{"success":false,"msg":"The Order has been edited, please re-load the Jobs and edit the details again."' . $bookingtimedetails["quor_UpdateOn"] . ' -- ' . $lastreaddate . '}';
            exit;
        }
        $bkdt = date('Y-m-d H:i:s');
        $data = array();
        if ($ispickup == '1') {
            if ($action == 'PickUp') {
                $data["quor_PickupToBeManual"] = 1;
                $dlsid = ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID;
                $data['quor_Type'] = 0;
                if ($current_dlsid <> 28) {
                    $data["quor_PickedupTime"] = $_POST['status_time'];
                }
                $data["quor_PickupConfTime"] = $_POST['confirmation_time'];
            } 
            elseif ($action == 'Failed') {
                $qry = "select quor_Status from qugeo_order where quor_id = " . $quor_id;
                $dlsid = $db->getItemFromDB($qry, true);
                
                $data["quor_DeliveredTime"] = "0000-00-00 00:00:00";
            } 
            else {
                $dlsid = ORDER_PICKUP_AT_ORIGIN_DLS_ID;
                $trsid = 0;
                $data['quor_Type'] = 0;
                $data["quor_SchedulePickupTime"] = "0000-00-00 00:00:00";
                $data["quor_PickedupTime"] = "0000-00-00 00:00:00";
                $data["quor_QugeoPickupDDBOrderId"] = "";
                $data["quor_PickupToBeManual"] = "1";
                $data["quor_PickupConfTime"] = "0000-00-00 00:00:00";
                ;
            }
        } else {
            if ($action == 'Delivery') {
                $dlsid = ORDER_DELIVERY_COMPLETED_DLS_ID;
                $trsid = 7;
                if ($current_dlsid <> 38) {
                    $data["quor_ScheduleDeliveryTime"] = $_POST['status_time'];
                }
                $data["quor_DeliveryConfTime"] = $_POST['confirmation_time'];
            } 
            elseif ($action == 'Failed') {
                $qry = "select quor_Status from qugeo_order where quor_id = " . $quor_id;
                $dlsid = $db->getItemFromDB($qry, true); 
                $data["quor_DeliveredTime"] = "0000-00-00 00:00:00";
            } 
            else {
                $dlsid = ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID;
                $trsid = 3;
                $data['quor_Type'] = 0;
                $data["quor_DeliveredTime"] = "0000-00-00 00:00:00";
                $data["quor_ScheduleDeliveryTime"] = "0000-00-00 00:00:00";
                $data["quor_DeliveryConfTime"] = "0000-00-00 00:00:00";
                $data["quor_QugeoDeliveryDDBOrderId"] = "";
                $data["quor_DeliveryToBeManual"] = "1";
            }
        }
        $data["quor_UpdateOn"] = $bkdt;
        $data["quor_Status"] = $dlsid;
        
//        print_r($data);
//        exit(1);
        $db->query('begin');
        $con = ' quor_id=' . $quor_id;
        $db->perform('qugeo_order', $data, 'update', $con);

        $qrystring = $db->getItemFromDB("SELECT quor_StatusUpdateQry FROM qugeo_order WHERE quor_id = {$quor_id}");
        $updateQueries = getQugeoParentStatusUpdated($qrystring, $dlsid);
        $updateQueries = str_replace("###6", "1", $updateQueries);
        $updateQuerys = explode(';', $updateQueries);
        foreach ($updateQuerys as $updateQuery) {
            $updateQuery = trim($updateQuery);
            if ($updateQuery != '') {
                $status = $db->query("{$updateQuery}");
            }
        }

        if ($action == 'Delivery') {
            //Get id from retaline_customer_order		
            $qry = "select order_id from retaline_customer_order where order_order_id = '" . $bookingtimedetails['quor_RefNo'] . "'";
            $orderid = $db->getItemFromDB($qry, true);

            //Get Retuned items
            $qry = "select quor_ItemReturned from qugeo_order where quor_id = " . $quor_id;
            $returneditems = $db->getItemFromDB($qry, true);
            $returnbarcodes = json_decode($returneditems);

            //Get barcode of the items delivered to the client
            //$qry = "select stiid_barcode,1 as a from retaline_customer_order_items_barcodes where customer_order_id = " . $orderid;
            $quor_TransferOrder_id = $db->getItemFromDB("SELECT quor_TransferOrder_id FROM qugeo_order WHERE quor_id = {$quor_id}");
            $is_retalineLite = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'IS_RETALINE_LITE'");
            if ($is_retalineLite != 1) {
                $qry = "SELECT stiid_barcode,1 AS a FROM finascop_stock_transfer_order_details_barcodes WHERE fsto_id = {$quor_TransferOrder_id}";
                $barcodes = $db->getMulipleData($qry, true);

                $companyMargin = 0;
                $operationMargin = 0;
                $csMargin = 0;
                $distributorMargin = 0;
                $stiid_poLandingCostleastSKU = 0;
                $totGST = 0;
                $retailorMargin = 0;
                $courierMargin = 0;
                $driverMargin = 0;
                $quor_TransferOrder_Type = -1;
                $quor_Type = -1;
                $qugeoDetails = $db->getFromDB("SELECT quor_RefNo,quor_TransferOrder_id,quor_DeliveryMethodsAllowed,quor_Type,quor_TransferOrder_Type,quor_Deliverybr_id,"
                        . "quor_AmountCollectible FROM qugeo_order WHERE quor_id = {$quor_id}", true);
                $br_PyramidLevel = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch WHERE br_ID = {$qugeoDetails['quor_Deliverybr_id']}");
                
                
                foreach ($barcodes as $barcode) {
                    if (!in_array($barcode, $returnbarcodes)) {
                        //Update the status of the barcode in the finascop_stock_item_inventorydetails
                        $db->query('update finascop_stock_item_inventorydetails set stiid_status = 6 where stiid_barcode = ' . $barcode['stiid_barcode']);
                        $qry = "select stii_id,stiid_itemmasterid from finascop_stock_item_inventorydetails where stiid_barcode = '" . $barcode['stiid_barcode'] . "'";

                        $stiiid = $db->getFromDB($qry, true);
                        //Insert into finascop_stock_item_inventorydetails_movement
                        $fsiidmData = array();
                        $fsiidmData['stiid_id'] = $stiiid['stii_id'];
                        $fsiidmData['stiidm_itemmasterid'] = $stiiid['stiid_itemmasterid'];
                        $fsiidmData['stiidm_barcode'] = $barcode['stiid_barcode'];
                        $fsiidmData['created_at'] = date('Y-m-d H:i:s');
                        $fsiidmData['stiidm_details'] = 'Delivered item in the Delivery order ' . $bookingtimedetails['quor_RefNo'];
                        $fsiidmstatus = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiidmData);


                        $barcodeDetails = $db->getFromDB("SELECT stii_epraft,stiid_itemmasterid FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = {$barcode['stiid_barcode']}", true);
                        $packageDetails = $db->getFromDB("SELECT csb_package_type_name,csb_package_type_id,cs_nos,cs_package_type_name,cs_package_type_id,ds_package_type_id,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,cos_package_type_id "
                                . "FROM finascop_stock_itemmaster WHERE stit_ID = {$barcodeDetails['stiid_itemmasterid']}", true);
                        if ($_SESSION['admin']->br_PyramidLevel == 2) {
                            $stii_epraft = $barcodeDetails['stii_epraft'] / $packageDetails['cs_nos'];
                        }
                        else if ($_SESSION['admin']->br_PyramidLevel == 3) {
                            $stii_epraft = $barcodeDetails['stii_epraft'] / ($packageDetails['ds_nos'] * $packageDetails['cs_nos']);
                        } 
                        else {
                            $stii_epraft = $barcodeDetails['stii_epraft'];
                        }

                        $poDetail = $db->getFromDB("SELECT stiid_fpoid,stiid_fpodid,stiid_itemmasterid,stiid_poLandingCostleastSKU FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = {$barcode['stiid_barcode']}", true);
                        $margins = $db->getFromDB("SELECT fpod_b2bCSgst,fpod_b2bcs_companymargin,fpod_b2bcs_opermargin,fpod_b2bcs_csmargin,
                fpod_b2bRetailgst,fpod_b2bretai_companymargin,fpod_b2bretai_opermargin,fpod_b2bretai_csmargin,fpod_b2bretai_dtrbtrmargin,
                fpod_itemptrgst,fpod_itemptr_dtrbtrmargin,fpod_itemptr_csmargin,fpod_itemptr_opermargin,fpod_itemptr_companymargin,
                fpod_itemptsgst,fpod_itempts_csmargin,fpod_itempts_opermargin,fpod_itempts_companymargin,
                fpod_companyMarginCD,fpod_incentiveMarginCD,fpod_csMarginCD,fpod_distributorMarginCD,fpod_retailorMarginCD,fpod_courierMarginCD,
                fpod_companyMarginHD,fpod_incentiveMarginHD,fpod_csMarginHD,fpod_distributorMarginHD,fpod_retailorMarginHD,fpod_driverMarginHD,
                fpod_companyMargin,fpod_incentiveMargin,fpod_csMargin,fpod_distributorMargin,fpod_retailorMargin,
                fpod_gstHmDel,fpod_gstCouDel,fpod_gstPikup 
                FROM finascop_purchase_order_details WHERE fpod_id = {$poDetail['stiid_fpodid']}", true);
                        $stit_fixedB2BRates = $db->getItemFromDB("SELECT stit_fixedB2BRates FROM finascop_stock_itemmaster WHERE stit_ID = {$poDetail['stiid_itemmasterid']}");
                        $skuNos = $db->getFromDB("SELECT cos_nos,ds_nos FROM finascop_stock_itemmaster WHERE stit_ID = {$poDetail['stiid_itemmasterid']}", true);
                        $quor_TransferOrder_Type = $qugeoDetails['quor_TransferOrder_Type'];
//quor_TransferOrder_Type : 0 - CPD2BR, 1 - B2C, 2 - B2B, 3 - Return
                        $quor_Type = $qugeoDetails['quor_Type'];
//quor_Type : 1- Drive, 2-Hired, 3-CustomerPickup ,4-Courier, 5-DriverPickup, 6-ManualDelivery   
                       
                       $stiid_poLandingCostleastSKU += $poDetail['stiid_poLandingCostleastSKU'];

                        if ($quor_TransferOrder_Type == 1) 
                        {
                            if ($quor_Type == 5) {//driver delivery
                                $companyMargin = $companyMargin + $margins['fpod_companyMarginHD'];
                                $operationMargin = $operationMargin + $margins['fpod_incentiveMarginHD'];
                                $csMargin = $csMargin + $margins['fpod_csMarginHD'];
                                $distributorMargin = $distributorMargin + $margins['fpod_distributorMarginHD'];
                                $retailorMargin = $retailorMargin + $margins['fpod_retailorMarginHD'];
                                $driverMargin = $driverMargin + $margins['fpod_driverMarginHD'];
                                $totGST = $totGST + $margins['fpod_gstHmDel'];

                            } 
                            elseif ($quor_Type == 3) {//customer pickup
                                $companyMargin = $companyMargin + $margins['fpod_companyMargin'];
                                $operationMargin = $operationMargin + $margins['fpod_incentiveMargin'];
                                $csMargin = $csMargin + $margins['fpod_csMargin'];
                                $distributorMargin = $distributorMargin + $margins['fpod_distributorMargin'];
                                $retailorMargin = $retailorMargin +
                                $margins['fpod_retailorMargin'];
                                $totGST = $totGST + $margins['fpod_gstPikup'];


                            } 
                            elseif ($quor_Type == 4) {//courier delivery
                                $companyMargin = $companyMargin + $margins['fpod_companyMarginCD'];
                                $operationMargin = $operationMargin + $margins['fpod_incentiveMarginCD'];
                                $csMargin = $csMargin + $margins['fpod_csMarginCD'];
                                $distributorMargin = $distributorMargin + $margins['fpod_distributorMarginCD'];
                                $retailorMargin = $retailorMargin + $margins['fpod_retailorMarginCD'];
                                $courierMargin = $courierMargin + $margins['fpod_courierMarginCD'];
                                $totGST = $totGST + $margins['fpod_gstCouDel'];
                            }
                        } 
                        elseif($quor_TransferOrder_Type == 2) 
                        {
                            if ($stit_fixedB2BRates == 1) {
                                if ($_SESSION['admin']->br_PyramidLevel == 2) {//b2b sales cs to distributor - Fixed pricing
                                    $companyMargin = $companyMargin + $margins['fpod_itempts_companymargin'];
                                    $operationMargin = $operationMargin + $margins['fpod_itempts_opermargin'];
                                    $csMargin = $csMargin + $margins['fpod_itempts_csmargin'];
                                    $totGST = $totGST + $margins['fpod_itemptsgst'];
                                } 
                                elseif ($_SESSION['admin']->br_PyramidLevel == 3) { // b2b sales distributor to retailer - Fixed pricing
                                    $companyMargin = $companyMargin + $margins['fpod_itemptr_companymargin'];
                                    $operationMargin = $operationMargin + $margins['fpod_itemptr_opermargin'];
                                    $csMargin = $csMargin + $margins['fpod_itemptr_csmargin'];
                                    $distributorMargin = $distributorMargin + $margins['fpod_itemptr_dtrbtrmargin'];
                                    $totGST = $totGST + $margins['fpod_itemptrgst'];
                                }
                            } 
                            elseif ($stit_fixedB2BRates == 0) {
                                if ($_SESSION['admin']->br_PyramidLevel == 2) {//b2b sales cs to distributor - margin pricing
                                    $companyMargin = $companyMargin + $margins['fpod_b2bcs_companymargin'];
                                    $operationMargin = $operationMargin + $margins['fpod_b2bcs_opermargin'];
                                    $csMargin = $csMargin + $margins['fpod_b2bcs_csmargin'];
                                    $totGST = $totGST + $margins['fpod_b2bCSgst'];
                                } 
                                elseif ($_SESSION['admin']->br_PyramidLevel == 3) {// b2b sales distributor to retailer - margin pricing
                                    $companyMargin = $companyMargin + $margins['fpod_b2bretai_companymargin'];
                                    $operationMargin = $operationMargin + $margins['fpod_b2bretai_opermargin'];
                                    $csMargin = $csMargin + $margins['fpod_b2bretai_csmargin'];
                                    $distributorMargin = $distributorMargin + $margins['fpod_b2bretai_dtrbtrmargin'];
                                    $totGST = $totGST + $margins['fpod_b2bRetailgst'];
                                }
                            }
                        }
                    }
//                    echo 'quor_TransferOrder_Type:' . $quor_TransferOrder_Type; 
//                    echo 'quor_Type:' . $quor_Type;;
//                    echo 'stiid_poLandingCostleastSKU' . $stiid_poLandingCostleastSKU;
//                    echo 'stit_fixedB2BRates' . $stit_fixedB2BRates;
//                    echo '$_SESSION[admin]->br_PyramidLevel' . $_SESSION['admin']->br_PyramidLevel;
//                    exit(1);

                }
                
        if ($quor_TransferOrder_Type == 1) 
            {
                if ($quor_Type == 5) {//driver delivery
                   
                     $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 77";
                    $wqSettings = $db->getFromDB($query, true);
                    $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                    $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                    $br_ReferenceIDs = $db->getMultipleData($br_query);
                    
                    $total = $companyMargin + $operationMargin + $csMargin + $distributorMargin + $retailorMargin + $driverMargin;
                    $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                    //$stiid_poLandingCostleastSKU
                    $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total,2);
                    $transctionTemplate['dr']['client']['key'] = $account;
                    $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU,2);
                    $transctionTemplate['cr']['sales']['br_ReferenceID'] = $br_ReferenceIDs[3][0];
                    $transctionTemplate['cr']['companyMargin']['amt'] = $companyMargin;
                    $transctionTemplate['cr']['companyMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0];
                    $transctionTemplate['cr']['operationMargin']['amt'] = $operationMargin;
                    $transctionTemplate['cr']['operationMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 
                    $transctionTemplate['cr']['csMargin']['amt'] = $csMargin;
                    $transctionTemplate['cr']['csMargin']['br_ReferenceID'] = $br_ReferenceIDs[1][0]; 
                    $transctionTemplate['cr']['distributorMargin']['amt'] = $distributorMargin;
                    $transctionTemplate['cr']['distributorMargin']['br_ReferenceID'] = $br_ReferenceIDs[2][0]; 
                    $transctionTemplate['cr']['retailerMargin']['amt'] = $retailorMargin;
                    $transctionTemplate['cr']['retailerMargin']['br_ReferenceID'] = $br_ReferenceIDs[3][0]; 
                    $transctionTemplate['cr']['driverMargin']['amt'] = $driverMargin;
                    $transctionTemplate['cr']['driverMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 

                    $search = array("#ID#", "#NO#", "#AMT#",);
                    $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'],   round($stiid_poLandingCostleastSKU + $total,2) );
                    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                    if (strcmp($transctionTemplate['comments'], '') != 0) {
                        $fields = array(
                            "waqu_TransDate" => date('Y-m-d'),
                            "waqu_comment" => $transctionTemplate['comments'],
                            "waqu_SourceID" => intval($quor_id),
                            "waqs_id" => intval($wqSettings['waqs_id']),
                            "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total,2),
                            "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                            "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                        );
                        $status = $db->perform('finascop_wallet_queue', $fields);
                    }
                    
                }
                elseif ($quor_Type == 3) {//customer pickup
                    $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 24";
                    $wqSettings = $db->getFromDB($query, true);
                    $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                    $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                    $br_ReferenceIDs = $db->getMultipleData($br_query);
                    
                    $total = $companyMargin + $operationMargin + $csMargin + $distributorMargin + $retailorMargin;
                    $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                    //$stiid_poLandingCostleastSKU
                    $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total,2);
                    $transctionTemplate['dr']['client']['key'] = $account;
                    $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU,2);
                    $transctionTemplate['cr']['sales']['br_ReferenceID'] = $br_ReferenceIDs[3][0];
                    $transctionTemplate['cr']['companyMargin']['amt'] = $companyMargin;
                    $transctionTemplate['cr']['companyMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0];
                    $transctionTemplate['cr']['operationMargin']['amt'] = $operationMargin;
                    $transctionTemplate['cr']['operationMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 
                    $transctionTemplate['cr']['csMargin']['amt'] = $csMargin;
                    $transctionTemplate['cr']['csMargin']['br_ReferenceID'] = $br_ReferenceIDs[1][0]; 
                    $transctionTemplate['cr']['distributorMargin']['amt'] = $distributorMargin;
                    $transctionTemplate['cr']['distributorMargin']['br_ReferenceID'] = $br_ReferenceIDs[2][0]; 
                    $transctionTemplate['cr']['retailerMargin']['amt'] = $retailorMargin;
                    $transctionTemplate['cr']['retailerMargin']['br_ReferenceID'] = $br_ReferenceIDs[3][0]; 
                    
                    $search = array("#ID#", "#NO#", "#AMT#",);
                    $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'],  round($stiid_poLandingCostleastSKU + $total,2)  );
                    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                    if (strcmp($transctionTemplate['comments'], '') != 0) {
                        $fields = array(
                            "waqu_TransDate" => date('Y-m-d'),
                            "waqu_comment" => $transctionTemplate['comments'],
                            "waqu_SourceID" => intval($quor_id),
                            "waqs_id" => intval($wqSettings['waqs_id']),
                            "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total,2),
                            "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                            "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                        );
                        $status = $db->perform('finascop_wallet_queue', $fields);
                    }
                } 
                elseif ($quor_Type == 4) {//courier delivery
                    
                    $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 78";
                    $wqSettings = $db->getFromDB($query, true);
                    $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                    $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                    $br_ReferenceIDs = $db->getMultipleData($br_query);
                    
                    $total = $companyMargin + $operationMargin + $csMargin + $distributorMargin + $retailorMargin +$courierMargin;
                    $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                    //$stiid_poLandingCostleastSKU
                    $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total,2);
                    $transctionTemplate['dr']['client']['key'] = $account;
                    $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU,2);
                    $transctionTemplate['cr']['sales']['br_ReferenceID'] = $br_ReferenceIDs[3][0];
                    $transctionTemplate['cr']['companyMargin']['amt'] = $companyMargin;
                    $transctionTemplate['cr']['companyMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0];
                    $transctionTemplate['cr']['operationMargin']['amt'] = $operationMargin;
                    $transctionTemplate['cr']['operationMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 
                    $transctionTemplate['cr']['csMargin']['amt'] = $csMargin;
                    $transctionTemplate['cr']['csMargin']['br_ReferenceID'] = $br_ReferenceIDs[1][0]; 
                    $transctionTemplate['cr']['distributorMargin']['amt'] = $distributorMargin;
                    $transctionTemplate['cr']['distributorMargin']['br_ReferenceID'] = $br_ReferenceIDs[2][0]; 
                    $transctionTemplate['cr']['retailerMargin']['amt'] = $retailorMargin;
                    $transctionTemplate['cr']['retailerMargin']['br_ReferenceID'] = $br_ReferenceIDs[3][0]; 
                    $transctionTemplate['cr']['courierMargin']['amt'] = $courierMargin;
                    $transctionTemplate['cr']['courierMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 

                    $search = array("#ID#", "#NO#", "#AMT#",);
                    $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'],  round($stiid_poLandingCostleastSKU + $total,2)  );
                    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                    if (strcmp($transctionTemplate['comments'], '') != 0) {
                        $fields = array(
                            "waqu_TransDate" => date('Y-m-d'),
                            "waqu_comment" => $transctionTemplate['comments'],
                            "waqu_SourceID" => intval($quor_id),
                            "waqs_id" => intval($wqSettings['waqs_id']),
                            "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total,2),
                            "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                            "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                        );
                        $status = $db->perform('finascop_wallet_queue', $fields);
                    }
                }
                
            } 
            elseif ($quor_TransferOrder_Type == 2)
            {
                if ($stit_fixedB2BRates == 1) {
                    if ($_SESSION['admin']->br_PyramidLevel == 2) {//b2b sales cs to distributor - Fixed pricing
                       
                        $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 69";
                        $wqSettings = $db->getFromDB($query, true);
                        $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                        $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                        $br_ReferenceIDs = $db->getMultipleData($br_query);

                        $total = $companyMargin + $operationMargin + $csMargin;
                        $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                        //$stiid_poLandingCostleastSKU
                        $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total,2);
                        $transctionTemplate['dr']['client']['key'] = $account;
                        $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU,2);
                        $transctionTemplate['cr']['sales']['br_ReferenceID'] = $br_ReferenceIDs[1][0];
                        $transctionTemplate['cr']['companyMargin']['amt'] = $companyMargin;
                        $transctionTemplate['cr']['companyMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0];
                        $transctionTemplate['cr']['operationMargin']['amt'] = $operationMargin;
                        $transctionTemplate['cr']['operationMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 
                        $transctionTemplate['cr']['csMargin']['amt'] = $csMargin;
                        $transctionTemplate['cr']['csMargin']['br_ReferenceID'] = $br_ReferenceIDs[1][0]; 


                        $search = array("#ID#", "#NO#", "#AMT#",);
                        $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'],  round($stiid_poLandingCostleastSKU + $total,2));
                        $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                        if (strcmp($transctionTemplate['comments'], '') != 0) {
                            $fields = array(
                                "waqu_TransDate" => date('Y-m-d'),
                                "waqu_comment" => $transctionTemplate['comments'],
                                "waqu_SourceID" => intval($quor_id),
                                "waqs_id" => intval($wqSettings['waqs_id']),
                                "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total,2),
                                "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                                "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                            );
                            $status = $db->perform('finascop_wallet_queue', $fields);
                        }
                    } 
                    elseif ($_SESSION['admin']->br_PyramidLevel == 3) {// b2b sales distributor to retailer - Fixed pricing

                        $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 70";
                        $wqSettings = $db->getFromDB($query, true);
                        $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                        $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                        $br_ReferenceIDs = $db->getMultipleData($br_query);

                        $total = $companyMargin + $operationMargin + $csMargin + $distributorMargin;
                                            $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                        //$stiid_poLandingCostleastSKU
                        $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total,2);
                        $transctionTemplate['dr']['client']['key'] = $account;
                        $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU,2);
                        $transctionTemplate['cr']['sales']['br_ReferenceID'] = $br_ReferenceIDs[2][0];
                        $transctionTemplate['cr']['companyMargin']['amt'] = $companyMargin;
                        $transctionTemplate['cr']['companyMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0];
                        $transctionTemplate['cr']['operationMargin']['amt'] = $operationMargin;
                        $transctionTemplate['cr']['operationMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 
                        $transctionTemplate['cr']['csMargin']['amt'] = $csMargin;
                        $transctionTemplate['cr']['csMargin']['br_ReferenceID'] = $br_ReferenceIDs[1][0]; 
                        $transctionTemplate['cr']['distributorMargin']['amt'] = $distributorMargin;
                        $transctionTemplate['cr']['distributorMargin']['br_ReferenceID'] = $br_ReferenceIDs[2][0]; 


                        $search = array("#ID#", "#NO#", "#AMT#",);
                        $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'],  round($stiid_poLandingCostleastSKU + $total,2) );
                        $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                        if (strcmp($transctionTemplate['comments'], '') != 0) {
                            $fields = array(
                                "waqu_TransDate" => date('Y-m-d'),
                                "waqu_comment" => $transctionTemplate['comments'],
                                "waqu_SourceID" => intval($quor_id),
                                "waqs_id" => intval($wqSettings['waqs_id']),
                                "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total,2),
                                "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                                "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                            );
                            $status = $db->perform('finascop_wallet_queue', $fields);
                        }

                    }
                } 
                elseif ($stit_fixedB2BRates == 0) {
                    if ($_SESSION['admin']->br_PyramidLevel == 2) {//b2b sales cs to distributor - margin pricing
                        $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 69";
                        $wqSettings = $db->getFromDB($query, true);
                        $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                        $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                        $br_ReferenceIDs = $db->getMultipleData($br_query);

                        $total = $companyMargin + $operationMargin + $csMargin;
                        $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                        //$stiid_poLandingCostleastSKU
                        $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total,2);
                        $transctionTemplate['dr']['client']['key'] = $account;
                        $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU,2);
                        $transctionTemplate['cr']['sales']['br_ReferenceID'] = $br_ReferenceIDs[1][0];
                        $transctionTemplate['cr']['companyMargin']['amt'] = $companyMargin;
                        $transctionTemplate['cr']['companyMargin']['br_ReferenceID'] = $br_ReferenceIDs[1][0];
                        $transctionTemplate['cr']['operationMargin']['amt'] = $operationMargin;
                        $transctionTemplate['cr']['operationMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 
                        $transctionTemplate['cr']['csMargin']['amt'] = $csMargin;
                        $transctionTemplate['cr']['csMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 


                        $search = array("#ID#", "#NO#", "#AMT#",);
                        $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'],  round($stiid_poLandingCostleastSKU + $total,2) );
                        $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                        if (strcmp($transctionTemplate['comments'], '') != 0) {
                            $fields = array(
                                "waqu_TransDate" => date('Y-m-d'),
                                "waqu_comment" => $transctionTemplate['comments'],
                                "waqu_SourceID" => intval($quor_id),
                                "waqs_id" => intval($wqSettings['waqs_id']),
                                "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total,2),
                                "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                                "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                            );
                            $status = $db->perform('finascop_wallet_queue', $fields);
                        }
                    } 
                    elseif ($_SESSION['admin']->br_PyramidLevel == 3) {// b2b sales distributor to retailer - margin pricing
$query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 70";
                        $wqSettings = $db->getFromDB($query, true);
                        $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                        $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                        $br_ReferenceIDs = $db->getMultipleData($br_query);
                        

                        $total = $companyMargin + $operationMargin + $csMargin + $distributorMargin;
                        $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                        //$stiid_poLandingCostleastSKU
                        $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total,2);
                        $transctionTemplate['dr']['client']['key'] = $account;
                        $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU,2);
                        $transctionTemplate['cr']['sales']['br_ReferenceID'] = $br_ReferenceIDs[2][0];
                        $transctionTemplate['cr']['companyMargin']['amt'] = $companyMargin;
                        $transctionTemplate['cr']['companyMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0];
                        $transctionTemplate['cr']['operationMargin']['amt'] = $operationMargin;
                        $transctionTemplate['cr']['operationMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 
                        $transctionTemplate['cr']['csMargin']['amt'] = $csMargin;
                        $transctionTemplate['cr']['csMargin']['br_ReferenceID'] = $br_ReferenceIDs[1][0]; 
                        $transctionTemplate['cr']['distributorMargin']['amt'] = $distributorMargin;
                        $transctionTemplate['cr']['distributorMargin']['br_ReferenceID'] = $br_ReferenceIDs[2][0]; 


                        $search = array("#ID#", "#NO#", "#AMT#",);
                        $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'],  round($stiid_poLandingCostleastSKU + $total,2)  );
                        $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                        if (strcmp($transctionTemplate['comments'], '') != 0) {
                            
                            $fields = array(
                                "waqu_TransDate" => date('Y-m-d'),
                                "waqu_comment" => $transctionTemplate['comments'],
                                "waqu_SourceID" => intval($quor_id),
                                "waqs_id" => intval($wqSettings['waqs_id']),
                                "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total,2),
                                "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                                "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                            );
                            $status = $db->perform('finascop_wallet_queue', $fields);
                        }
                    }
                }
            }
            }

            //UPdate Return
            $qry = "select coalesce(quor_ItemReturned,'') as ss   from qugeo_order where quor_id = " . $quor_id;
            $return_items = $db->getItemFromDB($qry, true);

            $updateurl = $db->getItemFromDb("select quor_ItemReturnUpdate from qugeo_order where quor_id = " . $quor_id, true);
            $updateurl = str_replace("##13", $return_items, $updateurl);
            //$db->query($updateurl);
            // $db->query('update retaline_customer_order set status_id = ' . QUGEO_TO_CUSTOMER_ORDER_STATUS_DELIVERD_CONFIRM . ' where order_id = ' . $orderid);
            //$db->query("INSERT INTO retaline_customer_order_history(order_id, order_status, created_at, updated_at) VALUES(" . $orderid . ", " . QUGEO_TO_CUSTOMER_ORDER_STATUS_DELIVERD_CONFIRM . ", NOW(), NOW())");
        }
        if ($action == 'Failed' && $ispickup == '0') {
            $qry = "select order_id from retaline_customer_order where order_order_id = '" . $bookingtimedetails['quor_RefNo'] . "'";
            $orderid = $db->getItemFromDB($qry, true);
            $qry = "select coalesce(quor_ItemReturned,'') as ss   from qugeo_order where quor_id = " . $quor_id;
            $return_items = $db->getItemFromDB($qry, true);

            //UPdate Return
            $updateurl = $db->getItemFromDb("select quor_ItemReturnUpdate from qugeo_order where quor_id = " . $quor_id, true);
            $updateurl = str_replace("##13", $return_items, $updateurl);
            //$db->query($updateurl);
            //$db->query('update retaline_customer_order set status_id = ' . QUGEO_TO_CUSTOMER_ORDER_STATUS_DELIVERY_FAILED . ' where order_id = ' . $orderid);
            //$db->query("INSERT INTO retaline_customer_order_history(order_id, order_status, created_at, updated_at) VALUES(" . $orderid . ", " . QUGEO_TO_CUSTOMER_ORDER_STATUS_DELIVERY_FAILED . ", NOW(), NOW())");
        }

        $db->query('commit');
        echo '{"success":true,"msg":"Updated the Job"}';
        break;
    /* Branch combo store */
    case 'getbranchStore':
        $query = $_POST['query'];
        if ($query != '')
            $con = " and br_Name like '" . $query . "%'";
        else
            $con = '';


        $qry = "select br_ID,br_Name from finascop_branch where br_status = 'Active' " . $con . " order by br_Name ";
        $branch = $db->getMulipleData($qry, true);
        if (!empty($branch)) {
            $branch = json_encode($branch);
            echo '{"data":' . $branch . '}';
        } else {
            echo '{"data":[]}';
        }
        break;
    /* Combo store for vehicle */
    case 'getVehicleStore':
        loadVehicleDetails();
        break;

    /* Op for grid listing */
    case 'listJobGrid':

        loadJobdetails();
        break;

    /* Combo store for reasons */
    case 'getReasons':
        $ispickup = $_POST['IsPickup'];
        if ($ispickup == '1') {
            $qry = "select dls_DelStatus as reason_txt,dls_ID as reason_id  from qugeo_deliverystatus where dls_ID in (30,35,36,37)";
        } else {
            $qry = "select dls_DelStatus as reason_txt,dls_ID as reason_id  from qugeo_deliverystatus where dls_ID in (10,11,12,13,14)";
        }
        $status = $db->getMulipleData($qry, true);
        if (!empty($status)) {
            echo '{"data":' . json_encode($status) . '}';
        } else {
            echo '{"data":[]}';
        }
        break;

    case 'job_confirmation':
        $data = $_POST;

        echo '{"success":false,"msg":"no op"}';
        break;
    case 'listbarcodesinQreturn':
        $quor_id = $_POST['quor_id'];
        $qry = "select quor_ItemReturned from qugeo_order where quor_id = " . $quor_id;
        $returneditems = $db->getItemFromDB($qry, true);
        $returnbarcodes = json_decode($returneditems);
        $resCount = count($returnbarcodes);
        if ($resCount > 0) {
            for ($i = 0; $i < $resCount; $i++) {
                $datas[$i]['stiid_barcode'] = $returnbarcodes[$i];
            }
            echo '{"totalCount":"', $resCount, '","data":' . json_encode($datas) . '}';
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        break;
}		