<?php

//session_name("TMU");
session_start();
session_regenerate_id();

// $_SESSION['rand_code'] = $tmprandcode;
//Define the root path
define('ROOT', dirname(__FILE__));

//Define Includes Path for further Use
define('INCLUDE_PATH', "./includes");

//Include Main Libraries
include(INCLUDE_PATH . "/config.php");
//require_once(EXTERNAL_LIBRARY_PATH);
require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/finascop_config/config.php');
include(INCLUDE_PATH . "/lib.php");

//include(INCLUDE_PATH . "/functions.php");
//Create DB Object

$db = new sqlDb(DSN);
$s3upload = new cgoS3FileHandler();
//identify the url
define('WEB_URL', substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], basename(__FILE__)) - 1));

//Get Operation Todo
$TODO = $_GET['TODO'];

#===============================================================================
/* Load the Initial data into JS that needs to be populated
 * from DB or from SESSION. Each data will be saved as inidividual JS Variables
 * This will be executed only based specific Request/Condition
 */
if ($TODO == 'initdata') {
    $tostatus = $db->getMultipleData("SELECT fstos_status FROM finascop_stock_transfer_order_status");
    $statusnames = $db->getMultipleData("SELECT admin_description FROM retaline_customer_order_status where status_id > 2");
    $statuOptions = $db->getMultipleData("SELECT admin_description FROM retaline_customer_order_status ");
    header("Content-type: text/javascript; charset: UTF-8");
    header("Cache-Control: must-revalidate");
    $offset = 60 * 60;
    $ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
    header($ExpStr);

    echo 'var _SESSION 		= ' . json_encode($_SESSION['admin']) . ';';
    echo 'var ORDER_STATUS_VALUES = ' . json_encode($statusnames) . ';';
    echo 'var ORDER_STATUS_OPTIONS = ' . json_encode($statuOptions) . ';';
    echo 'var PCORDER_STATUS_OPTIONS = ' . json_encode($tostatus) . ';';
    unset($_SESSION['admin']->email_action);
    echo 'var _PHPSESSID 	= "' . session_id() . '";';
    $zoneId = (intval($_SESSION['admin']->typId) == 2) ? intval($_SESSION['admin']->typdetsid) : 0;
    echo 'var REP_ENGINE_PROXY_DASHBOARD_URL = "' . str_replace('#ZONEID#', $zoneId, str_replace('#PASSKEY#', urlencode(session_id()), REP_ENGINE_PROXY_DASHBOARD_URL)) . '";';
    echo 'var REP_ENGINE_PROXY_DASHBOARD_URL_TPL = "' . REP_ENGINE_PROXY_DASHBOARD_URL . '";';
    echo 'var REP_ENGINE_PROXY_OTHERREPORT_URL_TPL = "' . REP_ENGINE_PROXY_OTHERREPORT_URL_TPL . '";';
    echo 'var GMAP_LOCATION_ICON = "' . GMAP_LOCATION_ICON . '";';
    echo 'var GMAP_BRANCH_ICON = "' . GMAP_BRANCH_ICON . '";';
    echo 'var GMAP_PICKUP_ICON = "' . GMAP_PICKUP_ICON . '";';
    echo 'var GMAP_TRUCK_ICON = "' . GMAP_TRUCK_ICON . '";';
    echo 'var QC_VEHICLE_NEAR_PICKUP_CIRCLE_DIST = "' . QC_VEHICLE_NEAR_PICKUP_CIRCLE_DIST . '";';
    echo 'var GOOGLE_MAP_API_KEY = "' . GOOGLE_MAP_API_KEY . '";';
    echo 'var CAREGO_BOOK_APP_URL = "' . CAREGO_BOOK_APP_URL . '";';
    echo 'var API_ACTIONLOG_URL = "' . API_ACTIONLOG_URL . '";';
    echo 'var BETWEEN_DATES_TYPE_A = "' . BETWEEN_DATES_TYPE_A . '";';
    echo 'var IS_FINASCOP_PROJECT = "' . IS_FINASCOP_PROJECT . '";';
    echo "var FINASCOP_CURRENCY_FORMAT = '00,00,00,00,00,000.00';";
    echo "var MINIMUM_PASSWORD_STRENGTH = 60;";
    echo "var STATUS_COMBO_DATA = [['1','Active'],['0','Inactive']];\n";
    exit;
}
#===============================================================================

/* Set UserID From Session if thorugh Webserver
 * If the request is thorugh CLI (For caching files based on permission), index.php will set UserID
 */
if ($_SESSION['admin']->UserId != "") {
    $userID = $_SESSION['admin']->UserId;
}

/* $CACHE_JSFILE = $userID.".js";
  $CACHE_JSFILE_FSPATH	= ROOT.CACHE_PATH.'/'.$CACHE_JSFILE;
  $CACHE_JSFILE_WSPATH		= CACHE_PATH.$CACHE_JSFILE;
 */
$file_name = $userID . '.js';
$cache_file = ROOT . CACHE_PATH . '/' . $file_name;
$web_path = WEB_URL . CACHE_PATH . '/' . $file_name;

//=========================================
// #1: LOAD Cache file if applicable
//-----------------------------------------
// if(!eregi('sil.lab', $_SERVER['HTTP_HOST']) && !eregi('localhost', $_SERVER['HTTP_HOST']))
//INCLUDE Main Libraries (like SQL Connection, other main functions)
//include(INCLUDE_PATH . "/lib.php");
//Create DB Object
//$db = new sqlDb(DSN);
//ConfigManager::setConfiguration($db);
$JSCacheFileTime = $db->getItemFromDB('select JSCacheTime from ' . FINASCOP_DB . 'finascop_usr_master where UserId = ' . $userID);
//echo "File exists " . file_exists($cache_file) . " OP " . $op . " filemtime " . filemtime($cache_file) . " JSCacheTime " .  $JSCacheFileTime;
//exit;

if (file_exists($cache_file) && $op != 'generateUserJs' && filemtime($cache_file) == $JSCacheFileTime && $JSCacheFileTime != '') {
    header('Location: ' . $web_path . '?v=' . filemtime($cache_file), TRUE, 302);
    exit;
}
/* if(!empty($CACHE_JSFILE) && !DEBUG_MODE){
  //Set Cache File FS (File System) Paths


  if(file_exists($CACHE_JSFILE_FSPATH)){
  header('Location: '.$CACHE_JSFILE_WSPATH, TRUE, 302);
  exit;
  /*
  The HTTP status code changes the way browsers and robots handle redirects,
  so if you are using header(Location:) it's a good idea to set the status code at the same time.
  Browsers typically:
  re-request a 307 page every time,
  cache a 302 page for the session,
  and cache a 301 page for longer, or even indefinitely.
  Search engines typically transfer "page rank" to the new location for 301 redirects,
  but not for 302, 303 or 307. If the status code is not specified, header('Location:') defaults to 302.
 * /
  }
  } */
//-----------------------------------------
// #1: End: LOAD Cache file
//=========================================
// Turn on output buffering
ob_start();
$jsContent = "";



/* INCLUDE JavaScript Minifier Library
 * JSMin is a filter which removes comments and unnecessary whitespace from JavaScript  files.
 * It typically reduces filesize by half, resulting in faster downloads.
 */
include("./min/lib/JSMin.php");



/* include the FTPUPload Applet based on Permission */
/* if (user_access("FTPApplet", "render")) {
  @include(ROOT."/resources/ux/FTPApplet.js");
  } */

/* include the init */
include(MODULES_PATH . "/ui/js/init.js");
/* include the actionlog */
//include(MODULES_PATH."/actionLog/js/actionlog.js");
/* include the util JS Files */
include(MODULES_PATH . "/ui/js/util.js");
include(MODULES_PATH . "/ui/js/finascop_common.js");
include(INCLUDE_PATH . "/libjs/extfix.js");
include(INCLUDE_PATH . "/libjs/app.js");
//include(INCLUDE_PATH."/libjs/Applet.js");
include(INCLUDE_PATH . "/lang/lang-en.js");
/* includes external classes */
include(ROOT . "/resources/ux/RowExpander.js");
include(ROOT . "/resources/ux/Ext.ux.util.js");
include(ROOT . "/resources/ux/Ext.ux.grid.RowActions.js");
include(ROOT . "/resources/ux/Ext.ux.dd.GridReorderDropTarget.js");
/* includes external Plugin - for - Remote Field Validation  */
include(ROOT . "/resources/ux/Ext.ux.plugins.js");
include(ROOT . "/resources/ux/Ext.ux.form.LovCombo.js");
/* includes external plugin for gmap */
include(ROOT . "/resources/ux/Ext.ux.GMapPanel.js");
/* --- Include User Extions: Start --- */
include(ROOT . "/resources/ux/StatusBar.js");
include(ROOT . "/resources/ux/TabCloseMenu.js");
include(ROOT . "/resources/ux/Ext.ux.PasswordField.js");
//include(ROOT."/resources/ux/ux_VerticalTabPanel.js");
include(ROOT . "/resources/ux/Ext.ux.dd.GridReorderDropTarget.js");

include(ROOT . "/resources/ux/FileUploadField.js");
include(ROOT . "/resources/ux/ItemSelector.js");
include(ROOT . "/resources/ux/MultiSelect.js");


include(ROOT . "/resources/ux/xdatetime.js");

include(ROOT . "/resources/ux/MessageWindow.js");

include(ROOT . "/resources/ux/RowEditor.js");



include(ROOT . "/resources/ux/menu/EditableItem.js");
include(ROOT . "/resources/ux/menu/RangeMenu.js");
include(ROOT . "/resources/ux/menu/ListMenu.js");
include(ROOT . "/resources/ux/menu/TreeMenu.js");

include(ROOT . "/resources/ux/gridfilters/GridFilters.js");
include(ROOT . "/resources/ux/gridfilters/menu/RangeMenu.js");
include(ROOT . "/resources/ux/gridfilters/menu/ListMenu.js");
include(ROOT . "/resources/ux/gridfilters/filter/Filter.js");
include(ROOT . "/resources/ux/gridfilters/filter/StringFilter.js");
include(ROOT . "/resources/ux/gridfilters/filter/DateFilter.js");
include(ROOT . "/resources/ux/gridfilters/filter/ListFilter.js");
include(ROOT . "/resources/ux/gridfilters/filter/NumericFilter.js");
include(ROOT . "/resources/ux/gridfilters/filter/BooleanFilter.js");

include(ROOT . "/resources/ux/Ext.CellToolTip.js");

///include(ROOT."/resources/ux/Ext.ux.GridTotals.js");
include(ROOT . "/resources/ux/Ext.ux.GroupSummary.js");
include(ROOT . "/resources/ux/Ext.ux.SimpleTotals.js");
include(ROOT . "/js/dynamodb-marshaler.min.js");


include(ROOT . "/js/ux/treegrid/TreeGridSorter.js");
include(ROOT . "/js/ux/treegrid/TreeGridColumnResizer.js");
include(ROOT . "/js/ux/treegrid/TreeGridNodeUI.js");
include(ROOT . "/js/ux/treegrid/TreeGridLoader.js");
include(ROOT . "/js/ux/treegrid/TreeGridColumns.js");
include(ROOT . "/js/ux/treegrid/TreeGrid.js");

include(ROOT . "/resources/ux/calendar-debug.js.js");

/* --- Include User Extions: End --- */

//include finascop extjs overrides.
//include(ROOT . '/finascop_config/finascop_overrides.js');
$jsContent = ob_get_clean();




/* Obtain the permitted modules */
$permitted = getPermittedModules();
//print_r($permitted);
foreach ($permitted as $permJs) {
    ob_start();
    /* `ui` and `auth` modules should be eliminated */

    if ($permJs != 'ui' && $permJs != 'auth') {
        //include (MODULES_PATH."/".$permJs.'/lang/en_us.js');
        include (MODULES_PATH . "/" . $permJs . '/js/' . $permJs . '.js');
    }
    $js_code = ob_get_clean();
    //$js_code = preg_replace("/(,( )?)+/",",",$js_code);
    $js_code = preg_replace("/((\/\*\*\/)?,( )?(\/\*\*\/)?)+/", ",", $js_code);
    $js_code = str_replace("[,", "[", $js_code);
    $js_code = str_replace(",]", "]", $js_code);

    //OUTPUT Module JS
    $jsContent .= $js_code;
}


ob_start();

/* include ui */
include(MODULES_PATH . "/ui/lang/en_us.js");

/* includes menu */
include(MODULES_PATH . "/ui/menu.php");

/* includes header */
include(MODULES_PATH . "/ui/js/header.js");

/* include ui */
include(MODULES_PATH . "/ui/js/ui.js");

/* include multiselect */
//include(MODULES_PATH."/mail-templates/js/DDView.js");
//include(MODULES_PATH."/mail-templates/js/ItemSelector.js");
//include(MODULES_PATH."/mail-templates/js/MultiSelect.js");
include(ROOT . "/js/ZeroClipboard.js");
include(MODULES_PATH . "/route/js/DDView.js");
//include(MODULES_PATH."/mail-templates/js/ItemSelector.js");
//include(MODULES_PATH."/route/js/MultiSelect.js");

include(ROOT . "/js/TimePicker/Ext.ux.Timefield.js");
include(ROOT . "/js/TimePicker/spinnerMod.js");

/* for event calendar */

include(ROOT . "/calendar/src/Ext.calendar.js");
include(ROOT . "/calendar/src/templates/DayBodyTemplate.js");
include(ROOT . "/calendar/src/templates/DayBodyTemplate.js");
include(ROOT . "/calendar/src/templates/DayViewTemplate.js");
include(ROOT . "/calendar/src/templates/BoxLayoutTemplate.js");
include(ROOT . "/calendar/src/templates/MonthViewTemplate.js");
include(ROOT . "/calendar/src/dd/CalendarScrollManager.js");
include(ROOT . "/calendar/src/dd/StatusProxy.js");
include(ROOT . "/calendar/src/dd/CalendarDD.js");
include(ROOT . "/calendar/src/dd/DayViewDD.js");
include(ROOT . "/calendar/src/EventRecord.js");
include(ROOT . "/calendar/src/views/MonthDayDetailView.js");
include(ROOT . "/calendar/src/widgets/CalendarPicker.js");
include(ROOT . "/calendar/src/WeekEventRenderer.js");
include(ROOT . "/calendar/src/views/CalendarView.js");
include(ROOT . "/calendar/src/views/MonthView.js");
include(ROOT . "/calendar/src/views/DayHeaderView.js");
include(ROOT . "/calendar/src/views/DayBodyView.js");
include(ROOT . "/calendar/src/views/DayView.js");
include(ROOT . "/calendar/src/views/WeekView.js");
include(ROOT . "/calendar/src/widgets/DateRangeField.js");
include(ROOT . "/calendar/src/widgets/ReminderField.js");
include(ROOT . "/calendar/src/EventEditForm.js");
include(ROOT . "/calendar/src/EventEditWindow.js");
include(ROOT . "/calendar/src/CalendarPanel.js");



/* buffering the page */
$jsContent .= ob_get_clean();

//if(!DEBUG_MODE)
// $jsContent = trim(JSMin::minify($jsContent));
//header("Content-type: text/javascript; charset: UTF-8");
//file_put_contents($CACHE_JSFILE_FSPATH, $jsContent, LOCK_EX);
// echo $jsContent;
file_put_contents($cache_file, $jsContent, LOCK_EX);
$JSCacheFileTime = time();
touch($cache_file, $JSCacheFileTime);
//$JSCacheFileTime = filemtime($cache_file);
$_SESSION['admin']->JSCacheTime = $JSCacheFileTime;
$db->query("update " . FINASCOP_DB . "finascop_usr_master set JSCacheTime = '" . $JSCacheFileTime . "' where UserId = " . $userID . ";");
header('Location: ' . $web_path . '?v=' . $JSCacheFileTime, TRUE, 302);

exit();
?>

