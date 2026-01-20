<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'booking_id',
        'class_session_assignment_id',
        'plan_session_id',
        'user_id',
        'attended_at',
        'status',
    ];

    protected $casts = [
        'attended_at' => 'datetime',
    ];
}
