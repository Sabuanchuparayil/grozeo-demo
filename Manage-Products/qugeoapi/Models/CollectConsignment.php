<?php
	namespace Models;
	{
		class CollectConsignment extends ModelAbstract
		{
			private function getRandomRef(){
				$nodb = new \cgoDynamiteDB();			
				$arrSession['Data']=array();
				array_push($arrSession['Data'],array('col'=>'refno'));	
				$arrSession['ExclusiveStartKey'] = array('col'=>'refno','val'=>uniqid()); 	
				$arrSession['Limit'] =1;
				$rsno = $nodb->query('RefnoVault',$arrSession,'scan');	
				if (count($rsno)>0){					
					return $rsno['refno'];
					}else{
					
					return null;	
				}
			}
			public function GET_refno($flag,$request){
				$arrAuth = array();
				$refno = $this->getRandomRef();
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
				$db = new \cgoSqlDB();
				if ($this->closeallbookingsession($request['apikey'])){	
					$rd = $db->getFromDB('select br_ID,(select br_name from  branch br where br.br_id =  db.br_id  ) as brName from  deliveryboy db where db_ID=? limit 1',array('s',$_SESSION["loginid"]) ,true);
					
					$arrSession =array();
					$arrSession['Data']=array();
					$arrSession['PartitionKey'] = array('col'=>'usertype','val'=>(int)$_SESSION["usertype"]);
					$arrSession['SortKey']=array('col'=>'id','val'=>(string)$_SESSION["loginid"]);									
					array_push($arrSession['Data'],array('col'=>'clienttype','val'=>(string)$request['clienttype'] ));
					array_push($arrSession['Data'],array('col'=>'clientosname','val'=>(string)$request['clientosname'] ));
					array_push($arrSession['Data'],array('col'=>'clientosver','val'=>(string)$request['clientosver'] ));
					array_push($arrSession['Data'],array('col'=>'clientappver','val'=>(string)$request['clientappver'] ));
					
					$nosession = $nodb->perform('APISession','update',$arrSession,$response);
					
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
					array_push($arrSession['Data'],array('col'=>'dbid','val'=>(int)$_SESSION["loginid"] ));
					array_push($arrSession['Data'],array('col'=>'consignee','val'=>array()));
					array_push($arrSession['Data'],array('col'=>'isnewconsignee','val'=>0 ));
					$nosession = $nodb->perform('BookingSession','insert',$arrSession,$response);	
					$arrAuth = array();	
					if ($nosession){					
						$arrAuth['success']=true;
						$arrAuth['msg'] = 'New Booking Session started';	
						$arrAuth['Data']['bksession'] = $bksession;			
						$arrAuth['Data']['BranchId'] = $rd['br_ID'];	
						$arrAuth['Data']['BranchName'] = $rd['brName'];							
						}else{
						$arrAuth['msg'] = 'New Booking session failed';							
					}
					}else{
					$arrAuth['msg'] = 'New Booking session failed';		
				}
				return	$arrAuth;			
			}
			public function GET_company($flag,$request){
				if (!array_key_exists('BranchId', $request) || !isset($request) ) {
					throw new \Exception('Missing GET parameters ');
				}	
				$arrAuth = array();
				$db = new \cgoSqlDB();
				$company = $db->getMulipleData('select company.comp_id as CompanyId, comp_name as CompanyName from  company inner join  branch_company  using(comp_id) where br_id =? order by comp_name asc ',array('s',$request['BranchId']) ,true);					
				
				$arrAuth['success']=true;
				$arrAuth['msg'] = 'Company';	
				$arrAuth['Data']['Company'] =$company;												
				
				return $arrAuth;	
			}
			public function GET_state($flag,$request){
				
				$arrAuth = array();
				$db = new \cgoSqlDB();
				$state = $db->getMulipleData('select st_ID as StateId, ucase(st_name) as StateName from  state  order by StateName asc ',array() ,true);					
				
				$arrAuth['success']=true;
				$arrAuth['msg'] = 'State List';	
				$arrAuth['Data']['State'] =$state;												
				
				return $arrAuth;	
			}
			public function GET_district($flag,$request){
				if (!array_key_exists('StateId', $request) || !isset($request) ) {
					throw new \Exception('Missing GET parameter ' . $request['StateId']);
				}	
				$arrAuth = array();
				$db = new \cgoSqlDB();
				$district = $db->getMulipleData('select dst_Id as DistrictId, ucase(dst_Name) as DistrictName from  district where st_id=? order by DistrictName asc ',array('s',$request['StateId']) ,true);					
				
				$arrAuth['success']=true;
				$arrAuth['msg'] = 'District List';	
				$arrAuth['Data']['District'] =$district;												
				
				return $arrAuth;	
			}
			public function GET_branch($flag,$request){
				if (!array_key_exists('CompanyId', $request) || !isset($request) ) {
					throw new \Exception('Missing GET parameters ');
				}	
				$arrAuth = array();
				$db = new \cgoSqlDB();
				if($request['DistrictId']!='') {
					$branch = $db->getMulipleData('select branch.br_id as BranchId, br_name as BranchName,ucase(br_District) as District,ucase(br_State) as State from  branch inner join  branch_company  using(br_Id) inner join district on district.dst_Name = branch.br_District  where comp_id =?  and district.dst_Id=? order by BranchName asc ',array('si',$request['CompanyId'],$request['DistrictId']) ,true);
					}elseif($request['StateId']!=''){
					$branch = $db->getMulipleData('select branch.br_id as BranchId, br_name as BranchName,ucase(br_District) as District,ucase(br_State) as State from  branch inner join  branch_company  using(br_Id) inner join state on state.st_name = branch.br_State where comp_id =? and state.st_ID = ? order by BranchName asc ',array('si',$request['CompanyId'],$request['StateId']) ,true);
					}else{
					$branch = $db->getMulipleData('select branch.br_id as BranchId, br_name as BranchName,ucase(br_District) as District,ucase(br_State) as State from  branch inner join  branch_company  using(br_Id) where comp_id =? order by BranchName asc ',array('s',$request['CompanyId']) ,true);	
				}		
				
				$arrAuth['success']=true;
				$arrAuth['msg'] = 'Branch List';	
				$arrAuth['Data']['Branch'] =$branch;												
				
				return $arrAuth;	
			}
			public function GET_customer($flag,$request){
				if (!array_key_exists('start', $request) || !array_key_exists('limit', $request) || !array_key_exists('value', $request) || !array_key_exists('BranchId', $request) || !array_key_exists('type', $request) || !isset($request) ) {
					throw new \Exception('Missing GET parameters ');
				}	
				
				$arrAuth = array();
				$db = new \cgoSqlDB();
				if($request['type']==1){					
					$customer = $db->getMulipleData('select c_id as CustId, concat(c_name,"/",c_ph1) as Customer, c_ph1 as Mobile,c_Credit as HasCredit from  customer where c_name like ?  and br_id = ? and c_active =1 order by c_name asc limit ?,?',array('siii',$request['value'].'%',$request['BranchId'],$request['start'],$request['limit']) ,true);					
					}elseif($request['type']==2){					
					$customer = $db->getMulipleData('select c_id as CustId, concat(c_ph1,"/",c_name) as Customer, c_ph1 as Mobile,c_Credit as HasCredit from  customer where c_ph1 like ?  and br_id = ? and c_active =1 order by c_name asc  limit ?,?',array('siii',$request['value'].'%',$request['BranchId'],$request['start'],$request['limit']) ,true);					
					}elseif($request['type']==3){					
					$customer = $db->getMulipleData('select c_id as CustId, concat(c_ph2,"/",c_name) as Customer, c_ph2 as Mobile,c_Credit as HasCredit from  customer where c_ph2 like ?  and br_id = ? and c_active =1 order by c_name asc  limit ?,?',array('siii',$request['value'].'%',$request['BranchId'],$request['start'],$request['limit']) ,true);					
				}
				$arrAuth['success']=true;
				$arrAuth['msg'] = 'Customer List';	
				$arrAuth['Data']['Customer'] =$customer;												
				
				return $arrAuth;	
			}
			public function POST_customer($flag,$request){
				if (!array_key_exists('customer', $request) || !array_key_exists('bksession', $request) || !isset($request) ) {
					throw new \Exception('Missing Post parameters '  );	
				}
				if(!$this->validatebookingsession($request['apikey'],$request['bksession'])){
					throw new \Exception('Invalid Booking session '  );	
				}
				global $db;
				//file_put_contents('php://stderr', print_r(DSN,TRUE));
				$db = new \SqlDB(DSN);
				$cgodb = new \cgoSqlDB();
				/*Setting current  date default */
				$date = date_default_timezone_set('Asia/Kolkata');
				$dt = date("Y-m-d H:i:s");    
				
				
				$ts = microtime(true);
				$fileName = $ts . '.0.spl';
				/*create an instance of diskwriter*/
				//$dw = new \diskWriter($fileName, 0, true);
				
				$cgodb->begintransaction();
				$cid = $cgodb->getItemFromDB("SELECT IF (COALESCE(MAX(c_ID),0)=0,1,MAX(c_ID)+1) as id FROM  customer WHERE c_ID < 1110000001;  ",null,true);
				$cust = $request['customer'];
				file_put_contents('php://stderr', print_r($cust,TRUE));
				$cgodb->query("INSERT INTO  customer(c_ID,c_Name,c_Add1,c_Add2,c_Add3,c_City,c_District,c_State,c_Ph1,c_Ph2,c_Bank,p_ID,c_Email,c_Fax,c_CSTNo,c_KGSTno,c_Active,c_Credit,c_Creditlimit,ct_ID,br_Id,p_Name,TIN,taxable,c_CreatedOn,c_smsenabled) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",array("isssssssssssssssssssssssss",$cid,$cust['custname'],$cust['address'],"","","",$cust['distid'] ,$cust['stateid'],$cust['mobno'],"","","0","","","","","1","0","0.00","0",$cust['branchid'],"","","0",$dt ,"1"));				
				$data = array(
				"c_ID"=>$cid,
				"c_Name"=>$cust['custname'],
				"c_Add1"=>$cust['address'],
				"c_Add2"=>"",
				"c_Add3"=>"",
				"c_City"=>"",
				"c_District"=>$cust['distid'] ,
				"c_State"=>$cust['stateid'],
				"c_Ph1"=>$cust['mobno'],
				"c_Ph2"=>"",
				"c_Bank"=>"",
				"p_ID"=>"0",
				"c_Email"=>"",
				"c_Fax"=>"",
				"c_CSTNo"=>"",
				"c_KGSTno"=>"",
				"c_Active"=>"1",
				"c_Credit"=>"0",
				"c_Creditlimit"=>"0.00",
				"ct_ID"=>"0",
				"br_Id"=>$cust['branchid'],
				"p_Name"=>"",
				"TIN"=>"",
				"taxable"=>"0",
				"c_CreatedOn"=>$dt ,
				"c_smsenabled"=>"1");				
				//$cgodb->perform(DB_PREFIX . '1.customer', $data);
				//actionDW('customer', $data, $dw, 'insert');
				//$inFilePath = $dw->mkS3GzipFile(false,true,AWSDATAUPLOADBUCKET,AWSDATAUPLOADCONTENTTYPE);
				//$proc = array('S3Bucket' => AWSDATAUPLOADBUCKET, 'bid' => 0, 'filename' => $inFilePath, 'entry' => date("Y-m-d G:i:s", $ts));
				
				//$cgodb->query("INSERT INTO " . DB_PREFIX . "config.process_queue(S3Bucket,bid, filename,entry)values(?,?,?,?)",array("ssss",AWSDATAUPLOADBUCKET,'0',$inFilePath,date("Y-m-d G:i:s", $ts)));
				$cgodb->committransaction();	
				$nodb = new \cgoDynamiteDB();				
				$arrSession = array();
				$arrSession['PartitionKey']=array('col'=>'apikey','val'=>$request['apikey']);			
				$arrSession['SortKey']=array('col'=>'bksession','val'=>$request['bksession']);
				$arrSession['Data']=array();								
				array_push($arrSession['Data'],array('col'=>'consignee','val'=>$cid));
				array_push($arrSession['Data'],array('col'=>'isnewconsignee','val'=>1 ));				
				$nosession = $nodb->perform('BookingSession','update',$arrSession,$response);	
				if ($nosession){					
					$arrAuth['success']=true;
					$arrAuth['msg'] = 'Saved Customer for booking session';	
					$arrAuth['Data']['customer'] = null;													
					}else{
					$arrAuth['msg'] = 'Unable to save Customer';		
					$arrAuth['Data']['customer'] = null;						
				}
				return	$arrAuth;					
			}	
			public function GET_paymode($flag,$request){				
				$arrAuth = array();
				$db = new \cgoSqlDB();
				$paymode = $db->getMulipleData('select pm_ID as PmId, pm_Paymode from  paymode where pm_ID<4  order by pm_ID asc ',array() ,true);					
				
				$arrAuth['success']=true;
				$arrAuth['msg'] = 'Paymode List';	
				$arrAuth['Data']['Paymode'] =$paymode;												
				
				return $arrAuth;	
			}		
			public function GET_deliverymode($flag,$request){				
				$arrAuth = array();
				$db = new \cgoSqlDB();
				$delmode = $db->getMulipleData('select dlm_ID as DlmId,dlm_Delmode as DeliveryMode from  deliverymode where dlm_ID < 3 order by dlm_ID asc ',array() ,true);					
				
				$arrAuth['success']=true;
				$arrAuth['msg'] = 'Delivery Mode List';	
				$arrAuth['Data']['Deliverymode'] =$delmode;												
				
				return $arrAuth;	
			}
			public function GET_hasboxrate($flag,$request){	
				if (!array_key_exists('CompanyId', $request) || !array_key_exists('CustId', $request)  || !array_key_exists('SrBranchId', $request) || !array_key_exists('DsBranchId', $request) || !isset($request) ) {
					throw new \Exception('Missing GET parameters ');
				}	
				$arrAuth = array();
				$db = new \cgoSqlDB();
				$qry = ' SELECT Q_Id  FROM   quoations  WHERE cmp_ID= ?
				AND c_ID=? AND br_Id=? AND approvedtype =1  AND now() BETWEEN dateFrm AND dateTo AND approval=1 ORDER BY `count` DESC LIMIT 0,1';
				$result = $db->getFromDB($qry ,array('iii',$request['CompanyId'],$request['CustId'],$request['SrBranchId']) ,true);				
							
				if((int)$result['Q_Id']>0){					
					$minQuouteAmount = $db->getItemFromDB('SELECT  getQuoteAmuount(?,?,?,3,?) as  minQuoteAmt',array('iiii',$request['SrBranchId'],$request['DsBranchId'],$result['Q_Id'],1),true);					
					if($minQuouteAmount>0){						
						$arrAuth['Data']['HasBoxRate'] =true;					 
						$boxtype[0] = array("ID"=>1,"Name"=>"Small Box");
						$boxtype[1] = array("ID"=>2,"Name"=>"Medium Box");
						$boxtype[2] = array("ID"=>3,"Name"=>"Large Box");
						$boxtype[3] = array("ID"=>4,"Name"=>"Extra Large");
						$arrAuth['Data']['BoxType'] =$boxtype;
						}else{
						$arrAuth['Data']['HasBoxRate'] =false;
						$arrAuth['Data']['BoxType'] = array();
					}
					}else{
					$arrAuth['Data']['HasBoxRate'] =false;
					$arrAuth['Data']['BoxType'] = array();
				}
				$arrAuth['success']=true;
				$arrAuth['msg'] = 'Quotation Details';											
				
				return $arrAuth;	
			}
			public function GET_contenttype($flag,$request){	
				if (!array_key_exists('contenttype', $request) || !isset($request) ) {
					throw new \Exception('Missing GET parameters ');
				}
				$arrAuth = array();
				$db = new \cgoSqlDB();
				$contenttype = $db->getMulipleData('select ct_ID as CtID,ct_Name as CtName from  contenttype where ct_Active=1 and ct_name like ?  order by ct_Name asc ',array('s',$request['contenttype'].'%') ,true);					
				
				$arrAuth['success']=true;
				$arrAuth['msg'] = 'Content Type';	
				$arrAuth['Data']['ContentType'] =$contenttype;												
				
				return $arrAuth;	
			}
			public function GET_packingtype($flag,$request){	
				if (!array_key_exists('packingtype', $request) || !isset($request) ) {
					throw new \Exception('Missing GET parameters ');
				}
				$arrAuth = array();
				$db = new \cgoSqlDB();
				$packingtype = $db->getMulipleData('select pk_ID as PkID,pk_Name as PkName from  packingtype where pk_Active=1  and pk_Name like ? order by pk_Name asc ',array('s',$request['packingtype'].'%') ,true);							
				
				$arrAuth['success']=true;
				$arrAuth['msg'] = 'Packing Type';	
				$arrAuth['Data']['packingtype'] =$packingtype;												
				
				return $arrAuth;	
			}			
			private function validatebooking($books,&$string){
				if(intval($books['dbrid']) == 0){
					$string = "Invalid destination branch";
					return false;
				}
				if($books['pvtmark'] == ''){
					$string = "Invalid pvtmark";
					return false;
				}
				if(intval($books['delmode']) == 0){
					$string = "Invalid delivery mode";
					return false;
				}            
				if(intval($books['CompanyId']) == 0){
					$string = "Invalid Company";
					return false;
				}             
				if(intval($books['ConsgeId']) == 0){
					$string = "Invalid Consignee";
					return false;
				}             
				if(intval($books['paymode']) == 0){
					$string = "Invalid Paymode";
					return false;
				} 
				if(intval($books['ConsgrId']) == 0){
					$string = "Invalid Consignor";
					return false;
				} 
				if(intval($books['sbrid']) == 0){
					$string = "Invalid Source branch";
					return false;
				} 
				return true;
				
			}
			private function validateconsignment($consdets,&$string){
				$rowcount =0;
				foreach ($consdets as $value) {
					$rowcount++;
					if(floatval($value['vol']) == 0 && intval($value['boxtype'])==0){
						$string = "Invalid volume or box type. Row: " . $rowcount;
						return false;	
					}
					if(intval($value['packettypeid'])==0){
						$string = "Invalid packet type. Row: " . $rowcount;
						return false;	
					}
					if(intval($value['contenttypeid'])==0){
						$string = "Invalid content type. Row: " . $rowcount;
						return false;	
					}
					if(floatval($value['wt'])==0){
						$string = "Invalid weight. Row: " . $rowcount;
						return false;	
					}	
					if(floatval($value['pkts'])==0){
						$string = "Invalid weight. Row: " . $rowcount;
						return false;	
					}					
				}
				if($rowcount ==0){
					$string = "No consignment details found";
					return false;						
				}
				return true;
			}
			public function POST_createbooking($flag,$request){
				//|| !array_key_exists('slotime', $request) || !array_key_exists('bksession', $request)  || !array_key_exists('slotdate', $request)
				if (!array_key_exists('bookdets', $request)  || !array_key_exists('bksession', $request)  || !array_key_exists('consdets', $request)  || !isset($request) ) {
					throw new \Exception('Missing Post parameters '  );	
				}
				if(!$this->validatebookingsession($request['apikey'],$request['bksession'],$stage)){
					throw new \Exception('Invalid Booking session '  );	
				}				
				/*if(intval($stage) == 7){
					throw new \Exception('The booking is already completed.'  );	
					}elseif(intval($stage)<6){
					throw new \Exception('The booking has not received all the details, please fill all the details.'  );	
					}elseif(intval($stage)!=6){
					throw new \Exception('Invalid sequence of event for booking.'  );	
				}*/
				$valdate = date("YmdHis");				
				//$request["slotime"]=$valdate = date("YmdHis");
				//$request["slotdate"]=$valdate = date("YmdHis");
				$books =  json_decode($request['bookdets'],true);
				if ($this->validatebooking($books,$response)==false){
					file_put_contents('php://stderr', print_r($response,TRUE));
					file_put_contents('php://stderr', print_r($books,TRUE));
					$arrAuth =array();
					$arrAuth['msg'] = $response;		
					$arrAuth['Data']['refno'] = null;	
					return $arrAuth;
				}
				$cons =  json_decode($request['consdets'],true);
				if ($this->validateconsignment($cons,$response)==false){
					file_put_contents('php://stderr', print_r($response,TRUE));
					file_put_contents('php://stderr', print_r($cons,TRUE));
					$arrAuth =array();
					$arrAuth['msg'] = $response;		
					$arrAuth['Data']['refno'] = null;	
					return $arrAuth;					
				}
				$books['entryby']=$_SESSION["drvname"];
				$books['ipaddress']=$_SERVER['REMOTE_ADDR'];
				$nodb = new \cgoDynamiteDB();		
				$arrSession = array();
				$arrSession['PartitionKey']=array('col'=>'apikey','val'=>$request['apikey']);			
				$arrSession['SortKey']=array('col'=>'bksession','val'=>$request['bksession']);
				$arrSession['Data']=array();		
				array_push($arrSession['Data'],array('col'=>'bookdets','val'=>$books));	
				array_push($arrSession['Data'],array('col'=>'consdets','val'=>$cons));	
				//array_push($arrSession['Data'],array('col'=>'stage','val'=>3 ));				
				$nosession = $nodb->perform('BookingSession','update',$arrSession,$response);					
				//$ctr=685;
				while(true){				
					//$bkrefno = $ctr;		
					$util =  new Utils();
					$bkrefno =$util->getRandomRef();
					$arrSession = array();					
					$arrSession['Data']=array();	
					array_push($arrSession['Data'],array('col'=>'apikey','val'=>$request['apikey']));
					array_push($arrSession['Data'],array('col'=>'bksession','val'=>$request['bksession']));
					array_push($arrSession['Data'],array('col'=>'bkrefno','val'=>(string)$bkrefno));		
					array_push($arrSession['Data'],array('col'=>'IsProcessed','val'=>0));					
					array_push($arrSession['Data'],array('col'=>'dbid','val'=>(int)$_SESSION["loginid"]));								
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
			public function GET_todaysbooking($flag,$request){
				$arrAuth = array();
				$db = new \cgoSqlDB();
				$todaysbooking = $db->getMulipleData('select bk_no as bkno,bk_consignor as Consignor,bk_consignee as Consignee,bk_destbranch as Destination,bk_time as Time,bk_PktCount as PacketCount,bk_totwt as Weight, bk_NetAmt - bk_roundoff as NetAmt,bk_PvtMark as PvtMark, (select trs_TranStatus from transactionstatus where transactionstatus.trs_ID = inward_booking.trs_ID ) as Currentstatus from  inward_booking where bk_user=?  and bk_date=? and bk_SoftwareType=2 and bk_cancelled = 0 order by bk_time desc ',array('ss',$_SESSION["loginid"],date('Y-m-d')) ,true);							
				
				$arrAuth['success']=true;
				$arrAuth['msg'] = "Today's Booking";	
				$arrAuth['Data']['todaysbooking'] =$todaysbooking;												
				
				return $arrAuth;
			}
			public function GET_todaysconsignment($flag,$request){
				$arrAuth = array();
				$db = new \cgoSqlDB();
				$todaysbooking = $db->getMulipleData('select sum(if(bk_paymode="PAID",bk_netamt,0)) as CashInHand,sum(if(bk_paymode="PAID",1,0)) as Paid,sum(if(bk_paymode="TO PAY",1,0)) as ToPay,sum(if(bk_paymode="CREDIT",1,0)) as Credit   from  inward_booking where bk_user=?  and bk_date=? and bk_SoftwareType=2 and bk_cancelled = 0 order by bk_time desc ',array('ss',$_SESSION["loginid"],date('Y-m-d')) ,true);							
				
				$arrAuth['success']=true;
				$arrAuth['msg'] = "Today's Summary";	
				$arrAuth['Data']['todaysbooking'] =$todaysbooking;												
				
				return $arrAuth;
			}
		}		 
	}																											