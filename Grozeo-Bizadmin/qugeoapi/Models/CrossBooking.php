<?php
namespace Models;
{
	class CrossBooking extends ModelAbstract
	{
		public function GET_branch($flag,$request){	
			$db = new \cgoSqlDB();
			if (!array_key_exists('branchid', $request)  || !isset($request) ) {
				if (array_key_exists('query', $request)  ) {
					$rs = $db->getMulipleData('select br_ID as id,br_Name as name from  branch where br_name like ? order by br_Name',array('s',$request['query'].'%')  ,true);
				}else{
					$rs = $db->getMulipleData('select br_ID as id,br_Name as name from  branch order by br_Name',null  ,true);
				}
			}
			else{
				if (array_key_exists('query', $request)  ) {
					$rs = $db->getMulipleData('select br_ID as id,br_Name as name from  branch where br_ID =? and br_name like ? order by br_Name',array('is',$request['branchid'],$request['query'].'%') ,true);
				}else{
					$rs = $db->getMulipleData('select br_ID as id,br_Name as name from  branch where br_ID =?  order by br_Name',array('i',$request['branchid']) ,true);					
				}			
			}
				
			$arrBranch =array();
			if($rs !== false){
				$arrBranch['success']=true;
				$arrBranch['msg'] = 'Branch Details';			
				$arrBranch['Data']['branch'] = $rs;			
			}
			else{
				$arrBranch['msg'] = 'Branch not found';			
				$arrBranch['Data']['branch'] = array();						
			}
			return $arrBranch;					
		}
		
		public function GET_customer($flag,$request){	
			if (!array_key_exists('query', $request) || !array_key_exists('branchid', $request) || (intval($request['branchid'])==0) || !isset($request) ) {
					throw new \Exception('Missing GET parameters ');
			}
			$db = new \cgoSqlDB();
			if (!array_key_exists('customerid', $request)  || !isset($request) ) 
				$rs = $db->getMulipleData('select c_ID as id,concat(left(c_Name,25),",",c_ph1) as name from  customer where br_id = ? and c_name like ? order by c_Name limit 25',array('is',$request['branchid'],$request['query'].'%') ,true);
			else
				$rs = $db->getMulipleData('select c_ID as id,concat(left(c_Name,25),",",c_ph1) as name from  customer where br_id = ? and  c_ID =?  and c_name like ? order by c_Name limit 25',array('iis',$request['branchid'],$request['customerid'],$request['query'].'%') ,true);

			$arrCustomer =array();
			if($rs !== false){
				$arrCustomer['success']=true;
				$arrCustomer['msg'] = 'Customer Details';			
				$arrCustomer['Data']['customer'] = $rs;			
			}
			else{
				$arrCustomer['msg'] = 'Customer not found';			
				$arrCustomer['Data']['customer'] = array();						
			}
			return $arrCustomer;					
		}
		
		public function POST_inwardcrossbook($flag,$request){	
			if (!array_key_exists('crossbookdets', $request) || !isset($request) ) {
					throw new \Exception('Missing POST parameter -crossbookdets ');
			}elseif (!array_key_exists('brid', $request) || !isset($request) ) {
					throw new \Exception('Missing POST parameter - brid ');
			}
			$nodb = new \cgoDynamiteDB();	
			$arrCrossBook = array();					
			$arrCrossBook['Data']=array();	
			$valdate = date("YmdHis");
			$queueid = round(microtime(true) * 1000);
			array_push($arrCrossBook['Data'],array('col'=>'id','val'=>(int)$queueid));
			array_push($arrCrossBook['Data'],array('col'=>'brid','val'=>(int)$request['brid']));
			array_push($arrCrossBook['Data'],array('col'=>'crossdt','val'=>(int)$valdate));		
			array_push($arrCrossBook['Data'],array('col'=>'IsProcessed','val'=>0));					
			array_push($arrCrossBook['Data'],array('col'=>'crossbookdets','val'=>json_decode($request['crossbookdets'],true)));			
			$response = null;
			$newbooking = $nodb->perform('CrossInwardBooking','insert',$arrCrossBook,$response);
			$arrAuth = array();
			if ($newbooking){					
				$arrAuth['success']=true;
				$arrAuth['msg'] = 'Queued Cross booking';	
				$arrAuth['Data']['refno'] = $queueid;													
			}else{
				$arrAuth['msg'] = 'Unable to queue for cross booking, try later';		
				$arrAuth['Data']['refno'] = null;						
			}			
			return $arrAuth;
		}
	
		public function GET_contenttype($flag,$request){
			$db = new \cgoSqlDB();
			if (!array_key_exists('contentid', $request)  || !isset($request) ) {
				if (array_key_exists('query', $request)  ) {
					$rs = $db->getMulipleData('select ct_ID as id,ct_Name as name from  contenttype where ct_Name like ? order by ct_Name',array('s',$request['query'].'%')  ,true);
				}else{
					$rs = $db->getMulipleData('select ct_ID as id,ct_Name as name from  contenttype order by ct_Name',null  ,true);
				}
			}
			else{
				if (array_key_exists('query', $request)  ) {
					$rs = $db->getMulipleData('select ct_ID as id,ct_Name as name from  contenttype where ct_ID =? and ct_Name like ? order by ct_Name',array('is',$request['contentid'],$request['query'].'%') ,true);
				}else{
					$rs = $db->getMulipleData('select ct_ID as id,ct_Name as name from  contenttype where ct_ID =?  order by ct_Name',array('i',$request['contentid']) ,true);					
				}			
			}
				
			$arrContentType =array();
			if($rs !== false){
				$arrContentType['success']=true;
				$arrContentType['msg'] = 'Content Type Details';			
				$arrContentType['Data']['contenttype'] = $rs;			
			}
			else{
				$arrContentType['msg'] = 'Content Type not found';			
				$arrContentType['Data']['contenttype'] = array();						
			}
			return $arrContentType;					
		}
		
		public function GET_packingtype($flag,$request){
			$db = new \cgoSqlDB();
			if (!array_key_exists('packingit', $request)  || !isset($request) ) {
				if (array_key_exists('query', $request)  ) {
					$rs = $db->getMulipleData('select pk_ID as id,pk_Name as name from  packingtype where pk_Name like ? order by pk_Name',array('s',$request['query'].'%')  ,true);
				}else{
					$rs = $db->getMulipleData('select pk_ID as id,pk_Name as name from  packingtype order by pk_Name',null  ,true);
				}
			}else{
				if (array_key_exists('query', $request)  ) {
					$rs = $db->getMulipleData('select pk_ID as id,pk_Name as name from  packingtype where pk_ID =? and pk_Name like ? order by pk_Name',array('is',$request['packingit'],$request['query'].'%') ,true);
				}else{
					$rs = $db->getMulipleData('select pk_ID as id,pk_Name as name from  packingtype where pk_ID =?  order by pk_Name',array('i',$request['packingit']) ,true);					
				}			
			}
				
			$arrpackingtype =array();
			if($rs !== false){
				$arrpackingtype['success']=true;
				$arrpackingtype['msg'] = 'Packing Type Details';			
				$arrpackingtype['Data']['packingtype'] = $rs;			
			}
			else{
				$arrpackingtype['msg'] = 'Packing Type not found';			
				$arrpackingtype['Data']['packingtype'] = array();						
			}
			return $arrpackingtype;					
		}
	
		public function GET_outwardcrossbook($flag,$request){	
			if (!array_key_exists('bkid', $request) || !isset($request) ) {
					throw new \Exception('Missing GET parameter - bkid ');
			}elseif (!array_key_exists('brid', $request) || !isset($request) ) {
					throw new \Exception('Missing GET parameter - brid ');
			}
			
			$db = new \cgoSqlDB();
			$rs = $db->getMulipleData('select  getCrossBranchID(bk_s_br_id) as sbrid, getCrossCustID(bk_s_br_id) as ConsgrId, getCrossBranchID(bk_d_br_id) as dbrid, getCrossCustID(bk_d_br_id) as ConsgeId,bk_pvtmark as pvtmark,bk_TotWt as wt,bk_TotVol as vol,bk_TotInvAmt as InvAmt,bk_no as bkno,bk_PktCount as pkts,(select br_HasINCrossPickup from  branch where br_id = bk_d_br_id) as HasINCrossPickup,(SELECT  getCrossContentID(ct_ID) FROM  inward_consignment WHERE  inward_consignment.d_br = tr.bk_brk_br_id AND  inward_consignment.bk_id = tr.bk_id limit 1) as contenttypeid, (SELECT  getCrossPackingID(pk_ID) FROM  inward_consignment WHERE  inward_consignment.d_br = tr.bk_brk_br_id AND  inward_consignment.bk_id = tr.bk_id  limit 1) as packettypeid,(SELECT co_InvNo FROM  inward_consignment WHERE  inward_consignment.d_br = tr.bk_brk_br_id AND  inward_consignment.bk_id = tr.bk_id  limit 1) as InvoiceNo,bk_TotInvAmt as InvoiceAmt,(SELECT co_InvDate FROM  inward_consignment WHERE  inward_consignment.d_br = tr.bk_brk_br_id AND  inward_consignment.bk_id = tr.bk_id  limit 1) as InvoiceDt,bk_user as User  from  inward_booking tr where bk_brk_br_id =? and bk_id = ?  ',array('ii',$request['brid'],$request['bkid']) ,true);					
			
			$arrCustomer =array();
			if($rs !== false){
				$arrCustomer['success']=true;
				$arrCustomer['msg'] = 'Cross Booking Details';			
				$arrCustomer['Data']['outwardcrossbook'] = $rs;			
			}
			else{
				$arrCustomer['msg'] = 'Cross Booking Details';			
				$arrCustomer['Data']['outwardcrossbook'] = array();						
			}
			return $arrCustomer;	
			
		}		
		
		public function GET_company($flag,$request){	
			$db = new \cgoSqlDB();
			
			if (!array_key_exists('brid', $request) || !isset($request) ) {
					throw new \Exception('Missing GET parameter - brid ');
			}			
			
			if (!array_key_exists('compid', $request)  || !isset($request) ) {
				if (array_key_exists('query', $request)  ) {
					$rs = $db->getMulipleData('select comp_id as id,comp_name as name from  company inner join  branch_company using(comp_id) where comp_name like ? and br_id = ? order by comp_name',array('si',$request['query'].'%',$request['brid'])  ,true);
				}else{
					$rs = $db->getMulipleData('select comp_id as id,comp_name as name from  company inner join  branch_company using(comp_id) where br_id = ? order by comp_name',array('i',$request['brid'])  ,true);
				}
			}
			else{
				if (array_key_exists('query', $request)  ) {
					$rs = $db->getMulipleData('select comp_id as id,comp_name as name from  company inner join  branch_company using(comp_id) where comp_id =? and comp_name like ? and br_id = ? order by comp_name',array('isi',$request['compid'],$request['query'].'%',$request['brid']) ,true);
				}else{
					$rs = $db->getMulipleData('select comp_id as id,comp_name as name from  company inner join  branch_company using(comp_id) where comp_id =?  and br_id = ? order by comp_name',array('ii',$request['compid'],$request['brid']) ,true);					
				}			
			}
			
			$arrCompany =array();
			if($rs !== false){
				$arrCompany['success']=true;
				$arrCompany['msg'] = 'Company Details';			
				$arrCompany['Data']['company'] = $rs;			
			}
			else{
				$arrCompany['msg'] = 'Company not found';			
				$arrCompany['Data']['company'] = array();						
			}
			return $arrCompany;					
		}
		
	}	
}