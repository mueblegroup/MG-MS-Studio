<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Studio extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'subdomain', 'custom_domain', 'owner_user_id', 'status',
        'plan_name', 'platform_subscription_plan_id', 'stripe_customer_id',
        'stripe_subscription_id', 'stripe_subscription_item_id', 'subscription_status',
        'trial_ends_at', 'subscription_ends_at', 'cancel_at_period_end', 'canceled_at',
        'manually_suspended_at', 'suspension_reason', 'settings',
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
            $request = app()->bound('request') ? request() : null;
            $isSuperadminUpdate = $request?->routeIs('superadmin.studios.update') ?? false;

            if ($isSuperadminUpdate && $studio->stripe_subscription_id) {
                if ($studio->isDirty('trial_ends_at')) {
                    $studio->trial_ends_at = $studio->getOriginal('trial_ends_at');
                }

                if ($studio->isDirty('subscription_ends_at')) {
                    $studio->subscription_ends_at = $studio->getOriginal('subscription_ends_at');
                }
            }

            if ($isSuperadminUpdate && $studio->isDirty('status')) {
                if ($studio->status === 'suspended') {
                    $studio->manually_suspended_at = now();
                    $studio->suspension_reason = trim((string) $request->input('suspension_reason')) ?: 'Suspended by superadmin';
                } else {
                    $studio->manually_suspended_at = null;
                    $studio->suspension_reason = null;
                }
            }

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
        return $this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    public function isSubscriptionExpired(): bool
    {
        return in_array($this->status, ['active', 'inactive'], true)
            && $this->subscription_ends_at
            && $this->subscription_ends_at->isPast();
    }

    public function isManuallySuspended(): bool
    {
        return (bool) $this->manually_suspended_at;
    }

    public function effectiveStatus(): string
    {
        if ($this->isManuallySuspended()) {
            return 'suspended';
        }

        if ($this->isTrialExpired() || $this->isSubscriptionExpired()) {
            return 'inactive';
        }

        return $this->status;
    }

    public function isActive(): bool
    {
        return in_array($this->effectiveStatus(), ['active', 'trial'], true);
    }
}
