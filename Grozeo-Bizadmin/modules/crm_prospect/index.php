<?php

switch ($op) {
    case 'getProspectDetails':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $userId = $_SESSION['admin']->Finascop_UserId;
        // SECURITY: use buildSafeFilterQuery
        $allowedFields = ['prospect_name', 'prospect_phone', 'prospect_email', 'prospect_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        /* RAW (disabled): $filter = $_POST['filter']; */
        $search = " WHERE 1=1 ";//AND crmuId NOT IN (3,7) 
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
            $search .= " AND crpr_type = {$_POST['type']} ";
            
    }
        $qry = "SELECT * FROM  finascop_crm_prospect {$search}
        LIMIT $rec_start,$rec_limit";
        $items = $db->getMulipleData($qry, true);

        $countDataQuery = "SELECT COUNT(*) FROM  finascop_crm_prospect  {$search} ORDER BY crmuId";
        $count = $db->getItemFromDB($countDataQuery);

        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'loadEditProspectData':
        $type = $_POST['EditStatus'];
        $crpr_id = $_REQUEST['id'];
        if ($crpr_id > 0){
            $QRY = "SELECT * FROM finascop_crm_prospect WHERE id  = $crpr_id";

            $results = $db->getFromDB($QRY, true);
            $crpr_type = $db->getItemFromDB("SELECT name FROM crm_contact_type where id = {$results['crpr_type']}");
        }
        
        if ($type == 1) {
            if (!empty($results)) {
                echo '{"success":true, "data":', json_encode($results), '}';
            } else
                echo '{"success":true,"data":[]}';
        } else {
            if ($crpr_id > 0)
                require(THIS_MODULE_PATH . "/prospectview.php");
        }
        break;
    case 'getCrmStatus':
        $qry = "select id,name from prospect_stages  order by name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'updateProspectStages':
        $prospectId = $_POST['prospectId'];
        $leadUpdate['crmuId'] = $_POST['crmStatus'];
        $leadUpdate['crmFollowupDate'] = date('Y-m-d', strtotime($_POST['crmFollowupDate']));
        $leadUpdate['crmRemarks'] = $_POST['crmRemarks'];

        $crmCmmn['crProspectId'] = $prospectId;
        $crmCmmn['crmu_id'] = $_POST['crmStatus'];
        $crmCmmn['crmc_remark'] = $_POST['crmRemarks'];

        $db->query('begin');
        $status = $db->perform('finascop_crm_communication', $crmCmmn);
        $status = $db->perform('finascop_crm_prospect', $leadUpdate, 'update', " id = {$prospectId}");

        switch ($_POST['crmStatus']) {
            case 1:
                $message = "Appointment scheduled.";
                break;
            case 2:
                $message = "Presentation scheduled.";
                break;
            case 3:
                $message = "Decision-maker bought-in.";
                break;
            case 4:
                $message = "Contract sent";
                break;
            case 5:
                $message = "Closed Won";
                break;
            case 6:
                $message = "Closed Lost.";
                break;
        }

        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg: '" . $message . "' }";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;
    case 'sendInvitation':
        $id  = $_POST['prospectId'];
        $prospectData = $db->getFromDB("SELECT * FROM finascop_crm_prospect WHERE id = {$id}",true);
        $fields['code'] = $prospectData['invitationCode'];
        $fields['fullname'] = $prospectData['crpr_orgName'];
        $fields['email'] = $prospectData['crpr_orgEmail'];

        $url =  $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'PROSPECT_INVITATION'");
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
            exit;
        }
        $db->query('begin');
        $leadUpdate['invitationSent'] = 1;
        $leadUpdate['invitationLink'] = $result['url'];
        $leadUpdate['crpr_UpdatedOn'] = date('Y-m-d H:i:s');
        $status = $db->perform("finascop_crm_prospect",$leadUpdate, 'update', " id = {$id}");
        $prospectData = $db->getFromDB("SELECT invitationCode,crpr_indMobile FROM finascop_crm_prospect WHERE id = {$id}",true);
        $templatedata['invitationLink'] = $prospectData['invitationCode'];
        $mobile = $prospectData['crpr_indMobile'];        
        $status = $db->query('commit');
        if ($status) {
            sms::fetchContentSendSms($templatedata, $mobile, 24);
            echo "{success: true,msg: '" . $result['message'] . "' }";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;
}
