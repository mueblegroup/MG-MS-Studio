<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudioOnboardingCheckout extends Model
{
    protected $fillable = [
        'user_id',
        'platform_subscription_plan_id',
        'studio_name',
        'subdomain',
        'timezone',
        'currency',
        'stripe_checkout_session_id',
        'stripe_customer_id',
        'stripe_subscription_id',
        'status',
        'expires_at',
        'completed_at',
        'failure_reason',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(PlatformSubscriptionPlan::class, 'platform_subscription_plan_id');
    }
}
