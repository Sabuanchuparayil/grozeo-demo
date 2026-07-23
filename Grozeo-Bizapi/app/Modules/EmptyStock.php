<?php 

namespace App\Modules;

use App\Modules\OrderCollect;
use App\Exceptions\MsgException;

class EmptyStock
{
    private $order;

    public function __construct()
    {
        $this->order = new OrderCollect;
    }

   public function emptyStockOrder(array $request, array $cart, $hasRestService = 0)
   {
        if(count($cart) == 0)
        {
            throw new MsgException("Empty cart emptyStockOrder");
        }
        return $this->order->createOrder($cart, $request, $hasRestService); 
   } 

}