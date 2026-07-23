<?php
require_once(ROOT . '/finascop_config/lib.php');
define('AWS_ROOT', '/home/system/awsapi');
require(AWS_ROOT . '/aws-autoloader.php');
require_once(INCLUDE_PATH . "/finascop_common_functions.php");

use Aws\S3\S3Client;
use Shuchkin\SimpleXLSX;

require dirname(__FILE__, 4) . "/externallibs/vendor/simplexlsx-master/src/SimpleXLSX.php";
function syncProductToStore($productId)
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
stit_HSN_code, stit_HSNCode, stit_hsnId, stit_ID, stit_ingredients, stit_item_volume, stit_itemBarcode, stit_itemERPId, stit_itemId,
stit_itemName, stit_itemReturnTime, stit_long_description, stit_Min_Stock, stit_MRP, stit_nutritionlabel, stit_OpeningStock,
stit_orgin_country, stit_package_master, stit_package_type_id, stit_package_type_namme, stit_ParentItemId, stit_PeriodFrom,
stit_PeriodTo, stit_preparation_use, stit_product_variant, stit_PurchaseEnabled, stit_qty, stit_quantity, stit_safety_warning,
stit_SalesEnabled, stit_salesUnit, stit_SKU, stit_status, stit_stdPacking, stit_StockEnabled, stit_storage_instruction,
stit_StoreGroup, stit_Tangible, stit_unit, stit_updatedOn, stit_warning, stitl1_optimumqty, stitl2_optimumqty, stitl3_optimumqty,
taxValueId, updatedBy FROM third_party_products WHERE stit_ID = {$ItemId}", true);
    //print_r($data);stit_displaylabel
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
    $prdctsubcat = $parentdb->getItemFromDB("SELECT sub_category_id FROM mypha_productsubcategory WHERE sub_category = '{$gs1SubCateg}'");
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
    if (!empty($data['gtin'])) {
        $fstrd['fsipc_isCompany'] = 0;
        $fstrd['fsipc_isIndividual'] = 0;
        $fstrd['fsipc_storeGroup'] = $data['stit_StoreGroup'];
        $fstrd['fsipc_store'] = $branchId;
        $fstrd['fsipc_stit_id'] = $stitId;
        $fstrd['fsipc_code'] = $data['gtin'];
        $fstrd['fsipc_codeType'] = 0;
        $isExists = $parentdb->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes 
    WHERE fsipc_stit_id = '{$fstrd['fsipc_stit_id']}' AND fsipc_code = '{$fstrd['fsipc_code']}' AND fsipc_store = {$branchId}");
        if ($isExists == 0) {
            $fstrd['fsipc_createdOn'] = date("Y-m-d H:i");
            $fstrd['fsipc_createdBy'] = $_SESSION['admin']->Finascop_UserId;

            $status = $parentdb->perform('finascop_stock_itemmaster_product_codes', $fstrd);
            $fsipcs['fsipc_id'] = $parentdb->insert_id();

            $fsipcs['fsipc_stit_id'] = $stitId;
            $fsipcs['fsipcs_store'] = $fstrd['fsipc_store'];
            $fsipcs['fsipcs_Code'] = $fstrd['fsipc_code'];
            $status = $parentdb->perform('finascop_stock_itemmaster_product_code_stores', $fsipcs);
        }
    }

    $expLog['grozeo_stitId'] = $stitId;
    $expLog['stit_updatedOn'] = date("Y-m-d H:i:s");
    $expLog['verifedBy'] = $_SESSION['admin']->Finascop_UserId;
    $status = $db->perform('third_party_products', $expLog, 'update', " stit_ID = {$productId}");





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

function updateTPUniqueItemTablefn($uid, $uniData)
{
    global $db;
    $uniData['fsi_category_id'] = addslashes($uniData['fsi_category_id']);
    $chkUnqExiste = $db->getFromDB("SELECT fsi_uid,fsi_count FROM third_party_products_uniqueitem WHERE fsi_item_id = {$uniData['fsi_item_id']} "
        . "  AND fsi_brand_id = {$uniData['fsi_brand_id']} AND fsi_category_id = {$uniData['fsi_category_id']} AND fsi_variant = '{$uniData['fsi_variant']}'", true);
    $uidCount = $db->getItemFromDB("SELECT fsi_count FROM third_party_products_uniqueitem WHERE fsi_uid = {$uid}");

    if ($uid == 0) {

        if (intval($chkUnqExiste['fsi_uid']) > 0) {
            $fsuidata['fsi_count'] = intval($chkUnqExiste['fsi_count']) + 1;
            $status = $db->perform("third_party_products_uniqueitem", $fsuidata, 'update', " fsi_uid = {$chkUnqExiste['fsi_uid']}");
            $stit_fsiuid['fsi_uid'] = $chkUnqExiste['fsi_uid'];
        } else {

            $fsuidata = $uniData;
            $fsuidata['fsi_count'] = 1;
            $ustatus = $db->perform("third_party_products_uniqueitem", $fsuidata);
            $stit_fsiuid['fsi_uid'] = $db->insert_id();
            $stit_fsiuid['status'] = 'NEW';
        }
    } else {
        if (intval($chkUnqExiste['fsi_uid']) > 0) {
            if (intval($chkUnqExiste['fsi_uid']) != $uid) {

                $fsuidata['fsi_count'] = intval($uidCount) - 1;
                $status = $db->perform("third_party_products_uniqueitem", $fsuidata, 'update', " fsi_uid = {$uid}");
                $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM third_party_products WHERE stit_fsiuid = {$uid}");
                $db->perform("third_party_products_uniqueitem", $uuit, 'update', " fsi_uid = {$uid}");


                $updatCou['fsi_count'] = intval($chkUnqExiste['fsi_count']) + 1;
                $status = $db->perform("third_party_products_uniqueitem", $updatCou, 'update', " fsi_uid = {$chkUnqExiste['fsi_uid']}");
                $stit_fsiuid['fsi_uid'] = $chkUnqExiste['fsi_uid'];
            } else {
                $stit_fsiuid['fsi_uid'] = $uid;
            }
        } else {

            $fsdata['fsi_count'] = intval($uidCount) - 1;
            $status = $db->perform("third_party_products_uniqueitem", $fsdata, 'update', " fsi_uid = {$uid}");
            $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM third_party_products WHERE stit_fsiuid = {$uid}");
            $db->perform("third_party_products_uniqueitem", $uuit, 'update', " fsi_uid = {$uid}");

            $fsuidata = $uniData;
            $fsuidata['fsi_count'] = 1;
            $ustatus = $db->perform("third_party_products_uniqueitem", $fsuidata);
            $stit_fsiuid['fsi_uid'] = $db->insert_id();
            $stit_fsiuid['status'] = 'NEW';
        }
    }
    return $stit_fsiuid;
}

function removeNonUtf8($string)
{
    // First, normalize the string to NFC form (canonical composition)
    $string = Normalizer::normalize($string, Normalizer::FORM_C);

    // Remove non-UTF-8 characters using a regular expression
    $pattern = '/
        (
            [\x00-\x7F]                  # ASCII characters (1 byte)
            | [\xC2-\xDF][\x80-\xBF]     # UTF-8 2-byte sequences
            | \xE0[\xA0-\xBF][\x80-\xBF] # UTF-8 3-byte sequences starting with E0
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # UTF-8 3-byte sequences
            | \xED[\x80-\x9F][\x80-\xBF] # UTF-8 3-byte sequences starting with ED
            | \xF0[\x90-\xBF][\x80-\xBF]{2} # UTF-8 4-byte sequences starting with F0
            | [\xF1-\xF3][\x80-\xBF]{3}   # UTF-8 4-byte sequences
            | \xF4[\x80-\x8F][\x80-\xBF]{2} # UTF-8 4-byte sequences starting with F4
        )
        (?!\xCC\x87)  # Exclude the combining dot above character (\u0307)
    /x';

    // Remove non-UTF-8 characters using the regex pattern
    $string = preg_replace($pattern, '$1', $string);

    // Normalize and convert to UTF-8 to ensure proper encoding
    $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

    // Remove any invalid characters that might remain
    $string = preg_replace('/[^\x20-\x7E\xA0-\xFF\xC0-\xDF\xE0-\xEF\xF0-\xF7\x80-\xFF]/u', '', $string);


    // Remove any remaining invalid UTF-8 sequences (non-printable characters or unencoded ones)
    $string = preg_replace('/[^\p{L}\p{N}\s\p{P}\p{M}]/u', '', $string);

    // Remove any combining diacritical marks (e.g., accents, umlauts)
    $string = preg_replace('/\p{Mn}/u', '', $string);

    return $string;
}



// Function to convert XLSX to JSON
function xlsxToJson($xlsxFile)
{
    $xlsx = new SimpleXMLElement($xlsxFile, 0, true);
    $sheetData = $xlsx->sheets->sheet->data;

    $dataArray = array();

    foreach ($sheetData->row as $row) {
        $rowData = array();
        foreach ($row->cell as $cell) {
            $rowData[] = (string) $cell;
        }
        $dataArray[] = $rowData;
    }

    return json_encode($dataArray);
}
switch ($op) {
    case 'listproductBankUpload':
        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 21 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'desc' : $data['dir'];
        $search = ' WHERE 1=1';
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $searchitem .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                }
            }
        }

        if ($rec_sort == 'createdOn') {
            $rec_sort = 'id';
        }
        if ($data['showAll'] == 0)
            $search .= " AND balanceCount > 0 OR feedbackedCount > 0";
        $mainQuery = "SELECT 
    psum.id,
    psum.createdBy,
    CONCAT(pup.FirstName, ' ', pup.LastName) AS createdByName,
    IF(psum.TYPE = 1, 'Product', 'Image') AS typeName,
    IF(psum.isScrap = 1, 
       CONCAT(psum.brand, ' - ', 'Store Data'), 
       CONCAT(psum.brand, ' - ', 'Brand Data')) AS brand,
    DATE_FORMAT(psum.createdOn, '%d-%m-%Y %H:%i:%s') AS createdOn,
    psum.count AS totalCount,

    IFNULL(stats.actualCount, 0) AS actualCount,
    IFNULL(stats.feedbackedCount, 0) AS feedbackedCount,
    IFNULL(stats.removedCount, 0) AS removedCount,
    IFNULL(stats.notAvailableCount, 0) AS notAvailableCount,
    IFNULL(stats.mappedCount, 0) AS mappedCount,
    IFNULL(stats.balanceCount, 0) AS balanceCount,

    psum.count - IFNULL(stats.balanceCount, 0) AS convertedCount

FROM 
    product_source_upload_master psum
INNER JOIN 
    finascop_usr_profile pup ON pup.UserId = psum.createdBy

LEFT JOIN 
(
    SELECT 
        g.uploadedFrom,

        COUNT(*) AS actualCount,

        SUM(CASE WHEN g.isValid = 0 AND g.isArchived = 7 AND u.sourceProductId IS NULL THEN 1 ELSE 0 END) AS feedbackedCount,
        SUM(CASE WHEN g.isValid = 1 AND g.isArchived = 5 THEN 1 ELSE 0 END) AS removedCount,
        SUM(CASE WHEN g.isValid = 1 AND g.isArchived <> 5 THEN 1 ELSE 0 END) AS notAvailableCount,
        
        COUNT(u.sourceProductId) AS mappedCount,

        SUM(CASE 
            WHEN g.isValid = 0 AND g.isArchived <> 7 AND u.sourceProductId IS NULL 
            THEN 1 ELSE 0 END
        ) AS balanceCount

    FROM gs1_products_source g
    LEFT JOIN upload_product_map u ON g.id = u.sourceProductId

    GROUP BY g.uploadedFrom

) stats ON stats.uploadedFrom = psum.id";
        $countQuery = "SELECT COUNT(*) FROM ({$mainQuery}) AS uploadCount {$search} {$searchitem}";
        $count = $db->getItemFromDB($countQuery);
        $listQuery = " SELECT * FROM ({$mainQuery}) AS uploadList {$search}  {$searchitem} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';

        // $db->printGridJson($countQuery, $listQuery);
        break;
    case 'uploadStockcsvFile':
        $file = $_FILES['excel_file']['tmp_name'];
        $newPath = str_replace('tmp', 'dev/shm', $file);
        copy($file, $newPath);

        //$spreadsheet = SimpleXLSX::parse($newPath);
        if ($xlsx = SimpleXLSX::parse($newPath)) {
            $dataArray = $xlsx->rows();
            $headings = array_shift($dataArray);
            $dataWithHeadings = [];
            foreach ($dataArray as $rowData) {
                // Combine headings with row data to create an associative array
                $rowWithHeadings = array_combine($headings, $rowData);
                // Add the row to the result array
                $dataWithHeadings[] = $rowWithHeadings;
            }
        }
        $jsonData = $dataWithHeadings; // Set the second parameter to true for associative array, or omit for object
        $jsonString = json_encode($jsonData);
        // Check if decoding was successful
        if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
            // JSON decoding failed
            $message =  "Error decoding JSON: " . json_last_error_msg();
            echo '{"success":true,"valid":false,"error":"{$message}"}';
            exit();
        } else {
            $db->query('begin');
            //$input_data = iconv("UTF-8","UTF-8//IGNORE",$jsonString);
            $updateCount = count($jsonData);
            $fsbiu['createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $fsbiu['createdOn'] = date("Y-m-d H:i:s");
            $fsbiu['data'] = $jsonString;
            $fsbiu['count'] = $updateCount;
            $fsbiu['brand'] = $_POST['upload_brand'];
            //print_r($fsbiu);
            $status = $db->perform('product_source_upload_master', $fsbiu);
            $lastId = $db->insert_id();
            $invalidErps = [];
            foreach ($jsonData as $value) {
                $gs1DupData['unique_key'] = md5(json_encode($value));
                $gs1DupData['uploadedFrom'] = $lastId;
                $brandName = removeNonUtf8($value['Brand']);
                $companyName = removeNonUtf8($value['Company Name']);
                $categoryName = removeNonUtf8($value['Product Group 2']);
                $subcategoryName = removeNonUtf8($value['Product Group 3']);

                $qry = 'select * from gs1_brand_source where brandName = "' . $brandName . '"';
                $tmpsdata = $db->getMultipleData($qry, true);
                if ($tmpsdata) {
                    $gs1DupData['brandId'] = $tmpsdata[0]['id'];
                    $gs1DupData['brand'] = $brandName;
                } else {
                    $insertQuery = 'INSERT INTO gs1_brand_source (brandName) VALUES ("' . $brandName . '")';
                    $tmpsdata = $db->query($insertQuery);
                    $gs1DupData['brandId'] = $db->insert_id();
                    $gs1DupData['brand'] = $brandName;
                }
                $companyId = $db->getItemFromDB('SELECT id FROM gs1_company WHERE companyName = "' . $companyName . '"');
                if ($companyId > 0) {
                    //$gs1DupData["companyId"] = $companyId;
                    $gs1DupData["company_detail"] = $companyName;
                } else {
                    $insertQuery = 'INSERT INTO gs1_company (companyName) VALUES ("' . $companyName . '")';
                    $tmpsdata = $db->query($insertQuery);
                    //$gs1DupData["companyId"] = $db->insert_id();
                    $gs1DupData["company_detail"] = $companyName;
                }
                $categoryId = $db->getItemFromDB('SELECT id FROM gs1_category where categoryName = "' . $categoryName . '"');
                if ($categoryId > 0) {
                    $gs1DupData["categoryId"] = $categoryId;
                    $gs1DupData["category"] = $categoryName;
                } else {
                    $insertQuery = 'INSERT INTO gs1_category (categoryName) VALUES ("' . $categoryName . '")';
                    $tmpsdata = $db->query($insertQuery);
                    $categoryId = $db->insert_id();
                    $gs1DupData["categoryId"] = $categoryId;
                    $gs1DupData["category"] = $categoryName;
                }
                $subCategoryId = $db->getItemFromDB('SELECT id FROM gs1_subCategory where categoryId = "' . $categoryId . '" AND subCategoryName = "' . $subcategoryName . '"');
                if ($subCategoryId > 0) {
                    $gs1DupData["subCategoryId"] = $subCategoryId;
                    $gs1DupData["sub_category"] = $subcategoryName;
                } else {
                    $insertQuery = 'INSERT INTO gs1_subCategory (subCategoryName,categoryId) VALUES ("' . $subcategoryName . '","' . $categoryId . '")';
                    $tmpsdata = $db->query($insertQuery);
                    $subCategoryId = $db->insert_id();
                    $gs1DupData["subCategoryId"] = $subCategoryId;
                    $gs1DupData["sub_category"] = $subcategoryName;
                }
                $name = removeNonUtf8($value['Description']);
                $gs1DupData["type"] = $value['Product Group 1'];
                $gs1DupData["name"] = $name;
                $gs1DupData["gtin"] = $value['GTIN'];
                $gs1DupData["caution"] = removeNonUtf8($value['Safety Warnings']);
                $gs1DupData["variant"] = removeNonUtf8($value[' Variant']);
                $gs1DupData["sku_code"] = $value['PVID'];
                $description = $value['Further Description'] . $value['Other Information'] . $value['Trademark Information'];
                $gs1DupData["description"] = removeNonUtf8($description);
                $gs1DupData["gpc_code"] = $value['Subscriber Code'];
                $gs1DupData["marketing_info"] = removeNonUtf8($value['Product Marketing']);
                $gs1DupData["url"] = $value['Web Address'];
                $gs1DupData["activation_date"] = $value['Version Date'];
                //$gs1DupData["deactivation_date"] = $gs1Data['deactivation_date'];
                $derived_description = $value['Features'] . $value['Brand Marketing'] . $value['Manufacturer Marketing'];
                $gs1DupData["derived_description"] = removeNonUtf8($derived_description);
                $gs1DupData["country_of_origin"] = str_replace('Country of Origin - ', '', $value['Country']);
                //$gs1DupData["created_date"] = $gs1Data['created_date'];
                //$gs1DupData["modified_date"] = $gs1Data['modified_date'];

                $gs1DupData["packaging_type"] = $value['Storage Type'];
                $gs1DupData["published"] = $value['Regulated Product Name'];
                $weights_and_measures = mb_convert_encoding($value['Preparation and Usage'], 'UTF-8', 'UTF-8');
                $gs1DupData["weights_and_measures"] = removeNonUtf8($weights_and_measures);
                $gs1DupData["dimensions"] = removeNonUtf8($value['Storage']);
                $gs1DupData["case_configuration"] = removeNonUtf8($value['Manufacturers Address']);
                /*$gs1DupData["hs_code"] = $gs1Data['hs_code'];
                $gs1DupData["igst"] = $gs1Data['igst'];
                $gs1DupData["cgst"] = $gs1Data['cgst'];
                $gs1DupData["sgst"] = $gs1Data['sgst'];*/
                $gs1DupData["margin"] = $value['Net Content'];
                $Ingredients = mb_convert_encoding($value['Ingredients'], 'UTF-8', 'UTF-8');
                $gs1DupData["attributes"] = removeNonUtf8($Ingredients);
                $gs1DupData["additional_attributes"] = removeNonUtf8($value['Nutrition']);
                /*$gs1DupData["image_front"] = $gs1Data['image_front'];
                $gs1DupData["image_back"] = $gs1Data['image_back'];
                $gs1DupData["image_top"] = $gs1Data['image_top'];
                $gs1DupData["image_bottom"] = $gs1Data['image_bottom'];
                $gs1DupData["image_left"] = $gs1Data['image_left'];
                $gs1DupData["image_right"] = $gs1Data['image_right'];
                $gs1DupData["image_top_left"] = $gs1Data['image_top_left'];
                $gs1DupData["image_top_right"] = $gs1Data['image_top_right'];*/
                $gs1DupData["importedOn"] = date('Y-m-d H:i:s');

                /*$gs1DupData["retailCategory"] = $retailCategory;
                $gs1DupData["department"] = $department;
                $gs1DupData["categoryGr"] = $category;
                $gs1DupData["subCategoryGr"] = $subCategory;
                $gs1DupData["isMastersMapped"] = 1;
                $gs1DupData["id"] = $gs1Data['id'];*/
                // print_r($gs1DupData);exit();
                $isGtinAvailable = $db->getItemFromDB("SELECT id FROM gs1_products_source WHERE gtin = {$gs1DupData["gtin"]}");
                if ($isGtinAvailable > 0) {
                    $gs1DupData["updatedOn"] = date('Y-m-d H:i:s');
                    $status = $db->perform('gs1_products_source', $gs1DupData, 'update', " id = {$isGtinAvailable}");
                } else {
                    $status = $db->perform('gs1_products_source', $gs1DupData);
                }
            }
            $status = $db->query('commit');
            if ($status == 1) {
                $dispMsg = "Going to save {$updateCount} Items";

                echo '{"success":true,"valid":true,"msg":"' . $dispMsg . '","fbiu_id":' . $lastId . '}';
            } else {
                // var_dup($error);
                echo '{"success":false,"valid":false}';
            }
        }
        break;
    case 'listProductUploadedItems':
        $data = $_POST;
        $rec_sort = empty($data['sort']) ? 'id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' AND 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(*) from gs1_products_source WHERE uploadedFrom = '{$_POST['fbiu_id']}' {$filter_part}";
        $listQuery = "SELECT id,brand,name,gtin,type,category,sub_category,hs_code,igst,sku_code from gs1_products_source WHERE uploadedFrom = '{$_POST['fbiu_id']}' {$filter_part}  ORDER BY CAST({$rec_sort} as char) {$rec_sort_dir},binary {$rec_sort} {$rec_sort_dir}  ";
        //$db->printGridJson($countQuery, $listQuery);
        $count = $db->getItemFromDB($countQuery);
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
            //echo json_encode($qry);
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        break;
    case 'uploadScrapcsvFile':
        $file = $_FILES['excel_file']['tmp_name'];
        $newPath = str_replace('tmp', 'dev/shm', $file);
        copy($file, $newPath);

        //$spreadsheet = SimpleXLSX::parse($newPath);
        if ($xlsx = SimpleXLSX::parse($newPath)) {
            $dataArray = $xlsx->rows();
            $headings = array_shift($dataArray);
            $dataWithHeadings = [];
            foreach ($dataArray as $rowData) {
                // Combine headings with row data to create an associative array
                $rowWithHeadings = array_combine($headings, $rowData);
                // Add the row to the result array
                $dataWithHeadings[] = $rowWithHeadings;
            }
        }
        $jsonData = $dataWithHeadings; // Set the second parameter to true for associative array, or omit for object
        $jsonString = json_encode($jsonData);
        // Check if decoding was successful
        if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
            // JSON decoding failed
            $message =  "Error decoding JSON: " . json_last_error_msg();
            echo '{"success":true,"valid":false,"error":"{$message}"}';
            exit();
        } else {
            $db->query('begin');
            //$input_data = iconv("UTF-8","UTF-8//IGNORE",$jsonString);
            $updateCount = count($jsonData);
            $fsbiu['createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $fsbiu['createdOn'] = date("Y-m-d H:i:s");
            $fsbiu['data'] = $jsonString;

            $fsbiu['isScrap'] = 1;
            $fsbiu['brand'] = $_POST['upload_brand'];
            $fsbiu['storeGroupId'] = ($_POST['merchantId'] > 0 ? $_POST['merchantId']  : 0);
            //print_r($fsbiu);
            $status = $db->perform('product_source_upload_master', $fsbiu);
            $lastId = $db->insert_id();
            $invalidErps = [];
            $arraKey = 0;
            if ($fsbiu['storeGroupId'] > 0) {
                $uploadIds =  $db->getItemFromDB("SELECT GROUP_CONCAT(id) FROM product_source_upload_master WHERE storeGroupId = {$fsbiu['storeGroupId']}");
            }

            foreach ($jsonData as $value) {
                $arraKey = $arraKey + 1;
                $gs1DupData['unique_key'] = md5(json_encode($value));
                $gs1DupData['uploadedFrom'] = $lastId;
                $gs1DupData['storeGroupId'] = $fsbiu['storeGroupId'];
                $gs1DupData["gtin"] = time() . $arraKey;
                $brandName = removeNonUtf8($value['brand']);
                $companyName = removeNonUtf8($value['company']);
                $categoryName = removeNonUtf8($value['category']);
                $subcategoryName = removeNonUtf8($value['subcategory']);
                $skuFlag = 0;
                $name = removeNonUtf8($value['sku']);
                if (!empty($uploadIds)) {
                    $skuIsExists = $db->getItemFromDB("SELECT COUNT(*) FROM gs1_products_source WHERE uploadedFrom IN ({$uploadIds}) AND REPLACE(name, ' ', '') = REPLACE('{$name}', ' ', '') AND isArchived <> 5");
                    if ($skuIsExists > 0)
                        $skuFlag = 1;
                    else
                        $skuFlag = 0;
                }
                if ($skuFlag == 0) {
                    $qry = 'select * from gs1_brand_source where brandName = "' . $brandName . '"';
                    $tmpsdata = $db->getMultipleData($qry, true);
                    if ($tmpsdata) {
                        $gs1DupData['brandId'] = $tmpsdata[0]['id'];
                        $gs1DupData['brand'] = $brandName;
                    } else {
                        $insertQuery = 'INSERT INTO gs1_brand_source (brandName,isEnabled) VALUES ("' . $brandName . '","1")';
                        $tmpsdata = $db->query($insertQuery);
                        $gs1DupData['brandId'] = $db->insert_id();
                        $gs1DupData['brand'] = $brandName;
                    }
                    $companyId = $db->getItemFromDB('SELECT id FROM gs1_company WHERE companyName = "' . $companyName . '"');
                    if ($companyId > 0) {
                        //$gs1DupData["companyId"] = $companyId;
                        $gs1DupData["company_detail"] = $companyName;
                    } else {
                        $insertQuery = 'INSERT INTO gs1_company (companyName) VALUES ("' . $companyName . '")';
                        $tmpsdata = $db->query($insertQuery);
                        //$gs1DupData["companyId"] = $db->insert_id();
                        $gs1DupData["company_detail"] = $companyName;
                    }
                    $categoryId = $db->getItemFromDB('SELECT id FROM gs1_category where categoryName = "' . $categoryName . '"');
                    if ($categoryId > 0) {
                        $gs1DupData["categoryId"] = $categoryId;
                        $gs1DupData["category"] = $categoryName;
                    } else {
                        $insertQuery = 'INSERT INTO gs1_category (categoryName) VALUES ("' . $categoryName . '")';
                        $tmpsdata = $db->query($insertQuery);
                        $categoryId = $db->insert_id();
                        $gs1DupData["categoryId"] = $categoryId;
                        $gs1DupData["category"] = $categoryName;
                    }
                    $subCategoryId = $db->getItemFromDB('SELECT id FROM gs1_subCategory where categoryId = "' . $categoryId . '" AND subCategoryName = "' . $subcategoryName . '"');
                    if ($subCategoryId > 0) {
                        $gs1DupData["subCategoryId"] = $subCategoryId;
                        $gs1DupData["sub_category"] = $subcategoryName;
                    } else {
                        $insertQuery = 'INSERT INTO gs1_subCategory (subCategoryName,categoryId) VALUES ("' . $subcategoryName . '","' . $categoryId . '")';
                        $tmpsdata = $db->query($insertQuery);
                        $subCategoryId = $db->insert_id();
                        $gs1DupData["subCategoryId"] = $subCategoryId;
                        $gs1DupData["sub_category"] = $subcategoryName;
                    }
                    $gs1DupData['hs_code'] = $value['HSN'];
                    $gs1DupData['igst'] = $value['GST'];
                    $gs1DupData['sku_code'] = $value['Code'];

                    $gs1DupData["type"] = $categoryName;
                    $gs1DupData["name"] = $name;
                    $description = $value['specification'] . $value['long_description'];
                    $gs1DupData["description"] = removeNonUtf8($description);
                    $gs1DupData["url"] = $value['source_url'];
                    $derived_description = $value['short_description'];
                    $gs1DupData["derived_description"] = removeNonUtf8($derived_description);
                    $gs1DupData["country_of_origin"] = $value['country'];
                    $gs1DupData["case_configuration"] = $companyName;
                    $gs1DupData['image_front'] = $value['image'];
                    $gs1DupData['isValid'] = 0;
                    $subImages = $value['sub_image'];
                    preg_match_all('/<img[^>]+src="([^">]+)"/', $subImages, $matches);
                    $image_fields = [
                        'image_back',
                        'image_top',
                        'image_bottom',
                        'image_left',
                        'image_right',
                        'image_top_left'
                    ];
                    // Get the src values
                    $src_images = $matches[1];

                    // Initialize an empty array to store mapped image URLs
                    $urls = [];
                    // Check if any matches were found and extract the URLs
                    if (isset($matches[1]) && count($matches[1]) > 0) {
                        $urls = $matches[1];
                    }
                    // Map each field to the corresponding URL, or set to null if not enough URLs
                    foreach ($image_fields as $index => $field) {
                        $gs1DupData[$field] = $urls[$index] ?? null;
                    }
                    $gs1DupData["importedOn"] = date('Y-m-d H:i:s');

                    $isAvailable = $db->getItemFromDB("SELECT id FROM gs1_products_source WHERE unique_key = '{$gs1DupData["unique_key"]}'");
                    if ($isAvailable > 0) {
                        $gs1DupData["updatedOn"] = date('Y-m-d H:i:s');
                        $status = $db->perform('gs1_products_source', $gs1DupData, 'update', " id = {$isAvailable}");
                    } else {
                        $status = $db->perform('gs1_products_source', $gs1DupData);
                    }
                }
            }
            $updateCount = $db->getItemFromDB("SELECT COUNT(*) FROM gs1_products_source WHERE uploadedFrom = {$lastId}");
            $upd['count'] = $updateCount;
            $status = $db->perform('product_source_upload_master', $upd, 'update', " id = {$lastId}");
            $status = $db->query('commit');
            if ($status == 1) {
                $dispMsg = "Going to save {$updateCount} Items";

                echo '{"success":true,"valid":true,"msg":"' . $dispMsg . '","fbiu_id":' . $lastId . '}';
            } else {
                // var_dup($error);
                echo '{"success":false,"valid":false}';
            }
        }
        break;
    case 'uploadExcelFile':
        $file = $_FILES['excel_file']['tmp_name'];
        $newPath = str_replace('tmp', 'dev/shm', $file);
        copy($file, $newPath);

        //$spreadsheet = SimpleXLSX::parse($newPath);
        if ($xlsx = SimpleXLSX::parse($newPath)) {
            $dataArray = $xlsx->rows();
            $headings = array_shift($dataArray);
            $dataWithHeadings = [];
            foreach ($dataArray as $rowData) {
                // Combine headings with row data to create an associative array
                $rowWithHeadings = array_combine($headings, $rowData);
                // Add the row to the result array
                $dataWithHeadings[] = $rowWithHeadings;
            }
        }
        $jsonData = $dataWithHeadings; // Set the second parameter to true for associative array, or omit for object
        $jsonString = json_encode($jsonData);
        // Check if decoding was successful
        if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
            // JSON decoding failed
            $message =  "Error decoding JSON: " . json_last_error_msg();
            echo '{"success":true,"valid":false,"error":"{$message}"}';
            exit();
        } else {
            $db->query('begin');
            $updateCount = count($jsonData);
            $fsbiu['createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $fsbiu['createdOn'] = date("Y-m-d H:i:s");
            $fsbiu['data'] = $jsonString;
            $fsbiu['count'] = $updateCount;
            $fsbiu['brand'] = $_POST['merchantName'];
            $fsbiu['storeGroupId'] = $_POST['merchantId'];

            $status = $db->perform('product_source_upload_master', $fsbiu);
            $uploadId = $db->insert_id();
            $invalidErps = [];
            $arraKey = 0;

            foreach ($jsonData as $value) {
                $validation = '';
                $hsnCount = 0;
                $gstCount = 0;
                $hsnCount = 0;
                $brandCount = 0;
                $categCount = 0;
                $unitCount = 0;
                $countryCount = 0;
                $data['SI_No'] = $value['SI_No'];
                $data['Barcode'] = $value['Barcode'];
                $data['SKU'] = $value['SKU'];
                $data['HSN_code'] = $value['HSN_code'];
                $data['GST'] = $value['GST'];
                $data['hsnId'] = $db->getItemFromDB("SELECT hsn_id FROM finascop_hsn WHERE hsn_code = '{$value['HSN_code']}'");
                if ($data['hsnId'] > 0)
                    $data['gstId'] = $db->getItemFromDB("SELECT id FROM hsn_value WHERE hsnId = {$data['hsnId']}");
                $data['Short_description'] = $value['Short_description'];
                $data['Description'] = $value['Description'];
                $data['Product_Variant'] = $value['Product_Variant'];
                $data['Brand_Name'] = $value['Brand_Name'];
                $data['BrandId'] = $db->getItemFromDB("SELECT brand_id FROM mypha_productbrands WHERE brand_name = '{$value['Brand_Name']}'");
                $data['Category_Name'] = $value['Category_Name'];
                $data['CategoryId'] = $db->getItemFromDB("SELECT sub_category_id FROM mypha_productsubcategory WHERE sub_category = '{$value['Category_Name']}'");
                $data['Featured'] = $value['Featured'];
                $data['Popular'] = $value['Popular'];
                $data['Quantity'] = $value['Quantity'];
                $data['Unit'] = $value['Unit'];
                $data['UnitId'] = $db->getItemFromDB("SELECT unit_id FROM mypha_unit WHERE unit_name  = '{$value['Unit']}'");
                $data['Courier_Delivery'] = $value['Courier_Delivery'];
                $data['Direct_Delivery'] = $value['Direct_Delivery'];
                $data['FoodType'] = $value['Food_Type'];
                $data['CountryOfOrgin'] = $value['CountryOfOrgin'];
                $data['countryId'] = $db->getItemFromDB("SELECT country_id FROM finascop_country WHERE country_name  = '{$value['CountryOfOrgin']}'");
                $data['ProductWeight'] = $value['ProductWeight'];
                $data['merchantId'] = $_POST['merchantId'];
                $data['createdBy'] = $_SESSION['admin']->Finascop_UserId;
                $data['createdOn'] = date("Y-m-d H:i:s");
                $data['uid'] = $uploadId;

                if (empty($data['hsnId']) || $data['hsnId'] == 0) {
                    $validation .= "HSN Missing.";
                    $hsnCount++;
                }
                if (empty($data['gstId']) || $data['gstId'] == 0) {
                    $validation .= "GST Missing.";
                    $gstCount++;
                }
                if (empty($data['BrandId']) || $data['BrandId'] == 0) {
                    $validation .= "Brand Missing.";
                    $brandCount++;
                }
                if (empty($data['CategoryId']) || $data['CategoryId'] == 0) {
                    $validation .= "Category Missing.";
                    $categCount++;
                }
                /*if (empty($data['UnitId']) || $data['UnitId'] == 0) {
                    $validation .= "Unit Missing.";
                    $unitCount++;
                }*/
                if (empty($data['countryId']) || $data['countryId'] == 0) {
                    $validation .= "Country of Orgin Missing.";
                    $countryCount++;
                }

                $SKUUnique = $db->getItemFromDB("SELECT COUNT(*) from  product_import_tmp  WHERE SKU = '{$value['SKU']}' AND  merchantId = {$data['merchantId']}");
                if ($SKUUnique > 0) {
                    $validation .= "Duplicate SKU.";
                }
                if (!empty($validation))
                    $isVerified = 0;
                else
                    $isVerified = 1;
                $data['isVerified'] = $isVerified;
                $data['validation'] = $validation;

                $datafsi = array_filter($data, 'strlen');
                $status = $db->perform("product_import_tmp", $datafsi);
                $status = $db->query('commit');
            }

            $dispMsg = "Confirm products after updating unverified products.";
            if ($status == 1) {
                echo '{"success":true,"valid":true,"uploadId":"' . $uploadId . '","msg":"' . $dispMsg . '"}';
            } else {
                echo '{"success":false,"valid":false}';
            }
        }
        break;
    case 'cofirmProductImport':
        $id = $_POST['id'];
        $productData = $db->getMultipleData("SELECT Barcode,Brand_Name,BrandId,Category_Name,CategoryId,countryId,CountryOfOrgin,
        Courier_Delivery,createdBy,createdOn,Description,Direct_Delivery,Featured,FoodType,GST,
        gstId,HSN_code,hsnId,id,isVerified,merchantId,Popular,Product_Variant,ProductWeight,
        Quantity,Short_description,SI_No,SKU,uid,Unit,UnitId,validation from product_import_tmp WHERE isVerified = 1 and  uid ={$id}", true);
        foreach ($productData as $value) {

            switch ($value['Food_Type']) {
                case 'Edible - Vegetarian':
                    $foodtype = 1;
                    break;
                case 'Edible - Non Vegetarian':
                    $foodtype = 2;
                    break;
                case 'Edible - Vegan':
                    $foodtype = 3;
                    break;
                case 'Edible':
                    $foodtype = 4;
                    break;
                default:
                    $foodtype = 0;
                    break;
            }
            $data['stit_displaylabel'] = $value['SI_No'];
            $data['stit_SKU'] = $value['SKU'];
            $data['gtin'] = $value['Barcode'];
            $data['stit_HSNCode'] = $value['HSN_code'];
            $data['stit_HSN_code'] = $value['HSN_code'];
            $data['stit_GST'] = $value['GST'];
            $data['stit_hsnId'] = $value['hsnId'];
            $data['stit_Description'] = $value['Short_description'];
            $data['stit_long_description'] = $value['Description'];
            $data['stit_product_variant'] = $value['Product_Variant'];
            $data['stit_brand_name'] = $value['Brand_Name'];
            $data['pdt_brand'] = $value['BrandId'];
            $data['stit_category_name'] = $value['Category_Name'];
            $data['product_category'] = $value['CategoryId'];
            $data['featured'] = $value['Featured'];
            $data['popular'] = $value['Popular'];
            $data['stit_itemName'] = 'Private Product';
            $data['stit_itemId'] = $db->getItemFromDB("SELECT itemname_id FROM finascop_stock_itemmastername WHERE item_name  = 'Private Product'");
            $data['courierDelivery'] = $value['Courier_Delivery'];
            $data['directDelivery'] = $value['Direct_Delivery'];
            $data['stit_courierWt'] = $value['ProductWeight'];
            $data['taxValueId'] = $value['gstId'];
            $data['itemProcessingTime'] = $db->getItemFromDB("SELECT processingTime FROM mypha_productsubcategory WHERE sub_category_id = '{$value['CategoryId']}'");
            $maufactureDetails =  $db->getFromDb("SELECT manufacture_id,manufacture_name FROM mypha_productmanufacture WHERE manufacture_id = (SELECT manufacture_id FROM mypha_productbrands WHERE brand_id = {$value['BrandId']})", true);
            $data['med_manufactureid'] = $maufactureDetails['manufacture_id'];
            $data['med_manufacturename'] = $maufactureDetails['manufacture_name'];
            $data['stit_quantity'] = $value['Quantity'] . $value['Unit'];
            $data['stit_unit'] = $value['UnitId'];
            $data['stit_qty'] = (float)$value['Quantity'];
            $data['stit_foodtype'] = $foodtype;
            $data['stit_orgin_country'] = $value['countryId'];
            $data['stit_StoreGroup'] = $value['merchantId'];
            $data['tpCreatedFrom'] = $id;

            $fsuidata['fsi_item_id'] = $data['stit_itemId'];
            $fsuidata['fsi_item_name'] = $data['stit_itemName'];
            $fsuidata['fsi_brand_id'] = $data['pdt_brand'];
            $fsuidata['fsi_brand_name'] = $data['stit_brand_name'];
            $fsuidata['fsi_category_id'] = $data['product_category'];
            $fsuidata['fsi_categry_name'] = $data['stit_category_name'];
            $fsuidata['fsi_variant'] = $data['stit_product_variant'];

            $stit_fsiuid = updateTPUniqueItemTablefn(0, $fsuidata);
            $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];
            $itemName = $data['stit_itemName'];

            $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from third_party_products  WHERE stit_itemName = '{$itemName}' AND  stit_product_variant = '{$data['stit_product_variant']}' AND stit_quantity = '{$value['Quantity']}' AND product_category = {$data['product_category']} AND pdt_brand = {$data['pdt_brand']} AND  stit_StoreGroup = {$data['stit_StoreGroup']}");
            if ($IsItemNameUnique > 0) {
                echo "This Item already existing. " . "\t" . $value['sno'] . "\t" . $value['SKU'] . " Item Name - " . $data['stit_itemName'] . ", Variant - " . $data['stit_product_variant'] . ",  QTY - " . $value['Quantity'] . ", product_category - " . $data['stit_category_name'] . ", Brand - " . $data['stit_brand_name'] . " \r\n";
                continue;
            }
            $SKUUnique = $db->getItemFromDB("SELECT COUNT(*) from  third_party_products  WHERE stit_SKU = '{$value['SKU']}' AND  stit_StoreGroup = {$data['stit_StoreGroup']}");
            $db->query('begin');
            $datafsi = array_filter($data, 'strlen');
            $status = $db->perform("third_party_products", $datafsi);
            $masterid = $db->insert_id();
            if ($masterid > 0) {
                $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM third_party_products WHERE stit_fsiuid = {$stit_fsiuid['fsi_uid']}");
                if ($stit_fsiuid['status'] == 'NEW') {
                    $db->perform("third_party_products_uniqueitem", $uuit, 'update', " fsi_uid = {$stit_fsiuid['fsi_uid']}");
                }
            }

            $status = $db->query('commit');
        }
        if ($status == 1) {
            $dispMsg = "Confirmed listed items.";

            echo '{"success":true,"valid":true,"msg":"' . $dispMsg . '"}';
        } else {
            echo '{"success":false,"valid":false}';
        }
        break;
    case 'getMerchantStore':
        $search_hint = $_POST['query'];
        if (!empty($search_hint)) {
            $qry = "select store_group_id AS id ,store_group_name AS name from  finascop_branch_group WHERE store_group_name LIKE '%{$search_hint}%'  order by store_group_name ";
        } else {
            $qry = "select store_group_id AS id ,store_group_name AS name from  finascop_branch_group order by store_group_name ";
        }

        $data = $parentdb->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'listproductThirdParty':
        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'desc' : $data['dir'];
        $search = ' WHERE 1=1 and storeGroupId > 0';
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $searchitem .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                }
            }
        }

        if ($rec_sort == 'createdOn') {
            $rec_sort = 'id';
        }


        $countQuery = "SELECT COUNT(1) from product_source_upload_master psum INNER JOIN finascop_usr_profile ON UserId = createdBy {$search} {$searchitem}";
        $count = $db->getItemFromDB($countQuery);
        //$count = $db->getItemFromDB($countQuery);
        //id,createdBy,createdByName,typeName,count,brand,createdOn
        $listQuery = " SELECT id,createdBy,CONCAT(FirstName,'',LastName) AS createdByName,
        IF(TYPE = 1,'Product','Image') AS typeName,count,brand,DATE_FORMAT(psum.createdOn, '%d-%m-%Y %H:%i:%s') AS createdOn,
        IF((SELECT COUNT(*) FROM third_party_products WHERE tpCreatedFrom = id )>0,1,0) AS isConfirmed,status 
        from product_source_upload_master psum
        INNER JOIN finascop_usr_profile ON UserId = createdBy {$search}  {$searchitem} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';

        break;
    case 'listTPProductUploadedItems':
        $data = $_POST;
        $rec_sort = empty($data['sort']) ? 'stit_ID' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' AND 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(*) from third_party_products WHERE tpCreatedFrom = '{$_POST['fbiu_id']}' {$filter_part}";
        $listQuery = "SELECT stit_ID,stit_SKU,stit_HSNCode,stit_GST,stit_hsnId,stit_Description,stit_long_description,
                stit_product_variant,stit_brand_name,pdt_brand,stit_category_name,product_category,
                featured,popular,courierDelivery,directDelivery,stit_itemId,stit_courierWt,taxValueId,
                stit_quantity,stit_qty,stit_unit,stit_orgin_country from third_party_products WHERE tpCreatedFrom = '{$_POST['fbiu_id']}' {$filter_part}  ORDER BY CAST({$rec_sort} as char) {$rec_sort_dir},binary {$rec_sort} {$rec_sort_dir}  ";
        $count = $db->getItemFromDB($countQuery);
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        break;
    case 'syncToParentDB':
        $itemar = $_POST['itemarr'];
        $itemdecode = json_decode($itemar);
        $itemcount = count($itemdecode);
        $isSynced = $db->getItemSafe("SELECT isSync FROM product_source_upload_master WHERE id = ?", "i", [$_POST['id']]);
        if ($isSynced == 2) {
            echo "{'success':false,'valid':false,'msg': 'Product already synced.'}";
            exit();
        }
        if ($isSynced == 1) {
            //$db->query('begin');
            for ($i = 0; $i < $itemcount; $i++) {
                syncProductToStore($itemdecode[$i]);
            }
            $syncData['isSync'] = 2;
            $status = $db->perform('product_source_upload_master', $syncData, 'update', "id = " . intval($_POST['id']));
            $status = $db->executeSafe("DELETE FROM product_import_tmp WHERE uid = ?", "i", [$_POST['id']]);
            //$status = $db->query('commit');

            $uploadData = $db->getFromSafe("SELECT * FROM product_source_upload_master WHERE id = ?", "i", [$_POST['id']], true);
            $logData['uuid'] = getNewFinascopApiKey();
            $logData['storegroupid'] = $uploadData['storeGroupId'];
            $logData['source'] = $_SERVER['HTTP_HOST'];
            $logData['User'] = $_SESSION['admin']->Finascop_UserId;
            $logData['tstamp'] = date('Y-m-d H:i:s');
            $logData['Description'] = $uploadData['data'];
            creatInvetoryLog($logData);
        } else {
            echo "{'success':false,'valid':false,'msg': 'Sync Iamges and proceed with product syncing.'}";
            exit();
        }

        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Data exported '}";
        } else {
            echo "{'success':False,'valid':false,'msg': 'Error While Saving.'}";
        }
        break;
    case 'syncTpImages':
        $itemar = $_POST['itemarr'];
        $itemdecode = json_decode($itemar);
        $itemcount = count($itemdecode);
        $isSynced = $db->getItemSafe("SELECT isSync FROM product_source_upload_master WHERE id = ?", "i", [$_POST['id']]);
        if ($isSynced == 1) {
            echo "{'success':false,'valid':false,'msg': 'Images already synced.'}";
            exit();
        }
        $db->query('begin');
        for ($i = 0; $i < $itemcount; $i++) {
            $imageFolder = $db->getItemFromDB("SELECT stit_displaylabel FROM third_party_products WHERE stit_ID = {$itemdecode[$i]}");
            $stit_ID = $itemdecode[$i];
            // Specify your AWS credentials and region
            $credentials = [
                'key'    => PBUPLOADACCESSID,
                'secret' => PBUPLOADSECRETKEY,
                'region' => PBUPLOADREGION
            ];

            // Create an S3Client object
            $s3Client = new S3Client([
                'version'     => 'latest',
                'credentials' => $credentials,
                'region'      => PBUPLOADREGION
            ]);

            // Bucket name
            $bucketName = 'productbankimages';

            // Prefix to search for
            $baseFolder = 'private_product/' . $_POST['id'] . '/' . $imageFolder . '/';

            //try {
            // Use the listObjectsV2 method to list objects with the specified prefix
            $result = $s3Client->listObjectsV2([
                'Bucket' => $bucketName,
                'Prefix' => $baseFolder
            ]);

            $data = [];
            $count = count($result);
            if (isset($result['Contents']) && count($result['Contents']) > 0) {
                foreach ($result['Contents'] as $key => $object) {
                    if (substr($object['Key'], -1) === '/') {
                        continue;
                    }

                    $pathParts = explode('/', $object['Key']);
                    $fileName = end($pathParts); // Extract file name

                    $data[$key] = [
                        'id' => $key + 1,
                        'folder' => $imageFolder, // Subfolder from variable
                        'imagetype' => 'Third Party',
                        'imagename' => $fileName,
                        'thumpimg_path' => 'https://productbankimages.s3.eu-west-2.amazonaws.com/' . $object['Key'],
                        'image_url' => $s3Client->getObjectUrl($bucketName, $object['Key']),
                    ];
                    $imagePath = $s3Client->getObjectUrl($bucketName, $object['Key']);


                    $fileuploadname = trim(str_replace('.', '', uniqid("", true))) . "." . pathinfo(basename($imagePath), PATHINFO_EXTENSION);
                    $destinationPath = $_SERVER["DOCUMENT_ROOT"] . '/tmp/importimages/' . $fileuploadname;
                    if (copy($imagePath, $destinationPath)) {

                        $s3upload = new cgoS3FileHandler();

                        $cloudFrontPath = 'products/';

                        $s3upload->putFileToS3($cloudFrontPath, AWSBUCKETUPLOADS, $destinationPath, $fileuploadname);

                        if (file_exists($destinationPath)) {
                            $data = array(
                                "product_id" => $stit_ID,
                                "image_url" => $fileuploadname,
                                "image_thumb_url" => '',
                                "image_type" => 1,
                                "bucket_name" => '',
                                "created_at" => date("Y-m-d H:i:s"),
                                "image_folder" => 'products/'
                            );
                            $status = $db->perform('thirdparty_item_images', $data);
                        }
                    }
                }
            }

            $syncData['isSync'] = 1;
            $status = $db->perform('product_source_upload_master', $syncData, 'update', "id = " . intval($_POST['id']));
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Product images synced.'}";
        } else {
            echo "{'success':False,'valid':false,'msg': 'Error While Saving.'}";
        }
        break;
    case 'listImportedItems':
        $data = $_POST;
        $rec_sort = empty($data['sort']) ? 'isVerified' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' AND 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(*) from product_import_tmp WHERE uid = '{$_POST['fbiu_id']}' {$filter_part}";
        $listQuery = "SELECT Brand_Name,BrandId,Category_Name,CategoryId,countryId,CountryOfOrgin,
        Courier_Delivery,createdBy,createdOn,Description,Direct_Delivery,Featured,FoodType,GST,
        gstId,HSN_code,hsnId,id,isVerified,merchantId,Popular,Product_Variant,ProductWeight,
        Quantity,Short_description,SI_No,SKU,uid,Unit,UnitId,validation from product_import_tmp WHERE uid = '{$_POST['fbiu_id']}' {$filter_part}  ORDER BY CAST({$rec_sort} as char) {$rec_sort_dir},binary {$rec_sort} {$rec_sort_dir}  ";
        $count = $db->getItemFromDB($countQuery);
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        break;

    case 'getprdctDetails':
        $id = $_POST['id'];
        $qry = "select Brand_Name,BrandId,Category_Name,CategoryId,countryId,CountryOfOrgin,
        GST,gstId,HSN_code,hsnId,id,Quantity,Unit,UnitId from product_import_tmp WHERE id = {$id} ";
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'verifyProduct':
        $id = $_POST['id'];
        if ($_POST['BrandId'] > 0) {
            $data['Brand_Name'] = $_POST['Brand_Name'];
            $data['BrandId'] = $_POST['BrandId'];
        }
        if ($_POST['CategoryId'] > 0) {
            $data['CategoryId'] = $_POST['CategoryId'];
            $data['Category_Name'] = $_POST['Category_Name'];
        }
        if ($_POST['countryId'] > 0) {
            $data['countryId'] = $_POST['countryId'];
            $data['CountryOfOrgin'] = $_POST['CountryOfOrgin'];
        }
        if ($_POST['hsnId'] > 0) {
            $data['hsnId'] = $_POST['pdthsnId'];
            $data['HSN_code'] = $_POST['HSN_code'];
            $data['gstId'] = $_POST['pdtgstId'];
            $data['GST'] = $_POST['GST'];
        }
        if ($_POST['UnitId'] > 0) {
            $data['UnitId'] = $_POST['UnitId'];
            $data['Unit'] = $_POST['Unit'];
        }
        $data['Quantity'] = $_POST['Quantity'];
        $data['isVerified'] = 1;
        $db->query('begin');
        $status = $db->perform('product_import_tmp', $data, 'update', " id = {$id}");
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Product Verified.'}";
        } else {
            echo "{'success':False,'valid':false,'msg': 'Error While Saving.'}";
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
    case 'getGstStore':
        $hsnId = $_POST['hsnId'];
        $search_hint = $_POST['query'];
        $qry = "select id, hsnGst,hsnId,hsnCess from hsn_value WHERE hsnId = {$hsnId} AND hsnGst LIKE '{$search_hint}%'order by id";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'showImages':
        ob_start();
        include('ImageFile.php');
        $rehtml = ob_get_clean();
        echo $rehtml;
        exit;
        break;
    case 'discardProductImport':
        $uploadId = $_POST['id'];
        $db->query('begin');
        $disdata['updatedOn'] = date('Y-m-d H:i:s');
        $disdata['status'] = 0;
        $status = $db->query("DELETE FROM product_import_tmp WHERE uid = {$uploadId}");
        $status = $db->perform('product_source_upload_master', $disdata, 'update', " id = {$uploadId}");
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Product Import Discarded.'}";
        } else {
            echo "{'success':False,'valid':false,'msg': 'Error While Saving.'}";
        }
        break;
    case 'liststoreDataProducts':
        $data = $_POST;
        $rec_sort = empty($data['sort']) ? 'g.id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' AND 1=1';
        $feedback = $data['feedback'];

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        switch ($feedback) {
            case 1:
                $countQuery = "SELECT COUNT(*) from gs1_products_source AS g LEFT JOIN upload_product_map AS u ON g.id = u.sourceProductId WHERE g.isValid = 0 AND u.sourceProductId IS NULL AND g.isArchived = 7 AND uploadedFrom = '{$_POST['fbiu_id']}' {$filter_part}";
                $listQuery = "SELECT g.id AS id,g.brand AS brand,g.name AS name,g.gtin AS gtin,g.type AS type,g.category AS category,g.sub_category AS sub_category from gs1_products_source AS g LEFT JOIN upload_product_map AS u ON g.id = u.sourceProductId WHERE g.isValid = 0 AND u.sourceProductId IS NULL AND g.isArchived = 7 AND uploadedFrom = '{$_POST['fbiu_id']}' {$filter_part}  ORDER BY CAST({$rec_sort} as char) {$rec_sort_dir},binary {$rec_sort} {$rec_sort_dir}  ";
                break;
            default:
                $countQuery = "SELECT COUNT(*) from gs1_products_source AS g LEFT JOIN upload_product_map AS u ON g.id = u.sourceProductId WHERE g.isValid = 0 AND u.sourceProductId IS NULL AND g.isArchived <> 7 AND uploadedFrom = '{$_POST['fbiu_id']}' {$filter_part}";
                $listQuery = "SELECT g.id AS id,g.brand AS brand,g.name AS name,g.gtin AS gtin,g.type AS type,g.category AS category,g.sub_category AS sub_category from gs1_products_source AS g LEFT JOIN upload_product_map AS u ON g.id = u.sourceProductId WHERE g.isValid = 0 AND u.sourceProductId IS NULL AND g.isArchived <> 7 AND uploadedFrom = '{$_POST['fbiu_id']}' {$filter_part}  ORDER BY CAST({$rec_sort} as char) {$rec_sort_dir},binary {$rec_sort} {$rec_sort_dir}  ";
                break;
        }

        //$db->printGridJson($countQuery, $listQuery);
        $count = $db->getItemFromDB($countQuery);
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
            //echo json_encode($qry);
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        break;
    case 'getMatchedProducts':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $brand = str_replace(' ', '', $_POST['brand']);
        $cond = " WHERE 1=1  AND productId IS NULL ";
        $tpcond = " ";
        $tpquery = " ";
        $uploadId = $_POST['userId'];
        if ($uploadId > 0) {
            $sgId = $db->getItemFromDB("SELECT storeGroupId FROM product_source_upload_master WHERE id = {$uploadId}");
            $tpcond .= " WHERE 1=1 AND stit_StoreGroup = {$sgId} ";
            $tpquery .= " UNION
            SELECT stit_ID,stit_SKU,stit_brand_name,med_manufacturename,stit_StoreGroup FROM  `third_party_products` ";
        }

        if (!empty($brand)) {
            $cond .= " AND REPLACE(stit_brand_name, ' ', '') LIKE '%{$brand}%'";
        }
        if (!empty($brand) && $uploadId > 0) {
            $tpcond .= " AND REPLACE(stit_brand_name, ' ', '') LIKE '%{$brand}%'";
        }

        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    $tpcond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $cond .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                    $tpcond .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                }
            }
        }
        if (!empty($brand) || ($_POST['brand_id'] > 0)) {
            $countQuery = "SELECT COUNT(*) FROM  finascop_stock_itemmaster LEFT JOIN upload_product_map ON productId = stit_ID  {$cond} ";
            $count = $db->getItemFromDB($countQuery);

            $qry = "SELECT stit_ID,stit_SKU,stit_brand_name,med_manufacturename,stit_StoreGroup FROM  finascop_stock_itemmaster LEFT JOIN upload_product_map ON productId = stit_ID {$cond}  
            {$tpquery} {$tpcond} "; //ORDER BY {$sort} {$dir}
            $data = $db->getMultipleData($qry, true);
        }
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'markAvaialableStoreProducts':
        $uploadId = $_POST['uploadId'];
        if ($_POST['uploadId'] == 0) {
            $uploadId = $db->getItemSafe("SELECT uploadedFrom FROM gs1_products_source WHERE id = ?", "i", [$_POST['storePrdctId']]);
        }
        $storePrdctId = $_POST['storePrdctId'];
        $mappedPrdctId = $_POST['mappedPrdctId'];
        $grozeoStitId = $db->getItemFromDB("SELECT grozeo_stitId FROM product_grozeo_map WHERE product_stitId = {$mappedPrdctId}");
        $grozeoStitId = ($grozeoStitId > 0) ? $grozeoStitId : 0;
        if ($uploadId > 0 && $storePrdctId > 0 && $mappedPrdctId > 0) {
            $data['uploadId'] = $uploadId;
            $data['sourceProductId'] = $storePrdctId;
            $data['productId'] = $mappedPrdctId;
            $data['grozeoStitId'] = $grozeoStitId;
            $data['uploadId'] = $uploadId;
            $data['createdOn'] = date("Y-m-d H:i:s");
            $data['createdBy'] = $_SESSION['admin']->Finascop_UserId;

            $db->query('begin');
            $status = $db->perform('upload_product_map', $data);
            $status = $db->query('commit');
        }

        if ($status == 1) {
            $dispMsg = "Product get mapped.";

            echo '{"success":true,"valid":true,"msg":"' . $dispMsg . '"}';
        } else {
            // var_dup($error);
            echo '{"success":false,"valid":false}';
        }
        break;
    case 'getMappedProducts':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $brand = str_replace(' ', '', $_POST['brand']);
        $cond = " WHERE 1=1  AND uploadId = " . intval($_POST['uploadId']) . "  ";

        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $cond .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                }
            }
        }
        if ($_POST['uploadId'] > 0) {
            $countQuery = "SELECT COUNT(*) FROM  finascop_stock_itemmaster INNER JOIN upload_product_map ON productId = stit_ID {$cond} ";
            $count = $db->getItemFromDB($countQuery);

            $qry = "SELECT stit_ID,stit_SKU,stit_brand_name,med_manufacturename,stit_category_name,stit_HSNCode,stit_HSN_code,stit_GST,IF(grozeoStitId > 0,'Yes','No') AS isExported  FROM  finascop_stock_itemmaster INNER JOIN upload_product_map ON productId = stit_ID  {$cond}  "; //ORDER BY {$sort} {$dir}
            $data = $db->getMultipleData($qry, true);
        }
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'mappedProductsexportexcel':
        require(THIS_MODULE_PATH . "/function.php");

        $lastParameters = json_decode(stripslashes(($_POST["filterData"])), true);

        for ($i = 0; $i <= $i; $i++) {
            if (array_key_exists('filter[' . $i . '][field]', $lastParameters)) {
                $filterParams[$i]['field'] = $lastParameters['filter[' . $i . '][field]'];
                unset($lastParameters['filter[' . $i . '][field]']);
                $filterParams[$i]['data']['type'] = $lastParameters['filter[' . $i . '][data][type]'];
                unset($lastParameters['filter[' . $i . '][data][type]']);
                $filterParams[$i]['data']['value'] = $lastParameters['filter[' . $i . '][data][value]'];
                unset($lastParameters['filter[' . $i . '][data][value]']);
                $filterParams[$i]['data']['comparison'] = $lastParameters['filter[' . $i . '][data][comparison]'];
                unset($lastParameters['filter[' . $i . '][data][comparison]']);
            } else {
                break;
            }
        }
        $_POST['filter'] = $filterParams;
        foreach ($lastParameters as $keys => $values) {
            $_POST[$keys] = $values;
        }
        $_POST['start'] = 0;
        $_POST['limit'] = 100000;


        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $cond = " WHERE 1=1  AND uploadId = " . intval($_POST['uploadId']) . " ";

        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $cond .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                }
            }
        }
        if ($_POST['uploadId'] > 0) {
            $listQuery = "SELECT stit_ID,stit_SKU,stit_brand_name,med_manufacturename,stit_category_name,stit_HSNCode,stit_HSN_code,stit_GST FROM  finascop_stock_itemmaster 
            INNER JOIN upload_product_map ON productId = stit_ID  {$cond}  limit {$start},{$limit} "; //ORDER BY {$sort} {$dir}
        }

        $_SESSION['Export']['Query'] = $listQuery;
        $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
        _exportExcelReport($_POST);
        break;
    case 'markNotAvailable':
        $uploadId = $_POST['uploadId'];
        $storePrdctId = $_POST['storePrdctId'];
        if ($uploadId > 0 && $storePrdctId > 0) {
            $gs1DupData["isArchived"] = 0;
            $gs1DupData["isValid"] = 1;
            $gs1DupData["updatedOn"] = date('Y-m-d H:i:s');
            $db->query('begin');
            $status = $db->perform('gs1_products_source', $gs1DupData, 'update', " id = {$storePrdctId}");
            $status = $db->query('commit');
        }

        if ($status == 1) {
            $dispMsg = "Product availablity marked.";

            echo '{"success":true,"valid":true,"msg":"' . $dispMsg . '"}';
        } else {
            // var_dup($error);
            echo '{"success":false,"valid":false}';
        }
        break;
    case 'gettoSyncProducts':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $brand = str_replace(' ', '', $_POST['brand']);
        $cond = " WHERE 1=1  AND status <> 5";

        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $cond .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                }
            }
        }
        $countQuery = "SELECT COUNT(*) FROM  finascop_stock_itemmaster INNER JOIN upload_product_map ON productId = stit_ID {$cond} ";
        $count = $db->getItemFromDB($countQuery);

        $qry = "SELECT id,stit_ID,stit_SKU,stit_brand_name,med_manufacturename,stit_category_name,stit_HSNCode,stit_HSN_code,stit_GST,IF(grozeoStitId > 0,'Yes','No') AS isExported  FROM  finascop_stock_itemmaster 
            INNER JOIN upload_product_map ON productId = stit_ID  {$cond}  "; //ORDER BY {$sort} {$dir}
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'removeProduct':
        $uploadId = $_POST['uploadId'];
        $storePrdctId = $_POST['storePrdctId'];
        $feedback = $_POST['feedback'];
        if ($uploadId > 0 && $storePrdctId > 0) {
            switch ($feedback) {
                case 1:
                    $gs1DupData["isValid"] = 0;
                    $gs1DupData['isArchived'] = 7;
                    break;
                case 0:
                    $gs1DupData["isValid"] = 1;
                    $gs1DupData['isArchived'] = 5;
                    break;
            }

            $gs1DupData["updatedOn"] = date('Y-m-d H:i:s');
            $db->query('begin');
            $status = $db->perform('gs1_products_source', $gs1DupData, 'update', " id = {$storePrdctId}");
            $status = $db->query('commit');
        }

        if ($status == 1) {
            $dispMsg = "Product removed.";

            echo '{"success":true,"valid":true,"msg":"' . $dispMsg . '"}';
        } else {
            // var_dup($error);
            echo '{"success":false,"valid":false}';
        }
        break;
    case 'feedbackedProductsexportexcel':
        require(THIS_MODULE_PATH . "/function.php");

        $lastParameters = json_decode(stripslashes(($_POST["filterData"])), true);

        for ($i = 0; $i <= $i; $i++) {
            if (array_key_exists('filter[' . $i . '][field]', $lastParameters)) {
                $filterParams[$i]['field'] = $lastParameters['filter[' . $i . '][field]'];
                unset($lastParameters['filter[' . $i . '][field]']);
                $filterParams[$i]['data']['type'] = $lastParameters['filter[' . $i . '][data][type]'];
                unset($lastParameters['filter[' . $i . '][data][type]']);
                $filterParams[$i]['data']['value'] = $lastParameters['filter[' . $i . '][data][value]'];
                unset($lastParameters['filter[' . $i . '][data][value]']);
                $filterParams[$i]['data']['comparison'] = $lastParameters['filter[' . $i . '][data][comparison]'];
                unset($lastParameters['filter[' . $i . '][data][comparison]']);
            } else {
                break;
            }
        }
        $_POST['filter'] = $filterParams;
        foreach ($lastParameters as $keys => $values) {
            $_POST[$keys] = $values;
        }
        $_POST['start'] = 0;
        $_POST['limit'] = 100000;


        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $cond = " ";

        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $cond .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                }
            }
        }
        if ($_POST['fbiu_id'] > 0) {
            $listQuery = "SELECT g.id AS id,g.brand AS brand,g.name AS name,g.gtin AS gtin,g.type AS type,g.category AS category,g.sub_category AS sub_category 
            from gs1_products_source AS g LEFT JOIN upload_product_map AS u ON g.id = u.sourceProductId 
            WHERE g.isValid = 0 AND u.sourceProductId IS NULL AND g.isArchived = 7 AND uploadedFrom = '{$_POST['fbiu_id']}'  {$cond} ";
        }

        $_SESSION['Export']['Query'] = $listQuery;
        $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_feed";
        _exportExcelReport($_POST);
        break;
    case 'updateSourceData':
        $prdid = $_POST['prdid'];
        $brandName = $_POST['edit_brand'];
        $qry = 'select id from gs1_brand_source where brandName = "' . $brandName . '"';
        $tmpsdata = $db->getFromDB($qry, true);
        if ($tmpsdata['id'] > 0) {
            $data['brandId'] = $tmpsdata['id'];
            $data['brand'] = $brandName;
        } else {
            $insertQuery = 'INSERT INTO gs1_brand_source (brandName) VALUES ("' . $brandName . '")';
            $tmpsdata = $db->query($insertQuery);
            $gs1DupData['brandId'] = $db->insert_id();
            $gs1DupData['brand'] = $brandName;
        }
        $data['name'] = $_POST['edit_sku'];
        $data["updatedOn"] = date('Y-m-d H:i:s');
        $db->query('begin');
        $status = $db->perform('gs1_products_source', $data, 'update', " id = {$prdid}");
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Details updated.'}";
        } else {
            echo "{'success':False,'valid':false,'msg': 'Error While Saving.'}";
        }
        break;
}
