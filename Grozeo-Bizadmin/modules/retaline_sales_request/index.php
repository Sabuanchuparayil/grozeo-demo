<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");

switch ($op) {
    case 'b2bSOCustomerDetails':
        $b2bSOCustomer = $_POST['b2bSOCustomer'];
        $result = $db->getFromDB("SELECT b2b_Customer_Incharge,b2b_Customer_pincode,b2b_Customer_Mobile,b2b_Customer_gst,rbsch_name,b2b_Customer_gst,b2b_Customer_dlno1,b2b_Customer_dlno2,b2b_Customer_fssaino"
                . "  FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = {$b2bSOCustomer}", true);
        $isSalesRequestAvailable = $db->getFromDB("SELECT bbsr_id,bbsr_SRNumber,b2b_Customer_ID,b2b_Customer_Name,DATE_FORMAT(b2bsr_requestDate,'%d-%m-%Y') as b2bsr_requestDate FROM retaline_B2B_SalesRequest WHERE b2b_Customer_ID = {$b2bSOCustomer} AND bbso_id IS NULL ", true);
        $result['bbsr_id'] = $isSalesRequestAvailable['bbsr_id'];
        $result['bbsr_SRNumber'] = $isSalesRequestAvailable['bbsr_SRNumber'];
        $result['b2bsr_requestDate'] = $isSalesRequestAvailable['b2bsr_requestDate'];
        if (!empty($result)) {
            echo json_encode($result);
        } else {
            echo '{"data":[]}';
        }
        break;
    case 'getB2BCustomerItems' :
        if ($_POST['query'] != '') {
            $con = "AND stit_SKU LIKE '%" . $_POST['query'] . "%'";
        } else {
            $con = " ";
        }

        $qry = "SELECT fsi.stit_ID,fsi.stit_SKU FROM " . FINASCOP_DB . "finascop_stock_itemmaster fsi INNER JOIN finascop_stock_branch_inventory fsbi"
                . " ON fsi.stit_ID = fsbi.stit_id "
                . " WHERE  fsbi.branch_id = {$_SESSION['admin']->finascop_current_branch_id} AND stit_SalesEnabled = 1 and fsbi.item_count > 0 {$con}  ORDER BY stit_SKU";
        $result = $db->getMultipleData($qry, true);
        if (!empty($result)) {
            echo '{"totalCount":' . count($result) . ',"data":' . json_encode($result) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'getB2BBrCustomers':
        $result = $db->getMulipleData("SELECT b2b_Customer_ID,b2b_Customer_Name FROM retaline_B2Bcustomer WHERE b2b_Customer_status='Active' AND br_ID = {$_SESSION['admin']->finascop_current_branch_id}", true);
        if (!empty($result)) {
            echo '{"totalCount":' . count($result) . ',"data":' . json_encode($result) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'getPaymentTerms':
//$stpa_id = $_POST['stpa_id'];
//$paymentTerms = $db->getItemFromDB("select stpa_paymentTerms  from finascop_stock_party where stpa_id = {$stpa_id}");
        $qry = "select ptc_name as ptc_id,ptc_name from " . FINASCOP_DB . "retaline_paymtTermscfg where ptc_status = 1 order by ptc_id";
        $data = $db->getMultipleData($qry, true);
//echo json_encode($data);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'saveB2BSRItemDetails':

        $adhocCount = $db->getItemSafe("SELECT COUNT(*) FROM retaline_B2B_SalesRequest_adhoc WHERE adhoc_uniqueid = ? AND adhoc_updatedon = '{$_POST['sradhoc_updatedon']}'", "i", [$_POST['b2bsr_UniqueID']]);
//        if ($_POST['sradhoc_updatedon'] != '' && $adhocCount == 0) {
//            echo '{"success":false,"msg":"Reload data updation is going on."}';
//            exit();
//        }
        $db->query('begin');
        if ($adhocCount > 0) {
            $updatedDate = $db->getItemSafe("SELECT adhoc_updatedon FROM retaline_B2B_SalesRequest_adhoc WHERE adhoc_uniqueid = ?", "s", [$_POST['b2bsr_UniqueID']]);
            if ($updatedDate == $_POST['sradhoc_updatedon']) {
                $adhocData['adhoc_updatedon'] = date("Y-m-d H:i");
                $adhocData['adhoc_updatedby'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('retaline_B2B_SalesRequest_adhoc', $adhocData, 'update', " adhoc_uniqueid = '{$_POST['b2bsr_UniqueID']}'");
            } else {
                echo '{"success":false,"msg":"Reload data updation is going on."}';
                exit();
            }
        } else {
            $adhocData['adhoc_uniqueid'] = $_POST['b2bsr_UniqueID'];
            $adhocData['adhoc_customer'] = $_POST['b2bsr_Customerid'];
            $adhocData['adhoc_createdon'] = date("Y-m-d H:i");
            $adhocData['adhoc_createdby'] = $_SESSION['admin']->Finascop_UserId;
            $adhocData['adhoc_updatedon'] = date("Y-m-d H:i");
            $adhocData['adhoc_updatedby'] = $_SESSION['admin']->Finascop_UserId;
            $adhocData['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
            if ($_POST['adhoc_srValue'] > 0)
                $adhocData['adhoc_srValue'] = $_POST['adhoc_srValue'];
            $adhocData['adhoc_paymentTerms'] = $_POST['adhoc_paymentTerms'];
            if ($_POST['adhoc_paymentValue'] > 0)
                $adhocData['adhoc_paymentValue'] = $_POST['adhoc_paymentValue'];
            $adhocData['adhoc_validityType'] = $_POST['adhoc_validityType'];
            if ($_POST['adhoc_shippingcharge'] > 0)
                $adhocData['adhoc_shippingcharge'] = $_POST['adhoc_shippingcharge'];
            if ($_POST['adhoc_gdiscount'] > 0)
                $adhocData['adhoc_gdiscount'] = $_POST['adhoc_gdiscount'];
            $adhocData['adhoc_gdiscounttype'] = $_POST['adhoc_gdiscounttype'];
            $status = $db->perform('retaline_B2B_SalesRequest_adhoc', $adhocData);
        }
        $haveGST = $db->getItemSafe("SELECT b2b_Customer_gst FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = ?", "i", [$_POST['b2bsr_Customerid']]);

        $data['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
        $data['b2bsr_UniqueID'] = $_POST['b2bsr_UniqueID'];
        $data['b2bsr_Customerid'] = $_POST['b2bsr_Customerid'];
        $data['b2bsr_itemid'] = $_POST['b2bsr_itemid'];
        $data['b2bsr_itemname'] = $_POST['b2bsr_itemname'];
        $data['b2bsr_itemmrp'] = $_POST['b2bsr_itemmrp'];
        $data['b2bsr_itemqty'] = $_POST['b2bsr_itemqty'];
        $data['b2bsr_itemoffrqty'] = $_POST['b2bsr_itemoffrqty'];
        $data['b2bsr_itemrate'] = $_POST['b2bsr_itemrate'];
        $data['b2bsr_itemaddidisc'] = $_POST['b2bsr_itemaddidisc'];
        $data['b2bsr_amount'] = $_POST['b2bsr_amount'];
        $b2bsr_netamount = $_POST['b2bsr_netamount'];
        $data['b2bsr_netamountet'] = $b2bsr_netamount;
        $stit_GSTAmt = $b2bsr_netamount * $_POST['stit_GST'] / 100;
        $stit_GSTAmt = round($stit_GSTAmt, 2);
        if ($haveGST == '') {
            $cessAmt = $b2bsr_netamount / 100;
            $cessAmt = round($cessAmt, 2);
        } else {
            $cessAmt = 0;
        }
        $data['b2bsr_itemgst'] = $stit_GSTAmt;
        $data['b2bsr_itemcess'] = $cessAmt;
        $data['b2bsr_netamount'] = $_POST['b2bsr_netamount'] + $cessAmt + $stit_GSTAmt;
        $data['b2bsr_initialnetamount'] = $_POST['b2bsr_netamount'];
        $data['b2bsr_idiscountcalculs'] = $_POST['b2bsr_idiscountcalculs'];
        $data['b2bsr_createdon'] = date("Y-m-d H:i:s");
        $data['b2bsr_createdby'] = $_SESSION['admin']->Finascop_UserId;
        $data['b2bsr_totalqty'] = (int) $data['b2bsr_itemoffrqty'] + (int) $data['b2bsr_itemqty'];
        $data['b2bsr_balanceqty'] = $data['b2bsr_totalqty'];
        $data['b2bsr_effectiverate'] = $data['b2bsr_netamount'] / $data['b2bsr_totalqty'];
        $data['b2bsr_itemPkg'] = $_POST['b2bsr_itemPkg'];
//var fpot_itemoffrrateet = (parseFloat(fpot_itemoffrrate) * 100) / (100 + parseFloat(taxRate));
        $taxRate = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$data['b2bsr_itemid']}");
        $data['b2bsr_notes'] = $_POST['b2bsr_notes'];

//for margin distributions
        $data['b2bsr_itemrateet'] = $_POST['b2bsr_itemrateet'];
        (float) $eprbft = ((float) $data['b2bsr_effectiverate'] / (100 + (float) $taxRate)) * 100;
        (float) $mrpbft = ((float) $data['b2bsr_itemmrp'] / (100 + (float) $taxRate)) * 100;
        $actmarginDistriPercent = 100 - (($eprbft / $mrpbft) * 100);
        $marginDistriPercent = round($actmarginDistriPercent);
        $data['actual_marginDistri'] = $actmarginDistriPercent;
        $data['bmd_percent'] = $marginDistriPercent;

        $itemMargin = $db->getFromDB("SELECT rmim_bmd_id from retaline_margin_item_mapping where rmim_stit_id = {$data['b2bsr_itemid']}");
        if ($itemMargin > 0) {
            $marginDistri = $itemMargin;
        } else {
            $marginDistri = $db->getItemFromDB("SELECT bmd_id from retaline_margindistributionsb2b where is_default = 1");
        }

        $data['bmdd_id'] = $marginDistri;

        $dup = $db->getItemFromDB("SELECT COUNT(1) FROM retaline_B2B_SalesRequest_temp WHERE b2bsr_UniqueID = '{$data['b2bsr_UniqueID']}' and b2bsr_itemid = {$data['b2bsr_itemid']}");
        if ($dup > 0) {
            $con = "b2bsr_UniqueID = '{$data['b2bsr_UniqueID']}' and b2bsr_itemid = {$data['b2bsr_itemid']}";
            $status = $db->perform('retaline_B2B_SalesRequest_temp', $data, 'update', $con);
        } else {
            $status = $db->perform('retaline_B2B_SalesRequest_temp', $data);
        }
        $newupdatedDate = $db->getItemSafe("SELECT adhoc_updatedon FROM retaline_B2B_SalesRequest_adhoc WHERE adhoc_uniqueid = ?", "s", [$_POST['b2bsr_UniqueID']]);
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'B2B Sales Request Item saved successfully.'";
            echo '{"success":true,"date":"' . $newupdatedDate . '","msg":' . $msg . '}';
        } else {
            $msg = "'Error while saving .'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'generateUniqueID':
        $uniqueId = '';
        while ($uniqueId == '') {
            $uniqueId = getNewFinascopApiKey();
        }
        echo '{"uid":"' . $uniqueId . '"}';
        break;
    case 'itemHistory':
        $pe_partyItems = $_POST['b2bSalesRequestItems'];
        $b2bsr_Customerid = $_POST['b2bsr_Customerid'];

        $curBranchLevel = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");
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
        $qry = "SELECT m.stit_ID,stit_itemName,stit_SKU,stit_brand_name,stit_category_name,stit_product_variant,stit_quantity,stit_HSN_code,stit_GST,csb_package_type_name,cs_nos,cs_package_type_name,stit_fixedB2BRates,"
                . "ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,csb_package_type_id,cs_package_type_id,"
                . "(SELECT fpod_leastSKUmrp from finascop_stock_branch_inventory where stit_id = m.stit_ID AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} and item_count >0 order by id desc limit 1) as last_mrp,"
                . "(SELECT selling_price from finascop_stock_branch_inventory where stit_id = m.stit_ID AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} and item_count >0 order by id desc limit 1 ) as last_sp,"
                . "(SELECT fsbg_id from finascop_stock_branch_inventory where stit_id = m.stit_ID AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} and item_count >0  order by id desc limit 1 ) as fsbgId,"
                . "(SELECT item_count from finascop_stock_branch_inventory where stit_id = m.stit_ID AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} and item_count >0  order by id desc limit 1 ) as itemCount, "
                . "(SELECT fpod_itemleastSKUptr from finascop_stock_branch_inventory where stit_id = m.stit_ID AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} and item_count >0  order by id desc limit 1 ) as fpod_itemleastSKUptr,"
                . "(SELECT fpod_itemleastSKUpts from finascop_stock_branch_inventory where stit_id = m.stit_ID AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} and item_count >0  order by id desc limit 1 ) as fpod_itemleastSKUpts,"
                . "(SELECT fpod_leastSKUb2bCSsp from finascop_stock_branch_inventory where stit_id = m.stit_ID AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} and item_count >0  order by id desc limit 1 ) as  fpod_leastSKUb2bCSsp,"
                . "(SELECT fpod_leastSKUb2bRetailsp from finascop_stock_branch_inventory where stit_id = m.stit_ID AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} and item_count >0  order by id desc limit 1 ) as fpod_leastSKUb2bRetailsp "
                . "{$stockFields} FROM finascop_stock_itemmaster m WHERE m.stit_ID ='{$pe_partyItems}' ";
//                echo $qry;
//                exit(1);
        $itemHistory = $db->getFromDB($qry, true);
        $itemHistory['cs_nos'] = ($itemHistory['cs_nos'] > 0 ) ? $itemHistory['cs_nos'] : 1;
        $itemHistory['ds_nos'] = ($itemHistory['ds_nos'] > 0 ) ? $itemHistory['ds_nos'] : 1;
        $itemHistory['cos_nos'] = ($itemHistory['cos_nos'] > 0 ) ? $itemHistory['cos_nos'] : 1;

        $itemMargin = $db->getFromDB("SELECT rmim_bmd_id from retaline_margin_item_mapping where rmim_stit_id = {$pe_partyItems}");
        if ($itemMargin > 0) {
            $marginDistri = $db->getFromDB("SELECT bmd_company,bmd_cs,bmd_distributor,bmd_management from retaline_margindistributionsb2b where bmd_id = {$itemMargin}", true);
        } else {
            $marginDistri = $db->getFromDB("SELECT bmd_company,bmd_cs,bmd_distributor,bmd_management from retaline_margindistributionsb2b where is_default = 1", true);
        }

        $prices = $db->getFromDB("SELECT COUNT(*) AS itemCount,stiid_mrp,stiid_leastSKUmrp,stiid_leastSKUepr,fsbg_id,stiid_expirydate,stiid_poLandingCostleastSKU FROM finascop_stock_item_inventorydetails "
                . "WHERE stiid_itemmasterid = {$pe_partyItems} AND cpd_branch_id = {$_SESSION['admin']->finascop_current_branch_id} AND stiid_status IN (1,4) GROUP BY stiid_itemmasterid  ORDER BY stiid_expirydate asc", true);
        //$fsbg_epr = $db->getItemFromDb("SELECT fsbg_leastSKUepr FROM finascop_stock_item_batch_group WHERE fsbg_id = {$itemHistory['fsbgId']}");
        $fsbg_epr = $prices['stiid_poLandingCostleastSKU'];
        $itemHistory['itemSmaalStockUnit'] = $itemHistory['csb_package_type_name'] . ' contains ' . $itemHistory['cs_nos'] . ' quantity ' . $itemHistory['cs_package_type_name'] . ', ' . $itemHistory['cs_nos'] * $itemHistory['ds_nos'] . ' ' . $itemHistory['ds_package_type_name'] . ', ' . $itemHistory['cs_nos'] * $itemHistory['ds_nos'] * $itemHistory['cos_nos'] . ' ' . $itemHistory['cos_package_type_name'];
        if ($_SESSION['admin']->br_PyramidLevel == 2) {
            $itemHistory['itemUnitForm'] = $itemHistory['cs_package_type_name'];

            $itemHistory['skuMRP'] = $prices['stiid_leastSKUmrp'] * $itemHistory['cos_nos'] * $itemHistory['ds_nos'];
            $itemHistory['skuEPR'] = $fsbg_epr * $itemHistory['cos_nos'] * $itemHistory['ds_nos'];

            $totalMargin = $marginDistri['bmd_company'] + $marginDistri['bmd_cs'] + $marginDistri['bmd_management'];
        } if ($_SESSION['admin']->br_PyramidLevel == 3) {
            $itemHistory['itemUnitForm'] = $itemHistory['ds_package_type_name'];
            $itemHistory['skuMRP'] = $prices['stiid_leastSKUmrp'] * $itemHistory['cos_nos'];
            $itemHistory['skuEPR'] = $fsbg_epr * $itemHistory['cos_nos'];
            $totalMargin = $marginDistri['bmd_company'] + $marginDistri['bmd_cs'] + $marginDistri['bmd_management'] + $marginDistri['bmd_distributor'];
        }
        $priceDiff = $itemHistory['skuMRP'] - $itemHistory['skuEPR'];
        $itemHistory['skuRateDiff'] = $priceDiff;
        if ($itemHistory['stit_fixedB2BRates'] == 0) {
            //$itemHistory['skuRate'] = $itemHistory['skuEPR'] + ($priceDiff * $totalMargin / 100);
            if ($_SESSION['admin']->br_PyramidLevel == 2) {
                $itemHistory['skuRate'] = ($itemHistory['fpod_leastSKUb2bCSsp'] * (100 + $itemHistory['stit_GST']) / 100) * $itemHistory['cos_nos'] * $itemHistory['ds_nos'];
            } else if ($_SESSION['admin']->br_PyramidLevel == 3) {
                $itemHistory['skuRate'] = ($itemHistory['fpod_leastSKUb2bRetailsp'] * (100 + $itemHistory['stit_GST']) / 100) * $itemHistory['cos_nos'];
            }
        } else {
            if ($_SESSION['admin']->br_PyramidLevel == 2) {
                $itemHistory['skuRate'] = ($itemHistory['fpod_itemleastSKUpts'] * (100 + $itemHistory['stit_GST']) / 100) * $itemHistory['cos_nos'] * $itemHistory['ds_nos'];
            } else if ($_SESSION['admin']->br_PyramidLevel == 3) {
                $itemHistory['skuRate'] = ($itemHistory['fpod_itemleastSKUptr'] * (100 + $itemHistory['stit_GST']) / 100) * $itemHistory['cos_nos'];
            }
        }
        $lastPurchaseRate = $db->getItemFromDB("SELECT b2bsr_itemrate FROM retaline_B2B_SalesRequestDetails WHERE b2bsr_Customerid = {$b2bsr_Customerid} AND b2bsr_itemid = {$pe_partyItems} ORDER BY bbsr_id DESC LIMIT 1");
        if ($lastPurchaseRate > 0) {
            $itemHistory['lastPurchaseRate'] = $lastPurchaseRate;
        } else {
            $itemHistory['lastPurchaseRate'] = 0;
        }

        if (!empty($itemHistory)) {
            echo json_encode($itemHistory);
        }
        break;
    case 'srGenDiscounCalculate':
        $b2bsr_UniqueID = $_POST['b2bsr_UniqueID'];
        $total_initialnetamount = $_POST['total_initialnetamount'];

        if ($_POST['fpot_gdiscpercent'] > 0) {
            $fpot_gdiscpercent = $_POST['fpot_gdiscpercent'];
        } else {
            $fpot_gdiscpercent = 0;
        }

        $fpot_shippingpercent = $_POST['fpot_hshippingcharge'];
        $fpotDeatils = $db->getMultipleData("SELECT b2bsr_itemid,b2bsr_netamount,b2bsr_amount,b2bsr_initialnetamount,b2bsr_totalqty,b2bsr_effectiverate,b2bsr_itemrateet,b2bsr_itemaddidisc,b2bsr_idiscountcalculs,b2bsr_totalqty,b2bsr_itemqty FROM retaline_B2B_SalesRequest_temp where b2bsr_UniqueID = '{$b2bsr_UniqueID}' ", true);
        $handlinchgGst = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'HANDLING_CHRG_GST'");
        $handlinchgGst = $_POST['adhoc_shippingcharge'] * $handlinchgGst / 100;
        $db->query('begin');
        foreach ($fpotDeatils as $fpotDeatil) {

            if ($fpotDeatil['fpot_idiscountcalculs'] == 'Amount') {
                $netOfferRete = $fpotDeatil['b2bsr_itemrateet'] - $fpotDeatil['b2bsr_itemaddidisc'];
            } else {
                $netOfferRete = $fpotDeatil['b2bsr_itemrateet'] - ($fpotDeatil['b2bsr_itemrateet'] * $fpotDeatil['b2bsr_itemaddidisc'] / 100);
            }
            $netOfferRete = round($netOfferRete, 2);
            $generalDiscount = round($netOfferRete * $fpot_gdiscpercent / 100, 2);
            $finalOfferRate = round(($netOfferRete - $generalDiscount), 2);
            $fpot['b2bsr_gendiscount'] = $generalDiscount;
            $fpot_shippingcharge = $fpotDeatil['b2bsr_initialnetamount'] * $fpot_shippingpercent / 100;
            $fpot['b2bsr_shippingcharge'] = round($fpot_shippingcharge, 2);
            $fpot_netamount = $finalOfferRate * $fpotDeatil['b2bsr_itemqty'];
            $fpot['b2bsr_itemgst'] = $fpot_netamount * $_POST['itemGST'] / 100;
            $fpot['b2bsr_netamountet'] = $fpot_netamount;
            $fpot['b2bsr_netamount'] = $fpot_netamount + $fpot['b2bsr_itemgst'];
            $fpot_effectiverate = ($fpot['b2bsr_netamount'] + $fpot['b2bsr_shippingcharge'] + $handlinchgGst) / $fpotDeatil['b2bsr_totalqty'];
            $fpot['b2bsr_effectiverate'] = round($fpot_effectiverate, 2);

            //$fpot['b2bsr_gendiscount'] = floatval($fpotDeatil['b2bsr_initialnetamount']) * $fpot_gdiscpercent / 100;
            //$fpot['b2bsr_shippingcharge'] = floatval($fpotDeatil['b2bsr_initialnetamount']) * $fpot_shippingpercent / 100;
            // $fpot['b2bsr_netamount'] = floatval($fpotDeatil['b2bsr_initialnetamount']) - floatval($fpot['b2bsr_gendiscount']) + floatval($fpot['b2bsr_shippingcharge']);
            //$fpot['b2bsr_effectiverate'] = $fpot['b2bsr_netamount'] / $fpotDeatil['b2bsr_totalqty'];
            $con = "b2bsr_UniqueID = '{$b2bsr_UniqueID}' and b2bsr_itemid = {$fpotDeatil['b2bsr_itemid']}";
            $status = $db->perform('retaline_B2B_SalesRequest_temp', $fpot, 'update', $con);
        }
        $adhocpo['adhoc_paymentTerms'] = $_POST['adhoc_paymentTerms'];
        $adhocpo['adhoc_paymentValue'] = $_POST['adhoc_paymentValue'];
        $adhocpo['adhoc_validityType'] = $_POST['adhoc_validityType'];
        $adhocpo['adhoc_shippingcharge'] = $_POST['adhoc_shippingcharge'];
        $adhocpo['adhoc_shippingchargeGST'] = $handlinchgGst;
        $adhocpo['adhoc_gdiscount'] = $_POST['adhoc_gdiscount'];
        $adhocpo['adhoc_gdiscounttype'] = $_POST['adhoc_gdiscounttype'];
        $adcon = " adhoc_uniqueid = '{$b2bsr_UniqueID}'";
        $adhocpo = array_filter($adhocpo, 'strlen');
        $status = $db->perform('retaline_B2B_SalesRequest_adhoc', $adhocpo, 'update', $adcon);
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
    case 'listSrDetailsStore':
        if (isset($_POST['filter'])) {
        $allowedFields = ['bbsr_id', 'bbsr_SRNumber', 'b2b_Customer_Name', 'b2b_Customer_ID', 'bbsr_SRUpdatedOn', 'status_id'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }
        $b2bsr_UniqueID = $_POST['b2bsr_UniqueID'];
        $countDataQuery = "SELECT count(*) from retaline_B2B_SalesRequest_temp where b2bsr_UniqueID = '{$b2bsr_UniqueID}' {$filter_qry} ";
        $listQuery = "SELECT  b2bsr_Customerid,b2bsr_itemid,b2bsr_itemname,b2bsr_itemmrp,b2bsr_itemqty,b2bsr_itemoffrqty,if(b2bsr_itemoffrqty > 0,CONCAT(b2bsr_itemqty,'+',b2bsr_itemoffrqty),b2bsr_totalqty) as b2bsr_totalqty,b2bsr_balanceqty,b2bsr_itemrate,b2bsr_itemaddidisc,b2bsr_effectiverate,"
                . "b2bsr_idiscountcalculs,b2bsr_netamount,b2bsr_amount,b2bsr_initialnetamount,b2bsr_gendiscount,b2bsr_shippingcharge,"
                . "IF(b2bsr_itemaddidisc > 0,(CONCAT(b2bsr_itemaddidisc,'',IF(b2bsr_idiscountcalculs = 'Amount',' Rs',' %'))),'') AS itemDisc from retaline_B2B_SalesRequest_temp where b2bsr_UniqueID = '{$b2bsr_UniqueID}' ORDER BY b2bsr_createdon ASC ";
        $db->printGridJson($countDataQuery, $listQuery);

        break;

    case 'saveSalesRequest':
// PO is for a CPD. Purchase and CPD module is only for CPD users
        $data = $_POST;
        $podata['b2b_Customer_ID'] = $data['customerId'];
        $podata['b2b_Customer_Name'] = $data['customerName'];
        if (!array_key_exists('bbsr_id', $data) || !empty($data['bbsr_id'])) {
            $podata['bbsr_SRNumber'] = $db->getItemFromDB("SELECT CONCAT('SO',DATE_FORMAT(CURDATE(),'%Y'), LPAD((COUNT(1)+1), 5, '0')) AS soNo FROM retaline_B2B_SalesRequest WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");
        }
        $podata['b2bsr_requestDate'] = date('Y-m-d');
        $podata['bbsr_SREnteredBy'] = $_SESSION['admin']->Finascop_UserId;
        $podata['bbsr_paymentTerms'] = $data['po_payment_terms'];
        $podata['bbsr_paymentValue'] = $data['po_payment_value'];
        $podata['bbsr_validityType'] = $data['fpo_validityType'];
        $podata['bbsr_validDate'] = date('Y-m-d', strtotime($podata['fpo_poDate'] . " + {$data['fpo_validityType']} days"));
        $podata['bbsr_srValue'] = $data['poValue'];
        $podata['bbsr_createdon'] = date("Y-m-d H:i:s");
        $podata['bbsr_SRUpdatedOn'] = date("Y-m-d H:i:s");
        $podata['bbsr_SREnteredBy'] = $_SESSION['admin']->Finascop_UserId;
        $podata['br_ID'] = $_SESSION['admin']->finascop_current_branch_id;
        switch ($data['po_gdiscountcalculs']) {
            case 'Amount':
                $gdispercent = floatval($data['fpot_generalDiscount']) * 100 / $data['total_initialofferamount'];
                break;
            case 'Percentage':
                $gdispercent = $data['fpot_generalDiscount'];
                break;
        }
        if ($gdispercent > 0) {
            $podata['bbsr_gdiscpercent'] = $gdispercent;
        }
        $podata['bbsr_shippingcharge'] = 0 + $data['fpot_shippingcharge'];
//CPD id for PO 
//        $podata['fpo_poDeliveryType'] = $data['po_delivery_datetype'];
//        $podata['fpo_poDeliveryDate'] = date('Y-m-d', strtotime($data['po_delivery_date']));

        $uniqueId = $data['uid'];
        $itemdetail = $db->getMultipleData("SELECT * FROM retaline_B2B_SalesRequest_temp WHERE b2bsr_UniqueID = '{$uniqueId}' ORDER BY b2bsr_createdon ASC", true);
        //print_r($itemdetail);
        $c = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_B2B_SalesRequest_temp WHERE b2bsr_UniqueID = '{$uniqueId}'");
        //print_r($c);
        if ($c > 0) {
            $db->query('begin');
            //print_r($podata);
            $podstatus = $db->perform('retaline_B2B_SalesRequest', $podata);
            $poId = $db->insert_id();

            $poValue = 0;
            for ($i = 0; $i < $c; $i++) {
                //foreach ($itemdetails as $itemdetail) {
                //print_r($itemdetail[$i]);
                $fpoddata['bbsr_id'] = $poId;
                $fpoddata['b2bsr_Customerid'] = $itemdetail[$i]['b2bsr_Customerid'];
                $fpoddata['b2bsr_itemid'] = $itemdetail[$i]['b2bsr_itemid'];
                $fpoddata['b2bsr_itemname'] = $itemdetail[$i]['b2bsr_itemname'];
                $fpoddata['b2bsr_itemmrp'] = $itemdetail[$i]['b2bsr_itemmrp'];
                $fpoddata['b2bsr_itemqty'] = $itemdetail[$i]['b2bsr_itemqty'];
                $fpoddata['b2bsr_itemoffrqty'] = $itemdetail[$i]['b2bsr_itemoffrqty'];
                $fpoddata['b2bsr_itemrate'] = $itemdetail[$i]['b2bsr_itemrate'];
                $fpoddata['b2bsr_itemaddidisc'] = $itemdetail[$i]['b2bsr_itemaddidisc'];
                $fpoddata['b2bsr_effectiverate'] = $itemdetail[$i]['b2bsr_effectiverate'];
                $fpoddata['b2bsr_notes'] = $itemdetail[$i]['b2bsr_notes'];
                $fpoddata['b2bsr_createdon'] = $itemdetail[$i]['b2bsr_createdon'];
                $fpoddata['b2bsr_createdby'] = $itemdetail[$i]['b2bsr_createdby'];
                $fpoddata['b2bsr_totalqty'] = $itemdetail[$i]['b2bsr_totalqty'];
                $fpoddata['b2bsr_balanceqty'] = $itemdetail[$i]['b2bsr_balanceqty'];
                $fpoddata['b2bsr_idiscountcalculs'] = $itemdetail[$i]['b2bsr_idiscountcalculs'];
                $fpoddata['b2bsr_initialnetamount'] = $itemdetail[$i]['b2bsr_initialnetamount'];
                $fpoddata['b2bsr_netamount'] = $itemdetail[$i]['b2bsr_netamount'];
                $fpoddata['b2bsr_amount'] = $itemdetail[$i]['b2bsr_amount'];
                $fpoddata['b2bsr_itemrateet'] = $itemdetail[$i]['b2bsr_itemrateet'];
                $fpoddata['b2bsr_itemcess'] = $itemdetail[$i]['b2bsr_itemcess'];
                $fpoddata['b2bsr_itemgst'] = $itemdetail[$i]['b2bsr_itemgst'];
                $fpoddata['b2bsr_netamountet'] = $itemdetail[$i]['b2bsr_netamountet'];
                $fpoddata['b2bsr_gendiscount'] = $itemdetail[$i]['b2bsr_gendiscount'];
                $fpoddata['b2bsr_shippingcharge'] = $itemdetail[$i]['b2bsr_shippingcharge'];
                $fpoddata['b2bsr_itemPkg'] = $itemdetail[$i]['b2bsr_itemPkg'];
//margin distribution on purchase order details 14/3/2020
                $fpoddata['bmd_id'] = $itemdetail[$i]['bmdd_id'];
                $fpoddata['bmd_margin'] = (float) $itemdetail[$i]['fpot_itemmrp'] - (float) $itemdetail[$i]['fpot_effectiverate'];
                $fpoddata['bmd_percent'] = $itemdetail[$i]['bmd_percent'];
                $itemPack = $db->getFromDB("SELECT ds_package_type_id,cos_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemdetail[$i]['b2bsr_itemid']}", true);
                if ($_SESSION['admin']->br_PyramidLevel == 2) {
                    $fpoddata['b2bsr_purchasingUnit'] = $itemPack['ds_package_type_id'];
                } else if ($_SESSION['admin']->br_PyramidLevel == 3) {
                    $fpoddata['b2bsr_purchasingUnit'] = $itemPack['cos_package_type_id'];
                }
                //
                $podstatus = $db->perform('retaline_B2B_SalesRequestDetails', $fpoddata);
            }
            $podstatus = $db->query("DELETE FROM retaline_B2B_SalesRequest_temp WHERE b2bsr_UniqueID = '{$uniqueId}'");
            $podstatus = $db->query("DELETE FROM retaline_B2B_SalesRequest_adhoc WHERE adhoc_uniqueid = '{$uniqueId}'");
            $podstatus = $db->query('commit');
            $msg = "'Sales Request saved .'";
        } else {
            $msg = "'Item details are not added. Please add details'";
        }
        if ($podstatus == 1) {
            //$msg = "' Sales Request saved.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {

            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'listB2BSalesRequests':
        $data = $_POST;

        $limit = is_numeric($data['limit']) ? $data['limit'] : 23;
        $start = is_numeric($data['start']) ? $data['start'] : 0;
        $_allowed_sort = ['bbsr_id', 'bbsr_SRUpdatedOn'];
        $sort = in_array(trim($data['sort'] ?? ''), $_allowed_sort) ? trim($data['sort']) : 'bbsr_id';
        $dir = (strtoupper(trim($data['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';

        $filter_qry = " WHERE status_id = 1 ";
        if (!empty($data['CustomerID'])) {
            $filter_qry .= " AND b2b_Customer_ID = {$data['CustomerID']}";
        }
        if (isset($data['filter'])) {
            $filter = $data['filter'];
            foreach ($filter as $key => $val) {
                switch ($val['data']['type']) {
                    case 'string':
                        $filter_qry .= " AND " . $val['field'] . "  LIKE  '" . $val['data']['value'] . "%'";
                        break;
                }
            }
        }

        if ($_SESSION['admin']->br_PyramidLevel == 1) {
            $filter_qry .= " ";
            /*if ($_SESSION['admin']->IsSuperUser == 'Yes') {
                $filter_qry .= " ";
            } else {
                $filter_qry .= " AND br_ID = {$_SESSION['admin']->finascop_current_branch_id} ";
            }*/
        } else {
            $filter_qry .= " AND br_ID = {$_SESSION['admin']->finascop_current_branch_id} ";
        }
        $countDataQuery = "SELECT count(1) from retaline_B2B_SalesRequest {$filter_qry} ";
        $listQuery = "SELECT bbsr_id, bbsr_SRNumber, b2bsr_requestDate,b2b_Customer_ID,b2b_Customer_Name,"
                . "IF((status_id = 1),'Active','Inactive')AS bbsr_Active "
                . "FROM retaline_B2B_SalesRequest {$filter_qry} GROUP BY bbsr_id ORDER BY {$sort} {$dir} LIMIT {$start},{$limit}";
        $db->printGridJson($countDataQuery, $listQuery);
        break;
    case 'getSRDetails':
        $poId = $_POST['bbsr_id'];
        $podata = $db->getFromDB("SELECT  bbsr_id,bbsr_SRNumber,b2b_Customer_ID,b2b_Customer_Name,bbsr_SRUpdatedOn,DATE_FORMAT(b2bsr_requestDate,'%d-%m-%Y') as b2bsr_requestDate,bbsr_paymentTerms,bbsr_SREnteredBy,bbsr_srValue,bbsr_paymentValue,"
                . "CONCAT(bbsr_gdiscpercent,'','%') as bbsr_gdiscpercent,CONCAT(bbsr_validityType,'',' days') as bbsr_validityType,bbsr_shippingcharge  FROM retaline_B2B_SalesRequest where bbsr_id = {$poId}", true);
        if (!empty($podata)) {
            echo json_encode($podata);
        }
        break;
    case 'listSrItemsStore':
        if (isset($_POST['filter'])) {
        $allowedFields = ['bbsr_id', 'bbsr_SRNumber', 'b2b_Customer_Name', 'b2b_Customer_ID', 'bbsr_SRUpdatedOn', 'status_id'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }
        $bbsr_id = $_POST['bbsr_id'];
        $countDataQuery = "SELECT count(*) from retaline_B2B_SalesRequestDetails where bbsr_id = '{$bbsr_id}' {$filter_qry} ";
        $listQuery = "SELECT  b2bsr_Customerid,b2bsr_itemid,b2bsr_itemname,b2bsr_itemmrp,b2bsr_itemqty,b2bsr_itemoffrqty,if(b2bsr_itemoffrqty > 0,CONCAT(b2bsr_itemqty,'+',b2bsr_itemoffrqty),b2bsr_totalqty) as b2bsr_totalqty,b2bsr_balanceqty,b2bsr_itemrate,b2bsr_itemaddidisc,b2bsr_effectiverate,"
                . "b2bsr_idiscountcalculs,b2bsr_netamount,b2bsr_amount,b2bsr_initialnetamount,b2bsr_gendiscount,b2bsr_shippingcharge,"
                . "IF(b2bsr_itemaddidisc > 0,(CONCAT(b2bsr_itemaddidisc,'',IF(b2bsr_idiscountcalculs = 'Amount',' Rs',' %'))),'') AS itemDisc from retaline_B2B_SalesRequestDetails where bbsr_id = '{$bbsr_id}'  ORDER BY b2bsr_createdon ASC ";
        $db->printGridJson($countDataQuery, $listQuery);

        break;
    case 'deleteB2BItemFromSR':
        $itemid = $_POST['itemid'];
        $uid = $_POST['uid'];
        $db->query('begin');
        if ($uid == 0) {
            $delquery = "DELETE FROM retaline_B2B_SalesRequestDetails  WHERE bbsr_id = " . intval($_POST['srId']) . " AND b2bsr_itemid = {$itemid}";
        } else {
            $delquery = "DELETE FROM retaline_B2B_SalesRequest_temp  WHERE b2bsr_UniqueID = '{$uid}' AND b2bsr_itemid = {$itemid}";
        }

        $status = $db->query($delquery);

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Deleted Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while deleting data' }}";
        }
        break;
    case 'convertToB2BSalesOrder':
        $bbsr_id = $_POST['bbsr_id'];
        $db->query('begin');
        $podata['b2bsr_requestDate'] = date('Y-m-d');
        $podata['bbsr_paymentTerms'] = $_POST['srpaymentTerms'];
        $podata['bbsr_paymentValue'] = $_POST['srpaymentValue'];
        $podata['bbsr_validityType'] = $_POST['srvalidityType'];
        $podata['bbsr_validDate'] = date('Y-m-d', strtotime($podata['b2bsr_requestDate'] . " + {$podata['bbsr_validityType']} days"));
        $podata['bbsr_srValue'] = $_POST['soValue'];
        $podata['bbsr_SRUpdatedOn'] = date("Y-m-d H:i:s");
        $podata['bbsr_SREnteredBy'] = $_SESSION['admin']->Finascop_UserId;
        $srItemDetails = $db->getMultipleData("SELECT * FROM retaline_B2B_SalesRequestDetails WHERE bbsr_id = {$bbsr_id}", true);
        $srDetails = $db->getFromDB("SELECT * FROM retaline_B2B_SalesRequest WHERE bbsr_id = {$bbsr_id}", true);
        $message = "";
        $countMisMatch = 0;
        foreach ($srItemDetails as $srItemDeta) {
            // $itemHistory['skuMRP'] = $prices['stiid_leastSKUmrp'] * $itemHistory['cos_nos'] * $itemHistory['ds_nos'];
            $itemData = $db->getFromDB("SELECT cos_nos,ds_nos FROM finascop_stock_itemmaster WHERE stit_ID = {$srItemDeta['b2bsr_itemid']}", true);
            //echo "b2bsr_itemmrp".$srItemDeta['b2bsr_itemmrp'];
            if ($_SESSION['admin']->br_PyramidLevel == 2) {
                $stiid_leastSKUmrp = $srItemDeta['b2bsr_itemmrp'] / ($itemData['cos_nos'] * $itemData['ds_nos']);
            } else if ($_SESSION['admin']->br_PyramidLevel == 3) {
                $stiid_leastSKUmrp = $srItemDeta['b2bsr_itemmrp'] / $itemData['cos_nos'];
            }
//echo '$stiid_leastSKUmrp'.$stiid_leastSKUmrp;
            $mrpItemCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_item_inventorydetails "
                    . "WHERE stiid_itemmasterid = {$srItemDeta['b2bsr_itemid']} AND stiid_leastSKUmrp = {$stiid_leastSKUmrp} AND cpd_branch_id = {$srDetails['br_ID']} AND stiid_status IN (1,4) ORDER BY stiid_expirydate asc", true);


            $itemCount = $db->getItemFromDB("SELECT item_count FROM finascop_stock_branch_inventory WHERE stit_id = {$srItemDeta['b2bsr_itemid']} AND branch_id = {$srDetails['br_ID']}");
            if ($itemCount >= $srItemDeta['b2bsr_itemqty'] && $mrpItemCount >= $srItemDeta['b2bsr_itemqty']) {
                $countStatus = 0;
            } else {
                $message .= "{$srItemDeta['b2bsr_itemname']} with MRP:{$srItemDeta['b2bsr_itemmrp']} has only {$mrpItemCount} Nos. and have total {$itemCount} Nos.";
                $countStatus = 1;
            }
            $countMisMatch = $countMisMatch + $countStatus;
        }
        if ($countMisMatch > 0) {
            echo '{"success":false,"msg":' . $message . '}';
            exit();
        }
        $status = $db->perform('retaline_B2B_SalesRequest', $podata, 'update', " bbsr_id = {$bbsr_id}");



        $soDetails['b2b_Customer_ID'] = $srDetails['b2b_Customer_ID'];
        $soDetails['b2b_Customer_Name'] = $srDetails['b2b_Customer_Name'];
        $soDetails['bbso_SONumber'] = $db->getItemFromDB("SELECT CONCAT('SO',DATE_FORMAT(CURDATE(),'%Y'),LPAD((COUNT(1)+1), 6, '0')) AS soNo FROM retaline_B2B_SalesOrder");
        //$soDetails['bbso_SONumber'] = $srDetails['bbsr_SRNumber'];
        $soDetails['bbso_SODate'] = $srDetails['bbsr_createdon'];
        $soDetails['bbso_SOOrderedby'] = $_SESSION['admin']->Finascop_UserId;
        $soDetails['bbso_SOValue'] = $srDetails['bbsr_srValue'];
        $soDetails['bbso_HandlingCharges'] = $srDetails['bbsr_shippingcharge'];
        $soDetails['status_id'] = 2;
        $soDetails['br_ID'] = $srDetails['br_ID'];
        $soDetails['bbso_createdon'] = date("Y-m-d H:i:s");
        $soDetails['bbso_paymentTerms'] = $srDetails['bbsr_paymentTerms'];
        $soDetails['bbso_paymentValue'] = $srDetails['bbsr_paymentValue'];
        $soDetails['bbso_validityType'] = $srDetails['bbsr_validityType'];
        $soDetails['bbso_gdiscpercent'] = $srDetails['bbsr_gdiscpercent'];
        $soDetails['bbso_validDate'] = $srDetails['bbsr_validDate'];


        $status = $db->perform('retaline_B2B_SalesOrder', $soDetails);
        $bbso_id = $db->insert_id();

        $srDatat['bbso_id'] = $bbso_id;
        $srDatat['status_id'] = 0;
        $srDatat['bbsr_SRUpdatedOn'] = date("Y-m-d H:i:s");
        $srDatat['bbsr_SRUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('retaline_B2B_SalesRequest', $srDatat, 'update', " bbsr_id = {$bbsr_id}");

        foreach ($srItemDetails as $srItemDetail) {
            $gst_details = $db->getFromDB("SELECT fbi.stit_HSNCode AS b2bso_HSN,
                 (({$srItemDetail['b2bsr_amount']} * 100) / (100 + fbi.stit_GST)) AS b2bso_amount_btax,
                  (fbi.stit_GST / 2) AS b2bso_cgst_percent,(fbi.stit_GST / 2) AS b2bso_sgst_percent,
                  ({$srItemDetail['b2bsr_itemgst']}/2) AS b2bso_cgst_value,
                  ({$srItemDetail['b2bsr_itemgst']}/2) AS b2bso_sgst_value
                  FROM finascop_stock_itemmaster fbi WHERE fbi.stit_id = {$srItemDetail['b2bsr_itemid']} ", true);
            $gstItemRate = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID ={$srItemDetail['b2bsr_itemid']}");
            $fsbg_id = $db->getItemFromDB("SELECT fsbg_id from finascop_stock_branch_inventory where stit_id = {$srItemDetail['b2bsr_itemid']} AND branch_id = {$srDetails['br_ID']}");
            $soItemDetail['bbso_id'] = $bbso_id;
            $soItemDetail['b2bso_itemid'] = $srItemDetail['b2bsr_itemid'];
            $soItemDetail['b2bso_itemname'] = $srItemDetail['b2bsr_itemname'];
            $soItemDetail['b2bso_HSN'] = $gst_details['b2bso_HSN'];
            $soItemDetail['b2bso_itemmrp'] = $srItemDetail['b2bsr_itemmrp'];
            $soItemDetail['b2bso_itemrate'] = $srItemDetail['b2bsr_itemrate'];
            $soItemDetail['b2bso_itemPkg'] = $srItemDetail['b2bsr_itemPkg'];
            $soItemDetail['b2bso_itemqty'] = $srItemDetail['b2bsr_itemqty'];
            $soItemDetail['b2bso_gst'] = $gstItemRate;
            $soItemDetail['b2bso_cgst_percent'] = $gst_details['b2bso_cgst_percent'];
            $soItemDetail['b2bso_cgst_value'] = $gst_details['b2bso_cgst_value'];
            $soItemDetail['b2bso_sgst_percent'] = $gst_details['b2bso_sgst_percent'];
            $soItemDetail['b2bso_sgst_value'] = $gst_details['b2bso_sgst_value'];
            $soItemDetail['b2bso_amount_btax'] = $gst_details['b2bso_amount_btax'];
            $soItemDetail['b2bso_amount'] = $srItemDetail['b2bsr_amount'];
            $soItemDetail['b2bso_netamount'] = $srItemDetail['b2bsr_netamount'];
            $soItemDetail['b2bso_createdon'] = date("Y-m-d H:i:s");
            $soItemDetail['b2bso_createdby'] = $_SESSION['admin']->Finascop_UserId;
            $soItemDetail['fsbg_id'] = $fsbg_id;
            $soItemDetail['b2bso_purchasingUnit'] = $srItemDetail['b2bsr_purchasingUnit'];
            $soItemDetail['b2bso_totalqty'] = $srItemDetail['b2bsr_totalqty'];
            $soItemDetail['b2bso_balanceqty'] = $srItemDetail['b2bsr_balanceqty'];
            $soItemDetail['b2bso_itemoffrqty'] = $srItemDetail['b2bsr_itemoffrqty'];
            $soItemDetail['b2bso_itemaddidisc'] = $srItemDetail['b2bsr_itemaddidisc'];
            $soItemDetail['b2bso_idiscountcalculs'] = $srItemDetail['b2bsr_idiscountcalculs'];
            $soItemDetail['b2bso_notes'] = $srItemDetail['b2bsr_notes'];
            $soItemDetail['b2bso_effectiverate'] = $srItemDetail['b2bsr_effectiverate'];
            $soItemDetail['actual_marginDistri'] = $srItemDetail['actual_marginDistri'];
            $soItemDetail['b2bso_gendiscount'] = $srItemDetail['b2bsr_gendiscount'];
            $soItemDetail['b2bso_shippingcharge'] = $srItemDetail['b2bsr_shippingcharge'];
            $soItemDetail['bmd_percent'] = $srItemDetail['bmd_percent'];
            $soItemDetail['bmd_id'] = $srItemDetail['bmd_id'];
            $soItemDetail['bmd_margin'] = $srItemDetail['bmd_margin'];
            $soItemDetail['b2bso_itemrateet'] = $srItemDetail['b2bsr_itemrateet'];
            $soItemDetail['b2bso_itemcess'] = $srItemDetail['b2bsr_itemcess'];
            $soItemDetail['b2bso_itemgst'] = $srItemDetail['b2bsr_itemgst'];
            $soItemDetail['b2bso_netamountet'] = $srItemDetail['b2bsr_netamountet'];

            //print_r($soItemDetail);
            $status = $db->perform('retaline_B2B_SalesOrderDetails', $soItemDetail);
        }

        $query = "SELECT b2b_Customer_ID,br_ID,bbso_HandlingCharges,bbso_CGSTVal,bbso_SGSTVal,bbso_InvValBtax,bbso_InvValBtax,bbso_SOValue
        FROM retaline_B2B_SalesOrder WHERE bbso_id = {$bbso_id}";
        $B2BSOData = $db->getFromDB($query, true);
        $listQuery = "SELECT b2bso_itemid,b2bso_itemqty,b2bso_itemmrp,b2bso_gst,b2bso_cgst_percent,b2bso_sgst_percent,
        b2bso_cgst_value,b2bso_sgst_value,b2bso_amount_btax,b2bso_amount,b2bso_netamount,b2bso_totalqty
         FROM retaline_B2B_SalesOrderDetails WHERE bbso_id = {$bbso_id}";
        $itemDetails = $db->getMultipleData($listQuery, true);
        $date = date('Y-m-d H:i:s');


        $data['fsto_source'] = $B2BSOData['br_ID'];
        $data['fsto_destination'] = $B2BSOData['b2b_Customer_ID'];
        $data['fsto_handlingcharge'] = $B2BSOData['bbso_HandlingCharges'];
        $data['fsto_cgstval'] = round($B2BSOData['bbso_CGSTVal'], 2);
        $data['fsto_sgstval'] = round($B2BSOData['bbso_SGSTVal'], 2);
        $data['fsto_amtbeforetax'] = round($B2BSOData['bbso_InvValBtax'], 2);
        $data['fsto_amtaftertax'] = round($B2BSOData['bbso_InvValBtax'], 2);
        $data['fsto_netamount'] = round($B2BSOData['bbso_SOValue'], 2);

        $data['fsto_sourcetype'] = 1;
        $data['fsto_destinationtype'] = 2;
        $tdy = date("Y-m-d") . " 00:00:00";
        $maxId = $db->getItemFromDB("select right(fsto_uid,3)*1 as fsto_uid  from `finascop_stock_transfer_order` where `fsto_source` = {$data['fsto_source']} and `fsto_createdOn` between '{$tdy}' and '{$date}' order by `fsto_id` desc limit 0,1");
        $br_key = $db->getItemFromDB("SELECT br_key FROM finascop_branch WHERE br_ID={$data['fsto_source']}");
        $uid_max = getNewTONumber($data['fsto_source']);
        $data['fsto_uid'] = $uid_max;
        $data['fsto_ordertype'] = 2;
        $data['fsto_type'] = 0;
        $data['fstr_id'] = $bbso_id;
        $data['fsto_createdOn'] = $date;
        $data['fsto_initiatedBy'] = $_SESSION['admin']->finascop_current_branch_id;
        $data['fsto_createdBy'] = $_SESSION['admin']->UserId;
        $data['fsto_updateon'] = $date;
        $data['fsto_updateby'] = $_SESSION['admin']->UserId;
        $data['fsto_status'] = 6;

        $status = $db->perform('finascop_stock_transfer_order', $data);
        $lastId = $db->insert_id();

        if ($lastId) {

            foreach ($itemDetails as $key => $item) {
                $fstro_gst_value = $item['b2bso_cgst_value'] + $item['b2bso_sgst_value'];
                $trODetailss['fsto_ItemId'] = $item['b2bso_itemid'];
                $trODetailss['fsto_ItemQty'] = $item['b2bso_totalqty']; //$item['b2bso_itemqty']
                $trODetailss['fstro_ItemMRP'] = $item['b2bso_itemmrp'];
                $trODetailss['fstro_gst_percent'] = round($item['b2bso_gst'], 2);
                $trODetailss['fstro_gst_value'] = round($fstro_gst_value, 2);
                $trODetailss['fstro_cgst_percent'] = round($item['b2bso_cgst_percent'], 2);
                $trODetailss['fstro_sgst_percent'] = round($item['b2bso_sgst_percent'], 2);
                $trODetailss['fstro_cgst_value'] = round($item['b2bso_cgst_value'], 2);
                $trODetailss['fstro_sgst_value'] = round($item['b2bso_sgst_value'], 2);
                $trODetailss['fstro_totamtbeforetax'] = round($item['b2bso_amount_btax'], 2);
                $trODetailss['fstro_totamtaftertax'] = round($item['b2bso_amount'], 2);
                $trODetailss['fstro_kfc_percent'] = 0;
                $trODetailss['fstro_kfc_value'] = 0;

                $items = $db->getFromDb("SELECT item_weight,stit_item_volume 
                FROM finascop_stock_itemmaster where stit_itemId = {$trODetailss['fsto_ItemId']}", true);
                $trODetailss['fsto_ItemWeight'] = $items['item_weight'] * $trODetailss['fsto_ItemQty'];
                $trODetailss['fsto_ItemVolume'] = $items['stit_item_volume'] * $trODetailss['fsto_ItemQty'];
                $trODetailss['fsto_uid'] = $data['fsto_uid'];
                $trODetailss['fsto_id'] = $lastId;

                $trODetailss['fstro_createdBy'] = $_SESSION['admin']->UserId;
                $trODetailss['fstro_createdOn'] = $date;

                $status = $db->perform('finascop_stock_transfer_order_details', $trODetailss);
            }
        }

        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "' Sales Order Created.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'getSalesRequestAdhoc':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 12;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['bbsr_id', 'bbsr_SRUpdatedOn'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'bbsr_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'fpot_createdon' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;

        $filter_qry = " WHERE 1 = 1 AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}";
        if (isset($_POST['filter'])) {
        $allowedFields = ['bbsr_id', 'bbsr_SRNumber', 'b2b_Customer_Name', 'b2b_Customer_ID', 'bbsr_SRUpdatedOn', 'status_id'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }
//        else {
//            $filter_qry .= " and (fpo_Active = 1) ";
//        }
        $date = date('Y-m-d');
        $countDataQuery = "SELECT count(*) from retaline_B2B_SalesRequest_adhoc fp  {$filter_qry}  ORDER BY adhoc_createdon DESC";
        $listQuery = "SELECT  adhoc_uniqueid ,adhoc_customer,adhoc_createdon,adhoc_createdby,adhoc_srValue,(SELECT b2b_Customer_Name FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = adhoc_customer) as customerName
 FROM retaline_B2B_SalesRequest_adhoc fp  {$filter_qry} ORDER BY adhoc_createdon DESC LIMIT $start,$limit ";
        $db->printGridJson($countDataQuery, $listQuery);
        break;
    case 'loadAdhocSalesRequest':
        $type = $_POST['type'];
        if ($type == 'SR') {
            $podata = $db->getFromSafe("SELECT bbsr_SRNumber, bbsr_id as adhoc_uniqueid ,DATE_FORMAT(bbsr_createdon,'%d-%m-%Y %H:%i:%s') as adhoc_createdon,bbsr_SREnteredBy as adhoc_createdby,bbsr_SRUpdatedOn as adhoc_updatedon,
            bbsr_srValue as adhoc_srValue,bbsr_paymentTerms as adhoc_paymentTerms,bbsr_paymentValue as adhoc_paymentValue,bbsr_validityType as adhoc_validityType,bbsr_shippingcharge as adhoc_shippingcharge,bbsr_gdiscpercent as adhoc_gdiscount,'Percentage' as adhoc_gdiscounttype,
            (SELECT b2b_Customer_Name FROM retaline_B2Bcustomer rb WHERE rb.b2b_Customer_ID = fp.b2b_Customer_ID) as customerName,b2b_Customer_ID as adhoc_customer 
 FROM retaline_B2B_SalesRequest fp  {$filter_qry} where bbsr_id = ?", "s", [$_POST['uniqueid']], true);
        } else {
            $podata = $db->getFromSafe("SELECT  adhoc_uniqueid ,DATE_FORMAT(adhoc_createdon,'%d-%m-%Y %H:%i:%s') as adhoc_createdon,adhoc_createdby,adhoc_updatedon,
            adhoc_srValue,adhoc_paymentTerms,adhoc_paymentValue,adhoc_validityType,adhoc_shippingcharge,adhoc_gdiscount,adhoc_gdiscounttype,
            (SELECT b2b_Customer_Name FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = adhoc_customer) as customerName,adhoc_customer 
 FROM retaline_B2B_SalesRequest_adhoc fp  {$filter_qry} where adhoc_uniqueid = ?", "s", [$_POST['uniqueid']], true);
        }

        if (!empty($podata)) {
//            $fpot_assigneddby = $db->getItemSafe("SELECT fpot_assigneddby FROM finascop_purchase_order_temp WHERE fpot_uniqueid = ? GROUP BY fpot_uniqueid", "i", [$_POST['uniqueid']]);
//            if ($fpot_assigneddby > 0) {
//                $podata['assigneddby'] = $fpot_assigneddby;
//            }
            echo json_encode($podata);
        }
        break;
    case 'deleteAdhocDetails':
        $db->query('begin');
        $del_query = "DELETE FROM retaline_B2B_SalesRequest_adhoc WHERE adhoc_uniqueid='{$_POST['adhoc_uniqueid']}'";
        $temp = $db->query($del_query);
        if ($temp) {
            $del_query = "DELETE FROM retaline_B2B_SalesRequest_temp WHERE b2bsr_UniqueID='{$_POST['adhoc_uniqueid']}'";
            $db->query($del_query);
        }
        $status = $db->query('commit');
        if (status) {
            echo "{success:true,valid:true,message:'Deleted Succesfully ' }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Deleteing.'}";
        }
        break;
    case 'listSrDetailsStoreNew':
        if (isset($_POST['filter'])) {
        $allowedFields = ['bbsr_id', 'bbsr_SRNumber', 'b2b_Customer_Name', 'b2b_Customer_ID', 'bbsr_SRUpdatedOn', 'status_id'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }
        $b2bsr_UniqueID = $_POST['b2bsr_UniqueID'];
        $countDataQuery = "SELECT count(*) from retaline_B2B_SalesRequestDetails where bbsr_id = '{$b2bsr_UniqueID}' {$filter_qry} ";
        $listQuery = "SELECT  bbsr_id,b2bsr_Customerid,b2bsr_itemid,b2bsr_itemname,b2bsr_itemmrp,b2bsr_itemqty,b2bsr_itemoffrqty,if(b2bsr_itemoffrqty > 0,CONCAT(b2bsr_itemqty,'+',b2bsr_itemoffrqty),b2bsr_totalqty) as b2bsr_totalqty,b2bsr_balanceqty,b2bsr_itemrate,b2bsr_itemaddidisc,b2bsr_effectiverate,"
                . "b2bsr_idiscountcalculs,b2bsr_netamount,b2bsr_amount,b2bsr_initialnetamount,b2bsr_gendiscount,b2bsr_shippingcharge,"
                . "IF(b2bsr_itemaddidisc > 0,(CONCAT(b2bsr_itemaddidisc,'',IF(b2bsr_idiscountcalculs = 'Amount',' Rs',' %'))),'') AS itemDisc from retaline_B2B_SalesRequestDetails where bbsr_id = '{$b2bsr_UniqueID}' ORDER BY b2bsr_createdon ASC ";
        $db->printGridJson($countDataQuery, $listQuery);

        break;
    case 'saveB2BSRItemDetailsNew':

        $adhocCount = $db->getItemSafe("SELECT COUNT(*) FROM retaline_B2B_SalesRequest WHERE bbsr_id = ? AND bbsr_SRUpdatedOn = '{$_POST['sradhoc_updatedon']}'", "i", [$_POST['b2bsr_UniqueID']]);
//        if ($_POST['sradhoc_updatedon'] != '' && $adhocCount == 0) {
//            echo '{"success":false,"msg":"Reload data updation is going on."}';
//            exit();
//        }
        $db->query('begin');
        if ($adhocCount > 0) {
            $updatedDate = $db->getItemSafe("SELECT bbsr_SRUpdatedOn FROM retaline_B2B_SalesRequest WHERE bbsr_id = ?", "s", [$_POST['b2bsr_UniqueID']]);
            if ($updatedDate == $_POST['sradhoc_updatedon']) {
                $adhocData['bbsr_SRUpdatedOn'] = date("Y-m-d H:i");
                $adhocData['bbsr_SRUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('retaline_B2B_SalesRequest', $adhocData, 'update', " bbsr_id = '{$_POST['b2bsr_UniqueID']}'");
            } else {
                echo '{"success":false,"msg":"Reload data updation is going on."}';
                exit();
            }
        }
        $haveGST = $db->getItemSafe("SELECT b2b_Customer_gst FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = ?", "i", [$_POST['b2bsr_Customerid']]);

        //$data['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
        $data['bbsr_id'] = $_POST['b2bsr_UniqueID'];
        $data['b2bsr_Customerid'] = $_POST['b2bsr_Customerid'];
        $data['b2bsr_itemid'] = $_POST['b2bsr_itemid'];
        $data['b2bsr_itemname'] = $_POST['b2bsr_itemname'];
        $data['b2bsr_itemmrp'] = $_POST['b2bsr_itemmrp'];
        $data['b2bsr_itemqty'] = $_POST['b2bsr_itemqty'];
        $data['b2bsr_itemoffrqty'] = $_POST['b2bsr_itemoffrqty'];
        $data['b2bsr_itemrate'] = $_POST['b2bsr_itemrate'];
        $data['b2bsr_itemaddidisc'] = $_POST['b2bsr_itemaddidisc'];
        $data['b2bsr_amount'] = $_POST['b2bsr_amount'];
        $b2bsr_netamount = $_POST['b2bsr_netamount'];
        $data['b2bsr_netamountet'] = $b2bsr_netamount;
        $stit_GSTAmt = $b2bsr_netamount * $_POST['stit_GST'] / 100;
        $stit_GSTAmt = round($stit_GSTAmt, 2);
        if ($haveGST == '') {
            $cessAmt = $b2bsr_netamount / 100;
            $cessAmt = round($cessAmt, 2);
        } else {
            $cessAmt = 0;
        }
        $data['b2bsr_itemgst'] = $stit_GSTAmt;
        $data['b2bsr_itemcess'] = $cessAmt;
        $data['b2bsr_netamount'] = $_POST['b2bsr_netamount'] + $cessAmt + $stit_GSTAmt;
        $data['b2bsr_initialnetamount'] = $_POST['b2bsr_netamount'];
        $data['b2bsr_idiscountcalculs'] = $_POST['b2bsr_idiscountcalculs'];
        $data['b2bsr_createdon'] = date("Y-m-d H:i:s");
        $data['b2bsr_createdby'] = $_SESSION['admin']->Finascop_UserId;
        $data['b2bsr_totalqty'] = (int) $data['b2bsr_itemoffrqty'] + (int) $data['b2bsr_itemqty'];
        $data['b2bsr_balanceqty'] = $data['b2bsr_totalqty'];
        $data['b2bsr_effectiverate'] = $data['b2bsr_netamount'] / $data['b2bsr_totalqty'];
        $data['b2bsr_itemPkg'] = $_POST['b2bsr_itemPkg'];
//var fpot_itemoffrrateet = (parseFloat(fpot_itemoffrrate) * 100) / (100 + parseFloat(taxRate));
        $taxRate = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$data['b2bsr_itemid']}");
        $data['b2bsr_notes'] = $_POST['b2bsr_notes'];

//for margin distributions
        $data['b2bsr_itemrateet'] = $_POST['b2bsr_itemrateet'];
        (float) $eprbft = ((float) $data['b2bsr_effectiverate'] / (100 + (float) $taxRate)) * 100;
        (float) $mrpbft = ((float) $data['b2bsr_itemmrp'] / (100 + (float) $taxRate)) * 100;
        $actmarginDistriPercent = 100 - (($eprbft / $mrpbft) * 100);
        $marginDistriPercent = round($actmarginDistriPercent);
        $data['actual_marginDistri'] = $actmarginDistriPercent;
        $data['bmd_percent'] = $marginDistriPercent;

        $itemMargin = $db->getFromDB("SELECT rmim_bmd_id from retaline_margin_item_mapping where rmim_stit_id = {$data['b2bsr_itemid']}");
        if ($itemMargin > 0) {
            $marginDistri = $itemMargin;
        } else {
            $marginDistri = $db->getItemFromDB("SELECT bmd_id from retaline_margindistributionsb2b where is_default = 1");
        }

        $data['bmd_id'] = $marginDistri;

        $dup = $db->getItemSafe("SELECT COUNT(1) FROM retaline_B2B_SalesRequestDetails WHERE bbsr_id = ? and b2bsr_itemid = {$data['b2bsr_itemid']}", "i", [$_POST['b2bsr_UniqueID']]);
        if ($dup > 0) {
            $con = "bbsr_id = {$_POST['b2bsr_UniqueID']} and b2bsr_itemid = {$data['b2bsr_itemid']}";
            $status = $db->perform('retaline_B2B_SalesRequestDetails', $data, 'update', $con);
        } else {
            $status = $db->perform('retaline_B2B_SalesRequestDetails', $data);
        }
        $newupdatedDate = $db->getItemSafe("SELECT bbsr_SRUpdatedOn FROM retaline_B2B_SalesRequest WHERE bbsr_id = ?", "s", [$_POST['b2bsr_UniqueID']]);
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'B2B Sales Request Item saved successfully.'";
            echo '{"success":true,"date":"' . $newupdatedDate . '","msg":' . $msg . '}';
        } else {
            $msg = "'Error while saving .'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'saveSalesRequestinSR':
        $data = $_POST;
        $uniqueId = $data['b2bsrid'];
        $podata['bbsr_srValue'] = $data['poValue'];
        $podata['bbsr_SRUpdatedOn'] = date("Y-m-d H:i:s");
        $podata['bbsr_SRUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $db->query('begin');
        $status = $db->perform('retaline_B2B_SalesRequest', $podata, 'update', " bbsr_id = {$uniqueId} ");
        $status = $db->query('commit');

        if ($status == 1) {
            $msg = "' Sales Request saved.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {

            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'soGenDiscounCalculate':
        $bbsr_id = $_POST['bbsr_id'];
        $b2bsr_UniqueID = $_POST['b2bsr_UniqueID'];
        $total_initialnetamount = $_POST['total_initialnetamount'];

        if ($_POST['fpot_gdiscpercent'] > 0) {
            $fpot_gdiscpercent = $_POST['fpot_gdiscpercent'];
        } else {
            $fpot_gdiscpercent = 0;
        }

        $fpot_shippingpercent = $_POST['fpot_hshippingcharge'];
        $fpotDeatils = $db->getMultipleData("SELECT b2bsr_itemid,b2bsr_netamount,b2bsr_amount,b2bsr_initialnetamount,b2bsr_totalqty,b2bsr_effectiverate,b2bsr_itemrateet,b2bsr_itemaddidisc,b2bsr_idiscountcalculs,"
                . "b2bsr_totalqty,b2bsr_itemqty FROM retaline_B2B_SalesRequestDetails where bbsr_id = {$bbsr_id} ", true);
        $handlinchgGst = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'HANDLING_CHRG_GST'");
        $handlinchgGst = $_POST['adhoc_shippingcharge'] * $handlinchgGst / 100;
        $db->query('begin');
        foreach ($fpotDeatils as $fpotDeatil) {
            $itemGST = $db->getItemFRomDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$fpotDeatil['b2bsr_itemid']}");
            if ($fpotDeatil['fpot_idiscountcalculs'] == 'Amount') {
                $netOfferRete = $fpotDeatil['b2bsr_itemrateet'] - $fpotDeatil['b2bsr_itemaddidisc'];
            } else {
                $netOfferRete = $fpotDeatil['b2bsr_itemrateet'] - ($fpotDeatil['b2bsr_itemrateet'] * $fpotDeatil['b2bsr_itemaddidisc'] / 100);
            }
            $netOfferRete = round($netOfferRete, 2);
            $generalDiscount = round($netOfferRete * $fpot_gdiscpercent / 100, 2);
            $finalOfferRate = round(($netOfferRete - $generalDiscount), 2);
            $fpot['b2bsr_gendiscount'] = $generalDiscount;
            $fpot_shippingcharge = $fpotDeatil['b2bsr_initialnetamount'] * $fpot_shippingpercent / 100;
            $fpot['b2bsr_shippingcharge'] = round($fpot_shippingcharge, 2);
            $fpot_netamount = $finalOfferRate * $fpotDeatil['b2bsr_itemqty'];
            $fpot['b2bsr_itemgst'] = $fpot_netamount * $itemGST / 100;
            $fpot['b2bsr_netamountet'] = $fpot_netamount;
            $fpot['b2bsr_netamount'] = $fpot_netamount + $fpot['b2bsr_itemgst'];
            $fpot_effectiverate = ($fpot['b2bsr_netamount'] + $fpot['b2bsr_shippingcharge'] + $handlinchgGst) / $fpotDeatil['b2bsr_totalqty'];
            $fpot['b2bsr_effectiverate'] = round($fpot_effectiverate, 2);

            //$fpot['b2bsr_gendiscount'] = floatval($fpotDeatil['b2bsr_initialnetamount']) * $fpot_gdiscpercent / 100;
            //$fpot['b2bsr_shippingcharge'] = floatval($fpotDeatil['b2bsr_initialnetamount']) * $fpot_shippingpercent / 100;
            // $fpot['b2bsr_netamount'] = floatval($fpotDeatil['b2bsr_initialnetamount']) - floatval($fpot['b2bsr_gendiscount']) + floatval($fpot['b2bsr_shippingcharge']);
            //$fpot['b2bsr_effectiverate'] = $fpot['b2bsr_netamount'] / $fpotDeatil['b2bsr_totalqty'];
            $b2bsr_netamount += $fpot['b2bsr_netamount'];
            $con = "bbsr_id = {$bbsr_id} and b2bsr_itemid = {$fpotDeatil['b2bsr_itemid']}";
            $status = $db->perform('retaline_B2B_SalesRequestDetails', $fpot, 'update', $con);
        }
        $b2bsr_requestDate = $db->getItemFromDB("SELECT b2bsr_requestDate FROM retaline_B2B_SalesRequest WHERE bbsr_id = {$bbsr_id}");
        $b2bsr_requestDate = strtotime($b2bsr_requestDate);
        $totalHC = $handlinchgGst + $_POST['adhoc_shippingcharge'];
        $adhocpo['bbsr_paymentTerms'] = $_POST['adhoc_paymentTerms'];
        $adhocpo['bbsr_paymentValue'] = $_POST['adhoc_paymentValue'];
        $adhocpo['bbsr_validityType'] = $_POST['adhoc_validityType'];
        $adhocpo['bbsr_shippingcharge'] = $_POST['adhoc_shippingcharge'];
        $adhocpo['bbsr_validDate'] = date('Y-m-d', strtotime('+15 days', $b2bsr_requestDate));
        $adhocpo['bbsr_srValue'] = $b2bsr_netamount + $totalHC;
        $adhocpo['bbsr_gdiscpercent'] = $_POST['adhoc_gdiscount'];
        //print_r($adhocpo);
        //$adhocpo['adhoc_gdiscounttype'] = $_POST['adhoc_gdiscounttype'];
        $adcon = " bbsr_id = {$bbsr_id}";
        $adhocpo = array_filter($adhocpo, 'strlen');
        $status = $db->perform('retaline_B2B_SalesRequest', $adhocpo, 'update', $adcon);

        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'General discount Apllied to PO items.'";
            echo '{"success":true,"bbsr_srValue":' . $adhocpo['bbsr_srValue'] . ',"msg":' . $msg . '}';
        } else {
            $msg = "'Error while applying general discount.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'listSoItemsStore':
        if (isset($_POST['filter'])) {
        $allowedFields = ['bbsr_id', 'bbsr_SRNumber', 'b2b_Customer_Name', 'b2b_Customer_ID', 'bbsr_SRUpdatedOn', 'status_id'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }
        $bbsr_id = $_POST['bbsr_id'];
        $br_ID = $db->getItemFromDB("SELECT br_ID FROM retaline_B2B_SalesRequest WHERE bbsr_id = {$bbsr_id}");
        $countDataQuery = "SELECT count(*) from retaline_B2B_SalesRequestDetails where bbsr_id = '{$bbsr_id}' {$filter_qry} ";
        $listQuery = "SELECT  bbsr_id,b2bsr_Customerid,b2bsr_itemid,b2bsr_itemname,b2bsr_itemmrp,b2bsr_itemqty,b2bsr_itemoffrqty,if(b2bsr_itemoffrqty > 0,CONCAT(b2bsr_itemqty,'+',b2bsr_itemoffrqty),b2bsr_totalqty) as b2bsr_totalqty,"
                . "b2bsr_balanceqty,b2bsr_itemrate,b2bsr_itemaddidisc,b2bsr_effectiverate,b2bsr_idiscountcalculs,b2bsr_netamount,b2bsr_amount,b2bsr_initialnetamount,b2bsr_gendiscount,b2bsr_shippingcharge,"
                . "@itemcount:=(SELECT item_count FROM finascop_stock_branch_inventory WHERE stit_id = b2bsr_itemid AND branch_id = {$br_ID}) AS itemCount,IF(b2bsr_totalqty > @itemcount,1,0) AS srStatus,"
                . "IF(b2bsr_itemaddidisc > 0,(CONCAT(b2bsr_itemaddidisc,'',IF(b2bsr_idiscountcalculs = 'Amount',' Rs',' %'))),'') AS itemDisc from retaline_B2B_SalesRequestDetails where bbsr_id = '{$bbsr_id}'  ORDER BY b2bsr_createdon ASC ";
        $db->printGridJson($countDataQuery, $listQuery);

        break;
    case 'updateQtyinSr':

        $adhocCount = $db->getItemSafe("SELECT COUNT(*) FROM retaline_B2B_SalesRequest WHERE bbsr_id = ? AND bbsr_SRUpdatedOn = '{$_POST['bbsr_SRUpdatedOn']}'", "i", [$_POST['bbsr_id']]);

        $db->query('begin');
        if ($adhocCount > 0) {
            $updatedDate = $db->getItemSafe("SELECT bbsr_SRUpdatedOn FROM retaline_B2B_SalesRequest WHERE bbsr_id = ?", "i", [$_POST['bbsr_id']]);
            if ($updatedDate == $_POST['bbsr_SRUpdatedOn']) {
                $adhocData['bbsr_SRUpdatedOn'] = date("Y-m-d H:i");
                $adhocData['bbsr_SRUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('retaline_B2B_SalesRequest', $adhocData, 'update', " bbsr_id = '{$_POST['bbsr_id']}'");
            } else {
                echo '{"success":false,"msg":"Reload data updation is going on."}';
                exit();
            }
        }
        $itemDetails = $db->getFromSafe("SELECT * FROM retaline_B2B_SalesRequestDetails WHERE bbsr_id = ? AND b2bsr_itemid = {$_POST['b2bsr_itemid']}", "i", [$_POST['bbsr_id']], true);
        $haveGST = $db->getItemFromDB("SELECT b2b_Customer_gst FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = {$itemDetails['b2bsr_Customerid']}");
        $data['b2bsr_itemqty'] = $_POST['changedQty'];
        $data['b2bsr_amount'] = $itemDetails['b2bsr_itemrateet'] * $_POST['changedQty'];
        if ($itemDetails['b2bsr_idiscountcalculs'] == 'Percentage') {
            $netAmount = $itemDetails['b2bsr_itemrateet'] - ($itemDetails['b2bsr_itemrateet'] * $itemDetails['b2bsr_itemaddidisc'] / 100);
        } else {
            $netAmount = $itemDetails['b2bsr_itemrateet'] - $itemDetails['b2bsr_itemaddidisc'];
        }
        //echo '$netAmount'.$netAmount;
        $data['b2bsr_netamountet'] = $netAmount * $_POST['changedQty'];
        $taxRate = $db->getItemSafe("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = ?", "i", [$_POST['b2bsr_itemid']]);
        $stit_GSTAmt = $data['b2bsr_netamountet'] * $taxRate / 100;
        $stit_GSTAmt = round($stit_GSTAmt, 2);
        if ($haveGST == '') {
            $cessAmt = $data['b2bsr_netamountet'] / 100;
            $cessAmt = round($cessAmt, 2);
        } else {
            $cessAmt = 0;
        }
        $data['b2bsr_itemgst'] = $stit_GSTAmt;
        $data['b2bsr_itemcess'] = $cessAmt;
        $data['b2bsr_netamount'] = $data['b2bsr_netamountet'] + $cessAmt + $stit_GSTAmt;
        $data['b2bsr_initialnetamount'] = $data['b2bsr_netamount'];
        $data['b2bsr_totalqty'] = (int) $itemDetails['b2bsr_itemoffrqty'] + (int) $data['b2bsr_itemqty'];
        $data['b2bsr_balanceqty'] = $data['b2bsr_totalqty'];
        $data['b2bsr_effectiverate'] = $data['b2bsr_netamount'] / $data['b2bsr_totalqty'];
        (float) $eprbft = ((float) $data['b2bsr_effectiverate'] / (100 + (float) $taxRate)) * 100;
        (float) $mrpbft = ((float) $itemDetails['b2bsr_itemmrp'] / (100 + (float) $taxRate)) * 100;
        $actmarginDistriPercent = 100 - (($eprbft / $mrpbft) * 100);
        $marginDistriPercent = round($actmarginDistriPercent);
        $data['actual_marginDistri'] = $actmarginDistriPercent;
        $data['bmd_percent'] = $marginDistriPercent;

        $itemMargin = $db->getFromDB("SELECT rmim_bmd_id from retaline_margin_item_mapping where rmim_stit_id = {$_POST['b2bsr_itemid']}");
        if ($itemMargin > 0) {
            $marginDistri = $itemMargin;
        } else {
            $marginDistri = $db->getItemFromDB("SELECT bmd_id from retaline_margindistributionsb2b where is_default = 1");
        }

        $data['bmd_id'] = $marginDistri;

        $con = " bbsr_id = {$_POST['bbsr_id']} AND b2bsr_itemid = {$_POST['b2bsr_itemid']}";
        $status = $db->perform('retaline_B2B_SalesRequestDetails', $data, 'update', $con);

        $newupdatedDate = $db->getItemSafe("SELECT bbsr_SRUpdatedOn FROM retaline_B2B_SalesRequest WHERE bbsr_id = ?", "i", [$_POST['bbsr_id']]);
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'B2B Sales Request Item saved successfully.'";
            echo '{"success":true,"date":"' . $newupdatedDate . '","msg":' . $msg . '}';
        } else {
            $msg = "'Error while saving .'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'loadB2bCustomerSalesRequest':
        $b2bSOCustomer = $_POST['b2bSOCustomer'];
        $bbsr_id = $_POST['bbsr_id'];

        break;
    case 'deleteB2BSalesRequest':
        $srId = $_POST['srId'];
        $db->query('begin');
        if ($srId > 0) {
            $delquery = "DELETE FROM retaline_B2B_SalesRequestDetails  WHERE bbsr_id = {$srId}";
            $status = $db->query($delquery);
            $delquerySR = "DELETE FROM retaline_B2B_SalesRequest  WHERE bbsr_id = {$srId}";
            $status = $db->query($delquerySR);
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Deleted Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while deleting data' }}";
        }
        break;
}