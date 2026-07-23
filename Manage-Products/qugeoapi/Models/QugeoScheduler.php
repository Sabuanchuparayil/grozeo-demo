<?php
	namespace Models;
{
	class QugeoScheduler{
		var $db;
		public function __construct() {
			$this->db = new \sqlDb(DSN);
		}
		private function setDeffredPickupSchedule($orderid,$ispickup,$order){	
				
			$now = date('Y-m-d H:i:s');

			if ((strtotime($now) - strtotime($order['FirstScheduleRun'])) > (QUGEO_DEFER_MANUAL_SCHEDULE_MAX)){
				//IF the Deffering has passed the maximum defering time
				$this->handoverForManualScheduling($orderid,$ispickup,$order);		
				return;
			}else{
				$scheduletime = $this->db->getItemFromDB("select quor_ScheduleOpeningTime from qugeo_order where quor_id = " . $order['quor_id'],true);			
				$now = date('Y-m-d H:i:s');
				if((strtotime($scheduletime) - strtotime($now)) > 0)
						//If the Schedule time is pushed to a new time on processing other booking
						return;
				}
				if(QUGEO_SECONDARY_CANDIDATE_ENABLED == 1){
					$OrderPoller = new QugeoOrderPoller();
					$marked = $OrderPoller->FindandMarkProspectiveCandidates($orderid,$ispickup,$order);				
				}
				$util =  new Utils();
				$util->setPickupTime($order,true,$this->db);
			}
		private function getBookingOrderAndPollIt($booking,$schedule,$specificvehicle,$Manualpush){
			
			$OrderInitiate = new QugeoOrderHandler();		
			$this->db->query('begin');
			foreach ($booking as $key=>$values) {
			
					if ($Manualpush==false){
						if($values['quor_PickupToBeManual']==1){
							echo "Manual push cause multiple booking from same customer ". $values['quor_id'] . "\n";							
							continue;
						}
					}					
					$order=null;
					
					$consignment = $this->db->getMulipleData('select quod_id as detid,quor_RefNo as RefNo,quor_IsBarcode as Barcode  from qugeo_orderdetails where quor_id = ' . $values['quor_id']  . '  ' ,true);					
					//$consume_id,$consume_RefNo,$Date,$Name,$Address,$Location,$Phone,$Lat,$Lng,$OTP,$Stage1Distance,$Stage2Distance,$DistanceKM,$status,$pktcount,$consignmentdetails,$Manualpush
										
					$orderid = $OrderInitiate->CreateOrder(true,$values['quor_id'],  $values['quor_RefNo'],  $values['quor_Date'],  $values['quor_PickupName'],  $values['quor_PickupAddress'],  $values['quor_PickupLocation'],  $values['quor_PickupPhone'],  $values['pickupLat'],  $values['pickupLng'],  $values['quor_PickupSMS'],  $values['quor_PickupStage1Distance'],  $values['quor_PickupStage2Distance'],$values['quor_DeliveryName'],  $values['quor_DeliveryAddress'],  $values['quor_DeliveryLocation'],  $values['quor_DeliveryPhone'],  $values['deliveryLat'],  $values['deliveryLng'],  $values['quor_DeliverySMS'],  $values['quor_DeliveryStage1Distance'],  $values['quor_DeliveryStage2Distance'],$values['quor_DistanceinKM'],$values['quor_Status'],0,$consignment,$Manualpush,$values['quor_FirstScheduleRun'],$order,$values['quor_PickupPincode'],$values['quor_DeliveryPincode'],$values['quor_Pickupbr_id'] , $values['quor_Deliverybr_id'], $values['quor_AmountCollectible']);
					if($orderid=='' || empty($orderid)){
						echo "Error on creating order " . $orderid . " for booking " . $values['bk_no'];
						exit;
					}		
					//echo $orderid;
					if($schedule){
						$OrderPoller = new QugeoOrderPoller();	
						$createdpoll = $OrderPoller->CreateAPoll($orderid,true,$order,$specificvehicle);
						if(substr($values['quor_LastScheduleRun'],0,4)=='0000'){
							$now = date('Y-m-d H:i:s');
						}else{
							$now = $values['quor_LastScheduleRun'];
						}
						$order['LastScheduleRun']=$now ;
						if($createdpoll){
							$this->db->query("UPDATE qugeo_order set quor_Type=1,quor_LastScheduleRun ='" . $now . "', quor_QugeoPickupDDBOrderId = '" . $orderid . "',quor_Status=" . ORDER_PICKUP_POLLED_DLS_ID . " where quor_id = " . $values['quor_id']  );				
						}else{
							$this->db->query("UPDATE qugeo_order  set quor_LastScheduleRun ='" . $now . "' where quor_id = " . $values['quor_id'] );
							
							$this->setDeffredPickupSchedule($orderid,true,$order);					
						}
					}
				}
			$this->db->query('commit');
			return $orderid;
		}
		private function getDeliveryOrderAndPollIt($booking,$schedule,$specificvehicle,$Manualpush,$WasDirectDeliveryPickUp=true,$deliveringbranch){
			
			$OrderInitiate = new QugeoOrderHandler();		
			$this->db->query('begin');
			foreach ($booking as $key=>$values) {
				
				$consignment = $this->db->getMulipleData('select quod_id as detid,quor_RefNo as RefNo,quor_IsBarcode as Barcode  from qugeo_orderdetails where quor_id = ' . $values['quor_id']  . '  ' ,true);					
				$order=null;
				$orderid = $OrderInitiate->CreateOrder(false,$values['quor_id'],  $values['quor_RefNo'],  $values['quor_Date'],  $values['quor_PickupName'],  $values['quor_PickupAddress'],  $values['quor_PickupLocation'],  $values['quor_PickupPhone'],  $values['pickupLat'],  $values['pickupLng'],  $values['quor_PickupSMS'],  $values['quor_PickupStage1Distance'],  $values['quor_PickupStage2Distance'],$values['quor_DeliveryName'],  $values['quor_DeliveryAddress'],  $values['quor_DeliveryLocation'],  $values['quor_DeliveryPhone'],  $values['deliveryLat'],  $values['deliveryLng'],  $values['quor_DeliverySMS'],  $values['quor_DeliveryStage1Distance'],  $values['quor_DeliveryStage2Distance'],$values['quor_DistanceinKM'],$values['quor_Status'],0,$consignment,$Manualpush,$values['quor_FirstScheduleRun'],$order,$values['quor_PickupPincode'],$values['quor_DeliveryPincode'],$values['quor_Pickupbr_id'] , $values['quor_Deliverybr_id'], $values['quor_AmountCollectible']  );
				if($orderid=='' || empty($orderid)){
					echo "Error on creating order " . $orderid . " for booking " . $values['bk_no'];
					exit;
				}		
				if($schedule){
					$OrderPoller = new QugeoOrderPoller();	
					file_put_contents('php://stderr', "GOING TO CALL " . false );	
					$createdpoll = $OrderPoller->CreateAPoll($orderid,'false',$order,$specificvehicle);
					if($createdpoll){
						$this->db->query("UPDATE  qugeo_order set quor_Type=1,quor_QugeoDeliveryDDBOrderId = '" . $orderid . "',quor_Status=" . ORDER_DELIVERY_POLLED_DLS_ID . " where quor_id = " . $values['quor_id'] );				
					}else{
						$this->handoverForManualScheduling($orderid,false,$order);					
					}
				}
			}
			$this->db->query('commit');
			return $orderid;
		}
		public function scheduleNewBookings(){
			 
			$booking = $this->db->getMulipleData('SELECT  quor_id,  quor_RefNo,  quor_QugeoPickupDDBOrderId,quor_QugeoDeliveryDDBOrderId,  quor_PickupToBeManual,  quor_ScheduleOpeningTime,  quor_Date,  quor_PickupName,  quor_PickupAddress,  quor_PickupLocation,  quor_PickupPhone,  quor_PickupLat as pickupLat,  quor_PickupLng as pickupLng,  quor_PickupSMS,  quor_PickupStage1Distance,  quor_PickupStage2Distance,  quor_DeliveryName,  quor_DeliveryAddress,  quor_DeliveryLocation,  quor_DeliveryPhone,  quor_DeliveryLat as deliveryLat,  quor_DeliveryLng as deliveryLng,  quor_DeliverySMS,  quor_DeliveryStage1Distance,  quor_DeliveryStage2Distance,  quor_CreatedOn, quor_Status,quor_DistanceinKM,quor_LastScheduleRun,quor_PickupToBeManual,quor_ScheduleOpeningTime,quor_FirstScheduleRun,quor_PickupPincode,quor_DeliveryPincode,quor_Pickupbr_id,quor_Deliverybr_id,quor_AmountCollectible FROM qugeo_order where quor_PickupToBeManual =0 and quor_ScheduleOpeningTime <="' . date('Y-m-d H:i:s' ) . '" and quor_Type = 1 and quor_Status =' . ORDER_PICKUP_AT_ORIGIN_DLS_ID . ' order by quor_CreatedOn asc ' ,true);
			if(!empty($booking)){
				echo "Found booking for Auto scheduling \n";
				$this->getBookingOrderAndPollIt($booking,true,'',false);
			}else{
				echo "Found zero booking for Auto scheduling \n";
			}
		}
		public function scheduleABooking($quorid,&$order,$schedule=true,$specificvehicle='',$Manualpush=true){
					 
				$booking = $this->db->getMulipleData('select quor_id,  quor_RefNo,  quor_QugeoPickupDDBOrderId,quor_QugeoDeliveryDDBOrderId,  quor_PickupToBeManual,  quor_ScheduleOpeningTime,  quor_Date,  quor_PickupName,  quor_PickupAddress,  quor_PickupLocation,  quor_PickupPhone,  quor_PickupLat as pickupLat,  quor_PickupLng as pickupLng,  quor_PickupSMS,  quor_PickupStage1Distance,  quor_PickupStage2Distance,  quor_DeliveryName,  quor_DeliveryAddress,  quor_DeliveryLocation,  quor_DeliveryPhone,  quor_DeliveryLat as deliveryLat,  quor_DeliveryLng as deliveryLng,  quor_DeliverySMS,  quor_DeliveryStage1Distance,  quor_DeliveryStage2Distance,  quor_CreatedOn, quor_Status,quor_DistanceinKM,quor_LastScheduleRun,quor_PickupToBeManual,quor_ScheduleOpeningTime,quor_FirstScheduleRun,quor_PickupPincode,quor_DeliveryPincode,quor_Pickupbr_id,quor_Deliverybr_id,quor_AmountCollectible from  qugeo_order where quor_id = ' . $quorid  ,true);
				$orderid='';
				if(!empty($booking)){
					$orderid = $this->getBookingOrderAndPollIt($booking,$schedule,$specificvehicle,$Manualpush);
				}
			return	$orderid;			
		}		
		public function scheduleADelivery($quorid,&$order,$schedule=true,$specificvehicle='',$Manualpush=true,$WasDirectDeliveryPickUp=false,$deliveringbranch){
				 
				$booking = $this->db->getMulipleData('select quor_id,  quor_RefNo,  quor_QugeoPickupDDBOrderId,quor_QugeoDeliveryDDBOrderId,  quor_PickupToBeManual,  quor_ScheduleOpeningTime,  quor_Date,  quor_PickupName,  quor_PickupAddress,  quor_PickupLocation,  quor_PickupPhone,  quor_PickupLat as pickupLat,  quor_PickupLng as pickupLng,  quor_PickupSMS,  quor_PickupStage1Distance,  quor_PickupStage2Distance,  quor_DeliveryName,  quor_DeliveryAddress,  quor_DeliveryLocation,  quor_DeliveryPhone,  quor_DeliveryLat as deliveryLat,  quor_DeliveryLng as deliveryLng,  quor_DeliverySMS,  quor_DeliveryStage1Distance,  quor_DeliveryStage2Distance,  quor_CreatedOn, quor_Status,quor_DistanceinKM,quor_LastScheduleRun,quor_PickupToBeManual,quor_ScheduleOpeningTime,quor_FirstScheduleRun,quor_Pickupbr_id,quor_Deliverybr_id,quor_AmountCollectible from  qugeo_order  where quor_id = ' . $quorid  ,true);
				$orderid='';
				if(!empty($booking)){
						$orderid=$this->getDeliveryOrderAndPollIt($booking,$schedule,$specificvehicle,$Manualpush,$WasDirectDeliveryPickUp,$deliveringbranch);
				}
			return	$orderid;			
		}
		public function updateNoResponsePolls(){
			
				$valdatetime = date("YmdHis");	
				$valdatetime = date("YmdHis",strtotime($valdatetime) - QUGEO_POLL_NO_RESPONSE_TIMEOUT)  ;
				//file_put_contents('php://stderr', $valdatetime . ' =  ' . date("YmdHis") ."\n");
				$arrOrderDetails=array();
				$arrOrderDetails['PartitionKey'] = array('col'=>'isclosed','val'=>0,'oper'=>'=');
				$arrOrderDetails['SortKey']=array('col'=>'createddatetime','val'=>(int)$valdatetime,'oper'=>'<');	
				$arrOrderDetails['IndexName'] = 'isclosed-createddatetime-index';
				$arrOrderDetails['queryAttributes']=array('pollingid','apikey','orderid','currentstatus','ispickup');
				$nodb = new \cgoDynamiteDB();	
				$rsno = $nodb->query('QugeoOrderPollingDetails',$arrOrderDetails,'query');	
				if(isset($rsno) && count($rsno) > 0 ){
					foreach($rsno as $value){	
						$orderid=$value['orderid'];
						$arrOrder['PartitionKey'] = array('col'=>'orderid','val'=>$orderid,'oper'=>'=');		
						$arrOrder['getAttributes']=array('quor_id','IsPickup');
						$nors = $nodb->query('QugeoOrderDetails',$arrOrder,'getItem');	
							if(isset($nors) && count($nors) > 0 ){
								$arrUpdate = array();
								$arrUpdate['PartitionKey']=array('col'=>'orderid','val'=>$orderid);				
								$arrUpdate['Data']=array();								
								if($nors['IsPickup']==1){
										$this->db->query("UPDATE  qugeo_order set quor_Status=" . ORDER_PICKUP_POLL_NORESP_DLS_ID . " where quor_id = " .$nors['quor_id'] );								
										array_push($arrUpdate['Data'],array('col'=>'OrderStatus','val'=>(int)ORDER_PICKUP_POLL_NORESP_DLS_ID ));
								}else{
										$this->db->query("UPDATE  qugeo_order set  quor_Status=" . ORDER_DELIVERY_POLL_NORESP_DLS_ID . " where quor_id = " . $nors['quor_id'] );
										array_push($arrUpdate['Data'],array('col'=>'OrderStatus','val'=>(int)ORDER_DELIVERY_POLL_NORESP_DLS_ID));		
								}
								$uprs = $nodb->perform('QugeoOrderDetails','update',$arrUpdate,$response);	
								$pollresp = new QugeoOrderPoller();
								$pollclosed = $pollresp->PollResponse($value['pollingid'],3,false,$acceptedorder);
								if($uprs==false ){
									throw new \Exception('No response of update on QugeoOrderDetails, throws error');
								}						
							}					
					}
				}else {
					return 0;	
				}
				
		}
		public function rescheduleBookings(){	
				
				$arrOpenOrders=array();
				$arrOpenOrders['PartitionKey'] = array('col'=>'IsClosed','val'=>0,'oper'=>'=');
				$arrOpenOrders['SortKey']=array('col'=>'OrderStatus','val1'=>(int)ORDER_PICKUP_POLL_REJECTED_DLS_ID,'val2'=>(int)ORDER_PICKUP_POLL_NORESP_DLS_ID,'SortKeyBetween'=>true);				
				//$arrOpenOrders['SortKey']=array('col'=>'Latitude','val'=>$arrDegrees['lat1'],'oper'=>'=');	
				$arrOpenOrders['IndexName'] = 'IsClosed-OrderStatus-index';
				$arrOpenOrders['queryAttributes']=array('orderid','IsPickup','createddatetime','createddate','quor_id','pickupLat','pickupLng','DistSrcToBr','DistSrcToDst','deliveryLat','deliveryLng','BrLat','BrLong','HasDirectDeliveryPickUp','IsClosed','OrderStatus','Acceptedapikey','bkno','mobile','name','address','pktcount','chrgwt','netamt','ManuallyPushed','BookingDateTime','ScheduleOpeningTime','UserScheduleTime','FirstScheduleRun','PickupPincode','DeliveryPincode','TotalDistKM','pickuplocation','deliverylocation');
				$nodb = new \cgoDynamiteDB();	
				$rsno = $nodb->query('QugeoOrderDetails',$arrOpenOrders,'query');
				echo "Rescheuled \n";
				print_r($rsno);
				if(isset($rsno) && count($rsno) > 0 ){		
					$order =array();
					foreach($rsno as $value){		
						$orderid=$value['orderid'];
						$order  = array('orderid'=>$orderid,'IsPickup'=>$value['IsPickup'],'createddatetime'=>$value['createddatetime'],'createddate'=>$value['createddate'],'quor_id'=>$value['quor_id'],'pickupLat'=>$value['pickupLat'],'pickupLng'=>$value['pickupLng'],'DistSrcToBr'=>$value['DistSrcToBr'],'DistSrcToDst'=>$value['DistSrcToDst'],'deliveryLat'=>$value['deliveryLat'],'deliveryLng'=>$value['deliveryLng'],'BrLat'=>$value['BrLat'],'BrLong'=>$value['BrLong'],'HasDirectDeliveryPickUp'=>$value['HasDirectDeliveryPickUp'],'IsClosed'=>$value['IsClosed'],'OrderStatus'=>$value['OrderStatus'],'Acceptedapikey'=>$value['Acceptedapikey'],'bkno'=>$value['bkno'],'mobile'=>$value['mobile'],'name'=>$value['name'],'address'=>$value['address'],'pktcount'=>$value['pktcount'],'chrgwt'=>$value['chrgwt'],'netamt'=>$value['netamt'],'ManuallyPushed'=>$value['ManuallyPushed'],'BookingDateTime'=>$value['BookingDateTime'],'ScheduleOpeningTime'=>$value['ScheduleOpeningTime'],'UserScheduleTime'=>$value['UserScheduleTime'],'FirstScheduleRun'=>$value['FirstScheduleRun'],'PickupPincode'=>$value['PickupPincode'],'DeliveryPincode'=>$value['DeliveryPincode'],'Lat'=>$value['pickupLat'],'Lng'=>$value['pickupLng'],'TotalDistKM'=>$value['TotalDistKM'],'pickuplocation'=>$value['pickuplocation'],'deliverylocation'=>$value['deliverylocation']);		
						if($value['ManuallyPushed']==0){	
							echo $orderid . " to Manual Pushed \n";
							$OrderPoller = new QugeoOrderPoller();	
							$createdpoll = $OrderPoller->CreateAPoll($orderid,true,$order);
							$now = date('Y-m-d H:i:s');
							if($createdpoll){
								echo $orderid . " to poll created \n";
								$this->updateOrderOnPoll($orderid,ORDER_PICKUP_POLLED_DLS_ID);
								$this->db->query("UPDATE  qugeo_order set quor_LastScheduleRun ='" . $now . "', quor_QugeoPickupDDBOrderId = '" . $orderid . "',quor_Status=" . ORDER_PICKUP_POLLED_DLS_ID . " where quor_id = " . $value['quor_id']  );											
							}else{
								echo $orderid . " to NO poll created \n";
								//If no vehicle found - puch to manual schedule		
								$this->db->query("UPDATE  qugeo_order set quor_LastScheduleRun ='" . $now . "' where quor_id = " . $value['quor_id'] );
								$this->setDeffredPickupSchedule($orderid,true,$order);								
								//$this->handoverForManualScheduling($orderid,true,$order);						
							}			
						}else{
								//If Manually pushed 	
								echo $orderid . " to Manual schedule \n";
								$this->handoverForManualScheduling($orderid,true,$order);						
							}							
					}
				}			
		}
		public function rescheduleDelivery(){	
				
				$arrOpenOrders=array();
				$arrOpenOrders['PartitionKey'] = array('col'=>'IsClosed','val'=>0,'oper'=>'=');
				$arrOpenOrders['SortKey']=array('col'=>'OrderStatus','val1'=>(int)ORDER_DELIVERY_POLL_REJECTED_DLS_ID,'val2'=>(int)ORDER_DELIVERY_POLL_NORESP_DLS_ID,'SortKeyBetween'=>true);				
				//$arrOpenOrders['SortKey']=array('col'=>'Latitude','val'=>$arrDegrees['lat1'],'oper'=>'=');				
				$arrOpenOrders['IndexName'] = 'IsClosed-OrderStatus-index';
				$arrOpenOrders['queryAttributes']=array('orderid','IsPickup','createddatetime','createddate','quor_id','bk_brk_br_id','pickupLat','pickupLng','DistSrcToBr','DistSrcToDst','deliveryLat','deliveryLng','BrLat','BrLong','HasDirectDeliveryPickUp','IsClosed','OrderStatus','Acceptedapikey','bkno','mobile','name','address','pktcount','chrgwt','netamt','ManuallyPushed','PickupPincode','DeliveryPincode','TotalDistKM','pickuplocation','deliverylocation');
				$nodb = new \cgoDynamiteDB();	
				$rsno = $nodb->query('QugeoOrderDetails',$arrOpenOrders,'query');
				if(isset($rsno) && count($rsno) > 0 ){		
					$order =array();
					foreach($rsno as $value){					
						$order  =array('orderid'=>$value['orderid'],'IsPickup'=>$value['IsPickup'],'createddatetime'=>$value['createddatetime'],'createddate'=>$value['createddate'],'quor_id'=>$value['quor_id'],'bk_brk_br_id'=>$value['bk_brk_br_id'],'pickupLat'=>$value['pickupLat'],'pickupLng'=>$value['pickupLng'],'DistSrcToBr'=>$value['DistSrcToBr'],'DistSrcToDst'=>$value['DistSrcToDst'],'deliveryLat'=>$value['deliveryLat'],'deliveryLng'=>$value['deliveryLng'],'BrLat'=>$value['BrLat'],'BrLong'=>$value['BrLong'],'HasDirectDeliveryPickUp'=>$value['HasDirectDeliveryPickUp'],'IsClosed'=>$value['IsClosed'],'OrderStatus'=>$value['OrderStatus'],'Acceptedapikey'=>$value['Acceptedapikey'],'bkno'=>$value['bkno'],'mobile'=>$value['mobile'],'name'=>$value['name'],'address'=>$value['address'],'pktcount'=>$value['pktcount'],'chrgwt'=>$value['chrgwt'],'netamt'=>$value['netamt'],'Lat'=>$value['deliveryLat'],'Lng'=>$value['deliveryLng'],'PickupPincode'=>$value['PickupPincode'],'DeliveryPincode'=>$value['DeliveryPincode'],'Lat'=>$value['deliveryLat'],'Lng'=>$value['deliveryLng'],'TotalDistKM'=>$value['TotalDistKM'],'pickuplocation'=>$value['pickuplocation'],'deliverylocation'=>$value['deliverylocation']);					
					if($value['ManuallyPushed']==0){	
						$OrderPoller = new QugeoOrderPoller();	
						$createdpoll = $OrderPoller->CreateAPoll($orderid,false,$order);
						if($createdpoll){
							$this->updateOrderOnPoll($orderid,ORDER_DELIVERY_POLLED_DLS_ID);
							$this->db->query("UPDATE  qugeo_order set quor_QugeoDeliveryDDBOrderId = '" . $orderid . "',quor_Status=" . ORDER_DELIVERY_POLLED_DLS_ID . " where quor_id = " . $values['quor_id']  );							
						}else{
								//If no vehicle found								
								$this->handoverForManualScheduling($orderid,false,$order);						
							}
					}else{
						$this->handoverForManualScheduling($value['orderid'],false,$order);														
					}
				}
			}
		}
		public function handoverForManualScheduling($orderid,$ispickup,$order){		
			
			$arrUpdate = array();
			$arrUpdate['PartitionKey']=array('col'=>'orderid','val'=>$orderid);				
			$arrUpdate['Data']=array();
			array_push($arrUpdate['Data'],array('col'=>'IsClosed','val'=>1));
			if($ispickup){
					$this->db->query("UPDATE  qugeo_order set quor_PickupToBeManual=1,quor_Type=0,quor_Status=" . ORDER_PICKUP_AT_ORIGIN_DLS_ID . " where quor_id = " . $order['quor_id']  );					
			}else{
					$this->db->query("UPDATE  qugeo_order set  quor_DeliveryToBeManual=1,quor_Type=0,quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . "  where quor_id = " . $order['quor_id']  );					
			}
			$nodb = new \cgoDynamiteDB();
			$uprs = $nodb->perform('QugeoOrderDetails','update',$arrUpdate,$response);	
		}		
		public function validateLiveQugeos(){
			
				$valdatetime = date("YmdHis");	
				//$outofservicedt = $valdatetime - QUGEO_LOGIN_OUT_OF_SERVICE_TIMEOUT;
				$logoutcutofftime = date("YmdHis",strtotime($valdatetime) - QUGEO_LOGIN_KEEPALIVE_TIMEOUT)  ;
				$nodb = new \cgoDynamiteDB();
				$degMat = new \cgoGeoUtilities();
				$arrVehicle=array();
				$arrVehicle['PartitionKey'] = array('col'=>'Is_Live','val'=>(int)'1','oper'=>'=');
				$arrVehicle['SortKey']=array('col'=>'LocationUpdateddatetime','val'=>(int)$logoutcutofftime,'oper'=>'<');	
				$arrVehicle['IndexName'] = 'Is_Live-LocationUpdateddatetime-index';
				$arrVehicle['queryAttributes']=array('apikey','LocationUpdateddatetime','DriverId');					
				$nodb = new \cgoDynamiteDB();	
				$rsno = $nodb->query('QugeoLiveVehicles',$arrVehicle,'query');	
				$vehicledetails =array();	
				if(isset($rsno) && count($rsno) > 0 ){				
					foreach($rsno as $value){					
						$arrUpdate=array();
						$arrUpdate['PartitionKey']=array('col'=>'apikey','val'=>(string)$value['apikey']);
						$arrUpdate['Data']=array();
						$datetime = date("YmdHis");						
						array_push($arrUpdate['Data'],array('col'=>'Is_Live','val'=>0 ));
						array_push($arrUpdate['Data'],array('col'=>'LoggedOutAt','val'=>(string)$datetime ));
						array_push($arrUpdate['Data'],array('col'=>'IsCleanLogout','val'=>3 ));							
						$nors = $nodb->perform('QugeoLiveVehicles','update',$arrUpdate,$response);
						$arrSession =array();
						$arrSession['PartitionKey']=array('col'=>'apikey','val'=>$value['apikey']);
						$arrSession['Data']=array();
						array_push($arrSession['Data'],array('col'=>'HasLoggedOut','val'=>1));
						array_push($arrSession['Data'],array('col'=>'LoggedOutAt','val'=>(string)$datetime ));
						array_push($arrSession['Data'],array('col'=>'IsCleanLogout','val'=>0 ));
						$nors = $nodb->perform('APIHistory','update',$arrSession,$response);
						$arrSession =array();
						$arrSession['Data']=array();
						$arrSession['PartitionKey'] = array('col'=>'usertype','val'=>2);
						$arrSession['SortKey']=array('col'=>'id','val'=>(string)$value['DriverId']);				
						array_push($arrSession['Data'],array('col'=>'apikey','val'=>(string)'-' ));					
						$nosession = $nodb->perform('APISession','update',$arrSession,$response);
						$this->db->query('update qugeo_driver set d_apikey = "-" where d_ID  =' . $value['DriverId'] );
					}			
					return true;
				} else {
					return false;
				}	
		}
		public function IsQugeoAPIAlive($apikey){
				$nodb = new \cgoDynamiteDB();	
				$arrOrder['PartitionKey'] = array('col'=>'apikey','val'=>$apikey,'oper'=>'=');
				$arrOrder['IndexName'] = 'apikey-index';		
				$arrOrder['queryAttributes']=array('apikey');	
				$rsno = $nodb->query('APISession',$arrOrder,'query');	
				if(isset($rsno) && count($rsno) > 0 ){
					return true;
				}else{
					return false;
				}
		}
		public function updateOrderOnPoll($orderid,$status){
				$nodb = new \cgoDynamiteDB();	
				$arrUpdate = array();
				$arrUpdate['PartitionKey']=array('col'=>'orderid','val'=>$orderid);				
				$arrUpdate['Data']=array();
				array_push($arrUpdate['Data'],array('col'=>'OrderStatus','val'=>(int)$status));
				$rsno = $nodb->perform('QugeoOrderDetails','update',$arrUpdate,$response);					
				if($rsno!=false){
					return true;			
				}else {
					return false;	
				}
				
		}
	}

}