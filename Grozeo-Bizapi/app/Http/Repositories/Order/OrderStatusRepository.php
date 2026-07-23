<?php

namespace App\Http\Repositories\Order;

use App\Models\Order;
use App\Models\OrderStatus;

class OrderStatusRepository
{
    protected $order;

    protected $orderStatus;

    public function __construct(Order $order, OrderStatus $orderStatus)
    {
        $this->order = $order;
        $this->orderStatus = $orderStatus;
    }

    /**
     * Retrieve status for an order.
     *
     * @param string $id
     * @return \Illuminate\Support\Collection
     */
    public function get($id)
    {
        $this->checkOrderBelongsToCustomer($id);
        return $this->orderStatus
                    ->where('stat_order_id', $id)
                    ->get();
    }

    /**
     * Check an order belongs to a customer
     *
     * @param string $id
     * @throws \Exception
     * @return void
     */
    public function checkOrderBelongsToCustomer($id)
    {
        $orderExists = $this->order
                    ->where('order_customer_id', auth_user()->cust_id)
                    ->where('order_order_id', $id)
                    ->exists();
        if(!$orderExists) {
            throw new \Exception("Invalid Order", 400);
        }
    }

}
