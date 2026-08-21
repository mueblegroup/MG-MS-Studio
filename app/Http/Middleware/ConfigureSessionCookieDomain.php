<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigureSessionCookieDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        $rootDomain = strtolower((string) config('saas.root_domain'));
        $centralDomains = array_map('strtolower', (array) config('saas.central_domains', []));
        $configuredDomain = env('SESSION_DOMAIN');

        $isPlatformHost = in_array($host, $centralDomains, true)
            || ($rootDomain !== '' && ($host === $rootDomain || str_ends_with($host, '.'.$rootDomain)));

        // A Domain cookie can only target the current host or one of its parent
        // domains. Custom studio domains are unrelated to the SaaS root domain,
        // so they must use a host-only session cookie.
        config([
            'session.domain' => $isPlatformHost && filled($configuredDomain)
                ? (string) $configuredDomain
                : null,
        ]);

        return $next($request);
    }
}
