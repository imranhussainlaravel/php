<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedOrigins = array_filter(explode(',', env('ALLOWED_ORIGINS', '')));
        $origin         = $request->header('Origin');
        $method         = $request->method();

        // Require Origin header on all mutating requests — bots frequently omit it
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE']) && !$origin) {
            return response()->json(['error' => 'Origin required'], 403);
        }

        // Validate origin against the allowed list when one is provided
        if ($origin && count($allowedOrigins) && !in_array($origin, $allowedOrigins)) {
            return response()->json(['error' => 'Origin not allowed'], 403);
        }

        return $next($request);
    }
}
