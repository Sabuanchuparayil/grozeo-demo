<?php

switch ($op) {
    case 'getBranch':
        switch ($_SESSION['admin']->br_PyramidLevel) {
            case 1:
                $cond = " AND br_PyramidLevel = 2";
                break;
            case 2:
                $cond = " AND br_PyramidLevel = 2";
                break;
            case 3:
                $cond = " AND br_PyramidLevel = 3";
                break;
        }

        $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch where br_status = 'Active' {$cond}", true);


        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'getBranchDestination':
        $br_cpd = $_POST['br_cpd'];
        if ($br_cpd > 0) {
            $cond = " AND br_cpd = {$br_cpd}";
        }
        $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch where br_status = 'Active' {$cond}", true);


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
            }
            $searchQuery .= " AND branch_id = {$_POST['sourceBranch']}";
        }

        //finascop_getjsonkeyarray("SELECT stit_ID,stit_itemName,stit_SKU FROM finascop_stock_itemmaster where 1=1 {$searchQuery} ");
        $qry = $db->getMulipleData("SELECT id,fsi.stit_ID as stit_ID,stit_itemName,CONCAT(stit_SKU,'-',fpod_leastSKUmrp)AS stit_SKU,cs_package_type_name,{$packageName} as packageName,fsbg_id,fpod_leastSKUmrp FROM finascop_stock_itemmaster fsi INNER JOIN finascop_stock_branch_inventory fsb ON fsb.stit_id=fsi.stit_ID  where 1=1 {$searchQuery}", true);
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
    case 'addItemstoTransferRequest':
        $uuid = $_POST['uuid'];
        $fstr_id = $_POST['fstr_id'];
        $fstrd['fstr_ItemId'] = $_POST['fstr_ItemId'];
        $fstrd['fstr_RequiredItemQty'] = $_POST['fstr_RequiredItemQty'];
        $fstrd['fstr_ApprovedItemQty'] = $_POST['fstr_RequiredItemQty'];
        $fstrd['fstr_ItemMRP'] = $_POST['fstr_ItemMRP'];
        
        $fstrd['fstr_ItemUnits'] = $_POST['fstr_StockItemUnits'];
        $fstrd['fstr_leastSKUCount'] = $_POST['leastSKUCountfstr'];
        $fstrd['least_package_type_id'] = $_POST['fstrleast_package_type_id'];
        $count = $db->getItemSafe("SELECT COUNT(*) FROM finascop_stock_transfer_request_details WHERE uuid = '{$uuid}' AND fstr_ItemId = ?", "i", [$_POST['fstr_ItemId']]);
        $db->query('begin');
        if ($count > 0) {
            $fstrd['fstrd_createdOn'] = date("Y-m-d H:i");
            $fstrd['fstrd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('finascop_stock_transfer_request_details', $fstrd, 'update', " uuid = '{$uuid}' AND fstr_ItemId = {$_POST['fstr_ItemId']}");
        } else {
            $fstrd['uuid'] = $uuid;
            $fstrd['fstrd_createdOn'] = date("Y-m-d H:i");
            $fstrd['fstrd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('finascop_stock_transfer_request_details', $fstrd);
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
    case 'listTransferRequestItemStore':
        $uuid = $_POST['uuid'];
        $fstr_id = $_POST['fstr_id'];
        $rec_sort = empty($data['sort']) ? 'fstr_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['fstr_id', 'fstr_TRNumber', 'fstr_createdOn', 'fstr_source', 'fstr_destination', 'fstr_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        if ($fstr_id > 0) {
            $cond = " AND fstr_id = {$fstr_id}";
        } else {
            if (!empty($uuid))
                $cond = " AND uuid = '{$uuid}'";
            else
                $cond = "AND uuid = 0";
        }
        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . "finascop_stock_transfer_request_details where {$filter_part} {$cond}";
        $listQuery = "SELECT uuid,fstr_id,fstrd_id,fstr_ItemId,fstr_RequiredItemQty,fstrd_status,(SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = fstr_ItemId) as fstr_ItemName,"
                . "fstr_ItemUnits,fstr_leastSKUCount,least_package_type_id,(select package_type_name from mypha_productpackage_type WHERE package_type_id = least_package_type_id) as leastSkuUnit,"
                . "(select package_type_name from mypha_productpackage_type WHERE package_type_id = fstr_ItemUnits) as unitName "
                . "from " . FINASCOP_DB . "finascop_stock_transfer_request_details WHERE {$filter_part} {$cond} ORDER BY $rec_sort $rec_sort_dir ";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'saveTransferRequest':
        $uuid = $_POST['uuid'];
        $fstr_id = $_POST['fstr_id'];
        $fstr['fstr_source'] = $_POST['fstr_source'];
        $fstr['fstr_destination'] = $_POST['fstr_destination'];
        $fstr['fstr_createdOn'] = date("Y-m-d H:i");
        $fstr['fstr_createdBy'] = $_SESSION['admin']->UserId;
        $fstr['fstr_initiatedBy'] = $_SESSION['admin']->finascop_current_branch_id;
        $db->query('begin');
        $date = date("Y-m-d H:i");
        $tdy = date("Y-m-d") . " 00:00";
        $maxId = $db->getItemSafe("select right(fstr_uid,3)*1 as fstr_uid  from `finascop_stock_transfer_request` where `fstr_source` = ? and `fstr_createdOn` between '{$tdy}' and '{$date}' order by `fstr_id` desc limit 1", "s", [$_POST['fstr_source']]);
        $br_key = $db->getItemSafe("SELECT br_key FROM finascop_branch WHERE br_ID=?", "s", [$_POST['fstr_source']]);
        $uid_max = 'TRQ' . date('ymd') . $br_key . str_pad(($maxId + 1), 3, '0', STR_PAD_LEFT);
        $fstr['fstr_uid'] = $uid_max;
        $status = $db->perform('finascop_stock_transfer_request', $fstr);
        $lastId = $db->insert_id();
        $fstrd['fstrd_status'] = 1;
        $fstrd['fstr_id'] = $lastId;
        $fstrd['fstrd_createdOn'] = date("Y-m-d H:i");
        $fstrd['fstrd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('finascop_stock_transfer_request_details', $fstrd, 'update', " uuid = '{$uuid}'");
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Items added to request.'";
            echo '{"success":true,"valid":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error occured while saving.'";
            echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
        }
        break;
    case 'listRetalineTransferRequest':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'fstr_id' : ($sort == 'fstr_createdOn' ? 'fstr_id' : $sort );
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status_name') {
                            if ($field['data']['value'] == 'Transfer Requested') {
                                //    $field['data']['value'] = 2;
                                $fiterItem = 1;
                                $search .= " and (fstr_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Partially Attended') {
                                //    $field['data']['value'] = 3;
                                $fiterItem = 5;
                                $search .= " and (fstr_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Completely Attended') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 10;
                                $search .= " and (fstr_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Invoke Expired') {
                                //    $field['data']['value'] = 3;
                                $fiterItem = 15;
                                $search .= " and (fstr_status = {$fiterItem}) ";
                            } else {
                                //    $field['data']['value'] = 0;
                                $search .= " and (fstr_status = 1 or fstr_status = 5 or fstr_status = 10 ) ";
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
            $where = " AND fstr_source =" . $br_ID;
        else
            $where = " AND fstr_source =" . $_SESSION['admin']->finascop_current_branch_id;
        $countQuery = "SELECT COUNT(*) FROM finascop_stock_transfer_request  INNER JOIN finascop_stock_transfer_request_status ON fstrs_id = fstr_status {$search} {$where}";

        $listQuery = "SELECT fstr_id,fstr_uid,fstr_source,fstr_destination,fstr_status,"
                . "CASE WHEN fstr_type=3 THEN 'Sales Order' WHEN fstr_type=1 THEN 'Transfer Invoked' WHEN fstr_status=2 THEN 'Stock Requested' ELSE 'Transfer Requested' END AS fstr_type,"
                . "fstrs_status AS status_name,"
                . "DATE_FORMAT(fstr_createdOn,'%d %M %Y') as fstr_createdOn,"
                . "fstr_initiatedBy,(SELECT br_Name FROM finascop_branch WHERE br_ID = fstr_initiatedBy) as initiatedBranch,"
                . "(SELECT br_Name FROM finascop_branch where br_ID = fstr_source) as sourcename,"
                . "(SELECT br_Name FROM finascop_branch where br_ID = fstr_destination) as branch  FROM finascop_stock_transfer_request INNER JOIN finascop_stock_transfer_request_status ON fstrs_id = fstr_status   {$search} {$where} ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'getBranchName':
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch WHERE br_status = 'Active' AND (br_PyramidLevel = 2  OR br_ID={$branch_id})", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'listCreateTransferOrderData':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'fstrd_id' : $sort;
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
                                $search .= " and (fstrd_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Ordered') {
                                //    $field['data']['value'] = 3;
                                $fiterItem = 2;
                                $search .= " and (fstrd_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Deleted') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 3;
                                $search .= " and (fstrd_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Packed') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 4;
                                $search .= " and (fstrd_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Partially Received') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 5;
                                $search .= " and (fstrd_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Received') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 6;
                                $search .= " and (fstrd_status = {$fiterItem}) ";
                            } else {
                                //    $field['data']['value'] = 0;
                                $search .= " and (fstrd_status = 1 or fstrd_status = 2 or fstrd_status = 3 or fstrd_status = 4 or fstrd_status = 5 or fstrd_status = 6) ";
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

        $countQry = "SELECT COUNT(*) FROM finascop_stock_transfer_request_details  {$search} AND fstr_id = " . intval($_POST['fstr_id']) . "";

        $listQry = "SELECT fstr_id,fstrd_id,fstr_ItemId,fstr_ApprovedItemQty,fstr_RequiredItemQty,fstrd_status,fstr_ItemUnits,fstr_leastSKUCount,least_package_type_id,"
                . "CASE WHEN fstrd_status=1 THEN 'Requested' WHEN fstrd_status=2 THEN 'Ordered' WHEN fstrd_status=3 THEN 'Deleted' WHEN fstrd_status=4 THEN 'Packed' "
                . "WHEN fstrd_status=5 THEN 'Partially Received' WHEN fstrd_status=6 THEN 'Received' ELSE 'Not Saved' END AS status_name FROM finascop_stock_transfer_request_details {$search} AND fstr_id ={$_POST['fstr_id']} ORDER BY {$sort} {$dir} ";
        $datas = $db->getMulipleData($listQry, true);
        $fstr_source = $db->getItemSafe("SELECT fstr_source FROM finascop_stock_transfer_request WHERE fstr_id = ?", "i", [$_POST['fstr_id']]);
        $sourcePyramid = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch where br_ID = {$fstr_source}");
        $resCount = count($datas);
        $count = $db->getItemFromDB($countQry);
        //print_r($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $datas[$i]['fstr_ItemName'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = {$datas[$i]['fstr_ItemId']}");
                $packageType = $db->getFromDB("SELECT isMedicine,cs_package_type_name,ds_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['fstr_ItemId']}", true);
                if ($sourcePyramid == 2) {
                    $packTyp = $packageType['cs_package_type_name'];
                } else if ($sourcePyramid == 3) {
                    $packTyp = $packageType['ds_package_type_name'];
                } else if ($sourcePyramid == 4) {
                    $packTyp = $packageType['ds_package_type_name'];
                }
                if ($packageType['isMedicine'] == 0) {
                    $product_category = $db->getItemFromDB("SELECT product_category FROM finascop_stock_itemmaster where stit_ID = {$datas[$i]['fstr_ItemId']}");
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
                    $product_category = $db->getItemFromDB("SELECT product_category FROM finascop_stock_itemmaster where stit_ID = {$datas[$i]['fstr_ItemId']}");
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


                $datas[$i]['packageType'] = $db->getItemFromDB("(select package_type_name from mypha_productpackage_type WHERE package_type_id = {$datas[$i]['fstr_ItemUnits']})");
                $datas[$i]['least_package_type'] = $db->getItemFromDB("(select package_type_name from mypha_productpackage_type WHERE package_type_id = {$datas[$i]['least_package_type_id']})");
            }
        }
        echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        break;
    case 'removeEnquiry':
        $id = $_POST['fstrd_id'];
        $fstr_id = $_POST['fstr_id'];
        $data = array(
            'fstrd_status' => 3
        );
        $db->query('begin');
        $qry = $db->perform(FINASCOP_DB . 'finascop_stock_transfer_request_details', $data, 'update', 'fstrd_id=' . $id);
        if ($qry) {
            $total_count = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_request_details WHERE fstr_id={$fstr_id}");
            $status_requested_count = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_request_details WHERE fstr_id={$fstr_id} AND fstrd_status=1");
            if ($status_requested_count == 0) {
                $data = array(
                    'fstr_status' => 10
                );
                $qry = $db->perform(FINASCOP_DB . 'finascop_stock_transfer_request', $data, 'update', 'fstr_id=' . $fstr_id);

//                }
            } else if ($total_count > $status_requested_count) {
                $data = array(
                    'fstr_status' => 5
                );
                $qry = $db->perform(FINASCOP_DB . 'finascop_stock_transfer_request', $data, 'update', 'fstr_id=' . $fstr_id);
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
        $id = $_POST['fstr_id'];
        //$status = $_POST['activestatus'];
        $data = array(
            'fstr_status' => 6
        );
        $db->query('begin');
        $qry = $db->perform(FINASCOP_DB . 'finascop_stock_transfer_request', $data, 'update', 'fstr_id=' . $id);
        if ($qry) {
            $datas = array(
                'fstrd_status' => 3
            );
            $qrys = $db->perform(FINASCOP_DB . 'finascop_stock_transfer_request_details', $datas, 'update', 'fstr_id=' . $id);
        }
        $status = $db->query('commit');
        if ($status) {

            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'deleteItem':
        $id = $_POST['fstrd_id'];
        $del_query = "DELETE FROM finascop_stock_transfer_request_details WHERE fstrd_id=" . $id;
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
        
        
        $fstr_StockItemUnits = $griddata['fstr_StockItemUnits'];
        $itemId = $griddata['fstr_ItemId'];
        $packTypes = $db->getFromDB("SELECT stdpckl11_package_type_id,stdpckl21_package_type_id,stdpckl31_package_type_id,stdpckl41_package_type_id,least_package_type_name,least_package_type_id,stdpckl1_nos,stdpckl2_nos,"
                . "stdpckl3_nos FROM finascop_stock_itemmaster WHERE stit_ID = {$itemId}", true);
        //print_r($packTypes);
        if ($fstr_StockItemUnits == $packTypes['stdpckl12_package_type_id']) {
            $level = '4';
            $fpot_leastSKUqty = $fstr_RequiredItemQty / $packTypes['stdpckl1_nos'];
        } else if ($fstr_StockItemUnits == $packTypes['stdpckl11_package_type_id']) {
            $level = '3';
            $fpot_leastSKUqty = $fstr_RequiredItemQty;
        } else if ($fstr_StockItemUnits == $packTypes['stdpckl21_package_type_id']) {
            $level = '2';
            $fpot_leastSKUqty = $fstr_RequiredItemQty * $packTypes['stdpckl2_nos'];
        } else if ($fstr_StockItemUnits == $packTypes['stdpckl31_package_type_id']) {
            $level = '1';
            $fpot_leastSKUqty = $fstr_RequiredItemQty * $packTypes['stdpckl2_nos'] * $packTypes['stdpckl3_nos'];
        }
        
        $db->query('begin');
        $lpupDatat['least_package_type_id'] = $packTypes['least_package_type_id'];
        $lpupDatat['fstr_leastSKUCount'] = $fpot_leastSKUqty;
        $lpupDatat['fstr_ApprovedItemQty'] = $griddata['fstr_ApprovedItemQty'];
        $lpupDatat['fstrd_updatedOn'] = date("Y-m-d H:i:s");
        $lpupDatat['fstrd_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('finascop_stock_transfer_request_details', $lpupDatat, 'update', " fstrd_id = {$griddata['fstrd_id']}");
        $status = $db->query('commit');
        if ($status) {
            $msg = "Quantity Updated Successfully";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'createTransferOrder':
        $db->query('begin');
        //$items = array();
        $fstr_ItemId = json_decode(stripslashes($_POST['fstr_ItemId']), true);
        $fstrd_id = json_decode(stripslashes($_POST['fstrd_id']), true);
        $fstr_ApprovedItemQty = json_decode(stripslashes($_POST['fstr_ApprovedItemQty']), true);
        $fstr_leastSKUCount = json_decode(stripslashes($_POST['fstr_leastSKUCount']), true);
        $fstr_ItemName = json_decode(stripslashes($_POST['fstr_ItemName']), true);
        $fstr_id = $_POST['fstr_id'];
        $subcategoryname = json_decode(stripslashes($_POST['subcategoryname']), true);
        $date = date('Y-m-d H:i:s');
        if ($fstr_id) {
            $order_det = $db->getFromDb("SELECT fstr_source,fstr_destination,fstr_type FROM finascop_stock_transfer_request WHERE fstr_id={$fstr_id}", true);
            for ($i = 0; $i < count($fstr_ItemId); $i++) {
                $items = $db->getFromDb("SELECT item_weight,stit_item_volume,stit_GST,cos_nos,ds_nos,cs_nos FROM finascop_stock_itemmaster where stit_ID = {$fstr_ItemId[$i]}", true);

                $itemStockCount = $db->getItemFromDB("SELECT item_count FROM finascop_stock_branch_inventory WHERE stit_id = {$fstr_ItemId[$i]} AND branch_id = {$order_det['fstr_source']}", true);
                if ($fstr_ApprovedItemQty[$i] > $itemStockCount) {
                    $itemSku = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$fstr_ItemId[$i]}");
                    $msg = "Inventory count for {$itemSku} is {$itemStockCount} only.";
                    echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
                    exit();
                }
            }
//            $unique_fstrID = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order WHERE fstr_id={$fstr_id}");
//            if ($unique_fstrID == 0) {
            $data['fstr_id'] = $fstr_id;
//            $maxFstoid = $db->getItemFromDB("SELECT MAX(fsto_id) FROM finascop_stock_transfer_order ");
//            $maxFstId = date('Ymd') . ($maxFstoid + 1);
//
//            $data['fsto_uid'] = $maxFstId;

            $data['fsto_source'] = $order_det['fstr_source'];
            $data['fsto_destination'] = $order_det['fstr_destination'];
            $data['fsto_sourcetype'] = 1;
            $data['fsto_destinationtype'] = 1;
            $tdy = date("Y-m-d") . " 00:00:00";
            $maxId = $db->getItemFromDB("select right(fsto_uid,3)*1 as fsto_uid  from `finascop_stock_transfer_order` where `fsto_source` = {$order_det['fstr_source']} and `fsto_createdOn` between '{$tdy}' and '{$date}' order by `fsto_id` desc limit 1");
            $br_key = $db->getItemFromDB("SELECT br_key FROM finascop_branch WHERE br_ID={$order_det['fstr_source']}");
            $uid_max = 'TOR' . date('ymd') . $br_key . str_pad(($maxId + 1), 3, '0', STR_PAD_LEFT);
            $data['fsto_uid'] = $uid_max;
            $data['fsto_type'] = $order_det['fstr_type'];
            $data['fsto_createdOn'] = $date;
            $data['fsto_initiatedBy'] = $_SESSION['admin']->finascop_current_branch_id;
            $data['fsto_updateon'] = $date;
            $data['fsto_updateby'] = $_SESSION['admin']->UserId;
            $data['fsto_createdBy'] = $_SESSION['admin']->UserId;
            $data['fsto_status'] = 6;

            $status = $db->perform('finascop_stock_transfer_order', $data);
            $lastId = $db->insert_id();
//            } else {
//                $lastId = $db->getItemFromDB("SELECT fsto_id FROM finascop_stock_transfer_order WHERE fstr_id={$fstr_id}");
//            }
            $fsto['fsto_cgstval'] = 0;
            $fsto['fsto_sgstval'] = 0;
            $fsto['fsto_amtbeforetax'] = 0;
            $fsto['fsto_amtaftertax'] = 0;
            $fsto['fsto_netamount'] = 0;

            if ($lastId) {
                for ($i = 0; $i < count($fstr_ItemId); $i++) {
                    $unique_id = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_request_details WHERE fstrd_id={$fstrd_id[$i]} AND fstrd_status=1");
                    if ($unique_id > 0) {
                        $fstr_ItemMRP = $db->getItemFromDB("SELECT fstr_ItemMRP FROM finascop_stock_transfer_request_details WHERE fstrd_id={$fstrd_id[$i]}");
                        $data_details['fsto_ItemId'] = $fstr_ItemId[$i];
                        //$data_details['fsto_ItemQty'] = $fstr_ApprovedItemQty[$i];
                        $data_details['fsto_ItemQty'] = $fstr_leastSKUCount[$i];
////

                        $data_details['fsto_ItemWeight'] = round(($items['item_weight'] * $fstr_leastSKUCount[$i]), 3); //r 3
                        $data_details['fsto_ItemVolume'] = round(($items['stit_item_volume'] * $fstr_leastSKUCount[$i]), 3);
                        $data_details['fsto_uid'] = $db->getItemFromDB("SELECT fsto_uid FROM finascop_stock_transfer_order WHERE fstr_id={$fstr_id}");
                        $data_details['fsto_id'] = $lastId;
                        $data_details['fstro_createdBy'] = $_SESSION['admin']->UserId;
                        $data_details['fstro_createdOn'] = $date;
                        $data_details['fstro_ItemMRP'] = $fstr_ItemMRP;

                        $itemPriceDetails = $db->getFromDB("SELECT mrp,selling_price,fpod_poLandingCostleastSKU FROM finascop_stock_branch_inventory WHERE stit_id = {$fstr_ItemId[$i]} AND branch_id = {$order_det['fstr_source']} and fpod_leastSKUmrp = {$fstr_ItemMRP}", true);
                        if (count($itemPriceDetails) > 0) {
                            
//                            if ($_SESSION['admin']->br_PyramidLevel == 2) {
//                                $data_details['fstro_ItemSPincTax'] = $itemPriceDetails['fpod_poLandingCostleastSKU'] * $items['cos_nos'] * $items['ds_nos'];
//                            } else if ($_SESSION['admin']->br_PyramidLevel == 3) {
//                                $data_details['fstro_ItemSPincTax'] = $itemPriceDetails['fpod_poLandingCostleastSKU'] * $items['ds_nos'];
//                            }
                            $data_details['fstro_ItemSPincTax'] = $itemPriceDetails['fpod_poLandingCostleastSKU'] ;

                            $data_details['fstro_gst_value'] = $items['stit_GST'];
                            $amtbtax = round(($data_details['fstro_ItemSPincTax'] / (100 + $items['stit_GST'])) * 100, 2);
                            $data_details['fstro_cgst_percent'] = round(($items['stit_GST'] / 2), 2);
                            $data_details['fstro_sgst_percent'] = round(($items['stit_GST'] / 2), 2);
                            $data_details['fstro_cgst_value'] = $amtbtax * $fstr_leastSKUCount[$i] * ($data_details['fstro_cgst_percent'] / 100);  //amtbtax * qty * (cgst/100)
                            $data_details['fstro_sgst_value'] = $amtbtax * $fstr_leastSKUCount[$i] * ($data_details['fstro_sgst_percent'] / 100); //amtbtax * qty * sgst
                            $data_details['fstro_totamtbeforetax'] = $amtbtax * $fstr_leastSKUCount[$i];
                            $data_details['fstro_totamtaftertax'] = $data_details['fstro_ItemSPincTax'] * $fstr_leastSKUCount[$i];
                            $data_details['fstro_kfc_percent'] = 0;
                            $data_details['fstro_kfc_value'] = 1;


                            //fsto_cgstval,fsto_sgstval,fsto_amtbeforetax,fsto_amtaftertax,fsto_netamount
                        }
                        $data_details = array_filter($data_details, 'strlen');

                        $status = $db->perform('finascop_stock_transfer_order_details', $data_details);

                        if ($status) {
                            $datas = array(
                                'fstrd_status' => 2
                            );
                            $status = $db->perform(FINASCOP_DB . 'finascop_stock_transfer_request_details', $datas, 'update', 'fstrd_id=' . $fstrd_id[$i]);
                            $total_count = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_request_details WHERE fstr_id={$fstr_id}");
                            $status_requested_count = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_request_details WHERE fstr_id={$fstr_id} AND fstrd_status=1");
                            if ($status_requested_count == 0) {
//                                $datas = array(
//                                    'fstrd_status' => 2
//                                );
                                $datasr = array(
                                    'fstr_status' => 10
                                );
//                                $status = $db->perform(FINASCOP_DB . 'finascop_stock_transfer_request_details', $datas, 'update', 'fstrd_id=' . $fstrd_id[$i]);
                                $status = $db->perform(FINASCOP_DB . 'finascop_stock_transfer_request', $datasr, 'update', 'fstr_id=' . $fstr_id);
                            } else if ($total_count > $status_requested_count) {
                                $data = array(
                                    'fstr_status' => 5
                                );
                                $qry = $db->perform(FINASCOP_DB . 'finascop_stock_transfer_request', $data, 'update', 'fstr_id=' . $fstr_id);
                            }
                        }

                        ///
                    }
                }
            }

            $staoDetail = $db->getFromDB("SELECT SUM(fstro_cgst_value) AS totalCGST,SUM(fstro_sgst_value) AS totalSGST,SUM(fstro_totamtbeforetax) AS tatalAmtBT,SUM(fstro_totamtaftertax) AS tatalAmt "
                    . "FROM finascop_stock_transfer_order_details WHERE fsto_id = {$lastId}", true);
            $fsto['fsto_cgstval'] = $staoDetail['totalCGST'];
            $fsto['fsto_sgstval'] = $staoDetail['totalSGST'];
            $fsto['fsto_amtbeforetax'] = $staoDetail['tatalAmtBT'];
            $fsto['fsto_amtaftertax'] = $staoDetail['tatalAmt'];
            $fsto['fsto_netamount'] = $staoDetail['tatalAmt'];

            $status = $db->perform('finascop_stock_transfer_order', $fsto, 'update', " fsto_id = {$lastId}");


//            for ($i = 0; $i < count($fstr_id); $i++) {
//                if ($fstr_ApprovedItemQty[$i] == 0) {
//                    $msg = "Please add quantity for transfer order";
//                    echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
//                    exit();
//                }
//                $unique_id = $db->getItemFromDb("SELECT COUNT(*) FROM finascop_stock_transfer_request_details WHERE fstrd_id={$fstrd_id[$i]} AND fstrd_status=1");
//                if ($unique_id > 0) {
//                    $data['fstr_id'] = $fstr_id[$i];
//                    $maxFstoid = $db->getItemFromDB("SELECT MAX(fsto_id) FROM finascop_stock_transfer_order ");
//                    $maxFstId = date('Ymd') . ($maxFstoid + 1);
//                    $data['fsto_uid'] = $maxFstId;
//                    $order_det = $db->getFromDb("SELECT fstr_source,fstr_destination,fstr_type FROM finascop_stock_transfer_request WHERE fstr_id={$fstr_id[$i]}", true);
//                    $data['fsto_source'] = $order_det['fstr_source'];
//                    $data['fsto_destination'] = $order_det['fstr_destination'];
//                    $data['fsto_sourcetype'] = 1;
//                    $data['fsto_destinationtype'] = 1;
//                    $data['fsto_type'] = $order_det['fstr_type'];
//                    $data['fsto_createdOn'] = $date;
//
//                    $status = $db->perform('finascop_stock_transfer_order', $data);
//                    $lastId = $db->insert_id();
//                    if ($lastId) {
//                        $data_details['fsto_ItemId'] = $fstr_ItemId[$i];
//                        $data_details['fsto_ItemQty'] = $fstr_ApprovedItemQty[$i];
//
//                        $items = $db->getFromDb("SELECT item_weight,stit_item_volume FROM finascop_stock_itemmaster where stit_itemId = {$fstr_ItemId[$i]}", true);
//                        $data_details['fsto_ItemWeight'] = $items['item_weight'] * $fstr_ApprovedItemQty[$i];
//                        $data_details['fsto_ItemVolume'] = $items['stit_item_volume'] * $fstr_ApprovedItemQty[$i];
//                        $data_details['fsto_uid'] = $maxFstId;
//                        $data_details['fsto_id'] = $lastId;
//                        $data_details['fstro_createdBy'] = $_SESSION['admin']->Finascop_UserId;
//                        $data_details['fstro_createdOn'] = $date;
//                        $status = $db->perform('finascop_stock_transfer_order_details', $data_details);
//
//                        if ($status) {
//                            $datas = array(
//                                'fstrd_status' => 2
//                            );
//                            $status = $db->perform(FINASCOP_DB . 'finascop_stock_transfer_request_details', $datas, 'update', 'fstrd_id=' . $fstrd_id[$i]);
//                            $total_count = $db->getItemFromDb("SELECT COUNT(*) FROM finascop_stock_transfer_request_details WHERE fstrd_id={$fstrd_id[$i]}");
//                            $status_requested_count = $db->getItemFromDb("SELECT COUNT(*) FROM finascop_stock_transfer_request_details WHERE fstr_id={$fstr_id[$i]} AND fstrd_status=1");
//                            if ($status_requested_count == 0) {
////                                $datas = array(
////                                    'fstrd_status' => 2
////                                );
//                                $datasr = array(
//                                    'fstr_status' => 10
//                                );
////                                $status = $db->perform(FINASCOP_DB . 'finascop_stock_transfer_request_details', $datas, 'update', 'fstrd_id=' . $fstrd_id[$i]);
//                                $status = $db->perform(FINASCOP_DB . 'finascop_stock_transfer_request', $datasr, 'update', 'fstr_id=' . $fstr_id[$i]);
//                            } else if ($total_count > $status_requested_count) {
//                                $data = array(
//                                    'fstr_status' => 5
//                                );
//                                $qry = $db->perform(FINASCOP_DB . 'finascop_stock_transfer_request', $data, 'update', 'fstr_id=' . $fstr_id);
//                            }
//                        }
//                    }
//                }
//            }
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
        $fstr_StockItemUnits = $_POST['fstr_StockItemUnits'];
        $itemId = $_POST['itemId'];
        $fstr_RequiredItemQty = $_POST['fstr_RequiredItemQty'];
        $packTypes = $db->getFromDB("SELECT stdpckl11_package_type_id,stdpckl21_package_type_id,stdpckl31_package_type_id,stdpckl41_package_type_id,least_package_type_name,least_package_type_id,stdpckl1_nos,stdpckl2_nos,stdpckl3_nos FROM finascop_stock_itemmaster WHERE stit_ID = {$itemId}", true);
        //print_r($packTypes);
        if ($fstr_StockItemUnits == $packTypes['stdpckl12_package_type_id']) {
            $level = '4';
            $fpot_leastSKUqty = $fstr_RequiredItemQty / $packTypes['stdpckl1_nos'];
        } else if ($fstr_StockItemUnits == $packTypes['stdpckl11_package_type_id']) {
            $level = '3';
            $fpot_leastSKUqty = $fstr_RequiredItemQty;
        } else if ($fstr_StockItemUnits == $packTypes['stdpckl21_package_type_id']) {
            $level = '2';
            $fpot_leastSKUqty = $fstr_RequiredItemQty * $packTypes['stdpckl2_nos'];
        } else if ($fstr_StockItemUnits == $packTypes['stdpckl31_package_type_id']) {
            $level = '1';
            $fpot_leastSKUqty = $fstr_RequiredItemQty * $packTypes['stdpckl2_nos'] * $packTypes['stdpckl3_nos'];
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
}
