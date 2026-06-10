<?php

namespace App\Http\Middleware;

use App\Models\Studio;
use App\Models\StudioDomain;
use App\Support\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveStudioTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $studio = $this->resolveFromAuthenticatedUser($request)
            ?: $this->resolveFromHost($request);

        if ($studio) {
            app(TenantManager::class)->set($studio);
            $request->attributes->set('studio', $studio);

            return $next($request);
        }

        if ($this->isCentralDomain($request)) {
            return $next($request);
        }

        if (app()->environment(['local', 'testing'])) {
            $fallback = Studio::query()->where('id', 1)->first();

            if ($fallback) {
                app(TenantManager::class)->set($fallback);
                $request->attributes->set('studio', $fallback);
            }

            return $next($request);
        }

        abort(404, 'Studio not found.');
    }

    private function resolveFromAuthenticatedUser(Request $request): ?Studio
    {
        $user = $request->user();

        if (! $user || ! $user->studio_id) {
            return null;
        }

        return Studio::query()
            ->where('id', $user->studio_id)
            ->whereIn('status', ['active', 'trial'])
            ->first();
    }

    private function resolveFromHost(Request $request): ?Studio
    {
        $host = strtolower($request->getHost());

        $domain = StudioDomain::query()
            ->where('domain', $host)
            ->where('is_verified', true)
            ->with('studio')
            ->first();

        if ($domain && $domain->studio?->isActive()) {
            return $domain->studio;
        }

        $rootDomain = strtolower((string) config('saas.root_domain'));

        if ($rootDomain && str_ends_with($host, '.' . $rootDomain)) {
            $subdomain = str_replace('.' . $rootDomain, '', $host);

            return Studio::query()
                ->where('subdomain', $subdomain)
                ->whereIn('status', ['active', 'trial'])
                ->first();
        }

        return Studio::query()
            ->where('custom_domain', $host)
            ->whereIn('status', ['active', 'trial'])
            ->first();
    }

    private function isCentralDomain(Request $request): bool
    {
        $host = strtolower($request->getHost());

        return in_array($host, array_map('strtolower', config('saas.central_domains', [])), true);
    }
}