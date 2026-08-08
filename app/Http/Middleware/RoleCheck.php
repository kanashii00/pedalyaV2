<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleCheck
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->deny($request, 'Unauthenticated', 401);
        }

        if (!in_array($user->role, $roles)) {
            return $this->deny($request, 'Forbidden: insufficient role', 403);
        }

        return $next($request);
    }

    private function deny(Request $request, string $message, int $status): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => $message], $status);
        }

        if ($status === 401) {
            return redirect()->route('login')->with('error', 'Please sign in to continue.');
        }

        return back()->withErrors(['role' => 'You do not have permission to access this page.']);
    }
}
