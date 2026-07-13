<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        $studio = app(TenantManager::class)->current();

        return view('auth.login', [
            'studio' => $studio,
            'portalType' => $studio ? 'studio' : 'central',
            'studentSelfRegistrationEnabled' => ! $studio
                || (bool) data_get($studio->settings, 'allow_student_self_registration', true),
            'loginTitle' => $studio ? $studio->name.' Studio Login' : 'Mueble Studio Client Portal',
            'loginSubtitle' => $studio
                ? 'Login to manage this studio, its teachers, students, classes, attendance and payments.'
                : 'Login to register studios, manage your studio portals, subdomains and platform setup.',
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();
        $studio = app(TenantManager::class)->current();

        if (! $studio) {
            if ($user->role === 'superadmin') {
                return redirect()->intended(route('superadmin.dashboard', absolute: false));
            }

            return redirect()->intended(route('customer.dashboard', absolute: false));
        }

        if ($user->role === 'superadmin' || $user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        if ($user->role === 'teacher') {
            return redirect()->intended(route('teacher.dashboard', absolute: false));
        }

        if ($user->role === 'student') {
            return redirect()->intended(route('student.dashboard', absolute: false));
        }

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
