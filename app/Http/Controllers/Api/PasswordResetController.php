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

        $resetError = $this->resetError($validated, $resetRecord);

        if ($resetError !== null) {
            return response()->json(['message' => $resetError], 422);
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

    private function resetError(array $validated, ?object $resetRecord): ?string
    {
        $expired = $resetRecord !== null
            && Carbon::parse($resetRecord->created_at)->lt(now()->subMinutes(10));

        if ($expired) {
            DB::table('password_reset_tokens')
                ->where('email', $validated['email'])
                ->delete();
        }

        $guards = [
            [$resetRecord === null, 'No password reset request was found.'],
            [$expired, 'The password reset code has expired.'],
            [$resetRecord !== null && !Hash::check($validated['code'], $resetRecord->token), 'The password reset code is incorrect.'],
        ];

        foreach ($guards as $guard) {
            if ($guard[0]) {
                return $guard[1];
            }
        }

        return null;
    }
}