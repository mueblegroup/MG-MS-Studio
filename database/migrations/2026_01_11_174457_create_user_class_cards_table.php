<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_class_cards');
        Schema::enableForeignKeyConstraints();
        Schema::create('user_class_cards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_card_id')->constrained('class_cards')->cascadeOnDelete();

            $table->timestamp('purchased_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();

            $table->unsignedInteger('classes_remaining');

            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_class_cards');
    }
};
