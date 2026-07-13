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
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'canceled_at' => 'datetime',
    ];

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

    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === 'trial';
    }
}
