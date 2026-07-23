<?php

/*
		* Created on 01-Aug-08
		* @author : Ratheesh Kumar CK <ratheesh@saturn.in>
		*
		* Will Insert / Update a USER
		*
	*/

//Check if the Same USER ID Already Exists or Not

$usRoles = [];
if ($_POST['user']['id_admin_role'] > 0) {
	$us1Role = array(
		'RoleId' => $_POST['user']['id_admin_role'],
		'type' => 1
	);
	array_push($usRoles, $us1Role);
}

if (isset($_POST['usrRole']['secondaryRole']) && !empty($_POST['usrRole']['secondaryRole'])) {
	$secondaryRoles = explode(',', $_POST['usrRole']['secondaryRole']);
	foreach ($secondaryRoles as $secondaryRole) {
		$us2Role = array(
			'RoleId' => $secondaryRole,
			'type' => 0
		);
		array_push($usRoles, $us2Role);
	}
}

function userUpdation($RefId, $postdata)
{
	global $db;
	$user = array(
		'UserName' => $postdata['user']['admin_username'],
		'Passwd' => password_hash(trim($postdata['user']['admin_password']), PASSWORD_DEFAULT),
		'UserEmail' => $postdata['user']['admin_email'],
		'UserStatus' => 1,
		'IsActive' => 1,
		'RoleId' => intval($postdata['user']['id_admin_role']),
		'JSCacheTime' => '',
		'RefId' => $RefId
	);

	if (!isset($postdata['user']['id_admin_role'])) {
		unset($user['RoleId']);
	}

	if (trim($postdata['user']['admin_password']) == "") {
		unset($user['Passwd']);
	} else {
		$user['Passwd'] = password_hash(trim($postdata['user']['admin_password']), PASSWORD_DEFAULT);

		// Must be exact 32 chars (256 bit)
		$passkeyencrass = substr(hash('sha256', ENCRASS_KEY, true), 0, 32);
		// IV must be exact 16 chars (128 bit)
		$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
		$user['Encrass'] = base64_encode(openssl_encrypt(trim($postdata['user']['admin_password']), ENCRASS_METHOD, $passkeyencrass, OPENSSL_RAW_DATA, $iv));
		//$decrypted = openssl_decrypt(base64_decode("QEpTcVN5ohmA7sIdwtzWGQ=="), $method, $password, OPENSSL_RAW_DATA, $iv);
		if (empty($user['Encrass'])) {
			echo "{errors: { reason: 'Encrass is not enabled' }}";
			exit;
		}
	}


	if (!isset($postdata['user']['id_admin_role'])) {
		unset($user['RoleId']);
	}

	if (!isset($postdata['user']['admin_usernum'])) {
		unset($user['UserNumber']);
	}

	$UserId = $postdata['user']['uidnr_admin'];

	$profile = array(
		'FirstName' => $postdata['profile']['admin_fname'],
		'LastName' => $postdata['profile']['admin_lname'],
		'Address' => $postdata['profile']['admin_address'],
		'Telephone' => $postdata['profile']['admin_telephone'],
		'Office' => $postdata['profile']['admin_telephone'],
		'typID' => $postdata['profile']['user_type']
	);


	$db->query('begin');
	$postdata['profile']['cmp_br'] = $postdata['cmp_br'];
	if ($postdata['profile']['user_type'] == 2) {
		$postdata['profile']['cmp_br']['user_company'] = explode(',', $postdata['cmp_br']['active_company']);
	}

	if ($postdata['profile']['user_type'] == 3) {
		$postdata['profile']['cmp_br']['user_company'] = explode(',', $postdata['cmp_br']['active_company']);
		$postdata['profile']['cmp_br']['user_branch'] = explode(',', $postdata['cmp_br']['active_branch']);
	}
	if ($postdata['profile']['user_type'] == 4) {
		$postdata['profile']['cmp_br']['user_company'] = explode(',', $postdata['cmp_br']['auditor_company']);
		$postdata['profile']['cmp_br']['user_branch'] = explode(',', $postdata['cmp_br']['auditor_branch']);
	}
	if ($postdata['profile']['user_type'] == 5) {
		$postdata['profile']['cmp_br']['user_company'] = explode(',', $postdata['cmp_br']['office_company']);
		$postdata['profile']['cmp_br']['user_branch'] = explode(',', $postdata['cmp_br']['branch_office']);
	}
	$userdets = new \finascop\User();

	$UserId = $userdets->saveUser($profile, $user, $postdata['profile'], $UserId, (empty($user['uidnr_admin']) ? true : false), false);

	$userRoles = [];
	if ($_POST['user']['id_admin_role'] > 0) {
		$usr1Role = array(
			'RoleId' => $_POST['user']['id_admin_role'],
			'UserId' => $UserId,
			'type' => 1
		);
		array_push($userRoles, $usr1Role);
	}
	
	if (isset($_POST['usrRole']['secondaryRole']) && !empty($_POST['usrRole']['secondaryRole'])) {
		$secondaryRoles = explode(',', $_POST['usrRole']['secondaryRole']);
		foreach ($secondaryRoles as $secondaryRole) {
			$usr2Role = array(
				'RoleId' => $secondaryRole,
				'UserId' => $UserId,
				'type' => 0
			);
			array_push($userRoles, $usr2Role);
		}
	}
	if ($userRoles[0]['RoleId'] > 0) {
		$status = $db->query("DELETE FROM resource_role_mapping WHERE UserId = {$UserId}");
		foreach ($userRoles as $userRole) {
			$roleData = array(
				'RoleId' => $userRole['RoleId'],
				'UserId' => $userRole['UserId'],
				'type' => $userRole['type']
			);
			$status = $db->perform('resource_role_mapping', $roleData);
		}
	}

	//usrRole
	if (!empty($postdata['resource']['roleDesignation']))
		$designationId = $db->getItemFromDB("SELECT id FROM org_designation WHERE name = '" . $postdata['resource']['roleDesignation'] . "'");
	else {
		$designationId = 0;
	}
	$resource = array(
		'UserId' => $UserId,
		'dateofBirth' => date('Y-m-d', strtotime($postdata['resource']['dateofBirth'])),
		'emergencyContactName' => $postdata['resource']['emergency_contact_name'],
		'emergencyContact' => $postdata['resource']['emergency_contact_number'],
		'relationship' => $postdata['resource']['emergency_relationship'],
		'address' => $postdata['resource']['communication_address'],
		'bloodgroup' => $postdata['resource']['bloodgroup'],
		'designationId' => $designationId
	);
	$dataExists = $db->getItemFromDB("SELECT COUNT(*) FROM resource_data WHERE UserId = {$UserId}");
	$resource = array_filter($resource);
	if ($dataExists > 0) {
		$status = $db->perform('resource_data', $resource, 'update', " UserId = {$UserId}");
	} else {
		$status = $db->perform('resource_data', $resource);
	}

	$db->query('commit');


	//If Self Profile / Preference is updated by the logged in user
	//then update his session values
	if ($UserId == $_SESSION['admin']->Finascop_UserId) {
		$_SESSION['admin']->FirstName = $profile['FirstName'];
		$_SESSION['admin']->LastName = $profile['LastName'];
	}

	echo "{success: true,user_id:" . $UserId . "}";
}

if (trim($_POST['user']['admin_username']) == "") {
	echo "{success:false,errors: { reason: 'The Login name cannot be blank' }}";
	exit();
}
$qry = "SELECT count(*) from " . FINASCOP_DB . "finascop_usr_master where UserId<>'" . $user['uidnr_admin']
	. "' and  UserName='" . $user['admin_username'] . "'";

$duplicates = $db->getItemFromDB($qry);

if ($duplicates > 0) {
	echo "{success:false,errors: { reason: '" . USER_CHECK . "' }}";
	exit();
}

$qry = "SELECT count(*) from " . FINASCOP_DB . "finascop_usr_master where UserId<>'" . $user['uidnr_admin']
	. "' and  UserEmail='" . $user['admin_email'] . "'";

$duplicates = $db->getItemFromDB($qry);

if ($duplicates > 0) {
	echo "{success:false,errors: { reason: '" . USER_EMAIL . "' }}";
	exit();
}

$qry = "SELECT count(*) from " . FINASCOP_DB . "finascop_usr_profile where UserId<>'" . $user['uidnr_admin']
	. "' and  Telephone='" . $_POST['profile']['admin_telephone'] . "'";

$duplicates = $db->getItemFromDB($qry);

if ($duplicates > 0) {
	echo "{success:false,errors: { reason: 'Mobile already exists.' }}";
	exit();
}


if ($_POST['profile']['user_type'] == 4) {
	$qry = "SELECT COUNT(*) from " . FINASCOP_DB . "finascop_company WHERE auditing_company = 'Yes'";
	$sel = $db->getItemFromDB($qry);
	if ($sel == 0) {
		echo "{success:false,errors: { reason: 'Cannot create auditor!Auditing Company does not exist, Please create auditing Company.' }}";
		exit();
	}
}
//api for user creation in partner
// Extract RoleId values
$roleIds = array_column($usRoles, 'RoleId');
$roleIds = array_map('intval', $roleIds);
// Convert array to a comma-separated string
$roleIdString = implode(',', $roleIds);




$apiCallRequired = $db->getItemFromDB("SELECT COUNT(*) FROM sys_role WHERE permissionOn > 0 AND RoleId IN ({$roleIdString})");
if ($apiCallRequired > 0) {
	$refIds = $db->getItemFromDB("SELECT GROUP_CONCAT(RefId) FROM sys_role WHERE RoleId IN ({$roleIdString})");
	$refIds = explode(',',$refIds);
	$url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'BAURL'");
	$fields = array(
		"FullName" => $_POST['profile']['admin_fname'],
		"Address" => $_POST['profile']['admin_address'],
		"Phone" =>  $_POST['profile']['admin_telephone'],
		"Email" => $_POST['user']['admin_email'],
		"Password" => $_POST['user']['admin_password'],
		"City" => '',
		"State" => '',
		"Country" => '',
		"UserType" => 3,
		"roleId" => $refIds,
		"areaId" => ''
	);
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
	if ($result['refId'] > 0) {
		userUpdation($result['refId'], $_POST);		
	} else {
		$apiResponse = "API conflicted. Please try again!".$result['message'];
		echo "{success:false,errors: { reason: '" . $apiResponse . "' }}";
		exit();
	}
} else {
	userUpdation(0, $_POST);
}
