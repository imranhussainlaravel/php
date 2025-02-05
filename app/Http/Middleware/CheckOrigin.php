<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrigin
{
    public function handle(Request $request, Closure $next)
    {
        $allowedOrigins = explode(',', env('ALLOWED_ORIGINS', ''));
        $origin = $request->header('Origin');
    
        // Allow requests without Origin header (non-browser clients)
        if (!$origin) {
            return $next($request);
        }
    
        // Check if the Origin matches allowed domains
        if (!in_array($origin, $allowedOrigins)) {
            return response()->json(['error' => 'Origin not allowed'], 403);
        }
    
        return $next($request);
    }
}