<?php

namespace App\Providers;

use App\Services\StudioSettingsService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class MailSettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            if (!Schema::hasTable('studio_settings')) {
                return;
            }

            $settings = app(StudioSettingsService::class);

            if (!$settings->get('mail_enabled', false)) {
                return;
            }

            $mailer = (string) $settings->get('mail_mailer', config('mail.default', 'smtp'));
            $encryption = (string) $settings->get('mail_encryption', 'tls');

            config([
                'mail.default' => $mailer,
                'mail.mailers.smtp.host' => (string) $settings->get('mail_host', config('mail.mailers.smtp.host')),
                'mail.mailers.smtp.port' => (int) $settings->get('mail_port', config('mail.mailers.smtp.port', 587)),
                'mail.mailers.smtp.username' => (string) $settings->get('mail_username', config('mail.mailers.smtp.username')),
                'mail.mailers.smtp.password' => (string) $settings->get('mail_password', config('mail.mailers.smtp.password')),
                'mail.mailers.smtp.scheme' => $encryption !== '' ? $encryption : null,
                'mail.mailers.smtp.local_domain' => (string) $settings->get('mail_ehlo_domain', config('mail.mailers.smtp.local_domain')),
                'mail.from.address' => (string) $settings->get('mail_from_address', config('mail.from.address')),
                'mail.from.name' => (string) $settings->get('mail_from_name', config('mail.from.name')),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
