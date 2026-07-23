<?php

namespace App\Listeners;

use App\Events\DelayedOrderActions;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Repositories\DelayedOrderUpdateRepository;

class DelayedOrderActionsListener
{
    /**
     * Order History
     *
     * @var OrderHistory
     */
    private $delayActions;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(DelayedOrderUpdateRepository $delayActions)
    {
        $this->delayActions = $delayActions;
    }

    /**
     * Handle the event.
     *
     * @param  DelayedOrderActions  $event
     * @return void
     */
    public function handle(DelayedOrderActions $event)
    {
        $this->delayActions->delayedOrderActions($event->orderID, $event->type, $event->delay);
    }
}
