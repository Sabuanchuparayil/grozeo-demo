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

function updateUniqueItemTable($uid, $uniData)
{
    global $db;
    $uniData['fsi_category_id'] = addslashes($uniData['fsi_category_id']);
    $chkUnqExiste = $db->getFromDB("SELECT fsi_uid,fsi_count FROM " . FINASCOP_DB . "finascop_stock_uniqueitem WHERE fsi_item_id = {$uniData['fsi_item_id']} "
        . "  AND fsi_brand_id = {$uniData['fsi_brand_id']} AND fsi_category_id = {$uniData['fsi_category_id']} AND fsi_variant = '{$uniData['fsi_variant']}'", true);
    $uidCount = $db->getItemFromDB("SELECT fsi_count FROM " . FINASCOP_DB . "finascop_stock_uniqueitem WHERE fsi_uid = {$uid}");

    if ($uid == 0) {

        if (intval($chkUnqExiste['fsi_uid']) > 0) {
            $fsuidata['fsi_count'] = intval($chkUnqExiste['fsi_count']) + 1;
            $status = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $fsuidata, 'update', " fsi_uid = {$chkUnqExiste['fsi_uid']}");
            $stit_fsiuid['fsi_uid'] = $chkUnqExiste['fsi_uid'];
        } else {

            $fsuidata = $uniData;
            $fsuidata['fsi_count'] = 1;
            $ustatus = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $fsuidata);
            $stit_fsiuid['fsi_uid'] = $db->insert_id();
            $stit_fsiuid['status'] = 'NEW';
        }
    } else {
        if (intval($chkUnqExiste['fsi_uid']) > 0) {
            if (intval($chkUnqExiste['fsi_uid']) != $uid) {
                if (intval($uidCount) > 0) {
                    $fsuidata['fsi_count'] = intval($uidCount) - 1;
                } else {
                    $fsuidata['fsi_count'] = 0;
                }
                $status = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $fsuidata, 'update', " fsi_uid = {$uid}");
                $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM finascop_stock_itemmaster WHERE stit_fsiuid = {$uid}");
                $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $uuit, 'update', " fsi_uid = {$uid}");


                $updatCou['fsi_count'] = intval($chkUnqExiste['fsi_count']) + 1;
                $status = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $updatCou, 'update', " fsi_uid = {$chkUnqExiste['fsi_uid']}");
                $stit_fsiuid['fsi_uid'] = $chkUnqExiste['fsi_uid'];
            } else {
                $stit_fsiuid['fsi_uid'] = $uid;
            }
        } else {
            if (intval($uidCount) > 0) {
                $fsdata['fsi_count'] = intval($uidCount) - 1;
            } else {
                $fsdata['fsi_count'] = 0;
            }
            $status = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $fsdata, 'update', " fsi_uid = {$uid}");
            $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM finascop_stock_itemmaster WHERE stit_fsiuid = {$uid}");
            $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $uuit, 'update', " fsi_uid = {$uid}");

            $fsuidata = $uniData;
            $fsuidata['fsi_count'] = 1;
            $ustatus = $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $fsuidata);
            $stit_fsiuid['fsi_uid'] = $db->insert_id();
            $stit_fsiuid['status'] = 'NEW';
        }
    }
    return $stit_fsiuid;
}

$db = new sqlDb(DSN);


$qry = "select * from tmp_master_pro where masterid = 0 ";
//echo $qry;
$tmpsdata = $db->getMultipleData($qry, true);
print_r(count($tmpsdata));
if (count($tmpsdata) > 0) {
    foreach ($tmpsdata as $tmpdata) {

        $db->query('begin');
        $tmpdata['stit_itemBarcode'] = str_replace("'", "", trim($tmpdata['stit_itemBarcode']));
        $tmpdata['stit_itemName'] = str_replace("'", "", trim($tmpdata['stit_itemName']));
        $tmpdata['stit_MRP'] = str_replace("'", "", trim($tmpdata['stit_MRP']));
        $tmpdata['stit_product_variant'] = str_replace("'", "", trim($tmpdata['stit_product_variant']));
        $tmpdata['stit_quantity'] = str_replace("'", "", trim($tmpdata['stit_quantity']));
        $tmpdata['stit_HSNCode'] = str_replace("'", "", trim($tmpdata['stit_HSNCode']));
        $tmpdata['med_manufacturename'] = str_replace("'", "", trim($tmpdata['med_manufacturename']));
        $tmpdata['medcompos_name'] = str_replace("'", "", trim($tmpdata['medcompos_name']));
        $tmpdata['least_package_type_name'] = str_replace("'", "", trim($tmpdata['least_package_type_name']));
        $tmpdata['Packaging'] = str_replace("'", "", trim($tmpdata['Packaging']));
        $tmpdata['stit_medSchedule'] = str_replace("'", "", trim($tmpdata['stit_medSchedule']));
        $tmpdata['stit_medUsage'] = str_replace("'", "", trim($tmpdata['stit_medUsage']));
        $tmpdata['stit_medSalt'] = str_replace("'", "", trim($tmpdata['stit_medSalt']));
        $tmpdata['stit_medMecAction'] = str_replace("'", "", trim($tmpdata['stit_medMecAction']));
        $tmpdata['stit_medPharmacokinets'] = str_replace("'", "", trim($tmpdata['stit_medPharmacokinets']));
        $tmpdata['stit_medOnstAction'] = str_replace("'", "", trim($tmpdata['stit_medOnstAction']));
        $tmpdata['stit_medDurationAction'] = str_replace("'", "", trim($tmpdata['stit_medDurationAction']));
        $tmpdata['stit_medHalfLife'] = str_replace("'", "", trim($tmpdata['stit_medHalfLife']));
        $tmpdata['stit_medSideEffects'] = str_replace("'", "", trim($tmpdata['stit_medSideEffects']));
        $tmpdata['stit_medContraIndication'] = str_replace("'", "", trim($tmpdata['stit_medContraIndication']));
        $tmpdata['stit_medSpecialPrecaution'] = str_replace("'", "", trim($tmpdata['stit_medSpecialPrecaution']));
        $tmpdata['stit_medPregnancyRelated'] = str_replace("'", "", trim($tmpdata['stit_medPregnancyRelated']));
        $tmpdata['stit_medAlchoholInteraction'] = str_replace("'", "", trim($tmpdata['stit_medAlchoholInteraction']));
        $tmpdata['stit_medOldAge'] = str_replace("'", "", trim($tmpdata['stit_medOldAge']));
        $tmpdata['stit_medBreastFeeding'] = str_replace("'", "", trim($tmpdata['stit_medBreastFeeding']));
        $tmpdata['stit_medChildRelated'] = str_replace("'", "", trim($tmpdata['stit_medChildRelated']));
        $tmpdata['stit_medIntication'] = str_replace("'", "", trim($tmpdata['stit_medIntication']));
        $tmpdata['stit_medInteraction'] = str_replace("'", "", trim($tmpdata['stit_medInteraction']));
        $tmpdata['stit_medDosage'] = str_replace("'", "", trim($tmpdata['stit_medDosage']));
        $tmpdata['stit_medStorageReq'] = str_replace("'", "", trim($tmpdata['stit_medStorageReq']));
        $tmpdata['stit_medMissedDosage'] = str_replace("'", "", trim($tmpdata['stit_medMissedDosage']));
        $tmpdata['stit_medOverdoseEffect'] = str_replace("'", "", trim($tmpdata['stit_medOverdoseEffect']));
        $tmpdata['stit_medExpertAdvise'] = str_replace("'", "", trim($tmpdata['stit_medExpertAdvise']));
        $tmpdata['stit_medHowToUse'] = str_replace("'", "", trim($tmpdata['stit_medHowToUse']));
        $tmpdata['stit_medFaqs'] = str_replace("'", "", trim($tmpdata['stit_medFaqs']));






        switch ($tmpdata['foodtype']) {
            case 'Vegetarian':
                $foodtype = 1;
                break;
            case 'Non Vegetarian':
                $foodtype = 2;
                break;
            case 'Vegan':
                $foodtype = 3;
                break;
            default:
                $foodtype = 0;
                break;
        }


        $SKU = $tmpdata['item_name'] . " " . $tmpdata['stit_product_variant'] . " " . $tmpdata['stit_quantity'];
        //$SKU = $tmpdata['sku'];
        if (($tmpdata['tmp_id'] % 1000) == 0) {
            echo $tmpdata['sno'] . " -- " . $SKU . "\r\n";
        }
        $medicineMaster_id = $db->getItemFromDB("SELECT medicineMaster_id FROM mypha_medicineMaster WHERE medicineMaster_name = '{$tmpdata['stit_itemName']}'");
        if ($medicineMaster_id == 0) {
            echo "INVALID DATA " . "\t" . $tmpdata['sno'] . "\t" . $tmpdata['item_name'] . " ... \r\n";
            continue;
        } else {
            $medBrandDetails = $db->getFromDB("SELECT medicineMaster_id,medicineMaster_name,manufacture_name,medicine_type_name,composition_name,subCategory_name,medicine_composition,mc.subCategory_id as subcateg,"
                . "medicine_type_id,medicine_manufacture FROM mypha_medicineMaster "
                . "INNER JOIN  mypha_medicineType ON medicine_type_id = medicine_type "
                . "INNER JOIN  mypha_composition mc ON composition_id = medicine_composition "
                . "INNER JOIN  mypha_manufacture ON manufacture_id = medicine_manufacture "
                . "INNER JOIN mypha_subCategory ms on ms.subCategory_id = mc.subCategory_id WHERE  medicineMaster_id = {$medicineMaster_id} ", true);
        }

        $hsnid = $db->getItemFromDB("SELECT hsn_id FROM finascop_hsn WHERE hsn_code = '{$tmpdata['stit_HSNCode']}'");
        if ($hsnid == 0) {
            echo "INVALID DATA " . "\t" . $tmpdata['sno'] . "\t" . $SKU . ", Hsn code - " . $tmpdata['stit_HSNCode'] . " ... \r\n";
            continue;
        }
        if ($hsnid > 0) {
            $gstpercent = $db->getItemFromDB("SELECT gst_percent FROM finascop_hsn WHERE hsn_id = {$hsnid}");

            $hsncode = $db->getItemFromDB("SELECT hsn_code FROM finascop_hsn WHERE hsn_id = {$hsnid}");
        }
        $unitid = $db->getItemFromDB("SELECT unit_id FROM mypha_unit WHERE unit_name = '{$tmpdata['unit']}'");
        if ($unitid == 0) {
            echo "INVALID DATA " . "\t" . $tmpdata['sno'] . "\t" . $SKU . ", Unit - " . $tmpdata['unit'] . " ... \r\n";
            continue;
        }






        $featured = (strtolower($tmpdata['featured']) == 'yes' ? 1 : 0);
        $popular = (strtolower($tmpdata['popular']) == 'yes' ? 1 : 0);
        $isRRPApplicable = (strtolower($tmpdata['isRRPApplicable']) == 'yes' ? 1 : 0);
        $courierDelivery = (strtolower($tmpdata['courierDelivery']) == 'yes' ? 1 : 0);
        $directDelivery = (strtolower($tmpdata['directDelivery']) == 'yes' ? 1 : 0);
        //$stit_quantity = $tmpdata['stit_qty'] . $tmpdata['unit'];
        $datafsi = array(
            "stit_itemId" => $medicineMaster_id,
            "stit_SKU" => $SKU,
            "stit_itemERPId" => '',
            "stit_itemBarcode" => $tmpdata['stit_itemBarcode'],
            "stit_itemReturnTime" => $tmpdata['itemReturnTime'],
            "stit_HSNCode" => $hsncode,
            "stit_hsnId" => $hsnid,
            "stit_GST" => $gstpercent,
            "stit_Description" => $tmpdata['short_description'],
            "stit_product_variant" => $tmpdata['variant'],
            "pdt_package_type_id" => 2, //miss
            "dosform_id" => $medBrandDetails['medicine_type_id'],
            "medcompos_id" => $medBrandDetails['medicine_composition'],
            "dosform_name" => $medBrandDetails['medicine_type_name'],
            "medcompos_name" => $medBrandDetails['composition_name'],
            "med_drug_groupid" => $medBrandDetails['subcateg'],
            "med_drug_groupname" => $medBrandDetails['subCategory_name'],
            "med_manufactureid" => $medBrandDetails['medicine_manufacture'],
            "med_manufacturename" => $medBrandDetails['manufacture_name'],
            "product_category" => $medBrandDetails['subcateg'],
            "pdt_brand" => $medBrandDetails['medicine_composition'],
            "stit_category_name" => $medBrandDetails['subCategory_name'],
            "stit_brand_name" => $medBrandDetails['composition_name'],
            "featured" => $featured,
            "popular" => $popular,
            "stit_custInitiate" => 0, //miss
            "stit_long_description" => $tmpdata['long_description'], //miss
            "stit_quantity" => $tmpdata['stit_quantity'],
            "stit_qty" => $tmpdata['quantity'],
            "stit_itemName" => $tmpdata['item_name'],
            "stit_HSN_code" => $hsncode, //miss
            "stit_package_type_namme" => 'Packet', //miss
            "stitl1_optimumqty" => 1, //miss
            "stitl2_optimumqty" => 1, //miss
            "stitl3_optimumqty" => 1, //miss
            "stit11_minimumqty" => 1, //miss
            "stit12_minimumqty" => 1, //miss
            "stit13_minimumqty" => 1, //miss
            "stit11_maximumqty" => 1, //miss
            "stit12_maximumqty" => 1, //miss
            "stit13_maximumqty" => 1, //miss
            "stii_csb" => 5, //miss -- Central stock buffer
            "stii_csbretail" => 5,
            "stit_orgin_country" => $country, // stit_product_orgin
            "stit_foodtype" => $foodtype,
            "courierDelivery" => $courierDelivery,
            "directDelivery" => $directDelivery,
            "isRRPApplicable" => $isRRPApplicable,
            "stit_ingredients" => $tmpdata['ingredients'],
            "stit_preparation_use" => $tmpdata['preparation_use'],
            "stit_allergens" => $tmpdata['allergens'],
            "stit_nutritionlabel" => $tmpdata['nutritionlabel'],
            "least_package_type_id" => $least_package_type_id,
            "least_package_type_name" => $tmpdata['sku'],
            "stit_qty" => $tmpdata['stit_qty'],
            "stit_unit" => $unitid,
            "isMedicine" => 1,
        );

        $fsuidata['fsi_item_id'] = $datafsi['stit_itemId'];
        $fsuidata['fsi_item_name'] = $datafsi['stit_itemName'];
        $fsuidata['fsi_brand_id'] = $datafsi['pdt_brand'];
        $fsuidata['fsi_brand_name'] = $datafsi['stit_brand_name'];
        $fsuidata['fsi_category_id'] = $datafsi['product_category'];
        $fsuidata['fsi_categry_name'] = $datafsi['stit_category_name'];
        $fsuidata['fsi_variant'] = $datafsi['stit_product_variant'];
        $fsuidata['fsi_displaylabel'] = $datafsi['stit_displaylabel'];
        $fsuidata['isMedicine'] = 1;



        $itemName = $tmpdata['item_name'];





        $stit_fsiuid = updateUniqueItemTable(0, $fsuidata);
        $datafsi['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];

        $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  WHERE stit_itemName = '{$itemName}' AND  stit_product_variant = '{$tmpdata['variant']}' AND stit_quantity = '{$tmpdata['quantity']}' AND product_category = {$sub_category_id} AND pdt_brand = {$brand}");
        if ($IsItemNameUnique > 0) {
            echo "This Item already existing. " . "\t" . $tmpdata['sno'] . "\t" . $SKU . " Item Name - " . $itemName . ", Variant - " . $tmpdata['variant'] . ",  QTY - " . $tmpdata['quantity'] . ", product_category - " . $sub_category_id . ", Brand - " . $brand . " \r\n";
            continue;
        }
        $SKUUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  WHERE stit_SKU = '{$SKU}'  ");
        if ($SKUUnique > 0) {
            echo "This SKU already existing. " . "\t" . $tmpdata['sno'] . "\t" . $SKU . "\r\n";
            continue;
        }


        $datafsi = array_filter($datafsi, 'strlen');
        $status = $db->perform(FINASCOP_DB . "finascop_stock_itemmaster", $datafsi);
        $masterid = $db->insert_id();


        $dataFsims = array(
            "stit_Id" => $masterid,
            "stit_medSchedule" => $tmpdata['stit_medSchedule'],
            "stit_medUsage" => $tmpdata['stit_medUsage'],
            "stit_medSalt" => $tmpdata['stit_medSalt'],
            "stit_medMecAction" => $tmpdata['stit_medMecAction'],
            "stit_medPharmacokinets" => $tmpdata['stit_medPharmacokinets'],
            "stit_medOnstAction" => $tmpdata['stit_medOnstAction'],
            "stit_medDurationAction" => $tmpdata['stit_medDurationAction'],
            "stit_medHalfLife" => $tmpdata['stit_medHalfLife'],
            "stit_medSideEffects" => $tmpdata['stit_medSideEffects'],
            "stit_medContraIndication" => $tmpdata['stit_medContraIndication'],
            "stit_medSpecialPrecaution" => $tmpdata['stit_medSpecialPrecaution'],
            "stit_medPregnancyRelated" => $tmpdata['stit_medPregnancyRelated'],
            "stit_medAlchoholInteraction" => $tmpdata['stit_medAlchoholInteraction'],
            "stit_medOldAge" => $tmpdata['stit_medOldAge'],
            "stit_medBreastFeeding" => $tmpdata['stit_medBreastFeeding'],
            "stit_medChildRelated" => $tmpdata['stit_medChildRelated'],
            "stit_medIntication" => $tmpdata['stit_medIntication'],
            "stit_medInteraction" => $tmpdata['stit_medInteraction'],
            "stit_medDosage" => $tmpdata['stit_medDosage'],
            "stit_medStorageReq" => $tmpdata['stit_medStorageReq'],
            "stit_medMissedDosage" => $tmpdata['stit_medMissedDosage'],
            "stit_medOverdoseEffect" => $tmpdata['stit_medOverdoseEffect'],
            "stit_medExpertAdvise" => $tmpdata['stit_medExpertAdvise'],
            "stit_medHowToUse" => $tmpdata['stit_medHowToUse'],
            "stit_medFaqs" => $tmpdata['stit_medFaqs']
        );

        $status = $db->perform(FINASCOP_DB . "finascop_stock_itemmaster_sub", $dataFsims);

        //File download
        $url = str_replace('?dl=0', '?dl=1', $tmpdata['images']);

        // Use basename() function to return the base name of file 
        $fileuploadname = trim(str_replace('.', '', uniqid("", true))) . ".jpg";
        $file_name = $fileuploadname;

        // Use file_get_contents() function to get the file
        // from url and use file_put_contents() function to
        // save the file by using base name
        //Image upload
        /**
          if (file_put_contents('/tmp/' . $file_name, file_get_contents($url))) {
          $imgdownloaded = true;
          } else {
          $imgdownloaded = false;
          echo "File downloading failed. - " . "\t" . $tmpdata['sno'] . "\t" . $SKU . "\r\n";
          }
          if ($imgdownloaded === true) {
          $s3upload = new cgoS3FileHandler();
          $cloudFrontPath = 'product/main/' . $fileuploadname;
          $s3upload->putFileToS3($cloudFrontPath, AWSS3ASSETUPLOADBUCKET, '/tmp/' . $file_name);
          $cloudFrontPath = "https://" . AWSS3ASSETUPLOADBUCKET . "/" . $cloudFrontPath;
          $data = array(
          "product_id" => $masterid,
          "image_url" => $cloudFrontPath,
          "image_thumb_url" => $cloudFrontPath,
          "image_type" => 1,
          "bucket_name" => AWSS3ASSETUPLOADBUCKET,
          "bucket_path" => 'product/main/'
          );
          $status = $db->perform('finascop_stock_item_images', $data);
          }
          unlink('/tmp/' . $file_name);
         * */
        $barcount = 0;
        /* $barcount = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster_product_codes  WHERE fsipc_code = '{$tmpdata['barcode']}'  ");
          if ($barcount == 0) {
          $fstrd = array();
          $fstrd['fsipc_isIndividual'] = 0;
          $fstrd['fsipc_store'] = 0;
          $fstrd['fsipc_isCompany'] = 1;
          $fstrd['fsipc_storeGroup'] = 0;
          $fstrd['fsipc_store'] = 0;
          $fstrd['fsipc_stit_id'] = $masterid;
          $fstrd['fsipc_code'] = $tmpdata['barcode'];
          $fstrd['fsipc_codeType'] = 'Company Barcode';
          $fstrd['fsipc_createdOn'] = date("Y-m-d H:i");
          $fstrd['fsipc_createdBy'] = 0;
          $status = $db->perform('finascop_stock_itemmaster_product_codes', $fstrd);
          } else {
          echo "Duplicate barcode - " . "\t" . $tmpdata['sno'] . "\t" . $SKU . ", BARCODE -- " . $tmpdata['barcode'] . "\r\n";
          } */

        $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM finascop_stock_itemmaster WHERE stit_fsiuid = {$stit_fsiuid['fsi_uid']}");
        if ($stit_fsiuid['status'] == 'NEW') {

            $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $uuit, 'update', " fsi_uid = {$stit_fsiuid['fsi_uid']}");
        }
        $db->query("update tmp_master_pro set masterid = {$masterid} WHERE tmp_id = {$tmpdata['tmp_id']}");
        $message = 'Saved Successfully';

        $db->query('commit');
    }
} else {
    echo 'No data to import.';
}
