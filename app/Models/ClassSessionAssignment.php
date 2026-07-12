<?php

namespace App\Models;

use App\Models\Concerns\AssignsStudio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSessionAssignment extends Model
{
    use AssignsStudio, SoftDeletes;

    protected $fillable = [
        'studio_id',
        'user_id',
        'class_session_id',
        'assigned_by',
        'notes',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function session()
    {
        return $this->belongsTo(ClassSession::class, 'class_session_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class, 'class_session_id');
    }

    public function attendance()
    {
        return $this->hasOne(Attendance::class, 'class_session_assignment_id');
    }
}
