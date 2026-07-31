<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'admin' || $user->studio_id) {
            return $next($request);
        }

        if ($user->hasCompleteClientProfile()) {
            return $next($request);
        }

        return redirect()
            ->route('customer.account')
            ->with('warning', 'Complete all required personal and organisation details before accessing the client portal.');
    }
}
