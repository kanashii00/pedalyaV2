<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GeofenceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public array $geofence
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('geofence-alerts')];
    }

    public function broadcastWith(): array
    {
        return ['geofence' => $this->geofence];
    }
}
