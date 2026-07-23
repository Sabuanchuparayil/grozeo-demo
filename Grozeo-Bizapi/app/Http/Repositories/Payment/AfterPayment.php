<?php

namespace App\Http\Repositories\Payment;

use App\Models\Cart;
use App\Models\BlockedItems;
use Illuminate\Support\Facades\DB;
use BackOffice\Models\BranchInventory;

class AfterPayment
{

    public static function minusStock($customer_id, $order_id = null)
    {
        return (new static)->reduceStockNo($customer_id, $order_id);
    }
    /**
     * Reduce stock count when transaction is completed.
     *
     * @param string $pay
     * @return json
     */
    private function reduceStockNo($customer_id, $order_id = null)
    {

        $blockedItem = $this->getStockBlocked($customer_id, $order_id)
            ->toArray();
        $this->getBranchInventory($blockedItem, $order_id);

        return $customer_id;
    }
    /**
     * Get product based on order id .
     *
     * @param string $orderId
     * @return \Illuminate\Support\Collection
     */
    private function getStockBlocked($customer_id = '', $order_id = null)
    {
        $query = BlockedItems::where('customer_id', $customer_id);
        if ($order_id) {
            $query->where('order_id', $order_id);
        }
        return $query->select('item_id', 'branch_id', 'count')
            ->get();
    }

    private function getBranchInventory(array $blockedItem, $order_id = null)
    {
        DB::transaction(function () use ($blockedItem, $order_id) {
            foreach ($blockedItem as $item) {
                $branchInventoy = BranchInventory::where('stit_id', $item['item_id'])
                    ->where('branch_id', $item['branch_id'])
                    ->lockForUpdate()
                    ->select('item_count')
                    ->first();
                if ($branchInventoy && $branchInventoy->item_count >= $item['count']) {
                    $reduceCount = $branchInventoy->item_count - $item['count'];
                    BranchInventory::where('stit_id', $item['item_id'])
                        ->where('branch_id', $item['branch_id'])
                        ->where('item_count', '>=', $item['count'])
                        ->update(['item_count' => $reduceCount]);
                }
            }
            if ($order_id && count($blockedItem) > 0) {
                $productIds = array_column($blockedItem, 'item_id');
                Cart::where('cart_customer_id', auth_user()->cust_id ?? 0)
                    ->whereIn('cart_product_id', $productIds)
                    ->delete();
            }
            $this->removeBlockedItem($order_id);
        });
    }

    private function removeBlockedItem($order_id = null)
    {
        $customer_id = auth_user()->cust_id ?? 0;
        $query = BlockedItems::where('customer_id', $customer_id);
        if ($order_id) {
            $query->where('order_id', $order_id);
        }
        return $query->delete();
    }

    public function clearItems()
    {
        Cart::where('cart_customer_id', auth_user()->cust_id)
            ->delete();
    }
}
