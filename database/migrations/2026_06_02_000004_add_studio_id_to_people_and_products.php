<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['users', 'classes', 'class_sessions', 'plans', 'plan_sessions', 'class_cards'];

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
        $tables = ['class_cards', 'plan_sessions', 'plans', 'class_sessions', 'classes', 'users'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'studio_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('studio_id');
                });
            }
        }
    }
};
