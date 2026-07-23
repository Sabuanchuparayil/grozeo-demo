<?php

switch ($op) {
    case 'listCampaigns':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'referers_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        if (count($_POST['filter']) > 0) {
            $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
            $filter = $_POST['filter'];
            foreach ($filter as $key => $val) {
                 $type = $val['data']['type'];
                $value = $val['data']['value'];

                $field = $val['field'];
                switch ($field){
                //switch ($val['data']['type']) {
                //    case 'string':
                //        $filter_qry .= " AND " . $val['field'] . "  LIKE  '" . $val['data']['value'] . "%'";
                //        break;
                    case 'campaign_name':
                        $search .= " AND campaign_name LIKE '%" . $value . "%' ";
                        break;
                     case 'campaign_type':
                        $search .= " AND campaign_type LIKE '%" . $value . "%' ";
                        break;
                    case 'campaign_startedOn':
                        $value = str_replace("/", "", $value);
                        $value = substr($value, 4, 4) . substr($value, 0, 2) . substr($value, 2, 2);
                        $search .= " AND  DATE_FORMAT(campaign_startedOn,'%Y%m%d') " . $comparisons[$val['data']['comparison']] . " " . $value;
                        break;
                    case 'campaign_count':
                        $search .= " AND campaign_count LIKE '%" . $value . "%' ";
                        break;
                }
            }
        }
    //    $filter = $_POST['filter'];
    /*    if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');
                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $search .= " AND ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $search .= " AND ({$field[field]} LIKE '{$field['data']['value']}%') ";
                    }
                }
            }
        }*/
        $date = date('Y-m-d');
        $countQuery = "SELECT COUNT(*) FROM finascop_crm_campaigns {$search} ORDER BY {$sort} {$dir} limit $start,$limit";
        $listQuery = "SELECT campaign_id,campaign_name,campaign_type,campaign_templateId,campaign_refererId,campaign_count,DATE_FORMAT(campaign_startedOn,'%d-%m-%Y') as campaign_startedOn FROM finascop_crm_campaigns {$search}"
                . " ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'campaignsDetailsView':
        $campaign_id = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($campaign_id || $ID) {
            $data = $db->getFromDB("SELECT campaign_id,campaign_name,campaign_type,(SELECT template_name FROM finascop_crm_templates WHERE template_id=campaign_templateId) AS campaign_templateId,campaign_refererId,campaign_count,campaign_startedOn FROM finascop_crm_campaigns "
                    . "WHERE campaign_id=" . $campaign_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'MapReference':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;

        $_allowed_sort = ['sms_id', 'sms_sent_on'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'sms_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'reference_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;


        $filter_qry = " AND  1 = 1 ";
        if (isset($_POST['filter'])) {
        $allowedFields = ['sms_id', 'sms_message', 'sms_status', 'sms_sent_on', 'sms_recipient'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }
        $order .= "ORDER BY CAST({$sort} as char) {$dir},binary {$sort} {$dir}";
        $refID = $_POST['refrence_referers_id'];
        $day = date("Y-m-d");
        $smsStatus = $db->getItemFromDB("SELECT COUNT(*) from finascop_crm_reference WHERE DATE(reference_sms_on)='{$day}' ");
        if ($smsStatus > 0) {
            $countQuery = "SELECT COUNT(*) FROM finascop_crm_reference WHERE refrence_referers_id={$refID} {$filter_qry}";
            $count = $db->getItemFromDB($countQuery);
            $listQuery = "SELECT reference_id,reference_cl_id,IF(reference_from=0,(SELECT COUNT(crco_id) FROM finascop_crm_contact WHERE crco_id = reference_cl_id),"
                    . "(SELECT COUNT(crle_id) FROM finascop_crm_lead WHERE reference_cl_id= crle_id)) as itemcount,refrence_referers_id as refrence_referers_id ,"
                    . "IF(reference_from=0,(SELECT crco_indContactperson FROM finascop_crm_contact WHERE crco_id=reference_cl_id),(SELECT crle_indContactperson "
                    . "FROM finascop_crm_lead WHERE crle_id=reference_cl_id)) as contactPerson,IF(reference_from=0,(SELECT crco_indPrimaryMobile FROM finascop_crm_contact "
                    . "WHERE crco_id=reference_cl_id),(SELECT crle_indPrimaryMobile FROM finascop_crm_lead WHERE crle_id=reference_cl_id)) as contactNumber,"
                    . "IF(reference_from=0,(select count(crco_id) from finascop_crm_contact where FIND_IN_SET(reference_cl_id,crco_id) AND DATE(reference_sms_on)='{$day}'),"
                    . "(select count(crle_id) from finascop_crm_lead where FIND_IN_SET(reference_cl_id,crle_id) AND DATE(reference_sms_on)='{$day}')) as checkeds, "
                    . "IF(reference_from=0,(SELECT crco_indEmail FROM finascop_crm_contact WHERE crco_id=reference_cl_id),(SELECT crle_indEmail FROM finascop_crm_lead "
                    . "WHERE crle_id=reference_cl_id)) as contactEmail FROM finascop_crm_reference WHERE refrence_referers_id={$refID} {$filter_qry}  {$order} ";
            $datas = $db->getMulipleData($listQuery, true);
//            $resCount = count($datas);
//            for ($i = 0; $i < $resCount; $i++) {
//                $datas[$i]['checked'] =$db->getMulipleData("SELECT IF(reference_from=0,(select count(crco_id) from finascop_crm_contact where FIND_IN_SET(reference_cl_id,crco_id) AND DATE(reference_sms_on)='{$day}'),(select count(crle_id) from finascop_crm_lead where FIND_IN_SET(reference_cl_id,crle_id) AND DATE(reference_sms_on)='{$day}')) as checked FROM finascop_crm_reference", true);
//            }
        } else {
            $countQuery = "SELECT COUNT(*) FROM finascop_crm_reference WHERE refrence_referers_id={$refID} {$filter_qry}";
            $count = $db->getItemFromDB($countQuery);
            $listQuery = "SELECT reference_id,reference_cl_id,IF(reference_from=0,(SELECT COUNT(crco_id) FROM finascop_crm_contact WHERE crco_id = reference_cl_id),"
                    . "(SELECT COUNT(crle_id) FROM finascop_crm_lead WHERE reference_cl_id= crle_id)) as itemcount,refrence_referers_id as refrence_referers_id ,"
                    . "IF(reference_from=0,(SELECT crco_indContactperson FROM finascop_crm_contact WHERE crco_id=reference_cl_id),(SELECT crle_indContactperson "
                    . "FROM finascop_crm_lead WHERE crle_id=reference_cl_id)) as contactPerson,IF(reference_from=0,(SELECT crco_indPrimaryMobile FROM finascop_crm_contact "
                    . "WHERE crco_id=reference_cl_id),(SELECT crle_indPrimaryMobile FROM finascop_crm_lead WHERE crle_id=reference_cl_id)) as contactNumber, "
                    . "IF(reference_from=0,(SELECT crco_indEmail FROM finascop_crm_contact WHERE crco_id=reference_cl_id),(SELECT crle_indEmail FROM finascop_crm_lead "
                    . "WHERE crle_id=reference_cl_id)) as contactEmail FROM finascop_crm_reference WHERE refrence_referers_id={$refID} {$filter_qry}  {$order} ";
            $datas = $db->getMulipleData($listQuery, true);
        }

        if (!empty($datas)) {

            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else {
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        }
//        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'sendCampaign':
        $refIds = json_decode(stripslashes($_POST['refIds']), true);
        $mob_num = json_decode(stripslashes($_POST['mob_num']), true);
        $email_name = json_decode(stripslashes($_POST['email_name']), true);
        $referers_id = $_POST['referers_id'];
        $camp_name = $_POST['camp_name'];
        $sms_type = $_POST['template'];
        $date = date('Y-m-d H:i:s');
        $tpl = $db->getFromDB("SELECT * FROM template_variables WHERE template_variable_templateId = '{$sms_type}'", true);
        $count = count($tpl['template_variable_variableId']);
        $campaign['campaign_name'] = $camp_name;
        $campaign['campaign_templateId'] = $sms_type;
        $campaign['campaign_type'] = $_POST['type'];
        $campaign['campaign_refererId'] = $referers_id;
        $campaign['campaign_count'] = count($refIds);
        $campaign['campaign_startedOn'] = $date;
        $campaign['campaign_startedBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('finascop_crm_campaigns', $campaign);
        $lastId = $db->insert_id();
        for ($j = 0; $j < $count; $j++) {
            
        }
        $db->query('begin');
        
        $company_name = $_SESSION['admin']->finascop_current_company;
        for ($i = 0; $i < count($refIds); $i++) {
            if ($_POST['type'] == 'SMS') {
                $camp_refId = $db->getItemFromDB("SELECT uniqueId FROM finascop_crm_lead WHERE crle_id='{$refIds[$i]}'");
                $camp['receiver_id'] = $mob_num[$i];
                $camp['is_sms'] = 1;
                $camps['text_message'] = $db->getItemSafe("SELECT template_sms FROM finascop_crm_templates WHERE template_id='?'", "s", [$_POST['template']]);
                $camp['text_message'] = str_replace(
                        array('[referer_Id]', '[company_name]'), array($camp_refId, $company_name), $camps['text_message']
                );
                $campDet = array(
                    'campaign_id' => $lastId,
                    'recepients' => $mob_num[$i],
                    'lead_id' => $refIds[$i]
                );
            } else if ($_POST['type'] == 'Email') {
                $camp_refId = $db->getItemFromDB("SELECT uniqueId FROM finascop_crm_lead WHERE crle_id='{$refIds[$i]}'");
                $camp['receiver_id'] = $email_name[$i];
                $camp['is_sms'] = 0;
                $camps['text_message'] = $db->getItemSafe("SELECT template_email FROM finascop_crm_templates WHERE template_id='?'", "s", [$_POST['template']]);
                $camp['text_message'] = str_replace(
                        array('[referer_Id]', '[company_name]'), array($camp_refId, $company_name), $camps['text_message']
                );
                $campDet = array(
                    'campaign_id' => $lastId,
                    'recepients' => $email_name[$i],
                    'lead_id' => $refIds[$i]
                );
            }
            $camp['created_on'] = $date;
            $data_updates['reference_sms_on'] = $date;

            $status6 = $db->perform(FINASCOP_DB . 'finascop_crm_campaign_details', $campDet);
            $status5 = $db->perform(FINASCOP_DB . 'retaline_emailsms_queue', $camp);
            $status = $db->perform('finascop_crm_reference', $data_updates, 'update', "reference_cl_id ={$refIds[$i]} AND refrence_referers_id={$referers_id}");
        }
        $data_update['referers_sentSMS_on'] = $date;
        $status = $db->perform('finascop_crm_referers', $data_update, 'update', "referers_id ={$referers_id}");
        $status = $db->query('commit');
        if ($status) {
            echo '{"success":true,"msg":"SMS Send Successfully"}';
        }
        break;

    case 'removeCampaign':
        $refIds = json_decode(stripslashes($_POST['refIds']), true);
        $db->query('begin');

        for ($i = 0; $i < count($refIds); $i++) {

            $status = $db->query("DELETE FROM finascop_crm_reference WHERE reference_cl_id={$refIds[$i]} AND reference_from=1");
            $status = $db->query("DELETE FROM finascop_crm_lead WHERE crle_id={$refIds[$i]} AND crle_refered_status<>1");
        }
        $status = $db->query('commit');
        if ($status) {
            echo '{"success":true,"msg":"Removed reference from campaign successfully"}';
        }
        break;
    case 'getTemplates':
        $tempate_type = $_POST['templates_type'];
        $qry = "SELECT template_id,template_name FROM finascop_crm_templates WHERE template_type='{$tempate_type}' AND template_IsActive=1 ";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
    case 'getReference':
        $qry = "SELECT referers_id,referers_name AS referers_contact_id FROM finascop_crm_referers";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
    case 'uploadcsvFile':
        $referers_ids = $_POST['referers_ids'];
        $file = $_FILES['excel_ref_file']['tmp_name'];
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
                $dupCategory = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer WHERE cust_mobile = '{$value[1]}'");
                $dupLeadCategory = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_crm_lead WHERE crle_indPrimaryMobile = '{$value[1]}' AND crle_reference={$referers_ids}");
                if ($dupCategory < 1) {
                    if ($dupLeadCategory < 1) {
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
                            'crle_CreatedBy' => $_SESSION['admin']->Finascop_UserId
                        );
                        $status_history = $db->perform(FINASCOP_DB . 'finascop_crm_lead_history', $data);
                    }
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
    case 'sendTestCampaign':
        $db->query('begin');
        if ($_POST['type'] == 'SMS') {
            $test['receiver_id'] = $_POST['mob_num'];
            $test['is_sms'] = 1;
            $test['text_message'] = $db->getItemSafe("SELECT template_sms FROM finascop_crm_templates WHERE template_id='?'", "s", [$_POST['template']]);
        } else {
            $test['receiver_id'] = $_POST['email'];
            $test['is_sms'] = 0;
            $test['text_message'] = $db->getItemSafe("SELECT template_email FROM finascop_crm_templates WHERE template_id='?'", "s", [$_POST['template']]);
        }
        $test['created_on'] = date("Y-m-d H:i:s");
        $status = $db->perform(FINASCOP_DB . 'retaline_emailsms_queue', $test);
        $status = $db->query('commit');
        if ($status) {
            echo '{"success":true,"msg":"Campaign Send Successfully"}';
        } else {
            // var_dup($error);
            echo '{"success":false,"msg":"Error in sending campaign"}';
        }
        break;
}