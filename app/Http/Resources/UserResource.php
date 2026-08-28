<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'email'          => $this->when($this->isCurrentUser($request), $this->email),
            'phoneNumber'    => $this->when($this->isCurrentUser($request), $this->phoneNumber),
            'address'        => $this->when($this->isCurrentUser($request), $this->address),
            'profilePicture' => $this->profilePicture,
            'role'           => $this->role,
            'status'         => $this->status,
            'verified'       => (bool) $this->verified,
            'idUploaded'     => (bool) $this->idUploaded,
            'idVerification' => $this->idVerification,
            'totalRentals'   => $this->totalRentals,
            'totalSpent'     => $this->totalSpent,
            'isVerified'     => (bool) $this->verified,
            'activeRental'   => $this->whenLoaded('currentRental', fn () => new RentalResource($this->currentRental)),
            'rentalsCount'   => $this->whenCounted('rentals'),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }

    private function isCurrentUser(Request $request): bool
    {
        return $request->user() && $request->user()->id === $this->id;
    }
}
