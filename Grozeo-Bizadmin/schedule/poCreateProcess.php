<?php

class OrderPOProcessorSch {

    public function darkOrdersStatusUpdate() {
        $db = new sqlDb(DSN);
        $now = date('Y-m-d H:i:s');
        $countStockRequestedOrders = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer_order WHERE status_id = 42 ");
        echo '......countStockRequestedOrders......' . $countStockRequestedOrders.'/n';
        if ($countStockRequestedOrders > 0) {
            $packedOrders = $db->getMultipleData("SELECT order_id,order_order_id,updated_at,order_branch_id FROM retaline_customer_order WHERE status_id = 42", true);
            //getStockRequest
            foreach ($packedOrders as $packOrder) {
                $fsr_id = $db->getItemFromDB("SELECT fsr_id FROM order_request_log WHERE order_id = {$packOrder['order_id']}");

                $fstr_id = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_request WHERE fsr_id = {$fsr_id}");
                $fstoDetails = $db->getFromDB("SELECT fsto_id,fsto_status FROM finascop_stock_transfer_order WHERE fstr_id = {$fstr_id} AND fsto_ordertype = 0 ", true);
                if ($fstoDetails['fsto_status'] == 10) {
                    $rcodata['updated_at'] = date('Y-m-d H:i:s');
                    $rcodata['status_id'] = 43;
                    $status = $db->perform('retaline_customer_order', $rcodata, 'update', " status_id = 42 AND order_id = {$packOrder['order_id']}");
                }
            }
        }

        $countPackedOrders = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer_order WHERE status_id = 43 ");
        echo '......countPackedOrders......' . $countPackedOrders.'/n';
        if ($countPackedOrders > 0) {
            $deliveryOrders = $db->getMultipleData("SELECT order_id,order_order_id,updated_at,order_branch_id FROM retaline_customer_order WHERE status_id = 43", true);
            foreach ($deliveryOrders as $deliveryOrder) {
                $fsr_id = $db->getItemFromDB("SELECT fsr_id FROM order_request_log WHERE order_id = {$deliveryOrder['order_id']}");

                $fstr_id = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_request WHERE fsr_id = {$fsr_id}");
                $fstoDetails = $db->getFromDB("SELECT fsto_id,fsto_status FROM finascop_stock_transfer_order WHERE fstr_id = {$fstr_id} AND fsto_ordertype = 0 ", true);
                $qugeoDetail = $db->getFromDB("SELECT quor_id,quor_Status FROM qugeo_order WHERE quor_TransferOrder_id = {$fstoDetails['fsto_id']} AND quor_TransferOrder_Type = 0 ", true);
                if ($qugeoDetail['quor_Status'] == 15) {
                    $rcodata['updated_at'] = date('Y-m-d H:i:s');
                    $rcodata['status_id'] = 44;
                    $status = $db->perform('retaline_customer_order', $rcodata, 'update', " status_id = 43 AND order_id = {$deliveryOrder['order_id']}");
                }
            }
        }

        $countDeliveredOrders = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer_order WHERE status_id = 44 ");
        echo '......countDeliveredOrders......' . $countDeliveredOrders.'/n';
        if ($countDeliveredOrders > 0) {
            $deliveredOrders = $db->getMultipleData("SELECT order_id,order_order_id,updated_at,order_branch_id FROM retaline_customer_order WHERE status_id = 44", true);
            foreach ($deliveredOrders as $deliveredOrders) {
                $fsr_id = $db->getItemFromDB("SELECT fsr_id FROM order_request_log WHERE order_id = {$deliveredOrders['order_id']}");
                $fstr_id = $db->getItemFromDB("SELECT fstr_id FROM finascop_stock_request WHERE fsr_id = {$fsr_id}");
                $fstoDetails = $db->getFromDB("SELECT fsto_id,fsto_status,fsto_requestreceived FROM finascop_stock_transfer_order WHERE fstr_id = {$fstr_id} AND fsto_ordertype = 0 ", true);
                if ($fstoDetails['fsto_requestreceived'] == 2) {
                    $rcodata['updated_at'] = date('Y-m-d H:i:s');
                    $rcodata['status_id'] = 45;
                    $status = $db->perform('retaline_customer_order', $rcodata, 'update', " status_id = 44 AND order_id = {$deliveredOrders['order_id']}");
                }
            }
        }
    }

    public function darkOrdersAvaialble() {
        $db = new sqlDb(DSN);
        $now = date('Y-m-d H:i:s');
        //$data['tpopredet_uniqueid'] = getRandomRef();

        $countHoldOrders = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer_order WHERE status_id = 40  AND order_cutoff_time <= '{$now}'");
        if ($countHoldOrders > 0) {
            $db->query("set group_concat_max_len=95000;");
            $holdOrders = $db->getMultipleData("SELECT order_id,order_order_id,updated_at,order_branch_id FROM retaline_customer_order WHERE status_id = 40 AND order_cutoff_time <= '{$now}'", true);
            $holdOrderIds = $db->getItemFromDB("SELECT GROUP_CONCAT(order_id) FROM retaline_customer_order WHERE status_id = 40 AND order_cutoff_time <= '{$now}'", true);
            foreach ($holdOrders as $holdOrder) {
                echo '......holdOrder......' . $holdOrder['order_id'].'/n';
                $isLogEntry = $db->getItemFromDB("SELECT COUNT(*) FROM order_request_log WHERE order_id = {$holdOrder['order_id']}");
                $brachDetails = $db->getFromDB("SELECT br_typeParent,br_parentPacking FROM finascop_branch WHERE br_ID = {$holdOrder['order_branch_id']}", true);
                if (($brachDetails['br_parentPacking'] == 1) && ($isLogEntry == 0)) {
                    $fsr['fsr_source'] = $holdOrder['order_branch_id'];
                    $fsr['fsr_destination'] = $brachDetails['br_typeParent'];
                    $fsr['fsr_createdOn'] = date("Y-m-d H:i");
                    $fsr['fsr_createdBy'] = 1;
                    $fsr['fsr_initiatedBy'] = $holdOrder['order_branch_id'];

                    $date = date("Y-m-d H:i");
                    $tdy = date("Y-m-d") . " 00:00";
                    $maxId = $db->getItemFromDB("select right(fsr_uid,3)*1 as fsr_uid  from `finascop_stock_request` where `fsr_source` = {$fsr['fsr_source']} and `fsr_createdOn` between '{$tdy}' and '{$date}' order by `fsr_id` desc limit 1");
                    $br_key = $db->getItemFromDB("SELECT br_key FROM finascop_branch WHERE br_ID={$fsr['fsr_source']}");
                    $uid_max = getNewSRNumber($fsr['fsr_source']);
                    $fsr['fsr_uid'] = $uid_max;
                    $status = $db->perform('finascop_stock_request', $fsr);
                    $lastId = $db->insert_id();

                    $orderDeatils = $db->getMultipleData("SELECT item_product_id,item_order_qty FROM retaline_customer_order_items WHERE customer_order_id = {$holdOrder['order_id']}", true);
                    foreach ($orderDeatils as $orderDeatil) {
                        $fsrd['fsrd_status'] = 1;
                        $fsrd['fsr_id'] = $lastId;
                        $fsrd['fsrd_createdOn'] = date("Y-m-d H:i");
                        $fsrd['fsrd_createdBy'] = 1;

                        $fsrd['fsr_ItemId'] = $orderDeatil['item_product_id'];
                        $fsrd['fsr_RequiredItemQty'] = $orderDeatil['item_order_qty'];
                        $fsrd['fsr_ApprovedItemQty'] = $orderDeatil['item_order_qty'];
                        $mrp = $db->getItemFromDB("SELECT fpod_leastSKUmrp FROM finascop_stock_branch_inventory WHERE stit_ID = {$orderDeatil['item_product_id']} AND branch_id = {$brachDetails['br_typeParent']} ");
                        if ($mrp > 0) {
                            $fsrd['fsr_ItemMRP'] = $mrp;
                        } else {
                            $fsrd['fsr_ItemMRP'] = 0;
                        }


                        $fsrd['fsr_ItemUnits'] = $db->getItemFromDB("SELECT least_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$orderDeatil['item_product_id']} ");
                        $fsrd['fsr_leastSKUCount'] = $orderDeatil['item_order_qty'];
                        $fsrd['least_package_type_id'] = $db->getItemFromDB("SELECT least_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$orderDeatil['item_product_id']} ");
                        $status = $db->perform('finascop_stock_request_details', $fsrd);
                    }
                    $data['fsr_id'] = $lastId;
                    $data['order_id'] = $holdOrder['order_id'];
                    $data['createdOn'] = date("Y-m-d H:i:s");
                    $status = $db->perform('order_request_log', $data);

                    if ($status = 1) {
                        $rcodata['updated_at'] = $now;
                        $rcodata['status_id'] = 41;
                        $status = $db->perform('retaline_customer_order', $rcodata, 'update', " status_id = 40 AND order_id = {$holdOrder['order_id']}");
                    }
                }
            }
        }
    }

    public function holdOrdersAvaialbale() {
        $db = new sqlDb(DSN);
        $now = date('Y-m-d H:i:s');
        $data['tpopredet_uniqueid'] = getRandomRef();
        $countHoldOrders = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer_order WHERE status_id = 27  AND order_cutoff_time <= '{$now}'");
        if ($countHoldOrders > 0) {
            $db->query("set group_concat_max_len=95000;");
            $holdOrders = $db->getMultipleData("SELECT order_id,order_order_id,updated_at,order_branch_id FROM retaline_customer_order WHERE status_id = 27 AND order_cutoff_time <= '{$now}'", true);
            $holdOrderIds = $db->getItemFromDB("SELECT GROUP_CONCAT(order_id) FROM retaline_customer_order WHERE status_id = 27 AND order_cutoff_time <= '{$now}'", true);
            //print_r($holdOrders);
//            print_r($holdOrderIds);
            foreach ($holdOrders as $holdOrder) {
                $orderDeatils = $db->getMultipleData("SELECT item_product_id,item_order_qty FROM retaline_customer_order_items WHERE customer_order_id = {$holdOrder['order_id']}", true);
                foreach ($orderDeatils as $orderDeatil) {

                    $fcpod_vendorid = $db->getItemFromDB("SELECT fcpod_vendorid FROM finascop_contractpo_products WHERE fcpod_itemid = {$orderDeatil['item_product_id']}");
                    $asctedbrach_cpr = $db->getItemFromDB("SELECT asctedbrach_cpr FROM finascop_stock_party WHERE stpa_id = {$fcpod_vendorid} AND deliverMode_cpr = 2");
//                    print_r($asctedbrach_cpr);
                    if ($asctedbrach_cpr > 0) {
                        //tmp_poprereq_detail
                        $data['tpopredet_orderBranch'] = $holdOrder['order_branch_id'];
                        $data['tpopredet_vendorid'] = $fcpod_vendorid;
                        $data['tpopredet_itemid'] = $orderDeatil['item_product_id'];
                        $tpopredet_orderIds = $db->getItemFromDB("SELECT tpopredet_orderIds FROM tmp_poprereq_details WHERE tpopredet_uniqueid = '{$data['tpopredet_uniqueid']}' AND tpopredet_itemid = {$data['tpopredet_itemid']}");
                        $item_order_qty = $db->getItemFromDB("SELECT tpopredet_itemqty FROM tmp_poprereq_details WHERE tpopredet_uniqueid = '{$data['tpopredet_uniqueid']}' AND tpopredet_itemid = {$data['tpopredet_itemid']} AND tpopredet_orderBranch = {$holdOrder['order_branch_id']}");
                        if (empty($tpopredet_orderIds)) {
                            $data['tpopredet_orderIds'] = $holdOrder['order_id'];
                        } else {
                            $data['tpopredet_orderIds'] = $tpopredet_orderIds . ',' . $holdOrder['order_id'];
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
                    $status = $db->perform('retaline_customer_order', $rcodata, 'update', " status_id = 27 AND order_id = {$holdOrder['order_id']}");
                }
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
        }
        echo '............' . $now;
        if ($holdOrderIds != '') {
            $dataRco['updated_at'] = $now;
            $dataRco['status_id'] = 30;
            $status = $db->perform('retaline_customer_order', $dataRco, 'update', " status_id = 29 AND order_id IN ({$holdOrderIds})");
        }
    }

    public function inwardOrdersAvaialbale() {
        $db = new sqlDb(DSN);
        $countInwardOrders = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer_order WHERE status_id IN(33,34,45) ");
        if ($countInwardOrders > 0) {
            $inwardOrders = $db->getMultipleData("SELECT order_id,order_order_id,updated_at,order_branch_id FROM retaline_customer_order WHERE status_id IN(33,34,45)", true);
            foreach ($inwardOrders as $inwardOrder) {
                $cfg_Value = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'ORDERSUCCESS'");
                $url = str_replace('{orderId}', $inwardOrder['order_id'], $cfg_Value);

                $opts = array(
                    CURLOPT_URL => $url,
                    CURLINFO_CONTENT_TYPE => "application/json",
                    CURLOPT_BINARYTRANSFER => TRUE,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_HTTPHEADER => array('Content-Type: application/json')
                );

                $ch = curl_init();
                curl_setopt_array($ch, $opts);
                $data = curl_exec($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);
            }
        }
    }

    public function autoTransferDarkOrders() {
        $db = new sqlDb(DSN);
        $satelliteBranches = $db->getMultipleData("SELECT br_ID,br_Name,br_typeParent,br_parentPacking FROM finascop_branch WHERE br_type = 1 AND br_parentPacking = 1", true);
        if ($satelliteBranches[0]['br_ID'] > 0) {
            foreach ($satelliteBranches as $satelliteBranch) {
                $satelliteOrders = $db->getMultipleData("SELECT * FROM finascop_stock_request WHERE fsr_source = {$satelliteBranch['br_ID']} AND fsr_type = 0 AND fsrs_status =  1", true);
                if ($satelliteOrders[0]['fsr_id'] > 0) {
                    foreach ($satelliteOrders as $satelliteOrder) {
                        $date = date('Y-m-d H:i:s');
                        $data['fstr_destination'] = $satelliteOrder['fsr_source'];
                        $data['fstr_source'] = $satelliteOrder['fsr_destination'];
                        $data['fstr_type'] = 2;
                        $tdy = date("Y-m-d") . " 00:00:00";
                        $maxId = $db->getItemFromDB("select right(fstr_uid,3)*1 as fstr_uid  from `finascop_stock_transfer_request` where `fstr_source` = {$satelliteOrder['fsr_destination']} and `fstr_createdOn` between '{$tdy}' and '{$date}' order by `fstr_id` desc limit 1");
                        $br_key = $db->getItemFromDB("SELECT br_key FROM finascop_branch WHERE br_ID={$satelliteOrder['fsr_destination']}");
                        $uid_max = 'TRQ' . date('ymd') . $br_key . str_pad(($maxId + 1), 3, '0', STR_PAD_LEFT);
                        $data['fstr_uid'] = $uid_max;
                        $data['fstr_createdOn'] = $date;
                        $data['fstr_initiatedBy'] = 0;
                        $data['fstr_updatedOn'] = $date;
                        $data['fstr_updatedBy'] = 0;
                        $data['fstr_createdBy'] = 0;
                        $data['fstr_status'] = 1;

                        $status = $db->perform('finascop_stock_transfer_request', $data);
                        $lastId = $db->insert_id();

                        if ($lastId) {
                            $fsrItemDetails = $db->getMultipleData("SELECT * FROM finascop_stock_request_details WHERE fsr_id ={$satelliteOrder['fsr_id']} AND fsrd_status = 1", true);
                            foreach ($fsrItemDetails as $fsrItem) {
                                $data_details['fstr_ItemId'] = $fsrItem['fsr_ItemId'];
                                $data_details['fstr_RequiredItemQty'] = $fsrItem['fsr_ApprovedItemQty'];
                                $data_details['fstr_ApprovedItemQty'] = $fsrItem['fsr_ApprovedItemQty'];
                                $data_details['fstr_id'] = $lastId;
                                $data_details['fstrd_status'] = 1;

                                $data_details['uuid'] = $uid_max;
                                $data_details['fstrd_createdBy'] = 0;
                                $data_details['fstrd_createdOn'] = $date;
                                $data_details['fstr_ItemUnits'] = $fsrItem['fsr_ItemUnits'];
                                $data_details['fstr_leastSKUCount'] = $fsrItem['fsr_leastSKUCount'];
                                $data_details['least_package_type_id'] = $fsrItem['least_package_type_id'];


                                $itemPriceDetails = $db->getFromDB("SELECT fpod_leastSKUmrp,fpod_leastSKUepr FROM finascop_stock_branch_inventory WHERE stit_id = {$fsrItem['fsr_ItemId']} AND branch_id = {$satelliteOrder['fsr_destination']} limit 1", true);
                                if (count($itemPriceDetails) > 0) {
                                    $data_details['fstr_ItemMRP'] = $itemPriceDetails['fpod_leastSKUmrp'];
}
                                $data_details = array_filter($data_details, 'strlen');

                                $status = $db->perform('finascop_stock_transfer_request_details', $data_details);

                                if ($status) {
                                    $datas = array(
                                        'fsrd_status' => 2
                                    );
                                    $status = $db->perform('finascop_stock_request_details', $datas, 'update', 'fsrd_id=' . $fsrItem['fsrd_id']);
                                    $total_count = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_request_details WHERE fsr_id={$satelliteOrder['fsr_id']}");
                                    $status_requested_count = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_request_details WHERE fsr_id={$satelliteOrder['fsr_id']} AND fsrd_status=1");
                                    if ($status_requested_count == 0) {
                                        $datasr = array(
                                            'fstr_id' => $lastId,
                                            'fsrs_status' => 10
                                        );
                                        $status = $db->perform('finascop_stock_request', $datasr, 'update', 'fsr_id=' . $satelliteOrder['fsr_id']);
                                    } else if ($total_count > $status_requested_count) {
                                        $datasru = array(
                                            'fstr_id' => $lastId,
                                            'fsrs_status' => 5
                                        );
                                        $qry = $db->perform('finascop_stock_request', $datasru, 'update', 'fsr_id=' . $satelliteOrder['fsr_id']);
                                    }
                                }
                            }
                        }
                        $orderLogs = $db->getMultipleData("SELECT * FROM order_request_log WHERE fsr_id = {$satelliteOrder['fsr_id']}", true);
                        if ($orderLogs[0]['order_id'] > 0) {
                            foreach ($orderLogs as $orderLog) {
                                $rcodata['updated_at'] = date('Y-m-d H:i:s');
                                $rcodata['status_id'] = 42;
                                $status = $db->perform('retaline_customer_order', $rcodata, 'update', " status_id = 41 AND order_id = {$orderLog['order_id']}");
                            }
                        }
                    }
                }
            }
        }
    }

}
