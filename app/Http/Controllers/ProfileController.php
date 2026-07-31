<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\TwoFactorAuthenticationService;
use App\Support\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request, TwoFactorAuthenticationService $twoFactor): View|RedirectResponse
    {
        if (! app(TenantManager::class)->current() && $request->user()?->role === 'admin') {
            return redirect()->route('customer.account');
        }

        $user = $request->user();
        $this->ensureTwoFactorSetup($user, $twoFactor);

        return view('profile.edit', [
            'user' => $user->fresh(),
            'twoFactorProvisioningUri' => $twoFactor->provisioningUri((string) $user->two_factor_secret, $user->email),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $originalPhone = $user->phone_number;
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($originalPhone !== $user->phone_number) {
            $user->phone_verified_at = null;
        }

        $user->save();

        if ($user->role === 'admin' && ! $user->studio_id && $user->hasCompleteClientProfile()) {
            $user->forceFill(['profile_completed_at' => now()])->save();

            return redirect()
                ->route('customer.dashboard')
                ->with('success', 'Your profile is complete. Client portal access is now enabled.');
        }

        return Redirect::back()->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', ['password' => ['required', 'current_password']]);
        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function ensureTwoFactorSetup($user, TwoFactorAuthenticationService $twoFactor): void
    {
        if ($user->hasTwoFactorEnabled() || $user->two_factor_secret) {
            return;
        }

        $user->forceFill([
            'two_factor_secret' => $twoFactor->generateSecret(),
            'two_factor_recovery_codes' => $twoFactor->generateRecoveryCodes(),
        ])->save();
    }
}
