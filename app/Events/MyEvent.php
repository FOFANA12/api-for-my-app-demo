<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MyEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $id;

    public function __construct($message, $id)
    {
        $this->message = $message;
        $this->id = $id;
    }

    public function broadcastOn()
    {
        return ['channel-'.$this->id];
    }


    public function broadcastWith () {
        return [
            'message' => $this->message,
        ];
    }

    public function broadcastAs()
    {
        return 'my-event';
    }
}
