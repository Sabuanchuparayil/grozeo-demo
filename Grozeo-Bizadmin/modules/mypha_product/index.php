<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");
switch ($op) {
    case 'listItemMasterData':
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['item_id', 'item_name', 'item_code'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'item_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'stit_ID' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $search = " WHERE 1=1 ";
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
        $countQuery = "SELECT count(*)
		from " . FINASCOP_DB . "finascop_stock_itemmaster {$search} AND isMedicine = 0 order by {$sort} {$dir}";
        //$count = $db->getItemFromDB($countQuery);
        $total = "select SUM(stit_MRP) AS total_mrp,SUM(stit_GST) as tax_total "
            . " from  finascop_stock_itemmaster  order by stit_ID desc";
        $coltotal = $db->getFromDB($total, true);
        $qry = "SELECT 
        stit_ID AS ItemId,
        product_is_home,
        stit_itemName,
        stit_status,
        least_package_type_id,
        least_package_type_name,
        IF(stit_status = 1, 'Active', 'Inactive') AS statusName,
        product_category,
        stit_HSN_code,
        taxValueId,
        stit_package_type_namme,
        stit_category_name,
        stit_brand_name,
        stit_product_variant,
        stit_quantity,
        stit_GST AS tax,
        stit_MRP AS mrp,
        IF(featured = 1, 'Yes', 'No') AS isFeatured,
        IF(popular = 1, 'Yes', 'No') AS isPopular,
        (SELECT COUNT(1) 
         FROM finascop_stock_item_images 
         WHERE product_id = stit_ID AND image_type = 1) AS miCount,
        (SELECT COUNT(1) 
         FROM finascop_stock_item_images 
         WHERE product_id = stit_ID AND image_type = 0) AS aiCount,
        CONCAT(
            (SELECT COUNT(1) 
             FROM finascop_stock_item_images 
             WHERE product_id = stit_ID AND image_type = 1), 
            '/', 
            (SELECT COUNT(1) 
             FROM finascop_stock_item_images 
             WHERE product_id = stit_ID AND image_type = 0)
        ) AS imgCount,
        stit_SKU,
        IF(isVerified = 1, 'Yes', 'No') AS isVerified,
        isMedicine,
        stit_displaylabel,
        itemProcessingTime
    FROM finascop_stock_itemmaster ";
        $count = $db->getItemFromDB("SELECT COUNT(*) FROM ({$qry}) AS countItem {$search} AND isMedicine = 0 ");
        $listQuery = "SELECT * FROM ({$qry} ) as itemList {$search} AND isMedicine = 0 order by {$sort} {$dir}  limit {$rec_start},{$rec_limit}";
        $data = $db->getMultipleData($listQuery, true);
        $result = [];
        foreach ($data as $key => $value) {

            foreach ($coltotal as $k => $v) {

                $value[$k] = $v;
                $result[$key] = $value;
            }
        }

        echo '{"totalCount":' . $count . ',"data":' . json_encode($result) . '}';
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
        $qry = "select stit_hsnId,stit_SKU,stit_ID as itemId,stit_itemId as item ,stit_HSNCode as HSN,stit_GST as GST,taxValueId,stit_MRP as MRP, stgp_groupID as itemgroup,stit_displaylabel,itemProcessingTime,stit_product_variant,stit_itemReturnTime,stit_custInitiate,"
            . "stit_package_type_namme ,pdt_package_type_id,product_category,pdt_brand,featured,pdt_sale_rate,popular,item_breadth,item_height,item_weight,stit_qty,stit_unit,"
            . "cos_nos,cos_package_type_id,cos_package_type_name,cosb_package_type_id,cosb_package_type_name,cos_length,cos_length,cos_breadth,cos_height,cos_weight,"
            . "ccs_nos,ccs_package_type_id,ccs_package_type_name,ccsb_package_type_id,ccsb_package_type_name,ccs_length,ccs_breadth,ccs_height,ccs_weight,"
            . "rs_nos,rs_package_type_id,rs_package_type_name,rsb_package_type_id,rsb_package_type_name,rs_length,rs_breadth,rs_height,rs_weight,"
            . "cs_nos,cs_package_type_id,cs_package_type_name,csb_package_type_id,csb_package_type_name,cs_length,cs_breadth,cs_height,cs_weight,"
            . "ds_nos,ds_package_type_id,ds_package_type_name,dsb_package_type_id,dsb_package_type_name,ds_length,ds_breadth,ds_height,ds_weight,courierDelivery,directDelivery,directPurchase,stit_foodtype,stit_orgin_country,"
            . "(SELECT country_name FROM finascop_country WHERE country_id = stit_orgin_country) as orgCountryName,isRRPApplicable,stit_ingredients,stit_preparation_use,stit_allergens,stit_nutritionlabel,"
            . "stit_package_master,stit_courierWt,(SELECT rpckm_name FROM retaline_package_master WHERE rpckm_id = stit_package_master) as rpckm_name,"
            //."(select stgp_groupName from ".FINASCOP_DB."finascop_stock_group b where b.stgp_groupID = a.stgp_groupID)as groups,"
            . " stit_Description as description,stit_long_description,stit_quantity,stit_HSN_code,stit_brand_name,stit_category_name,stit_itemName,stitl1_optimumqty,stitl2_optimumqty,stitl3_optimumqty,"
            . "stit11_minimumqty,stit12_minimumqty,stit13_minimumqty,stit11_maximumqty,stit12_maximumqty,stit13_maximumqty,stii_csb,stii_csbretail,isMedicine,least_package_type_id,least_package_type_name,"
            . "stit_package_type_id,stit_stdPacking,stdpckl11_package_type_id,stdpckl1_nos,stdpckl12_package_type_id,stdpckl21_package_type_id,stdpckl2_nos,stdpckl22_package_type_id,stdpckl31_package_type_id,
stdpckl3_nos,stdpckl32_package_type_id,stdpckl41_package_type_id,stdpckl4_nos,stdpckl42_package_type_id,stit_salesUnit,item_length from " . FINASCOP_DB . "finascop_stock_itemmaster  where stit_ID = '$id' ";
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'saveItemMaster':
        //stit_qty,stit_unit
        //$SKU = $_POST['stit_category_name'] . " " . $_POST['stit_brand_name'] . " " . $_POST['item_name'] . " " . $_POST['stit_product_variant'] . " " . $_POST['stit_quantity'];
        //On 01-10-2020
        //$SKU = $_POST['stit_brand_name'] . " " . $_POST['item_name'] . " " . $_POST['stit_product_variant'] . " " . $_POST['stit_quantity'] . " " . $_POST['least_package_type_name'];
        $SKU = $_POST['stit_SKU'];
        $dSKU = addslashes($SKU);
        $manufactureId = $db->getItemSafe("SELECT manufacture_id FROM mypha_productbrands WHERE brand_id = ?", "s", [$_POST['pdt_brand']]);
        $manufactureDetails = $db->getFromDB("SELECT manufacture_id,manufacture_name FROM mypha_productmanufacture WHERE manufacture_id = $manufactureId", true);
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
            "stit_hsnId" => $stit_hsnId,
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
            "cosb_package_type_id" => $_POST['cosb_package_type_id'],
            "cosb_package_type_name" => $_POST['cosb_package_type_name'],
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
            "dsb_package_type_id" => $_POST['dsb_package_type_id'],
            "dsb_package_type_name" => $_POST['dsb_package_type_name'],
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
            "stit_package_master" => (!empty($_POST['stit_package_master']) ? $_POST['stit_package_master'] : 0),
            "stit_courierWt" => (!empty($_POST['stit_courierWt']) ? $_POST['stit_courierWt'] : ''),
            "item_length" => ($_POST['item_length'] > 0 ? $_POST['item_length'] : 0),
            "item_breadth" => ($_POST['item_breadth'] > 0 ? $_POST['item_breadth'] : 0),
            "item_height" => ($_POST['item_height'] > 0 ? $_POST['item_height'] : 0),
        );



        //$data = array_filter($data);

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

        if ($_POST['dupitem'] == 'D') {
            unset($_POST['id']);
        }
        $db->query('begin');
        if ($_POST['directPurchase'] == 0) {
            if (empty($_POST['stit_unit']) || empty($_POST['stit_qty'])) {
                echo "{success: false, msg:'Enter Quantity and unit.'}";
                exit;
            }
        }
        if (empty($_POST['id'])) {

            $stit_fsiuid = updateUniqueItemTable(0, $fsuidata);
            $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];

            $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  WHERE stit_itemId = '{$itemName}' AND  stit_product_variant = '{$_POST['stit_product_variant']}' "
                . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} AND isMedicine = 0 ");
            if ($IsItemNameUnique > 0) {
                echo "{success: false, msg:'This Item already existing.'}";
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
            $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  WHERE stit_itemId = '{$itemName}' AND stit_ID <> {$_POST['id']} AND isMedicine = 0 "
                . "AND  stit_product_variant = '{$_POST['stit_product_variant']}' AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$_POST['product_category']} AND pdt_brand = {$_POST['pdt_brand']} AND stit_SKU = '{$dSKU}'");
            if ($IsItemNameUnique > 0) {
                echo "{success: false, msg:'This Item already existing.'}";
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
            $uuit['fsi_def_itemmaster_id'] = $db->getItemFromDB("SELECT MIN(stit_ID) FROM finascop_stock_itemmaster WHERE stit_fsiuid = {$stit_fsiuid['fsi_uid']}");
            if ($stit_fsiuid['status'] == 'NEW') {
                $db->perform(FINASCOP_DB . "finascop_stock_uniqueitem", $uuit, 'update', " fsi_uid = {$stit_fsiuid['fsi_uid']}");
            }

            $fpodData['fpod_itemname'] = $dSKU;
            $status = $db->perform(FINASCOP_DB . "finascop_purchase_order_details", $fpodData, 'update', "fpod_itemid = " . intval($_POST['id']));

            $fsiiData['stii_itemmastername'] = $dSKU;
            $status = $db->perform(FINASCOP_DB . "finascop_stock_item_inventory", $fsiiData, 'update', "stii_itemmasterid = " . intval($_POST['id']));

            $fsiidData['stiid_itemmastername'] = $dSKU;
            $status = $db->perform(FINASCOP_DB . "finascop_stock_item_inventorydetails", $fsiidData, 'update', "stiid_itemmasterid = " . intval($_POST['id']));
            $message = "Updated Successfully";
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
        $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'{$message}'}";
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

        $isGlobal = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id = '{$fstrd['fsipc_stit_id']}' AND fsipc_isCompany = 1");

        if ($_POST['stit_storeGroup'] > 0 && $fstrd['fsipc_isIndividual'] = 0) {
            $isStoreGlobal = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id = '{$fstrd['fsipc_stit_id']}' "
                . "AND fsipc_isCompany = 0 AND fsipc_storeGroup = {$_POST['stit_storeGroup']} AND fsipc_isIndividual = 0");
            $isStoreGlobalCodeExist = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id <> '{$fstrd['fsipc_stit_id']}' "
                . "AND fsipc_isCompany = 0 AND fsipc_storeGroup = {$_POST['stit_storeGroup']} AND fsipc_isIndividual = 0 AND fsipc_code = '{$_POST['stit_code']}'");
        }
        if ($_POST['stit_storeGroup'] > 0 && $_POST['stit_store'] > 0) {
            $isCodeinStore = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id = '{$fstrd['fsipc_stit_id']}' "
                . "AND fsipc_isCompany = 0 AND fsipc_storeGroup = {$_POST['stit_storeGroup']} AND fsipc_isIndividual = 1 AND fsipc_store = {$_POST['stit_store']}");
            $codeCodeAlreadyExist = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id <> '{$fstrd['fsipc_stit_id']}' "
                . "AND fsipc_isCompany = 0 AND fsipc_storeGroup = {$_POST['stit_storeGroup']} AND fsipc_isIndividual = 1 AND fsipc_store = {$_POST['stit_store']} AND fsipc_code = '{$_POST['stit_code']}'");
        }

        if ($codeCodeAlreadyExist > 0 || $isStoreGlobalCodeExist > 0) {
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
        if ($isStoreGlobal == 0 && $isGlobal == 0 && $isCodeinStore == 0) {

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
        }
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
            if ($count > 0) {
                echo '{"totalCount":' . $count . ',"data":' . json_encode($result) . '}';
            } else {
                echo '{"totalCount":0,"data":[]}';
            }
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
            $isInserted = $db->getItemFromDB("SELECT COUNT(*) FROM attributeProductMap WHERE attributeId = {$attributeId} AND stitId = {$ItemId} ");
            if ($isInserted > 0) {
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
            }
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
            $productAttributeDetails = $db->getMultipleData("SELECT * FROM attributeProductMap WHERE attributeId = {$attributeId} AND stitId = {$ItemId} ", true);
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
    case 'listPrivateProducts':
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['item_id', 'item_name', 'item_code'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'item_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'stit_ID' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $search = " WHERE 1=1 ";
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
        $countQuery = "SELECT count(*)
            from  finascop_stock_itemmaster {$search} AND isMedicine = 0 order by {$sort} {$dir}";
        //$count = $db->getItemFromDB($countQuery);
        $total = "select SUM(stit_MRP) AS total_mrp,SUM(stit_GST) as tax_total "
            . " from  finascop_stock_itemmaster  order by stit_ID desc";
        $coltotal = $db->getFromDB($total, true);
        $qry = "
    SELECT 
        bg.store_group_name,
        sm.stit_ID AS ItemId,
        sm.product_is_home,
        sm.stit_itemName,
        sm.stit_status,
        sm.least_package_type_id,
        sm.least_package_type_name,
        IF(sm.stit_status = 1, 'Active', 'Inactive') AS statusName,
        sm.product_category,
        sm.stit_HSN_code,
        sm.taxValueId,
        sm.stit_package_type_namme,
        sm.stit_category_name,
        sm.stit_brand_name,
        sm.stit_product_variant,
        sm.stit_quantity,
        sm.stit_GST AS tax,
        sm.stit_MRP AS mrp,
        IF(sm.featured = 1, 'Yes', 'No') AS isFeatured,
        IF(sm.popular = 1, 'Yes', 'No') AS isPopular,
        (SELECT COUNT(1) 
         FROM finascop_stock_item_images 
         WHERE product_id = sm.stit_ID AND image_type = 1) AS miCount,
        (SELECT COUNT(1) 
         FROM finascop_stock_item_images 
         WHERE product_id = sm.stit_ID AND image_type = 0) AS aiCount,
        CONCAT(
            (SELECT COUNT(1) 
             FROM finascop_stock_item_images 
             WHERE product_id = sm.stit_ID AND image_type = 1), 
            '/', 
            (SELECT COUNT(1) 
             FROM finascop_stock_item_images 
             WHERE product_id = sm.stit_ID AND image_type = 0)
        ) AS imgCount,
        sm.stit_SKU,
        IF(sm.isVerified = 1, 'Yes', 'No') AS isVerified,
        sm.isMedicine,
        sm.stit_displaylabel,
        sm.itemProcessingTime
    FROM 
        finascop_stock_itemmaster sm
    INNER JOIN 
        finascop_branch_group bg 
        ON bg.store_group_id = sm.stit_StoreGroup
";
        $count = $db->getItemFromDB("SELECT COUNT(*) FROM ({$qry}) AS countItem {$search} AND isMedicine = 0 ");
        $listQuery = "SELECT * FROM ({$qry} ) as itemList {$search} AND isMedicine = 0 order by {$sort} {$dir}  limit {$rec_start},{$rec_limit}";
        $data = $db->getMultipleData($listQuery, true);
        $result = [];
        foreach ($data as $key => $value) {

            foreach ($coltotal as $k => $v) {

                $value[$k] = $v;
                $result[$key] = $value;
            }
        }

        echo '{"totalCount":' . $count . ',"data":' . json_encode($result) . '}';
        break;
}
