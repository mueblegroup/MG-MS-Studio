<?php

namespace App\Models;

use App\Models\Concerns\AssignsStudio;
use App\Services\StudioSeatLimitService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, AssignsStudio;

    protected $fillable = [
        'studio_id',
        'name',
        'email',
        'password',
        'role',
        'phone_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (! $user->studio_id || ! in_array($user->role, ['student', 'teacher', 'admin'], true)) {
                return;
            }

            $studio = Studio::query()->findOrFail($user->studio_id);
            app(StudioSeatLimitService::class)->assertCanAdd($studio, $user->role);
        });
    }

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

    public function ownedStudios()
    {
        return $this->hasMany(Studio::class, 'owner_user_id');
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

    public function sentPlatformMessages()
    {
        return $this->hasMany(PlatformMessage::class, 'sender_id');
    }

    public function receivedPlatformMessages()
    {
        return $this->hasMany(PlatformMessage::class, 'recipient_id');
    }
}
