<?php

namespace BackOffice\Http\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Order;
use BackOffice\Status\CustomerOrderStatus;
use App\Models\WalletTransaction;
use BackOffice\Models\BranchInventory;
use App\Events\OrderHistory;
use BackOffice\Models\TransferOrder;
use BackOffice\Status\TransferOrderStatus;
use BackOffice\Models\TransferOrderDetails;

class CheckTolerance {

    public static function validateOrderAmount($order_id, $fstoId, $partnerType) {
        return (new static)->compareOrderPrice($order_id, $fstoId, $partnerType);
    }
    
    public static function updateIncompleteItemAmount($order_id, $fstoId) {
        return (new static)->getPackedPrice($fstoId, $order_id);
    }

    private function compareOrderPrice($order_id, $fstoId, $partnerType) {
        $toleranceDetailsAmt = DB::table('retaline_tolerance_master')->select('rtm_id', 'rtm_value', 'rtm_percentage')->where('rtm_default', 1)->first();
        $toleranceValueAmt = $toleranceDetailsAmt->rtm_value;
        $order = Order::where('order_id', $order_id)
                ->first();
        $toleranceValuePerc = $order->total * $toleranceDetailsAmt->rtm_percentage / 100;
        $toleranceValueDiff = $this->price_diff($toleranceValueAmt, $toleranceValuePerc);
        if ($toleranceValueDiff > 0) {
            $toleranceValue = $toleranceValueAmt;
        } else {
            $toleranceValue = $toleranceValuePerc;
        }

        DB::table('b2corder_tolerance_log')->insert(
                [
                    'otl_order_id' => $order_id,
                    'tolerance_id' => $toleranceDetailsAmt->rtm_id,
                    'tolerance_amt_value' => $toleranceDetailsAmt->rtm_value,
                    'tolerance_percentage_value' => $toleranceDetailsAmt->rtm_percentage,
                    'tolerance_value' => $toleranceValue,
                    'createdOn' => date('Y-m-d H:i:s')
                ]
        );
        $currentAmount = DB::table('retaline_customer_order')->select('subtotal')->where('order_id', $order_id)->first();

        $packedPrice = $this->getPackedPrice($fstoId, $order_id);
        $priceDifference = $this->price_diff($currentAmount->subtotal, $packedPrice);
        switch ($priceDifference) {
            case 0:
                return true;
                break;
            case ($priceDifference > 0):
                $toleranceDiffless = $this->price_diff($toleranceValue, $priceDifference);
                if ($toleranceDiffless >= 0) {
                    $this->creditCustomerWallet($order_id, $priceDifference, $partnerType);
                    return true;
                } else {
                    $this->toleranceExceedActions($order_id, $fstoId);
                    return false;
                }

                return true;
                break;
            case ($priceDifference < 0):
                $toleranceDiff = $this->price_diff($toleranceValue, abs($priceDifference));
                if ($toleranceDiff >= 0) {
                    $this->debitCustomerWallet($order_id, $priceDifference, $partnerType);
                    return true;
                } else {
                    $this->toleranceExceedActions($order_id, $fstoId);
                    return false;
                }
                break;
        }
    }

    private function getPackedPrice($fstoId, $orderId) {
        $itemDetails = DB::table('retaline_customer_order_items')
                ->select('item_sales_price', 'item_order_qty', 'item_product_id')//sum(fstro_ItemSPincTax*fsto_pkdQty) as amt,
                ->where('customer_order_id', '=', $orderId)
                ->get();
        $itemAmount = 0;

        foreach ($itemDetails as $itemDetail) {

            $fstoDetails = DB::table('finascop_stock_transfer_order_details')
                    ->select('fsto_stockValue', 'fstro_ItemSPincTax', 'fsto_pkdQty', 'fsto_ItemId')//sum(fstro_ItemSPincTax*fsto_pkdQty) as amt,
                    ->where('fsto_id', '=', $fstoId)
                    ->where('fsto_ItemId', '=', $itemDetail->item_product_id)
                    ->first();

            $subProducts = DB::table('finascop_stock_itemmaster')->select('stit_id', 'stit_ConvertCalcRate', 'stit_GST', 'stit_ParentItemId')->where('stit_id', $itemDetail->item_product_id)->first();
            if (($subProducts->stit_ConvertCalcRate != $fstoDetails->fsto_stockValue) && ($subProducts->stit_ParentItemId > 0) && ($fstoDetails->fsto_stockValue > 0)) {
                $actualQty = $subProducts->stit_ConvertCalcRate * $fstoDetails->fsto_pkdQty;
                $difference = round(($fstoDetails->fsto_stockValue - $actualQty), 2);
                $convertedWeight = round(($difference / $subProducts->stit_ConvertCalcRate), 2);
                $newQuantity = round(($fstoDetails->fsto_pkdQty + $convertedWeight), 2);
                $fstro_ItemPackedSPincTax = $fstoDetails->fstro_ItemSPincTax * $newQuantity;
                $itemAmount += $fstoDetails->fstro_ItemSPincTax * $newQuantity;
                $packedQty = $newQuantity;
            } else {
                $fstro_ItemPackedSPincTax = $fstoDetails->fstro_ItemSPincTax * $fstoDetails->fsto_pkdQty;
                $itemAmount += $fstoDetails->fstro_ItemSPincTax * $fstoDetails->fsto_pkdQty;
                $packedQty = $fstoDetails->fsto_pkdQty;
            }

            TransferOrderDetails::where('fsto_id', $fstoId)->where('fsto_ItemId', $itemDetail->item_product_id)
                    ->update(['fstro_ItemPackedSPincTax' => $fstro_ItemPackedSPincTax, 'fstro_updatedOn' => date('Y-m-d H:i:s'), 'fsto_pkdQty' => $packedQty]);
        }


        return $itemAmount;
    }

    private function price_diff($v1, $v2) {
        $diff = $v1 - $v2;
        return $diff;
    }

    private function creditCustomerWallet($order_id, $priceDifference, $partnerType) {
        $orderDetails = DB::table('retaline_customer_order')
                ->selectRaw('order_id,order_customer_id,payment_mode,total,order_wallet_amount,payment_mode,order_order_id,order_branch_id')
                ->where('order_id', '=', $order_id)
                ->first();
        if ($orderDetails->payment_mode > 1 && $orderDetails->payment_mode < 6) {
            $model = Customer::find($orderDetails->order_customer_id);
            //'1 - for pay on delivery, 2 - for Online Payment, 3 - Wallet, 4 - COD with Wallet,  5 - online with Wallet, 6 - Online on Delivery, 7 - Cash on delivery'
            //$model->cust_walletbalance += ($orderDetails->payment_mode == 2 || $orderDetails->payment_mode == 4 || $orderDetails->payment_mode == 5? $priceDifference : $orderDetails->order_wallet_amount);
            $model->cust_walletbalance += abs($priceDifference);
            $model->save();
            auth_user()->cust_walletbalance = $model->cust_walletbalance;
            $branch = DB::table('finascop_branch')->select('br_Name', 'br_storeGroup')->where('br_ID', $orderDetails->order_branch_id)->first();
            $branch_name = @$branch->br_Name;
            $info = "Order {$orderDetails->order_order_id} from {$branch_name} Cancelled by {$partnerType} due to item(s) unavailability";

            $openBalQuery = '(SELECT brcw_closingBalance FROM retaline_customer_wallet_transaction tx1 WHERE tx1.cust_id = '.$request->order_customer_id.' AND tx1.brcw_id = (SELECT MAX(tx2.brcw_id) FROM retaline_customer_wallet_transaction tx2 WHERE tx2.cust_id = '.$request->order_customer_id.'))';
            $openBalQueryData = DB::select($openBalQuery);

            WalletTransaction::create([
                'cust_id'               => $orderDetails->order_customer_id,
                'refentry_id'           => $orderDetails->order_id,
                'brcw_SourceType'       => 1,
                'brcw_Amount'           => abs($priceDifference), //-$orderDetails->order_wallet_amount,
                'brcw_AddInfo'          => $info,
                'stiid_barcode'         => 0,
                'brcw_OpeningBalance'   => (@$openBalQueryData[0]->brcw_closingBalance) ? $openBalQueryData[0]->brcw_closingBalance : 0
            ]);
        }
    }

    private function debitCustomerWallet($order_id, $priceDifference, $partnerType) {
        $orderDetails = DB::table('retaline_customer_order')
                ->selectRaw('order_id,order_customer_id,payment_mode,total,order_wallet_amount,payment_mode,order_order_id,order_branch_id')
                ->where('order_id', '=', $order_id)
                ->first();
        if ($orderDetails->payment_mode > 1 && $orderDetails->payment_mode < 6) {
            $model = Customer::find($orderDetails->order_customer_id);
            //'1 - for pay on delivery, 2 - for Online Payment, 3 - Wallet, 4 - COD with Wallet,  5 - online with Wallet, 6 - Online on Delivery, 7 - Cash on delivery'
            //$model->cust_walletbalance += ($orderDetails->payment_mode == 2 || $orderDetails->payment_mode == 4 || $orderDetails->payment_mode == 5? $priceDifference : $orderDetails->order_wallet_amount);
            $model->cust_walletbalance -= abs($priceDifference);
            $model->save();
            auth_user()->cust_walletbalance = $model->cust_walletbalance;
            $branch = DB::table('finascop_branch')->select('br_Name', 'br_storeGroup')->where('br_ID', $orderDetails->order_branch_id)->first();
            $branch_name = @$branch->br_Name;
            $info = "Additional quantity purchase {$orderDetails->order_order_id} from {$branch_name}";

            $openBalQuery = '(SELECT brcw_closingBalance FROM retaline_customer_wallet_transaction tx1 WHERE tx1.cust_id = '.$request->order_customer_id.' AND tx1.brcw_id = (SELECT MAX(tx2.brcw_id) FROM retaline_customer_wallet_transaction tx2 WHERE tx2.cust_id = '.$request->order_customer_id.'))';
            $openBalQueryData = DB::select($openBalQuery);

            WalletTransaction::create([
                'cust_id'               => $orderDetails->order_customer_id,
                'refentry_id'           => $orderDetails->order_id,
                'brcw_SourceType'       => 1,
                'brcw_Amount'           => $priceDifference, //-$orderDetails->order_wallet_amount,
                'brcw_AddInfo'          => $info,
                'stiid_barcode'         => 0,
                'brcw_OpeningBalance'   => (@$openBalQueryData[0]->brcw_closingBalance) ? $openBalQueryData[0]->brcw_closingBalance : 0
            ]);
        }
    }

    private function toleranceExceedActions($order_id, $fstoId) {
        Order::where('order_id', $order_id)
                ->update(['status_id' => CustomerOrderStatus::HOLD_FOR_CUSTOMER_APPROVAL, 'updated_at' => date('Y-m-d H:i:s')]);
        event(new OrderHistory($order_id, CustomerOrderStatus::HOLD_FOR_CUSTOMER_APPROVAL, "Hold for Customer approval"));

        TransferOrder::where('fsto_id', $fstoId)
                ->update(['fsto_status' => TransferOrderStatus::HOLD_FOR_CUSTOMER_APPROVAL, 'fsto_updateon' => date('Y-m-d H:i:s'), 'fsto_isalreadypacked' => 1]);
    }

}
