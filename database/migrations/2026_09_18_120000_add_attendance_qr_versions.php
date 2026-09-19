<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['class_sessions', 'plan_sessions'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('attendance_qr_version', 64)->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['class_sessions', 'plan_sessions'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn('attendance_qr_version'));
        }
    }
};
