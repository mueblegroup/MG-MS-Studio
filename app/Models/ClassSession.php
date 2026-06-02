<?php

namespace App\Models;

use App\Models\ClassModel;
use Illuminate\Database\Eloquent\Model;

class ClassSession extends Model
{
    protected $fillable = [
        'studio_id', 'class_id', 'start_time', 'end_time', 'capacity', 'venue_name'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
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

}