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


        $qry = "select br_ID,br_Name from finascop_branch where br_status = 'Active' AND br_ID>0 " . $con . " order by br_Name ";
        $branch = $db->getMulipleData($qry, true);
        if (!empty($branch)) {
            $branch = json_encode($branch);
            echo '{"data":' . $branch . '}';
        } else {
            echo '{"data":[]}';
        }


        break;
    case 'listDeliveryJobs':
        loadDeliverJobDetails();
        break;
    case 'order_details_viewdj':
        require(THIS_MODULE_PATH . "/detailView.php");
        break;
    case 'loadVehicle':
        $action = $_POST['action'];
        loadVehicleDetailsDj($action);
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
        financialTransaction($trId, $lastId, $quor_TransferOrder_Type,$quor_TransferOrder_id,$_POST['quor_id']);


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
        $bk_id = intval($_POST['qugeobk_NO']);
        $br_id = intval($_POST['br_id']);
        $handling_br_id = intval($_POST['handling_br_id']);
        $v_id = $_POST['hdnVehicleId'];
        $type = $_POST['type'];
        //$nodb = new \cgoDynamiteDB();

        $poller = new \Models\QugeoOrderPoller();
        if ($poller->HasLivePoll($v_id) == true) {
            echo '{"success":false,"msg":"The driver has a live poll, please try after two minutes."}';
            return;
        }
        $scheduleorder = new \Models\QugeoScheduler();
        if ($scheduleorder->IsQugeoAPIAlive($v_id) == false) {
            echo '{"success":false,"msg":"The Vehicle isnt active anymore, please reload"}';
            return;
        }
        if ($type == 'PICKUP') {
            $orderid = $scheduleorder->scheduleABooking($bk_id, $orderdetails, true, $v_id, true);
        } else {
            $orderid = $scheduleorder->scheduleADelivery($bk_id, $orderdetails, true, $v_id, true, false, $handling_br_id);
        }
        echo '{"success":true,"msg":"Queued for scheduling","Orderid":"' . $orderid . '"}';

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

        $qugeoDetails = $db->getFromDB("SELECT quor_RefNo, quor_TransferOrder_id,quor_DeliveryMethodsAllowed,quor_Type,quor_TransferOrder_Type,quor_Deliverybr_id,"
                        . "quor_AmountCollectible FROM qugeo_order WHERE quor_id = {$_POST['quor_id']}", true);

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
        foreach ($fstoBarcodes as $fstoBarcod) {
            $poDetail = $db->getFromDB("SELECT stiid_fpoid,stiid_fpodid,stiid_itemmasterid,stiid_poLandingCostleastSKU FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = {$fstoBarcod['stiid_barcode']}", true);
           
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

                            } elseif ($quor_Type == 3) {//customer pickup
                                $companyMargin = $companyMargin + $margins['fpod_companyMargin'];
                                $operationMargin = $operationMargin + $margins['fpod_incentiveMargin'];
                                $csMargin = $csMargin + $margins['fpod_csMargin'];
                                $distributorMargin = $distributorMargin + $margins['fpod_distributorMargin'];
                                $retailorMargin = $retailorMargin +
                                $margins['fpod_retailorMargin'];
                                $totGST = $totGST + $margins['fpod_gstPikup'];


                            } elseif ($quor_Type == 4) {//courier delivery
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
                                } elseif ($_SESSION['admin']->br_PyramidLevel == 3) { // b2b sales distributor to retailer - Fixed pricing
                                    $companyMargin = $companyMargin + $margins['fpod_itemptr_companymargin'];
                                    $operationMargin = $operationMargin + $margins['fpod_itemptr_opermargin'];
                                    $csMargin = $csMargin + $margins['fpod_itemptr_csmargin'];
                                    $distributorMargin = $distributorMargin + $margins['fpod_itemptr_dtrbtrmargin'];
                                    $totGST = $totGST + $margins['fpod_itemptrgst'];
                                }
                            } else {
                                if ($_SESSION['admin']->br_PyramidLevel == 2) {//b2b sales cs to distributor - margin pricing
                                    $companyMargin = $companyMargin + $margins['fpod_b2bcs_companymargin'];
                                    $operationMargin = $operationMargin + $margins['fpod_b2bcs_opermargin'];
                                    $csMargin = $csMargin + $margins['fpod_b2bcs_csmargin'];
                                    $totGST = $totGST + $margins['fpod_b2bCSgst'];
                                } elseif ($_SESSION['admin']->br_PyramidLevel == 3) {// b2b sales distributor to retailer - margin pricing
                                    $companyMargin = $companyMargin + $margins['fpod_b2bretai_companymargin'];
                                    $operationMargin = $operationMargin + $margins['fpod_b2bretai_opermargin'];
                                    $csMargin = $csMargin + $margins['fpod_b2bretai_csmargin'];
                                    $distributorMargin = $distributorMargin + $margins['fpod_b2bretai_dtrbtrmargin'];
                                    $totGST = $totGST + $margins['fpod_b2bRetailgst'];
                                }
                            }
                        }

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
                            "waqu_Amount" =>  round($stiid_poLandingCostleastSKU + $total,2),
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
                            "waqu_Amount" =>  round($stiid_poLandingCostleastSKU + $total,2),
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
                    $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'],   round($stiid_poLandingCostleastSKU + $total,2) );
                    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                    if (strcmp($transctionTemplate['comments'], '') != 0) {
                        $fields = array(
                            "waqu_TransDate" => date('Y-m-d'),
                            "waqu_comment" => $transctionTemplate['comments'],
                            "waqu_SourceID" => intval($quor_id),
                            "waqs_id" => intval($wqSettings['waqs_id']),
                            "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total,2),
                            "br_id" =>  intval($qugeoDetails['quor_Deliverybr_id']),
                            "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                        );
                        $status = $db->perform('finascop_wallet_queue', $fields);
                    }
                }
            } 
            elseif ($quor_TransferOrder_Type == 2)
            {
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
                                "waqu_Amount" =>  round($stiid_poLandingCostleastSKU + $total,2),
                                "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                                "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                            );
                            $status = $db->perform('finascop_wallet_queue', $fields);
                        }
                    } 
                    elseif ($_SESSION['admin']->br_PyramidLevel == 3) {

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
                } else {
                    if ($_SESSION['admin']->br_PyramidLevel == 2) {
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
                    } elseif ($_SESSION['admin']->br_PyramidLevel == 3) {
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
                                "waqu_Amount" =>  round($stiid_poLandingCostleastSKU + $total,2),
                                "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                                "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                            );
                            $status = $db->perform('finascop_wallet_queue', $fields);
                        }
                    }
                }
            }
        if ($quor_TransferOrder_Type == 1) {
            $order_customerpickup_otp = $db->getItemFromDB("SELECT order_customerpickup_otp  FROM retaline_customer_order WHERE order_id = {$trId}");
            if ($order_customerpickup_otp != $_POST['pickup_otp']) {
                echo "{'success':false,'valid':false,'message': 'OTP is not valid.'}";
                exit();
            }
        } 
        elseif ($quor_TransferOrder_Type == 2) {
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

        

        switch ($qgeoStatus['quor_Type']) {
            case '3':
            case '5':
                financialTransactionCustPickup($trId, $lastId, $quor_TransferOrder_Type, $quor_TransferOrder_id, $_POST['quor_id']);
//           $fields = array("tempvalue" => 'SESSION->br_PyramidLevel' . $_SESSION['admin']->br_PyramidLevel);
//           $db->perform('temptable',$fields);
                break;
        }

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
        switch ($quor_Type) {
            case '1':
            case '2':
            case '4':
            case '6':
                financialTransaction($trId, $lastId, $quor_TransferOrder_Type, $quor_TransferOrder_id, $_POST['quor_id']);
//           $fields = array("tempvalue" => 'SESSION->br_PyramidLevel' . $_SESSION['admin']->br_PyramidLevel);
//           $db->perform('temptable',$fields);
                break;
        }
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
        foreach ($fstoBarcodes as $fstoBarcod) {
            $poDetail = $db->getFromDB("SELECT stiid_fpoid,stiid_fpodid,stiid_itemmasterid,stiid_poLandingCostleastSKU FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = {$fstoBarcod['stiid_barcode']}", true);
            
            $stiid_poLandingCostleastSKU += $poDetail['stiid_poLandingCostleastSKU'];
             
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
                                } elseif ($_SESSION['admin']->br_PyramidLevel == 3) { // b2b sales distributor to retailer - Fixed pricing
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
                                } elseif ($_SESSION['admin']->br_PyramidLevel == 3) {// b2b sales distributor to retailer - margin pricing
                                    $companyMargin = $companyMargin + $margins['fpod_b2bretai_companymargin'];
                                    $operationMargin = $operationMargin + $margins['fpod_b2bretai_opermargin'];
                                    $csMargin = $csMargin + $margins['fpod_b2bretai_csmargin'];
                                    $distributorMargin = $distributorMargin + $margins['fpod_b2bretai_dtrbtrmargin'];
                                    $totGST = $totGST + $margins['fpod_b2bRetailgst'];
                                }
                            }
                        }
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
                    $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'],  round($stiid_poLandingCostleastSKU + $total,2) );
                    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                    if (strcmp($transctionTemplate['comments'], '') != 0) {
                        $fields = array(
                            "waqu_TransDate" => date('Y-m-d'),
                            "waqu_comment" => $transctionTemplate['comments'],
                            "waqu_SourceID" => intval($quor_id),
                            "waqs_id" => intval($wqSettings['waqs_id']),
                            "waqu_Amount" =>  round($stiid_poLandingCostleastSKU + $total,2),
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
                    $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'],  round($stiid_poLandingCostleastSKU + $total,2) );
                    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                    if (strcmp($transctionTemplate['comments'], '') != 0) {
                        $fields = array(
                            "waqu_TransDate" => date('Y-m-d'),
                            "waqu_comment" => $transctionTemplate['comments'],
                            "waqu_SourceID" => intval($quor_id),
                            "waqs_id" => intval($wqSettings['waqs_id']),
                            "waqu_Amount" =>  round($stiid_poLandingCostleastSKU + $total,2),
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
                    $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'],  round($stiid_poLandingCostleastSKU + $total,2) );
                    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                    if (strcmp($transctionTemplate['comments'], '') != 0) {
                        $fields = array(
                            "waqu_TransDate" => date('Y-m-d'),
                            "waqu_comment" => $transctionTemplate['comments'],
                            "waqu_SourceID" => intval($quor_id),
                            "waqs_id" => intval($wqSettings['waqs_id']),
                            "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total,2),
                            "br_id" =>  intval($qugeoDetails['quor_Deliverybr_id']),
                            "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                        );
                        $status = $db->perform('finascop_wallet_queue', $fields);
                    }
                }
            } 
            elseif ($quor_TransferOrder_Type == 2)
            {
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
                        $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'],  round($stiid_poLandingCostleastSKU + $total,2) );
                        $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                        if (strcmp($transctionTemplate['comments'], '') != 0) {
                            $fields = array(
                                "waqu_TransDate" => date('Y-m-d'),
                                "waqu_comment" => $transctionTemplate['comments'],
                                "waqu_SourceID" => intval($quor_id),
                                "waqs_id" => intval($wqSettings['waqs_id']),
                                "waqu_Amount" =>  round($stiid_poLandingCostleastSKU + $total,2),
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
                    if ($_SESSION['admin']->br_PyramidLevel == 2) {
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
                    } elseif ($_SESSION['admin']->br_PyramidLevel == 3) {
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
                }
            }

        $qrystring = $db->getItemSafe("SELECT quor_StatusUpdateQry FROM qugeo_order WHERE quor_id = ?", "i", [$_POST['quor_id']]);
        $quor_Status = $qgeoStatus['quor_Status'];
        dispatchOrders($qrystring, $quor_Status, $trId, $lastId, $quor_TransferOrder_Type, $quor_TransferOrder_id, $_POST['quor_id']);

        //BEGIN FINANCIAL TRANSACTION
        $quor_id = $_POST['quor_id'];
        $quor_Type = $db->getItemFromDB("SELECT quor_Type FROM qugeo_order WHERE quor_id = {$quor_id} ");
        //1- Drive, 2-Hired, 3-CustomerPickup ,4-Courier, 5-DriverPickup, 6-ManualDelivery
        switch ($quor_Type) {
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
                financialTransaction($trId, $lastId, $quor_TransferOrder_Type, $quor_TransferOrder_id, $quor_id);
//           $fields = array("tempvalue" => 'SESSION->br_PyramidLevel' . $_SESSION['admin']->br_PyramidLevel);
//           $db->perform('temptable',$fields);
                break;
        }
        //END FINANCIAL TRANSACTION  



        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Order dispatched'}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
}