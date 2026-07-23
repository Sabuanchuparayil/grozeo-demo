<?php
define('ROOT', dirname(dirname(__FILE__)));
define('INCLUDE_PATH', ROOT . "/includes");
define('AWS_ROOT', '/home/system/awsapi');
require(ROOT . '/includes/config.php');
require(INCLUDE_PATH . '/functions.php');
require(ROOT . '/includes/lib.php');
require('TextLocal.php');
require(AWS_ROOT . '/aws-autoloader.php');



$url = 'https://gs1datakart.org/upload/product_image/8901396/8901396354604/8901396354604_b.jpg';
$newfilename = '/tmp/importimages/testname1234.'.pathinfo(basename($url), PATHINFO_EXTENSION);
$target = $_SERVER["DOCUMENT_ROOT"].$newfilename;
// echo $target;
var_dump(copy($url, $target));