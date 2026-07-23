<?php

namespace App\Listeners;

use App\Events\OrderHistory;
use App\Modules\Order\OrderHistoryStore;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderHistoryListener
{
    /**
     * Order History
     *
     * @var OrderHistory
     */
    private $orderHistory;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(OrderHistoryStore $orderHistory)
    {
        $this->orderHistory = $orderHistory;
    }

    /**
     * Handle the event.
     *
     * @param  OrderHistory  $event
     * @return void
     */
    public function handle(OrderHistory $event)
    {
        $this->orderHistory->storeHistory($event->order_id, $event->order_status);
    }
}
