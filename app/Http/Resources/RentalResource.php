<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'rentalId'          => $this->rentalId,
            'bicycleId'         => $this->bicycleId,
            'bicycleName'       => $this->bicycleName,
            'bicycleSerial'     => $this->bicycleSerial,
            'riderId'           => $this->riderId,
            'riderName'         => $this->riderName,
            'riderEmail'        => $this->riderEmail,
            'status'            => $this->status,
            'startTime'         => $this->startTime,
            'endTime'           => $this->endTime,
            'startLocation'     => $this->startLocation,
            'endLocation'       => $this->endLocation,
            'ratePerHour'       => $this->ratePerHour,
            'totalFee'          => $this->totalFee,
            'durationMinutes'   => $this->durationMinutes,
            'durationFormatted' => $this->durationFormatted,
            'chargedHours'      => $this->chargedHours,
            'totalDistance'     => $this->totalDistance,
            'paymentStatus'     => $this->paymentStatus,
            'paymentMethod'     => $this->paymentMethod,
            'paymentReference'  => $this->paymentReference,
            'notes'             => $this->notes,
            'cancelledBy'       => $this->cancelledBy,
            'cancelReason'      => $this->cancelReason,
            'approvedBy'        => $this->approvedBy,
            'approvedAt'        => $this->approvedAt,
            'bicycle'           => new BicycleResource($this->whenLoaded('bicycle')),
            'rider'             => new UserResource($this->whenLoaded('rider')),
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
