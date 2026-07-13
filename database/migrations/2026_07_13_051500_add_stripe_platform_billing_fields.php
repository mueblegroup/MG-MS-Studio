<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_subscription_plans', function (Blueprint $table): void {
            $table->string('stripe_product_id')->nullable()->after('billing_interval');
            $table->string('stripe_price_id')->nullable()->after('stripe_product_id');
        });

        Schema::table('studios', function (Blueprint $table): void {
            $table->string('stripe_customer_id')->nullable()->index()->after('platform_subscription_plan_id');
            $table->string('stripe_subscription_id')->nullable()->unique()->after('stripe_customer_id');
            $table->string('stripe_subscription_item_id')->nullable()->after('stripe_subscription_id');
            $table->string('subscription_status')->nullable()->index()->after('stripe_subscription_item_id');
            $table->boolean('cancel_at_period_end')->default(false)->after('subscription_ends_at');
            $table->timestamp('canceled_at')->nullable()->after('cancel_at_period_end');
        });
    }

    public function down(): void
    {
        Schema::table('studios', function (Blueprint $table): void {
            $table->dropColumn([
                'stripe_customer_id',
                'stripe_subscription_id',
                'stripe_subscription_item_id',
                'subscription_status',
                'cancel_at_period_end',
                'canceled_at',
            ]);
        });

        Schema::table('platform_subscription_plans', function (Blueprint $table): void {
            $table->dropColumn(['stripe_product_id', 'stripe_price_id']);
        });
    }
};
