<?php

switch ($op) {

    case 'generateUniqueId':
        $uniqueId = '';
        while ($uniqueId == '') {
            $uniqueId = getNewFinascopApiKey();
        }

        echo '{"uid":"' . $uniqueId . '"}';
        break;
    case 'itemHistory':
        $pe_partyItems = $_POST['pe_partyItems'];
        $po_billing_to = $_POST['po_billing_to'];
        $isStandardPacking = $_POST['isStandardPacking'];

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
        $qry = "SELECT m.stit_ID,stit_itemName,stit_SKU,stit_brand_name,stit_category_name,stit_product_variant,stit_quantity,stit_fixedB2BRates,least_package_type_name,isRRPApplicable,"
                . "(SELECT fpod_effectiverate from finascop_purchase_order_details where fpod_itemid = m.stit_ID ORDER BY fpod_id desc limit 1) as last_mrp,"
                . "(SELECT min(fpod_effectiverate) FROM finascop_purchase_order_details where fpod_itemid = m.stit_ID) as last_sp,stit_HSN_code,stit_GST,csb_package_type_name,cs_nos,"
                . "cs_package_type_name,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,cosb_package_type_name "
                . "{$stockFields} FROM finascop_stock_itemmaster m WHERE m.stit_ID ='{$pe_partyItems}' ";

        $itemHistory = $db->getFromDB($qry, true);
        $itemHistory['itemCSCount'] = $db->getItemFromDB("SELECT item_count FROM finascop_stock_branch_inventory WHERE stit_id = {$pe_partyItems}"); //branch_id = {$po_billing_to} AND 
        $itemHistory['itemSmaalStockUnit'] = $itemHistory['csb_package_type_name'] . ' - ' . $itemHistory['cs_nos'] . ' ' . $itemHistory['cs_package_type_name'] . ', ' . $itemHistory['cs_nos'] . ' ' . $itemHistory['cs_package_type_name'] . ' of ' . $itemHistory['ds_nos'] . ' ' . $itemHistory['ds_package_type_name'] . ' each, ' . $itemHistory['cs_nos'] * $itemHistory['ds_nos'] . $itemHistory['ds_package_type_name'] . ' of ' . $itemHistory['cos_nos'] . ' ' . $itemHistory['cos_package_type_name'] . ' each';
        if ($isStandardPacking == 1) {
            $itemHistory['itemUnitForm'] = $itemHistory['least_package_type_name'];
        } else {
            $itemHistory['itemUnitForm'] = $itemHistory['least_package_type_name'];
        }

        if (!empty($itemHistory)) {
            echo json_encode($itemHistory);
        }
        break;
    case 'saveItemPODetails':
        //print_r($_POST);exit();
        $isStandardPacking = $_POST['isStandardPacking'];
        $adhocCount = $db->getItemSafe("SELECT COUNT(*) FROM finascop_contractpo WHERE fcpo_uniqueid = ? ", "i", [$_POST['fpot_uniqueid']]);

        $db->query('begin');
        if ($adhocCount > 0) {
//            $updatedDate = $db->getItemSafe("SELECT fcpo_updatedon FROM finascop_contractpo WHERE prereq_uniqueid = ?", "s", [$_POST['fpopredet_uniqueid']]);
//            if ($updatedDate == $_POST['poprereq_updatedon']) {
            $adhocData['fcpo_updatedon'] = date("Y-m-d H:i");
            $adhocData['fcpo_updatedby'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('finascop_contractpo', $adhocData, 'update', " fcpo_uniqueid = '{$_POST['fpot_uniqueid']}'");
//            } else {
//                echo '{"success":false,"msg":"Reload data updation is going on."}';
//                exit();
//            }
        } else {
            $adhocData['fcpo_uniqueid'] = $_POST['fpot_uniqueid'];
            $adhocData['fcpo_vendor'] = $_POST['fpot_vendorid'];
            $adhocData['fcpo_createdon'] = date("Y-m-d H:i");
            $adhocData['fcpo_createdby'] = $_SESSION['admin']->Finascop_UserId;
            $adhocData['fcpo_updatedon'] = date("Y-m-d H:i");
            $adhocData['fcpo_updatedby'] = $_SESSION['admin']->Finascop_UserId;
            $adhocData['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;

            $status = $db->perform('finascop_contractpo', $adhocData);
        }

        $data['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
        $data['fcpod_uniqueid'] = $_POST['fpot_uniqueid'];
        $data['fcpod_vendorid'] = $_POST['fpot_vendorid'];
        $data['fcpod_itemid'] = $_POST['fpot_itemid'];
        $data['fcpod_itemname'] = $_POST['fpot_itemname'];

        $data['fcpod_itemoffrrate'] = $_POST['fpot_itemoffrrate'];
        $data['fcpod_itemoffrrateet'] = $_POST['fpot_itemoffrrateExcT'];

        $data['fcpod_giftname'] = $_POST['fpot_giftname'];
        $data['fcpod_giftqty'] = $_POST['fpot_giftqty'];
        $data['fcpod_notes'] = $_POST['fpot_notes'];
        $data['fcpod_createdon'] = date("Y-m-d H:i:s");
        $data['fcpod_createdby'] = $_SESSION['admin']->Finascop_UserId;
        $qry = "SELECT stit_GST,csb_package_type_name,cs_nos,cs_package_type_name,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,stdpckl11_package_type_id,stdpckl21_package_type_id,"
                . "stdpckl2_nos,stdpckl31_package_type_id,stdpckl3_nos,stdpckl41_package_type_id,stdpckl4_nos,stit_GST,csb_package_type_name,cs_nos,stdpckl1_nos "
                . "FROM finascop_stock_itemmaster m WHERE m.stit_ID ='{$_POST['fpot_itemid']}' ";
        $itemHistory = $db->getFromDB($qry, true);
        $data['fcpod_purchasingUnit'] = $db->getItemFromDB("SELECT least_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$data['fcpod_itemid']}");
        //echo 'fcpod_purchasingUnit'.$data['fcpod_purchasingUnit'];
        //print_r($itemHistory);
        if ($data['fcpod_purchasingUnit'] == $itemHistory['stdpckl12_package_type_id']) {
            $level = '4';
            $fpot_leastSKUqty = 1 / $itemHistory['stdpckl1_nos'];
        } else if ($data['fcpod_purchasingUnit'] == $itemHistory['stdpckl11_package_type_id']) {
            $level = '3';
            $fpot_leastSKUqty = 1;
        } else if ($data['fcpod_purchasingUnit'] == $itemHistory['stdpckl21_package_type_id']) {
            $level = '2';
            $fpot_leastSKUqty = 1 * $itemHistory['stdpckl2_nos'];
        } else if ($data['fcpod_purchasingUnit'] == $itemHistory['stdpckl31_package_type_id']) {
            $level = '1';
            $fpot_leastSKUqty =  $itemHistory['stdpckl2_nos'] * $itemHistory['stdpckl3_nos'];
        } else {
            $level = '1';
            $fpot_leastSKUqty = 1;
        }
        if (empty($fpot_leastSKUqty) || $fpot_leastSKUqty <= 0) {
            echo '{"success":false,"msg":"Invalid packing quantity for this item."}';
            exit();
        }
//echo 'level'.$level;
//echo 'fpot_leastSKUqty'.$fpot_leastSKUqty;
        $data['fcpod_leastSKUqty'] = $fpot_leastSKUqty;
        
            $data['isStandardPacking'] = 0;
            
            $data['fcpod_leastSKUmrp'] = $_POST['fpot_itemmrp'];
            $data['fcpod_effectiverateSKU'] = $data['fcpod_itemoffrrateet'];
            $data['fcpod_poLandingCostleastSKU'] = $data['fcpod_itemoffrrate'];
            $data['fcpod_poMMGleastSKU'] = $data['fcpod_leastSKUmrp'] - $data['fcpod_poLandingCostleastSKU'];

            $data['fcpod_itemmrp'] = round($data['fcpod_leastSKUmrp'] * $data['fcpod_leastSKUqty'], 2);
            $data['fcpod_effectiverate'] = round($data['fcpod_effectiverateSKU'] * $data['fcpod_leastSKUqty'], 2);
            $data['fcpod_poLandingCost'] = round($data['fcpod_poLandingCostleastSKU'] * $data['fcpod_leastSKUqty'], 2);
            $data['fcpod_poMMG'] = round($data['fcpod_poMMGleastSKU'] * $data['fcpod_leastSKUqty'], 2);
            $data['fcpod_isRRP'] = $_POST['fpot_isRRP'];
            
        // print_r($data);

        $dup = $db->getItemFromDB("SELECT COUNT(1) FROM finascop_contractpo_products WHERE fcpod_uniqueid = '{$data['fcpod_uniqueid']}' and fcpod_itemid = {$data['fcpod_itemid']}");

        if ($dup > 0) {
            $con = "fcpod_uniqueid = '{$data['fcpod_uniqueid']}' and fcpod_itemid = {$data['fcpod_itemid']}";
            $status = $db->perform('finascop_contractpo_products', $data, 'update', $con);
        } else {
            $status = $db->perform('finascop_contractpo_products', $data);
        }
        $newupdatedDate = $db->getItemSafe("SELECT fcpo_updatedon FROM finascop_contractpo WHERE fcpo_uniqueid = ?", "s", [$_POST['fpot_uniqueid']]);
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Item Contract PO details saved successfully.'";
            echo '{"success":true,"date":"' . $newupdatedDate . '","msg":' . $msg . '}';
        } else {
            $msg = "'Error while saving invoice.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'listContractPodetailsStore':
        if (isset($_POST['filter'])) {
            $allowedFields = ['cpo_id', 'cpo_number', 'cpo_date', 'vendor_name', 'cpo_status', 'cpo_total'];
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        //echo $filter_qry;
        $fpot_uniqueid = $_POST['fpot_uniqueid'];
        $countDataQuery = "SELECT COUNT(*) from finascop_contractpo_products where fcpod_uniqueid = '{$fpot_uniqueid}' {$filter_qry} ";
        $listQuery = "SELECT  fcpod_vendorid,fcpod_itemid,fcpod_itemname,fcpod_itemmrp,fcpod_itemoffrrate,fcpod_itemoffrrateet,fcpod_giftname,fcpod_giftqty,fcpod_notes,fcpod_purchasingUnit,"
                . "fcpod_leastSKUmrp,isStandardPacking,(SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = fcpod_purchasingUnit) as  leastSKU,fcpod_customerRateHmDel,fcpod_customerRateCouDel "
                . "from finascop_contractpo_products where fcpod_uniqueid = '{$fpot_uniqueid}' {$filter_qry} ORDER BY fcpod_createdon ASC ";
        $db->printGridJson($countDataQuery, $listQuery);
        break;
    case 'getPurchaseOrderContract':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 12;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'fcpo_createdon' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;

        $filter_qry = " WHERE 1 = 1 ";//AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}
        if (isset($_POST['filter'])) {
            $allowedFields = ['cpo_id', 'cpo_number', 'cpo_date', 'vendor_name', 'cpo_status', 'cpo_total'];
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }

        $date = date('Y-m-d');
        $countDataQuery = "SELECT count(*) from finascop_contractpo fp  {$filter_qry}  ORDER BY fcpo_createdon DESC";
        $listQuery = "SELECT  fcpo_uniqueid,fcpo_name,fcpo_createdby,(SELECT stpa_Fname FROM finascop_stock_party WHERE stpa_id = fcpo_vendor) as vendorName,fcpo_status,fcpo_createdon,fcpo_id,fcpo_updatedon 
 FROM finascop_contractpo fp  {$filter_qry} ORDER BY fcpo_createdon DESC LIMIT $start,$limit ";
        $db->printGridJson($countDataQuery, $listQuery);
        break;
    case 'loadContractPO':

        $podata = $db->getFromSafe("SELECT  fcpo_uniqueid ,fcpo_name ,DATE_FORMAT(fcpo_createdon,'%d-%m-%Y %H:%i:%s') as fcpo_createdon,fcpo_createdby,fcpo_updatedon,fcpo_poValue,fcpo_id,fcpo_delivered_byvendor,
            fcpo_paymentTerms,fcpo_paymentValue,fcpo_validityType,(SELECT stpa_Fname FROM finascop_stock_party WHERE stpa_id = fcpo_vendor) as vendorName,fcpo_vendor,fcpo_name,fcpo_validDate  
 FROM finascop_contractpo fp  {$filter_qry} where fcpo_uniqueid = ?", "s", [$_POST['uniqueid']], true);
        if (!empty($podata)) {
            echo json_encode($podata);
        }
        break;
    case 'deleteItemFromContractPO':
        //print_r($_POST);exit();
        $itemid = $_POST['itemid'];
        $uid = $_POST['uid'];
        $db->query('begin');
        $delquery = "DELETE FROM finascop_contractpo_products  WHERE fcpod_uniqueid = '{$uid}' AND fcpod_itemid = {$itemid}";
        $status = $db->query($delquery);

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Deleted Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while deleting data' }}";
        }
        break;
    case 'deleteContractPODetails':
        $db->query('begin');
        $del_query = "DELETE FROM finascop_contractpo WHERE fcpo_uniqueid='{$_POST['adhoc_uniqueid']}'";
        $temp = $db->query($del_query);
        if ($temp) {
            $del_query = "DELETE FROM finascop_contractpo_products WHERE fcpod_uniqueid='{$_POST['adhoc_uniqueid']}'";
            $db->query($del_query);
        }
        $status = $db->query('commit');
        if (status) {
            echo "{success:true,valid:true,message:'Deleted Succesfully ' }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Deleteing.'}";
        }
        break;
    case 'contractPOSave':
        $fpopredet_uniqueid = $_POST['fpot_uniqueid'];
        $db->query('begin');


        $adhocpo['fcpo_delivered_byvendor'] = $_POST['po_delivered_byvendor'];
        $adhocpo['fcpo_paymentTerms'] = $_POST['adhoc_paymentTerms'];
        $adhocpo['fcpo_validDate'] = date('Y-m-d', strtotime($_POST['adhoc_paymentValue']));

        $fcpo_status = $db->getItemFromDB("SELECT fcpo_status FROM finascop_contractpo WHERE fcpo_uniqueid = '{$fpopredet_uniqueid}'");
        if ($_POST['isConfirm'] == 1) {
            if ($fcpo_status == 0) {
                $adhocpo['fcpo_name'] = 'CPR' . time();
            }
            $adhocpo['fcpo_status'] = 1;
        } else {
            if ($fcpo_status == 0) {
                $adhocpo['fcpo_status'] = 0;
            }
        }
        // print_r($adhocpo);
        $adcon = " fcpo_uniqueid = '{$fpopredet_uniqueid}'";
        $adhocpo = array_filter($adhocpo);
        $status = $db->perform('finascop_contractpo', $adhocpo, 'update', $adcon);

        $prereqpode['fcpod_fcponame'] = $adhocpo['fcpo_name'];
        $prereqpode['fcpo_validDate'] = date('Y-m-d', strtotime($_POST['adhoc_paymentValue']));
        $prereqpode['fcpo_id'] = $db->getItemFromDB("SELECT fcpo_id FROM finascop_contractpo WHERE fcpo_uniqueid = '{$fpopredet_uniqueid}'");
        $status = $db->perform('finascop_contractpo_products', $prereqpode, 'update', " fcpod_uniqueid = '{$fpopredet_uniqueid}'");

        $bmdDetails = $db->getFromDB("SELECT * FROM retaline_margindistributions WHERE is_default = 1", true);
        $contractpo_details = $db->getMultipleData("SELECT fcpod_uniqueid,fcpod_fcponame,fcpod_itemid,fcpod_itemmrp,fcpod_itemoffrrate,fcpod_itemoffrrateet,fcpod_effectiverate,fcpod_poLandingCost,fcpod_poMMG,fcpod_leastSKUmrp,"
                . "fcpod_effectiverateSKU,fcpod_poLandingCostleastSKU,fcpod_poMMGleastSKU,fcpod_leastSKUqty FROM finascop_contractpo_products WHERE fcpod_uniqueid = '{$fpopredet_uniqueid}'", true);
        foreach ($contractpo_details as $contractpo_detail) {
            //print_r($contractpo_detail);
            $qry = "SELECT stit_GST,csb_package_type_name,cs_nos,cs_package_type_name,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name "
                    . "FROM finascop_stock_itemmaster m WHERE m.stit_ID ='{$contractpo_detail['fcpod_itemid']}' ";
            $itemHistory = $db->getFromDB($qry, true);

            $fpoddata['bmd_id'] = $bmdDetails['bmd_id'];
            $fpoddata['bmd_company'] = $bmdDetails['bmd_company'];
            $fpoddata['bmd_incentive'] = $bmdDetails['bmd_incentive'];
            $fpoddata['bmd_customer'] = $bmdDetails['bmd_customer'];
            $fpoddata['bmd_cs'] = $bmdDetails['bmd_hub'];
            $fpoddata['bmd_distributor'] = $bmdDetails['bmd_distributor'];
            $fpoddata['bmd_retailor'] = $bmdDetails['bmd_retailor'];
            $fpoddata['bmd_driver'] = $bmdDetails['bmd_driver'];
            $fpoddata['bmd_courier'] = $bmdDetails['bmd_courier'];

            $fpoddata['fcpod_leastSKUqty'] = $contractpo_detail['fcpod_leastSKUqty'];

            $fpoddata['fcpod_itemmrp'] = $contractpo_detail['fcpod_itemmrp'];
            $fpoddata['fcpod_effectiverate'] = $contractpo_detail['fcpod_effectiverate'];
            $fpoddata['fcpod_poLandingCost'] = $contractpo_detail['fcpod_poLandingCost'];
            $fpoddata['fcpod_poMMG'] = $contractpo_detail['fcpod_poMMG'];

            $fpoddata['fcpod_leastSKUmrp'] = $contractpo_detail['fcpod_leastSKUmrp'];
            $fpoddata['fcpod_effectiverateSKU'] = $contractpo_detail['fcpod_effectiverateSKU'];
            $fpoddata['fcpod_poLandingCostleastSKU'] = $contractpo_detail['fcpod_poLandingCostleastSKU'];
            $fpoddata['fcpod_poMMGleastSKU'] = $contractpo_detail['fcpod_poMMGleastSKU'];


            // print_r($fpoddata);
            $fpod_spHmDel = $fpoddata['fcpod_poLandingCost'] + (($fpoddata['fcpod_poMMG'] * $fpoddata['bmd_company'] / 100) + ($fpoddata['fcpod_poMMG'] * $fpoddata['bmd_incentive'] / 100) + ($fpoddata['fcpod_poMMG'] * $fpoddata['bmd_cs'] / 100) + ($fpoddata['fcpod_poMMG'] * $fpoddata['bmd_distributor'] / 100) + ($fpoddata['fcpod_poMMG'] * $fpoddata['bmd_retailor'] / 100) + ($fpoddata['fcpod_poMMG'] * $fpoddata['bmd_driver'] / 100));
            $fpoddata['fcpod_spHmDel'] = round($fpod_spHmDel, 2);
            $fpod_spCouDel = $fpoddata['fcpod_poLandingCost'] + (($fpoddata['fcpod_poMMG'] * $fpoddata['bmd_company'] / 100) + ($fpoddata['fcpod_poMMG'] * $fpoddata['bmd_incentive'] / 100) + ($fpoddata['fcpod_poMMG'] * $fpoddata['bmd_cs'] / 100) + ($fpoddata['fcpod_poMMG'] * $fpoddata['bmd_distributor'] / 100) + ($fpoddata['fcpod_poMMG'] * $fpoddata['bmd_retailor'] / 100) + ($fpoddata['fcpod_poMMG'] * $fpoddata['bmd_courier'] / 100));
            $fpoddata['fcpod_spCouDel'] = round($fpod_spCouDel, 2);
            $fpod_spPikup = $fpoddata['fcpod_poLandingCost'] + (($fpoddata['fcpod_poMMG'] * $fpoddata['bmd_company'] / 100) + ($fpoddata['fcpod_poMMG'] * $fpoddata['bmd_incentive'] / 100) + ($fpoddata['fcpod_poMMG'] * $fpoddata['bmd_cs'] / 100) + ($fpoddata['fcpod_poMMG'] * $fpoddata['bmd_distributor'] / 100) + ($fpoddata['fcpod_poMMG'] * $fpoddata['bmd_retailor'] / 100));
            $fpoddata['fcpod_spPikup'] = round($fpod_spPikup, 2);

            $fpod_spetHmDel = ($fpoddata['fcpod_spHmDel'] * 100) / (100 + $itemHistory['stit_GST']);
            $fpoddata['fcpod_spetHmDel'] = round($fpod_spetHmDel, 2);
            $fpod_spetCouDel = ($fpoddata['fcpod_spCouDel'] * 100) / (100 + $itemHistory['stit_GST']);
            $fpoddata['fcpod_spetCouDel'] = round($fpod_spetCouDel, 2);
            $fpod_spetPikup = ($fpoddata['fcpod_spPikup'] * 100) / (100 + $itemHistory['stit_GST']);
            $fpoddata['fcpod_spetPikup'] = round($fpod_spetPikup, 2);


            $fpoddata['fcpod_gstHmDel'] = $fpoddata['fcpod_spHmDel'] - $fpoddata['fcpod_spetHmDel'];
            $fpoddata['fcpod_gstCouDel'] = $fpoddata['fcpod_spCouDel'] - $fpoddata['fcpod_spetCouDel'];
            $fpoddata['fcpod_gstPikup'] = $fpoddata['fcpod_spPikup'] - $fpoddata['fcpod_spetPikup'];

            $fpoddata['fcpod_marginHmDel'] = $fpoddata['fcpod_spetHmDel'] - $fpoddata['fcpod_effectiverate'];
            $fpoddata['fcpod_marginCouDel'] = $fpoddata['fcpod_spetCouDel'] - $fpoddata['fcpod_effectiverate'];
            $fpoddata['fcpod_marginPikup'] = $fpoddata['fcpod_spetPikup'] - $fpoddata['fcpod_effectiverate'];

            $fpod_companyMargin = $fpoddata['fcpod_marginHmDel'] * ($fpoddata['bmd_company'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_driver']));
            $fpoddata['fcpod_companyMarginHD'] = round($fpod_companyMargin, 2);
            $fpod_incentiveMargin = $fpoddata['fcpod_marginHmDel'] * ($fpoddata['bmd_incentive'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_driver']));
            $fpoddata['fcpod_incentiveMarginHD'] = round($fpod_incentiveMargin, 2);
            $fpod_csMargin = $fpoddata['fcpod_marginHmDel'] * ($fpoddata['bmd_cs'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_driver']));
            $fpoddata['fcpod_csMarginHD'] = round($fpod_csMargin, 2);
            $fpod_distributorMargin = $fpoddata['fcpod_marginHmDel'] * ($fpoddata['bmd_distributor'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_driver']));
            $fpoddata['fcpod_distributorMarginHD'] = round($fpod_distributorMargin, 2);
            $fpod_retailorMargin = $fpoddata['fcpod_marginHmDel'] * ($fpoddata['bmd_retailor'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_driver']));
            $fpoddata['fcpod_retailorMarginHD'] = round($fpod_retailorMargin, 2);
            $fpod_driverMargin = $fpoddata['fcpod_marginHmDel'] * ($fpoddata['bmd_driver'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_driver']));
            $fpoddata['fcpod_driverMarginHD'] = round($fpod_driverMargin, 2);


            $fpod_companyMargin = $fpoddata['fcpod_marginCouDel'] * ($fpoddata['bmd_company'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_courier']));
            $fpoddata['fcpod_companyMarginCD'] = round($fpod_companyMargin, 2);
            $fpod_incentiveMargin = $fpoddata['fcpod_marginCouDel'] * ($fpoddata['bmd_incentive'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_courier']));
            $fpoddata['fcpod_incentiveMarginCD'] = round($fpod_incentiveMargin, 2);
            $fpod_csMargin = $fpoddata['fcpod_marginCouDel'] * ($fpoddata['bmd_cs'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_courier']));
            $fpoddata['fcpod_csMarginCD'] = round($fpod_csMargin, 2);
            $fpod_distributorMargin = $fpoddata['fcpod_marginCouDel'] * ($fpoddata['bmd_distributor'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_courier']));
            $fpoddata['fcpod_distributorMarginCD'] = round($fpod_distributorMargin, 2);
            $fpod_retailorMargin = $fpoddata['fcpod_marginCouDel'] * ($fpoddata['bmd_retailor'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_courier']));
            $fpoddata['fcpod_retailorMarginCD'] = round($fpod_retailorMargin, 2);
            $fpod_courierMargin = $fpoddata['fcpod_marginCouDel'] * ($fpoddata['bmd_courier'] / ($fpoddata['bmd_company'] + $fpoddata['bmd_incentive'] + $fpoddata['bmd_cs'] + $fpoddata['bmd_distributor'] + $fpoddata['bmd_retailor'] + $fpoddata['bmd_courier']));
            $fpoddata['fcpod_courierMarginCD'] = round($fpod_courierMargin, 2);

            $fpoddata['fcpod_itemSmallStockUnit'] = $itemHistory['csb_package_type_name'] . ' contains ' . $itemHistory['cs_nos'] . ' quantity ' . $itemHistory['cs_package_type_name'] . ', ' . $itemHistory['cs_nos'] * $itemHistory['ds_nos'] . ' ' . $itemHistory['ds_package_type_name'] . ', ' . $itemHistory['cs_nos'] * $itemHistory['ds_nos'] * $itemHistory['cos_nos'] . ' ' . $itemHistory['cos_package_type_name'];

            $fpoddata['fcpod_customerRateHmDel'] = $fpoddata['fcpod_spetHmDel'] / $fpoddata['fcpod_leastSKUqty'];
            $fpoddata['fcpod_customerRateCouDel'] = $fpoddata['fcpod_spetCouDel'] / $fpoddata['fcpod_leastSKUqty'];

            //print_r($fpoddata);
            $status = $db->perform('finascop_contractpo_products', $fpoddata, 'update', " fcpod_uniqueid = '{$fpopredet_uniqueid}' AND fcpod_itemid = {$contractpo_detail['fcpod_itemid']}");
        }
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Contract PO saved.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error while saving data.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'getCPODetails':
        $poId = $_POST['poId'];
        $podata = $db->getFromDB("SELECT  fcpo_uniqueid,fcpo_id,fcpo_name,fcpo_vendor,(SELECT stpa_Fname FROM finascop_stock_party WHERE stpa_id = fcpo_vendor) as vendorName,"
                . "DATE_FORMAT(fcpo_createdon,'%d-%m-%Y') as fcpo_createdon,fcpo_paymentTerms,fcpo_paymentValue,DATE_FORMAT(fcpo_validDate,'%d-%m-%Y') as fcpo_validDate,"
                . "(SELECT br_Name FROM finascop_branch WHERE br_ID = branch_id) as centralStore  FROM finascop_contractpo where fcpo_id = {$poId}", true);
        if (!empty($podata)) {
            echo json_encode($podata);
        }
        break;
    case 'contractVendorDetails':
        $contract_VendorId = $_POST['contract_VendorId'];
        $result = $db->getFromDB("SELECT stpa_id,stpa_Fname,stpa_MobileNo  FROM finascop_stock_party WHERE stpa_id = {$contract_VendorId}", true);
        $isSalesRequestAvailable = $db->getFromDB("SELECT fcpo_id,fcpo_uniqueid,fcpo_name,fcpo_vendor,DATE_FORMAT(fcpo_createdon,'%d-%m-%Y') as fcpo_createdon FROM finascop_contractpo WHERE fcpo_vendor = {$contract_VendorId}  ", true);
        $result['fcpo_id'] = $isSalesRequestAvailable['fcpo_id'];
        $result['fcpo_uniqueid'] = $isSalesRequestAvailable['fcpo_uniqueid'];
        $result['fcpo_name'] = $isSalesRequestAvailable['fcpo_name'];
        $result['fcpo_createdon'] = $isSalesRequestAvailable['fcpo_createdon'];
        if (!empty($result)) {//pe_party
            echo json_encode($result);
        } else {
            echo '{"data":[]}';
        }
        break;
    case 'getVendorName':
        $qry = $db->getMulipleData("SELECT stpa_id,stpa_Fname FROM finascop_stock_party WHERE stpa_IsVendor = 1 AND deliverMode_cpr <> 3 AND br_id = {$_SESSION['admin']->finascop_current_branch_id} ORDER BY stpa_Fname ASC", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
}
