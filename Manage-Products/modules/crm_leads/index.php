<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");
require_once(INCLUDE_PATH . "/finascop_accounts_Transactions.php");
$ICONPATH = DEFICNPATH;
$DATETIMEFORMAT = DATETIMEFORMAT;
$DATEFORMAT = DATEFORMAT;
switch ($op) {


    case 'getLeadDetails':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $userId = $_SESSION['admin']->Finascop_UserId;
        $allowedFields = ['lead_id', 'lead_name', 'lead_phone', 'lead_email', 'lead_source', 'lead_status', 'lead_created_on'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
            }
        }

        $qry = "SELECT * FROM (SELECT l.crle_id AS id,l.crle_id, crle_indContactperson AS lead_name,crle_indEmail AS email,
        crle_indPrimaryMobile AS mobile,IF(crle_userId!= 0, IF(crle_userId=$userId,'FORME','FOROTHER'), 'NONE') AS ABO,
        'Individual' AS TYPE,CASE WHEN l.crmu_id = 1 THEN 'UnAttended' WHEN l.crmu_id = 2 THEN 'Assigned' WHEN l.crmu_id = 5 THEN 'Call Later'
         WHEN l.crmu_id = 4 THEN 'Not Interested' ELSE 'Interested' END AS STATUS, crle_userId,crle_isActive
         FROM finascop_crm_lead l
         WHERE
         ((l.crle_isScheduleAlive = 1 AND (l.crmu_id =5 OR l.crmu_id =4) ) OR l.crmu_id = 1  OR l.crmu_id=2 or l.crmu_id = 3) ) AS SA {$search}
        ORDER BY (CASE
                  WHEN STATUS = 'Assigned' THEN
                   'ASC'
                  ELSE
                   STATUS
                END)
        LIMIT $rec_start,$rec_limit";
        $items = $db->getMulipleData($qry, true);

        $countDataQuery = "SELECT COUNT(*) FROM  finascop_crm_lead l WHERE
        ((l.crle_isScheduleAlive = 1 AND (l.crmu_id =5 OR l.crmu_id =4) ) OR l.crmu_id = 1  OR l.crmu_id=2 OR l.crmu_id=3)
        ORDER BY l.crmu_id";
        $count = $db->getItemFromDB($countDataQuery);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;




    case 'loadEditLeadData':
        $type = $_POST['EditStatus'];
        if ($type == 1) {
            $crle_id = $_POST['_edit_crco_id'];
        } else {
            $crle_id = ((empty($_GET['crle_id'])) || $_GET['crle_id'] == 'undefined') ? 0 : $_GET['crle_id'];
        }

        $QRY = "SELECT crle_id AS lead_customerId,crle_userId,crle_indContactperson AS textfieldMarketingLeadIndividualContactPerson,crle_reference as referers_id,"
                . "crle_refered_status AS refererse_id,crle_referedBy_status AS referedl_by,crle_indPrimaryMobile AS numberfieldMarketingLeadindividualPrimarymobile,crle_indEmail AS textfieldMarketingLeadIndividualEmail "
                . "FROM finascop_crm_lead WHERE crle_id=$crle_id";

        $results = $db->getFromDB($QRY, true);

        if ($type == 1) {
            if (!empty($results)) {
                echo '{"success":true, "data":', json_encode($results), '}';
            } else
                echo '{"success":true,"data":[]}';
        }
        else {
            require(THIS_MODULE_PATH . "/leadsview.php");
        }
        break;
    case 'EditLeadDetails':
        $db->query('begin');
        $indi_contact_person = $_POST['textfieldMarketingLeadIndividualContactPerson'];
        $indi_primary_mobile = $_POST['numberfieldMarketingLeadindividualPrimarymobile'];
        $indi_email = $_POST['textfieldMarketingLeadIndividualEmail'];
        $reference = $_POST['referers_id'];
//        $refered_by = $_POST['referedl_by'];
//        if ($refered_by == 'Yes') {
//            $refBy = 1;
//        } else {
//            $refBy = 0;
//        }
        $referer_id = $_POST['refererse_id'];
        if ($referer_id == 'Yes') {
            $refId = 1;
        } else {
            $refId = 0;
        }

        $data = array(
            'crle_indContactperson' => $indi_contact_person,
            'crle_indPrimaryMobile' => $indi_primary_mobile,
            'crle_indEmail' => $indi_email,
            'crle_referedBy_status' => 0,
            'crle_refered_status' => $refId,
            'crle_reference' => $reference,
        );

        $n = 5;
        $uniqueId = getName($n);
        $data['uniqueId'] = $uniqueId;
        $leadid = $_POST['customer_id'];
        if ($leadid > 0) {
            $isUnique = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_crm_lead WHERE (crle_indPrimaryMobile='{$indi_primary_mobile}' OR crle_indEmail='{$indi_email}') AND crle_id<>{$leadid}");
            if ($isUnique > 0) {
                echo "{success: false,errors: { msg: 'Editing details are already existing.' }}";
                exit;
            } else {
                $status = $db->perform(FINASCOP_DB . 'finascop_crm_lead', $data, 'update', "crle_id={$leadid}");
                $lastId = $leadid;
            }
        } else {
            $isUnique = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_crm_lead WHERE crle_indPrimaryMobile='{$indi_primary_mobile}' OR crle_indEmail='{$indi_email}'");
            if ($isUnique > 0) {
                echo "{success: false,errors: { msg: 'Lead already existing.' }}";
                exit;
            } else {
                $status = $db->perform(FINASCOP_DB . 'finascop_crm_lead', $data);
                $lastId = $db->insert_id();
            }
        }

        if ($referer_id == 'Yes') {
            $reference_data = array(
                'referers_contact_id' => $lastId,
                'referers_from' => 2,
                'referers_created_on' => date('Y-m-d H:i:s'),
                'referers_created_by' => $_SESSION['admin']->Finascop_UserId
            );
            //echo "SELECT COUNT(*) FROM finascop_crm_referers WHERE referers_contact_id={$reference_data['referers_contact_id']}";
            $qry_ref = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_crm_referers WHERE referers_contact_id={$reference_data['referers_contact_id']} AND referers_from=2");
            if ($qry_ref == 0) {
                $status_referer = $db->perform(FINASCOP_DB . 'finascop_crm_referers', $reference_data);
            }
        }

        $data = array(
            'crlh_type' => 'U',
            'crle_id' => $lastId,
            'crle_indContactperson' => $indi_contact_person,
            'crle_indPrimaryMobile' => $indi_primary_mobile,
            'crle_indEmail' => $indi_email,
            'crle_referedBy_status' => 0,
            'crle_refered_status' => $refId,
            'crle_reference' => $reference,
            'uniqueId' => $uniqueId
        );
        if ($reference != '') {
            $referen = explode(",", $reference);
            $count_ref = count($referen);
            $del_ref = $db->query("delete FROM finascop_crm_reference WHERE reference_cl_id={$lastId} AND reference_from=1");
            for ($j = 0; $j < $count_ref; $j++) {
                $refer_data = array(
                    'reference_cl_id' => $lastId,
                    'refrence_referers_id' => $referen[$j],
                    'reference_from' => 1
                );
                $status5 = $db->perform(FINASCOP_DB . 'finascop_crm_reference', $refer_data);
            }
        }
        $status_history = $db->perform(FINASCOP_DB . 'finascop_crm_lead_history', $data);

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Lead details saved successfully'}";
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
}
