<?php

namespace App\Listeners;

use App\Events\SendNotifications;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Repositories\SendNotificationsRepository;

class SendNotificationsListener
{
    /**
     * Order History
     *
     * @var OrderHistory
     */
    private $sendNotification;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(SendNotificationsRepository $sendNotification)
    {
        $this->sendNotification = $sendNotification;
    }

    /**
     * Handle the event.
     *
     * @param  SendNotifications  $event
     * @return void
     */
    public function handle(SendNotifications $event)
    {
        $this->sendNotification->sendNotifications($event->orderID, $event->eventID);
    }
}
