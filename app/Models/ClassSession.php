<?php

namespace App\Models;

use App\Models\ClassModel;
use App\Models\Concerns\AssignsStudio;
use Illuminate\Database\Eloquent\Model;

class ClassSession extends Model
{
    use AssignsStudio;

    protected $fillable = [
        'studio_id',
        'class_id',
        'start_time',
        'end_time',
        'capacity',
        'venue_name',
        'status',
        'change_type',
        'change_reason',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'changed_at' => 'datetime',
    ];

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function bookings()
    {
        return $this->hasMany(\App\Models\ClassSessionBooking::class, 'class_session_id');
    }

    public function assignments()
    {
        return $this->hasMany(\App\Models\ClassSessionAssignment::class, 'class_session_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}