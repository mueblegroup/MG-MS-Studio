<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('studio_id');
            $table->unsignedBigInteger('platform_subscription_plan_id')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('MYR');
            $table->string('billing_interval')->nullable();
            $table->string('provider')->nullable();
            $table->string('reference')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->string('status')->default('pending')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('studio_id', 'psp_studio_fk')
                ->references('id')
                ->on('studios')
                ->cascadeOnDelete();

            $table->foreign('platform_subscription_plan_id', 'psp_plan_fk')
                ->references('id')
                ->on('platform_subscription_plans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_subscription_payments');
    }
};
