<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditAuthenticatedActions
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && ! in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            $routeName = (string) ($request->route()?->getName() ?? 'unnamed');

            if (! str_starts_with($routeName, 'two-factor.') && ! str_starts_with($routeName, 'logout')) {
                app(AuditLogService::class)->record('action.'.$routeName, metadata: [
                    'status_code' => $response->getStatusCode(),
                    'input_keys' => array_values(array_diff(array_keys($request->all()), [
                        '_token', 'password', 'password_confirmation', 'current_password', 'code', 'recovery_code',
                    ])),
                ]);
            }
        }

        return $response;
    }
}
