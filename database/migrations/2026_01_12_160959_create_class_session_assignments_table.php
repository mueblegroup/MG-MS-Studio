<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('class_session_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // student
            $table->foreignId('class_session_id')->constrained('class_sessions')->cascadeOnDelete();

            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete(); // admin
            $table->text('notes')->nullable();

            $table->string('status')->default('assigned'); // assigned | cancelled (optional)
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['user_id', 'class_session_id'], 'uniq_student_session');
            $table->index(['class_session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_session_assignments');
    }
};
