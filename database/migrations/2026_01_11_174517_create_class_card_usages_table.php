<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('class_card_usages');
        Schema::enableForeignKeyConstraints();
        Schema::create('class_card_usages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_class_card_id')
                ->constrained('user_class_cards')
                ->cascadeOnDelete();

            // No bookings yet, so we consume credits against a session.
            $table->foreignId('class_session_id')
                ->constrained('class_sessions')
                ->cascadeOnDelete();

            $table->timestamp('used_at')->useCurrent();

            $table->timestamps();

            // Stop duplicate usage for same session on the same card
            $table->unique(['user_class_card_id', 'class_session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_card_usages');
    }
};
