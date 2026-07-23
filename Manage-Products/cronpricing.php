<?php

define('SITE_PATH', dirname(__FILE__));
define('INCLUDE_PATH', SITE_PATH . "/includes");
define('ADMIN_PATH', SITE_PATH);
define('INCLUDE_FC', SITE_PATH . "/finascop_config");

include(INCLUDE_PATH . "/config.php");
include(INCLUDE_PATH . "/lib.php");
include(INCLUDE_PATH . "/lang/lang-en.php");
//include(INCLUDE_PATH . "/functions.php");
include(INCLUDE_FC . "/lib.php");

$db = new sqlDb(DSN);
$db->query("SET NAMES utf8");
$db->query("SET CHARSET utf8");
//echo '$parentBarcodes';
$parentBarcodes = $db->getMultipleData("SELECT * FROM finascop_stock_item_inventorydetails WHERE  stiid_parent_barcode = 0", true);
echo 'statt'.time().'/n';
$i = 1;
//print_r($parentBarcodes);
foreach ($parentBarcodes as $parentBarcode) {
    //echo '$childBarcodes';
    echo '$i = '.$i.'/n';
    $childBarcodes = $db->getMultipleData("SELECT * FROM finascop_stock_item_inventorydetails WHERE  stiid_parent_barcode = {$parentBarcode['stiid_barcode']}", true);
    //print_r($childBarcodes);
    //if (count($childBarcodes) > 0) {
        foreach ($childBarcodes as $childBarcode) {
            $fsiiddatatl['bmd_percent'] = $parentBarcode['bmd_percent'];
            $fsiiddatatl['bmd_company'] = $parentBarcode['bmd_company'];
            $fsiiddatatl['bmd_hub'] = $parentBarcode['bmd_hub'];
            $fsiiddatatl['bmd_incentive'] = $parentBarcode['bmd_incentive'];
            $fsiiddatatl['bmd_technology'] = $parentBarcode['bmd_technology'];
            $fsiiddatatl['bmd_customer'] = $parentBarcode['bmd_customer'];
            $fsiiddatatl['bmd_percentorg'] = $parentBarcode['bmd_percentorg'];
            $fsiiddatatl['bmd_companyorg'] = $parentBarcode['bmd_companyorg'];
            $fsiiddatatl['bmd_huborg'] = $parentBarcode['bmd_huborg'];
            $fsiiddatatl['bmd_incentiveorg'] = $parentBarcode['bmd_incentiveorg'];
            $fsiiddatatl['bmd_technologyorg'] = $parentBarcode['bmd_technologyorg'];
            $fsiiddatatl['bmd_customerorg'] = $parentBarcode['bmd_customerorg'];
            $fsiiddatatl['bmdd_id'] = $parentBarcode['bmdd_id'];
            $fsiiddatatl['stiid_itemSmallStockUnit'] = $parentBarcode['stiid_itemSmallStockUnit'];
            $fsiiddatatl['stiid_leastSKUmrp'] = $parentBarcode['stiid_leastSKUmrp'];
            $fsiiddatatl['stiid_leastSKUepr'] = $parentBarcode['stiid_leastSKUepr'];
            $fsiiddatatl['bmd_cs'] = $parentBarcode['bmd_cs'];
            $fsiiddatatl['bmd_distributor'] = $parentBarcode['bmd_distributor'];
            $fsiiddatatl['bmd_retailor'] = $parentBarcode['bmd_retailor'];
            $fsiiddatatl['bmd_driver'] = $parentBarcode['bmd_driver'];
            $fsiiddatatl['bmd_courier'] = $parentBarcode['bmd_courier'];
            $fsiiddatatl['stiid_companyMargin'] = $parentBarcode['stiid_companyMargin'];
            $fsiiddatatl['stiid_incentiveMargin'] = $parentBarcode['stiid_incentiveMargin'];
            $fsiiddatatl['stiid_csMargin'] = $parentBarcode['stiid_csMargin'];
            $fsiiddatatl['stiid_distributorMargin'] = $parentBarcode['stiid_distributorMargin'];
            $fsiiddatatl['stiid_retailorMargin'] = $parentBarcode['stiid_retailorMargin'];
            $fsiiddatatl['stiid_driverMargin'] = $parentBarcode['stiid_driverMargin'];
            $fsiiddatatl['stiid_courierMargin'] = $parentBarcode['stiid_courierMargin'];
            $fsiiddatatl['stiid_customerRateHmDel'] = $parentBarcode['stiid_customerRateHmDel'];
            $fsiiddatatl['stiid_customerRateCouDel'] = $parentBarcode['stiid_customerRateCouDel'];
            $fsiiddatatl['stiid_customerRatePikup'] = $parentBarcode['stiid_customerRatePikup'];
            $fsiiddatatl['stiid_customerProfitHmDel'] = $parentBarcode['stiid_customerProfitHmDel'];
            $fsiiddatatl['stiid_customerProfitCouDel'] = $parentBarcode['stiid_customerProfitCouDel'];
            $fsiiddatatl['stiid_customerProfitPikup'] = $parentBarcode['stiid_customerProfitPikup'];
            $fsiiddatatl['stiid_hasGift'] = $parentBarcode['stiid_hasGift'];

            $db->query('begin');
            $status = $db->perform('finascop_stock_item_inventorydetails', $fsiiddatatl,'update'," stiid_id = {$childBarcode['stiid_id']}");
            $status = $db->query('commit');
            $i++;
        }
    //}
}
echo 'finish'.time();

