<?php

namespace App\Http\Controllers;

use App\Models\StudioPaymentGateway;
use App\Support\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Throwable;

class StudioPaymentGatewayController extends Controller
{
    public function index()
    {
        $studio = app(TenantManager::class)->current();
        abort_unless($studio, 404);

        $gateways = StudioPaymentGateway::query()
            ->where('studio_id', $studio->id)
            ->whereIn('provider', ['stripe', 'hitpay'])
            ->get()
            ->keyBy('provider');

        return view('admin.settings.payment-gateways', [
            'studio' => $studio,
            'stripe' => $gateways->get('stripe'),
            'hitpay' => $gateways->get('hitpay'),
            'stripeWebhookUrl' => route('webhooks.stripe'),
            'hitpayWebhookUrl' => route('webhooks.hitpay'),
        ]);
    }

    public function update(Request $request, string $provider)
    {
        $provider = strtolower($provider);
        abort_unless(in_array($provider, ['stripe', 'hitpay'], true), 404);

        $studio = app(TenantManager::class)->current();
        abort_unless($studio, 404);

        $gateway = StudioPaymentGateway::query()->firstOrNew([
            'studio_id' => $studio->id,
            'provider' => $provider,
        ]);

        if ($provider === 'stripe') {
            $validated = $request->validate([
                'enabled' => ['nullable', 'boolean'],
                'environment' => ['required', Rule::in(['sandbox', 'production'])],
                'publishable_key' => ['nullable', 'string', 'max:500'],
                'secret_key' => ['nullable', 'string', 'max:500'],
                'webhook_secret' => ['nullable', 'string', 'max:500'],
            ]);

            $existing = (array) ($gateway->credentials ?? []);
            $gateway->fill([
                'enabled' => $request->boolean('enabled'),
                'environment' => $validated['environment'],
                'credentials' => [
                    'publishable_key' => $request->filled('publishable_key') ? $validated['publishable_key'] : ($existing['publishable_key'] ?? ''),
                    'secret_key' => $request->filled('secret_key') ? $validated['secret_key'] : ($existing['secret_key'] ?? ''),
                ],
                'webhook_secret' => $request->filled('webhook_secret') ? $validated['webhook_secret'] : $gateway->webhook_secret,
            ])->save();
        } else {
            $validated = $request->validate([
                'enabled' => ['nullable', 'boolean'],
                'environment' => ['required', Rule::in(['sandbox', 'production'])],
                'api_key' => ['nullable', 'string', 'max:500'],
                'salt' => ['nullable', 'string', 'max:500'],
                'event_webhook_salt_key' => ['nullable', 'string', 'max:500'],
            ]);

            $existing = (array) ($gateway->credentials ?? []);
            $gateway->fill([
                'enabled' => $request->boolean('enabled'),
                'environment' => $validated['environment'],
                'credentials' => [
                    'api_key' => $request->filled('api_key') ? $validated['api_key'] : ($existing['api_key'] ?? ''),
                    'salt' => $request->filled('salt') ? $validated['salt'] : ($existing['salt'] ?? ''),
                ],
                'webhook_secret' => $request->filled('event_webhook_salt_key') ? $validated['event_webhook_salt_key'] : $gateway->webhook_secret,
            ])->save();
        }

        return back()->with('success', ucfirst($provider).' payment gateway settings saved.');
    }

    public function test(Request $request, string $provider)
    {
        $provider = strtolower($provider);
        abort_unless(in_array($provider, ['stripe', 'hitpay'], true), 404);

        $studio = app(TenantManager::class)->current();
        abort_unless($studio, 404);

        $gateway = StudioPaymentGateway::query()
            ->where('studio_id', $studio->id)
            ->where('provider', $provider)
            ->firstOrFail();

        try {
            if (! $gateway->enabled) {
                throw new \RuntimeException('Enable the gateway before testing it.');
            }

            $credentials = (array) $gateway->credentials;

            if ($provider === 'stripe') {
                $secret = (string) ($credentials['secret_key'] ?? '');
                if ($secret === '') {
                    throw new \RuntimeException('Stripe secret key is missing.');
                }

                $client = new \Stripe\StripeClient($secret);
                $account = $client->accounts->retrieve();
                $message = 'Connected to Stripe account '.($account->business_profile?->name ?: $account->id).'.';
            } else {
                $apiKey = (string) ($credentials['api_key'] ?? '');
                if ($apiKey === '') {
                    throw new \RuntimeException('HitPay API key is missing.');
                }

                $baseUrl = $gateway->environment === 'production'
                    ? 'https://api.hit-pay.com/v1'
                    : 'https://api.sandbox.hit-pay.com/v1';

                $response = Http::withHeaders([
                    'X-BUSINESS-API-KEY' => $apiKey,
                    'Accept' => 'application/json',
                ])->get($baseUrl.'/recurring-billing');

                if (! $response->successful()) {
                    throw new \RuntimeException('HitPay returned HTTP '.$response->status().'.');
                }

                $message = 'HitPay API connection succeeded.';
            }

            $gateway->forceFill([
                'last_tested_at' => now(),
                'last_test_status' => 'success',
                'last_test_message' => $message,
            ])->save();

            return back()->with('success', $message);
        } catch (Throwable $exception) {
            $gateway->forceFill([
                'last_tested_at' => now(),
                'last_test_status' => 'failed',
                'last_test_message' => $exception->getMessage(),
            ])->save();

            return back()->withErrors(['gateway' => $exception->getMessage()]);
        }
    }
}
