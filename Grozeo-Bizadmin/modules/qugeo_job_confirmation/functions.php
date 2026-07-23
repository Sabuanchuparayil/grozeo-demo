<?php

require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/includes/config.php');
function getVehiclesOfTheDay(){
	 global $db;
    $nodb = new \cgoDynamiteDB();
		
        $arrVehicleOrder = array();
        $arrVehicleOrder['PartitionKey'] = array('col' => 'createddate', 'val' => (int)str_replace('-','',$_POST['date']), 'oper' => '=');
        $arrVehicleOrder['SortKey'] = array('col' => 'HandlingBranch',  'val' =>  (int)$_POST['br_id'], 'oper' => '=');
        $arrVehicleOrder['IndexName'] = 'createddate-HandlingBranch-index';
        $arrVehicleOrder['queryAttributes'] = array('Acceptedapikey');
        $arrVehicleOrder['Condition'] = array();
        array_push($arrVehicleOrder['Condition'], array('col' => 'Acceptedapikey', 'type' => 'S', 'val' => '-', 'oper' => '<>'));     
        $rsno = $nodb->query('QugeoOrderDetails', $arrVehicleOrder, 'query');
        if (isset($rsno) && count($rsno) > 0) {
            $rs = array();
			 foreach ($rsno as $vehicleapi) {
				   array_push($rs, $vehicleapi['Acceptedapikey']);
			  }			 
		  
			$rs = array_unique($rs); 

			return $rs;
		}else{
			return null;
		}
}
function loadVehicleDetails() {
    global $db;
    $nodb = new \cgoDynamiteDB();
		
        $vehicles  = getVehiclesOfTheDay();
        if (count($vehicles) > 0) {
            $rs = array();			
            foreach ($vehicles as $value) {
                $arrVehicle = array();
                $arrVehicle['PartitionKey'] = array('col' => 'apikey',  'val' => (string)$value, 'oper' => '=');
                $arrVehicle['queryAttributes'] = array('apikey', 'v_no', 'createddatetime');
                $arrVehicle['Condition'] = array();
                array_push($arrVehicle['Condition'], array('col' => 'Is_Live', 'val' => 0, 'oper' => '='));
                $nors = $nodb->query('QugeoLiveVehicles', $arrVehicle, 'query');
                if (isset($nors) && count($nors) > 0) {
                        array_push($rs, array('v_no' =>$nors[0]['v_no'] . '_' . date("H:i:s", strtotime($nors[0]['createddatetime'])), 'v_id' =>$nors[0]['apikey']));
                }
            }
            $count = count($rs);
            $rs = json_encode($rs);
        } else {
            $count = 0;
            $rs = '[]';       
    }
	echo '{"totalCount":' . $count . ',"data":' . $rs . '}';
}

function loadJobdetails() {
    global $db;
	 $nodb = new \cgoDynamiteDB();
	if($_POST['vehicle_id']==""){
		 $vehicles  = getVehiclesOfTheDay();
		
        if (count($vehicles) > 0) {
            $vehicle = array();			
            foreach ($vehicles as $value) {
                $arrVehicle = array();
                $arrVehicle['PartitionKey'] = array('col' => 'apikey', 'val' => $value, 'oper' => '=');
                $arrVehicle['queryAttributes'] = array('apikey');
                $arrVehicle['Condition'] = array();
                array_push($arrVehicle['Condition'], array('col' => 'Is_Live', 'val' => 0, 'oper' => '='));
                $nors = $nodb->query('QugeoLiveVehicles', $arrVehicle, 'query');
				
                if (isset($nors) && count($nors) > 0) {
                        array_push($vehicle, $nors[0]['apikey']);
                }
            }
            //$count = count($rs);
            //$rs = json_encode($rs);
        } else {
            $vehicle =array();      
		}
	}elseif(strrpos($_POST['vehicle_id'], ",")==false){
		$vehicle = array($_POST['vehicle_id']);
    }else{
		$vehicle = explode(",", $_POST['vehicle_id']);
	}	
	
    $rs = array();
    foreach ($vehicle as $apikey) {

        $arrVehicles['PartitionKey'] = array('col' => 'apikey',  'val' => $apikey, 'oper' => '=');
        $arrVehicles['queryAttributes'] = array('apikey', 'IsPickup', 'v_no', 'Is_Live', 'createddatetime');       

		
        $rsno = $nodb->query('QugeoLiveVehicles', $arrVehicles, 'query');

        if (isset($rsno) && count($rsno) > 0) {
            foreach ($rsno  as $vehicleapi) {
                $arrVehicleOrder['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
                $arrVehicleOrder['queryAttributes'] = array('orderid');
		        if ($_POST['chk_box2'] == 'false') {
					 $arrVehicleOrder['Condition'] = array();
					array_push($arrVehicleOrder['Condition'], array('col' => 'IsPickup', 'val' => '1', 'oper' => '='));
				} elseif ($_POST['chk_box1'] == 'false') {
					$arrVehicleOrder['Condition'] = array();
					array_push($arrVehicleOrder['Condition'], array('col' => 'IsPickup',  'val' => '0', 'oper' => '='));
				}
                $vhordrs = $nodb->query('QugeoLiveVehicleOrders', $arrVehicleOrder, 'query');
                if (isset($vhordrs) && count($vhordrs) > 0) {
                    foreach ($vhordrs  as $value) {
                        $arrOrder['PartitionKey'] = array('col' => 'orderid', 'val' => $value['orderid'], 'oper' => '=');
                        $arrOrder['getAttributes'] = array('quor_RefNo', 'date', 'updateddatetime', 'pickuplocation', 'deliverylocation', 'netamt', 'paymode', 'HasReCalculatedCharges', 'ReCalculatedCharges', 'ReCalculcationPaymentType', 'quor_id', 'orderid','IsPickup','OrderStatus');
                        $ordrs = $nodb->query('QugeoOrderDetails', $arrOrder, 'getItem');
						
                        if (isset($ordrs) && count($ordrs) > 0) {
                            $qry = "select quor_Date,quor_Status,quor_QugeoPickupDDBOrderId,quor_QugeoDeliveryDDBOrderId,quor_UpdateOn,"
                                    . "(select dls_DelStatus from  qugeo_deliverystatus where qugeo_deliverystatus.dls_ID = qugeo_order.quor_Status) as status, quor_Status as dls_id,"
                                    . "quor_Paymode,quor_AmountCollectible,coalesce(quor_ItemReturned,'[]') as quor_ItemReturned from  qugeo_order where quor_id =  " . $ordrs['quor_id'] ;
                            $booking = $db->getFromDB($qry, true);
                            //print_r($booking);
                            if ($ordrs['IsPickup'] == 1) {
                                if ($booking['quor_QugeoPickupDDBOrderId'] != $ordrs['orderid']) {
                                    $editable = false;
                                } else {
                                    switch ($booking['quor_Status']) {
                                        case ORDER_PICKUP_AT_ORIGIN_DLS_ID:
                                        case ORDER_PICKUP_PICKEDUP_TOBR_DLS_ID:
                                        case ORDER_PICKUP_PICKEDUP_TODST_DLS_ID:
                                        case ORDER_PICKUP_FAILED_DOOR_LOCKED_DLS_ID:
                                        case ORDER_PICKUP_FAILED_ADDRESS_NOT_FOUND_DLS_ID:
                                        case ORDER_PICKUP_FAILED_PARCEL_NOT_READY_DLS_ID:
                                            $editable = true;
                                            break;
                                        case ORDER_PICKUP_FLAGGED_TODST_DLS_ID:
                                        case ORDER_PICKUP_FLAGGED_TOBR_DLS_ID:
                                            if ($vehicleapi['Is_Live'] == 1) {
                                                $editable = false;
                                            } else {
                                                $editable = true;
                                            }
                                            break;
                                        default:
                                            $editable = false;
                                    }
                                }
                            } else {
                                if ($booking['quor_QugeoDeliveryDDBOrderId'] != $ordrs['orderid']) {
                                    $editable = false;
                                } else {
                                    switch ($booking['quor_Status']) {
                                        case ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID:
                                        case ORDER_DELIVERY_FAILED_DOOR_LOCKED_DLS_ID:
                                        case ORDER_DELIVERY_FAILED_REFUSED_DLS_ID:
                                        case ORDER_DELIVERY_FAILED_ADDRESS_NOT_FOUND_DLS_ID:
                                        case ORDER_DELIVERY_FAILED_DAMAGED_DLS_ID:
                                        case ORDER_DELIVERY_MARKED_DLS_ID:
                                            $editable = true;
                                            break;
                                        case ORDER_DELIVERY_OUT_FOR_DELIVERY:
                                            if ($vehicleapi['Is_Live'] == 1) {
                                                $editable = false;
                                            } else {
                                                $editable = true;
                                            }
                                            break;
                                        default:
                                            $editable = false;
                                    }
                                }
                            }
							$qry = "select dls_DelStatus from qugeo_deliverystatus where qugeo_deliverystatus.dls_ID =" . $ordrs['OrderStatus'];
							$closingstaus = $db->getItemFromDB($qry, true);
                            array_push($rs, array('orderid' => $ordrs['orderid'], 'date' => $booking['quor_Date'], 'quor_RefNo' => $ordrs['quor_RefNo'], 'From' => $ordrs['pickuplocation'], 
                                'To' => $ordrs['deliverylocation'], 'NetAmt' => $ordrs['netamt'], 'Paymode' => $booking['quor_Paymode'], 'HasReCalculatedCharges' => $ordrs['HasReCalculatedCharges'], 
                                'ReCalculatedCharges' => ($ordrs['HasReCalculatedCharges'] == 1 ? $ordrs['ReCalculatedCharges'] : 0), 
                                'ReCalculcationPaymentType' => ($ordrs['HasReCalculatedCharges'] == 1 ? $ordrs['ReCalculcationPaymentType'] : -1), 
                                'IsPickup' => $ordrs['IsPickup'], 'status' => $booking['status'], 'closing_status' => $closingstaus,'IsEditable' => $editable, 
                                'v_no' => $vehicleapi['v_no'] . '_' . date("H:i:s", strtotime($vehicleapi['createddatetime'])), 'BkLastEditTime' => $booking['quor_UpdateOn'], 'quor_id' => $ordrs['quor_id'],
                                'status_time'=>date('Y-m-d H:i:s', strtotime($ordrs['updateddatetime'])),'dls_id'=>$booking['dls_id'],'quor_AmountCollectible' => $booking['quor_AmountCollectible'],
                                'quor_ItemReturned' => $booking['quor_ItemReturned']));
                        }
                    }
                }
            }
        }
    }
    echo '{"totalCount":' . count($rs) . ',"data":' . json_encode($rs) . ',"datetime":"' . date('Y-m-d H:i:s') .'"  }';
}
