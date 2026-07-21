<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->string('status', 30)->default('scheduled')->after('venue_name');
            $table->string('change_type', 30)->nullable()->after('status');
            $table->text('change_reason')->nullable()->after('change_type');
            $table->foreignId('changed_by')->nullable()->after('change_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at')->nullable()->after('changed_by');
            $table->index(['class_id', 'status', 'start_time'], 'class_sessions_class_status_start_idx');
        });
    }

    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropIndex('class_sessions_class_status_start_idx');
            $table->dropConstrainedForeignId('changed_by');
            $table->dropColumn(['status', 'change_type', 'change_reason', 'changed_at']);
        });
    }
};