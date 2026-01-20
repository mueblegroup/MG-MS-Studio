<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->text('description')->nullable();

            $table->decimal('price', 10, 2)->default(0);
            // recurrence config for plan schedule (optional, but useful for reference)
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_frequency', ['everyday', '7days', 'monthly', 'yearly', 'custom'])->nullable();
            $table->unsignedSmallInteger('custom_frequency_days')->nullable();
            $table->date('until_date')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
