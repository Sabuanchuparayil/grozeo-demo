<?php
namespace Models;
{
	class PackingType extends ModelAbstract
	{
		 public function GET_packingtype($flag,$request){
			$db = new \cgoSqlDB();
			if (!array_key_exists('packingid', $request)  || !isset($request) ) 
				$rs = $db->getMulipleData('select pk_ID as id,pk_Name as name from  packingtype order by pk_Name',null ,true);
			else
				$rs = $db->getMulipleData('select pk_ID as id,pk_Name as name from  packingtype where pk_ID =? order by pk_Name',array('i',$request['packingid']) ,true);
				
			$arrLocation =array();
			if($rs !== false){
				$arrLocation['success']=true;
				$arrLocation['msg'] = 'Packing Type Found';			
				$arrLocation['Data']['packingtype'] = $rs;			
			}
			else{
				$arrLocation['msg'] = 'Packing Type Not Found';			
				$arrLocation['Data']['Packingtype'] = array();						
			}
			return $arrLocation;			
		 }
	}
}