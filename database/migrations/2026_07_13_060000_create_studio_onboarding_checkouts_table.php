<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_onboarding_checkouts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_subscription_plan_id')->constrained('platform_subscription_plans');
            $table->string('studio_name');
            $table->string('subdomain', 40);
            $table->string('timezone', 80)->nullable();
            $table->string('currency', 3)->default('MYR');
            $table->string('stripe_checkout_session_id')->nullable()->unique();
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_subscription_id')->nullable()->unique();
            $table->string('status', 30)->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['subdomain', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_onboarding_checkouts');
    }
};
