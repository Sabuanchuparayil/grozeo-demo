<?php

switch ($op) {

    case 'getPOdetailsData':
        $data = $_POST['POname'];
        $qry = "SELECT stpa_id AS customerId,stpa_Fname, stpa_Lname, stpa_GSTIN, stpa_Address ,stpa_City AS stpa_City,stpa_PINCODE AS stpa_PINCODE,
(SELECT st_name FROM finascop_state b INNER JOIN finascop_district d ON b.st_ID = d.st_Id WHERE d.dst_Id = a.dst_Id)AS st_name,
(SELECT b.st_ID FROM finascop_state b INNER JOIN finascop_district d ON b.st_ID = d.st_Id WHERE d.dst_Id = a.dst_Id)AS st_id,
(SELECT c.dst_Id FROM finascop_district c WHERE c.dst_Id = a.dst_Id )AS dst_Id,
(SELECT dst_Name FROM finascop_district c WHERE c.dst_Id = a.dst_Id )AS dst_Name
  FROM finascop_stock_party a      WHERE UPPER(stpa_Fname) LIKE UPPER('%$data%')";
        $countDataQuery = "SELECT count(*) from finascop_stock_party where UPPER(stpa_Fname) LIKE UPPER('%$data%')";
        $count = $db->getItemFromDB($countDataQuery);
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'poVendorItemsGridStore':

        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 12;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['po_id', 'po_number', 'po_date', 'po_total'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'po_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'stit_ID' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;

        $filter_qry = " AND 1 = 1 ";
        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }
        $vendorId = $_POST['vendorId'];

        $uniqueId = '';
        while ($uniqueId == '') {
            $uniqueId = getNewFinascopApiKey();
        }
        if ($vendorId > 0) {
            $curBranchLevel = $db->getItemFromDB("SELECT br_stockLevel FROM finascop_branch WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");
            switch ($curBranchLevel) {
                case 1:
                    $stockFields = 'stitl1_optimumqty as optiQty,stit11_minimumqty as minQty';
                    break;
                case 2:
                    $stockFields = 'stitl2_optimumqty as optiQty,stit12_minimumqty as minQty';
                    break;
                case 3:
                    $stockFields = 'stitl3_optimumqty as optiQty,stit13_minimumqty as minQty';
                    break;
            }
            $qry = "SELECT m.stit_ID,stit_itemName,stit_SKU,stit_brand_name,stit_category_name,stit_product_variant,stit_quantity,"
                    . "(SELECT fpod_effectiverate from finascop_purchase_order_details where fpod_itemid = m.stit_ID ORDER BY fpod_id desc limit 1) as last_mrp,"
                    . "(SELECT min(fpod_effectiverate) FROM finascop_purchase_order_details where fpod_itemid = m.stit_ID) as last_sp,stit_HSN_code,stit_GST,"
                    . "'{$uniqueId}' as uniqueId,{$stockFields} FROM finascop_stock_itemmaster m "
                    . "INNER JOIN finascop_stock_party_items s  ON s.stit_id = m.stit_ID WHERE s.stpa_id='{$vendorId}' {$filter_qry}";
            $data = $db->getMultipleData($qry, true);

            $countQuery = "SELECT COUNT(*) FROM finascop_stock_itemmaster m "
                    . "INNER JOIN finascop_stock_party_items s  ON s.stit_id = m.stit_ID WHERE s.stpa_id='{$vendorId}' {$filter_qry}";
            $count = $db->getItemFromDB($countQuery);


            if (!empty($data)) {
                echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
            } else {
                echo '{"totalCount":"0","data":[]}';
            }
        }
        break;
    case 'saveItemPODetails':
//        print_r($_POST);
//       exit();

        $isStandardPacking = $_POST['isStandardPacking'];
        if(!empty($_POST['poadhoc_updatedon'])){
            $adhocCount = $db->getItemSafe("SELECT COUNT(*) FROM finascop_purchase_order_poadhoc WHERE adhoc_uniqueid = ? AND adhoc_updatedon = '{$_POST['poadhoc_updatedon']}'", "i", [$_POST['fpot_uniqueid']]);
                if ($_POST['poadhoc_updatedon'] != '' && $adhocCount == 0) {
                    echo '{"success":false,"msg":"Reload data updation is going on."}';
                    exit();
                }
        }
        $taxRate = $db->getItemSafe("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = ?", "i", [$_POST['fpot_itemid']]);
        if ($_POST['po_gstType'] == 'Inclusive') {
            $fpot_itemoffrrate = $_POST['fpot_itemoffrrate'];
            $fpot_itemoffrrateet = (floatval($fpot_itemoffrrate) * 100) / (100 + floatval($taxRate));
        } else {
            $fpot_itemoffrrate = $_POST['fpot_itemoffrrate'] + ($_POST['fpot_itemoffrrate'] * floatval($taxRate) / 100);
            $fpot_itemoffrrateet = $_POST['fpot_itemoffrrate'];
        }
        $itemmrp = $_POST['fpot_itemmrp'];
        if ($fpot_itemoffrrate > $itemmrp) {
            echo '{"valid":false,"msg":"Offer Rate is not valid"}';
            exit();
        }
        $db->query('begin');
        if ($isStandardPacking == 1) {
            $data['isStandardPacking'] = 1;
        } else {
            $data['isStandardPacking'] = 0;
        }
        if ($adhocCount > 0) {
            $updatedDate = $db->getItemSafe("SELECT adhoc_updatedon FROM finascop_purchase_order_poadhoc WHERE adhoc_uniqueid = ?", "s", [$_POST['fpot_uniqueid']]);
            if ($updatedDate == $_POST['poadhoc_updatedon']) {
                $adhocData['adhoc_updatedon'] = date("Y-m-d H:i");
                $adhocData['adhoc_updatedby'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('finascop_purchase_order_poadhoc', $adhocData, 'update', " adhoc_uniqueid = '{$_POST['fpot_uniqueid']}'");
            } else {
                echo '{"success":false,"msg":"Reload data updation is going on."}';
                exit();
            }
        } else {

            $adhocData['adhoc_name'] = 'PPO' . time();
            $adhocData['adhoc_uniqueid'] = $_POST['fpot_uniqueid'];
            $adhocData['adhoc_vendor'] = $_POST['fpot_vendorid'];
            $adhocData['adhoc_createdon'] = date("Y-m-d H:i");
            $adhocData['adhoc_createdby'] = $_SESSION['admin']->Finascop_UserId;
            $adhocData['adhoc_updatedon'] = date("Y-m-d H:i");
            $adhocData['adhoc_updatedby'] = $_SESSION['admin']->Finascop_UserId;
            $adhocData['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
            if ($_POST['adhoc_poValue'] > 0)
                $adhocData['adhoc_poValue'] = $_POST['adhoc_poValue'];
            $adhocData['adhoc_paymentTerms'] = $_POST['adhoc_paymentTerms'];
            if ($_POST['adhoc_paymentValue'] > 0)
                $adhocData['adhoc_paymentValue'] = $_POST['adhoc_paymentValue'];
            $adhocData['adhoc_validityType'] = $_POST['adhoc_validityType'];
            if ($_POST['adhoc_shippingcharge'] > 0)
                $adhocData['adhoc_shippingcharge'] = $_POST['adhoc_shippingcharge'];
            if ($_POST['adhoc_gdiscount'] > 0)
                $adhocData['adhoc_gdiscount'] = $_POST['adhoc_gdiscount'];
            $adhocData['adhoc_gdiscounttype'] = $_POST['adhoc_gdiscounttype'];
            $status = $db->perform('finascop_purchase_order_poadhoc', $adhocData);
        }


        $data['fpot_adhocname'] = $_POST['fpot_adhocname'];
        $data['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
        $data['fpot_uniqueid'] = $_POST['fpot_uniqueid'];
        $data['fpot_vendorid'] = $_POST['fpot_vendorid'];
        $data['fpot_itemid'] = $_POST['fpot_itemid'];
        $data['fpot_itemname'] = $_POST['fpot_itemname'];
        $data['fpot_itemmrp'] = $_POST['fpot_itemmrp'];
        $data['fpot_itemqty'] = $_POST['fpot_itemqty'];
        $data['fpot_itemoffrqty'] = $_POST['fpot_itemoffrqty'];
        $data['fpot_itemaddidisc'] = $_POST['fpot_itemaddidisc'];
        $taxRate = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$data['fpot_itemid']}");
        if ($_POST['po_gstType'] == 'Inclusive') {
            $fpot_itemoffrrate = $_POST['fpot_itemoffrrate'];
            $fpot_itemoffrrateet = (floatval($fpot_itemoffrrate) * 100) / (100 + floatval($taxRate));
        } else {
            $fpot_itemoffrrate = $_POST['fpot_itemoffrrate'] + ($_POST['fpot_itemoffrrate'] * floatval($taxRate) / 100);
            $fpot_itemoffrrateet = $_POST['fpot_itemoffrrate'];
        }
        $data['fpot_itemoffrrate'] = round($fpot_itemoffrrate, 2);
        $data['fpot_itemoffrrateet'] = round($fpot_itemoffrrateet, 2);
        $data['fpot_amount'] = $_POST['fpot_amount'];
        $data['fpot_netamount'] = $_POST['fpot_netamount'];

        $data['fpot_initialnetamount'] = $_POST['fpot_netamount'];
        $data['fpot_idiscountcalculs'] = $_POST['fpot_idiscountcalculs'];
        $data['fpot_createdon'] = date("Y-m-d H:i:s");
        $data['fpot_createdby'] = $_SESSION['admin']->Finascop_UserId;
        $data['fpot_totalqty'] = (int) $data['fpot_itemoffrqty'] + (int) $data['fpot_itemqty'];
        $data['fpot_balanceqty'] = $data['fpot_totalqty'];



        $data['fpot_pogstType'] = $_POST['po_gstType'];
        //var fpot_itemoffrrateet = (parseFloat(fpot_itemoffrrate) * 100) / (100 + parseFloat(taxRate));
        //$data['fpot_pogstAmt'] = $_POST['fpot_netamount'] * $taxRate / 100;
        $fpot_netamountet = (floatval($_POST['fpot_netamount']) * 100) / (100 + floatval($taxRate));
        $data['fpot_netamountet'] = round($fpot_netamountet, 2);

        //changed on 08/12/21


        $fpot_effectiverate = $data['fpot_netamountet'] / $data['fpot_totalqty'];
        $data['fpot_effectiverate'] = round($fpot_effectiverate, 2);

        $data['fpot_pogstAmt'] = $data['fpot_netamount'] - $data['fpot_netamountet'];
        $data['fpot_netamountTotal'] = $data['fpot_netamount'];

        $fpot_itemoffrratech = $_POST['fpot_netamount'] / $_POST['fpot_itemqty'];
        $data['fpot_itemoffrratech'] = round($fpot_itemoffrratech, 2);
        $fpot_itemoffrrateetch = ($data['fpot_itemoffrratech'] * 100) / (100 + $taxRate);
        $data['fpot_itemoffrrateetch'] = round($fpot_itemoffrrateetch, 2);

        $data['fpot_giftqty'] = $_POST['fpot_giftqty'];
        $data['fpot_giftname'] = $_POST['fpot_giftname'];
        $data['fpot_notes'] = $_POST['fpot_notes'];

        $data['fpot_purchasingUnit'] = $_POST['itemUnitForm'];

//for margin distributions
        (float) $eprbft = ((float) $data['fpot_effectiverate'] / (100 + (float) $taxRate)) * 100;
        (float) $mrpbft = ((float) $data['fpot_itemmrp'] / (100 + (float) $taxRate)) * 100;
        $actmarginDistriPercent = 100 - (($eprbft / $mrpbft) * 100);
        $marginDistriPercent = round($actmarginDistriPercent);
        $data['actual_marginDistri'] = round($actmarginDistriPercent, 2);
        $data['bmd_percent'] = $marginDistriPercent;

        $data['fpot_itempts'] = $_POST['fpot_itempts'];
        $data['fpot_itemptr'] = $_POST['fpot_itemptr'];



        $fpot_effectiverategst = $data['fpot_pogstAmt'] / $data['fpot_totalqty'];
        $data['fpot_effectiverategst'] = round($fpot_effectiverategst, 2);

        $data['fpot_poLandingCost'] = $data['fpot_effectiverate'] + $data['fpot_effectiverategst'];
        $data['fpot_poMMG'] = $data['fpot_itemmrp'] - $data['fpot_poLandingCost'];

        $qry = "SELECT stdpckl11_package_type_id,stdpckl21_package_type_id,stdpckl2_nos,stdpckl31_package_type_id,stdpckl3_nos,stdpckl41_package_type_id,stdpckl4_nos,stit_GST,csb_package_type_name,cs_nos,stdpckl1_nos,"
                . "cs_package_type_name,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,ccs_package_type_id,ccs_package_type_name,rs_package_type_id,rs_package_type_name FROM finascop_stock_itemmaster m WHERE m.stit_ID ='{$_POST['fpot_itemid']}' ";
        $itemHistory = $db->getFromDB($qry, true);
        //print_r($itemHistory);
        //$data['fpot_leastSKUqty'] = $itemHistory['cs_nos'] * $itemHistory['ds_nos'] * $itemHistory['cos_nos'];
        if ($data['fpot_purchasingUnit'] == $itemHistory['stdpckl12_package_type_id']) {
            $level = '4';
            $fpot_leastSKUqty = 1 / $itemHistory['stdpckl1_nos'];
        } else if ($data['fpot_purchasingUnit'] == $itemHistory['stdpckl11_package_type_id']) {
            $level = '3';
            $fpot_leastSKUqty = 1;
        } else if ($data['fpot_purchasingUnit'] == $itemHistory['stdpckl21_package_type_id']) {
            $level = '2';
            $fpot_leastSKUqty = 1 * $itemHistory['stdpckl2_nos'];
        } else if ($data['fpot_purchasingUnit'] == $itemHistory['stdpckl31_package_type_id']) {
            $level = '1';
            $fpot_leastSKUqty = $itemHistory['stdpckl2_nos'] * $itemHistory['stdpckl3_nos'];
        }
        $fpod_leastSKUTotalqty = $data['fpot_totalqty'] * $fpot_leastSKUqty;
        $fpod_leastSKUepr = $data['fpot_effectiverate'] / $fpot_leastSKUqty;
        $fpod_leastSKUmrp = $data['fpot_itemmrp'] / $fpot_leastSKUqty;
        $fpot_leastSKUoffrrateet = $data['fpot_itemoffrrateet'] / $fpot_leastSKUqty;

        //echo '$fpot_leastSKUqty'.$fpot_leastSKUqty;
        //exit();
        $data['fpot_leastSKUepr'] = round(($fpod_leastSKUepr * 100) / (100 + $itemHistory['stit_GST']), 2);
        //$data['fpot_leastSKUmrp'] = round(($fpod_leastSKUmrp * 100) / (100 + $itemHistory['stit_GST']), 2);
        $data['fpot_leastSKUmrp'] = round($fpod_leastSKUmrp, 2);
        $data['fpot_leastSKUoffrrateet'] = $fpot_leastSKUoffrrateet;
        $data['fpot_leastSKUqty'] = $fpot_leastSKUqty;
        $data['fpot_leastSKUTotalqty'] = $fpod_leastSKUTotalqty;
        $data['fpot_leastSKUbalanceqty'] = $fpod_leastSKUTotalqty;
        $data['fpot_pyramidLevel'] = $level;
        $data['fpot_isRRP'] = $_POST['fpot_isRRP'];
        //$marginDistriPercent = ceil(($quantityVerification['fpod_effectiverate'] / $quantityVerification['fpod_itemmrp']) * 100);
        //$bmd_id = $db->getItemFromDB("SELECT bmd_id FROM retaline_margindistributions WHERE is_default = 1");
        //$data['bmdd_id'] = $db->getItemFromDB("SELECT bmdd_id FROM retaline_margindistributionsDetails WHERE bmd_id = {$bmd_id} AND bmd_percent = {$marginDistriPercent}");
        // = $db->getFromDB("SELECT * FROM  retaline_margindistributionsDetails WHERE bmd_id = {$bmd_id} AND bmd_percent = {$marginDistriPercent}", true);
        //print_r($bmdDetails);
        $bmdDetails = $db->getFromDB("SELECT * FROM retaline_margindistributions WHERE is_default = 1", true);
//        $data['bmd_company'] = $bmdDetails['bmd_company'];
//        $data['bmd_hub'] = $bmdDetails['bmd_hub'];
//        $data['bmd_incentive'] = $bmdDetails['bmd_incentive'];
//        $data['bmd_technology'] = $bmdDetails['bmd_technology'];
//        $data['bmd_customer'] = $bmdDetails['bmd_customer'];
        $data['bmd_id'] = $bmdDetails['bmd_id'];
        $bmdDetailsb2b = $db->getFromDB("SELECT * FROM retaline_margindistributionsb2b WHERE is_default = 1", true);
        $data['b2bbmd_id'] = $bmdDetailsb2b['bmd_id'];
        //print_r($data);
        //exit();
        $db->query('begin');
        $dup = $db->getItemFromDB("SELECT COUNT(1) FROM finascop_purchase_order_temp WHERE fpot_uniqueid = '{$data['fpot_uniqueid']}' and fpot_itemid = {$data['fpot_itemid']}");

        if ($dup > 0) {
            $con = "fpot_uniqueid = '{$data['fpot_uniqueid']}' and fpot_itemid = {$data['fpot_itemid']}";
            $status = $db->perform('finascop_purchase_order_temp', $data, 'update', $con);
        } else {
            $status = $db->perform('finascop_purchase_order_temp', $data);
        }
        $newupdatedDate = $db->getItemSafe("SELECT adhoc_updatedon FROM finascop_purchase_order_poadhoc WHERE adhoc_uniqueid = ?", "s", [$_POST['fpot_uniqueid']]);
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Item PO details saved successfully.'";
            echo '{"success":true,"date":"' . $newupdatedDate . '","msg":' . $msg . '}';
        } else {
            $msg = "'Error while saving invoice.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'savePO':
        // PO is for a CPD. Purchase and CPD module is only for CPD users
        $data = $_POST;
        $podata['fpo_vendorId'] = $data['customerId'];
        $podata['fpo_vendorName'] = $data['customerName'];
        $podata['fpo_poNumber'] = getNewPONumber($data['po_billing_to']);
        $podata['fpo_poDate'] = date('Y-m-d');
        $podata['fpo_poOrderedby'] = $_SESSION['admin']->Finascop_UserId;
        $podata['fpo_paymentTerms'] = $data['po_payment_terms'];
        $podata['fpo_paymentValue'] = $data['po_payment_value'];
        $podata['fpo_validityType'] = $data['fpo_validityType'];
        $podata['fpo_validDate'] = date('Y-m-d', strtotime($podata['fpo_poDate'] . " + {$data['fpo_validityType']} days"));
        $podata['fpo_poValue'] = $data['poValue'];
        $podata['fpo_createdon'] = date("Y-m-d H:i:s");
        $podata['fpo_poValueDiff'] = $data['fpo_poValueDiff'];
        $podata['fpo_poFinalValue'] = $data['fpo_poFinalValue'];
        $fpo_potype = $db->getItemFromDB("SELECT adhoc_potype FROM finascop_purchase_order_poadhoc WHERE adhoc_uniqueid = '{$data['uid']}'");
        $poOrders = array();
        if ($fpo_potype > 0) {
            $podata['fpo_potype'] = $fpo_potype;
        }
        //$fpot_gendiscounttot = $db->getMultipleData("SELECT sum(fpot_gendiscount) FROM finascop_purchase_order_temp WHERE fpot_uniqueid = '{$uniqueId}' ORDER BY fpot_createdon ASC", true);
        switch ($data['po_gdiscountcalculs']) {
            case 'Amount':
                $gdispercent = floatval($data['fpot_generalDiscount']) * 100 / $data['total_initialofferamount'];
                break;
            case 'Percentage':
                $gdispercent = $data['fpot_generalDiscount'];
                break;
        }
        if ($gdispercent > 0) {
            $podata['fpo_gdiscpercent'] = $gdispercent;
        }
        $podata['fpo_shippingcharge'] = 0 + $data['fpot_shippingcharge'];
        $podata['fpo_centralStore'] = $data['po_billing_to'];
        //CPD id for PO 
        $podata['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
//        $podata['fpo_poDeliveryType'] = $data['po_delivery_datetype'];
//        $podata['fpo_poDeliveryDate'] = date('Y-m-d', strtotime($data['po_delivery_date']));
        //print_r($data);
        $uniqueId = $data['uid'];
        $db->query('begin');
        if (($data['fpot_shippingcharge'] == 0) && ($gdispercent == 0)) {
            $fpotDeatils = $db->getMultipleData("SELECT fpot_itemid,fpot_netamount,fpot_amount,fpot_initialnetamount,fpot_totalqty,fpot_effectiverate,fpot_itemoffrrateet,fpot_idiscountcalculs,fpot_itemaddidisc,"
                    . "fpot_itemqty,fpot_netamountet,fpot_pogstType,fpot_itemoffrrate FROM finascop_purchase_order_temp where fpot_uniqueid = '{$uniqueId}' ", true);

            $handlinchgGstVal = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'HANDLING_CHRG_GST'");
            $handlinchgGst = $data['fpot_shippingcharge'] - (($data['fpot_shippingcharge'] * 100) / (100 + $handlinchgGstVal ));
            $handlinchgGst = round($handlinchgGst, 2);
            $totalnetAmount = $db->getItemFromDB("SELECT SUM(fpot_netamount) FROM finascop_purchase_order_temp where fpot_uniqueid = '{$uniqueId}' ");
            //print_r($fpotDeatils);
            foreach ($fpotDeatils as $fpotDeatil) {
                $taxRate = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$fpotDeatil['fpot_itemid']}");

                $amount = $fpotDeatil['fpot_itemoffrrate'] * $fpotDeatil['fpot_itemqty'];
                if ($fpotDeatil['fpot_idiscountcalculs'] == 'Amount') {
                    $fpot['fpot_itemaddidiscamt'] = $fpotDeatil['fpot_itemaddidisc'];
                    $amountAftrItemDis = $amount - $fpot['fpot_itemaddidiscamt'];
                } else {
                    $fpot_itemaddidiscamt = $amount * $fpotDeatil['fpot_itemaddidisc'] / 100;
                    $fpot['fpot_itemaddidiscamt'] = round($fpot_itemaddidiscamt, 2);
                    $amountAftrItemDis = $amount - $fpot['fpot_itemaddidiscamt'];
                }
                $generalDiscount = round($amountAftrItemDis * $gdispercent / 100, 2);
                $finatlnetAmt = round(($amountAftrItemDis - $generalDiscount), 2);
                $fpot['fpot_gendiscount'] = $generalDiscount;
                $fpot['fpot_netamount'] = $finatlnetAmt;
                $fpot_netamountet = ($finatlnetAmt * 100) / (100 + $taxRate);
                $fpot['fpot_netamountet'] = round($fpot_netamountet, 2);
                $fpot['fpot_pogstAmt'] = $fpot['fpot_netamount'] - $fpot['fpot_netamountet'];

                $fpot_itemoffrratech = $fpot['fpot_netamount'] / $fpotDeatil['fpot_itemqty'];
                $fpot['fpot_itemoffrratech'] = round($fpot_itemoffrratech, 2);
                $fpot_itemoffrrateetch = ($fpot['fpot_itemoffrratech'] * 100) / (100 + $taxRate);
                $fpot['fpot_itemoffrrateetch'] = round($fpot_itemoffrrateetch, 2);
                //print_r($fpot);
                $con = "fpot_uniqueid = '{$uniqueId}' and fpot_itemid = {$fpotDeatil['fpot_itemid']}";
                $status = $db->perform('finascop_purchase_order_temp', $fpot, 'update', $con);
            }

            $fpot_netamountetTotal = $db->getItemFromDB("SELECT SUM(fpot_netamountTotal) FROM finascop_purchase_order_temp where fpot_uniqueid = '{$uniqueId}'");
            $fpotDeatilsdup = $db->getMultipleData("SELECT fpot_itemmrp,fpot_itemid,fpot_netamount,fpot_amount,fpot_initialnetamount,fpot_totalqty,fpot_effectiverate,fpot_itemoffrrateet,fpot_idiscountcalculs,fpot_itemaddidisc,"
                    . "fpot_itemqty,fpot_netamountet,fpot_netamountTotal,fpot_pogstAmt FROM finascop_purchase_order_temp where fpot_uniqueid = '{$uniqueId}' ", true);
            //print_r($fpotDeatilsdup);
            foreach ($fpotDeatilsdup as $fpotDeatil2) {
                $fpot_shippingcharge = $data['fpot_shippingcharge'] * $fpotDeatil2['fpot_netamountTotal'] / $fpot_netamountetTotal;
                $fpot2['fpot_shippingcharge'] = round($fpot_shippingcharge, 2);
                $fpot_shippingchargegst = ($fpot2['fpot_shippingcharge'] * 100) / (100 + $handlinchgGstVal);
                $fpot2['fpot_shippingchargegst'] = round($fpot_shippingchargegst, 2);

                $fpot2['fpot_netamountet'] = $fpotDeatil2['fpot_netamountet'] + $fpot2['fpot_shippingcharge'] - $fpot2['fpot_shippingchargegst'];
                $fpot2['fpot_pogstAmt'] = $fpotDeatil2['fpot_pogstAmt'] + $fpot2['fpot_shippingchargegst'];

                $fpot_effectiverate = $fpot2['fpot_netamountet'] / $fpotDeatil2['fpot_totalqty'];
                $fpot2['fpot_effectiverate'] = round($fpot_effectiverate, 2);

                $fpot_effectiverategst = $fpot2['fpot_pogstAmt'] / $fpotDeatil2['fpot_totalqty'];
                $fpot2['fpot_effectiverategst'] = round($fpot_effectiverategst, 2);

                $fpot2['fpot_poLandingCost'] = $fpot2['fpot_effectiverate'] + $fpot2['fpot_effectiverategst'];
                $fpot2['fpot_poMMG'] = $fpotDeatil2['fpot_itemmrp'] - $fpot2['fpot_poLandingCost'];

                $con = "fpot_uniqueid = '{$uniqueId}' and fpot_itemid = {$fpotDeatil2['fpot_itemid']}";
                //print_r($fpot2);
                $status = $db->perform('finascop_purchase_order_temp', $fpot2, 'update', $con);
            }
        }
        $itemdetails = $db->getMultipleData("SELECT * FROM finascop_purchase_order_temp WHERE fpot_uniqueid = '{$uniqueId}' ORDER BY fpot_createdon ASC", true);
        //print_r($itemdetails);
        $c = sizeof($itemdetails[0]);
        if ($c > 0) {
            $status = $db->perform('finascop_purchase_order', $podata);
            $poId = $db->insert_id();

            $poValue = 0;


            foreach ($itemdetails as $itemdetail) {
                $qry = "SELECT stdpckl11_package_type_id,stdpckl21_package_type_id,stdpckl2_nos,stdpckl31_package_type_id,stdpckl3_nos,stdpckl41_package_type_id,stdpckl4_nos,stit_GST,csb_package_type_name,cs_nos,"
                        . "cs_package_type_name,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name FROM finascop_stock_itemmaster m WHERE m.stit_ID ='{$itemdetail['fpot_itemid']}' ";

                $itemHistory = $db->getFromDB($qry, true);
                //print_r($itemHistory);
                $fpoddata['fpod_itempoqty'] = intval($itemdetail['fpot_itemqty']) + intval($itemdetail['fpot_itemoffrqty']);
                $fpoddata['fpod_itemSmallStockUnit'] = $itemHistory['csb_package_type_name'] . ' contains ' . $itemHistory['cs_nos'] . ' quantity ' . $itemHistory['cs_package_type_name'] . ', ' . $itemHistory['cs_nos'] * $itemHistory['ds_nos'] . ' ' . $itemHistory['ds_package_type_name'] . ', ' . $itemHistory['cs_nos'] * $itemHistory['ds_nos'] * $itemHistory['cos_nos'] . ' ' . $itemHistory['cos_package_type_name'];
                $itemHistory['cs_nos'] = ($itemHistory['cs_nos'] > 0 ) ? $itemHistory['cs_nos'] : 1;
                $itemHistory['ds_nos'] = ($itemHistory['ds_nos'] > 0 ) ? $itemHistory['ds_nos'] : 1;
                $itemHistory['cos_nos'] = ($itemHistory['cos_nos'] > 0 ) ? $itemHistory['cos_nos'] : 1;
                $fpoddata['fpod_leastSKUqty'] = $itemdetail['fpot_leastSKUqty'];
                $fpoddata['fpod_leastSKUTotalqty'] = $itemdetail['fpot_leastSKUTotalqty'];
                $fpoddata['fpod_leastSKUBalanceqty'] = $itemdetail['fpot_leastSKUbalanceqty'];

                $fpoddata['isStandardPacking'] = 1;
                $fpoddata['fpod_itempts'] = $itemdetail['fpot_itempts'];
                $fpoddata['fpod_itemptr'] = $itemdetail['fpot_itemptr'];
                $fpoddata['fpod_itemmrp'] = $itemdetail['fpot_itemmrp'];
                $fpoddata['fpod_effectiverate'] = $itemdetail['fpot_effectiverate'];
                $fpoddata['fpod_poLandingCost'] = $itemdetail['fpot_poLandingCost'];
                $fpoddata['fpod_poMMG'] = $itemdetail['fpot_poMMG'];

                $fpoddata['fpod_isRRP'] = $itemdetail['fpot_isRRP'];





                $fpoddata['fpod_leastSKUepr'] = $itemdetail['fpot_leastSKUepr'];

                if ($fpoddata['fpod_itempts'] > 0) {
                    $fpoddata['fpod_itemleastSKUpts'] = round(($fpoddata['fpod_itempts'] / $itemdetail['fpot_leastSKUqty']), 2);
                }
                if ($fpoddata['fpod_itemptr'] > 0) {
                    $fpoddata['fpod_itemleastSKUptr'] = round(($fpoddata['fpod_itemptr'] / $itemdetail['fpot_leastSKUqty']), 2);
                }
                $fpoddata['fpod_leastSKUmrp'] = $itemdetail['fpot_leastSKUmrp'];
                $fpoddata['fpod_leastSKUmargin'] = round($fpoddata['fpod_leastSKUmrp'] - $fpoddata['fpod_leastSKUepr'], 2);
                $fpoddata['fpod_poLandingCostleastSKU'] = round(($itemdetail['fpot_poLandingCost'] / $itemdetail['fpot_leastSKUqty']), 2);
                $fpoddata['fpod_poMMGleastSKU'] = round(($fpoddata['fpod_poMMG'] / $itemdetail['fpot_leastSKUqty']), 2);


                //18/december/2021



                $fpoddata['fpod_netrate'] = $itemdetail['fpot_netrate'];
                $fpoddata['fpod_shippingchargegst'] = $itemdetail['fpot_shippingchargegst'];
                $fpoddata['fpod_shippingcharge'] = $itemdetail['fpot_shippingcharge'];
                $fpoddata['fpod_netamountet'] = $itemdetail['fpot_netamountet'];
                $fpoddata['fpod_pogstType'] = $itemdetail['fpot_pogstType'];
                $fpoddata['fpod_pogstAmt'] = $itemdetail['fpot_pogstAmt'];
                $fpoddata['fpod_netamountTotal'] = $itemdetail['fpot_netamountTotal'];
                $fpoddata['fpod_fpoId'] = $poId;
                $fpoddata['fpod_vendorid'] = $itemdetail['fpot_vendorid'];
                $fpoddata['fpod_itemid'] = $itemdetail['fpot_itemid'];
                $fpoddata['fpod_itemname'] = $itemdetail['fpot_itemname'];

                $fpoddata['fpod_itemqty'] = $itemdetail['fpot_itemqty'];
                $fpoddata['fpod_itemoffrqty'] = $itemdetail['fpot_itemoffrqty'];

                $fpoddata['fpod_itemoffrrate'] = $itemdetail['fpot_itemoffrrate'];
                $fpoddata['fpod_itemoffrrateet'] = $itemdetail['fpot_itemoffrrateet'];
                $fpoddata['fpod_itemoffrratech'] = $itemdetail['fpot_itemoffrratech'];
                $fpoddata['fpod_itemoffrrateetch'] = $itemdetail['fpot_itemoffrrateetch'];
                $fpoddata['fpod_itemaddidisc'] = $itemdetail['fpot_itemaddidisc'];

                $fpoddata['fpod_giftname'] = $itemdetail['fpot_giftname'];
                $fpoddata['fpod_giftqty'] = $itemdetail['fpot_giftqty'];
                $fpoddata['fpod_notes'] = $itemdetail['fpot_notes'];
                $fpoddata['fpod_createdon'] = $itemdetail['fpot_createdon'];
                $fpoddata['fpod_createdby'] = $itemdetail['fpot_createdby'];
                $fpoddata['fpod_totalqty'] = $itemdetail['fpot_totalqty'];
                $fpoddata['fpod_balanceqty'] = $itemdetail['fpot_balanceqty'];
                $fpoddata['fpod_giftbalqty'] = $itemdetail['fpot_giftqty'];
                $fpoddata['fpod_idiscountcalculus'] = $itemdetail['fpot_idiscountcalculs'];
                $fpoddata['fpod_initialnetamount'] = $itemdetail['fpot_initialnetamount'];
                $fpoddata['fpod_netamount'] = $itemdetail['fpot_netamount'];
                $fpoddata['fpod_amount'] = $itemdetail['fpot_amount'];
                //margin distribution on purchase order details 14/3/2020
                $fpoddata['bmd_id'] = $itemdetail['bmd_id'];
                $fpoddata['bmd_margin'] = (float) $itemdetail['fpot_itemmrp'] - (float) $itemdetail['fpot_effectiverate'];
                $fpoddata['bmd_percent'] = $itemdetail['bmd_percent'];

                //on 20/10/2021




                $bmdDetails = $db->getFromDB("SELECT * FROM retaline_margindistributions WHERE bmd_id = {$itemdetail['bmd_id']}", true);

                $fpoddata['bmd_company'] = $bmdDetails['bmd_company'];
                $fpoddata['bmd_incentive'] = $bmdDetails['bmd_incentive'];
                $fpoddata['bmd_customer'] = $bmdDetails['bmd_customer'];
                $fpoddata['bmd_cs'] = $bmdDetails['bmd_hub']; //on 28 december 2020
                $fpoddata['bmd_distributor'] = $bmdDetails['bmd_distributor'];
                $fpoddata['bmd_retailor'] = $bmdDetails['bmd_retailor'];
                $fpoddata['bmd_driver'] = $bmdDetails['bmd_driver'];
                $fpoddata['bmd_courier'] = $bmdDetails['bmd_courier'];
                //echo 'fpot_poMMG' . $itemdetail['fpot_poMMG'];
                //print_r($bmdDetails);
                $margin['company'] = $itemdetail['fpot_poMMG'] * ($fpoddata['bmd_company'] / 100);
                $margin['operations'] = $itemdetail['fpot_poMMG'] * ($fpoddata['bmd_incentive'] / 100);
                $margin['cs'] = $itemdetail['fpot_poMMG'] * ($fpoddata['bmd_cs'] / 100);
                $margin['distributor'] = $itemdetail['fpot_poMMG'] * ($fpoddata['bmd_distributor'] / 100);
                $margin['retailor'] = $itemdetail['fpot_poMMG'] * ($fpoddata['bmd_retailor'] / 100);
                $margin['driver'] = $itemdetail['fpot_poMMG'] * ($fpoddata['bmd_driver'] / 100);
                $margin['courier'] = $itemdetail['fpot_poMMG'] * ($fpoddata['bmd_courier'] / 100);
                //print_r($margin);

                $fpod_spHmDel = $fpoddata['fpod_poLandingCost'] + ($margin['company'] + $margin['operations'] + $margin['cs'] + $margin['distributor'] + $margin['retailor'] + $margin['driver']);
                $fpoddata['fpod_spHmDel'] = round($fpod_spHmDel, 2);
                $fpod_spCouDel = $fpoddata['fpod_poLandingCost'] + ($margin['company'] + $margin['operations'] + $margin['cs'] + $margin['distributor'] + $margin['retailor'] + $margin['courier']);
                $fpoddata['fpod_spCouDel'] = round($fpod_spCouDel, 2);
                $fpod_spPikup = $fpoddata['fpod_poLandingCost'] + ($margin['company'] + $margin['operations'] + $margin['cs'] + $margin['distributor'] + $margin['retailor'] );
                $fpoddata['fpod_spPikup'] = round($fpod_spPikup, 2);

                $fpod_spetHmDel = ($fpoddata['fpod_spHmDel'] * 100) / (100 + $itemHistory['stit_GST']);
                $fpoddata['fpod_spetHmDel'] = round($fpod_spetHmDel, 2);
                $fpod_spetCouDel = ($fpoddata['fpod_spCouDel'] * 100) / (100 + $itemHistory['stit_GST']);
                $fpoddata['fpod_spetCouDel'] = round($fpod_spetCouDel, 2);
                $fpod_spetPikup = ($fpoddata['fpod_spPikup'] * 100) / (100 + $itemHistory['stit_GST']);
                $fpoddata['fpod_spetPikup'] = round($fpod_spetPikup, 2);

                $fpoddata['fpod_gstHmDel'] = $fpoddata['fpod_spHmDel'] - $fpoddata['fpod_spetHmDel'];
                $fpoddata['fpod_gstCouDel'] = $fpoddata['fpod_spCouDel'] - $fpoddata['fpod_spetCouDel'];
                $fpoddata['fpod_gstPikup'] = $fpoddata['fpod_spPikup'] - $fpoddata['fpod_spetPikup'];

                $fpoddata['fpod_marginHmDel'] = $fpoddata['fpod_spetHmDel'] - $fpoddata['fpod_effectiverate']; //fpod_effectiverate,fpod_poLandingCost
                $fpoddata['fpod_marginCouDel'] = $fpoddata['fpod_spetCouDel'] - $fpoddata['fpod_effectiverate'];
                $fpoddata['fpod_marginPikup'] = $fpoddata['fpod_spetPikup'] - $fpoddata['fpod_effectiverate'];

                $fpod_companyMargin = $fpoddata['fpod_marginHmDel'] * ($fpoddata['bmd_company'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_driver']));
                $fpoddata['fpod_companyMarginHD'] = floor($fpod_companyMargin * 100) / 100;
                $fpod_incentiveMargin = $fpoddata['fpod_marginHmDel'] * ($fpoddata['bmd_incentive'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_driver']));
                $fpoddata['fpod_incentiveMarginHD'] = floor($fpod_incentiveMargin * 100) / 100;
                $fpod_csMargin = $fpoddata['fpod_marginHmDel'] * ($fpoddata['bmd_cs'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_driver']));
                $fpoddata['fpod_csMarginHD'] = floor($fpod_csMargin * 100) / 100;
                $fpod_distributorMargin = $fpoddata['fpod_marginHmDel'] * ($fpoddata['bmd_distributor'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_driver']));
                $fpoddata['fpod_distributorMarginHD'] = floor($fpod_distributorMargin * 100) / 100;
                $fpod_retailorMargin = $fpoddata['fpod_marginHmDel'] * ($fpoddata['bmd_retailor'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_driver']));
                $fpoddata['fpod_retailorMarginHD'] = floor($fpod_retailorMargin * 100) / 100;
                $fpod_driverMargin = $fpoddata['fpod_marginHmDel'] * ($fpoddata['bmd_driver'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_driver']));
                $fpoddata['fpod_driverMarginHD'] = floor($fpod_driverMargin * 100) / 100;


                $fpod_companyMargin = $fpoddata['fpod_marginCouDel'] * ($fpoddata['bmd_company'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_courier']));
                $fpoddata['fpod_companyMarginCD'] = floor($fpod_companyMargin * 100) / 100;
                $fpod_incentiveMargin = $fpoddata['fpod_marginCouDel'] * ($fpoddata['bmd_incentive'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_courier']));
                $fpoddata['fpod_incentiveMarginCD'] = floor($fpod_incentiveMargin * 100) / 100;
                $fpod_csMargin = $fpoddata['fpod_marginCouDel'] * ($fpoddata['bmd_cs'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_courier']));
                $fpoddata['fpod_csMarginCD'] = floor($fpod_csMargin * 100) / 100;
                $fpod_distributorMargin = $fpoddata['fpod_marginCouDel'] * ($fpoddata['bmd_distributor'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_courier']));
                $fpoddata['fpod_distributorMarginCD'] = floor($fpod_distributorMargin * 100) / 100;
                $fpod_retailorMargin = $fpoddata['fpod_marginCouDel'] * ($fpoddata['bmd_retailor'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_courier']));
                $fpoddata['fpod_retailorMarginCD'] = floor($fpod_retailorMargin * 100) / 100;
                $fpod_courierMargin = $fpoddata['fpod_marginCouDel'] * ($fpoddata['bmd_courier'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_courier']));
                $fpoddata['fpod_courierMarginCD'] = floor($fpod_courierMargin * 100) / 100;

                $fpod_companyMargin = $fpoddata['fpod_marginPikup'] * ($fpoddata['bmd_company'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor']));
                $fpoddata['fpod_companyMargin'] = floor($fpod_companyMargin * 100) / 100;
                $fpod_incentiveMargin = $fpoddata['fpod_marginPikup'] * ($fpoddata['bmd_incentive'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor']));
                $fpoddata['fpod_incentiveMargin'] = floor($fpod_incentiveMargin * 100) / 100;
                $fpod_csMargin = $fpoddata['fpod_marginPikup'] * ($fpoddata['bmd_cs'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor']));
                $fpoddata['fpod_csMargin'] = floor($fpod_csMargin * 100) / 100;
                $fpod_distributorMargin = $fpoddata['fpod_marginPikup'] * ($fpoddata['bmd_distributor'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor']));
                $fpoddata['fpod_distributorMargin'] = floor($fpod_distributorMargin * 100) / 100;
                $fpod_retailorMargin = $fpoddata['fpod_marginPikup'] * ($fpoddata['bmd_retailor'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor']));
                $fpoddata['fpod_retailorMargin'] = floor($fpod_retailorMargin * 100) / 100;









//sku rate - sp for customer b2c
                // echo $fpoddata['fpod_leastSKUqty'];
                $fpoddata['fpod_customerRateHmDel'] = round($fpoddata['fpod_spetHmDel'] / $fpoddata['fpod_leastSKUqty'], 2);
                $fpoddata['fpod_customerRateCouDel'] = round($fpoddata['fpod_spetCouDel'] / $fpoddata['fpod_leastSKUqty'], 2);
                $fpoddata['fpod_customerRatePikup'] = round($fpoddata['fpod_spetPikup'] / $fpoddata['fpod_leastSKUqty'], 2);

                $bmdDetailsb2b = $db->getFromDB("SELECT * FROM retaline_margindistributionsb2b WHERE bmd_id = {$itemdetail['b2bbmd_id']}", true);

                $fpoddata['b2bbmd_id'] = $itemdetail['b2bbmd_id'];

                if ($fpoddata['fpod_itempts'] > 0 && $fpoddata['fpod_itemptr'] > 0) {
                    $fpod_itemptswithtax = $fpoddata['fpod_itempts'] * (100 + $itemHistory['stit_GST']) / 100;
                    $fpoddata['fpod_itemptswithtax'] = round($fpod_itemptswithtax, 2);
                    $fpod_itemptrwithtax = $fpoddata['fpod_itemptr'] * (100 + $itemHistory['stit_GST']) / 100;
                    $fpoddata['fpod_itemptrwithtax'] = round($fpod_itemptrwithtax, 2);
                    $fpoddata['fpod_itemptsgst'] = $fpoddata['fpod_itemptswithtax'] - $fpoddata['fpod_itempts'];
                    $fpoddata['fpod_itemptrgst'] = $fpoddata['fpod_itemptrwithtax'] - $fpoddata['fpod_itemptr'];

                    $fpoddata['fpod_itemptsmargin'] = $fpoddata['fpod_itempts'] - $fpoddata['fpod_effectiverate']; //fpod_effectiverate,fpod_poLandingCost
                    $fpoddata['fpod_itemptrmargin'] = $fpoddata['fpod_itemptr'] - $fpoddata['fpod_effectiverate'];

                    $fpod_itempts_companymargin = $fpoddata['fpod_itemptsmargin'] * ($bmdDetailsb2b['bmd_company'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_cs']));
                    $fpoddata['fpod_itempts_companymargin'] = round($fpod_itempts_companymargin, 2);
                    $fpod_itempts_opermargin = $fpoddata['fpod_itemptsmargin'] * ($bmdDetailsb2b['bmd_management'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_cs']));
                    $fpoddata['fpod_itempts_opermargin'] = round($fpod_itempts_opermargin, 2);
                    $fpod_itempts_csmargin = $fpoddata['fpod_itemptsmargin'] * ($bmdDetailsb2b['bmd_cs'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_cs']));
                    $fpoddata['fpod_itempts_csmargin'] = round($fpod_itempts_csmargin, 2);

                    $fpod_itemptr_companymargin = $fpoddata['fpod_itemptrmargin'] * ($bmdDetailsb2b['bmd_company'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_distributor'] + $bmdDetailsb2b['bmd_cs']));
                    $fpoddata['fpod_itemptr_companymargin'] = round($fpod_itemptr_companymargin, 2);
                    $fpod_itemptr_opermargin = $fpoddata['fpod_itemptrmargin'] * ($bmdDetailsb2b['bmd_management'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_distributor'] + $bmdDetailsb2b['bmd_cs']));
                    $fpoddata['fpod_itemptr_opermargin'] = round($fpod_itemptr_opermargin, 2);
                    $fpod_itemptr_csmargin = $fpoddata['fpod_itemptrmargin'] * ($bmdDetailsb2b['bmd_cs'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_distributor'] + $bmdDetailsb2b['bmd_cs']));
                    $fpoddata['fpod_itemptr_csmargin'] = round($fpod_itemptr_csmargin, 2);
                    $fpod_itemptr_dtrbtrmargin = $fpoddata['fpod_itemptrmargin'] * ($bmdDetailsb2b['bmd_distributor'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_distributor'] + $bmdDetailsb2b['bmd_cs']));
                    $fpoddata['fpod_itemptr_dtrbtrmargin'] = round($fpod_itemptr_dtrbtrmargin, 2);
                }


                $fpod_b2bCSsp = $fpoddata['fpod_poLandingCost'] + (($itemdetail['fpot_poMMG'] * $bmdDetailsb2b['bmd_company'] / 100) + ($itemdetail['fpot_poMMG'] * $bmdDetailsb2b['bmd_management'] / 100) + ($itemdetail['fpot_poMMG'] * $bmdDetailsb2b['bmd_cs'] / 100));
                $fpoddata['fpod_b2bCSsp'] = round($fpod_b2bCSsp, 2);
                $fpod_b2bRetailsp = $fpoddata['fpod_poLandingCost'] + (($itemdetail['fpot_poMMG'] * $bmdDetailsb2b['bmd_company'] / 100) + ($itemdetail['fpot_poMMG'] * $bmdDetailsb2b['bmd_management'] / 100) + ($itemdetail['fpot_poMMG'] * $bmdDetailsb2b['bmd_cs'] / 100) + ($itemdetail['fpot_poMMG'] * $bmdDetailsb2b['bmd_distributor'] / 100));
                $fpoddata['fpod_b2bRetailsp'] = round($fpod_b2bRetailsp, 2);
                $fpod_b2bCSspet = $fpoddata['fpod_b2bCSsp'] * 100 / (100 + $itemHistory['stit_GST']);
                $fpoddata['fpod_b2bCSspet'] = round($fpod_b2bCSspet, 2);
                $fpod_b2bRetailspet = $fpoddata['fpod_b2bRetailsp'] * 100 / (100 + $itemHistory['stit_GST']);
                $fpoddata['fpod_b2bRetailspet'] = round($fpod_b2bRetailspet, 2);
                $fpoddata['fpod_b2bCSgst'] = $fpoddata['fpod_b2bCSsp'] - $fpoddata['fpod_b2bCSspet'];
                $fpoddata['fpod_b2bRetailgst'] = $fpoddata['fpod_b2bRetailsp'] - $fpoddata['fpod_b2bRetailspet'];

                $fpoddata['fpod_b2bCSmargin'] = $fpoddata['fpod_b2bCSspet'] - $fpoddata['fpod_effectiverate']; //fpod_effectiverate,fpod_poLandingCost
                $fpoddata['fpod_b2bRetailmargin'] = $fpoddata['fpod_b2bRetailspet'] - $fpoddata['fpod_effectiverate'];

                $fpod_b2bcs_companymargin = $fpoddata['fpod_b2bCSmargin'] * ($bmdDetailsb2b['bmd_company'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_cs']));
                $fpoddata['fpod_b2bcs_companymargin'] = round($fpod_b2bcs_companymargin, 2);
                $fpod_b2bcs_opermargin = $fpoddata['fpod_b2bCSmargin'] * ($bmdDetailsb2b['bmd_management'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_cs']));
                $fpoddata['fpod_b2bcs_opermargin'] = round($fpod_b2bcs_opermargin, 2);
                $fpod_b2bcs_csmargin = $fpoddata['fpod_b2bCSmargin'] * ($bmdDetailsb2b['bmd_cs'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_cs']));
                $fpoddata['fpod_b2bcs_csmargin'] = round($fpod_b2bcs_csmargin, 2);

                $fpod_b2bretai_companymargin = $fpoddata['fpod_b2bRetailmargin'] * ($bmdDetailsb2b['bmd_company'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_distributor'] + $bmdDetailsb2b['bmd_cs']));
                $fpoddata['fpod_b2bretai_companymargin'] = round($fpod_b2bretai_companymargin, 2);
                $fpod_b2bretai_opermargin = $fpoddata['fpod_b2bRetailmargin'] * ($bmdDetailsb2b['bmd_management'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_distributor'] + $bmdDetailsb2b['bmd_cs']));
                $fpoddata['fpod_b2bretai_opermargin'] = round($fpod_b2bretai_opermargin, 2);
                $fpod_b2bretai_csmargin = $fpoddata['fpod_b2bRetailmargin'] * ($bmdDetailsb2b['bmd_cs'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_distributor'] + $bmdDetailsb2b['bmd_cs']));
                $fpoddata['fpod_b2bretai_csmargin'] = round($fpod_b2bretai_csmargin, 2);
                $fpod_b2bretai_dtrbtrmargin = $fpoddata['fpod_b2bRetailmargin'] * ($bmdDetailsb2b['bmd_distributor'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_distributor'] + $bmdDetailsb2b['bmd_cs']));
                $fpoddata['fpod_b2bretai_dtrbtrmargin'] = round($fpod_b2bretai_dtrbtrmargin, 2);






                $fpoddata['fpod_leastSKUb2bCSsp'] = round($fpoddata['fpod_b2bCSspet'] / $fpoddata['fpod_leastSKUqty'], 2);
                $fpoddata['fpod_leastSKUb2bRetailsp'] = round($fpoddata['fpod_b2bRetailspet'] / $fpoddata['fpod_leastSKUqty'], 2);

                /* on 20 /20/21
                 * 
                  $fpoddata['fpod_companyMargin'] = round($fpoddata['fpod_leastSKUmargin'] * ($bmdDetails['bmd_company'] / 100), 2);
                  $fpoddata['fpod_incentiveMargin'] = round($fpoddata['fpod_leastSKUmargin'] * ($bmdDetails['bmd_incentive'] / 100), 2);
                  $fpoddata['fpod_csMargin'] = round($fpoddata['fpod_leastSKUmargin'] * ($bmdDetails['bmd_hub'] / 100), 2);
                  $fpoddata['fpod_distributorMargin'] = round($fpoddata['fpod_leastSKUmargin'] * ($bmdDetails['bmd_distributor'] / 100), 2);
                  $fpoddata['fpod_retailorMargin'] = round($fpoddata['fpod_leastSKUmargin'] * ($bmdDetails['bmd_retailor'] / 100), 2);
                  $fpoddata['fpod_driverMargin'] = round($fpoddata['fpod_leastSKUmargin'] * ($bmdDetails['bmd_driver'] / 100), 2);
                  $fpoddata['fpod_courierMargin'] = round($fpoddata['fpod_leastSKUmargin'] * ($bmdDetails['bmd_courier'] / 100), 2);
                 * $fpod_customerProfitHmDel = $fpoddata['fpod_leastSKUmargin'] - ($fpoddata['fpod_companyMargin'] + $fpoddata['fpod_csMargin'] + $fpoddata['fpod_distributorMargin'] +
                  $fpoddata['fpod_retailorMargin'] + $fpoddata['fpod_incentiveMargin'] + $fpoddata['fpod_driverMargin']);
                  $fpod_customerProfitCouDel = $fpoddata['fpod_leastSKUmargin'] - ($fpoddata['fpod_companyMargin'] + $fpoddata['fpod_csMargin'] + $fpoddata['fpod_distributorMargin'] +
                  $fpoddata['fpod_retailorMargin'] + $fpoddata['fpod_incentiveMargin'] + $fpoddata['fpod_courierMargin']);
                  $fpod_customerProfitPikup = $fpoddata['fpod_leastSKUmargin'] - ($fpoddata['fpod_companyMargin'] + $fpoddata['fpod_csMargin'] + $fpoddata['fpod_distributorMargin'] +
                  $fpoddata['fpod_retailorMargin'] + $fpoddata['fpod_incentiveMargin']);
                  $fpoddata['fpod_customerProfitHmDel'] = round($fpod_customerProfitHmDel, 2);
                  $fpoddata['fpod_customerProfitCouDel'] = round($fpod_customerProfitCouDel, 2);
                  $fpoddata['fpod_customerProfitPikup'] = round($fpod_customerProfitPikup, 2);
                 *  on 15/10/21 
                  $fpoddata['fpod_customerRateHmDel'] = $fpoddata['fpod_leastSKUepr'] + $fpoddata['fpod_companyMargin'] + $fpoddata['fpod_csMargin'] + $fpoddata['fpod_distributorMargin'] +
                  $fpoddata['fpod_retailorMargin'] + $fpoddata['fpod_incentiveMargin'] + $fpoddata['fpod_driverMargin'];
                  $fpoddata['fpod_customerRateCouDel'] = $fpoddata['fpod_leastSKUepr'] + $fpoddata['fpod_companyMargin'] + $fpoddata['fpod_csMargin'] + $fpoddata['fpod_distributorMargin'] +
                  $fpoddata['fpod_retailorMargin'] + $fpoddata['fpod_incentiveMargin'] + $fpoddata['fpod_courierMargin']; //add courier too
                  $fpoddata['fpod_customerRatePikup'] = $fpoddata['fpod_leastSKUepr'] + $fpoddata['fpod_companyMargin'] + $fpoddata['fpod_csMargin'] + $fpoddata['fpod_distributorMargin'] +
                  $fpoddata['fpod_retailorMargin'] + $fpoddata['fpod_incentiveMargin'];

                  $fpoddata['fpod_customerProfitHmDel'] = 100 - ($fpoddata['fpod_customerRateHmDel'] * 100) / $fpoddata['fpod_leastSKUmrp'];
                  $fpoddata['fpod_customerProfitCouDel'] = 100 - ($fpoddata['fpod_customerRateCouDel'] * 100) / $fpoddata['fpod_leastSKUmrp'];
                  $fpoddata['fpod_customerProfitPikup'] = 100 - ($fpoddata['fpod_customerRatePikup'] * 100) / $fpoddata['fpod_leastSKUmrp']; */
                if ($itemdetail['fpot_giftqty'] > 0) {
                    $fpoddata['fpod_hasGift'] = 1;
                } else {
                    $fpoddata['fpod_hasGift'] = 0;
                }

                $fpoddata['fpod_purchasingUnit'] = $itemdetail['fpot_purchasingUnit'];
                $fpoddata['fpod_itemOrderIds'] = $itemdetail['fpot_itemOrderIds'];
//                $fpoddata['bmd_company'] = $itemdetail['bmd_company'];
//                $fpoddata['bmd_hub'] = $itemdetail['bmd_hub'];
//                $fpoddata['bmd_incentive'] = $itemdetail['bmd_incentive'];
//                $fpoddata['bmd_technology'] = $itemdetail['bmd_technology'];
//                $fpoddata['bmd_customer'] = $itemdetail['bmd_customer'];
                //print_r($fpoddata);
                //exit();
                $podstatus = $db->perform('finascop_purchase_order_details', $fpoddata);
                if ($fpo_potype == 2) {
                    $itemOrderIds = explode(',', $itemdetail['fpot_itemOrderIds']);
                    foreach ($itemOrderIds as $itemOrderId) {
                        if (!in_array($itemOrderId, $poOrders, true)) {
                            array_push($poOrders, $itemOrderId);
                        }

                        $poOrderMaping['poOrdIt_orderId'] = $itemOrderId;
                        $poOrderMaping['poOrdIt_itemId'] = $fpoddata['fpod_itemid'];
                        $poOrderMaping['poOrdIt_poid'] = $fpoddata['fpod_fpoId'];
                        $count = $db->getItemFromDB("SELECT COUNT(*) FROM poOrderItemMapping WHERE poOrdIt_orderId = {$itemOrderId} AND poOrdIt_itemId = {$fpoddata['fpod_itemid']} AND poOrdIt_poid = {$fpoddata['fpod_fpoId']}");
                        if ($count == 0) {
                            $podMapstatus = $db->perform('poOrderItemMapping', $poOrderMaping);
                        }
                    }
                }
            }
            if (count($poOrders) > 0) {
                foreach ($poOrders as $poOrder) {
                    $orderItems = $db->getItemFromDB("SELECT GROUP_CONCAT(item_product_id) FROM retaline_customer_order_items WHERE customer_order_id = {$poOrder}");
                    $orderItemsArray = explode(',', $orderItems);
                    $poItems = $db->getItemFromDB("SELECT GROUP_CONCAT(poOrdIt_itemId) FROM poOrderItemMapping WHERE poOrdIt_orderId = {$poOrder}");
                    $poItemsArray = explode(',', $poItems);

                    $itemsSame = (count(array_unique(array_merge($orderItemsArray, $poItemsArray))) === count($poItemsArray)) ? 1 : 2;
                    if ($itemsSame == 1) {
                        $dataRco['updated_at'] = date('Y-m-d H:i:s');
                        $dataRco['status_id'] = 31;
                        $status = $db->perform('retaline_customer_order', $dataRco, 'update', " status_id = 30 AND order_id = {$poOrder}");
                    }
                }
            }

            $status = $db->query("DELETE FROM finascop_purchase_order_temp WHERE fpot_uniqueid = '{$uniqueId}'");
            $status = $db->query("DELETE FROM finascop_purchase_order_poadhoc WHERE adhoc_uniqueid = '{$uniqueId}'");
            $msg = "'Error while saving PO.'";
            $podstatus = $db->query('commit');
        } else {
            $msg = "'Item details are not added. Please add details'";
        }
        if ($podstatus == 1) {
            $msg = "' PO details saved.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {

            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'getPurchaseOrderData':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 12;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['po_id', 'po_number', 'po_date', 'po_total'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'po_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'fpo_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;

        $filter_qry = " WHERE 1 = 1 ";
        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }

                        break;
                }
            }
        }
        if ($sort == 'fpo_poDate') {
            $sort = 'fpo_id';
        }
//        else {
//            $filter_qry .= " and (fpo_Active = 1) ";
//        }
if ($_SESSION['admin']->br_PyramidLevel == 1) {
    $filter_qry .= " ";
    /*if ($_SESSION['admin']->IsSuperUser == 'Yes') {
        $filter_qry .= " ";
    } else {
        $filter_qry .= " AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} " ;
    }*/
} else {
    $filter_qry .= " AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} " ;
}
        $date = date('dd-mm-YYYY');
        $countDataQuery = "SELECT count(*) from finascop_purchase_order fp INNER JOIN finascop_usr_master um ON fp.fpo_poOrderedby = um.UserId {$filter_qry} ";
        $listQuery = "SELECT  fpo_id,fpo_poNumber,DATE_FORMAT(fpo_poDate,'%d-%m-%Y') as fpo_poDate,fpo_paymentTerms,fpo_poDeliveryDate,fpo_poDeliveryType,fpo_paymentValue ,UserName,fpo_vendorName,
            DATE_FORMAT(fpo_validDate,'%d-%m-%Y') as fpo_validDate,IF((fpo_Active=1),'Active','Inactive') AS fpo_Active,IF((fpo_potype=1),'Manual','Initiated') AS fpo_potype   
 FROM finascop_purchase_order fp INNER JOIN finascop_usr_master um ON fp.fpo_poOrderedby = um.UserId {$filter_qry} ORDER BY {$sort} {$dir} LIMIT $start,$limit ";
        $db->printGridJson($countDataQuery, $listQuery);
        break;
    case 'getOrderDetails':
        $data = $_POST['fpo_id'];
        $podata = $db->getFromDB("SELECT  fpo_id,fpo_poNumber,fpo_poDate,fpo_paymentTerms,fpo_poDeliveryDate,fpo_poDeliveryType,fpo_paymentValue  FROM finascop_purchase_order where fpo_id = {$data}", true);
        $qry = "SELECT fpod_itemname,fpod_itemqty,fpod_itemoffrrate,fpod_itemmrp,fpod_itemoffrqty,fpod_itemoffrrate,fpod_effectiverate FROM finascop_purchase_order_details fpod WHERE fpod_fpoId = $data ";

        $items = $db->getMulipleData($qry, true);

        if (!empty($items)) {
            echo '{"podata":' . json_encode($podata) . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;
    case 'getVendorName':
        $qry = $db->getMulipleData("SELECT stpa_id,stpa_Fname FROM finascop_stock_party WHERE stpa_IsVendor = 1 AND br_id = {$_SESSION['admin']->finascop_current_branch_id} ORDER BY stpa_Fname ASC", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'vendorItemStore':
        $stpa_id = $_POST['stpa_id'];
        $itemIds = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_id) FROM finascop_stock_party_items WHERE stpa_id = {$stpa_id}");
        if (!empty($itemIds)) {
            $qry = "select stit_ID,stit_SKU from " . FINASCOP_DB . "finascop_stock_itemmaster where stit_ID IN({$itemIds}) and stit_status = 1 order by stit_SKU";
        $data = $db->getMultipleData($qry, true);
        }

        if (!empty($data)) {
            echo json_encode($data);
        } else
            echo [];
        break;
    case 'listPodetailsStore':
        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }
        $fpot_uniqueid = $_POST['fpot_uniqueid'];
        $countDataQuery = "SELECT count(*) from finascop_purchase_order_temp where fpot_uniqueid = '{$fpot_uniqueid}' {$filter_qry} ";
        $listQuery = "SELECT  fpot_vendorid,fpot_itemid,fpot_itemname,fpot_itemmrp,fpot_itemqty,fpot_itemoffrqty,if(fpot_itemoffrqty > 0,CONCAT(fpot_itemqty,'+',fpot_itemoffrqty),fpot_totalqty) as fpot_totalqty,fpot_balanceqty,fpot_itemoffrrate,fpot_itemoffrrateet,fpot_itemaddidisc,fpot_effectiverate,"
                . "fpot_idiscountcalculs,fpot_netamount,fpot_amount,fpot_initialnetamount,fpot_gendiscount,fpot_shippingcharge,fpot_pogstAmt,fpot_netamountTotal,fpot_netamountet,fpot_leastSKUepr,fpot_leastSKUmrp,fpot_leastSKUoffrrateet,fpot_leastSKUqty,fpot_purchasingUnit,fpot_leastSKUTotalqty,"
                . "(SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = fpot_purchasingUnit) as unitName,"
                . "(SELECT least_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = fpot_itemid) as leastSKU,"
                . "IF(fpot_itemaddidisc > 0,(CONCAT(fpot_itemaddidisc,'',IF(fpot_idiscountcalculs = 'Amount',' Rs',' %'))),'') AS itemDisc from finascop_purchase_order_temp where fpot_uniqueid = '{$fpot_uniqueid}' ORDER BY fpot_createdon ASC ";
        $db->printGridJson($countDataQuery, $listQuery);

        break;
    case 'generateUniqueId':
        $uniqueId = '';
        while ($uniqueId == '') {
            $uniqueId = getNewFinascopApiKey();
        }

        echo '{"uid":"' . $uniqueId . '"}';
        break;
    case 'getGenDiscounCalculate':
        //print_r($_POST);exit();
        $fpot_uniqueid = $_POST['fpot_uniqueid'];
        $total_initialnetamount = $_POST['total_initialnetamount'];
        if ($_POST['fpot_gdiscpercent'] > 0) {
            $fpot_gdiscpercent = $_POST['fpot_gdiscpercent'];
        } else {
            $fpot_gdiscpercent = 0;
        }

        $fpot_shippingpercent = $_POST['fpot_hshippingcharge'];
        $fpotDeatils = $db->getMultipleData("SELECT fpot_itemid,fpot_netamount,fpot_amount,fpot_initialnetamount,fpot_totalqty,fpot_effectiverate,fpot_itemoffrrateet,fpot_idiscountcalculs,fpot_itemaddidisc,"
                . "fpot_itemqty,fpot_netamountet,fpot_pogstType,fpot_itemoffrrate FROM finascop_purchase_order_temp where fpot_uniqueid = '{$fpot_uniqueid}' ", true);
        $db->query('begin');
        $handlinchgGstVal = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'HANDLING_CHRG_GST'");
        $handlinchgGst = $_POST['adhoc_shippingcharge'] - (($_POST['adhoc_shippingcharge'] * 100) / (100 + $handlinchgGstVal ));
        $handlinchgGst = round($handlinchgGst, 2);
        $totalnetAmount = $db->getItemFromDB("SELECT SUM(fpot_netamount) FROM finascop_purchase_order_temp where fpot_uniqueid = '{$fpot_uniqueid}' ");
        foreach ($fpotDeatils as $fpotDeatil) {
            //print_r($fpotDeatil);
            $taxRate = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$fpotDeatil['fpot_itemid']}");

            $amount = $fpotDeatil['fpot_itemoffrrate'] * $fpotDeatil['fpot_itemqty'];
            //echo 'fpot_initialnetamount'.$fpotDeatil['fpot_initialnetamount'];
            //echo $fpotDeatil['fpot_itemaddidisc'];
            if ($fpotDeatil['fpot_idiscountcalculs'] == 'Amount') {
                $fpot['fpot_itemaddidiscamt'] = $fpotDeatil['fpot_itemaddidisc'];
                $amountAftrItemDis = $amount - $fpot['fpot_itemaddidiscamt'];
                //$fpot['fpot_itemaddidiscamt'] = $fpotDeatil['fpot_itemaddidisc'];
                //$netOfferRete = $fpotDeatil['fpot_itemoffrrateet'] - $fpotDeatil['fpot_itemaddidisc'];
            } else {
                $fpot_itemaddidiscamt = $amount * $fpotDeatil['fpot_itemaddidisc'] / 100;
                //echo '$fpot_itemaddidiscamt'.$fpot_itemaddidiscamt;
                $fpot['fpot_itemaddidiscamt'] = round($fpot_itemaddidiscamt, 2);
                $amountAftrItemDis = $amount - $fpot['fpot_itemaddidiscamt'];
                //$fpot_itemaddidiscamt = $fpotDeatil['fpot_itemoffrrateet'] * $fpotDeatil['fpot_itemaddidisc'] / 100;
                //$fpot['fpot_itemaddidiscamt'] = round($fpot_itemaddidiscamt, 2);
                //$netOfferRete = $fpotDeatil['fpot_itemoffrrateet'] - $fpot['fpot_itemaddidiscamt'];
            }
            //echo $fpotDeatil['fpot_itemaddidiscamt'];
            //echo '$amountAftrItemDis'.$amountAftrItemDis;
            //$netOfferRete = round($netOfferRete, 2);
            $generalDiscount = round($amountAftrItemDis * $fpot_gdiscpercent / 100, 2);
            $finatlnetAmt = round(($amountAftrItemDis - $generalDiscount), 2);
            $fpot['fpot_gendiscount'] = $generalDiscount;
            //$fpot_shippingcharge = $fpotDeatil['fpot_initialnetamount'] * $fpot_shippingpercent / 100;
            //$fpot['fpot_shippingcharge'] = round($fpot_shippingcharge, 2);
            //$fpot['fpot_netamount'] = floatval($fpotDeatil['fpot_initialnetamount']) - floatval($fpot['fpot_gendiscount']) + floatval($fpot['fpot_shippingcharge']);
            //$fpot_netamount = $finalOfferRate * $fpotDeatil['fpot_itemqty'];
            $fpot['fpot_netamount'] = $finatlnetAmt;
            //$fpot['fpot_netrate'] = $finalOfferRate;
            $fpot_netamountet = ($finatlnetAmt * 100) / (100 + $_POST['itemGST']);
            $fpot['fpot_netamountet'] = round($fpot_netamountet, 2);
            $fpot['fpot_pogstAmt'] = $fpot['fpot_netamount'] - $fpot['fpot_netamountet'];

            $fpot_itemoffrratech = $fpot['fpot_netamount'] / $fpotDeatil['fpot_itemqty'];
            $fpot['fpot_itemoffrratech'] = round($fpot_itemoffrratech, 2);
            $fpot_itemoffrrateetch = ($fpot['fpot_itemoffrratech'] * 100) / (100 + $_POST['itemGST']);
            $fpot['fpot_itemoffrrateetch'] = round($fpot_itemoffrrateetch, 2);
            //$fpot['fpot_pogstAmt'] = $fpot_netamount * $_POST['itemGST'] / 100;
            //$fpot['fpot_netamount'] = $fpot_netamount + $fpot['fpot_pogstAmt'];
//print_r($fpot);
            $con = "fpot_uniqueid = '{$fpot_uniqueid}' and fpot_itemid = {$fpotDeatil['fpot_itemid']}";
            //print_r($fpot);exit();
            $status = $db->perform('finascop_purchase_order_temp', $fpot, 'update', $con);
        }

        $fpot_netamountetTotal = $db->getItemFromDB("SELECT SUM(fpot_netamountTotal) FROM finascop_purchase_order_temp where fpot_uniqueid = '{$fpot_uniqueid}'");
        //echo '$fpot_netamountetTotal'.$fpot_netamountetTotal;
        $fpotDeatilsdup = $db->getMultipleData("SELECT fpot_itemmrp,fpot_itemid,fpot_netamount,fpot_amount,fpot_initialnetamount,fpot_totalqty,fpot_effectiverate,fpot_itemoffrrateet,fpot_idiscountcalculs,fpot_itemaddidisc,"
                . "fpot_itemqty,fpot_netamountet,fpot_netamountTotal,fpot_pogstAmt FROM finascop_purchase_order_temp where fpot_uniqueid = '{$fpot_uniqueid}' ", true);
        foreach ($fpotDeatilsdup as $fpotDeatil2) {
            //print_r($fpotDeatil2);
            $fpot_shippingcharge = $_POST['adhoc_shippingcharge'] * $fpotDeatil2['fpot_netamountTotal'] / $fpot_netamountetTotal;
            $fpot2['fpot_shippingcharge'] = round($fpot_shippingcharge, 2);
            $fpot_shippingchargeet = ($fpot2['fpot_shippingcharge'] * 100) / (100 + $handlinchgGstVal);
            $fpot2['fpot_shippingchargeet'] = round($fpot_shippingchargeet, 2);
            $fpot_shippingchargegst = $fpot2['fpot_shippingcharge'] - $fpot2['fpot_shippingchargeet'];
            $fpot2['fpot_shippingchargegst'] = round($fpot_shippingchargegst, 2);

//fpot_netamountet,fpot_pogstAmt
            $fpot2['fpot_netamountet'] = $fpotDeatil2['fpot_netamountet'] + $fpot2['fpot_shippingcharge'] - $fpot2['fpot_shippingchargegst'];
            $fpot2['fpot_pogstAmt'] = $fpotDeatil2['fpot_pogstAmt'] + $fpot2['fpot_shippingchargegst'];

            $fpot_effectiverate = $fpot2['fpot_netamountet'] / $fpotDeatil2['fpot_totalqty'];
            $fpot2['fpot_effectiverate'] = round($fpot_effectiverate, 2);

            $fpot_effectiverategst = $fpot2['fpot_pogstAmt'] / $fpotDeatil2['fpot_totalqty'];
            $fpot2['fpot_effectiverategst'] = round($fpot_effectiverategst, 2);

            $fpot2['fpot_poLandingCost'] = $fpot2['fpot_effectiverate'] + $fpot2['fpot_effectiverategst'];
            $fpot2['fpot_poMMG'] = $fpotDeatil2['fpot_itemmrp'] - $fpot2['fpot_poLandingCost'];
            //print_r($fpot2);exit();
            $con = "fpot_uniqueid = '{$fpot_uniqueid}' and fpot_itemid = {$fpotDeatil2['fpot_itemid']}";
            $status = $db->perform('finascop_purchase_order_temp', $fpot2, 'update', $con);
        }
        $adhocpo['adhoc_paymentTerms'] = $_POST['adhoc_paymentTerms'];
        $adhocpo['adhoc_paymentValue'] = $_POST['adhoc_paymentValue'];
        $adhocpo['adhoc_validityType'] = $_POST['adhoc_validityType'];
        $adhocpo['adhoc_shippingcharge'] = $_POST['adhoc_shippingcharge'];
        $adhocpo['adhoc_shippingchargeGST'] = $handlinchgGst;
        $adhocpo['adhoc_gdiscount'] = $_POST['adhoc_gdiscount'];
        $adhocpo['adhoc_gdiscounttype'] = $_POST['adhoc_gdiscounttype'];
        $adcon = " adhoc_uniqueid = '{$fpot_uniqueid}'";
        $adhocpo = array_filter($adhocpo);
        $status = $db->perform('finascop_purchase_order_poadhoc', $adhocpo, 'update', $adcon);
        $totalHC = $handlinchgGst + $_POST['adhoc_shippingcharge'];
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'General discount Apllied to PO items.'";
            echo '{"success":true,"hcg":' . $totalHC . ',"msg":' . $msg . '}';
        } else {
            $msg = "'Error while applying general discount.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'getPODetails':
        $poId = $_POST['poId'];
        $podata = $db->getFromDB("SELECT  fpo_id,fpo_poNumber,fpo_vendorId,fpo_vendorName,DATE_FORMAT(fpo_poDate,'%d-%m-%Y') as fpo_poDate,fpo_paymentTerms,fpo_poOrderedby,fpo_poValue,fpo_paymentValue,"
                . "CONCAT(fpo_gdiscpercent,'','%') as fpo_gdiscpercent,CONCAT(fpo_validityType,'',' days') as fpo_validityType,fpo_shippingcharge,fpo_centralStore,"
                . "(SELECT br_Name FROM finascop_branch WHERE br_ID = fpo_centralStore) as centralStore,fpo_poFinalValue,fpo_poValueDiff  FROM finascop_purchase_order where fpo_id = {$poId}", true);
        if (!empty($podata)) {
            echo json_encode($podata);
        }
        break;
    case 'viewListPodetailsStore':
        $poId = $_POST['poId'];
        $countQuery = "SELECT COUNT(*) FROM finascop_purchase_order_details fpod WHERE fpod_fpoId = {$poId}";
        $listQuery = "SELECT fpod_itemid, fpod_itemname, fpod_itemmrp, fpod_itemqty, fpod_itemoffrqty, if(fpod_itemoffrqty > 0,CONCAT(fpod_itemqty,'+',fpod_itemoffrqty),fpod_totalqty) as fpod_totalqty, fpod_balanceqty, fpod_itemoffrrate, fpod_itemoffrrateet, fpod_itemaddidisc, fpod_effectiverate,
                    fpod_idiscountcalculus,IF(fpod_itemaddidisc > 0,(CONCAT(fpod_itemaddidisc,'',IF(fpod_idiscountcalculus = 'Amount',' Rs',' %'))),'') AS itemDisc, fpod_amount, fpod_giftname, fpod_netamount, 
                    fpod_giftqty,fpod_notes,fpod_purchasingUnit,(SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = fpod_purchasingUnit) as unitName FROM finascop_purchase_order_details fpod 
                    WHERE fpod_fpoId = {$poId} ORDER BY fpod_id ASC";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'deleteVendorItemFromPO':
        $itemid = $_POST['itemid'];
        $uid = $_POST['uid'];
        $db->query('begin');
        $delquery = "DELETE FROM finascop_purchase_order_temp  WHERE fpot_uniqueid = '{$uid}' AND fpot_itemid = {$itemid}";
        $status = $db->query($delquery);

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Deleted Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while deleting data' }}";
        }
        break;
    case 'vendorDetails':
        $pe_party = $_POST['pe_party'];
        $vendordata = $db->getFromDB("SELECT *  FROM finascop_stock_party where stpa_id = {$pe_party}", true);
        if (!empty($vendordata)) {
            echo json_encode($vendordata);
        }
        break;
    case 'itemHistory':
        $pe_partyItems = $_POST['pe_partyItems'];
        $po_billing_to = $_POST['po_billing_to'];
        $isStandardPacking = $_POST['isStandardPacking'];
        $br_PyramidLevel = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch WHERE br_ID = {$po_billing_to}");

        $curBranchLevel = $db->getItemFromDB("SELECT br_stockLevel FROM finascop_branch WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");
        switch ($curBranchLevel) {
            case 1:
                $stockFields = ',stitl1_optimumqty as optiQty,stit11_minimumqty as minQty';
                break;
            case 2:
                $stockFields = ',stitl2_optimumqty as optiQty,stit12_minimumqty as minQty';
                break;
            case 3:
                $stockFields = ',stitl3_optimumqty as optiQty,stit13_minimumqty as minQty';
                break;
        }
        $qry = "SELECT m.stit_ID,stit_itemName,stit_SKU,stit_brand_name,stit_category_name,stit_product_variant,stit_quantity,stit_fixedB2BRates,isRRPApplicable,"
                . "(SELECT fpod_effectiverate from finascop_purchase_order_details where fpod_itemid = m.stit_ID ORDER BY fpod_id desc limit 1) as last_mrp,"
                . "(SELECT min(fpod_effectiverate) FROM finascop_purchase_order_details where fpod_itemid = m.stit_ID) as last_sp,stit_HSN_code,stit_GST,csb_package_type_name,cs_nos,"
                . "cs_package_type_name,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,cosb_package_type_name,stdpckl11_package_type_id,stdpckl12_package_type_id,"
                . "stdpckl21_package_type_id,stdpckl31_package_type_id,stdpckl41_package_type_id,isMedicine,stit_package_type_id,least_package_type_id,least_package_type_name "
                . "{$stockFields} FROM finascop_stock_itemmaster m WHERE m.stit_ID ='{$pe_partyItems}' ";

        $itemHistory = $db->getFromDB($qry, true);
        $itemHistory['optiQty'] = round($itemHistory['optiQty'], 0);
        $itemHistory['minQty'] = round($itemHistory['minQty'], 0);
        $itemHistory['stit_GST'] = round($itemHistory['stit_GST'], 2);
        $itemHistory['itemCSCount'] = $db->getItemFromDB("SELECT item_count FROM finascop_stock_branch_inventory WHERE branch_id = {$po_billing_to} AND stit_id = {$pe_partyItems}");
        $itemHistory['itemCSCount'] = ($itemHistory['itemCSCount'] > 0) ? $itemHistory['itemCSCount'] : 0;
        if ($itemHistory['optiQty'] == $itemHistory['optiQty']) {
            $itemHistory['itemSmaalStockUnit'] = $itemHistory['least_package_type_name'];
        } else {
            $itemHistory['itemSmaalStockUnit'] = $itemHistory['csb_package_type_name'] . ' - ' . $itemHistory['cs_nos'] . ' ' . $itemHistory['cs_package_type_name'] . ', ' . $itemHistory['cs_nos'] . ' ' . $itemHistory['cs_package_type_name'] . ' of ' . $itemHistory['ds_nos'] . ' ' . $itemHistory['ds_package_type_name'] . ' each, ' . $itemHistory['cs_nos'] * $itemHistory['ds_nos'] . $itemHistory['ds_package_type_name'] . ' of ' . $itemHistory['cos_nos'] . ' ' . $itemHistory['cos_package_type_name'] . ' each';
        }
        if ($isStandardPacking == 1) {
            $itemHistory['itemUnitForm'] = $itemHistory['csb_package_type_name'];
        } else {
            $itemHistory['itemUnitForm'] = $itemHistory['cosb_package_type_name'];
        }
        $itemHistory['billingToPramidLevel'] = $br_PyramidLevel;
        if (!empty($itemHistory)) {
            echo json_encode($itemHistory);
        }
        break;
    case 'getCentralStores':
        $centralStore = $db->getMulipleData("SELECT br_ID,CONCAT(br_Name,' - ',branch_shortname) AS br_Name FROM finascop_branch WHERE br_cpd > 0 AND br_PyramidLevel <> 1 AND br_type <> 1 AND br_status = 'Active' ORDER BY br_PyramidLevel ASC", true);
        //$centralStore = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch WHERE br_cpd = {$_SESSION['admin']->finascop_current_branch_id} AND br_PyramidLevel = 2 AND br_status = 'Active' ", true);
        if (!empty($centralStore)) {
            echo json_encode($centralStore);
        } else
            echo $centralStore;
        break;
    case 'printPODetails':
        ob_start();
        include('podetails.php');
        $resHtml = ob_get_contents();
        ob_end_clean();
        echo $resHtml;
        exit();
        break;
    case 'getPaymentTerms':
        $stpa_id = $_POST['stpa_id'];
        $paymentTerms = $db->getItemFromDB("select stpa_paymentTerms  from finascop_stock_party where stpa_id = {$stpa_id}");
        $qry = "select ptc_name as ptc_id,ptc_name from " . FINASCOP_DB . "retaline_paymtTermscfg where ptc_id in ({$paymentTerms}) order by ptc_id";
        $data = $db->getMultipleData($qry, true);
        //echo json_encode($data);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getPurchaseOrderAdhoc':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 12;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['po_id', 'po_number', 'po_date', 'po_total'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'po_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'fpot_createdon' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;

        $filter_qry = " WHERE 1 = 1 ";
        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }
        if ($_SESSION['admin']->br_PyramidLevel == 1) {
            $filter_qry .= " ";
            /*if ($_SESSION['admin']->IsSuperUser == 'Yes') {
                $filter_qry .= " ";
            } else {
                $filter_qry .= " AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} " ;
            }*/
        } else {
            $filter_qry .= " AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} " ;
        }
//        else {
//            $filter_qry .= " and (fpo_Active = 1) ";
//        }
        $date = date('Y-m-d');
        $countDataQuery = "SELECT count(*) from finascop_purchase_order_poadhoc fp  {$filter_qry}  ORDER BY adhoc_createdon DESC";
        $listQuery = "SELECT  adhoc_uniqueid ,adhoc_name,/*DATE_FORMAT(adhoc_createdon,'%d-%m-%Y %H:%i:%s') as*/ adhoc_createdon,adhoc_createdby,
            (SELECT stpa_Fname FROM finascop_stock_party WHERE stpa_id = adhoc_vendor) as vendorName,IF((adhoc_potype=2),'Initiated','Manual') AS fpo_potype 
 FROM finascop_purchase_order_poadhoc fp  {$filter_qry} ORDER BY adhoc_createdon DESC LIMIT $start,$limit ";
        $db->printGridJson($countDataQuery, $listQuery);

        break;
    case 'loadAdhocPO':

        $podata = $db->getFromSafe("SELECT  adhoc_uniqueid ,adhoc_name ,DATE_FORMAT(adhoc_createdon,'%d-%m-%Y %H:%i:%s') as adhoc_createdon,adhoc_createdby,adhoc_updatedon,
            adhoc_poValue,adhoc_paymentTerms,adhoc_paymentValue,adhoc_validityType,adhoc_shippingcharge,adhoc_gdiscount,adhoc_gdiscounttype,adhoc_poFinalValue,adhoc_poValueDifference,
            (SELECT stpa_Fname FROM finascop_stock_party WHERE stpa_id = adhoc_vendor) as vendorName,adhoc_vendor,adhoc_billingTo
 FROM finascop_purchase_order_poadhoc fp  {$filter_qry} where adhoc_uniqueid = ?", "s", [$_POST['uniqueid']], true);
        if (!empty($podata)) {
//            $fpot_assigneddby = $db->getItemSafe("SELECT fpot_assigneddby FROM finascop_purchase_order_temp WHERE fpot_uniqueid = ? GROUP BY fpot_uniqueid", "i", [$_POST['uniqueid']]);
//            if ($fpot_assigneddby > 0) {
//                $podata['assigneddby'] = $fpot_assigneddby;
//            }
            echo json_encode($podata);
        }

        break;
    case 'checkAssignPO':
        $fpot_assigneddby = $db->getItemSafe("SELECT fpot_assigneddby FROM finascop_purchase_order_temp WHERE fpot_uniqueid = ? GROUP BY fpot_uniqueid", "i", [$_POST['uniqueid']]);
        if ($fpot_assigneddby > 0) {
            $podata['assigneddby'] = $fpot_assigneddby;
        } else {
            $status = $db->executeSafe("UPDATE finascop_purchase_order_temp SET fpot_assigneddby = {$_SESSION['admin']->Finascop_UserId} where fpot_uniqueid = ?", "s", [$_POST['uniqueid']]);
            $podata['assigneddby'] = $_SESSION['admin']->Finascop_UserId;
        }
        echo json_encode($podata);
        break;

    case 'deleteAdhocDetails':
        $db->query('begin');
        $del_query = "DELETE FROM finascop_purchase_order_poadhoc WHERE adhoc_uniqueid='{$_POST['adhoc_uniqueid']}'";
        $temp = $db->query($del_query);
        if ($temp) {
            $del_query = "DELETE FROM finascop_purchase_order_temp WHERE fpot_uniqueid='{$_POST['adhoc_uniqueid']}'";
            $db->query($del_query);
        }
        $status = $db->query('commit');
        if (status) {
            echo "{success:true,valid:true,message:'Deleted Succesfully ' }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Deleteing.'}";
        }
        break;
    case 'adhocSaveClose':
        $fpot_uniqueid = $_POST['fpot_uniqueid'];
        $db->query('begin');

        $adhocpo['adhoc_paymentTerms'] = $_POST['adhoc_paymentTerms'];
        $adhocpo['adhoc_paymentValue'] = $_POST['adhoc_paymentValue'];
        $adhocpo['adhoc_validityType'] = $_POST['adhoc_validityType'];
        $adhocpo['adhoc_billingTo'] = $_POST['adhoc_billingTo'];
        $adhocpo['adhoc_poValueDifference'] = $_POST['adhoc_poValueDiff'];
        $adhocpo['adhoc_poFinalValue'] = $_POST['adhoc_poFinalValue'];
        $adcon = " adhoc_uniqueid = '{$fpot_uniqueid}'";
        $adhocpo = array_filter($adhocpo);
        if (count($adhocpo) > 0) {
            $status = $db->perform('finascop_purchase_order_poadhoc', $adhocpo, 'update', $adcon);
        }

        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Adhoc PO saved.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error while saving data.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'getPTStore':
        $packs = array();
        $search_hint = $_POST['query'];
        //print_r($_POST);
        if (!in_array($_POST['stdpckl11'], $packs)) {
            $packs[] = $_POST['stdpckl11'];
        }
//        if ($_POST['isMedicine'] != 1) {
//            if (!in_array($_POST['stdpckl12'], $packs)) {
//                $packs[] = $_POST['stdpckl12'];
//            }
//        }

        if (!in_array($_POST['stdpckl21'], $packs)) {
            $packs[] = $_POST['stdpckl21'];
        }
        if (!in_array($_POST['stdpckl31'], $packs)) {
            $packs[] = $_POST['stdpckl31'];
        }
        if (!in_array($_POST['stdpckl41'], $packs)) {
            $packs[] = $_POST['stdpckl41'];
        }
        $packs = array_filter($packs);
        if (count($packs) > 0) {
            $pachTyp = implode(',', $packs);
            $qry = "select package_type_id,package_type_name from " . FINASCOP_DB . "mypha_productpackage_type WHERE package_type_id IN ({$pachTyp}) AND status = 1 AND package_type_name LIKE '{$search_hint}%'order by package_type_name";
            $data = $db->getMultipleData($qry, true);
        }

        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'updateRRPValue':
        $mrpEntered = $_POST['mrpEntered'];
        $fpot_itemoffrrate = $_POST['fpot_itemoffrrate'];
        $pe_partyItems = $_POST['pe_partyItems'];
        $rrp_factordefault = $_SESSION['admin']->DEFAULT_RRP;
        if ($pe_partyItems > 0) {
            $rrpAlreadyExist = $db->getItemFromDB("SELECT GROUP_CONCAT(DISTINCT(fpod_itemmrp)) AS MRPS FROM finascop_purchase_order_details WHERE fpod_itemid = {$pe_partyItems} ");
            $rrpAlreadyExistArr = explode(',', $rrpAlreadyExist);
            if ($rrpAlreadyExistArr[0] > 0) {
                
                if($mrpEntered > 0){
                    $result['rrpCount'] = count($rrpAlreadyExistArr)+1;
                    $result['isAlready'] = 1;
                    $rrpValue = $rrpAlreadyExist.'.Entered RRP - '.$mrpEntered;
                }else{
                    $rrpValue = $rrpAlreadyExist;
                    $result['rrpCount'] = count($rrpAlreadyExistArr);
                    $result['isAlready'] = 1;
                }
                
            } else {
                if ($mrpEntered > 0) {
                    $rrpValue = round($mrpEntered, 2);
                    $result['isAlready'] = 0;
                    $result['rrpCount'] = 1;
                } else {
                    $itemDetails = $db->getFromDB("SELECT stit_SKU,pdt_brand,stit_itemId,product_category FROM finascop_stock_itemmaster WHERE stit_ID = {$pe_partyItems}", true);
                    $rrp_factorSKU = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 1 AND rrp_detail = {$pe_partyItems}");
                    if ($rrp_factorSKU > 1) {
                        $rrpFactor = $rrp_factorSKU;
                    } else {
                        $rrp_factorBrand = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 2 AND rrp_detail = {$itemDetails['pdt_brand']}");
                        if ($rrp_factorBrand > 1) {
                            $rrpFactor = $rrp_factorBrand;
                        } else {
                            $rrp_factorItem = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 3 AND rrp_detail = {$itemDetails['stit_itemId']}");
                            if ($rrp_factorItem > 1) {
                                $rrpFactor = $rrp_factorItem;
                            } else {
                                $rrp_factorSC = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 4 AND rrp_detail = {$itemDetails['product_category']}");
                                if ($rrp_factorSC > 1) {
                                    $rrpFactor = $rrp_factorSC;
                                }
                            }
                        }
                    }

                    if ($rrpFactor > 1) {
                        $rrpFactorCalc = $rrpFactor;
                    } else {
                        $rrpFactorCalc = $rrp_factordefault;
                    }
                    $result['rrpFactor'] = $rrpFactorCalc;
                    $rrpValue = $fpot_itemoffrrate * $rrpFactorCalc;
                    $rrpValue = round($rrpValue, 2);
                    $result['isAlready'] = 0;
                    $result['rrpCount'] = 1;
                }
            }


            $result['rrpValue'] = $rrpValue;

            $result['success'] = true;
        } else {
            $result['msg'] = "Enter valid data.";
            $result['success'] = false;
        }

        if (!empty($result)) {
            echo json_encode($result);
        }
        break;
}








