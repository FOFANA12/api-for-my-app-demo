<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotifyUserMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message, $id)
    {
        $this->message = $message;
        $this->id = $id;
    }

    public function broadcastOn()
    {
        return ['ntfmsg-'.$this->id];
    }


    public function broadcastWith () {
        return [
            'message' => $this->message,
            'code' => 'ntfmsg',
        ];
    }

    public function broadcastAs()
    {
        return 'notification';
    }
}
