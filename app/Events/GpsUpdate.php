<?php

namespace App\Events;

use App\Models\Bicycle;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class GpsUpdate implements ShouldBroadcast
{
    use SerializesModels;

    public Bicycle $bicycle;

    public float $lat;

    public float $lng;

    public array $meta;

    public function __construct(Bicycle $bicycle, float $lat, float $lng, array $meta = [])
    {
        $this->bicycle = $bicycle;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->meta = [
            'speed' => $meta['speed'] ?? null,
            'battery' => $meta['battery'] ?? 0,
            'lock_status' => $meta['lock_status'] ?? 'locked',
            'zone' => $meta['zone'] ?? null,
            'zone_distance' => $meta['zone_distance'] ?? null,
        ];
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
                'speed' => $this->meta['speed'],
                'battery' => $this->meta['battery'],
                'locked' => $this->meta['lock_status'] === 'locked',
                'status' => $this->bicycle->status,
                'zone' => $this->meta['zone'],
                'zone_distance' => $this->meta['zone_distance'],
                'updated_at' => now()->toIso8601String(),
            ],
        ];
    }
}
