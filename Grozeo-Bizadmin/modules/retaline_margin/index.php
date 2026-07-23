<?php

$userid = $_SESSION['admin']->Finascop_UserId;
switch ($op) {
    case 'listRetalimMarginDetail':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'bmd_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['rm_id', 'rm_category', 'rm_margin', 'rm_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM retaline_margindistributions {$search}";
        $listQuery = "SELECT bmd_id,bmd_name,IF((status=1),'Active','Inactive') AS status,is_default FROM retaline_margindistributions"
                . " {$search} ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";
        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'MargindetailsView':

        $bmd_id = isset($_POST['bmd_id']) ? intval($_POST['bmd_id']) : 0;
        if ($bmd_id) {

            $data = $db->getFromDB("SELECT bmd_id,bmd_name,status,bmd_company,bmd_hub,bmd_incentive,bmd_technology,bmd_customer,bmd_cs,bmd_distributor,bmd_retailor,bmd_management,bmd_promotion,"
                    . "bmd_logistics,bmd_driver,bmd_courier,bmd_pickup,bmd_bank FROM retaline_margindistributions  WHERE bmd_id =" . $bmd_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'saveMargin':
        $db->query('begin');
        $data = array(
            "bmd_name" => $_POST['bmd_name'],
            "bmd_management" => 0,
            "bmd_company" => $_POST['bmd_company'],
            "bmd_hub" => $_POST['bmd_hub'],
            "bmd_distributor" => $_POST['bmd_distributor'],
            "bmd_retailor" => $_POST['bmd_retailor'],
            "bmd_technology" => 0,
            "bmd_promotion" => 0,
            "bmd_incentive" => $_POST['bmd_incentive'],
            "bmd_logistics" => 0,
            "bmd_driver" => $_POST['bmd_driver'],
            "bmd_courier" => $_POST['bmd_courier'],
            "bmd_pickup" => 0,
            "bmd_bank" => 0,
            "bmd_customer" => $_POST['bmd_customer'],
            "status" => $_POST['status'],
        );

        $data['created_on'] = date('Y-m-d H:i:s');
        $data['created_by'] = $userid;
        $adzone_name = $db->perform('retaline_margindistributions', $data);
        $lastId = $db->insert_id();

        $return_rec = $db->getFromDb("SELECT bmd_id,bmd_name,status FROM retaline_margindistributions WHERE bmd_id = {$lastId}", true);
        $adzone_name = $db->query('commit');
        if ($adzone_name) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'setDefault':
        $bmd_id = $_POST['bmd_id'];
        $db->query('begin');
        $db->query("UPDATE retaline_margindistributions SET is_default = 0");
        $data['is_default'] = 1;
        $data['status'] = 1;
        $data['updated_on'] = date("Y-m-d H:i:s");
        $data['updated_by'] = $userid;
        $status = $db->perform('retaline_margindistributions', $data, 'update', " bmd_id = {$bmd_id}");
//        $dmh['bmd_id'] = $bmd_id;
//        $dmh['bmd_updatedBy'] = $userid;
//        $dmh['bmd_updatedDate'] = date("Y-m-d H:i:s");
//        $status = $db->perform('brm_marginDistributionHistory', $dmh);
        $status = $db->query('commit');
        if ($status) {
            $msg = "Saved as Default";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'listRetalimMarginDetailB2B':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'bmd_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['rm_id', 'rm_category', 'rm_margin', 'rm_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM retaline_margindistributionsb2b {$search}";
        $listQuery = "SELECT bmd_id,bmd_name,IF((status=1),'Active','Inactive') AS status,is_default FROM retaline_margindistributionsb2b"
                . " {$search} ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'MargindetailsViewB2B':
        $bmd_id = isset($_POST['bmd_id']) ? intval($_POST['bmd_id']) : 0;
        if ($bmd_id) {

            $data = $db->getFromDB("SELECT bmd_id,bmd_name,status,bmd_company,bmd_cs,bmd_distributor,bmd_management FROM retaline_margindistributionsb2b  WHERE bmd_id =" . $bmd_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'saveMarginB2B':
        $db->query('begin');
        $data = array(
            "bmd_name" => $_POST['bmd_name'],
            "bmd_management" => $_POST['bmd_management'],
            "bmd_company" => $_POST['bmd_company'],
            "bmd_cs" => $_POST['bmd_hub'],
            "bmd_distributor" => $_POST['bmd_distributor'],
            "status" => $_POST['status'],
        );

        $data['created_on'] = date('Y-m-d H:i:s');
        $data['created_by'] = $userid;
        $adzone_name = $db->perform('retaline_margindistributionsb2b', $data);
        $lastId = $db->insert_id();

        $return_rec = $db->getFromDb("SELECT bmd_id,bmd_name,status FROM retaline_margindistributionsb2b WHERE bmd_id = {$lastId}", true);
        $adzone_name = $db->query('commit');
        if ($adzone_name) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'setDefaultB2B':
        $bmd_id = $_POST['bmd_id'];
        $db->query('begin');
        $db->query("UPDATE retaline_margindistributionsb2b SET is_default = 0");
        $data['is_default'] = 1;
        $data['status'] = 1;
        $data['updated_on'] = date("Y-m-d H:i:s");
        $data['updated_by'] = $userid;
        $status = $db->perform('retaline_margindistributionsb2b', $data, 'update', " bmd_id = {$bmd_id}");
        $status = $db->query('commit');
        if ($status) {
            $msg = "Saved as Default";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'listRetalinMarginItemMappping':
        //'rmim_stit_id', 'rmim_isMedicine', 'rmim_bmd_id', 'rmim_createdOn', 'rmim_updatedOn', 'stit_SKU', 'itemType', 'itemBC', 'itemMargin'
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'rmim_stit_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        $sort = ($sort == 'itemType')? 'rmim_isMedicine': $sort;
        $dir = ($sort == 'rmim_isMedicine') ? ($dir=='ASC') ? 'DESC' : 'ASC' : $dir;
        $isListQryUN = ($sort == 'itemBC');
        $sort = ($sort == 'itemBC')? 'max_score': $sort;
        $sort = ($sort == 'itemMargin')? 'bmd_name': $sort;
        if (isset($_POST['filter']) && $_POST['filter'] != '') {
            foreach ($_POST['filter'] as $key => $v) {
                if (array_key_exists($v['field'], $fields))
                    $field = $fields[$v['field']];
                else {
                    $field = $v['field'];
                }
                switch ($v['data']['type']) {
                    case 'list':
                        if ($field == 'itemType') {
                            $search .= (($search == "") ? " where " : " and ") . 'rmim_isMedicine' . " = '" . ($v['data']['value'] == 'Medicine' ? '1' : '0') . "'";
                        } else {
                            $search .= (($search == "") ? " where " : " and ") . $field . " like '" . $v['data']['value'] . "%'";
                        }
                        break;
                        
                    case 'string':
                        if ($field == 'stit_SKU') {
                    $stit_SKU = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(rmim_stit_id),0) FROM retaline_margin_item_mapping WHERE rmim_stit_id IN (SELECT (stit_ID) FROM finascop_stock_itemmaster WHERE stit_SKU LIKE '{$v['data']['value']}%' )");
                    $search .= " AND rmim_stit_id IN({$stit_SKU}) ";
                        } else if ($field == 'itemBC') {
                    $itemBC = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(rmim_stit_id),0) FROM retaline_margin_item_mapping WHERE rmim_stit_id IN (SELECT (stit_ID) FROM finascop_stock_itemmaster WHERE stit_brand_name LIKE '{$v['data']['value']}%' OR dosform_name LIKE '{$v['data']['value']}%')");
                    $search .= " AND rmim_stit_id IN({$itemBC}) ";
                        } else if ($field == 'itemMargin') {
                    $itemMargin = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(rmim_stit_id),0) FROM retaline_margin_item_mapping WHERE rmim_bmd_id IN (SELECT (bmd_id) FROM retaline_margindistributionsb2b WHERE bmd_name LIKE '{$v['data']['value']}%' )");
                    $search .= " AND rmim_stit_id IN({$itemMargin}) ";
                        } else {
                            $search .= (($search == "") ? " where " : " and ") . $field . " like '" . $v['data']['value'] . "%'";
                        }
                        break;
                }
            }
        }
        $countQuery = "SELECT COUNT(*) FROM retaline_margin_item_mapping {$search}";
if($isListQryUN){
    $listQuery = "SELECT rmim_stit_id,rmim_isMedicine,rmim_bmd_id,rmim_createdOn,rmim_updatedOn,stit_SKU,dosform_name,stit_brand_name,bmd_id,bmd_name, stit_brand_name AS max_score FROM retaline_margin_item_mapping a
 INNER JOIN finascop_stock_itemmaster b ON b.stit_ID = a.rmim_stit_id INNER JOIN retaline_margindistributionsb2b c 
 ON c.bmd_id = a.rmim_bmd_id {$search} AND rmim_isMedicine = 0 "
    . "UNION SELECT rmim_stit_id,rmim_isMedicine,rmim_bmd_id,rmim_createdOn,rmim_updatedOn, stit_SKU,dosform_name,stit_brand_name,bmd_id,bmd_name, dosform_name AS max_score 
 FROM retaline_margin_item_mapping d INNER JOIN finascop_stock_itemmaster e ON e.stit_ID = d.rmim_stit_id 
 INNER JOIN retaline_margindistributionsb2b f 
ON f.bmd_id = d.rmim_bmd_id {$search} AND rmim_isMedicine = 1 " 
    . "ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";
}
else{
    $listQuery = "SELECT rmim_stit_id,rmim_isMedicine,rmim_bmd_id,rmim_createdOn,rmim_updatedOn, stit_SKU,dosform_name,stit_brand_name,bmd_id,bmd_name FROM retaline_margin_item_mapping a INNER JOIN finascop_stock_itemmaster b ON b.stit_ID = a.rmim_stit_id INNER JOIN retaline_margindistributionsb2b c ON c.bmd_id = a.rmim_bmd_id {$search} ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";
}
//        echo $listQuery;
//        exit;
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        $count = $db->getItemFromDB($countQuery);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $itemDetails = $db->getFromDB("SELECT stit_SKU,dosform_name,stit_brand_name FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['rmim_stit_id']}", true);
                $datas[$i]['stit_SKU'] = $itemDetails['stit_SKU'];
                if ($datas[$i]['rmim_isMedicine'] == 0) {
                    $itemType = 'Product';
                    $itemBC = $itemDetails['stit_brand_name'];
                } else {
                    $itemType = 'Medicine';
                    $itemBC = $itemDetails['dosform_name'];
                }
                $datas[$i]['itemType'] = $itemType;
                $datas[$i]['itemBC'] = $itemBC;
                $datas[$i]['itemMargin'] = $db->getItemFromDB("SELECT bmd_name FROM retaline_margindistributionsb2b WHERE bmd_id = {$datas[$i]['rmim_bmd_id']}");
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
            //echo json_encode($qry);
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        break;
    case 'MarginItemdetailsView':
        $bmd_id = isset($_POST['bmd_id']) ? intval($_POST['bmd_id']) : 0;
        if ($bmd_id) {

            $data = $db->getFromDB("SELECT bmd_id,bmd_name,status,bmd_company,bmd_cs,bmd_distributor,bmd_management FROM retaline_margindistributionsb2b  WHERE bmd_id =" . $bmd_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'listMarginItemSearch':

        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $item_name = $_POST['currentItem'];
        $item_id = $_POST['current_type'];
        $cond = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['rm_id', 'rm_category', 'rm_margin', 'rm_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
        //1:Medicine,2:Product
        switch ($item_id) {
            case 1:

                if ($item_name != '') {
                    $cond .= " AND dosform_name LIKE '%{$item_name}%'";
                }
                $countQuery = "SELECT COUNT(stit_itemId) FROM finascop_stock_itemmaster  {$cond} AND isMedicine=1 AND stit_status = 1";
                $count = $db->getItemFromDB($countQuery);

                $qry = "SELECT stit_itemName,stit_ID,dosform_name as brand,stit_SKU,'Medicine' as type  FROM finascop_stock_itemmaster {$cond} AND isMedicine=1 AND stit_status = 1 ORDER BY stit_SKU ASC";
                $data = $db->getMultipleData($qry, true);
                break;
            case 2:
                if ($item_name != '') {
                    $cond .= " AND stit_brand_name  LIKE '%{$item_name}%'";
                }
                $countQuery = "SELECT COUNT(stit_itemId) FROM finascop_stock_itemmaster  {$cond} AND isMedicine=0 AND stit_status = 1";
                $count = $db->getItemFromDB($countQuery);

                $qry = "SELECT stit_itemName,stit_ID,stit_brand_name as brand,stit_SKU,'Product' as type  FROM finascop_stock_itemmaster {$cond} AND isMedicine=0 AND stit_status = 1 ORDER BY stit_SKU ASC";
                $data = $db->getMultipleData($qry, true);
                break;
        }
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'getB2bMargins':

        $qry = $db->getMulipleData("SELECT bmd_id,bmd_name FROM retaline_margindistributionsb2b where is_default = 0 ", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'saveItemMargins':
        $itemar = $_POST['itemarr'];
        $itemMargin = $_POST['itemMargin'];
        $itemType = $_POST['itemType'];
        if ($itemType == 1) {
            $isMedicine = 1;
        } else {
            $isMedicine = 0;
        }

        $itemdecode = json_decode($itemar);
        // print_r($itemdecode);
        $itemcount = count($itemdecode);
        //exit;
        for ($i = 0; $i < $itemcount; $i++) {


            $data = array(
                "rmim_isMedicine" => $isMedicine,
                "rmim_stit_id" => $itemdecode[$i],
                "rmim_bmd_id" => $itemMargin
            );
            $itemdup = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_margin_item_mapping WHERE rmim_stit_id = {$itemdecode[$i]}");
            if ($itemdup == 0) {
                $data['rmim_createdOn'] = date('Y-m-d H:i:s');
                $data['rmim_createdBy'] = $_SESSION['admin']->UserId;
                $status = $db->perform(FINASCOP_DB . 'retaline_margin_item_mapping', $data);
            } else {
                $data['rmim_updatedOn'] = date('Y-m-d H:i:s');
                $data['rmim_updatedBy'] = $_SESSION['admin']->UserId;
                $status = $db->perform(FINASCOP_DB . 'retaline_margin_item_mapping', $data, 'update', " rmim_stit_id = {$itemdecode[$i]}");
            }
        }

        if ($status) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
}