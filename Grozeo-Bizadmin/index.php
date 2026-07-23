<?php
	
/**
 * Created on 21-Jul-09
 * @author : Ratheesh Kumar CK <ratheesh@saturn.in>
 *
 * Main file of the Project. It is the handler of each request
 * Every request is routed through this file.
 */
//session_name("TMU"); 
$GLOBALS['NotCarego'] = true;
require_once __DIR__ . '/includes/session_init.php';
grozeoStartSession(true);

/** Set Include Path * */
set_include_path(get_include_path() . PATH_SEPARATOR . "./includes");
/** EOF Include Path * */
//Set Document Root
define('ROOT', dirname(__FILE__));

//Define Includes Path for further Use
define('INCLUDE_PATH', ROOT . "/includes");

//Include Main Libraries
require(INCLUDE_PATH . '/config.php');

//include INCLUDE_PATH . "/phpmyprofiler.php";

require(INCLUDE_PATH . '/lib.php');
require(INCLUDE_PATH . '/lang/lang-en.php');
require(INCLUDE_PATH . '/functions.php');
require(ROOT . '/finascop_config/config.php');
//require_once(ROOT . '/finascop_config/lib.php');
//require_once(EXTERNAL_LIBRARY_PATH);

include(ROOT . '/class.smtpSend.php');
//Create DB Object
$db = new sqlDb(DSN);

//$profileLog = new phpMyProfiler(false,false,false,ROOT . CACHE_PATH);
//$profileLog->setLink($db->link);

$supportdb = new sqlDb(SUPPORTDSN);
//$sqlservdb = new SqlSrvDB(SQLSERVDSN);

//Initial OP variable. $op controls, which operation of the
//requested module needs to be executed
$op = false;
//Initialize $module, which module needs to be executed
$module = 'ui';

//Import GET/POST variables used by auth and routing (extract removed for security)
$loginUsername = isset($_POST['loginUsername']) ? $_POST['loginUsername'] : '';
$loginPassword = isset($_POST['loginPassword']) ? $_POST['loginPassword'] : '';
$rememberMe = isset($_POST['rememberMe']) ? $_POST['rememberMe'] : 0;
$rememberLogin = false;

//Set Requested Module, to Execute that requested on
if (isset($_GET['module']))
    $module = $_GET['module'];
if (isset($_POST['module']))
    $module = $_POST['module'];
//BIRT Session activity and Logging Actions
//Activity::Log(compact($_POST, $_GET, $_FILES, $_SESSION), $module);

//Set Requested Operation, to Execute that requested on
if (isset($_GET['op']))
    $op = $_GET['op'];
if (isset($_POST['op']))
    $op = $_POST['op'];
$mf = false;
$ak = false;
if (isset($_GET['mf']))
    $mf = $_GET['mf'];
if (isset($_POST['mf']))
    $mf = $_POST['mf'];
if (isset($_GET['ak']))
    $ak = $_GET['ak'];
if (isset($_POST['ak']))
    $ak = $_POST['ak'];


//print_r($_REQUEST);
//booster

require_once(INCLUDE_PATH . "/finascop_common_functions.php");

if (isset($_SESSION['admin'])) {
    if ($_SESSION['admin']->typId == 3 || $_SESSION['admin']->typId == 4) {
        $db->query('set @entryBr=' . $_SESSION['admin']->typdetsid);
    } else {
        $db->query('set @entryBr=1');
    }
    $db->query('set @gAppuserID="' . $_SESSION['admin']->UserId . '"');
    $db->query('set @ComputerName="' . Activity::getIpAddr() . '"');

	if ($module != 'auth' && $module != 'ui' && $op != 'logout' && $module != 'access'&& $module != 'vlsl_upload'&& $module != 'mypha_prescription'&& $op != 'mapPrescriptionMedicine'&& $module != 'vlsl_upload'&& $module != 'finascop_stock_upload'&& $op != 'buildStockUploadCsv') {
		if (isset($_GET['apikey']))
			$api_key = $_GET['apikey'];
		if (isset($_GET['tstamp']))
			$tstamp = $_GET['tstamp'];	
		if (isset($_POST['apikey']))
			$api_key = $_POST['apikey'];
		if (isset($_POST['tstamp']))
			$tstamp = $_POST['tstamp'];			
        if (empty($api_key) || empty($tstamp)) {
            echo "{'invalid_api1':true}";
            exit;
        } else if (!empty($api_key)) {
            if (IsValidapikeyLocal($api_key) == false) {
                echo "{'invalid_api2':true}";
                session_destroy();
                setcookie("remember_uidnr_admin", false, (time() - 1));
                exit;
            }
        }
    }


    if (!user_access($module, $op)) {
        access_denied($module, $op);
    } else {
        //Include/Iniate the Module files if the user is Authenticated
        include(ROOT . "/init_modules.php");
    }
} else {
    //If Session Expired, then redirect the screen.
    if ($module != 'auth' && $module != 'ui') {
        echo "{session_expired:true}";
        exit;
    }
    $module = 'auth';
    //Include/Iniate the Module files since this module not required authentication
    include(ROOT . "/init_modules.php");
}

