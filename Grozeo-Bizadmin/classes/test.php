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

function updateUniqueItemTable($uid, $uniData) {
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

                $fsuidata['fsi_count'] = intval($uidCount) - 1;
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

            $fsdata['fsi_count'] = intval($uidCount) - 1;
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
        $tmpdata['item_name'] = str_replace("'", "", trim($tmpdata['item_name']));
        $tmpdata['sku'] = str_replace("'", "", trim($tmpdata['sku']));
        $tmpdata['brand'] = str_replace("'", "", trim($tmpdata['brand']));
        $tmpdata['country'] = str_replace("'", "", trim($tmpdata['country']));
        $tmpdata['variant'] = str_replace("'", "", trim($tmpdata['variant']));
        $tmpdata['quantity'] = str_replace("'", "", trim($tmpdata['quantity']));
        $tmpdata['wt'] = str_replace("'", "", trim($tmpdata['wt']));
        $tmpdata['unit'] = str_replace("'", "", trim($tmpdata['unit']));
        $tmpdata['odoparentcat'] = str_replace("'", "", trim($tmpdata['odoparentcat']));
        $tmpdata['odocat'] = str_replace("'", "", trim($tmpdata['odocat']));
        $tmpdata['odosubcat'] = str_replace("'", "", trim($tmpdata['odosubcat']));
        $tmpdata['family'] = str_replace("'", "", trim($tmpdata['family']));
        $tmpdata['barcode'] = str_replace("'", "", trim($tmpdata['barcode']));
        $tmpdata['foodtype'] = str_replace("'", "", trim($tmpdata['foodtype']));
        $tmpdata['stit_qty'] = str_replace("'", "", trim($tmpdata['stit_qty']));
        $tmpdata['courierDelivery'] = str_replace("'", "", trim($tmpdata['courierDelivery']));
        $tmpdata['directDelivery'] = str_replace("'", "", trim($tmpdata['directDelivery']));
        $tmpdata['isRRPApplicable'] = str_replace("'", "", trim($tmpdata['isRRPApplicable']));
        $tmpdata['long_description'] = str_replace("'", "", trim($tmpdata['isRRPApplicable']));
        $tmpdata['allergens'] = str_replace("'", "", trim($tmpdata['allergens']));
        $tmpdata['preparation_use'] = str_replace("'", "", trim($tmpdata['preparation_use']));
        $tmpdata['ingredients'] = str_replace("'", "", trim($tmpdata['ingredients']));
        $tmpdata['nutritionlabel'] = str_replace("'", "", trim($tmpdata['nutritionlabel']));
        $tmpdata['manufacture'] = str_replace("'", "", trim($tmpdata['manufacture']));
        $tmpdata['featured'] = str_replace("'", "", trim($tmpdata['featured']));
        $tmpdata['popular'] = str_replace("'", "", trim($tmpdata['popular']));
        $tmpdata['itemReturnTime'] = str_replace("'", "", trim($tmpdata['itemReturnTime']));
        $tmpdata['short_description'] = str_replace("'", "", trim($tmpdata['short_description']));






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

        //$SKU = $tmpdata['stit_brand_name'] . " " . $tmpdata['item_name'] . " " . $tmpdata['stit_product_variant'] . " " . $tmpdata['stit_quantity'] . " " . $tmpdata['stit_category_name'];
        /* if(trim($tmpdata['item_name']) == trim($tmpdata['variant'])){
          $SKU = trim($tmpdata['brand']) . " " . trim($tmpdata['item_name']) . " " . $tmpdata['quantity'];
          }else{
          $SKU = trim($tmpdata['brand']) . " " . trim($tmpdata['item_name']) . " " . trim($tmpdata['variant']) . " " . trim($tmpdata['quantity']);
          } */
        //$dSKU = addslashes($SKU);
        $SKU = trim($tmpdata['brand']) . " " . trim($tmpdata['item_name']) . " " . trim($tmpdata['variant']) . " " . trim($tmpdata['stit_qty']) . " " . trim($tmpdata['unit']);
        //$SKU = $tmpdata['sku'];
        if (($tmpdata['tmp_id'] % 1000) == 0) {
            echo $tmpdata['sno'] . " -- " . $SKU . "\r\n";
        }
        $country = $db->getItemFromDB("SELECT country_id FROM finascop_country WHERE country_name = '{$tmpdata['country']}'");

        $sub_category_id = $db->getItemFromDB("SELECT sub_category_id FROM mypha_productsubcategory WHERE sub_category = '{$tmpdata['odosubcat']}'");

        if ($sub_category_id == 0) {
            echo "INVALID DATA " . "\t" . $tmpdata['sno'] . "\t" . $tmpdata['item_name'] . ", SUB CAT - " . $tmpdata['odosubcat'] . " ... \r\n";
            continue;
        }


        $main_category_id = $db->getItemFromDB("SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = '{$sub_category_id}'");

        if ($main_category_id == 0) {
            echo "INVALID DATA " . "\t" . $tmpdata['sno'] . "\t" . $tmpdata['item_name'] . ", CATE - " . $main_category_id . " ... \r\n";
            continue;
        }


        $main_category = $db->getItemFromDB("SELECT category_name FROM mypha_productcategory WHERE category_id = '{$main_category_id}'");

        $parent_category_id = $db->getItemFromDB("SELECT parent_category FROM mypha_productcategory WHERE category_id = '{$main_category_id}'");

        if ($parent_category_id == 0) {
            echo "INVALID DATA " . "\t" . $tmpdata['sno'] . "\t" . $tmpdata['item_name'] . ", -- Parent cat - " . $parent_category_id . " ... \r\n";
            continue;
        }


        $parent_category = $db->getItemFromDB("SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = {$parent_category_id}");

        //$bus_type = $db->getItemFromDB("SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = {$parent_category_id}");

        /* if ($bus_type == 0) {
          echo "INVALID DATA " . "\t" . $tmpdata['sno'] . "\t" . $tmpdata['item_name'] . ", Business type - " . $bus_type . " ... \r\n";
          continue;
          } */

        $brand = $db->getItemFromDB("SELECT brand_id FROM mypha_productbrands WHERE brand_name = '{$tmpdata['brand']}'");
        if (intval($brand) == 0) {
            echo "INVALID DATA " . "\t" . $tmpdata['sno'] . "\t" . $SKU . ", -- Brand - " . $tmpdata['brand'] . " ... \r\n";
            continue;
            //$db->query("insert into mypha_productbrands(brand_name,status,created_on,updated_on,created_by,updated_by) values('{$tmpdata['brand']}',1,NOW(),NOW(),1,1) ");
            //brand = $db->insert_id();
            //echo "NEW BRAND ID " . $brand . "\r\n";
        }
        $manufacture_id = $db->getItemFromDB("SELECT manufacture_id FROM mypha_productbrands WHERE brand_id = {$brand}");
        $manufacture_name = $db->getItemFromDB("SELECT manufacture_name FROM mypha_productmanufacture WHERE manufacture_id = {$manufacture_id}");

        $unitid = $db->getItemFromDB("SELECT unit_id FROM mypha_unit WHERE unit_name = '{$tmpdata['unit']}'");
        if ($unitid == 0) {
            echo "INVALID DATA " . "\t" . $tmpdata['sno'] . "\t" . $SKU . ", Unit - " . $tmpdata['unit'] . " ... \r\n";
            continue;
        }
        $hsnid = $db->getItemFromDB("SELECT hsn_id FROM finascop_hsn WHERE hsn_code = '{$tmpdata['HSNcode']}'");
        if ($hsnid == 0) {
            echo "INVALID DATA " . "\t" . $tmpdata['sno'] . "\t" . $SKU . ", Hsn code - " . $tmpdata['HSNcode'] . " ... \r\n";
            continue;
        }
        if ($hsnid > 0) {
            $gstpercent = $db->getItemFromDB("SELECT gst_percent FROM finascop_hsn WHERE hsn_id = {$hsnid}");

            $hsncode = $db->getItemFromDB("SELECT hsn_code FROM finascop_hsn WHERE hsn_id = {$hsnid}");
        }

        $least_package_type_id = $db->getItemFromDB("SELECT package_type_id FROM mypha_productpackage_type WHERE package_type_name = '{$tmpdata['sku']}'");

        $itemnameUnique = $db->getItemFromDB("SELECT itemname_id from finascop_stock_itemmastername WHERE item_name ='{$tmpdata['item_name']}' ");


        if ($itemnameUnique > 0) {
            
        } else {

            $data = array(
                "item_name" => $tmpdata['item_name'],
                //"item_businessType" => $bus_type,
                "status" => 1
            );

            $data['created_on'] = date('Y-m-d H:i:s');
            $data['created_by'] = 1;
            $data['updated_by'] = 1;
            $data['updated_on'] = date('Y-m-d H:i:s');
            //print_r($data);
            $status = $db->perform('finascop_stock_itemmastername', $data);
            $itemnameUnique = $db->insert_id();
        }
        $featured = (strtolower($tmpdata['featured']) == 'yes' ? 1 : 0);
        $popular = (strtolower($tmpdata['popular']) == 'yes' ? 1 : 0);
        $isRRPApplicable = (strtolower($tmpdata['isRRPApplicable']) == 'yes' ? 1 : 0);
        $courierDelivery = (strtolower($tmpdata['courierDelivery']) == 'yes' ? 1 : 0);
        $directDelivery = (strtolower($tmpdata['directDelivery']) == 'yes' ? 1 : 0);
        $stit_quantity = $tmpdata['stit_qty'] . $tmpdata['unit'];
        $datafsi = array(
            "stit_itemId" => $itemnameUnique,
            "stit_SKU" => $SKU,
            "stit_itemERPId" => '',
            "stit_itemBarcode" => $tmpdata['barcode'],
            "stit_itemReturnTime" => $tmpdata['itemReturnTime'],
            "stit_HSNCode" => $hsncode,
            "stit_hsnId" => $hsnid,
            "stit_GST" => $gstpercent,
            "stit_Description" => $tmpdata['short_description'],
            "stit_product_variant" => $tmpdata['variant'],
            "pdt_package_type_id" => 2, //miss
            "product_category" => $sub_category_id,
            //"product_midcategory" => $main_category_id,
            //"product_parentcategory" => $parent_category_id,
            "pdt_brand" => $brand,
            "featured" => $featured,
            "popular" => $popular,
            //"perishable" => 0, //miss
            "stit_custInitiate" => 0, //miss
            //"item_length" => 0, //miss
            //"item_breadth" => 0, //miss
            //"item_height" => 0, //miss
            //"item_weight" => 0, //miss
            //"stit_item_volume" => 0, //miss
            "stit_long_description" => $tmpdata['long_description'], //miss
            "stit_quantity" => $stit_quantity,
            "stit_qty" => $tmpdata['quantity'],
            "stit_itemName" => $tmpdata['item_name'],
            "stit_HSN_code" => $hsncode, //miss
            "stit_package_type_namme" => 'Packet', //miss
            "stit_category_name" => $tmpdata['odosubcat'],
            "stit_brand_name" => $tmpdata['brand'],
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
            "med_manufactureid" => $manufacture_id,
            "med_manufacturename" => $manufacture_name,
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
            "stit_storage_instruction" => $tmpdata['storage']
        );



        $fsuidata['fsi_item_id'] = $itemnameUnique;
        $fsuidata['fsi_item_name'] = $tmpdata['item_name'];
        $fsuidata['fsi_brand_id'] = $brand;
        $fsuidata['fsi_brand_name'] = $tmpdata['brand'];
        $fsuidata['fsi_category_id'] = $sub_category_id;
        $fsuidata['fsi_categry_name'] = $tmpdata['odosubcat'];
        $fsuidata['fsi_variant'] = $tmpdata['variant'];


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

        //$itemERPId = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  WHERE stit_itemERPId = '{$data['stit_itemERPId']}'  ");
        // if ($itemERPId > 0) {
        // echo "{success: false, msg:'This Item ERP Id already existing.'}";
        //exit;
        // }

        $datafsi = array_filter($datafsi, 'strlen');
        //print_r($datafsi);
        $status = $db->perform(FINASCOP_DB . "finascop_stock_itemmaster", $datafsi);
        $masterid = $db->insert_id();

        //File download
        $url = str_replace('?dl=0', '?dl=1', $tmpdata['images']);
//echo 'url'.$url.'\n';continue;
        // Use basename() function to return the base name of file 
        $fileuploadname = trim(str_replace('.', '', uniqid("", true))) . ".jpg";
        $file_name = $fileuploadname;

        // Use file_get_contents() function to get the file
        // from url and use file_put_contents() function to
        // save the file by using base name
        //Image upload
//echo 'file_name'.$file_name.'\n';continue;

        /* if (file_put_contents('/tmp/' . $file_name, file_get_contents($url))) {
          $imgdownloaded = true;
          } else {
          $imgdownloaded = false;
          echo "File downloading failed. - " . "\t" . $tmpdata['sno'] . "\t" . $SKU . "\r\n";
          }

          if ($imgdownloaded === true) {
          //echo 'imgdownloaded'.$imgdownloaded;continue;
          $s3upload = new cgoS3FileHandler();
          $cloudFrontPath = 'products/' . $fileuploadname;
          $s3upload->putFileToS3($cloudFrontPath, AWSBUCKETUPLOADS, '/tmp/' . $file_name);
          $cloudFrontPath = AWSBUCKETPATH . "/" . $cloudFrontPath;
          $data = array(
          "product_id" => $masterid,
          "image_url" => $fileuploadname,
          "image_thumb_url" => '',
          "image_type" => 1,
          "bucket_name" => '',
          "image_folder" => 'products/'
          );
          //print_r($data);
          $status = $db->perform('finascop_stock_item_images', $data);
          }
          unlink('/tmp/' . $file_name); */
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
?>