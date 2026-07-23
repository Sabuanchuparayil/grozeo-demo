<?php
ini_set('memory_limit', -1);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
   exit(0);
}
require('../includes/config.php');
require('../finascop_config/config.php');
require('../finascop_config/lib.php');
require('../includes/lib.php');
require('../includes/finascop_common_functions.php');
//require('../classes/softsms.php');
require('../classes/TextLocal.php');
require('../classes/DeliveryFinascop.php');
require('../classes/DeliveryConfirmation.php');
require('../classes/PayOnDelivery.php');
require(QUGEO_API_ROOT . '/qugeoapi-autoloader.php');
//require(EXTERNAL_LIBRARY_PATH);
require(EXTERNAL_LIBRARY_PATH);
require('../includes/CloudFcmNotification.php');
//require('../includes/txtlocalsms.php');
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
