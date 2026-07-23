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

function getdata($dates, $DriverId) {
		$nodb = new \cgoDynamiteDB();
		//For each date
		$driverjobdetails = array();
		
		foreach ($dates as $Date) {
			
			$arrDriver = array();
			$arrDriver['PartitionKey'] = array('col' => 'DriverId', 'val' => (int)$DriverId, 'oper' => '=');
			$arrDriver['SortKey'] = array('col' => 'Date', 'val' => (int)$Date, 'oper' => '=');
			$arrDriver['getAttributes']=array('data');
			$drrs = $nodb->query('DriverActivitySummary',$arrDriver,'getItem');	
			if (isset($drrs) && count($drrs) > 0) {
				$daterow = $drrs['data'];
				}else{
				$tripcount = 0;
				$orderdist = 0;
				$ordercompletedist = 0;
				$ordercount = 0;
				$daterow = array();
				$daterow['activity_date'] = substr($Date, 0, 4) .'-'. substr($Date, 4,2) .'-'. substr($Date,6,2);		
				$daterow['TotalJobs'] = 0;
				$daterow['JobsCompl'] = 0;
				$daterow['DayStart'] = '';
				$daterow['DayEnd'] = '';
				$daterow['FirstJob'] = '';
				$daterow['LastJob'] = '';
				$daterow['TotalKm'] = 0;
				$daterow['AvgLocKm'] = 0;
				$daterow['TotalTrips'] =0;
				$daterow['WorkingHours'] ='';
				$daterow['details'] = array();
				$arrDriver = array();
				$arrDriver['PartitionKey'] = array('col' => 'DriverId', 'val' => (int)$DriverId, 'oper' => '=');
				$arrDriver['SortKey'] = array('col' => 'createddate', 'val' => (int)$Date, 'oper' => '=');
				$arrDriver['IndexName'] = 'DriverId-createddate-index';
				$arrDriver['queryAttributes'] = array('apikey', 'createddatetime', 'TotalJobs', 'LocationUpdateddatetime', 'v_no', 'createddate');
				$rsno = $nodb->query('QugeoLiveVehicles', $arrDriver, 'query');
				$degMat = new \cgoGeoUtilities();
				file_put_contents('php://stderr', print_r($rsno, TRUE));
				if (isset($rsno) && count($rsno) > 0) {
					//For each vehicle on that day
					foreach ($rsno as $value) {
						$daterow['TotalJobs'] = $value['TotalJobs']  + $daterow['TotalJobs'];
						if ($daterow['DayStart'] == '' || $daterow['DayStart'] > $value['createddatetime']) {
							$daterow['DayStart'] = $value['createddatetime'];
						}
						$arrEventKey = array();
						$arrEventKey = array();
						$arrEventKey['PartitionKey'] = array('col' => 'apikey', 'val' => $value['apikey'], 'oper' => '=');
						$arrEventKey['queryAttributes'] = array('latitude', 'longitude', 'extrainfo', 'tstamp');
						$arrEventKey['Condition']=array();
						array_push($arrEventKey['Condition'], array('col' => 'extrainfo.event', 'val' => 'locationupdate', 'oper' => "<>"));
						$Geors = $nodb->query('QugeoEventGeoLocations', $arrEventKey, 'query');
						$geodets = array();
						//For each geo location of the vehcile
						if (isset($Geors) && count($Geors) > 0) {
							$gotstart = false;
							//file_put_contents('php://stderr', print_r($Geors, TRUE));
							foreach ($Geors as $geos) {
								if ($daterow['DayEnd'] == '' || $daterow['DayEnd'] < substr($geos['tstamp'],0,14)) {
									$daterow['DayEnd'] = substr($geos['tstamp'],0,14);
								}
								if ($geos['extrainfo']['event'] == 'vehicleselected' && $gotstart == false) {
									$gotstart = true;
									$geodets['startlocation'] = array('latitude' => $geos['latitude'], 'longitude' => $geos['longitude']);
									} elseif ($geos['extrainfo']['event'] == 'GetOrderDetails') {
									$geodets['orderlocation'][$geos['extrainfo']['order']] = array('latitude' => $geos['latitude'], 'longitude' => $geos['longitude']);
								}
							}
						}
						$arrApiKey = array();
						$arrApiKey['PartitionKey'] = array('col' => 'apikey', 'val' => $value['apikey'], 'oper' => '=');
						$arrApiKey['queryAttributes'] = array('Latitude', 'Longitude', 'order', 'createddatetime', 'orderid', 'IsClosed');
						$nors = $nodb->query('QugeoLiveVehicleOrders', $arrApiKey, 'query');
						//For each order the vehicle handled
						if (isset($nors) && count($nors) > 0) {
							finascop_aasort($nors, 'order');
							$ctr = 0;
							if (!array_key_exists('startlocation', $geodets)) {
								$latitude = $nors[0]['Latitude'];
								$longitude = $nors[0]['Longitude'];
								} else {
								$latitude = $geodets['startlocation']['latitude'];
								$longitude = $geodets['startlocation']['longitude'];
							}
							foreach ($nors as $orders) {
								if ($daterow['FirstJob'] == '' || $daterow['FirstJob'] > $orders['createddatetime']) {
									$daterow['FirstJob'] = $orders['createddatetime'];
								}
								if ($daterow['LastJob'] == '' || $daterow['LastJob'] < $orders['createddatetime']) {
									$daterow['LastJob'] = $orders['createddatetime'];
								}
								$dist = 0;
								if ($orders['IsClosed'] == '1') {
									$jobdist = $degMat->GetDrivingDistance($latitude, $orders['Latitude'], $longitude, $orders['Longitude']);
									$latitude = $orders['Latitude'];
									$longitude = $orders['Longitude'];
									$orderdist = $orderdist + $jobdist;
									$dist = 0;
									$actlatitude = $geodets['orderlocation'][$orders['orderid']]['latitude'];
									$actlongitude = $geodets['orderlocation'][$orders['orderid']]['longitude'];
									//file_put_contents('php://stderr',  $actlatitude . ',' . $orders['Latitude'] . ',' . $actlongitude . ',' . $orders['Longitude'] . "\n" );
									$dist = $degMat->GetDrivingDistance($actlatitude, $orders['Latitude'], $actlongitude, $orders['Longitude']);
									$dist = number_format((float)$dist, 2, '.', '');
									array_push($daterow['details'], array("order" => $value['v_no'] . '_' . $value['createddatetime'] . '_' . $orders['orderid'], "Diff" => $dist,"Dist"=>$jobdist));
									$ordercompletedist = $ordercompletedist + $dist;
									$ordercount++;
								}
							}
						}
						$tripcount = $tripcount + 1; 
					}
					//date_format($orders['createddatetime'], 'YmdHis');
					$date1=date_create($daterow['FirstJob']);
					$date2=date_create($daterow['LastJob']);
					$interval = date_diff($date1,$date2);
					$daterow['WorkingHours'] =  $interval->format('%h')." : ".$interval->format('%i');
					
					$daterow['FirstJob']  = substr($daterow['FirstJob'], 0, 4) .'-'. substr($daterow['FirstJob'], 4,2) .'-'. substr($daterow['FirstJob'],6,2) .' '. substr($daterow['FirstJob'],8,2) .':'. substr($daterow['FirstJob'],10,2) .':'. substr($daterow['FirstJob'],12,2);
					$daterow['LastJob']  = substr($daterow['LastJob'], 0, 4) .'-'. substr($daterow['LastJob'], 4,	2) .'-'. substr($daterow['LastJob'],6,2) .' '. substr($daterow['LastJob'],8,2) .':'. substr($daterow['LastJob'],10,2) .':'. substr($daterow['LastJob'],12,2);
					$daterow['DayStart']  = substr($daterow['DayStart'], 0, 4) .'-'. substr($daterow['DayStart'], 4,2) .'-'. substr($daterow['DayStart'],6,2) .' '. substr($daterow['DayStart'],8,2) .':'. substr($daterow['DayStart'],10,2) .':'. substr($daterow['DayStart'],12,2);
					$daterow['DayEnd']  = substr($daterow['DayEnd'], 0, 4) .'-'. substr($daterow['DayEnd'], 4,2) .'-'. substr($daterow['DayEnd'],6,2) .' '. substr($daterow['DayEnd'],8,2) .':'. substr($daterow['DayEnd'],10,2) .':'. substr($daterow['DayEnd'],12,2);
					
					$daterow['JobsCompl'] = $ordercount;
					
					$daterow['TotalKm'] = $orderdist;
					$daterow['TotalTrips'] = $tripcount;
					$distav = number_format((float)($ordercompletedist / $ordercount), 2, '.', '');
					$daterow['AvgLocKm'] = $distav;
					
					$arrUpdate=array();				
					$arrUpdate['Data']=array();
					$valdate = date("YmdHis");			
					array_push($arrUpdate['Data'],array('col'=>'data','val'=>$daterow));				
					array_push($arrUpdate['Data'],array('col'=>'createddatetime','val'=>(int)$valdate ));				
					array_push($arrUpdate['Data'],array('col'=>'DriverId','val'=>(int)$DriverId ));				
					array_push($arrUpdate['Data'],array('col'=>'Date','val'=>(int)$Date ));				
					$insrs = $nodb->perform('DriverActivitySummary','insert',$arrUpdate,$response);	
					
				}
			}
			array_push($driverjobdetails, $daterow);
		}
		return $driverjobdetails;
	}

function loadCashCollectDetails() {


    $nodb = new \cgoDynamiteDB();
    global $db;
    $recLimit = intval($_POST['limit']);
    $recStart = intval($_POST['start']);
    $sort = $_POST['sort'];
    $dir = $_POST['dir'];
    $sort = empty($sort) ? 'quor_id' : $sort;
    $dir = empty($dir) ? 'DESC' : $dir;
    $d_ID = $_POST['d_ID'];
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
    if (!empty($d_ID)) {
        $qry = "SELECT COUNT(*) FROM " . FINASCOP_DB . "qugeo_order " . " INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status INNER JOIN finascop_stock_transfer_order ON quor_TransferOrder_id = fsto_id AND fsto_ordertype = 1
        INNER JOIN retaline_customer_order ON order_id = fstr_id AND payment_mode = 7 WHERE  (quor_Status = 38) AND quor_AmountCollectible > 0 AND quor_TransferOrder_Type = 1  and quor_DeliveryDriverId = {$d_ID} ";

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
                . " INNER JOIN finascop_stock_transfer_order ON quor_TransferOrder_id = fsto_id AND fsto_ordertype = 1
                INNER JOIN retaline_customer_order ON order_id = fstr_id AND payment_mode IN (4,7) "
                . " WHERE (quor_Status = 38) AND quor_AmountCollectible > 0 AND quor_TransferOrder_Type = 1 AND quor_DeliveryDriverId = {$d_ID} {$search} "
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
                $data[$i]['v_no'] = "";
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
