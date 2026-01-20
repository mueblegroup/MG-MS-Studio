<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Allow attendance to be recorded via class_session_assignments too
            $table->unsignedBigInteger('class_session_assignment_id')->nullable()->after('booking_id');

            // Make sure you have attended_at (if you already have it, remove this line)
            if (!Schema::hasColumn('attendances', 'attended_at')) {
                $table->timestamp('attended_at')->nullable();
            }

            // Optional: status for admin marking
            if (!Schema::hasColumn('attendances', 'status')) {
                $table->enum('status', ['attended', 'no_show'])->nullable();
            }

            $table->unique(['class_session_assignment_id']);
            $table->index('class_session_assignment_id');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique(['class_session_assignment_id']);
            $table->dropIndex(['class_session_assignment_id']);
            $table->dropColumn('class_session_assignment_id');

            // If you added these, drop them (only if you created them)
            // $table->dropColumn(['attended_at', 'status']);
        });
    }
};
