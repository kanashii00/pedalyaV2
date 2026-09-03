<?php

namespace App\Events;

use App\Models\Bicycle;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class GpsUpdate implements ShouldBroadcast
{
    use SerializesModels;

    public Bicycle $bicycle;

    public float $lat;

    public float $lng;

    public ?float $speed;

    public int $batteryLevel;

    public string $lockStatus;

    public ?string $zoneLevel;

    public ?float $zoneDistance;

    public function __construct(Bicycle $bicycle, float $lat, float $lng, ?float $speed = null, int $batteryLevel = 0, string $lockStatus = 'locked', ?string $zoneLevel = null, ?float $zoneDistance = null)
    {
        $this->bicycle = $bicycle;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->speed = $speed;
        $this->batteryLevel = $batteryLevel;
        $this->lockStatus = $lockStatus;
        $this->zoneLevel = $zoneLevel;
        $this->zoneDistance = $zoneDistance;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('gps.'.$this->bicycle->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'GpsUpdate';
    }

    public function broadcastWith(): array
    {
        return [
            'bicycle' => [
                'id' => $this->bicycle->id,
                'name' => $this->bicycle->name,
                'lat' => $this->lat,
                'lng' => $this->lng,
                'speed' => $this->speed,
                'battery' => $this->batteryLevel,
                'locked' => $this->lockStatus === 'locked',
                'status' => $this->bicycle->status,
                'zone' => $this->zoneLevel,
                'zone_distance' => $this->zoneDistance,
                'updated_at' => now()->toIso8601String(),
            ],
        ];
    }
}
