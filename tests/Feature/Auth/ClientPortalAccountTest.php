<?php

use App\Models\Studio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('central administrators without a studio are valid client portal accounts', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'studio_id' => null,
    ]);

    expect($user->isClientPortalAccount())->toBeTrue();
});

test('studio owners remain valid client portal accounts when studio id is populated', function () {
    $owner = User::factory()->create([
        'role' => 'admin',
        'studio_id' => null,
    ]);

    $studio = Studio::query()->create([
        'name' => 'Owner Studio',
        'slug' => 'owner-studio',
        'subdomain' => 'owner-studio',
        'owner_user_id' => $owner->id,
        'status' => 'trial',
        'plan_name' => 'trial',
        'trial_ends_at' => now()->addDays(14),
        'settings' => [],
    ]);

    $owner->forceFill(['studio_id' => $studio->id])->save();

    expect($owner->fresh()->isClientPortalAccount())->toBeTrue();
});

test('ordinary studio administrators are not client portal accounts', function () {
    $owner = User::factory()->create([
        'role' => 'admin',
        'studio_id' => null,
    ]);

    $studio = Studio::query()->create([
        'name' => 'Managed Studio',
        'slug' => 'managed-studio',
        'subdomain' => 'managed-studio',
        'owner_user_id' => $owner->id,
        'status' => 'trial',
        'plan_name' => 'trial',
        'trial_ends_at' => now()->addDays(14),
        'settings' => [],
    ]);

    $administrator = User::factory()->create([
        'role' => 'admin',
        'studio_id' => null,
    ]);
    $administrator->forceFill(['studio_id' => $studio->id])->save();

    expect($administrator->fresh()->isClientPortalAccount())->toBeFalse();
});

test('teachers and students are not client portal accounts', function (string $role) {
    $user = User::factory()->create([
        'role' => $role,
        'studio_id' => null,
    ]);

    expect($user->isClientPortalAccount())->toBeFalse();
})->with(['teacher', 'student']);
