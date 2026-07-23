<?php

namespace App\Http\Repositories\Order;

interface OrderRepositoryInterface
{
    public function updateOrderItemsCoupon($items, $discountAmount, $coupon, $process);
    public function updateOrderCoupon($order, $updatedItems);
    public function updateOrderItemsPacking($items, $discountAmount);
    public function updateOrderPacking($orders, $updateItemOrders);
}