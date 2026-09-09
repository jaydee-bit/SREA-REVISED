<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class NewIncidentReported implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $incident;

    public function __construct(array $incident)
    {
        $this->incident = $incident;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('live-map'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'incident.reported';
    }
}