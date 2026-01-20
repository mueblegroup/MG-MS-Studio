<?php

namespace App\Http\Middleware;

// app/Http/Middleware/RedirectIfAuthenticated.php
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $role = Auth::user()->role;

                if ($role === 'admin') {
                    return redirect('/admin/dashboard');
                } elseif ($role === 'teacher') {
                    return redirect('/teacher/dashboard');
                }
                return redirect('/dashboard'); // Default for students
            }
        }
        return $next($request);
    }
}
