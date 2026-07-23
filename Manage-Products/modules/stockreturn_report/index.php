<?php

class stockReturnReportGeneration {

    public function _getStockReturnReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'stit_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = ' WHERE 1=1 AND item_count > 0 ';
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
        $current_branch_id = $_SESSION['admin']->finascop_current_branch_id;
        $returnOrders = $db->getItemFromDB(" SELECT GROUP_CONCAT(rtrqo_id) FROM finascop_stock_return_request_order LEFT JOIN retaline_customer_order bco ON bco.order_id = finascop_stock_return_request_order.order_id
                        LEFT JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        LEFT JOIN finascop_branch ON br_ID = order_branch_id  WHERE 1 = 1  "); //AND rtrqo_sourceBranch = {$current_branch_id}
        if (!empty($returnOrders)) {
            $damagedItems = "SELECT rtrqod_item_id,SUM(rtrqod_return_damaged) AS item_count from finascop_stock_return_request_order_details WHERE rtrqod_isPackOrderCreated = 0 AND rtrqod_return_damaged > 0 AND rtrqo_id IN ($returnOrders) GROUP BY rtrqod_item_id ORDER BY {$sort} {$dir} LIMIT $start,$limit";
            $datas = $db->getMulipleData($damagedItems, true);
            $itemCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_return_request_order_details WHERE rtrqod_return_damaged > 0 AND rtrqo_id IN ($returnOrders) ");
            $countQuery = "SELECT COUNT(*) FROM finascop_stock_return_request_order_details WHERE rtrqod_return_damaged > 0 AND rtrqo_id IN ($returnOrders) ";
            $listQuery = $damagedItems;
        }

        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getStockLostReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'stit_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;

        $search = ' WHERE 1=1 AND item_lossCount > 0 ';
        $filter = $postvar['filter'];
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
        $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;
        $branchName = $_POST['branchName'];
        //$where = " AND branch_id =" . $br_ID;


        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . " finascop_return_branch_inventory fsb "
                . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID "
                . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$where}{$searchitem}";
        $count = $db->getItemFromDB($countQuery);
        $listQuery = "SELECT fsb.stit_id as stit_id,br.br_Name as br_Name,fsi.stit_SKU as stit_SKU,fsbg_id,frbi_epr,"
                . "fsb.item_count as item_count,fsb.mrp as mrp,fsb.selling_price as selling_price,fsb.branch_id as branchId,stit_category_name,stit_quantity,stit_brand_name,stit_product_variant,frbi_status,frbi_id,"
                . "CASE WHEN frbi_status= 1 THEN 'Damaged' WHEN frbi_status= 2 THEN 'Expirable' END AS frbi_statusName,item_lossCount "
                . "FROM finascop_return_branch_inventory fsb "
                . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID "
                . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$where}{$searchitem} ORDER BY {$sort} {$dir} LIMIT $start,$limit";
        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getReturnDetailsReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'stiid_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = ' WHERE fi.stiid_status > 7 && fi.stiid_status < 13';
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
        $countQuery = "SELECT COUNT(1) FROM finascop_stock_item_inventorydetails fi "
                . "INNER JOIN finascop_purchase_order ON fpo_id = stiid_fpoid INNER JOIN finascop_stock_item_inventorydetails_status fs ON fs.stiid_status = fi.stiid_status INNER JOIN finascop_branch ON br_ID = cpd_branch_id {$search}{$where}{$searchitem}";
        $listQuery = "SELECT stiid_id,br_Name,fpo_id,fpo_vendorName,fpo_poNumber,DATE_FORMAT(fpo_poDate,'%d-%m-%Y') AS fpo_poDate,stiid_barcode,stiid_itemmastername,"
                . "DATE_FORMAT(stiid_expirydate,'%d-%m-%Y') AS stiid_expirydate,stiid_batchno,cpd_branch_id,br_Name,stiid_description FROM finascop_stock_item_inventorydetails fi "
                . "INNER JOIN finascop_purchase_order ON fpo_id = stiid_fpoid INNER JOIN finascop_stock_item_inventorydetails_status fs ON fs.stiid_status = fi.stiid_status INNER JOIN finascop_branch ON br_ID = cpd_branch_id {$search}{$where}{$searchitem} ORDER BY {$sort} {$dir} LIMIT $start,$limit ";

        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getSalesReturnReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'rtrqo_id' : $sort;
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
        if ($sort == 'order_created_on') {
                $sort = 'rtrqo_id';
                }
        if ($_POST['current_branch_id'] > 0) {
            $current_branch_id = $_POST['current_branch_id'];
        } else {
            $current_branch_id = $db->getItemSafe("SELECT br_ID FROM finascop_branch WHERE br_Name = ?", "s", [$_POST['br_Name']]);
        }
        switch ($sort) {

            default :
                $order .= "ORDER BY CAST({$sort} as char) {$dir},binary {$sort} {$dir}";
                break;
        }
        if ($_POST['br_Name'] != '') {
            $filter_qry .= "AND br_Name  LIKE  '" . $_POST['br_Name'] . "%'";
        }


        $query = " SELECT rtrqo_id,order_order_id,order_packedbags_count,order_customer_id,order_branch_id,br_Name,bco.status_id as status,DATE_FORMAT(created_at,'%d-%m-%Y') AS order_created_on,TIME_FORMAT(cast(created_at as time),'%r') as ordertime,admin_description AS order_status,
            (SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = order_customer_id) AS delivery_to,(SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = order_customer_id) AS cust_mobile,order_itemReturnRequestCount,
            order_HasReturn,order_ItemsReturned,order_ReturnVerified,(SELECT SUM(item_order_qty) FROM retaline_customer_order_items WHERE customer_order_id = bco.order_id) as order_qty
            FROM finascop_stock_return_request_order INNER JOIN retaline_customer_order bco ON bco.order_id = finascop_stock_return_request_order.order_id
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id  "
                . " WHERE 1 = 1 and bco.status_id > 0 AND rtrqo_type = 0  GROUP BY bco.order_id "; //AND order_branch_id = {$current_branch_id}
        $countQuery = " SELECT COUNT(*) FROM ({$query}) AS orerCount {$filter_qry}   ORDER BY  {$sort} {$dir} ";
        $listQuery = "SELECT * FROM({$query}) as orderList  {$filter_qry}  ORDER BY  {$sort} {$dir} limit " . $start . "," . $limit;
        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getStockLostDetailsReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'stiid_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = ' WHERE fi.stiid_status IN (20,21)';
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

        $countQuery = "SELECT COUNT(1) FROM finascop_stock_item_inventorydetails fi "
                . "INNER JOIN finascop_purchase_order ON fpo_id = stiid_fpoid INNER JOIN finascop_stock_item_inventorydetails_status fs ON fs.stiid_status = fi.stiid_status INNER JOIN finascop_branch ON br_ID = cpd_branch_id {$search}{$where}{$searchitem}";
        $listQuery = "SELECT stiid_id,br_Name,fpo_id,fpo_vendorName,fpo_poNumber,DATE_FORMAT(fpo_poDate,'%d-%m-%Y') AS fpo_poDate,stiid_barcode,stiid_itemmastername,"
                . "DATE_FORMAT(stiid_expirydate,'%d-%m-%Y') AS stiid_expirydate,stiid_batchno,cpd_branch_id,br_Name,stiid_description FROM finascop_stock_item_inventorydetails fi "
                . "INNER JOIN finascop_purchase_order ON fpo_id = stiid_fpoid INNER JOIN finascop_stock_item_inventorydetails_status fs ON fs.stiid_status = fi.stiid_status INNER JOIN finascop_branch ON br_ID = cpd_branch_id {$search}{$where}{$searchitem} ORDER BY {$sort} {$dir} LIMIT $start,$limit ";

        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function handler($op) {
        global $db;
        switch ($op) {
            case 'getStockReturnReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getStockReturnReportGridData($_POST);

                $datas = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);

                $resCount = count($datas);
                if (!empty($datas)) {

                    for ($i = 0; $i < $resCount; $i++) {
                        $datas[$i]['stit_SKU'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['rtrqod_item_id']}");
                        $datas[$i]['returnOrders'] = $returnOrders;
                    }

                    echo '{"totalCount":' . $count . ',"data":' . json_encode($datas) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'StockReturnReportsexportexcel':
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
                $qry = $this->_getStockReturnReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getStockLostReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getStockLostReportGridData($_POST);

                $datas = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);

                $resCount = count($datas);
                if (!empty($datas)) {

                    echo '{"totalCount":' . $count . ',"data":' . json_encode($datas) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'StockLostReportsexportexcel':
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
                $qry = $this->_getStockLostReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getReturnDetailsReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getReturnDetailsReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'ReturnDetailsReportsexportexcel':
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
                $qry = $this->_getReturnDetailsReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getSalesReturnReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getSalesReturnReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);
                
                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'SalesReturnReportsexportexcel':
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
                $qry = $this->_getSalesReturnReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getStockLostDetailsReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getStockLostDetailsReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'StockLostDetailsReportsexportexcel':
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
                $qry = $this->_getStockLostDetailsReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
        }
    }

}

$obj = new stockReturnReportGeneration($db);
$obj->handler($op);



