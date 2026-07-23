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


$qry = "select * from tmp_master_pro where masterid > 0 limit 11396,3000";
//echo $qry;
$tmpsdata = $db->getMultipleData($qry, true);
print_r(count($tmpsdata));
if (count($tmpsdata) > 0) {
    foreach ($tmpsdata as $tmpdata) {

        $db->query('begin');
        $unitid = $db->getItemFromDB("SELECT unit_id FROM mypha_unit WHERE unit_name = '{$tmpdata['unit']}'");
        $iteDetails = $db->getFromDB("SELECT stit_brand_name,stit_itemName,stit_product_variant,stit_quantity from finascop_stock_itemmaster where stit_ID = {$tmpdata['masterid']}");
        $SKU = trim($iteDetails['stit_brand_name']) . " " . trim($iteDetails['stit_itemName']) . " " . trim($iteDetails['stit_product_variant']) . " " . trim($iteDetails['stit_quantity']);

        $least_package_type_id = $db->getItemFromDB("SELECT package_type_id FROM mypha_productpackage_type WHERE package_type_name = '{$tmpdata['sku']}'");

        
        
        $datafsi = array(
            "stit_ingredients" => $tmpdata['ingredients'],
            "stit_preparation_use" => $tmpdata['preparation_use'],
            "stit_allergens" => $tmpdata['allergens'],
            "stit_nutritionlabel" => $tmpdata['nutritionlabel'],
            "least_package_type_id" => $least_package_type_id,
            "least_package_type_name" => $tmpdata['sku'],
            "stit_package_type_id" => $least_package_type_id,
            "stit_package_type_namme" => $tmpdata['sku'],
            "cosb_package_type_id" => $least_package_type_id,
            "cosb_package_type_name" => $tmpdata['sku'],
            "ccsb_package_type_id" => $least_package_type_id,
            "ccsb_package_type_name" => $tmpdata['sku'],
            "dsb_package_type_id" => $least_package_type_id,
            "dsb_package_type_name" => $tmpdata['sku'],
            "csb_package_type_id" => $least_package_type_id,
            "csb_package_type_name" => $tmpdata['sku'],
            "stdpckl11_package_type_id" => $least_package_type_id,
            "stdpckl12_package_type_id" => $least_package_type_id,
            "stdpckl1_nos" => 1,
            "stit_qty" => $tmpdata['stit_qty'],
            "stit_unit" => $unitid,
            "stit_safety_warning" => $tmpdata['safety'],
            "stit_storage_instruction" => $tmpdata['storage'],
			"stit_long_description" => $tmpdata['long_description']
        );


        

        $status = $db->perform("finascop_stock_itemmaster", $datafsi,'update'," stit_ID = {$tmpdata['masterid']}");
        /*$SKUUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  WHERE stit_SKU = '{$SKU}'  ");
        if ($SKUUnique > 0) {
            echo "This SKU already existing. " . "\t" . $tmpdata['sno'] . "\t" . $SKU . "\r\n";
            continue;
        }
        $status = $db->query("UPDATE finascop_stock_itemmaster SET stit_SKU = '{$SKU}' WHERE stit_ID = {$tmpdata['masterid']}");
        */
        $db->query('commit');
    }
} else {
    echo 'No data to import.';
}
?>