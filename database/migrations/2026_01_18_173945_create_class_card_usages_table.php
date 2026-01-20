<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('class_card_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_class_card_id');
            $table->unsignedBigInteger('used_by')->nullable(); // admin user id
            $table->timestamp('used_at')->useCurrent();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index('user_class_card_id');
            // optional foreign keys if you want:
            // $table->foreign('user_class_card_id')->references('id')->on('user_class_cards')->cascadeOnDelete();
            // $table->foreign('used_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_card_usages');
    }
};
