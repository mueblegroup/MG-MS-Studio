<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code', 30)->nullable()->after('state');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->timestamp('profile_completed_at')->nullable()->after('phone_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'city',
                'state',
                'postal_code',
                'phone_verified_at',
                'profile_completed_at',
            ]);
        });
    }
};
