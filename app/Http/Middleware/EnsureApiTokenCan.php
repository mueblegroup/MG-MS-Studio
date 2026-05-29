<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiTokenCan
{
    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        if (!$user || !$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated API request.',
            ], 401);
        }

        foreach ($abilities as $ability) {
            if ($token->can('*') || $token->can($ability)) {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'This API token does not have permission to perform this action.',
            'required_abilities' => $abilities,
        ], 403);
    }
}
