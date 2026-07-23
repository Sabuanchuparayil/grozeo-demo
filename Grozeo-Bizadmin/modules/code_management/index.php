<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");

switch ($op) {
    case 'storeForReferreType':
        $qry = "select id,name from  referrer_type  where id NOT IN (0,2,4,5,8) order by id ";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo json_encode($data);
        } else
            echo [];
        break;
    case 'storeForReferrer':
        $type = $_POST['type'];
        switch ($type) {
            case 0:
            case 1:

                break;
            case 2:
                $qry = "select  id,CONCAT(bpName, ' (',bpEmail,') ') as name from  business_partner order by bpName";
                $data = $db->getMultipleData($qry, true);
                break;
            case 3:
                $qry = "select id,CONCAT(baName, ' (',baEmail,') ') as name from  business_associate where baType = 2 and userType = 3  order by baName";
                $data = $db->getMultipleData($qry, true);
                break;
            case 4:
                $qry = "select id,CONCAT(baName, ' (',baEmail,') ') as name from  business_associate where baIsPartner = 1 and userType = 3  order by baName";
                $data = $db->getMultipleData($qry, true);
                break;
            case 5:
                $qry = "select id,CONCAT(baName, ' (',baEmail,') ') as name from  business_associate where baType = 1 and userType = 3 order by baName";
                $data = $db->getMultipleData($qry, true);
                break;
            case 6:
                $qry = "select id,CONCAT(roName, ' (',roEmailId,') ') as name from  relationship_officer where type = 2 order by roName";
                $data = $db->getMultipleData($qry, true);
                break;
            case 7:
                $qry = "select id,CONCAT(baName, ' (',baEmail,') ') as name from  business_associate where userType = 7 order by baName";
                $data = $db->getMultipleData($qry, true);
                break;
            case 8:
                $qry = "select id,CONCAT(baName, ' (',baEmail,') ') as name from  business_associate where userType = 8 order by baName";
                $data = $db->getMultipleData($qry, true);
                break;
            case 9:
                $qry = "select store_group_id as id,store_group_name as name from  finascop_branch_group  order by store_group_name";
                $data = $db->getMultipleData($qry, true);
                break;
            case 10:
                $qry = "select d_ID as id,d_Name as name from  qugeo_driver  order by d_Name";
                $data = $db->getMultipleData($qry, true);
                break;
        }
        if (!empty($data)) {
            echo json_encode($data);
        } else
            echo [];
        break;
    case 'getCodeMagmtDetails':
        // $id = $_POST['id'];
        $rec_limit = 20;//empty($_POST['limit']) ? 16 : $_POST['limit'];20
        $rec_start = 0;//empty($_POST['start']) ? 0 : $_POST['start'];0
        $rec_sort = empty($_POST['sort']) ? 'id' : $_POST['sort'];
        $rec_sort_dir = empty($_POST['dir']) ? 'DESC' : $_POST['dir'];
        $allowedFields = ['code_name', 'code_value', 'code_type', 'code_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
            }
        }
        if (!empty($_POST['codeType']))
            $search .= " and  codeType = {$_POST['codeType']} ";
        else
            $search .= " and  codeType > 0 ";
        if (!empty($_POST['validity']))
            $search .= " and  validity = {$_POST['validity']} ";
        if (!empty($_POST['referrerType']))
            $search .= " and  referrerTypeId = {$_POST['referrerType']} ";
        if (!empty($_POST['referrerId']))
            $search .= " and  referrerId = {$_POST['referrerId']} ";
        $countDataQuery = "SELECT count(*) from finascop_crm_prospect WHERE  1=1 {$search}";
        $count = $db->getItemFromDB($countDataQuery);

        $qry = "SELECT id,invitationCode,codeType,validity,status,referrerTypeId,(SELECT name FROM referrer_type WHERE id = referrerTypeId) AS referrerTypeName,crpr_CreatedOn,crpr_ExpiredOn,
        referrerId,status,IF(status = 1,'Active','Inactive') AS statusName,IF(validity = 1,'Single','Multiple') AS validityName,
        IF(status = 1,'Active','Inactive') AS statusName,
        CASE WHEN codeType = 1 THEN 'Referral' WHEN codeType = 2 THEN 'Invitation' WHEN codeType = 3 THEN 'Conversion' END AS codeTypeName ,
        CASE WHEN referrerTypeId = 0 THEN crpr_orgName  WHEN referrerId = 1 THEN '-'         
        WHEN referrerTypeId = 2 THEN (select bpName from  business_partner WHERE id = referrerId) 
        WHEN referrerTypeId = 3 THEN (select baName from  business_associate WHERE id = referrerId) 
        WHEN referrerTypeId = 4 THEN (select baName from  business_associate WHERE id = referrerId) 
        WHEN referrerTypeId = 5 THEN (select baName from  business_associate WHERE id = referrerId) 
        WHEN referrerTypeId = 6 THEN (select roName from  relationship_officer WHERE id = referrerId) 
        WHEN referrerTypeId = 7 THEN (select baName from  business_associate WHERE id = referrerId) 
        WHEN referrerTypeId = 8 THEN (select baName from  business_associate WHERE id = referrerId) 
        WHEN referrerTypeId = 9 THEN (select store_group_name from  finascop_branch_group WHERE store_group_id = referrerId) 
        WHEN referrerTypeId = 10 THEN (select d_Name from  qugeo_driver WHERE d_ID = referrerId)  END AS referrerName FROM finascop_crm_prospect WHERE  1=1 {$search}
        ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit ";

        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'loadCodeMagmtData':
        $id = !empty($_GET['id']) ? $_GET['id'] : 0;
        if ($id > 0) {
            $QRY = "SELECT id,invitationCode,codeType,validity,status,referrerTypeId,(SELECT name FROM referrer_type WHERE id = referrerTypeId) AS referrerTypeName,crpr_CreatedOn,crpr_ExpiredOn,
        referrerId,status,IF(status = 1,'Active','Inactive') AS statusName,IF(validity = 1,'Single','Multiple') AS validityName,
        IF(status = 1,'Active','Inactive') AS statusName,blockMerchant,codePlanType,invitationLink,
        CASE WHEN codeType = 1 THEN 'Referral' WHEN codeType = 2 THEN 'Invitation' WHEN codeType = 3 THEN 'Conversion' END AS codeTypeName ,CASE WHEN referrerId = 1 THEN '-' 
        WHEN referrerTypeId = 2 THEN (select bpName from  business_partner WHERE id = referrerId) 
        WHEN referrerTypeId = 3 THEN (select baName from  business_associate WHERE id = referrerId) 
        WHEN referrerTypeId = 4 THEN (select baName from  business_associate WHERE id = referrerId) 
        WHEN referrerTypeId = 5 THEN (select baName from  business_associate WHERE id = referrerId) 
        WHEN referrerTypeId = 6 THEN (select roName from  relationship_officer WHERE id = referrerId) 
        WHEN referrerTypeId = 7 THEN (select baName from  business_associate WHERE id = referrerId) 
        WHEN referrerTypeId = 8 THEN (select baName from  business_associate WHERE id = referrerId) 
        WHEN referrerTypeId = 9 THEN (select store_group_name from  finascop_branch_group WHERE store_group_id = referrerId) 
        WHEN referrerTypeId = 10 THEN (select d_Name from  qugeo_driver WHERE d_ID = referrerId)  END AS referrerName FROM finascop_crm_prospect WHERE id = {$id}";
            $results = $db->getFromDB($QRY, true);
            require(THIS_MODULE_PATH . "/detailsview.php");
        }
        break;

    case 'convertToContact':
        $db->query('begin');
        $id = $_POST['id'];
        $status = $_POST['status'];
        $enquiry_type = $_POST['type'];
        $mobileAvailable  = $db->getItemFromDB("SELECT crme_mobile FROM finascop_crm_prospect WHERE id = {$id} ");
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
        $qry = $db->perform('finascop_crm_prospect', $data, 'update', 'id=' . $id);

        $convertQry = "SELECT * FROM finascop_crm_prospect where id=$id";

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

    case 'removeCodeMagmt':
        $id = $_POST['id'];
        //$status = $_POST['activestatus'];
        $data = array(
            'crmm_IsActive' => 0
        );
        $qry = $db->perform(FINASCOP_DB . 'finascop_crm_prospect', $data, 'update', 'id=' . $id);
        if ($qry) {
            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'saveCodes':

        $id = $_POST['id'];

        $data['crpr_mode'] = 5;

        $data['invitationCode'] = $_POST['invitationCode'];
        $data['codeType'] = $_POST['codeType'];
        $data['validity'] = $_POST['validity'];
        $data['referrerTypeId'] = $_POST['referrerType'];
        if($_POST['referrerId'] > 0){
            $data['referrerId'] = $_POST['referrerId'];
        }else{
            $data['crpr_orgName'] = $_POST['referrerName'];
        }
        
        $data['crpr_ExpiredOn'] = date('Y-m-d', strtotime($_POST['crpr_ExpiredOn'])) . ' ' . date('H:i', strtotime($_POST['crpr_ExpiredTime']));
        $data['status'] = $_POST['status'];
        if($_POST['blockMerchant'] == 1){
            $data['blockMerchant'] = $_POST['blockMerchant'];
            $data['codePlanType'] = $_POST['codePlanType'];
        }
        
        $isUnique = $db->getItemSafe("SELECT COUNT(*) FROM finascop_crm_prospect WHERE invitationCode ='?' ", "s", [$_POST['invitationCode']]);
        if ($isUnique > 0) {
            echo "{success: false, msg: 'Code already existing.' }";
            exit;
        }
        $db->query('begin');
        if ($id > 0) {
            $data['crpr_UpdatedOn'] = date('Y-m-d H:i:s');
            $data['crpr_UpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('finascop_crm_prospect', $data, 'update', " id = {$id} ");
        } else {
            $codeLink = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'CODELINK'");
            $data['invitationLink'] = $codeLink.$_POST['invitationCode'];
            $data['crpr_CreatedOn'] = date('Y-m-d H:i:s');
            $data['crpr_CreatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('finascop_crm_prospect', $data);
        }
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;
}
