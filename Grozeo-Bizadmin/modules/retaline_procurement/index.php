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

switch ($op) {
    case 'getVendorName':
        $qry = $db->getMulipleData("SELECT stpa_id,stpa_Fname FROM finascop_stock_party WHERE stpa_IsVendor = 1 ORDER BY stpa_Fname ASC", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'listRetalineProcurementItemStore':
        $data = $_POST;
        $rec_sort = empty($data['sort']) ? 'stpi_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];

        $customerId = $_POST['stpa_id'];
        if ($customerId != '') {

            $qry = "SELECT stpi_id,stit_type,IF(stit_type = 1,'Medicine','Product') AS itemType,finascop_stock_itemmaster.stit_id AS itemId,stit_SKU AS itemName,stit_brand_name,stit_quantity,least_package_type_name 
                    FROM finascop_stock_party_items INNER JOIN finascop_stock_itemmaster ON finascop_stock_itemmaster .stit_id= finascop_stock_party_items.stit_id WHERE stpa_id='{$customerId}' AND directPurchase = 1 ORDER BY $rec_sort $rec_sort_dir";

            $data = $db->getMultipleData($qry, true);

            $countQuery = "SELECT COUNT(*) FROM finascop_stock_party_items s  WHERE s.stpa_id='{$customerId}'";
            $count = $db->getItemFromDB($countQuery);


            if (!empty($data)) {
                echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
            } else {
                echo '{"totalCount":"0","data":[]}';
            }
        }

        break;
    case 'listRetalineProcurement':
        $rpd_recorddate = date('Y-m-d', strtotime($_POST['rpd_recorddate']));
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'stit_SKU' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        if (empty($rpd_recorddate)) {
            $rpd_recorddate = date('Y-m-d');
        }
        $search = " WHERE 1=1 AND rpd_date = '{$rpd_recorddate}' ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status_name') {
                            
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
        $branchName = $_POST['rpd_branch'];
        $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;
        $where .= " AND rpd_branch =" . $br_ID;

        $countQuery = "SELECT COUNT(*) FROM retaline_procurement_details INNER JOIN finascop_stock_itemmaster ON stit_ID =  rpd_stitId   {$search} {$where} GROUP BY rpd_stitId";

        $listQuery = "SELECT rpd_stitId,sum(rpd_quantity) as rpd_quantity,stit_SKU FROM retaline_procurement_details INNER JOIN finascop_stock_itemmaster ON stit_ID =  rpd_stitId     {$search} {$where} GROUP BY rpd_stitId ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'listProcurementDetails':
        $ItemId = $_POST['ItemId'];
        $rpd_branch = $_POST['rpd_branch'];
        $rpd_recorddate = date('Y-m-d', strtotime($_POST['rpd_recorddate']));
        $sort = empty($sort) ? 'rpd_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 AND rpd_stitId = {$ItemId} AND rpd_date = '{$rpd_recorddate}' and rpd_branch = {$rpd_branch} ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status_name') {
                            
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
        $countQuery = "SELECT COUNT(*) FROM retaline_procurement_details INNER JOIN finascop_stock_party ON stpa_id =  rpd_vendor  {$search} {$where}";

        $listQuery = "SELECT rpd_id,rpd_stitId,rpd_quantity,rpd_vendor,rpd_createdOn,stpa_Fname,rpd_status,(SELECT SUM(retgrnd_itemqty) FROM retaline_grn_details WHERE retgrnd_vendorid = rpd_vendor AND retgrnd_itemid = {$ItemId} AND DATE_FORMAT(retgrnd_createdon,'%Y-%m-%d') = '{$rpd_recorddate}') AS receivedQty FROM retaline_procurement_details "
                . "INNER JOIN finascop_stock_party ON stpa_id =  rpd_vendor {$search} {$where} ORDER BY {$sort} {$dir} ";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'saveRetalineProcurement':
        $data = $_POST;
        $griddata = json_decode(stripslashes($data['vendorItems']));
        $griddata = (array) $griddata;
        $db->query('begin');
        for ($i = 0; $i < count($griddata); $i++) {
            if ($griddata[$i]->rpd_quantity > 0) {
                $rpdData['rpd_stitId'] = $griddata[$i]->itemId;
                $rpdData['rpd_quantity'] = $griddata[$i]->rpd_quantity;
                $rpdData['rpd_vendor'] = $data['pe_party'];
                $rpdData['rpd_branch'] = $data['rpd_branch'];
                $rpdData['rpd_date'] = date('Y-m-d', strtotime($data['rpd_date']));
                $rpdData['rpd_createdOn'] = date("Y-m-d H:i:s");
                $rpdData['rpd_createdBy'] = $_SESSION['admin']->Finascop_UserId;

                $status = $db->perform('retaline_procurement_details', $rpdData);
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Items added to request.'";
            echo '{"success":true,"valid":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error occured while saving.'";
            echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
        }

        break;
    case 'getBranch':

        $query = $_POST['query'];
        if ($query != '')
            $con = " and br_Name like '" . $query . "%'";
        else
            $con = '';
        if ($_SESSION['admin']->Finascop_UserId != 1) {
            $userBrances = $db->getItemFromDB("SELECT GROUP_CONCAT(br_Id) FROM finascop_user_activebranches WHERE UserId = {$_SESSION['admin']->Finascop_UserId}");
            if (!empty($userBrances)) {
                $con .= " AND br_ID IN ({$userBrances}) ";
            } else {
                $con = '';
            }
        } else {
            $con = '';
        }

        $qry = "select br_ID,CONCAT(br_Name ,'-',branch_shortname) as br_Name from finascop_branch where br_status = 'Active' AND br_ID>0 AND br_PyramidLevel = 3 " . $con . " order by br_Name ";
        $branch = $db->getMulipleData($qry, true);
        if (!empty($branch)) {
            $branch = json_encode($branch);
            echo '{"data":' . $branch . '}';
        } else {
            echo '{"data":[]}';
        }
        break;
    case 'listRetalineProcurementItems':
        $stpa_id = $_POST['stpa_id'];
        $rpd_dateview = date('Y-m-d', strtotime($_POST['rpd_dateview']));
        $sort = empty($sort) ? 'rpd_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status_name') {
                            
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
        if ($stpa_id != '') {

            if ($stpa_id > 0) {
                $search .= " AND rpd_vendor = {$stpa_id} ";
            }
            if ($_SESSION['admin']->br_PyramidLevel > 1) {
                $search .= " AND rpd_branch = '{$_SESSION['admin']->finascop_current_branch_id}' ";
            }
            if (!empty($rpd_dateview)) {
                $search .= " AND rpd_date = '{$rpd_dateview}' ";
            }
            $countQuery = "SELECT COUNT(*) FROM retaline_procurement_details INNER JOIN finascop_stock_itemmaster ON stit_ID =  rpd_stitId INNER JOIN finascop_branch ON br_ID = rpd_branch   {$search} {$where} ";

            $listQuery = "SELECT rpd_stitId,rpd_quantity,stit_SKU,CONCAT(br_Name ,'-',branch_shortname) as br_Name,least_package_type_name FROM retaline_procurement_details INNER JOIN finascop_stock_itemmaster ON stit_ID =  rpd_stitId  INNER JOIN finascop_branch ON br_ID = rpd_branch   {$search} {$where}  ORDER BY {$sort} {$dir} ";

            $db->printGridJson($countQuery, $listQuery);
        }

        break;
    case 'listDistributededJobs':
        loadDistributedJobDetails();
        break;
    case 'order_details_viewDistribtedJpob':
        require(THIS_MODULE_PATH . "/detailView.php");
        break;
    case 'getBranchDistriJob':

        $query = $_POST['query'];
        if ($query != '')
            $con = " and br_Name like '" . $query . "%'";
        else
            $con = '';
        if ($_SESSION['admin']->Finascop_UserId != 1) {
            $userBrances = $db->getItemFromDB("SELECT GROUP_CONCAT(br_Id) FROM finascop_user_activebranches WHERE UserId = {$_SESSION['admin']->Finascop_UserId}");
            if (!empty($userBrances)) {
                $con .= " AND br_ID IN ({$userBrances}) ";
            } else {
                $con = '';
            }
        } else {
            $con = '';
        }

        $qry = "select br_ID,CONCAT(br_Name ,'-',branch_shortname) as br_Name from finascop_branch where br_status = 'Active' AND br_ID>0  " . $con . " order by br_Name ";//AND br_PyramidLevel <> 4
        $branch = $db->getMulipleData($qry, true);
        if (!empty($branch)) {
            $branch = json_encode($branch);
            echo '{"data":' . $branch . '}';
        } else {
            echo '{"data":[]}';
        }
        break;
    case 'loadVehicle':
        $action = $_POST['action'];
        loadVehicleDetailsDistributedJob($action);
        break;
    case 'saveQugeo':
        $quorIds = json_decode(stripslashes($_POST['quorIds']));
        $totalOrders = count($quorIds);
        $uniqueId = $_POST['uniqueId'];
        $v_id = $_POST['hdnVehicleId'];
        $type = $_POST['type'];

        $geoData = array();
        for ($i = 0; $i < count($quorIds); $i++) {
            $delLocations = $db->getFromDB("SELECT quor_id,quor_PickupPincode,quor_PickupLat,quor_PickupLng,quor_PickupLocation,quor_DeliveryPincode,quor_DeliveryLat,quor_DeliveryLng,quor_DeliveryLocation,"
                    . "if(quor_Status=22,'PICKUP', if(quor_Status=31,'DELIVERY','')) as drivetype,quor_Pickupbr_id,quor_Deliverybr_id FROM  qugeo_order WHERE quor_id = {$quorIds[$i]}", true);
            array_push($geoData, array('quor_id' => $delLocations['quor_id'], 'latitude' => $delLocations['quor_DeliveryLat'], 'longitude' => $delLocations['quor_DeliveryLng'], 'zipcode' => $delLocations['quor_DeliveryPincode']));
            $quor_PickupLat = $delLocations['quor_PickupLat'];
            $quor_PickupLng = $delLocations['quor_PickupLng'];
        }

        $slotNearestdeliveryLocations = sortByNearestLatLongPoints($geoData, $quor_PickupLat, $quor_PickupLng, false);
        $poller = new \Models\QugeoOrderPoller();
        if ($poller->HasLivePoll($v_id) == true) {
            echo '{"success":false,"msg":"The driver has a live poll, please try after two minutes."}';
            return;
        }

        $slotdeliveryLocations = array_reverse($slotNearestdeliveryLocations);

        $packingOrderIds = array_column($slotdeliveryLocations, 'quor_id');


        $ddbOrderIds = array();
        for ($j = 0; $j < $totalOrders; $j++) {
            $currentOrderCount = $j + 1;
            $slotOrderDetails = $db->getFromDB("SELECT quor_id,quor_PickupPincode,quor_PickupLat,quor_PickupLng,quor_PickupLocation,quor_DeliveryPincode,quor_DeliveryLat,quor_DeliveryLng,quor_DeliveryLocation,"
                    . "if(quor_Status=22,'PICKUP', if(quor_Status=31,'DELIVERY','')) as drivetype,quor_Pickupbr_id,quor_Deliverybr_id FROM  qugeo_order WHERE quor_id = {$slotdeliveryLocations[$j]['quor_id']}", true);

            $bk_id = intval($slotOrderDetails['quor_id']);
            if ($slotOrderDetails['drivetype'] == 'PICKUP') {
                $br_id = intval($slotOrderDetails['quor_Pickupbr_id']);
                $handling_br_id = intval($slotOrderDetails['quor_Pickupbr_id']);
            } else {
                $br_id = intval($slotOrderDetails['quor_Deliverybr_id']);
                $handling_br_id = intval($slotOrderDetails['quor_Deliverybr_id']);
            }

            $scheduleorder = new \Models\QugeoScheduler();
            if ($scheduleorder->IsQugeoAPIAlive($v_id) == false) {
                echo '{"success":false,"msg":"The Vehicle isnt active anymore, please reload"}';
                return;
            }
            $orderid = $scheduleorder->scheduleABookingSchJobs($bk_id, $orderdetails, true, $v_id, true, $packingOrderIds, $totalOrders, $currentOrderCount, 'Sch');
            $schdata['quor_id'] = $bk_id;
            $schdata['quorddb_id'] = $orderid;
            $schdata['sch_uuid'] = $uniqueId;
            $status = $db->perform('quor_scheduled_deliveries', $schdata);
            $ddbOrderIds = array_push($ddbOrderIds, $orderid);
        }
        $scheduledOrders = json_encode($ddbOrderIds);
        echo '{"success":true,"msg":"Queued for scheduling","Orderid":"' . $scheduledOrders . '"}';

        break;
    case 'dispatchEntry':
        $quorIds = json_decode(stripslashes($_POST['quor_id']));
        $totalOrders = count($quorIds);

        $db->query('begin');
        for ($i = 0; $i < count($quorIds); $i++) {
            $date = date('Y-m-d', strtotime($_POST['dispatch_date']));
            $time = date("H:i:s", strtotime($_POST['dispatch_time']));
            $dispatch_array = explode(',', $_POST['dispatch_array']);
            $data = array(
                "bcd_vehicleNo" => $_POST['vehicle_no'],
                "bcd_driver" => $_POST['driver'],
                "bcd_driverContact" => $_POST['driverContact'],
                "bcd_driverLrgcn" => $_POST['driverLrgcn'],
                "bcd_dispatchDate" => $date,
                "bcd_dispatchTime" => $time,
                "quor_id" => $quorIds[$i]
                    //"bcd_status" => 0
            );
            $orderdata = array();
            unset($data['bcd_id']);
            $data['bcd_createdOn'] = date('Y-m-d H:i:s');
            $data['bcd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('qugeo_order_dispatch', $data);

            $quor_TransferOrder_id = $db->getItemFromDB("SELECT quor_TransferOrder_id FROM qugeo_order WHERE quor_id = {$quorIds[$i]} ");
            $trId = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id} ");
            $quor_TransferOrder_Type = $db->getItemFromDB("SELECT quor_TransferOrder_Type FROM qugeo_order WHERE quor_id = {$quorIds[$i]} ");
            //quor_TransferOrder_Type : 0 - CPD2BR, 1 - B2C, 2 - B2B, 3 - Return

            $lastId = $db->insert_id();
            $qgeoStatus['quor_UpdateOn'] = date("Y-m-d H:i:s");
            $qgeoStatus['quor_Status'] = 9;
            $qgeoStatus['quor_Type'] = 2;
            $fstostatus = $db->perform('qugeo_order', $qgeoStatus, 'update', "quor_id = {$quorIds[$i]}");
            $qrystring = $db->getItemFromDB("SELECT quor_StatusUpdateQry FROM qugeo_order WHERE quor_id = {$quorIds[$i]}");
            $quor_Status = $qgeoStatus['quor_Status'];
            dispatchOrders($qrystring, $quor_Status, $trId, $lastId, $quor_TransferOrder_Type, $quor_TransferOrder_id, $quorIds[$i]);
            //financialTransaction($trId, $lastId, $quor_TransferOrder_Type, $quor_TransferOrder_id, $quorIds[$i]);
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Order dispatched'}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;
    case 'saveManualDelivery':
        $quorIds = json_decode(stripslashes($_POST['quor_id']));
        $totalOrders = count($quorIds);

        $db->query('begin');
        for ($i = 0; $i < count($quorIds); $i++) {
            $date = date('Y-m-d', strtotime($_POST['delivered_date']));
            $time = date("H:i:s", strtotime($_POST['delivered_time']));
            $quor_DeliveryConfTime = $date . ' ' . $time;
            $isDriver = $_POST['isDriver'];
            $data = array(
                "qmd_deliveredBy" => $_POST['delivered_by'],
                "qmd_remarks " => $_POST['qmd_remarks'],
                "qmd_Date" => $date,
                "qmd_Time" => $time,
                "quor_id" => $quorIds[$i]
            );
            $data['qmd_createdOn'] = date('Y-m-d H:i:s');
            $data['qmd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('qugeo_manual_deliver', $data);

            $quor_TransferOrder_id = $db->getItemFromDB("SELECT quor_TransferOrder_id FROM qugeo_order WHERE quor_id = {$quorIds[$i]} ");
            $trId = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id} ");
            $quor_TransferOrder_Type = $db->getItemFromDB("SELECT quor_TransferOrder_Type FROM qugeo_order WHERE quor_id = {$quorIds[$i]} ");
            $lastId = $db->insert_id();

            $qugeoDetails = $db->getFromDB("SELECT quor_RefNo,quor_TransferOrder_id,quor_DeliveryMethodsAllowed,quor_Type,quor_TransferOrder_Type,quor_Deliverybr_id,quor_AmountCollectible FROM qugeo_order WHERE quor_id = {$quorIds[$i]}", true);

            $qgeoStatus['quor_UpdateOn'] = date("Y-m-d H:i:s");
            $qgeoStatus['quor_Status'] = 15;
            $qgeoStatus['quor_DeliveryConfTime'] = date('Y-m-d', strtotime($quor_DeliveryConfTime));
            $qgeoStatus['quor_Type'] = 6;

            $fstostatus = $db->perform('qugeo_order', $qgeoStatus, 'update', "quor_id = {$quorIds[$i]}");

            $fstoBarcodes = $db->getMultipleData("SELECT stiid_id,stiid_barcode FROM finascop_stock_transfer_order_details_barcodes WHERE fsto_id = {$quor_TransferOrder_id}", true);
            $companyMargin = 0;
            $operationMargin = 0;
            $csMargin = 0;
            $distributorMargin = 0;
            $stiid_poLandingCostleastSKU = 0;
            $totGST = 0;
            $retailorMargin = 0;
            $courierMargin = 0;
            $driverMargin = 0;

            if ($quor_TransferOrder_Type == 1) {
                if ($quor_Type == 5) {//driver delivery
                    $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 77";
                    $wqSettings = $db->getFromDB($query, true);
                    $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                    $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                    $br_ReferenceIDs = $db->getMultipleData($br_query);

                    $total = $companyMargin + $operationMargin + $csMargin + $distributorMargin + $retailorMargin + $driverMargin;
                    $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                    //$stiid_poLandingCostleastSKU
                    $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total, 2);
                    $transctionTemplate['dr']['client']['key'] = $account;
                    $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU, 2);
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
                    $replace = array($qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'], round($stiid_poLandingCostleastSKU + $total, 2));
                    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                    if (strcmp($transctionTemplate['comments'], '') != 0) {
                        $fields = array(
                            "waqu_TransDate" => date('Y-m-d'),
                            "waqu_comment" => $transctionTemplate['comments'],
                            "waqu_SourceID" => intval($quor_id),
                            "waqs_id" => intval($wqSettings['waqs_id']),
                            "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total, 2),
                            "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                            "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                        );
                        $status = $db->perform('finascop_wallet_queue', $fields);
                    }
                } elseif ($quor_Type == 3) {//customer pickup
                    $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 24";
                    $wqSettings = $db->getFromDB($query, true);
                    $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                    $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                    $br_ReferenceIDs = $db->getMultipleData($br_query);

                    $total = $companyMargin + $operationMargin + $csMargin + $distributorMargin + $retailorMargin;
                    $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                    //$stiid_poLandingCostleastSKU
                    $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total, 2);
                    $transctionTemplate['dr']['client']['key'] = $account;
                    $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU, 2);
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
                    $replace = array($qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'], round($stiid_poLandingCostleastSKU + $total, 2));
                    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                    if (strcmp($transctionTemplate['comments'], '') != 0) {
                        $fields = array(
                            "waqu_TransDate" => date('Y-m-d'),
                            "waqu_comment" => $transctionTemplate['comments'],
                            "waqu_SourceID" => intval($quor_id),
                            "waqs_id" => intval($wqSettings['waqs_id']),
                            "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total, 2),
                            "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                            "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                        );
                        $status = $db->perform('finascop_wallet_queue', $fields);
                    }
                } elseif ($quor_Type == 4) {//courier delivery
                    $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 78";
                    $wqSettings = $db->getFromDB($query, true);
                    $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                    $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                    $br_ReferenceIDs = $db->getMultipleData($br_query);

                    $total = $companyMargin + $operationMargin + $csMargin + $distributorMargin + $retailorMargin + $courierMargin;
                    $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                    //$stiid_poLandingCostleastSKU
                    $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total, 2);
                    $transctionTemplate['dr']['client']['key'] = $account;
                    $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU, 2);
                    $transctionTemplate['Cr']['sales']['br_ReferenceID'] = $br_ReferenceIDs[3][0];
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
                    $replace = array($qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'], round($stiid_poLandingCostleastSKU + $total, 2));
                    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                    if (strcmp($transctionTemplate['comments'], '') != 0) {
                        $fields = array(
                            "waqu_TransDate" => date('Y-m-d'),
                            "waqu_comment" => $transctionTemplate['comments'],
                            "waqu_SourceID" => intval($quor_id),
                            "waqs_id" => intval($wqSettings['waqs_id']),
                            "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total, 2),
                            "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                            "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                        );
                        $status = $db->perform('finascop_wallet_queue', $fields);
                    }
                }
            } elseif ($quor_TransferOrder_Type == 2) {
                if ($stit_fixedB2BRates == 1) {
                    if ($_SESSION['admin']->br_PyramidLevel == 2) {

                        $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 69";
                        $wqSettings = $db->getFromDB($query, true);
                        $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                        $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                        $br_ReferenceIDs = $db->getMultipleData($br_query);

                        $total = $companyMargin + $operationMargin + $csMargin;
                        $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                        //$stiid_poLandingCostleastSKU
                        $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total, 2);
                        $transctionTemplate['dr']['client']['key'] = $account;
                        $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU, 2);
                        $transctionTemplate['cr']['sales']['br_ReferenceID'] = $br_ReferenceIDs[1][0];
                        $transctionTemplate['cr']['companyMargin']['amt'] = $companyMargin;
                        $transctionTemplate['cr']['companyMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0];
                        $transctionTemplate['cr']['operationMargin']['amt'] = $operationMargin;
                        $transctionTemplate['cr']['operationMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0];
                        $transctionTemplate['cr']['csMargin']['amt'] = $csMargin;
                        $transctionTemplate['cr']['csMargin']['br_ReferenceID'] = $br_ReferenceIDs[1][0];


                        $search = array("#ID#", "#NO#", "#AMT#",);
                        $replace = array($qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'], round($stiid_poLandingCostleastSKU + $total, 2));
                        $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                        if (strcmp($transctionTemplate['comments'], '') != 0) {
                            $fields = array(
                                "waqu_TransDate" => date('Y-m-d'),
                                "waqu_comment" => $transctionTemplate['comments'],
                                "waqu_SourceID" => intval($quor_id),
                                "waqs_id" => intval($wqSettings['waqs_id']),
                                "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total, 2),
                                "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                                "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                            );
                            $status = $db->perform('finascop_wallet_queue', $fields);
                        }
                    } elseif ($_SESSION['admin']->br_PyramidLevel == 3) {

                        $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 70";
                        $wqSettings = $db->getFromDB($query, true);
                        $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                        $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                        $br_ReferenceIDs = $db->getMultipleData($br_query);
                        $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                        //$stiid_poLandingCostleastSKU
                        $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total, 2);
                        $transctionTemplate['dr']['client']['key'] = $account;
                        $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU, 2);
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
                        $replace = array($qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'], round($stiid_poLandingCostleastSKU + $total, 2));
                        $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                        if (strcmp($transctionTemplate['comments'], '') != 0) {
                            $fields = array(
                                "waqu_TransDate" => date('Y-m-d'),
                                "waqu_comment" => $transctionTemplate['comments'],
                                "waqu_SourceID" => intval($quor_id),
                                "waqs_id" => intval($wqSettings['waqs_id']),
                                "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total, 2),
                                "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                                "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                            );
                            $status = $db->perform('finascop_wallet_queue', $fields);
                        }
                    }
                } elseif ($stit_fixedB2BRates == 0) {
                    if ($_SESSION['admin']->br_PyramidLevel == 2) {
                        $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 69";
                        $wqSettings = $db->getFromDB($query, true);
                        $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                        $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                        $br_ReferenceIDs = $db->getMultipleData($br_query);
                        $total = $companyMargin + $operationMargin + $csMargin;
                        $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                        //$stiid_poLandingCostleastSKU
                        $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total, 2);
                        $transctionTemplate['dr']['client']['key'] = $account;
                        $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU, 2);
                        $transctionTemplate['cr']['sales']['br_ReferenceID'] = $br_ReferenceIDs[1][0];
                        $transctionTemplate['cr']['companyMargin']['amt'] = $companyMargin;
                        $transctionTemplate['cr']['companyMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0];
                        $transctionTemplate['cr']['operationMargin']['amt'] = $operationMargin;
                        $transctionTemplate['cr']['operationMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0];
                        $transctionTemplate['cr']['csMargin']['amt'] = $csMargin;
                        $transctionTemplate['cr']['csMargin']['br_ReferenceID'] = $br_ReferenceIDs[1][0];


                        $search = array("#ID#", "#NO#", "#AMT#",);
                        $replace = array($qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'], round($stiid_poLandingCostleastSKU + $total, 2));
                        $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                        if (strcmp($transctionTemplate['comments'], '') != 0) {
                            $fields = array(
                                "waqu_TransDate" => date('Y-m-d'),
                                "waqu_comment" => $transctionTemplate['comments'],
                                "waqu_SourceID" => intval($quor_id),
                                "waqs_id" => intval($wqSettings['waqs_id']),
                                "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total, 2),
                                "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                                "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                            );
                            $status = $db->perform('finascop_wallet_queue', $fields);
                        }
                    } elseif ($_SESSION['admin']->br_PyramidLevel == 3) {
                        $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 70";
                        $wqSettings = $db->getFromDB($query, true);
                        $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                        $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                        $br_ReferenceIDs = $db->getMultipleData($br_query);

                        $total = $companyMargin + $operationMargin + $csMargin + $distributorMargin;
                        $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                        //$stiid_poLandingCostleastSKU
                        $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total, 2);
                        $transctionTemplate['dr']['client']['key'] = $account;
                        $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU, 2);
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
                        $replace = array($qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'], round($stiid_poLandingCostleastSKU + $total, 2));
                        $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                        if (strcmp($transctionTemplate['comments'], '') != 0) {

                            $fields = array(
                                "waqu_TransDate" => date('Y-m-d'),
                                "waqu_comment" => $transctionTemplate['comments'],
                                "waqu_SourceID" => intval($quor_id),
                                "waqs_id" => intval($wqSettings['waqs_id']),
                                "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total, 2),
                                "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                                "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                            );
                            $status = $db->perform('finascop_wallet_queue', $fields);
                        }
                    }
                }
            }

            $qrystring = $db->getItemFromDB("SELECT quor_StatusUpdateQry FROM qugeo_order WHERE quor_id = {$quorIds[$i]}");
            $quor_Status = $qgeoStatus['quor_Status'];
            dispatchOrders($qrystring, $quor_Status, $trId, $lastId, $quor_TransferOrder_Type, $quor_TransferOrder_id, $quorIds[$i]);

            //BEGIN FINANCIAL TRANSACTION
            $quor_id = $quorIds[$i];
            $quor_Type = $db->getItemFromDB("SELECT quor_Type FROM qugeo_order WHERE quor_id = {$quor_id} ");
            //1- Drive, 2-Hired, 3-CustomerPickup ,4-Courier, 5-DriverPickup, 6-ManualDelivery
            switch ($quor_Type) {
                case '1':
                case '2':
                case '3':
                case '4':
                case '5':
                case '6':
                    //financialTransaction($trId, $lastId, $quor_TransferOrder_Type, $quor_TransferOrder_id, $quor_id);
                    break;
            }
            //END FINANCIAL TRANSACTION  
        }

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Order dispatched'}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'getBranchName':
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        $qry = $db->getMulipleData("SELECT br_ID,CONCAT(br_Name ,'-',branch_shortname) as br_Name FROM finascop_branch WHERE br_status = 'Active' ", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'listReceiveDistributedDetails':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'dis.bcd_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    default:
                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                        }
                }
            }
        }
//        if ($_SESSION['admin']->br_PyramidLevel > 2) {
//            $brId = $_SESSION['admin']->finascop_current_branch_id;
//        } else {
//            $brId = 0;
//        }

        $brId = $_SESSION['admin']->finascop_current_branch_id;

        //$query = "SELECT quor_id,quor_RefNo,quor_Refno_Source,quor_Type,quor_TransferOrder_id,quor_Deliverybr_id from qugeo_order where quor_TransferOrder_Type = 0 and quor_Status IN (15,38) and quor_Deliverybr_id = {$brId}";
        $countQuery = "SELECT COUNT(*) FROM qugeo_order where quor_TransferOrder_Type IN(0,3) and quor_Status IN (15,38) and quor_Deliverybr_id = {$brId}";
        $listQuery = "SELECT quor_id,quor_RefNo,quor_Pickupbr_id,quor_Type,quor_TransferOrder_id,quor_Deliverybr_id,quor_TransferOrder_Type from qugeo_order where quor_TransferOrder_Type IN(4) and quor_Status IN (15,38) and quor_Deliverybr_id = {$brId}   ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $fstr_requestreceived = $db->getItemFromDB("select rdc_requestreceived from retaline_distribution_chart inner join finascop_stock_transfer_order on
        retaline_distribution_chart.rdc_id = finascop_stock_transfer_order.fstr_id and fsto_ordertype=4
                where finascop_stock_transfer_order.fsto_id = {$datas[$i]['quor_TransferOrder_id']}");
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
                $datas[$i]['quor_Deliverybr_name'] = $db->getItemFromDB("SELECT CONCAT(br_Name ,'-',branch_shortname) FROM finascop_branch WHERE br_ID = {$datas[$i]['quor_Deliverybr_id']}");
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
    case 'listReceiveItemsStore':
        $fsto_id = $_POST['quor_TransferOrder_id'];
        $quor_TransferReqId = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$fsto_id}");
        $fstrdDetails = $db->getMultipleData("SELECT fstod_id,fsto_ItemId,fstro_receivedOn,fstro_receivedTime,fsto_ItemQtyL3Received,fsto_pkdQty,fsto_ItemQty FROM finascop_stock_transfer_order_details "
                . "WHERE fsto_id = {$fsto_id} ", true);
        $count = count($fstrdDetails);
        if (!empty($fstrdDetails)) {
            for ($i = 0; $i < $count; $i++) {
                $datas[$i]['stii_id'] = $fstrdDetails[$i]['fstod_id'];
                $datas[$i]['stii_itemmastername'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$fstrdDetails[$i]['fsto_ItemId']}");
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
    case 'ReceiveDistributedDetails':
        $data = $_POST;
        $griddata = json_decode(stripslashes($data['receiveItems']));
        $griddata = (array) $griddata;

        $quor_id = $_POST['quor_id'];
        $quor_TransferOrder_id = $_POST['quor_TransferOrder_id'];
        $date = date('Y-m-d', strtotime($_POST['receive_date']));
        $time = date("H:i:s", strtotime($_POST['receive_time']));
        $fsto_isPurchaseReturn = $db->getItemFromDB("SELECT fsto_isPurchaseReturn FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        $quor_TransferReqId = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        $fsto_source = $db->getItemFromDB("SELECT fsto_source FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        $fstoItemDetails = $db->getMultipleData("SELECT * FROM finascop_stock_transfer_order_details WHERE fsto_id = {$quor_TransferOrder_id}", true);

        $db->query('begin');
        if ($quor_id > 0) {
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

                    $fsbgDetails = $db->getFromDB("SELECT * FROM finascop_stock_item_batch_group WHERE stit_ID = {$fsto_ItemId} AND fsbg_leastSKUmrp = {$itemMRP}", true);
                    if ($fsto_isPurchaseReturn == 0) {

                        $fstrdDetails = $db->getFromDB("SELECT rdc_ApprovedItemQty,rdc_TransferedItemQty,rdc_ReceivedItemQty,rdc_leastSKUCount FROM retaline_distribution_chart_details WHERE rdc_id = {$quor_TransferReqId} AND rdc_ItemId = {$fsto_ItemId}", true);
                        $totalReceivedTR = intval($fstrdDetails['rdc_ReceivedItemQty']) + intval($receiveStockItemQty);

                        $fstrd['rdc_ReceivedItemQty'] = $totalReceivedTR;
                        $fstrd['rdcd_updatedOn'] = date('Y-m-d H:i:s');
                        $fstrd['rdcd_updatedBy'] = $_SESSION['admin']->UserId;
                        if ($totalReceivedTR >= 0) {
                            $fstrd['rdcd_status'] = 6;
                        }

                        $status = $db->perform('retaline_distribution_chart_details', $fstrd, 'update', " rdc_id = {$quor_TransferReqId} AND rdc_ItemId = {$fsto_ItemId}");

                        $fstod['fsto_ItemQtyL3Received'] = $totalReceivedTO;
                        $fstod['fsto_immovableItem'] = $balanceItem;
                        $fstod['fstro_updatedOn'] = date('Y-m-d H:i:s');
                        $fstod['fstro_updatedBy'] = $_SESSION['admin']->UserId;
                        $fstod['fstro_receivedOn'] = $date;
                        $fstod['fstro_receivedTime'] = $time;
                        $fstod['fstro_receivedStatus'] = 1;
                        $status = $db->perform('finascop_stock_transfer_order_details', $fstod, 'update', " fsto_id = {$fsto_id} AND fsto_ItemId = {$fsto_ItemId}");


                        $fstrCountDetails = $db->getFromDB("SELECT SUM(rdc_leastSKUCount) as totrdc_leastSKUCount,SUM(rdc_ApprovedItemQty) as totApprovedItemQty,SUM(rdc_TransferedItemQty) as totTransferedItemQty,SUM(rdc_ReceivedItemQty) as totReceivedItemQty "
                                . "FROM retaline_distribution_chart_details WHERE rdcd_status <> 3 AND rdc_id = {$quor_TransferReqId}", true);
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

                        $totalTos = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order WHERE  fstr_id = {$quor_TransferReqId} AND fsto_ordertype = 4");
                        $ReceivedTos = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order WHERE  fstr_id = {$quor_TransferReqId} and fsto_requestreceived = 2 AND fsto_ordertype = 4");

                        $fstr['rdc_requestreceived'] = 2;

                        $fstr['rdc_updatedBy'] = $_SESSION['admin']->UserId;
                        $fstr['rdc_updatedOn'] = date('Y-m-d H:i:s');
                        $status = $db->perform('retaline_distribution_chart', $fstr, 'update', " rdc_id = {$quor_TransferReqId}");



                        $itemCount = $db->getItemFromDB("SELECT count(*) FROM finascop_stock_branch_inventory WHERE stit_id = {$fsto_ItemId} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} "); // AND fsbg_id = {$fsbg_id} AND fsbg_id = {$fsbgDetails['fsbg_id']}
                        if ($itemCount > 0) {
                            $fbisupd['fpod_leastSKUepr'] = $fsbgDetails['fsbg_leastSKUepr'];
//                            $fbisupd['fpod_customerRateHmDel'] = $fsbgDetails['fsbg_customerRateHmDel'];
//                            $fbisupd['fpod_customerRateCouDel'] = $fsbgDetails['fsbg_customerRateCouDel'];
//                            $fbisupd['fpod_customerRatePikup'] = $fsbgDetails['fsbg_customerRatePikup'];
//                            $fbisupd['fpod_poLandingCostleastSKU'] = $fsbgDetails['fpod_poLandingCostleastSKU'];
//                            $fbisupd['fpod_poMMGleastSKU'] = $fsbgDetails['fpod_poMMGleastSKU'];
//                            $fbisupd['fpod_itemleastSKUptr'] = $fsbgDetails['fsbg_itemleastSKUptr'];
//                            $fbisupd['fpod_itemleastSKUpts'] = $fsbgDetails['fsbg_itemleastSKUpts'];
//                            $fbisupd['fpod_leastSKUb2bCSsp'] = $fsbgDetails['fsbg_leastSKUb2bCSsp'];
//                            $fbisupd['fpod_leastSKUb2bRetailsp'] = $fsbgDetails['fsbg_leastSKUb2bRetailsp'];

                            $db->query("UPDATE finascop_stock_branch_inventory SET item_count = item_count + {$receiveStockItemQty} WHERE stit_id = {$fsto_ItemId} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}  "); //AND fsbg_id = {$fsbgDetails['fsbg_id']}
                            $status = $db->perform('finascop_stock_branch_inventory', $fbisupd, 'update', " stit_id = {$fsto_ItemId} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}  "); //AND fsbg_id = {$fsbgDetails['fsbg_id']}
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
    case 'gethiredVehicleStore':
        $qry = $db->getMulipleData("SELECT v_ID,v_No FROM qugeo_vehicle", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'getAdditionalItemName':
        $quor_TransferOrder_id = $_POST['quor_TransferOrder_id'];
        $fstr_id = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        $rdc_date = $db->getItemFromDB("SELECT rdc_date FROM retaline_distribution_chart WHERE rdc_id = {$fstr_id}");
        $relatedRdc = $db->getItemFromDB("SELECT GROUP_CONCAT(rdc_id) FROM retaline_distribution_chart WHERE rdc_id NOT IN({$fstr_id}) AND rdc_date = '{$rdc_date}'");
        if ($_POST['query'] != '') {
            $que = addslashes($_POST['query']);
            $searchQuery = " AND stit_SKU LIKE '%{$que}%'";
        } else {
            $searchQuery = '';
        }
        if ($_POST['sourceBranch'] > 0) {
            $br_PyramidLevel = $db->getItemSafe("SELECT br_PyramidLevel FROM finascop_branch WHERE br_ID = ?", "i", [$_POST['sourceBranch']]);
            switch ($br_PyramidLevel) {
                case 2:
                    $packageName = 'cs_package_type_name';
                    break;
                case 3:
                    $packageName = 'ds_package_type_name';
                    break;
                case 4:
                    $packageName = 'cos_package_type_name';
                    break;
            }
        }
        $relatedRdcArr = explode(',', $relatedRdc);
        if ($relatedRdcArr[0] > 0) {
            $qry = $db->getMulipleData("SELECT rdc_ItemId,stit_ID,stit_itemName,stit_SKU,cs_package_type_name,''  as packageName "
                    . "FROM retaline_distribution_chart_details INNER JOIN finascop_stock_itemmaster ON stit_ID = rdc_ItemId where 1=1 AND directPurchase = 1 AND rdc_id IN ({$relatedRdc}) {$searchQuery} GROUP BY  rdc_ItemId", true);
            if (!empty($qry)) {
                echo json_encode($qry);
            } else
                echo [];
        } else
            echo [];
        break;
    case 'getPTStore':
        $packs = array();
        $itemId = $_POST['itemId'];
        $search_hint = $_POST['query'];
        $packTypes = $db->getFromDB("SELECT stdpckl11_package_type_id,stdpckl21_package_type_id,stdpckl31_package_type_id,stdpckl41_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemId}", true);
        //print_r($_POST);
        if (!in_array($packTypes['stdpckl11_package_type_id'], $packs)) {
            $packs[] = $packTypes['stdpckl11_package_type_id'];
        }

        if (!in_array($packTypes['stdpckl21_package_type_id'], $packs)) {
            $packs[] = $packTypes['stdpckl21_package_type_id'];
        }
        if (!in_array($packTypes['stdpckl31_package_type_id'], $packs)) {
            $packs[] = $packTypes['stdpckl31_package_type_id'];
        }
        if (!in_array($packTypes['stdpckl41_package_type_id'], $packs)) {
            $packs[] = $packTypes['stdpckl41_package_type_id'];
        }
        $packs = array_filter($packs);
        if (count($packs) > 0) {
            $pachTyp = implode(',', $packs);
            $qry = "select package_type_id,package_type_name from " . FINASCOP_DB . "mypha_productpackage_type WHERE package_type_id IN ({$pachTyp}) AND status = 1 AND package_type_name LIKE '{$search_hint}%'order by package_type_name";
            $data = $db->getMultipleData($qry, true);
        }

        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getPackingDetails':
        $fstr_StockItemUnits = $_POST['fstr_StockItemUnits'];
        $itemId = $_POST['itemId'];
        $fstr_RequiredItemQty = $_POST['fstr_RequiredItemQty'];
        $packTypes = $db->getFromDB("SELECT stdpckl11_package_type_id,stdpckl21_package_type_id,stdpckl31_package_type_id,stdpckl41_package_type_id,least_package_type_name,least_package_type_id,stdpckl1_nos,stdpckl2_nos,stdpckl3_nos FROM finascop_stock_itemmaster WHERE stit_ID = {$itemId}", true);
        //print_r($packTypes);
        if ($fstr_StockItemUnits == $packTypes['stdpckl12_package_type_id']) {
            $level = '4';
            $fpot_leastSKUqty = $fstr_RequiredItemQty / $packTypes['stdpckl1_nos'];
        } else if ($fstr_StockItemUnits == $packTypes['stdpckl11_package_type_id']) {
            $level = '3';
            $fpot_leastSKUqty = $fstr_RequiredItemQty;
        } else if ($fstr_StockItemUnits == $packTypes['stdpckl21_package_type_id']) {
            $level = '2';
            $fpot_leastSKUqty = $fstr_RequiredItemQty * $packTypes['stdpckl2_nos'];
        } else if ($fstr_StockItemUnits == $packTypes['stdpckl31_package_type_id']) {
            $level = '1';
            $fpot_leastSKUqty = $fstr_RequiredItemQty * $packTypes['stdpckl2_nos'] * $packTypes['stdpckl3_nos'];
        }
        $result['leastSKUqty'] = $fpot_leastSKUqty;
        $result['least_package_type_id'] = $packTypes['least_package_type_id'];
        $result['least_package_type_name'] = $packTypes['least_package_type_name'];
        $result['success'] = true;
        if (!empty($result)) {
            echo json_encode($result);
        }
        break;
    case 'addItemstoAdditionalItemReceive':
        $quor_TransferOrder_id = $_POST['quor_TransferOrder_id'];
        $quor_id = $_POST['quor_id'];
        $quor_RefNo = $_POST['quor_RefNo'];

        $fstrd['rdcai_ItemId'] = $_POST['fstr_ItemId'];
        $fstrd['rdcai_ReceivedItemQty'] = $_POST['fstr_RequiredItemQty'];
        $fstrd['rdcai_status'] = 2;

        $fstrd['rdcai_ItemUnits'] = $_POST['fstr_StockItemUnits'];
        $fstrd['rdcai_leastSKUCount'] = $_POST['leastSKUCountfstr'];
        $fstrd['least_package_type_id'] = $_POST['fstrleast_package_type_id'];

        $count = $db->getItemSafe("SELECT COUNT(*) FROM retaline_distribution_chart_additional_items WHERE quor_id = '{$quor_id}' AND rdcai_ItemId = ?", "i", [$_POST['fstr_ItemId']]);
        $db->query('begin');
        if ($count > 0) {
            
        } else {
            $fstrd['quor_id'] = $quor_id;
            $fstrd['quor_RefNo'] = $quor_RefNo;
            $fstrd['fsto_id'] = $quor_TransferOrder_id;
            $fstrd['rdc_id'] = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
            $fstrd['rdcai_createdOn'] = date("Y-m-d H:i");
            $fstrd['rdcai_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('retaline_distribution_chart_additional_items', $fstrd);
        }
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Items added to indent.'";
            echo '{"success":true,"valid":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error occured while saving.'";
            echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
        }
        break;
    case 'listAdditionalItemReceiveItemStore':
        $quor_id = $_POST['quor_id'];
        $quor_TransferOrder_id = $_POST['quor_TransferOrder_id'];
        $rec_sort = empty($data['sort']) ? 'rdcai_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = " 1=1 AND quor_id = {$quor_id} ";
        $cond = '  ';

        if (isset($data['filter'])) {
        $allowedFields = ['proc_id', 'proc_PO_Number', 'proc_createdOn', 'proc_vendor_name', 'proc_status', 'proc_total'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . "retaline_distribution_chart_additional_items where {$filter_part} {$cond}";
        $listQuery = "SELECT rdcai_id,rdc_id,rdcai_ItemId,rdcai_ReceivedItemQty,rdcai_status,quor_RefNo,quor_id,fsto_id,rdcai_ItemMRP,rdcai_ItemUnits,rdcai_leastSKUCount,least_package_type_id,rdcai_createdOn,rdcai_createdBy,"
                . "(SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = rdcai_ItemId) as fstr_ItemName,"
                . "(select package_type_name from mypha_productpackage_type WHERE package_type_id = least_package_type_id) as leastSkuUnit,"
                . "(select package_type_name from mypha_productpackage_type WHERE package_type_id = rdcai_ItemUnits) as unitName "
                . "from " . FINASCOP_DB . "retaline_distribution_chart_additional_items WHERE {$filter_part} {$cond} ORDER BY $rec_sort $rec_sort_dir ";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'deleteAdditionalItem':
        $id = $_POST['fstrd_id'];
        $del_query = "DELETE FROM retaline_distribution_chart_additional_items WHERE rdcai_id=" . $id;
        $db->query($del_query);
        $status = $db->query('commit');
        if ($status) {

            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'saveAdditionalItemReceive':
        $quor_id = $_POST['quor_id'];
        $quor_TransferOrder_id = $_POST['quor_TransferOrder_id'];
        $date = date('Y-m-d', strtotime($_POST['textfieldreceiveddispatchReceiveddate']));
        $time = date("H:i:s", strtotime($_POST['textfieldreceiveddispatchReceivedtime']));
        $fsto_isPurchaseReturn = $db->getItemFromDB("SELECT fsto_isPurchaseReturn FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        $quor_TransferReqId = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        $fsto_source = $db->getItemFromDB("SELECT fsto_source FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        $fstoItemDetails = $db->getMultipleData("SELECT * FROM retaline_distribution_chart_additional_items WHERE fsto_id = {$quor_TransferOrder_id} AND rdcai_status = 2", true);
        $db->query('begin');
        if ($quor_id > 0) {
            foreach ($fstoItemDetails as $fstoItemDetail) {
                if ($fstoItemDetail['rdcai_ReceivedItemQty'] > 0) {
                    $receiveStockItemQty = $fstoItemDetail['rdcai_ReceivedItemQty'];
                    $fsto_ItemId = $fstoItemDetail['rdcai_ItemId'];
                    $fsto_id = $quor_TransferOrder_id;

                    $packageDetails = $db->getFromDB("SELECT csb_package_type_name,csb_package_type_id,cs_nos,cs_package_type_name,cs_package_type_id,ds_package_type_id,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,cos_package_type_id "
                            . "FROM finascop_stock_itemmaster WHERE stit_ID = {$fsto_ItemId}", true);
                    $itemMRP = $db->getItemFromDB("SELECT mrp FROM finascop_stock_branch_inventory WHERE branch_id = {$fsto_source} AND stit_id = {$fsto_ItemId} limit 1");


                    $fsbgDetails = $db->getFromDB("SELECT * FROM finascop_stock_branch_inventory WHERE stit_id = {$fsto_ItemId} AND branch_id = {$fsto_source} ", true);

                    $itemCount = $db->getItemFromDB("SELECT count(*) FROM finascop_stock_branch_inventory WHERE stit_id = {$fsto_ItemId} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} AND fsbg_id = {$fsbgDetails['fsbg_id']}"); // AND fsbg_id = {$fsbg_id} 
                    if ($itemCount > 0) {
                        $fbisupd['fpod_leastSKUepr'] = $fsbgDetails['fpod_leastSKUepr'];

                        $db->query("UPDATE finascop_stock_branch_inventory SET item_count = item_count + {$receiveStockItemQty} WHERE stit_id = {$fsto_ItemId} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}  AND fsbg_id = {$fsbgDetails['fsbg_id']}");
                        $status = $db->perform('finascop_stock_branch_inventory', $fbisupd, 'update', " stit_id = {$fsto_ItemId} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}  AND fsbg_id = {$fsbgDetails['fsbg_id']}");
                    } else {
                        $fsbipts['stit_id'] = $fsto_ItemId;
                        $fsbipts['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
                        $fsbipts['item_count'] = $receiveStockItemQty;
                        $fsbipts['mrp'] = $fsbgDetails['mrp'];
                        $fsbipts['selling_price'] = $fsbgDetails['selling_price'];
                        $fsbipts['updated_on'] = date('Y-m-d H:i:s');
                        $fsbipts['fsbg_id'] = $fsbgDetails['fsbg_id'];

                        $fsbipts['fpod_leastSKUmrp'] = $fsbgDetails['fpod_leastSKUmrp'];
                        $fsbipts['fpod_leastSKUepr'] = $fsbgDetails['fpod_leastSKUepr'];
                        $fsbipts['fpod_customerRateHmDel'] = $fsbgDetails['fpod_customerRateHmDel'];
                        $fsbipts['fpod_customerRateCouDel'] = $fsbgDetails['fpod_customerRateHmDel'];
                        $fsbipts['fpod_customerRatePikup'] = $fsbgDetails['fpod_customerRatePikup'];

                        $fsbipts['fpod_itemleastSKUptr'] = $fsbgDetails['fpod_itemleastSKUptr'];
                        $fsbipts['fpod_itemleastSKUpts'] = $fsbgDetails['fpod_itemleastSKUpts'];

                        $fsbipts['fpod_leastSKUb2bCSsp'] = $fsbgDetails['fpod_leastSKUb2bCSsp'];
                        $fsbipts['fpod_leastSKUb2bRetailsp'] = $fsbgDetails['fpod_leastSKUb2bRetailsp'];

                        $fsbipts['fpod_poLandingCostleastSKU'] = $fsbgDetails['fpod_poLandingCostleastSKU'];
                        $fsbipts['fpod_poMMGleastSKU'] = $fsbgDetails['fpod_poMMGleastSKU'];

                        $db->perform('finascop_stock_branch_inventory', $fsbipts);
                    }

                    $updatatLog['old_selling_price'] = $fsbgDetails['fsbg_sellinprice'];
                    $updatatLog['selling_price'] = $fsbgDetails['fsbg_sellinprice'];
                    $updatatLog['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
                    $updatatLog['stit_id'] = $fsto_ItemId;
                    $updatatLog['item_count'] = $receiveStockItemQty;
                    $updatatLog['fpod_skuPurchaseQty'] = $receiveStockItemQty;
                    $updatatLog['updated_on'] = date("Y-m-d H:i:s");
                    $updatatLog['updated_by'] = $_SESSION['admin']->Finascop_UserId;
                    $updatatLog['type'] = 'On Receive Distributed';
                    $updatatLog['action'] = 'Additional Items on Receive on branch - ' . $_SESSION['admin']->finascop_current_branch_id;
                    //$status = $db->perform('finascop_stock_branch_inventory_log', $updatatLog);
                    
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
                    $updataDta['rdcai_status'] = 6;
                    $updataDta['rdcai_updatedOn'] = date("Y-m-d H:i:s");
                    $updataDta['rdcai_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
                    $updataDta['fstro_receivedOn'] = $date;
                    $updataDta['fstro_receivedTime'] = $time;
                    $status = $db->perform('retaline_distribution_chart_additional_items', $updataDta, 'update', " rdcai_id = {$fstoItemDetail['rdcai_id']}");
                }
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Details saved ' }";
        } else {
            echo "{'success':False,'valid':false,'msg': 'Error While Saving.'}";
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
    case 'printDCInvoice':
        $quor_id = $_POST['quor_id'];
        ob_start();
        include('deliveryInvoice.php');
        $resHtml = ob_get_contents();
        ob_end_clean();

        echo $resHtml;
        break;
    case 'printDeliveryNote':
        $quor_id = $_POST['quor_id'];
        ob_start();
        include('deliveryNote.php');
        $resHtml = ob_get_contents();
        ob_end_clean();

        echo $resHtml;
        break;
    case 'checkUpdatePrintCount':
        $data = $_POST;
        if ($data['order_id'] > 0) {
            $fstoId = $db->getItemFromDB("SELECT quor_TransferOrder_id FROM qugeo_order WHERE quor_id = {$data['order_id']}");
            $fstoDet = $db->getFromDB("SELECT fsto_id,fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$fstoId}",true);
            
            $status = $db->query("update retaline_distribution_chart SET rdc_isInvoiced = 1 where rdc_id = {$fstoDet['fstr_id']}");
            
            if ($_SESSION['admin']->UserId != 1) {
                if ($data['type'] == 1) {
                    $invcount = $db->getItemFromDB("SELECT COUNT(*) FROM dischart_invoice_print_log WHERE orderId = {$data['order_id']} AND userId = {$_SESSION['admin']->UserId} and invoiceType = 1");
                    $invValidCount = $db->getItemFromDB("SELECT notification_content FROM app_notification WHERE notification_status = 3");
                } else {
                    $invcount = $db->getItemFromDB("SELECT COUNT(*) FROM dischart_invoice_print_log WHERE orderId = {$data['order_id']} AND userId = {$_SESSION['admin']->UserId} and invoiceType = 2");
                    $invValidCount = $invValidCount = $db->getItemFromDB("SELECT notification_content FROM app_notification WHERE notification_status = 4");
}
                if ($invcount == $invValidCount) {
                    echo '{"success":false,"valid":false}';
                    exit();
                }
            }

            $db->query('begin');
            $oipl['invoiceType'] = $data['type'];
            $oipl['orderId'] = $data['order_id'];
            $oipl['userId'] = $_SESSION['admin']->UserId;
            $status = $db->perform('dischart_invoice_print_log', $oipl);
            $status = $db->query('commit');
            if ($status > 0) {
                echo '{"success":true,"valid":true}';
            } else {
                echo '{"success":false,"valid":false}';
            }
        }
        break;
}