<?php

namespace App\Http\Middleware\Sanctum;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Sanctum expired token middleware.
 *
 * Add middleware in bootstrap/app.php
 * $middleware->api(prepend: [ \App\Http\Middleware\Sanctum\ExpiredToken::class ]);
 */
class ExpiredToken
{
    public function handle(Request $request, Closure $next)
    {
        $bearer = $request->bearerToken();

        if ($bearer) {
            $token = PersonalAccessToken::findToken($bearer);

            if ($token instanceof PersonalAccessToken) {
                if ($token->expires_at && $token->expires_at->isPast()) {
                    return response()->json([
                        'message' => 'Expired Token.',
                        'token_expired' => $token->expires_at && $token->expires_at->isPast(),
                        'token_details' => $token
                    ], 403);
                }
            }
        }

        return $next($request);
    }
}
