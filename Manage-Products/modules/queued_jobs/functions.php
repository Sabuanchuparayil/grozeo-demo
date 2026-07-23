<?php

require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/finascop_config/config.php');

function loadQueuedJobs() {
    $nodb = new \cgoDynamiteDB();
    global $db;
    $recLimit = intval($_POST['limit']);
    $recStart = intval($_POST['start']);
    $recSort = $_POST['sort'];
    $recSortDir = $_POST['dir'];
    $br_id = $_POST['br_id'];
    $date = $_POST['date'];
    
    $recLimit = $recLimit == 0 ? 20 : $recLimit;

    $ind = intval($_POST['ind']);
    if (!empty($br_id)) {
        if ($ind == 1) {
            $qry = "SELECT COUNT(*) FROM qugeo_order "
                    . "WHERE quor_PickupToBeManual =1    AND quor_Date = '{$date}' AND "
                    . "(quor_Status =" . ORDER_PICKUP_AT_ORIGIN_DLS_ID . " OR"
                    . " quor_Status = " . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . ") AND "
                    . $br_id . " = if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . ",quor_Deliverybr_id,quor_Pickupbr_id)";


            $query = "SELECT quor_RefNo as booking_no,quor_Date as booked_at,"
                    . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " or quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",quor_PickupName,quor_DeliveryName) as customer,"
                    . "quor_PickupLocation as source,"
                    . "quor_DeliveryLocation as destination,"
                    . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . ",'DELIVER', if(quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",'DISPATCH','PICKUP')) as type,"
                    . "quor_id,"
                    . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " or quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",quor_DeliveryLat,quor_PickupLat) as latitude,"
                    . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " or quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",quor_DeliveryLng,quor_PickupLng) as longitude, "
                    . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " or quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",' " . GMAP_DELIVERY_ICON . "','" . GMAP_PIKCUP_ICON . "') as mapicon, "
                    . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . ",getBranchGodownGeoLocation(quor_Deliverybr_id,1), getBranchGodownGeoLocation(quor_Pickupbr_id,1)) as brGodownLati,"
                    . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . ",getBranchGodownGeoLocation(quor_Deliverybr_id,2), getBranchGodownGeoLocation(quor_Pickupbr_id,2)) as brGodownLong"
                    . " FROM qugeo_order "
                    . "WHERE   "
                    . "((quor_PickupToBeManual =1 AND (quor_Status =" . ORDER_PICKUP_AT_ORIGIN_DLS_ID . " OR quor_Status =" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ") AND quor_Pickupbr_id = " . $br_id . ") OR "
                    . " (quor_DeliveryToBeManual =1 AND quor_Status = " . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " AND quor_Deliverybr_id =" . $br_id . " ))  "
                    . " ORDER BY quor_Date ASC LIMIT $recStart,$recLimit";
            $data = $db->getMultipleData($query, true);
            $totalCount = $db->getItemFromDB($qry);
        } else {

            $qry = "SELECT COUNT(*) FROM qugeo_order "
                    . "WHERE  (quor_Status between " . ORDER_PICKUP_FLAGGED_TOBR_DLS_ID . " AND "
                    . ORDER_PICKUP_PICKEDUP_TODST_DLS_ID
                    . " OR quor_Status =" . ORDER_DELIVERY_OUT_FOR_DELIVERY . ") AND quor_Date = '{$date}' AND "
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
                    . "quor_id FROM qugeo_order "
                    . "WHERE  quor_Date = '{$date}' AND ((quor_Status between " . ORDER_PICKUP_FLAGGED_TOBR_DLS_ID . " AND " . ORDER_PICKUP_PICKEDUP_TODST_DLS_ID
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
                    $arrAPI['getAttributes'] = array('extrainfo', 'id');
                    $rsno = $nodb->query('APIHistory', $arrAPI, 'getItem');
                    if (isset($rsno) && count($rsno) > 0) {
                        $vehno = $rsno['extrainfo'];
                        $driverId = $rsno['id'];
                        $driverDetails = $db->getFromDB("SELECT d_Name,d_Ph1 FROM qugeo_driver WHERE d_ID = {$driverId}", TRUE);
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
