<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL can leave the table behind when CREATE TABLE succeeds but a
        // later ALTER TABLE for a generated foreign-key name fails. Because
        // this migration was not recorded as completed, the partial table is
        // safe to rebuild before retrying it.
        Schema::dropIfExists('studio_onboarding_checkouts');

        Schema::create('studio_onboarding_checkouts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('platform_subscription_plan_id');
            $table->string('studio_name');
            $table->string('subdomain', 40);
            $table->string('timezone', 80)->nullable();
            $table->string('currency', 3)->default('MYR');
            $table->string('stripe_checkout_session_id')->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'soc_user_fk')
                ->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('platform_subscription_plan_id', 'soc_plan_fk')
                ->references('id')->on('platform_subscription_plans');

            $table->unique('stripe_checkout_session_id', 'soc_checkout_session_uq');
            $table->unique('stripe_subscription_id', 'soc_subscription_uq');
            $table->index(['user_id', 'status'], 'soc_user_status_idx');
            $table->index(['subdomain', 'status'], 'soc_subdomain_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_onboarding_checkouts');
    }
};
