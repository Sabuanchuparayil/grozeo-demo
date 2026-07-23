<?php
namespace Models;
{
	class Customer extends ModelAbstract
	{
		 //Get Address
		 private function getOrderDetails($table,$key,$selectcolumn){
			 
		 }
		/* public function getPickupDetails($rd,&$data,&$cntr){
			 //trs_id, dls_id,bk_no,bk_date,bk_time,bk_PickupQugeoId,bk_DeliveryQugeoId,bk_id,bk_brk_br_id,bk_DelDate,bk_DelTime,bk_Consignor,bk_PktCount,bk_TotInvAmt,bk_NetAmt,bk_paymode
			 $data['BookedDateTime'] =  $rd['bk_date']  . ' ' . $rd['bk_time'] ;
			 if($rd['dls_id']== 0 || ($rd['dls_id'] >=25 && $rd['dls_id'] <=30) ){
				 $rd['dls_id']
			 }
			 
			 			 
		 }*/
		 public function GET_address($flag,$request){
			if (!array_key_exists('isdelivery', $request)  || !isset($request) ) 
				throw new \Exception('Missing GET parameters ');			 
			 //GetItem
			 $nodb = new \cgoDynamiteDB();	
			 $arrCustAddress =array();
			 $arrCustAddress['PartitionKey']=array('col'=>'c_id','val'=>$_SESSION["c_id"]);
			 $addresstoselect = ($request['isdelivery']=='true'?'delivery_addresses':'pickup_addresses');
			 $arrCustAddress['getAttributes']=array($addresstoselect);
			 $rsno = $nodb->query('CustomerMaster',$arrCustAddress,'getItem');		
	 
			 $arrCustAddress =array();
			 if(count($rsno) > 0 ){		
				if (array_key_exists('saveasaddress', $request)) {
					$respAddress = $rsno[$addresstoselect];
					if (array_key_exists('pincode', $request)){
						foreach($respAddress as $k => $v )	{
							if($v['pincode']!=$request['pincode']){
								unset($respAddress[$k]);
							}
						}
					}
					if (array_key_exists($request['saveasaddress'], $respAddress)){
						$arrCustAddress['success']=true;
						$arrCustAddress['msg'] = 'Customer Address';							
						$arrCustAddress['Data']['Address'] = $respAddress[$request['saveasaddress']];	
						file_put_contents('php://stderr', "Adress 1 \n");
						file_put_contents('php://stderr', print_r( $respAddress[$request['saveasaddress']], TRUE));
					}
					else{
						$arrCustAddress['success']=true;
						$arrCustAddress['msg'] = 'Customer address not found';	
						$arrCustAddress['Data']['Address'] = array();		
					}				
				}	
				else{	
					$arr=array(); 
					foreach ($rsno[$addresstoselect] as $k => $v) {
						if (array_key_exists('pincode', $request)){							
							if($v['pincode']==$request['pincode']){									
									array_push($arr, $v);
							}
						}else{
							array_push($arr, $v);
						}
					}
					//$respAddress = $arr;
					
					$arrCustAddress['success']=true;
					$arrCustAddress['msg'] = 'Customer Address';						
					$arrCustAddress['Data']['Address'] = $arr;
							file_put_contents('php://stderr', "Adress 2 \n");
				file_put_contents('php://stderr', print_r($arr, TRUE));
				}
			}
			else{
				$arrCustAddress['success']=true;
				$arrCustAddress['msg'] = 'Customer address not found';	
				$arrCustAddress['Data']['Address'] = array();
			}
				return $arrCustAddress ;		
		}
		 public function POST_address($flag,$request){
				if (!array_key_exists('isdelivery', $request) ||  !array_key_exists('saveasaddress', $request) ||  !array_key_exists('address', $request) || !isset($request) ) {
					throw new \Exception('Missing Post parameters '  );	
				}
				 $address =  json_decode($request['address'],true );	
				 if(intval($address['location']['id'])==0){
					 throw new \Exception('Missing location id'  );	
				 }elseif($address['location']['name']==''){
					 throw new \Exception('Missing location name'  );	
				 }elseif(intval($address['locationid'])==0){
					 throw new \Exception('Missing locationid'  );	
				 }
				 
				 $nodb = new \cgoDynamiteDB();	
				 $arrCustAddress =array();
				 $arrCustAddress['PartitionKey']=array('col'=>'c_id','val'=>$_SESSION["c_id"]);
				 $addresstoselect = ($request['isdelivery']=='true'?'delivery_addresses':'pickup_addresses');
				 $arrCustAddress['getAttributes']=array($addresstoselect);
				 $rsno = $nodb->query('CustomerMaster',$arrCustAddress,'getItem');			
				 if(count($rsno) > 0 ){	
					$respAddress = $rsno[$addresstoselect];
				 }	
				$respAddress[$request['saveasaddress']] = $address ;
				$arrUpdate=array();
				$arrUpdate['PartitionKey']=array('col'=>'c_id','val'=>(int)$_SESSION["c_id"]);
				$arrUpdate['createifnotexists'] =true;
				$arrUpdate['Data']=array();

				array_push($arrUpdate['Data'],array('col'=>$addresstoselect,'val'=>$respAddress));
				$nors = $nodb->perform('CustomerMaster','update',$arrUpdate,$response);		
				$arrCustAddress=array();
			if(isset($nors) && count($nors) > 0 ){	
				$arrCustAddress['success']=true;			
				$arrCustAddress['msg'] = 'Customer Address added';	
				$arrCustAddress['Data']['Address'] = null;
			}
			else{
				print_r($response);
				print_r($arrUpdate);
				$arrCustAddress['msg'] = 'Customer address not added';	
				$arrCustAddress['Data']['Address'] = null;
			}
				return $arrCustAddress ;		
				
		}
		 public function GET_tracking($flag,$request){
			 if (!array_key_exists('bk_no', $request) || !isset($request) ) {
					throw new \Exception('Missing Get parameters '  );	
			 }
 			 $db = new \cgoSqlDB();	
			 $rd = $db->getFromDB('select  trs_id, dls_id,bk_no,bk_date,bk_time,bk_PickupQugeoId,bk_DeliveryQugeoId,bk_id,bk_brk_br_id,bk_DelDate,bk_DelTime,bk_Consignor,bk_PktCount,bk_TotInvAmt,bk_NetAmt,bk_paymode from  inward_booking where bk_cancelled =0 and bk_no=? ',array('s',$request['bk_no'] ) ,true);
			 if(empty($rd)){
				$arrCustAddress['msg'] = 'Invalid tracking number';	
				$arrCustAddress['Data']['Tracking'] = null;
				return;
			 }
			 $data = array();
			 $cntr = 0;
			// $this->getPickupDetails($rd,$data);
			 	
		 }		
	}
}