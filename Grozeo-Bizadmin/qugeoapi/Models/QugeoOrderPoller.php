<?php

namespace Models; {

    class QugeoOrderPoller
    {

        public function __construct()
        {
            $this->db = new \sqlDb(DSN);
        }

        private function WasQueued($orderid, $apikey, $isPickup)
        {
            $nodb = new \cgoDynamiteDB();
            $arrVehicle = array();
            $arrVehicle['PartitionKey'] = array('col' => 'orderid', 'val' => $orderid, 'oper' => '=');
            $arrVehicle['SortKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
            $arrVehicle['IndexName'] = 'orderid-apikey-index';
            $arrVehicle['queryAttributes'] = array('orderid');
            $arrVehicle['Condition'] = array();
            array_push($arrVehicle['Condition'], array('col' => 'ispickup', 'val' => (int) $isPickup, 'oper' => '='));
            $nodb = new \cgoDynamiteDB();
            $rsno = $nodb->query('QugeoOrderPollingDetails', $arrVehicle, 'query');
            $vehicledetails = array();
            if (isset($rsno) && count($rsno) > 0) {
                return count($rsno);
            } else {
                return 0;
            }
        }

        private function getBestQugeoCandidate($vehicledetails, $orderid, $isPickup, $ignorepreviouspushes)
        {
            //$dist = array();
            $currentload = array();
            $RatePerKm = array();
            $Capacity = array();
            foreach ($vehicledetails as $key => $row) {
                if (!$ignorepreviouspushes) {
                    if ($this->WasQueued($orderid, $row['apikey'], $isPickup) > 0) {
                        echo "Yes this " . $orderid . " was queued " . $row['apikey'] . "\n";
                        unset($vehicledetails[$key]);
                    } elseif ($this->HasLivePoll($row['apikey'])) {
                        echo " THis guy has live poll " . $row['apikey'] . "\n";
                        unset($vehicledetails[$key]);
                    } else {
                        //$dist[$key] = $row['distance'];		
                        $currentload[$key] = $row['CurrentLoadedWeight'];
                        $RatePerKm[$key] = $row['RatePerKm'];
                        $Capacity[$key] = $row['capacity'];
                    }
                }
            }
            if (!empty($vehicledetails)) {
                //array_multisort($dist, SORT_ASC, $vehicledetails);
                finascop_aasort($vehicledetails, 'distance');
                return $vehicledetails[0];
            } else {
                return false;
            }

            //More than 20 open orders?
            //Single Load	
            //Is Engaged and this order is much(previously configured) far from current order
            //Tonnage - Current Weight
            //Coverage - Current Volume
            //Rate * Distance
            //Proximity
            //Order processed today
            //Driver rating
        }

        private function getQugeoCandidates($orderLat, $orderLong, $driversofbranch = 0)
        {
            $nodb = new \cgoDynamiteDB();
            $degMat = new \cgoGeoUtilities();
            file_put_contents('php://stderr', "....................................getQugeoCandidates....driversofbranch.................................................. \n");
            file_put_contents('php://stderr', print_r($driversofbranch, TRUE));
            $arrDegrees = $degMat->getDegreeMatrix($orderLong, $orderLat, QC_VEHICLE_NEAR_PICKUP_CIRCLE_DIST);
            $arrVehicle = array();

            $arrVehicle['PartitionKey'] = array('col' => 'Is_Live', 'val' => 1, 'oper' => '=');
            $arrVehicle['SortKey'] = array('col' => 'Latitude', 'val1' => (float) $arrDegrees['lat1'], 'val2' => (float) $arrDegrees['lat2'], 'SortKeyBetween' => true);
            //$arrVehicle['SortKey']=array('col'=>'Latitude','val'=>$arrDegrees['lat1'],'oper'=>'=');				
            $arrVehicle['IndexName'] = 'Is_Live-Latitude-index';
            $arrVehicle['queryAttributes'] = array('apikey', 'v_id', 'v_no', 'Latitude', 'Longitude', 'v_capacity', 'CurrentLoadedWeight', 'RatePerKm', 'AWS_SNS_ARN', 'DeliveryRange', 'IsEngaged', 'MarkedNextBkId', 'MarkedNextBrId', 'FCM_ID', 'DriverPhone', 'DriverBranchId', 'createdBy', 'sourceId', 'Home_Latitude', 'Home_Longitude');
            $arrVehicle['Condition'] = array();
            array_push($arrVehicle['Condition'], array('col' => 'isallowAutoSchedule', 'val' => (int)1, 'oper' => '='));
            array_push($arrVehicle['Condition'], array('col' => 'Longitude', 'val1' => (float) $arrDegrees['lon1'], 'val2' => (float) $arrDegrees['lon2'], 'ConditionBetween' => true));
            if (QUGEO_SINGLE_JOB_MODE == 1) {
                array_push($arrVehicle['Condition'], array('col' => 'IsEngaged', 'val' => 0, 'oper' => '='));
            }
            if ($driversofbranch > 0) { //storeGroupId
                array_push($arrVehicle['Condition'], array('col' => 'createdBy', 'val' => (int)1, 'oper' => '='));
                array_push($arrVehicle['Condition'], array('col' => 'sourceId', 'val' => (int)$driversofbranch, 'oper' => '='));
            } else {
                array_push($arrVehicle['Condition'], array('col' => 'createdBy', 'val' => (int)1, 'oper' => '>'));
                array_push($arrVehicle['Condition'], array('col' => 'sourceId', 'val' => (int)0, 'oper' => '>'));
            }
            $nodb = new \cgoDynamiteDB();
            //print_r($arrVehicle);
            file_put_contents('php://stderr', print_r($arrVehicle, TRUE));
            $rsno = $nodb->query('QugeoLiveVehicles', $arrVehicle, 'query');
            file_put_contents('php://stderr', "....................................getQugeoCandidates2...................................................... \n");
            file_put_contents('php://stderr', print_r($rsno, TRUE));
            $vehicledetails = array();
            if (isset($rsno) && count($rsno) > 0) {
                foreach ($rsno as $value) {
                    $dist = $degMat->GetDrivingDistance($orderLat, $value['Latitude'], $orderLong, $value['Longitude']);
                    file_put_contents('php://stderr', print_r($dist, TRUE));
                    $coveragedist = $degMat->GetDrivingDistance($orderLat, $value['Home_Latitude'], $orderLong, $value['Home_Longitude']);
                    file_put_contents('php://stderr', "...coveragedist......... \n");
                    file_put_contents('php://stderr', print_r($coveragedist, TRUE));
                    $DeliveryRange = $value['DeliveryRange'];
                    file_put_contents('php://stderr', "...DeliveryRange......... \n");
                    file_put_contents('php://stderr', print_r($DeliveryRange, TRUE));
                    if ($coveragedist <= $DeliveryRange) {
                        array_push($vehicledetails, array('apikey' => $value['apikey'], 'v_No' => $value['v_no'], 'distance' => $dist, 'capacity' => $value['v_capacity'], 'CurrentLoadedWeight' => $value['CurrentLoadedWeight'], 'RatePerKm' => $value['RatePerKm'], 'AWS_SNS_ARN' => $value['AWS_SNS_ARN'], 'DeliveryRange' => $value['DeliveryRange'], 'FCM_ID' => $value['FCM_ID'], 'DriverPhone' => $value['DriverPhone']));
                        echo "Distance " . $dist . "\n";
                    }
                }
                return $vehicledetails;
            } else {
                //get Delivery rule of order check it has own delivery rule or grozeo delivery rule
                return false;
            }
        }

        public function CreateAPoll($orderid, $isPickup, $order, $specificcandidate = '', $quorIds, $totalOrders, $currentOrderCount, $quor_id)
        {
            file_put_contents('php://stderr', "....................................CreateAPoll..quor_id...................................................... \n");
            file_put_contents('php://stderr', print_r($quor_id, TRUE));
            $delLocations = $this->db->getFromDB("SELECT quor_id,quor_RefNo,quor_PickupPincode,quor_PickupLat,quor_PickupLng,quor_PickupLocation,quor_DeliveryPincode,quor_DeliveryLat,quor_DeliveryLng,quor_DeliveryLocation,quor_DeliveryAddress,"
                . "quor_Pickupbr_id,quor_Deliverybr_id,quor_QugeoPickupDDBOrderId,quor_TransferOrder_Type,fstr_id FROM  qugeo_order  
                INNER JOIN finascop_stock_transfer_order ON quor_TransferOrder_id = fsto_id 
                 WHERE quor_id = '{$quor_id}'", true);
				 file_put_contents('php://stderr', "....................................CreateAPoll..delLocations...................................................... \n");
            file_put_contents('php://stderr', print_r($delLocations, TRUE));
                 if($delLocations['quor_TransferOrder_Type'] == '1'){
$orderDetails = $this->db->getFromDB("SELECT delivery_rule_id,delivery_rule_type FROM retaline_customer_order WHERE order_id = {$delLocations['fstr_id']}",true);
file_put_contents('php://stderr', "....................................CreateAPoll..orderDetails...................................................... \n");
            file_put_contents('php://stderr', print_r($orderDetails, TRUE));
                 }
            
            //$deliveryRules = $this->db->getFromDB("SELECT is_default,br_rdrIdExpress,rdr_ruleFor FROM retaline_delivery_rules dr INNER JOIN finascop_branch b ON b.br_rdrIDExpress = dr. rdr_id AND b.br_ID = '{$delLocations['quor_Pickupbr_id']}' WHERE rdr_deliveryMode = 2", true);
            //$deliveryRules = $this->db->getFromDB("SELECT rdr_calculationMode,is_default,rdr_ruleFor,rdr_ruleForId FROM retaline_delivery_rules WHERE rdr_deliveryMode = 2 AND rdr_ruleFor = 3 AND rdr_ruleForiD ='{$delLocations['quor_Pickupbr_id']}' ", true);
            $deliveryRules = $this->db->getFromDB("SELECT rdr_calculationMode,is_default,rdr_ruleFor,rdr_ruleForId,rdr_storeGroupId FROM retaline_delivery_rules WHERE rdr_deliveryMode = 2 AND rdr_id ='{$orderDetails['delivery_rule_id']}' ", true);
            file_put_contents('php://stderr', "....................................CreateAPoll..deliveryRules...................................................... \n");
            file_put_contents('php://stderr', print_r($deliveryRules, TRUE));
            $storeGroupId = $this->db->getFromDB("SELECT br_storeGroup,br_Name FROM  finascop_branch WHERE br_ID = '{$delLocations['quor_Pickupbr_id']}'", true);
            file_put_contents('php://stderr', "....................................CreateAPoll..storeGroupId...................................................... \n");
            file_put_contents('php://stderr', print_r($storeGroupId, TRUE));
            if ($specificcandidate == '') {

                $ownVehicle1 = $this->getQugeoCandidates($order['Lat'], $order['Lng'], $storeGroupId['br_storeGroup']);

                file_put_contents('php://stderr', "....................................own..availablevehicles...................................................... \n");
                file_put_contents('php://stderr', print_r($ownVehicle1, TRUE));

                //if (empty($availablevehicles)) {
                //if (empty($deliveryRules) || ($deliveryRules['rdr_ruleFor'] == 1 && $deliveryRules['br_rdrIdExpress'] > 0)) {
                //if (empty($deliveryRules) || $deliveryRules['rdr_calculationMode'] == 3) {
                    if ($deliveryRules['rdr_storeGroupId'] == 0) {
                        file_put_contents('php://stderr', "....................................area..availablevehicles...................................................... \n");                        
                        $ownVehicle2 = $this->getQugeoCandidates($order['Lat'], $order['Lng']);
                        file_put_contents('php://stderr', print_r($ownVehicle2, TRUE));
                    }
                    if (!empty($ownVehicle1) && !empty($ownVehicle2)) {
                        // Merge the two arrays
                        $availablevehicles = array_merge($ownVehicle1, $ownVehicle2);
                    } else {
                        // Set $availableVehicles to the non-empty array
                        $availablevehicles = !empty($ownVehicle1) ? $ownVehicle1 : $ownVehicle2;
                    }
                    if (empty($availablevehicles)) {
                        echo "No candidates " . "\n";
                        return false;
                    }
                    file_put_contents('php://stderr', "....................................availablevehicles...................................................... \n");
                    file_put_contents('php://stderr', print_r($availablevehicles, TRUE));
                //}

                $candidate = $this->getBestQugeoCandidate($availablevehicles, $orderid, $isPickup, false);
            } else {
                $arrVehicle['PartitionKey'] = array('col' => 'apikey', 'val' => $specificcandidate, 'oper' => '=');
                $arrVehicle['getAttributes'] = array('apikey', 'v_id', 'v_no', 'Latitude', 'Longitude', 'v_capacity', 'CurrentLoadedWeight', 'RatePerKm', 'AWS_SNS_ARN', 'DeliveryRange', 'IsEngaged', 'MarkedNextBkId', 'MarkedNextBrId', 'FCM_ID', 'DriverPhone', 'DriverBranchId', 'createdBy', 'sourceId');
                $nodb = new \cgoDynamiteDB();
                $rsno = $nodb->query('QugeoLiveVehicles', $arrVehicle, 'getItem');
                if (isset($rsno) && count($rsno) > 0) {
                    $candidate = array('AWS_SNS_ARN' => $rsno['AWS_SNS_ARN'], 'apikey' => $rsno['apikey'], 'DeliveryRange' => $rsno['DeliveryRange'], 'FCM_ID' => $rsno['FCM_ID'], 'DriverPhone' => $rsno['DriverPhone'], 'DriverBranchId' => $rsno['DriverBranchId'], 'createdBy' => $rsno['createdBy'], 'sourceId' => $rsno['sourceId']);
                } else {
                    $candidate = false;
                }
            }

            if ($candidate !== false) {
                file_put_contents('php://stderr', "CreateAPoll CALLED------candidate list \n ");
                file_put_contents('php://stderr', print_r($candidate, TRUE));
                $pollid = sha1(microtime(true) . mt_rand(10000, 90000));
                $valdate = date("Ymd");
                $valdatetime = date("YmdHis");
                //Use AWS SNS to push message

                $pushedmessage = $this->PushPollingDetailsSchJobs($candidate['apikey'], $pollid, 'NEW', $orderid, $isPickup, $order, $candidate['AWS_SNS_ARN'], $message, ($isPickup === true ? (intval($candidate['DeliveryRange']) >= intval($order['TotalDistKM']) ? true : false) : false), $candidate['FCM_ID'], $candidate['DriverPhone'], $quorIds, $totalOrders, $currentOrderCount, $quor_id);

                if ($pushedmessage) {
                    //Save message to table
                    $savedmessage = $this->SavePollingDetails($pollid, $orderid, $candidate['apikey'], $isPickup, $valdate, $valdatetime, $message, ($isPickup ? (intval($candidate['DeliveryRange']) >= intval($order['TotalDistKM']) ? true : false) : false));
                    return $savedmessage;
                }
            } else {
                //No candidate;
                return false;
            }
        }

        public function CreateAPollSchJob($orderid, $isPickup, $order, $specificcandidate = '', $quorIds, $totalOrders, $currentOrderCount, $quor_id)
        {


            $arrVehicle['PartitionKey'] = array('col' => 'apikey', 'val' => $specificcandidate, 'oper' => '=');
            $arrVehicle['getAttributes'] = array('apikey', 'AWS_SNS_ARN', 'DeliveryRange', 'FCM_ID', 'DriverPhone');
            $nodb = new \cgoDynamiteDB();
            $rsno = $nodb->query('QugeoLiveVehicles', $arrVehicle, 'getItem');
            if (isset($rsno) && count($rsno) > 0) {
                $candidate = array('AWS_SNS_ARN' => $rsno['AWS_SNS_ARN'], 'apikey' => $rsno['apikey'], 'DeliveryRange' => $rsno['DeliveryRange'], 'FCM_ID' => $rsno['FCM_ID'], 'DriverPhone' => $rsno['DriverPhone']);
            } else {
                $candidate = false;
            }

            if ($candidate !== false) {
                $pollid = sha1(microtime(true) . mt_rand(10000, 90000));
                $valdate = date("Ymd");
                $valdatetime = date("YmdHis");
                //Use AWS SNS to push message

                $pushedmessage = $this->PushPollingDetailsSchJobs($candidate['apikey'], $pollid, 'NEW', $orderid, $isPickup, $order, $candidate['AWS_SNS_ARN'], $message, ($isPickup === true ? (intval($candidate['DeliveryRange']) >= intval($order['TotalDistKM']) ? true : false) : false), $candidate['FCM_ID'], $candidate['DriverPhone'], $quorIds, $totalOrders, $currentOrderCount, $quor_id);

                if ($pushedmessage) {
                    //Save message to table
                    $savedmessage = $this->SavePollingDetails($pollid, $orderid, $candidate['apikey'], $isPickup, $valdate, $valdatetime, $message, ($isPickup ? (intval($candidate['DeliveryRange']) >= intval($order['TotalDistKM']) ? true : false) : false));
                    return $savedmessage;
                }
            } else {
                //No candidate;
                return false;
            }
        }

        private function PushPollingDetailsSchJobs($apikey, $pollid, $msgtype, $orderid, $isPickup, $order, $arn, &$message, $withinrange, $fcmid, $mobno, $quorIds, $totalOrders, $currentOrderCount, $quor_id)
        {
            //$sns = new \cgoAWSSNS();
            $fcmmsg = new \firebasemessage();
            if ($totalOrders == $currentOrderCount) {
                $message = array();
                $deliveryLocs = array();
                $orderIds = array();
                file_put_contents('php://stderr', "PushPollingDetailsSchJobs  quorIds\n ");
                file_put_contents('php://stderr', print_r($quorIds, TRUE));
                for ($i = 0; $i < count($quorIds); $i++) {
                    $delLocations = $this->db->getFromDB("SELECT quor_id,quor_RefNo,quor_PickupPincode,quor_PickupLat,quor_PickupLng,quor_PickupLocation,quor_DeliveryPincode,quor_DeliveryLat,quor_DeliveryLng,quor_DeliveryLocation,quor_DeliveryAddress,"
                        . "quor_Pickupbr_id,quor_Deliverybr_id,quor_QugeoPickupDDBOrderId,dls_DelStatus,quor_Status,quor_PickupAddress,quor_PickupPhone FROM  qugeo_order INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status WHERE quor_id = {$quorIds[$i]}", true);
                    file_put_contents('php://stderr', print_r($delLocations, TRUE));
                    if (!empty($delLocations['quor_QugeoPickupDDBOrderId'])) {
                        $quor_QugeoPickupDDBOrderId = $delLocations['quor_QugeoPickupDDBOrderId'];
                    } else {
                        $quor_QugeoPickupDDBOrderId = $orderid;
                    }
                    $storeGroup = $this->db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$delLocations['quor_Deliverybr_id']}",true);
                    $quor_DeliveryLocation = $delLocations['quor_DeliveryAddress'] . '' . $delLocations['quor_DeliveryPincode'];
                    if ($i == 0) {
                        array_push($orderIds, array("id" => $quor_QugeoPickupDDBOrderId, "order" => $delLocations['quor_id'], "orderNo" => $delLocations['quor_RefNo'], "location" => $quor_DeliveryLocation, "latitude" => $delLocations['quor_DeliveryLat'], "longitude" => $delLocations['quor_DeliveryLng'], "orderStatus" => $delLocations['dls_DelStatus'], "statusId" => $delLocations['quor_Status']));
                        array_push($deliveryLocs, array("latitude" => $delLocations['quor_DeliveryLat'], "longitude" => $delLocations['quor_DeliveryLng'], "location" => $quor_DeliveryLocation));
                    }

                    $delBoyReq['fcmid'] = $fcmid;
                    $delBoyReq['apikey'] = $apikey;
                    $delBoyReq['pollid'] = $pollid;
                    $delBoyReq['mobile'] = $mobno;
                    $delBoyReq['quorId'] = $quorIds[$i];
                    $delBoyReq['quor_QugeoPickupDDBOrderId'] = $quor_QugeoPickupDDBOrderId;
                    $db = new \cgoSqlDB();
                    $db->query("insert into qugeo_driver_log(fcmid, apikey, pollid, mobile, quorId,quor_QugeoPickupDDBOrderId) values('{$fcmid}','{$apikey}','{$pollid}','{$mobno}',{$quorIds[$i]},'{$quor_QugeoPickupDDBOrderId}')");

                    // $insquery = 'INSERT INTO qugeo_driver_log '  . ' (' . join(', ', array_keys($delBoyReq)) . ') VALUES ('. join(', ', array_values($delBoyReq)) . ')';
                    //$db->query($insquery);
                    //$db->perform("qugeo_driver_log","insert",$delBoyReq);
                    //file_put_contents('php://stderr',  'InterBranch =>' . $order['InterBranch'] . ' withinrange =>' . ( $withinrange ? 'true' : 'false'));
                }
                file_put_contents('php://stderr', "PushPollingDetailsSchJobs  \n ");
                file_put_contents('php://stderr', print_r($orderIds, TRUE));
                $disttobr = $order['TotalDistKM'] . ' KM';
                $geocoords = array(
                    "pickup" => array("latitude" => $order['pickupLat'], "longitude" => $order['pickupLng'], "location" => $order['pickuplocation'], "address" => $delLocations['quor_PickupAddress'], "mobile" => $delLocations['quor_PickupPhone']),
                    "delivery" => $deliveryLocs
                );

                $message['data'] = array(
                    "yourapikey" => $apikey,
                    "msgid" => $pollid,
                    "msgtype" => $msgtype,
                    "storeGroup"=>$storeGroup,
                    "orderid" => json_encode($orderIds),
                    "orderCount" => $totalOrders,
                    "ispickuporder" => $isPickup,
                    "details" => json_encode($geocoords)
                );
                file_put_contents('php://stderr', "PushPollingDetailsSchJobsmessage  \n ");
                file_put_contents('php://stderr', print_r($message['data'], TRUE));
                $fcmmsg->sendmessage(AWS_SNS_ORDER_TTL, $mobno, "Scheduled Order Received", "Drive", $message['data'], $fcmid);
            }
            return true;
        }

        private function PushPollingDetails($apikey, $pollid, $msgtype, $orderid, $isPickup, $order, $arn, &$message, $withinrange, $fcmid, $mobno)
        {
            //$sns = new \cgoAWSSNS();
            $fcmmsg = new \firebasemessage();
            $message = array();
            $disttobr = $order['TotalDistKM'] . ' KM';
            //file_put_contents('php://stderr',  'InterBranch =>' . $order['InterBranch'] . ' withinrange =>' . ( $withinrange ? 'true' : 'false'));
            $geocoords = array(
                "pickup" => array("latitude" => $order['pickupLat'], "longitude" => $order['pickupLng'], "location" => $order['pickuplocation']),
                "delivery" => array("latitude" => $order['deliveryLat'], "longitude" => $order['deliveryLng'], "location" => $order['deliverylocation'])
            );

            $message['data'] = array(
                "yourapikey" => $apikey,
                "msgid" => $pollid,
                "msgtype" => $msgtype,
                "orderid" => $orderid,
                "ispickuporder" => $isPickup,
                "details" => json_encode($geocoords)
            );
            //public function sendmessage($ttl, $labeltext, $bodytext, $title, $data, $fcmid){
            $fcmmsg->sendmessage(AWS_SNS_ORDER_TTL, $mobno, "New Order Received", "Drive", $message['data'], $fcmid);
            return true;
            /* if ($sns->getEndPointDetails($arn,'Enabled')=='true'){	 
              $msgid = $sns->publishToEndPoint($arn,json_encode($message),AWS_SNS_ORDER_TTL);
              return true;
              }else{
              return false;
              } */
        }

        private function SavePollingDetails($pollid, $orderid, $apikey, $ispickup, $valdate, $valdatetime, $message, $withinrange)
        {
            $nodb = new \cgoDynamiteDB();
            $arrOrder = array();
            $arrOrder['Data'] = array();
            $valdate = date("Ymd");
            $valdatetime = date("YmdHis");
            array_push($arrOrder['Data'], array('col' => 'pollingid', 'val' => $pollid));
            array_push($arrOrder['Data'], array('col' => 'apikey', 'val' => $apikey));
            array_push($arrOrder['Data'], array('col' => 'orderid', 'val' => $orderid));
            array_push($arrOrder['Data'], array('col' => 'createddatetime', 'val' => (int) $valdatetime));
            array_push($arrOrder['Data'], array('col' => 'createddate', 'val' => (int) $valdate));
            array_push($arrOrder['Data'], array('col' => 'currentstatus', 'val' => 'POLLED'));
            array_push($arrOrder['Data'], array('col' => 'ispickup', 'val' => (int) $ispickup));
            array_push($arrOrder['Data'], array('col' => 'isclosed', 'val' => 0));
            array_push($arrOrder['Data'], array('col' => 'pollingdetails', 'val' => $message));
            array_push($arrOrder['Data'], array('col' => 'withinrange', 'val' => (bool) $withinrange));
            $NewOrder = $nodb->perform('QugeoOrderPollingDetails', 'insert', $arrOrder, $response);
            if ($NewOrder !== false) {
                return true;
            } else {
                return false;
            }
        }

        public function PollResponse($pollid, $pollresponse, $delivertobranch, &$acceptedorder)
        {

            $nodb = new \cgoDynamiteDB();
            $arrUpdate = array();
            $arrUpdate['PartitionKey'] = array('col' => 'pollingid', 'val' => $pollid);
            $arrUpdate['Data'] = array();
            array_push($arrUpdate['Data'], array('col' => 'isclosed', 'val' => 1));
            array_push($arrUpdate['Data'], array('col' => 'closedat', 'val' => (string) date("YmdHis")));
            if ($pollresponse == 1) {
                array_push($arrUpdate['Data'], array('col' => 'currentstatus', 'val' => 'ACCEPTED'));
                array_push($arrUpdate['Data'], array('col' => 'acceptedfor', 'val' => (string) ($delivertobranch == true ? 'BRANCH' : 'DIRECT')));
                $acceptedorder = true;
            } else {
                $acceptedorder = false;
                array_push($arrUpdate['Data'], array('col' => 'currentstatus', 'val' => (string) ($pollresponse == 2 ? 'REJECTED' : 'NORESPONSE')));
            }
            $nors = $nodb->perform('QugeoOrderPollingDetails', 'update', $arrUpdate, $response);

            if (count($response) > 0) {
                return true;
            } else {
                return false;
            }
        }

        public function IsPollClosed($pollid, &$apikey)
        {

            $nodb = new \cgoDynamiteDB();
            $arrUpdate = array();
            $arrUpdate['PartitionKey'] = array('col' => 'pollingid', 'val' => $pollid, 'oper' => '=');
            $arrUpdate['queryAttributes'] = array('isclosed', 'apikey');
            $rsno = $nodb->query('QugeoOrderPollingDetails', $arrUpdate, 'query');
            file_put_contents('php://stderr', "POST_pullpendingorder IsPollClosed " . $pollid . " \n ");
            file_put_contents('php://stderr', print_r($rsno, TRUE));
            if (isset($rsno) && count($rsno) > 0) {
                $apikey = $rsno[0]['apikey'];
                if ($rsno[0]['isclosed'] == 0) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return true;
            }
        }

        public function HasLivePoll($apikey)
        {

            $nodb = new \cgoDynamiteDB();
            $arrUpdate = array();
            $arrUpdate['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
            $arrUpdate['SortKey'] = array('col' => 'isclosed', 'val' => 0, 'oper' => '=');
            $arrUpdate['IndexName'] = 'apikey-isclosed-index';
            $arrUpdate['queryAttributes'] = array('isclosed');
            $rsno = $nodb->query('QugeoOrderPollingDetails', $arrUpdate, 'query');

            if (isset($rsno) && count($rsno) > 0) {
                return true;
            } else {
                return false;
            }
        }

        public function FindandMarkProspectiveCandidates($orderid, $isPickup, $order, $specificcandidate = '')
        {
            $nodb = new \cgoDynamiteDB();
            if ($isPickup) {
                $availablevehicles = $this->getSecondaryQugeoCandidates($order['pickupLat'], $order['pickupLng']);
            } else {
                $availablevehicles = $this->getSecondaryQugeoCandidates($order['deliveryLat'], $order['deliveryLng']);
            }
            if (empty($availablevehicles)) {
                return false;
            }
            $candidate = $this->getBestSecondaryQugeoCandidate($availablevehicles, $orderid, $isPickup, true);
            if ($candidate !== false) {
                $arrUpdate = array();
                $arrUpdate['PartitionKey'] = array('col' => 'apikey', 'val' => $candidate['apikey']);
                $arrUpdate['Data'] = array();
                array_push($arrUpdate['Data'], array('col' => 'MarkedNextBkId', 'val' => (int) $order['bk_id']));
                array_push($arrUpdate['Data'], array('col' => 'MarkedNextBrId', 'val' => (int) $order['bk_brk_br_id']));
                $nors = $nodb->perform('QugeoLiveVehicles', 'update', $arrUpdate, $response);
                return true;
            } else {
                //No candidate;
                return false;
            }
        }

        private function getSecondaryQugeoCandidates($orderLat, $orderLong)
        {
            $nodb = new \cgoDynamiteDB();
            $degMat = new \cgoGeoUtilities();
            $arrDegrees = $degMat->getDegreeMatrix($orderLong, $orderLat, QC_VEHICLE_NEAR_PICKUP_CIRCLE_DIST);
            $arrVehicle = array();
            $arrVehicle['PartitionKey'] = array('col' => 'Is_Live', 'val' => 1, 'oper' => '=');
            $arrVehicle['SortKey'] = array('col' => 'OnJobCompletionLatitude', 'val1' => (float) $arrDegrees['lat1'], 'val2' => (float) $arrDegrees['lat2'], 'SortKeyBetween' => true);
            //$arrVehicle['SortKey']=array('col'=>'Latitude','val'=>$arrDegrees['lat1'],'oper'=>'=');				
            $arrVehicle['IndexName'] = 'Is_Live-OnJobCompletionLatitude-index';
            $arrVehicle['queryAttributes'] = array('apikey', 'v_id', 'v_no', 'OnJobCompletionLatitude', 'OnJobCompletionLongitude', 'v_capacity', 'CurrentLoadedWeight', 'RatePerKm', 'AWS_SNS_ARN', 'DeliveryRange', 'IsEngaged', 'MarkedNextBkId', 'MarkedNextBrId');
            $arrVehicle['Condition'] = array();
            array_push($arrVehicle['Condition'], array('col' => 'OnJobCompletionLongitude', 'val1' => (float) $arrDegrees['lon1'], 'val2' => (float) $arrDegrees['lon2'], 'ConditionBetween' => true));
            array_push($arrVehicle['Condition'], array('col' => 'MarkedNextBkId', 'val' => 0, 'oper' => '='));
            array_push($arrVehicle['Condition'], array('col' => 'IsEngaged', 'val' => 0, 'oper' => '='));
            $nodb = new \cgoDynamiteDB();
            $rsno = $nodb->query('QugeoLiveVehicles', $arrVehicle, 'query');
            $vehicledetails = array();
            if (isset($rsno) && count($rsno) > 0) {
                foreach ($rsno as $value) {
                    $dist = $degMat->GetDrivingDistance($orderLat, $value['OnJobCompletionLatitude'], $orderLong, $value['OnJobCompletionLongitude']);
                    array_push($vehicledetails, array('apikey' => $value['apikey'], 'v_No' => $value['v_no'], 'distance' => $dist, 'capacity' => $value['v_capacity'], 'CurrentLoadedWeight' => $value['CurrentLoadedWeight'], 'RatePerKm' => $value['RatePerKm'], 'AWS_SNS_ARN' => $value['AWS_SNS_ARN'], 'DeliveryRange' => $value['DeliveryRange']));
                }
                return $vehicledetails;
            } else {
                return false;
            }
        }

        private function getBestSecondaryQugeoCandidate($vehicledetails, $orderid, $isPickup, $ignorepreviouspushes)
        {
            //$dist = array();
            $currentload = array();
            $RatePerKm = array();
            $Capacity = array();
            foreach ($vehicledetails as $key => $row) {
                if (!$ignorepreviouspushes) {
                    if ($this->WasQueued($orderid, $row['apikey'], $isPickup) > 0) {
                        unset($vehicledetails[$key]);
                    } else {
                        //$dist[$key] = $row['distance'];		
                        $currentload[$key] = $row['CurrentLoadedWeight'];
                        $RatePerKm[$key] = $row['RatePerKm'];
                        $Capacity[$key] = $row['capacity'];
                    }
                }
            }
            if (!empty($vehicledetails)) {
                //array_multisort($dist, SORT_ASC, $vehicledetails);
                finascop_aasort($vehicledetails, 'distance');
                return $vehicledetails[0];
            } else {
                return false;
            }

            //More than 20 open orders?
            //Single Load	
            //Is Engaged and this order is much(previously configured) far from current order
            //Tonnage - Current Weight
            //Coverage - Current Volume
            //Rate * Distance
            //Proximity
            //Order processed today
            //Driver rating
        }
    }
}
