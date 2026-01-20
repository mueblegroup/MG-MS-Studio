<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            // If logged in
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // If guest
            $table->string('session_id')->nullable()->index();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // one active cart per user (when user_id exists)
            $table->unique(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
