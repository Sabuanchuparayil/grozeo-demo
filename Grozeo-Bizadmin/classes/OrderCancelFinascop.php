<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of OrderCancelFinascop
 *
 * @author monsy
 */

class OrderCancelFinascop
{
  protected  const CashReceipt = 1;
  protected  const CashPayment = 2;
  protected const BankReceipt = 3;
  protected const BankPayment = 4;
  protected const Journal = 5;
  protected const Contra = 6;
  protected const Sale = 7;
  protected const Purchase = 8;
  protected const DebitNote = 9;
  protected const CreditNote = 10;

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

  public static function OrderCancellationVoucher($order_id)
  {
    $db = new sqlDb(DSN);

    $order = $db->getFromDB('SELECT order_id AS fsto_id,order_delivery_charge,order_courier_charge,order_branch_id,
          order_total_sgst AS sgst, order_total_cgst AS cgst,storegroup_id,
          order_order_id AS orders, order_total_amount AS selling_price, total, order_wallet_amount
          FROM retaline_customer_order rco WHERE order_id =  '. $order_id,true);

    $orderData['order_id'] = $order_id;
    $orderData['finascopEventRefId'] = $db->getItemFromDB("SELECT event_ref_id FROM finance_event_master WHERE id = 4");

    if ($order['order_branch_id'] > 0) {
      $orderBranchId =  $order['order_branch_id'];
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
  public static function OrderCancellationVoucherOld($order_id)
  {
    $db = new sqlDb(DSN);

    $log['entity_id'] = $order_id;
    $log['createdOn'] = date('Y-m-d H:i:s');
    $log['type'] = "OrderCancellationVoucher";
    $log['status'] = 1;
    $log['comments'] = "OrderCancellationVoucher : " . $order_id;
    $db->perform("finascop_log", $log);

    $order = $db->getFromDB('SELECT order_id AS fsto_id,order_delivery_charge,order_courier_charge,order_branch_id,
          order_total_sgst AS sgst, order_total_cgst AS cgst,storegroup_id,
          order_order_id AS orders, order_total_amount AS selling_price, total, order_wallet_amount
          FROM retaline_customer_order rco WHERE order_id =  ' . $order_id, true);

    $data['TransactionTypeId'] = static::BankPayment;
    $data['docTypeID'] = static::BankPayment;
    $data['entry_type'] = 1;
    $data['Narration'] = 'Cancellation of Sales Order:' . $order['orders'];
    $data['reference'] = "Cancellation of Sales Order: " . $order['orders'];

    $data['entry_RefId'] = $db->getItemFromDB("SELECT entry_RefId FROM retaline_customer_order WHERE order_id = {$order_id}");
    //sha1(microtime(true) . mt_rand(10000, 90000));
    $uplog['orderOrderId'] = $order['orders'];
    $data['order_order_id'] = $order['orders'];
    $data['order_event'] = "OrderCancellationVoucher";
    if ($order['order_branch_id'] > 0) {
      $orderBranchId =  $order['order_branch_id'];
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


    $bankCharges = 0.00;
    //$bankGOCA = round(round($order[0]->selling_price,2) -($bankCharges + round($order[0]->cgst,2) + round($order[0]->sgst,2)),2);
    $bankGOCA = round($order['total'], 2);

    $data['Account'][0]['ledgerId'] = static::TenantSalesOrder;
    $data['Account'][0]['amount'] =  round($order['total'], 2);
    $data['Account'][0]['particulars'] = "Tenant Sales Order";
    $data['Account'][0]['isDebtor'] = 1;

    $data['Particulars'][0]['ledgerId'] = static::BANKGOCA;
    $data['Particulars'][0]['amount'] = $bankGOCA;
    $data['Particulars'][0]['particulars'] = "Bank Grozeo Online Collection Account";
    $data['Particulars'][0]['isDebtor'] = 0;


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

    $status = json_decode($response, true);

    $log['entity_id'] = $order_id;
    $log['createdOn'] = date('Y-m-d H:i:s');
    $log['type'] = "OrderCancellationVoucher";
    $log['status'] = 1;
    $log['comments'] = "Result:" . $response;
    $db->perform("finascop_log", $log);

    if ($status['statusId'] == 1) {
      return true;
    } else {
      return false;
    }
  }
}
