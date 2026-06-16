<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCentralDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isCentralHost($request)) {
            return $next($request);
        }

        if ($request->user()) {
            return match ($request->user()->role) {
                'superadmin' => redirect()->away($this->centralUrl('/superadmin/dashboard')),
                'admin' => redirect()->route('admin.dashboard'),
                'teacher' => redirect()->route('teacher.dashboard'),
                'student' => redirect()->route('student.dashboard'),
                default => redirect()->route('login'),
            };
        }

        return redirect()->route('login');
    }

    private function isCentralHost(Request $request): bool
    {
        $host = strtolower($request->getHost());
        $centralDomains = array_values(array_unique(array_filter(array_map(
            static fn ($domain) => strtolower(trim((string) $domain)),
            config('saas.central_domains', [])
        ))));

        return in_array($host, $centralDomains, true);
    }

    private function centralUrl(string $path = '/'): string
    {
        $base = rtrim((string) config('app.url'), '/');

        if ($base === '') {
            $centralDomain = collect(config('saas.central_domains', []))->first();
            $base = $centralDomain ? 'https://' . $centralDomain : url('/');
        }

        return $base . '/' . ltrim($path, '/');
    }
}
