<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");

switch ($op) {
    case 'getEnquiryDetails':
        // $id = $_POST['crme_id'];
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $rec_sort = empty($_POST['sort']) ? 'crme_id' : $_POST['sort'];
        $rec_sort_dir = empty($_POST['dir']) ? 'ASC' : $_POST['dir'];
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
        $countDataQuery = "SELECT count(*) from finascop_crm_enquiry WHERE  crmm_IsActive != 0 and crmm_IsActive != 2";
        $count = $db->getItemFromDB($countDataQuery);

        $qry = "SELECT crme_id,crme_name,crme_mobile,crme_email,crmm_IsActive FROM finascop_crm_enquiry WHERE  crmm_IsActive != 0 and crmm_IsActive != 2 {$search}
        ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit ";

        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'loadEnquiryData':
        $crme_id = !empty($_GET['crme_id']) ? $_GET['crme_id'] : 0;
        $QRY = "SELECT crme_name AS enquiryName,crme_email AS enquiryEmail,crme_mobile AS enquiryMobile,crms_id,crme_description FROM finascop_crm_enquiry WHERE crme_id = $crme_id";
        $results = $db->getFromDB($QRY, true);
        require(THIS_MODULE_PATH . "/enquiryview.php");
        break;

    case 'convertToContact':
        $db->query('begin');
        $id = $_POST['crme_id'];
        $status = $_POST['status'];
        $enquiry_type = $_POST['type'];
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

    case 'removeEnquiry':
        $id = $_POST['crme_id'];
        //$status = $_POST['activestatus'];
        $data = array(
            'crmm_IsActive' => 0
        );
        $qry = $db->perform(FINASCOP_DB . 'finascop_crm_enquiry', $data, 'update', 'crme_id=' . $id);
        if ($qry) {
            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
}