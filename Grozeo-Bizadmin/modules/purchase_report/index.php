<?php

class PurchaseReportGeneration {

    public function _getPurchaseReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'fpe_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $br_id = $_SESSION['admin']->finascop_current_branch_id;
        $filter_qry = " WHERE 1 = 1 ";
        // if (isset($_POST['filter'])) {
        if (isset($_POST['filter'])) {
        $allowedFields = ['date_from', 'date_to', 'br_id', 'vendor_id', 'po_status'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }

        $countQuery = "SELECT count(*) from finascop_purchase_entry  {$filter_qry} ";
        $listQuery = "SELECT  fpe_id,fpe_vendorName,fpe_fpoPoNumber,DATE_FORMAT(fpe_fpoPODate,'%d-%m-%Y') as fpe_fpoPODate,fpe_invoiceNumber,DATE_FORMAT(fpe_invoiceDate,'%d-%m-%Y') as fpe_invoiceDate,fpe_grossAmt,fpe_discount,fpe_netQty,fpe_netItems,fpe_netTax,fpe_netAmount,"
                . "ROUND(fpe_netIgst,2) as fpe_netIgst,ROUND(fpe_netCgst,2) as fpe_netCgst,ROUND(fpe_netSgst,2) as fpe_netSgst from finascop_purchase_entry {$filter_qry} ORDER BY {$sort} {$dir} LIMIT $start,$limit ";
        // limit " . $start . "," . $limit;


        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getPurchaseReturnReportGridData($postvar) {
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
        if (isset($data['filter'])) {
        $allowedFields = ['date_from', 'date_to', 'br_id', 'vendor_id', 'po_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }
        $branchName = $_POST['branchName'];
        $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;
        
        //$where = " AND branch_id =" . $br_ID;
        //$selBranchCpd = $db->getItemFromDB("SELECT br_IsCPD FROM finascop_branch WHERE br_ID = {$br_ID}");
        if ($selBranchCpd == 1) {
            $rackStatus = " AND stiid_status = 1";
            $dispatchStatus = " AND stiid_status = 2";
            $receiveStatus = " ";
            $deliverStatus = " ";
        } else {
            $rackStatus = " AND stiid_status = 4";
            $dispatchStatus = " ";
            $receiveStatus = " AND stiid_status = 3";
            $deliverStatus = " AND stiid_status = 5";
        }

        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . " finascop_return_branch_inventory fsb "
                . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID "
                . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$searchitem}";
        $count = $db->getItemFromDB($countQuery);
        $listQuery = "SELECT fsb.stit_id as stit_id,CONCAT(br.br_Name ,'-',br.branch_shortname) as br_Name,fsi.stit_SKU as stit_SKU,fsbg_id,frbi_epr,frbi_leastSKUmrp,frbi_leastSKUepr,"
                . "fsb.item_count as item_count,fsb.mrp as mrp,fsb.selling_price as selling_price,fsb.branch_id as branchId,stit_category_name,stit_quantity,stit_brand_name,stit_product_variant,frbi_status,frbi_id,"
                . "CASE WHEN frbi_status= 1 THEN 'Damaged' WHEN frbi_status= 2 THEN 'Expirable' END AS frbi_statusName,"
                . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stitl1_optimumqty "
                . "WHEN br.br_stockLevel= 2 THEN fsi.stitl2_optimumqty "
                . "ELSE stitl3_optimumqty END AS optimumqty,"
                . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stit11_minimumqty "
                . "WHEN br.br_stockLevel= 2 THEN fsi.stit12_minimumqty "
                . "ELSE stit13_minimumqty END AS minimumqty,"
                . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stit11_maximumqty "
                . "WHEN br.br_stockLevel= 2 THEN fsi.stit13_maximumqty "
                . "ELSE stit13_maximumqty END AS maximumqty "
                . "FROM finascop_return_branch_inventory fsb "
                . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID "
                . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$searchitem} AND fsb.item_count > 0 ORDER BY {$sort} {$dir}  LIMIT $start,$limit";
        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getPurchaseReturnedReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $search = ' WHERE 1=1';
        if (isset($data['filter'])) {
        $allowedFields = ['date_from', 'date_to', 'br_id', 'vendor_id', 'po_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }
        $branchName = $_POST['branchName'];
        $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;
        
        //$where = " AND branch_id =" . $br_ID;
        //$selBranchCpd = $db->getItemFromDB("SELECT br_IsCPD FROM finascop_branch WHERE br_ID = {$br_ID}");
        if ($selBranchCpd == 1) {
            $rackStatus = " AND stiid_status = 1";
            $dispatchStatus = " AND stiid_status = 2";
            $receiveStatus = " ";
            $deliverStatus = " ";
        } else {
            $rackStatus = " AND stiid_status = 4";
            $dispatchStatus = " ";
            $receiveStatus = " AND stiid_status = 3";
            $deliverStatus = " AND stiid_status = 5";
        }

        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . " finascop_return_branch_inventory fsb "
                . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID "
                . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$searchitem} AND item_returned > 0";

        $listQuery = "SELECT fsb.stit_id as stit_id,CONCAT(br.br_Name ,'-',br.branch_shortname) as br_Name,fsi.stit_SKU as stit_SKU,fsbg_id,frbi_epr,"
                . "fsb.item_count as item_count,fsb.mrp as mrp,fsb.selling_price as selling_price,fsb.branch_id as branchId,stit_category_name,stit_quantity,stit_brand_name,stit_product_variant,frbi_status,frbi_id,"
                . "CASE WHEN frbi_status= 1 THEN 'Damaged' WHEN frbi_status= 2 THEN 'Expirable' END AS frbi_statusName,item_returned "
                . "FROM finascop_return_branch_inventory fsb "
                . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID "
                . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$searchitem} AND item_returned > 0 ORDER BY fsb.stit_id asc LIMIT $start,$limit";
        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getPurchaseDetailsReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'fpod_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $br_id = $_SESSION['admin']->finascop_current_branch_id;
        $filter_qry = " WHERE 1 = 1 ";
        // if (isset($_POST['filter'])) {
        if (isset($_POST['filter'])) {
        $allowedFields = ['date_from', 'date_to', 'br_id', 'vendor_id', 'po_status'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }
        if ($sort == 'fpo_poDate') {
            $sort = 'fpo_id';
        }

        $countQuery = "SELECT count(*) from finascop_purchase_order_details  "
                . "INNER JOIN finascop_purchase_order ON fpo_id = fpod_fpoId INNER JOIN mypha_productpackage_type ON package_type_id = fpod_purchasingUnit {$filter_qry} ";
        $listQuery = "SELECT fpod_id,fpo_id,fpo_vendorName,fpo_poNumber,DATE_FORMAT(fpo_poDate,'%d-%m-%Y') AS fpo_poDate,fpod_itemname,fpod_itemqty,fpod_totalqty,fpod_giftname,fpod_giftqty,fpod_itemSmallStockUnit,fpod_itempoqty,"
                . "fpod_leastSKUqty,fpod_leastSKUmrp,fpod_customerRateHmDel,fpod_itemaddidisc,fpod_leastSKUepr,fpod_purchasingUnit,IF(fpod_itemaddidisc > 0,(CONCAT(fpod_itemaddidisc,'',IF(fpod_idiscountcalculus = 'Amount',' Rs',' %'))),'') AS itemDisc,package_type_name FROM finascop_purchase_order_details  "
                . "INNER JOIN finascop_purchase_order ON fpo_id = fpod_fpoId INNER JOIN mypha_productpackage_type ON package_type_id = fpod_purchasingUnit {$filter_qry} ORDER BY {$sort} {$dir} LIMIT $start,$limit ";
        // limit " . $start . "," . $limit;


        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getPurchaseReturnableDetailsReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'stiid_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = ' WHERE fi.stiid_status IN (10,15)';
        if (isset($data['filter'])) {
        $allowedFields = ['date_from', 'date_to', 'br_id', 'vendor_id', 'po_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }

        $countQuery = "SELECT COUNT(1) FROM finascop_stock_item_inventorydetails fi "
                . "INNER JOIN finascop_purchase_order ON fpo_id = stiid_fpoid INNER JOIN finascop_stock_item_inventorydetails_status fs ON fs.stiid_status = fi.stiid_status INNER JOIN finascop_branch ON br_ID = cpd_branch_id {$search}{$where}{$searchitem}";
        $listQuery = "SELECT stiid_id,CONCAT(br_Name ,'-',branch_shortname) as br_Name,fpo_id,fpo_vendorName,fpo_poNumber,DATE_FORMAT(fpo_poDate,'%d-%m-%Y') AS fpo_poDate,stiid_barcode,stiid_itemmastername,"
                . "DATE_FORMAT(stiid_expirydate,'%d-%m-%Y') AS stiid_expirydate,stiid_batchno,cpd_branch_id,stiid_description FROM finascop_stock_item_inventorydetails fi "
                . "INNER JOIN finascop_purchase_order ON fpo_id = stiid_fpoid INNER JOIN finascop_stock_item_inventorydetails_status fs ON fs.stiid_status = fi.stiid_status INNER JOIN finascop_branch ON br_ID = cpd_branch_id {$search}{$where}{$searchitem} ORDER BY {$sort} {$dir} LIMIT $start,$limit ";

        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }
    
    public function _getPurchaseReturnedDetailsReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'stiid_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = ' WHERE fi.stiid_status IN (11,16)';
        if (isset($data['filter'])) {
        $allowedFields = ['date_from', 'date_to', 'br_id', 'vendor_id', 'po_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }

        $countQuery = "SELECT COUNT(1) FROM finascop_stock_item_inventorydetails fi "
                . "INNER JOIN finascop_purchase_order ON fpo_id = stiid_fpoid INNER JOIN finascop_stock_item_inventorydetails_status fs ON fs.stiid_status = fi.stiid_status INNER JOIN finascop_branch ON br_ID = cpd_branch_id {$search}{$where}{$searchitem}";
        $listQuery = "SELECT stiid_id,CONCAT(br_Name ,'-',branch_shortname) as br_Name,fpo_id,fpo_vendorName,fpo_poNumber,DATE_FORMAT(fpo_poDate,'%d-%m-%Y') AS fpo_poDate,stiid_barcode,stiid_itemmastername,"
                . "DATE_FORMAT(stiid_expirydate,'%d-%m-%Y') AS stiid_expirydate,stiid_batchno,cpd_branch_id,stiid_description FROM finascop_stock_item_inventorydetails fi "
                . "INNER JOIN finascop_purchase_order ON fpo_id = stiid_fpoid INNER JOIN finascop_stock_item_inventorydetails_status fs ON fs.stiid_status = fi.stiid_status INNER JOIN finascop_branch ON br_ID = cpd_branch_id {$search}{$where}{$searchitem} ORDER BY {$sort} {$dir} LIMIT $start,$limit ";

        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function handler($op) {
        global $db;
        switch ($op) {
            case 'getPurchaseReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getPurchaseReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'PurchaseReportsexportexcel':
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
                $qry = $this->_getPurchaseReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getPurchaseReturnReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getPurchaseReturnReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'PurchaseReturnReportsexportexcel':
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
                $qry = $this->_getPurchaseReturnReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getPurchaseReturnedReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getPurchaseReturnedReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'PurchaseReturnedReportsexportexcel':
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
                $qry = $this->_getPurchaseReturnedReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getPurchaseDetailsReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getPurchaseDetailsReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'PurchaseDetailsReportsexportexcel':
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
                $qry = $this->_getPurchaseDetailsReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getPurchaseReturnableDetailsReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getPurchaseReturnableDetailsReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'PurchaseReturnableDetailsReportsexportexcel':
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
                $qry = $this->_getPurchaseReturnableDetailsReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getPurchaseReturnedDetailsReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getPurchaseReturnedDetailsReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'PurchaseReturnedDetailsReportsexportexcel':
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
                $qry = $this->_getPurchaseReturnedDetailsReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
        }
    }

}

$obj = new PurchaseReportGeneration($db);
$obj->handler($op);



