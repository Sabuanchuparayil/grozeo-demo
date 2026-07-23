<?php

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
            $where = " AND fsto_source =" . $br_ID;
        } else {
            $where = " AND fsto_source =" . $_SESSION['admin']->finascop_current_branch_id;
        }

        $query = "SELECT fo.fsto_id as fsto_id,fo.fsto_uid as fsto_uid,fstr_id,(SELECT SUM(fsto_ItemWeight) FROM finascop_stock_transfer_order_details fd WHERE fo.fsto_id= fd.fsto_id) as fsto_ItemWeight,"
                . "(SELECT SUM(fsto_ItemVolume) FROM finascop_stock_transfer_order_details fd WHERE fo.fsto_id= fd.fsto_id) as fsto_ItemVolume,fsto_source,fsto_sourcetype,fsto_destination,fsto_destinationtype,"
                . "(SELECT br_Name FROM finascop_branch where br_ID = fsto_source) as fsto_sourceName,fsto_isalreadypacked,"
                . "CASE WHEN fsto_ordertype=0 THEN 'CPD TO BR' WHEN fsto_ordertype=1 THEN 'B2C' WHEN fsto_ordertype=2 THEN 'B2B' WHEN fsto_ordertype=3 THEN 'BR TO CPD' END AS fsto_ordertype,"
                . "CASE WHEN fsto_type=0 THEN 'User Created' WHEN fsto_type=1 THEN 'System Created' END AS fsto_type,"
                . "(SELECT fstos_status FROM finascop_stock_transfer_order_status WHERE fstos_id = fsto_status) as fsto_statusName,fsto_status,DATE_FORMAT(fsto_createdOn,'%d-%m-%Y') as fstoCreatedOn,fsto_createdOn,"
                . "CASE WHEN fsto_ordertype = 0 THEN (SELECT br_Name FROM finascop_branch where br_ID = fsto_destination) "
                . "WHEN fsto_ordertype = 1 THEN (SELECT cust_customer_name FROM retaline_customer WHERE cust_id = fsto_destination) "
                . "WHEN fsto_ordertype = 2 THEN (SELECT b2b_Customer_Name FROM retaline_B2Bcustomer where b2b_Customer_ID = fsto_destination) "
                . "WHEN fsto_ordertype = 3 THEN (SELECT br_Name FROM finascop_branch where br_ID = fsto_destination) END AS fsto_destinationName "
                . "  FROM finascop_stock_transfer_order  fo";

        $countQuery = "SELECT COUNT(*) FROM ($query) as trdocount {$search} {$where} AND fsto_status<>9 ORDER BY /*CAST({$sort} as char) {$dir},binary*/ {$sort} {$dir} ";

        $listQuery = "SELECT * FROM ({$query}) AS trdo {$search} {$where} AND fsto_status<>9 ORDER BY  /*CAST({$sort} as char) {$dir},binary*/  {$sort} {$dir} limit $start,$limit";

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
        $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch where br_status = 'Active' {$cond}", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'getBranchName':
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch WHERE br_status = 'Active' AND (br_cpd={$branch_id} OR br_ID={$branch_id})", true);
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
            $countQuery = "SELECT COUNT(*) FROM retaline_godown_boy  WHERE  branch_id={$order_branch_id}";

            $listQuery = "SELECT id,name,has_open_orders,phone,is_offline,if(is_offline = 1,'Offline','Online') as liveStatus from retaline_godown_boy WHERE  branch_id={$order_branch_id} ORDER BY name ASC";

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

        $listQry = "SELECT fo.fsto_uid as fsto_uid,fo.fsto_id as fsto_id,fsto_ItemId,fsto_createdOn,fsto_destination,fsto_destination,fsto_source,(SELECT br_Name FROM finascop_branch where br_ID = fsto_source) as fsto_source,"
                . "(SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = fsto_ItemId) as item_name,fsto_ItemQty,fsto_pkdQty,(SELECT br_Name FROM finascop_branch where br_ID = fsto_destination) as branch,"
                . "fo.fsto_id as fsto_id,fstro_ItemMRP as mrp"
                . " FROM finascop_stock_transfer_order fo INNER JOIN finascop_stock_transfer_order_details fd ON fo.fsto_id = fd.fsto_id  {$search} AND fo.fsto_id={$fsto_id} ORDER BY {$sort} {$dir} ";
        $datas = $db->getMulipleData($listQry, true);
        $resCount = count($datas);
        $count = $db->getItemFromDB($countQry);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $branch = $db->getItemFromDB("SELECT mrp FROM finascop_stock_branch_inventory WHERE stit_id={$datas[$i]['fsto_ItemId']} AND branch_id={$datas[$i]['fsto_destination']}");
                //$datas[$i]['mrp'] = $branch;
                $datas[$i]['slNo'] = $i + 1;
            }
        }
        echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        break;
    case 'assignOrderPicker':
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
            case 'CPD TO BR':
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
            case 'BR TO CPD':
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
                $tmpitems = [];
                $tmpitems['item_id'] = $fsto_ItemId;
                if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                    $barcode = $db->getItemFromDB("SELECT GROUP_CONCAT(tmp_barcode_code) FROM finascop_stock_transfer_order_details_barcodes_temp WHERE tmp_barcode_itemId={$fsto_ItemId} AND tmp_barcode_fstoId={$order_id} AND rpb_status=1", true);
                    $barcodes = explode(',', $barcode);
                    $tmpitems['barcodes'] = $barcodes;
                } else {
                    $tmpitems['count'] = $fsto_pkdQty;
                }
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
            echo '{"success":true,"valid":false,"msg":"' . $result['error']['msg'][0]. $result['error']['msg'][1] .'"}';
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
            $packingList = $result['packinglist'];
            if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                foreach ($barcodes as $barcod) {
                    $tmp['rpb_status'] = 2;
                    $status = $db->perform("finascop_stock_transfer_order_details_barcodes_temp", $tmp, 'update', 'tmp_barcode_fstoId =' . $order_id . ' AND tmp_barcode_code=' . $barcod);
                }
            }
            echo '{"success":true,"valid":true,"msg":"Order Packed .","packcount":"', count($packingList), '","data":' . json_encode(array_values($packingList)) . '}';
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
            case 'CPD TO BR':
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
            case 'BR TO CPD':
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
                $tmpitems = [];
                $tmpitems['item_id'] = $fsto_ItemId;
                if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                    $barcode = $db->getItemFromDB("SELECT GROUP_CONCAT(tmp_barcode_code) FROM finascop_stock_transfer_order_details_barcodes_temp WHERE tmp_barcode_itemId={$fsto_ItemId} AND tmp_barcode_fstoId={$order_id} AND rpb_status=1", true);
                    $barcodes = explode(',', $barcode);
                    $tmpitems['barcodes'] = $barcodes;
                } else {
                    $tmpitems['count'] = $fsto_pkdQty;
                }
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
            $packingList = $result['packinglist'];
            if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                foreach ($barcodes as $barcod) {
                    $tmp['rpb_status'] = 2;
                    $status = $db->perform("finascop_stock_transfer_order_details_barcodes_temp", $tmp, 'update', 'tmp_barcode_fstoId =' . $order_id . ' AND tmp_barcode_code=' . $barcod);
                }
            }
            echo '{"success":true,"valid":true,"msg":"Order Packed .","packcount":"', count($packingList), '","data":' . json_encode(array_values($packingList)) . '}';
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
        $listQuery = "SELECT fstod_id as bcod_id,fsto_id as bcor_id,fsto_ItemId as stit_ID,fsto_ItemQty as bcod_Count,(SELECT stit_SKU FROM finascop_stock_itemmaster im WHERE im.stit_ID = oi.fsto_ItemId) as stitSKU,"
                . "fsto_pkdQty as bcod_scannedcount "
                . "from finascop_stock_transfer_order_details oi WHERE fsto_id = {$_POST['orderId']} {$filter_part}  ORDER BY $rec_sort $rec_sort_dir ";
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
}
