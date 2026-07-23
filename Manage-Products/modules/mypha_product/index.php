<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");
require_once(ROOT . '/finascop_config/lib.php');
define('AWS_ROOT', '/home/system/awsapi');
require(AWS_ROOT . '/aws-autoloader.php');

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
    $itemAttributes = $db->getMultipleData("SELECT * FROM attributeProductMap WHERE stitId = {$ItemId}", true);

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
    $stit_SKU = mysqli_real_escape_string($db->linker(), $data['stit_SKU']);
    $bgitemExists = $parentdb->getItemFromDB("select stit_ID FROM finascop_stock_itemmaster WHERE stit_SKU = '{$stit_SKU}' AND stit_StoreGroup = 0");

    $itemExists = $db->getItemFromDB("SELECT COUNT(grozeo_stitId) FROM product_grozeo_map where product_stitId = {$productId}");


    //$data = array_filter($data);
    //print_r($data);exit();
    unset($data['stit_ID']);
    unset($data['prdctBankItemName']);
    unset($data['pdt_sale_rate']);

    $gs1ItemMaster = $db->getItemFromDB("SELECT item_name FROM finascop_stock_itemmastername WHERE itemname_id = {$data['stit_itemId']}");
    $gs1ItemMaster = mysqli_real_escape_string($db->linker(), $gs1ItemMaster);
    $prdctMaster = $parentdb->getItemFromDB("SELECT itemname_id  FROM finascop_stock_itemmastername WHERE item_name = '{$gs1ItemMaster}'");
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
    $gs1hsn_description = $db->getItemFromDB("SELECT hsn_description FROM finascop_hsn WHERE hsn_id = {$data['stit_hsnId']}");
    $gs1taxValues = $db->getFromDB("SELECT hsnGst,hsnCess,hsnDescription FROM hsn_value WHERE id = {$data['taxValueId']}", true);
    $prdcthsn = $parentdb->getItemFromDB("SELECT hsn_id FROM finascop_hsn WHERE hsn_code = '{$gs1hsn}'");

    //$gst_percent = $db->getItemFromDB("SELECT gst_percent FROM finascop_hsn WHERE hsn_id = {$data['stit_hsnId']}");
    $gst_percent =  $gs1taxValues['hsnGst'];

    //print_r($gs1hsn);
    //print_r($prdcthsn);
    if ($prdcthsn > 0) {
        $pdctTaxValues = $parentdb->getFromDB("SELECT id,hsnGst,hsnCess,hsnDescription FROM hsn_value WHERE hsnId = {$prdcthsn} and hsnGst = {$gs1taxValues['hsnGst']}", true);
        $data['stit_hsnId'] = $prdcthsn;
        $data['stit_HSNCode'] = $prdcthsn;
        $data['stit_HSN_code'] = $gs1hsn;
        if ($pdctTaxValues['id'] > 0) {
            $data['stit_GST'] = $pdctTaxValues['hsnGst'];
            $data['taxValueId'] = $pdctTaxValues['id'];
        } else {
            $ptvData['hsnGst'] = $gs1taxValues['hsnGst'];
            $ptvData['hsnCess'] = $gs1taxValues['hsnCess'];
            $ptvData['hsnDescription'] = $gs1taxValues['hsnDescription'];
            $ptvData['hsnId'] = $prdcthsn;
            $status = $parentdb->perform('hsn_value', $ptvData);

            $data['taxValueId'] = $parentdb->insert_id();
            $data['stit_HSN_code'] = $gs1hsn;
            $data['stit_GST'] = $pdctTaxValues['hsnGst'];
        }
        //$data['stit_GST'] = $parentdb->getItemFromDB("SELECT gst_percent FROM finascop_hsn WHERE hsn_id = {$data['stit_hsnId']}");
        //print_r($data);
    } else {
        /*$message = " Kindly contact admin to sync HSN.";
        echo "{success: false, msg: '{$message}'}";
        exit;*/
        $hsnData['hsn_code'] = $gs1hsn;
        $hsnData['gst_percent'] = $gs1taxValues['hsnGst'];
        $hsnData['hsn_description'] = $gs1hsn_description;
        $status = $parentdb->perform('finascop_hsn', $hsnData);

        $ptviData['hsnGst'] = $gs1taxValues['hsnGst'];
        $ptvData['hsnCess'] = $gs1taxValues['hsnCess'];
        $ptvData['hsnDescription'] = $gs1taxValues['hsnDescription'];
        $ptvData['hsnId'] = $parentdb->insert_id();

        $data['stit_hsnId'] = $parentdb->insert_id();
        $data['stit_HSNCode'] = $parentdb->insert_id();

        $status = $parentdb->perform('hsn_value', $ptvData);

        $data['taxValueId'] = $parentdb->insert_id();
        $data['stit_HSN_code'] = $gs1hsn;
        $data['stit_GST'] = $gs1taxValues['hsnGst'];
    }

    $gs1Brand = $db->getItemFromDB("SELECT brand_name FROM mypha_productbrands WHERE brand_id = {$data['pdt_brand']}");

    //$gs1Brand = mysqli_real_escape_string($db->linker(), $gs1Brand);

    $gs1Manuf = $db->getItemFromDB("SELECT manufacture_name FROM mypha_productmanufacture WHERE manufacture_id = {$data['med_manufactureid']}");
    //$gs1Manuf = mysqli_real_escape_string($db->linker(), $gs1Manuf);
    $prdctbrand = $parentdb->getItemFromDB('SELECT brand_id FROM mypha_productbrands WHERE brand_name = "' . $gs1Brand . '"');
    //echo $prdctbrand;exit();
    $prdctmanu = $parentdb->getItemFromDB('SELECT manufacture_id FROM mypha_productmanufacture WHERE manufacture_name = "' . $gs1Manuf . '"');
    if ($prdctbrand > 0) {
        $data['pdt_brand'] = $prdctbrand;
        $data['stit_brand_name'] = $gs1Brand;
        $data['med_manufactureid'] = $parentdb->getItemFromDB('SELECT manufacture_id FROM mypha_productbrands WHERE brand_name = "' . $gs1Brand . '"');
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
    $gs1SubCateg = mysqli_real_escape_string($db->linker(), $gs1SubCateg);
    $prdctsubcat = $parentdb->getItemFromDB("SELECT sub_category_id FROM mypha_productsubcategory WHERE sub_category_id = {$data['product_category']}");
    if ($prdctsubcat > 0) {
        $data['product_category'] = $prdctsubcat;
        $data['stit_category_name'] = $gs1SubCateg;
    } else {
        $message = " Kindly contact admin to sync Subcategory.";
        echo "{success: false, msg: '{$message}'}";
        exit;
        /*$gs1categ = $db->getItemFromDB("SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = {$data['product_category']}");
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
        $data['stit_category_name'] = $gs1SubCateg;*/
    }
    // List of keys to check
    $keysToCheck = ['stit_PeriodTo', 'stit_PeriodFrom', 'verifedOn'];

    foreach ($keysToCheck as $key) {
        // Check if the key exists and is either empty or invalid
        if (!isset($data[$key]) || $data[$key] === '' || $data[$key] === null) {
            unset($data[$key]); // Unset the key if empty
        }
    }
    if ($itemExists > 0 || $bgitemExists > 0) {
        $stitId = $db->getItemFromDB("SELECT grozeo_stitId FROM product_grozeo_map where product_stitId = {$productId}");
        if ($stitId == 0) {
            $stitId = $bgitemExists;
        }
        //$stitId = $parentdb->getItemFromDB("select stit_ID FROM finascop_stock_itemmaster WHERE gtin = '{$data['gtin']}'");

        $currentUid = $parentdb->getItemFromDB("select stit_fsiuid FROM finascop_stock_itemmaster WHERE stit_ID = {$stitId}");
        $stit_fsiuid = updateUniqueParentItemTable($currentUid, $uniqItemData);
        $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];

        $status = $parentdb->perform('finascop_stock_itemmaster', $data, 'update', " stit_ID = '{$stitId}'");
    } else {
        $lesku = mysqli_real_escape_string($db->linker(), $data['stit_SKU']);
        $checkSKUExists = $parentdb->getItemFromDB('select stit_ID FROM finascop_stock_itemmaster WHERE stit_SKU = "{$lesku}"');
        if ($checkSKUExists > 0) {
            $stitId = $checkSKUExists;
            $currentUid = $parentdb->getItemFromDB('select stit_fsiuid FROM finascop_stock_itemmaster WHERE stit_SKU = "{$lesku}"');
            $stit_fsiuid = updateUniqueParentItemTable($currentUid, $uniqItemData);
            $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];
            $status = $parentdb->perform('finascop_stock_itemmaster', $data, 'update', " stit_ID = {$checkSKUExists}");
        } else {
            $stit_fsiuid = updateUniqueParentItemTable(0, $uniqItemData);
            $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];
            //print_r($data);
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
    $expLog['enteredBy'] = $_SESSION['admin']->Finascop_UserId;
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
        //print_r($itemProductCode);
        if (!empty($itemProductCode['fsipc_code'])) {
            $isGlobal = $parentdb->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id = '{$stitId}' AND fsipc_isCompany = 1 and fsipc_code = '{$itemProductCode['fsipc_code']}'");
            if ($isGlobal == 0) {
                $fstrd['fsipc_isCompany'] = 1;
                $fstrd['fsipc_isIndividual'] = 1;
                $fstrd['fsipc_storeGroup'] = 0;
                $fstrd['fsipc_store'] = 0;
                $fstrd['fsipc_stit_id'] = $stitId;
                $fstrd['fsipc_code'] = $itemProductCode['fsipc_code'];
                $fstrd['fsipc_codeType'] = 'Company Barcode';

                $fstrd['fsipc_createdOn'] = date("Y-m-d H:i");
                $fstrd['fsipc_createdBy'] = $_SESSION['admin']->Finascop_UserId;

                $status = $parentdb->perform('finascop_stock_itemmaster_product_codes', $fstrd);
                $isExistPc = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_code_stores WHERE fsipc_stit_id = {$stitId} AND fsipcs_store = {$fstrd['fsipc_store']} AND fsipcs_Code = {$fstrd['fsipc_code']}");
                $fsipcs['fsipc_id'] = $db->insert_id();

                if ($isExistPc == 0) {
                    $fsipcs['fsipc_stit_id'] = $stitId;
                    $fsipcs['fsipcs_store'] = $fstrd['fsipc_store'];
                    $fsipcs['fsipcs_Code'] = $fstrd['fsipc_code'];
                    $status = $parentdb->perform('finascop_stock_itemmaster_product_code_stores', $fsipcs);
                }
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
                "createdBy" => $_SESSION['admin']->Finascop_UserId,
                "created_at" => date("Y-m-d H:i:s"),
                "image_type" => empty($itemImage['image_type']) ? 0 : $itemImage['image_type'],
                "bucket_name" => '',
                "image_folder" => 'products/'
            );
            $status = $parentdb->perform('finascop_stock_item_images', $imgdata);
        }
    }

    foreach ($itemAttributes as $itemAttribute) {
        if ($itemAttribute['id'] > 0) {
            $attributeExist = $parentdb->getItemFromDB("SELECT COUNT(*) FROM attributeProductMap WHERE stitId = {$stitId} AND attributeId = {$itemAttribute['attributeId']} AND attributeValueId = {$itemAttribute['attributeValueId']}");
            if ($attributeExist == 0) {
                $attrData['stitId'] = $stitId;
                $attrData['attributeId'] = $itemAttribute['attributeId'];
                $attrData['attributeValueId'] = $itemAttribute['attributeValueId'];
                $attrData['createdOn'] = date("Y-m-d H:i:s");
                $status = $parentdb->perform('attributeProductMap', $attrData);
            }
        }
    }
}

function exportMerchantProduct($productId)
{


    global $db;
    global $parentdb;
    $ItemId = $productId;
    $data = $db->getFromDB("SELECT case_configuration, ccs_breadth, ccs_height, ccs_length, ccs_nos, ccs_package_type_id, ccs_package_type_name, ccs_volume,
ccs_weight, ccsb_package_type_id, ccsb_package_type_name, cos_breadth, cos_height, cos_length, cos_nos, cos_package_type_id,
cos_package_type_name, cos_volume, cos_weight, cosb_package_type_id, cosb_package_type_name, courierDelivery, createdBy,
createdOn, cs_breadth, cs_height, cs_length, cs_nos, cs_package_type_id, cs_package_type_name, cs_volume, cs_weight,
csb_package_type_id, csb_package_type_name, directDelivery, directPurchase, dosform_id, dosform_name, ds_breadth, ds_height,
ds_length, ds_nos, ds_package_type_id, ds_package_type_name, ds_volume, ds_weight, dsb_package_type_id, dsb_package_type_name,
featured, gtin, isEdited, isMedicine, isRRPApplicable, isVerified, item_breadth, item_height, item_length, item_weight,
itemProcessingTime, least_package_type_id, least_package_type_name, med_drug_groupid, med_drug_groupname, med_manufactureid,
med_manufacturename, medcompos_id, medcompos_name, pdt_brand, pdt_package_type_id, pdt_product_type_id, pdt_sale_rate, popular,
prdctBankItemName, prescription, product_category, product_image_url, product_is_home, product_s3_addbucket, product_s3_addfile,
product_s3_mainbucket, product_s3_mainfile, product_tags, rs_breadth, rs_height, rs_length, rs_nos, rs_package_type_id,
rs_package_type_name, rs_volume, rs_weight, rsb_package_type_id, rsb_package_type_name, stdpckl11_package_type_id,
stdpckl12_package_type_id, stdpckl1_nos, stdpckl21_package_type_id, stdpckl22_package_type_id, stdpckl2_nos, stdpckl31_package_type_id,
stdpckl32_package_type_id, stdpckl3_nos, stdpckl41_package_type_id, stdpckl42_package_type_id, stdpckl4_nos, stgp_groupID, stii_csb,
stii_csbretail, stit11_maximumqty, stit11_minimumqty, stit12_maximumqty, stit12_minimumqty, stit13_maximumqty, stit13_minimumqty,
stit_allergens, stit_brand_name, stit_category_name, stit_ConvertCalcMode, stit_ConvertCalcRate, stit_Convertible, stit_courierWt,
stit_custInitiate, stit_Description, stit_fixedB2BRates, stit_foodtype, stit_fsiuid, stit_GST, stit_HasChildItem,
stit_HSN_code, stit_HSNCode, stit_hsnId, stit_ingredients, stit_item_volume, stit_itemBarcode, stit_itemERPId, stit_itemId,
stit_itemName, stit_itemReturnTime, stit_long_description, stit_Min_Stock, stit_MRP, stit_nutritionlabel, stit_OpeningStock,
stit_orgin_country, stit_package_master, stit_package_type_id, stit_package_type_namme, stit_ParentItemId, stit_PeriodFrom,
stit_PeriodTo, stit_preparation_use, stit_product_variant, stit_PurchaseEnabled, stit_qty, stit_quantity, stit_safety_warning,
stit_SalesEnabled, stit_salesUnit, stit_SKU, stit_status, stit_stdPacking, stit_StockEnabled, stit_storage_instruction,
stit_StoreGroup, stit_Tangible, stit_unit, stit_updatedOn, stit_warning, stitl1_optimumqty, stitl2_optimumqty, stitl3_optimumqty,
taxValueId, updatedBy FROM third_party_products WHERE stit_ID = {$ItemId}", true);
    //print_r($data);
    $uniqItemData = $db->getFromDB("SELECT * FROM third_party_products_uniqueitem WHERE fsi_uid = {$data['stit_fsiuid']}", true);
    $itemImages = $db->getMultipleData("SELECT * FROM thirdparty_item_images WHERE product_id = {$ItemId}", true);

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

    $itemProductCodes = $db->getMultipleData("SELECT * FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id = {$ItemId}", true);
    $gs1ItemMaster = $db->getItemFromDB("SELECT item_name FROM finascop_stock_itemmastername WHERE itemname_id = {$data['stit_itemId']}");
    $gs1ItemMaster = mysqli_real_escape_string($db->linker(), $gs1ItemMaster);
    $prdctMaster = $parentdb->getItemFromDB("SELECT itemname_id  FROM finascop_stock_itemmastername WHERE item_name = '{$gs1ItemMaster}'");
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
    $gs1hsn_description = $db->getItemFromDB("SELECT hsn_description FROM finascop_hsn WHERE hsn_id = {$data['stit_hsnId']}");
    $gs1taxValues = $db->getFromDB("SELECT hsnGst,hsnCess,hsnDescription FROM hsn_value WHERE id = {$data['taxValueId']}", true);
    $prdcthsn = $parentdb->getItemFromDB("SELECT hsn_id FROM finascop_hsn WHERE hsn_code = '{$gs1hsn}'");

    if ($prdcthsn > 0) {
        $pdctTaxValues = $parentdb->getFromDB("SELECT id,hsnGst,hsnCess,hsnDescription FROM hsn_value WHERE hsnId = {$prdcthsn} and hsnGst = {$gs1taxValues['hsnGst']}", true);
        $data['stit_hsnId'] = $prdcthsn;
        $data['stit_HSNCode'] = $gs1hsn;
        $data['stit_HSN_code'] = $gs1hsn;
        if ($pdctTaxValues['id'] > 0) {
            $data['stit_GST'] = $pdctTaxValues['hsnGst'];
            $data['taxValueId'] = $pdctTaxValues['id'];
        } else {
            $ptvData['hsnGst'] = $gs1taxValues['hsnGst'];
            $ptvData['hsnCess'] = $gs1taxValues['hsnCess'];
            $ptvData['hsnDescription'] = $gs1taxValues['hsnDescription'];
            $ptvData['hsnId'] = $prdcthsn;
            $status = $parentdb->perform('hsn_value', $ptvData);

            $data['taxValueId'] = $parentdb->insert_id();
            $data['stit_HSN_code'] = $gs1hsn;
            $data['stit_GST'] = $pdctTaxValues['hsnGst'];
        }
    } else {

        $hsnData['hsn_code'] = $gs1hsn;
        $hsnData['gst_percent'] = $gs1taxValues['hsnGst'];
        $hsnData['hsn_description'] = $gs1hsn_description;
        $status = $parentdb->perform('finascop_hsn', $hsnData);

        $ptviData['hsnGst'] = $gs1taxValues['hsnGst'];
        $ptvData['hsnCess'] = $gs1taxValues['hsnCess'];
        $ptvData['hsnDescription'] = $gs1taxValues['hsnDescription'];
        $ptvData['hsnId'] = $parentdb->insert_id();

        $data['stit_hsnId'] = $parentdb->insert_id();
        $data['stit_HSNCode'] = $gs1hsn;

        $status = $parentdb->perform('hsn_value', $ptvData);

        $data['taxValueId'] = $parentdb->insert_id();
        $data['stit_HSN_code'] = $gs1hsn;
        $data['stit_GST'] = $gs1taxValues['hsnGst'];
    }

    $gs1Brand = $db->getItemFromDB("SELECT brand_name FROM mypha_productbrands WHERE brand_id = {$data['pdt_brand']}");

    $gs1Manuf = $db->getItemFromDB("SELECT manufacture_name FROM mypha_productmanufacture WHERE manufacture_id = {$data['med_manufactureid']}");
    $prdctbrand = $parentdb->getItemFromDB('SELECT brand_id FROM mypha_productbrands WHERE brand_name = "' . $gs1Brand . '"');
    $prdctmanu = $parentdb->getItemFromDB('SELECT manufacture_id FROM mypha_productmanufacture WHERE manufacture_name = "' . $gs1Manuf . '"');
    if ($prdctbrand > 0) {
        $data['pdt_brand'] = $prdctbrand;
        $data['stit_brand_name'] = $gs1Brand;
        $data['med_manufactureid'] = $parentdb->getItemFromDB('SELECT manufacture_id FROM mypha_productbrands WHERE brand_name = "' . $gs1Brand . '"');
        $data['med_manufacturename'] = $parentdb->getItemFromDB("SELECT manufacture_name FROM mypha_productmanufacture WHERE manufacture_id = '{$data['med_manufactureid']}'");
    } else {
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
    $gs1SubCateg = mysqli_real_escape_string($db->linker(), $gs1SubCateg);
    $prdctsubcat = $parentdb->getItemFromDB("SELECT sub_category_id FROM mypha_productsubcategory WHERE sub_category_id = {$data['product_category']}");
    if ($prdctsubcat > 0) {
        $data['product_category'] = $prdctsubcat;
        $data['stit_category_name'] = $gs1SubCateg;
    } else {
        $message = " Kindly contact admin to sync Subcategory.";
        echo "{success: false, msg: '{$message}'}";
        exit;
    }

    $lesku = mysqli_real_escape_string($db->linker(), $data['stit_SKU']);
    $checkSKUExists = $parentdb->getItemFromDB("select stit_ID FROM finascop_stock_itemmaster WHERE stit_SKU = '{$lesku}' AND stit_StoreGroup = {$data['stit_StoreGroup']}");

    $data = array_filter($data, function ($value) {
        return trim($value) !== ''; // Remove empty strings and whitespace
    });

    if ($checkSKUExists > 0) {
        $stitId = $checkSKUExists;
        $currentUid = $parentdb->getItemFromDB("select stit_fsiuid FROM finascop_stock_itemmaster WHERE stit_ID = {$stitId}");
        $stit_fsiuid = updateUniqueParentItemTable($currentUid, $uniqItemData);
        $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];
        $status = $parentdb->perform('finascop_stock_itemmaster', $data, 'update', " stit_ID = {$checkSKUExists}");
        $stitId = $checkSKUExists;
    } else {
        $stit_fsiuid = updateUniqueParentItemTable(0, $uniqItemData);
        $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];
        $status = $parentdb->perform('finascop_stock_itemmaster', $data);
        $stitId = $parentdb->insert_id();
    }

    $branchId = $parentdb->getItemFromDB("SELECT br_ID FROM finascop_branch WHERE br_storeGroup = {$data['stit_StoreGroup']} LIMIT 1");
    $isStockAvailable = $parentdb->getItemFromDB("SELECT id FROM finascop_stock_branch_inventory WHERE stit_id = {$stitId}  AND branch_id = {$branchId}");
    if ($isStockAvailable == 0) {
        $stkEntry['branch_id'] = $branchId;
        $stkEntry['stit_id'] = $stitId;
        $stkEntry['item_count'] = 0;
        $stkEntry['mrp'] = 0;
        $stkEntry['selling_price'] = 0;
        $status = $parentdb->perform('finascop_stock_branch_inventory', $stkEntry);
    }


    $expLog['grozeo_stitId'] = $stitId;
    $expLog['stit_updatedOn'] = date("Y-m-d H:i:s");
    $expLog['verifedBy'] = $_SESSION['admin']->Finascop_UserId;
    $status = $db->perform('third_party_products', $expLog, 'update', " stit_ID = {$productId}");

    foreach ($itemProductCodes as $itemProductCode) {
        if (!empty($itemProductCode['fsipc_code'])) {
            $fstrd['fsipc_isCompany'] = $itemProductCode['fsipc_isCompany'];
            $fstrd['fsipc_isIndividual'] = $itemProductCode['fsipc_isIndividual'];
            $fstrd['fsipc_storeGroup'] = $itemProductCode['fsipc_storeGroup'];
            $fstrd['fsipc_store'] = 0;
            $fstrd['fsipc_stit_id'] = $stitId;
            $fstrd['fsipc_code'] = $itemProductCode['fsipc_code'];
            $fstrd['fsipc_codeType'] = $itemProductCode['fsipc_codeType'];

            $fstrd['fsipc_createdOn'] = date("Y-m-d H:i");
            $fstrd['fsipc_createdBy'] = $_SESSION['admin']->Finascop_UserId;

            $status = $parentdb->perform('finascop_stock_itemmaster_product_codes', $fstrd);
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
                "createdBy" => $_SESSION['admin']->Finascop_UserId,
                "created_at" => date("Y-m-d H:i:s"),
                "image_type" => empty($itemImage['image_type']) ? 0 : $itemImage['image_type'],
                "bucket_name" => '',
                "image_folder" => 'products/'
            );
            $status = $parentdb->perform('finascop_stock_item_images', $imgdata);
        }
    }
}
switch ($op) {
    case 'listItemMasterData':
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['item_id', 'item_name', 'item_code'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'item_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'ItemId' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');

        $searchBrand = '';
        $searchCategory = '';

        $search = " WHERE 1=1 ";

        $allProducts = $_POST['allProducts'];
        //$searchBrand = $_POST['pdctsearchBrand'];
        //$searchCategory = $_POST['pdctsearchCategory'];
        if ($allProducts == 1) {
            $search .= " ";
        } else {
            //if ($_SESSION['admin']->IsSuperUser == 'Yes') {
            $search = " WHERE ItemId = 0  ";
            //}
        }
        if (isset($filter)) {
            $filter = $_POST['filter'];
            foreach ($filter as $key => $val) {
                $type = $val['data']['type'];
                $value = $val['data']['value'];
                $field = $val['field'];
                //$search = " WHERE 1=1 ";

                switch ($val['data']['type']) {
                    case 'string':
                        /*if ($val['field'] == 'stit_HSN_code') {
                            $hsn_code = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(hsn_id),0) FROM finascop_hsn WHERE hsn_code LIKE '{$val['data']['value']}%' ");
                            $search .= " AND stit_HSNCode IN({$hsn_code}) ";
                        } else */
                        switch ($val['field']) {
                            case 'stit_category_name':
                                $searchCategory = $val['data']['value'];
                                $search .= " AND stit_category_name LIKE '%{$searchCategory}%' ";
                                break;
                            case 'stit_brand_name':
                                $searchBrand = $val['data']['value'];
                                $search .= " AND stit_brand_name LIKE '%{$searchBrand}%' ";
                                break;
                            case 'statusName':
                                if ($val['data']['value'] == 'Active') {
                                    $search .= " and stit_status = 1 ";
                                } else if ($val['data']['value'] == 'Inactive') {
                                    $search .= " and stit_status = 0 ";
                                } else {
                                    $search .= " and stit_status IN(1,0) ";
                                }
                                break;
                            case 'isVerified':
                                if ($val['data']['value'] == 'Yes') {
                                    $search .= " and (isVerified = 1) ";
                                } else if ($val['data']['value'] == 'No') {
                                    $search .= " and (isVerified = 0) ";
                                } else {
                                    $search .= " and (isVerified IN(1,0)) ";
                                }
                                break;
                            default:
                                $search .= " and ({$val['field']} LIKE '%{$val['data']['value']}%') ";
                                break;
                        }
                        break;
                    case 'list':
                        if ($val['field'] == 'isVerified') {
                            if ($val['data']['value'] == 'Yes') {
                                $search .= " and (isVerified = 'Yes') ";
                            } else if ($val['data']['value'] == 'No') {
                                $search .= " and (isVerified = 'No') ";
                            } else {
                                $search .= "  ";
                            }
                        } else {
                            $search .= " and ({$val['field']} LIKE '{$val['data']['value']}%') ";
                        }
                        break;
                    case 'numeric':
                        if ($val['field'] == 'tax') {
                            $search .= " AND stit_GST " . $comparisons[$val['data']['comparison']] . " " . $val['data']['value'];
                        } else {
                            $search .= " AND " . $val['field'] . " " . $comparisons[$val['data']['comparison']] . " " . $val['data']['value'];
                        }
                        break;
                    case 'date':
                        switch ($val['data']['comparison']) {
                            case 'gt':
                                $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') > '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                break;
                            case 'lt':
                                $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') < '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                break;
                            case 'eq':
                                $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') = '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                break;
                        }

                        break;
                }
            }
        }

        if ($_POST['search'] == 1) {
            $countQuery = "SELECT count(*)
            from " . FINASCOP_DB . "finascop_stock_itemmaster {$search} AND isMedicine = 0 order by {$sort} {$dir}";
            //$count = $db->getItemFromDB($countQuery);
            //$total = "select SUM(stit_MRP) AS total_mrp,SUM(stit_GST) as tax_total "
            //   . " from " . FINASCOP_DB . "finascop_stock_itemmaster  order by stit_ID desc";
            //$coltotal = $db->getFromDB($total, true);
            /*$qry = "SELECT  pdt_brand,stit_ID AS ItemId ,product_is_home,stit_displaylabel, stit_itemName ,stit_status,least_package_type_id,least_package_type_name,product_category,
            stit_HSN_code,stit_package_type_namme,stit_category_name,stit_brand_name,stit_product_variant,stit_quantity,stit_GST AS tax, stit_SKU,
            finascop_stock_itemmaster.createdOn AS createdOn,CONCAT(FirstName,'',LastName) AS createdBy,IF(stit_status = 1,'Active','Inactive') AS statusName,
            IF((isVerified = 1),'Yes','No') AS isVerified, taxValueId,
            @miCount:=(SELECT COUNT(1) FROM finascop_stock_item_images WHERE `product_id`= stit_ID AND `image_type` = 1 ), 
            @aiCount:=(SELECT COUNT(1) FROM finascop_stock_item_images WHERE `product_id`= stit_ID AND `image_type` = 0),
            IF(mrp.mrps> 0,'Yes','No') AS hasMrp,IF(exportProduct.exportedPrdcts> 0,'Yes','No') AS isExported,
            CONCAT(@miCount, '/',@aiCount) AS imgCount,itemProcessingTime
            FROM finascop_stock_itemmaster LEFT JOIN finascop_usr_profile ON UserId = createdBy 
            LEFT JOIN (SELECT stit_id AS stitid, COUNT(*) AS mrps FROM item_mrp GROUP BY stit_id ) mrp ON mrp.stitid = finascop_stock_itemmaster.stit_ID 
            LEFT JOIN (SELECT product_stitId AS stitid, COUNT(*) AS exportedPrdcts FROM product_grozeo_map GROUP BY product_stitId ) exportProduct ON exportProduct.stitid = finascop_stock_itemmaster.stit_ID
            WHERE isMedicine = 0 ";*/
            $qry = "SELECT 
    pdt_brand,
    stit_ID AS ItemId,
    product_is_home,
    stit_displaylabel,
    stit_itemName,
    stit_status,
    least_package_type_id,
    least_package_type_name,
    product_category,
    stit_HSN_code,
    stit_package_type_namme,
    stit_category_name,
    stit_brand_name,
    stit_product_variant,
    stit_quantity,
    stit_GST AS tax, 
    stit_SKU,
    finascop_stock_itemmaster.createdOn AS createdOn,
    CONCAT(FirstName, '', LastName) AS createdBy,
    IF(stit_status = 1, 'Active', 'Inactive') AS statusName,
    IF(isVerified = 1, 'Yes', 'No') AS isVerified,
    taxValueId,
    COALESCE(miCount.miCount, 0) AS miCount,  -- Count of images of type 1
    COALESCE(aiCount.aiCount, 0) AS aiCount,  -- Count of images of type 0
    IF(mrp.mrps > 0, 'Yes', 'No') AS hasMrp,
    IF(exportProduct.exportedPrdcts > 0, 'Yes', 'No') AS isExported,
    CONCAT(COALESCE(miCount.miCount, 0), '/', COALESCE(aiCount.aiCount, 0)) AS imgCount,
    itemProcessingTime
FROM 
    finascop_stock_itemmaster 
LEFT JOIN 
    finascop_usr_profile ON UserId = createdBy 
LEFT JOIN 
    (SELECT stit_id AS stitid, COUNT(*) AS mrps FROM item_mrp GROUP BY stit_id) mrp 
    ON mrp.stitid = finascop_stock_itemmaster.stit_ID 
LEFT JOIN 
    (SELECT product_stitId AS stitid, COUNT(*) AS exportedPrdcts FROM product_grozeo_map GROUP BY product_stitId) exportProduct 
    ON exportProduct.stitid = finascop_stock_itemmaster.stit_ID
LEFT JOIN 
    (SELECT product_id, COUNT(*) AS miCount FROM finascop_stock_item_images WHERE image_type = 1 GROUP BY product_id) miCount 
    ON miCount.product_id = finascop_stock_itemmaster.stit_ID
LEFT JOIN 
    (SELECT product_id, COUNT(*) AS aiCount FROM finascop_stock_item_images WHERE image_type = 0 GROUP BY product_id) aiCount 
    ON aiCount.product_id = finascop_stock_itemmaster.stit_ID
WHERE 
    isMedicine = 0";
            //$count = $db->getItemFromDB("{$qry} {$search} order by {$sort} {$dir}");
            $count = $db->getItemFromDB("SELECT COUNT(*) FROM ({$qry}) AS countItem   {$search} order by {$sort} {$dir}");
            $listQuery = "SELECT * FROM ({$qry}) AS listItem   {$search} order by {$sort} {$dir} limit {$rec_start},{$rec_limit}";
            //$listQuery = "{$qry} {$search} order by {$sort} {$dir} limit {$rec_start},{$rec_limit} ";
            $data = $db->getMultipleData($listQuery, true);
            /*$result = [];
            foreach ($data as $key => $value) {
                //foreach ($coltotal as $k => $v) {
                // $value[$k] = $v;
                $result[$key] = $value;
                // }
            }*/
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":0,"data":[]}';
        }



        break;
    case 'gethsnStore':
        $search_hint = $_POST['query'];
        if (!empty($search_hint))
            $qry = "select hsn_id,hsn_code,gst_percent from " . FINASCOP_DB . "finascop_hsn WHERE hsn_code LIKE '{$search_hint}%' AND hsn_code IS NOT NULL order by hsn_code";
        else
            $qry = "select hsn_id,hsn_code,gst_percent from " . FINASCOP_DB . "finascop_hsn WHERE hsn_code IS NOT NULL  order by hsn_code";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getItemMasterStockGroups':


        $qry = "select stgp_groupID AS group_id ,stgp_groupName AS group_name,stgp_fqGroupName AS parent_group"
            . " from " . FINASCOP_DB . "finascop_stock_group where stgp_isLeaf = 1 ";


        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getItemMaster_EditData':

        $id = $_POST['id'];
        $qry = "select gtin,stit_SKU,stit_ID as itemId,stit_itemId as item ,stit_HSNCode as HSN,stit_GST as GST,taxValueId,stit_hsnId,stit_MRP as MRP, stgp_groupID as itemgroup,stit_displaylabel,stit_product_variant,stit_itemReturnTime,stit_custInitiate,"
            . "stit_package_type_namme ,pdt_package_type_id,product_category,pdt_brand,featured,pdt_sale_rate,popular,item_length,item_breadth,item_height,item_weight,stit_qty,stit_unit,"
            . "cos_nos,cos_package_type_id,cos_package_type_name,cosb_package_type_id,cosb_package_type_name,cos_length,cos_length,cos_breadth,cos_height,cos_weight,"
            . "ccs_nos,ccs_package_type_id,ccs_package_type_name,ccsb_package_type_id,ccsb_package_type_name,ccs_length,ccs_breadth,ccs_height,ccs_weight,"
            . "rs_nos,rs_package_type_id,rs_package_type_name,rsb_package_type_id,rsb_package_type_name,rs_length,rs_breadth,rs_height,rs_weight,"
            . "cs_nos,cs_package_type_id,cs_package_type_name,csb_package_type_id,csb_package_type_name,cs_length,cs_breadth,cs_height,cs_weight,"
            . "ds_nos,ds_package_type_id,ds_package_type_name,dsb_package_type_id,dsb_package_type_name,ds_length,ds_breadth,ds_height,ds_weight,courierDelivery,directDelivery,directPurchase,stit_foodtype,stit_orgin_country,"
            . "(SELECT country_name FROM finascop_country WHERE country_id = stit_orgin_country) as orgCountryName,isRRPApplicable,stit_ingredients,stit_preparation_use,stit_allergens,stit_nutritionlabel,"
            . "(SELECT GROUP_CONCAT(stitc_country) FROM finascop_stock_itemmaster_country WHERE stitc_stit_id = stit_ID) AS stit_productsFor,"
            //."(select stgp_groupName from ".FINASCOP_DB."finascop_stock_group b where b.stgp_groupID = a.stgp_groupID)as groups,"
            . " stit_Description as description,stit_long_description,stit_quantity,stit_HSN_code,stit_brand_name,stit_category_name,stit_itemName,stitl1_optimumqty,stitl2_optimumqty,stitl3_optimumqty,"
            . "stit11_minimumqty,stit12_minimumqty,stit13_minimumqty,stit11_maximumqty,stit12_maximumqty,stit13_maximumqty,stii_csb,stii_csbretail,isMedicine,least_package_type_id,least_package_type_name,"
            . "stit_package_type_id,stit_stdPacking,stdpckl11_package_type_id,stdpckl1_nos,stdpckl12_package_type_id,stdpckl21_package_type_id,stdpckl2_nos,stdpckl22_package_type_id,stdpckl31_package_type_id,
stdpckl3_nos,stdpckl32_package_type_id,stdpckl41_package_type_id,stdpckl4_nos,stdpckl42_package_type_id,stit_salesUnit,stit_storage_instruction,stit_safety_warning,stit_warning,itemProcessingTime,
(SELECT category_name FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category)) AS main_categoryName,(SELECT parent_category FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category)) AS parent_categorysc,(SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category))) AS parent_categoryname,(SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category))) AS primary_businessTypesc,(SELECT business_type_name FROM finascop_business_type WHERE business_type_id = (SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category)))) AS btName,stit_package_master,stit_courierWt,(SELECT rpckm_name FROM retaline_package_master WHERE rpckm_id = stit_package_master) as rpckm_name 
 from " . FINASCOP_DB . "finascop_stock_itemmaster  where stit_ID = '$id' ";
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'saveItemMaster':

        $imageOptions = 3;
        $imageOptions = $_POST['imageOptions'];
        //stit_qty,stit_unit
        //$SKU = $_POST['stit_category_name'] . " " . $_POST['stit_brand_name'] . " " . $_POST['item_name'] . " " . $_POST['stit_product_variant'] . " " . $_POST['stit_quantity'];
        //On 01-10-2020
        //$SKU = $_POST['stit_brand_name'] . " " . $_POST['item_name'] . " " . $_POST['stit_product_variant'] . " " . $_POST['stit_quantity']. " " . $_POST['least_package_type_name'];
        $SKU = $_POST['stit_SKU'];
        $dSKU = addslashes($SKU);
        $manufactureId = $db->getItemSafe("SELECT manufacture_id FROM mypha_productbrands WHERE brand_id = ?", "s", [$_POST['pdt_brand']]);
        $manufactureDetails = $db->getFromDB("SELECT manufacture_id,manufacture_name FROM mypha_productmanufacture WHERE manufacture_id = $manufactureId", true);

        $stit_productsFor = $_POST['stit_productsFor'];
        $stit_productsForCountries = explode(',', $stit_productsFor);
        //stit_package_type_id,stit_stdPacking,stdpckl11_package_type_id,stdpckl1_nos,stdpckl12_package_type_id,stdpckl21_package_type_id,stdpckl2_nos,stdpckl22_package_type_id,stdpckl31_package_type_id,
        //stdpckl3_nos,stdpckl32_package_type_id,stdpckl41_package_type_id,stdpckl4_nos,stdpckl42_package_type_id,stit_salesUnit
        $stit_hsnId = $db->getItemSafe("SELECT hsn_id FROM finascop_hsn WHERE hsn_code = ?", "s", [$_POST['HSN_code']]);
        $data = array(
            "stit_stdPacking" => ($_POST['stit_stdPacking'] > 0 ? $_POST['stit_stdPacking'] : 0),
            "stit_salesUnit" => ($_POST['stit_salesUnit'] > 0 ? $_POST['stit_salesUnit'] : 0),
            "stit_package_type_id" => ($_POST['stit_package_type_id'] > 0 ? $_POST['stit_package_type_id'] : 0),
            "stdpckl11_package_type_id" => ($_POST['stdpckl11_package_type_id'] > 0 ? $_POST['stdpckl11_package_type_id'] : 0),
            "stdpckl1_nos" => ($_POST['stdpckl1_nos'] > 0 ? $_POST['stdpckl1_nos'] : 0),
            "stdpckl12_package_type_id" => ($_POST['stdpckl12_package_type_id'] > 0 ? $_POST['stdpckl12_package_type_id'] : 0),
            "stdpckl21_package_type_id" => ($_POST['stdpckl21_package_type_id'] > 0 ? $_POST['stdpckl21_package_type_id'] : 0),
            "stdpckl2_nos" => ($_POST['stdpckl2_nos'] > 0 ? $_POST['stdpckl2_nos'] : 0),
            "stdpckl22_package_type_id" => ($_POST['stdpckl22_package_type_id'] > 0 ? $_POST['stdpckl22_package_type_id'] : 0),
            "stdpckl31_package_type_id" => ($_POST['stdpckl31_package_type_id'] > 0 ? $_POST['stdpckl31_package_type_id'] : 0),
            "stdpckl3_nos" => ($_POST['stdpckl3_nos'] > 0 ? $_POST['stdpckl3_nos'] : 0),
            "stdpckl32_package_type_id" => ($_POST['stdpckl32_package_type_id'] > 0 ? $_POST['stdpckl32_package_type_id'] : 0),
            "stdpckl41_package_type_id" => ($_POST['stdpckl41_package_type_id'] > 0 ? $_POST['stdpckl41_package_type_id'] : 0),
            "stdpckl4_nos" => ($_POST['stdpckl4_nos'] > 0 ? $_POST['stdpckl4_nos'] : 0),
            "stdpckl42_package_type_id" => ($_POST['stdpckl42_package_type_id'] > 0 ? $_POST['stdpckl42_package_type_id'] : 0),
            "stit_itemId" => ($_POST['item'] > 0 ? $_POST['item'] : 0),
            "stit_SKU" => $SKU,
            "stit_itemERPId" => ($_POST['stit_itemERPId'] > 0 ? trim($_POST['stit_itemERPId']) : 0),
            "stit_itemBarcode" => ($_POST['stit_itemBarcode'] > 0 ? $_POST['stit_itemBarcode'] : 0),
            "stit_itemReturnTime" => ($_POST['stit_itemReturnTime'] > 0) ? $_POST['stit_itemReturnTime'] : 0,
            "stit_custInitiate" => ($_POST['stit_custInitiate'] > 0 ? $_POST['stit_custInitiate'] : 0),
            "stit_HSNCode" => ($_POST['HSN'] > 0 ? $_POST['HSN'] : 0),
            "stit_hsnId" => $stit_hsnId,
            "stit_GST" => ($_POST['GST'] > 0 ? $_POST['GST'] : 0),
            "taxValueId" => ($_POST['taxValueId'] > 0 ? $_POST['taxValueId'] : 0),
            "stit_displaylabel" => (!empty($_POST['display_label']) ? $_POST['display_label'] : ''),
            "itemProcessingTime" => (!empty($_POST['itemProcessingTime']) ? $_POST['itemProcessingTime'] : ''),
            "stit_Description" => (!empty($_POST['description']) ? $_POST['description'] : ''),
            "stit_product_variant" => (!empty($_POST['stit_product_variant']) ? $_POST['stit_product_variant'] : ''),
            "product_category" => ($_POST['product_category'] > 0 ? $_POST['product_category'] : 0),
            "pdt_brand" => ($_POST['pdt_brand'] > 0 ? $_POST['pdt_brand'] : 0),
            "featured" => ($_POST['featured'] > 0 ? $_POST['featured'] : 0),
            "popular" => ($_POST['popular'] > 0 ? $_POST['popular'] : 0),
            "med_manufactureid" => ($manufactureDetails['manufacture_id'] > 0 ? $manufactureDetails['manufacture_id'] : 0),
            "med_manufacturename" => (!empty($manufactureDetails['manufacture_name']) ? $manufactureDetails['manufacture_name'] : ''),
            "stit_long_description" => (!empty($_POST['stit_long_description']) ? $_POST['stit_long_description'] : ''),
            "stit_quantity" => (!empty($_POST['stit_quantity']) ? $_POST['stit_quantity'] : ''),
            "stit_itemName" => (!empty($_POST['item_name']) ? $_POST['item_name'] : ''),
            "stit_HSN_code" => (!empty($_POST['HSN_code']) ? $_POST['HSN_code'] : ''),
            "stit_package_type_namme" => (!empty($_POST['stit_package_type_namme']) ? $_POST['stit_package_type_namme'] : ''),
            "stit_category_name" => (!empty($_POST['stit_category_name']) ? $_POST['stit_category_name'] : ''),
            "stit_brand_name" => (!empty($_POST['stit_brand_name']) ? $_POST['stit_brand_name'] : ''),
            "stitl1_optimumqty" => ($_POST['stitl1_optimumqty'] > 0 ? $_POST['stitl1_optimumqty'] : 0),
            "stitl2_optimumqty" => ($_POST['stitl2_optimumqty'] > 0 ? $_POST['stitl2_optimumqty'] : 0),
            "stitl3_optimumqty" => ($_POST['stitl3_optimumqty'] > 0 ? $_POST['stitl3_optimumqty'] : 0),
            "stit11_minimumqty" => ($_POST['stit11_minimumqty'] > 0 ? $_POST['stit11_minimumqty'] : 0),
            "stit12_minimumqty" => ($_POST['stit12_minimumqty'] > 0 ? $_POST['stit12_minimumqty'] : 0),
            "stit13_minimumqty" => ($_POST['stit13_minimumqty'] > 0 ? $_POST['stit13_minimumqty'] : 0),
            "stit11_maximumqty" => ($_POST['stit11_maximumqty'] > 0 ? $_POST['stit11_maximumqty'] : 0),
            "stit12_maximumqty" => ($_POST['stit12_maximumqty'] > 0 ? $_POST['stit12_maximumqty'] : 0),
            "stit13_maximumqty" => ($_POST['stit13_maximumqty'] > 0 ? $_POST['stit13_maximumqty'] : 0),
            "stii_csb" => ($_POST['stii_csb'] > 0 ? $_POST['stii_csb'] : 0),
            "stii_csbretail" => ($_POST['stii_csbretail'] > 0 ? $_POST['stii_csbretail'] : 0),
            "least_package_type_id" => ($_POST['least_package_type_id'] > 0 ? $_POST['least_package_type_id'] : 0),
            "least_package_type_name" => (!empty($_POST['least_package_type_name']) ? $_POST['least_package_type_name'] : ''),
            "isMedicine" => 0,
            "cosb_package_type_id" => ($_POST['cosb_package_type_id'] > 0 ? $_POST['cosb_package_type_id'] : 0),
            "cosb_package_type_name" => (!empty($_POST['cosb_package_type_name']) ? $_POST['cosb_package_type_name'] : ''),
            "cos_length" => ($_POST['cos_length'] > 0 ? $_POST['cos_length'] : 0),
            "cos_breadth" => ($_POST['cos_breadth'] > 0 ? $_POST['cos_breadth'] : 0),
            "cos_height" => ($_POST['cos_height'] > 0 ? $_POST['cos_height'] : 0),
            "cos_weight" => ($_POST['cos_weight'] > 0 ? $_POST['cos_weight'] : 0),
            "cos_volume" => ($_POST['cos_volume'] > 0 ? $_POST['cos_volume'] : 0),
            "ccsb_package_type_id" => ($_POST['ccsb_package_type_id'] > 0 ? $_POST['ccsb_package_type_id'] : 0),
            "ccsb_package_type_name" => (!empty($_POST['ccsb_package_type_name']) ? $_POST['ccsb_package_type_name'] : ''),
            "ccs_length" => ($_POST['ccs_length'] > 0 ? $_POST['ccs_length'] : 0),
            "ccs_breadth" => ($_POST['ccs_breadth'] > 0 ? $_POST['ccs_breadth'] : 0),
            "ccs_height" => ($_POST['ccs_height'] > 0 ? $_POST['ccs_height'] : 0),
            "ccs_weight" => ($_POST['ccs_weight'] > 0 ? $_POST['ccs_weight'] : 0),
            "ccs_volume" => ($_POST['ccs_volume'] > 0 ? $_POST['ccs_volume'] : 0),
            "rsb_package_type_id" => ($_POST['ccsb_package_type_id'] > 0 ? $_POST['ccsb_package_type_id'] : 0),
            "rsb_package_type_name" => (!empty($_POST['ccsb_package_type_name']) ? $_POST['ccsb_package_type_name'] : ''),
            "rs_length" => ($_POST['rs_length'] > 0 ? $_POST['rs_length'] : 0),
            "rs_breadth" => ($_POST['rs_breadth'] > 0 ? $_POST['rs_breadth'] : 0),
            "rs_height" => ($_POST['rs_height'] > 0 ? $_POST['rs_height'] : 0),
            "rs_weight" => ($_POST['rs_weight'] > 0 ? $_POST['rs_weight'] : 0),
            "rs_volume" => ($_POST['rs_volume'] > 0 ? $_POST['rs_volume'] : 0),
            "csb_package_type_id" => ($_POST['csb_package_type_id'] > 0 ? $_POST['csb_package_type_id'] : 0),
            "csb_package_type_name" => (!empty($_POST['csb_package_type_name']) ? $_POST['csb_package_type_name'] : ''),
            "cs_length" => ($_POST['cs_length'] > 0 ? $_POST['cs_length'] : 0),
            "cs_breadth" => ($_POST['cs_breadth'] > 0 ? $_POST['cs_breadth'] : 0),
            "cs_height" => ($_POST['cs_height'] > 0 ? $_POST['cs_height'] : 0),
            "cs_weight" => ($_POST['cs_weight'] > 0 ? $_POST['cs_weight'] : 0),
            "cs_volume" => ($_POST['cs_volume'] > 0 ? $_POST['cs_volume'] : 0),
            "dsb_package_type_id" => ($_POST['dsb_package_type_id'] > 0 ? $_POST['dsb_package_type_id'] : 0),
            "dsb_package_type_name" => (!empty($_POST['dsb_package_type_name']) ? $_POST['dsb_package_type_name'] : ''),
            "ds_length" => ($_POST['ds_length'] > 0 ? $_POST['ds_length'] : 0),
            "ds_breadth" => ($_POST['ds_breadth'] > 0 ? $_POST['ds_breadth'] : 0),
            "ds_height" => ($_POST['ds_height'] > 0 ? $_POST['ds_height'] : 0),
            "ds_weight" => ($_POST['ds_weight'] > 0 ? $_POST['ds_weight'] : 0),
            "ds_volume" => ($_POST['ds_volume'] > 0 ? $_POST['ds_volume'] : 0),
            "courierDelivery" => ($_POST['courierDelivery'] > 0 ? $_POST['courierDelivery'] : 0),
            "directDelivery" => ($_POST['directDelivery'] > 0 ? $_POST['directDelivery'] : 0),
            "directPurchase" => ($_POST['directPurchase'] > 0 ? $_POST['directPurchase'] : 0),
            "isRRPApplicable" => ($_POST['isRRPApplicable'] > 0 ? $_POST['isRRPApplicable'] : 0),
            "stit_foodtype" => ($_POST['stit_foodtype'] > 0 ? $_POST['stit_foodtype'] : 0),
            "stit_orgin_country" => ($_POST['stit_orgin_country'] > 0 ? $_POST['stit_orgin_country'] : 0),
            "stit_unit" => ($_POST['stit_unit'] > 0 ? $_POST['stit_unit'] : 0),
            "stit_qty" => ($_POST['stit_qty'] > 0 ? $_POST['stit_qty'] : 0),
            "stit_ingredients" => (!empty($_POST['stit_ingredients']) ? $_POST['stit_ingredients'] : ''),
            "stit_preparation_use" => (!empty($_POST['stit_preparation_use']) ? $_POST['stit_preparation_use'] : ''),
            "stit_allergens" => (!empty($_POST['stit_allergens']) ? $_POST['stit_allergens'] : ''),
            "stit_nutritionlabel" => (!empty($_POST['stit_nutritionlabel']) ? $_POST['stit_nutritionlabel'] : ''),
            "stit_warning" => (!empty($_POST['stit_warning']) ? $_POST['stit_warning'] : ''),
            "stit_safety_warning" => (!empty($_POST['stit_safety_warning']) ? $_POST['stit_safety_warning'] : ''),
            "stit_storage_instruction" => (!empty($_POST['stit_storage_instruction']) ? $_POST['stit_storage_instruction'] : ''),
            "stit_package_master" => (!empty($_POST['stit_package_master']) ? $_POST['stit_package_master'] : 0),
            "stit_courierWt" => (!empty($_POST['stit_courierWt']) ? $_POST['stit_courierWt'] : 0),
            "item_length" => (!empty($_POST['item_length']) ? $_POST['item_length'] : 0),
            "item_breadth" => (!empty($_POST['item_breadth']) ? $_POST['item_breadth'] : 0),
            "item_height" => (!empty($_POST['item_height']) ? $_POST['item_height'] : 0)

        );



        //$data = array_filter($data);

        $fsuidata['fsi_item_id'] = $data['stit_itemId'];
        $fsuidata['fsi_item_name'] = $data['stit_itemName'];
        $fsuidata['fsi_brand_id'] = $data['pdt_brand'];
        $fsuidata['fsi_brand_name'] = $data['stit_brand_name'];
        $fsuidata['fsi_category_id'] = $data['product_category'];
        $fsuidata['fsi_categry_name'] = $data['stit_category_name'];
        $fsuidata['fsi_variant'] = mysqli_real_escape_string($db->linker(), $data['stit_product_variant']);
        $fsuidata['fsi_displaylabel'] = $data['stit_displaylabel'];
        $fsuidata['isMedicine'] = 0;


        $itemName = $_POST['item'];

        if ($_POST['dupitem'] == 'D') {
            unset($_POST['id']);
        }
        $db->query('begin');
        if ($_POST['directPurchase'] == 0) {
            if (empty($_POST['stit_unit']) || empty($_POST['stit_qty'])) {
                // echo "{success: false, msg:'Enter Quantity and unit.'}";
                //exit;
            }
        }
        if ($_POST['least_package_type_id'] > 0) {
            $lptyp = " AND least_package_type_id = " . intval($_POST['least_package_type_id']) . " ";
        } else {
            $lptyp = " ";
        }
        if (empty($_POST['id'])) {

            $stit_fsiuid = updateUniqueItemTable(0, $fsuidata);
            $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];
            if ($data['stit_itemName'] == 'Private Product') {
                $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  
    WHERE stit_product_variant = '" . mysqli_real_escape_string($db->linker(), $_POST['stit_product_variant']) . "' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} AND isMedicine = 0 ");
                $matchinSKUs = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_SKU) FROM finascop_stock_itemmaster  
        WHERE stit_product_variant = '" . mysqli_real_escape_string($db->linker(), $_POST['stit_product_variant']) . "' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} AND isMedicine = 0 ");
            } else {
                $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  
            WHERE stit_itemId = '{$itemName}' AND  stit_product_variant = '" . mysqli_real_escape_string($db->linker(), $_POST['stit_product_variant']) . "' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} AND isMedicine = 0 ");
                $matchinSKUs = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_SKU) FROM finascop_stock_itemmaster  
        WHERE stit_itemId = '{$itemName}' AND  stit_product_variant = '" . mysqli_real_escape_string($db->linker(), $_POST['stit_product_variant']) . "' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} AND isMedicine = 0 ");
            }

            if ($IsItemNameUnique > 0 && $data['stit_itemName'] != 'Private Product') {

                $message = "This Item already existing.Matching SKU(s) are " . $matchinSKUs;
                echo "{success: false, msg: '{$message}'}";
                exit;
            }
            $SKUUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  WHERE stit_SKU = '{$dSKU}'  ");
            if ($SKUUnique > 0) {
                echo "{success: false, msg:'This SKU already existing.'}";
                exit;
            }
            //            $itemERPId = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  WHERE stit_itemERPId = '{$data['stit_itemERPId']}'  ");
            //            if ($itemERPId > 0) {
            //                echo "{success: false, msg:'This Item ERP Id already existing.'}";
            //                exit;
            //            }
            $data['createdOn'] = date('Y-m-d H:i:s');
            $data['createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform(FINASCOP_DB . "finascop_stock_itemmaster", $data);
            $itemIdFrPt = $db->insert_id();

            if ($stit_productsForCountries[0] > 0) {
                $status = $db->query("DELETE FROM finascop_stock_itemmaster_country WHERE stitc_stit_id = {$itemIdFrPt}");
                for ($i = 0; $i < count($stit_productsForCountries); $i++) {
                    $fsitcdata['stitc_stit_id'] = $itemIdFrPt;
                    $fsitcdata['stitc_country'] = $stit_productsForCountries[$i];
                    $status = $db->perform(FINASCOP_DB . "finascop_stock_itemmaster_country", $fsitcdata);
                }
            }
            $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM finascop_stock_itemmaster WHERE stit_fsiuid = {$stit_fsiuid['fsi_uid']}");
            if ($stit_fsiuid['status'] == 'NEW') {

                $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $uuit, 'update', " fsi_uid = {$stit_fsiuid['fsi_uid']}");
            }
            $message = 'Saved Successfully';
        } else {
            $itemIdFrPt = $_POST['id'];
            $fsiUid = $db->getItemFromDB("SELECT stit_fsiuid FROM " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID = {$_POST['id']}");
            $stit_fsiuid = updateUniqueItemTable($fsiUid, $fsuidata);
            $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];
            if ($data['stit_itemName'] == 'Private Product') {
                $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  
                WHERE stit_product_variant = '" . mysqli_real_escape_string($db->linker(), $_POST['stit_product_variant']) . "' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} AND isMedicine = 0 AND stit_ID <> {$_POST['id']}");
                $matchinSKUs = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_SKU) FROM finascop_stock_itemmaster  
                    WHERE stit_product_variant = '" . mysqli_real_escape_string($db->linker(), $_POST['stit_product_variant']) . "' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} AND isMedicine = 0 AND stit_ID <> {$_POST['id']} ");
            } else {
                $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  
                        WHERE stit_itemId = '{$itemName}' AND  stit_product_variant = '" . mysqli_real_escape_string($db->linker(), $_POST['stit_product_variant']) . "' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} AND isMedicine = 0 AND stit_ID <> {$_POST['id']}");
                $matchinSKUs = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_SKU) FROM finascop_stock_itemmaster  
                    WHERE stit_itemId = '{$itemName}' AND  stit_product_variant = '" . mysqli_real_escape_string($db->linker(), $_POST['stit_product_variant']) . "' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} AND isMedicine = 0 AND stit_ID <> {$_POST['id']}");
            }

            if ($IsItemNameUnique > 0 && $data['stit_itemName'] != 'Private Product') {

                $message = "This Item already existing.Matching SKU(s) are " . $matchinSKUs;
                echo "{success: false, msg: '{$message}'}";
                exit;
            }
            $SKUUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  WHERE stit_SKU = '{$dSKU}'  AND stit_ID <> {$_POST['id']}");
            if ($SKUUnique > 0) {
                echo "{success: false, msg:'This SKU already existing.'}";
                exit;
            }
            $con = 'stit_ID=' . intval($_POST['id']);
            $data['updatedOn'] = date('Y-m-d H:i:s');
            $data['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform(FINASCOP_DB . "finascop_stock_itemmaster", $data, 'update', $con);

            if ($stit_productsForCountries[0] > 0) {
                $status = $db->query("DELETE FROM finascop_stock_itemmaster_country WHERE stitc_stit_id = {$itemIdFrPt}");
                for ($i = 0; $i < count($stit_productsForCountries); $i++) {
                    $fsitcdata['stitc_stit_id'] = $itemIdFrPt;
                    $fsitcdata['stitc_country'] = $stit_productsForCountries[$i];
                    $status = $db->perform(FINASCOP_DB . "finascop_stock_itemmaster_country", $fsitcdata);
                }
            }
            $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM finascop_stock_itemmaster WHERE stit_fsiuid = {$stit_fsiuid['fsi_uid']}");
            if ($stit_fsiuid['status'] == 'NEW') {
                $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $uuit, 'update', " fsi_uid = {$stit_fsiuid['fsi_uid']}");
            }

            /* $fpodData['fpod_itemname'] = $dSKU;
            $status = $db->perform(FINASCOP_DB . "finascop_purchase_order_details", $fpodData, 'update', "fpod_itemid = " . intval($_POST['id']));

            $fsiiData['stii_itemmastername'] = $dSKU;
            $status = $db->perform(FINASCOP_DB . "finascop_stock_item_inventory", $fsiiData, 'update', "stii_itemmasterid = " . intval($_POST['id']));

            $fsiidData['stiid_itemmastername'] = $dSKU;
            $status = $db->perform(FINASCOP_DB . "finascop_stock_item_inventorydetails", $fsiidData, 'update', "stiid_itemmasterid = " . intval($_POST['id']));
            $message = "Updated Successfully";*/
        }
        //        $db->query('commit');
        //        $db->query('begin');
        $cosdpid = $db->getItemFromDB("SELECT IF(stdpckl11_package_type_id = cosb_package_type_id,'stdpckl11_package_type_id',IF(stdpckl21_package_type_id = cosb_package_type_id,'stdpckl21_package_type_id',"
            . "IF(stdpckl31_package_type_id = cosb_package_type_id,'stdpckl31_package_type_id','stdpckl41_package_type_id'))) AS cosdpid FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}");

        if ($cosdpid == 'stdpckl11_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl1_nos,stdpckl12_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            //print_r($cosDet);
            $packingdata['cos_nos'] = $cosDet['stdpckl1_nos'];
            $packingdata['cos_package_type_id'] = $cosDet['stdpckl12_package_type_id'];
            $packingdata['cos_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl12_package_type_id']}");
        } else if ($cosdpid == 'stdpckl21_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl2_nos,stdpckl22_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['cos_nos'] = $cosDet['stdpckl2_nos'];
            $packingdata['cos_package_type_id'] = $cosDet['stdpckl22_package_type_id'];
            $packingdata['cos_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl22_package_type_id']}");
        } else if ($cosdpid == 'stdpckl31_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl3_nos,stdpckl32_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['cos_nos'] = $cosDet['stdpckl3_nos'];
            $packingdata['cos_package_type_id'] = $cosDet['stdpckl32_package_type_id'];
            $packingdata['cos_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl32_package_type_id']}");
        } else if ($cosdpid == 'stdpckl41_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl4_nos,stdpckl42_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['cos_nos'] = $cosDet['stdpckl4_nos'];
            $packingdata['cos_package_type_id'] = $cosDet['stdpckl42_package_type_id'];
            $packingdata['cos_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl42_package_type_id']}");
        }
        //print_r($packingdata);exit();
        $ccsdpid = $db->getItemFromDB("SELECT IF(stdpckl11_package_type_id = ccsb_package_type_id,'stdpckl11_package_type_id',IF(stdpckl21_package_type_id = ccsb_package_type_id,'stdpckl21_package_type_id',"
            . "IF(stdpckl31_package_type_id = ccsb_package_type_id,'stdpckl31_package_type_id','stdpckl41_package_type_id'))) AS ccsdpid FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}");
        if ($ccsdpid == 'stdpckl11_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl1_nos,stdpckl12_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['ccs_nos'] = $cosDet['stdpckl1_nos'];
            $packingdata['ccs_package_type_id'] = $cosDet['stdpckl12_package_type_id'];
            $packingdata['ccs_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl12_package_type_id']}");
        } else if ($ccsdpid == 'stdpckl21_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl2_nos,stdpckl22_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['ccs_nos'] = $cosDet['stdpckl2_nos'];
            $packingdata['ccs_package_type_id'] = $cosDet['stdpckl22_package_type_id'];
            $packingdata['ccs_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl22_package_type_id']}");
        } else if ($ccsdpid == 'stdpckl31_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl3_nos,stdpckl32_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['ccs_nos'] = $cosDet['stdpckl3_nos'];
            $packingdata['ccs_package_type_id'] = $cosDet['stdpckl32_package_type_id'];
            $packingdata['ccs_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl32_package_type_id']}");
        } else if ($ccsdpid == 'stdpckl41_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl4_nos,stdpckl42_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['ccs_nos'] = $cosDet['stdpckl4_nos'];
            $packingdata['ccs_package_type_id'] = $cosDet['stdpckl42_package_type_id'];
            $packingdata['ccs_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl42_package_type_id']}");
        }
        $rssdpid = $db->getItemFromDB("SELECT IF(stdpckl11_package_type_id = rsb_package_type_id,'stdpckl11_package_type_id',IF(stdpckl21_package_type_id = rsb_package_type_id,'stdpckl21_package_type_id',"
            . "IF(stdpckl31_package_type_id = rsb_package_type_id,'stdpckl31_package_type_id','stdpckl41_package_type_id'))) AS rsdpid FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}");
        if ($rssdpid == 'stdpckl11_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl1_nos,stdpckl12_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['rs_nos'] = $cosDet['stdpckl1_nos'];
            $packingdata['rs_package_type_id'] = $cosDet['stdpckl12_package_type_id'];
            $packingdata['rs_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl12_package_type_id']}");
        } else if ($rssdpid == 'stdpckl21_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl2_nos,stdpckl22_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['rs_nos'] = $cosDet['stdpckl2_nos'];
            $packingdata['rs_package_type_id'] = $cosDet['stdpckl22_package_type_id'];
            $packingdata['rs_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl22_package_type_id']}");
        } else if ($rssdpid == 'stdpckl31_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl3_nos,stdpckl32_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['rs_package_type_id'] = $cosDet['stdpckl32_package_type_id'];
            $packingdata['rs_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl32_package_type_id']}");
        } else if ($rssdpid == 'stdpckl41_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl4_nos,stdpckl42_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['rs_nos'] = $cosDet['stdpckl4_nos'];
            $packingdata['rs_package_type_id'] = $cosDet['stdpckl42_package_type_id'];
            $packingdata['rs_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl42_package_type_id']}");
        }
        $csdpid = $db->getItemFromDB("SELECT IF(stdpckl11_package_type_id = csb_package_type_id,'stdpckl11_package_type_id',IF(stdpckl21_package_type_id = csb_package_type_id,'stdpckl21_package_type_id',"
            . "IF(stdpckl31_package_type_id = csb_package_type_id,'stdpckl31_package_type_id','stdpckl41_package_type_id'))) AS csdpid FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}");
        if ($csdpid == 'stdpckl11_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl1_nos,stdpckl12_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['cs_nos'] = $cosDet['stdpckl1_nos'];
            $packingdata['cs_package_type_id'] = $cosDet['stdpckl12_package_type_id'];
            $packingdata['cs_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl12_package_type_id']}");
        } else if ($csdpid == 'stdpckl21_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl2_nos,stdpckl22_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['cs_nos'] = $cosDet['stdpckl2_nos'];
            $packingdata['cs_package_type_id'] = $cosDet['stdpckl22_package_type_id'];
            $packingdata['cs_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl22_package_type_id']}");
        } else if ($csdpid == 'stdpckl31_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl3_nos,stdpckl32_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['cs_nos'] = $cosDet['stdpckl3_nos'];
            $packingdata['cs_package_type_id'] = $cosDet['stdpckl32_package_type_id'];
            $packingdata['cs_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl32_package_type_id']}");
        } else if ($csdpid == 'stdpckl41_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl4_nos,stdpckl42_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['cs_nos'] = $cosDet['stdpckl4_nos'];
            $packingdata['cs_package_type_id'] = $cosDet['stdpckl42_package_type_id'];
            $packingdata['cs_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl42_package_type_id']}");
        }
        $dsdpid = $db->getItemFromDB("SELECT IF(stdpckl11_package_type_id = dsb_package_type_id,'stdpckl11_package_type_id',IF(stdpckl21_package_type_id = dsb_package_type_id,'stdpckl21_package_type_id',"
            . "IF(stdpckl31_package_type_id = dsb_package_type_id,'stdpckl31_package_type_id','stdpckl41_package_type_id'))) AS csdpid FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}");
        if ($dsdpid == 'stdpckl11_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl1_nos,stdpckl12_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['ds_nos'] = $cosDet['stdpckl1_nos'];
            $packingdata['ds_package_type_id'] = $cosDet['stdpckl12_package_type_id'];
            $packingdata['ds_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl12_package_type_id']}");
        } else if ($dsdpid == 'stdpckl21_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl2_nos,stdpckl22_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['ds_nos'] = $cosDet['stdpckl2_nos'];
            $packingdata['ds_package_type_id'] = $cosDet['stdpckl22_package_type_id'];
            $packingdata['ds_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl22_package_type_id']}");
        } else if ($dsdpid == 'stdpckl31_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl3_nos,stdpckl32_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['ds_nos'] = $cosDet['stdpckl3_nos'];
            $packingdata['ds_package_type_id'] = $cosDet['stdpckl32_package_type_id'];
            $packingdata['ds_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl32_package_type_id']}");
        } else if ($dsdpid == 'stdpckl41_package_type_id') {
            $cosDet = $db->getFromDB("SELECT stdpckl4_nos,stdpckl42_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemIdFrPt}", true);
            $packingdata['ds_nos'] = $cosDet['stdpckl4_nos'];
            $packingdata['ds_package_type_id'] = $cosDet['stdpckl42_package_type_id'];
            $packingdata['ds_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$cosDet['stdpckl42_package_type_id']}");
        }
        $condit = 'stit_ID=' . intval($itemIdFrPt);
        $packingdata['updatedOn'] = date('Y-m-d H:i:s');
        $packingdata['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform(FINASCOP_DB . "finascop_stock_itemmaster", $packingdata, 'update', $condit);
        if (!empty($_POST['stit_itemBarcode'])) {
            $fstrd['fsipc_isCompany'] = 1;
            $fstrd['fsipc_storeGroup'] = 0;
            $fstrd['fsipc_store'] = 0;
            $fstrd['fsipc_codeType'] = 'Company Barcode';
            $fstrd['fsipc_stit_id'] = $itemIdFrPt;
            $fstrd['fsipc_code'] = $_POST['stit_itemBarcode'];
            $isGlobal = $db->getItemSafe("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id = '{$fstrd['fsipc_stit_id']}' AND fsipc_isCompany = 1 AND fsipc_code = ?", "s", [$_POST['stit_itemBarcode']]);
            $isGlobalCodeExist = $db->getItemSafe("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id <> '{$fstrd['fsipc_stit_id']}' AND fsipc_isCompany = 1 AND fsipc_code = ?", "s", [$_POST['stit_itemBarcode']]);

            $fstrd['fsipc_createdOn'] = date("Y-m-d H:i");
            $fstrd['fsipc_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            //print_r($fstrd);
            if ($isGlobal == 0 && $isGlobalCodeExist == 0)
                $status = $db->perform('finascop_stock_itemmaster_product_codes', $fstrd);
            else
                $message .= "Code already exists.";
        }


        if ($imageOptions == '2') {
            $images = [];

            for ($i = 1; $i <= 5; $i++) {
                $postKey = 'imgurl' . $i;
                if (!empty($_POST[$postKey])) {
                    $images[] = strtok($_POST[$postKey], '?');
                }
            }

            if (!empty($images)) {
                $s3upload = new cgoS3FileHandler();
                $cloudFrontPath = 'products/';
                $documentRoot = $_SERVER["DOCUMENT_ROOT"];
                $tmpDir = $documentRoot . '/tmp/importimages/';

                if (!is_dir($tmpDir)) {
                    if (!mkdir($tmpDir, 0777, true)) { // Recursive and permissive for temp files
                        error_log("Failed to create temporary directory: " . $tmpDir);
                        return;
                    }
                }

                foreach ($images as $imageUrl) {
                    if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                        error_log("Invalid image URL detected: " . $imageUrl);
                        continue;
                    }

                    $fileExtension = pathinfo(basename($imageUrl), PATHINFO_EXTENSION);
                    $fileuploadname = md5(uniqid(rand(), true)) . "." . $fileExtension;
                    $destinationPath = $tmpDir . $fileuploadname;

                    if (@copy($imageUrl, $destinationPath)) {
                        $isFileUploaded = $s3upload->putFileToS3($cloudFrontPath, AWSBUCKETUPLOADS, $destinationPath, $fileuploadname);

                        if ($isFileUploaded) {
                            $imdata = [
                                "product_id" => $itemIdFrPt,
                                "image_url" => $fileuploadname, // Store the filename on S3
                                "image_thumb_url" => '', // Assuming this is handled elsewhere or not used
                                "image_type" => 1,
                                "bucket_name" => AWSBUCKETUPLOADS, // Store the bucket name for future reference
                                "createdBy" => $_SESSION['admin']->Finascop_UserId,
                                "created_at" => date("Y-m-d H:i:s"),
                                "image_folder" => 'products/' // Store the folder on S3
                            ];

                            try {
                                $status = $db->perform('finascop_stock_item_images', $imdata);
                                if (!$status) {
                                    error_log("Failed to insert image data into DB for product_id: {$itemIdFrPt}, filename: {$fileuploadname}");
                                }
                            } catch (Exception $e) {
                                error_log("Database error inserting image data for product_id {$itemIdFrPt}: " . $e->getMessage());
                            }
                        } else {
                            error_log("S3 upload failed for image: {$imageUrl} to {$cloudFrontPath}{$fileuploadname}");
                        }

                        if (file_exists($destinationPath)) {
                            unlink($destinationPath);
                        }
                    } else {
                        error_log("Failed to copy image from URL: {$imageUrl} to local path: {$destinationPath}");
                    }
                }
            }
        }


        $db->query('commit');
        if ($status) {
            echo "{success: true,imageOptions:{$imageOptions},stit_ID:{$itemIdFrPt},msg:'{$message}'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data' }";
        }

        break;
    case 'statusChange':
        $itemId = $_POST['stit_id'];
        $status = $_POST['status'];
        if ($status == 1) {
            $data['stit_status'] = 0;
        } else {
            $data['stit_status'] = 1;
        }
        $db->query('begin');
        if ($itemId > 0) {
            $con = ' stit_ID =' . intval($itemId);
            $data['updatedOn'] = date('Y-m-d H:i:s');
            $data['updatedBy'] = $_SESSION['admin']->Finascop_UserId;

            $status = $db->perform(FINASCOP_DB . "finascop_stock_itemmaster", $data, 'update', $con);
            $message = "Status Changed.";
        }

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Status Changed.'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data' }";
        }
        break;
    case 'addCodesToStore':
        $stit_isAllStores = $_POST['stit_isAllStores'];
        $validity = 0;
        if ($stit_isAllStores == 1) {
            $fstrd['fsipc_isIndividual'] = 0;
            $fstrd['fsipc_store'] = 0;
        } else {
            $fstrd['fsipc_isIndividual'] = 1;
            $fstrd['fsipc_store'] = $_POST['stit_store'];
        }
        if ($_POST['stit_codeType'] == 'Company Barcode') {
            $fstrd['fsipc_isCompany'] = 1;
            $fstrd['fsipc_storeGroup'] = 0;
            $fstrd['fsipc_store'] = 0;
        } else {
            $fstrd['fsipc_isCompany'] = 0;
            $fstrd['fsipc_storeGroup'] = $_POST['stit_storeGroup'];
        }
        $fstrd['fsipc_stit_id'] = $_POST['stit_MasterID'];
        $fstrd['fsipc_code'] = $_POST['stit_code'];
        $fstrd['fsipc_codeType'] = $_POST['stit_codeType'];

        $isGlobal = $db->getItemSafe("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id = '{$fstrd['fsipc_stit_id']}' AND fsipc_isCompany = 1 AND fsipc_code = ?", "s", [$_POST['stit_code']]);
        $isGlobalCodeExist = $db->getItemSafe("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id <> '{$fstrd['fsipc_stit_id']}' AND fsipc_isCompany = 1 AND fsipc_code = ?", "s", [$_POST['stit_code']]);
        if ($_POST['stit_storeGroup'] > 0 && $fstrd['fsipc_isIndividual'] = 0) {
            $isStoreGlobal = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id = '{$fstrd['fsipc_stit_id']}' "
                . "AND fsipc_isCompany = 0 AND fsipc_storeGroup = {$_POST['stit_storeGroup']} AND fsipc_isIndividual = 0 AND fsipc_code = '{$_POST['stit_code']}'");
            $isStoreGlobalCodeExist = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id <> '{$fstrd['fsipc_stit_id']}' "
                . "AND fsipc_isCompany = 0 AND fsipc_storeGroup = {$_POST['stit_storeGroup']} AND fsipc_isIndividual = 0 AND fsipc_code = '{$_POST['stit_code']}'");
        }
        if ($_POST['stit_storeGroup'] > 0 && $_POST['stit_store'] > 0) {
            $isCodeinStore = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id = '{$fstrd['fsipc_stit_id']}' "
                . "AND fsipc_isCompany = 0 AND fsipc_storeGroup = {$_POST['stit_storeGroup']} AND fsipc_isIndividual = 1 AND fsipc_store = {$_POST['stit_store']} AND fsipc_code = '{$_POST['stit_code']}'");
            $codeCodeAlreadyExist = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id <> '{$fstrd['fsipc_stit_id']}' "
                . "AND fsipc_isCompany = 0 AND fsipc_storeGroup = {$_POST['stit_storeGroup']} AND fsipc_isIndividual = 1 AND fsipc_store = {$_POST['stit_store']} AND fsipc_code = '{$_POST['stit_code']}'");
        }

        if ($codeCodeAlreadyExist > 0 || $isStoreGlobalCodeExist > 0 || $isGlobalCodeExist > 0) {
            $msg = "'Code already exist for another Item.'";
            echo '{"success":true,"valid":false,"msg":' . $msg . '}';
            exit();
        }

        //echo '$isGlobal'.$isGlobal;
        //echo '$isStoreGlobal'.$isStoreGlobal;
        //echo '$isCodeinStore'.$isCodeinStore;
        $db->query('begin');
        if ($isStoreGlobal > 0 || $isGlobal > 0 || $isCodeinStore > 0) {
            $msg = "'Code already added.'";
            echo '{"success":true,"valid":false,"msg":' . $msg . '}';
            exit();
        }
        //if ($isStoreGlobal == 0 && $isGlobal == 0 && $isCodeinStore == 0) {

        if ($_POST['stit_storeGroup'] > 0) {

            if ($stit_isAllStores > 0) {
                $validity = 1;
            }
            if ($_POST['stit_store'] > 0) {
                $validity = 1;
            }
            if ($validity == 0) {
                $msg = "'Enter store or set code for all stores'";
                echo '{"success":true,"valid":false,"msg":' . $msg . '}';
                exit();
            }
        }
        $fstrd['fsipc_createdOn'] = date("Y-m-d H:i");
        $fstrd['fsipc_createdBy'] = $_SESSION['admin']->Finascop_UserId;
        //print_r($fstrd);
        $status = $db->perform('finascop_stock_itemmaster_product_codes', $fstrd);
        $fsipcs['fsipc_id'] = $db->insert_id();
        if ($stit_isAllStores == 0) {
            $fsipcs['fsipc_stit_id'] = $fstrd['fsipc_stit_id'];
            $fsipcs['fsipcs_store'] = $fstrd['fsipc_store'];
            $fsipcs['fsipcs_Code'] = $fstrd['fsipc_code'];
            $status = $db->perform('finascop_stock_itemmaster_product_code_stores', $fsipcs);
        }
        //}
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Items added to request.'";
            echo '{"success":true,"valid":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error occured while saving.'";
            echo '{"success":false,"valid":false,"msg":' . $msg . '}';
        }
        break;
    case 'listProductCodeItemStore':
        $itemId = $_POST['itemId'];
        $rec_sort = empty($data['sort']) ? 'fsipc_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = " 1=1 AND fsipc_stit_id = {$itemId} ";

        if (isset($data['filter'])) {
        $allowedFields = ['item_id', 'item_name', 'item_code', 'item_composition', 'item_brand', 'item_category', 'item_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . "finascop_stock_itemmaster_product_codes LEFT JOIN finascop_branch ON br_ID = fsipc_store LEFT JOIN finascop_branch_group ON store_group_id = fsipc_storeGroup where {$filter_part} {$cond}";
        $listQuery = "SELECT fsipc_id,fsipc_stit_id,fsipc_code,fsipc_codeType,fsipc_storeGroup,fsipc_store,fsipc_createdOn,fsipc_createdBy,IF(fsipc_storeGroup > 0,store_group_name,'Global') AS store_group_name,"
            . "IF(fsipc_store > 0,br_Name,'All Stores') AS br_Name "
            . "from " . FINASCOP_DB . "finascop_stock_itemmaster_product_codes LEFT JOIN finascop_branch ON br_ID = fsipc_store LEFT JOIN finascop_branch_group ON store_group_id = fsipc_storeGroup WHERE {$filter_part} {$cond} ORDER BY $rec_sort $rec_sort_dir ";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'getStoreGroup':
        if ($_POST['query'] != '') {
            $que = addslashes($_POST['query']);
            $searchQuery = " AND store_group_name LIKE '%{$que}%'";
        } else {
            $searchQuery = '';
        }

        //finascop_getjsonkeyarray("SELECT stit_ID,stit_itemName,stit_SKU FROM finascop_stock_itemmaster where 1=1 {$searchQuery} ");
        $qry = $db->getMulipleData("SELECT store_group_id,store_group_name FROM finascop_branch_group where status = 1 {$searchQuery}", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'getBranchName':
        $store_group = $_POST['store_group'];
        if ($store_group > 0) {
            $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch WHERE br_status = 'Active' AND br_storeGroup = {$store_group}", true);
        }

        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'getPTStore':
        $packs = array();
        $search_hint = $_POST['query'];
        //print_r($_POST);
        if (!in_array($_POST['stdpckl11'], $packs)) {
            $packs[] = $_POST['stdpckl11'];
        }
        //        if (!in_array($_POST['stdpckl12'], $packs)) {
        //            $packs[] = $_POST['stdpckl12'];
        //        }
        if (!in_array($_POST['stdpckl21'], $packs)) {
            $packs[] = $_POST['stdpckl21'];
        }
        if (!in_array($_POST['stdpckl31'], $packs)) {
            $packs[] = $_POST['stdpckl31'];
        }
        if (!in_array($_POST['stdpckl41'], $packs)) {
            $packs[] = $_POST['stdpckl41'];
        }
        $packs = array_filter($packs);
        if (count($packs) > 0) {
            $pachTyp = implode(',', $packs);
            $qry = "select package_type_id,package_type_name from " . FINASCOP_DB . "mypha_productpackage_type WHERE package_type_id IN ({$pachTyp}) AND status = 1 AND package_type_name LIKE '{$search_hint}%'order by package_type_name";
            $data = $db->getMultipleData($qry, true);
        }

        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'deleteItem':
        $id = $_POST['fsipc_id'];
        $del_query = "DELETE FROM finascop_stock_itemmaster_product_codes WHERE fsipc_id =" . $id;
        $status = $db->query($del_query);
        $del_query = "DELETE FROM finascop_stock_itemmaster_product_code_stores WHERE fsipc_id=" . $id;
        $status = $db->query($del_query);

        $status = $db->query('commit');
        if ($status) {

            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'getOrginCountry':
        $search_hint = $_POST['query'];
        $qry = "select country_id,country_name from finascop_country WHERE status = 1 AND country_name LIKE '{$search_hint}%'order by country_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getItemCategory':
        $sub_category_id = $_POST['sub_category_id'];
        $subcatDetails = $db->getFromDB("SELECT isPerishable,main_category,processingTime,hasRestaurantService FROM mypha_productsubcategory WHERE sub_category_id = {$sub_category_id}", true);
        $main_category = $subcatDetails['main_category'];
        $parent_category = $db->getItemFromDB("SELECT parent_category FROM mypha_productcategory WHERE category_id = {$main_category}");

        $itemHistory['iteParentCategory'] = $db->getItemFromDB("SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = {$parent_category}");
        $parent_category_businessType = $db->getItemFromDB("SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = {$parent_category}");
        $itemHistory['iteMidCategory'] = $db->getItemFromDB("SELECT category_name FROM mypha_productcategory WHERE category_id = {$main_category}");
        $parent_category_businessTypeName = $db->getItemFromDB("SELECT business_type_name FROM finascop_business_type WHERE business_type_id = {$parent_category_businessType}");
        $itemHistory['categoryCombination'] = $parent_category_businessTypeName . ' > ' . $itemHistory['iteParentCategory'] . ' > ' . $itemHistory['iteMidCategory'];
        $itemHistory['isPerishable'] = $subcatDetails['isPerishable'];
        $itemHistory['processingTime'] = $subcatDetails['processingTime'];
        $itemHistory['hasRestaurantService'] = $subcatDetails['hasRestaurantService'];
        if (!empty($itemHistory)) {
            echo json_encode($itemHistory);
        }
        break;
    case 'verifyProdct':
        $medId = $_POST['itemid'];
        $db->query('begin');

        $data = array(
            "isVerified" => 1,
            "verifedOn" => date('Y-m-d H:i:s'),
            "verifedBy" => $_SESSION['admin']->Finascop_UserId
        );
        $isVerified = $db->getItemFromDb("SELECT isVerified FROM finascop_stock_itemmaster WHERE stit_ID = {$medId}");

        if ($medId > 0 && $isVerified == 0) {
            $status = $db->perform("finascop_stock_itemmaster", $data, 'update', 'stit_ID =' . $medId);
        } else {
            echo "{'success':true,'valid':false,'message': 'Data is already verified..'}";
            exit();
        }

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved '}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'productsCoutryStore':
        $typeAhead = '';
        $qry = "SELECT country_id, country_name FROM finascop_country WHERE status = 1  $typeAhead ORDER BY country_name ASC";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'getBusinessType':


        $qry = "select business_type_id,business_type_name from " . FINASCOP_DB . "finascop_business_type where status= 1  order by business_type_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        // echo '{success:true, data:'.json_encode($data).'}';
        break;
    case 'getDepartment':
        if ($_POST['primaryBt'] > 0) {
            $primaryBt = $_POST['primaryBt'];
        } else {
            $primaryBt = 0;
        }
        $qry = "select parent_category_id,parent_category FROM mypha_productparent_category where status= 1 AND  parent_category_businessType = {$primaryBt}  order by parent_category";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getCategory':
        if ($_POST['department'] > 0) {
            $primaryBt = $_POST['department'];
        } else {
            $primaryBt = 0;
        }
        $qry = "select category_id,category_name FROM mypha_productcategory where status= '1' AND  parent_category = {$primaryBt}  order by category_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getSubCategory':
        if ($_POST['category'] > 0) {
            $primaryBt = $_POST['category'];
        } else {
            $primaryBt = 0;
        }
        $qry = "select sub_category_id,sub_category FROM mypha_productsubcategory where status = 1 AND  main_category = {$primaryBt}  order by sub_category";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'exportDataToParentDB':
        $ItemId = $_POST['ItemId'];
        $db->query('begin');
        exportProduct($ItemId);
        $sourceData = $db->getFromDB("SELECT gs1.id AS sourceId,gs1.uploadedFrom AS uploadedId,pum.storeGroupId AS storeGroupId FROM gs1_products_extension gs2 
        INNER JOIN gs1_products_source gs1 ON gs1.id = gs2.id
        INNER JOIN product_source_upload_master pum ON gs1.uploadedFrom = pum.id WHERE gs2.productId = {$ItemId}", true);
        if ($sourceData && $sourceData['storeGroupId'] > 0) {

            $branch_id = $parentdb->getItemFromDB("SELECT br_ID FROM finascop_branch WHERE br_storeGroup = {$sourceData['storeGroupId']} ORDER BY br_ID ASC LIMIT 1");

            $grozeo_stitId = $db->getItemFromDB("SELECT grozeo_stitId FROM product_grozeo_map WHERE product_stitId= {$ItemId}");
            $upmdata['grozeoStitId'] = $grozeo_stitId;
            $upmdata['status'] = 5;
            $status = $db->perform('upload_product_map', $upmdata, 'update', " uploadId = {$sourceData['uploadedId']} AND productId = {$ItemId}");

            $isStockAvailable = $parentdb->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory WHERE stit_id = {$grozeo_stitId} AND branch_id = {$branch_id}");
            if ($isStockAvailable == 0) {
                $stckIns['stit_id'] = $grozeo_stitId;
                $stckIns['branch_id'] = $branch_id;
                $stckIns['item_count'] = 0;
                $stckIns['created_at'] = date('Y-m-d H:i:s');
                $status = $parentdb->perform('finascop_stock_branch_inventory', $stckIns);
            }
        }
        if ($_POST['tpProduct'] == 1) {
            $tpDatails = $db->getFromDB("SELECT stit_ID,grozeo_stitId FROM third_party_products WHERE mappedProductId = {$ItemId}", true);
            $tpItemId = $tpDatails['stit_ID'];
            $isGrozeoPrdct = $tpDatails['grozeo_stitId'];
            $isProductExported = $db->getItemFromDB("SELECT grozeo_stitId FROM product_grozeo_map WHERE product_stitId= {$ItemId}");

            $grozeoHasStock = $parentdb->getFromDB("SELECT * FROM finascop_stock_branch_inventory WHERE stit_id = {$isGrozeoPrdct}", true);
            if ($grozeoHasStock['id'] > 0) {
                $grzStockUp['status'] = 0;
                $grzStockUp['updated_on'] = date('Y-m-d H:i:s');
                $status = $parentdb->perform('finascop_stock_branch_inventory', $grzStockUp, 'update', " stit_id= {$isGrozeoPrdct}");
                //isProductExported have stock in grozeo
                $hasStock = $parentdb->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory WHERE stit_id= {$isProductExported} AND branch_id = {$grozeoHasStock['branch_id']}");
                if ($hasStock == 0) {
                    $stckIns['stit_id'] = $isProductExported;
                    $stckIns['branch_id'] = $grozeoHasStock['branch_id'];
                    $stckIns['item_count'] = $grozeoHasStock['item_count'];
                    $stckIns['created_at'] = date('Y-m-d H:i:s');
                    $status = $parentdb->perform('finascop_stock_branch_inventory', $stckIns);
                }
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Data confirmed get synced '}";
        } else {
            echo "{'success':False,'valid':false,'msg': 'Error While Saving.'}";
        }
        break;
    case 'exportAllData':
        $itemar = $_POST['itemarr'];
        $itemdecode = json_decode($itemar);
        $itemcount = count($itemdecode);
        $db->query('begin');
        for ($i = 0; $i < $itemcount; $i++) {
            exportProduct($itemdecode[$i]);
        }
        //$datas = $db->getMultipleData("SELECT * FROM finascop_stock_itemmaster", true);


        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Data confirmed get synced '}";
        } else {
            echo "{'success':False,'valid':false,'msg': 'Error While Saving.'}";
        }
        break;
    case 'getPackMastrStore':
        $search_hint = $_POST['query'];
        $qry = "select rpckm_id,rpckm_name from  retaline_package_master  WHERE rpckm_status = 1 and rpckm_name LIKE '{$search_hint}%'order by rpckm_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getSubCategoryAttributes':
        $prdctCategory = $_POST['prdctCategory'];
        if ($prdctCategory > 0) {
            $count = $db->getItemFromDB("SELECT COUNT(*) FROM attributeSubcategoryMap WHERE subCategoryId = {$prdctCategory} group by attributeId");
            $result = $db->getMultipleData("SELECT attributeId,name,valueMode FROM attributeSubcategoryMap INNER JOIN attribute ON id = attributeId AND subCategoryId = {$prdctCategory} GROUP BY attributeId ORDER BY valueMode ASC ", true);
            if ($count > 0)
                echo '{"totalCount":' . $count . ',"data":' . json_encode($result) . '}';
            else
                echo '{"totalCount":0,"data":[]}';
        } else {
            echo '{"totalCount":0,"data":[]}';
        }

        break;
    case 'getAttributeValues':
        $attributeId = $_POST['attributeId'];
        $qry = "select id,attributeId,valueName from attributeValue where attributeId = {$attributeId} order by valueName";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'saveAttributeInProducts':
        $attributeIds = json_decode($_POST['attributeIds']);
        $ItemId = $_POST['ItemId'];

        $db->query('begin');
        foreach ($attributeIds as $attributeId) {
            $attributeValues = $_POST['attValues_' . $attributeId];
            // Check if the string contains a comma
            if (strpos($attributeValues, ',') !== false) {
                // Split the string into an array
                $attributeValuesArrays = explode(',', $attributeValues);
            } else {
                // If no comma is found, create an array with the original string as its only element
                $attributeValuesArrays = array($attributeValues);
            }
            foreach ($attributeValuesArrays as $attributeValuesArray) {
                if ($attributeValuesArray > 0) {
                    $isInserted = $db->getItemFromDB("SELECT COUNT(*) FROM attributeProductMap WHERE attributeId = {$attributeId} AND stitId = {$ItemId} AND attributeValueId = {$attributeValuesArray} ");
                    if ($isInserted == 0) {
                        $indata['attributeId'] = $attributeId;
                        $indata['attributeValueId'] = $attributeValuesArray;
                        //$indata['attributeValueMode'] = $_POST['valueMode_' . $attributeId];
                        $indata['createdOn'] = date('Y-m-d H:i:s');
                        $indata['createdBy'] = $_SESSION['admin']->Finascop_UserId;
                        $indata['stitId'] = $ItemId;
                        $status = $db->perform('attributeProductMap', $indata);
                    }
                }
            }
            if (!empty($attributeValues))
                $status = $db->query("DELETE FROM attributeProductMap WHERE attributeId = {$attributeId} AND stitId = {$ItemId} AND attributeValueId NOT IN ({$attributeValues}) ");

            /*if ($isInserted > 0) {
                $updata['attributeValues'] =  $_POST['attValues_' . $attributeId];
                $updata['attributeValueMode'] = $_POST['valueMode_' . $attributeId];
                $updata['updatedOn'] = date('Y-m-d H:i:s');
                $updata['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('attributeProductMap', $updata, 'update', " attributeId = {$attributeId} AND stitId = {$ItemId}");
            } else {
                $indata['attributeId'] = $attributeId;
                $indata['attributeValues'] = $_POST['attValues_' . $attributeId];
                $indata['attributeValueMode'] = $_POST['valueMode_' . $attributeId];
                $indata['createdOn'] = date('Y-m-d H:i:s');
                $indata['createdBy'] = $_SESSION['admin']->Finascop_UserId;
                $indata['stitId'] = $ItemId;
                $status = $db->perform('attributeProductMap', $indata);
            }*/
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved '}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'attributePrdct_form_load':
        $attributeIds = json_decode($_POST['attributeIds']);
        $ItemId = $_POST['ItemId'];
        foreach ($attributeIds as $attributeId) {
            $productAttributeDetails = $db->getMultipleData("SELECT attributeId,GROUP_CONCAT(attributeValueId) AS attributeValues,valueMode AS attributeValueMode  FROM attributeProductMap
             INNER JOIN attribute ON attribute.id = attributeId WHERE attributeId = {$attributeId} AND stitId = {$ItemId} GROUP BY attributeId,stitId", true);
            if (!empty($productAttributeDetails)) {
                foreach ($productAttributeDetails as $productAttributeDetail) {
                    $data['attId_' . $attributeId] = $productAttributeDetail['attributeId'];
                    $data['attValues_' . $attributeId] = $productAttributeDetail['attributeValues'];
                    $data['valueMode_' . $attributeId] = $productAttributeDetail['attributeValueMode'];
                }
            }
        }
        if (!empty($data)) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            echo '{"success":true,"data":[]}';
        }

        break;
    case 'getGstStore':
        $hsnId = $_POST['hsnId'];
        $search_hint = $_POST['query'];
        $qry = "select id, hsnGst,hsnId,hsnCess from hsn_value WHERE hsnId = {$hsnId} AND hsnGst LIKE '{$search_hint}%'order by id";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'fetchDescription':

        $prdctSKU = $_POST['prdctSKU'];
        $prdctBrand = $_POST['prdctBrand'];
        $prdctCategory = $_POST['prdctCategory'];
        $description = $_POST['description'];

        //$prompt = "Generate product description for: " . $prdctSKU . " of brand " . $prdctBrand . "  under the category " .$prdctCategory;
        //$prompt = "Create an expanded product description. The content should be complete and concise. The content should not contain any links or external references: " . $prdctSKU . " of brand " . $prdctBrand . " under the category " .$prdctCategory." with short description " .$description;
        $prompt = "Create an expanded product description for  our ecom portal with few hash tags and seo capable keywords on  " . '"' . $prdctSKU . '"' . " of brand " . $prdctBrand . "  under the category " . $prdctCategory . " with short description " . $description . ". Please make sure that the content is complete and concise, while does not contain any links or external references.";
        $fields['contents']['parts']['text'] = $prompt;

        $key = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'GOOGLE_KEY'");
        $aiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $key;
        $fields_string = json_encode($fields);
        //print_r($invoiceUrl . "/n");
        //print_r($fields_string . "/n");
        $opts = array(
            CURLOPT_URL => $aiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POST => count($fields),
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        $response = json_decode($data);
        //print_r($response);
        echo '{"success":true,"data":' . $data . '}';
        break;
    case 'exportAndSyncProducts':
        $uploadId = $_POST['uploadId'];
        $storeGroupId = $db->getItemFromDB("SELECT storeGroupId FROM product_source_upload_master WHERE id = {$uploadId}");
        $branch_id = $parentdb->getItemFromDB("SELECT br_ID FROM finascop_branch WHERE br_storeGroup = {$storeGroupId} ORDER BY br_ID ASC LIMIT 1");
        $cond = " WHERE 1=1  AND uploadId = " . intval($_POST['uploadId']) . " and status = 0 ";
        $listQuery = "SELECT stit_ID,stit_SKU,stit_brand_name,med_manufacturename,stit_category_name,stit_HSNCode,stit_HSN_code,stit_GST FROM  finascop_stock_itemmaster INNER JOIN upload_product_map ON productId = stit_ID  {$cond} ";
        $products = $db->getMultipleData($listQuery, true);
        if (count($products) > 0 && $products[0]['stit_ID'] > 0) {
            $db->query('begin');
            foreach ($products as $product) {
                exportProduct($product['stit_ID']);
                $grozeo_stitId = $db->getItemFromDB("SELECT grozeo_stitId FROM product_grozeo_map WHERE product_stitId= {$product['stit_ID']}");
                $upmdata['grozeoStitId'] = $grozeo_stitId;
                $upmdata['status'] = 5;
                $status = $db->perform('upload_product_map', $upmdata, 'update', " uploadId = {$uploadId} AND productId = {$product['stit_ID']}");

                $isStockAvailable = $parentdb->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory WHERE stit_id = {$grozeo_stitId} AND branch_id = {$branch_id}");
                if ($isStockAvailable == 0) {
                    $stckIns['stit_id'] = $grozeo_stitId;
                    $stckIns['branch_id'] = $branch_id;
                    $stckIns['item_count'] = 0;
                    $stckIns['created_at'] = date('Y-m-d H:i:s');
                    $status = $parentdb->perform('finascop_stock_branch_inventory', $stckIns);
                }
            }
            $status = $db->query('commit');
        } else {
            echo "{success:false,valid:true,message:'Product already synced.'}";
            exit();
        }

        if ($status == 1) {
            echo "{success:true,valid:true,message:'Product syncing is going on, please wait...'}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'bulkExportAndSyncProducts':
        $itemar = $_POST['itemarr'];
        $itemdecode = json_decode($itemar);
        $itemcount = count($itemdecode);
        $db->query('begin');
        for ($i = 0; $i < $itemcount; $i++) {
            $mapDetails = $db->getFromDB("SELECT productId,uploadId,grozeoStitId FROM upload_product_map WHERE id = {$itemdecode[$i]}", true);
            $storeGroupId =  $db->getItemFromDB("SELECT storeGroupId FROM product_source_upload_master WHERE id = {$mapDetails['uploadId']}");
            if ($mapDetails['grozeoStitId'] == 0)
                exportProduct($mapDetails['productId']);
            $grozeo_stitId = $db->getItemFromDB("SELECT grozeo_stitId FROM product_grozeo_map WHERE product_stitId= {$mapDetails['productId']}");

            $upmdata['grozeoStitId'] = $grozeo_stitId;
            $upmdata['status'] = 5;
            $status = $db->perform('upload_product_map', $upmdata, 'update', " uploadId = {$mapDetails['uploadId']} AND productId = {$mapDetails['productId']}");
            if ($storeGroupId > 0) {
                $branch_id = $parentdb->getItemFromDB("SELECT br_ID FROM finascop_branch WHERE br_storeGroup = {$storeGroupId} ORDER BY br_ID ASC LIMIT 1");
                $isStockAvailable = $parentdb->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory WHERE stit_id = {$grozeo_stitId} AND branch_id = {$branch_id}");
                if ($isStockAvailable == 0) {
                    $stckIns['stit_id'] = $grozeo_stitId;
                    $stckIns['branch_id'] = $branch_id;
                    $stckIns['item_count'] = 0;
                    $stckIns['created_at'] = date('Y-m-d H:i:s');
                    $status = $parentdb->perform('finascop_stock_branch_inventory', $stckIns);
                }
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Data confirmed get synced '}";
        } else {
            echo "{'success':False,'valid':false,'msg': 'Error While Saving.'}";
        }
        break;
    case 'getUnitValue':
        $unitId = $_POST['unitId'];
        $search_hint = $_POST['query'];
        $qry = "select id,value from unit_value WHERE unitId = {$unitId} order by id";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'scrapedOutput':
        ob_start();
        include('scraperapi.php');
        $resHtml = ob_get_contents();
        ob_end_clean();
        echo $resHtml;
        exit();
        break;
    case 'listNewProductsData':
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['item_id', 'item_name', 'item_code'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'item_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'ItemId' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');

        $searchBrand = '';
        $searchCategory = '';

        $search = " WHERE 1=1 AND isExported = 'No' ";
        $IsSuperUser = $_SESSION['admin']->IsSuperUser;
        $UserType = $_SESSION['admin']->UserType;
        if ($IsSuperUser == 'Yes' || $UserType == 'Admin') {
            $search .= " ";
        } else {
            $search .= "  AND UserId = {$_SESSION['admin']->Finascop_UserId} ";
        }

        if (isset($filter)) {
            $filter = $_POST['filter'];
            foreach ($filter as $key => $val) {
                $type = $val['data']['type'];
                $value = $val['data']['value'];
                $field = $val['field'];
                //$search = " WHERE 1=1 ";

                switch ($val['data']['type']) {
                    case 'string':
                        /*if ($val['field'] == 'stit_HSN_code') {
                            $hsn_code = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(hsn_id),0) FROM finascop_hsn WHERE hsn_code LIKE '{$val['data']['value']}%' ");
                            $search .= " AND stit_HSNCode IN({$hsn_code}) ";
                        } else */
                        switch ($val['field']) {
                            case 'stit_category_name':
                                $searchCategory = $val['data']['value'];
                                $search .= " AND stit_category_name LIKE '%{$searchCategory}%' ";
                                break;
                            case 'stit_brand_name':
                                $searchBrand = $val['data']['value'];
                                $search .= " AND stit_brand_name LIKE '%{$searchBrand}%' ";
                                break;
                            case 'statusName':
                                if ($val['data']['value'] == 'Active') {
                                    $search .= " and stit_status = 1 ";
                                } else if ($val['data']['value'] == 'Inactive') {
                                    $search .= " and stit_status = 0 ";
                                } else {
                                    $search .= " and stit_status IN(1,0) ";
                                }
                                break;
                            case 'isVerified':
                                if ($val['data']['value'] == 'Yes') {
                                    $search .= " and (isVerified = 1) ";
                                } else if ($val['data']['value'] == 'No') {
                                    $search .= " and (isVerified = 0) ";
                                } else {
                                    $search .= " and (isVerified IN(1,0)) ";
                                }
                                break;
                            default:
                                $search .= " and ({$val['field']} LIKE '%{$val['data']['value']}%') ";
                                break;
                        }
                        break;
                    case 'list':
                        if ($val['field'] == 'isVerified') {
                            if ($val['data']['value'] == 'Yes') {
                                $search .= " and (isVerified = 'Yes') ";
                            } else if ($val['data']['value'] == 'No') {
                                $search .= " and (isVerified = 'No') ";
                            } else {
                                $search .= "  ";
                            }
                        } else {
                            $search .= " and ({$val['field']} LIKE '{$val['data']['value']}%') ";
                        }
                        break;
                    case 'numeric':
                        if ($val['field'] == 'tax') {
                            $search .= " AND stit_GST " . $comparisons[$val['data']['comparison']] . " " . $val['data']['value'];
                        } else {
                            $search .= " AND " . $val['field'] . " " . $comparisons[$val['data']['comparison']] . " " . $val['data']['value'];
                        }
                        break;
                    case 'date':
                        switch ($val['data']['comparison']) {
                            case 'gt':
                                $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') > '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                break;
                            case 'lt':
                                $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') < '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                break;
                            case 'eq':
                                $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') = '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                break;
                        }

                        break;
                }
            }
        }

        $countQuery = "SELECT count(*)
            from finascop_stock_itemmaster {$search} AND isMedicine = 0 order by {$sort} {$dir}";

        $qry = "SELECT 
    pdt_brand,
    stit_ID AS ItemId,
    product_is_home,
    stit_displaylabel,
    stit_itemName,
    stit_status,
    least_package_type_id,
    least_package_type_name,
    product_category,
    stit_HSN_code,
    stit_package_type_namme,
    stit_category_name,
    stit_brand_name,
    stit_product_variant,
    stit_quantity,
    stit_GST AS tax, 
    stit_SKU,
    finascop_stock_itemmaster.createdOn AS createdOn,
    CONCAT(FirstName, '', LastName) AS createdBy,
    IF(stit_status = 1, 'Active', 'Inactive') AS statusName,
    IF(isVerified = 1, 'Yes', 'No') AS isVerified,
    taxValueId,
    COALESCE(miCount.miCount, 0) AS miCount,  -- Count of images of type 1
    COALESCE(aiCount.aiCount, 0) AS aiCount,  -- Count of images of type 0
    IF(mrp.mrps > 0, 'Yes', 'No') AS hasMrp,
    IF(exportProduct.exportedPrdcts > 0, 'Yes', 'No') AS isExported,
    CONCAT(COALESCE(miCount.miCount, 0), '/', COALESCE(aiCount.aiCount, 0)) AS imgCount,
    itemProcessingTime
FROM 
    finascop_stock_itemmaster 
LEFT JOIN 
    finascop_usr_profile ON UserId = createdBy 
LEFT JOIN 
    (SELECT stit_id AS stitid, COUNT(*) AS mrps FROM item_mrp GROUP BY stit_id) mrp 
    ON mrp.stitid = finascop_stock_itemmaster.stit_ID 
LEFT JOIN 
    (SELECT product_stitId AS stitid, COUNT(*) AS exportedPrdcts FROM product_grozeo_map GROUP BY product_stitId) exportProduct 
    ON exportProduct.stitid = finascop_stock_itemmaster.stit_ID
LEFT JOIN 
    (SELECT product_id, COUNT(*) AS miCount FROM finascop_stock_item_images WHERE image_type = 1 GROUP BY product_id) miCount 
    ON miCount.product_id = finascop_stock_itemmaster.stit_ID
LEFT JOIN 
    (SELECT product_id, COUNT(*) AS aiCount FROM finascop_stock_item_images WHERE image_type = 0 GROUP BY product_id) aiCount 
    ON aiCount.product_id = finascop_stock_itemmaster.stit_ID
WHERE 
    isMedicine = 0";
        $count = $db->getItemFromDB("SELECT COUNT(*) FROM ({$qry}) AS countItem   {$search} order by {$sort} {$dir}");
        $listQuery = "SELECT * FROM ({$qry}) AS listItem   {$search} order by {$sort} {$dir} limit {$rec_start},{$rec_limit}";
        $data = $db->getMultipleData($listQuery, true);

        echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';

        break;
    case 'saveAsMerchantProduct':

        $imageOptions = 3;
        $imageOptions = $_POST['imageOptions'];
        $gs1Id = $_POST['gs1Id'];

        $SKU = $_POST['stit_SKU'];
        $dSKU = addslashes($SKU);
        $manufactureId = $db->getItemSafe("SELECT manufacture_id FROM mypha_productbrands WHERE brand_id = ?", "s", [$_POST['pdt_brand']]);
        $manufactureDetails = $db->getFromDB("SELECT manufacture_id,manufacture_name FROM mypha_productmanufacture WHERE manufacture_id = $manufactureId", true);
        $baseData = $db->getFromDB("SELECT storeGroupId,sku_code FROM gs1_products_extension WHERE id = {$gs1Id}", true);
        $storeGroupId = $baseData['storeGroupId'];
        $storeCode = $baseData['sku_code'];
        $uploadId = $db->getItemFromDB("SELECT uploadedFrom FROM gs1_products_source WHERE id = {$gs1Id}");
        if ($storeGroupId == 0 || empty($storeGroupId)) {
            echo "{success: false, msg:'Merchant Id missing not possible to map as Merchant Product.'}";
            exit;
        }

        $stit_productsFor = $_POST['stit_productsFor'];
        $stit_productsForCountries = explode(',', $stit_productsFor);
        $stit_hsnId = $db->getItemSafe("SELECT hsn_id FROM finascop_hsn WHERE hsn_code = ?", "s", [$_POST['HSN_code']]);
        $data = array(
            "stit_StoreGroup" => $storeGroupId,
            "stit_stdPacking" => ($_POST['stit_stdPacking'] > 0 ? $_POST['stit_stdPacking'] : 0),
            "stit_salesUnit" => ($_POST['stit_salesUnit'] > 0 ? $_POST['stit_salesUnit'] : 0),
            "stit_package_type_id" => ($_POST['stit_package_type_id'] > 0 ? $_POST['stit_package_type_id'] : 0),
            "stit_itemId" => ($_POST['item'] > 0 ? $_POST['item'] : 0),
            "stit_SKU" => $SKU,
            "stit_itemERPId" => ($_POST['stit_itemERPId'] > 0 ? trim($_POST['stit_itemERPId']) : 0),
            "stit_itemBarcode" => ($_POST['stit_itemBarcode'] > 0 ? $_POST['stit_itemBarcode'] : 0),
            "stit_itemReturnTime" => ($_POST['stit_itemReturnTime'] > 0) ? $_POST['stit_itemReturnTime'] : 0,
            "stit_custInitiate" => ($_POST['stit_custInitiate'] > 0 ? $_POST['stit_custInitiate'] : 0),
            "stit_HSNCode" => ($_POST['HSN'] > 0 ? $_POST['HSN'] : 0),
            "stit_hsnId" => $stit_hsnId,
            "stit_GST" => ($_POST['GST'] > 0 ? $_POST['GST'] : 0),
            "taxValueId" => ($_POST['taxValueId'] > 0 ? $_POST['taxValueId'] : 0),
            "stit_displaylabel" => (!empty($_POST['display_label']) ? $_POST['display_label'] : ''),
            "itemProcessingTime" => (!empty($_POST['itemProcessingTime']) ? $_POST['itemProcessingTime'] : ''),
            "stit_Description" => (!empty($_POST['description']) ? $_POST['description'] : ''),
            "stit_product_variant" => (!empty($_POST['stit_product_variant']) ? $_POST['stit_product_variant'] : ''),
            "product_category" => ($_POST['product_category'] > 0 ? $_POST['product_category'] : 0),
            "pdt_brand" => ($_POST['pdt_brand'] > 0 ? $_POST['pdt_brand'] : 0),
            "featured" => ($_POST['featured'] > 0 ? $_POST['featured'] : 0),
            "popular" => ($_POST['popular'] > 0 ? $_POST['popular'] : 0),
            "med_manufactureid" => ($manufactureDetails['manufacture_id'] > 0 ? $manufactureDetails['manufacture_id'] : 0),
            "med_manufacturename" => (!empty($manufactureDetails['manufacture_name']) ? $manufactureDetails['manufacture_name'] : ''),
            "stit_long_description" => (!empty($_POST['stit_long_description']) ? $_POST['stit_long_description'] : ''),
            "stit_quantity" => (!empty($_POST['stit_quantity']) ? $_POST['stit_quantity'] : ''),
            "stit_itemName" => (!empty($_POST['item_name']) ? $_POST['item_name'] : ''),
            "stit_HSN_code" => (!empty($_POST['HSN_code']) ? $_POST['HSN_code'] : ''),
            "stit_package_type_namme" => (!empty($_POST['stit_package_type_namme']) ? $_POST['stit_package_type_namme'] : ''),
            "stit_category_name" => (!empty($_POST['stit_category_name']) ? $_POST['stit_category_name'] : ''),
            "stit_brand_name" => (!empty($_POST['stit_brand_name']) ? $_POST['stit_brand_name'] : ''),
            "least_package_type_id" => ($_POST['least_package_type_id'] > 0 ? $_POST['least_package_type_id'] : 0),
            "least_package_type_name" => (!empty($_POST['least_package_type_name']) ? $_POST['least_package_type_name'] : ''),
            "isMedicine" => 0,
            "courierDelivery" => ($_POST['courierDelivery'] > 0 ? $_POST['courierDelivery'] : 0),
            "directDelivery" => ($_POST['directDelivery'] > 0 ? $_POST['directDelivery'] : 0),
            "directPurchase" => ($_POST['directPurchase'] > 0 ? $_POST['directPurchase'] : 0),
            "isRRPApplicable" => ($_POST['isRRPApplicable'] > 0 ? $_POST['isRRPApplicable'] : 0),
            "stit_foodtype" => ($_POST['stit_foodtype'] > 0 ? $_POST['stit_foodtype'] : 0),
            "stit_orgin_country" => ($_POST['stit_orgin_country'] > 0 ? $_POST['stit_orgin_country'] : 0),
            "stit_unit" => ($_POST['stit_unit'] > 0 ? $_POST['stit_unit'] : 0),
            "stit_qty" => ($_POST['stit_qty'] > 0 ? $_POST['stit_qty'] : 0),
            "stit_package_master" => (!empty($_POST['stit_package_master']) ? $_POST['stit_package_master'] : 0),
            "stit_courierWt" => (!empty($_POST['stit_courierWt']) ? $_POST['stit_courierWt'] : 0),
            "item_length" => (!empty($_POST['item_length']) ? $_POST['item_length'] : 0),
            "item_breadth" => (!empty($_POST['item_breadth']) ? $_POST['item_breadth'] : 0),
            "item_height" => (!empty($_POST['item_height']) ? $_POST['item_height'] : 0),
            "tpConvertedStatus" => 2,
            "tpCreatedFrom" => $uploadId
        );

        $fsuidata['fsi_item_id'] = $data['stit_itemId'];
        $fsuidata['fsi_item_name'] = $data['stit_itemName'];
        $fsuidata['fsi_brand_id'] = $data['pdt_brand'];
        $fsuidata['fsi_brand_name'] = $data['stit_brand_name'];
        $fsuidata['fsi_category_id'] = $data['product_category'];
        $fsuidata['fsi_categry_name'] = $data['stit_category_name'];
        $fsuidata['fsi_variant'] = mysqli_real_escape_string($db->linker(), $data['stit_product_variant']);
        $fsuidata['fsi_displaylabel'] = $data['stit_displaylabel'];
        $fsuidata['isMedicine'] = 0;

        $itemName = $_POST['item'];

        if ($_POST['dupitem'] == 'D') {
            unset($_POST['id']);
        }
        $db->query('begin');
        if ($_POST['directPurchase'] == 0) {
            if (empty($_POST['stit_unit']) || empty($_POST['stit_qty'])) {
                // echo "{success: false, msg:'Enter Quantity and unit.'}";
                //exit;
            }
        }
        if ($_POST['least_package_type_id'] > 0) {
            $lptyp = " AND least_package_type_id = " . intval($_POST['least_package_type_id']) . " ";
        } else {
            $lptyp = " ";
        }
        if (empty($_POST['id'])) {

            $stit_fsiuid = updateTPUniqueItemTable(0, $fsuidata);
            $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];
            if ($data['stit_itemName'] == 'Private Product') {
                $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from third_party_products  
    WHERE stit_product_variant = '" . mysqli_real_escape_string($db->linker(), $_POST['stit_product_variant']) . "' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} AND isMedicine = 0 ");
                $matchinSKUs = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_SKU) FROM third_party_products  
        WHERE stit_product_variant = '" . mysqli_real_escape_string($db->linker(), $_POST['stit_product_variant']) . "' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} AND isMedicine = 0 ");
            } else {
                $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from third_party_products  
            WHERE stit_itemId = '{$itemName}' AND  stit_product_variant = '" . mysqli_real_escape_string($db->linker(), $_POST['stit_product_variant']) . "' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} AND isMedicine = 0 ");
                $matchinSKUs = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_SKU) FROM third_party_products  
        WHERE stit_itemId = '{$itemName}' AND  stit_product_variant = '" . mysqli_real_escape_string($db->linker(), $_POST['stit_product_variant']) . "' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} AND isMedicine = 0 ");
            }

            if ($IsItemNameUnique > 0 && $data['stit_itemName'] != 'Private Product') {

                $message = "This Item already existing.Matching SKU(s) are " . $matchinSKUs;
                echo "{success: false, msg: '{$message}'}";
                exit;
            }
            $SKUUnique = $db->getItemFromDB("SELECT COUNT(*) from third_party_products  WHERE stit_SKU = '{$dSKU}'  ");
            if ($SKUUnique > 0) {
                echo "{success: false, msg:'This SKU already existing.'}";
                exit;
            }


            $data['createdOn'] = date('Y-m-d H:i:s');
            $data['createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform("third_party_products", $data);
            $itemIdFrPt = $db->insert_id();

            if ($stit_productsForCountries[0] > 0) {
                $status = $db->query("DELETE FROM third_party_products_country WHERE stitc_stit_id = {$itemIdFrPt}");
                for ($i = 0; $i < count($stit_productsForCountries); $i++) {
                    $fsitcdata['stitc_stit_id'] = $itemIdFrPt;
                    $fsitcdata['stitc_country'] = $stit_productsForCountries[$i];
                    $status = $db->perform("third_party_products_country", $fsitcdata);
                }
            }
            $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM third_party_products WHERE stit_fsiuid = {$stit_fsiuid['fsi_uid']}");
            if ($stit_fsiuid['status'] == 'NEW') {

                $db->perform("third_party_products_uniqueitem", $uuit, 'update', " fsi_uid = {$stit_fsiuid['fsi_uid']}");
            }
            $message = 'Saved Successfully';
        } else {
            $itemIdFrPt = $_POST['id'];
            $fsiUid = $db->getItemSafe("SELECT stit_fsiuid FROM third_party_products WHERE stit_ID = ?", "i", [$_POST['id']]);
            $stit_fsiuid = updateTPUniqueItemTable($fsiUid, $fsuidata);
            $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];
            if ($data['stit_itemName'] == 'Private Product') {
                $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from third_party_products  
                WHERE stit_product_variant = '" . mysqli_real_escape_string($db->linker(), $_POST['stit_product_variant']) . "' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} AND isMedicine = 0 AND stit_ID <> {$_POST['id']}");
                $matchinSKUs = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_SKU) FROM third_party_products  
                    WHERE stit_product_variant = '" . mysqli_real_escape_string($db->linker(), $_POST['stit_product_variant']) . "' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} AND isMedicine = 0 AND stit_ID <> {$_POST['id']} ");
            } else {
                $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from third_party_products  
                        WHERE stit_itemId = '{$itemName}' AND  stit_product_variant = '" . mysqli_real_escape_string($db->linker(), $_POST['stit_product_variant']) . "' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} AND isMedicine = 0 AND stit_ID <> {$_POST['id']}");
                $matchinSKUs = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_SKU) FROM third_party_products  
                    WHERE stit_itemId = '{$itemName}' AND  stit_product_variant = '" . mysqli_real_escape_string($db->linker(), $_POST['stit_product_variant']) . "' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} AND isMedicine = 0 AND stit_ID <> {$_POST['id']}");
            }

            if ($IsItemNameUnique > 0 && $data['stit_itemName'] != 'Private Product') {

                $message = "This Item already existing.Matching SKU(s) are " . $matchinSKUs;
                echo "{success: false, msg: '{$message}'}";
                exit;
            }
            $SKUUnique = $db->getItemSafe("SELECT COUNT(*) from third_party_products  WHERE stit_SKU = '{$dSKU}'  AND stit_ID <> ?", "i", [$_POST['id']]);
            if ($SKUUnique > 0) {
                echo "{success: false, msg:'This SKU already existing.'}";
                exit;
            }
            $con = 'stit_ID=' . intval($_POST['id']);
            $data['updatedOn'] = date('Y-m-d H:i:s');
            $data['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform("third_party_products", $data, 'update', $con);

            if ($stit_productsForCountries[0] > 0) {
                $status = $db->query("DELETE FROM third_party_products_country WHERE stitc_stit_id = {$itemIdFrPt}");
                for ($i = 0; $i < count($stit_productsForCountries); $i++) {
                    $fsitcdata['stitc_stit_id'] = $itemIdFrPt;
                    $fsitcdata['stitc_country'] = $stit_productsForCountries[$i];
                    $status = $db->perform("third_party_products_country", $fsitcdata);
                }
            }
            $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM third_party_products WHERE stit_fsiuid = {$stit_fsiuid['fsi_uid']}");
            if ($stit_fsiuid['status'] == 'NEW') {
                $db->perform("third_party_products_uniqueitem", $uuit, 'update', " fsi_uid = {$stit_fsiuid['fsi_uid']}");
            }
        }

        $condit = 'stit_ID=' . intval($itemIdFrPt);
        $packingdata['updatedOn'] = date('Y-m-d H:i:s');
        $packingdata['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform("third_party_products", $packingdata, 'update', $condit);

        if (!empty($storeCode)) {
            $fstrd['fsipc_isCompany'] = 0;
            $fstrd['fsipc_storeGroup'] = $storeGroupId;
            $fstrd['fsipc_store'] = 0;
            $fstrd['fsipc_codeType'] = 'Store Store';
            $fstrd['fsipc_stit_id'] = $itemIdFrPt;
            $fstrd['fsipc_code'] = $storeCode;
            $isGlobalCodeExist = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id <> '{$fstrd['fsipc_stit_id']}' AND fsipc_storeGroup = {$storeGroupId}  AND fsipc_code = '{$storeCode}'");

            $fstrd['fsipc_createdOn'] = date("Y-m-d H:i");
            $fstrd['fsipc_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            //print_r($fstrd);
            if ($isGlobalCodeExist == 0)
                $status = $db->perform('finascop_stock_itemmaster_product_codes', $fstrd);
            else
                $message .= "Code already exists.";
        }

        if ($imageOptions == '2') {
            $images = [];

            for ($i = 1; $i <= 5; $i++) {
                $postKey = 'imgurl' . $i;
                if (!empty($_POST[$postKey])) {
                    $images[] = strtok($_POST[$postKey], '?');
                }
            }

            if (!empty($images)) {
                $s3upload = new cgoS3FileHandler();
                $cloudFrontPath = 'products/';
                $documentRoot = $_SERVER["DOCUMENT_ROOT"];
                $tmpDir = $documentRoot . '/tmp/importimages/';

                if (!is_dir($tmpDir)) {
                    if (!mkdir($tmpDir, 0777, true)) { // Recursive and permissive for temp files
                        error_log("Failed to create temporary directory: " . $tmpDir);
                        return;
                    }
                }

                foreach ($images as $imageUrl) {
                    if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                        error_log("Invalid image URL detected: " . $imageUrl);
                        continue;
                    }

                    $fileExtension = pathinfo(basename($imageUrl), PATHINFO_EXTENSION);
                    $fileuploadname = md5(uniqid(rand(), true)) . "." . $fileExtension;
                    $destinationPath = $tmpDir . $fileuploadname;

                    if (@copy($imageUrl, $destinationPath)) {
                        $isFileUploaded = $s3upload->putFileToS3($cloudFrontPath, AWSBUCKETUPLOADS, $destinationPath, $fileuploadname);

                        if ($isFileUploaded) {
                            $imdata = [
                                "product_id" => $itemIdFrPt,
                                "image_url" => $fileuploadname, // Store the filename on S3
                                "image_thumb_url" => '', // Assuming this is handled elsewhere or not used
                                "image_type" => 1,
                                "bucket_name" => AWSBUCKETUPLOADS, // Store the bucket name for future reference
                                "createdBy" => $_SESSION['admin']->Finascop_UserId,
                                "created_at" => date("Y-m-d H:i:s"),
                                "image_folder" => 'products/' // Store the folder on S3
                            ];

                            try {
                                $status = $db->perform('thirdparty_item_images', $imdata);
                                if (!$status) {
                                    error_log("Failed to insert image data into DB for product_id: {$itemIdFrPt}, filename: {$fileuploadname}");
                                }
                            } catch (Exception $e) {
                                error_log("Database error inserting image data for product_id {$itemIdFrPt}: " . $e->getMessage());
                            }
                        } else {
                            error_log("S3 upload failed for image: {$imageUrl} to {$cloudFrontPath}{$fileuploadname}");
                        }

                        if (file_exists($destinationPath)) {
                            unlink($destinationPath);
                        }
                    } else {
                        error_log("Failed to copy image from URL: {$imageUrl} to local path: {$destinationPath}");
                    }
                }
            }
        }

        $db->query('commit');
        exportMerchantProduct($itemIdFrPt);
        if ($status) {
            echo "{success: true,imageOptions:{$imageOptions},stit_ID:{$itemIdFrPt},msg:'{$message}'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data' }";
        }

        break;
}
