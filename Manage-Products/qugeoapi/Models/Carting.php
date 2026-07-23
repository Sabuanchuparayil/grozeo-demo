<?php
    namespace Models;
    {
        class Carting extends ModelAbstract
        {	
            public function GET_logkeys($flag,$request){	

                $logs =array();			
                $vehicles['success']=true;	
                $vehicles['msg'] = 'Bucket and Credentials for logging';			
                $vehicles['Data']['logkeys'] = array('S3BUCKET'=>QUGEO_LOGS_UPLOAD_BUCKET,'UploadPrefix'=>date("Y/m/d/") . $request["apikey"] . "_" . date("His"). "_",'ACCESSKEY'=>QUGEO_S3_UPLOAD_ACCESS,'SECRETKEY'=>QUGEO_S3_UPLOAD_SECRET);						
                return $vehicles;			
            }
            public function GET_failedstatus($flag,$request){	
                $db = new \cgoSqlDB();
                if (!array_key_exists('ispickup', $request) || !isset($request) ) {
                        throw new \Exception('Missing GET parameters ');
                }			
                if($request['ispickup'] =='true'){
                        $failedstatus = $db->getMulipleData('select dls_ID as id,dls_DelStatus as name  from  qugeo_deliverystatus where dls_ID in (37,36,35)  order by dls_DelStatus asc ',array() ,true);			
                        }else{
                        $failedstatus = $db->getMulipleData('select dls_ID as id,dls_DelStatus as name  from  qugeo_deliverystatus where dls_ID in (10,11,12,13,14)  order by dls_DelStatus asc ',array() ,true);			
                }
                $failedstatuses =array();
                if($failedstatus !== false){				
                        $failedstatuses['success']=true;	
                        $failedstatuses['msg'] = 'List of Failed status types';			
                        $failedstatuses['Data']['failedstatus'] = $failedstatus;						
                        }else{
                        $failedstatuses['msg'] = 'Did not Failed status types';							
                        $failedstatuses['Data']['failedstatus'] = array();	
                }
                return $failedstatuses;			
            }			
            private function sendOTP($mobile,$otp,$name,$ispickup,$bkno){
                //$sms = new \SoftSMS();
                if($ispickup==1){
                        $str = "Arriving for pickup of " . $bkno . ". Dear " . $name . ", an agent will arrive at your location today to pickup your consignment. Please provide the OTP " . $otp . " to the collection boy on completion of pickup" ;
                        }else{
                            //1607100000000130928
                        $str = "Out for Delivery of " . $bkno . ". Dear " . $name . ", your order is arriving, please provide the OTP " . $otp . " to the delivery boy on completion of delivery" ;
                        $db = new \SqlDB(DSN);
                        \sms::send($mobile,$str,$db,"");
                }
                //$smsresponse = $sms->sendSMS( $mobile,$str);

                //textLocalsms(TextLocalSMS_CREDENTIALS,array($mobile),TextLocalSMS_SENDER,$str);
                //$email = new \cgoAWSSES();
                //$email->send_mail($mobile . '@yopmail.com',$str);

            }
            public function GET_vehicletypelist($flag,$request){	
                $db = new \cgoSqlDB();
                $vehiclelist = $db->getMulipleData('select distinct vhty_id as id,vhty_name as name  from  qugeo_vehicletype  where vhty_Active = 1 order by vhty_name asc ',array() ,true);			
                $vehicles =array();
                if($vehiclelist !== false){				
                        $vehicles['success']=true;	
                        $vehicles['msg'] = 'List of vehicles types';			
                        $vehicles['Data']['vehicletypes'] = $vehiclelist;						
                        }else{
                        $vehicles['msg'] = 'Did not find vehicle type list';							
                        $vehicles['Data']['vehicletypes'] = array();	
                }
                return $vehicles;			
            }
            public function GET_lastvehicleused($flag,$request){		
                if (!array_key_exists('prevlogoutinfo', $request) || !isset($request) ) {
                        throw new \Exception('Missing GET parameters ');
                }	
                if($request['prevlogoutinfo']==''){
                        $arrAPI=array();
                        $arrAPI['PartitionKey'] = array('col'=>'usertype','val'=>$_SESSION["usertype"],'oper'=>'=');
                        $arrAPI['SortKey']=array('col'=>'id','val'=>$_SESSION["loginid"],'oper'=>'=');				
                        $arrAPI['IndexName'] = 'usertype-id-index';
                        $arrAPI['queryAttributes']=array('apikey');
                        $arrAPI['Condition']=array();
                        array_push($arrAPI['Condition'],array('col'=>'HasLoggedOut','val'=>'0','oper'=>'=' ));	
                        //array_push($arrAPI['Condition'],array('col'=>'HasOrders','val'=>'1','oper'=>'=' ));	
                        array_push($arrAPI['Condition'],array('col'=>'apikey','val'=>$request["apikey"],'oper'=>"<>" ));
                        $nodb = new \cgoDynamiteDB();	
                        $rsno = $nodb->query('APIHistory',$arrAPI,'query');	

                        if(isset($rsno) && count($rsno) > 0){
                                $vehicles =array();
                                $vehicles['msg'] = 'Enter the previous session details';			
                                $vehicles['Data']['vehiclesused'] = array();		
                                $vehicles['Data']['cleanlogout'] = false; 	
                                return $vehicles;
                        }
                        }else{
                        $arrAPI=array();
                        $arrAPI['PartitionKey'] = array('col'=>'usertype','val'=>$_SESSION["usertype"],'oper'=>'=');
                        $arrAPI['SortKey']=array('col'=>'id','val'=>$_SESSION["loginid"],'oper'=>'=');				
                        $arrAPI['IndexName'] = 'usertype-id-index';
                        $arrAPI['queryAttributes']=array('apikey');
                        $arrAPI['Condition']=array();
                        array_push($arrAPI['Condition'],array('col'=>'HasLoggedOut','val'=>0,'oper'=>'=' ));	
                        array_push($arrAPI['Condition'],array('col'=>'apikey','val'=>$request["apikey"],'oper'=>'<>' ));
                        $nodb = new \cgoDynamiteDB();	
                        $rsno = $nodb->query('APIHistory',$arrAPI,'query');
                        if(isset($rsno) && count($rsno) > 0 ){
                                foreach($rsno as $value){						
                                        $arrUpdate=array();
                                        $arrUpdate['PartitionKey']=array('col'=>'apikey','val'=>$value['apikey'],'oper'=>'=');
                                        $arrUpdate['Data']=array();
                                        array_push($arrUpdate['Data'],array('col'=>'HasLoggedOut','val'=>1));
                                        $nors = $nodb->perform('APIHistory','update',$arrUpdate,$response);

                                        //Delete from QugeoLiveVehicles
                                        $arrUpdate=array();
                                        $arrUpdate['PartitionKey']=array('col'=>'apikey','val'=>$value['apikey']);
                                        $arrUpdate['Data']=array();
                                        array_push($arrUpdate['Data'],array('col'=>'Is_Live','val'=>0 ));
                                        array_push($arrUpdate['Data'],array('col'=>'LoggedOutAt','val'=>(string)date("YmdHis")));
                                        array_push($arrUpdate['Data'],array('col'=>'IsCleanLogout','val'=>2 ));						
                                        $nors = $nodb->perform('QugeoLiveVehicles','update',$arrUpdate,$response);
                                }
                        }		
                }
                $db = new \cgoSqlDB();
                $lastvehicles = $db->getMulipleData('select  v_ID as id,v_no as regno, qugeo_vehicletype.vhty_id as v_type,vhty_name  from  qugeo_drivervehicle inner join qugeo_vehicletype on qugeo_vehicletype.vhty_id = qugeo_drivervehicle.vhty_id where d_ID =? and v_active =1 order by dv_id desc limit 3',array('i',$_SESSION["loginid"]) ,true);			
                $vehicles =array();
                if($lastvehicles !== false){				
                        $vehicles['success']=true;	
                        $vehicles['msg'] = 'Please validate your details';			
                        $vehicles['Data']['vehiclesused'] = $lastvehicles;	
                        $vehicles['Data']['cleanlogout'] = true;		
                        }else{
                        $vehicles['msg'] = 'Did not find any history of vehicles used';							
                        $vehicles['Data']['vehiclesused'] = array();	
                        $vehicles['Data']['cleanlogout'] = true;				
                }
                return $vehicles;			
            }
            public function GET_ownvehiclelist($flag,$request){		
                if (!array_key_exists('allotedvehicle', $request) || !isset($request) ) {
                        throw new \Exception('Missing GET parameters ');
                }	
                $db = new \cgoSqlDB();
                $availablevehicles = $db->getMulipleData('select v_ID as id,v_no as regno,v_Type as v_type  from  qugeo_vehicle  where v_No like ? and v_active =1 order by 2 ',array('s','%'.$request["allotedvehicle"].'%') ,true);			
                $vehicles =array();
                if($availablevehicles !== false){				
                        $vehicles['success']=true;	
                        $vehicles['msg'] = 'List of matching vehicles found';			
                        $vehicles['Data']['availablevehicles'] = $availablevehicles;			
                        }else{
                        $vehicles['msg'] = 'Did not any matching vehicle number';							
                        $vehicles['Data']['availablevehicles'] =  array() ;			
                }
                return $vehicles;			
            }
            public function POST_otprequest($flag,$request){
                if (!array_key_exists('orderid', $request)  || !array_key_exists('mobno', $request)  || !array_key_exists('geocoords', $request)  || !isset($request) ) {
                        throw new \Exception('Missing get parameters ');
                }
                $util =  new Utils();
                $extrainfo = array("event"=>"order otp re-request",'order'=>$request['orderid']);
                $util->LogGeoCordinates($request['geocoords'],$_SESSION["usertype"],$_SESSION["loginid"],$request["apikey"],$extrainfo);  	
                $nodb = new \cgoDynamiteDB();	
                $arrOrder['PartitionKey'] = array('col'=>'orderid','val'=>$request['orderid'],'oper'=>'=');		
                $arrOrder['getAttributes']=array('mobile','bkno','name','IsPickup','OTP');
                $nors = $nodb->query('QugeoOrderDetails',$arrOrder,'getItem');	
                if($nors!=false ) {
                        $this->sendOTP($request['mobno'],$nors['OTP'],$nors['name'],$nors['IsPickup'],$nors['bkno']);
                        $arrSession['success']=true;
                        $arrSession['msg'] = 'OTP send to ' . $request['mobno'] ;		
                        }else{
                        $arrSession['msg'] = 'OTP request failed';				
                }

                $arrSession['Data']['otprequest'] = array();
                return $arrSession;								
            }
            public function POST_vehicleselected($flag,$request){		
                if (!array_key_exists('ishired', $request)  || !array_key_exists('vehicleid', $request)  || !array_key_exists('vehicleregno', $request)    || !array_key_exists('vehicletype', $request)  || !array_key_exists('geocoords', $request)  || !isset($request) ) {
                        throw new \Exception('Missing POST parameters ');
                }
                if(intval($request['vehicletype'])==0){
                        throw new \Exception('Invalid vehicle type');
                }
                if(trim($request['vehicleregno'])==""){
                        throw new \Exception('Invalid Vehicle Number');
                }
                $util =  new Utils();
                $extrainfo = array("event"=>"vehicleselected");
                $util->LogGeoCordinates($request['geocoords'],$_SESSION["usertype"],$_SESSION["loginid"],$request["apikey"],$extrainfo);

                //Update APISession table with vehicle used
                $arrSession =array();
                $arrSession['Data']=array();
                $arrSession['PartitionKey'] = array('col'=>'usertype','val'=>(int)$_SESSION["usertype"]);
                $arrSession['SortKey']=array('col'=>'id','val'=>(string)$_SESSION["loginid"]);				
                array_push($arrSession['Data'],array('col'=>'extrainfo','val'=>array("v_id"=>$request["vehicleid"],"v_no"=>$request["vehicleregno"])));
                $nodb = new \cgoDynamiteDB();	
                $nosession = $nodb->perform('APISession','update',$arrSession,$response);
                if(!$nosession) {
                        $arrSession =array();
                        $arrSession['msg'] = 'Unable to update API Session';			
                        $arrSession['Data']['dashboarddetails'] = null;				
                        return $arrSession;
                }

                //Update APISession History table with vehicle used
                $arrSession =array();
                $arrSession['Data']=array();
                $arrSession['PartitionKey']=array('col'=>'apikey','val'=>$request["apikey"]);
                array_push($arrSession['Data'],array('col'=>'extrainfo','val'=>array("v_id"=>$request["vehicleid"],"v_no"=>$request["vehicleregno"])));
                $nosession = $nodb->perform('APIHistory','update',$arrSession,$response);
                if(!$nosession) {
                        $arrSession =array();
                        $arrSession['msg'] = 'Unable to update API Session Archive';			
                        $arrSession['Data']['dashboarddetails'] = null;				
                        return $arrSession;
                }

                //Insert into Branch Live Vehicles
                $arrSession =array();
                $arrSession['Data']=array();
                $valdate = date("Ymd");
                $valdatetime = date("YmdHis");
                $request["geocoords"]=json_decode($request["geocoords"]);
                $db = new \cgoSqlDB();
                $driverdetails = $db->getFromDB('select d_licenceexpairy as license,d_awssnsarn as awssnsarn,d_HomeLati,d_HomeLong,d_Rating,d_Name,br_id,d_Ph1,d_DeliveryRange,gcmregstid,d_Ph1 from  qugeo_driver  where  d_ID = ? ',array('i',$_SESSION["loginid"]) ,true);	
                $vehicledetails = $db->getFromDB('select vhty_MaxCapacity as capacity, 0 as Rate,vhty_name as name,vhty_Icon from  qugeo_vehicletype  where  vhty_id = ? ',array('i',$request['vehicletype']) ,true);	
                array_push($arrSession['Data'],array('col'=>'apikey','val'=>(string)$request["apikey"] ));			
                array_push($arrSession['Data'],array('col'=>'createddatetime','val'=>(int)$valdatetime ));
                array_push($arrSession['Data'],array('col'=>'createddate','val'=>(int)$valdate ));
                array_push($arrSession['Data'],array('col'=>'LocationUpdateddatetime','val'=>(int)$valdatetime ));			
                array_push($arrSession['Data'],array('col'=>'Latitude','val'=>(float)$request["geocoords"]->details[0]->latitude ));
                array_push($arrSession['Data'],array('col'=>'Longitude','val'=>(float)$request["geocoords"]->details[0]->longitude ));
                array_push($arrSession['Data'],array('col'=>'v_id','val'=>(string)$request["vehicleid"] ));
                array_push($arrSession['Data'],array('col'=>'v_no','val'=>(string)$request["vehicleregno"] ));
                array_push($arrSession['Data'],array('col'=>'Is_Live','val'=>1 ));
                array_push($arrSession['Data'],array('col'=>'AWS_SNS_ARN','val'=>(string)$driverdetails['awssnsarn'] ));
                array_push($arrSession['Data'],array('col'=>'FCM_ID','val'=>(string)$driverdetails['gcmregstid'] ));
                array_push($arrSession['Data'],array('col'=>'DriverId','val'=>(int)$_SESSION["loginid"] ));
                array_push($arrSession['Data'],array('col'=>'DriverBranchId','val'=>(int)$driverdetails['br_id'] ));
                array_push($arrSession['Data'],array('col'=>'DriverName','val'=>(string)$driverdetails['d_Name'] ));
                array_push($arrSession['Data'],array('col'=>'DriverPhone','val'=>(string)$driverdetails['d_Ph1'] ));
                array_push($arrSession['Data'],array('col'=>'v_type','val'=>(int)$request['vehicletype'] ));
                array_push($arrSession['Data'],array('col'=>'v_capacity','val'=>(float)$vehicledetails['capacity'] ));
                array_push($arrSession['Data'],array('col'=>'v_typename','val'=>(string)$vehicledetails['name'] ));
                array_push($arrSession['Data'],array('col'=>'v_MapIcon','val'=>(string)$vehicledetails['vhty_Icon'] ));
                array_push($arrSession['Data'],array('col'=>'CurrentLoadedWeight','val'=>0 ));
                array_push($arrSession['Data'],array('col'=>'CurrentLoadedVolume','val'=>0 ));
                array_push($arrSession['Data'],array('col'=>'AssignedLoadedWeight','val'=>0 ));
                array_push($arrSession['Data'],array('col'=>'AssignedLoadedVolume','val'=>0 ));			
                array_push($arrSession['Data'],array('col'=>'RatePerKm','val'=>(float)$vehicledetails['Rate']  ));
                array_push($arrSession['Data'],array('col'=>'Home_Latitude','val'=>(float)$driverdetails['d_HomeLati'] ));
                array_push($arrSession['Data'],array('col'=>'Home_Longitude','val'=>(float)$driverdetails['d_HomeLong'] ));
                array_push($arrSession['Data'],array('col'=>'Rating','val'=>(string)$driverdetails['d_Rating'] ));
                array_push($arrSession['Data'],array('col'=>'mobno','val'=>(string)$driverdetails['d_Ph1'] ));
                array_push($arrSession['Data'],array('col'=>'ReportingBranch','val'=>(int)$driverdetails['br_id'] ));
                array_push($arrSession['Data'],array('col'=>'DeliveryRange','val'=>(int)$driverdetails['d_DeliveryRange'] ));
                array_push($arrSession['Data'],array('col'=>'MarkedNextBkId','val'=>0));	
                array_push($arrSession['Data'],array('col'=>'MarkedNextBrId','val'=>0));	
                array_push($arrSession['Data'],array('col'=>'IsEngaged','val'=>0));			
                array_push($arrSession['Data'],array('col'=>'OnJobCompletionLatitude','val'=>0));
                array_push($arrSession['Data'],array('col'=>'OnJobCompletionLongitude','val'=>0));
                $LiveVehicles = $nodb->perform('QugeoLiveVehicles','insert',$arrSession,$response);
                $LiveVehiclesHistory = $nodb->perform('QugeoLiveVehiclesHistory','insert',$arrSession,$response);
                $tmparr = $arrSession;
                $arrSession =array();
                if(!$LiveVehicles  ) {
                        $arrSession['msg'] = 'Unable to Queue to live vehicles';			
                        $arrSession['Data']['dashboarddetails'] = $response;
                        print_r($tmparr);	
                        }else{
                        $response = array();
                        array_push($response,array('title'=>'Your license is valid till','value'=>$driverdetails['license'] ));				
                        if($request["vehicleid"]>0){
                                $availablevehicles = $db->getFromDB('select dt_insurance as insurance,dt_fitness as fitness  from  qugeo_vehicle  where v_ID = ? ',array('i',$request["vehicleid"]) ,true);	
                                array_push($response,array('title'=>'Vehicle Insurance expires on ','value'=>$availablevehicles['insurance'] ));
                                array_push($response,array('title'=>'Vehicle Fitness is valid till ','value'=>$availablevehicles['fitness'] ));
                        }
                        $db->begintransaction();
                        $db->query('INSERT INTO  qugeo_drivervehicle(d_ID,v_ID,v_No,lastused,vhty_id) VALUES(?,?,?,now(),?) ',array('iisi',$_SESSION["loginid"],$request["vehicleid"],$request["vehicleregno"],$request['vehicletype']));
                        $db->committransaction();

                        $arrSession['success']=true;
                        $arrSession['msg'] = 'Dasboard Details';			
                        $arrSession['Data']['vehicle'] = $response;					
                }				
                return $arrSession;			
            }
            public function POST_polledorder($flag,$request){
                file_put_contents('php://stderr', "POST_polledorder CALLED " . $request["orderid"] . " \n ");
                if (!array_key_exists('orderid', $request)  || !array_key_exists('hasaccepted', $request)  || !array_key_exists('delivertobranch', $request)    || !array_key_exists('msgid', $request)  || !array_key_exists('geocoords', $request)  || !isset($request) ) {
                        throw new \Exception('Missing POST parameters ');
                }
                if($request['orderid']==''){
                        throw new \Exception('Invalid order id');
                }
                if($request['msgid']==''){
                        throw new \Exception('Invalid Message id');
                }
                /*$data = array(
                        'istriprerouted' => true,
                        'mapdetails' => array(
                        'latitude' => 8.5207294999999998,
                        'longitude' => 76.942287300000004,
                        'zoomlevel' => 10,
                        'locationicon' => 'https://maps.google.com/mapfiles/ms/icons/yellow-dot.png' ),
                        'nextorderdetails' => array(
                        'ispickup' => true,
                        'orderid' => '09619a56490274a4f66de203717d82401cb47d02',
                        'orderno' => 'ABG125F',
                        'Customer' => 'Praseed',
                        'address' => '10 D Peach SFS Attikuzhi',
                        'contph' => '9123456780',
                        'cashtobecollected' => 800,
                        'noofboxes' => 10,
                        'weight' => '109Kg',
                        ));	
                        $arrSession =array();
                        $arrSession['success']=true;
                        $arrSession['msg'] = 'Dasboard Details';			
                        $arrSession['Data']['orderdetails'] = $data;		
                return $arrSession;*/
                $util =  new Utils();
                $extrainfo = array("event"=>"pollreponse","responsedetails"=>array("orderid"=>$request["orderid"],"hasaccepted"=>$request['hasaccepted'],"msgid"=>$request['msgid']));
                $util->LogGeoCordinates($request['geocoords'],$_SESSION["usertype"],$_SESSION["loginid"],$request["apikey"],$extrainfo);  
                $pollresp = new QugeoOrderPoller();
                $arrSession =array();
                $nextorder = array();
                
                
                $nextorder['istriprerouted']=false;
                $nextorder['mapdetails']=array();
                $nextorder['nextorderdetails']=array();
                if ($pollresp->IsPollClosed($request['msgid'],$acceptedapikey)==true){
                        //if($acceptedapikey != $request["apikey"]){
                                if($request['hasaccepted']=='true'){	
                                        $arrSession['success']=false;
                                        $arrSession['msg'] = 'Your poll response has been timed out';
                                        $arrSession['Data']['vehicle'] = $nextorder;	
                                        return $arrSession;	
                                        }else{
                                        $arrSession['success']=false;
                                        $arrSession['msg'] = 'Your poll response has been timed out';
                                        $arrSession['Data']['vehicle'] = $nextorder;						
                                        return $arrSession;						
                                }
                        //}
                }
                $pollclosed = $pollresp->PollResponse($request['msgid'],($request['hasaccepted']=='true'?1:2),($request['delivertobranch']=='true'?true:false),$acceptedorder);


                $orderhandler = new QugeoOrderHandler();
                $udpatedorder = $orderhandler->UpdateOrderOnPoll(($request['hasaccepted']=='true'?1:2),$request['orderid'],$request['apikey'],($request['delivertobranch']=='true'?true:false),$orderdetails);
                if($pollclosed==true && $acceptedorder && $udpatedorder){	
                        if($udpatedorder){
                                $assigned = $orderhandler->AssignOrderToQugeoDriver($request['orderid'],$request['apikey'],$orderdetails,$nextorder,$isnewroute);	
                                //echo "doneeee";
                                if($isnewroute){
                                        $arrSession['msg'] = 'New route reworked';
                                        }else{
                                        $arrSession['msg'] = 'No change in route';
                                }
                                $arrSession['success']=true;						
                                }else{
                                $arrSession['msg'] = 'Unable to assign order';
                                throw new \Exception('Unable to assign order');
                        }
                        }else{
                        $arrSession['success']=true;	
                        $arrSession['msg'] = 'No changes in the route';
                }	
                file_put_contents('php://stderr', print_r($nextorder,TRUE));
                $arrSession['Data']['vehicle'] = $nextorder;	
                
                return $arrSession;	
            }
            public function POST_milestone($flag,$request){		
                if (!array_key_exists('orderid', $request)   || !array_key_exists('milestone', $request)   || !array_key_exists('geocoords', $request)  || !isset($request) ) {
                        throw new \Exception('Missing POST parameters ');
                }
                $arrSession =array();	
                file_put_contents('php://stderr', print_r($request, TRUE));
                if($request['milestone']==500){
                $nodb = new \cgoDynamiteDB();	
                $arrOrder['PartitionKey'] = array('col'=>'orderid','val'=>$request['orderid'],'oper'=>'=');		
                $arrOrder['getAttributes']=array('pickupmobile','quor_RefNo','pickupname','deliveryname','deliverymobile','IsPickup','pickupOTP','IsMilestoneLock','deliveryOTP');
                $nors = $nodb->query('QugeoOrderDetails',$arrOrder,'getItem');	
                file_put_contents('php://stderr', print_r($nors, TRUE));
                if($nors!=false ) {
                        if($nors['IsMilestoneLock']=='0' ) {	
                                if($nors['IsPickup'] !='1'){
                                        //Your {#var#} Order No.{#var#} is arriving to you soon. Please provide the OTP {#var#} to our delivery partner on request.
                                    //1607100000000004818
                                $str = "Your " . PROJECT_NAME . " Order No.". $nors['quor_RefNo'] . " is arriving to you soon. Please provide the OTP " . $nors['deliveryOTP'] ." to our delivery partner  on request.";
                                $db = new \SqlDB(DSN);
                                \sms::send($nors['deliverymobile'],$str,$db,"");	
                                }
                                $arrOrder=array();
                                $arrOrder['PartitionKey'] = array('col'=>'apikey','val'=>$request['apikey'],'oper'=>'=');	
                                $arrOrder['SortKey']=array('col'=>'orderid','val'=>$request['orderid'],'oper'=>'=');
                                $arrOrder['Data']=array();
                                array_push($arrOrder['Data'],array('col'=>'IsMilestoneLock','val'=>1));								
                                $NewOrder = $nodb->perform('QugeoLiveVehicleOrders','update',$arrOrder,$response);	
                        }
                        $arrUpdate = array();
                        $arrUpdate['PartitionKey']=array('col'=>'orderid','val'=>$request['orderid']);				
                        $arrUpdate['Data']=array();
                        array_push($arrUpdate['Data'],array('col'=>'IsMilestoneLock','val'=>1));
                        array_push($arrUpdate['Data'],array('col'=>'MilestoneCovered','val'=>(int)$request['milestone']));
                        $uprs = $nodb->perform('QugeoOrderDetails','update',$arrUpdate,$response);	
                        $arrSession['success']=true;
                        $arrSession['msg'] = 'Milestone action done';	
                        }else{
                        $arrSession['msg'] = 'Milestone action error';				
                }
                }else{
                $arrSession['success']=true;
                $arrSession['msg'] = 'Milestone action completed';
                }	



                $arrSession['Data']['milestone'] = array();
                return $arrSession;	
            }	
            public function POST_geolocation($flag,$request){		
                if ( !array_key_exists('geocoords', $request)  || !isset($request) ) {
                        throw new \Exception('Missing POST parameters ');
                }
                $util =  new Utils();
                $extrainfo = array("event"=>"locationupdate");
                $util->LogGeoCordinates($request['geocoords'],$_SESSION["usertype"],$_SESSION["loginid"],$request["apikey"],$extrainfo,true);  	
                $arrSession =array();	
                $arrSession['success']=true;
                $arrSession['msg'] = 'Location Updated';
                $arrSession['Data']['location'] = array();
                return $arrSession;	
            }	
            public function GET_consignment($flag,$request){	
                if (!array_key_exists('orderid', $request)  || !array_key_exists('otp', $request)  || !array_key_exists('geocoords', $request)  || !isset($request) ) {
                        throw new \Exception('Missing POST parameters ');
                }
                $util =  new Utils();
                $extrainfo = array("event"=>"GetOrderDetails",'order'=>$request['orderid']);
                $util->LogGeoCordinates($request['geocoords'],$_SESSION["usertype"],$_SESSION["loginid"],$request["apikey"],$extrainfo); 
                $nodb = new \cgoDynamiteDB();	
                $arrOrder['PartitionKey'] = array('col'=>'orderid','val'=>$request['orderid'],'oper'=>'=');		
                $arrOrder['getAttributes']=array('OTP','GoodsWorth','pktcount','ContentTypeId','PackingTypeId','netamt','paymode','Consignment');
                $nors = $nodb->query('QugeoOrderDetails',$arrOrder,'getItem');	
                $response =array();
                $arrSession =array();
                if($nors!=false ) {
                        if($request['otp']==$nors['OTP']){
                                $db = new \cgoSqlDB();
                                $contenttype = $db->getItemFromDB("select ct_Name from  contenttype where ct_ID = " . $nors['ContentTypeId'],array(),true);
                                $response = array( "goodsworth" => $nors['GoodsWorth'],"totalcount"=>$nors['pktcount'],"contenttypeid"=> $nors['ContentTypeId'],"contenttype"=> $contenttype,"packingtypeid"=> $nors['PackingTypeId'],"amount"=>  $nors['netamt'],"cashat"=>($nors['paymode']=='PAID'?1:0),"details"=>$nors['Consignment']);   
                                $arrSession['success']=true;
                                $arrSession['msg'] = 'Consignments';
                                }else{
                                $arrSession['msg'] = 'Invalid OTP';	
                        }
                        }else{
                        $arrSession['msg'] = 'Get consignment action error';				
                }
                $arrSession['Data']['consignment'] = $response;	
                return $arrSession;			
            }
            public function PUT_consignment($flag,$request){
                if (!array_key_exists('orderid', $request)  || !array_key_exists('consignment', $request)  || !array_key_exists('geocoords', $request)  || !isset($request) ) {
                        throw new \Exception('Missing POST parameters ');
                }	
                $cons = json_decode($request['consignment'],true);
                foreach($cons['details'] as $key => $value) {
                        $cons['details'][$key]['contenttypeid'] = $cons['contenttypeid'];
                        $cons['details'][$key]['packingtypeid'] = $cons['packingtypeid'];
                }
                $arrSession =array();
                $util =  new Utils();
                $isvalid  = $util->IsValidConsignment($cons,$str);
                if(!$isvalid){
                        $arrSession['msg'] = $str;	
                        $arrSession['Data']['charges'] = array();	
                        return	$arrSession;	
                }			
                $nodb = new \cgoDynamiteDB();	
                $arrOrder =array();
                $arrOrder['PartitionKey'] = array('col'=>'orderid','val'=>$request['orderid'],'oper'=>'=');		
                $arrOrder['getAttributes']=array('IsPickup','HasDirectDeliveryPickUp','DstLat','DstLong','SrcLat','SrcLong','SrcLocId','DstLocId','SrcLocName','DstLocName','netamt','ConsignorID','ConsigneeID','SrcRemoteLati','SrcRemoteLong','DstRemoteLati','DstRemoteLong','SrcBrLat','SrcBrLong','DstBrLat','DstBrLong','SrcBranch','DestBranch','TotalDistKM','SrcBrId','DstBrId','Taxable');
                $nors = $nodb->query('QugeoOrderDetails',$arrOrder,'getItem');	

                if(count($nors) > 0 ){
                        if($nors['IsPickup']==1){
                                $HasDetails = array();
                                $HasDetails['pickup_addresses'] = array('SrcBrName'=>$nors['SrcBranch'],'SrcBrId'=>$nors['SrcBrId'],'locationid'=>$nors['SrcLocId'],'location'=>array('location'=>$nors['SrcLocName'],'lati'=>$nors['SrcRemoteLati'],'long'=>$nors['SrcRemoteLong']),'SrcBrLat'=>$nors['SrcBrLat'],'SrcBrLong'=>$nors['SrcBrLong']);
                                if($nors['SrcLat']!=$nors['SrcRemoteLati'] || $nors['SrcLong']!=$nors['SrcRemoteLong'] ){
                                        $HasDetails['pickup_addresses']['actualcoords'] = array('latitude'=>$nors['SrcLat'],'longitude'=>$nors['SrcLong']);	
                                }
                                $HasDetails['delivery_addresses'] = array('DstBrName'=>$nors['DestBranch'],'DstBrId'=>$nors['DstBrId'],'locationid'=>$nors['DstLocId'],'location'=>array('location'=>$nors['DstLocName'],'lati'=>$nors['DstRemoteLati'],'long'=>$nors['DstRemoteLong']),'DstBrLat'=>$nors['DstBrLat'],'DstBrLong'=>$nors['DstBrLong']);
                                if($nors['DstLat']!=$nors['DstRemoteLati'] || $nors['DstLong']!=$nors['DstRemoteLong'] ){
                                        $HasDetails['delivery_addresses']['actualcoords'] = array('latitude'=>$nors['DstLat'],'longitude'=>$nors['DstLong']);	
                                }					
                                $HasDetails['consignment'] = json_decode($request['consignment'],true);
                                $HasDetails['ConsignorId'] = $nors['ConsignorID'];
                                $HasDetails['ConsigneeId'] = $nors['ConsigneeID'];
                                $HasDetails['TotalDistKM'] = $nors['TotalDistKM'];
                                $HasDetails['Taxable'] = $nors['Taxable'];
                                $chrgs = new Charges('getcharges');
                                $params = $chrgs->getcharges($flag,$request,$HasDetails);
                                $arrCharges =array();	
                                $arrCharges['chargeweight']=$params['TotalChargWt'];
                                $arrCharges['packets']=$params['TotalPkts'];
                                $arrCharges['transcharges']=$params['TotalFreightAmt'];
                                $arrCharges['delicharges']= $params['delicharges'];
                                $arrCharges['taxes']=$params['taxamt'];		
                                $arrCharges['total']=$params['netamt']-$params['roundoff'];
                                if($arrCharges['total']>$nors['netamt']){
                                        $arrCharges['hasnewcharges']=true;
                                        $arrCharges['additionalamt']=$arrCharges['total']-$nors['netamt'];								
                                        }else{
                                        $arrCharges['hasnewcharges']=false;		
                                        $arrCharges['additionalamt']=0;						
                                }
                                $arrUpdate= array();
                                $arrUpdate['PartitionKey']=array('col'=>'orderid','val'=>$request['orderid']);
                                $arrUpdate['Data']=array();
                                array_push($arrUpdate['Data'],array('col'=>'NewConsignmentDetails','val'=>$HasDetails['consignment']));
                                array_push($arrUpdate['Data'],array('col'=>'NewChargeDetails','val'=>$params));
                                array_push($arrUpdate['Data'],array('col'=>'HasReCalculatedCharges','val'=>(int)($arrCharges['hasnewcharges']==true?1:0)));
                                array_push($arrUpdate['Data'],array('col'=>'ReCalculatedCharges','val'=>(float)$arrCharges['additionalamt']));					
                                $nors = $nodb->perform('QugeoOrderDetails','update',$arrUpdate,$response);
                                if($nors!=false){
                                        $arrSession['success']=true;
                                        $arrSession['msg'] = 'Charges';
                                        $arrSession['Data']['charges'] = $arrCharges;		
                                }
                                return $arrSession;
                                }else{
                                throw new \Exception('Delivery not implemented');						
                        }
                        }else{
                        print_r($arrOrder);
                        throw new \Exception('Missing required charges data'  );	
                }			
            }	
            public function POST_concludeorder($flag,$request){		 
                if (!array_key_exists('orderid', $request)  || !array_key_exists('failed', $request)  || !array_key_exists('failedreasonid', $request) || !array_key_exists('return_items', $request) || !array_key_exists('confirmationdetails', $request) || !array_key_exists('geocoords', $request)|| !isset($request) ) {
                        throw new \Exception('Missing POST parameters ');
                }	
                file_put_contents('php://stderr', "CONCLUDE CALLED " . $request["orderid"] . " \n ");
                file_put_contents('php://stderr', print_r($request,TRUE));
                $nodb = new \cgoDynamiteDB();	
                $db = new \cgoSqlDB();
                $arrOrder = array();
                $util =  new Utils();
                $extrainfo = array("event"=>"conclude","requestdetails"=>array("orderid"=>$request["orderid"]));
                $util->LogGeoCordinates($request['geocoords'],$_SESSION["usertype"],$_SESSION["loginid"],$request["apikey"],$extrainfo);  
                $arrOrder['PartitionKey'] = array('col'=>'orderid','val'=>$request['orderid'],'oper'=>'=');		
                $arrOrder['getAttributes']=array('IsPickup','HasReCalculatedCharges','ReCalculatedCharges','quor_id','HasDirectDeliveryPickUp','totwt','totvol','AcceptedAsDirectDelivery','HandlingBranch','quor_RefNo','deliverymobile');
                $nors = $nodb->query('QugeoOrderDetails',$arrOrder,'getItem');

                $totwt = floatval($nors['totwt']);
                $totvol =  floatval($nors['totvol']);

                $arrUpdate = array();
                $arrUpdate['PartitionKey'] = array('col'=>'orderid','val'=>$request['orderid'],'oper'=>'=');		
                $arrUpdate['Data']=array();		
                $valdatetime = date("YmdHis");
                array_push($arrUpdate['Data'],array('col'=>'IsClosed','val'=>1));	
                array_push($arrUpdate['Data'],array('col'=>'ClosedAt','val'=>$valdatetime));
                array_push($arrUpdate['Data'],array('col'=>'updateddatetime','val'=>$valdatetime));	
                $neworder =array();
                $WasAdirectDeliveryPickup=false;
                $AssignedLoadedWeight = 0;
                $AssignedLoadedVolume =  0;
                $AssOper = '';
                $CurrentLoadedVolume = 0;
                $CurrentLoadedWeight =  0;
                $CurOper='';	
                if($nors!=false){
                        $JobNo = $nors['quor_RefNo'];
                        $deliverymobile = $nors['deliverymobile'];
                        if($nors['IsPickup']==1){
                                if($request['failed']=='true'){
                                        array_push($arrUpdate['Data'],array('col'=>'OrderStatus','val'=>(int)$request['failedreasonid']));			
                                        array_push($arrUpdate['Data'],array('col'=>'FailedReasonID','val'=>(int)$request['failedreasonid']));
                                        $db->query("UPDATE  qugeo_order set quor_Status=" . $request['failedreasonid'] . " where quor_id = " . $nors['quor_id']  );	
                                        $delreason = $db->getItemFromDb("select dls_DelStatus from qugeo_deliverystatus where dls_ID = " . $request['failedreasonid'],true);	
                                        $updateurl = $db->getItemFromDb("select quor_StatusUpdateQry from qugeo_order where quor_id = " . $nors['quor_id'],true);	
                                        //$updateurl = str_replace("###1",QUGEO_TO_CUSTOMER_ORDER_STATUS_DELIVERY_FAILED,$updateurl);
                                        $updateurl = getQugeoParentStatusUpdated($updateurl,$request['failedreasonid']);
                                        $updateurl = str_replace("###6","1",$updateurl);
                                        $updateurl = str_replace("###2",$delreason,$updateurl);
                                        $execQry = explode(";",$updateurl);
                                        if(trim($execQry[0]) != "" )
                                                        $db->query(trim($execQry[0]));
                                        if(trim($execQry[1]) != "" )	
                                                $db->query(trim($execQry[1]));
                                        //FOR TRACKING - CLEAR
                                        $updateurl = $db->getItemFromDb("select quor_TrackingUpdateQry from qugeo_order where quor_id = " . $nors['quor_id'],true);									
                                        $TrackingUpdate = str_replace("###1",$NewDeliveryOrderId,QUGEO_TRACKING_API_GATEWAY);																
                                        $TrackingUpdate = str_replace("###2",AWSDYNAMODBTABLEPREFIX,$TrackingUpdate);	
                                        $updateurl = str_replace("###1","",$updateurl);
                                        $updateurl = str_replace("###6","1",$updateurl);
                                        if(trim($updateurl) != "" )
                                                $db->query($updateurl);	
                                        //Insert into History
                                        $updateurl = $db->getItemFromDb("select quor_TrackingHistory from qugeo_order where quor_id = " . $nors['quor_id'],true);	
                                        $updateurl = str_replace("##12",QUGEO_TO_B2C_ORDER_STATUS_PICKUP_FAILED,$updateurl);
                                        if(trim($updateurl) != "" )
                                                        $db->query($updateurl);	
                                        $AssOper = 	'-';
                                        $AssignedLoadedWeight= $totwt;
                                        $AssignedLoadedVolume= $totvol;
                                        }else{
                                        $confirmationdetails = json_decode($request['confirmationdetails'],true);
                                        if($nors['AcceptedAsDirectDelivery']==1){
                                                array_push($arrUpdate['Data'],array('col'=>'OrderStatus','val'=>(int)ORDER_PICKUP_PICKEDUP_TODST_DLS_ID));
                                                //FOR TABLE UPDATE
                                                $db->query("UPDATE  qugeo_order set quor_Status=" . ORDER_PICKUP_PICKEDUP_TODST_DLS_ID . ",quor_PickedupTime='" .  date("Y-m-d H:i:s", strtotime($valdatetime)) . "' where quor_id = " . $nors['quor_id'] );	
                                                $updateurl = $db->getItemFromDb("select quor_StatusUpdateQry from qugeo_order where quor_id = " . $nors['quor_id'],true);	
                                                //$updateurl = str_replace("###1",QUGEO_TO_CUSTOMER_ORDER_STATUS_OUT_DELIVERY,$updateurl);
                                                $updateurl = getQugeoParentStatusUpdated($updateurl,ORDER_PICKUP_PICKEDUP_TODST_DLS_ID);
                                                $updateurl = str_replace("###6","1",$updateurl);
                                                $updateurl = str_replace("###2","",$updateurl);
                                                $execQry = explode(";",$updateurl);
                                                if(trim($execQry[0]) != "" )
                                                                $db->query(trim($execQry[0]));
                                                if(trim($execQry[1]) != "" )
                                                                $db->query(trim($execQry[1]));																	

                                                $directdelivery = new QugeoScheduler();
                                                $NewDeliveryOrderId = $directdelivery->scheduleADelivery($nors['quor_id'],$orderdetails,false,'',true,true,$nors['HandlingBranch']);

                                                //FOR TRACKING
                                                $updateurl = $db->getItemFromDb("select quor_TrackingUpdateQry from qugeo_order where quor_id = " . $nors['quor_id'],true);		
                                                $DriverName = $db->getItemFromDb("select d_Name from qugeo_driver where d_ID = " . $_SESSION["loginid"],true);		
                                                $DriverPhone = $db->getItemFromDb("select d_Ph1 from qugeo_driver where d_ID = " . $_SESSION["loginid"],true);		
                                                $TrackingUpdate = str_replace("###1",$NewDeliveryOrderId,QUGEO_TRACKING_API_GATEWAY);																
                                                $TrackingUpdate = str_replace("###2",AWSDYNAMODBTABLEPREFIX,$TrackingUpdate);	
                                                $updateurl = str_replace("###1",$TrackingUpdate,$updateurl);
                                                $updateurl = str_replace("###6","1",$updateurl);
                                                $updateurl = str_replace("##10",addslashes($DriverName),$updateurl);
                                                $updateurl = str_replace("##11",addslashes($DriverPhone),$updateurl);
                                                if(trim($updateurl) != "" )
                                                                $db->query($updateurl);		
                                                //Insert into History
                                                $updateurl = $db->getItemFromDb("select quor_TrackingHistory from qugeo_order where quor_id = " . $nors['quor_id'],true);	
                                                $updateurl = str_replace("##12",QUGEO_TO_B2C_ORDER_STATUS_OUT_FOR_DELIVERY,$updateurl);
                                                if(trim($updateurl) != "" )
                                                                $db->query($updateurl);	

                                                $WasAdirectDeliveryPickup=true;
                                                }else{
                                                $db->query("UPDATE  qugeo_order set quor_Status=" . ORDER_PICKUP_PICKEDUP_TOBR_DLS_ID . ",quor_PickedupTime='" .  date("Y-m-d H:i:s", strtotime($valdatetime)) . "' where quor_id = " . $nors['quor_id'] );	
                                                $updateurl = $db->getItemFromDb("select quor_StatusUpdateQry from qugeo_order where quor_id = " . $nors['quor_id']);	
                                                //$updateurl = str_replace("###1",QUGEO_TO_CUSTOMER_ORDER_STATUS_OUT_DELIVERY,$updateurl);
                                                $updateurl = getQugeoParentStatusUpdated($updateurl,ORDER_PICKUP_PICKEDUP_TOBR_DLS_ID);
                                                $updateurl = str_replace("###6","1",$updateurl);
                                                $updateurl = str_replace("###2","",$updateurl);
                                                $execQry = explode(";",$updateurl);
                                                if(trim($execQry[0]) != "" )
                                                        $db->query(trim($execQry[0]));
                                                if(trim($execQry[1]) != "" )
                                                        $db->query(trim($execQry[1]));			
                                                //FOR TRACKING - CLEAR
                                                $updateurl = $db->getItemFromDb("select quor_TrackingUpdateQry from qugeo_order where quor_id = " . $nors['quor_id'],true);									
                                                $TrackingUpdate = str_replace("###1",$NewDeliveryOrderId,QUGEO_TRACKING_API_GATEWAY);																
                                                $TrackingUpdate = str_replace("###2",AWSDYNAMODBTABLEPREFIX,$TrackingUpdate);	
                                                $updateurl = str_replace("###1","",$updateurl);
                                                $updateurl = str_replace("###6","1",$updateurl);
                                                if(trim($updateurl) != "" )
                                                                $db->query($updateurl);									
                                                array_push($arrUpdate['Data'],array('col'=>'OrderStatus','val'=>(int)ORDER_PICKUP_PICKEDUP_TOBR_DLS_ID));
                                        }	
                                        $CurrentLoadedVolume = $totvol;
                                        $CurrentLoadedWeight =  $totwt;
                                        $CurOper='+';
                                        if($nors['HasReCalculatedCharges']==1){							
                                                array_push($arrUpdate['Data'],array('col'=>'ReCalculcationPaymentType','val'=>(int)$confirmationdetails['paymenttypeid']));
                                        }
                                }						
                                }else{ //delivery
                                if($request['failed']=='true'){						


                                        $delreason = $db->getItemFromDb("select dls_DelStatus from qugeo_deliverystatus where dls_ID = " . $request['failedreasonid'],true);	
                                        $updateurl = $db->getItemFromDb("select quor_StatusUpdateQry from qugeo_order where quor_id = " . $nors['quor_id'],true);	
                                        $temDetails = $db->getItemFromDb("select quor_ItemDetails from qugeo_order where quor_id = " . $nors['quor_id'],true);	
                                        $temDetailsarr = json_decode($temDetails);
                                        $barcodearr = array_column($temDetailsarr, 'barcodes');
                                        $barcodes = json_encode($barcodearr);
                                        $barcodes = "[" .  str_replace("]","",str_replace("[","",$barcodes)) . "]";
                                        $db->query("UPDATE  qugeo_order set  quor_Status=" . $request['failedreasonid'] . ", quor_ItemReturned = '" . $barcodes ."' where quor_id = " . $nors['quor_id']  );	
                                        array_push($arrUpdate['Data'],array('col'=>'OrderStatus','val'=>(int)$request['failedreasonid'],'return_items'=> $barcodes));	
                                        //$updateurl = str_replace("###1",QUGEO_TO_CUSTOMER_ORDER_STATUS_DELIVERY_FAILED,$updateurl);

                                        $updateurl = getQugeoParentStatusUpdated($updateurl,$request['failedreasonid']);
                                        $updateurl = str_replace("###6","1",$updateurl);
                                        $updateurl = str_replace("###2",$delreason,$updateurl);
                                        $execQry = explode(";",$updateurl);
                                        if(trim($execQry[0]) != "" )
                                                $db->query(trim($execQry[0]));
                                        if(trim($execQry[1]) != "" )
                                                        $db->query(trim($execQry[1]));	
                                        //Insert into History
                                        $updateurl = $db->getItemFromDb("select quor_TrackingHistory from qugeo_order where quor_id = " . $nors['quor_id'],true);	
                                        $updateurl = str_replace("##12",QUGEO_TO_B2C_ORDER_STATUS_DELIVERY_FAILED,$updateurl);
                                        if(trim($updateurl) != "" )
                                                        $db->query($updateurl);	
                                        }else{
                                        $request['return_items'] =  str_replace('"', '', $request['return_items']);
                                        $request['return_items'] =  str_replace('\'', '', $request['return_items']);
                                        //$request['ondel_payment_mode'] 
                                        //$request['ondel_payment_amount']
                                        //$request['ondel_refer_id']
                                        array_push($arrUpdate['Data'],array('col'=>'OrderStatus','val'=>(int)ORDER_DELIVERY_MARKED_DLS_ID,'return_items'=> $request['return_items']));	
                                        $db->query("UPDATE  qugeo_order set  quor_Status=" . ORDER_DELIVERY_MARKED_DLS_ID . ",quor_DeliveredTime='" . date("Y-m-d H:i:s", strtotime($valdatetime)) . "', quor_ItemReturned = '" . $request['return_items'] ."' where quor_id = " . $nors['quor_id'] );	
                                        $updatedetails = $db->getFromDb("select quor_StatusUpdateQry,quor_AmountCollectible,quor_Deliverybr_id from qugeo_order where quor_id = " . $nors['quor_id'],array() ,true);	
                                        file_put_contents('php://stderr', json_encode($updatedetails));
                                        $updateurl = $updatedetails['quor_StatusUpdateQry'];
                                        //$updateurl = str_replace("###1",QUGEO_TO_CUSTOMER_ORDER_STATUS_DELIVERD,$updateurl);
                                        file_put_contents('php://stderr', "hellonzz " . $nors['quor_id'] . ' -- '. $updateurl . "\n");  
                                        $updateurl = getQugeoParentStatusUpdated($updateurl,ORDER_DELIVERY_MARKED_DLS_ID);
                                        $updateurl = str_replace("###2","",$updateurl);
                                        $updateurl = str_replace("###6",(intval($request['ondel_payment_mode'])==1?7:6),$updateurl);
                                        $updateurl = str_replace("###7",(intval($request['ondel_payment_mode'])==1?"":$request['ondel_refer_id']),$updateurl);
                                        $execQry = explode(";",$updateurl);
                                        if(trim($execQry[0]) != "" )
                                                $db->query(trim($execQry[0]));
                                        if(trim($execQry[1]) != "" )
                                                        $db->query(trim($execQry[1]));	
                                        //Insert into History
                                        $updateurl = $db->getItemFromDb("select quor_TrackingHistory from qugeo_order where quor_id = " . $nors['quor_id'],true);	
                                        $updateurl = str_replace("##12",QUGEO_TO_B2C_ORDER_STATUS_DELIVERED_NOT_CONFIRMED,$updateurl);
                                        if(trim($updateurl) != "" )
                                                $db->query($updateurl);	
                                        //$AssOper = 	'-';
                                        //$AssignedLoadedWeight= $totwt;
                                        //$AssignedLoadedVolume= $totvol;	
                                        $CurrentLoadedVolume =  $totvol;
                                        $CurrentLoadedWeight = $totwt;
                                        $CurOper='-';		

                                        $sqldbconn = new \SqlDB(DSN);							
                                        $DriverName = $db->getItemFromDb("select d_Name from qugeo_driver where d_ID = " . $_SESSION["loginid"],true);
                                        //Our delivery partner {#var#} has delivered your Order No.{#var#} successfully. Thank you for selecting {#var#}.
                                        //1607100000000004819
                                        $qry =  "Our delivery partner " . $DriverName . " has delivered your Order No." . $JobNo ." successfully. Thank you for selecting." . PROJECT_NAME;	
                                        \sms::send($deliverymobile,$qry,$sqldbconn,"");
                                        file_put_contents('php://stderr', 'ON DEL PAY MODE  -- ' . $request['ondel_payment_mode'] );

                                        //margin

                                        $qugeoDetails = $sqldbconn->getFromDB("SELECT quor_TransferOrder_id,quor_DeliveryMethodsAllowed,quor_Type,quor_TransferOrder_Type,quor_Deliverybr_id,"
                                                . "quor_AmountCollectible FROM qugeo_order WHERE quor_id = {$nors['quor_id']}", true);
                                                                                                                //print_r($qugeoDetails);
                                        $fstoBarcodes = $sqldbconn->getMultipleData("SELECT stiid_id,stiid_barcode FROM finascop_stock_transfer_order_details_barcodes WHERE fsto_id = {$qugeoDetails['quor_TransferOrder_id']}", true);
    $companyMargin = 0;
    $operationMargin = 0;
    $csMargin = 0;
    $distributorMargin = 0;
    $stiid_poLandingCostleastSKU = 0;
    $totGST = 0;$retailorMargin=0;$courierMargin=0;$driverMargin=0;
    $br_PyramidLevel = $sqldbconn->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch WHERE br_ID = {$qugeoDetails['quor_Deliverybr_id']}");
    foreach ($fstoBarcodes as $fstoBarcod) {
    $poDetail = $sqldbconn->getFromDB("SELECT stiid_fpoid,stiid_fpodid,stiid_itemmasterid,stiid_poLandingCostleastSKU FROM finascop_stock_item_inventorydetails WHERE stiid_barcode = {$fstoBarcod['stiid_barcode']}", true);
    $margins = $sqldbconn->getFromDB("SELECT fpod_b2bCSgst,fpod_b2bcs_companymargin,fpod_b2bcs_opermargin,fpod_b2bcs_csmargin,
    fpod_b2bRetailgst,fpod_b2bretai_companymargin,fpod_b2bretai_opermargin,fpod_b2bretai_csmargin,fpod_b2bretai_dtrbtrmargin,
    fpod_itemptrgst,fpod_itemptr_dtrbtrmargin,fpod_itemptr_csmargin,fpod_itemptr_opermargin,fpod_itemptr_companymargin,
    fpod_itemptsgst,fpod_itempts_csmargin,fpod_itempts_opermargin,fpod_itempts_companymargin,
    fpod_companyMarginCD,fpod_incentiveMarginCD,fpod_csMarginCD,fpod_distributorMarginCD,fpod_retailorMarginCD,fpod_courierMarginCD,
    fpod_companyMarginHD,fpod_incentiveMarginHD,fpod_csMarginHD,fpod_distributorMarginHD,fpod_retailorMarginHD,fpod_driverMarginHD,
    fpod_companyMargin,fpod_incentiveMargin,fpod_csMargin,fpod_distributorMargin,fpod_retailorMargin,
    fpod_gstHmDel,fpod_gstCouDel,fpod_gstPikup 
    FROM finascop_purchase_order_details WHERE fpod_id = {$poDetail['stiid_fpodid']}", true);
    $stit_fixedB2BRates = $sqldbconn->getItemFromDB("SELECT stit_fixedB2BRates FROM finascop_stock_itemmaster WHERE stit_ID = {$poDetail['stiid_itemmasterid']}");
    $skuNos = $sqldbconn->getFromDB("SELECT cos_nos,ds_nos FROM finascop_stock_itemmaster WHERE stit_ID = {$poDetail['stiid_itemmasterid']}", true);
        $quor_TransferOrder_Type = $qugeoDetails['quor_TransferOrder_Type'];
//quor_TransferOrder_Type : 0 - CPD2BR, 1 - B2C, 2 - B2B, 3 - Return
        $quor_Type = $qugeoDetails['quor_Type'];
        //quor_Type : 1- Drive, 2-Hired, 3-CustomerPickup ,4-Courier, 5-DriverPickup, 6-ManualDelivery 
        if ($quor_TransferOrder_Type == 1) 
        {
            if ($quor_Type == 5) {//driver delivery
                $companyMargin = $companyMargin + $margins['fpod_companyMarginHD'];
                $operationMargin = $operationMargin + $margins['fpod_incentiveMarginHD'];
                $csMargin = $csMargin + $margins['fpod_csMarginHD'];
                $distributorMargin = $distributorMargin + $margins['fpod_distributorMarginHD'];
                $retailorMargin = $retailorMargin + $margins['fpod_retailorMarginHD'];
                $driverMargin = $driverMargin + $margins['fpod_driverMarginHD'];
                $totGST = $totGST + $margins['fpod_gstHmDel'];

            } elseif ($quor_Type == 3) {//customer pickup
                $companyMargin = $companyMargin + $margins['fpod_companyMargin'];
                $operationMargin = $operationMargin + $margins['fpod_incentiveMargin'];
                $csMargin = $csMargin + $margins['fpod_csMargin'];
                $distributorMargin = $distributorMargin + $margins['fpod_distributorMargin'];
                $retailorMargin = $retailorMargin +
                $margins['fpod_retailorMargin'];
                $totGST = $totGST + $margins['fpod_gstPikup'];


            } elseif ($quor_Type == 4) {//courier delivery
                $companyMargin = $companyMargin + $margins['fpod_companyMarginCD'];
                $operationMargin = $operationMargin + $margins['fpod_incentiveMarginCD'];
                $csMargin = $csMargin + $margins['fpod_csMarginCD'];
                $distributorMargin = $distributorMargin + $margins['fpod_distributorMarginCD'];
                $retailorMargin = $retailorMargin + $margins['fpod_retailorMarginCD'];
                $courierMargin = $courierMargin + $margins['fpod_courierMarginCD'];
                $totGST = $totGST + $margins['fpod_gstCouDel'];
            }
        } 
        elseif($quor_TransferOrder_Type == 2) 
        {
            if ($stit_fixedB2BRates == 1) {
                if ($_SESSION['admin']->br_PyramidLevel == 2) {//b2b sales cs to distributor - Fixed pricing
                    $companyMargin = $companyMargin + $margins['fpod_itempts_companymargin'];
                    $operationMargin = $operationMargin + $margins['fpod_itempts_opermargin'];
                    $csMargin = $csMargin + $margins['fpod_itempts_csmargin'];
                    $totGST = $totGST + $margins['fpod_itemptsgst'];
                } elseif ($_SESSION['admin']->br_PyramidLevel == 3) { // b2b sales distributor to retailer - Fixed pricing
                    $companyMargin = $companyMargin + $margins['fpod_itemptr_companymargin'];
                    $operationMargin = $operationMargin + $margins['fpod_itemptr_opermargin'];
                    $csMargin = $csMargin + $margins['fpod_itemptr_csmargin'];
                    $distributorMargin = $distributorMargin + $margins['fpod_itemptr_dtrbtrmargin'];
                    $totGST = $totGST + $margins['fpod_itemptrgst'];
                }
            } else {
                if ($_SESSION['admin']->br_PyramidLevel == 2) {//b2b sales cs to distributor - margin pricing
                    $companyMargin = $companyMargin + $margins['fpod_b2bcs_companymargin'];
                    $operationMargin = $operationMargin + $margins['fpod_b2bcs_opermargin'];
                    $csMargin = $csMargin + $margins['fpod_b2bcs_csmargin'];
                    $totGST = $totGST + $margins['fpod_b2bCSgst'];
                } elseif ($_SESSION['admin']->br_PyramidLevel == 3) {// b2b sales distributor to retailer - margin pricing
                    $companyMargin = $companyMargin + $margins['fpod_b2bretai_companymargin'];
                    $operationMargin = $operationMargin + $margins['fpod_b2bretai_opermargin'];
                    $csMargin = $csMargin + $margins['fpod_b2bretai_csmargin'];
                    $distributorMargin = $distributorMargin + $margins['fpod_b2bretai_dtrbtrmargin'];
                    $totGST = $totGST + $margins['fpod_b2bRetailgst'];
                }
            }
        }
    }

    if ($quor_TransferOrder_Type == 1) 
    {
    if ($quor_Type == 5) {//driver delivery

     $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 77";
    $wqSettings = $sqldbconn->getFromDB($query, true);
    $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
    $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

    $br_ReferenceIDs = $sqldbconn->getMultipleData($br_query);

    $total = $companyMargin + $operationMargin + $csMargin + $distributorMargin + $retailorMargin + $driverMargin;
    $transctionTemplate['dr']['sales']['amt'] = round($total,2);
    $transctionTemplate['dr']['sales']['br_ReferenceID'] = $br_ReferenceIDs[3][0];
    $transctionTemplate['cr']['companyMargin']['amt'] = $companyMargin;
    $transctionTemplate['cr']['companyMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0];
    $transctionTemplate['cr']['operationMargin']['amt'] = $operationMargin;
    $transctionTemplate['cr']['operationMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 
    $transctionTemplate['cr']['csMargin']['amt'] = $csMargin;
    $transctionTemplate['cr']['csMargin']['br_ReferenceID'] = $br_ReferenceIDs[1][0]; 
    $transctionTemplate['cr']['distributorMargin']['amt'] = $distributorMargin;
    $transctionTemplate['cr']['distributorMargin']['br_ReferenceID'] = $br_ReferenceIDs[2][0]; 
    $transctionTemplate['cr']['retailerMargin']['amt'] = $retailorMargin;
    $transctionTemplate['cr']['retailerMargin']['br_ReferenceID'] = $br_ReferenceIDs[3][0]; 
    $transctionTemplate['cr']['driverMargin']['amt'] = $driverMargin;
    $transctionTemplate['cr']['driverMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 

    $search = array("#ID#", "#NO#", "#AMT#",);
    $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'],  round($total,2) );
    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
    $fields = array(
        "waqu_TransDate" => date('Y-m-d'),
        "waqu_comment" => $transctionTemplate['comments'],
        "waqu_SourceID" => intval($quor_id),
        "waqs_id" => intval($wqSettings['waqs_id']),
        "waqu_Amount" =>  round($stiid_poLandingCostleastSKU + $total,2),
        "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
        "waqu_Data" => stripslashes(json_encode($transctionTemplate))
    );
    //$status = $sqldbconn->perform('finascop_wallet_queue', $fields);

    }
    elseif ($quor_Type == 3) {//customer pickup
    $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 24";
    $wqSettings = $sqldbconn->getFromDB($query, true);
    $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
    $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

    $br_ReferenceIDs = $sqldbconn->getMultipleData($br_query);

    $total = $companyMargin + $operationMargin + $csMargin + $distributorMargin + $retailorMargin;
    $transctionTemplate['dr']['sales']['amt'] = round($total,2);
    $transctionTemplate['dr']['sales']['br_ReferenceID'] = $br_ReferenceIDs[3][0];
    $transctionTemplate['cr']['companyMargin']['amt'] = $companyMargin;
    $transctionTemplate['cr']['companyMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0];
    $transctionTemplate['cr']['operationMargin']['amt'] = $operationMargin;
    $transctionTemplate['cr']['operationMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 
    $transctionTemplate['cr']['csMargin']['amt'] = $csMargin;
    $transctionTemplate['cr']['csMargin']['br_ReferenceID'] = $br_ReferenceIDs[1][0]; 
    $transctionTemplate['cr']['distributorMargin']['amt'] = $distributorMargin;
    $transctionTemplate['cr']['distributorMargin']['br_ReferenceID'] = $br_ReferenceIDs[2][0]; 
    $transctionTemplate['cr']['retailerMargin']['amt'] = $retailorMargin;
    $transctionTemplate['cr']['retailerMargin']['br_ReferenceID'] = $br_ReferenceIDs[3][0]; 

    $search = array("#ID#", "#NO#", "#AMT#",);
    $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'],  round($total,2) );
    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
    $fields = array(
        "waqu_TransDate" => date('Y-m-d'),
        "waqu_comment" => $transctionTemplate['comments'],
        "waqu_SourceID" => intval($quor_id),
        "waqs_id" => intval($wqSettings['waqs_id']),
        "waqu_Amount" =>  round($stiid_poLandingCostleastSKU + $total,2),
        "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
        "waqu_Data" => stripslashes(json_encode($transctionTemplate))
    );
    //$status = $sqldbconn->perform('finascop_wallet_queue', $fields);


    } 
    elseif ($quor_Type == 4) {//courier delivery

    $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 78";
    $wqSettings = $sqldbconn->getFromDB($query, true);
    $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
    $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

    $br_ReferenceIDs = $sqldbconn->getMultipleData($br_query);

    $total = $companyMargin + $operationMargin + $csMargin + $distributorMargin + $retailorMargin +$courierMargin;
    $transctionTemplate['dr']['sales']['amt'] = round($total,2);
    $transctionTemplate['dr']['sales']['br_ReferenceID'] = $br_ReferenceIDs[3][0];
    $transctionTemplate['cr']['companyMargin']['amt'] = $companyMargin;
    $transctionTemplate['cr']['companyMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0];
    $transctionTemplate['cr']['operationMargin']['amt'] = $operationMargin;
    $transctionTemplate['cr']['operationMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 
    $transctionTemplate['cr']['csMargin']['amt'] = $csMargin;
    $transctionTemplate['cr']['csMargin']['br_ReferenceID'] = $br_ReferenceIDs[1][0]; 
    $transctionTemplate['cr']['distributorMargin']['amt'] = $distributorMargin;
    $transctionTemplate['cr']['distributorMargin']['br_ReferenceID'] = $br_ReferenceIDs[2][0]; 
    $transctionTemplate['cr']['retailerMargin']['amt'] = $retailorMargin;
    $transctionTemplate['cr']['retailerMargin']['br_ReferenceID'] = $br_ReferenceIDs[3][0]; 
    $transctionTemplate['cr']['courierMargin']['amt'] = $courierMargin;
    $transctionTemplate['cr']['courierMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 

    $search = array("#ID#", "#NO#", "#AMT#",);
    $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'],  round($total,2) );
    $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
    $fields = array(
        "waqu_TransDate" => date('Y-m-d'),
        "waqu_comment" => $transctionTemplate['comments'],
        "waqu_SourceID" => intval($quor_id),
        "waqs_id" => intval($wqSettings['waqs_id']),
        "waqu_Amount" => doubleval($qugeoDetails['quor_AmountCollectible']),
        "br_id" =>  intval($qugeoDetails['quor_Deliverybr_id']),
        "waqu_Data" => stripslashes(json_encode($transctionTemplate))
    );
    //$status = $sqldbconn->perform('finascop_wallet_queue', $fields);
    }
    } 
    elseif ($quor_TransferOrder_Type == 2)
    {
        if ($stit_fixedB2BRates == 1) {
            if ($_SESSION['admin']->br_PyramidLevel == 2) {

                $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 69";
                $wqSettings = $sqldbconn->getFromDB($query, true);
                $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                $br_ReferenceIDs = $sqldbconn->getMultipleData($br_query);

                $total = $companyMargin + $operationMargin + $csMargin;
                $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                //$stiid_poLandingCostleastSKU
                $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total,2);
                $transctionTemplate['dr']['client']['key'] = $account;
                $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU,2);
                $transctionTemplate['cr']['sales']['br_ReferenceID'] = $br_ReferenceIDs[1][0];
                $transctionTemplate['cr']['companyMargin']['amt'] = $companyMargin;
                $transctionTemplate['cr']['companyMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0];
                $transctionTemplate['cr']['operationMargin']['amt'] = $operationMargin;
                $transctionTemplate['cr']['operationMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 
                $transctionTemplate['cr']['csMargin']['amt'] = $csMargin;
                $transctionTemplate['cr']['csMargin']['br_ReferenceID'] = $br_ReferenceIDs[1][0]; 


                $search = array("#ID#", "#NO#", "#AMT#",);
                $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'], round($stiid_poLandingCostleastSKU + $total,2) );
                $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                $fields = array(
                    "waqu_TransDate" => date('Y-m-d'),
                    "waqu_comment" => $transctionTemplate['comments'],
                    "waqu_SourceID" => intval($quor_id),
                    "waqs_id" => intval($wqSettings['waqs_id']),
                    "waqu_Amount" =>  round($stiid_poLandingCostleastSKU + $total,2),
                    "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                    "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                );
                //$status = $sqldbconn->perform('finascop_wallet_queue', $fields);

            }
            elseif ($_SESSION['admin']->br_PyramidLevel == 3) {

                $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 70";
                $wqSettings = $sqldbconn->getFromDB($query, true);
                $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                $br_ReferenceIDs = $sqldbconn->getMultipleData($br_query);

                $total = $companyMargin + $operationMargin + $csMargin + $distributorMargin;
                $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                //$stiid_poLandingCostleastSKU
                $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total,2);
                $transctionTemplate['dr']['client']['key'] = $account;
                $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU,2);
                $transctionTemplate['cr']['sales']['br_ReferenceID'] = $br_ReferenceIDs[2][0];
                $transctionTemplate['cr']['companyMargin']['amt'] = $companyMargin;
                $transctionTemplate['cr']['companyMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0];
                $transctionTemplate['cr']['operationMargin']['amt'] = $operationMargin;
                $transctionTemplate['cr']['operationMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 
                $transctionTemplate['cr']['csMargin']['amt'] = $csMargin;
                $transctionTemplate['cr']['csMargin']['br_ReferenceID'] = $br_ReferenceIDs[1][0]; 
                $transctionTemplate['cr']['distributorMargin']['amt'] = $distributorMargin;
                $transctionTemplate['cr']['distributorMargin']['br_ReferenceID'] = $br_ReferenceIDs[2][0]; 


                $search = array("#ID#", "#NO#", "#AMT#",);
                $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'],  round($stiid_poLandingCostleastSKU + $total,2) );
                $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                $fields = array(
                    "waqu_TransDate" => date('Y-m-d'),
                    "waqu_comment" => $transctionTemplate['comments'],
                    "waqu_SourceID" => intval($quor_id),
                    "waqs_id" => intval($wqSettings['waqs_id']),
                    "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total,2),
                    "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                    "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                );
                //$status = $sqldbconn->perform('finascop_wallet_queue', $fields);

            }
            } 
            else {
            if ($_SESSION['admin']->br_PyramidLevel == 2) {
                $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 69";
                $wqSettings = $sqldbconn->getFromDB($query, true);
                $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                $br_ReferenceIDs = $sqldbconn->getMultipleData($br_query);

                $total = $companyMargin + $operationMargin + $csMargin;
                $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                //$stiid_poLandingCostleastSKU
                $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total,2);
                $transctionTemplate['dr']['client']['key'] = $account;
                $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU,2);
                $transctionTemplate['cr']['sales']['br_ReferenceID'] = $br_ReferenceIDs[1][0];
                $transctionTemplate['cr']['companyMargin']['amt'] = $companyMargin;
                $transctionTemplate['cr']['companyMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0];
                $transctionTemplate['cr']['operationMargin']['amt'] = $operationMargin;
                $transctionTemplate['cr']['operationMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 
                $transctionTemplate['cr']['csMargin']['amt'] = $csMargin;
                $transctionTemplate['cr']['csMargin']['br_ReferenceID'] = $br_ReferenceIDs[1][0]; 


                $search = array("#ID#", "#NO#", "#AMT#",);
                $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'], round($stiid_poLandingCostleastSKU + $total,2) );
                $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                $fields = array(
                    "waqu_TransDate" => date('Y-m-d'),
                    "waqu_comment" => $transctionTemplate['comments'],
                    "waqu_SourceID" => intval($quor_id),
                    "waqs_id" => intval($wqSettings['waqs_id']),
                    "waqu_Amount" => round($stiid_poLandingCostleastSKU + $total,2),
                    "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                    "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                );
                //$status = $sqldbconn->perform('finascop_wallet_queue', $fields);
            } elseif ($_SESSION['admin']->br_PyramidLevel == 3) {
            $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 70";
                $wqSettings = $sqldbconn->getFromDB($query, true);
                $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
                $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM (SELECT @r := {$qugeoDetails['quor_Deliverybr_id']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";

                $br_ReferenceIDs = $sqldbconn->getMultipleData($br_query);

                $total = $companyMargin + $operationMargin + $csMargin + $distributorMargin;
                $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = (SELECT b2b_Customer_ID FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$qugeoDetails['quor_RefNo']}')");
                //$stiid_poLandingCostleastSKU
                $transctionTemplate['dr']['client']['amt'] = round($stiid_poLandingCostleastSKU + $total,2);
                $transctionTemplate['dr']['client']['key'] = $account;
                $transctionTemplate['cr']['sales']['amt'] = round($stiid_poLandingCostleastSKU,2);
                $transctionTemplate['cr']['sales']['br_ReferenceID'] = $br_ReferenceIDs[2][0];
                $transctionTemplate['cr']['companyMargin']['amt'] = $companyMargin;
                $transctionTemplate['cr']['companyMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0];
                $transctionTemplate['cr']['operationMargin']['amt'] = $operationMargin;
                $transctionTemplate['cr']['operationMargin']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 
                $transctionTemplate['cr']['csMargin']['amt'] = $csMargin;
                $transctionTemplate['cr']['csMargin']['br_ReferenceID'] = $br_ReferenceIDs[1][0]; 
                $transctionTemplate['cr']['distributorMargin']['amt'] = $distributorMargin;
                $transctionTemplate['cr']['distributorMargin']['br_ReferenceID'] = $br_ReferenceIDs[2][0]; 

                $qugeoDetails = $sqldbconn->getFromDB("SELECT quor_TransferOrder_id,quor_DeliveryMethodsAllowed,quor_Type,quor_TransferOrder_Type,quor_Deliverybr_id,quor_AmountCollectible FROM qugeo_order WHERE quor_id = {$nors['quor_id']}", true);
                $search = array("#ID#", "#NO#", "#AMT#",);
                $replace = array( $qugeoDetails['quor_RefNo'], $qugeoDetails['quor_TransferOrder_id'],  round($stiid_poLandingCostleastSKU + $total,2) );
                $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
                $fields = array(
                    "waqu_TransDate" => date('Y-m-d'),
                    "waqu_comment" => $transctionTemplate['comments'],
                    "waqu_SourceID" => intval($quor_id),
                    "waqs_id" => intval($wqSettings['waqs_id']),
                    "waqu_Amount" =>  round($stiid_poLandingCostleastSKU + $total,2),
                    "br_id" => intval($qugeoDetails['quor_Deliverybr_id']),
                    "waqu_Data" => stripslashes(json_encode($transctionTemplate))
                );
                //$status = $sqldbconn->perform('finascop_wallet_queue', $fields);

            }
            }
            }



    if($updatedetails['quor_AmountCollectible'] == 0)
    {
        $flag = 0;
        $sourcetype = $sqldbconn->getItemFromDb('select quor_Refno_Source from  qugeo_order where quor_id = ' . $nors['quor_id']);
        switch($sourcetype){
        case '0': // Branch Tramsfer
            if($br_PyramidLevel == 4){
                 $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 12";	
                        $wqSettingsl1 = $sqldbconn->getFromDB($query, true);
                        $transctionTemplatel1 = json_decode($wqSettingsl1['waqs_Configuration'], true);
                        $amt = getSourceOrderGrandtotal($sourcetype,$nors['quor_RefNo']);
                        $transctionTemplatel1['dr']['distStockinTransit']['amt'] = $amt;
                        $transctionTemplatel1['cr']['distStock']['amt'] = $amt;//  
                        $flag = 1;
            }
           
            

            break;
        case '1': // B2C
            $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 39";
            $wqSettingsl1 = $sqldbconn->getFromDB($query, true);
            $transctionTemplatel1 = json_decode($wqSettingsl1['waqs_Configuration'], true);
            $amt = getSourceOrderGrandtotal($sourcetype,$nors['quor_RefNo']);
            $transctionTemplatel1['dr']['retailerDeliveryCharges']['amt'] = $amt;
            $transctionTemplatel1['cr']['retailerStockinTransit']['amt'] = $amt;
             $flag = 1;
          
            break;
        //case '2': // B2B
            
           // break;
        //case '3': // Return
            
           // break;
    }
         
//            $fields = array("tempvalue" => '[retailerDeliveryCharges][amt]' . $amt);
//            $sqldbconn->perform('temptable',$fields);		
    if( $flag == 1){
        $qugeoDetails = $sqldbconn->getFromDB("SELECT quor_TransferOrder_id,quor_DeliveryMethodsAllowed,quor_Type,quor_TransferOrder_Type,quor_Deliverybr_id,quor_AmountCollectible,quor_RefNo FROM qugeo_order WHERE quor_id = {$nors['quor_id']}", true);
            $search = array("#ID#","#NO#","#AMT#");
            $replace = array($qugeoDetails['quor_TransferOrder_id'],$qugeoDetails['quor_RefNo'],$amt);

            $transctionTemplatel1['comments'] = str_replace($search, $replace, $transctionTemplatel1['comments']);

            $fieldsl1 = array(
                    "waqu_TransDate" => date('Y-m-d'),
                    "waqu_comment" => $transctionTemplatel1['comments'],
                    "waqu_SourceID" => intval($rsno['quor_id']),
                    "waqs_id" => intval($wqSettingsl1['waqs_id']),
                    "waqu_Amount" => doubleval($amt),
                    "br_id" => intval($updatedetails['quor_Deliverybr_id'] ),
                    "waqu_Data" => stripslashes(json_encode($transctionTemplatel1))
            );
            $status = $sqldbconn->perform('finascop_wallet_queue', $fieldsl1);	
    }
            	
    }elseif(intval($request['ondel_payment_mode'])==2 && $updatedetails['quor_AmountCollectible'] > 0)
    {
            $branchid =  intval($updatedetails['quor_Deliverybr_id'] );
            $defaulpaymentgateway = $sqldbconn->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'defaulpaymentgateway'");
            $query = "SELECT accled_ReferenceId FROM finascop_accounts_ledgertype_default fald INNER JOIN finascop_accounts_ledger fal ON fald.Group_ID = fal.Group_ID WHERE ledgertypedefaultname = '{$defaulpaymentgateway}'  AND accled_BranchId = {$branchid}";
            $accled_ReferenceId = $sqldbconn->getItemFromDB($query);

            //23-1
            $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 40";
            $wqSettingsl1 = $sqldbconn->getFromDB($query, true);
            $transctionTemplatel1 = json_decode($wqSettingsl1['waqs_Configuration'], true);

            $transctionTemplatel1['dr']['bank']['amt'] = $updatedetails['quor_AmountCollectible'];
            $transctionTemplatel1['dr']['bank']['key'] = $accled_ReferenceId;
            $transctionTemplatel1['cr']['cashCollectibleatRetailor']['amt'] = $updatedetails['quor_AmountCollectible'];

            $search = array("#AMT#", "#NO#", "#ID#");
            $replace = array($updatedetails['quor_AmountCollectible'], $quor_id, $quor_RefNo);
            $transctionTemplatel1['comments'] = str_replace($search, $replace, $transctionTemplatel1['comments']);		
            $fieldsl1 = array(
                    "waqu_TransDate" => date('Y-m-d'),
                    "waqu_comment" => $transctionTemplatel1['comments'],
                    "waqu_SourceID" => intval($rsno['quor_id']),
                    "waqs_id" => intval($wqSettingsl1['waqs_id']),
                    "waqu_Amount" => doubleval($updatedetails['quor_AmountCollectible']),
                    "br_id" => intval($updatedetails['quor_Deliverybr_id'] ),
                    "waqu_Data" => stripslashes(json_encode($transctionTemplatel1))
            );
            $status = $sqldbconn->perform('finascop_wallet_queue', $fieldsl1);		


            //23-2

            $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 62";
            $wqSettingsl1 = $sqldbconn->getFromDB($query, true);
            $transctionTemplatel1 = json_decode($wqSettingsl1['waqs_Configuration'], true);

            $transctionTemplatel1['dr']['retailerSales']['amt'] = $updatedetails['quor_AmountCollectible'];
            $transctionTemplatel1['cr']['retailorStockinTransit']['amt'] = $updatedetails['quor_AmountCollectible'];

            $search = array("#AMT#", "#NO#", "#ID#");
            $replace = array($updatedetails['quor_AmountCollectible'], $quor_id, $quor_RefNo);
            $transctionTemplatel1['comments'] = str_replace($search, $replace, $transctionTemplatel1['comments']);		
            $fieldsl1 = array(
                    "waqu_TransDate" => date('Y-m-d'),
                    "waqu_comment" => $transctionTemplatel1['comments'],
                    "waqu_SourceID" => intval($rsno['quor_id']),
                    "waqs_id" => intval($wqSettingsl1['waqs_id']),
                    "waqu_Amount" => doubleval($updatedetails['quor_AmountCollectible']),
                    "br_id" => intval($updatedetails['quor_Deliverybr_id'] ),
                    "waqu_Data" => stripslashes(json_encode($transctionTemplatel1))
            );
            $status = $sqldbconn->perform('finascop_wallet_queue', $fieldsl1);										
    }elseif($updatedetails['quor_AmountCollectible'] > 0){
            $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 41";
            $wqSettingsl1 = $sqldbconn->getFromDB($query, true);
            $transctionTemplatel1 = json_decode($wqSettingsl1['waqs_Configuration'], true);

            $transctionTemplatel1['cr']['cashCollectibleinRetailor']['amt'] = $updatedetails['quor_AmountCollectible'];								
            $transctionTemplatel1['dr']['cashinHandDriver']['amt'] = $updatedetails['quor_AmountCollectible'];									


            $search = array("#AMT#", "#NO#", "#ID#");
            $replace = array($amount, $quor_id, $quor_RefNo);
            $transctionTemplatel1['comments'] = str_replace($search, $replace, $transctionTemplatel1['comments']);

            $fieldsl1 = array(
                    "waqu_TransDate" => date('Y-m-d'),
                    "waqu_comment" => $transctionTemplatel1['comments'],
                    "waqu_SourceID" => intval($rsno['quor_id']),
                    "waqs_id" => intval($wqSettingsl1['waqs_id']),
                    "waqu_Amount" => doubleval($updatedetails['quor_AmountCollectible']),
                    "br_id" => intval($updatedetails['quor_Deliverybr_id']),
                    "waqu_Data" => stripslashes(json_encode($transctionTemplatel1))
            );
            $status = $sqldbconn->perform('finascop_wallet_queue', $fieldsl1);
            //24-2
            $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 62";
            $wqSettingsl1 = $sqldbconn->getFromDB($query, true);
            $transctionTemplatel1 = json_decode($wqSettingsl1['waqs_Configuration'], true);

            $transctionTemplatel1['dr']['retailerSales']['amt'] = $updatedetails['quor_AmountCollectible'];
            $transctionTemplatel1['cr']['retailorStockinTransit']['amt'] = $updatedetails['quor_AmountCollectible'];

            $search = array("#AMT#", "#NO#", "#ID#");
            $replace = array($updatedetails['quor_AmountCollectible'], $quor_id, $quor_RefNo);
            $transctionTemplatel1['comments'] = str_replace($search, $replace, $transctionTemplatel1['comments']);		
            $fieldsl1 = array(
                    "waqu_TransDate" => date('Y-m-d'),
                    "waqu_comment" => $transctionTemplatel1['comments'],
                    "waqu_SourceID" => intval($rsno['quor_id']),
                    "waqs_id" => intval($wqSettingsl1['waqs_id']),
                    "waqu_Amount" => doubleval($updatedetails['quor_AmountCollectible']),
                    "br_id" => intval($updatedetails['quor_Deliverybr_id'] ),
                    "waqu_Data" => stripslashes(json_encode($transctionTemplatel1))
            );
            $status = $sqldbconn->perform('finascop_wallet_queue', $fieldsl1);	
    }

    }
                                                                                //FOR TRACKING - CLEAR
                                $updateurl = $db->getItemFromDb("select quor_TrackingUpdateQry from qugeo_order where quor_id = " . $nors['quor_id'],true);
                                $updateurl = str_replace("###1","",$updateurl);
                                $updateurl = str_replace("###6","1",$updateurl);
                                if(trim($updateurl) != "" )
                                        $db->query($updateurl);		
                        }	

                        $nors = $nodb->perform('QugeoOrderDetails','update',$arrUpdate,$response);
                        $arrUpdate = array();			
                        $arrUpdate['PartitionKey'] = array('col'=>'apikey','val'=>$request['apikey'],'oper'=>'=');	
                        $arrUpdate['SortKey']=array('col'=>'orderid','val'=>$request['orderid'],'oper'=>'=');
                        $arrUpdate['Data']=array();
                        array_push($arrUpdate['Data'],array('col'=>'IsClosed','val'=>1));		
                        array_push($arrUpdate['Data'],array('col'=>'IsLiveOrder','val'=>0));		

                        $NewOrder = $nodb->perform('QugeoLiveVehicleOrders','update',$arrUpdate,$response);	
                        if($AssOper!='' || $CurOper!=''){			
                                $arrLiveVehicle = array();
                                $arrLiveVehicle['PartitionKey'] = array('col'=>'apikey','val'=>$request['apikey'],'oper'=>'=');	
                                $arrLiveVehicle['Data']=array();
                                if($AssOper!=''){
                                        array_push($arrLiveVehicle['Data'],array('col'=>'AssignedLoadedWeight','val'=>(float)$AssignedLoadedWeight,'oper'=>$AssOper));
                                        array_push($arrLiveVehicle['Data'],array('col'=>'AssignedLoadedVolume','val'=>(float)$AssignedLoadedVolume,'oper'=>$AssOper));
                                }
                                if($CurOper!=''){
                                        array_push($arrLiveVehicle['Data'],array('col'=>'CurrentLoadedVolume','val'=>(float)$CurrentLoadedVolume,'oper'=>$CurOper));
                                        array_push($arrLiveVehicle['Data'],array('col'=>'CurrentLoadedWeight','val'=>(float)$CurrentLoadedWeight,'oper'=>$CurOper));
                                }					
                                $nodb->perform('QugeoLiveVehicles','update',$arrLiveVehicle,$response);	
                        }

                        $orderhandler = new QugeoOrderHandler();								
                        if($WasAdirectDeliveryPickup){
                                $udpatedorder = $orderhandler->UpdateOrderOnPoll(1,$NewDeliveryOrderId,$request['apikey'],true,$orderdetails);
                                $orderhandler->AssignOrderToQugeoDriver($NewDeliveryOrderId,$request['apikey'],$orderdetails,$neworder,$dummy2,true);	
                                $hasorder = true;
                                }else{
                                $hasorder = $orderhandler->GetNextOrder($request['apikey'],$neworder);	
                        }
                        if($hasorder){					
                                $arrSession['msg'] = 'Has new order';
                                }else{					 
                                $arrSession['msg'] = 'No new order';	
                                $orderhandler->UpdateReleasingLocation($request['apikey'],0,0);	
                        }		
                        $arrSession['success']=true;				
                        }else{
                        $arrSession['success']=false;
                        $arrSession['msg'] = 'Invalid Order Id';							
                }
                $arrSession['Data']['vehicle'] = $neworder;		

                return $arrSession;
            }
            public function GET_itemdetails($flag,$request){	
                if (!array_key_exists('orderid', $request)  || !isset($request) ) {
                        throw new \Exception('Missing POST parameters ');
                }
                //$util =  new Utils();
                //$extrainfo = array("event"=>"GetItemDetails",'order'=>$request['orderid']);
                //$util->LogGeoCordinates($request['geocoords'],$_SESSION["usertype"],$_SESSION["loginid"],$request["apikey"],$extrainfo); 
                $nodb = new \cgoDynamiteDB();	
                $arrOrder['PartitionKey'] = array('col'=>'orderid','val'=>$request['orderid'],'oper'=>'=');		
                $arrOrder['getAttributes']=array('quor_id');
                $nors = $nodb->query('QugeoOrderDetails',$arrOrder,'getItem');	
                $response ="";
                $arrSession =array();
                if($nors!=false ) {
                                $db = new \cgoSqlDB();
                                $itemdets = $db->getItemFromDB("select quor_ItemDetails from  qugeo_order where quor_id = " . $nors['quor_id'],array(),true);
                                $response =  json_decode($itemdets);
                                $arrSession['success']=true;
                                $arrSession['msg'] = 'Item Details';				

                        }else{
                        $arrSession['msg'] = 'Get Item details action error';				
                }
                $arrSession['Data'] = $response;	
                return $arrSession;			
            }
            public function GET_pendingorders($flag,$request){
                $db = new \cgoSqlDB();
                $pendingorders = $db->getMulipleData('select if(quor_Status=22,"PICKUP", "DELIVERY") as drivetype,if(quor_Status=22,quor_PickupName,quor_DeliveryName) as name,if(quor_Status=22,quor_PickupPhone,quor_DeliveryPhone) as phone, quor_CreatedOn as Date, quor_id as id,quor_RefNo as OrderNo,md5(quor_UpdateOn) as `key`  from  qugeo_order  where (quor_Pickupbr_id=? OR quor_Deliverybr_id=?) and(quor_DeliveryMethodsAllowed&1) = 1  and (quor_Status = 22 or quor_Status = 31)  order by quor_CreatedOn asc limit ?,? ',array('iiii',intval($_SESSION["branchid"]),intval($_SESSION["branchid"]),intval($request['start']),intval($request['limit'])) ,true);			
                $vehicles =array();
                if($pendingorders !== false){				
                        $vehicles['success']=true;	
                        $vehicles['msg'] = 'Pending orders';			
                        $vehicles['Data']['pendingorders'] = $pendingorders;						
                        }else{
                        $vehicles['msg'] = 'No Pending orders';							
                        $vehicles['Data']['pendingorders'] = array();	
                }
                return $vehicles;				
            }
            public function GET_myorders($flag,$request){
                //quor_PickupDriverId, quor_DeliveryDriverId
                $db = new \cgoSqlDB();
                $pendingorders = $db->getMulipleData('select if(quor_Status=22,"PICKUP", "DELIVERY") as drivetype,if(quor_Status=22,quor_PickupName,quor_DeliveryName) as name,if(quor_Status=22,quor_PickupPhone,quor_DeliveryPhone) as phone, quor_CreatedOn as Date, quor_RefNo as OrderNo  from  qugeo_order  where (quor_QugeoPickupDDBDriverId = ? or  quor_QugeoDeliveryDDBDriverId = ?)  order by quor_CreatedOn asc limit ?,? ',array('iiii',$request["apikey"],$request["apikey"],intval($request['start']),intval($request['limit']),) ,true);			
                $vehicles =array();
                if($pendingorders !== false){				
                        $vehicles['success']=true;	
                        $vehicles['msg'] = 'My orders';			
                        $vehicles['Data']['myorders'] = $pendingorders;						
                        }else{
                        $vehicles['msg'] = 'No orders';							
                        $vehicles['Data']['myorders'] = array();	
                }
                return $vehicles;	
            }
            public function GET_deliveredorders($flag,$request){
                $db = new \cgoSqlDB();
                $pendingorders = $db->getMulipleData('select quor_DeliveryName as name,quor_DeliveryPhone as phone, quor_DeliveredTime as Date, quor_RefNo as OrderNo  from  qugeo_order  where quor_Type = 1  and  quor_DeliveryDriverId = ? and (quor_Status = 15 or quor_Status = 38)   order by quor_DeliveredTime desc limit ?,? ',array('iii',intval($_SESSION["loginid"]),intval($request['start']),intval($request['limit'])) ,true);			
                $vehicles =array();
                if($pendingorders !== false){				
                        $vehicles['success']=true;	
                        $vehicles['msg'] = 'Delivered orders';			
                        $vehicles['Data']['deliveredorders'] = $pendingorders;						
                        }else{
                        $vehicles['msg'] = 'No Delivered orders';							
                        $vehicles['Data']['deliveredorders'] = array();	
                }
                return $vehicles;	
            }
            public function GET_cashinhand($flag,$request){
                $cashinhand =array();
                $cashinhand['success']=true;	
                $cashinhand['msg'] = 'Cash In Hand';			
                $cashinhand['Data']['cashinhand'] = "-";	
                return $cashinhand;	
            }
            public function GET_myearnings($flag,$request){
                $cashinhand =array();
                $cashinhand['success']=true;	
                $cashinhand['msg'] = 'Earnings';			
                $cashinhand['Data']['myearnings'] = "-";	
                return $cashinhand;					
            }
            public function POST_pullpendingorder($flag,$request){
				//drivetype, Id
				if (!array_key_exists('key', $request)  || !array_key_exists('drivetype', $request)  || !array_key_exists('id', $request)  ||  !isset($request) ) {
					throw new \Exception('Missing POST parameters ');
				}	
				file_put_contents('php://stderr', "POST_pullpendingorder CALLED " . $request["id"] . " \n ");
				file_put_contents('php://stderr', print_r($request,TRUE));
				$db = new \cgoSqlDB();
				$updateon = $db->getItemFromDB("select md5(quor_UpdateOn) as updton from  qugeo_order where quor_id = " . $request['id'],array(),true);

				if($request['key'] <> $updateon ){
					$pullpendingorder =array();
					$pullpendingorder['success']=false;	
					$pullpendingorder['msg'] = 'Order has been updated, please reload and try again ';			
					$pullpendingorder['Data']['pendingorder'] = $request['key'] . "<>" . $updateon;	
					return $pullpendingorder;	
				}

				$scheduleorder = new  QugeoScheduler();
				if ($scheduleorder->IsQugeoAPIAlive($request["apikey"]) == false) {
					echo '{"success":false,"msg":"The Vehicle isnt active anymore, please reload"}';
					return;
				}
				if (strtoupper($request['drivetype']) == 'PICKUP') {
					$orderid = $scheduleorder->scheduleABooking($request['id'], $orderdetails, true, $request["apikey"], true);
				} else {
					$orderid = $scheduleorder->scheduleADelivery($request['id'], $orderdetails, true, $request["apikey"], true, false, 0);
				}

				$pullpendingorder =array();
				$pullpendingorder['success']=true;	
				$pullpendingorder['msg'] = 'Order has been pushed';			
				$pullpendingorder['Data']['pullpendingorder'] = array();	
				return $pullpendingorder;	

			}
        }
	}									