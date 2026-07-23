<?php

class InventoryReportGeneration {

    public function _getStockReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'fsb.stit_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
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
                    $searchitem .= " and ({$field[field]} LIKE '{$field['data']['value']}%') ";
                }
            }
        }
        $branchName = $_POST['branchName'];
        $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;
        //$where = " AND branch_id =" . $br_ID;

        $br_PyramidLevel = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch WHERE br_ID = {$br_ID}");
        if ($br_PyramidLevel == 2) {
            $rackStatus = " AND stiid_status = 1";
            $dispatchStatus = " AND stiid_status = 2";
            $receiveStatus = " AND stiid_status = -1";
            $deliverStatus = " AND stiid_status = -1";
        } else if ($br_PyramidLevel == 3) {
            $rackStatus = " AND stiid_status = 1";
            $dispatchStatus = " AND stiid_status = 2";
            $receiveStatus = " AND stiid_status = 3";
            $deliverStatus = " AND stiid_status = -1";
        } else if ($br_PyramidLevel == 4) {
            $rackStatus = " AND stiid_status = 4";
            $dispatchStatus = " AND stiid_status = -1";
            $receiveStatus = " AND stiid_status = 3";
            $deliverStatus = " AND stiid_status = 5";
        }

        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . " finascop_stock_branch_inventory fsb "
                . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID "
                . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$searchitem}";
        $listQuery = "SELECT fsb.stit_id as stit_id,CONCAT(br.br_Name ,'-',br.branch_shortname) as br_Name,fsi.stit_SKU as stit_SKU,fsbg_id,purchasing_unit,"
                . "fsb.item_count as item_count,fsb.mrp as mrp,fsb.selling_price as selling_price,fsb.branch_id as branchId,"
                . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stitl1_optimumqty "
                . "WHEN br.br_stockLevel= 2 THEN fsi.stitl2_optimumqty "
                . "ELSE stitl3_optimumqty END AS optimumqty,"
                . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stit11_minimumqty "
                . "WHEN br.br_stockLevel= 2 THEN fsi.stit12_minimumqty "
                . "ELSE stit13_minimumqty END AS minimumqty,"
                . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stit11_maximumqty "
                . "WHEN br.br_stockLevel= 2 THEN fsi.stit13_maximumqty "
                . "ELSE stit13_maximumqty END AS maximumqty,csb_package_type_name,cs_nos,cs_package_type_name,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,br_PyramidLevel, "
                . "(SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = purchasing_unit) as purchasing_unitName "
                . "FROM finascop_stock_branch_inventory fsb "
                . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID "
                . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$searchitem} ORDER BY {$sort} {$dir} LIMIT $start,$limit ";
        // limit " . $start . "," . $limit;


        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getB2CSalesReportGridData($postvar) {

        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'order_id' : $sort;
//        $sort = ($sort=='order_created_on') ? 'DATE(order_created_on)' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $userID = $_SESSION['admin']->UserId;
        $filter_qry = "WHERE 1=1 ";

        $order = '';
        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
            }
        }

        if ($_POST['current_branch_id'] > 0) {
            $current_branch_id = $_POST['current_branch_id'];
        } else {
            $current_branch_id = $db->getItemSafe("SELECT br_ID FROM finascop_branch WHERE br_Name = ?", "s", [$_POST['br_Name']]);
        }
        if ($sort == 'order_created_on') {
            $sort = 'order_id';
        }
        if ($_POST['br_Name'] != '') {
            $filter_qry .= " AND br_Name  LIKE  '" . $_POST['br_Name'] . "%'";
        }
        if (!empty($_POST['search_stock_from_date']) && !empty($_POST['search_stock_to_date'])) {
            $filter_qry .= " and DATE_FORMAT(created_at, '%Y-%m-%d') BETWEEN '" . date('Y-m-d', strtotime($_POST['search_stock_from_date'])) . "' AND '" . date('Y-m-d', strtotime($_POST['search_stock_to_date'])) . "'";
        }


        $query = " SELECT order_id,order_order_id,order_packedbags_count,order_customer_id,order_branch_id,CONCAT(br_Name ,'-',branch_shortname) as br_Name,bco.status_id as status,DATE_FORMAT(created_at,'%d-%m-%Y') AS order_created_on,
             TIME_FORMAT(cast(created_at as time),'%r') as ordertime,admin_description AS order_status,admin_description,order_payment_gateway_refid,order_payment_gateway_refid_crc32,
            (SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = order_customer_id) AS delivery_to,(SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = order_customer_id) AS cust_mobile,
            order_HasReturn,order_ItemsReturned,order_ReturnVerified,created_at,order_total_amount,
            CASE WHEN payment_mode = 1 THEN 'Pay On Delivery' WHEN payment_mode = 2 THEN 'Online Payment' WHEN payment_mode = 3 THEN 'Wallet' WHEN payment_mode = 4 THEN 'COD With Wallet' 
            WHEN payment_mode = 5 THEN 'Online With Wallet' WHEN payment_mode = 6 THEN 'Online On Delivery' WHEN payment_mode = 7 THEN 'Cash On Delivery' END AS payMode
            FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id WHERE 1 = 1 and bco.status_id > 0 "; //AND order_branch_id = {$current_branch_id}
        $countQuery = " SELECT COUNT(*) FROM ({$query}) AS orerCount {$filter_qry} ORDER BY  {$sort} {$dir} ";
        $listQuery = "SELECT * FROM({$query}) as orderList  {$filter_qry}  ORDER BY  {$sort} {$dir} limit " . $start . "," . $limit;
        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getB2BSalesReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'bbso_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;

        //$filter_qry = " WHERE  br_ID = {$_SESSION['admin']->finascop_current_branch_id}";
        $filter_qry = " WHERE 1=1 ";
        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }
        /* status_id >= 1 AND status_id <= 7 AND */
        $countQuery = "SELECT count(*) from(SELECT bbso_id, bbso_SONumber, bbso_SODate,b2b_Customer_ID,b2b_Customer_Name,bbso_SOValue,bbso_InvoiceStatus,"
                . "@bbso_InvoiceStatusName:=(CASE WHEN bbso_InvoiceStatus = 0 THEN 'Not Ready for Invoice' WHEN bbso_InvoiceStatus = 1 THEN 'Ready for Invoice' WHEN bbso_InvoiceStatus = 2 THEN 'Invoiced' END) AS bbso_InvoiceStatusName,"
                . "bbso_HandlingCharges ,(SELECT status from retaline_B2B_Status rbbs WHERE rbbs.status_id = rbs.status_id) AS bbso_Active,status_id "
                . "FROM retaline_B2B_SalesOrder rbs) as ase {$filter_qry} ";
        $listQuery = "SELECT * FROM (SELECT bbso_id, bbso_SONumber, bbso_SODate,b2b_Customer_ID,b2b_Customer_Name,bbso_SOValue,bbso_InvoiceStatus,"
                . "@bbso_InvoiceStatusName:=(CASE WHEN bbso_InvoiceStatus = 0 THEN 'Not Ready for Invoice' WHEN bbso_InvoiceStatus = 1 THEN 'Ready for Invoice' WHEN bbso_InvoiceStatus = 2 THEN 'Invoiced' END) AS bbso_InvoiceStatusName,"
                . "bbso_HandlingCharges ,(SELECT status from retaline_B2B_Status rbbs WHERE rbbs.status_id = rbs.status_id) AS bbso_Active,status_id "
                . "FROM retaline_B2B_SalesOrder rbs) as asd {$filter_qry} ORDER BY {$sort} {$dir} LIMIT {$start},{$limit}";
        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getStockDetailsReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'stiid_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = ' WHERE stiid_status < 6';
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');
                switch ($field['data']['type']) {
                    case 'string':
                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field[field]} LIKE '{$field['data']['value']}%') ";
                        }
                        break;
                    case 'date':

                        switch ($field['data']['comparison']) {
                            case 'gt' :
                                $searchitem .= " and DATE_FORMAT(" . $field['field'] . ", '%Y-%m-%d') > '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            case 'lt':
                                $searchitem .= " and DATE_FORMAT(" . $field['field'] . ", '%Y-%m-%d') < '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            case 'eq':
                                $searchitem .= " and DATE_FORMAT(" . $field['field'] . ", '%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                        }
                        //$filter_qry .= " AND DATE(order_created_on) " . $comparisons[$val['data']['comparison']] . " '" . date('Y-m-d', strtotime($val['data']['value'])) . "' ";
                        //}

                        break;
                    case 'numeric' :
                        $searchitem .= " AND " . $field['field'] . " " . $comparisons[$field['data']['comparison']] . " " . $field['data']['value'];
                        break;
                    case 'list':
                }
            }
        }
        if ($sort == 'fpo_poDate') {
            $sort = 'fpo_id';
        }
        if ($sort == 'stiid_expirydate') {
            $sort = 'stiid_id';
        }

        $countQuery = "SELECT COUNT(1) FROM finascop_stock_item_inventorydetails "
                . "INNER JOIN finascop_purchase_order ON fpo_id = stiid_fpoid INNER JOIN finascop_branch ON br_ID = cpd_branch_id {$search}{$searchitem}";
        $listQuery = "SELECT stiid_id,CONCAT(br_Name ,'-',branch_shortname) as br_Name,fpo_id,fpo_vendorName,fpo_poNumber,DATE_FORMAT(fpo_poDate,'%d-%m-%Y') AS fpo_poDate,stiid_barcode,stiid_itemmastername,stiid_leastSKUmrp,"
                . "DATE_FORMAT(stiid_expirydate,'%d-%m-%Y') AS stiid_expirydate,stiid_batchno,cpd_branch_id FROM finascop_stock_item_inventorydetails "
                . "INNER JOIN finascop_purchase_order ON fpo_id = stiid_fpoid INNER JOIN finascop_branch ON br_ID = cpd_branch_id {$search}{$searchitem} ORDER BY {$sort} {$dir} LIMIT $start,$limit ";

        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getFastMovingStockDetailsReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'stiid_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = ' WHERE stiid_status < 6';
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $searchitem .= " and ({$field[field]} LIKE '{$field['data']['value']}%') ";
                }
            }
        }
        if ($sort == 'fpo_poDate') {
            $sort = 'fpo_id';
        }
        if ($sort == 'stiid_expirydate') {
            $sort = 'stiid_id';
        }

        $countQuery = "SELECT COUNT(1) FROM finascop_stock_item_inventorydetails "
                . "INNER JOIN finascop_purchase_order ON fpo_id = stiid_fpoid INNER JOIN finascop_branch ON br_ID = cpd_branch_id {$search}{$searchitem}";
        $listQuery = "SELECT stiid_id,CONCAT(br_Name ,'-',branch_shortname) as br_Name,fpo_id,fpo_vendorName,fpo_poNumber,DATE_FORMAT(fpo_poDate,'%d-%m-%Y') AS fpo_poDate,stiid_barcode,stiid_itemmastername,"
                . "DATE_FORMAT(stiid_expirydate,'%d-%m-%Y') AS stiid_expirydate,stiid_batchno,cpd_branch_id FROM finascop_stock_item_inventorydetails "
                . "INNER JOIN finascop_purchase_order ON fpo_id = stiid_fpoid INNER JOIN finascop_branch ON br_ID = cpd_branch_id {$search}{$searchitem} ORDER BY {$sort} {$dir} LIMIT $start,$limit ";

        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getBannedStockDetailsReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'stiid_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = ' WHERE stiid_status IN (13,14,15,16,17)';
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $searchitem .= " and ({$field[field]} LIKE '{$field['data']['value']}%') ";
                }
            }
        }

        $countQuery = "SELECT COUNT(1) FROM finascop_stock_item_inventorydetails "
                . "INNER JOIN finascop_purchase_order ON fpo_id = stiid_fpoid INNER JOIN finascop_branch ON br_ID = cpd_branch_id {$search}{$searchitem}";
        $listQuery = "SELECT stiid_id,CONCAT(br_Name ,'-',branch_shortname) as br_Name,fpo_id,fpo_vendorName,fpo_poNumber,DATE_FORMAT(fpo_poDate,'%d-%m-%Y') AS fpo_poDate,stiid_barcode,stiid_itemmastername,"
                . "DATE_FORMAT(stiid_expirydate,'%d-%m-%Y') AS stiid_expirydate,stiid_batchno,cpd_branch_id FROM finascop_stock_item_inventorydetails "
                . "INNER JOIN finascop_purchase_order ON fpo_id = stiid_fpoid INNER JOIN finascop_branch ON br_ID = cpd_branch_id {$search}{$searchitem} ORDER BY {$sort} {$dir} LIMIT $start,$limit ";

        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getB2CSalesDetailsReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'order_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = ' WHERE 1 =1 ';
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');
                switch ($field['data']['type']) {
                    case 'string':
                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field[field]} LIKE '{$field['data']['value']}%') ";
                        }
                        break;
                    case 'date':

                        switch ($field['data']['comparison']) {
                            case 'gt' :
                                $searchitem .= " and DATE_FORMAT(" . $field['field'] . ", '%Y-%m-%d') > '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            case 'lt':
                                $searchitem .= " and DATE_FORMAT(" . $field['field'] . ", '%Y-%m-%d') < '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            case 'eq':
                                $searchitem .= " and DATE_FORMAT(" . $field['field'] . ", '%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                        }
                        //$filter_qry .= " AND DATE(order_created_on) " . $comparisons[$val['data']['comparison']] . " '" . date('Y-m-d', strtotime($val['data']['value'])) . "' ";
                        //}

                        break;
                    case 'numeric' :
                        $searchitem .= " AND " . $field['field'] . " " . $comparisons[$field['data']['comparison']] . " " . $field['data']['value'];
                        break;
                    case 'list':
                }
            }
        }
        if ($_POST['br_Name'] != '') {
            $searchitem .= " AND br_Name  LIKE  '" . $_POST['br_Name'] . "%'";
        }
        if (!empty($_POST['search_stock_from_date']) && !empty($_POST['search_stock_to_date'])) {
            $searchitem .= " and DATE_FORMAT(retaline_customer_order.created_at, '%Y-%m-%d') BETWEEN '" . date('Y-m-d', strtotime($_POST['search_stock_from_date'])) . "' AND '" . date('Y-m-d', strtotime($_POST['search_stock_to_date'])) . "'";
        }


        $countQuery = "SELECT COUNT(1) FROM retaline_customer_order_items INNER JOIN retaline_customer_order ON customer_order_id = order_id  
        INNER JOIN finascop_stock_transfer_order ON fstr_id = order_id AND fsto_ordertype = 1 
        INNER JOIN finascop_branch ON br_ID = retaline_customer_order.order_branch_id 
        INNER JOIN finascop_stock_itemmaster ON stit_ID = item_product_id {$search}{$searchitem}";
        $listQuery = "SELECT order_id,order_order_id,CONCAT(br_Name ,'-',branch_shortname) as br_Name,item_product_id,stit_SKU,item_igst,item_retail_price,item_sales_price,item_order_qty,item_price,CASE WHEN payment_mode = 1 THEN 'Pay On Delivery' WHEN payment_mode = 2 THEN 'Online Payment' WHEN payment_mode = 3 THEN 'Wallet' WHEN payment_mode = 4 THEN 'COD With Wallet' 
        WHEN payment_mode = 5 THEN 'Online With Wallet' WHEN payment_mode = 6 THEN 'Online On Delivery' WHEN payment_mode = 7 THEN 'Cash On Delivery' END AS payMode,DATE_FORMAT(retaline_customer_order.created_at,'%Y-%m-%d') AS orderDate 
        FROM retaline_customer_order_items INNER JOIN retaline_customer_order ON customer_order_id = order_id  
INNER JOIN finascop_stock_transfer_order ON fstr_id = order_id AND fsto_ordertype = 1 
INNER JOIN finascop_branch ON br_ID = retaline_customer_order.order_branch_id 
INNER JOIN finascop_stock_itemmaster ON stit_ID = item_product_id {$search}{$searchitem} ORDER BY {$sort} {$dir} LIMIT $start,$limit ";

        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getB2BSalesDetailsReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'stiid_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = ' WHERE 1 =1 ';
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');
                switch ($field['data']['type']) {
                    case 'string':
                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field[field]} LIKE '{$field['data']['value']}%') ";
                        }
                        break;
                    case 'date':

                        switch ($field['data']['comparison']) {
                            case 'gt' :
                                $searchitem .= " and DATE_FORMAT(" . $field['field'] . ", '%Y-%m-%d') > '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            case 'lt':
                                $searchitem .= " and DATE_FORMAT(" . $field['field'] . ", '%Y-%m-%d') < '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            case 'eq':
                                $searchitem .= " and DATE_FORMAT(" . $field['field'] . ", '%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                        }
                        //$filter_qry .= " AND DATE(order_created_on) " . $comparisons[$val['data']['comparison']] . " '" . date('Y-m-d', strtotime($val['data']['value'])) . "' ";
                        //}

                        break;
                    case 'numeric' :
                        $searchitem .= " AND " . $field['field'] . " " . $comparisons[$field['data']['comparison']] . " " . $field['data']['value'];
                        break;
                    case 'list':
                }
            }
        }

        $countQuery = "SELECT COUNT(1) FROM retaline_B2B_SalesOrder INNER JOIN finascop_stock_transfer_order ON fstr_id = bbso_id AND fsto_ordertype = 2 "
                . "INNER JOIN finascop_stock_transfer_order_details_barcodes ON finascop_stock_transfer_order_details_barcodes.fsto_id = finascop_stock_transfer_order.fsto_id "
                . "INNER JOIN finascop_stock_item_inventorydetails ON finascop_stock_item_inventorydetails.stiid_id = finascop_stock_transfer_order_details_barcodes.stiid_id "
                . "INNER JOIN finascop_purchase_order ON fpo_id = stiid_fpoid INNER JOIN finascop_branch ON finascop_branch.br_ID = cpd_branch_id {$search}{$searchitem}";
        $listQuery = "SELECT bbso_SONumber,finascop_stock_item_inventorydetails.stiid_id,fpo_id,fpo_vendorName,fpo_poNumber,DATE_FORMAT(fpo_poDate,'%d-%m-%Y') AS fpo_poDate,"
                . "finascop_stock_item_inventorydetails.stiid_barcode,stiid_itemmastername,stiid_leastSKUmrp,DATE_FORMAT(stiid_expirydate,'%d-%m-%Y') AS stiid_expirydate,stiid_batchno,"
                . "cpd_branch_id,CONCAT(br_Name ,'-',branch_shortname) as br_Name,DATE_FORMAT(bbso_SODate,'%d-%m-%Y') AS bbso_SODate FROM retaline_B2B_SalesOrder "
                . "INNER JOIN finascop_stock_transfer_order ON fstr_id = bbso_id AND fsto_ordertype = 2 "
                . "INNER JOIN finascop_stock_transfer_order_details_barcodes ON finascop_stock_transfer_order_details_barcodes.fsto_id = finascop_stock_transfer_order.fsto_id "
                . "INNER JOIN finascop_stock_item_inventorydetails ON finascop_stock_item_inventorydetails.stiid_id = finascop_stock_transfer_order_details_barcodes.stiid_id "
                . "INNER JOIN finascop_purchase_order ON fpo_id = stiid_fpoid INNER JOIN finascop_branch ON finascop_branch.br_ID = cpd_branch_id {$search}{$searchitem} ORDER BY {$sort} {$dir} LIMIT $start,$limit ";

        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function handler($op) {
        global $db;
        switch ($op) {
            case 'getStockReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getStockReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'StockReportsexportexcel':
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

                //$filterdata = json_decode($_POST['filterData'],true);
                //array_push($_POST,$filterdata) ;
                foreach ($lastParameters as $keys => $values) {
                    $_POST[$keys] = $values;
                }
                $_POST['start'] = 0;
                $_POST['limit'] = 100000;
                $qry = $this->_getStockReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getB2CSalesReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getB2CSalesReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'B2CSalesReportsexportexcel':
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

                //$filterdata = json_decode($_POST['filterData'],true);
                //array_push($_POST,$filterdata) ;
                foreach ($lastParameters as $keys => $values) {
                    $_POST[$keys] = $values;
                }
                $_POST['start'] = 0;
                $_POST['limit'] = 100000;
                $qry = $this->_getB2CSalesReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getB2BSalesReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getB2BSalesReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'B2BSalesReportsexportexcel':
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

                //$filterdata = json_decode($_POST['filterData'],true);
                //array_push($_POST,$filterdata) ;
                foreach ($lastParameters as $keys => $values) {
                    $_POST[$keys] = $values;
                }
                $_POST['start'] = 0;
                $_POST['limit'] = 100000;
                $qry = $this->_getB2BSalesReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getStockDetailsReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getStockDetailsReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'StockDetailsReportsexportexcel':
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

                //$filterdata = json_decode($_POST['filterData'],true);
                //array_push($_POST,$filterdata) ;
                foreach ($lastParameters as $keys => $values) {
                    $_POST[$keys] = $values;
                }
                $_POST['start'] = 0;
                $_POST['limit'] = 100000;
                $qry = $this->_getStockDetailsReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getFastMovingStockDetailsReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getFastMovingStockDetailsReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'FastMovingStockDetailsReportsexportexcel':
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

                //$filterdata = json_decode($_POST['filterData'],true);
                //array_push($_POST,$filterdata) ;
                foreach ($lastParameters as $keys => $values) {
                    $_POST[$keys] = $values;
                }
                $_POST['start'] = 0;
                $_POST['limit'] = 100000;
                $qry = $this->_getFastMovingStockDetailsReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getBannedStockDetailsReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getBannedStockDetailsReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'bannedStockDetailsReportsexportexcel':
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

                //$filterdata = json_decode($_POST['filterData'],true);
                //array_push($_POST,$filterdata) ;
                foreach ($lastParameters as $keys => $values) {
                    $_POST[$keys] = $values;
                }
                $_POST['start'] = 0;
                $_POST['limit'] = 100000;
                $qry = $this->_getBannedStockDetailsReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getB2CSalesDetailsReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getB2CSalesDetailsReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'B2CSalesDetailsReportsexportexcel':
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

                //$filterdata = json_decode($_POST['filterData'],true);
                //array_push($_POST,$filterdata) ;
                foreach ($lastParameters as $keys => $values) {
                    $_POST[$keys] = $values;
                }
                $_POST['start'] = 0;
                $_POST['limit'] = 100000;
                $qry = $this->_getB2CSalesDetailsReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getB2BSalesDetailsReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getB2BSalesDetailsReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'B2BSalesDetailsReportsexportexcel':
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

                //$filterdata = json_decode($_POST['filterData'],true);
                //array_push($_POST,$filterdata) ;
                foreach ($lastParameters as $keys => $values) {
                    $_POST[$keys] = $values;
                }
                $_POST['start'] = 0;
                $_POST['limit'] = 100000;
                $qry = $this->_getB2BSalesDetailsReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getBranchName':
                $qry = $db->getMulipleData("SELECT br_ID,CONCAT(br_Name ,'-',branch_shortname) as br_Name FROM finascop_branch WHERE br_status = 'Active' AND br_PyramidLevel = 4 ", true);
                if (!empty($qry)) {
                    echo json_encode($qry);
                } else
                    echo [];
                break;
        }
    }

}

$obj = new InventoryReportGeneration($db);
$obj->handler($op);



