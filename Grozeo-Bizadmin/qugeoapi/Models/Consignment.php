<?php
	namespace Models;
	{
		class Consignment extends ModelAbstract
		{
			/* public GET_refno($flag,$request){
				$nodb = new \cgoDynamiteDB();	
				
				$frstchr ='';
				$pref = 'AA';
				$cod= '0000';
				$suff = 'A';
				while ($pref<>'ZZ' || $suff<>'Z' || $cod <> '9999' ) {
				if(intval($cod)!= '9999'){
				$cod = str_pad(intval($cod)+1, 4, '0', STR_PAD_LEFT); 
				} 
				else if ($suff!='Z'){
				$cod= '0001';
				$suff = chr(ord($suff)+1);
				}				
				else if($pref!="ZZ"){
				$cod= '0001';
				$suff = 'A';	
				$frstchr = MID($pref,1,1); 
				$scndchr = MID($pref,2,1);
				if(scndchr !='Z')
				$scndchr = chr(ord($scndchr) +1);
				elseif($frstchr != 'Z'){
				$scndchr = 'A';
				$frstchr = chr(ord($frstchr) +1);
				}		
				$pref = $frstchr . $scndchr;	  
				}
				$arrSession = array();
				$arrSession['Data']=array();	
			  	$nosession = $nodb->perform('BookingSession','insert',$arrSession,$response);	
				if (!$nosession){		
				exit("failed...");
				}
				}
			}*/
			public function GET_refno($flag,$request){
				$arrAuth = array();
				$util =  new Utils();
				$refno = $util->getRandomRef();
				if (!$refno!=''){					
					$arrAuth['success']=true;
					$arrAuth['msg'] = 'New Reference number';	
					$arrAuth['Data']['bksession'] =$refno;												
					}else{
					$arrAuth['msg'] = 'New Booking session failed';							
				}	
				return $arrAuth;	
			}
			private function validatebookingsession($apikey,$bksession,&$stage='-1'){
				$nodb = new \cgoDynamiteDB();	
				$arrAPI['PartitionKey'] = array('col'=>'apikey','val'=>$apikey);
				$arrAPI['SortKey'] = array('col'=>'bksession','val'=>$bksession);	
				$arrAPI['getAttributes']=array('isalive','stage');
				$rsno = $nodb->query('BookingSession',$arrAPI,'getItem');	
				
				if(count($rsno) > 0 ){	
					$active = $rsno['isalive'];
					$stage = $rsno['stage'];
					if($active==1) 
					return true;
					else
					return false;
					}else{
					return false; 
				}	 
			}
			private function closeallbookingsession($apikey){
				$nodb = new \cgoDynamiteDB();			
				$arrSession['PartitionKey'] = array('col'=>'apikey','val'=>$apikey,'oper'=>'=');
				$arrSession['IndexName'] = 'apikey-index';
				$arrSession['queryAttributes']=array('isalive','bksession');
				$arrSession['Condition']=array();
				array_push($arrSession['Condition'],array('col'=>'isalive','val'=>1,'oper'=>'=' ));	
				$rsno = $nodb->query('BookingSession',$arrSession,'query');
				if(isset($rsno) && count($rsno) > 0 ){
					foreach ($rsno as $value) {
						$bksession = $value['bksession'];
						$arrSession = array();
						$arrSession['Data']=array();
						$arrSession['PartitionKey']=array('col'=>'apikey','val'=>$apikey);
						$arrSession['SortKey']=array('col'=>'bksession','val'=>$bksession);				
						array_push($arrSession['Data'],array('col'=>'isalive','val'=>0 ));
						$nosession = $nodb->perform('BookingSession','update',$arrSession,$response);
						if(!$nosession) return false;
					}
				}
				return true;				
				
			}
			public function GET_createbookingsession($flag,$request){	
				$nodb = new \cgoDynamiteDB();			
				if ($this->closeallbookingsession($request['apikey'])){	
					$arrSession = array();
					$arrSession['Data']=array();
					$bksession =  sha1(microtime(true).mt_rand(1000,9000));
					$valdate = date("Y-m-d");
					$valdatetime = date("Y-m-d H:i:s");
					array_push($arrSession['Data'],array('col'=>'apikey','val'=>$request['apikey']));
					array_push($arrSession['Data'],array('col'=>'createddatetime','val'=>(string)$valdatetime ));
					array_push($arrSession['Data'],array('col'=>'createddate','val'=>(string)$valdate ));
					array_push($arrSession['Data'],array('col'=>'bksession','val'=>$bksession ));
					array_push($arrSession['Data'],array('col'=>'isalive','val'=>1 ));
					$nosession = $nodb->perform('BookingSession','insert',$arrSession,$response);	
					$arrAuth = array();	
					if ($nosession){					
						$arrAuth['success']=true;
						$arrAuth['msg'] = 'New Booking Session started';	
						$arrAuth['Data']['bksession'] = $bksession;		
						$arrAuth['Data']['MaxPackets'] = array("value"=>CAREGO_MAX_PACKETS,"string"=>'Maximum of  ' . CAREGO_MAX_PACKETS . ' packets can be send at a time');
						$arrAuth['Data']['MaxGoodsWorth']  = array("value"=>CAREGO_MAX_GOODSWORTH,"string"=>'You cannot send goods worth more than ' . CAREGO_MAX_GOODSWORTH . ' rupees');
						$arrAuth['Data']['MaxDimensionFeet']  = array("value"=>CAREGO_MAX_DIMENSION_FEET,"string"=>'The  maximum allowed dimension is ' . CAREGO_MAX_DIMENSION_FEET . ' feet');
						$arrAuth['Data']['MaxDimensionInches']  = array("value"=>CAREGO_MAX_DIMENSION_INCHES,"string"=>'The  maximum allowed dimension is ' . CAREGO_MAX_DIMENSION_INCHES . ' inches');
						$arrAuth['Data']['MaxWeight']  = array("value"=>CAREGO_MAX_WEIGHT_KG,"string"=>'Maximum of  ' . CAREGO_MAX_WEIGHT_KG . ' kilograms can be send at a time');
						$arrAuth['Data']['MaxWeightKg']  = array("value"=>CAREGO_MAX_WEIGHT_KG,"string"=>'Maximum of  ' . CAREGO_MAX_WEIGHT_KG . ' kilograms can be send at a time');
						$arrAuth['Data']['MaxWeightGram']  = array("value"=>CAREGO_MAX_WEIGHT_GRAM,"string"=>'Maximum of  ' . CAREGO_MAX_WEIGHT_GRAM . ' grams can be send at a time');
						}else{
						$arrAuth['msg'] = 'New Booking session failed';							
					}
					}else{
					$arrAuth['msg'] = 'New Booking session failed';		
				}
				return	$arrAuth;			
			}		 
			public function POST_selectedpincodes($flag,$request){
				if (!array_key_exists('pincodes', $request) || !array_key_exists('bksession', $request) || !isset($request) ) {
					throw new \Exception('Missing Post parameters '  );	
				}
				if(!$this->validatebookingsession($request['apikey'],$request['bksession'])){
					throw new \Exception('Invalid Booking session '  );	
				}
				$nodb = new \cgoDynamiteDB();				
				$arrSession = array();
				$arrSession['PartitionKey']=array('col'=>'apikey','val'=>$request['apikey']);			
				$arrSession['SortKey']=array('col'=>'bksession','val'=>$request['bksession']);
				$arrSession['Data']=array();								
				array_push($arrSession['Data'],array('col'=>'selectedpincodes','val'=>(string)$request['pincodes'] ));
				array_push($arrSession['Data'],array('col'=>'stage','val'=>1 ));				
				$nosession = $nodb->perform('BookingSession','update',$arrSession,$response);	
				if ($nosession){					
					$arrAuth['success']=true;
					$arrAuth['msg'] = 'Saved pincodes for booking session';	
					$arrAuth['Data']['pincodes'] = null;													
					}else{
					$arrAuth['msg'] = 'Unable to save pincodes';		
					$arrAuth['Data']['pincodes'] = null;						
				}
				return	$arrAuth;					
			}			
			public function POST_paymentmode($flag,$request){
				if (!array_key_exists('paymentmode', $request) || !isset($request) ) {
					throw new \Exception('Missing Post parameters '  );	
				}
				if(!$this->validatebookingsession($request['apikey'],$request['bksession'])){
					throw new \Exception('Invalid Booking session '  );	
				}
				$nodb = new \cgoDynamiteDB();				
				$arrSession = array();
				$arrSession['PartitionKey']=array('col'=>'apikey','val'=>$request['apikey']);			
				$arrSession['SortKey']=array('col'=>'bksession','val'=>$request['bksession']);
				$arrSession['Data']=array();								
				array_push($arrSession['Data'],array('col'=>'paymentmode','val'=>$request['paymentmode'] ));
				array_push($arrSession['Data'],array('col'=>'stage','val'=>2 ));					
				$nosession = $nodb->perform('BookingSession','update',$arrSession,$response);	
				if ($nosession){					
					$arrAuth['success']=true;
					$arrAuth['msg'] = 'Saved payment mode for booking session';	
					$arrAuth['Data']['paymentmode'] = null;													
					}else{
					$arrAuth['msg'] = 'Unable to save payment mode';		
					$arrAuth['Data']['paymentmode'] = null;						
				}
				return	$arrAuth;					
			}			
			public function POST_address($flag,$request){
				if (!array_key_exists('isdelivery', $request) || !array_key_exists('address', $request) || !isset($request) ) {
					throw new \Exception('Missing Post parameters '  );	
				}
				if(!$this->validatebookingsession($request['apikey'],$request['bksession'])){
					throw new \Exception('Invalid Booking session '  );	
				}				
				$arrAuth = array();	
				
				if (array_key_exists('saveasaddress', $request)){
					$cust =  new Customer('POST_address');
					$nocust = $cust->POST_address($flag,$request);
					if (array_key_exists( 'success' , $nocust)==false || $nocust['success']!==true){
						$arrAuth['msg'] = 'Unable to save address';		
						$arrAuth['Data']['address'] = null;			
						return $arrAuth;
					}					
				}
				$nodb = new \cgoDynamiteDB();				
				$arrSession = array();
				$arrSession['PartitionKey']=array('col'=>'apikey','val'=>$request['apikey']);			
				$arrSession['SortKey']=array('col'=>'bksession','val'=>$request['bksession']);
				$arrSession['Data']=array();				
				$addresstoselect = ($request['isdelivery']=='true'?'delivery_addresses':'pickup_addresses');		
				array_push($arrSession['Data'],array('col'=>$addresstoselect,'val'=>json_decode($request['address'],true)));	
				file_put_contents('php://stderr', "Adress \n");
				file_put_contents('php://stderr', print_r($request['address'], TRUE));
				array_push($arrSession['Data'],array('col'=>'stage','val'=>(int)($request['isdelivery']=='true'?5:4)));		
				$nosession = $nodb->perform('BookingSession','update',$arrSession,$response);	
				if ($nosession){					
					$arrAuth['success']=true;
					$arrAuth['msg'] = 'Saved Address for booking session';	
					$arrAuth['Data']['address'] = null;													
					}else{
					$arrAuth['msg'] = 'Unable to save address';		
					$arrAuth['Data']['address'] = $response;						
				}
				return	$arrAuth;
			}
			public function POST_consignment($flag,$request){
				if (!array_key_exists('consignment', $request) || !isset($request) ) {
					throw new \Exception('Missing Post parameters '  );	
				}
				if(!$this->validatebookingsession($request['apikey'],$request['bksession'])){
					throw new \Exception('Invalid Booking session '  );	
				}	
				
				$cons =  json_decode($request['consignment'],true);
				$arrAuth = array();
				$util =  new Utils();
				$isvalid  = $util->IsValidConsignment($cons,$str);
				if(!$isvalid){
					$arrAuth['msg'] = $str;	
					$arrAuth['Data']['consignment'] = array();	
					return	$arrAuth;	
				}
				$nodb = new \cgoDynamiteDB();				
				$arrSession = array();
				$arrSession['PartitionKey']=array('col'=>'apikey','val'=>$request['apikey']);			
				$arrSession['SortKey']=array('col'=>'bksession','val'=>$request['bksession']);
				$arrSession['Data']=array();		
				array_push($arrSession['Data'],array('col'=>'consignment','val'=>$cons));	
				array_push($arrSession['Data'],array('col'=>'stage','val'=>3 ));				
				$nosession = $nodb->perform('BookingSession','update',$arrSession,$response);	
				if ($nosession){					
					$arrAuth['success']=true;
					$arrAuth['msg'] = 'Saved consignment for booking session';	
					$arrAuth['Data']['consignment'] = null;													
					}else{
					$arrAuth['msg'] = 'Unable to save consignment';		
					$arrAuth['Data']['consignment'] = null;						
				}
				return	$arrAuth;
			}
			public function GET_charges($flag,$request){
				if(!$this->validatebookingsession($request['apikey'],$request['bksession'],$stage)){
					throw new \Exception('Invalid Booking session '  );	
				}				 
				if($stage!=5){
					throw new \Exception('For getting charges, all the details are to be filled.'  );	
				}	 
				$util =  new Utils();		
				$chrgs = new Charges('getcharges');
				$params = $chrgs->getcharges($flag,$request);
				
				$arrCharges =array();		
				$arrCharges['source'] = $params['source'];
				$arrCharges['destination'] = $params['destination'];
				$arrCharges['route'] = $params['gmap']['route'];			 
				$arrCharges['distance']=$params['distance'];
				$arrCharges['chargeweight']=$params['TotalChargWt'];
				$arrCharges['packets']=$params['TotalPkts'];
				$arrCharges['transcharges']=$params['TotalFreightAmt'];
				$arrCharges['delicharges']= $params['delicharges'];
				$arrCharges['taxes']=$params['taxamt'];		
				$arrCharges['total']=$params['netamt']-$params['roundoff'];	
				
				$arrSession =array();
				$arrSession['Data']= array();
				if (is_array($arrCharges)){
					if(floatval($params['distance'])>0) {
						if($this->updatecharges($request,$params)){						
							$arrSession['success']=true;
							$arrSession['msg'] = 'Charges for booking session';	
							$arrSession['Data']['charges'] = $arrCharges;	
							$arrSession['Data']['slots']= $util->getPickupScheduleSlots();					
							$arrSession['Data']['holidays']=$this->getHolidays();	
							$arrSession['Data']['daterange']=$this->getDateRange();		
							$arrSession['Data']['restricteddays']=$this->getRestrictedDays();		
						}
						else{
							$arrSession['msg'] = 'Failed updating Charges for booking session';		
						}
						}else{
						$arrSession['msg'] = 'The pickup location and delivery location are same, please verify your location details';		
					}
					}else{
					$arrSession['msg'] = 'Failed getting Charges for booking session';		
				}			 
				return $arrSession;
			}
			public function POST_createbooking($flag,$request){
				//|| !array_key_exists('slotime', $request) || !array_key_exists('slotdate', $request)
				if (!array_key_exists('paymentdetails', $request)  || !isset($request) ) {
					throw new \Exception('Missing Post parameters '  );	
				}
				if(!$this->validatebookingsession($request['apikey'],$request['bksession'],$stage)){
					throw new \Exception('Invalid Booking session '  );	
				}				
				if(intval($stage) == 7){
					throw new \Exception('The booking is already completed.'  );	
					}elseif(intval($stage)<6){
					throw new \Exception('The booking has not received all the details, please fill all the details.'  );	
					}elseif(intval($stage)!=6){
					throw new \Exception('Invalid sequence of event for booking.'  );	
				}
				$valdate = date("YmdHis");				
				//$request["slotime"]=$valdate = date("YmdHis");
				//$request["slotdate"]=$valdate = date("YmdHis");
				$nodb = new \cgoDynamiteDB();		
				$schedule =  json_decode($request['schedule'],true);	
				
				if((int)$schedule["type"]!=1){
						$arrType = array();								
						$arrType['Data']['refno'] = null;	
						if(!isset($schedule["slot"]) || trim($schedule["slot"])===''){ 	
							$arrType['msg'] = 'Invalid slot time. Please select a valid date and slot';		
							return $arrType;
							}
						if(!isset($schedule["date"]) || trim($schedule["date"])===''){
							$arrType['msg'] = 'Invalid slot date. Please select a valid date and slot';	
							return $arrType;
						}
				}
				//$ctr=685;
				while(true){				
					//$bkrefno = $ctr;	
					$arrSession = array();
					$arrSession['PartitionKey'] = array('col'=>'apikey','val'=>$request['apikey'],'oper'=>'=');
					$arrSession['SortKey'] = array('col'=>'bksession','val'=>$request['bksession'],'oper'=>'=');					
					$arrSession['IndexName'] = 'apikey-bksession-index';
					$arrSession['queryAttributes']=array('bkrefno');					
					$rsno = $nodb->query('BookingQueue',$arrSession,'query');
					if(isset($rsno) && count($rsno) > 0 ){
						$bkrefno = $rsno['bkrefno'];
						break;
					}
					$util =  new Utils();
					$bkrefno =$util->getRandomRef();
					$arrSession = array();					
					$arrSession['Data']=array();	
					$util =  new Utils();
					/*if((int)$schedule["type"]==0){					
						$schedule["slot"] = date('H:i:s');	
						$schedule["date"] = date( 'Y-m-d' ) ;		
					}*/
					array_push($arrSession['Data'],array('col'=>'apikey','val'=>$request['apikey']));
					array_push($arrSession['Data'],array('col'=>'bksession','val'=>$request['bksession']));
					array_push($arrSession['Data'],array('col'=>'bkrefno','val'=>(string)$bkrefno));		
					array_push($arrSession['Data'],array('col'=>'IsProcessed','val'=>0));					
					array_push($arrSession['Data'],array('col'=>'c_id','val'=>(int)$_SESSION["c_id"]));
					array_push($arrSession['Data'],array('col'=>'IsBranchBooking','val'=>(bool)$_SESSION["IsBranchBooking"]));
					array_push($arrSession['Data'],array('col'=>'scheduletype','val'=>(int)$schedule["type"]));
					array_push($arrSession['Data'],array('col'=>'slotime','val'=>$schedule["slot"]));	
					array_push($arrSession['Data'],array('col'=>'slotdate','val'=>$schedule["date"]));	
					array_push($arrSession['Data'],array('col'=>'createddatetime','val'=>  date("Y-m-d H:i:s")));	
					$arrSession['ConditionExpression']='attribute_not_exists(bkrefno)';	
					$response = null;
					$newbooking = $nodb->perform('BookingQueue','insert',$arrSession,$response);	
					
					if($newbooking){
						break;
						}else{
						file_put_contents('php://stderr', print_r($newbooking,TRUE));
						file_put_contents('php://stderr', print_r($response,TRUE));
						//print_r($response);
					}
					$ctr++;	
				}
				
				$arrSession = array();				
				$arrSession['PartitionKey']=array('col'=>'apikey','val'=>$request['apikey']);			
				$arrSession['SortKey']=array('col'=>'bksession','val'=>$request['bksession']);
				$arrSession['Data']=array();		
				
				array_push($arrSession['Data'],array('col'=>'bkrefno','val'=>$bkrefno));	
				array_push($arrSession['Data'],array('col'=>'paymentdetails','val'=>$request['paymentdetails']));	
				array_push($arrSession['Data'],array('col'=>'stage','val'=>7 ));		
				array_push($arrSession['Data'],array('col'=>'remote_address','val'=>(string)$_SERVER['REMOTE_ADDR'] ));
				array_push($arrSession['Data'],array('col'=>'isalive','val'=>0 ));
				$response = array();			
				
				$nosession = $nodb->perform('BookingSession','update',$arrSession,$response);	
				if ($nosession){					
					$arrAuth['success']=true;
					$arrAuth['msg'] = 'Created New booking';	
					$arrAuth['Data']['refno'] = $bkrefno;													
					}else{
					$arrAuth['msg'] = 'Unable to create New booking, try later';		
					$arrAuth['Data']['refno'] = null;						
				}
				
				$util->deleteRefNoVault($bkrefno);
				
				return	$arrAuth;
			}	
			private function updatecharges($request,$params){
				$nodb = new \cgoDynamiteDB();				
				$arrSession = array();
				$arrSession['PartitionKey']=array('col'=>'apikey','val'=>$request['apikey']);			
				$arrSession['SortKey']=array('col'=>'bksession','val'=>$request['bksession']);
				$arrSession['Data']=array();								
				array_push($arrSession['Data'],array('col'=>'calculatedcharges','val'=>$params));
				array_push($arrSession['Data'],array('col'=>'stage','val'=>6 ));					
				$nosession = $nodb->perform('BookingSession','update',$arrSession,$response);	
				if ($nosession){					
					return true;
					}else{
					return false;
				}				
			}
			private function getDateRange(){
				$util =  new Utils();
				$pickupdays = $util->getPickupdays();
				return  array(strtotime($pickupdays[0]),strtotime($pickupdays[1]));
			}		
			private function getHolidays(){			
				$util =  new Utils();
				$pickupdays = $util->getPickupdays();
				$holidays =  $util->getHolidays($pickupdays[0],$pickupdays[1]);
				return ($holidays==false?array():$holidays);
			}
			private function getRestrictedDays(){			
				$util =  new Utils();
				$pickupdays = $util->getPickupdays();
				if($pickupdays[0] <>  date( 'Y-m-d' )){
					return array();
					}else{
					$now = date("His");
					$slots = $util->getPickupScheduleSlots($now);
					$restrictdays =  array(array("date"=>strtotime($pickupdays[0]),"slots"=>$slots));
					return $restrictdays;
				}
				
			}
			private function getCurrentLocationOfVehicle($qugeoid){
				$nodb = new \cgoDynamiteDB();	
				$arrOrder['PartitionKey'] = array('col'=>'orderid','val'=>$request['orderid'],'oper'=>'=');		
				$arrOrder['getAttributes']=array('Acceptedapikey');
				$nors = $nodb->query('QugeoOrderDetails',$arrOrder,'getItem');	
				if($nors!=false ) {
					$arrVehicle['PartitionKey'] = array('col'=>'apikey','val'=>$nors['Acceptedapikey'],'oper'=>'=');		
					$arrVehicle['getAttributes']=array('Latitude','Longitude');
					$nodb = new \cgoDynamiteDB();	
					$rsno = $nodb->query('QugeoLiveVehicles',$arrVehicle,'getItem');
					if(isset($rsno) && count($rsno) > 0 ){										
						return $rsno["Latitude"] . "," . $rsno["Longitude"];
						} else {
						return "0.00,0.00";
					}		
					}else{
					return "0.00,0.00";				
				}		
				
			}
			public function GET_tracking($flag,$request){
				$db = new \cgoSqlDB();	
				$arrTrack = array();	
				$arrTrack['msg']="Booking Details";
				$arrTrack['Data']['track'] = array();
				
				if ($request['bkno']==''){
					$start = (intval($request['start'])==0?0:$request['start']);
					$limit = (intval($request['limit'])==0?10:$request['limit']);
					$rd = $db->getMulipleData('select  bk_no as BkNo,bk_date as Date,bk_Netamt-bk_roundoff as Netamt from  inward_booking where bk_cancelled =0 and bk_cr_c_id=? and trs_id < 7 order by bk_date desc, bk_id desc limit ?,?',array('iii', (int)$_SESSION["c_id"],$start,$limit) ,true);
					if($rd==false){						
						$arrTrack['msg']="No bookings found";
						return $arrTrack;
					}
					$arrTrack['success']=true;
					$arrTrack['Data']['track'] = $rd;
					}else{
					$rd = $db->getFromDB('select  bk_PickupQugeoId,bk_DeliveryQugeoId,trs_ID,dls_id,(select concat_ws(",",br_Lati,br_Lng) from  branch where br_id = bk_s_br_id) as SrcbGeoLoc,(select concat_ws(",",br_Lati,br_Lng) from  branch where br_id = bk_d_br_id) as DstGeoLoc  , CONCAT_WS(",",bk_ConsorLati,bk_ConsorLong) as ConsrGeoLocation, CONCAT_WS(",",bk_ConseeLati,bk_ConseeLong) as ConeeGeoLocation  from  inward_booking where bk_cancelled =0 and bk_no=? limit 1',array('s', (string)$request['bkno']) ,true);
					if($rd==false){					
						$arrTrack['msg']="Tracking details not found, please search after sometime";
						return $arrTrack;
					}
					switch($rd['dls_id']) {
						case 0:
						case 1:
						case 39:
						//Source Branch
						$geo = explode(",",$rd['SrcbGeoLoc']);							
						break;	
						case 9:
						$geo = explode(",",$this->getCurrentLocationOfVehicle($rd['bk_DeliveryQugeoId']));
						break;
						case 15:
						case 38:
						//Delivered 							
						$geo = explode(",",$rd['ConeeGeoLocation']);
						break;
						case 2:
						case 3:
						case 4:
						case 5:
						case 6:
						case 7:
						case 8:
						case 10:
						case 11:
						case 12:
						case 13:
						case 14:
						case 31:
						case 32:
						case 33:
						case 34:
						//Receive at Branch  or Failed
						$geo = explode(",",$rd['DstGeoLoc']);
						break;
						case 22:
						case 23:
						case 24:							
						case 25:	
						case 26:								
						case 27:								
						case 30:								
						case 35:								
						case 36:								
						case 37:
						//Consignor Location
						$geo = explode(",",$rd['ConsrGeoLocation']);
						break;				
						case 28:
						case 29:
						//PickedUp, but still in transit
						$geo = explode(",",$this->getCurrentLocationOfVehicle($rd['bk_PickupQugeoId']));
						break;								
						default:
						$geo =   explode(",","0.00,0.00");	
						break;
					}			
					$arrTrack['success']=true;
					$arrTrack['Data']['track'] = array("Latitude"=>$geo[0],"Longitude"=>$geo[1]);
				}
				return $arrTrack;
			}
			public function GET_bookinghistory($flag,$request){
				$db = new \cgoSqlDB();	
				$arrTrack = array();	
				$arrTrack['msg']="Booking Details";
				$arrTrack['Data']['history'] = array();
				
				if ($request['bkno']==''){
					$start = (intval($request['start'])==0?0:$request['start']);
					$limit = (intval($request['limit'])==0?10:$request['limit']);
					$rd = $db->getMulipleData('select  bk_no as BkNo,bk_date as Date,bk_Netamt-bk_roundoff as Netamt,(select dls_DelStatus from  qugeo_deliverystatus where qugeo_deliverystatus.dls_ID =  inward_booking.dls_id ) as Status from  inward_booking where bk_cancelled =0 and bk_cr_c_id=?  order by bk_date desc, bk_id desc limit ?,?',array('iii', (int)$_SESSION["c_id"],$start,$limit) ,true);
					if($rd==false){						
						$arrTrack['msg']="No bookings found";
						return $arrTrack;
					}
					$arrTrack['success']=true;
					$arrTrack['Data']['history'] = $rd;
					}else{
					$rd = $db->getFromDB('select  bk_no as BkNo,bk_date as Date ,bk_Netamt-bk_roundoff as Netamt, bk_TotalDistKM as Distance, concat(bk_Consignor,"|~|",bk_ConsrAddress) as ConsignorAddress, concat(bk_Consignee,"|~|",bk_ConsgAddress) as ConsigneeAddress,bk_PktCount as Packets,bk_TotInvAmt as GoodsWorth,(bk_DrCollectionChrg+bk_DoorDeliveryChrg) as AddChargs,if(bk_STaxType=0,0,1) as taxable,bk_tax as taxrate,bk_id,bk_brk_br_id,(select concat_ws(",",br_Lati,br_Lng) from  branch where br_id = bk_s_br_id) as SrcbGeoLoc,(select concat_ws(",",br_Lati,br_Lng) from  branch where br_id = bk_d_br_id) as DstGeoLoc,bk_s_br_id,bk_d_br_id,bk_ConsorLati,bk_ConsorLong,bk_ConseeLati,bk_ConseeLong from  inward_booking where bk_cancelled =0 and bk_no=? limit 1',array('s', (string)$request['bkno']) ,true);
					if($rd==false){					
						$arrTrack['msg']="Booking details not found, please try after sometime";
						return $arrTrack;
					}
					$rd['gmap']['route']= array();
					array_push($rd['gmap']['route'],array('latitude'=>$rd['bk_ConsorLati'],'longitude'=>$rd['bk_ConsorLong']));
					if ($rd['bk_s_br_id'] != $rd['bk_d_br_id']){
						$geo = explode(",",$rd['SrcbGeoLoc']);
						array_push($rd['gmap']['route'],array('latitude'=>$geo[0],'longitude'=>$geo[1]));
						$geo = explode(",",$rd['DstGeoLoc']);
						array_push($rd['gmap']['route'],array('latitude'=>$geo[0],'longitude'=>$geo[1]));							
					}
					array_push($rd['gmap']['route'],array('latitude'=>$rd['bk_ConseeLati'],'longitude'=>$rd['bk_ConseeLong']));
					
					$consignment = $db->getMulipleData('select co_Wt as weight,co_Length as size_length,co_Breadth as size_breadth,co_Height as size_height,co_Packets as `count`,(select ct_Name from  contenttype where ct_ID = inward_consignment.ct_ID) as ContentType,(select pk_Name from  packingtype where pk_ID = inward_consignment.pk_ID) as PackingType, "feet" as size_unit, "kg" as weight_unit from  inward_consignment where bk_id = ' . $rd['bk_id'] . ' and d_br= ' . $rd['bk_brk_br_id'] . '  ' ,array(),true);
					
					//Tax
					if ($rd['taxable']=='0'){						
						$rd['TaxAmt'] = round((($rd['Netamt'] +$rd['AddChargs'])*$rd['taxrate'])/100,2);
						}else{
						$rd['TaxAmt'] = 0;
					}
					$rd['Consignment'] = $consignment;					
					unset($array['taxable']);
					unset($array['taxrate']);
					unset($array['bk_id']);
					unset($array['bk_brk_br_id']);
					$arrTrack['success']=true;
					$arrTrack['Data']['history'] = $rd;
				}
				return $arrTrack;
			}
		}		 
	}				