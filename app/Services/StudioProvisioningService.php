<?php

namespace App\Services;

use App\Models\PlatformSubscriptionPlan;
use App\Models\Studio;
use App\Models\StudioDomain;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class StudioProvisioningService
{
    public function provision(array $data, ?int $provisionedBy = null): array
    {
        $email = Str::lower(trim((string) $data['owner_email']));
        $matches = User::withTrashed()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->get();

        if ($matches->count() > 1) {
            throw new RuntimeException(
                'Multiple accounts already use this email. Resolve the duplicate accounts before provisioning a studio.'
            );
        }

        $existingOwner = $matches->first();

        if ($existingOwner?->trashed()) {
            throw new RuntimeException('This email belongs to a deleted account. Restore that account before continuing.');
        }

        if ($existingOwner && ! $existingOwner->isClientPortalAccount()) {
            throw new RuntimeException(
                'This email belongs to a teacher, student, or studio-only administrator and cannot be promoted automatically.'
            );
        }

        $plan = ! empty($data['platform_subscription_plan_id'])
            ? PlatformSubscriptionPlan::query()->where('is_active', true)->findOrFail($data['platform_subscription_plan_id'])
            : null;

        if ($plan && $plan->max_admins !== null && (int) $plan->max_admins < 1) {
            throw new RuntimeException('The selected plan does not include an administrator seat.');
        }

        return DB::transaction(function () use ($data, $email, $existingOwner, $plan, $provisionedBy): array {
            $ownerWasCreated = ! $existingOwner;
            $owner = $existingOwner ?: User::query()->create([
                'studio_id' => null,
                'name' => $data['owner_name'],
                'email' => $email,
                'phone_number' => $data['owner_phone'] ?? null,
                'role' => 'admin',
                'password' => Hash::make(Str::random(64)),
            ]);

            if ($existingOwner) {
                $owner->forceFill([
                    'name' => $data['owner_name'],
                    'phone_number' => $data['owner_phone'] ?? $owner->phone_number,
                ])->save();
            }

            $subdomain = Str::lower($data['subdomain']);
            $rootDomain = Str::lower((string) config('saas.root_domain'));
            $status = $data['status'];
            $trialDays = (int) ($plan?->trial_days ?: config('saas.trial_days', 14));

            $studio = Studio::query()->create([
                'name' => $data['studio_name'],
                'slug' => $this->uniqueSlug($data['studio_name']),
                'subdomain' => $subdomain,
                'owner_user_id' => $owner->id,
                'status' => $status,
                'platform_subscription_plan_id' => $plan?->id,
                'plan_name' => $plan?->name,
                'trial_ends_at' => $status === 'trial' ? now()->addDays($trialDays) : null,
                'subscription_ends_at' => $data['subscription_ends_at'] ?? null,
                'subscription_status' => $plan ? 'manually_provisioned' : null,
                'settings' => [
                    'timezone' => $data['timezone'],
                    'currency' => Str::upper($data['currency']),
                    'manual_provisioning' => [
                        'provisioned_at' => now()->toIso8601String(),
                        'provisioned_by' => $provisionedBy,
                        'plan_id' => $plan?->id,
                    ],
                ],
            ]);

            StudioDomain::query()->create([
                'studio_id' => $studio->id,
                'domain' => $subdomain.'.'.$rootDomain,
                'type' => 'subdomain',
                'is_primary' => true,
                'is_verified' => true,
                'verified_at' => now(),
            ]);

            // A newly created owner gets this studio as their primary tenant.
            // Existing client owners retain their current primary studio while
            // gaining this studio through owner_user_id.
            if ($ownerWasCreated || ! $owner->studio_id) {
                $owner->forceFill(['studio_id' => $studio->id])->save();
            }

            return [$studio, $owner, $ownerWasCreated];
        });
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'studio';
        $slug = $base;
        $suffix = 2;

        while (Studio::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
