<?php

require_once(INCLUDE_PATH . '/lib.php');
require_once(INCLUDE_PATH . '/config.php');

function loadVehicleDetails($action)
{
    global $db;
    $longitude = $_POST['longitude'];
    $latitude = $_POST['latitude'];

    if (!empty($_POST['br_id'])) {

        $nodb = new \cgoDynamiteDB();
        $degMat = new \cgoGeoUtilities();
        $arrDegrees = $degMat->getDegreeMatrix($longitude, $latitude, QC_VEHICLE_NEAR_PICKUP_CIRCLE_DIST);

        $arrVehicle = array();
        $arrVehicle['PartitionKey'] = array('col' => 'Is_Live', 'val' => 1, 'oper' => '=');
        $arrVehicle['SortKey'] = array('col' => 'Latitude', 'val1' => (float) $arrDegrees['lat1'], 'val2' => (float) $arrDegrees['lat2'], 'SortKeyBetween' => true);
        //$arrVehicle['SortKey']=array('col'=>'Latitude','type'=>'N','val'=>$arrDegrees['lat1'],'oper'=>'=');				
        $arrVehicle['IndexName'] = 'Is_Live-Latitude-index';
        $arrVehicle['queryAttributes'] = array('apikey', 'v_id', 'v_no', 'Latitude', 'Longitude', 'LocationUpdateddatetime', 'DriverName', 'v_typename', 'v_capacity', 'CurrentLoadedWeight', 'v_MapIcon');
        $arrVehicle['Condition'] = array();
        array_push($arrVehicle['Condition'], array('col' => 'Longitude', 'val1' => (float) $arrDegrees['lon1'], 'val2' => (float) $arrDegrees['lon2'], 'ConditionBetween' => true));

        $rsno = $nodb->query('QugeoLiveVehicles', $arrVehicle, 'query');
        if (isset($rsno) && count($rsno) > 0) {
            $rs = array();
            foreach ($rsno as $value) {
                array_push($rs, array('v_ID' => $value['apikey'], 'v_No' => $value['v_no'], 'Latitude' => $value['Latitude'], 'Longitude' => $value['Longitude'], 'LastLocationDtTm' => $value['LocationUpdateddatetime'], 'DriverName' => $value['DriverName'], 'Vehicletypename' => $value['v_typename'], 'MaxLoad' => $value['v_capacity'], 'CurrentLoad' => $value['CurrentLoadedWeight'], 'v_MapIcon' => $value['v_MapIcon']));
            }
            $count = count($rs);
            $rs = json_encode($rs);
        } else {
            $count = 0;
            $rs = '[]';
        }
        echo '{"totalCount":' . $count . ',"data":' . $rs . '}';
    } else {
        echo '{"totalCount":0,"data":[]}';
    }
}

function loadScheduleDetails()
{
    $nodb = new \cgoDynamiteDB();
    global $db;
    $recLimit = intval($_POST['limit']);
    $recStart = intval($_POST['start']);
    $recSort = $_POST['sort'];
    $recSortDir = $_POST['dir'];
    $br_id = $_POST['br_id'];
    $recLimit = $recLimit == 0 ? 20 : $recLimit;

    $ind = intval($_POST['ind']);
    if (!empty($br_id)) {
        if ($ind == 1) {
            $qry = "SELECT COUNT(*) FROM " . FINASCOP_DB . "qugeo_order " . "WHERE  " . "((((quor_PickupToBeManual =1 AND quor_Status =" . ORDER_PICKUP_AT_ORIGIN_DLS_ID . ") OR (quor_Status =" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ")) AND quor_Pickupbr_id = " . $br_id . ") OR " . " (quor_DeliveryToBeManual =1 AND quor_Status = " . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " AND quor_Deliverybr_id =" . $br_id . " ))  ";

            $query = "SELECT quor_RefNo as booking_no,quor_Date as booked_at,"
                . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " or quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",quor_PickupName,quor_DeliveryName) as customer,"
                . "quor_PickupLocation as source,"
                . "quor_DeliveryLocation as destination,"
                . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . ",'DELIVER', if(quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",'DISPATCH','PICKUP')) as type,"
                . "quor_id, "
                . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " or quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",quor_DeliveryLat,quor_PickupLat) as latitude,"
                . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " or quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",quor_DeliveryLng,quor_PickupLng) as longitude, "
                . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " or quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",' " . GMAP_DELIVERY_ICON . "','" . GMAP_PIKCUP_ICON . "') as mapicon, "
                . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . "," . FINASCOP_DB . "getBranchGodownGeoLocation(quor_Deliverybr_id,1), " . FINASCOP_DB . "getBranchGodownGeoLocation(quor_Pickupbr_id,1)) as brGodownLati,"
                . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . "," . FINASCOP_DB . "getBranchGodownGeoLocation(quor_Deliverybr_id,2), " . FINASCOP_DB . "getBranchGodownGeoLocation(quor_Pickupbr_id,2)) as brGodownLong"
                . " FROM " . FINASCOP_DB . " qugeo_order "
                . "WHERE   quor_Type = 1 AND "
                . "((((quor_PickupToBeManual =1 AND quor_Status =" . ORDER_PICKUP_AT_ORIGIN_DLS_ID . ") OR (quor_Status =" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ")) AND quor_Pickupbr_id = " . $br_id . ") OR "
                . " (quor_DeliveryToBeManual =1 AND quor_Status = " . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " AND quor_Deliverybr_id =" . $br_id . " ))  "
                . " ORDER BY quor_Date ASC LIMIT $recStart,$recLimit";
            $data = $db->getMultipleData($query, true);
            $totalCount = $db->getItemFromDB($qry);
        } else {

            $qry = "SELECT COUNT(*) FROM " . FINASCOP_DB . "qugeo_order "
                . "WHERE (quor_Status between " . ORDER_PICKUP_FLAGGED_TOBR_DLS_ID . " AND "
                . ORDER_PICKUP_PICKEDUP_TODST_DLS_ID
                . " OR quor_Status =" . ORDER_DELIVERY_OUT_FOR_DELIVERY . ") AND "
                . $br_id . " = if(quor_Status<>" . ORDER_DELIVERY_OUT_FOR_DELIVERY . ",quor_Pickupbr_id,quor_Deliverybr_id)";

            $query = "SELECT quor_RefNo as booking_no,quor_Date as booked_at,"
                . " if(quor_Status =" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . ",quor_PickupName,quor_DeliveryName) as customer,"
                . "quor_PickupLocation as source,"
                . "quor_DeliveryLocation as destination,"
                . "if(quor_Status <>" . ORDER_DELIVERY_OUT_FOR_DELIVERY . ",' " . GMAP_PIKCUP_ICON . "','" . GMAP_DELIVERY_ICON . "') as mapicon,"
                . "if(quor_Status <>" . ORDER_DELIVERY_OUT_FOR_DELIVERY . ",'BOOKING','DELIVERY') as type,"
                . "if(quor_Status <>" . ORDER_DELIVERY_OUT_FOR_DELIVERY . ",quor_QugeoPickupDDBOrderId,quor_QugeoDeliveryDDBOrderId) as orderid,"
                . "if(quor_Status<>" . ORDER_DELIVERY_OUT_FOR_DELIVERY . ",quor_PickupLat,quor_DeliveryLat) as latitude,"
                . "if(quor_Status<>" . ORDER_DELIVERY_OUT_FOR_DELIVERY . ",quor_PickupLng,quor_DeliveryLng) as longitude,"
                . "'Paid' as bk_paymode,"
                . "'0' as bk_netamt,"
                . "if(quor_Status <>" . ORDER_DELIVERY_OUT_FOR_DELIVERY . ",quor_PickupToBeManual,quor_DeliveryToBeManual) as ismanual,"
                . "quor_id FROM " . FINASCOP_DB . "qugeo_order "
                . "WHERE   ((quor_Status between " . ORDER_PICKUP_FLAGGED_TOBR_DLS_ID . " AND " . ORDER_PICKUP_PICKEDUP_TODST_DLS_ID
                . " AND quor_Pickupbr_id = " . $br_id . " )OR (quor_Status =" . ORDER_DELIVERY_OUT_FOR_DELIVERY . " AND quor_Deliverybr_id =" . $br_id . ")) "
                . " ORDER BY quor_Date ASC LIMIT $recStart,$recLimit";
            $data = $db->getMultipleData($query, true);
            $totalCount = $db->getItemFromDB($qry);
            foreach ($data as $value) {
                $arrOrder = array();
                $arrOrder['PartitionKey'] = array('col' => 'orderid', 'val' => $value['orderid'], 'oper' => '=');
                $arrOrder['getAttributes'] = array('Acceptedapikey', 'OrderStatus', 'updateddatetime');
                $ordrs = $nodb->query('QugeoOrderDetails', $arrOrder, 'getItem');
                if (isset($ordrs) && count($ordrs) > 0 && $ordrs['Acceptedapikey'] != '') {
                    $arrAPI = array();
                    $arrAPI['PartitionKey'] = array('col' => 'apikey', 'val' => $ordrs['Acceptedapikey'], 'oper' => '=');
                    $arrAPI['IndexName'] = 'apikey-index';
                    $arrAPI['queryAttributes'] = array('extrainfo');
                    $rsno = $nodb->query('APISession', $arrAPI, 'query');
                    if (isset($rsno) && count($rsno) > 0) {
                        $vehno = $rsno[0]['extrainfo'];
                        $value['vehno'] = $vehno['v_no'];
                        $value['status'] = $ordrs['OrderStatus'];
                        $value['statustime'] = $ordrs['updateddatetime'];
                    } else {
                        $value['vehno'] = '';
                        $value['status'] = '';
                        $value['statustime'] = '';
                    }
                } else {
                    $value['vehno'] = '';
                    $value['status'] = '';
                    $value['statustime'] = '';
                }
            }
        }

        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
    } else {
        echo '{"totalCount":0,"data":[]}';
    }
}

function listLiveVehicles()
{
    $nodb = new \cgoDynamiteDB();


    $arrOrder = array();
    //$arrOrder['PartitionKey'] = array('col' => 'Is_Live', 'val' => 1, 'oper' => '=');
    if ($_SESSION['admin']->br_PyramidLevel == 1) {
        $arrOrder['PartitionKey'] = array('col' => 'Is_Live', 'val' => (int) 1, 'oper' => '=');
        $arrOrder['IndexName'] = 'Is_Live-index';
        $arrOrder['queryAttributes'] = array('DriverName', 'v_no', 'v_typename', 'createddatetime', 'TotalJobs', 'Home_Longitude', 'AssignedLoadedWeight', 'AssignedLoadedVolume', 'CurrentLoadedVolume', 'CurrentLoadedWeight', 'apikey', 'DriverId', 'mobno');


        /*$arrOrder['PartitionKey'] = array('col' => 'Is_Live', 'val' => (int) 1, 'oper' => '=');
        $arrOrder['SortKey'] = array('col' => 'createddate', 'val' => (int) str_replace('-', '', $_POST['date']), 'oper' => '=');
        $arrOrder['IndexName'] = 'Is_Live-createddate-index';
        $arrOrder['queryAttributes'] = array('DriverName', 'v_no', 'v_typename', 'createddatetime', 'TotalJobs', 'Home_Longitude', 'AssignedLoadedWeight', 'AssignedLoadedVolume', 'CurrentLoadedVolume', 'CurrentLoadedWeight', 'apikey', 'DriverId', 'mobno');*/
    } else {
        $arrOrder['PartitionKey'] = array('col' => 'ReportingBranch', 'val' => (int) $_POST['br_id'], 'oper' => '=');
        $arrOrder['SortKey'] = array('col' => 'createddate', 'val' => (int) str_replace('-', '', $_POST['date']), 'oper' => '=');
        $arrOrder['IndexName'] = 'ReportingBranch-createddate-index';
        $arrOrder['queryAttributes'] = array('DriverName', 'v_no', 'v_typename', 'createddatetime', 'TotalJobs', 'Home_Longitude', 'AssignedLoadedWeight', 'AssignedLoadedVolume', 'CurrentLoadedVolume', 'CurrentLoadedWeight', 'apikey', 'DriverId', 'mobno');
        $arrOrder['Condition'] = array();
        array_push($arrOrder['Condition'], array('col' => 'Is_Live', 'val' => 1, 'oper' => '='));
    }




    $response = array();
    $rsno = $nodb->query('QugeoLiveVehicles', $arrOrder, 'query');
    if (isset($rsno) && count($rsno) > 0) {
        foreach ($rsno as $vehicleapi) {
            $apikey = $vehicleapi['apikey'];
            array_push($response, array('vehno' => $vehicleapi['v_no'], 'DriverId' => $vehicleapi['DriverId'], 'drivername' => $vehicleapi['DriverName'], 'mobno' => $vehicleapi['mobno'], 'logintime' => $vehicleapi['createddatetime'], 'vtype' => $vehicleapi['v_typename'], 'assgwt' => $vehicleapi['AssignedLoadedWeight'], 'assgvol' => $vehicleapi['AssignedLoadedVolume'], 'currwt' => $vehicleapi['CurrentLoadedWeight'], 'currvol' => $vehicleapi['CurrentLoadedVolume'], 'totjobs' => $vehicleapi['TotalJobs'], 'jobscompleted' => '0', 'kmcovered' => '0', 'vehid' => $apikey));
        }
    }

    echo '{"totalCount":' . count($response) . ',"data":' . json_encode($response) . '}';
}

function listschroute()
{
    $nodb = new \cgoDynamiteDB();
    $arrLive = array();
    $arrLive['PartitionKey'] = array('col' => 'apikey', 'val' => $_POST['vehapikey'], 'oper' => '=');
    $arrLive['getAttributes'] = array('Home_Latitude', 'Home_Longitude');
    $nors = $nodb->query('QugeoLiveVehicles', $arrLive, 'getItem');
    $HomeLat = 0;
    $HomeLong = 0;
    if (isset($nors) && count($nors) > 0) {
        $HomeLat = $nors['Home_Latitude'];
        $HomeLong = $nors['Home_Longitude'];
    }

    $arrAPI = array();
    $arrAPI['PartitionKey'] = array('col' => 'apikey', 'val' => $_POST['vehapikey'], 'oper' => '=');
    $arrAPI['SortKey'] = array('col' => 'IsClosed', 'val' => 10, 'oper' => '<');
    $arrAPI['IndexName'] = 'apikey-IsClosed-index';
    $arrAPI['queryAttributes'] = array('order', 'Latitude', 'Longitude');
    $rsno = $nodb->query('QugeoLiveVehicleOrders', $arrAPI, 'query');
    $arr = array();
    $order = array();
    $MaxOrder = 0;

    if (isset($rsno) && count($rsno) > 0) {
        foreach ($rsno as $value) {
            array_push($arr, array('Latitude' => $value['Latitude'], 'Longitude' => $value['Longitude'], 'Order' => $value['order']));
            if ($MaxOrder < (float) $value['order']) {
                $MaxOrder = (float) $value['order'];
            }
        }
        //echo  $HomeLat ." -- " . $HomeLong . " -- " . $MaxOrder ;
        if ($HomeLat > 0 && $HomeLong > 0) {
            array_push($arr, array('Latitude' => $HomeLat, 'Longitude' => $HomeLong, 'Order' => ($MaxOrder + 1)));
        }
        finascop_aasort($arr, 'Order');
    }
    $polyLine = json_encode($arr);

    echo '{success:true,"routelocations":' . $polyLine . '}';
}

function listactroute()
{
    $nodb = new \cgoDynamiteDB();
    $arrAPI = array();
    $arrAPI['PartitionKey'] = array('col' => 'apikey', 'val' => $_POST['vehapikey'], 'oper' => '=');
    $arrAPI['IndexName'] = 'apikey-index';
    $arrAPI['queryAttributes'] = array('longitude', 'latitude', 'tstamp', 'userdatetime', 'disttravled', 'provider', 'version');
    $rsno = $nodb->query('QugeoEventGeoLocations', $arrAPI, 'query');
    $arr = array();
    if (isset($rsno) && count($rsno) > 0) {
        foreach ($rsno as $value) {
            array_push($arr, array('Latitude' => $value['latitude'], 'Longitude' => $value['longitude'], 'tstamp' => substr($value['tstamp'], 0, 14), 'apptime' => $value['userdatetime'], 'disttravled' => $value['disttravled'], 'version' => $value['version'], 'provider' => $value['provider']));
        }
        finascop_aasort($arr, 'apptime');
    }
    $polyLine = json_encode($arr);

    echo '{success:true,"polylines":' . $polyLine . '}';
}

function getVehiclesOfTheDay()
{
    global $db;
    $nodb = new \cgoDynamiteDB();

    $arrVehicleOrder = array();
    $arrVehicleOrder['PartitionKey'] = array('col' => 'createddate', 'val' => (int) str_replace('-', '', $_POST['date']), 'oper' => '=');
    $arrVehicleOrder['SortKey'] = array('col' => 'HandlingBranch', 'val' => (int) $_POST['br_id'], 'oper' => '=');
    $arrVehicleOrder['IndexName'] = 'createddate-HandlingBranch-index';
    $arrVehicleOrder['queryAttributes'] = array('Acceptedapikey');
    $arrVehicleOrder['Condition'] = array();
    array_push($arrVehicleOrder['Condition'], array('col' => 'Acceptedapikey', 'val' => '-', 'oper' => '<>'));
    $rsno = $nodb->query('QugeoOrderDetails', $arrVehicleOrder, 'query');
    if (isset($rsno) && count($rsno) > 0) {
        $rs = array();
        foreach ($rsno as $vehicleapi) {
            array_push($rs, $vehicleapi['Acceptedapikey']);
        }

        $rs = array_unique($rs);

        return $rs;
    } else {
        return null;
    }
}

function loadHistoryStore()
{
    global $db;
    $nodb = new \cgoDynamiteDB();

    $vehicles = getVehiclesOfTheDay();
    if (count($vehicles) > 0) {
        $rs = array();
        foreach ($vehicles as $value) {
            $arrVehicle = array();
            $arrVehicle['PartitionKey'] = array('col' => 'apikey', 'val' => $value, 'oper' => '=');
            $arrVehicle['queryAttributes'] = array('apikey', 'v_no', 'createddatetime');
            $arrVehicle['Condition'] = array();
            array_push($arrVehicle['Condition'], array('col' => 'Is_Live', 'val' => 0, 'oper' => '='));
            $nors = $nodb->query('QugeoLiveVehicles', $arrVehicle, 'query');
            if (isset($nors) && count($nors) > 0) {
                array_push($rs, array('v_no' => $nors[0]['v_no'] . '_' . date("H:i:s", strtotime($nors[0]['createddatetime'])), 'v_id' => $nors[0]['apikey']));
            }
        }
        $count = count($rs);
        $rs = json_encode($rs);
    } else {
        $count = 0;
        $rs = '[]';
    }
    echo '{"totalCount":' . $count . ',"data":' . $rs . '}';
}

function listVehicleHistory()
{
    $nodb = new \cgoDynamiteDB();

    $arrOrder = array();

    $arrOrder['SortKey'] = array('col' => 'createddate', 'val' => (int) str_replace('-', '', $_POST['date']), 'oper' => '=');

    if ($_SESSION['admin']->br_PyramidLevel == 1) {
        $arrOrder['PartitionKey'] = array('col' => 'Is_Live', 'val' => (int) 0, 'oper' => '=');
        $arrOrder['IndexName'] = 'Is_Live-createddate-index';
        $arrOrder['queryAttributes'] = array('DriverName', 'v_no', 'v_typename', 'createddatetime', 'TotalJobs', 'Home_Longitude', 'AssignedLoadedWeight', 'AssignedLoadedVolume', 'CurrentLoadedVolume', 'CurrentLoadedWeight', 'apikey', 'mobno', 'IsCleanLogout', 'LoggedOutAt', 'KmsCovered');
    } else {
        $arrOrder['PartitionKey'] = array('col' => 'ReportingBranch', 'val' => (int) $_POST['br_id'], 'oper' => '=');
        $arrOrder['IndexName'] = 'ReportingBranch-createddate-index';
        $arrOrder['queryAttributes'] = array('DriverName', 'v_no', 'v_typename', 'createddatetime', 'TotalJobs', 'Home_Longitude', 'AssignedLoadedWeight', 'AssignedLoadedVolume', 'CurrentLoadedVolume', 'CurrentLoadedWeight', 'apikey', 'mobno', 'IsCleanLogout', 'LoggedOutAt', 'KmsCovered');
        $arrOrder['Condition'] = array();
        array_push($arrOrder['Condition'], array('col' => 'Is_Live', 'val' => 0, 'oper' => '='));
    }


    /*if ($_SESSION['admin']->br_PyramidLevel == 1) {
        if ($_SESSION['admin']->IsSuperUser == 'Yes') {
            array_push($arrOrder['Condition'], array('col' => 'ReportingBranch', 'val' => (int) 0, 'oper' => '>'));
            $arrOrder['PartitionKey'] = array('col' => 'ReportingBranch', 'val' => (int) 0, 'oper' => '>');
        } else {
            $arrOrder['PartitionKey'] = array('col' => 'ReportingBranch', 'val' => (int) $_POST['br_id'], 'oper' => '=');
        }
    } else {
        $arrOrder['PartitionKey'] = array('col' => 'ReportingBranch', 'val' => (int) $_POST['br_id'], 'oper' => '=');
    }

    $arrOrder['IndexName'] = 'ReportingBranch-createddate-index';
    $arrOrder['queryAttributes'] = array('DriverName', 'v_no', 'v_typename', 'createddatetime', 'TotalJobs', 'Home_Longitude', 'AssignedLoadedWeight', 'AssignedLoadedVolume', 'CurrentLoadedVolume', 'CurrentLoadedWeight', 'apikey', 'mobno', 'IsCleanLogout', 'LoggedOutAt', 'KmsCovered');
    $arrOrder['Condition'] = array();
    array_push($arrOrder['Condition'], array('col' => 'Is_Live', 'val' => 0, 'oper' => '='));*/


    $response = array();
    $rsno = $nodb->query('QugeoLiveVehicles', $arrOrder, 'query');

    if (isset($rsno) && count($rsno) > 0) {
        foreach ($rsno as $vehicleapi) {
            $apikey = $vehicleapi['apikey'];
            array_push($response, array('vehno' => $vehicleapi['v_no'], 'drivername' => $vehicleapi['DriverName'], 'mobno' => $vehicleapi['mobno'], 'logintime' => $vehicleapi['createddatetime'], 'vtype' => $vehicleapi['v_typename'], 'assgwt' => $vehicleapi['AssignedLoadedWeight'], 'assgvol' => $vehicleapi['AssignedLoadedVolume'], 'currwt' => $vehicleapi['CurrentLoadedWeight'], 'currvol' => $vehicleapi['CurrentLoadedVolume'], 'totjobs' => $vehicleapi['TotalJobs'], 'jobscompleted' => '0', 'kmcovered' => $vehicleapi['KmsCovered'], 'vehid' => $apikey, 'IsCleanLogout' => getLogOutType($vehicleapi['IsCleanLogout']), 'LoggedOutAt' => $vehicleapi['LoggedOutAt']));
        }
    }

    echo '{"totalCount":' . count($response) . ',"data":' . json_encode($response) . '}';
}

function getLogOutType($type)
{
    switch ($type) {
        case "1":
            return "Clean";
            break;
        case "2":
            return "Crash";
            break;
        case "3":
            return "TimeOut";
            break;
        case "4":
            return "Forced";
            break;
        default:
            return '-';
    }
}

function getSnapRoad()
{
    $nodb = new \cgoDynamiteDB();
    $arrAPI = array();
    $arrAPI['PartitionKey'] = array('col' => 'apikey', 'val' => $_POST['vehapikey'], 'oper' => '=');
    $arrAPI['IndexName'] = 'apikey-index';
    $arrAPI['queryAttributes'] = array('longitude', 'latitude', 'tstamp', 'userdatetime', 'disttravled', 'provider', 'version');
    $rsno = $nodb->query('QugeoEventGeoLocations', $arrAPI, 'query');
    $arr = array();
    if (isset($rsno) && count($rsno) > 0) {
        foreach ($rsno as $value) {
            array_push($arr, array('Latitude' => $value['latitude'], 'Longitude' => $value['longitude'], 'tstamp' => substr($value['tstamp'], 0, 14), 'apptime' => $value['userdatetime'], 'disttravled' => $value['disttravled'], 'version' => $value['version'], 'provider' => $value['provider']));
        }
        finascop_aasort($arr, 'apptime');
    }

    $param = '';
    $road_arr = array();
    foreach ($arr as $index => $value) {
        $param .= $value['Latitude'] . ',' . $value['Longitude'];
        if (($index + 1) % 100 == 0) {
            $obj = new cgoGeoUtilities();
            $road_data = $obj->getSnapToRoad($param);
            $param = '';
            if (!empty($road_data)) {
                /*  $res_data = json_decode($road_data); */

                $road_arr = array_merge($road_arr, $road_data);
            }
        } else {
            if (count($arr) - 1 !== $index) {
                $param .= '|';
            }
        }
    }
    if (!empty($param)) {
        $obj = new cgoGeoUtilities();
        $road_data = $obj->getSnapToRoad($param);
        $param = '';
        if (!empty($road_data)) {
            $road_arr = array_merge($road_arr, $road_data);
        }
    }

    $response = array();
    foreach ($road_arr as $key => $val) {
        array_push($response, array('Latitude' => $val['location']['latitude'], 'Longitude' => $val['location']['longitude'], 'tstamp' => '20161104131415', 'apptime' => '20161104131415', 'disttravled' => 0, 'version' => '1', 'provider' => 'ABC'));
    }
    if (!empty($response)) {
        $polyLine = json_encode($response);

        echo '{success:true,"polylines":' . $polyLine . '}';
    } else {
        echo '{success:true,"polylines":"[]"}';
    }
}

function isliveDriverSSession($apikey)
{
    /* $nodb = new \cgoDynamiteDB();
      $arrAPI = array();
      $arrAPI['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
      $arrAPI['IndexName'] = 'apikey-index';
      $arrAPI['queryAttributes'] = array('validtill', 'id', 'usertype', 'extrainfo');
      $rsno = $nodb->query('APISession', $arrAPI, 'query');
      //Extend the Session
      if (isset($rsno) && count($rsno) > 0) {
      return true;
      } else {
      return false;
      } */
    global $db;
    $qry = "select count(*) as ss from qugeo_driver where d_apikey = '" . $apikey . "'";
    return intval($db->getItemFromDB($qry));
}

function getUsedVehicleDetails($apikey)
{
    $nodb = new \cgoDynamiteDB();
    $arrLive = array();
    $arrLive['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
    $arrLive['getAttributes'] = array('v_no', 'createddatetime', 'DriverName');
    $nors = $nodb->query('QugeoLiveVehicles', $arrLive, 'getItem');

    if (isset($nors) && count($nors) > 0) {
        return $nors;
    } else {
        return [];
    }
}

function loadInTransitDetails()
{


    $nodb = new \cgoDynamiteDB();
    global $db;
    $recLimit = intval($_POST['limit']);
    $recStart = intval($_POST['start']);
    $sort = $_POST['sort'];
    $dir = $_POST['dir'];
    $sort = empty($sort) ? 'quor_id' : $sort;
    $dir = empty($dir) ? 'DESC' : $dir;
    $br_id = $_POST['br_id'];
    $recLimit = $recLimit == 0 ? 20 : $recLimit;
    $filter = $_POST['filter'];
    if (isset($filter)) {
        foreach ($filter as $key => $field) {
            switch ($field['data']['type']) {
                case 'string':
                    if ($field['data']['value'] != "") {
                        $checkComa = strstr($field['data']['value'], ',');
                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            if ($field['field'] == 'booking_no') {
                                $search .= " and (quor_RefNo LIKE '{$field[data][value]}%') ";
                            } else {
                                $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                            }
                        }
                    }
                    break;
                case 'list':

                    if ($field['field'] == 'dls_DelStatus') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $search .= " AND dls_DelStatus IN ('" . $fiterItem . "')";
                    }
                    break;
            }
        }
    }
    if ($sort == 'booked_at') {
        $sort = 'quor_id';
    }
    $ind = intval($_POST['ind']);
    if ($_SESSION['admin']->br_PyramidLevel == 1) {
        $brQuery = " ";
        /*if ($_SESSION['admin']->IsSuperUser == 'Yes') {
            $brQuery = " ";
        } else {
            $brQuery = " AND quor_Deliverybr_id = {$br_id} ";
        }*/
    } else {
        $brQuery = " AND quor_Deliverybr_id = {$br_id} ";
    }
    if (!empty($br_id)) {
        $qry = "SELECT COUNT(*) FROM " . FINASCOP_DB . "qugeo_order " . " INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status WHERE  ((quor_type in (2,3,4) and quor_Status=9 ) or (quor_type=1 and quor_Status in (23,24,25,26,27,28,29,32,33,34,35,36,37,38)) ) {$brQuery} ";

        $query = "SELECT quor_RefNo as booking_no,DATE_FORMAT(quor_Date,'%d-%m-%Y') as booked_at,"
            . "quor_DeliveryName,quor_DeliveryPhone,quor_Deliverybr_id,quor_Pickupbr_id,(SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_source) "
            . " AS source,quor_UpdateOn,"
            . " CASE WHEN fsto_destinationtype = 1 THEN (SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_destination) "
            . " WHEN fsto_destinationtype = 2 THEN (SELECT cust_customer_name FROM retaline_customer WHERE cust_id =  fsto_destination) "
            . " WHEN fsto_destinationtype = 3 THEN (SELECT b2b_Customer_Name FROM retaline_B2Bcustomer where b2b_Customer_ID  = fsto_destination) END as destination,"
            . " CASE WHEN fsto_destinationtype = 1 THEN (SELECT br_Phone FROM finascop_branch WHERE br_ID = fsto_destination) "
            . " WHEN fsto_destinationtype = 2 THEN (SELECT cust_mobile FROM retaline_customer WHERE cust_id =  fsto_destination) "
            . " WHEN fsto_destinationtype = 3 THEN (SELECT b2b_Customer_Mobile FROM retaline_B2Bcustomer where b2b_Customer_ID  = fsto_destination) END as dest_phone,"
            . "if(quor_Status=22,'PICKUP', if(quor_Status=31,'DELIVERY','')) as drivetype,"
            . "quor_id,quor_PickupLat,quor_PickupLng,quor_DeliveryLat,quor_DeliveryLng,"
            . " '" . GMAP_DELIVERY_ICON . "' as deliverymapicon ,'" . GMAP_PIKCUP_ICON . "' as pickupmapicon, "
            . "dls_DelStatus,quor_Status,"
            . "CASE WHEN quor_Type=1 THEN 'Drive' WHEN quor_Type=2 THEN 'Hired' WHEN quor_Type=3 THEN 'Customer Pickup' WHEN quor_Type=4 THEN 'Courier' WHEN quor_Type=5 THEN 'Driver Pickup' WHEN quor_Type=6 THEN 'Manual Delivery' END AS quor_TypeName,quor_Type,"
            . "DATE_FORMAT(quor_ScheduleOpeningTime,'%d-%m-%Y %H:%i:%s') as quor_ScheduleOpeningTime,quor_QugeoPickupDDBOrderId,quor_AmountCollectible,quor_Paymode,quor_QugeoPickupDDBDriverId,quor_QugeoDeliveryDDBDriverId "
            . " FROM " . FINASCOP_DB . " qugeo_order INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status "
            . " INNER JOIN finascop_stock_transfer_order ON quor_TransferOrder_id = fsto_id"
            . " WHERE ((quor_type in (2,3,4,6) and quor_Status=9 ) or (quor_type=1 and quor_Status in (9,10,11,12,13,14,23,24,25,26,27,28,29,32,33,34,35,36,37,38)) ) {$brQuery} {$search} "
            . " ORDER BY /*CAST({$sort} as char) {$dir},binary */{$sort} {$dir} LIMIT $recStart,$recLimit";
        $data = $db->getMultipleData($query, true);
        $resCount = count($data);
        if (!empty($data)) {
            for ($i = 0; $i < $resCount; $i++) {
                if ($data[$i]['quor_QugeoDeliveryDDBOrderId'] == '') {
                    $data[$i]['orderid'] = $data[$i]['quor_QugeoPickupDDBOrderId'];
                } else {
                    $data[$i]['orderid'] = $data[$i]['quor_QugeoDeliveryDDBOrderId'];
                }
                if ($data[$i]['quor_QugeoDeliveryDDBDriverId'] == '') {
                    $data[$i]['driverid'] = $data[$i]['quor_QugeoPickupDDBDriverId'];
                } else {
                    $data[$i]['driverid'] = $data[$i]['quor_QugeoDeliveryDDBDriverId'];
                }
                $data[$i]['date'] = $data[$i]['booked_at'];
                $data[$i]['quor_RefNo'] = $data[$i]['booking_no'];
                $data[$i]['From'] = $data[$i]['source'];
                $data[$i]['To'] = $data[$i]['destination'];
                $data[$i]['NetAmt'] = $data[$i]['quor_AmountCollectible'];
                $data[$i]['Paymode'] = $data[$i]['quor_Paymode'];
                $data[$i]['HasReCalculatedCharges'] = 0;
                $data[$i]['ReCalculatedCharges'] = 0;
                $data[$i]['ReCalculcationPaymentType'] = -1;
                if (in_array($data[$i]['quor_Status'], array(23, 24, 25, 26, 27, 28, 29, 35, 36, 37, 10, 11, 12))) {
                    $data[$i]['IsPickup'] = 1;
                }
                if (($data[$i]['quor_Status'] >= 32) && ($data[$i]['quor_Status'] <= 38)) {
                    $data[$i]['IsPickup'] = 0;
                }
                $data[$i]['status'] = $data[$i]['djStatus'];
                if ($data[$i]['quor_Type'] > 1) {
                    $data[$i]['IsEditable'] = false;
                }
                $data[$i]['v_no'] = "";
                switch ($data[$i]['quor_Type']) {
                    case 1:
                        $activedriveid = ($data[$i]['quor_QugeoPickupDDBDriverId'] == '' ? $data[$i]['quor_QugeoDeliveryDDBDriverId'] : $data[$i]['quor_QugeoPickupDDBDriverId']);
                        $activepoll = isliveDriverSSession($activedriveid);
                        $data[$i]['activedriveid'] = $activedriveid;
                        $data[$i]['IsEditable'] = (($activepoll == 0 || $data[$i]['IsPickup'] == 1) ? true : false); //check if vehicle live VISHNU    
                        //$data[$i]['IsEditable'] = ($data[$i]['IsPickup'] == 1 ? true : false);  
                        if ($activedriveid != '') {
                            $vehicleapi = getUsedVehicleDetails($activedriveid);
                            if (isset($vehicleapi['v_no'])) {
                                $data[$i]['v_no'] = $vehicleapi['v_no'];
                                $data[$i]['session'] = date("Y-m-d H:i:s", strtotime($vehicleapi['createddatetime']));
                            }
                        }
                        break;
                    case 2:
                        $disdata = $db->getFromDB("SELECT bcd_id,bcd_vehicleNo,bcd_driver,TIME_FORMAT(bcd_dispatchTime, '%r') AS bcd_dispatchTime,DATE_FORMAT(bcd_dispatchDate, '%Y-%m-%d')  AS bcd_dispatchDate,bcd_dispatchTime  "
                            . "FROM qugeo_order_dispatch WHERE quor_id = {$data[$i]['quor_id']}", true);
                        $data[$i]['v_no'] = $disdata['bcd_vehicleNo'];
                        $data[$i]['session'] = $disdata['bcd_dispatchDate'] . ' ' . $disdata['bcd_dispatchTime'];
                        break;
                    default:
                        $data[$i]['v_no'] = '-';
                        $data[$i]['session'] = '-';
                        break;
                }

                $data[$i]['BkLastEditTime'] = $data[$i]['quor_UpdateOn'];
                $data[$i]['status_time'] = $data[$i]['quor_UpdateOn'];
                $data[$i]['dls_id'] = $data[$i]['quor_Status'];
            }
        }

        $totalCount = $db->getItemFromDB($qry);

        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        //    } else {
        //        echo '{"totalCount":0,"data":[]}';
        //    }
    }
}

function listSignupEnquiry()
{
    $nodb = new \cgoDynamiteDB();


    $arrOrder = array();

    $arrOrder['PartitionKey'] = array('col' => 'mobile', 'val' => '', 'oper' => '!=');
    $arrOrder['SortKey'] = array('col' => 'isPartner', 'val' => 1, 'oper' => '=');
    $arrOrder['IndexName'] = 'mobile-isPartner-index';
    $arrOrder['queryAttributes'] = array('uuid', 'tstamp', 'mobile', 'status');
    $arrOrder['Condition'] = array();
    array_push($arrOrder['Condition'], array('col' => 'status', 'val' => 6, 'oper' => '<'));

    $response = array();
    $rsno = $nodb->query('signuplogs', $arrOrder, 'query');
    if (isset($rsno) && count($rsno) > 0) {
        foreach ($rsno as $vehicleapi) {
            $apikey = $vehicleapi['apikey'];
            array_push($response, array('mobile' => $vehicleapi['mobile'], 'tstamp' => $vehicleapi['tstamp'], 'uuid' => $vehicleapi['uuid'], 'status' => $vehicleapi['status']));
        }
    }

    echo '{"totalCount":' . count($response) . ',"data":' . json_encode($response) . '}';
}
