<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RecurringHitPayService extends HitPayService
{
    public function createRecurringBilling(array $payload): array
    {
        return $this->request()->post($this->baseUrl().'/recurring-billing', $payload)->throw()->json();
    }

    public function getRecurringBilling(string $id): array
    {
        $response = $this->request()->get($this->baseUrl().'/recurring-billing/'.$id);

        if ($response->successful()) {
            $payload = $response->json() ?: [];
            $billing = $this->findRecurringBillingInPayload($payload, $id);

            return $billing ?: $payload;
        }

        if ($response->status() === 404 || $response->status() === 405) {
            $items = $this->listRecurringBillings();
            $billing = $this->findRecurringBillingInPayload($items, $id);

            if ($billing) {
                return $billing;
            }
        }

        throw new RuntimeException('HitPay recurring billing lookup failed: '.$response->status().' '.$response->body());
    }

    public function updateRecurringBilling(string $id, array $payload): array
    {
        return $this->request()->put($this->baseUrl().'/recurring-billing/'.$id, $payload)->throw()->json();
    }

    public function cancelRecurringBilling(string $id): array
    {
        $response = $this->request()->delete($this->baseUrl().'/recurring-billing/'.$id);

        if (! $response->successful()) {
            throw new RuntimeException('HitPay recurring billing cancellation failed: '.$response->status().' '.$response->body());
        }

        return $response->json() ?: ['id' => $id, 'status' => 'canceled'];
    }

    public function listRecurringBillings(array $query = []): array
    {
        $response = $this->request()->get($this->baseUrl().'/recurring-billing', $query);

        if (! $response->successful()) {
            throw new RuntimeException('HitPay recurring billing lookup failed: '.$response->status().' '.$response->body());
        }

        return $response->json() ?: [];
    }

    public function validateEventWebhook(string $rawPayload, ?string $signature): bool
    {
        if (! $signature) {
            return false;
        }

        $config = $this->gateways->hitpay();
        $salts = array_values(array_filter([
            $config['event_webhook_salt_key'] ?? null,
            $config['salt'] ?? null,
        ]));

        foreach ($salts as $salt) {
            $calculated = hash_hmac('sha256', $rawPayload, (string) $salt);
            if (hash_equals($signature, $calculated)) {
                return true;
            }
        }

        return false;
    }

    private function findRecurringBillingInPayload(array $payload, string $id): ?array
    {
        if ((string) ($payload['id'] ?? '') === $id) {
            return $payload;
        }

        foreach (['data', 'recurring_billing', 'recurringBilling', 'billing', 'result', 'items'] as $key) {
            if (! isset($payload[$key]) || ! is_array($payload[$key])) {
                continue;
            }

            $candidate = $this->findRecurringBillingInPayload($payload[$key], $id);
            if ($candidate) {
                return $candidate;
            }
        }

        foreach ($payload as $value) {
            if (! is_array($value)) {
                continue;
            }

            $candidate = $this->findRecurringBillingInPayload($value, $id);
            if ($candidate) {
                return $candidate;
            }
        }

        return null;
    }

    private function request(): PendingRequest
    {
        $config = $this->gateways->hitpay();
        $apiKey = (string) ($config['api_key'] ?? '');
        if ($apiKey === '') {
            throw new RuntimeException('HitPay API key is not configured for this studio.');
        }

        return Http::withHeaders([
            'X-BUSINESS-API-KEY' => $apiKey,
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->asJson();
    }

    private function baseUrl(): string
    {
        $config = $this->gateways->hitpay();
        return rtrim((string) ($config['base_url'] ?? ''), '/');
    }
}
