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
        if ($request->routeIs('webhooks.stripe')) {
            $this->processRenewalEvent($request);
        }

        return $next($request);
    }

    private function processRenewalEvent(Request $request): void
    {
        $secret = (string) config('services.stripe.webhook_secret');
        $signature = (string) $request->header('Stripe-Signature');

        if ($secret === '' || $signature === '') {
            return;
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $request->getContent(),
                $signature,
                $secret,
            );

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
            ]);
        }
    }
}