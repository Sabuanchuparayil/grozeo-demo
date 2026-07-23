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

function updateUniqueParentItemTable($uid, $uniData)
{
    global $parentdb;
    unset($uniData['fsi_uid']);
    //print_r($uniData);
    // print_r($uid);
    //           exit;
    $uniData['fsi_category_id'] = addslashes($uniData['fsi_category_id']);
    $chkUnqExiste = $parentdb->getFromDB("SELECT fsi_uid,fsi_count FROM finascop_stock_uniqueitem WHERE fsi_item_id = {$uniData['fsi_item_id']} AND fsi_brand_id = {$uniData['fsi_brand_id']} AND fsi_category_id = {$uniData['fsi_category_id']} AND fsi_variant = '" . mysqli_real_escape_string($parentdb->linker(), $uniData['fsi_variant']) . "' AND isMedicine = {$uniData['isMedicine']}", true);
    $uidCount = $parentdb->getItemFromDB("SELECT fsi_count FROM  finascop_stock_uniqueitem WHERE fsi_uid = {$uid}");
    //print_r($chkUnqExiste);
    if ($uid == 0) {

        if (intval($chkUnqExiste['fsi_uid']) > 0) {

            $fsuidata['fsi_count'] = intval($chkUnqExiste['fsi_count']) + 1;
            $status = $parentdb->perform("finascop_stock_uniqueitem", $fsuidata, 'update', " fsi_uid = {$chkUnqExiste['fsi_uid']}");
            $stit_fsiuid['fsi_uid'] = $chkUnqExiste['fsi_uid'];
        } else {

            $fsuidata = $uniData;
            $fsuidata['fsi_count'] = 1;
            $ustatus = $parentdb->perform("finascop_stock_uniqueitem", $fsuidata);
            $stit_fsiuid['fsi_uid'] = $parentdb->insert_id();
            $stit_fsiuid['status'] = 'NEW';
        }
    } else {

        if (intval($chkUnqExiste['fsi_uid']) > 0) {
            if (intval($chkUnqExiste['fsi_uid']) != $uid) {

                $fsuidata['fsi_displaylabel'] = $uniData['fsi_displaylabel'];
                if (intval($uidCount) > 0) {
                    $fsuidata['fsi_count'] = intval($uidCount) - 1;
                }
                $status = $parentdb->perform("finascop_stock_uniqueitem", $fsuidata, 'update', " fsi_uid = {$uid}");
                $uuit['fsi_def_itemmaster_id'] = $parentdb->getItemFromDB("SELECT MIN(stit_ID) FROM finascop_stock_itemmaster WHERE stit_fsiuid = {$uid}");
                $parentdb->perform("finascop_stock_uniqueitem", $uuit, 'update', " fsi_uid = {$uid}");


                $updatCou['fsi_count'] = intval($chkUnqExiste['fsi_count']) + 1;
                $status = $parentdb->perform("finascop_stock_uniqueitem", $updatCou, 'update', " fsi_uid = {$chkUnqExiste['fsi_uid']}");
                $stit_fsiuid['fsi_uid'] = $chkUnqExiste['fsi_uid'];
            } else {
                $fsuidata['fsi_displaylabel'] = $uniData['fsi_displaylabel'];
                // $fsuidata['fsi_count'] = intval($uidCount) - 1;

                $status = $parentdb->perform("finascop_stock_uniqueitem", $fsuidata, 'update', " fsi_uid = {$uid}");
                $stit_fsiuid['fsi_uid'] = $uid;
            }
        } else {
            if (intval($uidCount) > 0) {
                $fsdata['fsi_count'] = intval($uidCount) - 1;

                $status = $parentdb->perform("finascop_stock_uniqueitem", $fsdata, 'update', " fsi_uid = {$uid}");
            }
            $uuit['fsi_def_itemmaster_id'] = $parentdb->getItemFromDB("SELECT MIN(stit_ID) FROM finascop_stock_itemmaster WHERE stit_fsiuid = {$uid}");
            $parentdb->perform("finascop_stock_uniqueitem", $uuit, 'update', " fsi_uid = {$uid}");

            $fsuidata = $uniData;
            $fsuidata['fsi_count'] = 1;
            $ustatus = $parentdb->perform("finascop_stock_uniqueitem", $fsuidata);
            $stit_fsiuid['fsi_uid'] = $parentdb->insert_id();
            $stit_fsiuid['status'] = 'NEW';
        }
    }
    return $stit_fsiuid;
}

function exportProduct($productId)
{


    global $db;
    global $parentdb;
    $ItemId = $productId;
    $data = $db->getFromDB("SELECT * FROM finascop_stock_itemmaster WHERE stit_ID = {$ItemId}", true);
    //print_r($data);
    $uniqItemData = $db->getFromDB("SELECT * FROM finascop_stock_uniqueitem WHERE fsi_uid = {$data['stit_fsiuid']}", true);
    $itemMrps = $db->getMultipleData("SELECT * FROM item_mrp WHERE stit_Id = {$ItemId}", true);
    $itemImages = $db->getMultipleData("SELECT * FROM finascop_stock_item_images WHERE product_id = {$ItemId}", true);
    $itemProductCodes = $db->getMultipleData("SELECT * FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id = {$ItemId}", true);
    $itemProductCodeStores = $db->getMultipleData("SELECT * FROM finascop_stock_itemmaster_product_code_stores WHERE fsipc_stit_id = {$ItemId}", true);

    if ($data['courierDelivery'] == 1) {
        $data['courierDelivery'] = 1;
    } else {
        $data['courierDelivery'] = 0;
    }
    if ($data['directDelivery'] == 1) {
        $data['directDelivery'] = 1;
    } else {
        $data['directDelivery'] = 0;
    }
    //print_r($data);exit();
    //$itemExists = $parentdb->getItemFromDB("select COUNT(*) FROM finascop_stock_itemmaster WHERE gtin = {$data['gtin']}");


    $itemExists = $db->getItemFromDB("SELECT COUNT(grozeo_stitId) FROM product_grozeo_map where product_stitId = {$productId}");


    //$data = array_filter($data);
    //print_r($data);exit();
    unset($data['stit_ID']);
    unset($data['prdctBankItemName']);
    unset($data['pdt_sale_rate']);

    $gs1ItemMaster = $db->getItemFromDB("SELECT item_name FROM finascop_stock_itemmastername WHERE itemname_id = {$data['stit_itemId']}");
    $gs1ItemMaster = mysqli_real_escape_string($db->linker(), $gs1ItemMaster);
    $prdctMaster = $parentdb->getItemFromDB("SELECT itemname_id  FROM finascop_stock_itemmastername WHERE item_name = '{$gs1ItemMaster}'");
	//print_r($prdctMaster);exit();
    if ($prdctMaster > 0) {
        $data['stit_itemId'] = $prdctMaster;
    } else {
        /*$message = " Kindly contact admin to sync Product Master.";
        echo "{success: false, msg: '{$message}'}";
        exit;*/
        $pmData['item_name'] = $gs1ItemMaster;
        $pmData['created_on'] = date('Y-m-d H:i:s');
        $status = $parentdb->perform('finascop_stock_itemmastername', $pmData);
        $data['stit_itemId'] = $parentdb->insert_id();
        $data['stit_itemName'] = $gs1ItemMaster;
    }

    $gs1hsn = $db->getItemFromDB("SELECT hsn_code FROM finascop_hsn WHERE hsn_id = {$data['stit_hsnId']}");
    $prdcthsn = $parentdb->getItemFromDB("SELECT hsn_id FROM finascop_hsn WHERE hsn_code = '{$gs1hsn}'");
    $gst_percent = $db->getItemFromDB("SELECT gst_percent FROM finascop_hsn WHERE hsn_id = {$data['stit_hsnId']}");
    //print_r($gs1hsn);
    //print_r($prdcthsn);
    if ($prdcthsn > 0) {
        $data['stit_hsnId'] = $prdcthsn;
        $data['stit_HSNCode'] = $prdcthsn;
        $data['stit_HSN_code'] = $gs1hsn;
        $data['stit_GST'] = $parentdb->getItemFromDB("SELECT gst_percent FROM finascop_hsn WHERE hsn_id = {$data['stit_hsnId']}");
    } else {
        /*$message = " Kindly contact admin to sync HSN.";
        echo "{success: false, msg: '{$message}'}";
        exit;*/
        $hsnData['hsn_code'] = $gs1hsn;
        $hsnData['gst_percent'] = $gst_percent;
        $status = $parentdb->perform('finascop_hsn', $hsnData);
        $data['stit_hsnId'] = $parentdb->insert_id();
        $data['stit_HSNCode'] = $parentdb->insert_id();
        $data['stit_HSN_code'] = $gs1hsn;
        $data['stit_GST'] = $gst_percent;
    }

    $gs1Brand = $db->getItemFromDB("SELECT brand_name FROM mypha_productbrands WHERE brand_id = {$data['pdt_brand']}");
	$gs1Brand = mysqli_real_escape_string($db->linker(), $gs1Brand);
    $gs1Manuf = $db->getItemFromDB("SELECT manufacture_name FROM mypha_productmanufacture WHERE manufacture_id = {$data['med_manufactureid']}");
    $prdctbrand = $parentdb->getItemFromDB("SELECT brand_id FROM mypha_productbrands WHERE brand_name = '{$gs1Brand}'");
    $prdctmanu = $parentdb->getItemFromDB("SELECT manufacture_id FROM mypha_productmanufacture WHERE manufacture_name = '{$gs1Manuf}'");
    if ($prdctbrand > 0) {
        $data['pdt_brand'] = $prdctbrand;
        $data['stit_brand_name'] = $gs1Brand;
        $data['med_manufactureid'] = $parentdb->getItemFromDB("SELECT manufacture_id FROM mypha_productbrands WHERE brand_name = '{$gs1Brand}'");
        $data['med_manufacturename'] = $parentdb->getItemFromDB("SELECT manufacture_name FROM mypha_productmanufacture WHERE manufacture_id = '{$data['med_manufactureid']}'");
    } else {
        /*$message = " Kindly contact admin to sync brands.";
        echo "{success: false, msg: '{$message}'}";
        exit;*/
        if ($prdctmanu > 0) {
            $data['med_manufactureid'] = $prdctmanu;
            $data['med_manufacturename'] = $gs1Manuf;
        } else {
            $manuData['manufacture_name'] = $gs1Manuf;
            $status = $parentdb->perform('mypha_productmanufacture', $manuData);
            $data['med_manufactureid'] = $parentdb->insert_id();
            $data['med_manufacturename'] = $gs1Manuf;
        }
        $branddata['brand_name'] = $gs1Brand;
        $status = $parentdb->perform('mypha_productbrands', $branddata);
        $data['pdt_brand'] = $parentdb->insert_id();
        $data['stit_brand_name'] = $gs1Brand;
    }

    $gs1SubCateg = $db->getItemFromDB("SELECT sub_category FROM mypha_productsubcategory WHERE sub_category_id = {$data['product_category']}");
    $prdctsubcat = $parentdb->getItemFromDB("SELECT sub_category_id FROM mypha_productsubcategory WHERE sub_category = '{$gs1SubCateg}'");
    if ($prdctsubcat > 0) {
        $data['product_category'] = $prdctsubcat;
        $data['stit_category_name'] = $gs1SubCateg;
    } else {
        /* $message = " Kindly contact admin to sync Subcategory.";
        echo "{success: false, msg: '{$message}'}";
        exit;*/
        $gs1categ = $db->getItemFromDB("SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = {$data['product_category']}");
        $gs1categName = $db->getItemFromDB("SELECT category_name FROM mypha_productcategory WHERE category_id = {$gs1categ}");
        $prdctcateg = $parentdb->getItemFromDB("SELECT category_id FROM mypha_productcategory WHERE category_name = '{$gs1categName}'");
        if ($prdctcateg > 0) {
            $subcatdata['main_category'] = $prdctcateg;
        } else {
            $gs1ParentCateg = $db->getItemFromDB("SELECT parent_category FROM mypha_productcategory WHERE category_id = {$gs1categ}");
            $gs1ParentcategName = $db->getItemFromDB("SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = {$gs1ParentCateg}");
            $prdctParentcateg = $parentdb->getItemFromDB("SELECT parent_category_id FROM mypha_productparent_category WHERE parent_category = '{$gs1ParentcategName}'");
            if ($prdctParentcateg > 0) {
                $catData['parent_category'] = $prdctParentcateg;
            } else {
                $gs1retCateg = $db->getItemFromDB("SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = {$gs1ParentCateg}");
                $gs1retCategName = $db->getItemFromDB("SELECT business_type_name FROM finascop_business_type WHERE business_type_id = {$gs1retCateg}");
                $prdctretCateg = $parentdb->getItemFromDB("SELECT business_type_id FROM finascop_business_type WHERE business_type_name = '{$gs1retCategName}'");
                if ($prdctretCateg > 0) {
                    $pacatData['parent_category_businessType'] = $prdctretCateg;
                } else {
                    $retcatData['business_type_name'] = $gs1retCategName;
                    $status = $parentdb->perform('finascop_business_type', $retcatData);
                    $pacatData['parent_category_businessType'] = $parentdb->insert_id();
                }
                $pacatData['parent_category'] = $gs1ParentcategName;
                $status = $parentdb->perform('mypha_productparent_category', $pacatData);
                $catData['parent_category'] = $parentdb->insert_id();
            }
            $catData['category_name'] = $gs1categName;
            $status = $parentdb->perform('mypha_productcategory', $catData);
            $subcatdata['main_category'] = $parentdb->insert_id();
        }
        $subcatdata['sub_category'] = $gs1SubCateg;
        $status = $parentdb->perform('mypha_productsubcategory', $subcatdata);
        $data['product_category'] = $parentdb->insert_id();
        $data['stit_category_name'] = $gs1SubCateg;
    }

    if ($itemExists > 0) {
        $stitId = $db->getItemFromDB("SELECT grozeo_stitId FROM product_grozeo_map where product_stitId = {$productId}");
        //$stitId = $parentdb->getItemFromDB("select stit_ID FROM finascop_stock_itemmaster WHERE gtin = '{$data['gtin']}'");

        $currentUid = $parentdb->getItemFromDB("select stit_fsiuid FROM finascop_stock_itemmaster WHERE stit_ID = {$stitId}");
        $stit_fsiuid = updateUniqueParentItemTable($currentUid, $uniqItemData);
        $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];

        $status = $parentdb->perform('finascop_stock_itemmaster', $data, 'update', " stit_ID = '{$stitId}'");
    } else {
        $lesku = mysqli_real_escape_string($db->linker(), $data['stit_SKU']);
        $checkSKUExists = $parentdb->getItemFromDB("select stit_ID FROM finascop_stock_itemmaster WHERE stit_SKU = '{$lesku}'");
        if ($checkSKUExists > 0) {
            $stitId = $checkSKUExists;            
            $currentUid = $parentdb->getItemFromDB("select stit_fsiuid FROM finascop_stock_itemmaster WHERE stit_SKU = '{$lesku}'");
            $stit_fsiuid = updateUniqueParentItemTable(0, $uniqItemData);
            $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];
            $status = $parentdb->perform('finascop_stock_itemmaster', $data, 'update', " stit_ID = {$checkSKUExists}");
        } else {
            $status = $parentdb->perform('finascop_stock_itemmaster', $data);
            $stitId = $parentdb->insert_id();
        }
    }

    $idMapped = $db->getItemFromDB("SELECT COUNT(*) FROM product_grozeo_map where grozeo_stitId = {$stitId} AND product_stitId = {$productId}");

    if ($idMapped > 0) {
        $ppdata['updatedOn'] = date("Y-m-d H:i:s");
        $status = $db->perform('product_grozeo_map', $ppdata, 'update', " grozeo_stitId = {$stitId} AND product_stitId = {$productId} ");
    } else {
        $ppdata['product_stitId'] = $productId;
        $ppdata['grozeo_stitId'] = $stitId;
        $ppdata['createdOn'] = date("Y-m-d H:i:s");
        $status = $db->perform('product_grozeo_map', $ppdata);
    }

    //exportLog

    $expLog['product_stitId'] = $productId;
    $expLog['grozeo_stitId'] = $stitId;
    $expLog['enteredOn'] = date("Y-m-d H:i:s");
    $expLog['enteredBy'] = 1;
    $status = $db->perform('product_export_log', $expLog);
    //echo $stitId;
    /*foreach ($itemMrps as $itemMrp) {
        if ($itemMrp['itemMrp'] > 0) {
            $mrpExist = $parentdb->getItemFromDB("SELECT COUNT(*) FROM item_mrp WHERE stit_Id = {$stitId} AND itemMrp = {$itemMrp['itemMrp']}");
            if ($mrpExist == 0) {
                $mrpData['stit_Id'] = $stitId;
                $mrpData['itemMrp'] = $itemMrp['itemMrp'];
                $mrpData['location'] = $itemMrp['location'];
                $mrpData['createdOn'] = date("Y-m-d H:i:s");
                $status = $parentdb->perform('item_mrp', $mrpData);
            }
        }
    }*/
    foreach ($itemProductCodes as $itemProductCode) {
        if (!empty($itemProductCode['fsipc_code'])) {
            $isGlobal = $parentdb->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id = '{$stitId}' AND fsipc_isCompany = 1 and fsipc_code = '{$data['gtin']}'");
            if ($isGlobal == 0) {
                $fstrd['fsipc_isCompany'] = 1;
                $fstrd['fsipc_isIndividual'] = 1;
                $fstrd['fsipc_storeGroup'] = 0;
                $fstrd['fsipc_store'] = 0;
                $fstrd['fsipc_stit_id'] = $stitId;
                $fstrd['fsipc_code'] = $data['gtin'];
                $fstrd['fsipc_codeType'] = 'Company Barcode';

                $fstrd['fsipc_createdOn'] = date("Y-m-d H:i");
                $fstrd['fsipc_createdBy'] = 1;

                $status = $parentdb->perform('finascop_stock_itemmaster_product_codes', $fstrd);
                $fsipcs['fsipc_id'] = $db->insert_id();

                $fsipcs['fsipc_stit_id'] = $stitId;
                $fsipcs['fsipcs_store'] = $fstrd['fsipc_store'];
                $fsipcs['fsipcs_Code'] = $fstrd['fsipc_code'];
                $status = $parentdb->perform('finascop_stock_itemmaster_product_code_stores', $fsipcs);
            }
        }
    }


    $itemImageExist = $parentdb->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_item_images WHERE product_id = {$stitId}");
    if ($itemImageExist > 0) {
        $status = $parentdb->query("DELETE FROM finascop_stock_item_images WHERE product_id = {$stitId}");
    }
    foreach ($itemImages as $itemImage) {
        if (!empty($itemImage['image_url'])) {
            $imgdata = array(
                "product_id" => $stitId,
                "image_url" => $itemImage['image_url'],
                "image_thumb_url" => '',
                "createdBy" => 1,
                "created_at" => date("Y-m-d H:i:s"),
                "image_type" => $itemImage['image_type'],
                "bucket_name" => '',
                "image_folder" => 'products/'
            );
            $status = $parentdb->perform('finascop_stock_item_images', $imgdata);
        }
    }
}

$db = new sqlDb(DSN);
$parentdb = new sqlDb(PARENTDSN);

$qry = "SELECT stit_ID,stit_SKU,stit_category_name,product_category,gtin FROM finascop_stock_itemmaster 
WHERE stit_ID IN (28878)";

$tmpsdata = $db->getMultipleData($qry, true);
//print_r($tmpsdata);exit();
if (count($tmpsdata) > 0) {
    foreach ($tmpsdata as $tmpdata) {
        $ItemId = $tmpdata['stit_ID'];
        $db->query('begin');
        exportProduct($ItemId);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Data exported '}";
        } else {
            echo "{'success':False,'valid':false,'msg': 'Error While Saving.'}";
        }
    }
}
