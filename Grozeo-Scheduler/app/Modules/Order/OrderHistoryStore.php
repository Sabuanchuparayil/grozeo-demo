<?php

namespace App\Modules\Order;

use App\Models\OrderHistory;

class OrderHistoryStore
{
    private $history;

    public function __construct()
    {
        $this->history = new OrderHistory();
    }

    public function storeHistory($order_id, $status)
    {
        return $this->history->create([
            'order_id' => $order_id,
            'order_status' => $status,
        ]);
    }
    
}