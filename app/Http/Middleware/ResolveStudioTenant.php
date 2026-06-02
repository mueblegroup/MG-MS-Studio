<?php

namespace App\Http\Middleware;

use App\Models\Studio;
use App\Models\StudioDomain;
use App\Support\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class ResolveStudioTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $studio = $this->resolve($request) ?: $this->fallbackStudio();

        app(TenantManager::class)->set($studio);

        if ($studio) {
            app()->instance('studio', $studio);
            app()->instance('studio.id', $studio->id);
        }

        return $next($request);
    }

    protected function resolve(Request $request): ?Studio
    {
        if (!Schema::hasTable('studios')) {
            return null;
        }

        $host = strtolower($request->getHost());

        if (Schema::hasTable('studio_domains')) {
            $domain = StudioDomain::query()
                ->where('domain', $host)
                ->first();

            if ($domain?->studio) {
                return $domain->studio;
            }
        }

        $parts = explode('.', $host);
        $subdomain = count($parts) >= 3 ? $parts[0] : null;

        if ($subdomain && !in_array($subdomain, ['www', 'app', 'admin'], true)) {
            $studio = Studio::query()
                ->where('subdomain', $subdomain)
                ->orWhere('slug', $subdomain)
                ->first();

            if ($studio) {
                return $studio;
            }
        }

        return null;
    }

    protected function fallbackStudio(): ?Studio
    {
        if (!Schema::hasTable('studios')) {
            return null;
        }

        return Studio::query()->where('id', 1)->first();
    }
}
