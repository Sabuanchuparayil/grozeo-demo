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

    case 'listCentralStores':

        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 16 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'br_Name' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['cs_id', 'cs_name', 'cs_status', 'cs_item', 'cs_quantity', 'cs_date'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }

        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . "finascop_branch WHERE br_PyramidLevel= 2 ";
        $listQuery = "SELECT br_ID,br_Name,branch_shortname,br_csdefault,"
                . "(SELECT dst_Name FROM " . FINASCOP_DB . "finascop_district WHERE dst_Id = br_District) as br_District,"
                . "(SELECT st_name FROM " . FINASCOP_DB . "finascop_state WHERE st_ID = br_State) as br_State,"
                . "(SELECT comp_name from " . FINASCOP_DB . "finascop_company WHERE comp_id = "
                . "(SELECT comp_id from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id = a.br_ID)) as company,"
                . "(SELECT comp_id from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id = a.br_ID) as comp_id,"
                . "br_Address,br_Fax,br_Email,br_Phone,br_Incharge,br_status,br_pincode,br_Lat,br_Lng,if(br_PyramidLevel <> 1,(SELECT br_Name FROM finascop_branch WHERE br_ID = a.br_cpd),' ') AS  branchCpd "
                . "from " . FINASCOP_DB . "finascop_branch a WHERE br_PyramidLevel= 2 AND  {$filter_part} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
//                   echo $listQuery;
//                    exit;echo
        //$db->printGridJson($countQuery, $listQuery);
        $datas = $db->getMulipleData($listQuery, true);
        $count = $db->getItemFromDB($countQuery);
        // print_r($datas);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $br_defDistributor = $db->getFromDB("SELECT br_ID,br_Name FROM finascop_branch WHERE br_cpd = {$datas[$i]['br_ID']} AND br_csdefault = 1 AND br_PyramidLevel = 3", true);
                $datas[$i]['br_defDistributor'] = ($br_defDistributor['br_ID'] > 0 ? $br_defDistributor['br_Name'] : '-');
                $br_defRetailor = ($br_defDistributor['br_ID'] > 0 ? ($db->getItemFromDB("SELECT br_Name FROM finascop_branch WHERE br_cpd = {$br_defDistributor['br_ID']} AND br_csdefault = 1 AND br_PyramidLevel = 4")) : '-');
                $datas[$i]['br_defRetailor'] = $br_defRetailor;
            }
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

    case 'saveCentralStore':
        global $db;
        $db->query('begin');
        $data = $_POST;
        unset($data['br_stockLevel']);
        unset($data['br_cpd']);
        $data['br_defaultapibranch'] = 0;
        $data['br_cpd'] = $db->getItemFromDB("SELECT br_ID FROM finascop_branch WHERE br_PyramidLevel = 1");
        //echo $dat;
        $branch = new \finascop\accounts\Master\brmBranch();
        $status = $branch->saveBranch($data, ($data['br_ID'] > 0 ? false : true), false);
        if ($status) {
            echo "{success: true}";
            $db->query('commit');
        } else {
            echo "{success: false, errors:  'FINASCOP: Error occured while saving data' }";
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

    case 'cenStoreConSettings':
        $districts = json_decode(stripslashes($_POST['districts']), true);
        $state = $_POST['state'];
        $centarlStoreId = $_POST['centarlStoreId'];
        $db->query('begin');
        $mcsc_id = $_POST['mcsc_id'];
        $mcsc_state = $_POST['mcsc_state'];
        $data = array(
            'mcsc_state' => $state,
            'mcsc_centralStore' => $centarlStoreId
        );
        for ($i = 0; $i < count($districts); $i++) {
            $data['mcsc_district'] = $districts[$i];
            $data['mcsc_updatedOn'] = date('Y-m-d H:i:s');
            $data['mcsc_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $ingCount = $db->getItemFromDB("SELECT COUNT(*) FROM mypha_centralstore_config WHERE mcsc_state = {$state} AND mcsc_centralStore = {$centarlStoreId} AND mcsc_district = {$districts[$i]}");
            if ($ingCount > 0) {
                $mcsc_id = $db->getItemFromDB("SELECT mcsc_id FROM mypha_centralstore_config WHERE mcsc_state = {$state} AND mcsc_centralStore = {$centarlStoreId} AND mcsc_district = {$districts[$i]}");
                $status = $db->perform("mypha_centralstore_config", $data, 'update', 'mcsc_id =' . $mcsc_id);
            } else {
                $status = $db->perform("mypha_centralstore_config", $data);
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message: 'Districts mapped .'}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listMappedDistricts':
        $centarlStoreId = $_POST['centarlStoreId'];
        if ($centarlStoreId > 0) {
            $countQuery = "SELECT COUNT(*) FROM mypha_centralstore_config WHERE mcsc_centralStore = {$centarlStoreId} ";
            $listQuery = "SELECT mcsc_id,mcsc_state,(SELECT st_name FROM finascop_state where st_ID = mcsc_state) as state,mcsc_district,"
                    . "(SELECT dst_Name FROM finascop_district  WHERE dst_Id = mcsc_district) as dst_Name FROM mypha_centralstore_config  WHERE mcsc_centralStore = {$centarlStoreId} ";
            $db->printGridJson($countQuery, $listQuery);
        }
        break;
    case 'deleteMappedDistrict':
        $mcsc_id = $_POST['mcsc_id'];
        $db->query('begin');
        $delquery = "DELETE FROM mypha_centralstore_config  WHERE mcsc_id = {$mcsc_id}";
        $status = $db->query($delquery);

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Deleted Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while deleting data' }}";
        }
        break;
    case 'setCSDefault':
        $br_ID = $_POST['br_ID'];
        $pyramid = $_POST['pyramid'];
        $db->query('begin');
        switch ($pyramid) {
            case 2; //central store
                $status = $db->query("UPDATE finascop_branch SET br_csdefault = 0 WHERE `br_PyramidLevel` = {$pyramid}");
                break;
            case 3://distributor
                $defaultCS = $db->getItemFromDB("SELECT br_ID FROM finascop_branch WHERE br_csdefault = 1 AND br_PyramidLevel = 2");
                $cs = $db->getItemFromDB("SELECT br_cpd FROM finascop_branch WHERE br_ID = {$br_ID}");
                if ($defaultCS != $cs) {
                    echo '{"success":true,"valid":false,"msg":"The Central Store of this Distributor is not Default."}';
                    exit();
                }
                $status = $db->query("UPDATE finascop_branch SET br_csdefault = 0 WHERE `br_PyramidLevel` = {$pyramid} AND br_cpd = $cs");
                break;
            case 4://retailor
                $defaultDist = $db->getItemFromDB("SELECT br_ID FROM finascop_branch WHERE br_csdefault = 1 AND br_PyramidLevel = 3");
                $retailerDistibutor = $db->getItemFromDB("SELECT br_cpd FROM finascop_branch WHERE br_ID = {$br_ID}");
                $distriRetailor = $db->getItemFromDB("SELECT br_cpd FROM finascop_branch WHERE br_ID = {$retailerDistibutor}");
//                if ($defaultCS != $ret) {
//                    echo '{"success":true,"valid":false,"msg":"The Distributor of this Retailor is not Default."}';
//                    exit();
//                }
                $status = $db->query("UPDATE finascop_branch SET br_csdefault = 0 ");
                $status = $db->query("UPDATE finascop_branch SET br_csdefault = 1 where  br_ID = {$retailerDistibutor}");
                $status = $db->query("UPDATE finascop_branch SET br_csdefault = 1 where  br_ID = {$distriRetailor}");
                break;
        }

        $data['br_csdefault'] = 1;
        //$data['br_deliveryMode'] = $_POST['br_deliveryMode'];
        //$data['br_rdrId'] = $_POST['br_rdrId'];
        $status = $db->perform('finascop_branch', $data, 'update', " br_ID = {$br_ID}");
//have to update retaline_appconfig too 
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "Default Updated";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
}


