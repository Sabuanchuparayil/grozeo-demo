<?php

/*define('SITE_PATH', dirname(__FILE__));
define('INCLUDE_PATH', SITE_PATH . "/includes");
define('ADMIN_PATH', SITE_PATH);
define('INCLUDE_FC', SITE_PATH . "/finascop_config");

include(INCLUDE_PATH . "/config.php");
include(INCLUDE_PATH . "/lib.php");
include(INCLUDE_PATH . "/lang/lang-en.php");
//include(INCLUDE_PATH . "/functions.php");
include(INCLUDE_FC . "/lib.php");*/

class FindRETCSid{

public function setFindRETCSid(){
$db = new sqlDb(DSN);
$db->query("SET NAMES utf8");
$db->query("SET CHARSET utf8");

//ConfigManager::setConfiguration($db);
$maxRetailDistance = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'MAX_RETAIL_DISTANCE'");
$customers = $db->getMultipleData("SELECT cust_id,cust_branch_id,cust_customer_id  FROM retaline_customer WHERE cust_issetretcs = 0", true);
$initialkm = 15;
$defaultcentralstore = $db->getItemFromDB("SELECT ret.br_id AS br_id FROM finascop_branch ret  INNER JOIN  finascop_branch dist ON ret.br_cpd = dist.br_id  INNER JOIN  finascop_branch cs ON dist.br_cpd = cs.br_id WHERE  ret.br_csdefault = 1 AND dist.br_csdefault = 1 AND  cs.br_csdefault = 1 LIMIT 1");
foreach ($customers as $customer) {
	
	$pinDistrict = 0;
	$centralStore = 0;
	$defaultDistributer =0;
	 $defaultRetailer = 0;
	 
    $custAddress = $db->getFromDB("SELECT deli_id,deli_delivery_pin,deli_latitude,deli_longitude from retaline_customer_delivery_info WHERE deli_status = 'active' AND deli_is_primary = 1 AND deli_customer_id = {$customer['cust_id']}", true);
    if ($custAddress['deli_delivery_pin'] > 0) {
        $pinDistrict = $db->getItemFromDB("SELECT dst_id from retaline_pincode where pincode = {$custAddress['deli_delivery_pin']}");
    }
	

	 
    if ($pinDistrict > 0) {
        $centralStore = $db->getItemFromDB("SELECT branch_id FROM retaline_pincode WHERE dst_id = {$pinDistrict}");
		
        if ($centralStore > 0)
            $defaultDistributer = $db->getItemFromDB("SELECT br_ID FROM finascop_branch WHERE br_cpd = {$centralStore} AND br_csdefault = 1 AND br_PyramidLevel = 3");
        if ($defaultDistributer > 0)
            $defaultRetailer = $db->getItemFromDB("SELECT br_ID FROM finascop_branch WHERE br_cpd = {$defaultDistributer} AND br_csdefault = 1 AND br_PyramidLevel = 4");
        //select default retailer of the default distributer.
    } 

    $locs = new cgoGeoUtilities();
    if (($custAddress['deli_longitude'] > 0) && ($custAddress['deli_latitude'] > 0) || ($custAddress['deli_delivery_pin'] > 0)) {
        $inckm = $initialkm;
		
        do {
            $locdata = $locs->getNearestAerialRetailers($custAddress['deli_longitude'], $custAddress['deli_latitude'], $inckm);
            $inckm = $inckm + 5;
        } while (($locdata[0]['distance'] == NULL || $locdata[0]['distance'] == '') && ($inckm <= intval($maxRetailDistance)));
        $db->query('begin');
		$retloc = array();
		$csloc = array();
        if ($locdata[0]['distance'] != NULL || $locdata[0]['distance'] != '') {
            $retloc['deli_retailer'] = $locdata[0]['br_ID'];
            $retloc['deli_retailerdistance'] = $locdata[0]['distance'];
            //$retloc['deli_branch_id'] = $defaultRetailer; -- no need laravel is doing it
            $retloc['deli_centralstore'] = $defaultcentralstore;
            $retloc['deli_updated_at'] = date("Y-m-d H:i:s");
            $retloc = array_filter($retloc);
            $status = $db->perform('retaline_customer_delivery_info', $retloc, 'update', " deli_id = {$custAddress['deli_id']}");
            $status = $db->query("UPDATE retaline_customer SET cust_issetretcs = 1,cust_updated_at=now()  WHERE cust_id = {$customer['cust_id']}");
        } else {
            if ($defaultRetailer > 0) {
             //   $csloc['deli_branch_id'] = $defaultRetailer; -- no need laravel is doing it
                $csloc['deli_centralstore'] = $defaultcentralstore;
                $csloc['deli_retailer'] = 0;
            }else{
			//	$csloc['deli_branch_id'] = 0; -- no need laravel is doing it
                $csloc['deli_centralstore'] = $defaultcentralstore;
                $csloc['deli_retailer'] = 0;
			}
            //$retloc = array_filter($retloc);
            $csloc['deli_updated_at'] = date("Y-m-d H:i:s");
            $status = $db->perform('retaline_customer_delivery_info', $csloc, 'update', " deli_id = {$custAddress['deli_id']}");
            $status = $db->query("UPDATE retaline_customer SET cust_issetretcs = 1,cust_updated_at=now() WHERE cust_id = {$customer['cust_id']}");
        }
        echo $customer['cust_id'] . ',';
        $status = $db->query('commit');
    }

    //exit();
}
}	
}

