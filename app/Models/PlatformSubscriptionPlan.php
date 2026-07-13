<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'billing_interval',
        'stripe_product_id',
        'stripe_price_id',
        'max_students',
        'max_teachers',
        'max_admins',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function studios()
    {
        return $this->hasMany(Studio::class);
    }

    public function payments()
    {
        return $this->hasMany(PlatformSubscriptionPayment::class);
    }
}
