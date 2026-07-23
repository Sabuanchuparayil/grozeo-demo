<?php
require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/includes/config.php');
require_once(EXTERNAL_LIBRARY_PATH);
require_once(INCLUDE_PATH . "/finascop_common_functions.php");

switch ($op) {
    case 'listTransferOrderData':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'fsto_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'string':
                        if ($field['field'] == 'fsto_source') {
                            $fsto_source = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(br_ID),0) FROM finascop_branch WHERE br_Name LIKE '{$field['data']['value']}%' ");
                            $search .= " AND fsto_source IN({$fsto_source}) ";
                        } else if ($field['field'] == 'fsto_destination') {
                            $fsto_destination = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(br_ID),0) FROM finascop_branch WHERE br_Name LIKE '{$field['data']['value']}%' ");
                            $search .= " AND fsto_destination IN({$fsto_destination}) ";
                        } else {
                            $search .= " and " . $field['field'] . " LIKE '" . $field['data']['value'] . "%' ";
                        }
                        break;
                    case 'date':
                        //                        $value = str_replace("/", "", $value);
                        //                        $value = substr($value, 4, 4) . substr($value, 0, 2) . substr($value, 2, 2);
                        $value = date('Y-m-d', strtotime($field['data']['value']));
                        $search .= " AND  DATE_FORMAT(fsto_createdOn,'%Y-%m-%d') " . $comparisons[$field['data']['comparison']] . " '{$value}'";

                        break;
                    case 'list':
                        if ($field['field'] == 'fsto_statusName') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $search .= " AND fsto_statusName IN ('" . $fiterItem . "')";
                        }
                        break;
                        /* if ($field['data']['value'] != "") {
                      $checkComa = strstr($field['data']['value'], ',');
                      if ($checkComa != '') {
                      $fiterItem = $field['data']['value'];
                      $fiterItem = str_replace(',', "','", $fiterItem);
                      $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                      } else {
                      $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                      }
                      } */
                }
            }
        }
        if ($sort == 'fstoCreatedOn') {
            $sort = 'fsto_id';
        }
        $branchName = $_POST['branchName'];
        $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;
        $sourcePyramid = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch where br_ID = {$br_ID}");
        if ($sourcePyramid == 1) {
            $fsto_ordertypeName = "'CPD to CS'";
        } else if ($sourcePyramid == 2) {
            $fsto_ordertypeName = "'CS to Distributor'";
        } else if ($sourcePyramid == 3) {
            $fsto_ordertypeName = "'Distributor to Retailor'";
        } else {
            $fsto_ordertypeName = "'-'";
        }
        if ($_SESSION['admin']->br_PyramidLevel == 1) {
            $where = " ";
            /*if ($_SESSION['admin']->IsSuperUser == 'Yes') {
                $where = " ";
            } else {
                $where = " AND fstosSource =" . $br_ID;
            }*/
        } else {
            $where = " AND fstosSource =" . $_SESSION['admin']->finascop_current_branch_id;
        }

        $query = "SELECT fo.fsto_id as fsto_id,fo.fsto_uid as fsto_uid,fstr_id,(SELECT SUM(fsto_ItemWeight) FROM finascop_stock_transfer_order_details fd WHERE fo.fsto_id= fd.fsto_id) as fsto_ItemWeight,"
            . "(SELECT SUM(fsto_ItemVolume) FROM finascop_stock_transfer_order_details fd WHERE fo.fsto_id= fd.fsto_id) as fsto_ItemVolume,fsto_sourcetype,fsto_destination,fsto_destinationtype,"
            . "fsto_isalreadypacked,IF(fsto_ordertype = 1,(SELECT order_branch_type_id FROM retaline_customer_order WHERE order_id = fstr_id),0) AS order_branch_type_id,"
            . "CASE WHEN fsto_ordertype=0 THEN 'Branch Transfer' WHEN fsto_ordertype=1 THEN 'B2C' WHEN fsto_ordertype=2 THEN 'B2B' WHEN fsto_ordertype=3 THEN 'Return' WHEN fsto_ordertype=4 THEN 'Distribution' END AS fsto_ordertype,"
            . "CASE WHEN fsto_type=0 THEN 'User Created' WHEN fsto_type=1 THEN 'System Created' END AS fsto_type,"
            . "(SELECT fstos_status FROM finascop_stock_transfer_order_status WHERE fstos_id = fsto_status) as fsto_statusName,fsto_status,DATE_FORMAT(fsto_createdOn,'%d-%m-%Y') as fstoCreatedOn,fsto_createdOn,"
            . "CASE WHEN fsto_ordertype = 0 THEN (SELECT CONCAT(br_Name ,'-',branch_shortname) FROM finascop_branch where br_ID = fsto_destination) "
            . "WHEN fsto_ordertype = 1 THEN (SELECT cust_customer_name FROM retaline_customer WHERE cust_id = fsto_destination) "
            . "WHEN fsto_ordertype = 2 THEN (SELECT b2b_Customer_Name FROM retaline_B2Bcustomer where b2b_Customer_ID = fsto_destination) "
            . "WHEN fsto_ordertype = 3 THEN (SELECT CONCAT(br_Name ,'-',branch_shortname) FROM finascop_branch where br_ID = fsto_destination) END AS fsto_destinationName,
                    CASE WHEN fsto_ordertype = 1 AND (SELECT order_branch_type_id FROM retaline_customer_order WHERE order_id = fstr_id)= 2 AND (SELECT asctedbrach_cpr FROM finascop_stock_party WHERE stpa_id = fsto_source) > 0 
                    THEN (SELECT asctedbrach_cpr FROM finascop_stock_party WHERE stpa_id = fsto_source) 
                    WHEN fsto_ordertype = 1 AND (SELECT order_branch_type_id FROM retaline_customer_order WHERE order_id = fstr_id)= 2 AND (SELECT asctedbrach_cpr FROM finascop_stock_party WHERE stpa_id = fsto_source) = 0 
                    THEN 1 ELSE fsto_source END AS fstosSource,fsto_source,(SELECT CONCAT(br_Name ,'-',branch_shortname) FROM finascop_branch WHERE br_ID = fstosSource) AS fsto_sourceName,
                    (SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'IS_INVOICE') as ownInvoice,(SELECT br_type FROM finascop_branch WHERE br_ID = fsto_source) as branchType,
                    @srCount:=(SELECT fsr_id FROM finascop_stock_request WHERE fstr_id = fo.fstr_id),
                    CASE WHEN fsto_ordertype = 0 THEN (if(@srCount > 0,1,0)) WHEN fsto_ordertype=1 THEN 0 WHEN fsto_ordertype=2 THEN 0 WHEN fsto_ordertype=3 THEN 1  WHEN fsto_ordertype=4 THEN 0 END as isReturn,
                    (SELECT br_parentPacking FROM finascop_branch WHERE br_ID = fsto_source) as br_parentPacking  "
            . "  FROM finascop_stock_transfer_order  fo";

        $countQuery = "SELECT COUNT(*) FROM ($query) as trdocount {$search} {$where} AND fsto_status NOT IN (9,11,20,10) AND fsto_ordertype < 4 ORDER BY /*CAST({$sort} as char) {$dir},binary*/ {$sort} {$dir} ";

        $listQuery = "SELECT * FROM ({$query}) AS trdo {$search} {$where} AND fsto_status NOT IN (9,11,20,10) AND fsto_ordertype < 4 ORDER BY  /*CAST({$sort} as char) {$dir},binary*/  {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'getItemBarcodes':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'tmp_barcode_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
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
        $fsto_source = $_POST['fsto_source'];
        $countQuery = "SELECT COUNT(*) FROM finascop_stock_transfer_order_details_barcodes_temp {$search}  AND tmp_barcode_itemId=" . intval($_POST['fsto_itemId']) . " AND tmp_barcode_fstoId={$_POST['fsto_id']} AND rpb_status=1 ORDER BY {$sort} {$dir}";

        $listQuery = "SELECT tmp_barcode_code,tmp_barcode_id,rpb_status FROM finascop_stock_transfer_order_details_barcodes_temp {$search} AND tmp_barcode_itemId=" . intval($_POST['fsto_itemId']) . " AND tmp_barcode_fstoId={$_POST['fsto_id']} AND rpb_status=1 ORDER BY {$sort} {$dir} ";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'getBranches':
        $fsto_source = $_POST['fsto_source'];
        if ($fsto_source > 0) {
            $cond = " AND br_ID = {$fsto_source}";
        } else {
            $cond = " ";
        }
        $qry = $db->getMulipleData("SELECT br_ID,CONCAT(br_Name ,'-',branch_shortname) as br_Name FROM finascop_branch where br_status = 'Active' {$cond}", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'getBranchName':
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        $qry = $db->getMulipleData("SELECT br_ID,CONCAT(br_Name ,'-',branch_shortname) as br_Name FROM finascop_branch WHERE br_status = 'Active' AND (br_cpd={$branch_id} OR br_ID={$branch_id})", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'getOrderPicker':
        $order_branch_id = $_POST['fsto_source'];
        // $qry = $db->getMulipleData("SELECT id,name,has_open_orders,phone,is_offline from retaline_godown_boy WHERE  branch_id={$order_branch_id} ", true);
        //has_open_orders = 0 AND COALESCE(fcm_id,'') <> '' AND AND is_offline = 0 
        if ($order_branch_id > 0) {
            $countQuery = "SELECT COUNT(*) FROM retaline_godown_boy  WHERE  status = 1 and branch_id={$order_branch_id}";

            $listQuery = "SELECT id,name,has_open_orders,phone,is_offline,if(is_offline = 1,'Offline','Online') as liveStatus from retaline_godown_boy WHERE  status = 1 and branch_id={$order_branch_id} ORDER BY name ASC";

            $db->printGridJson($countQuery, $listQuery);
        }

        break;
    case 'getmanualPacking':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'fo.fsto_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status_name') {
                            if ($field['data']['value'] == 'Requested') {
                                //    $field['data']['value'] = 2;
                                $fiterItem = 1;
                                $search .= " and (fstrd_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Ordered') {
                                //    $field['data']['value'] = 3;
                                $fiterItem = 2;
                                $search .= " and (fstrd_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Deleted') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 3;
                                $search .= " and (fstrd_status = {$fiterItem}) ";
                            } else {
                                //    $field['data']['value'] = 0;
                                $search .= " and (fstrd_status = 1 or fstrd_status = 2 or fstrd_status = 3 ) ";
                            }
                        }

                        break;
                    case 'string':
                        foreach ($filter as $key => $field) {
                            if ($field['data']['value'] != "") {
                                $checkComa = strstr($field['data']['value'], ',');
                                if ($checkComa != '') {
                                    $fiterItem = $field['data']['value'];
                                    $fiterItem = str_replace(',', "','", $fiterItem);
                                    $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                                } else {
                                    $search .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                                }
                            }
                        }
                }
            }
        }
        $fsto_id = $_POST['fsto_id'];
        $barcode = $_POST['barcodesearch_field'];
        $branch = $_POST['fsto_destination'];

        $countQry = "SELECT COUNT(*) FROM finascop_stock_transfer_order fo INNER JOIN finascop_stock_transfer_order_details fd ON fo.fsto_id = fd.fsto_id {$search} AND fo.fsto_id = {$fsto_id}  ORDER BY {$sort} {$dir}";

        $listQry = "SELECT fo.fsto_uid as fsto_uid,fo.fsto_id as fsto_id,fsto_ItemId,fsto_createdOn,fsto_destination,fsto_destination,fsto_source,(SELECT CONCAT(br_Name ,'-',branch_shortname) FROM finascop_branch where br_ID = fsto_source) as fsto_source,"
            . "(SELECT br_type FROM finascop_branch WHERE br_ID = fsto_source) as branchType,(SELECT br_parentPacking FROM finascop_branch WHERE br_ID = fsto_source) as br_parentPacking,"
            . "(SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = fsto_ItemId) as item_name,fsto_ItemQty,fsto_pkdQty,(SELECT CONCAT(br_Name ,'-',branch_shortname) FROM finascop_branch where br_ID = fsto_destination) as branch,"
            . "fo.fsto_id as fsto_id,fstro_ItemMRP as mrp,fsto_isConversionRequired,(SELECT stit_ParentItemId FROM finascop_stock_itemmaster where stit_ID = fsto_ItemId) as stit_ParentItemId,"
            . "(SELECT stit_ConvertCalcMode FROM finascop_stock_itemmaster where stit_ID = fsto_ItemId) as stit_ConvertCalcMode,(SELECT stit_ConvertCalcRate FROM finascop_stock_itemmaster where stit_ID = fsto_ItemId) as stit_ConvertCalcRate,fsto_stockValue "
            . " FROM finascop_stock_transfer_order fo INNER JOIN finascop_stock_transfer_order_details fd ON fo.fsto_id = fd.fsto_id  {$search} AND fo.fsto_id={$fsto_id} ORDER BY {$sort} {$dir} ";
        $datas = $db->getMulipleData($listQry, true);
        $resCount = count($datas);
        $count = $db->getItemFromDB($countQry);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $branch = $db->getItemFromDB("SELECT mrp FROM finascop_stock_branch_inventory WHERE stit_id={$datas[$i]['fsto_ItemId']} AND branch_id={$datas[$i]['fsto_destination']}");
                //$datas[$i]['mrp'] = $branch;
                $datas[$i]['slNo'] = $i + 1;
                $datas[$i]['stit_ConvertCalcMode'] = $datas[$i]['stit_ConvertCalcMode'] * $datas[$i]['fsto_ItemQty'];
                $datas[$i]['parentItemName'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = {$datas[$i]['stit_ParentItemId']}");
            }
        }
        echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        break;
    case 'assignOrderPicker':
        $isboyAlreadyPolled = $db->getItemSafe("SELECT COUNT(*) FROM finascop_stock_transfer_order WHERE fsto_polled_boy = ? AND fsto_status IN (2,4) AND fsto_source = {$_POST['br_ID']}", "i", [$_POST['id']]);
        if ($isboyAlreadyPolled == 0) {
            //$br_IsCPD = $db->getItemSafe("SELECT br_IsCPD FROM finascop_branch WHERE br_ID = ?", "i", [$_POST['br_ID']]);
            $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'ASSIGNEXEC'");
            $fields = array(
                "is_cpd" => 0,
                "order_id" => $_POST['order_NO'],
                "boy_id" => $_POST['id'],
                "branch_id" => $_POST['br_ID'],
                "order_pk_id" => $_POST['order_ID'],
            );
            $fields_string = json_encode($fields);
            //print_r($fields_string);
            $opts = array(
                CURLOPT_URL => $url,
                CURLINFO_CONTENT_TYPE => "application/json",
                CURLOPT_BINARYTRANSFER => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_POST => count($fields),
                CURLOPT_POSTFIELDS => $fields_string,
                CURLOPT_HTTPHEADER => array('Content-Type: application/json')
            );

            $ch = curl_init();
            curl_setopt_array($ch, $opts);
            $data = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            header("Content-Type: application/json");
            echo $data;
        } else {
            echo '{"status":false,"msg":"Boy is already polled.."}';
        }

        break;

    case 'orderPrint':
        ob_start();
        include('printView.php');
        $resHtml = ob_get_clean();
        echo $resHtml;
        break;
    case "order_details_view":
        require(THIS_MODULE_PATH . "/printView.php");
        break;

    case "barcodeCheck":
        $fsto_id = $_POST['fsto_id'];
        $barcode = $_POST['barcodesearch_field'];
        $branch = $_POST['fsto_source'];
        $fsto_ordertype = $db->getItemFromDB("SELECT fsto_ordertype FROM finascop_stock_transfer_order WHERE fsto_id = {$fsto_id}");
        if ($fsto_ordertype == 3) {
            $isReturn = 1;
        } else {
            $isReturn = 0;
        }
        $db->query('begin');
        if ($barcode != '') {

            $unique_brcode = $db->getItemFromDB("SELECT count(tmp_barcode_code) FROM finascop_stock_transfer_order_details_barcodes_temp WHERE tmp_barcode_code={$barcode} and branch_id = {$branch} AND "
                . "rpb_status = 1 and tmp_barcode_isReturn = {$isReturn}");

            $cpd = $db->getItemFromDB("SELECT is_branch FROM finascop_stock_item_inventorydetails WHERE stiid_barcode={$barcode} AND cpd_branch_id={$branch}");
            if ($cpd == 0) {
                $brCdItemId = $db->getItemFromDB("SELECT stiid_itemmasterid FROM finascop_stock_item_inventorydetails WHERE stiid_barcode={$barcode} AND stiid_status=1 AND cpd_branch_id={$branch}");
            } else {
                if ($isReturn == 0) {
                    $brCdItemId = $db->getItemFromDB("SELECT stiid_itemmasterid FROM finascop_stock_item_inventorydetails WHERE stiid_barcode={$barcode} AND (stiid_status=1 OR stiid_status=4) AND cpd_branch_id={$branch}");
                } else {
                    // if ($_SESSION['admin']->br_PyramidLevel == 4) {
                    $brCdItemId = $db->getItemFromDB("SELECT stiid_itemmasterid FROM finascop_stock_item_inventorydetails WHERE stiid_barcode={$barcode} AND (stiid_status=1 OR stiid_status=8 OR stiid_status=4 OR stiid_status=13) AND cpd_branch_id={$branch}");
                    //} else {
                    // $brCdItemId = $db->getItemFromDB("SELECT stiid_itemmasterid FROM finascop_stock_item_inventorydetails WHERE stiid_barcode={$barcode} AND (stiid_status=9 OR stiid_status=4) AND cpd_branch_id={$branch}");
                    //}
                }
            }
            if ($brCdItemId) {
                $pkdBarcodeCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order_details_barcodes_temp WHERE  tmp_barcode_itemId = {$brCdItemId} AND tmp_barcode_fstoId = {$fsto_id} AND rpb_status = 1");
                $pkdQty = $db->getFromDB("SELECT fsto_ItemQty,fsto_pkdQty FROM finascop_stock_transfer_order_details WHERE fsto_ItemId={$brCdItemId} AND fsto_id={$fsto_id}", true);
                $icrPkdQty['tmp_barcode_code'] = $barcode;
                $icrPkdQty['tmp_barcode_itemId'] = $brCdItemId;
                $icrPkdQty['tmp_barcode_fstoId'] = $fsto_id;
                $icrPkdQty['tmp_barcode_createdOn'] = date('Y-m-d H:i:s');
                $icrPkdQty['tmp_barcode_createdBy'] = $_SESSION['admin']->UserId;
                $icrPkdQty['branch_id'] = $branch;
                if ($pkdBarcodeCount < $pkdQty['fsto_ItemQty']) {
                    if ($unique_brcode == 0) {
                        $status = $db->perform('finascop_stock_transfer_order_details_barcodes_temp', $icrPkdQty);
                        $lastId = $db->insert_id();
                    } else {
                        echo '{"success":false,"valid":false,"msg":"Barcode already entered"}';
                        exit();
                    }
                    $item_id = $db->getItemFromDB("SELECT tmp_barcode_itemId FROM finascop_stock_transfer_order_details_barcodes_temp WHERE tmp_barcode_id={$lastId} AND tmp_barcode_code={$barcode}");
                } else {
                    echo '{"success":false,"valid":false,"msg":"Packed Quantity exceeded"}';
                    exit();
                }
                $status = $db->query('commit');
                if ($status == 1) {
                    $msg = "'tmp data added'";
                    echo '{"success":true,"valid":true,"msg":' . $msg . ',item_id :' . $item_id . '}';
                } else {
                    $msg = "'Error occured while saving.'";
                    echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
                }
            } else {
                echo '{"success":false,"valid":false,"msg":"Already packed"}';
                exit();
            }
        }
        break;

    case 'barcodedelete':
        $fsto_id = $_POST['fsto_id'];
        $branch = $_POST['fsto_source'];
        $db->query('begin');
        $del_query = "DELETE FROM finascop_stock_transfer_order_details_barcodes_temp WHERE tmp_barcode_fstoId={$fsto_id} AND rpb_status=1";
        $db->query($del_query);

        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'tmp deleted'";
            echo '{"success":true,"valid":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error occured while saving.'";
            echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
        }
        break;
    case 'forcesubmitManualPacking':
        $peItemSGriddata = json_decode(stripslashes($_POST['itemGriddata']));
        // print_r($peItemSGriddata);
        $db->query('begin');
        $fsto_uid = $_POST['fsto_uid'];
        $order_id = $_POST['fsto_id'];

        if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
            $cfg_Value = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'MANUAL_PACKING_URL'");
            $url = str_replace('{orderId}', $fsto_uid, $cfg_Value);
        } else {
            $cfg_Value = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'MANUAL_PACKING_NOBARCODE_URL'");
            $url = str_replace('{orderId}', $fsto_uid, $cfg_Value);
        }
        $fsto_updateon = $db->getItemFromDB("SELECT fsto_updateon FROM finascop_stock_transfer_order WHERE fsto_id = {$order_id}");
        //echo '$url'.$url;
        $noofbags = $_POST['noofbags'];
        $fsto_ordertype = $_POST['fsto_ordertype'];
        switch ($fsto_ordertype) {
            case 'Branch Transfer':
                $orderType = 0;
                break;
            case 'B2C':
                $orderType = 1;
                $packing_invDate = date('Y-m-d', $_POST['packing_invDate']);
                $packing_invNo = $_POST['packing_invNo'];
                $packing_invAmt = $_POST['packing_invAmt'];
                $result['invoiceno'] = $packing_invNo;
                $result['invoicedate'] = $packing_invDate;
                $result['invoiceamt'] = $packing_invAmt;
                break;
            case 'B2B':
                $orderType = 2;
                break;
            case 'Return':
                $orderType = 3;
                break;
        }
        $result['type'] = $orderType;
        $result['ismanual'] = 1;
        $result['key'] = md5($fsto_updateon);
        $result['is_incomplete'] = true;

        if (!empty($peItemSGriddata)) {
            foreach ($peItemSGriddata as $peItemS) {
                $fsto_ItemId = $peItemS->fsto_ItemId;
                $fsto_pkdQty = $peItemS->fsto_pkdQty;
                if (!empty($peItemS->fsto_stockValue)) {
                    $fsto_stockValue = $peItemS->fsto_stockValue;
                } else {
                    $fsto_stockValue = 0;
                }
                if (!empty($peItemS->stit_ConvertCalcRate)) {
                    $stit_ConvertCalcRate = $peItemS->stit_ConvertCalcRate;
                } else {
                    $stit_ConvertCalcRate = 0;
                }
                $tmpitems = [];
                $tmpitems['item_id'] = $fsto_ItemId;
                if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                    $barcode = $db->getItemFromDB("SELECT GROUP_CONCAT(tmp_barcode_code) FROM finascop_stock_transfer_order_details_barcodes_temp WHERE tmp_barcode_itemId={$fsto_ItemId} AND tmp_barcode_fstoId={$order_id} AND rpb_status=1", true);
                    $barcodes = explode(',', $barcode);
                    $tmpitems['barcodes'] = $barcodes;
                } else {
                    $tmpitems['count'] = $fsto_pkdQty;
                }
                $tmpitems['fsto_stockValue'] = $fsto_stockValue;
                $tmpitems['stit_ConvertCalcRate'] = $stit_ConvertCalcRate;
                $result['items'][] = $tmpitems;
            }
        }
        $result['boy_order_id'] = '-10';
        $result['number_bags'] = $noofbags;

        $fields_string = json_encode($result);
        //print_r($fields_string);
        $opts = array(
            CURLOPT_URL => $url,
            CURLINFO_CONTENT_TYPE => "application/json",
            CURLOPT_BINARYTRANSFER => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST => count($result),
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        $haserror = false;
        $hascriticalerror = false;
        switch ($info['http_code']) {
            case "200":  # OK
                $result = json_decode($data, true);
                break;
            case "400":
            case "406":
                $haserror = true;
                break;
            default:
                $hascriticalerror = true;
        }
        curl_close($ch);
        header("Content-Type: application/json");
        // print_r($data);
        $result = json_decode($data, true);
        //print_r($result);
        if ($hascriticalerror) {

            echo '{"success":true,"valid":false,"msg":"' . str_replace("'", "", str_replace('"', "", $data)) . '"}';
        } elseif ($haserror) {
            $result = json_decode($data, true);
            echo '{"success":true,"valid":false,"msg":"' . $result['error']['msg'][0] . $result['error']['msg'][1] . '"}';
        } elseif ($result['status'] == 'mismatch') {
            $missedBarcodes = $result['data']['mismatched'][0]['barcodes'];
            if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                foreach ($missedBarcodes as $missedBarcod) {
                    $tmp['rpb_status'] = 3;
                    $status = $db->perform("finascop_stock_transfer_order_details_barcodes_temp", $tmp, 'update', 'tmp_barcode_fstoId =' . $order_id . ' AND tmp_barcode_code=' . $missedBarcod);
                }
            }

            echo '{"success":true,"valid":false,"msg":"Barcode Mismatched ."}';
            exit();
        } else {
            $packingList = $result['packinglist']['packingNumber'];
            $pickingList = $result['packinglist']['pickingNumber'];
            if (count($packingList) > 0) {
                $msg = "Order Packed";
            }
            if (count($pickingList) > 0) {
                $msg = "Order Picked - Picking No: {$pickingList}";
            }
            if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                foreach ($barcodes as $barcod) {
                    $tmp['rpb_status'] = 2;
                    $status = $db->perform("finascop_stock_transfer_order_details_barcodes_temp", $tmp, 'update', 'tmp_barcode_fstoId =' . $order_id . ' AND tmp_barcode_code=' . $barcod);
                }
            }
            echo '{"success":true,"valid":true,"msg":"' . $msg . '","packcount":"', count($packingList), '","data":' . json_encode(array_values($packingList)) . '}';
            exit();
        }
        //echo '{"success":true,"valid":true,"msg":"Data updated ."}';
        exit;
        //echo $data;
        break;
    case 'forcesubmitManualPackingold':
        $db->query('begin');
        $fsto_id = $_POST['fsto_id'];
        $nw_pkd_qty['fstro_updatedOn'] = date('Y-m-d H:i:s');
        $nw_pkd_qty['fstro_updatedBy'] = $_SESSION['admin']->UserId;

        if (!empty($peItemSGriddata)) {
            foreach ($peItemSGriddata as $peItemS) {
                $fsto_id = $peItemS->fsto_id;
                $fsto_ItemId = $peItemS->fsto_ItemId;
                $fsto_source = $peItemS->fsto_source;
                $branchId = $db->getItemFromDB("SELECT br_ID FROM finascop_branch WHERE br_Name = '{$fsto_source}'");
                if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                    $barcode = $db->getMultipleData("SELECT tmp_barcode_id,tmp_barcode_code FROM finascop_stock_transfer_order_details_barcodes_temp WHERE tmp_barcode_itemId={$fsto_ItemId} AND tmp_barcode_fstoId={$fsto_id} ", true);
                    for ($i = 0; $i < count($barcode); $i++) {
                        $barCode_branch = $db->getItemFromDB("SELECT is_branch FROM finascop_stock_item_inventorydetails WHERE stiid_barcode='{$barcode[$i]['tmp_barcode_code']}' AND cpd_branch_id={$branchId}");
                        if ($barCode_branch == 0) {
                            $brCdItemId = $db->getItemFromDB("SELECT stiid_itemmasterid FROM finascop_stock_item_inventorydetails WHERE stiid_barcode='{$barcode[$i]['tmp_barcode_code']}' AND stiid_status=1 AND cpd_branch_id={$branchId}");
                            if ($brCdItemId > 0) {
                                $pk_cnt = $db->getItemFromDB("SELECT fsto_pkdQty FROM finascop_stock_transfer_order_details WHERE fsto_id={$fsto_id} AND fsto_ItemId={$fsto_ItemId}");
                                $fstoBarcode = $db->getItemFromDB("SELECT fsto_pkdBarcodes FROM finascop_stock_transfer_order_details WHERE fsto_id={$fsto_id} AND fsto_ItemId={$fsto_ItemId}");
                                if ($fstoBarcode != '') {
                                    $nw_pkd_qty['fsto_pkdBarcode'] = $fstoBarcode . ',' . $barcode[$i]['tmp_barcode_code'];
                                } else {
                                    $nw_pkd_qty['fsto_pkdBarcode'] = $barcode[$i]['tmp_barcode_code'];
                                }
                                $nw_pkd_qty['fsto_pkdQty'] = $pk_cnt + 1;
                                $status = $db->perform("finascop_stock_transfer_order_details", $nw_pkd_qty, 'update', 'fsto_ItemId =' . $fsto_ItemId . ' AND fsto_id=' . $fsto_id);
                                $tmp['rpb_status'] = 2;
                                $status = $db->perform("finascop_stock_transfer_order_details_barcodes_temp", $tmp, 'update', 'tmp_barcode_fstoId =' . $fsto_id . ' AND tmp_barcode_code=' . $barcode[$i]['tmp_barcode_code']);
                            } else {
                                $tmp['rpb_status'] = 3;
                                $status = $db->perform("finascop_stock_transfer_order_details_barcodes_temp", $tmp, 'update', 'tmp_barcode_fstoId =' . $fsto_id . ' AND tmp_barcode_code=' . $barcode[$i]['tmp_barcode_code']);
                            }
                        } else {
                            $brCdItemId = $db->getItemFromDB("SELECT stiid_itemmasterid FROM finascop_stock_item_inventorydetails WHERE stiid_barcode={$barcode[$i]['tmp_barcode_code']} AND (stiid_status=1 OR stiid_status=4) AND cpd_branch_id={$branchId}");
                            if ($brCdItemId > 0) {
                                $pk_cnt = $db->getItemFromDB("SELECT fsto_pkdQty FROM finascop_stock_transfer_order_details WHERE fsto_id={$fsto_id} AND fsto_ItemId={$fsto_ItemId}");
                                $fstoBarcode = $db->getItemFromDB("SELECT fsto_pkdBarcodes FROM finascop_stock_transfer_order_details WHERE fsto_id={$fsto_id} AND fsto_ItemId={$fsto_ItemId}");
                                if ($fstoBarcode != '') {
                                    $nw_pkd_qty['fsto_pkdBarcode'] = $fstoBarcode . ',' . $barcode[$i]['tmp_barcode_code'];
                                } else {
                                    $nw_pkd_qty['fsto_pkdBarcode'] = $barcode[$i]['tmp_barcode_code'];
                                }
                                $nw_pkd_qty['fsto_pkdQty'] = $pk_cnt + 1;
                                $status = $db->perform("finascop_stock_transfer_order_details", $nw_pkd_qty, 'update', 'fsto_ItemId =' . $fsto_ItemId . ' AND fsto_id=' . $fsto_id);
                                $tmp['rpb_status'] = 2;
                                $status = $db->perform("finascop_stock_transfer_order_details_barcodes_temp", $tmp, 'update', 'tmp_barcode_fstoId =' . $fsto_id . ' AND tmp_barcode_code=' . $barcode[$i]['tmp_barcode_code']);
                            } else {
                                $tmp['rpb_status'] = 3;
                                $status = $db->perform("finascop_stock_transfer_order_details_barcodes_temp", $tmp, 'update', 'tmp_barcode_fstoId =' . $fsto_id . ' AND tmp_barcode_code=' . $barcode[$i]['tmp_barcode_code']);
                            }
                        }
                    }
                } else {
                    $nw_pkd_qty['fsto_pkdQty'] = $peItemS->fsto_pkdQty;
                    $status = $db->perform("finascop_stock_transfer_order_details", $nw_pkd_qty, 'update', 'fsto_ItemId =' . $fsto_ItemId . ' AND fsto_id=' . $fsto_id);
                }
            }
        }
        $tmp_order = array(
            "fsto_isalreadypacked" => 1,
            "fsto_status" => 9
        );
        $status = $db->perform("finascop_stock_transfer_order", $tmp_order, 'update', 'fsto_id =' . $fsto_id);
        $status = $db->query('commit');
        if ($status) {
            echo '{"success":true,"valid":true,"msg":"Data updated ."}';
            exit;
        } else {
            echo '{"success":false,"msg":"Error while saving data."}';
            exit;
        }


        break;
    case 'submitManualPacking':
        $peItemSGriddata = json_decode(stripslashes($_POST['itemGriddata']));
        // print_r($peItemSGriddata);
        $getCurrentStatus = $db->getItemFromDB("SELECT fsto_status FROM finascop_stock_transfer_order WHERE fsto_id = {$order_id}");
        if ($getCurrentStatus != 6) {
            echo '{"success":true,"valid":false,"msg":"Order status changes kindle reload ."}';
            exit();
        }

        $fsto_uid = $_POST['fsto_uid'];
        $order_id = $_POST['order_id'];

        $tmp_order['fsto_manualpackinguserid'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform("finascop_stock_transfer_order", $tmp_order, 'update', 'fsto_id =' . $order_id);

        $db->query('begin');

        if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
            $cfg_Value = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'MANUAL_PACKING_URL'");
            $url = str_replace('{orderId}', $fsto_uid, $cfg_Value);
        } else {
            $cfg_Value = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'MANUAL_PACKING_NOBARCODE_URL'");
            $url = str_replace('{orderId}', $fsto_uid, $cfg_Value);
        }
        $fsto_updateon = $db->getItemFromDB("SELECT fsto_updateon FROM finascop_stock_transfer_order WHERE fsto_id = {$order_id}");
        //echo '$url'.$url;

        $noofbags = $_POST['noofbags'];
        $fsto_ordertype = $_POST['fsto_ordertype'];
        switch ($fsto_ordertype) {
            case 'Branch Transfer':
                $orderType = 0;
                break;
            case 'B2C':
                $orderType = 1;
                $packing_invDate = date('Y-m-d', $_POST['packing_invDate']);
                $packing_invNo = $_POST['packing_invNo'];
                $packing_invAmt = $_POST['packing_invAmt'];
                $result['invoiceno'] = $packing_invNo;
                $result['invoicedate'] = $packing_invDate;
                $result['invoiceamt'] = $packing_invAmt;
                break;
            case 'B2B':
                $orderType = 2;
                break;
            case 'Return':
                $orderType = 3;
                break;
        }
        $result['type'] = $orderType;
        $result['ismanual'] = 1;
        $result['key'] = md5($fsto_updateon);
        //$result['is_incomplete'] = false;

        if (!empty($peItemSGriddata)) {
            foreach ($peItemSGriddata as $peItemS) {
                $fsto_ItemId = $peItemS->fsto_ItemId;
                $fsto_pkdQty = $peItemS->fsto_pkdQty;
                if (!empty($peItemS->fsto_stockValue)) {
                    $fsto_stockValue = $peItemS->fsto_stockValue;
                } else {
                    $fsto_stockValue = 0;
                }

                $tmpitems = [];
                $tmpitems['item_id'] = $fsto_ItemId;
                if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                    $barcode = $db->getItemFromDB("SELECT GROUP_CONCAT(tmp_barcode_code) FROM finascop_stock_transfer_order_details_barcodes_temp WHERE tmp_barcode_itemId={$fsto_ItemId} AND tmp_barcode_fstoId={$order_id} AND rpb_status=1", true);
                    $barcodes = explode(',', $barcode);
                    $tmpitems['barcodes'] = $barcodes;
                } else {
                    $tmpitems['count'] = $fsto_pkdQty;
                }
                $tmpitems['fsto_stockValue'] = $fsto_stockValue;
                $result['items'][] = $tmpitems;
            }
        }
        $result['boy_order_id'] = '-10';
        $result['number_bags'] = $noofbags;

        $fields_string = json_encode($result);
        // print_r($fields_string);
        $opts = array(
            CURLOPT_URL => $url,
            CURLINFO_CONTENT_TYPE => "application/json",
            CURLOPT_BINARYTRANSFER => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST => count($result),
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        $haserror = false;
        $hascriticalerror = false;
        switch ($info['http_code']) {
            case "200":  # OK
                $result = json_decode($data, true);
                break;
            case "400":
            case "406":
                $haserror = true;
                break;
            default:
                $hascriticalerror = true;
        }
        //print_r($info);
        curl_close($ch);
        header("Content-Type: application/json");
        $result = json_decode($data, true);
        //print_r($result);
        if ($hascriticalerror) {
            if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                foreach ($barcodes as $barcod) {
                    $tmp['rpb_status'] = 3;
                    $status = $db->perform("finascop_stock_transfer_order_details_barcodes_temp", $tmp, 'update', 'tmp_barcode_fstoId =' . $order_id . ' AND tmp_barcode_code=' . $barcod);
                }
            }
            $status = $db->query('commit');

            echo '{"success":true,"valid":false,"msg":"' . str_replace("'", "", str_replace('"', "", $data)) . '"}';
        } elseif ($haserror) {
            if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                foreach ($barcodes as $barcod) {
                    $tmp['rpb_status'] = 3;
                    $status = $db->perform("finascop_stock_transfer_order_details_barcodes_temp", $tmp, 'update', 'tmp_barcode_fstoId =' . $order_id . ' AND tmp_barcode_code=' . $barcod);
                }
            }
            $status = $db->query('commit');
            $result = json_decode($data, true);
            echo '{"success":true,"valid":false,"msg":"' . $result['error']['msg'] . '"}';
        } elseif ($result['status'] == 'mismatch') {
            $missedBarcodes = $result['data']['mismatched'][0]['barcodes'];
            if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                foreach ($missedBarcodes as $missedBarcod) {
                    $tmp['rpb_status'] = 3;
                    $status = $db->perform("finascop_stock_transfer_order_details_barcodes_temp", $tmp, 'update', 'tmp_barcode_fstoId =' . $order_id . ' AND tmp_barcode_code=' . $missedBarcod);
                }
            }

            echo '{"success":true,"valid":false,"msg":"Barcode Mismatched ."}';
            exit();
        } else if ($result['status'] == 'error') {
            if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                foreach ($barcodes as $barcod) {
                    $tmp['rpb_status'] = 3;
                    $status = $db->perform("finascop_stock_transfer_order_details_barcodes_temp", $tmp, 'update', 'tmp_barcode_fstoId =' . $order_id . ' AND tmp_barcode_code=' . $barcod);
                }
            }
            $status = $db->query('commit');
            $message = $result['error']['msg'];
            echo '{"success":true,"valid":false,"msg":"' . $message . '"}';
            exit();
        } else {

            $packingList = $result['packinglist']['packingNumber'];
            $pickingList = $result['packinglist']['pickingNumber'];
            if (count($packingList) > 0) {
                $msg = "Order Packed";
            }
            if (count($pickingList) > 0) {
                $msg = "Order Picked ";
            }
            if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                foreach ($barcodes as $barcod) {
                    $tmp['rpb_status'] = 2;
                    $status = $db->perform("finascop_stock_transfer_order_details_barcodes_temp", $tmp, 'update', 'tmp_barcode_fstoId =' . $order_id . ' AND tmp_barcode_code=' . $barcod);
                }
            }
            echo '{"success":true,"valid":true,"msg":"' . $msg . '","packcount":"', count($packingList), '","data":' . json_encode(array_values($packingList)) . '}';
            exit();
        }

        //        if ($status) {
        //            if ($flag == 1) {
        //                echo '{"success":true,"valid":false,"msg":"Barcode Mismatched ."}';
        //                exit();
        //            } else if ($flag == 2) {
        //                $message = $result['error']['msg'];
        //                echo '{"success":true,"valid":false,"msg":"' . $message . '"}';
        //                exit();
        //            } else if ($flag == 3) {
        //                echo '{"success":true,"valid":true,"msg":"Order Packed .","packcount":"', count($packingList), '","data":' . json_encode(array_values($packingList)) . '}';
        //                exit();
        //            }
        //        }
        //echo '{"success":true,"valid":true,"msg":"Data updated ."}';
        exit;
        //echo $data;
        break;
    case 'revokeTransferOrder':
        $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'REVOKEORDER'");
        $boyorder = $db->getFromSafe("SELECT boy_id,order_pk_id FROM retaline_godown_boy_orders_request WHERE order_pk_id = ?  ORDER BY id DESC limit 1", "i", [$_POST['orderId']], true);


        $fields = array(
            "order_pk_id" => $boyorder['order_pk_id'],
            "boy_id" => (int) $boyorder['boy_id'],
        );
        $fields_string = json_encode($fields);
        $opts = array(
            CURLOPT_URL => $url,
            CURLINFO_CONTENT_TYPE => "application/json",
            CURLOPT_BINARYTRANSFER => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST => count($fields),
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        header("Content-Type: application/json");
        echo $data;
        break;
    case 'listItemsinTransferOrder':
        //        $br_IsCPD = $db->getItemSafe("SELECT br_IsCPD FROM finascop_branch WHERE br_ID = ?", "i", [$_POST['orderType']]);
        //        if ($br_IsCPD == 1) {
        //            $orderType = 'cpd';
        //        } else {
        //            $orderType = 'branch';
        //        }
        //if ($orderType == 'cpd') {
        $rec_sort = empty($data['sort']) ? 'fsto_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' AND 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['fsto_id', 'fsto_uid', 'fsto_createdOn', 'fsto_source', 'fsto_destination', 'fsto_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(*) from finascop_stock_transfer_order_details WHERE fsto_id = " . intval($_POST['orderId']) . " {$filter_part}";
        $listQuery = "SELECT fstod_id as bcod_id,fsto_id as bcor_id,fsto_ItemId as stit_ID,fsto_ItemQty,stit_SKU as stitSKU,"
            . "fsto_pkdQty ,ROUND((fsto_ItemQty*stit_ConvertCalcRate),3) AS bcod_Count,fsto_stockValue AS bcod_scannedcount  "
            . "from finascop_stock_transfer_order_details oi INNER JOIN finascop_stock_itemmaster im ON im.stit_ID = oi.fsto_ItemId WHERE fsto_id = {$_POST['orderId']} {$filter_part}  ORDER BY $rec_sort $rec_sort_dir ";
        //        } else {
        //            $rec_sort = empty($data['sort']) ? 'customer_order_id' : $data['sort'];
        //            $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        //            $filter_part = ' AND 1=1';
        //
        //            if (isset($data['filter'])) {
        //
        //                foreach ($data['filter'] as $key => $val) {
        //                    $filter_part .= " and " . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
        //                }
        //            }
        //            if ($_POST['type'] == 'B2C') {
        //                $countQuery = "SELECT COUNT(*) from retaline_customer_order_items WHERE customer_order_id = " . intval($_POST['orderId']) . " {$filter_part}";
        //                $listQuery = "SELECT item_product_id as stit_ID, item_order_qty as bcod_Count,(SELECT stit_SKU FROM finascop_stock_itemmaster im WHERE im.stit_ID = oi.item_product_id) as stitSKU, item_order_qty_scanned as bcod_scannedcount "
        //                        . "from retaline_customer_order_items oi WHERE customer_order_id = {$_POST['orderId']} {$filter_part}  ORDER BY $rec_sort $rec_sort_dir ";
        //            } else {
        //                $countQuery = "SELECT COUNT(*) from retaline_B2B_SalesOrderDetails WHERE bbso_id = " . intval($_POST['orderId']) . " {$filter_part}";
        //                $listQuery = "SELECT * FROM(SELECT b2bso_itemid as stit_ID, b2bso_itemqty as bcod_Count,b2bso_itemname as stitSKU, '' as bcod_scannedcount,bbso_id as customer_order_id "
        //                        . "from retaline_B2B_SalesOrderDetails oi  ) AS b2bso WHERE customer_order_id = {$_POST['orderId']}  {$filter_part}  ORDER BY $rec_sort $rec_sort_dir ";
        //            }
        //        }

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'listTOPolledHistory':
        $sort = empty($data['sort']) ? 'created_at' : $data['sort'];
        $dir = empty($data['dir']) ? 'DESC' : $data['dir'];
        $filter_part = ' AND 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['fsto_id', 'fsto_uid', 'fsto_createdOn', 'fsto_source', 'fsto_destination', 'fsto_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $orquery = "SELECT id,(SELECT name FROM retaline_godown_boy WHERE id = boy_id) as boy_name,order_id,status,CASE WHEN status = 1 THEN 'Request Sent'
                WHEN status = 2 THEN 'Accepted'
                 WHEN status = 3 THEN 'Rejected'
                WHEN status = 4 THEN 'Time Out'
            END AS ordersreqtatus,DATE_FORMAT(created_at,'%d-%m-%Y %H:%i:%s') as created_at,DATE_FORMAT(updated_at,'%d-%m-%Y %H:%i:%s') as updated_at,'-' AS accepted_time,'-' AS scan_start_time,'-' AS last_scan_time,'-' AS completed_time "
            . "FROM retaline_godown_boy_orders_request WHERE order_pk_id = '{$_POST['orderId']}' ORDER BY  CAST({$sort} as char) {$dir},binary  {$sort} {$dir}";
        //                / AND branch_id = {$_POST['current_branch_id']}

        $datas = $db->getMulipleData($orquery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                if ($datas[$i]['status'] == 2) {
                    $boyQuery = "SELECT id,(SELECT name FROM retaline_godown_boy WHERE id = boy_id) as boy_name,order_id,status,CASE WHEN status = 1 THEN 'Accepted'
                WHEN status = 2 THEN 'Scanning started'
                 WHEN status = 3 THEN 'Incomplete orders'
                WHEN status = 4 THEN 'Completed'
                WHEN status = 5 THEN 'Revoked'
            END AS ordersreqtatus,DATE_FORMAT(created_at,'%d-%m-%Y') as created_at,DATE_FORMAT(updated_at,'%d-%m-%Y %H:%i:%s') as updated_at,
DATE_FORMAT(accepted_time,'%d-%m-%Y %H:%i:%s') as accepted_time,DATE_FORMAT(scan_start_time,'%d-%m-%Y %H:%i:%s') as scan_start_time,DATE_FORMAT(last_scan_time,'%d-%m-%Y %H:%i:%s') as last_scan_time,DATE_FORMAT(completed_time,'%d-%m-%Y %H:%i:%s') as completed_time  
FROM retaline_godown_boy_orders WHERE bgor_id = {$datas[$i]['id']} ORDER BY  CAST({$sort} as char) {$dir},binary  {$sort} {$dir}";
                    $result = $db->getFromDB($boyQuery, true);
                    if ($result['status'] == 1) {

                        $datas['a' . $i]['id'] = $result['id'];
                        $datas['a' . $i]['boy_name'] = $result['boy_name'];
                        $datas['a' . $i]['order_id'] = $result['order_id'];
                        $datas['a' . $i]['ordersreqtatus'] = 'Accepted';
                        $datas['a' . $i]['created_at'] = $result['created_at'];
                        $datas['a' . $i]['updated_at'] = $result['updated_at'];
                        $datas['a' . $i]['accepted_time'] = $result['accepted_time'];
                    } else if ($result['status'] == 5) {
                        $datas['a' . $i]['id'] = $result['id'];
                        $datas['a' . $i]['boy_name'] = $result['boy_name'];
                        $datas['a' . $i]['order_id'] = $result['order_id'];
                        $datas['a' . $i]['ordersreqtatus'] = 'Accepted';
                        $datas['a' . $i]['created_at'] = $result['created_at'];
                        $datas['a' . $i]['updated_at'] = $result['updated_at'];
                        $datas['a' . $i]['accepted_time'] = $result['accepted_time'];

                        $datas['b' . $i]['id'] = $result['id'];
                        $datas['b' . $i]['boy_name'] = $result['boy_name'];
                        $datas['b' . $i]['order_id'] = $result['order_id'];
                        $datas['b' . $i]['ordersreqtatus'] = 'Revoked';
                        $datas['b' . $i]['created_at'] = $result['created_at'];
                        $datas['b' . $i]['updated_at'] = $result['updated_at'];
                        $datas['b' . $i]['accepted_time'] = $result['updated_at'];
                    }
                }
            }
            $count = count($datas);
            echo '{"totalCount":"', $count, '","data":' . json_encode(array_values($datas)) . '}';
            //echo json_encode($qry);
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';

        // $db->printGridJson($countQuery, $listQuery);
        break;
    case 'packageView':
        ob_start();
        include('packageView.php');
        $resHtml = ob_get_clean();
        echo $resHtml;
        break;
    case 'deleteFromPackingOrder':
        $fstod_id = $_POST['fstod_id'];
        $fsto_id = $_POST['fsto_id'];

        $data['fstro_isRemoved'] = 1;
        $data['fstro_updatedOn'] = date("Y-m-d H:i");
        $data['fstro_updatedBy'] = $_SESSION['admin']->Finascop_UserId;

        $db->query('begin');
        //$status = $db->perform('finascop_stock_transfer_order_details', $data, 'update', "fstod_id = {$fstod_id}");
        $delQry = "DELETE FROM finascop_stock_transfer_order_details WHERE fstod_id = {$fstod_id}";
        $status = $db->query($delQry);

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Deleted Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while deleting data' }}";
        }
        break;
    case 'updateTotalQtyinPackingOrder':
        $fstod_id = $_POST['fstod_id'];
        $fsto_id = $_POST['fsto_id'];
        $new_totalqty = $_POST['new_totalqty'];
        $currentQty = $_POST['currentQty'];

        $fstodDetails['fsto_ItemQty'] = $new_totalqty;
        $fstodDetails['fsto_prevItemQty'] = $currentQty;
        $fstodDetails['fstro_updatedOn'] = date("Y-m-d H:i");
        $fstodDetails['fstro_updatedBy'] = $_SESSION['admin']->Finascop_UserId;

        $orderStatus = $db->getItemFromDB("SELECT fsto_status FROM finascop_stock_transfer_order WHERE fsto_id = {$fsto_id}");

        $db->query('begin');
        if ($orderStatus == 6) {
            $status = $db->perform('finascop_stock_transfer_order_details', $fstodDetails, 'update', " fstod_id = {$fstod_id}");
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
            exit();
        }
        $status = $db->query('commit');

        if ($status == 1) {
            echo "{success: true,msg:'Updated Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'listScheduledOrderData':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'fsto_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'string':
                        if ($field['field'] == 'fsto_source') {
                            $fsto_source = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(br_ID),0) FROM finascop_branch WHERE br_Name LIKE '{$field['data']['value']}%' ");
                            $search .= " AND fsto_source IN({$fsto_source}) ";
                        } else if ($field['field'] == 'fsto_destination') {
                            $fsto_destination = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(br_ID),0) FROM finascop_branch WHERE br_Name LIKE '{$field['data']['value']}%' ");
                            $search .= " AND fsto_destination IN({$fsto_destination}) ";
                        } else {
                            $search .= " and " . $field['field'] . " LIKE '" . $field['data']['value'] . "%' ";
                        }
                        break;
                    case 'date':
                        //                        $value = str_replace("/", "", $value);
                        //                        $value = substr($value, 4, 4) . substr($value, 0, 2) . substr($value, 2, 2);
                        $value = date('Y-m-d', strtotime($field['data']['value']));
                        $search .= " AND  DATE_FORMAT(fsto_createdOn,'%Y-%m-%d') " . $comparisons[$field['data']['comparison']] . " '{$value}'";

                        break;
                    case 'list':
                        if ($field['field'] == 'fsto_statusName') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $search .= " AND fsto_statusName IN ('" . $fiterItem . "')";
                        }
                        break;
                        /* if ($field['data']['value'] != "") {
                      $checkComa = strstr($field['data']['value'], ',');
                      if ($checkComa != '') {
                      $fiterItem = $field['data']['value'];
                      $fiterItem = str_replace(',', "','", $fiterItem);
                      $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                      } else {
                      $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                      }
                      } */
                }
            }
        }
        if ($sort == 'fstoCreatedOn') {
            $sort = 'fsto_id';
        }
        $branchName = $_POST['branchName'];
        $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;
        $sourcePyramid = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch where br_ID = {$br_ID}");
        if ($sourcePyramid == 1) {
            $fsto_ordertypeName = "'CPD to CS'";
        } else if ($sourcePyramid == 2) {
            $fsto_ordertypeName = "'CS to Distributor'";
        } else if ($sourcePyramid == 3) {
            $fsto_ordertypeName = "'Distributor to Retailor'";
        } else {
            $fsto_ordertypeName = "'-'";
        }
        if ($_SESSION['admin']->br_PyramidLevel == 1) {
            $where = " AND fsto_source =" . $br_ID;
        } else {
            $where = " AND fsto_source =" . $_SESSION['admin']->finascop_current_branch_id;
        }

        $query = "SELECT fo.fsto_id as fsto_id,fo.fsto_uid as fsto_uid,fstr_id,(SELECT SUM(fsto_ItemWeight) FROM finascop_stock_transfer_order_details fd WHERE fo.fsto_id= fd.fsto_id) as fsto_ItemWeight,"
            . "(SELECT SUM(fsto_ItemVolume) FROM finascop_stock_transfer_order_details fd WHERE fo.fsto_id= fd.fsto_id) as fsto_ItemVolume,fsto_source,fsto_sourcetype,fsto_destination,fsto_destinationtype,"
            . "(SELECT CONCAT(br_Name ,'-',branch_shortname) FROM finascop_branch where br_ID = fsto_source) as fsto_sourceName,fsto_isalreadypacked,"
            . "CASE WHEN fsto_ordertype=0 THEN 'Branch Transfer' WHEN fsto_ordertype=1 THEN 'B2C' WHEN fsto_ordertype=2 THEN 'B2B' WHEN fsto_ordertype=3 THEN 'Return' WHEN fsto_ordertype=4 THEN 'Branch Distribution' END AS fsto_ordertype,"
            . "CASE WHEN fsto_type=0 THEN 'User Created' WHEN fsto_type=1 THEN 'System Created' END AS fsto_type,"
            . "(SELECT fstos_status FROM finascop_stock_transfer_order_status WHERE fstos_id = fsto_status) as fsto_statusName,fsto_status,DATE_FORMAT(fsto_createdOn,'%d-%m-%Y') as fstoCreatedOn,fsto_createdOn,"
            . "CASE WHEN fsto_ordertype = 0 THEN (SELECT CONCAT(br_Name ,'-',branch_shortname) FROM finascop_branch where br_ID = fsto_destination) "
            . "WHEN fsto_ordertype = 1 THEN (SELECT cust_customer_name FROM retaline_customer WHERE cust_id = fsto_destination) "
            . "WHEN fsto_ordertype = 2 THEN (SELECT b2b_Customer_Name FROM retaline_B2Bcustomer where b2b_Customer_ID = fsto_destination) "
            . "WHEN fsto_ordertype = 3 THEN (SELECT CONCAT(br_Name ,'-',branch_shortname) FROM finascop_branch where br_ID = fsto_destination) END AS fsto_destinationName,"
            . "IF(fsto_ordertype = 1,(SELECT order_slot_date FROM retaline_customer_order WHERE order_id = fstr_id),'-') AS slotDate,"
            . "IF(fsto_ordertype = 1,(SELECT CONCAT(DATE_FORMAT(rbds_time_from,'%h:%i %p'),'-',DATE_FORMAT(rbds_time_to,'%h:%i %p')) FROM retaline_branch_delivery_slot WHERE rbds_id = (SELECT order_slot_id FROM retaline_customer_order WHERE order_id = fstr_id)),'-') AS slotTime "
            . "  FROM finascop_stock_transfer_order  fo";

        $countQuery = "SELECT COUNT(*) FROM ($query) as trdocount {$search} {$where} AND fsto_status = 11 ORDER BY /*CAST({$sort} as char) {$dir},binary*/ {$sort} {$dir} ";

        $listQuery = "SELECT * FROM ({$query}) AS trdo {$search} {$where} AND fsto_status = 11 ORDER BY  /*CAST({$sort} as char) {$dir},binary*/  {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'moveToPackingOrder':
        $fsto_id = $_POST['fsto_id'];
        $fsto_status = $_POST['fsto_status'];
        $fsto_ordertype = $_POST['fsto_ordertype'];
        $fstr_id = $_POST['fstr_id'];


        $db->query('begin');
        if (($fsto_status == 11) && ($fsto_ordertype == 'B2C')) {
            $fstodDetails['fsto_status'] = 6;
            $fstodDetails['fsto_updateon'] = date("Y-m-d H:i");
            $fstodDetails['fsto_updateby'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('finascop_stock_transfer_order', $fstodDetails, 'update', " fsto_id = {$fsto_id}");

            $rcodata['status_id'] = 7;
            $rcodata['updated_at'] = date("Y-m-d H:i:s");
            $status = $db->perform('retaline_customer_order', $rcodata, 'update', " order_id = {$fsto_id}");
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
            exit();
        }
        $status = $db->query('commit');

        if ($status == 1) {
            echo "{success: true,msg:'Updated Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }

        break;
    case 'getretalinePackaging':
        if ($_POST['rpckm_type'] > 0) {
            $primaryBt = $_POST['rpckm_type'];
        } else {
            $primaryBt = 0;
        }
        $qry = "select rpckm_id,rpckm_name from retaline_package_master where rpckm_status = 1 order by rpckm_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'savePackageDetails':
        $packcount = $_POST['packcount'];
        $fstoId = $_POST['packorder'];
        $orderType = $_POST['packordertype'];
        $db->query('begin');
        for ($i = 1; $i <= $packcount; $i++) {
            $packData['rtopd_fstoId'] = $fstoId;
            $packData['rtopd_orderType'] = $orderType;
            $packData['rtopd_packets'] = $_POST['packets_' . $i];
            $packData['rtopd_packaging'] = $_POST['packaging_' . $i];
            $packData['rtopd_packetweigh'] = $_POST['packetweigh_' . $i];
            $packData['rtopd__createdOn'] = date("Y-m-d H:i");
            $packData['rtopd__createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('retaline_transfer_order_pack_details', $packData);
        }

        $fstoStatus['fsto_updateon'] = date("Y-m-d H:i:s");
        $fstoStatus['fsto_status'] = 10;
        $fstoStatus['fsto_updateby'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('finascop_stock_transfer_order', $fstoStatus, 'update', "fsto_id = {$fstoId}");

        $fstrId = $db->getFromDB("SELECT fstr_id,fsto_ordertype FROM finascop_stock_transfer_order WHERE fsto_id = {$fstoId}", true);
        switch ($fstrId['fsto_ordertype']) {
            case 1:
                $rcoStatus['updated_at'] = date("Y-m-d H:i:s");
                $rcoStatus['status_id'] = 9;
                $status = $db->perform('retaline_customer_order', $rcoStatus, 'update', "order_id = {$fstrId['fstr_id']}");
                break;
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved '}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listTransferOrderCompleted':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'fsto_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'string':
                        if ($field['field'] == 'fsto_source') {
                            $fsto_source = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(br_ID),0) FROM finascop_branch WHERE br_Name LIKE '{$field['data']['value']}%' ");
                            $search .= " AND fsto_source IN({$fsto_source}) ";
                        } else if ($field['field'] == 'fsto_destination') {
                            $fsto_destination = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(br_ID),0) FROM finascop_branch WHERE br_Name LIKE '{$field['data']['value']}%' ");
                            $search .= " AND fsto_destination IN({$fsto_destination}) ";
                        } else {
                            $search .= " and " . $field['field'] . " LIKE '" . $field['data']['value'] . "%' ";
                        }
                        break;
                    case 'date':
                        //                        $value = str_replace("/", "", $value);
                        //                        $value = substr($value, 4, 4) . substr($value, 0, 2) . substr($value, 2, 2);
                        $value = date('Y-m-d', strtotime($field['data']['value']));
                        $search .= " AND  DATE_FORMAT(fsto_createdOn,'%Y-%m-%d') " . $comparisons[$field['data']['comparison']] . " '{$value}'";

                        break;
                    case 'list':
                        if ($field['field'] == 'fsto_statusName') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $search .= " AND fsto_statusName IN ('" . $fiterItem . "')";
                        }
                        break;
                        /* if ($field['data']['value'] != "") {
                      $checkComa = strstr($field['data']['value'], ',');
                      if ($checkComa != '') {
                      $fiterItem = $field['data']['value'];
                      $fiterItem = str_replace(',', "','", $fiterItem);
                      $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                      } else {
                      $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                      }
                      } */
                }
            }
        }
        if ($sort == 'fstoCreatedOn') {
            $sort = 'fsto_id';
        }
        $branchName = $_POST['branchName'];
        $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;
        $sourcePyramid = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch where br_ID = {$br_ID}");
        if ($sourcePyramid == 1) {
            $fsto_ordertypeName = "'CPD to CS'";
        } else if ($sourcePyramid == 2) {
            $fsto_ordertypeName = "'CS to Distributor'";
        } else if ($sourcePyramid == 3) {
            $fsto_ordertypeName = "'Distributor to Retailor'";
        } else {
            $fsto_ordertypeName = "'-'";
        }
        if ($_SESSION['admin']->br_PyramidLevel == 1) {
            $where .= " ";
            /*if ($_SESSION['admin']->IsSuperUser == 'Yes') {
                $where .= " ";
            } else {
                $where = " AND fstosSource =" . $br_ID;
            }*/
        } else {
            $where = " AND fstosSource =" . $_SESSION['admin']->finascop_current_branch_id;
        }

        $query = "SELECT 
    fo.fsto_id,
    fo.fsto_uid,
    fo.fstr_id,
    
    -- Total Weight & Volume
    (SELECT SUM(fsto_ItemWeight) FROM finascop_stock_transfer_order_details fd WHERE fd.fsto_id = fo.fsto_id) AS fsto_ItemWeight,
    (SELECT SUM(fsto_ItemVolume) FROM finascop_stock_transfer_order_details fd WHERE fd.fsto_id = fo.fsto_id) AS fsto_ItemVolume,

    fo.fsto_isInvoiced,
    fo.fsto_sourcetype,
    fo.fsto_destination,
    fo.fsto_destinationtype,
    fo.fsto_isalreadypacked,
    
    -- Order Type Name
    CASE fo.fsto_ordertype
        WHEN 0 THEN 'Branch Transfer'
        WHEN 1 THEN 'B2C'
        WHEN 2 THEN 'B2B'
        WHEN 3 THEN 'Return'
        WHEN 4 THEN 'Distribution'
    END AS fsto_ordertypeName,
    fo.fsto_ordertype,

    -- Order Branch Type
    IF(fo.fsto_ordertype = 1, rco.order_branch_type_id, 0) AS order_branch_type_id,

    -- Source Type
    CASE 
        WHEN fo.fsto_ordertype = 1 AND rco.order_branch_type_id = 2 THEN 
            IF(sp.asctedbrach_cpr > 0, sp.asctedbrach_cpr, 1)
        ELSE fo.fsto_source
    END AS fstosSource,
    fo.fsto_source,

    -- Source Name
    CONCAT(src.br_Name, '-', src.branch_shortname) AS fsto_sourceName,

    -- Order Type
    CASE fo.fsto_type
        WHEN 0 THEN 'User Created'
        WHEN 1 THEN 'System Created'
    END AS fsto_type,

    -- Status Name
    st.fstos_status AS fsto_statusName,
    fo.fsto_status,

    -- Created On
    DATE_FORMAT(fo.fsto_createdOn, '%d-%m-%Y') AS fstoCreatedOn,
    fo.fsto_createdOn,

    -- Destination Name
    CASE fo.fsto_ordertype
        WHEN 0 THEN CONCAT(dst.br_Name, '-', dst.branch_shortname)
        WHEN 1 THEN rc.cust_customer_name
        WHEN 2 THEN b2b.b2b_Customer_Name
        WHEN 3 THEN CONCAT(dst.br_Name, '-', dst.branch_shortname)
    END AS fsto_destinationName,

    -- Invoice config
    (SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'IS_INVOICE') AS ownInvoice,

    -- Branch Type and Parent Packing
    src.br_type AS branchType,
    src.br_parentPacking,

    -- Is Return
    CASE 
        WHEN fo.fsto_ordertype = 0 THEN IF(fsr.fsr_id IS NOT NULL, 1, 0)
        WHEN fo.fsto_ordertype = 1 THEN 0
        WHEN fo.fsto_ordertype = 2 THEN 0
        WHEN fo.fsto_ordertype = 3 THEN 1
        WHEN fo.fsto_ordertype = 4 THEN 0
    END AS isReturn,

    -- Shipment Status Logic
    CASE 
        WHEN fo.fsto_hasShipmentCreated = 0 THEN 'NA'
        WHEN fo.fsto_hasShipmentCreated = 1 THEN 'Shipment Created'
        WHEN fo.fsto_hasShipmentCreated = 2 AND TIMESTAMPDIFF(MINUTE, fo.fsto_updateon, NOW()) <= 5 THEN 'Shipment Initiated'
        WHEN fo.fsto_hasShipmentCreated = 2 AND TIMESTAMPDIFF(MINUTE, fo.fsto_updateon, NOW()) > 5 THEN 'Shipment Failed'
    END AS fsto_shipmentStatus,

    fo.fsto_hasShipmentCreated,
    fo.fsto_updateon

FROM finascop_stock_transfer_order fo

-- JOINs
LEFT JOIN retaline_customer_order rco ON rco.order_id = fo.fstr_id
LEFT JOIN finascop_stock_party sp ON sp.stpa_id = fo.fsto_source
LEFT JOIN finascop_branch src ON src.br_ID = fo.fsto_source
LEFT JOIN finascop_branch dst ON dst.br_ID = fo.fsto_destination
LEFT JOIN retaline_customer rc ON rc.cust_id = fo.fsto_destination
LEFT JOIN retaline_B2Bcustomer b2b ON b2b.b2b_Customer_ID = fo.fsto_destination
LEFT JOIN finascop_stock_transfer_order_status st ON st.fstos_id = fo.fsto_status
LEFT JOIN finascop_stock_request fsr ON fsr.fstr_id = fo.fstr_id";

        $countQuery = "SELECT COUNT(*) FROM ($query) as trdocount {$search} {$where} AND fsto_status IN (10)  ORDER BY /*CAST({$sort} as char) {$dir},binary*/ {$sort} {$dir} ";

        $listQuery = "SELECT * FROM ({$query}) AS trdo {$search} {$where} AND fsto_status IN (10) ORDER BY  /*CAST({$sort} as char) {$dir},binary*/  {$sort} {$dir} limit $start,$limit";
        //AND fsto_ordertype < 4
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'generateInvoice':
        $toDetails = $db->getFromSafe("SELECT * FROM finascop_stock_transfer_order WHERE fsto_id = ?", "i", [$_POST['fsto_id']], true);
        $toDetailItems = $db->getMultipleData("SELECT fsto_ItemId,fsto_ItemQty,fsto_ItemQtyL3Received FROM finascop_stock_transfer_order_details WHERE fsto_id = {$_POST['fsto_id']}", true);

        $db->query('begin');
        $fsto_ordertype = $toDetails['fsto_ordertype'];
        $invData['tiInvoiceNumber'] = getNewTransferInvoiceNumber($toDetails['fsto_destination']);
        $invData['ti_fsto_id'] = $_POST['fsto_id'];
        $invData['tiOrdertype'] = $toDetails['fsto_ordertype'];
        $invData['tiSource'] = $toDetails['fsto_source'];
        $invData['tiDestination'] = $toDetails['fsto_destination'];
        $invData['tiCreatedOn'] = date("Y-m-d H:i:s");
        $invData['tiCreatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('TransferInvoice', $invData);
        $lastId = $db->insert_id();

        $subTotal = 0;
        $taxableValueTotal = 0;
        $itemsgstTotal = 0;
        $itemcgstTotal = 0;
        $j = 0;
        for ($i = 0; $i < count($toDetailItems); $i++) {
            $damageReturnqty = 0;
            $stockReturnqty = 0;
            $unitRate = 0;
            $damageReturnqty = $db->getItemFromDB("SELECT fsto_ItemQty FROM finascop_stock_transfer_order_details ftsod INNER JOIN finascop_stock_transfer_order fsto ON fsto.fsto_id = ftsod.fsto_id "
                . "WHERE fsto_ItemId = {$toDetailItems[$i]['fsto_ItemId']} AND fsto_source = {$toDetails['fsto_destination']} AND fsto_destination = {$toDetails['fsto_source']} "
                . "AND DATE_FORMAT(fsto_createdOn,'%d-%m-%Y') = '{$orderDetails['booked_at']}' "
                . "AND fsto_ordertype = 3 AND fsto_status = 10");
            $stockReturnqty = $db->getItemFromDB("SELECT fsto_ItemQtyL3Received FROM finascop_stock_transfer_order_details ftsod "
                . "INNER JOIN finascop_stock_transfer_order fsto ON fsto.fsto_id = ftsod.fsto_id "
                . "INNER JOIN finascop_stock_transfer_request fstr ON fstr.fstr_id = fsto.fstr_id "
                . "INNER JOIN finascop_stock_request fsr ON fsr.fstr_id = fstr.fstr_id "
                . "INNER JOIN finascop_stock_request_details fsrd ON fsrd.fsr_id = fsr.fsr_id and fsr_ItemId = {$toDetailItems[$i]['fsto_ItemId']} AND fsr_rdc_id = {$toDetails['fstr_id']} "
                . "WHERE fsto_ItemId = {$toDetailItems[$i]['fsto_ItemId']} AND fsto_source = {$toDetails['fsto_destination']} AND fsto_destination = {$toDetails['fsto_source']} "
                . "AND fsto_ordertype = 0 AND fsto_status = 10");
            $totalReturn = $damageReturnqty + $stockReturnqty;
            $itemGST = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$toDetailItems[$i]['fsto_ItemId']} ");
            $booked_at = date('Y-m-d H:i:s', strtotime($invData['tiCreatedOn']));
            switch ($fsto_ordertype) {
                case 0:
                    $unitRate = $db->getItemFromDB("SELECT transfer_billing_price FROM finascop_raw_branch_inventory WHERE stit_id = {$toDetailItems[$i]['fsto_ItemId']} AND branch_id = {$toDetails['fsto_source']} AND created_at <= '{$booked_at}' ORDER BY id DESC LIMIT 1 ");
                    if ($unitRate < 1) {
                        echo '{"success":false,"valid":false,"msg": "Item price is missing."}';
                        exit();
                    }
                    break;
                case 4:
                    $unitRate = $db->getItemFromDB("SELECT fishmart_price FROM finascop_raw_branch_inventory WHERE stit_id = {$toDetailItems[$i]['fsto_ItemId']} AND branch_id = {$toDetails['fsto_source']} AND created_at <= '{$booked_at}' ORDER BY id DESC LIMIT 1 ");
                    if ($unitRate < 1) {
                        echo '{"success":false,"valid":false,"msg": "Item price is missing."}';
                        exit();
                    }
                    break;
            }
            $amount = $unitRate * ($toDetailItems[$i]['fsto_ItemQty'] - $totalReturn);
            $amount = round($amount, 2);
            if ($itemGST > 0) {
                $itemCGSTpercent = $itemGST / 2;
                $itemSGSTpercent = $itemGST / 2;
                $taxableValue = $amount * 100 / (100 + $itemGST);
                $taxableValue = round($taxableValue, 2);
                $itemcgst = $taxableValue * $itemCGSTpercent / 100;
                $itemcgst = round($itemcgst, 2);

                $itemsgst = $taxableValue * $itemSGSTpercent / 100;
                $itemsgst = round($itemsgst, 2);
            } else {
                $taxableValue = 0;
                $itemcgst = 0;
                $itemsgst = 0;
            }

            $taxableValueTotal = $taxableValueTotal + $taxableValue;
            $itemsgstTotal = $itemsgstTotal + $itemsgst;
            $itemcgstTotal = $itemcgstTotal + $itemcgst;
            $subTotal = $subTotal + $amount;
            $order_amt_total = $subTotal + $taxableValue;

            $invDetailData['ti_id'] = $lastId;
            $invDetailData['tiItemId'] = $toDetailItems[$i]['fsto_ItemId'];
            $invDetailData['tiItemStockQty'] = $toDetailItems[$i]['fsto_ItemQty'];
            $invDetailData['tiItemReturnQty'] = $totalReturn;
            $invDetailData['tiItemUnitRate'] = $unitRate;
            $invDetailData['tiItemGST'] = $itemGST;
            $invDetailData['tiItemAmount'] = $amount;
            $invDetailData['tiItemTaxableValue'] = $taxableValue;
            $invDetailData['tiItemCGST'] = $itemcgst;
            $invDetailData['tiItemSGST'] = $itemsgst;
            $invDetailData = array_filter($invDetailData);
            //print_r($invDetailData);
            $status = $db->perform('TransferInvoiceDetails', $invDetailData);
        }
        $invUpData['tiCGST'] = $itemcgstTotal;
        $invUpData['tiSGST'] = $itemsgstTotal;
        $invUpData['tiTax'] = $taxableValue;
        $invUpData['tiSubTotal'] = $subTotal;
        $invUpData['tiHandlingCharge'] = $toDetails['fsto_handlingcharge'];

        $tcs = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TCS'");
        $invUpData['tiTCS'] = $tcs;
        $tiTcsValue = ($subTotal + $toDetails['fsto_handlingcharge']) * $tcs / 100;
        $invUpData['tiTCSValue'] = $tiTcsValue;
        $order_amt_total = $order_amt_total + $toDetails['fsto_handlingcharge'];
        $invUpData['tiTotal'] = $order_amt_total;
        $invUpData['tiUpdatedtedOn'] = date("Y-m-d H:i:s");
        $invUpData['tiUpdatedtedBy'] = $_SESSION['admin']->Finascop_UserId;
        $invUpData = array_filter($invUpData);

        $status = $db->perform('TransferInvoice', $invUpData, 'update', " id = {$lastId}");
        switch ($fsto_ordertype) {
            case 0:
                break;
            case 4:
                $status = $db->query("update retaline_distribution_chart SET rdc_isInvoiced = 1 where rdc_id = {$toDetails['fstr_id']}");
                break;
        }

        $toup['fsto_isInvoiced'] = 1;
        $toup['fsto_updateon'] = date("Y-m-d H:i:s");
        $toup['fsto_updateby'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('finascop_stock_transfer_order', $toup, 'update', "fsto_id = " . intval($_POST['fsto_id']));

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Invoice Generated'}";
        } else {
            echo "{'success':False,'valid':false,'msg': 'Error While Saving.'}";
        }
        break;
    case 'generateshipment':
        $fsto_id = $_POST['fstoId'];

        $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'SHIPPINGURL'");
        $fields = array(
            "fstoId" => $fsto_id
        );
        $fields_string = json_encode($fields);
        //print_r($fields_string);
        $opts = array(
            CURLOPT_URL => $url,
            CURLINFO_CONTENT_TYPE => "application/json",
            CURLOPT_BINARYTRANSFER => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST => count($fields),
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        header("Content-Type: application/json");
        $result = json_decode($data, true);
        if ($result['status'] == 'success') {
            echo '{"success":true,"valid":true,"msg":"Shipment Created ."}';
        } else {
            echo '{"success":true,"valid":false,"msg":"Failed to create shipment ."}';
        }
        break;
    case 'setPackCompleted':
        $fsto_ordertype = $_POST['fsto_ordertype'];
        $fsto_id = $_POST['fsto_id'];
        $db->query('begin');
        $fstoStatus['fsto_updateon'] = date("Y-m-d H:i:s");
        $fstoStatus['fsto_status'] = 10;
        $status = $db->perform('finascop_stock_transfer_order', $fstoStatus, 'update', "fsto_id = {$fsto_id}");
        $qorStatus = $db->getItemFromDB("SELECT quor_Status FROM qugeo_order WHERE quor_TransferOrder_id = {$fsto_id}");
        if ($fsto_ordertype == 1) {
            if ($qorStatus == 15) {
                $b2cData['status_id'] = 18;
            } else {
                $b2cData['status_id'] = 9;
            }

            $b2cData['updated_at'] = date("Y-m-d H:i:s");
            $status = $db->perform('retaline_customer_order', $b2cData, 'order_id', "fsto_id = {$fsto_id}");
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Packing completed'}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'getBoxDetails':
        $fsto_ordertype = $_POST['fsto_ordertype'];
        $fsto_id = $_POST['fsto_id'];
        $boxData = $db->getFromDB("SELECT quor_PacketCount,quor_RefNo,quor_DeliveryMethodsAllowed FROM qugeo_order WHERE quor_TransferOrder_id = {$fsto_id}", true);
        $packinglist = array();
        if ($boxData['quor_PacketCount'] > 0) {
            for ($i = 1; $i <= $boxData['quor_PacketCount']; $i++) {
                $packid = "{$boxData['quor_RefNo']}/{$boxData['quor_PacketCount']}/{$i}";
                $packinglist[] = $packid;
            }
            echo '{"success":true,"valid":true,"msg":"Box Details.","packcount":"' . count($packinglist) . '","data":' . json_encode($packinglist) . '}';
        } else {
            echo '{"success":false,"valid":false,"msg":"Issue in order,kindly proceed with another order."}';
        }

        break;
    case 'setManuallyAssign':
        $fsto_id = $_POST['fsto_id'];
        $db->query('begin');
        $qgeoStatus['fsto_updateon'] = date("Y-m-d H:i:s");
        $qgeoStatus['fsto_status'] = 6;

        $status = $db->perform('finascop_stock_transfer_order', $qgeoStatus, 'update', "fsto_id = {$fsto_id} and fsto_status = 2");

        $rcStatus['updated_at'] = date("Y-m-d H:i:s");
        $rcStatus['status_id'] = 7;

        $fstr_id = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = {$fsto_id}");

        $status = $db->perform('retaline_customer_order', $rcStatus, 'update', "order_id = {$fstr_id} and status_id = 5");
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Order Status Changed'}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'getPackageSlip':
        ob_start();
        include('packageSlip.php');
        $resHtml = ob_get_clean();
        echo $resHtml;
        break;
    case 'showShipmentLog':
        ob_start();
        include('shipmentLog.php');
        $resHtml = ob_get_clean();
        echo $resHtml;
        break;
}
