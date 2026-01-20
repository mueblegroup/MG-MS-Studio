<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'purchasable_type',
        'purchasable_id',
        'quantity',
        'unit_price',
        'currency',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'unit_price' => 'decimal:2',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }
}
