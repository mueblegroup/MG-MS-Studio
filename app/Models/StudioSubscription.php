<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudioSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'class_id',
        'current_class_session_id',
        'last_fulfilled_class_session_id',
        'initial_order_id',
        'provider',
        'provider_subscription_id',
        'provider_customer_id',
        'status',
        'currency',
        'amount',
        'billing_interval',
        'started_at',
        'current_period_start',
        'current_period_end',
        'next_billing_at',
        'cancelled_at',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'started_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'next_billing_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function currentClassSession()
    {
        return $this->belongsTo(ClassSession::class, 'current_class_session_id');
    }

    public function lastFulfilledClassSession()
    {
        return $this->belongsTo(ClassSession::class, 'last_fulfilled_class_session_id');
    }

    public function initialOrder()
    {
        return $this->belongsTo(Order::class, 'initial_order_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'studio_subscription_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'studio_subscription_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing', 'past_due'], true);
    }
}
