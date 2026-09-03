<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApiGateway
{
    /**
     * Handle all incoming Pedalya API requests.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = (string) Str::uuid();

        $response = $next($request);

        $response->headers->set('X-Pedalya-Gateway', 'active');
        $response->headers->set('X-Request-ID', $requestId);
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
