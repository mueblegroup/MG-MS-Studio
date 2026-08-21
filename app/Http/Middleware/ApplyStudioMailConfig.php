<?php

namespace App\Http\Middleware;

use App\Services\StudioSettingsService;
use App\Support\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyStudioMailConfig
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app(TenantManager::class)->current()) {
            return $next($request);
        }

        $settings = app(StudioSettingsService::class);

        if (! $settings->get('mail_enabled', false)) {
            return $next($request);
        }

        $mailer = (string) $settings->get('mail_mailer', config('mail.default', 'smtp'));
        $scheme = $this->normalizeScheme((string) $settings->get('mail_encryption', 'tls'));

        config([
            'mail.default' => $mailer,
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.scheme' => $scheme,
            'mail.mailers.smtp.encryption' => $scheme,
            'mail.mailers.smtp.url' => null,
            'mail.mailers.smtp.host' => (string) $settings->get('mail_host', config('mail.mailers.smtp.host')),
            'mail.mailers.smtp.port' => (int) $settings->get('mail_port', config('mail.mailers.smtp.port', 587)),
            'mail.mailers.smtp.username' => (string) $settings->get('mail_username', config('mail.mailers.smtp.username')),
            'mail.mailers.smtp.password' => (string) $settings->get('mail_password', config('mail.mailers.smtp.password')),
            'mail.mailers.smtp.timeout' => null,
            'mail.mailers.smtp.local_domain' => (string) $settings->get('mail_ehlo_domain', config('mail.mailers.smtp.local_domain')),
            'mail.from.address' => (string) $settings->get('mail_from_address', config('mail.from.address')),
            'mail.from.name' => (string) $settings->get('mail_from_name', config('mail.from.name')),
        ]);

        $this->forgetResolvedMailers();

        return $next($request);
    }

    private function normalizeScheme(?string $scheme): ?string
    {
        $scheme = strtolower(trim((string) $scheme));

        return match ($scheme) {
            '', 'none', 'null' => null,
            'ssl', 'smtps' => 'smtps',
            default => 'smtp',
        };
    }

    private function forgetResolvedMailers(): void
    {
        try {
            if (app()->bound('mail.manager')) {
                $manager = app('mail.manager');
                if (method_exists($manager, 'forgetMailers')) {
                    $manager->forgetMailers();
                }
            }

            app()->forgetInstance('mailer');
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
