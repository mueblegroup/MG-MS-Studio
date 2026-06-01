<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'studio_subscription_id',
        'amount',
        'currency',
        'method',
        'provider',
        'reference',
        'provider_reference',
        'payload',
        'paid_at',
        'billing_period_start',
        'billing_period_end',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
        'paid_at' => 'datetime',
        'billing_period_start' => 'datetime',
        'billing_period_end' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function studioSubscription()
    {
        return $this->belongsTo(StudioSubscription::class, 'studio_subscription_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
