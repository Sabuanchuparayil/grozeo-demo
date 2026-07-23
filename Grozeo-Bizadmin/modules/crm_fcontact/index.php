<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");
require_once(INCLUDE_PATH . "/finascop_accounts_Transactions.php");

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
        $leaddata = array(
            'crle_orgName' => $contact_data['crco_orgName'],
            'crle_location' => $contact_data['crco_location'],
            'crle_orgPincode' => $contact_data['crco_orgPincode'],
            'crle_orgCountry' => $contact_data['crco_orgCountry'],
            'crle_groute' => $contact_data['crco_groute'],
            'crle_glocality' => $contact_data['crco_glocality'],
            'crle_gplace' => $contact_data['crco_gplace'],
            'glatitude' => $contact_data['glatitude'],
            'glongitude' => $contact_data['glongitude'],
            'crle_orgAddress' => $contact_data['crco_orgAddress'],
            'crle_indContactperson' => $contact_data['crco_indContactperson'],
            'crle_indMobile' => $contact_data['crco_indMobile'],
            'crle_orgContactNo' => $contact_data['crco_orgContactNo'],
            'retailCategory' => $contact_data['retailCategory'],
            'crle_orgEmail' => $contact_data['crco_orgEmail'],
            'crle_type' => $contact_data['crco_type'],
            'crmuId' => 2,
            'crle_CreatedOn' => date('Y-m-d H:i:s'),
            'crle_CreatedBy' => $_SESSION['admin']->Finascop_UserId,
            'crle_isActive' => 1,
            'assignedRO' => $getareaRos['roId'],
            'contactId' => $contactId

        );
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

    case 'getContactDetails':
        $rec_limit = empty($_POST['limit']) ? 15 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $allowedFields = ['contact_id', 'contact_name', 'contact_phone', 'contact_email', 'contact_type', 'contact_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
            }
        }
        if ($_POST['type']) {
            $search .= " AND crco_type = {$_POST['type']} ";
        }

        $countDataQuery = "SELECT COUNT(*) FROM finascop_crm_contact fcon  {$search}   ORDER BY id ";
        $count = $db->getItemFromDB($countDataQuery);
        $userId = $_SESSION['admin']->Finascop_UserId;
        $current_date = date("Y-m-d");
        $qry = "SELECT fcc.id, crco_orgName,crco_type, name as contactType, CASE WHEN crco_mode=1 THEN 'Enquiries from the Site or SM campaigns' 
        WHEN crco_mode=2 THEN 'Contacts created through CRM web form' WHEN crco_mode=3 THEN 'Contacts creation through CRM mobile app with current location and photo' 
        WHEN crco_mode=4 THEN 'Contacts created through CRM mobile app with Google address API' END AS contactMode,crco_type,
        CASE WHEN crco_CreatedFrom=1 THEN 'Admin' WHEN crco_CreatedFrom =2 THEN 'BA' WHEN crco_CreatedFrom =3 THEN 'RO' END AS crco_CreatedFrom,
        CASE WHEN crco_CreatedFrom=1 THEN (SELECT FirstName FROM finascop_usr_profile WHERE UserId = crco_CreatedBy) WHEN crco_CreatedFrom =2 THEN (SELECT baName FROM business_associate WHERE id = crco_CreatedBy) WHEN crco_CreatedFrom =3 THEN (SELECT roName FROM relationship_officer WHERE id = crco_CreatedBy) END AS crco_CreatedBy,
        crco_indContactperson, crco_indMobile, crco_orgEmail,crmu_id,crco_isActive,DATE_FORMAT(crco_CreatedOn,'%d-%m-%Y %H:%i:%s') as crco_CreatedOn FROM finascop_crm_contact fcc inner join crm_contact_type cct on cct.id = crco_type {$search} ORDER BY id desc  LIMIT $rec_start,$rec_limit";

        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'loadEditData':
        $statusType = $_POST['EditStatus'];

        if ($statusType == 1) {
            $crco_id = $_POST['_edit_crco_id'];
        } else {
            //  $_GET['crco_id'];
            $crco_id = !empty($_GET['crco_id']) ? $_GET['crco_id'] : 0;
        }

        $QRY = " SELECT * FROM finascop_crm_contact l WHERE l.id=$crco_id";


        $results = $db->getFromDB($QRY, true);
        if ($results['crco_type'] > 0) {
            $crco_type = $db->getItemFromDb("SELECT name from crm_contact_type where id ={$results['crco_type']}");
        }
        $results['crco_typeName'] = $crco_type;
        if ($statusType == 1) {
            if (!empty($results)) {
                echo '{"success":true, "data":', json_encode($results), '}';
            } else
                echo '{"success":true,"data":[]}';
        } else {

            if ($results['retailCategory_isOthers']  == 1) {
                $businessCategory = "(Not Active)";
            } else {
                $businessCategory = "(Active)";
            }
            if($results['retailCategory'] > 0){
                $retailCategory = $db->getItemFromDb("SELECT business_category_name FROM retaline_business_category WHERE business_category_id = {$results['retailCategory']}");
            }
            
            if ($results['crco_mode'] > 0) {
                switch ($results['crco_mode']) {
                    case 1:
                        $crco_mode = 'Enquiries from the Site or SM campaigns';
                        break;
                    case 2:
                        $crco_mode = 'Contacts created through CRM web form';
                        break;
                    case 3:
                        $crco_mode = 'Contacts creation through CRM mobile app with current location and photo';
                        break;
                    case 4:
                        $crco_mode = 'Contacts created through CRM mobile app with Google address API';
                        break;
                }
            }
            if ($crco_id > 0) {
                require(THIS_MODULE_PATH . "/contactview.php");
            }
        }

        break;


    case 'convertToLead':
        $db->query('begin');
        $id = $_POST['crco_id'];
        $status = $_POST['status'];
        if ($status == 1) {
            $enquiry_status = 2;
        }
        $data = array(
            'crmm_IsActive' => $enquiry_status
        );
        $qry = $db->perform(FINASCOP_DB . 'finascop_crm_enquiry', $data, 'update', 'crme_id=' . $id);
        if ($enquiry_type == 1) {
            $convertQry = "SELECT crme_id AS crme_id,{$enquiry_type} AS crco_isIndividual,crme_name AS crco_orgName,
                crme_email AS crco_orgEmail,crme_mobile AS crco_orgprimaryMobile,crme_description as crco_description,
                FROM finascop_crm_enquiry where crme_id=$id";
        } else {
            $indiValue = 2;
            $convertQry = "SELECT crme_id AS crme_id,{$indiValue} AS crco_isIndividual,crme_name AS crco_indContactperson,
                crme_email AS crco_indEmail,crme_mobile AS crco_indPrimaryMobile,crme_description as crco_description 
                FROM finascop_crm_enquiry where crme_id=$id";
        }
        $enquiry_data = $db->getFromDB($convertQry, true);


        $contact_data = $db->perform(FINASCOP_DB . 'finascop_crm_contact', $enquiry_data);
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Updated Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'moveToLead':
        $db->query('begin');
        $contactId = $_POST['contactId'];


        $qry = " SELECT * FROM finascop_crm_contact WHERE id = {$contactId} ";
        $contact_data = $db->getFromDB($qry, true);


        $areaForLead = getAreaForLead($contact_data['glatitude'], $contact_data['glongitude']);
        if ($areaForLead['id'] > 0) {
            assignAreaToLead($contactId, $areaForLead, $contact_data);

            $contactstatus = array(
                'crmu_id' => 2,
                'crco_UpdatedOn' => date('Y-m-d H:i:s'),
                'crco_UpdatedBy' => $_SESSION['admin']->Finascop_UserId,
            );
            $status = $db->perform('finascop_crm_contact', $contactstatus, 'update', 'id=' . $contactId);
            $extMsg = '"This contact is converting to a lead ."';
        } else {
            echo "{success: false, msg: 'Matching area not available..' }";
            exit;
        }



        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:{$extMsg}}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data'}";
        }
        break;
    case 'insertAddContactData':

        $db->query('begin');
        $crco_indContactperson = $_POST['crco_indContactperson'];
        $crco_contact_primarymob = $_POST['crco_indMobile'];
        $crco_indEmail = $_POST['crco_orgEmail'];


        $data = array(
            'crco_orgName' => $_POST['crco_orgName'],
            'crco_location' => $_POST['crco_location'],
            'crco_orgPincode' => $_POST['crco_orgPincode'],
            'crco_orgCountry' => $_POST['crco_orgCountry'],
            'crco_groute' => $_POST['crco_groute'],
            'crco_glocality' => $_POST['crco_glocality'],
            'crco_gplace' => $_POST['crco_gplace'],
            'glatitude' => $_POST['glatitude'],
            'glongitude' => $_POST['glongitude'],
            'crco_orgAddress' => $_POST['crco_orgAddress'],
            'crco_indContactperson' => $_POST['crco_indContactperson'],
            'crco_indMobile' => $_POST['crco_indMobile'],
            'crco_orgContactNo' => $_POST['crco_orgContactNo'],
            'retailCategory' => $_POST['retailCategory'],
            'crco_orgEmail' => $_POST['crco_orgEmail'],
            'crmu_id' => 0

        );
        if ($_POST['id'] > 0) {
            $data['crco_type'] = $_POST['crco_type'];
            $isUnique = $db->getItemSafe("SELECT COUNT(*) FROM finascop_crm_contact WHERE crco_indMobile ='{$crco_indMobile}' AND crco_type = {$data['crco_type']} AND id<>?", "i", [$_POST['id']]);
            if ($isUnique > 0) {
                echo "{success: false,errors: { msg: 'Editing details are already existing.' }}";
                exit;
            } else {


                $data['crco_UpdatedOn'] = date('Y-m-d H:i:s');
                $data['crco_UpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
                $status1 = $db->perform('finascop_crm_contact', $data, 'update', "id = " . intval($_POST['id']));
                $lastid = $_POST['id'];
            }
        } else {
            $crco_types = $_POST['addcrco_type'];
            $crcoTypes = explode(',', $crco_types);
            foreach ($crcoTypes as $crcoType) {
                $isUnique = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_crm_contact WHERE crco_indMobile='{$crco_indMobile}' and crco_type = {$crcoType}");
                if ($isUnique > 0) {
                    echo "{success: false,errors: { msg: 'Contact already existing.' }}";
                    exit;
                } else {
                    $data['crco_type'] = $crcoType;
                    $data['crco_CreatedOn'] = date('Y-m-d H:i:s');
                    $data['crco_CreatedBy'] = $_SESSION['admin']->Finascop_UserId;
                    $status1 = $db->perform('finascop_crm_contact', $data);
                    $lastid = $db->insert_id();
                }
            }
        }


        $status = $db->query('commit');
        $userId = $_SESSION['admin']->Finascop_UserId;
        $current_date = date("Y-m-d");
        $dataqry = "SELECT * FROM finascop_crm_contact fcon WHERE id = {$lastid} ";
        $return_rec = $db->getFromDB($dataqry, true);
        if ($status) {
            echo "{success: true,msg:'Contact Details has been saved successfully',data:" . json_encode($return_rec) . " }";
        } else {
            if ($isUnique > 0) {
                echo "{success: false,errors: { msg: 'Contact already existing.' }}";
                exit;
            } else {
                echo "{success: false,errors: { msg: 'Data not saved successfully' }}";
            }
        }
        break;

    case 'getReference':
        $contact = $_POST['contactid'];
        $qry = "SELECT referers_id,(SELECT crle_indContactperson FROM finascop_crm_lead WHERE crle_id = referers_contact_id) AS referers_contact_id FROM finascop_crm_referers";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
    case 'getContactType':
        $type = $_POST['type'];
        $qry = "SELECT id,name FROM crm_contact_type WHERE status = 1 AND id = {$type}";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
    case 'getRetailCategory':
        $business_category_ingroup = $_POST['business_category_ingroup'];
        if ($business_category_ingroup == '0') {
            $cond = " AND business_category_ingroup = 0 ";
        } else {
            $cond = " AND business_category_ingroup = 1 ";
        }

        $qry = "SELECT business_category_id,business_category_name FROM retaline_business_category WHERE status = 1 AND store_group_id = 0 {$cond}";
        $items = $db->getMulipleData($qry, true);
        $count = count($items);
        if ($business_category_ingroup != '0') {
            $items[$count]['business_category_id'] = -1;
            $items[$count]['business_category_name'] = "Others";
        }
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
    case 'verifyContact':
        $id = $_POST['id'];
        $data['crmu_id'] = 1;
        $data['crco_isActive'] = 1;
        $data['crco_UpdatedOn'] = date('Y-m-d H:i:s');
        $data['crco_UpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $db->query('begin');
        $status = $db->perform('finascop_crm_contact', $data, 'update', " id = {$id}");
        $status = $db->query('commit');
        $dataqry = "SELECT * FROM finascop_crm_contact fcon WHERE id = {$id} ";
        $return_rec = $db->getFromDB($dataqry, true);
        if ($status) {
            echo "{success: true,msg:'Contact verified.',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{success: false,errors: { msg: 'Contact already existing.' }}";
        }
        break;

    case 'insertContactAndMoveToLead':
        $crco_indContactperson = $_POST['crco_indContactperson'];
        $crco_contact_primarymob = $_POST['crco_indMobile'];
        $crco_indEmail = $_POST['crco_orgEmail'];
        if ($_POST['retailCategory_isOthers'] == 1)
            $retailCategory_isOthers = 1;
        else
            $retailCategory_isOthers = 0;
        $data = array(
            'crco_orgName' => $_POST['crco_orgName'],
            'crco_location' => $_POST['crco_location'],
            'crco_orgPincode' => $_POST['crco_orgPincode'],
            'crco_orgCountry' => $_POST['crco_orgCountry'],
            'crco_groute' => $_POST['crco_groute'],
            'crco_glocality' => $_POST['crco_glocality'],
            'crco_gplace' => $_POST['crco_gplace'],
            'glatitude' => $_POST['glatitude'],
            'glongitude' => $_POST['glongitude'],
            'crco_orgAddress' => $_POST['crco_orgAddress'],
            'crco_indContactperson' => $_POST['crco_indContactperson'],
            'crco_indMobile' => $_POST['crco_indMobile'],
            'crco_orgContactNo' => $_POST['crco_orgContactNo'],
            'retailCategory' => $_POST['retailCategory'],
            'crco_orgEmail' => $_POST['crco_orgEmail'],
            'retailCategory_isOthers' => $retailCategory_isOthers

        );
        if ($_POST['id'] > 0) {
            $data['crco_type'] = $_POST['crco_type'];
            $isUnique = $db->getItemSafe("SELECT COUNT(*) FROM finascop_crm_contact WHERE crco_indMobile ='{$crco_indMobile}' AND crco_type = {$data['crco_type']} AND id<>?", "i", [$_POST['id']]);
            if ($isUnique > 0) {
                echo "{success: false, msg: 'Editing details are already existing.'}";
                exit;
            } else {


                $data['crco_UpdatedOn'] = date('Y-m-d H:i:s');
                $data['crco_UpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
                $status1 = $db->perform('finascop_crm_contact', $data, 'update', "id = " . intval($_POST['id']));
                $lastid = $_POST['id'];

                $contactId =  $lastid;
                $vdata['crmu_id'] = 1;
                $vdata['crco_isActive'] = 1;
                $vdata['crco_UpdatedOn'] = date('Y-m-d H:i:s');
                $vdata['crco_UpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
                //$db->query('begin');
                $status = $db->perform('finascop_crm_contact', $vdata, 'update', " id = {$contactId}");

                $qry = " SELECT * FROM finascop_crm_contact WHERE id = {$contactId} ";
                $contact_data = $db->getFromDB($qry, true);

                if ($_POST['retailCategory_isOthers'] == 0) {
                    $areaForLead = getAreaForLead($contact_data['glatitude'], $contact_data['glongitude']);
                    if ($areaForLead['id'] > 0) {
                        assignAreaToLead($contactId, $areaForLead, $contact_data);
                        $contactstatus = array(
                            'crmu_id' => 2,
                            'crco_UpdatedOn' => date('Y-m-d H:i:s'),
                            'crco_UpdatedBy' => $_SESSION['admin']->Finascop_UserId,
                        );
                        $status = $db->perform('finascop_crm_contact', $contactstatus, 'update', 'id=' . $contactId);
                        $extMsg = '"This contact is converting to a lead ."';
                    }
                }
            }
        } else {
            $crco_types = $_POST['addcrco_type'];
            $crcoTypes = explode(',', $crco_types);
            //$db->query('begin');
            foreach ($crcoTypes as $crcoType) {
                $isUnique = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_crm_contact WHERE crco_indMobile='{$crco_indMobile}' and crco_type = {$crcoType}");
                if ($isUnique > 0) {
                    echo "{success: false,msg: 'Contact already existing.'}";
                    exit;
                } else {
                    $data['crco_type'] = $crcoType;
                    $data['crco_CreatedOn'] = date('Y-m-d H:i:s');
                    $data['crco_CreatedBy'] = $_SESSION['admin']->Finascop_UserId;
                    $status1 = $db->perform('finascop_crm_contact', $data);
                    $lastid = $db->insert_id();

                    $contactId =  $lastid;
                    $vdata['crmu_id'] = 1;
                    $vdata['crco_isActive'] = 1;
                    $vdata['crco_UpdatedOn'] = date('Y-m-d H:i:s');
                    $vdata['crco_UpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
                    //$db->query('begin');
                    $status = $db->perform('finascop_crm_contact', $vdata, 'update', " id = {$contactId}");

                    $qry = " SELECT * FROM finascop_crm_contact WHERE id = {$contactId} ";
                    $contact_data = $db->getFromDB($qry, true);
                    if ($_POST['retailCategory_isOthers'] == 0) {

                        $areaForLead = getAreaForLead($data['glatitude'], $data['glongitude']);


                        if ($areaForLead['id'] > 0) {
                            assignAreaToLead($contactId, $areaForLead, $data);
                            $contactstatus = array(
                                'crmu_id' => 2,
                                'crco_UpdatedOn' => date('Y-m-d H:i:s'),
                                'crco_UpdatedBy' => $_SESSION['admin']->Finascop_UserId,
                            );
                            $status = $db->perform('finascop_crm_contact', $contactstatus, 'update', 'id=' . $contactId);
                            $extMsg = '"This contact is converting to a lead ."';
                        }
                    }
                }
            }
        }


        //$status = $db->query('commit');
        $dataqry = "SELECT * FROM finascop_crm_contact fcon WHERE id = {$lastid} ";
        $return_rec = $db->getFromDB($dataqry, true);
        if ($status) {
            echo "{success: true,msg:'Details has been saved successfully',data:" . json_encode($return_rec) . " }";
        } else {
            if ($isUnique > 0) {
                echo "{success: false,msg: 'Contact already existing.'}";
                exit;
            } else {
                echo "{success: false,msg: 'Data not saved successfully'}";
            }
        }
        break;
    case 'listAreaForContact':
        $crco_id = $_POST['crco_id'];
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
        $contactDetails  = $db->getFromDB("SELECT glatitude,glongitude FROM finascop_crm_contact WHERE id = {$crco_id} ", true);
        $qry = "SELECT *, calcDistance({$contactDetails['glatitude']}, {$contactDetails['glongitude']}, areaLatitude, areaLongitude) AS distance FROM `area_entries` HAVING distance <=50 
        ORDER BY distance ASC";

        $items = $db->getMulipleData($qry, true);
        $count = count($items);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'upgradeToLead':
        $db->query('begin');
        $contactId = $_POST['contactId'];
        $areaId = $_POST['areaId'];

        $qry = " SELECT * FROM finascop_crm_contact WHERE id = {$contactId} ";
        $contact_data = $db->getFromDB($qry, true);

        if ($areaId > 0) {
            $areaForLead = $db->getFromDB("SELECT * FROM area_entries WHERE id = {$areaId}", true);
            assignAreaToLead($contactId, $areaForLead, $contact_data);

            $contactstatus = array(
                'crmu_id' => 2,
                'crco_UpdatedOn' => date('Y-m-d H:i:s'),
                'crco_UpdatedBy' => $_SESSION['admin']->Finascop_UserId,
            );
            $status = $db->perform('finascop_crm_contact', $contactstatus, 'update', 'id=' . $contactId);
            $extMsg = '"This contact is converting to a lead ."';
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
}
