<?php

namespace App\Services;

use App\Models\Bicycle;
use App\Models\Rental;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RentalService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function startRental(User $user, int $bicycleId): Rental
    {
        if (!$user->verified) {
            throw new \Exception('User must be verified to start a rental.');
        }

        $activeRental = Rental::where('riderId', $user->id)
            ->whereIn('status', ['active', 'pending'])
            ->first();

        if ($activeRental) {
            throw new \Exception('User already has an active rental.');
        }

        $bicycle = Bicycle::findOrFail($bicycleId);

        if ($bicycle->status !== 'available') {
            throw new \Exception('Bicycle is not available for rental.');
        }

        if ($bicycle->batteryLevel < 20) {
            throw new \Exception('Bicycle battery level is too low to rent.');
        }

        return DB::transaction(function () use ($user, $bicycle) {
            $rental = Rental::create([
                'rentalId' => $this->generateRentalId(),
                'riderId' => $user->id,
                'riderName' => $user->name,
                'riderEmail' => $user->email,
                'bicycleId' => $bicycle->id,
                'bicycleName' => $bicycle->name,
                'bicycleSerial' => $bicycle->serialNumber,
                'startTime' => Carbon::now(),
                'startLocation' => ['lat' => $bicycle->currentLat, 'lng' => $bicycle->currentLng],
                'status' => 'active',
                'ratePerHour' => $bicycle->hourlyRate ?? 15.00,
            ]);

            $bicycle->update([
                'status' => 'rented',
                'currentRider' => $user->id,
                'currentRentalId' => $rental->id,
            ]);

            $user->increment('totalRentals');

            $this->notificationService->create(
                $user->id,
                'Rental Started',
                "Your rental {$rental->rentalId} has started. Enjoy your ride!",
                'rental_started'
            );

            return $rental;
        });
    }

    public function returnRental(
        Rental $rental,
        User $user,
        ?float $returnLat,
        ?float $returnLng,
        ?string $paymentMethod,
        ?string $paymentReference,
        ?string $notes
    ): array {
        if ($rental->status !== 'active') {
            throw new \Exception('Rental is not active.');
        }

        $endTime = Carbon::now();
        $fees = $this->calculateFees(
            $rental->startTime,
            $endTime->toDateTimeString(),
            $rental->ratePerHour
        );

        return DB::transaction(function () use ($rental, $user, $endTime, $returnLat, $returnLng, $paymentMethod, $paymentReference, $notes, $fees) {
            $rental->update([
                'endTime' => $endTime,
                'endLocation' => ['lat' => $returnLat, 'lng' => $returnLng],
                'totalFee' => $fees['totalFee'],
                'durationMinutes' => $fees['durationMinutes'],
                'durationFormatted' => $fees['durationFormatted'],
                'chargedHours' => $fees['chargedHours'],
                'paymentMethod' => $paymentMethod,
                'paymentReference' => $paymentReference,
                'paymentStatus' => 'paid',
                'notes' => $notes,
                'status' => 'completed',
            ]);

            $bicycle = Bicycle::find($rental->bicycleId);
            if ($bicycle) {
                $bicycle->update([
                    'status' => 'available',
                    'currentLat' => $returnLat,
                    'currentLng' => $returnLng,
                    'currentRider' => null,
                    'currentRentalId' => null,
                    'totalRentals' => $bicycle->totalRentals + 1,
                ]);
            }

            $user->increment('totalSpent', $fees['totalFee']);

            $this->notificationService->create(
                $user->id,
                'Rental Completed',
                "Your rental {$rental->rentalId} has been completed. Total fee: ₱{$fees['totalFee']}.",
                'rental_completed'
            );

            return ['rental' => $rental->fresh(), 'fees' => $fees];
        });
    }

    public function approveRental(Rental $rental, User $admin): Rental
    {
        if ($rental->status !== 'pending') {
            throw new \Exception('Rental is not pending approval.');
        }

        $rental->update([
            'status' => 'active',
            'approvedBy' => $admin->id,
            'approvedAt' => Carbon::now(),
        ]);

        $this->notificationService->create(
            $rental->riderId,
            'Rental Approved',
            "Your rental {$rental->rentalId} has been approved.",
            'rental_approved'
        );

        return $rental->fresh();
    }

    public function cancelRental(Rental $rental, User $user, ?string $reason = null): Rental
    {
        if (!in_array($rental->status, ['active', 'pending'])) {
            throw new \Exception('Rental cannot be cancelled in its current status.');
        }

        $rental->update([
            'status' => 'cancelled',
            'cancelReason' => $reason,
            'cancelledBy' => $user->id,
        ]);

        $bicycle = Bicycle::find($rental->bicycleId);
        if ($bicycle) {
            $bicycle->update([
                'status' => 'available',
                'currentRider' => null,
                'currentRentalId' => null,
            ]);
        }

        $this->notificationService->create(
            $rental->riderId,
            'Rental Cancelled',
            "Your rental {$rental->rentalId} has been cancelled." . ($reason ? " Reason: {$reason}" : ''),
            'rental_cancelled'
        );

        return $rental->fresh();
    }

    public function calculateFees(string $startTime, ?string $endTime, float $ratePerHour): array
    {
        $start = Carbon::parse($startTime);
        $end = $endTime ? Carbon::parse($endTime) : Carbon::now();

        $durationMinutes = (int) $start->diffInMinutes($end);
        $durationHours = $durationMinutes / 60;
        $chargedHours = max(ceil($durationHours), 1);
        $totalFee = $chargedHours * $ratePerHour;

        $hours = floor($durationMinutes / 60);
        $minutes = $durationMinutes % 60;
        $durationFormatted = "{$hours}h {$minutes}m";

        return [
            'totalFee' => round($totalFee, 2),
            'durationMinutes' => $durationMinutes,
            'durationFormatted' => $durationFormatted,
            'chargedHours' => $chargedHours,
            'ratePerHour' => $ratePerHour,
        ];
    }

    public function generateRentalId(): string
    {
        $date = Carbon::now()->format('Ymd');
        $random = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));

        return "REN-{$date}-{$random}";
    }
}
