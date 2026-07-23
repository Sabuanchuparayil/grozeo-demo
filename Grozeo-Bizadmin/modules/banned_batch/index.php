<?php

$userid = $_SESSION['admin']->Finascop_UserId;
switch ($op) {
    case 'getItemName':
        if ($_POST['query'] != '') {
            $que = addslashes($_POST['query']);
            $searchQuery = " AND stit_SKU LIKE '%{$que}%'";
        } else {
            $searchQuery = '';
        }


        //finascop_getjsonkeyarray("SELECT stit_ID,stit_itemName,stit_SKU FROM finascop_stock_itemmaster where 1=1 {$searchQuery} ");
        $qry = $db->getMulipleData("SELECT fsi.stit_ID as stit_ID,stit_itemName,stit_SKU FROM finascop_stock_itemmaster fsi  where 1=1 and isMedicine = 1 {$searchQuery}", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'saveMedicineBatchData':
        $data['fsibb_batch'] = $_POST['medicineMaster_batch'];
        $data['stit_id'] = $_POST['medicine_master'];
        $data['fsibb_createdOn'] = date('Y-m-d H:i:s');
        $data['fsibb_createdBy'] = $_SESSION['admin']->Finascop_UserId;

        $db->query('begin');
        $medCatUnique = $db->getItemSafe("SELECT COUNT(*) from finascop_stock_item_banned_batch WHERE fsibb_batch ='?' AND stit_id = {$_POST['medicine_master']} ", "s", [$_POST['medicineMaster_batch']]);
        if ($medCatUnique == 0) {
            $status = $db->perform('finascop_stock_item_banned_batch', $data);
            $lastId = $db->insert_id();

            $inventoryDetails = $db->getMultipleData("SELECT stiid_id,stiid_itemmasterid,stiid_itemmastername,stiid_batchno,cpd_branch_id,fsbg_id,stiid_status,stiid_barcode  FROM finascop_stock_item_inventorydetails "
                    . "WHERE stiid_itemmasterid = {$_POST['medicine_master']} AND stiid_batchno = '{$_POST['medicineMaster_batch']}' AND stiid_status IN (1,4)", true);

            foreach ($inventoryDetails as $inventoryDetai) {
                $pyramidLevel = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch WHERE br_ID = {$inventoryDetai['cpd_branch_id']}");

                $invent['stiid_status'] = 17;
                $invent['stiid_updatedon'] = date("Y-m-d H:i:s");
                $invent['stiid_updatedby'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform("finascop_stock_item_inventorydetails", $invent, 'update', " stiid_id = {$inventoryDetai['stiid_id']}");
                $fsiidmDatatl['stiidm_barcode'] = $inventoryDetai['stiid_barcode'];
                $fsiidmDatatl['stiidm_itemmasterid'] = $inventoryDetai['stiid_itemmasterid'];
                $fsiidmDatatl['created_at'] = date('Y-m-d H:i:s');
                $fsiidmDatatl['stiid_id'] = $inventoryDetai['stiid_id'];
                $fsiidmDatatl['stiidm_details'] = 'Barcode is banned - ' . $inventoryDetai['stiid_barcode'];
                $status = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiidmDatatl);
                $datetime = date('Y-m-d H:i:s');
                $status = $db->query("UPDATE finascop_stock_branch_inventory SET updated_on = '{$datetime}',item_count = item_count - 1 WHERE stit_id = {$inventoryDetai['stiid_itemmasterid']} AND branch_id = {$inventoryDetai['cpd_branch_id']}  AND fsbg_id = {$inventoryDetai['fsbg_id']}");
            }

            $return_rec = $db->getFromDb("SELECT *  FROM finascop_stock_item_banned_batch WHERE fsibb_id = {$lastId}", true);
            $status = $db->query('commit');
            if ($status == 1) {
                echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
            } else {
                echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
            }
        } else {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        }

        break;
    case 'listMedicineBannedBatch':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'fsibb_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        // SECURITY: use buildSafeFilterQuery
        $allowedFields = ['batch_no', 'item_name', 'item_code', 'ban_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        /* RAW (disabled): $filter = $_POST['filter']; */
        $subCategory_name = '';
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');
                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $search .= " AND ({$field['field']} IN('{$fiterItem}')) ";
                    } else if ($field['field'] == 'stit_SKU') {
                            $stit_SKU = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(stit_id),0) FROM finascop_stock_item_banned_batch WHERE stit_id IN (SELECT GROUP_CONCAT(stit_ID) FROM finascop_stock_itemmaster WHERE stit_SKU LIKE '{$field['data']['value']}%')");
                            $search .= " AND a.stit_id IN({$stit_SKU}) ";
                        } else {
                        $search .= " AND ({$field['field']} LIKE '{$field['data']['value']}%') ";
                    }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM finascop_stock_item_banned_batch a {$search}  ";
        $count = $db->getItemFromDB($countQuery);

        $listQuery = "SELECT fsibb_id,a.stit_id,fsibb_batch,fsibb_createdOn,fsibb_createdBy,b.stit_ID,stit_SKU FROM finascop_stock_item_banned_batch a INNER JOIN finascop_stock_itemmaster b ON b.stit_ID = a.stit_id {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $datas[$i]['stit_SKU'] = $db->getItemFromDb("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID={$datas[$i]['stit_id']}");
                $datas[$i]['fsibbCreatedBy'] = $db->getItemFromDb("SELECT CONCAT(FirstName,' ',FirstName) FROM finascop_usr_profile WHERE UserId={$datas[$i]['fsibb_createdBy']}");
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else {
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        }
        break;
}
