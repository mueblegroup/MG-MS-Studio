<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictStaffShopPurchases
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, ['admin', 'teacher'], true)) {
            return $next($request);
        }

        $warning = 'Purchasing is disabled for administrator and teacher accounts. Please use a student account to buy classes, plans, or class cards.';

        if ($request->routeIs('shop.index')) {
            session()->now('error', $warning);
            return $next($request);
        }

        if ($request->routeIs('shop.*')) {
            return redirect()->route('shop.index')->with('error', $warning);
        }

        return $next($request);
    }
}
