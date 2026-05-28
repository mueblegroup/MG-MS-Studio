<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'currency',
        'subtotal',
        'total',
        'status',
        'payment_provider',
        'provider_reference',
        'paid_at',
    ];

    protected static function booted(): void
    {
        static::created(function (Order $order) {
            if ($order->status === 'pending') {
                $order->createUserNotification(
                    'Payment due',
                    'Your order #' . $order->id . ' is pending payment. Please complete the payment to confirm your booking or purchase.',
                    'payment_due'
                );
            }
        });

        static::updated(function (Order $order) {
            if (!$order->wasChanged('status')) {
                return;
            }

            if ($order->status === 'paid') {
                $order->createUserNotification(
                    'Payment successful',
                    'Your payment for order #' . $order->id . ' has been received successfully. Your purchase is now being processed.',
                    'payment_success'
                );
            }

            if ($order->status === 'cancelled') {
                $order->createUserNotification(
                    'Payment cancelled',
                    'Your payment for order #' . $order->id . ' was cancelled. You can return to checkout if you still want to complete this purchase.',
                    'payment_due'
                );
            }
        });
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function createUserNotification(string $title, string $message, string $type): void
    {
        try {
            if (!$this->user_id || !Schema::hasTable('app_notifications')) {
                return;
            }

            AppNotification::create([
                'user_id' => $this->user_id,
                'created_by' => null,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'action_url' => '/student/payments',
                'data' => [
                    'order_id' => $this->id,
                    'status' => $this->status,
                    'total' => $this->total,
                    'currency' => $this->currency,
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
