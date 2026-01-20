<?php

namespace App\Models;

use App\Models\User;
use App\Models\ClassSession;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'name', 'description', 'teacher_id', 'type', 'capacity', 'price', 'is_recurring', 'recurrence_frequency', 'custom_frequency_days', 'until_date'
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function sessions()
    {
        return $this->hasMany(ClassSession::class, 'class_id');
    }
}
