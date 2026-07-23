<?php
/**
 * Local development configuration for Grozeo-Bizadmin.
 */

$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: 'mypharmacydev';
$dbUser = getenv('DB_USERNAME') ?: 'grozeo';
$dbPass = getenv('DB_PASSWORD') ?: 'grozeo_local';

define('DSN', "mysql://{$dbUser}:{$dbPass}@{$dbHost}:{$dbPort}/{$dbName}");
define('SUPPORTDSN', "mysql://{$dbUser}:{$dbPass}@{$dbHost}:{$dbPort}/{$dbName}");

define('DEBUG_MODE', true);
define('CACHE_PATH', '/cache');
define('MODULES_PATH', dirname(__DIR__) . '/modules');
define('DEFAULT_OPERATION', '');
define('PERM_SEPERATOR', ';');

define('SITE_TITLE', 'Grozeo Bizadmin');
define('ONLOAD_THROBBER_TEXT', 'Loading...');
define('REPORTSERVER', 'http://127.0.0.1:8081/');

define('DEFAULT_FROM_NAME', 'Grozeo Local');
define('DEFAULT_FROM_ADDRESS', 'noreply@localhost');

define('GOOGLE_MAP_API_KEY', '');
define('REP_ENGINE_PROXY_DASHBOARD_URL', 'http://127.0.0.1:8081/');
define('REP_ENGINE_PROXY_OTHERREPORT_URL_TPL', 'http://127.0.0.1:8081/');
define('GMAP_LOCATION_ICON', '/resources/images/map/location.png');
define('GMAP_BRANCH_ICON', '/resources/images/map/branch.png');
define('GMAP_PICKUP_ICON', '/resources/images/map/pickup.png');
define('GMAP_TRUCK_ICON', '/resources/images/map/truck.png');
define('QC_VEHICLE_NEAR_PICKUP_CIRCLE_DIST', '500');
define('CAREGO_BOOK_APP_URL', 'http://127.0.0.1:8081/');
define('API_ACTIONLOG_URL', 'http://127.0.0.1:8000/');
define('BETWEEN_DATES_TYPE_A', '1');
define('LOGIN_KEEPALIVE_TIMEOUT', 3600);

define('SYMBOL', '₹');
define('CURRENCY', 'INR');
define('DEF_LATITUDE', '28.5935552');
define('DEF_LONGITUDE', '77.2961699');

define('AWSACCESSKEY', getenv('AWS_ACCESS_KEY_ID') ?: '');
define('AWSPASSWORDKEY', getenv('AWS_SECRET_ACCESS_KEY') ?: '');
define('AWSBUCKETNAME', getenv('AWS_BUCKET') ?: '');
define('AWSS3ASSETUPLOADACCESSID', getenv('AWS_ACCESS_KEY_ID') ?: '');
define('AWSS3ASSETUPLOADSECRETKEY', getenv('AWS_SECRET_ACCESS_KEY') ?: '');
define('AWSS3ASSETUPLOADREGION', getenv('AWS_DEFAULT_REGION') ?: 'ap-south-1');

define('EXTERNAL_LIBRARY_PATH', dirname(__DIR__) . '/includes/local_external_stub.php');
define('SYSTEM_LOCK', sys_get_temp_dir() . '/grozeo_bizadmin.lock');
