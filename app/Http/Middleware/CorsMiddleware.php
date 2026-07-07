<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Allowed origins for CORS requests.
     * Add any additional origins as needed.
     */
    private array $allowedOrigins = [
        'https://admin.nexonpackaging.com',
        'https://nexonpackaging.com',
        'https://www.nexonpackaging.com',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');

        // Check if the origin is in our allowed list
        $allowedOrigin = null;
        if ($origin && in_array($origin, $this->allowedOrigins)) {
            $allowedOrigin = $origin;
        }

        // Handle preflight OPTIONS requests immediately
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 204);
        } else {
            $response = $next($request);
        }

        // Add CORS headers if origin is allowed
        if ($allowedOrigin) {
            $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Max-Age', '86400'); // Cache preflight for 24 hours
        }

        return $response;
    }
}
