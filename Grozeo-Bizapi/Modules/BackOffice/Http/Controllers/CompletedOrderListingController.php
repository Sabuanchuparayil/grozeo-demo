<?php

namespace BackOffice\Http\Controllers;

use BackOffice\Models\BoyOrder;
use App\Http\Responses\SuccessWithData;

class CompletedOrderListingController
{
    protected $order;

    protected const COMPLETED = 4;

    public function __construct(BoyOrder $order)
    {
        $this->order = $order;
    }

    public function __invoke()
    {
        $orders = $this->order
            ->select('id', 'boy_id', 'order_id', 'accepted_time')
            ->whereDate('completed_time', today())
            ->where('boy_id', auth_user()->id)
            ->where('status', static::COMPLETED)
            ->get();
        return new SuccessWithData($orders);
    }
}
