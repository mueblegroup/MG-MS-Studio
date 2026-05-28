<?php

namespace App\Http\Controllers;

use App\Services\StudioSettingsService;
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

        return view('admin.settings.studio', compact('data'));
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
}
