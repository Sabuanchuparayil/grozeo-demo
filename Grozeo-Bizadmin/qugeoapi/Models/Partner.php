<?php

namespace Models; {

    class Partner extends ModelAbstract
    {

        public function POST_loadVehicleDetails($flag, $request)
        {
            file_put_contents('php://stderr', "....................................POST_loadVehicleDetails...................................................... \n");
            file_put_contents('php://stderr', print_r($request, TRUE));
            file_put_contents('php://stderr', print_r($flag, TRUE));
            if (!array_key_exists('br_id', $request) || !array_key_exists('longitude', $request) || !array_key_exists('latitude', $request) || !isset($request)) {
                throw new \Exception('Missing POST parameters ');
            }
            if ($request['br_id'] == '') {
                throw new \Exception('Invalid branch');
            }
            if ($request['longitude'] == '') {
                throw new \Exception('Invalid longitude');
            }
            if ($request['latitude'] == '') {
                throw new \Exception('Invalid latitude');
            }
            $longitude = $_POST['longitude'];
            $latitude = $_POST['latitude'];

            $userType = $_POST['userType'];
            $userId = $_POST['userId'];

            $nodb = new \cgoDynamiteDB();

            $degMat = new \cgoGeoUtilities();

            $db = new \cgoSqlDB();
            $storeGroupId = $db->getFromDB("SELECT br_storeGroup,br_Name FROM  finascop_branch WHERE br_ID =  ?", array('i', $request['br_id']), true);

            $arrDegrees = $degMat->getDegreeMatrix($longitude, $latitude, QC_VEHICLE_NEAR_PICKUP_CIRCLE_DIST);
            file_put_contents('php://stderr', "....................................POST_loadVehicleDetails arrDegrees...................................................... \n");
            file_put_contents('php://stderr', print_r($arrDegrees, TRUE));
            file_put_contents('php://stderr', print_r($storeGroupId, TRUE));

            $attVehicles = array();
            $attVehicles['PartitionKey'] = array('col' => 'Is_Live', 'val' => 1, 'oper' => '=');

            $attVehicles['SortKey'] = array('col' => 'Latitude', 'val1' => (float) $arrDegrees['lat1'], 'val2' => (float) $arrDegrees['lat2'], 'SortKeyBetween' => true);

            //$attVehicles['SortKey']=array('col'=>'Latitude','type'=>'N','val'=>$arrDegrees['lat1'],'oper'=>'=');				
            $attVehicles['IndexName'] = 'Is_Live-Latitude-index';

            $attVehicles['queryAttributes'] = array('apikey', 'v_id', 'v_no', 'Latitude', 'Longitude', 'LocationUpdateddatetime', 'DriverName', 'v_typename', 'v_capacity', 'CurrentLoadedWeight', 'v_MapIcon');

            $attVehicles['Condition'] = array();

            array_push($attVehicles['Condition'], array('col' => 'Longitude', 'val1' => (float) $arrDegrees['lon1'], 'val2' => (float) $arrDegrees['lon2'], 'ConditionBetween' => true));
            //array_push($attVehicles['Condition'], array('col' => 'isallowAutoSchedule', 'val' => (int)1, 'oper' => '='));

            //storegroup drivers
            //array_push($attVehicles['Condition'], array('col' => 'DriverBranchId', 'val' => (int)$_POST['br_id'], 'oper' => '='));
            if ($userType > 0 && $userId > 0) {
                array_push($attVehicles['Condition'], array('col' => 'createdBy', 'val' => (int)$userType, 'oper' => '='));
                array_push($attVehicles['Condition'], array('col' => 'sourceId', 'val' => (int)$userId, 'oper' => '='));
            } else {
                array_push($attVehicles['Condition'], array('col' => 'createdBy', 'val' => (int)1, 'oper' => '='));
                array_push($attVehicles['Condition'], array('col' => 'sourceId', 'val' => (int)$storeGroupId['br_storeGroup'], 'oper' => '='));
            }
            $vehicle = array();
            file_put_contents('php://stderr', print_r($attVehicles, TRUE));
            //echo json_encode($attVehicles)."\n";
            $rsno = $nodb->query('QugeoLiveVehicles', $attVehicles, 'query');
            if (isset($rsno) && count($rsno) > 0) {
                $rs = array();
                $vehicle['success'] = true;
                $vehicle['msg'] = 'Vehicle details';
                foreach ($rsno as $value) {
                    array_push($rs, array('v_ID' => $value['apikey'], 'v_No' => $value['v_no'], 'Latitude' => $value['Latitude'], 'Longitude' => $value['Longitude'], 'LastLocationDtTm' => $value['LocationUpdateddatetime'], 'DriverName' => $value['DriverName'], 'Vehicletypename' => $value['v_typename'], 'MaxLoad' => $value['v_capacity'], 'CurrentLoad' => $value['CurrentLoadedWeight'], 'v_MapIcon' => $value['v_MapIcon']));
                }
                $vehicle['Data'] = $rs;
            } else {
                //drivers other than storegroup
                $vehicle = array();
                /*array_push($attVehicles['Condition'], array('col' => 'createdBy', 'val' => (int)1, 'oper' => '>'));               
                //echo json_encode($attVehicles)."\n";
                $rsno = $nodb->query('QugeoLiveVehicles', $attVehicles, 'query');
                if (isset($rsno) && count($rsno) > 0) {
                    $rs = array();
                    $vehicle['success'] = true;
                    $vehicle['msg'] = 'Vehicle details';
                    foreach ($rsno as $value) {
                        array_push($rs, array('v_ID' => $value['apikey'], 'v_No' => $value['v_no'], 'Latitude' => $value['Latitude'], 'Longitude' => $value['Longitude'], 'LastLocationDtTm' => $value['LocationUpdateddatetime'], 'DriverName' => $value['DriverName'], 'Vehicletypename' => $value['v_typename'], 'MaxLoad' => $value['v_capacity'], 'CurrentLoad' => $value['CurrentLoadedWeight'], 'v_MapIcon' => $value['v_MapIcon']));
                    }
                    $vehicle['Data'] = $rs;
                } else {*/
                $vehicle['success'] = false;
                $vehicle['msg'] = 'No vehicles found';
                $vehicle['Data'] = array();
                //}
            }

            return $vehicle;
        }

        public function POST_scheduleABookingJobs($flag, $request)
        {
            file_put_contents('php://stderr', "....................................POST_scheduleABookingJobs...................................................... \n");
            file_put_contents('php://stderr', print_r($request, TRUE));
            if (!array_key_exists('quorIds', $request) || !array_key_exists('qugeobk_NO', $request) || !array_key_exists('br_id', $request) || !array_key_exists('handling_br_id', $request) || !array_key_exists('hdnVehicleId', $request) || !array_key_exists('type', $request) || !isset($request)) {
                throw new \Exception('Missing POST parameters ');
            }

            if ($request['quorIds'] == '') {
                throw new \Exception('Invalid Ids');
            }
            if ($request['qugeobk_NO'] == '') {
                throw new \Exception('Invalid qugeobk_NO');
            }
            if ($request['br_id'] == '') {
                throw new \Exception('Invalid branch');
            }
            if ($request['handling_br_id'] == '') {
                throw new \Exception('Invalid handing branch');
            }
            if ($request['hdnVehicleId'] == '') {
                throw new \Exception('Invalid Vehicle Id');
            }
            if ($request['type'] == '') {
                throw new \Exception('Invalid type');
            }

            $uniqueId = uniqid();

            $br_id = intval($request['br_id']);
            $isScheduled = intval($request['isScheduled']);
            $handling_br_id = intval($request['handling_br_id']);
            $v_id = $request['hdnVehicleId'];
            $type = $request['type'];
            $db = new \cgoSqlDB();
            $scheduleorder = new QugeoScheduler();
            $poller = new QugeoOrderPoller();
            if ($isScheduled == 1) {
                $string = str_replace(array('[', ']', '"'), '', $request['quorIds']);
                $quorIds = explode(',', $string);
                $totalOrders = count($quorIds);

                $geoData = array();
                for ($i = 0; $i < count($quorIds); $i++) {
                    file_put_contents('php://stderr', print_r($quorIds[$i], TRUE));
                    $delLocations = $db->getFromDB("SELECT quor_id,quor_PickupPincode,quor_PickupLat,quor_PickupLng,quor_PickupLocation,quor_DeliveryPincode,quor_DeliveryLat,quor_DeliveryLng,quor_DeliveryLocation,"
                        . "if(quor_Status=22,'PICKUP', if(quor_Status=31,'DELIVERY','')) as drivetype,quor_Pickupbr_id,quor_Deliverybr_id FROM  qugeo_order WHERE quor_id = ? ", array('i', intval($quorIds[$i])), true);
                    file_put_contents('php://stderr', print_r($delLocations, TRUE));
                    array_push($geoData, array('quor_id' => $delLocations['quor_id'], 'latitude' => $delLocations['quor_DeliveryLat'], 'longitude' => $delLocations['quor_DeliveryLng'], 'zipcode' => $delLocations['quor_DeliveryPincode']));
                    $quor_PickupLat = $delLocations['quor_PickupLat'];
                    $quor_PickupLng = $delLocations['quor_PickupLng'];
                }

                $slotNearestdeliveryLocations = sortByNearestLatLongPoints($geoData, $quor_PickupLat, $quor_PickupLng, false);

                $response = array();


                if ($poller->HasLivePoll($v_id) == true) {
                    $response['success'] = false;
                    $response['msg'] = 'The driver has a live poll, please try after two minutes.';
                    $response['Data'] = 0;
                    return $response;
                }
                $slotdeliveryLocations = array_reverse($slotNearestdeliveryLocations);

                $packingOrderIds = array_column($slotdeliveryLocations, 'quor_id');


                if ($scheduleorder->IsQugeoAPIAlive($v_id) == false) {
                    $response['success'] = false;
                    $response['msg'] = 'The Vehicle isnt active anymore, please reload.';
                    $response['Data'] = 0;
                    return $response;
                }
                $ddbOrderIds = array();
                for ($j = 0; $j < $totalOrders; $j++) {
                    $currentOrderCount = $j + 1;
                    $slotOrderDetails = $db->getFromDB("SELECT quor_id,quor_PickupPincode,quor_PickupLat,quor_PickupLng,quor_PickupLocation,quor_DeliveryPincode,quor_DeliveryLat,quor_DeliveryLng,quor_DeliveryLocation,"
                        . "if(quor_Status=22,'PICKUP', if(quor_Status=31,'DELIVERY','')) as drivetype,quor_Pickupbr_id,quor_Deliverybr_id FROM  qugeo_order WHERE quor_id = ?", array('i', $slotdeliveryLocations[$j]['quor_id']), true);

                    $bk_id = intval($slotOrderDetails['quor_id']);
                    if ($slotOrderDetails['drivetype'] == 'PICKUP') {
                        $br_id = intval($slotOrderDetails['quor_Pickupbr_id']);
                        $handling_br_id = intval($slotOrderDetails['quor_Pickupbr_id']);
                    } else {
                        $br_id = intval($slotOrderDetails['quor_Deliverybr_id']);
                        $handling_br_id = intval($slotOrderDetails['quor_Deliverybr_id']);
                    }


                    if ($scheduleorder->IsQugeoAPIAlive($v_id) == false) {
                        echo '{"success":false,"msg":"The Vehicle isnt active anymore, please reload"}';
                        return;
                    }
                    //if ($slotOrderDetails['drivetype'] == 'PICKUP') {

                    //            if ($type == 'PICKUP') {
                    $orderid = $scheduleorder->scheduleABookingSchJobs($bk_id, $orderdetails, true, $v_id, true, $packingOrderIds, $totalOrders, $currentOrderCount, 'Sch');
                    //            } else {
                    //                $orderid = $scheduleorder->scheduleADeliverySchJobs($bk_id, $orderdetails, true, $v_id, true, false, $handling_br_id, $packingOrderIds, $totalOrders, $currentOrderCount, 'Sch');
                    //            }
                    $schdata['quor_id'] = $bk_id;
                    $schdata['quorddb_id'] = $orderid;
                    $schdata['sch_uuid'] = $uniqueId;
                    //$status = $db->perform('quor_scheduled_deliveries', $schdata);
                    $db->begintransaction();
                    $db->query('INSERT INTO  quor_scheduled_deliveries(quor_id,quorddb_id,sch_uuid) VALUES(?,?,?) ', array('iss', $schdata['quor_id'], $schdata['quorddb_id'], $schdata['sch_uuid']));
                    $db->committransaction();
                    $ddbOrderIds = array_push($ddbOrderIds, $orderid);
                }
            } else {

                file_put_contents('php://stderr', "....................................hyper delivery...................................................... \n");
                //$quorIds = array();
                //array_push($quorIds, $request['qugeobk_NO']);
                $quorIds = $request['quorIds'];
                file_put_contents('php://stderr', print_r($quorIds, TRUE));
                file_put_contents('php://stderr', print_r($type, TRUE));
                $bk_id = intval($quorIds[0]);

                if ($scheduleorder->IsQugeoAPIAlive($v_id) == false) {
                    $response['success'] = false;
                    $response['msg'] = 'The Vehicle isnt active anymore, please reload.';
                    $response['Data'] = 0;
                    return $response;
                }
                if ($poller->HasLivePoll($v_id) == true) {
                    $response['success'] = false;
                    $response['msg'] = 'The driver has a live poll, please try after two minutes.';
                    $response['Data'] = 0;
                    return $response;
                }
                if ($type == 'PICKUP') {
                    $orderid = $scheduleorder->scheduleABookingSchJobs($bk_id, $orderdetails, true, $v_id, true, $quorIds, 1, 1, 'Nor');
                } else {
                    $orderid = $scheduleorder->scheduleADeliverySchJobs($bk_id, $orderdetails, true, $v_id, true, false, $handling_br_id, $quorIds, 1, 1, 'Nor');
                }
            }



            file_put_contents('php://stderr', "....................................orderid...................................................... \n");
            file_put_contents('php://stderr', print_r($orderid, TRUE));
            if (isset($orderid)) {
                $response['success'] = true;
                $response['msg'] = 'Queued for scheduling';
                $response['Data'] = $orderid;
            } else {
                $response['success'] = false;
                $response['msg'] = 'The driver has a live poll, please try after two minutes.';
                $response['Data'] = 0;
            }
            return $response;
        }

        public function POST_listLiveVehicles($flag, $request)
        {
            file_put_contents('php://stderr', "....................................POST_listLiveVehicles...................................................... \n");
            file_put_contents('php://stderr', print_r($request, TRUE));
            if (!array_key_exists('storeGroupId', $request)) {
                throw new \Exception('Missing POST parameters ');
            }
            if ($request['storeGroupId'] == '') {
                throw new \Exception('Invalid branch');
            }

            $date = date('Y-m-d');
            $db = new \cgoSqlDB();

            $nodb = new \cgoDynamiteDB();
            $vehicles = array();
            $branches = $db->getMulipleData('SELECT  br_ID,br_Name FROM finascop_branch WHERE br_status = "Active" AND br_storeGroup = ' . $_POST['storeGroupId'] . ' order by br_Name asc ', array(), true);
            if (!empty($branches)) {
                foreach ($branches as $key => $values) {
                    $arrOrder = array();
                    $arrOrder['PartitionKey'] = array('col' => 'ReportingBranch', 'val' => (int) $values['br_ID'], 'oper' => '=');
                    $arrOrder['SortKey'] = array('col' => 'createddate', 'val' => (int) str_replace('-', '', $date), 'oper' => '=');
                    $arrOrder['IndexName'] = 'ReportingBranch-createddate-index';
                    $arrOrder['queryAttributes'] = array('apikey', 'v_id', 'v_no', 'Latitude', 'Longitude', 'LocationUpdateddatetime', 'DriverName', 'v_typename', 'v_capacity', 'CurrentLoadedWeight', 'v_MapIcon');
                    $arrOrder['Condition'] = array();
                    array_push($arrOrder['Condition'], array('col' => 'Is_Live', 'val' => 1, 'oper' => '='));

                    $rsno = $nodb->query('QugeoLiveVehicles', $arrOrder, 'query');
                    if (isset($rsno) && count($rsno) > 0) {
                        foreach ($rsno as $vehicleapi) {
                            $apikey = $vehicleapi['apikey'];
                            array_push($vehicles, array('v_ID' => $vehicleapi['apikey'], 'v_No' => $vehicleapi['v_no'], 'Latitude' => $vehicleapi['Latitude'], 'Longitude' => $vehicleapi['Longitude'], 'LastLocationDtTm' => $vehicleapi['LocationUpdateddatetime'], 'DriverName' => $vehicleapi['DriverName'], 'Vehicletypename' => $vehicleapi['v_typename'], 'MaxLoad' => $vehicleapi['v_capacity'], 'CurrentLoad' => $vehicleapi['CurrentLoadedWeight'], 'v_MapIcon' => $vehicleapi['v_MapIcon']));
                            //array_push($vehicles, array('vehno' => $vehicleapi['v_no'], 'DriverId' => $vehicleapi['DriverId'], 'drivername' => $vehicleapi['DriverName'], 'mobno' => $vehicleapi['mobno'], 'logintime' => $vehicleapi['createddatetime'], 'vtype' => $vehicleapi['v_typename'], 'assgwt' => $vehicleapi['AssignedLoadedWeight'], 'assgvol' => $vehicleapi['AssignedLoadedVolume'], 'currwt' => $vehicleapi['CurrentLoadedWeight'], 'currvol' => $vehicleapi['CurrentLoadedVolume'], 'totjobs' => $vehicleapi['TotalJobs'], 'jobscompleted' => '0', 'kmcovered' => '0', 'vehid' => $apikey));
                        }
                    }
                }
            }

            if (isset($vehicles) && count($vehicles) > 0) {
                $response['success'] = true;
                $response['msg'] = 'Vehicle details';
                $response['Data'] = $vehicles;
            } else {
                $response['success'] = false;
                $response['msg'] = 'No vehicles found';
                $response['Data'] = array();
            }

            return $response;
        }

        public function POST_saveDeliverCODJobs($flag, $request)
        {
            file_put_contents('php://stderr', "....................................POST_saveDeliverCODJobs...................................................... \n");
            file_put_contents('php://stderr', print_r($request, TRUE));
            if (!array_key_exists('storeGroupId', $request)) {
                throw new \Exception('Missing POST parameters ');
            }
            if ($request['storeGroupId'] == '') {
                throw new \Exception('Invalid branch');
            }

            $date = date('Y-m-d');
            $db = new \cgoSqlDB();

            $nodb = new \cgoDynamiteDB();

            $string = str_replace(array('[', ']'), '', $request['quorIds'][0]);
            $quorIdList = explode(',', $string);

            $cashCollectedDate = $request['collectionDate'];


            if ($quorIdList[0] > 0) {

                foreach ($quorIdList as $quorIds) {
                    $quor_id = $quorIds;
                    $quorType['quor_Status'] = 15;
                    $quor_DeliveryConfTime = date('Y-m-d H:i:s', strtotime($cashCollectedDate));
                    $quorType['quor_UpdateOn'] = date('Y-m-d H:i:s');
                    $db->query("UPDATE  qugeo_order set quor_Status= " . $quorType['quor_Status'] . ",quor_DeliveryConfTime = '" . $quor_DeliveryConfTime . "',quor_UpdateOn = '" . $quorType['quor_UpdateOn'] . "' where quor_id = " . $quor_id);


                    $qrystring = $db->getItemFromDB("SELECT quor_StatusUpdateQry FROM qugeo_order WHERE quor_id = " . $quor_id, true);
                    $updateQueries = getQugeoParentStatusUpdated($qrystring, $quorType['quor_Status']);
                    $updateurl = str_replace("###2", "", $updateQueries);
                    $updateQuerys = explode(";", $updateurl);
                    $qugeoDetails = array();
                    foreach ($updateQuerys as $updateQuery) {
                        $updateQuery = trim($updateQuery);
                        if ($updateQuery != '') {
                            $db->query($updateQuery);
                        }
                    }
                    $quor_TransferOrder_id = $db->getItemFromDb("select quor_TransferOrder_id from qugeo_order where quor_id = " . $quor_id, true);
                    $quor_AmountCollectible = $db->getItemFromDb("select quor_AmountCollectible from qugeo_order where quor_id = " . $quor_id, true);
                    /* if ($quor_AmountCollectible > 0) {
                        \PayOnDelivery::PODVoucher($quor_TransferOrder_id);
                    }*/
                    $parentOrder = $db->getFromDB("SELECT fsto_ordertype,fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id} ", true);
                    if ($parentOrder['fsto_ordertype'] == 1) {
                        $custOrderId = $parentOrder['fstr_id'];
                        $delQry = "CALL UpdateDeliveryStatus($quor_id,$custOrderId,'" . $quor_DeliveryConfTime . "')";
                        $status = $db->query($delQry);
                    }
                    \DeliveryConfirmation::DeliveryConfirmationVoucher($quor_TransferOrder_id);
                    \DeliveryConfirmation::DeliveryEmail($quor_TransferOrder_id);
                    $quor_Type = $db->getItemFromDB("SELECT quor_Type FROM qugeo_order WHERE quor_id = " . $quor_id, true);
                    //if ($quor_Type == 1 && $quor_AmountCollectible > 0) {
                    //  \PayOnDelivery::PODCashCollectionVoucher($quor_TransferOrder_id);
                    //  \PayOnDelivery::PODCashSettlementVoucher($quor_TransferOrder_id);
                    //}
                }
            }
            if ($quorIdList[0] > 0) {

                $response['success'] = true;
                $response['msg'] = 'Delivered';
                $response['Data'] = array();
            } else {
                $response['success'] = false;
                $response['msg'] = 'Error While Converting';
                $response['Data'] = array();
            }
            return $response;
        }
    }
}
