<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'is_recurring',
        'recurrence_frequency',
        'custom_frequency_days',
        'until_date',
        'is_active',
    ];

    protected $casts = [
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
        'until_date' => 'date',
    ];

    public function sessions()
    {
        return $this->hasMany(PlanSession::class, 'plan_id');
    }

    public function userPlans()
    {
        return $this->hasMany(\App\Models\UserPlan::class, 'plan_id');
    }
}
