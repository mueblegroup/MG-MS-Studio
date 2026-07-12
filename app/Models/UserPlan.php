<?php

namespace App\Models;

use App\Models\Concerns\AssignsStudio;
use Illuminate\Database\Eloquent\Model;

class UserPlan extends Model
{
    use AssignsStudio;

    protected $fillable = [
        'studio_id',
        'plan_id',
        'user_id',
        'starts_on',
        'ends_on',
        'is_active',
    ];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'is_active' => 'boolean',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
