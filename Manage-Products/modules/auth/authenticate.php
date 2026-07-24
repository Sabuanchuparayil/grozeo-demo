<?php
// Suppress display of warnings/deprecations so auth always returns clean JSON
@ini_set('display_errors', '0');

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
    } else if ((string)$captcha_entered !== (string)($_SESSION['rand_code'] ?? '')) {
        authJsonResponse(['errors' => ['reason' => 'The answer you have entered is not correct.']]);
        $_SESSION['auth_failures'] = (!empty($_SESSION['auth_failures']) ? $_SESSION['auth_failures'] : 0) + 1;
        return;
    }
}

$query = "select UserId, Passwd, IsActive from " . FINASCOP_DB . "finascop_usr_master where UserName = ?";
//$rs 		= $db->query($query);
$rd = $db->getFromSafe($query, "s", [$loginUsername], TRUE);


if ($rd === false || empty($rd)) {
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
        if ($rs === false || empty($rs))
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

            // #region agent log
            error_log('[DEBUG-31830d][H1] Post-login session build START for user=' . $TMPSESSION['admin']->UserId);
            // #endregion
            if ($TMPSESSION['admin']->IsSuperUser == 'Yes') {
                $TMPSESSION['isSuperUser'] = true;
            } else {
                $roleName = $db->getItemFromDB('select RoleName from sys_role where RoleId = ' . (int) $TMPSESSION['admin']->RoleId);
                $TMPSESSION['isSuperUser'] = ($roleName == 'Super User');
            }
            // #region agent log
            error_log('[DEBUG-31830d][H1] Role resolved, isSuperUser=' . ($TMPSESSION['isSuperUser'] ? 'true' : 'false'));
            // #endregion

            //Get the Permissions Allowed to this User and store it in a session
            $qry = "select group_concat(distinct r.SysModOpId SEPARATOR ';') as role_perms,"
                    . " group_concat(distinct c.SysModOpId SEPARATOR ';') as user_perms "
                    . "from " . FINASCOP_DB . "finascop_usr_master u left join sys_role_capability r on (u.RoleId=r.RoleId) "
                    . "left join usr_capability c on (c.UserId=u.UserId) where u.UserId='" . $TMPSESSION['admin']->UserId . "'";

            $rd = $db->getFromDB($qry, true);
            if ($rd === false || !is_array($rd)) {
                $rd = ['role_perms' => '', 'user_perms' => ''];
            }
            $rd['role_perms'] = explode(PERM_SEPERATOR, $rd['role_perms'] ?? '');
            $rd['user_perms'] = explode(PERM_SEPERATOR, $rd['user_perms'] ?? '');


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
            $tmpmcEnabled = $db->mcEnabled ?? false;
            if (property_exists($db, 'mcEnabled')) { $db->mcEnabled = false; }
            //Get the permitted modules for the user — set session admin so getPermittedMenus() can read it
            $_SESSION['admin'] = $TMPSESSION['admin'];
            include(ROOT . "/modules/ui/functions.php");
            $permittedMenus = getPermittedMenus();
            $permitted_menus = is_array($permittedMenus) ? implode(',', $permittedMenus) : '';
            // #region agent log
            error_log('[DEBUG-31830d][H2] permittedMenus type=' . gettype($permittedMenus) . ' permitted_menus="' . $permitted_menus . '"');
            // #endregion
            if (property_exists($db, 'mcEnabled')) { $db->mcEnabled = $tmpmcEnabled; }
            unset($tmpmcEnabled);

            //$TMPSESSION['admin']->DefaultView = $default_view;
            //get the menu id corresponding to the logged in user's role

            $mod_con = '';
            if (strlen($permitted_menus) > 0) {
                $mod_con = " AND b.MenuId in ($permitted_menus) ";
            }
            // #region agent log
            error_log('[DEBUG-31830d][H1] About to run InitFunction query, mod_con="' . $mod_con . '"');
            // #endregion
            $qry = "select a.InitFunction from sys_module_operation a,sys_menu b "
                    . "where b.IsEnabled='Yes' and b.ParentMenuId = 0 "
                    . "AND a.MenuId = b.MenuId {$mod_con} ";
            $menu_init_function = $db->getItemFromDB($qry);
            // #region agent log
            error_log('[DEBUG-31830d][H1] InitFunction query OK, result=' . var_export($menu_init_function, true));
            // #endregion

            //if(user_access("tickets"))
            //$TMPSESSION['admin']->init_function = 'Application.Documents.init();';
            //Maintain Session in dynamodb
            $request['usertype'] = 3;

            $UserId = $TMPSESSION['admin']->UserId;
            $typeId = $TMPSESSION['admin']->finascop_typId;
            // #region agent log
            error_log('[DEBUG-31830d][H3] About to call additionalLoginActions');
            // #endregion
            $user = new \finascop\User();
            try {
                $user->additionalLoginActions($TMPSESSION['admin'], false, $UserId, $TMPSESSION['admin']->finascop_typId);
                // #region agent log
                error_log('[DEBUG-31830d][H3] additionalLoginActions completed OK');
                // #endregion
            } catch (\Throwable $loginActionError) {
                // #region agent log
                error_log('[DEBUG-31830d][H3] additionalLoginActions CAUGHT: ' . $loginActionError->getMessage());
                // #endregion
            }
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

            // #region agent log
            error_log('[DEBUG-31830d][H4] About to set session and return success JSON');
            // #endregion
            if ($remember == 1) {
                header("Location: /");
            } else {
                $TMPSESSION['admin']->IsApplicationLogin = 0;
                $_SESSION = $TMPSESSION;
                // #region agent log
                error_log('[DEBUG-31830d][H4] Session set, returning success JSON now');
                // #endregion
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
