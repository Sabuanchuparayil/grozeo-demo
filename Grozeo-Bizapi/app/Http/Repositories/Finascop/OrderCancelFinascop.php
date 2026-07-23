<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace App\Http\Repositories\Finascop;

use App\Http\Repositories\Finascop\StoreFinascop;

use Illuminate\Support\Facades\DB;


class OrderCancelFinascop {
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
        
        
    public static function OrderCancellationVoucher($order_id ){

      $orderidCount = DB::select('SELECT count(entity_id) as count
          FROM finascop_log WHERE type = "SalesOrderCancellation" AND entity_id =  '. $order_id) ;
      if($orderidCount[0]->count > 0){
        return;
      }
      else{
        $logId = DB::table('finascop_log')->insertGetId(
                [
                    'entity_id' => $order_id,
                    'createdOn' => date('Y-m-d H:i:s'),
                    'type' => 'SalesOrderCancellation',
                    'status' => 0
                ]
        );
      }          
      $order = DB::select('SELECT order_id AS fsto_id,order_delivery_charge,order_courier_charge,order_branch_id,
      order_total_sgst AS sgst, order_total_cgst AS cgst,storegroup_id,
      order_order_id AS orders, order_total_amount AS selling_price , total, order_wallet_amount
      FROM retaline_customer_order rco WHERE order_id =  '. $order_id);
      
      $data['TransactionTypeId'] = static::Journal;
      $data['docTypeID'] = static::Journal;
      $data['Narration'] = 'Cancelltaion of Sales Order:' . $order[0]->orders ;
      $data['reference'] = 'Cancelltaion of Sales Order:' . $order[0]->orders ;
      $data['entry_type'] = 1;
      $data['entry_RefId'] =  StoreFinascop::getSalesOrder_entryRefId($order_id)->entry_RefId;
      //getSalesOrder_entryRefId($order_id)//sha1(microtime(true) . mt_rand(10000, 90000));
      $data['order_order_id'] = $order[0]->orders;
      $data['order_event'] = "SalesOrderCancellation";
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
    
      $data['storeGroupId'] = $orderBranchsg;  
      $data['storeGroupRefId'] = $ledgerRefId;
      $data['br_ID_store_group'] = $orderBranchId;

      $data['br_Name_store_group'] = $br_Name_store_group;
        $data['StoreGroupName'] = $strgrpName;
     
    } else{
      $data['storeGroupId'] = -1;
    }

      $data['Account'] = array();
      $data['Particulars'] = array();
      
      
      $bankCharges = 0.00;      
      //$bankGOCA = round(round($order[0]->selling_price,2) -($bankCharges + round($order[0]->cgst,2) + round($order[0]->sgst,2)),2);
      $returnAmt = round($order[0]->total,2);

      $data['Account'][0]['ledgerId'] = static::TenantSalesOrder;
      $data['Account'][0]['amount'] =  round($order[0]->total,2);
      $data['Account'][0]['particulars'] = "Tenant Sales Order";
      $data['Account'][0]['isDebtor'] = 1;
      
      $data['Particulars'][0]['ledgerId'] = static::CustomerWallet;
      $data['Particulars'][0]['amount'] = $returnAmt;
      $data['Particulars'][0]['particulars'] = "Customer Wallet";
      $data['Particulars'][0]['isDebtor'] = 0;
///////////////////////////////////////////////////////////////////////////////////////////////

         $accStmtID = DB::table('finascop_log')->insertGetId(
         [
             'entity_id' => $order_id,
             'createdOn' => date('Y-m-d H:i:s'),
             'type' => 'SalesOrderCancellation Statement',
             'status' => 0,
             'comments'=> 'SalesOrderCancellation Statement : ' . $order[0]->orders
         ]);
   
          
          try{
			  
        
            $cBQuery = "SELECT closingBalance FROM order_account_statement  WHERE storeId = {$order[0]->order_branch_id} ORDER BY id DESC LIMIT 0,1";

            $result = DB::select($cBQuery); 
 
			
			if(empty($result)){
				$closingBalance =  round(0.00,2);;
			}
			else{
				$closingBalance =  round($result[0]->closingBalance,2);
			}
              
              DB::table('order_account_statement')->insert([
                 'orderId' => $order[0]->fsto_id,
                 'orderOrderId' => $order[0]->orders,
                 'storeGroupId' => $order[0]->storegroup_id,
                 'storeId' => $order[0]->order_branch_id,
                 'particulars' => "Sale Cancellation and Refund",
                 'isDebtor' => 0,
                 'amount' => round($order[0]->total,2),
                 'openingBalance' => $closingBalance
             ]);
              
             DB::table('finascop_log')->where('id',$accStmtID)->update(array('status'=>1, 'orderOrderId' => $order[0]->orders,'comments'=>'SalesOrderCancellation: '. $order[0]->orders));
             } 
        catch (Exception $exAccStmt) {
              DB::table('finascop_log')->where('id',$accStmtID)->update(array('status'=>2,'orderOrderId' => $order[0]->orders, 'comments'=>'SalesOrderCancellation: '. $order[0]->orders . '. Exception : ' . $exAccStmt . "Data : " . json_encode($data)));
        } 
      
///////////////////////////////////////////////////////////////////////////////////////////////      
        $cURLConnection = curl_init();
        $headers = [
            "x-functions-key:".config('services.finascop.fkey'),
            'Content-Type:application/json'
        ];

        $url = config('services.finascop.dataentry')."/api/FinascopDataEntry";
        curl_setopt($cURLConnection, CURLOPT_URL, $url);
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cURLConnection, CURLOPT_ENCODING , '');
        curl_setopt($cURLConnection, CURLOPT_MAXREDIRS , 10);
        curl_setopt($cURLConnection, CURLOPT_TIMEOUT ,  0);
        curl_setopt($cURLConnection, CURLOPT_FOLLOWLOCATION , true);
        curl_setopt($cURLConnection, CURLOPT_HTTP_VERSION ,CURL_HTTP_VERSION_1_1);
        curl_setopt($cURLConnection, CURLOPT_POST, 1);
        curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($cURLConnection, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS | CURLPROTO_HTTP);
        

      $response = curl_exec($cURLConnection);
      curl_close($cURLConnection);

      $status = json_decode($response,true);

        if ($status['statusId'] == 1) {
          DB::table('finascop_log')->where('id',$logId)->update(array('status'=>3,'orderOrderId' => $order[0]->orders, 'comments'=>'SalesOrderCancellation: '. $order[0]->orders .' Status: '. $response. 'URL: ' . $url .' headers: '. implode(',', $headers)));
           return true;
        } else {
                    DB::table('finascop_log')->where('id',$logId)->update(array('status'=>2,'orderOrderId' => $order[0]->orders, 'comments'=>'SalesOrderCancellation: '. $order[0]->orders .' Status: '. $response. 'URL: ' . $url .' headers: '. implode(',', $headers)));
           return false;
        }
        }
}
