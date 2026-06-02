<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['orders', 'order_items', 'payments', 'studio_subscriptions'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'studio_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('studio_id')->nullable()->after('id')->index();
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['studio_subscriptions', 'payments', 'order_items', 'orders'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'studio_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('studio_id');
                });
            }
        }
    }
};
