<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassCardUsage extends Model
{
    protected $fillable = [
        'user_class_card_id',
        'used_by',
        'used_at',
        'notes',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function userClassCard()
    {
        return $this->belongsTo(UserClassCard::class, 'user_class_card_id');
    }

    public function usedBy()
    {
        return $this->belongsTo(User::class, 'used_by');
    }
}
