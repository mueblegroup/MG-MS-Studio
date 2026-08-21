<?php

namespace App\Models;

use App\Models\Concerns\AssignsStudio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserClassCard extends Model
{
    use AssignsStudio, SoftDeletes;

    protected $fillable = [
        'studio_id',
        'user_id',
        'class_card_id',
        'purchased_at',
        'expires_at',
        'classes_remaining',
        'status',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function classCard()
    {
        return $this->belongsTo(\App\Models\ClassCard::class, 'class_card_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function card()
    {
        return $this->belongsTo(ClassCard::class, 'class_card_id');
    }

    public function usages()
    {
        return $this->hasMany(\App\Models\ClassCardUsage::class, 'user_class_card_id');
    }
}
