<?php

namespace App\Services;

use App\Models\ClassSession;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderFulfillmentService
{
    /**
     * Fulfill a PAID order.
     * - Safe to run multiple times (webhook retries).
     * - Uses orders.fulfilled_at as the idempotency guard.
     */
    public function fulfillPaidOrder(int $orderId): void
    {
        DB::transaction(function () use ($orderId) {
            /** @var \App\Models\Order|null $order */
            $order = Order::lockForUpdate()->with('items')->find($orderId);

            if (!$order) {
                return;
            }

            // Only fulfill paid orders
            if ($order->status !== 'paid') {
                return;
            }

            // Idempotent: already fulfilled
            if ($order->fulfilled_at) {
                return;
            }

            $studioId = (int) ($order->studio_id ?: current_studio_id() ?: 1);

            foreach ($order->items as $item) {
                $type = class_basename($item->purchasable_type);
                $pid  = (int) $item->purchasable_id;
                $itemStudioId = (int) ($item->studio_id ?: $studioId);

                if ($type === 'ClassSession') {
                    $this->grantIndividualClass($order->user_id, $pid, $order->id, $itemStudioId, $item->meta ?? []);

                    if (!empty($order->studio_subscription_id)) {
                        $this->advanceSubscriptionAfterClassFulfillment((int) $order->studio_subscription_id, $pid, $itemStudioId);
                    }

                    continue;
                }

                if ($type === 'Plan') {
                    $this->grantPlan($order->user_id, $pid, $itemStudioId);
                    continue;
                }

                if ($type === 'ClassCard') {
                    $qty = max(1, (int) $item->quantity);
                    $this->grantClassCard($order->user_id, $pid, $qty, $itemStudioId);
                    continue;
                }
            }

            // Mark as fulfilled (so webhook retries won't duplicate grants)
            $order->update(['fulfilled_at' => now()]);
        });
    }

    /**
     * Individual class purchase:
     * - Insert into class_session_assignments (your requirement)
     * - Also insert into bookings (helpful for frontend / attendance flow)
     */
    protected function grantIndividualClass(int $userId, int $classSessionId, int $orderId, int $studioId, array $meta = []): void
    {
        // 1) bookings (unique on user_id + class_session_id)
        DB::table('bookings')->updateOrInsert(
            [
                'user_id' => $userId,
                'class_session_id' => $classSessionId,
            ],
            [
                'studio_id' => $studioId,
                'status' => 'booked',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // 2) class_session_assignments (supports soft delete, no unique index)
        $existing = DB::table('class_session_assignments')
            ->where('user_id', $userId)
            ->where('class_session_id', $classSessionId)
            ->first();

        $noteParts = [];
        if (!empty($meta['label'])) $noteParts[] = "Item: " . $meta['label'];
        if (!empty($meta['date']))  $noteParts[] = "Date: " . $meta['date'];
        if (!empty($meta['time']))  $noteParts[] = "Time: " . $meta['time'];
        if (!empty($meta['billing_reason'])) $noteParts[] = "Billing: " . $meta['billing_reason'];
        $noteParts[] = "Purchased via Order #" . $orderId;
        $notes = implode(" | ", $noteParts);

        if (!$existing) {
            DB::table('class_session_assignments')->insert([
                'studio_id' => $studioId,
                'user_id' => $userId,
                'class_session_id' => $classSessionId,
                'assigned_by' => null,
                'notes' => $notes,
                'status' => 'assigned',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return;
        }

        // If it exists but was soft deleted, "restore" it
        if (!empty($existing->deleted_at)) {
            DB::table('class_session_assignments')
                ->where('id', $existing->id)
                ->update([
                    'studio_id' => $studioId,
                    'deleted_at' => null,
                    'status' => 'assigned',
                    'notes' => $notes,
                    'updated_at' => now(),
                ]);
        }
        // else: already assigned; do nothing
    }

    /**
     * Plan purchase:
     * - user_plans only (plan sessions derived later via plan_sessions)
     */
    protected function grantPlan(int $userId, int $planId, int $studioId): void
    {
        $plan = DB::table('plans')->where('id', $planId)->first();
        $startsOn = now()->toDateString();
        $endsOn = $plan?->until_date ? (string) $plan->until_date : null;
        $resolvedStudioId = (int) ($plan?->studio_id ?: $studioId);

        DB::table('user_plans')->updateOrInsert(
            [
                'user_id' => $userId,
                'plan_id' => $planId,
            ],
            [
                'studio_id' => $resolvedStudioId,
                'starts_on' => $startsOn,
                'ends_on' => $endsOn,
                'is_active' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * ClassCard purchase:
     * - Inserts into user_class_cards
     * - quantity = number of cards purchased (1 card = total_classes passes)
     */
    protected function grantClassCard(int $userId, int $classCardId, int $qty, int $studioId): void
    {
        $card = DB::table('class_cards')->where('id', $classCardId)->first();

        $resolvedStudioId = (int) ($card?->studio_id ?: $studioId);
        $totalClasses = (int) ($card?->total_classes ?? 10);
        $validWeeks   = (int) ($card?->validity_weeks ?? 12);

        $purchasedAt = now();
        $expiresAt = now()->addWeeks($validWeeks);

        for ($i = 0; $i < $qty; $i++) {
            DB::table('user_class_cards')->insert([
                'studio_id' => $resolvedStudioId,
                'user_id' => $userId,
                'class_card_id' => $classCardId,
                'purchased_at' => $purchasedAt,
                'expires_at' => $expiresAt,
                'classes_remaining' => $totalClasses,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]);
        }
    }

    protected function advanceSubscriptionAfterClassFulfillment(int $subscriptionId, int $fulfilledClassSessionId, int $studioId): void
    {
        $subscription = DB::table('studio_subscriptions')
            ->lockForUpdate()
            ->where('id', $subscriptionId)
            ->first();

        if (!$subscription) {
            return;
        }

        $resolvedStudioId = (int) ($subscription->studio_id ?: $studioId);

        $nextSession = ClassSession::query()
            ->where('class_id', $subscription->class_id)
            ->where('start_time', '>', ClassSession::whereKey($fulfilledClassSessionId)->value('start_time'))
            ->orderBy('start_time')
            ->first();

        DB::table('studio_subscriptions')
            ->where('id', $subscriptionId)
            ->update([
                'studio_id' => $resolvedStudioId,
                'last_fulfilled_class_session_id' => $fulfilledClassSessionId,
                'current_class_session_id' => $nextSession?->id,
                'updated_at' => now(),
            ]);
    }
}
