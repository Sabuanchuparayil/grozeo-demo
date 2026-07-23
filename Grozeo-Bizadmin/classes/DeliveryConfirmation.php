<?php

/**
 * Description of DeliveryConfirmation
 *
 * @author monsy
 */
class DeliveryConfirmation
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

  public static function DeliveryConfirmationVoucher($order_id)
  {
    file_put_contents('php://stderr', 'DeliveryConfirmationVoucherDeliveryConfirmationVoucherDeliveryConfirmationVoucherDeliveryConfirmationVoucherDeliveryConfirmationVoucherDeliveryConfirmationVoucher  -- ' . $order_id);
    $db = new sqlDb(DSN);

    $delVoucherItems = $db->getFromDB("SELECT fsto_id,order_delivery_charge,storegroup_id,order_courier_charge,order_delivery_charge_gst,
		order_order_id AS orders, order_total_amount AS amount_before_tax, order_roundoff, order_id, order_branch_id,order_tcs_utgst,
    order_tds,order_tcs,order_tcs_cgst,order_tcs_sgst,order_tcs_igst,order_total_utgst,order_delivery_charge_utgst,order_method,payment_mode,
		order_total_sgst AS sgst, order_total_cgst AS cgst,order_delivery_charge_igst,order_total_igst,order_delivery_charge_cgst,order_delivery_charge_sgst,
    quor_AmountCollectible,quor_Paymode,quor_DeliveryDriverId 
    FROM retaline_customer_order rco 
		INNER JOIN finascop_stock_transfer_order fsto ON rco.order_id = fsto.fstr_id  AND fsto.fsto_ordertype = 1 
    INNER JOIN qugeo_order ON quor_TransferOrder_id = fsto_id AND quor_TransferOrder_Type = 1 WHERE fsto_id = {$order_id}", true);
    switch (OPERATING_COUNTRY) {
      case 'INDIA':
        file_put_contents('php://stderr', 'confirmDataconfirmDataconfirmData  -- ' . json_encode($delVoucherItems));
        file_put_contents('php://stderr', 'confirmData query -- ' . "SELECT MerchantDiscountRate_MDR, IGSTInputonMDR, CGSTInputonMDR, SGSTInputonMDR, UTGSTInputonMDR, CCInputonMDR, OrderGrandTotal_POD FROM finance_autoposting_values WHERE order_id={$delVoucherItems['order_id']}");
        $confirmData = $db->getFromDB("SELECT MerchantDiscountRate_MDR, IGSTInputonMDR, CGSTInputonMDR, SGSTInputonMDR, UTGSTInputonMDR, CCInputonMDR, OrderGrandTotal_POD FROM finance_autoposting_values WHERE order_id={$delVoucherItems['order_id']}", true);

        file_put_contents('php://stderr', 'confirmDataconfirmDataconfirmData  -- ' . json_encode($confirmData));


        /*$confUpdateData['MerchantDiscountRate_MDR_DigitalPayment'] = $confirmData["MerchantDiscountRate_MDR"];
    $confUpdateData['IGSTInputon_MDR_DigitalPayment'] = $confirmData["IGSTInputonMDR"];
    $confUpdateData['CGSTInputon_MDR_DigitalPayment'] = $confirmData["CGSTInputonMDR"];
    $confUpdateData['SGSTInputon_MDR_DigitalPayment'] = $confirmData["SGSTInputonMDR"];
    $confUpdateData['UTGSTInputon_MDR_DigitalPayment'] = $confirmData["UTGSTInputonMDR"];
    $confUpdateData['CCInputon_MDR_DigitalPayment'] = $confirmData["CCInputonMDR"];
    $confUpdateData['TSOPOD_DigitalPayment'] = $confirmData["OrderGrandTotal_POD"];
    $confUpdateData['TSOPOD_CashPayment'] = $confirmData["OrderGrandTotal_POD"];*/

        if ($delVoucherItems['quor_AmountCollectible'] > 0) {
          switch ($delVoucherItems['order_method']) {
            case 1:
              if ($delVoucherItems['quor_DeliveryDriverId'] > 0) {
                $driverDetails = $db->getFromDB("SELECT createdBy,sourceId FROM qugeo_driver WHERE d_ID = {$delVoucherItems['quor_DeliveryDriverId']}", true);
                if (@$driverDetails['createdBy'] == 1) {
                  $confUpdateData['TSOPOD_CashPayment'] = $delVoucherItems['quor_AmountCollectible'];
                  $confUpdateData['TenantCollection_COD'] = $delVoucherItems['quor_AmountCollectible'];
                  $confUpdateData['CourierCollection_COD'] = 0;
                  $confUpdateData['GrozeoLogisticsPartnerCollection_COD'] = 0;
                } else {
                  $confUpdateData['TSOPOD_CashPayment'] = $delVoucherItems['quor_AmountCollectible'];
                  $confUpdateData['TenantCollection_COD'] = 0;
                  $confUpdateData['CourierCollection_COD'] = 0;
                  $confUpdateData['GrozeoLogisticsPartnerCollection_COD'] = $delVoucherItems['quor_AmountCollectible'];
                }
              }
              break;
            case 3:
              $confUpdateData['TSOPOD_CashPayment'] = $delVoucherItems['quor_AmountCollectible'];
              $confUpdateData['TenantCollection_COD'] = 0;
              $confUpdateData['CourierCollection_COD'] = $delVoucherItems['quor_AmountCollectible'];
              $confUpdateData['GrozeoLogisticsPartnerCollection_COD'] = 0;
              break;
          }
        }
        break;
      case 'AE':
      case 'UK':
        file_put_contents('php://stderr', 'confirmDataconfirmDataconfirmData  -- ' . json_encode($delVoucherItems));
        file_put_contents('php://stderr', 'confirmData query -- ' . "SELECT DeliveryAgent_PODCashinHand,TSOPOD_CashPayment,CourierCollection_COD,GrozeoLogisticsPartnerCollection_COD,TenantCollection_COD,POD_CashSettledbyDA,POD_CashSettledbyLSP FROM finance_autoposting_values WHERE order_id={$delVoucherItems['order_id']}");
        $confirmData = $db->getFromDB("SELECT DeliveryAgent_PODCashinHand,TSOPOD_CashPayment,CourierCollection_COD,GrozeoLogisticsPartnerCollection_COD,TenantCollection_COD,POD_CashSettledbyDA,POD_CashSettledbyLSP FROM finance_autoposting_values WHERE order_id={$delVoucherItems['order_id']}", true);

        file_put_contents('php://stderr', 'confirmDataconfirmDataconfirmData  -- ' . json_encode($confirmData));
        if ($delVoucherItems['quor_AmountCollectible'] > 0) {
          /*switch ($delVoucherItems['order_method']) {
            case 1:
              if ($delVoucherItems['quor_DeliveryDriverId'] > 0) {
                $driverDetails = $db->getFromDB("SELECT createdBy,sourceId FROM qugeo_driver WHERE d_ID = {$delVoucherItems['quor_DeliveryDriverId']}", true);
                if (@$driverDetails['createdBy'] == 1) {
                  $confUpdateData['DeliveryAgent_PODCashinHand'] = $delVoucherItems['quor_AmountCollectible'];
                  $confUpdateData['TSOPOD_CashPayment'] = $delVoucherItems['quor_AmountCollectible'];
                  $confUpdateData['TenantCollection_COD'] = $delVoucherItems['quor_AmountCollectible'];
                  $confUpdateData['CourierCollection_COD'] = 0;
                  $confUpdateData['GrozeoLogisticsPartnerCollection_COD'] = 0;
                  $confUpdateData['POD_CashSettledbyDA'] = $delVoucherItems['quor_AmountCollectible'];
                } else {
                  $confUpdateData['DeliveryAgent_PODCashinHand'] = $delVoucherItems['quor_AmountCollectible'];
                  $confUpdateData['TSOPOD_CashPayment'] = $delVoucherItems['quor_AmountCollectible'];
                  $confUpdateData['TenantCollection_COD'] = 0;
                  $confUpdateData['CourierCollection_COD'] = 0;
                  $confUpdateData['GrozeoLogisticsPartnerCollection_COD'] = $delVoucherItems['quor_AmountCollectible'];
                  $confUpdateData['POD_CashSettledbyDA'] = $delVoucherItems['quor_AmountCollectible'];
                }
              }
              break;
            case 3:
              $confUpdateData['TSOPOD_CashPayment'] = $delVoucherItems['quor_AmountCollectible'];
              $confUpdateData['TenantCollection_COD'] = 0;
              $confUpdateData['CourierCollection_COD'] = $delVoucherItems['quor_AmountCollectible'];
              $confUpdateData['GrozeoLogisticsPartnerCollection_COD'] = 0;
              $confUpdateData['POD_CashSettledbyLSP'] = $delVoucherItems['quor_AmountCollectible'];

              break;
          }*/
        }


        break;
    }


    $confUpdateData = array_filter($confUpdateData);
    if (count($confUpdateData) != 0) {
      $status = $db->perform('finance_autoposting_values', $confUpdateData, 'update', " order_id = {$delVoucherItems['order_id']} ");
    }

    $orderData['order_id'] = $delVoucherItems['order_id'];
    $orderData['finascopEventRefId'] = $db->getItemFromDB("SELECT event_ref_id FROM finance_event_master WHERE id =6");

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
  public static function DeliveryEmail($order_id)
  {
    $db = new sqlDb(DSN);

    $delVoucherItems = $db->getFromDB("SELECT order_order_id,order_customer_id,order_branch_id FROM retaline_customer_order rco 
		INNER JOIN finascop_stock_transfer_order fsto ON rco.order_id = fsto.fstr_id AND fsto.fsto_ordertype = 1 WHERE fsto_id = {$order_id}", true);

    $customerDetails = $db->getFromDB("SELECT cust_mobile,cust_email,cust_customer_name FROM retaline_customer WHERE cust_id = {$delVoucherItems['order_customer_id']}", true);

    $orderData['fullname'] = $customerDetails['cust_customer_name'];
    $orderData['email'] = $customerDetails['cust_email'];
    $orderData['storename'] = $db->getItemFromDB("SELECT br_Name FROM finascop_branch WHERE br_ID = {$delVoucherItems['order_branch_id']}");
    $orderData['ordernum'] = $delVoucherItems['order_order_id'];

    $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'DELIVERYEMAIL'");
    $fields_string = json_encode($orderData);

    $opts = array(
      CURLOPT_URL => $url,
      CURLINFO_CONTENT_TYPE => "application/json",
      CURLOPT_BINARYTRANSFER => TRUE,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_POST => count($orderData),
      CURLOPT_POSTFIELDS => $fields_string,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json')
    );

    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return true;
  }
  public static function DeliveryConfirmationVoucherOld($order_id)
  {
    $db = new sqlDb(DSN);
    $log['entity_id'] = $order_id;
    $log['createdOn'] = date('Y-m-d H:i:s');
    $log['type'] = "DeliveryConfirmationVoucher";
    $log['status'] = 1;
    $log['comments'] = "DeliveryConfirmationVoucher";
    $db->perform("finascop_log", $log);

    $lastId = $db->insert_id();


    $delVoucherItems = $db->getFromDB("SELECT fsto_id,fstr_id,order_delivery_charge,storegroup_id,order_courier_charge,order_delivery_charge_gst,
    order_delivery_charge_cgst,order_delivery_charge_sgst,order_delivery_charge_igst,order_delivery_charge_utgst,
		order_order_id AS orders, order_total_amount AS amount_before_tax, order_roundoff, order_id, order_branch_id,total,payment_mode, order_wallet_amount,
		order_total_sgst AS sgst, order_total_cgst AS cgst,order_tdr,order_tdr_cgst,order_tdr_sgst,order_tdr_igst FROM retaline_customer_order rco 
		INNER JOIN finascop_stock_transfer_order fsto ON rco.order_id = fsto.fstr_id AND fsto.fsto_ordertype = 1 WHERE fsto_id = {$order_id}", true);
    $tenantExpense = $db->getFromDB("SELECT delivery_income,diIGST,diCGST,diSGST,diUTGST,pgIGST,pgCGST,pgSGST,pgUTGST,tenantExpense 
FROM tenant_income_expense WHERE orderId = {$delVoucherItems['fstr_id']}", true);


    $tenantDeliveryIncome = $tenantExpense['delivery_income'];
    $tdrOnlinePayment = $delVoucherItems['order_tdr'];

    $pgchgsIGST = $tenantExpense['pgIGST'];
    $pgchgsCGST = $tenantExpense['pgCGST'];
    $pgchgsSGST = $tenantExpense['pgSGST'];


    $tenant = $tenantExpense['tenantExpense'];

    //$tenant = round($tenantDeliveryIncome + $tdrOnlinePayment + $pgchgsIGST + $pgchgsCGST + $pgchgsSGST, 2);



    $data['TransactionTypeId'] = static::Journal;
    $data['docTypeID'] = static::Journal;
    $data['entry_type'] = 1;
    $data['Narration'] = "Delivery of Sales Order : " . $delVoucherItems['orders'] . '. On Confirmation.';
    $data['reference'] = "Delivery Confirmation : " . $delVoucherItems['orders'];

    $data['entry_RefId'] = $db->getItemFromDB("SELECT entry_RefId FROM retaline_customer_order WHERE order_id = {$delVoucherItems['order_id']}");
    //sha1(microtime(true) . mt_rand(10000, 90000));
    $uplog['orderOrderId'] = $delVoucherItems['orders'];
    $data['order_order_id'] = $delVoucherItems['orders'];
    $data['order_event'] = "DeliveryConfirmation";

    if ($delVoucherItems['order_branch_id'] > 0) {
      $orderBranchId =  $delVoucherItems['order_branch_id'];
      $orderBranchsg = $db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$orderBranchId}");
      $ledgerRefId = $db->getItemFromDB("SELECT storeRefId FROM finascop_branch_group WHERE store_group_id = {$orderBranchsg}");
      $strgrpName = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = {$orderBranchsg}");
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
    $data['Account'][$accIndex]['ledgerId'] = static::RefId;
    $data['Account'][$accIndex]['ledgerRefId'] = $ledgerRefId;
    $data['Account'][$accIndex]['amount'] = $tenant;
    $data['Account'][$accIndex]['particulars'] = $strgrpName;
    $data['Account'][$accIndex]['isDebtor'] = 1;




    $parIndex = 0;
    if (round($tdrOnlinePayment, 2) <> 0) {
      $data['Particulars'][$parIndex]['ledgerId'] = static::TDROnlineTransactions;
      $data['Particulars'][$parIndex]['amount'] = $tdrOnlinePayment;
      $data['Particulars'][$parIndex]['particulars'] = "TDR Online Transactions";
      $data['Particulars'][$parIndex]['isDebtor'] = 0;
      $parIndex++;
    }

    if (round($tenantDeliveryIncome, 2) <> 0) {
      $data['Particulars'][$parIndex]['ledgerId'] = static::DeliveryChargesIncome;
      $data['Particulars'][$parIndex]['amount'] = $tenantDeliveryIncome;
      $data['Particulars'][$parIndex]['particulars'] = "Delivery Income";
      $data['Particulars'][$parIndex]['isDebtor'] = 0;
      $parIndex++;
    }

    if ($pgchgsIGST > 0) {
      $data['Particulars'][$parIndex]['ledgerId'] = static::IGST;
      $data['Particulars'][$parIndex]['amount'] = $pgchgsIGST;
      $data['Particulars'][$parIndex]['particulars'] = "IGST";
      $data['Particulars'][$parIndex]['isDebtor'] = 0;
      $parIndex++;
    }

    if ($pgchgsSGST > 0) {
      $data['Particulars'][$parIndex]['ledgerId'] = static::SGST;
      $data['Particulars'][$parIndex]['amount'] = $pgchgsSGST;
      $data['Particulars'][$parIndex]['particulars'] = "SGST";
      $data['Particulars'][$parIndex]['isDebtor'] = 0;
      $parIndex++;
    }

    if ($pgchgsCGST >  0) {
      $data['Particulars'][$parIndex]['ledgerId'] = static::CGST;
      $data['Particulars'][$parIndex]['amount'] = $pgchgsCGST;
      $data['Particulars'][$parIndex]['particulars'] = "CGST";
      $data['Particulars'][$parIndex]['isDebtor'] = 0;
      $parIndex++;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////

    $log['entity_id'] = $order_id;
    $log['createdOn'] = date('Y-m-d H:i:s');
    $log['type'] = "Order Account Statement";
    $log['status'] = 0;
    $log['comments'] = "Order Account Statement: " . $delVoucherItems['orders'] . " Expences.";
    $db->perform("finascop_log", $log);

    $logLastId = $db->insert_id();

    try {
      $cBQuery = "SELECT closingBalance FROM order_account_statement  WHERE storeId = {$orderBranchId} ORDER BY id DESC LIMIT 0,1";

      $closingBalance = $db->getItemFromDB($cBQuery);

      if (empty($closingBalance)) {
        $closingBalance =  round(0.00, 2);
      } else {
        $closingBalance =  round($closingBalance, 2);
      }


      $oasEntry = array();
      $expenses = $tdrOnlinePayment + $tenantDeliveryIncome + $pgchgsIGST + $pgchgsSGST + $pgchgsCGST;
      $oasEntry['orderId'] = $order_id;
      $oasEntry['orderOrderId'] = $delVoucherItems['orders'];
      $oasEntry['storeGroupId'] = $orderBranchsg;
      $oasEntry['storeId'] = $orderBranchId;
      $oasEntry['particulars'] = "Expenses";
      $oasEntry['isDebtor'] = 0;
      $oasEntry['amount'] = round($expenses, 2);
      $oasEntry['openingBalance'] = $closingBalance;

      $db->perform("order_account_statement", $oasEntry);


      $uplog['comments'] = "Success:Order Account Statement : " . $delVoucherItems['orders'] . " Expenses.";
      $uplog['status'] = 1;
      $db->perform("finascop_log", $uplog, 'update', " id = {$logLastId}");
    } catch (Exception $exAccStmt) {
      $uplog['comments'] = "Failed Exception thrown: Order Account Statement : " . $delVoucherItems['orders'] . "Expenses." . $exAccStmt;
      $uplog['status'] = 2;
      $db->perform("finascop_log", $uplog, 'update', " id = {$logLastId}");
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
      $uplog['comments'] = "Delivery Confirmation Voucher: " . $delVoucherItems['orders'] . "Exception : " . $ex . 'Data: ' . json_encode($data);
      $db->perform("finascop_log", $uplog, 'update', " id = {$lastId}");
    }
    $status = json_decode($response, true);




    if ($status['statusId'] == 1) {
      $log['status'] = 1;
      $log['comments'] = $log['comments'] . " SO:" .  $delVoucherItems['orders'] . ",Result:" . $response . 'Data: ' . json_encode($data);
      $db->perform("finascop_log", $log, 'update', " id = {$lastId}");
      return true;
    } else {
      $log['status'] = 2;
      $log['comments'] = "Delivery Confirmation Voucher of SalesOrder :" . $delVoucherItems['orders'] . ' Status: ' . $response . 'URL: ' . $url . ' headers: ' . implode(',', $headers) . 'Data: ' . json_encode($data);
      $db->perform("finascop_log", $log, 'update', " id = {$lastId}");
      return false;
    }
  }
}
