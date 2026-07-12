<?php

namespace App\Services;

use App\Models\Studio;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudioSeatLimitService
{
    private const ROLE_LIMIT_COLUMNS = [
        'student' => 'max_students',
        'teacher' => 'max_teachers',
        'admin' => 'max_admins',
    ];

    public function usage(Studio $studio): array
    {
        $studio->loadMissing('platformSubscriptionPlan');

        return collect(self::ROLE_LIMIT_COLUMNS)
            ->mapWithKeys(function (string $column, string $role) use ($studio) {
                $limit = $studio->platformSubscriptionPlan?->{$column};
                $used = User::query()
                    ->where('studio_id', $studio->id)
                    ->where('role', $role)
                    ->count();

                return [$role => [
                    'used' => $used,
                    'limit' => $limit === null ? null : (int) $limit,
                    'unlimited' => $limit === null,
                    'remaining' => $limit === null ? null : max(0, (int) $limit - $used),
                    'full' => $limit !== null && $used >= (int) $limit,
                    'percentage' => $limit === null || (int) $limit === 0
                        ? 0
                        : min(100, (int) round(($used / (int) $limit) * 100)),
                ]];
            })
            ->all();
    }

    public function assertCanAdd(Studio $studio, string $role): void
    {
        if (! array_key_exists($role, self::ROLE_LIMIT_COLUMNS)) {
            throw new \InvalidArgumentException("Unsupported seat role [{$role}].");
        }

        DB::transaction(function () use ($studio, $role) {
            $lockedStudio = Studio::query()
                ->with('platformSubscriptionPlan')
                ->lockForUpdate()
                ->findOrFail($studio->id);

            $column = self::ROLE_LIMIT_COLUMNS[$role];
            $limit = $lockedStudio->platformSubscriptionPlan?->{$column};

            if ($limit === null) {
                return;
            }

            $used = User::query()
                ->where('studio_id', $lockedStudio->id)
                ->where('role', $role)
                ->count();

            if ($used >= (int) $limit) {
                $label = ucfirst($role);

                throw ValidationException::withMessages([
                    'seat_limit' => "{$label} seat limit reached ({$used}/{$limit}). Upgrade your plan to add more {$role}s.",
                ]);
            }
        }, 3);
    }
}
