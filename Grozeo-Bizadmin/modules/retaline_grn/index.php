<?php

require_once(ROOT . '/finascop_config/lib.php');
switch ($op) {
    case 'generateUniqueId':
        $uniqueId = '';
        while ($uniqueId == '') {
            $uniqueId = getNewFinascopApiKey();
        }

        echo '{"uid":"' . $uniqueId . '"}';
        break;
    case 'saveItemGRNRetqDetails':
        if(!empty($_POST['poretgrn_updatedon'])){
            $adhocCount = $db->getItemSafe("SELECT COUNT(*) FROM retaline_grn WHERE retgrn_uniqueid = ? AND retgrn_updatedon = '{$_POST['poretgrn_updatedon']}'", "i", [$_POST['retgrnd_uniqueid']]);
        }
        
//        if ($_POST['poretgrn_updatedon'] != '' && $adhocCount > 0) {
//            echo '{"success":false,"msg":"Reload data updation is going on."}';
//            exit();
//        }
        $db->query('begin');
        if ($adhocCount > 0) {
            $retgrn_id = $db->getItemSafe("SELECT retgrn_id FROM retaline_grn WHERE retgrn_uniqueid = ?", "s", [$_POST['retgrnd_uniqueid']]);
            $updatedDate = $db->getItemSafe("SELECT retgrn_updatedon FROM retaline_grn WHERE retgrn_uniqueid = ?", "s", [$_POST['retgrnd_uniqueid']]);
            if ($updatedDate == $_POST['poretgrn_updatedon']) {
                $adhocData['retgrn_updatedon'] = date("Y-m-d H:i");
                $adhocData['retgrn_updatedby'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('retaline_grn', $adhocData, 'update', " retgrn_uniqueid = '{$_POST['retgrnd_uniqueid']}'");
            } else {
                echo '{"success":false,"msg":"Reload data updation is going on."}';
                exit();
            }
        } else {
            $adhocData['retgrn_uniqueid'] = $_POST['retgrnd_uniqueid'];
            $adhocData['retgrn_uniqueid'] = $_POST['retgrnd_uniqueid'];
            $adhocData['retgrn_vendor'] = $_POST['retgrnd_vendorid'];
            $adhocData['retgrn_createdon'] = date("Y-m-d H:i");
            $adhocData['retgrn_createdby'] = $_SESSION['admin']->Finascop_UserId;
            $adhocData['retgrn_updatedon'] = date("Y-m-d H:i");
            $adhocData['retgrn_updatedby'] = $_SESSION['admin']->Finascop_UserId;
            $adhocData['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;

            $status = $db->perform('retaline_grn', $adhocData);
            $retgrn_id = $db->insert_id();
        }

        $data['retgrn_id'] = $retgrn_id;
        $data['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
        $data['retgrnd_uniqueid'] = $_POST['retgrnd_uniqueid'];
        $data['retgrnd_vendorid'] = $_POST['retgrnd_vendorid'];
        $data['retgrnd_itemid'] = $_POST['retgrnd_itemid'];
        $data['retgrnd_itemname'] = $_POST['retgrnd_itemname'];
        $data['retgrnd_itemoffrrate'] = $_POST['retgrnd_itemoffrrate'];
        $data['retgrnd_itemmrp'] = $_POST['retgrnd_itemmrp'];
        $data['retgrnd_itemqty'] = $_POST['retgrnd_itemqty'];
        $data['retgrnd_createdon'] = date("Y-m-d H:i:s");
        $data['retgrnd_createdby'] = $_SESSION['admin']->Finascop_UserId;
        $data['retgrnd_purchasingUnit'] = $_POST['retgrnd_purchasingUnit'];
        $data['retgrnd_amount'] = $_POST['retgrnd_amount'];
        $data['retgrnd_netamount'] = $_POST['retgrnd_amount'];
        //$data['retgrnd_purchasingUnit'] = $db->getItemFromDB("SELECT csb_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$data['retgrnd_itemid']}");

        $db->query('begin');
        $dup = $db->getItemFromDB("SELECT COUNT(1) FROM retaline_grn_details WHERE retgrnd_uniqueid = '{$data['retgrnd_uniqueid']}' and retgrnd_itemid = {$data['retgrnd_itemid']}");

        if ($dup > 0) {
            $con = "retgrnd_uniqueid = '{$data['retgrnd_uniqueid']}' and retgrnd_itemid = {$data['retgrnd_itemid']}";
            $status = $db->perform('retaline_grn_details', $data, 'update', $con);
        } else {
            $status = $db->perform('retaline_grn_details', $data);
        }
        $newupdatedDate = $db->getItemSafe("SELECT retgrn_updatedon FROM retaline_grn WHERE retgrn_uniqueid = ?", "s", [$_POST['retgrnd_uniqueid']]);
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Item GRN details saved successfully.'";
            echo '{"success":true,"date":"' . $newupdatedDate . '","msg":' . $msg . '}';
        } else {
            $msg = "'Error while saving .'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'listRetGrndetailsStore':
        if (isset($_POST['filter'])) {
            $allowedFields = ['grn_id', 'grn_PONumber', 'grn_GRNNumber', 'grn_createdOn', 'grn_vendor_name', 'grn_status'];
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        $retgrnd_uniqueid = $_POST['retgrnd_uniqueid'];
        $countDataQuery = "SELECT count(*) from retaline_grn_details where retgrnd_uniqueid = '{$retgrnd_uniqueid}' {$filter_qry} ";
        $listQuery = "SELECT  retgrnd_vendorid,retgrnd_itemid,retgrnd_itemname,retgrnd_itemmrp,retgrnd_itemqty,retgrnd_itemoffrqty,"
                . "if(retgrnd_itemoffrqty > 0,CONCAT(retgrnd_itemqty,'+',retgrnd_itemoffrqty),retgrnd_totalqty) as retgrnd_totalqty,retgrnd_balanceqty,retgrnd_itemoffrrate,retgrnd_itemoffrrateet,retgrnd_itemaddidisc,"
                . "retgrnd_effectiverate,(SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = retgrnd_purchasingUnit) as unitName,"
                . "retgrnd_idiscountcalculs,retgrnd_netamount,retgrnd_amount,retgrnd_initialnetamount,retgrnd_gendiscount,retgrnd_shippingcharge,retgrnd_purchasingUnit,"
                . "IF(retgrnd_itemaddidisc > 0,(CONCAT(retgrnd_itemaddidisc,'',IF(retgrnd_idiscountcalculs = 'Amount',' Rs',' %'))),'') AS itemDisc from retaline_grn_details where retgrnd_uniqueid = '{$retgrnd_uniqueid}' ORDER BY retgrnd_createdon ASC ";
        $db->printGridJson($countDataQuery, $listQuery);

        break;
    case 'retGRNSaveClose':
        $retgrnd_uniqueid = $_POST['retgrnd_uniqueid'];
        $adhocpo['retgrn_billingTo'] = $_POST['retgrn_billingTo'];

        if ($_POST['isConfirm'] == 1) {
            $adhocpo['retgrn_name'] = getNewGRNNumber($_POST['retgrn_billingTo']);
            $adhocpo['retgrn_status'] = 1;
            $adhocpo['retgrn_date'] = date('Y-m-d', strtotime($_POST['retgrn_date']));
            $retgrnpode['retgrnd_retgrnname'] = $adhocpo['retgrn_name'];
        } else {
            $adhocpo['retgrn_status'] = 0;
        }

        $adhocpo = array_filter($adhocpo);
        if (!empty($retgrnd_uniqueid) && (count($adhocpo) > 0)) {
            $db->query('begin');
            $adcon = " retgrn_uniqueid = '{$retgrnd_uniqueid}'";
            $status = $db->perform('retaline_grn', $adhocpo, 'update', $adcon);

            $retgrnpode['retgrnd_retgrnname'] = $adhocpo['retgrn_name'];
            $status = $db->perform('retaline_grn_details', $retgrnpode, 'update', " retgrnd_uniqueid = '{$retgrnd_uniqueid}'");
        }
        if ($_POST['isConfirm'] == 1) {
            //create PO
            $grnMainData = $db->getFromDB("SELECT * FROM retaline_grn WHERE retgrn_uniqueid = '{$retgrnd_uniqueid}'", true);
            $poDatat['fpo_vendorId'] = $grnMainData['retgrn_vendor'];
            $poDatat['fpo_vendorName'] = $db->getItemFromDB("SELECT stpa_Fname FROM finascop_stock_party WHERE stpa_id = {$grnMainData['retgrn_vendor']}");
            $poDatat['fpo_poNumber'] = getNewPONumber($_POST['retgrn_billingTo']);
            $poDatat['fpo_poDate'] = date('Y-m-d');
            $poDatat['fpo_poOrderedby'] = $grnMainData['retgrn_createdby'];
            $poDatat['fpo_poFinalValue'] = $grnMainData['retgrn_poValue'];
            $poDatat['branch_id'] = $grnMainData['branch_id'];
            $poDatat['fpo_paymentTerms'] = 'Net 15';
            $poDatat['fpo_validityType'] = 15;
            $poDatat['fpo_validDate'] = date('Y-m-d', strtotime($poDatat['fpo_poDate'] . " + {$poDatat['fpo_validityType']} days"));
            $poDatat['fpo_centralStore'] = $grnMainData['retgrn_billingTo'];
            $poDatat['fpo_updatedby'] = $grnMainData['retgrn_createdby'];
            $poDatat['fpo_createdon'] = date("Y-m-d H:i:s");
            $poDatat['fpo_createdon'] = date("Y-m-d H:i:s");

            $poDatat['fpo_potype'] = 3;

            //print_r($poDatat);
            $status = $db->perform('finascop_purchase_order', $poDatat);
            $poId = $db->insert_id();

            $poDetailsData['fpod_fpoId'] = $poId;
            $poDetailsData['fpod_vendorid'] = $grnMainData['retgrn_vendor'];
            $grnItems = $db->getMultipleData("SELECT * FROM  retaline_grn_details WHERE retgrnd_uniqueid = '{$retgrnd_uniqueid}'", true);
            $poValue = 0;
            foreach ($grnItems as $grnItem) {

                //update projected stock to remove projected stock of that vendor
                $status = $db->query("UPDATE retaline_procurement_details SET rpd_status = 1 WHERE rpd_stitId = {$grnItem['retgrnd_itemid']} AND rpd_vendor = {$poDatat['fpo_vendorId']} AND rpd_date = '{$poDatat['fpo_poDate']}' AND rpd_branch = {$poDatat['fpo_centralStore']} and rpd_status = 0");

                $taxRate = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$grnItem['retgrnd_itemid']}");

                $poDetailsData['fpod_itemid'] = $grnItem['retgrnd_itemid'];
                $poDetailsData['fpod_itemname'] = $grnItem['retgrnd_itemname'];
                $poDetailsData['fpod_itemid'] = $grnItem['retgrnd_itemid'];
                $poDetailsData['fpod_itemid'] = $grnItem['retgrnd_itemid'];
                $poDetailsData['fpod_isRRP'] = 0;
                $poDetailsData['fpod_itemmrp'] = $grnItem['retgrnd_itemmrp'];
                $poDetailsData['fpod_itemqty'] = $grnItem['retgrnd_itemqty'];
                $poDetailsData['fpod_itempoqty'] = $grnItem['retgrnd_itemqty'];
                $poDetailsData['fpod_totalqty'] = $grnItem['retgrnd_itemqty'];
                $poDetailsData['fpod_receivedqty'] = 0;
                $poDetailsData['fpod_balanceqty'] = $grnItem['retgrnd_itemqty'];
                $poDetailsData['fpod_itemoffrrate'] = $grnItem['retgrnd_itemoffrrate'];
                $poDetailsData['fpod_itemoffrrateet'] = (floatval($grnItem['retgrnd_itemoffrrate']) * 100) / (100 + floatval($taxRate));
                $fpod_amount = $poDetailsData['fpod_itemqty'] * $poDetailsData['fpod_itemoffrrate'];
                $fpod_amount = round($fpod_amount, 2);
                $poDetailsData['fpod_amount'] = $fpod_amount;
                $poDetailsData['fpod_netamount'] = $fpod_amount;
                $poValue += $poDetailsData['fpod_netamount'];
                $fpod_netamountet = floatval($poDetailsData['fpod_netamount'] * 100) / (100 + floatval($taxRate));
                $fpod_netamountet = round($fpod_netamountet, 2);
                $fpod_itemoffrratech = $poDetailsData['fpod_netamount'] / $grnItem['retgrnd_itemqty'];
                $poDetailsData['fpod_itemoffrratech'] = round($fpod_itemoffrratech, 2);
                $fpod_itemoffrrateetch = ($poDetailsData['fpod_itemoffrratech'] * 100) / (100 + $taxRate);
                $poDetailsData['fpod_itemoffrrateetch'] = round($fpod_itemoffrrateetch, 2);
                $poDetailsData['fpod_itemaddidisc'] = 0;
                $fpod_effectiverate = $fpod_netamountet / $poDetailsData['fpod_totalqty'];
                $poDetailsData['fpod_effectiverate'] = round($fpod_effectiverate, 2);

                $fpod_pogstAmt = $poDetailsData['fpod_netamount'] - $fpod_netamountet;
                $fpod_effectiverategst = $fpod_pogstAmt / $poDetailsData['fpod_totalqty'];
                $fpod_effectiverategst = round($fpot_effectiverategst, 2);

                $poDetailsData['fpod_poLandingCost'] = $poDetailsData['fpod_effectiverate'] + $fpod_effectiverategst;
                $poDetailsData['fpod_poMMG'] = $poDetailsData['fpod_itemmrp'] - $poDetailsData['fpod_poLandingCost'];


                $qry = "SELECT stdpckl11_package_type_id,stdpckl21_package_type_id,stdpckl2_nos,stdpckl31_package_type_id,stdpckl3_nos,stdpckl41_package_type_id,stdpckl4_nos,stit_GST,csb_package_type_name,cs_nos,stdpckl1_nos,"
                        . "cs_package_type_name,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,ccs_package_type_id,ccs_package_type_name,rs_package_type_id,rs_package_type_name FROM finascop_stock_itemmaster m WHERE m.stit_ID ='{$grnItem['retgrnd_itemid']}' ";
                $itemHistory = $db->getFromDB($qry, true);
                if ($grnItem['retgrnd_purchasingUnit'] == $itemHistory['stdpckl12_package_type_id']) {
                    $level = '4';
                    $fpod_leastSKUqty = 1 / $itemHistory['stdpckl1_nos'];
                } else if ($grnItem['retgrnd_purchasingUnit'] == $itemHistory['stdpckl11_package_type_id']) {
                    $level = '3';
                    $fpod_leastSKUqty = 1;
                } else if ($grnItem['retgrnd_purchasingUnit'] == $itemHistory['stdpckl21_package_type_id']) {
                    $level = '2';
                    $fpod_leastSKUqty = 1 * $itemHistory['stdpckl2_nos'];
                } else if ($grnItem['retgrnd_purchasingUnit'] == $itemHistory['stdpckl31_package_type_id']) {
                    $level = '1';
                    $fpod_leastSKUqty = $itemHistory['stdpckl2_nos'] * $itemHistory['stdpckl3_nos'];
                }
                $fpod_leastSKUTotalqty = $poDetailsData['fpod_totalqty'] * $fpod_leastSKUqty;
                $fpod_leastSKUepr = $poDetailsData['fpod_effectiverate'] / $fpod_leastSKUqty;
                $fpod_leastSKUmrp = $poDetailsData['fpod_itemmrp'] / $fpod_leastSKUqty;
                $fpot_leastSKUoffrrateet = $poDetailsData['fpod_itemoffrrateet'] / $fpod_leastSKUqty;

                $poDetailsData['fpod_leastSKUepr'] = round(($fpod_leastSKUepr * 100) / (100 + $itemHistory['stit_GST']), 2);
                $poDetailsData['fpod_leastSKUmrp'] = round($fpod_leastSKUmrp, 2);
                $data['fpot_leastSKUoffrrateet'] = $fpot_leastSKUoffrrateet;
                $poDetailsData['fpod_leastSKUqty'] = $fpod_leastSKUqty;
                $poDetailsData['fpod_leastSKUTotalqty'] = $fpod_leastSKUTotalqty;
                $poDetailsData['fpod_leastSKUBalanceqty'] = $fpod_leastSKUTotalqty;
                $data['fpod_isRRP'] = 0;

                $bmdDetails = $db->getFromDB("SELECT * FROM retaline_margindistributions WHERE is_default = 1", true);
                $poDetailsData['bmd_id'] = $bmdDetails['bmd_id'];
                $bmdDetailsb2b = $db->getFromDB("SELECT * FROM retaline_margindistributionsb2b WHERE is_default = 1", true);
                $poDetailsData['b2bbmd_id'] = $bmdDetailsb2b['bmd_id'];

                $poDetailsData['bmd_margin'] = (float) $poDetailsData['fpod_itemmrp'] - (float) $poDetailsData['fpod_effectiverate'];
                //$poDetailsData['bmd_percent'] = $poDetailsData['bmd_percent'];

                $poDetailsData['bmd_company'] = $bmdDetails['bmd_company'];
                $poDetailsData['bmd_incentive'] = $bmdDetails['bmd_incentive'];
                $poDetailsData['bmd_customer'] = $bmdDetails['bmd_customer'];
                $poDetailsData['bmd_cs'] = $bmdDetails['bmd_hub']; //on 28 december 2020
                $poDetailsData['bmd_distributor'] = $bmdDetails['bmd_distributor'];
                $poDetailsData['bmd_retailor'] = $bmdDetails['bmd_retailor'];
                $poDetailsData['bmd_driver'] = $bmdDetails['bmd_driver'];
                $poDetailsData['bmd_courier'] = $bmdDetails['bmd_courier'];

                $margin['company'] = $poDetailsData['fpod_poMMG'] * ($poDetailsData['bmd_company'] / 100);
                $margin['operations'] = $poDetailsData['fpod_poMMG'] * ($poDetailsData['bmd_incentive'] / 100);
                $margin['cs'] = $poDetailsData['fpod_poMMG'] * ($poDetailsData['bmd_cs'] / 100);
                $margin['distributor'] = $poDetailsData['fpod_poMMG'] * ($poDetailsData['bmd_distributor'] / 100);
                $margin['retailor'] = $poDetailsData['fpod_poMMG'] * ($poDetailsData['bmd_retailor'] / 100);
                $margin['driver'] = $poDetailsData['fpod_poMMG'] * ($poDetailsData['bmd_driver'] / 100);
                $margin['courier'] = $poDetailsData['fpod_poMMG'] * ($poDetailsData['bmd_courier'] / 100);

                $fpod_spHmDel = $poDetailsData['fpod_poLandingCost'] + ($margin['company'] + $margin['operations'] + $margin['cs'] + $margin['distributor'] + $margin['retailor'] + $margin['driver']);
                $poDetailsData['fpod_spHmDel'] = round($fpod_spHmDel, 2);
                $fpod_spCouDel = $poDetailsData['fpod_poLandingCost'] + ($margin['company'] + $margin['operations'] + $margin['cs'] + $margin['distributor'] + $margin['retailor'] + $margin['courier']);
                $poDetailsData['fpod_spCouDel'] = round($fpod_spCouDel, 2);
                $fpod_spPikup = $poDetailsData['fpod_poLandingCost'] + ($margin['company'] + $margin['operations'] + $margin['cs'] + $margin['distributor'] + $margin['retailor'] );
                $poDetailsData['fpod_spPikup'] = round($fpod_spPikup, 2);

                $fpod_spetHmDel = ($poDetailsData['fpod_spHmDel'] * 100) / (100 + $itemHistory['stit_GST']);
                $poDetailsData['fpod_spetHmDel'] = round($fpod_spetHmDel, 2);
                $fpod_spetCouDel = ($poDetailsData['fpod_spCouDel'] * 100) / (100 + $itemHistory['stit_GST']);
                $poDetailsData['fpod_spetCouDel'] = round($fpod_spetCouDel, 2);
                $fpod_spetPikup = ($poDetailsData['fpod_spPikup'] * 100) / (100 + $itemHistory['stit_GST']);
                $poDetailsData['fpod_spetPikup'] = round($fpod_spetPikup, 2);

                $poDetailsData['fpod_gstHmDel'] = $poDetailsData['fpod_spHmDel'] - $poDetailsData['fpod_spetHmDel'];
                $poDetailsData['fpod_gstCouDel'] = $poDetailsData['fpod_spCouDel'] - $poDetailsData['fpod_spetCouDel'];
                $poDetailsData['fpod_gstPikup'] = $poDetailsData['fpod_spPikup'] - $poDetailsData['fpod_spetPikup'];

                $poDetailsData['fpod_marginHmDel'] = $poDetailsData['fpod_spetHmDel'] - $poDetailsData['fpod_effectiverate']; //fpod_effectiverate,fpod_poLandingCost
                $poDetailsData['fpod_marginCouDel'] = $poDetailsData['fpod_spetCouDel'] - $poDetailsData['fpod_effectiverate'];
                $poDetailsData['fpod_marginPikup'] = $poDetailsData['fpod_spetPikup'] - $poDetailsData['fpod_effectiverate'];

                $fpod_companyMargin = $poDetailsData['fpod_marginHmDel'] * ($poDetailsData['bmd_company'] / ($poDetailsData['bmd_company'] + $poDetailsData['bmd_incentive'] + $poDetailsData['bmd_cs'] + $poDetailsData['bmd_distributor'] + $poDetailsData['bmd_retailor'] + $poDetailsData['bmd_driver']));
                $poDetailsData['fpod_companyMarginHD'] = floor($fpod_companyMargin * 100) / 100;
                $fpod_incentiveMargin = $poDetailsData['fpod_marginHmDel'] * ($poDetailsData['bmd_incentive'] / ($poDetailsData['bmd_company'] + $poDetailsData['bmd_incentive'] + $poDetailsData['bmd_cs'] + $poDetailsData['bmd_distributor'] + $poDetailsData['bmd_retailor'] + $poDetailsData['bmd_driver']));
                $poDetailsData['fpod_incentiveMarginHD'] = floor($fpod_incentiveMargin * 100) / 100;
                $fpod_csMargin = $poDetailsData['fpod_marginHmDel'] * ($poDetailsData['bmd_cs'] / ($poDetailsData['bmd_company'] + $poDetailsData['bmd_incentive'] + $poDetailsData['bmd_cs'] + $poDetailsData['bmd_distributor'] + $poDetailsData['bmd_retailor'] + $poDetailsData['bmd_driver']));
                $poDetailsData['fpod_csMarginHD'] = floor($fpod_csMargin * 100) / 100;
                $fpod_distributorMargin = $poDetailsData['fpod_marginHmDel'] * ($poDetailsData['bmd_distributor'] / ($poDetailsData['bmd_company'] + $poDetailsData['bmd_incentive'] + $poDetailsData['bmd_cs'] + $poDetailsData['bmd_distributor'] + $poDetailsData['bmd_retailor'] + $poDetailsData['bmd_driver']));
                $poDetailsData['fpod_distributorMarginHD'] = floor($fpod_distributorMargin * 100) / 100;
                $fpod_retailorMargin = $poDetailsData['fpod_marginHmDel'] * ($poDetailsData['bmd_retailor'] / ($poDetailsData['bmd_company'] + $poDetailsData['bmd_incentive'] + $poDetailsData['bmd_cs'] + $poDetailsData['bmd_distributor'] + $poDetailsData['bmd_retailor'] + $poDetailsData['bmd_driver']));
                $poDetailsData['fpod_retailorMarginHD'] = floor($fpod_retailorMargin * 100) / 100;
                $fpod_driverMargin = $poDetailsData['fpod_marginHmDel'] * ($poDetailsData['bmd_driver'] / ($poDetailsData['bmd_company'] + $poDetailsData['bmd_incentive'] + $poDetailsData['bmd_cs'] + $poDetailsData['bmd_distributor'] + $poDetailsData['bmd_retailor'] + $poDetailsData['bmd_driver']));
                $poDetailsData['fpod_driverMarginHD'] = floor($fpod_driverMargin * 100) / 100;


                $fpod_companyMargin = $poDetailsData['fpod_marginCouDel'] * ($poDetailsData['bmd_company'] / ($poDetailsData['bmd_company'] + $poDetailsData['bmd_incentive'] + $poDetailsData['bmd_cs'] + $poDetailsData['bmd_distributor'] + $poDetailsData['bmd_retailor'] + $poDetailsData['bmd_courier']));
                $poDetailsData['fpod_companyMarginCD'] = floor($fpod_companyMargin * 100) / 100;
                $fpod_incentiveMargin = $poDetailsData['fpod_marginCouDel'] * ($poDetailsData['bmd_incentive'] / ($poDetailsData['bmd_company'] + $poDetailsData['bmd_incentive'] + $poDetailsData['bmd_cs'] + $poDetailsData['bmd_distributor'] + $poDetailsData['bmd_retailor'] + $poDetailsData['bmd_courier']));
                $poDetailsData['fpod_incentiveMarginCD'] = floor($fpod_incentiveMargin * 100) / 100;
                $fpod_csMargin = $poDetailsData['fpod_marginCouDel'] * ($poDetailsData['bmd_cs'] / ($poDetailsData['bmd_company'] + $poDetailsData['bmd_incentive'] + $poDetailsData['bmd_cs'] + $poDetailsData['bmd_distributor'] + $poDetailsData['bmd_retailor'] + $poDetailsData['bmd_courier']));
                $poDetailsData['fpod_csMarginCD'] = floor($fpod_csMargin * 100) / 100;
                $fpod_distributorMargin = $poDetailsData['fpod_marginCouDel'] * ($poDetailsData['bmd_distributor'] / ($poDetailsData['bmd_company'] + $poDetailsData['bmd_incentive'] + $poDetailsData['bmd_cs'] + $poDetailsData['bmd_distributor'] + $poDetailsData['bmd_retailor'] + $poDetailsData['bmd_courier']));
                $poDetailsData['fpod_distributorMarginCD'] = floor($fpod_distributorMargin * 100) / 100;
                $fpod_retailorMargin = $poDetailsData['fpod_marginCouDel'] * ($poDetailsData['bmd_retailor'] / ($poDetailsData['bmd_company'] + $poDetailsData['bmd_incentive'] + $poDetailsData['bmd_cs'] + $poDetailsData['bmd_distributor'] + $poDetailsData['bmd_retailor'] + $poDetailsData['bmd_courier']));
                $poDetailsData['fpod_retailorMarginCD'] = floor($fpod_retailorMargin * 100) / 100;
                $fpod_courierMargin = $poDetailsData['fpod_marginCouDel'] * ($poDetailsData['bmd_courier'] / ($poDetailsData['bmd_company'] + $poDetailsData['bmd_incentive'] + $poDetailsData['bmd_cs'] + $poDetailsData['bmd_distributor'] + $poDetailsData['bmd_retailor'] + $poDetailsData['bmd_courier']));
                $poDetailsData['fpod_courierMarginCD'] = floor($fpod_courierMargin * 100) / 100;

                $fpod_companyMargin = $poDetailsData['fpod_marginPikup'] * ($poDetailsData['bmd_company'] / ($poDetailsData['bmd_company'] + $poDetailsData['bmd_incentive'] + $poDetailsData['bmd_cs'] + $poDetailsData['bmd_distributor'] + $poDetailsData['bmd_retailor']));
                $poDetailsData['fpod_companyMargin'] = floor($fpod_companyMargin * 100) / 100;
                $fpod_incentiveMargin = $poDetailsData['fpod_marginPikup'] * ($poDetailsData['bmd_incentive'] / ($poDetailsData['bmd_company'] + $poDetailsData['bmd_incentive'] + $poDetailsData['bmd_cs'] + $poDetailsData['bmd_distributor'] + $poDetailsData['bmd_retailor']));
                $poDetailsData['fpod_incentiveMargin'] = floor($fpod_incentiveMargin * 100) / 100;
                $fpod_csMargin = $poDetailsData['fpod_marginPikup'] * ($poDetailsData['bmd_cs'] / ($poDetailsData['bmd_company'] + $poDetailsData['bmd_incentive'] + $poDetailsData['bmd_cs'] + $poDetailsData['bmd_distributor'] + $poDetailsData['bmd_retailor']));
                $poDetailsData['fpod_csMargin'] = floor($fpod_csMargin * 100) / 100;
                $fpod_distributorMargin = $poDetailsData['fpod_marginPikup'] * ($poDetailsData['bmd_distributor'] / ($poDetailsData['bmd_company'] + $poDetailsData['bmd_incentive'] + $poDetailsData['bmd_cs'] + $poDetailsData['bmd_distributor'] + $poDetailsData['bmd_retailor']));
                $poDetailsData['fpod_distributorMargin'] = floor($fpod_distributorMargin * 100) / 100;
                $fpod_retailorMargin = $poDetailsData['fpod_marginPikup'] * ($poDetailsData['bmd_retailor'] / ($poDetailsData['bmd_company'] + $poDetailsData['bmd_incentive'] + $poDetailsData['bmd_cs'] + $poDetailsData['bmd_distributor'] + $poDetailsData['bmd_retailor']));
                $poDetailsData['fpod_retailorMargin'] = floor($fpod_retailorMargin * 100) / 100;

                $poDetailsData['fpod_customerRateHmDel'] = round($poDetailsData['fpod_spetHmDel'] / $poDetailsData['fpod_leastSKUqty'], 2);
                $poDetailsData['fpod_customerRateCouDel'] = round($poDetailsData['fpod_spetCouDel'] / $poDetailsData['fpod_leastSKUqty'], 2);
                $poDetailsData['fpod_customerRatePikup'] = round($poDetailsData['fpod_spetPikup'] / $poDetailsData['fpod_leastSKUqty'], 2);


                $fpod_b2bCSsp = $poDetailsData['fpod_poLandingCost'] + (($poDetailsData['fpod_poMMG'] * $bmdDetailsb2b['bmd_company'] / 100) + ($poDetailsData['fpod_poMMG'] * $bmdDetailsb2b['bmd_management'] / 100) + ($poDetailsData['fpod_poMMG'] * $bmdDetailsb2b['bmd_cs'] / 100));
                $poDetailsData['fpod_b2bCSsp'] = round($fpod_b2bCSsp, 2);
                $fpod_b2bRetailsp = $poDetailsData['fpod_poLandingCost'] + (($poDetailsData['fpod_poMMG'] * $bmdDetailsb2b['bmd_company'] / 100) + ($poDetailsData['fpod_poMMG'] * $bmdDetailsb2b['bmd_management'] / 100) + ($poDetailsData['fpod_poMMG'] * $bmdDetailsb2b['bmd_cs'] / 100) + ($poDetailsData['fpod_poMMG'] * $bmdDetailsb2b['bmd_distributor'] / 100));
                $poDetailsData['fpod_b2bRetailsp'] = round($fpod_b2bRetailsp, 2);
                $fpod_b2bCSspet = $poDetailsData['fpod_b2bCSsp'] * 100 / (100 + $itemHistory['stit_GST']);
                $poDetailsData['fpod_b2bCSspet'] = round($fpod_b2bCSspet, 2);
                $fpod_b2bRetailspet = $poDetailsData['fpod_b2bRetailsp'] * 100 / (100 + $itemHistory['stit_GST']);
                $poDetailsData['fpod_b2bRetailspet'] = round($fpod_b2bRetailspet, 2);
                $poDetailsData['fpod_b2bCSgst'] = $poDetailsData['fpod_b2bCSsp'] - $poDetailsData['fpod_b2bCSspet'];
                $poDetailsData['fpod_b2bRetailgst'] = $poDetailsData['fpod_b2bRetailsp'] - $poDetailsData['fpod_b2bRetailspet'];

                $poDetailsData['fpod_b2bCSmargin'] = $poDetailsData['fpod_b2bCSspet'] - $poDetailsData['fpod_effectiverate']; //fpod_effectiverate,fpod_poLandingCost
                $poDetailsData['fpod_b2bRetailmargin'] = $poDetailsData['fpod_b2bRetailspet'] - $poDetailsData['fpod_effectiverate'];

                $fpod_b2bcs_companymargin = $poDetailsData['fpod_b2bCSmargin'] * ($bmdDetailsb2b['bmd_company'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_cs']));
                $poDetailsData['fpod_b2bcs_companymargin'] = round($fpod_b2bcs_companymargin, 2);
                $fpod_b2bcs_opermargin = $poDetailsData['fpod_b2bCSmargin'] * ($bmdDetailsb2b['bmd_management'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_cs']));
                $poDetailsData['fpod_b2bcs_opermargin'] = round($fpod_b2bcs_opermargin, 2);
                $fpod_b2bcs_csmargin = $poDetailsData['fpod_b2bCSmargin'] * ($bmdDetailsb2b['bmd_cs'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_cs']));
                $poDetailsData['fpod_b2bcs_csmargin'] = round($fpod_b2bcs_csmargin, 2);

                $fpod_b2bretai_companymargin = $poDetailsData['fpod_b2bRetailmargin'] * ($bmdDetailsb2b['bmd_company'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_distributor'] + $bmdDetailsb2b['bmd_cs']));
                $poDetailsData['fpod_b2bretai_companymargin'] = round($fpod_b2bretai_companymargin, 2);
                $fpod_b2bretai_opermargin = $poDetailsData['fpod_b2bRetailmargin'] * ($bmdDetailsb2b['bmd_management'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_distributor'] + $bmdDetailsb2b['bmd_cs']));
                $poDetailsData['fpod_b2bretai_opermargin'] = round($fpod_b2bretai_opermargin, 2);
                $fpod_b2bretai_csmargin = $poDetailsData['fpod_b2bRetailmargin'] * ($bmdDetailsb2b['bmd_cs'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_distributor'] + $bmdDetailsb2b['bmd_cs']));
                $poDetailsData['fpod_b2bretai_csmargin'] = round($fpod_b2bretai_csmargin, 2);
                $fpod_b2bretai_dtrbtrmargin = $poDetailsData['fpod_b2bRetailmargin'] * ($bmdDetailsb2b['bmd_distributor'] / ($bmdDetailsb2b['bmd_company'] + $bmdDetailsb2b['bmd_management'] + $bmdDetailsb2b['bmd_distributor'] + $bmdDetailsb2b['bmd_cs']));
                $poDetailsData['fpod_b2bretai_dtrbtrmargin'] = round($fpod_b2bretai_dtrbtrmargin, 2);

                $poDetailsData['fpod_leastSKUb2bCSsp'] = round($poDetailsData['fpod_b2bCSspet'] / $poDetailsData['fpod_leastSKUqty'], 2);
                $poDetailsData['fpod_leastSKUb2bRetailsp'] = round($poDetailsData['fpod_b2bRetailspet'] / $poDetailsData['fpod_leastSKUqty'], 2);

                $poDetailsData['fpod_hasGift'] = 0;

                $poDetailsData['fpod_purchasingUnit'] = $grnItem['retgrnd_purchasingUnit'];

                $poDetailsData['fpod_leastSKUmargin'] = round($poDetailsData['fpod_leastSKUmrp'] - $poDetailsData['fpod_leastSKUepr'], 2);
                $poDetailsData['fpod_poLandingCostleastSKU'] = round(($poDetailsData['fpod_poLandingCost'] / $poDetailsData['fpod_leastSKUqty']), 2);
                $poDetailsData['fpod_poMMGleastSKU'] = round(($fpoddata['fpod_poMMG'] / $poDetailsData['fpod_leastSKUqty']), 2);
                $poDetailsData['fpod_createdon'] = date("Y-m-d H:i:s");
                //print_r($poDetailsData);
                $podstatus = $db->perform('finascop_purchase_order_details', $poDetailsData);
            }

            $poDatatUp['fpo_poValue'] = $poValue;
            $poDatatUp['fpo_poFinalValue'] = $poValue;
            $status = $db->perform('finascop_purchase_order', $poDatatUp, 'update', " fpo_id = {$poId}");


            //Stockinward
            //print_r($grnMainData);
            $podetailItems = $db->getMultipleData("SELECT * FROM finascop_purchase_order_details WHERE fpod_fpoId = {$poId}", true);
            foreach ($podetailItems as $podetailItem) {
                //echo "pod  finascop_stock_item_inventory insert";
                $fpo_centralStore = $grnMainData['retgrn_billingTo'];
                $stit_GST = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$podetailItem['fpod_itemid']}");
                $fsiidata['stii_itemmasterid'] = $podetailItem['fpod_itemid'];
                $fsiidata['stii_fpoid'] = $poId;
                $fsiidata['stii_fpodid'] = $podetailItem['fpod_id'];
                $totalInventory = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_item_inventory ");
                $batchCurrent = 'RET' . ($totalInventory + 1);
                $fsiidata['stii_batch'] = $batchCurrent;
                $date = date("Y-m-d"); // current date

                $expdate = date("Y-m-d", strtotime("+3 day"));
                $fsiidata['stii_expirydate'] = $expdate;

                $fsiidata['stii_createdon'] = date("Y-m-d H:i:s");
                $fsiidata['stii_createdby'] = $_SESSION['admin']->Finascop_UserId;
                $fsiidata['stii_updatedon'] = date("Y-m-d H:i:s");
                $fsiidata['stii_updatedby'] = $_SESSION['admin']->Finascop_UserId;

                $packageDetails = $db->getFromDB("SELECT csb_package_type_name,csb_package_type_id,cs_nos,cs_package_type_name,cs_package_type_id,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,cos_package_type_id,"
                        . "cosb_package_type_id,dsb_package_type_id,isRRPApplicable,stit_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$podetailItem['fpod_itemid']}", true);
                if ($packageDetails['cs_nos'] == 0) {
                    $packageDetails['cs_nos'] = 1;
                }
                if ($packageDetails['ds_nos'] == 0) {
                    $packageDetails['ds_nos'] = 1;
                }
                if ($packageDetails['cos_nos'] == 0) {
                    $packageDetails['cos_nos'] = 1;
                }

                $fbis['stit_id'] = $podetailItem['fpod_itemid'];
                $fbis['branch_id'] = $grnMainData['retgrn_billingTo'];

                $fbis['mrp'] = $podetailItem['fpod_itemmrp'];
                $fbis['selling_price'] = $podetailItem['fpod_customerRatePikup'];
                $fbis['updated_on'] = date('Y-m-d H:i:s');
                $bmdDetails = $db->getFromDB("SELECT * FROM  retaline_margindistributions WHERE bmd_id = {$podetailItem['bmd_id']}", true);
                $br_PyramidLevel = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch WHERE br_ID = {$grnMainData['retgrn_billingTo']}");

                $hasGift = 0;

                $fsibg['fsbg_leastSKUmrp'] = $podetailItem['fpod_leastSKUmrp'];
                $fsibg['fsbg_leastSKUepr'] = $podetailItem['fpod_leastSKUepr'];
                $fsibg['fsbg_customerRateHmDel'] = $podetailItem['fpod_customerRateHmDel'];
                $fsibg['fsbg_customerRateCouDel'] = $podetailItem['fpod_customerRateCouDel'];
                $fsibg['fsbg_customerRatePikup'] = $podetailItem['fpod_customerRatePikup'];

                $fsibg['fsbg_itemleastSKUptr'] = $podetailItem['fpod_itemleastSKUptr'];
                $fsibg['fsbg_itemleastSKUpts'] = $podetailItem['fpod_itemleastSKUpts'];

                $fsibg['fsbg_leastSKUb2bCSsp'] = $podetailItem['fpod_leastSKUb2bCSsp'];
                $fsibg['fsbg_leastSKUb2bRetailsp'] = $podetailItem['fpod_leastSKUb2bRetailsp'];

                $fsibg['fpod_poLandingCostleastSKU'] = $podetailItem['fpod_poLandingCostleastSKU'];
                $fsibg['fpod_poMMGleastSKU'] = $podetailItem['fpod_poMMGleastSKU'];

                $fsibg['stit_ID'] = $fsiidata['stii_itemmasterid'];
                $fsibg['fsbg_sellinprice'] = $podetailItem['fpod_customerRatePikup'];
                $fsibg['fsbg_mrp'] = $podetailItem['fpod_itemmrp'];
                $fsibg['fsbg_batch'] = $fsiidata['stii_batch'];
                $fsibg['fsbg_expirydate '] = date('Y-m-d', strtotime($fsiidata['stii_expirydate']));

                $fsibg['fsbg_has_gift'] = $hasGift;
                $entryCount = $db->getItemFromDB("SELECT COUNT(1) FROM finascop_stock_item_batch_group WHERE stit_ID = {$fsibg['stit_ID']} AND fsbg_leastSKUmrp = {$podetailItem['fpod_leastSKUmrp']}");
                if ($entryCount == 0) {
                    $itemCount = $db->getItemFromDB("SELECT COUNT(1) FROM finascop_stock_item_batch_group WHERE stit_ID = {$fsibg['stit_ID']}");
                    $fsibgentry = $db->perform('finascop_stock_item_batch_group', $fsibg);
                    $lastId = $db->insert_id();
                    $fsbg_id = $lastId;
                    $stiiIdfsibg['fsbg_id'] = $lastId;
                    $stiiIdfsibg['fsbg_idorg'] = $lastId;

                    $db->query("UPDATE finascop_stock_item_batch_group SET fsbg_name = '" . dechex($fsibg ['stit_ID']) . "/" . (intval($itemCount) + 1) . "'  WHERE fsbg_id = {$lastId} ");
                } else {
                    $stiiIdfsibg['fsbg_id'] = $db->getItemFromDB("SELECT fsbg_id FROM finascop_stock_item_batch_group WHERE stit_ID = {$fsibg['stit_ID']} AND fsbg_leastSKUmrp = {$podetailItem['fpod_leastSKUmrp']}");
                    $stiiIdfsibg['fsbg_idorg'] = $stiiIdfsibg['fsbg_id'];
                    $fsbg_id = $stiiIdfsibg['fsbg_id'];
                }
                $fsiidata['stii_itemmastername'] = $podetailItem['fpod_itemname'];

                $fsiidata['bmdd_id'] = $podetailItem['bmd_id'];

                $fsiidata['stii_package_type2'] = $packageDetails['csb_package_type_id'];
                $fsiidata['stii_package_type3'] = $packageDetails['cs_package_type_id'];
                $fsiidata['stii_package_type4'] = $packageDetails['cos_package_type_id'];
                $fsiidata['stii_qty'] = $podetailItem['fpod_leastSKUTotalqty'];
                $fsiidata['stii_enteredqty'] = $podetailItem['fpod_leastSKUTotalqty'];
                $fsiidata['stii_enteredpT'] = $packageDetails['stit_package_type_id'];

//new in finascop_stock_item_inventory    

                $fsiidata['stii_leastSKUmrp'] = $podetailItem['fpod_leastSKUmrp'];
                $fsiidata['stii_leastSKUepr'] = $podetailItem['fpod_leastSKUepr'];

                $fpod_effectiverate = $podetailItem['fpod_effectiverate'];

                $fsiidata['stii_customerRateHmDel'] = $podetailItem['fpod_customerRateHmDel'];
                $fsiidata['stii_customerRateCouDel'] = $podetailItem['fpod_customerRateCouDel'];
                $fsiidata['stii_customerRatePikup'] = $podetailItem['fpod_customerRatePikup'];

                $fsiidata['stii_itemleastSKUptr'] = $podetailItem['fpod_itemleastSKUptr'];
                $fsiidata['stii_itemleastSKUpts'] = $podetailItem['fpod_itemleastSKUpts'];

                $fsiidata['stii_leastSKUb2bCSsp'] = $podetailItem['fpod_leastSKUb2bCSsp'];
                $fsiidata['stii_leastSKUb2bRetailsp'] = $podetailItem['fpod_leastSKUb2bRetailsp'];

                $fsiidata['stii_poLandingCostleastSKU'] = $podetailItem['fpod_poLandingCostleastSKU'];
                $fsiidata['stii_poMMGleastSKU'] = $podetailItem['fpod_poMMGleastSKU'];

                //new in finascop_stock_branch_inventory    
                $fbis['fpod_leastSKUmrp'] = $podetailItem['fpod_leastSKUmrp'];
                $fbis['fpod_leastSKUepr'] = $podetailItem['fpod_leastSKUepr'];
                $fbis['fpod_customerRateHmDel'] = $podetailItem['fpod_customerRateHmDel'];
                $fbis['fpod_customerRateCouDel'] = $podetailItem['fpod_customerRateCouDel'];
                $fbis['fpod_customerRatePikup'] = $podetailItem['fpod_customerRatePikup'];

                $fbis['fpod_itemleastSKUptr'] = $podetailItem['fpod_itemleastSKUptr'];
                $fbis['fpod_itemleastSKUpts'] = $podetailItem['fpod_itemleastSKUpts'];

                $fbis['fpod_leastSKUb2bCSsp'] = $podetailItem['fpod_leastSKUb2bCSsp'];
                $fbis['fpod_leastSKUb2bRetailsp'] = $podetailItem['fpod_leastSKUb2bRetailsp'];

                $fbis['fpod_poLandingCostleastSKU'] = $podetailItem['fpod_poLandingCostleastSKU'];
                $fbis['fpod_poMMGleastSKU'] = $podetailItem['fpod_poMMGleastSKU'];
//print_r($fbis);
                $fsiidata['stii_cpd'] = $grnMainData['retgrn_billingTo'];
                $fsiidata['stii_mrp'] = $podetailItem['fpod_itemmrp'];
                $fsiidata['stii_selpri'] = $podetailItem['fpod_customerRatePikup'];
                $fsiidata['stii_epraft'] = $podetailItem['fpod_effectiverate'];
                $fsiidata['stii_eprbft'] = ((float) $podetailItem['fpod_effectiverate'] / (100 + (float) $stit_GST)) * 100;
                $fsiidata['stii_created_level'] = $br_PyramidLevel;
//print_r($fsiidata);
                $fsiistatus = $db->perform('finascop_stock_item_inventory', $fsiidata);
                $stiiId['stii_id'] = $db->insert_id();
                $stiiId['stiid_status'] = 1;
                $stiiId['cpd_branch_id'] = $grnMainData['retgrn_billingTo'];
                $stiiId['is_branch'] = 0;
                $stiiId['bmdd_id'] = $fsiidata['bmdd_id'];


                $fpoddata['fpod_receivedqty'] = $podetailItem['fpod_leastSKUTotalqty'];
                $fpoddata['fpod_balanceqty'] = 0;

                $fpoddata['fpod_leastSKUreceivedqty'] = $podetailItem['fpod_leastSKUTotalqty'];
                $fpoddata['fpod_leastSKUBalanceqty'] = (int) $podetailItem['fpod_leastSKUTotalqty'] - (int) $fpoddata['fpod_leastSKUreceivedqty'];
                if ($fpoddata['fpod_leastSKUBalanceqty'] == 0) {
                    $podsvstatus = 2;
                } else if ($fpoddata['fpod_leastSKUBalanceqty'] == $podetailItem['fpod_leastSKUTotalqty']) {
                    $podsvstatus = 0;
                } else {
                    $podsvstatus = 1;
                }
                $fpoddata['fpod_stockVerificationStatus'] = $podsvstatus;
                //echo "pod det updation";
                //print_r($fpoddata);
                $podstatus = $db->perform('finascop_purchase_order_details', $fpoddata, 'update', " fpod_id = {$podetailItem['fpod_id']}");

                if ($packageDetails['isRRPApplicable'] == 1) {
                    $efbe = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory WHERE stit_id = {$fbis['stit_id']} AND branch_id = {$fbis['branch_id']} "); //AND fsbg_id = {$fsbg_id}
                    $remainingCount = $db->getFromDB("SELECT item_count,fpod_customerRateHmDel,fpod_customerRateCouDel,fpod_customerRatePikup,fpod_itemleastSKUptr,fpod_itemleastSKUpts,fpod_leastSKUb2bRetailsp,"
                            . "fpod_poLandingCostleastSKU,fpod_poMMGleastSKU FROM finascop_stock_branch_inventory WHERE stit_id = {$fbis['stit_id']} "); //AND fpod_leastSKUmrp = {$fsiidata['stii_leastSKUmrp']}
                } else {
                    $efbe = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory WHERE stit_id = {$fbis['stit_id']} AND branch_id = {$fbis['branch_id']} AND fsbg_id = {$fsbg_id}");
                    $remainingCount = $db->getFromDB("SELECT item_count,fpod_customerRateHmDel,fpod_customerRateCouDel,fpod_customerRatePikup,fpod_itemleastSKUptr,fpod_itemleastSKUpts,fpod_leastSKUb2bRetailsp,"
                            . "fpod_poLandingCostleastSKU,fpod_poMMGleastSKU FROM finascop_stock_branch_inventory WHERE stit_id = {$fbis['stit_id']} AND fpod_leastSKUmrp = {$fsiidata['stii_leastSKUmrp']}");
                }

                if ($remainingCount['item_count'] > 0) {
                    $totalItemCount = $remainingCount['item_count'] + $podetailItem['fpod_leastSKUTotalqty'];

                    $totalfpod_leastSKUepr = ($podetailItem['fpod_leastSKUepr'] * $podetailItem['fpod_leastSKUTotalqty']) + ($remainingCount['item_count'] * $remainingCount['fpod_leastSKUepr']);
                    $fpod_leastSKUepr = $totalfpod_leastSKUepr / $totalItemCount;
                    $podetailItem['fpod_leastSKUepr'] = round($fpod_leastSKUepr, 2);
                    $totalfpod_customerRateHmDel = ($podetailItem['fpod_customerRateHmDel'] * $podetailItem['fpod_leastSKUTotalqty']) + ($remainingCount['item_count'] * $remainingCount['fpod_customerRateHmDel']);
                    $fpod_customerRateHmDel = $totalfpod_customerRateHmDel / $totalItemCount;
                    $podetailItem['fpod_customerRateHmDel'] = round($fpod_customerRateHmDel, 2);
                    $podetailItem['fpod_customerRateCouDel'] = $podetailItem['fpod_customerRateCouDel'];
                    $totalfpod_customerRatePikup = ($podetailItem['fpod_customerRatePikup'] * $podetailItem['fpod_leastSKUTotalqty']) + ($remainingCount['item_count'] * $remainingCount['fpod_customerRatePikup']);
                    $fpod_customerRatePikup = $totalfpod_customerRatePikup / $totalItemCount;
                    $podetailItem['fpod_customerRatePikup'] = round($fpod_customerRatePikup, 2);
                    $totalfpod_itemleastSKUptr = ($podetailItem['fpod_itemleastSKUptr'] * $podetailItem['fpod_leastSKUTotalqty']) + ($remainingCount['item_count'] * $remainingCount['fpod_itemleastSKUptr']);
                    $fpod_itemleastSKUptr = $totalfpod_itemleastSKUptr / $totalItemCount;
                    $podetailItem['fpod_itemleastSKUptr'] = round($fpod_itemleastSKUptr, 2);
                    $totalfpod_itemleastSKUpts = ($podetailItem['fpod_itemleastSKUpts'] * $podetailItem['fpod_leastSKUTotalqty']) + ($remainingCount['item_count'] * $remainingCount['fpod_itemleastSKUpts']);
                    $fpod_itemleastSKUpts = $totalfpod_itemleastSKUpts / $totalItemCount;
                    $podetailItem['fpod_itemleastSKUpts'] = round($fpod_itemleastSKUpts, 2);
                    $totalfpod_leastSKUb2bCSsp = ($podetailItem['fpod_leastSKUb2bCSsp'] * $podetailItem['fpod_leastSKUTotalqty']) + ($remainingCount['item_count'] * $remainingCount['fpod_leastSKUb2bCSsp']);
                    $fpod_leastSKUb2bCSsp = $totalfpod_leastSKUb2bCSsp / $totalItemCount;
                    $podetailItem['fpod_leastSKUb2bCSsp'] = round($fpod_leastSKUb2bCSsp, 2);
                    $totalfpod_leastSKUb2bRetailsp = ($podetailItem['fpod_leastSKUb2bRetailsp'] * $podetailItem['fpod_leastSKUTotalqty']) + ($remainingCount['item_count'] * $remainingCount['fpod_leastSKUb2bRetailsp']);
                    $fpod_leastSKUb2bRetailsp = $totalfpod_leastSKUb2bRetailsp / $totalItemCount;
                    $podetailItem['fpod_leastSKUb2bRetailsp'] = round($fpod_leastSKUb2bRetailsp, 2);
                    $totalfpod_poLandingCostleastSKU = ($podetailItem['fpod_poLandingCostleastSKU'] * $podetailItem['fpod_leastSKUTotalqty']) + ($remainingCount['item_count'] * $remainingCount['fpod_poLandingCostleastSKU']);
                    $fpod_poLandingCostleastSKU = $totalfpod_poLandingCostleastSKU / $totalItemCount;
                    $podetailItem['fpod_poLandingCostleastSKU'] = round($fpod_poLandingCostleastSKU, 2);
                    $totalfpod_poMMGleastSKU = ($podetailItem['fpod_poMMGleastSKU'] * $podetailItem['fpod_leastSKUTotalqty']) + ($remainingCount['item_count'] * $remainingCount['fpod_poMMGleastSKU']);
                    $fpod_poMMGleastSKU = $totalfpod_poMMGleastSKU / $totalItemCount;
                    $podetailItem['fpod_poMMGleastSKU'] = round($fpod_poMMGleastSKU, 2);
                } else {
                    $podetailItem['fpod_itemleastSKUptr'] = $podetailItem['fpod_itemleastSKUptr'];
                    $podetailItem['fpod_itemleastSKUpts'] = $podetailItem['fpod_itemleastSKUpts'];

                    $podetailItem['fpod_leastSKUb2bCSsp'] = $podetailItem['fpod_leastSKUb2bCSsp'];
                    $podetailItem['fpod_leastSKUb2bRetailsp'] = $podetailItem['fpod_leastSKUb2bRetailsp'];
                }

                if ($efbe > 0) {

                    $fbisupd['fpod_leastSKUepr'] = $podetailItem['fpod_leastSKUepr'];
                    $fbisupd['fpod_customerRateHmDel'] = $podetailItem['fpod_customerRateHmDel'];
                    $fbisupd['fpod_customerRateCouDel'] = $podetailItem['fpod_customerRateCouDel'];
                    $fbisupd['fpod_customerRatePikup'] = $podetailItem['fpod_customerRatePikup'];
                    $fbisupd['fpod_poLandingCostleastSKU'] = $podetailItem['fpod_poLandingCostleastSKU'];
                    $fbisupd['fpod_poMMGleastSKU'] = $podetailItem['fpod_poMMGleastSKU'];
                    $fbisupd['fpod_itemleastSKUptr'] = $podetailItem['fpod_itemleastSKUptr'];
                    $fbisupd['fpod_itemleastSKUpts'] = $podetailItem['fpod_itemleastSKUpts'];
                    $fbisupd['fpod_leastSKUb2bCSsp'] = $podetailItem['fpod_leastSKUb2bCSsp'];
                    $fbisupd['fpod_leastSKUb2bRetailsp'] = $podetailItem['fpod_leastSKUb2bRetailsp'];



                    $db->query("UPDATE finascop_stock_branch_inventory SET item_count = item_count + {$fsiidata['stii_qty']}  WHERE stit_id = {$podetailItem['fpod_itemid']} AND branch_id = {$grnMainData['retgrn_billingTo']}  AND fsbg_id = {$fsbg_id} ");
                    $status = $db->perform('finascop_stock_branch_inventory', $fbisupd, 'update', " stit_id = {$podetailItem['fpod_itemid']} AND branch_id = {$grnMainData['retgrn_billingTo']} AND fsbg_id = {$fsbg_id}");


                    //$db->query("UPDATE finascop_stock_branch_inventory SET item_count = item_count + {$fsiidata['stii_qty']}  WHERE stit_id = {$podetailItem['fpod_itemid']} AND branch_id = {$grnMainData['retgrn_billingTo']} "); //AND fsbg_id = {$fsbg_id}
                    //$status = $db->perform('finascop_stock_branch_inventory', $fbisupd, 'update', " stit_id = {$podetailItem['fpod_itemid']} AND branch_id = {$grnMainData['retgrn_billingTo']} "); //AND fsbg_id = {$fsbg_id}
                } else {
                    $fbis['item_count'] = $fsiidata['stii_qty'];
                    $fbis['fsbg_id'] = $fsbg_id;
                    $fbis['purchasing_unit'] = $podetailItem['fpod_purchasingUnit'];
                    //echo "finascop_stock_branch_inventory insert";
                    // print_r($fbis);
                    $status = $db->perform('finascop_stock_branch_inventory', $fbis);
                }

                $updatatLog['old_selling_price'] = $podetailItem['fpod_customerRatePikup'];
                $updatatLog['selling_price'] = $podetailItem['fpod_customerRatePikup'];
                $updatatLog['branch_id'] = $grnMainData['retgrn_billingTo'];
                $updatatLog['stit_id'] = $podetailItem['fpod_itemid'];
                $updatatLog['item_count'] = $fsiidata['stii_qty'];
                $updatatLog['fpod_skuPurchaseQty'] = $fsiidata['stii_qty'];
                $updatatLog['updated_on'] = date("Y-m-d H:i:s");
                $updatatLog['updated_by'] = $_SESSION['admin']->Finascop_UserId;
                $updatatLog['type'] = 'On Direct Purchase';
                $updatatLog['action'] = 'On Direct Purchase to branch - ' . $grnMainData['retgrn_billingTo'];
                //$status = $db->perform('finascop_stock_branch_inventory_log', $updatatLog);
                $updatatLog['selling_price'] = NULL;
                $updatatLog['old_selling_price'] = NULL;
                $updatatLog['fpod_skuPurchaseRange'] = NULL;
                $updatatLog['fpod_skuAvgPurchaseRate'] = NULL;
                $updatatLog['fpod_skuLastPurchaseRate'] = NULL;
                $updatatLog['fpod_effectivemargin'] = NULL;

                $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'INVENTORYLOG'");
                $fields_string = json_encode($updatatLog);
                $opts = array(
                    CURLOPT_URL => $url,
                    CURLINFO_CONTENT_TYPE => "application/json",
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_BINARYTRANSFER => TRUE,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_POST => count($fields),
                    CURLOPT_POSTFIELDS => $fields_string,
                    CURLOPT_HTTPHEADER => array('Content-Type: application/json')
                );

                $ch = curl_init();
                curl_setopt_array($ch, $opts);
                $logrresult = curl_exec($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);
                header("Content-Type: application/json");
                //$result = json_decode($datacl, true);
                if ($logrresult != true) {
                    echo '{"success":false, "msg":"Some problem in log insertion."}';
                    exit();
                }
            }

            $poIdup['retgrn_poId'] = $poId;
            $status = $db->perform('retaline_grn', $poIdup, 'update', $adcon);
        }

        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'PO saved.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error while saving data.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'getGoodReceiveNoteRetaline':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 12;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'fpot_createdon' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;

        $filter_qry = " WHERE 1 = 1 ";
        if (isset($_POST['filter'])) {
            $allowedFields = ['grn_id', 'grn_PONumber', 'grn_GRNNumber', 'grn_createdOn', 'grn_vendor_name', 'grn_status'];
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
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
        $date = date('Y-m-d');
        $countDataQuery = "SELECT count(*) from retaline_grn fp  {$filter_qry}  ORDER BY retgrn_createdon DESC";
        $listQuery = "SELECT  retgrn_uniqueid ,retgrn_name,retgrn_createdby,(SELECT stpa_Fname FROM finascop_stock_party WHERE stpa_id = retgrn_vendor) as vendorName,retgrn_status,retgrn_createdon,retgrn_billingTo,
            (SELECT CONCAT(br_Name,' - ',branch_shortname) AS br_Name FROM finascop_branch WHERE br_ID = retgrn_billingTo) as receivedAt 
 FROM retaline_grn fp  {$filter_qry} ORDER BY retgrn_createdon DESC LIMIT $start,$limit ";
        $db->printGridJson($countDataQuery, $listQuery);

        break;
    case 'deleteGRNDetails':
        $db->query('begin');
        $del_query = "DELETE FROM retaline_grn WHERE retgrn_uniqueid='{$_POST['retgrn_uniqueid']}'";
        $temp = $db->query($del_query);
        if ($temp) {
            $del_query = "DELETE FROM retaline_grn_details WHERE retgrnd_uniqueid='{$_POST['retgrn_uniqueid']}'";
            $db->query($del_query);
        }
        $status = $db->query('commit');
        if (status) {
            echo "{success:true,valid:true,message:'Deleted Succesfully ' }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Deleteing.'}";
        }
        break;
    case 'loadGoodReceiveNote':

        $podata = $db->getFromSafe("SELECT  retgrn_uniqueid ,retgrn_name ,DATE_FORMAT(retgrn_createdon,'%d-%m-%Y %H:%i:%s') as retgrn_createdon,retgrn_createdby,retgrn_updatedon,
            retgrn_poValue,retgrn_paymentTerms,retgrn_paymentValue,retgrn_validityType,retgrn_shippingcharge,retgrn_gdiscount,retgrn_gdiscounttype,
            (SELECT stpa_Fname FROM finascop_stock_party WHERE stpa_id = retgrn_vendor) as vendorName,retgrn_vendor,retgrn_billingTo
 FROM retaline_grn fp  {$filter_qry} where retgrn_uniqueid = ?", "s", [$_POST['uniqueid']], true);
        if (!empty($podata)) {
            echo json_encode($podata);
        }

        break;
    case 'printGRNRetDetails':
        ob_start();
        include('podetails.php');
        $resHtml = ob_get_contents();
        ob_end_clean();
        echo $resHtml;
        exit();
        break;
    case 'deleteVendorItemFromGRNRetaline':
        $itemid = $_POST['itemid'];
        $uid = $_POST['uid'];
        $db->query('begin');
        $delquery = "DELETE FROM retaline_grn_details  WHERE retgrnd_uniqueid = '{$uid}' AND retgrnd_itemid = {$itemid}";
        $status = $db->query($delquery);

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Deleted Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while deleting data' }}";
        }
        break;
    case 'getVendorName':
        $qry = $db->getMulipleData("SELECT stpa_id,stpa_Fname FROM finascop_stock_party WHERE stpa_IsVendor = 1 ORDER BY stpa_Fname ASC", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'viewListGRNdetailsStore':
        $poId = $_POST['poId'];
        $countQuery = "SELECT COUNT(*) FROM retaline_grn_details fpod WHERE retgrnd_uniqueid = '{$poId}'";
        $listQuery = "SELECT retgrnd_uniqueid,retgrnd_itemid, retgrnd_itemname, retgrnd_itemqty,retgrnd_itemoffrrate,retgrnd_purchasingUnit,retgrnd_amount,(SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = retgrnd_purchasingUnit) as unitName FROM retaline_grn_details fpod 
                    WHERE retgrnd_uniqueid = '{$poId}' ORDER BY retgrnd_itemid ASC";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'getGRNDetails':
        $poId = $_POST['poId'];
        $podata = $db->getFromDB("SELECT  retgrn_billingTo,retgrn_uniqueid,retgrn_name,retgrn_vendor,branch_id,retgrn_billingTo,DATE_FORMAT(retgrn_date,'%d-%m-%Y') as retgrn_date,"
                . "(SELECT stpa_Fname FROM finascop_stock_party WHERE stpa_id = retgrn_vendor) as stpa_Fname,"
                . "(SELECT br_Name FROM finascop_branch WHERE br_ID = retgrn_billingTo) as centralStore  FROM retaline_grn where retgrn_uniqueid = '{$poId}'", true);
        if (!empty($podata)) {
            echo json_encode($podata);
        }
        break;
    case 'printGRNDetails':
        ob_start();
        include('grnDetails.php');
        $resHtml = ob_get_contents();
        ob_end_clean();
        echo $resHtml;
        exit();
        break;
    case 'getBranch':

        $branch = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch where br_status = 'Active' AND br_PyramidLevel <> 4 {$cond}", true);


        if (!empty($branch)) {
            $branch = json_encode($branch);
            echo '{"data":' . $branch . '}';
        } else {
            echo '{"data":[]}';
        }
        break;
    case 'listDistributionChartItemStore':
        $dc_source = $_POST['dc_source'];
        $dc_ItemId = $_POST['dc_ItemId'];
        $dc_date = date('Y-m-d', strtotime($_POST['dc_date']));
        if (!empty($dc_source) && !empty($dc_ItemId) && !empty($dc_date)) {
            $rec_sort = empty($data['sort']) ? 'br_ID' : $data['sort'];
            $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
            $filter_part = ' where 1=1';

            if (isset($data['filter'])) {
        $allowedFields = ['grn_id', 'grn_PONumber', 'grn_GRNNumber', 'grn_createdOn', 'grn_vendor_name', 'grn_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            $query = "SELECT br_ID,br_Name,"
                    . "(SELECT SUM(fstr_RequiredItemQty) FROM finascop_stock_indent_details fsindet INNER JOIN finascop_stock_indent fsind ON fsind.fstr_id = fsindet.fstr_id WHERE fstr_destination = br_ID AND rsi_date = '{$dc_date}' AND fstr_ItemId = {$dc_ItemId}) AS fstr_RequiredItemQty,"
                    . "(SELECT SUM(rdc_RequiredItemQty) FROM retaline_distribution_chart_details rdcd INNER JOIN retaline_distribution_chart rdc ON rdc.rdc_id = rdcd.rdc_id WHERE rdc_date = '{$dc_date}' AND rdc_ItemId = {$dc_ItemId} and rdcd_status < 2 AND br_ID = rdc.rdc_destination) AS rdc_RequiredItemQty,"
                    . "(SELECT SUM(rdc_ApprovedItemQty) FROM retaline_distribution_chart_details rdcd INNER JOIN retaline_distribution_chart rdc ON rdc.rdc_id = rdcd.rdc_id WHERE rdc_date = '{$dc_date}' AND rdc_ItemId = {$dc_ItemId} and rdcd_status < 2 AND  br_ID = rdc.rdc_destination) AS rdc_ApprovedItemQty,"
                    . "(SELECT SUM(item_count) FROM finascop_stock_branch_inventory WHERE stit_id = {$dc_ItemId} AND branch_id = br_ID) AS rdc_ClosingItemQty,"
                    . "(SELECT least_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$dc_ItemId}) AS unitName FROM  finascop_branch   WHERE  br_cpd = {$dc_source} AND br_status = 'Active'";

            $countQuery = "SELECT COUNT(1) FROM ({$query}) AS dcItemCount {$filter_part} {$cond} ";
            $listQuery = "SELECT * FROM ({$query}) AS dcItems {$filter_part} {$cond}  ";
        }
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'listRetalineDistributionChart':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'rdc_id' : ($sort == 'rdc_createdOn' ? 'rdc_id' : $sort );
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status_name') {
                            if ($field['data']['value'] == 'Chart Created') {
                                //    $field['data']['value'] = 2;
                                $fiterItem = 1;
                                $search .= " and (rdc_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Partially Attended') {
                                //    $field['data']['value'] = 3;
                                $fiterItem = 5;
                                $search .= " and (rdc_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Completely Attended') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 10;
                                $search .= " and (rdc_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Invoke Expired') {
                                //    $field['data']['value'] = 3;
                                $fiterItem = 15;
                                $search .= " and (rdc_status = {$fiterItem}) ";
                            } else {
                                //    $field['data']['value'] = 0;
                                $search .= " and (rdc_status = 1 or rdc_status = 5 or rdc_status = 10 ) ";
                            }
                        }

                        break;
                    case 'date':
                        $datefield = 'rdc_createdOn';
                        switch ($field['data']['comparison']) {
                            case 'gt' :
                                $search .= " and DATE_FORMAT(" . $datefield . ", '%Y-%m-%d') > '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            case 'lt':
                                $search .= " and DATE_FORMAT(" . $datefield . ", '%Y-%m-%d') < '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            case 'eq':
                                $search .= " and DATE_FORMAT(" . $datefield . ", '%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            default:
                                $search .= " and DATE_FORMAT(" . $datefield . ", '%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";

                                break;
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
        $branchName = $_POST['branchName'];
        $br_ID = empty($branchName) ? $_SESSION['admin']->finascop_current_branch_id : $branchName;

        if ($_SESSION['admin']->br_PyramidLevel == 1)
            $where = " AND rdc_source =" . $br_ID;
        else
            $where = " AND rdc_source =" . $_SESSION['admin']->finascop_current_branch_id;
        $countQuery = "SELECT COUNT(*) FROM (SELECT rdc_id,rdc_uid,rdc_source,rdc_destination,rdc_status,"
                . "CASE WHEN rdc_type=3 THEN 'Sales Order' WHEN rdc_type=1 THEN 'Transfer Invoked' WHEN rdc_status=2 THEN 'Stock Requested' ELSE 'Transfer Requested' END AS rdc_type,"
                . "fssi_status AS status_name,"
                . "DATE_FORMAT(rdc_createdOn,'%d %M %Y') as rdc_createdOn,"
                . "rdc_initiatedBy,(SELECT br_Name FROM finascop_branch WHERE br_ID = rdc_initiatedBy) as initiatedBranch,"
                . "(SELECT br_Name FROM finascop_branch where br_ID = rdc_source) as sourcename,"
                . "(SELECT br_Name FROM finascop_branch where br_ID = rdc_destination) as branch  FROM retaline_distribution_chart INNER JOIN finascop_stock_transfer_request_status ON fstrs_id = rdc_status ) AS countDC {$search} {$where}";

        $listQuery = "SELECT * FROM (SELECT rdc_id,rdc_uid,rdc_source,rdc_destination,rdc_status,"
                . "CASE WHEN rdc_type=3 THEN 'Sales Order' WHEN rdc_type=1 THEN 'Transfer Invoked' WHEN rdc_status=2 THEN 'Stock Requested' ELSE 'Transfer Requested' END AS rdc_type,"
                . "fssi_status AS status_name,"
                . "DATE_FORMAT(rdc_createdOn,'%d %M %Y') as rdc_createdOn,"
                . "rdc_initiatedBy,(SELECT br_Name FROM finascop_branch WHERE br_ID = rdc_initiatedBy) as initiatedBranch,"
                . "(SELECT br_Name FROM finascop_branch where br_ID = rdc_source) as sourcename,"
                . "(SELECT br_Name FROM finascop_branch where br_ID = rdc_destination) as branch  FROM retaline_distribution_chart INNER JOIN finascop_stock_transfer_request_status ON fstrs_id = rdc_status   ) AS listDc {$search} {$where} ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'getItemName':
        if ($_POST['query'] != '') {
            $que = addslashes($_POST['query']);
            $searchQuery = " AND stit_SKU LIKE '%{$que}%'";
        } else {
            $searchQuery = '';
        }
        if ($_POST['sourceBranch'] > 0) {
            $br_PyramidLevel = $db->getItemSafe("SELECT br_PyramidLevel FROM finascop_branch WHERE br_ID = ?", "i", [$_POST['sourceBranch']]);
            switch ($br_PyramidLevel) {
                case 2:
                    $packageName = 'cs_package_type_name';
                    break;
                case 3:
                    $packageName = 'ds_package_type_name';
                    break;
                case 4:
                    $packageName = 'cos_package_type_name';
                    break;
            }
        }

        $qry = $db->getMulipleData("SELECT fsi.stit_ID as stit_ID,stit_itemName,stit_SKU,cs_package_type_name,{$packageName} as packageName FROM finascop_stock_itemmaster fsi "
                . "INNER  JOIN finascop_stock_branch_inventory ON finascop_stock_branch_inventory.stit_ID =  fsi.stit_id  where 1=1 AND stit_status = 1  AND directPurchase = 1 AND item_count > 0 AND  branch_id = {$_POST['sourceBranch']}  {$searchQuery}", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'toLoadAllStocks':
        $dc_source = $_POST['dc_source'];
        $dc_ItemId = $_POST['dc_ItemId'];
        $dc_date = date('Y-m-d', strtotime($_POST['dc_date']));
        $podata['inStock'] = $db->getItemFromDB("SELECT item_count FROM finascop_stock_branch_inventory WHERE stit_id = {$dc_ItemId} AND branch_id = {$dc_source}");
        $podata['allottedStock'] = $db->getItemFromDB("SELECT SUM(rdc_ApprovedItemQty) FROM retaline_distribution_chart_details INNER JOIN retaline_distribution_chart ON retaline_distribution_chart.rdc_id = retaline_distribution_chart_details.rdc_id WHERE rdc_ItemId = {$dc_ItemId} AND rdc_date = '{$dc_date}' AND rdc_source = {$dc_source} and rdcd_status < 2 GROUP BY rdc_ItemId");
        $rpd_quantity = $db->getItemFromDB("SELECT SUM(rpd_quantity) FROM retaline_procurement_details WHERE rpd_stitId = {$dc_ItemId} AND rpd_date = '{$dc_date}' AND rpd_branch = {$dc_source} and rpd_status = 0 GROUP BY rpd_stitId");
        $podata['projectedStock'] = $rpd_quantity;

        if (empty($podata['projectedStock'])) {
            $podata['projectedStock'] = 0;
        }
        if (empty($podata['inStock'])) {
            $podata['inStock'] = 0;
        }
        if (empty($podata['allottedStock'])) {
            $podata['allottedStock'] = 0;
        }
        $podata['success'] = true;
        $podata['valid'] = true;
        if (!empty($podata)) {
            echo json_encode($podata);
        }
        break;
    case 'saveDistributionChart':
        $dc_ItemId = $_POST['dc_ItemId'];
        $dc_source = $_POST['dc_source'];
        $dc_date = date('Y-m-d', strtotime($_POST['dc_date']));

        $griddata = json_decode(stripslashes($_POST['dcItems']));
        $griddata = (array) $griddata;
        //$db->query('begin');

        for ($i = 0; $i < count($griddata); $i++) {
            if ($griddata[$i]->rdc_packedItemQty > 0) {
                if ($griddata[$i]->fstr_RequiredItemQty > 0) {
                    $fstr_RequiredItemQty = $griddata[$i]->fstr_RequiredItemQty;
                } else {
                    $fstr_RequiredItemQty = 0;
                }

                $packTypes = $db->getFromDB("SELECT stdpckl11_package_type_id,stdpckl21_package_type_id,stdpckl31_package_type_id,stdpckl41_package_type_id,least_package_type_name,least_package_type_id,stdpckl1_nos,"
                        . "stdpckl2_nos,stdpckl3_nos FROM finascop_stock_itemmaster WHERE stit_ID = {$dc_ItemId}", true);
                //print_r($packTypes);



                $rdc_id = $db->getItemFromDB("SELECT rdc_id FROM retaline_distribution_chart WHERE rdc_date = '{$dc_date}' AND rdc_source = {$dc_source} AND rdc_destination = {$griddata[$i]->br_ID} AND rdc_status = 1 ");
                $purchasingInit = $db->getItemFromDB("SELECT purchasing_unit FROM finascop_stock_branch_inventory WHERE stit_id = {$dc_ItemId} AND branch_id = {$dc_source}");

                if ($rdc_id > 0) {
                    $rdcd_id = $db->getItemFromDB("SELECT rdcd_id FROM retaline_distribution_chart_details WHERE rdc_id = {$rdc_id} and rdc_ItemId = {$dc_ItemId} ");
                    if ($rdcd_id > 0) {
                        $rpdUData['rdc_RequiredItemQty'] = $fstr_RequiredItemQty;
                        $rdc_ApprovedItemQty = $db->getItemFromDB("SELECT rdc_ApprovedItemQty FROM retaline_distribution_chart_details WHERE rdcd_id = {$rdcd_id}");
                        $rdc_ApprovedItemQty = $rdc_ApprovedItemQty + $griddata[$i]->rdc_packedItemQty;

                        if ($purchasingInit == $packTypes['stdpckl12_package_type_id']) {
                            $level = '4';
                            $fpot_leastSKUqty = $rdc_ApprovedItemQty / $packTypes['stdpckl1_nos'];
                        } else if ($purchasingInit == $packTypes['stdpckl11_package_type_id']) {
                            $level = '3';
                            $fpot_leastSKUqty = $rdc_ApprovedItemQty;
                        } else if ($purchasingInit == $packTypes['stdpckl21_package_type_id']) {
                            $level = '2';
                            $fpot_leastSKUqty = $rdc_ApprovedItemQty * $packTypes['stdpckl2_nos'];
                        } else if ($purchasingInit == $packTypes['stdpckl31_package_type_id']) {
                            $level = '1';
                            $fpot_leastSKUqty = $rdc_ApprovedItemQty * $packTypes['stdpckl2_nos'] * $packTypes['stdpckl3_nos'];
                        }
                        $rpdUData['rdc_leastSKUCount'] = $fpot_leastSKUqty;

                        $rpdUData['rdc_ApprovedItemQty'] = $rdc_ApprovedItemQty;
                        $rpdUData['rdc_TransferedItemQty'] = $rdc_ApprovedItemQty;
                        $rpdUData['rdcd_updatedOn'] = date("Y-m-d H:i:s");
                        $rpdUData['rdcd_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
                        $status = $db->perform('retaline_distribution_chart_details', $rpdUData, 'update', " rdcd_id = {$rdcd_id} ");
                    } else {
                        $rpdIData['rdc_ItemUnits'] = $purchasingInit;
                        $rpdIData['least_package_type_id'] = $packTypes['least_package_type_id'];
                        $rpdIData['rdc_id'] = $rdc_id;
                        $rpdIData['rdc_ItemId'] = $dc_ItemId;
                        $rpdIData['rdc_RequiredItemQty'] = $fstr_RequiredItemQty;
                        $rpdIData['rdc_ApprovedItemQty'] = $griddata[$i]->rdc_packedItemQty;
                        $rpdIData['rdc_TransferedItemQty'] = $griddata[$i]->rdc_packedItemQty;

                        if ($purchasingInit == $packTypes['stdpckl12_package_type_id']) {
                            $level = '4';
                            $fpot_leastSKUqty = $griddata[$i]->rdc_packedItemQty / $packTypes['stdpckl1_nos'];
                        } else if ($purchasingInit == $packTypes['stdpckl11_package_type_id']) {
                            $level = '3';
                            $fpot_leastSKUqty = $griddata[$i]->rdc_packedItemQty;
                        } else if ($purchasingInit == $packTypes['stdpckl21_package_type_id']) {
                            $level = '2';
                            $fpot_leastSKUqty = $griddata[$i]->rdc_packedItemQty * $packTypes['stdpckl2_nos'];
                        } else if ($purchasingInit == $packTypes['stdpckl31_package_type_id']) {
                            $level = '1';
                            $fpot_leastSKUqty = $griddata[$i]->rdc_packedItemQty * $packTypes['stdpckl2_nos'] * $packTypes['stdpckl3_nos'];
                        }
                        $rpdIData['rdc_leastSKUCount'] = $fpot_leastSKUqty;

                        $rpdIData['rdcd_createdOn'] = date("Y-m-d H:i:s");
                        $rpdIData['rdcd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
                        $status = $db->perform('retaline_distribution_chart_details', $rpdIData);
                    }
                } else {
                    $date = date("Y-m-d H:i");
                    $tdy = date("Y-m-d") . " 00:00";
                    $maxId = $db->getItemFromDB("select right(rdc_uid,3)*1 as rdc_uid  from `retaline_distribution_chart` where `rdc_source` = {$dc_source} and `rdc_date` between '{$tdy}' and '{$date}' order by `rdc_id` desc limit 1");
                    $br_key = $db->getItemFromDB("SELECT br_key FROM finascop_branch WHERE br_ID={$griddata[$i]->br_ID}");
                    $uid_max = getNewDTCNumber($griddata[$i]->br_ID);
                    $rdc['rdc_uid'] = $uid_max;
                    $rdc['rdc_date'] = $dc_date;
                    $rdc['rdc_source'] = $dc_source;
                    $rdc['rdc_destination'] = $griddata[$i]->br_ID;
                    $rdc['rdc_createdOn'] = date("Y-m-d H:i:s");
                    $rdc['rdc_createdBy'] = $_SESSION['admin']->Finascop_UserId;
                    $status = $db->perform('retaline_distribution_chart', $rdc);
                    $newrdc_id = $db->insert_id();

                    $rdcDetails['rdc_id'] = $newrdc_id;
                    $rdcDetails['rdc_ItemUnits'] = $purchasingInit;
                    $lpupDatat['least_package_type_id'] = $packTypes['least_package_type_id'];
                    $rdcDetails['rdc_ItemId'] = $dc_ItemId;
                    $rdcDetails['rdc_RequiredItemQty'] = $fstr_RequiredItemQty;
                    $rdcDetails['rdc_ApprovedItemQty'] = $griddata[$i]->rdc_packedItemQty;
                    $rdcDetails['rdc_TransferedItemQty'] = $griddata[$i]->rdc_packedItemQty;

                    if ($purchasingInit == $packTypes['stdpckl12_package_type_id']) {
                        $level = '4';
                        $fpot_leastSKUqty = $griddata[$i]->rdc_packedItemQty / $packTypes['stdpckl1_nos'];
                    } else if ($purchasingInit == $packTypes['stdpckl11_package_type_id']) {
                        $level = '3';
                        $fpot_leastSKUqty = $griddata[$i]->rdc_packedItemQty;
                    } else if ($purchasingInit == $packTypes['stdpckl21_package_type_id']) {
                        $level = '2';
                        $fpot_leastSKUqty = $griddata[$i]->rdc_packedItemQty * $packTypes['stdpckl2_nos'];
                    } else if ($purchasingInit == $packTypes['stdpckl31_package_type_id']) {
                        $level = '1';
                        $fpot_leastSKUqty = $griddata[$i]->rdc_packedItemQty * $packTypes['stdpckl2_nos'] * $packTypes['stdpckl3_nos'];
                    }
                    $rdcDetails['rdc_leastSKUCount'] = $fpot_leastSKUqty;

                    $rdcDetails['rdcd_createdOn'] = date("Y-m-d H:i:s");
                    $rdcDetails['rdcd_createdBy'] = $_SESSION['admin']->Finascop_UserId;
                    $status = $db->perform('retaline_distribution_chart_details', $rdcDetails);
                }
            }
        }
        //$status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Items updated.'";
            echo '{"success":true,"valid":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error occured while saving.'";
            echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
        }
        break;
    case 'getBranchName':
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch WHERE br_status = 'Active' AND (br_PyramidLevel = 2  OR br_ID={$branch_id})", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'listViewDistributionChartData':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'rdcd_id' : $sort;
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
                                $search .= " and (rdcd_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Ordered') {
                                //    $field['data']['value'] = 3;
                                $fiterItem = 2;
                                $search .= " and (rdcd_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Deleted') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 3;
                                $search .= " and (rdcd_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Packed') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 4;
                                $search .= " and (rdcd_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Partially Received') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 5;
                                $search .= " and (rdcd_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Received') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 6;
                                $search .= " and (rdcd_status = {$fiterItem}) ";
                            } else {
                                //    $field['data']['value'] = 0;
                                $search .= " and (rdcd_status = 1 or rdcd_status = 2 or rdcd_status = 3 or rdcd_status = 4 or rdcd_status = 5 or rdcd_status = 6) ";
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

        $countQry = "SELECT COUNT(*) FROM retaline_distribution_chart_details  {$search} AND rdc_id = " . intval($_POST['rdc_id']) . " AND rdcd_status <> 3";

        $listQry = "SELECT rdc_id,rdcd_id,rdc_ItemId,rdc_TransferedItemQty,rdc_ApprovedItemQty,rdc_RequiredItemQty,rdcd_status,rdc_ItemUnits,rdc_leastSKUCount,least_package_type_id,"
                . "CASE WHEN rdcd_status=1 THEN 'Requested' WHEN rdcd_status=2 THEN 'Ordered' WHEN rdcd_status=3 THEN 'Deleted' WHEN rdcd_status=4 THEN 'Packed' "
                . "WHEN rdcd_status=5 THEN 'Partially Received' WHEN rdcd_status=6 THEN 'Received' ELSE 'Not Saved' END AS status_name FROM retaline_distribution_chart_details {$search} AND rdc_id ={$_POST['rdc_id']} AND rdcd_status <> 3 ORDER BY {$sort} {$dir} ";
        $datas = $db->getMulipleData($listQry, true);
        $rdc_source = $db->getItemSafe("SELECT rdc_source FROM retaline_distribution_chart WHERE rdc_id = ?", "i", [$_POST['rdc_id']]);
        $sourcePyramid = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch where br_ID = {$rdc_source}");
        $resCount = count($datas);
        $count = $db->getItemFromDB($countQry);
        //print_r($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $datas[$i]['rdc_ItemName'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = {$datas[$i]['rdc_ItemId']}");
                $packageType = $db->getFromDB("SELECT isMedicine,cs_package_type_name,ds_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['rdc_ItemId']}", true);
                if ($sourcePyramid == 2) {
                    $packTyp = $packageType['cs_package_type_name'];
                } else if ($sourcePyramid == 3) {
                    $packTyp = $packageType['ds_package_type_name'];
                } else if ($sourcePyramid == 4) {
                    $packTyp = $packageType['ds_package_type_name'];
                }
                if ($packageType['isMedicine'] == 0) {
                    $product_category = $db->getItemFromDB("SELECT product_category FROM finascop_stock_itemmaster where stit_ID = {$datas[$i]['rdc_ItemId']}");
                    $datas[$i]['subcategory'] = $product_category;
                    $category = $db->getFromDB("SELECT main_category,sub_category FROM mypha_productsubcategory where sub_category_id = {$product_category}", true);
                    $datas[$i]['subcategoryname'] = $category['sub_category'];
                    $main_category = $category['main_category'];
                    if ($main_category > 0) {
                        $datas[$i]['category_name'] = $db->getItemFromDB("SELECT category_name FROM mypha_productcategory where category_id = {$main_category}");
                    } else {
                        $datas[$i]['category_name'] = '';
                    }
                } else {
                    $product_category = $db->getItemFromDB("SELECT product_category FROM finascop_stock_itemmaster where stit_ID = {$datas[$i]['rdc_ItemId']}");
                    $datas[$i]['subcategory'] = $product_category;
                    $category = $db->getFromDB("SELECT category_id,subCategory_name FROM mypha_subCategory where subCategory_id = {$product_category}", true);
                    $datas[$i]['subcategoryname'] = $category['subCategory_name']; //Drug Group
                    $main_category = $category['category_id'];
                    if ($main_category > 0) {
                        $datas[$i]['category_name'] = $db->getItemFromDB("SELECT category_name FROM mypha_category where category_id = {$main_category}"); //system
                    } else {
                        $datas[$i]['category_name'] = '';
                    }
                }


                $datas[$i]['currentstock'] = $db->getItemFromDB("SELECT item_count FROM finascop_stock_branch_inventory WHERE stit_id = {$datas[$i]['rdc_ItemId']} AND branch_id = {$rdc_source}");
                $datas[$i]['packageType'] = $db->getItemFromDB("(select package_type_name from mypha_productpackage_type WHERE package_type_id = {$datas[$i]['rdc_ItemUnits']})");
                $datas[$i]['least_package_type'] = $db->getItemFromDB("(select package_type_name from mypha_productpackage_type WHERE package_type_id = {$datas[$i]['least_package_type_id']})");
            }
        }
        echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        break;
    case 'vendorItemStore':
        $stpa_id = $_POST['stpa_id'];
        $itemIds = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_id) FROM finascop_stock_party_items WHERE stpa_id = {$stpa_id}");
        if (!empty($itemIds)) {
            $qry = "select stit_ID,stit_SKU from " . FINASCOP_DB . "finascop_stock_itemmaster where stit_ID IN({$itemIds}) AND directPurchase = 1 order by stit_SKU";
            $data = $db->getMultipleData($qry, true);
        }

        if (!empty($data)) {
            echo json_encode($data);
        } else
            echo [];
        break;
    case 'rdcCreateTransferOrder':
        $peItemSGriddata = json_decode(stripslashes($_POST['itemGriddata']));



        $rdc_id = $_POST['rdc_id'];
        $noofbags = $_POST['noofbags'];
        $date = date('Y-m-d H:i:s');
        $db->query('begin');
        //create transfer order
        if ($rdc_id > 0) {
            $order_det = $db->getFromDb("SELECT rdc_source,rdc_destination FROM retaline_distribution_chart WHERE rdc_id={$rdc_id}", true);
            for ($i = 0; $i < count($peItemSGriddata); $i++) {
                $items = $db->getFromDb("SELECT item_weight,stit_item_volume,stit_GST,cos_nos,ds_nos,cs_nos FROM finascop_stock_itemmaster where stit_ID = {$peItemSGriddata[$i]->rdc_ItemId}", true);

                $itemStockCount = $db->getItemFromDB("SELECT item_count FROM finascop_stock_branch_inventory WHERE stit_id = {$peItemSGriddata[$i]->rdc_ItemId} AND branch_id = {$order_det['rdc_source']}", true);
                if ($peItemSGriddata[$i]->rdc_TransferedItemQty > $itemStockCount) {
                    $itemSku = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$peItemSGriddata[$i]->rdc_ItemId}");
                    $msg = "Inventory count for {$itemSku} is {$itemStockCount} only.";
                    echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
                    exit();
                }
            }
            $data['fstr_id'] = $rdc_id;

            $data['fsto_source'] = $order_det['rdc_source'];
            $data['fsto_destination'] = $order_det['rdc_destination'];
            $data['fsto_sourcetype'] = 1;
            $data['fsto_destinationtype'] = 1;
            $tdy = date("Y-m-d") . " 00:00:00";
            $maxId = $db->getItemFromDB("select right(fsto_uid,3)*1 as fsto_uid  from `finascop_stock_transfer_order` where `fsto_source` = {$order_det['rdc_source']} and `fsto_createdOn` between '{$tdy}' and '{$date}' order by `fsto_id` desc limit 1");
            $br_key = $db->getItemFromDB("SELECT br_key FROM finascop_branch WHERE br_ID={$order_det['rdc_source']}");
            $uid_max = getNewTONumber($order_det['rdc_source']);
            $data['fsto_uid'] = $uid_max;
            $data['fsto_type'] = 1;
            $data['fsto_ordertype'] = 4;
            $data['fsto_createdOn'] = $date;
            $data['fsto_initiatedBy'] = $_SESSION['admin']->finascop_current_branch_id;
            $data['fsto_updateon'] = $date;
            $data['fsto_updateby'] = $_SESSION['admin']->UserId;
            $data['fsto_createdBy'] = $_SESSION['admin']->UserId;
            $data['fsto_handlingcharge'] = $_POST['extraCharges'];
            $data['fsto_status'] = 6;

            $status = $db->perform('finascop_stock_transfer_order', $data);
            $lastId = $db->insert_id();

            $fsto['fsto_cgstval'] = 0;
            $fsto['fsto_sgstval'] = 0;
            $fsto['fsto_amtbeforetax'] = 0;
            $fsto['fsto_amtaftertax'] = 0;
            $fsto['fsto_netamount'] = 0;

            if ($lastId) {
                for ($i = 0; $i < count($peItemSGriddata); $i++) {
                    //print_r($peItemSGriddata);
                    $unique_id = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_distribution_chart_details WHERE rdcd_id ={$peItemSGriddata[$i]->rdcd_id} ");
                    if ($unique_id > 0) {
                        $itemPriceDetails = $db->getFromDB("SELECT mrp,selling_price,fpod_poLandingCostleastSKU,fpod_leastSKUepr,fpod_leastSKUmrp,fpod_customerRatePikup FROM finascop_stock_branch_inventory WHERE stit_id = {$peItemSGriddata[$i]->rdc_ItemId} AND branch_id = {$order_det['rdc_source']} ", true);

                        $fstr_ItemMRP = $itemPriceDetails['fpod_leastSKUmrp'];
                        $data_details['fsto_ItemId'] = $peItemSGriddata[$i]->rdc_ItemId;
                        $data_details['fsto_ItemQty'] = $peItemSGriddata[$i]->rdc_TransferedItemQty;
                        $data_details['fsto_pkdQty'] = $peItemSGriddata[$i]->rdc_TransferedItemQty;
////

                        $data_details['fsto_uid'] = $uid_max;
                        $data_details['fsto_id'] = $lastId;
                        $data_details['fstro_createdBy'] = $_SESSION['admin']->UserId;
                        $data_details['fstro_createdOn'] = $date;
                        $data_details['fstro_ItemMRP'] = $fstr_ItemMRP;

                        if (count($itemPriceDetails) > 0) {

                            $data_details['fstro_ItemSPincTax'] = $itemPriceDetails['fpod_leastSKUepr'];

                            $data_details['fstro_gst_value'] = $items['stit_GST'];
                            $amtbtax = round(($data_details['fstro_ItemSPincTax'] / (100 + $items['stit_GST'])) * 100, 2);
                            $data_details['fstro_cgst_percent'] = round(($items['stit_GST'] / 2), 2);
                            $data_details['fstro_sgst_percent'] = round(($items['stit_GST'] / 2), 2);
                            $data_details['fstro_cgst_value'] = $amtbtax * ($data_details['fstro_cgst_percent'] / 100);  //amtbtax * qty * (cgst/100)
                            $data_details['fstro_sgst_value'] = $amtbtax * ($data_details['fstro_sgst_percent'] / 100); //amtbtax * qty * sgst
                            $data_details['fstro_totamtbeforetax'] = $amtbtax * $fstr_leastSKUCount[$i];
                            $data_details['fstro_totamtaftertax'] = $data_details['fstro_ItemSPincTax'];
                            $data_details['fstro_kfc_percent'] = 0;
                            $data_details['fstro_kfc_value'] = 1;
                        }
                        $data_details = array_filter($data_details, 'strlen');

                        $status = $db->perform('finascop_stock_transfer_order_details', $data_details);

                        if ($status) {
                            $datas = array(
                                'rdcd_status' => 2,
                                'rdcd_updatedOn' => $date,
                                'rdcd_updatedBy' => $_SESSION['admin']->UserId,
                                'rdc_TransferedItemQty' => $peItemSGriddata[$i]->rdc_TransferedItemQty
                            );
                            $status = $db->perform(FINASCOP_DB . 'retaline_distribution_chart_details', $datas, 'update', 'rdcd_id=' . $peItemSGriddata[$i]->rdcd_id);
                            $total_count = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_distribution_chart_details WHERE rdcd_id={$peItemSGriddata[$i]->rdcd_id}");
                            $status_requested_count = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_distribution_chart_details WHERE rdc_id={$rdc_id} ");
                            if ($status_requested_count == 0) {
                                $datasr = array(
                                    'rdc_status' => 10
                                );
                                $status = $db->perform(FINASCOP_DB . 'retaline_distribution_chart', $datasr, 'update', " rdc_id={$rdc_id} ");
                            } else if ($total_count > $status_requested_count) {
                                $data = array(
                                    'fstr_status' => 5
                                );
                                $qry = $db->perform(FINASCOP_DB . 'retaline_distribution_chart', $data, 'update', " rdc_id={$rdc_id} ");
                            }
                        }
                    }
                }
            }

            $staoDetail = $db->getFromDB("SELECT SUM(fstro_cgst_value) AS totalCGST,SUM(fstro_sgst_value) AS totalSGST,SUM(fstro_totamtbeforetax) AS tatalAmtBT,SUM(fstro_totamtaftertax) AS tatalAmt "
                    . "FROM finascop_stock_transfer_order_details WHERE fsto_id = {$lastId}", true);
            $fsto['fsto_cgstval'] = $staoDetail['totalCGST'];
            $fsto['fsto_sgstval'] = $staoDetail['totalSGST'];
            $fsto['fsto_amtbeforetax'] = $staoDetail['tatalAmtBT'];
            $fsto['fsto_amtaftertax'] = $staoDetail['tatalAmt'];
            $fsto['fsto_netamount'] = $staoDetail['tatalAmt'];

            $status = $db->perform('finascop_stock_transfer_order', $fsto, 'update', " fsto_id = {$lastId}");
        }
        $status = $db->query('commit');
        if ($status) {
            $msg = "Order transfered succesfully";
            echo '{"success":true,"valid":true,"fsto_uid":"' . $uid_max . '","order_id":"' . $lastId . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
        }

        break;
    case 'submitManualPacking':
        $peItemSGriddata = json_decode(stripslashes($_POST['itemGriddata']));

        $order_id = $_POST['order_id'];
        $fsto_uid = $_POST['fsto_uid'];
        $rdc_id = $_POST['rdc_id'];
        $noofbags = $_POST['noofbags'];
        $date = date('Y-m-d H:i:s');
        $db->query('begin');

        //pack order and dispatch
        $cfg_Value = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'MANUAL_PACKING_NOBARCODE_URL'");
        $url = str_replace('{orderId}', $fsto_uid, $cfg_Value);
        $fsto_updateon = $db->getItemFromDB("SELECT fsto_updateon FROM finascop_stock_transfer_order WHERE fsto_id = {$order_id}");

        $noofbags = $_POST['noofbags'];
        $orderType = 4;

        $result['type'] = $orderType;
        $result['ismanual'] = 1;
        $result['key'] = md5($fsto_updateon);
        if (!empty($peItemSGriddata)) {
            foreach ($peItemSGriddata as $peItemS) {
                $fsto_ItemId = $peItemS->rdc_ItemId;
                $fsto_pkdQty = floatval($peItemS->rdc_TransferedItemQty);
                $tmpitems = [];
                $tmpitems['item_id'] = $fsto_ItemId;

                $tmpitems['count'] = $fsto_pkdQty;

                $tmpitems['fsto_stockValue'] = 0;
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
            $status = $db->query('commit');
            echo '{"success":true,"valid":false,"msg":"' . str_replace("'", "", str_replace('"', "", $data)) . '"}';
        } elseif ($haserror) {
            $status = $db->query('commit');
            $result = json_decode($data, true);
            echo '{"success":true,"valid":false,"msg":"' . $result['error']['msg'] . '"}';
        } elseif ($result['status'] == 'mismatch') {
            $status = $db->query('commit');
            $missedBarcodes = $result['data']['mismatched'][0]['barcodes'];
            echo '{"success":true,"valid":false,"msg":"Barcode Mismatched ."}';
            exit();
        } else if ($result['status'] == 'error') {
            $status = $db->query('commit');
            $message = $result['error']['msg'][0];
            echo '{"success":true,"valid":false,"msg":"' . $message . '"}';
            exit();
        } else {
            $status = $db->query('commit');
            $packingList = $result['packinglist']['packingNumber'];
            echo '{"success":true,"valid":true,"msg":"Order Packed .","packcount":"', count($packingList), '","data":' . json_encode(array_values($packingList)) . '}';
            exit();
        }
        exit;
        break;
    case 'itemHistory':
        $pe_party = $_POST['pe_party'];
        $pe_partyItems = $_POST['pe_partyItems'];
        $po_billing_to = $_POST['po_billing_to'];
        $dc_date = date('Y-m-d', strtotime($_POST['dc_date']));
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
        $lastPurchasePrice = $db->getFromDB("SELECT fpod_effectiverate,fpod_id,fpod_fpoId,fpo_centralStore,DATE_FORMAT(fpo_poDate,'%d %M %Y') as fpo_poDate FROM finascop_purchase_order_details "
                . "INNER JOIN  finascop_purchase_order ON fpo_id = fpod_fpoId WHERE fpod_itemid = {$pe_partyItems} ORDER BY fpod_id DESC LIMIT 1", true);
        if ($lastPurchasePrice['fpo_centralStore'] > 0) {
            $branchName = $db->getItemFromDB("SELECT br_Name FROM finascop_branch WHERE br_ID = {$lastPurchasePrice['fpo_centralStore']}");
        }

        $itemHistory['allottedStock'] = $db->getItemFromDB("SELECT SUM(rdc_ApprovedItemQty) FROM retaline_distribution_chart_details INNER JOIN retaline_distribution_chart ON retaline_distribution_chart.rdc_id = retaline_distribution_chart_details.rdc_id WHERE rdc_ItemId = {$pe_partyItems} AND rdc_date = '{$dc_date}' AND rdc_source = {$po_billing_to} GROUP BY rdc_ItemId");
        $rpd_quantity = $db->getItemFromDB("SELECT SUM(rpd_quantity) FROM retaline_procurement_details WHERE rpd_stitId = {$pe_partyItems} AND rpd_vendor = {$pe_party} AND rpd_date = '{$dc_date}' AND rpd_branch = {$po_billing_to} GROUP BY rpd_stitId");
        $itemHistory['projectedStock'] = $rpd_quantity;
        $totalProjecteStock = $db->getItemFromDB("SELECT SUM(rpd_quantity) FROM retaline_procurement_details WHERE rpd_stitId = {$pe_partyItems} AND  rpd_date = '{$dc_date}' AND rpd_branch = {$po_billing_to} GROUP BY rpd_stitId");
        $itemHistory['totalProjecteStock'] = $totalProjecteStock;
        if (empty($itemHistory['totalProjecteStock']))
            $itemHistory['totalProjecteStock'] = 0;
        if (empty($itemHistory['projectedStock']))
            $itemHistory['projectedStock'] = 0;
        if ($itemHistory['last_mrp'] > 0) {
            $itemHistory['last_mrp'] = $itemHistory['last_mrp'] . ' / ' . $itemHistory['itemUnitForm'] . ' (' . $branchName . ') - ' . $lastPurchasePrice['fpo_poDate'];
        } else {
            $itemHistory['last_mrp'] = 0;
        }

        $last_prchaserate = $db->getFromDB("SELECT fpod_effectiverate,fpo_poDate FROM finascop_purchase_order_details "
                . "INNER JOIN  finascop_purchase_order ON fpo_id = fpod_fpoId WHERE fpod_itemid = {$pe_partyItems} AND fpo_centralStore = {$po_billing_to} ORDER BY fpod_id DESC LIMIT 1", true);
        if ($last_prchaserate['fpod_effectiverate'] > 0) {
            $itemHistory['last_prchaserate'] = $last_prchaserate['fpod_effectiverate'] . ' / ' . $itemHistory['itemUnitForm'];
        } else {
            $itemHistory['last_prchaserate'] = 0;
        }

        if (!empty($itemHistory)) {
            echo json_encode($itemHistory);
        }
        break;
    case 'getBaseStationBranch':

        $query = $_POST['query'];
        if ($query != '')
            $con = " and br_Name like '" . $query . "%'";
        else
            $con = '';

        $qry = "select br_ID,br_Name from finascop_branch where br_status = 'Active' AND br_ID>0 AND br_PyramidLevel = 3 " . $con . " order by br_Name ";
        $branch = $db->getMulipleData($qry, true);
        if (!empty($branch)) {
            $branch = json_encode($branch);
            echo '{"data":' . $branch . '}';
        } else {
            echo '{"data":[]}';
        }
        break;
    case 'updateSellingPrice':
        $uuid = $_POST['uuid'];
        $search_baseStation = $_POST['search_baseStation'];
        $griddata = json_decode(stripslashes($_POST['spUpdatedItems']));
        $griddata = (array) $griddata;

        $retailors = $db->getItemFromDB("SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_cpd = {$search_baseStation}");
        $branches = $search_baseStation . ',' . $retailors;
        $branchesArr = explode(',', $branches);
        $db->query('begin');
        for ($i = 0; $i < count($griddata); $i++) {
            if ($griddata[$i]->selling_price > 0) {//&& $griddata[$i]->fpod_skuPurchaseQty > 0
                $updatat['fpod_customerRatePikup'] = $griddata[$i]->selling_price;
                $updatat['updated_on'] = date("Y-m-d H:i:s");
                $status = $db->perform('finascop_stock_branch_inventory', $updatat, 'update', " stit_id = {$griddata[$i]->stitId} and branch_id IN ({$branches})");
                for ($j = 0; $j < count($branchesArr); $j++) {
                    $updatatLog['old_selling_price'] = $griddata[$i]->fpod_customerRatePikup;
                    $updatatLog['selling_price'] = $griddata[$i]->selling_price;
                    $updatatLog['branch_id'] = $branchesArr[$j];
                    $updatatLog['stit_id'] = $griddata[$i]->stitId;
                    $updatatLog['item_count'] = $griddata[$i]->item_count;
                    $updatatLog['fpod_skuPurchaseRange'] = $griddata[$i]->fpod_skuPurchaseRange;
                    if ($griddata[$i]->fpod_skuPurchaseQty > 0) {
                        $updatatLog['fpod_skuPurchaseQty'] = $griddata[$i]->fpod_skuPurchaseQty;
                    } else {
                        $updatatLog['fpod_skuPurchaseQty'] = 0;
                    }

                    $updatatLog['fpod_skuAvgPurchaseRate'] = $griddata[$i]->fpod_skuAvgPurchaseRate;
                    $updatatLog['fpod_skuLastPurchaseRate'] = $griddata[$i]->fpod_skuLastPurchaseRate;
                    $updatatLog['fpod_leastSKUepr'] = $griddata[$i]->fpod_leastSKUepr;
                    $updatatLog['fpod_effectivemargin'] = $griddata[$i]->fpod_effectivemargin;
                    $updatatLog['updated_on'] = date("Y-m-d H:i:s");
                    $updatatLog['updated_by'] = $_SESSION['admin']->Finascop_UserId;
                    $updatatLog['type'] = 'online';                    

                $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'INVENTORYLOG'");
                $fields_string = json_encode($updatatLog);
                $opts = array(
                    CURLOPT_URL => $url,
                    CURLINFO_CONTENT_TYPE => "application/json",
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_BINARYTRANSFER => TRUE,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_POST => count($fields),
                    CURLOPT_POSTFIELDS => $fields_string,
                    CURLOPT_HTTPHEADER => array('Content-Type: application/json')
                );

                $ch = curl_init();
                curl_setopt_array($ch, $opts);
                $logrresult = curl_exec($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);
                header("Content-Type: application/json");
                //$result = json_decode($datacl, true);
                if ($logrresult != true) {
                    echo '{"success":false, "msg":"Some problem in log insertion."}';
                    exit();
                }
                    //$status = $db->perform('finascop_stock_branch_inventory_log', $updatatLog);
                }
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Sellingprice updated.'";
            echo '{"success":true,"valid":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error occured while saving.'";
            echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
        }
        break;
    case 'listSelectedItems':
        $branchId = $_POST['baseStation'];
        $data = $_POST;
        $rec_sort = empty($data['sort']) ? 'stitId' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'DESC' : $data['dir'];
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {

                if ($field['field'] == 'category_name') {
                    $searchitem .= " and (category_name LIKE '{$field[data][value]}%') ";
                } else if ($field['field'] == 'parent_category') {
                    $searchitem .= " and (mypha_productparent_category.parent_category LIKE '{$field[data][value]}%') ";
                } else {
                    $checkComa = strstr($field['data']['value'], ',');

                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $searchitem .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                    }
                }
            }
        }


        if ($branchId > 0) {

            $lastSePriDate = $db->getItemFromDB("SELECT updated_on FROM finascop_stock_branch_inventory_log WHERE branch_id = {$branchId} ORDER BY id DESC LIMIT 1");
            if (empty($lastSePriDate)) {
                $date = '0000-00-00 00:00:00';
                $datespCon = " fpod_createdon >= '{$date}'";
            } else {
                $date = $lastSePriDate;
                $datespCon = " fpod_createdon > '{$date}'";
            }
            $isSellingPrice = $db->getItemFromDB(" SELECT COUNT(*) FROM finascop_purchase_order_details INNER JOIN  finascop_purchase_order ON fpo_id = fpod_fpoId  WHERE fpo_centralStore = {$branchId} AND {$datespCon} ");
            $qry = "SELECT branch_id,finascop_stock_itemmaster.stit_ID AS stitId,stit_SKU,item_count,fpod_customerRatePikup,fpod_leastSKUepr FROM finascop_stock_branch_inventory "
                    . "INNER JOIN finascop_stock_itemmaster ON finascop_stock_itemmaster.stit_ID =  finascop_stock_branch_inventory.stit_id WHERE branch_id = {$branchId} || "
                    . "branch_id IN (SELECT br_ID FROM finascop_branch WHERE br_cpd = {$branchId}) GROUP BY finascop_stock_itemmaster.stit_ID";

            $countQuery = "SELECT COUNT(*) FROM ({$qry}) AS count";
            $listQuery = "SELECT * FROM ({$qry}) AS listdata ";
            //$db->printGridJson($countQuery, $listQuery);
            $datas = $db->getMulipleData($qry, true);
            $resCount = count($datas);
            if (!empty($datas)) {
                for ($i = 0; $i < $resCount; $i++) {
                    $lastDate = $db->getItemFromDB("SELECT updated_on FROM finascop_stock_branch_inventory_log WHERE stit_id = {$datas[$i]['stitId']} AND branch_id = {$branchId} ORDER BY id DESC LIMIT 1");
                    if (empty($lastDate)) {
                        $date = '0000-00-00 00:00:00';
                        $dateCon = " fpod_createdon >= '{$date}'";
                    } else {
                        $date = $lastDate;
                        $dateCon = " fpod_createdon > '{$date}'";
                    }
                    $fpod_skuPurchaseQty = $db->getItemFromDB(" SELECT SUM(fpod_leastSKUTotalqty) FROM finascop_purchase_order_details INNER JOIN  finascop_purchase_order ON fpo_id = fpod_fpoId  WHERE fpod_itemid = {$datas[$i]['stitId']} AND fpo_centralStore = {$branchId} AND {$dateCon} ");
                    if ($fpod_skuPurchaseQty > 0) {
                        $skuPurchaseRange = $db->getFromDB("SELECT MAX(fpod_itemoffrrate) as maxRate,MIN(fpod_itemoffrrate) as minRate FROM finascop_purchase_order_details "
                                . "INNER JOIN  finascop_purchase_order ON fpo_id = fpod_fpoId WHERE fpod_itemid = {$datas[$i]['stitId']} AND fpo_centralStore = {$branchId} AND {$dateCon} ORDER BY fpod_id DESC LIMIT 1", true);
                        if ($skuPurchaseRange['maxRate'] == $skuPurchaseRange['minRate']) {
                            $datas[$i]['fpod_skuPurchaseRange'] = $skuPurchaseRange['maxRate'];
                        } else {
                            $datas[$i]['fpod_skuPurchaseRange'] = $skuPurchaseRange['minRate'] . ' - ' . $skuPurchaseRange['maxRate'];
                        }

                        $totalPurchaseRate = $db->getItemFromDB(" SELECT SUM(fpod_itemoffrrate) FROM finascop_purchase_order_details INNER JOIN  finascop_purchase_order ON fpo_id = fpod_fpoId  WHERE fpod_itemid = {$datas[$i]['stitId']} AND fpo_centralStore = {$branchId} AND {$dateCon} ");

                        $fpod_skuAvgPurchaseRate = $totalPurchaseRate / 2;
                        $fpod_skuLastPurchaseRate = $db->getItemFromDB("SELECT fpod_itemoffrrate FROM finascop_purchase_order_details INNER JOIN  finascop_purchase_order ON fpo_id = fpod_fpoId "
                                . "WHERE fpod_itemid = {$datas[$i]['stitId']} AND fpo_centralStore = {$branchId}  AND {$dateCon} ORDER BY fpod_id DESC LIMIT 1");
                        $fpod_leastSKUepr = $db->getItemFromDB("SELECT fpod_effectiverate FROM finascop_purchase_order_details INNER JOIN  finascop_purchase_order ON fpo_id = fpod_fpoId "
                                . "WHERE fpod_itemid = {$datas[$i]['stitId']} AND fpo_centralStore = {$branchId}  AND {$dateCon} ORDER BY fpod_id DESC LIMIT 1");
                        $datas[$i]['fpod_skuPurchaseQty'] = $fpod_skuPurchaseQty;
                        $datas[$i]['totalPurchaseRate'] = $totalPurchaseRate;
                        $datas[$i]['fpod_skuAvgPurchaseRate'] = $fpod_skuAvgPurchaseRate;
                        $datas[$i]['fpod_skuLastPurchaseRate'] = $fpod_skuLastPurchaseRate;
                        $datas[$i]['fpod_leastSKUepr'] = $fpod_leastSKUepr;
                        $fpod_effectivemargin = 100 - (($fpod_skuLastPurchaseRate / $datas[$i]['fpod_customerRatePikup']) * 100);
                        $datas[$i]['fpod_effectivemargin'] = round($fpod_effectivemargin, 2);
                    } else {
                        $datas[$i]['fpod_skuPurchaseRange'] = 0;
                        $datas[$i]['fpod_skuPurchaseQty'] = 0;
                        $datas[$i]['totalPurchaseRate'] = 0;
                        $datas[$i]['fpod_skuAvgPurchaseRate'] = 0;
                        $datas[$i]['fpod_skuLastPurchaseRate'] = 0;
                        $datas[$i]['fpod_leastSKUepr'] = 0;
                        $datas[$i]['fpod_effectivemargin'] = 0;
                    }
                    $datas[$i]['selling_price'] = '';
                    $datas[$i]['lastdate'] = $date;
                }
                echo '{"totalCount":"', $resCount, '","data":' . json_encode($datas) . '}';
            } else {
                echo '{"totalCount":"0","data":[]}';
            }
        }
        break;
    case 'getAllBranch':

        $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch where br_status = 'Active'  {$cond}", true);


        if (!empty($qry)) {
            $branch = json_encode($qry);
            echo '{"data":' . $branch . '}';
        } else {
            echo '{"data":[]}';
        }
        break;
    case 'listSalesItems':
        $branchId = $_POST['baseStation'];
        $sales_dateview = date('Y-m-d', strtotime($_POST['sales_dateview']));
        $data = $_POST;
        $rec_sort = empty($data['sort']) ? 'stitId' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'DESC' : $data['dir'];
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {

                if ($field['field'] == 'category_name') {
                    $searchitem .= " and (category_name LIKE '{$field[data][value]}%') ";
                } else if ($field['field'] == 'parent_category') {
                    $searchitem .= " and (mypha_productparent_category.parent_category LIKE '{$field[data][value]}%') ";
                } else {
                    $checkComa = strstr($field['data']['value'], ',');

                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $searchitem .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                    }
                }
            }
        }


        if ($branchId > 0) {

            /* $qry = "SELECT finascop_stock_itemmaster.stit_ID AS stitItemId,stit_SKU,branch_id,item_count,least_package_type_name FROM finascop_stock_branch_inventory "
              . "INNER JOIN finascop_stock_itemmaster ON finascop_stock_itemmaster.stit_ID = finascop_stock_branch_inventory.stit_id WHERE  stit_ParentItemId = 0 AND branch_id = {$branchId}"; */
            //to list all raw roducts on 6thoctober 22
            $qry = "SELECT finascop_stock_itemmaster.stit_ID AS stitItemId,stit_SKU,least_package_type_name FROM finascop_stock_itemmaster WHERE  directPurchase = 1 AND stit_ParentItemId = 0 ";

            $poqry = "SELECT fpod_itemid,fpod_itemname,fpod_itemqty,SUM(fpod_totalqty) AS purchasedItem,SUM(fpod_receivedqty) AS receiveddItem,fpod_itemmrp FROM finascop_purchase_order_details 
INNER JOIN finascop_purchase_order ON fpo_id = fpod_fpoId 
INNER JOIN finascop_stock_itemmaster ON finascop_stock_itemmaster.stit_ID = fpod_itemid AND stit_ParentItemId = 0 WHERE fpo_poDate = '{$sales_dateview}' AND fpo_centralStore = {$branchId} || fpo_centralStore IN (SELECT br_ID FROM finascop_branch WHERE br_cpd = {$branchId}) GROUP BY fpod_itemid";


            $countQuery = "SELECT COUNT(*) FROM ({$qry}) AS count";
            $listQuery = "SELECT * FROM ({$qry}) AS listdata ";
            $datas = $db->getMultipleData($listQuery, true);


            $resCount = $db->getItemFromDB($countQuery);

            if (!empty($datas)) {
                if ($datas[0]['stitItemId'] > 0) {
                    for ($i = 0; $i < $resCount; $i++) {
                        $isBrOnline = $db->getItemFromDB("SELECT br_SalesOnline FROM finascop_branch WHERE br_ID = {$branchId}");

                        $returnTransferdItem = $db->getItemFromDB("SELECT SUM(fsr_ApprovedItemQty) FROM finascop_stock_request_details INNER JOIN finascop_stock_request ON finascop_stock_request.fsr_id = finascop_stock_request_details.fsr_id  WHERE fsr_type = 1 AND fsrs_status = 10 AND fsr_source = {$branchId} AND fsr_ItemId = {$datas[$i]['stitItemId']} AND DATE_FORMAT(fsr_createdOn,'%Y-%m-%d') = '{$sales_dateview}' GROUP BY fsr_ItemId");
                        $returnDamadedItem = $db->getItemFromDB("SELECT SUM(fsrrpd_ItemQty) FROM finascop_stock_return_request_packing_details INNER JOIN finascop_stock_return_request_packing WHERE frrp_source = {$branchId} AND fsrrpd_ItemId = {$datas[$i]['stitItemId']} AND DATE_FORMAT(frrp_createdOn,'%Y-%m-%d') = '{$sales_dateview}' GROUP BY fsrrpd_ItemId");
                        $transferedData = $db->getFromDB("SELECT fsto_ItemQty,SUM(fsto_pkdQty) as transfered,SUM(fsto_ItemQtyL3Received) FROM finascop_stock_transfer_order_details "
                                . "INNER JOIN finascop_stock_transfer_order ON finascop_stock_transfer_order.fsto_id = finascop_stock_transfer_order_details.fsto_id WHERE fsto_ordertype IN (0,2) AND fsto_ItemId = {$datas[$i]['stitItemId']} AND fsto_source = {$branchId} AND DATE_FORMAT(fsto_createdOn,'%Y-%m-%d') = '{$sales_dateview}' GROUP BY fsto_ItemId", true);
                        $saleData = $db->getFromDB("SELECT fsto_ItemQty,SUM(fsto_pkdQty) as sales FROM finascop_stock_transfer_order_details "
                                . "INNER JOIN finascop_stock_transfer_order ON finascop_stock_transfer_order.fsto_id = finascop_stock_transfer_order_details.fsto_id WHERE fsto_ordertype IN (1) AND fsto_ItemId = {$datas[$i]['stitItemId']} AND fsto_source = {$branchId} AND DATE_FORMAT(fsto_createdOn,'%Y-%m-%d') = '{$sales_dateview}' GROUP BY fsto_ItemId", true);
                        if ($saleData['sales'] > 0) {
                            $datas[$i]['salesEntry'] = $saleData['sales'];
                        } else {
                            $datas[$i]['salesEntry'] = 0;
                        }
                        if ($transferedData['transfered'] > 0) {
                            $datas[$i]['transfereCumSales'] = $transferedData['transfered'];
                        } else {
                            $datas[$i]['transfereCumSales'] = 0;
                        }


                        $receivedData = $db->getFromDB("SELECT fsto_ItemQty,SUM(fsto_pkdQty) as requested,SUM(fsto_ItemQtyL3Received) as receiveddItem FROM finascop_stock_transfer_order_details "
                                . "INNER JOIN finascop_stock_transfer_order ON finascop_stock_transfer_order.fsto_id = finascop_stock_transfer_order_details.fsto_id WHERE fsto_ItemId = {$datas[$i]['stitItemId']} AND fsto_destination = {$branchId} AND DATE_FORMAT(fsto_createdOn,'%Y-%m-%d') = '{$sales_dateview}' GROUP BY fsto_ItemId", true);

                        if ($receivedData['receiveddItem'] > 0) {
                            $datas[$i]['purchasedItem'] = $receivedData['receiveddItem'];
                        } else {
                            $datas[$i]['purchasedItem'] = 0;
                        }

                        $item_count = $db->getItemFromDB("SELECT item_count FROM finascop_stock_branch_inventory WHERE branch_id = {$branchId} AND stit_id = {$datas[$i]['stitItemId']}");
                        $datas[$i]['openingStockCount'] = $datas[$i]['transfered'] + $item_count;
                        $datas[$i]['shortageItem'] = $receivedData['requested'] - $receivedData['receiveddItem'];
                        if ($returnDamadedItem > 0) {
                            $datas[$i]['damagedItem'] = $returnDamadedItem;
                        } else {
                            $datas[$i]['damagedItem'] = 0;
                        }
                        if ($returnTransferdItem > 0) {
                            $datas[$i]['returnItem'] = $returnTransferdItem;
                        } else {
                            $datas[$i]['returnItem'] = 0;
                        }
                        if ($isBrOnline == 1) {
                            $datas[$i]['isBrOnline'] = true;
                        } else {
                            $datas[$i]['isBrOnline'] = false;
                        }
                    }
                }
            }
            finascop_arsort($datas, 'openingStockCount');
            echo '{"totalCount":"', $resCount, '","data":' . json_encode($datas) . '}';
            //$db->printGridJson($countQuery, $listQuery);
        }
        break;
    case 'listStckAlertItems':
        $branchId = $_POST['stal_branch'];
        $sa_date = date('Y-m-d', strtotime($_POST['sa_date']));
        $data = $_POST;
        $rec_sort = empty($data['sort']) ? 'stitId' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'DESC' : $data['dir'];
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {

                if ($field['field'] == 'category_name') {
                    $searchitem .= " and (category_name LIKE '{$field[data][value]}%') ";
                } else if ($field['field'] == 'parent_category') {
                    $searchitem .= " and (mypha_productparent_category.parent_category LIKE '{$field[data][value]}%') ";
                } else {
                    $checkComa = strstr($field['data']['value'], ',');

                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $searchitem .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                    }
                }
            }
        }


        if ($branchId > 0) {

            $qry = "SELECT branch_id,finascop_stock_itemmaster.stit_ID AS stitId,stit_SKU,item_count,fpod_customerRatePikup,COALESCE(rpd_quantity, 0 ) AS rpd_quantity,(COALESCE(rpd_quantity, 0 )+item_count) AS total FROM finascop_stock_itemmaster 
LEFT  JOIN finascop_stock_branch_inventory ON finascop_stock_itemmaster.stit_ID =  finascop_stock_branch_inventory.stit_id 
LEFT JOIN retaline_procurement_details ON rpd_stitId = finascop_stock_itemmaster.stit_ID AND rpd_branch = {$branchId} AND rpd_date = '{$sa_date}' WHERE branch_id = {$branchId} GROUP BY finascop_stock_itemmaster.stit_ID";

            $countQuery = "SELECT COUNT(*) FROM ({$qry}) AS count";
            $listQuery = "SELECT * FROM ({$qry}) AS listdata ";

            $db->printGridJson($countQuery, $listQuery);
        }
        break;
    case 'changeDCStatus':
        $rdc_id = $_POST['rdc_id'];
        $rdc_status = $_POST['rdc_status'];
        $branchDesi = $_POST['branchDesi'];
        $db->query('begin');
        $updateDc['rdc_status'] = 2;
        $updateDc['rdc_updatedOn'] = date('Y-m-d H:i:s');
        $updateDc['rdc_updatedBy'] = $_SESSION['admin']->UserId;
        //$status = $db->perform('retaline_distribution_chart', $updateDc, 'update', " rdc_id = {$rdc_id}");
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Items added to request.'";
            echo '{"success":true,"valid":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error occured while saving.'";
            echo '{"success":false,,"valid":false,"msg":' . $msg . '}';
        }
        break;
    case 'printPackingList':
        $rdc_id = $_POST['rdc_id'];
        $rdc_status = $_POST['rdc_status'];
        $branchDesi = $_POST['branchDesi'];

        ob_start();
        include('packPrintDetails.php');
        $resHtml = ob_get_contents();
        ob_end_clean();

        echo $resHtml;
        break;
    case 'printSendAlertList':
        $branchId = $_REQUEST['stal_branch'];
        $branchName = $db->getItemFromDB("select br_Name from finascop_branch where  br_ID = {$branchId}");
        $strDate = substr($_REQUEST['sa_date'], 4, 11);
        $sa_date = date('Y-m-d', strtotime($strDate));
        $sa_Displaydate = date('d-m-Y', strtotime($strDate));
        /* $qry = "SELECT branch_id,finascop_stock_itemmaster.stit_ID AS stitId,stit_SKU,item_count,fpod_customerRatePikup,COALESCE(SUM(rpd_quantity), 0 ) AS rpd_quantity,(COALESCE(SUM(rpd_quantity), 0 )+item_count) AS total FROM finascop_stock_itemmaster 
          INNER JOIN retaline_procurement_details ON rpd_stitId = finascop_stock_itemmaster.stit_ID  AND rpd_date = '{$sa_date}' AND rpd_branch  = {$branchId}
          LEFT JOIN finascop_stock_branch_inventory ON finascop_stock_itemmaster.stit_ID = finascop_stock_branch_inventory.stit_id AND branch_id  = {$branchId}
          GROUP BY finascop_stock_itemmaster.stit_ID"; */
        $qry = "SELECT branch_id,finascop_stock_itemmaster.stit_ID AS stitId,stit_SKU,item_count,fpod_customerRatePikup FROM finascop_stock_itemmaster 
INNER JOIN finascop_stock_branch_inventory ON finascop_stock_itemmaster.stit_ID = finascop_stock_branch_inventory.stit_id AND branch_id  = {$branchId} and item_count > 0 WHERE stit_status = 1 AND directPurchase = 1 
 GROUP BY finascop_stock_itemmaster.stit_ID";

        $details = $db->getMultipleData($qry, true);

        ob_start();
        include('alertDetails.php');
        $resHtml = ob_get_contents();
        ob_end_clean();

        echo $resHtml;
        break;

    case 'updateSales':
        $uuid = $_POST['uuid'];
        $sale_source = $_POST['search_baseStation'];
        $sales_date = date('Y-m-d', strtotime($_POST['sales_dateview']));
        $griddata = json_decode(stripslashes($_POST['spUpdatedItems']));
        $griddata = (array) $griddata;
        $db->query('begin');

        for ($i = 0; $i < count($griddata); $i++) {
            if ($griddata[$i]->salesEntry > 0) {
                $rspDatat['rsp_uuid'] = $uuid;
                $rspDatat['rsp_branch'] = $sale_source;
                $rspDatat['rsp_date'] = $sales_date;
                $rspDatat['rsp_item'] = $griddata[$i]->stitItemId;
                $rspDatat['rsp_count'] = $griddata[$i]->item_count;
                $rspDatat['rsp_transfer'] = $griddata[$i]->transfereCumSales;
                $rspDatat['rsp_opening'] = $griddata[$i]->openingStockCount;
                $rspDatat['rsp_purchase'] = $griddata[$i]->purchasedItem;
                $rspDatat['rsp_sales'] = $griddata[$i]->salesEntry;
                $rspDatat['rsp_damage'] = $griddata[$i]->damagedItem;
                $rspDatat['rsp_wastage'] = $griddata[$i]->wastageCount;
                $rspDatat['rsp_closing'] = $griddata[$i]->closingCount;
                $rspDatat['rsp_institutinalPrice'] = $griddata[$i]->institutinalPrice;
                $rspDatat['rsp_createdOn'] = date('Y-m-d H:i:s');
                $rspDatat['rsp_createdBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('retaline_sales_update', $rspDatat);
            }


            if ($griddata[$i]->closingCount > 0) {
                $isEntry = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory WHERE stit_id = {$griddata[$i]->stitItemId} and branch_id = {$sale_source} ");
                if ($isEntry > 0) {
                    $updatat['item_count'] = $griddata[$i]->closingCount;
                    $updatat['updated_on'] = date("Y-m-d H:i:s");
                    $status = $db->perform('finascop_stock_branch_inventory', $updatat, 'update', " stit_id = {$griddata[$i]->stitItemId} and branch_id = {$sale_source} ");
                } else {
                    $insdata['stit_id'] = $griddata[$i]->stitItemId;
                    $insdata['item_count'] = $griddata[$i]->closingCount;
                    $insdata['branch_id'] = $sale_source;
                    $insdata['created_at'] = date("Y-m-d H:i:s");
                    $status = $db->perform('finascop_stock_branch_inventory', $insdata);
                }

                if (empty($griddata[$i]->item_count)) {
                    $griddata[$i]->item_count = 0;
                }

                $updatatLog['branch_id'] = $sale_source;
                $updatatLog['stit_id'] = $griddata[$i]->stitItemId;
                $updatatLog['old_item_count'] = $griddata[$i]->item_count;
                $updatatLog['item_count'] = $griddata[$i]->closingCount;

                $updatatLog['updated_on'] = date("Y-m-d H:i:s");
                $updatatLog['updated_by'] = $_SESSION['admin']->Finascop_UserId;
                $updatatLog['action'] = 'Sales update.';

                //$status = $db->perform('finascop_stock_branch_inventory_log', $updatatLog);
                $updatatLog['selling_price'] = NULL;
                $updatatLog['old_selling_price'] = NULL;
                $updatatLog['fpod_skuPurchaseRange'] = NULL;
                $updatatLog['fpod_skuAvgPurchaseRate'] = NULL;
                $updatatLog['fpod_skuLastPurchaseRate'] = NULL;
                $updatatLog['fpod_effectivemargin'] = NULL;
                $updatatLog['fpod_skuPurchaseQty'] = NULL;
                $updatatLog['fpod_leastSKUepr'] = NULL;

                $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'INVENTORYLOG'");
                $fields_string = json_encode($updatatLog);
                $opts = array(
                    CURLOPT_URL => $url,
                    CURLINFO_CONTENT_TYPE => "application/json",
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_BINARYTRANSFER => TRUE,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_POST => count($fields),
                    CURLOPT_POSTFIELDS => $fields_string,
                    CURLOPT_HTTPHEADER => array('Content-Type: application/json')
                );

                $ch = curl_init();
                curl_setopt_array($ch, $opts);
                $logrresult = curl_exec($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);
                header("Content-Type: application/json");
                //$result = json_decode($datacl, true);
                if ($logrresult != true) {
                    echo '{"success":false, "msg":"Some problem in log insertion."}';
                    exit();
                }
            }
        }
        $status = $db->query('commit');
        if ($status) {
            $msg = "Sales Updated";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'deleteItemBfrPack':
        $rdcd_id = $_POST['rdcd_id'];
        $db->query('begin');
        $uprdc['rdcd_status'] = 3;
        $uprdc['rdcd_updatedOn'] = date("Y-m-d H:i:s");
        $uprdc['rdcd_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('retaline_distribution_chart_details', $uprdc, 'update', " rdcd_id = {$rdcd_id} ");
        $status = $db->query('commit');
        if ($status) {
            $msg = "Item removed";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;

    case 'updateChildStock':

        $retgrnd_uniqueid = $_POST['retgrnd_uniqueid'];

        $grnMainData = $db->getFromDB("SELECT * FROM retaline_grn WHERE retgrn_uniqueid = '{$retgrnd_uniqueid}'", true);
        $grnItems = $db->getMultipleData("SELECT * FROM  retaline_grn_details WHERE retgrnd_uniqueid = '{$retgrnd_uniqueid}'", true);
        foreach ($grnItems as $grnItem) {
            $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'UPDATECHILDSTOCK'");
            if (!empty($url)) {
                $fields = array(
                    "parentItem" => $grnItem['retgrnd_itemid'],
                    "branch" => $grnMainData['retgrn_billingTo']
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
            }
        }
        //echo $data;
        $msg = "Stock updated.";
        echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';

        break;
    case 'listRetalineUpdateSale':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'rsp_id' : ($sort == 'rsp_createdOn' ? 'rsp_id' : $sort );
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':

                        break;
                    case 'date':
                        $datefield = 'rsp_createdOn';
                        switch ($field['data']['comparison']) {
                            case 'gt' :
                                $search .= " and DATE_FORMAT(" . $datefield . ", '%Y-%m-%d') > '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            case 'lt':
                                $search .= " and DATE_FORMAT(" . $datefield . ", '%Y-%m-%d') < '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            case 'eq':
                                $search .= " and DATE_FORMAT(" . $datefield . ", '%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            default:
                                $search .= " and DATE_FORMAT(" . $datefield . ", '%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";

                                break;
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

        $countQuery = "SELECT COUNT(*) FROM (SELECT rsp_id,rsp_uuid,rsp_branch,rsp_date,(SELECT br_Name FROM finascop_branch WHERE br_ID = rsp_branch) as branchName  FROM retaline_sales_update GROUP BY rsp_uuid) AS countDC {$search} {$where}";

        $listQuery = "SELECT * FROM (SELECT rsp_id,rsp_uuid,rsp_branch,rsp_date,(SELECT br_Name FROM finascop_branch WHERE br_ID = rsp_branch) as branchName  FROM retaline_sales_update  GROUP BY rsp_uuid ) AS listDc {$search} {$where} ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'listViewUpdateSales':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'rsp_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':

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

        $countQry = "SELECT COUNT(*) FROM retaline_sales_update  {$search} AND rsp_uuid = '{$_POST['rsp_uuid']}'  ";

        $listQry = "SELECT rsp_id,rsp_uuid,rsp_branch,rsp_date,rsp_item,rsp_count,rsp_transfer,rsp_opening,rsp_purchase,rsp_sales,rsp_damage,rsp_return,rsp_wastage,rsp_closing FROM retaline_sales_update {$search} AND rsp_uuid = '{$_POST['rsp_uuid']}' ORDER BY {$sort} {$dir} ";
        $datas = $db->getMulipleData($listQry, true);
        $resCount = count($datas);
        $count = $db->getItemFromDB($countQry);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $datas[$i]['rsp_ItemName'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = {$datas[$i]['rsp_item']}");
            }
        }
        echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        break;
    case 'updateStock':
        $uuid = $_POST['uuid'];
        $sale_source = $_POST['search_baseStation'];
        $sales_date = date('Y-m-d', strtotime($_POST['sales_dateview']));
        $griddata = json_decode(stripslashes($_POST['spUpdatedItems']));
        $griddata = (array) $griddata;


        $retailors = $db->getItemFromDB("SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_cpd = {$search_baseStation} AND br_type <> 1");
        $branches = $search_baseStation . ',' . $retailors;
        $branchesArr = explode(',', $branches);
        for ($i = 0; $i < count($griddata); $i++) {
            for ($j = 0; $j < count($branchesArr); $j++) {
                $parentItemId = $griddata[$i]->stitItemId;
                if ($parentItemId > 0) {
                    $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'UPDATECHILDSTOCK'");
                    if (!empty($url)) {
                        $fields = array(
                            "parentItem" => $parentItemId,
                            "branch" => $branchesArr[$j]
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
                    }
                }
            }
        }
        $msg = "'Stock updation processing.'";
        echo '{"success":true,"valid":true,"msg":' . $msg . '}';
}