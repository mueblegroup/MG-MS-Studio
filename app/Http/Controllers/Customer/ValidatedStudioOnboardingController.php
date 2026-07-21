<?php

namespace App\Http\Controllers\Customer;

use App\Services\StudioOnboardingPaymentService;
use App\Support\StudioLocaleOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ValidatedStudioOnboardingController extends StudioOnboardingController
{
    public function create(Request $request): View|RedirectResponse
    {
        $response = parent::create($request);

        if ($response instanceof View) {
            $response->with([
                'timezoneOptions' => StudioLocaleOptions::timezones(),
                'currencyOptions' => StudioLocaleOptions::currencies(),
            ]);
        }

        return $response;
    }

    public function store(Request $request, StudioOnboardingPaymentService $payments): RedirectResponse
    {
        $request->validate([
            'timezone' => ['required', 'string', Rule::in(array_keys(StudioLocaleOptions::timezones()))],
            'currency' => ['required', 'string', Rule::in(array_keys(StudioLocaleOptions::currencies()))],
        ]);

        return parent::store($request, $payments);
    }
}
