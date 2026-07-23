<?php
namespace BackOffice\Tasks\PostingScheduler;

use App\Helpers\HttpCurlCalls;
use Illuminate\Support\Facades\DB;
use App\Models\Order;

use App\Http\Responses\{
    SuccessWithData,
    SuccessResponse,
    ErrorResponse,
    ErrorWithData
};

final class EntryTypes{
    const Ledger = 0;
    const Tenant = 1;
    const DynamicCostCentre = 2;
}

final class CostCentre{
    const RESERVE = 17;
    const OrderReferralMerchant_ORM = 15;
    const CustomerSourceMerchant_CSM = 16;
}

final class DyanmicCostCentreType{
    const OrderReferalMerchant = 1;
    const CustomerSourceMerchant = 2;
    const BusinessAssociate = 3;
	const AreaAssociate = 4;
	const BusinessPartner = 5;
}

class FinascopPosting
{
    public function __construct() {}

    // Executed by scheduler for each event added in NoSql for autoposting.
    public function finascopPosting($order_id, $finascopEventRefId, $storeGroupId)
    {
        try
        {
            $event = DB::table('finance_event_master')->select('id','name')->where('event_ref_id', $finascopEventRefId)->first();

            if ($event) {
                  $query = "SELECT id, rulename FROM finance_autoposting_rule WHERE event_master_id = " . $event->id;
                $ruels = DB::select($query);

                foreach($ruels as $rule)
                {
                    $this->finascopPostingRun($order_id, $event->id, $event->name, $rule->rulename,$rule->id, $storeGroupId);
                }
            }else{
                info(' refID : ' . $finascopEventRefId . ' could not retrive an event from database.');

                // Write log entry and exit.
                $logRecID = DB::table('finascop_log')->insertGetId([
                    'entity_id' => $order_id,'createdOn' => date('Y-m-d H:i:s'),'type' => $event->name,'status' => 0,
                    'comments' => 'With refID : ' . $finascopEventRefId . ' could not retrive an event record from finance_event_master table.'
                ]);

            }
        }
        catch (\Exception $evEx)
        {
            info($evEx->getMessage());
            $logEvent = DB::table('finascop_log')->insertGetId([
                'entity_id' => $order_id,'createdOn' => date('Y-m-d H:i:s'),'type' => $event->name,'status' => 0,
                'comments' => 'Retriving data with refID : ' . $finascopEventRefId . ' from finance_event_master table threw an exception.'
            ]);
        }
     }

     //Run through details of a rule to prepare voucher entry and cost allocations and then call finascop api to execute sql server data_entry
    private function finascopPostingRun($order_id, $eventID, $finascopEvent, $ruleName,$ruleID, $storeGroupId = -1)
    {
        $voucher = array();
        try
        {
            $order = DB::table('retaline_customer_order')
            ->select('order_order_id','order_branch_id','entry_RefId')
            ->where('order_id', $order_id)
            ->first();


            $order_number = $order->order_order_id;


            $logRecID = DB::table('finascop_log')->insertGetId([
                'entity_id' => $order_id,'createdOn' => date('Y-m-d H:i:s'),'type' => $finascopEvent,'status' => 0,
                'comments' => $finascopEvent . ' rule : ' . $ruleName . ' on Order number : ' . $order_number . '.  Applying rule_id : ' . $ruleID
            ]);

            $ruleQry = "SELECT far.id AS rule_id,fem.id AS event_id,fem.name AS `event`, fat.id AS `voucher_type_id`, " .
            "fat.name AS `voucher_type`, vouchernarration as narration_template FROM `finance_autoposting_rule` far " .
            "INNER JOIN `finance_event_master` fem ON far.event_master_id = fem.id " .
            "INNER JOIN `finance_voucher_type` fat ON far.voucher_id = fat.id " .
            "WHERE fem.id = " . $eventID ."  AND far.id = '".$ruleID."'";


            $rule = DB::select($ruleQry);

            if (count($rule) === 0)
            {
                DB::table('finascop_log')->where('id', $logRecID)->update(['status' => 3]);
                return new ErrorResponse('Rule not found');
            }

            $ruleDetailsQry = "SELECT fas.ledger_id,fas.type_id,fas.isDebtor,fch.column_name,fas.value_head_id " .
            "FROM `finance_autoposting_settings` fas  INNER JOIN `finance_calculation_heads` fch " .
            "ON fas.value_head_id = fch.id WHERE fas.autoposting_rule_id = " . $rule[0]->rule_id . " AND cost_centre_id = -1 ORDER BY fas.isDebtor ASC";
            $ruleDetails = DB::select($ruleDetailsQry);

            //$strBranchId = DB::table('retaline_customer_order')->where('order_id', $order_id)->value('order_branch_id');
            $strBranchId = $order->order_branch_id;

            $storeDetails = $this->getStoreDetails($strBranchId);

            //$entry_RefId = DB::table('retaline_customer_order')->where('order_id', $order_id)->value('entry_RefId');
            $entry_RefId = $order->entry_RefId;


            $voucher = [
                'StoreGroupName'      => $storeDetails->store_group_name,
                'storeGroupId'        => (int) $storeGroupId,
                'storeGroupRefId'     => $storeDetails->storeRefId,
                'br_Name_store_group' => $storeDetails->br_Name,
                'br_ID_store_group'   => (int) $strBranchId,
                'TransactionTypeId'   => (int) $rule[0]->voucher_type_id,
                'docTypeID'           => (int) $rule[0]->voucher_type_id,
                'narration'           => $finascopEvent . " of Sales Order : " . $order_number,
                'reference'           => $finascopEvent . " of Sales Order : " . $order_number,
                'entry_RefId'         => $entry_RefId,
                'entry_type'          => 1,
                'order_order_id'      => (string) $order_number,
                'order_event'         => $finascopEvent . "_" . $ruleID,
                'Account'             => array(),
                'Particulars'         => array()
            ];

            $voucherAmount = 0;
            if(count($ruleDetails) > 0)
            {
                foreach ($ruleDetails as $rd)
                {
                    try{
                        $amount = DB::table('finance_autoposting_values')->where('order_id', $order_id)->value($rd->column_name);
                    }
                    catch (\Exception $cnEx)
                    {
						info($cnEx->getMessage());
                        $logCNF = DB::table('finascop_log')->insertGetId([
                            'entity_id' => $order_id,'createdOn' => date('Y-m-d H:i:s'),'type' => $finascopEvent,'status' => 0,
                            'comments' => $rd->column_name . ' Column not found in finance_autoposting_values table.  Rule : ' . $ruleName . ' on Order number : ' . $order_number . '.  Applying rule_id : ' . $ruleID
                        ]);
                    }

                    if (is_null($amount) || $amount == 0)
                    {
                        continue;
                    }

                    $costCentreDetails = $this->getCostCentreAllocation($rd->ledger_id,$rule[0]->rule_id,$amount,$rd->isDebtor,$order_id,$eventID,$storeGroupId,$rd->value_head_id);

                    $storeRefID = null;
                    try
                    {
                        if($rd->type_id == EntryTypes::Tenant)
                        {
                            $storeRefID = DB::table('finascop_branch_group')->where('store_group_id', $storeGroupId)->value('storeRefId');
                        }

                    }
                    catch (\Exception $ex)
                    {
                        info($ex->getMessage());
                    }
                    if ($rd->isDebtor == 1)
                    {
                        $voucher['Account'][] = [
                            'isDebtor'    => 1,
                            'ledgerId'    => $rd->ledger_id,
                            'ledgerRefId' => $storeRefID,
                            'particulars' => "",
                            'amount'      => $amount,
                            'CostCentreEntries' => $costCentreDetails
                        ];
                        $voucherAmount += $amount;
                    }
                    if ($rd->isDebtor == 0)
                    {
                        $voucher['Particulars'][] = [
                            'isDebtor'    => 0,
                            'ledgerId'    => $rd->ledger_id,
                            'ledgerRefId' => $storeRefID,
                            'particulars' => "",
                            'amount'      => $amount,
                            'CostCentreEntries' => $costCentreDetails
                        ];
                        $voucherAmount += $amount;
                    }
                }
            }
            if( $voucherAmount == 0)
            {
                return;
            }

            $voucher['narration'] = $this->buildNarration($order_id,$rule[0]->narration_template);
        }
        catch (\Exception $e)
        {
            info($e->getMessage());
			return new ErrorWithData("Error : " .$e->getMessage(), $voucher, 406);
        }

        try
        {

            $headers = [
                "x-functions-key:" . config('services.finascop.fkey'),
                'Content-Type:application/json'
            ];

             $url = config('services.finascop.dataentry') . "/api/FinascopDataEntry";

			$response = (new HttpCurlCalls)->curlCall($url, json_encode($voucher), 'POST', $headers);

        }
        catch (Exception $ex)
        {
            DB::table('finascop_log')->where('id', $logRecID)->update(
            ['status' => 3,'comments' => $finascopEvent . ' rule : ' . $ruleName . '.  Applying rule_id : ' . $ruleID . ' on Order number : ' . $order_number . ", Exception : "  . $ex . 'Data: ' . json_encode($voucher)
            ]);
            return new ErrorWithData('Exception : ' . $ex->getMessage(), $voucher, 406);
        }
        $status = $response;

        if ($status->statusId == 1)
        {
            DB::table('finascop_log')->where('id', $logRecID)->update(
            ['status' => 1,'comments' => $finascopEvent . ' rule : ' . $ruleName . '.  Applying rule_id : ' . $ruleID . ' on Order number : ' . $order_number . " Result:" . json_encode($response). 'Data: ' . json_encode($voucher)
            ]);
            DB::table('finance_autoposting_log')->insert([['order_id' => $order_id , 'finance_event_master_id' => $eventID]]);
            return new SuccessWithData($response);
        }
        else if ($status->statusId == 4)
        {
            DB::table('finascop_log')->where('id', $logRecID)->update(
            ['status' => 3,'comments' =>  ' Exception Thrown : ' .$finascopEvent . ' rule : ' . $ruleName . '.  Applying rule_id : ' . $ruleID . ' on Order number : ' . $order_number . ' Status: ' . json_encode($response) . 'URL: ' . $url . ' headers: ' . implode(',', $headers) . 'Data: ' . json_encode($voucher)
            ]);
            return new ErrorWithData("FinascopDataEntry returned with status 4 - Exception.", $voucher, 406);
        }
        else
        {
            DB::table('finascop_log')->where('id', $logRecID)->update(
            ['status' => 2,'comments' => $finascopEvent . ' rule : ' . $ruleName . '.  Applying rule_id : ' . $ruleID . ' on Order number : ' . $order_number . ' Status: ' . json_encode($response) . 'URL: ' . $url . ' headers: ' . implode(',', $headers) . 'Data: ' . json_encode($voucher)
            ]);
            return new ErrorWithData("FinascopDataEntry returned with status 2 - Failure", $voucher, 406);
        }
    }

    private function buildNarration($orderId,$narrationTemplate){
        $placeHolder = $this->getNextPlaceHolder($narrationTemplate);
        $orderDetails = ($placeHolder != null) ? $this->getOrderDetails($orderId) : null;
        $paymentGatewayDetails = ($orderDetails != null && $orderDetails->order_payment_gateway != '') ? $this->getPaymentGatewayDetails($orderDetails->order_payment_gateway,$orderId) : null;
        while($placeHolder != null){
            $value = $this->getValueforPlaceHolder($placeHolder,$orderDetails,$paymentGatewayDetails,$orderId);
            $narrationTemplate = str_replace( $placeHolder,$value,$narrationTemplate);
            $placeHolder = $this->getNextPlaceHolder($narrationTemplate);
        }

        return $narrationTemplate;
    }

    private function getPaymentGatewayDetails($paymentGateway,$orderId){
        $paymentgatewayclass = config("paymentgateway.". $paymentGateway . ".class");
        $paymentGatewayobj = new $paymentgatewayclass();
        return $paymentGatewayobj->getNarrationDetails($orderId);
    }

    private function getOrderDetails($orderId) {
        return DB::table('retaline_customer_order as rco')
            ->select(
                'rco.order_nettotal',
                'rc.cust_customer_name',
                'rco.order_order_id',
                'fbg.store_group_name',
                'rco.order_branch_id',
                'fb.br_Name',
                'rco.order_confirm_date',
                'rco.order_confirmed_on',
                'rco.payment_mode',
                'order_payment_gateway',
                DB::raw('DATE(rco.order_cancel_date) AS cancellation_date'),
                'rco.storegroup_id',
                'rco.status_id'
            )
            ->leftJoin('retaline_customer as rc', 'rco.order_customer_id', '=', 'rc.cust_id')
            ->leftJoin('finascop_branch_group as fbg', 'rco.storegroup_id', '=', 'fbg.store_group_id')
            ->leftJoin('finascop_branch as fb', 'rco.order_branch_id', '=', 'fb.br_ID')
            ->where('rco.order_id', $orderId)
            ->first();
    }

    private function getNextPlaceHolder(&$narrationTemplate){
        $start = strpos($narrationTemplate, '{#');
        $end = strpos($narrationTemplate, '#}');

        if ($start !== false && $end !== false) {
            return substr($narrationTemplate, $start, $end - $start + 2);
        }
    }


    private function getValueforPlaceHolder($placeHolder,&$orderDetails,&$paymentGateWayDetails,$order_id)
    {
        $value = "";
        if($orderDetails != null){
            switch($placeHolder){
                case '{#Net_Amount_Payable#}':
                    return $orderDetails->order_nettotal;
                break;
                case '{#Customer_Name#}':
                    return $orderDetails->cust_customer_name;
                break;
                case '{#Sale_Order_Number#}':
                    return $orderDetails->order_order_id;
                break;
                case '{#Sale_Order_Date#}':
                    return $orderDetails->order_confirm_date;
                break;
                case '{#Store_Name#}':
                    return $orderDetails->br_Name;
                break;
                case '{#Date_of_Cancellation#}':
                    return $orderDetails->cancellation_date;
                break;
                case '{#Customer_Wallet#}':
                    return DB::table('finascop_wallet_transaction')->where('refentry_id', $order_id)->value('cust_id');
                break;
                case '{#Customer_Bank_Account#}':
                    return "NOT IMPLEMENTED";
                break;
                case '{#Sale_Order_Cancellation_Number#}':
                    return DB::table('retaline_customer_order_cancellationdets')->where('order_id', $order_id)->value('id');
                break;
                case '{#Tenant_Invoice_Number#}':
                    return DB::table('retaline_customer_order')->where('order_id', $order_id)->value('order_invoiceno');
                break;
                case '{#Tenant_Invoice_Date#}':
                    return DB::table('retaline_customer_order')->where('order_id', $order_id)->value('order_confirm_date');
                break;
                case '{#Grozeo_Invoice_Number#}':
                    return DB::table('invoice_number')->where('order_id', $order_id)->where('invoice_type', 3)->value('inv_number');
                break;
                case '{#Grozeo_Invoice_Date#}':
                    return DB::table('invoice_number')->where('order_id', $order_id)->where('invoice_type', 3)->value('created_at');
                break;
                case '{#Grozeo_Invoice_for_Restaurant_Service_Number#}':
                    return DB::table('invoice_number')->where('order_id', $order_id)->where('invoice_type', 1)->value('inv_number');
                break;
                case '{#Grozeo_Invoice_for_Restaurant_Service_Date#}':
                    return DB::table('invoice_number')->where('order_id', $order_id)->where('invoice_type', 1)->value('created_at');
                break;
                case '{#Delivery_Partner_Name#}':
                    return 'NOT IMPLEMENTED';
                break;
                default:
            $value = "";
            }
        }

        if($paymentGateWayDetails != null){
            switch($placeHolder){
                case '{#Customer_Card#}':
                    return $paymentGateWayDetails['payment_type']['type'];
                break;
                case '{#Mode_Of_Payment#}':
                    return $paymentGateWayDetails['payment_type']['mode'];
                break;
                case '{#Bank_Reference_ID.#}':
                    return $paymentGateWayDetails['reference'];
                break;
                default:
                $value = "";
            }
        }
        return $value;
    }

    // retrive cost centre allocations on a margin if any
    private function getCostCentreAllocation($ledgerID, $ruleID,$amount,$isDebtor,$order_id,$eventID,$storeGroupId,$valueHeadID)
    {
        $costCentreDetails = array();
        $hasCostCentre = DB::table('finance_autoposting_settings')
        ->where('ledger_id', $ledgerID)
        ->where('autoposting_rule_id',$ruleID)
        ->where('cost_centre_id', '<>', -1)
        ->value('id');


        if (!is_null($hasCostCentre) && $hasCostCentre > 0)
        {
            $costDetailsQry = "SELECT fas.cost_centre_id,fch.column_name, fas.type_id ".
            " FROM `finance_autoposting_settings` fas  INNER JOIN `finance_calculation_heads` fch " .
            " ON fas.value_head_id = fch.id WHERE fas.autoposting_rule_id = " . $ruleID . " AND cost_centre_id <> -1";


            $costCentreRule = $this->getCostDistributionRule($order_id, $eventID, $storeGroupId,$valueHeadID);



            $costDetails = DB::select($costDetailsQry);


            foreach ($costDetails as $cd){
                $cdamount = DB::table('finance_autoposting_values')->where('order_id', $order_id)->value($cd->column_name);
                $costCentreRefId = "";
                if (is_null($cdamount) || $cdamount == 0)
                {
                    continue;
                }

                if($cd->type_id == EntryTypes::DynamicCostCentre){

                    if($cd->cost_centre_id == DyanmicCostCentreType::OrderReferalMerchant){
                        $costCentreRefId = $this->getOrderReferralMerchantRefId($order_id);
                        $cost_centre_id = -1;
                    }elseif($cd->cost_centre_id == DyanmicCostCentreType::CustomerSourceMerchant){
                        $costCentreRefId = $this->getCustomerSourceMerchantRefId($order_id);
                        $cost_centre_id = -1;
                    }elseif($cd->cost_centre_id == DyanmicCostCentreType::BusinessAssociate){
                        $costCentreRefId = $this->getBusinessAssociateRefId($order_id);
                        $cost_centre_id = -1;
                    }elseif($cd->cost_centre_id == DyanmicCostCentreType::AreaAssociate){
                        $costCentreRefId = $this->getAreaAssociateRefId($order_id);
                        $cost_centre_id = -1;
                    }elseif($cd->cost_centre_id == DyanmicCostCentreType::BusinessPartner){
                        $costCentreRefId = $this->getBusinessPartnerRefId($order_id);
                        $cost_centre_id = -1;
                    }

                    if($costCentreRefId == NULL){
                        continue;
                    }

                }
                else{
                    $cost_centre_id = $cd->cost_centre_id;
                }



                $costCentreDetails[] = [
                    'costCentreId' => $cost_centre_id,
                    'costCentreRule' => $costCentreRule,
                    'costCentreRefId' => $costCentreRefId,
                    'ledgerId'    => $ledgerID,
                    'particulars' => "",
                    'amount'    => $cdamount,
                    'isDebtor'    =>$isDebtor
                ];

            }

        }

        return $costCentreDetails;
    }

    // get reference id of Order Referal merchant for dynamic allocation of margin
    private function getOrderReferralMerchantRefId($order_id)
    {
        try
        {
            $OrderReferralMerchantId = DB::table('retaline_customer_order')->where('order_id', $order_id)->value('storegroup_id');
            return DB::table('finascop_branch_group')->where('store_group_id', $OrderReferralMerchantId)->value('storeRefId');
        }
        catch (\Exception $e)
        {
            info($e->getMessage());
            return NULL;
        }
        return NULL;
    }

    // get reference id of Customer Source merchant for dynamic allocation of margin
    private function getCustomerSourceMerchantRefId($order_id)
    {
        try
        {
            $del_address_id = DB::table('retaline_customer_order_delivery_address')->where('customer_order_id', $order_id)->value('deli_id');
            $CustomerSourceMerchantId = DB::table('retaline_customer_delivery_info')->where('deli_id', $del_address_id)->value('storegroupId');
            return DB::table('finascop_branch_group')->where('store_group_id', $CustomerSourceMerchantId)->value('storeRefId');
        }
        catch (\Exception $e)
        {
            info($e->getMessage());
            return NULL;
        }
        return NULL;
    }

    // get reference id of Business Associte for dynamic allocation of margin
    private function getBusinessAssociateRefId($order_id)
    {
        try
        {
            $branchId = DB::table('retaline_customer_order')->where('order_id', $order_id)->value('order_branch_id');
            $areaId = DB::table('finascop_branch')->where('br_ID', $branchId)->value('areaId');
            $businessAssociateId = DB::table('area_entries')->where('id', $areaId)->value('areaBusinessAssociate');
            return DB::table('business_associate')->where('id', $businessAssociateId)->value('refid');
        }
        catch (\Exception $e)
        {
            info($e->getMessage());
            return NULL;
        }
        return NULL;
    }

    // get reference id of Area Associate for dynamic allocation of margin
    private function getAreaAssociateRefId($order_id)
    {
        try
        {
            $branchId = DB::table('retaline_customer_order')->where('order_id', $order_id)->value('order_branch_id');
            $brDetails = DB::table('finascop_branch')
            ->select('br_Lat','br_Lng')
            ->where('br_ID', $branchId)
            ->first();
            $areaIdQry = "SELECT id FROM (SELECT id, areaSpan, calcDistance('" . $brDetails->br_Lat ."', '".$brDetails->br_Lng."', areaLatitude, areaLongitude) AS distance
            FROM area_entries HAVING distance <= areaSpan ORDER BY distance LIMIT 1) AS area_entry";
            info($areaIdQry);
            //$areaId = DB::table('finascop_branch')->where('br_ID', $branchId)->value('areaId');
            $areaRes = DB::select($areaIdQry);
            $businessAssociateId = DB::table('area_entries')->where('id',$areaRes[0]->id)->value('areaBusinessAssociate');
            return DB::table('business_associate')->where('id', $businessAssociateId)->value('refid');
        }
        catch (\Exception $e)
        {
            info($e->getMessage());
            return NULL;
        }
        return NULL;
    }

    // get reference id of Order Business Partner for dynamic allocation of margin
    private function getBusinessPartnerRefId($order_id)
    {
        try
        {
            $branchId = DB::table('retaline_customer_order')->where('order_id', $order_id)->value('order_branch_id');
            $areaId = DB::table('finascop_branch')->where('br_ID', $branchId)->value('areaId');
            $businessAssociateId = DB::table('area_entries')->where('id', $areaId)->value('areaBusinessAssociate');
            $businessPartnerId = DB::table('business_associate')->where('id', $businessAssociateId)->value('bpId');
            return DB::table('business_partner')->where('id', $businessPartnerId)->value('refid');
        }
        catch (\Exception $e)
        {
            info($e->getMessage());
            return NULL;
        }
        return NULL;
    }
    private function getStoreDetails( $strBranchId)
    {
        //"SELECT storeRefId,br_storeGroup,store_group_name,br_Name FROM finascop_branch_group g
        //inner join finascop_branch b on g.store_group_id = b.br_storeGroup  WHERE br_ID=@brid
        $brDetails = DB::table('finascop_branch_group')
        ->join('finascop_branch', 'store_group_id', '=', 'br_storeGroup')
        ->where('br_ID', $strBranchId) // Replace $userId with the actual user ID you're interested in
        ->select('storeRefId', 'br_storeGroup', 'store_group_name', 'br_Name')
        ->first();

        return $brDetails;
    }

    private function getCostDistributionRule($order_id, $eventID, $storeGroupId,$valueHeadID)
    {
        try
        {
            $order = Order::where('order_id', $order_id)->first();
            if($order)
            {
                $saleType = ($order->storegroup_id == 0) ? 0 : 1;
                $deliveryType = ($order->order_method == 3) ? 0 : 1;
                $paymentType = ($order->payment_mode == 1) ? 1 : 0;

                $getAreaType = DB::select("SELECT t.id FROM finance_area_type t INNER JOIN  business_associate ba ON ba.baType=t.baType AND ba.baMode=t.baMode INNER JOIN area_entries a ON a.areaBusinessAssociate = ba.id INNER JOIN finascop_branch b ON b.areaId=a.id WHERE b.br_ID={$order->order_branch_id}");
                if(@$getAreaType[0]->id)
                {
                    $areaType =  $getAreaType[0]->id;
                }
                else
                {
                    $brDetails = DB::table('finascop_branch')->select('br_Lat','br_Lng')->where('br_ID', $order->order_branch_id)->first();
                    $areaIdByBranch = DB::select("SELECT id FROM (SELECT id, areaSpan, calcDistance('{$brDetails->br_Lat}', '{$brDetails->br_Lng}', areaLatitude, areaLongitude) AS distance FROM area_entries HAVING distance <= areaSpan ORDER BY distance LIMIT 1) AS area_entry");
                    $areaType = NULL;
                    if(@$areaIdByBranch[0]->id)
                    {
                        $getAreaType = DB::select("SELECT t.id FROM finance_area_type t INNER JOIN  business_associate ba ON ba.baType=t.baType AND ba.baMode=t.baMode INNER JOIN area_entries a ON a.areaBusinessAssociate = ba.id WHERE a.id={$areaIdByBranch[0]->id}");
                        $areaType = @$getAreaType[0]->id ? $getAreaType[0]->id : NULL;
                    }
                }

                $rulename = DB::table('cost_distribution_function')->where([
                    ['event_master_id', $eventID],
                    ['sale_type_id', $saleType],
                    ['payment_type_id', $paymentType],
                    ['delivery_type_id', $deliveryType],
                    ['area_type_id', $areaType],
                    ['item_value_head_id', $valueHeadID]
                ])
                ->value('rulename');

                if ($rulename) {
                    return $rulename;
                } else {
                    return "No rulename found for the given conditions.";
                }
            }
        }
        catch (\Exception $e)
        {
            info("COST DISTRIBUTION ERROR => ".$e->getMessage());
            return 'Rule Not Found Exception';
        }
    }
}
