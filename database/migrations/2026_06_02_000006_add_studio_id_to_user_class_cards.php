<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_class_cards') && !Schema::hasColumn('user_class_cards', 'studio_id')) {
            Schema::table('user_class_cards', function (Blueprint $table) {
                $table->unsignedBigInteger('studio_id')->nullable()->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_class_cards') && Schema::hasColumn('user_class_cards', 'studio_id')) {
            Schema::table('user_class_cards', function (Blueprint $table) {
                $table->dropColumn('studio_id');
            });
        }
    }
};
