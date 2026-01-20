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
}
