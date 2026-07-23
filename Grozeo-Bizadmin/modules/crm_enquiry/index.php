<?php

require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/finascop_config/config.php');
require_once(INCLUDE_PATH . '/config.php');
require_once(EXTERNAL_LIBRARY_PATH);

require_once(INCLUDE_PATH . "/finascop_common_functions.php");


global $db;
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
        if ($_POST['type']) {
            $search .= " AND crme_type = {$_POST['type']} ";
        }
        $countDataQuery = "SELECT count(*) from finascop_crm_enquiry WHERE  crmm_IsActive != 0 and crmm_IsActive != 2 {$search}";
        $count = $db->getItemFromDB($countDataQuery);

        $qry = "SELECT crme_id,crme_name,crme_mobile,crme_email,crmm_IsActive,crmm_store_name,DATE_FORMAT(crmm_CreatedOn,'%d-%m-%Y %H:%i:%s') AS crmm_CreatedOn FROM finascop_crm_enquiry WHERE  crmm_IsActive != 0 and crmm_IsActive != 2 {$search}
        ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit ";

        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'loadEnquiryData':
        $crme_id = !empty($_GET['crme_id']) ? $_GET['crme_id'] : 0;
        if ($crme_id > 0) {
            $QRY = "SELECT crme_name AS enquiryName,crme_email AS enquiryEmail,crme_mobile AS enquiryMobile,crms_id,crme_description,crmm_location FROM finascop_crm_enquiry WHERE crme_id = {$crme_id}";
            $results = $db->getFromDB($QRY, true);
            require(THIS_MODULE_PATH . "/enquiryview.php");
        }
        break;

    case 'convertToContact':
        $db->query('begin');
        $id = $_POST['crme_id'];
        $status = $_POST['status'];
        $enquiry_type = $_POST['type'];
        $mobileAvailable  = $db->getItemFromDB("SELECT crme_mobile FROM finascop_crm_enquiry WHERE crme_id = {$id} ");
        if (empty($mobileAvailable)) {
            echo "{success: false,msg:'Contact details are missing in this enqiry'}";
            exit();
        }

        if ($status == 1) {
            $enquiry_status = 2;
        }
        $data = array(
            'crmm_IsActive' => $enquiry_status
        );
        $qry = $db->perform('finascop_crm_enquiry', $data, 'update', 'crme_id=' . $id);

        $convertQry = "SELECT crme_id AS crme_id,crme_name AS crco_orgName,crme_type AS crco_type,
                crme_email AS crco_orgEmail,crme_mobile AS crco_indMobile,crme_description as crco_description 
                FROM finascop_crm_enquiry where crme_id=$id";

        $enquiry_data = $db->getFromDB($convertQry, true);

        $isUnique = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_crm_contact WHERE crco_indMobile ='{$enquiry_data['crco_indMobile']}' AND crco_type = {$enquiry_data['crco_type']} ");
        if ($isUnique > 0) {
            echo "{success: false, msg: 'Mobile already existing.' }";
            exit;
        }
        if ($_POST['type'] != 4) {
            $status = $db->perform('finascop_crm_contact', $enquiry_data);
        } else {
            $assData['name'] = $enquiry_data['crco_orgName'];
            $assData['mobile'] = $enquiry_data['crco_indMobile'];
            $assData['email'] = $enquiry_data['crco_orgEmail'];
            $assData['entryType'] = 1;
            $assData['createdOn'] = date('Y-m-d H:i:s');
            $assData['createdBy'] = $_SESSION['admin']->UserId;
            $status = $supportdb->perform('crm_area_associate', $assData);
        }

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
    case 'getGeneralEnquiryDetails':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $rec_sort = empty($_POST['sort']) ? 'id' : $_POST['sort'];
        $rec_sort_dir = empty($_POST['dir']) ? 'ASC' : $_POST['dir'];
        $filter = $_POST['filter'];
        $search = "";
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status') {
                            if ($field['data']['value'] == 'Active') {
                                $fiterItem = 1;
                                $search .= " and ({$field['field']} = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Inactive') {
                                $fiterItem = 0;
                                $search .= " and ({$field['field']} = {$fiterItem}) ";
                            } else {
                                $search .= " and (status = 1 or status=0) ";
                            }
                        }

                        break;
                    case 'date':
                        if ($field['field'] == 'createdDate') {

                            switch ($field['data']['comparison']) {
                                case 'gt':
                                    $search .= " and DATE_FORMAT(createdOn,'%Y-%m-%d') > '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'lt':
                                    $search .= " and DATE_FORMAT(createdOn,'%Y-%m-%d') < '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'eq':
                                    $search .= " and DATE_FORMAT(createdOn,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                default:
                                    $search .= " and DATE_FORMAT(createdOn,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                            }
                        }
                        break;
                    default:


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
        if ($rec_sort == 'createdDate') {
            $rec_sort = 'id';
        }
        $countDataQuery = "SELECT count(*) from general_enquiry WHERE  status = 1 {$search}";
        $count = $supportdb->getItemFromDB($countDataQuery);

        $qry = "SELECT id,name,mobile,email,DATE_FORMAT(createdOn,'%d-%m-%Y') AS createdDate,DATE_FORMAT(createdOn,'%d-%m-%Y %H:%i:%s') AS createdOn,country_code FROM general_enquiry WHERE  status = 1 {$search}
        ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit ";

        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'removeGeneralEnquiry':
        $id = $_POST['crme_id'];
        //$status = $_POST['activestatus'];
        $data = array(
            'status' => 2
        );
        $qry = $supportdb->perform('general_enquiry', $data, 'update', 'id=' . $id);
        if ($qry) {
            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'loadGeneralEnquiryData':
        $crme_id = !empty($_GET['crme_id']) ? $_GET['crme_id'] : 0;
        $results = [];
        if ($crme_id > 0) {
            $QRY = "SELECT name AS enquiryName,email AS enquiryEmail,mobile AS enquiryMobile,
            message as crme_description,country_code FROM general_enquiry WHERE id = {$crme_id}";
            $results = $supportdb->getFromDB($QRY, true);
        }

        require(THIS_MODULE_PATH . "/enquiryview.php");
        break;
    case 'getSignupEnquiryDetails':
        listSignupEnquiry();
        break;
    case 'loadSignupEnquiryData':
        break;
    case 'removeSignupEnquiry':
        removeEnquiry($_POST["mobile"]);
        
        break;
}
