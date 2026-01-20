<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id','currency','subtotal','total','status',
        'payment_provider','provider_reference',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
