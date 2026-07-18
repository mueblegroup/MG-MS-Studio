<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\TwoFactorAuthenticationService;
use App\Support\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorAuthenticationController extends Controller
{
    public function edit(Request $request): RedirectResponse
    {
        if (! app(TenantManager::class)->current() && $request->user()?->role === 'admin') {
            return redirect()->route('customer.account', ['#two-factor']);
        }

        return redirect()->route('profile.edit', ['#two-factor']);
    }

    public function enable(Request $request, TwoFactorAuthenticationService $twoFactor, AuditLogService $audit): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'current_password'],
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! $user->two_factor_secret || ! $twoFactor->verify($user->two_factor_secret, $validated['code'])) {
            return back()->withErrors(['code' => 'The authentication code is invalid.']);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();
        $audit->record('two_factor.enabled', $user);

        return back()->with('status', 'two-factor-enabled');
    }

    public function disable(Request $request, AuditLogService $audit): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);
        $user = $request->user();
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
        $audit->record('two_factor.disabled', $user);

        return back()->with('status', 'two-factor-disabled');
    }

    public function regenerate(Request $request, TwoFactorAuthenticationService $twoFactor, AuditLogService $audit): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);
        $user = $request->user();
        abort_unless($user->hasTwoFactorEnabled(), 409);
        $user->forceFill(['two_factor_recovery_codes' => $twoFactor->generateRecoveryCodes()])->save();
        $audit->record('two_factor.recovery_codes_regenerated', $user);

        return back()->with('status', 'recovery-codes-regenerated');
    }

    public function challenge(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('two_factor_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function verifyChallenge(Request $request, TwoFactorAuthenticationService $twoFactor, AuditLogService $audit): RedirectResponse
    {
        $request->validate([
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $user = User::query()->findOrFail((int) $request->session()->get('two_factor_user_id'));
        $verified = filled($request->input('code'))
            && $twoFactor->verify((string) $user->two_factor_secret, (string) $request->input('code'));

        if (! $verified && filled($request->input('recovery_code'))) {
            $submitted = strtoupper(trim((string) $request->input('recovery_code')));
            $codes = $user->two_factor_recovery_codes ?? [];
            $index = array_search($submitted, $codes, true);

            if ($index !== false) {
                unset($codes[$index]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();
                $verified = true;
            }
        }

        if (! $verified) {
            $audit->record('two_factor.challenge_failed', $user);
            return back()->withErrors(['code' => 'The authentication or recovery code is invalid.']);
        }

        Auth::login($user, (bool) $request->session()->pull('two_factor_remember', false));
        $request->session()->forget('two_factor_user_id');
        $request->session()->regenerate();
        $audit->record('two_factor.challenge_passed', $user);

        return redirect()->intended('/');
    }
}
