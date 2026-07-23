<?php

switch ($op) {
    case 'listHoldOrders':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;

        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'order_id' : $sort;
        //$sort = ($sort=='order_created_on') ? 'DATE(order_created_on)' : $sort;
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


        if ($sort == 'order_created_on') {
            $sort = 'order_id';
        }
        if ($_POST['br_Name'] != '') {
            $filter_qry .= " AND br_Name  LIKE  '" . $_POST['br_Name'] . "%'";
        }
        if ($_SESSION['admin']->br_PyramidLevel == 2) {
            $centralStore = $_SESSION['admin']->finascop_current_branch_id;
            $distributors = $db->getItemFromDB("SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_cpd = {$centralStore}");
            $reatailors = $db->getItemFromDB("SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_cpd IN ({$distributors})");
            $current_branch_id = $reatailors;
        } else {
            $current_branch_id = $_SESSION['admin']->finascop_current_branch_id;
        }


        $query = " SELECT order_id,order_order_id,order_packedbags_count,bco.order_customer_id,order_branch_id,br_Name,bco.status_id as status,DATE_FORMAT(created_at,'%d-%m-%Y') AS order_created_on,
             TIME_FORMAT(cast(created_at as time),'%r') as ordertime,admin_description AS order_status,admin_description,order_payment_gateway_refid,order_payment_gateway_refid_crc32,
            (SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS delivery_to,(SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS cust_mobile,
            order_HasReturn,order_ItemsReturned,order_ReturnVerified,created_at,order_total_amount,payment_mode,order_method
            FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id  WHERE 1 = 1 and bco.status_id IN (27,28,29,30,31,32) "; //and bco.status_id = 22 AND order_branch_id IN ({$current_branch_id}
        $countQuery = $db->getItemFromDB(" SELECT COUNT(*) FROM ({$query}) AS orerCount {$filter_qry} ORDER BY  {$sort} {$dir} ");
        $listQuery = "SELECT * FROM({$query}) as orderList  {$filter_qry}  ORDER BY  {$sort} {$dir} limit " . $start . "," . $limit;
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {

                $orderItems = $db->getItemFromDB("SELECT count(*) FROM retaline_customer_order_items WHERE customer_order_id = {$datas[$i]['order_id']}");
//                $quor_Type = $db->getItemFromDB("SELECT quor_Type FROM qugeo_order WHERE quor_TransferOrder_Type = 1 AND quor_TransferOrder_id = {$datas[$i]['order_id']}");
//                $datas[$i]['quor_DeliveryMethodsAllowed'] = $db->getItemFromDB("SELECT quor_DeliveryMethodsAllowed FROM qugeo_order WHERE quor_TransferOrder_Type = 1 AND quor_TransferOrder_id = {$datas[$i]['order_id']}");
//                $datas[$i]['quor_Type'] = $quor_Type;
                $datas[$i]['poNumber'] = '';
                switch ($datas[$i]['order_method']) {
                    case '1':
                        $datas[$i]['order_methodName'] = 'Deliver';
                        break;
                    case '2':
                        $datas[$i]['order_methodName'] = 'Collect';
                        break;
                    case '3':
                        $datas[$i]['order_methodName'] = 'Courier';
                        break;
                }



                $datas[$i]['itemCount'] = $orderItems;
                $datas[$i]['itemShortCount'] = $orderItems;
            }
            echo '{"totalCount":"', $countQuery, '","data":' . json_encode($datas) . '}';
        }
        break;
    case 'holdorder_details':
        require(THIS_MODULE_PATH . "/order_details.php");
        break;
    case 'mauallyInitiatePO':
        $order_id = $_POST['order_id'];
        $order_branch_id = $db->getItemFromDB("SELECT order_branch_id FROM retaline_customer_order WHERE order_id = {$order_id}");
        $orderDeatils = $db->getMultipleData("SELECT item_product_id,item_order_qty FROM retaline_customer_order_items WHERE customer_order_id = {$order_id}", true);
        $now = date('Y-m-d H:i:s');
        $data['tpopredet_uniqueid'] = getRandomRef();
        $db->query('begin');
        foreach ($orderDeatils as $orderDeatil) {
            $fcpod_vendorid = $db->getItemFromDB("SELECT fcpod_vendorid FROM finascop_contractpo_products WHERE fcpod_itemid = {$orderDeatil['item_product_id']}");
            $asctedbrach_cpr = $db->getItemFromDB("SELECT asctedbrach_cpr FROM finascop_stock_party WHERE stpa_id = {$fcpod_vendorid} AND deliverMode_cpr = 2");
//                    print_r($asctedbrach_cpr);
            if ($asctedbrach_cpr > 0) {
                //tmp_poprereq_detail
                $data['tpopredet_orderBranch'] = $order_branch_id;
                $data['tpopredet_vendorid'] = $fcpod_vendorid;
                $data['tpopredet_itemid'] = $orderDeatil['item_product_id'];
                $tpopredet_orderIds = $db->getItemFromDB("SELECT tpopredet_orderIds FROM tmp_poprereq_details WHERE tpopredet_uniqueid = '{$data['tpopredet_uniqueid']}' AND tpopredet_itemid = {$data['tpopredet_itemid']}");
                $item_order_qty = $db->getItemFromDB("SELECT tpopredet_itemqty FROM tmp_poprereq_details WHERE tpopredet_uniqueid = '{$data['tpopredet_uniqueid']}' AND tpopredet_itemid = {$data['tpopredet_itemid']} AND tpopredet_orderBranch = {$order_branch_id}");
                if (empty($tpopredet_orderIds)) {
                    $data['tpopredet_orderIds'] = $order_id;
                } else {
                    $data['tpopredet_orderIds'] = $tpopredet_orderIds . ',' . $order_id;
                }
                if ($item_order_qty > 0) {
                    $data['tpopredet_itemqty'] = $item_order_qty + $orderDeatil['item_order_qty'];
                } else {
                    $data['tpopredet_itemqty'] = $orderDeatil['item_order_qty'];
                }
                $status = $db->perform('tmp_poprereq_details', $data);
            }
        }
        //Initiated on cut off
        if ($status = 1) {
            $rcodata['updated_at'] = $now;
            $rcodata['status_id'] = 29;
            $status = $db->perform('retaline_customer_order', $rcodata, 'update', " status_id = 27 AND order_id = {$order_id}");
        }


        $vendorOrders = $db->getMultipleData("SELECT GROUP_CONCAT(tpopredet_vendorid) as vendore,tpopredet_orderBranch FROM tmp_poprereq_details WHERE tpopredet_uniqueid = '{$data['tpopredet_uniqueid']}' GROUP BY tpopredet_orderBranch", true);
        foreach ($vendorOrders as $vendorOrder) {
            $vendorIdArray = explode(',', $vendorOrder['vendore']);
            foreach ($vendorIdArray as $vendorId) {
                $vendorItems = $db->getMultipleData("SELECT * FROM tmp_poprereq_details WHERE tpopredet_uniqueid = '{$data['tpopredet_uniqueid']}' AND tpopredet_vendorid = {$vendorId}", true);
                $adhocData['adhoc_name'] = 'PPO' . time();
                $adhocData['adhoc_uniqueid'] = getRandomRef();
                $adhocData['adhoc_vendor'] = $vendorId;
                $adhocData['branch_id'] = $db->getItemFromDB("SELECT br_ID FROM finascop_branch WHERE br_cpd = 0 AND br_PyramidLevel = 1");
                $adhocData['adhoc_potype'] = 2;
                $adhocData['adhoc_billingTo'] = $vendorOrder['tpopredet_orderBranch'];
                $adhocData['adhoc_createdon'] = date("Y-m-d H:i");
                $adhocData['adhoc_updatedon'] = date("Y-m-d H:i");
                $status = $db->perform('finascop_purchase_order_poadhoc', $adhocData);
                foreach ($vendorItems as $vendorItem) {
                    $cprItemRates = $db->getFromDB("SELECT * FROM finascop_contractpo_products WHERE fcpod_itemid = {$vendorItem['tpopredet_itemid']} and fcpod_vendorid = {$vendorId}", true);

                    $dataAdh['fpot_itemOrderIds'] = $vendorItem['tpopredet_orderIds'];
                    $dataAdh['branch_id'] = $adhocData['branch_id'];
                    $dataAdh['fpot_adhocname'] = $adhocData['adhoc_name'];
                    $dataAdh['fpot_uniqueid'] = $adhocData['adhoc_uniqueid'];
                    $dataAdh['fpot_vendorid'] = $vendorId;
                    $dataAdh['fpot_itemid'] = $cprItemRates['fcpod_itemid'];
                    $dataAdh['fpot_itemname'] = $cprItemRates['fcpod_itemname'];
                    $dataAdh['fpot_itemmrp'] = $cprItemRates['fcpod_itemmrp'];
                    $dataAdh['fpot_itemqty'] = $vendorItem['tpopredet_itemqty'];
                    $taxRate = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$dataAdh['fpot_itemid']}");
                    $bmd_id = $db->getItemFromDB("SELECT bmd_id FROM retaline_margindistributions WHERE is_default = 1");
                    $dataAdh['bmd_id'] = $bmd_id;
                    $bmdDetailsb2b = $db->getItemFromDB("SELECT bmd_id FROM retaline_margindistributionsb2b WHERE is_default = 1");
                    $dataAdh['b2bbmd_id'] = $bmdDetailsb2b;

                    $dataAdh['fpot_itemoffrrate'] = $cprItemRates['fcpod_itemoffrrate'];
                    $dataAdh['fpot_itemoffrrateet'] = $cprItemRates['fcpod_itemoffrrateet'];
                    $dataAdh['fpot_amount'] = $dataAdh['fpot_itemqty'] * $dataAdh['fpot_itemoffrrate'];
                    $dataAdh['fpot_netamount'] = $dataAdh['fpot_amount'];

                    $dataAdh['fpot_initialnetamount'] = $dataAdh['fpot_netamount'];
                    $dataAdh['fpot_createdon'] = date("Y-m-d H:i:s");
                    $dataAdh['fpot_totalqty'] = $dataAdh['fpot_itemqty'];
                    $dataAdh['fpot_balanceqty'] = $dataAdh['fpot_totalqty'];

                    $fpot_netamountet = (floatval($dataAdh['fpot_netamount']) * 100) / (100 + floatval($taxRate));
                    $dataAdh['fpot_netamountet'] = round($fpot_netamountet, 2);

                    $fpot_effectiverate = $dataAdh['fpot_netamountet'] / $dataAdh['fpot_totalqty'];
                    $dataAdh['fpot_effectiverate'] = round($fpot_effectiverate, 2);

                    $dataAdh['fpot_pogstAmt'] = $dataAdh['fpot_netamount'] - $dataAdh['fpot_netamountet'];
                    $dataAdh['fpot_netamountTotal'] = $dataAdh['fpot_netamount'];

                    $fpot_itemoffrratech = $dataAdh['fpot_netamount'] / $dataAdh['fpot_itemqty'];
                    $dataAdh['fpot_itemoffrratech'] = round($fpot_itemoffrratech, 2);
                    $fpot_itemoffrrateetch = ($dataAdh['fpot_itemoffrratech'] * 100) / (100 + $taxRate);
                    $dataAdh['fpot_itemoffrrateetch'] = round($fpot_itemoffrrateetch, 2);

                    $dataAdh['fpot_purchasingUnit'] = $cprItemRates['fcpod_purchasingUnit'];

//for margin distributions
                    (float) $eprbft = ((float) $dataAdh['fpot_effectiverate'] / (100 + (float) $taxRate)) * 100;
                    (float) $mrpbft = ((float) $dataAdh['fpot_itemmrp'] / (100 + (float) $taxRate)) * 100;
                    $actmarginDistriPercent = 100 - (($eprbft / $mrpbft) * 100);
                    $marginDistriPercent = round($actmarginDistriPercent);
                    $dataAdh['actual_marginDistri'] = round($actmarginDistriPercent, 2);
                    $dataAdh['bmd_percent'] = $marginDistriPercent;

                    $fpot_effectiverategst = $dataAdh['fpot_pogstAmt'] / $dataAdh['fpot_totalqty'];
                    $dataAdh['fpot_effectiverategst'] = round($fpot_effectiverategst, 2);

                    $dataAdh['fpot_poLandingCost'] = $dataAdh['fpot_effectiverate'] + $dataAdh['fpot_effectiverategst'];
                    $dataAdh['fpot_poMMG'] = $dataAdh['fpot_itemmrp'] - $dataAdh['fpot_poLandingCost'];

                    $qry = "SELECT stdpckl11_package_type_id,stdpckl21_package_type_id,stdpckl2_nos,stdpckl31_package_type_id,stdpckl3_nos,stdpckl41_package_type_id,stdpckl4_nos,stit_GST,csb_package_type_name,cs_nos,"
                            . "cs_package_type_name,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name FROM finascop_stock_itemmaster m WHERE m.stit_ID ='{$dataAdh['fpot_itemid']}' ";
                    $itemHistory = $db->getFromDB($qry, true);
                    $dataAdh['fpot_leastSKUqty'] = $itemHistory['cs_nos'] * $itemHistory['ds_nos'] * $itemHistory['cos_nos'];
                    if ($dataAdh['fpot_purchasingUnit'] == $itemHistory['stdpckl11_package_type_id']) {
                        $fpod_leastSKUepr = $dataAdh['fpot_effectiverate'];
                        $dataAdh['fpot_leastSKUepr'] = round(($fpod_leastSKUepr * 100) / (100 + $itemHistory['stit_GST']), 2);
                    }
                    if ($dataAdh['fpot_purchasingUnit'] == $itemHistory['stdpckl21_package_type_id']) {
                        $fpod_leastSKUepr = $dataAdh['fpot_effectiverate'] / ($itemHistory['stdpckl2_nos']);
                        $dataAdh['fpot_leastSKUepr'] = round(($fpod_leastSKUepr * 100) / (100 + $itemHistory['stit_GST']), 2);
                    }
                    if ($dataAdh['fpot_purchasingUnit'] == $itemHistory['stdpckl31_package_type_id']) {
                        $fpod_leastSKUepr = $dataAdh['fpot_effectiverate'] / ($itemHistory['stdpckl2_nos'] * $itemHistory['stdpckl3_nos']);
                        $dataAdh['fpot_leastSKUepr'] = round(($fpod_leastSKUepr * 100) / (100 + $itemHistory['stit_GST']), 2);
                    }
                    if ($dataAdh['fpot_purchasingUnit'] == $itemHistory['stdpckl41_package_type_id']) {
                        $fpod_leastSKUepr = $dataAdh['fpot_effectiverate'] / ($itemHistory['stdpckl2_nos'] * $itemHistory['stdpckl3_nos'] * $itemHistory['stdpckl4_nos']);
                        $dataAdh['fpot_leastSKUepr'] = round(($fpod_leastSKUepr * 100) / (100 + $itemHistory['stit_GST']), 2);
                    }


                    $dataAdh['fpot_poLandingCost'] = $dataAdh['fpot_effectiverate'] + $dataAdh['fpot_effectiverategst'];
                    $dataAdh['fpot_poMMG'] = $dataAdh['fpot_itemmrp'] - $dataAdh['fpot_poLandingCost'];
                    $status = $db->perform('finascop_purchase_order_temp', $dataAdh);
                }
            }
        }

        $dataRco['updated_at'] = $now;
        $dataRco['status_id'] = 30;
        $status = $db->perform('retaline_customer_order', $dataRco, 'update', " status_id = 29 AND order_id = {$order_id}");
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'PO Created Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while deleting data' }}";
        }
        break;
    case 'removeHold';
        $order_id = $_POST['order_id'];
        $now = date('Y-m-d H:i:s');
        
        $db->query('begin');
        $dataRco['updated_at'] = $now;
        $dataRco['status_id'] = 34;
        $status = $db->perform('retaline_customer_order', $dataRco, 'update', " status_id = 27 AND order_id = {$order_id}");
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Order hold removed.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while deleting data' }}";
        }
        break;
}