<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_plans') && !Schema::hasColumn('user_plans', 'studio_id')) {
            Schema::table('user_plans', function (Blueprint $table) {
                $table->unsignedBigInteger('studio_id')->nullable()->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_plans') && Schema::hasColumn('user_plans', 'studio_id')) {
            Schema::table('user_plans', function (Blueprint $table) {
                $table->dropColumn('studio_id');
            });
        }
    }
};
