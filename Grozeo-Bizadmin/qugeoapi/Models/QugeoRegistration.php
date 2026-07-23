<?php
namespace Models;
{
	class QugeoRegistration extends ModelAbstract
	{
		public function GET_userdetails($flag,$request){		
			if (!array_key_exists('mobilenumber', $request) || !isset($request) ) {
					throw new \Exception('Missing GET parameters ');
			}	
			if ($request['mobilenumber']==''){
				throw new \Exception('Invalid mobile number');
			}			
			$db = new \cgoSqlDB();
			$lastvehicles = $db->getFromDb('select d_Name as name,d_Add1 as address1,d_Add2 as address2,d_Add3 as address3,d_licence as licno,d_licenceexpairy as licexpon  from  qugeo_driver  where d_Ph1 =? and d_Active =1 order by d_ID asc limit 1',array('s',$request["mobilenumber"]) ,true);
			$vehicles =array();			
			
			if($lastvehicles !== false){				
				$vehicles['success']=true;	
				$vehicles['msg'] = 'Please validate your details';			
				$vehicles['Data']['userdetails'] = $lastvehicles;			
			}else{				
				$vehicles['msg'] = 'Sorry that we could not find your details in our delivery agent list.. please contact ' . PROJECT_NAME . ' administrator';			
				$vehicles['Data']['userdetails'] = array() ;			
			}
			return $vehicles;			
		}
		public function POST_approveuser($flag,$request){		
			if (!array_key_exists('mobilenumber', $request) || !isset($request) ) {
					throw new \Exception('Missing POST parameters ');
			}	
			if ($request['mobilenumber']==''){
				throw new \Exception('Invalid mobile number');
			}			
			$db = new \cgoSqlDB();
			$lastvehicles = $db->getFromDb('select d_id,d_Name as name,d_Add1 as address1,d_Add2 as address2,d_Add3 as address3,d_licence as licno,d_licenceexpairy as licexpon,d_Ph1  from  qugeo_driver  where d_Ph1 =? and d_Active =1 order by d_ID asc limit 1',array('i',$request["mobilenumber"]) ,true);
			$vehicles =array();			
			
			if($lastvehicles !== false){				
				$util =  new Utils(); //d_Ph1
				$otp = $util->getOTP($lastvehicles['d_Ph1']);
				//$otp=1234;
				//QUGEO_OTP_TIMEOUT
				$db->begintransaction();
				$db->query('update  qugeo_driver set d_otp=?,d_otpvalidtill=now() where d_id = ?',array('ii',$otp,$lastvehicles['d_id']));				
				//$sms = new \SoftSMS();
				//$smsresponse = $sms->sendSMS( $request["mobilenumber"],"Welcome " . $lastvehicles['name'] . " to Pocketkart. Your OTP for activating PocketKart Drive app is " . $otp );
				//textLocalsms(TextLocalSMS_CREDENTIALS,array($request["mobilenumber"]),TextLocalSMS_SENDER,"Hi, Your otp for verification is " . $otp);
				$sqldbconn = new \sqlDb(DSN);
				//$sms = new \sms();
				//$sms->send($request["mobilenumber"],"Hi, Your otp for verification is " . $otp  ,$sqldbconn,"");
				//Welcome to {#var#} Drive. {#var#} is your OTP to complete the registration process. Thank you for using Drive.
                                //1607100000000004851
				$str = "Welcome to " . PROJECT_NAME . " Drive. " . $otp . " is your OTP to complete the registration process. Thank you for using Drive.";
				//\sms::send($request["mobilenumber"],$str,$db,"");
                                $templatedata['otp'] = $otp;
                                \sms::fetchContentSendSms($templatedata, $request["mobilenumber"], 10);
				$smsresponse = true;
				$db->committransaction();
				$vehicles['success']=($smsresponse==true?true:false);	
				$vehicles['msg'] = ($smsresponse==true)?'Please use the OTP just send, to complete the registration':'Failed sending OTP';						
				$vehicles['Data']['SMS_Sender_Response'] = $smsresponse;		
				$vehicles['Data']['OTP'] = $otp;						
			}else{				
				$vehicles['msg'] = 'Sorry that we could not find your details in our delivery agent list.. please contact ' . PROJECT_NAME . ' administrator';			
				$vehicles['Data']['userdetails'] = array() ;			
			}
			return $vehicles;			
		}
		public function POST_qugeoregistration($flag,$request){		
			if (!array_key_exists('mobilenumber', $request) || !array_key_exists('gcmregisterationid', $request) || !array_key_exists('imeinumber', $request) || !array_key_exists('otp', $request) || !array_key_exists('geocoords', $request) ||  !isset($request) ) {
					throw new \Exception('Missing POST parameters ');
			}	
			if ($request['mobilenumber']==''){
				throw new \Exception('Invalid mobile number');
			}			
			if ($request['imeinumber']==''){
				throw new \Exception('Invalid imei number');
			}
			if ($request['gcmregisterationid']==''){
				throw new \Exception('Invalid GCM registration number');
			}			
			$util =  new Utils();
			$extrainfo = array("event"=>"registration","gcmregisterationid"=>$request['gcmregisterationid'],"imeinumber"=>$request['imeinumber'],"OTP"=>$request['otp']);
			$util->LogGeoCordinates($request['geocoords'],"2","0",$request['mobilenumber'],$extrainfo);
			
			$db = new \cgoSqlDB();
			$rsOTPdetails = $db->getFromDb('select d_id,d_otp,d_otpvalidtill,TIMESTAMPDIFF(SECOND, "2016-05-12 10:23:29",DATE_ADD(NOW(), INTERVAL ' . QUGEO_OTP_TIMEOUT  . ' SECOND))  as timediff,d_name as name  from  qugeo_driver  where d_Ph1 =? and d_Active =1 order by d_ID asc limit 1',array('i',$request["mobilenumber"]) ,true);
			
			$vehicles =array();			
			$vehicles['Data']['userdetails'] = array() ;	
			$vehicles['OTPExpired'] = false;				
			if(!$rsOTPdetails){
				$vehicles['msg'] = 'Sorry that we could not find your details in our delivery agent list.. please contact ' . PROJECT_NAME . ' administrator';							
				return $vehicles;			
			}elseif($rsOTPdetails['d_otp'] != $request['otp'] || intval($request['otp'])==0){
				$vehicles['msg'] = 'Not a valid OTP ' ;								
				return $vehicles;							
			}/*elseif($rsOTPdetails['timediff'] <= 0 ){
				$vehicles['msg'] = 'The used OTP has expired';				
				$vehicles['OTPExpired'] = true;				
				return $vehicles;							
			}*/elseif($request['gcmregisterationid'] =='' ){
				$vehicles['msg'] = 'Blank GCM registration received';								
				return $vehicles;							
			}
			
			$db->begintransaction();
			$awssnsarn = $db->getItemFromDb('select d_awssnsarn from  qugeo_driver where gcmregstid = ?',array('s',$request['gcmregisterationid']));	
			//$sns = new \cgoAWSSNS();
			
			/*if($awssnsarn!=''){			
				$sns->deleteEndPoint($awssnsarn);
			}*/
			file_put_contents('php://stderr', print_r($request, TRUE));
			$awssnsarn='';			
			//$awssnsarn = $sns->createEndPoint($request['gcmregisterationid'],$rsOTPdetails['name']."_".$request["mobilenumber"]."_".date("YmdHis"));
			$db->query('update  qugeo_driver set gcmregstid ="" where gcmregstid = ?',array('s',$request['gcmregisterationid']));				
			$db->query('update  qugeo_driver set d_otp=0,d_awssnsarn=?,imeinumber=?,gcmregstid=? where d_id = ?',array('sssi',$awssnsarn,$request['imeinumber'],$request['gcmregisterationid'],$rsOTPdetails['d_id']));			
			$db->committransaction();
			$vehicles['success']=true;	
			$vehicles['msg'] = 'Registration completed';			
			$vehicles['Data']['userdetails'] = array();
			return $vehicles;			
		}		
	}
}