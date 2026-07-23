<?php

class OrderPOProcessorSch {

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
        $countInwardOrders = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer_order WHERE status_id IN(33,34) ");
        if ($countInwardOrders > 0) {
            $inwardOrders = $db->getMultipleData("SELECT order_id,order_order_id,updated_at,order_branch_id FROM retaline_customer_order WHERE status_id IN(33,34)", true);
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

}
