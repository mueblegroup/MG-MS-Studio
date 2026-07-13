<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlanTrialController extends Controller
{
    public function update(Request $request, PlatformSubscriptionPlan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'trial_days' => ['required', 'integer', 'min:0', 'max:365'],
        ]);

        $plan->update([
            'trial_days' => (int) $validated['trial_days'],
        ]);

        return redirect()
            ->route('superadmin.subscription-plans.index')
            ->with('success', $plan->trial_days > 0
                ? "{$plan->trial_days}-day free trial enabled for {$plan->name}."
                : "Free trial disabled for {$plan->name}.");
    }
}
