<?php
	namespace Models;
	{
		class Charges extends ModelAbstract
		{
			private $db;
			public function getcharges($flag,$request,$HasDetails=false){
				$this->db = new \cgoSqlDB();				 
				$nodb = new \cgoDynamiteDB();	
				if($HasDetails===false){
					$arrSessionDetts =array();
					$arrSessionDetts['PartitionKey']=array('col'=>'apikey','val'=>$request['apikey']);
					$arrSessionDetts['SortKey']=array('col'=>'bksession','val'=>$request['bksession']);
					$arrSessionDetts['getAttributes']=array('delivery_addresses','pickup_addresses','consignment');
					$rsno = $nodb->query('BookingSession',$arrSessionDetts,'getItem');		
					
					$params =array();
					//Get session details
					if(count($rsno) > 0 ){	
						$respdeliveryAddress =$rsno['delivery_addresses'];
						$resppickupAddress = $rsno['pickup_addresses'];
						$respConsignment = $rsno['consignment'];
						}else{
						throw new \Exception('Missing required charges data'  );	
					}	
					}else{
					$resppickupAddress = $HasDetails['pickup_addresses'];
					$respdeliveryAddress = $HasDetails['delivery_addresses'];
					$respConsignment = $HasDetails['consignment'];
					$_SESSION["c_id"] = $HasDetails['ConsignorId'] ;
				}	
				$params['cust']['id'] = $_SESSION["c_id"];
				if($HasDetails===false){
					$rs =  $this->db->getFromDB('select br_id,taxable,c_Name from  customer where c_id = ?',array('i',($params['cust']['id'])),true);
					$params['cust']['brid'] = $rs['br_id'];
					$params['cust']['taxable'] = $rs['taxable'];
					$params['cust']['name'] = $rs['c_Name'];
					}else{
					$params['cust']['brid'] = $resppickupAddress['SrcBrId'];
					$params['cust']['taxable'] = $HasDetails['Taxable'];
					$params['cust']['name'] = "";				
				}
				//Set Locations from sessions
				$params['srcbr']['location']['id'] = $resppickupAddress['locationid'];
				$params['srcbr']['location']['name'] = $resppickupAddress['location']['name'];
				$params['dstbr']['location']['id'] = $respdeliveryAddress['locationid'];	
				$params['dstbr']['location']['name'] = $respdeliveryAddress['location']['name'];
				//Actual pick up co-ordinates
				if(!empty($resppickupAddress['actualcoords'])){
					$params['srcbr']['location']['cust']['coord']=$resppickupAddress['actualcoords'];
				}			 
				//Actual delivery co-ordinates
				if(!empty($respdeliveryAddress['actualcoords'])){
					$params['dstbr']['location']['cust']['coord']=$respdeliveryAddress['actualcoords'];
				}
				//Branch details  	
				if($HasDetails===false){
					$rs = $this->db->getFromDB('select branch.br_id as br_id,branch.br_Lati as br_Lati,branch.br_Lng as br_Lng,br_name from  branch_location inner join  branch  using (br_id) where brlo_id = ?',array('i', $params['srcbr']['location']['id']),true);
					$params['srcbr']['id'] = $rs['br_id'];
					$params['srcbr']['name'] = $rs['br_name'];
					$params['srcbr']['lati'] = $rs['br_Lati'];
					$params['srcbr']['long'] = $rs['br_Lng'];
					//print_r($params['srcbr']['location']['id']);
					$rs  = $this->db->getFromDB('select branch.br_id as br_id,branch.br_Lati as br_Lati,branch.br_Lng as br_Lng,br_name from  branch_location  inner join  branch  using (br_id) where brlo_id = ?',array('i',($params['dstbr']['location']['id'] )),true);
					
					$params['dstbr']['id'] = $rs['br_id'];
					$params['dstbr']['name'] = $rs['br_name'];
					$params['dstbr']['lati'] = $rs['br_Lati'];
					$params['dstbr']['long'] = $rs['br_Lng'];
					}else{
					
					$params['srcbr']['id'] = $resppickupAddress['SrcBrId'];
					$params['srcbr']['name'] = $resppickupAddress['SrcBrName'];
					$params['srcbr']['lati'] = $resppickupAddress['SrcBrLat'];
					$params['srcbr']['long'] = $resppickupAddress['SrcBrLong'];
					
					$params['dstbr']['id'] = $respdeliveryAddress['DstBrId'];
					$params['dstbr']['name'] = $respdeliveryAddress['DstBrName'];
					$params['dstbr']['lati'] = $respdeliveryAddress['DstBrLat'];
					$params['dstbr']['long'] = $respdeliveryAddress['DstBrLong'];		 
				}	
				$params['loadtype']=1;
				$params['adddetails']=0;
				$params['branchratesonly']=0;
				$params['pkttype']=0;
				$params['cmpid']=1;
				
				//get Route and Distance
				if($HasDetails===false){
					if(!$this->getRouteDistance($params)){
						exit('didnt getRouteDistance');
					}
					}else{
				    $params['distance'] = $HasDetails['TotalDistKM'];
				}
				
				//get the rate master
				
				if(!$this->getRateMaster($params)){
					exit('didnt getRateMaster');
				}
				
				//Get the rate Heads
				if(!$this->getRateHeads($params)){
					exit('didnt getRateHeads');
				}
				
				$itr =0;
				$params['TotalFreightAmt']=0;
				$params['TotalWeight']=0;
				$params['TotalVolume']=0;
				$params['TotalChargWt']=0;
				$params['TotalPkts']=0;
				$params['TotalBaseRate']=0;		
				$params['TotalSettingsRate']=0;					 
				$params['TotINVAmt']=$respConsignment['goodsworth'] ;
				$params['consg']=array();
				foreach ($respConsignment['details'] as $k => $consignment) { 
					//Get getConsignmentCharge
					array_push($params['consg'],$consignment);
					if(!$this->getConsignmentCharge($params,$itr)){
						exit('didnt getConsignmentCharge');
					}
					$itr++;
				}
				file_put_contents('php://stderr', " Your params \n");
				file_put_contents('php://stderr', print_r($params, TRUE));			
				$params['source'] = $resppickupAddress;
				$params['destination'] = $respdeliveryAddress;			 
				$params['doorcoll']=$this->getAdditionalCharge($params,7);
				$params['doordeli']=$this->getAdditionalCharge($params,8);			 
				$params['delicharges']=$params['doorcoll']+$params['doordeli'];
				if(!$this->getTax($params)){
					exit('didnt getTax');
				}		 
				$params['grsamt'] = ($params['TotalFreightAmt']+ $params['delicharges']);
				$params['netamt'] = ($params['TotalFreightAmt']+ $params['delicharges']+$params['taxamt']);
				$params['roundoff'] = $params['netamt'] - round($params['TotalFreightAmt']+ $params['delicharges']+$params['taxamt'],0) ;		 
				/*$arrSession =array();
					$arrSession['success']=true;
					$arrSession['msg'] = 'Charges for booking session';	
				$arrSession['Data']['charges'] = $arrCharges;		*/		
				
				return  $params;
				
			}	
			//Get Distance between two cordinates
			private function GetDrivingDistance($lat1, $lat2, $long1, $long2){
				$url = "https://maps.googleapis.com/maps/api/distancematrix/json?key=" .GMAP_DIST_API_KEY . "&origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2;
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				$response = curl_exec($ch);
				curl_close($ch);
				$response_a = json_decode($response, true);
				
				$dist = $response_a['rows'][0]['elements'][0]['distance']['value'];
				if((float)$dist==0){
					file_put_contents('php://stderr', print_r("ERRORRRRRRR in DISTCANCE ". GMAP_DIST_API_KEY,TRUE));
					file_put_contents('php://stderr', print_r($response_a,TRUE));
					
					}
				$db = new \cgoSqlDB();
				$db->query("INSERT INTO  googleapiscount(dt,typ,useby,`count`) VALUES (?,?,?,?)  ON DUPLICATE KEY UPDATE `count`=`count`+1",array('ssss', date('Ymd'),'distancematrix','api/charges',1));
				return round($dist/1000,2);
			}
			//Get the Route and total distance
			private function getRouteDistance(&$params,$HasDetails=false){
				
				if ($params['srcbr']['id']==$params['dstbr']['id'] ){
					$intrabranch = true;
					}else{
					$intrabranch = false;
				}		
				//echo $params['srcbr']['id']. ' -- ' . $params['dstbr']['id'] . ' -- ' .			 $intrabranch;
				$params['srcbr']['location']['dist']=0;
				$params['dstbr']['location']['dist'] =0;
				$params['gmap']['route']= array();
				if(intval( $params['srcbr']['location']['id'])>0){
					$rs = $this->db->getFromDB('select brlo_Distance,brlo_Lati,brlo_Long from  branch_location where brlo_id = ?',array('i',($params['srcbr']['location']['id'])),true);
					$params['srcbr']['location']['dist'] =$rs['brlo_Distance'];
					$params['srcbr']['location']['lati'] =$rs['brlo_Lati'];
					$params['srcbr']['location']['long'] =$rs['brlo_Long'];	
					
					if (isset($params['srcbr']['location']['cust']['coord'])){
						
						$dist = $this->GetDrivingDistance($params['srcbr']['location']['cust']['coord']['latitude'],$params['srcbr']['location']['lati'],$params['srcbr']['location']['cust']['coord']['longitude'],$params['srcbr']['location']['long']);
						$params['srcbr']['location']['cust']['dist']=$dist;				
						
						array_push($params['gmap']['route'],array('latitude'=>$params['srcbr']['location']['cust']['coord']['latitude'],'longitude'=>$params['srcbr']['location']['cust']['coord']['longitude']));
						
						$dist = $this->GetDrivingDistance($params['srcbr']['location']['cust']['coord']['latitude'],$params['srcbr']['lati'],$params['srcbr']['location']['cust']['coord']['longitude'],$params['srcbr']['long']);
						$params['cust']['srcbr']['dist']=$dist;				
						}else{	
						$params['srcbr']['location']['cust']['dist'] =0;
						$params['cust']['srcbr']['dist'] =0;
						array_push($params['gmap']['route'],array('latitude'=>$params['srcbr']['location']['lati'],'longitude'=>$params['srcbr']['location']['long']));		
					}	
				}
				if(!$intrabranch){
					array_push($params['gmap']['route'],array('latitude'=>$params['srcbr']['lati'],'longitude'=>$params['srcbr']['long']));	
					array_push($params['gmap']['route'],array('latitude'=>$params['dstbr']['lati'],'longitude'=>$params['dstbr']['long']));		
				}
				if(intval($params['dstbr']['location']['id'] )>0){
					$rs = $this->db->getFromDB('select brlo_Distance,brlo_Lati,brlo_Long from  branch_location where brlo_id = ?',array('i',($params['dstbr']['location']['id'])),true);				
					$params['dstbr']['location']['dist'] =$rs['brlo_Distance'];
					$params['dstbr']['location']['lati'] =$rs['brlo_Lati'];
					$params['dstbr']['location']['long'] =$rs['brlo_Long'];						
					if (isset($params['dstbr']['location']['cust']['coord'])){
						$dist = $this->GetDrivingDistance($params['dstbr']['location']['cust']['coord']['latitude'],$params['dstbr']['location']['lati'],$params['dstbr']['location']['cust']['coord']['longitude'],$params['dstbr']['location']['long']);
						$params['dstbr']['location']['cust']['dist']=$dist;		
						array_push($params['gmap']['route'],array('latitude'=>$params['dstbr']['location']['cust']['coord']['latitude'],'longitude'=>$params['dstbr']['location']['cust']['coord']['longitude']));
						
						$dist = $this->GetDrivingDistance($params['dstbr']['location']['cust']['coord']['latitude'],$params['dstbr']['lati'],$params['dstbr']['location']['cust']['coord']['longitude'],$params['dstbr']['long']);
						$params['cust']['dstbr']['dist'] =$dist;
						
						}else{
						$params['cust']['dstbr']['dist'] =0;
						$params['dstbr']['location']['cust']['dist'] =0;
						array_push($params['gmap']['route'],array('latitude'=>$params['dstbr']['location']['lati'],'longitude'=>$params['dstbr']['location']['long']));	
					}
				}
				if($intrabranch){
					
					$params['distance'] = $this->GetDrivingDistance($params['gmap']['route'][0]['latitude'],$params['gmap']['route'][1]['latitude'],$params['gmap']['route'][0]['longitude'],$params['gmap']['route'][1]['longitude']);
					}else{
					$params['distance'] = $this->db->getItemFromDB('select distance from  brrate where (sbr_id=? and dbr_id=? ) or (sbr_id=? and dbr_id=?)',array('iiii',$params['srcbr']['id'],$params['dstbr']['id'] ,$params['dstbr']['id'] ,$params['srcbr']['id']),true);
					$params['distance'] = $params['srcbr']['location']['dist'] + $params['dstbr']['location']['dist'] + $params['srcbr']['location']['cust']['dist'] + $params['dstbr']['location']['cust']['dist'] + $params['distance'] ;
				}
				return true;
			}
			//Get the rate id, rate cost basis and rate factor 
			public function getRateMaster(&$params){
				
				$params['rateid'] = $this->db->getItemFromDB('select  getRateId(?,?,?,?,?,?,?,?,?,?) as gri',array('iiiiiiiiii',$params['cmpid'],$params['cust']['id'], $params['cust']['brid'],$params['srcbr']['id'],$params['dstbr']['id'] , $params['srcbr']['location']['id'],$params['dstbr']['location']['id'] ,$params['loadtype'],$params['adddetails'],$params['branchratesonly']),true);
				
				$params['ratelevel'] = $this->db->getItemFromDB('select coalesce(@RateLevel,0) as ratelevel',array(),true);
				$ratedetails = $this->db->getFromDB('select c_ID,raty_CostBasisId,raty_MinDist,raty_DistSlab from  ratetype where raty_id = ?',array('i',$params['rateid']),true);
				if($ratedetails['raty_CostBasisId']	==1){
					$params['rahd_costfactorid'] = $this->db->getItemFromDB('select rahd_CostFactorId from  rateheadsdetails where rahd_FromBrId = ? and rahd_FromBrIsRemote =  ? and rahd_ToBrId = ? and rahd_ToBrIsRemote = ? and raty_id = ? and  raty_LoadTypeId = ? and  raty_LoadDetsId = ? limit 1',array('iiiiiii', ( $params['srcbr']['location']['id'] > 0? $params['srcbr']['location']['id'] : $params['srcbr']['id']),( $params['srcbr']['location']['id'] > 0? 1 :0),($params['dstbr']['location']['id']  > 0?$params['dstbr']['location']['id'] : $params['dstbr']['id'] ),($params['dstbr']['location']['id']  > 0? 1 : 0), $params['rateid'], $params['loadtype'], $params['adddetails']),true);         
				}
				else{
					$params['rahd_costfactorid'] = $this->db->getItemFromDB('select rahd_CostFactorId from  rateheadsdetails where raty_id = ? and  raty_LoadTypeId = ? and  raty_LoadDetsId = ? limit 1',array('iii',$params['rateid'],$params['loadtype'],$params['adddetails']),true);
				}
				$params['raty_costbasisid'] = $ratedetails['raty_CostBasisId'];
				$params['raty_MinDist'] = $ratedetails['raty_MinDist'];
				$params['raty_DistSlab'] = $ratedetails['raty_DistSlab'];
				return true;	
			}
			//Get the rate Heads
			public function getRateHeads(&$params){		
				
				$rateheads = $this->db->getMulipleData('call  getBookChargeHeadRates(?,?,?,?,?,?,?,?,?) ',array('iiiiiiiii',$params['srcbr']['id'],$params['dstbr']['id'] ,$params['rateid'],$params['raty_costbasisid'],$params['rahd_costfactorid'], $params['srcbr']['location']['id'],$params['dstbr']['location']['id'] ,$params['loadtype'],$params['adddetails']),true);
				$params['rateheads'] = $rateheads;
				return true;			
			}
			//Get consignment charge
			public function getConsignmentCharge(&$params,$arr){
				//print_r($params['consg'][$arr]);
				//Convert to Kilograms
				if ($params['consg'][$arr]['weight_unit']=='Grams'){
					$params['consg'][$arr]['weight'] = round($params['consg'][$arr]['weight']/1000,3);
					$params['consg'][$arr]['weight_unit']='Kilograms';
				}
				//Convert to Feet from meter
				if ($params['consg'][$arr]['size_unit']=='Meter'){
					$params['consg'][$arr]['size_length'] = round($params['consg'][$arr]['size_length']/3.28084,2);
					$params['consg'][$arr]['size_breadth'] = round($params['consg'][$arr]['size_breadth']/3.28084,2);
					$params['consg'][$arr]['size_height'] = round($params['consg'][$arr]['size_height']/3.28084,2);
					$params['consg'][$arr]['size_unit']='Feet';
				}	
				//Convert to Feet  from Centimeter			
				if ($params['consg'][$arr]['size_unit']=='Centimeter'){
					$params['consg'][$arr]['size_length'] = round($params['consg'][$arr]['size_length']*0.0328084,2);
					$params['consg'][$arr]['size_breadth'] = round($params['consg'][$arr]['size_breadth']*0.0328084,2);
					$params['consg'][$arr]['size_height'] = round($params['consg'][$arr]['size_height']*0.0328084,2);
					$params['consg'][$arr]['size_unit']='Feet';
				}
				//Convert to Feet  from Inches			
				if ($params['consg'][$arr]['size_unit']=='Inches'){
					$params['consg'][$arr]['size_length'] = round($params['consg'][$arr]['size_length']*0.0833333,2);
					$params['consg'][$arr]['size_breadth'] = round($params['consg'][$arr]['size_breadth']*0.0833333,2);
					$params['consg'][$arr]['size_height'] = round($params['consg'][$arr]['size_height']*0.0833333,2);
					$params['consg'][$arr]['size_unit']='Feet';
				}	
				//Calculate volume
				$params['consg'][$arr]['vol']=round($params['consg'][$arr]['size_length']*$params['consg'][$arr]['size_breadth']*$params['consg'][$arr]['size_height'],0)*$params['consg'][$arr]['count'];
				$params['consg'][$arr]['weight'] = $params['consg'][$arr]['weight'] * $params['consg'][$arr]['count'];
				//Calculate Charge Weight
				$chargerwt = $this->db->getItemFromDB("select  getChargerWt( ?,?,?,?) as getchrgerwt",array('iddi',$params['cmpid'],$params['consg'][$arr]['weight'],$params['consg'][$arr]['vol'],$params['ratelevel']),true);  
				
				$params['rahd_costattributeid'] = 0;
				//If rate is calculated on the basis of charge weight
				if ($params['rahd_costfactorid']==3){
					$this->db->query("CALL  getRateOfChargeHead(?,?,?,?,?,?,1,@j1,@LclFxdMinQty,@LclFxdSlabQty,@j2,@j3,?,?,?,?)",array('iiiiiiiiii',$params['srcbr']['id'],$params['dstbr']['id'] ,$params['rateid'],$params['raty_costbasisid'],$params['rahd_costfactorid'],$params['rahd_costattributeid'], $params['srcbr']['location']['id'],$params['dstbr']['location']['id'] ,$params['loadtype'],$params['adddetails']),true);		
					
					$SlabQty = $this->db->getFromDB("select CAST(coalesce(@LclFxdMinQty,0)AS UNSIGNED) as MinQty,CAST(coalesce(@LclFxdSlabQty,0)AS UNSIGNED) as SlabQty",array(),true);	
					
					
					$chargerwt = $this->getSlabValue($chargerwt,($SlabQty['MinQty']),($SlabQty['SlabQty']),($SlabQty['MinQty']),($SlabQty['MinQty']) + ($SlabQty['SlabQty']),$lngSlabLevel);
				}
				$params['consg'][$arr]['chrgwt'] = $chargerwt;
				if ($params['rahd_costfactorid']==4 && $params['pkttype']==1 ){
					$params['rahd_costattributeid'] = $params['pktid'];
				}
				else{
					$params['rahd_costattributeid'] =0;
				}
				$this->db->query("set @appcalculateddist = ?", array('d',floatval($params['distance'])),true);
				
				$FreightAmt =  $this->db->getItemFromDB("select  getRate(?,?,?,?,?,?,?,?,?,?,0,?,?,?,?,1,0,?,?,?,?,?) as getrate" ,array('ddddiiiiidiiiiiiiii',$params['consg'][$arr]['weight'],$params['consg'][$arr]['vol'],$params['consg'][$arr]['count'],0.00,$params['dstbr']['id'] ,$params['srcbr']['id'], $params['cust']['id'], $params['cust']['id'],$params['cmpid'],$params['consg'][$arr]['chrgwt'],$params['rateid'],$params['raty_costbasisid'],$params['rahd_costfactorid'],$params['rahd_costattributeid'],$params['adddetails'],$params['ratelevel'], $params['srcbr']['location']['id'],$params['dstbr']['location']['id'] ,$params['loadtype']),true);
				
				$baserate =  $this->db->getItemFromDB("select coalesce(@baseRate,0) as baseRate" ,array(),true);			
				$SettingsbaseRate =  $this->db->getItemFromDB("select coalesce(@SettingsbaseRate,0) as SettingsbaseRate" ,array(),true);
				
				$params['consg'][$arr]['FreightAmt'] = $FreightAmt;
				$params['consg'][$arr]['baserate'] = $baserate;
				$params['consg'][$arr]['SettingsbaseRate'] = $SettingsbaseRate;
				
				$params['TotalFreightAmt']=$params['consg'][$arr]['FreightAmt']+$params['TotalFreightAmt'];
				$params['TotalWeight']=$params['consg'][$arr]['weight']+$params['TotalWeight'];
				$params['TotalVolume']=$params['consg'][$arr]['vol']+$params['TotalVolume'];
				$params['TotalChargWt']=$params['consg'][$arr]['chrgwt']+$params['TotalChargWt'];
				$params['TotalBaseRate']=$params['consg'][$arr]['baserate']+$params['TotalBaseRate'];		
				$params['TotalSettingsRate']=$params['consg'][$arr]['SettingsbaseRate']+$params['TotalSettingsRate'];		
				$params['TotalPkts']=$params['consg'][$arr]['count']+$params['TotalPkts'];
				
				return true;
			}
			
			private function getAdditionalCharge(&$params,$ChrgId){			
				
				$key = array_search($ChrgId, array_column($params['rateheads'], 'comh_id'));
				if($key==false){
					return 0;
				}			
				$parentid = $params['rateheads'][$key]['Parent'] ;
				$calctype = $params['rateheads'][$key]['Rtype'] ;
				$rate = $params['rateheads'][$key]['Rate'] ;
				//print_r($params['rateheads'][$key]);
				//echo $key . ' - ' . $rate. ' - ' . $calctype . ' - ' . $parentid;
				if ($calctype==1){
					switch($ChrgId) {
						case 1:
						return 0;
						break;					
						case 9:					
						case 10:	
						$calculateCharges = 0;	
						break;
						default:
						$calculateCharges = round(($this->getParentCharge($params,$parentid) * $rate) / 100, 2);
						break;
					}
				}
				else{
					$calculateCharges = $this->getFixedCharge($params,$key);
				}
				return $calculateCharges;
			}
			private function getParentCharge($params,$parentId){
				
				switch($parentId) {
					case 1:
					return $params['TotalFreightAmt'];
					break;
					default:
					return 0;
					break;
				}	
			}
			
			private function getFixedCharge($params,$key){
				$dblAmtToBeCalc = $this->getRateBasedOnTotal($params,$params['rateheads'][$key][4]);
				$dblAmtToBeCalc = $this->getSlabValue($dblAmtToBeCalc,$params['rateheads'][$key][5],$params['rateheads'][$key][6],$params['rateheads'][$key][5],$params['rateheads'][$key][5] + $params['rateheads'][$key][6],$lngSlabLevel);
				$dblMinRate =  $params['rateheads'][$key][7];
				$dblRateSlab = $params['rateheads'][$key][8];
				$FxdChrg = 	$lngSlabLevel * $dblRateSlab;
				if ($dblMinRate == 0) $dblMinRate = $dblRateSlab;
				if ($dblMinRate > $FxdChrg) $FxdChrg = $dblMinRate;
				return $FxdChrg;
			}
			
			private function getRateBasedOnTotal($params,$typ){ 
				
				switch($typ) {
					case 1:
					$val = $params['TotalWeight'];
					break;
					case 2:
					$val =  $params['TotalVolume'];
					break;					
					case 3:		
					$val = $params['TotalChargWt'];
					break;
					case 4:					
					$val = $params['TotalPkts'];
					break;
					case 5:
					$val = $params['TotalFreightAmt'];
					break;
					default:
					$val = 0;
				}
				return $val;
			}
			
			private function getSlabValue($currentval,$dblMinQty,$dblQtySlab,$dblLower,$dblHigher,&$lngSlabLevel){
				
				if($currentval<=$dblLower){
					$currentval = $dblLower;
				}
				else{
					if ($dblMinQty > 0 && $dblQtySlab > 0){
						$lngSlabLevel =0;
						while (true){
							$lngSlabLevel++;
							if ($currentval >= $dblLower && $currentval <= $dblHigher) {
								break ;
							}
							else{
								$dblLower = $dblHigher;
								$dblHigher = $dblHigher + $dblQtySlab;
							}
						}
						$currentval = $dblHigher;
					}
				}
				return $currentval;
			}
			
			private function getTax(&$params){
				
				$taxminimum = $this->db->getItemFromDB("select  getTaxApplicableAmt(1) as TaxMin",array(),true);
				$params['taxper']=0;
				if (($params['TotalFreightAmt'] +$params['delicharges'])>$taxminimum){					
					$taxpercent = $this->db->getItemFromDB("select  getSalesTax(1) as Tax",array(),true);
					$params['taxper'] = $taxpercent;
					if($params['cust']['taxable']==0){ 
						$params['taxamt'] = round((($params['TotalFreightAmt'] +$params['delicharges'])*$taxpercent)/100,2);
						}else{
						$params['taxamt'] = 0;
					}
					}else{
					$params['taxamt'] = 0;
				}
				return true;	
			}
			
		}
	}	