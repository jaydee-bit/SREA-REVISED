<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncidentClosed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $incidentId;
    public ?int $responderId;
    public string $barangay;

    public function __construct(int $incidentId, ?int $responderId, string $barangay)
    {
        $this->incidentId = $incidentId;
        $this->responderId = $responderId;
        $this->barangay = $barangay;
    }

    public function broadcastOn(): array
    {
        return [new Channel('live-map')];
    }

    public function broadcastAs(): string
    {
        return 'incident.closed';
    }
}