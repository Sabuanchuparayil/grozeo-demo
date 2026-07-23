<?php

namespace BackOffice\Tasks;

use BackOffice\Models\CpdOrder;
use Illuminate\Support\Facades\DB;
use BackOffice\Status\CpdOrderStatus;
use BackOffice\Models\BoyOrderRequest;
use BackOffice\Status\BoyOrderRequestStatus;

class ClearCpdOrder
{
    /**
     * Timeout frequency in minutes.
     * 
     * @var int
     */
    protected const TIME_OUT_FREQUENCY = 3;
    
    /**
     * Clear pending cpd orders.
     *
     * @return void
     */
    public function __invoke()
    {
        $orders = $this->getPendingOrders();
        DB::transaction(function () use ($orders) {
            $this->updateRequests($orders->pluck('id'));
            $this->revertOrderStatus($orders->pluck('order_id'));
        });
    }

    /**
     * Get the pending orders assigned to godown boys.
     *
     * @return \Illuminate\Database\Eloquent\Collection|BoyOrderRequest[]
     */
    protected function getPendingOrders()
    {
        return BoyOrderRequest::select('id', 'order_id')
            ->where('status', BoyOrderRequestStatus::REQUEST_SENT)
            ->where('created_at', '<', now()->subMinutes(static::TIME_OUT_FREQUENCY))
            ->get();
    }

    /**
     * Update pending order status as timed out.
     *
     * @param array $orderIds
     * @return void
     */
    protected function updateRequests($orderIds)
    {
        BoyOrderRequest::whereIn('id', $orderIds)
            ->update(['status' => BoyOrderRequestStatus::TIMED_OUT]);
    }

    /**
     * Revert Cpd order status to manual queued.
     *
     * @param array $orderIds
     * @return void
     */
    protected function revertOrderStatus($orderIds)
    {
        CpdOrder::whereIn('order_no', $orderIds)
            ->update(['order_status' => CpdOrderStatus::MANUAL_QUEUED]);

    }
}
