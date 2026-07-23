<?php
	namespace Models;
	{
		class QugeoOrderPoller{
			public function __construct() {}
			private function WasQueued($orderid,$apikey,$isPickup){		
				$nodb = new \cgoDynamiteDB();
				$arrVehicle=array();
				$arrVehicle['PartitionKey'] = array('col'=>'orderid','val'=>$orderid,'oper'=>'=');
				$arrVehicle['SortKey']=array('col'=>'apikey','val'=>$apikey,'oper'=>'=');				
				$arrVehicle['IndexName'] = 'orderid-apikey-index';
				$arrVehicle['queryAttributes']=array('orderid');
				$arrVehicle['Condition']=array();
				array_push($arrVehicle['Condition'],array('col'=>'ispickup','val'=>(int)$isPickup,'oper'=>'='));	
				$nodb = new \cgoDynamiteDB();	
				$rsno = $nodb->query('QugeoOrderPollingDetails',$arrVehicle,'query');	
				$vehicledetails =array();	
				if(isset($rsno) && count($rsno) > 0 ){
					return count($rsno);
					} else {
					return 0;
				}
			}
			private function getBestQugeoCandidate($vehicledetails,$orderid,$isPickup,$ignorepreviouspushes){
				//$dist = array();
				$currentload = array();
				$RatePerKm = array();
				$Capacity = array();
				foreach ($vehicledetails as $key => $row)
				{
					if(!$ignorepreviouspushes){
						if($this->WasQueued($orderid,$row['apikey'],$isPickup)>0){
							echo "Yes this " . $orderid . " was queued " . $row['apikey'] . "\n" ;
							unset($vehicledetails[$key]);
							}elseif($this->HasLivePoll($row['apikey'])){
							echo " THis guy has live poll " . $row['apikey'] . "\n" ;
							unset($vehicledetails[$key]);
							}else{
							//$dist[$key] = $row['distance'];		
							$currentload[$key] = $row['CurrentLoadedWeight'];		
							$RatePerKm[$key] = $row['RatePerKm'];	
							$Capacity[$key] = $row['capacity'];	
						}
					}			
				}
				if(!empty($vehicledetails)){
					//array_multisort($dist, SORT_ASC, $vehicledetails);
					finascop_aasort($vehicledetails,'distance');
					return $vehicledetails[0];
					}else{
					return false;
				}
				
				//More than 20 open orders?
				
				//Single Load	
				
				//Is Engaged and this order is much(previously configured) far from current order
				
				//Tonnage - Current Weight
				
				//Coverage - Current Volume
				
				//Rate * Distance
				
				//Proximity
				
				//Order processed today
				
				//Driver rating
				
			}
			private function getQugeoCandidates($orderLat,$orderLong,$driversofbranch=0){
				$nodb = new \cgoDynamiteDB();
				$degMat = new \cgoGeoUtilities();
			
				$arrDegrees = $degMat->getDegreeMatrix($orderLong,$orderLat,QC_VEHICLE_NEAR_PICKUP_CIRCLE_DIST);		
				$arrVehicle=array();
				$arrVehicle['PartitionKey'] = array('col'=>'Is_Live','val'=>1,'oper'=>'=');
				$arrVehicle['SortKey']=array('col'=>'Latitude','val1'=>(float)$arrDegrees['lat1'],'val2'=>(float)$arrDegrees['lat2'],'SortKeyBetween'=>true);				
				//$arrVehicle['SortKey']=array('col'=>'Latitude','val'=>$arrDegrees['lat1'],'oper'=>'=');				
				$arrVehicle['IndexName'] = 'Is_Live-Latitude-index';
				$arrVehicle['queryAttributes']=array('apikey','v_id','v_no','Latitude','Longitude','v_capacity','CurrentLoadedWeight','RatePerKm','AWS_SNS_ARN','DeliveryRange','IsEngaged','MarkedNextBkId','MarkedNextBrId','FCM_ID','DriverPhone');
				$arrVehicle['Condition']=array();
				array_push($arrVehicle['Condition'],array('col'=>'Longitude','val1'=>(float)$arrDegrees['lon1'],'val2'=>(float)$arrDegrees['lon2'],'ConditionBetween'=>true));	
				if(QUGEO_SINGLE_JOB_MODE==1){
					array_push($arrVehicle['Condition'],array('col'=>'IsEngaged','val'=>0,'oper'=>'=' ));	
				}
				$nodb = new \cgoDynamiteDB();	
				//print_r($arrVehicle);
				$rsno = $nodb->query('QugeoLiveVehicles',$arrVehicle,'query');	
				
				$vehicledetails =array();	
				if(isset($rsno) && count($rsno) > 0 ){				
					foreach($rsno as $value){					
						$dist = $degMat->GetDrivingDistance($orderLat,$value['Latitude'],$orderLong,$value['Longitude']);
						array_push($vehicledetails,array('apikey'=>$value['apikey'],'v_No'=>$value['v_no'],'distance'=>$dist,'capacity'=>$value['v_capacity'],'CurrentLoadedWeight'=>$value['CurrentLoadedWeight'],'RatePerKm'=>$value['RatePerKm'],'AWS_SNS_ARN'=>$value['AWS_SNS_ARN'],'DeliveryRange'=>$value['DeliveryRange'],'FCM_ID'=>$value['FCM_ID'],'DriverPhone'=>$value['DriverPhone']));
						echo  "Distance " . $dist ."\n";
					}			
					return $vehicledetails;
					} else {
					return false;
				}			
			}
			public function CreateAPoll($orderid,$isPickup,$order,$specificcandidate=''){	
				
				if($specificcandidate==''){		
					
					$availablevehicles = $this->getQugeoCandidates($order['Lat'],$order['Lng']);
					
					if(empty($availablevehicles)){
						echo "No candidates " . "\n";
						return false;
					}

					$candidate = $this->getBestQugeoCandidate($availablevehicles,$orderid,$isPickup,false);					

					}else{		
					$arrVehicle['PartitionKey'] = array('col'=>'apikey','val'=>$specificcandidate,'oper'=>'=');		
					$arrVehicle['getAttributes']=array('apikey','AWS_SNS_ARN','DeliveryRange','FCM_ID','DriverPhone');
					$nodb = new \cgoDynamiteDB();	
					$rsno = $nodb->query('QugeoLiveVehicles',$arrVehicle,'getItem');
					if(isset($rsno) && count($rsno) > 0 ){				
						$candidate= array('AWS_SNS_ARN'=>$rsno['AWS_SNS_ARN'],'apikey'=>$rsno['apikey'],'DeliveryRange'=>$rsno['DeliveryRange'],'FCM_ID'=>$rsno['FCM_ID'],'DriverPhone'=>$rsno['DriverPhone']);
						} else {
						$candidate= false;
					}				
				}
				
				if ($candidate!==false){
					$pollid =  sha1(microtime(true).mt_rand(10000,90000));
					$valdate = date("Ymd");
					$valdatetime = date("YmdHis");	
					//Use AWS SNS to push message
					
					$pushedmessage = $this->PushPollingDetails($candidate['apikey'],$pollid,'NEW',$orderid,$isPickup,$order,$candidate['AWS_SNS_ARN'],$message,($isPickup===true?(intval($candidate['DeliveryRange'])>=intval($order['TotalDistKM'])?true:false):false),$candidate['FCM_ID'],$candidate['DriverPhone']);
					
					if ($pushedmessage){
						//Save message to table
						$savedmessage = $this->SavePollingDetails($pollid,$orderid,$candidate['apikey'],$isPickup,$valdate,$valdatetime,$message,($isPickup?(intval($candidate['DeliveryRange'])>=intval($order['TotalDistKM'])?true:false):false));
						return $savedmessage;
					}
					}else{
					//No candidate;
					return false;
				}		
				
			}
			private function PushPollingDetails($apikey,$pollid,$msgtype,$orderid,$isPickup,$order,$arn,&$message,$withinrange,$fcmid,$mobno){
				//$sns = new \cgoAWSSNS();
				$fcmmsg = new \firebasemessage();
				$message=array();		
				$disttobr = $order['TotalDistKM'] . ' KM';
				//file_put_contents('php://stderr',  'InterBranch =>' . $order['InterBranch'] . ' withinrange =>' . ( $withinrange ? 'true' : 'false'));
				$geocoords = array(
				"pickup"=> array( "latitude"=>$order['pickupLat'], "longitude"=>$order['pickupLng'], "location"=>$order['pickuplocation']),					
				"delivery"=> array( "latitude"=>$order['deliveryLat'], "longitude"=>$order['deliveryLng'],"location"=>$order['deliverylocation']));

				$message['data'] = array( "yourapikey"=>$apikey,
				"msgid"=> $pollid,
				"msgtype"=> $msgtype,
				"orderid"=>$orderid,
				"ispickuporder"=>$isPickup,		
				"details" =>json_encode($geocoords)				
				);
				//public function sendmessage($ttl, $labeltext, $bodytext, $title, $data, $fcmid){
				$fcmmsg->sendmessage(AWS_SNS_ORDER_TTL,$mobno, "New Order Received", "Drive",$message['data'], $fcmid);
				return true;	
				/*if ($sns->getEndPointDetails($arn,'Enabled')=='true'){	 
					$msgid = $sns->publishToEndPoint($arn,json_encode($message),AWS_SNS_ORDER_TTL);
					return true;
					}else{
					return false;
				}*/
			}
			private function SavePollingDetails($pollid,$orderid,$apikey,$ispickup,$valdate,$valdatetime,$message,$withinrange){
				$nodb = new \cgoDynamiteDB();
				$arrOrder =array();
				$arrOrder['Data']=array();
				$valdate = date("Ymd");
				$valdatetime = date("YmdHis");			
				array_push($arrOrder['Data'],array('col'=>'pollingid','val'=>$pollid));	
				array_push($arrOrder['Data'],array('col'=>'apikey','val'=>$apikey));	
				array_push($arrOrder['Data'],array('col'=>'orderid','val'=>$orderid));			
				array_push($arrOrder['Data'],array('col'=>'createddatetime','val'=>(int)$valdatetime ));
				array_push($arrOrder['Data'],array('col'=>'createddate','val'=>(int)$valdate ));
				array_push($arrOrder['Data'],array('col'=>'currentstatus','val'=>'POLLED' ));
				array_push($arrOrder['Data'],array('col'=>'ispickup','val'=>(int)$ispickup));		
				array_push($arrOrder['Data'],array('col'=>'isclosed','val'=>0));
				array_push($arrOrder['Data'],array('col'=>'pollingdetails','val'=>$message));
				array_push($arrOrder['Data'],array('col'=>'withinrange','val'=>(bool)$withinrange));
				$NewOrder = $nodb->perform('QugeoOrderPollingDetails','insert',$arrOrder,$response);
				if($NewOrder!==false){								
					return true;
					} else {
					return false;
				}
			}
			public function PollResponse($pollid,$pollresponse,$delivertobranch,&$acceptedorder){
				
				$nodb = new \cgoDynamiteDB();
				$arrUpdate=array();
				$arrUpdate['PartitionKey']=array('col'=>'pollingid','val'=>$pollid);
				$arrUpdate['Data']=array();
				array_push($arrUpdate['Data'],array('col'=>'isclosed','val'=>1));
				array_push($arrUpdate['Data'],array('col'=>'closedat','val'=>(string)date("YmdHis") ));
				if($pollresponse==1){				
					array_push($arrUpdate['Data'],array('col'=>'currentstatus','val'=>'ACCEPTED' ));
					array_push($arrUpdate['Data'],array('col'=>'acceptedfor','val'=>(string)($delivertobranch==true?'BRANCH':'DIRECT')));	
					$acceptedorder =true;			
					}else{
					$acceptedorder =false;	
					array_push($arrUpdate['Data'],array('col'=>'currentstatus','val'=>(string)($pollresponse==2?'REJECTED':'NORESPONSE')));
				}				
				$nors = $nodb->perform('QugeoOrderPollingDetails','update',$arrUpdate,$response);	
				
				if(count($response)>0){			
					return true;
					}else{
					return false;
				}
			}
			public function IsPollClosed($pollid,&$apikey){
				
				$nodb = new \cgoDynamiteDB();
				$arrUpdate=array();
				$arrUpdate['PartitionKey']=array('col'=>'pollingid','val'=>$pollid,'oper'=>'=');
				$arrUpdate['queryAttributes']=array('isclosed','apikey');		
				$rsno = $nodb->query('QugeoOrderPollingDetails',$arrUpdate,'query');	
				
				if(isset($rsno) && count($rsno) > 0 ){
					$apikey = $rsno[0]['apikey'];
					if($rsno[0]['isclosed']==0){
						return false;
						}else{
						return true;
					}
					} else {					
					return true;
				}	
				
			}
			public function HasLivePoll($apikey){
				
				$nodb = new \cgoDynamiteDB();
				$arrUpdate=array();
				$arrUpdate['PartitionKey']=array('col'=>'apikey','val'=>$apikey,'oper'=>'=');
				$arrUpdate['SortKey']=array('col'=>'isclosed','val'=>0,'oper'=>'=');			
				$arrUpdate['IndexName'] = 'apikey-isclosed-index';
				$arrUpdate['queryAttributes']=array('isclosed');		
				$rsno = $nodb->query('QugeoOrderPollingDetails',$arrUpdate,'query');			
				
				if(isset($rsno) && count($rsno) > 0 ){					
					return true;
					} else {
					return false;
				}	
				
			}
			public function FindandMarkProspectiveCandidates($orderid,$isPickup,$order,$specificcandidate=''){
				
				if($isPickup){
					$availablevehicles = $this->getSecondaryQugeoCandidates($order['pickupLat'],$order['pickupLng']);
					}else{
					$availablevehicles = $this->getSecondaryQugeoCandidates($order['deliveryLat'],$order['deliveryLng']);			
				}
				if(empty($availablevehicles)){
					return false;
				}
				$candidate = $this->getBestSecondaryQugeoCandidate($availablevehicles,$orderid,$isPickup,true);		
				if ($candidate!==false){
					$arrUpdate=array();
					$arrUpdate['PartitionKey']=array('col'=>'apikey','val'=>$candidate['apikey']);
					$arrUpdate['Data']=array();
					array_push($arrUpdate['Data'],array('col'=>'MarkedNextBkId','val'=>(int)$order['bk_id']));
					array_push($arrUpdate['Data'],array('col'=>'MarkedNextBrId','val'=>(int)$order['bk_brk_br_id']));			
					$nors = $nodb->perform('QugeoLiveVehicles','update',$arrUpdate,$response);
					return true;
					}else{
					//No candidate;
					return false;
				}
				
			}
			private function getSecondaryQugeoCandidates($orderLat,$orderLong){
				$nodb = new \cgoDynamiteDB();
				$degMat = new \cgoGeoUtilities();
				$arrDegrees = $degMat->getDegreeMatrix($orderLong,$orderLat,QC_VEHICLE_NEAR_PICKUP_CIRCLE_DIST);		
				$arrVehicle=array();
				$arrVehicle['PartitionKey'] = array('col'=>'Is_Live','val'=>1,'oper'=>'=');
				$arrVehicle['SortKey']=array('col'=>'OnJobCompletionLatitude','val1'=>(float)$arrDegrees['lat1'],'val2'=>(float)$arrDegrees['lat2'],'SortKeyBetween'=>true);				
				//$arrVehicle['SortKey']=array('col'=>'Latitude','val'=>$arrDegrees['lat1'],'oper'=>'=');				
				$arrVehicle['IndexName'] = 'Is_Live-OnJobCompletionLatitude-index';
				$arrVehicle['queryAttributes']=array('apikey','v_id','v_no','OnJobCompletionLatitude','OnJobCompletionLongitude','v_capacity','CurrentLoadedWeight','RatePerKm','AWS_SNS_ARN','DeliveryRange','IsEngaged','MarkedNextBkId','MarkedNextBrId');
				$arrVehicle['Condition']=array();
				array_push($arrVehicle['Condition'],array('col'=>'OnJobCompletionLongitude','val1'=>(float)$arrDegrees['lon1'],'val2'=>(float)$arrDegrees['lon2'],'ConditionBetween'=>true));	
				array_push($arrVehicle['Condition'],array('col'=>'MarkedNextBkId','val'=>0,'oper'=>'=' ));	
				array_push($arrVehicle['Condition'],array('col'=>'IsEngaged','val'=>0,'oper'=>'=' ));
				$nodb = new \cgoDynamiteDB();	
				$rsno = $nodb->query('QugeoLiveVehicles',$arrVehicle,'query');	
				$vehicledetails =array();	
				if(isset($rsno) && count($rsno) > 0 ){				
					foreach($rsno as $value){					
						$dist = $degMat->GetDrivingDistance($orderLat,$value['OnJobCompletionLatitude'],$orderLong,$value['OnJobCompletionLongitude']);
						array_push($vehicledetails,array('apikey'=>$value['apikey'],'v_No'=>$value['v_no'],'distance'=>$dist,'capacity'=>$value['v_capacity'],'CurrentLoadedWeight'=>$value['CurrentLoadedWeight'],'RatePerKm'=>$value['RatePerKm'],'AWS_SNS_ARN'=>$value['AWS_SNS_ARN'],'DeliveryRange'=>$value['DeliveryRange']));
					}			
					return $vehicledetails;
					} else {
					return false;
				}			
			}
			private function getBestSecondaryQugeoCandidate($vehicledetails,$orderid,$isPickup,$ignorepreviouspushes){
				//$dist = array();
				$currentload = array();
				$RatePerKm = array();
				$Capacity = array();
				foreach ($vehicledetails as $key => $row)
				{
					if(!$ignorepreviouspushes){
						if($this->WasQueued($orderid,$row['apikey'],$isPickup)>0){
							unset($vehicledetails[$key]);
							}else{
							//$dist[$key] = $row['distance'];		
							$currentload[$key] = $row['CurrentLoadedWeight'];		
							$RatePerKm[$key] = $row['RatePerKm'];	
							$Capacity[$key] = $row['capacity'];	
						}
					}			
				}
				if(!empty($vehicledetails)){
					//array_multisort($dist, SORT_ASC, $vehicledetails);
					finascop_aasort($vehicledetails,'distance');
					return $vehicledetails[0];
					}else{
					return false;
				}
				
				//More than 20 open orders?
				
				//Single Load	
				
				//Is Engaged and this order is much(previously configured) far from current order
				
				//Tonnage - Current Weight
				
				//Coverage - Current Volume
				
				//Rate * Distance
				
				//Proximity
				
				//Order processed today
				
				//Driver rating
				
			}
		}
	}			