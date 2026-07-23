<?php
namespace App\Finance\UK;
use App\Helpers\SafeMath;

use Illuminate\Support\Facades\DB;

use App\Models\{
    Order,
    Branch,
    FinanceAutopostingValues
};

use App\Finance\UK\FinanceUKFunctions;
use App\Http\Repositories\AutopostingLogRepository;

class FinanceUK
{
    function __construct(){}

    
    public function FinanceAutopostings($data, $type)
    {
        $this->finascopAutopostingFormulaCalculation($data, $type);
        return;
        
        switch($type)
        {
            case '07801eb4-38d7-11ee-9967-065723bafb24': //Checkout
                $this->orderCheckout($data);
            break;
            case '078022db-38d7-11ee-9967-065723bafb24': //Order Success
                $this->orderSuccess($data);
            break;
            case '078025ad-38d7-11ee-9967-065723bafb24': //Order Cancellation
                $this->orderCancellation($data);
            break;
            case '0780238a-38d7-11ee-9967-065723bafb24': //Packing Completion
                $this->packingCompletion($data);
            break;
            case '07802425-38d7-11ee-9967-065723bafb24': //Order Pickup
                $this->orderPickup($data);
            break;
            case '078024a3-38d7-11ee-9967-065723bafb24': //Order Delivery
                $this->orderDelivery($data);
            break;
            case '07802530-38d7-11ee-9967-065723bafb24': //Delivery Confirmation
                $this->deliveryConfirmation($data);
            break;
            case '0780263b-38d7-11ee-9967-065723bafb24': //Order Update
                $this->orderUpdate($data);
            break;
        }
    }

    private function finascopAutopostingFormulaCalculation($order_id,$eventRefId){

        $order = Order::where('order_id', $order_id)->first();
        $event = DB::table('finance_event_master')->where('event_ref_id',$eventRefId )->first();
        $headValue = NULL;
        if($order && $event)
        {
            $valueHeadFormulas = DB::table('finance_calculation_heads')
            ->where('eventId',$event->id )
            ->orderBy('displayorder', 'asc')
            ->get();;
            //INFO("valueHeadFormulas : " . json_encode($valueHeadFormulas));
            foreach($valueHeadFormulas as $valueHeadFormula){
                $fExists = DB::table('finascop_autoposting_calculations')->where('order_id', $order_id)->where('head_id', $valueHeadFormula->id)->count();
                if($fExists == 0)
                {
                    $valueHeadFormulaStr = $valueHeadFormula->calculation;
                    //info("valueHeadFormulaStr: {$valueHeadFormulaStr} order_id : {$order_id}");                    
                    if(isset($valueHeadFormulaStr) && trim($valueHeadFormulaStr) !== '' ){
                        $mathExpression = $this->getMathExpression($order->order_id,$valueHeadFormulaStr);
                        //info("mathExpression: {$mathExpression} order_id : {$order_id}"); 
                        $mathExpression = isset($mathExpression) && trim($mathExpression) !== '' ? $mathExpression : 0;
                        try{
                            try {
                            $headValue = SafeMath::evaluate($mathExpression);
                        } catch (\InvalidArgumentException $mathErr) {
                            info(["status" => "unsafeMathExpression", "msg" => $mathErr->getMessage()]);
                            $headValue = 0;
                        }
                        }catch (\Throwable $e)
                        {
                            info([
                                'status'    => 'mathExpressionValidationException',
                                'msg'       => $e->getMessage() . "--- Order ID: " . $order_id . " ValueHeadId: " . $valueHeadFormula->id
                            ]); 

                            return;
                        }


                        DB::table('finascop_autoposting_calculations')->insert([
                            'order_id' => $order->order_id,
                            'head_id'  => $valueHeadFormula->id,
                            'head_value' => DB::raw('ROUND(' . $headValue . ', 2)'),
                            'created_at' => now(), 
                            'updated_at' => now()
                        ]);

                        $fields = [
                            'head_id'   => $valueHeadFormula->id,
                            'formula'   => $valueHeadFormulaStr,
                            'math_expression' => $mathExpression,
                            'head_value'=> $headValue
                        ];

                        (new AutopostingLogRepository)->createLog([
                                'entity_id'         => $order->order_id,
                                'event'             => $event->name,
                                'info'              => json_encode($fields),
                                'type'              => $valueHeadFormula->type,
                        ]);

                    }
                }
            }

        }
    }

    private function getMathExpression($order_id,$valueHeadFormula){
        //info("valueHeadFormula : " . $valueHeadFormula);
        $order = Order::where('order_id', $order_id)->first();
        if(substr( $valueHeadFormula, 0, 3) === "FN_"){
            $head_value = NULL;
            $functionName = $valueHeadFormula;
            if (method_exists(FinanceUKFunctions::class, $functionName)) {
                //info("Method $functionName calling.");
                $head_value = FinanceUKFunctions::$functionName($order);
                //info("Method $functionName call successful.");
            } else {
                info("Error: Method $functionName does not exist.");
            }

            return $head_value;
        }
        $dbTableInfo = explode(".", $valueHeadFormula);

        // info("dbTableInfo retaline_customer_order_items : " . json_encode($dbTableInfo));

        if($dbTableInfo[0] == 'SUM_IF_GEN_retaline_customer_order_items'){
           return  DB::table('retaline_customer_order_items')
            ->where('customer_order_id',$order_id )
            ->where('is_restaurant', 0)
            ->sum($dbTableInfo[1]);
        }

        if($dbTableInfo[0] == 'SUM_retaline_customer_order_items'){
            return  DB::table('retaline_customer_order_items')->where('customer_order_id',$order_id )->sum($dbTableInfo[1]);
        }

        // info("dbTableInfo : retaline_customer_order " . json_encode($dbTableInfo));

        if($dbTableInfo[0] == 'retaline_customer_order'){
            return  DB::table('retaline_customer_order')->where('order_id',$order_id )->value($dbTableInfo[1]);
        }


        $placeHolder = $this->getNextPlaceHolder($valueHeadFormula);
        while($placeHolder != null){
            if(substr( $placeHolder, 0, 3) === "FN_"){
                $head_value = NULL;
                $functionName = $placeHolder;
    
                if (method_exists(FinanceUKFunctions::class, $functionName)) {
                    //info("Method $functionName calling.");
                    $head_value = FinanceUKFunctions::$functionName($order);
                    //info("Method $functionName call successful.");
                } else {
                    info("Error: Method $functionName does not exist.");
                }
    
                $valueHeadFormula = str_replace( '['.$placeHolder.']',$head_value ?? 0,$valueHeadFormula);
                $placeHolder = $this->getNextPlaceHolder($valueHeadFormula);
                continue;
            }

            $dbTableInfo = explode(".", $placeHolder);

        // info("dbTableInfo retaline_customer_order_items : " . json_encode($dbTableInfo));

            if($dbTableInfo[0] == 'SUM_IF_GEN_retaline_customer_order_items'){
                $head_value =  DB::table('retaline_customer_order_items')
                ->where('customer_order_id',$order_id )
                ->where('is_restaurant', 0)
                ->sum($dbTableInfo[1]);
                $valueHeadFormula = str_replace( '['.$placeHolder.']',$head_value ?? 0,$valueHeadFormula);
                $placeHolder = $this->getNextPlaceHolder($valueHeadFormula);
                continue;
            }
            if($dbTableInfo[0] == 'SUM_retaline_customer_order_items'){
                $head_value =  DB::table('retaline_customer_order_items')->where('customer_order_id',$order_id )->sum($dbTableInfo[1]);
                $valueHeadFormula = str_replace( '['.$placeHolder.']',$head_value ?? 0,$valueHeadFormula);
                $placeHolder = $this->getNextPlaceHolder($valueHeadFormula);
                continue;
            }

            // info("dbTableInfo : retaline_customer_order " . json_encode($dbTableInfo));

            if($dbTableInfo[0] == 'retaline_customer_order'){
                $head_value =  DB::table('retaline_customer_order')->where('order_id',$order_id )->value($dbTableInfo[1]);
                $valueHeadFormula = str_replace( '['.$placeHolder.']',$head_value ?? 0,$valueHeadFormula);
                $placeHolder = $this->getNextPlaceHolder($valueHeadFormula);
                continue;
            }

            $value = $this->getValueForPlaceHolder($placeHolder,$order_id);
            $valueHeadFormula = str_replace( '['.$placeHolder.']',$value ?? 0,$valueHeadFormula);
            $placeHolder = $this->getNextPlaceHolder($valueHeadFormula);
            
        }

        $mathExpression = str_replace( ',',' ',$valueHeadFormula);

        return $mathExpression;

    }

    private function getNextPlaceHolder(&$valueHeadFormula){
        $start = strpos($valueHeadFormula, '[');
        $end = strpos($valueHeadFormula, ']');

        if ($start !== false && $end !== false) {
            return substr($valueHeadFormula, $start + 1, $end - $start - 1);
        }
    }

    private function getValueForPlaceHolder($placeHolder,$order_id)
    {

        return  DB::table('finance_calculation_heads')
            ->join('finascop_autoposting_calculations', 'finance_calculation_heads.id', '=', 'finascop_autoposting_calculations.head_id')
            ->where('order_id', $order_id)
            ->where('column_name', $placeHolder)
            ->value('head_value');

    }

    private function orderCheckout($data)
    {
        $order = Order::where('order_id', $data)->first();
        if($order)
        {
            $fExists = FinanceAutopostingValues::where('order_id', $data)->count();
            if($fExists == 0)
            {
                FinanceAutopostingValues::create([
                    'order_id'                  => $order->order_id,
                    'VATRSP_Final'              => $order->order_total_gst,
                    'OrderDeliveryCharges_ODC'  => ($order->order_delivery_charge - $order->order_delivery_charge_gst),
                    'RetailSalePriceRRP'        => $order->order_mrp_et,
                    'MRP'                       => $order->order_mrp,
                    'TaxinRRP'                  => ($order->order_mrp - $order->order_mrp_et)
                ]);
            }
        }
    }
    private function orderSuccess($data)
    {
        $order = Order::where('order_id', $data)->first();
        if($order)
        {
            $autoPostingVals = [
                'order_id'                      => $order->order_id,
                'RetailSalePriceRRP'            => $order->order_mrp_et,
                'MRP'                           => $order->order_mrp,
                'TaxinRRP'                      => ($order->order_mrp - $order->order_mrp_et),
                'OrderGrandTotal'               => $order->total,
                'TradeDiscount'                 => $order->order_saved_amount,
                'OrderDeliveryCharges_ODC'      => $order->order_delivery_charge_et,
                'SellerSalesMargin_SSM'         => $order->order_sales_margin,
                'order_payment_mode'            => $order->payment_mode,
                'OrderGrandTotal_POD'           => 0,
                'TSOPOD_PendingCollection'      => 0,
                'SellerMDR_SMDR'                => 0,
                'VATonSMDR'                     => 0,
                'RetailSalePrice'               => 0,
                'VATRSP_Final'                  => 0,
            ];
            if($order->order_roundoff < 0)
            {
                $autoPostingVals['RoundDown'] = abs($order->order_roundoff);
            }
            if($order->order_roundoff > 0)
            {
                $autoPostingVals['RoundUp'] = $order->order_roundoff;
            }
    
            $autoPostingVals['VATonSSM'] = round($order->order_sales_margin * 0.2, 2);
            $autoPostingVals['VATonODC'] = $order->order_delivery_charge_gst;


            $goozeoState = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='GROZEO_STATE'");
            $branchData = Branch::select('br_pgchargeId', 'br_State')->where('br_Id', $order->order_branch_id)->first();
            if($branchData->br_pgchargeId > 0)
            {
                $pgPerc = DB::select("SELECT pgChargePercentage FROM pgcharge_master WHERE pgChargeId={$branchData->br_pgchargeId}");
            }
            else
            {
                $pgPerc = DB::select("SELECT pgChargePercentage FROM pgcharge_master WHERE pgChargeIsDefault=1 AND pgChargeStatus=1");
            }
            
            // restaurant
            $gstOthers = 0;
            foreach ($order->orderItems as $item)
            {
                $autoPostingVals['RetailSalePrice'] += ($item->order_item_mrp_et - $item->order_item_seller_discount);
                $autoPostingVals['VATRSP_Final'] += $item->order_item_gst;
                $gstOthers += $item->order_item_gst;
            }
            if($autoPostingVals['RetailSalePrice'] > 0)
            {
                $otherTot = $autoPostingVals['RetailSalePrice'] + $gstOthers;
                $roundOtherTot = round($otherTot);
                $otherTotRound = round(($otherTot - $roundOtherTot), 2);
                $otherTotRoundABS = abs($otherTotRound);
                if($otherTotRound > 0)
                {
                    $autoPostingVals['RoundDown_General'] = abs($otherTotRound);
                    $autoPostingVals['RoundUp_General'] = 0;
                }
                if($otherTotRound < 0)
                {
                    $autoPostingVals['RoundUp_General'] = abs($otherTotRound);
                    $autoPostingVals['RoundDown_General'] = 0;
                }
            }

            if($order->payment_mode == 2 || $order->payment_mode == 3 || $order->payment_mode == 5)
            {
                // payment gateway
                $paymentgateway = $order->order_payment_gateway;
                if(config("paymentgateway.{$paymentgateway}.tax") == 'inclusive')
                {
                    $mdrTax = $order->order_payment_gateway_fees - $order->order_payment_gateway_tax;
                    $autoPostingVals['MerchantDiscountRate_MDR'] = $mdrTax;
                }
                else
                {
                    $autoPostingVals['MerchantDiscountRate_MDR'] = $order->order_payment_gateway_fees;
                }
                $autoPostingVals['VATInputonMDR'] = $order->order_payment_gateway_tax;
                // payment result
                if($pgPerc[0]->pgChargePercentage)
                {
                    $autoPostingVals['SellerMDR_SMDR'] = round($order->total * ($pgPerc[0]->pgChargePercentage / 100), 2);
                    $autoPostingVals['VATonSMDR'] = round($autoPostingVals['SellerMDR_SMDR'] * 0.20, 2);
                }
                $autoPostingVals['OrderGrandTotal'] = $order->total;
                $autoPostingVals['OrderGrandTotal_POD'] = NULL;
                $autoPostingVals['CustomerWallet_Withdrawal'] = $order->order_wallet_amount;
            }
            else if($order->payment_mode == 1 || $order->payment_mode == 4)
            {
                $autoPostingVals['OrderGrandTotal'] = NULL;
                $autoPostingVals['OrderGrandTotal_POD'] = $order->total;
                $autoPostingVals['TSOPOD_PendingCollection'] = $order->total - $order->order_wallet_amount;
                $autoPostingVals['CustomerWallet_Withdrawal_POD'] = $order->order_wallet_amount;
            }


            $fExists = FinanceAutopostingValues::where('order_id', $data)->first();
            if($fExists == NULL)
            {
                FinanceAutopostingValues::create($autoPostingVals);
            }
            else
            {
                FinanceAutopostingValues::where('order_id', $order->order_id)->update($autoPostingVals);
            }
        }
    }
    private function orderCancellation($data)
    {
        $order = Order::where('order_id', $data)->first();
        if($order)
        {
            $autoPostingVals = FinanceAutopostingValues::select('OrderGrandTotal', 'OrderGrandTotal_POD', 'TSOPOD_PendingCollection')->where('order_id', $order->order_id)->first();
            if($autoPostingVals)
            {
                $autoUpdate = [
                    'TSOPODPendingCollection_Cancelled' => @$autoPostingVals->TSOPOD_PendingCollection,
                    'is_cancelled'                      => '1',
                    'order_payment_mode'                => $order->payment_mode
                ];
                if($order->payment_mode == 3 || $order->payment_mode == 2 || $order->payment_mode == 5)
                {
                    $autoUpdate['CustomerWallet_Deposit'] = $autoPostingVals->OrderGrandTotal;
                    $autoUpdate['CustomerWalletDeposit_Cancelled'] = $autoPostingVals->OrderGrandTotal;
                }
                if($order->payment_mode == 4)
                {
                    $autoUpdate['CustomerWallet_Deposit'] = $order->order_wallet_amount;
                    $autoUpdate['CustomerWalletDeposit_Cancelled'] = $order->order_wallet_amount;
                }
                $autoPosting = FinanceAutopostingValues::where('order_id', $order->order_id)->update($autoUpdate);
            }
        }
    }
    private function packingCompletion($data)
    {
    }
    private function orderPickup($data)
    {
    }
    private function orderDelivery($data)
    {
    }
    private function deliveryConfirmation($data)
    {
        $order = Order::where('order_id', $data)->first();
        if($order)
        {
            if($order->payment_mode == 1 || $order->payment_mode == 4)
            {
                $favValues = FinanceAutopostingValues::select('OrderGrandTotal_POD', 'CustomerWallet_Withdrawal_POD')->where('order_id', $data)->first();
                $favUpdate = FinanceAutopostingValues::where('order_id', $data)->update([
                    'CourierCollection_COD'                 => @$favValues->OrderGrandTotal_POD - @$favValues->CustomerWallet_Withdrawal_POD,
                    'GrozeoLogisticsPartnerCollection_COD'  => @$favValues->OrderGrandTotal_POD - @$favValues->CustomerWallet_Withdrawal_POD,
                    'TenantCollection_COD'                  => @$favValues->OrderGrandTotal_POD - @$favValues->CustomerWallet_Withdrawal_POD
                ]);
            }
        }
    }
    private function orderUpdate($data)
    {
    }

}