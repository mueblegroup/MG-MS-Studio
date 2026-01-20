<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plan_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plan_id')
                ->constrained('plans')
                ->cascadeOnDelete();

            $table->string('session_name')->nullable(); // optional label e.g. "Class 1"
            $table->dateTime('start_time');
            $table->dateTime('end_time');

            $table->integer('capacity')->nullable();
            $table->string('venue_name')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['plan_id', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_sessions');
    }
};
