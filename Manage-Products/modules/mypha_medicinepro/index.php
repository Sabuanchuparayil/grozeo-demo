<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");
switch ($op) {
    case 'listItemMasterData':
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'stit_ID' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $search = " WHERE 1=1 ";
        
        if (isset($filter)) {
            if (isset($_POST['filter'])) {
        $allowedFields = ['med_id', 'med_name', 'med_composition', 'med_brand', 'med_type', 'med_status'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }%' ");
                        $search .= " AND stit_HSNCode IN({$hsn_code}) ";
                    }  else if ($val['field'] == 'statusName') {
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
                    case 'numeric' :
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
		from " . FINASCOP_DB . "finascop_stock_itemmaster {$search} AND isMedicine = 1 order by stit_itemId";
        //$count = $db->getItemFromDB($countQuery);
        $total = "select SUM(stit_MRP) AS total_mrp,SUM(stit_GST) as tax_total "
                . " from " . FINASCOP_DB . "finascop_stock_itemmaster {$search} order by stit_ID desc";
        $coltotal = $db->getFromDB($total, true);
        $qry = "SELECT * FROM (select  stit_ID as ItemId ,product_is_home, (SELECT item_name FROM finascop_stock_itemmastername where itemname_id = stit_itemId) as ItemName ,stit_status,least_package_type_id,least_package_type_name,"
                . "IF(stit_status = 1,'Active','Inactive') AS statusName,"
                //    . "stit_status AS statusName,"
                . "stit_itemName,stit_package_type_namme,stit_category_name,stit_brand_name,stit_product_variant,stit_quantity,"
                . "(SELECT hsn_code FROM finascop_hsn where hsn_id = stit_HSNCode) as hsn_code,stit_GST as tax,stit_MRP as mrp,(IF(stit_Convertible = 1,1,0) )AS convertable_off ,(IF(stit_Convertible = 0,1,0))AS convertable_on,"
                . "(IF(stit_SalesEnabled = 0,0,1) )AS list_in_sales_off ,(IF(stit_SalesEnabled = 1,0,1))AS list_in_sales_on,"
                . "(IF(stit_StockEnabled = 0,0,1) )AS stock_disabled ,(IF(stit_StockEnabled = 1,0,1))AS stock_enabled,"
                . "(IF(stit_PurchaseEnabled = 0,0,1) )AS list_in_purchase_off ,(IF(stit_PurchaseEnabled = 1,0,1))AS list_in_purchase_on,"
                . "(IF(stit_Tangible = 1,0,1) )AS tangible_off ,(IF(stit_Tangible = 0,0,1))AS tangible_on, @miCount:=(SELECT COUNT(1) FROM finascop_stock_item_images WHERE `product_id`= stit_ID AND `image_type` = 1 ), 
@aiCount:=(SELECT COUNT(1) FROM finascop_stock_item_images WHERE `product_id`= stit_ID AND `image_type` = 0), 
CONCAT(@miCount, '/',@aiCount) AS imgCount,stit_SKU,IF((isVerified = 1),'Yes','No') AS isVerified "
                . " from " . FINASCOP_DB . "finascop_stock_itemmaster {$search} AND isMedicine = 1) as itemList order by {$sort} {$dir}  ";
        $count = $db->getItemFromDB("SELECT COUNT(*) FROM ({$qry}) AS countItem");
        $listQuery = "{$qry} limit {$rec_start},{$rec_limit}";
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
        $qry = "select hsn_id,hsn_code,gst_percent from " . FINASCOP_DB . "finascop_hsn WHERE hsn_code LIKE '{$search_hint}%'order by hsn_code";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getMedicineName':
        $search_hint = $_POST['query'];
        $itemid = $_POST['itemid'];
        if ($_POST['query'] != '') {
            $cond = " AND medicineMaster_name LIKE '{$search_hint}%'";
        } else {
            $cond = "";
        }
        if ($_POST['query'] != '') {
            if ($itemid > 0) {
                $qry = $db->getMulipleData("SELECT medicineMaster_id,medicineMaster_name,medicine_type_name,composition_name,subCategory_name,medicine_type FROM mypha_medicineMaster "
                        . "INNER JOIN  mypha_medicineType ON medicine_type_id = medicine_type "
                        . "LEFT JOIN  mypha_composition mc ON composition_id = medicine_composition "
                        . "LEFT JOIN mypha_subCategory ms on ms.subCategory_id = mc.subCategory_id WHERE medicine_status = 1 {$cond} order by medicineMaster_name asc", true);
                if (!empty($qry)) {
                    echo '{"success":true,"data":' . json_encode($qry) . '}';
                    //echo json_encode($qry);
                } else
                    echo [];
            } else {
//            $qry = $db->getMulipleData("SELECT medicineMaster_id,medicineMaster_name,medicine_type_name,composition_name,subCategory_name FROM mypha_medicineMaster "
//                    . "INNER JOIN  mypha_medicineType ON medicine_type_id = medicine_type "
//                    . "INNER JOIN  mypha_composition mc ON composition_id = medicine_composition "
//                    . "INNER JOIN mypha_subCategory ms on ms.subCategory_id = mc.subCategory_id 
//                    LEFT JOIN finascop_stock_itemmaster ON stit_itemId = medicineMaster_id AND isMedicine = 1
//WHERE medicine_status = 1 AND stit_itemId IS NULL {$cond} order by medicineMaster_name asc", true);

                $medicines = $db->getMulipleData("SELECT medicineMaster_id,medicineMaster_name,medicine_type_name,composition_name,subCategory_name,medicine_type FROM mypha_medicineMaster "
                        . "INNER JOIN  mypha_medicineType ON medicine_type_id = medicine_type "
                        . "LEFT JOIN  mypha_composition mc ON composition_id = medicine_composition "
                        . "LEFT JOIN mypha_subCategory ms on ms.subCategory_id = mc.subCategory_id WHERE medicine_status = 1 {$cond} order by medicineMaster_name asc", true);
                $max_loop = 10;
                $count = 0;
                $response = array();
                foreach ($medicines as $medicine) {
                    $isinItemMAster = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster WHERE  isMedicine = 1 AND stit_itemId = {$medicine['medicineMaster_id']}");
                    if ($isinItemMAster == 0) {
                        array_push($response, $medicine);
                        $count++;
                        if ($count == $max_loop)
                            break;
                    }
                }
                if (!empty($response)) {
                    echo '{"success":true,"data":' . json_encode($response) . '}';
                    //echo json_encode($qry);
                } else
                    echo [];
            }
        } else
            echo [];



        break;
    case 'saveItemMaster':
        //$SKU = $_POST['stit_category_name'] . " " . $_POST['stit_brand_name'] . " " . $_POST['item_name'] . " " . $_POST['stit_product_variant'] . " " . $_POST['stit_quantity'];
        //$SKU = $_POST['item_name'] . " " . $_POST['med_generic_name'] . " " . $_POST['med_drug_groupname'] . " " . $_POST['med_dosage_form'] . " " . $_POST['stit_product_variant'] . " " . $_POST['stit_quantity'];
        //On 01-10-2020
        $SKU = $_POST['item_name'] . " " . $_POST['stit_product_variant'] . " " . $_POST['stit_quantity'];
        $dSKU = addslashes($SKU);
        $medBrandDetails = $db->getFromDB("SELECT medicineMaster_id,medicineMaster_name,manufacture_name,medicine_type_name,composition_name,subCategory_name,medicine_composition,mc.subCategory_id as subcateg,"
                . "medicine_type_id,medicine_manufacture FROM mypha_medicineMaster "
                . "INNER JOIN  mypha_medicineType ON medicine_type_id = medicine_type "
                . "INNER JOIN  mypha_composition mc ON composition_id = medicine_composition "
                . "INNER JOIN  mypha_manufacture ON manufacture_id = medicine_manufacture "
                . "INNER JOIN mypha_subCategory ms on ms.subCategory_id = mc.subCategory_id WHERE  medicineMaster_id = {$_POST['item']} ", true);
        $data = array(
            "stit_stdPacking" => ($_POST['stit_stdPacking'] > 0 ? $_POST['stit_stdPacking'] : 0),
            "stit_salesUnit" => ($_POST['stit_salesUnit'] > 0 ? $_POST['stit_salesUnit'] : 0),
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
            "stit_itemId" => $_POST['item'],
            "stit_SKU" => $SKU,
            "stit_HSNCode" => $_POST['HSN'],
            "stit_GST" => $_POST['GST'],
            "stit_itemReturnTime" => ($_POST['stit_itemReturnTime'] > 0) ? $_POST['stit_itemReturnTime'] : 0,
            "stit_custInitiate" => $_POST['stit_custInitiate'],
            "stit_Description" => $_POST['description'],
            "stit_product_variant" => $_POST['stit_product_variant'],
            "stit_displaylabel" => $_POST['display_label'],
            "featured" => $_POST['featured'],
            "popular" => $_POST['popular'],
            "prescription" => $_POST['prescription'],
            "stit_quantity" => $_POST['stit_quantity'],
            "stit_itemName" => $_POST['item_name'],
            "stit_HSN_code" => $_POST['HSN_code'],
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
            "stitl1_optimumqty" => $_POST['stitl1_optimumqty'],
            "stitl2_optimumqty" => $_POST['stitl2_optimumqty'],
            "stitl3_optimumqty" => $_POST['stitl3_optimumqty'],
            "stit11_minimumqty" => $_POST['stit11_minimumqty'],
            "stit12_minimumqty" => $_POST['stit12_minimumqty'],
            "stit13_minimumqty" => $_POST['stit13_minimumqty'],
            "stit11_maximumqty" => $_POST['stit11_maximumqty'],
            "stit12_maximumqty" => $_POST['stit12_maximumqty'],
            "stit13_maximumqty" => $_POST['stit13_maximumqty'],
            "stii_csb" => $_POST['stii_csb'],
            "cosb_package_type_id" => $_POST['cosb_package_type_id'],
            "cosb_package_type_name" => $_POST['cosb_package_type_name'],
            "cos_length" => ($_POST['cos_length'] > 0 ? $_POST['cos_length'] : 0),
            "cos_breadth" => ($_POST['cos_breadth'] > 0 ? $_POST['cos_breadth'] : 0),
            "cos_height" => ($_POST['cos_height'] > 0 ? $_POST['cos_height'] : 0),
            "cos_weight" => ($_POST['cos_weight'] > 0 ? $_POST['cos_weight'] : 0),
            "cos_volume" => ($_POST['cos_volume'] > 0 ? $_POST['cos_volume'] : 0),
            "ccsb_package_type_id" => $_POST['ccsb_package_type_id'],
            "ccsb_package_type_name" => $_POST['ccsb_package_type_name'],
            "ccs_length" => ($_POST['ccs_length'] > 0 ? $_POST['ccs_length'] : 0),
            "ccs_breadth" => ($_POST['ccs_breadth'] > 0 ? $_POST['ccs_breadth'] : 0),
            "ccs_height" => ($_POST['ccs_height'] > 0 ? $_POST['ccs_height'] : 0),
            "ccs_weight" => ($_POST['ccs_weight'] > 0 ? $_POST['ccs_weight'] : 0),
            "ccs_volume" => ($_POST['ccs_volume'] > 0 ? $_POST['ccs_volume'] : 0),
            "rsb_package_type_id" => $_POST['ccsb_package_type_id'],
            "rsb_package_type_name" => $_POST['ccsb_package_type_name'],
            "rs_length" => ($_POST['rs_length'] > 0 ? $_POST['rs_length'] : 0),
            "rs_breadth" => ($_POST['rs_breadth'] > 0 ? $_POST['rs_breadth'] : 0),
            "rs_height" => ($_POST['rs_height'] > 0 ? $_POST['rs_height'] : 0),
            "rs_weight" => ($_POST['rs_weight'] > 0 ? $_POST['rs_weight'] : 0),
            "rs_volume" => ($_POST['rs_volume'] > 0 ? $_POST['rs_volume'] : 0),
            "csb_package_type_id" => $_POST['csb_package_type_id'],
            "csb_package_type_name" => $_POST['csb_package_type_name'],
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
            "stii_csbretail" => $_POST['stii_csbretail'],
            "least_package_type_id" => $_POST['least_package_type_id'],
            "least_package_type_name" => $_POST['least_package_type_name'],
            "isMedicine" => 1,
            "stit_fixedB2BRates" => $_POST['stit_fixedB2BRates']
        );



        $fsuidata['fsi_item_id'] = $data['stit_itemId'];
        $fsuidata['fsi_item_name'] = $data['stit_itemName'];
        $fsuidata['fsi_brand_id'] = $data['pdt_brand'];
        $fsuidata['fsi_brand_name'] = $data['stit_brand_name'];
        $fsuidata['fsi_category_id'] = $data['product_category'];
        $fsuidata['fsi_categry_name'] = $data['stit_category_name'];
        $fsuidata['fsi_variant'] = $data['stit_product_variant'];
        $fsuidata['fsi_displaylabel'] = $data['stit_displaylabel'];
        $fsuidata['isMedicine'] = 1;

//        print_r($data);
//        exit();
        $itemName = $_POST['item'];

        if ($_POST['dupitem'] == 'D') {
            unset($_POST['id']);
        }
        //print_r($data);exit();
        $db->query('begin');
        if (empty($_POST['id'])) {

            $stit_fsiuid = updateUniqueItemTable(0, $fsuidata);
            $data['stit_fsiuid'] = $stit_fsiuid['fsi_uid'];

            $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  WHERE stit_itemId = '{$itemName}' AND  stit_product_variant = '{$_POST['stit_product_variant']}' "
                    . "AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$data['product_category']} AND pdt_brand = {$data['pdt_brand']} AND isMedicine = 1");
            if ($IsItemNameUnique > 0) {
                echo "{success: false, msg:'This Item already existing.'}";
                exit;
            }
            $SKUUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  WHERE stit_SKU = '{$dSKU}'  ");
            if ($SKUUnique > 0) {
                echo "{success: false, msg:'This SKU already existing.'}";
                exit;
            }
            $data['isEdited'] = 1;
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
            $IsItemNameUnique = $db->getItemFromDB("SELECT COUNT(*) from " . FINASCOP_DB . "finascop_stock_itemmaster  WHERE stit_itemId = '{$itemName}' AND stit_ID <> {$_POST['id']} AND isMedicine = 1 "
                    . "AND  stit_product_variant = '{$_POST['stit_product_variant']}' AND stit_quantity = '{$_POST['stit_quantity']}' AND product_category = {$data['product_category']} AND pdt_brand = {$data['pdt_brand']}");
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
            $data['isEdited'] = 2;
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
            $packingdata['ccs_package_type_name'] = $db->getItemFromDB("SELECT medicine_type_name FROM mypha_medicineType WHERE medicine_type_id = {$cosDet['stdpckl12_package_type_id']}");
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
            $packingdata['rs_package_type_name'] = $db->getItemFromDB("SELECT medicine_type_name FROM mypha_medicineType WHERE medicine_type_id = {$cosDet['stdpckl12_package_type_id']}");
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
            $packingdata['cs_package_type_name'] = $db->getItemFromDB("SELECT medicine_type_name FROM mypha_medicineType WHERE medicine_type_id = {$cosDet['stdpckl12_package_type_id']}");
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
            $packingdata['ds_package_type_name'] = $db->getItemFromDB("SELECT medicine_type_name FROM mypha_medicineType WHERE medicine_type_id = {$cosDet['stdpckl12_package_type_id']}");
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
    case 'getItemMaster_EditData':
        $id = $_POST['id'];
        $qry = "select stit_ID as itemId,stit_itemId as medicineMasters,medcompos_name as med_generic_name,dosform_name as med_dosage_form ,stit_HSNCode as HSN,stit_GST as GST,stit_product_variant,stit_displaylabel,product_category,pdt_brand,featured,popular,prescription,"
                . "cos_nos,cos_package_type_id,cos_package_type_name,cosb_package_type_id,cosb_package_type_name,cos_length,cos_length,cos_breadth,cos_height,cos_weight,stit_itemReturnTime,stit_custInitiate,stit_fixedB2BRates,"
                . "ccs_nos,ccs_package_type_id,ccs_package_type_name,ccsb_package_type_id,ccsb_package_type_name,ccs_length,ccs_breadth,ccs_height,ccs_weight,"
                . "rs_nos,rs_package_type_id,rs_package_type_name,rsb_package_type_id,rsb_package_type_name,rs_length,rs_breadth,rs_height,rs_weight,"
                . "cs_nos,cs_package_type_id,cs_package_type_name,csb_package_type_id,csb_package_type_name,cs_length,cs_breadth,cs_height,cs_weight,"
                . "ds_nos,ds_package_type_id,ds_package_type_name,dsb_package_type_id,dsb_package_type_name,ds_length,ds_breadth,ds_height,ds_weight,"
                . " stit_Description as description,stit_long_description,stit_quantity,stit_HSN_code,stit_displaylabel as stit_displaylabels,stit_brand_name,stit_category_name,stit_itemName,stitl1_optimumqty,stitl2_optimumqty,stitl3_optimumqty,"
                . "stit11_minimumqty,stit12_minimumqty,stit13_minimumqty,stit11_maximumqty,stit12_maximumqty,stit13_maximumqty,stii_csb,stii_csbretail,isMedicine,med_drug_groupname,least_package_type_id,least_package_type_name,"
                . "stit_stdPacking,stdpckl11_package_type_id,stdpckl1_nos,stdpckl12_package_type_id,stdpckl21_package_type_id,stdpckl2_nos,stdpckl22_package_type_id,stdpckl31_package_type_id,
stdpckl3_nos,stdpckl32_package_type_id,stdpckl41_package_type_id,stdpckl4_nos,stdpckl42_package_type_id,stit_salesUnit from " . FINASCOP_DB . "finascop_stock_itemmaster  where stit_ID = '$id' ";
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getPTStore':
        $packs = array();
        $search_hint = $_POST['query'];
        //print_r($_POST);
        if (!in_array($_POST['stdpckl11'], $packs)) {
            $packs[] = $_POST['stdpckl11'];
        }
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
    case 'verifyDrugProdct':
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
}
