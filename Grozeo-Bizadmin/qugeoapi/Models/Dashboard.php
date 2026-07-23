<?php
	namespace Models;
	{
		class Dashboard extends ModelAbstract
		{
			public function GET_orderqueue($flag,$request){		

				$orderhandler = new QugeoOrderHandler();
				$orderdetails = $orderhandler->GetOrdersList($request['apikey'],2);
				
				$orderqueue['success']=true;	
				$orderqueue['msg'] = 'Order Details';	
				$dets = array();
				foreach($orderdetails as $orders){
					array_push($dets,array("bookno"=>$orders['bkno'],"location"=>$orders['locationname'],"ordertype"=>$orders['ordertype']));
				}
				$orderqueue['Data']['orderqueue'] = $dets;	
				return $orderqueue;
			}
			public function GET_pickuporders($flag,$request){		 
				$orderhandler = new QugeoOrderHandler();
				$orderdetails = $orderhandler->GetOrdersList($request["apikey"],3);
				$orderqueue['success']=true;	
				$orderqueue['msg'] = 'Order Details';	
				$dets = array();
				foreach($orderdetails as $orders){
					array_push($dets,array("bookno"=>($orders['IsFailed']==true?"**":"").$orders['bkno'],"location"=>$orders['locationname'],"CompletedAt"=>$orders['CompletedAt']));
				}
				$orderqueue['Data']['pickuporders'] = $dets;	
				return $orderqueue;
			}	
			public function GET_deliveryorders($flag,$request){		 
				$orderhandler = new QugeoOrderHandler();
				$orderdetails = $orderhandler->GetOrdersList($request["apikey"],4);
				$orderqueue['success']=true;	
				$orderqueue['msg'] = 'Order Details';			
				$dets = array();
				foreach($orderdetails as $orders){
					array_push($dets,array("bookno"=>($orders['IsFailed']==true?"**":"").$orders['bkno'],"location"=>$orders['locationname'],"CompletedAt"=>$orders['CompletedAt']));
				}				
				$orderqueue['Data']['deliveryorders'] = $dets;	
				return $orderqueue;
			}
			public function GET_cashinhand($flag,$request){	
				$response =array();		
				$response['success']=true;	
				$response['msg'] = 'Cash In Hand';			
				$response['Data']['cashinhand'] = "150.00";				
				return $response;			
			}
			public  function GET_myearnings($flag,$request){	
				$response =array();		
				$response['success']=true;	
				$response['msg'] = 'My Earnings';			
				$response['Data']['earnings'] = array("pickup"=>"1500","delivery"=>"500");				
				return $response;			
			}		
		}
	}	