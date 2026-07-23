<?php

class PrdctEntryReportGeneration
{
    public function _getPrdctImageEntryReportGridData($postvar){

        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = ' WHERE 1=1 AND  isMedicine = 0';
        $filter = $_POST['filter'];
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        if (isset($filter)) {
            if (isset($_POST['filter'])) {
        $allowedFields = ['date_from', 'date_to', 'br_id', 'item_category', 'item_type', 'status'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }else if ($val['data']['value'] == 'Inactive') {
                                $search .= " and stit_status = 0 ";
                            } else {
                                $search .= " and stit_status IN(1,0) ";
                            }
                        } else if ($val['field'] == 'isVerified') {
                            if ($val['data']['value'] == 'Yes') {
                                $search .= " and (isVerified = 1) ";
                            } else if ($val['data']['value'] == 'No') {
                                $search .= " and (isVerified = 0) ";
                            } else {
                                $search .= " and (isVerified IN(1,0)) ";
                            }
                        } else if ($val['field'] == 'enteredBy') {
                            $search .= " and CONCAT(FirstName,' ',LastName) ( LIKE '{$val['data']['value']}%') ";
                        } else {
                            $search .= " and ({$val['field']} LIKE '{$val['data']['value']}%') ";
                        }
                        break;
                    case 'list':
                        if ($val['field'] == 'isVerified') {
                            if ($val['data']['value'] == 'Yes') {
                                $search .= " and (isVerified = 'Yes') ";
                            } else if ($val['data']['value'] == 'No') {
                                $search .= " and (isVerified = 'No') ";
                            } else {
                                $search .= "  ";
                            }
                        } else {
                            $search .= " and ({$val['field']} LIKE '{$val['data']['value']}%') ";
                        }
                        break;
                    case 'numeric':
                        if ($val['field'] == 'tax') {
                            $search .= " AND stit_GST " . $comparisons[$val['data']['comparison']] . " " . $val['data']['value'];
                        } else {
                            $search .= " AND " . $val['field'] . " " . $comparisons[$val['data']['comparison']] . " " . $val['data']['value'];
                        }
                        break;
                    case 'date':
                        switch ($val['data']['comparison']) {
                            case 'gt':
                                $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') > '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                break;
                            case 'lt':
                                $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') < '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                break;
                            case 'eq':
                                $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') = '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                break;
                        }

                        break;
                }
            }
        }

        if (!empty($_POST['search_prdexp_from_date']) && !empty($_POST['search_prdexp_to_date'])) {
            $search .= " and DATE_FORMAT(created_at, '%Y-%m-%d') BETWEEN '" . date('Y-m-d', strtotime($_POST['search_prdexp_from_date'])) . "' AND '" . date('Y-m-d', strtotime($_POST['search_prdexp_to_date'])) . "'";
        }

        $qry = "SELECT  id,stit_ID ,CONCAT(FirstName,' ',LastName) AS enteredBy ,stit_itemName,created_at,
        stit_SKU,stit_category_name,finascop_stock_item_images.createdBy,stit_brand_name,image_type
        from  finascop_stock_item_images INNER JOIN finascop_stock_itemmaster ON stit_ID = product_id        
        INNER JOIN finascop_usr_profile ON  UserId = finascop_stock_item_images.createdBy";

        $countQuery = "SELECT COUNT(*) from  finascop_stock_item_images INNER JOIN finascop_stock_itemmaster ON stit_ID = product_id 
        INNER JOIN finascop_usr_profile ON  UserId = finascop_stock_item_images.createdBy
          {$search} order by {$sort} {$dir}";
        $listQuery = "{$qry}  {$search} order by {$sort} {$dir} limit {$start},{$limit} ";

        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }
    public function _getPrdctExportLogReportGridData($postvar)
    {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = ' WHERE 1=1 AND  isMedicine = 0';
        $filter = $_POST['filter'];
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        if (isset($filter)) {
            if (isset($_POST['filter'])) {
        $allowedFields = ['date_from', 'date_to', 'br_id', 'item_category', 'item_type', 'status'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }else if ($val['data']['value'] == 'Inactive') {
                                $search .= " and stit_status = 0 ";
                            } else {
                                $search .= " and stit_status IN(1,0) ";
                            }
                        } else if ($val['field'] == 'isVerified') {
                            if ($val['data']['value'] == 'Yes') {
                                $search .= " and (isVerified = 1) ";
                            } else if ($val['data']['value'] == 'No') {
                                $search .= " and (isVerified = 0) ";
                            } else {
                                $search .= " and (isVerified IN(1,0)) ";
                            }
                        } else if ($val['field'] == 'enteredBy') {
                            $search .= " and CONCAT(FirstName,' ',LastName) ( LIKE '{$val['data']['value']}%') ";
                        } else {
                            $search .= " and ({$val['field']} LIKE '{$val['data']['value']}%') ";
                        }
                        break;
                    case 'list':
                        if ($val['field'] == 'isVerified') {
                            if ($val['data']['value'] == 'Yes') {
                                $search .= " and (isVerified = 'Yes') ";
                            } else if ($val['data']['value'] == 'No') {
                                $search .= " and (isVerified = 'No') ";
                            } else {
                                $search .= "  ";
                            }
                        } else {
                            $search .= " and ({$val['field']} LIKE '{$val['data']['value']}%') ";
                        }
                        break;
                    case 'numeric':
                        if ($val['field'] == 'tax') {
                            $search .= " AND stit_GST " . $comparisons[$val['data']['comparison']] . " " . $val['data']['value'];
                        } else {
                            $search .= " AND " . $val['field'] . " " . $comparisons[$val['data']['comparison']] . " " . $val['data']['value'];
                        }
                        break;
                    case 'date':
                        switch ($val['data']['comparison']) {
                            case 'gt':
                                $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') > '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                break;
                            case 'lt':
                                $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') < '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                break;
                            case 'eq':
                                $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') = '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                break;
                        }

                        break;
                }
            }
        }

        if (!empty($_POST['search_prdexp_from_date']) && !empty($_POST['search_prdexp_to_date'])) {
            $search .= " and DATE_FORMAT(enteredOn, '%Y-%m-%d') BETWEEN '" . date('Y-m-d', strtotime($_POST['search_prdexp_from_date'])) . "' AND '" . date('Y-m-d', strtotime($_POST['search_prdexp_to_date'])) . "'";
        }

        $qry = "select  stit_ID ,CONCAT(FirstName,' ',LastName) AS enteredBy ,
        stit_SKU,stit_category_name,IF((isVerified = 1),'Yes','No') AS isVerified,verifedOn,enteredOn
        from  finascop_stock_itemmaster INNER JOIN product_export_log on product_stitId = stit_ID INNER JOIN finascop_usr_profile ON  UserId = enteredBy   ";

        $countQuery = "SELECT COUNT(*) FROM  finascop_stock_itemmaster INNER JOIN product_export_log on product_stitId = stit_ID INNER JOIN finascop_usr_profile ON  UserId = enteredBy
          {$search} order by {$sort} {$dir}";
        $listQuery = "{$qry}  {$search} order by {$sort} {$dir} limit {$start},{$limit} ";

        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }
    public function _getPrdctEntryReportGridData($postvar)
    {
        global $db;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $sort = trim($postvar['sort']);
        $dir = trim($postvar['dir']);
        $sort = empty($sort) ? 'stit_ID' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = ' WHERE 1=1';
        $filter = $_POST['filter'];
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        if (isset($filter)) {
            if (isset($_POST['filter'])) {
        $allowedFields = ['date_from', 'date_to', 'br_id', 'item_category', 'item_type', 'status'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }else if ($val['data']['value'] == 'Inactive') {
                                $search .= " and stit_status = 0 ";
                            } else {
                                $search .= " and stit_status IN(1,0) ";
                            }
                        } else if ($val['field'] == 'isVerified') {
                            if ($val['data']['value'] == 'Yes') {
                                $search .= " and (isVerified = 1) ";
                            } else if ($val['data']['value'] == 'No') {
                                $search .= " and (isVerified = 0) ";
                            } else {
                                $search .= " and (isVerified IN(1,0)) ";
                            }
                        } else {
                            $search .= " and ({$val['field']} LIKE '{$val['data']['value']}%') ";
                        }
                        break;
                    case 'list':
                        if ($val['field'] == 'isVerified') {
                            if ($val['data']['value'] == 'Yes') {
                                $search .= " and (isVerified = 'Yes') ";
                            } else if ($val['data']['value'] == 'No') {
                                $search .= " and (isVerified = 'No') ";
                            } else {
                                $search .= "  ";
                            }
                        } else {
                            $search .= " and ({$val['field']} LIKE '{$val['data']['value']}%') ";
                        }
                        break;
                    case 'numeric':
                        if ($val['field'] == 'tax') {
                            $search .= " AND stit_GST " . $comparisons[$val['data']['comparison']] . " " . $val['data']['value'];
                        } else {
                            $search .= " AND " . $val['field'] . " " . $comparisons[$val['data']['comparison']] . " " . $val['data']['value'];
                        }
                        break;
                    case 'date':
                        switch ($val['data']['comparison']) {
                            case 'gt':
                                $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') > '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                break;
                            case 'lt':
                                $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') < '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                break;
                            case 'eq':
                                $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') = '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                break;
                        }

                        break;
                }
            }
        }

        if (!empty($_POST['search_prdent_from_date']) && !empty($_POST['search_prdent_to_date'])) {
            switch ($_POST['actionType']) {
                case 'Add':
                    $search .= " and DATE_FORMAT(createdOn, '%Y-%m-%d') BETWEEN '" . date('Y-m-d', strtotime($_POST['search_prdent_from_date'])) . "' AND '" . date('Y-m-d', strtotime($_POST['search_prdent_to_date'])) . "'";
                    break;
                case 'Edit':
                    $search .= " and DATE_FORMAT(updatedOn, '%Y-%m-%d') BETWEEN '" . date('Y-m-d', strtotime($_POST['search_prdent_from_date'])) . "' AND '" . date('Y-m-d', strtotime($_POST['search_prdent_to_date'])) . "'";
                    break;
                case 'Verify':
                    $search .= " and DATE_FORMAT(verifedOn, '%Y-%m-%d') BETWEEN '" . date('Y-m-d', strtotime($_POST['search_prdent_from_date'])) . "' AND '" . date('Y-m-d', strtotime($_POST['search_prdent_to_date'])) . "'";
                    break;
            }
        }

        $qry = "select  stit_ID ,createdOn,(SELECT CONCAT(FirstName,'',LastName) AS createdBy FROM finascop_usr_profile WHERE UserId = createdBy) as createdBy,
        (SELECT CONCAT(FirstName,'',LastName) AS updatedBy FROM finascop_usr_profile WHERE UserId = updatedBy) as updatedBy,updatedOn,
        stit_SKU,stit_category_name,IF((isVerified = 1),'Yes','No') AS isVerified,verifedOn,(SELECT CONCAT(FirstName,'',LastName) AS verifedBy FROM finascop_usr_profile WHERE UserId = verifedBy) as verifedBy "
            . " from  finascop_stock_itemmaster where isMedicine = 0   ";

        $countQuery = "SELECT COUNT(*) FROM ({$qry}) AS countItem  {$search} order by {$sort} {$dir}";
        $listQuery = "SELECT * FROM ({$qry}) AS listItem  {$search} order by {$sort} {$dir} limit {$start},{$limit} ";

        return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
    }
    public function handler($op)
    {
        global $db;
        switch ($op) {
            case 'getPrdctEntryReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getPrdctEntryReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'prdctEntryReportsexportexcel':
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
                $qry = $this->_getPrdctEntryReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'listItemMasterData':
                $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
                $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
                $sort = empty($sort) ? 'stit_ID' : $sort;
                $dir = empty($dir) ? 'DESC' : $dir;
                $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
                $search = " WHERE 1=1 ";
                if (isset($filter)) {
                    if (isset($_POST['filter'])) {
        $allowedFields = ['date_from', 'date_to', 'br_id', 'item_category', 'item_type', 'status'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }%' ");
                                        $search .= " AND stit_HSNCode IN({$hsn_code}) ";
                                    } else */

                                if ($val['field'] == 'statusName') {
                                    if ($val['data']['value'] == 'Active') {
                                        $search .= " and stit_status = 1 ";
                                    } else if ($val['data']['value'] == 'Inactive') {
                                        $search .= " and stit_status = 0 ";
                                    } else {
                                        $search .= " and stit_status IN(1,0) ";
                                    }
                                } else if ($val['field'] == 'isVerified') {
                                    if ($val['data']['value'] == 'Yes') {
                                        $search .= " and (isVerified = 1) ";
                                    } else if ($val['data']['value'] == 'No') {
                                        $search .= " and (isVerified = 0) ";
                                    } else {
                                        $search .= " and (isVerified IN(1,0)) ";
                                    }
                                } else {
                                    $search .= " and ({$val['field']} LIKE '{$val['data']['value']}%') ";
                                }
                                break;
                            case 'list':
                                if ($val['field'] == 'isVerified') {
                                    if ($val['data']['value'] == 'Yes') {
                                        $search .= " and (isVerified = 'Yes') ";
                                    } else if ($val['data']['value'] == 'No') {
                                        $search .= " and (isVerified = 'No') ";
                                    } else {
                                        $search .= "  ";
                                    }
                                } else {
                                    $search .= " and ({$val['field']} LIKE '{$val['data']['value']}%') ";
                                }
                                break;
                            case 'numeric':
                                if ($val['field'] == 'tax') {
                                    $search .= " AND stit_GST " . $comparisons[$val['data']['comparison']] . " " . $val['data']['value'];
                                } else {
                                    $search .= " AND " . $val['field'] . " " . $comparisons[$val['data']['comparison']] . " " . $val['data']['value'];
                                }
                                break;
                            case 'date':
                                switch ($val['data']['comparison']) {
                                    case 'gt':
                                        $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') > '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                        break;
                                    case 'lt':
                                        $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') < '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                        break;
                                    case 'eq':
                                        $search .= " and DATE_FORMAT(" . $val['field'] . ", '%Y%m%d') = '" . date('Ymd', strtotime($val['data']['value'])) . "'";
                                        break;
                                }

                                break;
                        }
                    }
                }
                $countQuery = "SELECT count(*)
                    from " . FINASCOP_DB . "finascop_stock_itemmaster {$search} AND isMedicine = 0 order by {$sort} {$dir}";
                //$count = $db->getItemFromDB($countQuery);
                $total = "select SUM(stit_MRP) AS total_mrp,SUM(stit_GST) as tax_total "
                    . " from " . FINASCOP_DB . "finascop_stock_itemmaster  order by stit_ID desc";
                $coltotal = $db->getFromDB($total, true);
                $qry = "SELECT  stit_ID AS ItemId ,product_is_home,stit_displaylabel, stit_itemName ,stit_status,least_package_type_id,least_package_type_name,product_category,
                stit_HSN_code,stit_package_type_namme,stit_category_name,stit_brand_name,stit_product_variant,stit_quantity,stit_GST AS tax, stit_SKU,
                finascop_stock_itemmaster.createdOn AS createdOn,CONCAT(FirstName,'',LastName) AS createdBy,IF(stit_status = 1,'Active','Inactive') AS statusName,
                IF((isVerified = 1),'Yes','No') AS isVerified, 
                @miCount:=(SELECT COUNT(1) FROM finascop_stock_item_images WHERE `product_id`= stit_ID AND `image_type` = 1 ), 
                @aiCount:=(SELECT COUNT(1) FROM finascop_stock_item_images WHERE `product_id`= stit_ID AND `image_type` = 0),
                IF(mrp.mrps> 0,'Yes','No') AS hasMrp,
                CONCAT(@miCount, '/',@aiCount) AS imgCount 
                FROM finascop_stock_itemmaster LEFT JOIN finascop_usr_profile ON UserId = createdBy 
                LEFT JOIN (SELECT stit_id AS stitid, COUNT(*) AS mrps FROM item_mrp GROUP BY stit_id ) mrp ON mrp.stitid = finascop_stock_itemmaster.stit_ID
                WHERE isMedicine = 0  ";
                $count = $db->getItemFromDB("SELECT COUNT(*) FROM ({$qry}) AS countItem  {$search} order by {$sort} {$dir}");
                $listQuery = "SELECT * FROM ({$qry}) AS listItem  {$search} order by {$sort} {$dir} limit {$rec_start},{$rec_limit}";
                $data = $db->getMultipleData($listQuery, $listQuery);
                $result = [];
                foreach ($data as $key => $value) {

                    foreach ($coltotal as $k => $v) {

                        $value[$k] = $v;
                        $result[$key] = $value;
                    }
                }

                echo '{"totalCount":' . $count . ',"data":' . json_encode($result) . '}';
                break;
            case 'getPrdctExportLogReportGridData':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getPrdctExportLogReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
            case 'prdctExportLogReportsexportexcel':
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
                $qry = $this->_getPrdctExportLogReportGridData($_POST);
                //print_r($qry['listQuery']);
                $_SESSION['Export']['Query'] = $qry['listQuery'];
                $_SESSION['Export']['Settings']['title'] = date('d-M-y') . "_";
                _exportExcelReport($_POST);
                break;
            case 'getImageLogReport':
                $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
                $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

                $qry = $this->_getPrdctImageEntryReportGridData($_POST);

                $data = $db->getMultipleData($qry['listQuery'], true);

                $count = $db->getItemFromDB($qry['countQuery']);


                if (!empty($data)) {
                    echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
                } else {
                    echo '{"totalCount":"0","data":[]}';
                }
                break;
        }
    }
}

$obj = new PrdctEntryReportGeneration($db);
$obj->handler($op);
