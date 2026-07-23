<?php

$userid = $_SESSION['admin']->Finascop_UserId;

switch ($op) {

    case 'dispatchdetailsView':
        $bcd_id = $_GET['bcd_id'];
        if ($bcd_id) {
            $result = $db->getMultipleData("SELECT order_no,order_id,
                    CASE WHEN order_status = 0 THEN 'Created'
                WHEN order_status = 1 THEN 'Manual Queued'
                WHEN order_status = 2 THEN 'Polled'
                 WHEN order_status = 3 THEN 'Assigned'
                WHEN order_status = 4 THEN 'Scanning Started'
                WHEN order_status = 5 THEN 'Incomplete Order'
                WHEN order_status = 6 THEN 'Order Completed'
                WHEN order_status = 7 THEN 'Cancelled'
                WHEN order_status = 8 THEN 'Expired'
                WHEN order_status = 9 THEN 'Dispatched'
                WHEN order_status = 10 THEN 'Partly Received'
                ELSE 'Received'
                    END AS order_status,fcp.br_Name AS cpd_Name,fb.br_Name AS branch_name FROM retaline_branch_dispatch dis 
                    INNER JOIN retaline_branch_outward_order cpo ON dis.bcd_id=cpo.bcd_id 
                    INNER JOIN finascop_branch fcp ON fcp.br_ID = cpo.cpd_id 
                    INNER JOIN finascop_branch fb ON fb.br_ID = cpo.branch_id WHERE dis.bcd_id =" . $bcd_id, true);
            $disdata = $db->getFromDB("SELECT bcd_id,bcd_vehicleNo,bcd_driver,TIME_FORMAT(bcd_dispatchTime, '%r') AS bcd_dispatchTime,DATE_FORMAT(bcd_dispatchDate, '%d-%m-%Y')  AS bcd_dispatchDate FROM retaline_branch_dispatch WHERE bcd_id = $bcd_id", true);
            require(THIS_MODULE_PATH . "/dispatch_iframe_view.php");
        }
        break;

    case 'listDispatchDetails':
        $cpddipatchDate = date('Y-m-d', strtotime($_POST['cpddipatchDate']));
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'rbd.bcd_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }

        if ($_POST['cpddipatchDate']) {
            $searchitem = " AND bcd_dispatchDate = '{$cpddipatchDate}'";
        }
        $countQuery = "SELECT COUNT(*) FROM retaline_branch_dispatch  {$search} {$searchitem}";
        $listQuery = "SELECT rbd.bcd_id AS bcdId,bcd_vehicleNo,bcd_driver,TIME_FORMAT(bcd_dispatchTime, '%r') AS bcd_dispatchTime,DATE_FORMAT(bcd_dispatchDate, '%d-%m-%Y')  AS bcd_dispatchDate,"
                . "(SELECT br_Name FROM finascop_branch WHERE br_ID = branch_id) AS branch, order_no "
                . "FROM retaline_branch_dispatch rbd INNER JOIN retaline_branch_outward_order rbod ON rbod.bcd_id = rbd.bcd_id" . "{$search}{$searchitem}  ORDER BY CAST({$sort} as char) {$dir},binary {$sort} {$dir} limit $rec_start,$rec_limit";
        $db->printGridJson($countQuery, $listQuery);
        break;
// completed orders of a cpd is for dispatch
    case 'listScheduleOrderData':
        $cpd_id = $_POST['cpd_id'];
        if ($cpd_id != 0) {
            $cpId = "AND cpo.cpd_id = {$cpd_id} ";
        }
        //$cbrid = $_POST['current_branch_id'];
        $countQuery = "SELECT 0";
        $listQuery = "SELECT quor_id,quor_RefNo,quor_TransferOrder_id,DATE_FORMAT(quor_Date, '%d-%m-%Y') AS quor_Date,fcp.br_Name AS cpd_Name,fb.br_Name AS branch_name FROM qugeo_order "
                . "INNER JOIN finascop_stock_transfer_order fsto ON quor_RefNo = fsto_id "
                . "INNER JOIN finascop_branch fcp ON fcp.br_ID = fsto.fsto_source "
                . " INNER JOIN finascop_branch fb ON fb.br_ID = fsto.fsto_destination WHERE quor_Type = 2";
//        $listQuery = "SELECT order_id,order_no,order_no_last_id,cpo.cpd_id,DATE_FORMAT(bcor_createdon, '%d-%m-%Y') AS bcor_createdon,branch_id,'Completed' AS order_status,fcp.br_Name AS cpd_Name ,"
//                . "fb.br_Name AS branch_name FROM retaline_branch_outward_order cpo "
//                . "INNER JOIN finascop_branch fcp ON fcp.br_ID = cpo.branch_id "
//                . "INNER JOIN finascop_branch fb ON fb.br_ID = cpo.branch_id WHERE  order_status = 6 $cpId";

        // $listQuery = "SELECT order_id,order_no,order_no_last_id,cpo.cpd_id,DATE_FORMAT(bcor_createdon, '%d-%m-%Y') AS bcor_createdon,branch_id,'Completed' AS order_status,cp.cpd_Name AS cpd_Name ,"
        //         . "br_Name AS branch_name FROM retaline_branch_outward_order cpo "
        //         . "INNER JOIN retaline_cpd cp ON cp.cpd_id = cpo.cpd_id "
        //         . "INNER JOIN finascop_branch fb ON fb.br_ID = cpo.branch_id WHERE cpo.branch_id = {$cbrid} AND cpo.cpd_id = {$cpd_id} AND order_status = 6";
        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'dispatchDetails':
        $db->query('begin');
        $date = date('Y-m-d', strtotime($_POST['dispatch_date']));
        $time = date("H:i:s", strtotime($_POST['dispatch_time']));
        $dispatch_array = explode(',', $_POST['dispatch_array']);
        $data = array(
            "bcd_vehicleNo" => $_POST['vehicle_no'],
            "bcd_driver" => $_POST['driver'],
            "bcd_dispatchDate" => $date,
            "bcd_dispatchTime" => $time,
            "bcd_id" => $_POST['id']
            //"bcd_status" => 0
        );
        $orderdata = array();
        unset($data['bcd_id']);
        $data['bcd_createdOn'] = date('Y-m-d H:i:s');
        $data['bcd_createdBy'] = $userid;
        $status = $db->perform('retaline_branch_dispatch', $data);
        
        
        $lastId = $db->insert_id();
        if ($dispatch_array) {
            for ($i = 0; $i < count($dispatch_array); $i++) {
//                $DispatchVehicle['bcd_id'] = $lastId;
//                $DispatchVehicle['order_status'] = 9;
//                $orderstatus = $db->perform("retaline_branch_outward_order", $DispatchVehicle, 'update', 'order_id =' . (int) $dispatch_array[$i]);
                $bcdddata['quor_id'] = $lastId;
                $quor_TransferOrder_id - $db->getItemFromDB("SELECT quor_TransferOrder_id FROM qugeo_order WHERE quor_id = {$dispatch_array[$i]} ");
                $transfreOrderDetails = $db->getFromDb("SELECT fsto_source,fsto_sourcetype,fsto_destination,fsto_destinationtype FROM finascop_stock_transfer_order WHERE fsto_id = {$quor_TransferOrder_id}",true);
                $bcdddata['bcdd_source'] = $transfreOrderDetails['fsto_source'];
                $bcdddata['bcdd_sourceType'] = $transfreOrderDetails['fsto_sourcetype'];
                $bcdddata['bcdd_destination'] = $transfreOrderDetails['fsto_destination'];
                $bcdddata['bcdd_destinationType'] = $transfreOrderDetails['fsto_destinationtype'];
                $bcddstatus = $db->perform('retaline_branch_dispatch_details',$bcdddata);
                $fstodata['fsto_status'] = 9;
                $fstodata['fsto_updateon'] = datae("Y-m-d H:i:s");
                $fstodata['fsto_updateby'] = $userid;
                $fstostatus = $db->perform('finascop_stock_transfer_order',$fstodata,'update',' fsto_id = {$quor_TransferOrder_id}');
                $fsiid['stiid_status'] = 3;
                $fsiid['stiid_updatedon'] = date('Y-m-d H:i:s');
                $fsiid['stiid_updatedby'] = $userid;
                $fsiidstatus = $db->perform("finascop_stock_item_inventorydetails", $fsiid, 'update', 'cpd_order_id =' . (int) $dispatch_array[$i]);

                $inventoryDetails = $db->getMultipleData("SELECT * FROM finascop_stock_item_inventorydetails WHERE cpd_order_id =" . (int) $dispatch_array[$i], true);
                foreach ($inventoryDetails as $inventoryDetail) {
                    $fsiidmData['stiid_id'] = $inventoryDetail['stiid_id'];
                    $fsiidmData['stiidm_itemmasterid'] = $inventoryDetail['stiid_itemmasterid'];
                    $fsiidmData['stiidm_barcode'] = $inventoryDetail['stiid_barcode'];
                    $fsiidmData['created_at'] = date('Y-m-d H:i:s');
                    $fsiidmData['stiidm_details'] = 'Dispatched this item in the dispatch order '.$lastId;
                    $fsiidmstatus = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiidmData);
                }
            }
        }

        $return_rec = $db->getFromDb("SELECT * from retaline_branch_dispatch WHERE bcd_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;
}
?>


