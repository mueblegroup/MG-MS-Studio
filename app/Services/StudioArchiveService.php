<?php

namespace App\Services;

use App\Models\Studio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class StudioArchiveService
{
    public function __construct(
        private readonly PlatformStripeBillingService $billing,
    ) {
    }

    public function archive(Studio $studio, string $reason, ?int $archivedBy = null): void
    {
        if ($studio->trashed()) {
            return;
        }

        if ($studio->stripe_subscription_id && ! in_array(
            strtolower((string) $studio->subscription_status),
            ['canceled', 'cancelled', 'incomplete_expired'],
            true
        )) {
            // Do this before the local transaction. If Stripe rejects the
            // request, the studio remains accessible and no paid renewal is
            // silently left running behind an archived tenant.
            $this->billing->cancelAtPeriodEnd($studio);
            $studio->refresh();
        }

        DB::transaction(function () use ($studio, $reason, $archivedBy): void {
            $verifiedDomainIds = $studio->domains()
                ->where('is_verified', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $settings = (array) $studio->settings;
            $settings['archive'] = [
                'archived_at' => now()->toIso8601String(),
                'archived_by' => $archivedBy,
                'reason' => $reason,
                'verified_domain_ids' => $verifiedDomainIds,
                'previous_status' => $studio->status,
            ];

            $studio->domains()->update([
                'is_verified' => false,
                'verified_at' => null,
            ]);

            $studio->forceFill([
                'status' => 'suspended',
                'manually_suspended_at' => now(),
                'suspension_reason' => 'Archived: '.$reason,
                'settings' => $settings,
            ])->save();

            $this->revokeUserSessions($studio);
            $studio->delete();
        });
    }

    public function restore(Studio $studio, ?int $restoredBy = null): void
    {
        if (! $studio->trashed()) {
            throw new RuntimeException('This studio is not archived.');
        }

        DB::transaction(function () use ($studio, $restoredBy): void {
            $settings = (array) $studio->settings;
            $archive = (array) ($settings['archive'] ?? []);
            $verifiedDomainIds = collect($archive['verified_domain_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->all();

            $archive['restored_at'] = now()->toIso8601String();
            $archive['restored_by'] = $restoredBy;
            $settings['archive'] = $archive;

            $studio->restore();
            $studio->forceFill([
                // Restoration never silently grants access. A superadmin must
                // review billing and explicitly activate the studio afterward.
                'status' => 'inactive',
                'manually_suspended_at' => null,
                'suspension_reason' => null,
                'settings' => $settings,
            ])->save();

            if ($verifiedDomainIds !== []) {
                $studio->domains()
                    ->whereIn('id', $verifiedDomainIds)
                    ->update([
                        'is_verified' => true,
                        'verified_at' => now(),
                    ]);
            }
        });
    }

    private function revokeUserSessions(Studio $studio): void
    {
        if (! Schema::hasTable('sessions') || ! Schema::hasColumn('sessions', 'user_id')) {
            return;
        }

        DB::table('sessions')
            ->whereIn('user_id', $studio->users()->pluck('id'))
            ->delete();
    }
}
