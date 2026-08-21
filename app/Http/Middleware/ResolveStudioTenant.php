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

        // The platform billing webhook uses central/platform Stripe credentials.
        // Studio Stripe/HitPay webhooks must continue through host resolution so
        // the studio's own gateway credentials are loaded by later middleware.
        if ($this->isPlatformWebhookPath($request)) {
            return $next($request);
        }

        if ($this->isCentralDomain($request)) {
            return $next($request);
        }

        $hostStudio = $this->resolveStudioFromHost($request);
        $userStudio = $this->resolveFromAuthenticatedUser($request);

        if ($hostStudio) {
            // Payment providers can deliver a delayed webhook after a studio's
            // SaaS status changes. Keep accepting signed tenant payment events
            // so local financial/subscription state cannot become stale.
            if (! $hostStudio->isActive() && ! $this->isTenantPaymentWebhookPath($request)) {
                return response()->view('errors.studio-inactive', [
                    'studio' => $hostStudio,
                    'billingUrl' => $this->centralUrl('/customer/billing'),
                    'loginUrl' => $this->centralUrl('/login'),
                ], 402);
            }

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

        abort(404, 'Studio not found.');
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

    private function resolveStudioFromHost(Request $request): ?Studio
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

        if ($domain?->studio) {
            return $domain->studio;
        }

        $rootDomain = strtolower((string) config('saas.root_domain'));

        if ($rootDomain && str_ends_with($host, '.' . $rootDomain)) {
            $subdomain = str_replace('.' . $rootDomain, '', $host);
            return Studio::query()->where('subdomain', $subdomain)->first();
        }

        return Studio::query()->where('custom_domain', $host)->first();
    }

    private function centralUrl(string $path = '/'): string
    {
        $base = rtrim((string) config('app.url'), '/');
        return $base . '/' . ltrim($path, '/');
    }

    private function isCentralDomain(Request $request): bool
    {
        return in_array(strtolower($request->getHost()), array_map('strtolower', config('saas.central_domains', [])), true);
    }

    private function isRootDomain(Request $request): bool
    {
        $rootDomain = strtolower((string) config('saas.root_domain'));
        return $rootDomain !== '' && strtolower($request->getHost()) === $rootDomain;
    }

    private function isPlatformWebhookPath(Request $request): bool
    {
        return $request->is('webhooks/platform-stripe');
    }

    private function isTenantPaymentWebhookPath(Request $request): bool
    {
        return $request->is('webhooks/stripe') || $request->is('webhooks/hitpay');
    }

    private function isCentralPlatformPath(Request $request): bool
    {
        if (! $this->isRootDomain($request) && ! $this->isCentralDomain($request)) {
            return false;
        }

        return $request->is('/') || $request->is('login') || $request->is('register') || $request->is('forgot-password')
            || $request->is('reset-password*') || $request->is('customer*') || $request->is('superadmin*') || $request->is('institutes*');
    }

    private function isStudioAreaPath(Request $request): bool
    {
        return $request->is('admin*') || $request->is('teacher*') || $request->is('student*');
    }

    private function isLocalDevelopmentHost(Request $request): bool
    {
        $host = strtolower($request->getHost());
        return in_array($host, ['localhost', '127.0.0.1'], true) || str_ends_with($host, '.test');
    }
}
