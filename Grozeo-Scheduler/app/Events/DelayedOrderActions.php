<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DelayedOrderActions
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * Customer Order id
     *
     * @var int
     */
    public $orderID;
    /**
     * delay type
     *
     * @var int
     */
    public $type;
    /**
     * delay time in minutes(default = 0)
     *
     * @var int
     */
    public $delay;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($orderID, $type, $delay = 0)
    {
        $this->orderID = $orderID;
        $this->type = $type;
        $this->delay = $delay;
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
