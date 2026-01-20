<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanSession extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'plan_id',
        'session_name',
        'start_time',
        'end_time',
        'capacity',
        'venue_name',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function sessions()
    {
        return $this->hasMany(PlanSession::class, 'plan_id');
    }

    public function getFormattedDatesAttribute()
    {
        return $this->sessions->pluck('start_time')->map(function($date) {
            return $date->format('Y-m-d');
        })->implode(', ');
    }

}
