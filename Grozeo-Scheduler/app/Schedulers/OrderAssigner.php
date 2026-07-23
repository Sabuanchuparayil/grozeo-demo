<?php

namespace App\Schedulers;

use Carbon\Carbon;
use App\Models\{
    GodownBoy,
    TransferOrder,
    BoyOrderRequest
};
use App\Status\TransferOrderStatus;
use App\Traits\CanSendNotificationsToBoy;
use Illuminate\Support\Facades\Log;

abstract class OrderAssigner
{
    use CanSendNotificationsToBoy;

    /**
     * Timed out status code.
     *
     * @var int
     */
    public const TIMED_OUT = 4;

    /**
     * Request sent status code.
     *
     * @var int
     */
    public const REQUEST_SENT = 1;

    /**
     * List of open orders.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $openOrders;

    /**
     * List of available godown boys.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $godownBoys;

    /**
     * List of temporarily assigned orders.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $tempAssignedOrders;

    /**
     * Process the open orders.
     *
     * @return void
     */
    protected function processOrders()
    {
        foreach ($this->openOrders as $order) {
            $boy = $this->findFreeGodownBoy($order);

            if (!$boy) {
                /*$orderGraceTime = Carbon::parse($order->fsto_openingtime)
                    ->addMinutes(config('app.manual_assignment_grace_time'));
                if (now()->gt($orderGraceTime)) {*/
                    $this->assignManualScheduling($order);
                /*}*/
                continue;
            }
            $this->assignOrderToBoy($order, $boy);
        }
    }

    /**
     * Find a godown boy who is free.
     *
     * @param \Illuminate\Database\Eloquent\Model $order
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function findFreeGodownBoy($order)
    {
        $boys = $this->godownBoys[$order->fsto_source] ?? [];
        foreach ($boys as $boy) {
            $boy->refresh();
            if (
                $boy->is_polled == 1
                || (isset($this->tempAssignedOrders[$boy->id])
                    && $this->tempAssignedOrders[$boy->id]->contains('order_pk_id', $order->fsto_id))
            ) {
                continue;
            }
            return $boy;
        }

        return null;
    }

    /**
     * Get the open orders.
     *
     * @return \Illuminate\Support\Collection
     */
    abstract protected function getOpenOrders();

    /**
     * Get the list of godown boys grouped by branch.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getGodownBoys()
    {
        $boys = GodownBoy::select('id', 'branch_id', 'fcm_id','name')
            ->where('has_open_orders', 0)
            ->where('is_allowAutoSchedule', 1)
            ->where('is_offline', 0)
            ->where('is_polled', 0)
            ->where('status', 1)
            ->get();
        return $boys->groupBy('branch_id');
    }

    /**
     * Get all temporarily assigned (requested) orders.
     *
     * @param array $orderIds
     * @return \Illuminate\Support\Collection
     */
    protected function getTempAssignedOrders($orderIds)
    {
        return BoyOrderRequest::select('id', 'boy_id', 'branch_id', 'order_id','order_pk_id')
            ->whereIn('order_pk_id', $orderIds)
            ->get()
            ->groupBy('boy_id');
    }

    /**
     * Assign the order for manual scheduling
     *
     * @param \Illuminate\Database\Eloquent\Model $order
     * @return void
     */
    public function assignManualScheduling($order)
    {
        if($order->fsto_status != TransferOrderStatus::TO_MANUALLY_ASSIGN)
        {
            $order->update(['fsto_status' => TransferOrderStatus::TO_MANUALLY_ASSIGN]);
            TransferOrder::reverseStatusUpdate($order->fsto_id,TransferOrderStatus::TO_MANUALLY_ASSIGN);
        }
    }

    /**
     * Assign an order to a godown boy.
     *
     * @param \Illuminate\Database\Eloquent\Model $order
     * @param \Illuminate\Database\Eloquent\Model $boy
     * @return void
     */
    public function assignOrderToBoy($order, $boy)
    {
        $orderRequest = BoyOrderRequest::create([
            'boy_id' => $boy->id,
            'branch_id' => $boy->branch_id,
            'order_id' => $order->fsto_uid,
            'status' => static::REQUEST_SENT,
            'order_pk_id' => $order->fsto_id
        ]);

        $order->update(['fsto_status' => TransferOrderStatus::GODOWN_BOY_POLLED,'fsto_polled_boy' => $boy->id, 'fsto_polled_request_id' => $orderRequest->id ]);

        // TransferOrder::reverseStatusUpdate($order->fsto_id,TransferOrderStatus::GODOWN_BOY_POLLED);

        $boy->update(['is_polled' => 1, 'poll_expire_at' => now()->addMinutes(3)]);

        $this->sendNotificationToBoy(
            $order->fsto_uid,
            $boy->fcm_id,
            $orderRequest->id,
            -1,
            false,
            $order->fsto_id
        );
    }

    public function clearGodownBoys()
    {
        GodownBoy::where('is_polled', 1)
            ->where('poll_expire_at', '<=', now())
            ->update(['is_polled' => 0]);
    }

     public function clearScheduledOrders()
    {
        //DB::enableQueryLog();
        $scheduledOrders = TransferOrder::select('fsto_id', 'fsto_uid', 'fsto_source', 'fsto_sourcetype', 'fsto_openingtime')
            ->where('fsto_status', TransferOrderStatus::SCHEDULED_ORDER)
            ->whereIn('fsto_ordertype', [0,1,2])
            ->where('fsto_openingtime', '<=', now()->format('Y-m-d H:i:s'))
            ->get();
        foreach ($scheduledOrders as $scheduledOrder) {

            TransferOrder::reverseStatusUpdate($scheduledOrder->fsto_id,TransferOrderStatus::TO_MANUALLY_ASSIGN);
        }
    }

}
