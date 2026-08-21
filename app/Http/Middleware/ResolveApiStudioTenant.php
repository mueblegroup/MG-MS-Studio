<?php

namespace App\Http\Middleware;

use App\Models\Studio;
use App\Support\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveApiStudioTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'A studio administrator API token is required.',
            ], 403);
        }

        $studioId = $this->studioIdFromToken($user->currentAccessToken()?->abilities ?? []);

        if (! $studioId && $user->studio_id) {
            $studioId = (int) $user->studio_id;
        }

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
