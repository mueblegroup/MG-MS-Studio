<?php

namespace App\Models;

use App\Models\User;
use App\Models\ClassSession;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'studio_id',
        'name',
        'description',
        'teacher_id',
        'type',
        'capacity',
        'price',
        'billing_interval',
        'subscription_grace_days',
        'is_recurring',
        'recurrence_frequency',
        'custom_frequency_days',
        'until_date',
    ];

    protected $casts = [
        'is_recurring' => 'boolean',
        'price' => 'decimal:2',
        'subscription_grace_days' => 'integer',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function sessions()
    {
        return $this->hasMany(ClassSession::class, 'class_id');
    }

    public function studioSubscriptions()
    {
        return $this->hasMany(StudioSubscription::class, 'class_id');
    }

    public function isSubscriptionClass(): bool
    {
        return $this->type === 'subscription';
    }
}
