<?php

global $db;
$userid = $_SESSION['admin']->Finascop_UserId;
switch ($op) {
    case 'saveDeliveryRules':

        $data = array_filter($_POST);
        $rdrdata['rdr_deliveryMode'] = $data['rdr_deliveryMode'];
        $rdrdata['rdr_calculationMode'] = $data['rdr_calculationMode'];
        $rdrdata['rdr_fixedRateperkm'] = $data['rdr_fixedRateperkm'];
        $rdrdata['rdr_fixedRateMin'] = $data['rdr_fixedRateMin'];
        $rdrdata['rdr_fixedRateMax'] = $data['rdr_fixedRateMax'];
        $rdrdata['rdr_ruleFor'] = $data['rdr_ruleFor'];
        $rdrdata['rdr_ruleName'] = $data['rdr_ruleName'];
        $rdrdata['rdr_isFreeDelivery'] = $data['rdr_isfreeDeliveryCbx'];
        if ($data['rdr_isfreeDeliveryAmt'] > 0) {
            $rdrdata['rdr_isfreeDeliveryAmt'] = $data['rdr_isfreeDeliveryAmt'];
        } else {
            $rdrdata['rdr_isfreeDeliveryAmt'] = 0;
        }

        $rdrdata['rdr_ruleForId'] = $data['rdr_ruleForId'];
        $rdrdata['rdr_fromkm1'] = $data['rdr_fromkm1'];
        $rdrdata['rdr_tokm1'] = $data['rdr_tokm1'];
        $rdrdata['rdr_amt1'] = $data['rdr_amt1'];
        $rdrdata['rdr_fromkm2'] = $data['rdr_fromkm2'];
        $rdrdata['rdr_tokm2'] = $data['rdr_tokm2'];
        $rdrdata['rdr_amt2'] = $data['rdr_amt2'];
        $rdrdata['rdr_fromkm3'] = $data['rdr_fromkm3'];
        $rdrdata['rdr_tokm3'] = $data['rdr_tokm3'];
        $rdrdata['rdr_amt3'] = $data['rdr_amt3'];

        $rdrdata['rdr_createdOn'] = date('Y-m-d H:i:s');
        $rdrdata['rdr_createdBy'] = $userid;


        $rdrdata = array_filter($rdrdata);
        $db->query('begin');
        $status = $db->perform(FINASCOP_DB . "retaline_delivery_rules", $rdrdata);
        $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Delivery Charge Saved'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data' }";
        }
        break;
    case 'storeForGroupStre':
        $type = $_POST['type'];
        switch ($type) {
            case 2:
                $qry = "select store_group_id as id,store_group_name as name from finascop_branch_group where status = 1 order by store_group_name";
                $data = $db->getMultipleData($qry, true);
                break;
            case 3:
                $qry = "select br_ID as id,CONCAT(br_Name ,'-',branch_shortname) as name from  finascop_branch where br_status = 'Active' order by br_Name";
                $data = $db->getMultipleData($qry, true);
                break;
            case 4:
                $qry = "select id,areaName as name from  area_entries  order by areaName";
                $data = $db->getMultipleData($qry, true);
                break;
        }
        if (!empty($data)) {
            echo json_encode($data);
        } else
            echo [];
        break;
    case 'listDeliveryRules':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'is_default' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
                                $dmIds = implode(',', $dmIds);
                                $search .= " and (rdr_deliveryMode IN({$dmIds})) ";
                            } else {
                                if ($val['data']['value'] == 'Courier Delivery') {
                                    $fiterItem = 1;
                                    $search .= " and (rdr_deliveryMode = {$fiterItem}) ";
                                } else if ($val['data']['value'] == 'Hyperlocal Delivery') {
                                    $fiterItem = 2;
                                    $search .= " and (rdr_deliveryMode = {$fiterItem}) ";
                                } else if ($val['data']['value'] == 'Scheduled Local Delivery') {
                                    $fiterItem = 3;
                                    $search .= " and (rdr_deliveryMode = {$fiterItem}) ";
                                } else if ($val['data']['value'] == 'Local Last Mile Delivery') {
                                    $fiterItem = 4;
                                    $search .= " and (rdr_deliveryMode = {$fiterItem}) ";
                                } else if ($val['data']['value'] == 'Parcel Delivery') {
                                    $fiterItem = 5;
                                    $search .= " and (rdr_deliveryMode = {$fiterItem}) ";
                                } else if ($val['data']['value'] == 'Cargo Delivery') {
                                    $fiterItem = 6;
                                    $search .= " and (rdr_deliveryMode = {$fiterItem}) ";
                                } else if ($val['data']['value'] == 'Manual Delivery') {
                                    $fiterItem = 7;
                                    $search .= " and (rdr_deliveryMode = {$fiterItem}) ";
                                }
                            }
                        }
                        if ($val['field'] == 'rdr_calculationModeName') {
                            $checkComa = strstr($val['data']['value'], ',');
                            if ($checkComa != '') {
                                $fiterItem = $val['data']['value'];
                                $cmItems = explode(',', $fiterItem);
                                $cmItemsCount = count($cmItems);
                                $cdmIds = array();
                                for ($di = 0; $di < $cmItemsCount; $di++) {
                                    switch ($cmItems[$di]) {
                                        case 'Distance':
                                            array_push($cdmIds, 1);
                                            break;
                                        case 'Fixed':
                                            array_push($cdmIds, 2);
                                            break;
                                    }
                                }
                                $cdmIds = implode(',', $cdmIds);
                                $search .= " and (rdr_calculationMode IN({$cdmIds})) ";
                            } else {
                                if ($val['data']['value'] == 'Distance') {
                                    $fiterItem = 1;
                                    $search .= " and (rdr_calculationMode = {$fiterItem}) ";
                                } else if ($val['data']['value'] == 'Fixed') {
                                    $fiterItem = 2;
                                    $search .= " and (rdr_calculationMode = {$fiterItem}) ";
                                } else
                                    $search .= " and (rdr_calculationMode=3) ";
                            }
                        }
                        if ($val['field'] == 'rdr_ruleForName') {
                            $checkComa = strstr($val['data']['value'], ',');
                            if ($checkComa != '') {
                                $fiterItem = $val['data']['value'];
                                $rfItems = explode(',', $fiterItem);
                                $rfItemsCount = count($rfItems);
                                $rfIds = array();
                                for ($di = 0; $di < $rfItemsCount; $di++) {
                                    switch ($rfItems[$di]) {
                                        case 'Common Rule':
                                            array_push($rfIds, 1);
                                            break;
                                        case 'Store Group':
                                            array_push($rfIds, 2);
                                            break;
                                        case 'Store':
                                            array_push($rfIds, 3);
                                            break;
                                        case 'Area':
                                            array_push($rfIds, 4);
                                            break;
                                    }
                                }
                                $rfIds = implode(',', $rfIds);
                                $search .= " and (rdr_ruleFor IN({$rfIds})) ";
                            } else {
                                if ($val['data']['value'] == 'Common Rule') {
                                    $fiterItem = 1;
                                    $search .= " and (rdr_ruleFor = {$fiterItem}) ";
                                } else if ($val['data']['value'] == 'Store Group') {
                                    $fiterItem = 2;
                                    $search .= " and (rdr_ruleFor = {$fiterItem}) ";
                                } else if ($val['data']['value'] == 'Store') {
                                    $fiterItem = 3;
                                    $search .= " and (rdr_ruleFor = {$fiterItem}) ";
                                } else if ($val['data']['value'] == 'Area') {
                                    $fiterItem = 4;
                                    $search .= " and (rdr_ruleFor = {$fiterItem}) ";
                                }
                            }
                        }
                        break;
                    case 'string':
                        if ($val['field'] == 'rdr_ruleName') {
                            //                            $rdr_ruleName = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(rdr_id),0) FROM retaline_delivery_rules WHERE rdr_ruleName LIKE '{$val['data']['value']}%' ");
                            $search .= " AND rdr_ruleName  LIKE  '" . $val['data']['value'] . "%'";
                        }
                        if ($val['field'] == 'freeDelivery') {
                            //                            $rdr_ruleName = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(rdr_id),0) FROM retaline_delivery_rules WHERE rdr_ruleName LIKE '{$val['data']['value']}%' ");
                            $search .= " AND rdr_isfreeDeliveryAmt  LIKE  '" . $val['data']['value'] . "%'";
                        } else {
                            $search .= " and ({$val['field']} LIKE '{$val['data']['value']}%') ";
                        }
                        break;
                }
            }
        }
        //            foreach ($filter as $key => $field) {
        //                if ($field['data']['value'] != "") {
        //                    $checkComa = strstr($field['data']['value'], ',');
        //                    if ($checkComa != '') {
        //                        $fiterItem = $field['data']['value'];
        //                        $fiterItem = str_replace(',', "','", $fiterItem);
        //                        $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
        //                    } else if ($field == 'rdr_deliveryModeName') {
        //                    $rdr_deliveryMode = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(rdr_id),0) FROM retaline_delivery_rules WHERE rdr_ruleName LIKE '{$field['data']['value']}%' ");
        //                    $search .= " AND rdr_ruleName IN({$rdr_deliveryMode}) ";
        //                }
        //                    else {
        //                        $search .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
        //                    }
        //                }
        //            }


        $query = "SELECT rdr_id,rdr_ruleName,is_default,rdr_deliveryMode,rdr_calculationMode,rdr_ruleFor,status,
        CASE WHEN rdr_deliveryMode = 1 THEN 'Courier Delivery'  WHEN rdr_deliveryMode = 2 THEN 'Hyperlocal Delivery'  WHEN rdr_deliveryMode = 3 THEN 'Scheduled Local Delivery'   WHEN rdr_deliveryMode = 4 THEN 'Local Last Mile Delivery'  WHEN rdr_deliveryMode = 5 THEN 'Parcel Delivery' WHEN rdr_deliveryMode = 6 THEN 'Cargo Delivery'  WHEN rdr_deliveryMode = 7 THEN 'Manual Delivery' END AS rdr_deliveryModeName,
        CASE WHEN rdr_calculationMode = 1 THEN 'Distance' WHEN rdr_calculationMode = 2 THEN 'Fixed' WHEN rdr_calculationMode = 3 THEN 'Grozeo' WHEN rdr_calculationMode = 4 THEN 'Weight' WHEN rdr_calculationMode = 5 THEN 'Zone' END AS rdr_calculationModeName,
        CASE WHEN rdr_ruleFor = 1 THEN 'Common Rule' WHEN rdr_ruleFor = 2 THEN 'Store Group' WHEN rdr_ruleFor = 3 THEN 'Store' WHEN rdr_ruleFor = 4 THEN 'Area' END AS rdr_ruleForName,
        rdr_isfreeDelivery,IF(rdr_isfreeDelivery = 1,rdr_isfreeDeliveryAmt,0) AS freeDelivery,rdr_isfreeDeliveryAmt,CASE WHEN rdr_ruleFor = 1 THEN '-' WHEN rdr_ruleFor = 2 THEN (SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = rdr_ruleForId) WHEN rdr_ruleFor = 3 THEN (SELECT br_Name FROM finascop_branch WHERE br_ID = rdr_ruleForId) WHEN rdr_ruleFor = 4 THEN (SELECT areaName FROM area_entries WHERE id = rdr_ruleForId) END AS ruleForName,
        CASE WHEN rdr_ruleFor = 1 THEN '-' WHEN rdr_ruleFor = 2 THEN '-' WHEN rdr_ruleFor = 3 THEN (SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = (SELECT br_storeGroup FROM finascop_branch WHERE br_ID = rdr_ruleForId)) WHEN rdr_ruleFor = 4 THEN '-' END storeGroupName,
        CASE WHEN status = 1 THEN 'Active' WHEN status = 0 THEN 'Inactive' END statusName FROM retaline_delivery_rules ";
        //$countQuery = "SELECT COUNT(*) FROM retaline_delivery_rules  {$search}";
        $countQuery = "SELECT COUNT(*) FROM ({$query}) as countQry  {$search}";

        $listQuery = " SELECT * FROM ({$query}) AS listQry {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'setDRDefault':
        $rdr_id = $_POST['rdr_id'];
        $deliveryMode = $_POST['rdr_deliveryMode'];
        switch ($deliveryMode) {
            case 'Courier Delivery':
                $rdr_deliveryMode = 1;
                break;
            case 'Express Delivery':
            case 'Hyperlocal Delivery':
                $rdr_deliveryMode = 2;
                break;
            case 'Scheduled Delivery':
            case 'Scheduled Local Delivery':
                $rdr_deliveryMode = 3;
                break;
            case 'Local Last Mile Delivery':
                $rdr_deliveryMode = 4;
                break;
            case 'Parcel Delivery':
                $rdr_deliveryMode = 5;
                break;
            case 'Cargo Delivery':
                $rdr_deliveryMode = 6;
                break;
            case 'Manual Delivery':
                $rdr_deliveryMode = 7;
                break;
        }
        $db->query('begin');
        $defaultCount = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_delivery_rules WHERE is_default = 1 AND rdr_deliveryMode = {$rdr_deliveryMode}");
        if ($defaultCount > 0) {
            $status = $db->query("UPDATE retaline_delivery_rules SET is_default = 0 WHERE rdr_deliveryMode = {$rdr_deliveryMode}");
        }

        $data['is_default'] = 1;
        $data['rdr_updatedOn'] = date("Y-m-d H:i:s");
        $data['rdr_updatedBy'] = $userid;
        $status = $db->perform('retaline_delivery_rules', $data, 'update', " rdr_id = {$rdr_id}");
        $status = $db->query('commit');
        if ($status) {
            $msg = "Default updated.";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'dr_details':
        require(THIS_MODULE_PATH . "/rightpanel.php");
        break;
    case 'deliveryrules_form_load':
        $podata = $db->getFromDB("SELECT rdr_id,rdr_ruleName,rdr_deliveryMode,rdr_calculationMode,rdr_ruleFor,"
            . "CASE WHEN rdr_deliveryMode = 1 THEN 'Courier Delivery'
            WHEN rdr_deliveryMode = 2 THEN 'Express Delivery'
            WHEN rdr_deliveryMode = 3 THEN 'Scheduled Delivery'
        END AS rdr_deliveryModeName,CASE WHEN rdr_calculationMode = 1 THEN 'Distance'
            WHEN rdr_calculationMode = 2 THEN 'Fixed' WHEN rdr_calculationMode = 3 THEN 'Grozeo' WHEN rdr_calculationMode = 4 THEN 'Weight' WHEN rdr_calculationMode = 5 THEN 'Zone' 
        END AS rdr_calculationModeName, CASE WHEN rdr_ruleFor = 1 THEN 'Common Rule'
            WHEN rdr_ruleFor = 2 THEN 'Store Group' WHEN rdr_ruleFor = 3 THEN 'Store'
        END AS rdr_ruleForName,rdr_fixedRateperkm,rdr_fixedRateMin,rdr_fixedRateMax,rdr_fromkm1,rdr_tokm1,rdr_amt1,rdr_amt1,rdr_fromkm2,rdr_tokm2,rdr_amt2,rdr_fromkm3,rdr_tokm3,rdr_amt3,rdr_isfreeDelivery,
        rdr_isfreeDeliveryAmt,rdr_ruleFor,rdr_ruleForId,is_default FROM retaline_delivery_rules WHERE rdr_id ='{$_POST['rdr_id']}'", true);
        if (!empty($podata)) {
            echo json_encode($podata);
        }
        break;
    case 'getDeliverRuleSlabs':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";

        $uuid = $_POST['uuid'];
        $drId = $_POST['drId'];
        if ($drId > 0) {
            $search .= " AND drId = {$drId} ";
        } else {
            $search .= " AND uuid = '{$uuid}' ";
        }
        $countQuery = "SELECT COUNT(*) FROM delivery_rule_slab  {$search}";

        $listQuery = "SELECT id,slabType,slabKm,slabAmount,weight,zoneId,
        if(zoneId > 0,(SELECT name FROM delivery_zone  WHERE id = zoneId),'-') AS zoneName,
        CASE WHEN slabType = 1 THEN 'Upto' WHEN slabType = 2 THEN 'Above' WHEN slabType = 3 THEN 'Next' END AS slabTypeName FROM delivery_rule_slab  {$search}  ORDER BY {$sort} {$dir} ";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'saveRevisedDeliveryRulesOld':

        $data = array_filter($_POST);
        $uuid = $_POST['uuid'];
        $griddata = json_decode(stripslashes($_POST['distanceSlabs']));
        $griddata = (array) $griddata;
        $rdrdata['rdr_deliveryMode'] = $data['rdr_deliveryMode'];
        $rdrdata['rdr_calculationMode'] = $data['rdr_calculationMode'];
        if ($data['rdr_calculationMode'] == 2) {
            $rdrdata['rdr_fixedRateperkm'] = $data['rdr_fixedRate'];
        }
        $rdrdata['rdr_fixedRateMin'] = $data['rdr_minCharge'];
        $rdrdata['rdr_fixedRateMax'] = $data['rdr_maxCharge'];
        $rdrdata['rdr_fromkm1'] = $data['rdr_maxDistnce'];
        $rdrdata['rdr_tokm1'] = $data['rdr_maxWt'];
        $rdrdata['rdr_ruleFor'] = $data['rdr_ruleFor'];
        $rdrdata['rdr_ruleName'] = $data['rdr_ruleName'];
        $rdrdata['rdr_isFreeDelivery'] = $data['rdr_isfreeDeliveryCbx'];

        $rdrdata['rdr_isfreeDeliveryAmt'] = 0;

        $rdrdata['rdr_ruleForId'] = $data['rdr_ruleForId'];

        $rdrdata['rdr_amt1'] = $data['rdr_Ratekm'];

        $rdrdata['rdr_createdOn'] = date('Y-m-d H:i:s');
        $rdrdata['rdr_createdBy'] = $userid;


        $rdrdata = array_filter($rdrdata);
        $db->query('begin');
        $status = $db->perform("retaline_delivery_rules", $rdrdata);
        $drId = $db->insert_id();
        $dsdata['drId'] = $drId;
        if (count($griddata)) {
            $slabDatas = $supportdb->getMulipleData("SELECT * FROM distance_slab_tmp WHERE uuid = '{$uuid}'", true);
            foreach ($slabDatas as $slabData) {
                $dsdata['slabType'] = $slabData['slabType'];
                $dsdata['slabKm'] = $slabData['slabKm'];
                $dsdata['slabAmount'] = $slabData['slabAmount'];
                $status = $db->perform("delivery_rule_slab", $dsdata);
            }
            $supportdb->query("DELETE FROM distance_slab_tmp WHERE uuid = '{$uuid}'");
        }
        $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Delivery Charge Saved'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data' }";
        }
        break;
    case 'saveRevisedDeliveryRules':

        $data = array_filter($_POST);
        $uuid = $_POST['uuid'];
        $griddata = json_decode(stripslashes($_POST['distanceSlabs']));
        $griddata = (array) $griddata;
        $rdrdata['rdr_deliveryMode'] = $data['rdr_deliveryMode'];
        $rdrdata['rdr_calculationMode'] = $data['rdr_calculationMode'];
        if ($data['rdr_calculationMode'] == 2) {
            $rdrdata['rdr_fixedRateperkm'] = $data['rdr_fixedRateperkm'];
            $rdrdata['rdr_fixedRateMin'] = $data['rdr_fixedRateMin'];
            $rdrdata['rdr_fixedRateMax'] = $data['rdr_fixedRateMax'];
            if ($data['rdr_isfreeDeliveryAmt'] > 0) {
                $rdrdata['rdr_isfreeDeliveryAmt'] = $data['rdr_isfreeDeliveryAmt'];
                $rdrdata['rdr_isFreeDelivery'] = 1;
            } else {
                $rdrdata['rdr_isfreeDeliveryAmt'] = 0;
                $rdrdata['rdr_isFreeDelivery'] = 0;
            }
        }

        if ($data['rdr_calculationMode'] == 1) {
            $rdrdata['rdr_fixedRateMin'] = $data['rdr_minCharge'];
            $rdrdata['rdr_amt2'] = $data['rdr_maxCharge'];
            $rdrdata['rdr_isfreeDeliveryAmt'] = 0;
            $rdrdata['rdr_isFreeDelivery'] = 0;
        }


        $rdrdata['rdr_ruleFor'] = $data['rdr_ruleFor'];
        $rdrdata['rdr_ruleName'] = $data['rdr_ruleName'];

        $rdrdata['rdr_ruleForId'] = $data['rdr_ruleForId'];

        $rdrdata['rdr_createdOn'] = date('Y-m-d H:i:s');
        $rdrdata['rdr_createdBy'] = $userid;


        $rdrdata = array_filter($rdrdata);
        $db->query('begin');
        $status = $db->perform("retaline_delivery_rules", $rdrdata);
        $drId = $db->insert_id();
        $dsdata['drId'] = $drId;
        if (count($griddata)) {
            $slabDatas = $supportdb->getMulipleData("SELECT * FROM distance_slab_tmp WHERE uuid = '{$uuid}' ORDER BY id ASC", true);
            foreach ($slabDatas as $slabData) {
                $dsdata['weight'] = $slabData['weight'];
                $dsdata['zoneId'] = $slabData['zoneId'];
                $dsdata['slabType'] = $slabData['slabType'];
                $dsdata['slabKm'] = $slabData['slabKm'];
                $dsdata['slabAmount'] = $slabData['slabAmount'];
                $dsdata = array_filter($dsdata);
                $status = $db->perform("delivery_rule_slab", $dsdata);
            }
            $supportdb->query("DELETE FROM distance_slab_tmp WHERE uuid = '{$uuid}'");
        }
        $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Delivery Charge Saved'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data' }";
        }
        break;
    case 'saveDRSlabs':
        $type = $_POST['type'];
        switch ($type) {
            case '1':
                $data['slabKm']  = $_POST['slabKm'];
                break;
            case '2':
                $data['weight']  = $_POST['weight'];
                break;
            case '3':
                $data['weight']  = $_POST['weight'];
                $data['zoneId']  = $_POST['zoneId'];
                break;
        }
        $data['uuid']  = $_POST['uuid'];
        $data['slabType']  = $_POST['slabType'];
        $data['slabAmount']  = $_POST['slabAmount'];
        $supportdb->query('begin');
        $status = $supportdb->perform("distance_slab_tmp", $data);
        $supportdb->query('commit');
        if ($status) {
            echo "{success: true,msg:'Delivery Charge Saved'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data' }";
        }
        break;
    case 'deleteDistanceSlab':
        $dsId = $_POST['dsId'];
        $supportdb->query('begin');

        $status = $supportdb->query("DELETE FROM distance_slab_tmp WHERE id = {$dsId}");
        $status = $supportdb->query('commit');
        if ($status) {
            $msg = "Entry removed";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'tmplistDeliverRuleSlabs':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";

        $uuid = $_POST['uuid'];
        $drId = $_POST['drId'];
        if ($drId > 0) {
            $search .= " AND drId = {$drId} ";
        } else {
            $search .= " AND uuid = '{$uuid}' ";
        }
        $countQuery = "SELECT COUNT(*) FROM distance_slab_tmp  {$search}";

        $listQuery = "SELECT id,slabType,slabKm,slabAmount,weight,zoneId,
        CASE WHEN slabType = 1 THEN 'Upto' WHEN slabType = 2 THEN 'Above' WHEN slabType = 3 THEN 'Next' END AS slabTypeName FROM distance_slab_tmp  {$search}  ORDER BY {$sort} {$dir} ";

        $supportdb->printGridJson($countQuery, $listQuery);
        break;
    case 'zoneStre':
        $qry = "select id,name from  delivery_zone  WHERE storegroupId = 0 order by name  ";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo json_encode($data);
        } else
            echo [];
        break;
}
