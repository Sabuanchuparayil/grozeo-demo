<?php
	
	/*
		* Created on 30-Jul-08
		* @author : Ratheesh Kumar CK <ratheesh@saturn.in>
		*
		* Complete Usermanagement of the Application will be handled by this module.
		*
	*/
	writeLog(__FILE__);
	require_once(EXTERNAL_LIBRARY_PATH);
	require_once(ROOT . '/finascop_config/lib.php');
	require_once(ROOT . '/finascop_config/config.php');
	require_once(INCLUDE_PATH . "/finascop_User.php");
	
	switch ($op) {
		case 'setUserPassword':
        $userID = $_POST['user_id'];
        $currentPassword = $_POST['currentPassword']; // verified below with password_verify
        $newPassword = password_hash(trim($_POST['newPassword']), PASSWORD_DEFAULT);
        $qry = "select count(*) from " . FINASCOP_DB . "finascop_usr_master where UserId='{$userID}'";
		
        if (intval($db->getItemFromDB($qry)) > 0) {
            //Update Password
			// Must be exact 32 chars (256 bit)
			$passkeyencrass = substr(hash('sha256', ENCRASS_KEY, true), 0, 32);		
			// IV must be exact 16 chars (128 bit)
			$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);		
			$encrypted = base64_encode(openssl_encrypt(trim($_POST['newPassword']), ENCRASS_METHOD, $passkeyencrass, OPENSSL_RAW_DATA, $iv));	
			if(empty($encrypted)){
				echo "{errors: { reason: 'Encrass is not enabled' }}";
				exit;
			}
            $data = array("Passwd" => $newPassword,'Encrass'=>$encrypted);
            $status = $db->perform(FINASCOP_DB . "finascop_usr_master", $data, "update", "UserId='$userID'");
            if ($status) {
                echo "{success: true}";
			} else
			echo "{errors: { reason: '" . UPDATE_FAIL_ADM . "' }}";
			}else {
            echo "{errors: { reason: '" . CHANGE_PWD_INCORRECT . "' }}";
		}
        break;
		case 'check-duplication':
		
        if ($_POST['type'] == 'emp' && $_POST['id_employee'] != "") {
            $employeeID = $_POST['id_employee'];
            $userID = $db->getItemFromDB("SELECT uidnr_admin from employee where id_employee=" . $employeeID);
		}
        $username = $_POST['value'];
        $o = new stdClass;
        $o->success = true;
        $o->valid = true;
        if (isDuplicate($username, false, $userID)) {
            $o->valid = false;
            $o->reason = REP_DUP_USER;
		}
        header("Content-Type: application/json");
        echo json_encode($o);
        break;
		
		
		case 'check-current_password':
        $currentPassword = md5($_POST['value']);
        $userID = $_SESSION['admin']->Finascop_UserId;
        $o = new stdClass;
        $o->success = true;
        $o->valid = false;
        $o->reason = CURR_PASS;
        $qry = "select count(*) from " . FINASCOP_DB . "finascop_usr_master where UserId='$userID' and Passwd='" . $currentPassword . "'";
        if (intval($db->getItemFromDB($qry)) > 0) {
            $o->valid = true;
            unset($o->reason);
		}
        header("Content-Type: application/json");
        echo json_encode($o);
        break;
		
		
		case 'check_email_duplication' :
        $email = $_POST['value'];
        $UserId = $_POST['id'];
        $o = new stdClass;
        $o->success = true;
        $o->valid = true;
        $qry = "select count(UserId) from " . FINASCOP_DB . "finascop_usr_master where UserEmail ='" . $email . "' ";
        if (!empty($UserId)) {
            $qry .= " and UserId<>" . $UserId;
		}
        if (intval($db->getItemFromDB($qry)) > 0) {
            $o->reason = DUP_EMAIL;
            $o->valid = false;
		}
        header("Content-Type: application/json");
        echo json_encode($o);
        break;
		
		case 'check_username_duplication' :
        $uname = $_POST['value'];
        $UserId = $_POST['id'];
        $o = new stdClass;
        $o->success = true;
        $o->valid = true;
        $qry = "select count(UserId) from " . FINASCOP_DB . "finascop_usr_master where UserName ='" . $uname . "' and UserName<>'' ";
        if (!empty($UserId)) {
            $qry .= " and UserId<>" . $UserId;
		}
        if (intval($db->getItemFromDB($qry)) > 0) {
            $o->reason = DUP_USERNAME;
            $o->valid = false;
		}
		
        $qry = "select count(usr_id) from app_user where usr_name ='" . $uname . "' and usr_name<>'' ";
        if (!empty($UserId)) {
            $qry .= " and usr_id<>" . $UserId;
		}
        if (intval($db->getItemFromDB($qry)) > 0) {
			
            $query2 = "select b.br_Name from app_user a inner join branch_id b on a.br_id=b.br_ID  where a.usr_name ='"
			. $uname . "'  ";
            $branch = $db->getFromDB($query2);
            $o->reason = DUP_USERNAME_Branch . ' ' . $branch[0];
            $o->valid = false;
		}
        header("Content-Type: application/json");
        echo json_encode($o);
        break;
		
		
		
		case 'logout':
		
        deleteAll('uploads/tmp/' . session_id());
        session_destroy();
        setcookie("remember_uidnr_admin", false, (time() - 1));
        break;
		
		case 'getUserProfile':
        $id = $_SESSION['admin']->Finascop_UserId;
        $qry = "SELECT u.UserId AS uidnr_admin, u.UserName AS admin_username, u.UserEmail AS admin_email,u.UserStatus AS admin_status, "
		. "u.RoleId AS id_admin_role,p.FirstName AS admin_fname,p.LastName AS admin_lname, "
		. "REPLACE(REPLACE(p.Address,'',''),'','')AS admin_address, p.Telephone AS admin_telephone,p.typId AS user_type "
		. "FROM " . FINASCOP_DB . "finascop_usr_profile p," . FINASCOP_DB . "finascop_usr_master u WHERE "
		. "u.UserId = p.UserId AND u.UserId = {$id}";
		
		
        $tmp = $db->getFromDB($qry, TRUE);
        $reporting_branch = 0;
        if ($tmp['user_type'] == 4)
		$reporting_company = $db->getItemFromDB("SELECT comp_id AS id from " . FINASCOP_DB . "finascop_company WHERE  auditing_company = 'Yes'");
		
        if ($tmp['user_type'] == 2) {
            $active_company = $db->getItemFromDB("SELECT GROUP_CONCAT(comp_id) from " . FINASCOP_DB . "finascop_user_activecompanies WHERE UserId = {$tmp['uidnr_admin']}");
			} else {
            $active_branch = $db->getItemFromDB("SELECT GROUP_CONCAT(br_Id) from " . FINASCOP_DB . "finascop_user_activebranches WHERE UserId = {$tmp['uidnr_admin']}");
            if (!empty($active_branch))
			$active_company = $db->getItemFromDB("SELECT GROUP_CONCAT(comp_id) from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id IN ({$active_branch})");
		}
        $auditor_branch = $db->getItemFromDB("SELECT GROUP_CONCAT(br_Id) from " . FINASCOP_DB . "finascop_user_auditingbranches WHERE UserId = {$tmp['uidnr_admin']}");
        if (!empty($auditor_branch))
		$auditor_company = $db->getItemFromDB("SELECT GROUP_CONCAT(comp_id) from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id IN ({$auditor_branch})");
        $user = new \finascop\User();
        $report_to_user = $user->getReportToUser($tmp['uidnr_admin']);
        $data = array(
		"user[uidnr_admin]" => $tmp['uidnr_admin'],
		"user[admin_username]" => $tmp['admin_username'],
		"user[admin_email]" => $tmp['admin_email'],
		"user[admin_status]" => $tmp['admin_status'],
		"user[id_admin_role]" => $tmp['id_admin_role'],
		"profile[admin_fname]" => $tmp['admin_fname'],
		"profile[admin_lname]" => $tmp['admin_lname'],
		"profile[admin_address]" => stripslashes($tmp['admin_address']),
		"profile[user_type]" => $tmp['user_type'],
		"profile[admin_telephone]" => $tmp['admin_telephone'],
		"cmp_br[reporting_company]" => $reporting_company,
		"cmp_br[reporting_branch]" => $reporting_branch,
		"cmp_br[active_company]" => $active_company,
		"cmp_br[active_branch]" => $active_branch,
		"cmp_br[auditor_company]" => $auditor_company,
		"cmp_br[auditor_branch]" => $auditor_branch,
		"cmp_br[report_to_user]" => $report_to_user,
        );
        $resutl = array(
		"success" => "true",
		"data" => $data
        );
        echo json_encode($resutl);
        break;
		
		
		case 'getDetails':
        $id = $_POST['id'];
		
        $qry = "SELECT u.UserId AS uidnr_admin, u.UserName AS admin_username, u.UserEmail AS admin_email,u.UserStatus AS admin_status, u.RoleId AS id_admin_role,p.typId AS user_type,p.FirstName AS admin_fname,p.LastName AS admin_lname, REPLACE(REPLACE(p.Address,'',''),'','')AS admin_address,p.Telephone AS admin_telephone FROM  " . FINASCOP_DB . "finascop_usr_profile p, " . FINASCOP_DB . "finascop_usr_master u WHERE u.UserId = p.UserId AND u.UserId = $id";
		
        $tmp = $db->getFromDB($qry, TRUE);
		
        $user = new \finascop\User();
        $user->getDetails($tmp, $id);
		
        $tmp['admin_address'] = stripslashes($tmp['admin_address']);
		
        $cmp_br = '';
		
        //$reporting_branch = $db->getItemFromDB("SELECT br_Id from " . FINASCOP_DB . "finascop_user_reportingbranch WHERE UserId = {$tmp['uidnr_admin']}");
        $reporting_branch = 0;
        if ($tmp['user_type'] == 4) {
            $reporting_company = $db->getItemFromDB("SELECT comp_id AS id from " . FINASCOP_DB . "finascop_company WHERE  auditing_company = 'Yes'");
		}
		
        if ($tmp['user_type'] == 2) {
            $active_company = $db->getItemFromDB("SELECT GROUP_CONCAT(comp_id) from " . FINASCOP_DB . "finascop_user_activecompanies WHERE UserId = {$tmp['uidnr_admin']}");
			} else {
            $active_branch = $db->getItemFromDB("SELECT GROUP_CONCAT(br_Id) from " . FINASCOP_DB . "finascop_user_activebranches WHERE UserId = {$tmp['uidnr_admin']}");
            if (!empty($active_branch))
			$active_company = $db->getItemFromDB("SELECT GROUP_CONCAT(comp_id) from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id IN ({$active_branch})");
		}
        $auditor_branch = $db->getItemFromDB("SELECT GROUP_CONCAT(br_Id) from " . FINASCOP_DB . "finascop_user_auditingbranches WHERE UserId = {$tmp['uidnr_admin']}");
		
        if (!empty($auditor_branch)) {
            $auditor_company = $db->getItemFromDB("SELECT GROUP_CONCAT(comp_id) from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id IN ({$auditor_branch})");
		}
		
        $report_to_user = $user->getReportToUser($tmp['uidnr_admin']);
		
        if ($tmp['user_type'] == 4) {
			
            $totalCount = $user->getReportingToCount($tmp['uidnr_admin']);
            if ($totalCount > 0) {
                $isreport_to_user = "true";
				} else {
                $isreport_to_user = "false";
			}
		}
        $data = array(
		"user[uidnr_admin]" => $tmp['uidnr_admin'],
		"user[admin_username]" => $tmp['admin_username'],
		"user[admin_email]" => $tmp['admin_email'],
		"user[admin_status]" => $tmp['admin_status'],
		"user[id_admin_role]" => $tmp['id_admin_role'],
		"profile[admin_fname]" => $tmp['admin_fname'],
		"profile[admin_lname]" => $tmp['admin_lname'],
		"profile[admin_address]" => $tmp['admin_address'],
		"profile[admin_telephone]" => $tmp['admin_telephone'],
		"profile[user_type]" => $tmp['user_type'],
		"profile[e_autoapproval]" => $tmp['e_autoapproval'],
		"profile[e_approver]" => $tmp['e_approver'],
		"cmp_br[reporting_company]" => $reporting_company,
		"cmp_br[reporting_branch]" => $reporting_branch,
		"cmp_br[active_company]" => $active_company,
		/* "active_company" => $active_company, */
		"cmp_br[active_branch]" => $active_branch,
		"cmp_br[auditor_company]" => $auditor_company,
		"cmp_br[auditor_branch]" => $auditor_branch,
		"cmp_br[report_to_user]" => $report_to_user,
		"ad[is_auditor]" => $tmp['is_auditor'],
		"isreport_to_user" => $isreport_to_user,
        );
		
        $resutl = array(
		"success" => "TRUE",
		"data" => $data
        );
        echo json_encode($resutl);
        break;
		
		case 'save':
        require(THIS_MODULE_PATH . "/save.php");
        break;
		
		case 'delete':
        require(THIS_MODULE_PATH . "/delete.php");
        break;
		
		case 'getUsers':
        //generateJsComboStore();
        break;
		
		case 'getTZ':
        generate_timezone_data();
        break;
		
		
		case 'changePassword':
        $userID = $_SESSION['admin']->Finascop_UserId;
        $currentPassword = $_POST['currentPassword']; // verified below with password_verify
        $newPassword = password_hash(trim($_POST['newPassword']), PASSWORD_DEFAULT);
		//Encrass
		$passkeyencrass = substr(hash('sha256', ENCRASS_KEY, true), 0, 32);	
		$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);		
		$Encrass = base64_encode(openssl_encrypt(trim($_POST['newPassword']), ENCRASS_METHOD, $passkeyencrass, OPENSSL_RAW_DATA, $iv));			
			if(empty($Encrass)){
				echo "{errors: { reason: 'Encrass is not enabled' }}";
				exit;
			}
        
		$qry = "select count(*) from " . FINASCOP_DB . "finascop_usr_master where UserId='$userID' and Passwd='" . $currentPassword . "'";
		
        if (intval($db->getItemFromDB($qry)) > 0) {
            $data = array("Passwd" => $newPassword,'Encrass'=>$Encrass);
            $status = $db->perform(FINASCOP_DB . "finascop_usr_master", $data, "update", "UserId='$userID'");
            if ($status) {
                echo "{success: true}";
			} else
			echo "{errors: { reason: '" . UPDATE_FAIL_ADM . "' }}";
			}else {
            echo "{errors: { reason: '" . CHANGE_PWD_INCORRECT . "' }}";
		}
		
        break;
		
		
		case 'active':
        $userID = $_POST['uidnr_admin'];
        if (empty($userID))
		return;
        $db->perform(FINASCOP_DB . "finascop_usr_master", array('IsActive' => "func:if(IsActive='Yes', 'No', 'Yes')"), 'update', 'UserId=' . $userID);
        echo '{success:true}';
        break;
		
		
		case 'get_preference' :
        if (!empty($_POST['id'])) {
            $id = $_POST['id'];
			} else {
            $id = $_SESSION['admin']->Finascop_UserId;
		}
        $qry = "SELECT Varname,Varvalue FROM usr_preference	WHERE	UserId = ". $id;
        $rd = $db->getArrayFromDB($qry);
        $o = new stdClass();
        $o->success = true;
        $o->data = $rd;
        echo json_encode($o);
        break;
		
		
		case 'delete_view':
        $ViewId = $_POST['view_id'];
        $qry = "delete from sys_views where ViewId = $ViewId";
        $status = $db->query($qry);
        if ($status) {
            echo "{success:true}";
		}
        break;
		
		
		case 'getPermittedModuleData':
        PermittedModulesJsComboStore($_POST['RoleId'], $_POST['UserId']);
        break;
		
		
		case 'loadDetailStore':
        $query = "SELECT zon_ID,zon_Name FROM branch_zone";
        $data = $db->getMulipleData($query, true);
        echo json_encode($data);
        break;
		
		
		case 'getBranch':
		
        $query = $_POST['query'];
        if ($query != '')
		$con = " and br_Name like '" . $query . "%'";
        else
		$con = '';
		
        $qry = "select br_ID,br_Name from " . FINASCOP_DB . "finascop_branch where br_status = 'Active' AND br_id>1  
		" . $con . " order by br_Name ";
        $branch = $db->getMulipleData($qry, true);
        if (!empty($branch)) {
            $branch = json_encode($branch);
            echo '{"data":' . $branch . '}';
			} else {
            echo '{"data":[]}';
		}
        break;
		
		
		case 'getComboData':
        $ind = $_GET['ind'];
        switch ($ind) {
            case 1:
			$qry = "SELECT comp_id AS id, CONCAT (comp_shortname ,' [', comp_name ,']') AS `name` from " . FINASCOP_DB . "finascop_company "
			. "WHERE auditing_company = 'Yes'";
			break;
            case 3:
            case 5:
			$qry = "SELECT comp_id AS id, CONCAT (comp_shortname ,' [', comp_name ,']') AS `name` from " . FINASCOP_DB . "finascop_company ";
			break;
            case 2:
			if ($_POST['comp_id'] > 0)
			$qry = "SELECT br_ID as id,br_Name as `name` from " . FINASCOP_DB . "finascop_branch where br_ID in (select br_Id from " . FINASCOP_DB . "finascop_branch_company"
			. " where comp_id IN ({$_POST['comp_id']}))";
			else
			$qry = "SELECT br_ID as id,br_Name as `name` from " . FINASCOP_DB . "finascop_branch ";
			break;
            case 4:
            case 6:
			if ($_POST['comp_id'] > 0)
			$qry = "SELECT b.br_ID AS id,CONCAT ((SELECT comp_shortname from " . FINASCOP_DB . "finascop_company WHERE comp_id = bc.comp_id),' [', b.br_Name ,']')  AS `name`
			from " . FINASCOP_DB . "finascop_branch b
			INNER join " . FINASCOP_DB . "finascop_branch_company bc
			ON b.br_ID = bc.br_Id "
			. " where bc.comp_id IN ({$_POST['comp_id']})";
			else
			$qry = "SELECT b.br_ID AS id,CONCAT ((SELECT comp_shortname from " . FINASCOP_DB . "finascop_company WHERE comp_id = bc.comp_id),' [', b.br_Name ,']')  AS `name`
			from " . FINASCOP_DB . "finascop_branch b
			INNER join " . FINASCOP_DB . "finascop_branch_company bc
			ON b.br_ID = bc.br_Id ";
			
			break;
            case 7:
			$typId = $_POST['user_type'];
			$id = $_POST['user_id'];
			if ($_POST['user_id'] > 0) {
				$con = " WHERE UserId != {$_POST['user_id']} ";
			}
			/* else{
				if($typId =4){
				$con = " WHERE UserId != {$_POST['user_id']} and (typId = 4 or UserId in (select UserId  from " . FINASCOP_DB . "finascop_usr_master where IsSuperUser='Yes')  )  ";
				}
			}** */
			
			if ($typId == '4') {
				$condition = (($_POST['user_id'] > 0) ? 'and' : 'where' );
				$con .= " {$condition} typId = {$typId} or UserId in (select UserId AS id  from " . FINASCOP_DB . "finascop_usr_master where IsSuperUser='Yes')  ";
			}
			$qry = "SELECT UserId AS id, CONCAT(FirstName,' ',LastName) AS `name` FROM  " . FINASCOP_DB . "finascop_usr_profile {$con}";
			
			break;
		}
        if (!empty($qry)) {
            $qry .= " ORDER BY `name` ASC";
            finascop_getjsonkeyarray($qry);
		}
        break;
		
		default:
        require(THIS_MODULE_PATH . "/list.php");
        break;
		
		
		
		case 'loaddisablecompanyStore':
        $qry = "SELECT comp_id AS dcid, CONCAT (comp_shortname ,' [', comp_name ,']') AS `dcname` from " . FINASCOP_DB . "finascop_company "
		. "WHERE cmp_status = 'Inactive'";
		
        $dcompany = $db->getMulipleData($qry, true);
        if (!empty($dcompany)) {
            $dcompany = json_encode($dcompany);
            echo '{"data":' . $dcompany . '}';
			} else {
            echo '{"data":[]}';
		}
		
        break;
		
		
		
		
		case 'loaddisablebranchStore':
        $qry = "SELECT br_ID AS dbid, CONCAT (branch_shortname ,' [', br_Name ,']') AS `dbname` from " . FINASCOP_DB . "finascop_branch "
		. "WHERE br_status = 'Inactive'";
		
        $dbranch = $db->getMulipleData($qry, true);
        if (!empty($dbranch)) {
            $dbranch = json_encode($dbranch);
            echo '{"data":' . $dbranch . '}';
			} else {
            echo '{"data":[]}';
		}
		
        break;
	}
	
