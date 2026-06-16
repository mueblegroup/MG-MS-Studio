<?php

namespace App\Models;

use App\Models\Concerns\AssignsStudio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use AssignsStudio;
    use SoftDeletes;

    protected $fillable = [
        'studio_id',
        'name',
        'description',
        'teacher_id',
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

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
