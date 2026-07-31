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
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, AssignsStudio;

    protected $fillable = [
        'studio_id', 'name', 'email', 'password', 'role', 'phone_number',
        'organisation_name', 'job_title', 'country', 'date_of_birth', 'gender',
        'address', 'city', 'state', 'postal_code', 'emergency_contact_name',
        'emergency_contact_phone', 'phone_verified_at', 'profile_completed_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
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
            'phone_verified_at' => 'datetime',
            'profile_completed_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function clientProfileRequiredFields(): array
    {
        return [
            'name', 'email', 'phone_number', 'organisation_name', 'job_title',
            'country', 'date_of_birth', 'gender', 'address', 'city', 'state', 'postal_code',
        ];
    }

    public function missingClientProfileFields(): array
    {
        if ($this->role !== 'admin' || $this->studio_id) {
            return [];
        }

        return collect($this->clientProfileRequiredFields())
            ->filter(fn (string $field) => blank($this->{$field}))
            ->values()
            ->all();
    }

    public function hasCompleteClientProfile(): bool
    {
        return $this->missingClientProfileFields() === [];
    }

    public function hasTwoFactorEnabled(): bool
    {
        return filled($this->two_factor_secret) && $this->two_factor_confirmed_at !== null;
    }

    public function studio() { return $this->belongsTo(Studio::class); }
    public function ownedStudios() { return $this->hasMany(Studio::class, 'owner_user_id'); }
    public function classSessionBookings() { return $this->hasMany(\App\Models\ClassSessionBooking::class, 'student_id'); }
    public function plans() { return $this->hasMany(\App\Models\UserPlan::class, 'user_id'); }
    public function classSessionAssignments() { return $this->hasMany(\App\Models\ClassSessionAssignment::class, 'user_id'); }
    public function appNotifications() { return $this->hasMany(\App\Models\AppNotification::class, 'user_id'); }
    public function unreadAppNotifications() { return $this->hasMany(\App\Models\AppNotification::class, 'user_id')->whereNull('read_at'); }
    public function sentPlatformMessages() { return $this->hasMany(PlatformMessage::class, 'sender_id'); }
    public function receivedPlatformMessages() { return $this->hasMany(PlatformMessage::class, 'recipient_id'); }
    public function auditLogs() { return $this->hasMany(AuditLog::class); }
}
