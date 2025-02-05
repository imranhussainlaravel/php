<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrigin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $allowedOrigins = explode(',', env('ALLOWED_ORIGINS', ''));
        $origin = $request->header('Origin');

        if (!in_array($origin, $allowedOrigins)) {
            return response()->json(['error' => 'Origin not allowed'], 403);
        }

        return $next($request);
    }
}
