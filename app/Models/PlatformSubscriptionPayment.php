<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSubscriptionPayment extends Model
{
    protected $fillable = [
        'studio_id',
        'platform_subscription_plan_id',
        'amount',
        'currency',
        'billing_interval',
        'provider',
        'reference',
        'paid_at',
        'period_start',
        'period_end',
        'status',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'metadata' => 'array',
    ];

    public function studio()
    {
        return $this->belongsTo(Studio::class);
    }

    public function plan()
    {
        return $this->belongsTo(PlatformSubscriptionPlan::class, 'platform_subscription_plan_id');
    }
}
