<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('studios')) {
            Schema::create('studios', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('subdomain')->nullable()->unique();
                $table->string('custom_domain')->nullable()->unique();
                $table->unsignedBigInteger('owner_user_id')->nullable()->index();
                $table->string('status')->default('active')->index();
                $table->string('plan_name')->nullable();
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamp('subscription_ends_at')->nullable();
                $table->json('settings')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('studio_domains')) {
            Schema::create('studio_domains', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('studio_id')->index();
                $table->string('domain')->unique();
                $table->string('type')->default('subdomain');
                $table->boolean('is_primary')->default(false);
                $table->boolean('is_verified')->default(false);
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_domains');
        Schema::dropIfExists('studios');
    }
};
