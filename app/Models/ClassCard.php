<?php

namespace App\Models;

use App\Models\Concerns\AssignsStudio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassCard extends Model
{
    use AssignsStudio;
    use SoftDeletes;

    protected $fillable = [
        'studio_id',
        'name',
        'total_classes',
        'validity_weeks',
        'price',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function purchases() // or userClassCards, your choice
    {
        return $this->hasMany(\App\Models\UserClassCard::class, 'class_card_id');
    }

    public function userClassCards()
    {
        return $this->hasMany(UserClassCard::class);
    }
}
