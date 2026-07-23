<?php

namespace App\Http\Repositories\Order;

use App\Models\OrderReturns;

class OrderReturnsRepository
{
    protected $orderReturns;

    public function __construct(OrderReturns $orderReturns)
    {
        $this->orderReturns = $orderReturns;
    }

    public function create($data)
    {
        $data['customer_id'] = auth_user()->cust_id;
        return $this->orderReturns->updateOrCreate(
            ['order_id' => $data['order_id']],
            $data
        );
    }

}
