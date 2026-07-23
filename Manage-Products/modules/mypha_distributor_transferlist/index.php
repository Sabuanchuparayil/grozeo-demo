<?php

require_once(INCLUDE_PATH . "/finascop_accounts_Master.php");
require_once(INCLUDE_PATH . "/finascop_common_functions.php");
global $db;
switch ($op) {
    case 'getPyramids':
        $qry = "SELECT  br_ID,br_Name,br_PyramidLevel from finascop_branch WHERE br_cpd = 2 "
                . "ORDER BY br_Name ASC ";
        $party = $db->getMultipleData($qry, true);

        echo '{"success":true,"data":' . json_encode($party) . '}';
        break;
    case 'getItems':
        $type = $_POST['type'];
        switch ($type) {
            case 1:
                $qry = "SELECT medicineMaster_id AS item_id, medicineMaster_name AS item_name "
                        . "FROM " . FINASCOP_DB . "mypha_medicineMaster ";
                ;
                break;
            case 2:
                $qry = "SELECT stit_ID AS item_id, stit_itemName AS item_name "
                        . "FROM " . FINASCOP_DB . "finascop_stock_itemmaster ";
                ;
                break;
        }
        $items = $db->getMultipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . count($items) . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'assignExecutive':

        $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'ASSIGNEXEC'");
        $fields = array(
            "is_cpd" => 1,
            "order_id" => $_POST['order_ID'],
            "boy_id" => $_POST['id'],
            "branch_id" => $_POST['br_ID']
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

        $countQuery = "SELECT COUNT(*) from retaline_godown_boy where {$filter_part} AND has_open_orders = 0 AND branch_id={$cpd_ID}";
        $listQuery = "SELECT id,name,phone,has_open_orders from retaline_godown_boy WHERE {$filter_part}  AND has_open_orders = 0 AND branch_id={$cpd_ID} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
// outward  orders of current cpd, to assign an executive the order should be manually queued
    case 'listScheduleOrder':
        //$cpd_id = $_POST['cpd_id'];
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
                                } else {
                                    $searchitem .= " and (order_status=11) ";
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

            $listQuery = "SELECT order_id,order_no,order_no_last_id,cpo.cpd_id,DATE_FORMAT(bcor_createdon, '%d-%m-%Y') AS bcor_createdon,branch_id,
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
        $rec_sort = empty($data['sort']) ? 'name' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(*) from retaline_godown_boy WHERE {$filter_part}";
        $listQuery = "SELECT id,name,phone,branch_id AS cpdid,is_cpd,IF((is_cpd=1),'CPD','Branch') AS is_cpd,
  (SELECT br_Name FROM finascop_branch cp WHERE br_ID = cpdid)  AS cpd_name
                    from retaline_godown_boy gb WHERE {$filter_part}  ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
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
        if ($_POST['branch_name'] == '') {
            $branchname = $_POST['cpdname'];
        }
        $data = array(
            "name" => $_POST['name'],
            "phone" => $_POST['phone'],
            "branch_id" => $branchname,
            "is_cpd" => $db->getItemFromDB("SELECT br_IsCPD FROM finascop_branch WHERE br_ID = {$branchname}"),
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
            $qry = $db->getFromDB("SELECT name,phone,branch_id as branch_name,is_cpd,
(SELECT br_Name FROM finascop_branch  WHERE br_ID = branch_id) AS cpd_branch
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
        $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch", true);


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
        /*
          $url = "https://stgapi.billavenue.com/billpay/extMdmCntrl/mdmRequest/xml";
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
          $result = curl_exec($ch);
         */
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
        $fields = array(
            "is_cpd" => 1,
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
}

