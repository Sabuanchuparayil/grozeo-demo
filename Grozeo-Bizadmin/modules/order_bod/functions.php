<?php
	
	require_once(ROOT . '/finascop_config/lib.php');
	require_once(ROOT . '/finascop_config/config.php');
	
	require_once(EXTERNAL_LIBRARY_PATH);
	require_once(QUGEO_API_ROOT . '/Models/Utils.php');
	require_once(INCLUDE_PATH . '/lib.php');
	require_once(INCLUDE_PATH . '/config.php');

function isliveDriverSSession($apikey) {
    global $db;
    $qry = "select count(*) as ss from qugeo_driver where d_apikey = '" . $apikey . "'";
    return intval($db->getItemFromDB($qry));
}
function getUsedVehicleDetails($apikey) {
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
    function loadBODJobs() {


        $nodb = new \cgoDynamiteDB();
        global $db;
        $recLimit = intval($_POST['limit']);
        $recStart = intval($_POST['start']);
        $sort = $_POST['sort'];
        $dir = $_POST['dir'];
        $sort = empty($sort) ? 'quor_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        
        $recLimit = $recLimit == 0 ? 20 : $recLimit;
        $filter = $_POST['filter'];
        if ($_SESSION['admin']->br_PyramidLevel == 1) {
            $search = " ";
            /*if ($_SESSION['admin']->IsSuperUser == 'Yes') {
                $search = " ";
            } else {
                $search = " and order_branch_id = {$_POST['br_id']} ";
            }*/
        } else {
            $search = " and order_branch_id = {$_POST['br_id']} ";
        }
        
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
       
        
            $qry = "SELECT COUNT(*) FROM  qugeo_order " . " INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status INNER JOIN finascop_stock_transfer_order ON quor_TransferOrder_id = fsto_id and fsto_ordertype = 1
            INNER JOIN retaline_customer_order ON order_id = fstr_id AND payment_mode = 6 WHERE (quor_type in (2,3,4) and quor_Status=9 ) or (quor_type=1 and quor_Status = 38) or (quor_Status = 15) AND quor_TransferOrder_Type = 1 {$search} ";
    
            $query = "SELECT quor_RefNo as booking_no,DATE_FORMAT(quor_Date,'%d-%m-%Y') as booked_at,order_ondel_bankref_id,"
                    . "quor_DeliveryName,quor_DeliveryPhone,quor_Deliverybr_id,quor_Pickupbr_id,(SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_source) "
                    . " AS source,quor_UpdateOn,quor_DeliveryDriverId,"
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
                    . " INNER JOIN finascop_stock_transfer_order ON quor_TransferOrder_id = fsto_id and fsto_ordertype = 1
                    INNER JOIN retaline_customer_order ON order_id = fstr_id AND payment_mode = 6 "
                    . " WHERE (quor_type in (2,3,4) and quor_Status=9 ) or (quor_type=1 and quor_Status = 38) or (quor_Status = 15) AND quor_TransferOrder_Type = 1 {$search} "
                    . " ORDER BY /*CAST({$sort} as char) {$dir},binary */{$sort} {$dir} ";//LIMIT $recStart,$recLimit
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
                    if (in_array($data[$i]['quor_Status'], array(23, 24, 25, 26, 27, 28, 29, 35, 36, 37))) {
                        $data[$i]['IsPickup'] = 1;
                    }
                    if (($data[$i]['quor_Status'] >= 32) && ($data[$i]['quor_Status'] <= 38)) {
                        $data[$i]['IsPickup'] = 0;
                    }
                    $data[$i]['status'] = $data[$i]['djStatus'];
                    if ($data[$i]['quor_Type'] > 1) {
                        $data[$i]['IsEditable'] = false;
                    }
                    $data[$i]['driver'] = $db->getItemFromDB("SELECT CONCAT(d_Name,'',l_Name) FROM qugeo_driver WHERE d_ID = {$data[$i]['quor_DeliveryDriverId']}");
					$data[$i]['v_no'] = "";
					switch ($data[$i]['quor_TransferOrder_Type']) {
						case 0:
						break;
						case 1:
						$order_customer_id = $db->getItemFromDB("SELECT order_customer_id FROM retaline_customer_order WHERE order_id = {$data[$i]['fstr_id']}");
						$data[$i]['customer'] = $db->getItemFromDB("SELECT cust_customer_name FROM retaline_customer WHERE cust_id = {$order_customer_id}");
						break;
						case 2:
						break;
						case 3:
						break;
						case 4:
						break;
					}
                    switch ($data[$i]['quor_Type']) {
                        case 1:
                            $activedriveid = ($data[$i]['quor_QugeoPickupDDBDriverId'] == '' ? $data[$i]['quor_QugeoDeliveryDDBDriverId'] : $data[$i]['quor_QugeoPickupDDBDriverId']);
                            $activepoll = isliveDriverSSession($activedriveid);
                            $data[$i]['IsEditable'] = ($activepoll > 0 ? false : true); //check if vehicle live VISHNU     
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
        
    }