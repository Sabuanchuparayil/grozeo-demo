<?php

/**
 * Description of DeliveryFinascop
 *
 * @author monsy
 */
class DeliveryFinascop
{
  protected const PayOnDelivery = 1;
  protected const OnlinePayment = 2;
  protected const WalletPayment = 3;
  protected const CODWithWallet = 4;
  protected const onlinaWithWallet = 5;
  protected const OnlineOnDelivery = 6;
  protected const CashOnDelivery = 7;

  protected const CashReceipt = 1;
  protected const CashPayment = 2;
  protected const BankReceipt = 3;
  protected const BankPayment = 4;
  protected const Journal = 5;
  protected const ContraEntry = 6;
  protected const Sales_Order = 7;
  protected const Sales_Invoice = 8;
  protected const Purchase_Order = 9;
  protected const Purchase_Receipt = 10;
  protected const DebitNote = 11;
  protected const CreditNote = 12;


  protected const RefId = 0;
  protected const BANKGGOA = 17;
  protected const BANKGOCA = 18;
  protected const BANKGTPA = 19;
  protected const BANKGCCA = 20;
  protected const IGSTInput = 21;
  protected const CGSTInput = 22;
  protected const SGSTInput = 23;
  protected const UTGSTInput = 24;
  protected const PODCollection_OCA = 25;
  protected const TenantSecurityDeposit = 26;
  protected const Courier_GLP = 27;
  protected const TradeDeposits_STL = 28;
  protected const SalesOrder = 29;
  protected const TenantSalesOrder = 30;
  protected const TenantSales = 31;
  protected const TenantDelivery = 32;
  protected const TenantSalesOrderRoundOff = 33;
  protected const TenantIGST = 34;
  protected const TenantCGST = 35;
  protected const TenantSGST = 36;
  protected const IGST = 37;
  protected const CGST = 38;
  protected const SGST = 39;
  protected const TenantUTGST = 40;
  protected const TCSIGST = 41;
  protected const TCSCGST = 42;
  protected const TCSSGST = 43;
  protected const TCSUTGST = 44;
  protected const TDSoncontractors_sub_contractorsAY22_23 = 45;
  protected const SuspenseAccount = 46;
  protected const DeliveryCharges = 47;
  protected const RoundOff = 48;
  protected const BankChargesTDR = 49;
  protected const GrozeoMonthlyPlan = 50;
  protected const GrozeoAnnualPlan = 51;
  protected const DeliveryChargesIncome = 52;
  protected const GrozeoSupportServices = 53;
  protected const TDROnlineTransactions = 54;
  protected const OrderProcessingCharges = 55;
  protected const CustomerWallet = 56;
  protected const TDSonE_commercetransactionsAY22_23 = 57;

  public static function DeliveryVoucherOld($order_id)
  {
    $db = new sqlDb(DSN);
    $log['entity_id'] = $order_id;
    $log['createdOn'] = date('Y-m-d H:i:s');
    $log['type'] = "DeliveryVoucher";
    $log['status'] = 1;
    $log['comments'] = "DeliveryVoucher";
    $db->perform("finascop_log", $log);

    $lastId = $db->insert_id();


    $delVoucherItems = $db->getFromDB("SELECT fsto_id,order_delivery_charge,storegroup_id,order_courier_charge,order_delivery_charge_gst,
		order_order_id AS orders, order_total_amount AS amount_before_tax, order_roundoff, order_id, order_branch_id,order_tcs_utgst,
    order_tds,order_tcs,order_tcs_cgst,order_tcs_sgst,order_tcs_igst,order_total_utgst,order_delivery_charge_utgst,
		order_total_sgst AS sgst, order_total_cgst AS cgst,order_delivery_charge_igst,order_total_igst,order_delivery_charge_cgst,order_delivery_charge_sgst FROM retaline_customer_order rco 
		INNER JOIN finascop_stock_transfer_order fsto ON rco.order_id = fsto.fstr_id AND fsto.fsto_ordertype = 1 WHERE fsto_id = {$order_id}", true);

    $amount_before_tax = round(($delVoucherItems["amount_before_tax"]), 2);
    $tenantDeliveryCharge = ($delVoucherItems["order_delivery_charge"] + $delVoucherItems["order_courier_charge"]) - $delVoucherItems["order_delivery_charge_gst"];
    $tenantDeliveryCharge = round($tenantDeliveryCharge, 2);

    $tenantIGST = round($delVoucherItems['order_delivery_charge_igst'] + $delVoucherItems['order_total_igst'], 2);
    $tenantCGST = round($delVoucherItems['cgst'] + $delVoucherItems['order_delivery_charge_cgst'], 2);
    $tenantSGST = round($delVoucherItems['sgst'] + $delVoucherItems['order_delivery_charge_sgst'], 2);
    $tenantUTGST = round($delVoucherItems['order_total_utgst'] + $delVoucherItems['order_delivery_charge_utgst'], 2);


    $tcs_igst = $delVoucherItems['order_tcs_igst'];
    $tcs_cgst = $delVoucherItems['order_tcs_cgst'];
    $tcs_sgst = $delVoucherItems['order_tcs_sgst'];
    $tcs_utgst = $delVoucherItems['order_tcs_utgst'];

    $tdsIT = $delVoucherItems['order_tds'];

    $roundOff = round(($delVoucherItems["order_roundoff"]), 2);

    $data['TransactionTypeId'] = static::Journal;
    $data['docTypeID'] = static::Journal;
    $data['entry_type'] = 1;
    $data['Narration'] = "Delivery of Sales Order : " . $delVoucherItems['orders'] . '. Ready for pickup.';
    $data['reference'] = "Delivery of Sales Order: " . $delVoucherItems['orders'];

    $data['entry_RefId'] = $db->getItemFromDB("SELECT entry_RefId FROM retaline_customer_order WHERE order_id = {$delVoucherItems['order_id']}");
    //sha1(microtime(true) . mt_rand(10000, 90000));
    $uplog['orderOrderId'] = $delVoucherItems['orders'];
    $data['order_order_id'] = $delVoucherItems['orders'];
    $data['order_event'] = "DeliveryVoucher";
    if ($delVoucherItems['order_branch_id'] > 0) {

      $orderBranchId =  $delVoucherItems['order_branch_id'];
      $orderBranchsg = $db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$orderBranchId}");
      $ledgerRefId = $db->getItemFromDB("SELECT storeRefId FROM finascop_branch_group 
                WHERE store_group_id = {$orderBranchsg}");
      $strgrpName = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group 
                WHERE store_group_id = {$orderBranchsg}");
      $br_Name_store_group = $db->getItemFromDB("SELECT br_Name FROM finascop_branch WHERE br_ID = {$orderBranchId}");
      $data['storeGroupId'] = $orderBranchsg;
      $data['storeGroupRefId'] = $ledgerRefId;
      $data['br_ID_store_group'] = $orderBranchId;
      $data['br_Name_store_group'] = $br_Name_store_group;
      $data['StoreGroupName'] = $strgrpName;
    } else {
      $data['storeGroupId'] = -1;
    }

    $data['Account'] = array();
    $data['Particulars'] = array();


    $accIndex = 0;
    $data['Account'][$accIndex]['ledgerId'] = static::TenantSales;
    $data['Account'][$accIndex]['amount'] =  round($amount_before_tax, 2);
    $data['Account'][$accIndex]['particulars'] = "Tenant Sales";
    $data['Account'][$accIndex]['isDebtor'] = 1;
    $accIndex++;

    $data['Account'][$accIndex]['ledgerId'] = static::TenantDelivery;
    $data['Account'][$accIndex]['amount'] = $tenantDeliveryCharge;
    $data['Account'][$accIndex]['particulars'] = "Tenant Delivery Charges";
    $data['Account'][$accIndex]['isDebtor'] = 1;
    $accIndex++;

    if ($tenantIGST > 0) {

      $data['Account'][$accIndex]['ledgerId'] = static::TenantIGST;
      $data['Account'][$accIndex]['amount'] = $tenantIGST;
      $data['Account'][$accIndex]['particulars'] = "Tenant IGST";
      $data['Account'][$accIndex]['isDebtor'] = 1;
      $accIndex++;
    }


    if ($tenantCGST > 0) {

      $data['Account'][$accIndex]['ledgerId'] = static::TenantCGST;
      $data['Account'][$accIndex]['amount'] = $tenantCGST;
      $data['Account'][$accIndex]['particulars'] = "Tenant CGST";
      $data['Account'][$accIndex]['isDebtor'] = 1;
      $accIndex++;
    }

    if ($tenantSGST > 0) {

      $data['Account'][$accIndex]['ledgerId'] = static::TenantSGST;
      $data['Account'][$accIndex]['amount'] = $tenantSGST;
      $data['Account'][$accIndex]['particulars'] = "Tenant SGST";
      $data['Account'][$accIndex]['isDebtor'] = 1;
      $accIndex++;
    }
    if ($tenantUTGST > 0) {

      $data['Account'][$accIndex]['ledgerId'] = static::TenantUTGST;
      $data['Account'][$accIndex]['amount'] = $tenantUTGST;
      $data['Account'][$accIndex]['particulars'] = "Tenant UTGST";
      $data['Account'][$accIndex]['isDebtor'] = 1;
      $accIndex++;
    }
    $parIndex = 0;
    if (round($tcs_igst, 2) <> 0) {

      $data['Particulars'][$parIndex]['ledgerId'] = static::TCSIGST;
      $data['Particulars'][$parIndex]['amount'] = $tcs_igst;
      $data['Particulars'][$parIndex]['particulars'] = "TCS IGST';// Collected at Source";
      $data['Particulars'][$parIndex]['isDebtor'] = 0;
      $parIndex++;
    }

    if (round($tcs_cgst, 2) <> 0) {
      $data['Particulars'][$parIndex]['ledgerId'] = static::TCSCGST;
      $data['Particulars'][$parIndex]['amount'] = $tcs_cgst;
      $data['Particulars'][$parIndex]['particulars'] = "TCS CGST"; // Collected at Source";
      $data['Particulars'][$parIndex]['isDebtor'] = 0;
      $parIndex++;
    }

    if (round($tcs_sgst, 2) <> 0) {
      $data['Particulars'][$parIndex]['ledgerId'] = static::TCSSGST;
      $data['Particulars'][$parIndex]['amount'] = $tcs_sgst;
      $data['Particulars'][$parIndex]['particulars'] = "TCS SGST"; // Collected at Source";
      $data['Particulars'][$parIndex]['isDebtor'] = 0;
      $parIndex++;
    }
    if (round($tcs_utgst, 2) <> 0) {
      $data['Particulars'][$parIndex]['ledgerId'] = static::TCSUTGST;
      $data['Particulars'][$parIndex]['amount'] = $tcs_utgst;
      $data['Particulars'][$parIndex]['particulars'] = "TCS UTGST"; // Collected at Source";
      $data['Particulars'][$parIndex]['isDebtor'] = 0;
      $parIndex++;
    }


    $data['Particulars'][$parIndex]['ledgerId'] = static::TDSonE_commercetransactionsAY22_23;
    $data['Particulars'][$parIndex]['amount'] = $tdsIT;
    $data['Particulars'][$parIndex]['particulars'] = "TDS on E-commerce transactions";
    $data['Particulars'][$parIndex]['isDebtor'] = 0;
    $parIndex++;

    $accountSum = $amount_before_tax + $tenantDeliveryCharge + $tenantIGST + $tenantCGST + $tenantSGST + $tenantUTGST;
    //$tenant = ($accountSum - (($tcs_utgst + $tcs_cgst + $tcs_sgst + $tcs_igst + $tdsIT) + $roundOff));
    $tenant = ($accountSum + $roundOff) - ($tcs_utgst + $tcs_cgst + $tcs_sgst + $tcs_igst + $tdsIT);

    // $tenant = round(($accountSum - ((round($tcs_utgst,2) + round($tcs_cgst, 2) + round($tcs_sgst, 2) + round($tcs_igst, 2) + round($tdsIT, 2)) + round($roundOff, 2) )), 2);
    $ledgerRefId = $db->getItemFromDB("SELECT storeRefId FROM finascop_branch_group 
		  WHERE store_group_id = {$delVoucherItems['storegroup_id']}");

    $data['Particulars'][$parIndex]['ledgerId'] = static::RefId;
    $data['Particulars'][$parIndex]['ledgerRefId'] = $ledgerRefId;
    $data['Particulars'][$parIndex]['amount'] = round(($tenant), 2);
    $data['Particulars'][$parIndex]['particulars'] = $strgrpName;
    $data['Particulars'][$parIndex]['isDebtor'] = 0;
    $ledgerRefIdIndex = $parIndex;
    $parIndex++;

    if ($roundOff > 0) {
      //$data['Particulars'][$ledgerRefIdIndex]['amount'] = round(($tenant + $roundOff),2);
      //$data['Account'][0]['amount'] =  round($amount_before_tax + $roundOff, 2);

      $data['Account'][$accIndex]['ledgerId'] = static::TenantSalesOrderRoundOff;
      $data['Account'][$accIndex]['amount'] = round(abs($roundOff), 2);
      $data['Account'][$accIndex]['particulars'] = "Tenant Sales Round Off";
      $data['Account'][$accIndex]['isDebtor'] = 1;
      $accIndex++;
    }
    if ($roundOff < 0) {
      //$data['Account'][0]['amount'] = round($amount_before_tax + $roundOff, 2);

      $data['Particulars'][$parIndex]['ledgerId'] = static::TenantSalesOrderRoundOff;
      $data['Particulars'][$parIndex]['amount'] = round(abs($roundOff), 2);
      $data['Particulars'][$parIndex]['particulars'] = "Tenant Sales Round Off";
      $data['Particulars'][$parIndex]['isDebtor'] = 0;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////

    $log['entity_id'] = $order_id;
    $log['createdOn'] = date('Y-m-d H:i:s');
    $log['type'] = "Order Account Statement";
    $log['status'] = 0;
    $log['comments'] = "Order Account Statement: " . $delVoucherItems['orders'] . " TCS and TDS entries.";
    $db->perform("finascop_log", $log);

    $lastStmtId = $db->insert_id();

    try {
      $cBQuery = "SELECT closingBalance FROM order_account_statement  WHERE storeId = {$orderBranchId} ORDER BY id DESC LIMIT 0,1";

      $closingBalance = $db->getItemFromDB($cBQuery);

      if (empty($closingBalance)) {
        $closingBalance =  round(0.00, 2);
      } else {
        $closingBalance =  round($closingBalance, 2);
      }


      $oasEntry = array();
      $tcs = $tcs_igst + $tcs_cgst + $tcs_sgst +  $tcs_utgst;
      $oasEntry['orderId'] = $order_id;
      $oasEntry['orderOrderId'] = $delVoucherItems['orders'];
      $oasEntry['storeGroupId'] = $orderBranchsg;
      $oasEntry['storeId'] = $orderBranchId;
      $oasEntry['particulars'] = "TCS";
      $oasEntry['isDebtor'] = 0;
      $oasEntry['amount'] = round($tcs, 2);
      $oasEntry['openingBalance'] = $closingBalance;

      $db->perform("order_account_statement", $oasEntry);



      $oasEntry = array();
      $closingBalance = round($closingBalance - $tcs, 2);
      $oasEntry['orderId'] = $order_id;
      $oasEntry['orderOrderId'] = $delVoucherItems['orders'];
      $oasEntry['storeGroupId'] = $orderBranchsg;
      $oasEntry['storeId'] = $orderBranchId;
      $oasEntry['particulars'] = "TDS";
      $oasEntry['isDebtor'] = 0;
      $oasEntry['amount'] = round($tdsIT, 2);
      $oasEntry['openingBalance'] = $closingBalance;

      $db->perform("order_account_statement", $oasEntry);


      $uplog['comments'] = "Success:Order Account Statement : " . $delVoucherItems['orders'] . " TCS and TDS entries.";
      $uplog['status'] = 1;
      $db->perform("finascop_log", $uplog, 'update', " id = {$lastStmtId}");
    } catch (Exception $exAccStmt) {
      $uplog['comments'] = "Failed Exception thrown: Order Account Statement : " . $delVoucherItems['orders'] . " TCS and TDS entries.";
      $uplog['status'] = 2;
      $db->perform("finascop_log", $uplog, 'update', " id = {$lastStmtId}");
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////


    try {
      $cURLConnection = curl_init();
      $headers = [
        "x-functions-key:" . DATAENTRY_KEY,
        'Content-Type:application/json'
      ];

      $url = DATAENTRY_ENDPOINT . "/api/FinascopDataEntry";
      curl_setopt($cURLConnection, CURLOPT_URL, $url);
      curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($cURLConnection, CURLOPT_ENCODING, '');
      curl_setopt($cURLConnection, CURLOPT_MAXREDIRS, 10);
      curl_setopt($cURLConnection, CURLOPT_TIMEOUT,  0);
      curl_setopt($cURLConnection, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($cURLConnection, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
      curl_setopt($cURLConnection, CURLOPT_POST, 1);
      curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, json_encode($data));
      curl_setopt($cURLConnection, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS | CURLPROTO_HTTP);

      $response = curl_exec($cURLConnection);
      curl_close($cURLConnection);
    } catch (Exception $ex) {
      $uplog['status'] = 3;
      $uplog['comments'] = "Delivery Voucher: " . $delVoucherItems['orders'] . "Exception : " . $ex . 'Data: ' . json_encode($data);
      $db->perform("finascop_log", $uplog, 'update', " id = {$lastId}");
    }
    $status = json_decode($response, true);




    if ($status['statusId'] == 1) {
      $log['status'] = 1;
      $log['comments'] = "Result:" . $response . 'Data: ' . json_encode($data);
      $db->perform("finascop_log", $log, 'update', " id = {$lastId}");
      return true;
    } else {
      $log['status'] = 2;
      $log['comments'] = "Delivery Voucher of SalesOrder :" . $delVoucherItems['orders'] . ' Status: ' . $response . 'URL: ' . $url . ' headers: ' . implode(',', $headers) . 'Data: ' . json_encode($data);
      $db->perform("finascop_log", $log, 'update', " id = {$lastId}");
      return false;
    }
  }
  public static function DeliveryVoucher($order_id)
  {
    $db = new sqlDb(DSN);

    $delVoucherItems = $db->getFromDB("SELECT fsto_id,order_delivery_charge,storegroup_id,order_courier_charge,order_delivery_charge_gst,
		order_order_id AS orders, order_total_amount AS amount_before_tax, order_roundoff, order_id, order_branch_id,order_tcs_utgst,
    order_tds,order_tcs,order_tcs_cgst,order_tcs_sgst,order_tcs_igst,order_total_utgst,order_delivery_charge_utgst,
		order_total_sgst AS sgst, order_total_cgst AS cgst,order_delivery_charge_igst,order_total_igst,order_delivery_charge_cgst,order_delivery_charge_sgst FROM retaline_customer_order rco 
		INNER JOIN finascop_stock_transfer_order fsto ON rco.order_id = fsto.fstr_id AND fsto.fsto_ordertype = 1 WHERE fsto_id = {$order_id}", true);

    $orderData['order_id'] = $order_id;
    $orderData['finascopEventRefId'] = $db->getItemFromDB("SELECT event_ref_id FROM finance_event_master WHERE name = 'Order Delivery'");

    if ($delVoucherItems['order_branch_id'] > 0) {
      $orderBranchId =  $delVoucherItems['order_branch_id'];
      $orderBranchsg = $db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$orderBranchId}");

      $orderData['storeGroupId'] = $orderBranchsg;
    } else {
      $orderData['storeGroupId'] = -1;
    }

    $finascopPostingServiceUrl = $db->getItemFromDB("SELECT cfg_Value from sys_configuration where cfg_Name = 'FINANCEAUTOPOSTING'");

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $finascopPostingServiceUrl,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $orderData,
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return true;
  }
  public static function DeliveryPickupVoucher($order_id)
  {
    $db = new sqlDb(DSN);

    $delVoucherItems = $db->getFromDB("SELECT fsto_id,order_delivery_charge,storegroup_id,order_courier_charge,order_delivery_charge_gst,
		order_order_id AS orders, order_total_amount AS amount_before_tax, order_roundoff, order_id, order_branch_id,order_tcs_utgst,
    order_tds,order_tcs,order_tcs_cgst,order_tcs_sgst,order_tcs_igst,order_total_utgst,order_delivery_charge_utgst,
		order_total_sgst AS sgst, order_total_cgst AS cgst,order_delivery_charge_igst,order_total_igst,order_delivery_charge_cgst,order_delivery_charge_sgst FROM retaline_customer_order rco 
		INNER JOIN finascop_stock_transfer_order fsto ON rco.order_id = fsto.fstr_id AND fsto.fsto_ordertype = 1 WHERE fsto_id = {$order_id}", true);


    $pickupsData = $db->getFromDB("SELECT RoundUp, RoundDown FROM finance_autoposting_values WHERE order_id={$delVoucherItems['order_id']}", true);

    file_put_contents('php://stderr', 'DeliveryPickupVoucher  -- ' . json_encode($pickupsData));
    file_put_contents('php://stderr', 'DeliveryPickupVoucher query -- ' . "SELECT RoundUp, RoundDown FROM finance_autoposting_values WHERE order_id={$delVoucherItems['order_id']}");

    $pickupsUpdateData['RoundUp_ForSettlement'] = $pickupsData["RoundUp"];
    $pickupsUpdateData['RoundDown_ForSettlement'] = $pickupsData["RoundDown"];
    $pickupsUpdateData = array_filter($pickupsUpdateData);
    if (count($pickupsUpdateData) != 0) {
      $status = $db->perform('finance_autoposting_values', $pickupsUpdateData, 'update', " order_id = {$delVoucherItems['order_id']} ");
    }


    $orderData['order_id'] = $delVoucherItems['order_id'];
    $orderData['finascopEventRefId'] = $db->getItemFromDB("SELECT event_ref_id FROM finance_event_master WHERE id =7");

    if ($delVoucherItems['order_branch_id'] > 0) {
      $orderBranchId =  $delVoucherItems['order_branch_id'];
      $orderBranchsg = $db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$orderBranchId}");

      $orderData['storegroup_id'] = $orderBranchsg;
    } else {
      $orderData['storegroup_id'] = -1;
    }

    $finascopPostingServiceUrl = $db->getItemFromDB("SELECT cfg_Value from sys_configuration where cfg_Name = 'FINANCEAUTOPOSTING'");

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $finascopPostingServiceUrl,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $orderData,
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return true;
  }
}
