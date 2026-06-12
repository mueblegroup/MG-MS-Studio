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
        app(TenantManager::class)->clear();

        $hostStudio = $this->resolveFromHost($request);
        $userStudio = $this->resolveFromAuthenticatedUser($request);

        if ($hostStudio) {
            if ($request->user() && $request->user()->studio_id && (int) $request->user()->studio_id !== (int) $hostStudio->id) {
                abort(403, 'This account is not assigned to this studio portal.');
            }

            app(TenantManager::class)->set($hostStudio);
            $request->attributes->set('studio', $hostStudio);

            return $next($request);
        }

        if ($this->isCentralDomain($request)) {
            if ($userStudio) {
                app(TenantManager::class)->set($userStudio);
                $request->attributes->set('studio', $userStudio);
            }

            return $next($request);
        }

        if (app()->environment(['local', 'testing'])) {
            $fallback = $userStudio ?: Studio::query()->where('id', 1)->first();

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

        if ($this->isCentralDomain($request)) {
            return null;
        }

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
