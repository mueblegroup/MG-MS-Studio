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

        $salts = array_values(array_filter([
            config('services.hitpay.event_webhook_salt_key'),
            config('services.hitpay.salt'),
        ]));

        foreach ($salts as $salt) {
            $calculated = hash_hmac('sha256', $rawPayload, (string) $salt);
            if (hash_equals($signature, $calculated)) {
                return true;
            }
        }

        return false;
    }

    private function request(): PendingRequest
    {
        $apiKey = (string) config('services.hitpay.api_key');
        if ($apiKey === '') {
            throw new RuntimeException('Missing HITPAY_API_KEY');
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
        return rtrim((string) config('services.hitpay.base_url'), '/');
    }
}
