<?php

switch ($op) {
    case 'listfinascopStockUpload':
        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'fbiu_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
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
                    $searchitem .= " and ({$field[field]} LIKE '%{$field['data']['value']}%') ";
                }
            }
        }
        if ($_SESSION['admin']->current_branch_iscpd == 1) {
            $br_ID = $_POST['branchName'];
        } else {
            $br_ID = $_SESSION['admin']->finascop_current_branch_id;
        }
        if ($rec_sort == 'fsu_date') {
            $rec_sort = 'fbiu_id';
        }


        $countQuery = "SELECT COUNT(1) from finascop_stock_branch_inventory_upload {$search} AND fbiu_branch = {$br_ID} {$searchitem}";
        $count = $db->getItemFromDB($countQuery);
        //$count = $db->getItemFromDB($countQuery);
        $listQuery = " SELECT fbiu_id,fbiu_branch,if(fbiu_status=0,'Not Saved','Saved') as fbiu_status,fbiu_createdOn,if(fbiu_uploadedbyapi = 0,'From Admin','From API') as fbiu_uploadedbyapi,"
                . "DATE_FORMAT(fbiu_createdOn, '%d-%m-%Y') as fsu_date  from finascop_stock_branch_inventory_upload {$search} AND fbiu_branch = {$br_ID} {$searchitem} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $datas[$i]['branch_name'] = $db->getItemFromDB("SELECT br_Name FROM finascop_branch WHERE br_ID = {$datas[$i]['fbiu_branch']} ");
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';

// $db->printGridJson($countQuery, $listQuery);
        break;
    case 'uploadStockcsvFile':
        $file = $_FILES['excel_file']['tmp_name'];
        $newPath = str_replace('tmp', 'dev/shm', $file);
        copy($file, $newPath);
        $row = 0;
        $csvData = array();
        if (($handle = fopen($newPath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                $csvData[$row] = $data;

                $num = count($data);
                $row++;
            }
            fclose($handle);
        }
        $db->query('begin');
        if ($_SESSION['admin']->current_branch_iscpd == 1) {
            $br_ID = $_POST['branch'];
        } else {
            $br_ID = $_SESSION['admin']->finascop_current_branch_id;
        }
        $fsbiu['fbiu_branch'] = $br_ID;
        $fsbiu['fbiu_createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $fsbiu['fbiu_createdOn'] = date("Y-m-d H:i:s");
        // print_r($fsbiu);
        $status = $db->perform('finascop_stock_branch_inventory_upload', $fsbiu);
        $lastId = $db->insert_id();
        $invalidErps = [];
        $updateCount = 0;
        foreach ($csvData as $key => $value) {
            if ($key == 0) {
                $col0 = 'erpId';
                $col2 = 'Qty';
                $col3 = 'MRP';
                $col4 = 'selling_price';
                if (($value[0] != $col0) || ($value[1] != $col2) || ($value[2] != $col3) || ($value[3] != $col4)) {
                    echo '{"success":true,"valid":false,"error":"Valid column names are erpId,Qty,MRP,selling_price."}';
                    exit();
                }
            } else {
                $stit_id = $db->getItemFromDB("SELECT fsipc_stit_id FROM finascop_stock_itemmaster_product_code_stores WHERE  fsipcs_Code = '{$value[0]}' AND fsipcs_store = {$br_ID}");
                if ($stit_id == 0) {
                    $br_storeGroup = $db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$br_ID}");

                    $stit_id = $db->getItemFromDB("SELECT fsipc_stit_id FROM finascop_stock_itemmaster_product_codes WHERE   fsipc_code = '{$value[0]}'  AND fsipc_storeGroup = {$br_storeGroup}  AND fsipc_isIndividual =0 AND fsipc_isCompany = 0");
                    if ($stit_id == 0) {
                        $stit_id = $db->getItemFromDB("SELECT fsipc_stit_id FROM finascop_stock_itemmaster_product_codes WHERE  fsipc_code = '{$value[0]}'   AND fsipc_isCompany = 1");
                    }
                }

                if ($stit_id > 0) {
                    $fbiud['fbiu_id'] = $lastId;
                    $fbiud['stit_id'] = $stit_id;
                    $fbiud['branch_id'] = $br_ID;
                    if ((floatval($value[1]) <= 0) || (floatval($value[2]) <= 0) || (floatval($value[3]) <= 0)) {
                        $fbiud['item_count'] = 0;
                        $fbiud['mrp'] = 0;
                        $fbiud['selling_price'] = 0;
                    } else {
                        $fbiud['item_count'] = floor($value[1]);
                        $fbiud['mrp'] = floatval($value[2]);
                        $fbiud['selling_price'] = floatval($value[3]);
                    }


                    $status = $db->perform('finascop_stock_branch_inventory_upload_detail', $fbiud);
                    $updateCount ++;
                } else {
                    array_push($invalidErps, $value[0]);
//                    echo '{"success":true,"valid":false,"error":"ERP Id ' . $value[0] . ' doesnot exist"}';
//                    exit();
                }
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            if (count($invalidErps) > 0) {
                $erpIds = implode(',', $invalidErps);
                $dispMsg = "Following ERP Ids does not exists - {$erpIds},  Going to save {$updateCount} Items.";
            } else {
                $dispMsg = "Going to save {$updateCount} Items";
            }

            echo '{"success":true,"valid":true,"msg":"' . $dispMsg . '","fbiu_id":' . $lastId . '}';
        } else {
            // var_dup($error);
            echo '{"success":false,"valid":false}';
        }
        break;
    case 'listStockUploadedItems':
        $rec_sort = empty($data['sort']) ? 'id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' AND 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(*) from finascop_stock_branch_inventory_upload_detail WHERE fbiu_id = '{$_POST['fbiu_id']}' {$filter_part}";
        $listQuery = "SELECT fbiu_id,stit_id,branch_id,item_count,mrp,selling_price from finascop_stock_branch_inventory_upload_detail "
                . "WHERE fbiu_id = '{$_POST['fbiu_id']}' {$filter_part}  ORDER BY CAST({$rec_sort} as char) {$rec_sort_dir},binary {$rec_sort} {$rec_sort_dir}  ";
        //$db->printGridJson($countQuery, $listQuery);
        $count = $db->getItemFromDB($countQuery);
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $datas[$i]['stit_sku'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['stit_id']}");
                $fsipcs_Code = $db->getItemFromDB("SELECT fsipcs_Code FROM finascop_stock_itemmaster_product_code_stores WHERE  fsipc_stit_id = {$datas[$i]['stit_id']} AND fsipcs_store = {$datas[$i]['branch_id']}");
                if (empty($fsipcs_Code)) {
                    $br_storeGroup = $db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$datas[$i]['branch_id']}");

                    $fsipcs_Code = $db->getItemFromDB("SELECT fsipc_code  FROM finascop_stock_itemmaster_product_codes WHERE   fsipc_stit_id = {$datas[$i]['stit_id']}  AND fsipc_storeGroup = {$br_storeGroup}  AND fsipc_isIndividual =0 AND fsipc_isCompany = 0");
                    if (empty($fsipcs_Code)) {
                        $fsipcs_Code = $db->getItemFromDB("SELECT fsipc_code FROM finascop_stock_itemmaster_product_codes WHERE  fsipc_stit_id  = '{$datas[$i]['stit_id']}'   AND fsipc_isCompany = 1");
                    }
                }
                //$datas[$i]['stit_itemERPId'] = $db->getItemFromDB("SELECT stit_itemERPId FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['stit_id']}");
                $datas[$i]['stit_itemERPId'] = $fsipcs_Code;
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
            //echo json_encode($qry);
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        break;
    case 'confirmStockUpload':
        $fbiu_id = $_POST['fbiu_id'];
        $db->query('begin');

        $fsbiu['fbiu_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $fsbiu['fbiu_updatedOn'] = date("Y-m-d H:i:s");
        if ($_SESSION['admin']->br_PyramidLevel == 1) {
            $br_ID = $_POST['branchName'];
        } else {
            $br_ID = $_SESSION['admin']->finascop_current_branch_id;
        }
        $db->query("UPDATE finascop_stock_branch_inventory SET item_count = 0 WHERE branch_id = {$br_ID}");
        $fsbiudetails = $db->getMulipleData("SELECT fbiu_id,stit_id,branch_id,item_count,mrp,selling_price,fsbg_id from finascop_stock_branch_inventory_upload_detail WHERE fbiu_id = {$fbiu_id}", true);
        $fsbiudetailsCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory_upload_detail WHERE fbiu_id = {$fbiu_id}");
        $bmdDetails = $db->getFromDB("SELECT * FROM retaline_margindistributions WHERE is_default = 1", true);
        if ($fsbiudetailsCount > 0) {
            foreach ($fsbiudetails as $fsbiudetail) {
//            echo "SELECT id FROM finascop_stock_branch_inventory WHERE stit_id = {$fsbiudetail['stit_id']} AND branch_id = {$fsbiudetail['branch_id']} AND fsbg_id = {$fsbiudetail['fsbg_id']}";
                $fsbiCount = $db->getItemFromDb("SELECT id FROM finascop_stock_branch_inventory WHERE stit_id = {$fsbiudetail['stit_id']} AND branch_id = {$fsbiudetail['branch_id']} AND fsbg_id = {$fsbiudetail['fsbg_id']}");
                $itemLandingCost = $fsbiudetail['selling_price'];
                $itemMMG = round(($fsbiudetail['mrp'] - $itemLandingCost), 2);
                $stit_GST = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$fsbiudetail['stit_id']}");
                $least_package_type_id = $db->getItemFromDB("SELECT least_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$fsbiudetail['stit_id']}");

                $fpod_spHmDel = $itemLandingCost + (($itemMMG * $bmdDetails['bmd_company'] / 100) + ($itemMMG * $bmdDetails['bmd_incentive'] / 100) + ($itemMMG * $bmdDetails['bmd_cs'] / 100) + ($itemMMG * $bmdDetails['bmd_distributor'] / 100) + ($itemMMG * $bmdDetails['bmd_retailor'] / 100) + ($itemMMG * $bmdDetails['bmd_driver'] / 100));
                $fpoddata['fcpod_spHmDel'] = round($fpod_spHmDel, 2);
                $fpod_spCouDel = $itemLandingCost + (($itemMMG * $bmdDetails['bmd_company'] / 100) + ($itemMMG * $bmdDetails['bmd_incentive'] / 100) + ($itemMMG * $bmdDetails['bmd_cs'] / 100) + ($itemMMG * $bmdDetails['bmd_distributor'] / 100) + ($itemMMG * $bmdDetails['bmd_retailor'] / 100) + ($itemMMG * $bmdDetails['bmd_courier'] / 100));
                $fpoddata['fcpod_spCouDel'] = round($fpod_spCouDel, 2);
                $fpod_spPikup = $itemLandingCost + (($itemMMG * $bmdDetails['bmd_company'] / 100) + ($itemMMG * $bmdDetails['bmd_incentive'] / 100) + ($itemMMG * $bmdDetails['bmd_cs'] / 100) + ($itemMMG * $bmdDetails['bmd_distributor'] / 100) + ($itemMMG * $bmdDetails['bmd_retailor'] / 100));
                $fpoddata['fcpod_spPikup'] = round($fpod_spPikup, 2);

                $fpod_spetHmDel = ($fpoddata['fcpod_spHmDel'] * 100) / (100 + $stit_GST);
                $fpoddata['fcpod_spetHmDel'] = round($fpod_spetHmDel, 2);
                $fpod_spetCouDel = ($fpoddata['fcpod_spCouDel'] * 100) / (100 + $stit_GST);
                $fpoddata['fcpod_spetCouDel'] = round($fpod_spetCouDel, 2);
                $fpod_spetPikup = ($fpoddata['fcpod_spPikup'] * 100) / (100 + $stit_GST);
                $fpoddata['fcpod_spetPikup'] = round($fpod_spetPikup, 2);

                if ($fsbiCount > 0) {
                    $fsbu['mrp'] = $fsbiudetail['mrp'];
                    $fsbu['selling_price'] = $fsbiudetail['selling_price'];
                    $fsbu['item_count'] = $fsbiudetail['item_count'];
                    $fsbu['updated_on'] = date("Y-m-d H:i:s");
                    $fsbu['fpod_poLandingCostleastSKU'] = $itemLandingCost;
                    $fsbu['fpod_poMMGleastSKU'] = $itemMMG;
                    $fsbu['fpod_leastSKUmrp'] = $fsbiudetail['mrp'];
                    $fsbu['purchasing_unit'] = $least_package_type_id;
                    $fsbu['fpod_customerRateHmDel'] = $fpoddata['fcpod_spetHmDel'];
                    $fsbu['fpod_customerRateCouDel'] = $fpoddata['fcpod_spetCouDel'];
                    $fsbu['fpod_customerRatePikup'] = $fpoddata['fcpod_spetPikup'];
                    $status = $db->perform('finascop_stock_branch_inventory', $fsbu, 'update', " id = {$fsbiCount}");
                } else {
                    $fsbi['mrp'] = $fsbiudetail['mrp'];
                    $fsbi['selling_price'] = $fsbiudetail['selling_price'];
                    $fsbi['item_count'] = $fsbiudetail['item_count'];
                    $fsbi['stit_id'] = $fsbiudetail['stit_id'];
                    $fsbi['branch_id'] = $fsbiudetail['branch_id'];
                    $fsbi['fsbg_id'] = $fsbiudetail['fsbg_id'];
                    $fsbi['updated_on'] = date("Y-m-d H:i:s");
                    $fsbi['fpod_poLandingCostleastSKU'] = $itemLandingCost;
                    $fsbi['fpod_poMMGleastSKU'] = $itemMMG;
                    $fsbi['fpod_leastSKUmrp'] = $fsbiudetail['mrp'];
                    $fsbu['purchasing_unit'] = $least_package_type_id;
                    $fsbi['fpod_customerRateHmDel'] = $fpoddata['fcpod_spetHmDel'];
                    $fsbi['fpod_customerRateCouDel'] = $fpoddata['fcpod_spetCouDel'];
                    $fsbi['fpod_customerRatePikup'] = $fpoddata['fcpod_spetPikup'];
                    $status = $db->perform('finascop_stock_branch_inventory', $fsbi);
                }
            }
            $fbiud['fbiu_status'] = 1;
            $status = $db->perform('finascop_stock_branch_inventory_upload', $fbiud, 'update', " fbiu_id = {$fbiu_id}");
            $msg = "Uploaded stock confirmed.";
        } else {
            $status = $db->query("DELETE FROM finascop_stock_branch_inventory_upload WHERE fbiu_id = {$fbiu_id}");
            $msg = "Nothing To upload";
        }


        $status = $db->query('commit');
        if ($status == 1) {
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            // var_dup($error);
            echo '{"success":false,"valid":false}';
        }
        break;
    case 'buildStockUploadCsv':
        if ($_SESSION['admin']->current_branch_iscpd == 1) {
            $br_ID = $_REQUEST['branchName'];
        } else {
            $br_ID = $_SESSION['admin']->finascop_current_branch_id;
        }
        $brancNam = $db->getItemFRomDB("SELECT br_Name FROM finascop_branch WHERE br_ID = {$br_ID}");
//        $listQuery = "SELECT fsi.stit_ID as itemId,stit_SKU,item_count,mrp,selling_price,stit_itemERPId FROM finascop_stock_itemmaster fsi "
//                . "LEFT JOIN finascop_stock_branch_inventory fsbi ON fsbi.stit_id = fsi.stit_ID WHERE branch_id = {$br_ID} AND stit_itemERPId <> ''";
//        $items = $db->getMulipleData($listQuery, true);
        $data = "erpId" . "," . "Qty" . "," . "MRP" . "," . "selling_price" . "\n";
        for ($j = 1; $j <= 3; $j++) {
            $itemERPId = 100 + $j;
            $item_count = 10;
            $item_mrp = 50;
            $selling_price = 45;
            $data .= $itemERPId . ',' . $item_count . ',' . $item_mrp . ',' . $selling_price . "\n";
        }
        $brancNam = str_replace(' ', '_', $brancNam);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="stockupload.csv"');
        echo $data;
        exit();
        break;
    case 'getBranchName':
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch WHERE br_status = 'Active' AND br_StoreType = 'Dealer' AND br_PyramidLevel = 4", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;

    case 'checkBranchStoreType':
        $count = $db->getItemSafe("SELECT COUNT(*) FROM finascop_branch WHERE br_status = 'Active' AND br_StoreType = 'Dealer' AND br_PyramidLevel = 4 AND br_ID = ?", "i", [$_POST['branchName']]);
        if ($count == 1) {
            echo '{"success":true,"valid":true}';
        } else {
            echo '{"success":false,"valid":false}';
        }
        break;
    case 'getRrpDetailStore':
        $type = $_POST['type'];
        switch ($type) {
            case '1':
                $qry = $db->getMulipleData("SELECT stit_ID AS rrp_detailId,stit_SKU AS rrp_detailName FROM finascop_stock_itemmaster WHERE stit_status = 1 order by stit_SKU asc", true);
                break;
            case '2':
                $qry = $db->getMulipleData("SELECT brand_id AS rrp_detailId,brand_name AS rrp_detailName FROM mypha_productbrands WHERE status = 1 order by brand_name asc", true);
                break;
            case '3':
                $qry = $db->getMulipleData("SELECT itemname_id AS rrp_detailId,item_name AS rrp_detailName FROM finascop_stock_itemmastername WHERE status = 1 order by item_name asc", true);
                break;
            case '4':
                $qry = $db->getMulipleData("SELECT sub_category_id AS rrp_detailId,sub_category AS rrp_detailName FROM mypha_productsubcategory WHERE status = 1 order by sub_category asc", true);
                break;
        }


        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'saveRRPData':
        $data['rrp_type'] = $_POST['rrp_type'];
        $data['rrp_detail'] = $_POST['rrp_detail'];
        $data['rrp_factor'] = $_POST['rrp_factor'];



        $rrpUnique = $db->getItemSafe("SELECT COUNT(*) from retaline_rrpMaster WHERE rrp_type ='?' AND rrp_detail = {$_POST['rrp_detail']} ", "s", [$_POST['rrp_type']]);
        $db->query('begin');
        if ($rrpUnique == 0) {
            $data['rrp_createdOn'] = date('Y-m-d H:i:s');
            $data['rrp_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('retaline_rrpMaster', $data);
            $lastId = $db->insert_id();
        } else {
            $data['rrp_updatedOn'] = date('Y-m-d H:i:s');
            $data['rrp_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('retaline_rrpMaster', $data, 'update', " rrp_type ='{$_POST['rrp_type']}' AND rrp_detail = {$_POST['rrp_detail']}");
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listRRPFactor':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'rrp_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        $subCategory_name = '';
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');

                    if ($field['field'] == 'rrp_typeName') {
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
                                    $typeId .= $comma . (int) 1;
                                    break;
                                case 'Brand':
                                    $typeId .= $comma . (int) 2;
                                    break;
                                case 'ItemMaster':
                                    $typeId .= $comma . (int) 3;
                                    break;
                                case 'Subcategory':
                                    $typeId .= $comma . (int) 4;
                                    break;
                            }
                        }
                        $search .= " and (rrp_type IN({$typeId})) ";
                    } else if ($field['field'] == 'rrp_detailName') {
                        $rrp_detailName = $field[data][value];
                        //$search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                    }

                    //$search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM retaline_rrpMaster {$search}  ";
        $count = $db->getItemFromDB($countQuery);
        $cond = '';
        $listQuery = "SELECT rrp_id,rrp_type,rrp_detail,rrp_factor FROM retaline_rrpMaster {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                switch ($datas[$i]['rrp_type']) {
                    case '1':
                        if (!empty($rrp_detailName)) {
                            $cond = " AND stit_SKU like '{$rrp_detailName}%'";
                        }
                        $datas[$i]['rrp_typeName'] = 'SKU';
                        $datas[$i]['rrp_detailName'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['rrp_detail']} {$cond}");
                        break;
                    case '2':
                        if (!empty($rrp_detailName)) {
                            $cond = " AND brand_name like '{$rrp_detailName}%'";
                        }
                        $datas[$i]['rrp_typeName'] = 'Brand';
                        $datas[$i]['rrp_detailName'] = $db->getItemFromDB("SELECT brand_name FROM mypha_productbrands WHERE brand_id = {$datas[$i]['rrp_detail']} {$cond}");
                        break;
                    case '3':
                        if (!empty($rrp_detailName)) {
                            $cond = " AND item_name like '{$rrp_detailName}%'";
                        }
                        $datas[$i]['rrp_typeName'] = 'ItemMaster';
                        $datas[$i]['rrp_detailName'] = $db->getItemFromDB("SELECT item_name FROM finascop_stock_itemmastername WHERE itemname_id = {$datas[$i]['rrp_detail']} {$cond}");
                        break;
                    case '4':
                        if (!empty($rrp_detailName)) {
                            $cond = " AND sub_category like '{$rrp_detailName}%'";
                        }
                        $datas[$i]['rrp_typeName'] = 'Subcategory';
                        $datas[$i]['rrp_detailName'] = $db->getItemFromDB("SELECT sub_category FROM mypha_productsubcategory WHERE sub_category_id = {$datas[$i]['rrp_detail']} {$cond}");
                        break;
                }
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else {
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        }
        break;
    case 'deleteRRPFactor':
        $id = $_POST['rrp_id'];
        $del_query = "DELETE FROM retaline_rrpMaster WHERE rrp_id =" . $id;
        $status = $db->query($del_query);

        $status = $db->query('commit');
        if ($status) {

            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'checkRRPData':
        $data['rrp_type'] = $_POST['rrp_type'];
        $data['rrp_detail'] = $_POST['rrp_detail'];
        $data['rrp_factor'] = $_POST['rrp_factor'];
        $rrp_factorFlag = 0;

        switch ($_POST['rrp_type']) {
            case 1:
                $rrp_factorSKU = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 1 AND rrp_detail = {$data['rrp_detail']}");
                if ($rrp_factorSKU > 1) {
                    $rrp_factorFlag = 1;
                    $rrpFactor .= "Already have factor for SKU - " . $rrp_factorSKU;
                } else {
                    $rrp_factorBrand = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 2 AND rrp_detail = {$itemDetails['pdt_brand']}");
                    if ($rrp_factorBrand > 1) {
                        $rrp_factorFlag = 1;
                        $rrpFactor .= "Already have factor for SKU Brand - " . $rrp_factorBrand;
                    } else {
                        $rrp_factorItem = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 3 AND rrp_detail = {$itemDetails['stit_itemId']}");
                        if ($rrp_factorItem > 1) {
                            $rrp_factorFlag = 1;
                            $rrpFactor .= "Already have factor for SKU Item - " . $rrp_factorItem;
                        } else {
                            $rrp_factorSC = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 4 AND rrp_detail = {$itemDetails['product_category']}");
                            if ($rrp_factorSC > 1) {
                                $rrp_factorFlag = 1;
                                $rrpFactor .= "Already have factor for SKU Subcategory - " . $rrp_factorSC;
                            }
                        }
                    }
                }
                break;
            case 2:
                $rrp_factorBrand = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 2 AND rrp_detail = {$data['rrp_detail']}");
                if ($rrp_factorBrand > 1) {
                    $rrp_factorFlag = 1;
                    $rrpFactor .= "Already have factor for this Brand - " . $rrp_factorBrand;
                }
                break;
            case 3:
                $rrp_factorItem = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 3 AND rrp_detail = {$data['rrp_detail']}");
                if ($rrp_factorItem > 1) {
                    $rrp_factorFlag = 1;
                    $rrpFactor .= "Already have factor for this Item - " . $rrp_factorItem;
                }
                break;
            case 4:
                $rrp_factorSC = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 4 AND rrp_detail = {$data['rrp_detail']}");
                if ($rrp_factorSC > 1) {
                    $rrp_factorFlag = 1;
                    $rrpFactor .= "Already have factor for this Subcategory - " . $rrp_factorSC;
                }
                break;
        }

        if ($rrp_factorFlag == 1) {
            echo "{success:true,valid:false,message:'.$rrpFactor.'}";
        } else {
            echo "{'success':true,'valid':true}";
        }
        break;
}
    