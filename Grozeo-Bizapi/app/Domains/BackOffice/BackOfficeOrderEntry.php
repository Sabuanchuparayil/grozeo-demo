<?php

namespace App\Domains\BackOffice;

use App\Models\Order;
use App\BackOffice\BO_Order;

class BackOfficeOrderEntry
{
    protected $boOrder;

    protected $order;

    protected $orderItems;

    public function __construct(Order $order, $orderItems)
    {
        $this->boOrder = new BO_Order;
        $this->order = $order;
        $this->orderItems = $orderItems;
    }

    public static function create(Order $order, $orderItems)
    {
        return (new static($order, $orderItems))->addOrderEntry();
    }

    public function addOrderEntry()
    {
        $order = $this->boOrder->create(
            $this->prepareData()
        );

        $order->orderItems()->createMany(
            $this->prepareOrderItems()
        );
    }

    public function prepareData()
    {
        return [
            'order_generated_id' => $this->order->order_order_id,
            'order_user_type' => 'Customer',
            'if_order_for_customer_enum' => 'Yes',
            'order_total_amount' => $this->order->order_total_amount,
            'order_created_on' => now(),
            'order_status' => 1,
            'order_tax' => $this->order->order_total_gst,
            'app' => 'mobile',
        ];
    }

    private function prepareOrderItems()
    {
        return $this->orderItems->map(function ($item) {
            return [
                'order_product_id' => $item->cart_product_id,
                'order_product_qty' => $item->cart_order_qty,
                'order_product_price' => $item->cart_retail_price,
                'order_product_total_price' => $item->cart_price,
                'order_product_tax' => 0,
                'order_product_total_incentive' => 0,
            ];
        })->toArray();
    }

}
