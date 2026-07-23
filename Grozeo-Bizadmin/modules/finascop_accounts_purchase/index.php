<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");
require_once(INCLUDE_PATH . "/finascop_wallet_client.php");
$userid = $_SESSION['admin']->Finascop_UserId;

switch ($op) {
  case 'vendorPOSearchGridStore':
    $search_field = $_POST['search_field'];
    $type = $_POST['type'];
    switch ($type) {
      case 'Vendor PO':
        $handlinchgGstVal = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'HANDLING_CHRG_GST'");
        $hcgtax = 100 + $handlinchgGstVal;

        $countQuery = "SELECT COUNT(*) FROM finascop_purchase_order WHERE /*fpo_Active = 1 AND*/ branch_id = {$_SESSION['admin']->finascop_current_branch_id} and fpo_vendorName LIKE '{$search_field}%'";
        $listQuery = "SELECT fpo_id as search_id,fpo_vendorName as search_name,fpo_vendorId,fpo_poNumber,DATE_FORMAT(fpo_poDate,'%d-%m-%Y') as fpo_poDate,"
                . "fpo_shippingcharge,(fpo_shippingcharge*{$handlinchgGstVal}/{$hcgtax}) as handlinchgGstVal,fpo_gdiscpercent,fpo_poValue,fpo_isInvoicedStatus,fpo_poFinalValue,CASE WHEN fpo_stockVerificationStatus = 1 OR fpo_stockVerificationStatus = 2 THEN 'Partial' "
                . "WHEN fpo_stockVerificationStatus = 0 THEN 'Nothing' WHEN fpo_stockVerificationStatus = 3 THEN 'Completed' END AS stockVerificationStatus "
                . "FROM finascop_purchase_order WHERE /*fpo_Active = 1 AND*/ branch_id = {$_SESSION['admin']->finascop_current_branch_id} and fpo_vendorName LIKE '{$search_field}%'";
        break;
    }
    $db->printGridJson($countQuery, $listQuery);
    break;

  case 'listPodetailsStore':
    $poId = $_POST['poId'];
    if ($poId > 0) {
      $countQuery = "SELECT COUNT(*) FROM finascop_purchase_order_details fpod WHERE fpod_fpoId = {$poId} ";
      $listQuery = "SELECT fpod_id,fpod_itemid,fpod_itemname,fpod_itemqty,fpod_itemoffrratech,fpod_itemoffrrateetch,fpod_itemmrp,fpod_itemoffrqty,fpod_itemoffrrate,fpod_itemoffrrateet,fpod_effectiverate,"
              . "fpod_totalqty,fpod_receivedqty,fpod_invoicedqty,fpod_netamount,(SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = fpod_purchasingUnit) as itemUnit,"
              . "(SELECT stit_HSN_code FROM finascop_stock_itemmaster WHERE stit_ID = fpod_itemid) as itemhsn,(SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = fpod_itemid) as itemGST,"
              . "((SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = fpod_itemid)/2) AS sgst,((SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = fpod_itemid)/2) AS cgst,fpod_poLandingCost,fpod_pogstAmt,fpod_shippingchargegst "
              . " FROM finascop_purchase_order_details fpod WHERE fpod_fpoId = {$poId}";
      $db->printGridJson($countQuery, $listQuery);
    }

    break;
  case 'savePurchaseEntry':
    $invoiceNumber = trim($_POST['ref_no']);
    if (empty($invoiceNumber)) {
      $msg = "Enter a valid invoice number";
      echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
      exit();
    }
    $purchaseEntryData = array(
        "fpe_vendor_id" => $_POST['VendorId'],
        "fpe_vendorName" => $_POST['pe_party'],
        "fpe_fpoId" => $_POST['PoId'],
        "fpe_fpoPoNumber" => $_POST['purchcase_order_no'],
        "fpe_fpoPODate" => date('Y-m-d', strtotime($_POST['purchase_order_date'])),
        "fpe_invoiceNumber" => $invoiceNumber,
        "fpe_invoiceDate" => date('Y-m-d', strtotime($_POST['inv_date'])),
        "fpe_subTotal" => $_POST['pe_subTotal'],
        "fpe_genDiscount" => $_POST['pe_genDiscount'],
        "fpe_handlingCharge" => $_POST['pe_handlingCharge'],
        "fpe_handlingChargeGst" => $_POST['pe_handlingChargeGst'],
        "fpe_grossAmt" => $_POST['grossAmount'],
        "fpe_discount" => $_POST['discount'],
        "fpe_netAmount" => $_POST['netAmount'],
        "fpe_netQty" => $_POST['totalItemQty'],
        "fpe_netItems" => $_POST['totalItems'],
        "fpe_netTax" => $_POST['tax'],
        "fpe_netIgst" => $_POST['taxIgst'],
        "fpe_netCgst" => $_POST['taxCgst'],
        "fpe_netSgst" => $_POST['taxSgst'],
        "fpe_createdOn" => date("Y-m-d H:i:s"),
        "fpe_created_by" => $_SESSION['admin']->Finascop_UserId,
        "branch_id" => $_SESSION['admin']->finascop_current_branch_id
    );
    $db->query('begin');
    $estatus = $db->perform("finascop_purchase_entry", $purchaseEntryData);
    $new_item_id = $db->insert_id();

    $peItemSGriddata = json_decode(stripslashes($_POST['peItemSGriddata']));

    if (!empty($peItemSGriddata)) {
      $totalEPR = 0;
      $fpe_amtBfTax = 0;
      foreach ($peItemSGriddata as $peItemS) {

        if (intval($peItemS->fpod_invoiceqty) > 0) {
          $peItemS_table = array(
              'fped_fpeId' => $new_item_id,
              'fped_fpodId' => $peItemS->fpod_id,
              'fped_fpodItemId' => $peItemS->fpod_itemid,
              'fped_fpodItemName' => $peItemS->fpod_itemname,
              'fped_qtyInvoiced' => $peItemS->fpod_invoiceqty,
              'fped_igstAmt' => $peItemS->itemIgst,
              'fped_cgstAmt' => $peItemS->itemCgst,
              'fped_sgst' => $peItemS->itemSgst,
              'fped_amtBfTax' => $peItemS->amtBfTax,
              'fped_amtAfTax' => $peItemS->amtAfTax,
              'fped_createdOn' => date("Y-m-d H:i:s"),
              'fped_createdBy' => $_SESSION['admin']->Finascop_UserId
          );
          $fpod_poLandingCost = $db->getItemFromDB("SELECT fpod_poLandingCost FROM finascop_purchase_order_details WHERE fpod_id = {$peItemS->fpod_id}");
          $fpe_amtBfTax += $peItemS->amtBfTax;
          $fpe_netTax += $peItemS->itemGST;
          $fpe_netCGST += $peItemS->itemCgst;
          $fpe_netSGST += $peItemS->itemSgst;
          $estatus = $db->perform("finascop_purchase_entry_details", $peItemS_table);

          $fpodInvoiceQty = $db->getItemFromDB("SELECT fpod_invoicedqty FROM finascop_purchase_order_details WHERE fpod_id = {$peItemS->fpod_id}");
          $fpodData['fpod_invoicedqty'] = intval($fpodInvoiceQty) + intval($peItemS->fpod_invoiceqty);
          $itemEPR = $peItemS->fpod_invoiceqty * $fpod_poLandingCost;
          $totalEPR = $totalEPR + $itemEPR;
          $estatus = $db->perform("finascop_purchase_order_details", $fpodData, 'update', 'fpod_id =' . $peItemS->fpod_id);
        }
      }
    }
    $pe_handlingChargeGst = $pe_handlingChargeGst / 2;
    $pe_handlingChargeCGST = $pe_handlingChargeSGST = round($pe_handlingChargeGst, 2);
    $fpe_netSGST = round($fpe_netSGST, 2);
    $fpe_netCGST = round($fpe_netCGST, 2);
    $fpeData['fpe_netTax'] = $fpe_netTax;
    $estatus = $db->perform("finascop_purchase_entry", $fpeData, 'update', 'fpe_id =' . $new_item_id);
    $pePoMatch = $db->getFromSafe("SELECT SUM(fpod_invoicedqty) as invqtysum,SUM(fpod_itemqty) as recqtysum FROM finascop_purchase_order_details WHERE fpod_fpoId = ?", "i", [$_POST['PoId']], true);
    if ($pePoMatch['invqtysum'] > 0) {
      $podata['fpo_isInvoicedStatus'] = 1;
      $podata['fpo_invoicedOn'] = date("Y-m-d H:i:s");
    }
    if ($pePoMatch['invqtysum'] == $pePoMatch['recqtysum']) {
      $podata['fpo_isInvoicedStatus'] = 2;
      $podata['fpo_invoicedOn'] = date("Y-m-d H:i:s");
    }
    $estatus = $db->perform("finascop_purchase_order", $podata, 'update', 'fpo_id = ' . intval($_POST['PoId']));
    $vendoDetails = $db->getFromSafe("SELECT stpa_Fname,stpa_MobileNo FROM finascop_stock_party WHERE stpa_id = ?", "i", [$_POST['VendorId']], true);

    $estatus = $db->query('commit');

    if ($estatus == 1) {

      echo '{"success":true,"valid":true,"msg":"Data saved ."}';
      exit;
    } else {
      echo '{"success":false,"error":"Error while saving data."}';
      exit;
    }
    break;
  case 'listPurchaseEntries':
    $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
    $limit = is_numeric($limit) ? $limit : 12;
    $start = is_numeric($start) ? $start : 0;
    $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
    $sort = empty($sort) ? 'fpe_id' : $sort;
    $dir = empty($dir) ? 'DESC' : $dir;

    $filter_qry = " WHERE 1 = 1 ";
    // if (isset($_POST['filter'])) {
    if (count($_POST['filter']) > 0) {
      $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
      $filter = $_POST['filter'];
      foreach ($filter as $key => $val) {
        $type = $val['data']['type'];
        $value = $val['data']['value'];

        $field = $val['field'];
        switch ($field) {
          //switch ($val['data']['type']) {
          //    case 'string':
          //        $filter_qry .= " AND " . $val['field'] . "  LIKE  '" . $val['data']['value'] . "%'";
          //        break;
          case 'fpe_vendorName':
            $filter_qry .= " AND fpe_vendorName LIKE '%" . $value . "%' ";
            break;
          case 'fpe_fpoPoNumber':
            $filter_qry .= " AND fpe_fpoPoNumber LIKE '%" . $value . "%' ";
            break;
          case 'fpe_fpoPODate':
            $value = str_replace("/", "", $value);
            $value = substr($value, 4, 4) . substr($value, 0, 2) . substr($value, 2, 2);
            $filter_qry .= " AND  DATE_FORMAT(fpe_fpoPODate,'%Y%m%d') " . $comparisons[$val['data']['comparison']] . " " . $value;
            break;
          case 'fpe_invoiceNumber':
            $filter_qry .= " AND fpe_invoiceNumber LIKE '%" . $value . "%' ";
            break;
          case 'fpe_invoiceDate':
            $value = str_replace("/", "", $value);
            $value = substr($value, 4, 4) . substr($value, 0, 2) . substr($value, 2, 2);
            $filter_qry .= " AND  DATE_FORMAT(fpe_invoiceDate,'%Y%m%d') " . $comparisons[$val['data']['comparison']] . " " . $value;
            break;
          case 'fpe_netAmount':
            $filter_qry .= " AND fpe_netAmount LIKE '%" . $value . "%' ";
            break;
          case 'fpe_netIgst':
            $filter_qry .= " AND fpe_netIgst LIKE '%" . $value . "%' ";
            break;
          case 'fpe_netCgst':
            $filter_qry .= " AND fpe_netCgst LIKE '%" . $value . "%' ";
            break;
          case 'fpe_netSgst':
            $filter_qry .= " AND fpe_netSgst LIKE '%" . $value . "%' ";
            break;
        }
      }
    }
    if ($sort == 'fpe_invoiceDate') {
      $sort = 'fpe_id';
    }
    if ($sort == 'fpe_fpoPODate') {
      $sort = 'fpe_id';
    }
    $date = date('%d-%m-%Y');
    $countDataQuery = "SELECT count(*) from finascop_purchase_entry  {$filter_qry} ";
    $listQuery = "SELECT  fpe_id,fpe_vendorName,fpe_fpoPoNumber,DATE_FORMAT(fpe_fpoPODate,'%d-%m-%Y') as fpe_fpoPODate,fpe_invoiceNumber,DATE_FORMAT(fpe_invoiceDate,'%d-%m-%Y') as fpe_invoiceDate,fpe_grossAmt,fpe_discount,fpe_netQty,fpe_netItems,fpe_netTax,fpe_netAmount,"
            . "ROUND(fpe_netIgst,2) as fpe_netIgst,ROUND(fpe_netCgst,2) as fpe_netCgst,ROUND(fpe_netSgst,2) as fpe_netSgst,ROUND(fpe_netIgst+fpe_netCgst+fpe_netSgst,2) AS totalGst from finascop_purchase_entry {$filter_qry} ORDER BY fpe_id DESC LIMIT $start,$limit ";
    $db->printGridJson($countDataQuery, $listQuery);
    break;
  case 'getVendorDetails':
    $search_field = $_POST['search_field'];

    $countQuery = "SELECT COUNT(*) FROM finascop_stock_party  WHERE br_id = {$_SESSION['admin']->finascop_current_branch_id} AND stpa_Fname LIKE '$search_field%'";
    $listQuery = "SELECT stpa_id AS customerId,CONCAT(stpa_Fname,' ',stpa_Lname) as stpa_Fname, stpa_GSTIN, stpa_Address ,stpa_City AS stpa_City,stpa_PINCODE AS stpa_PINCODE,
(SELECT st_name FROM finascop_state b INNER JOIN finascop_district d ON b.st_ID = d.st_Id WHERE d.dst_Id = a.dst_Id)AS st_name,
(SELECT b.st_ID FROM finascop_state b INNER JOIN finascop_district d ON b.st_ID = d.st_Id WHERE d.dst_Id = a.dst_Id)AS st_id,
(SELECT c.dst_Id FROM finascop_district c WHERE c.dst_Id = a.dst_Id )AS dst_Id,
(SELECT dst_Name FROM finascop_district c WHERE c.dst_Id = a.dst_Id )AS dst_Name
  FROM finascop_stock_party a      WHERE br_id = {$_SESSION['admin']->finascop_current_branch_id} AND stpa_Fname LIKE '$search_field%'";
    $db->printGridJson($countQuery, $listQuery);

    break;
  case 'listPurchaseEntryForPayProce':
    $vendorId = $_POST['vendorId'];
    $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
    $limit = is_numeric($limit) ? $limit : 12;
    $start = is_numeric($start) ? $start : 0;
    $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
    $sort = empty($sort) ? 'fpe_id' : $sort;
    $dir = empty($dir) ? 'DESC' : $dir;

    $filter_qry = " WHERE 1 = 1 AND fpe_vendor_id = {$vendorId}  ";
    if (isset($_POST['filter'])) {
        $allowedFields = ['acc_id', 'acc_name', 'vendor_name', 'invoice_date', 'invoice_number', 'acc_status', 'total_amount'];
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
    $countDataQuery = "SELECT count(*) from finascop_purchase_entry  {$filter_qry} ";
    $listQuery = "SELECT  fpe_id,fpe_vendorName,fpe_fpoPoNumber,DATE_FORMAT(fpe_fpoPODate,'%d-%m-%Y') as fpe_fpoPODate,fpe_invoiceNumber,DATE_FORMAT(fpe_invoiceDate,'%d-%m-%Y') as fpe_invoiceDate,fpe_grossAmt,fpe_discount,fpe_netQty,fpe_netItems,fpe_netTax,fpe_netAmount,"
            . "fpe_netIgst,fpe_netCgst,fpe_netSgst,if(fpe_netAmtPaid = fpe_netAmount,'Paid','Not Paid') AS paymentStatus from finascop_purchase_entry {$filter_qry} ORDER BY fpe_id DESC LIMIT $start,$limit ";
    $db->printGridJson($countDataQuery, $listQuery);
    break;
  case 'invoice_profile_view':
    ob_start();
    include('invoiceDetail.php');
    $resHtml = ob_get_clean();
    echo $resHtml;
    break;
  case 'processCashPayment':
    $db->query('begin');
    $fpeids = json_decode($_POST['cashPEIds']);
    foreach ($fpeids as $fpeid) {
      $estatus = $db->query("UPDATE  finascop_purchase_entry SET fpe_netAmtPaid = fpe_netAmount WHERE fpe_id = " . $fpeid);
    }
    $vendoDetails = $db->getFromSafe("SELECT stpa_Fname,stpa_MobileNo FROM finascop_stock_party WHERE stpa_id = ?", "i", [$_POST['cashVendorID']], true);

    if ($status == 1) {
      $db->query('commit');
      echo "{\"success\":true,\"msg\":'Cash Payment is under process.'}";
    } else {
      echo "{\"success\":false,\"msg\":'Cash Payment processing failed.'}";
      echo "\n,\"error\": " . json_encode($result) . "}";
      exit(1);
    }

    break;

  case 'getBankLedgers':
    $FinascopWC = new FinascopWalletClient(FINASCOPAPIDOMAIN);

    $branchApiKey = $db->getItemFromDB("SELECT br_ReferenceId FROM finascop_branch WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");
    $RefIDs['companyApiKey'] = $db->getItemFromDB("SELECT comp_ReferenceId FROM finascop_company WHERE comp_id ={$_SESSION['admin']->finascop_current_company_id}");

    $apiresult = $FinascopWC->getBankLedgers($branchApiKey, $RefIDs);

    $result = json_decode($apiresult, true);
    if ($result['success'] == 'true') {
      $totalCount = sizeof($result['Data']['Ledgers']);
      $data = array("totalCount" => $totalCount, "data" => $result['Data']['Ledgers']);
      echo json_encode($data);
    } else {
      echo '{"success":false,"data":[]}';
      echo "\n,\"error\": " . json_encode($result['error']) . "}";
      exit(1);
    }
    break;
  case 'processChequePayment':
    $db->query('begin');

    $fpeids = json_decode($_POST['chequePEIds']);

    foreach ($fpeids as $fpeid) {
      $qry = "UPDATE  finascop_purchase_entry SET fpe_netAmtPaid = fpe_netAmount WHERE fpe_id = " . $fpeid;
      $estatus = $db->query($qry);
    }
    $vendoDetails = $db->getFromSafe("SELECT stpa_Fname,stpa_MobileNo FROM finascop_stock_party WHERE stpa_id = ?", "i", [$_POST['chequeVendorID']], true);

    if ($status == 1) {
      $db->query('commit');
      echo "{\"success\":true,\"msg\":'Cheque Payment is under process.'}";
    } else {
      echo "{\"success\":false,\"msg\":'Cheque Payment processing failed.'}";
      echo "\n,\"error\": " . json_encode($result) . "}";
      exit(1);
    }

    break;
  case 'purchaseEntryAdded':
    $poId = $_POST['poid'];
    $pe_handlingCharge = 0;
    $fpe_grossAmt = 0;
    $fpe_handlingChargeGst = 0;
    $peIds = $db->getMultipleData("SELECT fpe_genDiscount,fpe_handlingCharge,fpe_grossAmt,fpe_handlingChargeGst FROM finascop_purchase_entry WHERE fpe_fpoId = {$poId}", true);
    if (count($peIds) > 0) {
      foreach ($peIds as $peId) {
        $pe_handlingCharge += $peId['fpe_handlingCharge'];
        $fpe_grossAmt += $peId['fpe_grossAmt'];
        $fpe_handlingChargeGst += $peId['fpe_handlingChargeGst'];
      }
    }
    if (count($peIds) > 0) {
      echo '{"success":true,"data":' . $pe_handlingCharge . ',"groamt":' . $fpe_grossAmt . ',"fpe_handlingChargeGst":' . $fpe_handlingChargeGst . '}';
    } else {
      echo '{"success":true,"data":0}';
    }
    break;
  case 'viewPurchaseEntryData':
    $fpe_id = isset($_POST['fpe_id']) ? intval($_POST['fpe_id']) : 0;
    if ($fpe_id) {
      $sql = "SELECT * FROM finascop_purchase_entry  WHERE fpe_id = " . $fpe_id;
      $results = $db->getFromDB($sql, true);
      if (!$results) {
        echo '{"success":true,"data":[]}';
      } else {
        echo '{"success":true, "data":',
        json_encode($results),
        '}';
      }
    }
    break;
  case 'listPEdetailsStore':
    $fped_fpeId = $_POST['fped_fpeId'];
    if ($fped_fpeId > 0) {
      $countQuery = "SELECT COUNT(*) FROM finascop_purchase_entry_details  WHERE fped_fpeId = {$fped_fpeId} ";
      $listQuery = "SELECT  fped_fpodItemName,CONCAT(fped_qtyInvoiced,' ',(select csb_package_type_name from finascop_stock_itemmaster WHERE stit_ID = fped_fpodItemId)) AS fped_qtyInvoiced,
      fped_igstAmt,fped_cgstAmt,fped_sgst,fped_amtBfTax,fped_amtAfTax,(fped_amtAfTax - fped_amtBfTax) AS totalTax FROM finascop_purchase_entry_details  WHERE fped_fpeId = {$fped_fpeId}";
      $db->printGridJson($countQuery, $listQuery);
    }

    break;
}    