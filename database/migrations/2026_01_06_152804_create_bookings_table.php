<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_session_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['booked', 'cancelled', 'attended', 'no_show'])->default('booked');
            $table->timestamps();

            $table->unique(['user_id', 'class_session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
