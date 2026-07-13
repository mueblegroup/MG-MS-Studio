<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Studio extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'subdomain',
        'custom_domain',
        'owner_user_id',
        'status',
        'plan_name',
        'platform_subscription_plan_id',
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_subscription_item_id',
        'subscription_status',
        'trial_ends_at',
        'subscription_ends_at',
        'cancel_at_period_end',
        'canceled_at',
        'manually_suspended_at',
        'suspension_reason',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'canceled_at' => 'datetime',
        'manually_suspended_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Studio $studio): void {
            if ($studio->manually_suspended_at) {
                $studio->status = 'suspended';
            }
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function platformSubscriptionPlan()
    {
        return $this->belongsTo(PlatformSubscriptionPlan::class);
    }

    public function platformSubscriptionPayments()
    {
        return $this->hasMany(PlatformSubscriptionPayment::class);
    }

    public function domains()
    {
        return $this->hasMany(StudioDomain::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function isTrialExpired(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isPast();
    }

    public function isSubscriptionExpired(): bool
    {
        return $this->status === 'active'
            && $this->subscription_ends_at
            && $this->subscription_ends_at->isPast();
    }

    public function isManuallySuspended(): bool
    {
        return (bool) $this->manually_suspended_at;
    }

    public function isActive(): bool
    {
        if ($this->isManuallySuspended() || $this->isTrialExpired() || $this->isSubscriptionExpired()) {
            return false;
        }

        return in_array($this->status, ['active', 'trial'], true);
    }
}
