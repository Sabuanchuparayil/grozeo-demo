<?php
namespace Models;
{
	class ContentType extends ModelAbstract
	{
		 public function GET_contenttype($flag,$request){
			$db = new \cgoSqlDB();
			if (!array_key_exists('contentid', $request)  || !isset($request) ) 
				$rs = $db->getMulipleData('select ct_ID as id,ct_Name as name from  contenttype',null ,true);
			else
				$rs = $db->getMulipleData('select ct_ID as id,ct_Name as name from  contenttype where ct_ID =?',array('i',$request['packingid']) ,true);
				
			$arrLocation =array();
			if($rs !== false){
				$arrLocation['success']=true;
				$arrLocation['msg'] = 'Content Type Found';			
				$arrLocation['Data']['contenttype'] = $rs;			
			}
			else{
				$arrLocation['msg'] = 'Content Type Found';			
				$arrLocation['Data']['contenttype'] = array();						
			}
			return $arrLocation;			
		 }
	}
}