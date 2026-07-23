<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");
require_once(INCLUDE_PATH . "/finascop_accounts_Transactions.php");
$DATETIMEFORMAT = DATETIMEFORMAT;
$DATEFORMAT = DATEFORMAT;
$ICONPATH = DEFICNPATH;
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

        $countDataQuery = "SELECT COUNT(*) FROM finascop_crm_contact fcon  WHERE  ((fcon.crco_isScheduleAlive = 1 AND (fcon.crmu_id =5 OR fcon.crmu_id =4) ) OR fcon.crmu_id = 1  "
                . "OR fcon.crmu_id=2 OR fcon.crmu_id=3) ORDER BY fcon.crmu_id ";
        $count = $db->getItemFromDB($countDataQuery);
        $userId = $_SESSION['admin']->Finascop_UserId;
        $current_date = date("Y-m-d");
        $qry = "SELECT * FROM (SELECT fcon.crco_id, crco_indContactperson AS contact_name,crco_indEmail AS email,"
                . "crco_indPrimaryMobile AS mobile,IF(crco_userId!= 0, IF(crco_userId=$userId ,'FORME','FOROTHER'), 'NONE') AS ABO, "
                . "'Individual' AS TYPE, CASE WHEN fcon.crmu_id = 1 THEN 'UnAttended' WHEN fcon.crmu_id = 2 THEN 'Assigned' WHEN fcon.crmu_id = 5 THEN 'Call Later' "
                . "WHEN fcon.crmu_id = 4 THEN 'Not Interested' ELSE 'Interested' END AS STATUS, crco_userId,crco_isActive FROM finascop_crm_contact fcon "
                . "WHERE ((fcon.crco_isScheduleAlive = 1 AND (fcon.crmu_id =5 OR fcon.crmu_id =4) ) OR fcon.crmu_id = 1  OR fcon.crmu_id=2 OR fcon.crmu_id=3) ) AS SA {$search} "
                . "ORDER BY (CASE WHEN STATUS = 'Assigned' THEN 'ASC' ELSE STATUS END)  LIMIT $rec_start,$rec_limit";

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

        $QRY = " SELECT crco_id AS customerId,crco_userId,referedBy_status,reference_status,crco_reference,
        crco_indContactperson AS textfieldContactDetailsContactPerson,
        crco_indPrimaryMobile AS numberfieldContactDetailsprimaryMobile,
        crco_indEmail AS textfieldContactDetailsEmail,
        crco_description AS textareaContactDescription,
        crmu_id
    FROM finascop_crm_contact l
    WHERE l.crco_id=$crco_id";
//        }
        $results = $db->getFromDB($QRY, true);

        if ($statusType == 1) {
            if (!empty($results)) {
                echo '{"success":true, "data":', json_encode($results), '}';
            } else
                echo '{"success":true,"data":[]}';
        }
        else {
            require(THIS_MODULE_PATH . "/contactview.php");
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
    case 'insertCommunicationData':
        $db->query('begin');
        $contactId = $_POST['contactId'];
        
        $contactdata = array(
            'crmu_id' => 3,
            'crco_userId' => 0
        );
        $status3 = $db->perform(FINASCOP_DB . 'finascop_crm_contact', $contactdata, 'update', 'crco_id=' . $contactId);
        $lead_value = 1;

        $qry = " SELECT crco_id AS crco_id, crco_isIndividual  AS crle_isIndividual,crco_indContactperson  AS crle_indContactperson,crco_indMobile AS crle_indMobile,"
                . "referedBy_status AS crle_referedBy_status,reference_status AS crle_refered_status,crco_reference AS crle_reference,crco_indEmail  AS crle_indEmail,"
                . "crco_indPrimaryMobile AS crle_indPrimaryMobile,$lead_value AS crmu_id FROM finascop_crm_contact WHERE crco_id = $contactId ";
        $contact_data = $db->getFromDB($qry, true);
        
        $n = 5;
        $uniqueId = getName($n);
        $data['uniqueId'] = $uniqueId;
        $lead_status = $db->perform(FINASCOP_DB . 'finascop_crm_lead', $contact_data, $uniqueId);
        $lastId = $db->insert_id();
        if ($contact_data['crle_refered_status'] == 1) {
            $reference_data = array(
                'referers_contact_id' => $lastId,
                'referers_from' => 2,
                'referers_created_on' => date('Y-m-d H:i:s'),
                'referers_created_by' => $_SESSION['admin']->Finascop_UserId,
            );
            //echo "SELECT COUNT(*) FROM finascop_crm_referers WHERE referers_contact_id={$reference_data['referers_contact_id']}";
            $qry_ref = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_crm_referers WHERE referers_contact_id={$reference_data['referers_contact_id']} AND referers_from=2");
            if ($qry_ref == 0) {
                $status = $db->perform(FINASCOP_DB . 'finascop_crm_referers', $reference_data);
            }
        }
//        if ($contact_data['crle_refered_status']!= '') {
//            $referen = explode(",", $contact_data['crle_refered_status']);
//            $count_ref = count($referen);
//            $del_ref = $db->query("delete FROM finascop_crm_reference WHERE reference_cl_id={$lastid} AND reference_from=1");
//            for ($j = 0; $j < $count_ref; $j++) {
//                $refer_data = array(
//                    'reference_cl_id' => $lastid,
//                    'refrence_referers_id' => $referen[$j],
//                    'reference_from' => 0
//                );
//                $status5 = $db->perform(FINASCOP_DB . 'finascop_crm_reference', $refer_data);
//            }
//        }
         $n = 5;
        $uniqueId = getName($n);
        $data['uniqueId'] = $uniqueId;
        $contact_data += ['crlh_type' => 'I', 'crle_id' => $lastId];
        $lead_history_status = $db->perform(FINASCOP_DB . 'finascop_crm_lead_history', $contact_data, $uniqueId);
        $contactstatus = array(
            'crmu_id' => '6',
        );
        $status = $db->perform(FINASCOP_DB . 'finascop_crm_contact', $contactstatus, 'update', 'crco_id=' . $contactId);
        $extMsg = '"This contact is converting to a lead ."';

        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:{$extMsg}}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'insertAddContactData':
        $db->query('begin');
        $crco_indContactperson = $_POST['textfieldContactDetailsContactPerson'];
        $crco_contact_primarymob = $_POST['numberfieldContactDetailsprimaryMobile'];
        $crco_indEmail = $_POST['textfieldContactDetailsEmail'];
        $reference = $_POST['referers_id'];
//        $refered_by = $_POST['refered_by'];
//        if ($refered_by == 'Yes') {
//            $refBy = 1;
//        }
//        else{
//            $refBy = 0;
//        }
        $referer_id = $_POST['referer_id'];
        if ($referer_id == 'Yes') {
            $refId = 1;
        } else {
            $refId = 0;
        }

        $data = array(
            'crco_indContactperson' => $crco_indContactperson,
            'crco_indPrimaryMobile' => $crco_contact_primarymob,
            'crco_indEmail' => $crco_indEmail,
            'referedBy_status' => 0,
            'reference_status' => $refId,
            'crco_reference' => $reference,
        );
        if ($_POST['customerId'] > 0) {
            $isUnique = $db->getItemSafe("SELECT COUNT(*) FROM finascop_crm_contact WHERE (crco_indPrimaryMobile='{$crco_contact_primarymob}' OR crco_indEmail='{$crco_indEmail}') AND crco_id<>?", "i", [$_POST['customerId']]);
            if ($isUnique > 0) {
                echo "{success: false,errors: { msg: 'Editing details are already existing.' }}";
                exit;
            } else {
                $status1 = $db->perform(FINASCOP_DB . 'finascop_crm_contact', $data, 'update', "crco_id = " . intval($_POST['customerId']));
                $lastid = $_POST['customerId'];
            }
        } else {
            $isUnique = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_crm_contact WHERE crco_indPrimaryMobile='{$crco_contact_primarymob}' OR crco_indEmail='{$crco_indEmail}'");
            if ($isUnique > 0) {
                echo "{success: false,errors: { msg: 'Contact already existing.' }}";
                exit;
            } else {
                $status1 = $db->perform(FINASCOP_DB . 'finascop_crm_contact', $data);
                $lastid = $db->insert_id();
            }
        }
        $history_data = array(
            'crch_type' => 'I',
            'crco_id' => $lastid,
            'crco_indContactperson' => $crco_indContactperson,
            'crco_indPrimaryMobile' => $crco_contact_primarymob,
            'crco_indEmail' => $crco_indEmail,
            'referedBy_status' => 0,
            'reference_status' => $refId,
            'crco_reference' => $reference,
            'crco_CreatedOn' => date('Y-m-d H:i:s'),
        );
//        if ($reference !== '') {
//            $referen = explode(",", $reference);
//            $count_ref = count($referen);
//            $del_ref = $db->query("delete FROM finascop_crm_reference WHERE reference_cl_id={$lastid} AND reference_from=0");
//            for ($j = 0; $j < $count_ref; $j++) {
//                $refer_data = array(
//                    'reference_cl_id' => $lastid,
//                    'refrence_referers_id' => $referen[$j],
//                    'reference_from' => 0
//                );
//                $status5 = $db->perform(FINASCOP_DB . 'finascop_crm_reference', $refer_data);
//            }
//        }
        $status2 = $db->perform(FINASCOP_DB . 'finascop_crm_contact_history', $history_data);
//        if ($referer_id == 'Yes') {
//            $reference_data = array(
//                'referers_contact_id' => $lastid,
//                'referers_from' => 1,
//                'referers_created_on' => date('Y-m-d H:i:s'),
//                'referers_created_by' => $_SESSION['admin']->Finascop_UserId,
//            );
//            //echo "SELECT COUNT(*) FROM finascop_crm_referers WHERE referers_contact_id={$reference_data['referers_contact_id']}";
//            $qry_ref = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_crm_referers WHERE referers_contact_id={$reference_data['referers_contact_id']} AND referers_from=1");
//            if ($qry_ref == 0) {
//                $status = $db->perform(FINASCOP_DB . 'finascop_crm_referers', $reference_data);
//            }
//        }
        $status = $db->query('commit');
        $userId = $_SESSION['admin']->Finascop_UserId;
        $current_date = date("Y-m-d");
        $dataqry = "SELECT fcon.crco_id, crco_indContactperson AS contact_name,IF(fcon.crmu_id=1,'XXXX' , crco_indEmail) AS email,IF(fcon.crmu_id=1,'XXXX' , crco_indPrimaryMobile) AS mobile,"
                . "IF(crco_userId!= 0, IF(crco_userId=$userId ,'FORME','FOROTHER'), 'NONE') AS ABO, 'Individual' AS TYPE,CASE WHEN fcon.crmu_id = 1 THEN 'UnAttended' "
                . "WHEN fcon.crmu_id = 2 THEN 'Assigned' WHEN fcon.crmu_id = 5 THEN 'Call Later' WHEN fcon.crmu_id = 4 THEN 'Not Interested' ELSE 'Interested' END AS STATUS, "
                . "crco_userId,crco_isActive FROM finascop_crm_contact fcon WHERE crco_id = {$lastid}  AND ((fcon.crco_isScheduleAlive = 1  AND (fcon.crmu_id =5 OR fcon.crmu_id =4) ) "
                . "OR fcon.crmu_id = 1  OR fcon.crmu_id=2 OR fcon.crmu_id=3)";
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
}

