<?php
	
	/*
		* Created on 01-Aug-08
		* @author : Ratheesh Kumar CK <ratheesh@saturn.in>
		*
		* Will Insert / Update a USER
		*
	*/
	
	//Check if the Same USER ID Already Exists or Not
	
	
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
	
	
	
	if ($_POST['profile']['user_type'] == 4) {
		$qry = "SELECT COUNT(*) from " . FINASCOP_DB . "finascop_company WHERE auditing_company = 'Yes'";
		$sel = $db->getItemFromDB($qry);
		if ($sel == 0) {
			echo "{success:false,errors: { reason: 'Cannot create auditor!Auditing Company does not exist, Please create auditing Company.' }}";
			exit();
		}
	}
	
	$user = array(
    'UserName' => $_POST['user']['admin_username'],
    'Passwd' => password_hash(trim($_POST['user']['admin_password']), PASSWORD_DEFAULT),
    'UserEmail' => $_POST['user']['admin_email'],
    'UserStatus' => 1,
    'IsActive' => 1,
    'RoleId' => intval($_POST['user']['id_admin_role']),
    'JSCacheTime' => ''
	);
	
	if (!isset($_POST['user']['id_admin_role'])) {
		unset($user['RoleId']);
	}
	
	if (trim($_POST['user']['admin_password']) == "") {
		unset($user['Passwd']);		
		} else {
		$user['Passwd'] = password_hash(trim($_POST['user']['admin_password']), PASSWORD_DEFAULT);
		
		// Must be exact 32 chars (256 bit)
		$passkeyencrass = substr(hash('sha256', ENCRASS_KEY, true), 0, 32);		
		// IV must be exact 16 chars (128 bit)
		$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);		
		$user['Encrass'] = base64_encode(openssl_encrypt(trim($_POST['user']['admin_password']), ENCRASS_METHOD, $passkeyencrass, OPENSSL_RAW_DATA, $iv));				 
		//$decrypted = openssl_decrypt(base64_decode("QEpTcVN5ohmA7sIdwtzWGQ=="), $method, $password, OPENSSL_RAW_DATA, $iv);
		if(empty($user['Encrass'])){
			echo "{errors: { reason: 'Encrass is not enabled' }}";
			exit;
		}
	}
	
	
	if (!isset($_POST['user']['id_admin_role'])) {
		unset($user['RoleId']);
	}
	
	if (!isset($_POST['user']['admin_usernum'])) {
		unset($user['UserNumber']);
	}
	
	$UserId = $_POST['user']['uidnr_admin'];
	
	$profile = array(
    'FirstName' => $_POST['profile']['admin_fname'],
    'LastName' => $_POST['profile']['admin_lname'],
    'Address' => $_POST['profile']['admin_address'],
    'Telephone' => $_POST['profile']['admin_telephone'],
    'Office' => $_POST['profile']['admin_telephone'],
    'typID' => $_POST['profile']['user_type']); 


$db->query('begin');
$_POST['profile']['cmp_br'] = $_POST['cmp_br'];
if ($_POST['profile']['user_type'] == 2) {
$_POST['profile']['cmp_br']['user_company'] = explode(',', $_POST['cmp_br']['active_company']);
}

if ($_POST['profile']['user_type'] == 3) {
$_POST['profile']['cmp_br']['user_company'] = explode(',', $_POST['cmp_br']['active_company']);
$_POST['profile']['cmp_br']['user_branch'] = explode(',', $_POST['cmp_br']['active_branch']);
}
if ($_POST['profile']['user_type'] == 4) {
$_POST['profile']['cmp_br']['user_company'] = explode(',', $_POST['cmp_br']['auditor_company']);
$_POST['profile']['cmp_br']['user_branch'] = explode(',', $_POST['cmp_br']['auditor_branch']);
}

$userdets = new \finascop\User();

$UserId = $userdets->saveUser($profile,$user,$_POST['profile'],$UserId,(empty($user['uidnr_admin'])?true:false),false);

$db->query('commit');
//If Self Profile / Preference is updated by the logged in user
//then update his session values
if ($UserId == $_SESSION['admin']->Finascop_UserId) {
$_SESSION['admin']->FirstName = $profile['FirstName'];
$_SESSION['admin']->LastName = $profile['LastName'];
}
echo "{success: true,user_id:" . $UserId . "}";

