<?php
ini_set('memory_limit', -1);

/*if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
   exit(0);
}*/
define('ROOT', '../');
define('INCLUDE_PATH', '../includes');
require_once ('../includes/config.php');
require_once ('../includes/config.php');
require_once ('../finascop_config/config.php');
require_once ('../finascop_config/lib.php');
//require_once ('../includes/lib.php');
//require_once ('../includes/functions.php');
require_once ('../includes/finascop_accounts_Transactions.php');
require_once ('../includes/finascop_common_functions.php');
//require_once ('../classes/sqlDb.php'); 
//require_once ('../rpc/softsms.php');
require_once ('../partnerapi/partnerapi-autoloader.php');
require_once (EXTERNAL_LIBRARY_PATH);
include ('../finascop_libs/dompdf/autoload.inc.php');
// Requests from the same server don't have a HTTP_ORIGIN header
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

try {	
    $API = new  Controllers\RequestHandler($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
	echo $API->processAPI();
} catch (Exception $e) {
    echo json_encode(Array('error' => $e->getMessage()));
}
