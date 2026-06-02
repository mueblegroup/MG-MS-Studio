<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'studio_id','order_id','purchasable_type','purchasable_id',
        'quantity','unit_price','currency','meta',
    ];

    protected $casts = ['meta' => 'array'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function purchasable()
    {
        return $this->morphTo();
    }
}
