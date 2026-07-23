<?php

class GeneralReportGeneration {

    public function _getSuppliersReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'stpa_Fname' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $filter_query = ' 1=1';
        $br_id = $_SESSION['admin']->finascop_current_branch_id;

        if (count($postvar['filter']) > 0) {


            $filter = $postvar['filter'];
            foreach ($filter as $key => $val) {

                $type = $val['data']['type'];
                $value = $val['data']['value'];

                $field = $val['field'];
                switch ($field) {

                    case 'stpa_Fname':
                        $filter_query .= " AND stpa_Fname LIKE '%" . $value . "%' ";
                        break;
                    case 'stpa_Lname':
                        $filter_query .= " AND stpa_Lname LIKE '%" . $value . "%' ";
                        break;
                    case 'stpa_GSTIN':
                        $filter_query .= " AND stpa_GSTIN LIKE '%" . $value . "%' ";
                        break;
                    case 'stpa_City':
                        $filter_query .= " AND stpa_City LIKE '%" . $value . "%' ";
                        break;
                    case 'stpa_PINCODE':
                        $filter_query .= " AND stpa_PINCODE LIKE '%" . $value . "%' ";
                        break;
                    case 'st_name':
                        $filter_query .= " AND dst_Id IN (SELECT d.dst_Id FROM " . FINASCOP_DB . " finascop_district d INNER JOIN " . FINASCOP_DB . " finascop_state b ON b.st_ID = d.st_Id WHERE b.st_name LIKE '%" . $value . "%')";
                        break;
                    case 'dst_Name':
                        $filter_query .= " AND dst_Id IN (select dst_Id from " . FINASCOP_DB . "finascop_district  where dst_Name LIKE '%" . $value . "%' ) ";
                        break;
                    default:
                        $filter_query .= " AND {$field} LIKE '%" . $value . "%' ";
                        break;
                }
            }
        }


        $countQuery = "SELECT COUNT(*) FROM " . FINASCOP_DB . " finascop_stock_party a where br_id = {$br_id} and a.stpa_IsVendor=1 and {$filter_query} ";
        $listQuery = "select stpa_id as customerId, stpa_Fname as stpa_Fname,stpa_Lname as stpa_Lname,stpa_Address as stpa_Address,stpa_ContactPerson as stpa_ContactPerson,stpa_MobileNo as stpa_MobileNo,stpa_Email as stpa_Email,stpa_PanNo as stpa_PanNo ,stpa_City as stpa_City,stpa_PINCODE as stpa_PINCODE,stpa_GSTIN as stpa_GSTIN,"
                . "(select st_name from " . FINASCOP_DB . "finascop_state b inner join " . FINASCOP_DB . " finascop_district d on b.st_ID = d.st_Id where d.dst_Id = a.dst_Id)as st_name,"
                . "(select b.st_ID from " . FINASCOP_DB . "finascop_state b inner join " . FINASCOP_DB . " finascop_district d on b.st_ID = d.st_Id where d.dst_Id = a.dst_Id)as st_id,"
                . "(select c.dst_Id from " . FINASCOP_DB . " finascop_district c where c.dst_Id = a.dst_Id )as dst_Id,"
                . "(select dst_Name from " . FINASCOP_DB . " finascop_district c where c.dst_Id = a.dst_Id )as dst_Name,stpa_dlno1,stpa_dlno2,stpa_fssaino"
                . "  from " . FINASCOP_DB . " finascop_stock_party a where br_id = {$br_id} and a.stpa_IsVendor=1 and {$filter_query} order by {$sort} {$dir} limit {$start},{$limit}"; // limit " . $start . "," . $limit;


        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getCustomersReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'cust_customer_name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $filter_qry = ' WHERE 1=1 ';
        if (isset($postvar['filter'])) {
            $filter = $postvar['filter'];
            foreach ($filter as $key => $val) {
                switch ($val['data']['type']) {
                    case 'string':
                        if ($val['field'] == 'cust_customer_name') {
                            $filter_qry .= " AND cust_customer_name  LIKE  '" . $val['data']['value'] . "%'";
                        } else if ($val['field'] == 'cust_mobile') {
                            $filter_qry .= " AND cust_mobile  LIKE  '" . $val['data']['value'] . "%'";
                        } else if ($val['field'] == 'cust_email') {
                            $filter_qry .= " AND cust_email  LIKE  '" . $val['data']['value'] . "%'";
                        } else if ($val['field'] == 'cust_ref_code') {
                            $filter_qry .= " AND cust_ref_code  LIKE  '" . $val['data']['value'] . "%'";
                        } else if ($val['field'] == 'cust_walletbalance') {
                            $filter_qry .= " AND cust_walletbalance  LIKE  '" . $val['data']['value'] . "%'";
                        } else if ($val['field'] == 'cust_prom_reward_point') {
                            $filter_qry .= " AND cust_prom_reward_point  LIKE  '" . $val['data']['value'] . "%'";
                        } else if ($val['field'] == 'cust_status') {
                            $filter_qry .= " AND cust_status  LIKE  '" . $val['data']['value'] . "%'";
                        }
                        break;
                }
            }
        }

        $countQuery = "SELECT COUNT(*) "
                . " FROM retaline_customer {$filter_qry}";


        $listQuery = "SELECT cust_id,cust_customer_id,cust_customer_name,cust_mobile,cust_email,cust_walletbalance,cust_prom_reward_point,cust_status,cust_ref_code,cust_branch_id from retaline_customer {$filter_qry}"
                . " ORDER BY {$sort} {$dir} LIMIT {$start},{$limit} ";

        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getDeliveryResourceReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'd_Name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        // SECURITY: use buildSafeFilterQuery
        $allowedFields = ['date_from', 'date_to', 'br_id', 'report_type'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        /* RAW (disabled): if (isset($_POST['filter']) && $_POST['filter'] != '') { */
            foreach ($_POST['filter'] as $key => $v) {
                switch ($v['data']['type']) {
                    case 'string':
                        if ($v['field'] == 'branch') {
                            $v['field'] = 'br_id';
                            $qry = "select br_ID from finascop_branch where br_Name "
                                    . " like'" . $v['data']['value'] . "%' and br_id=qugeo_driver.br_id";
                            $filterCon .= " and qugeo_driver.br_id in(" . $qry . ") ";
                            //$search .= " and ({$v['field']} = {$fiterCon}) ";
                        } else if ($v['field'] == 'address') {
                            $filterCon .= " and d_Add1 like '%" . $v['data']['value'] . "%'";
                            $search .= " and (d_Add1 LIKE '{$field['data']['value']}%') ";
                        } else {
                            $filterCon .= " and " . $v['field'] . " like '%" . $v['data']['value'] . "%'";
                            $search .= " and ({$v['field']} LIKE '{$field['data']['value']}%') ";
                        }
                        break;
                    case 'list':
                        if ($v['field'] == 'd_isallowAutoSchedule') {
                            if ($v['field'] == 'd_isallowAutoSchedule') {
                                $fiterCon = ($v['data']['value'] == 'Yes') ? 1 : 0;
                                $search .= " and ({$v['field']} = {$fiterCon}) ";
                            } else if ($field['data']['value'] == 'No') {
                                $fiterCon = 0;
                                $search .= " and ({$v['field']} = {$fiterCon}) ";
                            } else {
                                // $search .= " and ({$v['field']} LIKE '{$field['data']['value']}%') ";
                                $search .= " and (d_isallowAutoSchedule = 1 or d_isallowAutoSchedule=0) ";
                            }
                        }

                        if ($v['field'] == 'd_isallowManualSchedule') {
                            if ($v['field'] == 'd_isallowManualSchedule') {
                                $fiterCon = ($v['data']['value'] == 'Yes') ? 1 : 0;
                                $search .= " and ({$v['field']} = {$fiterCon}) ";
                            } else if ($field['data']['value'] == 'No') {
                                $fiterCon = 0;
                                $search .= " and ({$v['field']} = {$fiterCon}) ";
                            } else {
                                //    $search .= " and ({$v['field']} LIKE '{$field['data']['value']}%') ";
                                $search .= " and (d_isallowAutoSchedule = 1 or d_isallowAutoSchedule=0) ";
                            }
                        }

                        break;
                }
            }
        }

        $countQuery = "select count(d_id) from qugeo_driver {$search} " . $filterCon;


        $listQuery = "select d_ID, d_Name,concat_ws(',',d_Add1,d_Add2,d_Add3) as address,
        d_Ph1,(select br_Name from finascop_branch where br_id=qugeo_driver.br_id) 
        as branch,IF((d_isallowAutoSchedule=1),'Yes','No')  as d_isallowAutoSchedule,  IF((d_isallowManualSchedule=1),'Yes','No')  as d_isallowManualSchedule from qugeo_driver {$search} {$filterCon} 
	 order by {$sort} {$dir} limit $start,$limit";

        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getCreditAgeReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'bbso_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 AND DATEDIFF(NOW(),bbso_validDate) > 0";
        if (isset($_POST['filter']) && $_POST['filter'] != '') {
            foreach ($_POST['filter'] as $key => $v) {
                switch ($v['data']['type']) {
                    case 'string':

                        $filterCon .= " and " . $v['field'] . " like '%" . $v['data']['value'] . "%'";
                        $search .= " and ({$v['field']} LIKE '{$field['data']['value']}%') ";
                        break;
                    case 'list':

                        break;
                }
            }
        }

        $countQuery = "select count(bbso_id) from retaline_B2B_SalesOrder {$search} " . $filterCon;


        $listQuery = "SELECT bbso_id,b2b_Customer_Name,bbso_SONumber,bbso_SODate,bbso_SOValue,bbso_validDate,bbso_InvNumber,bbso_InvDate,DATEDIFF(NOW(),bbso_validDate) AS delayDay FROM retaline_B2B_SalesOrder {$search} {$filterCon} 
	 order by {$sort} {$dir} limit $start,$limit";

        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function _getSuppliersProductsReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'stpi_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $filter_query = ' 1=1';

        if (count($postvar['filter']) > 0) {


            $filter = $postvar['filter'];
            foreach ($filter as $key => $val) {

                $type = $val['data']['type'];
                $value = $val['data']['value'];

                $field = $val['field'];
                $filter_query .= " AND {$field} LIKE '%" . $value . "%' ";
            }
        }

        $query = "SELECT stpi_id,stit_type,IF(stit_type = 1,'Medicine','Product') AS itemType,stit_id AS itemId,
IF(stit_type = 1,(SELECT stit_SKU FROM finascop_stock_itemmaster WHERE finascop_stock_itemmaster.stit_id= finascop_stock_party_items.stit_id AND isMedicine=1),
(SELECT stit_SKU FROM finascop_stock_itemmaster WHERE finascop_stock_itemmaster .stit_id= finascop_stock_party_items.stit_id AND isMedicine=0)) AS itemName,
(SELECT stpa_Fname FROM finascop_stock_party WHERE finascop_stock_party_items.stpa_id = finascop_stock_party.stpa_id) AS supplier
FROM finascop_stock_party_items";
        $countQuery = "SELECT COUNT(*) FROM ({$query}) AS supplierItems where   {$filter_query} ";
        $listQuery = "SELECT * FROM ({$query}) AS supplierItems  WHERE  {$filter_query} order by {$sort} {$dir} limit {$start},{$limit}"; // limit " . $start . "," . $limit;


        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }
    
    public function _getB2BCustomersReportGridData($postvar) {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'b2b_Customer_Name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $filter_qry = ' WHERE 1=1 ';
        if (isset($postvar['filter'])) {
            $filter = $postvar['filter'];
            foreach ($filter as $key => $val) {
                switch ($val['data']['type']) {
                    case 'string':
                        $filter_qry .= " AND {$val['field']}  LIKE  '" . $val['data']['value'] . "%'";
                        break;
                }
            }
        }

        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . "retaline_B2Bcustomer a INNER JOIN finascop_branch b ON b.br_ID = a.br_ID  {$filter_qry}";


        $listQuery = "SELECT b2b_Customer_ID,b2b_Customer_Name,br_Name,"
                . "b2b_Customer_Address,b2b_Customer_Email,b2b_Customer_Phone,"
                . "b2b_Customer_status,b2b_Customer_pincode,b2b_Customer_Mobile,b2b_Customer_Incharge,"
                . "b2b_Customer_Lat,b2b_Customer_Lng,rbsch_name,b2b_Customer_gst,b2b_Customer_dlno1,b2b_Customer_dlno2,b2b_Customer_fssaino "
                . "FROM " . FINASCOP_DB . "retaline_B2Bcustomer a INNER JOIN finascop_branch b ON b.br_ID = a.br_ID  {$filter_qry}"
                . " ORDER BY {$sort} {$dir} LIMIT {$start},{$limit} ";

        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }

    public function handler($op) {
        global $db;
        switch ($op) {
            case 'getSuppliersReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getSuppliersReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'SupplierReportsexportexcel':
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
                $qry = $this->_getSuppliersReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getCustomerReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getCustomersReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'CustomerReportsexportexcel':
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
                $qry = $this->_getCustomersReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getDeliveryResourceReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getDeliveryResourceReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'DeliveryResourceReportsexportexcel':
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
                $qry = $this->_getDeliveryResourceReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getCreditAgeReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getCreditAgeReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'CreditAgeReportsexportexcel':
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
                $qry = $this->_getCreditAgeReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getSupplierProductsReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getSuppliersProductsReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'SupplierProductReportsexportexcel':
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
                $qry = $this->_getSuppliersProductsReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getB2BCustomerReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getB2BCustomersReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'B2BCustomerReportsexportexcel':
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
                $qry = $this->_getB2BCustomersReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
        }
    }

}

$obj = new GeneralReportGeneration($db);
$obj->handler($op);



