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

        if ($this->isPlatformWebhookPath($request)) {
            return $next($request);
        }

        if ($this->isCentralDomain($request)) {
            return $next($request);
        }

        $hostStudio = $this->resolveFromHost($request);
        $userStudio = $this->resolveFromAuthenticatedUser($request);

        if ($hostStudio) {
            if ($request->user()) {
                $this->authorizeUserForStudio($request, $hostStudio);
            }

            app(TenantManager::class)->set($hostStudio);
            $request->attributes->set('studio', $hostStudio);

            return $next($request);
        }

        if ($this->isCentralPlatformPath($request)) {
            return $next($request);
        }

        if ($userStudio && $this->isStudioAreaPath($request)) {
            app(TenantManager::class)->set($userStudio);
            $request->attributes->set('studio', $userStudio);

            return $next($request);
        }

        if (app()->environment(['local', 'testing']) && $this->isLocalDevelopmentHost($request)) {
            return $next($request);
        }

        abort(404, 'Studio not found or subscription access has ended.');
    }

    private function authorizeUserForStudio(Request $request, Studio $studio): void
    {
        $user = $request->user();

        if ($user->role === 'superadmin') {
            return;
        }

        if ($user->studio_id && (int) $user->studio_id === (int) $studio->id) {
            return;
        }

        if ($user->role === 'admin' && ! $user->studio_id && (int) $studio->owner_user_id === (int) $user->id) {
            return;
        }

        abort(403, 'This account is not assigned to this studio portal.');
    }

    private function resolveFromAuthenticatedUser(Request $request): ?Studio
    {
        $user = $request->user();

        if (! $user || ! $user->studio_id) {
            return null;
        }

        $studio = Studio::query()->find($user->studio_id);

        return $studio?->isActive() ? $studio : null;
    }

    private function resolveFromHost(Request $request): ?Studio
    {
        $host = strtolower($request->getHost());

        if ($this->isCentralDomain($request) || $this->isRootDomain($request)) {
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
            $studio = Studio::query()->where('subdomain', $subdomain)->first();

            return $studio?->isActive() ? $studio : null;
        }

        $studio = Studio::query()->where('custom_domain', $host)->first();

        return $studio?->isActive() ? $studio : null;
    }

    private function isCentralDomain(Request $request): bool
    {
        $host = strtolower($request->getHost());
        $centralDomains = array_map('strtolower', config('saas.central_domains', []));

        return in_array($host, $centralDomains, true);
    }

    private function isRootDomain(Request $request): bool
    {
        $rootDomain = strtolower((string) config('saas.root_domain'));

        return $rootDomain !== '' && strtolower($request->getHost()) === $rootDomain;
    }

    private function isPlatformWebhookPath(Request $request): bool
    {
        return $request->is('webhooks/platform-stripe')
            || $request->is('webhooks/stripe')
            || $request->is('webhooks/hitpay');
    }

    private function isCentralPlatformPath(Request $request): bool
    {
        if (! $this->isRootDomain($request) && ! $this->isCentralDomain($request)) {
            return false;
        }

        return $request->is('/')
            || $request->is('login')
            || $request->is('register')
            || $request->is('forgot-password')
            || $request->is('reset-password*')
            || $request->is('customer*')
            || $request->is('superadmin*')
            || $request->is('institutes*');
    }

    private function isStudioAreaPath(Request $request): bool
    {
        return $request->is('admin*') || $request->is('teacher*') || $request->is('student*');
    }

    private function isLocalDevelopmentHost(Request $request): bool
    {
        $host = strtolower($request->getHost());

        return in_array($host, ['localhost', '127.0.0.1'], true)
            || str_ends_with($host, '.test');
    }
}
