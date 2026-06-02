<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     * @property $table->softDeletes();
     */
    protected $fillable = [
        'studio_id',
        'name',
        'email',
        'password',
        'role',
        'phone_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function studio()
    {
        return $this->belongsTo(Studio::class);
    }

    public function classSessionBookings()
    {
        return $this->hasMany(\App\Models\ClassSessionBooking::class, 'student_id');
    }

    public function plans()
    {
        return $this->hasMany(\App\Models\UserPlan::class, 'user_id');
    }

    public function classSessionAssignments()
    {
        return $this->hasMany(\App\Models\ClassSessionAssignment::class, 'user_id');
    }

    public function appNotifications()
    {
        return $this->hasMany(\App\Models\AppNotification::class, 'user_id');
    }

    public function unreadAppNotifications()
    {
        return $this->hasMany(\App\Models\AppNotification::class, 'user_id')->whereNull('read_at');
    }
}
