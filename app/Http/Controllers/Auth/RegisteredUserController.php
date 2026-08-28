<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'pendingOauth' => session('pending_oauth'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $pendingOauth = $request->session()->get('pending_oauth');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class, 'email'),
            ],
            'phoneNumber' => ['nullable', 'string', 'max:20'],
        ];

        // Google-authenticated users don't need a password to register.
        if (! $pendingOauth) {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $request->validate($rules);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phoneNumber' => $request->phoneNumber,
            'password' => $pendingOauth ? null : Hash::make($request->password),
            'google_id' => $pendingOauth['google_id'] ?? null,
            'avatar' => $pendingOauth['avatar'] ?? null,
            'oauth_provider' => $pendingOauth ? 'google' : null,
            'email_verified_at' => $pendingOauth ? now() : null,
            'role' => User::ROLE_RIDER,
            'status' => User::STATUS_ACTIVE,
            'verified' => false,
            'idUploaded' => false,
            'totalRentals' => 0,
            'totalSpent' => 0,
        ]);

        if ($pendingOauth) {
            $request->session()->forget('pending_oauth');
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('rider.dashboard');
    }
}
