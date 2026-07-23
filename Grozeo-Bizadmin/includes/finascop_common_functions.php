<?php

/*
  Data Entry 'doc_type' numbers and what They Mean
  1 - Cash Receipt
  2 - Cash Payment
  3 - Bank Receipt
  4 - Bank Payment
  5 - Journal Voucher
  6 - Contra Entry
  Purchase 'doc_type' numbers and what They Mean
  7 - Purchase Invoice
  8 - Purchase Returnable
  9 - Purchase Return

 */
/*
  Data Entry 'Type' text and what They Mean
  1 - Receipt
  2 - Payment
  3 - Receipt
  4 - Payment
  5 - Journal Voucher
  6 - Contra Entry
 */

use Dompdf\Dompdf;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

function defineMenuinDeliJobs($statusSum)
{
    $arr = [
        'NoDelivery' => 0,
        'Drive' => 1,
        'Hired' => 2,
        'CustomerPickup' => 4,
        'Courier' => 8,
        'DriverPickup' => 16,
        'ManualDelivery' => 32
    ];
    $statusNames = array();
    foreach ($arr as $status => $value) {
        if (($statusSum & $value) == $value) {
            $statusNames[] = $status;
        }
    }
    //print_r($statusNames);
    $statusNam = implode(',', $statusNames);
    return $statusNam;
}

function loadDeliverJobDetails()
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
    $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
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
                case 'date':
                    //                        $value = str_replace("/", "", $value);
                    //                        $value = substr($value, 4, 4) . substr($value, 0, 2) . substr($value, 2, 2);
                    $value = date('Y-m-d', strtotime($field[data][value]));
                    $search .= " AND  DATE_FORMAT(quor_CreatedOn,'%Y-%m-%d') " . $comparisons[$field['data']['comparison']] . " '{$value}'";

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

    if ($sort == 'booked_at' || $sort == 'orgOrderDate') {
        $sort = 'quor_id';
    }
    $ind = intval($_POST['ind']);
    if ($_SESSION['admin']->br_PyramidLevel == 1) {
        $brQuery = " ";
        /*if ($_SESSION['admin']->IsSuperUser == 'Yes') {
            $brQuery = " ";
        } else {
            $brQuery = " AND quor_Pickupbr_id = {$br_id} ";
        }*/
    } else {
        $brQuery = " AND quor_Pickupbr_id = {$br_id} ";
    }
    if (!empty($br_id)) {
        $qry = "SELECT COUNT(*) FROM " . FINASCOP_DB . "qugeo_order INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status WHERE   quor_TransferOrder_Type = 1  AND quor_slot_id = 0 {$brQuery} {$search} ";

        $query = "SELECT quor_RefNo as booking_no,DATE_FORMAT(quor_CreatedOn,'%d-%m-%Y') as booked_at,quor_PickupPhone,quor_PickupName,quor_DeliveryName,quor_DeliveryPhone,quor_Deliverybr_id,quor_Pickupbr_id,"
            . "quor_PickupLocation as source,quor_TransferOrder_Type,quor_TransferOrder_id,quor_CreatedOn,quor_DeliveryMethodsAllowed,"
            . "quor_DeliveryLocation as destination,quor_AmountCollectible,quor_Paymode,"
            . "if(quor_Status=22,'PICKUP', if(quor_Status=31,'DELIVERY','')) as drivetype,"
            . "quor_id,quor_PickupLat,quor_PickupLng,quor_DeliveryLat,quor_DeliveryLng,"
            . " '" . GMAP_DELIVERY_ICON . "' as deliverymapicon ,'" . GMAP_PIKCUP_ICON . "' as pickupmapicon, "
            . "dls_DelStatus ,quor_Status,"
            . "CASE WHEN quor_Type=1 THEN 'Drive' WHEN quor_Type=2 THEN 'Hired' WHEN quor_Type=3 THEN 'Customer Pickup' WHEN quor_Type=4 THEN 'Courier' WHEN quor_Type=5 THEN 'Driver Pickup' WHEN quor_Type=6 THEN 'Manual Delivery' END AS quor_TypeName,quor_Type,"
            . "DATE_FORMAT(quor_ScheduleOpeningTime,'%d-%m-%Y %H:%i:%s') as quor_ScheduleOpeningTime "
            . " FROM " . FINASCOP_DB . " qugeo_order INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status "
            . "WHERE   quor_TransferOrder_Type = 1  AND quor_slot_id = 0 {$brQuery} {$search} ORDER BY  {$sort} {$dir} LIMIT $recStart,$recLimit";

        $data = $db->getMultipleData($query, true);
        $totalCount = $db->getItemFromDB($qry);
        $resCount = count($data);
        if (!empty($data)) {
            for ($i = 0; $i < $resCount; $i++) {
                $fstr_id = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$data[$i]['quor_TransferOrder_id']}");
                $data[$i]['orderMethod'] = defineMenuinDeliJobs($data[$i]['quor_DeliveryMethodsAllowed']);
                if ($fstr_id > 0) {
                    switch ($data[$i]['quor_TransferOrder_Type']) {
                        case 0:
                            //$data[$i]['orderMethod'] = 0;
                            $data[$i]['orgOrderDate'] = $db->getItemFromDB("SELECT DATE_FORMAT(fstr_createdOn,'%d-%m-%Y %H:%i:%s') as fstr_createdOn  FROM finascop_stock_transfer_request WHERE fstr_id = {$fstr_id}");
                            break;
                        case 1:
                            //$data[$i]['orderMethod'] = $db->getItemFromDB("SELECT order_method FROM retaline_customer_order WHERE order_id = {$fstr_id}");
                            $data[$i]['orgOrderDate'] = $db->getItemFromDB("SELECT DATE_FORMAT(created_at,'%d-%m-%Y %H:%i:%s') as  created_at FROM retaline_customer_order WHERE order_id = {$fstr_id}");
                            $data[$i]['SONumber'] = $db->getItemFromDB("SELECT SONumber FROM B2CSalesOrder WHERE customer_order_id = {$fstr_id}");
                            break;
                        case 2:
                            //$data[$i]['orderMethod'] = 0;
                            $data[$i]['orgOrderDate'] = $db->getItemFromDB("SELECT DATE_FORMAT(bbso_createdon,'%d-%m-%Y %H:%i:%s') as bbso_createdon  FROM retaline_B2B_SalesOrder WHERE bbso_id = {$fstr_id}");
                            break;
                        case 3:
                            //$data[$i]['orderMethod'] = 0;
                            $data[$i]['orgOrderDate'] = $db->getItemFromDB("SELECT DATE_FORMAT(frrp_createdOn,'%d-%m-%Y %H:%i:%s') as frrp_createdOn FROM finascop_stock_return_request_packing WHERE frrp_id = {$fstr_id}");
                            break;
                    }
                }
            }
        }
        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        //    } else {
        //        echo '{"totalCount":0,"data":[]}';
        //    }
    }
}

function loadScheduledJobDetails()
{
    $pending = $_POST['pending'];
    $delivered = $_POST['delivered'];
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
    $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
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
                case 'date':
                    //                        $value = str_replace("/", "", $value);
                    //                        $value = substr($value, 4, 4) . substr($value, 0, 2) . substr($value, 2, 2);
                    $value = date('Y-m-d', strtotime($field[data][value]));
                    $search .= " AND  DATE_FORMAT(quor_CreatedOn,'%Y-%m-%d') " . $comparisons[$field['data']['comparison']] . " '{$value}'";

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

    if ($sort == 'booked_at' || $sort == 'orgOrderDate') {
        $sort = 'quor_id';
    }
    if ($pending == 1) {
        $search .= " AND quor_Status IN (24,25)";
    } else if ($delivered == 1) {
        $search .= " AND quor_Status IN (15,38)";
    } else {
        $search .= " AND quor_Status NOT IN (15,38)";
    }
    $ind = intval($_POST['ind']);
    if ($_SESSION['admin']->br_PyramidLevel == 1) {
        $brQuery = " ";
        /*if ($_SESSION['admin']->IsSuperUser == 'Yes') {
            $brQuery = " ";
        } else {
            $brQuery = " AND quor_Pickupbr_id = {$br_id} ";
        }*/
    } else {
        $brQuery = " AND quor_Pickupbr_id = {$br_id} ";
    }
    if (!empty($br_id)) {
        $qry = "SELECT COUNT(*) FROM " . FINASCOP_DB . "qugeo_order INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status WHERE  quor_slot_id > 0 and quor_Pickupbr_id = {$br_id} {$search} ";

        $query = "SELECT quor_RefNo as booking_no,DATE_FORMAT(quor_CreatedOn,'%d-%m-%Y') as booked_at,quor_PickupPhone,quor_PickupName,quor_DeliveryName,quor_DeliveryPhone,quor_Deliverybr_id,quor_Pickupbr_id,"
            . "quor_PickupLocation as source,quor_TransferOrder_Type,quor_TransferOrder_id,quor_CreatedOn,quor_DeliveryMethodsAllowed,DATE_FORMAT(quor_slot_date,'%d-%m-%Y') as quor_slot_date,quor_slot_id,"
            . "quor_DeliveryLocation as destination,quor_DeliveryAddress,"
            . "if(quor_Status=22,'PICKUP', if(quor_Status=31,'DELIVERY','')) as drivetype,"
            . "quor_id,quor_PickupLat,quor_PickupLng,quor_DeliveryLat,quor_DeliveryLng,"
            . " '" . GMAP_DELIVERY_ICON . "' as deliverymapicon ,'" . GMAP_PIKCUP_ICON . "' as pickupmapicon, "
            . "dls_DelStatus ,quor_Status,"
            . "CASE WHEN quor_Type=1 THEN 'Drive' WHEN quor_Type=2 THEN 'Hired' WHEN quor_Type=3 THEN 'Customer Pickup' WHEN quor_Type=4 THEN 'Courier' WHEN quor_Type=5 THEN 'Driver Pickup' WHEN quor_Type=6 THEN 'Manual Delivery' END AS quor_TypeName,quor_Type,"
            . "DATE_FORMAT(quor_ScheduleOpeningTime,'%d-%m-%Y %H:%i:%s') as quor_ScheduleOpeningTime "
            . " FROM " . FINASCOP_DB . " qugeo_order INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status "
            . "WHERE   quor_slot_id > 0   {$brQuery} {$search} ORDER BY  {$sort} {$dir} "; //LIMIT $recStart,$recLimit

        $data = $db->getMultipleData($query, true);
        $totalCount = $db->getItemFromDB($qry);
        $resCount = count($data);
        if (!empty($data)) {
            for ($i = 0; $i < $resCount; $i++) {
                $fstr_id = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$data[$i]['quor_TransferOrder_id']}");
                $data[$i]['orderMethod'] = defineMenuinDeliJobs($data[$i]['quor_DeliveryMethodsAllowed']);
                if ($fstr_id > 0) {
                    switch ($data[$i]['quor_TransferOrder_Type']) {
                        case 0:
                            //$data[$i]['orderMethod'] = 0;
                            $data[$i]['orgOrderDate'] = $db->getItemFromDB("SELECT DATE_FORMAT(fstr_createdOn,'%d-%m-%Y %H:%i:%s') as fstr_createdOn  FROM finascop_stock_transfer_request WHERE fstr_id = {$fstr_id}");
                            $data[$i]['quor_slot_delivery'] = '';
                            break;
                        case 1:
                            //$data[$i]['orderMethod'] = $db->getItemFromDB("SELECT order_method FROM retaline_customer_order WHERE order_id = {$fstr_id}");
                            $data[$i]['orgOrderDate'] = $db->getItemFromDB("SELECT DATE_FORMAT(created_at,'%d-%m-%Y %H:%i:%s') as  created_at FROM retaline_customer_order WHERE order_id = {$fstr_id}");
                            $data[$i]['deliveryLocation'] = '';
                            $data[$i]['quor_slot_delivery'] = $db->getItemFromDB("SELECT CONCAT(rbds_time_from,'-',rbds_time_to) FROM retaline_branch_delivery_slot where rbds_id = {$data[$i]['quor_slot_id']}");
                            break;
                        case 2:
                            //$data[$i]['orderMethod'] = 0;
                            $data[$i]['orgOrderDate'] = $db->getItemFromDB("SELECT DATE_FORMAT(bbso_createdon,'%d-%m-%Y %H:%i:%s') as bbso_createdon  FROM retaline_B2B_SalesOrder WHERE bbso_id = {$fstr_id}");
                            $data[$i]['quor_slot_delivery'] = '';
                            break;
                        case 3:
                            //$data[$i]['orderMethod'] = 0;
                            $data[$i]['orgOrderDate'] = $db->getItemFromDB("SELECT DATE_FORMAT(frrp_createdOn,'%d-%m-%Y %H:%i:%s') as frrp_createdOn FROM finascop_stock_return_request_packing WHERE frrp_id = {$fstr_id}");
                            $data[$i]['quor_slot_delivery'] = '';
                            break;
                    }
                }
            }
        }
        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        //    } else {
        //        echo '{"totalCount":0,"data":[]}';
        //    }
    }
}

function loadVehicleDetailsDj($action)
{
    global $db;
    $longitude = $_POST['longitude'];
    $latitude = $_POST['latitude'];
    //echo "br id " . $_POST['br_id'] . "\n"; 
    if (!empty($_POST['br_id'])) {

        $nodb = new \cgoDynamiteDB();

        $degMat = new \cgoGeoUtilities();

        $arrDegrees = $degMat->getDegreeMatrix($longitude, $latitude, QC_VEHICLE_NEAR_PICKUP_CIRCLE_DIST);

        $attVehicles = array();
        $attVehicles['PartitionKey'] = array('col' => 'Is_Live', 'val' => 1, 'oper' => '=');

        $attVehicles['SortKey'] = array('col' => 'Latitude', 'val1' => (float) $arrDegrees['lat1'], 'val2' => (float) $arrDegrees['lat2'], 'SortKeyBetween' => true);

        //$attVehicles['SortKey']=array('col'=>'Latitude','type'=>'N','val'=>$arrDegrees['lat1'],'oper'=>'=');				
        $attVehicles['IndexName'] = 'Is_Live-Latitude-index';

        $attVehicles['queryAttributes'] = array('apikey', 'v_id', 'v_no', 'Latitude', 'Longitude', 'LocationUpdateddatetime', 'DriverName', 'v_typename', 'v_capacity', 'CurrentLoadedWeight', 'v_MapIcon', 'mobno');

        $attVehicles['Condition'] = array();

        array_push($attVehicles['Condition'], array('col' => 'Longitude', 'val1' => (float) $arrDegrees['lon1'], 'val2' => (float) $arrDegrees['lon2'], 'ConditionBetween' => true));
        array_push($attVehicles['Condition'], array('col' => 'isallowAutoSchedule', 'val' => (int)1, 'oper' => '='));
        if ($_SESSION['admin']->br_PyramidLevel != 1) {
            array_push($attVehicles['Condition'], array('col' => 'DriverBranchId', 'val' => (int)$_POST['br_id'], 'oper' => '='));
        }
        //echo json_encode($attVehicles)."\n";
        $rsno = $nodb->query('QugeoLiveVehicles', $attVehicles, 'query');
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

function loadVehicleDetailsSchJob($action)
{
    global $db;
    $longitude = $_POST['longitude'];
    $latitude = $_POST['latitude'];
    //echo "br id " . $_POST['br_id'] . "\n"; 
    if (!empty($_POST['br_id'])) {

        $nodb = new \cgoDynamiteDB();

        $degMat = new \cgoGeoUtilities();

        $arrDegrees = $degMat->getDegreeMatrix($longitude, $latitude, QC_VEHICLE_NEAR_PICKUP_CIRCLE_DIST);

        $attVehicles = array();
        $attVehicles['PartitionKey'] = array('col' => 'Is_Live', 'val' => 1, 'oper' => '=');

        $attVehicles['SortKey'] = array('col' => 'Latitude', 'val1' => (float) $arrDegrees['lat1'], 'val2' => (float) $arrDegrees['lat2'], 'SortKeyBetween' => true);

        //$attVehicles['SortKey']=array('col'=>'Latitude','type'=>'N','val'=>$arrDegrees['lat1'],'oper'=>'=');				
        $attVehicles['IndexName'] = 'Is_Live-Latitude-index';

        $attVehicles['queryAttributes'] = array('apikey', 'v_id', 'v_no', 'Latitude', 'Longitude', 'LocationUpdateddatetime', 'DriverName', 'v_typename', 'v_capacity', 'CurrentLoadedWeight', 'v_MapIcon');

        $attVehicles['Condition'] = array();

        array_push($attVehicles['Condition'], array('col' => 'Longitude', 'val1' => (float) $arrDegrees['lon1'], 'val2' => (float) $arrDegrees['lon2'], 'ConditionBetween' => true));
        array_push($attVehicles['Condition'], array('col' => 'isallowManualSchedule', 'val' => (int)1, 'oper' => '='));
        array_push($attVehicles['Condition'], array('col' => 'DriverBranchId', 'val' => (int)$_POST['br_id'], 'oper' => '='));
        //echo json_encode($attVehicles)."\n";
        $rsno = $nodb->query('QugeoLiveVehicles', $attVehicles, 'query');
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

function loadDistributedJobDetails()
{
    $pending = $_POST['pending'];
    $delivered = $_POST['delivered'];
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
    $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
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
                                $search .= " and (quor_RefNo LIKE '{$field['data']['value']}%') ";
                            } else {
                                $search .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                            }
                        }
                    }
                    break;
                case 'date':
                    //                        $value = str_replace("/", "", $value);
                    //                        $value = substr($value, 4, 4) . substr($value, 0, 2) . substr($value, 2, 2);
                    $value = date('Y-m-d', strtotime($field[data][value]));
                    $search .= " AND  DATE_FORMAT(quor_CreatedOn,'%Y-%m-%d') " . $comparisons[$field['data']['comparison']] . " '{$value}'";

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

    if ($sort == 'booked_at' || $sort == 'orgOrderDate') {
        $sort = 'quor_id';
    }
    if ($pending == 1) {
        $search .= " AND quor_Status IN (24,25)";
    } else if ($delivered == 1) {
        $search .= " AND quor_Status IN (15,38)";
    } else {
        $search .= " AND quor_Status NOT IN (15,38)";
    }
    $ind = intval($_POST['ind']);
    if ($_SESSION['admin']->br_PyramidLevel == 1) {
        $brQuery = " ";
        /*if ($_SESSION['admin']->IsSuperUser == 'Yes') {
            $brQuery = " ";
        } else {
            $brQuery = " AND quor_Pickupbr_id = {$br_id} ";
        }*/
    } else {
        $brQuery = " AND quor_Pickupbr_id = {$br_id} ";
    }
    if (!empty($br_id)) {
        $qry = "SELECT COUNT(*) FROM " . FINASCOP_DB . "qugeo_order INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status WHERE  quor_Deliverybr_id = {$br_id} ";

        $query = "SELECT quor_RefNo as booking_no,DATE_FORMAT(quor_CreatedOn,'%d-%m-%Y') as booked_at,quor_PickupPhone,quor_PickupName,quor_DeliveryName,quor_DeliveryPhone,quor_Deliverybr_id,quor_Pickupbr_id,"
            . "quor_PickupLocation as source,quor_TransferOrder_Type,quor_TransferOrder_id,quor_CreatedOn,quor_DeliveryMethodsAllowed,"
            . "quor_DeliveryLocation as destination,quor_PacketCount,"
            . "if(quor_Status=22,'PICKUP', if(quor_Status=31,'DELIVERY','')) as drivetype,"
            . "quor_id,quor_PickupLat,quor_PickupLng,quor_DeliveryLat,quor_DeliveryLng,"
            . " '" . GMAP_DELIVERY_ICON . "' as deliverymapicon ,'" . GMAP_PIKCUP_ICON . "' as pickupmapicon, "
            . "dls_DelStatus ,quor_Status,"
            . "CASE WHEN quor_Type=1 THEN 'Drive' WHEN quor_Type=2 THEN 'Hired' WHEN quor_Type=3 THEN 'Customer Pickup' WHEN quor_Type=4 THEN 'Courier' WHEN quor_Type=5 THEN 'Driver Pickup' WHEN quor_Type=6 THEN 'Manual Delivery' END AS quor_TypeName,quor_Type,"
            . "DATE_FORMAT(quor_ScheduleOpeningTime,'%d-%m-%Y %H:%i:%s') as quor_ScheduleOpeningTime "
            . " FROM " . FINASCOP_DB . " qugeo_order INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status "
            . "WHERE   quor_TransferOrder_Type IN (0,2,3,4) AND quor_slot_id = 0  {$brQuery} {$search} ORDER BY  {$sort} {$dir} LIMIT $recStart,$recLimit";

        $data = $db->getMultipleData($query, true);
        $totalCount = $db->getItemFromDB($qry);
        $resCount = count($data);
        if (!empty($data)) {
            for ($i = 0; $i < $resCount; $i++) {
                $fstr_id = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$data[$i]['quor_TransferOrder_id']}");
                $fsto_isInvoiced = $db->getItemFromDB("SELECT fsto_isInvoiced FROM finascop_stock_transfer_order WHERE fsto_id = {$data[$i]['quor_TransferOrder_id']}");
                $data[$i]['orderMethod'] = defineMenuinDeliJobs($data[$i]['quor_DeliveryMethodsAllowed']);

                if ($fstr_id > 0) {
                    switch ($data[$i]['quor_TransferOrder_Type']) {
                        case 0:
                            //$data[$i]['orderMethod'] = 0;
                            $data[$i]['orgOrderDate'] = $db->getItemFromDB("SELECT DATE_FORMAT(fstr_createdOn,'%d-%m-%Y %H:%i:%s') as fstr_createdOn  FROM finascop_stock_transfer_request WHERE fstr_id = {$fstr_id}");
                            $data[$i]['fsto_isInvoiced'] = $fsto_isInvoiced;
                            $fsr_id = $db->getItemFromDB("SELECT fsr_id FROM finascop_stock_request WHERE fstr_id = {$fstr_id}");
                            if ($fsr_id > 0) {
                                $data[$i]['printInvoice'] = 0;
                            } else {
                                $data[$i]['printInvoice'] = 1;
                            }

                            break;
                        case 1:
                            //$data[$i]['orderMethod'] = $db->getItemFromDB("SELECT order_method FROM retaline_customer_order WHERE order_id = {$fstr_id}");
                            $data[$i]['orgOrderDate'] = $db->getItemFromDB("SELECT DATE_FORMAT(created_at,'%d-%m-%Y %H:%i:%s') as  created_at FROM retaline_customer_order WHERE order_id = {$fstr_id}");
                            $data[$i]['fsto_isInvoiced'] = 1;
                            $data[$i]['printInvoice'] = 0;
                            break;
                        case 2:
                            //$data[$i]['orderMethod'] = 0;
                            $data[$i]['orgOrderDate'] = $db->getItemFromDB("SELECT DATE_FORMAT(bbso_createdon,'%d-%m-%Y %H:%i:%s') as bbso_createdon  FROM retaline_B2B_SalesOrder WHERE bbso_id = {$fstr_id}");
                            $data[$i]['fsto_isInvoiced'] = 1;
                            $data[$i]['printInvoice'] = 0;
                            break;
                        case 3:
                            //$data[$i]['orderMethod'] = 0;
                            $data[$i]['orgOrderDate'] = $db->getItemFromDB("SELECT DATE_FORMAT(frrp_createdOn,'%d-%m-%Y %H:%i:%s') as frrp_createdOn FROM finascop_stock_return_request_packing WHERE frrp_id = {$fstr_id}");
                            $data[$i]['fsto_isInvoiced'] = 1;
                            $data[$i]['printInvoice'] = 0;
                            break;
                        case 4:
                            //$data[$i]['orderMethod'] = 0;
                            $data[$i]['orgOrderDate'] = $db->getItemFromDB("SELECT DATE_FORMAT(rdc_createdOn,'%d-%m-%Y %H:%i:%s') as rdc_createdOn FROM retaline_distribution_chart WHERE rdc_id = {$fstr_id}");
                            $data[$i]['fsto_isInvoiced'] = $fsto_isInvoiced;
                            $data[$i]['printInvoice'] = 1;
                            break;
                    }
                }
            }
        }
        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        //    } else {
        //        echo '{"totalCount":0,"data":[]}';
        //    }
    }
}

function loadVehicleDetailsDistributedJob($action)
{
    global $db;
    $longitude = $_POST['longitude'];
    $latitude = $_POST['latitude'];
    //echo "br id " . $_POST['br_id'] . "\n"; 
    if (!empty($_POST['br_id'])) {

        $nodb = new \cgoDynamiteDB();

        $degMat = new \cgoGeoUtilities();

        $arrDegrees = $degMat->getDegreeMatrix($longitude, $latitude, QC_VEHICLE_NEAR_PICKUP_CIRCLE_DIST);

        $attVehicles = array();
        $attVehicles['PartitionKey'] = array('col' => 'Is_Live', 'val' => 1, 'oper' => '=');

        $attVehicles['SortKey'] = array('col' => 'Latitude', 'val1' => (float) $arrDegrees['lat1'], 'val2' => (float) $arrDegrees['lat2'], 'SortKeyBetween' => true);

        //$attVehicles['SortKey']=array('col'=>'Latitude','type'=>'N','val'=>$arrDegrees['lat1'],'oper'=>'=');				
        $attVehicles['IndexName'] = 'Is_Live-Latitude-index';

        $attVehicles['queryAttributes'] = array('apikey', 'v_id', 'v_no', 'Latitude', 'Longitude', 'LocationUpdateddatetime', 'DriverName', 'v_typename', 'v_capacity', 'CurrentLoadedWeight', 'v_MapIcon');

        $attVehicles['Condition'] = array();

        array_push($attVehicles['Condition'], array('col' => 'Longitude', 'val1' => (float) $arrDegrees['lon1'], 'val2' => (float) $arrDegrees['lon2'], 'ConditionBetween' => true));
        array_push($attVehicles['Condition'], array('col' => 'isallowManualSchedule', 'val' => '1', 'oper' => '='));
        //echo json_encode($attVehicles)."\n";
        $rsno = $nodb->query('QugeoLiveVehicles', $attVehicles, 'query');
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

function generateNextDocNo($doc_type, $EnteredByBranchID)
{
    global $db;
    $success = $db->query("INSERT INTO " . FINASCOP_DB . "finascop_doc_number_ref"
        . "(dore_DocType,br_id) "
        . "VALUES ({$doc_type},{$EnteredByBranchID}) ON DUPLICATE KEY UPDATE dore_DocType = {$doc_type}, br_id = {$EnteredByBranchID}");

    $con = "dore_DocType = {$doc_type}  AND br_id = {$EnteredByBranchID}";
    $DocRec = $db->getFromDB("SELECT COALESCE(dore_lastDocNo,0) as dore_lastDocNo,COALESCE(dore_DocCode,dore_DocType) as dore_DocCode "
        . "FROM " . FINASCOP_DB . "finascop_doc_number_ref WHERE {$con}", true);
    $prefix = '';
    $dore_DocCodeLen = strlen($DocRec['dore_DocCode']);
    $prefix = $dore_DocCodeLen > 2 ? substr($DocRec['dore_DocCode'], -2) : $DocRec['dore_DocCode'];

    $lastDocNo = $DocRec['dore_lastDocNo'];

    $docNumber = intval($lastDocNo) + 1;

    $newDocNo = $prefix . $docNumber;

    $success = $db->query("REPLACE INTO " . FINASCOP_DB . "finascop_doc_number_ref"
        . "(dore_DocType,dore_DocCode,br_id,dore_lastDocNo) "
        . "VALUES ({$doc_type},'{$prefix}',{$EnteredByBranchID},'{$docNumber}')");

    if (!$success) {
        return '{"success" : false, "msg":"FINASCOP: Cannot update last Document Number in database."}';
        exit;
    }

    return $newDocNo;
}

function finascop_numberToWords($numberstring, $basic_unit = '', $fractional_unit = '')
{
    $result = '';
    $str_arr = explode('.', $numberstring);
    $number = $b = str_replace(',', '', $str_arr[0]);
    $arraynum = array_map('intval', str_split($str_arr[1]));

    $no = $number;
    $point = $str_arr[1];
    $hundred = null;
    $digits_1 = strlen($no);
    $point_1 = strlen($point);
    $i = 0;
    $str = array();
    $words = array(
        '0' => '',
        '1' => 'One',
        '2' => 'Two',
        '3' => 'Three',
        '4' => 'Four',
        '5' => 'Five',
        '6' => 'Six',
        '7' => 'Seven',
        '8' => 'Eight',
        '9' => 'Nine',
        '10' => 'Ten',
        '11' => 'Eleven',
        '12' => 'Twelve',
        '13' => 'Thirteen',
        '14' => 'Fourteen',
        '15' => 'Fifteen',
        '16' => 'Sixteen',
        '17' => 'Seventeen',
        '18' => 'Eighteen',
        '19' => 'Nineteen',
        '20' => 'Twenty',
        '30' => 'Thirty',
        '40' => 'Forty',
        '50' => 'Fifty',
        '60' => 'Sixty',
        '70' => 'Seventy',
        '80' => 'Eighty',
        '90' => 'Ninety'
    );
    $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
    while ($i < $digits_1) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += ($divider == 10) ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str[] = ($number < 21) ? $words[$number] .
                " " . $digits[$counter] . $plural . " " . $hundred :
                $words[floor($number / 10) * 10]
                . " " . $words[$number % 10] . " "
                . $digits[$counter] . $plural . " " . $hundred;
        } else
            $str[] = null;
    }
    $str = array_reverse($str);

    $result .= implode('', $str);
    $result .= $basic_unit . ' ';
    $i = 0;
    while ($i < $point_1) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($point % $divider);
        $point = floor($point / $divider);
        $i += ($divider == 10) ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str1)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str1[0]) ? ' and ' : null;
            $str1[] = ($number < 21) ? $words[$number] .
                " " . $digits[$counter] . $plural . " " . $hundred :
                $words[floor($number / 10) * 10]
                . " " . $words[$number % 10] . " "
                . $digits[$counter] . $plural . " " . $hundred;
        } else
            $str1[] = null;
    }

    $str1 = array_reverse($str1);

    if (count($str1) > 0 && $str1[0] != '') {

        $result .= implode('', $str1);
        $result .= $fractional_unit . ' ';
    }
    return $result /* . 'Only' */;
}

function getRandomRef()
{
    if (preg_match('/sil.lab/i', $_SERVER['HTTP_HOST'])) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $string = '';
        $max = strlen($characters) - 1;
        for ($i = 0; $i < 7; $i++) {
            $string .= $characters[mt_rand(0, $max)];
        }
        return $string;
    }
    if (REFNUMBER_FROM_DDB) {
        $nodb = new \cgoDynamiteDB();
        $nodb->AddTablePrefix(false);
        $arrSession['Data'] = array();
        array_push($arrSession['Data'], array('col' => 'refno'));
        $arrSession['ExclusiveStartKey'] = array('col' => 'refno', 'val' => (string) uniqid());
        $arrSession['Limit'] = 1;
        $rsno = $nodb->query(REF_NO_VAULT_DDB_TABLE, $arrSession, 'scan');
        if (count($rsno) > 0) {
            deleteRefNoVault($rsno[0]['refno']);
            return $rsno[0]['refno'];
        } else {
            return null;
        }
    } else {
        global $db;
        $refnos = $db->getFromDB('SELECT pnva_id,pnva_refno from ' . FINASCOP_DB . 'finascop_pnrno_vault  limit 1  FOR UPDATE;', true);
        $db->query('delete from ' . FINASCOP_DB . 'finascop_pnrno_vault where pnva_id = ' . $refnos['pnva_id']);
        return $refnos['pnva_refno'];
    }
}

function getNewFinascopApiKey()
{
    return sha1(microtime(true) . mt_rand(10000, 90000));
}

function deleteRefNoVault($refno)
{
    if (preg_match('/sil.lab/i', $_SERVER['HTTP_HOST'])) {
        return true;
    }
    try {
        $nodb = new \cgoDynamiteDB();
        $nodb->AddTablePrefix(false);
        $arrSession = array();
        $arrSession['PartitionKey'] = array('col' => 'refno', 'val' => (string) $refno);
        $nosession = $nodb->perform(REF_NO_VAULT_DDB_TABLE, 'delete', $arrSession, $response);
    } catch (Exception $e) {
        print_r($e->getMessage());
    }
}

function IsValidapikeyLocal($apikey)
{
    global $db;
    $api_key = $db->getItemFromDB('SELECT apikey from ' . FINASCOP_DB . 'finascop_user_details where UserId = ' . $_SESSION['admin']->Finascop_UserId . ';');
    if ($api_key == $apikey) {
        return true;
    }
    return false;
}

function IsValidapikey($apikey)
{
    $nodb = new \cgoDynamiteDB();
    $arrAPI = array();
    $arrAPI['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
    $arrAPI['IndexName'] = 'apikey-index';
    $arrAPI['queryAttributes'] = array('validtill', 'id', 'usertype', 'extrainfo');
    //$arrAPI['Condition']=array();
    //array_push($arrAPI['Condition'],array('col'=>'validtill','val'=>time(),'oper'=>'>' ));	
    //array_push($arrAPI['Condition'],array('col'=>'validtill','val'=>0,'oper'=>'>' ));	

    $rsno = $nodb->query('APISession', $arrAPI, 'query');


    //Extend the Session
    if (isset($rsno) && count($rsno) > 0) {
        return true;
        /*
          $usertype = $rsno[0]['usertype'];
          $id = $rsno[0]['id'];
          $extrainfo = $rsno[0]['extrainfo'];
          $arrUpdate=array();
          $arrUpdate['PartitionKey']=array('col'=>'usertype','val'=>$usertype);
          $arrUpdate['SortKey']=array('col'=>'id','val'=>$id);
          $arrUpdate['Data']=array();
          $validityseconds = LOGIN_KEEPALIVE_TIMEOUT;

          array_push($arrUpdate['Data'],array('col'=>'validtill','val'=>(int)(time() + $validityseconds)));
          array_push($arrUpdate['Data'],array('col'=>'lastvalidation','val'=>(string)date("YmdHis") ));
          $nors = $nodb->perform('APISession','update',$arrUpdate,$response);
          if ($nors){
          $_SESSION["loginid"]  = $id;
          $_SESSION["usertype"]  = $usertype;
          if($usertype==1 || $usertype==3){
          $_SESSION["c_id"] = $extrainfo['c_id'];
          }elseif($usertype==5){
          $_SESSION["drvname"] = $extrainfo['db_Name'];
          }
          return true;
          }
          else
          return false;
         */
    } else {
        return false;
    }
}

function finascop_getjsonkeyarray($sql)
{
    global $db;
    $items = $db->getMultipleData($sql);
    if ($items) {
        echo json_encode($items);
    } else {
        echo '{"success": true,"data":[]}';
    }
}

function CreatePDF($company_name, $branch_name, $branch_address, $receipt_no, $receipt_date, $acet_type, $acet_Amount, $acet_InWords, $acet_Narration, $acet_ledg_Id, $particular)
{
    /* instantiate and use the dompdf class */
    $receipt_date = date("F jS, Y", strtotime($receipt_date));
    $company_name = strtoupper($company_name);
    $branch_name = strtoupper($branch_name);
    $branch_address = strtoupper($branch_address);

    $dompdf = new Dompdf();
    $htmla = '<html><head><style>table, th, td {
  
    border-collapse: collapse;
}</style></head><body> <table cellpadding="0" cellspacing="0"><tr><td colspan="3"><table><tr><td style = "padding-right:250px;"></td><td align="center"> <br><b>
							     ' . $company_name . ' PVT LTD </b><br>
						   ' . $branch_address . ' <br>
						   ' . $branch_name . ' <br><br>
                                                   <b>' . $acet_type . ' Voucher</b>
                            </td></tr></table></td></tr></table>
		<br><br><table width = "600"><tr> <td style = "padding-right:8px;"></td><td style = "padding-right:120px;">No : <b> ' . $receipt_no . ' </b></td><td>
                   
                                Dated : <b>' . $receipt_date . '</b><br>
                                
                            </td></tr></table><br>

 
<table border="1" cellpadding="0" cellspacing="0" style="border-spacing: 0;width:100%;">

  <tr>
    <th align="center">Particulars</th>
    <th align="right">Amount</th>
  </tr>
  <tr>
    <td align="left" style="padding:0px;margin:0px;">
   <br><b>Account : </b><br>';
    foreach ($particular as $key => $value) {
        ($value[ledg_Id] == $acet_ledg_Id) ? $through = ' <align="center"><br><br><b>Through:</b><br><div style="padding-left:30px;"> ' . $value[NAME] . '</div><br><br>' :
            $particular_name .= "<table style='width:100%;border-spacing: 0;'><tr><td style='padding-left:30px;margin:0px;height:1px;border:none;'>" . $value[NAME] . '</td></tr></table>';
    }
    $htmlb = '<b>Narration: <br> <div style="padding-left:30px;"> ' . $acet_Narration . '</div></b><br><b>Amount (in Words) :<br><div style="padding-left:30px;">INR ' . $acet_InWords . 'Only</div></b></td>
        
    <td align="right">';
    foreach ($particular as $key => $value) {
        ($value[ledg_Id] == $acet_ledg_Id) ? $through_amount = ' <br><br><br><br><br><br><br><br>' :
            $particular_amount .= ($value[actr_IsNegative] == 1) ? '<table style="width:100%"><tr><td align="right" cellpadding=0 cellspacing=0>-' . $value[amount] . "</td></tr></table>" : '<table style="width:100%;border-spacing: 0;"><tr><td align="right" style="padding:0px;margin:0px;height:1px;border:none;">' . $value[amount] . "</td></tr></table>";
    }
    $htmlc = '</td>
  </tr>
   <tr>
    <td align="center"></td>
    <td align="right"><br><b>' . $acet_Amount . '</b> </td>

  </tr>
  
</table>
<br>
<table>
<tr>
<td style = "padding-right:530px;"> </td>
<td> <b><br>Authorised Signatory</b> </td>
</tr>
</table>
</body>
</html>';
    $html = $htmla . $particular_name . $through . $htmlb . $particular_amount . $through_amount . $htmlc;
    $dompdf->loadHtml($html);
    //$dompdf->loadHtml("<h1>test pdf generation</h1>");

    $dompdf->setPaper('A4', 'portrait');

    /* Render the HTML as PDF */
    $dompdf->render();


    $file_to_save = '/home/web/jatayu-booking/html/uploads/file.pdf';
    ob_clean();
    header("Content-type: application/octet-stream");
    header("Content-disposition: attachment;filename= $acet_type.pdf");

    //readfile($file_to_save);
    echo $dompdf->output();

    // echo $data;
}

function CreateJournalVoucherPDF($company_name, $branch_name, $branch_address, $receipt_no, $receipt_date, $acet_type, $acet_Amount, $acet_InWords, $acet_Narration, $acet_ledg_Id, $particular)
{
    /* instantiate and use the dompdf class */
    $receipt_date = date("F jS, Y", strtotime($receipt_date));
    $company_name = strtoupper($company_name);
    $branch_name = strtoupper($branch_name);
    $branch_address = strtoupper($branch_address);

    $dompdf = new Dompdf();
    $htmla = '<html><head><style>table, th, td {
  
    border-collapse: collapse;
}</style></head><body> <table cellpadding="0" cellspacing="0"><tr><td colspan="3"><table><tr><td style = "padding-right:250px;"></td><td align="center"> <br><b>
							     ' . $company_name . ' PVT LTD </b><br>
						   ' . $branch_address . ' <br>
						   ' . $branch_name . ' <br><br>
                                                   <b>' . $acet_type . '</b>
                            </td></tr></table></td></tr></table>
		<br><br><table width = "600"><tr> <td style = "padding-right:8px;"></td><td style = "padding-right:120px;">No : <b> ' . $receipt_no . ' </b></td><td>
                   
                                Dated : <b>' . $receipt_date . '</b><br>
                                
                            </td></tr></table><br>

 
<table border="1" cellpadding="0" cellspacing="0" style="border-spacing: 0;width:100%;">

  <tr>
    <th align="center">Particulars</th>
    <th align="right">Debit</th>
    <th align="right">Credit</th>
  </tr>
  <tr>
    <td align="left" style="padding:0px;margin:0px;">
   ';

    foreach ($particular as $key => $value) {

        $particular_name .= "<table style='width:100%;border-spacing: 0;'><tr><td style='padding-left:30px;margin:0px;height:1px;border:none;'>" . $value[NAME] . '</td></tr></table>';
    }

    $htmlb = '</td>
        
    <td align="right" cellpadding=0 cellspacing=0 style="padding-left:30px;margin:0px;height:0px;border:none;">';

    foreach ($particular as $key => $value) {
        if ((($value[actr_IsNegative] == 1) && ($value[actr_IsDebtor] == 0)) || (($value[actr_IsNegative] == 0) && ($value[actr_IsDebtor] == 1))) {
            $debit_amount .= "<table style='width:100%;border-spacing: 0;'><tr><td align='right' cellpadding=0 cellspacing=0 style='padding-left:30px;margin:0px;height:0px;border:none;'>$value[amount] </td></tr></table>";
            $credit_amount .= "<table style='width:100%;border-spacing: 0;'><tr><td align='right' cellpadding=0 cellspacing=0 style='padding-left:30px;margin:0px;height:0px;border:none;'>&nbsp;</td></tr></table>";
        } elseif ((($value[actr_IsNegative] == 1) && ($value[actr_IsDebtor] == 1)) || (($value[actr_IsNegative] == 0) && ($value[actr_IsDebtor] == 0))) {
            $credit_amount .= "<table style='width:100%;border-spacing: 0;'><tr><td align='right' cellpadding=0 cellspacing=0 style='padding-left:30px;margin:0px;height:0px;border:none;'>$value[amount] </td></tr></table>";
            $debit_amount .= "<table style='width:100%;border-spacing: 0;'><tr><td align='right' cellpadding=0 cellspacing=0 style='padding-left:30px;margin:0px;height:0px;border:none;'>&nbsp;</td></tr></table>";
        }
    }
    $htmlc = '</td> <td align="right" style="padding:0px;margin:0px;">';

    $htmld = '</td>
  </tr>
    <tr>
    <td style="padding-left:28px; border-top: hidden !important;"><b><br>Narration : </b><br> <div style="padding-left:30px;"> ' . $acet_Narration . '</div></td>
    <td style="border-top: hidden !important;"> </td>
    <td style="border-top: hidden !important;"> </td>
  </tr>
   <tr>
    <td align="center"></td>
    <td align="right"><br><b>' . $acet_Amount . '</b> </td>
    <td align="right"><br><b>' . $acet_Amount . '</b> </td>
  </tr>
  
</table> 
<br>
<table>
<tr>
<td style = "padding-right:530px;"> </td>
<td> <b><br>Authorised Signatory</b> </td>
</tr>
</table>
</body>
</html>';
    $html = $htmla . $particular_name . $htmlb . $debit_amount . $htmlc . $credit_amount . $htmld;
    $dompdf->loadHtml($html);
    //$dompdf->loadHtml("<h1>test pdf generation</h1>");

    $dompdf->setPaper('A4', 'portrait');

    /* Render the HTML as PDF */
    $dompdf->render();


    $file_to_save = '/home/web/jatayu-booking/html/uploads/file.pdf';
    ob_clean();
    header("Content-type: application/octet-stream");
    header("Content-disposition: attachment;filename= $acet_type.pdf");

    //readfile($file_to_save);
    echo $dompdf->output();

    // echo $data;
}

function SavePDFtoS3($company_name, $branch_name, $branch_address, $receipt_no, $receipt_date, $acet_type, $acet_Amount, $acet_InWords, $acet_Narration, $acet_ledg_Id, $particular)
{
    /* instantiate and use the dompdf class */
    $receipt_date = date("F jS, Y", strtotime($receipt_date));
    $company_name = strtoupper($company_name);
    $branch_name = strtoupper($branch_name);
    $branch_address = strtoupper($branch_address);

    $s3 = new S3Client([
        'version' => '2006-03-01',
        'region' => AWS_FINASCOP_ASSET_REGION,
        'credentials' => array(
            'key' => AWS_FINASCOP_ASSET_ACCESS_KEY,
            'secret' => AWS_FINASCOP_ASSET_PASSWORD_KEY
        )
    ]);


    $dompdf = new Dompdf();
    $htmla = '<html><head><style>table, th, td {
  
    border-collapse: collapse;
}</style></head><body> <table cellpadding="0" cellspacing="0"><tr><td colspan="0"><table><tr><td style = "padding-right:200px; padding-left:0px;"></td><td align="center"> <br><b>
							     ' . $company_name . ' PVT LTD </b><br>
						   ' . $branch_address . ' <br>
						   ' . $branch_name . ' <br><br>
                                                   <b>' . $acet_type . ' Voucher</b>
                            </td></tr></table></td></tr></table>
		<br><br><table width = "600"><tr> <td style = "padding-right:10px;"></td><td style = "padding-right:140px;">No : <b> ' . $receipt_no . ' </b></td><td>
                   
                                Dated : <b>' . $receipt_date . '</b><br>
                                
                            </td></tr></table><br>

 
<table border="1" cellpadding="0" cellspacing="0" style="border-spacing: 0;width:100%;">

  <tr>
    <th align="center">Particulars</th>
    <th align="right">Amount</th>
  </tr>
  <tr>
    <td align="left" style="padding:0px;margin:0px;">
   <br><b>Account : </b><br>';
    foreach ($particular as $key => $value) {
        ($value[ledg_Id] == $acet_ledg_Id) ? $through = ' <align="center"><br><br><b>Through:</b><br><div style="padding-left:30px;"> ' . $value[NAME] . '</div><br><br>' :
            $particular_name .= "<table style='width:100%;border-spacing: 0;'><tr><td style='padding-left:30px;margin:0px;height:1px;border:none;'>" . $value[NAME] . '</td></tr></table>';
    }
    $htmlb = '<b>Narration: <br> <div style="padding-left:30px;"> ' . $acet_Narration . '</div></b><br><b>Amount (in Words) :<br><div style="padding-left:30px;">INR ' . $acet_InWords . 'Only</div></b></td>
        
    <td align="right">';
    foreach ($particular as $key => $value) {
        ($value[ledg_Id] == $acet_ledg_Id) ? $through_amount = ' <br><br><br><br><br><br><br><br>' :
            $particular_amount .= ($value[actr_IsNegative] == 1) ? '<table style="width:100%"><tr><td align="right" cellpadding=0 cellspacing=0>-' . $value[amount] . "</td></tr></table>" : '<table style="width:100%;border-spacing: 0;"><tr><td align="right" style="padding:0px;margin:0px;height:1px;border:none;">' . $value[amount] . "</td></tr></table>";
    }
    $htmlc = '</td>
  </tr>
   <tr>
    <td align="center"></td>
    <td align="right"><br><b>' . $acet_Amount . '</b> </td>

  </tr>
  
</table>
<br>
<table>
<tr>
<td style = "padding-right:530px;"> </td>
<td> <b><br>Authorised Signatory</b> </td>
</tr>
</table>
</body>
</html>';
    $html = $htmla . $particular_name . $through . $htmlb . $particular_amount . $through_amount . $htmlc;
    $dompdf->loadHtml($html);
    //$dompdf->loadHtml("<h1>test pdf generation</h1>");

    $dompdf->setPaper('A4', 'portrait');

    /* Render the HTML as PDF */
    $dompdf->render();


    ob_clean();

    $file_name = sha1(microtime(true) . mt_rand(10000, 90000));
    try {
        // Upload data.
        $result = $s3->putObject([
            'Bucket' => AWS_FINASCOP_ASSET_BUCKET,
            'Key' => $file_name . '.pdf',
            'Body' => $dompdf->output(),
            'ACL' => 'public-read'
        ]);
        return $result['ObjectURL'];
        // return '{"success" : true,"msg":"' . $msg . '", "aws_s3_object_url" : "'.$result['ObjectURL'] . ' ","Version" : "'.$result['VersionId'].'", "RefNo" : "' . $receipt_no . '","acet_Type_Id" : "'.$data['ledger_type'].'","UpdateOn" : "' . $updateon . '"}';
    } catch (S3Exception $e) {
        echo $e->getMessage() . PHP_EOL;
    }
}

function updateUniqueItemTable($uid, $uniData)
{
    global $db;
    //    print_r($uniData);
    //    print_r($uid);
    //           exit;
    $uniData['fsi_category_id'] = addslashes($uniData['fsi_category_id']);
    $chkUnqExiste = $db->getFromDB("SELECT fsi_uid,fsi_count FROM " . FINASCOP_DB . "finascop_stock_uniqueitem WHERE fsi_item_id = {$uniData['fsi_item_id']} "
        . "  AND fsi_brand_id = {$uniData['fsi_brand_id']} AND fsi_category_id = {$uniData['fsi_category_id']} AND fsi_variant = '{$uniData['fsi_variant']}' AND isMedicine = {$uniData['isMedicine']}", true);
    $uidCount = $db->getItemFromDB("SELECT fsi_count FROM " . FINASCOP_DB . "finascop_stock_uniqueitem WHERE fsi_uid = {$uid}");

    if ($uid == 0) {

        if (intval($chkUnqExiste['fsi_uid']) > 0) {

            $fsuidata['fsi_count'] = intval($chkUnqExiste['fsi_count']) + 1;
            $status = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $fsuidata, 'update', " fsi_uid = {$chkUnqExiste['fsi_uid']}");
            $stit_fsiuid['fsi_uid'] = $chkUnqExiste['fsi_uid'];
        } else {

            $fsuidata = $uniData;
            $fsuidata['fsi_count'] = 1;
            $ustatus = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $fsuidata);
            $stit_fsiuid['fsi_uid'] = $db->insert_id();
            $stit_fsiuid['status'] = 'NEW';
        }
    } else {

        if (intval($chkUnqExiste['fsi_uid']) > 0) {
            if (intval($chkUnqExiste['fsi_uid']) != $uid) {

                $fsuidata['fsi_displaylabel'] = $uniData['fsi_displaylabel'];
                if (intval($uidCount) > 0) {
                    $fsuidata['fsi_count'] = intval($uidCount) - 1;
                } else {
                    $fsuidata['fsi_count'] = 0;
                }

                $status = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $fsuidata, 'update', " fsi_uid = {$uid}");
                $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM finascop_stock_itemmaster WHERE stit_fsiuid = {$uid}");
                $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $uuit, 'update', " fsi_uid = {$uid}");


                $updatCou['fsi_count'] = intval($chkUnqExiste['fsi_count']) + 1;
                $status = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $updatCou, 'update', " fsi_uid = {$chkUnqExiste['fsi_uid']}");
                $stit_fsiuid['fsi_uid'] = $chkUnqExiste['fsi_uid'];
            } else {
                $fsuidata['fsi_displaylabel'] = $uniData['fsi_displaylabel'];
                // $fsuidata['fsi_count'] = intval($uidCount) - 1;

                $status = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $fsuidata, 'update', " fsi_uid = {$uid}");
                $stit_fsiuid['fsi_uid'] = $uid;
            }
        } else {

            if (intval($uidCount) > 0) {
                $fsdata['fsi_count'] = intval($uidCount) - 1;
            } else {
                $fsdata['fsi_count'] = 0;
            }

            $status = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $fsdata, 'update', " fsi_uid = {$uid}");
            $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM finascop_stock_itemmaster WHERE stit_fsiuid = {$uid}");
            $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $uuit, 'update', " fsi_uid = {$uid}");

            $fsuidata = $uniData;
            $fsuidata['fsi_count'] = 1;
            $ustatus = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $fsuidata);
            $stit_fsiuid['fsi_uid'] = $db->insert_id();
            $stit_fsiuid['status'] = 'NEW';
        }
    }
    return $stit_fsiuid;
}

function getName($n)
{
    $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }

    return $randomString;
}

function getIndianCurrency(float $number)
{
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(
        0 => '',
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine',
        10 => 'Ten',
        11 => 'Eleven',
        12 => 'Twelve',
        13 => 'Thirteen',
        14 => 'Fourteen',
        15 => 'Fifteen',
        16 => 'Sixteen',
        17 => 'Seventeen',
        18 => 'Eighteen',
        19 => 'Nineteen',
        20 => 'Twenty',
        30 => 'Thirty',
        40 => 'Forty',
        50 => 'Fifty',
        60 => 'Sixty',
        70 => 'Seventy',
        80 => 'Eighty',
        90 => 'Ninety'
    );
    $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
    while ($i < $digits_length) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $counter = count($str);
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . ' ' . $hundred : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . ' ' . $hundred;
        } else
            $str[] = null;
    }
    $Rupees = implode('', array_reverse($str));
    if ($decimal > 10 and $decimal < 20) {
        $paise = ($decimal > 0) ? "And " . ($words[$decimal]) . ' Paise' : '';
    } else {
        $paise = ($decimal > 0) ? "And " . ($words[intval($decimal / 10, 10) * 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
    }

    return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise;
}

function ExportFile($records)
{
    $heading = false;
    if (!empty($records))
        foreach ($records as $row) {
            if (!$heading) {
                // display field/column names as a first row
                echo implode("\t", array_keys($row)) . "\n";
                $heading = true;
            }
            echo implode("\t", array_values($row)) . "\n";
        }
    exit;
}

//sort array by closest lat and lng coordinates

function sortByNearestLatLong($geoData, $lat, $long, $returnNearestOnly = true)
{
    // CREATE AN ARRAY FOR USE INSIDE THE FUNCTION
    $arrCloseMatchLat = array();
    $arrCloseMatchLong = array();
    $matchedGeoSet = array();

    // LOOP THROUGH ALL THE $geoData ARRAY AND SUBTRACT THE GIVEN LAT & LONG VALUES
    // FROM THOSE CONTAINED IN THE ORIGINAL ARRAY: $geoData
    // WE KNOW THAT THE SMALLER THE RESULT OF THE SUBTRACTION; THE CLOSER WE ARE
    // WE DO THIS FOR BOTH THE LONGITUDE & LATITUDE... HENCE OUR ARRAY:
    // $arrCloseMatchLat AND $arrCloseMatchLong RESPECTIVELY
    foreach ($geoData as $iKey => $arrGeoStrip) {
        $arrCloseMatchLat[$iKey] = abs(floatval(($arrGeoStrip['latitude']) - $lat));
        $arrCloseMatchLong[$iKey] = abs(floatval(($arrGeoStrip['longitude']) - $long));
    }


    // WE SORT BOTH ARRAYS NUMERICALLY KEEPING THE KEYS WHICH WE NEED FOR OUR FINAL RESULT
    arsort($arrCloseMatchLat, SORT_NUMERIC);
    arsort($arrCloseMatchLong, SORT_NUMERIC);

    // WE CAN RETURN ONLY THE RESULT OF THE FIRST, CLOSEST MATCH
    if ($returnNearestOnly) {
        foreach ($arrCloseMatchLat as $index => $difference) {
            $matchedGeoSet['latitudes'][] = $geoData[$index];
            break;
        }
        foreach ($arrCloseMatchLong as $index => $difference) {
            $matchedGeoSet['longitudes'][] = $geoData[$index];
            break;
        }
        // OR WE CAN RETURN THE ENTIRE $geoData ARRAY ONLY SORTED IN A "CLOSEST FIRST" FASHION...
        // WE DO THIS FOR BOTH THE LONGITUDE & LATITUDE RESPECTIVELY SO WE END UP HAVING 2
        // ARRAYS: ONE THAT SORTS THE CLOSEST IN TERMS OF LONG VALUES
        // AN ONE THAT SORTS THE CLOSEST IN TERMS OF LAT VALUES...
    } else {
        foreach ($arrCloseMatchLat as $index => $difference) {
            $matchedGeoSet['latitudes'][] = $geoData[$index];
        }
        foreach ($arrCloseMatchLong as $index => $difference) {
            $matchedGeoSet['longitudes'][] = $geoData[$index];
        }
    }
    return $matchedGeoSet;
}

function sortByNearestLatLongPoints($geoData, $lat, $long, $returnNearestOnly = false)
{

    $arrCloseMatch = array();
    $matchedGeoSet = array();

    // LOOP THROUGH ALL THE $geoData ARRAY AND SUBTRACT THE GIVEN LAT & LONG VALUES
    // FROM THOSE CONTAINED IN THE ORIGINAL ARRAY: $geoData
    // WE KNOW THAT THE SMALLER THE RESULT OF THE SUBTRACTION; THE CLOSER WE ARE
    // WE DO THIS FOR BOTH THE LONGITUDE & LATITUDE... HENCE OUR ARRAY 
    // $arrClostMatch and add the values calculated for Lat/Lon:
    foreach ($geoData as $iKey => $arrGeoStrip) {
        $arrCloseMatch[$iKey] = abs(floatval(($arrGeoStrip['latitude']) - $lat)) + abs(floatval(($arrGeoStrip['longitude']) - $long));
    }

    // SORT ARRAY NUMERICALLY KEEPING THE KEYS WHICH WE NEED FOR OUR FINAL RESULT
    asort($arrCloseMatch, SORT_NUMERIC);

    // RETURN CLOSEST OR FULL LIST
    foreach ($arrCloseMatch as $index => $difference) {

        $matchedGeoSet[] = $geoData[$index];
        if ($returnNearestOnly == true) {
            break;
        }
    }

    return $matchedGeoSet;
}

function getNewGRNNumber($brId)
{
    global $db;
    $shortName = $db->getItemFromDB("SELECT branch_shortname FROM  finascop_branch  where br_ID = {$brId}");
    $lastOrderNo = $db->getItemFromDB("SELECT right(retgrn_name,3)*1 as grnNum FROM  retaline_grn  where retgrn_billingTo = {$brId} ORDER BY retgrn_id DESC LIMIT 1");
    $lastOrderNo = (!empty($lastOrderNo)) ? $lastOrderNo : 0;
    return $shortName . '/GRN/' . date('ym') . '/' . str_pad(($lastOrderNo + 1), 3, '0', STR_PAD_LEFT);
}

function getNewPONumber($brId)
{
    global $db;
    $shortName = $db->getItemFromDB("SELECT branch_shortname FROM  finascop_branch  where br_ID = {$brId}");
    $lastOrderNo = $db->getItemFromDB("SELECT right(fpo_poNumber,3)*1 as PONum FROM  finascop_purchase_order  where fpo_centralStore = {$brId} ORDER BY fpo_id DESC LIMIT 1");
    $lastOrderNo = (!empty($lastOrderNo)) ? $lastOrderNo : 0;
    return $shortName . '/PO/' . date('ym') . '/' . str_pad(($lastOrderNo + 1), 3, '0', STR_PAD_LEFT);
}

function getNewTONumber($brId)
{
    global $db;
    $shortName = $db->getItemFromDB("SELECT branch_shortname FROM  finascop_branch  where br_ID = {$brId}");
    $lastOrderNo = $db->getItemFromDB("SELECT right(fsto_uid,3)*1 as ToNum FROM  finascop_stock_transfer_order where `fsto_source` = {$brId} ORDER BY fsto_id DESC LIMIT 1");
    $lastOrderNo = (!empty($lastOrderNo)) ? $lastOrderNo : 0;
    return $shortName . '-TOR-' . date('ym') . '-' . str_pad(($lastOrderNo + 1), 3, '0', STR_PAD_LEFT);
}

function getNewTRNumber($brId)
{
    global $db;
    $shortName = $db->getItemFromDB("SELECT branch_shortname FROM  finascop_branch  where br_ID = {$brId}");
    $lastOrderNo = $db->getItemFromDB("SELECT right(fstr_uid,3)*1 as TRNum FROM  finascop_stock_transfer_request where `fstr_source` = {$brId} ORDER BY fstr_id DESC LIMIT 1");
    $lastOrderNo = (!empty($lastOrderNo)) ? $lastOrderNo : 0;
    return $shortName . '/TRQ/' . date('ym') . '/' . str_pad(($lastOrderNo + 1), 3, '0', STR_PAD_LEFT);
}

function getNewSRNumber($brId)
{
    global $db;
    $shortName = $db->getItemFromDB("SELECT branch_shortname FROM  finascop_branch  where br_ID = {$brId}");
    $lastOrderNo = $db->getItemFromDB("SELECT right(fsr_uid,3)*1 as SRNum FROM  finascop_stock_request where `fsr_source` = {$brId} ORDER BY fsr_id DESC LIMIT 1");
    $lastOrderNo = (!empty($lastOrderNo)) ? $lastOrderNo : 0;
    return $shortName . '/SRQ/' . date('ym') . '/' . str_pad(($lastOrderNo + 1), 3, '0', STR_PAD_LEFT);
}

function getNewDTCNumber($brId)
{
    global $db;
    $shortName = $db->getItemFromDB("SELECT branch_shortname FROM  finascop_branch  where br_ID = {$brId}");
    $lastOrderNo = $db->getItemFromDB("SELECT right(rdc_uid,3)*1 as DTCNum FROM  retaline_distribution_chart  where rdc_destination = {$brId} ORDER BY rdc_id DESC LIMIT 1");
    $lastOrderNo = (!empty($lastOrderNo)) ? $lastOrderNo : 0;
    return $shortName . '/DTC/' . date('ym') . '/' . str_pad(($lastOrderNo + 1), 3, '0', STR_PAD_LEFT);
}

function getNewTransferInvoiceNumber($brId)
{
    global $db;
    $shortName = $db->getItemFromDB("SELECT branch_shortname FROM  finascop_branch  where br_ID = {$brId}");
    $lastOrderNo = $db->getItemFromDB("SELECT right(tiInvoiceNumber,3)*1 as tiInvoiceNumber FROM  TransferInvoice  where tiDestination = {$brId} ORDER BY id DESC LIMIT 1");
    $lastOrderNo = (!empty($lastOrderNo)) ? $lastOrderNo : 0;
    return $shortName . '/TINV/' . date('ym') . '/' . str_pad(($lastOrderNo + 1), 3, '0', STR_PAD_LEFT);
}
function getNewPOSNumber($brId)
{
    global $db;
    $shortName = $db->getItemFromDB("SELECT branch_shortname FROM  finascop_branch  where br_ID = {$brId}");
    $lastOrderNo = $db->getItemFromDB("SELECT right(posNumber,3)*1 as TRNum FROM  pos_orders where `posSource` = {$brId} ORDER BY posId DESC LIMIT 1");
    $lastOrderNo = (!empty($lastOrderNo)) ? $lastOrderNo : 0;
    return $shortName . '/POS/' . date('ym') . '/' . str_pad(($lastOrderNo + 1), 3, '0', STR_PAD_LEFT);
}
function generateRandomString($length = 10)
{
    $characters = '3479acdefghjklmnprtwxyACDEFHIJKLMNPRTWXY';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}
function loadDeficientDeliverJobDetails()
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
    $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
    $filter = $_POST['filter'];
    $search = " ";
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
                                $search .= " and (quor_RefNo LIKE '{$field['data']['value']}%') ";
                            } else {
                                $search .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                            }
                        }
                    }
                    break;
                case 'date':
                    //                        $value = str_replace("/", "", $value);
                    //                        $value = substr($value, 4, 4) . substr($value, 0, 2) . substr($value, 2, 2);
                    $value = date('Y-m-d', strtotime($field[data][value]));
                    $search .= " AND  DATE_FORMAT(quor_CreatedOn,'%Y-%m-%d') " . $comparisons[$field['data']['comparison']] . " '{$value}'";

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
    if (!empty($_POST['searchOrderNumber'])) {
        $search .= " and  order_order_id = '{$_POST['searchOrderNumber']}'";
    }
    if (!empty($_POST['resolved']) == 1) {
        $cond = " INNER JOIN retaline_customer_order_history ON retaline_customer_order_history.order_id = retaline_customer_order.order_id AND order_status = 55 ";
        $search .= " AND quor_Status in (38,15,40)";
    }else{
        $cond = " ";
        $search .= " AND quor_Status = 41 ";
    }
    if ($sort == 'booked_at' || $sort == 'orgOrderDate') {
        $sort = 'quor_id';
    }
    if ($br_id > 0) {
        $brQuery = " AND areaId = {$br_id} ";
    }
    $qry = "SELECT COUNT(*) FROM " . FINASCOP_DB . "qugeo_order 
        INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status INNER JOIN finascop_stock_transfer_order ON fsto_id = quor_TransferOrder_id 
        INNER JOIN retaline_customer_order ON order_id = fstr_id INNER JOIN finascop_branch ON br_ID = order_branch_id 
        INNER JOIN finascop_branch_group ON store_group_id = br_storeGroup {$cond} WHERE   quor_TransferOrder_Type = 1   {$brQuery} {$search} ";

    $query = "SELECT quor_RefNo as booking_no,DATE_FORMAT(quor_CreatedOn,'%d-%m-%Y') as booked_at,quor_PickupPhone,quor_PickupName,quor_DeliveryName,quor_DeliveryPhone,quor_Deliverybr_id,quor_Pickupbr_id,"
        . "quor_PickupLocation as source,quor_TransferOrder_Type,quor_TransferOrder_id,quor_CreatedOn,quor_DeliveryMethodsAllowed,"
        . "quor_DeliveryLocation as destination,quor_AmountCollectible,quor_Paymode,"
        . "if(quor_Status=22,'PICKUP', if(quor_Status=31,'DELIVERY','')) as drivetype,"
        . "quor_id,quor_PickupLat,quor_PickupLng,quor_DeliveryLat,quor_DeliveryLng,"
        . " '" . GMAP_DELIVERY_ICON . "' as deliverymapicon ,'" . GMAP_PIKCUP_ICON . "' as pickupmapicon, "
        . "dls_DelStatus ,quor_Status,"
        . "CASE WHEN quor_Type=1 THEN 'Drive' WHEN quor_Type=2 THEN 'Hired' WHEN quor_Type=3 THEN 'Customer Pickup' WHEN quor_Type=4 THEN 'Courier' WHEN quor_Type=5 THEN 'Driver Pickup' WHEN quor_Type=6 THEN 'Manual Delivery' END AS quor_TypeName,quor_Type,"
        . "DATE_FORMAT(quor_ScheduleOpeningTime,'%d-%m-%Y %H:%i:%s') as quor_ScheduleOpeningTime,order_delivery_charge,order_branch_id,payment_mode,store_group_name,
        fstr_id,DATE_FORMAT(retaline_customer_order.created_at,'%d-%m-%Y %H:%i:%s') as  created_at "
        . " FROM " . FINASCOP_DB . " qugeo_order 
        INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status 
        INNER JOIN finascop_stock_transfer_order ON fsto_id = quor_TransferOrder_id 
        INNER JOIN retaline_customer_order ON order_id = fstr_id  
        INNER JOIN finascop_branch ON br_ID = order_branch_id 
        INNER JOIN finascop_branch_group ON store_group_id = br_storeGroup {$cond} "
        . "WHERE   quor_TransferOrder_Type = 1  {$brQuery} {$search} ORDER BY  {$sort} {$dir} LIMIT $recStart,$recLimit";

    $data = $db->getMultipleData($query, true);
    $totalCount = $db->getItemFromDB($qry);
    $resCount = count($data);
    for ($i = 0; $i < $resCount; $i++) {
        $attVehicles = array();
        if ($data[$i]['booking_no'] > 0) {
            $attVehicles['PartitionKey'] = array('col' => 'orderID', 'val' => $data[$i]['booking_no'], 'oper' => '=');
            $attVehicles['SortKey'] = array('col' => 'orderMethod', 'val' => '3', 'oper' => '=');
            $attVehicles['IndexName'] = 'orderID-orderMethod-index';
            $attVehicles['queryAttributes'] = array('orderID', 'orderMethod', 'request', 'response', 'type');
            $attVehicles['Condition'] = array();
            array_push($attVehicles['Condition'], array('col' => 'type', 'val' => '1', 'oper' => '='));

            $consignmentLogs = $nodb->query('shipping_consignment_log', $attVehicles, 'query');
            if (count($consignmentLogs) > 0) {
                $consignResponse = [];
                foreach ($consignmentLogs as $item) {
                    if ($item['request'] === 'Partner List') {
                        $decoded = json_decode($item['response'], true); // Decode JSON
                        if (is_array($decoded)) {
                            $consignResponse = array_merge($consignResponse, $decoded); // Merge into responses array
                        }
                    }
                }
                // Initialize variables to store the minimum delivery charge and the corresponding array
                $minDeliveryCharge = PHP_INT_MAX;
                $minChargeArray = null;
                // Loop through the responses to find the array with the lowest delivery charge
                foreach ($consignResponse as $response) {
                    if (isset($response['delivery_charge']) && $response['delivery_charge'] < $minDeliveryCharge) {
                        $minDeliveryCharge = $response['delivery_charge'];
                        $minChargeArray = $response;
                    }
                }
                $data[$i]['partner_id'] = $minChargeArray['partner_id'];
                $data[$i]['amtRequired'] = $minChargeArray['delivery_charge'];
                $data[$i]['storeGroup'] = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group 
                INNER JOIN finascop_branch ON br_storeGroup = store_group_id WHERE br_ID = {$data[$i]['order_branch_id']}");
                $storeShare =  $db->getItemFromDB("SELECT shareValue FROM delivery_cost_share WHERE branchId = {$data[$i]['order_branch_id']}");
                $data[$i]['storeShare'] = ($storeShare > 0 ? $storeShare : 0);
                $data[$i]['amtDeficit'] = $minChargeArray['delivery_charge'] - $data[$i]['order_delivery_charge'];
                $data[$i]['amtAdjusted'] = 0;
                $data[$i]['deliveryTolerance'] = 0;

                $data[$i]['paidAmt'] = $data[$i]['order_delivery_charge'];
                //print_r($minChargeArray);
            }
        }


        $fstr_id = $data[$i]['fstr_id'];
        $data[$i]['orderMethod'] = defineMenuinDeliJobs($data[$i]['quor_DeliveryMethodsAllowed']);
        if ($fstr_id > 0) {
            $data[$i]['orgOrderDate'] = $data[$i]['created_at'];
            $data[$i]['SONumber'] = $db->getItemFromDB("SELECT SONumber FROM B2CSalesOrder WHERE customer_order_id = {$fstr_id}");
        }
    }

    if ($totalCount > 0) {
        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
    } else {
        echo '{"totalCount":0,"data":[]}';
    }
}

function ozontelCallcenter($agentID, $phone, $uniqueId)
{
    $url = OZTEL_URL;
    $fields = array(
        'api_key'  => OZTEL_API_KEY,
        'username' => OZTEL_USERNAME,
        'agentID'  => $agentID,
        'campaignName' => OZTEL_CAMPAIGN,
        'customerNumber' => $phone,
        'uui' => $uniqueId,
        'UCID' => true
    );
    $callurl = $url . '?' . http_build_query($fields, '', '&', PHP_QUERY_RFC3986);

    $opts = array(
        CURLOPT_URL => $callurl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array('Content-Type: application/json')
    );
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $response = curl_exec($ch);
    curl_close($ch);


    return $response;
}

function voxbayCallcenter($phone, $agentID)
{
    $phone = ltrim($phone, '+');
    $url = VOXBAY_URL;
    $fields = array(
        'id_dept' => 0,
        'uid'  => UID,
        'upin' => PIN,
        'user_no'  => $agentID,
        'destination' => $phone
    );
    $callurl = $url . '?' . http_build_query($fields, '', '&', PHP_QUERY_RFC3986);
    $opts = array(
        CURLOPT_URL => $callurl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        //CURLOPT_SSL_VERIFYPEER => false
    );
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $response = curl_exec($ch);  
    curl_close($ch);
    return $response;
}
