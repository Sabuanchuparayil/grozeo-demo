<?php
	namespace Models;
	{
		class Auth extends ModelAbstract
		{	
			private function resumeSession($requestapikey){
				$session['IsSessionResumed'] = true;
				$session['apikey'] = $requestapikey;
				$orderhandler = new QugeoOrderHandler();
				$hasorder = $orderhandler->GetNextOrder($requestapikey,$neworder,true);	
				if($hasorder){
					$session['HasOrder'] = true;
					//$session['nextorderdetails'] = $neworder;
					$session['istriprerouted'] = $neworder['istriprerouted'];
					$session['mapdetails'] = $neworder['mapdetails'];
					$session['nextorderdetails'] = $neworder['nextorderdetails'];				
					}else{
					$session['HasOrder'] = false;
					$session['nextorderdetails'] = array();
					$session['istriprerouted'] = false;
					$session['mapdetails'] = array();
				}
				return $session;
				
			}
			public function GET_auth($flag,$request){		
				if (!array_key_exists('userid', $request) || !array_key_exists('password',$request) || !array_key_exists('usertype',$request) || !isset($request) ) {
					file_put_contents('php://stderr', "MISSING parameter 1 for GET_auth " . $_SERVER['REMOTE_ADDR'] . "\n");
					file_put_contents('php://stderr', print_r($request, TRUE));
					throw new \Exception('Invalid Login credentials');
				}
				if ($request['userid']=='' ||$request['password'] ==''){
					file_put_contents('php://stderr', "MISSING parameter 2 for GET_auth " . $_SERVER['REMOTE_ADDR'] . "\n");
					file_put_contents('php://stderr', print_r($request, TRUE));
					throw new \Exception('Invalid Login Credentials');
				}
				$db = new \cgoSqlDB();				
				$nodb = new \cgoDynamiteDB();				
				$extrainfo = array();
				$isvalidarn =true;
				$arrAuth =array();
				if ($request['usertype']==1){ //Customer
					$rd = $db->getFromDB('select  primary_cid as Id, (select c_name from  customer where c_id = primary_cid) as Name, (select c_Ph1 from  customer where c_id = primary_cid) as Mobile, (select c_Icon from  customer where c_id = primary_cid)  as Icon from ' . DB_PREFIX . 'config.customer_login where approved =1 and userid=? and password =?',array('ss',$request['userid'] ,$request['password'] ) ,true);
					$extrainfo = array("c_id"=>$rd['Id'],"IsBranchBooking"=>false,"BranchUser"=>0);
					}elseif($request['usertype']==2){//Quego Driver
					$rd = $db->getFromDB('select  d_ID as Id, d_Name as Name, gcmregstid,d_awssnsarn,br_id from  qugeo_driver where d_Active =1 and d_Ph1=? and imeinumber =?',array('ss',$request['userid'] ,$request['password'] ) ,true);			
					if(!array_key_exists('gcmregisterationid',$request)){
						throw new \Exception('Missing GCM registraion id');	
					}				
					$extrainfo = array("v_id"=>"0");
					if($rd['gcmregstid'] !=""){
						//$sns = new \cgoAWSSNS();
						//if ($sns->getEndPointDetails($rd['d_awssnsarn'],'Enabled')=='false'){	 				
						//	$isvalidarn =false;
						//	}else{
							$isvalidarn =true;
						//}
					}
					}elseif($request['usertype']==3){//Backoffice user
					$rd = $db->getFromDB('select  UserId as Id, UserName as Name from ' . DB_PREFIX . 'config.usr_master where UserId=? and  passwd = ? ',array('is',$request['userid'] ,$request['password'] ) ,true);
					if(!array_key_exists('custid',$request)){
						throw new \Exception('Missing customer details');	
						}elseif(!array_key_exists('actiontype',$request)){
						throw new \Exception('Missing customer details - actiontype');	
					}
					if(count($rd) == 0 || empty( $rd )){
						$arrAuth['msg'] = 'Invalid login credentialS ' . $request['usertype'] ;	
						$arrAuth['Data'] = array();
						return $arrAuth ;		
					}				
					if($request['actiontype'] == 'Book'){								
						$extrainfo = array("c_id"=>$request['custid'],"IsBranchBooking"=>true,"BranchUser"=>$request['userid']);
						$request['usertype']=1;
						$rd['Id'] = $request['custid'];
						$rd['Name'] = $request['custname'];
					}
					}elseif($request['usertype']==4){//CrossBooking
					$util =  new Utils();									
					if($util->IsValidDomainAuthKey($request['password'],INWARD_CROSSBOOKING_DOMAIN_NAME,$request['userid'],2) != true){
						throw new \Exception('Invalid Login CredentialS');	
					}	
					$rd['Id']=$request['userid'];
					$rd['Name'] = '';
					$extrainfo = array("u_id"=>$request['userid']);
					}elseif($request['usertype']==5){//Delivery Boy
					$rd = $db->getFromDB('select db_ID as Id, db_Name as Name,otp,otpvalidtill,br_ID,(select br_name from  branch br where br.br_id =  db.br_id  ) as brName from  deliveryboy db where db_Ph1=? limit 1',array('s',$request['userid']) ,true);
					$extrainfo = array("db_ID"=>$rd['Id'],"db_Name"=>$rd['Name']);
					if($rd['otp'] != $request['password'] ){
						$isvalidotp =false;
						}else{
						$isvalidotp =true;
					}
					}else{
					throw new \Exception('Invalid login usertype');	
				}
				
				
				
				if(count($rd) == 0 || empty( $rd )){
					file_put_contents('php://stderr', print_r($request, TRUE));
					$arrAuth['msg'] = 'Invalid Login credentials ' . $request['usertype'] ;	
					$arrAuth['Data'] = array();
					}elseif($request['usertype']==2 && ($rd['gcmregstid']!= $request['gcmregisterationid'] || $rd['gcmregstid'] =='')){
					$arrAuth['msg'] = 'Invalid GCM registraion id';	
					$arrAuth['Data'] = array();
					}elseif($request['usertype']==2 && $isvalidarn == false){
					$arrAuth['msg'] = 'GCM has changed, You need to re-install Application';	
					$arrAuth['Data'] = array();
					}elseif($request['usertype']==5 && $isvalidotp == false){
					$arrAuth['msg'] = 'Invalid OTP, please check';	
					$arrAuth['Data'] = array();
					}else{
					$arrAPI=array();
					$arrAPI['PartitionKey'] =array('col'=>'usertype','val'=>(int)$request['usertype']);
					$arrAPI['SortKey'] =array('col'=>'id','val'=>(string)$rd['Id']);
					$arrAPI['getAttributes']=array('apikey');
					$rsno = $nodb->query('APISession',$arrAPI,'getItem');	
					if(isset($rsno) && count($rsno) > 0 ){
						$apikey = $rsno['apikey'];
						if( $apikey!='-' && isset($apikey) && trim($apikey)!==''){
							if($request['usertype']==2){
								
								$arrAPI=array();
								$arrAPI['PartitionKey'] = array('col'=>'apikey','val'=>$apikey,'oper'=>'=');
								$arrAPI['getAttributes']=array('Is_Live','v_no','v_id','v_type','AWS_SNS_ARN');
								$rsno = $nodb->query('QugeoLiveVehicles',$arrAPI,'getItem');	
								if(isset($rsno) && count($rsno) > 0 ){
									if (intval($rsno['Is_Live'])>0  && $rd['d_awssnsarn'] == $rsno['AWS_SNS_ARN'] ){
										$prevSession = $this->resumeSession($apikey);
										$prevSession['vehicleregno'] = $rsno['v_no'];
										$prevSession['Name'] = $rd['Name'];
										$prevSession['vehicleid'] = $rsno['v_id'];
										$prevSession['vehicletype'] = $rsno['v_type'];
										$arrAuth['success']=true;
										$arrAuth['msg'] = 'Session Resumed';	
										$arrAuth['Data'] = $prevSession;	
										return $arrAuth;
									}
								}
								
								$arrUpdate=array();
								$arrUpdate['PartitionKey']=array('col'=>'apikey','val'=>$apikey);
								$arrUpdate['Data']=array();
								$datetime = date("YmdHis");
								array_push($arrUpdate['Data'],array('col'=>'Is_Live','val'=>(int)0 ));
								array_push($arrUpdate['Data'],array('col'=>'LoggedOutAt','val'=>(string)$datetime ));
								array_push($arrUpdate['Data'],array('col'=>'IsCleanLogout','val'=>2 ));	
								$nors = $nodb->perform('QugeoLiveVehicles','update',$arrUpdate,$response);
							}	
							$arrSession =array();
							$arrSession['PartitionKey']=array('col'=>'apikey','val'=>$apikey);
							$arrSession['Data']=array();
							array_push($arrSession['Data'],array('col'=>'HasLoggedOut','val'=>1));
							array_push($arrSession['Data'],array('col'=>'LoggedOutAt','val'=>(int)date("YmdHis") ));
							array_push($arrSession['Data'],array('col'=>'IsCleanLogout','val'=>0 ));
							$nors = $nodb->perform('APIHistory','update',$arrSession,$response);	
							
						}
					}
					
					$apikey =  sha1(microtime(true).mt_rand(10000,90000));
					$arrUpdate=array();				
					$arrUpdate['Data']=array();
					$valdate = date("YmdHis");
					
					if($request['usertype']==2){
						$validityseconds = QUGEO_LOGIN_KEEPALIVE_TIMEOUT;
						}elseif($request['usertype']==5){
						$validityseconds = CAREGO_APP_LOGIN_KEEPALIVE_TIMEOUT;
						}else{
						$validityseconds = CAREGO_LOGIN_KEEPALIVE_TIMEOUT;
					}
					
					array_push($arrUpdate['Data'],array('col'=>'usertype','val'=>(int)$request['usertype']));
					array_push($arrUpdate['Data'],array('col'=>'id','val'=>(string)$rd['Id']));	
					if(isset($rd['br_id'])){
						array_push($arrUpdate['Data'],array('col'=>'branchid','val'=>(int)$rd['br_id']));	
					}else{
						array_push($arrUpdate['Data'],array('col'=>'branchid','val'=>0));	
					}
					array_push($arrUpdate['Data'],array('col'=>'apikey','val'=>$apikey));
					array_push($arrUpdate['Data'],array('col'=>'validtill','val'=>(int)(time() + $validityseconds)));	
					array_push($arrUpdate['Data'],array('col'=>'lastvalidation','val'=>(int)$valdate ));
					array_push($arrUpdate['Data'],array('col'=>'extrainfo','val'=>$extrainfo ));
					array_push($arrUpdate['Data'],array('col'=>'clienttype','val'=>(string)$request['clienttype'] ));
					array_push($arrUpdate['Data'],array('col'=>'clientosname','val'=>(string)$request['clientosname'] ));
					array_push($arrUpdate['Data'],array('col'=>'clientosver','val'=>(string)$request['clientosver'] ));
					array_push($arrUpdate['Data'],array('col'=>'clientappver','val'=>(string)$request['clientappver'] ));
					
					$nors = $nodb->perform('APISession','insert',$arrUpdate,$response);		
					
					
					if ($nors){
						$arrUpdate=array();
						$valdate = date("Ymd");
						$valdatetime = date("YmdHis");
						if ($request['usertype']==1){
							$arrUpdate['PartitionKey']=array('col'=>'c_id','val'=>(int)$extrainfo['c_id']);
							$arrUpdate['createifnotexists'] =true;
							$table = 'CustomerMaster';
							}elseif($request['usertype']==2){
							$arrUpdate['PartitionKey']=array('col'=>'d_id','val'=>(string)$rd['Id']);
							$table = 'QugeoDriveMaster';	
							$db->query('update qugeo_driver set d_apikey = "' . $apikey . '" where d_ID  =' . $rd['Id']  );						
							}elseif($request['usertype']==3){
							$arrUpdate['PartitionKey']=array('col'=>'u_id','val'=>(int)$rd['Id']);
							$table = 'UserMaster';							
							}elseif($request['usertype']==4){
							$arrUpdate['PartitionKey']=array('col'=>'createddatetime','val'=>(int)$valdatetime);
							$arrUpdate['SortKey']=array('col'=>'id','val'=>(string)$rd['Id']);
							$arrUpdate['createifnotexists'] =true;
							$extrainfo['c_id']=0;
							$table = 'CrossBookSession';							
							}elseif($request['usertype']==5){
							$arrUpdate['PartitionKey']=array('col'=>'db_id','val'=>(int)$rd['Id']);
							$table = 'DeliveryBoyMaster';							
						}
						$arrUpdate['Data']=array();
						
						
						array_push($arrUpdate['Data'],array('col'=>'lastlogindate','val'=>(string)$valdate));												
						array_push($arrUpdate['Data'],array('col'=>'lastlogindatetime','val'=>(string)$valdatetime));
						
						$nors = $nodb->perform($table,'update',$arrUpdate,$response);		
						if ($nors){
							$arrUpdate=array();
							$arrUpdate['Data']=array();													
							array_push($arrUpdate['Data'],array('col'=>'id','val'=>(string)$rd['Id']));
							array_push($arrUpdate['Data'],array('col'=>'extrainfo','val'=>(string)($request['usertype']==2?$extrainfo['v_id']:$extrainfo['c_id']) ));
							array_push($arrUpdate['Data'],array('col'=>'usertype','val'=>(int)$request['usertype'] ));
							array_push($arrUpdate['Data'],array('col'=>'apikey','val'=>$apikey ));
							array_push($arrUpdate['Data'],array('col'=>'createddatetime','val'=>(string)$valdatetime ));
							array_push($arrUpdate['Data'],array('col'=>'createddate','val'=>(int)$valdate ));
							array_push($arrUpdate['Data'],array('col'=>'HasLoggedOut','val'=>0 ));
							array_push($arrUpdate['Data'],array('col'=>'IP','val'=>$_SERVER['REMOTE_ADDR']));
							array_push($arrUpdate['Data'],array('col'=>'clienttype','val'=>(string)$request['clienttype'] ));
							array_push($arrUpdate['Data'],array('col'=>'clientosname','val'=>(string)$request['clientosname'] ));
							array_push($arrUpdate['Data'],array('col'=>'clientosver','val'=>(string)$request['clientosver'] ));
							array_push($arrUpdate['Data'],array('col'=>'clientappver','val'=>(string)$request['clientappver'] ));
							
							$nors = $nodb->perform('APIHistory','insert',$arrUpdate,$response);		
							if ($nors){
								$arrAuth['success']=true;
								$_SESSION["loginid"]  = ($request['usertype']==1?$extrainfo['c_id']:$rd['Id']);	
								$_SESSION["usertype"]  = $request['usertype'];	
								$arrAuth['msg'] = 'API Key Generated';	
								$arrAuth['Data']['apikey'] = $apikey;			
								$arrAuth['Data']['Name'] = $rd['Name'];	
								$arrAuth['Data']['IsSessionResumed'] = false;	
								$arrAuth['Data']['HasOrder'] = false;	
								$arrAuth['Data']['nextorderdetails'] = array();	
								$arrAuth['Data']['istriprerouted'] = false;
								$arrAuth['Data']['mapdetails'] = array();
								if ($request['usertype']==1){
									$arrAuth['Data']['Mobile'] = $rd['Mobile'];	
									$arrAuth['Data']['Icon'] = $rd['Icon'];	
									}elseif ($request['usertype']==5){
									$arrAuth['Data']['BranchId'] = $rd['br_ID'];	
									$arrAuth['Data']['BranchName'] = $rd['brName'];	
								}								
								}else{
								$arrAuth['msg'] = 'API Key Generation failed for APIHistory';									
								$arrAuth['Data']['APIHistory']='';
							}
							}else{
							$arrAuth['msg'] = 'API Key Generation failed  for '. $table;	
							$arrAuth['Data']='';
						}
						}else{
						$arrAuth['msg'] = 'API Key Generation failed for APISession';	
						$arrAuth['Data']='';
						print_r($response);
					}
				}
				return $arrAuth ;		
			}
			public function GET_verifyKey($flag,$request){
				if(verifyKey($request['apikey'],null)){
					$arrAuth['success']=true;
					$arrAuth['msg'] = 'API Key Valid';	
					$arrAuth['Data'] = array('apikey'=> 'Valid');	
					}else{
					$arrAuth['msg'] = 'API Key InValid';	
					$arrAuth['Data'] = array('apikey'=> 'InValid');
				}
			}
			public function verifyKey($apiKey,$origin){
				/*$db = new \cgoSqlDB();	
				$isvalid = $db->getItemFromDB('select  1 as IsValid from ' . DB_PREFIX . 'config.customer_login where approved =1 and apikey = ? and apivalidtill > ?',array('ss',$apiKey ,time() ) ,true);*/
				$nodb = new \cgoDynamiteDB();	
				$arrAPI=array();
				//GetItem
				/*//array_push($arrUpdate['PartitionKey'],array('col'=>'apikey','val'=>$apiKey));
					//array_push($arrUpdate['Data'],array('col'=>'validtill'));
					$arrAPI['PartitionKey']=array('col'=>'c_id','val'=>1);
					$arrAPI['getAttributes']=array('validtill');
					$rsno = $nodb->query('CustomerAPI',$arrAPI,'getItem');			
					//print_r($rsno['validtill'] . ' ' . time());
					if (intval($rsno['validtill']) > time())
					return true;
					else
				return false;*/
				//Query
				$arrAPI['PartitionKey'] = array('col'=>'apikey','val'=>$apiKey,'oper'=>'=');
				$arrAPI['IndexName'] = 'apikey-index';
				$arrAPI['queryAttributes']=array('validtill','id','usertype','extrainfo','branchid');
				$arrAPI['Condition']=array();
				array_push($arrAPI['Condition'],array('col'=>'validtill','val'=>time(),'oper'=>'>' ));	
				$rsno = $nodb->query('APISession',$arrAPI,'query');
				
				//Extend the Session
				if(isset($rsno) && count($rsno) > 0){
					$usertype = $rsno[0]['usertype'];
					$id = $rsno[0]['id'];
					$branchid = $rsno[0]['branchid'];
					$extrainfo = $rsno[0]['extrainfo'];
					$arrUpdate=array();
					$arrUpdate['PartitionKey']=array('col'=>'usertype','val'=>$usertype);
					$arrUpdate['SortKey']=array('col'=>'id','val'=>$id);								
					$arrUpdate['Data']=array();
					if($usertype==2){
						$validityseconds = QUGEO_LOGIN_KEEPALIVE_TIMEOUT;
						}elseif($usertype==5){
						$validityseconds = CAREGO_APP_LOGIN_KEEPALIVE_TIMEOUT;
						}else{
						$validityseconds = CAREGO_LOGIN_KEEPALIVE_TIMEOUT;
					}
					array_push($arrUpdate['Data'],array('col'=>'validtill','val'=>(int)(time() + $validityseconds)));
					array_push($arrUpdate['Data'],array('col'=>'lastvalidation','val'=>(string)date("YmdHis") ));
					$nors = $nodb->perform('APISession','update',$arrUpdate,$response);
					if ($nors){
						$_SESSION["loginid"]  = $id;
						$_SESSION["usertype"]  = $usertype;
						$_SESSION["branchid"]  = $branchid;
						if($usertype==1 || $usertype==3){					
							$_SESSION["c_id"] = $extrainfo['c_id']; 
							$_SESSION["IsBranchBooking"] = $extrainfo['IsBranchBooking']; 
							}elseif($usertype==5){
							$_SESSION["drvname"] = $extrainfo['db_Name']; 
						}
						return true;
					}
					else
					return false;
				}
				else{
					return false;			
				}
				
			}
			public function POST_logout($flag,$request){
				$nodb = new \cgoDynamiteDB();	
				$db = new \cgoSqlDB();
				//Set apikey to blank or hypen
				$arrSession =array();
				$arrSession['Data']=array();
				$arrSession['PartitionKey'] = array('col'=>'usertype','val'=>(int)$_SESSION["usertype"]);
				$arrSession['SortKey']=array('col'=>'id','val'=>(string)$_SESSION["loginid"]);				
				array_push($arrSession['Data'],array('col'=>'apikey','val'=>'-' ));			
				$nosession = $nodb->perform('APISession','update',$arrSession,$response);
				
				$datetime = date("YmdHis");
				//Set apihistory log out details 
				$arrSession =array();
				$arrSession['PartitionKey']=array('col'=>'apikey','val'=>$request['apikey']);
				$arrSession['Data']=array();
				array_push($arrSession['Data'],array('col'=>'HasLoggedOut','val'=>1));
				array_push($arrSession['Data'],array('col'=>'LoggedOutAt','val'=>(string)$datetime ));
				array_push($arrSession['Data'],array('col'=>'IsCleanLogout','val'=>1 ));
				$nors = $nodb->perform('APIHistory','update',$arrSession,$response);

				file_put_contents('php://stderr', " Calling Logout  \n");
				file_put_contents('php://stderr', print_r($_SESSION, TRUE));
				

				//Delete from QugeoLiveVehicles
				if($_SESSION["usertype"]==2){
					$util =  new Utils();	
					$kmscovered = $util->getKMInaTrip($request['apikey']);
					$arrUpdate=array();
					$arrUpdate['PartitionKey']=array('col'=>'apikey','val'=>$request['apikey']);
					$arrUpdate['Data']=array();
					array_push($arrUpdate['Data'],array('col'=>'Is_Live','val'=>0 ));
					array_push($arrUpdate['Data'],array('col'=>'LoggedOutAt','val'=>(string)$datetime ));
					array_push($arrUpdate['Data'],array('col'=>'IsCleanLogout','val'=>1 ));	
					array_push($arrUpdate['Data'],array('col'=>'KmsCovered','val'=>$kmscovered));	
					$nors = $nodb->perform('QugeoLiveVehicles','update',$arrUpdate,$response);
					$db->query('update qugeo_driver set d_apikey = "-" where d_ID  =' . $_SESSION["loginid"] );
				}
				$arrAuth=array();
				$arrAuth['success']=true;
				$arrAuth['msg'] = 'You have been successfully logged out';	
				$arrAuth['Data'] = array();
				return $arrAuth;
			}
			public function GET_testapi($flag,$request){		
				$arrAuth=array();
				$arrAuth['success']=true;
				$arrAuth['msg'] = 'Testing OK';	
				$arrAuth['Data'] = array();
				return $arrAuth;
			}
		}
	}
	
