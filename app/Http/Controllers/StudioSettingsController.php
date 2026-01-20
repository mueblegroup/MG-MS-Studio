<?php

namespace App\Http\Controllers;

use App\Services\StudioSettingsService;
use Illuminate\Http\Request;

class StudioSettingsController extends Controller
{
    public function edit(StudioSettingsService $settings)
    {
        $data = [
            'studio_name' => $settings->get('studio_name', config('app.name')),
            'currency' => $settings->get('currency', 'MYR'),
            'default_payment_provider' => $settings->get('default_payment_provider', 'stripe'),
            'shop_class_early_cutoff_days' => (int) $settings->get('shop_class_early_cutoff_days', (int) env('SHOP_CLASS_EARLY_CUTOFF_DAYS', 0)),
            'shop_plan_early_cutoff_days' => (int) $settings->get('shop_plan_early_cutoff_days', (int) env('SHOP_PLAN_EARLY_CUTOFF_DAYS', 0)),
        ];

        return view('admin.settings.studio', compact('data'));
    }

    public function update(Request $request, StudioSettingsService $settings)
    {
        $validated = $request->validate([
            'studio_name' => 'required|string|max:120',
            'currency' => 'required|string|max:10', // you can restrict to list later
            'default_payment_provider' => 'required|in:stripe,hitpay',

            'shop_class_early_cutoff_days' => 'required|integer|min:0|max:365',
            'shop_plan_early_cutoff_days' => 'required|integer|min:0|max:365',
        ]);

        $settings->setMany([
            'studio_name' => $validated['studio_name'],
            'currency' => strtoupper($validated['currency']),
            'default_payment_provider' => $validated['default_payment_provider'],
            'shop_class_early_cutoff_days' => (int) $validated['shop_class_early_cutoff_days'],
            'shop_plan_early_cutoff_days' => (int) $validated['shop_plan_early_cutoff_days'],
        ]);

        return back()->with('success', 'Studio settings updated.');
    }
}
