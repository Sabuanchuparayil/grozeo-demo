<?php

/*
 * Created on 23-Jul-08
 * @author : Ratheesh Kumar CK <ratheesh@saturn.in>
 *
 * Checks wheter the inputted USERName & Password Exists, login success or not.
 *
 * If it is sucess, sets values to session and return data
 */
require_once(EXTERNAL_LIBRARY_PATH);
require_once(ROOT . '/finascop_config/lib.php');
require_once(INCLUDE_PATH . "/finascop_User.php");


//Declare Globals
global $db;

function authJsonResponse(array $payload): void
{
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload);
}

$captcha_entered = $_POST['loginCaptcha'] ?? '';
$loginUsername = $_POST['loginUsername'] ?? '';
$loginPassword = $_POST['loginPassword'] ?? '';
$rememberMe = $_POST['rememberMe'] ?? 0;
$remember = $_POST['remember'] ?? 0;
// TODO: Implement proper API key/token auth for app clients (mf+ak); do not bypass password verification.
if ($mf !== false && $ak !== false) {

    if (validateMachineFinger($mf) == false) {
        authJsonResponse(['errors' => ['reason' => 'Sorry, you are not an authorised user.']]);
        return;
    }
    if (validateAuthKey($ak) == false) {
        authJsonResponse(['errors' => ['reason' => 'Invalid Time line, check your system clock']]);
        return;
    }
} else {
    if (($loginUsername == "" || $loginPassword == "") || $captcha_entered == "") {
        authJsonResponse(['errors' => ['reason' => 'Username,Password and Captcha cannot be blank.']]);
        return;
    } else if ($captcha_entered != $_SESSION['rand_code']) {
        authJsonResponse(['errors' => ['reason' => 'The answer you have entered is not correct.']]);
        $_SESSION['auth_failures'] = (!empty($_SESSION['auth_failures']) ? $_SESSION['auth_failures'] : 0) + 1;
        return;
    }
}

$query = "select UserId, Passwd, IsActive from " . FINASCOP_DB . "finascop_usr_master where UserName = ?";
//$rs 		= $db->query($query);
$rd = $db->getFromSafe($query, "s", [$loginUsername], TRUE);


if (count($rd) == 0) {
    authJsonResponse(['errors' => ['reason' => USER_NOT_FOUND]]);
} else {
    //$rd = $db->fetch_array($rs); // fetch the data from result
    /* compare the passwords .. */
    $passwordMatch = false;
    if (password_verify($loginPassword, $rd['Passwd'])) {
        // Modern bcrypt hash
        $passwordMatch = ($rd['IsActive'] == 'Yes');
    } else if ($rd['IsActive'] == 'Yes' && ($rd['Passwd'] === $loginPassword || $rd['Passwd'] === md5($loginPassword))) {
        // Legacy MD5 - migrate to bcrypt on next login
        $newHash = password_hash($loginPassword, PASSWORD_DEFAULT);
        $db->executeSafe('UPDATE ' . FINASCOP_DB . 'finascop_usr_master SET Passwd = ? WHERE UserId = ?', 'si', [$newHash, $rd['UserId']]);
        $passwordMatch = true;
    }
    if ($passwordMatch) {
        $password = $rd['Passwd'];
        //$query 		= "select  a.UserName,a.IsSuperUser,a.RoleId,b.UserId,b.FirstName,b.LastName,b.typId,b.typdetsid from " . FINASCOP_DB . "finascop_usr_master a inner join  " . FINASCOP_DB . "finascop_usr_profile b on a.UserId = b.UserId where a.UserId = " . $rd['UserId'];
        $query = "SELECT * FROM " . FINASCOP_DB . "finascop_usr_master a, " . FINASCOP_DB . "finascop_usr_profile b WHERE a.UserId = b.UserId AND a.UserId = " . $rd['UserId'];
        //$rs 		= $db->query($query);
        $rs = $db->getFromDB($query, TRUE);
        if (count($rs) == 0)
            authJsonResponse(['errors' => ['reason' => LOGIN_FAIL]]);
        else {

            $rs = (object) $rs;
            $_SESSION['admin'] = null;
            $TMPSESSION['admin'] = $rs;
            if (isset($TMPSESSION['b4admin'])) {
                $SESS_DATA = $TMPSESSION['b4admin'];
                if (is_object($SESS_DATA)) {
                    foreach ($SESS_DATA as $k => $v) {
                        $TMPSESSION['admin']->$k = $v;
                    }
                }
            }
            //To allow only single session of a user
//            $SessionUpdatedInDB = allowSingleSessionOfUser($TMPSESSION['admin']->UserId);
//            if (!$SessionUpdatedInDB) {
//                echo "{errors: { reason: 'Login Failed!. Unable to register session in Database.' }}";
//                exit(1);
//            }
//            session_start();
            $TMPSESSION['admin']->finascop_typId = $TMPSESSION['admin']->typId;
            $TMPSESSION['admin']->Finascop_UserId = $TMPSESSION['admin']->UserId;

            //unset($TMPSESSION['admin']->typId);

            $roleName = $db->getItemFromDB('select RoleName from sys_role where RoleId = ' . $TMPSESSION['admin']->RoleId);
            ($roleName == 'Super User') ? $TMPSESSION['isSuperUser'] = true : $TMPSESSION['isSuperUser'] = false;

            //Get the Permissions Allowed to this User and store it in a session
            $qry = "select group_concat(distinct r.SysModOpId SEPARATOR ';') as role_perms,"
                    . " group_concat(distinct c.SysModOpId SEPARATOR ';') as user_perms "
                    . "from " . FINASCOP_DB . "finascop_usr_master u left join sys_role_capability r on (u.RoleId=r.RoleId) "
                    . "left join usr_capability c on (c.UserId=u.UserId) where u.UserId='" . $TMPSESSION['admin']->UserId . "'";

            $rd = $db->getFromDB($qry, true);
            $rd['role_perms'] = explode(PERM_SEPERATOR, $rd['role_perms']);
            $rd['user_perms'] = explode(PERM_SEPERATOR, $rd['user_perms']);


            $TMPSESSION['admin']->perms = array_values(array_unique(array_merge($rd['role_perms'], $rd['user_perms'])));
            //$TMPSESSION['admin']->perms =  $rd['user_perms'];
            //Get the list of Menus needs to be blocked
            $blocked = blocked_capabilities($TMPSESSION['admin']->UserId);
            foreach ($TMPSESSION['admin']->perms as $key => $capability) {
                if (in_array($capability, $blocked['capabilities']))
                    unset($TMPSESSION['admin']->perms[$key]);
            }

            /*
             * Modified By Lakshmi Jayaram
             * ON 06-Oct-2009
             */
            //START
            $tmpmcEnabled = $db->mcEnabled;
            $db->mcEnabled = false;
            //Get the permitted modules for the user
            include(ROOT . "/modules/ui/functions.php");
            $permitted_menus = implode(',', getPermittedMenus());
            $db->mcEnabled = $tmpmcEnabled;
            unset($tmpmcEnabled);

            //$TMPSESSION['admin']->DefaultView = $default_view;
            //get the menu id corresponding to the logged in user's role

            if (strlen($permitted_menus) > 0) {
                $mod_con = " AND b.MenuId in ($permitted_menus) ";
            }
            //$permitted_menus = strlen($permitted_menus) > 0 ? $permitted_menus : '';
            $qry = "select InitFunction from sys_module_operation a,sys_menu b "
                    . "where b.IsEnabled='Yes' and /*a.ModuleName = '$default_module' AND*/ b.ParentMenuId = 0 "
                    . "AND a.MenuId = b.MenuId {$mod_con} ";
            $menu_init_function = $db->getItemFromDB($qry);

            //if(user_access("tickets"))
            //$TMPSESSION['admin']->init_function = 'Application.Documents.init();';
            //Maintain Session in dynamodb
            $request['usertype'] = 3;

            $UserId = $TMPSESSION['admin']->UserId;
            $typeId = $TMPSESSION['admin']->finascop_typId;
            $user = new \finascop\User();
            $user->additionalLoginActions($TMPSESSION['admin'], false, $UserId, $TMPSESSION['admin']->finascop_typId);
            unset($TMPSESSION['admin']->typId);
            /* Modified by sreeram on 5/4/2010
             * reason - Introduced encryption for cookie value
             */
            $is_retalineLite = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'IS_RETALINE_LITE'");
            $TMPSESSION['admin']->IS_RETALINE_LITE = $is_retalineLite;
            $is_medicineRequired = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'IS_MEDICINE_REQUIRED'");
            $TMPSESSION['admin']->IS_MEDICINE_REQUIRED = $is_medicineRequired;
            $defaultrrp = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'DEFAULT_RRP'");
            $TMPSESSION['admin']->DEFAULT_RRP = $defaultrrp;
            if ($rememberMe == 1) {
                //$cookie_val=encrypt_decrypt($TMPSESSION['admin']->UserId.'_'.microtime());
                $stringToEncrypt = $TMPSESSION['admin']->UserId . ':' . $password . ':' . microtime();
                $cookie_val = encrypt($stringToEncrypt);
                setcookie("remember_uidnr_admin", $cookie_val, (time() + (365 * 24 * 60 * 60)));
            }

            if ($remember == 1) {
                header("Location: /");
            } else {
                $TMPSESSION['admin']->IsApplicationLogin = 0;
                $_SESSION = $TMPSESSION;
                authJsonResponse(['success' => true]);
            }
        }
    } else {
        /* password is wrong or deactive user came */
        //echo "{errors: { reason: '".(($rd['IsActive']=='Yes')?PASSWORD_INCORRECT1 . $loginUsername . PASSWORD_INCORRECT2:DEACTIVE_USER)."' }}";
        if ($rd['IsActive'] != '')
            authJsonResponse(['errors' => ['reason' => (($rd['IsActive'] == 'Yes') ? PASSWORD_INCORRECT1 : DEACTIVE_USER)]]);
        else
            authJsonResponse(['errors' => ['reason' => PASSWORD_INCORRECT1]]);
    }
}
