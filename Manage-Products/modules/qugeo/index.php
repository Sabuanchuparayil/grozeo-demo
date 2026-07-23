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
        
        $dls_ID = empty($_POST['dls_ID'])? '0':$_POST['dls_ID'];
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


        $qry = "select br_ID,br_Name from finascop_branch where br_status = 'Active' AND br_ID>0 " . $con . " order by br_Name ";
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

    case 'export_to_excel' :
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
            $quorType['quor_DeliveryConfTime'] = date('Y-m-d H:i:s', strtotime($deliverReturnDate));
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
//BEGIN FINANCIAL TRANSACION
        $qugeoDetails = $db->getFromDB("SELECT quor_RefNo,quor_TransferOrder_id,quor_DeliveryMethodsAllowed,quor_Type,quor_TransferOrder_Type,quor_Deliverybr_id,quor_AmountCollectible FROM qugeo_order WHERE quor_id = {$quor_id}", true);


        $quor_TransferOrder_id = $db->getItemFromDB("SELECT quor_TransferOrder_id FROM qugeo_order WHERE quor_id = {$quor_id}");
        $quor_RefNo = $db->getItemFromDB("SELECT fsto_uid FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        $barcodes = $db->getItemFromDB("SELECT GROUP_CONCAT(stiid_barcode) FROM finascop_stock_transfer_order_details_barcodes WHERE fsto_id = {$quor_TransferOrder_id}");

        //$amount = $db->getItemFromDB("SELECT SUM(stii_epraft) FROM finascop_stock_item_inventorydetails WHERE stiid_barcode IN ({$barcodes})");
        $amount = 0;


        $barcodesArray = explode(',', $barcodes);
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
        if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
            foreach ($barcodesArray as $singleBarode) {
                $barcodeDetails = $db->getFromDB("SELECT stii_epraft,stiid_itemmasterid FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = {$singleBarode}", true);
                $packageDetails = $db->getFromDB("SELECT csb_package_type_name,csb_package_type_id,cs_nos,cs_package_type_name,cs_package_type_id,ds_package_type_id,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,cos_package_type_id "
                        . "FROM finascop_stock_itemmaster WHERE stit_ID = {$barcodeDetails['stiid_itemmasterid']}", true);
                if ($_SESSION['admin']->br_PyramidLevel == 2) {
                    $stii_epraft = $barcodeDetails['stii_epraft'] / $packageDetails['cs_nos'];
                } else if ($_SESSION['admin']->br_PyramidLevel == 3) {
                    $stii_epraft = $barcodeDetails['stii_epraft'] / ($packageDetails['ds_nos'] * $packageDetails['cs_nos']);
                } else {
                    $stii_epraft = $barcodeDetails['stii_epraft'];
                }

                $poDetail = $db->getFromDB("SELECT stiid_fpoid,stiid_fpodid,stiid_itemmasterid,stiid_poLandingCostleastSKU FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = {$singleBarode}", true);
                $query_margins = "SELECT fpod_b2bCSgst,fpod_b2bcs_companymargin,fpod_b2bcs_opermargin,fpod_b2bcs_csmargin,
                fpod_b2bRetailgst,fpod_b2bretai_companymargin,fpod_b2bretai_opermargin,fpod_b2bretai_csmargin,fpod_b2bretai_dtrbtrmargin,
                fpod_itemptrgst,fpod_itemptr_dtrbtrmargin,fpod_itemptr_csmargin,fpod_itemptr_opermargin,fpod_itemptr_companymargin,
                fpod_itemptsgst,fpod_itempts_csmargin,fpod_itempts_opermargin,fpod_itempts_companymargin,
                fpod_companyMarginCD,fpod_incentiveMarginCD,fpod_csMarginCD,fpod_distributorMarginCD,fpod_retailorMarginCD,fpod_courierMarginCD,
                fpod_companyMarginHD,fpod_incentiveMarginHD,fpod_csMarginHD,fpod_distributorMarginHD,fpod_retailorMarginHD,fpod_driverMarginHD,
                fpod_companyMargin,fpod_incentiveMargin,fpod_csMargin,fpod_distributorMargin,fpod_retailorMargin,
                fpod_gstHmDel,fpod_gstCouDel,fpod_gstPikup 
                FROM finascop_purchase_order_details WHERE fpod_id = {$poDetail['stiid_fpodid']}";

                $margins = $db->getFromDB($query_margins, true);
                $stiid_poLandingCostleastSKU += $poDetail['stiid_poLandingCostleastSKU'];

                $stit_fixedB2BRates = $db->getItemFromDB("SELECT stit_fixedB2BRates FROM finascop_stock_itemmaster WHERE stit_ID = {$poDetail['stiid_itemmasterid']}");
                $skuNos = $db->getFromDB("SELECT cos_nos,ds_nos FROM finascop_stock_itemmaster WHERE stit_ID = {$poDetail['stiid_itemmasterid']}", true);
                $quor_TransferOrder_Type = $qugeoDetails['quor_TransferOrder_Type'];
//quor_TransferOrder_Type : 0 - CPD2BR, 1 - B2C, 2 - B2B, 3 - Return
                $quor_Type = $qugeoDetails['quor_Type'];
//quor_Type : 1- Drive, 2-Hired, 3-CustomerPickup ,4-Courier, 5-DriverPickup, 6-ManualDelivery  
//echo 'quor_TransferOrder_Type' . $quor_TransferOrder_Type;
//echo '$stit_fixedB2BRates' . $stit_fixedB2BRates;
//exit(1);
                if ($quor_TransferOrder_Type == 1) { //B2C
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
                } elseif ($quor_TransferOrder_Type == 2) { //B2B
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
                    } elseif ($stit_fixedB2BRates == 0) {
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

                $amount = $amount + $stii_epraft;
            }
        }
        if ($quor_TransferOrder_Type == 1) { //B2C
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
        } elseif ($quor_TransferOrder_Type == 2) { //B2B
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


        //$amount = round(doubleval($amount),2);
        $sorceDestination = $db->getFromDB("SELECT fsto_source,fsto_destination from finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}", true);
        //echo 'amont' . $amount;
        if ($deliverReturnType == 'Delivered') {
            $fstr_id = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
            $B2COrderDetails = $db->getFromDB("SELECT payment_mode,total,order_total_gst,order_kfc_amount,order_total_cgst,order_total_sgst,order_branch_id,order_delivery_charge FROM retaline_customer_order WHERE order_id = {$fstr_id}", true);
            if (($qugeoDetails['quor_TransferOrder_Type'] == 1) && ($qugeoDetails['quor_DeliveryMethodsAllowed'] == 8)) {
                $branchid = intval($qugeoDetails['quor_Deliverybr_id']);
                $qoc_courier = $db->getItemFromDB("SELECT qoc_courier FROM qugeo_order_courier WHERE quor_id = {$quor_id}");
                $accled_ReferenceIdcourier = $db->getItemFromDB("SELECT accled_ReferenceId FROM mst_courier WHERE mst_courier_id = {$qoc_courier}");
                $defaulpaymentgateway = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'defaulpaymentgateway'");
                $query = "SELECT accled_ReferenceId FROM finascop_accounts_ledgertype_default fald INNER JOIN finascop_accounts_ledger fal ON fald.Group_ID = fal.Group_ID WHERE ledgertypedefaultname = '{$defaulpaymentgateway}'  AND accled_BranchId = {$branchid}";
                $accled_ReferenceId = $db->getItemFromDB($query);
                $mst_courier_phone = $db->getItemFromDB("SELECT mst_courier_phone FROM mst_courier WHERE mst_courier_id = {$qoc_courier}");
                if ($B2COrderDetails['payment_mode'] == 6) {
                    //25-B2CCourierHomeDeliveryOnlinePaid 42
                    $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 42";
                    $wqSettingsl1 = $db->getFromDB($query, true);
                    $transctionTemplate11 = json_decode($wqSettingsl1['waqs_Configuration'], true);

                    $transctionTemplate11['dr']['bank']['amt'] = $qugeoDetails['quor_AmountCollectible'];
                    $transctionTemplate11['dr']['bank']['key'] = $accled_ReferenceId;
                    $transctionTemplate11['cr']['courier']['amt'] = $qugeoDetails['quor_AmountCollectible'];
                    $transctionTemplate11['cr']['courier']['key'] = $accled_ReferenceIdcourier;

                    $search = array("#AMT#", "#NO#", "#ID#", "#NAME#", "#PHONE#");
                    $replace = array($qugeoDetaicls['quor_AmountCollectible'], $quor_id, $quor_RefNo, $defaulpaymentgateway, $mst_courier_phone);
                    $transctionTemplate11['comments'] = str_replace($search, $replace, $transctionTemplate11['comments']);
                    if (strcmp($transctionTemplate11['comments'], '') != 0) {
                        $fieldsl1 = array(
                            "waqu_TransDate" => date('Y-m-d'),
                            "waqu_comment" => $transctionTemplate11['comments'],
                            "waqu_SourceID" => intval($quor_id),
                            "waqs_id" => intval($wqSettingsl1['waqs_id']),
                            "waqu_Amount" => doubleval($qugeoDetails['quor_AmountCollectible']),
                            "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                            "waqu_Data" => stripslashes(json_encode($transctionTemplate11))
                        );
                        $status = $db->perform('finascop_wallet_queue', $fieldsl1);
                    }
                    //25-2-B2CcourierHomeDeliveryOnlin

                    $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 64";
                    $wqSettingsl2 = $db->getFromDB($query, true);
                    $transctionTemplate12 = json_decode($wqSettingsl2['waqs_Configuration'], true);

                    $transctionTemplate12['dr']['retailerSales']['amt'] = $qugeoDetails['quor_AmountCollectible'];
                    $transctionTemplate12['cr']['retailorStockinTransit']['amt'] = $qugeoDetails['quor_AmountCollectible'];

                    $search = array("#AMT#", "#ID#");
                    $replace = array($qugeoDetails['quor_AmountCollectible'], $quor_RefNo);
                    $transctionTemplate12['comments'] = str_replace($search, $replace, $transctionTemplate12['comments']);
                    if (strcmp($transctionTemplate['comments'], '') != 0) {
                        $fieldsl2 = array(
                            "waqu_TransDate" => date('Y-m-d'),
                            "waqu_comment" => $transctionTemplate12['comments'],
                            "waqu_SourceID" => intval($quor_id),
                            "waqs_id" => intval($wqSettingsl2['waqs_id']),
                            "waqu_Amount" => doubleval($qugeoDetails['quor_AmountCollectible']),
                            "br_id" => intval($updatedetails['quor_Deliverybr_id']),
                            "waqu_Data" => stripslashes(json_encode($transctionTemplate12))
                        );
                        $status = $db->perform('finascop_wallet_queue', $fieldsl2);
                    }
                }

                if ($B2COrderDetails['payment_mode'] == 7) {
                    //26-B2CCourierHomeDeliveryCOD 43
                    $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 43";
                    $wqSettingsl3 = $db->getFromDB($query, true);
                    $transctionTemplate13 = json_decode($wqSettingsl3['waqs_Configuration'], true);

                    $transctionTemplate13['dr']['retailerDeliveryCharges']['amt'] = $qugeoDetails['quor_AmountCollectible'];
                    //$transctionTemplate13['cr']['cashCollectibleatRetailor']['amt'] = $qugeoDetails['quor_AmountCollectible'];
                    $transctionTemplate13['cr']['courier']['amt'] = $qugeoDetails['quor_AmountCollectible'];
                    $transctionTemplate13['cr']['courier']['key'] = $accled_ReferenceIdcourier;

                    $search = array("#AMT#", "#NO#", "#ID#", "#NAME#", "#PHONE#");
                    $replace = array($qugeoDetails['quor_AmountCollectible'], $quor_id, $quor_RefNo, $defaulpaymentgateway, $mst_courier_phone);
                    $transctionTemplate13['comments'] = str_replace($search, $replace, $transctionTemplate13['comments']);
                    if (strcmp($transctionTemplate13['comments'], '') != 0) {
                        $fieldsl3 = array(
                            "waqu_TransDate" => date('Y-m-d'),
                            "waqu_comment" => $transctionTemplate13['comments'],
                            "waqu_SourceID" => intval($quor_id),
                            "waqs_id" => intval($wqSettingsl3['waqs_id']),
                            "waqu_Amount" => doubleval($qugeoDetails['quor_AmountCollectible']),
                            "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                            "waqu_Data" => stripslashes(json_encode($transctionTemplate13))
                        );
                        $status = $db->perform('finascop_wallet_queue', $fieldsl3);
                    }
                    //26-2-B2CcourierHomeDeliveryCOD

                    $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 65";
                    $wqSettingsl4 = $db->getFromDB($query, true);
                    $transctionTemplate14 = json_decode($wqSettingsl4['waqs_Configuration'], true);

                    $transctionTemplate14['dr']['retailerSales']['amt'] = $qugeoDetails['quor_AmountCollectible'];
                    $transctionTemplate14['cr']['retailorStockinTransit']['amt'] = $qugeoDetails['quor_AmountCollectible'];

                    $search = array("#AMT#", "#NO#", "#ID#");
                    $replace = array($qugeoDetails['quor_AmountCollectible'], $quor_id, $quor_RefNo);
                    $transctionTemplate14['comments'] = str_replace($search, $replace, $transctionTemplate14['comments']);
                    if (strcmp($transctionTemplate14['comments'], '') != 0) {
                        $fieldsl4 = array(
                            "waqu_TransDate" => date('Y-m-d'),
                            "waqu_comment" => $transctionTemplate14['comments'],
                            "waqu_SourceID" => intval($quor_id),
                            "waqs_id" => intval($wqSettingsl4['waqs_id']),
                            "waqu_Amount" => doubleval($qugeoDetails['quor_AmountCollectible']),
                            "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                            "waqu_Data" => stripslashes(json_encode($transctionTemplate14))
                        );
                        $status = $db->perform('finascop_wallet_queue', $fieldsl4);
                    }
                    //26-3 66
                    $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 66";
                    $wqSettingsl6 = $db->getFromDB($query, true);
                    $transctionTemplate16 = json_decode($wqSettingsl6['waqs_Configuration'], true);

                    $transctionTemplate16['cr']['cashCollectibleatRetailor']['amt'] = $qugeoDetails['quor_AmountCollectible'];
                    $transctionTemplate16['dr']['cahinHandCourier']['amt'] = $qugeoDetails['quor_AmountCollectible'];
                    $transctionTemplate16['dr']['cahinHandCourier']['key'] = $accled_ReferenceIdcourier;

                    $search = array("#AMT#", "#NO#", "#ID#", "#NAME#", "#PHONE#");
                    $replace = array($qugeoDetails['quor_AmountCollectible'], $quor_id, $quor_RefNo, $defaulpaymentgateway, $mst_courier_phone);
                    $transctionTemplate16['comments'] = str_replace($search, $replace, $transctionTemplate16['comments']);
                    if (strcmp($transctionTemplate16['comments'], '') != 0) {
                        $fields66 = array(
                            "waqu_TransDate" => date('Y-m-d'),
                            "waqu_comment" => $transctionTemplate16['comments'],
                            "waqu_SourceID" => intval($quor_id),
                            "waqs_id" => intval($wqSettingsl6['waqs_id']),
                            "waqu_Amount" => doubleval($qugeoDetails['quor_AmountCollectible']),
                            "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                            "waqu_Data" => stripslashes(json_encode($transctionTemplate16))
                        );
                        $status = $db->perform('finascop_wallet_queue', $fields66);
                    }
                }
            }
        }
        $fsto_isPurchaseReturn = $db->getItemFromDB("SELECT fsto_isPurchaseReturn FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}");
        if ($fsto_isPurchaseReturn == 0) {
            
        } else {
            if ($_SESSION['admin']->br_PyramidLevel == 4) {
                $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 51";
                $wqSettings = $db->getFromDB($query, true);
                $transctionTemplate1 = json_decode($wqSettings['waqs_Configuration'], true);
                $transctionTemplate1['cr']['retailerStockDamaged']['amt'] = round(doubleval($amount), 2);
                $transctionTemplate1['cr']['retailerStockDamaged']['br_ReferenceID'] = $db->getItemFromDB("SELECT br_ReferenceID FROM finascop_branch WHERE br_ID = {$sorceDestination['fsto_source']}");
                $transctionTemplate1['dr']['distributorStockDamaged']['amt'] = round(doubleval($amount), 2);
                $transctionTemplate1['dr']['distributorStockDamaged']['br_ReferenceID'] = $db->getItemFromDB("SELECT br_ReferenceID FROM finascop_branch WHERE br_ID = {$sorceDestination['fsto_destination']}");
            } else {
                $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 54";
                $wqSettings = $db->getFromDB($query, true);
                $transctionTemplate1 = json_decode($wqSettings['waqs_Configuration'], true);
                $transctionTemplate1['cr']['distributStockDamaged']['amt'] = round(doubleval($amount), 2);
                $transctionTemplate1['dr']['csStockInTransit']['amt'] = round(doubleval($amount), 2);
            }
        }
        //print_r($transctionTemplate1);




        $search = array("#AMT#", "#NO#", "#ID#");
        $replace = array($commentsamt, $quor_id, $quor_RefNo);
        $transctionTemplate1['comments'] = str_replace($search, $replace, $transctionTemplate1['comments']);


        if (strcmp($transctionTemplate1['comments'], '') != 0) {

            $fields = array(
                "waqu_TransDate" => date('Y-m-d'),
                "waqu_comment" => $transctionTemplate1['comments'],
                "waqu_SourceID" => intval($quor_id),
                "waqs_id" => intval($wqSettings['waqs_id']),
                "waqu_Amount" => round(doubleval($amount), 2),
                "br_id" => intval($_SESSION['admin']->finascop_current_branch_id),
                "waqu_Data" => stripslashes(json_encode($transctionTemplate1))
            );
            $status = $db->perform('finascop_wallet_queue', $fields);
        }

//        print_r($wqSettings);
        //print_r($transctionTemplate1);
//        exit();
        //BEGIN FINANCIAL TRANSACION
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Delivered. '}";
        } else {
            echo "{'success':false,'valid':false,'msg': 'Error While Converting.'}";
        }
        break;
}	