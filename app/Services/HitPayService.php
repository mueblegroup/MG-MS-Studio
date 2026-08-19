<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HitPayService
{
    public function __construct(protected StudioPaymentGatewayService $gateways)
    {
    }

    public function createPaymentRequest(array $payload): array
    {
        $config = $this->gateways->hitpay();
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $apiKey = (string) ($config['api_key'] ?? '');

        if ($apiKey === '') {
            throw new \RuntimeException('HitPay API key is not configured for this studio.');
        }

        $res = Http::withHeaders([
                'X-BUSINESS-API-KEY' => $apiKey,
                'Accept' => 'application/json',
            ])
            ->asForm()
            ->post($baseUrl . '/payment-requests', $payload);

        if (!$res->successful()) {
            throw new \RuntimeException('HitPay error: ' . $res->status() . ' ' . $res->body());
        }

        return $res->json();
    }

    /**
     * Validates a legacy HitPay form webhook with the current studio's secret.
     */
    public function validateWebhook(array $data): bool
    {
        $received = (string)($data['hmac'] ?? '');
        if ($received === '') return false;

        $config = $this->gateways->hitpay();
        $secrets = array_values(array_filter([
            $config['event_webhook_salt_key'] ?? null,
            $config['salt'] ?? null,
        ]));
        if (empty($secrets)) return false;

        $copy = $data;
        unset($copy['hmac']);
        ksort($copy);

        $sigStr = '';
        foreach ($copy as $k => $v) {
            $sigStr .= $k . (string)$v;
        }

        foreach ($secrets as $secret) {
            $calc = hash_hmac('sha256', $sigStr, (string) $secret);
            if (hash_equals($received, $calc)) {
                return true;
            }
        }

        return false;
    }
}
