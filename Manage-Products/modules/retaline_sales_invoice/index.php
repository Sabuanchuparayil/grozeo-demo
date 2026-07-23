<?php
require_once(INCLUDE_PATH . "/finascop_common_functions.php");
require_once(INCLUDE_PATH . "/finascop_wallet_client.php");
switch ($op) {
    case 'listBarCodesOfItem':
        $data = $_POST;
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 12;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'stiid_barcode' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;

        $bbsd_id = $data['bbsd_id'];

        $db->query("SET @slNo = {$start};");
        $listQuery = "SELECT @slNo := @slNo + 1 as slNo, stiid_barcode FROM retaline_B2B_SalesOrderDetails_barcodes
         WHERE bbsd_id = {$bbsd_id} ORDER BY {$sort} {$dir} LIMIT {$start},{$limit}";
        $countDataQuery = "SELECT count(1) from retaline_B2B_SalesOrderDetails_barcodes WHERE bbsd_id = {$bbsd_id} ";
        $db->printGridJson($countDataQuery, $listQuery);
        break;
    case 'updateHandlingCharges':
        $data = $_POST;
        $db->query("UPDATE retaline_B2B_SalesOrder SET bbso_SOValue = bbso_SOValue - bbso_HandlingCharges + {$data['bbso_HandlingCharges']},
        bbso_InvValAtax = bbso_InvValAtax - bbso_HandlingCharges + {$data['bbso_HandlingCharges']} WHERE bbso_id = {$data['bbso_id']}");

        $status = $db->query("UPDATE retaline_B2B_SalesOrder SET bbso_HandlingCharges = {$data['bbso_HandlingCharges']} WHERE bbso_id = {$data['bbso_id']}");

        $con = "bbso_id = {$data['bbso_id']}";
        $TotAmount = $db->getItemFromDB("SELECT bbso_InvValAtax FROM retaline_B2B_SalesOrder WHERE {$con}");
        $paise = round($TotAmount - ($Ruppes = floor($TotAmount)), 2) * 100;
        $B2BSalesInvData['bbso_totInFig'] = "Rupees " . $Ruppes . " and " . $paise . " Paise";
        $B2BSalesInvData['bbso_totInWords'] = getIndianCurrency(number_format((float) $TotAmount, 2, '.', ''));

        $data['bbso_updatedon'] = date("Y-m-d H:i:s");
        $data['bbso_updatedby'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('retaline_B2B_SalesOrder', $B2BSalesInvData, 'update', $con);

        if ($status == 1) {
            echo '{"success":true}';
        } else {
            echo '{"success":false}';
        }
        break;
    case 'printInvoice':
        $data = $_GET;
        CreateInvoicePDF($data['bbso_InvNumber'], $data['bbso_id']);
        break;
    case 'saveB2BSalesInvoice':
        $bbso_id = $_POST['bbso_id'];
        $db->query('begin');
        if (!empty($bbso_id)) {
            $neIinvDetails = $db->getFromDB("SELECT COALESCE(MAX(bbso_invid) + 1,0) as bbso_invid, CONCAT('INV',DATE_FORMAT(CURDATE(),'%Y'),LPAD(COALESCE(MAX(bbso_invid) + 1,0), 6, '0')) AS bbso_InvNumber FROM retaline_B2B_SalesOrder", true);
            $data['bbso_InvNumber'] = $neIinvDetails['bbso_InvNumber'];
            $data['bbso_invid'] = $neIinvDetails['bbso_invid'];
            $data['bbso_InvDate'] = date("Y-m-d H:i:s");
            $data['status_id'] = 9;
            $data['bbso_InvoiceStatus'] = 2;

            $data['bbso_InvByUserName'] = $db->getItemFromDB("SELECT UserName FROM finascop_usr_master WHERE UserId = " . $_SESSION['admin']->Finascop_UserId);
            $data['bbso_InvIPAddress'] = getIPAddress();

            $con = "bbso_id = {$bbso_id}";
            $status = $db->perform('retaline_B2B_SalesOrder', $data, 'update', $con);
        } else {
            $status = 0;
        }


        //BEGIN FINANCIAL TRANSACION
        //CREDIT CUSTOMER 
        //DEBIT SALES LEDGER    
        $query = "SELECT b2b_Customer_ID,b2b_Customer_Name,bbso_InvValAtax,br_ID,bbso_InvNumber,bbso_InvDate,bbso_CGSTVal,bbso_SGSTVal,bbso_SOValue FROM retaline_B2B_SalesOrder rbs WHERE bbso_id = {$bbso_id}";
        //echo $query;
        
        $B2BSalesInvData = $db->getFromDB($query, true);
        $taxValue = $db->getFromDB("SELECT SUM(b2bso_cgst_value) AS cgstval,SUM(b2bso_sgst_value) as sgstval FROM retaline_B2B_SalesOrderDetails WHERE bbso_id = {$bbso_id}", true);
        $totalEPR = $db->getItemFromDB("SELECT SUM(b2bso_itemqty*b2bso_effectiverate) FROM retaline_B2B_SalesOrderDetails WHERE bbso_id = {$bbso_id}");
        $b2bso_amounttotal = $db->getItemFromDB("SELECT SUM(b2bso_amount) FROM retaline_B2B_SalesOrderDetails WHERE bbso_id = {$bbso_id}");
        $custGST = $db->getItemFromDB("SELECT b2b_Customer_gst FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = {$B2BSalesInvData['b2b_Customer_ID']}");

        $totalDebitSalesValue = $B2BSalesInvData['bbso_SOValue'];

        $totalSales = $b2bso_amounttotal;
        $totalGst = $taxValue['cgstval'] + $taxValue['sgstval'];
        $hcMargimDistribution = $B2BSalesInvData['bbso_SOValue'] - ($totalSales + $totalGst);

        $fsto_id = $db->getItemFromDB("SELECT fsto_id FROM finascop_stock_transfer_order WHERE fstr_id = {$bbso_id} AND fsto_ordertype = 2");
        $fstoBarcodes = $db->getMultipleData("SELECT stiid_id,stiid_barcode FROM finascop_stock_transfer_order_details_barcodes WHERE fsto_id = {$fsto_id}", true);
        $companyMargin = 0;
        $operationMargin = 0;
        $csMargin = 0;
        $distributorMargin = 0;
        $stiid_poLandingCostleastSKU = 0;
        $totGST = 0;
        foreach ($fstoBarcodes as $fstoBarcod) {
            $poDetail = $db->getFromDB("SELECT stiid_fpoid,stiid_fpodid,stiid_itemmasterid,stiid_poLandingCostleastSKU FROM finascop_stock_item_inventorydetails WHERE stiid_id = {$fstoBarcod['stiid_id']}", true);
            $margins = $db->getFromDB("SELECT fpod_b2bCSgst,fpod_b2bcs_companymargin,fpod_b2bcs_opermargin,fpod_b2bcs_csmargin,
                fpod_b2bRetailgst,fpod_b2bretai_companymargin,fpod_b2bretai_opermargin,fpod_b2bretai_csmargin,fpod_b2bretai_dtrbtrmargin,
                fpod_itemptrgst,fpod_itemptr_dtrbtrmargin,fpod_itemptr_csmargin,fpod_itemptr_opermargin,fpod_itemptr_companymargin,
                fpod_itemptsgst,fpod_itempts_csmargin,fpod_itempts_opermargin,fpod_itempts_companymargin FROM finascop_purchase_order_details WHERE fpod_id = {$poDetail['stiid_fpodid']}", true);
            $stit_fixedB2BRates = $db->getItemFromDB("SELECT stit_fixedB2BRates FROM finascop_stock_itemmaster WHERE stit_ID = {$poDetail['stiid_itemmasterid']}");
            $skuNos = $db->getFromDB("SELECT cos_nos,ds_nos FROM finascop_stock_itemmaster WHERE stit_ID = {$poDetail['stiid_itemmasterid']}", true);
            if ($_SESSION['admin']->br_PyramidLevel == 2) {
                $stiid_poLandingCostleastSKU = ($stiid_poLandingCostleastSKU + $poDetail['stiid_poLandingCostleastSKU']) * $skuNos['cos_nos'] * $skuNos['ds_nos'];
            } else {
                $stiid_poLandingCostleastSKU = ($stiid_poLandingCostleastSKU + $poDetail['stiid_poLandingCostleastSKU']) * $skuNos['ds_nos'];
            }

            if ($stit_fixedB2BRates == 1) {
                if ($_SESSION['admin']->br_PyramidLevel == 2) {
                    $companyMargin = $companyMargin + $margins['fpod_itempts_companymargin'];
                    $operationMargin = $operationMargin + $margins['fpod_itempts_opermargin'];
                    $csMargin = $csMargin + $margins['fpod_itempts_csmargin'];
                    $totGST = $totGST + $margins['fpod_itemptsgst'];
                } elseif ($_SESSION['admin']->br_PyramidLevel == 3) {
                    $companyMargin = $companyMargin + $margins['fpod_itemptr_companymargin'];
                    $operationMargin = $operationMargin + $margins['fpod_itemptr_opermargin'];
                    $csMargin = $csMargin + $margins['fpod_itemptr_csmargin'];
                    $distributorMargin = $distributorMargin + $margins['fpod_itemptr_dtrbtrmargin'];
                    $totGST = $totGST + $margins['fpod_itemptrgst'];
                }
            } else {
                if ($_SESSION['admin']->br_PyramidLevel == 2) {
                    $companyMargin = $companyMargin + $margins['fpod_b2bcs_companymargin'];
                    $operationMargin = $operationMargin + $margins['fpod_b2bcs_opermargin'];
                    $csMargin = $csMargin + $margins['fpod_b2bcs_csmargin'];
                    $totGST = $totGST + $margins['fpod_b2bCSgst'];
                } elseif ($_SESSION['admin']->br_PyramidLevel == 3) {
                    $companyMargin = $companyMargin + $margins['fpod_b2bretai_companymargin'];
                    $operationMargin = $operationMargin + $margins['fpod_b2bretai_opermargin'];
                    $csMargin = $csMargin + $margins['fpod_b2bretai_csmargin'];
                    $distributorMargin = $distributorMargin + $margins['fpod_b2bretai_dtrbtrmargin'];
                    $totGST = $totGST + $margins['fpod_b2bRetailgst'];
                }
            }
        }

//        if ($_SESSION['admin']->br_PyramidLevel == 2) {
//            $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 10"; //6-SalesB2BCStoDist
//            $wqSettings = $db->getFromDB($query, true);
//            $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
//
//            $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = {$B2BSalesInvData['b2b_Customer_ID']}");
//            if (!empty($account)) {
//                $transctionTemplate['dr']['csB2BDistribuLedger']['key'] = $account;
//            } else {
//                $msg = "'Customer Ledger not available. B2B Sales Invoice.'";
//                echo '{"success":false,"msg":' . $msg . '}';
//                exit(1);
//            }
//       
//            $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM 
//			( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM 
//			(SELECT @r := {$B2BSalesInvData['br_ID']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";
//
//            $br_ReferenceIDs = $db->getMultipleData($br_query);
//            //$transctionTemplate['cr']['csStock']['amt'] = $totalEPR;
//
//            $transctionTemplate['cr']['cgst']['amt'] = round($taxValue['cgstval'],2);
//            $transctionTemplate['cr']['sgst']['amt'] = round($taxValue['sgstval'],2);
//            $transctionTemplate['cr']['marginCompany']['amt'] = round($companyMargin,2);
//            $transctionTemplate['cr']['marginCompany']['br_ReferenceID'] = $br_ReferenceIDs[1][0]; 
//            $transctionTemplate['cr']['marginOperation']['amt'] = round($operationMargin,2);
//            $transctionTemplate['cr']['marginOperation']['br_ReferenceID'] = $br_ReferenceIDs[1][0]; 
//            $transctionTemplate['cr']['marginCentralStore']['amt'] = round($csMargin,2);
//            $transctionTemplate['cr']['marginCentralStore']['br_ReferenceID'] = $br_ReferenceIDs[0][0]; 
//            $marginsAndTax = ($transctionTemplate['cr']['cgst']['amt']  + $transctionTemplate['cr']['sgst']['amt'] + $transctionTemplate['cr']['marginCompany']['amt'] + $transctionTemplate['cr']['marginOperation']['amt'] +$transctionTemplate['cr']['marginCentralStore']['amt']);
//            $transctionTemplate['cr']['csSales']['amt'] = round($totalDebitSalesValue - $marginsAndTax, 2);                        
//            $transctionTemplate['dr']['csB2BDistribuLedger']['amt'] = round($transctionTemplate['cr']['csSales']['amt'] + $marginsAndTax,2);
//            //$transctionTemplate['cr']['roundOff']['amt'] = round($totalDebitSalesValue - $marginsAndTax, 2) - $transctionTemplate['dr']['csB2BDistribuLedger']['amt'];    
//            $temp = $totalEPR - $totalDebitSalesValue ;
//            $transctionTemplate['cr']['roundOff']['amt'] = round($temp,2);
////            print_r("totalEPR : ".$totalEPR. " totalDebitSalesValue: " .$totalDebitSalesValue . "Round off: " .$transctionTemplate['cr']
//            //print_r($transctionTemplate);
//
//        } elseif ($_SESSION['admin']->br_PyramidLevel == 3) {
//            $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 14"; //9-SalesB2BDistToRetailor
//            $wqSettings = $db->getFromDB($query, true);
//            $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
//
//            $account = $db->getItemFromDB("SELECT accled_ReferenceId FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = {$B2BSalesInvData['b2b_Customer_ID']}");
//            if (!empty($account)) {
//                $transctionTemplate['dr']['b2bRetailerLedger']['key'] = $account;
//            } else {
//                $transctionTemplate['dr']['b2bRetailerLedger']['key'] = 'Replace with accled_ReferenceId of ' . $B2BSalesInvData['b2b_Customer_Name'] . 'on branch br_ID' . $B2BSalesInvData['br_ID'];
//            }
//            
//            $br_query = "SELECT T2.br_ReferenceID,T2.br_cpd,T2.br_ID FROM 
//            ( SELECT @r AS _id, (SELECT @r := br_cpd FROM finascop_branch WHERE br_ID = _id) AS br_cpd, @l := @l + 1 AS lvl FROM 
//            (SELECT @r := {$B2BSalesInvData['br_ID']}, @l := 0) vars, finascop_branch m WHERE @r <> 0) T1 JOIN finascop_branch T2 ON T1._id = T2.br_ID";
//            $br_ReferenceIDs = $db->getMultipleData($br_query);
//            
//           
//            $transctionTemplate['cr']['cgst']['amt'] = round($taxValue['cgstval'],2);
//            $transctionTemplate['cr']['sgst']['amt'] = round($taxValue['sgstval'],2);
//            $transctionTemplate['cr']['marginCompany']['amt'] = round($companyMargin,2);
//            $transctionTemplate['cr']['marginCompany']['br_ReferenceID'] = $br_ReferenceIDs[2][0]; 
//            $transctionTemplate['cr']['marginOperation']['amt'] = round($operationMargin,2);
//            $transctionTemplate['cr']['marginOperation']['br_ReferenceID'] = $br_ReferenceIDs[2][0]; 
//            $transctionTemplate['cr']['marginCentralStore']['amt'] = round($csMargin,2);
//            $transctionTemplate['cr']['marginCentralStore']['br_ReferenceID'] = $br_ReferenceIDs[1][0]; 
//            $transctionTemplate['cr']['marginDistributer']['amt'] = round($distributorMargin,2);
//            $transctionTemplate['cr']['marginDistributer']['br_ReferenceID'] = $br_ReferenceIDs[0][0];             
//            $marginsAndTax = ($transctionTemplate['cr']['cgst']['amt']  + $transctionTemplate['cr']['sgst']['amt'] + $transctionTemplate['cr']['marginCompany']['amt'] + $transctionTemplate['cr']['marginOperation']['amt'] + $transctionTemplate['cr']['marginCentralStore']['amt'] + $transctionTemplate['cr']['marginDistributer']['amt']);
//            $transctionTemplate['cr']['distriSales']['amt'] = round($totalDebitSalesValue - $marginsAndTax, 2);                        
//            $transctionTemplate['dr']['b2bRetailerLedger']['amt'] = round($transctionTemplate['cr']['csSales']['amt'] + $marginsAndTax,2);
//            $temp = $totalEPR - $totalDebitSalesValue ;
//            $transctionTemplate['cr']['roundOff']['amt'] = round($temp,2);
//
//        }
//        $b2CustDetails = $db->getFromDB("SELECT b2b_Customer_Name,b2b_Customer_Mobile FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = {$B2BSalesInvData['b2b_Customer_ID']}");
//
//        $search = array("#AMT#", "#NO#", "#ID#", "#NAME#", "#PHONE#");
//        $replace = array($totalEPR, $bbso_id, $B2BSalesInvData['bbso_InvNumber'], $b2CustDetails['b2b_Customer_Name'], $b2CustDetails['b2b_Customer_Mobile']);
//        $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);
//
//        $fields = array(
//            "waqu_TransDate" => date('Y-m-d'),
//            "waqu_comment" => $transctionTemplate['comments'],
//            "waqu_SourceID" => intval($bbso_id),
//            "waqs_id" => intval($wqSettings['waqs_id']),
//            "waqu_Amount" => doubleval($totalEPR),
//            "br_id" => intval($_SESSION['admin']->finascop_current_branch_id),
//            "waqu_Data" => stripslashes(json_encode($transctionTemplate))
//        );
//        //print_r($transctionTemplate);
//        $status = $db->perform('finascop_wallet_queue', $fields);

        //CREDIT CUSTOMER 
        //DEBIT SALES LEDGER    
        //END FINANCIAL TRANSACION

        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'B2B Sales Invoice {$data['bbso_InvNumber']} is ready.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error while creating B2B Sales Invoice.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'b2bSOCustomerDetails':

        $b2bSOCustomer = $_POST['b2bSOCustomer'];
        $result = $db->getFromDB("SELECT b2b_Customer_Incharge,b2b_Customer_pincode,b2b_Customer_Mobile,b2b_Customer_gst,rbsch_name"
                . "  FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = {$b2bSOCustomer}", true);
        if (!empty($result)) {
            echo json_encode($result);
        } else {
            echo '{"data":[]}';
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
    case 'getB2BSODetails':
        $data = $_POST;
        $query = "SELECT bbso_id, bbso_SONumber, bbso_SODate,b2b_Customer_ID,b2b_Customer_Name,bbso_SOValue,"
                . "bbso_HandlingCharges ,(SELECT status from retaline_B2B_Status rbbs WHERE rbbs.status_id = rbs.status_id)AS bbso_Active "
                . "FROM retaline_B2B_SalesOrder rbs WHERE bbso_id = {$data['bbso_id']}";
        $B2BSalesOrderData = $db->getFromDB($query, true);
        $listQuery = "SELECT bbso_id,bbsd_id,b2bso_itemid, b2bso_itemname, b2bso_itemmrp, b2bso_itemqty, b2bso_itemrate,b2bso_gst,b2bso_itemoffrqty,"
                . "b2bso_itemPkg,b2bso_amount,b2bso_discountpercent,b2bso_discountamt, b2bso_netamount "
                . "FROM retaline_B2B_SalesOrderDetails WHERE bbso_id = {$data['bbso_id']}";
        $B2BSOItemDetails = $db->getMultipleData($listQuery, true);

        if (!empty($B2BSOItemDetails)) {
            echo '{"totalCount":' . count($B2BSOItemDetails) . ',"data":' . json_encode($B2BSOItemDetails) . ',"SOdata":' . json_encode($B2BSalesOrderData) . '}';
        } else {
            echo '{"totalCount":"0","data":[],"SOdata":[]}';
        }

        break;
    case 'generateUniqueID':
        $uniqueId = '';
        while ($uniqueId == '') {
            $uniqueId = getNewFinascopApiKey();
        }
        echo '{"uid":"' . $uniqueId . '"}';
        break;

    case 'listB2BSalesInvoces':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 23;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'bbso_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;

        $filter_qry = " WHERE bbso_InvoiceStatus IN (1,2) AND  br_ID = {$_SESSION['admin']->finascop_current_branch_id}";
        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    } else if ($val['field'] == 'bbso_InvoiceStatusName') {
                            if ($val['data']['value'] == 'Invoiced') {
                                $fiterItem = 2;
                                $filter_qry .= " and (bbso_InvoiceStatus= {$fiterItem}) ";
                            } else {
                                $fiterItem = 1;
                                $filter_qry .= " and (bbso_InvoiceStatus = {$fiterItem}) ";
                            }
                        }
                        break;
                }
            }
        }

        $countDataQuery = "SELECT count(1) from retaline_B2B_SalesOrder {$filter_qry} ";
        $listQuery = "SELECT bbso_id, bbso_InvNumber, bbso_InvDate,b2b_Customer_ID,b2b_Customer_Name,bbso_SOValue,bbso_InvoiceStatus,"
                . "CASE WHEN bbso_InvoiceStatus = 0 THEN 'Not Ready for Invoice' WHEN bbso_InvoiceStatus = 1 THEN 'Ready for Invoice' WHEN bbso_InvoiceStatus = 2 THEN 'Invoiced' END AS bbso_InvoiceStatusName,"
                . "bbso_HandlingCharges ,(SELECT status from retaline_B2B_Status rbbs WHERE rbbs.status_id = rbs.status_id) AS bbso_Active,status_id "
                . "FROM retaline_B2B_SalesOrder rbs {$filter_qry} ORDER BY {$sort} {$dir} LIMIT {$start},{$limit}";

        $db->printGridJson($countDataQuery, $listQuery);
        break;
}