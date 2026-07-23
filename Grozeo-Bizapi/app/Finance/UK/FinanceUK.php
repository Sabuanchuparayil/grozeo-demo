<?php
namespace App\Finance\UK;

use Illuminate\Support\Facades\DB;

use App\Models\{
    Order,
    Branch,
    FinanceAutopostingValues
};

class FinanceUK
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

        $autoPostingInsert['VATonSSM'] = round($data['order_sales_margin'] * 0.2, 2);
        $autoPostingInsert['VATonODC'] = $data['order_delivery_charge_gst'];

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
        $apUpdate['VATInputonMDR'] = $data['tax'];
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
        $VATonSMDR = 0;
        if($pgPerc[0]->pgChargePercentage)
        {
            $SellerMDR_SMDR = round($data['total']*($pgPerc[0]->pgChargePercentage/100), 2);
            $VATonSMDR = round($SellerMDR_SMDR * 0.20, 2);
        }
        if($data['payment_mode'] == 2)
        {
            $autoPosting = FinanceAutopostingValues::where('order_id', $data['order_id'])->update([
                'SellerMDR_SMDR'    => $SellerMDR_SMDR,  
                'VATonSMDR'         => $VATonSMDR
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
                'RetailSalePrice'   => 0,
                'VATRSP_Final'      => 0,
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
                $params['RetailSalePrice'] += ($item->order_item_mrp_et - $item->order_item_seller_discount);
                $params['VATRSP_Final'] += $item->order_item_gst;
                $gstOthers += $item->order_item_gst;
            }
            if($params['RetailSalePrice'] > 0)
            {
                $otherTot = $params['RetailSalePrice'] + $gstOthers;
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