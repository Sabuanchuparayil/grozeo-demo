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
    case 'getBranch':

        //****** Previous Code ******//

        $query = $_POST['query'];
        if ($query != '')
            $con = " and br_Name like '" . $query . "%'";
        else
            $con = '';


        $qry = "select br_ID,CONCAT(br_Name ,'-',branch_shortname) as br_Name from finascop_branch where br_status = 'Active' AND br_ID>0 " . $con . " order by br_Name ";
        $branch = $db->getMulipleData($qry, true);
        if (!empty($branch)) {
            $branch = json_encode($branch);
            echo '{"data":' . $branch . '}';
        } else {
            echo '{"data":[]}';
        }


        break;
    case 'listScheduledJobs':
        loadScheduledJobDetails();
        break;
    case 'order_details_viewschJob':
        require(THIS_MODULE_PATH . "/detailView.php");
        break;
    case 'loadVehicle':
        $action = $_POST['action'];
        loadVehicleDetailsSchJob($action);
        break;
    case 'assignDriverExecutive':
        $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'ASSIGNEXEC'");
        $fields = array(
            "is_cpd" => 0,
            "order_id" => $_POST['order_ID'],
            "boy_id" => $_POST['id'],
            "branch_id" => $_POST['br_ID'],
            "is_b2border" => ($_POST['type'] == 'B2B' ? 1 : 0),
            "order_pk_id" => $_POST['orderautoId']
        );
        $fields_string = json_encode($fields);
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
    case 'typeConvereter':
        $quor_id = $_POST['quor_id'];
        $quorType['quor_Type'] = $_POST['radiobuttonQuorType'];
        $quorType['quor_UpdateOn'] = date('Y-m-d H:i:s');
        $db->query('begin');
        if ($quorType['quor_Type'] == 1) {
            $quorType['quor_PickupToBeManual'] = 1;
        }
        $status = $db->perform('qugeo_order', $quorType, 'update', " quor_id ={$quor_id}");
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Order Converted '}";
        } else {
            echo "{'success':false,'valid':false,'msg': 'Error While Converting.'}";
        }
        break;
    case 'gethiredVehicleStore':
        $qry = $db->getMulipleData("SELECT v_ID,v_No FROM qugeo_vehicle", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'listPolledHistoryDj':
        $quor_id = $_POST['orderId'];
        //$orderId = $db->getItemFromDB("SELECT quor_TransferOrder_id FROM qugeo_order WHERE quor_id = {$quor_id}");

        $arrAPI = array();
        $arrAPI['PartitionKey'] = array('col' => 'quor_id', 'val' => (int) $quor_id, 'oper' => '=');
        $arrAPI['IndexName'] = 'quor_id-index';
        $arrAPI['queryAttributes'] = array('orderid');
        $nodb = new \cgoDynamiteDB();
        $rsno = $nodb->query('QugeoOrderDetails', $arrAPI, 'query');
        $datas = [];
        /*
          accepted_time: "-"
          boy_name: "Vishnu P"
          completed_time: "-"
          created_at: "08-08-2020 16:10:05"
          id: "310"
          last_scan_time: "-"
          order_id: "TOR2008081024001"
          ordersreqtatus: "Accepted"
          scan_start_time: "-"
          status: "2"
          updated_at: "08-08-2020 16:10:11"
         */

        if (isset($rsno) && count($rsno) > 0) {
            foreach ($rsno as $value) {
                $innerdata = [];
                $arrAPI = array();
                $arrAPI['PartitionKey'] = array('col' => 'orderid', 'val' => $value['orderid'], 'oper' => '=');
                $arrAPI['IndexName'] = 'orderid-apikey-index';
                $arrAPI['queryAttributes'] = array('orderid', 'apikey', 'closedat', 'createddatetime', 'currentstatus', 'isclosed');
                $rspolls = $nodb->query('QugeoOrderPollingDetails', $arrAPI, 'query');
                if (isset($rspolls) && count($rspolls) > 0) {
                    foreach ($rspolls as $rspoll) {
                        $innerdata = [];
                        //print_r($rspoll);
                        $innerdata['ordersreqtatus'] = $rspoll['currentstatus'];
                        $innerdata['updated_at'] = date('Y-m-d H:i:s', strtotime($rspoll['closedat']));
                        $innerdata['created_at'] = date('Y-m-d H:i:s', strtotime($rspoll['createddatetime']));
                        $innerdata['isclosed'] = $rspoll['isclosed'];
                        if ($innerdata['isclosed'] == 1) {
                            $innerdata['isopenPoll'] = 'No';
                        } else {
                            $innerdata['isopenPoll'] = 'Yes';
                        }
                        $innerdata['createddatetime'] = $rspoll['createddatetime'];
                        $arrAPI = array();
                        $arrAPI['PartitionKey'] = array('col' => 'apikey', 'val' => (string) $rspoll['apikey']);
                        $arrAPI['getAttributes'] = array('id');
                        $apidetails = $nodb->query('APIHistory', $arrAPI, 'getItem');
                        if (isset($apidetails) && count($apidetails) > 0) {
                            $driverName = $db->getItemFromDB("SELECT d_Name FROM qugeo_driver WHERE d_ID = " . $apidetails['id']);
                            $innerdata['boy_name'] = $driverName;
                        }
                        array_push($datas, $innerdata);
                    }
                }
            }
            usort($datas, function($b, $a) {
                $retval = $a['createddatetime'] <=> $b['createddatetime'];
                if ($retval == 0) {
                    $retval = $a['suborder'] <=> $b['suborder'];
                    if ($retval == 0) {
                        $retval = $a['details']['subsuborder'] <=> $b['details']['subsuborder'];
                    }
                }
                return $retval;
            });
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        } else {
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        }



        // $db->printGridJson($countQuery, $listQuery);
        break;
    case 'dispatchEntry':
        //retaline_branch_dispatch,retaline_branch_outward_order,finascop_stock_item_inventorydetails,finascop_stock_item_inventorydetails_movement
        $db->query('begin');
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
            "quor_id" => $_POST['quor_id']
                //"bcd_status" => 0
        );
        $orderdata = array();
        unset($data['bcd_id']);
        $data['bcd_createdOn'] = date('Y-m-d H:i:s');
        $data['bcd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('qugeo_order_dispatch', $data);

        $quor_TransferOrder_id = $db->getItemSafe("SELECT quor_TransferOrder_id FROM qugeo_order WHERE quor_id = ? ", "i", [$_POST['quor_id']]);
        $trId = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id} ");
        $quor_TransferOrder_Type = $db->getItemSafe("SELECT quor_TransferOrder_Type FROM qugeo_order WHERE quor_id = ? ", "i", [$_POST['quor_id']]);
        //quor_TransferOrder_Type : 0 - CPD2BR, 1 - B2C, 2 - B2B, 3 - Return

        $lastId = $db->insert_id();
//        if ($dispatch_array) {
//            for ($i = 0; $i < count($dispatch_array); $i++) {
//                $DispatchVehicle['bcd_id'] = $lastId;
//                $DispatchVehicle['order_status'] = 9;
//                $orderstatus = $db->perform("retaline_branch_outward_order", $DispatchVehicle, 'update', 'order_id =' . (int) $dispatch_array[$i]);
//                $bcdddata['quor_id'] = $lastId;
//                
//                $transfreOrderDetails = $db->getFromDb("SELECT fsto_source,fsto_sourcetype,fsto_destination,fsto_destinationtype FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}", true);
//                $bcdddata['bcdd_source'] = $transfreOrderDetails['fsto_source'];
//                $bcdddata['bcdd_sourceType'] = $transfreOrderDetails['fsto_sourcetype'];
//                $bcdddata['bcdd_destination'] = $transfreOrderDetails['fsto_destination'];
//                $bcdddata['bcdd_destinationType'] = $transfreOrderDetails['fsto_destinationtype'];
//                $bcddstatus = $db->perform('retaline_branch_dispatch_details', $bcdddata);bcd_driverLrgcn
        $qgeoStatus['quor_UpdateOn'] = date("Y-m-d H:i:s");
        $qgeoStatus['quor_Status'] = 9;
        $qgeoStatus['quor_Type'] = 2;
        $fstostatus = $db->perform('qugeo_order', $qgeoStatus, 'update', "quor_id = " . intval($_POST['quor_id']));
        $qrystring = $db->getItemSafe("SELECT quor_StatusUpdateQry FROM qugeo_order WHERE quor_id = ?", "i", [$_POST['quor_id']]);
        $quor_Status = $qgeoStatus['quor_Status'];
        dispatchOrders($qrystring, $quor_Status, $trId, $lastId, $quor_TransferOrder_Type, $quor_TransferOrder_id, $_POST['quor_id']);

//            }
//        }
        // $return_rec = $db->getFromDb("SELECT * from qugeo_order_dispatch WHERE bcd_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Order dispatched'}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;
    case 'deliveryChallanView':
        $quor_id = $_GET['quor_id'];
        if ($quor_id) {
            $bcd_id = $db->getItemFromDB("SELECT bcd_id FROM qugeo_order_dispatch WHERE quor_id = {$quor_id}");
            $quor_TransferOrder_id = $db->getItemFromDB("SELECT quor_TransferOrder_id FROM qugeo_order WHERE quor_id = {$quor_id} ");
            $toDetails = $db->getFromDB("SELECT fsto_id,fsto_uid,fstr_id,fsto_ordertype,fsto_type,fsto_status,fsto_createdOn,fsto_handlingcharge,fsto_cgstval,fsto_sgstval,fsto_kfcval,fsto_amtbeforetax,fsto_amtaftertax,"
                    . "fsto_discount,fsto_netamount,fsto_sourcetype,fsto_source,fsto_destination FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}", true);

            $toDetailItems = $db->getMultipleData("SELECT @slNo := @slNo + 1 as slNo,fsto_ItemId,fsto_ItemQty,fstro_ItemSPincTax,fstro_gst_value,fstro_cgst_percent,fstro_sgst_percent,fstro_cgst_value,fstro_sgst_value,fstro_totamtbeforetax,fstro_totamtaftertax,"
                    . "fstro_kfc_percent,fstro_kfc_value FROM finascop_stock_transfer_order_details WHERE fsto_id = {$quor_TransferOrder_id}", true);
//            $result = $db->getMultipleData("SELECT order_no,order_id,
//                    CASE WHEN order_status = 0 THEN 'Created'
//                WHEN order_status = 1 THEN 'Manual Queued'
//                WHEN order_status = 2 THEN 'Polled'
//                 WHEN order_status = 3 THEN 'Assigned'
//                WHEN order_status = 4 THEN 'Scanning Started'
//                WHEN order_status = 5 THEN 'Incomplete Order'
//                WHEN order_status = 6 THEN 'Order Completed'
//                WHEN order_status = 7 THEN 'Cancelled'
//                WHEN order_status = 8 THEN 'Expired'
//                WHEN order_status = 9 THEN 'Dispatched'
//                WHEN order_status = 10 THEN 'Partly Received'
//                ELSE 'Received'
//                    END AS order_status,fcp.br_Name AS cpd_Name,fb.br_Name AS branch_name FROM retaline_branch_dispatch dis 
//                    INNER JOIN retaline_branch_outward_order cpo ON dis.bcd_id=cpo.bcd_id 
//                    INNER JOIN finascop_branch fcp ON fcp.br_ID = cpo.cpd_id 
//                    INNER JOIN finascop_branch fb ON fb.br_ID = cpo.branch_id WHERE dis.bcd_id =" . $bcd_id, true);
            if ($bcd_id > 0) {
                $disdata = $db->getFromDB("SELECT bcd_id,bcd_vehicleNo,bcd_driver,TIME_FORMAT(bcd_dispatchTime, '%r') AS bcd_dispatchTime,DATE_FORMAT(bcd_dispatchDate, '%d-%m-%Y')  AS bcd_dispatchDate "
                        . "FROM qugeo_order_dispatch WHERE bcd_id = $bcd_id", true);
            }

            require(THIS_MODULE_PATH . "/dispatch_iframe_view.php");
        }
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
            //if ($slotOrderDetails['drivetype'] == 'PICKUP') {
            
//            if ($type == 'PICKUP') {
                $orderid = $scheduleorder->scheduleABookingSchJobs($bk_id, $orderdetails, true, $v_id, true, $packingOrderIds, $totalOrders, $currentOrderCount, 'Sch');
//            } else {
//                $orderid = $scheduleorder->scheduleADeliverySchJobs($bk_id, $orderdetails, true, $v_id, true, false, $handling_br_id, $packingOrderIds, $totalOrders, $currentOrderCount, 'Sch');
//            }
            $schdata['quor_id'] = $bk_id;
            $schdata['quorddb_id'] = $orderid;
            $schdata['sch_uuid'] = $uniqueId;
            $status = $db->perform('quor_scheduled_deliveries', $schdata);
            $ddbOrderIds = array_push($ddbOrderIds, $orderid);
        }
        $scheduledOrders = json_encode($ddbOrderIds);
        echo '{"success":true,"msg":"Queued for scheduling","Orderid":"' . $scheduledOrders . '"}';

        break;
    case 'getcourierCargoStore':
        $qry = $db->getMulipleData("SELECT mst_courier_id,mst_courier_name FROM mst_courier WHERE status = 1", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'saveCustomerPickup':
        $db->query('begin');

        $date = date('Y-m-d', strtotime($_POST['pickup_date']));
        $time = date("H:i:s", strtotime($_POST['textfieldcpd_pickuptime']));
        $quor_DeliveryConfTime = $date . ' ' . $time;
        $isDriver = $_POST['isDriver'];

        $quor_TransferOrder_id = $db->getItemSafe("SELECT quor_TransferOrder_id FROM qugeo_order WHERE quor_id = ? ", "i", [$_POST['quor_id']]);
        $trId = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id} ");
        $quor_TransferOrder_Type = $db->getItemSafe("SELECT quor_TransferOrder_Type FROM qugeo_order WHERE quor_id = ? ", "i", [$_POST['quor_id']]);

//quor_TransferOrder_Type : 0 - CPD2BR, 1 - B2C, 2 - B2B, 3 - Return

        if ($quor_TransferOrder_Type == 1) {
            $order_customerpickup_otp = $db->getItemFromDB("SELECT order_customerpickup_otp  FROM retaline_customer_order WHERE order_id = {$trId}");
            if ($order_customerpickup_otp != $_POST['pickup_otp']) {
                echo "{'success':false,'valid':false,'message': 'OTP is not valid.'}";
                exit();
            }
        } elseif ($quor_TransferOrder_Type == 2) {
            $pickup_otp = $_POST['pickup_otp'];
            $desiredOtp = "1234";
            $order_customerpickup_otp = strval($pickup_otp);
            //echo '$order_customerpickup_otp' . strcmp($desiredOtp, $order_customerpickup_otp);
//            print_r($order_customerpickup_otp);
            //if ($order_customerpickup_otp != $desiredOtp) {
            if (strcmp($desiredOtp, $order_customerpickup_otp) != 0) {
                echo "{'success':false,'valid':false,'message': 'OTP is not valid.'}";
                exit();
            }
        }

        $data = array(
            "qcp_pickupBy" => $_POST['picked_by'],
            "qcp_pickupMobile" => $_POST['pickup_mobile'],
            "qcp_pickupOtp" => $_POST['pickup_otp'],
            "qcp_pickupDate" => $date,
            "qcp_pickupTime" => $time,
            "quor_id" => $_POST['quor_id']
        );
        $data['qcp_createdOn'] = date('Y-m-d H:i:s');
        $data['qcp_createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('qugeo_customer_pickup', $data);


        $lastId = $db->insert_id();

        $qgeoStatus['quor_UpdateOn'] = date("Y-m-d H:i:s");
        $qgeoStatus['quor_Status'] = 15;
        $qgeoStatus['quor_DeliveryConfTime'] = date('Y-m-d', strtotime($quor_DeliveryConfTime));
        if ($isDriver == 1) {
            $qgeoStatus['quor_Type'] = 5;
        } else {
            $qgeoStatus['quor_Type'] = 3;
        }

        $fstostatus = $db->perform('qugeo_order', $qgeoStatus, 'update', "quor_id = " . intval($_POST['quor_id']));

        $qrystring = $db->getItemSafe("SELECT quor_StatusUpdateQry FROM qugeo_order WHERE quor_id = ?", "i", [$_POST['quor_id']]);
        $quor_Status = $qgeoStatus['quor_Status'];
        dispatchOrders($qrystring, $quor_Status, $trId, $lastId, $quor_TransferOrder_Type, $quor_TransferOrder_id, $_POST['quor_id']);




        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Order dispatched'}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'saveByCourier':
        $db->query('begin');
        $date = date('Y-m-d', strtotime($_POST['qoc_date']));
        $time = date("H:i:s", strtotime($_POST['qoc_time']));
        $data = array(
            "qoc_courier" => $_POST['qoc_courier'],
            "qoc_qcn" => $_POST['qoc_qcn'],
            "qoc_date" => $date,
            "qoc_time" => $time,
            "quor_id" => $_POST['quor_id']
        );
        $data['qoc_createdOn'] = date('Y-m-d H:i:s');
        $data['qoc_createdBy'] = $_SESSION['admin']->Finascop_UserId;

        $status = $db->perform('qugeo_order_courier', $data);

        $quor_TransferOrder_id = $db->getItemSafe("SELECT quor_TransferOrder_id FROM qugeo_order WHERE quor_id = ? ", "i", [$_POST['quor_id']]);
        $trId = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id} ");
        $quor_TransferOrder_Type = $db->getItemSafe("SELECT quor_TransferOrder_Type FROM qugeo_order WHERE quor_id = ? ", "i", [$_POST['quor_id']]);
        //quor_TransferOrder_Type : 0 - CPD2BR, 1 - B2C, 2 - B2B, 3 - Return
        $lastId = $db->insert_id();

        $qgeoStatus['quor_UpdateOn'] = date("Y-m-d H:i:s");
        $qgeoStatus['quor_Status'] = 9;
        $qgeoStatus['quor_Type'] = 4;
        $fstostatus = $db->perform('qugeo_order', $qgeoStatus, 'update', "quor_id = " . intval($_POST['quor_id']));

        $qrystring = $db->getItemSafe("SELECT quor_StatusUpdateQry FROM qugeo_order WHERE quor_id = ?", "i", [$_POST['quor_id']]);
        $quor_Status = $qgeoStatus['quor_Status'];
        dispatchOrders($qrystring, $quor_Status, $trId, $lastId, $quor_TransferOrder_Type, $quor_TransferOrder_id, $_POST['quor_id']);
        $quor_Type = $db->getItemSafe("SELECT quor_Type FROM qugeo_order WHERE quor_id = ? ", "i", [$_POST['quor_id']]);
//quor_Type : 1- Drive, 2-Hired, 3-CustomerPickup ,4-Courier, 5-DriverPickup, 6-ManualDelivery        

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Order dispatched'}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'sendOtpToCustomer':
        $mobile = $_POST['mobile'];
        $date = date('Y-m-d H:i:s');
        $db->query('begin');
        $camp['receiver_id'] = $mobile;
        $camp['is_sms'] = 1;
        $camp['text_message'] = 'OTP for Customer Pick up - 1111';
        $camp['created_on'] = $date;
        $status = $db->perform(FINASCOP_DB . 'retaline_emailsms_queue1', $camp);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Order dispatched'}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'saveManualDelivery':
        $db->query('begin');
        $date = date('Y-m-d', strtotime($_POST['delivered_date']));
        $time = date("H:i:s", strtotime($_POST['delivered_time']));
        $quor_DeliveryConfTime = $date . ' ' . $time;
        $isDriver = $_POST['isDriver'];
        $data = array(
            "qmd_deliveredBy" => $_POST['delivered_by'],
            "qmd_Date" => $date,
            "qmd_Time" => $time,
            "quor_id" => $_POST['quor_id']
        );
        $data['qmd_createdOn'] = date('Y-m-d H:i:s');
        $data['qmd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('qugeo_manual_deliver', $data);

        $quor_TransferOrder_id = $db->getItemSafe("SELECT quor_TransferOrder_id FROM qugeo_order WHERE quor_id = ? ", "i", [$_POST['quor_id']]);
        $trId = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id} ");
        $quor_TransferOrder_Type = $db->getItemSafe("SELECT quor_TransferOrder_Type FROM qugeo_order WHERE quor_id = ? ", "i", [$_POST['quor_id']]);
        $lastId = $db->insert_id();

        $qugeoDetails = $db->getFromSafe("SELECT quor_RefNo,quor_TransferOrder_id,quor_DeliveryMethodsAllowed,quor_Type,quor_TransferOrder_Type,quor_Deliverybr_id,quor_AmountCollectible FROM qugeo_order WHERE quor_id = ?", "i", [$_POST['quor_id']], true);
        
        $qgeoStatus['quor_UpdateOn'] = date("Y-m-d H:i:s");
        $qgeoStatus['quor_Status'] = 15;
        $qgeoStatus['quor_DeliveryConfTime'] = date('Y-m-d', strtotime($quor_DeliveryConfTime));
        $qgeoStatus['quor_Type'] = 6;

        $fstostatus = $db->perform('qugeo_order', $qgeoStatus, 'update', "quor_id = " . intval($_POST['quor_id']));
        $qrystring = $db->getItemSafe("SELECT quor_StatusUpdateQry FROM qugeo_order WHERE quor_id = ?", "i", [$_POST['quor_id']]);
        $quor_Status = $qgeoStatus['quor_Status'];
        dispatchOrders($qrystring, $quor_Status, $trId, $lastId, $quor_TransferOrder_Type, $quor_TransferOrder_id, $_POST['quor_id']);

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Order dispatched'}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
}