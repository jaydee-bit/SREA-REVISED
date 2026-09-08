<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResponderUnassigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $responderId;

    public function __construct(int $responderId)
    {
        $this->responderId = $responderId;
    }

    public function broadcastOn(): array
    {
        return [new Channel('live-map')];
    }

    public function broadcastAs(): string
    {
        return 'responder.unassigned';
    }
}