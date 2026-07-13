<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Studio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
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

        return view('customer.account', [
            'user' => $user,
            'studio' => $studio,
        ]);
    }
}
