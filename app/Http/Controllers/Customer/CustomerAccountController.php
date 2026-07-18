<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Studio;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function edit(Request $request, TwoFactorAuthenticationService $twoFactor): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->role === 'superadmin') {
            return redirect()->route('profile.edit');
        }

        abort_unless($user->role === 'admin', 403, 'Client account access is only available to client owner accounts.');

        $studio = Studio::query()
            ->where('owner_user_id', $user->id)
            ->first();

        if ($studio) {
            abort_unless((int) $studio->owner_user_id === (int) $user->id, 403, 'Only the studio owner can manage this client account.');
        }

        if (! $user->hasTwoFactorEnabled() && ! $user->two_factor_secret) {
            $user->forceFill([
                'two_factor_secret' => $twoFactor->generateSecret(),
                'two_factor_recovery_codes' => $twoFactor->generateRecoveryCodes(),
            ])->save();
        }

        return view('customer.account', [
            'user' => $user->fresh(),
            'studio' => $studio,
            'twoFactorProvisioningUri' => $twoFactor->provisioningUri((string) $user->two_factor_secret, $user->email),
        ]);
    }
}
