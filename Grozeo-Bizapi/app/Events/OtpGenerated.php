<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class OtpGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Otp to be send to user's phone
     *
     * @var int
     */
    public $otp;

    /**
     * Mobile number to which otp will be send
     *
     * @var string
     */
    public $mobile;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($mobile, $msg)
    {        
        $this->mobile = $mobile;
		$this->msg = $msg;
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
