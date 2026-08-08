<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeviceAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            return response()->json(['message' => 'Missing API key'], 401);
        }

        $expectedKey = config('services.device_api_key');

        if (!$expectedKey || !hash_equals($expectedKey, $apiKey)) {
            return response()->json(['message' => 'Invalid API key'], 401);
        }

        return $next($request);
    }
}
