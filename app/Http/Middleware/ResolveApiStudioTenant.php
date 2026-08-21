<?php

namespace App\Http\Middleware;

use App\Models\Studio;
use App\Models\User;
use App\Support\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class ResolveApiStudioTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        // This middleware is prepended to Laravel's API middleware group, so it
        // intentionally resolves the Sanctum bearer token itself before
        // SubstituteBindings can route-model-bind an unscoped tenant model.
        $plainToken = (string) $request->bearerToken();
        $accessToken = $plainToken !== '' ? PersonalAccessToken::findToken($plainToken) : null;
        $user = $accessToken?->tokenable;

        if (! $accessToken || ! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'A studio administrator API token is required.',
            ], 403);
        }

        $studioId = $this->studioIdFromToken((array) $accessToken->abilities);

        // Compatibility for existing staff-admin tokens created before explicit
        // studio binding was introduced.
        if (! $studioId && $user->studio_id) {
            $studioId = (int) $user->studio_id;
        }

        // Owner accounts can also keep an old token only when ownership is
        // unambiguous. Owners of multiple studios must recreate a studio-bound
        // token from the intended workspace.
        if (! $studioId) {
            $ownedStudioIds = Studio::query()
                ->where('owner_user_id', $user->id)
                ->limit(2)
                ->pluck('id');

            if ($ownedStudioIds->count() === 1) {
                $studioId = (int) $ownedStudioIds->first();
            }
        }

        if (! $studioId) {
            return response()->json([
                'success' => false,
                'message' => 'This API token is not bound to a studio. Recreate the token from the intended studio workspace.',
            ], 403);
        }

        $studio = Studio::query()->find($studioId);
        $authorized = $studio && (
            (int) $user->studio_id === (int) $studio->id
            || (int) $studio->owner_user_id === (int) $user->id
        );

        if (! $authorized) {
            return response()->json([
                'success' => false,
                'message' => 'This API token does not have access to the requested studio.',
            ], 403);
        }

        $tenants = app(TenantManager::class);
        $tenants->set($studio);

        try {
            return $next($request);
        } finally {
            $tenants->clear();
        }
    }

    private function studioIdFromToken(array $abilities): ?int
    {
        foreach ($abilities as $ability) {
            if (! is_string($ability) || ! str_starts_with($ability, 'studio:')) {
                continue;
            }

            $id = (int) substr($ability, strlen('studio:'));
            if ($id > 0) {
                return $id;
            }
        }

        return null;
    }
}
