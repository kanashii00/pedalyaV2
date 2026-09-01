<?php

namespace App\Services;

use App\Exceptions\RentalException;
use App\Models\Bicycle;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RentalService
{
    protected NotificationService $notificationService;
    protected IoTService $iotService;

    public function __construct(
        NotificationService $notificationService,
        IoTService $iotService
    ) {
        $this->notificationService = $notificationService;
        $this->iotService = $iotService;
    }

public function startRental(
        User $user,
        int $bicycleId,
        int $durationMinutes = 30,
        string $paymentMethod = 'cash',
        ?string $paymentReference = null
    ): Rental {
        if (!$user->verified) {
            throw new RentalException(
                'User must be verified to start a rental.'
            );
        }

        $activeRental = Rental::where('riderId', $user->id)
            ->whereIn('status', [
                'active',
                'pending',
                'overdue',
            ])
            ->first();

        if ($activeRental) {
            throw new RentalException(
                'User already has an active rental.'
            );
        }

        $bicycle = Bicycle::findOrFail($bicycleId);

        if ($bicycle->status !== Bicycle::STATUS_AVAILABLE) {
            throw new RentalException(
                'Bicycle is not available for rental.'
            );
        }

        if ($bicycle->batteryLevel < 20) {
            throw new RentalException(
                'Bicycle battery level is too low to rent.'
            );
        }

        $ratePerHour = $bicycle->hourlyRate ?? 15.00;

        $durationHours = $durationMinutes / 60;
        $chargedHours = max((int) ceil($durationHours), 1);
        $totalFee = round($ratePerHour * $chargedHours, 2);

        $durationFormatted = $this->formatDuration($durationMinutes);

        $pricing = [
            'ratePerHour' => $ratePerHour,
            'chargedHours' => $chargedHours,
            'totalFee' => $totalFee,
            'durationFormatted' => $durationFormatted,
        ];

        $isGcash = $paymentMethod === 'gcash';
        $rental = DB::transaction(function () use (
            $user,
            $bicycle,
            $durationMinutes,
            $paymentMethod,
            $paymentReference,
            $pricing
        ) {
            return $this->persistRental(
                $user,
                $bicycle,
                $durationMinutes,
                $paymentMethod,
                $paymentReference,
                $pricing
            );
        });

        // Queue unlock only after the rental transaction succeeds.
        // The actual lockStatus changes only after the device acknowledges it.
        if (!$isGcash) {
            $this->iotService->sendCommand(
                $bicycle->id,
                'unlock',
                [
                    'reason' => 'rental_started',
                    'rental_id' => $rental->id,
                    'rental_code' => $rental->rentalId,
                ],
                $user
            );
        }

        return $rental;
    }

    private function persistRental(
        User $user,
        Bicycle $bicycle,
        int $durationMinutes,
        string $paymentMethod,
        ?string $paymentReference,
        array $pricing
    ): Rental {
        $isGcash = $paymentMethod === 'gcash';
        $ratePerHour = $pricing['ratePerHour'];
        $chargedHours = $pricing['chargedHours'];
        $totalFee = $pricing['totalFee'];
        $durationFormatted = $pricing['durationFormatted'];

        $startTime = Carbon::now();

        $expectedEndTime = $startTime
            ->copy()
            ->addMinutes($durationMinutes);

        $rental = Rental::create([
            'rentalId' => $this->generateRentalId(),

            'riderId' => $user->id,
            'riderName' => $user->name,
            'riderEmail' => $user->email,

            'bicycleId' => $bicycle->id,
            'bicycleName' => $bicycle->name,
            'bicycleSerial' => $bicycle->serialNumber,

            'startTime' => $startTime,
            'expectedEndTime' => $expectedEndTime,

            'startLocation' => [
                'lat' => $bicycle->currentLat,
                'lng' => $bicycle->currentLng,
            ],

            'status' => $isGcash ? 'pending' : 'active',

            'ratePerHour' => $ratePerHour,
            'totalFee' => $totalFee,

            'durationMinutes' => $durationMinutes,
            'durationFormatted' => $durationFormatted,
            'chargedHours' => $chargedHours,

            'paymentMethod' => $paymentMethod,
            'paymentReference' => $paymentReference,
            'paymentStatus' => $isGcash
                ? 'pending_verification'
                : 'paid',
        ]);

        if (!$isGcash) {
            $bicycle->update([
                'status' => Bicycle::STATUS_RENTED,
                'currentRider' => $user->id,
                'currentRentalId' => $rental->id,
            ]);

            $user->increment('totalRentals');
        }

        if ($isGcash) {
            Payment::create([
                'rentalId' => $rental->id,
                'userId' => $user->id,
                'bicycleId' => $bicycle->id,

                'paymentReference' =>
                    $paymentReference
                    ?? 'GC-' . strtoupper(
                        substr(bin2hex(random_bytes(4)), 0, 8)
                    ),

                'paymentMethod' => 'gcash',
                'amount' => $totalFee,
                'convenienceFee' => 0,
                'totalAmount' => $totalFee,
                'currency' => 'PHP',
                'status' => 'pending',

                'paymentDetails' => [
                    'rental_id' => $rental->rentalId,
                    'duration_minutes' => $durationMinutes,
                    'bicycle_name' => $bicycle->name,
                    'bicycle_serial' => $bicycle->serialNumber,
                ],
            ]);
        }

        $this->notificationService->create(
            $user->id,
            $isGcash
                ? 'GCash Payment Submitted'
                : 'Rental Started',
            $isGcash
                ? "Your rental {$rental->rentalId} is pending payment verification. Please wait for admin approval."
                : "Your rental {$rental->rentalId} has started. Pay PHP {$totalFee} upon return. Enjoy your ride!",
            'rental_started'
        );

        return $rental;
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
      if (!in_array($rental->status, ['active', 'overdue'], true)) {
    throw new RentalException(
        'Only active or overdue rentals can be returned.'
    );
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
                'paymentMethod' => $paymentMethod ?? $rental->paymentMethod,
                'paymentReference' => $paymentReference ?? $rental->paymentReference,
                'paymentStatus' => 'paid',
                'notes' => $notes,
                'status' => 'completed',
            ]);

            // Automated settlement rule: Completed + Paid releases the bicycle
            // (status -> Available) and secures its smart-lock controls
            // (lockStatus -> Locked + queued physical lock command).
            $bicycle = Bicycle::find($rental->bicycleId);
            if ($bicycle) {
                $this->settleBicycleForRental($rental, $user, [
                    'currentLat' => $returnLat,
                    'currentLng' => $returnLng,
                    'totalRentals' => $bicycle->totalRentals + 1,
                ]);
            }

            $user->increment('totalSpent', $fees['totalFee']);

            $this->notificationService->create(
                $user->id,
                'Rental Completed',
                "Your rental {$rental->rentalId} has been completed. Total fee: PHP {$fees['totalFee']}.",
                'rental_completed'
            );

            return ['rental' => $rental->fresh(), 'fees' => $fees];
        });
    }

    /**
     * Automated status update rule.
     *
 * When a rental's status is "Completed" and its payment status is
* "Paid", the corresponding bicycle is released and its status
* becomes "Available". A physical lock command is queued for the
* ESP32 after the database transaction commits. The lockStatus is
* updated only after the device acknowledges the lock command.
     *
     * The rule is idempotent: an already-settled bicycle is left
     * untouched and no duplicate lock command is queued.
     */
    public function settleBicycleForRental(Rental $rental, ?User $actor = null, array $extraAttributes = []): bool
    {
        $bicycle = $this->settleableBicycle($rental);

        if ($bicycle === null) {
            return false;
        }

        $attributes = array_merge([
            'status' => Bicycle::STATUS_AVAILABLE,
            'currentRider' => null,
            'currentRentalId' => null,
        ], $extraAttributes);

        $bicycle->fill($attributes);

        // Already settled — nothing to do.
        if (!$bicycle->isDirty()) {
            return false;
        }

        $bicycle->save();

        // Lock action control: queue the physical lock command so the
        // ESP32 secures the bicycle (executes after commit).
        Rental::resolveConnection()->afterCommit(function () use ($bicycle, $rental, $actor) {
            $this->iotService->sendCommand($bicycle->id, 'lock', [
                'reason' => 'rental_settled',
                'rentalId' => $rental->id,
            ], $actor);
        });

        return true;
    }

    /**
     * Returns the bicycle that may be settled for this rental, or null when any
     * settlement precondition fails (status, payment, or ownership).
     */
    private function settleableBicycle(Rental $rental): ?Bicycle
    {
        if ($rental->status !== Rental::STATUS_COMPLETED
            || strtolower((string) $rental->paymentStatus) !== 'paid'
            || !$rental->bicycleId) {
            return null;
        }

        $bicycle = Bicycle::find($rental->bicycleId);

        // Never hijack a bicycle that another rental now holds.
        // (currentRentalId historically stores either the rental PK or the
        // REN- reference string, so both are recognised as self.)
        if ($bicycle === null
            || ($bicycle->currentRentalId !== null
                && !in_array((string) $bicycle->currentRentalId, [(string) $rental->id, (string) $rental->rentalId], true))) {
            return null;
        }

        return $bicycle;
    }

    public function approveRental(Rental $rental, User $admin): Rental
    {
        if ($rental->status !== 'pending') {
            throw new RentalException('Rental is not pending approval.');
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
            throw new RentalException('Rental cannot be cancelled in its current status.');
        }

        $rental->update([
            'status' => 'cancelled',
            'cancelReason' => $reason,
            'cancelledBy' => $user->id,
        ]);

        // Rental cancelled: bicycle becomes Available and the smart lock
        // is Locked again since nobody is authorized to use it.
        $bicycle = Bicycle::find($rental->bicycleId);
        if ($bicycle) {
            $bicycle->update([
                'status' => Bicycle::STATUS_AVAILABLE,
                'currentRider' => null,
                'currentRentalId' => null,
                'lockStatus' => Bicycle::LOCK_LOCKED,
            ]);

            $this->iotService->sendCommand($rental->bicycleId, 'lock', [], $user);
        }

        $this->notificationService->create(
            $rental->riderId,
            'Rental Cancelled',
            "Your rental {$rental->rentalId} has been cancelled." . ($reason ? " Reason: {$reason}" : ''),
            'rental_cancelled'
        );

        return $rental->fresh();
    }

    public function markExpiredRentalsOverdue(): int
{
    return Rental::where('status', 'active')
        ->whereNotNull('expectedEndTime')
        ->where('expectedEndTime', '<=', Carbon::now())
        ->update([
            'status' => 'overdue',
            'overdueAt' => Carbon::now(),
        ]);
}

    public function calculateFees(string $startTime, ?string $endTime, float $ratePerHour): array
    {
        $start = Carbon::parse($startTime);
        $end = $endTime ? Carbon::parse($endTime) : Carbon::now();

        $durationMinutes = (int) $start->diffInMinutes($end);
        $durationHours = $durationMinutes / 60;
        $chargedHours = max(ceil($durationHours), 1);
        $totalFee = $chargedHours * $ratePerHour;

        $durationFormatted = $this->formatDuration($durationMinutes);

        return [
            'totalFee' => round($totalFee, 2),
            'durationMinutes' => $durationMinutes,
            'durationFormatted' => $durationFormatted,
            'chargedHours' => $chargedHours,
            'ratePerHour' => $ratePerHour,
        ];
    }

    public function createRentalFromPaidPayment(Payment $payment): Rental
    {
        $metadata = $payment->metadata ?? [];
        $durationHours = (int) ($metadata['rental_duration_hours'] ?? 1);
        $rider = User::find($payment->userId);

        $rental = Rental::create([
            'rentalId' => $this->generateRentalId(),
            'bicycleId' => $payment->bicycleId,
            'bicycleName' => optional(Bicycle::find($payment->bicycleId))->name,
            'bicycleSerial' => optional(Bicycle::find($payment->bicycleId))->serialNumber,
            'riderId' => $payment->userId,
            'riderName' => $rider?->name,
            'riderEmail' => $rider?->email,
            'status' => 'active',
            'startTime' => now(),
            'endTime' => now()->addHours(max($durationHours, 1)),
            'ratePerHour' => $durationHours > 0 ? round($payment->totalAmount / $durationHours, 2) : $payment->totalAmount,
            'totalFee' => $payment->totalAmount,
            'chargedHours' => max($durationHours, 1),
            'durationMinutes' => max($durationHours, 1) * 60,
            'durationFormatted' => $durationHours . 'h 0m',
            'paymentStatus' => 'paid',
            'paymentMethod' => 'gcash',
        ]);

        $payment->update(['rentalId' => $rental->id]);

        $bicycle = Bicycle::find($payment->bicycleId);
        if ($bicycle) {
            $bicycle->update([
                'status' => Bicycle::STATUS_RENTED,
                'currentRider' => $payment->userId,
                'currentRentalId' => $rental->id,
                'lockStatus' => Bicycle::LOCK_UNLOCKED,
                'lastLockAction' => now(),
            ]);

            if ($rider) {
                $this->iotService->sendCommand($bicycle->id, 'unlock', [], $rider);
            }
        }

        return $rental;
    }

    private function formatDuration(int $durationMinutes): string
    {
        $hours = intdiv($durationMinutes, 60);
        $minutes = $durationMinutes % 60;

        return "{$hours}h {$minutes}m";
    }

    public function generateRentalId(): string
    {
        $date = Carbon::now()->format('Ymd');
        $random = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));

        return "REN-{$date}-{$random}";
    }
}
