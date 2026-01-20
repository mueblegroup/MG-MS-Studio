<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('class_session_bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('class_session_id')->constrained('class_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();

            // later: how they paid (classcard/plan/paid/special)
            $table->string('source')->nullable(); // e.g. classcard, plan, paid, manual

            // later: attendance tracking
            $table->timestamp('attended_at')->nullable();
            $table->timestamps();

            $table->unique(['class_session_id', 'student_id'], 'unique_student_session');
            $table->index(['student_id', 'class_session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_session_bookings');
    }
};
