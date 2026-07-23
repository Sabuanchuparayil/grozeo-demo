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
        
        $qry = "select quor_UpdateOn,quor_id,quor_RefNo,quor_TransferOrder_id from qugeo_order where quor_id =  " . $quor_id;
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

            //UPdate Return
            $qry = "select coalesce(quor_ItemReturned,'') as ss   from qugeo_order where quor_id = " . $quor_id;
            $return_items = $db->getItemFromDB($qry, true);

            $updateurl = $db->getItemFromDb("select quor_ItemReturnUpdate from qugeo_order where quor_id = " . $quor_id, true);
            $updateurl = str_replace("##13", $return_items, $updateurl);
            
            $quor_AmountCollectible = $db->getItemFromDb("select quor_AmountCollectible from qugeo_order where quor_id = " . $quor_id);
            if($quor_AmountCollectible > 0){
                //PayOnDelivery::PODVoucher($bookingtimedetails["quor_TransferOrder_id"]);
            }
            $parentOrder = $db->getFromDB("SELECT fsto_ordertype,fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id} ",true);
            if ($parentOrder['fsto_ordertype'] == 1) {
                $custOrderId = $orderid;
                $confirmation_time = $_POST['confirmation_time'];
                $delQry = "CALL UpdateDeliveryStatus($quor_id,$custOrderId,'".$confirmation_time."')";
                $status = $db->query($delQry);
            }

            DeliveryConfirmation::DeliveryConfirmationVoucher($quor_TransferOrder_id);
            DeliveryConfirmation::DeliveryEmail($quor_TransferOrder_id);
            $quor_Type = $db->getItemFromDB("SELECT quor_Type FROM qugeo_order WHERE quor_id = {$quor_id}");
            if ($quor_Type == 1 && $quor_AmountCollectible > 0) {
                //PayOnDelivery::PODCashCollectionVoucher($quor_TransferOrder_id);
                //PayOnDelivery::PODCashSettlementVoucher($quor_TransferOrder_id);
            }
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