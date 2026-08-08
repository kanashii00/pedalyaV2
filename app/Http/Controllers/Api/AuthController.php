<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if ($user->status !== User::STATUS_ACTIVE) {
            return response()->json(['message' => 'Your account is not active.'], 403);
        }

        $deviceName = $validated['device_name'] ?? 'web';

        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => new UserResource($user),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phoneNumber' => ['nullable', 'string', 'max:20'],
            'address'     => ['nullable', 'string', 'max:500'],
            'password'    => ['required', 'string', 'confirmed', Password::defaults()],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'phoneNumber' => $validated['phoneNumber'] ?? null,
            'address'     => $validated['address'] ?? null,
            'password'    => $validated['password'],
            'role'        => User::ROLE_RIDER,
            'status'      => User::STATUS_ACTIVE,
            'verified'    => false,
        ]);

        $token = $user->createToken($validated['device_name'] ?? 'web')->plainTextToken;

        return response()->json([
            'message'    => 'Account created successfully.',
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => new UserResource($user),
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function profile(Request $request): UserResource
    {
        $user = $request->user();

        $user->load([
            'currentRental',
            'rentals' => fn ($q) => $q->latest()->limit(10),
        ]);

        return new UserResource($user);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'email'        => 'sometimes|email|unique:users,email,' . $user->id,
            'phoneNumber'  => 'nullable|string|max:20',
            'address'      => 'nullable|string|max:500',
            'profilePicture' => 'nullable|string|max:500',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => new UserResource($user->fresh()),
        ]);
    }
}
