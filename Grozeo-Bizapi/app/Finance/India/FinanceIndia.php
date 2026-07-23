<?php
namespace App\Finance\India;

use Illuminate\Support\Facades\DB;

use App\Models\{
    Order,
    Branch,
    FinanceAutopostingValues
};

class FinanceIndia
{
    function __construct(){}

    public function FinanceAutopostings($data, $type)
    {
        switch($type)
        {
            case "coupon":
                $this->couponAutopostings($data);
            break;
            case "collectorder":
                $this->collectorderAutopostings($data);
            break;
            case "ordercancel":
                $this->ordercancelAutopostings($data);
            break;
            case "restaurant":
                $this->restaurantAutopostings($data);
            break;
            case "paymentgateway":
                $this->paymentgatewayAutopostings($data);
            break;
            case "paymentresult":
                $this->paymentresultAutopostings($data);
            break;
            case "wallet":
                $this->walletAutopostings($data);
            break;
            case "packing":
                $this->packingAutopostings($data);
            break;
            case "courier":
                $this->courierAutopostings($data);
            break;
            
        }
    }

    private function couponAutopostings($data)
    {
        $apUpdate = [];
        $OrderGrandTotal = $data['total'];
        if(round($data['order_roundoff'], 2) < 0)
        {
            $apUpdate['RoundDown'] = abs(round($data['order_roundoff'], 2));
        }
        if(round($data['order_roundoff'], 2) > 0)
        {
            $apUpdate['RoundUp'] = round($data['order_roundoff'], 2);
        }
        if(($data['payment_mode'] == 1) || ($data['payment_mode'] == 4))
        {
            $apUpdate['OrderGrandTotal'] = NULL;
            $apUpdate['OrderGrandTotal_POD'] = $OrderGrandTotal;
            $apUpdate['TSOPOD_PendingCollection'] = $OrderGrandTotal - $data['order_wallet_amount'];
        }
        else
        {
            $apUpdate['OrderGrandTotal'] = $OrderGrandTotal;
            $apUpdate['OrderGrandTotal_POD'] = NULL;
        }
        $autoPosting = FinanceAutopostingValues::where('order_id', $data['order_id'])->update($apUpdate);
    }
    private function collectorderAutopostings($data)
    {
        $autoPostingInsert = [
            'order_id'                      => $data['order_id'],
            'RetailSalePriceinMRP'          => $data['order_mrp_et'],
            'MRP_RRP'                       => $data['order_mrp'],
            'TaxinMRP'                      => ($data['order_mrp'] - $data['order_mrp_et']),
            'OrderGrandTotal'               => $data['total'],
            'TradeDiscount'                 => $data['order_saved_amount'],
            'OrderDeliveryCharges_ODC'      => $data['order_delivery_charge_et'],
            'SellerSalesMargin_SSM'         => $data['order_sales_margin'],
        ];
        if($data['order_roundoff'] < 0)
        {
            $autoPostingInsert['RoundDown'] = abs($data['order_roundoff']);
        }
        if($data['order_roundoff'] > 0)
        {
            $autoPostingInsert['RoundUp'] = $data['order_roundoff'];
        }
        $branchData = Branch::select('br_pgchargeId', 'br_State')->where('br_Id', $data['order_branch_id'])->first();
        $goozeoState = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='GROZEO_STATE'");
        if($branchData->br_State == $goozeoState[0]->cfg_Value)
        {
            $autoPostingInsert['CGSTonSSM'] = round($data['order_sales_margin'] * 0.09, 2);
            $autoPostingInsert['SGSTonSSM'] = round($data['order_sales_margin'] * 0.09, 2);
        }
        else
        {
            $autoPostingInsert['IGSTonSSM'] = round($data['order_sales_margin'] * 0.18, 2);
        }
        if(@auth_user()->primaryAddress->state->st_ID == $goozeoState[0]->cfg_Value)
        {
            $tRCGST = ($data['order_delivery_charge_cgst'] > 0) ? $data['order_delivery_charge_cgst'] : ($data['order_delivery_charge_igst']/2);
            $tRSGST = ($data['order_delivery_charge_sgst'] > 0) ? $data['order_delivery_charge_sgst'] : ($data['order_delivery_charge_igst']/2);
            $autoPostingInsert['CGSTonODC'] = $tRCGST;
            $autoPostingInsert['SGSTonODC'] = $tRSGST;
        }
        else
        {
            $tRIGST = ($data['order_delivery_charge_igst'] > 0) ? $data['order_delivery_charge_igst'] : ($data['order_delivery_charge_cgst'] + $data['order_delivery_charge_sgst']);
            $autoPostingInsert['IGSTonODC'] = $tRIGST;
        }
        $autoPosting = FinanceAutopostingValues::create($autoPostingInsert);
    }
    private function ordercancelAutopostings($data)
    {
        $autoPostingVals = FinanceAutopostingValues::select('OrderGrandTotal', 'OrderGrandTotal_POD', 'TSOPOD_PendingCollection')->where('order_id', $data['order_id'])->first();
            
        $autoUpdate = [
            'TSOPODPendingCollection_Cancelled' => $autoPostingVals->TSOPOD_PendingCollection,
            'is_cancelled'                      => '1',
            'order_payment_mode'                => $data['payment_mode']
        ];
        $autoPosting = FinanceAutopostingValues::where('order_id', $data['order_id'])->update($autoUpdate);
    }
    private function paymentgatewayAutopostings($data)
    {
        $paymentgateway = $data['paymentgateway'];
        if(config("paymentgateway.{$paymentgateway}.tax") == 'inclusive')
        {
            $mdrTax = $data['fees'] - $data['tax'];
            $apUpdate['MerchantDiscountRate_MDR'] = $mdrTax;
        }
        else
        {
            $apUpdate['MerchantDiscountRate_MDR'] = $data['fees'];
        }
        if(@config("paymentgateway.{$paymentgateway}.b_type") == 'intra')
        {
            $apUpdate['CGSTInputonMDR'] = $data['tax']/2;
            $apUpdate['SGSTInputonMDR'] = $data['tax']/2;
        }
        else
        {
            $apUpdate['IGSTInputonMDR'] = $data['tax'];
        }
        $autoPosting = FinanceAutopostingValues::where('order_id', $data['order_id'])->update($apUpdate);
    }
    private function paymentresultAutopostings($data)
    {
        $goozeoState = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='GROZEO_STATE'");
        $branchData = Branch::select('br_pgchargeId', 'br_State')->where('br_Id', $data['order_branch_id'])->first();
        if($branchData->br_pgchargeId > 0)
        {
            $pgPerc = DB::select("SELECT pgChargePercentage FROM pgcharge_master WHERE pgChargeId={$branchData->br_pgchargeId}");
        }
        else
        {
            $pgPerc = DB::select("SELECT pgChargePercentage FROM pgcharge_master WHERE pgChargeIsDefault=1 AND pgChargeStatus=1");
        }
        $SellerMDR_SMDR = 0;
        $CGSTonSMDR = 0;
        $SGSTonSMDR = 0;
        $IGSTonSMDR = 0;
        if($pgPerc[0]->pgChargePercentage)
        {
            $SellerMDR_SMDR = round($data['total']*($pgPerc[0]->pgChargePercentage/100), 2);
        }

        if($branchData->br_State == $goozeoState[0]->cfg_Value)
        {
            if($SellerMDR_SMDR)
            {
                $CGSTonSMDR = round($SellerMDR_SMDR * 0.09, 2);
                $SGSTonSMDR = round($SellerMDR_SMDR * 0.09, 2);
            }
        }
        else
        {
            if($SellerMDR_SMDR)
            {
                $IGSTonSMDR = round($SellerMDR_SMDR * 0.18, 2);
            }
        }
        if($data['payment_mode'] == 2)
        {
            $autoPosting = FinanceAutopostingValues::where('order_id', $data['order_id'])->update([
                'SellerMDR_SMDR'    => $SellerMDR_SMDR,  
                'CGSTonSMDR'        => $CGSTonSMDR,  
                'SGSTonSMDR'        => $SGSTonSMDR,  
                'IGSTonSMDR'        => $IGSTonSMDR 
            ]);
        }
    }
    private function walletAutopostings($data)
    {
        $autoUpdate['CustomerWallet_Deposit'] = $data['amount'];
        $autoUpdate['CustomerWalletDeposit_Cancelled'] = $data['amount'];
        $autoPosting = FinanceAutopostingValues::where('order_id', $data['order_id'])->update($autoUpdate);
    }
    private function restaurantAutopostings($data)
    {
        $order = Order::where('order_id', $data['order_id'])->first();
        if($order)
        {
            $params = [
                'RetailSalePrice_Restaurant'    => 0,
                'IGSTonRSP_Restaurant'          => 0,
                'CGSTonRSP_Restaurant'          => 0,
                'SGSTonRSP_Restaurant'          => 0,
                'UTGSTonRSP_Restaurant'         => 0,
                'CConRSP_Restaurant'            => 0,
                'GSTonRSP_RestaurantTotal'      => 0,
                'RetailSalePrice'               => 0,
                'CConRSP_Final'                 => 0,
                'IGSTonRSP_Final'               => 0,
                'CGSTonRSP_Final'               => 0,
                'SGSTonRSP_Final'               => 0,
                'UTGSTonRSP_Final'              => 0,
                'TCSIGST'                       => 0,
                'TCSCGST'                       => 0,
                'TCSSGST'                       => 0,
                'TCSUTGST'                      => 0,
                'TCSGSTTotal'                   => 0,
            ];
            if(($order->payment_mode == 1) || ($order->payment_mode == 4))
            {
                $params['OrderGrandTotal'] = NULL;
                $params['OrderGrandTotal_POD'] = $order->total;
                $params['TSOPOD_PendingCollection'] = $order->total - $order->order_wallet_amount;
                $params['CustomerWallet_Withdrawal_POD'] = $order->order_wallet_amount;
            }
            else
            {
                $params['CustomerWallet_Withdrawal'] = $order->order_wallet_amount;
            }
            $gstOthers = 0;
            foreach ($order->orderItems as $item)
            {
                if(@$item->item->productCategory->hasRestaurantService == 1)
                {
                    $params['RetailSalePrice_Restaurant'] += ($item->order_item_mrp_et - $item->order_item_seller_discount);
                    $params['IGSTonRSP_Restaurant'] += $item->order_item_igst;
                    $params['CGSTonRSP_Restaurant'] += $item->order_item_cgst;
                    $params['SGSTonRSP_Restaurant'] += $item->order_item_sgst;
                    $params['UTGSTonRSP_Restaurant'] += $item->order_item_ugst;
                    $params['CConRSP_Restaurant'] += $item->order_item_cess;
                    $params['GSTonRSP_RestaurantTotal'] += $item->order_item_gst;
                }
                else
                {
                    $params['RetailSalePrice'] += ($item->order_item_mrp_et - $item->order_item_seller_discount);
                    $params['IGSTonRSP_Final'] += $item->order_item_igst;
                    $params['CGSTonRSP_Final'] += $item->order_item_cgst;
                    $params['SGSTonRSP_Final'] += $item->order_item_sgst;
                    $params['UTGSTonRSP_Final'] += $item->order_item_ugst;
                    $params['CConRSP_Final'] += $item->order_item_cess;
                    $params['TCSIGST'] += $item->order_item_tcs_igst;
                    $params['TCSCGST'] += $item->order_item_tcs_cgst;
                    $params['TCSSGST'] += $item->order_item_tcs_sgst;
                    $params['TCSUTGST'] += $item->order_item_tcs_utgst;
                    $params['TCSGSTTotal'] += $item->order_item_tcs_gst;
                    $gstOthers += $item->order_item_gst;

                }
            }
            if($params['RetailSalePrice_Restaurant'] > 0)
            {
                $restTot = $params['RetailSalePrice_Restaurant'] + $params['GSTonRSP_RestaurantTotal'];
                $roundRestTot = round($restTot);
                $restTotRound = round(($restTot - $roundRestTot), 2);
                $restTotRoundABS = abs($restTotRound);
                if($restTotRound > 0)
                {
                    $params['RoundDown_RestaurantSales'] = abs($restTotRound);
                    $params['RoundUp_RestaurantSales'] = 0;
                }
                if($restTotRound < 0)
                {
                    $params['RoundUp_RestaurantSales'] = abs($restTotRound);
                    $params['RoundDown_RestaurantSales'] = 0;
                }
            }
            if($params['RetailSalePrice'] > 0)
            {
                $otherTot = $params['RetailSalePrice'] + $gstOthers + $params['CConRSP_Final'];
                $roundOtherTot = round($otherTot);
                $otherTotRound = round(($otherTot - $roundOtherTot), 2);
                $otherTotRoundABS = abs($otherTotRound);
                if($otherTotRound > 0)
                {
                    $params['RoundDown_General'] = abs($otherTotRound);
                    $params['RoundUp_General'] = 0;
                }
                if($otherTotRound < 0)
                {
                    $params['RoundUp_General'] = abs($otherTotRound);
                    $params['RoundDown_General'] = 0;
                }
            }
            $updateValues = FinanceAutopostingValues::where('order_id', $data['order_id'])->update($params);
        }
    }
    private function packingAutopostings($data)
    {
    }
    private function courierAutopostings($data)
    {
        if($data['payment_mode'] == 1 || $data['payment_mode'] == 4)
        {
            $favValues = FinanceAutopostingValues::select('OrderGrandTotal_POD', 'CustomerWallet_Withdrawal_POD')->where('order_id', $data['order_id'])->first();
            $favUpdate = FinanceAutopostingValues::where('order_id', $data['order_id'])->update([
                'CourierCollection_COD'                 => @$favValues->OrderGrandTotal_POD - @$favValues->CustomerWallet_Withdrawal_POD,
                'GrozeoLogisticsPartnerCollection_COD'  => @$favValues->OrderGrandTotal_POD - @$favValues->CustomerWallet_Withdrawal_POD,
                'TenantCollection_COD'                  => @$favValues->OrderGrandTotal_POD - @$favValues->CustomerWallet_Withdrawal_POD
            ]);
        }
    }
}