<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");
require_once(INCLUDE_PATH . "/finascop_accounts_Transactions.php");
$ICONPATH = DEFICNPATH;
$DATETIMEFORMAT = DATETIMEFORMAT;
$DATEFORMAT = DATEFORMAT;

function getAreaForLead($latitude, $longitude)
{
    global $db;
    $fields['latitude'] = $latitude;
    $fields['longitude'] = $longitude;
    $areaData = [];
    $url =  $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'ASSIGN_AREA'");
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
    //print_r($result);
    if ($result['data']['status'] == 'success') {
        $areaData = $result['data']['data'];
    }
    return $areaData;
}
function assignAreaToLead($contactId, $areaForLead = array(), $contact_data = array())
{
    global $db;
    $getareaRos = $db->getFromDB("SELECT ro.id as roId,COUNT(fcl.id) AS fclCount FROM relationship_officer AS ro 
    LEFT JOIN finascop_crm_lead AS fcl ON fcl.assignedRO = ro.id WHERE ro.roArea = {$areaForLead['id']} ORDER BY fclCount ASC", true);
    $baName = $db->getItemFromDB("SELECT baName FROM business_associate WHERE ID = {$areaForLead['areaBusinessAssociate']}");
    if ($getareaRos['roId'] > 0) {

        $leaddata['assignedRO'] = $getareaRos['roId'];
        $leaddata['isLeadAreaAssigned'] = 1;
        $leaddata['baId'] = $areaForLead['areaBusinessAssociate'];
        $leaddata['baName'] = $baName;
        $leaddata['areaId'] = $areaForLead['id'];
        $leaddata['areaName'] = $areaForLead['areaName'];
        $status = $db->perform('finascop_crm_lead', $leaddata);
    } else {
        echo "{success: false, msg: 'RO s are not available.' }";
        exit;
    }
}
switch ($op) {


    case 'getLeadDetails':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $userId = $_SESSION['admin']->Finascop_UserId;
        $filter = $_POST['filter'];
        $search = " WHERE 1=1 AND crmuId NOT IN (3,7) ";
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
        if ($_POST['type']) {
            $search .= " AND crle_type = {$_POST['type']} ";
        }

        $qry = "SELECT id,crmuId,crle_type,crle_orgName,crle_orgEmail,crle_indMobile,crle_orgPincode,crle_orgCountry,crle_isActive,crle_groute,
        crle_glocality,crle_gplace,isLeadAreaAssigned,baId,baName,areaId,areaName,assignedRO,
        CASE WHEN assignedRO > 0 THEN (SELECT roName FROM relationship_officer WHERE id = assignedRO) ELSE '-' END AS assignedROName,
        CASE WHEN crle_CreatedFrom=1 THEN 'Admin' WHEN crle_CreatedFrom =2 THEN 'BA' WHEN crle_CreatedFrom =3 THEN 'RO' END AS crle_CreatedFrom,
        CASE WHEN crle_CreatedFrom=1 THEN (SELECT FirstName FROM finascop_usr_profile WHERE UserId = crle_CreatedBy) 
        WHEN crle_CreatedFrom =2 THEN (SELECT baName FROM business_associate WHERE id = crle_CreatedBy) 
        WHEN crle_CreatedFrom =3 THEN (SELECT roName FROM relationship_officer WHERE id = crle_CreatedBy) END AS crle_CreatedBy,
        DATE_FORMAT(crle_CreatedOn,'%d-%m-%Y %H:%i:%s') as crle_CreatedOn
         FROM  finascop_crm_lead";
        $listQuery = "SELECT * FROM ({$qry}) AS leadList {$search} LIMIT {$rec_start},{$rec_limit}";
        $items = $db->getMulipleData($listQuery, true);

        $countDataQuery = "SELECT COUNT(*) FROM  ({$qry}) AS countQry {$search} ORDER BY crmuId";
        $count = $db->getItemFromDB($countDataQuery);

        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;




    case 'loadEditLeadData':
        $type = $_POST['EditStatus'];
        $crle_id = $_REQUEST['id'];
        if ($crle_id > 0) {
            $QRY = "SELECT * FROM finascop_crm_lead WHERE id  = {$crle_id}";
            $results = $db->getFromDB($QRY, true);
            $crle_type = $db->getItemFromDB("SELECT name FROM crm_contact_type where id = {$results['crle_type']}");
        }


        if ($type == 1) {
            if (!empty($results)) {
                echo '{"success":true, "data":', json_encode($results), '}';
            } else
                echo '{"success":true,"data":[]}';
        } else {
            if ($crle_id > 0) {

                require(THIS_MODULE_PATH . "/leadsview.php");
            }
        }
        break;
    case 'EditLeadDetails':
        $db->query('begin');
        $db->query('begin');
        $crle_indContactperson = $_POST['crle_indContactperson'];
        $crle_contact_primarymob = $_POST['crle_indMobile'];
        $crle_indEmail = $_POST['crle_orgEmail'];


        $data = array(
            'crle_orgName' => $_POST['crle_orgName'],
            'crle_location' => $_POST['crle_location'],
            'crle_orgPincode' => $_POST['crle_orgPincode'],
            'crle_orgCountry' => $_POST['crle_orgCountry'],
            'crle_groute' => $_POST['crle_groute'],
            'crle_glocality' => $_POST['crle_glocality'],
            'crle_gplace' => $_POST['crle_gplace'],
            'glatitude' => $_POST['glatitude'],
            'glongitude' => $_POST['glongitude'],
            'crle_orgAddress' => $_POST['crle_orgAddress'],
            'crle_indContactperson' => $_POST['crle_indContactperson'],
            'crle_indMobile' => $_POST['crle_indMobile'],
            'crle_orgContactNo' => $_POST['crle_orgContactNo'],
            'retailCategory' => $_POST['retailCategory'],
            'crle_orgEmail' => $_POST['crle_orgEmail']

        );
        $areaForLead = getAreaForLead($_POST['glatitude'], $_POST['glongitude']);
        if ($_POST['id'] > 0) {
            $isLeadAreaAssigned = $db->getItemSafe("SELECT isLeadAreaAssigned FROM finascop_crm_lead WHERE ID = ?", "i", [$_POST['id']]);
            if ($isLeadAreaAssigned == 0) {
            }
            $data['crle_type'] = $_POST['crle_type'];
            $isUnique = $db->getItemSafe("SELECT COUNT(*) FROM finascop_crm_lead WHERE crle_indMobile ='{$crle_indMobile}' AND crle_type = {$data['crle_type']} AND id<>?", "i", [$_POST['id']]);
            if ($isUnique > 0) {
                echo "{success: false,errors: { msg: 'Editing details are already existing.' }}";
                exit;
            } else {


                $data['crle_UpdatedOn'] = date('Y-m-d H:i:s');
                $data['crle_UpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
                $status1 = $db->perform('finascop_crm_lead', $data, 'update', "id = " . intval($_POST['id']));
                $lastid = $_POST['id'];
            }
        } else {
            $crle_types = $_POST['addcrle_type'];
            $crcoTypes = explode(',', $crle_types);
            foreach ($crcoTypes as $crcoType) {
                $isUnique = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_crm_lead WHERE crle_indMobile='{$crle_indMobile}' and crle_type = {$crcoType}");
                if ($isUnique > 0) {
                    echo "{success: false,errors: { msg: 'Mobile already existing.' }}";
                    exit;
                } else {
                    if ($areaForLead['id'] > 0) {
                        $getareaRos = $db->getFromDB("SELECT ro.id as roId,COUNT(fcl.id) AS fclCount FROM relationship_officer AS ro 
    LEFT JOIN finascop_crm_lead AS fcl ON fcl.assignedRO = ro.id WHERE ro.roArea = {$areaForLead['id']} ORDER BY fclCount ASC", true);
                        $baName = $db->getItemFromDB("SELECT baName FROM business_associate WHERE ID = {$areaForLead['areaBusinessAssociate']}");
                        if ($getareaRos['roId'] > 0) {
                            $data['isLeadAreaAssigned'] = 1;
                            $data['baId'] = $areaForLead['areaBusinessAssociate'];
                            $data['baName'] = $baName;
                            $data['areaId'] = $areaForLead['id'];
                            $data['areaName'] = $areaForLead['areaName'];
                        }
                    }
                    $data['crle_type'] = $crcoType;
                    $data['crle_CreatedOn'] = date('Y-m-d H:i:s');
                    $data['crle_CreatedBy'] = $_SESSION['admin']->Finascop_UserId;
                    $status1 = $db->perform('finascop_crm_lead', $data);
                    $lastid = $db->insert_id();
                }
            }
        }


        $status = $db->query('commit');
        $userId = $_SESSION['admin']->Finascop_UserId;
        $current_date = date("Y-m-d");
        $dataqry = "SELECT * FROM finascop_crm_lead fcon WHERE id = {$lastid} ";
        $return_rec = $db->getFromDB($dataqry, true);
        if ($status) {
            echo "{success: true,msg:'Lead Details has been saved successfully',data:" . json_encode($return_rec) . " }";
        } else {
            if ($isUnique > 0) {
                echo "{success: false,errors: { msg: 'Lead already existing.' }}";
                exit;
            } else {
                echo "{success: false,errors: { msg: 'Data not saved successfully' }}";
            }
        }
        break;


    case 'getReference':
        $contact = $_POST['contactid'];
        $qry = "SELECT referers_id,referers_name AS referers_contact_id FROM finascop_crm_referers";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
    case 'uploadcsvFile':
        $referers_ids = $_POST['referers_ids'];
        $file = $_FILES['excel_file']['tmp_name'];
        $newPath = str_replace('tmp', 'dev/shm', $file);
        copy($file, $newPath);
        $row = 0;
        $csvData = array();
        if (($handle = fopen($newPath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                $csvData[$row] = $data;

                $num = count($data);
                $row++;
            }
            fclose($handle);
        }
        $db->query('begin');

        foreach ($csvData as $key => $value) {
            if ($key > 0) {
                $value[0] = str_replace("'", "\'", $value[0]);
                $value[1] = str_replace("'", "\'", $value[1]);
                $value[2] = str_replace("'", "\'", $value[2]);
                $lead['crle_indContactperson'] = $value[0];
                $lead['crle_indPrimaryMobile'] = $value[1];
                $lead['crle_indEmail'] = $value[2];
                $lead['crle_reference'] = $referers_ids;
                $lead['crle_CreatedOn'] = date("Y-m-d H:i:s");
                $lead['crle_CreatedBy'] = $_SESSION['admin']->Finascop_UserId;
                $n = 5;
                $uniqueId = getName($n);
                $lead['uniqueId'] = $uniqueId;
                $dupCategory = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer WHERE cust_mobile = '{$value[1]}'");
                if ($dupCategory < 1) {
                    $status = $db->perform("finascop_crm_lead", $lead);

                    $lastId = $db->insert_id();
                    $refer_data = array(
                        'reference_cl_id' => $lastId,
                        'refrence_referers_id' => $referers_ids,
                        'reference_from' => 1
                    );
                    $status5 = $db->perform(FINASCOP_DB . 'finascop_crm_reference', $refer_data);
                    $data = array(
                        'crlh_type' => 'U',
                        'crle_id' => $lastId,
                        'crle_indContactperson' => $value[0],
                        'crle_indPrimaryMobile' => $value[1],
                        'crle_indEmail' => $value[2],
                        'crle_referedBy_status' => 0,
                        'crle_reference' => $referers_ids,
                        'crle_CreatedOn' => date("Y-m-d H:i:s"),
                        'uniqueId' => $uniqueId,
                        'crle_CreatedBy' => $_SESSION['admin']->Finascop_UserId
                    );
                    $status_history = $db->perform(FINASCOP_DB . 'finascop_crm_lead_history', $data);
                }
            }
        }

        $status = $db->query('commit');
        if ($status == 1) {
            echo '{"success":true,"valid":true}';
        } else {
            // var_dup($error);
            echo '{"success":false,"valid":false}';
        }

        break;
    case 'assignAreaManually':
        $leadId = $_POST['leadId'];
        $getLeadDetails = $db->getFromDB("SELECT glatitude,glongitude FROM finascop_crm_lead WHERE id = {$leadId}", true);
        $fields['latitude'] = $getLeadDetails['glatitude'];
        $fields['longitude'] = $getLeadDetails['glongitude'];
        $areaData = [];
        $url =  $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'ASSIGN_AREA'");
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
        //print_r($result);
        if ($result['data']['status'] == 'success') {
            $areaData = $result['data']['data'];
        }
        if ($areaForLead['baId'] > 0) {
            $leaddata['isLeadAreaAssigned'] = 1;
            $leaddata['baId'] = $areaForLead['baId'];
            $leaddata['baName'] = $areaForLead['baName'];
            $leaddata['areaId'] = $areaForLead['areaId'];
            $leaddata['areaName'] = $areaForLead['areaName'];
        } else {
            $leaddata['isLeadAreaAssigned'] = 0;
        }
        $db->query('begin');
        $status = $db->perform('finascop_crm_lead', $leaddata, 'update', " id = {$leadId}");
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg: 'Area assigned to lead.' }";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;

    case 'getCrmStatus':
        $qry = "select crmu_id,crmu_name from finascop_crm_status  WHERE crmu_IsDefault = 1 order by crmu_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'convertLeadtoProspect':
        $leadId = $_POST['leadId'];
        $leadUpdate['crmuId'] = $_POST['crmStatus'];
        $leadUpdate['crmFollowupDate'] = date('Y-m-d', strtotime($_POST['crmFollowupDate']));
        $leadUpdate['crmRemarks'] = $_POST['crmRemarks'];

        $crmCmmn['crle_id'] = $leadId;
        $crmCmmn['crmu_id'] = $_POST['crmStatus'];
        $crmCmmn['crmc_remark'] = $_POST['crmRemarks'];

        $db->query('begin');
        $status = $db->perform('finascop_crm_communication', $crmCmmn);
        $status = $db->perform('finascop_crm_lead', $leadUpdate, 'update', " id = {$leadId}");
        $leadData = $db->getFromDB("SELECT * FROM finascop_crm_lead WHERE id = {$leadId}", true);
        switch ($_POST['crmStatus']) {
            case 3:
                if ($leadData['crle_type'] == 1) {
                    $proData['crpr_orgName'] = $leadData['crle_orgName'];
                    $proData['crpr_mode'] = $leadData['crle_mode'];
                    $proData['crpr_type'] = $leadData['crle_type'];
                    $proData['crpr_description'] = $leadData['crle_description'];
                    $proData['crpr_location'] = $leadData['crle_location'];
                    $proData['crpr_orgPincode'] = $leadData['crle_orgPincode'];
                    $proData['crpr_orgCountry'] = $leadData['crle_orgCountry'];
                    $proData['crpr_groute'] = $leadData['crle_groute'];
                    $proData['crpr_glocality'] = $leadData['crle_glocality'];
                    $proData['crpr_gplace'] = $leadData['crle_gplace'];
                    $proData['glatitude'] = $leadData['glatitude'];
                    $proData['glongitude'] = $leadData['glongitude'];
                    $proData['crpr_orgAddress'] = $leadData['crle_orgAddress'];
                    $proData['crpr_indContactperson'] = $leadData['crle_indContactperson'];
                    $proData['crpr_indMobile'] = $leadData['crle_indMobile'];
                    $proData['crpr_orgContactNo'] = $leadData['crle_orgContactNo'];
                    $proData['retailCategory'] = $leadData['retailCategory'];
                    $proData['crpr_orgEmail'] = $leadData['crle_orgEmail'];
                    $proData['leadId'] = $leadId;
                    $proData['baId'] = $leadData['baId'];
                    $proData['assignedRO'] = $leadData['assignedRO'];
                    $proData['baName'] = $leadData['baName'];
                    $proData['areaId'] = $leadData['areaId'];
                    $proData['areaName'] = $leadData['areaName'];
                    $proData['crmuId'] = $leadData['crmuId'];
                    $invitationCode = substr(strtoupper(md5('now()' . $leadData['crle_orgEmail'])), 0, 4);
                    $proData['invitationCode'] = generateRandomString(4);
                    $status = $db->perform('finascop_crm_prospect', $proData);
                }

                $message = "Lead converted to Prospect.";
                break;
            case 4:
                $message = "Lead is moved to Pending status.";
                break;
            case 5:
                $message = "Lead is in Delegated status.";
                break;
            case 6:
                $message = "Lead is in Dubious status.";
                break;
            case 7:
                $message = "Lead is in Lost Lead status.";
                break;
            case 8:
                $message = "Lead is in Bad Fit status.";
                break;
            case 10:
                $sgId = $_POST['sgId'];
                $proData['crpr_orgName'] = $leadData['crle_orgName'];
                $proData['crpr_mode'] = $leadData['crle_mode'];
                $proData['crpr_type'] = $leadData['crle_type'];
                $proData['crpr_description'] = $leadData['crle_description'];
                $proData['crpr_location'] = $leadData['crle_location'];
                $proData['crpr_orgPincode'] = $leadData['crle_orgPincode'];
                $proData['crpr_orgCountry'] = $leadData['crle_orgCountry'];
                $proData['crpr_groute'] = $leadData['crle_groute'];
                $proData['crpr_glocality'] = $leadData['crle_glocality'];
                $proData['crpr_gplace'] = $leadData['crle_gplace'];
                $proData['glatitude'] = $leadData['glatitude'];
                $proData['glongitude'] = $leadData['glongitude'];
                $proData['crpr_orgAddress'] = $leadData['crle_orgAddress'];
                $proData['crpr_indContactperson'] = $leadData['crle_indContactperson'];
                $proData['crpr_indMobile'] = $leadData['crle_indMobile'];
                $proData['crpr_orgContactNo'] = $leadData['crle_orgContactNo'];
                $proData['retailCategory'] = $leadData['retailCategory'];
                $proData['crpr_orgEmail'] = $leadData['crle_orgEmail'];
                $proData['leadId'] = $leadId;
                $proData['baId'] = $leadData['baId'];
                $proData['assignedRO'] = $leadData['assignedRO'];
                $proData['baName'] = $leadData['baName'];
                $proData['areaId'] = $leadData['areaId'];
                $proData['areaName'] = $leadData['areaName'];
                $proData['crmuId'] = $leadData['crmuId'];
                $invitationCode = substr(strtoupper(md5('now()' . $leadData['crle_orgEmail'])), 0, 4);
                $proData['invitationCode'] = generateRandomString(4);
                $proData['storeGroupId'] = $sgId;
                $status = $db->perform('finascop_crm_prospect', $proData);
                $prospect_Id = $db->insert_id();

                $sgdata['store_group_grosmartMerchant'] = 1;
                $sgdata['updated_on'] = date('Y-m-d H:i:s');
                $sgdata['updated_by'] = $_SESSION['admin']->Finascop_UserId;
                $sgdata['prospect_Id'] = $prospect_Id;
                $status = $db->perform("finascop_branch_group", $sgdata, 'update', 'store_group_id =' . $sgId);

                $message = "Converted to GroSmart Merchant.";
                break;
        }

        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg: '" . $message . "' }";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;
    case 'loadEditData':
        $statusType = $_POST['EditStatus'];

        if ($statusType == 1) {
            $crle_id = $_POST['_edit_crle_id'];
        } else {
            //  $_GET['crle_id'];
            $crle_id = !empty($_GET['crle_id']) ? $_GET['crle_id'] : 0;
        }

        $QRY = " SELECT * FROM finascop_crm_lead l WHERE l.id=$crle_id";


        $results = $db->getFromDB($QRY, true);
        if ($results['crle_type'] > 0) {
            $crle_type = $db->getItemFromDb("SELECT name from crm_contact_type where id ={$results['crle_type']}");
        }
        $results['crle_typeName'] = $crle_type;
        if ($statusType == 1) {
            if (!empty($results)) {
                echo '{"success":true, "data":', json_encode($results), '}';
            } else
                echo '{"success":true,"data":[]}';
        } else {

            if ($results['retailCategory'] >= 0) {
                if ($results['retailCategory'] == 0) {
                    $retailCategory = "Others";
                } else {
                    $retailCategory = $db->getItemFromDb("SELECT business_category_name FROM retaline_business_category WHERE business_category_id = {$results['retailCategory']}");
                }
            }
            if ($results['crle_mode'] > 0) {
                switch ($results['crle_mode']) {
                    case 1:
                        $crle_mode = 'Enquiries from the Site or SM campaigns';
                        break;
                    case 2:
                        $crle_mode = 'Contacts created through CRM web form';
                        break;
                    case 3:
                        $crle_mode = 'Contacts creation through CRM mobile app with current location and photo';
                        break;
                    case 4:
                        $crle_mode = 'Contacts created through CRM mobile app with Google address API';
                        break;
                }
            }
            if ($crle_id > 0)
                require(THIS_MODULE_PATH . "/contactview.php");
        }

        break;
    case 'getContactType':
        $qry = "SELECT id,name FROM crm_contact_type WHERE status = 1";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
    case 'getRetailCategory':
        $qry = "SELECT business_category_id,business_category_name FROM retaline_business_category WHERE status = 1";
        $items = $db->getMulipleData($qry, true);
        $count = count($items);
        $items[$count]['business_category_id'] = 0;
        $items[$count]['business_category_name'] = "Others";
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
    case 'listAreaForLead':
        $crle_id = $_POST['crle_id'];
        $typeName = $_POST['typeName'];
        $isLeadAreaAssigned = $_POST['isLeadAreaAssigned'];
        $search = " WHERE areaBusinessAssociate > 0 ";
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
        $contactDetails  = $db->getFromDB("SELECT glatitude,glongitude,areaId FROM finascop_crm_lead WHERE id = {$crle_id} ", true);
        $qry = "SELECT *, calcDistance({$contactDetails['glatitude']}, {$contactDetails['glongitude']}, areaLatitude, areaLongitude) AS distance,IF(id = {$contactDetails['areaId']},1,0) as currentArea FROM `area_entries` HAVING distance <=15 
            ORDER BY distance ASC";

        $items = $db->getMulipleData($qry, true);
        $count = count($items);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'scheduleMeetings':
        $data['crmUserId'] = $_POST['leadId'];
        $data['meetingDate'] = date('Y-m-d', strtotime($_POST['crmscheduleDate']));
        $data['meetingTime'] = date('H:i', strtotime($_POST['crmscheduleTime']));
        $data['meetingRemarks'] = $_POST['crmScheduleRemarks'];
        $db->query('begin');
        $status = $db->perform('crm_meetings', $data);
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg: '" . $message . "' }";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;
    case 'delegateLeadOld':
        $db->query('begin');
        $LeadId = $_POST['crle_id'];
        $areaId = $_POST['areaId'];

        $qry = " SELECT * FROM finascop_crm_lead WHERE id = {$LeadId} ";
        $contact_data = $db->getFromDB($qry, true);

        if ($areaId > 0) {
            $areaForLead = $db->getFromDB("SELECT * FROM area_entries WHERE id = {$areaId}", true);
            assignAreaToLead($LeadId, $areaForLead, $contact_data);


            $extMsg = '"Area assigned to lead ."';
        } else {
            echo "{success: false, msg: 'Select Area and proceed' }";
            exit;
        }

        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:{$extMsg}}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data'}";
        }
        break;
    case 'loadSurveyDetails':
        $leadId = $_REQUEST['leadId'];
        $surveyDetails = $db->getMultipleData("SELECT 
        (SELECT crle_orgName FROM finascop_crm_lead WHERE id = crm_user_id) AS crm_user_name,
        co.id AS option_id,cr.created_at as responseDate,
           cq.id AS question_id,   
           co.answer AS option_text,
           CASE
               WHEN co.id = cr.answer_id THEN cq.question
               ELSE ''
           END AS question_text,
           CASE
               WHEN co.id = cr.answer_id THEN 1
               ELSE 0
           END AS isCorrect,
           co.answer AS user_answer
       FROM
           crm_survey_questions cq
       LEFT JOIN
           crm_survey_options co ON cq.id = co.question_id
       LEFT JOIN
           crm_survey_responses cr ON cq.id = cr.question_id AND crm_user_id = {$leadId} AND crm_user_type = 'lead'
       WHERE
           cq.crm_type = 'lead'
       ORDER BY
       cq.id, co.id,(co.id = cr.answer_id)", true);
        ob_start();
        include('loadSurvey.php');
        $resHtml = ob_get_contents();
        ob_end_clean();
        echo $resHtml;
        exit();
        break;
    case 'listROforArea':
        $areaId = $_POST['areaId'];
        if ($areaId > 0) {
            $qry = "select id,roName from relationship_officer where roArea = {$areaId} order by roName";
            $items = $db->getMultipleData($qry, true);
        }

        $count = count($items);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listLeadArea':

        $crle_id = $_POST['crle_id'];
        $typeName = $_POST['typeName'];
        $isLeadAreaAssigned = $_POST['isLeadAreaAssigned'];

        $areaId = $_POST['areaId'];
        if ($crle_id > 0) {
            $contactDetails  = $db->getFromDB("SELECT glatitude,glongitude,areaId FROM finascop_crm_lead WHERE id = {$crle_id} ", true);
            $qry = "SELECT *, calcDistance({$contactDetails['glatitude']}, {$contactDetails['glongitude']}, areaLatitude, areaLongitude) AS distance,IF(id = {$contactDetails['areaId']},1,0) as currentArea FROM `area_entries` HAVING distance <=15 
            ORDER BY distance ASC";
            $data = $db->getMultipleData($qry, true);
        }

        if (!empty($data)) {
            echo json_encode($data);
        } else
            echo [];
        break;
    case 'delegateLead':
        $db->query('begin');
        $LeadId = $_POST['crle_id'];
        $areaId = $_POST['areaId'];
        $roId = $_POST['roId'];

        $qry = " SELECT * FROM finascop_crm_lead WHERE id = {$LeadId} ";
        $contact_data = $db->getFromDB($qry, true);
        $db->query('begin');
        if ($areaId > 0 && $roId > 0) {
            $newData['areaId'] = $areaId;
            $newData['assignedRO'] = $roId;
            $newData['areaName'] =  $_POST['areaName'];

            $status = $db->perform('finascop_crm_lead', $newData, 'update', " id = {$LeadId}");
            $extMsg = '"Delegate area to lead ."';
        } else {
            echo "{success: false, msg: 'Select Area and proceed' }";
            exit;
        }

        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:{$extMsg}}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data'}";
        }
        break;
    case 'listMerchants':
        $sgName = $_POST['sgName'];
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'store_group_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE status = 1 ";
        $searchitem = '';
        if (!empty($sgName)) {
            $searchitem .= " AND  store_group_name LIKE '%{$sgName}%'";
        }

        //if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
        $countQuery = "SELECT COUNT(*) FROM finascop_branch_group  {$search}{$searchitem} ";
        $listQuery = "SELECT a.store_group_id,store_group_name,IF((status=1),'Active','Inactive') AS status,contactNumber,
            IF(store_group_grosmartMerchant = 1,'YES','NO') AS grosmartMerchant,store_group_grosmartMerchant FROM finascop_branch_group a {$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
}
