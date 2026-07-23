<?php

switch ($op) {
    case 'getUserPrescriptonData':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $filter_qry = " WHERE 1 = 1 ";
        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }
        switch ($_POST['type']) {
            case 'NV':
                $status = ' AND status = 0';
                $statName = 'Unverified';
                $couStatus = 0;
                break;
            case 'Verified':
                $status = ' AND status = 1';
                $statName = 'Verified';
                $couStatus = 1;
                break;
            case 'Approved':
                $status = ' AND status = 3';
                $statName = 'Approved';
                $couStatus = 3;
                break;
        }
        $date = date('Y-m-d');
        $countDataQuery = "SELECT count(*) from upload_prescription fp INNER JOIN retaline_customer um ON fp.cust_id = um.cust_id {$filter_qry} {$status} GROUP BY fp.cust_id ORDER BY id ASC,isAssigned DESC";
        $listQuery = "SELECT  id,fp.cust_id as customerId,cust_customer_name,cust_email,cust_mobile,/*DATE_FORMAT(created_at,'%d-%m-%Y') as */created_at,/*DATE_FORMAT(expiry_date,'%d-%m-%Y') as*/ expiry_date,status,isAssigned,assignedUser,isSkipped,
            order_id,priority,prescription_json,prescriptionmedi_json,(SELECT COUNT(1) FROM upload_prescription cp WHERE cp.status = {$couStatus} AND cp.cust_id = fp.cust_id) as  preCount,'{$statName}' AS statusName FROM upload_prescription fp INNER JOIN retaline_customer um ON fp.cust_id = um.cust_id {$filter_qry} {$status} GROUP BY fp.cust_id ORDER BY id ASC,isAssigned DESC LIMIT $start,$limit ";
        $db->printGridJson($countDataQuery, $listQuery);
        break;
    case 'getUserForMappingUserPrescription':

        $data = $_POST['id'];
        $customerId = $_POST['customerId'];
        $type = $_POST['type'];
        switch ($type) {
            case 'NV':
                $con = " cust_id = {$customerId} AND status = 0";
                $alreadyAssigned = $db->getItemFromDB("SELECT COUNT(*) FROM upload_prescription WHERE status = 0 AND assignedUser = {$_SESSION['admin']->Finascop_UserId}");
                break;
            case 'Verified':
                $con = " cust_id = {$customerId} AND status = 1";
                $alreadyAssigned = $db->getItemFromDB("SELECT COUNT(*) FROM upload_prescription WHERE status = 1 AND assignedUser = {$_SESSION['admin']->Finascop_UserId}");
                break;
        }
        if ($alreadyAssigned > 0) {
            echo "{success: false, msg: 'You have already accepted a request, Please update the status and choose another.'}";
            exit();
        } else {
            $db->query('begin');
            $UserId = array(
                "assignedUser" => $_SESSION['admin']->Finascop_UserId,
                "isAssigned" => 1,
                "updated_at" => date('Y-m-d H:i:s'),
                "updatedBy" => $_SESSION['admin']->Finascop_UserId
            );
            $status = $db->perform(FINASCOP_DB . 'upload_prescription', $UserId, 'update', $con);

            $status = $db->query('commit');
            if ($status == 1) {
                echo "{success: true,msg:'Details has been saved successfully',data:" . json_encode($return_rec) . " }";
            } else {
                echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
            }
        }
        break;
    case 'mapPrescriptionMedicine':
        $customerId = $_REQUEST['customerId']; //'&customerId=' + customerId + 
        $type = $_REQUEST['type'];
        $orderId = $_REQUEST['order_id'];
        switch ($type) {
            case'NV':
                $status = ' AND status = 0';
                break;
            case 'Verified':
                $status = ' AND status = 1';
                break;
            case 'Approved':
                $status = ' AND status = 3';
                break;
        }
        $awspath = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'AWSPATH'");
        $prescriptions = $db->getMultipleData("SELECT id,prescription_json,prescriptionmedi_json,CONCAT('{$awspath}','',prescription_folder,'',document_url) as document_url,order_id,priority FROM  upload_prescription WHERE cust_id = {$customerId} {$status} order by priority asc", true);
        if ($orderId > 0) {
            $orderItems = $db->getMultipleData("SELECT ppi.item_product_id,item_order_qty,stit_SKU FROM retaline_customer_order_items ppi INNER JOIN finascop_stock_itemmaster fsi ON fsi.stit_ID = ppi.item_product_id  WHERE customer_order_id = {$orderId}", true);
        }
        if ($customerId > 0) {
            $precrip = json_encode($prescriptions);
            $ordItem = json_encode($orderItems);
            echo '{"success":true,"prescription":' . $precrip . ',"orderItem":' . $ordItem . '}';
        } else {
            echo '{"success":false,"data":"Nothing to display"}';
        }
        break;
    case 'postMedicines':
        $orderItems = $db->getMultipleData("SELECT stit_ID,stit_SKU  FROM finascop_stock_itemmaster WHERE  stit_status = 1 AND  stit_SKU LIKE '%{$_POST['medicineName']}%'", true);//isMedicine = 1 AND
        $count = count($orderItems);
        if ($orderItems != false) {
            $orderItems[$count]['stit_ID'] = 'NA';
            $orderItems[$count]['stit_SKU'] = 'Not Legible';
            $ordItem = json_encode($orderItems);
            echo '{"success":true,"medicinelist":' . $ordItem . '}';
        } else {
            echo '{"success":false,"data":"Nothing to display"}';
        }
        break;
    case 'getPrescriptionStatus':
        $id = $_POST['id'];
        $customerId = $_POST['customerId'];
        $upprec['status'] = $_POST['status'];
        $upprec['isAssigned'] = 0;
        $upprec['assignedUser'] = 0;
        $upprec['updated_at'] = date('Y-m-d H:i:s');
        $upprec['updatedBy'] = $_SESSION['admin']->Finascop_UserId;

        $db->query('begin');
        $status = $db->perform(FINASCOP_DB . 'upload_prescription', $upprec, 'update', "id = {$id}");
        $status = $db->query('commit');
        if ($status == 1) {
            echo '{"success":true,"msg":"Details has been saved successfully"}';
        } else {
            echo "{success:false,msg: 'Error occured while saving data'}";
        }
        break;
    case 'getMapMedicinePrescriptions':
        $id = $_POST['id'];
        $expiry_date = date("Y-m-d", strtotime($_POST['expiry_date']));
        $uppresc['prescription_json'] = "{$_POST['prescription_json']}";
        $uppresc['prescriptionmedi_json'] = "{$_POST['prescriptionmedi_json']}";

        $uppresc['updated_at'] = date('Y-m-d H:i:s');
        $uppresc['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $uppresc['expiry_date'] = $expiry_date;
        $uppresc['isSkipped'] = 0;
        $uppresc['isAssigned'] = 0;
        $uppresc['assignedUser'] = 0;
        $uppresc['isScheduleProcessed'] = 0;
        $db->query('begin');

        $medicines = $_POST['medicines'];
        $medicineIds = explode(',', $medicines);
        $medCount = count($medicineIds);
        $notLegibleFlag = 0;
        for ($i = 0; $i < $medCount; $i++) {
            $mpmm['pmm_expirydate'] = $expiry_date;
            $mpmm['prescription_id'] = $id;
            if ($medicineIds[$i] == 'NA') {
                $notLegibleFlag = 1;
            } else {
                $pmm_id = $db->getItemSafe("SELECT pmm_id FROM mypha_prescriptiom_medicine_map WHERE stit_Id = {$medicineIds[$i]} AND cust_id = ?", "i", [$_POST['customerId']]);
                if ($pmm_id > 0) {
                    $mpmm['pmm_createdOn'] = date('Y-m-d H:i:s');
                    $mpmm['pmm_createdBy'] = $_SESSION['admin']->Finascop_UserId;
                    $status = $db->perform(FINASCOP_DB . 'mypha_prescriptiom_medicine_map', $mpmm, 'update', " pmm_id = {$pmm_id}");
                } else {
                    $mpmm['stit_Id'] = $medicineIds[$i];
                    $mpmm['cust_id'] = $_POST['customerId'];
                    $mpmm['pmm_updatedOn'] = date('Y-m-d H:i:s');
                    $mpmm['pmm_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
                    $status = $db->perform(FINASCOP_DB . 'mypha_prescriptiom_medicine_map', $mpmm);
                }
            }
        }
        if ($notLegibleFlag == 0) {
            $uppresc['status'] = 3;
        } else {
            $uppresc['status'] = 5;
        }

        $status = $db->perform(FINASCOP_DB . 'upload_prescription', $uppresc, 'update', 'id=' . $id);
        $orderId = $db->getItemFromDB("SELECT order_id FROM upload_prescription WHERE  id = {$id}");

        $orderPres = $db->getItemFromDB("SELECT count(*) FROM upload_prescription WHERE status = 1 AND order_id = {$orderId}");
        if ($orderPres == 0 && $notLegibleFlag == 0) {
            $rco['order_approvedOn'] = date('Y-m-d H:i:s');
            $rco['order_approvalStatus'] = 1;
            $rco['order_approvedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform(FINASCOP_DB . 'retaline_customer_order', $rco, 'update', " order_id = {$orderId}");
        }


        $status = $db->query('commit');
        if ($status == 1) {
            echo '{"success":true,"msg":"Details has been saved successfully"}';
        } else {
            echo "{success:false,msg: 'Error occured while saving data'}";
        }
        break;
    case 'skipPrescription':
        $upprec['isAssigned'] = 0;
        $upprec['isSkipped'] = 1;
        $upprec['assignedUser'] = 0;
        $upprec['updated_at'] = date('Y-m-d H:i:s');
        $upprec['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $upprec['skippedBy'] = $_SESSION['admin']->Finascop_UserId;
        $upprec['skippedOn'] = date('Y-m-d H:i:s');

        $db->query('begin');
        $status = $db->perform(FINASCOP_DB . 'upload_prescription', $upprec, 'update', "status = 1 AND assignedUser = {$_SESSION['admin']->Finascop_UserId}");
        $status = $db->query('commit');
        if ($status == 1) {
            echo '{"success":true,"msg":"Details saved successfully"}';
        } else {
            echo "{success:false,msg: 'Error occured while saving data'}";
        }
        break;
    case 'getQueuedPrescriptonData':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 23;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $filter_qry = " WHERE 1 = 1 ";
        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }

        $date = date('Y-m-d');
        $countDataQuery = "SELECT count(*) from retaline_customer_order fp INNER JOIN retaline_customer um ON fp.order_customer_id = um.cust_id {$filter_qry} {$status} ORDER BY order_id ASC ";
        $listQuery = "SELECT  order_id,order_order_id,order_isB2b,order_customer_id,order_branch_id,order_prescription_validated,cust_customer_name,cust_mobile,created_at,order_customer_id FROM retaline_customer_order fp INNER JOIN retaline_customer um ON fp.order_customer_id = um.cust_id {$filter_qry} {$status}  ORDER BY order_id ASC LIMIT $start,$limit ";
        $db->printGridJson($countDataQuery, $listQuery);
        break;
    case 'getS3detailsforFiles':
        $s3data['vcrresizer'] = AWSBUCKETUPLOADS;
        $s3data['vcrbucket'] = AWSBUCKETNAME;
        $s3data['access_Key'] = AWSS3ASSETUPLOADACCESSID;
        $s3data['secret_Key'] = AWSS3ASSETUPLOADSECRETKEY;
        $s3data['bucket_Region'] = AWSS3ASSETUPLOADREGION;
        $s3data['oncompleteurl'] = AWSPRESCFOLDER;
        $data = json_encode($s3data);
        echo '{"success":true,"data":' . $data . '}';
        break;
    case 'savePrescriptionFiles':
        $orderId = $_POST['orderId'];
        $customerId = $_POST['customerId'];
        $data['document_url'] = $_POST['assignment_filename'];
        $data['created_at'] = date("Y-m-d H:i:s");
        $data['updated_at'] = date("Y-m-d H:i:s");
        $data['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $data['order_id'] = $orderId;
        $data['cust_id'] = $customerId;
        $data['prescription_folder'] = AWSPRESCFOLDER;
        $data['status'] = 1;
        $db->query('begin');
        $status = $db->perform('upload_prescription', $data);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'File Saved..'}";
        } else {
            echo "{'success':false,'valid':false,'message': 'Error Occured during processg.'}";
        }
        break;
    case 'listMedicinesMapped':
        $customerId = $_REQUEST['customerId']; //'&customerId=' + customerId + 
        $type = $_REQUEST['type'];
        $orderId = $_REQUEST['order_id'];
        switch ($type) {
            case'NV':
                $status = ' AND status = 0';
                break;
            case 'Verified':
                $status = ' AND status = 1';
                break;
            case 'Approved':
                $status = ' AND status = 3';
                break;
        }
        $rec_limit = empty($data['limit']) ? 16 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'stiid_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' AND 1=1';

        if (isset($_POST['filter'])) {

            foreach ($_POST['filter'] as $key => $val) {
                if ($val['field'] == 'stiid_barcode') {
                    $filter_part .= " and " . $val['field'] . " = " . $val['data']['value'] . " ";
                } else {
                    $filter_part .= " and " . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM  mypha_prescriptiom_medicine_map WHERE cust_id = {$customerId} ";
        $listQuery = "SELECT pmm_id,cust_id,pmm_expirydate,stit_SKU FROM  mypha_prescriptiom_medicine_map INNER JOIN finascop_stock_itemmaster ON finascop_stock_itemmaster.stit_ID = mypha_prescriptiom_medicine_map.stit_Id  WHERE cust_id = {$customerId} ";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'getHoldOrders':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;

        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'order_id' : $sort;
        //$sort = ($sort=='order_created_on') ? 'DATE(order_created_on)' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $userID = $_SESSION['admin']->UserId;
        $filter_qry = "WHERE 1=1 ";

        $order = '';
        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
                }
            }
        }
        if ($_POST['current_branch_id'] > 0) {
            $current_branch_id = $_POST['current_branch_id'];
        } else {
            $current_branch_id = $db->getItemSafe("SELECT br_ID FROM finascop_branch WHERE br_Name = ?", "s", [$_POST['br_Name']]);
        }
        if ($sort == 'order_created_on') {
            $sort = 'order_id';
        }
        if ($_POST['br_Name'] != '') {
            $filter_qry .= " AND br_Name  LIKE  '" . $_POST['br_Name'] . "%'";
        }


        if ($sort == 'order_created_on') {
            $sort = 'order_id';
        }
        if ($_POST['br_Name'] != '') {
            $filter_qry .= " AND br_Name  LIKE  '" . $_POST['br_Name'] . "%'";
        }
        if ($_SESSION['admin']->br_PyramidLevel == 2) {
            $centralStore = $_SESSION['admin']->finascop_current_branch_id;
            $distributors = $db->getItemFromDB("SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_cpd = {$centralStore}");
            $reatailors = $db->getItemFromDB("SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_cpd IN ({$distributors})");
            $current_branch_id = $reatailors;
        } else {
            $current_branch_id = $_SESSION['admin']->finascop_current_branch_id;
        }


        $query = " SELECT order_id,order_order_id,order_packedbags_count,bco.order_customer_id,order_branch_id,br_Name,bco.status_id as status,DATE_FORMAT(created_at,'%d-%m-%Y') AS order_created_on,
             TIME_FORMAT(cast(created_at as time),'%r') as ordertime,admin_description AS order_status,admin_description,order_payment_gateway_refid,order_payment_gateway_refid_crc32,
            (SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS delivery_to,(SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS cust_mobile,
            order_HasReturn,order_ItemsReturned,order_ReturnVerified,created_at,order_total_amount,payment_mode,order_method
            FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id  WHERE 1 = 1 and bco.status_id = 22 AND order_branch_id IN ({$current_branch_id})"; //and bco.status_id = 22
        $countQuery = $db->getItemFromDB(" SELECT COUNT(*) FROM ({$query}) AS orerCount {$filter_qry} ORDER BY  {$sort} {$dir} ");
        $listQuery = "SELECT * FROM({$query}) as orderList  {$filter_qry}  ORDER BY  {$sort} {$dir} limit " . $start . "," . $limit;
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {

                $orderItems = $db->getItemFromDB("SELECT count(*) FROM retaline_customer_order_items WHERE customer_order_id = {$datas[$i]['order_id']}");
                $prescriptionStatus = $db->getItemFromDB("SELECT STATUS FROM upload_prescription WHERE order_id = {$datas[$i]['order_id']} ORDER BY updated_at DESC LIMIT 1");
//                $quor_Type = $db->getItemFromDB("SELECT quor_Type FROM qugeo_order WHERE quor_TransferOrder_Type = 1 AND quor_TransferOrder_id = {$datas[$i]['order_id']}");
//                $datas[$i]['quor_DeliveryMethodsAllowed'] = $db->getItemFromDB("SELECT quor_DeliveryMethodsAllowed FROM qugeo_order WHERE quor_TransferOrder_Type = 1 AND quor_TransferOrder_id = {$datas[$i]['order_id']}");
//                $datas[$i]['quor_Type'] = $quor_Type;
                $datas[$i]['prescStatusId'] = $prescriptionStatus;
                switch ($datas[$i]['order_method']) {
                    case '1':
                        $datas[$i]['order_methodName'] = 'Deliver';
                        break;
                    case '2':
                        $datas[$i]['order_methodName'] = 'Collect';
                        break;
                    case '3':
                        $datas[$i]['order_methodName'] = 'Courier';
                        break;
                }

                switch ($prescriptionStatus) {
                    case '0':
                        $datas[$i]['prescStatusName'] = 'Uploaded'; //Unverified
                        break;
                    case '1':
                        $datas[$i]['prescStatusName'] = 'Verified';
                        break;
                    case '2':
                        $datas[$i]['prescStatusName'] = 'Rejected';
                        break;
                    case '3':
                        $datas[$i]['prescStatusName'] = 'Approved';
                        break;
                    case '4':
                        $datas[$i]['prescStatusName'] = 'Expired';
                        break;
                    case '5':
                        $datas[$i]['prescStatusName'] = 'Incomplete';
                        break;
                }

                $datas[$i]['itemCount'] = $orderItems;
            }
            echo '{"totalCount":"', $countQuery, '","data":' . json_encode($datas) . '}';
        }
        break;
    case 'holdorder_details':
        require(THIS_MODULE_PATH . "/order_details.php");
        break;
}