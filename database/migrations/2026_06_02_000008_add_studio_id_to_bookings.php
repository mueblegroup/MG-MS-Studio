<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings') && !Schema::hasColumn('bookings', 'studio_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->unsignedBigInteger('studio_id')->nullable()->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'studio_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('studio_id');
            });
        }
    }
};
