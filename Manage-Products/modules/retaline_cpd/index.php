<?php

require_once(INCLUDE_PATH . "/finascop_accounts_Master.php");
require_once(INCLUDE_PATH . "/finascop_common_functions.php");
global $db;
switch ($op) {
    case 'assignExecutive':

        $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'ASSIGNEXEC'");
        $fields = array(
            "is_cpd" => 1,
            "order_id" => $_POST['order_ID'],
            "boy_id" => $_POST['id'],
            "branch_id" => $_POST['br_ID'],
            "is_b2border" => 0,
            "orderautoId" => $_POST['orderautoId']
        );
        $fields_string = json_encode($fields);
        $opts = array(
            CURLOPT_URL => $url,
            CURLINFO_CONTENT_TYPE => "application/json",
            CURLOPT_BINARYTRANSFER => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST => count($fields),
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        header("Content-Type: application/json");
        echo $data;

        break;

    case 'loadExecutive':
        //$branch_ID=$_POST['branch_ID'];
        $cpd_ID = $_POST['cpd_ID'];
        $rec_limit = empty($data['limit']) ? 16 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'name' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' 1=1';

        // if (isset($data['filter'])) {
        //     foreach ($data['filter'] as $key => $val) {
        //         $filter_part .= " and " . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
        //     }
        // }
        $countQuery = "SELECT COUNT(*) from retaline_godown_boy where {$filter_part} AND has_open_orders = 0 AND branch_id={$cpd_ID} AND fcm_id <> '' AND is_offline = 0 ";
        $listQuery = "SELECT id,name,phone,has_open_orders from retaline_godown_boy WHERE {$filter_part}  AND has_open_orders = 0 AND branch_id={$cpd_ID} AND fcm_id <> '' AND is_offline = 0  ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";

//        $countQuery = "SELECT COUNT(*) from retaline_godown_boy where {$filter_part} AND has_open_orders = 0 AND branch_id={$cpd_ID} AND fcm_id <> '' AND is_offline = 0";
//        $listQuery = "SELECT id,name,phone,has_open_orders from retaline_godown_boy WHERE {$filter_part} AND has_open_orders = 0 AND branch_id={$cpd_ID} AND fcm_id <> '' AND is_offline = 0 ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
// outward  orders of current cpd, to assign an executive the order should be manually queued
    case 'listScheduleOrder':
        $cpd_id = $_POST['cpd_id'];
        $cbrid = $_POST['current_branch_id'];
        $cpd_id = $cbrid;
        if ($cpd != '' && $br_id != '') {
            $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
            $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
            $sort = empty($sort) ? 'order_id' : $sort;
            $dir = empty($dir) ? 'DESC' : $dir;
            $search = " WHERE 1=1 ";
            $filter = $_POST['filter'];
            if (isset($filter)) {

                foreach ($filter as $key => $field) {
                    switch ($field['data']['type']) {
                        case 'list':
                            if ($field['field'] == 'order_status') {
                                if ($field['data']['value'] == 'Created') {
                                    $fiterItem = 0;
                                    $searchitem .= " and ({$field[field]} = {$fiterItem}) ";
                                } else if ($field['data']['value'] == 'Manual Queued') {
                                    $fiterItem = 1;
                                    $searchitem .= " and ({$field[field]} = {$fiterItem}) ";
                                } else if ($field['data']['value'] == 'Polled') {
                                    $fiterItem = 2;
                                    $searchitem .= " and ({$field[field]} = {$fiterItem}) ";
                                } else if ($field['data']['value'] == 'Assigned') {
                                    $fiterItem = 3;
                                    $searchitem .= " and ({$field[field]} = {$fiterItem}) ";
                                } else if ($field['data']['value'] == 'Scanning Started') {
                                    $fiterItem = 4;
                                    $searchitem .= " and ({$field[field]} = {$fiterItem}) ";
                                } else if ($field['data']['value'] == 'Incomplete Order') {
                                    $fiterItem = 5;
                                    $searchitem .= " and ({$field[field]} = {$fiterItem}) ";
                                } else if ($field['data']['value'] == 'Order Completed') {
                                    $fiterItem = 6;
                                    $searchitem .= " and ({$field[field]} = {$fiterItem}) ";
                                } else if ($field['data']['value'] == 'Cancelled') {
                                    $fiterItem = 7;
                                    $searchitem .= " and ({$field[field]} = {$fiterItem}) ";
                                } else if ($field['data']['value'] == 'Expired') {
                                    $fiterItem = 8;
                                    $searchitem .= " and ({$field[field]} = {$fiterItem}) ";
                                } else if ($field['data']['value'] == 'Dispatched') {
                                    $fiterItem = 9;
                                    $searchitem .= " and ({$field[field]} = {$fiterItem}) ";
                                } else if ($field['data']['value'] == 'Partly Received') {
                                    $fiterItem = 10;
                                    $searchitem .= " and ({$field[field]} = {$fiterItem}) ";
                                } else if ($field['data']['value'] == 'Received') {
                                    $fiterItem = 11;
                                    $searchitem .= " and ({$field[field]} = {$fiterItem}) ";
                                }
                            }

                            break;
                        default:


                            $checkComa = strstr($field['data']['value'], ',');

                            if ($checkComa != '') {
                                $fiterItem = $field['data']['value'];
                                $fiterItem = str_replace(',', "','", $fiterItem);
                                $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                            } else {
                                $searchitem .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                            }
                    }
                }
            }
            /* if ($cpd_id != 0) {
              $cpId = "AND cpo.cpd_id = {$cpd_id}";
              } */
            $countQuery = "SELECT COUNT(*) FROM retaline_branch_outward_order cpo 
            INNER JOIN finascop_branch cp ON cp.br_ID = cpo.cpd_id 
            INNER JOIN finascop_branch fb ON fb.br_ID = cpo.branch_id WHERE cpo.cpd_id = {$cbrid} $cpId $searchitem ";

            $listQuery = "SELECT order_id,order_no,order_no_last_id,cpo.cpd_id,DATE_FORMAT(bcor_createdon, '%d-%m-%Y') AS bcor_createdon,DATE_FORMAT(bcor_createdon, '%d-%m-%Y %H:%i:%s') AS createddatetime,
                DATE_FORMAT(bcor_updatedon, '%d-%m-%Y %H:%i:%s') AS updatedatetime,branch_id,
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
            END AS order_status,
            cp.br_Name AS cpd_Name ,fb.br_Name AS branch_name FROM retaline_branch_outward_order cpo 
            INNER JOIN finascop_branch cp ON cp.br_ID = cpo.cpd_id 
            INNER JOIN finascop_branch fb ON fb.br_ID = cpo.branch_id WHERE cpo.cpd_id = {$cbrid} $cpId $searchitem " . " ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";
            $db->printGridJson($countQuery, $listQuery);



            //     $listQuery = "SELECT order_id,order_no,order_no_last_id,cpo.cpd_id,DATE_FORMAT(bcor_createdon, '%d-%m-%Y') AS bcor_createdon,branch_id,
            //     CASE WHEN order_status = 0 THEN 'Created'
            //         WHEN order_status = 1 THEN 'Manual Queued'
            //         WHEN order_status = 2 THEN 'Assigned'
            //         WHEN order_status = 3 THEN 'Scanning Started'
            //         WHEN order_status = 4 THEN 'Incomplete Order'
            //         WHEN order_status = 5 THEN 'Order Completed'
            //         WHEN order_status = 6 THEN 'Cancelled'
            //         WHEN order_status = 7 THEN 'Expired'
            //         WHEN order_status = 8 THEN 'Dispatched'
            //         WHEN order_status = 9 THEN 'Partly Received'
            //         ELSE 'Received'
            //     END AS order_status,
            //     cp.cpd_Name AS cpd_Name ,br_Name AS branch_name FROM retaline_branch_outward_order cpo INNER JOIN brm_cpd cp ON cp.cpd_id = cpo.cpd_id INNER JOIN finascop_branch fb ON fb.br_ID = cpo.branch_id WHERE cpo.branch_id = {$cbrid} AND cpo.cpd_id = {$cpd_id}  $searchitem " . " ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";
            //     $db->printGridJson($countQuery, $listQuery);
            // 
        }
        break;

    case 'getComboStore':

        $branch = new \finascop\accounts\Master\Branch();
        $branch->getComboStore($_GET['ind'], $_POST['state']);

        break;

    case 'saveCPD':

        $db->query('begin');
        $data = $_POST;
        $data = array_filter($data);
        unset($data['apikey']);
        unset($data['tstamp']);
        if ($data['cpd_id'] > 0) {
            $dupCPD = $db->getItemFromDB("SELECT COUNT(*) FROM brm_cpd WHERE cpd_name = '{$data['cpd_Name']}' AND cpd_id <> {$data['cpd_id']}");
            if ($dupCPD > 0) {
                echo "{success: false, msg:  'CPD Already Exists' }";
                exit();
            } else {
                $status = $db->perform('brm_cpd', $data, 'update', " cpd_id = {$data['cpd_id']}");
            }
        } else {
            $dupCPD = $db->getItemFromDB("SELECT COUNT(*) FROM brm_cpd WHERE cpd_name = '{$data['cpd_Name']}'");
            if ($dupCPD > 0) {
                echo "{success: false, msg:  'CPD Already Exists' }";
                exit();
            } else {
                $status = $db->perform('brm_cpd', $data);
            }
        }
        $status = $db->query('commit');
        if ($status > 0) {
            echo "{success: true, msg:  'CPD Saved' }";
        } else {
            echo "{success: false, errors:  'FINASCOP: Error occured while saving data' }";
        }
        break;

    case 'listCpd':
        $rec_limit = empty($data['limit']) ? 16 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'cpd_Name' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . "brm_cpd";
        $listQuery = "SELECT cpd_id,cpd_Name,"
                . "(SELECT dst_Name FROM " . FINASCOP_DB . "finascop_district WHERE dst_Id = cpd_District) as cpd_District,"
                . "(SELECT st_name FROM " . FINASCOP_DB . "finascop_state WHERE st_ID = cpd_State) as cpd_State,"
                . "cpd_Address,cpd_Fax,cpd_Phone,cpd_Incharge,cpd_status,cpd_pincode,cpd_Lat,cpd_Lng "
                . "from " . FINASCOP_DB . "brm_cpd a WHERE {$filter_part} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'getCpdDetails':
        $id = $_POST['id'];
        if (!empty($id)) {
            $db->_loadRecordJson("SELECT cpd_id,cpd_Name, cpd_District, cpd_State,cpd_Address,cpd_Fax,cpd_Phone,cpd_Incharge,cpd_pincode,cpd_Lat,cpd_Lng
					from " . FINASCOP_DB . "brm_cpd a WHERE cpd_id = " . $id, true);
        }
        break;

    case 'changeStatus':
        $brid = $_POST['cpd_id'];
        $br_status = $_POST['cpd_status'];
        $st = ($br_status == 'Active') ? 'Inactive' : 'Active';
        $up = array('cpd_status' => $st);
        $db->query('begin');
        $status = $db->perform(FINASCOP_DB . "brm_cpd", $up, "update", "cpd_id={$brid}");

        $status = $db->query('commit');

        if ($status == 1) {
            echo "{success: true,msg:  'Status Changed' }";
        } else
            echo "{errors: { reason: 'Error occured while saving data' }}";
        break;

    case 'getGoDownExecutive':
        $rec_limit = empty($data['limit']) ? 16 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = '1=1';

        /*    if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    } */
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');
                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $filter_part .= " and ({$field['field']} IN('{$fiterItem}')) ";
//                    } else if ($field['field'] == 'cpd_name') {
//                        $cpd_name = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(id),0) FROM retaline_godown_boy WHERE branch_id IN (SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_Name LIKE '{$field['data']['value']}%')");
//                        $filter_part .= " AND id  IN({$cpd_name}) ";
                    } else {
                        $filter_part .= " and " . $field['field'] . " LIKE '%" . $field['data']['value'] . "%' ";
                    }
                }
            }
        }
        $query = "SELECT id,name, 
            phone,branch_id AS cpdid,is_cpd,                  
            CASE  WHEN is_cpd = 1 THEN 'CPD' 
                    WHEN is_cpd = 2 THEN 'Central Store'
                    WHEN is_cpd = 3 THEN 'Distributor'
                    WHEN is_cpd = 4 THEN 'Retailer' END AS type,
                    IF((is_offline=1),'Offline','Online') AS is_offline,
                    latlng_updated_at,
                    IF((is_allowAutoSchedule=1),'Yes','No')  as is_allowAutoSchedule,
                    IF((is_allowManualSchedule=1),'Yes','No')  as is_allowManualSchedule,
                        (SELECT br_Name FROM finascop_branch cp WHERE br_ID = cpdid)  AS cpd_name
                    from retaline_godown_boy gb ";
        $countQuery = "SELECT COUNT(*) from ({$query}) as gcount WHERE {$filter_part} ";
        $listQuery = " SELECT * FROM({$query}) AS lisgow WHERE {$filter_part} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";

        //    echo $listQuery;
        //    exit(1);
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'getCPDName':
        //$qry = $db->getMulipleData("SELECT cpd_id, cpd_Name FROM brm_cpd", true);
        $qry = '';

        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'savegodownExecutive':
        $id = $_POST['id'];
        if ($_POST['cpdname'] == '') {
            $branchname = $_POST['branch_name'];
        }
        if ($_POST['branch_name'] > 0) {
            $branchname = $_POST['branch_name'];
        } else {
            $branchname = $_POST['branch_id'];
        }

        $phone  = $_POST['phone'];
        if (empty($_POST['id'])) {
            $isPhoneDuplicated = $db->getItemFromDB("SELECT COUNT(1) FROM retaline_godown_boy WHERE phone = " . $phone);
        } else {
            $isPhoneDuplicated = $db->getItemFromDB("SELECT COUNT(1) FROM retaline_godown_boy WHERE phone = " . $phone . " AND id <>" . $id);
        }

        if ($isPhoneDuplicated >= 1) {
            echo "{success:true,valid:false,message:'Order Picker with Mobile Phone number already exists.'}";
            exit();
        }

        $data = array(
            "name" => $_POST['name'],
            "lname" => $_POST['lname'],
            "emp_id" => $_POST['emp_id'],
            "emp_ni_number" => $_POST['emp_ni_number'],
            "emp_email_id" => $_POST['emp_email_id'],
            "emp_add1" => $_POST['emp_add1'],
            "emp_add2" => $_POST['emp_add2'],
            "emp_pincode" => $_POST['emp_pincode'],
            "phone" => $_POST['phone'],
            "branch_id" => $branchname,
            "is_cpd" => $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch WHERE br_ID = {$branchname}"),
            "is_allowManualSchedule" => $_POST['is_allowManualSchedule'],
            "is_allowAutoSchedule" => $_POST['is_allowAutoSchedule'],
            "gdwn_party" => $_POST['gdwn_party']
        );
        $db->query('begin');
        if (empty($_POST['id'])) {
            $status = $db->perform('retaline_godown_boy', $data);
        } else {
            $status = $db->perform('retaline_godown_boy', $data, "update", "id='" . $id . "'");
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ' }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'loadgodownExecutive':
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id) {
            //$qry = $db->getFromDB("SELECT name,phone,branch_id,(select cpd_Name from brm_cpd  where cpd_id = branch_id)as branch_id_hidden FROM retaline_godown_boy WHERE id = $id", true);
            $qry = $db->getFromDB("SELECT name,lname,emp_id,emp_ni_number,emp_email_id, emp_add1, emp_add2,emp_pincode,
                phone,branch_id as branch_name,is_allowManualSchedule,is_allowAutoSchedule,
(SELECT br_Name FROM finascop_branch  WHERE br_ID = branch_id) AS cpd_branch,gdwn_party,(SELECT stpa_Fname FROM finascop_stock_party  WHERE stpa_id = gdwn_party) AS gdwn_partyname
 FROM retaline_godown_boy WHERE id = $id", true);
            if (!$qry) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($qry),
                '}';
            }
        }
        break;
    case 'getBranchName':
        //to show only distributor and central store 
        $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch where br_status = 'Active' and br_PyramidLevel IN (2,3,4)", true);


        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'listItemsinOutwardOrder':
        $rec_limit = empty($data['limit']) ? 16 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'bcod_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' AND 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(*) from retaline_branch_outward_order_items WHERE bcor_id = " . intval($_POST['orderId']) . " {$filter_part}";
        $listQuery = "SELECT bcod_id,bcor_id,stit_ID,bcod_Count,(SELECT stit_SKU FROM finascop_stock_itemmaster im WHERE im.stit_ID = oi.stit_ID) as stitSKU from retaline_branch_outward_order_items oi WHERE bcor_id = " . intval($_POST['orderId']) . " {$filter_part}  ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'schedulerinvoke':
        $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'CPD_TO_BR_INVOKE_TRANSFER'");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        echo $server_output;
        break;
    case 'revokeOrder':
        $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'REVOKEORDER'");
        $boyId = $db->getItemSafe("SELECT boy_id FROM retaline_godown_boy_orders_request WHERE order_id = ? limit 1", "i", [$_POST['orderId']]);
        $orderType = $_POST['orderType'];
        if ($orderType == 'branch') {
            $is_cpd = 0;
        } else {
            $is_cpd = 1;
        }
        $fields = array(
            "is_cpd" => $is_cpd,
            "order_id" => $_POST['orderId'],
            "boy_id" => (int) $boyId
        );
        $fields_string = json_encode($fields);
        $opts = array(
            CURLOPT_URL => $url,
            CURLINFO_CONTENT_TYPE => "application/json",
            CURLOPT_BINARYTRANSFER => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST => count($fields),
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        header("Content-Type: application/json");
        echo $data;
        break;
    case 'listScannedBarcodes':
        $rec_sort = empty($data['sort']) ? 'boib_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' AND 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(*) from retaline_branch_outward_order_items_barcodes WHERE bcod_id = " . intval($_POST['bcod_id']) . "  {$filter_part}";
        $listQuery = "SELECT boib_id,stiid_barcode from retaline_branch_outward_order_items_barcodes WHERE bcod_id = " . intval($_POST['bcod_id']) . "  ORDER BY $rec_sort $rec_sort_dir ";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'listPolledHistory':
        $rec_sort = empty($data['sort']) ? 'created_at' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' AND 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        if ($_POST['orderType'] == 'cpd') {
            $cpd = 1;
        } else {
            $cpd = 0;
        }
        $countQuery = "SELECT COUNT(*) FROM(SELECT (SELECT name FROM retaline_godown_boy WHERE id = boy_id) as boy_name,order_id,STATUS AS ordersreqtatus,created_at,updated_at,'-' AS accepted_time,'-' AS scan_start_time,'-' AS last_scan_time,'-' AS completed_time "
                . "FROM retaline_godown_boy_orders_request WHERE order_id = '{$_POST['orderId']}'  AND branch_id = {$_POST['current_branch_id']} UNION
SELECT (SELECT name FROM retaline_godown_boy WHERE id = boy_id) as boy_name,order_id,STATUS AS ordersreqtatus,DATE_FORMAT(created_at,'%d-%m-%Y %H:%i:%s') as created_at,DATE_FORMAT(updated_at,'%d-%m-%Y %H:%i:%s') as updated_at,
DATE_FORMAT(accepted_time,'%d-%m-%Y %H:%i:%s') as accepted_time,DATE_FORMAT(scan_start_time,'%d-%m-%Y %H:%i:%s') as scan_start_time,DATE_FORMAT(last_scan_time,'%d-%m-%Y %H:%i:%s') as last_scan_time,DATE_FORMAT(completed_time,'%d-%m-%Y %H:%i:%s') as completed_time 
FROM retaline_godown_boy_orders WHERE order_id = '{$_POST['orderId']}'   AND branch_id = {$_POST['current_branch_id']}) as hisCount";

        $listQuery = "SELECT (SELECT name FROM retaline_godown_boy WHERE id = boy_id) as boy_name,order_id,status,CASE WHEN status = 1 THEN 'Request Sent'
                WHEN status = 2 THEN 'Accepted'
                 WHEN status = 3 THEN 'Rejected'
                WHEN status = 4 THEN 'Time Out'
            END AS ordersreqtatus,DATE_FORMAT(created_at,'%d-%m-%Y %H:%i:%s') as created_at,DATE_FORMAT(updated_at,'%d-%m-%Y %H:%i:%s') as updated_at,'-' AS accepted_time,'-' AS scan_start_time,'-' AS last_scan_time,'-' AS completed_time "
                . "FROM retaline_godown_boy_orders_request WHERE order_id = '{$_POST['orderId']}'   AND branch_id = {$_POST['current_branch_id']} UNION
SELECT (SELECT name FROM retaline_godown_boy WHERE id = boy_id) as boy_name,order_id,status,CASE WHEN status = 1 THEN 'Accepted'
                WHEN status = 2 THEN 'Scanning started'
                 WHEN status = 3 THEN 'Incomplete orders'
                WHEN status = 4 THEN 'Completed'
                WHEN status = 5 THEN 'Revoked'
            END AS ordersreqtatus,DATE_FORMAT(created_at,'%d-%m-%Y %H:%i:%s') as created_at,DATE_FORMAT(updated_at,'%d-%m-%Y %H:%i:%s') as updated_at,
DATE_FORMAT(accepted_time,'%d-%m-%Y %H:%i:%s') as accepted_time,DATE_FORMAT(scan_start_time,'%d-%m-%Y %H:%i:%s') as scan_start_time,DATE_FORMAT(last_scan_time,'%d-%m-%Y %H:%i:%s') as last_scan_time,DATE_FORMAT(completed_time,'%d-%m-%Y %H:%i:%s') as completed_time  
FROM retaline_godown_boy_orders WHERE order_id = '{$_POST['orderId']}'   AND branch_id = {$_POST['current_branch_id']} ORDER BY created_at DESC";

        $orquery = "SELECT id,(SELECT name FROM retaline_godown_boy WHERE id = boy_id) as boy_name,order_id,status,CASE WHEN status = 1 THEN 'Request Sent'
                WHEN status = 2 THEN 'Accepted'
                 WHEN status = 3 THEN 'Rejected'
                WHEN status = 4 THEN 'Time Out'
            END AS ordersreqtatus,DATE_FORMAT(created_at,'%d-%m-%Y %H:%i:%s') as created_at,DATE_FORMAT(updated_at,'%d-%m-%Y %H:%i:%s') as updated_at,'-' AS accepted_time,'-' AS scan_start_time,'-' AS last_scan_time,'-' AS completed_time "
                . "FROM retaline_godown_boy_orders_request WHERE order_id = '{$_POST['orderId']}'   AND branch_id = {$_POST['current_branch_id']}";

        $datas = $db->getMulipleData($orquery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                if ($datas[$i]['status'] == 2) {
                    $boyQuery = "SELECT id,(SELECT name FROM retaline_godown_boy WHERE id = boy_id) as boy_name,order_id,status,CASE WHEN status = 1 THEN 'Accepted'
                WHEN status = 2 THEN 'Scanning started'
                 WHEN status = 3 THEN 'Incomplete orders'
                WHEN status = 4 THEN 'Completed'
                WHEN status = 5 THEN 'Revoked'
            END AS ordersreqtatus,DATE_FORMAT(created_at,'%d-%m-%Y %H:%i:%s') as created_at,DATE_FORMAT(updated_at,'%d-%m-%Y %H:%i:%s') as updated_at,
DATE_FORMAT(accepted_time,'%d-%m-%Y %H:%i:%s') as accepted_time,DATE_FORMAT(scan_start_time,'%d-%m-%Y %H:%i:%s') as scan_start_time,DATE_FORMAT(last_scan_time,'%d-%m-%Y %H:%i:%s') as last_scan_time,DATE_FORMAT(completed_time,'%d-%m-%Y %H:%i:%s') as completed_time  
FROM retaline_godown_boy_orders WHERE bgor_id = {$datas[$i]['id']} ";
                    $result = $db->getFromDB($boyQuery, true);
                    if ($result['status'] == 1) {

                        $datas['a' . $i]['id'] = $result['id'];
                        $datas['a' . $i]['boy_name'] = $result['boy_name'];
                        $datas['a' . $i]['order_id'] = $result['order_id'];
                        $datas['a' . $i]['ordersreqtatus'] = 'Accepted';
                        $datas['a' . $i]['created_at'] = $result['created_at'];
                        $datas['a' . $i]['updated_at'] = $result['updated_at'];
                        $datas['a' . $i]['accepted_time'] = $result['accepted_time'];
                    } else if ($result['status'] == 5) {
                        $datas['a' . $i]['id'] = $result['id'];
                        $datas['a' . $i]['boy_name'] = $result['boy_name'];
                        $datas['a' . $i]['order_id'] = $result['order_id'];
                        $datas['a' . $i]['ordersreqtatus'] = 'Accepted';
                        $datas['a' . $i]['created_at'] = $result['created_at'];
                        $datas['a' . $i]['updated_at'] = $result['updated_at'];
                        $datas['a' . $i]['accepted_time'] = $result['accepted_time'];

                        $datas['b' . $i]['id'] = $result['id'];
                        $datas['b' . $i]['boy_name'] = $result['boy_name'];
                        $datas['b' . $i]['order_id'] = $result['order_id'];
                        $datas['b' . $i]['ordersreqtatus'] = 'Revoked';
                        $datas['b' . $i]['created_at'] = $result['created_at'];
                        $datas['b' . $i]['updated_at'] = $result['updated_at'];
                        $datas['b' . $i]['accepted_time'] = $result['updated_at'];
                    }
                }
            }
            $count = count($datas);
            echo '{"totalCount":"', $count, '","data":' . json_encode(array_values($datas)) . '}';
            //echo json_encode($qry);
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';

        // $db->printGridJson($countQuery, $listQuery);
        break;
    case 'logoutGodownExecutive':
        $cfg_Value = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'PACKSURE_FORCELOGOUT'");
        $fields = array(
            "boy_id" => $_POST['id'],
            "phone" => $_POST['phone']
        );
        $url = str_replace('{mobno}', $_POST['phone'], $cfg_Value);

        $fields_string = json_encode($fields);
        // print_r($url);exit();
        $opts = array(
            CURLOPT_URL => $url,
            CURLINFO_CONTENT_TYPE => "application/json",
            CURLOPT_BINARYTRANSFER => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST => count($fields),
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        header("Content-Type: application/json");

        if ($data['success'] == true) {
            $rgb['is_offline'] = 1;
            $db->query('begin');
            $status = $db->perform('retaline_godown_boy', $rgb, 'update', "id = " . intval($_POST['id']));
            $status = $db->query('commit');
            if ($status == 1) {
                echo "{success:true,valid:true,message:'Logged out successfully.' }";
            } else {
                echo "{success:false,valid:false,message:'Server error' }";
            }
        } else {
            echo $data;
        }

        break;
    case 'getVendorName':
        $qry = $db->getMulipleData("SELECT stpa_id,stpa_Fname FROM finascop_stock_party WHERE stpa_IsVendor = 1 AND br_id = {$_SESSION['admin']->finascop_current_branch_id} ORDER BY stpa_Fname ASC", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
}

