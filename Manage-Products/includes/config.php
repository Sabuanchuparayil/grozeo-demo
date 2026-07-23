<?php
/**
 * Local development configuration for Manage-Products.
 * Generated for localhost setup — adjust credentials/paths as needed.
 */

define('DSN', getenv('MANAGE_PRODUCTS_DSN') ?: 'mysql://user:password@127.0.0.1:3306/mypharmacydev');
define('PARENTDSN', getenv('MANAGE_PRODUCTS_PARENT_DSN') ?: getenv('MANAGE_PRODUCTS_DSN') ?: 'mysql://user:password@127.0.0.1:3306/mypharmacydev');

define('DEBUG_MODE', true);
define('CACHE_PATH', '/cache');
define('MODULES_PATH', dirname(__DIR__) . '/modules');
define('DEFAULT_OPERATION', '');
define('PERM_SEPERATOR', ';');

define('SITE_TITLE', 'Grozeo Manage Products');
define('ONLOAD_THROBBER_TEXT', 'Loading...');
define('REPORTSERVER', 'http://127.0.0.1:8080/');

define('DEFAULT_FROM_NAME', 'Grozeo Local');
define('DEFAULT_FROM_ADDRESS', 'noreply@localhost');

define('GOOGLE_MAP_API_KEY', '');
define('REP_ENGINE_PROXY_DASHBOARD_URL', 'http://127.0.0.1:8080/');
define('REP_ENGINE_PROXY_OTHERREPORT_URL_TPL', 'http://127.0.0.1:8080/');
define('GMAP_LOCATION_ICON', '/resources/images/map/location.png');
define('GMAP_BRANCH_ICON', '/resources/images/map/branch.png');
define('GMAP_PICKUP_ICON', '/resources/images/map/pickup.png');
define('GMAP_TRUCK_ICON', '/resources/images/map/truck.png');
define('QC_VEHICLE_NEAR_PICKUP_CIRCLE_DIST', '500');
define('CAREGO_BOOK_APP_URL', 'http://127.0.0.1:8080/');
define('API_ACTIONLOG_URL', 'http://127.0.0.1:8000/');
define('BETWEEN_DATES_TYPE_A', '1');
define('LOGIN_KEEPALIVE_TIMEOUT', 3600);

define('SYMBOL', '₹');
define('CURRENCY', 'INR');
define('DEF_LATITUDE', '28.5935552');
define('DEF_LONGITUDE', '77.2961699');

define('AWSACCESSKEY', getenv('AWS_ACCESS_KEY') ?: 'local-dev');
define('AWSPASSWORDKEY', getenv('AWS_SECRET_KEY') ?: 'local-dev');
define('AWSBUCKETNAME', getenv('AWS_BUCKET_NAME') ?: 'local-dev-bucket');
define('AWSS3ASSETUPLOADACCESSID', getenv('AWS_S3_UPLOAD_ACCESS_ID') ?: 'local-dev');
define('AWSS3ASSETUPLOADSECRETKEY', getenv('AWS_S3_UPLOAD_SECRET_KEY') ?: 'local-dev');
define('AWSS3ASSETUPLOADREGION', getenv('AWS_S3_UPLOAD_REGION') ?: 'ap-south-1');

define('AWS_SES_SMTP_USER', getenv('AWS_SES_SMTP_USER') ?: '');
define('AWS_SES_SMTP_PASSWORD', getenv('AWS_SES_SMTP_PASSWORD') ?: '');
define('AWS_SES_SMTP_HOST', getenv('AWS_SES_SMTP_HOST') ?: 'email-smtp.us-east-1.amazonaws.com');
define('AWS_SES_SMTP_PORT', getenv('AWS_SES_SMTP_PORT') ?: 587);
define('SCRAPERAPI_KEY', getenv('SCRAPERAPI_KEY') ?: '');

define('EXTERNAL_LIBRARY_PATH', dirname(__DIR__) . '/includes/local_external_stub.php');
define('SYSTEM_LOCK', sys_get_temp_dir() . '/grozeo_manage_products.lock');
