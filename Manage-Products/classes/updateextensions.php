<?php

define('ROOT', dirname(dirname(__FILE__)));
define('INCLUDE_PATH', ROOT . "/includes");
require(ROOT . '/includes/config.php');
define('AWS_ROOT', '/home/system/awsapi');
//include(INCLUDE_PATH . "/config.php");
require(INCLUDE_PATH . '/functions.php');
require(ROOT . '/includes/lib.php');
require('TextLocal.php');
require(AWS_ROOT . '/aws-autoloader.php');



$db = new sqlDb(DSN);


$qry = "select * from tmpimgext ";
//echo $qry;
$tmpsdata = $db->getMultipleData($qry, true);
print_r(count($tmpsdata));
if (count($tmpsdata) > 0) {
    foreach ($tmpsdata as $tmpdata) {
        $db->query('begin');
        $imagepath = explode('.',$tmpdata['imagepath']);
       // print_r($imagepath);
        $imageSno = $db->getItemFromDB("SELECT sno FROM tmp_master_images WHERE Images = '{$imagepath[0]}'");
        if($imageSno > 0){
            $imageName = $imagepath[0].'.'.$imagepath[1];
			//echo 'imageName'.$imageName;
            $db->query("UPDATE tmp_master_images SET Images = '{$imageName}' WHERE Images = '{$imagepath[0]}'");
            
            $db->query("UPDATE tmpimgext SET isupdated = 1 WHERE imagepath = '{$tmpdata['imagepath']}'");
        }
        
        $db->query('commit');
    }
} else {
    echo 'No data to import.';
}
?>