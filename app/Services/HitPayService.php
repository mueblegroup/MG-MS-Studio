<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HitPayService
{
    public function createPaymentRequest(array $payload): array
    {
        $baseUrl = rtrim(config('services.hitpay.base_url'), '/');
        $apiKey  = config('services.hitpay.api_key');

        if (!$apiKey) {
            throw new \RuntimeException('Missing HITPAY_API_KEY');
        }

        $res = Http::withHeaders([
                'X-BUSINESS-API-KEY' => $apiKey,
                'Accept' => 'application/json',
            ])
            ->asForm() // HitPay accepts form-data; safer for x-www-form-urlencoded
            ->post($baseUrl . '/payment-requests', $payload);

        if (!$res->successful()) {
            throw new \RuntimeException('HitPay error: ' . $res->status() . ' ' . $res->body());
        }

        return $res->json();
    }

    /**
     * Validates HitPay webhook HMAC using event_webhook_salt_key OR normal salt.
     */
    public function validateWebhook(array $data): bool
    {
        $received = (string)($data['hmac'] ?? '');
        if ($received === '') return false;

        $eventSalt  = config('services.hitpay.event_webhook_salt_key');
        $normalSalt = config('services.hitpay.salt');

        $secrets = array_values(array_filter([$eventSalt, $normalSalt]));
        if (empty($secrets)) return false;

        $copy = $data;
        unset($copy['hmac']);

        ksort($copy);

        $sigStr = '';
        foreach ($copy as $k => $v) {
            $sigStr .= $k . (string)$v;
        }

        foreach ($secrets as $secret) {
            $calc = hash_hmac('sha256', $sigStr, $secret);
            if (hash_equals($received, $calc)) {
                return true;
            }
        }

        return false;
    }
}
