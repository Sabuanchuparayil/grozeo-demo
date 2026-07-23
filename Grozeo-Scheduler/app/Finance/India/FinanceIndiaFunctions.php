<?php
namespace App\Finance\India;

use Illuminate\Support\Facades\DB;

use App\Models\{
    Order,
    Branch,
    Customer
};

class EntityTypes
{
    public const Product = 1;
    public const Brand = 2;
    public const Subcategory = 3;
    public const Category = 4;
    public const Department = 5;
    public const RetailCategory = 6;
    public const Store = 7;
    public const StoreGroup = 8;
}

class ChargeTypes
{
    public const Percentage = 1;
    public const Amount = 2;
}

class FinanceIndiaFunctions
{
    public static function FN_RoundDown($order)
    {
        if($order->order_roundoff < 0)
        {
            return self::nearestEvenDecimal((double) abs($order->order_roundoff));
        }
        return null;
    }

    public static function FN_RoundUp($order)
    {
        if($order->order_roundoff > 0)
        {
            return self::nearestEvenDecimal((double) $order->order_roundoff);
        }
        return null;
    }

    public static function FN_CGSTonSSM($order){
        $branchData = Branch::select('br_pgchargeId', 'br_State')->where('br_Id', $order->order_branch_id)->first();
        $grozeoState = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='GROZEO_STATE'");
        if($branchData->br_State == $grozeoState[0]->cfg_Value)
        {
            return self::nearestEvenDecimal(round($order->order_sales_margin * 0.09, 2));
        }

        return null;
    }

    
    public static function FN_SGSTonSSM($order){
        $branchData = Branch::select('br_pgchargeId', 'br_State')->where('br_Id', $order->order_branch_id)->first();
        $grozeoState = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='GROZEO_STATE'");
        if($branchData->br_State == $grozeoState[0]->cfg_Value)
        {
            return self::nearestEvenDecimal(round($order->order_sales_margin * 0.09, 2));
        }

        return null;
    }

    public static function FN_IGSTonSSM($order){
        $grozeoState = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='GROZEO_STATE'");
        $branchData = Branch::select('br_pgchargeId', 'br_State')->where('br_Id', $order->order_branch_id)->first();
        if($branchData->br_State != $grozeoState[0]->cfg_Value)
        {
            return self::nearestEvenDecimal(round($order->order_sales_margin * 0.18, 2));
        }

        return null;
    }

    public static function FN_CGSTonODC($order){
        $customer = Customer::where('cust_id', $order->order_customer_id)->first();
        $grozeoState = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='GROZEO_STATE'");
        if($customer->primaryAddress->state->st_ID == $grozeoState[0]->cfg_Value)
        {
            return ($order->order_delivery_charge_cgst > 0) ? self::nearestEvenDecimal($order->order_delivery_charge_cgst) : self::nearestEvenDecimal($order->order_delivery_charge_igst/2);
        }
        return null;
    }


    public static function FN_SGSTonODC($order){
        $customer = Customer::where('cust_id', $order->order_customer_id)->first();
        $grozeoState = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='GROZEO_STATE'");
        if($customer->primaryAddress->state->st_ID == $grozeoState[0]->cfg_Value)
        {
            return ($order->order_delivery_charge_sgst > 0) ? self::nearestEvenDecimal($order->order_delivery_charge_sgst) : self::nearestEvenDecimal($order->order_delivery_charge_igst/2);
        }

        return null;
    }

    public static function FN_IGSTonODC($order){
        $customer = Customer::where('cust_id', $order->order_customer_id)->first();
        $grozeoState = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='GROZEO_STATE'");
        if($customer->primaryAddress->state->st_ID != $grozeoState[0]->cfg_Value)
        {
            return ($order->order_delivery_charge_igst > 0) ? self::nearestEvenDecimal($order->order_delivery_charge_igst) : self::nearestEvenDecimal($order->order_delivery_charge_cgst + $order->order_delivery_charge_sgst);
        }
        return null;
    }



    //OrderGrandTotal

    public static function FN_OrderGrandTotal($order){

        $OrderGrandTotal = $order->total;
        if(($order->payment_mode == 1) || ($order->payment_mode == 4))
        {
            $OrderGrandTotal = NULL;
        }else if($order->payment_mode == 2 || $order->payment_mode == 3 || $order->payment_mode == 5)
        {
            $OrderGrandTotal = $order->total;
        }
        $decimalValue = (double) $OrderGrandTotal;
        return $decimalValue;
    }


    public static function FN_OrderGrandTotal_POD($order){

        $OrderGrandTotal_POD = 0;
        if(($order->payment_mode == 1) || ($order->payment_mode == 4))
        {
            $OrderGrandTotal_POD = $order->total;
        }else if($order->payment_mode == 2 || $order->payment_mode == 3 || $order->payment_mode == 5)
        {
            $OrderGrandTotal_POD = 0;
        }
        $decimalValue = (double) $OrderGrandTotal_POD;
        return $decimalValue;
    }

      //TSOPOD_PendingCollection

    public static function FN_TSOPOD_PendingCollection($order){

        $TSOPOD_PendingCollection = 0;
        if(($order->payment_mode == 1) || ($order->payment_mode == 4))
        {
            $TSOPOD_PendingCollection = $order->total - $order->order_wallet_amount;
        }else if($order->payment_mode == 2 || $order->payment_mode == 3 || $order->payment_mode == 5)
        {
            $TSOPOD_PendingCollection = 0;
        }
        $decimalValue = (double) $TSOPOD_PendingCollection;
        return $decimalValue;
    }

    //CustomerWallet_Withdrawal_POD

    public static function FN_CustomerWallet_Withdrawal_POD($order){

        $CustomerWallet_Withdrawal_POD = 0;
        if(($order->payment_mode == 1) || ($order->payment_mode == 4))
        {
            $CustomerWallet_Withdrawal_POD = $order->order_wallet_amount;
        }
        $decimalValue = (double) $CustomerWallet_Withdrawal_POD;
        return $decimalValue;
    }


    public static function FN_CustomerWallet_Withdrawal($order){

        $CustomerWallet_Withdrawal = 0;
        if(($order->payment_mode == 3) || ($order->payment_mode == 5))
        {
            $CustomerWallet_Withdrawal = $order->order_wallet_amount;
        }
        $decimalValue = (double) $CustomerWallet_Withdrawal;
        return $decimalValue;
    }

    public static function FN_RetailSalePrice_Restaurant($order){
        $RetailSalePrice_Restaurant = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if( $branchData->platform_tax_enabled == 1 && @$item->item->productCategory->hasRestaurantService == 1)
                {
                    $RetailSalePrice_Restaurant += ($item->order_item_mrp_et - $item->order_item_seller_discount);
                }
            }
        }
        $decimalValue = (double) $RetailSalePrice_Restaurant;
        return $decimalValue;
    }

    public static function FN_IGSTonRSP_Restaurant($order){
        $IGSTonRSP_Restaurant = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if( $branchData->platform_tax_enabled == 1 && @$item->item->productCategory->hasRestaurantService == 1)
                {
                    $IGSTonRSP_Restaurant += $item->order_item_igst;
                }
            }
        }
        $decimalValue = (double) $IGSTonRSP_Restaurant;
        return $decimalValue;
    }

    public static function FN_CGSTonRSP_Restaurant($order){
        $CGSTonRSP_Restaurant = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if( $branchData->platform_tax_enabled == 1 && @$item->item->productCategory->hasRestaurantService == 1)
                {
                    $CGSTonRSP_Restaurant += $item->order_item_cgst;
                }
            }

            
        }
        $decimalValue = (double) $CGSTonRSP_Restaurant;
        return $decimalValue;
    }

    public static function FN_SGSTonRSP_Restaurant($order){
        $SGSTonRSP_Restaurant = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if( $branchData->platform_tax_enabled == 1 && @$item->item->productCategory->hasRestaurantService == 1)
                {
                    $SGSTonRSP_Restaurant += $item->order_item_sgst;
                }
            }
        }
        $decimalValue = (double) $SGSTonRSP_Restaurant;
        return $decimalValue;
    }

    public static function FN_UTGSTonRSP_Restaurant($order){
        $UTGSTonRSP_Restaurant = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if( $branchData->platform_tax_enabled == 1 && @$item->item->productCategory->hasRestaurantService == 1)
                {
                    $UTGSTonRSP_Restaurant += $item->order_item_ugst;
                }
            }
        }
        $decimalValue = (double) $UTGSTonRSP_Restaurant;
        return $decimalValue;
    }

    public static function FN_CConRSP_Restaurant($order){
        $CConRSP_Restaurant = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if( $branchData->platform_tax_enabled == 1 && @$item->item->productCategory->hasRestaurantService == 1)
                {
                    $CConRSP_Restaurant += $item->order_item_cess;
                }
            }
        }
        $decimalValue = (double) $CConRSP_Restaurant;
        return $decimalValue;
    }

    public static function FN_GSTonRSP_RestaurantTotal($order){
        $GSTonRSP_RestaurantTotal = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if( $branchData->platform_tax_enabled == 1 && @$item->item->productCategory->hasRestaurantService == 1)
                {
                    $GSTonRSP_RestaurantTotal += $item->order_item_gst;
                }
            }
        }
        $decimalValue = (double) $GSTonRSP_RestaurantTotal;
        return $decimalValue;
    }


    public static function FN_RetailSalePrice($order){
        $RetailSalePrice = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if($branchData->platform_tax_enabled != 1)
                {
                    $RetailSalePrice += ($item->order_item_mrp_et - $item->order_item_seller_discount);
                }
            }
        }
        $decimalValue = (double) $RetailSalePrice;

        return $decimalValue;
    }

    //IGSTonRSP_Final
    public static function FN_IGSTonRSP_Final($order){
        $IGSTonRSP_Final = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if($branchData->platform_tax_enabled != 1)
                {
                    $IGSTonRSP_Final += $item->order_item_igst;
                }
            }
        }
        $decimalValue = (double) $IGSTonRSP_Final;
        return $decimalValue;
    }

    public static function FN_CGSTonRSP_Final($order){
        $CGSTonRSP_Final = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if($branchData->platform_tax_enabled != 1)
                {
                    $CGSTonRSP_Final += $item->order_item_cgst;
                }
            }
        }
        $decimalValue = (double) $CGSTonRSP_Final;
        return $decimalValue;
    }

    public static function FN_SGSTonRSP_Final($order){
        $SGSTonRSP_Final = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if($branchData->platform_tax_enabled != 1)
                {
                    $SGSTonRSP_Final += $item->order_item_sgst;
                }
            }
        }
        $decimalValue = (double) $SGSTonRSP_Final;
        return $decimalValue;
    }


    public static function FN_UTGSTonRSP_Final($order){
        $UTGSTonRSP_Final = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if($branchData->platform_tax_enabled != 1)
                {
                    $UTGSTonRSP_Final += $item->order_item_ugst;
                }
            }
        }
        $decimalValue = (double) $UTGSTonRSP_Final;
        return $decimalValue;
    }

    public static function FN_CConRSP_Final($order){
        $CConRSP_Final = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if($branchData->platform_tax_enabled != 1)
                {
                    $CConRSP_Final += $item->order_item_cess;
                }
            }
        }
        $decimalValue = (double) $CConRSP_Final;
        return $decimalValue;
    }

    public static function FN_TCSCC($order){
        $TCSCC = 0;
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if(@$item->is_restaurant != 1 && @$item->order_item_cess > 0)
                {
                    $TCSCC += $item->order_item_basket_price_et * 0.01;
                }
            }
        }
        $decimalValue = (double) round($TCSCC,2);
        return $decimalValue;
    }

    public static function FN_TCSIGST($order){
        $TCSIGST = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if($branchData->platform_tax_enabled != 1)
                {
                    $TCSIGST += $item->order_item_tcs_igst;
                }
            }
        }
        $decimalValue = (double) $TCSIGST;
        return $decimalValue;
    }

    public static function FN_TCSCGST($order){
        $TCSCGST = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if($branchData->platform_tax_enabled != 1)
                {
                    $TCSCGST += $item->order_item_tcs_cgst;
                }
            }
        }
        $decimalValue = (double) $TCSCGST;
        return $decimalValue;
    }


    public static function FN_TCSSGST($order){
        $TCSSGST = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if($branchData->platform_tax_enabled != 1)
                {
                    $TCSSGST += $item->order_item_tcs_sgst;
                }
            }
        }
        $decimalValue = (double) $TCSSGST;
        return $decimalValue;
    }

    public static function FN_TCSUTGST($order){
        $TCSUTGST = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if($branchData->platform_tax_enabled != 1)
                {
                    $TCSUTGST += $item->order_item_tcs_utgst;
                }
            }
        }
        $decimalValue = (double) $TCSUTGST;
        return $decimalValue;
    }

    public static function FN_TCSGSTTotal($order){
        $TCSGSTTotal = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if($branchData->platform_tax_enabled != 1)
                {
                    $TCSGSTTotal += $item->order_item_tcs_gst;
                }
            }
        }
        $decimalValue = (double) $TCSGSTTotal;
        return $decimalValue;
    }

    public static function FN_GSTTotal($order){
        $GSTTotal = 0;
        $branchData = Branch::select('platform_tax_enabled')->where('br_Id', $order->order_branch_id)->first();
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                if($branchData->platform_tax_enabled != 1)
                {
                    $GSTTotal += $item->order_item_gst;
                }
            }
        }
        $decimalValue = (double) $GSTTotal;
        return $decimalValue;
    }

    public static function FN_RoundDown_RestaurantSales($order){
        $RoundDown_RestaurantSales = 0;
        $RetailSalePrice_Restaurant = self::FN_RetailSalePrice_Restaurant($order);
        $GSTonRSP_RestaurantTotal =  self::FN_GSTonRSP_RestaurantTotal($order);

        if($RetailSalePrice_Restaurant > 0)
        {
            $restTot = $RetailSalePrice_Restaurant + $GSTonRSP_RestaurantTotal;
            $roundRestTot = round($restTot);
            $restTotRound = round(($restTot - $roundRestTot), 2);
            $restTotRoundABS = abs($restTotRound);
            if($restTotRound > 0)
            {
                $RoundDown_RestaurantSales = abs($restTotRound);
            }else{
                $RoundDown_RestaurantSales = 0;
            }
        }

        $decimalValue = (double) $RoundDown_RestaurantSales;
        return $decimalValue;
    }

    
    public static function FN_RoundUp_RestaurantSales($order){
        $RoundUp_RestaurantSales = 0;
        $RetailSalePrice_Restaurant = self::FN_RetailSalePrice_Restaurant($order);
        $GSTonRSP_RestaurantTotal =  self::FN_GSTonRSP_RestaurantTotal($order);

        if($RetailSalePrice_Restaurant > 0)
        {
            $restTot = $RetailSalePrice_Restaurant + $GSTonRSP_RestaurantTotal;
            $roundRestTot = round($restTot);
            $restTotRound = round(($restTot - $roundRestTot), 2);
            $restTotRoundABS = abs($restTotRound);
            if($restTotRound < 0)
            {
                $RoundUp_RestaurantSales = abs($restTotRound);
            }else{
                $RoundUp_RestaurantSales = 0;
            }
        }

        $decimalValue = (double) $RoundUp_RestaurantSales;
        return $decimalValue;
    }

    public static function FN_RoundDown_General($order){
        $RoundDown_General = 0;
        $RetailSalePrice = self::FN_RetailSalePrice($order);
        $CConRSP_Final = self::FN_CConRSP_Final($order);
        if($RetailSalePrice > 0)
        {
            $otherTot = $RetailSalePrice + self::FN_GSTTotal($order) + $CConRSP_Final;
            $roundOtherTot = round($otherTot);
            $otherTotRound = round(($otherTot - $roundOtherTot), 2);
            $otherTotRoundABS = abs($otherTotRound);
            if($otherTotRound > 0)
            {
                $RoundDown_General = $otherTotRoundABS;
            }else{
                $RoundDown_General = 0;
            }
        }
        $decimalValue = (double) $RoundDown_General;
        return $decimalValue;
    }

    public static function FN_RoundUp_General($order){
        $RoundUp_General = 0;
        $RetailSalePrice = self::FN_RetailSalePrice($order);
        $CConRSP_Final = self::FN_CConRSP_Final($order);
        $GSTTotal = self::FN_GSTTotal($order);

        if($RetailSalePrice > 0)
        {
            $otherTot = $RetailSalePrice + $GSTTotal + $CConRSP_Final;
            $roundOtherTot = round($otherTot);
            $otherTotRound = round(($otherTot - $roundOtherTot), 2);
            $otherTotRoundABS = abs($otherTotRound);
            if($otherTotRound < 0)
            {
                $RoundUp_General = $otherTotRoundABS;
            }else{
                $RoundUp_General = 0;
            }
        }
        $decimalValue = (double) $RoundUp_General;
        return $decimalValue;
    }

    public static function FN_MerchantDiscountRate_MDR($order){
        $MerchantDiscountRate_MDR = 0;

        if($order->payment_mode == 2 || $order->payment_mode == 3 || $order->payment_mode == 5)
        {
            $paymentgateway = $order->order_payment_gateway;
            if(config("paymentgateway.{$paymentgateway}.tax") == 'inclusive')
            {
                $MerchantDiscountRate_MDR = $order->order_payment_gateway_fees - $order->order_payment_gateway_tax;
            }
            else
            {
                $MerchantDiscountRate_MDR = $order->order_payment_gateway_fees;
            }
        }

        $decimalValue = (double)  $MerchantDiscountRate_MDR;
        return $decimalValue;

    }

    public static function FN_CGSTInputonMDR($order){
        $CGSTInputonMDR = 0;

        if(@config("paymentgateway.{$paymentgateway}.b_type") == 'intra')
        {
            $CGSTInputonMDR =  $order->order_payment_gateway_tax/2;
        }

        $decimalValue = (double)  $CGSTInputonMDR;
        return $decimalValue;
    }

    public static function FN_SGSTInputonMDR($order){
        $SGSTInputonMDR = 0;

        if(@config("paymentgateway.{$paymentgateway}.b_type") == 'intra')
        {
            $SGSTInputonMDR= $order->order_payment_gateway_tax/2;
        }

        $decimalValue = (double)  $SGSTInputonMDR;
        return $decimalValue;
    }

    public static function FN_IGSTInputonMDR($order){
        $IGSTInputonMDR = 0;

        if(@config("paymentgateway.{$paymentgateway}.b_type") != 'intra')
        {
            $IGSTInputonMDR= $order->order_payment_gateway_tax;
        }

        $decimalValue = (double)  $IGSTInputonMDR;
        return $decimalValue;
    }

    public static function FN_SellerMDR_SMDR($order){
        $SellerMDR_SMDR = 0;
        $branchData = Branch::select('br_pgchargeId', 'br_State')->where('br_Id', $order->order_branch_id)->first();
        if($branchData->br_pgchargeId > 0)
        {
            $pgPerc = DB::select("SELECT pgChargePercentage FROM pgcharge_master WHERE pgChargeId={$branchData->br_pgchargeId}");
        }
        else
        {
            $pgPerc = DB::select("SELECT pgChargePercentage FROM pgcharge_master WHERE pgChargeIsDefault=1 AND pgChargeStatus=1");
        }

        if(@$pgPerc[0]->pgChargePercentage)
        {
            $SellerMDR_SMDR = round($order->total * (@$pgPerc[0]->pgChargePercentage/100), 2);
        }
        $decimalValue = (double)  $SellerMDR_SMDR;
        return $decimalValue;
    }
    
    public static function FN_CGSTonSMDR($order){
        $CGSTonSMDR = 0;
        $SellerMDR_SMDR = self::FN_SellerMDR_SMDR($order);
        $branchData = Branch::select('br_pgchargeId', 'br_State')->where('br_Id', $order->order_branch_id)->first();
        $grozeoState = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='GROZEO_STATE'");
        if($branchData->br_State == $grozeoState[0]->cfg_Value)
        {
            if($SellerMDR_SMDR)
            {
                $CGSTonSMDR = round($SellerMDR_SMDR * 0.09, 2);
            }
        }

        info("");
        $decimalValue = (double)  $CGSTonSMDR;
        return $decimalValue;
    }

    public static function FN_SGSTonSMDR($order){
        $SGSTonSMDR = 0;
        $SellerMDR_SMDR = self::FN_SellerMDR_SMDR($order);
        $branchData = Branch::select('br_pgchargeId', 'br_State')->where('br_Id', $order->order_branch_id)->first();
        $grozeoState = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='GROZEO_STATE'");
        if($branchData->br_State == $grozeoState[0]->cfg_Value)
        {
            if($SellerMDR_SMDR)
            {
                $SGSTonSMDR = round($SellerMDR_SMDR * 0.09, 2);
            }
        }
        $decimalValue = (double)  $SGSTonSMDR;
        return $decimalValue;
    }


    //IGSTonSMDR
    public static function FN_IGSTonSMDR($order){
        $IGSTonSMDR = 0;
        $SellerMDR_SMDR = self::FN_SellerMDR_SMDR($order);
        $branchData = Branch::select('br_pgchargeId', 'br_State')->where('br_Id', $order->order_branch_id)->first();
        $grozeoState = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='GROZEO_STATE'");
        if($branchData->br_State != $grozeoState[0]->cfg_Value)
        {
            if($SellerMDR_SMDR)
            {
                $IGSTonSMDR = round($SellerMDR_SMDR * 0.18, 2);
            }
        }
        $decimalValue = (double)  $IGSTonSMDR;
        return $decimalValue;
    }

    //CustomerWallet_Deposit

    public static function FN_CustomerWallet_Deposit($order){
        $CustomerWallet_Deposit = 0;
        if($order->status_id == 19){
            $OrderGrandTotal = self::FN_OrderGrandTotal($order);
        
            if($order->payment_mode == 3  || $order->payment_mode == 5)
            {
                $CustomerWallet_Deposit = $OrderGrandTotal;
            }
            if($order->payment_mode == 4 )
            {
                $CustomerWallet_Deposit = $order->order_wallet_amount;
            }
        }
        $decimalValue = (double)  $CustomerWallet_Deposit;
        return $decimalValue;
    }

    public static function FN_CustomerWalletDeposit_Cancelled($order){
        $CustomerWalletDeposit_Cancelled = 0;
        if($order->status_id == 19){
            $OrderGrandTotal = self::FN_OrderGrandTotal($order);
            if($order->payment_mode == 3)
            {
                $CustomerWalletDeposit_Cancelled = $OrderGrandTotal;
            }
            if($order->payment_mode == 4  || $order->payment_mode == 5)
            {
                $CustomerWalletDeposit_Cancelled = $order->order_wallet_amount;
            }
        }

        $decimalValue = (double)  $CustomerWalletDeposit_Cancelled;
        return $decimalValue;
    }

    public static function FN_CourierCollection_COD($order){
        $CourierCollection_COD = 0;
        if($order->payment_mode == 1 || $order->payment_mode == 4)
        {
            $OrderGrandTotal_POD = self::FN_OrderGrandTotal_POD($order);
            $CustomerWallet_Withdrawal_POD = self::FN_CustomerWallet_Withdrawal_POD($order);
            $CourierCollection_COD = @$OrderGrandTotal_POD - @$CustomerWallet_Withdrawal_POD;
        }
        $decimalValue = (double)  $CourierCollection_COD;
        return $decimalValue;
    }

    public static function FN_GrozeoLogisticsPartnerCollection_COD($order){
        $GrozeoLogisticsPartnerCollection_COD = 0;
        if($order->payment_mode == 1 || $order->payment_mode == 4)
        {
            $OrderGrandTotal_POD = self::FN_OrderGrandTotal_POD($order);
            $CustomerWallet_Withdrawal_POD = self::FN_CustomerWallet_Withdrawal_POD($order);            
            $GrozeoLogisticsPartnerCollection_COD = @$OrderGrandTotal_POD - @$CustomerWallet_Withdrawal_POD;
        }
        $decimalValue = (double)  $GrozeoLogisticsPartnerCollection_COD;
        return $decimalValue;
    }
    
    public static function FN_TenantCollection_COD($order){
        $TenantCollection_COD = 0;
        if($order->payment_mode == 1 || $order->payment_mode == 4)
        {
            $OrderGrandTotal_POD = self::FN_OrderGrandTotal_POD($order);
            $CustomerWallet_Withdrawal_POD = self::FN_CustomerWallet_Withdrawal_POD($order); 
            $TenantCollection_COD = @$OrderGrandTotal_POD - @$CustomerWallet_Withdrawal_POD;
        }
        $decimalValue = (double)  $TenantCollection_COD;
        return $decimalValue;
    }

    public static function FN_GetSellerPlatformFee($order){

        
        $entityTypeMatrix = [
            ['stit_id', EntityTypes::Product],
            ['pdt_brand', EntityTypes::Brand],
            ['product_category', EntityTypes::Subcategory],
            ['category_id', EntityTypes::Category],
            ['departmentId', EntityTypes::Department],
            ['ratailCatId', EntityTypes::RetailCategory],
            ['br_ID', EntityTypes::Store],
            ['br_storegroup', EntityTypes::StoreGroup],
        ];

            $orderProductDetailsQry = "SELECT i.stit_id, i.pdt_brand, i.product_category,c.category_id, 
            pc.parent_category_id AS departmentId , sbt.business_type_id AS ratailCatId,b.br_ID, b.br_storegroup
                    FROM retaline_customer_order_items oi INNER JOIN retaline_customer_order o ON o.order_id=oi.customer_order_id
                    INNER JOIN finascop_stock_itemmaster i ON oi.item_product_id=i.stit_id INNER JOIN finascop_branch b ON o.order_branch_id=b.br_ID
                    INNER JOIN mypha_productsubcategory sc ON sc.sub_category_id = i.product_category
                    INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id AND c.STATUS = '1'
                    INNER JOIN mypha_productparent_category pc ON c.parent_category = pc.parent_category_id AND pc.STATUS = 1
                    INNER JOIN finascop_business_type sbt ON pc.parent_category_businessType = sbt.business_type_id
                    WHERE o.order_id = " . $order->order_id;


            $orderProductDetails = DB::select($orderProductDetailsQry);
            $decimalValue = 0;

            $orderProductDetailsMatrix = [];

            foreach ($orderProductDetails as $row) {
                foreach ((array)$row as $column => $value) {
                    $orderProductDetailsMatrix[] = [
                        'column_id' => $column,
                        'value' => $value
                    ];
                }
            }

        $detailIdPairs = [];

        foreach ($orderProductDetails as $opd) {
            foreach ($entityTypeMatrix as [$column, $type]) {
                if (!empty($opd->{$column})) {
                    $detailIdPairs[] = [
                        'type' => $type,
                        'detailId' => $opd->{$column}
                    ];
                }
            }
        }

        $additionalCharges = DB::table('additional_charge')
            ->select('type', 'detailId', 'charge', 'chargeType')
            ->where(function($query) use ($detailIdPairs) {
                foreach ($detailIdPairs as $pair) {
                    $query->orWhere(function($q) use ($pair) {
                        $q->where('type', $pair['type'])
                        ->where('detailId', 'like', $pair['detailId']);
                    });
                }
            })->get();
        
        $a_count = count($additionalCharges);
        $e_count = count($entityTypeMatrix);

        for ($i = 0; $i < $a_count;) {
            $skip_rows = $e_count;
            for($j = 0; $j < $e_count;$j++){
                $result = $additionalCharges[$i++];
                $skip_rows--;
                if($result->charge != 0){
                    if($result->chargeType == ValueTypes::Percentage){
                        $decimalValue += (double)$result->charge/100 * $order->subtotal;
                    }
                    if($result->chargeType == ValueTypes::Amount){
                        $decimalValue += (double)$result->charge;
                    }
                }
            }
            $i += $skip_rows;
        }
  
        
        if($decimalValue > 0) return $decimalValue;

        $SellerPlatformFee = DB::table('sys_configuration')
        ->where('cfg_Name', 'DEFAULT_SELLER_PLATFORM_CHARGE')
        ->value('cfg_Value');

        $decimalValue = (double)  $SellerPlatformFee/100 * $order->subtotal;
        return $decimalValue;
    }

    public static function FN_isCustomPaymentGateway($order)
    {
        $paymentGateway = $order->order_payment_gateway;
        if(!(isset($paymentGateway)) || is_null($paymentGateway) || trim($paymentGateway) == "")
        {
            return 0;
        }

        $pgTableName = "finascop_company_" . $paymentGateway;
        $storegroup_id = $order->storegroup_id;
    
   
        $result = DB::table($pgTableName)
            ->where('storegroup_id', '=', $storegroup_id) 
            ->exists(); 
        
        return $result ? 1 : 0;
    }

    public static function nearestEvenDecimal($decimalValue)
    {
        $numArr = explode('.',$decimalValue);
        if(@$numArr[1])
        {
            $num_length = strlen((string)$numArr[1]); //to cover .01, .001
            if($num_length == 1)
            {
                $numArr[1] = $numArr[1]*10;
            }
            $remainder = $numArr[1]%2;
            if ($remainder == 0)
            {
              return $decimalValue;
            }
            else
            {
              return $decimalValue + 0.01;
            }
        }else{
            return $decimalValue;
        }
    }
    
}