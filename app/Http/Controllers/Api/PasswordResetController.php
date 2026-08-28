<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    public function sendCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'exists:users,email',
            ],
        ]);

        $email = $validated['email'];

        $code = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($code),
                'created_at' => now(),
            ]
        );

        Mail::raw(
            "Your Pedalya password reset code is: {$code}\n\n"
            . "This code will expire in 10 minutes.\n\n"
            . "If you did not request a password reset, you can ignore this email.",
            function ($message) use ($email) {
                $message->to($email)
                    ->subject('Pedalya Password Reset Code');
            }
        );

        return response()->json([
            'message' => 'Password reset code sent successfully.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'exists:users,email',
            ],
            'code' => [
                'required',
                'digits:6',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'message' => 'No password reset request was found.',
            ], 422);
        }

        $createdAt = Carbon::parse($resetRecord->created_at);

        if ($createdAt->lt(now()->subMinutes(10))) {
            DB::table('password_reset_tokens')
                ->where('email', $validated['email'])
                ->delete();

            return response()->json([
                'message' => 'The password reset code has expired.',
            ], 422);
        }

        if (!Hash::check($validated['code'], $resetRecord->token)) {
            return response()->json([
                'message' => 'The password reset code is incorrect.',
            ], 422);
        }

        $user = User::where('email', $validated['email'])->firstOrFail();

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->delete();

        return response()->json([
            'message' => 'Password reset successfully.',
        ]);
    }
}