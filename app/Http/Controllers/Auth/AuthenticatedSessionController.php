<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditLogService;
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

    public function store(LoginRequest $request, AuditLogService $audit): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();
        $studio = app(TenantManager::class)->current();
        $destination = $this->destinationFor($user->role, (bool) $studio);

        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put([
                'two_factor_user_id' => $user->id,
                'two_factor_remember' => $request->boolean('remember'),
                'url.intended' => $destination,
            ]);
            Auth::logout();

            return redirect()->route('two-factor.challenge');
        }

        $audit->record('authentication.login', $user);

        return redirect()->intended($destination);
    }

    public function destroy(Request $request, AuditLogService $audit): RedirectResponse
    {
        $audit->record('authentication.logout', $request->user());
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function destinationFor(string $role, bool $hasStudio): string
    {
        if (! $hasStudio) {
            return $role === 'superadmin'
                ? route('superadmin.dashboard', absolute: false)
                : route('customer.dashboard', absolute: false);
        }

        return match ($role) {
            'superadmin', 'admin' => route('admin.dashboard', absolute: false),
            'teacher' => route('teacher.dashboard', absolute: false),
            'student' => route('student.dashboard', absolute: false),
            default => route('login', absolute: false),
        };
    }
}
