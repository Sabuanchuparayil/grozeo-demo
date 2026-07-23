<?php
require_once(INCLUDE_PATH . "/finascop_common_functions.php");
switch ($op) {
    case 'listCampaigns':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'referers_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
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
        }
        $date = date('Y-m-d');
        $countQuery = "SELECT COUNT(*) FROM finascop_crm_referers {$search} ORDER BY {$sort} {$dir} limit $start,$limit";
        $listQuery = "SELECT referers_id,referers_name /*as reference_name*/,IF(DATE(referers_sentSMS_on)='{$date}','Sent','') as sent_status,(SELECT COUNT(*) FROM finascop_crm_reference WHERE refrence_referers_id = referers_id) as reference_count FROM finascop_crm_referers {$search}"
        . " ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'campaignsDetailsView':
        $referers_id = isset($_POST['referers_id']) ? intval($_POST['referers_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($referers_id || $ID) {

            $data = $db->getFromDB("SELECT referers_id,referers_name AS reference_name,(SELECT COUNT(*) FROM finascop_crm_reference WHERE refrence_referers_id = referers_id) as reference_count,"
                    . "referers_mobile AS reference_mobile,referers_email AS reference_email FROM finascop_crm_referers WHERE referers_id=" . $referers_id, true);

            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'MapReference':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;

        $_allowed_sort = ['camp_id', 'camp_name', 'camp_start_date'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'camp_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'reference_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;


        $filter_qry = " AND  1 = 1 ";
        if (isset($_POST['filter'])) {
        $allowedFields = ['camp_id', 'camp_name', 'camp_type', 'camp_status', 'camp_start_date', 'camp_end_date'];
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
            $listQuery = "SELECT reference_id,reference_cl_id,IF(reference_from=0,(SELECT COUNT(crco_id) FROM finascop_crm_contact WHERE crco_id = reference_cl_id),(SELECT COUNT(crle_id) FROM finascop_crm_lead WHERE reference_cl_id= crle_id)) as itemcount,refrence_referers_id as refrence_referers_id ,IF(reference_from=0,(SELECT crco_indContactperson FROM finascop_crm_contact WHERE crco_id=reference_cl_id),(SELECT crle_indContactperson FROM finascop_crm_lead WHERE crle_id=reference_cl_id)) as contactPerson,IF(reference_from=0,(SELECT crco_indPrimaryMobile FROM finascop_crm_contact WHERE crco_id=reference_cl_id),(SELECT crle_indPrimaryMobile FROM finascop_crm_lead WHERE crle_id=reference_cl_id)) as contactNumber,IF(reference_from=0,(select count(crco_id) from finascop_crm_contact where FIND_IN_SET(reference_cl_id,crco_id) AND DATE(reference_sms_on)='{$day}'),(select count(crle_id) from finascop_crm_lead where FIND_IN_SET(reference_cl_id,crle_id) AND DATE(reference_sms_on)='{$day}')) as checkeds FROM finascop_crm_reference WHERE refrence_referers_id={$refID} {$filter_qry}  {$order} ";
            $datas = $db->getMulipleData($listQuery, true);
//            $resCount = count($datas);
//            for ($i = 0; $i < $resCount; $i++) {
//                $datas[$i]['checked'] =$db->getMulipleData("SELECT IF(reference_from=0,(select count(crco_id) from finascop_crm_contact where FIND_IN_SET(reference_cl_id,crco_id) AND DATE(reference_sms_on)='{$day}'),(select count(crle_id) from finascop_crm_lead where FIND_IN_SET(reference_cl_id,crle_id) AND DATE(reference_sms_on)='{$day}')) as checked FROM finascop_crm_reference", true);
//            }
        } else {
            $countQuery = "SELECT COUNT(*) FROM finascop_crm_reference WHERE refrence_referers_id={$refID} {$filter_qry}";
            $count = $db->getItemFromDB($countQuery);
            $listQuery = "SELECT reference_id,reference_cl_id,IF(reference_from=0,(SELECT COUNT(crco_id) FROM finascop_crm_contact WHERE crco_id = reference_cl_id),(SELECT COUNT(crle_id) FROM finascop_crm_lead WHERE reference_cl_id= crle_id)) as itemcount,refrence_referers_id as refrence_referers_id ,IF(reference_from=0,(SELECT crco_indContactperson FROM finascop_crm_contact WHERE crco_id=reference_cl_id),(SELECT crle_indContactperson FROM finascop_crm_lead WHERE crle_id=reference_cl_id)) as contactPerson,IF(reference_from=0,(SELECT crco_indPrimaryMobile FROM finascop_crm_contact WHERE crco_id=reference_cl_id),(SELECT crle_indPrimaryMobile FROM finascop_crm_lead WHERE crle_id=reference_cl_id)) as contactNumber FROM finascop_crm_reference WHERE refrence_referers_id={$refID} {$filter_qry}  {$order} ";
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
        $referers_id = $_POST['referers_id'];
        $date = date('Y-m-d H:i:s');
        $mob = implode(",", $mob_num);
        $str = TextLocalSMS_CAMPAIGN_TEMPLATE . "http://retaline.in/app";
        require(ROOT . '/classes/TextLocal.php');
        sms::send($mob, $str, $db, "");
        $db->query('begin');

        for ($i = 0; $i < count($refIds); $i++) {

            $data_updates['reference_sms_on'] = $date;
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
        $qry = "SELECT template_id,template_name FROM finascop_crm_templates";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
    case 'getReference':
        $qry = "SELECT referers_id,(SELECT crle_indContactperson FROM finascop_crm_lead WHERE crle_id = referers_contact_id) AS referers_contact_id FROM finascop_crm_referers";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
    case 'insertReferer':
        $db->query('begin');
        $filedata['referers_name'] = $_POST['referer_name'];
        $filedata['referers_email'] = $_POST['referer_email'];
        $filedata['referers_mobile'] = $_POST['referer_mobile'];
        $filedata['referers_location'] = $_POST['referer_location'];
        $filedata['referers_notes'] = $_POST['notes_id'];
        $filedata['referers_created_on'] = date('Y-m-d H:i:s');
        $filedata['referers_created_by'] = $_SESSION['admin']->Finascop_UserId;

//        $filedata['uniqueId'] = getNewFinascopApiKey();
        $n = 5;
        $filedata['uniqueId'] = getName($n);

        $status = $db->perform(FINASCOP_DB . 'finascop_crm_referers', $filedata);
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Added Successfully.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'getDistrict':
        $state = $_POST['st_Id'];
        $qry = "select dst_ID,dst_Name from " . FINASCOP_DB . "finascop_district order by dst_Name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        // echo '{success:true, data:'.json_encode($data).'}';
        break;
    case 'mobCheck':
        $referer_mob = $_POST['referer_mob'];
        $isMobileAvailable = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer WHERE cust_mobile = '{$referer_mob}'");
        if ($isMobileAvailable > 0) {
            echo "{success: true}";
        } else {
            echo "{success: false}";
}
        break;
}