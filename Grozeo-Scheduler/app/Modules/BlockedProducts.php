<?php

namespace App\Modules;

use App\Models\Cart;
use App\Models\BlockedItems;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\ReduceStock;

class BlockedProducts
{
    const MARKED_FOR_DELIVERY = 1;

    private $block;

    private $cart;

    public function __construct()
    {
        $this->block = new BlockedItems;
        $this->cart = new Cart;
    }

    public static function markedForDelivery($order_id, $customer_id)
    {
        return (new static)->update($order_id, $customer_id);
    }

    private function update($order_id, $customer_id)
    {
        DB::transaction(function () use($order_id, $customer_id) {
            $this->block->where('order_id', $order_id)
                            ->update([
                                "markedfordelivery" => static::MARKED_FOR_DELIVERY
                            ]);
            $this->cart->where('cart_customer_id', $customer_id)
                    ->delete();
            });
        return true;
    }

    public static function unsetMarkedItems($order_id)
    {
        return (new static)->remove($order_id);
    }

    private function remove($order_id)
    {
        return ReduceStock::unblockItems($order_id);
        /*
        return $this->block->where('order_id', $order_id)
                            ->where('markedfordelivery', 0)
                            ->delete();
        */
    }
}
