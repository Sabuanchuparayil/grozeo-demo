<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");
require_once(INCLUDE_PATH . "/finascop_accounts_Transactions.php");
function randomPassword()
{
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}
switch ($op) {
    case 'getProffession':
        $type = $_POST['type'];
        $qry = "SELECT id,name FROM crm_proffession ";
        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
    case 'getPreferredAreas':
        if (!empty($_POST['locationGlatitude']) && !empty($_POST['locationGlongitude'])) {
            $qry = "SELECT id,areaName, calcDistance({$_POST['locationGlatitude']}, {$_POST['locationGlongitude']}, areaLatitude, areaLongitude) AS distance 
            FROM `area_entries` WHERE (areaBusinessAssociate IS NULL OR areaBusinessAssociate = 0)  HAVING distance <=50 
    ORDER BY distance ASC";
        } else {
            $qry = "SELECT id,areaName FROM `area_entries` WHERE (areaBusinessAssociate IS NULL OR areaBusinessAssociate = 0) ";
        }

        $items = $db->getMulipleData($qry, true);
        $count = count($items);
        $items[$count]['id'] = -1;
        $items[$count]['areaName'] = "Others";
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
    case 'getStates':
        $defaultCountry = $db->getItemFromDB("SELECT country_id FROM retaline_country WHERE is_default = 1");

        $qry = "select st_ID,st_name from  finascop_state WHERE cnt_ID = {$defaultCountry} order by st_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getDistrict':
        $state = $_POST['st_Id'];
        $qry = "select dst_ID,dst_Name from  finascop_district where st_Id IN ({$state})  order by dst_Name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'loadEditData':
        $statusType = $_POST['EditStatus'];

        if ($statusType == 1) {
            $aaId = $_POST['_edit_aaId'];
        } else {
            //  $_GET['aaId'];
            $aaId = !empty($_GET['aaId']) ? $_GET['aaId'] : 0;
        }
        if ($aaId > 0) {
            $areaIds = $supportdb->getItemFromDB("SELECT GROUP_CONCAT(areaId) FROM area_associate_areas WHERE araeaAssociateId = {$aaId} ");
            $districtIds = $supportdb->getItemFromDB("SELECT GROUP_CONCAT(districtId) FROM area_associate_districts WHERE araeaAssociateId = {$aaId}");
            $statteIds = $supportdb->getItemFromDB("SELECT GROUP_CONCAT(stateId) FROM area_associate_districts WHERE araeaAssociateId = {$aaId}");
            $QRY = " SELECT caa.id as aaId,caa.name as aaName,mobile as aaMobile,email as aaEmail,professionId,businessVertical,organisationName,organisationType,
        gstNumber,businessLocation,locationGlatitude,locationGlongitude,createdBy,createdOn,updatedBy,
        updatedOn,cp.name as professionName,country_code  FROM crm_area_associate caa LEFT JOIN crm_proffession cp ON cp.id = professionId WHERE caa.id={$aaId}";
            $results = $supportdb->getFromDB($QRY, true);
            if ($results['createdBy'] > 0) {
                $results['createdByName'] = $db->getItemFromDB("SELECT FirstName FROM finascop_usr_profile WHERE UserId = {$results['createdBy']}");
            } else {
                $results['createdByName'] = 'Site';
            }
            $results['preferredArea'] = $areaIds;
            $results['as_st_id'] = $statteIds;
            $results['as_dst_Id'] = $districtIds;
        }
        if ($statusType == 1) {
            if (!empty($results)) {
                echo '{"success":true, "data":', json_encode($results), '}';
            } else
                echo '{"success":true,"data":[]}';
        } else {


            if ($aaId > 0) {
                require(THIS_MODULE_PATH . "/detailview.php");
            }
        }
        break;
    case 'insertContactAndMoveToLead':
        $id = $_POST['aaId'];
        $data['name'] = $_POST['aaName'];
        $data['mobile'] = $_POST['aaMobile'];
        $data['email'] = $_POST['aaEmail'];
        $data['professionId'] = $_POST['professionId'];

        $data['businessVertical'] = $_POST['businessVertical'];
        $data['organisationName'] = $_POST['organisationName'];
        $data['organisationType'] = $_POST['organisationType'];
        $data['gstNumber'] = $_POST['gstNumber'];
        $data['businessLocation'] = $_POST['businessLocation'];
        $data['locationGlatitude'] = $_POST['locationGlatitude'];
        $data['locationGlongitude'] = $_POST['locationGlongitude'];

        $data = array_filter($data);
        $preferredArea = $_POST['preferredArea'];
        $as_dst_Id = $_POST['as_dst_Id'];
        $as_st_id = $_POST['as_st_id'];
        //!empty($data['gstNumber']) &&
        if (
            !empty($data['businessVertical']) && !empty($data['organisationName']) && !empty($data['organisationType']) &&
            !empty($data['businessLocation']) && !empty($data['locationGlatitude']) && !empty($data['locationGlongitude']) && (!empty($preferredArea) || !empty($as_dst_Id))
        ) {
            $msg = "Converted to Lead.";
            $data['entryType'] = 2;
        } else {
            $msg = "Contact Created.";
            $data['entryType'] = 1;
        }

        $supportdb->query('begin');
        if ($id > 0) {
            $isUnique = $supportdb->getItemFromDB("SELECT COUNT(*) FROM crm_area_associate WHERE mobile ='{$_POST['aaMobile']}' AND id <> {$id}");
            if ($isUnique > 0) {
                echo "{success: false,msg: 'Editing details are already existing.'}";
                exit;
            } else {
                $data['updatedOn'] = date("Y-m-d H:i:s");
                $data['updatedBy'] = $_SESSION['admin']->UserId;
                $status = $supportdb->perform("crm_area_associate", $data, 'update', " id = {$id}");
                $araeaAssociateId = $id;
            }
        } else {
            $isUnique = $supportdb->getItemFromDB("SELECT COUNT(*) FROM crm_area_associate WHERE mobile ='{$_POST['aaMobile']}'");
            if ($isUnique > 0) {
                echo "{success: false,msg: 'Contact already existing.'}";
                exit;
            } else {
                $data['createdBy'] = $_SESSION['admin']->UserId;
                $status = $supportdb->perform("crm_area_associate", $data);
                $araeaAssociateId = $supportdb->insert_id();
            }
        }

        $crmCmmn['araeaAssociateId'] = $araeaAssociateId;
        $crmCmmn['entryTypeId'] = $data['entryType'];
        $crmCmmn['stageId'] = 0;
        $crmCmmn['createdBy'] = $_SESSION['admin']->UserId;
        $status = $supportdb->perform('crm_communications', $crmCmmn);

        if (!empty($preferredArea)) {
            $areas = explode(',', $preferredArea);
            foreach ($areas as $area) {
                $areaexists = $supportdb->getItemFromDB("SELECT COUNT(*) FROM area_associate_areas WHERE araeaAssociateId = {$araeaAssociateId} AND areaId = {$area}");
                if ($areaexists == 0) {
                    $aaaData['areaId'] = $area;
                    $aaaData['araeaAssociateId'] = $araeaAssociateId;
                    $status = $supportdb->perform("area_associate_areas", $aaaData);
                }
            }
        }
        if (!empty($as_dst_Id)) {
            $districts = explode(',', $as_dst_Id);
            foreach ($districts as $district) {
                $districtexists = $supportdb->getItemFromDB("SELECT COUNT(*) FROM area_associate_districts WHERE araeaAssociateId = {$araeaAssociateId} AND districtId = {$district}");
                if ($districtexists == 0) {
                    $aadData['districtId'] = $district;
                    $aadData['stateId'] = $db->getItemFromDB("SELECT st_ID FROM finascop_district where dst_ID = {$district} ");
                    $aadData['araeaAssociateId'] = $araeaAssociateId;
                    $status = $supportdb->perform("area_associate_districts", $aadData);
                }
            }
        }
        $status = $supportdb->query('commit');
        if ($status == 1) {
            echo "{success: true, msg:'{$msg}'}";
        } else {
            echo "{success: false, errors:  'Error occured while saving data' }";
        }

        break;



    case 'getContactDetails':
        $rec_limit = empty($_POST['limit']) ? 15 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $allowedFields = ['ap_name', 'ap_phone', 'ap_email', 'ap_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
            }
        }


        $countDataQuery = "SELECT COUNT(*) FROM crm_area_associate fcon  {$search}   ORDER BY id ";
        $count = $supportdb->getItemFromDB($countDataQuery);
        $userId = $_SESSION['admin']->Finascop_UserId;
        $current_date = date("Y-m-d");
        $qry = "SELECT caa.id as aaId,caa.name as aaName,mobile as aaMobile,email as aaEmail,professionId,businessVertical,organisationName,organisationType,
        gstNumber,businessLocation,locationGlatitude,locationGlongitude,createdBy,createdOn,updatedBy,
        updatedOn,cp.name as professionName,country_code  FROM crm_area_associate caa left join crm_proffession cp on cp.id = professionId {$search} ORDER BY aaId desc  LIMIT $rec_start,$rec_limit";
        //(SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) AS createdByName
        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            for ($i = 0; $i < count($items); $i++) {
                if ($items[$i]['createdBy'] > 0) {
                    $items[$i]['createdByName'] = $db->getItemFromDB("SELECT FirstName FROM finascop_usr_profile WHERE UserId = {$items[$i]['createdBy']}");
                } else {
                    $items[$i]['createdByName'] = 'Site';
                }
            }
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listEnquiryDetails':
        $rec_limit = empty($_POST['limit']) ? 15 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $allowedFields = ['ap_name', 'ap_phone', 'ap_email', 'ap_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
            }
        }


        $countDataQuery = "SELECT COUNT(*) FROM crm_area_associate fcon  {$search}   ORDER BY id ";
        $count = $supportdb->getItemFromDB($countDataQuery);
        $userId = $_SESSION['admin']->Finascop_UserId;
        $current_date = date("Y-m-d");
        $qry = "SELECT caa.id as aaId,caa.name as aaName,mobile as aaMobile,email as aaEmail,professionId,businessVertical,organisationName,organisationType,
        gstNumber,businessLocation,locationGlatitude,locationGlongitude,createdBy,createdOn,updatedBy,
        updatedOn,cp.name as professionName,country_code  FROM crm_area_associate caa left join crm_proffession cp on cp.id = professionId {$search} ORDER BY aaId desc  LIMIT $rec_start,$rec_limit";
        //(SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) AS createdByName
        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            for ($i = 0; $i < count($items); $i++) {
                if ($items[$i]['createdBy'] > 0) {
                    $items[$i]['createdByName'] = $db->getItemFromDB("SELECT FirstName FROM finascop_usr_profile WHERE UserId = {$items[$i]['createdBy']}");
                } else {
                    $items[$i]['createdByName'] = 'Site';
                }
            }
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listLeadDetails':
        $rec_limit = empty($_POST['limit']) ? 15 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $allowedFields = ['ap_name', 'ap_phone', 'ap_email', 'ap_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
            }
        }


        $countDataQuery = "SELECT COUNT(*) FROM crm_area_associate caa 
        inner join crm_proffession cp on cp.id = professionId 
        LEFT JOIN crm_stages stg on stg.id = stageId {$search} ";
        $count = $supportdb->getItemFromDB($countDataQuery);
        $userId = $_SESSION['admin']->Finascop_UserId;
        $current_date = date("Y-m-d");
        $qry = "SELECT caa.id as aaId,caa.name as aaName,mobile as aaMobile,email as aaEmail,professionId,businessVertical,organisationName,organisationType,
        gstNumber,businessLocation,locationGlatitude,locationGlongitude,createdBy,createdOn,updatedBy,caa.entryType,
        updatedOn,cp.name as professionName,stageId,stg.name as stageName,country_code  FROM crm_area_associate caa 
        inner join crm_proffession cp on cp.id = professionId 
        LEFT JOIN crm_stages stg on stg.id = stageId {$search} ORDER BY aaId desc  LIMIT $rec_start,$rec_limit";
        //(SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) AS createdByName
        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            for ($i = 0; $i < count($items); $i++) {
                if ($items[$i]['createdBy'] > 0) {
                    $items[$i]['createdByName'] = $db->getItemFromDB("SELECT FirstName FROM finascop_usr_profile WHERE UserId = {$items[$i]['createdBy']}");
                } else {
                    $items[$i]['createdByName'] = 'Site';
                }
            }
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'convertToContact':
        $supportdb->query('begin');
        $id = $_POST['enquiryId'];

        $mobileAvailable  = $supportdb->getItemFromDB("SELECT mobile FROM crm_area_associate WHERE id = {$id} ");
        if (empty($mobileAvailable)) {
            echo "{success: false,msg:'Contact details are missing in this enqiry'}";
            exit();
        }
        $isUnique = $supportdb->getItemFromDB("SELECT COUNT(*) FROM crm_area_associate WHERE mobile ='{$mobileAvailable}' AND status = 1 AND entryType = 1 AND id <> {$id}");
        if ($isUnique > 0) {
            echo "{success: false, msg: 'Mobile already existing.' }";
            exit;
        }

        $data = array(
            'entryType' => 1,
            'updatedOn' => date('Y-m-d H:i:s'),
            'updatedBy' => $_SESSION['admin']->UserId
        );

        $qry = $supportdb->perform('crm_area_associate', $data, 'update', 'id =' . $id);
        $crmCmmn['araeaAssociateId'] = $id;
        $crmCmmn['entryTypeId'] = 1;
        $crmCmmn['stageId'] = 0;
        $crmCmmn['remark'] = 'Converted to Contact';
        $crmCmmn['createdBy'] = $_SESSION['admin']->UserId;
        $status = $supportdb->perform('crm_communications', $crmCmmn);
        $status = $supportdb->query('commit');
        if ($status) {
            echo "{success: true,msg:'Updated Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'getCrmLeadStage':
        $qry = "select id,name from crm_stages  WHERE entryType = 2 order by id";
        $data = $supportdb->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'upgradeLeadStages':
        $leadId = $_POST['leadId'];
        switch ($_POST['crmStatus']) {
            case 1:
                $entryTypeId = 2;
                $message = "Demo Scheduled.";
                break;
            case 2:
                $entryTypeId = 2;
                $message = "Demo Given.";
                break;
            case 3:
                $entryTypeId = 2;
                $message = "Decision Maker Onboarded.";
                break;
            case 4:
                $message = "Lead interested.";
                $entryTypeId = 3;
                $leadUpdate['entryType'] = $entryTypeId;
                break;
        }
        $leadUpdate['stageId'] = $_POST['crmStatus'];
        $leadUpdate['updatedOn'] = date('Y-m-d', strtotime($_POST['crmFollowupDate']));

        $crmCmmn['araeaAssociateId'] = $leadId;
        $crmCmmn['entryTypeId'] = $entryTypeId;
        $crmCmmn['stageId'] = $_POST['crmStatus'];
        $crmCmmn['remark'] = $_POST['crmRemarks'];
        $crmCmmn['createdBy'] = $_SESSION['admin']->UserId;

        $supportdb->query('begin');
        $status = $supportdb->perform('crm_communications', $crmCmmn);
        $status = $supportdb->perform('crm_area_associate', $leadUpdate, 'update', " id = {$leadId}");
        $status = $supportdb->query('commit');
        if ($status) {
            echo "{success: true,msg: '" . $message . "' }";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;
    case 'listProspectDetails':
        $rec_limit = empty($_POST['limit']) ? 15 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $allowedFields = ['ap_name', 'ap_phone', 'ap_email', 'ap_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
            }
        }


        $countDataQuery = "SELECT COUNT(*) FROM crm_area_associate caa 
        INNER JOIN crm_proffession cp on cp.id = professionId 
        LEFT JOIN crm_stages stg on stg.id = stageId {$search} ";
        $count = $supportdb->getItemFromDB($countDataQuery);
        $userId = $_SESSION['admin']->Finascop_UserId;
        $current_date = date("Y-m-d");
        $qry = "SELECT caa.id as aaId,caa.name as aaName,mobile as aaMobile,email as aaEmail,professionId,businessVertical,organisationName,organisationType,
        gstNumber,businessLocation,locationGlatitude,locationGlongitude,createdBy,createdOn,updatedBy,
        updatedOn,cp.name as professionName,stageId,stg.name as stageName,country_code  FROM crm_area_associate caa 
        INNER JOIN crm_proffession cp on cp.id = professionId 
        LEFT JOIN crm_stages stg on stg.id = stageId {$search} ORDER BY aaId desc  LIMIT $rec_start,$rec_limit";
        //(SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) AS createdByName
        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            for ($i = 0; $i < count($items); $i++) {
                if ($items[$i]['createdBy'] > 0) {
                    $items[$i]['createdByName'] = $db->getItemFromDB("SELECT FirstName FROM finascop_usr_profile WHERE UserId = {$items[$i]['createdBy']}");
                } else {
                    $items[$i]['createdByName'] = 'Site';
                }
            }
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'insertProspects':
        $id = $_POST['aaId'];
        $data['name'] = $_POST['aaName'];
        $data['mobile'] = $_POST['aaMobile'];
        $data['email'] = $_POST['aaEmail'];
        $data['professionId'] = $_POST['professionId'];

        $data['businessVertical'] = $_POST['businessVertical'];
        $data['organisationName'] = $_POST['organisationName'];
        $data['organisationType'] = $_POST['organisationType'];
        $data['gstNumber'] = $_POST['gstNumber'];
        $data['businessLocation'] = $_POST['businessLocation'];
        $data['locationGlatitude'] = $_POST['locationGlatitude'];
        $data['locationGlongitude'] = $_POST['locationGlongitude'];

        $data = array_filter($data);
        $preferredArea = $_POST['preferredArea'];
        $as_dst_Id = $_POST['as_dst_Id'];
        $as_st_id = $_POST['as_st_id'];
        $msg = "Prospect Created.";
        $data['entryType'] = 3;

        $supportdb->query('begin');
        if ($id > 0) {
            $isUnique = $supportdb->getItemFromDB("SELECT COUNT(*) FROM crm_area_associate WHERE mobile ='{$_POST['aaMobile']}' AND id <> {$id}");
            if ($isUnique > 0) {
                echo "{success: false,errors: { msg: 'Editing details are already existing.' }}";
                exit;
            } else {
                $data['updatedOn'] = date("Y-m-d H:i:s");
                $data['updatedBy'] = $_SESSION['admin']->UserId;
                $status = $supportdb->perform("crm_area_associate", $data, 'update', " id = {$id}");
                $araeaAssociateId = $id;
            }
        } else {
            $isUnique = $supportdb->getItemFromDB("SELECT COUNT(*) FROM crm_area_associate WHERE mobile ='{$_POST['aaMobile']}'");
            if ($isUnique > 0) {
                echo "{success: false,errors: { msg: 'Data already existing.' }}";
                exit;
            } else {
                $data['createdBy'] = $_SESSION['admin']->UserId;
                $status = $supportdb->perform("crm_area_associate", $data);
                $araeaAssociateId = $supportdb->insert_id();
            }
        }

        $crmCmmn['araeaAssociateId'] = $araeaAssociateId;
        $crmCmmn['entryTypeId'] = $data['entryType'];
        $crmCmmn['stageId'] = 0;
        $crmCmmn['createdBy'] = $_SESSION['admin']->UserId;
        $status = $supportdb->perform('crm_communications', $crmCmmn);

        if (!empty($preferredArea)) {
            $areas = explode(',', $preferredArea);
            foreach ($areas as $area) {
                $areaexists = $supportdb->getItemFromDB("SELECT COUNT(*) FROM area_associate_areas WHERE araeaAssociateId = {$araeaAssociateId} AND areaId = {$area}");
                if ($areaexists == 0) {
                    $aaaData['areaId'] = $area;
                    $aaaData['araeaAssociateId'] = $araeaAssociateId;
                    $status = $supportdb->perform("area_associate_areas", $aaaData);
                }
            }
        }
        if (!empty($as_dst_Id)) {
            $districts = explode(',', $as_dst_Id);
            foreach ($districts as $district) {
                $districtexists = $supportdb->getItemFromDB("SELECT COUNT(*) FROM area_associate_districts WHERE araeaAssociateId = {$araeaAssociateId} AND districtId = {$district}");
                if ($districtexists == 0) {
                    $aadData['districtId'] = $district;
                    $aadData['stateId'] = $db->getItemFromDB("SELECT st_ID FROM finascop_district where dst_ID = {$district} ");
                    $aadData['araeaAssociateId'] = $araeaAssociateId;
                    $status = $supportdb->perform("area_associate_districts", $aadData);
                }
            }
        }
        $status = $supportdb->query('commit');
        if ($status == 1) {
            echo "{success: true, msg:'{$msg}'}";
        } else {
            echo "{success: false, errors:  'Error occured while saving data' }";
        }

        break;
    case 'listAreaForProspect':
        $aaId = $_POST['aaId'];
        $search = " WHERE 1=1 "; //areaBusinessAssociate > 0
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');
                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $search .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                    }
                }
            }
        }
        $aaAreas = $supportdb->getItemFromDB("SELECT GROUP_CONCAT(areaId) FROM area_associate_areas WHERE araeaAssociateId = {$aaId}");
        if (!empty($aaAreas) && ($aaAreas != -1)) {
            $search .= " AND id IN ({$aaAreas}) ";
        }
        $contactDetails  = $supportdb->getFromDB("SELECT locationGlatitude,locationGlongitude FROM crm_area_associate WHERE id = {$aaId} ", true);
        $qry = "SELECT *, calcDistance({$contactDetails['locationGlatitude']}, {$contactDetails['locationGlongitude']}, 
        areaLatitude, areaLongitude) AS distance,areaBusinessAssociate,IF(areaBusinessAssociate>0,'Yes','No') AS assigned,
        IF(areaLockedFor = {$aaId},1,0) AS checked FROM `area_entries` AE {$search} 
            ORDER BY distance ASC";

        $items = $db->getMulipleData($qry, true);
        $count = count($items);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'getCrmProspectStage':
        $qry = "select id,name from crm_stages  WHERE entryType = 3 and id > 4 order by id";
        $data = $supportdb->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'get_file_s3_details':
        $rid = $_POST['rid'];
        $data['grzBucketName'] = AWSBUCKETNAME;
        $data['accessKey'] = AWSS3ASSETUPLOADACCESSID;
        $data['secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
        $data['bucketRegion'] = AWSS3ASSETUPLOADREGION;
        $data['oncompleteurl'] = 'crm/';
        if ($data) {
            echo "{success: true,msg:'Saved Successfully','data':" . json_encode($data) . "}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'mapAreaApproveProspect':
        $araeaAssociateId = $_POST['aaId'];
        $areaIds = json_decode(stripslashes($_POST['areaIds']), true);
        $supportdb->query('begin');
        if (count($areaIds) > 0) {
            foreach ($areaIds as $areaId) {
                $areaexists = $supportdb->getItemFromDB("SELECT COUNT(*) FROM area_associate_areas WHERE araeaAssociateId = {$araeaAssociateId} AND areaId = {$areaId}");
                if ($areaexists == 0) {
                    $aaaData['areaId'] = $areaId;
                    $aaaData['araeaAssociateId'] = $araeaAssociateId;
                    $status = $supportdb->perform("area_associate_areas", $aaaData);
                }
                $areadata['areaLockedFor'] = $araeaAssociateId;
                $status = $db->perform("area_entries", $areadata, 'update', " id = {$areaId}");
            }
            $message = "Prospect Approved.";
            $leadUpdate['stageId'] = 4;
            $leadUpdate['updatedOn'] = date('Y-m-d', strtotime($_POST['crmFollowupDate']));
            $status = $supportdb->perform('crm_area_associate', $leadUpdate, 'update', " id = {$araeaAssociateId}");
        } else {
            echo "{success: false,msg: 'No area selected.' }";
            exit;
        }
        $status = $supportdb->query('commit');
        if ($status) {
            echo "{success: true,msg: '" . $message . "' }";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;
    case 'upgradeProspectStages':
        $leadId = $_POST['leadId'];
        switch ($_POST['crmStatus']) {
            case 5:
                $entryTypeId = 3;
                $message = "Mail Sent.";
                break;
            case 6:
                $entryTypeId = 3;
                $message = "Prospect Details Received.";
                break;
            case 7:
                $message = "Business Discussion Scheduled.";
                $entryTypeId = 3;
                break;
            case 8:
                $entryTypeId = 3;
                $message = "Business Discussed.";
                break;
            case 9:
                $entryTypeId = 3;
                $message = "Proposal Sent.";
                $filepath = $_POST['s3filepath'];
                if (!empty($filepath)) {
                    $crmCmmn['filePath'] = $filepath;
                }
                break;
            case 10:
                $isProspectApproved = $supportdb->getItemFromDB("SELECT COUNT(*) FROM crm_communications WHERE araeaAssociateId = {$leadId} AND stageId = 4");
                if ($isProspectApproved > 0) {
                    $entryTypeId = 4;
                    $message = "Prospect Interested.";
                    $leadUpdate['entryType'] = $entryTypeId;
                } else {
                    echo "{success: false,msg: 'Proceed after Prospect Approving process.' }";
                    exit;
                }

                break;
        }
        $leadUpdate['stageId'] = $_POST['crmStatus'];
        $leadUpdate['updatedOn'] = date('Y-m-d', strtotime($_POST['crmFollowupDate']));

        $crmCmmn['araeaAssociateId'] = $leadId;
        $crmCmmn['entryTypeId'] = $entryTypeId;
        $crmCmmn['stageId'] = $_POST['crmStatus'];
        $crmCmmn['remark'] = $_POST['crmRemarks'];
        $crmCmmn['createdBy'] = $_SESSION['admin']->UserId;

        $supportdb->query('begin');
        $status = $supportdb->perform('crm_communications', $crmCmmn);
        $status = $supportdb->perform('crm_area_associate', $leadUpdate, 'update', " id = {$leadId}");
        $status = $supportdb->query('commit');
        if ($status) {
            echo "{success: true,msg: '" . $message . "' }";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;
    case 'listAreaForOppurtunity':
        $aaId = $_POST['aaId'];
        $search = " WHERE 1=1 "; //areaBusinessAssociate > 0
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');
                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $search .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                    }
                }
            }
        }
        $aaAreas = $supportdb->getItemFromDB("SELECT GROUP_CONCAT(areaId) FROM area_associate_areas WHERE araeaAssociateId = {$aaId}");
        if (!empty($aaAreas)) {
            $search .= " AND id IN ({$aaAreas}) ";
        }

        $contactDetails  = $supportdb->getFromDB("SELECT locationGlatitude,locationGlongitude FROM crm_area_associate WHERE id = {$aaId} ", true);
        $qry = "SELECT *, calcDistance({$contactDetails['locationGlatitude']}, {$contactDetails['locationGlongitude']}, areaLatitude, areaLongitude) AS distance,
        areaBusinessAssociate,IF(areaBusinessAssociate>0,'Yes','No') AS assigned
        FROM `area_entries` AE  {$search}  ORDER BY distance ASC";

        $items = $db->getMulipleData($qry, true);
        $count = count($items);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listOppurtunityDetails':
        $rec_limit = empty($_POST['limit']) ? 15 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $allowedFields = ['ap_name', 'ap_phone', 'ap_email', 'ap_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
            }
        }


        $countDataQuery = "SELECT COUNT(*) FROM crm_area_associate caa 
        INNER JOIN crm_proffession cp on cp.id = professionId 
        LEFT JOIN crm_stages stg on stg.id = stageId {$search} ";
        $count = $supportdb->getItemFromDB($countDataQuery);
        $userId = $_SESSION['admin']->Finascop_UserId;
        $current_date = date("Y-m-d");
        $qry = "SELECT caa.id as aaId,caa.name as aaName,mobile as aaMobile,email as aaEmail,professionId,businessVertical,organisationName,organisationType,
        gstNumber,businessLocation,locationGlatitude,locationGlongitude,createdBy,createdOn,updatedBy,
        updatedOn,cp.name as professionName,stageId,stg.name as stageName,country_code  FROM crm_area_associate caa 
        INNER JOIN crm_proffession cp on cp.id = professionId 
        LEFT JOIN crm_stages stg on stg.id = stageId {$search} ORDER BY aaId desc  LIMIT $rec_start,$rec_limit";
        //(SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) AS createdByName
        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            for ($i = 0; $i < count($items); $i++) {
                if ($items[$i]['createdBy'] > 0) {
                    $items[$i]['createdByName'] = $db->getItemFromDB("SELECT FirstName FROM finascop_usr_profile WHERE UserId = {$items[$i]['createdBy']}");
                } else {
                    $items[$i]['createdByName'] = 'Site';
                }
            }
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listIncubateeDetails':
        $rec_limit = empty($_POST['limit']) ? 15 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $allowedFields = ['ap_name', 'ap_phone', 'ap_email', 'ap_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
            }
        }


        $countDataQuery = "SELECT COUNT(*) FROM crm_area_associate caa 
            INNER JOIN crm_proffession cp on cp.id = professionId 
            LEFT JOIN crm_stages stg on stg.id = stageId {$search} ";
        $count = $supportdb->getItemFromDB($countDataQuery);
        $userId = $_SESSION['admin']->Finascop_UserId;
        $current_date = date("Y-m-d");
        $qry = "SELECT caa.id as aaId,caa.name as aaName,mobile as aaMobile,email as aaEmail,professionId,businessVertical,organisationName,organisationType,
            gstNumber,businessLocation,locationGlatitude,locationGlongitude,createdBy,createdOn,updatedBy,
            updatedOn,cp.name as professionName,stageId,stg.name as stageName,country_code  FROM crm_area_associate caa 
            INNER JOIN crm_proffession cp on cp.id = professionId 
            LEFT JOIN crm_stages stg on stg.id = stageId {$search} ORDER BY aaId desc  LIMIT $rec_start,$rec_limit";
        //(SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) AS createdByName
        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            for ($i = 0; $i < count($items); $i++) {
                if ($items[$i]['createdBy'] > 0) {
                    $items[$i]['createdByName'] = $db->getItemFromDB("SELECT FirstName FROM finascop_usr_profile WHERE UserId = {$items[$i]['createdBy']}");
                } else {
                    $items[$i]['createdByName'] = 'Site';
                }
            }
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'approveOppurtunityArea':
        $araeaAssociateId = $_POST['aaId'];
        $areaIds = json_decode(stripslashes($_POST['areaIds']), true);
        //$supportdb->query('begin');
        if (count($areaIds) > 0) {
            foreach ($areaIds as $areaId) {
                $areaexists = $supportdb->getItemFromDB("SELECT COUNT(*) FROM area_associate_areas WHERE araeaAssociateId = {$araeaAssociateId} AND areaId = {$areaId}");
                if ($areaexists == 0) {
                    $aaaData['areaId'] = $areaId;
                    $aaaData['araeaAssociateId'] = $araeaAssociateId;
                    $status = $supportdb->perform("area_associate_areas", $aaaData);
                }
                $date = strtotime("+7 day");
                $areadata['areaLockedTill'] = date('Y-m-d', $date);
                $areadata['areaLockedFor'] = $araeaAssociateId;
                $status = $db->perform("area_entries", $areadata, 'update', " id = {$areaId}");
            }
            $message = "Oppurtunity Approved.Area locked for 7 days.";
            $leadUpdate['stageId'] = 11;
            $leadUpdate['updatedOn'] = date('Y-m-d', strtotime($_POST['crmFollowupDate']));
            $status = $supportdb->perform('crm_area_associate', $leadUpdate, 'update', " id = {$araeaAssociateId}");
            $entryTypeId = $supportdb->getItemFromDB("SELECT entryType FROM crm_area_associate WHERE id = {$araeaAssociateId}");

            $crmCmmn['araeaAssociateId'] = $araeaAssociateId;
            $crmCmmn['entryTypeId'] = $entryTypeId;
            $crmCmmn['stageId'] = $leadUpdate['stageId'];
            $crmCmmn['remark'] = $message;
            $crmCmmn['followupDate'] = $leadUpdate['updatedOn'];
            $crmCmmn['createdBy'] = $_SESSION['admin']->UserId;
            $status = $supportdb->perform('crm_communications', $crmCmmn);
        } else {
            echo "{success: false,msg: 'No area selected.' }";
            exit;
        }
        ///$status = $supportdb->query('commit');
        if ($status) {
            echo "{success: true,msg: '" . $message . "' }";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;
    case 'getCrmOppurtunityStage':
        $qry = "select id,name from crm_stages  WHERE entryType = 4 and id > 11 and id < 15 order by id";
        $data = $supportdb->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'upgradeOppurtunityStages':
        $leadId = $_POST['leadId'];
        $crmFollowupDate = $_POST['crmFollowupDate'];
        $crmRemarks = $_POST['crmRemarks'];
        switch ($_POST['crmStatus']) {
            case 12:
                $entryTypeId = 4;
                $message = "Incubation Fees Awaited.";
                $araeData['areaLockedTill'] = date('Y-m-d', strtotime($crmFollowupDate));
                break;
            case 13:
                $entryTypeId = 4;
                $message = "Incubation Fees Received.";
                $filepath = $_POST['s3filepath'];
                if (!empty($filepath)) {
                    $crmCmmn['filePath'] = $filepath;
                }
                break;
            case 14:
                $message = "Incubation Fees Receipt Sent.";
                $entryTypeId = 4;
                break;

            case 15:
                $isOppurtunityApproved = $supportdb->getItemFromDB("SELECT COUNT(*) FROM crm_communications WHERE araeaAssociateId = {$leadId} AND stageId = 11");
                if ($isOppurtunityApproved > 0) {
                    $entryTypeId = 5;
                    $message = "Move to Incubatee.";
                    $leadUpdate['entryType'] = $entryTypeId;
                    $crmFollowupDate = date('Y-m-d');
                    $crmRemarks = "Moved to Incubaee.";
                    $date = strtotime("+30 day");
                    $areadata['areaLockedTill'] = date('Y-m-d', $date);
                } else {
                    echo "{success: false,msg: 'Proceed after Oppurtunity Approving process.' }";
                    exit;
                }

                break;
        }
        $leadUpdate['stageId'] = $_POST['crmStatus'];
        $leadUpdate['updatedOn'] = date('Y-m-d', strtotime($crmFollowupDate));

        $crmCmmn['araeaAssociateId'] = $leadId;
        $crmCmmn['entryTypeId'] = $entryTypeId;
        $crmCmmn['stageId'] = $_POST['crmStatus'];
        $crmCmmn['remark'] = $crmRemarks;
        $crmCmmn['followupDate'] = $leadUpdate['updatedOn'];
        $crmCmmn['createdBy'] = $_SESSION['admin']->UserId;

        $supportdb->query('begin');
        $status = $supportdb->perform('crm_communications', $crmCmmn);
        $status = $supportdb->perform('crm_area_associate', $leadUpdate, 'update', " id = {$leadId}");
        if (!empty($araeData['areaLockedTill'])) {
            $araeData['areaUpdatedOn'] = date('Y-m-d H:i:s');
            $araeData['areaUpdatedBy'] = $_SESSION['admin']->UserId;
            $status = $db->perform('area_entries', $araeData, 'update', " areaLockedFor = {$leadId}");
        }
        $status = $supportdb->query('commit');
        if ($status) {
            echo "{success: true,msg: '" . $message . "' }";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;
    case 'getTraingModules':

        $RoleId = $db->getItemFromDB('select RoleId from finascop_usr_master where UserId = ' . $_GET['user_id']);

        $Basic_Menu = $supportdb->getMultipleData("SELECT id as MenuId,name as MenuText FROM training_module  ORDER BY id", true);

        if (!empty($Basic_Menu)) {
            $Basic_Tbar_Menu = array();
            foreach ($Basic_Menu as $index => $val) {
                $Basic_Tbar_Menu[$index] = array();
                $Basic_Tbar_Menu[$index]['id'] = 'BA_' . $val['MenuId'];
                $Basic_Tbar_Menu[$index]['text'] = $val['MenuText'];
                $Basic_Tbar_Menu[$index]['draggable'] = false;
                $Basic_Tbar_Menu[$index]['children'] = array();
                $Basic_Tbar_Menu[$index]['cls'] = 'myphasetting-btn';

                $query = "SELECT tmt.id as tmtId,ModuleId as MenuId,topicName AS text,aat.trainingId as trainingId
                          FROM training_module_topics tmt 
                          INNER JOIN training_module tm on tm.id = ModuleId 
                          LEFT JOIN area_associate_training aat on aat.trainingId = tmt.id WHERE ModuleId = {$val['MenuId']}";

                $MenuChildTree = $supportdb->getMultipleData($query, true);

                if (!empty($MenuChildTree)) {
                    $temp = array();
                    //$temp = appendNonMenuedItems($MenuChildTree, $_GET['user_id'],$RoleId,$db);
                    if (!empty($temp)) {
                        $MenuChildTree = array_merge($temp, $MenuChildTree);
                    }
                }
                if (!empty($MenuChildTree)) {
                    $MenuChildTree_node = buildTrainingTree($MenuChildTree, $val['MenuId']);
                    $Basic_Tbar_Menu[$index]['leaf'] = false;
                    $Basic_Tbar_Menu[$index]['children'] = $MenuChildTree_node;
                }
            }
        }
        echo json_encode($Basic_Tbar_Menu);
        break;
    case 'saveTrainings':

        $supportdb->query('begin');
        $sqaData['aaId'] = $_POST['aaId'];
        $sqaData['trainingDate'] = date('Y-m-d', strtotime($_POST['trainingDate']));
        $sqaData['trainingId'] = $_POST['trainingTopics'];
        $sqaData['trainingComments'] = $_POST['trainingComments'];
        $sqaData['createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $supportdb->perform('area_associate_training', $sqaData);


        $success = $supportdb->query('commit');
        if ($success) {
            echo "{success:true,valid:true,message:'Added training.'}";
        } else {
            echo "{success:false,valid:true,message:'Error occred while updating.'}";
        }
        break;
    case 'listTrainingModules':
        $aaId = intval($_POST['aaId']);
        $edit_status = $_POST['edit_status'];
        $allowedFields = ['ap_name', 'ap_phone', 'ap_email', 'ap_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        }
        $qry = "SELECT tmt.id as tmtId,ModuleId,topicName,tm.name as moduleName,trainingId,
        if(aat.trainingId is null,0,1) as checked,trainingDate,trainingComments
            FROM training_module_topics tmt 
            INNER JOIN training_module tm on tm.id = ModuleId 
            INNER JOIN area_associate_training aat on aat.trainingId = tmt.id and aaId = {$aaId} where 1 = 1 {$searchitem}";

        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo json_encode([]);
        break;
    case 'listInstarDetails':
        $rec_limit = empty($_POST['limit']) ? 15 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $allowedFields = ['ap_name', 'ap_phone', 'ap_email', 'ap_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
            }
        }


        $countDataQuery = "SELECT COUNT(*) FROM crm_area_associate caa 
                INNER JOIN crm_proffession cp on cp.id = professionId 
                LEFT JOIN crm_stages stg on stg.id = stageId {$search} ";
        $count = $supportdb->getItemFromDB($countDataQuery);
        $userId = $_SESSION['admin']->Finascop_UserId;
        $current_date = date("Y-m-d");
        $qry = "SELECT caa.id as aaId,caa.name as aaName,mobile as aaMobile,email as aaEmail,professionId,businessVertical,organisationName,organisationType,
                gstNumber,businessLocation,locationGlatitude,locationGlongitude,createdBy,createdOn,updatedBy,
                updatedOn,cp.name as professionName,stageId,stg.name as stageName,country_code  FROM crm_area_associate caa 
                INNER JOIN crm_proffession cp on cp.id = professionId 
                LEFT JOIN crm_stages stg on stg.id = stageId {$search} ORDER BY aaId desc  LIMIT $rec_start,$rec_limit";
        //(SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) AS createdByName
        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            for ($i = 0; $i < count($items); $i++) {
                if ($items[$i]['createdBy'] > 0) {
                    $items[$i]['createdByName'] = $db->getItemFromDB("SELECT FirstName FROM finascop_usr_profile WHERE UserId = {$items[$i]['createdBy']}");
                } else {
                    $items[$i]['createdByName'] = 'Site';
                }
            }
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listAreaForIncubatee':
        $aaId = $_POST['aaId'];
        $search = " WHERE 1=1 ";
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');
                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $search .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                    }
                }
            }
        }
        if ($aaId > 0) {
            $search .= " AND  areaLockedFor = {$aaId}";
        }
        $qry = "SELECT *, areaBusinessAssociate,IF(areaBusinessAssociate>0,'Yes','No') AS assigned FROM `area_entries` AE {$search} 
            ";

        $items = $db->getMulipleData($qry, true);
        $count = count($items);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'confirmAreaAssociate':
        $araeaAssociateId = $_POST['aaId'];
        $areaId = $_POST['areaId'];

        $areaDetails = $db->getFromDB("SELECT * FROM area_entries WHERE id = {$areaId}", true);
        $aaDetails = $supportdb->getFromDB("SELECT * FROM crm_area_associate WHERE id = {$araeaAssociateId}", true);
        $baexists = $db->getItemFromDB("SELECT COUNT(*) FROM business_associate WHERE baMobileNo = '{$aaDetails['mobile']}'");
        if($baexists > 0){
            echo "{success: false,msg:'Mobile already exist.'}";
            exit();
        }
        $entryTypeId = $supportdb->getItemFromDB("SELECT entryType FROM crm_area_associate WHERE id = {$araeaAssociateId}");
        if ($areaId > 0 && $entryTypeId != 7) {
            $supportdb->query('begin');

            $areaexists = $supportdb->getItemFromDB("SELECT COUNT(*) FROM area_associate_areas WHERE araeaAssociateId = {$araeaAssociateId} AND areaId = {$areaId}");
            if ($areaexists == 0) {
                $aaaData['areaId'] = $areaId;
                $aaaData['araeaAssociateId'] = $araeaAssociateId;
                $status = $supportdb->perform("area_associate_areas", $aaaData);
            }

            $message = "Area Associate Created.";
            $leadUpdate['entryType'] = 7;
            $leadUpdate['updatedOn'] = date('Y-m-d');
            $status = $supportdb->perform('crm_area_associate', $leadUpdate, 'update', " id = {$araeaAssociateId}");


            $status = $supportdb->query('commit');

            $temporary_password = randomPassword();
            $refid = $db->getItemFromDB("SELECT UUID() AS uuid");

            $baData['baName'] = $aaDetails['name'];
            $baData['baMobileNo'] = $aaDetails['mobile'];
            $baData['baEmail'] = $aaDetails['email'];
            $baData['baType'] = 1;
            $baData['baMode'] = 1;
            $baData['baAddress'] = $aaDetails['businessLocation'];
            $baData['baGSTIN'] = $aaDetails['gstNumber'];
            $baData['balatitude'] = $aaDetails['locationGlatitude'];
            $baData['balongitude'] = $aaDetails['locationGlongitude'];
            $baData['baCreatedOn'] = date('Y-m-d H:i:s');
            $baData['baCreatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $baData['temporary_password'] = $temporary_password;
            $baData['refid'] = $refid;
            $baData['st_id'] = $areaDetails['areaState'];
            $baData['dst_Id'] = $areaDetails['areaDistrict'];
            $status = $db->perform("business_associate", $baData);
            $baId = $db->insert_id();

            $data['areaBusinessAssociate'] = $baId;
            $data['areaUpdatedOn'] = date('Y-m-d H:i:s');
            $data['areaUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform("area_entries", $data, 'update', " id = {$areaId}");

            $group_id = '197';
            $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'CreateLedger'");
            $finields = array(
                "name" => $baData['baName'],
                "mobile" =>  $baData['baMobileNo'],
                "refid" => $refid,
                "group_id" => $group_id
            );

            $fields_stringfin = json_encode($finields);
            //echo $url;
            //print_r($fields_stringfin);
            $cURLConnection = curl_init();
            $headers = [
                "x-functions-key:" . DATAENTRY_KEY,
                'Content-Type:application/json'
            ];

            curl_setopt($cURLConnection, CURLOPT_URL, $url);
            curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($cURLConnection, CURLOPT_ENCODING, '');
            curl_setopt($cURLConnection, CURLOPT_MAXREDIRS, 10);
            curl_setopt($cURLConnection, CURLOPT_TIMEOUT,  0);
            curl_setopt($cURLConnection, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($cURLConnection, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            //curl_setopt($cURLConnection, CURLOPT_POST, 1);
            curl_setopt($cURLConnection, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $fields_stringfin);
            curl_setopt($cURLConnection, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS | CURLPROTO_HTTP);

            $response = curl_exec($cURLConnection);
            curl_close($cURLConnection);
            $resultFin = json_decode($response, true);
            //if ($resultFin['statusId'] == 1) {



            $baDetails = $db->getFromDB("SELECT * FROM business_associate WHERE id = {$baId}", true);
            if ($baDetails['st_id'] > 0)
                $state = $db->getItemFromDB("SELECT st_name FROM finascop_state WHERE st_ID = {$baDetails['st_id']}");
            $country = $db->getItemFromDB("SELECT country_name FROM retaline_country WHERE is_default = 1");

            $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'BAURL'");
            $fields = array(
                "FullName" => $baDetails['baName'],
                "Address" => $baDetails['baAddress'],
                "Phone" =>  $baDetails['baMobileNo'],
                "Email" => $baDetails['baEmail'],
                "Password" => $baDetails['temporary_password'],
                "City" => $baDetails['baCity'],
                "State" => $state,
                "Country" => $country,
                "UserType" => 3,
                "areaId" => $areaId
            );
            $fields_string = json_encode($fields);
            $opts = array(
                CURLOPT_URL => $url,
                CURLINFO_CONTENT_TYPE => "application/json",
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_BINARYTRANSFER => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_POST => count($fields),
                CURLOPT_POSTFIELDS => $fields_string,
                CURLOPT_HTTPHEADER => array('Content-Type: application/json')
            );

            $ch = curl_init();
            curl_setopt_array($ch, $opts);
            $datacl = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            header("Content-Type: application/json");
            $result = json_decode($datacl, true);
            if ($result['result'] == 0) {
                echo "{success: false,msg:'Failed, the email or mobile conflicted with an existing record. Please try with another email and mobile!'}";
                //exit;
            }


            //}
        } else {
            echo "{success: false,msg: 'No area selected./Area associate is already converted ' }";
            exit;
        }

        if ($status) {
            echo "{success: true,msg: '" . $message . "' }";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;
    case 'getTopics':
        $ModuleId = $_POST['ModuleId'];
        $qry = "SELECT id,topicName FROM training_module_topics WHERE ModuleId = '{$ModuleId}' ";
        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
    case 'getModules':
        $qry = "SELECT id,name FROM training_module";
        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
    case 'confirmTrainings':
        $supportdb->query('begin');
        $confirmTrainings = $supportdb->getItemFromDB("SELECT COUNT(*) FROM training_module_topics");
        $trainedTopicCount = $supportdb->getItemFromDB("SELECT COUNT(DISTINCT(trainingId)) FROM area_associate_training WHERE aaId = {$_POST['aaId']}");
        if ($confirmTrainings == $trainedTopicCount) {
            $crmCmmn['araeaAssociateId'] = $_POST['aaId'];
            $crmCmmn['entryTypeId'] = 6;
            $crmCmmn['stageId'] = 16;
            $crmCmmn['remark'] = "Incubation Completed";
            $crmCmmn['createdBy'] = $_SESSION['admin']->UserId;

            $leadUpdate['entryType'] = 6;
            $crmFollowupDate = date('Y-m-d');
            $leadUpdate['stageId'] = 16;
            $leadUpdate['updatedOn'] = date('Y-m-d', strtotime($crmFollowupDate));

            $status = $supportdb->perform('crm_communications', $crmCmmn);
            $status = $supportdb->perform('crm_area_associate', $leadUpdate, 'update', " id = {$_POST['aaId']}");
            $msg = "Training updated. Incubatee moved to Instar Associate";
        } else {
            $msg = "Training updated.";
        }
        $success = $supportdb->query('commit');
        if ($success) {
            echo "{success:true,valid:true,message:'{$msg}'}";
        } else {
            echo "{success:false,valid:true,message:'Error occred while updating.'}";
        }
        break;
    case 'getAACommunication':
        $araeaAssociateId = $_POST['aaId'];
        $countDataQuery = "SELECT count(*) from crm_communications LEFT JOIN crm_stages stg on stg.id = stageId WHERE araeaAssociateId = {$araeaAssociateId}";
        $count = $supportdb->getItemFromDB($countDataQuery);
        $listQuery = "SELECT cc.id,araeaAssociateId,stageId,remark,entryTypeId,createdOn,stg.name  AS stageName, CASE 
WHEN entryTypeId = 0 THEN 'Enquiry' 
WHEN entryTypeId = 1  THEN 'Contact' 
WHEN entryTypeId = 2  THEN 'Lead' 
WHEN entryTypeId = 3  THEN 'Prospect' 
WHEN entryTypeId = 4  THEN 'Opportunity' WHEN entryTypeId = 5  THEN 'Incubatee' WHEN entryTypeId = 6  THEN 'Instar'
END AS typeName from crm_communications cc LEFT JOIN crm_stages stg on stg.id = stageId WHERE araeaAssociateId = {$araeaAssociateId}";
        $items = $supportdb->getMulipleData($listQuery, true);


        if (!empty($items)) {

            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'removeGeneralEnquiry':
        $id = $_POST['id'];
        //$status = $_POST['activestatus'];
        $data = array(
            'status' => 0,
            'updatedOn' => date("Y-m-d H:i:s"),
            'updatedBy' => $_SESSION['admin']->UserId
        );
        $qry = $supportdb->perform('crm_area_associate', $data, 'update', 'id = ' . $id);
        if ($qry) {
            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data' }";
        }
        break;
}
