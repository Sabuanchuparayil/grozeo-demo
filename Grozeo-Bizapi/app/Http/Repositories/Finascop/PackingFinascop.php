<?php

namespace App\Http\Repositories\Finascop;

use Illuminate\Support\Facades\DB;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessWithData;

use App\Http\Repositories\Finascop\StoreFinascop;

class PackingFinascop
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
  protected const OrderSalesMargin = 58;
  

  public static function PackingVoucher($order_id)
  {

    $orderidCount = DB::select('SELECT count(entity_id) as count
          FROM finascop_log WHERE type = "PackingOrder" AND entity_id =  ' . $order_id);
    if ($orderidCount[0]->count > 0) {
      return;
    } else {
      $logId = DB::table('finascop_log')->insertGetId(
        [
          'entity_id' => $order_id,
          'createdOn' => date('Y-m-d H:i:s'),
          'type' => 'PackingOrder',
          'status' => 0
        ]
      );
    }
    $order = DB::select('SELECT fsto_id,rco.total,order_delivery_charge,order_courier_charge,total_afterpacking,payment_mode,order_delivery_charge_gst,
    order_delivery_charge_cgst,order_delivery_charge_sgst,order_delivery_charge_igst,order_delivery_charge_utgst,
                order_total_igst,order_total_sgst AS sgst, order_total_cgst AS cgst,order_roundoff, order_id, order_branch_id,
                order_order_id AS orders, order_total_amount AS selling_price,storegroup_id,order_total_utgst 
                FROM retaline_customer_order rco INNER JOIN finascop_stock_transfer_order fsto 
                ON rco.order_id = fsto.fstr_id AND fsto.fsto_ordertype = 1 WHERE fsto_id =  ' . $order_id);

    $customer_order_id = $order[0]->order_id;


    $cusState = DB::select("SELECT order_state FROM retaline_customer_order_delivery_address WHERE customer_order_id = {$customer_order_id}");
    $branchState =  DB::select("SELECT st_name FROM finascop_branch INNER JOIN finascop_state ON br_State = st_ID WHERE br_Id = {$order[0]->order_branch_id}");
    $tenantDeliveryCharge = round($order[0]->order_delivery_charge, 2) + round($order[0]->order_courier_charge, 2) - round($order[0]->order_delivery_charge_gst, 2);
    $tenantDeliveryCharge = round($tenantDeliveryCharge, 2);


    $intraState = (strcasecmp($cusState[0]->order_state, $branchState[0]->st_name) == 0) ? true : false;

      $tenantIGST = round($order[0]->order_delivery_charge_igst+$order[0]->order_total_igst,2);
      $tenantCGST = round($order[0]->cgst + $order[0]->order_delivery_charge_cgst, 2);
      $tenantSGST = round($order[0]->sgst + $order[0]->order_delivery_charge_sgst, 2);
      $tenantUTGST = round($order[0]->order_total_utgst + $order[0]->order_delivery_charge_utgst, 2);
    $order_delivery_charge_gst =  round($order[0]->order_delivery_charge_gst, 2);

    $orderidExCount = DB::select('SELECT rcep_amt_payable FROM customer_order_extra_payment_log WHERE rcep_order_id =  ' . $customer_order_id);
    $orderidWallCount = DB::select("SELECT brcw_Amount FROM retaline_customer_wallet_transaction WHERE refentry_id =  {$customer_order_id} order by brcw_id desc");


    $data['TransactionTypeId'] = static::Journal;
    $data['docTypeID'] = static::Journal;
    $data['Narration'] = 'Sales Order Packing ' . $order[0]->orders;
    $data['reference'] = 'Sales Order Packing ' . $order[0]->orders ;
    $data['entry_type'] = 1;
    $data['entry_RefId'] = StoreFinascop::getSalesOrder_entryRefId($customer_order_id)->entry_RefId; 
    //sha1(microtime(true) . mt_rand(10000, 90000));
   
    $data['order_order_id'] = $order[0]->orders;
    $data['order_event'] = "PackingOrder";
    if($order[0]->order_branch_id >= 0){        
      $orderBranchId =  $order[0]->order_branch_id;
      $br_storeGroup = DB::select("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$orderBranchId}");
      $orderBranchsg = $br_storeGroup[0]->br_storeGroup;
      $storeRefId = DB::select("SELECT storeRefId FROM finascop_branch_group WHERE store_group_id = {$orderBranchsg}");          
      $ledgerRefId = $storeRefId[0]->storeRefId;
      $strgrpNameDet = DB::select("SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = {$orderBranchsg}");
      $strgrpName = $strgrpNameDet[0]->store_group_name;
      $br_Name = DB::select("SELECT br_Name FROM finascop_branch WHERE br_ID = {$orderBranchId}");
      $br_Name_store_group = $br_Name[0]->br_Name;
    $data['storeGroupId'] = $br_storeGroup[0]->br_storeGroup;
    $data['storeGroupRefId'] = $ledgerRefId;
    $data['br_ID_store_group'] = $orderBranchId;

    $data['br_Name_store_group'] = $br_Name_store_group;
    $data['StoreGroupName'] = $strgrpName;
    
  } else{
    $data['storeGroupId'] = -1;
  }
    $data['Account'] = array();
    $data['Particulars'] = array();

    $accIndex = 0;
    $roundOff = round($order[0]->order_roundoff, 2);
    if (($order[0]->total_afterpacking > 0) && (isset($orderidExCount[0]->rcep_amt_payable) > 0 || isset($orderidWallCount[0]->brcw_Amount) > 0)) {
      $orderTotal = $order[0]->total;
      $walletAmt = ($order[0]->total - $order[0]->total_afterpacking);
      $TenantSales = $order[0]->total_afterpacking - ($order[0]->cgst + $order[0]->sgst + $order[0]->order_total_utgst +$order[0]->order_total_igst + $order[0]->order_delivery_charge_gst + $tenantDeliveryCharge + $roundOff);
      $TenantSales = round($TenantSales, 2);
    } else {
      $orderTotal = $order[0]->total;
      $TenantSales = $order[0]->selling_price;
    }
    $data['Account'][$accIndex]['ledgerId'] = static::TenantSalesOrder;
    $data['Account'][$accIndex]['amount'] = $orderTotal;
    $data['Account'][$accIndex]['particulars'] = "Tenant Sales Order";
    $data['Account'][$accIndex]['isDebtor'] = 1;



    $tenantCustomerWalletAmt = 0.00;




    $parIndex = 0;

    $data['Particulars'][$parIndex]['ledgerId'] = static::TenantSales;
    $data['Particulars'][$parIndex]['amount'] = $TenantSales;
    $data['Particulars'][$parIndex]['particulars'] = "Tenant Sales";
    $data['Particulars'][$parIndex]['isDebtor'] = 0;
	$data['Particulars'][$parIndex]['CostCentreEntries'] = array();
	$data['Particulars'][$parIndex]['CostCentreEntries'] = self::MarginDistribuions($customer_order_id,0);

    $parIndex++;
    $data['Particulars'][$parIndex]['ledgerId'] = static::TenantDelivery;
    $data['Particulars'][$parIndex]['amount'] = $tenantDeliveryCharge;
    $data['Particulars'][$parIndex]['particulars'] = "Tenant Delivery";
    $data['Particulars'][$parIndex]['isDebtor'] = 0;

    if (round($tenantCGST, 2) <> 0) {
      $parIndex++;
      $data['Particulars'][$parIndex]['ledgerId'] = static::TenantCGST;
      $data['Particulars'][$parIndex]['amount'] = $tenantCGST;
      $data['Particulars'][$parIndex]['particulars'] = "Tenant CGST";
      $data['Particulars'][$parIndex]['isDebtor'] = 0;
    }

    if (round($tenantSGST, 2) <> 0) {
      $parIndex++;
      $data['Particulars'][$parIndex]['ledgerId'] = static::TenantSGST;
      $data['Particulars'][$parIndex]['amount'] = $tenantSGST;
      $data['Particulars'][$parIndex]['particulars'] = "Tenant SGST";
      $data['Particulars'][$parIndex]['isDebtor'] = 0;
    }

    if (round($tenantUTGST, 2) <> 0) {
      $parIndex++;
      $data['Particulars'][$parIndex]['ledgerId'] = static::TenantUTGST;
      $data['Particulars'][$parIndex]['amount'] = $tenantUTGST;
      $data['Particulars'][$parIndex]['particulars'] = "Tenant UTGST";
      $data['Particulars'][$parIndex]['isDebtor'] = 0;
    }
    if (round($tenantIGST, 2) <> 0) {
      $parIndex++;
      $data['Particulars'][$parIndex]['ledgerId'] = static::TenantIGST;
      $data['Particulars'][$parIndex]['amount'] = $tenantIGST;
      $data['Particulars'][$parIndex]['particulars'] = "Tenant IGST";
      $data['Particulars'][$parIndex]['isDebtor'] = 0;
    }

    /*if(round($tenantCustomerWalletAmt,2) <> 0){
        $parIndex++;      
        $data['Particulars'][$parIndex]['ledgerId'] = static::CustomerWallet;
        $data['Particulars'][$parIndex]['amount'] = round($tenantCustomerWalletAmt,2);
        $data['Particulars'][$parIndex]['particulars'] = "Tenant Customer Wallet";
        $data['Particulars'][$parIndex]['isDebtor'] = 0;            
      }*/

    if (($order[0]->total_afterpacking > 0) && isset($orderidExCount[0]->rcep_amt_payable) > 0) {
      $parIndex++;
      $data['Particulars'][$parIndex]['ledgerId'] = static::CustomerWallet;
      $data['Particulars'][$parIndex]['amount'] = round($orderidExCount[0]->rcep_amt_payable, 2);
      $data['Particulars'][$parIndex]['particulars'] = "Tenant Customer Wallet";
      $data['Particulars'][$parIndex]['isDebtor'] = 1;
    }

    if (($order[0]->total_afterpacking > 0) && isset($orderidWallCount[0]->brcw_Amount) > 0) {
      $parIndex++;
      $data['Particulars'][$parIndex]['ledgerId'] = static::CustomerWallet;
      $data['Particulars'][$parIndex]['amount'] = round($orderidWallCount[0]->brcw_Amount, 2);
      $data['Particulars'][$parIndex]['particulars'] = "Tenant Customer Wallet";
      $data['Particulars'][$parIndex]['isDebtor'] = 0;
    }

    if ($roundOff > 0) {
      //$data['Account'][0]['amount'] = round($order[0]->total - $roundOff,2);

      $parIndex++;
      $data['Particulars'][$parIndex]['ledgerId'] = static::TenantSalesOrderRoundOff;
      $data['Particulars'][$parIndex]['amount'] = round(abs($roundOff), 2);
      $data['Particulars'][$parIndex]['particulars'] = "Tenant Sales Round Off";
      $data['Particulars'][$parIndex]['isDebtor'] = 0;
    }
    if ($roundOff < 0) {
      //$data['Particulars'][0]['amount'] = round($order[0]->selling_price + $roundOff,2);

      $accIndex++;
      $data['Account'][$accIndex]['ledgerId'] = static::TenantSalesOrderRoundOff;
      $data['Account'][$accIndex]['amount'] = round(abs($roundOff), 2);
      $data['Account'][$accIndex]['particulars'] = "Tenant Sales Round Off";
      $data['Account'][$accIndex]['isDebtor'] = 1;
    }

	
    try {
      $cURLConnection = curl_init();
      $headers = [
        "x-functions-key:" . config('services.finascop.fkey'),
        'Content-Type:application/json'
      ];

      $url = config('services.finascop.dataentry') . "/api/FinascopDataEntry";
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
      DB::table('finascop_log')->where('id', $logId)->update(array('status' => 3, 'orderOrderId' => $order[0]->orders,'comments' => 'PackingOrder: ' . $order[0]->orders . "Exception : " . $ex));
    }


    $chdata = json_decode($response, true);

    if (isset($chdata['statusId']) && $chdata['statusId'] == 1) {
      DB::table('finascop_log')->where('id', $logId)->update(array('status' => 1,'orderOrderId' => $order[0]->orders, 'comments' => 'PackingOrder: ' . $order[0]->orders));
      return new SuccessWithData(
        array("data" => $chdata)
      );
    } else {
      DB::table('finascop_log')->where('id', $logId)->update(array('status' => 2,'orderOrderId' => $order[0]->orders, 'comments' => "Data : " . json_encode($data) . ' SalesOrder: ' . $order[0]->orders . ' Status: ' . $response . 'URL: ' . $url . ' headers: ' . implode(',', $headers)));
      return new ErrorResponse($response);
    }
  }
  
  public static function MarginDistribuionVoucher($order_id)
  {
    $orderidCount = DB::select('SELECT count(entity_id) as count
          FROM finascop_log WHERE type = "MarginDistribuions" AND entity_id =  ' . $order_id);
    if ($orderidCount[0]->count > 0) {
      return;
    } else {
      $logId = DB::table('finascop_log')->insertGetId(
        [
          'entity_id' => $order_id,
          'createdOn' => date('Y-m-d H:i:s'),
          'type' => 'MarginDistribuions',
          'status' => 0
        ]
      );
    }
	$order = DB::select('SELECT fsto_id,rco.total,order_delivery_charge,order_courier_charge,total_afterpacking,payment_mode,order_delivery_charge_gst,
	order_delivery_charge_cgst,order_delivery_charge_sgst,order_delivery_charge_igst,order_delivery_charge_utgst,
	order_total_igst,order_total_sgst AS sgst, order_total_cgst AS cgst,order_roundoff, order_id, order_branch_id,
	order_order_id AS orders, order_total_amount AS selling_price,storegroup_id,order_total_utgst , order_sales_margin
	FROM retaline_customer_order rco INNER JOIN finascop_stock_transfer_order fsto 
	ON rco.order_id = fsto.fstr_id AND fsto.fsto_ordertype = 1 WHERE fsto_id =  ' . $order_id);

        if($order[0]->order_sales_margin > 0){
          $customer_order_id = $order[0]->order_id;

              $data['TransactionTypeId'] = static::Journal;
          $data['docTypeID'] = static::Journal;
          $data['Narration'] = 'Margin Distribuions of ' . $order[0]->orders;
          $data['reference'] = 'Margin Distribuions of ' . $order[0]->orders ;
          $data['entry_type'] = 1;
          $data['entry_RefId'] = StoreFinascop::getSalesOrder_entryRefId($customer_order_id)->entry_RefId; 
          // sha1(microtime(true) . mt_rand(10000, 90000));
          $data['order_order_id'] = $order[0]->orders;
          $data['order_event'] = "MarginDistribuions";  

              if($order[0]->order_branch_id >= 0){        
                      $orderBranchId =  $order[0]->order_branch_id;
                      $br_storeGroup = DB::select("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$orderBranchId}");
                      $orderBranchsg = $br_storeGroup[0]->br_storeGroup;
                      $storeRefId = DB::select("SELECT storeRefId FROM finascop_branch_group WHERE store_group_id = {$orderBranchsg}");          
                      $ledgerRefId = $storeRefId[0]->storeRefId;
                      $strgrpNameDet = DB::select("SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = {$orderBranchsg}");
                      $strgrpName = $strgrpNameDet[0]->store_group_name;
                      $br_Name = DB::select("SELECT br_Name FROM finascop_branch WHERE br_ID = {$orderBranchId}");
                      $br_Name_store_group = $br_Name[0]->br_Name;
                      $data['storeGroupId'] = $br_storeGroup[0]->br_storeGroup;
                      $data['storeGroupRefId'] = $ledgerRefId;
                      $data['br_ID_store_group'] = $orderBranchId;

                      $data['br_Name_store_group'] = $br_Name_store_group;
                      $data['StoreGroupName'] = $strgrpName;

                } else{
                      $data['storeGroupId'] = -1;
                }

          $data['Account'] = array();
          $data['Particulars'] = array();

          $accIndex = 0;

          $data['Account'][$accIndex]['ledgerId'] = 0;
          $data['Account'][$accIndex]['ledgerRefId'] = $data['storeGroupRefId'];
          $data['Account'][$accIndex]['amount'] = $order[0]->order_sales_margin;
          $data['Account'][$accIndex]['particulars'] = "Tenant Sales Order";
          $data['Account'][$accIndex]['isDebtor'] = 1;

          $parIndex = 0;
          $costCentreEntries = self::MarginDistribuions($customer_order_id,0);
            $ledgerId = array_column($costCentreEntries, 'ledgerId');

          $data['Particulars'][$parIndex]['ledgerId'] = $ledgerId[0];
          $data['Particulars'][$parIndex]['amount'] = $order[0]->order_sales_margin;
          $data['Particulars'][$parIndex]['particulars'] = "Test Cost";
          $data['Particulars'][$parIndex]['isDebtor'] = 0;
          $data['Particulars'][$parIndex]['CostCentreEntries'] = array();
          $data['Particulars'][$parIndex]['CostCentreEntries'] = $costCentreEntries;

          info(json_encode($data));

            try {
            $cURLConnection = curl_init();
            $headers = [
              "x-functions-key:" . config('services.finascop.fkey'),
              'Content-Type:application/json'
            ];

            $url = config('services.finascop.dataentry') . "/api/FinascopDataEntry";
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
            DB::table('finascop_log')->where('id', $logId)->update(array('status' => 3, 'orderOrderId' => $order[0]->orders,'comments' => 'PackingOrder: ' . $order[0]->orders . "Exception : " . $ex));
          }
        }
  }
  
    private static function MarginDistribuions($customer_order_id,$isDebtor)
  {
      
      $dtAllocations = DB::select('SELECT DISTINCT ocda.id,
        COALESCE((SELECT costcentre_id FROM cost_distribution WHERE id = ocda.distribution_id),-1) AS costCentreId, ledgerid AS ledgerId,
        allocation_amount AS amount, ' . $isDebtor.' AS isDebtor 
        FROM order_cost_distribution_allocations ocda INNER JOIN cost_distribution cd ON ocda.rule_id = cd.rule_id 
        INNER JOIN cost_distribution_rule cdr ON cd.rule_id = cdr.id 
        WHERE ocda.order_id = ' . $customer_order_id);

      return json_decode(json_encode($dtAllocations),true);
  }
}
