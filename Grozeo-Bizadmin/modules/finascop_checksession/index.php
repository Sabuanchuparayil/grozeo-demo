<?php
	
require_once(EXTERNAL_LIBRARY_PATH);
require_once(INCLUDE_PATH . "/finascop_User.php");
require_once(INCLUDE_PATH . "/finascop_common_functions.php");

switch ($op) {
    
    case 'getComboStore':
        $ind = $_GET['ind'];
        switch ($ind) {
            case 1:

                $user = new \finascop\User();
                $items=$user->getUserActiveComapnies($_SESSION['admin']->finascop_typId,$_SESSION['admin']->Finascop_UserId);
                break;
            case 2:
                $user = new \finascop\User();
                $items=$user->getUserActiveBranches($_SESSION['admin']->finascop_typId,$_SESSION['admin']->Finascop_UserId,$_POST['company']);
        }
		if ($items) {
			echo json_encode($items);
			} else {
			echo '{"success": true,"data":[]}';
		}
        break;

    case 'setCompanyAndBranch':

        $data = $_POST;

        $user = new \finascop\User();
        $user->setCompanyAndBranchInSession($_SESSION['admin'], $data);

        switch ($_SESSION['admin']->finascop_typId) {
            case 1:
                $qry = "SELECT count(*) as cnt from " . FINASCOP_DB . "finascop_branch "
                        . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company`) ";
                break;
            case 2:
                $qry = "SELECT count(*) as cnt from " . FINASCOP_DB . "finascop_branch"
                        . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id IN( SELECT comp_id  from " . FINASCOP_DB . "finascop_user_activecompanies WHERE UserId = {$_SESSION['admin']->Finascop_UserId})) ";
                break;
            case 3:
                $qry = "SELECT count(*) as cnt from " . FINASCOP_DB . "finascop_company 
			WHERE  comp_id IN( SELECT comp_id  from " . FINASCOP_DB . "finascop_branch_company 
			WHERE br_Id IN(SELECT br_Id from " . FINASCOP_DB . "finascop_user_activebranches WHERE UserId = {$_SESSION['admin']->Finascop_UserId})) ";
                break;
            case 4:
                $qry = "SELECT count(*) as cnt from " . FINASCOP_DB . "finascop_branch inner join " . FINASCOP_DB . "finascop_branch_company on finascop_branch_company.br_Id = finascop_branch.br_Id "
                        . " WHERE  finascop_branch.br_Id IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_user_auditingbranches` WHERE   UserId = {$_SESSION['admin']->Finascop_UserId} ) ";
                break;
            default:
                $qry = "SELECT 0 as cnt ";
        }

        $_SESSION['admin']->AssignedBranchCount = $db->getItemFromDB($qry);

        /*BRM CPD*/
            $_SESSION['admin']->current_branch_cpdId = $db->getItemFromDB("SELECT br_cpd from " . FINASCOP_DB . "finascop_branch WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");
            $_SESSION['admin']->current_branch_cpd = $db->getItemFromDB("SELECT br_Name from " . FINASCOP_DB . "finascop_branch WHERE br_ID = {$_SESSION['admin']->current_branch_cpdId}");
            $_SESSION['admin']->current_branch_iscpd = $db->getItemFromDB("SELECT br_PyramidLevel from " . FINASCOP_DB . "finascop_branch WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");
        $out = array("success" => true, "valid" => true,
            "data" => array("finascop_current_company_id" => $_SESSION['admin']->finascop_current_company_id,
                "finascop_current_branch_id" => $_SESSION['admin']->finascop_current_branch_id,
                "current_branch_iscpd" => $_SESSION['admin']->current_branch_iscpd,
                "finascop_current_company" => $_SESSION['admin']->finascop_current_company,
                "current_branch" => $_SESSION['admin']->current_branch, "is_auditor" => $_SESSION['admin']->is_auditor,
                "finascop_current_company_isactive" => $_SESSION['admin']->finascop_current_company_isactive,
                "finascop_current_branch_isactive" => $_SESSION['admin']->finascop_current_branch_isactive),
            "AssignedBranchCount" => $_SESSION['admin']->AssignedBranchCount);
        echo json_encode($out);

        break;
}						