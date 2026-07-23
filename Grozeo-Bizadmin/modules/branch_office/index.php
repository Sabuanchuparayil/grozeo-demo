<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");
require_once(INCLUDE_PATH . "/finascop_accounts_Master.php");
require_once(INCLUDE_PATH . "/brmClass.php");

switch ($op) {
    case 'changeStatus':


        $br_ID = $_POST['id'];
        $br_status = $_POST['bostatus'];
        $comp_id = $_POST['comp_id'];
        $st = ($br_status == 'Active') ? 'Inactive' : 'Active';
        $data = array('boStatus' => $st);
        $data['boUpdatedOn'] = date('Y-m-d H:i:s');
        $data['boUpdatedBy'] = $_SESSION['admin']->UserId;
        $db->query('begin');
        $status = $db->perform("branch_office", $data, "update", "id={$brid}");
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true}";
        } else
            echo "{errors: { reason: 'Error occured while saving data' }}";
        break;

    case 'getDetails':
        $id = $_POST['id'];
        $db->_loadRecordJson("SELECT * FROM  branch_office WHERE id = " . $id, true);
        break;

    case 'listBranchOffices':

        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 16 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'boName' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['bo_id', 'boState', 'boDistrict', 'company', 'bo_address', 'bo_pincode', 'bo_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }

        $countQuery = "SELECT COUNT(1) from branch_office WHERE  {$filter_part}";
        $listQuery = "SELECT id,boName,boShortCode,"
            . "(SELECT dst_Name FROM finascop_district WHERE dst_Id = boDistrict) as boDistrict,"
            . "(SELECT st_name FROM finascop_state WHERE st_ID = boState) as boState,"
            . "(SELECT comp_name from finascop_company WHERE comp_id = boCompany) as company,"
            . "boCompany,boAddress,boMobile,boEmail,boContactNo,boIncharge,IF(bostatus = 1,'Active','Inactive') AS bostatus,
            bopincode,boLat,boLng "
            . "from branch_office a WHERE {$filter_part} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        //                   echo $listQuery;
        //                    exit;echo
        //$db->printGridJson($countQuery, $listQuery);
        $datas = $db->getMulipleData($listQuery, true);
        $count = $db->getItemFromDB($countQuery);
        // print_r($datas);
        $resCount = count($datas);
        if (!empty($datas)) {

            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else {
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        }

        break;

    case 'getComboStore':

        $branch = new \finascop\accounts\Master\brmBranch();
        //print_r($_GET);
        $branch->getComboStore($_GET['ind'], $_POST['state']);

        break;

    case 'saveBranchOffice':
        global $db;
        $db->query('begin');
        $data = $_POST;
        $id = $data['id'];
        unset($data['apikey']);
        unset($data['tstamp']);
        if ($id > 0) {
            $data['boUpdatedOn'] = date('Y-m-d H:i:s');
            $data['boUpdatedBy'] = $_SESSION['admin']->UserId;
            $isbounique = $db->getItemFromDB("SELECT COUNT(*) from branch_office WHERE boShortCode ='{$data['boShortCode']}' AND id <> {$id} ");
            if ($manufactureUnique > 0) {
                echo "{success: false, message:'This code already exists.'}";
                exit;
            } else {
                $data = array_filter($data);
                $status = $db->perform('branch_office', $data, 'update', " id = {$id}");
            }
        } else {
            $data['boCreatedOn'] = date('Y-m-d H:i:s');
            $data['boCreatedBy'] = $_SESSION['admin']->UserId;
            $isbounique = $db->getItemFromDB("SELECT COUNT(*) from branch_office WHERE boShortCode ='{$data['boShortCode']}' ");
            if ($isbounique > 0) {
                echo "{success: false, message:'This code already exists.'}";
                exit;
            } else {
                $data = array_filter($data);
                $status = $db->perform('branch_office', $data);
                $lastId = $db->insert_id();
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true}";
        } else {
            echo "{success: false, errors:  'Error occured while saving data' }";
        }

        break;

    case 'loadStateCombo':


        $qry = "SELECT st_ID, st_name FROM finascop_state ";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'listDistrict':
        $st_ID = $_POST['st_ID'];
        if ($st_ID) {
            $countQuery = "SELECT COUNT(*) FROM finascop_district WHERE st_ID={$st_ID} ";
            $listQuery = "SELECT dst_Id,dst_Name FROM finascop_district  WHERE st_ID={$st_ID} ";
            $db->printGridJson($countQuery, $listQuery);
        }
        break;
}
