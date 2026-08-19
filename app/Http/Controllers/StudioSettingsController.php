<?php

namespace App\Http\Controllers;

use App\Models\StudioPaymentGateway;
use App\Services\StudioSettingsService;
use App\Support\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class StudioSettingsController extends Controller
{
    public function edit(StudioSettingsService $settings)
    {
        $data = [
            'studio_name' => $settings->get('studio_name', config('app.name')),
            'studio_display_name' => $settings->get('studio_display_name', config('app.name')),
            'currency' => $settings->get('currency', 'MYR'),
            'default_payment_provider' => $settings->get('default_payment_provider', 'stripe'),
            'shop_class_early_cutoff_days' => (int) $settings->get('shop_class_early_cutoff_days', (int) env('SHOP_CLASS_EARLY_CUTOFF_DAYS', 0)),
            'shop_plan_early_cutoff_days' => (int) $settings->get('shop_plan_early_cutoff_days', (int) env('SHOP_PLAN_EARLY_CUTOFF_DAYS', 0)),

            'mail_enabled' => (bool) $settings->get('mail_enabled', false),
            'mail_mailer' => $settings->get('mail_mailer', env('MAIL_MAILER', 'smtp')),
            'mail_host' => $settings->get('mail_host', env('MAIL_HOST', '')),
            'mail_port' => $settings->get('mail_port', env('MAIL_PORT', 587)),
            'mail_username' => $settings->get('mail_username', env('MAIL_USERNAME', '')),
            'mail_password' => $settings->get('mail_password', env('MAIL_PASSWORD', '')),
            'mail_encryption' => $settings->get('mail_encryption', env('MAIL_ENCRYPTION', 'tls')),
            'mail_from_address' => $settings->get('mail_from_address', env('MAIL_FROM_ADDRESS', '')),
            'mail_from_name' => $settings->get('mail_from_name', env('MAIL_FROM_NAME', config('app.name'))),
            'mail_ehlo_domain' => $settings->get('mail_ehlo_domain', env('MAIL_EHLO_DOMAIN', parse_url((string) config('app.url'), PHP_URL_HOST))),
        ];

        $envIssues = $this->getStudioSetupIssues($data);

        return view('admin.settings.studio', compact('data', 'envIssues'));
    }

    public function update(Request $request, StudioSettingsService $settings)
    {
        $validated = $request->validate([
            'studio_name' => 'required|string|max:120',
            'studio_display_name' => 'required|string|max:120',
            'currency' => 'required|string|max:10',
            'default_payment_provider' => 'required|in:stripe,hitpay',

            'shop_class_early_cutoff_days' => 'required|integer|min:0|max:365',
            'shop_plan_early_cutoff_days' => 'required|integer|min:0|max:365',

            'mail_enabled' => 'nullable|boolean',
            'mail_mailer' => 'required|string|in:smtp,log,array,sendmail',
            'mail_host' => 'nullable|string|max:255|required_if:mail_enabled,1',
            'mail_port' => 'nullable|integer|min:1|max:65535|required_if:mail_enabled,1',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl,none',
            'mail_from_address' => 'nullable|email|max:255|required_if:mail_enabled,1',
            'mail_from_name' => 'nullable|string|max:255|required_if:mail_enabled,1',
            'mail_ehlo_domain' => 'nullable|string|max:255',
        ]);

        $existingPassword = (string) $settings->get('mail_password', env('MAIL_PASSWORD', ''));
        $newPassword = $request->filled('mail_password') ? (string) $validated['mail_password'] : $existingPassword;

        $settings->setMany([
            'studio_name' => $validated['studio_name'],
            'studio_display_name' => $validated['studio_display_name'],
            'currency' => strtoupper($validated['currency']),
            'default_payment_provider' => $validated['default_payment_provider'],
            'shop_class_early_cutoff_days' => (int) $validated['shop_class_early_cutoff_days'],
            'shop_plan_early_cutoff_days' => (int) $validated['shop_plan_early_cutoff_days'],

            'mail_enabled' => $request->boolean('mail_enabled') ? 'true' : 'false',
            'mail_mailer' => $validated['mail_mailer'],
            'mail_host' => $validated['mail_host'] ?? '',
            'mail_port' => (int) ($validated['mail_port'] ?? 587),
            'mail_username' => $validated['mail_username'] ?? '',
            'mail_password' => $newPassword,
            'mail_encryption' => ($validated['mail_encryption'] ?? 'tls') === 'none' ? '' : ($validated['mail_encryption'] ?? 'tls'),
            'mail_from_address' => $validated['mail_from_address'] ?? '',
            'mail_from_name' => $validated['mail_from_name'] ?? config('app.name'),
            'mail_ehlo_domain' => $validated['mail_ehlo_domain'] ?? '',
        ]);

        $provider = (string) $validated['default_payment_provider'];
        if (! $this->gatewayReady($provider)) {
            return redirect()
                ->route('settings.payment-gateways.index', ['provider' => $provider])
                ->with('gateway_setup_notice', ucfirst($provider).' is selected as your payment gateway. Complete and enable its studio credentials before accepting payments.');
        }

        return back()->with('success', 'Studio settings updated.');
    }

    public function sendTestEmail(Request $request, StudioSettingsService $settings)
    {
        $validated = $request->validate([
            'test_email' => 'required|email|max:255',
        ]);

        if (!$settings->get('mail_enabled', false)) {
            return back()->withErrors([
                'test_email' => 'Custom mail server is disabled. Enable it, save settings, then send a test email.',
            ]);
        }

        $mailer = (string) config('mail.default', 'log');
        $host = (string) config('mail.mailers.smtp.host', '');
        $port = (string) config('mail.mailers.smtp.port', '');
        $fromAddress = (string) config('mail.from.address', '');
        $fromName = (string) config('mail.from.name', config('app.name'));

        try {
            Mail::raw(
                "This is a test email from {$fromName}.\n\n" .
                "Mailer: {$mailer}\n" .
                "Host: {$host}\n" .
                "Port: {$port}\n" .
                "Sent at: " . now()->format('Y-m-d H:i:s') . "\n",
                function ($message) use ($validated, $fromAddress, $fromName) {
                    $message->to($validated['test_email'])
                        ->subject('Test Email - ' . $fromName);

                    if ($fromAddress !== '') {
                        $message->from($fromAddress, $fromName);
                    }
                }
            );

            return back()->with('mail_test_success', "Test email sent to {$validated['test_email']} using {$mailer}.");
        } catch (Throwable $e) {
            report($e);

            return back()->with('mail_test_error', $e->getMessage());
        }
    }

    private function getStudioSetupIssues(array $data): array
    {
        $issues = [];

        if (blank($data['studio_name'] ?? null)) {
            $issues[] = [
                'type' => 'Studio Setting',
                'key' => 'studio_name',
                'message' => 'Studio Name is empty. Add it in this Studio Settings page.',
                'link' => route('settings.studio'),
            ];
        }

        if (blank($data['studio_display_name'] ?? null)) {
            $issues[] = [
                'type' => 'Studio Setting',
                'key' => 'studio_display_name',
                'message' => 'Studio Display Name is empty. Add it in this Studio Settings page.',
                'link' => route('settings.studio'),
            ];
        }

        if (blank($data['currency'] ?? null)) {
            $issues[] = [
                'type' => 'Studio Setting',
                'key' => 'currency',
                'message' => 'Currency is empty. Add a default currency like MYR.',
                'link' => route('settings.studio'),
            ];
        }

        $provider = strtolower((string) ($data['default_payment_provider'] ?? 'stripe'));
        if (in_array($provider, ['stripe', 'hitpay'], true) && ! $this->gatewayReady($provider)) {
            $issues[] = [
                'type' => 'Payment Gateway',
                'key' => ucfirst($provider),
                'message' => ucfirst($provider).' is selected for this studio but its own credentials are not fully configured and enabled. Configure the studio gateway before accepting student payments.',
                'link' => route('settings.payment-gateways.index', ['provider' => $provider]),
            ];
        }

        if (!empty($data['mail_enabled'])) {
            foreach ([
                'mail_host' => 'SMTP Host is required when custom mail server is enabled.',
                'mail_port' => 'SMTP Port is required when custom mail server is enabled.',
                'mail_from_address' => 'From Email is required when custom mail server is enabled.',
                'mail_from_name' => 'From Name is required when custom mail server is enabled.',
            ] as $key => $message) {
                if (blank($data[$key] ?? null)) {
                    $issues[] = [
                        'type' => 'Studio Setting',
                        'key' => $key,
                        'message' => $message,
                        'link' => route('settings.studio'),
                    ];
                }
            }
        }

        return $issues;
    }

    private function gatewayReady(string $provider): bool
    {
        $studio = app(TenantManager::class)->current();
        if (! $studio) {
            return false;
        }

        $gateway = StudioPaymentGateway::query()
            ->where('studio_id', $studio->id)
            ->where('provider', $provider)
            ->first();

        if (! $gateway || ! $gateway->enabled) {
            return false;
        }

        $credentials = (array) ($gateway->credentials ?? []);

        if ($provider === 'stripe') {
            return filled($credentials['publishable_key'] ?? null)
                && filled($credentials['secret_key'] ?? null)
                && filled($gateway->webhook_secret);
        }

        if ($provider === 'hitpay') {
            return filled($credentials['api_key'] ?? null)
                && (filled($credentials['salt'] ?? null) || filled($gateway->webhook_secret));
        }

        return false;
    }
}
