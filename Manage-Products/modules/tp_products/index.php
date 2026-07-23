<?php

$userid = $_SESSION['admin']->Finascop_UserId;

switch ($op) {
    case 'listItemMasterDataTP':
        $rec_limit = empty($_POST['limit']) ? 21 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['item_id', 'item_name', 'item_code', 'item_created_on'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'item_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'third_party_products.stit_ID' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $search = " WHERE 1=1 AND tpConvertedStatus NOT IN (1,2)";
        if (isset($filter)) {
            $filter = $_POST['filter'];
            foreach ($filter as $key => $val) {
                $type = $val['data']['type'];
                $value = $val['data']['value'];
                $field = $val['field'];
                switch ($val['data']['type']) {
                    case 'string':
                        if ($val['field'] == 'hsn_code') {
                            $hsn_code = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(hsn_id),0) FROM finascop_hsn WHERE hsn_code LIKE '{$val['data']['value']}%' ");
                            $search .= " AND stit_HSNCode IN({$hsn_code}) ";
                        } else if ($val['field'] == 'statusName') {
                            if ($val['data']['value'] == 'Active') {
                                $search .= " and stit_status = 1 ";
                            } else if ($val['data']['value'] == 'Inactive') {
                                $search .= " and stit_status = 0 ";
                            } else {
                                $search .= " and stit_status IN(1,0) ";
                            }
                        } else if ($val['field'] == 'isVerified') {
                            if ($val['data']['value'] == 'Yes') {
                                $search .= " and (isVerified = 1) ";
                            } else if ($val['data']['value'] == 'No') {
                                $search .= " and (isVerified = 0) ";
                            } else {
                                $search .= " and (isVerified IN(1,0)) ";
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
                }
            }
        }
        if($_POST['merchantId'] > 0){
            $search .= " AND stit_StoreGroup = " . intval($_POST['merchantId']) . " ";
        }
        $query = "SELECT stit_StoreGroup,tpConvertedStatus,stit_ID,stit_SKU,stit_itemName,stit_HSN_code,stit_category_name,stit_brand_name,stit_quantity,
         stit_product_variant,least_package_type_name,IF(tpStatus = 1,'Unverified','Imprecise') AS tpStatusName,tpStatus FROM third_party_products";
        $countQuery = "SELECT count(*) FROM ({$query}) as count {$search}  order by {$sort} {$dir}";

        $qry = "SELECT * FROM ({$query}) AS list {$search}  order by {$sort} {$dir}  ";
        $count = $db->getItemFromDB($countQuery);
        $listQuery = "{$qry} limit {$rec_start},{$rec_limit}";
        $db->printGridJson($countQuery, $listQuery);

        break;

    case 'listMatchedGs1Items':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['item_id', 'item_name', 'item_code', 'item_created_on'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'item_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $brand = $_POST['brand'];
        $itemName = $_POST['stit_itemName'];
        $SKU = $_POST['stit_SKU'];
        $archive = $_POST['archive'];
        $cond = " WHERE 1=1  ";


        if (!empty($brand)) {
            $cond .= " AND stit_brand_name LIKE '%{$brand}%' ";
        }
        if (!empty($itemName)) {
            //$cond .= " OR stit_SKU LIKE '%{$itemName}%' ";
        }
        if ($SKU > 0) {
            $cond .= "  ";
        }
        if ($_POST['brand_id'] > 0) {
            $cond .= " AND pdt_brand = " . intval($_POST['brand_id']) . "  ";
        }
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }
        if (!empty($brand) || ($_POST['brand_id'] > 0)) {
            $countQuery = "SELECT COUNT(*) FROM  finascop_stock_itemmaster  
            LEFT JOIN gs1_products_extension gpe  ON stit_ID =  gpe.productId  {$cond} LIMIT 0,5000 ";
            $count = $db->getItemFromDB($countQuery);

            $qry = "SELECT gpe.id,gpe.brand,gpe.name,gpe.category,gpe.sub_category,gpe.company_detail as company,stit_ID AS productId,
            stit_SKU,stit_brand_name,med_manufacturename,if(stit_ID > 0,'Yes','No') as isExisting 
              FROM  finascop_stock_itemmaster  
            LEFT JOIN gs1_products_extension gpe ON stit_ID =  gpe.productId {$cond}   LIMIT 0,5000"; //ORDER BY {$sort} {$dir}
            $data = $db->getMultipleData($qry, true);
        }
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;
    case 'tpPrdtsDetailsView':
        $stit_ID = isset($_POST['stit_ID']) ? intval($_POST['stit_ID']) : 0;
        $bucketPath = AWSBUCKETPATH;
        $folder = AWSBUCKETFOLDER;
        $preview = SLTHUMP;
        if ($stit_ID) {

            $data = $db->getFromDB("SELECT * FROM third_party_products WHERE stit_ID = " . $stit_ID, true);
            $image_urlend = $db->getItemFromDB("SELECT image_url FROM thirdparty_item_images WHERE product_id = {$stit_ID} AND image_type = 1");
            $data['image_urlend'] = $image_urlend;
            if (!empty($image_urlend)) {
                $data['image_url'] = "{$bucketPath}/{$folder}{$preview}{$image_urlend}";
            } else {
                $data['image_url'] = "-";
            }

            $data['stit_orgin_countryname'] = $db->getItemFromDB("SELECT country_name FROM finascop_country WHERE country_id = {$data['stit_orgin_country']}");
            $productCountry = $db->getItemFromDB("SELECT GROUP_CONCAT(stitc_country) FROM finascop_stock_itemmaster_country WHERE stitc_stit_id  = {$stit_ID} ");
            if (!empty($productCountry)) {
                $data['productCountry'] = $db->getItemFromDB("SELECT GROUP_CONCAT(country_name) FROM finascop_country WHERE country_id IN ({$productCountry})");
            } else {
                $data['productCountry'] = '-';
            }

            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'convertTPProducts':
        $itemId = $_POST['stit_ID'];
        $tpConvstatus = $_POST['status'];
        $db->query('begin');

        $data = array(
            "tpConvertedStatus" => $tpConvstatus,
            "stit_updatedOn" => date('Y-m-d H:i:s'),
            "updatedBy" => $userid
        );
        if ($tpConvstatus == 1) {
            $data['tpStatus'] = 1;
        }
        $status = $db->perform('third_party_products', $data, 'update', " stit_ID = {$itemId}");

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'getgs1Brands':
        $IsSuperUser = $_SESSION['admin']->IsSuperUser;
        if ($IsSuperUser == 'Yes') {
            $search .= " ";
        } else {
            $search .= " ";
            // $search .= " INNER JOIN user_brands ON brandId = id AND UserId = {$_SESSION['admin']->Finascop_UserId} ";
        }
        $qry = "select brand_id as id,brand_name as brandName from mypha_productbrands {$search} WHERE status = 1 order by brand_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'listTpProducts':
        $today = date("Y-m-d");
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['item_id', 'item_name', 'item_code', 'item_created_on'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'item_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'third_party_products.stit_ID' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $search = " WHERE 1=1 AND isDelete = 0 AND tpStatus = 1 AND tpConvertedStatus <> 2 AND (DATE(skippedOn) <> DATE(NOW()) OR DATE(skippedOn) IS NULL) ";
        if ($_POST['brand_id'] > 0) {
            $search .= " and pdt_brand = {$_POST['brand_id']} ";
        }
        if (isset($filter)) {
            $filter = $_POST['filter'];
            foreach ($filter as $key => $val) {
                $type = $val['data']['type'];
                $value = $val['data']['value'];
                $field = $val['field'];
                switch ($val['data']['type']) {
                    case 'string':
                        if ($val['field'] == 'hsn_code') {
                            $hsn_code = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(hsn_id),0) FROM finascop_hsn WHERE hsn_code LIKE '{$val['data']['value']}%' ");
                            $search .= " AND stit_HSNCode IN({$hsn_code}) ";
                        } else if ($val['field'] == 'statusName') {
                            if ($val['data']['value'] == 'Active') {
                                $search .= " and stit_status = 1 ";
                            } else if ($val['data']['value'] == 'Inactive') {
                                $search .= " and stit_status = 0 ";
                            } else {
                                $search .= " and stit_status IN(1,0) ";
                            }
                        } else if ($val['field'] == 'isVerified') {
                            if ($val['data']['value'] == 'Yes') {
                                $search .= " and (isVerified = 1) ";
                            } else if ($val['data']['value'] == 'No') {
                                $search .= " and (isVerified = 0) ";
                            } else {
                                $search .= " and (isVerified IN(1,0)) ";
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
                }
            }
        }
        $countQuery = "SELECT count(*) FROM third_party_products INNER JOIN mypha_productbrands ON brand_id = pdt_brand  {$search}  order by {$sort} {$dir}";
        //AND storegroup_id = 0
        $qry = "SELECT stit_ID,stit_SKU,stit_itemName,stit_HSN_code,stit_category_name,stit_brand_name,stit_quantity,
         stit_product_variant,least_package_type_name,if(tpStatus = 1,'Unverified','Imprecise') as tpStatusName,tpStatus 
         FROM third_party_products INNER JOIN mypha_productbrands ON brand_id = pdt_brand   {$search}  order by {$sort} {$dir}  ";
        $count = $db->getItemFromDB($countQuery);
        $listQuery = "{$qry} limit {$rec_start},{$rec_limit}";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'gsPrdtsDetailsView':
        $stit_ID = isset($_POST['stit_ID']) ? intval($_POST['stit_ID']) : 0;

        if ($stit_ID) {

            $data = $db->getFromDB("SELECT gpe.id,gpe.brand,gpe.name,gpe.category,gpe.sub_category,gpe.company_detail AS company,
            stit_product_variant,stit_quantity,stit_long_description,gpe.derived_description,gpe.gtin,stit_SKU,stit_brand_name,stit_itemName,stit_product_variant,
            stit_quantity,stit_category_name,stit_HSN_code,stit_GST,stit_itemReturnTime,
            (SELECT country_name FROM finascop_country WHERE country_id = stit_orgin_country) AS stit_orgin_countryname,least_package_type_name 
            FROM finascop_stock_itemmaster 
                LEFT JOIN  gs1_products_extension gpe  ON stit_ID =  gpe.productId WHERE stit_ID = " . $stit_ID, true);
            $data['image_urlend '] = null;
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'loadProductBasedOnType':
        $formType = $_POST['formType'];
        $tpItemId = $_POST['tpItemId'];
        $gsId = $_POST['gsId'];
        $productId = $_POST['productId'];
        $createProduct = $_POST['createProduct'];


        if ($createProduct == 1) {
            $qry = "SELECT stit_SKU,stit_ID AS itemId,stit_itemId AS item ,stit_HSNCode AS HSN,stit_GST AS GST,stit_MRP AS MRP,taxValueId,stit_hsnId, stgp_groupID AS itemgroup,stit_displaylabel,stit_product_variant,stit_itemReturnTime,stit_custInitiate,stit_package_type_namme ,pdt_package_type_id,product_category,pdt_brand,featured,pdt_sale_rate,popular,item_length,item_breadth,item_height,item_weight,stit_qty,stit_unit,cos_nos,cos_package_type_id,cos_package_type_name,cosb_package_type_id,cosb_package_type_name,cos_length,cos_length,cos_breadth,cos_height,cos_weight,ccs_nos,ccs_package_type_id,ccs_package_type_name,ccsb_package_type_id,ccsb_package_type_name,ccs_length,ccs_breadth,ccs_height,ccs_weight,rs_nos,rs_package_type_id,rs_package_type_name,rsb_package_type_id,rsb_package_type_name,rs_length,rs_breadth,rs_height,rs_weight,cs_nos,cs_package_type_id,cs_package_type_name,csb_package_type_id,csb_package_type_name,cs_length,cs_breadth,cs_height,cs_weight,ds_nos,ds_package_type_id,ds_package_type_name,dsb_package_type_id,dsb_package_type_name,ds_length,ds_breadth,ds_height,ds_weight,courierDelivery,directDelivery,directPurchase,stit_foodtype,stit_orgin_country,(SELECT country_name FROM finascop_country WHERE country_id = stit_orgin_country) AS orgCountryName,isRRPApplicable,stit_ingredients,stit_preparation_use,stit_allergens,stit_nutritionlabel,(SELECT GROUP_CONCAT(stitc_country) FROM finascop_stock_itemmaster_country WHERE stitc_stit_id = stit_ID) AS stit_productsFor, stit_Description AS description,stit_long_description,stit_quantity,stit_HSN_code,stit_brand_name,stit_category_name,stit_itemName,stitl1_optimumqty,stitl2_optimumqty,stitl3_optimumqty,stit11_minimumqty,stit12_minimumqty,stit13_minimumqty,stit11_maximumqty,stit12_maximumqty,stit13_maximumqty,stii_csb,stii_csbretail,isMedicine,least_package_type_id,least_package_type_name,stit_package_type_id,stit_stdPacking,stdpckl11_package_type_id,stdpckl1_nos,stdpckl12_package_type_id,stdpckl21_package_type_id,stdpckl2_nos,stdpckl22_package_type_id,stdpckl31_package_type_id,
    stdpckl3_nos,stdpckl32_package_type_id,stdpckl41_package_type_id,stdpckl4_nos,stdpckl42_package_type_id,stit_salesUnit,stit_storage_instruction,stit_safety_warning,stit_warning,
    (SELECT category_name FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category)) AS main_categoryName,(SELECT parent_category FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category)) AS parent_categorysc,(SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category))) AS parent_categoryname,(SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category))) AS primary_businessTypesc,(SELECT business_type_name FROM finascop_business_type WHERE business_type_id = (SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category)))) AS btName,stit_package_master,stit_courierWt,(SELECT rpckm_name FROM retaline_package_master WHERE rpckm_id = stit_package_master) AS rpckm_name 
    FROM  third_party_products  WHERE stit_ID =  {$tpItemId} ";
            $data = $db->getFromDB($qry, true);
            $data['tpDescription'] = '';
            $data['tpLongDescription'] = '';
        } else {
            $qry = "SELECT gpe.id,gpe.brand,gpe.name,gpe.category,gpe.sub_category,gpe.company_detail as company,
    stit_product_variant,stit_quantity,gpe.derived_description,stit_SKU,stit_ID as itemId,stit_itemId as item ,stit_HSNCode as HSN,taxValueId,stit_hsnId,stit_GST as GST,stit_MRP as MRP, stgp_groupID as itemgroup,stit_displaylabel,stit_product_variant,stit_itemReturnTime,stit_custInitiate,"
                . "stit_package_type_namme ,pdt_package_type_id,product_category,pdt_brand,featured,pdt_sale_rate,popular,item_length,item_breadth,item_height,item_weight,stit_qty,stit_unit,"
                . "cos_nos,cos_package_type_id,cos_package_type_name,cosb_package_type_id,cosb_package_type_name,cos_length,cos_length,cos_breadth,cos_height,cos_weight,"
                . "ccs_nos,ccs_package_type_id,ccs_package_type_name,ccsb_package_type_id,ccsb_package_type_name,ccs_length,ccs_breadth,ccs_height,ccs_weight,"
                . "rs_nos,rs_package_type_id,rs_package_type_name,rsb_package_type_id,rsb_package_type_name,rs_length,rs_breadth,rs_height,rs_weight,"
                . "cs_nos,cs_package_type_id,cs_package_type_name,csb_package_type_id,csb_package_type_name,cs_length,cs_breadth,cs_height,cs_weight,"
                . "ds_nos,ds_package_type_id,ds_package_type_name,dsb_package_type_id,dsb_package_type_name,ds_length,ds_breadth,ds_height,ds_weight,courierDelivery,directDelivery,directPurchase,stit_foodtype,stit_orgin_country,"
                . "(SELECT country_name FROM finascop_country WHERE country_id = stit_orgin_country) as orgCountryName,isRRPApplicable,stit_ingredients,stit_preparation_use,stit_allergens,stit_nutritionlabel,"
                . " stit_Description as description,stit_long_description,stit_quantity,stit_HSN_code,stit_brand_name,stit_category_name,stit_itemName,stitl1_optimumqty,stitl2_optimumqty,stitl3_optimumqty,"
                . "stit11_minimumqty,stit12_minimumqty,stit13_minimumqty,stit11_maximumqty,stit12_maximumqty,stit13_maximumqty,stii_csb,stii_csbretail,isMedicine,least_package_type_id,least_package_type_name,"
                . "stit_package_type_id,stit_stdPacking,stdpckl11_package_type_id,stdpckl1_nos,stdpckl12_package_type_id,stdpckl21_package_type_id,stdpckl2_nos,stdpckl22_package_type_id,stdpckl31_package_type_id,
stdpckl3_nos,stdpckl32_package_type_id,stdpckl41_package_type_id,stdpckl4_nos,stdpckl42_package_type_id,stit_salesUnit,stit_storage_instruction,stit_safety_warning,stit_warning,
(SELECT category_name FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category)) AS main_categoryName,(SELECT parent_category FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category)) AS parent_categorysc,(SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category))) AS parent_categoryname,(SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category))) AS primary_businessTypesc,(SELECT business_type_name FROM finascop_business_type WHERE business_type_id = (SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category)))) AS btName,stit_package_master,stit_courierWt,(SELECT rpckm_name FROM retaline_package_master WHERE rpckm_id = stit_package_master) as rpckm_name 
FROM finascop_stock_itemmaster  
    LEFT JOIN gs1_products_extension gpe ON stit_ID =  gpe.productId WHERE stit_ID =  {$productId} ";
            $data = $db->getFromDB($qry, true);
            $data['tpDescription'] = $db->getItemFromDB("SELECT stit_Description FROM third_party_products WHERE stit_ID = {$tpItemId}");
            $data['tpLongDescription'] = $db->getItemFromDB("SELECT stit_long_description FROM third_party_products WHERE stit_ID = {$tpItemId}");
        }

        if ($tpItemId > 0 && $productId > 0) {
            $Count = "SELECT SUM(imgCount)
        FROM
           (
           SELECT COUNT(*) AS imgCount FROM thirdparty_item_images WHERE product_id = {$tpItemId}
           UNION ALL
           SELECT COUNT(*) AS imgCount FROM finascop_stock_item_images WHERE product_id = {$productId}
           ) totalImages";
        } else {
            $Count = "SELECT COUNT(*)  FROM thirdparty_item_images WHERE product_id = {$tpItemId}";
        }
        $imageCount = $db->getItemFromDB($Count);
        $data['imageCount'] = $imageCount;


        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'saveAndCombineProduct':
        $toExport = 0;
        $itemIdFrPt = 0;
        $tpItemId = $_POST['tpItemId'];
        $gsId = $_POST['gsId'];
        $productId = $_POST['productId'];
        $createProduct = $_POST['createProduct'];


        //$SKU = $_POST['stit_brand_name'] . " " . $_POST['item_name'] . " " . $_POST['stit_product_variant'] . " " . $_POST['stit_quantity']. " " . $_POST['least_package_type_name'];
        $SKU = $_POST['stit_SKU'];
        $dSKU = addslashes($SKU);
        $manufactureId = $db->getItemSafe("SELECT manufacture_id FROM mypha_productbrands WHERE brand_id = ?", "s", [$_POST['pdt_brand']]);
        $manufactureDetails = $db->getFromDB("SELECT manufacture_id,manufacture_name FROM mypha_productmanufacture WHERE manufacture_id = {$manufactureId}", true);

        $stit_productsFor = $_POST['stit_productsFor'];
        $stit_productsForCountries = explode(',', $stit_productsFor);
        $stit_hsnId = $db->getItemSafe("SELECT hsn_id FROM finascop_hsn WHERE hsn_code = ?", "s", [$_POST['HSN']]);
        if (empty($stit_hsnId)) {
            $hsnData['hsn_code'] = $_POST['HSN'];
            $hsnData['gst_percent'] = $_POST['GST'];
            $status = $db->perform('finascop_hsn', $hsnData);
            $stit_hsnId = $db->insert_id();

            $ptviData['hsnGst'] = $_POST['GST'];
            $ptvData['hsnId'] = $stit_hsnId;
            $status = $db->perform('hsn_value', $ptvData);
            $data['taxValueId'] = $db->insert_id();
        }
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
            "stit_Description" => (!empty($_POST['description']) ? $_POST['description'] : ''),
            "stit_product_variant" => (!empty($_POST['stit_product_variant']) ? $_POST['stit_product_variant'] : ''),
            "product_category" => ($_POST['product_category'] > 0 ? $_POST['product_category'] : 0),
            "pdt_brand" => ($_POST['pdt_brand'] > 0 ? $_POST['pdt_brand'] : 0),
            "featured" => ($_POST['featured'] > 0 ? $_POST['featured'] : 0),
            "popular" => ($_POST['popular'] > 0 ? $_POST['popular'] : 0),
            "med_manufactureid" => ($manufactureDetails['manufacture_id'] > 0 ? $manufactureDetails['manufacture_id'] : 0),
            "med_manufacturename" => (!empty($manufactureDetails['manufacture_name']) ? $manufactureDetails['manufacture_name'] : ''),
            "stit_long_description" => (!empty($_POST['stit_long_description']) ? $_POST['stit_long_description'] : ''),
            "stit_quantity" => ($_POST['stit_quantity'] > 0 ? $_POST['stit_quantity'] : 0),
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
            "stit_courierWt" => (!empty($_POST['stit_courierWt']) ? $_POST['stit_courierWt'] : ''),
            "item_length" => (!empty($_POST['item_length']) ? $_POST['item_length'] : 0),
            "item_breadth" => (!empty($_POST['item_breadth']) ? $_POST['item_breadth'] : 0),
            "item_height" => (!empty($_POST['item_height']) ? $_POST['item_height'] : 0)

        );


        $fsuidata['fsi_item_id'] = $data['stit_itemId'];
        $fsuidata['fsi_item_name'] = $data['stit_itemName'];
        $fsuidata['fsi_brand_id'] = $data['pdt_brand'];
        $fsuidata['fsi_brand_name'] = $data['stit_brand_name'];
        $fsuidata['fsi_category_id'] = $data['product_category'];
        $fsuidata['fsi_categry_name'] = $data['stit_category_name'];
        $fsuidata['fsi_variant'] = $data['stit_product_variant'];
        $fsuidata['fsi_displaylabel'] = $data['stit_displaylabel'];
        $fsuidata['isMedicine'] = 0;


        $itemName = $_POST['item'];



        $db->query('begin');
        if ($_POST['directPurchase'] == 0) {
            if (empty($_POST['stit_unit']) || empty($_POST['stit_qty'])) {
                echo "{success: false, msg:'Enter Quantity and unit.'}";
                exit;
            }
        }
        if ($_POST['least_package_type_id'] > 0) {
            $lptyp = " AND least_package_type_id = " . intval($_POST['least_package_type_id']) . " ";
        } else {
            $lptyp = " ";
        }
        if ($_POST['createProduct'] == 1) {

            $stit_fsiuid = updateUniqueItemTable(0, $fsuidata);
            $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];

            $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from finascop_stock_itemmaster  WHERE stit_itemId = '{$itemName}' AND  stit_product_variant = '{$_POST['stit_product_variant']}' "
                . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} AND isMedicine = 0 ");
            if ($IsItemNameUnique > 0) {
                echo "{success: false, msg:'This Item already existing.'}";
                exit;
            }
            $SKUUnique = $db->getItemFromDB("SELECT COUNT(*) from  finascop_stock_itemmaster  WHERE stit_SKU = '{$dSKU}'  ");
            if ($SKUUnique > 0) {
                echo "{success: false, msg:'This SKU already existing.'}";
                exit;
            }

            $data['createdOn'] = date('Y-m-d H:i:s');
            $data['createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform("finascop_stock_itemmaster", $data);
            $itemIdFrPt = $db->insert_id();

            if ($stit_productsForCountries[0] > 0) {
                $status = $db->query("DELETE FROM finascop_stock_itemmaster_country WHERE stitc_stit_id = {$itemIdFrPt}");
                for ($i = 0; $i < count($stit_productsForCountries); $i++) {
                    $fsitcdata['stitc_stit_id'] = $itemIdFrPt;
                    $fsitcdata['stitc_country'] = $stit_productsForCountries[$i];
                    $status = $db->perform("finascop_stock_itemmaster_country", $fsitcdata);
                }
            }
            $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM finascop_stock_itemmaster WHERE stit_fsiuid = {$stit_fsiuid['fsi_uid']}");
            if ($stit_fsiuid['status'] == 'NEW') {

                $db->perform("finascop_stock_uniqueitem", $uuit, 'update', " fsi_uid = {$stit_fsiuid['fsi_uid']}");
            }
            $message = 'Saved Successfully';
        } else {
            $itemIdFrPt = $_POST['productId'];
            $fsiUid = $db->getItemSafe("SELECT stit_fsiuid FROM finascop_stock_itemmaster WHERE stit_ID = ?", "i", [$_POST['productId']]);
            $stit_fsiuid = updateUniqueItemTable($fsiUid, $fsuidata);
            $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];
            $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from finascop_stock_itemmaster  WHERE stit_itemId = '{$itemName}' AND stit_ID <> {$_POST['productId']} AND isMedicine = 0 "
                . "AND  stit_product_variant = '{$_POST['stit_product_variant']}' AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} {$lptyp} ");
            if ($IsItemNameUnique > 0) {
                echo "{success: false, msg:'This Item already existing.'}";
                exit;
            }
            $SKUUnique = $db->getItemSafe("SELECT COUNT(*) from  finascop_stock_itemmaster  WHERE stit_SKU = '{$dSKU}'  AND stit_ID <> ?", "i", [$_POST['productId']]);
            if ($SKUUnique > 0) {
                echo "{success: false, msg:'This SKU already existing.'}";
                exit;
            }
            $con = 'stit_ID=' . intval($_POST['productId']);
            $data['updatedOn'] = date('Y-m-d H:i:s');
            $data['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform("finascop_stock_itemmaster", $data, 'update', $con);

            if ($stit_productsForCountries[0] > 0) {
                $status = $db->query("DELETE FROM finascop_stock_itemmaster_country WHERE stitc_stit_id = {$itemIdFrPt}");
                for ($i = 0; $i < count($stit_productsForCountries); $i++) {
                    $fsitcdata['stitc_stit_id'] = $itemIdFrPt;
                    $fsitcdata['stitc_country'] = $stit_productsForCountries[$i];
                    $status = $db->perform("finascop_stock_itemmaster_country", $fsitcdata);
                }
            }
            $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM finascop_stock_itemmaster WHERE stit_fsiuid = {$stit_fsiuid['fsi_uid']}");
            if ($stit_fsiuid['status'] == 'NEW') {
                $db->perform("finascop_stock_uniqueitem", $uuit, 'update', " fsi_uid = {$stit_fsiuid['fsi_uid']}");
            }

            $message = "Updated Successfully";
        }
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
        $status = $db->perform("finascop_stock_itemmaster", $packingdata, 'update', $condit);



        //update third part products
        $tpStock['stit_id'] = $itemIdFrPt;
        $tpStock['updated_on'] = date('Y-m-d H:i:s');
        //$status = $db->perform('third_party_stock',$tpStock,'update'," stit_id= {$tpItemId}");

        $getTPImages = $db->getMultipleData("SELECT image_url,image_folder FROM thirdparty_item_images WHERE status = 1 AND product_id = {$tpItemId}", true);
        foreach ($getTPImages as $getTPImage) {
            $prdctImage['image_url'] = $getTPImage['image_url'];
            $prdctImage['image_folder'] = $getTPImage['image_folder'];
            $prdctImage['product_id'] = $itemIdFrPt;
            $prdctImage['image_type'] = $getTPImage['image_type'];
            $prdctImage = array_filter($prdctImage);
            if (count($prdctImage) > 0)
                $status = $db->perform('finascop_stock_item_images', $prdctImage);
        }
        $status = $db->query("DELETE FROM finascop_stock_item_images WHERE status = 0");
        $tpdata['updatedOn'] = date('Y-m-d H:i:s');
        $tpdata['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $tpdata['mappedProductId'] = $itemIdFrPt;
        $tpdata['isDelete'] = 1;
        $status = $db->perform('third_party_products', $tpdata, 'update', " stit_ID= {$tpItemId}");

        $isGrozeoPrdct = $db->getItemFromDB("SELECT grozeo_stitId FROM third_party_products WHERE stit_ID= {$tpItemId}");
        if ($isGrozeoPrdct > 0) {
            $isProductExported = $db->getItemFromDB("SELECT grozeo_stitId FROM product_grozeo_map WHERE product_stitId= {$itemIdFrPt}");
            if ($isProductExported > 0) {
                $toExport = 0;
                $grPrdInac['stit_status'] = 0;
                $grPrdInac['updatedOn'] = date('Y-m-d H:i:s');
                $status = $parentdb->perform('finascop_stock_itemmaster', $grPrdInac, 'update', " stit_ID= {$isGrozeoPrdct}");
                $grozeoHasStock = $parentdb->getFromDB("SELECT * FROM finascop_stock_branch_inventory WHERE stit_id = {$isGrozeoPrdct}", true);
                if ($grozeoHasStock['id'] > 0) {
                    $grzStockUp['status'] = 0;
                    $grzStockUp['updated_on'] = date('Y-m-d H:i:s');
                    $status = $parentdb->perform('finascop_stock_branch_inventory', $grzStockUp, 'update', " stit_id = {$isGrozeoPrdct} ");
                    //isProductExported have stock in grozeo
                    $hasStock = $parentdb->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory WHERE stit_id= {$isProductExported} AND branch_id = {$grozeoHasStock['branch_id']} ");
                    if ($hasStock == 0) {
                        $stckIns['stit_id'] = $isProductExported;
                        $stckIns['branch_id'] = $grozeoHasStock['branch_id'];
                        $stckIns['item_count'] = $grozeoHasStock['item_count'];
                        $stckIns['created_at'] = date('Y-m-d H:i:s');
                        $status = $parentdb->perform('finascop_stock_branch_inventory', $stckIns);
                    }
                }
            } else {
                $toExport = 1;
                $pgmIns['grozeo_stitId'] = $isGrozeoPrdct;
                $pgmIns['product_stitId'] = $itemIdFrPt;
                $pgmIns['createdOn'] = date('Y-m-d H:i:s');
                $status = $db->perform('product_grozeo_map', $pgmIns);
                $grPrdUp['mapping_id'] = $itemIdFrPt;
                $grPrdUp['stit_StoreGroup'] = 0;
                $grPrdUp['updatedOn'] = date('Y-m-d H:i:s');
                $status = $parentdb->perform('finascop_stock_itemmaster', $grPrdUp, 'update', " stit_ID= {$isGrozeoPrdct}");
            }
        }
        $db->query('commit');
        if ($status) {
            echo "{success: true,stit_ID:{$itemIdFrPt},toExport:{$toExport},msg:'{$message}'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data' }";
        }
        break;
    case 'skipTPProducts':
        $tpItemId = $_POST['tpItemId'];
        $db->query('begin');
        $tpdata['skippedOn'] = date('Y-m-d H:i:s');
        $tpdata['skippedBy'] = $_SESSION['admin']->Finascop_UserId;
        $tpdata['isSkip'] = 1;
        $status = $db->perform('third_party_products', $tpdata, 'update', " stit_ID= {$tpItemId}");
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Products is skipped.'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data' }";
        }
        break;
    case 'listMerchantProduct':
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['item_id', 'item_name', 'item_code', 'item_created_on'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'item_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'third_party_products.stit_ID' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $search = " WHERE 1=1 AND tpConvertedStatus = 2";
        if (isset($filter)) {
            $filter = $_POST['filter'];
            foreach ($filter as $key => $val) {
                $type = $val['data']['type'];
                $value = $val['data']['value'];
                $field = $val['field'];
                switch ($val['data']['type']) {
                    case 'string':
                        if ($val['field'] == 'hsn_code') {
                            $hsn_code = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(hsn_id),0) FROM finascop_hsn WHERE hsn_code LIKE '{$val['data']['value']}%' ");
                            $search .= " AND stit_HSNCode IN({$hsn_code}) ";
                        } else if ($val['field'] == 'statusName') {
                            if ($val['data']['value'] == 'Active') {
                                $search .= " and stit_status = 1 ";
                            } else if ($val['data']['value'] == 'Inactive') {
                                $search .= " and stit_status = 0 ";
                            } else {
                                $search .= " and stit_status IN(1,0) ";
                            }
                        } else if ($val['field'] == 'isVerified') {
                            if ($val[data][value] == 'Yes') {
                                $search .= " and (isVerified = 1) ";
                            } else if ($val[data][value] == 'No') {
                                $search .= " and (isVerified = 0) ";
                            } else {
                                $search .= " and (isVerified IN(1,0)) ";
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
                }
            }
        }
        $query = "SELECT tpConvertedStatus,stit_ID,stit_SKU,stit_itemName,stit_HSN_code,stit_category_name,stit_brand_name,stit_quantity,
             stit_product_variant,least_package_type_name,IF(tpStatus = 1,'Unverified','Imprecise') AS tpStatusName,tpStatus FROM third_party_products";
        $countQuery = "SELECT count(*) FROM ({$query}) as count {$search}  order by {$sort} {$dir}";

        $qry = "SELECT * FROM ({$query}) AS list {$search}  order by {$sort} {$dir}  ";
        $count = $db->getItemFromDB($countQuery);
        $listQuery = "{$qry} limit {$rec_start},{$rec_limit}";
        $db->printGridJson($countQuery, $listQuery);

        break;
    case 'listProductimages':
        $rec_limit = empty($_POST['limit']) ? 28 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $productId = $_POST['productId'];
        $tpItemId = $_POST['tpItemId'];
        $cond = '';


        $bucketPath = AWSBUCKETPATH;
        $prviewPath = SLTHUMP;
        $uploadfolder = AWSBUCKETFOLDER;

        if ($tpItemId > 0 && $productId > 0) {
            $qry = "SELECT CONCAT('{$bucketPath}','/','{$uploadfolder}','',image_url) as slgalimg_path,CONCAT('{$bucketPath}','/','{$uploadfolder}','thumbnail-',image_url) AS thumpimg_path,id,'Third Party' AS imagetype,product_id,status FROM thirdparty_item_images  WHERE product_id = {$tpItemId}
        UNION 
         SELECT CONCAT('{$bucketPath}','/','{$uploadfolder}','',image_url) AS slgalimg_path,CONCAT('{$bucketPath}','/','{$uploadfolder}','thumbnail-',image_url) AS thumpimg_path,id,'Product' AS imagetype,product_id,status FROM finascop_stock_item_images  WHERE product_id = {$productId} {$cond}  limit $rec_start,$rec_limit ";
            $Count = "SELECT SUM(imgCount)
        FROM
           (
           SELECT COUNT(*) AS imgCount FROM thirdparty_item_images WHERE product_id = {$tpItemId}
           UNION ALL
           SELECT COUNT(*) AS imgCount FROM finascop_stock_item_images WHERE product_id = {$productId}
           ) totalImages";
        } else {
            $qry = "SELECT CONCAT('{$bucketPath}','/','{$uploadfolder}','',image_url) as slgalimg_path,id,'Third Party' AS imagetype,product_id,status FROM thirdparty_item_images  WHERE product_id = {$tpItemId}";
            $Count = "SELECT COUNT(*)  FROM thirdparty_item_images WHERE product_id = {$tpItemId}";
        }

        $db->printGridJson($Count, $qry);
        break;
    case 'changeStatus':
        $db->query('begin');
        if ($_POST['imagetype'] == 'Third Party') {
            $status = $db->executeSafe("UPDATE thirdparty_item_images SET status = 0 where id = ?", "i", [$_POST['id']]);
        } else {
            $status = $db->executeSafe("UPDATE finascop_stock_item_images SET status = 0 where id = ?", "i", [$_POST['id']]);
        }
        $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Product image removed.'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data' }";
        }
        break;
    case 'getItemCategory':
        $sub_category_id = $_POST['sub_category_id'];
        $isPerishable = $db->getItemFromDB("SELECT isPerishable FROM mypha_productsubcategory WHERE sub_category_id = {$sub_category_id}");
        $main_category = $db->getItemFromDB("SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = {$sub_category_id}");
        $parent_category = $db->getItemFromDB("SELECT parent_category FROM mypha_productcategory WHERE category_id = {$main_category}");

        $itemHistory['iteParentCategory'] = $db->getItemFromDB("SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = {$parent_category}");
        $parent_category_businessType = $db->getItemFromDB("SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = {$parent_category}");
        $itemHistory['iteMidCategory'] = $db->getItemFromDB("SELECT category_name FROM mypha_productcategory WHERE category_id = {$main_category}");
        $parent_category_businessTypeName = $db->getItemFromDB("SELECT business_type_name FROM finascop_business_type WHERE business_type_id = {$parent_category_businessType}");
        $itemHistory['categoryCombination'] = $parent_category_businessTypeName . ' > ' . $itemHistory['iteParentCategory'] . ' > ' . $itemHistory['iteMidCategory'];
        $itemHistory['isPerishable'] = $isPerishable;
        if (!empty($itemHistory)) {
            echo json_encode($itemHistory);
        }
        break;
    case 'listPublicProduct':
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['item_id', 'item_name', 'item_code', 'item_created_on'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'item_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'third_party_products.stit_ID' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $search = " WHERE 1=1 AND tpConvertedStatus = 1";
        if (isset($filter)) {
            $filter = $_POST['filter'];
            foreach ($filter as $key => $val) {
                $type = $val['data']['type'];
                $value = $val['data']['value'];
                $field = $val['field'];
                switch ($val['data']['type']) {
                    case 'string':
                        if ($val['field'] == 'hsn_code') {
                            $hsn_code = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(hsn_id),0) FROM finascop_hsn WHERE hsn_code LIKE '{$val['data']['value']}%' ");
                            $search .= " AND stit_HSNCode IN({$hsn_code}) ";
                        } else if ($val['field'] == 'statusName') {
                            if ($val['data']['value'] == 'Active') {
                                $search .= " and stit_status = 1 ";
                            } else if ($val['data']['value'] == 'Inactive') {
                                $search .= " and stit_status = 0 ";
                            } else {
                                $search .= " and stit_status IN(1,0) ";
                            }
                        } else if ($val['field'] == 'isVerified') {
                            if ($val[data][value] == 'Yes') {
                                $search .= " and (isVerified = 1) ";
                            } else if ($val[data][value] == 'No') {
                                $search .= " and (isVerified = 0) ";
                            } else {
                                $search .= " and (isVerified IN(1,0)) ";
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
                }
            }
        }
        $query = "SELECT tpConvertedStatus,stit_ID,stit_SKU,stit_itemName,stit_HSN_code,stit_category_name,stit_brand_name,stit_quantity,
                 stit_product_variant,least_package_type_name,IF(tpStatus = 1,'Unverified','Imprecise') AS tpStatusName,tpStatus FROM third_party_products";
        $countQuery = "SELECT count(*) FROM ({$query}) as count {$search}  order by {$sort} {$dir}";

        $qry = "SELECT * FROM ({$query}) AS list {$search}  order by {$sort} {$dir}  ";
        $count = $db->getItemFromDB($countQuery);
        $listQuery = "{$qry} limit {$rec_start},{$rec_limit}";
        $db->printGridJson($countQuery, $listQuery);

        break;
}
