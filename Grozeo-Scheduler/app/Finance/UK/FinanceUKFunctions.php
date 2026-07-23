<?php
namespace App\Finance\UK;

use Illuminate\Support\Facades\DB;

use App\Models\{
    Order,
    Branch,
    Customer
};

class FinanceUKFunctions
{


    public static function FN_VATRSP_Final($order){
        $VATRSP_Final = 0;
        if($order->orderItems){
            foreach ($order->orderItems as $item)
            {
                $VATRSP_Final+= $item->order_item_gst;
            }
        }
        $decimalValue = (double) $VATRSP_Final;
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

    public static function FN_VATInputonMDR($order){
        $VATInputonMDR = 0;

        if(@config("paymentgateway.{$paymentgateway}.b_type") != 'intra')
        {
            $VATInputonMDR= $order->order_payment_gateway_tax;
        }

        $decimalValue = (double)  $VATInputonMDR;
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

    public static function FN_VATonSMDR($order){
        $VATonSMDR = 0;
        $SellerMDR_SMDR = self::FN_SellerMDR_SMDR($order);
        $branchData = Branch::select('br_pgchargeId', 'br_State')->where('br_Id', $order->order_branch_id)->first();
        $grozeoState = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='GROZEO_STATE'");
        if($branchData->br_State != $grozeoState[0]->cfg_Value)
        {
            if($SellerMDR_SMDR)
            {
                $VATonSMDR = round($SellerMDR_SMDR * 0.20, 2);
            }
        }
        $decimalValue = (double)  $VATonSMDR;
        return $decimalValue;
    }

    public static function FN_CustomerWallet_Deposit($order){
        $CustomerWallet_Deposit = 0;
        if($order->status_id == 19){
            $OrderGrandTotal = $order->total;
            if(($order->payment_mode == 1) || ($order->payment_mode == 4))
            {
                $OrderGrandTotal = NULL;
            }else if($order->payment_mode == 2 || $order->payment_mode == 3 || $order->payment_mode == 5)
            {
                $OrderGrandTotal = $order->total;
            }
            
            if($order->payment_mode == 3  || $order->payment_mode == 5)
            {
                $CustomerWallet_Deposit = $OrderGrandTotal;
            }
            if($order->payment_mode == 4)
            {
                $CustomerWallet_Deposit = $order->order_wallet_amount;
            }
        }
        $decimalValue = (double)  $CustomerWallet_Deposit;
        return $decimalValue;
    }

    public static function FN_GetSellerPlatformFee($order){

        $TSellerPlatformFee = DB::table('sys_configuration')
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

}


