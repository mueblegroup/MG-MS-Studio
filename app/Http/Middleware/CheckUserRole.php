<?php

namespace App\Http\Middleware;

use App\Support\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (! Auth::check() || Auth::user()->role !== $role) {
            abort(403, 'Unauthorized access.');
        }

        if ($this->isStudioRole($role) && $this->isStudioAreaPath($request)) {
            $studio = app(TenantManager::class)->current();

            abort_if(! $studio, 403, 'Studio context is required. Open this page from the studio subdomain.');

            $user = Auth::user();
            $belongsToStudio = $user->studio_id && (int) $user->studio_id === (int) $studio->id;
            $ownsStudio = $user->role === 'admin'
                && ! $user->studio_id
                && (int) $studio->owner_user_id === (int) $user->id;

            abort_unless($belongsToStudio || $ownsStudio, 403, 'This account is not assigned to this studio.');
        }

        return $next($request);
    }

    private function isStudioRole(string $role): bool
    {
        return in_array($role, ['admin', 'teacher', 'student'], true);
    }

    private function isStudioAreaPath(Request $request): bool
    {
        return $request->is('admin*') || $request->is('teacher*') || $request->is('student*');
    }
}
