<?php

namespace Models; {

    class Carting extends ModelAbstract
    {

        public function GET_logkeys($flag, $request)
        {

            $logs = array();
            $vehicles['success'] = true;
            $vehicles['msg'] = 'Bucket and Credentials for logging';
            $vehicles['Data']['logkeys'] = array('S3BUCKET' => QUGEO_LOGS_UPLOAD_BUCKET, 'UploadPrefix' => date("Y/m/d/") . $request["apikey"] . "_" . date("His") . "_", 'ACCESSKEY' => QUGEO_S3_UPLOAD_ACCESS, 'SECRETKEY' => QUGEO_S3_UPLOAD_SECRET);
            return $vehicles;
        }

        public function GET_failedstatus($flag, $request)
        {
            $db = new \cgoSqlDB();
            if (!array_key_exists('ispickup', $request) || !isset($request)) {
                throw new \Exception('Missing GET parameters ');
            }
            if ($request['ispickup'] == 'true') {
                $failedstatus = $db->getMulipleData('select dls_ID as id,dls_DelStatus as name  from  qugeo_deliverystatus where dls_ID in (37,36,35)  order by dls_DelStatus asc ', array(), true);
            } else {
                $failedstatus = $db->getMulipleData('select dls_ID as id,dls_DelStatus as name  from  qugeo_deliverystatus where dls_ID in (10,11,12,13,14)  order by dls_DelStatus asc ', array(), true);
            }
            $failedstatuses = array();
            if ($failedstatus !== false) {
                $failedstatuses['success'] = true;
                $failedstatuses['msg'] = 'List of Failed status types';
                $failedstatuses['Data']['failedstatus'] = $failedstatus;
            } else {
                $failedstatuses['msg'] = 'Did not Failed status types';
                $failedstatuses['Data']['failedstatus'] = array();
            }
            return $failedstatuses;
        }

        private function sendOTP($mobile, $otp, $name, $ispickup, $bkno)
        {
            //$sms = new \SoftSMS();
            $templatedata['order_order_id'] = $bkno;
            if ($ispickup == 1) {
                $str = "Arriving for pickup of " . $bkno . ". Dear " . $name . ", an agent will arrive at your location today to pickup your consignment. Please provide the OTP " . $otp . " to the collection boy on completion of pickup";
                \sms::fetchContentSendSms($templatedata, $mobile, 12);
            } else {
                //1607100000000130928
                $str = "Out for Delivery of " . $bkno . ". Dear " . $name . ", your order is arriving, please provide the OTP " . $otp . " to the delivery boy on completion of delivery";
                $db = new \sqlDb(DSN);
                //\sms::send($mobile, $str, $db, "");

                $templatedata['otp'] = $otp;
                \sms::fetchContentSendSms($templatedata, $mobile, 7);
            }
            //$smsresponse = $sms->sendSMS( $mobile,$str);
            //textLocalsms(TextLocalSMS_CREDENTIALS,array($mobile),TextLocalSMS_SENDER,$str);
            //$email = new \cgoAWSSES();
            //$email->send_mail($mobile . '@yopmail.com',$str);
        }

        public function GET_vehicletypelist($flag, $request)
        {
            $db = new \cgoSqlDB();
            $vehiclelist = $db->getMulipleData('select distinct vhty_id as id,vhty_name as name  from  qugeo_vehicletype  where vhty_Active = 1 order by vhty_name asc ', array(), true);
            $vehicles = array();
            if ($vehiclelist !== false) {
                $vehicles['success'] = true;
                $vehicles['msg'] = 'List of vehicles types';
                $vehicles['Data']['vehicletypes'] = $vehiclelist;
            } else {
                $vehicles['msg'] = 'Did not find vehicle type list';
                $vehicles['Data']['vehicletypes'] = array();
            }
            return $vehicles;
        }

        public function GET_lastvehicleused($flag, $request)
        {
            if (!array_key_exists('prevlogoutinfo', $request) || !isset($request)) {
                throw new \Exception('Missing GET parameters ');
            }
            if ($request['prevlogoutinfo'] == '') {
                $arrAPI = array();
                $arrAPI['PartitionKey'] = array('col' => 'usertype', 'val' => $_SESSION["usertype"], 'oper' => '=');
                $arrAPI['SortKey'] = array('col' => 'id', 'val' => $_SESSION["loginid"], 'oper' => '=');
                $arrAPI['IndexName'] = 'usertype-id-index';
                $arrAPI['queryAttributes'] = array('apikey');
                $arrAPI['Condition'] = array();
                array_push($arrAPI['Condition'], array('col' => 'HasLoggedOut', 'val' => '0', 'oper' => '='));
                //array_push($arrAPI['Condition'],array('col'=>'HasOrders','val'=>'1','oper'=>'=' ));	
                array_push($arrAPI['Condition'], array('col' => 'apikey', 'val' => $request["apikey"], 'oper' => "<>"));
                $nodb = new \cgoDynamiteDB();
                $rsno = $nodb->query('APIHistory', $arrAPI, 'query');

                if (isset($rsno) && count($rsno) > 0) {
                    $vehicles = array();
                    $vehicles['msg'] = 'Enter the previous session details';
                    $vehicles['Data']['vehiclesused'] = array();
                    $vehicles['Data']['cleanlogout'] = false;
                    return $vehicles;
                }
            } else {
                $arrAPI = array();
                $arrAPI['PartitionKey'] = array('col' => 'usertype', 'val' => $_SESSION["usertype"], 'oper' => '=');
                $arrAPI['SortKey'] = array('col' => 'id', 'val' => $_SESSION["loginid"], 'oper' => '=');
                $arrAPI['IndexName'] = 'usertype-id-index';
                $arrAPI['queryAttributes'] = array('apikey');
                $arrAPI['Condition'] = array();
                array_push($arrAPI['Condition'], array('col' => 'HasLoggedOut', 'val' => 0, 'oper' => '='));
                array_push($arrAPI['Condition'], array('col' => 'apikey', 'val' => $request["apikey"], 'oper' => '<>'));
                $nodb = new \cgoDynamiteDB();
                $rsno = $nodb->query('APIHistory', $arrAPI, 'query');
                if (isset($rsno) && count($rsno) > 0) {
                    foreach ($rsno as $value) {
                        $arrUpdate = array();
                        $arrUpdate['PartitionKey'] = array('col' => 'apikey', 'val' => $value['apikey'], 'oper' => '=');
                        $arrUpdate['Data'] = array();
                        array_push($arrUpdate['Data'], array('col' => 'HasLoggedOut', 'val' => 1));
                        $nors = $nodb->perform('APIHistory', 'update', $arrUpdate, $response);

                        //Delete from QugeoLiveVehicles
                        $arrUpdate = array();
                        $arrUpdate['PartitionKey'] = array('col' => 'apikey', 'val' => $value['apikey']);
                        $arrUpdate['Data'] = array();
                        array_push($arrUpdate['Data'], array('col' => 'Is_Live', 'val' => 0));
                        array_push($arrUpdate['Data'], array('col' => 'LoggedOutAt', 'val' => (string) date("YmdHis")));
                        array_push($arrUpdate['Data'], array('col' => 'IsCleanLogout', 'val' => 2));
                        $nors = $nodb->perform('QugeoLiveVehicles', 'update', $arrUpdate, $response);
                    }
                }
            }
            $db = new \cgoSqlDB();
            $lastvehicles = $db->getMulipleData('select  v_ID as id,v_no as regno, qugeo_vehicletype.vhty_id as v_type,vhty_name  from  qugeo_drivervehicle inner join qugeo_vehicletype on qugeo_vehicletype.vhty_id = qugeo_drivervehicle.vhty_id where d_ID =? and v_active =1 GROUP BY qugeo_vehicletype.vhty_id ORDER BY dv_id DESC ', array('i', $_SESSION["loginid"]), true);
            $vehicles = array();
            if ($lastvehicles !== false) {
                $vehicles['success'] = true;
                $vehicles['msg'] = 'Please validate your details';
                $vehicles['Data']['vehiclesused'] = $lastvehicles;
                $vehicles['Data']['cleanlogout'] = true;
            } else {
                $vehicles['msg'] = 'Did not find any history of vehicles used';
                $vehicles['Data']['vehiclesused'] = array();
                $vehicles['Data']['cleanlogout'] = true;
            }
            return $vehicles;
        }

        public function GET_ownvehiclelist($flag, $request)
        {
            if (!array_key_exists('allotedvehicle', $request) || !isset($request)) {
                throw new \Exception('Missing GET parameters ');
            }
            $db = new \cgoSqlDB();
            $availablevehicles = $db->getMulipleData('select v_ID as id,v_no as regno,v_Type as v_type,vhty_name  FROM  qugeo_vehicle  INNER JOIN qugeo_vehicletype ON qugeo_vehicletype.vhty_id = qugeo_vehicle.v_Type where v_No like ? and v_active =1 order by 2 ', array('s', '%' . $request["allotedvehicle"] . '%'), true);
            $vehicles = array();
            if ($availablevehicles !== false) {
                $vehicles['success'] = true;
                $vehicles['msg'] = 'List of matching vehicles found';
                $vehicles['Data']['availablevehicles'] = $availablevehicles;
            } else {
                $vehicles['msg'] = 'Did not any matching vehicle number';
                $vehicles['Data']['availablevehicles'] = array();
            }
            return $vehicles;
        }

        public function POST_otprequest($flag, $request)
        {
            if (!array_key_exists('orderid', $request) || !array_key_exists('mobno', $request) || !array_key_exists('geocoords', $request) || !isset($request)) {
                throw new \Exception('Missing get parameters ');
            }
            $util = new Utils();
            $extrainfo = array("event" => "order otp re-request", 'order' => $request['orderid']);
            $util->LogGeoCordinates($request['geocoords'], $_SESSION["usertype"], $_SESSION["loginid"], $request["apikey"], $extrainfo);
            $nodb = new \cgoDynamiteDB();
            $arrOrder['PartitionKey'] = array('col' => 'orderid', 'val' => $request['orderid'], 'oper' => '=');
            $arrOrder['getAttributes'] = array('mobile', 'bkno', 'name', 'IsPickup', 'OTP');
            $nors = $nodb->query('QugeoOrderDetails', $arrOrder, 'getItem');
            if ($nors != false) {
                $this->sendOTP($request['mobno'], $nors['OTP'], $nors['name'], $nors['IsPickup'], $nors['bkno']);
                $arrSession['success'] = true;
                $arrSession['msg'] = 'OTP send to ' . $request['mobno'];
            } else {
                $arrSession['msg'] = 'OTP request failed';
            }

            $arrSession['Data']['otprequest'] = array();
            return $arrSession;
        }

        public function POST_vehicleselected($flag, $request)
        {
            if (!array_key_exists('ishired', $request) || !array_key_exists('vehicleid', $request) || !array_key_exists('vehicleregno', $request) || !array_key_exists('vehicletype', $request) || !array_key_exists('geocoords', $request) || !isset($request)) {
                throw new \Exception('Missing POST parameters ');
            }
            if (intval($request['vehicletype']) == 0) {
                throw new \Exception('Invalid vehicle type');
            }
            if (trim($request['vehicleregno']) == "") {
                throw new \Exception('Invalid Vehicle Number');
            }
            $util = new Utils();
            $extrainfo = array("event" => "vehicleselected");
            $util->LogGeoCordinates($request['geocoords'], $_SESSION["usertype"], $_SESSION["loginid"], $request["apikey"], $extrainfo);

            //Update APISession table with vehicle used
            $arrSession = array();
            $arrSession['Data'] = array();
            $arrSession['PartitionKey'] = array('col' => 'usertype', 'val' => (int) $_SESSION["usertype"]);
            $arrSession['SortKey'] = array('col' => 'id', 'val' => (string) $_SESSION["loginid"]);
            array_push($arrSession['Data'], array('col' => 'extrainfo', 'val' => array("v_id" => $request["vehicleid"], "v_no" => $request["vehicleregno"])));
            $nodb = new \cgoDynamiteDB();
            $nosession = $nodb->perform('APISession', 'update', $arrSession, $response);
            if (!$nosession) {
                $arrSession = array();
                $arrSession['msg'] = 'Unable to update API Session';
                $arrSession['Data']['dashboarddetails'] = null;
                return $arrSession;
            }

            //Update APISession History table with vehicle used
            $arrSession = array();
            $arrSession['Data'] = array();
            $arrSession['PartitionKey'] = array('col' => 'apikey', 'val' => $request["apikey"]);
            array_push($arrSession['Data'], array('col' => 'extrainfo', 'val' => array("v_id" => $request["vehicleid"], "v_no" => $request["vehicleregno"])));
            $nosession = $nodb->perform('APIHistory', 'update', $arrSession, $response);
            if (!$nosession) {
                $arrSession = array();
                $arrSession['msg'] = 'Unable to update API Session Archive';
                $arrSession['Data']['dashboarddetails'] = null;
                return $arrSession;
            }

            //Insert into Branch Live Vehicles
            $arrSession = array();
            $arrSession['Data'] = array();
            $valdate = date("Ymd");
            $valdatetime = date("YmdHis");
            $request["geocoords"] = json_decode($request["geocoords"]);
            $db = new \cgoSqlDB();
            $driverdetails = $db->getFromDB('select d_licenceexpairy as license,d_awssnsarn as awssnsarn,d_HomeLati,d_HomeLong,d_Rating,d_Name,br_id,d_Ph1,d_DeliveryRange,gcmregstid,d_Ph1,d_isallowManualSchedule,d_isallowAutoSchedule,createdBy,sourceId from  qugeo_driver  where  d_ID = ? ', array('i', $_SESSION["loginid"]), true);
            $vehicledetails = $db->getFromDB('select vhty_MaxCapacity as capacity, 0 as Rate,vhty_name as name,vhty_Icon from  qugeo_vehicletype  where  vhty_id = ? ', array('i', $request['vehicletype']), true);
            array_push($arrSession['Data'], array('col' => 'apikey', 'val' => (string) $request["apikey"]));
            array_push($arrSession['Data'], array('col' => 'createddatetime', 'val' => (int) $valdatetime));
            array_push($arrSession['Data'], array('col' => 'createddate', 'val' => (int) $valdate));
            array_push($arrSession['Data'], array('col' => 'LocationUpdateddatetime', 'val' => (int) $valdatetime));
            array_push($arrSession['Data'], array('col' => 'Latitude', 'val' => (float) $request["geocoords"]->details[0]->latitude));
            array_push($arrSession['Data'], array('col' => 'Longitude', 'val' => (float) $request["geocoords"]->details[0]->longitude));
            array_push($arrSession['Data'], array('col' => 'v_id', 'val' => (string) $request["vehicleid"]));
            array_push($arrSession['Data'], array('col' => 'v_no', 'val' => (string) $request["vehicleregno"]));
            array_push($arrSession['Data'], array('col' => 'Is_Live', 'val' => 1));
            array_push($arrSession['Data'], array('col' => 'AWS_SNS_ARN', 'val' => (string) $driverdetails['awssnsarn']));
            array_push($arrSession['Data'], array('col' => 'FCM_ID', 'val' => (string) $driverdetails['gcmregstid']));
            array_push($arrSession['Data'], array('col' => 'DriverId', 'val' => (int) $_SESSION["loginid"]));
            array_push($arrSession['Data'], array('col' => 'DriverBranchId', 'val' => (int) $driverdetails['br_id']));
            array_push($arrSession['Data'], array('col' => 'DriverName', 'val' => (string) $driverdetails['d_Name']));
            array_push($arrSession['Data'], array('col' => 'DriverPhone', 'val' => (string) $driverdetails['d_Ph1']));
            array_push($arrSession['Data'], array('col' => 'v_type', 'val' => (int) $request['vehicletype']));
            array_push($arrSession['Data'], array('col' => 'v_capacity', 'val' => (float) $vehicledetails['capacity']));
            array_push($arrSession['Data'], array('col' => 'v_typename', 'val' => (string) $vehicledetails['name']));
            array_push($arrSession['Data'], array('col' => 'v_MapIcon', 'val' => (string) $vehicledetails['vhty_Icon']));
            array_push($arrSession['Data'], array('col' => 'CurrentLoadedWeight', 'val' => 0));
            array_push($arrSession['Data'], array('col' => 'CurrentLoadedVolume', 'val' => 0));
            array_push($arrSession['Data'], array('col' => 'AssignedLoadedWeight', 'val' => 0));
            array_push($arrSession['Data'], array('col' => 'AssignedLoadedVolume', 'val' => 0));
            array_push($arrSession['Data'], array('col' => 'RatePerKm', 'val' => (float) $vehicledetails['Rate']));
            array_push($arrSession['Data'], array('col' => 'Home_Latitude', 'val' => (float) $driverdetails['d_HomeLati']));
            array_push($arrSession['Data'], array('col' => 'Home_Longitude', 'val' => (float) $driverdetails['d_HomeLong']));
            array_push($arrSession['Data'], array('col' => 'Rating', 'val' => (string) $driverdetails['d_Rating']));
            array_push($arrSession['Data'], array('col' => 'mobno', 'val' => (string) $driverdetails['d_Ph1']));
            array_push($arrSession['Data'], array('col' => 'ReportingBranch', 'val' => (int) $driverdetails['br_id']));
            array_push($arrSession['Data'], array('col' => 'DeliveryRange', 'val' => (int) $driverdetails['d_DeliveryRange']));
            array_push($arrSession['Data'], array('col' => 'MarkedNextBkId', 'val' => 0));
            array_push($arrSession['Data'], array('col' => 'MarkedNextBrId', 'val' => 0));
            array_push($arrSession['Data'], array('col' => 'IsEngaged', 'val' => 0));
            array_push($arrSession['Data'], array('col' => 'OnJobCompletionLatitude', 'val' => 0));
            array_push($arrSession['Data'], array('col' => 'OnJobCompletionLongitude', 'val' => 0));
            array_push($arrSession['Data'], array('col' => 'isallowManualSchedule', 'val' => (int) $driverdetails['d_isallowManualSchedule']));
            array_push($arrSession['Data'], array('col' => 'isallowAutoSchedule', 'val' => (int) $driverdetails['d_isallowAutoSchedule']));
            array_push($arrSession['Data'], array('col' => 'createdBy', 'val' => (int) $driverdetails['createdBy']));
            array_push($arrSession['Data'], array('col' => 'sourceId', 'val' => (int) $driverdetails['sourceId']));
            file_put_contents('php://stderr', "POST_vehicleselected CALLED " . $driverdetails['d_Name'] . " \n ");
            file_put_contents('php://stderr', print_r($arrSession, TRUE));
            $LiveVehicles = $nodb->perform('QugeoLiveVehicles', 'insert', $arrSession, $response);
            $LiveVehiclesHistory = $nodb->perform('QugeoLiveVehiclesHistory', 'insert', $arrSession, $response);
            $tmparr = $arrSession;
            $arrSession = array();
            if (!$LiveVehicles) {
                $arrSession['msg'] = 'Unable to Queue to live vehicles';
                $arrSession['Data']['dashboarddetails'] = $response;
                print_r($tmparr);
            } else {
                $response = array();
                array_push($response, array('title' => 'Your license is valid till', 'value' => $driverdetails['license']));
                if ($request["vehicleid"] > 0) {
                    $availablevehicles = $db->getFromDB('select dt_insurance as insurance,dt_fitness as fitness  from  qugeo_vehicle  where v_ID = ? ', array('i', $request["vehicleid"]), true);
                    array_push($response, array('title' => 'Vehicle Insurance expires on ', 'value' => $availablevehicles['insurance']));
                    array_push($response, array('title' => 'Vehicle Fitness is valid till ', 'value' => $availablevehicles['fitness']));
                }
                $db->begintransaction();
                $db->query('INSERT INTO  qugeo_drivervehicle(d_ID,v_ID,v_No,lastused,vhty_id) VALUES(?,?,?,now(),?) ', array('iisi', $_SESSION["loginid"], $request["vehicleid"], $request["vehicleregno"], $request['vehicletype']));
                $db->committransaction();

                $arrSession['success'] = true;
                $arrSession['msg'] = 'Dasboard Details';
                $arrSession['Data']['vehicle'] = $response;
            }
            return $arrSession;
        }

        public function POST_polledorder($flag, $request)
        {
            file_put_contents('php://stderr', "POST_polledorder CALLED " . $request["orderid"] . " \n ");
            file_put_contents('php://stderr', "POST_polledorder CALLED " . $flag . " \n ");
            file_put_contents('php://stderr', "POST_polledorder CALLED " . $request . " \n ");
            if (!array_key_exists('orderid', $request) || !array_key_exists('hasaccepted', $request) || !array_key_exists('delivertobranch', $request) || !array_key_exists('msgid', $request) || !array_key_exists('geocoords', $request) || !isset($request)) {
                throw new \Exception('Missing POST parameters ');
            }
            if ($request['orderid'] == '') {
                throw new \Exception('Invalid order id');
            }
            if ($request['msgid'] == '') {
                throw new \Exception('Invalid Message id');
            }
            /* $data = array(
              'istriprerouted' => true,
              'mapdetails' => array(
              'latitude' => 8.5207294999999998,
              'longitude' => 76.942287300000004,
              'zoomlevel' => 10,
              'locationicon' => 'https://maps.google.com/mapfiles/ms/icons/yellow-dot.png' ),
              'nextorderdetails' => array(
              'ispickup' => true,
              'orderid' => '09619a56490274a4f66de203717d82401cb47d02',
              'orderno' => 'ABG125F',
              'Customer' => 'Praseed',
              'address' => '10 D Peach SFS Attikuzhi',
              'contph' => '9123456780',
              'cashtobecollected' => 800,
              'noofboxes' => 10,
              'weight' => '109Kg',
              ));
              $arrSession =array();
              $arrSession['success']=true;
              $arrSession['msg'] = 'Dasboard Details';
              $arrSession['Data']['orderdetails'] = $data;
              return $arrSession; */
            $util = new Utils();
            $extrainfo = array("event" => "pollreponse", "responsedetails" => array("orderid" => $request["orderid"], "hasaccepted" => $request['hasaccepted'], "msgid" => $request['msgid']));
            $util->LogGeoCordinates($request['geocoords'], $_SESSION["usertype"], $_SESSION["loginid"], $request["apikey"], $extrainfo);
            $pollresp = new QugeoOrderPoller();
            $arrSession = array();
            $nextorder = array();


            $nextorder['istriprerouted'] = false;
            $nextorder['mapdetails'] = array();
            $nextorder['nextorderdetails'] = array();
            if ($pollresp->IsPollClosed($request['msgid'], $acceptedapikey) == true) {
                //if($acceptedapikey != $request["apikey"]){
                if ($request['hasaccepted'] == 'true') {
                    $arrSession['success'] = false;
                    $arrSession['msg'] = 'Your poll response has been timed out';
                    $arrSession['Data']['vehicle'] = $nextorder;
                    return $arrSession;
                } else {
                    $arrSession['success'] = false;
                    $arrSession['msg'] = 'Your poll response has been timed out';
                    $arrSession['Data']['vehicle'] = $nextorder;
                    return $arrSession;
                }
                //}
            }
            $pollclosed = $pollresp->PollResponse($request['msgid'], ($request['hasaccepted'] == 'true' ? 1 : 2), ($request['delivertobranch'] == 'true' ? true : false), $acceptedorder);


            $orderhandler = new QugeoOrderHandler();
            $udpatedorder = $orderhandler->UpdateOrderOnPoll(($request['hasaccepted'] == 'true' ? 1 : 2), $request['orderid'], $request['apikey'], ($request['delivertobranch'] == 'true' ? true : false), $orderdetails);
            if ($pollclosed == true && $acceptedorder && $udpatedorder) {
                if ($udpatedorder) {
                    $assigned = $orderhandler->AssignOrderToQugeoDriver($request['orderid'], $request['apikey'], $orderdetails, $nextorder, $isnewroute);
                    //echo "doneeee";
                    if ($isnewroute) {
                        $arrSession['msg'] = 'New route reworked';
                    } else {
                        $arrSession['msg'] = 'No change in route';
                    }
                    $arrSession['success'] = true;
                } else {
                    $arrSession['msg'] = 'Unable to assign order';
                    throw new \Exception('Unable to assign order');
                }
            } else {
                $arrSession['success'] = true;
                $arrSession['msg'] = 'No changes in the route';
            }
            file_put_contents('php://stderr', print_r($nextorder, TRUE));
            $arrSession['Data']['vehicle'] = $nextorder;

            return $arrSession;
        }

        public function POST_polledordersch($flag, $request)
        {
            file_put_contents('php://stderr', "POST_polledordersch CALLEDCALLEDCALLEDCALLED " . $request["orderid"] . " \n ");
            file_put_contents('php://stderr', print_r($request, TRUE));
            file_put_contents('php://stderr', print_r($flag, TRUE));
            if (!array_key_exists('orderid', $request) || !array_key_exists('hasaccepted', $request) || !array_key_exists('delivertobranch', $request) || !array_key_exists('msgid', $request) || !array_key_exists('geocoords', $request) || !isset($request)) {
                throw new \Exception('Missing POST parameters ');
            }
            if ($request['orderid'] == '') {
                throw new \Exception('Invalid order id');
            }
            if ($request['msgid'] == '') {
                throw new \Exception('Invalid Message id');
            }
            $db = new \cgoSqlDB();
            $validOrder = $db->getItemFromDB("select quor_Status from  qugeo_order where quor_QugeoPickupDDBOrderId = '{$request['orderid']}' OR  quor_QugeoDeliveryDDBOrderId = '{$request['orderid']}'", true);
if(!empty($validOrder) && $validOrder > 0){
    $status = [22,23,24,25,27,31];
    if (!in_array($validOrder, $status)) {
        $varra['success'] = false;
        $varra['msg'] = "Order already accepted by another driver. Please select a different order.";
        $varra['Data']['orders'] = "";
        return $varra;
    }
}
            

            $util = new Utils();
            $extrainfo = array("event" => "pollreponse", "responsedetails" => array("orderid" => $request["orderid"], "hasaccepted" => $request['hasaccepted'], "msgid" => $request['msgid']));
            $util->LogGeoCordinates($request['geocoords'], $_SESSION["usertype"], $_SESSION["loginid"], $request["apikey"], $extrainfo);
            $pollresp = new QugeoOrderPoller();
            $arrSession = array();
            $nextorder = array();


            $nextorder['istriprerouted'] = false;
            $nextorder['mapdetails'] = array();
            $nextorder['nextorderdetails'] = array();
            /* if ($pollresp->IsPollClosed($request['msgid'], $acceptedapikey) == true) {
                //if($acceptedapikey != $request["apikey"]){
                if ($request['hasaccepted'] == 'true') {
                    $arrSession['success'] = false;
                    $arrSession['msg'] = 'Your poll response has been timed out';
                    $arrSession['Data']['vehicle'] = $nextorder;
                    return $arrSession;
                } else {
                    $arrSession['success'] = false;
                    $arrSession['msg'] = 'Your poll response has been timed out';
                    $arrSession['Data']['vehicle'] = $nextorder;
                    return $arrSession;
                }
                //}
            }*/
            //$pollclosed = $pollresp->PollResponse($request['msgid'], ($request['hasaccepted'] == 'true' ? 1 : 2), ($request['delivertobranch'] == 'true' ? true : false), $acceptedorder);


            $orderhandler = new QugeoOrderHandler();
            if($request['hasaccepted'] == 'true'){
				file_put_contents('php://stderr', "hasaccepted hasaccepted trueeee");
                $db->query("UPDATE qugeo_firebase_log SET rfir_StatusId=2 where rfir_StatusId=1 AND rfir_token = '".$request['fcm_token']."'");
            }else{
				file_put_contents('php://stderr', "hasaccepted hasaccepted falseeee");
                $db->query("UPDATE qugeo_firebase_log SET rfir_StatusId=3 where rfir_StatusId=1 AND rfir_token = '".$request['fcm_token']."'");
            }
            $udpatedorder = $orderhandler->UpdateOrderOnPoll(($request['hasaccepted'] == 'true' ? 1 : 2), $request['orderid'], $request['apikey'], ($request['delivertobranch'] == 'true' ? true : false), $orderdetails);
            //if ($pollclosed == true && $acceptedorder && $udpatedorder) {
            if ($request['hasaccepted'] == 'true' && $udpatedorder) {
                if ($udpatedorder) {
                    $assigned = $orderhandler->AssignOrderToQugeoDriverSch($request['orderid'], $request['apikey'], $orderdetails, $nextorder, $isnewroute);
                    //echo "doneeee";
                    if ($isnewroute) {
                        $arrSession['msg'] = 'New route reworked';
                    } else {
                        $arrSession['msg'] = 'No change in route';
                    }
                    $arrSession['success'] = true;
                } else {
                    $arrSession['msg'] = 'Unable to assign order';
                    throw new \Exception('Unable to assign order');
                }
            } else {
                $arrSession['success'] = true;
                $arrSession['msg'] = 'No changes in the route';
            }
            file_put_contents('php://stderr', print_r($nextorder, TRUE));
            $arrSession['Data']['vehicle'] = $nextorder;
            file_put_contents('php://stderr', "POST_polledordersch responseresponseresponse \n ");
            file_put_contents('php://stderr', print_r($arrSession, TRUE));
            return $arrSession;
        }

        public function POST_proceedpolledorder($flag, $request)
        {

            file_put_contents('php://stderr', "POST_proceedpolledorder CALLED " . $request["orderid"] . " \n ");
            file_put_contents('php://stderr', "POST_proceedpolledorder CALLED " . $flag . " \n ");
            file_put_contents('php://stderr', "POST_proceedpolledorder CALLED " . $request . " \n ");
            if (!array_key_exists('orderid', $request) || !array_key_exists('msgid', $request) || !isset($request)) {
                throw new \Exception('Missing POST parameters ');
            }
            if ($request['orderid'] == '') {
                throw new \Exception('Invalid order id');
            }
            if ($request['msgid'] == '') {
                throw new \Exception('Invalid Message id');
            }


            $pollresp = new QugeoOrderPoller();
            $arrSession = array();

            $sendOrderIds = json_decode($request["orderid"]);


            $pollclosed = $pollresp->PollResponse($request['msgid'], 1, false, $acceptedorder);
            if ($pollclosed == true && $acceptedorder) {
                $arrSession['success'] = true;
                $arrSession['msg'] = 'Poll closed';
                $arrSession['Data']['orderid'] = array_reverse($sendOrderIds->orderid);
            }
            return $arrSession;
        }

        public function POST_milestone($flag, $request)
        {
            if (!array_key_exists('orderid', $request) || !array_key_exists('milestone', $request) || !array_key_exists('geocoords', $request) || !isset($request)) {
                throw new \Exception('Missing POST parameters ');
            }
            $arrSession = array();
            file_put_contents('php://stderr', print_r($request, TRUE));
            if ($request['milestone'] == 50) {
                $nodb = new \cgoDynamiteDB();
                $arrOrder['PartitionKey'] = array('col' => 'orderid', 'val' => $request['orderid'], 'oper' => '=');
                $arrOrder['getAttributes'] = array('pickupmobile', 'quor_RefNo', 'pickupname', 'deliveryname', 'deliverymobile', 'IsPickup', 'pickupOTP', 'IsMilestoneLock', 'deliveryOTP');
                $nors = $nodb->query('QugeoOrderDetails', $arrOrder, 'getItem');
                file_put_contents('php://stderr', "POST_milestone " . $request["orderid"] . " \n ");
                file_put_contents('php://stderr', print_r($request, TRUE));
                file_put_contents('php://stderr', print_r($nors, TRUE));
                if ($nors != false) {
                    if ($nors['IsMilestoneLock'] == '0') {
                        if ($nors['IsPickup'] != '1') {
                            //Your {#var#} Order No.{#var#} is arriving to you soon. Please provide the OTP {#var#} to our delivery partner on request.
                            //1607100000000004818
                            /*$str = "Your " . PROJECT_NAME . " Order No." . $nors['quor_RefNo'] . " is arriving to you soon. Please provide the OTP " . $nors['deliveryOTP'] . " to our delivery partner  on request.";
                            $db = new \SqlDB(DSN);
                            //\sms::send($nors['deliverymobile'], $str, $db, "");
                            $templatedata['order_order_id'] = $nors['quor_RefNo'];
                            $templatedata['otp'] = $nors['deliveryOTP'];
                            \sms::fetchContentSendSms($templatedata, $nors['deliverymobile'], 7);*/
                        }
                        $arrOrder = array();
                        $arrOrder['PartitionKey'] = array('col' => 'apikey', 'val' => $request['apikey'], 'oper' => '=');
                        $arrOrder['SortKey'] = array('col' => 'orderid', 'val' => $request['orderid'], 'oper' => '=');
                        $arrOrder['Data'] = array();
                        array_push($arrOrder['Data'], array('col' => 'IsMilestoneLock', 'val' => 1));
                        $NewOrder = $nodb->perform('QugeoLiveVehicleOrders', 'update', $arrOrder, $response);
                    }
                    //Your {#var#} Order No.{#var#} is arriving to you soon. Please provide the OTP {#var#} to our delivery partner on request.
                    //1607100000000004818
                    $str = "Your " . PROJECT_NAME . " Order No." . $nors['quor_RefNo'] . " is arriving to you soon. Please provide the OTP " . $nors['deliveryOTP'] . " to our delivery partner  on request.";
                    $db = new \sqlDb(DSN);
                    //\sms::send($nors['deliverymobile'], $str, $db, "");
                    $templatedata['order_order_id'] = $nors['quor_RefNo'];
                    $templatedata['otp'] = $nors['deliveryOTP'];
                    \sms::fetchContentSendSms($templatedata, $nors['deliverymobile'], 7);

                    $arrUpdate = array();
                    $arrUpdate['PartitionKey'] = array('col' => 'orderid', 'val' => $request['orderid']);
                    $arrUpdate['Data'] = array();
                    array_push($arrUpdate['Data'], array('col' => 'IsMilestoneLock', 'val' => 1));
                    array_push($arrUpdate['Data'], array('col' => 'MilestoneCovered', 'val' => (int) $request['milestone']));
                    $uprs = $nodb->perform('QugeoOrderDetails', 'update', $arrUpdate, $response);
                    $arrSession['success'] = true;
                    $arrSession['msg'] = 'Milestone action done';
                } else {
                    $arrSession['msg'] = 'Milestone action error';
                }
            } else {
                $arrSession['success'] = true;
                $arrSession['msg'] = 'Milestone action completed';
            }



            $arrSession['Data']['milestone'] = array();
            return $arrSession;
        }

        public function POST_geolocation($flag, $request)
        {
            if (!array_key_exists('geocoords', $request) || !isset($request)) {
                throw new \Exception('Missing POST parameters ');
            }
            $util = new Utils();
            $extrainfo = array("event" => "locationupdate");
            $util->LogGeoCordinates($request['geocoords'], $_SESSION["usertype"], $_SESSION["loginid"], $request["apikey"], $extrainfo, true);
            $arrSession = array();
            $arrSession['success'] = true;
            $arrSession['msg'] = 'Location Updated';
            $arrSession['Data']['location'] = array();
            return $arrSession;
        }

        public function GET_consignment($flag, $request)
        {
            if (!array_key_exists('orderid', $request) || !array_key_exists('otp', $request) || !array_key_exists('geocoords', $request) || !isset($request)) {
                throw new \Exception('Missing POST parameters ');
            }
            $util = new Utils();
            $extrainfo = array("event" => "GetOrderDetails", 'order' => $request['orderid']);
            $util->LogGeoCordinates($request['geocoords'], $_SESSION["usertype"], $_SESSION["loginid"], $request["apikey"], $extrainfo);
            $nodb = new \cgoDynamiteDB();
            $arrOrder['PartitionKey'] = array('col' => 'orderid', 'val' => $request['orderid'], 'oper' => '=');
            $arrOrder['getAttributes'] = array('OTP', 'GoodsWorth', 'pktcount', 'ContentTypeId', 'PackingTypeId', 'netamt', 'paymode', 'Consignment');
            $nors = $nodb->query('QugeoOrderDetails', $arrOrder, 'getItem');
            $response = array();
            $arrSession = array();
            if ($nors != false) {
                if ($request['otp'] == $nors['OTP']) {
                    $db = new \cgoSqlDB();
                    $contenttype = $db->getItemFromDB("select ct_Name from  contenttype where ct_ID = " . $nors['ContentTypeId'], array(), true);
                    $response = array("goodsworth" => $nors['GoodsWorth'], "totalcount" => $nors['pktcount'], "contenttypeid" => $nors['ContentTypeId'], "contenttype" => $contenttype, "packingtypeid" => $nors['PackingTypeId'], "amount" => $nors['netamt'], "cashat" => ($nors['paymode'] == 'PAID' ? 1 : 0), "details" => $nors['Consignment']);
                    $arrSession['success'] = true;
                    $arrSession['msg'] = 'Consignments';
                } else {
                    $arrSession['msg'] = 'Invalid OTP';
                }
            } else {
                $arrSession['msg'] = 'Get consignment action error';
            }
            $arrSession['Data']['consignment'] = $response;
            return $arrSession;
        }

        public function PUT_consignment($flag, $request)
        {
            if (!array_key_exists('orderid', $request) || !array_key_exists('consignment', $request) || !array_key_exists('geocoords', $request) || !isset($request)) {
                throw new \Exception('Missing POST parameters ');
            }
            $cons = json_decode($request['consignment'], true);
            foreach ($cons['details'] as $key => $value) {
                $cons['details'][$key]['contenttypeid'] = $cons['contenttypeid'];
                $cons['details'][$key]['packingtypeid'] = $cons['packingtypeid'];
            }
            $arrSession = array();
            $util = new Utils();
            $isvalid = $util->IsValidConsignment($cons, $str);
            if (!$isvalid) {
                $arrSession['msg'] = $str;
                $arrSession['Data']['charges'] = array();
                return $arrSession;
            }
            $nodb = new \cgoDynamiteDB();
            $arrOrder = array();
            $arrOrder['PartitionKey'] = array('col' => 'orderid', 'val' => $request['orderid'], 'oper' => '=');
            $arrOrder['getAttributes'] = array('IsPickup', 'HasDirectDeliveryPickUp', 'DstLat', 'DstLong', 'SrcLat', 'SrcLong', 'SrcLocId', 'DstLocId', 'SrcLocName', 'DstLocName', 'netamt', 'ConsignorID', 'ConsigneeID', 'SrcRemoteLati', 'SrcRemoteLong', 'DstRemoteLati', 'DstRemoteLong', 'SrcBrLat', 'SrcBrLong', 'DstBrLat', 'DstBrLong', 'SrcBranch', 'DestBranch', 'TotalDistKM', 'SrcBrId', 'DstBrId', 'Taxable');
            $nors = $nodb->query('QugeoOrderDetails', $arrOrder, 'getItem');

            if (count($nors) > 0) {
                if ($nors['IsPickup'] == 1) {
                    $HasDetails = array();
                    $HasDetails['pickup_addresses'] = array('SrcBrName' => $nors['SrcBranch'], 'SrcBrId' => $nors['SrcBrId'], 'locationid' => $nors['SrcLocId'], 'location' => array('location' => $nors['SrcLocName'], 'lati' => $nors['SrcRemoteLati'], 'long' => $nors['SrcRemoteLong']), 'SrcBrLat' => $nors['SrcBrLat'], 'SrcBrLong' => $nors['SrcBrLong']);
                    if ($nors['SrcLat'] != $nors['SrcRemoteLati'] || $nors['SrcLong'] != $nors['SrcRemoteLong']) {
                        $HasDetails['pickup_addresses']['actualcoords'] = array('latitude' => $nors['SrcLat'], 'longitude' => $nors['SrcLong']);
                    }
                    $HasDetails['delivery_addresses'] = array('DstBrName' => $nors['DestBranch'], 'DstBrId' => $nors['DstBrId'], 'locationid' => $nors['DstLocId'], 'location' => array('location' => $nors['DstLocName'], 'lati' => $nors['DstRemoteLati'], 'long' => $nors['DstRemoteLong']), 'DstBrLat' => $nors['DstBrLat'], 'DstBrLong' => $nors['DstBrLong']);
                    if ($nors['DstLat'] != $nors['DstRemoteLati'] || $nors['DstLong'] != $nors['DstRemoteLong']) {
                        $HasDetails['delivery_addresses']['actualcoords'] = array('latitude' => $nors['DstLat'], 'longitude' => $nors['DstLong']);
                    }
                    $HasDetails['consignment'] = json_decode($request['consignment'], true);
                    $HasDetails['ConsignorId'] = $nors['ConsignorID'];
                    $HasDetails['ConsigneeId'] = $nors['ConsigneeID'];
                    $HasDetails['TotalDistKM'] = $nors['TotalDistKM'];
                    $HasDetails['Taxable'] = $nors['Taxable'];
                    $chrgs = new Charges('getcharges');
                    $params = $chrgs->getcharges($flag, $request, $HasDetails);
                    $arrCharges = array();
                    $arrCharges['chargeweight'] = $params['TotalChargWt'];
                    $arrCharges['packets'] = $params['TotalPkts'];
                    $arrCharges['transcharges'] = $params['TotalFreightAmt'];
                    $arrCharges['delicharges'] = $params['delicharges'];
                    $arrCharges['taxes'] = $params['taxamt'];
                    $arrCharges['total'] = $params['netamt'] - $params['roundoff'];
                    if ($arrCharges['total'] > $nors['netamt']) {
                        $arrCharges['hasnewcharges'] = true;
                        $arrCharges['additionalamt'] = $arrCharges['total'] - $nors['netamt'];
                    } else {
                        $arrCharges['hasnewcharges'] = false;
                        $arrCharges['additionalamt'] = 0;
                    }
                    $arrUpdate = array();
                    $arrUpdate['PartitionKey'] = array('col' => 'orderid', 'val' => $request['orderid']);
                    $arrUpdate['Data'] = array();
                    array_push($arrUpdate['Data'], array('col' => 'NewConsignmentDetails', 'val' => $HasDetails['consignment']));
                    array_push($arrUpdate['Data'], array('col' => 'NewChargeDetails', 'val' => $params));
                    array_push($arrUpdate['Data'], array('col' => 'HasReCalculatedCharges', 'val' => (int) ($arrCharges['hasnewcharges'] == true ? 1 : 0)));
                    array_push($arrUpdate['Data'], array('col' => 'ReCalculatedCharges', 'val' => (float) $arrCharges['additionalamt']));
                    $nors = $nodb->perform('QugeoOrderDetails', 'update', $arrUpdate, $response);
                    if ($nors != false) {
                        $arrSession['success'] = true;
                        $arrSession['msg'] = 'Charges';
                        $arrSession['Data']['charges'] = $arrCharges;
                    }
                    return $arrSession;
                } else {
                    throw new \Exception('Delivery not implemented');
                }
            } else {
                print_r($arrOrder);
                throw new \Exception('Missing required charges data');
            }
        }

        public function POST_preconcludeorder($flag, $request)
        {
            $orderid = $request["orderid"];
            $arrOrder['PartitionKey'] = array('col' => 'orderid', 'val' => $orderid, 'oper' => '=');
            $arrOrder['getAttributes'] = array('quor_id', 'IsPickup');
            $arrUpdate = array();
            $arrUpdate['PartitionKey'] = array('col' => 'orderid', 'val' => $orderid);
            $arrUpdate['Data'] = array();
            array_push($arrUpdate['Data'], array('col' => 'IsPickup', 'val' => (int) 0));
            if (!empty($request['signature_path'])) {
                array_push($arrUpdate['Data'], array('col' => 'Signature', 'val' => $request['signature_path']));
            }
            if (!empty($request['photo_path'])) {
                array_push($arrUpdate['Data'], array('col' => 'Photo', 'val' => $request['photo_path']));
            }
            $nodb = new \cgoDynamiteDB();
            $uprs = $nodb->perform('QugeoOrderDetails', 'update', $arrUpdate, $response);

            $getarrOrder = array();
            $getarrOrder['PartitionKey'] = array('col' => 'orderid', 'val' => $orderid, 'oper' => '=');
            $getarrOrder['getAttributes'] = array('IsPickup', 'HasReCalculatedCharges', 'ReCalculatedCharges', 'quor_id', 'HasDirectDeliveryPickUp', 'totwt', 'totvol', 'AcceptedAsDirectDelivery', 'HandlingBranch', 'quor_RefNo', 'deliverymobile');
            $nors = $nodb->query('QugeoOrderDetails', $getarrOrder, 'getItem');


            $db = new \cgoSqlDB();
            //save images of signature / image
            if (!empty($request['signature_path'])) {
                $db->query("UPDATE  qugeo_order set quor_signature='" . $request['signature_path'] . "' where quor_id = " . $nors['quor_id']);
            }

            if (!empty($request['photo_path'])) {
                $db->query("UPDATE  qugeo_order set quor_image = '" . $request['photo_path'] . "' where quor_id = " . $nors['quor_id']);
            }
            if ($uprs == false) {
                throw new \Exception('No response of update on QugeoOrderDetails, throws error');
            }



            $arrSession['success'] = true;
            $arrSession['msg'] = 'Confirm Delivery';
            $arrSession['Data']['confirm'] = array();
            return $arrSession;
        }

        public function POST_concludeorder($flag, $request)
        {
            if (!array_key_exists('orderid', $request) || !array_key_exists('failed', $request) || !array_key_exists('failedreasonid', $request) || !array_key_exists('return_items', $request) || !array_key_exists('confirmationdetails', $request) || !array_key_exists('geocoords', $request) || !isset($request)) {
                throw new \Exception('Missing POST parameters ');
            }
            file_put_contents('php://stderr', "CONCLUDE CALLED " . $request["orderid"] . " \n ");
            file_put_contents('php://stderr', print_r($request, TRUE));
            $nodb = new \cgoDynamiteDB();
            $db = new \cgoSqlDB();
            $arrOrder = array();
            $util = new Utils();
            $extrainfo = array("event" => "conclude", "requestdetails" => array("orderid" => $request["orderid"]));
            $util->LogGeoCordinates($request['geocoords'], $_SESSION["usertype"], $_SESSION["loginid"], $request["apikey"], $extrainfo);
            $arrOrder['PartitionKey'] = array('col' => 'orderid', 'val' => $request['orderid'], 'oper' => '=');
            $arrOrder['getAttributes'] = array('IsPickup', 'HasReCalculatedCharges', 'ReCalculatedCharges', 'quor_id', 'HasDirectDeliveryPickUp', 'totwt', 'totvol', 'AcceptedAsDirectDelivery', 'HandlingBranch', 'quor_RefNo', 'deliverymobile');
            $nors = $nodb->query('QugeoOrderDetails', $arrOrder, 'getItem');
            file_put_contents('php://stderr', "CONCLUDE CALLED nors " . $request["orderid"] . " \n ");
            file_put_contents('php://stderr', print_r($nors, TRUE));
            $totwt = floatval($nors['totwt']);
            $totvol = floatval($nors['totvol']);

            $arrUpdate = array();
            $arrUpdate['PartitionKey'] = array('col' => 'orderid', 'val' => $request['orderid'], 'oper' => '=');
            $arrUpdate['Data'] = array();
            $valdatetime = date("YmdHis");
            array_push($arrUpdate['Data'], array('col' => 'IsClosed', 'val' => 1));
            array_push($arrUpdate['Data'], array('col' => 'ClosedAt', 'val' => $valdatetime));
            array_push($arrUpdate['Data'], array('col' => 'updateddatetime', 'val' => $valdatetime));
            $neworder = array();
            $WasAdirectDeliveryPickup = false;
            $AssignedLoadedWeight = 0;
            $AssignedLoadedVolume = 0;
            $AssOper = '';
            $CurrentLoadedVolume = 0;
            $CurrentLoadedWeight = 0;
            $CurOper = '';
            if ($nors != false) {
                $JobNo = $nors['quor_RefNo'];
                $deliverymobile = $nors['deliverymobile'];
                if ($nors['IsPickup'] == 1) {
                    if ($request['failed'] == 'true') {
                        file_put_contents('php://stderr', "-----------------IsPickup--------failed-------------------------------");
                        array_push($arrUpdate['Data'], array('col' => 'OrderStatus', 'val' => (int) $request['failedreasonid']));
                        array_push($arrUpdate['Data'], array('col' => 'FailedReasonID', 'val' => (int) $request['failedreasonid']));
                        $db->query("UPDATE  qugeo_order set quor_Status=" . $request['failedreasonid'] . " where quor_id = " . $nors['quor_id']);
                        $delreason = $db->getItemFromDb("select dls_DelStatus from qugeo_deliverystatus where dls_ID = " . $request['failedreasonid'], true);
                        $updateurl = $db->getItemFromDb("select quor_StatusUpdateQry from qugeo_order where quor_id = " . $nors['quor_id'], true);
                        //$updateurl = str_replace("###1",QUGEO_TO_CUSTOMER_ORDER_STATUS_DELIVERY_FAILED,$updateurl);
                        $updateurl = getQugeoParentStatusUpdated($updateurl, $request['failedreasonid']);
                        $updateurl = str_replace("###6", "1", $updateurl);
                        $updateurl = str_replace("###2", $delreason, $updateurl);
                        $execQry = explode(";", $updateurl);
                        if (trim($execQry[0]) != "")
                            $db->query(trim($execQry[0]));
                        if (trim($execQry[1]) != "")
                            $db->query(trim($execQry[1]));
                        //FOR TRACKING - CLEAR
                        $updateurl = $db->getItemFromDb("select quor_TrackingUpdateQry from qugeo_order where quor_id = " . $nors['quor_id'], true);
                        $TrackingUpdate = str_replace("###1", $NewDeliveryOrderId, QUGEO_TRACKING_API_GATEWAY);
                        $TrackingUpdate = str_replace("###2", AWSDYNAMODBTABLEPREFIX, $TrackingUpdate);
                        $updateurl = str_replace("###1", "", $updateurl);
                        $updateurl = str_replace("###6", "1", $updateurl);
                        if (trim($updateurl) != "")
                            $db->query($updateurl);
                        //Insert into History
                        $updateurl = $db->getItemFromDb("select quor_TrackingHistory from qugeo_order where quor_id = " . $nors['quor_id'], true);
                        $updateurl = str_replace("##12", QUGEO_TO_B2C_ORDER_STATUS_PICKUP_FAILED, $updateurl);
                        if (trim($updateurl) != "")
                            $db->query($updateurl);
                        $AssOper = '-';
                        $AssignedLoadedWeight = $totwt;
                        $AssignedLoadedVolume = $totvol;
                    } else {
                        file_put_contents('php://stderr', "-----------------IsPickup--------succes-------------------------------");
                        $confirmationdetails = json_decode($request['confirmationdetails'], true);


                        if ($nors['AcceptedAsDirectDelivery'] == 1) {
                            file_put_contents('php://stderr', "-----------------IsPickup--------AcceptedAsDirectDelivery-------------------------------");
                            array_push($arrUpdate['Data'], array('col' => 'OrderStatus', 'val' => (int) ORDER_PICKUP_PICKEDUP_TODST_DLS_ID));
                            //FOR TABLE UPDATE
                            $db->query("UPDATE  qugeo_order set quor_Status=" . ORDER_PICKUP_PICKEDUP_TODST_DLS_ID . ",quor_PickedupTime='" . date("Y-m-d H:i:s", strtotime($valdatetime)) . "' where quor_id = " . $nors['quor_id']);
                            $updateurl = $db->getItemFromDb("select quor_StatusUpdateQry from qugeo_order where quor_id = " . $nors['quor_id'], true);
                            //$updateurl = str_replace("###1",QUGEO_TO_CUSTOMER_ORDER_STATUS_OUT_DELIVERY,$updateurl);
                            $updateurl = getQugeoParentStatusUpdated($updateurl, ORDER_PICKUP_PICKEDUP_TODST_DLS_ID);
                            $updateurl = str_replace("###6", "1", $updateurl);
                            $updateurl = str_replace("###2", "", $updateurl);
                            $execQry = explode(";", $updateurl);
                            if (trim($execQry[0]) != "")
                                $db->query(trim($execQry[0]));
                            if (trim($execQry[1]) != "")
                                $db->query(trim($execQry[1]));
                            $orderArr = array();
                            array_push($orderArr, $nors['quor_id']);
                            $directdelivery = new QugeoScheduler();
                            $NewDeliveryOrderId = $directdelivery->scheduleADeliverySchJobs($nors['quor_id'], $orderdetails, false, '', true, true, $nors['HandlingBranch'], $orderArr, 1, 1, 'Nor');

                            //FOR TRACKING
                            $updateurl = $db->getItemFromDb("select quor_TrackingUpdateQry from qugeo_order where quor_id = " . $nors['quor_id'], true);
                            $DriverName = $db->getItemFromDb("select d_Name from qugeo_driver where d_ID = " . $_SESSION["loginid"], true);
                            $DriverPhone = $db->getItemFromDb("select d_Ph1 from qugeo_driver where d_ID = " . $_SESSION["loginid"], true);
                            $TrackingUpdate = str_replace("###1", $NewDeliveryOrderId, QUGEO_TRACKING_API_GATEWAY);
                            $TrackingUpdate = str_replace("###2", AWSDYNAMODBTABLEPREFIX, $TrackingUpdate);
                            $updateurl = str_replace("###1", $TrackingUpdate, $updateurl);
                            $updateurl = str_replace("###6", "1", $updateurl);
                            $updateurl = str_replace("##10", addslashes($DriverName), $updateurl);
                            $updateurl = str_replace("##11", addslashes($DriverPhone), $updateurl);
                            if (trim($updateurl) != "")
                                $db->query($updateurl);
                            //Insert into History
                            $updateurl = $db->getItemFromDb("select quor_TrackingHistory from qugeo_order where quor_id = " . $nors['quor_id'], true);
                            $updateurl = str_replace("##12", QUGEO_TO_B2C_ORDER_STATUS_OUT_FOR_DELIVERY, $updateurl);
                            if (trim($updateurl) != "")
                                $db->query($updateurl);

                            $WasAdirectDeliveryPickup = true;
                        } else {
                            file_put_contents('php://stderr', "-----------------IsPickup--------NOtAcceptedAsDirectDelivery-------------------------------");
                            $db->query("UPDATE  qugeo_order set quor_Status=" . ORDER_PICKUP_PICKEDUP_TOBR_DLS_ID . ",quor_PickedupTime='" . date("Y-m-d H:i:s", strtotime($valdatetime)) . "' where quor_id = " . $nors['quor_id']);
                            $updateurl = $db->getItemFromDb("select quor_StatusUpdateQry from qugeo_order where quor_id = " . $nors['quor_id']);
                            //$updateurl = str_replace("###1",QUGEO_TO_CUSTOMER_ORDER_STATUS_OUT_DELIVERY,$updateurl);
                            $updateurl = getQugeoParentStatusUpdated($updateurl, ORDER_PICKUP_PICKEDUP_TOBR_DLS_ID);
                            $updateurl = str_replace("###6", "1", $updateurl);
                            $updateurl = str_replace("###2", "", $updateurl);
                            $execQry = explode(";", $updateurl);
                            if (trim($execQry[0]) != "")
                                $db->query(trim($execQry[0]));
                            if (trim($execQry[1]) != "")
                                $db->query(trim($execQry[1]));
                            //FOR TRACKING - CLEAR
                            $updateurl = $db->getItemFromDb("select quor_TrackingUpdateQry from qugeo_order where quor_id = " . $nors['quor_id'], true);
                            $TrackingUpdate = str_replace("###1", $NewDeliveryOrderId, QUGEO_TRACKING_API_GATEWAY);
                            $TrackingUpdate = str_replace("###2", AWSDYNAMODBTABLEPREFIX, $TrackingUpdate);
                            $updateurl = str_replace("###1", "", $updateurl);
                            $updateurl = str_replace("###6", "1", $updateurl);
                            if (trim($updateurl) != "")
                                $db->query($updateurl);
                            array_push($arrUpdate['Data'], array('col' => 'OrderStatus', 'val' => (int) ORDER_PICKUP_PICKEDUP_TOBR_DLS_ID));
                        }

                        $quor_TransferOrder_id = $db->getItemFromDB("SELECT quor_TransferOrder_id FROM qugeo_order WHERE quor_id = {$nors['quor_id']} ", true);
                        \DeliveryFinascop::DeliveryPickupVoucher($quor_TransferOrder_id);
                        $CurrentLoadedVolume = $totvol;
                        $CurrentLoadedWeight = $totwt;
                        $CurOper = '+';
                        if ($nors['HasReCalculatedCharges'] == 1) {
                            array_push($arrUpdate['Data'], array('col' => 'ReCalculcationPaymentType', 'val' => (int) $confirmationdetails['paymenttypeid']));
                        }
                    }
                } else { //delivery
                    if ($request['failed'] == 'true') {

                        file_put_contents('php://stderr', "-----------------delivery--------failed-------------------------------");
                        $delreason = $db->getItemFromDb("select dls_DelStatus from qugeo_deliverystatus where dls_ID = " . $request['failedreasonid'], true);
                        $updateurl = $db->getItemFromDb("select quor_StatusUpdateQry from qugeo_order where quor_id = " . $nors['quor_id'], true);
                        $temDetails = $db->getItemFromDb("select quor_ItemDetails from qugeo_order where quor_id = " . $nors['quor_id'], true);
                        $temDetailsarr = json_decode($temDetails);
                        $barcodearr = array_column($temDetailsarr, 'barcodes');
                        $barcodes = json_encode($barcodearr);
                        $barcodes = "[" . str_replace("]", "", str_replace("[", "", $barcodes)) . "]";
                        $db->query("UPDATE  qugeo_order set  quor_Status=" . $request['failedreasonid'] . ", quor_ItemReturned = '" . $barcodes . "' where quor_id = " . $nors['quor_id']);
                        array_push($arrUpdate['Data'], array('col' => 'OrderStatus', 'val' => (int) $request['failedreasonid'], 'return_items' => $barcodes));
                        //$updateurl = str_replace("###1",QUGEO_TO_CUSTOMER_ORDER_STATUS_DELIVERY_FAILED,$updateurl);

                        $updateurl = getQugeoParentStatusUpdated($updateurl, $request['failedreasonid']);
                        $updateurl = str_replace("###6", "1", $updateurl);
                        $updateurl = str_replace("###2", $delreason, $updateurl);
                        $execQry = explode(";", $updateurl);
                        if (trim($execQry[0]) != "")
                            $db->query(trim($execQry[0]));
                        if (trim($execQry[1]) != "")
                            $db->query(trim($execQry[1]));
                        //Insert into History
                        $updateurl = $db->getItemFromDb("select quor_TrackingHistory from qugeo_order where quor_id = " . $nors['quor_id'], true);
                        $updateurl = str_replace("##12", QUGEO_TO_B2C_ORDER_STATUS_DELIVERY_FAILED, $updateurl);
                        if (trim($updateurl) != "")
                            $db->query($updateurl);
                    } else {
                        file_put_contents('php://stderr', "-----------------delivery--------success-------------------------------");
                        $request['return_items'] = str_replace('"', '', $request['return_items']);
                        $request['return_items'] = str_replace('\'', '', $request['return_items']);
                        //$request['ondel_payment_mode'] 
                        //$request['ondel_payment_amount']
                        //$request['ondel_refer_id']
                        if($request['confirmdelivery'] == 1){
                            array_push($arrUpdate['Data'], array('col' => 'OrderStatus', 'val' => (int) ORDER_DELIVERY_MARKED_DLS_ID, 'return_items' => $request['return_items']));
                            $db->query("UPDATE  qugeo_order set  quor_Type = 1,quor_Status=" . ORDER_DELIVERY_MARKED_DLS_ID . ",quor_DeliveredTime='" . date("Y-m-d H:i:s", strtotime($valdatetime)) . "', quor_ItemReturned = '" . $request['return_items'] . "' where quor_id = " . $nors['quor_id']);
                            $updatedetails = $db->getFromDb("select quor_StatusUpdateQry,quor_AmountCollectible,quor_Deliverybr_id,quor_Paymode,quor_TransferOrder_Type from qugeo_order where quor_id = " . $nors['quor_id'], array(), true);
                            file_put_contents('php://stderr', json_encode($updatedetails));
                            $updateurl = $updatedetails['quor_StatusUpdateQry'];
                            //$updateurl = str_replace("###1",QUGEO_TO_CUSTOMER_ORDER_STATUS_DELIVERD,$updateurl);
                            file_put_contents('php://stderr', "hellonzz " . $nors['quor_id'] . ' -- ' . $updateurl . "\n");
                            $updateurl = getQugeoParentStatusUpdated($updateurl, ORDER_DELIVERY_MARKED_DLS_ID);
    
                            if ($updatedetails['quor_Paymode'] == 'Paid Online' || $updatedetails['quor_Paymode'] == 'Paid with Wallet') {
                                $db->query("UPDATE  qugeo_order set  quor_Status=" . ORDER_DELIVERY_COMPLETED_DLS_ID . ",quor_DeliveredTime='" . date("Y-m-d H:i:s", strtotime($valdatetime)) . "', quor_ItemReturned = '" . $request['return_items'] . "' where quor_id = " . $nors['quor_id']);
                                //$updateurl = str_replace(17, QUGEO_TO_B2C_ORDER_STATUS_DELIVERY_CONFIRMED, $updateurl);
                                $updateurl = str_replace("status_id = '17'", "status_id = '18'", $updateurl);
                            }
                            file_put_contents('php://stderr', "helllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllllonzz " . $nors['quor_id'] . ' -- ' . $updateurl . "\n");
                            $updateurl = str_replace("###2", "", $updateurl);
                            $updateurl = str_replace("###6", (intval($request['ondel_payment_mode']) == 1 ? 7 : 6), $updateurl);
                            $updateurl = str_replace("###7", (intval($request['ondel_payment_mode']) == 1 ? "" : $request['ondel_refer_id']), $updateurl);
                            $execQry = explode(";", $updateurl);
                            if (trim($execQry[0]) != "")
                                $db->query(trim($execQry[0]));
                            if (trim($execQry[1]) != "")
                                $db->query(trim($execQry[1]));
                            //Insert into History
    
                            $updateurl = $db->getItemFromDb("select quor_TrackingHistory from qugeo_order where quor_id = " . $nors['quor_id'], true);
    
                            if ($updatedetails['quor_Paymode'] == 'Paid Online' || $updatedetails['quor_Paymode'] == 'Paid with Wallet') {
                                $updateurl = str_replace("##12", QUGEO_TO_B2C_ORDER_STATUS_DELIVERY_CONFIRMED, $updateurl);
                            } else {
                                $updateurl = str_replace("##12", QUGEO_TO_B2C_ORDER_STATUS_DELIVERED_NOT_CONFIRMED, $updateurl);
                            }
    
                            if (trim($updateurl) != "")
                                $db->query($updateurl);
                            //$AssOper = 	'-';
                            //$AssignedLoadedWeight= $totwt;
                            //$AssignedLoadedVolume= $totvol;	
                            $CurrentLoadedVolume = $totvol;
                            $CurrentLoadedWeight = $totwt;
                            $CurOper = '-';
    
                            $sqldbconn = new \sqlDb(DSN);
                            $DriverName = $db->getItemFromDb("select d_Name from qugeo_driver where d_ID = " . $_SESSION["loginid"], true);
                            //Our delivery partner {#var#} has delivered your Order No.{#var#} successfully. Thank you for selecting {#var#}.
                            //1607100000000004819
                            $qry = "Our delivery partner " . $DriverName . " has delivered your Order No." . $JobNo . " successfully. Thank you for selecting." . PROJECT_NAME;
                            //\sms::send($deliverymobile, $qry, $sqldbconn, "");
                            $templatedata['order_order_id'] = $JobNo;
                            \sms::fetchContentSendSms($templatedata, $deliverymobile, 11);
                            $quor_TransferOrder_id = $db->getItemFromDB("SELECT quor_TransferOrder_id FROM qugeo_order WHERE quor_id = {$nors['quor_id']} ", true);
                            //\DeliveryFinascop::DeliveryVoucher($quor_TransferOrder_id);
                            if ($updatedetails['quor_AmountCollectible'] == 0) {
                                file_put_contents('php://stderr', 'DeliveryConfirmationVoucherDeliveryConfirmationVoucherDeliveryConfirmationVoucherDeliveryConfirmationVoucher  -- ' . $updatedetails['quor_AmountCollectible']);
                                if($updatedetails['quor_TransferOrder_Type'] == 1){
                                    $custOrderId = $db->getItemFromDb("select fstr_id from finascop_stock_transfer_order where fsto_id = " . $quor_TransferOrder_id, true);
                                    $quor_id = $nors['quor_id'];
                                    $delQry = "CALL UpdateDeliveryStatus($quor_id,$custOrderId,NULL)";
                                    $status = $db->query($delQry);
                                }
                                \DeliveryConfirmation::DeliveryConfirmationVoucher($quor_TransferOrder_id);
                                \DeliveryConfirmation::DeliveryEmail($quor_TransferOrder_id);
                            }
                            file_put_contents('php://stderr', 'ON DEL PAY MODE  -- ' . $request['ondel_payment_mode']);
    
                        }
                        
                        //margin
                    }
                    //FOR TRACKING - CLEAR
                    $updateurl = $db->getItemFromDb("select quor_TrackingUpdateQry from qugeo_order where quor_id = " . $nors['quor_id'], true);
                    $updateurl = str_replace("###1", "", $updateurl);
                    $updateurl = str_replace("###6", "1", $updateurl);
                    if (trim($updateurl) != "")
                        $db->query($updateurl);
                }

                $nors = $nodb->perform('QugeoOrderDetails', 'update', $arrUpdate, $response);
                $arrUpdate = array();
                $arrUpdate['PartitionKey'] = array('col' => 'apikey', 'val' => $request['apikey'], 'oper' => '=');
                $arrUpdate['SortKey'] = array('col' => 'orderid', 'val' => $request['orderid'], 'oper' => '=');
                $arrUpdate['Data'] = array();
                array_push($arrUpdate['Data'], array('col' => 'IsClosed', 'val' => 1));
                array_push($arrUpdate['Data'], array('col' => 'IsLiveOrder', 'val' => 0));

                $NewOrder = $nodb->perform('QugeoLiveVehicleOrders', 'update', $arrUpdate, $response);
                if ($AssOper != '' || $CurOper != '') {
                    $arrLiveVehicle = array();
                    $arrLiveVehicle['PartitionKey'] = array('col' => 'apikey', 'val' => $request['apikey'], 'oper' => '=');
                    $arrLiveVehicle['Data'] = array();
                    if ($AssOper != '') {
                        array_push($arrLiveVehicle['Data'], array('col' => 'AssignedLoadedWeight', 'val' => (float) $AssignedLoadedWeight, 'oper' => $AssOper));
                        array_push($arrLiveVehicle['Data'], array('col' => 'AssignedLoadedVolume', 'val' => (float) $AssignedLoadedVolume, 'oper' => $AssOper));
                    }
                    if ($CurOper != '') {
                        array_push($arrLiveVehicle['Data'], array('col' => 'CurrentLoadedVolume', 'val' => (float) $CurrentLoadedVolume, 'oper' => $CurOper));
                        array_push($arrLiveVehicle['Data'], array('col' => 'CurrentLoadedWeight', 'val' => (float) $CurrentLoadedWeight, 'oper' => $CurOper));
                    }
                    $nodb->perform('QugeoLiveVehicles', 'update', $arrLiveVehicle, $response);
                }

                $orderhandler = new QugeoOrderHandler();
                file_put_contents('php://stderr', "WasAdirectDeliveryPickup  quorIds\n ");
                file_put_contents('php://stderr', print_r($WasAdirectDeliveryPickup, TRUE));
                if ($WasAdirectDeliveryPickup) {
                    file_put_contents('php://stderr', "WasAdirectDeliveryPickup  ifffffffffffff\n ");
                    $udpatedorder = $orderhandler->UpdateOrderOnPoll(1, $NewDeliveryOrderId, $request['apikey'], true, $orderdetails);
                    $orderhandler->AssignOrderToQugeoDriverSch($NewDeliveryOrderId, $request['apikey'], $orderdetails, $neworder, $dummy2, true);
                    $hasorder = true;
                } else {
                    file_put_contents('php://stderr', "WasAdirectDeliveryPickup  elseeeeeeee\n ");
                    $hasorder = $orderhandler->GetNextOrder($request['apikey'], $neworder,false,$request['orderid']);
                }
                if ($hasorder) {
                    $arrSession['msg'] = 'Has new order';
                } else {
					if($request['confirmdelivery'] == 0){
						$arrSession['msg'] = 'Has new order';
					}else{
                    $arrSession['msg'] = 'No new order';
                    $orderhandler->UpdateReleasingLocation($request['apikey'], 0, 0);
					}
                }
                $arrSession['success'] = true;
            } else {
                $arrSession['success'] = false;
                $arrSession['msg'] = 'Invalid Order Id';
            }
            file_put_contents('php://stderr', "WasAdirectDeliveryPickup  arrSession\n ");
            file_put_contents('php://stderr', print_r($arrSession, TRUE));
            $arrSession['Data']['vehicle'] = $neworder;

            return $arrSession;
        }

        public function GET_itemdetails($flag, $request)
        {
            if (!array_key_exists('orderid', $request) || !isset($request)) {
                throw new \Exception('Missing POST parameters ');
            }
            //$util =  new Utils();
            //$extrainfo = array("event"=>"GetItemDetails",'order'=>$request['orderid']);
            //$util->LogGeoCordinates($request['geocoords'],$_SESSION["usertype"],$_SESSION["loginid"],$request["apikey"],$extrainfo); 
            $nodb = new \cgoDynamiteDB();
            $arrOrder['PartitionKey'] = array('col' => 'orderid', 'val' => $request['orderid'], 'oper' => '=');
            $arrOrder['getAttributes'] = array('quor_id');
            $nors = $nodb->query('QugeoOrderDetails', $arrOrder, 'getItem');
            $response = "";
            $arrSession = array();
            if ($nors != false) {
                $db = new \cgoSqlDB();
                $itemdets = $db->getItemFromDB("select quor_ItemDetails from  qugeo_order where quor_id = " . $nors['quor_id'], array(), true);
                $response = json_decode($itemdets);
                $arrSession['success'] = true;
                $arrSession['msg'] = 'Item Details';
            } else {
                $arrSession['msg'] = 'Get Item details action error';
            }
            $arrSession['Data'] = $response;
            return $arrSession;
        }

        public function GET_pendingorders($flag, $request)
        {
            file_put_contents('php://stderr', "GET_pendingorders........\n ");
            file_put_contents('php://stderr', print_r($request, TRUE));
            $db = new \cgoSqlDB();
            $getDriverDetails = $db->getFromDB('SELECT createdBy,d_ID,d_Name,sourceId FROM qugeo_driver WHERE d_apikey = ?', array('s', $request['apikey']), true);
            file_put_contents('php://stderr', print_r($getDriverDetails, TRUE));
            if ($getDriverDetails['createdBy'] <= 1) {
                $pendingorders = $db->getMulipleData('select quor_Status,if(quor_Status=22,"PICKUP", "DELIVERY") as drivetype,
                if(quor_Status=22,quor_PickupName,quor_DeliveryName) as name,if(quor_Status=22,quor_PickupPhone,
                quor_DeliveryPhone) as phone, quor_CreatedOn as Date, quor_id as id,quor_RefNo as OrderNo,
                md5(quor_UpdateOn) as `key`  from  qugeo_order  where quor_slot_id = 0 AND (quor_Pickupbr_id=? OR quor_Deliverybr_id=?) and(quor_DeliveryMethodsAllowed&1) = 1  
                and ((quor_Status = 22 OR quor_Status = 31) OR (quor_Status IN(23,27,9) AND quor_DeliveryDriverId = ?))  
                order by quor_CreatedOn asc limit ?,? ', array('iiiii', intval($_SESSION["branchid"]), intval($_SESSION["branchid"]), intval($getDriverDetails['d_ID']),intval($request['start']), intval($request['limit'])), true);
            } else {
                if ($getDriverDetails['createdBy'] == 2) {
                    $arrOrder = array();
                    $arrOrder['PartitionKey'] = array('col' => 'apikey', 'val' => $request['apikey'], 'oper' => '=');
                    $arrOrder['getAttributes'] = array('Latitude', 'Longitude', 'Home_Latitude', 'Home_Longitude', 'DeliveryRange', 'AssignedLoadedWeight', 'AssignedLoadedVolume', 'CurrentLoadedVolume', 'CurrentLoadedWeight', 'TotalJobs', 'DriverName', 'mobno');
                    $nodb = new \cgoDynamiteDB();
                    $driverDetails = $nodb->query('QugeoLiveVehicles', $arrOrder, 'getItem');
                    file_put_contents('php://stderr', "\n....driverDetails........\n ");
                    file_put_contents('php://stderr', print_r($driverDetails, TRUE));

                    /*$areaDetails = $db->getFromDB('SELECT areaLocation,areaSpan,areaLatitude,areaLongitude FROM area_entries WHERE areaBusinessAssociate = ?', array('i', intval($getDriverDetails["sourceId"])), true);
                    file_put_contents('php://stderr', print_r($areaDetails, TRUE));
                    $lat = $areaDetails['areaLatitude']; //latitude
                    $lon = $areaDetails['areaLongitude']; //longitude
                    $distance = $areaDetails['areaSpan']; //your distance in KM
                    $R = 6371; //constant earth radius. You can add precision here if you wish*/

                    $lat = $driverDetails['Latitude']; //latitude
                    $lon = $driverDetails['Longitude']; //longitude
                    $distance = $driverDetails['DeliveryRange']; //your distance in KM
                    $R = 6371;
                    $maxLat = $lat + rad2deg($distance / $R);
                    $minLat = $lat - rad2deg($distance / $R);
                    $maxLon = $lon + rad2deg(asin($distance / $R) / cos(deg2rad($lat)));
                    $minLon = $lon - rad2deg(asin($distance / $R) / cos(deg2rad($lat)));
                    file_put_contents('php://stderr', "\n....minLat........\n ");
                    file_put_contents('php://stderr', print_r($minLat, TRUE));
                    file_put_contents('php://stderr', "\n....minLon........\n ");
                    file_put_contents('php://stderr', print_r($minLon, TRUE));
                    file_put_contents('php://stderr', "\n....maxLat........\n ");
                    file_put_contents('php://stderr', print_r($maxLat, TRUE));
                    file_put_contents('php://stderr', "\n....maxLon........\n ");
                    file_put_contents('php://stderr', print_r($maxLon, TRUE));
                    $pendingorders = $db->getMulipleData('select quor_Status,if(quor_Status=22,"PICKUP", "DELIVERY") as drivetype,
                    if(quor_Status=22,quor_PickupName,quor_DeliveryName) as name,if(quor_Status=22,quor_PickupPhone,
                    quor_DeliveryPhone) as phone, quor_CreatedOn as Date, quor_id as id,quor_RefNo as OrderNo,
                    md5(quor_UpdateOn) as `key`,br_rdrIdExpress,quor_Pickupbr_id,br_name  from  qugeo_order 
                    INNER JOIN finascop_branch ON br_ID = quor_Pickupbr_id                     
                    where quor_slot_id = 0 AND  (br_Lat >= ? AND br_Lng >= ? AND br_Lat <= ? AND br_Lng <= ?) and(quor_DeliveryMethodsAllowed&1) = 1  
                    and ((quor_Status = 22 OR quor_Status = 31) OR (quor_Status IN(23,27,9) AND quor_DeliveryDriverId = ?))  
                    order by quor_CreatedOn asc limit ?,? ', array('ssssiii', floatval($minLat), floatval($minLon), floatval($maxLat), floatval($maxLon),intval($getDriverDetails['d_ID']), intval($request['start']), intval($request['limit'])), true);

                    //,rdr.is_default,rdr.rdr_ruleFor
                    //INNER JOIN retaline_delivery_rules rdr ON rdr_deliveryMode = 2 AND rdr_ruleFor = 3 AND rdr_calculationMode = 3
                }
            }
            $vehicles = array();
            if ($pendingorders !== false) {
                $vehicles['success'] = true;
                $vehicles['msg'] = 'Pending orders';
                $vehicles['Data']['pendingorders'] = $pendingorders;
            } else {
                $vehicles['msg'] = 'No Pending orders';
                $vehicles['Data']['pendingorders'] = array();
            }
            return $vehicles;
        }

        public function GET_myorders($flag, $request)
        {
            //quor_PickupDriverId, quor_DeliveryDriverId
            $db = new \cgoSqlDB();
            $pendingorders = $db->getMulipleData('select if(quor_Status=22,"PICKUP", "DELIVERY") as drivetype,if(quor_Status=22,quor_PickupName,quor_DeliveryName) as name,if(quor_Status=22,quor_PickupPhone,quor_DeliveryPhone) as phone, quor_CreatedOn as Date, quor_RefNo as OrderNo  from  qugeo_order  where (quor_QugeoPickupDDBDriverId = ? or  quor_QugeoDeliveryDDBDriverId = ?)  order by quor_CreatedOn asc limit ?,? ', array('iiii', $request["apikey"], $request["apikey"], intval($request['start']), intval($request['limit']),), true);
            $vehicles = array();
            if ($pendingorders !== false) {
                $vehicles['success'] = true;
                $vehicles['msg'] = 'My orders';
                $vehicles['Data']['myorders'] = $pendingorders;
            } else {
                $vehicles['msg'] = 'No orders';
                $vehicles['Data']['myorders'] = array();
            }
            return $vehicles;
        }

        public function GET_deliveredorders($flag, $request)
        {
            $db = new \cgoSqlDB();
            $pendingorders = $db->getMulipleData('select quor_DeliveryName as name,quor_DeliveryPhone as phone, 
            quor_DeliveredTime as Date, quor_RefNo as OrderNo,(SELECT br_Name FROM finascop_branch WHERE br_ID = quor_Pickupbr_id) AS pickUpBranch,
            (SELECT br_Name FROM finascop_branch WHERE br_ID = quor_Deliverybr_id) AS deliveryBranch,quor_PickupLocation,
            quor_DeliveryLocation  from  qugeo_order  where quor_Type = 1  and  quor_DeliveryDriverId = ? and (quor_Status = 15 or quor_Status = 38)   order by quor_DeliveredTime desc limit ?,? ', array('iii', intval($_SESSION["loginid"]), intval($request['start']), intval($request['limit'])), true);
            $vehicles = array();
            if ($pendingorders !== false) {
                $vehicles['success'] = true;
                $vehicles['msg'] = 'Delivered orders';
                $vehicles['Data']['deliveredorders'] = $pendingorders;
            } else {
                $vehicles['msg'] = 'No Delivered orders';
                $vehicles['Data']['deliveredorders'] = array();
            }
            return $vehicles;
        }

        public function GET_cashinhand($flag, $request)
        {
            $cashinhand = array();
            $cashinhand['success'] = true;
            $cashinhand['msg'] = 'Cash In Hand';
            $cashinhand['Data']['cashinhand'] = "-";
            return $cashinhand;
        }

        public function GET_myearnings($flag, $request)
        {
            $cashinhand = array();
            $cashinhand['success'] = true;
            $cashinhand['msg'] = 'Earnings';
            $cashinhand['Data']['myearnings'] = "-";
            return $cashinhand;
        }

        public function POST_pullpendingorder($flag, $request)
        {
            //drivetype, Id
            if (!array_key_exists('key', $request) || !array_key_exists('drivetype', $request) || !array_key_exists('id', $request) || !isset($request)) {
                throw new \Exception('Missing POST parameters ');
            }
            file_put_contents('php://stderr', "POST_pullpendingorder CALLED " . $request["id"] . " \n ");
            file_put_contents('php://stderr', print_r($request, TRUE));
            $db = new \cgoSqlDB();
            $updateon = $db->getItemFromDB("select md5(quor_UpdateOn) as updton from  qugeo_order where quor_id = " . $request['id'], array(), true);

            $validOrder = $db->getItemFromDB("select quor_Status from  qugeo_order where quor_id = " . $request['id'], array(), true);

            /*if ($validOrder != ORDER_PICKUP_AT_ORIGIN_DLS_ID) {
                $varra['success'] = false;
                $varra['msg'] = "Order already accepted by another driver. Please select a different order.";
                $varra['Data']['pendingorder'] = "";
                return $varra;
            }*/

            if ($request['key'] <> $updateon) {
                $pullpendingorder = array();
                $pullpendingorder['success'] = false;
                $pullpendingorder['msg'] = 'Order has been updated, please reload and try again ';
                $pullpendingorder['Data']['pendingorder'] = $request['key'] . "<>" . $updateon;
                return $pullpendingorder;
            }

            $scheduleorder = new QugeoScheduler();
            if ($scheduleorder->IsQugeoAPIAlive($request["apikey"]) == false) {
                echo '{"success":false,"msg":"The Vehicle isnt active anymore, please reload"}';
                return;
            }
            $orderArr = array();
            array_push($orderArr, $request['id']);
            if (strtoupper($request['drivetype']) == 'PICKUP') {
                $orderid = $scheduleorder->scheduleABookingSchJobs($request['id'], $orderdetails, true, $request["apikey"], true, $orderArr, 1, 1, 'Nor');
            } else {
                $orderid = $scheduleorder->scheduleADeliverySchJobs($request['id'], $orderdetails, true, $request["apikey"], true, false, 0, $orderArr, 1, 1, 'Nor');
            }

            $pullpendingorder = array();
            $pullpendingorder['success'] = true;
            $pullpendingorder['msg'] = 'Order has been pushed';
            $pullpendingorder['Data']['pullpendingorder'] = array();
            return $pullpendingorder;
        }

        public function GET_liveorders($flag, $request)
        {
            file_put_contents('php://stderr', "GET_liveorders CALLED ...... \n ");
            file_put_contents('php://stderr', print_r($request, TRUE));
            $orderIds = array();
            $deliveryLocs = array();
            $db = new \cgoSqlDB();
            $polledOrders = $db->getMulipleData('select *  from  qugeo_driver_log  where pollid = ?  order by id asc ', array('s', $request["msgid"]), true);

            foreach ($polledOrders as $polledOrder) {
                $delLocations = $db->getFromDB("SELECT quor_id,quor_RefNo,quor_PickupPincode,quor_PickupLat,quor_PickupLng,quor_PickupAddress,quor_PickupLocation,quor_DeliveryPincode,quor_DeliveryLat,quor_DeliveryLng,quor_DeliveryLocation,quor_DeliveryAddress,"
                    . "quor_Pickupbr_id,quor_Deliverybr_id,quor_QugeoPickupDDBOrderId,quor_QugeoDeliveryDDBOrderId,dls_DelStatus,quor_Status,quor_PickupPhone FROM  qugeo_order INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status WHERE quor_Status IN (22,23,25,9,27,31,32) AND quor_id = ?", array('i', $polledOrder['quorId']), true);

                if ($delLocations['quor_id'] > 0) {
                    $quor_QugeoPickupDDBOrderId = (!empty($delLocations['quor_QugeoDeliveryDDBOrderId'])?$delLocations['quor_QugeoDeliveryDDBOrderId']:$delLocations['quor_QugeoPickupDDBOrderId']);
                    $quor_DeliveryLocation = $delLocations['quor_DeliveryAddress'] . '' . $delLocations['quor_DeliveryPincode'];
                    array_push($orderIds, array("id" => $quor_QugeoPickupDDBOrderId, "order" => $polledOrder['quorId'], "orderNo" => $delLocations['quor_RefNo'], "location" => $quor_DeliveryLocation, "latitude" => $delLocations['quor_DeliveryLat'], "longitude" => $delLocations['quor_DeliveryLng'], "orderStatus" => $delLocations['dls_DelStatus'], "statusId" => $delLocations['quor_Status']));
                    array_push($deliveryLocs, array("latitude" => $delLocations['quor_DeliveryLat'], "longitude" => $delLocations['quor_DeliveryLng'], "location" => $quor_DeliveryLocation));
                }
            }
            $geocoords = array(
                "pickup" => array("latitude" => $delLocations['quor_PickupLat'], "longitude" => $delLocations['quor_PickupLng'], "location" => $delLocations['quor_PickupLocation'], "address" => $delLocations['quor_PickupAddress'], "mobile" => ['quor_PickupPhone']),
                "delivery" => $deliveryLocs
            );
            //$orders = json_encode($orderIds);
            //$details = json_encode($geocoords);
            $orders = $orderIds;
            $details = $geocoords;


            $vehicles = array();
            if ($polledOrders !== false) {
                if($request['hasaccepted'] == 'true'){
                    file_put_contents('php://stderr', "hasaccepted hasaccepted trueeee");
                    $db->query("UPDATE qugeo_firebase_log SET rfir_StatusId=2 where rfir_StatusId=1 AND rfir_token = '".$request['fcm_token']."'");
                }else{
                    file_put_contents('php://stderr', "hasaccepted hasaccepted falseeee");
                    $db->query("UPDATE qugeo_firebase_log SET rfir_StatusId=3 where rfir_StatusId=1 AND rfir_token = '".$request['fcm_token']."'");
                }
                $vehicles['success'] = true;
                $vehicles['msg'] = 'Polled orders';
                $vehicles['Data']['orders'] = $orders;
                $vehicles['Data']['details'] = $details;
            } else {
                $vehicles['msg'] = 'No orders';
                $vehicles['Data']['myorders'] = array();
                $vehicles['Data']['details'] =  array();
            }
            file_put_contents('php://stderr', "GET_liveorders responseresponseresponse \n ");
            file_put_contents('php://stderr', print_r($vehicles, TRUE));

            return $vehicles;
        }
        public function GET_s3Details($flag, $request)
        {
            file_put_contents('php://stderr', "GET_s3Details CALLED ...... \n ");
            file_put_contents('php://stderr', print_r($request, TRUE));

            $urls = array();

            $file_name = sha1(microtime(true) . mt_rand(10000, 90000));
            $s3 = new \Aws\S3\S3Client([
                'region'        => AWSS3ASSETUPLOADREGION,
                'version'       => 'latest',
                'credentials'   => array(
                    'key'           => AWSS3ASSETUPLOADACCESSID,
                    'secret'        => AWSS3ASSETUPLOADSECRETKEY,
                )
            ]);

            $cmdSignature = $s3->getCommand('PutObject', [
                'Bucket' => AWSBUCKETNAME,
                'Key'       => 'drive/' . md5('ymdHisu') . '.jpg',
                'ACL' => 'public-read'
            ]);

            $requestSignature = $s3->createPresignedRequest($cmdSignature, '+20 minutes');
            $presignedSignatureUrl = (string)$requestSignature->getUri();
            $signatureUrl = strtok($presignedSignatureUrl, '?');
            array_push($urls, array("type" => "signature", "presignedUrl" => $presignedSignatureUrl, "imageurl" => $signatureUrl));

            $cmdPhoto = $s3->getCommand('PutObject', [
                'Bucket' => AWSBUCKETNAME,
                'Key'       => 'drive/' . md5('ymdHisu') . '.jpg',
                'ACL' => 'public-read'
            ]);
            $requestImage = $s3->createPresignedRequest($cmdPhoto, '+20 minutes');
            $presignedImageUrl = (string)$requestImage->getUri();
            $imageUrl = strtok($presignedImageUrl, '?');
            array_push($urls, array("type" => "photo", "presignedUrl" => $presignedImageUrl, "imageurl" => $imageUrl));
            file_put_contents('php://stderr', print_r($presignedImageUrl, TRUE));

            $jsonString = json_encode($urls);
            $object = json_decode($jsonString);

            $vehicles = array();
            if ($requestSignature !== false && $requestImage !== false) {
                $vehicles['success'] = true;
                $vehicles['msg'] = 'URL Details';
                $vehicles['Data']['Details'] = $object;
            } else {
                $vehicles['msg'] = 'Failed to create url.';
                $vehicles['Data']['Details'] = array();
            }
            file_put_contents('php://stderr', print_r($vehicles, TRUE));
            return $vehicles;
        }
        public function POST_checkspotreturn($flag, $request)
        {
            if (!array_key_exists('orderid', $request) ||  !isset($request)) {
                throw new \Exception('Missing POST parameters ');
            }
            if ($request['orderid'] == '') {
                throw new \Exception('Invalid order id');
            }


            file_put_contents('php://stderr', "checkspotreturn CALLED " . $request["orderid"] . " \n ");
            file_put_contents('php://stderr', print_r($request, TRUE));
            $nodb = new \cgoDynamiteDB();
            $db = new \cgoSqlDB();
            $arrOrder = array();
            $util = new Utils();
            $extrainfo = array("event" => "checkspotreturn", "requestdetails" => array("orderid" => $request["orderid"]));
            $util->LogGeoCordinates($request['geocoords'], $_SESSION["usertype"], $_SESSION["loginid"], $request["apikey"], $extrainfo);
            $arrOrder['PartitionKey'] = array('col' => 'orderid', 'val' => $request['orderid'], 'oper' => '=');
            $arrOrder['getAttributes'] = array('IsPickup', 'HasReCalculatedCharges', 'ReCalculatedCharges', 'quor_id', 'HasDirectDeliveryPickUp', 'totwt', 'totvol', 'AcceptedAsDirectDelivery', 'HandlingBranch', 'quor_RefNo', 'deliverymobile');
            $nors = $nodb->query('QugeoOrderDetails', $arrOrder, 'getItem');
            $db = new \cgoSqlDB();
            $isSpotReturn = $db->getItemFromDb('SELECT SUM(stit_custInitiate) FROM finascop_stock_itemmaster INNER JOIN finascop_stock_transfer_order_details ON fsto_ItemId = stit_ID 
            INNER JOIN finascop_stock_transfer_order ON finascop_stock_transfer_order.fsto_id = finascop_stock_transfer_order_details.fsto_id INNER JOIN qugeo_order ON quor_TransferOrder_id = finascop_stock_transfer_order.fsto_id 
            WHERE quor_id =' . $nors["quor_id"], true);

            $itemDdetails = $db->getMulipleData('SELECT stit_ID,stit_SKU,stit_custInitiate,fsto_pkdQty FROM finascop_stock_itemmaster INNER JOIN finascop_stock_transfer_order_details ON fsto_ItemId = stit_ID 
            INNER JOIN finascop_stock_transfer_order ON finascop_stock_transfer_order.fsto_id = finascop_stock_transfer_order_details.fsto_id INNER JOIN qugeo_order ON quor_TransferOrder_id = finascop_stock_transfer_order.fsto_id 
            WHERE quor_id = ?', array('i', $nors["quor_id"]), true);

            $vehicles = array();
            if ($isSpotReturn > 0) {
                $vehicles['success'] = true;
                $vehicles['msg'] = 'Order have spot returnable items.';
                $vehicles['Data']['details'] = $itemDdetails;
            } else {
                $vehicles['msg'] = 'No items available in spot return';
                $vehicles['Data']['details'] =  array();
            }
            return $vehicles;
        }

        public  function POST_pollednotifications($flag, $request)
        {
			file_put_contents('php://stderr', "POST_pollednotifications");
            file_put_contents('php://stderr', print_r($request, TRUE));
            $pollednotification = "";
            $db = new \cgoSqlDB();
            $pollednotification = $db->getItemFromDb("SELECT rfir_payload FROM qugeo_firebase_log where rfir_StatusId = 1 
            and rfir_token = '".$request['token']."' and TIMESTAMPDIFF(MINUTE, rfir_date, '".date("Y-m-d H:i:s")."') <= 3 order by id desc ",true);  

            $pullpendingorder['success'] = true;
            $pullpendingorder['msg'] = 'Recent Notification';
            $pullpendingorder['Data']['pollednotification'] = $pollednotification;
			file_put_contents('php://stderr', print_r($pollednotification, TRUE));
            return $pullpendingorder;
        }
    }
}
