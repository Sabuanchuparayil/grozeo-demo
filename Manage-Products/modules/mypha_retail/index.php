<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");
require_once(INCLUDE_PATH . "/finascop_accounts_Master.php");
require_once(INCLUDE_PATH . "/brmClass.php");

switch ($op) {
    case 'changeStatus':

        $branch = new \finascop\accounts\Master\brmBranch();
        $branch->changeStatus($_POST['br_ID'], $_POST['br_status'], $_POST['comp_id']);

        break;

    case 'getDetails':
        $branch = new \finascop\accounts\Master\brmBranch();
        $branch->getDetails($_POST['id']);
        break;

    case 'listRetailStores':

        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 16 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'br_Name' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }

        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . "finascop_branch WHERE br_PyramidLevel= 4";
        $listQuery = "SELECT br_ID,br_Name,CONCAT(branch_shortname,'-',br_ID) AS branch_shortname,br_csdefault,br_cpd,br_ReferenceID,br_rdrIdSlotted,"
                . "(SELECT dst_Name FROM " . FINASCOP_DB . "finascop_district WHERE dst_Id = br_District) as br_District,br_StoreType,"
                . "(SELECT st_name FROM " . FINASCOP_DB . "finascop_state WHERE st_ID = br_State) as br_State,"
                . "(SELECT comp_name from " . FINASCOP_DB . "finascop_company WHERE comp_id = "
                . "(SELECT comp_id from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id = a.br_ID)) as company,"
                . "(SELECT comp_id from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id = a.br_ID) as comp_id,"
                . "br_Address,br_Fax,br_Email,br_Phone,br_Incharge,br_status,br_pincode,br_Lat,br_Lng,if(br_PyramidLevel <> 1,(SELECT br_Name FROM finascop_branch WHERE br_ID = a.br_cpd),' ') AS  branchCpd,br_deliveryMode "
                . "from " . FINASCOP_DB . "finascop_branch a WHERE br_PyramidLevel= 4 AND  {$filter_part} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        // $db->printGridJson($countQuery, $listQuery);
        $datas = $db->getMulipleData($listQuery, true);
        // print_r($datas);
        $count = $db->getItemFromDB($countQuery);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                switch ($datas[$i]['br_csdefault']) {
                    case 'Owned':
                        $datas[$i]['br_csdefault'] = 'Spoke';
                        break;
                    case 'Leased':
                        $datas[$i]['br_csdefault'] = 'Franchise';
                        break;
                    case 'Dealer':
                        $datas[$i]['br_csdefault'] = 'Store';
                        break;
                }
                if ($datas[$i]['br_csdefault'] == 1) {
                    $br_defDistributor = $db->getFromDB("SELECT br_ID,br_Name FROM finascop_branch WHERE br_ID = {$datas[$i]['br_cpd']} ", true);
                    $br_defCS = $db->getItemFromDB("SELECT br_cpd FROM finascop_branch WHERE br_ID = {$br_defDistributor['br_ID']}", true);
                    $br_defCentralStore = $db->getFromDB("SELECT br_ID,br_Name FROM finascop_branch WHERE br_ID = {$br_defCS} ", true);
                    $datas[$i]['br_defCS'] = $br_defCentralStore['br_Name'];
                    $datas[$i]['br_defDistributor'] = $br_defDistributor['br_Name'];
                } else {
                    $datas[$i]['br_defCS'] = '-';
                    $datas[$i]['br_defDistributor'] = '-';
                }
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else {
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        }

        break;
    /* $data = $_POST;
      $branch = new \finascop\accounts\Master\brmBranch();
      $branch->listRetailStores($data);

      break; */


    case 'listRetailtypeStores':

        $listQuery = "SELECT mprt_id, mprt_Type FROM mypha_retailer_types";

        $data = $db->getMultipleData($listQuery, true);
        echo json_encode($data);

        break;

    case 'getComboStore':
        $defaultCountry = $db->getItemFromDB("SELECT country_id FROM retaline_country WHERE is_default = 1");
        $ind = $_GET['ind'];
        $state = $_POST['state'];
        $query = $_POST['query'];
        switch ($ind) {
            case 1:
                $cond = (!empty($query) ? " AND st_name LIKE '{$query}%' " : " ");
                $qry = "SELECT st_ID AS id, st_name AS `name` FROM " . FINASCOP_DB . "finascop_state WHERE cnt_ID = {$defaultCountry} {$cond}";
                break;
            case 2:
                $cond = (!empty($query) ? " AND dst_Name LIKE '{$query}%' " : " ");
                $qry = "SELECT dst_Id AS id, dst_Name AS `name` FROM " . FINASCOP_DB . "finascop_district WHERE st_Id = {$state} {$cond}";
                break;
            case 3:
                $qry = "SELECT comp_id AS id, comp_name AS `name` from " . FINASCOP_DB . "finascop_company where cmp_status= 'Active' ";
                break;
            case 4:
                $qry = "SELECT br_ID AS id, br_Name AS `name` from " . FINASCOP_DB . "finascop_branch where br_status= 'Active' AND br_PyramidLevel = 3";
                break;
            case 5:
                $qry = "SELECT store_group_id AS id, store_group_name AS `name` from " . FINASCOP_DB . "finascop_branch_group where status= 1 ";
                break;
        }
        $qry .= " ORDER BY `name` ASC";

        finascop_getjsonkeyarray($qry);

        break;
        $branch = new \finascop\accounts\Master\brmBranch();
        //print_r($_GET);
        $branch->getComboStore($_GET['ind'], $_POST['state']);

        break;

    case 'saveRetailStore':

        global $db;
        $db->query('begin');
        $data = $_POST;
        //print_r($data);
        unset($data['temp']);
        //echo $dat;
        if ($data['br_ID'] > 0 && ($data['br_StoreType'] != 'Dealer')) {
            $br_cpd = $db->getItemFromDB("SELECT br_cpd FROM finascop_branch WHERE br_ID = {$data['br_ID']}");
            if ($br_cpd != $data['br_cpd']) {
                echo "{success: false, errors:  'You are not allowed to change Distributor.' }";
                exit();
            }
        }
        $branch = new \finascop\accounts\Master\brmBranch();
        $status = $branch->saveBranch($data, ($data['br_ID'] > 0 ? false : true), false);
        if ($status) {
            echo "{success: true}";
            $db->query('commit');
        } else {
            echo "{success: false, errors:  'FINASCOP: Error occured while saving data' }";
        }

        break;
    case 'renewBranchAPIKey':
        $data = $_POST;
        $update['br_ReferenceID'] = getNewFinascopApiKey();
        $status = $db->perform("finascop_branch", $update, "update", "br_ID={$data['br_ID']}");
        if ($status) {
            echo "{success: true, newBrAPIKey :'" . $update['br_ReferenceID'] . "'}";
        } else {
            echo "{success: false, errors:  'FINASCOP: Error occured while renewing API Key' }";
        }
        break;
    case 'getBranchName':
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch WHERE br_status = 'Active' AND br_StoreType <> 'Dealer' AND br_PyramidLevel = 4", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'storeForDeliveryRule':
        $type = $_POST['deliveryMode'];
        $branchId = $_POST['branchId'];
        if ($branchId > 0) {
            $qry = "select rdr_id as id,rdr_ruleName as name from retaline_delivery_rules where rdr_ruleFor = 3 AND rdr_ruleForId = {$branchId} AND rdr_deliveryMode = {$type} order by rdr_ruleName";
            $data = $db->getMultipleData($qry, true);
            if (empty($data)) {
                $branchSGId = $db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$branchId}");
                $qry = "select rdr_id as id,rdr_ruleName as name from retaline_delivery_rules where rdr_ruleFor = 2 AND rdr_ruleForId = {$branchSGId} AND rdr_deliveryMode = {$type} order by rdr_ruleName";
                $data = $db->getMultipleData($qry, true);
                if (empty($data)) {
                    $qry = "select rdr_id as id,rdr_ruleName as name from retaline_delivery_rules where rdr_ruleFor = 1 AND rdr_deliveryMode = {$type} order by rdr_ruleName";
                }
            }
        } else {
            $qry = "select rdr_id as id,rdr_ruleName as name from retaline_delivery_rules where is_default = 1 AND rdr_ruleFor = 1 AND rdr_deliveryMode = {$type} order by rdr_ruleName";
        }

        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo json_encode($data);
        } else
            echo [];
        break;
    case 'setBranchDeliveryRule':
        $br_ID = isset($_POST['br_ID']) ? intval($_POST['br_ID']) : 0;
        $data['br_rdrIdCourier'] = $_POST['br_rdrIdCourier'];
        $data['br_rdrIdSlotted'] = $_POST['br_rdrIdSlotted'];
        $data['br_rdrIdExpress'] = $_POST['br_rdrIdExpress'];
        $status = $db->perform("finascop_branch", $data, "update", "br_ID={$br_ID}");
        if ($status) {
            echo "{success: true,msg:'Delivery rule updated. '}";
        } else {
            echo "{success: false, errors:  'FINASCOP: Error occured while updating' }";
        }
        break;
    case 'loadBranchDeliveryRules':
        $br_ID = isset($_POST['br_ID']) ? intval($_POST['br_ID']) : 0;
        if ($br_ID) {
            _loadRecordJson("SELECT  br_rdrIdCourier,br_rdrIdSlotted,br_rdrIdExpress FROM finascop_branch WHERE br_ID = {$br_ID}");
        }
        break;
}




