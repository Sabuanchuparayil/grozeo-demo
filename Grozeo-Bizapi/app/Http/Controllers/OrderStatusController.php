<?php

namespace App\Http\Controllers;

use App\Http\Repositories\Order\OrderStatusRepository;

class OrderStatusController extends Controller
{
    protected OrderStatusRepository $orderStatus;

    public function __construct(OrderStatusRepository $orderStatus)
    {
        $this->orderStatus = $orderStatus;
    }

    public function get(string $id)
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->orderStatus->get($id),
        ]);
    }
}
