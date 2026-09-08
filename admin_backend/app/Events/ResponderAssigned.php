<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResponderAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $incident;

    public function __construct(array $incident)
    {
        $this->incident = $incident;
    }

    public function broadcastOn(): array
    {
        return [new Channel('live-map')];
    }

    public function broadcastAs(): string
    {
        return 'responder.assigned';
    }
}