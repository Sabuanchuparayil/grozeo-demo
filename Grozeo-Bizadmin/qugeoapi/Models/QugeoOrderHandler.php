<?php

namespace Models; {

    class QugeoOrderHandler
    {

        public function __construct()
        {
        }

        private function getRandString($len = 4)
        {
            $seed = str_split('abcdefghijklmnopqrstuvwxyz'
                . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                . '0123456789'); // and any other characters
            shuffle($seed); // probably optional since array_is randomized; this may be redundant
            $rand = '';
            foreach (array_rand($seed, $len) as $k)
                $rand .= $seed[$k];
            return $rand;
        }

        public function CreateOrder($ispickup, $consume_id, $consume_RefNo, $Date, $PickupName, $PickupAddress, $PickupLocation, $PickupPhone, $PickupLat, $PickupLng, $PickupOTP, $PickupStage1Distance, $PickupStage2Distance, $DeliveryName, $DeliveryAddress, $DeliveryLocation, $DeliveryPhone, $DeliveryLat, $DeliveryLng, $DeliveryOTP, $DeliveryStage1Distance, $DeliveryStage2Distance, $DistanceKM, $status, $pktcount, $consignmentdetails, $Manualpush, $ScheduleRun, &$Order, $PickupPincode, $DeliveryPincode, $PickupBrId, $DeliveryBrId, $netamt)
        {

            $orderid = sha1(microtime(true) . mt_rand(10000, 90000));
            $arrOrder = array();
            $Order = array();
            $arrOrder['Data'] = array();
            $valdate = date("Ymd");
            $valdatetime = date("YmdHis");
            file_put_contents('php://stderr', print_r("CreateOrder ispickup-" . $ispickup . "\n", TRUE));

            array_push($arrOrder['Data'], array('col' => 'orderid', 'val' => $orderid));
            array_push($arrOrder['Data'], array('col' => 'date', 'val' => (string) $Date));
            array_push($arrOrder['Data'], array('col' => 'IsPickup', 'val' => (int) ($ispickup == true ? 1 : 0)));
            array_push($arrOrder['Data'], array('col' => 'ManuallyPushed', 'val' => (int) ($Manualpush == true ? 1 : 0)));
            array_push($arrOrder['Data'], array('col' => 'createddatetime', 'val' => (int) $valdatetime));
            array_push($arrOrder['Data'], array('col' => 'updateddatetime', 'val' => (int) $valdatetime));
            array_push($arrOrder['Data'], array('col' => 'createddate', 'val' => (int) $valdate));
            array_push($arrOrder['Data'], array('col' => 'quor_id', 'val' => (int) $consume_id));
            array_push($arrOrder['Data'], array('col' => 'quor_RefNo', 'val' => (string) $consume_RefNo));


            array_push($arrOrder['Data'], array('col' => 'IsMilestoneLock', 'val' => '0'));
            array_push($arrOrder['Data'], array('col' => 'MilestoneCovered', 'val' => '0'));

            array_push($arrOrder['Data'], array('col' => 'OrderStatus', 'val' => (int) $status));
            array_push($arrOrder['Data'], array('col' => 'Acceptedapikey', 'val' => "-"));
            array_push($arrOrder['Data'], array('col' => 'bkno', 'val' => (string) $consume_RefNo));
            array_push($arrOrder['Data'], array('col' => 'pickupmobile', 'val' => (string) $PickupPhone));
            array_push($arrOrder['Data'], array('col' => 'pickupname', 'val' => (string) $PickupName));
            array_push($arrOrder['Data'], array('col' => 'pickupaddress', 'val' => (string) $PickupAddress));
            array_push($arrOrder['Data'], array('col' => 'pickuplocation', 'val' => (string) $PickupLocation));
            array_push($arrOrder['Data'], array('col' => 'pickupOTP', 'val' => (string) $PickupOTP));
            array_push($arrOrder['Data'], array('col' => 'pickupLat', 'val' => (float) $PickupLat));
            array_push($arrOrder['Data'], array('col' => 'pickupLng', 'val' => (float) $PickupLng));
            array_push($arrOrder['Data'], array('col' => 'pickupStage1Distance', 'val' => (float) $PickupStage1Distance));
            array_push($arrOrder['Data'], array('col' => 'pickupStage2Distance', 'val' => (float) $PickupStage2Distance));
            array_push($arrOrder['Data'], array('col' => 'deliverymobile', 'val' => (string) $DeliveryPhone));
            array_push($arrOrder['Data'], array('col' => 'deliveryname', 'val' => (string) $DeliveryName));
            array_push($arrOrder['Data'], array('col' => 'deliveryaddress', 'val' => (string) $DeliveryAddress));
            array_push($arrOrder['Data'], array('col' => 'deliverylocation', 'val' => (string) $DeliveryLocation));
            array_push($arrOrder['Data'], array('col' => 'deliveryOTP', 'val' => (string) $DeliveryOTP));
            array_push($arrOrder['Data'], array('col' => 'deliveryLat', 'val' => (float) $DeliveryLat));
            array_push($arrOrder['Data'], array('col' => 'deliveryLng', 'val' => (float) $DeliveryLng));
            array_push($arrOrder['Data'], array('col' => 'deliveryStage1Distance', 'val' => (float) $DeliveryStage1Distance));
            array_push($arrOrder['Data'], array('col' => 'deliveryStage2Distance', 'val' => (float) $DeliveryStage2Distance));
            array_push($arrOrder['Data'], array('col' => 'FirstScheduleRun', 'val' => $ScheduleRun));

            array_push($arrOrder['Data'], array('col' => 'TotalDistKM', 'val' => (float) $DistanceKM));

            array_push($arrOrder['Data'], array('col' => 'pktcount', 'val' => (int) $pktcount));
            array_push($arrOrder['Data'], array('col' => 'totwt', 'val' => 0));
            array_push($arrOrder['Data'], array('col' => 'netamt', 'val' => $netamt));
            array_push($arrOrder['Data'], array('col' => 'Consignment', 'val' => $consignmentdetails));
            array_push($arrOrder['Data'], array('col' => 'IsClosed', 'val' => 0));
            array_push($arrOrder['Data'], array('col' => 'PickupPincode', 'val' => $PickupPincode));
            array_push($arrOrder['Data'], array('col' => 'DeliveryPincode', 'val' => $DeliveryPincode));

            file_put_contents('php://stderr', print_r($arrOrder, TRUE));
            if ($ispickup) {
                array_push($arrOrder['Data'], array('col' => 'Lat', 'val' => (float) $PickupLat));
                array_push($arrOrder['Data'], array('col' => 'Lng', 'val' => (float) $PickupLng));
                array_push($arrOrder['Data'], array('col' => 'DelAssetBucket', 'val' => '-'));
                array_push($arrOrder['Data'], array('col' => 'ImgSignLocation', 'val' => '-'));
                array_push($arrOrder['Data'], array('col' => 'ImgCustLocation', 'val' => '-'));
                array_push($arrOrder['Data'], array('col' => 'Location', 'val' => (string) $PickupLocation));
                array_push($arrOrder['Data'], array('col' => 'HandlingBranch', 'val' => (int) $PickupBrId));
            } else {
                array_push($arrOrder['Data'], array('col' => 'HandlingBranch', 'val' => (int) $DeliveryBrId));
                array_push($arrOrder['Data'], array('col' => 'Location', 'val' => (string) $DeliveryLocation));
                array_push($arrOrder['Data'], array('col' => 'Lat', 'val' => (float) $DeliveryLat));
                array_push($arrOrder['Data'], array('col' => 'Lng', 'val' => (float) $DeliveryLng));
                array_push($arrOrder['Data'], array('col' => 'DelAssetBucket', 'val' => (string) QUGEO_DELIVERY_ASSET_BUCKET));
                $prefx = $this->getRandString();
                array_push($arrOrder['Data'], array('col' => 'ImgSignLocation', 'val' => (string) $prefx . "/" . "S_" . $consume_RefNo . ".jpg"));
                array_push($arrOrder['Data'], array('col' => 'ImgCustLocation', 'val' => (string) $prefx . "/" . "C_" . $consume_RefNo . ".jpg"));
            }
            foreach ($arrOrder['Data'] as $keys) {
                $Order[$keys['col']] = $keys['val'];
            }
            $degMat = new \cgoGeoUtilities();


            $nodb = new \cgoDynamiteDB();
            $NewOrder = $nodb->perform('QugeoOrderDetails', 'insert', $arrOrder, $response);
            if ($NewOrder != false) {
                return $orderid;
            } else {
                print_r($response);
                return false;
            }
        }

        public function UpdateOrderOnPoll($pollresponse, $orderid, $apikey, $delivertobr, &$orderdetails, $getOrderDetails = true)
        {
            $db = new \sqlDb(DSN);
            if ($getOrderDetails) {
                $arrOrder['PartitionKey'] = array('col' => 'orderid', 'val' => $orderid, 'oper' => '=');
                $arrOrder['getAttributes'] = array('orderid', 'IsPickup', 'HasDirectDeliveryPickUp', 'quor_id', 'Consignment', 'pickupLat', 'pickupLng', 'TotalDistKM', 'deliveryLat', 'deliveryLng', 'Lat', 'Lng', 'quor_RefNo', 'pickupmobile', 'deliverymobile', 'pickupname', 'pickupaddress', 'deliveryname', 'deliveryaddress', 'pickuplocation', 'deliverylocation', 'HandlingBranch', 'IsMilestoneLock', 'WasDirectDeliveryPickUp');
                $nodb = new \cgoDynamiteDB();
                $rsno = $nodb->query('QugeoOrderDetails', $arrOrder, 'getItem');
                if (isset($rsno) && count($rsno) > 0) {
                    $orderdetails = array();
                    $orderdetails['quor_id'] = $rsno['quor_id'];
                    $orderdetails['IsPickup'] = $rsno['IsPickup'];
                    $orderdetails['HasDirectDeliveryPickUp'] = $rsno['HasDirectDeliveryPickUp'];
                    $orderdetails['Consignment'] = $rsno['Consignment'];
                    $orderdetails['pickupLat'] = $rsno['pickupLat'];
                    $orderdetails['pickupLng'] = $rsno['pickupLng'];
                    $orderdetails['deliveryLat'] = $rsno['deliveryLat'];
                    $orderdetails['DistSrcToBr'] = $rsno['TotalDistKM'];
                    $orderdetails['DistSrcToDst'] = $rsno['TotalDistKM'];
                    $orderdetails['deliveryLng'] = $rsno['deliveryLng'];
                    $orderdetails['BrLat'] = $rsno['Lat'];
                    $orderdetails['BrLong'] = $rsno['Lng'];
                    $orderdetails['quor_RefNo'] = $rsno['quor_RefNo'];
                    $orderdetails['Srcmobile'] = $rsno['pickupmobile'];
                    $orderdetails['Dstmobile'] = $rsno['deliverymobile'];
                    $orderdetails['Srcname'] = $rsno['pickupname'];
                    $orderdetails['Dstname'] = $rsno['deliveryname'];
                    $orderdetails['Srcaddress'] = $rsno['pickupaddress'];
                    $orderdetails['Dstaddress'] = $rsno['deliveryaddress'];
                    $orderdetails['pickuplocation'] = $rsno['pickuplocation'];
                    $orderdetails['deliverylocation'] = $rsno['deliverylocation'];
                    $orderdetails['totwt'] = 0;
                    $orderdetails['totvol'] = 0;
                    $orderdetails['HandlingBranch'] = $rsno['HandlingBranch'];
                    $orderdetails['IsMilestoneLock'] = $rsno['IsMilestoneLock'];
                    $orderdetails['WasDirectDeliveryPickUp'] = $rsno['WasDirectDeliveryPickUp'];
                } else {
                    return false;
                }
            }

            $arrUpdate = array();
            $arrUpdate['PartitionKey'] = array('col' => 'orderid', 'val' => $orderid);
            $arrUpdate['Data'] = array();
            $valdate = date("YmdHis");
            array_push($arrUpdate['Data'], array('col' => 'Acceptedapikey', 'val' => $apikey));
            array_push($arrUpdate['Data'], array('col' => 'AcceptedAt', 'val' => (int) $valdate));
            array_push($arrUpdate['Data'], array('col' => 'updateddatetime', 'val' => (int) $valdate));
            if ($orderdetails['IsPickup'] == 1) {
                if ($pollresponse == 1) {
                    $db->query("UPDATE  qugeo_order set quor_Status=" . ($delivertobr == false ? ORDER_PICKUP_FLAGGED_TODST_DLS_ID : ORDER_PICKUP_FLAGGED_TOBR_DLS_ID) . ", quor_QugeoPickupDDBOrderId = '" . $orderid . "',quor_QugeoPickupDDBDriverId='" . $apikey . "', quor_SchedulePickupTime='" . date("Y-m-d H:i:s", strtotime($valdate)) . "',quor_PickupDriverId=" . $_SESSION["loginid"] . "  where quor_id = " . $orderdetails['quor_id']);
                    $updateurl = $db->getItemFromDb("select quor_StatusUpdateQry from qugeo_order where quor_id = " . $orderdetails['quor_id']);
                    //$updateurl = str_replace("###1",QUGEO_TO_CUSTOMER_ORDER_STATUS_POLL_ACCEPTED,$updateurl);
                    $updateurl = getQugeoParentStatusUpdated($updateurl, ($delivertobr == false ? ORDER_PICKUP_FLAGGED_TODST_DLS_ID : ORDER_PICKUP_FLAGGED_TOBR_DLS_ID));
                    $updateurl = str_replace("###6", "1", $updateurl);
                    $updateurl = str_replace("###2", "", $updateurl);
                    $execQry = explode(";", $updateurl);

                    $db->query(trim($execQry[0]));
                    $db->query(trim($execQry[1]));
                    file_put_contents('php://stderr', print_r("PICKUP AND ACCEPTED 1 -- " . $execQry[0] . "\n", TRUE));
                    file_put_contents('php://stderr', print_r("PICKUP AND ACCEPTED 2  -- " . $execQry[1] . "\n", TRUE));
                    //for tracking
                    $updateurl = $db->getItemFromDb("select quor_TrackingUpdateQry from qugeo_order where quor_id = " . $orderdetails['quor_id'], true);
                    $TrackingUpdate = str_replace("###1", $orderid, QUGEO_TRACKING_API_GATEWAY);
                    $TrackingUpdate = str_replace("###2", AWSDYNAMODBTABLEPREFIX, $TrackingUpdate);
                    $updateurl = str_replace("###1", $TrackingUpdate, $updateurl);
                    $updateurl = str_replace("###6", "1", $updateurl);
                    $DriverName = $db->getItemFromDb("select d_Name from qugeo_driver where d_ID = " . $_SESSION["loginid"], true);
                    $DriverPhone = $db->getItemFromDb("select d_Ph1 from qugeo_driver where d_ID = " . $_SESSION["loginid"], true);
                    $updateurl = str_replace("##10", addslashes($DriverName), $updateurl);
                    $updateurl = str_replace("##11", addslashes($DriverPhone), $updateurl);
                    $db->query($updateurl);

                    array_push($arrUpdate['Data'], array('col' => 'OrderStatus', 'val' => (int) ($delivertobr == false ? ORDER_PICKUP_FLAGGED_TODST_DLS_ID : ORDER_PICKUP_FLAGGED_TOBR_DLS_ID)));
                    array_push($arrUpdate['Data'], array('col' => 'AcceptedAsDirectDelivery', 'val' => (int) ($delivertobr == false ? true : false)));
                } else {
                    $db->query("UPDATE  qugeo_order set quor_Status=" . ($pollresponse == 2 ? ORDER_PICKUP_POLL_REJECTED_DLS_ID : ORDER_PICKUP_POLL_NORESP_DLS_ID) . " where quor_id = " . $orderdetails['quor_id']);
                    array_push($arrUpdate['Data'], array('col' => 'OrderStatus', 'val' => (int) ($pollresponse == 2 ? ORDER_PICKUP_POLL_REJECTED_DLS_ID : ORDER_PICKUP_POLL_NORESP_DLS_ID)));
                }
            } else {
                $db->query("UPDATE  qugeo_order set  quor_Status=" . ($pollresponse == 1 ? ORDER_DELIVERY_OUT_FOR_DELIVERY : ($pollresponse == 2 ? ORDER_DELIVERY_POLL_REJECTED_DLS_ID : ORDER_DELIVERY_POLL_NORESP_DLS_ID)) . ", quor_QugeoDeliveryDDBDriverId ='" . $apikey . "', quor_QugeoDeliveryDDBOrderId ='" . $orderid . "', quor_ScheduleDeliveryTime= '" . date("Y-m-d H:i:s", strtotime($valdate)) . "', quor_DeliveryDriverId ='" . $_SESSION["loginid"] . "' where quor_id = " . $orderdetails['quor_id']);
                array_push($arrUpdate['Data'], array('col' => 'OrderStatus', 'val' => (int) ($pollresponse == 1 ? ORDER_DELIVERY_OUT_FOR_DELIVERY : ($pollresponse == 2 ? ORDER_DELIVERY_POLL_REJECTED_DLS_ID : ORDER_DELIVERY_POLL_NORESP_DLS_ID))));
                array_push($arrUpdate['Data'], array('col' => 'AcceptedAsDirectDelivery', 'val' => -1));
            }
            $nors = $nodb->perform('QugeoOrderDetails', 'update', $arrUpdate, $response);
            return true;
        }

        public function AssignOrderToQugeoDriver($orderid, $apikey, $orderdetails, &$nextorder, &$isnewroute, $getOrder = true)
        {
            $arrOrder = array();
            $arrOrder['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
            $arrOrder['getAttributes'] = array('Latitude', 'Longitude', 'Home_Latitude', 'Home_Longitude', 'AssignedLoadedWeight', 'AssignedLoadedVolume', 'CurrentLoadedVolume', 'CurrentLoadedWeight', 'TotalJobs', 'DriverName', 'mobno');
            $nodb = new \cgoDynamiteDB();
            $rsno = $nodb->query('QugeoLiveVehicles', $arrOrder, 'getItem');
            $AssignedLoadedWeight = floatval($rsno['AssignedLoadedWeight']);
            $AssignedLoadedVolume = floatval($rsno['AssignedLoadedVolume']);
            $CurrentLoadedVolume = floatval($rsno['CurrentLoadedVolume']);
            $CurrentLoadedWeight = floatval($rsno['CurrentLoadedWeight']);
            $TotJobs = intval($rsno['TotalJobs']);
            $DriverName = $rsno['DriverName'];
            $DriverMobNo = $rsno['mobno'];
            $arrVehicle['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
            //$arrVehicle['IndexName'] = 'apikey-IsClosed-index';
            $arrVehicle['queryAttributes'] = array('orderid', 'order', 'Latitude', 'Longitude', 'IsClosed', 'IsLiveOrder', 'IsPickup', 'IsMilestoneLock');
            //array_push($arrVehicle['Condition'],array('col'=>'IsClosed','val'=>0,'oper'=>'='));				
            $nors = $nodb->query('QugeoLiveVehicleOrders', $arrVehicle, 'query');
            $order = array();
            $waypoints = '';
            $LiveOrder = '';
            $IsMilestoneLocked = 0;
            $NewOrderLat = ($orderdetails['IsPickup'] == 1 ? $orderdetails['pickupLat'] : $orderdetails['deliveryLat']);
            $NewOrderLong = ($orderdetails['IsPickup'] == 1 ? $orderdetails['pickupLng'] : $orderdetails['deliveryLng']);
            $CurrentOpenOrders = array();
            if (isset($nors) && count($nors) > 0) {
                $isOnlyOrder = false;
                $CurrentOrders = array();
                foreach ($nors as $value) {
                    array_push($CurrentOrders, array('orderid' => $value['orderid'], 'order' => $value['order'], 'Latitude' => $value['Latitude'], 'Longitude' => $value['Longitude'], 'IsClosed' => $value['IsClosed'], 'IsLiveOrder' => $value['IsLiveOrder'], 'IsPickup' => $value['IsPickup'], 'IsMilestoneLock' => $value['IsMilestoneLock']));
                    $order['order'] = $value['order'];
                }
                //array_multisort($order, SORT_ASC, $CurrentOrders);
                finascop_aasort($CurrentOrders, 'order');
                $CurrentOpenOrders = array();
                $MaxclosedOrder = 0;
                foreach ($CurrentOrders as $value) {
                    if ($value['IsClosed'] == '0') {
                        if ($value['IsLiveOrder'] == '1') {
                            $LiveOrder = $value['orderid'];
                            if ($value['IsMilestoneLock'] == 1) {
                                $NewLiveOrder = $value['orderid'];
                                $IsMilestoneLocked = $value['IsMilestoneLock'];
                                $MaxclosedOrder = $value['order'];
                            }
                        }
                        if ($value['IsMilestoneLock'] == 0) {
                            array_push($CurrentOpenOrders, $value);
                            $waypoints = ($waypoints == '' ? $value['Latitude'] . ',' . $value['Longitude'] : $waypoints . '|' . $value['Latitude'] . ',' . $value['Longitude']);
                        }
                    } else {
                        $MaxclosedOrder = $value['order'];
                    }
                }
                //push new order				
                array_push($CurrentOpenOrders, array('orderid' => $orderid, 'order' => 0, 'Latitude' => $NewOrderLat, 'Longitude' => $NewOrderLong, 'IsClosed' => 0, 'IsLiveOrder' => 0, 'IsPickup' => $orderdetails['IsPickup']));
                $waypoints = ($waypoints == '' ? $NewOrderLat . ',' . $NewOrderLong : $waypoints . '|' . $NewOrderLat . ',' . $NewOrderLong);
                $waypoints = "optimize:true|" . $waypoints;
                $route = new \cgoGeoUtilities();

                $newrouteorder = $route->getDrivingRoute($rsno['Latitude'], $rsno['Home_Latitude'], $rsno['Longitude'], $rsno['Home_Longitude'], $waypoints);
                //$newrouteorder = explode(",",$routeorder);
                $cnt = 0;
                foreach ($newrouteorder as $value) {
                    if ($cnt == 0 && $IsMilestoneLocked == 0) {
                        $NewLiveOrder = $CurrentOpenOrders[$value]['orderid'];
                    }
                    $MaxclosedOrder = $MaxclosedOrder + QUGEO_LIVE_ORDER_INCREMENT_VALUE;
                    $CurrentOpenOrders[$value]['order'] = $MaxclosedOrder;
                    $cnt++;
                }
            } else {
                $isLiveOrder = 1;
                $isOnlyOrder = true;
                $NewLiveOrder = $orderid;
                $CurrentOrders = array();
                array_push($CurrentOpenOrders, array('orderid' => $orderid, 'Latitude' => $NewOrderLat, 'Longitude' => $NewOrderLong, 'IsClosed' => 0, 'IsLiveOrder' => 0, 'IsPickup' => $orderdetails['IsPickup'], 'order' => QUGEO_LIVE_ORDER_INCREMENT_VALUE));
            }
            $isnewroute = false;
            $nextorder = array();
            $db = new \cgoSqlDB();
            $isSignature = $db->getItemFromDB("select cfg_Value from sys_configuration  where cfg_Name = 'IS_SIGNATURE'", array(), true);
            $nextorder['isSignature'] = $isSignature;
            $nextorder['istriprerouted'] = false;
            $nextorder['mapdetails'] = array();
            $nextorder['nextorderdetails'] = array();
            foreach ($CurrentOpenOrders as $value) {
                $arrOrder = array();
                $arrOrder['Data'] = array();
                $valdate = date("Ymd");
                $valdatetime = date("YmdHis");
                if ($value['orderid'] == $orderid) {
                    array_push($arrOrder['Data'], array('col' => 'apikey', 'val' => $apikey));
                    array_push($arrOrder['Data'], array('col' => 'orderid', 'val' => $orderid));
                    array_push($arrOrder['Data'], array('col' => 'IsLiveOrder', 'val' => (int) ($isOnlyOrder || $NewLiveOrder == $value['orderid'] ? 1 : 0)));
                    array_push($arrOrder['Data'], array('col' => 'order', 'val' => (int) $value['order']));
                    array_push($arrOrder['Data'], array('col' => 'createddatetime', 'val' => (int) $valdatetime));
                    array_push($arrOrder['Data'], array('col' => 'createddate', 'val' => (int) $valdate));
                    array_push($arrOrder['Data'], array('col' => 'IsPickup', 'val' => (int) $value['IsPickup']));
                    array_push($arrOrder['Data'], array('col' => 'IsClosed', 'val' => 0));
                    array_push($arrOrder['Data'], array('col' => 'Latitude', 'val' => (float) $NewOrderLat));
                    array_push($arrOrder['Data'], array('col' => 'Longitude', 'val' => (float) $NewOrderLong));
                    array_push($arrOrder['Data'], array('col' => 'HandlingBranch', 'val' => (int) $orderdetails['HandlingBranch']));
                    array_push($arrOrder['Data'], array('col' => 'IsMilestoneLock', 'val' => 0));
                    $NewOrder = $nodb->perform('QugeoLiveVehicleOrders', 'insert', $arrOrder, $response);
                    $arrLiveOrder = array();
                    $arrLiveOrder['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
                    $arrLiveOrder['Data'] = array();
                    $UpdateVehicleLoad = false;
                    if ($orderdetails['WasDirectDeliveryPickUp'] != '1') {
                        $UpdateVehicleLoad = true;
                        array_push($arrLiveOrder['Data'], array('col' => 'AssignedLoadedWeight', 'val' => (float) ($AssignedLoadedWeight + $orderdetails['totwt'])));
                        array_push($arrLiveOrder['Data'], array('col' => 'AssignedLoadedVolume', 'val' => (float) ($AssignedLoadedVolume + $orderdetails['totvol'])));
                    }

                    if ($orderdetails['IsPickup'] != '1' && $orderdetails['WasDirectDeliveryPickUp'] != '1') {
                        $UpdateVehicleLoad = true;
                        array_push($arrLiveOrder['Data'], array('col' => 'CurrentLoadedWeight', 'val' => (float) ($CurrentLoadedWeight + $orderdetails['totwt'])));
                        array_push($arrLiveOrder['Data'], array('col' => 'CurrentLoadedVolume', 'val' => (float) ($CurrentLoadedVolume + $orderdetails['totvol'])));
                    }
                    array_push($arrLiveOrder['Data'], array('col' => 'IsEngaged', 'val' => 1));
                    array_push($arrLiveOrder['Data'], array('col' => 'TotalJobs', 'val' => $TotJobs + 1));
                    $nors = $nodb->perform('QugeoLiveVehicles', 'update', $arrLiveOrder, $response);
                } else {
                    $arrOrder['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
                    $arrOrder['SortKey'] = array('col' => 'orderid', 'val' => $value['orderid'], 'oper' => '=');
                    $arrOrder['Data'] = array();
                    array_push($arrOrder['Data'], array('col' => 'IsLiveOrder', 'val' => (int) ($NewLiveOrder == $value['orderid'] ? 1 : 0)));
                    array_push($arrOrder['Data'], array('col' => 'order', 'val' => (int) $value['order']));
                    $NewOrder = $nodb->perform('QugeoLiveVehicleOrders', 'update', $arrOrder, $response);
                }
                if ($getOrder && $IsMilestoneLocked == 0) {
                    //echo "NewLiveOrder " . $NewLiveOrder . " LiveOrder " . $LiveOrder . " value[orderid] ". $value['orderid'] . " NewLiveOrder " . $NewLiveOrder ."\n"  ;
                    if ($NewLiveOrder != $LiveOrder && $value['orderid'] == $NewLiveOrder) {
                        $arrOrderDetails['PartitionKey'] = array('col' => 'orderid', 'val' => $NewLiveOrder, 'oper' => '=');
                        $arrOrderDetails['getAttributes'] = array('pickupLat', 'pickupLng', 'deliveryLat', 'deliveryLng', 'BrLat', 'BrLong', 'pickupmobile', 'deliverymobile', 'AcceptedAsDirectDelivery', 'pickupname', 'deliveryname', 'pickupaddress', 'deliveryaddress', 'pickuplocation', 'deliverylocation', 'quor_RefNo', 'pktcount', 'chrgwt', 'netamt', 'DelAssetBucket', 'ImgSignLocation', 'ImgCustLocation', 'IsPickup', 'totwt', 'Consignment', 'pickupOTP', 'deliveryOTP', 'quor_id', 'HandlingBranch');
                        $rsno = $nodb->query('QugeoOrderDetails', $arrOrderDetails, 'getItem');
                        $isnewroute = true;

                        if ($rsno['IsPickup'] == '1') {
                            $lat = $rsno['pickupLat'];
                            $long = $rsno['pickupLng'];
                        } else {
                            $lat = $rsno['deliveryLat'];
                            $long = $rsno['deliveryLng'];
                        }
                        $nextorder['istriprerouted'] = true;
                        $nextorder['mapdetails'] = array('latitude' => $lat, 'longitude' => $long, 'zoomlevel' => '10', 'locationicon' => "https://maps.google.com/mapfiles/ms/icons/yellow-dot.png");
                        $nextorder['nextorderdetails'] = array('ispickup' => ($rsno['IsPickup'] == 1 ? true : false), 'orderno' => $rsno['quor_RefNo'], 'customer' => ($rsno['IsPickup'] == 1 ? $rsno['pickupname'] : $rsno['deliveryname']), 'address' => ($rsno['IsPickup'] == 1 ? $rsno['pickupaddress'] : $rsno['deliveryaddress']), 'location' => ($rsno['IsPickup'] == 1 ? $rsno['pickuplocation'] : $rsno['deliverylocation']), 'contph' => ($rsno['IsPickup'] == 1 ? $rsno['pickupmobile'] : $rsno['deliverymobile']), 'noofboxes' => $rsno['pktcount'], 'weight' => $rsno['totwt'] . 'kg', 'cashtobecollected' => $rsno['netamt'], 'orderid' => $NewLiveOrder, 'orderdetails' => $rsno['Consignment'], 'otp' => ($rsno['IsPickup'] == 1 ? $rsno['pickupOTP'] : $rsno['deliveryOTP']));
                        if ($rsno['IsPickup'] == 0) {
                            $nextorder['nextorderdetails']['deluploadcreds'] = array('S3BUCKET' => QUGEO_S3_UPLOAD_BUCKET, 'ImgSign' => $rsno['ImgSignLocation'], 'ImgCust' => $rsno['ImgCustLocation'], 'ACCESSKEY' => QUGEO_S3_UPLOAD_ACCESS, 'SECRETKEY' => QUGEO_S3_UPLOAD_SECRET, 'REGION' => QUGEO_S3_UPLOAD_REGION, 'ENDPOINT' => QUGEO_S3_UPLOAD_ENDPOINT);
                            //$sqldbconn = new \SqlDB(DSN);	
                            //Your {#var#} Order No.{#var#} has been picked up by delivery partner {#var#}. Please check for items carefully while accepting the order
                            //1607100000000004817
                            $qry = "Your " . PROJECT_NAME . " Order No." . $rsno['quor_RefNo'] . " has been picked up by delivery partner " . $DriverName . ". Please check for items carefully while accepting the order";
                            //\sms::send($rsno['deliverymobile'], $qry, $sqldbconn, "");
                            $templatedata['order_order_id'] = $rsno['quor_RefNo'];
                            \sms::fetchContentSendSms($templatedata, $rsno['deliverymobile'], 12);
                        } else {
                        }
                        $this->UpdateReleasingLocation($apikey, $rsno['deliveryLat'], $rsno['deliveryLng']);
                    }
                }
            }
        }

        public function AssignOrderToQugeoDriverSch($orderid, $apikey, $orderdetails, &$nextorder, &$isnewroute, $getOrder = true)
        {
            $arrOrder = array();
            $arrOrder['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
            $arrOrder['getAttributes'] = array('Latitude', 'Longitude', 'Home_Latitude', 'Home_Longitude', 'AssignedLoadedWeight', 'AssignedLoadedVolume', 'CurrentLoadedVolume', 'CurrentLoadedWeight', 'TotalJobs', 'DriverName', 'mobno');
            $nodb = new \cgoDynamiteDB();
            $rsno = $nodb->query('QugeoLiveVehicles', $arrOrder, 'getItem');
            $AssignedLoadedWeight = floatval($rsno['AssignedLoadedWeight']);
            $AssignedLoadedVolume = floatval($rsno['AssignedLoadedVolume']);
            $CurrentLoadedVolume = floatval($rsno['CurrentLoadedVolume']);
            $CurrentLoadedWeight = floatval($rsno['CurrentLoadedWeight']);
            $TotJobs = intval($rsno['TotalJobs']);
            $DriverName = $rsno['DriverName'];
            $DriverMobNo = $rsno['mobno'];
            $arrVehicle['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
            //$arrVehicle['IndexName'] = 'apikey-IsClosed-index';
            $arrVehicle['queryAttributes'] = array('orderid', 'order', 'Latitude', 'Longitude', 'IsClosed', 'IsLiveOrder', 'IsPickup', 'IsMilestoneLock');
            //array_push($arrVehicle['Condition'],array('col'=>'IsClosed','val'=>0,'oper'=>'='));				
            $nors = $nodb->query('QugeoLiveVehicleOrders', $arrVehicle, 'query');
            $order = array();
            $waypoints = '';
            $LiveOrder = '';
            $IsMilestoneLocked = 0;
            $NewOrderLat = ($orderdetails['IsPickup'] == 1 ? $orderdetails['pickupLat'] : $orderdetails['deliveryLat']);
            $NewOrderLong = ($orderdetails['IsPickup'] == 1 ? $orderdetails['pickupLng'] : $orderdetails['deliveryLng']);
            $CurrentOpenOrders = array();

            file_put_contents('php://stderr', "-----------------else--------CurrentOpenOrders-------------------------------");
            $isLiveOrder = 1;
            $isOnlyOrder = true;
            $NewLiveOrder = $orderid;
            $CurrentOrders = array();
            array_push($CurrentOpenOrders, array('orderid' => $orderid, 'Latitude' => $NewOrderLat, 'Longitude' => $NewOrderLong, 'IsClosed' => 0, 'IsLiveOrder' => 0, 'IsPickup' => $orderdetails['IsPickup'], 'order' => QUGEO_LIVE_ORDER_INCREMENT_VALUE));
            $isnewroute = false;
            $nextorder = array();
            $db = new \cgoSqlDB();
            $isSignature = $db->getItemFromDB("select cfg_Value from sys_configuration  where cfg_Name = 'IS_SIGNATURE'", array(), true);
            file_put_contents('php://stderr', "-----------------isSignature-------------------------------");
            file_put_contents('php://stderr', print_r($isSignature, TRUE));
            $nextorder['isSignature'] = $isSignature;
            $nextorder['istriprerouted'] = false;
            $nextorder['mapdetails'] = array();
            $nextorder['nextorderdetails'] = array();
            file_put_contents('php://stderr', "-----------------CurrentOpenOrders-------------------------------");
            file_put_contents('php://stderr', print_r($CurrentOpenOrders, TRUE));
            foreach ($CurrentOpenOrders as $value) {
                $arrOrder = array();
                $arrOrder['Data'] = array();
                $valdate = date("Ymd");
                $valdatetime = date("YmdHis");
                file_put_contents('php://stderr', "-----------------orderid-------------------------------");
                file_put_contents('php://stderr', print_r($orderid, TRUE));
                file_put_contents('php://stderr', "-----------------value-------------------------------");
                file_put_contents('php://stderr', print_r($value, TRUE));
                if ($value['orderid'] == $orderid) {
                    array_push($arrOrder['Data'], array('col' => 'apikey', 'val' => $apikey));
                    array_push($arrOrder['Data'], array('col' => 'orderid', 'val' => $orderid));
                    array_push($arrOrder['Data'], array('col' => 'IsLiveOrder', 'val' => (int) ($isOnlyOrder || $NewLiveOrder == $value['orderid'] ? 1 : 0)));
                    array_push($arrOrder['Data'], array('col' => 'order', 'val' => (int) $value['order']));
                    array_push($arrOrder['Data'], array('col' => 'createddatetime', 'val' => (int) $valdatetime));
                    array_push($arrOrder['Data'], array('col' => 'createddate', 'val' => (int) $valdate));
                    array_push($arrOrder['Data'], array('col' => 'IsPickup', 'val' => (int) $value['IsPickup']));
                    array_push($arrOrder['Data'], array('col' => 'IsClosed', 'val' => 0));
                    array_push($arrOrder['Data'], array('col' => 'Latitude', 'val' => (float) $NewOrderLat));
                    array_push($arrOrder['Data'], array('col' => 'Longitude', 'val' => (float) $NewOrderLong));
                    array_push($arrOrder['Data'], array('col' => 'HandlingBranch', 'val' => (int) $orderdetails['HandlingBranch']));
                    array_push($arrOrder['Data'], array('col' => 'IsMilestoneLock', 'val' => 0));
                    $NewOrder = $nodb->perform('QugeoLiveVehicleOrders', 'insert', $arrOrder, $response);
                    $arrLiveOrder = array();
                    $arrLiveOrder['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
                    $arrLiveOrder['Data'] = array();
                    $UpdateVehicleLoad = false;
                    if ($orderdetails['WasDirectDeliveryPickUp'] != '1') {
                        $UpdateVehicleLoad = true;
                        array_push($arrLiveOrder['Data'], array('col' => 'AssignedLoadedWeight', 'val' => (float) ($AssignedLoadedWeight + $orderdetails['totwt'])));
                        array_push($arrLiveOrder['Data'], array('col' => 'AssignedLoadedVolume', 'val' => (float) ($AssignedLoadedVolume + $orderdetails['totvol'])));
                    }

                    if ($orderdetails['IsPickup'] != '1' && $orderdetails['WasDirectDeliveryPickUp'] != '1') {
                        $UpdateVehicleLoad = true;
                        array_push($arrLiveOrder['Data'], array('col' => 'CurrentLoadedWeight', 'val' => (float) ($CurrentLoadedWeight + $orderdetails['totwt'])));
                        array_push($arrLiveOrder['Data'], array('col' => 'CurrentLoadedVolume', 'val' => (float) ($CurrentLoadedVolume + $orderdetails['totvol'])));
                    }
                    array_push($arrLiveOrder['Data'], array('col' => 'IsEngaged', 'val' => 1));
                    array_push($arrLiveOrder['Data'], array('col' => 'TotalJobs', 'val' => $TotJobs + 1));
                    $nors = $nodb->perform('QugeoLiveVehicles', 'update', $arrLiveOrder, $response);
                } else {
                    $arrOrder['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
                    $arrOrder['SortKey'] = array('col' => 'orderid', 'val' => $value['orderid'], 'oper' => '=');
                    $arrOrder['Data'] = array();
                    array_push($arrOrder['Data'], array('col' => 'IsLiveOrder', 'val' => (int) ($NewLiveOrder == $value['orderid'] ? 1 : 0)));
                    array_push($arrOrder['Data'], array('col' => 'order', 'val' => (int) $value['order']));
                    $NewOrder = $nodb->perform('QugeoLiveVehicleOrders', 'update', $arrOrder, $response);
                }
                file_put_contents('php://stderr', "-----------------IsMilestoneLocked-------------------------------");
                file_put_contents('php://stderr', print_r($IsMilestoneLocked, TRUE));
                file_put_contents('php://stderr', "-----------------getOrder-------------------------------");
                file_put_contents('php://stderr', print_r($getOrder, TRUE));
                if ($getOrder && $IsMilestoneLocked == 0) {
                    //echo "NewLiveOrder " . $NewLiveOrder . " LiveOrder " . $LiveOrder . " value[orderid] ". $value['orderid'] . " NewLiveOrder " . $NewLiveOrder ."\n"  ;
                    if ($NewLiveOrder != $LiveOrder && $value['orderid'] == $NewLiveOrder) {
                        $arrOrderDetails['PartitionKey'] = array('col' => 'orderid', 'val' => $NewLiveOrder, 'oper' => '=');
                        $arrOrderDetails['getAttributes'] = array('pickupLat', 'pickupLng', 'deliveryLat', 'deliveryLng', 'BrLat', 'BrLong', 'pickupmobile', 'deliverymobile', 'AcceptedAsDirectDelivery', 'pickupname', 'deliveryname', 'pickupaddress', 'deliveryaddress', 'pickuplocation', 'deliverylocation', 'quor_RefNo', 'pktcount', 'chrgwt', 'netamt', 'DelAssetBucket', 'ImgSignLocation', 'ImgCustLocation', 'IsPickup', 'totwt', 'Consignment', 'pickupOTP', 'deliveryOTP', 'quor_id', 'HandlingBranch');
                        $rsno = $nodb->query('QugeoOrderDetails', $arrOrderDetails, 'getItem');
                        $isnewroute = true;
                        if ($rsno['IsPickup'] == '1') {
                            $lat = $rsno['pickupLat'];
                            $long = $rsno['pickupLng'];
                        } else {
                            $lat = $rsno['deliveryLat'];
                            $long = $rsno['deliveryLng'];
                        }
                        $nextorder['istriprerouted'] = true;
                        $quor_TransferOrder_Type = $db->getItemFromDB("select quor_TransferOrder_Type from qugeo_order  where quor_RefNo = '{$rsno['quor_RefNo']}'", array(), true);
                        switch ($quor_TransferOrder_Type) {
                            case 1:
                                $orderPk = $db->getItemFromDB("select order_id from retaline_customer_order  where order_order_id = '{$rsno['quor_RefNo']}'", array(), true);
                                break;
                            default:
                                $orderPk = 0;
                                break;
                        }
                        $nextorder['mapdetails'] = array('latitude' => $lat, 'longitude' => $long, 'zoomlevel' => '10', 'locationicon' => "https://maps.google.com/mapfiles/ms/icons/yellow-dot.png");
                        $nextorder['nextorderdetails'] = array('ispickup' => ($rsno['IsPickup'] == 1 ? true : false), 'order_id' => $orderPk, 'orderType' => $quor_TransferOrder_Type, 'orderno' => $rsno['quor_RefNo'], 'customer' => ($rsno['IsPickup'] == 1 ? $rsno['pickupname'] : $rsno['deliveryname']), 'address' => ($rsno['IsPickup'] == 1 ? $rsno['pickupaddress'] : $rsno['deliveryaddress']), 'location' => ($rsno['IsPickup'] == 1 ? $rsno['pickuplocation'] : $rsno['deliverylocation']), 'contph' => ($rsno['IsPickup'] == 1 ? $rsno['pickupmobile'] : $rsno['deliverymobile']), 'noofboxes' => $rsno['pktcount'], 'weight' => $rsno['totwt'] . 'kg', 'cashtobecollected' => $rsno['netamt'], 'orderid' => $NewLiveOrder, 'orderdetails' => $rsno['Consignment'], 'otp' => ($rsno['IsPickup'] == 1 ? $rsno['pickupOTP'] : $rsno['deliveryOTP']));
                        if ($rsno['IsPickup'] == 0) {
                            $nextorder['nextorderdetails']['deluploadcreds'] = array('S3BUCKET' => QUGEO_S3_UPLOAD_BUCKET, 'ImgSign' => $rsno['ImgSignLocation'], 'ImgCust' => $rsno['ImgCustLocation'], 'ACCESSKEY' => QUGEO_S3_UPLOAD_ACCESS, 'SECRETKEY' => QUGEO_S3_UPLOAD_SECRET, 'REGION' => QUGEO_S3_UPLOAD_REGION, 'ENDPOINT' => QUGEO_S3_UPLOAD_ENDPOINT);
                            $sqldbconn = new \SqlDb(DSN);
                            //Your {#var#} Order No.{#var#} has been picked up by delivery partner {#var#}. Please check for items carefully while accepting the order
                            //1607100000000004817
                            $qry = "Your " . PROJECT_NAME . " Order No." . $rsno['quor_RefNo'] . " has been picked up by delivery partner " . $DriverName . ". Please check for items carefully while accepting the order";
                            //\sms::send($rsno['deliverymobile'], $qry, $sqldbconn, "");
                            $templatedata['order_order_id'] = $rsno['quor_RefNo'];
                            \sms::fetchContentSendSms($templatedata, $rsno['deliverymobile'], 12);
                        } else {
                        }
                        $this->UpdateReleasingLocation($apikey, $rsno['deliveryLat'], $rsno['deliveryLng']);
                    }
                }
            }
        }

        public function GetNextOrder($apikey, &$nextorder, $readonly = false,$currentOrderId = '')
        {
            file_put_contents('php://stderr', "GetNextOrder  begin\n ");
            file_put_contents('php://stderr', print_r($currentOrderId, TRUE));
            $arrVehicle = array();
            $arrVehicle['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');              
            $arrVehicle['queryAttributes'] = array('orderid', 'order', 'IsLiveOrder');
            $arrVehicle['Condition'] = array();
            array_push($arrVehicle['Condition'], array('col' => 'IsClosed', 'val' => 0, 'oper' => '='));
            $nodb = new \cgoDynamiteDB();
            $db = new \cgoSqlDB();
            $nors = $nodb->query('QugeoLiveVehicleOrders', $arrVehicle, 'query');
            file_put_contents('php://stderr', "GetNextOrder  GetNextOrderGetNextOrder\n ");
            file_put_contents('php://stderr', print_r($nors, TRUE));
            if (isset($nors) && count($nors) > 0) {
                $CurrentOrders = array();
                $order = array();
                $HasLiveOrder = false;
                foreach ($nors as $value) {
                    array_push($CurrentOrders, array('orderid' => $value['orderid'], 'order' => $value['order']));
                    if ($value['IsLiveOrder'] == 1) {
                        $HasLiveOrder = true;
                        $Orderid = $value['orderid'];
                        break;
                    }
                    //$order['order']=$value['order'];
                }
                //array_multisort($order, SORT_ASC, $CurrentOrders);
                file_put_contents('php://stderr', "GetNextOrder  HasLiveOrder\n ");
            file_put_contents('php://stderr', print_r($HasLiveOrder, TRUE));
                if (!$HasLiveOrder) {
                    finascop_aasort($CurrentOrders, 'order');
                    $Orderid = $CurrentOrders[0]['orderid'];
                }
                if (!empty($currentOrderId)) {
                    $Orderid = $currentOrderId;
                }
                $arrOrderDetails['PartitionKey'] = array('col' => 'orderid', 'val' => $Orderid, 'oper' => '=');
                $arrOrderDetails['getAttributes'] = array('pickupLat', 'pickupLng', 'deliveryLat', 'deliveryLng', 'BrLat', 'BrLong', 'pickupmobile', 'deliverymobile', 'AcceptedAsDirectDelivery', 'pickupname', 'deliveryname', 'pickupaddress', 'deliveryaddress', 'pickuplocation', 'deliverylocation', 'quor_RefNo', 'pktcount', 'chrgwt', 'netamt', 'DelAssetBucket', 'ImgSignLocation', 'ImgCustLocation', 'IsPickup', 'totwt', 'Consignment', 'pickupOTP', 'deliveryOTP');
                $rsno = $nodb->query('QugeoOrderDetails', $arrOrderDetails, 'getItem');
                if ($rsno['IsPickup'] == '1') {
                    $lat = $rsno['pickupLat'];
                    $long = $rsno['pickupLng'];
                } else {

                    $lat = $rsno['deliveryLat'];
                    $long = $rsno['deliveryLng'];
                }
                if ($readonly == false) {
                    $arrUpdate = array();
                    $arrUpdate['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
                    $arrUpdate['SortKey'] = array('col' => 'orderid', 'val' => $Orderid, 'oper' => '=');
                    $arrUpdate['Data'] = array();
                    array_push($arrUpdate['Data'], array('col' => 'IsLiveOrder', 'val' => 1));
                    $NewOrder = $nodb->perform('QugeoLiveVehicleOrders', 'update', $arrUpdate, $response);
                }
                $quor_TransferOrder_Type = $db->getItemFromDB("select quor_TransferOrder_Type from qugeo_order  where quor_RefNo = '{$rsno['quor_RefNo']}'", array(), true);
                switch ($quor_TransferOrder_Type) {
                    case 1:
                        $orderPk = $db->getItemFromDB("select order_id from retaline_customer_order  where order_order_id = '{$rsno['quor_RefNo']}'", array(), true);
                        break;
                    default:
                        $orderPk = 0;
                        break;
                }
                $nextorder['istriprerouted'] = true;
                $nextorder['mapdetails'] = array('latitude' => $lat, 'longitude' => $long, 'zoomlevel' => '10', 'locationicon' => "https://maps.google.com/mapfiles/ms/icons/yellow-dot.png");

                $nextorder['nextorderdetails'] = array('ispickup' => ($rsno['IsPickup'] == 1 ? true : false), 'order_id' => $orderPk, 'orderType' => $quor_TransferOrder_Type,'orderno' => $rsno['quor_RefNo'], 'customer' => ($rsno['IsPickup'] == 1 ? $rsno['pickupname'] : $rsno['deliveryname']), 'address' => ($rsno['IsPickup'] == 1 ? $rsno['pickupaddress'] : $rsno['deliveryaddress']), 'location' => ($rsno['IsPickup'] == 1 ? $rsno['pickuplocation'] : $rsno['deliverylocation']), 'contph' => ($rsno['IsPickup'] == 1 ? $rsno['pickupmobile'] : $rsno['deliverymobile']), 'noofboxes' => $rsno['pktcount'], 'weight' => $rsno['totwt'] . 'kg', 'cashtobecollected' => $rsno['netamt'], 'orderid' => $Orderid, 'orderdetails' => $rsno['Consignment'], 'otp' => ($rsno['IsPickup'] == 1 ? $rsno['pickupOTP'] : $rsno['deliveryOTP']));

                if ($rsno['IsPickup'] == 0) {
                    $nextorder['nextorderdetails']['deluploadcreds'] = array('S3BUCKET' => QUGEO_S3_UPLOAD_BUCKET, 'ImgSign' => $rsno['ImgSignLocation'], 'ImgCust' => $rsno['ImgCustLocation'], 'ACCESSKEY' => QUGEO_S3_UPLOAD_ACCESS, 'SECRETKEY' => QUGEO_S3_UPLOAD_SECRET, 'REGION' => QUGEO_S3_UPLOAD_REGION, 'ENDPOINT' => QUGEO_S3_UPLOAD_ENDPOINT);
                }
                return true;
            } else {
                $nextorder['istriprerouted'] = false;
                $arrLiveOrder = array();
                $arrLiveOrder['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
                $arrLiveOrder['Data'] = array();
                array_push($arrLiveOrder['Data'], array('col' => 'IsEngaged', 'val' => 0));
                $nors = $nodb->perform('QugeoLiveVehicles', 'update', $arrLiveOrder, $response);
                return false;
            }
        }

        public function UpdateReleasingLocation($apikey, $OnJobCompletionLatitude, $OnJobCompletionLongitude)
        {
            $nodb = new \cgoDynamiteDB();
            $arrLiveOrder = array();
            $arrLiveOrder['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
            $arrLiveOrder['Data'] = array();
            array_push($arrLiveOrder['Data'], array('col' => 'OnJobCompletionLatitude', 'val' => (float) $OnJobCompletionLatitude));
            array_push($arrLiveOrder['Data'], array('col' => 'OnJobCompletionLongitude', 'val' => (float) $OnJobCompletionLongitude));
            $nors = $nodb->perform('QugeoLiveVehicles', 'update', $arrLiveOrder, $response);
        }

        public function GetOrdersList($apikey, $type)
        {
            /*
              $type = 1 -- All Orders
              $type = 2 -- Orders to complete
              $type = 3 -- Picked Up complete
              $type = 4 -- Delivery Up complete
             */
            $nodb = new \cgoDynamiteDB();
            $db = new \sqlDb(DSN);
            //Get vehicle session information
            $arrAPI = array();
            $arrAPI['PartitionKey'] = array('col' => 'apikey', 'type' => 'S', 'val' => $apikey);
            $arrAPI['getAttributes'] = array('HasLoggedOut', 'LoggedOutAt', 'IsCleanLogout');
            $rsno = $nodb->query('APIHistory', $arrAPI, 'getItem');
            $HasLoggedOut = $rsno['HasLoggedOut'];
            $LoggedOutAt = $rsno['LoggedOutAt'];
            $IsCleanLogout = $rsno['IsCleanLogout'];

            //Get Orders 
            $arrAPI = array();
            $arrAPI['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
            $arrAPI['SortKey'] = array('col' => 'IsClosed', 'val' => 10, 'oper' => '<');
            $arrAPI['IndexName'] = 'apikey-IsClosed-index';
            $arrAPI['queryAttributes'] = array('order', 'orderid', 'IsClosed', 'IsPickup');
            $rsno = $nodb->query('QugeoLiveVehicleOrders', $arrAPI, 'query');
            $orderdetails = array();
            if (isset($rsno) && count($rsno) > 0) {

                foreach ($rsno as $value) {
                    $arrOrders = array();
                    $arrOrders['PartitionKey'] = array('col' => 'orderid', 'val' => $value['orderid']);
                    $arrOrders['getAttributes'] = array('OrderStatus', 'IsClosed', 'IsPickup', 'bkno', 'pickuplocation', 'deliverylocation', 'chrgwt', 'totwt', 'totvol', 'netamt', 'paymode', 'pktcount', 'pickupLat', 'pickupLng', 'deliveryLat', 'deliveryLng', 'ClosedAt');
                    $nors = $nodb->query('QugeoOrderDetails', $arrOrders, 'getItem');
                    $icon = '';
                    $skip = false;
                    $failed = false;
                    if ($value['IsClosed'] == 1) {
                        if ($type == 2) {
                            $skip = true;
                        }
                        if ($value['IsPickup'] == "1") {
                            if ($nors['OrderStatus'] == ORDER_PICKUP_PICKEDUP_TOBR_DLS_ID || $nors['OrderStatus'] == ORDER_PICKUP_PICKEDUP_TODST_DLS_ID) {
                                $icon = GMAP_PIKCUP_DONE_ICON;
                            } else {
                                $failed = true;
                                $icon = GMAP_PIKCUP_FAILED_ICON;
                            }
                        } else {
                            if ($nors['OrderStatus'] == ORDER_DELIVERY_MARKED_DLS_ID) {
                                $icon = GMAP_DELIVERY_DONE_ICON;
                            } else {
                                $failed = true;
                                $icon = GMAP_DELIVERY_FAILED_ICON;
                            }
                        }
                    } else {
                        if ($type == 3 || $type == 4) {
                            $skip = true;
                        }
                        if ($HasLoggedOut == 1) {
                            if ($value['IsPickup'] == "1") {
                                $icon = GMAP_PIKCUP_TERMINATED_ICON;
                            } else {
                                $icon = GMAP_DELIVERY_TERMINATED_ICON;
                            }
                        } else {
                            if ($value['IsPickup'] == "1") {
                                $icon = GMAP_PIKCUP_ICON;
                            } else {
                                $icon = GMAP_DELIVERY_ICON;
                            }
                        }
                    }
                    if ($value['IsPickup'] == "1") {
                        if ($type == 4) {
                            $skip = true;
                        }
                        $locaname = $nors['pickuplocation'];
                        $lat = $nors['pickupLat'];
                        $lon = $nors['pickupLng'];
                    } else {
                        if ($type == 3) {
                            $skip = true;
                        }
                        $locaname = $nors['deliverylocation'];
                        $lat = $nors['deliveryLat'];
                        $lon = $nors['deliveryLng'];
                    }
                    if ($_POST['type'] == 'Order' && $_POST['type_value'] == $value['orderid']) {
                        $animate = true;
                    } else {
                        $animate = false;
                    }
                    if ($nors['ClosedAt'] != '') {
                        $closedat = date("H:i:s", strtotime($nors['ClosedAt']));
                    }
                    $dls_DelStatus = $db->getItemFromDb('select dls_DelStatus from  qugeo_deliverystatus where dls_ID = ?', array('i', $nors['OrderStatus']));
                    if ($skip == false) {
                        array_push($orderdetails, array('order' => $value['order'], 'orderid' => $value['orderid'], 'ordericon' => $icon, 'bkno' => $nors['bkno'], 'locationname' => $locaname, 'netamt' => $nors['netamt'], 'paymode' => $nors['paymode'], 'pktcount' => $nors['pktcount'], 'totwt' => $nors['totwt'], 'totvol' => $nors['totvol'], 'Latitude' => $lat, 'Longitude' => $lon, 'animate' => $animate, 'ordertype' => ($value['IsPickup'] == "1" ? 'Pickup' : 'Delivery'), 'CompletedAt' => $closedat, 'OrderStatus' => $dls_DelStatus, 'IsFailed' => $failed));
                    }
                }
            }
            return $orderdetails;
        }
    }
}
