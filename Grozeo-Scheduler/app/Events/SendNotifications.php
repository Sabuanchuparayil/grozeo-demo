<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SendNotifications
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Customer Order id
     *
     * @var int
     */
    public $orderID;
    /**
     * Event reference ID
     *
     * @var string
     */
    public $eventID;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($orderID, $eventID)
    {
        $this->orderID = $orderID;
        $this->eventID = $eventID;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
