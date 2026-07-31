<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSsoProvider;
use App\Services\PlatformSsoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SsoSettingsController extends Controller
{
    public function index(): View
    {
        $providers = collect(PlatformSsoService::PROVIDERS)->mapWithKeys(function (string $provider): array {
            return [$provider => PlatformSsoProvider::query()->firstOrCreate(['provider' => $provider])];
        });

        return view('superadmin.sso.index', compact('providers'));
    }

    public function update(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, PlatformSsoService::PROVIDERS, true), 404);

        $settings = PlatformSsoProvider::query()->firstOrCreate(['provider' => $provider]);

        $validated = $request->validate([
            'is_enabled' => ['nullable', 'boolean'],
            'client_id' => ['required', 'string', 'max:1000'],
            'client_secret' => [Rule::requiredIf(blank($settings->client_secret)), 'nullable', 'string', 'max:10000'],
            'tenant_id' => [$provider === 'microsoft' ? 'nullable' : 'prohibited', 'nullable', 'string', 'max:255'],
            'secret_expires_at' => [$provider === 'apple' ? 'nullable' : 'prohibited', 'nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:4000'],
        ]);

        $data = [
            'is_enabled' => $request->boolean('is_enabled'),
            'client_id' => trim($validated['client_id']),
            'tenant_id' => $provider === 'microsoft' ? ($validated['tenant_id'] ?? null) : null,
            'secret_expires_at' => $provider === 'apple' ? ($validated['secret_expires_at'] ?? null) : null,
            'notes' => $validated['notes'] ?? null,
        ];

        if (filled($validated['client_secret'] ?? null)) {
            $data['client_secret'] = $validated['client_secret'];
        }

        $settings->update($data);

        return back()->with('success', ucfirst($provider).' SSO settings updated.');
    }
}
