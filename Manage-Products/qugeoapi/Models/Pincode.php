<?php
	namespace Models;
	{
		class Pincode extends ModelAbstract
		{
			public function GET_hasservice($flag,$request){
				if (!array_key_exists('pincode', $request) || !array_key_exists('w', $request) || !isset($request)) 
				throw new \Exception('Missing GET parameters ');
				$db = new \cgoSqlDB();
				if($request['w'] =='pickup'){
					$isactive = $db->getItemFromDB('select  if(isactive=1,"Active","InActive") as active   from  branch_location inner join  pincode on brlo_pincode=pincode where brlo_Active =1 and Has_Pickup =1 and isActive =1 and  brlo_pincode =? limit 1',array('i',$request['pincode']) ,true);
					}else{
					$isactive = $db->getItemFromDB('select  if(isactive=1,"Active","InActive") as active   from  branch_location inner join  pincode on brlo_pincode=pincode where brlo_Active =1 and Has_Delivery = 1 and isActive =1 and  brlo_pincode =? limit 1',array('i',$request['pincode']) ,true);
				}
				$arrLocation =array();
				if($isactive == 'Active'){
					$arrLocation['success']=true;
					$arrLocation['msg'] = 'Has Service';			
					$arrLocation['Data']['pincode'] = $isactive;			
				}
				else{
					$arrLocation['msg'] = 'Has No Service';			
					$arrLocation['Data']['pincode'] = 'InActive';						
				}
				return $arrLocation;				
			}
			public function GET_location($flag,$request){
				if (!array_key_exists('pincode', $request)  || !isset($request) ) 
				throw new \Exception('Missing GET parameters ');	
				$db = new \cgoSqlDB();
				$rs = $db->getMulipleData('select  brlo_id as id,brlo_name as name,brlo_Lati as latitude,brlo_Long as longitude,' . CAREGO_LOCATION_DRAG_SOFT_LIMIT . ' as softlimit,' . CAREGO_LOCATION_DRAG_HARD_LIMIT . ' as hardlimit from  branch_location  inner join  pincode on brlo_pincode=pincode where brlo_Active =1 and isActive =1 and brlo_pincode =? order by brlo_name',array('i',$request['pincode']) ,true);
				$arrLocation =array();
				if($rs !== false){
					$arrLocation['success']=true;
					$arrLocation['msg'] = 'Locations Found';			
					$arrLocation['Data']['location'] = $rs;			
				}
				else{
					$arrLocation['msg'] = 'Locations Not Found';			
					$arrLocation['Data']['location'] = null;						
				}
				return $arrLocation;				
			}
			public function GET_gmap($flag,$request){
				if (!array_key_exists('locationid', $request)  || !isset($request) ) 
				throw new \Exception('Missing GET parameters ');	
				$db = new \cgoSqlDB();
				$rs = $db->getMulipleData('select  brlo_Lati as latitude,brlo_Long as longitude from  branch_location where brlo_id =?',array('i',$request['locationid']) ,true);
				$arrLocation =array();
				if($rs !== false){
					$arrLocation['success']=true;
					$arrLocation['msg'] = 'Locations Found';			
					$arrLocation['Data']['gmap'] = $rs;			
				}
				else{
					$arrLocation['msg'] = 'Locations Not Found';			
					$arrLocation['Data']['gmap'] = null;						
				}
				return $arrLocation;			
			}		 
		}
	}	