<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class GoogleAuthController extends Controller
{
    private function driver(string $redirectUrl, bool $stateless = false)
    {
        $driver = Socialite::driver('google')
            ->redirectUrl($redirectUrl);

        return $stateless ? $driver->stateless() : $driver;
    }

    /**
     * Web (session) flow - start.
     */
    public function redirectToProvider(Request $request): RedirectResponse
    {
        $this->guardCredentials();

        return $this->driver($this->webCallbackUrl($request))
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Web (session) flow - callback.
     */
    public function handleProviderCallback(Request $request): RedirectResponse
    {
        $googleUser = $this->resolveGoogleUser($this->webCallbackUrl($request));

        if (! $googleUser) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in failed. Please try again or use email login.',
            ]);
        }

        return $this->loginResponse($request, $googleUser, $this->findUser($googleUser));
    }

    private function loginResponse(Request $request, SocialiteUser $googleUser, ?User $user): RedirectResponse
    {
        if ($user === null) {
            $request->session()->put('pending_oauth', [
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Google User',
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
                'google_id' => $googleUser->getId(),
            ]);

            return redirect()->route('register')
                ->with('status', 'We found your Google account. Please complete your registration to continue.');
        }

        if (! $user->isActive()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Your account is inactive. Please contact the administrator.',
            ]);
        }

        $this->linkGoogleProfile($user, $googleUser);

        Auth::login($user, true);

        $request->session()->regenerate();

        $this->recordLogin($user);

        return $user->isAdmin()
            ? redirect()->intended(route('admin.dashboard'))
            : redirect()->intended(route('rider.dashboard'));
    }

    private function findUser(SocialiteUser $googleUser): ?User
    {
        return User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();
    }

    /**
     * API (mobile / Sanctum token) flow - start.
     * Returns the Google authorization URL for the client to open in a browser.
     */
    public function apiRedirect(Request $request): JsonResponse
    {
        $this->guardCredentials();

        $url = $this->driver($this->apiCallbackUrl($request), true)
            ->with(['prompt' => 'select_account'])
            ->redirect()
            ->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    /**
     * API (mobile / Sanctum token) flow - callback.
     * Returns a bearer token for existing users, or a registration payload for new users.
     */
    public function apiCallback(Request $request): JsonResponse
    {
        $googleUser = $this->resolveGoogleUser($this->apiCallbackUrl($request), true);

        if (! $googleUser) {
            return response()->json([
                'message' => 'Google authentication failed.',
            ], 401);
        }

        return $this->apiLoginResponse($request, $googleUser, $this->findUser($googleUser));
    }

    private function apiLoginResponse(Request $request, SocialiteUser $googleUser, ?User $user): JsonResponse
    {
        if ($user === null) {
            return $this->registrationPayload($googleUser);
        }

        if (! $user->isActive()) {
            return response()->json([
                'message' => 'Your account is not active.',
            ], 403);
        }

        $this->linkGoogleProfile($user, $googleUser);
        $this->recordLogin($user);

        $token = $user->createToken($request->input('device_name', 'google-oauth'))->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    private function registrationPayload(SocialiteUser $googleUser): JsonResponse
    {
        // New Google user: tell the mobile app to send them to registration.
        return response()->json([
            'needs_registration' => true,
            'message' => 'No account exists. Please complete registration.',
            'provider' => 'google',
            'profile' => [
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Google User',
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
                'google_id' => $googleUser->getId(),
            ],
        ]);
    }

    /**
     * Link an existing account to the Google identity and refresh avatar.
     */
    private function linkGoogleProfile(User $user, SocialiteUser $googleUser): void
    {
        $user->update([
            'google_id' => $user->google_id ?? $googleUser->getId(),
            'avatar' => $user->avatar ?? $googleUser->getAvatar(),
            'oauth_provider' => $user->oauth_provider ?? 'google',
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);
    }

    private function resolveGoogleUser(string $redirectUrl, bool $stateless = false): ?SocialiteUser
    {
        try {
            return $this->driver($redirectUrl, $stateless)->user();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function recordLogin(User $user): void
    {
        $user->update(['last_login_at' => now()]);
    }

    private function webCallbackUrl(Request $request): string
    {
        return $request->root().'/auth/google/callback';
    }

    private function apiCallbackUrl(Request $request): string
    {
        return $request->root().'/api/auth/google/callback';
    }

    private function guardCredentials(): void
    {
        abort_unless(
            config('services.google.client_id') && config('services.google.client_secret'),
            500,
            'Google OAuth is not configured. Set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET.'
        );
    }
}
