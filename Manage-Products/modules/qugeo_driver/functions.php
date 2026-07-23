<?php
	
	require_once(ROOT . '/finascop_config/lib.php');
	require_once(ROOT . '/finascop_config/config.php');
	
	require_once(EXTERNAL_LIBRARY_PATH);
	require_once(QUGEO_API_ROOT . '/Models/Utils.php');
	require_once(INCLUDE_PATH . '/lib.php');
	require_once(INCLUDE_PATH . '/config.php');
	function getdata($dates,$DriverId) {
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
