<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");
require_once(INCLUDE_PATH . "/finascop_accounts_Master.php");
require_once(INCLUDE_PATH . "/brmClass.php");
function _exportExcelReport($data)
{


    global $db;
    require_once INCLUDE_PATH . '/simpleExcelWriter.php';

    $query = $_SESSION['Export']['Query'];

    $heads = json_decode(stripslashes($data['headers']), true);
    $fields = json_decode(stripslashes($data['dataindexes']), true);
    $excel = new simpleExcelWriter($db);
    $time = date('YmdHis');
    if (!empty($data['caller_Name'])) {
        $excel->exportFile = $data['caller_Name'] . $time . '.xls';
    } else {
        $excel->exportFile = $_SESSION['Export']['Settings']['title'] . $time . '.xls';
    }

    $excel->totalFields = (isset($_SESSION['Export']['Settings']['totalFields'])) ? $_SESSION['Export']['Settings']['totalFields'] : false;
    $excel->export($query, $heads, $fields);
    exit();
}
function RetailStoreReportGridData($postvar)
{
    global $db;
    $data = $postvar;
    $limit = $postvar['limit'];
    $start = $postvar['start'];
    $limit = is_numeric($limit) ? $limit : 22;
    $start = is_numeric($start) ? $start : 0;
    $rec_limit = is_numeric($limit) ? $limit : 22;;
    $rec_start = is_numeric($start) ? $start : 0;
    $rec_sort = empty($postvar['sort']) ? 'br_Name' : $postvar['sort'];
    $rec_sort_dir = empty($postvar['dir']) ? 'ASC' : $postvar['dir'];
    $filter_part = ' 1=1';

    if ($rec_sort == 'brdate') {
        $rec_sort = 'br_ID';
    }
    if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
                    $dmIds = implode(',', $dmIds);
                    $filter_part .= " and ({$val['field']} IN({$dmIds})) ";
                } else {

                    switch ($val['data']['value']) {
                        case 'Spoke':
                            $val['data']['value'] = 'Owned';
                            break;
                        case 'Franchise':
                            $val['data']['value'] = 'Leased';
                            break;
                        case 'Store':
                            $val['data']['value'] = 'Dealer';
                            break;
                    }
                    $filter_part .= " and " . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
                }
            } else if ($val['field'] == 'br_type') {
                if (substr($val['data']['value'], 0, 1) === 'S' || substr($val['data']['value'], 0, 1) === 's') {
                    $filter_part .= " and br_type = 1 ";
                } else {
                    $filter_part .= " and br_type = 0 ";
                }
            } else if ($val['field'] == 'br_storeGroupName') {
                $storeGroupIds = $db->getItemFromDB("SELECT GROUP_CONCAT(store_group_id) FROM finascop_branch_group WHERE store_group_name LIKE '{$val['data']['value']}%'");
                $filter_part .= " and br_storeGroup IN ({$storeGroupIds}) ";
            } else if ($val['field'] == 'brdate') {

                switch ($val['data']['comparison']) {
                    case 'gt':
                        $filter_part .= " and DATE_FORMAT(br_createdOn,'%Y-%m-%d') > '" . date('Y-m-d', strtotime($val['data']['value'])) . "'";
                        break;
                    case 'lt':
                        $filter_part .= " and DATE_FORMAT(br_createdOn,'%Y-%m-%d') < '" . date('Y-m-d', strtotime($val['data']['value'])) . "'";
                        break;
                    case 'eq':
                        $filter_part .= " and DATE_FORMAT(br_createdOn,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($val['data']['value'])) . "'";
                        break;
                    default:
                        $filter_part .= " and DATE_FORMAT(br_createdOn,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($val['data']['value'])) . "'";
                        break;
                }
            } else {
                $filter_part .= " and " . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
            }
        }
    }
    $countQuery = "SELECT COUNT(1) from finascop_branch a WHERE br_PyramidLevel= 4 AND  {$filter_part}";
    $listQuery = "SELECT br_ID,br_Name,branch_shortname,br_csdefault,br_cpd,br_ReferenceID,br_rdrIdSlotted,(SELECT dst_Name FROM finascop_district WHERE dst_Id = br_District) as br_District,"
        . " CASE WHEN br_StoreType = 'Owned' THEN 'Spoke'"
        . " WHEN br_StoreType = 'Leased' THEN 'Franchise'"
        . " WHEN br_StoreType = 'Dealer' THEN 'Store' "
        . " END AS br_StoreType,br_sdId,br_pgchargeId,br_schedulePackiing,br_SalesOffline,br_SalesOnline,CASE WHEN br_type = 1 THEN 'Satellite' ELSE 'Retail' END AS br_type,br_storeGroup,
            (SELECT concat(pgChargeName,'-',pgChargePercentage) FROM pgcharge_master WHERE pgChargeId =br_pgchargeId) AS br_pgCharge,
            (SELECT concat(sdName,'-',sdDays) FROM settlementDays_master WHERE sdId =br_sdId) AS br_sd,"
        . "(SELECT st_name FROM finascop_state WHERE st_ID = br_State) as br_State,DATE_FORMAT(br_createdOn,'%d-%m-%Y') AS brdate, DATE_FORMAT(br_createdOn,'%H-%i-%s') AS brtime,"
        . "(SELECT comp_name from finascop_company WHERE comp_id = "
        . "(SELECT comp_id from finascop_branch_company WHERE br_Id = a.br_ID)) as company,"
        . "(SELECT comp_id from finascop_branch_company WHERE br_Id = a.br_ID) as comp_id,br_SalesOnline,"
        . "br_Address,br_Fax,br_Email,br_Phone,br_Incharge,br_status,br_pincode,br_Lat,br_Lng,if(br_PyramidLevel <> 1,(SELECT br_Name FROM finascop_branch WHERE br_ID = a.br_cpd),' ') AS  branchCpd,br_deliveryMode,
        (SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = a.br_storeGroup) AS br_storeGroupName,
        br_GST,areaId,IF(areaId > 0,(SELECT areaName FROM area_entries WHERE id = areaId),'') AS areaName "
        . "from finascop_branch a WHERE br_PyramidLevel= 4 AND  {$filter_part} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";

    return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
}
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

        $qry = RetailStoreReportGridData($_POST);
        $datas = $db->getMulipleData($qry['listQuery'], true);
        $count = $db->getItemFromDB($qry['countQuery']);
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
                //$datas[$i]['br_storeGroupName'] = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = {$datas[$i]['br_storeGroup']}");
                //if ($datas[$i]['br_csdefault'] == 1) {
                if ($datas[$i]['br_cpd'] > 0) {
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
        if ($data['br_scheduledDelivery'] == true) {
            $data['br_scheduledDelivery'] = 1;
        } else {
            $data['br_scheduledDelivery'] = 0;
        }
        if ($data['br_ownInvoice'] == true) {
            $data['br_ownInvoice'] = 1;
        } else {
            $data['br_ownInvoice'] = 0;
        }
        if ($data['br_directDelivery'] == true) {
            $data['br_directDelivery'] = 1;
        } else {
            $data['br_directDelivery'] = 0;
        }
        if ($data['br_courierDelivery'] == true) {
            $data['br_courierDelivery'] = 1;
        } else {
            $data['br_courierDelivery'] = 0;
        }
        if ($data['br_SalesOnline'] == true) {
            $data['br_SalesOnline'] = 1;
        } else {
            $data['br_SalesOnline'] = 0;
        }
        if ($data['br_SalesOffline'] == true) {
            $data['br_SalesOffline'] = 1;
        } else {
            $data['br_SalesOffline'] = 0;
        }
        if ($data['br_type'] == true) {
            $data['br_type'] = 1;
        } else {
            $data['br_type'] = 0;
            $data['br_typeParent'] = 0;
        }
        if ($data['br_parentPacking'] == true) {
            $data['br_parentPacking'] = 1;
        } else {
            $data['br_parentPacking'] = 0;
        }
        if (!empty($data['br_open_time'])) {
            $data['br_open_time'] = DATE("H:i", STRTOTIME($data['br_open_time']));
        } else {
            unset($data['br_open_time']);
        }
        if (!empty($data['br_close_time'])) {
            $data['br_close_time'] = DATE("H:i", STRTOTIME($data['br_close_time']));
        } else {
            unset($data['br_close_time']);
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
            $qry = "select rdr_id as id,rdr_ruleName as name from retaline_delivery_rules where rdr_deliveryMode = {$type} order by rdr_ruleName";
            $data = $db->getMultipleData($qry, true);
            /*$qry = "select rdr_id as id,rdr_ruleName as name from retaline_delivery_rules where rdr_ruleFor = 3 AND rdr_ruleForId = {$branchId} AND rdr_deliveryMode = {$type} order by rdr_ruleName";
            $data = $db->getMultipleData($qry, true);
            if (empty($data)) {
                $branchSGId = $db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$branchId}");
                $qry = "select rdr_id as id,rdr_ruleName as name from retaline_delivery_rules where rdr_ruleFor = 2 AND rdr_ruleForId = {$branchSGId} AND rdr_deliveryMode = {$type} order by rdr_ruleName";
                $data = $db->getMultipleData($qry, true);
                if (empty($data)) {
                    $qry = "select rdr_id as id,rdr_ruleName as name from retaline_delivery_rules where rdr_ruleFor = 1 AND rdr_deliveryMode = {$type} order by rdr_ruleName";
                }
            }*/
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
        $data['br_rdrIdCourier'] = ($_POST['br_rdrIdCourier'] > 0) ? $_POST['br_rdrIdCourier'] : 0;
        $data['br_rdrIdSlotted'] = ($_POST['br_rdrIdSlotted'] > 0) ? $_POST['br_rdrIdSlotted'] : 0;
        $data['br_rdrIdExpress'] = ($_POST['br_rdrIdExpress'] > 0) ? $_POST['br_rdrIdExpress'] : 0;
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
    case 'storeTypeParentBranch':
        $qry = "SELECT br_ID,br_Name FROM finascop_branch WHERE br_status = 'Active' AND br_typeParent <> 1";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo json_encode($data);
        } else
            echo [];
        break;
    case 'setBranchOnOFF':
        $br_ID = isset($_POST['br_ID']) ? intval($_POST['br_ID']) : 0;
        if ($_POST['br_SalesOnline'] == 1) {
            $msg = "Branch is Offline now.";
            $data['br_SalesOnline'] = 0;
        } else {
            $msg = "Branch is Online now.";
            $data['br_SalesOnline'] = 1;
        }

        $status = $db->perform("finascop_branch", $data, "update", "br_ID={$br_ID}");
        if ($status) {
            echo "{success: true,msg:'" . $msg . "'}";
        } else {
            echo "{success: false, errors:  'Error occured while updating' }";
        }
        break;
    case 'saveBranchTimings':
        $rpgtr['branch_id'] = $_POST['branch_id'];
        $rpgtr['br_open_time'] = DATE("H:i", STRTOTIME($_POST['br_open_time']));
        $rpgtr['br_close_time'] = DATE("H:i", STRTOTIME($_POST['br_close_time']));
        $count = $db->getItemSafe("SELECT COUNT(*) FROM branch_timings WHERE branch_id = ? and  br_open_time = '{$rpgtr['br_open_time']}'", "i", [$_POST['branch_id']]);
        $db->query('begin');
        if ($count == 0) {
            $rpgtr['createdOn'] = date('Y-m-d H:i:s');
            $rpgtr['createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('branch_timings', $rpgtr);
        } else {
            echo "{success: false,msg:'Time slot already added'}";
            exit();
        }
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Saved Successfully.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'deleteBranchTimeSlot':
        $db->query('begin');
        $del_query = "DELETE FROM branch_timings WHERE id = '{$_POST['id']}'";
        $temp = $db->query($del_query);
        $status = $db->query('commit');
        if (status) {
            echo "{success:true,valid:true,message:'Deleted Succesfully ' }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Deleteing.'}";
        }
        break;
    case 'listBranchTimingsStore':
        $branch_id = $_POST['branch_id'];
        $recSort = $_POST['sort'];
        $recSortDir = $_POST['dir'];
        $filterCon = " WHERE 1=1 ";

        if (isset($_POST['filter']) && $_POST['filter'] != '') {
            foreach ($_POST['filter'] as $key => $v) {
                if (array_key_exists($v['field'], $fields))
                    $field = $fields[$v['field']];
                else {
                    $field = $v['field'];
                }
                switch ($v['data']['type']) {
                    case 'string':

                        $filterCon .= (($filterCon == "") ? " where " : " and ") . $field . " like '" . $v['data']['value'] . "%'";

                        break;
                }
            }
        }

        $qry = "select count(*) from " . FINASCOP_DB . "branch_timings {$filterCon} AND branch_id = {$branch_id}";

        $totalCount = $db->getItemFromDB($qry);
        $prefix = FINASCOP_DB;
        $prefix1 = FINASCOP_DB . 'config';
        $db->query('set @cnt=0');
        $query = "select * from  branch_timings rpgtr $filterCon AND branch_id = {$branch_id} order by $recSort $recSortDir ";
        $data = $db->getMultipleData($query, true);

        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        break;
    case 'storeForpgCharge':

        $qry = "select pgChargeId,CONCAT(pgChargeName,'-',pgChargePercentage) AS pgChargeName from pgcharge_master where  pgChargeStatus = 1  order by pgChargeName";


        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo json_encode($data);
        } else
            echo [];
        break;
    case 'updatePGCharge':
        $br_ID = $_POST['br_ID'];
        $newbr_pgchargeId = $_POST['newbr_pgchargeId'];

        $br_ID = isset($_POST['br_ID']) ? intval($_POST['br_ID']) : 0;
        $data['br_pgchargeId'] = $newbr_pgchargeId;
        $db->query('begin');
        $status = $db->perform("finascop_branch", $data, "update", "br_ID={$br_ID}");
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'PG Charge updated. '}";
        } else {
            echo "{success: false, errors:  'FINASCOP: Error occured while updating' }";
        }
        break;
    case 'updateSettlementDays':
        $br_ID = $_POST['br_ID'];
        $newbr_sdId = $_POST['newbr_sdId'];

        $br_ID = isset($_POST['br_ID']) ? intval($_POST['br_ID']) : 0;
        $data['br_sdId'] = $newbr_sdId;
        $db->query('begin');
        $status = $db->perform("finascop_branch", $data, "update", "br_ID={$br_ID}");
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Settlement Days updated. '}";
        } else {
            echo "{success: false, errors:  'FINASCOP: Error occured while updating' }";
        }
        break;
    case 'storeForsdDays':
        $qry = "select sdId,CONCAT(sdName,'-',sdDays) AS sdName from settlementDays_master where  sdStatus = 1  order by sdName";


        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo json_encode($data);
        } else
            echo [];
        break;
    case 'listBranchSalesOrders':
        $branch_id = $_POST['branch_id'];
        $recSort = $_POST['sort'];
        $recSortDir = $_POST['dir'];
        $filterCon = " WHERE 1=1 ";

        if (isset($_POST['filter']) && $_POST['filter'] != '') {
            foreach ($_POST['filter'] as $key => $v) {
                if (array_key_exists($v['field'], $fields))
                    $field = $fields[$v['field']];
                else {
                    $field = $v['field'];
                }
                switch ($v['data']['type']) {
                    case 'string':

                        $filterCon .= (($filterCon == "") ? " where " : " and ") . $field . " like '" . $v['data']['value'] . "%'";

                        break;
                }
            }
        }

        $fromDate = date('Y-m-d', strtotime($_POST['orderFromDate']));
        $toDate = date('Y-m-d', strtotime($_POST['orderToDate']));
        $qry = "select count(*) from  retaline_customer_order {$filterCon} AND order_branch_id = {$branch_id} AND (order_confirm_date BETWEEN '{$fromDate}' AND '{$toDate}')  GROUP BY (order_confirm_date)";

        $totalCount = $db->getItemFromDB($qry);

        $query = "SELECT order_confirm_date,COUNT(*) AS orderCount,SUM(total) AS orderAmount,
        (SELECT COUNT(*) FROM retaline_customer_order b WHERE status_id < 9 AND b.order_confirm_date = a.order_confirm_date) AS packedCount,
        (SELECT COUNT(*) FROM retaline_customer_order b WHERE status_id = 9 AND b.order_confirm_date = a.order_confirm_date) AS pendingCount,
        (SELECT COUNT(*) FROM retaline_customer_order b WHERE status_id IN (10,11,12,13,14,15,16) AND b.order_confirm_date = a.order_confirm_date) AS intransitCount,
        (SELECT COUNT(*) FROM retaline_customer_order b WHERE status_id = 18 AND b.order_confirm_date = a.order_confirm_date) AS deliveredCount,
        (SELECT COUNT(*) FROM retaline_customer_order b WHERE status_id = 19 AND b.order_confirm_date = a.order_confirm_date) AS cancelledCount   
        FROM  retaline_customer_order a {$filterCon} AND order_branch_id = {$branch_id} AND (order_confirm_date BETWEEN '{$fromDate}' AND '{$toDate}')  GROUP BY (order_confirm_date) ";
        $data = $db->getMultipleData($query, true);
        if (!empty($data)) {
            echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":' . json_encode($data) . '}';
        }
        break;
    case 'loadSearchData':
        $branch_id = $_POST['branch_id'];
        $fromDate = date('Y-m-d', strtotime($_POST['orderFromDate']));
        $toDate = date('Y-m-d', strtotime($_POST['orderToDate']));

        $query = "WITH o AS(
    SELECT * FROM  retaline_customer_order a WHERE order_branch_id = {$branch_id} AND (a.order_confirm_date BETWEEN '{$fromDate}' AND '{$toDate}')
    )
    SELECT 
    (SELECT COUNT(*) FROM o) AS orderCount, 
    (SELECT SUM(total) FROM o) AS orderAmount,
    (SELECT COUNT(*) FROM o WHERE status_id < 9) AS packedCount,
    (SELECT COUNT(*) FROM o WHERE status_id = 9) AS pendingCount,
    (SELECT COUNT(*) FROM o WHERE status_id IN (10,11,12,13,14,15,16)) AS intransitCount,
    (SELECT COUNT(*) FROM o WHERE status_id = 18) AS deliveredCount,
    (SELECT COUNT(*) FROM o WHERE status_id = 19) AS cancelledCount";
        $data = $db->getFromDB($query, true);
        if (!empty($data)) {
            $data['success'] = true;
            echo json_encode($data);
        } else {
            $data['success'] = false;
            echo [];
        }

        break;
    case 'retailStoreExportexcel':
        $lastParameters = json_decode(stripslashes(($_POST["filterData"])), true);

        for ($i = 0; $i <= $i; $i++) {
            if (array_key_exists('filter[' . $i . '][field]', $lastParameters)) {
                $filterParams[$i]['field'] = $lastParameters['filter[' . $i . '][field]'];
                unset($lastParameters['filter[' . $i . '][field]']);
                $filterParams[$i]['data']['type'] = $lastParameters['filter[' . $i . '][data][type]'];
                unset($lastParameters['filter[' . $i . '][data][type]']);
                $filterParams[$i]['data']['value'] = $lastParameters['filter[' . $i . '][data][value]'];
                unset($lastParameters['filter[' . $i . '][data][value]']);
                $filterParams[$i]['data']['comparison'] = $lastParameters['filter[' . $i . '][data][comparison]'];
                unset($lastParameters['filter[' . $i . '][data][comparison]']);
            } else {
                break;
            }
        }
        $_POST['filter'] = $filterParams;

        //$filterdata = json_decode($_POST['filterData'],true);
        //array_push($_POST,$filterdata) ;
        foreach ($lastParameters as $keys => $values) {
            $_POST[$keys] = $values;
        }
        $_POST['start'] = 0;
        $_POST['limit'] = 100000;
        $qry = RetailStoreReportGridData($_POST);
        //print_r($qry['listQuery']);
        $_SESSION['Export']['Query'] = $qry['listQuery'];
        $_SESSION['Export']['Settings']['title'] = "stores_";
        _exportExcelReport($_POST);
        break;
    case 'listAreandRos':
        $branch_id = $_POST['branch_id'];
        $recSort = $_POST['sort'];
        $recSortDir = $_POST['dir'];
        $filterCon = " WHERE 1=1 ";
        if (!empty($_POST['branchArea'])) {
            $filterCon .= " AND areaName LIKE '%{$_POST['branchArea']}%' ";
        }

        if (isset($_POST['filter']) && $_POST['filter'] != '') {
            foreach ($_POST['filter'] as $key => $v) {
                if (array_key_exists($v['field'], $fields))
                    $field = $fields[$v['field']];
                else {
                    $field = $v['field'];
                }
                switch ($v['data']['type']) {
                    case 'string':

                        $filterCon .= (($filterCon == "") ? " where " : " and ") . $field . " like '" . $v['data']['value'] . "%'";

                        break;
                }
            }
        }

        $branchLocation = $db->getFromDB("SELECT br_Lat,br_Lng FROM finascop_branch WHERE br_ID = {$branch_id}",true);
        $qry = "select count(*) FROM `area_entries` ae 
LEFT JOIN `business_associate` ba ON ba.id = areaBusinessAssociate 
LEFT JOIN `relationship_officer` ro ON roArea = ae.id {$filterCon}";

        $totalCount = $db->getItemFromDB($qry);

        $db->query('set @cnt=0');
        $query = "SELECT ae.id AS areaId,`areaName`,`baName`,ro.id AS roId,roName,roMobile,
        calcDistance({$branchLocation['br_Lat']}, {$branchLocation['br_Lng']}, areaLatitude, areaLongitude) AS distance FROM `area_entries` ae 
LEFT JOIN `business_associate` ba ON ba.id = areaBusinessAssociate 
LEFT JOIN `relationship_officer` ro ON roArea = ae.id {$filterCon} HAVING distance <=50 order by $recSort $recSortDir ";
        $data = $db->getMultipleData($query, true);

        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        break;
    case 'assignAreatoStore':
        $br_ID = $_POST['br_ID'];
        $areaId = $_POST['areaId'];
        $roId = $_POST['roId'];
        if ($areaId > 0)
            $data['areaId'] = $areaId;
        if ($roId > 0)
            $data['roId'] = $roId;
        $data['updatedOn'] = date("Y-m-d H:i:s");
        $data['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('finascop_branch', $data, "update", " br_ID = {$br_ID}");
        if ($status) {
            echo "{success: true,msg:'Assigned area to store. '}";
        } else {
            echo "{success: false,msg:'Time slot already added'}";
            exit();
        }
        break;
}
