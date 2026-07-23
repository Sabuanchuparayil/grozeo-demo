<?php

/**

 * @crated on 17-02-2016
 */
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


global $db;
//..............
switch ($op) {
    case 'getFailureReasons':

        $dls_ID = empty($_POST['dls_ID']) ? '0' : $_POST['dls_ID'];
        $qry = "SELECT dls_ID, dls_Description FROM qugeo_deliverystatus WHERE dls_Description LIKE '%FAIL%' "
            . " UNION SELECT dls_ID,CONCAT('UPDATE FAILURE REASON, IF ANY (',dls_Description, ')' ) as dls_Description FROM qugeo_deliverystatus WHERE dls_ID =  {$dls_ID}"
            . " UNION SELECT dls_ID, dls_Description FROM qugeo_deliverystatus WHERE dls_Description LIKE '%INCOMPLETE%'";
        $FailureReasons = $db->getMulipleData($qry, true);
        if (!empty($FailureReasons)) {
            echo '{"data":' . json_encode($FailureReasons) . '}';
        } else
            echo '{"data":[]}';
        break;
    case 'listDriverPayment':
        $qry = "SELECT DISTINCT ib.bk_ID AS booking_id,ib.bk_NO AS booking_no,bk_Date AS booking_date,"
            . "ib.bk_NetAmt-ib.bk_roundoff AS net_amt,qci.qcis_percent AS percentage,qci.qcis_amount AS amount "
            . "FROM " . DB_PREFIX . "1.qc_incomedivision qci INNER JOIN " . DB_PREFIX . "1.retaline_inward_booking ib "
            . "ON qci.bk_id = ib.bk_ID AND qci.bk_brk_br_ID = ib.bk_brk_br_id "
            . "WHERE qcis_paybletotype=1";
        $DriverPayments = $db->getMulipleData($qry, true);
        if (!empty($DriverPayments)) {
            echo '{"totalCount":' . count($DriverPayments) . ',"data":' . json_encode($DriverPayments) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';


        break;
    case 'getDriver':
        if (!empty($_POST['br_id'])) {
            $qry = "select d_ID as driver_ID,d_Name as driver_Name from " . DB_PREFIX . " driver WHERE br_id = {$_POST['br_id']}";
            $drivers = $db->getMulipleData($qry, true);
            if (!empty($drivers)) {

                echo '{"data":' . json_encode($drivers) . '}';
                echo '{"data":[]}';
            }
        } else {
            echo '{"data":[]}';
        }
        break;
    case 'incomeDivisionDetails':

        $bk_id = $_POST['bk_id'];
        $qry = "SELECT (SELECT qcid_Name FROM retaline_qc_divisionconfig WHERE qcic_id = qcis_type) AS indi_type ,qcis_percent AS indi_percentage,qcis_amount AS indi_amount, IF(qcis_ispaid = 1,'Paid', 'Payable') AS indi_ispaid,IF(qcis_paybletotype = 0,(SELECT qcid_Name FROM retaline_qc_divisionconfig  WHERE qcic_id = qcis_payableto),IF(qcis_paybletotype = 1,(SELECT CONCAT(d_Name,'Ph:',d_Ph1) FROM driver WHERE d_ID = qcis_payableto),IF(qcis_paybletotype = 2,(SELECT CONCAT(br_Name,'Ph:',br_Phone) FROM finascop_branch WHERE br_ID = qcis_payableto),IF(qcis_paybletotype = 3,(SELECT crossbr_name FROM retaline_branch_crossbookingmapping WHERE crossbr_id = qcis_payableto),'')))) AS indi_payable_to,qcis_paidto AS indi_paid_to,qcis_paidon AS indi_paid_on,qcis_paidby AS indi_paid_by FROM retaline_qc_incomedivision WHERE bk_id = {$bk_id}";
        // $qry = "SELECT (SELECT qcid_Name FROM " . DB_PREFIX . "1.retaline_qc_divisionconfig WHERE qcic_id = qcis_type) AS indi_type ,"
        //         . "qcis_percent AS indi_percentage,qcis_amount AS indi_amount, IF(qcis_ispaid = 1,'Paid', 'Payable') AS indi_ispaid,"
        //         . "IF(qcis_paybletotype = 0,(SELECT qcid_Name FROM " . DB_PREFIX . "1.retaline_qc_divisionconfig  WHERE qcic_id = qcis_payableto),"
        //         . "IF(qcis_paybletotype = 1,(SELECT CONCAT(d_Name,'Ph:',d_Ph1) FROM " . DB_PREFIX . "1.driver WHERE d_ID = qcis_payableto),"
        //         . "IF(qcis_paybletotype = 2,(SELECT CONCAT(br_Name,'Ph:',br_Phone) FROM " . DB_PREFIX . "1.finascop_branch WHERE br_ID = qcis_payableto),"
        //         . "IF(qcis_paybletotype = 3,(SELECT crossbr_name FROM " . DB_PREFIX . "1.retaline_branch_crossbookingmapping WHERE crossbr_id = qcis_payableto),'')))) AS indi_payable_to"
        //         . ",qcis_paidto AS indi_paid_to,qcis_paidon AS indi_paid_on,"
        //         . "qcis_paidby AS indi_paid_by FROM " . DB_PREFIX . "1.qc_incomedivision WHERE bk_id = {$bk_id}";
        $incomeDivDetails = $db->getMulipleData($qry, true);
        if (!empty($incomeDivDetails)) {
            echo '{"totalCount":' . count($incomeDivDetails) . ',"data":' . json_encode($incomeDivDetails) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        //break;
        break;

    case 'listIncomeDivBooking':
        $filterCon = "";
        if (isset($_POST['br_id']) && isset($_POST['from_date']) && isset($_POST['to_date'])) {
            $filterCon .= " AND ib.bk_s_br_ID = {$_POST['br_id']}";
            $filterCon .= " AND ib.bk_Date >= {$_POST['from_date']}";
            $filterCon .= " AND ib.bk_Date >= {$_POST['to_date']}";
        }
        $qry = "SELECT DISTINCT ib.bk_ID as booking_id,ib.bk_NO as booking_no,bk_Date as booked_at,qcis_CalculatedOn as calculated_on FROM retaline_qc_incomedivision qci INNER JOIN retaline_inward_booking ib ON qci.bk_id = ib.bk_ID AND qci.bk_brk_br_id = ib.bk_brk_br_ID WHERE bk_IsProfitDivisionCalculated = 1 " . $filterCon;

        // $qry = "SELECT DISTINCT ib.bk_ID as booking_id,ib.bk_NO as booking_no,bk_Date as booked_at,qcis_CalculatedOn as calculated_on FROM " . DB_PREFIX . "retaline_qc_incomedivision qci "
        //         . "INNER JOIN " . DB_PREFIX . "retaline_inward_booking ib ON qci.bk_id = ib.bk_ID AND qci.bk_brk_br_ID = ib.bk_brk_br_id WHERE bk_IsProfitDivisionCalculated = 1 " . $filterCon;
        $incomeBrDetails = $db->getMulipleData($qry, true);
        if (!empty($incomeBrDetails)) {
            echo '{"totalCount":' . count($incomeBrDetails) . ',"data":' . json_encode($incomeBrDetails) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;
    case 'loadVehicle':
        $action = $_POST['action'];
        loadVehicleDetails($action);
        break;

    case 'listVehicleHistory':
        listVehicleHistory();
        break;

    case 'getVehicleStore':
        loadHistoryStore();
        break;

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

    case 'listSchedule':
    case 'listScheduledJobs':
        loadScheduleDetails();

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

    case 'listLiveVehicles':
        listLiveVehicles();
        break;

    case 'schroute':
        listschroute();
        break;
    case 'actroute':
        listactroute();
        break;
    case 'getMarkers':
        /* isjobconfirmation	0
          type	Order
          type_value	4f14e9ec4bffa950045300bd70bdc421ad31706d */
        $nodb = new \cgoDynamiteDB();
        //        if ($_POST['type'] == 'Order') {
        //            $arrOrder = array();
        //            $arrOrder['PartitionKey'] = array('col' => 'orderid', 'val' => $_POST['type_value'], 'oper' => '=');
        //            $arrOrder['getAttributes'] = array('Acceptedapikey');
        //            $nors = $nodb->query('QugeoOrderDetails', $arrOrder, 'getItem');
        //            $vehicle = $nors['Acceptedapikey'];
        //        } else {
        //            $vehicle = $_POST['type_value'];
        //        }
        //Get vehicle session information
        if ($_POST['type'] == 'Vehicle') {
            $driverid = $_POST['type_value'];
        } else {
            $driverid = $_POST['driverid'];
        }

        $arrAPI = array();
        $arrAPI['PartitionKey'] = array('col' => 'apikey', 'type' => 'S', 'val' => $driverid);
        $arrAPI['getAttributes'] = array('HasLoggedOut', 'LoggedOutAt', 'IsCleanLogout');
        $rsno = $nodb->query('APIHistory', $arrAPI, 'getItem');
        $HasLoggedOut = $rsno['HasLoggedOut'];
        $LoggedOutAt = $rsno['LoggedOutAt'];
        $IsCleanLogout = $rsno['IsCleanLogout'];

        //Get Orders 
        $arrAPI = array();
        $arrAPI['PartitionKey'] = array('col' => 'apikey', 'val' => $driverid, 'oper' => '=');
        $arrAPI['SortKey'] = array('col' => 'IsClosed', 'val' => 10, 'oper' => '<');
        $arrAPI['IndexName'] = 'apikey-IsClosed-index';
        $arrAPI['queryAttributes'] = array('order', 'orderid', 'IsClosed', 'IsPickup');
        $rsno = $nodb->query('QugeoLiveVehicleOrders', $arrAPI, 'query');
        $orderdetails = array();
        if (isset($rsno) && count($rsno) > 0) {

            foreach ($rsno as $value) {
                $arrOrders = array();
                $arrOrders['PartitionKey'] = array('col' => 'orderid', 'val' => $value['orderid']);
                $arrOrders['getAttributes'] = array('OrderStatus', 'IsClosed', 'IsPickup', 'bkno', 'SrcLocName', 'deliverylocation', 'chrgwt', 'totwt', 'totvol', 'netamt', 'paymode', 'pktcount', 'pickupLat', 'pickupLng', 'deliveryLat', 'deliveryLng');
                $nors = $nodb->query('QugeoOrderDetails', $arrOrders, 'getItem');
                //print_r($nors);
                $icon = '';
                if ($value['IsClosed'] == 1) {
                    if ($value['IsPickup'] == "1") {
                        if ($nors['OrderStatus'] == ORDER_PICKUP_PICKEDUP_TOBR_DLS_ID || $nors['OrderStatus'] == ORDER_PICKUP_PICKEDUP_TODST_DLS_ID) {
                            $icon = GMAP_PIKCUP_DONE_ICON;
                        } else {
                            $icon = GMAP_PIKCUP_FAILED_ICON;
                        }
                    } else {
                        if ($nors['OrderStatus'] == ORDER_DELIVERY_MARKED_DLS_ID) {
                            $icon = GMAP_DELIVERY_DONE_ICON;
                        } else {
                            $icon = GMAP_DELIVERY_FAILED_ICON;
                        }
                    }
                } else {
                    if ($HasLoggedOut == 1) {
                        if ($value['IsPickup'] == "1") {
                            $icon = GMAP_PIKCUP_TERMINATED_ICON;
                        } else {
                            $icon = GMAP_DELIVERY_TERMINATED_ICON;
                        }
                    } else {
                        if ($value['IsPickup'] == "1") {
                            $icon = GMAP_PIKCUP_ICON;
                        } else {
                            $icon = GMAP_DELIVERY_ICON;
                        }
                    }
                }
                if ($value['IsPickup'] == "1") {
                    $locaname = $nors['pickuplocation'];
                    $lat = $nors['pickupLat'];
                    $lon = $nors['pickupLng'];
                } else {
                    $locaname = $nors['deliverylocation'];
                    $lat = $nors['deliveryLat'];
                    $lon = $nors['deliveryLng'];
                }
                if ($_POST['type'] == 'Order' && $_POST['type_value'] == $value['orderid']) {
                    $animate = true;
                } else {
                    $animate = false;
                }
                array_push($orderdetails, array('order' => $value['order'], 'orderid' => $value['orderid'], 'ordericon' => $icon, 'bkno' => $nors['bkno'], 'locationname' => $locaname, 'netamt' => $nors['netamt'], 'paymode' => $nors['paymode'], 'pktcount' => $nors['pktcount'], 'totwt' => $nors['totwt'], 'totvol' => $nors['totvol'], 'Latitude' => $lat, 'Longitude' => $lon, 'animate' => $animate));
            }
        }

        $vehicledetails = array();
        $arrAPI = array();
        $arrAPI['PartitionKey'] = array('col' => 'apikey', 'type' => 'S', 'val' => $driverid);
        $arrAPI['getAttributes'] = array('v_typename', 'v_MapIcon', 'v_no', 'Latitude', 'Longitude', 'AssignedLoadedVolume', 'AssignedLoadedWeight', 'CurrentLoadedVolume', 'CurrentLoadedWeight', 'DriverName', 'mobno', 'Home_Latitude', 'Home_Longitude');
        $rsno = $nodb->query('QugeoLiveVehicles', $arrAPI, 'getItem');
        array_push($vehicledetails, array('apikey' => $driverid, 'v_typename' => $rsno['v_typename'], 'vehicleicon' => $rsno['v_MapIcon'], 'v_no' => $rsno['v_no'], 'Latitude' => $rsno['Latitude'], 'Longitude' => $rsno['Longitude'], 'DriverName' => $rsno['DriverName'], 'mobno' => $rsno['mobno'], 'animate' => true));

        $orderdetails = json_encode($orderdetails);
        $vehicledetails = json_encode($vehicledetails);
        $homelocation = json_encode(array('Latitude' => $rsno['Home_Latitude'], 'Longitude' => $rsno['Home_Longitude'], 'homeicon' => GMAP_QGDRV_HOME_ICON));

        echo '{success:true,"order":' . $orderdetails . ',"vehicle":' . $vehicledetails . ',"qgdrvhome":' . $homelocation . '}';
        break;

    case 'forceLogout':
        $apikey = $_POST['vehid'];
        $utils = new \Models\Utils();
        $kmscovered = $utils->getKMInaTrip($apikey);
        $driverid = $_POST['DriverId'];
        $nodb = new \cgoDynamiteDB();
        $datetime = date("YmdHis");
        $arrUpdate = array();
        $arrUpdate['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey);
        $arrUpdate['Data'] = array();
        array_push($arrUpdate['Data'], array('col' => 'Is_Live', 'val' => 0));
        array_push($arrUpdate['Data'], array('col' => 'LoggedOutAt', 'val' => (string) $datetime));
        array_push($arrUpdate['Data'], array('col' => 'IsCleanLogout', 'val' => 4));
        array_push($arrUpdate['Data'], array('col' => 'KmsCovered', 'val' => $kmscovered));
        $nors = $nodb->perform('QugeoLiveVehicles', 'update', $arrUpdate, $response);
        $arrSession = array();
        $arrSession['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey);
        $arrSession['Data'] = array();
        array_push($arrSession['Data'], array('col' => 'HasLoggedOut', 'val' => 1));
        array_push($arrSession['Data'], array('col' => 'LoggedOutAt', 'val' => (string) $datetime));
        array_push($arrSession['Data'], array('col' => 'IsCleanLogout', 'val' => 0));
        $nors = $nodb->perform('APIHistory', 'update', $arrSession, $response);
        $arrSession = array();
        $arrSession['Data'] = array();
        $arrSession['PartitionKey'] = array('col' => 'usertype', 'val' => 2);
        $arrSession['SortKey'] = array('col' => 'id', 'val' => (string) $driverid);
        array_push($arrSession['Data'], array('col' => 'apikey', 'val' => '-'));
        $nosession = $nodb->perform('APISession', 'update', $arrSession, $response);
        $db->query('update qugeo_driver set d_apikey = "-" where d_ID  =' . $driverid);
        echo '{success:true,"msg":"Forced Logout"}';
        break;

    case 'export_to_excel':
        require dirname(__FILE__) . '/export_to_excel.php';
        break;

    case 'snap_road':
    case 'snap_road_details':
        getSnapRoad();
        break;
    case 'listInTransitJobs':
        loadInTransitDetails();

        break;
    case 'markDeliverer':
        $quor_id = $_POST['orderid'];
        $deliverReturnType = $_POST['deliverReturnType'];
        $deliverReturnDate = $_POST['deliverReturnDate'];
        $deliverReturnRemarks = $_POST['deliverReturnRemarks'];
        $bankTransactionNo = $_POST['bankTransactionNo'];
        $deliveredAmtType = $_POST['deliveredAmtType'];
        //print_r($_POST);exit();
        $db->query('begin');
        if ($deliverReturnType == 'Delivered') {
            $quorType['quor_Status'] = 15;
            $quor_DeliveryConfTime = date('Y-m-d H:i:s', strtotime($deliverReturnDate));
        } else {
            $quorType['quor_Status'] = 22;
            $quorType['quor_Type'] = 0;

            $qcp['qoc_returnDate'] = date('Y-m-d', strtotime($deliverReturnDate));
            $qcp['qoc_returnRemarks'] = $deliverReturnRemarks;
            $qcp['qoc_updatedOn'] = date('Y-m-d H:i:s');
            $qcp['qoc_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('qugeo_order_courier', $qcp, 'update', " quor_id ={$quor_id}");
        }

        $quorType['quor_UpdateOn'] = date('Y-m-d H:i:s');
        //$quorType['quor_UpdateOn'] = $bankTransactionNo;

        $status = $db->perform('qugeo_order', $quorType, 'update', " quor_id ={$quor_id}");



        $qrystring = $db->getItemFromDB("SELECT quor_StatusUpdateQry FROM qugeo_order WHERE quor_id = {$quor_id}");
        $updateQueries = getQugeoParentStatusUpdated($qrystring, $quorType['quor_Status']);
        $updateurl = str_replace("###2", "", $updateQueries);
        $updateurl = str_replace("###6", ($deliveredAmtType == "Cash" ? 7 : 6), $updateurl); //if cash 7 
        $updateurl = str_replace("###7", ($deliveredAmtType == "Cash" ? "" : $bankTransactionNo), $updateurl);
        $updateQuerys = explode(";", $updateurl);
        $qugeoDetails = array();
        foreach ($updateQuerys as $updateQuery) {
            $updateQuery = trim($updateQuery);
            if ($updateQuery != '') {
                $status = $db->query("{$updateQuery}");
            }
        }
        $quor_AmountCollectible = $db->getItemFromDb("select quor_AmountCollectible from qugeo_order where quor_id = " . $quor_id);
        $quor_TransferOrder_id = $db->getItemFromDb("select quor_TransferOrder_id from qugeo_order where quor_id = " . $quor_id);
        $parentOrder = $db->getFromDB("SELECT fsto_ordertype,fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id} ",true);

        if ($quor_AmountCollectible > 0) {
            //PayOnDelivery::PODVoucher($quor_TransferOrder_id);
        }

        if ($parentOrder['fsto_ordertype'] == 1) {
            $custOrderId = $parentOrder['fstr_id'];
            $delQry = "CALL UpdateDeliveryStatus($quor_id,$custOrderId,'".$quor_DeliveryConfTime."')";
            $status = $db->query($delQry);
        }
        DeliveryConfirmation::DeliveryConfirmationVoucher($quor_TransferOrder_id);
        DeliveryConfirmation::DeliveryEmail($quor_TransferOrder_id);
        $quor_Type = $db->getItemFromDB("SELECT quor_Type FROM qugeo_order WHERE quor_id = {$quor_id}");
        if ($quor_Type == 1 && $quor_AmountCollectible > 0) {
           // PayOnDelivery::PODCashCollectionVoucher($quor_TransferOrder_id);
            //PayOnDelivery::PODCashSettlementVoucher($quor_TransferOrder_id);
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Delivered. '}";
        } else {
            echo "{'success':false,'valid':false,'msg': 'Error While Converting.'}";
        }
        break;
}
