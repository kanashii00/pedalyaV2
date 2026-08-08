<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'message'    => $this->message,
            'type'       => $this->type,
            'read'       => (bool) $this->read,
            'readAt'     => $this->readAt,
            'bicycleId'  => $this->bicycleId,
            'rentalId'   => $this->rentalId,
            'incidentId' => $this->incidentId,
            'sentBy'     => $this->sentBy,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
