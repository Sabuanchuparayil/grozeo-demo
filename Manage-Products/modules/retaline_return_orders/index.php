<?php

global $db;
switch ($op) {
    case 'listPurchaseReturned':
        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'stit_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $search = ' WHERE 1=1 AND item_returned > 0 ';
        if (isset($data['filter'])) {
        $allowedFields = ['ro_id', 'order_id', 'order_generated_id', 'ro_date', 'ro_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }
        if ($_SESSION['admin']->br_PyramidLevel == 1) {
            $centStores = $db->getItemFromDB("SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_cpd = {$_SESSION['admin']->finascop_current_branch_id}");
            $where = " AND branch_id IN ({$centStores})";
        } else {
            $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;
            $branchName = $_POST['branchName'];
            $where = " AND branch_id =" . $br_ID;
        }



        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . " finascop_return_branch_inventory fsb "
                . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID "
                . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$where}{$searchitem}";
        $count = $db->getItemFromDB($countQuery);
        $listQuery = "SELECT fsb.stit_id as stit_id,br.br_Name as br_Name,fsi.stit_SKU as stit_SKU,fsbg_id,frbi_epr,"
                . "fsb.item_count as item_count,fsb.mrp as mrp,fsb.selling_price as selling_price,fsb.branch_id as branchId,stit_category_name,stit_quantity,stit_brand_name,stit_product_variant,frbi_status,frbi_id,"
                . "CASE WHEN frbi_status= 1 THEN 'Damaged' WHEN frbi_status= 2 THEN 'Expirable' END AS frbi_statusName,item_returned "
                . "FROM finascop_return_branch_inventory fsb "
                . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID "
                . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$where}{$searchitem} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';

        break;
    case 'listStockLost':
        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'stit_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $search = ' WHERE 1=1 AND item_lossCount > 0 ';
        if (isset($data['filter'])) {
        $allowedFields = ['ro_id', 'order_id', 'order_generated_id', 'ro_date', 'ro_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }
        $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;
        $branchName = $_POST['branchName'];
        $where = " AND branch_id =" . $br_ID;


        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . " finascop_return_branch_inventory fsb "
                . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID "
                . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$where}{$searchitem}";
        $count = $db->getItemFromDB($countQuery);
        $listQuery = "SELECT fsb.stit_id as stit_id,br.br_Name as br_Name,fsi.stit_SKU as stit_SKU,fsbg_id,frbi_epr,"
                . "fsb.item_count as item_count,fsb.mrp as mrp,fsb.selling_price as selling_price,fsb.branch_id as branchId,stit_category_name,stit_quantity,stit_brand_name,stit_product_variant,frbi_status,frbi_id,"
                . "CASE WHEN frbi_status= 1 THEN 'Damaged' WHEN frbi_status= 2 THEN 'Expirable' END AS frbi_statusName,item_lossCount "
                . "FROM finascop_return_branch_inventory fsb "
                . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID "
                . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$where}{$searchitem} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';

        break;
    case 'getpurchaseReturnedDetailStore':
        $itemId = $_POST['itemId'];
        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'pure_Id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        if (isset($data['filter'])) {
        $allowedFields = ['ro_id', 'order_id', 'order_generated_id', 'ro_date', 'ro_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }


        $countQuery = "SELECT COUNT(*) FROM finascop_purchase_return_items fpri "
                . "INNER JOIN finascop_purchase_return ON finascop_purchase_return.pure_id = fpri.pure_id "
                . "INNER JOIN finascop_stock_party ON stpa_id = pure_vendorId WHERE purd_itemID = {$itemId} {$searchitem}";
        $count = $db->getItemFromDB($countQuery);
        $listQuery = "SELECT fpri.pure_Id,purd_itemID,purd_itemReturnedQty,purd_Rate,pure_EntryOn,pure_vendorId,stpa_Fname FROM finascop_purchase_return_items fpri "
                . "INNER JOIN finascop_purchase_return ON finascop_purchase_return.pure_id = fpri.pure_id "
                . "INNER JOIN finascop_stock_party ON stpa_id = pure_vendorId WHERE purd_itemID = {$itemId} {$searchitem} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        break;
}
