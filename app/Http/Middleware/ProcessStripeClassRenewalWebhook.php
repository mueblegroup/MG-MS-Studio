<?php

namespace App\Http\Middleware;

use App\Services\ReliableSubscriptionClassService;
use App\Services\SubscriptionClassService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ProcessStripeClassRenewalWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->routeIs('webhooks.stripe')) {
            return $next($request);
        }

        $event = $this->constructStripeEvent($request);

        if (! $event) {
            return $next($request);
        }

        // Stripe waits for a successful invoice.created acknowledgement before
        // automatically finalizing a recurring invoice. This event does not
        // require LMS processing, so acknowledge it immediately and avoid the
        // authenticated web-layout middleware stack entirely.
        if ($event->type === 'invoice.created') {
            return response()->json(['ok' => true, 'received' => 'invoice.created']);
        }

        $this->processRenewalEvent($event);

        return $next($request);
    }

    private function constructStripeEvent(Request $request): ?object
    {
        $secret = (string) config('services.stripe.webhook_secret');
        $signature = (string) $request->header('Stripe-Signature');

        if ($secret === '' || $signature === '') {
            return null;
        }

        try {
            return \Stripe\Webhook::constructEvent(
                $request->getContent(),
                $signature,
                $secret,
            );
        } catch (\Throwable $exception) {
            Log::warning('Stripe webhook signature validation failed in renewal middleware.', [
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            return null;
        }
    }

    private function processRenewalEvent(object $event): void
    {
        try {
            $service = app(SubscriptionClassService::class);

            if (in_array($event->type, ['invoice.paid', 'invoice.payment_succeeded'], true)) {
                $service->handleStripeInvoicePayment($event->data->object);
                return;
            }

            if ($event->type === 'invoice.payment_failed' && $service instanceof ReliableSubscriptionClassService) {
                $service->handleStripeInvoiceFailure($event->data->object);
            }
        } catch (\Throwable $exception) {
            Log::error('Stripe class renewal webhook processing failed.', [
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
                'event_type' => $event->type ?? null,
                'event_id' => $event->id ?? null,
            ]);
        }
    }
}
