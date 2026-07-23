<?php

switch ($op) {
    case 'getSPFDetailStore':
        $type = $_POST['type'];
        switch ($type) {
            case '1':
                $qry = $db->getMulipleData("SELECT stit_ID AS spf_detailId,stit_SKU AS spf_detailName FROM finascop_stock_itemmaster WHERE stit_status = 1 order by stit_SKU asc", true);
                break;
            case '2':
                $qry = $db->getMulipleData("SELECT brand_id AS spf_detailId,brand_name AS spf_detailName FROM mypha_productbrands WHERE status = 1 order by brand_name asc", true);
                break;
            case '3':
                $qry = $db->getMulipleData("SELECT itemname_id AS spf_detailId,item_name AS spf_detailName FROM finascop_stock_itemmastername WHERE status = 1 order by item_name asc", true);
                break;
            case '4':
                $qry = $db->getMulipleData("SELECT sub_category_id AS spf_detailId,sub_category AS spf_detailName FROM mypha_productsubcategory WHERE status = 1 order by sub_category asc", true);
                break;
        }


        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'saveSPFData':
        $data['spf_type'] = $_POST['spf_type'];
        $data['spf_detail'] = $_POST['spf_detail'];
        $data['spf_factor'] = $_POST['spf_factor'];



        $spfUnique = $db->getItemSafe("SELECT COUNT(*) from selling_price_factor WHERE spf_type ='?' AND spf_detail = {$_POST['spf_detail']} ", "s", [$_POST['spf_type']]);
        $db->query('begin');
        if ($spfUnique == 0) {
            $data['spf_createdOn'] = date('Y-m-d H:i:s');
            $data['spf_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('selling_price_factor', $data);
            $lastId = $db->insert_id();
        } else {
            $data['spf_updatedOn'] = date('Y-m-d H:i:s');
            $data['spf_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('selling_price_factor', $data, 'update', " spf_type ='{$_POST['spf_type']}' AND spf_detail = {$_POST['spf_detail']}");
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listSPFFactor':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'spf_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        $subCategory_name = '';
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');

                    if ($field['field'] == 'spf_typeName') {
                        $values = $field['data']['value'];
                        $valuesArr = explode(',', $values);
                        $arrCount = count($valuesArr);
                        for ($j = 0; $j < $arrCount; $j++) {
                            if ($j == 0) {
                                $comma = '';
                            } else {
                                $comma = ',';
                            }
                            switch ($valuesArr[$j]) {
                                case 'SKU':
                                    $typeId = 1;
                                    break;
                                case 'Brand':
                                    $typeId = 2;
                                    break;
                                case 'ItemMaster':
                                    $typeId = 3;
                                    break;
                                case 'Subcategory':
                                    $typeId = 4;
                                    break;
                            }
                        }
                        $search .= " and (spf_type IN({$typeId})) ";
                    } else if ($field['field'] == 'spf_detailName') {
                        $spf_detailName = $field['data']['value'];
                    }
                }
            }
        }
        if ($typeId > 0) {
            switch ($typeId) {
                case 1:
                    if (!empty($spf_detailName)) {
                        $detailIds = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_ID) FROM finascop_stock_itemmaster WHERE stit_SKU like '{$spf_detailName}%'");
                        $cond = " AND spf_detail IN ({$detailIds}) ";
                    }
                    break;
                case 2:
                    if (!empty($spf_detailName)) {
                        $detailIds = $db->getItemFromDB("SELECT GROUP_CONCAT(brand_id) FROM mypha_productbrands WHERE brand_name like '{$spf_detailName}%'");
                        $cond = " AND spf_detail IN ({$detailIds}) ";
                    }
                    break;
                case 3:
                    if (!empty($spf_detailName)) {
                        $detailIds = $db->getItemFromDB("SELECT GROUP_CONCAT(itemname_id) FROM finascop_stock_itemmastername WHERE item_name like '{$spf_detailName}%'");
                        $cond = " AND spf_detail IN ({$detailIds}) ";
                    }
                    break;
                case 4:
                    if (!empty($spf_detailName)) {
                        $detailIds = $db->getItemFromDB("SELECT GROUP_CONCAT(sub_category_id) FROM mypha_productsubcategory WHERE sub_category like '{$spf_detailName}%'");
                        $cond = " AND spf_detail IN ({$detailIds}) ";
                    }
                    break;
            }
        }

        $countQuery = "SELECT COUNT(*) FROM selling_price_factor {$search} {$cond} ";
        $count = $db->getItemFromDB($countQuery);

        $listQuery = "SELECT spf_id,spf_type,spf_detail,CONCAT(spf_factor,' %') AS spf_factor  FROM selling_price_factor {$search} {$cond} ORDER BY {$sort} {$dir} limit $start,$limit";
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                switch ($datas[$i]['spf_type']) {
                    case '1':

                        $datas[$i]['spf_typeName'] = 'SKU';
                        $datas[$i]['spf_detailName'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['spf_detail']} ");
                        break;
                    case '2':

                        $datas[$i]['spf_typeName'] = 'Brand';
                        $datas[$i]['spf_detailName'] = $db->getItemFromDB("SELECT brand_name FROM mypha_productbrands WHERE brand_id = {$datas[$i]['spf_detail']} ");
                        break;
                    case '3':

                        $datas[$i]['spf_typeName'] = 'ItemMaster';
                        $datas[$i]['spf_detailName'] = $db->getItemFromDB("SELECT item_name FROM finascop_stock_itemmastername WHERE itemname_id = {$datas[$i]['spf_detail']} ");
                        break;
                    case '4':

                        $datas[$i]['spf_typeName'] = 'Subcategory';
                        $datas[$i]['spf_detailName'] = $db->getItemFromDB("SELECT sub_category FROM mypha_productsubcategory WHERE sub_category_id = {$datas[$i]['spf_detail']} ");
                        break;
                }
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else {
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        }
        break;
    case 'deleteSPFFactor':
        $id = $_POST['spf_id'];
        $del_query = "DELETE FROM selling_price_factor WHERE spf_id =" . $id;
        $status = $db->query($del_query);

        $status = $db->query('commit');
        if ($status) {

            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'checkSPFData':
        $data['spf_type'] = $_POST['spf_type'];
        $data['spf_detail'] = $_POST['spf_detail'];
        $data['spf_factor'] = $_POST['spf_factor'];
        $spf_factorFlag = 0;

        switch ($_POST['spf_type']) {
            case 1:
                $spf_factorSKU = $db->getItemFromDB("SELECT spf_factor FROM selling_price_factor WHERE spf_type = 1 AND spf_detail = {$data['spf_detail']}");
                $itemDetails = $db->getFromDB("SELECT pdt_brand,stit_itemId,product_category FROM finascop_stock_itemmaster WHERE stit_ID = {$data['spf_detail']} ", true);
                if ($spf_factorSKU > 1) {
                    $spf_factorFlag = 1;
                    $spfFactor .= "Already have factor for SKU - " . $spf_factorSKU;
                } else {
                    $spf_factorBrand = $db->getItemFromDB("SELECT spf_factor FROM selling_price_factor WHERE spf_type = 2 AND spf_detail = {$itemDetails['pdt_brand']}");
                    if ($spf_factorBrand > 1) {
                        $spf_factorFlag = 1;
                        $spfFactor .= "Already have factor for SKU Brand - " . $spf_factorBrand;
                    } else {
                        $spf_factorItem = $db->getItemFromDB("SELECT spf_factor FROM selling_price_factor WHERE spf_type = 3 AND spf_detail = {$itemDetails['stit_itemId']}");
                        if ($spf_factorItem > 1) {
                            $spf_factorFlag = 1;
                            $spfFactor .= "Already have factor for SKU Item - " . $spf_factorItem;
                        } else {
                            $spf_factorSC = $db->getItemFromDB("SELECT spf_factor FROM selling_price_factor WHERE spf_type = 4 AND spf_detail = {$itemDetails['product_category']}");
                            if ($spf_factorSC > 1) {
                                $spf_factorFlag = 1;
                                $spfFactor .= "Already have factor for SKU Subcategory - " . $spf_factorSC;
                            }
                        }
                    }
                }
                break;
            case 2:
                $spf_factorBrand = $db->getItemFromDB("SELECT spf_factor FROM selling_price_factor WHERE spf_type = 2 AND spf_detail = {$data['spf_detail']}");
                if ($spf_factorBrand > 1) {
                    $spf_factorFlag = 1;
                    $spfFactor .= "Already have factor for this Brand - " . $spf_factorBrand;
                }
                break;
            case 3:
                $spf_factorItem = $db->getItemFromDB("SELECT spf_factor FROM selling_price_factor WHERE spf_type = 3 AND spf_detail = {$data['spf_detail']}");
                if ($spf_factorItem > 1) {
                    $spf_factorFlag = 1;
                    $spfFactor .= "Already have factor for this Item - " . $spf_factorItem;
                }
                break;
            case 4:
                $spf_factorSC = $db->getItemFromDB("SELECT spf_factor FROM selling_price_factor WHERE spf_type = 4 AND spf_detail = {$data['spf_detail']}");
                if ($spf_factorSC > 1) {
                    $spf_factorFlag = 1;
                    $spfFactor .= "Already have factor for this Subcategory - " . $spf_factorSC;
                }
                break;
        }

        if ($spf_factorFlag == 1) {
            echo "{success:true,valid:false,message:'.$spfFactor.'}";
        } else {
            echo "{'success':true,'valid':true}";
        }
        break;


    case 'saveMinMarginData':
        $data['mm_type'] = $_POST['mm_type'];
        $data['mm_detail'] = $_POST['mm_detail'];
        $data['mm_factor'] = $_POST['mm_factor'];



        $spfUnique = $db->getItemSafe("SELECT COUNT(*) from minimum_margin_range WHERE mm_type ='?' AND mm_detail = {$_POST['mm_detail']} ", "s", [$_POST['mm_type']]);
        $db->query('begin');
        if ($spfUnique == 0) {
            $data['mm_createdOn'] = date('Y-m-d H:i:s');
            $data['mm_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('minimum_margin_range', $data);
            $lastId = $db->insert_id();
        } else {
            $data['mm_updatedOn'] = date('Y-m-d H:i:s');
            $data['mm_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('minimum_margin_range', $data, 'update', " mm_type ='{$_POST['mm_type']}' AND mm_detail = {$_POST['mm_detail']}");
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listMinMarginFactor':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'mm_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        $subCategory_name = '';
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');

                    if ($field['field'] == 'mm_typeName') {
                        $values = $field['data']['value'];
                        $valuesArr = explode(',', $values);
                        $arrCount = count($valuesArr);
                        for ($j = 0; $j < $arrCount; $j++) {
                            if ($j == 0) {
                                $comma = '';
                            } else {
                                $comma = ',';
                            }
                            switch ($valuesArr[$j]) {
                                case 'SKU':
                                    $typeId = 1;
                                    break;
                                case 'Brand':
                                    $typeId = 2;
                                    break;
                                case 'ItemMaster':
                                    $typeId = 3;
                                    break;
                                case 'Subcategory':
                                    $typeId = 4;
                                    break;
                            }
                        }
                        $search .= " and (mm_type IN({$typeId})) ";
                    } else if ($field['field'] == 'mm_detailName') {
                        $mm_detailName = $field['data']['value'];
                    }
                }
            }
        }
        if ($typeId > 0) {
            switch ($typeId) {
                case 1:
                    if (!empty($mm_detailName)) {
                        $detailIds = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_ID) FROM finascop_stock_itemmaster WHERE stit_SKU like '{$mm_detailName}%'");
                        $cond = " AND mm_detail IN ({$detailIds}) ";
                    }
                    break;
                case 2:
                    if (!empty($mm_detailName)) {
                        $detailIds = $db->getItemFromDB("SELECT GROUP_CONCAT(brand_id) FROM mypha_productbrands WHERE brand_name like '{$mm_detailName}%'");
                        $cond = " AND mm_detail IN ({$detailIds}) ";
                    }
                    break;
                case 3:
                    if (!empty($mm_detailName)) {
                        $detailIds = $db->getItemFromDB("SELECT GROUP_CONCAT(itemname_id) FROM finascop_stock_itemmastername WHERE item_name like '{$mm_detailName}%'");
                        $cond = " AND mm_detail IN ({$detailIds}) ";
                    }
                    break;
                case 4:
                    if (!empty($mm_detailName)) {
                        $detailIds = $db->getItemFromDB("SELECT GROUP_CONCAT(sub_category_id) FROM mypha_productsubcategory WHERE sub_category like '{$mm_detailName}%'");
                        $cond = " AND mm_detail IN ({$detailIds}) ";
                    }
                    break;
            }
        } else {
            if (!empty($mm_detailName)) {
                $skuIds = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_ID) FROM finascop_stock_itemmaster WHERE stit_SKU like '%{$mm_detailName}%'");
                $brandIds = $db->getItemFromDB("SELECT GROUP_CONCAT(brand_id) FROM mypha_productbrands WHERE brand_name like '%{$mm_detailName}%'");
                $imIds = $db->getItemFromDB("SELECT GROUP_CONCAT(itemname_id) FROM finascop_stock_itemmastername WHERE item_name like '%{$mm_detailName}%'");
                $scIds = $db->getItemFromDB("SELECT GROUP_CONCAT(sub_category_id) FROM mypha_productsubcategory WHERE sub_category like '%{$mm_detailName}%'");
                if (!empty($skuIds)) {
                    $skuRelatedIds = $db->getItemFromDB("SELECT GROUP_CONCAT(mm_id) FROM minimum_margin_range WHERE mm_type = 1 AND mm_detail IN ({$skuIds})");
                    if (!empty($skuRelatedIds))
                        $mmIds .= $skuRelatedIds . ',';
                }
                if (!empty($brandIds)) {
                    $brandRelatedIds = $db->getItemFromDB("SELECT GROUP_CONCAT(mm_id) FROM minimum_margin_range WHERE mm_type = 2 AND mm_detail IN ({$brandIds})");
                    if (!empty($brandRelatedIds))
                        $mmIds .= $brandRelatedIds . ',';
                }
                if (!empty($imIds)) {
                    $imRelatedIds = $db->getItemFromDB("SELECT GROUP_CONCAT(mm_id) FROM minimum_margin_range WHERE mm_type = 3 AND mm_detail IN ({$imIds})");
                    if (!empty($imRelatedIds))
                        $mmIds .= $imRelatedIds . ',';
                }
                if (!empty($scIds)) {
                    $scRelatedIds = $db->getItemFromDB("SELECT GROUP_CONCAT(mm_id) FROM minimum_margin_range WHERE mm_type = 4 AND mm_detail IN ({$scIds})");
                    if (!empty($scRelatedIds))
                        $mmIds .= $scRelatedIds;
                }
                $mmIds = rtrim($mmIds, ',');
                if (!empty($mmIds))
                    $cond .= " AND mm_id IN ({$mmIds}) ";
                else
                    $cond .= " AND mm_id IN (0) ";
            }
        }
        $countQuery = "SELECT COUNT(*) FROM minimum_margin_range {$search} {$cond} ";
        $count = $db->getItemFromDB($countQuery);

        $listQuery = "SELECT mm_id,mm_type,mm_detail,CONCAT(mm_factor,' %') AS mm_factor  FROM minimum_margin_range {$search} {$cond}  ORDER BY {$sort} {$dir} limit $start,$limit";
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                switch ($datas[$i]['mm_type']) {
                    case '1':

                        $datas[$i]['mm_typeName'] = 'SKU';
                        $datas[$i]['mm_detailName'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['mm_detail']} ");
                        break;
                    case '2':

                        $datas[$i]['mm_typeName'] = 'Brand';
                        $datas[$i]['mm_detailName'] = $db->getItemFromDB("SELECT brand_name FROM mypha_productbrands WHERE brand_id = {$datas[$i]['mm_detail']} ");
                        break;
                    case '3':

                        $datas[$i]['mm_typeName'] = 'ItemMaster';
                        $datas[$i]['mm_detailName'] = $db->getItemFromDB("SELECT item_name FROM finascop_stock_itemmastername WHERE itemname_id = {$datas[$i]['mm_detail']} ");
                        break;
                    case '4':

                        $datas[$i]['mm_typeName'] = 'Subcategory';
                        $datas[$i]['mm_detailName'] = $db->getItemFromDB("SELECT sub_category FROM mypha_productsubcategory WHERE sub_category_id = {$datas[$i]['mm_detail']} ");
                        break;
                }
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else {
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        }
        break;
    case 'deleteMinMarginFactor':
        $id = $_POST['mm_id'];
        $del_query = "DELETE FROM minimum_margin_range WHERE mm_id =" . $id;
        $status = $db->query($del_query);

        $status = $db->query('commit');
        if ($status) {

            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'checkMinMarginData':
        $data['mm_type'] = $_POST['mm_type'];
        $data['mm_detail'] = $_POST['mm_detail'];
        $data['mm_factor'] = $_POST['mm_factor'];
        $mm_factorFlag = 0;

        switch ($_POST['mm_type']) {
            case 1:
                $mm_factorSKU = $db->getItemFromDB("SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 1 AND mm_detail = {$data['mm_detail']}");
                $itemDetails = $db->getFromDB("SELECT pdt_brand,stit_itemId,product_category FROM finascop_stock_itemmaster WHERE stit_ID = {$data['mm_detail']} ", true);
                if ($mm_factorSKU > 1) {
                    $mm_factorFlag = 1;
                    $spfFactor .= "Already have factor for SKU - " . $mm_factorSKU;
                } else {
                    $mm_factorBrand = $db->getItemFromDB("SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 2 AND mm_detail = {$itemDetails['pdt_brand']}");
                    if ($mm_factorBrand > 1) {
                        $mm_factorFlag = 1;
                        $spfFactor .= "Already have factor for SKU Brand - " . $mm_factorBrand;
                    } else {
                        $mm_factorItem = $db->getItemFromDB("SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 3 AND mm_detail = {$itemDetails['stit_itemId']}");
                        if ($mm_factorItem > 1) {
                            $mm_factorFlag = 1;
                            $spfFactor .= "Already have factor for SKU Item - " . $mm_factorItem;
                        } else {
                            $mm_factorSC = $db->getItemFromDB("SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 4 AND mm_detail = {$itemDetails['product_category']}");
                            if ($mm_factorSC > 1) {
                                $mm_factorFlag = 1;
                                $spfFactor .= "Already have factor for SKU Subcategory - " . $mm_factorSC;
                            }
                        }
                    }
                }
                break;
            case 2:
                $mm_factorBrand = $db->getItemFromDB("SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 2 AND mm_detail = {$data['mm_detail']}");
                if ($mm_factorBrand > 1) {
                    $mm_factorFlag = 1;
                    $spfFactor .= "Already have factor for this Brand - " . $mm_factorBrand;
                }
                break;
            case 3:
                $mm_factorItem = $db->getItemFromDB("SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 3 AND mm_detail = {$data['mm_detail']}");
                if ($mm_factorItem > 1) {
                    $mm_factorFlag = 1;
                    $spfFactor .= "Already have factor for this Item - " . $mm_factorItem;
                }
                break;
            case 4:
                $mm_factorSC = $db->getItemFromDB("SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 4 AND mm_detail = {$data['mm_detail']}");
                if ($mm_factorSC > 1) {
                    $mm_factorFlag = 1;
                    $spfFactor .= "Already have factor for this Subcategory - " . $mm_factorSC;
                }
                break;
        }

        if ($mm_factorFlag == 1) {
            echo "{success:true,valid:false,message:'.$spfFactor.'}";
        } else {
            echo "{'success':true,'valid':true}";
        }
        break;
    case 'getSellerPlatfChargeDetailStore':

        $limit = isset($_POST['limit']) ? $_POST['limit'] : 25;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $allowedFields = ['item_name', 'item_code', 'sp_price', 'sp_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        }
        switch ($_POST['type']) {
            case '1':
                $qry = "SELECT stit_ID AS spc_detailId,stit_SKU AS spc_detailName FROM finascop_stock_itemmaster WHERE stit_status = 1 order by stit_SKU asc";
                break;
            case '2':
                $qry = "SELECT brand_id AS spc_detailId,brand_name AS spc_detailName FROM mypha_productbrands WHERE status = 1 order by brand_name asc";
                break;
            case '3':
                $qry = "SELECT sub_category_id AS spc_detailId,sub_category AS spc_detailName FROM mypha_productsubcategory WHERE status = 1 order by sub_category asc";
                break;
            case '4':
                $qry = "SELECT category_id AS spc_detailId,category_name AS spc_detailName FROM mypha_productcategory WHERE status = 1 order by category_name asc";
                break;
            case '5':
                $qry = "SELECT parent_category_id AS spc_detailId,parent_category AS spc_detailName FROM mypha_productparent_category WHERE status = 1 order by parent_category asc";
                break;
            case '6':
                $qry = "SELECT business_type_id AS spc_detailId,business_type_name AS spc_detailName FROM finascop_business_type WHERE status = 1 order by business_type_name asc";
                break;
            case '7':
                $qry = "SELECT br_ID AS spc_detailId,br_Name AS spc_detailName FROM finascop_branch WHERE br_status = 'Active ORDER BY br_Name asc";
                break;
            case '8':
                $qry = "SELECT store_group_id AS spc_detailId,store_group_name AS spc_detailName FROM finascop_branch_group WHERE STATUS = 1 ORDER BY store_group_name asc";
                break;
        }
        $countQuery = "SELECT COUNT(*) FROM ({$qry}) AS countDet {$search}  ";
        $count = $db->getItemFromDB($countQuery);

        $listQuery = "SELECT * FROM ({$qry}) AS listDet {$search}  limit $start,$limit";
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else {
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        }
        break;
    case 'oldgetSellerPlatfChargeDetailStore':
        $type = $_POST['type'];
        switch ($type) {
            case '1':
                $qry = $db->getMulipleData("SELECT stit_ID AS spc_detailId,stit_SKU AS spc_detailName FROM finascop_stock_itemmaster WHERE stit_status = 1 order by stit_SKU asc", true);
                break;
            case '2':
                $qry = $db->getMulipleData("SELECT brand_id AS spc_detailId,brand_name AS spc_detailName FROM mypha_productbrands WHERE status = 1 order by brand_name asc", true);
                break;
            /*case '3':
                $qry = $db->getMulipleData("SELECT itemname_id AS spc_detailId,item_name AS spc_detailName FROM finascop_stock_itemmastername WHERE status = 1 order by item_name asc", true);
                break;*/
            case '3':
                $qry = $db->getMulipleData("SELECT sub_category_id AS spc_detailId,sub_category AS spc_detailName FROM mypha_productsubcategory WHERE status = 1 order by sub_category asc", true);
                break;
            case '4':
                $qry = $db->getMulipleData("SELECT category_id AS spc_detailId,category_name AS spc_detailName FROM mypha_productcategory WHERE status = 1 order by category_name asc", true);
                break;
            case '5':
                $qry = $db->getMulipleData("SELECT parent_category_id AS spc_detailId,parent_category AS spc_detailName FROM mypha_productparent_category WHERE status = 1 order by parent_category asc", true);
                break;
            case '6':
                $qry = $db->getMulipleData("SELECT business_type_id AS spc_detailId,business_type_name AS spc_detailName FROM finascop_business_type WHERE status = 1 order by business_type_name asc", true);
                break;
        }


        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'listSellerPlatCharge':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        $subCategory_name = '';
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');

                    if ($field['field'] == 'spc_typeName') {
                        $values = $field['data']['value'];
                        $valuesArr = explode(',', $values);
                        $arrCount = count($valuesArr);
                        for ($j = 0; $j < $arrCount; $j++) {
                            if ($j == 0) {
                                $comma = '';
                            } else {
                                $comma = ',';
                            }
                            switch ($valuesArr[$j]) {
                                case 'Product':
                                    $typeId = 1;
                                    break;
                                case 'Brand':
                                    $typeId = 2;
                                    break;
                                /*case 'ItemMaster':
                                    $typeId = 3;
                                    break;*/
                                case 'Subcategory':
                                    $typeId = 3;
                                    break;
                                case 'Category':
                                    $typeId = 4;
                                    break;
                                case 'Department':
                                    $typeId = 5;
                                    break;
                                case 'Retail Category':
                                    $typeId = 6;
                                    break;
                                case 'Branch':
                                    $typeId = 7;
                                    break;
                                case 'Store Group':
                                    $typeId = 8;
                                    break;
                            }
                        }
                        $search .= " and (type IN({$typeId})) ";
                    } else if ($field['field'] == 'spc_detailName') {
                        $spf_detailName = $field['data']['value'];
                    }
                }
            }
        }
        if ($typeId > 0) {
            switch ($typeId) {
                case 1:
                    if (!empty($spf_detailName)) {
                        $detailIds = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_ID) FROM finascop_stock_itemmaster WHERE stit_SKU like '{$spf_detailName}%'");
                        $cond = " AND detailId IN ({$detailIds}) ";
                    }
                    break;
                case 2:
                    if (!empty($spf_detailName)) {
                        $detailIds = $db->getItemFromDB("SELECT GROUP_CONCAT(brand_id) FROM mypha_productbrands WHERE brand_name like '{$spf_detailName}%'");
                        $cond = " AND detailId IN ({$detailIds}) ";
                    }
                    break;
                /*case 3:
                    if (!empty($spf_detailName)) {
                        $detailIds = $db->getItemFromDB("SELECT GROUP_CONCAT(itemname_id) FROM finascop_stock_itemmastername WHERE item_name like '{$spf_detailName}%'");
                        $cond = " AND detailId IN ({$detailIds}) ";
                    }
                    break;*/
                case 3:
                    if (!empty($spf_detailName)) {
                        $detailIds = $db->getItemFromDB("SELECT GROUP_CONCAT(sub_category_id) FROM mypha_productsubcategory WHERE sub_category like '{$spf_detailName}%'");
                        $cond = " AND detailId IN ({$detailIds}) ";
                    }
                    break;
                case 4:
                    if (!empty($spf_detailName)) {
                        $detailIds = $db->getItemFromDB("SELECT GROUP_CONCAT(category_id) FROM mypha_productcategory WHERE category_name like '{$spf_detailName}%'");
                        $cond = " AND detailId IN ({$detailIds}) ";
                    }
                    break;
                case 5:
                    if (!empty($spf_detailName)) {
                        $detailIds = $db->getItemFromDB("SELECT GROUP_CONCAT(parent_category_id) FROM mypha_productparent_category WHERE parent_category like '{$spf_detailName}%'");
                        $cond = " AND detailId IN ({$detailIds}) ";
                    }
                    break;
                case 6:
                    if (!empty($spf_detailName)) {
                        $detailIds = $db->getItemFromDB("SELECT GROUP_CONCAT(business_type_id) FROM finascop_business_type WHERE business_type_name like '{$spf_detailName}%'");
                        $cond = " AND detailId IN ({$detailIds}) ";
                    }
                    break;
                case 7:
                    if (!empty($spf_detailName)) {
                        $detailIds = $db->getItemFromDB("SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_Name like '{$spf_detailName}%'");
                        $cond = " AND detailId IN ({$detailIds}) ";
                    }
                    break;
                case 8:
                    if (!empty($spf_detailName)) {
                        $detailIds = $db->getItemFromDB("SELECT GROUP_CONCAT(store_group_id) FROM finascop_branch_group WHERE store_group_name like '{$spf_detailName}%'");
                        $cond = " AND detailId IN ({$detailIds}) ";
                    }
                    break;
            }
        } else {
            if (!empty($spf_detailName)) {
                $skuIds = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_ID) FROM finascop_stock_itemmaster WHERE stit_SKU like '%{$spf_detailName}%'");
                $brandIds = $db->getItemFromDB("SELECT GROUP_CONCAT(brand_id) FROM mypha_productbrands WHERE brand_name like '%{$spf_detailName}%'");
                $imIds = $db->getItemFromDB("SELECT GROUP_CONCAT(itemname_id) FROM finascop_stock_itemmastername WHERE item_name like '%{$spf_detailName}%'");
                $scIds = $db->getItemFromDB("SELECT GROUP_CONCAT(sub_category_id) FROM mypha_productsubcategory WHERE sub_category like '%{$spf_detailName}%'");
                $catIds = $db->getItemFromDB("SELECT GROUP_CONCAT(category_id) FROM mypha_productcategory WHERE category_name like '%{$spf_detailName}%'");
                $deptIds = $db->getItemFromDB("SELECT GROUP_CONCAT(parent_category_id) FROM mypha_productparent_category WHERE parent_category like '%{$spf_detailName}%'");
                $retcatIds = $db->getItemFromDB("SELECT GROUP_CONCAT(business_type_id) FROM finascop_business_type WHERE business_type_name like '%{$spf_detailName}%'");
                $branchIds = $db->getItemFromDB("SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_Name like '%{$spf_detailName}%'");
                $stgrpIds = $db->getItemFromDB("SELECT GROUP_CONCAT(store_group_id) FROM finascop_branch_group WHERE store_group_name like '%{$spf_detailName}%'");
                if (!empty($skuIds)) {
                    $skuRelatedIds = $db->getItemFromDB("SELECT GROUP_CONCAT(id) FROM additional_charge WHERE appliedOn = 1 AND detailId IN ({$skuIds})");
                    if (!empty($skuRelatedIds))
                        $mmIds .= $skuRelatedIds . ',';
                }
                if (!empty($brandIds)) {
                    $brandRelatedIds = $db->getItemFromDB("SELECT GROUP_CONCAT(id) FROM additional_charge WHERE appliedOn = 2 AND detailId IN ({$brandIds})");
                    if (!empty($brandRelatedIds))
                        $mmIds .= $brandRelatedIds . ',';
                }
                if (!empty($imIds)) {
                    $imRelatedIds = $db->getItemFromDB("SELECT GROUP_CONCAT(id) FROM additional_charge WHERE appliedOn = 3 AND detailId IN ({$imIds})");
                    if (!empty($imRelatedIds))
                        $mmIds .= $imRelatedIds . ',';
                }
                if (!empty($scIds)) {
                    $scRelatedIds = $db->getItemFromDB("SELECT GROUP_CONCAT(id) FROM additional_charge WHERE appliedOn = 3 AND detailId IN ({$scIds})");
                    if (!empty($scRelatedIds))
                        $mmIds .= $scRelatedIds;
                }
                if (!empty($catIds)) {
                    $catRelatedIds = $db->getItemFromDB("SELECT GROUP_CONCAT(id) FROM additional_charge WHERE appliedOn = 4 AND detailId IN ({$catIds})");
                    if (!empty($catRelatedIds))
                        $mmIds .= $catRelatedIds;
                }
                if (!empty($deptIds)) {
                    $deptRelatedIds = $db->getItemFromDB("SELECT GROUP_CONCAT(id) FROM additional_charge WHERE appliedOn = 5 AND detailId IN ({$deptIds})");
                    if (!empty($deptRelatedIds))
                        $mmIds .= $deptRelatedIds;
                }
                if (!empty($retcatIds)) {
                    $retcatRelatedIds = $db->getItemFromDB("SELECT GROUP_CONCAT(id) FROM additional_charge WHERE appliedOn = 6 AND detailId IN ({$retcatIds})");
                    if (!empty($retcatRelatedIds))
                        $mmIds .= $retcatRelatedIds;
                }
                if (!empty($branchIds)) {
                    $brRelatedIds = $db->getItemFromDB("SELECT GROUP_CONCAT(id) FROM additional_charge WHERE appliedOn = 7 AND detailId IN ({$branchIds})");
                    if (!empty($brRelatedIds))
                        $mmIds .= $brRelatedIds;
                }
                if (!empty($stgrpIds)) {
                    $sgRelatedIds = $db->getItemFromDB("SELECT GROUP_CONCAT(id) FROM additional_charge WHERE appliedOn = 7 AND detailId IN ({$stgrpIds})");
                    if (!empty($sgRelatedIds))
                        $mmIds .= $sgRelatedIds;
                }
                $mmIds = rtrim($mmIds, ',');
                if (!empty($mmIds))
                    $cond .= " AND id IN ({$mmIds}) ";
                else
                    $cond .= " AND id IN (0) ";
            }
        }

        $countQuery = "SELECT COUNT(*) FROM additional_charge {$search} {$cond} ";
        $count = $db->getItemFromDB($countQuery);

        $listQuery = "SELECT id,appliedOn,detailId,charge  FROM additional_charge {$search} {$cond} ORDER BY {$sort} {$dir} limit $start,$limit";
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                switch ($datas[$i]['appliedOn']) {
                    case '1':

                        $datas[$i]['spc_typeName'] = 'Product';
                        $datas[$i]['spc_detailName'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['detailId']} ");
                        break;
                    case '2':

                        $datas[$i]['spc_typeName'] = 'Brand';
                        $datas[$i]['spc_detailName'] = $db->getItemFromDB("SELECT brand_name FROM mypha_productbrands WHERE brand_id = {$datas[$i]['detailId']} ");
                        break;
                    /*case '3':

                        $datas[$i]['spc_typeName'] = 'ItemMaster';
                        $datas[$i]['spc_detailName'] = $db->getItemFromDB("SELECT item_name FROM finascop_stock_itemmastername WHERE itemname_id = {$datas[$i]['detailId']} ");
                        break;*/
                    case '3':

                        $datas[$i]['spc_typeName'] = 'Subcategory';
                        $datas[$i]['spc_detailName'] = $db->getItemFromDB("SELECT sub_category FROM mypha_productsubcategory WHERE sub_category_id = {$datas[$i]['detailId']} ");
                        break;
                    case '4':

                        $datas[$i]['spc_typeName'] = 'Category';
                        $datas[$i]['spc_detailName'] = $db->getItemFromDB("SELECT category_name FROM mypha_productcategory WHERE category_id = {$datas[$i]['detailId']} ");
                        break;
                    case '5':

                        $datas[$i]['spc_typeName'] = 'Department';
                        $datas[$i]['spc_detailName'] = $db->getItemFromDB("SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = {$datas[$i]['detailId']} ");
                        break;
                    case '6':

                        $datas[$i]['spc_typeName'] = 'Retail Catgeory';
                        $datas[$i]['spc_detailName'] = $db->getItemFromDB("SELECT business_type_name FROM finascop_business_type WHERE business_type_id = {$datas[$i]['detailId']} ");
                        break;
                    case '7':

                        $datas[$i]['spc_typeName'] = 'Branch';
                        $datas[$i]['spc_detailName'] = $db->getItemFromDB("SELECT br_Name FROM finascop_branch WHERE br_ID = {$datas[$i]['detailId']} ");
                        break;
                    case '8':

                        $datas[$i]['spc_typeName'] = 'Store Group';
                        $datas[$i]['spc_detailName'] = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = {$datas[$i]['detailId']} ");
                        break;
                }
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else {
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        }
        break;
    case 'oldlistSellerPlatCharge':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'business_type_name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1  and finascop_business_type.status = 1 ";
        $filter = $_POST['filter'];
        $subCategory_name = '';
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');

                    if ($field['field'] == 'spc_typeName') {
                        $values = $field['data']['value'];
                        $valuesArr = explode(',', $values);
                        $arrCount = count($valuesArr);
                        for ($j = 0; $j < $arrCount; $j++) {
                            if ($j == 0) {
                                $comma = '';
                            } else {
                                $comma = ',';
                            }
                            switch ($valuesArr[$j]) {
                                case 'SKU':
                                    $typeId = 1;
                                    break;
                                case 'Brand':
                                    $typeId = 2;
                                    break;
                                case 'ItemMaster':
                                    $typeId = 3;
                                    break;
                                case 'Subcategory':
                                    $typeId = 4;
                                    break;
                            }
                        }
                        $search .= " and (type IN({$typeId})) ";
                    } else if ($field['field'] == 'spc_detailName') {
                        $spf_detailName = $field['data']['value'];
                    }
                }
            }
        }


        $countQuery = "SELECT COUNT(*) FROM finascop_business_type LEFT JOIN additional_charge ON detailId = business_type_id AND additional_charge.status = 1 {$search} {$cond} ";
        $count = $db->getItemFromDB($countQuery);

        $listQuery = "SELECT id,business_type_id,business_type_name,appliedOn,detailId,charge  FROM finascop_business_type LEFT JOIN additional_charge ON detailId = business_type_id AND additional_charge.status = 1 {$search} {$cond} ORDER BY {$sort} {$dir} limit $start,$limit";
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else {
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        }
        break;
    case 'deleteSellerPlatCharge':
        $id = $_POST['spc_id'];
        $del_query = "DELETE FROM additional_charge WHERE id =" . $id;
        $status = $db->query($del_query);

        $status = $db->query('commit');
        if ($status) {

            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'checkSPCData':
        $data['spc_type'] = $_POST['spc_type'];
        $data['spc_detail'] = $_POST['spc_detail'];
        $data['spc_factor'] = $_POST['spc_factor'];
        $spf_factorFlag = 0;

        switch ($_POST['spc_type']) {
            case 1:
                $spf_factorSKU = $db->getItemFromDB("SELECT charge FROM additional_charge WHERE appliedOn = 1 AND detailId = {$data['spc_detail']}");
                $itemDetails = $db->getFromDB("SELECT pdt_brand,stit_itemId,product_category FROM finascop_stock_itemmaster WHERE stit_ID = {$data['spc_detail']} ", true);
                if ($spf_factorSKU > 1) {
                    $spf_factorFlag = 1;
                    $spfFactor .= "Already have factor for SKU - " . $spf_factorSKU;
                } else {
                    $spf_factorBrand = $db->getItemFromDB("SELECT charge FROM additional_charge WHERE appliedOn = 2 AND detailId = {$itemDetails['pdt_brand']}");
                    if ($spf_factorBrand > 1) {
                        $spf_factorFlag = 1;
                        $spfFactor .= "Already have factor for SKU Brand - " . $spf_factorBrand;
                    } else {
                        $spf_factorItem = $db->getItemFromDB("SELECT charge FROM additional_charge WHERE appliedOn = 3 AND detailId = {$itemDetails['stit_itemId']}");
                        if ($spf_factorItem > 1) {
                            $spf_factorFlag = 1;
                            $spfFactor .= "Already have factor for SKU Item - " . $spf_factorItem;
                        } else {
                            $spf_factorSC = $db->getItemFromDB("SELECT charge FROM additional_charge WHERE appliedOn = 4 AND detailId = {$itemDetails['product_category']}");
                            if ($spf_factorSC > 1) {
                                $spf_factorFlag = 1;
                                $spfFactor .= "Already have factor for SKU Subcategory - " . $spf_factorSC;
                            }
                        }
                    }
                }
                break;
            case 2:
                $spf_factorBrand = $db->getItemFromDB("SELECT charge FROM additional_charge WHERE appliedOn = 2 AND detailId = {$data['spc_detail']}");
                if ($spf_factorBrand > 1) {
                    $spf_factorFlag = 1;
                    $spfFactor .= "Already have charge for this Brand - " . $spf_factorBrand;
                }
                break;
            case 3:
                $spf_factorItem = $db->getItemFromDB("SELECT charge FROM additional_charge WHERE appliedOn = 3 AND detailId = {$data['spc_detail']}");
                if ($spf_factorItem > 1) {
                    $spf_factorFlag = 1;
                    $spfFactor .= "Already have charge for this Subcategory - " . $spf_factorItem;
                }
                break;
            case 4:
                $spf_factorSC = $db->getItemFromDB("SELECT charge FROM additional_charge WHERE appliedOn = 4 AND detailId = {$data['spc_detail']}");
                if ($spf_factorSC > 1) {
                    $spf_factorFlag = 1;
                    $spfFactor .= "Already have charge for this Category  - " . $spf_factorSC;
                }
                break;
            case 5:
                $spf_factorSC = $db->getItemFromDB("SELECT charge FROM additional_charge WHERE appliedOn = 5 AND detailId = {$data['spc_detail']}");
                if ($spf_factorSC > 1) {
                    $spf_factorFlag = 1;
                    $spfFactor .= "Already have charge for this Department - " . $spf_factorSC;
                }
                break;
            case 6:
                $spf_factorSC = $db->getItemFromDB("SELECT charge FROM additional_charge WHERE appliedOn = 6 AND detailId = {$data['spc_detail']}");
                if ($spf_factorSC > 1) {
                    $spf_factorFlag = 1;
                    $spfFactor .= "Already have charge for this Retail Catgeory - " . $spf_factorSC;
                }
                break;
            case 7:
                $spf_factorSC = $db->getItemFromDB("SELECT charge FROM additional_charge WHERE appliedOn = 7 AND detailId = {$data['spc_detail']}");
                if ($spf_factorSC > 1) {
                    $spf_factorFlag = 1;
                    $spfFactor .= "Already have charge for this Branch - " . $spf_factorSC;
                }
                break;
            case 8:
                $spf_factorSC = $db->getItemFromDB("SELECT charge FROM additional_charge WHERE appliedOn = 8 AND detailId = {$data['spc_detail']}");
                if ($spf_factorSC > 1) {
                    $spf_factorFlag = 1;
                    $spfFactor .= "Already have charge for this Store Group - " . $spf_factorSC;
                }
                break;
        }

        if ($spf_factorFlag == 1) {
            echo "{success:true,valid:false,message:'.$spfFactor.'}";
        } else {
            echo "{'success':true,'valid':true}";
        }
        break;
    case 'saveSPCData':
        $data['appliedOn'] = $_POST['spc_type'];
        $data['detailId'] = $_POST['spc_detail'];
        $data['charge'] = $_POST['spc_factor'];
        $data['chargeType'] = 1;
        $data['type'] = 1;
        $data['status'] = 1;



        $spfUnique = $db->getItemSafe("SELECT COUNT(*) from additional_charge WHERE appliedOn ='?' AND detailId = {$_POST['spc_detail']} ", "s", [$_POST['spc_type']]);
        $db->query('begin');
        if ($spfUnique == 0) {
            $data['createdOn'] = date('Y-m-d H:i:s');
            $data['createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('additional_charge', $data);
            $lastId = $db->insert_id();
        } else {
            $data['updatedOn'] = date('Y-m-d H:i:s');
            $data['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('additional_charge', $data, 'update', " appliedOn ='{$_POST['spc_type']}' AND detailId = {$_POST['spc_detail']}");
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'oldsaveSPCData':
        $data = $_POST;
        $griddata = json_decode(stripslashes($data['data']));
        $griddata = (array) $griddata;

        $db->query('begin');
        $idata['appliedOn'] = 0;
        $idata['detailId'] = $griddata['business_type_id'];
        $idata['charge'] = $griddata['charge'];

        $spfUnique = $db->getItemFromDB("SELECT id from additional_charge WHERE appliedOn =  0 AND detailId = {$griddata['business_type_id']} ");
        $db->query('begin');
        if ($spfUnique == 0) {
            $idata['createdOn'] = date('Y-m-d H:i:s');
            $idata['createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('additional_charge', $idata);
            $lastId = $db->insert_id();
        } else {
            $updata['status'] = 0;
            $updata['updatedOn'] = date('Y-m-d H:i:s');
            $updata['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('additional_charge', $updata, 'update', " appliedOn =  0 AND detailId = {$griddata['business_type_id']}");
            $spfchrgAvail = $db->getItemFromDB("SELECT id from additional_charge WHERE appliedOn =  0 AND detailId = {$griddata['business_type_id']} AND charge = {$griddata['charge']}");
            if ($spfchrgAvail > 0) {
                $idata['status'] = 1;
                $idata['updatedOn'] = date('Y-m-d H:i:s');
                $idata['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('additional_charge', $idata, 'update', " id =  {$spfchrgAvail}");
            } else {
                $idata['createdOn'] = date('Y-m-d H:i:s');
                $idata['createdBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('additional_charge', $idata);
                $lastId = $db->insert_id();
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Details saved '}";
        } else {
            echo "{'success':False,'valid':false,'msg': 'Error While Saving.'}";
        }
        break;
}
