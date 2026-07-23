<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of PayOnDelivery
 *
 * @author monsy
 */

class PayOnDelivery
{

  protected const PayOnDeliveryST = 1;
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

  public static function PODVoucher($order_id)
  {


    $db = new sqlDb(DSN);
    $log['entity_id'] = $order_id;
    $log['createdOn'] = date('Y-m-d H:i:s');
    $log['type'] = "PayOnDeliveryVoucher";
    $log['status'] = 1;
    $log['comments'] = "PayOnDeliveryVoucher";
    $db->perform("finascop_log", $log);

    $lastId = $db->insert_id();


    $order = $db->getFromDB('SELECT fsto_id,rco.total,order_delivery_charge,order_courier_charge, payment_mode,total_afterpacking,
                order_total_sgst AS sgst, order_total_cgst AS cgst,order_total_igst,order_roundoff, order_id, order_branch_id,total,storegroup_id,
                order_order_id AS orders, order_total_amount AS selling_price,order_wallet_amount,order_method
                FROM retaline_customer_order rco INNER JOIN finascop_stock_transfer_order fsto 
                ON rco.order_id = fsto.fstr_id AND fsto.fsto_ordertype = 1 WHERE fsto_id = ' . $order_id, true);

    $data['entry_type'] = 1;
    $data['Narration'] = 'Pay On Delivery Voucher ' . $order['orders'];
    $data['reference'] = "Pay On Delivery Voucher : " . $order['orders'];

    $data['entry_RefId'] = $db->getItemFromDB("SELECT entry_RefId FROM retaline_customer_order WHERE order_id = {$order['order_id']}"); 
            //sha1(microtime(true) . mt_rand(10000, 90000));
    $log['orderOrderId'] = $order['orders'];
    $data['order_order_id'] = $order['orders'];
    $data['order_event'] = "PayOnDeliveryVoucher";

    $data['Account'] = array();
    $data['Particulars'] = array();

    if ($order['order_branch_id'] > 0) {
      $orderBranchId =  $order['order_branch_id'];
      $orderBranchsg = $db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$orderBranchId}");
      $ledgerRefId = $db->getItemFromDB("SELECT storeRefId FROM finascop_branch_group 
      WHERE store_group_id = {$orderBranchsg}");
      $strgrpName = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group 
      WHERE store_group_id = {$orderBranchsg}");
      $br_Name_store_group = $db->getItemFromDB("SELECT br_Name FROM finascop_branch WHERE br_ID = {$orderBranchId}");
      $data['storeGroupId']  = $orderBranchsg;
      $data['storeGroupRefId'] = $ledgerRefId;
      $data['br_ID_store_group'] = $orderBranchId;
      $data['br_Name_store_group'] = $br_Name_store_group;
      $data['StoreGroupName'] = $strgrpName;
    } else {
      $data['storeGroupId'] = -1;
    }

    if ($order['payment_mode'] == static::CODWithWallet || $order['payment_mode'] == static::OnlineOnDelivery) {

      $data['TransactionTypeId'] = static::BankReceipt;
      $data['docTypeID'] = static::BankReceipt;



      $PaidByWalletAmount = round($order['order_wallet_amount'], 2);
      $customer_order_id = $order['order_id'];

      $tdr = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TDR'");
      $tdrCGST = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TDR_CGST'");

      $bankCharges = round($order['total'] * $tdr / 100, 2);
      $tdrCGSTval = round($bankCharges * $tdrCGST / 100, 2);

      if ($order['order_total_igst'] > 0) {
        $bankChargesIGST = $tdrCGSTval * 2;
        $bankChargesSGST = 0.00;
        $bankChargesCGST = 0.00;
      } else {
        $bankChargesIGST = 0.00;
        $bankChargesSGST = $tdrCGSTval;
        $bankChargesCGST = $tdrCGSTval;
      }


      $totBalToPay = round(round($order['total'], 2) - $PaidByWalletAmount, 2);
      $amtToBank = round($totBalToPay - ($bankCharges + round($bankChargesIGST, 2) + round($bankChargesSGST, 2) + round($bankChargesCGST, 2)), 2);

      $accIndex = 0;

      if ($amtToBank <> 0) {
        $data['Account'][$accIndex]['ledgerId'] = static::BANKGOCA;
        $data['Account'][$accIndex]['amount'] = $amtToBank;
        $data['Account'][$accIndex]['particulars'] = "Bank - Grozeo Online Collection Account";
        $data['Account'][$accIndex]['isDebtor'] = 1;
        $accIndex++;
      }

      if ($bankCharges <> 0) {
        $data['Account'][$accIndex]['ledgerId'] = static::BankChargesTDR;
        $data['Account'][$accIndex]['amount'] = $bankCharges;
        $data['Account'][$accIndex]['particulars'] = "Bank Charges TDR";
        $data['Account'][$accIndex]['isDebtor'] = 1;
        $accIndex++;
      }

      if ($bankChargesCGST <> 0) {
        $data['Account'][$accIndex]['ledgerId'] = static::CGSTInput;
        $data['Account'][$accIndex]['amount'] = round($bankChargesCGST, 2);
        $data['Account'][$accIndex]['particulars'] = "CGSTInput";
        $data['Account'][$accIndex]['isDebtor'] = 1;
        $accIndex++;
      }

      if ($bankChargesSGST <> 0) {
        $data['Account'][$accIndex]['ledgerId'] = static::SGSTInput;
        $data['Account'][$accIndex]['amount'] = round($bankChargesSGST, 2);
        $data['Account'][$accIndex]['particulars'] = "SGSTInput";
        $data['Account'][$accIndex]['isDebtor'] = 1;
        $accIndex++;
      }

      if ($bankChargesIGST <> 0) {
        $data['Account'][$accIndex]['ledgerId'] = static::IGSTInput;
        $data['Account'][$accIndex]['amount'] = round($bankChargesIGST, 2);
        $data['Account'][$accIndex]['particulars'] = "IGSTInput";
        $data['Account'][$accIndex]['isDebtor'] = 1;
        $accIndex++;
      }

      $parIndex = 0;
      if ($totBalToPay <> 0) {
        $data['Particulars'][$parIndex]['ledgerId'] = static::PODCollection_OCA;
        $data['Particulars'][$parIndex]['amount'] =  $totBalToPay;
        $data['Particulars'][$parIndex]['particulars'] = "POD Collection";
        $data['Particulars'][$parIndex]['isDebtor'] = 0;
        $parIndex++;
      }
    } elseif ($order['payment_mode'] == static::CashOnDelivery || $order['payment_mode'] == static::PayOnDeliveryST) {

      $data['TransactionTypeId']  = static::Journal;
      $data['docTypeID']          = static::Journal;

      $PaidByWalletAmount = round($order['order_wallet_amount'], 2);



      $totBalToPay = round(round($order['total'], 2) - $PaidByWalletAmount, 2);
      $courierOrGLP = round($totBalToPay, 2);
      if ($order['order_method'] == 3) {
        $data['Account'][0]['ledgerId'] = static::Courier_GLP;
        $data['Account'][0]['amount'] = $courierOrGLP;
        $data['Account'][0]['particulars'] = "Courier/GLP";
        $data['Account'][0]['isDebtor'] = 1;
      }


      $data['Particulars'][0]['ledgerId'] = static::PODCollection_OCA;
      $data['Particulars'][0]['amount'] =  $totBalToPay;
      $data['Particulars'][0]['particulars'] = "POD Collection";
      $data['Particulars'][0]['isDebtor'] = 0;
    }

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
      $log['status'] = 3;
      $log['comments'] = "Result:" . $ex;
      $db->perform("finascop_log", $log, 'update', " id = {$lastId}");
    }



    $status = json_decode($response, true);

    if ($status['statusId'] == 1) {
      $log['status'] = 1;
      $log['comments'] = " Pay On Delivery." . $order['orders'] . " Result:" . $response;
      $db->perform("finascop_log", $log, 'update', " id = {$lastId}");
      return true;
    } else {
      $log['status'] = 2;
      $log['comments'] = "Pay On Delivery." . ' Status: ' . $response . 'URL: ' . $url . ' headers: ' . implode(',', $headers);
      $db->perform("finascop_log", $log, 'update', " id = {$lastId}");
      return false;
    }
  }
  public static function PODCashCollectionVoucher($order_id)
  {
    $db = new sqlDb(DSN);
    $log['entity_id'] = $order_id;
    $log['createdOn'] = date('Y-m-d H:i:s');
    $log['type'] = "PODCashCollection";
    $log['status'] = 1;
    $log['comments'] = "POD-Cash Collection";
    $db->perform("finascop_log", $log);

    $lastId = $db->insert_id();


    $order = $db->getFromDB('SELECT fsto_id,rco.total,order_delivery_charge,order_courier_charge, payment_mode,total_afterpacking,
                order_total_sgst AS sgst, order_total_cgst AS cgst,order_total_igst,order_roundoff, order_id, order_branch_id,total,storegroup_id,
                order_order_id AS orders, order_total_amount AS selling_price,order_wallet_amount
                FROM retaline_customer_order rco INNER JOIN finascop_stock_transfer_order fsto 
                ON rco.order_id = fsto.fstr_id AND fsto.fsto_ordertype = 1 WHERE fsto_id = ' . $order_id, true);


    $data['entry_type'] = 1;
    $data['Narration'] = 'Pay On Delivery Cash Collection ' . $order['orders'];
    $data['reference'] = "Pay On Delivery Cash Collection : " . $order['orders'];

    $data['entry_RefId'] = $db->getItemFromDB("SELECT entry_RefId FROM retaline_customer_order WHERE order_id = {$order['order_id']}"); 
            //sha1(microtime(true) . mt_rand(10000, 90000));
    $log['orderOrderId'] = $order['orders'];
    $data['order_order_id'] = $order['orders'];
    $data['order_event'] = "PODCashCollection";

    $data['Account'] = array();
    $data['Particulars'] = array();

    $data['TransactionTypeId']  = static::Journal;
    $data['docTypeID']          = static::Journal;
    if ($order['order_branch_id'] > 0) {
      $orderBranchId =  $order['order_branch_id'];
      $orderBranchsg = $db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$orderBranchId}");
      $ledgerRefId = $db->getItemFromDB("SELECT storeRefId FROM finascop_branch_group 
      WHERE store_group_id = {$orderBranchsg}");
      $strgrpName = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group 
      WHERE store_group_id = {$orderBranchsg}");
      $br_Name_store_group = $db->getItemFromDB("SELECT br_Name FROM finascop_branch WHERE br_ID = {$orderBranchId}");
      $data['storeGroupId']  = $orderBranchsg;
      $data['storeGroupRefId'] = $ledgerRefId;
      $data['br_ID_store_group'] = $orderBranchId;
      $data['br_Name_store_group'] = $br_Name_store_group;
      $data['StoreGroupName'] = $strgrpName;
    } else {
      $data['storeGroupId'] = -1;
    }

    $orderAmount = round($order['total'], 2);

    $data['Account'][0]['ledgerId'] = static::Courier_GLP;
    $data['Account'][0]['amount'] = $orderAmount;
    $data['Account'][0]['particulars'] = "Courier / GLP";
    $data['Account'][0]['isDebtor'] = 1;

    $data['Particulars'][0]['ledgerId'] = static::PODCollection_OCA;
    $data['Particulars'][0]['amount'] =  $orderAmount;
    $data['Particulars'][0]['particulars'] = "POD Collection";
    $data['Particulars'][0]['isDebtor'] = 0;

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
      $log['status'] = 3;
      $log['comments'] = "Pay On Delivery Cash Collection :" . $ex;
      $db->perform("finascop_log", $log, 'update', " id = {$lastId}");
    }



    $status = json_decode($response, true);

    if ($status['statusId'] == 1) {
      $log['status'] = 1;
      $log['comments'] = " Pay On Delivery Cash Collection." . $order['orders'] . " Result:" . $response;
      $db->perform("finascop_log", $log, 'update', " id = {$lastId}");
      return true;
    } else {
      $log['status'] = 2;
      $log['comments'] = "Pay On Delivery Cash Collection." . ' Status: ' . $response . 'URL: ' . $url . ' headers: ' . implode(',', $headers);
      $db->perform("finascop_log", $log, 'update', " id = {$lastId}");
      return false;
    }
  }

  public static function PODCashSettlementVoucher($order_id)
  {
    $db = new sqlDb(DSN);
    $log['entity_id'] = $order_id;
    $log['createdOn'] = date('Y-m-d H:i:s');
    $log['type'] = "PODCashSettlement";
    $log['status'] = 1;
    $log['comments'] = "POD-Cash Settlement";
    $db->perform("finascop_log", $log);

    $lastId = $db->insert_id();


    $order = $db->getFromDB('SELECT fsto_id,rco.total,order_delivery_charge,order_courier_charge, payment_mode,total_afterpacking,
                order_total_sgst AS sgst, order_total_cgst AS cgst,order_total_igst,order_roundoff, order_id, order_branch_id,total,storegroup_id,
                order_order_id AS orders, order_total_amount AS selling_price,order_wallet_amount
                FROM retaline_customer_order rco INNER JOIN finascop_stock_transfer_order fsto 
                ON rco.order_id = fsto.fstr_id AND fsto.fsto_ordertype = 1 WHERE fsto_id = ' . $order_id, true);

    $data['entry_type'] = 1;
    $data['Narration'] = 'Pay On Delivery Cash Settlement ' . $order['orders'];
    $data['reference'] = "Pay On Delivery Cash Settlement : " . $order['orders'];

    $data['entry_RefId'] = $db->getItemFromDB("SELECT entry_RefId FROM retaline_customer_order WHERE order_id = {$order['order_id']}"); 
            //sha1(microtime(true) . mt_rand(10000, 90000));
    $log['orderOrderId'] = $order['orders'];
    $data['order_order_id'] = $order['orders'];
    $data['order_event'] = "PODCashSettlement";

    $data['Account'] = array();
    $data['Particulars'] = array();

    $data['TransactionTypeId']  = static::Journal;
    $data['docTypeID']          = static::Journal;
    if ($order['order_branch_id'] > 0) {
      $orderBranchId =  $order['order_branch_id'];
      $orderBranchsg = $db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$orderBranchId}");
      $ledgerRefId = $db->getItemFromDB("SELECT storeRefId FROM finascop_branch_group 
        WHERE store_group_id = {$orderBranchsg}");
      $strgrpName = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group 
        WHERE store_group_id = {$orderBranchsg}");
      $br_Name_store_group = $db->getItemFromDB("SELECT br_Name FROM finascop_branch WHERE br_ID = {$orderBranchId}");
      $data['storeGroupId']  = $orderBranchsg;
      $data['storeGroupRefId'] = $ledgerRefId;
      $data['br_ID_store_group'] = $orderBranchId;
      $data['br_Name_store_group'] = $br_Name_store_group;
      $data['StoreGroupName'] = $strgrpName;
    } else {
      $data['storeGroupId'] = -1;
    }

    $orderAmount = round($order['total'], 2);

    $data['Account'][0]['ledgerId'] = static::RefId;
    $data['Account'][0]['ledgerRefId'] = $ledgerRefId;
    $data['Account'][0]['amount'] = $orderAmount;
    $data['Account'][0]['particulars'] = "Tenant";
    $data['Account'][0]['isDebtor'] = 1;

    $data['Particulars'][0]['ledgerId'] = static::PODCollection_OCA;
    $data['Particulars'][0]['amount'] =  $orderAmount;
    $data['Particulars'][0]['particulars'] = "POD Collection";
    $data['Particulars'][0]['isDebtor'] = 0;

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
      $log['status'] = 3;
      $log['comments'] = "Pay On Delivery Cash Collection :" . $ex;
      $db->perform("finascop_log", $log, 'update', " id = {$lastId}");
    }



    $status = json_decode($response, true);

    if ($status['statusId'] == 1) {
      $log['status'] = 1;
      $log['comments'] = " Pay On Delivery Cash Collection." . $order['orders'] . " Result:" . $response;
      $db->perform("finascop_log", $log, 'update', " id = {$lastId}");
      return true;
    } else {
      $log['status'] = 2;
      $log['comments'] = "Pay On Delivery Cash Collection." . ' Status: ' . $response . 'URL: ' . $url . ' headers: ' . implode(',', $headers);
      $db->perform("finascop_log", $log, 'update', " id = {$lastId}");
      return false;
    }
  }
}
