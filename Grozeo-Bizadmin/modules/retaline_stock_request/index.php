<?php

switch ($op) {
    case 'getBranch':
        $cond = " AND br_PyramidLevel > 1 ";

        $qry = $db->getMulipleData("SELECT br_ID,CONCAT(br_Name,' - ',branch_shortname) AS br_Name FROM finascop_branch where br_status = 'Active' {$cond}", true);


        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'getBranchDestination':
        $br_cpd = $db->getItemSafe("SELECT br_cpd FROM finascop_branch where br_ID = ?", "s", [$_POST['br_cpd']]);
        $br_type = $db->getItemSafe("SELECT br_type FROM finascop_branch where br_ID = ?", "s", [$_POST['br_cpd']]);
        if ($br_cpd > 0) {
            if ($br_type == 1) {
                $br_cpd = $db->getItemSafe("SELECT br_typeParent FROM finascop_branch where br_ID = ?", "s", [$_POST['br_cpd']]);
                $cond = " AND br_ID = {$br_cpd}";
            } else {
                $cond = " AND br_ID = {$br_cpd}";
            }

            //$cond = " AND br_ID <> {$_POST['br_cpd']} AND br_PyramidLevel > 2 ";
        }
        $qry = $db->getMulipleData("SELECT br_ID,CONCAT(br_Name,' - ',branch_shortname) AS br_Name FROM finascop_branch where br_status = 'Active' {$cond}", true);


        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'getItemName':
        if ($_POST['query'] != '') {
            $que = addslashes($_POST['query']);
            $searchQuery = " AND stit_SKU LIKE '%{$que}%'";
        } else {
            $searchQuery = '';
        }
        if ($_POST['sourceBranch'] > 0) {
            $br_PyramidLevel = $db->getItemSafe("SELECT br_PyramidLevel FROM finascop_branch WHERE br_ID = ?", "i", [$_POST['sourceBranch']]);
            switch ($br_PyramidLevel) {
                case 2:
                    $packageName = 'cs_package_type_name';
                    break;
                case 3:
                    $packageName = 'ds_package_type_name';
                    break;
                case 4:
                    $packageName = 'cos_package_type_name';
                    break;
                default:
                    $packageName = '';
                    break;
            }
            //$searchQuery .= " AND branch_id = {$_POST['sourceBranch']}";
        }
        $searchQuery .= " AND (stit_ParentItemId = 0 OR directPurchase = 1) ";

        //finascop_getjsonkeyarray("SELECT stit_ID,stit_itemName,stit_SKU FROM finascop_stock_itemmaster where 1=1 {$searchQuery} ");
        $qry = $db->getMulipleData("SELECT id,fsi.stit_ID as stit_ID,stit_itemName,stit_SKU,cs_package_type_name,{$packageName} as packageName,fsbg_id,fpod_leastSKUmrp FROM finascop_stock_itemmaster fsi "
                . "INNER JOIN finascop_stock_branch_inventory fsb ON fsb.stit_id=fsi.stit_ID  where 1=1 {$searchQuery} GROUP BY fsi.stit_ID ", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'generateUniqueId':
        $uniqueId = '';
        while ($uniqueId == '') {
            $uniqueId = getNewFinascopApiKey();
        }
        echo '{"uid":"' . $uniqueId . '"}';
        break;
    case 'addItemstoStockRequest':
        $uuid = $_POST['uuid'];
        $fsr_id = $_POST['fsr_id'];
        $fsrd['fsr_ItemId'] = $_POST['fsr_ItemId'];
        $fsrd['fsr_RequiredItemQty'] = $_POST['fsr_RequiredItemQty'];
        $fsrd['fsr_ApprovedItemQty'] = $_POST['fsr_RequiredItemQty'];
        $fsrd['fsr_ItemMRP'] = $_POST['fsr_ItemMRP'];

        $fsrd['fsr_ItemUnits'] = $_POST['fsr_StockItemUnits'];
        $fsrd['fsr_leastSKUCount'] = $_POST['leastSKUCountfsr'];
        $fsrd['least_package_type_id'] = $_POST['fsrleast_package_type_id'];

        $db->query('begin');
        if ($fsr_id > 0) {
            $count = $db->getItemSafe("SELECT COUNT(*) FROM finascop_stock_request_details WHERE fsr_id = {$fsr_id} AND fsr_ItemId = ?", "i", [$_POST['fsr_ItemId']]);

            if ($count > 0) {
                $fsrd['fsrd_createdOn'] = date("Y-m-d H:i");
                $fsrd['fsrd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('finascop_stock_request_details', $fsrd, 'update', " fsr_id = {$fsr_id} AND fsr_ItemId = {$_POST['fsr_ItemId']}");
            } else {
                $fsrd['fsr_id'] = $fsr_id;
                $fsrd['fsrd_createdOn'] = date("Y-m-d H:i");
                $fsrd['fsrd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('finascop_stock_request_details', $fsrd);
            }
        } else {
            $count = $db->getItemSafe("SELECT COUNT(*) FROM finascop_stock_request_details WHERE uuid = '{$uuid}' AND fsr_ItemId = ?", "i", [$_POST['fsr_ItemId']]);

            if ($count > 0) {
                $fsrd['fsrd_createdOn'] = date("Y-m-d H:i");
                $fsrd['fsrd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('finascop_stock_request_details', $fsrd, 'update', " uuid = '{$uuid}' AND fsr_ItemId = {$_POST['fsr_ItemId']}");
            } else {
                $fsrd['uuid'] = $uuid;
                $fsrd['fsrd_createdOn'] = date("Y-m-d H:i");
                $fsrd['fsrd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('finascop_stock_request_details', $fsrd);
            }
        }

        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Items added to request.'";
            echo '{"success":true,"valid":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error occured while saving.'";
            echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
        }
        break;
    case 'listStockRequestItemStore':
        $uuid = $_POST['uuid'];
        $fsr_id = $_POST['fsr_id'];
        $rec_sort = empty($data['sort']) ? 'fsr_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['fssr_id', 'fssr_SRNumber', 'fssr_createdOn', 'fssr_source', 'fssr_destination', 'fssr_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        if ($fsr_id > 0) {
            $cond = " AND fsr_id = {$fsr_id}";
        } else {
            if (!empty($uuid))
                $cond = " AND uuid = '{$uuid}'";
            else
                $cond = "AND uuid = 0";
        }
        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . "finascop_stock_request_details where {$filter_part} {$cond}";
        $listQuery = "SELECT uuid,fsr_id,fsrd_id,fsr_ItemId,fsr_RequiredItemQty,fsrd_status,(SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = fsr_ItemId) as fsr_ItemName,"
                . "fsr_ItemUnits,fsr_leastSKUCount,least_package_type_id,(select package_type_name from mypha_productpackage_type WHERE package_type_id = least_package_type_id) as leastSkuUnit,"
                . "(select package_type_name from mypha_productpackage_type WHERE package_type_id = fsr_ItemUnits) as unitName "
                . "from " . FINASCOP_DB . "finascop_stock_request_details WHERE {$filter_part} {$cond} ORDER BY $rec_sort $rec_sort_dir ";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'saveStockRequest':
        $uuid = $_POST['uuid'];
        $fsr_id = $_POST['fsr_id'];
        $db->query('begin');
        if ($fsr_id > 0) {
            $fsrup['fsr_updatedOn'] = date("Y-m-d H:i");
            $fsrup['fsr_updatedBy'] = $_SESSION['admin']->UserId;
            $status = $db->perform('finascop_stock_request', $fsrup, 'update', " fsr_id = {$fsr_id}");

            $fsrdup['fsrd_status'] = 1;
            $fsrdup['fsrd_updatedOn'] = date("Y-m-d H:i");
            $fsrdup['fsrd_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('finascop_stock_request_details', $fsrdup, 'update', " fsr_id = {$fsr_id} ");
            $lastId = $fsr_id;
        } else {
            $fsr['fsr_source'] = $_POST['fsr_source'];
            $fsr['fsr_destination'] = $_POST['fsr_destination'];
            $fsr['fsr_createdOn'] = date("Y-m-d H:i");
            $fsr['fsr_createdBy'] = $_SESSION['admin']->UserId;
            $fsr['fsr_initiatedBy'] = $_SESSION['admin']->finascop_current_branch_id;

            $date = date("Y-m-d H:i");
            $tdy = date("Y-m-d") . " 00:00";
            $maxId = $db->getItemSafe("select right(fsr_uid,3)*1 as fsr_uid  from `finascop_stock_request` where `fsr_source` = ? and `fsr_createdOn` between '{$tdy}' and '{$date}' order by `fsr_id` desc limit 1", "s", [$_POST['fsr_source']]);
            $br_key = $db->getItemSafe("SELECT br_key FROM finascop_branch WHERE br_ID=?", "s", [$_POST['fsr_source']]);
            $uid_max = getNewSRNumber($_POST['fsr_source']);
            $fsr['fsr_uid'] = $uid_max;
            $status = $db->perform('finascop_stock_request', $fsr);
            $lastId = $db->insert_id();
            $fsrd['fsrd_status'] = 1;
            $fsrd['fsr_id'] = $lastId;
            $fsrd['fsrd_createdOn'] = date("Y-m-d H:i");
            $fsrd['fsrd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('finascop_stock_request_details', $fsrd, 'update', " uuid = '{$uuid}'");
        }


        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Items added to request.'";
            echo '{"success":true,"valid":true,"fsrId":' . $lastId . ',"msg":' . $msg . '}';
        } else {
            $msg = "'Error occured while saving.'";
            echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
        }
        break;
    case 'listRetalineStockRequest':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'fsr_id' : ($sort == 'fsr_createdOn' ? 'fsr_id' : $sort );
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 AND fsr_type = 0 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status_name') {
                            if ($field['data']['value'] == 'Transfer Requested') {
                                //    $field['data']['value'] = 2;
                                $fiterItem = 1;
                                $search .= " and (fsrs_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Partially Attended') {
                                //    $field['data']['value'] = 3;
                                $fiterItem = 5;
                                $search .= " and (fsrs_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Completely Attended') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 10;
                                $search .= " and (fsrs_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Invoke Expired') {
                                //    $field['data']['value'] = 3;
                                $fiterItem = 15;
                                $search .= " and (fsrs_status = {$fiterItem}) ";
                            } else {
                                //    $field['data']['value'] = 0;
                                $search .= " and (fsrs_status = 1 or fsrs_status = 5 or fsrs_status = 10 ) ";
                            }
                        }

                        break;
                    case 'string':
                        foreach ($filter as $key => $field) {
                            if ($field['data']['value'] != "") {
                                $checkComa = strstr($field['data']['value'], ',');
                                if ($checkComa != '') {
                                    $fiterItem = $field['data']['value'];
                                    $fiterItem = str_replace(',', "','", $fiterItem);
                                    $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                                } else {
                                    $search .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                                }
                            }
                        }
                }
//                if ($field['data']['value'] != "") {
//                    $checkComa = strstr($field['data']['value'], ',');
//                    if ($checkComa != '') {
//                        $fiterItem = $field['data']['value'];
//                        $fiterItem = str_replace(',', "','", $fiterItem);
//                        $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
//                    } else {
//                        $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
//                    }
//                }
            }
        }
        $branchName = $_POST['branchName'];
        $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;

        if ($_SESSION['admin']->br_PyramidLevel == 1){
            $where = " ";
            /*if ($_SESSION['admin']->IsSuperUser == 'Yes') {
                $where = " ";
            } else {
                $where = " AND fsr_source =" . $br_ID;
            }*/
            
        }else{
            $where = " AND fsr_source =" . $_SESSION['admin']->finascop_current_branch_id;
        }
            
        $countQuery = "SELECT COUNT(*) FROM finascop_stock_request  INNER JOIN finascop_stock_request_status ON fsrs_id = finascop_stock_request.fsrs_status {$search} {$where}";

        $listQuery = "SELECT fsr_id,fsr_uid,fsr_source,fsr_destination,finascop_stock_request.fsrs_status,"
                . "CASE WHEN fsr_type=0 THEN 'Stock Request' WHEN fsr_type=1 THEN 'Inter Branch Transfer'  END AS fsr_type,"
                . "finascop_stock_request_status.fsrs_status AS status_name,"
                . "DATE_FORMAT(fsr_createdOn,'%d %M %Y') as fsr_createdOn,"
                . "fsr_initiatedBy,(SELECT CONCAT(br_Name,' - ',branch_shortname) FROM finascop_branch WHERE br_ID = fsr_initiatedBy) as initiatedBranch,"
                . "(SELECT CONCAT(br_Name,' - ',branch_shortname) FROM finascop_branch where br_ID = fsr_source) as sourcename,"
                . "(SELECT CONCAT(br_Name,' - ',branch_shortname) FROM finascop_branch where br_ID = fsr_destination) as branch  FROM finascop_stock_request INNER JOIN finascop_stock_request_status ON fsrs_id = finascop_stock_request.fsrs_status   {$search} {$where} ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'getBranchName':
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        $qry = $db->getMulipleData("SELECT br_ID,CONCAT(br_Name,' - ',branch_shortname) AS br_Name FROM finascop_branch WHERE br_status = 'Active' AND (br_PyramidLevel = 2  OR br_ID={$branch_id})", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'listCreateTransferOrderData':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'fsrd_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status_name') {
                            if ($field['data']['value'] == 'Requested') {
                                //    $field['data']['value'] = 2;
                                $fiterItem = 1;
                                $search .= " and (fsrd_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Ordered') {
                                //    $field['data']['value'] = 3;
                                $fiterItem = 2;
                                $search .= " and (fsrd_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Deleted') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 3;
                                $search .= " and (fsrd_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Packed') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 4;
                                $search .= " and (fsrd_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Partially Received') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 5;
                                $search .= " and (fsrd_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Received') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 6;
                                $search .= " and (fsrd_status = {$fiterItem}) ";
                            } else {
                                //    $field['data']['value'] = 0;
                                $search .= " and (fsrd_status = 1 or fsrd_status = 2 or fsrd_status = 3 or fsrd_status = 4 or fsrd_status = 5 or fsrd_status = 6) ";
                            }
                        }

                        break;
                    case 'string':
                        foreach ($filter as $key => $field) {
                            if ($field['data']['value'] != "") {
                                $checkComa = strstr($field['data']['value'], ',');
                                if ($checkComa != '') {
                                    $fiterItem = $field['data']['value'];
                                    $fiterItem = str_replace(',', "','", $fiterItem);
                                    $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                                } else {
                                    $search .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                                }
                            }
                        }
                }
            }
        }

        $countQry = "SELECT COUNT(*) FROM finascop_stock_request_details  {$search} AND fsr_id = " . intval($_POST['fsr_id']) . "";

        $listQry = "SELECT fsr_id,fsrd_id,fsr_ItemId,fsr_ApprovedItemQty,fsr_RequiredItemQty,fsrd_status,fsr_ItemUnits,fsr_leastSKUCount,least_package_type_id,"
                . "CASE WHEN fsrd_status=1 THEN 'Requested' WHEN fsrd_status=2 THEN 'Ordered' WHEN fsrd_status=3 THEN 'Deleted' WHEN fsrd_status=4 THEN 'Packed' "
                . "WHEN fsrd_status=5 THEN 'Partially Received' WHEN fsrd_status=6 THEN 'Received' ELSE 'Not Saved' END AS status_name FROM finascop_stock_request_details {$search} AND fsr_id ={$_POST['fsr_id']} ORDER BY {$sort} {$dir} ";
        $datas = $db->getMulipleData($listQry, true);
        $fsr_source = $db->getItemSafe("SELECT fsr_source FROM finascop_stock_request WHERE fsr_id = ?", "i", [$_POST['fsr_id']]);
        $sourcePyramid = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch where br_ID = {$fsr_source}");
        $resCount = count($datas);
        $count = $db->getItemFromDB($countQry);
        //print_r($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $datas[$i]['fsr_ItemName'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = {$datas[$i]['fsr_ItemId']}");
                $packageType = $db->getFromDB("SELECT isMedicine,cs_package_type_name,ds_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['fsr_ItemId']}", true);
                if ($sourcePyramid == 2) {
                    $packTyp = $packageType['cs_package_type_name'];
                } else if ($sourcePyramid == 3) {
                    $packTyp = $packageType['ds_package_type_name'];
                } else if ($sourcePyramid == 4) {
                    $packTyp = $packageType['ds_package_type_name'];
                }
                if ($packageType['isMedicine'] == 0) {
                    $product_category = $db->getItemFromDB("SELECT product_category FROM finascop_stock_itemmaster where stit_ID = {$datas[$i]['fsr_ItemId']}");
                    $datas[$i]['subcategory'] = $product_category;
                    $category = $db->getFromDB("SELECT main_category,sub_category FROM mypha_productsubcategory where sub_category_id = {$product_category}", true);
                    $datas[$i]['subcategoryname'] = $category['sub_category'];
                    $main_category = $category['main_category'];
                    if ($main_category > 0) {
                        $datas[$i]['category_name'] = $db->getItemFromDB("SELECT category_name FROM mypha_productcategory where category_id = {$main_category}");
                    } else {
                        $datas[$i]['category_name'] = '';
                    }
                } else {
                    $product_category = $db->getItemFromDB("SELECT product_category FROM finascop_stock_itemmaster where stit_ID = {$datas[$i]['fsr_ItemId']}");
                    $datas[$i]['subcategory'] = $product_category;
                    $category = $db->getFromDB("SELECT category_id,subCategory_name FROM mypha_subCategory where subCategory_id = {$product_category}", true);
                    $datas[$i]['subcategoryname'] = $category['subCategory_name']; //Drug Group
                    $main_category = $category['category_id'];
                    if ($main_category > 0) {
                        $datas[$i]['category_name'] = $db->getItemFromDB("SELECT category_name FROM mypha_category where category_id = {$main_category}"); //system
                    } else {
                        $datas[$i]['category_name'] = '';
                    }
                }


                $datas[$i]['packageType'] = $db->getItemFromDB("(select package_type_name from mypha_productpackage_type WHERE package_type_id = {$datas[$i]['fsr_ItemUnits']})");
                $datas[$i]['least_package_type'] = $db->getItemFromDB("(select package_type_name from mypha_productpackage_type WHERE package_type_id = {$datas[$i]['least_package_type_id']})");
            }
        }
        echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        break;
    case 'removeEnquiry':
        $id = $_POST['fsrd_id'];
        $fsr_id = $_POST['fsr_id'];
        $data = array(
            'fsrd_status' => 3
        );
        $db->query('begin');
        $qry = $db->perform(FINASCOP_DB . 'finascop_stock_request_details', $data, 'update', 'fsrd_id=' . $id);
        if ($qry) {
            $total_count = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_request_details WHERE fsr_id={$fsr_id}");
            $status_requested_count = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_request_details WHERE fsr_id={$fsr_id} AND fsrd_status=1");
            if ($status_requested_count == 0) {
                $data = array(
                    'fsrs_status' => 10
                );
                $qry = $db->perform(FINASCOP_DB . 'finascop_stock_request', $data, 'update', 'fsr_id=' . $fsr_id);

//                }
            } else if ($total_count > $status_requested_count) {
                $data = array(
                    'fsrs_status' => 5
                );
                $qry = $db->perform(FINASCOP_DB . 'finascop_stock_request', $data, 'update', 'fsr_id=' . $fsr_id);
            }
        }
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;

    case 'removeEnquiryMain':
        $id = $_POST['fsr_id'];
        //$status = $_POST['activestatus'];
        $data = array(
            'fsrs_status' => 6
        );
        $db->query('begin');
        $qry = $db->perform(FINASCOP_DB . 'finascop_stock_request', $data, 'update', 'fsr_id=' . $id);
        if ($qry) {
            $datas = array(
                'fsrd_status' => 3
            );
            $qrys = $db->perform(FINASCOP_DB . 'finascop_stock_request_details', $datas, 'update', 'fsr_id=' . $id);
        }
        $status = $db->query('commit');
        if ($status) {

            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'deleteItem':
        $id = $_POST['fsrd_id'];
        $del_query = "DELETE FROM finascop_stock_request_details WHERE fsrd_id=" . $id;
        $db->query($del_query);
        $status = $db->query('commit');
        if ($status) {

            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'updateToItemQuantity':

        $data = $_POST;
        $griddata = json_decode(stripslashes($data['data']));
        $griddata = (array) $griddata;


        $fsr_StockItemUnits = $griddata['fsr_StockItemUnits'];
        $itemId = $griddata['fsr_ItemId'];
        $packTypes = $db->getFromDB("SELECT stdpckl11_package_type_id,stdpckl21_package_type_id,stdpckl31_package_type_id,stdpckl41_package_type_id,least_package_type_name,least_package_type_id,stdpckl1_nos,stdpckl2_nos,"
                . "stdpckl3_nos FROM finascop_stock_itemmaster WHERE stit_ID = {$itemId}", true);
        //print_r($packTypes);
        if ($fsr_StockItemUnits == $packTypes['stdpckl12_package_type_id']) {
            $level = '4';
            $fpot_leastSKUqty = $fsr_RequiredItemQty / $packTypes['stdpckl1_nos'];
        } else if ($fsr_StockItemUnits == $packTypes['stdpckl11_package_type_id']) {
            $level = '3';
            $fpot_leastSKUqty = $fsr_RequiredItemQty;
        } else if ($fsr_StockItemUnits == $packTypes['stdpckl21_package_type_id']) {
            $level = '2';
            $fpot_leastSKUqty = $fsr_RequiredItemQty * $packTypes['stdpckl2_nos'];
        } else if ($fsr_StockItemUnits == $packTypes['stdpckl31_package_type_id']) {
            $level = '1';
            $fpot_leastSKUqty = $fsr_RequiredItemQty * $packTypes['stdpckl2_nos'] * $packTypes['stdpckl3_nos'];
        }

        $db->query('begin');
        $lpupDatat['least_package_type_id'] = $packTypes['least_package_type_id'];
        $lpupDatat['fsr_leastSKUCount'] = $fpot_leastSKUqty;
        $lpupDatat['fsr_ApprovedItemQty'] = $griddata['fsr_ApprovedItemQty'];
        $lpupDatat['fsrd_updatedOn'] = date("Y-m-d H:i:s");
        $lpupDatat['fsrd_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('finascop_stock_request_details', $lpupDatat, 'update', " fsrd_id = {$griddata['fsrd_id']}");
        $status = $db->query('commit');
        if ($status) {
            $msg = "Quantity Updated Successfully";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'createTransferRequest':
        $db->query('begin');
        //$items = array();
        $fsr_ItemId = json_decode(stripslashes($_POST['fsr_ItemId']), true);
        $fsrd_id = json_decode(stripslashes($_POST['fsrd_id']), true);
        $fsr_ApprovedItemQty = json_decode(stripslashes($_POST['fsr_ApprovedItemQty']), true);
        $fsr_leastSKUCount = json_decode(stripslashes($_POST['fsr_leastSKUCount']), true);
        $fsr_ItemName = json_decode(stripslashes($_POST['fsr_ItemName']), true);
        $fsr_id = $_POST['fsr_id'];
        $subcategoryname = json_decode(stripslashes($_POST['subcategoryname']), true);
        $date = date('Y-m-d H:i:s');
        if ($fsr_id) {
            $order_det = $db->getFromDb("SELECT fsr_source,fsr_destination,fsr_type FROM finascop_stock_request WHERE fsr_id={$fsr_id}", true);


            $data['fstr_destination'] = $order_det['fsr_source'];
            $data['fstr_source'] = $order_det['fsr_destination'];
            $data['fstr_type'] = 2;
            $tdy = date("Y-m-d") . " 00:00:00";
            $maxId = $db->getItemFromDB("select right(fstr_uid,3)*1 as fstr_uid  from `finascop_stock_transfer_request` where `fstr_source` = {$order_det['fsr_destination']} and `fstr_createdOn` between '{$tdy}' and '{$date}' order by `fstr_id` desc limit 1");
            $br_key = $db->getItemFromDB("SELECT br_key FROM finascop_branch WHERE br_ID={$order_det['fsr_destination']}");
            $uid_max = getNewTRNumber($order_det['fsr_destination']);
            $data['fstr_uid'] = $uid_max;
            $data['fstr_createdOn'] = $date;
            $data['fstr_initiatedBy'] = $_SESSION['admin']->finascop_current_branch_id;
            $data['fstr_updatedOn'] = $date;
            $data['fstr_updatedBy'] = $_SESSION['admin']->UserId;
            $data['fstr_createdBy'] = $_SESSION['admin']->UserId;
            $data['fstr_status'] = 1;

            $status = $db->perform('finascop_stock_transfer_request', $data);
            $lastId = $db->insert_id();


            if ($lastId) {
                for ($i = 0; $i < count($fsr_ItemId); $i++) {
                    $unique_id = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_request_details WHERE fsrd_id={$fsrd_id[$i]} AND fsrd_status=1");
                    if ($unique_id > 0) {
                        $requestDetails = $db->getFromDB("SELECT * FROM finascop_stock_request_details WHERE fsrd_id={$fsrd_id[$i]} AND fsrd_status=1", true);

                        $data_details['fstr_ItemId'] = $fsr_ItemId[$i];
                        $data_details['fstr_RequiredItemQty'] = $fsr_leastSKUCount[$i];
                        $data_details['fstr_ApprovedItemQty'] = $fsr_leastSKUCount[$i];
                        $data_details['fstr_id'] = $lastId;
                        $data_details['fstrd_status'] = 1;

                        $data_details['uuid'] = $uid_max;
                        $data_details['fstrd_createdBy'] = $_SESSION['admin']->UserId;
                        $data_details['fstrd_createdOn'] = $date;
                        $data_details['fstr_ItemUnits'] = $requestDetails['fsr_ItemUnits'];
                        $data_details['fstr_leastSKUCount'] = $requestDetails['fsr_leastSKUCount'];
                        $data_details['least_package_type_id'] = $requestDetails['least_package_type_id'];


                        $itemPriceDetails = $db->getFromDB("SELECT fpod_leastSKUmrp,fpod_leastSKUepr FROM finascop_stock_branch_inventory WHERE stit_id = {$fsr_ItemId[$i]} AND branch_id = {$order_det['fsr_destination']} limit 1", true);
                        if (count($itemPriceDetails) > 0) {
                            $data_details['fstr_ItemMRP'] = $itemPriceDetails['fpod_leastSKUmrp'];
                        }
                        $data_details = array_filter($data_details, 'strlen');

                        $status = $db->perform('finascop_stock_transfer_request_details', $data_details);

                        if ($status) {
                            $datas = array(
                                'fsrd_status' => 2
                            );
                            $status = $db->perform(FINASCOP_DB . 'finascop_stock_request_details', $datas, 'update', 'fsrd_id=' . $fsrd_id[$i]);
                            $total_count = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_request_details WHERE fsr_id={$fsr_id}");
                            $status_requested_count = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_request_details WHERE fsr_id={$fsr_id} AND fsrd_status=1");
                            if ($status_requested_count == 0) {
                                $datasr = array(
                                    'fstr_id' => $lastId,
                                    'fsrs_status' => 10
                                );
                                $status = $db->perform(FINASCOP_DB . 'finascop_stock_request', $datasr, 'update', 'fsr_id=' . $fsr_id);
                            } else if ($total_count > $status_requested_count) {
                                $data = array(
                                    'fstr_id' => $lastId,
                                    'fsrs_status' => 5
                                );
                                $qry = $db->perform(FINASCOP_DB . 'finascop_stock_request', $data, 'update', 'fsr_id=' . $fsr_id);
                            }
                        }
                    }
                }
            }
            $orderLogs = $db->getMultipleData("SELECT * FROM order_request_log WHERE fsr_id = {$fsr_id}", true);
            if ($orderLogs[0]['order_id'] > 0) {
                foreach ($orderLogs as $orderLog) {
                    $rcodata['updated_at'] = date('Y-m-d H:i:s');
                    $rcodata['status_id'] = 42;
                    $status = $db->perform('retaline_customer_order', $rcodata, 'update', " status_id = 41 AND order_id = {$orderLog['order_id']}");
                }
            }
        } else {
            $msg = "Please select a request for transfer";
            echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
            exit();
        }
        $status = $db->query('commit');
        if ($status) {
            $msg = "Order transfered succesfully";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'getPackingDetails':
        $fsr_StockItemUnits = $_POST['fsr_StockItemUnits'];
        $itemId = $_POST['itemId'];
        $fsr_RequiredItemQty = $_POST['fsr_RequiredItemQty'];
        $packTypes = $db->getFromDB("SELECT stdpckl11_package_type_id,stdpckl21_package_type_id,stdpckl31_package_type_id,stdpckl41_package_type_id,least_package_type_name,least_package_type_id,stdpckl1_nos,stdpckl2_nos,stdpckl3_nos FROM finascop_stock_itemmaster WHERE stit_ID = {$itemId}", true);
        //print_r($packTypes);
        if ($fsr_StockItemUnits == $packTypes['stdpckl12_package_type_id']) {
            $level = '4';
            $fpot_leastSKUqty = $fsr_RequiredItemQty / $packTypes['stdpckl1_nos'];
        } else if ($fsr_StockItemUnits == $packTypes['stdpckl11_package_type_id']) {
            $level = '3';
            $fpot_leastSKUqty = $fsr_RequiredItemQty;
        } else if ($fsr_StockItemUnits == $packTypes['stdpckl21_package_type_id']) {
            $level = '2';
            $fpot_leastSKUqty = $fsr_RequiredItemQty * $packTypes['stdpckl2_nos'];
        } else if ($fsr_StockItemUnits == $packTypes['stdpckl31_package_type_id']) {
            $level = '1';
            $fpot_leastSKUqty = $fsr_RequiredItemQty * $packTypes['stdpckl2_nos'] * $packTypes['stdpckl3_nos'];
        }
        $result['leastSKUqty'] = $fpot_leastSKUqty;
        $result['least_package_type_id'] = $packTypes['least_package_type_id'];
        $result['least_package_type_name'] = $packTypes['least_package_type_name'];
        $result['success'] = true;
        if (!empty($result)) {
            echo json_encode($result);
        }
        break;
    case 'getPTStore':
        $packs = array();
        $itemId = $_POST['itemId'];
        $search_hint = $_POST['query'];
        $packTypes = $db->getFromDB("SELECT stdpckl11_package_type_id,stdpckl21_package_type_id,stdpckl31_package_type_id,stdpckl41_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemId}", true);
        //print_r($_POST);
        if (!in_array($packTypes['stdpckl11_package_type_id'], $packs)) {
            $packs[] = $packTypes['stdpckl11_package_type_id'];
        }
//        if ($_POST['isMedicine'] != 1) {
//            if (!in_array($_POST['stdpckl12'], $packs)) {
//                $packs[] = $_POST['stdpckl12'];
//            }
//        }

        if (!in_array($packTypes['stdpckl21_package_type_id'], $packs)) {
            $packs[] = $packTypes['stdpckl21_package_type_id'];
        }
        if (!in_array($packTypes['stdpckl31_package_type_id'], $packs)) {
            $packs[] = $packTypes['stdpckl31_package_type_id'];
        }
        if (!in_array($packTypes['stdpckl41_package_type_id'], $packs)) {
            $packs[] = $packTypes['stdpckl41_package_type_id'];
        }
        $packs = array_filter($packs);
        if (count($packs) > 0) {
            $pachTyp = implode(',', $packs);
            $qry = "select package_type_id,package_type_name from " . FINASCOP_DB . "mypha_productpackage_type WHERE package_type_id IN ({$pachTyp}) AND status = 1 AND package_type_name LIKE '{$search_hint}%'order by package_type_name";
            $data = $db->getMultipleData($qry, true);
        }

        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'intrBranch':
        $cond = " AND br_PyramidLevel > 2 ";

        $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch where br_status = 'Active' {$cond}", true);


        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'intrBranchDestination':
        $br_cpd = $db->getItemSafe("SELECT br_cpd FROM finascop_branch where br_ID = ?", "s", [$_POST['br_cpd']]);
        if ($br_cpd > 0) {
            $cond = " AND br_ID <> {$_POST['br_cpd']} AND br_PyramidLevel > 2 ";
        }
        $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch where br_status = 'Active' {$cond}", true);


        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'loadStockRequest':
        $fsr_id = $_POST['fsr_id'];
        $podata = $db->getFromDB("SELECT  fsr_source,fsr_destination from finascop_stock_request where fsr_id = {$fsr_id}", true);
        if (!empty($podata)) {
            echo json_encode($podata);
        }
        break;
    case 'getItemNameIntrBrnch':
        if ($_POST['query'] != '') {
            $que = addslashes($_POST['query']);
            $searchQuery = " AND stit_SKU LIKE '%{$que}%'";
        } else {
            $searchQuery = '';
        }
        if ($_POST['sourceBranch'] > 0) {
            $br_PyramidLevel = $db->getItemSafe("SELECT br_PyramidLevel FROM finascop_branch WHERE br_ID = ?", "i", [$_POST['sourceBranch']]);
            switch ($br_PyramidLevel) {
                case 2:
                    $packageName = 'cs_package_type_name';
                    break;
                case 3:
                    $packageName = 'ds_package_type_name';
                    break;
                case 4:
                    $packageName = 'cos_package_type_name';
                    break;
            }
            $searchQuery .= " AND branch_id = {$_POST['sourceBranch']}";
        }
        $searchQuery .= " AND (stit_ParentItemId = 0 OR directPurchase = 1) AND item_count > 0 ";

        //finascop_getjsonkeyarray("SELECT stit_ID,stit_itemName,stit_SKU FROM finascop_stock_itemmaster where 1=1 {$searchQuery} ");
        $qry = $db->getMulipleData("SELECT id,fsi.stit_ID as stit_ID,stit_itemName,stit_SKU,cs_package_type_name,{$packageName} as packageName,fsbg_id,fpod_leastSKUmrp,item_count FROM finascop_stock_itemmaster fsi "
                . "INNER JOIN finascop_stock_branch_inventory fsb ON fsb.stit_id=fsi.stit_ID  where 1=1 {$searchQuery}", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'addItemstoInterBranch':
        $uuid = $_POST['uuid'];
        $fsr_id = $_POST['fsr_id'];
        $fsrd['fsr_ItemId'] = $_POST['fsr_ItemId'];
        $fsrd['fsr_RequiredItemQty'] = $_POST['fsr_RequiredItemQty'];
        $fsrd['fsr_ApprovedItemQty'] = $_POST['fsr_RequiredItemQty'];
        $fsrd['fsr_ItemMRP'] = $_POST['fsr_ItemMRP'];

        $fsrd['fsr_ItemUnits'] = $_POST['fsr_StockItemUnits'];
        $fsrd['fsr_leastSKUCount'] = $_POST['leastSKUCountfsr'];
        $fsrd['least_package_type_id'] = $_POST['fsrleast_package_type_id'];

        $db->query('begin');
        if ($fsr_id > 0) {
            $count = $db->getItemSafe("SELECT COUNT(*) FROM finascop_stock_request_details WHERE fsr_id = {$fsr_id} AND fsr_ItemId = ?", "i", [$_POST['fsr_ItemId']]);

            if ($count > 0) {
                $fsrd['fsrd_createdOn'] = date("Y-m-d H:i");
                $fsrd['fsrd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('finascop_stock_request_details', $fsrd, 'update', " fsr_id = {$fsr_id} AND fsr_ItemId = {$_POST['fsr_ItemId']}");
            } else {
                $fsrd['fsr_id'] = $fsr_id;
                $fsrd['fsrd_createdOn'] = date("Y-m-d H:i");
                $fsrd['fsrd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('finascop_stock_request_details', $fsrd);
            }
        } else {
            $count = $db->getItemSafe("SELECT COUNT(*) FROM finascop_stock_request_details WHERE uuid = '{$uuid}' AND fsr_ItemId = ?", "i", [$_POST['fsr_ItemId']]);

            if ($count > 0) {
                $fsrd['fsrd_createdOn'] = date("Y-m-d H:i");
                $fsrd['fsrd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('finascop_stock_request_details', $fsrd, 'update', " uuid = '{$uuid}' AND fsr_ItemId = {$_POST['fsr_ItemId']}");
            } else {
                $fsrd['uuid'] = $uuid;
                $fsrd['fsrd_createdOn'] = date("Y-m-d H:i");
                $fsrd['fsrd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('finascop_stock_request_details', $fsrd);
            }
        }

        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Items added for transfer.'";
            echo '{"success":true,"valid":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error occured while saving.'";
            echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
        }
        break;
    case 'saveInterBranch':
        $uuid = $_POST['uuid'];
        $fsr_id = $_POST['fsr_id'];
        $db->query('begin');
        if ($fsr_id > 0) {
            $fsrup['fsr_updatedOn'] = date("Y-m-d H:i");
            $fsrup['fsr_updatedBy'] = $_SESSION['admin']->UserId;
            $status = $db->perform('finascop_stock_request', $fsrup, 'update', " fsr_id = {$fsr_id}");

            $fsrdup['fsrd_status'] = 1;
            $fsrdup['fsrd_updatedOn'] = date("Y-m-d H:i");
            $fsrdup['fsrd_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('finascop_stock_request_details', $fsrdup, 'update', " fsr_id = {$fsr_id} ");
            $lastId = $fsr_id;
        } else {
            $fsr['fsr_type'] = 1;
            $fsr['fsr_source'] = $_POST['fsr_source'];
            $fsr['fsr_destination'] = $_POST['fsr_destination'];
            $fsr['fsr_createdOn'] = date("Y-m-d H:i");
            $fsr['fsr_createdBy'] = $_SESSION['admin']->UserId;
            $fsr['fsr_initiatedBy'] = $_SESSION['admin']->finascop_current_branch_id;

            $date = date("Y-m-d H:i");
            $tdy = date("Y-m-d") . " 00:00";
            $maxId = $db->getItemSafe("select right(fsr_uid,3)*1 as fsr_uid  from `finascop_stock_request` where `fsr_source` = ? and `fsr_createdOn` between '{$tdy}' and '{$date}' order by `fsr_id` desc limit 1", "s", [$_POST['fsr_source']]);
            $br_key = $db->getItemSafe("SELECT br_key FROM finascop_branch WHERE br_ID=?", "s", [$_POST['fsr_source']]);
            $uid_max = getNewSRNumber($_POST['fsr_source']);
            $fsr['fsr_uid'] = $uid_max;
            $status = $db->perform('finascop_stock_request', $fsr);
            $lastId = $db->insert_id();
            $fsrd['fsrd_status'] = 1;
            $fsrd['fsr_id'] = $lastId;
            $fsrd['fsrd_createdOn'] = date("Y-m-d H:i");
            $fsrd['fsrd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('finascop_stock_request_details', $fsrd, 'update', " uuid = '{$uuid}'");
        }


        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Items added to request.'";
            echo '{"success":true,"valid":true,"fsrId":' . $lastId . ',"msg":' . $msg . '}';
        } else {
            $msg = "'Error occured while saving.'";
            echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
        }
        break;
    case 'listRetalineInterBranch':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'fsr_id' : ($sort == 'fsr_createdOn' ? 'fsr_id' : $sort );
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 AND fsr_type = 1 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status_name') {
                            if ($field['data']['value'] == 'Transfer Requested') {
                                //    $field['data']['value'] = 2;
                                $fiterItem = 1;
                                $search .= " and (fsrs_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Partially Attended') {
                                //    $field['data']['value'] = 3;
                                $fiterItem = 5;
                                $search .= " and (fsrs_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Completely Attended') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 10;
                                $search .= " and (fsrs_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Invoke Expired') {
                                //    $field['data']['value'] = 3;
                                $fiterItem = 15;
                                $search .= " and (fsrs_status = {$fiterItem}) ";
                            } else {
                                //    $field['data']['value'] = 0;
                                $search .= " and (fsrs_status = 1 or fsrs_status = 5 or fsrs_status = 10 ) ";
                            }
                        }

                        break;
                    case 'string':
                        foreach ($filter as $key => $field) {
                            if ($field['data']['value'] != "") {
                                $checkComa = strstr($field['data']['value'], ',');
                                if ($checkComa != '') {
                                    $fiterItem = $field['data']['value'];
                                    $fiterItem = str_replace(',', "','", $fiterItem);
                                    $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                                } else {
                                    $search .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                                }
                            }
                        }
                }
//                if ($field['data']['value'] != "") {
//                    $checkComa = strstr($field['data']['value'], ',');
//                    if ($checkComa != '') {
//                        $fiterItem = $field['data']['value'];
//                        $fiterItem = str_replace(',', "','", $fiterItem);
//                        $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
//                    } else {
//                        $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
//                    }
//                }
            }
        }
        $branchName = $_POST['branchName'];
        $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;

        if ($_SESSION['admin']->br_PyramidLevel == 1)
            $where = " AND fsr_source =" . $br_ID;
        else
            $where = " AND fsr_source =" . $_SESSION['admin']->finascop_current_branch_id;
        $countQuery = "SELECT COUNT(*) FROM finascop_stock_request  INNER JOIN finascop_stock_request_status ON fsrs_id = finascop_stock_request.fsrs_status {$search} {$where}";

        $listQuery = "SELECT fsr_id,fsr_uid,fsr_source,fsr_destination,finascop_stock_request.fsrs_status,"
                . "CASE WHEN fsr_type=0 THEN 'Stock Request' WHEN fsr_type=1 THEN 'Inter Branch Transfer'  END AS fsr_type,"
                . "finascop_stock_request_status.fsrs_status AS status_name,"
                . "DATE_FORMAT(fsr_createdOn,'%d %M %Y') as fsr_createdOn,"
                . "fsr_initiatedBy,(SELECT br_Name FROM finascop_branch WHERE br_ID = fsr_initiatedBy) as initiatedBranch,"
                . "(SELECT br_Name FROM finascop_branch where br_ID = fsr_source) as sourcename,"
                . "(SELECT br_Name FROM finascop_branch where br_ID = fsr_destination) as branch  FROM finascop_stock_request INNER JOIN finascop_stock_request_status ON fsrs_id = finascop_stock_request.fsrs_status   {$search} {$where} ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'listInterBranchItemStore':
        $uuid = $_POST['uuid'];
        $fsr_id = $_POST['fsr_id'];
        $rec_sort = empty($data['sort']) ? 'fsr_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['fssr_id', 'fssr_SRNumber', 'fssr_createdOn', 'fssr_source', 'fssr_destination', 'fssr_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        if ($fsr_id > 0) {
            $cond = " AND fsr_id = {$fsr_id}";
        } else {
            if (!empty($uuid))
                $cond = " AND uuid = '{$uuid}'";
            else
                $cond = "AND uuid = 0";
        }
        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . "finascop_stock_request_details where {$filter_part} {$cond}";
        $listQuery = "SELECT uuid,fsr_id,fsrd_id,fsr_ItemId,fsr_RequiredItemQty,fsrd_status,(SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = fsr_ItemId) as fsr_ItemName,"
                . "fsr_ItemUnits,fsr_leastSKUCount,least_package_type_id,(select package_type_name from mypha_productpackage_type WHERE package_type_id = least_package_type_id) as leastSkuUnit,"
                . "(select package_type_name from mypha_productpackage_type WHERE package_type_id = fsr_ItemUnits) as unitName "
                . "from " . FINASCOP_DB . "finascop_stock_request_details WHERE {$filter_part} {$cond} ORDER BY $rec_sort $rec_sort_dir ";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'intrBranchTransferRequest':
        $db->query('begin');
        //$items = array();
        $fsr_ItemId = json_decode(stripslashes($_POST['fsr_ItemId']), true);
        $fsrd_id = json_decode(stripslashes($_POST['fsrd_id']), true);
        $fsr_ApprovedItemQty = json_decode(stripslashes($_POST['fsr_ApprovedItemQty']), true);
        $fsr_leastSKUCount = json_decode(stripslashes($_POST['fsr_leastSKUCount']), true);
        $fsr_ItemName = json_decode(stripslashes($_POST['fsr_ItemName']), true);
        $fsr_id = $_POST['fsr_id'];
        $subcategoryname = json_decode(stripslashes($_POST['subcategoryname']), true);
        $date = date('Y-m-d H:i:s');
        if ($fsr_id) {
            $order_det = $db->getFromDb("SELECT fsr_source,fsr_destination,fsr_type FROM finascop_stock_request WHERE fsr_id={$fsr_id}", true);


            $data['fstr_destination'] = $order_det['fsr_destination'];
            $data['fstr_source'] = $order_det['fsr_source'];
            $data['fstr_type'] = 4;
            $tdy = date("Y-m-d") . " 00:00:00";
            $maxId = $db->getItemFromDB("select right(fstr_uid,3)*1 as fstr_uid  from `finascop_stock_transfer_request` where `fstr_source` = {$order_det['fsr_source']} and `fstr_createdOn` between '{$tdy}' and '{$date}' order by `fstr_id` desc limit 1");
            $br_key = $db->getItemFromDB("SELECT br_key FROM finascop_branch WHERE br_ID={$order_det['fsr_source']}");
            $uid_max = getNewTRNumber($order_det['fsr_source']);
            $isDuplicate = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_request WHERE fstr_uid = '$uid_max'");
            if ($isDuplicate > 0) {
                do {
                    $maxId = substr($uid_max, -3);
                    $uid_max = getNewTRNumber($order_det['fsr_source']);
                    $entryDuplicate = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_request WHERE fstr_uid = '$uid_max'");
                } while ($entryDuplicate == 0);
            }
            $data['fstr_uid'] = $uid_max;
            $data['fstr_createdOn'] = $date;
            $data['fstr_initiatedBy'] = $_SESSION['admin']->finascop_current_branch_id;
            $data['fstr_updatedOn'] = $date;
            $data['fstr_updatedBy'] = $_SESSION['admin']->UserId;
            $data['fstr_createdBy'] = $_SESSION['admin']->UserId;
            $data['fstr_status'] = 1;

            $status = $db->perform('finascop_stock_transfer_request', $data);


            $lastId = $db->insert_id();


            if ($lastId) {
                for ($i = 0; $i < count($fsr_ItemId); $i++) {
                    $unique_id = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_request_details WHERE fsrd_id={$fsrd_id[$i]} AND fsrd_status=1");
                    if ($unique_id > 0) {
                        $requestDetails = $db->getFromDB("SELECT * FROM finascop_stock_request_details WHERE fsrd_id={$fsrd_id[$i]} AND fsrd_status=1", true);

                        $data_details['fstr_ItemId'] = $fsr_ItemId[$i];
                        $data_details['fstr_RequiredItemQty'] = $fsr_leastSKUCount[$i];
                        $data_details['fstr_ApprovedItemQty'] = $fsr_leastSKUCount[$i];
                        $data_details['fstr_id'] = $lastId;
                        $data_details['fstrd_status'] = 1;

                        $data_details['uuid'] = $uid_max;
                        $data_details['fstrd_createdBy'] = $_SESSION['admin']->UserId;
                        $data_details['fstrd_createdOn'] = $date;
                        $data_details['fstr_ItemUnits'] = $requestDetails['fsr_ItemUnits'];
                        $data_details['fstr_leastSKUCount'] = $requestDetails['fsr_leastSKUCount'];
                        $data_details['least_package_type_id'] = $requestDetails['least_package_type_id'];


                        $itemPriceDetails = $db->getFromDB("SELECT fpod_leastSKUmrp,fpod_leastSKUepr FROM finascop_stock_branch_inventory WHERE stit_id = {$fsr_ItemId[$i]} AND branch_id = {$order_det['fsr_destination']} limit 1", true);
                        if (count($itemPriceDetails) > 0) {
                            $data_details['fstr_ItemMRP'] = $itemPriceDetails['fpod_leastSKUmrp'];
                        }
                        $data_details = array_filter($data_details, 'strlen');

                        $status = $db->perform('finascop_stock_transfer_request_details', $data_details);

                        if ($status) {
                            $datas = array(
                                'fsrd_status' => 2
                            );
                            $status = $db->perform(FINASCOP_DB . 'finascop_stock_request_details', $datas, 'update', 'fsrd_id=' . $fsrd_id[$i]);
                            $total_count = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_request_details WHERE fsr_id={$fsr_id}");
                            $status_requested_count = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_request_details WHERE fsr_id={$fsr_id} AND fsrd_status=1");
                            if ($status_requested_count == 0) {
                                $datasr = array(
                                    'fstr_id' => $lastId,
                                    'fsrs_status' => 10
                                );
                                $status = $db->perform(FINASCOP_DB . 'finascop_stock_request', $datasr, 'update', 'fsr_id=' . $fsr_id);
                            } else if ($total_count > $status_requested_count) {
                                $data = array(
                                    'fstr_id' => $lastId,
                                    'fsrs_status' => 5
                                );
                                $qry = $db->perform(FINASCOP_DB . 'finascop_stock_request', $data, 'update', 'fsr_id=' . $fsr_id);
                            }
                        }
                    }
                }
            }
        } else {
            $msg = "Please select a request for transfer";
            echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
            exit();
        }
        $status = $db->query('commit');
        if ($status) {
            $msg = "Order transfered succesfully";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
}
