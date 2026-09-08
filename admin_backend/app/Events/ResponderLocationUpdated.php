<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResponderLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $responderId;
    public float $latitude;
    public float $longitude;

    public function __construct(int $responderId, float $latitude, float $longitude)
    {
        $this->responderId = $responderId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function broadcastOn(): array
    {
        return [new Channel('live-map')];
    }

    public function broadcastAs(): string
    {
        return 'responder.location-updated';
    }
}