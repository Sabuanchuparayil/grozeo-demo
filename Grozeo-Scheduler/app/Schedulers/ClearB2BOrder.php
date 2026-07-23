<?php

namespace App\Schedulers;

use App\Models\B2bOrder;
use Illuminate\Support\Facades\DB;
use App\Status\B2bOrderStatus;
use App\Models\BoyOrderRequest;
use App\Status\BoyOrderRequestStatus;
use Illuminate\Support\Facades\Log;

class ClearB2BOrder
{
    /**
     * Timeout frequency in minutes.
     *
     * @var int
     */
    protected const TIME_OUT_FREQUENCY = 3;

    /**
     * Clear pending B2B orders.
     *
     * @return void
     */
    public function __invoke()
    {
        try
        {
            $orders = $this->getPendingOrders();
            DB::transaction(function () use ($orders) {
                $this->updateRequests($orders->pluck('id'));
                $this->revertOrderStatus($orders->pluck('order_id'));
            });
        }
        catch (\Exception $e)
        {
            info("BranchStatusUpdate ERROR => ".$e->getMessage());
        }
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
        B2bOrder::whereIn('bbso_SONumber', $orderIds)
            ->update(['status_id' => B2bOrderStatus::MANUAL_ASSIGNMENT]);
    }
}
