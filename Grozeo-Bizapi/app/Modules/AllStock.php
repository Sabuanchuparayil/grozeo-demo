<?php

namespace App\Modules;

use App\Modules\OrderCollect;
use App\Exceptions\MsgException;

class AllStock
{
    private $order;

    public function __construct()
    {
        $this->order = new OrderCollect;
    }

    public function allStockOrder(array $request, array $cart, $hasRestService = 0)
    {
        if(count($cart) == 0)
        {
            throw new MsgException("Empty cart");
        }
        return $this->order->createOrder($cart, $request, $hasRestService); 
    }
}