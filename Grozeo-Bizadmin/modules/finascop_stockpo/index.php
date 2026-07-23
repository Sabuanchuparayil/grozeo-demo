<?php

require_once(INCLUDE_PATH . "/finascop_wallet_client.php");
switch ($op) {

    case 'listStockPo':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'fpo_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        //$search = " WHERE 1=1 AND fpo_Active = 1 ";
        $search = " WHERE 1=1  ";
        $filter = $_POST['filter'];
        if (isset($_POST['filter'])) {
        $allowedFields = ['po_id', 'po_number', 'po_date', 'vendor_name', 'vendor_id', 'po_status', 'po_total'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }

                        break;
                }
            }
        }

        if ($_SESSION['admin']->br_PyramidLevel == 1) {
            $filter_qry .= " ";
            /*if ($_SESSION['admin']->IsSuperUser == 'Yes') {
                $filter_qry .= " ";
            } else {
                $filter_qry .= " AND fpo_centralStore =" . $br_ID;
            }*/
        } else {
            $filter_qry .= " AND fpo_centralStore = {$_SESSION['admin']->finascop_current_branch_id}";
        }
        $countQuery = "SELECT COUNT(*) FROM finascop_purchase_order  {$search} {$filter_qry}";
        $listQuery = "SELECT  fpo_id,fpo_poNumber,DATE_FORMAT(fpo_poDate,'%d-%m-%Y') as fpo_poDate,fpo_paymentTerms,(SELECT CONCAT(stpa_Fname,' ',stpa_Lname) FROM finascop_stock_party WHERE stpa_id = fpo_vendorId) as vendor_name,"
            . "DATE_FORMAT(fpo_poDeliveryDate,'%d-%m-%Y') as fpo_poDeliveryDate,fpo_poDeliveryType,fpo_paymentValue,fpo_stockVerificationStatus,IF((fpo_Active=1),'Active','Inactive')AS fpo_Active  FROM finascop_purchase_order "
            . "{$search} {$filter_qry} ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'stockpoDetailsView':
        $fpoId = isset($_POST['fpo_id']) ? intval($_POST['fpo_id']) : 0;
        if ($fpoId > 0) {
            $result = $db->getFromDB("SELECT  fpo_id,fpo_poNumber,DATE_FORMAT(fpo_poDate,'%d-%m-%Y') as fpo_poDate,fpo_paymentTerms,DATE_FORMAT(fpo_poDeliveryDate,'%d-%m-%Y') as fpo_poDeliveryDate,fpo_poDeliveryType,fpo_paymentValue,(SELECT CONCAT(stpa_Fname,' ',stpa_Lname) FROM finascop_stock_party WHERE stpa_id = fpo_vendorId) as vendor_name  FROM finascop_purchase_order where fpo_id = " . $fpoId, true);
            $result['success'] = true;
            echo json_encode($result);
        }
        break;
    case 'stockpoItemStore':
        $lt = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $st = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort1 = trim($_POST['sort']);
        $dir = trim($_POST['dir']);
        $lt = is_numeric($lt) ? $lt : 20;
        $st = is_numeric($st) ? $st : 0;
        $sort1 = empty($sort) ? 'fpod_itemid' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;

        $filter_qry = " WHERE 1 = 1 ";
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

        $clientID = isset($_POST['fpo_id']) ? intval($_POST['fpo_id']) : 0;
        $countQuery = "SELECT count(*) from finascop_purchase_order_details {$filter_qry} AND fpod_fpoId = {$clientID} ";

        $listQuery = "SELECT fpod_fpoId,fpod_id,fpod_itemid,fpod_itemname,fpod_itemqty,fpod_totalqty,fpod_receivedqty,fpod_balanceqty,fpod_giftqty,fpod_giftrecqty,fpod_giftbalqty,fpod_stockVerificationStatus,"
            . "fpod_itemoffrqty,fpod_purchasingUnit,(SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = fpod_purchasingUnit) as unitName,"
            . "(SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = fpod_itemid) as itemSku,fpod_leastSKUTotalqty,fpod_leastSKUBalanceqty,fpod_leastSKUreceivedqty,"
            . "(SELECT least_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = fpod_itemid) as least_package_type_name "
            . "from  finascop_purchase_order_details "
            . "{$filter_qry} AND fpod_fpoId = {$clientID} GROUP BY $sort1 ORDER BY {$sort1} {$dir} ";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'getItemStoreforStock':
        $fpodId = isset($_POST['fpod_id']) ? intval($_POST['fpod_id']) : 0;
        $qry = "SELECT fpod_itemid AS item_id, fpod_itemname AS item_name from " . FINASCOP_DB . "finascop_purchase_order_details WHERE fpod_id = {$fpodId}";
        $items = $db->getMultipleData($qry, true);
        $giftCount = $db->getItemFromDb("SELECT fpod_giftname FROM " . FINASCOP_DB . "finascop_purchase_order_details WHERE fpod_id = {$fpodId}");
        if (strlen($giftCount) > 0) {
            $gqry = "SELECT 'gift' AS item_id, CONCAT(fpod_giftname,'','(Gift)') AS item_name from " . FINASCOP_DB . "finascop_purchase_order_details WHERE fpod_id = {$fpodId}";
            $gitems = $db->getMultipleData($gqry, true);
            $data = array_merge($items, $gitems);
        } else {
            $data = $items;
        }


        echo '{"success":true,"data":' . json_encode($data) . '}';

        break;
    case 'itemStockpoDetailsView':
        $fpodId = isset($_POST['fpod_id']) ? intval($_POST['fpod_id']) : 0;
        if ($fpodId > 0) {
            $result = $db->getFromDB("SELECT fpod_id,fpod_itemname,fpod_itemid,fpod_itemqty,fpod_itemmrp,fpod_itemoffrrate,fpod_effectiverate,fpod_giftname,fpod_giftqty,fpod_itemoffrqty,fpod_totalqty,fpod_poLandingCost,fpod_leastSKUTotalqty,"
                . "(SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = fpod_itemid) as itemSku,(SELECT stit_HSN_code FROM finascop_stock_itemmaster WHERE stit_ID = fpod_itemid) as itemHSN,"
                . "(SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = fpod_itemid) as itemGST,fpod_purchasingUnit,fpod_leastSKUqty,(SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = fpod_purchasingUnit) as unitName"
                . "  FROM finascop_purchase_order_details where fpod_id = " . $fpodId, true);
            $fpod_purchasingUnitName = $db->getItemFromDB("SELECT package_type_name FROM  mypha_productpackage_type m WHERE m.package_type_id ='{$result['fpod_purchasingUnit']}' ");
            $least_package_type_name = $db->getItemFromDB("SELECT least_package_type_name FROM  finascop_stock_itemmaster m WHERE m.stit_ID ='{$result['fpod_itemid']}' ");
            $result['SKUS'] = $result['fpod_leastSKUTotalqty'] . ' ' . $least_package_type_name;
            $result['fpod_purchasingUnitName'] = $fpod_purchasingUnitName;
            $result['success'] = true;
            echo json_encode($result);
        }
        break;
    case 'addItemtoStock':
        $nofqty = $_REQUEST['poStockItemQty'];
        $leastSKUCount = $_REQUEST['leastSKUCount'];

        $quantityVerification = $db->getFromSafe("SELECT * FROM finascop_purchase_order_details WHERE fpod_id = ?", "i", [$_REQUEST['fpod_id']], true);
        $fpo_centralStore = $db->getItemFromDB("SELECT fpo_centralStore FROM finascop_purchase_order WHERE fpo_id = {$quantityVerification['fpod_fpoId']}");
        $stit_GST = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$quantityVerification['fpod_itemid']}");
        //$purchasingUnit = $db->getItemFromDB("SELECT csb_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$quantityVerification['fpod_itemid']}");


        (float) $eprbft = ((float) $quantityVerification['fpod_effectiverate'] / (100 + (float) $stit_GST)) * 100;
        $fsiidata['stii_fpoid'] = $_REQUEST['fpo_id'];
        $fsiidata['stii_fpodid'] = $_REQUEST['fpod_id'];
        if ($_REQUEST['poStockItemId'] == 'gift') {
            $fsiidata['stii_itemmasterid'] = 0;
        } else {
            $fsiidata['stii_itemmasterid'] = $_REQUEST['poStockItemId'];
        }
        $fsiidata['stii_batch'] = $_REQUEST['poStockItemBatch'];
        //$fsiidata['stii_expirydate'] = $_REQUEST['poStockItemExpiryDate'];
        $chkdt = $_REQUEST['poStockItemExpiryDate'];
        $chkdtarr = explode("GMT", $chkdt);
        $newdt = strtotime($chkdtarr[0]);

        $fsiidata['stii_expirydate'] = date('Y-m-d', $newdt);

        $fsiidata['stii_createdon'] = date("Y-m-d H:i:s");
        $fsiidata['stii_createdby'] = $_SESSION['admin']->Finascop_UserId;
        $fsiidata['stii_updatedon'] = date("Y-m-d H:i:s");
        $fsiidata['stii_updatedby'] = $_SESSION['admin']->Finascop_UserId;



        $db->query('begin');

        if ($_REQUEST['poStockItemId'] == 'gift') {
            if (($nofqty <= $quantityVerification['fpod_giftqty']) && ($nofqty <= $quantityVerification['fpod_giftbalqty'])) {
                $fsiiddata['stiid_itemmasterid'] = 0;
                $fsiiddata['stiid_giftname'] = $_REQUEST['poStockItemName'];
                //$fsiiddata['stiid_expirydate'] = date('Y-m-d', strtotime($_REQUEST['poStockItemExpiryDate']));
                $fsiiddata['stiid_expirydate'] = date('Y-m-d', strtotime($_REQUEST['poStockItemExpiryDate']));
                $fsiiddata['stiid_batchno'] = $_REQUEST['poStockItemBatch'];
                $fsiiddata['stiid_fpodid'] = $_REQUEST['fpod_id'];
                $fsiiddata['stiid_fpoid'] = $_REQUEST['fpo_id'];
                $fsiidata['stii_giftname'] = $_REQUEST['poStockItemName'];
                $fsiidata['stii_isgift'] = 1;
                $fsiidata['stii_qty'] = $nofqty;

                // for ($i = 0; $i < $nofqty; $i++) {
                //     $fsiidstatus = $db->perform('finascop_stock_item_inventorydetails', $fsiiddata);
                // }

                $barcode = $db->getItemFromDB("SELECT max(stiid_barcode) FROM finascop_stock_item_inventorydetails");
                if (intval($barcode) == 0) {
                    $barcode = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'BARCODE_START'");
                } else {
                    $barcode = intval($barcode) + 1;
                }

                for ($i = 0; $i < $nofqty; $i++) {
                    if ($i == 0) {
                        $firstBrcode = $barcode;
                    }
                    $fsiiddata['stiid_barcode'] = $barcode;
                    //print_r($fsiiddata);exit();
                    $fsiidstatus = $db->perform('finascop_stock_item_inventorydetails', $fsiiddata);
                    $barcode++;
                }
                $barcode--;
                $fsiidata['stii_barcodestart'] = $firstBrcode;
                $fsiidata['stii_barcodeend'] = $barcode;
                $fsiidata['stii_cpd'] = $_SESSION['admin']->finascop_current_branch_id;
                $fsiidata['stii_mrp'] = $quantityVerification['fpod_itemmrp'];
                $fsiidata['stii_created_level'] = 2;
                //$fsiidata['stii_selpri'] = $quantityVerification['fpod_effectiverate'];

                $fsiistatus = $db->perform('finascop_stock_item_inventory', $fsiidata);
                $stiiId['stii_id'] = $db->insert_id();
                $stii_id = $stiiId['stii_id'];
                $fsiidupstatus = $db->perform('finascop_stock_item_inventorydetails', $stiiId, 'update', " stiid_barcode BETWEEN {$firstBrcode} AND {$barcode}");

                //$fpoddata['fpod_receivedqty'] = (int) $quantityVerification['fpod_receivedqty'] + (int) $nofqty;
                $fpoddata['fpod_giftrecqty'] = (int) $quantityVerification['fpod_giftrecqty'] + (int) $nofqty;
                $fpoddata['fpod_balanceqty'] = (int) $quantityVerification['fpod_balanceqty'];
                $fpoddata['fpod_giftbalqty'] = (int) $quantityVerification['fpod_giftqty'] - (int) $fpoddata['fpod_giftrecqty'];
                if ($fpoddata['fpod_giftbalqty'] == 0 && $fpoddata['fpod_leastSKUBalanceqty'] == 0) {
                    $podsvstatus = 2;
                } else if ($fpoddata['fpod_giftbalqty'] == $quantityVerification['fpod_giftqty']) {
                    $podsvstatus = 0;
                } else {
                    $podsvstatus = 1;
                }
                $fpoddata['fpod_stockVerificationStatus'] = $podsvstatus;
                $podstatus = $db->perform('finascop_purchase_order_details', $fpoddata, 'update', "fpod_id = " . intval($_REQUEST['fpod_id']));
            } else {
                echo '{"success":false,"msg":"Provided Gift quantity doesnot match PO entry"}';
                exit();
            }
        } else {

            $bannedBatch = $db->getItemSafe("SELECT COUNT(*) FROM finascop_stock_item_banned_batch WHERE stit_id = ? AND fsibb_batch = '{$_REQUEST['poStockItemBatch']}' ", "i", [$_REQUEST['poStockItemId']]);
            if ($bannedBatch > 0) {
                $msg = "Batch is banned in the system.";
                echo '{"success":false,"msg":' . $msg . '}';
                exit();
            }
            $packageDetails = $db->getFromDB("SELECT csb_package_type_name,csb_package_type_id,cs_nos,cs_package_type_name,cs_package_type_id,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,cos_package_type_id,"
                . "cosb_package_type_id,dsb_package_type_id,isRRPApplicable FROM finascop_stock_itemmaster WHERE stit_ID = {$_REQUEST['poStockItemId']}", true);
            if ($packageDetails['cs_nos'] == 0) {
                $packageDetails['cs_nos'] = 1;
            }
            if ($packageDetails['ds_nos'] == 0) {
                $packageDetails['ds_nos'] = 1;
            }
            if ($packageDetails['cos_nos'] == 0) {
                $packageDetails['cos_nos'] = 1;
            }

            //print_r($quantityVerification);
            //print_r($leastSKUCount);

            if (($leastSKUCount > 0) && ($leastSKUCount <= $quantityVerification['fpod_leastSKUTotalqty']) && ($leastSKUCount <= $quantityVerification['fpod_leastSKUBalanceqty'])) {
                //if (($nofqty <= $quantityVerification['fpod_totalqty']) && ($nofqty <= $quantityVerification['fpod_balanceqty'])) {

                $fbis['stit_id'] = $_REQUEST['poStockItemId'];
                $fbis['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;

                $fbis['mrp'] = $quantityVerification['fpod_itemmrp'];
                //$fbis['selling_price'] = $quantityVerification['fpod_effectiverate'];
                $fbis['selling_price'] = $quantityVerification['fpod_customerRatePikup'];
                $fbis['updated_on'] = date('Y-m-d H:i:s');
                $bmdDetails = $db->getFromDB("SELECT * FROM  retaline_margindistributions WHERE bmd_id = {$quantityVerification['bmd_id']}", true);
                $br_PyramidLevel = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch WHERE br_ID = {$fpo_centralStore}");
                $countGift = $db->getItemFRomDB("SELECT fpod_giftqty FROM finascop_purchase_order_details WHERE fpod_id = {$fsiidata['stii_fpodid']}");
                if ($countGift > 0) {
                    $hasGift = 1;
                } else {
                    $hasGift = 0;
                }

                if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                    $fsiiddata['stiid_createdon'] = date("Y-m-d H:i:s");
                    $fsiiddata['stiid_createdby'] = $_SESSION['admin']->Finascop_UserId;
                    $fsiiddata['stiid_updatedon'] = date("Y-m-d H:i:s");
                    $fsiiddata['stiid_updatedby'] = $_SESSION['admin']->Finascop_UserId;
                    $fsiiddata['stiid_itemmastername'] = $_REQUEST['poStockItemName'];
                    $fsiiddata['stiid_itemmasterid'] = $_REQUEST['poStockItemId'];
                    //$fsiiddata['stiid_expirydate'] = date('Y-m-d', strtotime($_REQUEST['poStockItemExpiryDate']));
                    $fsiiddata['stiid_expirydate'] = date('Y-m-d', strtotime($_REQUEST['poStockItemExpiryDate']));
                    $fsiiddata['stiid_batchno'] = $_REQUEST['poStockItemBatch'];
                    $fsiiddata['stiid_fpodid'] = $_REQUEST['fpod_id'];
                    $fsiiddata['stiid_fpoid'] = $_REQUEST['fpo_id'];
                    $fsiidata['stii_isgift'] = 0;
                    $fsiiddata['stiid_mrp'] = $quantityVerification['fpod_itemmrp'];
                    //$fsiiddata['stiid_selpri'] = $quantityVerification['fpod_effectiverate'];
                    $fsiiddata['stiid_selpriorg'] = $quantityVerification['fpod_customerRatePikup'];
                    $fsiiddata['stiid_selpri'] = $quantityVerification['fpod_customerRatePikup'];
                    $fsiiddata['stii_epraft'] = $quantityVerification['fpod_effectiverate'];
                    $fsiiddata['stii_eprbft'] = $eprbft;
                    $fsiiddata['bmd_percent'] = $quantityVerification['bmd_percent'];

                    $fsiiddata['bmd_percentorg'] = $quantityVerification['bmd_percent'];
                    $fsiiddata['bmd_company'] = $bmdDetails['bmd_company'];
                    $fsiiddata['bmd_companyorg'] = $bmdDetails['bmd_company'];
                    $fsiiddata['bmd_hub'] = $bmdDetails['bmd_hub'];
                    $fsiiddata['bmd_huborg'] = $bmdDetails['bmd_hub'];
                    $fsiiddata['bmd_incentive'] = $bmdDetails['bmd_incentive'];
                    $fsiiddata['bmd_incentiveorg'] = $bmdDetails['bmd_incentive'];
                    $fsiiddata['bmd_technology'] = $bmdDetails['bmd_technology'];
                    $fsiiddata['bmd_technologyorg'] = $bmdDetails['bmd_technology'];
                    $fsiiddata['bmd_customer'] = $bmdDetails['bmd_customer'];
                    $fsiiddata['bmd_customerorg'] = $bmdDetails['bmd_customer'];
                    //new in finascop_stock_item_inventorydetails               
                    $fsiiddata['stiid_itemSmallStockUnit'] = $quantityVerification['fpod_itemSmallStockUnit'];
                    $fsiiddata['stiid_leastSKUmrp'] = $quantityVerification['fpod_leastSKUmrp'];
                    $fsiiddata['stiid_leastSKUepr'] = $quantityVerification['fpod_leastSKUepr'];
                    $fsiiddata['bmd_cs'] = $quantityVerification['bmd_cs'];
                    $fsiiddata['bmd_distributor'] = $quantityVerification['bmd_distributor'];
                    $fsiiddata['bmd_retailor'] = $quantityVerification['bmd_retailor'];
                    $fsiiddata['bmd_driver'] = $quantityVerification['bmd_driver'];
                    $fsiiddata['bmd_courier'] = $quantityVerification['bmd_courier'];
                    $fsiiddata['stiid_companyMargin'] = $quantityVerification['fpod_companyMargin'];
                    $fsiiddata['stiid_incentiveMargin'] = $quantityVerification['fpod_incentiveMargin'];
                    $fsiiddata['stiid_csMargin'] = $quantityVerification['fpod_csMargin'];
                    $fsiiddata['stiid_distributorMargin'] = $quantityVerification['fpod_distributorMargin'];
                    $fsiiddata['stiid_retailorMargin'] = $quantityVerification['fpod_retailorMargin'];
                    $fsiiddata['stiid_driverMargin'] = $quantityVerification['fpod_driverMargin'];
                    $fsiiddata['stiid_courierMargin'] = $quantityVerification['fpod_courierMargin'];
                    $fsiiddata['stiid_customerRateHmDel'] = $quantityVerification['fpod_customerRateHmDel'];
                    $fsiiddata['stiid_customerRateCouDel'] = $quantityVerification['fpod_customerRateCouDel'];
                    $fsiiddata['stiid_customerRatePikup'] = $quantityVerification['fpod_customerRatePikup'];
                    $fsiiddata['stiid_customerProfitHmDel'] = $quantityVerification['fpod_customerProfitHmDel'];
                    $fsiiddata['stiid_customerProfitCouDel'] = $quantityVerification['fpod_customerProfitCouDel'];
                    $fsiiddata['stiid_customerProfitPikup'] = $quantityVerification['fpod_customerProfitPikup'];
                    $fsiiddata['stiid_hasGift'] = $quantityVerification['fpod_hasGift'];

                    $fsiiddata['stiid_itemleastSKUptr'] = $quantityVerification['fpod_itemleastSKUptr'];
                    $fsiiddata['stiid_itemleastSKUpts'] = $quantityVerification['fpod_itemleastSKUpts'];

                    $fsiiddata['stiid_leastSKUb2bCSsp'] = $quantityVerification['fpod_leastSKUb2bCSsp'];
                    $fsiiddata['stiid_leastSKUb2bRetailsp'] = $quantityVerification['fpod_leastSKUb2bRetailsp'];

                    $fsiiddata['stiid_poLandingCostleastSKU'] = $quantityVerification['fpod_poLandingCostleastSKU'];
                    $fsiiddata['stiid_poMMGleastSKU'] = $quantityVerification['fpod_poMMGleastSKU'];

                    $barcode = $db->getItemFromDB("SELECT max(stiid_barcode) FROM finascop_stock_item_inventorydetails");
                    if (intval($barcode) == 0) {
                        $barcode = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'BARCODE_START'");
                    } else {
                        $barcode = intval($barcode) + 1;
                    }

                    $fsiidmData['stiidm_itemmasterid'] = $_REQUEST['poStockItemId'];
                    $nofbarcodeqty = $leastSKUCount;
                    for ($i = 0; $i < $nofbarcodeqty; $i++) {
                        if ($i == 0) {
                            $firstBrcode = $barcode;
                        }
                        $fsiiddata['stiid_barcode'] = $barcode;
                        $fsiiddata['is_converted'] = 0;
                        $fsiiddata['stiid_parent_barcode'] = 0;
                        switch ($br_PyramidLevel) {
                            case 2:
                                $fsiiddata['stiid_barcode_created_level'] = 2;
                                break;
                            case 3:
                                $fsiiddata['stiid_barcode_created_level'] = 3;
                                break;
                            case 4:
                                $fsiiddata['stiid_barcode_created_level'] = 4;
                                break;
                        }
                        $fsiidmData['created_at'] = date('Y-m-d H:i:s');
                        $fsiidmData['stiidm_barcode'] = $barcode;
                        //print_r($fsiiddata);
                        $fsiidstatus = $db->perform('finascop_stock_item_inventorydetails', $fsiiddata);
                        $fsiidmData['stiid_id'] = $db->insert_id();
                        $fsiidmData['stiidm_details'] = 'Added barcode for the item in order ' . $_REQUEST['fpo_id'];
                        $fsiidmstatus = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiidmData);
                        $barcode++;
                    }
                    $barcode--;
                }
                //new in finascop_stock_item_batch_group    
                $fsibg['fsbg_leastSKUmrp'] = $quantityVerification['fpod_leastSKUmrp'];
                $fsibg['fsbg_leastSKUepr'] = $quantityVerification['fpod_leastSKUepr'];
                $fsibg['fsbg_customerRateHmDel'] = $quantityVerification['fpod_customerRateHmDel'];
                $fsibg['fsbg_customerRateCouDel'] = $quantityVerification['fpod_customerRateCouDel'];
                $fsibg['fsbg_customerRatePikup'] = $quantityVerification['fpod_customerRatePikup'];

                $fsibg['fsbg_itemleastSKUptr'] = $quantityVerification['fpod_itemleastSKUptr'];
                $fsibg['fsbg_itemleastSKUpts'] = $quantityVerification['fpod_itemleastSKUpts'];

                $fsibg['fsbg_leastSKUb2bCSsp'] = $quantityVerification['fpod_leastSKUb2bCSsp'];
                $fsibg['fsbg_leastSKUb2bRetailsp'] = $quantityVerification['fpod_leastSKUb2bRetailsp'];

                $fsibg['fpod_poLandingCostleastSKU'] = $quantityVerification['fpod_poLandingCostleastSKU'];
                $fsibg['fpod_poMMGleastSKU'] = $quantityVerification['fpod_poMMGleastSKU'];

                $fsibg['stit_ID'] = $fsiidata['stii_itemmasterid'];
                $fsibg['fsbg_sellinprice'] = $quantityVerification['fpod_customerRatePikup'];
                $fsibg['fsbg_mrp'] = $quantityVerification['fpod_itemmrp'];
                $fsibg['fsbg_batch'] = $fsiidata['stii_batch'];
                $fsibg['fsbg_expirydate '] = date('Y-m-d', strtotime($fsiidata['stii_expirydate']));

                $fsibg['fsbg_has_gift'] = $hasGift;
                if (true == false) {
                    $entryCount = $db->getItemFromDB("SELECT COUNT(1) FROM finascop_stock_item_batch_group WHERE stit_ID = {$fsibg['stit_ID']} AND fsbg_customerRateHmDel = {$fsibg['fsbg_customerRateHmDel']} "
                        . "AND fsbg_customerRateCouDel = {$fsibg['fsbg_customerRateCouDel']} AND fsbg_customerRatePikup = {$fsibg['fsbg_customerRatePikup']} "
                        . "AND fsbg_leastSKUmrp = {$fsibg['fsbg_leastSKUmrp']} AND fsbg_has_gift = {$fsibg['fsbg_has_gift']} AND fsbg_expirydate = '{$fsibg['fsbg_expirydate']}' "
                        . "AND fsbg_batch = '{$fsiidata['stii_batch']}'");
                    if ($entryCount == 0) {
                        $itemCount = $db->getItemFromDB("SELECT COUNT(1) FROM finascop_stock_item_batch_group WHERE stit_ID = {$fsibg['stit_ID']}");
                        $fsibgentry = $db->perform('finascop_stock_item_batch_group', $fsibg);
                        $lastId = $db->insert_id();
                        $fsbg_id = $lastId;
                        $stiiIdfsibg['fsbg_id'] = $lastId;
                        $stiiIdfsibg['fsbg_idorg'] = $lastId;
                        //$db->executeSafe("UPDATE finascop_stock_branch_inventory SET fsbg_id = {$lastId}  WHERE stit_id = ? AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}", "i", [$_REQUEST['poStockItemId']]);
                        if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                            $fsiidupstatus = $db->perform('finascop_stock_item_inventorydetails ', $stiiIdfsibg, 'update', " stiid_barcode BETWEEN {$firstBrcode} AND {$barcode}");
                        }

                        $db->query("UPDATE finascop_stock_item_batch_group SET fsbg_name = '" . dechex($fsibg['stit_ID']) . "/" . (intval($itemCount) + 1) . "'  WHERE fsbg_id = {$lastId} ");
                    } else {
                        $stiiIdfsibg['fsbg_id'] = $db->getItemFromDB("SELECT fsbg_id FROM finascop_stock_item_batch_group WHERE stit_ID = {$fsibg['stit_ID']} AND fsbg_customerRateHmDel = {$fsibg['fsbg_customerRateHmDel']} "
                            . "AND fsbg_customerRateCouDel = {$fsibg['fsbg_customerRateCouDel']} AND fsbg_customerRatePikup = {$fsibg['fsbg_customerRatePikup']} "
                            . "AND fsbg_leastSKUmrp = {$fsibg['fsbg_leastSKUmrp']} AND fsbg_has_gift = {$fsibg['fsbg_has_gift']} AND fsbg_expirydate = '{$fsibg['fsbg_expirydate']}' "
                            . "AND fsbg_batch = '{$fsiidata['stii_batch']}'");
                        $fsbg_id = $stiiIdfsibg['fsbg_id'];
                        //$db->executeSafe("UPDATE finascop_stock_branch_inventory SET fsbg_id = {$entryCount}  WHERE stit_id = ? AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}", "i", [$_REQUEST['poStockItemId']]);
                        if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                            $fsiidupstatus = $db->perform('finascop_stock_item_inventorydetails ', $stiiIdfsibg, 'update', " stiid_barcode BETWEEN {$firstBrcode} AND {$barcode}");
                        }
                    }
                } else {
                    $entryCount = $db->getItemFromDB("SELECT COUNT(1) FROM finascop_stock_item_batch_group WHERE stit_ID = {$fsibg['stit_ID']} AND fsbg_leastSKUmrp = {$quantityVerification['fpod_leastSKUmrp']}");
                    if ($entryCount == 0) {
                        $itemCount = $db->getItemFromDB("SELECT COUNT(1) FROM finascop_stock_item_batch_group WHERE stit_ID = {$fsibg['stit_ID']}");
                        $fsibgentry = $db->perform('finascop_stock_item_batch_group', $fsibg);
                        $lastId = $db->insert_id();
                        $fsbg_id = $lastId;
                        $stiiIdfsibg['fsbg_id'] = $lastId;
                        $stiiIdfsibg['fsbg_idorg'] = $lastId;
                        //$db->executeSafe("UPDATE finascop_stock_branch_inventory SET fsbg_id = {$lastId}  WHERE stit_id = ? AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}", "i", [$_REQUEST['poStockItemId']]);
                        if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                            $fsiidupstatus = $db->perform('finascop_stock_item_inventorydetails ', $stiiIdfsibg, 'update', " stiid_barcode BETWEEN {$firstBrcode} AND {$barcode}");
                        }

                        $db->query("UPDATE finascop_stock_item_batch_group SET fsbg_name = '" . dechex($fsibg['stit_ID']) . "/" . (intval($itemCount) + 1) . "'  WHERE fsbg_id = {$lastId} ");
                    } else {
                        $stiiIdfsibg['fsbg_id'] = $db->getItemFromDB("SELECT fsbg_id FROM finascop_stock_item_batch_group WHERE stit_ID = {$fsibg['stit_ID']} AND fsbg_leastSKUmrp = {$quantityVerification['fpod_leastSKUmrp']}");
                        $stiiIdfsibg['fsbg_idorg'] = $stiiIdfsibg['fsbg_id'];
                        $fsbg_id = $stiiIdfsibg['fsbg_id'];
                        //$db->executeSafe("UPDATE finascop_stock_branch_inventory SET fsbg_id = {$entryCount}  WHERE stit_id = ? AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}", "i", [$_REQUEST['poStockItemId']]);
                        if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                            $fsiidupstatus = $db->perform('finascop_stock_item_inventorydetails ', $stiiIdfsibg, 'update', " stiid_barcode BETWEEN {$firstBrcode} AND {$barcode}");
                        }
                    }
                }
                $fsiidata['stii_itemmastername'] = $_REQUEST['poStockItemName'];

                $fsiidata['bmdd_id'] = $quantityVerification['bmd_id'];

                $fsiidata['stii_package_type2'] = $packageDetails['csb_package_type_id'];
                $fsiidata['stii_package_type3'] = $packageDetails['cs_package_type_id'];
                $fsiidata['stii_package_type4'] = $packageDetails['cos_package_type_id'];
                $fsiidata['stii_qty'] = $leastSKUCount;
                $fsiidata['stii_enteredqty'] = $nofqty;
                $fsiidata['stii_enteredpT'] = $_REQUEST['stii_enteredpT'];

                //new in finascop_stock_item_inventory    

                $fsiidata['stii_leastSKUmrp'] = $quantityVerification['fpod_leastSKUmrp'];
                $fsiidata['stii_leastSKUepr'] = $quantityVerification['fpod_leastSKUepr'];

                $fpod_effectiverate = $quantityVerification['fpod_effectiverate'];

                $fsiidata['stii_customerRateHmDel'] = $quantityVerification['fpod_customerRateHmDel'];
                $fsiidata['stii_customerRateCouDel'] = $quantityVerification['fpod_customerRateCouDel'];
                $fsiidata['stii_customerRatePikup'] = $quantityVerification['fpod_customerRatePikup'];

                $fsiidata['stii_itemleastSKUptr'] = $quantityVerification['fpod_itemleastSKUptr'];
                $fsiidata['stii_itemleastSKUpts'] = $quantityVerification['fpod_itemleastSKUpts'];

                $fsiidata['stii_leastSKUb2bCSsp'] = $quantityVerification['fpod_leastSKUb2bCSsp'];
                $fsiidata['stii_leastSKUb2bRetailsp'] = $quantityVerification['fpod_leastSKUb2bRetailsp'];

                $fsiidata['stii_poLandingCostleastSKU'] = $quantityVerification['fpod_poLandingCostleastSKU'];
                $fsiidata['stii_poMMGleastSKU'] = $quantityVerification['fpod_poMMGleastSKU'];

                //new in finascop_stock_branch_inventory    
                $fbis['fpod_leastSKUmrp'] = $quantityVerification['fpod_leastSKUmrp'];
                $fbis['fpod_leastSKUepr'] = $quantityVerification['fpod_leastSKUepr'];
                $fbis['fpod_customerRateHmDel'] = $quantityVerification['fpod_customerRateHmDel'];
                $fbis['fpod_customerRateCouDel'] = $quantityVerification['fpod_customerRateCouDel'];
                $fbis['fpod_customerRatePikup'] = $quantityVerification['fpod_customerRatePikup'];

                $fbis['fpod_itemleastSKUptr'] = $quantityVerification['fpod_itemleastSKUptr'];
                $fbis['fpod_itemleastSKUpts'] = $quantityVerification['fpod_itemleastSKUpts'];

                $fbis['fpod_leastSKUb2bCSsp'] = $quantityVerification['fpod_leastSKUb2bCSsp'];
                $fbis['fpod_leastSKUb2bRetailsp'] = $quantityVerification['fpod_leastSKUb2bRetailsp'];

                $fbis['fpod_poLandingCostleastSKU'] = $quantityVerification['fpod_poLandingCostleastSKU'];
                $fbis['fpod_poMMGleastSKU'] = $quantityVerification['fpod_poMMGleastSKU'];

                if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                    $fsiidata['stii_barcodestart'] = $firstBrcode;
                    $fsiidata['stii_barcodeend'] = $barcode;
                }
                $fsiidata['stii_cpd'] = $_SESSION['admin']->finascop_current_branch_id;
                $fsiidata['stii_mrp'] = $quantityVerification['fpod_itemmrp'];
                //$fsiidata['stii_selpri'] = $quantityVerification['fpod_effectiverate'];
                $fsiidata['stii_selpri'] = $quantityVerification['fpod_customerRatePikup'];
                $fsiidata['stii_epraft'] = $quantityVerification['fpod_effectiverate'];
                $fsiidata['stii_eprbft'] = $eprbft;
                $fsiidata['stii_created_level'] = $br_PyramidLevel;

                //print_r($fsiidata);
                $fsiistatus = $db->perform('finascop_stock_item_inventory', $fsiidata);
                $stiiId['stii_id'] = $db->insert_id();
                $stiiId['stiid_status'] = 1;
                $stiiId['cpd_branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
                $stiiId['is_branch'] = 0;
                $stiiId['bmdd_id'] = $fsiidata['bmdd_id'];
                if ($_SESSION['admin']->IS_RETALINE_LITE != 1) {
                    $fsiidupstatus = $db->perform('   finascop_stock_item_inventorydetails', $stiiId, 'update', " stiid_barcode BETWEEN {$firstBrcode} AND {$barcode}");
                }


                $fpoddata['fpod_giftbalqty'] = (int) $quantityVerification['fpod_giftbalqty'];
                $fpoddata['fpod_receivedqty'] = (int) $quantityVerification['fpod_receivedqty'] + (int) $nofqty;
                $fpoddata['fpod_balanceqty'] = (int) $quantityVerification['fpod_totalqty'] - (int) $fpoddata['fpod_receivedqty'];

                $fpoddata['fpod_leastSKUreceivedqty'] = (int) $quantityVerification['fpod_leastSKUreceivedqty'] + (int) $leastSKUCount;
                $fpoddata['fpod_leastSKUBalanceqty'] = (int) $quantityVerification['fpod_leastSKUTotalqty'] - (int) $fpoddata['fpod_leastSKUreceivedqty'];
                if ($fpoddata['fpod_leastSKUBalanceqty'] == 0 && $fpoddata['fpod_giftbalqty'] == 0) {
                    $podsvstatus = 2;
                } else if ($fpoddata['fpod_leastSKUBalanceqty'] == $quantityVerification['fpod_leastSKUTotalqty']) {
                    $podsvstatus = 0;
                } else {
                    $podsvstatus = 1;
                }
                $fpoddata['fpod_stockVerificationStatus'] = $podsvstatus;
                $podstatus = $db->perform('finascop_purchase_order_details', $fpoddata, 'update', "fpod_id = " . intval($_REQUEST['fpod_id']));

                $poOrdIt_orderId = $db->getItemSafe("SELECT poOrdIt_orderId FROM poOrderItemMapping WHERE poOrdIt_itemId = ? AND poOrdIt_poid = {$_REQUEST['fpo_id']}", "i", [$_REQUEST['poStockItemId']]);
                if ($poOrdIt_orderId > 0) {
                    $poim['poOrdIt_stockStatus'] = $podsvstatus;
                    $podMapstatus = $db->perform('poOrderItemMapping', $poim, 'update', " poOrdIt_itemId = {$_REQUEST['poStockItemId']} AND poOrdIt_poid = {$_REQUEST['fpo_id']}");

                    $orderItemsCount = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer_order_items WHERE customer_order_id = {$poOrdIt_orderId}");
                    $CompstockVerificationStatus = $db->getItemFromDB("SELECT COUNT(*) FROM poOrderItemMapping WHERE fpod_stockVerificationStatus = 2 and poOrdIt_orderId = {$poOrdIt_orderId}");
                    $PartstockVerificationStatus = $db->getItemFromDB("SELECT COUNT(*) FROM poOrderItemMapping WHERE fpod_stockVerificationStatus = 1 and poOrdIt_orderId = {$poOrdIt_orderId}");

                    $itemsSame = (count(array_unique(array_merge($orderItemsArray, $poItemsArray))) === count($poItemsArray)) ? 1 : 2;
                    if ($orderItemsCount == $CompstockVerificationStatus) {
                        $dataRco['updated_at'] = date('Y-m-d H:i:s');
                        $dataRco['status_id'] = 33;
                        $status = $db->perform('retaline_customer_order', $dataRco, 'update', " status_id = 30 AND order_id = {$poOrder}");
                    } else if ($orderItemsCount == $PartstockVerificationStatus) {
                        $dataRco['updated_at'] = date('Y-m-d H:i:s');
                        $dataRco['status_id'] = 32;
                        $status = $db->perform('retaline_customer_order', $dataRco, 'update', " status_id = 30 AND order_id = {$poOrder}");
                    }
                }

                //entryInFinascopeBranchEntry
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
                    $totalItemCount = $remainingCount['item_count'] + $leastSKUCount;

                    //                    $totalfpod_leastSKUmrp = ($quantityVerification['fpod_leastSKUmrp'] * $leastSKUCount) + ($remainingCount['item_count'] * $remainingCount['fpod_leastSKUmrp']);
                    //                    $fpod_leastSKUmrp = $totalfpod_leastSKUmrp / $totalItemCount;
                    //                    $quantityVerification['fpod_leastSKUmrp'] = round($fpod_leastSKUmrp, 2);
                    $totalfpod_leastSKUepr = ($quantityVerification['fpod_leastSKUepr'] * $leastSKUCount) + ($remainingCount['item_count'] * $remainingCount['fpod_leastSKUepr']);
                    $fpod_leastSKUepr = $totalfpod_leastSKUepr / $totalItemCount;
                    $quantityVerification['fpod_leastSKUepr'] = round($fpod_leastSKUepr, 2);
                    $totalfpod_customerRateHmDel = ($quantityVerification['fpod_customerRateHmDel'] * $leastSKUCount) + ($remainingCount['item_count'] * $remainingCount['fpod_customerRateHmDel']);
                    $fpod_customerRateHmDel = $totalfpod_customerRateHmDel / $totalItemCount;
                    $quantityVerification['fpod_customerRateHmDel'] = round($fpod_customerRateHmDel, 2);
                    $quantityVerification['fpod_customerRateCouDel'] = $quantityVerification['fpod_customerRateCouDel'];
                    $totalfpod_customerRatePikup = ($quantityVerification['fpod_customerRatePikup'] * $leastSKUCount) + ($remainingCount['item_count'] * $remainingCount['fpod_customerRatePikup']);
                    $fpod_customerRatePikup = $totalfpod_customerRatePikup / $totalItemCount;
                    $quantityVerification['fpod_customerRatePikup'] = round($fpod_customerRatePikup, 2);
                    $totalfpod_itemleastSKUptr = ($quantityVerification['fpod_itemleastSKUptr'] * $leastSKUCount) + ($remainingCount['item_count'] * $remainingCount['fpod_itemleastSKUptr']);
                    $fpod_itemleastSKUptr = $totalfpod_itemleastSKUptr / $totalItemCount;
                    $quantityVerification['fpod_itemleastSKUptr'] = round($fpod_itemleastSKUptr, 2);
                    $totalfpod_itemleastSKUpts = ($quantityVerification['fpod_itemleastSKUpts'] * $leastSKUCount) + ($remainingCount['item_count'] * $remainingCount['fpod_itemleastSKUpts']);
                    $fpod_itemleastSKUpts = $totalfpod_itemleastSKUpts / $totalItemCount;
                    $quantityVerification['fpod_itemleastSKUpts'] = round($fpod_itemleastSKUpts, 2);
                    $totalfpod_leastSKUb2bCSsp = ($quantityVerification['fpod_leastSKUb2bCSsp'] * $leastSKUCount) + ($remainingCount['item_count'] * $remainingCount['fpod_leastSKUb2bCSsp']);
                    $fpod_leastSKUb2bCSsp = $totalfpod_leastSKUb2bCSsp / $totalItemCount;
                    $quantityVerification['fpod_leastSKUb2bCSsp'] = round($fpod_leastSKUb2bCSsp, 2);
                    $totalfpod_leastSKUb2bRetailsp = ($quantityVerification['fpod_leastSKUb2bRetailsp'] * $leastSKUCount) + ($remainingCount['item_count'] * $remainingCount['fpod_leastSKUb2bRetailsp']);
                    $fpod_leastSKUb2bRetailsp = $totalfpod_leastSKUb2bRetailsp / $totalItemCount;
                    $quantityVerification['fpod_leastSKUb2bRetailsp'] = round($fpod_leastSKUb2bRetailsp, 2);
                    $totalfpod_poLandingCostleastSKU = ($quantityVerification['fpod_poLandingCostleastSKU'] * $leastSKUCount) + ($remainingCount['item_count'] * $remainingCount['fpod_poLandingCostleastSKU']);
                    $fpod_poLandingCostleastSKU = $totalfpod_poLandingCostleastSKU / $totalItemCount;
                    $quantityVerification['fpod_poLandingCostleastSKU'] = round($fpod_poLandingCostleastSKU, 2);
                    $totalfpod_poMMGleastSKU = ($quantityVerification['fpod_poMMGleastSKU'] * $leastSKUCount) + ($remainingCount['item_count'] * $remainingCount['fpod_poMMGleastSKU']);
                    $fpod_poMMGleastSKU = $totalfpod_poMMGleastSKU / $totalItemCount;
                    $quantityVerification['fpod_poMMGleastSKU'] = round($fpod_poMMGleastSKU, 2);
                } else {
                    $quantityVerification['fpod_itemleastSKUptr'] = $quantityVerification['fpod_itemleastSKUptr'];
                    $quantityVerification['fpod_itemleastSKUpts'] = $quantityVerification['fpod_itemleastSKUpts'];

                    $quantityVerification['fpod_leastSKUb2bCSsp'] = $quantityVerification['fpod_leastSKUb2bCSsp'];
                    $quantityVerification['fpod_leastSKUb2bRetailsp'] = $quantityVerification['fpod_leastSKUb2bRetailsp'];
                }
                $old_item_count = $db->getItemSafe("SELECT item_count FROM finascop_stock_branch_inventory WHERE stit_id = ? AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} ", "i", [$_REQUEST['poStockItemId']]);
                if ($efbe > 0) {

                    $fbisupd['fpod_leastSKUepr'] = $quantityVerification['fpod_leastSKUepr'];
                    $fbisupd['fpod_customerRateHmDel'] = $quantityVerification['fpod_customerRateHmDel'];
                    $fbisupd['fpod_customerRateCouDel'] = $quantityVerification['fpod_customerRateCouDel'];
                    $fbisupd['fpod_customerRatePikup'] = $quantityVerification['fpod_customerRatePikup'];
                    $fbisupd['fpod_poLandingCostleastSKU'] = $quantityVerification['fpod_poLandingCostleastSKU'];
                    $fbisupd['fpod_poMMGleastSKU'] = $quantityVerification['fpod_poMMGleastSKU'];
                    $fbisupd['fpod_itemleastSKUptr'] = $quantityVerification['fpod_itemleastSKUptr'];
                    $fbisupd['fpod_itemleastSKUpts'] = $quantityVerification['fpod_itemleastSKUpts'];
                    $fbisupd['fpod_leastSKUb2bCSsp'] = $quantityVerification['fpod_leastSKUb2bCSsp'];
                    $fbisupd['fpod_leastSKUb2bRetailsp'] = $quantityVerification['fpod_leastSKUb2bRetailsp'];

                    if ($quantityVerification['fpod_isRRP'] == 1) {
                        $db->executeSafe("UPDATE finascop_stock_branch_inventory SET item_count = item_count + {$fsiidata['stii_qty']}  WHERE stit_id = ? AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} ", "i", [$_REQUEST['poStockItemId']]);
                        $status = $db->perform('finascop_stock_branch_inventory', $fbisupd, 'update', " stit_id = {$_REQUEST['poStockItemId']} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}");
                    } else {
                        $db->executeSafe("UPDATE finascop_stock_branch_inventory SET item_count = item_count + {$fsiidata['stii_qty']}  WHERE stit_id = ? AND branch_id = {$_SESSION['admin']->finascop_current_branch_id}  AND fsbg_id = {$fsbg_id} ", "i", [$_REQUEST['poStockItemId']]);
                        $status = $db->perform('finascop_stock_branch_inventory', $fbisupd, 'update', " stit_id = {$_REQUEST['poStockItemId']} AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} AND fsbg_id = {$fsbg_id}");
                    }
                } else {
                    $fbis['item_count'] = $leastSKUCount;
                    $fbis['fsbg_id'] = $fsbg_id;
                    $fbis['purchasing_unit'] = $_POST['sileast_package_type_id'];
                    $status = $db->perform('finascop_stock_branch_inventory', $fbis);
                }

                $updatatLog['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
                $updatatLog['stit_id'] = $fsiidata['stii_qty'];
                $updatatLog['old_item_count'] = ($old_item_count > 0) ? $old_item_count : 0;
                $updatatLog['item_count'] = $fsiidata['stii_qty'];
                $updatatLog['fpod_skuPurchaseQty'] = $fsiidata['stii_qty'];
                $updatatLog['fpod_leastSKUepr'] = $quantityVerification['fpod_leastSKUepr'];
                $updatatLog['updated_on'] = date("Y-m-d H:i:s");
                $updatatLog['updated_by'] = $_SESSION['admin']->Finascop_UserId;
                $updatatLog['type'] = 'Stock Inward';
                $updatatLog['action'] = 'Stock update - Stock Inward';
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

                //$status = $db->perform('finascop_stock_branch_inventory_log', $updatatLog);
            } else {
                echo '{"success":false, "msg":"Quantity not matching with PO."}';
                exit();
            }
        }

        $totalFpodCount = $db->getItemSafe("SELECT COUNT(1) FROM finascop_purchase_order_details WHERE fpod_fpoId = ?", "i", [$_REQUEST['fpo_id']]);
        $fpodZcount = $db->getItemSafe("SELECT COUNT(1) FROM finascop_purchase_order_details WHERE fpod_fpoId = ? AND fpod_stockVerificationStatus = 0", "i", [$_REQUEST['fpo_id']]);
        $fpodOcount = $db->getItemSafe("SELECT COUNT(1) FROM finascop_purchase_order_details WHERE fpod_fpoId = ? AND fpod_stockVerificationStatus = 1", "i", [$_REQUEST['fpo_id']]);
        $fpodTcount = $db->getItemSafe("SELECT COUNT(1) FROM finascop_purchase_order_details WHERE fpod_fpoId = ? AND fpod_stockVerificationStatus = 2", "i", [$_REQUEST['fpo_id']]);
        if ($totalFpodCount == $fpodZcount) {
            $povsstatus = 0;
        } else if ($totalFpodCount == $fpodTcount) {
            //$fpoStatus['fpo_Active'] = 0;
            $povsstatus = 3;
        } else if ($fpodOcount > 0) {
            $povsstatus = 1;
        } else {
            $povsstatus = 2;
        }
        $fpoStatus['fpo_stockVerificationStatus'] = $povsstatus;
        $poStatus = $db->perform('finascop_purchase_order', $fpoStatus, 'update', "fpo_id = " . intval($_REQUEST['fpo_id']));

        $fpo_poNumber = $db->getItemSafe("SELECT fpo_poNumber FROM finascop_purchase_order WHERE fpo_id = ?", "i", [$_REQUEST['fpo_id']]);
        $poStatus = $db->query('commit');

        if ($poStatus == 1) {
            $msg = "' Barcode Generated Successfully'";
            echo '{"success":true,"msg":' . $msg . '}';

            //ob_start();
            //include('zebra.php');
            //include('barcodeview.php');
            //            $resHtml = ob_get_contents();
            //            ob_end_clean();
            //            echo $resHtml;
            //            header("Content-Disposition: attachment; filename=\"" . basename("barcode.prn") . "\"");
            //            header('Content-type: text/plain');
            //header("Content-Type: application/octet-stream");
            //            header("Connection: close");
        } else {
            $msg = "Error occuer while generating barcode";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;

    case 'rePrintBarcodeOp':
        $stiidD = $_POST['stii_id'];
        $barcode = $_POST['stiid_barcode'];
        $barcodeTo = $_POST['stiid_Tobarcode'];
        ob_start();
        // $startCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_item_inventorydetails WHERE stii_id=".$_REQUEST['stii_id']." AND stiid_barcode =".$barcode);
        // $endCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_item_inventorydetails WHERE stii_id=".$_REQUEST['stii_id']." AND stiid_barcode =".$barcodeTo);
        // if($startCount==$endCount){
        include('zebra.php');
        //include('barcodeview.php');
        $resHtml = ob_get_contents();
        ob_end_clean();

        header("Content-Disposition: attachment; filename=\"" . basename("barcode.prn") . "\"");
        header("Content-Type: application/octet-stream");
        header("Connection: close");
        echo $resHtml;
        exit();

        //include('barcodeview.php');

        break;
    case 'checkBarcode':
        $stiidD = $_POST['stii_id'];
        $barcode = $_POST['stiid_barcode'];
        $barcodeTo = $_POST['stiid_Tobarcode'];
        ob_start();
        $startCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_item_inventorydetails WHERE stii_id=" . $_REQUEST['stii_id'] . " AND stiid_barcode =" . $barcode);
        $endCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_item_inventorydetails WHERE stii_id=" . $_REQUEST['stii_id'] . " AND stiid_barcode =" . $barcodeTo);

        if ($startCount == $endCount) {
            echo '{"success":true}';
        } else {
            $msg = "Invalid Barcode Entered!";
            echo '{"success":false}';
            exit();
        }
        break;
    case 'listpoStockEntryItemStore':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'stii_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        //stii_created_level = 2 AND stii_cpd = {$_SESSION['admin']->finascop_current_branch_id} AND 
        $countQuery = "SELECT COUNT(*) FROM finascop_stock_item_inventory WHERE stii_fpoid = " . intval($_POST['fpo_id']) . " AND stii_fpodid = {$_POST['fpod_id']}";
        $listQuery = "SELECT stii_id,
        CASE WHEN (stii_itemmastername = '') THEN 'Gift'
        ELSE stii_itemmastername
        END AS stii_itemmastername,stii_itemmasterid,stii_batch,stii_qty,stii_isgift,DATE_FORMAT(stii_expirydate,'%d-%m-%Y') as stii_expirydate,DATE_FORMAT(stii_createdon,'%d-%m-%Y') as stii_createdon 
        FROM finascop_stock_item_inventory 
        WHERE stii_fpoid = " . intval($_POST['fpo_id']) . " AND stii_fpodid = {$_POST['fpod_id']} "
            . " ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'barcodeItemStore':
        $stiid = $_REQUEST['stii_id'];
        //$countQuery = "SELECT COUNT(*) FROM finascop_stock_item_inventorydetails WHERE stii_id={$stiid}";
        $listQuery = "SELECT stii_id,stiid_barcode FROM finascop_stock_item_inventorydetails WHERE stii_id={$stiid}";
        $data = $db->getMultipleData($listQuery, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        // echo '{"success":true,"data":' . json_encode($data) . '}';$db->printGridJson($countQuery, $listQuery);
        break;
    case 'calculateSellingPrice':

        $itemId = $_POST['itemId'];
        $fpod_id = $_POST['fpod_id'];
        $fpodDetails = $db->getFromDB("SELECT fpod_itemmrp,fpod_effectiverate,bmd_customer,bmd_percent,bmd_id,fpod_customerRatePikup FROM finascop_purchase_order_details WHERE fpod_id = {$fpod_id}  AND fpod_itemid = {$itemId}", true);
        //for margin distributions
        //$stit_GST = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$itemId}");
        //(float) $eprbft = ((float) $fpodDetails['fpod_effectiverate'] / (100 + (float) $stit_GST)) * 100;
        //(float) $mrpbft = ((float) $fpodDetails['fpod_itemmrp'] / (100 + (float) $stit_GST)) * 100;
        //        $marginDistriPercent = round(100 - (($eprbft / $mrpbft) * 100));
        //        if ($marginDistriPercent > 0) {
        //$bmd_id = $db->getItemFromDB("SELECT bmd_id FROM retaline_margindistributions WHERE is_default = 1 ");
        //$bmdd_id = $db->getItemFromDB("SELECT bmdd_id FROM retaline_margindistributionsDetails WHERE bmd_id = {$bmd_id} AND bmd_percent = {$marginDistriPercent}");
        //$bmd_customer = $db->getItemFromDB("SELECT bmd_customer FROM retaline_margindistributionsDetails WHERE bmdd_id = {$bmdd_id}");
        $priceDiff = floatval($fpodDetails['fpod_itemmrp']) - floatval($fpodDetails['fpod_effectiverate']);
        //To round off to 100%
        $bmd_customer = $db->getItemFromDB("SELECT bmd_customer FROM retaline_margindistributions WHERE bmd_id = {$fpodDetails['bmd_id']}");
        //$bmd_customer = ($fpodDetails['bmd_customer'] / $fpodDetails['bmd_percent']) * 100;
        $diffPercent = floatval($priceDiff) * $bmd_customer / 100;
        $sellingPrice = floatval($fpodDetails['fpod_itemmrp'] - $diffPercent);
        $sellingPrice = round($sellingPrice, 2);

        if ($fpodDetails['fpod_customerRatePikup'] > 0) {
            echo '{"success":true,"data":' . $fpodDetails['fpod_customerRatePikup'] . '}';
        } else {
            echo '{"success":false,"msg":"SP is not valid"}';
        }
        //        } else {
        //            echo '{"success":false,"msg":"Margin Percentage is not greater than zero"}';
        //        }
        //Selling price calculation changed on 3-12-19 above

        /* $subCategoryId = $db->getItemFromDB("SELECT product_category FROM finascop_stock_itemmaster WHERE stit_ID = {$itemId}");
          $marginDistribution = $db->getItemFromDB("SELECT subcat_bmd_id FROM brm_subcategory WHERE sub_category_id = {$subCategoryId}");
          if ($marginDistribution > 0) {
          $bmdCustomerPercent = $db->getItemFromDB("SELECT bmd_customer FROM retaline_margindistributions WHERE bmd_id = {$marginDistribution}");

          $priceDiff = floatval($fpodDetails['fpod_itemmrp']) - floatval($fpodDetails['fpod_effectiverate']);
          $diffPercent = floatval($priceDiff) * intval($bmdCustomerPercent) / 100;
          $sellingPrice = floatval($fpodDetails['fpod_effectiverate']) + (floatval($priceDiff) - floatval($diffPercent));
          } else {
          echo '{"success":false,"msg":"Margin Distribution not mapped"}';
          exit();
          } */

        break;
    case 'getGiftImage':
        $stii_id = $_POST['stii_id'];
        $qry = "select stii_id,stii_imageUrl from finascop_stock_item_inventory where `stii_id`= {$stii_id}";
        $data = $db->getFromDB($qry, true);
        // $result = $db->getMultipleData($stockregId, true);
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            $data = '';
            echo '{"success":true,"data":' . json_encode($data) . '}';
        }

        break;
    case 'getGiftImageS3details':
        $rid = $_POST['rid'];
        $data['file_name'] = ($rid . "_1"); /* add extension in js */
        $data['albumBucketName'] = AWSS3ASSETUPLOADBUCKET;
        $data['accessKey'] = AWSS3ASSETUPLOADACCESSID;
        $data['secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
        $data['bucketRegion'] = AWSS3ASSETUPLOADREGION;
        $data['oncompleteurl'] = 'gift/img/';
        $data['img_path_db'] = $db->getItemFromDB("select stii_imageUrl from finascop_stock_item_inventory where `stii_id`= {$rid}");
        echo "{success : true,'data':" . json_encode($data) . "}";
        break;
    case 'saveGiftImage':
        $db->query('begin');
        $bucketname = $_POST['bucket'];
        $filename = $_POST['uploaded_file_name'];
        $stii_id = $_POST['stii_id'];
        $file_path = $_POST['filepath'];
        $data = array(
            "stii_imageUrl" => $file_path,
        );
        $res = $db->perform('finascop_stock_item_inventory', $data, 'update', 'stii_id=' . $stii_id);
        $status = $db->query('commit');

        if ($status) {
            echo "{success: true,msg:'Updated Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'updateTotalQtyinPO':
        //fpod_stockVerificationStatus update issue there
        $fpod['fpod_totalqty'] = $_POST['new_totalqty'];
        $fpod['fpod_itemoffrqty'] = intval($_POST['new_totalqty']) - intval($_POST['currentQty']);
        $currentFpodDetails = $db->getFromSafe(" SELECT fpod_receivedqty,fpod_balanceqty,fpod_giftbalqty,fpod_qtyhistory from finascop_purchase_order_details WHERE fpod_id = ?", "i", [$_POST['podId']], true);
        if ($currentFpodDetails['fpod_qtyhistory'] == '') {
            $fpod['fpod_qtyhistory'] = 'Added extra quantity of ' . $fpod['fpod_itemoffrqty'] . ' on ' . date("Y-m-d H:i:s");
        } else {
            $fpod['fpod_qtyhistory'] = $currentFpodDetails['fpod_qtyhistory'] . ' , Added extra quantity of ' . $fpod['fpod_itemoffrqty'] . ' on ' . date("Y-m-d H:i:s");
        }

        $fpod['fpod_balanceqty'] = (int) $fpod['fpod_totalqty'] - (int) $currentFpodDetails['fpod_receivedqty'];
        if ($fpod['fpod_balanceqty'] == $fpod['fpod_totalqty']) {
            $podsvstatus = 0;
        } else {
            $podsvstatus = 1;
        }
        $fpod['fpod_stockVerificationStatus'] = $podsvstatus;
        $fpod['fpod_updatedby'] = $_SESSION['admin']->Finascop_UserId;
        $fpod['fpod_updatedon'] = date("Y-m-d H:i:s");
        $db->query('begin');
        $status = $db->perform('finascop_purchase_order_details', $fpod, 'update', 'fpod_id = ' . intval($_POST['podId']));

        $totalFpodCount = $db->getItemSafe("SELECT COUNT(1) FROM finascop_purchase_order_details WHERE fpod_fpoId = ?", "i", [$_POST['poId']]);
        $fpodZcount = $db->getItemSafe("SELECT COUNT(1) FROM finascop_purchase_order_details WHERE fpod_fpoId = ? AND fpod_stockVerificationStatus = 0", "i", [$_POST['poId']]);
        $fpodOcount = $db->getItemSafe("SELECT COUNT(1) FROM finascop_purchase_order_details WHERE fpod_fpoId = ? AND fpod_stockVerificationStatus = 1", "i", [$_POST['poId']]);
        $fpodTcount = $db->getItemSafe("SELECT COUNT(1) FROM finascop_purchase_order_details WHERE fpod_fpoId = ? AND fpod_stockVerificationStatus = 2", "i", [$_POST['poId']]);
        if ($totalFpodCount == $fpodZcount) {
            $povsstatus = 0;
        } else if ($totalFpodCount == $fpodTcount) {
            //$fpoStatus['fpo_Active'] = 0;
            $povsstatus = 3;
        } else if ($fpodOcount > 0) {
            $povsstatus = 1;
        } else {
            $povsstatus = 2;
        }
        $fpoStatus['fpo_stockVerificationStatus'] = $povsstatus;
        $fpoStatus['fpo_updatedon'] = date("Y-m-d H:i:s");
        $fpoStatus['fpo_updatedby'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('finascop_purchase_order', $fpoStatus, 'update', "fpo_id = " . intval($_POST['poId']));
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Updated Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'getPTStore':
        $packs = array();
        $itemId = $_POST['itemId'];
        $search_hint = $_POST['query'];
        $packTypes = $db->getFromDB("SELECT stdpckl11_package_type_id,stdpckl21_package_type_id,stdpckl31_package_type_id,stdpckl41_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$itemId}", true);
        //print_r($_POST);
        if (!in_array($packTypes['stdpckl11_package_type_id'], $packs)) {
            $packs[] = $packTypes['stdpckl11_package_type_id'];
        }
        //        if ($_POST['isMedicine'] != 1) {
        //            if (!in_array($_POST['stdpckl12'], $packs)) {
        //                $packs[] = $_POST['stdpckl12'];
        //            }
        //        }

        if (!in_array($packTypes['stdpckl21_package_type_id'], $packs)) {
            $packs[] = $packTypes['stdpckl21_package_type_id'];
        }
        if (!in_array($packTypes['stdpckl31_package_type_id'], $packs)) {
            $packs[] = $packTypes['stdpckl31_package_type_id'];
        }
        if (!in_array($packTypes['stdpckl41_package_type_id'], $packs)) {
            $packs[] = $packTypes['stdpckl41_package_type_id'];
        }
        $packs = array_filter($packs);
        if (count($packs) > 0) {
            $pachTyp = implode(',', $packs);
            $qry = "select package_type_id,package_type_name from " . FINASCOP_DB . "mypha_productpackage_type WHERE package_type_id IN ({$pachTyp}) AND status = 1 AND package_type_name LIKE '{$search_hint}%'order by package_type_name";
            $data = $db->getMultipleData($qry, true);
        }

        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getPackingDetails':
        $poStockItemUnits = $_POST['poStockItemUnits'];
        $itemId = $_POST['itemId'];
        $poStockItemQty = $_POST['poStockItemQty'];
        $packTypes = $db->getFromDB("SELECT stdpckl11_package_type_id,stdpckl21_package_type_id,stdpckl31_package_type_id,stdpckl41_package_type_id,least_package_type_name,least_package_type_id,stdpckl1_nos,stdpckl2_nos,stdpckl3_nos FROM finascop_stock_itemmaster WHERE stit_ID = {$itemId}", true);
        //print_r($packTypes);
        if ($poStockItemUnits == $packTypes['stdpckl12_package_type_id']) {
            $level = '4';
            $fpot_leastSKUqty = $poStockItemQty / $packTypes['stdpckl1_nos'];
        } else if ($poStockItemUnits == $packTypes['stdpckl11_package_type_id']) {
            $level = '3';
            $fpot_leastSKUqty = $poStockItemQty;
        } else if ($poStockItemUnits == $packTypes['stdpckl21_package_type_id']) {
            $level = '2';
            $fpot_leastSKUqty = $poStockItemQty * $packTypes['stdpckl2_nos'];
        } else if ($poStockItemUnits == $packTypes['stdpckl31_package_type_id']) {
            $level = '1';
            $fpot_leastSKUqty = $poStockItemQty * $packTypes['stdpckl2_nos'] * $packTypes['stdpckl3_nos'];
        }
        $result['leastSKUqty'] = $fpot_leastSKUqty;
        $result['least_package_type_id'] = $packTypes['least_package_type_id'];
        $result['least_package_type_name'] = $packTypes['least_package_type_name'];
        $result['success'] = true;
        if (!empty($result)) {
            echo json_encode($result);
        }
        break;
    case 'updateItemStock':
        $poStockItemId = $_POST['poStockItemId'];
        $isDarkStore = $db->getItemFromDB("SELECT br_type FROM finascop_branch WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");
        if ($isDarkStore == 0) {
            if ($poStockItemId > 0) {
                $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'UPDATECHILDSTOCK'");
                if (!empty($url)) {
                    $fields = array(
                        "parentItem" => $poStockItemId,
                        "branch" => $_SESSION['admin']->finascop_current_branch_id
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


        $msg = "'Stock updation processing.'";
        echo '{"success":true,"valid":true,"message":' . $msg . '}';

        break;
}
