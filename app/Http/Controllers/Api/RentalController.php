<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\RentalException;
use App\Http\Controllers\Controller;
use App\Http\Resources\RentalResource;
use App\Models\Bicycle;
use App\Models\Rental;
use App\Services\IoTService;
use App\Services\RentalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

class RentalController extends Controller
{
    private const RENTAL_NOT_FOUND = 'Rental not found';

    public function __construct(
        private RentalService $rentalService,
        private IoTService $iotService
    ) {}

    public function active(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $query = Rental::with(['bicycle', 'rider'])
            ->whereIn('status', [Rental::STATUS_ACTIVE, Rental::STATUS_OVERDUE]);

        if ($user->role !== 'admin') {
            $query->where('riderId', $user->id);
        }

        $rentals = $query->orderByDesc('startTime')->get();

        return RentalResource::collection($rentals);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'status'     => 'nullable|string|in:active,pending,completed,cancelled,overdue',
            'rider_id'   => 'nullable|integer|exists:users,id',
            'bicycle_id' => 'nullable|integer|exists:bicycles,id',
            'from'       => 'nullable|date',
            'to'         => 'nullable|date|after_or_equal:from',
            'per_page'   => 'nullable|integer|min:1|max:100',
        ]);

        $user = $request->user();
        $query = Rental::with(['bicycle', 'rider']);

        if ($user->role !== 'admin') {
            $query->where('riderId', $user->id);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['rider_id'])) {
            $query->where('riderId', $validated['rider_id']);
        }

        if (!empty($validated['bicycle_id'])) {
            $query->where('bicycleId', $validated['bicycle_id']);
        }

        if (!empty($validated['from'])) {
            $query->where('startTime', '>=', $validated['from']);
        }

        if (!empty($validated['to'])) {
            $query->where('startTime', '<=', $validated['to']);
        }

        $rentals = $query->orderByDesc('startTime')
            ->paginate($validated['per_page'] ?? 20);

        return RentalResource::collection($rentals);
    }

    public function show(int $id): RentalResource|JsonResponse
    {
        $rental = Rental::with(['bicycle', 'rider'])->find($id);

        if (!$rental) {
            return response()->json(['message' => self::RENTAL_NOT_FOUND], 404);
        }

        $user = request()->user();
        if ($user->role !== 'admin' && $rental->riderId !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return new RentalResource($rental);
    }

public function store(Request $request): JsonResponse
{
    $validated = $request->validate([
        'bicycle_id' => 'required|integer|exists:bicycles,id',
        'duration_minutes' => 'required|integer|min:30|max:480',
    ]);

    $user = $request->user();

    try {
        $rental = $this->rentalService->startRental(
            $user,
            $validated['bicycle_id'],
            $validated['duration_minutes']
        );

        return response()->json([
            'message' => 'Rental started successfully',
            'rental' => new RentalResource(
                $rental->load(['bicycle', 'rider'])
            ),
        ], 201);
    } catch (RentalException $e) {
        return response()->json([
            'message' => $e->getMessage(),
        ], 422);
    } catch (\Throwable $e) {
        Log::error('Failed to start rental', ['error' => $e->getMessage()]);
        return response()->json([
            'message' => 'Unable to start the rental.',
        ], 500);
    }
}

    public function returnRental(Request $request, int $id): JsonResponse
    {
        $rental = Rental::find($id);

        if (!$rental) {
            return response()->json(['message' => self::RENTAL_NOT_FOUND], 404);
        }

        $user = $request->user();
        if ($user->role !== 'admin' && $rental->riderId !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'return_lat'      => 'nullable|numeric|between:-90,90',
            'return_lng'      => 'nullable|numeric|between:-180,180',
            'payment_method'  => 'nullable|string',
            'payment_reference' => 'nullable|string',
            'notes'           => 'nullable|string',
        ]);

        try {
            $result = $this->rentalService->returnRental(
                $rental,
                $user,
                $validated['return_lat'] ?? null,
                $validated['return_lng'] ?? null,
                $validated['payment_method'] ?? null,
                $validated['payment_reference'] ?? null,
                $validated['notes'] ?? null,
            );

            return response()->json([
                'message' => 'Bicycle returned successfully',
                'rental'  => new RentalResource($result['rental']->load(['bicycle', 'rider'])),
                'fees'    => $result['fees'],
            ]);
        } catch (RentalException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Failed to return rental', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Unable to return the rental.',
            ], 500);
        }
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $rental = Rental::find($id);

        if (!$rental) {
            return response()->json(['message' => self::RENTAL_NOT_FOUND], 404);
        }

        if ($rental->status !== Rental::STATUS_PENDING) {
            return response()->json(['message' => 'Only pending rentals can be approved'], 422);
        }

        $rental->update([
            'status'     => Rental::STATUS_ACTIVE,
            'approvedBy' => $request->user()->id,
            'approvedAt' => now(),
        ]);

        // Rental approved: bicycle becomes Rented and the smart lock is
        // Unlocked because the rider is now authorized to use it.
        Bicycle::where('id', $rental->bicycleId)->update([
            'status' => Bicycle::STATUS_RENTED,
            'currentRider' => $rental->riderId,
            'currentRentalId' => $rental->id,
            'lockStatus' => Bicycle::LOCK_UNLOCKED,
        ]);

        $this->iotService->sendCommand($rental->bicycleId, 'unlock', [], $request->user());

        return response()->json([
            'message' => 'Rental approved successfully',
            'rental'  => new RentalResource($rental->fresh()->load(['bicycle', 'rider'])),
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $rental = Rental::find($id);

        if (!$rental) {
            return response()->json(['message' => self::RENTAL_NOT_FOUND], 404);
        }

        $user = $request->user();
        if ($user->role !== 'admin' && $rental->riderId !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (in_array($rental->status, [Rental::STATUS_COMPLETED, Rental::STATUS_CANCELLED], true)) {
            return response()->json(['message' => 'Rental is already ' . $rental->status], 422);
        }

        $rental->update([
            'status' => Rental::STATUS_CANCELLED,
            'cancelledBy' => (string) $user->id,
            'notes' => $request->input('notes'),
        ]);

        $bicycle = Bicycle::find($rental->bicycleId);
        if ($bicycle && (string) $bicycle->currentRentalId === (string) $rental->id) {
            // Rental cancelled: bicycle becomes Available and the smart lock
            // is Locked again since nobody is authorized to use it.
            $bicycle->update([
                'status' => Bicycle::STATUS_AVAILABLE,
                'currentRider' => null,
                'currentRentalId' => null,
                'lockStatus' => Bicycle::LOCK_LOCKED,
            ]);

            $this->iotService->sendCommand($rental->bicycleId, 'lock', [], $user);
        }

        return response()->json([
            'message' => 'Rental cancelled successfully',
            'rental'  => new RentalResource($rental->fresh()->load(['bicycle', 'rider'])),
        ]);
    }
}
