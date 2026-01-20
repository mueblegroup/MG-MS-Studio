<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('plan_session_id')->nullable()->after('class_session_assignment_id');

            $table->index('plan_session_id');
            // One attendance per user per plan session
            // We need user_id in attendances for this (see below).
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['plan_session_id']);
            $table->dropColumn('plan_session_id');
        });
    }
};
