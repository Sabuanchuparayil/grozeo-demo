<?php

require_once(INCLUDE_PATH . "/finascop_accounts_Master.php");
require_once(INCLUDE_PATH . "/finascop_common_functions.php");
switch ($op)
{

    case 'listCompany':
        $data = $_POST;
        $company = new \finascop\accounts\Master\Company();
        $company->listCompany($data);
        break;

    case 'saveCompany':
        global $db;
        $db->query('begin');
        $data = $_POST;
        $company = new \finascop\accounts\Master\Company();
        $isAddNew = intval($data['comp_id']) > 0 ? false : true;

        $status = $company->saveCompany($data, $isAddNew, false);
        if ($status)
        {
            echo "{success: true}";
            $db->query('commit');
        }
        else
        {
            echo "{success: false, errors:  'FINASCOP: Error occured while saving data' }";
        }

        break;

    case 'getDetails':

        $company = new \finascop\accounts\Master\Company();
        $company->getDetais($_POST['id']);

        break;

    case 'changeStatus':

        $data = $_POST;
        $company = new \finascop\accounts\Master\Company();
        $company->changeStatus($data);

        break;

    case 'checkAudit':

        $company = new \finascop\accounts\Master\Company();
        $company->checkAudit($_POST['comp_id'], $_POST['auditing_company']);

        break;

    case 'saveApiDomains':

        $company = new \finascop\accounts\Master\Company();
        $company->saveApiDomains($_POST['cmp_id'], $_POST['validip']);

        break;

    case 'getapiDomains':

        $company = new \finascop\accounts\Master\Company();
        $company->getApiDomains($_POST['cmp_id']);

        break;

    case 'deleteValidip':

        $company = new \finascop\accounts\Master\Company();
        $company->deleteValidIp($_POST['comp_id'], $_POST['validip']);

        break;
    case 'getCompanyBranches':
        $compId = $_POST['compId'];
        $branchIds = $db->getMultipleData("SELECT br_ID,comp_id FROM finascop_branch_company WHERE comp_id = {$compId} ", true);
        $branch = array();
        foreach ($branchIds as $branchId) {
            $branchDetails = $db->getFromDb("SELECT br_PyramidLevel,br_ID,br_Name,br_Phone,br_status,br_Lat,br_Lng,br_pincode,0 as b_marker FROM finascop_branch where br_status = 'Active' and br_ID ={$branchId['br_ID']}", true);
            array_push($branch, $branchDetails);
}										
        //print_r($branch);
        if (!empty($branch)) {
            echo json_encode($branch);
        } else
            echo [];
        break;
}										
