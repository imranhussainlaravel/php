<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class VerifyTurnstile
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->input('cf_turnstile_token');

        if (!$token) {
            return response()->json(['message' => 'Security check required.'], 422);
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v1/siteverify', [
            'secret'   => config('services.turnstile.secret'),
            'response' => $token,
            'remoteip' => $request->ip(),
        ]);

        if (!$response->successful() || !$response->json('success')) {
            return response()->json(['message' => 'Security verification failed. Please try again.'], 422);
        }

        return $next($request);
    }
}
