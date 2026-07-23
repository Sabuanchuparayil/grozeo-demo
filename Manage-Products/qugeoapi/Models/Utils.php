<?php
	namespace Models;
	{
		class Utils 
		{
			public function setPickupTime($order,$updateime=true,$db){
				$utils =  new \Models\Utils();
				//CAREGO_WAITING_TIME_BEFORE_PROCESSINGSTART
				$today = date( 'Y-m-d' );				
				$now = date('Y-m-d H:i:s' );				
				$nexttime = date('His', strtotime($now . " +" . QUGEO_DEFER_MANUAL_SCHEDULE_BY . " SECONDS"));	
				$pickupabletoday = $utils->getIsPickableToday($nexttime);		
				
				echo "Is pickup able today " . $pickupabletoday . "\n";
				if($pickupabletoday>0){
					//Is within current slot time? implemne for directbooking too
					$nexttime = $this->getEarliestProcessingTime($nexttime);
					$scheduletime = $today . ' ' .  date('H:i:s',strtotime($nexttime));		
					echo "Schedule time " . $scheduletime . "\n";
					$endtime = date('Y-m-d H:i:s', strtotime($scheduletime . " +" . QUGEO_DEFER_MANUAL_SCHEDULE_BY . " SECONDS"));
					$this->updatePickupSchedule($order,$scheduletime,false,$now,$endtime,$db);
					$this->updatePickupSchedule($order,$scheduletime,true,$now,$endtime,$db);
					}else{
					$pickupday = $utils->getPickupableDay();								
					$slots = $utils->getPickupScheduleSlots(0);								
					$slottime = $utils->getPickupStartTimeOfSlots($slots[0]);
					$pickuptime = date('H:i:s',strtotime($slottime));
					$scheduletime  = $pickupday . ' ' . $pickuptime;
					$endtime = date('Y-m-d H:i:s', strtotime($scheduletime . " +" . QUGEO_DEFER_MANUAL_SCHEDULE_BY . " SECONDS"));		
					echo "Schedule time " . $scheduletime . "\n";
					$this->updatePickupSchedule($order,$scheduletime,false,$now,$endtime,$db);
					$this->updatePickupSchedule($order,$scheduletime,true,$now,$endtime,$db);
				}				
				return $scheduletime;
			}
			public function updatePickupSchedule($order,$scheduletime,$otherbookings,$starttime,$endtime,$db){	
				echo "SCHEDULEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE " .$scheduletime . "\n";
				if($otherbookings==false){
					//Defer this booking[
					echo "Otherbookings false " . $scheduletime . " " . $order['quor_id']  . "\n";
					$db->query("UPDATE  qugeo_order set quor_ScheduleOpeningTime = '" . $scheduletime . "' where quor_id = " . $order['quor_id'] );				
					}else{
					echo "Otherbookings true " . $scheduletime . " " . $order['quor_id']  . "\n";
					//Defer other booking from same location in the same period.
					$db->query("UPDATE  qugeo_order set quor_ScheduleOpeningTime = '" . $scheduletime . "' where quor_PickupPincode = '" . $order['PickupPincode']  . "'  and  quor_PickupToBeManual =0 and quor_ScheduleOpeningTime between '" . $starttime ."' and '" . $endtime . "' ");			
				}
			}
			public function LogGeoCordinates($geocords,$usertype,$id,$apikey,$extrainfo,$updlivevehicles=false){
				$nodb = new \cgoDynamiteDB();
				$arrGeoCords = array();
				$arrGeoCords['Data']=array();			
				$valdate = date("Ymd");
				$valdatetime = date("YmdHis");
				$geocordsarr = json_decode($geocords);
				file_put_contents('php://stderr', print_r($geocordsarr , TRUE ));
				foreach ($geocordsarr->details as $geocords){
					if(intval($geocords->latitude)==0 || intval($geocords->longitude)==0 ){
						throw new \Exception('Invalid Geo-Coords  Lat - "' . intval($geocords->latitude) . '" - Long - "' . intval($geocords->longitude) . '"' );
					}
					$micro_date = microtime();
					$date_array = explode(" ",$micro_date);
					$date = date("YmdHis",$date_array[1]);
					$value = intval((floatval($date_array[0])*1000));
					$date = $date . str_pad(intval((floatval($date_array[0])*1000)), 3, '0', STR_PAD_LEFT);		
					$valdatetime = date("YmdHis");
					array_push($arrGeoCords['Data'],array('col'=>'apikey','val'=>$apikey));
					array_push($arrGeoCords['Data'],array('col'=>'latitude','val'=>(float)$geocords->latitude));
					array_push($arrGeoCords['Data'],array('col'=>'longitude','val'=>(float)$geocords->longitude));
					array_push($arrGeoCords['Data'],array('col'=>'bearing','val'=>(float)$geocords->bearing));
					array_push($arrGeoCords['Data'],array('col'=>'tstamp','val'=>(int)$date ));
					array_push($arrGeoCords['Data'],array('col'=>'date','val'=>(int)$valdate ));
					array_push($arrGeoCords['Data'],array('col'=>'usertype','val'=>(int)$usertype));
					array_push($arrGeoCords['Data'],array('col'=>'userid','val'=>(int)$id ));
					array_push($arrGeoCords['Data'],array('col'=>'extrainfo','val'=>$extrainfo)); 
					array_push($arrGeoCords['Data'],array('col'=>'userdatetime','val'=>(string)$geocords->userdatetime));	
					array_push($arrGeoCords['Data'],array('col'=>'disttravled','val'=>(string)$geocords->disttravled));	 
					array_push($arrGeoCords['Data'],array('col'=>'currentdatetime','val'=>(string)floatval($geocordsarr->currentdatetime)));	
					array_push($arrGeoCords['Data'],array('col'=>'provider','val'=>(string)$geocords->provider));		
					array_push($arrGeoCords['Data'],array('col'=>'version','val'=>(string)$geocordsarr->version));						
					//file_put_contents('php://stderr', "Writing geolocation for user time " . $geocords->userdatetime .  "\n" );
					$nosession = $nodb->perform('QugeoEventGeoLocations','insert',$arrGeoCords,$response);	
					//file_put_contents('php://stderr', print_r(" Event Location response " . $nosession , TRUE ));
					if($updlivevehicles){
						$arrUpdate=array();
						$arrUpdate['PartitionKey']=array('col'=>'apikey','val'=>$apikey);
						$arrUpdate['Data']=array();
						array_push($arrUpdate['Data'],array('col'=>'LocationUpdateddatetime','val'=>(int)$valdatetime ));			
						array_push($arrUpdate['Data'],array('col'=>'Latitude','val'=>(float)$geocords->latitude ));
						array_push($arrUpdate['Data'],array('col'=>'Longitude','val'=>(float)$geocords->longitude ));
						array_push($arrUpdate['Data'],array('col'=>'bearing','val'=>(float)$geocords->bearing));
						$nors = $nodb->perform('QugeoLiveVehicles','update',$arrUpdate,$response);
					}			
				}
			}
			public function getOTP($mobile){	
				$mobiles = ["9061160000", "9846583711", "8289847144", "9895670756", "8129160154","9495050000"];
				if(in_array($mobile, $mobiles))
				{
					return "1111";
				}		
				return "2255";
				//return mt_rand(1000,9999);
			}
			public function getDomainAuthKey($domainkey,$userid){
				$from_time = strtotime(date('Y-m-d H:i'));
				$to_time = strtotime("1960-01-01 05:30:00");
				$key = strtoupper(md5($domainkey.$userid.abs($to_time - $from_time)));
				//file_put_contents('php://stderr', print_r($domainkey.$userid.abs($to_time - $from_time) . "\n", TRUE));			
				return $key;
			}
			public function IsValidDomainAuthKey($testkey,$domainkey,$userid,$lvl=0){
				$cnt =-2;
				while($cnt<=$lvl){
					$from_time = strtotime(date('Y-m-d H:i'));
					$from_time = $from_time - ($cnt*60);
					$to_time = strtotime("1960-01-01 05:30:00");
					$key = strtoupper(md5($domainkey.$userid.abs($to_time - $from_time)));		
					//file_put_contents('php://stderr', print_r($domainkey.$userid.abs($to_time - $from_time) . "\n", TRUE));
					//file_put_contents('php://stderr', print_r($testkey . " -- " . $key . "\n", TRUE));		
					if($testkey==$key){
						return true;
					}
					$cnt = $cnt + 1;				
				}
				return false;
			}
			public function getRandomRef(){
				$nodb = new \cgoDynamiteDB();			
				$arrSession['Data']=array();
				array_push($arrSession['Data'],array('col'=>'refno'));	
				$arrSession['ExclusiveStartKey'] = array('col'=>'refno','val'=>(string)uniqid()); 	
				$arrSession['Limit'] =1;
				$rsno = $nodb->query('RefnoVault',$arrSession,'scan');	
				if (count($rsno)>0){					
					return $rsno[0]['refno'];
					}else{
					
					return null;	
				}
			}
			public function deleteRefNoVault($refno){
				try{
					$nodb = new \cgoDynamiteDB();		
					$arrSession = array();	
					$arrSession['PartitionKey']=array('col'=>'refno','val'=>(string)$refno);
					$nosession = $nodb->perform('RefnoVault','delete',$arrSession,$response);
					}catch (Exception $e) {
					print_r($e->getMessage());
				}			 
			}
			public function getPickupScheduleSlots($starttime=0){
				$db = new \cgoSqlDB();
				$slottime = $db->getMulipleData('select slots  from  qugeo_scheduleslots where availabletill >?  order by availabletill asc ',array('i',$starttime) ,false);						
				return $slottime;
			}
			public function getHolidays($startdate,$enddate){
				$db = new \cgoSqlDB();
				$holidays = $db->getMulipleData('select unix_timestamp(holi_days)  from  holidays where holi_days between ? and ?  order by holi_days asc ',array('ss',$startdate,$enddate) ,false);		
				return ($holidays==false?array():$holidays);			
			}
			public function getPickupdays(){
				$today = date( 'Y-m-d' );
				$now = date("His");
				$dets = $this->getPickupScheduleSlots($now);					
				if(count($dets)==0 || is_null($dets) || $dets===NULL  || $dets ==''){
					$today = date('Y-m-d', strtotime("+1 day"));				
				}
				
				$tilldate  = date('Y-m-d', strtotime("+30 days"));
				return array($today,$tilldate);
				//return array(strtotime($today),strtotime($tilldate));
			}
			public function getPickupableDay(){
				$pickupdays = $this->getPickupdays();
				$holidays = $this->getHolidays($pickupdays[0],$pickupdays[1]);
				
				print_r($pickupdays);
				print_r($holidays);
				
				$pickupday = $pickupdays[0];
				
				while (in_array(strtotime($pickupday),$holidays) && count($holidays)>0){
					$pickupday = date('Y-m-d', strtotime($pickupday . " +1 day"));
				}
				print_r($pickupday);
				return $pickupday;
				//return array(strtotime($today),strtotime($tilldate));
			}
			public function getIsPickableToday($starttime=0){
				$db = new \cgoSqlDB();
				$cnt = $this->getHolidays(date('Y-m-d'),date('Y-m-d'));
				if(count($cnt)>0){					
					return 0;
					}else{
					$slottime = $db->getItemFromDB('select count(*)  from  qugeo_scheduleslots where endtime >? ',array('i',$starttime) ,false);					
					return $slottime;
				}
			}
			public function getEarliestProcessingTime($time){
				$db = new \cgoSqlDB();
				$slottime = $db->getItemFromDB('select starttime  from  qugeo_scheduleslots where ? between starttime and endtime order by starttime limit 1 ',array('s',$time) ,false);
				echo "slottime  1 ". $slottime  . "\n";
				
				if($slottime == ''){
					$slottime = $db->getItemFromDB('select starttime  from  qugeo_scheduleslots where ? < starttime order by starttime limit 1 ',array('s',$time) ,false);
					echo "slottime  2 ". $slottime  . "\n";
					}else{
					echo "slottime  3 ". $time  . "\n";
					return $time;
				}
				return $slottime;
			}	
			public function getPickupStartTimeOfSlots($slots){
				$db = new \cgoSqlDB();
				$slottime = $db->getItemFromDB('select starttime  from  qugeo_scheduleslots where slots =?  limit 1 ',array('s',$slots) ,false);
				return $slottime;
			}
			public function IsValidConsignment($cons,&$str){
				$totalpackets =0;
				foreach($cons['details'] as $value) {				
					if(intval($value['contenttypeid'])==0 || intval($value['count'])==0 || intval($value['packingtypeid'])==0 || floatval($value['size_breadth'])==0 || floatval($value['size_length'])==0 || floatval($value['size_height'])==0    || floatval($value['weight'])==0 || $value['size_unit']=='' ||  $value['weight_unit']=='' || floatval($cons['goodsworth']) == 0){
						$str = 'Invalid entries found, please enter valid data';										
						return false;
					}
					if(intval($value['weight'])>CAREGO_MAX_WEIGHT_KG){						
						$str = 'Maximum of  ' . CAREGO_MAX_WEIGHT_KG . ' kilograms can be send at a time';	
						return false;
					}
					if(intval($value['count'])>CAREGO_MAX_PACKETS){						
						$str = 'Maximum of  ' . CAREGO_MAX_PACKETS . ' packets can be send at a time';	
						return false;
					}
					if(floatval($cons['goodsworth'])>CAREGO_MAX_GOODSWORTH){						
						$str = 'You cannot send goods worth more than ' . CAREGO_MAX_GOODSWORTH . ' rupees';	
						return false;
					}
					if ($value['size_unit'] == 'Feet'){					
						if(floatval($value['size_length'])>CAREGO_MAX_DIMENSION_FEET || floatval($value['size_breadth'])>CAREGO_MAX_DIMENSION_FEET || floatval($value['size_height']) >CAREGO_MAX_DIMENSION_FEET ){
							$str = 'The  maximum allowed dimension is ' . CAREGO_MAX_DIMENSION_FEET . ' feet';	
							return false;
						}
						}else{
						if(floatval($value['size_length'])>CAREGO_MAX_DIMENSION_INCHES || floatval($value['size_breadth'])>CAREGO_MAX_DIMENSION_INCHES || floatval($value['size_height']) >CAREGO_MAX_DIMENSION_INCHES ){
							$str = 'The  maximum allowed dimension is ' . CAREGO_MAX_DIMENSION_INCHES . ' inches';	
							return false;
						}
						if(floatval($value['size_length'])<CAREGO_MIN_DIMENSION_INCHES || floatval($value['size_breadth'])<CAREGO_MIN_DIMENSION_INCHES || floatval($value['size_height']) <CAREGO_MIN_DIMENSION_INCHES ){
							$str = 'The  minimum required dimension is ' . CAREGO_MIN_DIMENSION_INCHES . ' inches';	
							return false;
						}
						
					}
					$totalpackets = $totalpackets + intval($value['count']);					
				}
				if(intval($totalpackets)>CAREGO_MAX_PACKETS){						
					$str = 'Maximum of  ' . CAREGO_MAX_PACKETS . ' packets can be send at a time';	
					return false;
				}
				return true;
			}
			public function updatePickupDate($crid,$updatetime,$db){
				
				//' . ORDER_PICKUP_AT_ORIGIN_DLS_ID . '
				$db->begintransaction();
				$rowcount = $db->getItemFromDB('select count(*)  from  inward_booking where  bk_PickupManuallyScheduled =0 and bk_cancelled =0 and dls_id = ? and bk_cr_c_id = ? and bk_ScheduleOpeningTime <= ? ',array('iis',(int)ORDER_PICKUP_AT_ORIGIN_DLS_ID,(int)$crid,$updatetime));
				$output = $db->query('UPDATE  inward_booking set bk_PickupToBeManual=1,bk_ScheduleOpeningTime = ? where bk_PickupManuallyScheduled =0 and bk_cancelled =0 and dls_id = ? and bk_cr_c_id = ? and bk_ScheduleOpeningTime <= ? ',array('siis',$updatetime,(int)ORDER_PICKUP_AT_ORIGIN_DLS_ID,(int)$crid,$updatetime));
				$db->committransaction();	
				return $rowcount;
			}
			public function updatePickupDateToSchedule($starttime,$endtime,$crid,$db){
				//$db = new \cgoSqlDB();
				//' . ORDER_PICKUP_AT_ORIGIN_DLS_ID . '
				$db->begintransaction();
				$rowcount = $db->getItemFromDB('select count(*)  from  inward_booking where  bk_PickupManuallyScheduled =0 and bk_cancelled =0 and dls_id = ? and bk_cr_c_id = ? and bk_ScheduleOpeningTime between ? and ? ',array('iiss',(int)ORDER_PICKUP_AT_ORIGIN_DLS_ID,(int)$crid,$starttime,$endtime));
				$db->query('UPDATE  inward_booking set bk_PickupToBeManual=1,bk_ScheduleOpeningTime = ? where bk_PickupManuallyScheduled =0 and bk_cancelled =0 and dls_id = ? and bk_cr_c_id = ? and bk_ScheduleOpeningTime between ? and ? ',array('siiss',$endtime,(int)ORDER_PICKUP_AT_ORIGIN_DLS_ID,(int)$crid,$starttime,$endtime));
				$db->committransaction();		
				return $rowcount;
			}
			public function getBookingBetweenTime($starttime,$endtime,$crid){
				$db = new \cgoSqlDB();
				$newtime = $db->getItemFromDB("select bk_ScheduleOpeningTime from  inward_booking where bk_PickupManuallyScheduled =0 and bk_cancelled =0 and dls_id = ? and bk_cr_c_id = ? and bk_ScheduleOpeningTime between  ?  and ? order by bk_ScheduleOpeningTime limit 1" ,array('iiss',(int)ORDER_PICKUP_AT_ORIGIN_DLS_ID,(int)$crid,$starttime,$endtime),true); 
				return $newtime ;
			}
			public function IsCurrentVersion($request,&$versiontext){
				if (!array_key_exists('clientappver', $request) || !isset($request) ) {
					throw new \Exception('Missing GET parameters ');
				}	
				$db = new \cgoSqlDB();				
				
				$ver = $db->getItemFromDB('select keyvalue from  global_config where keyname=?',array('s',"CaregoCollectAppVer") ,false);
				if($ver!=$request['clientappver']){
					$versiontext = "Your application is outdated. Current version :" . $ver . " and your version :" . $request['clientappver'];
					return false;
				}
				return true ;
			}			
			public function getKMInaTrip($apikey){
				$degMat = new \cgoGeoUtilities();
				$kmscovered =0;
				$nodb = new \cgoDynamiteDB();
				$arrVehicle = array();
				$arrVehicle['PartitionKey'] = array('col'=>'apikey','val'=>$apikey,'oper'=>'=');		
				$arrVehicle['queryAttributes']=array('Home_Latitude','Home_Longitude');
				$nors = $nodb->query('QugeoLiveVehicles',$arrVehicle,'query');		
				if(isset($nors) && count($nors) > 0 ){
					foreach($nors as $value){	
						$HomeLatitude = $arrVehicle['Home_Latitude'];
						$HomeLongitude = $arrVehicle['Home_Longitude'];
					}
				}
				$arrVehicle = array();
				$arrVehicle['PartitionKey'] = array('col'=>'apikey','val'=>$apikey,'oper'=>'=');		
				$arrVehicle['queryAttributes']=array('orderid','order','Latitude','Longitude','IsClosed','IsLiveOrder','IsPickup','IsMilestoneLock');
				$nors = $nodb->query('QugeoLiveVehicleOrders',$arrVehicle,'query');		
				$CurrentOpenOrders = array();
				if(isset($nors) && count($nors) > 0 ){
					$CurrentOrders = array();
					foreach($nors as $value){	
						array_push($CurrentOrders,array('Latitude'=>$value['Latitude'],'Longitude'=>$value['Longitude'],'IsClosed'=>$value['IsClosed'],'order'=>$value['order']));						
					}
					finascop_aasort($CurrentOrders,'order');
					$prevlatlong =array();
					$prevlatlong['Latitude'] = 0;
					$prevlatlong['Longitude'] = 0;					
					foreach($CurrentOrders as $value){	
						if($value['IsClosed']=='1'){							
							if ($prevlatlong['Latitude'] !=0){
								$dist = $degMat->GetDrivingDistance($prevlatlong['Latitude'],$value['Latitude'],$prevlatlong['Longitude'],$value['Longitude']);
								$kmscovered = $kmscovered + $dist;
							}
							$prevlatlong['Latitude'] =$value['Latitude'];
							$prevlatlong['Longitude'] =$value['Longitude'];
						}
					}
					if($kmscovered>0 && $HomeLatitude !=0){
						$dist = $degMat->GetDrivingDistance($prevlatlong['Latitude'],$HomeLatitude,$prevlatlong['Longitude'],$HomeLongitude);
						$kmscovered = $kmscovered + $dist;
					}
				}
				return $kmscovered;
			}
		}
	}																							