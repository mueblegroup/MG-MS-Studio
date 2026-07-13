<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_subscription_plans', function (Blueprint $table): void {
            $table->unsignedSmallInteger('trial_days')->default(0)->after('billing_interval');
        });
    }

    public function down(): void
    {
        Schema::table('platform_subscription_plans', function (Blueprint $table): void {
            $table->dropColumn('trial_days');
        });
    }
};
