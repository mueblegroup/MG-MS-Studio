<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            if (!Schema::hasColumn('classes', 'billing_interval')) {
                $table->string('billing_interval')->nullable()->after('price');
            }
            if (!Schema::hasColumn('classes', 'subscription_grace_days')) {
                $table->unsignedSmallInteger('subscription_grace_days')->default(3)->after('billing_interval');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'studio_subscription_id')) {
                $table->foreignId('studio_subscription_id')->nullable()->after('user_id')->index();
            }
            if (!Schema::hasColumn('orders', 'billing_reason')) {
                $table->string('billing_reason')->nullable()->after('payment_provider');
            }
            if (!Schema::hasColumn('orders', 'fulfilled_at')) {
                $table->timestamp('fulfilled_at')->nullable()->after('paid_at');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'studio_subscription_id')) {
                $table->foreignId('studio_subscription_id')->nullable()->after('order_id')->index();
            }
            if (!Schema::hasColumn('payments', 'billing_period_start')) {
                $table->timestamp('billing_period_start')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('payments', 'billing_period_end')) {
                $table->timestamp('billing_period_end')->nullable()->after('billing_period_start');
            }
        });

        Schema::create('studio_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('current_class_session_id')->nullable()->constrained('class_sessions')->nullOnDelete();
            $table->foreignId('last_fulfilled_class_session_id')->nullable()->constrained('class_sessions')->nullOnDelete();
            $table->foreignId('initial_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('provider');
            $table->string('provider_subscription_id')->nullable()->index();
            $table->string('provider_customer_id')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->string('currency', 3)->default('MYR');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('billing_interval')->default('month');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('current_period_start')->nullable();
            $table->dateTime('current_period_end')->nullable();
            $table->dateTime('next_billing_at')->nullable()->index();
            $table->dateTime('cancelled_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'class_id', 'provider', 'status'], 'studio_sub_unique_active_like');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_subscriptions');

        Schema::table('payments', function (Blueprint $table) {
            foreach (['studio_subscription_id', 'billing_period_start', 'billing_period_end'] as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            foreach (['studio_subscription_id', 'billing_reason', 'fulfilled_at'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('classes', function (Blueprint $table) {
            foreach (['billing_interval', 'subscription_grace_days'] as $column) {
                if (Schema::hasColumn('classes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
