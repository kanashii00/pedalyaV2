<?php

namespace App\Events;

use App\Models\Bicycle;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class GeofenceAlert implements ShouldBroadcast
{
    use SerializesModels;

    public Bicycle $bicycle;

    public string $level;

    public float $distance;

    public float $lat;

    public float $lng;

    public function __construct(Bicycle $bicycle, string $level, float $distance, float $lat, float $lng)
    {
        $this->bicycle = $bicycle;
        $this->level = $level;
        $this->distance = $distance;
        $this->lat = $lat;
        $this->lng = $lng;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('geofence-alerts'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'GeofenceAlert';
    }

    public function broadcastWith(): array
    {
        return [
            'level' => $this->level,
            'distance' => $this->distance,
            'bicycle' => [
                'id' => $this->bicycle->id,
                'name' => $this->bicycle->name,
                'lat' => $this->lat,
                'lng' => $this->lng,
                'status' => $this->bicycle->status,
                'battery' => $this->bicycle->batteryLevel,
                'locked' => $this->bicycle->lockStatus === 'locked',
                'zone' => $this->level,
                'zone_distance' => $this->distance,
                'updated_at' => now()->toIso8601String(),
            ],
        ];
    }
}
